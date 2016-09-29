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
jimport('joomla.application.component.controller');

class CommunityVideosController extends CommunityBaseController
{

    var $_name = 'videos';

    public function checkVideoAccess($videoid = null) {
        $mainframe = JFactory::getApplication();
        $config = CFactory::getConfig();
        $jinput = $mainframe->input;
        $userId = $jinput->get('userid', 0, 'INT');

        $my = CFactory::getUser();
        $actor = CFactory::getuser($userId);

        // check privacy
        $allowed = true;

        // verify video-level privacy setting
        if($videoid) {
            if (!CPrivacy::isAccessAllowed($my->id, $actor->id, 'video', 'video', $videoid)) $allowed = false;
        } else {
            if (!CPrivacy::isAccessAllowed($my->id, $actor->id, 'privacyVideoView', 'privacyVideoView')) $allowed = false;
        }

        if(!$allowed) {

            echo "<div class=\"cEmpty cAlert\">" . JText::_('COM_COMMUNITY_PRIVACY_ERROR_MSG') . "</div>";
            return;
        }

        if (!$config->get('enablevideos')) {
            $redirect = CRoute::_('index.php?option=com_community&view=frontpage', false);
            $mainframe->redirect($redirect, JText::_('COM_COMMUNITY_VIDEOS_DISABLED'), 'warning');
        }
        return true;
    }

    public function ajaxRemoveFeatured($videoId)
    {
        $filter = JFilterInput::getInstance();
        $videoId = $filter->clean($videoId, 'int');

        $json = array();

        if (COwnerHelper::isCommunityAdmin()) {
            $model = CFactory::getModel('Featured');

            //CFactory::load( 'libraries' , 'featured' );
            $featured = new CFeatured(FEATURED_VIDEOS);
            $my = CFactory::getUser();

            if ($featured->delete($videoId)) {
                $json['success'] = true;
                $json['html'] = JText::_('COM_COMMUNITY_VIDEOS_REMOVED_FROM_FEATURED');
            } else {
                $json['error'] = JText::_('COM_COMMUNITY_VIDEOS_REMOVING_VIDEO_FROM_FEATURED_ERROR');
            }
        } else {
            $json['error'] = JText::_('COM_COMMUNITY_NOT_ALLOWED_TO_ACCESS_SECTION');
        }

        $this->cacheClean( array(COMMUNITY_CACHE_TAG_VIDEOS, COMMUNITY_CACHE_TAG_FEATURED, COMMUNITY_CACHE_TAG_ACTIVITIES) );

        die( json_encode($json) );
    }

    public function ajaxAddFeatured($videoId)
    {
        $filter = JFilterInput::getInstance();
        $videoId = $filter->clean($videoId, 'int');

        $json = array();

        $my = CFactory::getUser();
        if ($my->id == 0) {
            return $this->ajaxBlockUnregister();
        }

        if (COwnerHelper::isCommunityAdmin()) {
            $model = CFactory::getModel('Featured');

            if (!$model->isExists(FEATURED_VIDEOS, $videoId)) {
                //CFactory::load( 'libraries' , 'featured' );
                //CFactory::load( 'models' , 'videos' );
                $featured = new CFeatured(FEATURED_VIDEOS);
                $table = JTable::getInstance('Video', 'CTable');
                $table->load($videoId);
                $config = CFactory::getConfig();
                $limit = $config->get('featured' . FEATURED_VIDEOS . 'limit', 10);

                if ($featured->add($videoId, $my->id) === true) {
                    $json['success'] = true;
                    $json['html'] = JText::sprintf('COM_COMMUNITY_VIDEOS_IS_FEATURED', $table->title);
                } else {
                    $json['error'] = JText::sprintf('COM_COMMUNITY_VIDEOS_LIMIT_REACHED_FEATURED', $table->title, $limit);
                }
            } else {
                $json['error'] = JText::_('COM_COMMUNITY_VIDEOS_FEATURED_ERROR');
            }
        } else {
            $json['error'] = JText::_('COM_COMMUNITY_NOT_ALLOWED_TO_ACCESS_SECTION');
        }

        $this->cacheClean( array(COMMUNITY_CACHE_TAG_VIDEOS, COMMUNITY_CACHE_TAG_FEATURED, COMMUNITY_CACHE_TAG_ACTIVITIES) );

        die( json_encode($json) );
    }

    public function zencodercallback()
    {
        $jinput = JFactory::getApplication()->input;
        $videoId = $jinput->getInt('videoid', '');
        $videoName = $jinput->getString('videoname', '');

        $videoModel = CFactory::getModel('videos');
        $videoModel->zencoderCallback($videoId, $videoName);
        exit;

    }

    /**
     * Method is called from the reporting library. Function calls should be
     * registered here.
     *
     * return    String    Message that will be displayed to user upon submission.
     * */
    public function reportVideo($link, $message, $id)
    {
        //CFactory::load( 'libraries' , 'reporting' );
        $report = new CReportingLibrary();
        $config = CFactory::getConfig();
        $my = CFactory::getUser();

        if (!$config->get('enablereporting') || (($my->id == 0) && (!$config->get('enableguestreporting')))) {
            return '';
        }

        // Pass the link and the reported message
        $report->createReport(JText::_('COM_COMMUNITY_VIDEOS_ERROR'), $link, $message);

        // Add the action that needs to be called.
        $action = new stdClass();
        $action->label = 'COM_COMMUNITY_VIDEOS_UNPUBLISH';
        $action->method = 'videos,deleteVideo';
        $action->parameters = array($id, 0);
        $action->defaultAction = false;

        $report->addActions(array($action));
        return JText::_('COM_COMMUNITY_REPORT_SUBMITTED');
    }

    /**
     * Show all video within the system
     */
    public function display($cacheable = false, $urlparams = false)
    {
        $this->checkVideoAccess();
        $jinput = JFactory::getApplication()->input;
        $document = JFactory::getDocument();
        $viewType = $document->getType();
        $viewName = $jinput->get('view', $this->getName());
        $view = $this->getView($viewName, '', $viewType);

        echo $view->get(__FUNCTION__);
    }

    /**
     * Full application view
     */
    public function app()
    {
        $view = $this->getView('videos');
        echo $view->get('appFullView');
    }

    /**
     * Display all video by current user
     */
    public function myvideos()
    {
        $my = CFactory::getUser();
        $jinput = JFactory::getApplication()->input;
        if ($this->checkVideoAccess()) {
            $document = JFactory::getDocument();
            $viewType = $document->getType();
            $viewName = $jinput->get('view', $this->getName());
            $view = $this->getView($viewName, '', $viewType);

            $userid = $jinput->get('userid');
            $user = CFactory::getUser($userid);

            echo $view->get(__FUNCTION__, $user->id);
        }
    }

    /**
     * Display all video by current user
     */
    public function mypendingvideos()
    {
        if ($this->blockUnregister()) {
            return;
        }
        $this->checkVideoAccess();
        $jinput = JFactory::getApplication()->input;
        $document = JFactory::getDocument();
        $viewType = $document->getType();
        $viewName = $jinput->get('view', $this->getName());
        $view = $this->getView($viewName, '', $viewType);
        $user = CFactory::getUser();
        echo $view->get(__FUNCTION__, $user->id);
    }

    /**
     * Show the  'add' video page. It should just link to either link or upload
     */
    public function add()
    {
        if ($this->blockUnregister()) {
            return;
        }
        $this->checkVideoAccess();
        $jinput = JFactory::getApplication()->input;
        $document = JFactory::getDocument();
        $viewType = $document->getType();
        $viewName = $jinput->get('view', $this->getName());
        $view = $this->getView($viewName, '', $viewType);
        echo $view->get(__FUNCTION__);
    }

    /**
     * Show the add video link form
     * @return unknown_type
     */
    public function link()
    {
        // Check for request forgeries
        JSession::checkToken() or jexit(JText::_('COM_COMMUNITY_INVALID_TOKEN'));

        if ($this->blockUnregister()) {
            return;
        }
        $this->checkVideoAccess();
        $jinput = JFactory::getApplication()->input;

        $document = JFactory::getDocument();
        $viewType = $document->getType();
        $viewName = $jinput->get('view', $this->getName());
        $view = $this->getView($viewName, '', $viewType);

        // Preset the redirect url according to group type or user type
        $mainframe = JFactory::getApplication();
        $jinput = $mainframe->input;
        $redirect = CVideosHelper::getVideoReturnUrlFromRequest();
        $my = CFactory::getUser();

        // @rule: Do not allow users to add more videos than they are allowed to
        if (CLimitsLibrary::exceedDaily('videos')) {
            $mainframe->redirect($redirect, JText::_('COM_COMMUNITY_VIDEOS_LIMIT_REACHED'), 'error');
        }

        // Without CURL library, there's no way get the video information
        // remotely
        if (!CRemoteHelper::curlExists()) {
            $mainframe->redirect($redirect, JText::_('COM_COMMUNITY_CURL_NOT_EXISTS'));
        }

        // Determine if the video belongs to group or user and
        // assign specify value for checking accordingly
        $config = CFactory::getConfig();
        $creatorType = $jinput->get('creatortype', VIDEO_USER_TYPE, 'NONE');
        $groupid = $jinput->get('groupid', 0, 'INT');
        $eventid = $jinput->get('eventid', 0, 'INT');

        list($creatorType, $videoLimit) = $this->_manipulateParameter($groupid, $eventid, $config);

        $group = JTable::getInstance('Group', 'CTable');
        $group->load($groupid);

        $permission = $jinput->post->get('permissions', 0, 'NONE');

        // Do not allow video upload if user's video exceeded the limit
        if (CLimitsHelper::exceededVideoUpload($my->id, $creatorType)) {
            $message = JText::sprintf('COM_COMMUNITY_VIDEOS_CREATION_LIMIT_ERROR', $videoLimit);
            $mainframe->redirect($redirect, $message);
            exit;
        }

        // Create the video object and save
        $videoUrl = $jinput->get('videoLinkUrl', '', 'STRING');

        if (empty($videoUrl)) {
            $view->addWarning(JText::_('COM_COMMUNITY_VIDEOS_INVALID_VIDEO_LINKS'));
            echo $view->get(__FUNCTION__);
            exit;
        }

        $video = JTable::getInstance('Video', 'CTable');
        $isValid = $video->init($videoUrl);

        if (!$isValid) {
            $mainframe->redirect($redirect, $video->getProvider()->getError(), 'error');
            return;
        }

        $video->set('creator', $my->id);
        $video->set('creator_type', $creatorType);
        $video->set('permissions', $permission);
        $video->set('category_id', $jinput->post->get('category_id', '1', 'INT'));
        $video->set('location', $jinput->post->get('location', '', 'STRING'));
        $video->set('groupid', $groupid);
        $video->set('eventid', $eventid);

        /* make sure this video is not added before ( IE issue ) */
        if (!$video->isExist()) {
            if (!$video->store()) {
                $message = JText::_('COM_COMMUNITY_VIDEOS_ADD_LINK_FAILED');
                $mainframe->redirect($redirect, $message);
            }

            //add notification: New group video is added
            if ($video->groupid) {
                $group = JTable::getInstance('Group', 'CTable');
                $group->load($video->groupid);

                $modelGroup = $this->getModel('groups');
                $groupMembers = array();
                $groupMembers = $modelGroup->getMembersId($video->groupid, true);

                $params = new CParameter('');
                $params->set('title', $video->title);
                $params->set('group', $group->name);
                $params->set(
                    'group_url',
                    'index.php?option=com_community&view=groups&task=viewgroup&groupid=' . $group->id
                );
                $params->set('video', $video->title);
                $params->set(
                    'video_url',
                    'index.php?option=com_community&view=videos&task=videos&groupid=' . $group->id . '&videoid=' . $video->id
                );
                $params->set(
                    'url',
                    'index.php?option=com_community&view=videos&task=video&groupid=' . $group->id . '&videoid=' . $video->id
                );
                CNotificationLibrary::add(
                    'groups_create_video',
                    $my->id,
                    $groupMembers,
                    JText::sprintf('COM_COMMUNITY_GROUP_NEW_VIDEO_NOTIFICATION'),
                    '',
                    'groups.video',
                    $params
                );
            }

            // Trigger for onVideoCreate
            $this->_triggerEvent('onVideoCreate', $video);

            // Fetch the thumbnail and store it locally,
            // else we'll use the thumbnail remotely
            CError::assert($video->thumb, '', '!empty');
            $this->_fetchThumbnail($video->id);

            // Add activity logging
            $url = $video->getViewUri(false);

            $act = new stdClass();
            $act->cmd = 'videos.linking';
            $act->actor = $my->id;
            $act->access = $video->permissions;
            $act->target = 0;
            $act->title = '';
            $act->app = 'videos.linking';
            $act->content = '';
            $act->cid = $video->id;
            $act->location = $video->location;

            $act->comment_id = $video->id;
            $act->comment_type = 'videos.linking';

            $act->like_id = $video->id;
            $act->like_type = 'videos.linking';

            $act->groupid = ($video->groupid) ? $video->groupid : 0;
            $act->eventid = ($video->eventid) ? $video->eventid : 0;

            $params = new CParameter('');
            $params->set('video_url', $url);
            $params->set('style', COMMUNITY_STREAM_STYLE); // set stream style

            CActivityStream::add($act, $params->toString());
            CUserPoints::assignPoint('video.add', $video->creator);

            $this->cacheClean(
                array(
                    COMMUNITY_CACHE_TAG_VIDEOS,
                    COMMUNITY_CACHE_TAG_FRONTPAGE,
                    COMMUNITY_CACHE_TAG_FEATURED,
                    COMMUNITY_CACHE_TAG_VIDEOS_CAT,
                    COMMUNITY_CACHE_TAG_ACTIVITIES,
                    COMMUNITY_CACHE_TAG_GROUPS_DETAIL
                )
            );

            // Redirect user to his/her video page
            $message = JText::sprintf('COM_COMMUNITY_VIDEOS_UPLOAD_SUCCESS', $video->title);
            $mainframe->redirect($redirect, $message);
        }

        $id = $video->isExist();
        $video->load($id);

        $message = JText::sprintf('COM_COMMUNITY_VIDEOS_LINK_EXIST_WARNING', $video->getPermalink());
        $mainframe->redirect($redirect, $message);
    }

