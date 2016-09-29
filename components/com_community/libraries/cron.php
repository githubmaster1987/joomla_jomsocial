<?php

/**
 * @copyright (C) 2013 iJoomla, Inc. - All rights reserved.
 * @license GNU General Public License, version 2 (http://www.gnu.org/licenses/gpl-2.0.html)
 * @author iJoomla.com <webmaster@ijoomla.com>
 * @url https://www.jomsocial.com/license-agreement
 * The PHP code portions are distributed under the GPL license. If not otherwise stated, all images, manuals, cascading style sheets, and included JavaScript *are NOT GPL, and are released under the IJOOMLA Proprietary Use License v1.0
 * More info at https://www.jomsocial.com/license-agreement
 */
defined('_JEXEC') or die('Restricted access');

class CCron {

    private $_message = array();

    /**
     *
     */
    public function run() {
        jimport('joomla.filesystem.file');
        set_time_limit(120);
        $jinput = JFactory::getApplication()->input;

        // The cron job caller has the option to specify specific cron target
        $target = $jinput->getWord('target', '');
        if (!empty($target)) {
            $target = '_' . $target;
            if (method_exists($this, $target)) {
                // We're about to run a targeted con job
                // Close the connection so that the caller terminate the call
                while (ob_get_level())
                    ob_end_clean();
                header('Connection: close');
                ignore_user_abort();
                ob_start();
                echo('Closed');
                $size = ob_get_length();
                header("Content-Length: $size");
                ob_end_flush();
                flush();

                // The caller will get connection closed. Now run the target
                $this->$target();
            }
        } else {
            /* complete process all tasks */
            $this->_sendEmails();
            $this->processDigestMail();
            $this->_convertVideos();
            $this->_archiveActivities();
            $this->_cleanRSZFiles();
            $this->_removeTempPhotos();
            $this->_removeTempVideos();
            $this->_processPhotoStorage();
            $this->_updatePhotoFileSize();
            $this->_updateVideoFileSize();
            $this->_removeDeletedPhotos();
            $this->_processVideoStorage();
            $this->_processAvatarCoverStorage(COMMUNITY_PROCESS_STORAGE_LIMIT, 'users');
            $this->_processAvatarCoverStorage(COMMUNITY_PROCESS_STORAGE_LIMIT, 'groups');
            $this->_processAvatarCoverStorage(COMMUNITY_PROCESS_STORAGE_LIMIT, 'events');
            $this->_removePendingInvitation();
            $this->_processFileStorage();
            $this->_createIndexFile(JPATH_ROOT . '/images');
            $this->_removeDeletedUserFolder(JPATH_ROOT . '/images');
            $this->_unFeaturedEvents();

            $this->_updateCountryUserCount();
            $this->_updateCityUserCount();
        }

        // Include CAppPlugins library
        require_once( JPATH_COMPONENT . '/libraries/apps.php');
        // Trigger system event onCronRun
        $appsLib = CAppPlugins::getInstance();
        $appsLib->loadApplications();

        $args = array();
        $appsLib->triggerEvent('onCronRun', $args);

        //if debug is enabled, display in plain text
        $mainframe = JFactory::getApplication();
        $jinput = $mainframe->input;

        if($jinput->get('debug')){
            echo '<pre>';
            print_r($this->_message);
            echo '</pre>';
            die;
        }

        // Display cron messages if neessary
        header('Content-type: text/xml');
        echo '<?xml version="1.0" encoding="UTF-8" ?' . '>'; // saperated to assist syntax highliter
        echo '<messages>';
        foreach ($this->_message as $msg) {
            echo '<message>';
            echo $msg;
            echo '</message>';
        }
        echo '</messages>';
        exit;
    }

    /**
     *
     */
    public function sendEmailsOnPageLoad() {
        $mailq = new CMailq();
        $mailq->send();
    }

