<?php
    /**
     * @copyright (C) 2013 iJoomla, Inc. - All rights reserved.
     * @license GNU General Public License, version 2 (http://www.gnu.org/licenses/gpl-2.0.html)
     * @author iJoomla.com <webmaster@ijoomla.com>
     * @url https://www.jomsocial.com/license-agreement
     * The PHP code portions are distributed under the GPL license. If not otherwise stated, all images, manuals, cascading style sheets, and included JavaScript *are NOT GPL, and are released under the IJOOMLA Proprietary Use License v1.0
     * More info at https://www.jomsocial.com/license-agreement
     */

// Disallow direct access to this file
    defined('_JEXEC') or die('Restricted access');

    jimport('joomla.application.component.controller');

    require_once(JPATH_ROOT . '/components/com_community/libraries/core.php');

    /**
     * JomSocial Component Controller
     */
    class CommunityControllerVideos extends CommunityController
    {
        public function __construct()
        {
            parent::__construct();

            $this->registerTask('publish', 'savePublish');
            $this->registerTask('unpublish', 'savePublish');
        }

        public function display($cachable = false, $urlparams = array())
        {
            $jinput = JFactory::getApplication()->input;

            $viewName = $jinput->get('view', 'community');

            // Set the default layout and view name
            $layout = $jinput->get('layout', 'default');

            // Get the document object
            $document = JFactory::getDocument();

            // Get the view type
            $viewType = $document->getType();

            // Get the view
            $view = $this->getView($viewName, $viewType);

            $model = $this->getModel($viewName, 'CommunityAdminModel');

            if ($model) {
                $view->setModel($model, $viewName);
            }

            // Set the layout
            $view->setLayout($layout);

            // Display the view
            $view->display();
        }

        public function ajaxTogglePublish($id, $type, $viewName = false)
        {
            $video = JTable::getInstance('Video', 'CTable');
            $video->load($id);

            return parent::ajaxTogglePublish($id, $type, 'videos');
        }

        public function ajaxFetchThumbnailMultiple($id){
            $ids = explode(',',$id);

            $success = 0;
            $failure = 0;
            $failureNotSupported = 0; // @since 4.0 to keep track of non supported video

            if(count($ids)){
                foreach ($ids as $id) {
                    try {
                        $this->_fetchThumbnail($id, false);
                        $success++;
                    } catch (Exception $e) {
                        if($e->getMessage() == JText::_('COM_COMMUNITY_VIDEOS_PROVIDER_NOT_SUPPORTED_ERROR')){
                            $failureNotSupported++;
                        }else{
                            $failure++;
                        }
                    }
                }
            }

            $contents  = '<div>';
            $contents .= '<div>'.JText::_('COM_COMMUNITY_SUCCESS').': ' . $success  .'</div>';
            $contents .= '<div>'.JText::_('COM_COMMUNITY_FAILURE').': ' . $failure  .'</div>';
            $contents .= '<div>'.JText::_('COM_COMMUNITY_FAILURE_VIDEO_NOT_SUPPORTED').': '.$failureNotSupported.'</div>';
            $contents .= '</div>';

            $response = new JAXResponse();
            $response->addScriptCall('cWindowAddContent', $contents );
            $response->addScriptCall('setTimeout(function() { window.location.reload(); }, 1500 );');

            return $response->sendResponse();
        }

        /**
         * This function will regenerate the thumbnail of videos
         * @param int $id
         * @param bool $returnThumb
         * @return bool
         */
        public function _fetchThumbnail($id = 0, $returnThumb = false)
        {
            if (!COwnerHelper::isRegisteredUser()) {
                return;
            }
            if (!$id) {
                return false;
            }


            $table = JTable::getInstance('Video', 'CTable');
            $table->load($id);


            $config = CFactory::getConfig();

            if ($table->type == 'file') {
                // We can only recreate the thumbnail for local video file only
                // it's not possible to process remote video file with ffmpeg
                if ($table->storage != 'file') {
                    JFactory::getApplication()->enqueueMessage(JText::_('COM_COMMUNITY_INVALID_FILE_REQUEST') . ': ' . 'FFmpeg cannot process remote video.', 'error');
                    return false;
                }

                $videoLib = new CVideoLibrary();

                $videoFullPath = JPATH::clean(JPATH_ROOT . '/' . $table->path);
                if (!JFile::exists($videoFullPath)) {
                    return false;
                }

                // Read duration
                $videoInfo = $videoLib->getVideoInfo($videoFullPath);

                if (!$videoInfo) {
                    return false;
                } else {
                    $videoFrame = CVideosHelper::formatDuration((int)($videoInfo['duration']['sec'] / 2), 'HH:MM:SS');

                    // Create thumbnail
                    $oldThumb = $table->thumb;
                    $thumbFolder = CVideoLibrary::getPath($table->creator, 'thumb');
                    $thumbSize = CVideoLibrary::thumbSize();
                    $thumbFilename = $videoLib->createVideoThumb($videoFullPath, $thumbFolder, $videoFrame, $thumbSize);
                }

                if (!$thumbFilename) {
                    return false;
                }
            } else {

                if (!CRemoteHelper::curlExists()) {
                    throw new Exception(JText::_('COM_COMMUNITY_CURL_NOT_EXISTS'));
                    return false;
                }

                $videoLib = new CVideoLibrary();
                $videoObj = $videoLib->getProvider($table->path);
                if ($videoObj == false) {
                    JFactory::getApplication()->enqueueMessage($videoObj->getError(), 'error');
                    return false;
                }

                try {
                    $videoObj->isValid();
                } catch (Exception $e) {
                    JFactory::getApplication()->enqueueMessage($videoObj->getError(), 'error');
                    throw $e;
                }


                $remoteThumb = $videoObj->getThumbnail();
                $thumbData = CRemoteHelper::getContent($remoteThumb, true);

                if (empty($thumbData)) {
                    JFactory::getApplication()->enqueueMessage(JText::_('COM_COMMUNITY_INVALID_FILE_REQUEST') . ': ' . $remoteThumb, 'error');
                    return false;
                }

                // split the header and body
                list($headers, $body) = explode("\r\n\r\n", $thumbData, 2);
                preg_match('/Content-Type: image\/(.*)/i', $headers, $matches);

                if (!empty($matches)) {
                    $thumbPath = CVideoLibrary::getPath($table->creator, 'thumb');
                    $thumbFileName = CFileHelper::getRandomFilename($thumbPath);
                    $tmpThumbPath = $thumbPath . '/' . $thumbFileName;
                    if (!JFile::write($tmpThumbPath, $body)) {
                        JFactory::getApplication()->enqueueMessage(JText::_('COM_COMMUNITY_INVALID_FILE_REQUEST') . ': ' . $thumbFileName, 'error');
                        return false;
                    }

                    // We'll remove the old or none working thumbnail after this
                    $oldThumb = $table->thumb;

                    // Get the image type first so we can determine what extensions to use
                    $info = getimagesize($tmpThumbPath);
                    $mime = image_type_to_mime_type($info[2]);
                    $thumbExtension = CImageHelper::getExtension($mime);

                    $thumbFilename = $thumbFileName . $thumbExtension;
                    $thumbPath = $thumbPath . '/' . $thumbFilename;
                    if (!JFile::move($tmpThumbPath, $thumbPath)) {
                        throw new Exception(JText::_('WARNFS_ERR02') . ': ' . $thumbFileName);
                        return false;
                    }

                    // Resize the thumbnails
                    //CImageHelper::resizeProportional( $thumbPath , $thumbPath , $mime , CVideoLibrary::thumbSize('width') , CVideoLibrary::thumbSize('height') );

                    list($width, $height) = explode('x', $config->get('videosThumbSize'));
                    CImageHelper::resizeAspectRatio($thumbPath, $thumbPath, $width, $height);
                } else {
                    JFactory::getApplication()->enqueueMessage(JText::_('COM_COMMUNITY_PHOTOS_IMAGE_NOT_PROVIDED_ERROR'), 'error');

                    return false;
                }
            }

            // Update the DB with new thumbnail
            $thumb = $config->get('videofolder') . '/'
                . VIDEO_FOLDER_NAME . '/'
                . $table->creator . '/'
                . VIDEO_THUMB_FOLDER_NAME . '/'
                . $thumbFilename;

            $table->set('thumb', $thumb);
            $table->store();

            // If this video storage is not on local, we move it to remote storage
            // and remove the old thumb if existed
            if (($table->storage != 'file')) { // && ($table->storage == $storageType))
                $config = CFactory::getConfig();
                $storageType = $config->getString('videostorage');

                $storage = CStorage::getStorage($storageType);
                $storage->delete($oldThumb);

                $localThumb = JPATH::clean(JPATH_ROOT . '/' . $table->thumb);
                $tempThumbname = JPATH::clean(JPATH_ROOT . '/' . md5($table->thumb));
                if (JFile::exists($localThumb)) {
                    JFile::copy($localThumb, $tempThumbname);
                }
                if (JFile::exists($tempThumbname)) {
                    $storage->put($table->thumb, $tempThumbname);
                    JFile::delete($localThumb);
                    JFile::delete($tempThumbname);
                }
            } else {
                if (JFile::exists(JPATH_ROOT . '/' . $oldThumb)) {
                    JFile::delete(JPATH_ROOT . '/' . $oldThumb);
                }
            }


            if ($returnThumb) {
                return $table->getThumbnail();
            }
            return true;
        }

        public function ajaxEditVideo($id)
        {
            $response = new JAXResponse();

            $model = $this->getModel('videoscategories');
            $config = CFactory::getConfig();

            $categories = $model->getCategories();
            $video = JTable::getInstance('Video', 'CTable');

            $video->load($id);

            $video->title = CStringHelper::escape($video->title);
            $video->description = CStringHelper::escape($video->description);

            ob_start();
            ?>
            <form name="editvideo" action="" method="post" id="editvideo">
            <div
                style="background-color: #F9F9F9; border: 1px solid #D5D5D5; margin-bottom: 10px; padding: 5px;font-weight: bold;">
                <?php echo JText::_('Edit Video Detail'); ?>
            </div>
            <table cellspacing="0" class="admintable" border="0" width="100%">
                <tbody>
                <tr>
                    <td class="key" valign="top"><?php echo JText::_('COM_COMMUNITY_TITLE'); ?></td>
                    <td><input type="text" id="title" name="title" class="input text"
                               value="<?php echo $video->title; ?>" style="width: 90%;" maxlength="255"/></tD>
                </tr>
                <tr>
                    <td class="key"><?php echo JText::_('COM_COMMUNITY_DESCRIPTION'); ?></td>
                    <td><textarea name="description" style="width: 90%;" rows="8"
                                  id="description"><?php echo $video->description; ?></textarea></td>
                </tr>
                <tr>
                    <td class="key"><?php echo JText::_('COM_COMMUNITY_CATEGORY'); ?></td>
                    <td>
                        <select name="category_id">
                            <?php
                                for ($i = 0; $i < count($categories); $i++) {
                                    $selected = ($video->category_id == $categories[$i]->id) ? ' selected="selected"' : '';
                                    ?>
                                    <option
                                        value="<?php echo $categories[$i]->id; ?>"<?php echo $selected; ?>><?php echo $categories[$i]->name; ?></option>
                                <?php
                                }
                            ?>
                        </select>
                    </td>
                </tr>
                <?php if ($config->get('videosmapdefault')) { ?>
                    <tr>
                        <td class="key"><?php echo JText::_('COM_COMMUNITY_CONFIGURATION_LOCATION'); ?></td>
                        <td><input type="text" id="title" name="location" class="input text"
                                   value="<?php echo $video->location; ?>" style="width: 90%;"/></td>
                    </tr>
                <?php } ?>
                <tr>
                    <td class="key"><?php echo JText::_('COM_COMMUNITY_VIDEOS_WHO_CAN_SEE'); ?></td>
                    <td><?php echo CPrivacy::getHTML(
                            'permissions',
                            $video->permissions,
                            COMMUNITY_PRIVACY_BUTTON_LARGE,
                            null,
                            'select'
                        ); ?></td>
                </tr>
                </tbody>
            </table>
            <input type="hidden" name="id" value="<?php echo $video->id; ?>"/>
            <input type="hidden" name="option" value="com_community"/>
            <input type="hidden" name="task" value="savevideos"/>
            <input type="hidden" name="view" value="videos"/>
            <?php

            $contents = ob_get_contents();
            ob_end_clean();

            $response->addAssign('cWindowContent', 'innerHTML', $contents);

            $action = '<input type="button" class="btn btn-small btn-info pull-right" onclick="azcommunity.saveVideo();" name="' . JText::_(
                    'COM_COMMUNITY_SAVE'
                ) . '" value="' . JText::_('COM_COMMUNITY_SAVE') . '" />';
            $action .= '&nbsp;<input type="button" class="btn btn-small pull-left" onclick="cWindowHide();" name="' . JText::_(
                    'COM_COMMUNITY_CLOSE'
                ) . '" value="' . JText::_('COM_COMMUNITY_CLOSE') . '" />';
            $response->addScriptCall('cWindowActions', $action);

            return $response->sendResponse();
        }

        public function saveVideos()
        {
            $jinput     = JFactory::getApplication()->input;

            $video = JTable::getInstance('Videos', 'CommunityTable');
            $id = $jinput->post->get('id', '');

            if (empty($id)) {
                JFactory::getApplication()->enqueueMessage(JText::_('COM_COMMUNITY_INVALID_ID'), 'error');;
            }

            $postData = $jinput->post->getArray();

            $video->load($id);
            $video->bind($postData);

            $message = '';
            if ($video->store()) {
                $message = JText::_('COM_COMMUNITY_VIDEO_SUCCESSFULLY_SAVED');
            } else {
                $message = JText::_('COM_COMMUNITY_VIDEO_ERROR_WHILE_SAVING');
            }

            $mainframe = JFactory::getApplication();
            $mainframe->redirect('index.php?option=com_community&view=videos', $message, 'message');
        }

        public function ajaxviewVideo($id)
        {
            $response = new JAXResponse();

            $video = JTable::getInstance('Videos', 'CommunityTable');
            $video->load($id);

            $notiHtml = '<div class="cVideo-Player video-player text-center">
							' . $video->getPlayerHTML('560px', '400px') . '
						</div>';

            $response->addScriptCall('cWindowAddContent', $notiHtml);

            //$response->addAssign('cWindowContent', 'innerHTML', $contents);

            return $response->sendResponse();
        }

        public function delete()
        {
            $mainframe = JFactory::getApplication();
            $jinput = $mainframe->input;
            $model = $this->getModel('Videos', 'CommunityAdminModel');

            $id = $jinput->post->get('cid', '', 'array');
            $errors = false;
            $message = JText::_('Videos has ben deleted');

            if (empty($id)) {
                JFactory::getApplication()->enqueueMessage(JText::_('COM_COMMUNITY_INVALID_ID'), 'error');;
            }

            for ($i = 0; $i < count($id); $i++) {
                if (!$model->delete($id[$i])) {
                    $errors = true;
                } else {
                    // delete the stream
                    CActivityStream::remove('videos', $id[$i]);
                    CActivityStream::remove('videos.linking', $id[$i]);
                }
            }

            if ($errors) {
                $message = JText::_('Error deleting video');
            }
            $mainframe->redirect('index.php?option=com_community&view=videos', $message, 'message');
        }
    }