    private function _triggerEvent($event, $args)
    {
        // Trigger for onVideoCreate
        //CFactory::load( 'libraries' , 'apps' );
        $apps = CAppPlugins::getInstance();
        $apps->loadApplications();
        $params = array();
        $params[] = $args;
        $apps->triggerEvent($event, $params);
    }

    public function uploadVideo()
    {
        if ($this->blockUnregister()) {
            return;
        }
        $this->checkVideoAccess();

        $document = JFactory::getDocument();
        $viewType = $document->getType();
        $mainframe = JFactory::getApplication();
        $jinput = $mainframe->input;
        $my = CFactory::getUser();
        $creatorType = $jinput->get('creatortype', VIDEO_USER_TYPE, 'NONE'); //can be a user or group
        $groupid = ($creatorType == VIDEO_GROUP_TYPE) ? $jinput->getInt('groupid', 0) : 0;
        $eventid = ($creatorType == VIDEO_EVENT_TYPE) ? $jinput->getInt('eventid', 0) : 0;
        $targetid = $jinput->get('target', 0, 'INT');// this is for video posting at others profile
        $config = CFactory::getConfig();
        $title = $jinput->get('title', '', 'STRING');

        $returnStats = array();

        //check
        if (CLimitsLibrary::exceedDaily('videos')) {
            $returnStats['status'] = 'error';
            $returnStats['message'] = JText::_('COM_COMMUNITY_VIDEOS_LIMIT_REACHED');
            echo json_encode($returnStats);
            exit;
        }

        // Process according to video creator type
        if ($groupid != 0 || $eventid != 0) {
            list($creatorType, $videoLimit) = $this->_manipulateParameter($groupid, $eventid, $config);
        } else {
            $videoLimit = $config->get('videouploadlimit');
        }

        $permission = $jinput->post->getInt('permissions', 0);

        // Check is video upload is permitted
        if (CLimitsHelper::exceededVideoUpload($my->id, $creatorType)) {
            $returnStats['status'] = 'error';
            $returnStats['message'] = JText::sprintf('COM_COMMUNITY_VIDEOS_CREATION_LIMIT_ERROR', $videoLimit);
            echo json_encode($returnStats);
            exit;
        } else {
            if (!$config->get('enablevideos')) {
                $returnStats['status'] = 'error';
                $returnStats['message'] = JText::_('COM_COMMUNITY_VIDEOS_VIDEO_DISABLED');
                echo json_encode($returnStats);
                exit;
            } else {
                if (!$config->get('enablevideosupload')) {
                    $returnStats['status'] = 'error';
                    $returnStats['message'] = JText::_('COM_COMMUNITY_VIDEOS_UPLOAD_DISABLED');
                    echo json_encode($returnStats);
                    exit;
                }
            }
        }

        // Check if the video file is valid
        $files = $jinput->files->getArray();
        $videoFile = !empty($files['file']) ? $files['file'] : array();

        // #976 Fix mime-type issue
        if (isset($videoFile['type']) && (strtolower($videoFile['type']) === 'audio/mp4')) {
            $videoFile['type'] = 'video/mp4';
        }

        if (empty($files) || (empty($videoFile['name']) && $videoFile['size'] < 1)) {
            $returnStats['status'] = 'error';
            $returnStats['message'] = JText::_('COM_COMMUNITY_VIDEOS_UPLOAD_ERROR');
        }

        // Check file type.

        $fileType = CVideosHelper::getMIMEType($videoFile);

        $allowable = CVideosHelper::getValidMIMEType();
        if (!in_array(strtolower($fileType), $allowable)) {
            $returnStats['status'] = 'error';
            $returnStats['message'] = JText::sprintf('COM_COMMUNITY_VIDEOS_FILETYPE_ERROR', $fileType);
            echo json_encode($returnStats);
            exit;
        }

        $fileExtension = pathinfo($videoFile['name']);
        $allowextension = CVideosHelper::getValidExtensionType();

        if (!in_array(strtolower($fileExtension['extension']), $allowextension)) {
            $returnStats['status'] = 'error';
            $returnStats['message'] = JText::sprintf(
                'COM_COMMUNITY_VIDEOS_FILETYPE_ERROR',
                $fileExtension['extension']
            );
            echo json_encode($returnStats);
            exit;
        }

        // Check if the video file exceeds file size limit
        $uploadLimit = $config->get('maxvideouploadsize'); /* MB unit */
        $videoFileSize = sprintf("%u", filesize($videoFile['tmp_name']));

        //convert to MB
        $videoFileSize = number_format($videoFileSize / 1048576, 2);

        if (($uploadLimit > 0) && ($videoFileSize > $uploadLimit)) {
            $returnStats['status'] = 'error';
            $returnStats['message'] = JText::sprintf('COM_COMMUNITY_VIDEOS_FILE_SIZE_EXCEEDED', $uploadLimit);
            echo json_encode($returnStats);
            exit;
        }

        // Passed all checking, attempt to save the video file

        $folderPath = CVideoLibrary::getPath($my->id, 'original');
        $randomFileName = CFileHelper::getRandomFilename($folderPath, $videoFile['name'], '');
        $destination = JPATH::clean($folderPath . '/' . $randomFileName);

        if (!CFileHelper::upload($videoFile, $destination)) {
            $returnStats['status'] = 'error';
            $returnStats['message'] = JText::_('COM_COMMUNITY_VIDEOS_UPLOAD_ERROR');
            echo json_encode($returnStats);
            exit;
        }

        $config = CFactory::getConfig();
        $videofolder = $config->get('videofolder');

        //get the file name from the uploaded item
        $ext = explode('.', $videoFile['name']);
        $ext = '.' . $ext[count($ext) - 1];
        $filename = basename($videoFile['name'], $ext);
        $filename = ($filename == '') ? JText::_('COM_COMMUNITY_VIDEOS_TITLE_EMPTY') : $filename;



        //CFactory::load( 'models' , 'videos' );
        $video = JTable::getInstance('Video', 'CTable');
        $video->set('path', $videofolder . '/originalvideos/' . $my->id . '/' . $randomFileName);
        $video->set('title',($title != '') ? $title : $filename);
        $video->set('description', $jinput->post->get('description', '', 'STRING'));
        $video->set('category_id', $jinput->post->getInt('category_id', 0));
        $video->set('permissions', $permission);
        $video->set('creator', $my->id);
        $video->set('creator_type', $creatorType);
        $video->set('groupid', $groupid);
        $video->set('eventid', $eventid);
        $video->set('location', $jinput->get('location', '', 'STRING'));
        $video->set('filesize', $videoFileSize);

        // add the user indicator in the param if this video is posted to others profile.
        if($targetid){
            $params = new CParameter();
            $params->set('target_id', $targetid);
            $video->set('params', $params->toString());
        }

        if (!$video->store()) {
            $returnStats['status'] = 'error';
            $returnStats['message'] = JText::_('COM_COMMUNITY_VIDEOS_SAVE_ERROR');
            echo json_encode($returnStats);
            exit;
        }
        //add notification: New group album is added
        if ($video->groupid != 0) {

            $group = JTable::getInstance('Group', 'CTable');
            $group->load($video->groupid);

            $modelGroup = $this->getModel('groups');
            $groupMembers = array();
            $groupMembers = $modelGroup->getMembersId($video->groupid, true);

            $params = new CParameter('');
            $params->set('title', $video->title);
            $params->set('group', $group->name);
            $params->set(
                'group_url',
                'index.php?option=com_community&view=groups&task=viewgroup&groupid=' . $group->id
            );
            $params->set('url', 'index.php?option=com_community&view=videos&task=video&videoid=' . $video->id);
            CNotificationLibrary::add(
                'groups_create_video',
                $my->id,
                $groupMembers,
                JText::sprintf('COM_COMMUNITY_GROUP_NEW_VIDEO_NOTIFICATION', '{actor}', '{group}'),
                '',
                'groups.video',
                $params
            );
        }

        // Trigger for onVideoCreate
        $this->_triggerEvent('onVideoCreate', $video);

        $this->cacheClean(
            array(
                COMMUNITY_CACHE_TAG_VIDEOS,
                COMMUNITY_CACHE_TAG_FRONTPAGE,
                COMMUNITY_CACHE_TAG_FEATURED,
                COMMUNITY_CACHE_TAG_VIDEOS_CAT,
                COMMUNITY_CACHE_TAG_ACTIVITIES,
                COMMUNITY_CACHE_TAG_GROUPS_DETAIL
            )
        );

        // Video saved, redirect

        if (!isset($returnStats['status'])) {
            $returnStats['status'] = 'success';
            $returnStats['message'] = JText::sprintf('COM_COMMUNITY_VIDEOS_UPLOAD_SUCCESS', $video->title);
            $returnStats['videoid'] = $video->id;
            $returnStats['processing_str'] = JText::_('COM_COMMUNITY_VIDEOS_IN_PROGRESS');
        }

        echo json_encode($returnStats);
        exit;
    }