    /**
     * Avatar / Cover storage transfer
     * @param type $updateNum
     * @param type $element
     * @return type
     */
    private function _processAvatarCoverStorage($updateNum = COMMUNITY_PROCESS_STORAGE_LIMIT, $element = 'users') {
        $config = CFactory::getConfig();
        //$jconfig  = JFactory::getConfig();
        $app = JFactory::getApplication();

        // Because the configuration of users remote storage is stored as user_avatar_storage, we need to get the correct name for it.
        $configElement = $element == 'users' ? 'user' : $element;
        $configElement .= '_avatar_storage';

        /* get storage type */
        $storageMethod = $config->getString($configElement);
        $storage = CStorage::getStorage($storageMethod);

        $totalMoved = 0;
        $totalCover = 0;
        $db = JFactory::getDBO();

        /**
         * @todo should we use model to get user with avatar instead of query here ?
         */
        /* query user with avatar */
        $query = 'SELECT * FROM ' . $db->quoteName('#__community_' . $element) . ' '
                . 'WHERE ' . $db->quoteName('storage') . ' != ' . $db->quote($storageMethod) . ' '
                . ' AND ( '
                . '( ' . $db->quoteName('thumb') . ' != ' . $db->quote('') . ' AND ' . $db->quoteName('avatar') . ' != ' . $db->Quote('') . ' ) '
                . ' OR ' . $db->quoteName('cover') . ' != ' . $db->quote('')
                . ' ) '
                . 'LIMIT ' . $updateNum;
        $db->setQuery($query);

        $rows = $db->loadObjectList();



        if (!$rows) {
            $this->_message[] = JText::_('No avatars or cover of ' . $element . ' needed to be transferred');
            return;
        }

        foreach ($rows as $row) {
            $current = CStorage::getStorage($row->storage);

            /* do cover transfer is exists */
            if ($current->exists($row->cover)) {
                $tmpCoverFileName = JFactory::getConfig()->get('tmp_path') . '/' . md5($row->cover);
                $current->get($row->cover, $tmpCoverFileName);
                if (JFile::exists($tmpCoverFileName)) {
                    if ($storage->put($row->cover, $tmpCoverFileName)) {
                        switch ($element) {
                            case 'users':
                                // User's avatar and thumbnail is successfully uploaded to the remote location.
                                // We need to update it now.
                                $user = CFactory::getUser($row->userid);
                                $user->_storage = $storageMethod;
                                $user->save();

                                $cover = $user->_cover;

                                $photoTable = JTable::getInstance('Photo','CTable');
                                $photoTable->load(array('image'=>$row->cover));

                                $photoTable->storage = $storageMethod;
                                $photoTable->store();
                                break;
                            case 'groups':
                                $group = JTable::getInstance('Group', 'CTable');
                                $group->load($row->id);
                                $group->storage = $storageMethod;
                                $group->store();

                                $cover = $group->cover;

                                $photoTable = JTable::getInstance('Photo','CTable');
                                $photoTable->load(array('image'=>$row->cover));

                                $photoTable->storage = $storageMethod;
                                $photoTable->store();

                                break;
                            case 'events':
                                $event = JTable::getInstance('Event', 'CTable');
                                $event->load($row->id);
                                $event->storage = $storageMethod;
                                $event->store();

                                $cover = $event->cover;

                                $photoTable = JTable::getInstance('Photo','CTable');
                                $photoTable->load(array('image'=>$row->cover));

                                $photoTable->storage = $storageMethod;
                                $photoTable->store();

                        }
                        // Delete existing storage's avatar and thumbnail.
                        $current->delete($cover);

                        // Remove temporary generated avatar and thumbnail.
                        JFile::delete($tmpCoverFileName);
                        $totalCover++;
                    }
                }
            } else {
                switch ($element) {
                    case 'users':
                        // User's avatar and thumbnail is successfully uploaded to the remote location.
                        // We need to update it now.
                        $user = CFactory::getUser($row->userid);
                        $user->_storage = $storageMethod;
                        $user->save();
                        break;
                    case 'groups':
                        $group = JTable::getInstance('Group', 'CTable');
                        $group->load($row->id);
                        $group->storage = $storageMethod;
                        $group->store();
                        break;
                    case 'events':
                        $event = JTable::getInstance('Event', 'CTable');
                        $event->load($row->id);
                        $event->storage = $storageMethod;
                        $event->store();
                        break;
                }
            }

            /* If it exist on current storage, we can transfer it to preferred storage */
            if ($current->exists($row->thumb) && $current->exists($row->avatar)) {
                /**
                 * @todo Need to check if local is newer than remote storage
                 */
                // Move locally if file exists on remote storage.
                //$tmpThumbFileName = $jconfig->getValue( 'tmp_path' ) .'/'. md5( $row->thumb );
                $tmpThumbFileName = JFactory::getConfig()->get('tmp_path') . '/' . md5($row->thumb);
                $current->get($row->thumb, $tmpThumbFileName);

                //$tmpAvatarFileName    = $jconfig->getValue( 'tmp_path' ) .'/'. md5( $row->avatar );
                $tmpAvatarFileName = JFactory::getConfig()->get('tmp_path') . '/' . md5($row->avatar);
                $current->get($row->avatar, $tmpAvatarFileName);

                if($element == 'users'){
                    $avatar = $row->avatar;
                    $avatar = explode(".",$avatar);
                    $streamPath = $avatar[0].'_stream_.'.$avatar[1];

                    $avatar = $row->avatar;
                    $avatar = explode("/",$avatar);
                    $avatar[2] = 'profile-'.$avatar[2];
                    $profilePath = implode("/", $avatar);

                    if(JFile::exists($streamPath)){
                        if($storage->put($streamPath, $streamPath)){
                            JFile::delete($streamPath);
                        }
                    }

                    if(JFile::exists($profilePath)){
                        if($storage->put($profilePath, $profilePath)){
                            JFile::delete($profilePath);
                        }
                    }
                }

                /**
                 * Check again if prepare transfer files exists
                 */
                if (JFile::exists($tmpThumbFileName) && JFile::exists($tmpAvatarFileName)) {
                    /* Do transfer into preferred storage */
                    if ($storage->put($row->avatar, $tmpAvatarFileName) && $storage->put($row->thumb, $tmpThumbFileName)) {
                        switch ($element) {
                            case 'users':
                                // User's avatar and thumbnail is successfully uploaded to the remote location.
                                // We need to update it now.
                                $user = CFactory::getUser($row->userid);
                                $user->_storage = $storageMethod;
                                $user->save();

                                $avatar = $user->_avatar;
                                $thumb = $user->_thumb;

                                break;
                            case 'groups':
                                $group = JTable::getInstance('Group', 'CTable');
                                $group->load($row->id);
                                $group->storage = $storageMethod;
                                $group->store();

                                $avatar = $group->avatar;
                                $thumb = $group->thumb;
                                break;
                        }
                        // Delete existing storage's avatar and thumbnail.
                        $current->delete($avatar);
                        $current->delete($thumb);

                        // Remove temporary generated avatar and thumbnail.
                        JFile::delete($tmpAvatarFileName);
                        JFile::delete($tmpThumbFileName);
                        $totalMoved++;
                    }
                } else {
                    switch ($element) {
                        case 'users':
                            // User's avatar and thumbnail is successfully uploaded to the remote location.
                            // We need to update it now.
                            $user = CFactory::getUser(
                                $row->userid);
                            $user->_storage = $storageMethod;
                            $user->save();
                            break;
                        case 'groups':
                            $group = JTable::getInstance('Group', 'CTable');
                            $group->load($row->id);
                            $group->storage = $storageMethod;
                            $group->store();
                            break;
                    }
                }
            }
        }
        $this->_message[] = JText::sprintf('%1$s avatar file(s) transferred.', $totalMoved);
        $this->_message[] = JText::sprintf('%1$s cover file(s) transferred.', $totalCover);
    }

    /**
     * For photos that does not have proper filesize info, update it.
     * Due to IO issues, run only 20 photos at a time
     */
    private function _updatePhotoFileSize($updateNum = 20) {

        $db = JFactory::getDBO();

        $sql = 'SELECT ' . $db->quoteName('id')
                . ' FROM ' . $db->quoteName('#__community_photos')
                . ' WHERE ' . $db->quoteName('filesize') . '=' . $db->Quote(0)
                . ' ORDER BY rand() LIMIT ' . $updateNum;
        $db->setQuery($sql);
        $photos = $db->loadObjectList();

        if (!empty($photos)) {
            $photo = JTable::getInstance('Photo', 'CTable');

            foreach ($photos as $data) {
                $photo->load($data->id);
                $originalPath = JPATH_ROOT . '/' . $photo->original;
                if (JFile::exists($originalPath)) {
                    $photo->filesize = sprintf("%u", filesize($originalPath));
                    $photo->store();
                }
            }
        }
    }

    /**
     * For videos that does not have proper filesize info, update it.
     * Due to IO issues, run only 20 photos at a time
     */
    private function _updateVideoFileSize($updateNum = 20) {

        $db = JFactory::getDBO();
        $sql = 'SELECT ' . $db->quoteName('id') . ', ' . $db->quoteName('creator')
                . ' FROM ' . $db->quoteName('#__community_videos')
                . ' WHERE ' . $db->quoteName('type') . '=' . $db->quote('file')
                . ' AND ' . $db->quoteName('status') . '=' . $db->quote('ready')
                . ' AND ' . $db->quoteName('filesize') . '=' . $db->Quote(0)
                . ' ORDER BY rand() LIMIT ' . $updateNum;
        $db->setQuery($sql);
        $videos = $db->loadObjectList();

        if (!empty($videos)) {
            $video = JTable::getInstance('Video', 'CTable');

            foreach ($videos as $data) {
                $video->load($data->id);
                $videoPath = JPATH::clean(JPATH_ROOT
                                . '/' . $video->path);
                if (JFile::exists($videoPath)) {
                    $video->filesize = sprintf("%u", filesize($videoPath));
                    $video->store();
                }
            }
        }
    }

    /**
     * Remove all photos that are orphaned, whose parent album has been deleted
     */
    private function _removeDeletedPhotos($updateNum = 100) {
        $db = JFactory::getDBO();

        $sql = 'SELECT * FROM ' . $db->quoteName('#__community_photos')
                . ' WHERE ' . $db->quoteName('albumid') . '=' . $db->Quote(0)
                . ' ORDER BY id limit ' . $updateNum;
        $db->setQuery($sql);
        $result = $db->loadObjectList();

        if ($result) {
            foreach ($result as $row) {
                $photo = JTable::getInstance('Photo', 'CTable');
                $photo->load($row->id);
                $photo->delete();

                // Remove all related activities
                $query = 'DELETE FROM ' . $db->quoteName('#__community_activities')
                        . ' WHERE ' . $db->quoteName('app') . ' LIKE ' . $db->Quote('photos')
                        . ' AND ' . $db->quoteName('cid') . ' =' . $db->Quote($row->id)
                        . ' AND ' . $db->quoteName('params') . ' LIKE ' . $db->Quote('%photoid=' . $row->id . '%');
                $db->setQuery($query);
                $db->execute();
            }
        }


        /**
         * @since 3.2
         * Get photos in deleting queue and do delete physical files ( would be on S3 )
         */
        $query = ' SELECT * FROM ' . $db->quoteName('#__community_photos');
        $query .= ' WHERE '
                . $db->quoteName('published') . ' = ' . (int) (0)
                . ' AND '
                . $db->quoteName('status') . ' = ' . $db->quote('delete');
        $db->setQuery($query);
        $deletedPhotos = $db->loadObjectList();
        if ($deletedPhotos) {
            foreach ($deletedPhotos as $deletedPhoto) {
                $currentStorage = CStorage::getStorage($deletedPhoto->storage);
                $currentStorage->delete($deletedPhoto->image);
                $currentStorage->delete($deletedPhoto->thumbnail);
                $currentStorage->delete($deletedPhoto->original);
            }
        }
        /* And now we do delete in database */
        $query = ' DELETE FROM ' . $db->quoteName('#__community_photos');
        $query .= ' WHERE '
                . $db->quoteName('published') . ' = ' . (int) (0)
                . ' AND '
                . $db->quoteName('status') . ' = ' . $db->quote('delete');
        $db->setQuery($query);
        $db->execute();
    }

