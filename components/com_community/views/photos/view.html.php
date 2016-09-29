<?php
/**
 * @copyright (C) 2013 iJoomla, Inc. - All rights reserved.
 * @license GNU General Public License, version 2 (http://www.gnu.org/licenses/gpl-2.0.html)
 * @author iJoomla.com <webmaster@ijoomla.com>
 * @url https://www.jomsocial.com/license-agreement
 * The PHP code portions are distributed under the GPL license. If not otherwise stated, all images, manuals, cascading style sheets, and included JavaScript *are NOT GPL, and are released under the IJOOMLA Proprietary Use License v1.0
 * More info at https://www.jomsocial.com/license-agreement
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

if (!class_exists('CommunityViewPhotos')) {

    class CommunityViewPhotos extends CommunityView {

        public function regen() {
            /**
             * Opengraph
             */
            CHeadHelper::setType('website', JText::_('Running the Utility'));

            $tmpl = new CTemplate();

            echo $tmpl->fetch('photos.regen');
        }

        public function _addSubmenu() {
            $handler = $this->_getHandler();
            $handler->setSubmenus();
        }

        public function _flashuploader() {
            $jinput = JFactory::getApplication()->input;
            $groupId = $jinput->request->getInt('groupid', '');
            $model = CFactory::getModel('photos');

            // Since upload will always be the browser's photos, use the browsers id
            $my = CFactory::getUser();

            // Maintenance mode, clear out tokens that are older than 1 hours
            $model->cleanUpTokens();
            $token = $model->getUserUploadToken($my->id);

            // We need to generate our own session management since there
            // are some bridges causes the flash browser to not really work.
            if (!$token && $my->id != 0) {
                // Get the current browsers session object.
                $mySession = JFactory::getSession();

                // Generate a session handler for this user.
                $myToken = $mySession->getToken(true);

                $date = JDate::getInstance();
                $token = new stdClass();
                $token->userid = $my->id;
                $token->datetime = $date->toSql();
                $token->token = $myToken;

                $model->addUserUploadSession($token);
            }

            $mainframe = JFactory::getApplication();
            $jinput = $mainframe->input;
            $config = CFactory::getConfig();
            $albumId = $jinput->request->get('albumid', '', 'INT');
            $handler = $this->_getHandler();
            $uploadURI = $handler->getFlashUploadURI($token, $albumId);

            $albums = '';
            $createAlbumLink = '';
            $photoUploaded = '';
            $photoUploadLimit = '';
            $viewAlbumLink = '';

            if (!empty($groupId)) {
                //CFactory::load( 'models' , 'groups' );
                $group = JTable::getInstance('Group', 'CTable');
                $group->load($groupId);
                $albums = $model->getGroupAlbums($groupId, false, false, '', ( $group->isAdmin($my->id) || COwnerHelper::isCommunityAdmin()));
                $createAlbumLink = CRoute::_('index.php?option=com_community&view=photos&task=newalbum&groupid=' . $groupId);
                $photoUploaded = $model->getPhotosCount($groupId, PHOTOS_GROUP_TYPE);
                $photoUploadLimit = $config->get('groupphotouploadlimit');
                $viewAlbumLink = CRoute::_('index.php?option=com_community&view=photos&task=album&groupid=' . $groupId . '&albumid=' . $albumId);
            } else {
                $albums = $model->getAlbums($my->id);
                $createAlbumLink = CRoute::_('index.php?option=com_community&view=photos&task=newalbum&userid=' . $my->id);
                $photoUploaded = $model->getPhotosCount($my->id, PHOTOS_USER_TYPE);
                $photoUploadLimit = $config->get('photouploadlimit');
                $viewAlbumLink = CRoute::_('index.php?option=com_community&view=photos&task=album&userid=' . $my->id . '&albumid=' . $albumId);
            }

            $tmpl = new CTemplate();

            echo $tmpl->set('createAlbumLink', $createAlbumLink)
                ->set('albums', $albums)
                ->set('uploadURI', $uploadURI)
                ->set('albumId', $albumId)
                ->set('uploadLimit', $config->get('maxuploadsize'))
                ->set('photoUploaded', $photoUploaded)
                ->set('viewAlbumLink', $viewAlbumLink)
                ->set('photoUploadLimit', $photoUploadLimit)
                ->fetch('photos.flashuploader');
        }

        /**
         * Display the multi upload form
         * */
        public function _htmluploader() {
            $jinput = JFactory::getApplication()->input;
            $groupId = $jinput->getInt('groupid', '');
            $model = CFactory::getModel('photos');
            $my = CFactory::getUser();
            $config = CFactory::getConfig();
            $albumId = $jinput->request->getInt('albumid', '');

            if (!empty($groupId)) {
                $group = JTable::getInstance('Group', 'CTable');
                $group->load($groupId);
                $albums = $model->getGroupAlbums($groupId, false, false, '', ( $group->isAdmin($my->id) || COwnerHelper::isCommunityAdmin()));
                $createAlbumLink = CRoute::_('index.php?option=com_community&view=photos&task=newalbum&groupid=' . $groupId);
                $photoUploaded = $model->getPhotosCount($groupId, PHOTOS_GROUP_TYPE);
                $photoUploadLimit = $config->get('groupphotouploadlimit');
                $viewAlbumLink = CRoute::_('index.php?option=com_community&view=photos&task=album&groupid=' . $groupId . '&albumid=' . $albumId);
            } else {
                $albums = $model->getAlbums($my->id);
                if (empty($albumId) && !empty($albums) && !empty($albums[0]->id)) {
                    $albumId = $albums[0]->id;
                }
                $createAlbumLink = CRoute::_('index.php?option=com_community&view=photos&task=newalbum&userid=' . $my->id);
                $photoUploaded = $model->getPhotosCount($my->id, PHOTOS_USER_TYPE);
                $photoUploadLimit = $config->get('photouploadlimit');
                $viewAlbumLink = CRoute::_('index.php?option=com_community&view=photos&task=album&userid=' . $my->id . '&albumid=' . $albumId);
            }

            // Attach the photo upload css.
            // CTemplate::addStylesheet( 'photouploader' );

            $tmpl = new CTemplate();
            echo $tmpl->set('createAlbumLink', $createAlbumLink)
                ->set('albums', $albums)
                ->set('my', CFactory::getUser())
                ->set('albumId', $albumId)
                ->set('photoUploaded', $photoUploaded)
                ->set('viewAlbumLink', $viewAlbumLink)
                ->set('photoUploadLimit', $photoUploadLimit)
                ->set('uploadLimit', $config->get('maxuploadsize'))
                ->set('submenu', $this->showSubmenu(false))
                ->fetch('photos.htmluploader');
        }

        public function showSubmenu($display=true) {
            $this->_addSubmenu();
            return parent::showSubmenu($display);
        }

        public function group(){
            $this->display(null,'group');
        }

        public function event(){
            $this->display(null,'event');
        }


        /**
         * Default view method
         * Display all photos in the whole system
         * @param null $tpl
         * @param string $albumType either to show group,event or profile
         */
        public function display($tpl = null, $albumType = 'special') {
            $document = JFactory::getDocument();
            $my = CFactory::getUser();

            $mainframe = JFactory::getApplication();
            $jinput = $mainframe->input;

            // Set pathway for group photos
            // Community > Groups > Group Name > Photos
            $groupId = $jinput->get->get('groupid', '');
            $sortBy = $jinput->getString('sort', 'date');

            //event
            $eventId = $jinput->get->get('eventid', '');

            if (!empty($groupId)) {
                $group = JTable::getInstance('Group', 'CTable');
                $group->load($groupId);

                // @rule: Test if the group is unpublished, don't display it at all.
                if (!$group->published) {
                    $this->_redirectUnpublishGroup();
                    return;
                }

                $pathway = $mainframe->getPathway();
                $pathway->addItem(JText::_('COM_COMMUNITY_GROUPS'), CRoute::_('index.php?option=com_community&view=groups'));
                $pathway->addItem($group->name, CRoute::_('index.php?option=com_community&view=groups&task=viewgroup&groupid=' . $groupId));

                /**
                 * Opengraph
                 */
                CHeadHelper::setType('website', JText::sprintf('COM_COMMUNITY_GROUPS_PHOTO_LISTING',$group->name));
            } else {
                /**
                 * Opengraph
                 */
                CHeadHelper::setType('website', JText::_('COM_COMMUNITY_PHOTOS_ALL_PHOTOS_TITLE'));
            }

            $this->addPathway(JText::_('COM_COMMUNITY_PHOTOS_ALL_PHOTOS_TITLE'));

            $model = CFactory::getModel('photos');
            $limitstart = $jinput->getInt('limitstart', 0);

            if($eventId || $albumType == PHOTOS_EVENT_TYPE){
                $type = PHOTOS_EVENT_TYPE;
            }elseif($groupId || $albumType == PHOTOS_GROUP_TYPE){
                $type = PHOTOS_GROUP_TYPE;
            }else{
                $type = PHOTOS_USER_TYPE;
            }

            $handler = $this->_getHandler($type);
            $handler->setMiniHeader();

            $groupLink = !empty($groupId) ? '&groupid=' . $groupId : '';
            $feedLink = CRoute::_('index.php?option=com_community&view=photos' . $groupLink . '&format=feed');
            $feed = '<link rel="alternate" type="application/rss+xml" title="' . JText::_('COM_COMMUNITY_PHOTOS_ALL_PHOTOS_FEED') . '" href="' . $feedLink . '"/>';
            $document->addCustomTag($feed);

            $albumsData = $handler->getAllAlbumData($sortBy, $albumType);

            if ($albumsData === FALSE) {
                return;
            }

            $albumList = array();

            foreach ($albumsData['data'] as $album) {
                if($album->type == "group"){
                    $group = JTable::getInstance('Group', 'CTable');
                    $group->load($album->groupid);

                    if($group->approvals == 1 ){
                        if($group->isMember($my->id))
                            $albumList[] = $album;
                    } else {
                        $albumList[] = $album;
                    }
                } else {
                    $albumList[] = $album;
                }
            }

            $albumHTML = $this->_getAllAlbumsHTML($albumList, $type, $albumsData['pagination']);

            $featuredList = array();
            if (empty($groupId)) {
                $featured = new CFeatured(FEATURED_ALBUMS);
                $featuredAlbums = $featured->getItemIds();
                foreach ($featuredAlbums as $album) {
                    $table = JTable::getInstance('Album', 'CTable');
                    $table->load($album);

                    $table->thumbnail = $table->getCoverThumbPath();
                    $table->thumbnail = ($table->thumbnail) ? JURI::root(true) ."/". $table->thumbnail : JURI::root(true) . '/components/com_community/assets/album_thumb.jpg';
                    $featuredList[] = $table;
                }
            }

            $tmpl = new CTemplate();

            echo $tmpl->set('albumsHTML', $albumHTML)
                ->set('groupId', $groupId)
                ->fetch('photos.index');
        }

        /*
         * @since 2.4
         */

        public function modFeaturedAlbum() {
            return $this->_getFeaturedAlbum();
        }

        /*
         * @since 2.4
         */

        private function _getFeaturedAlbum() {
            $featuredList = array();
            $config = CFactory::getConfig();

            $featured = new CFeatured(FEATURED_ALBUMS);
            $featuredAlbums = $featured->getItemIds();

            foreach ($featuredAlbums as $album) {
                $table = JTable::getInstance('Album', 'CTable');
                $table->load($album);

                //exclude Albums that has Stricter permissions (non-public)
                if ($table->permissions > 0)
                    continue;

                if (empty($table->id))
                    continue;

                $table->thumbnail = $table->getCoverThumbPath();

                if ($table->location != '') {
                    $zoomableMap = CMapping::drawZoomableMap($table->location, 220, 150);
                } else {
                    $zoomableMap = "";
                }

                $table->zoomableMap = $zoomableMap;

                $featuredList[] = $table;
            }


            $tmpl = new CTemplate();
            $photoTag = CFactory::getModel('phototagging');

            //add photos info in featured list
            $photoModel = CFactory::getModel('photos');
            if (is_array($featuredList)) {
                foreach ($featuredList as &$fl) {
                    // bind photo links
                    $photos = $photoModel->getPhotos($fl->id, 5, 0);
                    $maxTime = '';

                    $tagRecords = array();
                    // Get all photos from album

                    if (count($photos)) {
                        for ($i = 0; $i < count($photos); $i++) {
                            $item = JTable::getInstance('Photo', 'CTable');
                            $item->bind($photos[$i]);
                            $photos[$i] = $item;

                            $photo = $photos[$i];
                            $photo->link = CRoute::_('index.php?option=com_community&view=photos&task=photo&userid=' . $fl->creator . '&albumid=' . $photo->albumid) . '&photoid=' . $photo->id;

                            //Get last update
                            $maxTime = ($photo->created > $maxTime) ? $photo->created : $maxTime;
                        }
                    }

                    //bind album desc
                    if (!$maxTime) {
                        $maxTime = $fl->created;
                    }

                    $maxTime = new JDate($maxTime);
                    $fl->lastUpdated = CActivityStream::_createdLapse($maxTime, false);
                    $fl->photos = $photos;
                    $fl->commentCount = CWallLibrary::getWallCount('albums', $fl->id);
                }
            }
            //try to get the photos within this album
            // Get show photo location map by default
            $photoMapsDefault = $config->get('photosmapdefault');

            return $tmpl->set('isCommunityAdmin', COwnerHelper::isCommunityAdmin())
                ->set('showFeatured', $config->get('show_featured'))
                ->set('featuredList', $featuredList)
                ->set('photoModel', $photoModel)
                ->set('photoMapsDefault', $photoMapsDefault)
                ->fetch('photos.album.featured');
        }

        public function myphotos() {
            $my = CFactory::getUser();
            $document = JFactory::getDocument();
            $jinput = JFactory::getApplication()->input;

            $userid = $jinput->getInt('userid', $my->id);
            $sortBy = $jinput->getString('sort', 'date');

            if ($userid) {
                $user = CFactory::getUser($userid);
            } else {
                $user = CFactory::getUser();
            }

            // set bread crumbs
            if ($userid == $my->id) {
                $this->addPathway(JText::_('COM_COMMUNITY_PHOTOS'), CRoute::_('index.php?option=com_community&view=photos'));
                $this->addPathway(JText::_('COM_COMMUNITY_PHOTOS_MY_PHOTOS_TITLE'));
            } else {
                $this->addPathway(JText::_('COM_COMMUNITY_PHOTOS'), CRoute::_('index.php?option=com_community&view=photos'));
                $this->addPathway(JText::sprintf('COM_COMMUNITY_PHOTOS_USER_PHOTOS_TITLE', $user->getDisplayName()), CRoute::_('index.php?option=com_community&view=photos&task=myphotos&userid=' . $userid));
            }

            $blocked = $user->isBlocked();

            if ($blocked && !COwnerHelper::isCommunityAdmin()) {
                $tmpl = new CTemplate();
                echo $tmpl->fetch('profile.blocked');
                return;
            }

            if ($my->id == $user->id) {
                /**
                 * Opengraph
                 */
                CHeadHelper::setType('website', JText::_('COM_COMMUNITY_PHOTOS_MY_PHOTOS_TITLE'));
            } else {
                /**
                 * Opengraph
                 */
                CHeadHelper::setType('website', JText::sprintf('COM_COMMUNITY_PHOTOS_USER_PHOTOS_TITLE', $user->getDisplayName()));
            }

            $this->attachMiniHeaderUser($user->id);

            $feedLink = CRoute::_('index.php?option=com_community&view=photos&task=myphotos&userid=' . $user->id . '&format=feed');
            $feed = '<link rel="alternate" type="application/rss+xml" title="' . JText::_('COM_COMMUNITY_SUBSCRIBE_MY_PHOTOS_FEED') . '" href="' . $feedLink . '"/>';
            $document->addCustomTag($feed);

            $model = CFactory::getModel('photos');

            $albums = $model->getProfileAlbums($user->id, true, true, $sortBy, array('group','event'));

            $tmpl = new CTemplate();

            echo $tmpl->set('albumsHTML', $this->_getAllAlbumsHTML($albums, PHOTOS_USER_TYPE, $model->getPagination(),true))
                ->fetch('photos.myphotos');
        }

        public function _getAllAlbumsHTML($albums, $type = PHOTOS_USER_TYPE, $pagination = NULL, $isMyOwnPhoto = false) {
            $mainframe = JFactory::getApplication();
            $jinput    = $mainframe->input;
            $task = $jinput->getCmd('task');
            $my = CFactory::getUser();
            $config = CFactory::getConfig();
            $groupId = $jinput->getInt('groupid', 'int');
            $eventId = $jinput->get('eventid',0,'int');
            $handler = $this->_getHandler();

            $tmpl = new CTemplate();

            // Use for redirect after editAlbum
            $displaygrp = ($groupId == 0) ? 'display' : 'displaygrp';

            $photoModel = CFactory::getModel('Photos');

            for ($i = 0; $i < count($albums); $i++) {
                $albums[$i]->count = count($photoModel->getPhotos($albums[$i]->id,100000,0));
                //change the album name according to the latest name of profile, event or groups avatars
                // act as trigger, have to move this somewhere later. //change album name
                switch($albums[$i]->type){
                    case 'group.avatar':
                        $table = JTable::getInstance('Group', 'CTable');
                        $table->load($albums[$i]->groupid);
                        $tmpName = JText::sprintf('COM_COMMUNITY_GROUP_AVATAR_NAME', ucfirst($table->name));
                        if($albums[$i]->name != $tmpName){
                            $photoModel->updateAlbumName($albums[$i]->id, $tmpName);
                            $albums[$i]->name = $tmpName;
                        }
                        break;
                    case 'event.avatar':
                        $table = JTable::getInstance('Event', 'CTable');
                        $table->load($albums[$i]->eventid);
                        $tmpName = JText::sprintf('COM_COMMUNITY_EVENT_AVATAR_NAME', ucfirst($table->title));
                        if($albums[$i]->name != $tmpName){
                            $photoModel->updateAlbumName($albums[$i]->id, $tmpName);
                            $albums[$i]->name = $tmpName;
                        }
                        break;
                    case 'profile.avatar':
                        $creator = CFactory::getUser($albums[$i]->creator);
                        $tmpName = JText::sprintf('COM_COMMUNITY_PROFILE_AVATAR_NAME', ucfirst($creator->getDisplayName()));

                        if($albums[$i]->name != $tmpName){
                           $photoModel->updateAlbumName($albums[$i]->id, $tmpName);
                            $albums[$i]->name = $tmpName;
                        }
                        break;
                    case 'user' :
                        //default album only
                        if($albums[$i]->default == 1){
                            $creator = CFactory::getUser($albums[$i]->creator);
                            $tmpName = JText::sprintf('COM_COMMUNITY_PROFILE_DEFAULT_ALBUM_NAME', ucfirst($creator->getDisplayName()));

                            if($albums[$i]->name != $tmpName){
                                $photoModel->updateAlbumName($albums[$i]->id, $tmpName);
                                $albums[$i]->name = $tmpName;
                            }

                            // reset the default album to public, always.
                            if($albums[$i]->permission != COMMUNITY_STATUS_PRIVACY_PUBLIC){
                                $photoModel->updateAlbumPermission($albums[$i]->id); // reset to default
                            }
                        }
                        break;
                    case 'profile.Cover':
                        $creator = CFactory::getUser($albums[$i]->creator);
                        $tmpName = JText::sprintf('COM_COMMUNITY_PROFILE_COVER_NAME');

                        if($albums[$i]->name != $tmpName){
                            $photoModel->updateAlbumName($albums[$i]->id, $tmpName);
                            $albums[$i]->name = $tmpName;
                        }
                        break;
                    case 'event.Cover':
                        $table = JTable::getInstance('Event', 'CTable');
                        $table->load($albums[$i]->eventid);
                        $tmpName = JText::sprintf('COM_COMMUNITY_EVENT_COVER_NAME', ucfirst($table->title));
                        if($albums[$i]->name != $tmpName){
                            $photoModel->updateAlbumName($albums[$i]->id, $tmpName);
                            $albums[$i]->name = $tmpName;
                        }
                        break;
                    case 'group.Cover':
                        $table = JTable::getInstance('Group', 'CTable');
                        $table->load($albums[$i]->groupid);
                        $tmpName = JText::sprintf('COM_COMMUNITY_GROUP_COVER_NAME', ucfirst($table->name));
                        if($albums[$i]->name != $tmpName){
                            $photoModel->updateAlbumName($albums[$i]->id, $tmpName);
                            $albums[$i]->name = $tmpName;
                        }
                        break;
                    case 'group':
                        //group default photo
                        if($albums[$i]->default == 1){
                            $table = JTable::getInstance('Group', 'CTable');
                            $table->load($albums[$i]->groupid);
                            $tmpName = JText::sprintf('COM_COMMUNITY_GROUP_DEFAULT_ALBUM_NAME', ucfirst($table->name));

                            if($albums[$i]->name != $tmpName){
                                $photoModel->updateAlbumName($albums[$i]->id, $tmpName);
                                $albums[$i]->name = $tmpName;
                            }
                        }
                }


                $albums[$i]->user = CFactory::getUser($albums[$i]->creator);

                $albums[$i]->totalComments = CWallLibrary::getWallCount('albums', $albums[$i]->id);


                //$albums[$i]->totalPhotos = $photosModel->getTotalPhotos($albums[$i]->id);

                if ($type == PHOTOS_GROUP_TYPE) {
                    $albums[$i]->link = CRoute::_("index.php?option=com_community&view=photos&task=album&albumid={$albums[$i]->id}&groupid={$albums[$i]->groupid}");
                    $albums[$i]->editLink = CRoute::_("index.php?option=com_community&view=photos&task=editAlbum&albumid={$albums[$i]->id}&groupid={$albums[$i]->groupid}&referrer={$displaygrp}");
                    $albums[$i]->uploadLink = "javascript:joms.notifications.showUploadPhoto({$albums[$i]->id},{$albums[$i]->groupid});"; //CRoute::_("index.php?option=com_community&view=photos&task=uploader&albumid={$albums[$i]->id}&groupid={$albums[$i]->groupid}");
                    $albums[$i]->isOwner = $my->authorise('community.view', 'photos.group.album.' . $groupId, $albums[$i]);
                }else{
                    $albums[$i]->link = CRoute::_("index.php?option=com_community&view=photos&task=album&albumid={$albums[$i]->id}");
                    $albums[$i]->editLink = CRoute::_("index.php?option=com_community&view=photos&task=editAlbum&albumid={$albums[$i]->id}&userid={$albums[$i]->creator}&referrer=myphotos");
                    $albums[$i]->uploadLink = "javascript:joms.notifications.showUploadPhoto({$albums[$i]->id});"; //CRoute::_("index.php?option=com_community&view=photos&task=uploader&albumid={$albums[$i]->id}&userid={$albums[$i]->creator}");
                    $albums[$i]->isOwner = ($my->id == $albums[$i]->creator);
                }



                // If new albums that has just been created and
                // does not contain any images, the lastupdated will always be 0000-00-00 00:00:00:00
                // Try to use the albums creation date instead.
                if ($albums[$i]->lastupdated == '0000-00-00 00:00:00' || $albums[$i]->lastupdated == '') {
                    $albums[$i]->lastupdated = $albums[$i]->created;

                    if ($albums[$i]->lastupdated == '' || $albums[$i]->lastupdated == '0000-00-00 00:00:00') {
                        $albums[$i]->lastupdated = JText::_('COM_COMMUNITY_PHOTOS_NO_ACTIVITY');
                    } else {
                        $lastUpdated = new JDate($albums[$i]->lastupdated);
                        $albums[$i]->lastupdated = CActivityStream::_createdLapse($lastUpdated, false);
                    }
                } else {
                    $params = new CParameter($albums[$i]->params);
                    $lastUpdated = new JDate($params->get('lastupdated'));
                    $albums[$i]->lastupdated = CActivityStream::_createdLapse($lastUpdated, false);
                }
            }

            $featured = new CFeatured(FEATURED_ALBUMS);
            $featuredList = $featured->getItemIds();

            $createLink = $handler->getAlbumCreateLink();

            if ($type == PHOTOS_GROUP_TYPE) {
                $isOwner = CGroupHelper::allowManagePhoto($groupId);
                $baselink = "index.php?option=com_community&view=photos&task=display&groupid=".$groupId;

                $groupModel    = CFactory::getModel('groups');
                $group = JTable::getInstance('Group', 'CTable');
                $group->load($groupId);
                $params = $group->getParams();
                $photopermission = ($params->get('photopermission') == GROUP_PHOTO_PERMISSION_ADMINS || $params->get('photopermission') == GROUP_PHOTO_PERMISSION_ALL ) ? 1 : 0;
                $isMember = $groupModel->isMember($my->id, $group->id);
                $isAdmin = $groupModel->isAdmin($my->id, $group->id);

                $tmpl->set('photoPermission', $photopermission)
                    ->set('isMember', $isMember)
                    ->set('isAdmin', $isAdmin);
            } else {
                $userId = $jinput->get('userid', ($task == 'myphotos' && $my->id) ? $my->id : '', 'int');

                if($task){
                    $baselink = "index.php?option=com_community&view=photos&task=".$task;
                }else{
                    $baselink = "index.php?option=com_community&view=photos";
                }


                $isOwner = ($my->id == $userId && $userId) ? true : false;
            }
            $sortBy = $jinput->get('sort', 'date');

            $task = $jinput->get('task', '');
            return $tmpl->set('isMember', $my->id != 0)
                ->set('isMyOwnPhoto', $isMyOwnPhoto)
                ->set('sortBy', $sortBy)
                ->set('baseLink', $baselink)
                ->set('config', $config)
                ->set('isOwner', $isOwner)
                ->set('type', $type)
                ->set('createLink', $createLink)
                ->set('currentTask', $task)
                ->set('showFeatured', $config->get('show_featured'))
                ->set('featuredList', $featuredList)
                ->set('isCommunityAdmin', COwnerHelper::isCommunityAdmin())
                ->set('my', $my)
                ->set('albums', $albums)
                ->set('pagination', $pagination)
                ->set('isSuperAdmin', COwnerHelper::isCommunityAdmin())
                ->set('groupId', $groupId)
                ->set('eventId', $eventId)
                ->set('submenu', $this->showSubmenu(false))
                ->fetch('album/list');
        }

        public function _getAlbumsHTML($albums, $type = PHOTOS_USER_TYPE, $pagination = NULL) {
            $mainframe = JFactory::getApplication();
            $jinput = $mainframe->input;

            $my = CFactory::getUser();
            $groupId = $jinput->request->get('groupid', '', 'INT');

            $tmpl = new CTemplate();

            $handler = $this->_getHandler();
            $photoTag = CFactory::getModel('phototagging');
            $photos = array();

            foreach ($albums as &$album) {
                $album->user = CFactory::getUser($album->creator);
                $album->link = CRoute::_("index.php?option=com_community&view=photos&task=album&albumid={$album->id}&userid={$album->creator}");
                $album->editLink = CRoute::_("index.php?option=com_community&view=photos&task=editAlbum&albumid={$album->id}&userid={$album->creator}&referrer=myphotos");
                $album->uploadLink = "javascript:joms.notifications.showUploadPhoto({$album->id});"; //CRoute::_("index.php?option=com_community&view=photos&task=uploader&albumid={$album->id}&userid={$album->creator}");
                $album->isOwner = ($my->id == $album->creator);

                // Get all photos from album
                $photos[$album->id] = $handler->getAlbumPhotos($album->id);
                $photoCount = count($photos[$album->id]);

                if ($type == PHOTOS_GROUP_TYPE) {
                    $album->link = CRoute::_("index.php?option=com_community&view=photos&task=album&albumid={$album->id}&groupid={$album->groupid}");
                    $album->editLink = CRoute::_("index.php?option=com_community&view=photos&task=editAlbum&albumid={$album->id}&groupid={$album->groupid}&referrer=myphotos");
                    $album->uploadLink = CRoute::_("index.php?option=com_community&view=photos&task=uploader&albumid={$album->id}&groupid={$album->groupid}");
                    $albums->isOwner = $my->authorise('community.view', 'photos.group.album.' . $groupId, $album);
                }
            }

            $createLink = CRoute::_('index.php?option=com_community&view=photos&task=newalbum&userid=' . $my->id);

            if ($type == PHOTOS_GROUP_TYPE) {
                $createLink = CRoute::_('index.php?option=com_community&view=photos&task=newalbum&groupid=' . $groupId);
                $baselink = "index.php?option=com_community&view=photos&task=display&groupid=".$groupId;
                $isOwner = CGroupHelper::allowManagePhoto($groupId);
            } else {
                $userId = $jinput->request->getInt('userid', '');
                $baselink = "index.php?option=com_community&view=photos&task=myphotos&userid=".$userId;
                $user = CFactory::getUser($userId);
                $isOwner = ($my->id == $user->id) ? true : false;
            }

            $featured = new CFeatured(FEATURED_ALBUMS);
            $featuredList = $featured->getItemIds();
            $config = CFactory::getConfig();

            $task = $jinput->get('task', '');
            return $tmpl->set('isMember', $my->id != 0)
                ->set('isOwner', $isOwner)
                ->set('type', $type)
                ->set('baseLink', $baselink)
                ->set('createLink', $createLink)
                ->set('currentTask', $task)
                ->set('isCommunityAdmin', COwnerHelper::isCommunityAdmin())
                ->set('my', $my)
                ->set('albums', $albums)
                ->set('pagination', $pagination)
                ->set('isSuperAdmin', COwnerHelper::isCommunityAdmin())
                ->set('featuredList', $featuredList)
                ->set('showFeatured', $config->get('show_featured'))
                ->set('groupId', $groupId)
                ->set('config', $config)
                ->fetch('album/list');
        }

        /**
         * Displays edit album form
         * */
        public function editAlbum($bolSaveSuccess = true) {
            $document = JFactory::getDocument();
            $config = CFactory::getConfig();

            $mainframe = JFactory::getApplication();
            $jinput = $mainframe->input;

            // Load necessary libraries, models
            $album = JTable::getInstance('Album', 'CTable');
            $albumId = $jinput->getInt('albumid', '');
            $referrer = $jinput->get->get('referrer', '', 'STRING');
            $album->load($albumId);
            $type = ($album->type != 'user' && $album->type != 'profile.avatar') ? PHOTOS_GROUP_TYPE : PHOTOS_USER_TYPE;
            $permissions = $album->permissions ? $album->permissions : $jinput->post->get('permissions', '', 'NONE');

            // Added to maintain user input value if there is save error
            if ($bolSaveSuccess === false) {
                $album->name = $jinput->post->get('name', '', 'STRING');
                $album->location = $jinput->post->get('location', '', 'STRING');
                $album->description = $jinput->post->get('description', '', 'STRING');
                $album->permissions = $jinput->post->get('permissions', '', 'NONE');
                $album->type = $jinput->post->get('type', '', 'NONE');
            }

            $this->addPathway(JText::sprintf('COM_COMMUNITY_PHOTOS_EDIT_ALBUM_TITLE', $album->name));

            if ($album->id == 0) {
                echo JText::_('COM_COMMUNITY_PHOTOS_INVALID_ALBUM');
                return;
            }

            /**
             * Opengraph
             */
            CHeadHelper::setType('website', JText::sprintf('COM_COMMUNITY_PHOTOS_EDIT_ALBUM_TITLE', $album->name));

            // $js = 'assets/validate-1.5.min.js';
            // CFactory::attach($js, 'js');

            $app = CAppPlugins::getInstance();
            $appFields = $app->triggerEvent('onFormDisplay', array('jsform-photos-newalbum'));
            $beforeFormDisplay = CFormElement::renderElements($appFields, 'before');
            $afterFormDisplay = CFormElement::renderElements($appFields, 'after');

            $tmpl = new CTemplate();
            echo $tmpl->set('album', $album)
                ->set('title', JText::sprintf('COM_COMMUNITY_PHOTOS_EDIT_ALBUM_TITLE', $album->name))
                ->set('type', $type)
                ->set('referrer', $referrer)
                ->set('permissions', $permissions)
                ->set('beforeFormDisplay', $beforeFormDisplay)
                ->set('afterFormDisplay', $afterFormDisplay)
                ->set('enableLocation', $config->get('enable_photos_location'))
                ->set('submenu', $this->showSubmenu(false))
                ->fetch('photos.editalbum');
        }

        /**
         * Display the new album form
         * */
        public function newalbum() {
            $config = CFactory::getConfig();
            $mainframe = JFactory::getApplication();
            $jinput = $mainframe->input;
            /**
             * Opengraph
             */
            CHeadHelper::setType('website', JText::_('COM_COMMUNITY_PHOTOS_CREATE_NEW_ALBUM_TITLE'));

            $this->addPathway(JText::_('COM_COMMUNITY_PHOTOS'), CRoute::_('index.php?option=com_community&view=photos'));
            $this->addPathway(JText::_('COM_COMMUNITY_PHOTOS_CREATE_NEW_ALBUM_TITLE'));

            // $js = 'assets/validate-1.5.min.js';
            // CFactory::attach($js, 'js');

            $handler = $this->_getHandler();
            $type = $handler->getType();

            $user = CFactory::getRequestUser();
            $params = $user->getParams();

            $album = JTable::getInstance('Album', 'CTable');
            $mainframe = JFactory::getApplication();
            $jinput = $mainframe->input;

            // Added to maintain user input value if there is save error
            $album->name = $jinput->post->get('name', '', 'STRING');
            $album->location = $jinput->post->get('location', '', 'STRING');
            $album->description = $jinput->post->get('description', '', 'STRING');
            $album->permissions = $jinput->post->get('permissions', $params->get('privacyPhotoView'), 'NONE');
            $album->type = $jinput->post->get('type', '', 'NONE');
            $album->eventid = $jinput->get('eventid',0,'INT');
            $album->groupid = $jinput->get('groupid', '', 'NONE');

            $app = CAppPlugins::getInstance();
            $appFields = $app->triggerEvent('onFormDisplay', array('jsform-photos-newalbum'));
            $beforeFormDisplay = CFormElement::renderElements($appFields, 'before');
            $afterFormDisplay = CFormElement::renderElements($appFields, 'after');

            $tmpl = new CTemplate();
            echo $tmpl->set('beforeFormDisplay', $beforeFormDisplay)
                ->set('afterFormDisplay', $afterFormDisplay)
                ->set('permissions', $album->permissions)
                ->set('type', $type)
                ->set('album', $album)
                ->set('referrer', '')
                ->set('enableLocation', $config->get('enable_photos_location'))
                ->set('submenu', $this->showSubmenu(false))
                ->fetch('photos.editalbum');
        }

        public function uploader() {
            $document = JFactory::getDocument();
            $handler = $this->_getHandler();
            $jinput = JFactory::getApplication()->input;
            $albumId = $jinput->getInt('albumid', -1);
            $my = CFactory::getUser();
            $mainframe = JFactory::getApplication();
            $jinput = $mainframe->input;

            /**
             * Opengraph
             */
            CHeadHelper::setType('website', JText::_('COM_COMMUNITY_PHOTOS_UPLOAD_MULTIPLE_PHOTOS_TITLE'));

            $this->addPathway(JText::_('COM_COMMUNITY_PHOTOS'), CRoute::_('index.php?option=com_community&view=photos'));

            if ($albumId != -1) {
                $album = JTable::getInstance('Album', 'CTable');
                $album->load($albumId);

                $this->addPathway($album->name, $handler->getAlbumURI($album->id));
            }
            $this->addPathway(JText::_('COM_COMMUNITY_PHOTOS_UPLOAD_MULTIPLE_PHOTOS_TITLE'));

            // $css = JURI::root(true) . '/components/com_community/assets/uploader/style.css';
            // $document->addStyleSheet($css);

            // Display submenu on the page.
            //$this->showSubmenu();

            // Add create album link
            $groupId = $jinput->request->get('groupid', '', 'INT');
            $type = PHOTOS_USER_TYPE;

            // Get the configuration for uploader tool
            $config = CFactory::getConfig();

            if ($handler->isExceedUploadLimit() && !CownerHelper::isCommunityAdmin()) {
                return;
            }
            echo $this->_htmluploader();
        }

        /**
         * Display the photo thumbnails from an album
         * */
        public function album() {
            $document = JFactory::getDocument();
            $mainframe = JFactory::getApplication();
            $jinput = $mainframe->input;
            $config = CFactory::getConfig();

            $my = CFactory::getUser();
            //CFactory::load( 'libraries' , 'activities' );
            // Get show photo location map by default
            $photoMapsDefault = $config->get('photosmapdefault');

            $albumId = $jinput->get('albumid', 0, 'INT');
            $album = JTable::getInstance('Album', 'CTable');
            $album->load($albumId);

            $defaultId = $jinput->get('photoid', 0, 'INT');
            $userId = $jinput->get('userid', $album->creator, 'INT');

            $jinput->set('userid',$userId);

            $limitstart = $jinput->get->get('limitstart', '0', 'INT');
            if (empty($limitstart)){
                $limitstart = $jinput->get->get('start', '0', 'INT');
            }

            $user = CFactory::getUser($album->creator);
            // Set pathway for group photos
            // Community > Groups > Group Name > Photos > Album Name
            $pathway = $mainframe->getPathway();

            // add this to the request just in case its missing
            $jinput->set('eventid',$album->eventid);
            $jinput->set('groupid',$album->groupid);

            $groupId = $jinput->get->get('groupid', '', 'INT');
            $eventId = $jinput->get->get('eventid', '', 'INT');

            $handler = $this->_getHandler();

            //we must make sure this album exists and not deleted
            if (!$album->id) {
                $tmpl = new CTemplate();
                echo $tmpl->fetch('album/missingalbum');
                return;
            }

            if($album->type == 'group'){
                if(!$my->authorise('community.view', 'photos.group.album.'.$album->groupid, $album)){
                    $tmpl = new CTemplate();
                    echo $tmpl->fetch('album/missingalbum');
                    return;
                }
            }else{
                if(!$my->authorise('community.view', 'photos.user.album.'.$album->id)){
                    $tmpl = new CTemplate();
                    echo $tmpl->fetch('album/missingalbum');
                    return;
                }
            }

            $groupId = $album->groupid;

            if ($groupId > 0) {
                $group = JTable::getInstance('Group', 'CTable');
                $group->load($groupId);

                $pathway->addItem(JText::_('COM_COMMUNITY_GROUPS'), CRoute::_('index.php?option=com_community&view=groups'));
                $pathway->addItem($group->name, CRoute::_('index.php?option=com_community&view=groups&task=viewgroup&groupid=' . $groupId));
                $pathway->addItem(JText::_('COM_COMMUNITY_PHOTOS'), CRoute::_('index.php?option=com_community&view=photos&groupid=' . $groupId));
            } elseif (!empty($eventId)) {
                $event = JTable::getInstance('Event', 'CTable');
                $event->load($eventId);

                $pathway->addItem(JText::_('COM_COMMUNITY_EVENTS'), CRoute::_('index.php?option=com_community&view=events'));
                $pathway->addItem($event->title, CRoute::_('index.php?option=com_community&view=events&task=viewevent&eventid=' . $eventId));
                $pathway->addItem(JText::_('COM_COMMUNITY_PHOTOS'), CRoute::_('index.php?option=com_community&view=photos&eventid=' . $eventId));
            } else {
                $pathway->addItem(JText::_('COM_COMMUNITY_PHOTOS'), CRoute::_('index.php?option=com_community&view=photos'));
                $pathway->addItem(JText::sprintf('COM_COMMUNITY_PHOTOS_USER_PHOTOS_TITLE', $user->getDisplayName()), CRoute::_('index.php?option=com_community&view=photos&task=myphotos&userid=' . $userId));
            }

            $handler->setMiniHeader();

            if (is_null($album->id)) {
                echo JText::_('COM_COMMUNITY_ALBUM_DELETED');
                return;
            }

            if (!$handler->isAlbumBrowsable($albumId)) {
                return;
            }

            //$photos		= $handler->getAlbumPhotos( $album->id );
            $photoPaginationLimit = intval($config->get('pagination'));
            $photoThumbLimit = $photoPaginationLimit;
            $model = CFactory::getModel('photos');
            $photos = $model->getPhotos($album->id, $photoThumbLimit, $limitstart);

            $pagination = $model->getPagination();

            $photo = JTable::getInstance('Photo', 'CTable');
            $photo->load($album->photoid);

            if ($album->photoid == '0') {
                $album->thumbnail = $photo->getThumbURI();
            } else {
                $album->thumbnail = $photo->getImageURI();
            }

            // Increment album's hit each time this page is loaded.
            $album->hit();

            if ($groupId > 0) {
                $otherAlbums = $model->getGroupAlbums($groupId);
            } elseif($eventId){
                $otherAlbums = $model->getEventAlbums($eventId);
            }else {
                $otherAlbums = $model->getAlbums($user->id, true, true, '', array('profile.default','group','event','profile.Cover','profile.avatar','group.avatar','group.Cover','event.Cover','event.avatar'));
            }

            $totalAlbums = count($otherAlbums);
            $showOtherAlbum = 6;
            $randomAlbum = array();

            if (count($otherAlbums) > 0) {
                $randomId = ($totalAlbums < $showOtherAlbum) ? array_rand($otherAlbums, $totalAlbums) : array_rand($otherAlbums, $showOtherAlbum);

                $count = 0;
                for ($i = 0; $i < $totalAlbums; $i++) {
                    $num = (is_array($randomId)) ? $randomId[$i] : $randomId;
                    if ($otherAlbums[$num]->id != $album->id) {
                        $count++;
                        $randomAlbum[] = $otherAlbums[$num];
                    }
                    if (count($randomAlbum) == ($showOtherAlbum - 1)) {
                        break;
                    }
                }
            }

            /* set head meta */
            if (
                strtolower(trim(JText::sprintf('COM_COMMUNITY_PHOTOS_USER_PHOTOS_TITLE', $handler->getCreatorName()))) == strtolower(trim($album->name))) {
                /**
                 * Opengraph
                 */
                CHeadHelper::setType('website', JText::sprintf('COM_COMMUNITY_PHOTOS_USER_PHOTOS_TITLE', $handler->getCreatorName()), CStringHelper::escape($album->getDescription()));
            } else {
                /**
                 * Opengraph
                 */
                CHeadHelper::setType('website', JText::sprintf('COM_COMMUNITY_PHOTOS_USER_PHOTOS_TITLE', $album->name), CStringHelper::escape($album->getDescription()));
            }

            $handler->setAlbumPathway(CStringHelper::escape($album->name));
            $handler->setRSSHeader($albumId);

            // Set album thumbnail and description for social bookmarking sites linking
            $document->addHeadLink($album->getCoverThumbURI(), 'image_src', 'rel');
            //$document->setDescription( CStringHelper::escape($album->getDescription()) );
            //CFactory::load( 'libraries' , 'phototagging' );
            $getTaggingUsers = new CPhotoTagging();
            $people = array();

            // @TODO temporary fix for undefined link
            $list= array ();
            foreach ( $photos as $photo ) {
                $photo->link = $handler->getPhotoURI($photo->id, $photo->albumid);
                CHeadHelper::addOpengraph('og:image', JUri::root() . '/' . $photo->image, true);
                $list[] = $photo;
            }
            $photos = $list;
            $albumParam = new Cparameter($album->params);
            $tagged = $albumParam->get('tagged');

            if (!empty($tagged)) {
                $people = explode(',', $albumParam->get('tagged'));
            }

            //Update lastUpdated
            $lastUpdated = new JDate($album->lastupdated);
            $album->lastUpdated = CActivityStream::_createdLapse($lastUpdated, false);

            $people = array_unique($people);

            CFactory::loadUsers($people);

            foreach ($people as &$person) {
                $person = CFactory::getUser($person);
            }

            //CFactory::load( 'libraries' , 'bookmarks' );
            $bookmarks = new CBookmarks($handler->getAlbumExternalURI($album->id));

            // Get wall data.
            $wallCount = CWallLibrary::getWallCount('albums', $album->id);
            $viewAllLink = false;
            if ($jinput->request->get('task', '') != 'app') {
                $viewAllLink = CRoute::_('index.php?option=com_community&view=photos&task=app&albumid=' . $album->id . '&app=walls');
            }

            $wallContent = CWallLibrary::getWallContents(
                'albums',
                $album->id,
                ( COwnerHelper::isCommunityAdmin() || ($my->id == $album->creator && ($my->id != 0))),
                $config->get('stream_default_comments'),
                0,
                'wall/content',
                'photos,album'
            );

            $wallForm    = CWallLibrary::getWallInputForm($album->id, 'photos,ajaxAlbumSaveWall', 'photos,ajaxAlbumRemoveWall', $viewAllLink);

            $viewAllLink = CRoute::_('index.php?option=com_community&view=photos&task=app&albumid=' . $album->id . '&app=walls');

            $wallViewAll = '';
            if ( $wallCount > $config->get('stream_default_comments') ) {
                $wallViewAll = CWallLibrary::getViewAllLinkHTML($viewAllLink, $wallCount);
            }

            $redirectUrl = CRoute::getURI(false);

            $tmpl = new CTemplate();
            if ($album->location != "") {

                $zoomableMap = CMapping::drawZoomableMap($album->location, 220, 150);
            } else {
                $zoomableMap = "";
            }
            // Get the likes / dislikes item
            //CFactory::load( 'libraries' , 'like' );
            $like = new CLike();
            $likeCount = $like->getLikeCount('album', $album->id);
            $likeLiked = $like->userLiked('album', $album->id, $my->id) === COMMUNITY_LIKE;

            $owner = CFactory::getUser($album->creator);
            $document->setTitle($album->name);

            //change the name of the title for this page if this is a default album
            switch(strtolower($album->type)){
                case 'profile.cover':
                    $album->name = JText::sprintf('COM_COMMUNITY_PHOTOS_ALBUM_DEFAULT_COVER_TITLE', CFactory::getUser($album->creator)->getDisplayName());
                    $document->setTitle(ucfirst($album->name));
                    break;
                case 'profile.avatar':
                    $album->name = JText::sprintf('COM_COMMUNITY_PHOTOS_ALBUM_DEFAULT_AVATAR_TITLE', CFactory::getUser($album->creator)->getDisplayName());
                    $document->setTitle(ucfirst($album->name));
                    break;
                case 'profile.gif':
                    $album->name = JText::sprintf('COM_COMMUNITY_PHOTOS_ALBUM_DEFAULT_ANIMATION_TITLE', CFactory::getUser($album->creator)->getDisplayName());
                    $document->setTitle(ucfirst($album->name));
                    break;
                case 'user':
                    if($album->default){
                        // this is a stream photos
                        $album->name = JText::sprintf('COM_COMMUNITY_PHOTOS_ALBUM_DEFAULT_STREAM_TITLE', CFactory::getUser($album->creator)->getDisplayName());
                        $document->setTitle(ucfirst($album->name));
                    }
                    break;
                case 'group.cover':
                    $groupTable = JTable::getInstance('Group', 'CTable');
                    $groupTable->load($album->groupid);
                    $album->name = JText::sprintf('COM_COMMUNITY_PHOTOS_ALBUM_DEFAULT_COVER_TITLE', $groupTable->name);
                    $document->setTitle(ucfirst($album->name));
                    break;
                case 'group.avatar':
                    $groupTable = JTable::getInstance('Group', 'CTable');
                    $groupTable->load($album->groupid);
                    $album->name = JText::sprintf('COM_COMMUNITY_PHOTOS_ALBUM_DEFAULT_AVATAR_TITLE', $groupTable->name);
                    $document->setTitle(ucfirst($album->name));
                    break;
                case 'group.gif':
                    $groupTable = JTable::getInstance('Group', 'CTable');
                    $groupTable->load($album->groupid);
                    $album->name = JText::sprintf('COM_COMMUNITY_PHOTOS_ALBUM_DEFAULT_ANIMATION_TITLE', $groupTable->name);
                    $document->setTitle(ucfirst($album->name));
                    break;
                case 'group':
                    $groupTable = JTable::getInstance('Group', 'CTable');
                    $groupTable->load($album->groupid);
                    if($album->default){
                        // this is a stream photos
                        $album->name = JText::sprintf('COM_COMMUNITY_PHOTOS_ALBUM_DEFAULT_STREAM_TITLE',$groupTable->name);
                        $document->setTitle(ucfirst($album->name));
                    }
                    break;
                case 'event.cover':
                    $eventTable = JTable::getInstance('Event', 'CTable');
                    $eventTable->load($album->eventid);
                    $album->name = JText::sprintf('COM_COMMUNITY_PHOTOS_ALBUM_DEFAULT_COVER_TITLE', $eventTable->title);
                    $document->setTitle(ucfirst($album->name));
                    break;
                case 'event.gif':
                    $eventTable = JTable::getInstance('Event', 'CTable');
                    $eventTable->load($album->eventid);
                    $album->name = JText::sprintf('COM_COMMUNITY_PHOTOS_ALBUM_DEFAULT_ANIMATION_TITLE', $eventTable->title);
                    $document->setTitle(ucfirst($album->name));
                    break;
                case 'event':
                    $eventTable = JTable::getInstance('Event', 'CTable');
                    $eventTable->load($album->eventid);
                    if($album->default){
                        // this is a stream photos
                        $album->name = JText::sprintf('COM_COMMUNITY_PHOTOS_ALBUM_DEFAULT_STREAM_TITLE',$eventTable->title);
                        $document->setTitle(ucfirst($album->name));
                    }
                default:

            }

            echo $tmpl
                ->set('photosmapdefault', $photoMapsDefault)
                ->set('my', $my)
                ->set('bookmarksHTML', $bookmarks->getHTML())
                ->set('isOwner', $handler->isAlbumOwner($album->id))
                ->set('isAdmin', COwnerHelper::isCommunityAdmin())
                ->set('owner', $owner)
                ->set('photos', $photos)
                ->set('people', $people)
                ->set('album', $album)
                ->set('groupId', $groupId)
                ->set('eventId', $eventId)
                ->set('otherAlbums', $randomAlbum)
                ->set('likeCount', $likeCount)
                ->set('likeLiked', $likeLiked)
                ->set('wallContent', $wallContent)
                ->set('wallForm', $wallForm)
                ->set('wallCount', $wallCount)
                ->set('wallViewAll', $wallViewAll)
                ->set('zoomableMap', $zoomableMap)
                ->set('pagination', $pagination)
                ->set('photoId',$defaultId)
                ->set('submenu', $this->showSubmenu(false))
                ->fetch('photos/list');
        }

        /**
         * Displays single photo view
         *
         * */
        public function photo() {
            $mainframe = JFactory::getApplication();
            $jinput    = $mainframe->input;
            $document  = JFactory::getDocument();

            $photoId   = $jinput->get('photoid', '', 'INT');
            $photo = JTable::getInstance('Photo', 'CTable');
            $photo->load($photoId);

            $albumId   = $jinput->get('albumid', $photo->albumid, 'INT');
            $userId    = $jinput->get('userid', $photo->creator, 'int');
            $user      = CFactory::getUser($userId);
            $my        = CFactory::getUser();
            $jinput->set('userid',$userId);

            $album = JTable::getInstance('Album', 'CTable');
            $album->load($photo->albumid);

            // add this to the request just in case its missing
            if($album->eventid){
                $jinput->set('eventid',$album->eventid);
            }elseif($album->groupid){
                $jinput->set('groupid',$album->groupid);
            }

            $handler = $this->_getHandler();
            $handler->setMiniHeader();

            echo $this->showSubmenu();

            $document->setTitle($album->name);
            $document->setMetaData('title', $album->name);
            $document->setMetaData('description', $photo->caption);

            CHeadHelper::addOpengraph('og:image', JUri::root() . $photo->image, true);

            $tmpl = new CTemplate();
            echo $tmpl
                ->set('album', $album)
                ->set('photo', $photo)
                ->set('my', $my)
                ->fetch('photos/single');
        }

        /**
         * return the resized images
         */
        public function showimage() {

        }

        /**
         * Return photos handlers
         * @param string $type if type is supplied, always follow the type
         * @return CommunityViewPhotosEventHandler|CommunityViewPhotosGroupHandler|CommunityViewPhotosUserHandler|null
         * @throws Exception
         *
         */
        private function _getHandler($type = '') {
            $handler = null;

            $mainframe = JFactory::getApplication();
            $jinput = $mainframe->input;
            $groupId = $jinput->get('groupid',0,'INT');
            $eventId = $jinput->get('eventid',0,'INT');

            if ($groupId || $type == 'group') {
                // group photo
                $handler = new CommunityViewPhotosGroupHandler($this);
            } elseif($eventId || $type == 'event') {
                $handler = new CommunityViewPhotosEventHandler($this);
            } else {
                // user photo
                $handler = new CommunityViewPhotosUserHandler($this);
            }

            return $handler;
        }

        /**
         * Application full view
         * */
        public function appFullView() {

            /**
             * Opengraph
             */
            // CHeadHelper::setType('website', JText::_('COM_COMMUNITY_PHOTOS_WALL_TITLE'));

            $mainframe = JFactory::getApplication();
            $jinput = $mainframe->input;
            $applicationName = JString::strtolower($jinput->get->get('app', '', 'STRING'));

            if (empty($applicationName)) {
                JFactory::getApplication()->enqueueMessage(JText::_('COM_COMMUNITY_APP_ID_REQUIRED'), 'error');
            }

            $output = '<div class="joms-page">';
            $output .= '<h3 class="joms-page__title">' . JText::_('COM_COMMUNITY_PHOTOS_WALL_TITLE') . '</h3>';

            if ($applicationName == 'walls') {
                //CFactory::load( 'libraries' , 'wall' );
                $limit = $jinput->request->get('limit', 5, 'INT');
                $limitstart = $jinput->request->get('limitstart', 0, 'INT');
                $albumId = $jinput->getInt('albumid', '');
                $my = CFactory::getUser();

                $album = JTable::getInstance('Album', 'CTable');
                $album->load($albumId);

                //CFactory::load( 'helpers' , 'owner' );
                //CFactory::load( 'helpers' , 'friends' );

                // Get the walls content
                $viewAllLink = false;
                $wallCount = false;
                if ($jinput->request->get('task', '') != 'app') {
                    $viewAllLink = CRoute::_('index.php?option=com_community&view=photos&task=app&albumid=' . $album->id . '&app=walls');
                    $wallCount = CWallLibrary::getWallCount('album', $album->id);
                }

                $output .= CWallLibrary::getWallContents('albums', $album->id, ( COwnerHelper::isCommunityAdmin() || COwnerHelper::isMine($my->id, $album->creator)), $limit, $limitstart);

                if (CFriendsHelper::isConnected($my->id, $album->creator) || COwnerHelper::isCommunityAdmin()) {
                    $output .= CWallLibrary::getWallInputForm($album->id, 'photos,ajaxAlbumSaveWall', 'photos,ajaxAlbumRemoveWall');
                }

                $output .= CWallLibrary::getViewAllLinkHTML($viewAllLink, $wallCount);

                jimport('joomla.html.pagination');
                $wallModel = CFactory::getModel('wall');
                $pagination = new JPagination($wallModel->getCount($album->id, 'albums'), $limitstart, $limit);

                $output .= '<div class="cPagination">' . $pagination->getPagesLinks() . '</div>';
            } else {
                $model = CFactory::getModel('apps');
                $applications = CAppPlugins::getInstance();
                $applicationId = $model->getUserApplicationId($applicationName);

                $application = $applications->get($applicationName, $applicationId);

                if (is_callable(array($application, 'onAppDisplay'), true)) {
                    // Get the parameters
                    $manifest = CPluginHelper::getPluginPath('community', $applicationName) . '/' . $applicationName . '/' . $applicationName . '.xml';

                    $params = new CParameter($model->getUserAppParams($applicationId), $manifest);

                    $application->params = $params;
                    $application->id = $applicationId;

                    $output = $application->onAppDisplay($params);
                } else {
                    JFactory::getApplication()->enqueueMessage(JText::_('COM_COMMUNITY_APPS_NOT_FOUND'), 'error');
                }
            }

            $output .= '</div>';
            echo $output;
        }

    }

    abstract class CommunityViewPhotoHandler extends CommunityView {

        protected $type = '';
        protected $model = '';
        protected $view = '';
        protected $my = '';

        abstract public function getType();

        abstract public function getFlashUploadURI($token, $albumId);

        abstract public function getAllAlbumData();

        abstract public function getAlbumURI($albumId);

        abstract public function getAlbumExternalURI($albumId);

        abstract public function getPhotoURI($photoId, $albumId);

        abstract public function getPhotoExternalURI($photoId, $albumId);

        abstract public function getCreatorName();

        abstract public function getAlbumPhotos($albumId);

        abstract public function getTaggingUsers();

        abstract public function getAlbumCreateLink();

        abstract public function setAlbumPathway($albumName);

        abstract public function setMiniHeader();

        abstract public function setSubmenus();

        abstract public function setRSSHeader($albumId);

        abstract public function isExceedUploadLimit();

        abstract public function isPhotoBrowsable($photoId);

        abstract public function isAlbumBrowsable($albumId);

        abstract public function isAlbumOwner($albumId);

        abstract public function isTaggable();

        abstract public function isWallAllowed();

        public function __construct(CommunityViewPhotos $viewObj) {
            $this->view = $viewObj;
            $this->my = CFactory::getUser();
            $this->model = CFactory::getModel('photos');
        }

    }

    class CommunityViewPhotosUserHandler extends CommunityViewPhotoHandler {

        var $user = null;

        public function __construct($viewObj) {
            parent::__construct($viewObj);
            $jinput = JFactory::getApplication()->input;

            $userid = $jinput->get('userid', null, 'INT');
            $this->user = CFactory::getUser($userid);
        }

        public function getAlbumCreateLink() {
            return CRoute::_('index.php?option=com_community&view=photos&task=newalbum&userid=' . $this->my->id);
        }

        public function getFlashUploadURI($token, $albumId) {
            $session = JFactory::getSession();
            $url = 'index.php?option=com_community&view=photos&task=upload&no_html=1&albumid=' . $albumId . '&tmpl=component';
            $url .= '&' . $session->getName() . '=' . $session->getId() . '&token=' . $token->token . '&uploaderid=' . $this->my->id . '&userid=' . $this->my->id;
            $url = JURI::root(true) . '/' . $url;
            return $url;
        }

        public function isWallAllowed() {
            $config = CFactory::getConfig();

            // Check if user is really allowed to post walls on this photo.
            if (COwnerHelper::isMine($this->my->id, $this->user->id) || (!$config->get('lockphotoswalls')) || ( $config->get('lockphotoswalls') && CFriendsHelper::isConnected($this->my->id, $this->user->id) ) || COwnerHelper::isCommunityAdmin()) {
                return true;
            }
            return false;
        }

        public function isTaggable() {
            if (COwnerHelper::isMine($this->my->id, $this->user->id) || CFriendsHelper::isConnected($this->my->id, $this->user->id)) {
                return true;
            }
            return false;
        }

        public function getTaggingUsers() {
            $model = CFactory::getModel('friends');
//		$friends	= $model->getFriends( $this->my->id , '' , false );
            $friends = $model->getFriendRecords($this->my->id, '', false);
            array_unshift($friends, $this->my);

            return $friends;
        }

        public function setRSSHeader($albumId) {
            $document = JFactory::getDocument();
            $album = JTable::getInstance('Album', 'CTable');
            $album->load($albumId);
            $mainframe = JFActory::getApplication();

            // Set feed url
            $link = CRoute::_('index.php?option=com_community&view=photos&task=album&albumid=' . $album->id . '&userid=' . $album->creator . '&format=feed');
            $feed = '<link rel="alternate" type="application/rss+xml" href="' . $link . '"/>';

            $document->addCustomTag($feed);
        }

        public function getAlbumPhotos($albumId) {
            $config = CFactory::getConfig();
            $model = CFactory::getModel('Photos');

            // @todo: make limit configurable?
            return $model->getAllPhotos($albumId, PHOTOS_USER_TYPE, null, null, $config->get('photosordering'));
        }

        public function setAlbumPathway($albumName) {
            $mainframe = JFactory::getApplication();
            $pathway = $mainframe->getPathway();
            $pathway->addItem($albumName);
        }

        public function setSubmenus() {
            $my = CFactory::getUser();
            $config = CFactory::getConfig();

            $jinput = JFactory::getApplication()->input;

            $task = $jinput->get('task', 0, 'WORD');
            $albumId = $jinput->get('albumid', 0, 'INT');

            $album = JTable::getInstance('Album', 'CTable');
            $album->load($albumId);

            switch ($task) {
                case '':
                    if ($albumId) {
                        $this->view->addSubmenuItem('index.php?option=com_community&view=photos&userid=' . $this->user->id . '&task=album&albumid=' . $albumId,
                            JText::_('COM_COMMUNITY_PHOTOS_BACK_TO_ALBUM'));
                    }

                    if($config->get('enablegroups')){
                        $this->view->addSubmenuItem('index.php?option=com_community&view=photos&task=group', JText::_('COM_COMMUNITY_PHOTOS_ALL_GROUP_PHOTOS'), '' , false , '' , 'joms-right');
                    }

                    if($config->get('enableevents')){
                        $this->view->addSubmenuItem('index.php?option=com_community&view=photos&task=event', JText::_('COM_COMMUNITY_PHOTOS_ALL_EVENT_PHOTOS'), '' , false , '' , 'joms-right');
                    }

                    $this->view->addSubmenuItem('index.php?option=com_community&view=photos&task=display', JText::_('COM_COMMUNITY_PHOTOS_ALL_PHOTOS'), '' , false , '' , 'joms-right');
                    if($task=='myphotos' && $my->id){
                        $this->view->addSubmenuItem('index.php?option=com_community&view=photos&task=newalbum&userid=' . $my->id, JText::_('COM_COMMUNITY_PHOTOS_CREATE_PHOTO_ALBUM'), '', true);
                    }
                    break;
                case 'photo':
                    if ($albumId) {
                        $this->view->addSubmenuItem('index.php?option=com_community&view=photos&userid=' . $this->user->id . '&task=album&albumid=' . $albumId,
                            JText::_('COM_COMMUNITY_PHOTOS_BACK_TO_ALBUM'));
                    }

                    if (COwnerHelper::isCommunityAdmin() || ($this->my->id == $album->creator && ($this->my->id != 0))) {
                        $this->view->addSubmenuItem('', JText::_('COM_COMMUNITY_DELETE'), "joms_delete_photo();", true);

                        if ($this->my->id == $album->creator) {
                            $this->view->addSubmenuItem('', JText::_('COM_COMMUNITY_PHOTOS_SET_AVATAR'),
                                "joms_set_as_profile_picture();", true);
                        }
                        $this->view->addSubmenuItem('', JText::_('COM_COMMUNITY_PHOTOS_SET_AS_ALBUM_COVER'),
                            "joms_set_as_album_cover();", true);
                    }
                    if (!$config->get('deleteoriginalphotos')) {
                        $this->view->addSubmenuItem('', JText::_('COM_COMMUNITY_DOWNLOAD_IMAGE'),
                            "joms_download_photo();", true);
                    }
                    //$this->view->addSubmenuItem('index.php?option=com_community&view=photos&task=group', JText::_('COM_COMMUNITY_PHOTOS_ALL_GROUP_PHOTOS'), '' , false , '' , 'joms-right');
                    //$this->view->addSubmenuItem('index.php?option=com_community&view=photos&task=event', JText::_('COM_COMMUNITY_PHOTOS_ALL_EVENT_PHOTOS'), '' , false , '' , 'joms-right');
                    //$this->view->addSubmenuItem('index.php?option=com_community&view=photos&task=display', JText::_('COM_COMMUNITY_PHOTOS_ALL_PHOTOS'), '' , false , '' , 'joms-right');
                    break;
                case 'singleupload':
                case 'uploader':
                    if ($albumId){
                        $this->view->addSubmenuItem('index.php?option=com_community&view=photos&userid=' . $this->user->id . '&task=album&albumid=' . $albumId, JText::_('COM_COMMUNITY_PHOTOS_BACK_TO_ALBUM'));
                    }
                    //$this->view->addSubmenuItem('index.php?option=com_community&view=photos&task=group', JText::_('COM_COMMUNITY_PHOTOS_ALL_GROUP_PHOTOS'), '' , false , '' , 'joms-right');
                    //$this->view->addSubmenuItem('index.php?option=com_community&view=photos&task=event', JText::_('COM_COMMUNITY_PHOTOS_ALL_EVENT_PHOTOS'), '' , false , '' , 'joms-right');
                    //$this->view->addSubmenuItem('index.php?option=com_community&view=photos&task=display', JText::_('COM_COMMUNITY_PHOTOS_ALL_PHOTOS'), '' , false , '' , 'joms-right');
                break;
                case 'myphotos':
                    //@since 4.2.1, no submenu is needed in my photos
                    return;
                    if($config->get('enablegroups')){
                        $this->view->addSubmenuItem('index.php?option=com_community&view=photos&task=group', JText::_('COM_COMMUNITY_PHOTOS_ALL_GROUP_PHOTOS'), '' , false , '' , 'joms-right');
                    }

                    if($config->get('enableevents')){
                        $this->view->addSubmenuItem('index.php?option=com_community&view=photos&task=event', JText::_('COM_COMMUNITY_PHOTOS_ALL_EVENT_PHOTOS'), '' , false , '' , 'joms-right');
                    }

                    $this->view->addSubmenuItem('index.php?option=com_community&view=photos&task=display', JText::_('COM_COMMUNITY_PHOTOS_ALL_PHOTOS'), '' , false , '' , 'joms-right');

                    if ($this->my->id != 0 || COwnerHelper::isCommunityAdmin()) {
                        $this->view->addSubmenuItem('index.php?option=com_community&view=photos&task=newalbum&userid=' . $my->id, JText::_('COM_COMMUNITY_PHOTOS_CREATE_PHOTO_ALBUM'), '', true);
                    }
                    break;
                case 'newalbum':
                    //$this->view->addSubmenuItem('index.php?option=com_community&view=photos&task=group', JText::_('COM_COMMUNITY_PHOTOS_ALL_GROUP_PHOTOS'), '' , false , '' , 'joms-right');
                    //$this->view->addSubmenuItem('index.php?option=com_community&view=photos&task=event', JText::_('COM_COMMUNITY_PHOTOS_ALL_EVENT_PHOTOS'), '' , false , '' , 'joms-right');
                    //$this->view->addSubmenuItem('index.php?option=com_community&view=photos&task=display', JText::_('COM_COMMUNITY_PHOTOS_ALL_PHOTOS'), '' , false , '' , 'joms-right');
                    break;
                default:

                    if(!$albumId){
                        // do not show this 3 links if we are viewing photo albums
                        if($config->get('enablegroups')){
                            $this->view->addSubmenuItem('index.php?option=com_community&view=photos&task=group', JText::_('COM_COMMUNITY_PHOTOS_ALL_GROUP_PHOTOS'), '' , false , '' , 'joms-right');
                        }

                        if($config->get('enableevents')){
                            $this->view->addSubmenuItem('index.php?option=com_community&view=photos&task=event', JText::_('COM_COMMUNITY_PHOTOS_ALL_EVENT_PHOTOS'), '' , false , '' , 'joms-right');
                        }

                        $this->view->addSubmenuItem('index.php?option=com_community&view=photos&task=display', JText::_('COM_COMMUNITY_PHOTOS_ALL_PHOTOS'), '' , false , '' , 'joms-right');
                    }

                    if (!$albumId && ($this->my->id != 0 || COwnerHelper::isCommunityAdmin())) {
                        $groupid = $jinput->get('groupid', 0, 'INT');
                        $exludeUploadTask = array('newalbum', 'editAlbum', 'display', 'group', 'event');
                        if (!in_array($task, $exludeUploadTask) && !CAlbumsHelper::isFixedAlbum($album)) {
                            $this->view->addSubmenuItem('javascript:void(0);', JText::_('COM_COMMUNITY_PHOTOS_UPLOAD_PHOTOS'), "joms.api.photoUpload('" . $albumId . "','" . $groupid . "'); return false;", true);
                        }
                    }elseif($albumId){
                        //add a back button
                        if($album->type == 'user'){
                            $link = "index.php?option=com_community&view=photos&task=myphotos&userid=".$album->creator;
                        }elseif($album->type == 'group'){
                            $link = "index.php?option=com_community&view=photos&task=display&groupid=".$album->groupid;
                        }elseif($album->type == 'event'){
                            $link = "index.php?option=com_community&view=photos&task=display&eventid=".$album->eventid;
                        }else{
                            $link = "index.php?option=com_community&view=photos&task=display";
                        }
                        $this->view->addSubmenuItem($link, JText::_('COM_COMMUNITY_PHOTOS_BACK_TO_ALBUM'), '' , false , '' , 'joms-left');
                    }

                    if ($task == 'album' && ($my->id == $album->creator || COwnerHelper::isCommunityAdmin()) && !CAlbumsHelper::isFixedAlbum($album)) {
                        $this->view->addSubmenuItem('javascript:void(0);', JText::_('COM_COMMUNITY_PHOTOS_ALBUM_DELETE'), "joms.api.albumRemove('".$albumId."')", true);
                        $this->view->addSubmenuItem('index.php?option=com_community&view=photos&task=editAlbum&albumid=' . $albumId . '&userid=' . $my->id . '&referrer=album', JText::_('COM_COMMUNITY_EDIT_ALBUM'), '', true);
                    }

                    if (($this->my->id != 0 || COwnerHelper::isCommunityAdmin()) && $task=='myphotos') {
                        $this->view->addSubmenuItem('index.php?option=com_community&view=photos&task=newalbum&userid=' . $my->id, JText::_('COM_COMMUNITY_PHOTOS_CREATE_PHOTO_ALBUM'), '', true);
                    }
                    break;
            }

            //@since 4.1 we only display my photos in all photo page
            $allowMyPhotoTask = array('display','event','group','myphotos');
            if ($my->id != 0 && in_array($task,$allowMyPhotoTask)) {
                $this->view->addSubmenuItem('index.php?option=com_community&view=photos&task=myphotos&userid=' . $my->id, JText::_('COM_COMMUNITY_PHOTOS_MY_PHOTOS'), '' , false , '' , 'joms-right');
            }
        }

        public function getType() {
            return PHOTOS_USER_TYPE;
        }

        /**
         * Deprecated since 1.8.9
         * */
        public function isPhotoBrowsable($photoId) {
            return $this->isAlbumBrowsable($photoId);
        }

        public function isAlbumBrowsable($albumId) {

            $mainframe = JFactory::getApplication();

            $album = JTable::getInstance('Album', 'CTable');
            $album->load($albumId);

            $my = CFactory::getUser();
            /* Community Admin can access anywhere */
            if (COwnerHelper::isCommunityAdmin($my->id)) {
                return true;
            }

            if ($this->user->block && !COwnerHelper::isCommunityAdmin($my->id)) {
                $mainframe->redirect('index.php?option=com_community&view=photos', JText::_('COM_COMMUNITY_PHOTOS_USER_ACCOUNT_IS_BANNED'));
                return false;
            }

            //owner can always access
            if($album->creator == $this->my->id){
                return true;
            }

            //if( !CPrivacy::isAccessAllowed($this->my->id, $this->user->id, 'user', 'privacyPhotoView') || $album->creator != $this->user->id )
            if (!CPrivacy::isAccessAllowed($this->my->id, $this->user->id, 'custom', $album->permissions)) {
                $this->noAccess();
                return false;
            }else{
                return true;
            }

            return false;
        }

        public function isAlbumOwner($albumId) {

            if ($this->my->id == 0)
                return false;

            $album = JTable::getInstance('Album', 'CTable');
            $album->load($albumId);

            return COwnerHelper::isMine($this->my->id, $album->creator);
        }

        /**
         * Return the uri to the album view, given the album id
         */
        public function getAlbumURI($albumId) {
            return CRoute::_('index.php?option=com_community&view=photos&task=album&albumid=' . $albumId . '&userid=' . $this->user->id);
        }

        public function getAlbumExternalURI($albumId) {
            return CRoute::getExternalURL('index.php?option=com_community&view=photos&task=album&albumid=' . $albumId . '&userid=' . $this->user->id);
        }

        /**
         * Return the uri to the photo view, given the album id and photo id
         */
        public function getPhotoURI($photoId, $albumId) {
            return CRoute::_('index.php?option=com_community&view=photos&task=photo&userid=' . $this->user->id . '&albumid=' . $albumId . '&photoid=' . $photoId);
        }

        public function getPhotoExternalURI($photoId, $albumId) {
            return CRoute::getExternalURL('index.php?option=com_community&view=photos&task=album&albumid=' . $albumId . '&userid=' . $this->user->id . '&photoid=' . $photoId);
        }

        public function isExceedUploadLimit() {
            $my = CFactory::getUser();

            if (CLimitsHelper::exceededPhotoUpload($my->id, PHOTOS_USER_TYPE)) {
                $config = CFactory::getConfig();
                $photoLimit = $config->get('photouploadlimit');

                echo JText::sprintf('COM_COMMUNITY_PHOTOS_UPLOAD_LIMIT_REACHED', $photoLimit);
                return true;
            }
            return false;
        }

        /**
         * Return data for the 'all album' view
         * @param string $sortBy
         * @return mixed
         */
        public function getAllAlbumData($sortBy = 'date', $type = 'special') {
            $albumsData['data'] = $this->model->getAllAlbums($this->my->id, 0, $sortBy, $type);
            $albumsData['pagination'] = $this->model->getPagination();
            return $albumsData;
        }

        public function setMiniHeader() {
            //if ($this->my->id != $this->user->id) {
                $this->view->attachMiniHeaderUser($this->user->id);
            //}
        }

        public function getCreatorName() {
            return $this->user->getDisplayName();
        }

    }

    class CommunityViewPhotosGroupHandler extends CommunityViewPhotoHandler {

        private $groupid = null;

        /**
         * Constructor
         */
        public function __construct($viewObj) {
            parent::__construct($viewObj);
            $this->groupid = JRequest::getInt('groupid', '', 'REQUEST');
        }

        public function getFlashUploadURI($token, $albumId) {
            $session = JFactory::getSession();
            $url = 'index.php?option=com_community&view=photos&task=upload&no_html=1&albumid=' . $albumId . '&tmpl=component';
            $url .= '&' . $session->getName() . '=' . $session->getId() . '&token=' . $token->token . '&uploaderid=' . $this->my->id . '&groupid=' . $this->groupid;
            $url = JURI::root(true). '/' . $url;
            return $url;
        }

        public function getAlbumCreateLink() {
            return CRoute::_('index.php?option=com_community&view=photos&task=newalbum&groupid=' . $this->groupid);
        }

        public function isWallAllowed() {
            return $this->isTaggable();
        }

        public function isTaggable() {
            //CFactory::load( 'helpers' , 'owner' );
            //CFactory::load( 'models' , 'groups' );
            $group = JTable::getInstance('Group', 'CTable');
            $group->load($this->groupid);

            //check if we can allow the current viewing user to tag the photos
            if ($group->isMember($this->my->id) || $group->isAdmin($this->my->id) || COwnerHelper::isCommunityAdmin()) {
                return true;
            }
            return false;
        }

        public function getTaggingUsers() {
            // for photo tagging. only allow to tag members
            $model = CFactory::getModel('groups');
            $ids = $model->getMembersId($this->groupid, true);
            $users = array();

            $group = JTable::getInstance('Group', 'CTable');
            $group->load($this->groupid);

            foreach ($ids as $id) {
                if ($this->my->id != $id) {
                    $user = CFactory::getUser($id);
                    $users[] = $user;
                }
            }

            //CFactory::load( 'helpers' , 'owner' );

            if (COwnerHelper::isCommunityAdmin() || $group->isAdmin($this->my->id) || $group->isMember($this->my->id))
                array_unshift($users, $this->my);

            return $users;
        }

        public function setRSSHeader($albumId) {
            return;
        }

        public function getAlbumPhotos($albumId) {
            $config = CFactory::getConfig();
            $model = CFactory::getModel('Photos');

            // @todo: make limit configurable?
            return $model->getAllPhotos($albumId, PHOTOS_GROUP_TYPE, null, null, $config->get('photosordering'));
        }

        public function setSubmenus() {
            //CFactory::load( 'helpers' , 'group' );
            //CFactory::load( 'helpers' , 'owner' );
            $jinput = JFactory::getApplication()->input;

            $task = $userid = $jinput->get('task', '', 'WORD');
            $albumId = $userid = $jinput->get('albumid', 0, 'INT');
            $photoId = $userid = $jinput->get('photoid', 0, 'INT');
            $groupId = $userid = $jinput->get('groupid', 0, 'INT');

            if (!empty($albumId)) {
                $album = JTable::getInstance('Album', 'CTable');
                $album->load($albumId);
                $groupId = $album->groupid;
            }

            //CFactory::load( 'models' , 'groups' );
            $config = CFactory::getConfig();
            $group = JTable::getInstance('Group', 'CTable');
            $group->load($groupId);

            $my = CFactory::getUser();
            $albumId = $albumId != 0 ? $albumId : '';

            // Check if the current user is banned from this group
            $isBanned = $group->isBanned($my->id);
            $allowManagePhotos = CGroupHelper::allowManagePhoto($this->groupid);

            if (($task == 'uploader' || $task == 'photo' || $task =='album') && !empty($albumId)) {
                $this->view->addSubmenuItem('index.php?option=com_community&view=photos&groupid=' . $this->groupid . '&task=' . ($photoId > 0 ? 'album' : 'display') . '&albumid=' . $albumId, JText::_('COM_COMMUNITY_PHOTOS_BACK_TO_ALBUM'));
            }

            if ($allowManagePhotos && $task != 'photo' && !$isBanned) {
                /* Group: Upload Photos */
                if ($task == 'album' && ( ($my->id == $album->creator && $allowManagePhotos ) || $group->isAdmin($my->id) || COwnerHelper::isCommunityAdmin() )) {

                    if(!CAlbumsHelper::isFixedAlbum($album)){
                        $this->view->addSubmenuItem('javascript:', JText::_('COM_COMMUNITY_PHOTOS_ALBUM_DELETE'), "joms.api.albumRemove('" . $album->id . "', '" . $task . "');", true);
                        $this->view->addSubmenuItem('index.php?option=com_community&view=photos&task=editAlbum&albumid=' . $album->id . '&groupid=' . $group->id . '&referrer=albumgrp', JText::_('COM_COMMUNITY_EDIT_ALBUM'), '', true);
                    }
                }

                //$this->view->addSubmenuItem('index.php?option=com_community&view=photos&task=newalbum&groupid=' . $this->groupid, JText::_('COM_COMMUNITY_PHOTOS_CREATE_PHOTO_ALBUM'), '', true, '', '');
            }

            if ($task == 'photo') {
                if ($album->hasAccess($my->id, 'deletephotos')) {
                    $this->view->addSubmenuItem('', JText::_('COM_COMMUNITY_PHOTOS_DELETE'), "joms_delete_photo();", true);
                }

                if ($my->id == $album->creator) {
                    $this->view->addSubmenuItem('', JText::_('COM_COMMUNITY_PHOTOS_SET_AVATAR'), "joms_set_as_profile_picture();", true);
                }

                if (($my->id == $album->creator && $allowManagePhotos ) || $group->isAdmin($my->id) || COwnerHelper::isCommunityAdmin()) {
                    $this->view->addSubmenuItem('', JText::_('COM_COMMUNITY_PHOTOS_SET_AS_ALBUM_COVER'), "joms_set_as_album_cover();", true);
                }

                if (!$config->get('deleteoriginalphotos')) {
                    $this->view->addSubmenuItem('', JText::_('COM_COMMUNITY_DOWNLOAD_IMAGE'), "joms_download_photo();", true);
                }

                if ($groupId != '' && $task=='myphotos') {
                    $this->view->addSubmenuItem('index.php?option=com_community&view=photos&task=newalbum&groupid=' . $groupId, JText::_('COM_COMMUNITY_PHOTOS_CREATE_PHOTO_ALBUM'), '', true);
                } elseif($task=='myphotos') {
                    $this->view->addSubmenuItem('index.php?option=com_community&view=photos&task=newalbum&userid=' . $my->id, JText::_('COM_COMMUNITY_PHOTOS_CREATE_PHOTO_ALBUM'), '', true);
              }
            }

            if($task == "display" && $task=='myphotos'){
                $this->view->addSubmenuItem('index.php?option=com_community&view=photos&task=newalbum&groupid=' . $groupId, JText::_('COM_COMMUNITY_PHOTOS_CREATE_PHOTO_ALBUM'), '', true);
            }


            //$this->view->addSubmenuItem('index.php?option=com_community&view=groups&task=viewgroup&groupid=' . $this->groupid, JText::_('COM_COMMUNITY_GROUPS_BACK_TO_GROUP'));
        }

        /**
         * Deprecated since 1.8.9
         * */
        public function isPhotoBrowsable($photoId) {
            return $this->isAlbumBrowsable($photoId);
        }

        public function isAlbumOwner($albumId) {

            if ($this->my->id == 0)
                return false;

            $group = JTable::getInstance('Group', 'CTable');
            $group->load($this->groupid);

            $album = JTable::getInstance('Album', 'CTable');
            $album->load($albumId);

            if ($album->creator == $this->my->id || COwnerHelper::isCommunityAdmin()) {
                return true;
            }

            return false;
        }

        public function isAlbumBrowsable($albumId) {
            $album = JTable::getInstance('Album', 'CTable');
            $album->load($albumId);

            $group = JTable::getInstance('Group', 'CTable');
            $group->load($album->groupid);

            $document = JFactory::getDocument();
            $mainframe = JFactory::getApplication();

            //@rule: Do not allow non members to view albums for private group
            if ($group->approvals == COMMUNITY_PRIVATE_GROUP && !$group->isMember($this->my->id) && !$group->isAdmin($this->my->id) && !COwnerHelper::isCommunityAdmin()) {
                /**
                 * Opengraph
                 */
                CHeadHelper::setType('website', JText::_('COM_COMMUNITY_RESTRICTED_ACCESS'));
                $mainframe->enqueueMessage(JText::_('COM_COMMUNITY_RESTRICTED_ACCESS', 'notice'));

                echo JText::_('COM_COMMUNITY_GROUPS_ALBUM_MEMBER_PERMISSION');
                return false;
            }

            return true;
        }

        public function getType() {
            return PHOTOS_GROUP_TYPE;
        }

        /**
         * Return the uri to the album view, given the album id
         */
        public function getAlbumURI($albumId) {
            return CRoute::_('index.php?option=com_community&view=photos&task=album&albumid=' . $albumId . '&groupid=' . $this->groupid);
        }

        public function getAlbumExternalURI($albumId) {
            return CRoute::getExternalURL('index.php?option=com_community&view=photos&task=album&albumid=' . $albumId . '&groupid=' . $this->groupid);
        }

        public function getPhotoURI($photoId, $albumId) {
            return CRoute::_('index.php?option=com_community&view=photos&task=photo&groupid=' . $this->groupid . '&albumid=' . $albumId . '&photoid=' . $photoId);
        }

        public function getPhotoExternalURI($photoId, $albumId) {
            return CRoute::getExternalURL('index.php?option=com_community&view=photos&task=photo&albumid=' . $albumId . '&groupid=' . $this->groupid . '&photoid=' . $photoId);
        }

        public function isExceedUploadLimit() {
            if (CLimitsHelper::exceededPhotoUpload($this->groupid, PHOTOS_GROUP_TYPE)) {
                $config = CFactory::getConfig();
                $photoLimit = $config->get('groupphotouploadlimit');

                echo JText::sprintf('COM_COMMUNITY_GROUPS_PHOTO_LIMIT', $photoLimit);
                return TRUE;
            }

            return FALSE;
        }

        /**
         * Return data for the 'all album' view
         */
        public function getAllAlbumData() {
            $my = CFactory::getUser();
            $group = JTable::getInstance('Group', 'CTable');
            $group->load($this->groupid);

            $type = PHOTOS_GROUP_TYPE;

            if(!$this->groupid){
                // if there is no id supplied, this should be listing all the group album that is visible to the current user
                $excludeType = array(
                    'group.default',
                    'group.gif',
                    'group.stream',
                    'group.cover',
                    'group.avatar'
                );

                $groupModel = CFactory::getModel('groups');
                $groupsId = $groupModel->getUserViewablegroups(CFactory::getUser()->id);
                $albumsData['data'] = $this->model->getGroupAlbums($groupsId, $type, false, '', false, '', $excludeType);
                $albumsData['pagination'] = $this->model->getPagination();
                return $albumsData;
            }

            //@rule: Do not allow non members to view albums for private group
            if ($group->approvals == COMMUNITY_PRIVATE_GROUP && !$group->isMember($my->id) && !$group->isAdmin($my->id)) {
                $this->noAccess();
                return FALSE;
            }

            $albumsData['data'] = $this->model->getGroupAlbums($this->groupid, $type);
            $albumsData['pagination'] = $this->model->getPagination();

            return $albumsData;
        }

        public function setMiniHeader() {
            // Do nothing because the mini header for groups are done on the view itself. Function is to satisfy the abstract.
        }

        public function setAlbumPathway($albumName) {
            $mainframe = JFactory::getApplication();
            $pathway = $mainframe->getPathway();
            $pathway->addItem($albumName, '');
        }

        public function getCreatorName() {
            $group = JTable::getInstance('Group', 'CTable');
            $group->load($this->groupid);

            return $group->name;
        }

    }

    class CommunityViewPhotosEventHandler extends CommunityViewPhotoHandler {

        private $eventid = null;

        /**
         * Constructor
         */
        public function __construct($viewObj) {
            parent::__construct($viewObj);

            $mainframe = JFactory::getApplication();
            $jinput = $mainframe->input;
            $this->eventid = $jinput->get('eventid', '', 'INT');
        }

        public function getFlashUploadURI($token, $albumId) {
            $session = JFactory::getSession();
            $url = 'index.php?option=com_community&view=photos&task=upload&no_html=1&albumid=' . $albumId . '&tmpl=component';
            $url .= '&' . $session->getName() . '=' . $session->getId() . '&token=' . $token->token . '&uploaderid=' . $this->my->id . '&eventid=' . $this->eventid;
            $url = JURI::root(true). '/' . $url;
            return $url;

//		$url = CRoute::_($url);
//		$uri = JURI::getInstance();
//		$uri = new JURI($uri->toString());
//		$uri->setPath($url);
//		$uri->setQuery('');
//		return $uri->toString();
        }

        public function getAlbumCreateLink() {
            return CRoute::_('index.php?option=com_community&view=photos&task=newalbum&eventid=' . $this->eventid);
        }

        public function isWallAllowed() {
            return $this->isTaggable();
        }

        public function isTaggable() {
            //CFactory::load( 'helpers' , 'owner' );
            //CFactory::load( 'models' , 'groups' );
            $event = JTable::getInstance('Event', 'CTable');
            $event->load($this->eventid);

            //check if we can allow the current viewing user to tag the photos
            if ($event->isMember($this->my->id) || $event->isAdmin($this->my->id) || COwnerHelper::isCommunityAdmin()) {
                return true;
            }
            return false;
        }

        public function getTaggingUsers() {
            // for photo tagging. only allow to tag members
            $model = CFactory::getModel('events');
            $ids = $model->getMembersId($this->eventid, true);
            $users = array();

            $event = JTable::getInstance('Event', 'CTable');
            $event->load($this->eventid);

            foreach ($ids as $id) {
                if ($this->my->id != $id) {
                    $user = CFactory::getUser($id);
                    $users[] = $user;
                }
            }

            //CFactory::load( 'helpers' , 'owner' );

            if (COwnerHelper::isCommunityAdmin() || $event->isAdmin($this->my->id) || $event->isMember($this->my->id))
                array_unshift($users, $this->my);

            return $users;
        }

        public function setRSSHeader($albumId) {
            return;
        }

        public function getAlbumPhotos($albumId) {
            $config = CFactory::getConfig();
            $model = CFactory::getModel('Photos');

            // @todo: make limit configurable?
            return $model->getAllPhotos($albumId, PHOTOS_EVENT_TYPE, null, null, $config->get('photosordering'));
        }

        public function setSubmenus() {
            //CFactory::load( 'helpers' , 'group' );
            //CFactory::load( 'helpers' , 'owner' );
            $jinput = JFactory::getApplication()->input;


            $task = $userid = $jinput->get('task', '', 'WORD');
            $albumId = $userid = $jinput->get('albumid', 0, 'INT');
            $eventid = $userid = $jinput->get('eventid', 0, 'INT');

            if (!empty($albumId)) {
                $album = JTable::getInstance('Album', 'CTable');
                $album->load($albumId);
                $eventid = $album->eventid;
            }

            $config = CFactory::getConfig();
            $event = JTable::getInstance('Event', 'CTable');
            $event->load($eventid);

            $my = CFactory::getUser();
            $albumId = $albumId != 0 ? $albumId : '';

            // Check if the current user is banned from this event
            $isBanned = false;
            $allowManagePhotos = CEventHelper::allowManagePhoto($this->eventid);

            if (($task == 'uploader' || $task == 'photo' || $task == 'album') && !empty($albumId)) {
                $this->view->addSubmenuItem('index.php?option=com_community&view=photos&eventid=' . $this->eventid . '&task=display&albumid=' . $albumId, JText::_('COM_COMMUNITY_PHOTOS_BACK_TO_ALBUM'));
            }

            if ($allowManagePhotos && $task != 'photo' && !$isBanned) {
                /* Event: Upload Photos */
                if ($task != 'newalbum' && $task != 'editAlbum') {
                    //$this->view->addSubmenuItem('javascript:void(0);', JText::_('COM_COMMUNITY_PHOTOS_UPLOAD_PHOTOS'), 'joms.notifications.showUploadPhoto(\'' . $albumId . '\',' . $this->eventid . '); return false;', true, '', '');
                }

                if ($task == 'album' && ( ($my->id == $album->creator && $allowManagePhotos ) || $event->isAdmin($my->id) || COwnerHelper::isCommunityAdmin() )) {

                    if(!CAlbumsHelper::isFixedAlbum($album)){
                        $this->view->addSubmenuItem('javascript:', JText::_('COM_COMMUNITY_PHOTOS_ALBUM_DELETE'), "joms.api.albumRemove('" . $album->id . "', '" . $task . "');", true);
                        $this->view->addSubmenuItem('index.php?option=com_community&view=photos&task=editAlbum&albumid=' . $album->id . '&eventid=' . $event->id . '&referrer=albumgrp', JText::_('COM_COMMUNITY_EDIT_ALBUM'), '', true);
                    }
                    //$this->view->addSubmenuItem('index.php?option=com_community&view=photos&task=group', JText::_('COM_COMMUNITY_PHOTOS_ALL_EVENT_PHOTOS'), '' , false , '' , 'joms-right');
                    //$this->view->addSubmenuItem('index.php?option=com_community&view=photos&task=event', JText::_('COM_COMMUNITY_PHOTOS_ALL_EVENT_PHOTOS'), '' , false , '' , 'joms-right');
                    //->view->addSubmenuItem('index.php?option=com_community&view=photos&task=display', JText::_('COM_COMMUNITY_PHOTOS_ALL_PHOTOS'), '' , false , '' , 'joms-right');
                    if ($my->id != 0) {
                        //$this->view->addSubmenuItem('index.php?option=com_community&view=photos&task=myphotos&userid=' . $my->id, JText::_('COM_COMMUNITY_PHOTOS_MY_PHOTOS'), '' , false , '' , 'joms-right');
                    }
                }

                //$this->view->addSubmenuItem('index.php?option=com_community&view=photos&task=newalbum&eventid=' . $this->eventid, JText::_('COM_COMMUNITY_PHOTOS_CREATE_PHOTO_ALBUM'), '', true, '', '');
            }

            if ($task == 'photo') {
                if ($album->hasAccess($my->id, 'deletephotos')) {
                    $this->view->addSubmenuItem('', JText::_('COM_COMMUNITY_PHOTOS_DELETE'), "joms_delete_photo();", true);
                }

                if ($my->id == $album->creator) {
                    $this->view->addSubmenuItem('', JText::_('COM_COMMUNITY_PHOTOS_SET_AVATAR'), "joms_set_as_profile_picture();", true);
                }

                if (($my->id == $album->creator && $allowManagePhotos ) || $event->isAdmin($my->id) || COwnerHelper::isCommunityAdmin()) {
                    $this->view->addSubmenuItem('', JText::_('COM_COMMUNITY_PHOTOS_SET_AS_ALBUM_COVER'), "joms_set_as_album_cover();", true);
                }

                if (!$config->get('deleteoriginalphotos')) {
                    $this->view->addSubmenuItem('', JText::_('COM_COMMUNITY_DOWNLOAD_IMAGE'), "joms_download_photo();", true);
                }

                if ($eventid != '' && $task=='myphotos') {
                    $this->view->addSubmenuItem('index.php?option=com_community&view=photos&task=newalbum&eventid=' . $eventid, JText::_('COM_COMMUNITY_PHOTOS_CREATE_PHOTO_ALBUM'), '', true);
                } elseif($task=='myphotos') {
                    $this->view->addSubmenuItem('index.php?option=com_community&view=photos&task=newalbum&userid=' . $my->id, JText::_('COM_COMMUNITY_PHOTOS_CREATE_PHOTO_ALBUM'), '', true);
                }
            }

            if($task == "display" && $task=='myphotos'){
                $this->view->addSubmenuItem('index.php?option=com_community&view=photos&task=newalbum&eventid=' . $eventid, JText::_('COM_COMMUNITY_PHOTOS_CREATE_PHOTO_ALBUM'), '', true);
            }


            //$this->view->addSubmenuItem('index.php?option=com_community&view=groups&task=viewgroup&eventid=' . $this->eventid, JText::_('COM_COMMUNITY_EVENTS_BACK_TO_EVENT'));
        }

        /**
         * Deprecated since 1.8.9
         * */
        public function isPhotoBrowsable($photoId) {
            return $this->isAlbumBrowsable($photoId);
        }

        public function isAlbumOwner($albumId) {

            if ($this->my->id == 0)
                return false;

            $event = JTable::getInstance('Event', 'CTable');
            $event->load($this->eventid);

            $album = JTable::getInstance('Album', 'CTable');
            $album->load($albumId);

            if ($album->creator == $this->my->id || COwnerHelper::isCommunityAdmin()) {
                return true;
            }

            return false;
        }

        public function isAlbumBrowsable($albumId) {
            $album = JTable::getInstance('Album', 'CTable');
            $album->load($albumId);

            $event = JTable::getInstance('Event', 'CTable');
            $event->load($album->eventid);

            if($event->type == 'group' && $event->contentid){
                $group = JTable::getInstance('Group', 'CTable');
                $group->load($event->contentid);
                $mainframe = JFactory::getApplication();

                //@rule: Do not allow non members to view albums for private group
                if ($group->approvals == COMMUNITY_PRIVATE_GROUP && !$group->isMember($this->my->id) && !$group->isAdmin($this->my->id) && !COwnerHelper::isCommunityAdmin()) {
                    /**
                     * Opengraph
                     */
                    CHeadHelper::setType('website', JText::_('COM_COMMUNITY_RESTRICTED_ACCESS'));
                    $mainframe->enqueueMessage(JText::_('COM_COMMUNITY_RESTRICTED_ACCESS', 'notice'));

                    echo JText::_('COM_COMMUNITY_GROUPS_ALBUM_MEMBER_PERMISSION');
                    return false;
                }
            }

            return true;
        }

        public function getType() {
            return PHOTOS_GROUP_TYPE;
        }

        /**
         * Return the uri to the album view, given the album id
         */
        public function getAlbumURI($albumId) {
            return CRoute::_('index.php?option=com_community&view=photos&task=album&albumid=' . $albumId . '&eventid=' . $this->eventid);
        }

        public function getAlbumExternalURI($albumId) {
            return CRoute::getExternalURL('index.php?option=com_community&view=photos&task=album&albumid=' . $albumId . '&eventid=' . $this->eventid);
        }

        public function getPhotoURI($photoId, $albumId) {
            return CRoute::_('index.php?option=com_community&view=photos&task=photo&eventid=' . $this->eventid . '&albumid=' . $albumId . '&photoid=' . $photoId);
        }

        public function getPhotoExternalURI($photoId, $albumId) {
            return CRoute::getExternalURL('index.php?option=com_community&view=photos&task=photo&albumid=' . $albumId . '&eventid=' . $this->eventid . '&photoid=' . $photoId);
        }

        public function isExceedUploadLimit() {
            if (CLimitsHelper::exceededPhotoUpload($this->groupid, PHOTOS_EVENT_TYPE)) {
                $config = CFactory::getConfig();
                $photoLimit = $config->get('eventphotouploadlimit');

                echo JText::sprintf('COM_COMMUNITY_EVENTS_PHOTO_LIMIT', $photoLimit);
                return TRUE;
            }

            return FALSE;
        }

        /**
         * Return data for the 'all album' view
         */
        public function getAllAlbumData() {
            $my = CFactory::getUser();
            $event = JTable::getInstance('Event', 'CTable');
            $event->load($this->eventid);
            $type = PHOTOS_EVENT_TYPE;

            if(!$this->eventid){
                // if there is no id supplied, this should be listing all the events album that is visible to the current user
                $excludeType = array(
                    'event.default',
                    'event.gif',
                    'event.stream',
                    'event.cover',
                    'event.avatar'
                );

                $eventModel = CFactory::getModel('events');
                $eventsId = $eventModel->getUserViewableEvents(CFactory::getUser()->id);
                $albumsData['data'] = $this->model->getEventAlbums($eventsId, $type, false, '', false, '', $excludeType);
                $albumsData['pagination'] = $this->model->getPagination();
                return $albumsData;
            }

            //check if this event belongs to a group
            if($event->type == 'group' && $event->contentid){
                $groupid = $event->contentid;
                $group = JTable::getInstance('Group', 'CTable');
                $group->load($groupid);

                //@rule: Do not allow non members to view albums for private group
                if ($group->approvals == COMMUNITY_PRIVATE_GROUP && !$group->isMember($my->id) && !$group->isAdmin($my->id)) {
                    $this->noAccess();
                    return FALSE;
                }
            }

            $allowShow = array(COMMUNITY_EVENT_STATUS_ATTEND,COMMUNITY_EVENT_STATUS_WONTATTEND,COMMUNITY_EVENT_STATUS_MAYBE);
            if(($event->permission == 1 || $event->unlisted == 1) && !in_array($event->getUserStatus($my->id),$allowShow)){
                //this is a invitation only group or unlisted event, so we only show covers album and exclude others
                $excludeType = array(
                    'event',
                    'event.gif',
                    'event.stream'
                );
                $albumsData['data'] = $this->model->getEventAlbums($this->eventid, false, false, '', false, '', $excludeType);
            }else{
                $albumsData['data'] = $this->model->getEventAlbums($this->eventid, $type);
            }


            $albumsData['pagination'] = $this->model->getPagination();

            return $albumsData;
        }

        public function setMiniHeader() {
            //echo CMiniHeader::showEventMiniHeader($this->eventid);
        }

        public function setAlbumPathway($albumName) {
            $mainframe = JFactory::getApplication();
            $pathway = $mainframe->getPathway();
            $pathway->addItem($albumName, '');
        }

        public function getCreatorName() {
            $event = JTable::getInstance('Event', 'CTable');
            $event->load($this->eventid);

            return $event->title;
        }

    }
}