    public function upload()
    {
        // Check for request forgeries
        JSession::checkToken() or jexit(JText::_('COM_COMMUNITY_INVALID_TOKEN'));

        if ($this->blockUnregister()) {
            return;
        }
        $this->checkVideoAccess();
        $jinput = JFactory::getApplication()->input;
        $document = JFactory::getDocument();
        $viewType = $document->getType();
        $viewName = $jinput->get('view', $this->getName());
        $view = $this->getView($viewName, '', $viewType);
        $mainframe = JFactory::getApplication();
        $my = CFactory::getUser();
        $creatorType = $jinput->get(
            'creatortype',
            VIDEO_USER_TYPE,
            'NONE'
        );
        $groupid = $jinput->get( 'groupid', 0, 'INT' );
        $eventid = $jinput->get( 'eventid', 0, 'INT' );
        $config = CFactory::getConfig();


        $redirect = CVideosHelper::getVideoReturnUrlFromRequest();

        // @rule: Do not allow users to add more videos than they are allowed to
        //CFactory::load( 'libraries' , 'limits' );

        if (CLimitsLibrary::exceedDaily('videos')) {
            $mainframe->redirect($redirect, JText::_('COM_COMMUNITY_VIDEOS_LIMIT_REACHED'), 'error');
        }

        // Process according to video creator type
        list($creatorType, $videoLimit) = $this->_manipulateParameter($groupid, $eventid, $config);
        $group = JTable::getInstance('Group', 'CTable');
        $group->load($groupid);

        if ($group->approvals) {
            $permission = 40;
        }
        $permission = $jinput->getInt('permissions', 0);

        // Check is video upload is permitted

        if (CLimitsHelper::exceededVideoUpload($my->id, $creatorType)) {
            $message = JText::sprintf('COM_COMMUNITY_VIDEOS_CREATION_LIMIT_ERROR', $videoLimit);
            $mainframe->redirect($redirect, $message);
            exit;
        }
        if (!$config->get('enablevideos')) {
            $mainframe->enqueueMessage(JText::_('COM_COMMUNITY_VIDEOS_VIDEO_DISABLED', 'notice'));
            $mainframe->redirect(CRoute::_('index.php?option=com_community&view=frontpage', false));
            exit;
        }
        if (!$config->get('enablevideosupload')) {
            $mainframe->enqueueMessage(JText::_('COM_COMMUNITY_VIDEOS_UPLOAD_DISABLED', 'notice'));
            $mainframe->redirect(CRoute::_('index.php?option=com_community&view=videos', false));
            exit;
        }

        // Check if the video file is valid
        $files = $jinput->files->getArray();
        $videoFile = !empty($files['videoFile']) ? $files['videoFile'] : array();

        // #976 Fix mime-type issue
        if (isset($videoFile['type']) && (strtolower($videoFile['type']) === 'audio/mp4')) {
            $videoFile['type'] = 'video/mp4';
        }

        if (empty($files) || (empty($videoFile['name']) && $videoFile['size'] < 1)) {
            $mainframe->enqueueMessage(JText::_('COM_COMMUNITY_VIDEOS_UPLOAD_ERROR', 'error'));
            $mainframe->redirect($redirect, false);
            exit;
        }

        // Check file type.

        $fileType = CVideosHelper::getMIMEType($videoFile);

        $allowable = CVideosHelper::getValidMIMEType();
        if (!in_array(strtolower($fileType), $allowable)) {
            $mainframe->redirect($redirect, JText::sprintf('COM_COMMUNITY_VIDEOS_FILETYPE_ERROR', $fileType));
            exit;
        }

        $fileExtension = pathinfo($videoFile['name']);
        $allowextension = CVideosHelper::getValidExtensionType();

        if (!in_array(strtolower($fileExtension['extension']), $allowextension)) {
            $mainframe->redirect(
                $redirect,
                JText::sprintf('COM_COMMUNITY_VIDEOS_FILETYPE_ERROR', $fileExtension['extension'])
            );
            exit;
        }

        // Check if the video file exceeds file size limit
        $uploadLimit = $config->get('maxvideouploadsize'); /* MB unit */
        $videoFileSize = sprintf("%u", filesize($videoFile['tmp_name']));

        //convert to MB
        $videoFileSize = number_format($videoFileSize / 1048576, 2);

        if (($uploadLimit > 0) && ($videoFileSize > $uploadLimit)) {
            $mainframe->redirect($redirect, JText::sprintf('COM_COMMUNITY_VIDEOS_FILE_SIZE_EXCEEDED', $uploadLimit));
        }

        // Passed all checking, attempt to save the video file

        $folderPath = CVideoLibrary::getPath($my->id, 'original');
        $randomFileName = CFileHelper::getRandomFilename($folderPath, $videoFile['name'], '');
        $destination = JPATH::clean($folderPath . '/' . $randomFileName);

        if (!CFileHelper::upload($videoFile, $destination)) {
            $mainframe->enqueueMessage(JText::_('COM_COMMUNITY_VIDEOS_UPLOAD_ERROR', 'error'));
            $mainframe->redirect($redirect, false);
            exit;
        }

        $config = CFactory::getConfig();
        $videofolder = $config->get('videofolder');

        //CFactory::load( 'models' , 'videos' );
        $video = JTable::getInstance('Video', 'CTable');
        $video->set('path', $videofolder . '/originalvideos/' . $my->id . '/' . $randomFileName);
        $video->set('title', $jinput->get('title', '', 'STRING'));
        $video->set('description', $jinput->get('description', '', 'STRING'));
        $video->set('category_id', $jinput->post->getInt('category_id', 0));
        $video->set('permissions', $permission);
        $video->set('creator', $my->id);
        $video->set('creator_type', $creatorType);
        $video->set('groupid', $groupid);
        $video->set('eventid', $eventid);
        $video->set('location', $jinput->get('location', '', 'STRING'));
        $video->set('filesize', $videoFileSize);

        if (!$video->store()) {
            $mainframe->enqueueMessage(JText::_('COM_COMMUNITY_VIDEOS_SAVE_ERROR', 'error'));
            $mainframe->redirect($redirect, false);
            exit;
        }
        //add notification: New group album is added
        if ($video->groupid != 0) {

            $group = JTable::getInstance('Group', 'CTable');
            $group->load($video->groupid);

            $modelGroup = $this->getModel('groups');
            $groupMembers = array();
            $groupMembers = $modelGroup->getMembersId($video->groupid, true);

            $params = new CParameter('');
            $params->set('title', $video->title);
            $params->set('group', $group->name);
            $params->set(
                'group_url',
                'index.php?option=com_community&view=groups&task=viewgroup&groupid=' . $group->id
            );
            $params->set('url', 'index.php?option=com_community&view=videos&task=video&videoid=' . $video->id);
            CNotificationLibrary::add(
                'groups_create_video',
                $my->id,
                $groupMembers,
                JText::sprintf('COM_COMMUNITY_GROUP_NEW_VIDEO_NOTIFICATION', '{actor}', '{group}'),
                '',
                'groups.video',
                $params
            );
        }

        // Trigger for onVideoCreate
        $this->_triggerEvent('onVideoCreate', $video);

        $this->cacheClean(
            array(
                COMMUNITY_CACHE_TAG_VIDEOS,
                COMMUNITY_CACHE_TAG_FRONTPAGE,
                COMMUNITY_CACHE_TAG_FEATURED,
                COMMUNITY_CACHE_TAG_VIDEOS_CAT,
                COMMUNITY_CACHE_TAG_ACTIVITIES,
                COMMUNITY_CACHE_TAG_GROUPS_DETAIL
            )
        );

        // Video saved, redirect
        $redirect = CVideosHelper::getVideoReturnUrlFromRequest('pending');
        $mainframe->redirect($redirect, JText::sprintf('COM_COMMUNITY_VIDEOS_UPLOAD_SUCCESS', $video->title));
    }

    /**
     * Displays the video
     * */
    public function video()
    {
        $jinput = JFactory::getApplication()->input;
        $videoid = $jinput->getInt('videoid');
        $this->checkVideoAccess($videoid);
        $document = JFactory::getDocument();
        $viewType = $document->getType();
        $viewName = $jinput->get('view', $this->getName());
        $view = $this->getView($viewName, '', $viewType);
        $my = CFactory::getUser();

        $this->cacheClean(array(COMMUNITY_CACHE_TAG_FRONTPAGE, COMMUNITY_CACHE_TAG_FEATURED));

        // Log user engagement
        CEngagement::log('video.display', $my->id);

        echo $view->get(__FUNCTION__);
    }

    /**
     * Controller method to remove a video
     */
    public function removevideo()
    {
        $mainframe = JFactory::getApplication();
        $jinput = $mainframe->input;
        $task = $jinput->post->get('currentTask', '', 'STRING');

        if ($task == 'video') {
            $task = 'myvideos';
        } /* after video deleted we can't display this video, than we redirect back to this user videos */

        $videoId = $jinput->post->get('videoid', '', 'INT');
        $userId = $jinput->post->get('userid', null, 'INT');
        $message = $this->deleteVideo($videoId, false); /* We will handle redirect with below code */

        if ($userId === null) {
            $url = CRoute::_('index.php?option=com_community&view=videos&task=' . $task, false);
        } else {
            /* redirect back to current requesting user */
            $url = CRoute::_(
                'index.php?option=com_community&view=videos&task=' . $task . '&userid=' . (int)$userId,
                false
            );
        }

        if ($message != false) {
            // Remove from activity stream
            CActivityStream::remove('videos', $videoId);
            $mainframe->redirect($url, $message);
        } else {
            $message = JText::_('COM_COMMUNITY_VIDEOS_DELETING_VIDEO_ERROR');
            $mainframe->redirect($url, $message);
        }
    }