    /**
     * Remove old dynamically resized image files
     */
    private function _cleanRSZFiles($updateNum = 5) {
        $db = JFactory::getDBO();

        $sql = 'SELECT * FROM ' . $db->quoteName('#__community_photos')
                . ' ORDER BY rand() limit ' . $updateNum;
        $db->setQuery($sql);
        $result = $db->loadObjectList();

        if (!$result) {
            return;
        }

        foreach ($result as $row) {
            // delete all rsz_ files which are no longer used
            $rszFiles = JFolder::files(dirname(JPATH_ROOT . '/' . $row->image), '.', false, true);
            if ($rszFiles)
                foreach ($rszFiles as $rszRow) {
                    if (substr(basename($rszRow), 0, 3) == 'rsz') {
                        JFile::delete($rszRow);
                    }
                }
        }
    }

    /**
     * If remote storage is used, transfer some files to the remote storage
     * - fetch file from current storage to a temp location
     * - put file from temp to new storage
     * - delete file from old storage
     */
    private function _processPhotoStorage($updateNum = 5) {
        $config = CFactory::getConfig();
        //$jconfig = JFactory::getConfig();
        $app = JFactory::getApplication();
        $photoStorage = $config->getString('photostorage');

        $fileTranferCount = 0;
        $storage = CStorage::getStorage($photoStorage);

        $db = JFactory::getDBO();

        // @todo, we nee to find a way to make sure that we transfer most of
        // our photos remotely
        $sql = 'SELECT * FROM ' . $db->quoteName('#__community_photos')
                . ' WHERE ' . $db->quoteName('storage') . '!=' . $db->Quote($photoStorage)
                . ' AND ' . $db->quoteName('albumid') . '!=' . $db->Quote(0)
                . ' AND ' . $db->quoteName('image').' NOT LIKE '.$db->Quote('%cover%')
                . ' ORDER BY rand() limit ' . $updateNum;
        $db->setQuery($sql);
        $result = $db->loadObjectList();

        if (!$result) {
            $this->_message[] = JText::_('No files to transfer.');
            return;
        }

        foreach ($result as $row) {
            $currentStorage = CStorage::getStorage($row->storage);

            // If current storage is file based, create the image since we might not have them yet
            if ($row->storage == 'file' && !JFile::exists(JPATH_ROOT . '/' . $row->image)) {
                // resize the original image to a smaller viewable version
                $this->_message[] = 'Image file missing. Creating image file.';

                // make sure original file exist
                if (JFile::exists(JPATH_ROOT . '/' . $row->original)) {
                    $displyWidth = $config->getInt('photodisplaysize');
                    $info = getimagesize(JPATH_ROOT . '/' . $row->original);
                    $imgType = image_type_to_mime_type($info[2]);
                    $width = ($info[0] < $displyWidth) ? $info[0] : $displyWidth;
                    CImageHelper ::resizeProportional(JPATH_ROOT . '/' . $row->original, JPATH_ROOT . '/' . $row->image, $imgType, $width);
                } else {
                    $this->_message[] = 'Original file is missing!!';
                }
            }

            // If it exist on current storage, we can transfer it to preferred storage
            if ($currentStorage->exists($row->image) && $currentStorage->exists($row->thumbnail)) {
                // File exist on remote storage, move it locally first
                //$tempFilename = $jconfig->getValue('tmp_path').'/'. md5($row->image);
                $tempFilename = JFactory::getConfig()->get('tmp_path') . '/' . md5($row->image);
                $currentStorage->get($row->image, $tempFilename);

                //$thumbsTemp       = $jconfig->getValue('tmp_path').'/thumb_' . md5($row->thumbnail);
                $thumbsTemp = JFactory::getConfig()->get('tmp_path') . '/thumb_' . md5($row->thumbnail);
                $currentStorage->get($row->thumbnail, $thumbsTemp);

                if (JFile::exists($tempFilename) && JFile::exists($thumbsTemp)) {
                    // we assume thumbnails is always there
                    // put both image and thumbnails remotely

                    if ($storage->put($row->image, $tempFilename) && $storage->put($row->thumbnail, $thumbsTemp)) {
                        // if the put is successful, update storage type
                        $photo = JTable::getInstance('Photo', 'CTable');
                        $photo->load($row->id);
                        $photo->storage = $photoStorage;
                        $photo->store();

                        //UPDATE ALBUM THUMBNAIL ======
                        $album = JTable::getInstance('Album', 'CTable');
                        $album->load($photo->albumid);
                        if ($row->id == $album->thumbnail_id) {
                            $album->setParam('thumbnail', $storage->getURI($row->thumbnail));
                            $album->store();
                        }
                        unset($album);
                        //============================

                        $currentStorage->delete($row->image);
                        $currentStorage->delete($row->thumbnail);

                        // remove temporary file
                        JFile::delete($tempFilename);
                        JFile::delete($thumbsTemp);
                        $fileTranferCount++;
                    }
                }
            }
        }

        $this->_message[] = $fileTranferCount . ' files transferred.';
    }

    private function _processVideoStorage($updateNum = 5) {
        $config = CFactory::getConfig();
        //$jconfig      = JFactory::getConfig();

        $app = JFactory::getApplication();
        $videoStorage = $config->getString('videostorage');

        $db = JFactory::getDBO();
        $query = ' SELECT * FROM ' . $db->quoteName('#__community_videos')
                . ' WHERE ' . $db->quoteName('storage') . ' != ' . $db->quote($videoStorage)
                //. ' AND ' . $db->quoteName('type') . ' = ' . $db->quote('file')
                . ' AND ' . $db->quoteName('status') . ' = ' . $db->quote('ready') . ' ORDER BY rand() limit ' . $updateNum;

        $db->setQuery($query);
        $result = $db->loadObjectList();

        if (!$result) {
            $this->_message[] = JText::_('No Videos to transfer.');
            return;
        }

        $storage = CStorage::getStorage($videoStorage);
        //$tempFolder   = $jconfig->getValue('tmp_path');
        $tempFolder = JFactory::getConfig()->get('tmp_path');
        $fileTransferCount = 0;

        foreach ($result as $videoEntry) {
            $currentStorage = CStorage::getStorage($videoEntry->storage);

            if ($videoEntry->type == 'file') {
                // If it exist on current storage, we can transfer it to preferred storage
                if ($currentStorage->exists($videoEntry->path)) {
                    // File exist on remote storage, move it locally first
                    $tempFilename = JPATH::clean($tempFolder . '/' . md5($videoEntry->path));
                    $tempThumbname = JPATH::clean($tempFolder . '/' . md5($videoEntry->thumb));
                    $currentStorage->get($videoEntry->path, $tempFilename);
                    $currentStorage->get($videoEntry->thumb, $tempThumbname);

                    if (JFile::exists($tempFilename) && JFile::exists($tempThumbname)) {
                        // we assume thumbnails is always there
                        // put both video and thumbnails remotely
                        if ($storage->put($videoEntry->path, $tempFilename) &&
                                $storage->put($videoEntry->thumb, $tempThumbname)) {
                            // if the put is successful, update storage type
                            $video = JTable::getInstance('Video', 'CTable');
                            $video->load($videoEntry->id);
                            $video->storage = $videoStorage;
                            $video->store();

                            // remove files on storage and temporary files

                            $currentStorage->delete($videoEntry->path);
                            $currentStorage->delete($videoEntry->thumb);
                            JFile::delete($tempFilename);
                            JFile::delete($tempThumbname);

                            $fileTransferCount++;
                        }
                    }
                }
            } else {
                // This is for non-upload video file type e.g. YouTube etc
                // We'll just process the video thumbnail only

                if ($currentStorage->exists($videoEntry->thumb)) {
                    $tempThumbname = JPATH::clean($tempFolder . '/' . md5($videoEntry->thumb));
                    $currentStorage->get($videoEntry->thumb, $tempThumbname);

                    if (JFile::exists($tempThumbname)) {
                        if ($storage->put($videoEntry->thumb, $tempThumbname)) {
                            $video = JTable::getInstance('Video', 'CTable');
                            $video->load($videoEntry->id);
                            $video->storage = $videoStorage;
                            $video->store();

                            $currentStorage->delete($videoEntry->thumb);
                            JFile::delete($tempThumbname);
                            $fileTransferCount++;
                        }
                    }
                }
            }
        }
        $this->_message [] = $fileTransferCount . ' video file(s) transferred';
    }