    /**
     * Controller method to remove a video
     * @param type $videoId
     * @param type $redirect
     * @return type
     */
    public function deleteVideo($videoId = 0, $redirect = true)
    {
        if ($this->blockUnregister()) {
            return;
        }

        $video = JTable::getInstance('Video', 'CTable');
        $mainframe = JFactory::getApplication();
        $video->load((int)$videoId);

        if (!empty($video->groupid)) {
            $allowManageVideos = CGroupHelper::allowManageVideo($video->groupid);
            CError::assert($allowManageVideos, '', '!empty', __FILE__, __LINE__);
        }

        // @rule: Add point when user removes a video
        CUserPoints::assignPoint('video.remove', $video->creator);

        if ($video->delete()) {
            // Delete all videos related data
            $this->_deleteVideoWalls($video->id);
            $this->_deleteVideoActivities($video->id);
            $this->_deleteFeaturedVideos($video->id);
            $this->_deleteVideoFiles($video);
            $this->_deleteProfileVideo($video->creator, $video->id);

            $db = JFactory::getDbo();
            //remove all stats from the video stats
            $query = "DELETE FROM ".$db->quoteName('#__community_video_stats')
                ." WHERE ".$db->quoteName('vid')."=".$db->quote($videoId);
            $db->setQuery($query);
            $db->execute();

            if (!empty($video->groupid)) {
                $message = JText::sprintf('COM_COMMUNITY_VIDEOS_REMOVED', $video->title);
                $redirect = CRoute::_('index.php?option=com_community&view=videos&task=display&groupid=' . $video->groupid, false);
            } else {
                $message = JText::sprintf('COM_COMMUNITY_VIDEOS_REMOVED', $video->title);
                $redirect = CRoute::_('index.php?option=com_community&view=videos', false);
            }
        }

        /**
         * @todo Should we return bool instead message string ?
         * @todo There is no process if $video->delete failed
         */
        if ($redirect === true) {
            $mainframe->redirect($redirect, $message);
        }

        return $message;
    }

    public function saveVideo()
    {
        // Check for request forgeries
        JSession::checkToken() or jexit(JText::_('COM_COMMUNITY_INVALID_TOKEN'));
        $jinput = JFactory::getApplication()->input;

        if ($this->blockUnregister()) {
            return;
        }

        $my = CFactory::getUser();
        $postData = $jinput->post->getArray();
        $mainframe = JFactory::getApplication();

        $video = JTable::getInstance('Video', 'CTable');
        $video->load($postData['id']);


        $redirect = CVideosHelper::getVideoReturnUrlFromRequest();

        if (!($video->bind($postData) && $video->store())) {
            $message = JText::_('COM_COMMUNITY_VIDEOS_SAVE_VIDEO_FAILED', 'error');
            $mainframe->redirect($redirect, $message);
        }

        // update permissions in activity streams as well
        $activityModel = CFactory::getModel('activities');
        $activityModel->updatePermission($video->permissions, null, $my->id, 'videos', $video->id);

        //update location in activity stream
        $data = array('app' => 'videos', 'cid' => $video->id);
        $update = array('location' => $postData['location']);
        $activityModel->update($data, $update);

        $message = JText::sprintf('COM_COMMUNITY_VIDEOS_SAVED', $video->title);
        $mainframe->redirect($redirect, $message);
    }

    public function ajaxFetchThumbnailMultiple()
    {
        $mainframe = JFactory::getApplication();
        $jinput = $mainframe->input;

        $ids = $jinput->post->get('ids',null, 'array');

        $filter = JFilterInput::getInstance();
        $ids = $filter->clean($ids,'array');

        $success = 0;
        $failure = 0;

        if (!COwnerHelper::isRegisteredUser())
        {
           return $this->ajaxBlockUnregister();
        }

        if(count($ids))
        {
            foreach ($ids as $id) {
                if ($this->_fetchThumbnail($id, false)) {
                    $success++;
                } else {
                    $failure++;
                }
            }
        }

        $response=array('success' => $success, 'failure' => $failure);
        die(json_encode($response));
    }

    public function ajaxFetchThumbnail($id, $return=true)
    {
        $filter = JFilterInput::getInstance();
        $id = $filter->clean($id, 'int');

        if (!COwnerHelper::isRegisteredUser()) {
            return $this->ajaxBlockUnregister();
        }

        $json = array();

        $thumbnail = $this->_fetchThumbnail($id, true);
        if (!$thumbnail) {
            $json['error'] = $this->getError() ? $this->getError() : 'Failed to fetch video thumbnail.';
        } else {
            $json['success'] = true;
            $json['message'] = JText::_('COM_COMMUNITY_VIDEOS_FETCH_THUMBNAIL_SUCCESS');
            $json['thumbnail'] = $thumbnail;
        }

        $json['title'] = JText::_('COM_COMMUNITY_VIDEOS_FETCH_THUMBNAIL');

        die( json_encode($json) );
    }

    /**
     * @param $message    A message that is submitted by the user
     * @param $uniqueId    The unique id for this group
     * @param int $photoId
     */
    public function ajaxSaveWall($message, $uniqueId, $photoId = 0)
    {
        $filter = JFilterInput::getInstance();
        $uniqueId = $filter->clean($uniqueId, 'int');
        $photoId = $filter->clean($photoId, 'int');

        if (!COwnerHelper::isRegisteredUser()) {
            return $this->ajaxBlockUnregister();
        }

        $response = new JAXResponse();
        $json = array();

        $my = CFactory::getUser();

        $video = JTable::getInstance('Video', 'CTable');
        $video->load($uniqueId);

        // If the content is false, the message might be empty.
        if (empty($message) && $photoId == 0) {
            $json['error'] = JText::_('COM_COMMUNITY_WALL_EMPTY_MESSAGE');
        } else {
            $config = CFactory::getConfig();

            // @rule: Spam checks
            if ($config->get('antispam_akismet_walls')) {
                //CFactory::load( 'libraries' , 'spamfilter' );

                $filter = CSpamFilter::getFilter();
                $filter->setAuthor($my->getDisplayName());
                $filter->setMessage($message);
                $filter->setEmail($my->email);
                $filter->setURL(
                    CRoute::_('index.php?option=com_community&view=videos&task=video&videoid=' . $uniqueId)
                );
                $filter->setType('message');
                $filter->setIP($_SERVER['REMOTE_ADDR']);

                if ($filter->isSpam()) {
                    $json['error'] = JText::_('COM_COMMUNITY_WALLS_MARKED_SPAM');
                    die( json_encode($json) );
                }
            }

            //CFactory::load( 'libraries' , 'wall' );
            $wall = CWallLibrary::saveWall(
                $uniqueId,
                $message,
                'videos',
                $my,
                ($my->id == $video->creator),
                'videos,video',
                'wall/content',
                0,
                $photoId
            );

            // Add activity logging
            $url = $video->getViewUri(false);

            $params = new CParameter('');
            $params->set('videoid', $uniqueId);
            $params->set('action', 'wall');
            $params->set('wallid', $wall->id);
            $params->set('video_url', $url);

            $act = new stdClass();
            $act->cmd = 'videos.wall.create';
            $act->actor = $my->id;
            $act->access = $video->permissions;
            $act->target = 0;
            $act->title = JText::sprintf(
                'COM_COMMUNITY_VIDEOS_ACTIVITIES_WALL_POST_VIDEO',
                '{video_url}',
                $video->title
            );
            $act->app = 'videos.comment';
            $act->cid = $uniqueId;
            $act->params = $params->toString();
            $act->groupid = $video->groupid;
            $act->eventid = $video->eventid;

            CActivityStream::add($act);
            // Add notification
            //CFactory::load( 'libraries' , 'notification' );

            $params = new CParameter('');
            $params->set('url', $url);
            $params->set('message', CUserHelper::replaceAliasURL($message));
            $params->set('video', $video->title);
            $params->set('video_url', $url);
            if ($my->id !== $video->creator) {
                CNotificationLibrary::add(
                    'videos_submit_wall',
                    $my->id,
                    $video->creator,
                    JText::sprintf('COM_COMMUNITY_VIDEO_WALL_EMAIL_SUBJECT'),
                    '',
                    'videos.wall',
                    $params
                );
            } else {
                //for activity reply action
                //get relevent users in the activity
                $wallModel = CFactory::getModel('wall');
                $users = $wallModel->getAllPostUsers('videos', $video->id, $video->creator);
                if (!empty($users)) {
                    CNotificationLibrary::add(
                        'videos_reply_wall',
                        $my->id,
                        $users,
                        JText::sprintf('COM_COMMUNITY_VIDEO_WALLREPLY_EMAIL_SUBJECT'),
                        '',
                        'videos.wallreply',
                        $params
                    );
                }
            }

            //email and add notification if user are tagged
            $info = array(
                'type' => 'video-comment',
                'video_id' => $video->id
            );
            CUserHelper::parseTaggedUserNotification($message, CFactory::getUser($video->creator), $wall, $info);

            // Add user points
            CUserPoints::assignPoint('videos.comment');

            //@since 4.1 we dump the info into photo stats
            $statsModel = CFactory::getModel('stats');
            $statsModel->addVideoStats($video->id,'comment');

            // Log user engagement
            CEngagement::log('video.comment', $my->id);

            $json['html'] = $wall->content;
            $json['success'] = true;
        }

        $this->cacheClean(array(COMMUNITY_CACHE_TAG_ACTIVITIES));

        die( json_encode($json) );

    }

    public function ajaxRemoveWall($wallId)
    {
        require_once(JPATH_COMPONENT . '/libraries/activities.php');

        $filter = JFilterInput::getInstance();
        $wallId = $filter->clean($wallId, 'int');

        //CFactory::load( 'helpers' , 'owner' );

        if (!COwnerHelper::isRegisteredUser()) {
            return $this->ajaxBlockUnregister();
        }

        // Only allow wall removal by admin or owner of the video.
        $response = new JAXResponse();
        $json = array();

        $wallsModel = $this->getModel('wall');
        $wall = $wallsModel->get($wallId);
        $video = JTable::getInstance('Video', 'CTable');
        $video->load($wall->contentid);
        $my = CFactory::getUser();

        if (COwnerHelper::isCommunityAdmin() || ($my->id == $video->creator)) {
            if ($wallsModel->deletePost($wallId)) {
                // Remove activity wall.
                CActivities::removeWallActivities(
                    array('app' => 'videos', 'cid' => $wall->contentid, 'createdAfter' => $wall->date),
                    $wallId
                );

                if ($wall->post_by != 0) {
                    //CFactory::load( 'libraries' , 'userpoints' );
                    CUserPoints::assignPoint('wall.remove', $wall->post_by);
                }
            } else {
                $json['error'] = JText::_('COM_COMMUNITY_GROUPS_REMOVE_WALL_ERROR');
            }
        } else {
            $json['error'] = JText::_('COM_COMMUNITY_GROUPS_REMOVE_WALL_ERROR');
        }

        if ( !$json['error'] ) {
            $json['success'] = true;
        }

        $this->cacheClean(array(COMMUNITY_CACHE_TAG_ACTIVITIES));

        die( json_encode($json) );
    }

    /**
     * Ajax method to display remove a video notice
     * @param int $id Video id
     * @param string $currentTask
     * @param int $userId
     * @return json
     */
    public function ajaxConfirmRemoveVideo($id)
    {
        $filter = JFilterInput::getInstance();
        $id = $filter->clean($id, 'int');

        if (!COwnerHelper::isRegisteredUser()) {
            return $this->ajaxBlockUnregister();
        }

        $my = CFactory::getUser();
        $video = JTable::getInstance('Video', 'CTable');
        $video->load($id);

        //This is final
        $this->cacheClean(
            array(
                COMMUNITY_CACHE_TAG_VIDEOS,
                COMMUNITY_CACHE_TAG_FRONTPAGE,
                COMMUNITY_CACHE_TAG_FEATURED,
                COMMUNITY_CACHE_TAG_VIDEOS_CAT,
                COMMUNITY_CACHE_TAG_ACTIVITIES
            )
        );

        $json = array(
            'html'   => JText::sprintf('COM_COMMUNITY_VIDEOS_REMOVE_VIDEO_CONFIRM', $video->title),
            'title'  => JText::_('COM_COMMUNITY_VIDEOS_DELETE_VIDEO'),
            'btnYes' => JText::_('COM_COMMUNITY_YES_BUTTON'),
            'btnNo'  => JText::_('COM_COMMUNITY_NO_BUTTON')
        );

        die( json_encode($json) );
    }