    private function _convertVideos() {

        $videos = new CVideos ();
        $videos->runConvert();
        if (trim($videos->errorMsg[0]) == 'No videos pending for conversion.') {
            $this->_message[] = "No videos pending for conversion.";
        } else if (strpos($videos->errorMsg [0], 'videos converted successfully')) {
            $this->_message [] = $videos->errorMsg[0];
        } else {
            $this->_message [] = 'Could not convert video';
        }
    }

    private function _sendEmails() {

        $mailq = new CMailq();

        $config = CFactory::getConfig();
        $mailq->send($config->get('totalemailpercron'));
    }

    /**
     * Archive older activities for performance reason
     */
    private function _archiveActivities() {
        $config = CFactory::getConfig();

        $db = JFactory::getDBO();

        $date = JDate::getInstance();
        $currentTime = $date->toSql();

        if(!$config->get('archive_activity_max_day')){
            return;
        }

        // Get the id of the most recent 500th (or whatever archive_activity_limit is)
        $sql = 'SELECT id FROM ' . $db->quoteName('#__community_activities')
                . ' WHERE '
                . $db->quoteName('archived') . '=' . $db->Quote(0)
                . ' AND DATEDIFF(\'' . $currentTime . '\',' . $db->quoteName('created') . ')<=' . $config->get('archive_activity_max_day')
                . ' ORDER BY ' . $db->quoteName('id') . ' DESC'
                . ' LIMIT ' . $config->get('archive_activity_limit') . ' , 1 ';

        $db->setQuery($sql);
        $id = $db->loadResult();

        if ($id) {

            // Now that we have the id, since id is auto-increment, we can assume
            // any value lower than it is an earlier stream data
            $sql = 'UPDATE ' . $db->quoteName('#__community_activities') . ' act'
                    . ' SET act.' . $db->quoteName('archived') . ' = ' . $db->Quote(1)
                    . ' WHERE '
                    /* Only archive those not archived yet */
                    . $db->quoteName('archived') . '=' .
                    $db->Quote(0)
                    . ' AND '
                    . $db->quoteName('id') . '<' . $db->Quote($id);

            $db->setQuery($sql);
            $db->execute();
        }
    }

    private function _removeTempPhotos() {
        $db = JFactory::getDBO();
        $sql = 'UPDATE ' . $db->quoteName('#__community_photos')
                . ' SET ' . $db->quoteName('albumid') . '=' . $db->Quote(0)
                . ' WHERE ' . $db->quoteName('status') . '=' . $db->Quote('temp')
                . '  AND ' . $db->quoteName('created') . ' < DATE_SUB( UTC_TIMESTAMP()  , INTERVAL 30 MINUTE )';

        $db->setQuery($sql);
        $db->execute();
    }

    private function _removeTempVideos() {
        $db = JFactory::getDBO();


        $sql = ' SELECT ' . $db->quoteName('thumb') . ' FROM ' . $db->quoteName('#__community_videos')
                . ' WHERE ' . $db->quoteName('status') . '=' . $db->quote('temp');

        $db->setQuery($sql);

        $result = $db->loadObjectList();

        if (!$result) {
            $this->_message[] = JText::_('No temporary videos to delete.');
            //return;
        } foreach ($result as $video) {
            $thumb = JPATH_ROOT . '/' . $video->thumb;
            JFile::delete($thumb);
        }

        $sql = 'DELETE FROM ' . $db->quoteName('#__community_videos')
                . ' WHERE ' . $db->quoteName('status') . '=' . $db->quote('temp');

        $db->setQuery($sql);

        $db->execute();

        /* Delete original video files if asked */
        $config = CFactory::getConfig();
        $videoFolder = $config->get('videofolder');
        $deleteOriginal = $config->get('deleteoriginalvideos');
        if ($deleteOriginal) {
            /* These videos already converted */
            $query = ' SELECT * FROM #__community_videos WHERE ' . $db->quoteName('status') . ' = ' . $db->quote('ready') . ' AND ' . $db->quoteName('type') . ' = ' . $db->quote('file');
            $db->setQuery($query);
            $videos = $db->loadObjectList();
            if ($videos) {
                foreach ($videos as $video) {
                    $path = JPATH_ROOT . '/' . $videoFolder . '/originalvideos/' . $video->creator . '/' . basename($video->path);
                    if (JFile::exists($path))
                        JFile::delete($path);
                }
            }
        }
    }

    private function _removePendingInvitation() {
        $eventTable = JTable::getInstance('Event', 'CTable');
        $eventTable->deletePendingMember();

        $this->_message[] = 'Removed Pending Invitation for Past Event';
    }

    private function _processFileStorage($updateNum = 5) {
        $config = CFactory::getConfig();
        //$jconfig = JFactory::getConfig();

        $app = JFactory ::getApplication();
        $fileStorage = $config->getString('file_storage');

        if ($fileStorage != 'file') {


            $fileTranferCount = 0;
            $storage = CStorage::getStorage($fileStorage);

            $db = JFactory::getDBO();

            $sql = 'SELECT * FROM ' . $db->quoteName('#__community_files')
                    . ' WHERE ' . $db->quoteName('storage') . '!=' . $db->Quote($fileStorage)
                    . ' ORDER BY rand() limit ' . $updateNum;

            $db->setQuery($sql);
            $result = $db->loadObjectList();

            if (
                    !$result) {
                $this->_message[] = JText::_('No files to transfer.');
                return;
            }

            foreach ($result as $row) {
                $currentStorage = CStorage::getStorage($row->storage);

                if ($currentStorage->exists($row->filepath)) {
                    // File exist on remote storage, move it locally first
                    //$tempFilename = $jconfig->getValue('tmp_path').'/'. md5($row->filepath);
                    $tempFilename = JFactory::getConfig()->get('tmp_path') . '/' . md5($row->filepath);
                    $currentStorage->get($row->filepath, $tempFilename);

                    if (JFile::exists($tempFilename)) {
                        if ($storage->put($row->filepath, $tempFilename)) {
                            // if the put is successful, update storage type
                            $file = JTable::getInstance('File', 'CTable');
                            $file->load($row->id);
                            $file->storage = $fileStorage;
                            $file->store();

                            $currentStorage->delete($row->filepath);

                            // remove temporary file
                            JFile::delete($tempFilename);
                            $fileTranferCount++;
                        }
                    }
                }
            }
            $this->_message[] = $fileTranferCount . ' files transferred. to s3';
        }
    }

    private function _createIndexFile($path, $level = 0) {

        $ignore = array('cgi-bin', '.', '..');
        // Directories to ignore when listing output. Many hosts
        // will deny PHP access to the cgi-bin.

        $dh = @opendir($path);
        // Open the directory to the handle $dh
        $flag = true;
        while (false !== ( $file = readdir($dh) )) {
            // Loop through the directory

            if (!in_array($file, $ignore)) {
                // Check that this file is not to be ignored
                //  $spaces = str_repeat('&nbsp;', ( $level * 4));
                // Just to add spacing to the list, to better
                // show the directory tree.

                if (is_dir("$path/$file")) {
                    // Its a directory, so we need to keep reading down...
                    //echo "<strong>$spaces $file</strong><br />";
                    $this->_createIndexFile("$path/$file", ($level + 1));
                    // Re-call this same function but on a new directory.
                    // this is what makes function recursive.
                } else {
                    if ($file == 'index.html') {
                        //echo "$path/$file" . '<br />';
                        $flag = false;
                    } else {
                        //echo "$path/$file" . '<br />';
                    }

                    // Just print out the filename
                }
            }
        }

        if ($flag) {
            $buffer = '';
            JFile::write($path . '/index.html', $buffer);
        }
        closedir($dh);
        // Close the directory handle
    }

    private function _removeDeletedUserFolder($path) {

        $folders = JFolder::folders($path);
        $allowFolders = array('photos', 'groupphotos', 'cover', 'videos');

        foreach ($folders as $folder) {
            if (in_array($folder, $allowFolders)) {
                $sub_folders = JFolder::folders($path . '/' . $folder);

                if ($folder == 'photos' || $folder == 'videos') {
                    foreach ($sub_folders as $sub) {
                        $user = CFactory::getUser($sub);

                        if (is_null($user->username)) {
                            JFolder::delete($path . '/' . $folder . '/' . $sub);
                        }
                    }
                } elseif ($folder == 'cover') {
                    $pCover = JFolder::folders($path . '/' . $folder . '/profile');
                    if($pCover){
                        foreach ($pCover as $_pCoverId) {
                            $pUser = CFactory::getUser($_pCoverId);

                            if (is_null($pUser->username)) {
                                JFolder::delete($path . '/' . $folder . '/profile/' . $_pCoverId);
                            }
                        }
                    }

                }
            }
        }
    }

    /**
     *
     * @return type
     */
    protected function _unFeaturedEvents() {
        $db = JFactory::getDbo();
        $query = ' DELETE '.$db->quoteName('featured').'.* FROM ' . $db->quoteName('#__community_featured') . ' AS ' . $db->quoteName('featured');
        $query .= ' INNER JOIN ' . $db->quoteName('#__community_events') . ' AS ' . $db->quoteName('events') . ' ON ' . $db->quoteName('events') . '.' . $db->quoteName('id') . ' = ' . $db->quoteName('featured') . '.' . $db->quoteName('cid');
        $query .= ' WHERE ' . $db->quoteName('featured') . '.' . $db->quoteName('type') . ' = ' . $db->quote('events');
        $query .= ' AND ' . $db->quoteName('events') . '.' . $db->quoteName('enddate') . ' < CURDATE()';
        $db->setQuery($query);
        return $db->execute();
    }

    protected function _updateCountryUserCount() {
        $db = JFactory::getDbo();

        $sql = 'SELECT ' . $db->quoteName('id') . ' FROM ' . $db->quoteName('#__community_fields')
                . ' WHERE ' . $db->quoteName('type') . '=' . $db->Quote('country')
                . ' AND ' . $db->quoteName('visible') . '=' . $db->Quote('1');

        $db->setQuery($sql);

        $countryId = $db->loadResult();
        if (!empty($countryId)) {
            $query = 'SELECT TRIM(' . $db->quoteName('value') . ') as ' . $db->quoteName('country') . ', COUNT(' . $db->quoteName('value') . ') as ' . $db->quoteName('count') . ' FROM ' . $db->quoteName('#__community_fields_values')
                    . ' WHERE ' . $db->quoteName('field_id') . '=' . $db->quote($countryId)
                    . ' AND ' . $db->quoteName('value') . ' != "" '
                    . ' GROUP BY ' . $db->quoteName('country')
                    . ' ORDER BY ' . $db->quoteName('count') . ' DESC'
                    . ' LIMIT 0,5';

            $db->setQuery($query);

            $result = $db->loadObjectList();

            $config = JTable::getInstance( 'configuration' , 'CommunityTable' );
            $config->load( 'countryList' );
            $config->name = 'countryList';
            $params = new JRegistry( $config->params );
            $params->set( 'countryList' , $result );

            $config->params = $params->toString();

            $config->store();
        }

    }

    protected function _updateCityUserCount() {
        $db = JFactory::getDbo();

        $cityField = CFactory::getConfig()->get('fieldcodecity');

        $sql = 'SELECT ' . $db->quoteName('id') . ' FROM ' . $db->quoteName('#__community_fields')
                . ' WHERE ' . $db->quoteName('fieldcode') . '=' . $db->Quote($cityField);

        $db->setQuery($sql);

        $id = $db->loadResult();

        if (!empty($id)) {
            $query = 'SELECT ' . $db->quoteName('value') . ' as ' . $db->quoteName('city') . ', COUNT(' . $db->quoteName('value') . ') as ' . $db->quoteName('count') . ' FROM ' . $db->quoteName('#__community_fields_values')
                    . ' WHERE ' . $db->quoteName('field_id') . '=' . $db->quote($id)
                    . ' GROUP BY ' . $db->quoteName('value')
                    . ' ORDER BY ' . $db->quoteName('count') . ' DESC'
                    . ' LIMIT 0,5';

            $db->setQuery($query);

            $result = $db->loadObjectList();

            $config = JTable::getInstance( 'configuration' , 'CommunityTable' );
            $config->load( 'cityList' );
            $config->name = 'cityList';
            $params = new JRegistry( $config->params );
            $params->set( 'cityList' , $result );

            $config->params = $params->toString();

            $config->store();
        }

    }