    public function ajaxRemoveVideo($id, $redirect = FALSE)
    {
        $filter = JFilterInput::getInstance();
        $id = $filter->clean($id, 'int');
        $redirect = $filter->clean($redirect, 'int');

        if (!COwnerHelper::isRegisteredUser()) {
            return $this->ajaxBlockUnregister();
        }

        $my = CFactory::getUser();
        $video = JTable::getInstance('Video', 'CTable');
        $video->load($id);

        $message = $this->deleteVideo($video->id, false);
        $json = array();

        if ($message != false) {
            CActivityStream::remove('videos', $video->id);

            // redirect url
            if ($redirect) {
                $url = 'index.php?option=com_community&view=videos';
                if ($video->eventid) {
                    $url .= '&task=display&eventid=' . $video->eventid;
                } else if ($video->groupid) {
                    $url .= '&task=display&groupid=' . $video->groupid;
                } else if ($video->creator) {
                    $url .= '&task=myvideos&userid=' . $video->creator;
                }
                $json['redirect'] = CRoute::_($url);
            }

            $json['success'] = true;
            $json['message'] = $message;
        } else {
            $message = JText::_('COM_COMMUNITY_VIDEOS_DELETING_VIDEO_ERROR');
            $json['error'] = $message;
        }

        die( json_encode($json) );
    }

    public function ajaxEditVideo($videoId, $redirectUrl = '')
    {
        $filter = JFilterInput::getInstance();
        $videoId = $filter->clean($videoId, 'int');
        $redirectUrl = $filter->clean($redirectUrl, 'string');

        if (!COwnerHelper::isRegisteredUser()) {
            return $this->ajaxBlockUnregister();
        }

        $json = array();

        //CFactory::load( 'models' , 'videos' );
        $video = JTable::getInstance('Video', 'CTable');
        $my = CFactory::getUser();

        $video->load($videoId);

        $group = JTable::getInstance('Group', 'CTable');
        // Load the group, based on video's groupid, NOT the url
        $group->load($video->groupid);

        if (COwnerHelper::isCommunityAdmin() || $video->creator == $my->id || $group->isAdmin($my->id)) {
            $model = CFactory::getModel('videos');
            $category = $model->getAllCategories();


            $cTree = CCategoryHelper::getCategories($category);
            $categoryHTML = CCategoryHelper::getSelectList('videos', $cTree, $video->category_id);

            $showPrivacy = $video->groupid != 0 ? false : true;
            $cWindowsHeight = $video->groupid != 0 ? 280 : 350;

            $redirectUrl = empty($redirectUrl) ? '' : urlencode($redirectUrl);
            $config = CFactory::getConfig();

            $params = new CParameter($video->params);

            $tmpl = new CTemplate();
            $tmpl->set('video', $video);
            $tmpl->set('showPrivacy', $showPrivacy);
            $tmpl->set('categoryHTML', $categoryHTML);
            $tmpl->set('redirectUrl', $redirectUrl);
            $tmpl->set('isStreamVideo', $params->get('activity_id'));
            $tmpl->set('enableLocation', $config->get('videosmapdefault'));
            $contents = $tmpl->fetch('videos/edit');

            $json['html']      = $contents;
            $json['title']     = JText::_('COM_COMMUNITY_VIDEOS_EDIT_VIDEO');
            $json['btnSave']   = JText::_('COM_COMMUNITY_SAVE_BUTTON');
            $json['btnCancel'] = JText::_('COM_COMMUNITY_CANCEL_BUTTON');
        } else {
            $json['error'] = JText::_('COM_COMMUNITY_NOT_ALLOWED_TO_ACCESS_SECTION');
        }

        $this->cacheClean(
            array(
                COMMUNITY_CACHE_TAG_VIDEOS,
                COMMUNITY_CACHE_TAG_FEATURED,
                COMMUNITY_CACHE_TAG_FRONTPAGE,
                COMMUNITY_CACHE_TAG_VIDEOS_CAT,
                COMMUNITY_CACHE_TAG_ACTIVITIES
            )
        );

        die( json_encode($json) );
    }

    public function ajaxAddVideo($creatorType = VIDEO_USER_TYPE, $contextid = 0)
    {
        $filter = JFilterInput::getInstance();
        $contextid = $filter->clean($contextid, 'int');
        $creatorType = $filter->clean($creatorType, 'string');


        if (!COwnerHelper::isRegisteredUser()) {
            return $this->ajaxBlockUnregister();
        }
        $my = CFactory::getUser();

        $objResponse = new JAXResponse();
        $objResponse->addAssign('cwin_logo', 'innerHTML', JText::_('COM_COMMUNITY_VIDEOS_ADD'));

        $json = array();
        $json['title'] = JText::_('COM_COMMUNITY_VIDEOS_ADD');

        // @rule: Do not allow users to add more videos than they are allowed to
        $this->_checkUploadLimit();

        if ($creatorType != VIDEO_GROUP_TYPE && $creatorType != VIDEO_EVENT_TYPE) {
            $contextid = 0;
        }

        if ($creatorType == VIDEO_GROUP_TYPE) {
            $group = JTable::getInstance('Group', 'CTable');
            $group->load($contextid);
            $isBanned = $group->isBanned($my->id);

            if ($isBanned) {
                $json['error'] = JText::_('COM_COMMUNITY_GROUPS_VIDEO_BANNED');
                die( json_encode($json) );
            }
        } else if ($creatorType == VIDEO_EVENT_TYPE) {
            $event = JTable::getInstance('Group', 'CTable');
            $event->load($contextid);

            // Is there eny checking here?
        }

        $config = CFactory::getConfig();

        $videoUpload = '';
        $linkUpload = $this->getLinkVideoHtml($creatorType, $contextid);

        if ($config->get('enablevideosupload')) {
            $videoUpload = $this->getUploadVideoHtml($creatorType, $contextid);
        }

        $uploadLimit = $config->get('maxvideouploadsize', ini_get('upload_max_filesize'));

        $tmpl = new CTemplate();
        $tmpl->set('enableVideoUpload', $config->get('enablevideosupload'));
        $tmpl->set('uploadLimit', $uploadLimit);
        $tmpl->set('linkUploadHtml', $linkUpload);
        $tmpl->set('videoUploadHtml', $videoUpload);
        $tmpl->set('creatorType', $creatorType);
        $tmpl->set('contextid', $contextid);
        $html = $tmpl->fetch('videos.add');

        $json['html'] = $html;
        die( json_encode($json) );
    }

    public function ajaxLinkVideo($creatorType = VIDEO_USER_TYPE, $groupid = 0)
    {
        if (!COwnerHelper::isRegisteredUser()) {
            return $this->ajaxBlockUnregister();
        }

        $html = $this->getLinkVideoHtml($creatorType, $groupid);
        $action = '<button class="btn" onclick="joms.videos.submitLinkVideo();">' . JText::_(
                'COM_COMMUNITY_VIDEOS_LINK'
            ) . '</button>';

        $objResponse = new JAXResponse();
        $objResponse->addAssign('cwin_logo', 'innerHTML', JText::_('COM_COMMUNITY_VIDEOS_LINK'));
        $objResponse->addScriptCall('cWindowAddContent', $html, $action);
        $objResponse->addScriptCall('joms.privacy.init();');

        return $objResponse->sendResponse();
    }

    public function getLinkVideoHtml($creatorType = VIDEO_USER_TYPE, $contextid = 0)
    {
        $filter = JFilterInput::getInstance();
        $creatorType = $filter->clean($creatorType, 'string');
        $contextid = $filter->clean($contextid, 'int');

        $user = CFactory::getRequestUser();
        $params = $user->getParams();
        $permissions = $params->get('privacyVideoView');

        $model = CFactory::getModel('videos');
        $category = $model->getAllCategories();

        $cTree = CCategoryHelper::getCategories($category);
        $categories = CCategoryHelper::getSelectList('videos', $cTree, null, true);

        $config = CFactory::getConfig();
        list($totalVideos, $videoUploadLimit) = $this->_getParameter($creatorType, $config);

        //CFactory::load( 'libraries' , 'privacy' );
        $tmpl = new CTemplate();
        $tmpl->set('categories', $categories);
        $tmpl->set('creatorType', $creatorType);
        $tmpl->set('groupid', $creatorType == VIDEO_GROUP_TYPE ? $contextid : '');
        $tmpl->set('eventid', $creatorType == VIDEO_EVENT_TYPE ? $contextid : '');
        $tmpl->set('videoUploaded', $totalVideos);
        $tmpl->set('permissions', $permissions);
        $tmpl->set('videoUploadLimit', $videoUploadLimit);
        $tmpl->set('enableLocation', $config->get('videosmapdefault'));

        $html = $tmpl->fetch('videos.link');
        return $html;
    }

    public function ajaxLinkVideoPreview($videoUrl)
    {
        $filter = JFilterInput::getInstance();
        $videoUrl = $filter->clean($videoUrl, 'string');

        $objResponse = new JAXResponse();

        if (!JSession::checkToken()) {
            $objResponse->addScriptCall('__throwError', JText::_('COM_COMMUNITY_INVALID_TOKEN'));
            $objResponse->sendResponse();
        }

        $config = CFactory::getConfig();

        if (!$config->get('enablevideos')) {
            $objResponse->addScriptCall('__throwError', JText::_('COM_COMMUNITY_VIDEOS_DISABLED'));
            $objResponse->sendResponse();
        }

        $my = CFactory::getUser();

        // @rule: Do not allow users to add more videos than they are allowed to
        if (CLimitsLibrary::exceedDaily('videos')) {
            $objResponse->addScriptCall('__throwError', JText::_('COM_COMMUNITY_VIDEOS_LIMIT_REACHED'));
            $objResponse->sendResponse();
        }

        // Without CURL library, there's no way get the video information
        // remotely
        if (!CRemoteHelper::curlExists()) {
            $objResponse->addScriptCall('__throwError', JText::_('COM_COMMUNITY_CURL_NOT_EXISTS'));
            $objResponse->sendResponse();
        }

        // Determine if the video belongs to group or user and
        // assign specify value for checking accordingly
        $creatorType = VIDEO_USER_TYPE;

        $videoLimit = $config->get('videouploadlimit');

        if (CLimitsHelper::exceededVideoUpload($my->id, $creatorType)) {
            $objResponse->addScriptCall(
                '__throwError',
                JText::sprintf('COM_COMMUNITY_VIDEOS_CREATION_LIMIT_ERROR', $videoLimit)
            );
            $objResponse->sendResponse();
        }

        // Create the video object and save
        if (empty($videoUrl)) {
            $objResponse->addScriptCall('__throwError', JText::_('COM_COMMUNITY_VIDEOS_INVALID_VIDEO_LINKS'));
            $objResponse->sendResponse();
        }


        $videoLib = new CVideoLibrary();

        $video = JTable::getInstance('Video', 'CTable');
        $isValid = $video->init($videoUrl);

        if (!$isValid) {
            $objResponse->addScriptCall('__throwError', $video->getProvider()->getError());
            $objResponse->sendResponse();
        }

        $video->set('creator', $my->id);
        $video->set('creator_type', $creatorType);
        $video->set('category_id', 1);
        $video->set('status', 'temp');

        if (!$video->store()) {
            $objResponse->addScript('__throwError', JText::_('COM_COMMUNITY_VIDEOS_ADD_LINK_FAILED'));
            $objResponse->sendResponse();
        }

        // Fetch the thumbnail and store it locally,
        // else we'll use the thumbnail remotely
        CError::assert($video->thumb, '', '!empty');
        $this->_fetchThumbnail($video->id);

        $model = CFactory::getModel('videos');
        $category = $model->getAllCategories();

        $category = CCategoryHelper::getCategories($category);

        $attachment = new stdClass();
        $attachment->video = $video;
        $attachment->category = $category;

        $objResponse->addScriptCall('__callback', $attachment);

        $objResponse->sendResponse();
    }

    public function ajaxUploadVideo($creatorType = VIDEO_USER_TYPE, $groupid = 0)
    {
        if (!COwnerHelper::isRegisteredUser()) {
            return $this->ajaxBlockUnregister();
        }

        // @rule: Do not allow users to add more videos than they are allowed to
        $this->_checkUploadLimit();

        $html = $this->getUploadVideoHtml($creatorType, $groupid);

        $action = '<button class="btn btn-primary" onclick="joms.videos.submitUploadVideo();">' . JText::_(
                'COM_COMMUNITY_VIDEOS_UPLOAD'
            ) . '</button>';

        $objResponse = new JAXResponse();
        $objResponse->addAssign('cwin_logo', 'innerHTML', JText::_('COM_COMMUNITY_VIDEOS_UPLOAD'));
        $objResponse->addScriptCall('cWindowAddContent', $html, $action);
        $objResponse->addScriptCall('joms.privacy.init();');

        return $objResponse->sendResponse();
    }

    public function getUploadVideoHtml($creatorType = VIDEO_USER_TYPE, $contextid = 0)
    {
        $filter = JFilterInput::getInstance();
        $creatorType = $filter->clean($creatorType, 'string');
        $contextid = $filter->clean($contextid, 'int');
        $my = CFactory::getUser();
        $user = CFactory::getRequestUser();
        $params = $user->getParams();
        $permissions = $params->get('privacyVideoView');

        $model = CFactory::getModel('videos');
        $category = $model->getAllCategories();


        $cTree = CCategoryHelper::getCategories($category);
        $categories = CCategoryHelper::getSelectList('videos', $cTree, null, true);

        $config = CFactory::getConfig();
        $uploadLimit = $config->get('maxvideouploadsize', ini_get('upload_max_filesize'));

        list($totalVideos, $videoUploadLimit) = $this->_getParameter($creatorType, $config);

        $tmpl = new CTemplate();
        $tmpl->set('categories', $categories);
        $tmpl->set('uploadLimit', $uploadLimit);
        $tmpl->set('creatorType', $creatorType);
        $tmpl->set('groupid', $creatorType == VIDEO_GROUP_TYPE ? $contextid : '');
        $tmpl->set('eventid', $creatorType == VIDEO_EVENT_TYPE ? $contextid : '');
        $tmpl->set('permissions', $permissions);
        $tmpl->set('videoUploaded', $totalVideos);
        $tmpl->set('videoUploadLimit', $videoUploadLimit);
        $tmpl->set('enableLocation', $config->get('videosmapdefault'));

        $html = $tmpl->fetch('videos.upload');
        return $html;
    }