    /**
     * Send off emails for inactive users about the recent updates
     * @param bool|false $debug debugging mode
     * @return bool|void
     */
    public function processDigestMail($debug = false, $ajax = false, $userId = 0, $days = 0){
        $db = JFactory::getDbo();
        //on cron run, lets get all the setings from digest
        $config = CFactory::getConfig();

        //we must first make sure the digest email is enabled, except if this is an ajax call
        if(!$config->get('enabledigest',1) && !$ajax){
            return false;
        }

        $inactiveDays = $config->get('digest_email_inactivity'); //days of inactivity

        //get all the users that is inactive for x days
        $query = "SELECT id, lastVisitDate,email FROM ".$db->quoteName('#__users')." as u "
            ." INNER JOIN ".$db->quoteName('#__community_users')." as a "
            //we might want to exclude the user who already been notified
            ." ON u.id=a.userid"
            ." LEFT JOIN ".$db->quoteName('#__community_digest_email')." as d"
            ." ON d.user_id=a.userid"
            ." WHERE "
            ."lastvisitDate <= DATE_SUB(CURDATE(), INTERVAL ".$inactiveDays." DAY)"
            ." AND lastvisitDate <> ".$db->quote('0000-00-00 00:00:00')
            ." AND (last_sent <= DATE_SUB(CURDATE(), INTERVAL ".$inactiveDays." DAY) OR d.last_sent IS NULL)"
            ." AND u.".$db->quoteName('block')."="."0 "
            ." AND a.".$db->quoteName('params')." NOT LIKE ".$db->quote('%"etype_system_reports_threshold":0%')
            ." AND a.".$db->quoteName('params')." NOT LIKE ".$db->quote('%"etype_system_reports_threshold":"0"%');

        $db->setQuery($query);
        $inactiveUsers = $db->loadObjectList(); // this is lists of users that can accept email digest and not being notified for the past x days

        //ajax request should be called from preview, so this is a dummy, replace the info needed
        if($days){
            $inactiveDays = $days;
        }

        if($ajax){
            //get all the users that is inactive for x days
            $query = "SELECT id, lastVisitDate,email FROM ".$db->quoteName('#__users')." as u "
                ." INNER JOIN ".$db->quoteName('#__community_users')." as a "
                //we might want to exclude the user who already been notified
                ." ON u.id=a.userid"
                ." LEFT JOIN ".$db->quoteName('#__community_digest_email')." as d"
                ." ON d.user_id=a.userid"
                ." WHERE "
                ." 1 "
                ." AND u.id=".$db->quote($userId)
                ." AND lastvisitDate <= DATE_SUB(CURDATE(), INTERVAL ".$inactiveDays." DAY)"
                ." AND lastvisitDate <> ".$db->quote('0000-00-00 00:00:00')
                ." AND (last_sent <= DATE_SUB(CURDATE(), INTERVAL ".$inactiveDays." DAY) OR d.last_sent IS NULL)"
                ." AND a.".$db->quoteName('params')." NOT LIKE ".$db->quote('%"etype_system_reports_threshold":0%')
                ." AND a.".$db->quoteName('params')." NOT LIKE ".$db->quote('%"etype_system_reports_threshold":"0"%');

            $db->setQuery($query);
            $inactiveUsers = $db->loadObjectList();
        }

        //if there is no inactive user, we dont have to do this
        if(!$inactiveUsers){
            return false;
        }

        //compile all the information needed
        $data = array();

        //default count for each
        $data['totalPosts'] = 0;
        $data['totalPhotos'] = 0;
        $data['totalVideos'] = 0;
        $data['totalGroups'] = 0;
        $data['totalEvents'] = 0;
        $data['totalDiscussions'] = 0;
        $data['totalAnnouncements'] = 0;

        //post entries
        if($config->get('digest_email_include_posts')){
            $postLimit = $config->get('digest_email_include_posts_count');//get the amount of post needed
            $query = "SELECT * FROM ".$db->quoteName('#__community_activities')
                ." WHERE "
                .$db->quoteName('app')."=".$db->quote('profile')
                ." AND (".$db->quoteName('access')."=".$db->quote(0)." OR ".$db->quoteName('access')."=".$db->quote(10).")"
                ." AND ".$db->quoteName('created')." >= DATE_SUB(CURDATE(), INTERVAL ".$inactiveDays." DAY)"
                ." LIMIT 0, ".$postLimit;

            $db->setQuery($query);
            $posts = $db->loadObjectList();

            //count the total posts
            $query = "SELECT count(id) FROM ".$db->quoteName('#__community_activities')
                ." WHERE "
                .$db->quoteName('app')."=".$db->quote('profile')
                ." AND (".$db->quoteName('access')."=".$db->quote(0)." OR ".$db->quoteName('access')."=".$db->quote(10).")"
                ." AND ".$db->quoteName('created')." >= DATE_SUB(CURDATE(), INTERVAL ".$inactiveDays." DAY)";

            $db->setQuery($query);
            $data['totalPosts'] = $db->loadResult();
        }

        //new images
        if($config->get('digest_email_include_photos')){
            $photoLimit = $config->get('digest_email_include_photos_count');
            $query = "SELECT p.* FROM ".$db->quoteName('#__community_photos')." as p"
                . " INNER JOIN ".$db->quoteName('#__community_photos_albums')." as a"
                . " ON p.albumid=a.id"
                ." WHERE 1"
                ." AND a.".$db->quoteName('groupid')."=".$db->quote('0')
                ." AND a.".$db->quoteName('eventid')."=".$db->quote('0')
                ." AND a.".$db->quoteName('permissions')." <= 10"
                ." AND p.".$db->quoteName('created')." >= DATE_SUB(CURDATE(), INTERVAL ".$inactiveDays." DAY)"
                ." LIMIT 0, ".$photoLimit;

            $db->setQuery($query);
            $photos = $db->loadObjectList();

            //count the total photos
            $query = "SELECT count(p.id) FROM ".$db->quoteName('#__community_photos')." as p"
                . " LEFT JOIN ".$db->quoteName('#__community_photos_albums')." as a"
                . " ON p.albumid=a.id"
                ." WHERE 1"
                ." AND a.".$db->quoteName('groupid')."=".$db->quote('0')
                ." AND a.".$db->quoteName('eventid')."=".$db->quote('0')
                ." AND a.".$db->quoteName('permissions')." <= 10"
                ." AND p.".$db->quoteName('created')." >= DATE_SUB(CURDATE(), INTERVAL ".$inactiveDays." DAY)";

            $db->setQuery($query);
            $data['totalPhotos'] = $db->loadResult();
        }

        //new videos
        if($config->get('digest_email_include_videos')){
            $videoLimit = $config->get('digest_email_include_videos_count');
            $query = "SELECT * FROM ".$db->quoteName('#__community_videos')
                ." WHERE 1"
                ." AND ".$db->quoteName('creator_type')."=".$db->quote('user')
                ." AND ".$db->quoteName('published')."=".$db->quote('1')
                ." AND ".$db->quoteName('permissions')." <= 10"
                ." AND ".$db->quoteName('created')." >= DATE_SUB(CURDATE(), INTERVAL ".$inactiveDays." DAY)"
                ." LIMIT 0, ".$videoLimit;

            $db->setQuery($query);
            $videos = $db->loadObjectList();

            //count the total videos
            $query = "SELECT count(id) FROM ".$db->quoteName('#__community_videos')
                ." WHERE 1"
                ." AND ".$db->quoteName('creator_type')."=".$db->quote('user')
                ." AND ".$db->quoteName('published')."=".$db->quote('1')
                ." AND ".$db->quoteName('permissions')." <= 10"
                ." AND ".$db->quoteName('created')." >= DATE_SUB(CURDATE(), INTERVAL ".$inactiveDays." DAY)";

            $db->setQuery($query);
            $data['totalVideos'] = $db->loadResult();
        }

        //groups updates
        if($config->get('digest_email_include_groups')){
            $newGroups = array();
            $groupPhotos = $groupVideos = $groupAnnouncements = $groupDiscussions = array();


            $groupLimit = $config->get('digest_email_include_groups_count');
            $query = "SELECT * FROM ".$db->quoteName('#__community_groups')
                ." WHERE 1"
                ." AND ".$db->quoteName('approvals')."=".$db->quote('0')
                ." AND ".$db->quoteName('published')."=".$db->quote('1')
                ." AND ".$db->quoteName('created')." >= DATE_SUB(CURDATE(), INTERVAL ".$inactiveDays." DAY)"
                ." LIMIT 0, ".$groupLimit;

            $db->setQuery($query);
            $newGroups = $db->loadObjectList();

            $query = "SELECT count(id) FROM ".$db->quoteName('#__community_groups')
                ." WHERE 1"
                ." AND ".$db->quoteName('approvals')."=".$db->quote('0')
                ." AND ".$db->quoteName('published')."=".$db->quote('1')
                ." AND ".$db->quoteName('created')." >= DATE_SUB(CURDATE(), INTERVAL ".$inactiveDays." DAY)";

            $db->setQuery($query);
            $data['totalGroups'] = $db->loadResult();

            //group photos
            if($config->get('digest_email_include_groups_photos')){
                $query = "SELECT * FROM ".$db->quoteName('#__community_photos')." as p"
                    . " LEFT JOIN ".$db->quoteName('#__community_photos_albums')." as a"
                    . " ON p.albumid=a.id"
                    ." WHERE 1"
                    ." AND a.".$db->quoteName('groupid')."<>".$db->quote('0')
                    ." AND a.".$db->quoteName('eventid')."=".$db->quote('0')
                    ." AND a.".$db->quoteName('permissions')." <= 10"
                    ." AND p.".$db->quoteName('created')." >= DATE_SUB(CURDATE(), INTERVAL ".$inactiveDays." DAY)"
                    ." LIMIT 0, ".$groupLimit;

                $db->setQuery($query);
                $groupPhotos = $db->loadObjectList();
            }


            //group videos
            if($config->get('digest_email_include_groups_videos')){
                $query = "SELECT * FROM ".$db->quoteName('#__community_videos')
                    ." WHERE 1"
                    ." AND ".$db->quoteName('creator_type')."=".$db->quote('group')
                    ." AND ".$db->quoteName('published')."=".$db->quote('1')
                    ." AND ".$db->quoteName('permissions')." <= 10"
                    ." AND ".$db->quoteName('created')." >= DATE_SUB(CURDATE(), INTERVAL ".$inactiveDays." DAY)"
                    ." LIMIT 0, ".$groupLimit;

                $db->setQuery($query);
                $groupVideos = $db->loadObjectList();
            }

            //group discussions
            if($config->get('digest_email_include_groups_discussions')){
                $query = "SELECT d.* FROM ".$db->quoteName('#__community_groups_discuss')." as d"
                    ." LEFT JOIN ".$db->quoteName('#__community_groups')." as g"
                    ." ON g.id=d.groupid"
                    ." WHERE 1"
                    ." AND g.".$db->quoteName('approvals')."=".$db->quote('0')
                    ." AND d.".$db->quoteName('lastreplied')." >= DATE_SUB(CURDATE(), INTERVAL ".$inactiveDays." DAY)"
                    ." LIMIT 0, ".$groupLimit;

                $db->setQuery($query);
                $groupDiscussions = $db->loadObjectList();


                //count total discussions
                $query = "SELECT count(d.id) FROM ".$db->quoteName('#__community_groups_discuss')." as d"
                    ." LEFT JOIN ".$db->quoteName('#__community_groups')." as g"
                    ." ON g.id=d.groupid"
                    ." WHERE 1"
                    ." AND g.".$db->quoteName('approvals')."=".$db->quote('0')
                    ." AND d.".$db->quoteName('lastreplied')." >= DATE_SUB(CURDATE(), INTERVAL ".$inactiveDays." DAY)";

                $db->setQuery($query);
                $data['totalDiscussions'] = $db->loadResult();
            }

            //group announcements
            if($config->get('digest_email_include_groups_announcements')) {
                $query = "SELECT b.* FROM " . $db->quoteName('#__community_groups_bulletins') . " as b"
                    . " LEFT JOIN " . $db->quoteName('#__community_groups') . " as g"
                    . " ON g.id=b.groupid"
                    . " WHERE 1"
                    . " AND g." . $db->quoteName('approvals') . "=" . $db->quote('0')
                    . " AND b." . $db->quoteName('date') . " >= DATE_SUB(CURDATE(), INTERVAL " . $inactiveDays . " DAY)"
                    . " LIMIT 0, " . $groupLimit;

                $db->setQuery($query);
                $groupAnnouncements = $db->loadObjectList();

                //count announcement
                $query = "SELECT count(b.id) FROM " . $db->quoteName('#__community_groups_bulletins') . " as b"
                    . " LEFT JOIN " . $db->quoteName('#__community_groups') . " as g"
                    . " ON g.id=b.groupid"
                    . " WHERE 1"
                    . " AND g." . $db->quoteName('approvals') . "=" . $db->quote('0')
                    . " AND b." . $db->quoteName('date') . " >= DATE_SUB(CURDATE(), INTERVAL " . $inactiveDays . " DAY)";

                $db->setQuery($query);
                $data['totalAnnouncements'] = $db->loadResult();
            }
        }


        //events update
        if($config->get('digest_email_include_events')){
            $newEvents = array();
            $eventPhotos = $geventVideos = array();


            $eventLimit = $config->get('digest_email_include_events_count');
            $query = "SELECT * FROM ".$db->quoteName('#__community_events')
                ." WHERE 1"
                ." AND ".$db->quoteName('type')."=".$db->quote('profile') //only normal event
                ." AND ".$db->quoteName('published')."=".$db->quote('1')
                ." AND ".$db->quoteName('created')." >= DATE_SUB(CURDATE(), INTERVAL ".$inactiveDays." DAY)"
                ." LIMIT 0, ".$eventLimit;

            $db->setQuery($query);
            $newEvents = $db->loadObjectList();

            $query = "SELECT count(id) FROM ".$db->quoteName('#__community_events')
                ." WHERE 1"
                ." AND ".$db->quoteName('type')."=".$db->quote('profile') //only normal event
                ." AND ".$db->quoteName('published')."=".$db->quote('1')
                ." AND ".$db->quoteName('created')." >= DATE_SUB(CURDATE(), INTERVAL ".$inactiveDays." DAY)";

            $db->setQuery($query);
            $data['totalEvents'] = $db->loadResult();

            //event photos
            if($config->get('digest_email_include_events_photos')){
                $query = "SELECT * FROM ".$db->quoteName('#__community_photos')." as p"
                    . " LEFT JOIN ".$db->quoteName('#__community_photos_albums')." as a"
                    . " ON p.albumid=a.id"
                    ." WHERE 1"
                    ." AND a.".$db->quoteName('groupid')."=".$db->quote('0')
                    ." AND a.".$db->quoteName('eventid')."<>".$db->quote('0')
                    ." AND a.".$db->quoteName('permissions')." <= 10"
                    ." AND p.".$db->quoteName('created')." >= DATE_SUB(CURDATE(), INTERVAL ".$inactiveDays." DAY)"
                    ." LIMIT 0, ".$eventLimit;

                $db->setQuery($query);
                $eventPhotos = $db->loadObjectList();
            }


            //event videos
            if($config->get('digest_email_include_events_videos')){
                $query = "SELECT * FROM ".$db->quoteName('#__community_videos')
                    ." WHERE 1"
                    ." AND ".$db->quoteName('creator_type')."=".$db->quote('event')
                    ." AND ".$db->quoteName('published')."=".$db->quote('1')
                    ." AND ".$db->quoteName('permissions')." <= 10"
                    ." AND ".$db->quoteName('created')." >= DATE_SUB(CURDATE(), INTERVAL ".$inactiveDays." DAY)"
                    ." LIMIT 0, ".$eventLimit;

                $db->setQuery($query);
                $eventVideos = $db->loadObjectList();
            }
        }


        // do not sent anything if there is no update at all
        if($data['totalPosts'] == 0 &&
        $data['totalPhotos'] == 0 &&
        $data['totalVideos'] == 0 &&
        $data['totalGroups'] == 0 &&
        $data['totalEvents'] == 0 &&
        $data['totalDiscussions'] == 0 &&
        $data['totalAnnouncements'] == 0){
            return true;
        }

        //posts
        if(isset($posts) && count($posts) > 0){
            foreach($posts as $post){
                $info = array();
                $user = CFactory::getUser($post->actor);

                $info['displayName'] = $user->getDisplayName();
                $info['message'] = CUserHelper::replaceAliasURL($post->title,false,true);
                $info['postlink'] = JURI::root().'index.php?option=com_community&view=profile&userid='.$user->id."&actid=".$post->id;
                $info['userthumb'] = $user->getThumbAvatar();

                $data['posts'][]=$info;
            }
        }

        //images
        if(isset($photos) && count($photos)>0){
            $photoTable  = JTable::getInstance( 'Photo' , 'CTable' );
            foreach($photos as $key => $photo){
                $photoTable->load($photo->id);
                //$photos[$key]->externalUrl = CStorage::getStorage($photo->storage)->getURI($photo->thumbnail);
                $photos[$key]->externalUrl = $photoTable->getThumbURI();
                $photos[$key]->link = JURI::root().'index.php?option=com_community&view=photos&task=photo&photoid='.$photo->id;
            }
            $data['photos'] = $photos;
        }

        //videos
        if(isset($videos) && count($videos)>0){
            $videoTable  = JTable::getInstance( 'Video' , 'CTable' );
            foreach($videos as $video){
                $videoTable->load($video->id);

                $info = array();
                $info['thumbnail'] = $videoTable->getThumbnail();
                $info['title'] = $videoTable->getTitle();
                $info['link'] = JURI::root().'index.php?option=com_community&view=videos&task=video&videoid='.$video->id;
                $info['desc'] = $videoTable->getDescription();
                $info['user'] = CFactory::getUser($videoTable->creator);
                $data['videos'][] = $info;
            }
        }

        //groups
        if(isset($newGroups) && count($newGroups) > 0){
            $groupTable  = JTable::getInstance( 'Group' , 'CTable' );
            foreach($newGroups as $group){
                $groupTable->load($group->id);
                $groupModel = CFactory::getModel('Groups');
                $approvedMembers = $groupModel->getMembers($group->id, false, true, false, true);
                $info = array();
                $info['total_members'] = count($approvedMembers);
                $info['title'] = $group->name;
                $info['cover'] = $groupTable->getCover();
                $info['link'] = JURI::root().'index.php?option=com_community&view=groups&task=viewgroup&groupid='.$group->id;
                $info['summary'] = $group->summary;
                $info['user'] = CFactory::getUser($groupTable->ownerid);
                $data['groups'][] = $info;
            }
        }

        //group discussions
        if(isset($groupDiscussions) && count($groupDiscussions) > 0){
            foreach($groupDiscussions as $discussion){
                $info = array();
                $info['title'] = htmlentities($discussion->title);
                //$info['cover'] = $groupTable->getCover();
                $info['link'] = JURI::root().'index.php?option=com_community&view=groups&task=viewdiscussion&topicid='.$discussion->id.'&groupid='.$discussion->groupid;
                $info['message'] = htmlentities(CUserHelper::replaceAliasURL($discussion->message,false,true));
                $info['user'] = CFactory::getUser($discussion->creator);
                $data['discussions'][] = $info;
            }
        }

        //group announcements
        if(isset($groupAnnouncements) && count($groupAnnouncements) > 0){
            foreach($groupAnnouncements as $announcement){
                $info = array();
                $info['title'] = htmlentities($announcement->title);
                //$info['cover'] = $groupTable->getCover();
                $info['link'] = JURI::root().'index.php?option=com_community&view=groups&task=viewbulletin&bulletinid='.$announcement->id.'&groupid='.$announcement->groupid;
                $info['message'] = htmlentities($announcement->message);
                $info['user'] = CFactory::getUser($announcement->created_by);
                $data['announcements'][] = $info;
            }
        }

        //events
        if(isset($newEvents) && count($newEvents) > 0){
            $eventTable  = JTable::getInstance( 'Event' , 'CTable' );
            foreach($newEvents as $event){
                $eventTable->load($event->id);
                $info = array();
                $info['total_members'] = $eventTable->getMembersCount(1);
                $info['title'] = $event->title;
                $info['cover'] = $eventTable->getCover();
                $info['link'] = JURI::root().'index.php?option=com_community&view=events&task=viewevent&eventid='.$event->id;
                $info['summary'] = $event->summary;
                $info['user'] = CFactory::getUser($eventTable->creator);
                $data['events'][] = $info;
            }
        }

        //time to send out the email
        $tmpl = new CTemplate();

        $mailfrom = JFactory::getConfig()->get('mailfrom');
        $fromname = JFactory::getConfig()->get('fromname');

        //lets send out 10 only per batch
        $totalSent = 0;
        foreach($inactiveUsers as $user){
            if($totalSent >= $config->get('digest_email_cron_email_run')){
                break;
            }
            $totalSent++;

            $userInfo = CFactory::getUser($user->id);
            $email = $user->email;
            $data['inactive_days'] = round((time()-strtotime($user->lastVisitDate))/60/60/24); // user inactive days
            $data['user_name'] = $userInfo->getDisplayName();
            $data['siteurl'] = CRoute::_(JUri::root().'index.php?option=com_community');
            $data['sitename'] = $config->get('sitename');

            $subject = JText::sprintf('COM_COMMUNITY_DIGEST_EMAIL_TITLE',$config->get('sitename'), $data['inactive_days']);
            $content =  $tmpl->set('data',$data)
                ->set('config',$config)
                ->fetch('email.digest');
            $message = html_entity_decode($content, ENT_QUOTES);
            $sendashtml = false;
            $copyrightemail = trim($config->get('copyrightemail'));
            $sendashtml = true;
            if ($config->get('htmlemail')) {

                $tmpl->set('name', $userInfo->getDisplayName());
                $tmpl->set('email', $user->email);

                $message = $tmpl->set(
                    'unsubscribeLink',
                    CRoute::_(JUri::root().'index.php?option=com_community&view=profile&task=email'),
                    false
                )
                    ->set('content', $message)
                    ->set('copyrightemail', $copyrightemail)
                    ->set('sitename', $config->get('sitename'))
                    ->set('recepientemail', $user->email)
                    ->fetch('email.html');
            }

            if($debug){
                echo $message;
                return;
            }elseif($ajax){
                return $message;
            }

            $mail = JFactory::getMailer();
            try{
                $mail->sendMail($mailfrom, $fromname, $email, $subject, $message, $sendashtml);
            }catch (Exception $e){
                return false;
            }

            //check if user exists
            $db = JFactory::getDbo();
            $db->setQuery("SELECT count(user_id) FROM ".$db->quoteName('#__community_digest_email')." WHERE ".$db->quoteName('user_id')."=".$db->quote($user->id));
            $userExists = $db->loadResult();

            if($userExists){
                //update will do
                $digestTable = JTable::getInstance( 'DigestEmail' , 'CommunityTable' );
                $digestTable->load($user->id);
                $jDate = JDate::getInstance();
                $timezone = new DateTimeZone(JFactory::getConfig()->get('offset'));
                $jDate->setTimezone($timezone);
                $digestTable->last_sent = $jDate->toSql(true);
                $digestTable->total_sent += 1;

                $digestTable->store();
            }else{
                //create a new entry
                $db->setQuery("INSERT INTO ".$db->quoteName('#__community_digest_email')
                    ."(".$db->quoteName('user_id').", ".$db->quoteName('total_sent').", ".$db->quoteName('last_sent')
                    .") VALUES (".$db->quote($user->id).",1,CURRENT_TIMESTAMP)");
                $db->execute();
            }

        }

        $this->_message[] = 'Digest email sent';
    }
}