    /**
     * Display searching form for videos
     * */
    public function search()
    {
        $config = CFactory::getConfig();
        $mainframe = JFactory::getApplication();
        $jinput = JFactory::getApplication()->input;
        $my = CFactory::getUser();

        if ($my->id == 0 && !$config->get('enableguestsearchvideos')) {
            $mainframe->enqueueMessage(JText::_('COM_COMMUNITY_RESTRICTED_ACCESS'), 'notice');
            return $this->blockUnregister();
        }

        $this->checkVideoAccess();
        $document = JFactory::getDocument();
        $viewType = $document->getType();
        $viewName = $jinput->get('view', $this->getName());
        $view = $this->getView($viewName, '', $viewType);

        echo $view->get(__FUNCTION__);
    }

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
                $this->setError(
                    JText::_('COM_COMMUNITY_INVALID_FILE_REQUEST') . ': ' . 'FFmpeg cannot process remote video.'
                );
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
                $this->setError(JText::_('COM_COMMUNITY_CURL_NOT_EXISTS'));
                return false;
            }

            $videoLib = new CVideoLibrary();
            $videoObj = $videoLib->getProvider($table->path);
            if ($videoObj == false) {
                $this->setError($videoLib->getError());
                return false;
            }
            try {
                $videoObj->isValid();
            } catch (Exception $e) {
                $this->setError($videoObj->getError());
                return false;
            }

            $remoteThumb = $videoObj->getThumbnail();
            $thumbData = CRemoteHelper::getContent($remoteThumb, true);

            if (empty($thumbData)) {
                $this->setError(JText::_('COM_COMMUNITY_INVALID_FILE_REQUEST') . ': ' . $remoteThumb);
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
                    $this->setError(JText::_('COM_COMMUNITY_INVALID_FILE_REQUEST') . ': ' . $thumbFileName);
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
                    $this->setError(JText::_('WARNFS_ERR02') . ': ' . $thumbFileName);
                    return false;
                }

                // Resize the thumbnails
                //CImageHelper::resizeProportional( $thumbPath , $thumbPath , $mime , CVideoLibrary::thumbSize('width') , CVideoLibrary::thumbSize('height') );

                list($width, $height) = explode('x', $config->get('videosThumbSize'));
                CImageHelper::resizeAspectRatio($thumbPath, $thumbPath, $width, $height);
            } else {
                $this->setError(JText::_('COM_COMMUNITY_PHOTOS_IMAGE_NOT_PROVIDED_ERROR'));
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

    /**
     * Delete video's wall
     *
     * @param    int $id The id of the video
     * @return    True on success
     * @since    1.2
     * */
    private function _deleteVideoWalls($id = 0)
    {


        if (!COwnerHelper::isRegisteredUser()) {
            return;
        }
        $video = CFactory::getModel('Videos');
        $video->deleteVideoWalls($id);
    }

    /**
     * Delete video's activity stream
     *
     * @params    int        $id        The video id
     * @return    True on success
     * @since    1.2
     *
     * */
    private function _deleteVideoActivities($id = 0)
    {
        if (!COwnerHelper::isRegisteredUser()) {
            return;
        }
        $video = CFactory::getModel('Videos');
        $video->deleteVideoActivities($id);
    }

    /**
     * Delete video's files and thumbnails
     *
     * @params    object    $video    The video object
     * @return    True on success
     * @since    1.2
     *
     * */
    private function _deleteVideoFiles($video)
    {
        // We passed in the video object because of
        // the table row of $video->id coud be deleted
        // thus, there's no way to retrive the thumbnail path
        // and also the flv file path
        if (!$video) {
            return;
        }
        if (!COwnerHelper::isRegisteredUser()) {
            return;
        }


        $storage = CStorage::getStorage($video->storage);

        if ($storage->exists($video->thumb)) {
            $storage->delete($video->thumb);
        }

        if ($storage->exists($video->path)) {
            $storage->delete($video->path);
        }
        /*
          jimport('joomla.filesystem.file');
          $files		= array();

          $thumb	= JPATH::clean(JPATH_ROOT .'/'. $video->thumb);
          if (JFile::exists( $thumb ))
          {
          $files[]= $thumb;
          }

          if ($video->type == 'file')
          {
          $flv	= JPATH::clean(JPATH_ROOT .'/'. $video->path);
          if (JFile::exists($flv))
          {
          $files[]= $flv;
          }
          }

          if (!empty($files))
          {
          return JFile::delete($files);
          }
         */

        return true;
    }

    /**
     * Delete featured videos
     *
     * @param    int $id The id of the video
     * @return    True on success
     * @since    1.2
     * */
    private function _deleteFeaturedVideos($id = 0)
    {
        if (!COwnerHelper::isRegisteredUser()) {
            return;
        }


        $featuredVideo = new CFeatured(FEATURED_VIDEOS);
        $featuredVideo->delete($id);

        return;
    }

    /**
     * Delete profile video
     *
     * @param    int $creator The id of the video creator
     * @return    True on success or unsuccess
     * */
    private function _deleteProfileVideo($creator, $deletedvideoid)
    {
        if (!COwnerHelper::isRegisteredUser()) {
            return;
        }

        // Only the video creator can use the video as his/her profile video
        $user = CFactory::getUser($creator);

        // Set params to default(0 for no profile video)
        $params = $user->getParams();

        $videoid = $params->get('profileVideo', 0);

        // Check if the current profile video id same with the deleted video id
        if ($videoid == $deletedvideoid) {
            $params->set('profileVideo', 0);
            $user->save('params');
        }

        return;
    }

    public function streamer()
    {
        $document = JFactory::getDocument();
        $document->setType('raw');
        $document->setMimeEncoding('video/x-flv');

        $mainframe = JFactory::getApplication();
        $jinput = $mainframe->input;

        $table = JTable::getInstance('Video', 'CTable');
        if (!$table->load($jinput->get('vid', null, 'NONE'))) {
            $this->setError($table->getError());
            return false;
        }

        $pos = $jinput->getInt('target', 0);

        $file = CString::str_ireplace('/', '\\', JPATH_ROOT . '/' . $table->path);
        if (!JFile::exists($file)) {
            return 'video file not found.';
        }

        $fileName = basename($file);
        $fileSize = filesize($file) - (($pos > 0) ? $pos + 1 : 0);

        $fh = fopen($file, 'rb') or die('cannot open file: ' . $file);
        $fileSize = filesize($file) - (($pos > 0) ? $pos + 1 : 0);
        fseek($fh, $pos);

        $binary_header = strtoupper(JFile::getExt($file)) . pack('C', 1) . pack('C', 1) . pack('N', 9) . pack('N', 9);

        $contentLength = ($pos > 0) ? $fileSize + 13 : $fileSize;

        /*
          session_cache_limiter('none');
          JResponse::clearHeaders(); /*
          JResponse::setHeader( 'Expires', 'Mon, 26 Jul 1997 05:00:00 GMT', true );
          JResponse::setHeader( 'Last-Modified', gmdate("D, d M Y H:i:s") . ' GMT', true );
          //JResponse::setHeader( 'Cache-Control', 'no-store, no-cache, must-revalidate, post-check=0, pre-check=0', true );
          //JResponse::setHeader( 'Pragma', 'no-cache', true );
          //JResponse::setHeader( 'Content-Disposition', 'attachment; filename="'.$fileName.'"', true);
          JResponse::setHeader( 'Content-Length', ($pos > 0) ? $fileSize + 13 : $fileSize, true );
          JResponse::setHeader( 'Content-Type', 'video/x-flv', true );
          JResponse::sendHeaders();
         */

        header('Content-Disposition: attachment; filename=' . $filename);
        header('Content-Length: ' . $contentLength);
        //header('Connection: close');
        header('Content-Type: video/x-flv; name=' . $filename);
        //header('Cache-Control: store, cache');
        //header('Pragma: cache');


        if ($pos > 0) {
            print $binary_header;
        }

        $limit_bw = true;
        $packet_size = 90 * 1024;
        $packet_interval = 0.3;

        while (!feof($fh)) {
            if (!$limit_bw) {
                print(fread($fh, filesize($file)));
            } else {
                $time_start = microtime(true);
                print(fread($fh, $packet_size));
                $time_stop = microtime(true);
                $time_difference = $time_stop - $time_start;
                if ($time_difference < $packet_interval) {
                    usleep($packet_interval * 1000000 - $time_difference * 1000000);
                }
            }
        }
    }

    public function ajaxSetVideoCategory($videoId, $catId)
    {
        $filter = JFilterInput::getInstance();
        $videoId = $filter->clean($videoId, 'int');
        $catId = $filter->clean($catId, 'int');

        $response = new JAXResponse();

        $my = CFactory::getUser();

        //CFactory::load( 'models' , 'videos' );
        $video = JTable::getInstance('Video', 'CTable');
        $video->load($videoId);

        $video->category_id = $catId;
        $video->store();

        return $response->sendResponse();
    }

    // check group id and return the creator type and it's upload number limit
    private function _manipulateParameter($groupid, $eventid, $config)
    {
        if($groupid){
            $allowManageVideos = CGroupHelper::allowManageVideo($groupid);
            CError::assert($allowManageVideos, '', '!empty', __FILE__, __LINE__);

            $creatorType = VIDEO_GROUP_TYPE;
            $videoLimit = $config->get('groupvideouploadlimit');
        }elseif($eventid){
            $creatorType = VIDEO_EVENT_TYPE;
            $videoLimit = $config->get('eventvideouploadlimit');
        }else{
            $creatorType = VIDEO_USER_TYPE;
            $videoLimit = $config->get('videouploadlimit');
        }

        return array($creatorType, $videoLimit);
    }

    // check creator type and return total uploaded number and limit per user
    private function _getParameter($creatorType, $config)
    {
        $model = CFactory::getModel('videos');
        $my = CFactory::getUser();
        if ($creatorType == VIDEO_GROUP_TYPE) {
            $totalVideos = $model->getVideosCount($my->id, VIDEO_GROUP_TYPE);
            $videoUploadLimit = $config->get('groupvideouploadlimit');
        } elseif($creatorType == VIDEO_EVENT_TYPE){
            $totalVideos = $model->getVideosCount($my->id, VIDEO_GROUP_TYPE);
            $videoUploadLimit = $config->get('eventvideouploadlimit');
        }else {
            $totalVideos = $model->getVideosCount($my->id, VIDEO_USER_TYPE);
            $videoUploadLimit = $config->get('videouploadlimit');
        }

        return array($totalVideos, $videoUploadLimit);
    }

    private function _checkUploadLimit()
    {
        if ( CLimitsLibrary::exceedDaily('videos') ) {
            $json = array(
                'title' => JText::_('COM_COMMUNITY_VIDEOS_ADD'),
                'error' => JText::_('COM_COMMUNITY_VIDEOS_LIMIT_REACHED')
            );

            die( json_encode( $json ) );
        }
    }

    public function ajaxShowStreamVideoWindow($activity_id)
    {
        $objResponse = new JAXResponse();
        $my = CFactory::getUser();

        $allowToView = true;
        if ($activity_id == '') {
            $allowToView = false;
        }

        $activityTable = JTable::getInstance('Activity', 'CTable');

        if (!$activityTable->load($activity_id)) {
            $allowToView = false;
        }

        /* === Start Premission Checking === */
        $user = CFactory::getUser($activityTable->actor);
        $blocked = $user->isBlocked();

        if ($blocked && !COwnerHelper::isCommunityAdmin()) {
            $allowToView = false;
        }

        $activity = new CActivity($activityTable);

        $headMetas = $activity->getParams('headMetas');

        $headMetaParams = '';
        /* We do convert into JRegistry to make it easier to use */
        if ($headMetas) {
            $headMetaParams = new JRegistry($headMetas);
        }

        $providerName = JString::strtolower($headMetaParams->get('video_provider'));

        if ($headMetaParams->get('type') == 'video' && $providerName != '') {
            $libraryPath = COMMUNITY_COM_PATH . '/libraries/videos' . '/' . $providerName . '.php';
            require_once($libraryPath);
            $className = 'CTableVideo' . JString::ucfirst($providerName);
            $provider = new $className();
        }

        if (!CPrivacy::isAccessAllowed($my->id, $activityTable->actor, 'custom', $activityTable->access)) {
            switch ($activityTable->access) {
                case '40':
                    $allowToView = false;
                    break;
                case '30':
                    $allowToView = false;
                    break;
                default:
                    $allowToView = false;
                    break;
            }
        }

        $objResponse->addScriptCall('cWindowShow', '', '', 640, 360);

        if ($allowToView) {
            $objResponse->addScriptCall(
                'cWindowAddContent',
                $provider->getViewHTML($headMetaParams->get('video_id'), 640, 360)
            );
        } else {
            $objResponse->addScriptCall('cWindowAddContent', JText::_('COM_COMMUNITY_VIDEO_UNABLE_VIEW'));
        }


        $objResponse->sendResponse();

    }

    public function ajaxShowVideoWindow($video_id)
    {
        $allowToView = true; //determine the view premission

        $my = CFactory::getUser();
        $config = CFactory::getConfig();

        $video = JTable::getInstance('Video', 'CTable');

        if (!$video->load($video_id)) {
            $allowToView = false;
        }

        /* === Start Premission Checking === */
        $user = CFactory::getUser($video->creator);
        $blocked = $user->isBlocked();

        if ($blocked && !COwnerHelper::isCommunityAdmin()) {
            $allowToView = false;
        }

        if ($video->creator_type == VIDEO_GROUP_TYPE) {
            //CFactory::load( 'helpers' , 'group' );

            if (!CGroupHelper::allowViewMedia($video->groupid)) {
                $allowToView = false;
            }
        } else {
            if (!CPrivacy::isAccessAllowed($my->id, $video->creator, 'custom', $video->permissions)) {
                switch ($video->permissions) {
                    case '40':
                        $allowToView = false;
                        break;
                    case '30':
                        $allowToView = false;
                        $this->noAccess(
                            JText::sprintf('COM_COMMUNITY_VIDEOS_FRIEND_PERMISSION_MESSAGE', $owner->getDisplayName())
                        );
                        break;
                    default:
                        $allowToView = false;
                        break;
                }
            }
        }

        if ( $video->published == 0 )
            $allowToView = false;
        /* === End Permission Checking === */

        if (!$allowToView) {
            $json = array( 'error' => JText::_('COM_COMMUNITY_VIDEO_UNABLE_VIEW') );
            die( json_encode($json) );
        }

        $video->hit();

        // JSON.

        $json = array();

        $json['playerHtml'] = $video->getPlayerHTML();
        $json['can_edit'] = $video->canEdit();
        $json['can_delete'] = (int) $my->authorise('community.delete', 'videos', $video);
        $json['video_url'] = $video->getPermalink();

        $json['hits'] = $video->getHits();
        $json['hits'] = JText::sprintf( CStringHelper::isPlural($json['hits']) ? 'COM_COMMUNITY_VIDEOS_HITS_COUNT_MANY' : 'COM_COMMUNITY_VIDEOS_HITS_COUNT', $json['hits'] );

        $json['my_id'] = (int) $my->id;
        $json['owner_id'] = (int) $video->creator;
        $json['is_admin'] = (int) COwnerHelper::isCommunityAdmin();
        $json['enableprofilevideo'] = $config->get('enableprofilevideo');
        $json['enablereporting'] = $config->get('enablereporting');
        $json['enablesharing'] = $config->get('enablesharethis') == 1;
        $json['groupid'] = $video->groupid > 0 ? $video->groupid : 0;
        $json['eventid'] = $video->eventid > 0 ? $video->eventid : 0;

        // Get photo like info.
        $like = new CLike();
        $json['like'] = array(
            'lang' => JText::_('COM_COMMUNITY_LIKE'),
            'lang_like' => JText::_('COM_COMMUNITY_LIKE'),
            'lang_liked' => JText::_('COM_COMMUNITY_LIKED'),
            'count' => $like->getLikeCount('videos', $video->id),
            'is_liked' => $like->userLiked('videos', $video->id, $my->id) === COMMUNITY_LIKE
        );

        // Languages.
        $json['lang'] = array(
            'comments'             => JText::_('COM_COMMUNITY_COMMENTS'),
            'tag_video'            => JText::_('COM_COMMUNITY_TAG_THIS_VIDEO'),
            'done_tagging'         => JText::_('COM_COMMUNITY_PHOTO_DONE_TAGGING'),
            'options'              => JText::_('COM_COMMUNITY_OPTIONS'),
            'fetch'                => JText::_('COM_COMMUNITY_VIDEOS_FETCH_THUMBNAIL'),
            'set_as_profile_video' => JText::_('COM_COMMUNITY_VIDEOS_SET_AS_PROFILE'),
            'edit_video'           => JText::_('COM_COMMUNITY_VIDEOS_EDIT_VIDEO'),
            'delete_video'         => JText::_('COM_COMMUNITY_VIDEOS_DELETE_VIDEO'),
            'share'                => JText::_('COM_COMMUNITY_SHARE'),
            'report'               => JText::_('COM_COMMUNITY_REPORT')
        );

        die(json_encode($json));
    }

    private function getVideoTags($video)
    {
        $tagging = new CVideoTagging();
        $taggedList = $tagging->getTaggedList($video->id);
        $tags = array();

        $my = CFactory::getUser();

        for ($i = 0, $count = count($taggedList); $i < $count; $i++) {
            $tagItem = $taggedList[$i];
            $tagUser = CFactory::getUser($tagItem->userid);

            // Check if user can remove tag.
            // 1st we check the tagged user is the photo owner.
            //   If yes, canRemoveTag == true.
            //   If no, then check on user is the tag creator or not.
            //     If yes, canRemoveTag == true
            //     If no, then check on user whether user is being tagged
            $canRemoveTag = 0;
            if (COwnerHelper::isMine($my->id, $video->creator) ||
                COwnerHelper::isMine($my->id, $tagItem->created_by) ||
                COwnerHelper::isMine($my->id, $tagItem->userid)
            ) {
                $canRemoveTag = 1;
            }

            $tagItem->user = $tagUser;
            $tagItem->canRemoveTag = $canRemoveTag;

            $tags[] = array(
                'id' => $tagItem->id,
                'userId' => $tagItem->userid,
                'displayName' => $tagItem->user->getDisplayName(),
                'profileUrl' => CRoute::_('index.php?option=com_community&view=profile&userid=' . $tagItem->userid, false),
                'canRemove' => $tagItem->canRemoveTag
            );
        }

        return $tags;
    }

    private function getVideoInfoHeader($video)
    {
        $date = CTimeHelper::getDate($video->created);
        $config = CFactory::getConfig();
        $creator = CFactory::getUser($video->creator);

        if ($config->get('activitydateformat') == 'lapse') {
            $created = CTimeHelper::timeLapse($date);
        } else {
            $created = $date->Format(JText::_('DATE_FORMAT_LC2'));
        }

        $userThumb = CUserHelper::getThumb($creator->id, 'avatar');

        $template = new CTemplate();
        return $template->set('creator', $creator)
            ->set('permission', $video->permissions)
            ->set('created', $created)
            ->set('userThumb', $userThumb)
            ->fetch('wall/info');
    }

    public function ajaxGetInfo($videoId, $showAllComments = false)
    {
        $my = CFactory::getUser();
        $config = CFactory::getConfig();
        $json = array();

        $video = JTable::getInstance('Video', 'CTable');
        $video->load( $videoId );

        // Get the header info.
        $header = $this->getVideoInfoHeader($video);

        // Get the wall form.
        $wallInput = '';
        if (CPrivacy::isAccessAllowed($my->id, $video->creator, 'custom', PRIVACY_FRIENDS) || !$config->get('lockvideoswalls')) {
            $wallInput = CWallLibrary::getWallInputForm(
                $video->id,
                'videos,ajaxSaveWall',
                'videos,ajaxRemoveWall'
            );
        }

        // Get the wall contents.
        $wallContents = CWallLibrary::getWallContents(
            'videos',
            $video->id,
            (COwnerHelper::isCommunityAdmin() || ($my->id == $video->creator && ($my->id != 0))),
            $showAllComments ? false : $config->get('stream_default_comments'),
            0,
            'wall/content',
            'videos,video'
        );

        // JSON.
        $json['head'] = $header;
        $json['comments'] = $wallContents;
        $json['form'] = $wallInput;

        $json['description'] = array(
            'content'          => $video->description,
            'excerpt'          => JHTML::_('string.truncate', $video->description, $config->getInt('streamcontentlength')),
            'lang_cancel'      => JText::_('COM_COMMUNITY_CANCEL'),
            'lang_save'        => JText::_('COM_COMMUNITY_SAVE'),
            'lang_add'         => JText::_('COM_COMMUNITY_ADD_DESCRIPTION'),
            'lang_edit'        => JText::_('COM_COMMUNITY_EDIT_DESCRIPTION'),
            'lang_placeholder' => JText::_('COM_COMMUNITY_EDIT_DESCRIPTION_PLACEHOLDER')
        );

        // Get the wall comments count.
        $totalComments = CWallLibrary::getWallCount('videos', $video->id);

        // Only show if we are not showing all comments, and total comments is not 0.
        if ( !$showAllComments ) {
            $commentDiff = $totalComments - $config->get('stream_default_comments');
            if ($commentDiff > 0) {
                $json['showall'] = JText::_('COM_COMMUNITY_SHOW_PREVIOUS_COMMENTS') . ' (' . $commentDiff . ')';
            }
        }

        // Tag info.
        $json['tagged'] = $this->getVideoTags($video);
        $json['tagLabel'] = JText::_('COM_COMMUNITY_VIDEOS_IN_THIS_VIDEO');
        $json['tagRemoveLabel'] = JText::_('COM_COMMUNITY_REMOVE');

        die( json_encode($json) );
    }

    public function ajaxShowVideoFeatured($video_id)
    {
        $objResponse = new JAXResponse();

        $allowToView = true; //determine the view premission
        $my = CFactory::getUser();
        $video = JTable::getInstance('Video', 'CTable');

        if (!$video->load($video_id)) {
            $allowToView = false;
        }

        /* === Start Premission Checking === */
        $user = CFactory::getUser($video->creator);
        $blocked = $user->isBlocked();

        if ($blocked && !COwnerHelper::isCommunityAdmin()) {
            $allowToView = false;
        }

        if ($video->creator_type == VIDEO_GROUP_TYPE) {
            //CFactory::load( 'helpers' , 'group' );

            if (!CGroupHelper::allowViewMedia($video->groupid)) {
                $allowToView = false;
            }
        } else {

            if (!CPrivacy::isAccessAllowed($my->id, $video->creator, 'custom', $video->permissions)) {
                switch ($video->permissions) {
                    case '40':
                        $allowToView = false;
                        break;
                    case '30':
                        $allowToView = false;
                        $this->noAccess(
                            JText::sprintf('COM_COMMUNITY_VIDEOS_FRIEND_PERMISSION_MESSAGE', $owner->getDisplayName())
                        );
                        break;
                    default:
                        $allowToView = false;
                        break;
                }
            }
        }

        /* === End Permission Checking === */

        if ($allowToView) {
            // Hit counter + 1
            $video->hit();
            $notiHtml = '<div class="cVideo-Player video-player">
							' . $video->getPlayerHTML() . '
						</div>';
        } else {
            $notiHtml = JText::_('COM_COMMUNITY_VIDEO_UNABLE_VIEW');
        }

        // Get like
        //CFactory::load( 'libraries' , 'like' );
        $likes = new CLike();
        $likesHTML = $likes->getHTML('videos', $video->id, $my->id);
        // Get wall count
        //CFactory::load( 'libraries' , 'wall' );
        $wallCount = CWallLibrary::getWallCount('videos', $video->id);
        // Get video link
        $videoCommentLink = CRoute::_(
            'index.php?option=com_community&view=videos&task=video&videoid=' . $video->id . '&groupid=' . $video->groupid . '&userid=' . $video->creator . '#comments'
        );
        $videoLink = CRoute::_(
            'index.php?option=com_community&view=videos&task=video&videoid=' . $video->id . '&groupid=' . $video->groupid . '&userid=' . $video->creator
        );
        $creatorName = $video->getCreatorName();
        $creatorLink = CRoute::_('index.php?option=com_community&view=profile&userid=' . $video->creator);

        $objResponse->addScriptCall(
            'updatePlayer',
            $notiHtml,
            $video->title,
            $likesHTML,
            $video->getHits(),
            $wallCount,
            $videoLink,
            $videoCommentLink,
            $creatorName,
            $creatorLink
        );
        $objResponse->sendResponse();
    }

    public function ajaxAddVideoTag($videoId, $userId)
    {
        $filter = JFilterInput::getInstance();
        $videoId = $filter->clean($videoId, 'int');
        $userId = $filter->clean($userId, 'int');

        if (!COwnerHelper::isRegisteredUser()) {
            return $this->ajaxBlockUnregister();
        }

        $response = new JAXResponse();
        $json = array();

        $my = CFactory::getUser();
        $videoModel = CFactory::getModel('videos');
        $tagging = new CVideoTagging();

        $video = JTable::getInstance('Video', 'CTable');
        $video->load($videoId);

        $user = CFactory::getUser($userId);
        if (empty($video->id) || !$video->tagAllow($my->id)) {
            $json['error'] = JText::_('COM_COMMUNITY_VIDEO_TAGGING_INVALID_VIDEO');
        } else {
            if (empty($userId) || empty($user->id)) {
                $json['error'] = JText::_('COM_COMMUNITY_VIDEO_TAGGING_INVALID_USER');
            } else {
                $tag = new stdClass();
                $tag->videoId = $videoId;
                $tag->userId = $userId;

                $tagId = $tagging->addTag($tag);

                $jsonString = '{}';
                if ($tagId > 0) {
                    $json['success'] = true;
                    $json['data'] = array(
                        'id' => $tagId,
                        'userId' => $userId,
                        'displayName' => $user->getDisplayName(),
                        'profileUrl' => CRoute::_('index.php?option=com_community&view=profile&userid=' . $userId, false),
                        'videoId' => $videoId,
                        'canRemove' => true
                    );

                    //send notification emails
                    if ($video->groupid) {
                        $url = 'index.php?option=com_community&view=videos&task=video&groupid=' . $video->groupid . '&videoid=' . $video->id;
                    } else {
                        $url = 'index.php?option=com_community&view=videos&task=video&videoid=' . $video->id;
                    }

                    if ($my->id != $userId) {
                        // Add notification
                        $params = new CParameter('');
                        $params->set('url', $url);
                        $params->set('video', $video->title);
                        $params->set('video_url', $url);

                        CNotificationLibrary::add(
                            'videos_tagging',
                            $my->id,
                            $userId,
                            JText::sprintf('COM_COMMUNITY_VIDEO_TAG_YOU'),
                            '',
                            'videos.tagging',
                            $params
                        );
                    }
                } else {
                    $json['error'] = $tagging->getError();
                }
            }
        }

        die( json_encode($json) );
    }

    public function ajaxRemoveVideoTag($videoId, $userId)
    {
        $filter = JFilterInput::getInstance();
        $videoId = $filter->clean($videoId, 'int');
        $userId = $filter->clean($userId, 'int');

        if (!COwnerHelper::isRegisteredUser()) {
            return $this->ajaxBlockUnregister();
        }

        $my = CFactory::getUser();
        $json = array();

        if (!$my->authorise('community.remove', 'videos.tag.' . $videoId)) {
            $json['error'] = JText::_('ACCESS FORBIDDEN');
            die(json_encode($json));
        }

        $tagging = new CVideoTagging();
        if (!$tagging->removeTag($videoId, $userId)) {
            $json['error'] = $tagging->getError();
            die(json_encode($json));
        }

        $json['success'] = true;
        die(json_encode($json));
    }

    /**
     * [ajaxCheckFileSize description]
     * @param  [iint] $fileSize [uploaded file size]
     * @return [type]           [description]
     */
    public function ajaxCheckFileSize($fileSize)
    {
        $filter = JFilterInput::getInstance();
        $fileSize = $filter->clean($fileSize, 'int');
        $maxPost = (int)(ini_get('post_max_size'));
        $response = new JAXResponse();

        // Convert to Mb
        $fileSize = (int)(round($fileSize / 1048576));

        if ($fileSize > $maxPost) {
            $response->addScriptCall('alert', 'File size exceeded post_max_size');
        } else {
            $response->addScriptCall('joms.jQuery(\'form#uploadVideo button\').prop("disabled", false);');
        }

        return $response->sendResponse();
    }

    /**
     * Edit video comments
     * @param int $wallId Wall id
     * @return bool
     */
    public function editVideoWall($wallId)
    {
        $my = CFactory::getUser();
        $wall = JTable::getInstance('Wall', 'CTable');
        $wall->load($wallId);
        return $my->authorise('community.edit', 'photos.wall.' . $wallId, $wall);
    }

    /**
     * Ajax method to save video description
     *
     * @param    int $videoId The video id
     * @param    string $description The description of the video
     * */
    public function ajaxSaveDescription($videoId, $description)
    {
        $filter = JFilterInput::getInstance();
        $videoId = $filter->clean($videoId, 'int');
        $description = $filter->clean($description, 'string');

        if ( !COwnerHelper::isRegisteredUser() ) {
            return $this->ajaxBlockUnregister();
        }

        $json = array();

        $my = CFactory::getUser();
        $config = CFactory::getConfig();

        $video = JTable::getInstance('Video', 'CTable');
        $video->load($videoId);

        $group = JTable::getInstance('Group', 'CTable');
        $group->load($video->groupid);

        if ( COwnerHelper::isCommunityAdmin() || $video->creator == $my->id || $group->isAdmin($my->id) ) {
            $video->description = $description;
            $video->store();
            $json['success'] = true;
            $json['caption'] = $video->description;
            $json['excerpt'] = JHTML::_('string.truncate', $video->description, $config->getInt('streamcontentlength'));
        } else {
            $json['error'] = JText::_('COM_COMMUNITY_NOT_ALLOWED_TO_ACCESS_SECTION');
        }

        die( json_encode( $json ) );
    }

}
