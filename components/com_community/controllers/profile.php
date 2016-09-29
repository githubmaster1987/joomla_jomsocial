<?php
/**
 * @copyright (C) 2013 iJoomla, Inc. - All rights reserved.
 * @license GNU General Public License, version 2 (http://www.gnu.org/licenses/gpl-2.0.html)
 * @author iJoomla.com <webmaster@ijoomla.com>
 * @url https://www.jomsocial.com/license-agreement
 * The PHP code portions are distributed under the GPL license. If not otherwise stated, all images, manuals, cascading style sheets, and included JavaScript *are NOT GPL, and are released under the IJOOMLA Proprietary Use License v1.0
 * More info at https://www.jomsocial.com/license-agreement
 */
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

class CommunityProfileController extends CommunityBaseController {

    /**
     * Edit a user's profile
     *
     * @access	public
     * @param	none
     */
    private $_icon = '';

    public function editProfileWall($wallId) {
        $my = CFactory::getUser();
        $wall = JTable::getInstance('Wall', 'CTable');
        $wall->load($wallId);

        if (COwnerHelper::isCommunityAdmin() || $my->id == $wall->post_by) {
            return true;
        }

        return false;
    }

    public function ajaxConfirmRemoveAvatar() {
        $response = new JAXResponse();
        $my = CFactory::getUser();

        $tmpl = new CTemplate();
        $content = JText::_('COM_COMMUNITY_CONFIRM_REMOVE_PROFILE_PICTURE');

        $formAction = CRoute::_('index.php?option=com_community&view=profile&task=removeAvatar');
        $actions = '<form action="' . $formAction . '" method="POST" class="reset-gap">';
        $actions .= '<button class="btn" onclick="cWindowHide();return false;">' . JText::_('COM_COMMUNITY_NO_BUTTON') . '</button>';
        $actions .= '<input class="btn btn-primary pull-right" type="submit" value="' . JText::_('COM_COMMUNITY_YES_BUTTON') . '" />';
        $actions .= '</form>';

        $response->addAssign('cwin_logo', 'innerHTML', JText::_('COM_COMMUNITY_REMOVE_PROFILE_PICTURE'));
        $response->addScriptCall('cWindowAddContent', $content, $actions);

        return $response->sendResponse();
    }

    public function removeAvatar() {
        $my = CFactory::getUser();
        $mainframe = JFactory::getApplication();

        if ($my->id == 0) {
            echo JText::_('COM_COMMUNITY_ACCESS_FORBIDDEN');
            return;
        }

        $model = CFactory::getModel('user');
        $model->removeProfilePicture($my->id, 'avatar');
        $model->removeProfilePicture($my->id, 'thumb');

        $mainframe->redirect(CRoute::_('index.php?option=com_community&view=profile', false), JText::_('COM_COMMUNITY_PROFILE_PICTURE_REMOVED'));
    }

    public function ajaxPlayProfileVideo($videoid = null, $userid = 0) {
        $filter = JFilterInput::getInstance();
        $videoid = $filter->clean($videoid, 'int');
        $userid = $filter->clean($userid, 'int');

        $objResponse = new JAXResponse();

        // Get necessary properties and load the libraries
        $my = CFactory::getUser();

        $video = JTable::getInstance('Video', 'CTable');
        $video->load($videoid);

        if (!empty($video->id)) {
            // Check video permission
            if (!$this->isPermitted($my->id, $video->creator, $video->permissions)) {
                switch ($video->permissions) {
                    case PRIVACY_PRIVATE :
                        $content = JText::_('COM_COMMUNITY_VIDEOS_OWNER_ONLY');
                        break;
                    case PRIVACY_FRIENDS :
                        $owner = CFactory::getUser($video->creator);
                        $content = JText::sprintf('COM_COMMUNITY_VIDEOS_FRIEND_PERMISSION_MESSAGE', $owner->getDisplayName());
                        break;
                    default:
                        $content = JText::_('COM_COMMUNITY_VIDEOS_LOGIN_MESSAGE');
                        break;
                }

                $objResponse->addScriptCall('cWindowShow', '', $title, 430, 80);
            } else {
                $title = $video->getTitle();
                $content = $notiHtml = '<div class="cVideo-Player video-player">
						' . $video->getPlayerHTML() . '
						</div>';

                //to get the width and height of the iframe
                preg_match('/< *[^>]*width *= *["\']?([^"\']*)/i', $notiHtml, $width);
                preg_match('/< *[^>]*height *= *["\']?([^"\']*)/i', $notiHtml, $height);

                //to match the window dimension with the iframe
                if ((isset($height[1]) && $height[1]) > 0 && (isset($width[1]) && $width[1] > 0)) {
                    $objResponse->addScriptCall('cWindowShow', '', '', $width[1] + 60, $height[1]);
                }

                $objResponse->addAssign('cwin_logo', 'innerHTML', JText::_('COM_COMMUNITY_VIDEOS_PROFILE_VIDEO'));
                $objResponse->addScriptCall('cWindowAddContent', $notiHtml);

                $objResponse->sendResponse();
            }
        } else {
            $content = JText::_('COM_COMMUNITY_VIDEOS_PROFILE_VIDEO_NOT_EXIST');

            if (COwnerHelper::isMine($my->id, $userid)) {
                $redirectURL = CRoute::_('index.php?option=com_community&view=profile&task=linkVideo', false);
                $action = '<input type="button" class="btn" onclick="cWindowHide(); window.location=\'' . $redirectURL . '\';" value="' . JText::_('COM_COMMUNITY_VIDEOS_ADD_PROFILE_VIDEO') . '"/>';

                $objResponse->addScriptCall('cWindowActions', $action);
            }

            $objResponse->addScriptCall('cWindowShow', '', $title, 430, 80);
        }

        $action = '<button  class="btn" onclick="javascript:cWindowHide();">' . JText::_('COM_COMMUNITY_BUTTON_CLOSE_BUTTON') . '</button>';
        $objResponse->addAssign('cwin_logo', 'innerHTML', JText::_('COM_COMMUNITY_VIDEOS_PROFILE_VIDEO'));
        $objResponse->addScriptCall('cWindowAddContent', $content, $action);

        return $objResponse->sendResponse();
    }

    // Confirm before change video
    public function ajaxConfirmLinkProfileVideo($id) {
        $filter = JFilterInput::getInstance();
        $id = $filter->clean($id, 'int');

        // $objResponse = new JAXResponse();
        $json = array(
            'title'  => JText::_('COM_COMMUNITY_VIDEOS_EDIT_PROFILE_VIDEO'),
            'html'   => JText::_('COM_COMMUNITY_VIDEOS_PROFILE_VIDEO_CONFIRM_LINK'),
            'btnYes' => JText::_('COM_COMMUNITY_YES_BUTTON'),
            'btnNo'  => JText::_('COM_COMMUNITY_NO_BUTTON')
        );

        die( json_encode($json) );
    }

    // Store to database and reload page
    public function ajaxLinkProfileVideo($videoid) {
        $filter = JFilterInput::getInstance();
        $videoid = $filter->clean($videoid, 'int');

        $my = CFactory::getUser();

        if ($my->id == 0) {
            return $this->ajaxBlockUnregister();
        }

        $params = $my->getParams();
        $params->set('profileVideo', $videoid);
        $my->save('params');

        $json = array(
            'success' => true,
            'message' => JText::_('COM_COMMUNITY_VIDEOS_PROFILE_VIDEO_LINKED')
        );

        die( json_encode($json) );
    }

    // Need confirmation before remove link
    public function ajaxRemoveConfirmLinkProfileVideo($userid, $videoid) {
        $filter = JFilterInput::getInstance();
        $videoid = $filter->clean($videoid, 'int');
        $userid = $filter->clean($userid, 'int');

        $my = CFactory::getUser();

        if ($my->id == 0) {
            return $this->ajaxBlockUnregister();
        }

        $json = array(
            'title'     => JText::_('COM_COMMUNITY_VIDEOS_REMOVE_PROFILE_VIDEO'),
            'html'      => JText::_('COM_COMMUNITY_VIDEOS_REMOVE_PROFILE_VIDEO_CONFIRM_LINK'),
            'btnYes'    => JText::_('COM_COMMUNITY_YES_BUTTON'),
            'btnNo'     => JText::_('COM_COMMUNITY_NO_BUTTON'),
            'btnDelete' => JText::_('COM_COMMUNITY_VIDEOS_DELETE_VIDEO')
        );

        die( json_encode($json) );
    }

    // Remove link
    public function ajaxRemoveLinkProfileVideo($userid, $videoid) {
        $filter = JFilterInput::getInstance();
        $videoid = $filter->clean($videoid, 'int');
        $userid = $filter->clean($userid, 'int');

        $objResponse = new JAXResponse();

        $my = CFactory::getUser();

        if ($my->id == 0) {
            return $this->ajaxBlockUnregister();
        }

        $user = CFactory::getUser($userid);

        // Set params to default(0 for no profile video)
        $params = $user->getParams();
        $params->set('profileVideo', 0);
        $user->save('params');

        $json = array(
            'success' => true,
            'message' => JText::_('COM_COMMUNITY_VIDEOS_PROFILE_VIDEO_REMOVED')
        );

        die( json_encode($json) );
    }

    public function ajaxIphoneProfile() {
        $document = JFactory::getDocument();

        $viewType = $document->getType();
        $view = $this->getView('profile', '', $viewType);


        $html = '';

        ob_start();
        $this->profile();
        $content = ob_get_contents();
        ob_end_clean();

        $tmpl = new CTemplate();
        $tmpl->set('toolbar_active', 'profile');
        $simpleToolbar = $tmpl->fetch('toolbar.simple');

        $objResponse->addAssign('social-content', 'innerHTML', $simpleToolbar . $content);
        return $objResponse->sendResponse();
    }

    /**
     * 	Ajax method to block user from the site. This method is only used by site administrators
     *
     * 	@params	$userId	int	The user id that needs to be blocked
     * 	@params	$isBlocked	boolean	Whether the user is already blocked or not. If it is blocked, system should unblock it.
     * */
    public function ajaxBanUser($userId, $isBlocked) {
        $filter = JFilterInput::getInstance();
        $userId = $filter->clean($userId, 'int');
        $isBlocked = $filter->clean($isBlocked, 'bool');

        $user = CFactory::getUser($userId);

        $objResponse = new JAXResponse();
        $title = '';
        $my = CFactory::getUser();

        //CFactory::load( 'helpers', 'owner' );

        if ($my->id == 0) {
            return $this->ajaxBlockUnregister();
        }

        // @rule: Only site admin can access this function.
        if ($my->authorise('community.ban', 'profile.' . $userId, $user)) {
            $isSuperAdmin = COwnerHelper::isCommunityAdmin($user->id);

            // @rule: Do not allow to block super administrators.
            if ($isSuperAdmin) {

                $json = array(
                    'title' => JText::_('COM_COMMUNITY_BAN_USER'),
                    'error' => JText::_('COM_COMMUNITY_NOT_ALLOWED_TO_BAN_SUPER_ADMIN')
                );

            } else {

                $content  = JText::sprintf($isBlocked ? 'COM_COMMUNITY_UNBAN_USER_CONFIRMATION' : 'COM_COMMUNITY_BAN_USER_CONFIRMATION', $user->getDisplayName());
                $content .= '<form method="POST" action="' . CRoute::_('index.php?option=com_community&view=profile&task=banuser', false) . '">';
                $content .= '<input type="hidden" name="userid" value="' . $userId . '">';
                $content .= ( $isBlocked ) ? '<input type="hidden" name="blocked" value="1">' : '';
                $content .= '</form>';

                $json = array(
                    'title'  => JText::_($isBlocked ? 'COM_COMMUNITY_UNBAN_USER' : 'COM_COMMUNITY_BAN_USER'),
                    'html'   => $content,
                    'btnYes' => JText::_('COM_COMMUNITY_YES_BUTTON'),
                    'btnNo'  => JText::_('COM_COMMUNITY_NO_BUTTON')
                );

            }
        }

        die( json_encode($json) );
    }

    /**
     * 	Ajax method to remove user's picture from the site. This method is only used by site administrators
     *
     * 	@params	$userId	int	The user id that needs to have their picture removed.
     * */
    public function ajaxRemovePicture($userId) {
        $filter = JFilterInput::getInstance();
        $userId = $filter->clean($userId, 'int');

        $json = array();

        $my = CFactory::getUser();
        //CFactory::load( 'helpers', 'owner' );

        if ($my->id == 0) {
            return $this->ajaxBlockUnregister();
        }

        // @rule: Site admin & owner can access this function.
        if (COwnerHelper::isCommunityAdmin($my->id) || $userId == $my->id) {
            $formAction = CRoute::_('index.php?option=com_community&view=profile&task=removepicture', false);
            $actions = '<form name="cancelRequest" action="' . $formAction . '" method="POST" class="reset-gap">';
            $actions .= '<input type="hidden" name="userid" value="' . $userId . '" />';
            $actions .= '<input type="button" class="btn" onclick="cWindowHide();return false;" value="' . JText::_('COM_COMMUNITY_NO_BUTTON') . '" />';
            $actions .= '<input type="submit" value="' . JText::_('COM_COMMUNITY_YES_BUTTON') . '" class="btn btn-primary pull-right" />&nbsp;';
            $actions .= '</form>';

            $json = array(
                'title'    => JText::_('COM_COMMUNITY_REMOVE_PROFILE_PICTURE'),
                'html'     => JText::_('COM_COMMUNITY_REMOVE_AVATAR_CONFIRMATION'),
                'btnYes'   => JText::_('COM_COMMUNITY_YES_BUTTON'),
                'btnNo'    => JText::_('COM_COMMUNITY_NO_BUTTON'),
                'redirUrl' => $formAction
            );
        }

        die( json_encode($json) );
    }

    public function ajaxUploadNewPicture($userId) {
        $filter = JFilterInput::getInstance();
        $userId = $filter->clean($userId, 'int');

        $objResponse = new JAXResponse();

        $my = CFactory::getUser();
        //CFactory::load( 'helpers', 'owner' );
        $this->cacheClean(array(COMMUNITY_CACHE_TAG_ACTIVITIES, COMMUNITY_CACHE_TAG_FRONTPAGE));
        if (!isCommunityAdmin()) {
            return $this->ajaxBlockUnregister();
        }

        $title = JText::_('COM_COMMUNITY_CHANGE_AVATAR');

        $formAction = CRoute::_('index.php?option=com_community&view=profile&task=uploadAvatar', false);

        $config = CFactory::getConfig();
        $uploadLimit = (double) $config->get('maxuploadsize');
        $uploadLimit .= 'MB';

        $content = '<form name="jsform-profile-ajaxuploadnewpicture" action="' . $formAction . '" id="uploadForm" method="post" enctype="multipart/form-data" class="reset-gap">';
        $content .= '<input class="btn" type="file" id="file-upload" name="Filedata" />';
        $content .= '<input type="hidden" name="action" value="doUpload" />';
        $content .= '<input type="hidden" name="userid" value="' . $userId . '" />';
        $content .= '</form>';

        $actions = '<input type="button" class="btn" onclick="cWindowHide();return false;" value="' . JText::_('COM_COMMUNITY_CANCEL_BUTTON') . '" />';
        $actions .= '<input type="button" value="' . JText::_('COM_COMMUNITY_BUTTON_UPLOAD_PICTURE') . '" class="btn btn-primary pull-right" onclick="joms.jQuery(\'#uploadForm\').submit();" />';


        if ($uploadLimit != 0) {
            $content .= '<p class="info">' . JText::sprintf('COM_COMMUNITY_MAX_FILE_SIZE_FOR_UPLOAD', $uploadLimit) . '</p>';
        }

        $objResponse->addAssign('cwin_logo', 'innerHTML', $title);
        $objResponse->addScriptCall('cWindowAddContent', $content, $actions);

        return $objResponse->sendResponse();
    }

    public function ajaxUpdateURL($userId) {
        $filter = JFilterInput::getInstance();
        $userId = $filter->clean($userId, 'int');
        $my = CFactory::getUser();
        $objResponse = new JAXResponse();
        $json = array();

        if (!COwnerHelper::isCommunityAdmin() && $userId != $my->id) {
            $json['title'] = JText::_('COM_COMMUNITY_PROFILE_CHANGE_ALIAS');
            $json['message'] = JText::_('COM_COMMUNITY_RESTRICTED_ACCESS');
            die( json_encode($json) );
        }

        $tmpl = new CTemplate();
        $user = CFactory::getUser($userId);

        $juriRoot = JURI::root(false);
        $juriPathOnly = JURI::root(true);
        $juriPathOnly = rtrim($juriPathOnly, '/');
        $profileURL = rtrim(str_replace($juriPathOnly, '', $juriRoot), '/');

        $profileURL .= CRoute::_('index.php?option=com_community&view=profile&userid=' . $user->id, false);
        $alias = $user->getAlias();

        $inputHTML = '<input name="alias" type="text" value="' . $alias . '" class="input-small">';
        $prefixURL = str_replace($alias, $inputHTML, $profileURL);

        // For backward compatibility issues, as we changed from ID-USER to ID:USER in 2.0,
        // we also need to test older urls.
        if ($prefixURL == $profileURL) {
            $prefixURL = CString::str_ireplace(CString::str_ireplace(':', '-', $alias), $inputHTML, $profileURL);
        }

        $tmpl->set('prefixURL', $prefixURL);
        $tmpl->set('user', $user);

        $content = $tmpl->fetch('ajax.updateurl');

        $json['title']     = JText::_('COM_COMMUNITY_PROFILE_CHANGE_ALIAS');
        $json['html']      = $content;
        $json['btnCancel'] = JText::_('COM_COMMUNITY_CANCEL_BUTTON');
        $json['btnUpdate'] = JText::_('COM_COMMUNITY_UPDATE_BUTTON');

        die( json_encode($json) );
    }

    /**
     * Resize user's thumbnail from the source image
     *
     * @param Object $imgObj
     * @param String $src
     *
     */
    public function ajaxUpdateThumbnail($sourceX, $sourceY, $width, $height, $hideSave = false) {
        $filter = JFilterInput::getInstance();
        $sourceX = $filter->clean($sourceX, 'float');
        $sourceY = $filter->clean($sourceY, 'float');
        $width = $filter->clean($width, 'float');
        $height = $filter->clean($height, 'float');
        $hideSave = $filter->clean($hideSave, 'bool');

        // Fetch the thumbnail remotely. This is necessary since the user
        // profile picture might not be stored locally
        $objResponse = new JAXResponse();
        $my = CFactory::getUser();

        if ($my->id && $my->_avatar != '') {
            CPhotosHelper::updateAvatar('profile', $my->id, $sourceX, $sourceY, $width, $height);
            $objResponse->addScriptCall('refreshThumbnail');
        } else {
            return $this->ajaxBlockUnregister();
        }

        return $objResponse->sendResponse();
    }

    /**
     * 	Check if permitted to play the video
     *
     * 	@param	int		$myid		The current user's id
     * 	@param	int		$userid		The active profile user's id
     * 	@param	int		$permission	The video's permission
     * 	@return	bool	True if it's permitted
     * 	@since	1.2
     */
    public function isPermitted($myid = 0, $userid = 0, $permissions = 0) {
        if ($permissions == 0)
            return true; // public

        if (COwnerHelper::isCommunityAdmin()) {
            return true;
        }

        $relation = 0;

        if ($myid != 0)
            $relation = 20; // site members

        if (CFriendsHelper::isConnected($myid, $userid))
            $relation = 30; // friends

        if (COwnerHelper::isMine($myid, $userid)) {
            $relation = 40; // mine
        }

        if ($relation >= $permissions) {
            return true;
        }

        return false;
    }

    /**
     * Ban user from the system
     * */
    public function banuser() {
        //CFactory::load( 'helpers', 'owner' );
        $mainframe = JFactory::getApplication();
        $jinput = $mainframe->input;

        $message = '';
        $userId = $jinput->post->getInt('userid', '');
        $blocked = $jinput->post->get('blocked', 0);

        $my = CFactory::getUser();
        $url = CRoute::_('index.php?option=com_community&view=frontpage', false);

        if ($my->id == 0) {
            return $this->blockUnregister();
        }

        if (COwnerHelper::isCommunityAdmin()) {
            $user = CFactory::getUser($userId);

            if ($user->id) {
                $user->block = ( $blocked == 1 ) ? 0 : 1;
                $user->save();

                //event on user ban
                $appsLib = CAppPlugins::getInstance();
                $appsLib->loadApplications();

                $appsLib->triggerEvent('onAfterBanningUser', array($my->id, $userId));

                $message = ( $blocked == 1 ) ? JText::_('COM_COMMUNITY_USER_UNBANNED') : JText::_('COM_COMMUNITY_USER_BANNED');
            } else {
                $message = JText::_('COM_COMMUNITY_INVALID_PROFILE');
            }
        } else {
            $message = JText::_('COM_COMMUNITY_ADMIN_ACCESS_ONLY');
        }

        $mainframe->redirect($url, $message);
    }

    /**
     * Reverts profile picture for specific user
     * @return type
     */
    public function removepicture() {
        $mainframe = JFactory::getApplication();
        $jinput = JFactory::getApplication()->input;
        $message = '';
        $userId = $jinput->post->getInt('userid', '');
        $my = CFactory::getUser();

        if ($userId == 0) {
            /* redirect back to current user profile */
            $url = CRoute::_('index.php?option=com_community&view=profile', false);
        } else {
            /* redirect back to viewing user profile */
            $url = CRoute::_('index.php?option=com_community&view=profile&userid=' . $userId, false);
        }

        if ($my->id == 0) {
            return $this->blockUnregister();
        }

        //Admin and owner can use this function
        if (COwnerHelper::isCommunityAdmin($my->id) || $userId == $my->id) {
            $user = CFactory::getUser($userId);

            // User id should be valid and admin should not be allowed to block themselves.
            if ($user->id) {
                $userModel = CFactory::getModel('User');
                $userModel->removeProfilePicture($user->id, 'avatar');
                $userModel->removeProfilePicture($user->id, 'thumb');

                $message = JText::_('COM_COMMUNITY_PROFILE_PICTURE_REMOVED');
            } else {
                $message = JText::_('COM_COMMUNITY_INVALID_PROFILE');
            }
        } else {
            $message = JText::_('COM_COMMUNITY_ADMIN_ACCESS_ONLY');
        }

        $mainframe->redirect($url, $message);
    }

    /**
     * Method is called from the reporting library. Function calls should be
     * registered here.
     *
     * return	String	Message that will be displayed to user upon submission.
     * */
    public function reportProfile($link, $message, $id) {
        $report = new CReportingLibrary();
        $config = CFactory::getConfig();
        $my = CFactory::getUser();

        if (!$config->get('enablereporting') || ( ( $my->id == 0 ) && (!$config->get('enableguestreporting') ) )) {
            return '';
        }

        $report->createReport(JText::_('COM_COMMUNITY_REPORT_BAD_USER'), $link, $message);

        $action = new stdClass();
        $action->label = 'COM_COMMUNITY_BLOCK_USER';
        $action->method = 'profile,blockProfile';
        $action->parameters = $id;
        $action->defaultAction = true;

        $report->addActions(array($action));

        return JText::_('COM_COMMUNITY_REPORT_SUBMITTED');
    }

    /**
     * Function that is called from the back end
     * */
    public function blockProfile($userId) {
        $user = CFactory::getUser($userId);

        //CFactory::load( 'helpers', 'owner' );

        if (COwnerHelper::isCommunityAdmin()) {

            if ($user->get('block') == 0) {
                $user->set('block', 1);
                $user->save();
                return JText::_('COM_COMMUNITY_USER_ACCOUNT_BANNED');
            } else {
                $user->set('block', 0);
                $user->save();
                return JText::_('COM_COMMUNITY_USER_ACCOUNT_UNBANNED');
            }
        }
    }

    /**
     * Responsible to display the edit profile form.
     * */
    public function edit() {
        CFactory::setActiveProfile();

        $mainframe = JFactory::getApplication();
        $jinput = $mainframe->input;
        $action = $jinput->post->get('action', '');
        $my = CFactory::getUser();
        $isAdminEdit = false; //indicates if the admin is editing the user account

        $userid = $jinput->get('userid', $my->id);
        if($userid != $my->id){
            //this is where admin edit the user profile
            if(COwnerHelper::isCommunityAdmin()){
                $user = CFactory::getUser($userid);
                $isAdminEdit = true;
            }else{
                // looks like someone is trying to edit someone elses account
                return false;
            }
        }else{
            $user = $my;
        }

        if ($user->id == 0) {
            /* Force redirect back to homepage */
            JFactory::getApplication()->redirect(CRoute::_('index.php?option=com_community&view=frontpage',false));
        }
        // Get/Create the model
        $model = $this->getModel('profile');
        $model->setProfile('hello me');

        $data = new stdClass();
        $data->profile = $model->getEditableProfile($user->id, $user->getProfileType());

        if ($action == 'profile') { /* JomSocial profile update */
            if ($this->_saveProfile($user->id)) {
                $msg = ($isAdminEdit) ? JText::_('COM_COMMUNITY_SETTINGS_SAVED_BY_ADMIN') : JText::_('COM_COMMUNITY_SETTINGS_SAVED');

                $mainframe->redirect(CRoute::_('index.php?option=com_community&view=profile&task=edit&userid='.$user->id, false), $msg);
            } else {
                $postData = $jinput->post->getArray();
                foreach ($data->profile['fields'] as $key => $fields) {
                    foreach ($fields as $key2 => $field) {
                        $fieldValue = isset( $postData[ 'field' . $field['id'] ] ) ? $postData[ 'field' . $field['id'] ] : NULL;
                        if ( is_array($fieldValue) ) {
                            $glue = '';
                            if ( $field['type'] == 'birthdate' ) {
                                $glue = '-';
                            } else if ( $field['type'] == 'checkbox' ) {
                                $glue = ',';
                            }
                            $fieldValue = implode( $glue, $fieldValue );
                        }
                        $data->profile['fields'][$key][$key2]['value'] = $fieldValue;
                    }
                }
            }
        } elseif ($action == 'detail') { /* Joomla! user detail update */
            $this->save();
        }

        /* template display */
        $document = JFactory::getDocument();

        $viewType = $document->getType();
        $viewName = $jinput->get('view', $this->getName());


        $lang = JFactory::getLanguage();
        $lang->load(COM_USER_NAME);

        // Check if user is really allowed to edit.
        //$params = $mainframe->getParams();
        $params = null;
        // check to see if Frontend User Params have been enabled
        $usersConfig = JComponentHelper::getParams('com_users');
        $check = $usersConfig->get('frontend_userparams');

        if ($check == '1' || $check == 1 || $check == NULL) {
            if ($user->authorise(COM_USER_NAME, 'edit')) {
                $params = $user->getParameters(true);

                //In Joomla 1.6, $params will be a JRegistry class, whereas it was JParameter in 1.5
                //render() does not exist in JRegistry. Will need to translate the JForm XML in 1.6 to those acceptable for JParameter in 1.5.
                if (get_class($params) != 'JParameter') {

                    $vals = $params->toArray();
                    $params = CJForm::getInstance('editDetails', JPATH_ADMINISTRATOR . '/components/com_users/models/forms/user.xml');

                    //set data for the form
                    foreach ($vals as $k => $v) {
                        $params->setValue($k, 'params', $v);
                    }
                }
            } else {
                //user can only edit front end value [ > 1.5, user can only edit timezone and language ]
                $params = $user->getParameters(true);

                if ((get_class($params) != 'JParameter' || get_class($params) != 'CParameter')) {
                    $vals = $params->toArray();
                    $params = CJForm::getInstance('editDetails', JPATH_ADMINISTRATOR . '/components/com_users/models/forms/user.xml');

                    //set data for the form
                    foreach ($vals as $k => $v) {
                        //@since 2.6, accept timezone and language only
                        if ($k == 'timezone' || $k == 'language') {
                            $params->setValue($k, 'params', $v);
                        } else {
                            $stat = $params->removeField($k, 'params');
                        }
                    }
                }
            }
        }


        $my = CFactory::getUser($user->id);
        $config = CFactory::getConfig();

        $myParams = $my->getParams();
        $myDTS = $myParams->get('daylightsavingoffset');
        $cOffset = ( $myDTS != '' ) ? $myDTS : $config->get('daylightsavingoffset');

        $dstOffset = array();
        $counter = -4;
        for ($i = 0; $i <= 8; $i++) {
            $dstOffset[] = JHTML::_('select.option', $counter, $counter);
            $counter++;
        }

        $offSetLists = JHTML::_('select.genericlist', $dstOffset, 'daylightsavingoffset', 'class="joms-select"', 'value', 'text', $cOffset);

        $data->params = $params;
        $data->offsetList = $offSetLists;

        $view = $this->getView($viewName, '', $viewType);

        $this->_icon = 'edit';

        if (!$data->profile) {
            echo $view->get('error', JText::_('COM_COMMUNITY_USER_NOT_FOUND'));
        } else {
            echo $view->get(__FUNCTION__, $data);
        }
    }

    public function editDetails() {
        //$user		= CFactory::getUser();
        $mainframe = JFactory::getApplication();
        $jinput = $mainframe->input;
        //editDetails page is merge with edit page
        $mainframe->redirect(CRoute::_('index.php?option=com_community&view=profile&task=edit#detailSet', false), '', '');
    }

    public function save() {
        // Check for request forgeries
        $mainframe = JFactory::getApplication();
        $jinput = $mainframe->input;
        JSession::checkToken() or jexit(JText::_('COM_COMMUNITY_INVALID_TOKEN'));

        JFactory::getLanguage()->load(COM_USER_NAME);

        $user = JFactory::getUser();
        $my = CFactory::getUser();
        //$userid = $jinput->post->get('id', 0, 'int');
        $userid = $jinput->get('id', $my->id);
        $user = false;

        // preform security checks
        if($userid != $my->id){
            //this is where admin edit the user info
            if(COwnerHelper::isCommunityAdmin()){
                $user = CFactory::getUser($userid);
                $isAdminEdit = true;
            }else{
                // looks like someone is trying to edit someone elses account
                echo $this->blockUnregister();
                return false;
            }
        }else{
            $user = $my;
        }

        if (!$user) {
            echo $this->blockUnregister();
            return;
        }

        $username = $user->get('username');

        //if joomla settings allow change login name
        if(JComponentHelper::getParams('com_users')->get('change_login_name')){
            $username = $jinput->get('username','','string');
        }

        //clean request
        $post = $jinput->post->getArray();
        $post['username'] = $username;
        $post['password'] = $jinput->post->get('password', '', 'RAW');
        $post['password2'] = $jinput->post->get('password2', '', 'RAW');

        //check email
        $post['email'] = $post['jsemail'];
        $email = $post['email'];
        $emailPass = $post['emailpass'];
        $modelReg = $this->getModel('register');

        //CFactory::load( 'helpers', 'validate' );
        if (!CValidateHelper::email($email)) {
            $msg = JText::sprintf('COM_COMMUNITY_INVITE_EMAIL_INVALID', $email);
            $mainframe->redirect(CRoute::_('index.php?option=com_community&view=profile&task=editDetails', false), $msg, 'error');
            return false;
        }

        if (!empty($email) && ($email != $emailPass) && $modelReg->isEmailExists(array('email' => $email))) {
            $msg = JText::sprintf('COM_COMMUNITY_EMAIL_EXIST', $email);
            $msg = stripslashes($msg);
            $mainframe->redirect(CRoute::_('index.php?option=com_community&view=profile&task=editDetails', false), $msg, 'error');
            return false;
        }

        // get the redirect
        $return = CRoute::_('index.php?option=com_community&view=profile&task=editDetails', false);

        // do a password safety check
        $changePassword = false;
        if (JString::strlen($post['jspassword']) || JString::strlen($post['jspassword2'])) {
            // so that "0" can be used as password e.g.
            if ($post['jspassword'] != $post['jspassword2']) {
                $msg = JText::_('PASSWORDS_DO_NOT_MATCH');
                $mainframe->redirect(CRoute::_('index.php?option=com_community&view=profile&task=editDetails', false), $msg, 'error');
                return false;
            }else{
                $changePassword = true;

                //Jooomla 3.2.0 fix. TO be remove in future
                if (version_compare(JVERSION, '3.2.0', '>=')) {
                    $salt = JUserHelper::genRandomPassword(32);
                    $crypt = JUserHelper::getCryptedPassword($post['jspassword'], $salt);
                    $password = $crypt . ':' . $salt;
                } else {
                    // Don't re-encrypt the password
                    // JUser bind has encrypted the password
                    if(class_exists(JUserHelper) && method_exists(JUserHelper,'hashpassword')){
                        $password = JUserHelper::hashPassword($post['jspassword']);
                    }else{
                        $password = $post['jspassword'];
                    }
                }
            }
        }

        // Handle the two factor authentication setup
        $data = $post['jform'];
        if (array_key_exists('twofactor', $data))
        {

            if(!class_exists('UsersModelUser')){
                require(JPATH_ROOT.'/administrator/components/com_users/models/user.php');
            }

            $model = new UsersModelUser();

            $twoFactorMethod = $data['twofactor']['method'];

            $userId = $user->id;

            // Get the current One Time Password (two factor auth) configuration
            $otpConfig = $model->getOtpConfig($userId);

            if ($twoFactorMethod != 'none')
            {
                // Run the plugins
                FOFPlatform::getInstance()->importPlugin('twofactorauth');
                $otpConfigReplies = FOFPlatform::getInstance()->runPlugins('onUserTwofactorApplyConfiguration', array($twoFactorMethod));

                // Look for a valid reply
                foreach ($otpConfigReplies as $reply)
                {
                    if (!is_object($reply) || empty($reply->method) || ($reply->method != $twoFactorMethod))
                    {
                        continue;
                    }

                    $otpConfig->method = $reply->method;
                    $otpConfig->config = $reply->config;

                    break;
                }

                // Save OTP configuration.
                $model->setOtpConfig($userId, $otpConfig);

                // Generate one time emergency passwords if required (depleted or not set)
                if (empty($otpConfig->otep))
                {
                    $oteps = $model->generateOteps($userId);
                }
            }
            else
            {
                $otpConfig->method = 'none';
                $otpConfig->config = array();
                $model->setOtpConfig($userId, $otpConfig);
            }

            // Unset the raw data
            unset($data['twofactor']);

        }

        // we don't want users to edit certain fields so we will unset them
        unset($post['gid']);
        unset($post['block']);
        unset($post['usertype']);
        unset($post['registerDate']);
        unset($post['activation']);

        //update CUser param 1st so that the new value will not be replace wif the old one.
        $my = $user;
        $params = $my->getParams();
        $postvars = $post['daylightsavingoffset'];
        $params->set('daylightsavingoffset', $postvars);


        // Store FB prefernce o ly FB connect data
        $connectModel = CFactory::getModel('Connect');
        if ($connectModel->isAssociated($user->id)) {
            $postvars = !empty($post['postFacebookStatus']) ? 1 : 0;
            $my->_cparams->set('postFacebookStatus', $postvars);
        }

        if($changePassword){
            $my->set('password', $password);
        }

        /* Save for CUser */
        $my->save();

        $model = CFactory::getModel('profile');
        $editSuccess = true;
        $msg = JText::_('COM_COMMUNITY_SETTINGS_SAVED');
        $jUser = JFactory::getUser($my->id);

        // Bind the form fields to the user table
        if (!$jUser->bind($post)) {
            $editSuccess = false;
        }

        // Store the web link table to the database
        if (!$jUser->save()) {
            $editSuccess = false;
        }

        if ($editSuccess && !$isAdminEdit) {
            /* Update Joomla! User session */
            $session = JFactory::getSession();
            $session->set('user', $jUser);
            // User with FB Connect, store post preference
            //execute the trigger
            $appsLib = CAppPlugins::getInstance();
            $appsLib->loadApplications();

            $userRow = array();
            $userRow[] = $jUser;

            $appsLib->triggerEvent('onUserDetailsUpdate', $userRow);
        }

        $additionalInfo = ($isAdminEdit) ? '&userid='.$user->id : '';
        $mainframe->redirect(CRoute::_('index.php?option=com_community&view=profile&task=edit'.$additionalInfo, false), $msg);
    }

    /**
     * Show rss feed for this user
     */
    public function feed() {
        $document = JFactory::getDocument();

        $item = new JFeedItem();
        $item->author = '';
        $document->addItem($item);
    }

    /**
     * Saves a user's profile
     *
     * @access	private
     * @param	none
     */
    private function _saveProfile($userid = '') {
        $model = $this->getModel('profile');
        $usermodel = $this->getModel('user');
        $document = JFactory::getDocument();
        $my = CFactory::getUser($userid);
        $mainframe = JFactory::getApplication();
        $jinput = $mainframe->input;
        $input = CFactory::getInput();

        if ($my->id == 0) {
            return $this->blockUnregister();
        }

        $appsLib = CAppPlugins::getInstance();
        $saveSuccess = $appsLib->triggerEvent('onFormSave', array('jsform-profile-edit'));

        if (empty($saveSuccess) || !in_array(false, $saveSuccess)) {
            $values = array();
            $profiles = $model->getEditableProfile($my->id, $my->getProfileType());

            foreach ($profiles['fields'] as $group => $fields) {

                foreach ($fields as $data) {
                    $fieldValue = new stdClass();

                    // Get value from posted data and map it to the field.
                    // Here we need to prepend the 'field' before the id because in the form, the 'field' is prepended to the id.
                    // Grab raw, unfiltered data
                    $postData = $input->post->get('field' . $data['id'], '', 'RAW'); //
                    // Retrieve the privacy data for this particular field.
                    $fieldValue->access = $jinput->post->getInt('privacy' . $data['id'], 0);
                    $fieldValue->value = CProfileLibrary::formatData($data['type'], $postData);

                    //@since 4.2 assign params if needed
                    if($data['type'] == 'birthdate'){ //@since 4.2 date has special value if specified
                        //the third parameter should be hide year or not
                        $fieldValue->params = CFieldsDate::getHideYearParams($postData);
                    }

                    if (get_magic_quotes_gpc()) {
                        $fieldValue->value = stripslashes($fieldValue->value);
                    }

                    $values[$data['id']] = $fieldValue;

                    // @rule: Validate custom profile if necessary
                    if (!CProfileLibrary::validateField($data['id'], $data['type'], $values[$data['id']]->value, $data['required'], $data['visible'])) {
                        // If there are errors on the form, display to the user.
                        // If it is a drop down selection, use a different message
                        $message = '';
                        switch ($data['type']) {
                            case 'select':
                                $message = JText::sprintf('COM_COMMUNITY_FIELD_SELECT_EMPTY', $data['name']);
                                break;
                            case 'url':
                                $message = JText::sprintf('COM_COMMUNITY_FIELD_INVALID_URL', $data['name']);
                                break;
                            default:
                                $data['value'] = $values[$data['id']]->value;
                                $message = CProfileLibrary::getErrorMessage($data);
                        }

                        $mainframe->enqueueMessage(CTemplate::quote($message), 'error');
                        return false;
                    }
                }
            }

            // Rebuild new $values with field code
            $valuesCode = array();

            foreach ($values as $key => $val) {
                $fieldCode = $model->getFieldCode($key);

                if ($fieldCode) {
                    // For backward compatibility, we can't pass in an object. We need it to behave
                    // like 1.8.x where we only pass values.
                    $valuesCode[$fieldCode] = $val->value;
                }
            }

            $saveSuccess = false;

            $appsLib = CAppPlugins::getInstance();
            $appsLib->loadApplications();

            // Trigger before onBeforeUserProfileUpdate
            $args = array();
            $args[] = $my->id;
            $args[] = $valuesCode;
            $result = $appsLib->triggerEvent('onBeforeProfileUpdate', $args);

            $optionList = $model->getAllList();

            foreach ($optionList as $list) {
                // $optionList return all the list, even if the field is disabled
                // So, need to check if we're using it or not first
                if (isset($values[$list['id']]) && is_array($list['options'])) {
                    $option = trim($values[$list['id']]->value);

                    $option = str_replace('&amp;', '&', $option);
                    $option = str_replace('&quot;', '"', $option);

                    if (JString::strlen(JString::trim($option)) != 0 && !in_array($option, $list['options'])) {
                        if (!in_array($option, CProfile::getCountryList())) {
                            $result[] = false;
                        }
                    }
                }
            }

            // make sure none of the $result is false
            if (!$result || (!in_array(false, $result) )) {
                $saveSuccess = true;
                $model->saveProfile($my->id, $values);
            }
        }

        // Trigger before onAfterUserProfileUpdate
        $args = array();
        $args[] = $my->id;
        $args[] = $saveSuccess;
        $result = $appsLib->triggerEvent('onAfterProfileUpdate', $args);

        if ($saveSuccess) {
            CUserPoints::assignPoint('profile.save');
            return true;
        } else {
            $mainframe->enqueueMessage(JText::_('COM_COMMUNITY_PROFILE_NOT_SAVED'), 'error');
            return false;
        }
    }

    /**
     * Displays front page profile of user
     *
     * @access	public
     * @param	none
     * @returns none
     */
    public function display($cacheable = false, $urlparams = false) {
        // By default, display the user profile page
        $document = JFactory::getDocument();
        $document->setTitle(JText::_('COM_COMMUNITY_PROFILE'));

        $this->profile();
    }

    private function _validVanityURL($alias, $userId) {


        $model = CFactory::getModel('Profile');
        $user = CFactory::getUser($userId);

        if (!$model->aliasExists($alias, $userId) && CValidateHelper::alias($alias)) {
            return true;
        }

        return false;
    }

    public function updateAlias() {
        $my = CFactory::getUser();
        $jinput = JFactory::getApplication()->input;

        $userId = $jinput->post->getInt('userid', 0);

        if (!COwnerHelper::isCommunityAdmin() && $my->id != $userId) {
            JFactory::getApplication()->enqueueMessage(JText::_('COM_COMMUNITY_RESTRICTED_ACCESS'), 'error');
            return;
        }

        $mainframe = JFactory::getApplication();
        $alias = $jinput->post->get('alias');
        $style = 'message';

        if ($userId != 0) {
            $user = CFactory::getUser($userId);

            $alias = JFilterOutput::stringURLSafe(urlencode($alias));
            if ($this->_validVanityURL($alias, $user->id)) {

                $user->set('_alias', $alias);
                $user->save('params');
                $message = JText::_('COM_COMMUNITY_ALIAS_UPDATED');
            } else {
                $message = JText::_('COM_COMMUNITY_ALIAS_ALREADY_EXISTS');
                $style = 'error';
            }
            $mainframe->redirect(CRoute::_('index.php?option=com_community&view=profile&userid=' . $userId, false), $message, $style);
        }
    }

    public function preferences() {
        $view = $this->getView('profile');
        $my = CFactory::getUser();
        $mainframe = JFactory::getApplication();
        $jinput = $mainframe->input;

        if ($my->id == 0) {
            return $this->blockUnregister();
        }

        $method = $jinput->getMethod();

        if ($method == 'POST') {
            $appsLib = CAppPlugins::getInstance();
            $saveSuccess = $appsLib->triggerEvent('onFormSave', array('jsform-profile-preferences'));

            if (empty($saveSuccess) || !in_array(false, $saveSuccess)) {
                $params = $my->getParams();
                $postvars = $jinput->post->getArray();
                $activity = $jinput->getInt('activityLimit');
                $profileLikes = $jinput->getInt('profileLikes', 0);
                $showOnlineStatus = $jinput->getInt('showOnlineStatus', 0);
                $editSuccess = true;
                $message = JText::_('COM_COMMUNITY_PREFERENCES_SETTINGS_SAVED');

                $mobileView = $jinput->get('mobileView', NULL, 'NONE');
                $params->set('mobileView', $mobileView);

                if ($activity != 0) {
                    $params->set('activityLimit', $activity);
                    $params->set('profileLikes', $profileLikes);
                    $params->set('showOnlineStatus', $showOnlineStatus);

                    //$jConfig = JFactory::getConfig();
                    $model = CFactory::getModel('Profile');

                    //if( $jConfig->getValue( 'sef' ) && isset( $postvars['alias'] ) && !empty( $postvars['alias'] ) )
                    if ($mainframe->get('sef') && isset($postvars['alias']) && !empty($postvars['alias'])) {
                        $alias = $jinput->post->get('alias');

                        $alias = JFilterOutput::stringURLSafe(urlencode($alias));
                        if ($this->_validVanityURL($alias, $my->id)) {
                            $my->set('_alias', $alias);
                        } else {
                            $message = JText::_('COM_COMMUNITY_ALIAS_ALREADY_EXISTS');
                            $editSuccess = false;
                        }
                    }
                    $my->save('params');

                    if ($editSuccess) {
                        $mainframe->enqueueMessage($message);
                    } else {
                        $mainframe->enqueueMessage($message, 'error');
                    }
                } else {
                    $mainframe->enqueueMessage(JText::_('COM_COMMUNITY_PREFERENCES_INVALID_VALUE'), 'error');
                }

                $postvars['search_email'] = $searchMail = $jinput->get('search_email', 0, 'NONE');
                $my->_search_email = $searchMail;

                $previousProfilePermission = $my->get('privacyProfileView');

                $activityModel = CFactory::getModel('activities');

                if (isset($postvars['resetPrivacyPhotoView'])) {
                    //Update all photos and album permission
                    $photoPermission = $jinput->post->get('privacyPhotoView', 0, 'INT');
                    $photoModel = CFactory::getModel('photos');
                    $photoModel->updatePermission($my->id, $photoPermission);
                    // Update all photos activity stream permission
                    $activityModel->updatePermission($photoPermission, null, $my->id, 'photos');

                    unset($postvars['resetPrivacyPhotoView']);
                }

                if (isset($postvars['resetPrivacyVideoView'])) {
                    //Update all videos permission
                    $videoPermission = $jinput->post->get('privacyVideoView', 0, 'INT');
                    $videoModel = CFactory::getModel('videos');
                    $videoModel->updatePermission($my->id, $videoPermission);
                    // Update all videos activity stream permission
                    $activityModel->updatePermission($videoPermission, null, $my->id, 'videos');

                    unset($postvars['resetPrivacyVideoView']);
                }

                //save notificaiton settings
                if (isset($postvars['alias']))
                    unset($postvars['alias']);
                if (isset($postvars['activityLimit']))
                    unset($postvars['alias']);
                if (isset($postvars['profileLikes']))
                    unset($postvars['alias']);

                foreach ($postvars as $key => $val) {
                    $params->set($key, $val);
                }

                $my->save('params');
            }
        }

        echo $view->get(__FUNCTION__);
    }

    /**
     * Allow user to set their privacy setting.
     * User privacy setting is actually just part of their params
     */
    public function privacy() {
        CFactory::setActiveProfile();

        //privacy task is moved to preference task
        $mainframe = JFactory::getApplication();
        $mainframe->redirect(CRoute::_('index.php?option=com_community&view=profile&task=preferences&Itemid=435#privacy', false), '', '');
    }

    /**
     * Allow user to set their email and notifications
     */
    public function email() {
        CFactory::setActiveProfile();

        //privacy task is moved to preference task
        $mainframe = JFactory::getApplication();
        $mainframe->redirect(CRoute::_('index.php?option=com_community&view=profile&task=preferences#email', false), '', '');
    }

    /**
     * Viewing a user's profile
     *
     * @access	public
     * @param	none
     * @return  none
     */
    public function profile() {
        $jinput = JFactory::getApplication()->input;
        $userid = $jinput->get('userid', 0, 'INT');
        $activityId = $jinput->get->get('actid', 0, 'INT');


        $data = new stdClass();
        $model = $this->getModel('profile');
        $my = CFactory::getUser();

        // Test if userid is 0, check if the user is viewing its own profile.
        if ($userid == 0 && $my->id != 0) {
            $userid = $my->id;

            // We need to set the 'userid' var so that other code that uses
            $jinput->set('userid', $userid);
        }

        //on another hand, if this is an activity view, get the actor of the activity id
        if($activityId){
            $activity = JTable::getInstance('Activity', 'CTable');
            $activity->load($activityId);
            $jinput->set('userid', $activity->actor);
            $userid = $activity->actor;
        }

        $user = CFactory::getUser($userid);

        if (!isset($user->id) || $user->username == '' || $user->block == 1) {
            //JFactory::getApplication()->enqueueMessage(JText::_('COM_COMMUNITY_USER_NOT_FOUND'), 'error');
            $tmpl = new CTemplate();
            echo $tmpl->fetch('profile/missingprofile');
            return;
        }

        $data->profile = $model->getViewableProfile($userid, $user->getProfileType());

        //show error if user id invalid / not found.
        if (empty($data->profile['id'])) {
            $this->blockUnregister();
        } else {

            CFactory::setActiveProfile($userid);

            $my = CFactory::getUser();
            $document = JFactory::getDocument();
            $viewType = $document->getType();
            $view = $this->getView('profile', '', $viewType);

            // Try initialize the user id. Maybe that user is logged in.
            $user = CFactory::getUser($userid);
            $id = $user->id;

            $data->largeAvatar = $my->getAvatar();

            // Assign the user object for the current viewer whether a guest or a member
            $data->user = $user;
            $data->apps = array();


            if (!$id) {
                echo $view->get('error', JText::_('COM_COMMUNITY_USER_NOT_FOUND'));
            } elseif($activityId) {
                echo $view->get('singleActivity', $activity);
            }else
            {
                echo $view->get(__FUNCTION__, $data, $id);
            }
        }//end if else
    }

    /**
     * Links an existing photo in the system and use it as the profile picture
     * * */
    public function linkPhoto() {
        $jinput = JFactory::getApplication()->input;

        $id = $jinput->post->getInt('id', 0);
        $photoModel = CFactory::getModel('Photos');
        $my = CFactory::getUser();

        if ($my->id == 0) {
            return $this->blockUnregister();
        }

        if ($id == 0) {
            echo JText::_('COM_COMMUNITY_PHOTOS_INVALID_PHOTO_ID');
            return;
        }

        $photo = JTable::getInstance('Photo', 'CTable');
        $photo->load($id);

        if ($my->id != $photo->creator) {
            echo JText::_('COM_COMMUNITY_ACCESS_DENIED');
            return;
        }

        jimport('joomla.filesystem.file');
        jimport('joomla.utilities.utility');

        $view = $this->getView('profile');

        //CFactory::load( 'helpers' , 'image' );

        $my = CFactory::getUser();

        if ($my->id == 0) {
            return $this->blockUnregister();
        }

        $mainframe = JFactory::getApplication();

        // @todo: configurable width?
        $imageMaxWidth = 160;

        // Get a hash for the file name.
        $fileName = JApplicationHelper::getHash($photo->id . time());
        $hashFileName = JString::substr($fileName, 0, 24);
        $photoPath = JPATH_ROOT . '/' . $photo->image; //$photo->original;

        if ($photo->storage == 'file') {
            // @rule: If photo original file still exists, we will use the original file.
            if (!JFile::exists($photoPath)) {
                $photoPath = JPATH_ROOT . '/' . $photo->image;
            }

            // @rule: If photo still doesn't exists, we should not allow the photo to be changed.
            if (!JFile::exists($photoPath)) {
                $mainframe->redirect(CRoute::_('index.php?option=com_community&view=profile&task=uploadAvatar', false), JText::_('COM_COMMUNITY_PHOTOS_SET_AVATAR_ERROR'), 'error');
                return;
            }
        } else {
            //CFactory::load( 'helpers' , 'remote' );
            $content = CRemoteHelper::getContent($photo->getImageURI());

            if (!$content) {
                $mainframe->redirect(CRoute::_('index.php?option=com_community&view=profile&task=uploadAvatar', false), JText::_('COM_COMMUNITY_PHOTOS_SET_AVATAR_ERROR'), 'error');
                return;
            }
            //$jConfig   = JFactory::getConfig();
            //$photoPath = $jConfig->getValue('tmp_path').'/'.md5( $photo->image);
            $photoPath = $mainframe->get('tmp_path') . '/' . md5($photo->image);

            // Store image on temporary location
            JFile::write($photoPath, $content);
        }

        $info = getimagesize($photoPath);
        $extension = CImageHelper::getExtension($info['mime']);
        $config = CFactory::getConfig();

        $storage = JPATH_ROOT . '/' . $config->getString('imagefolder') . '/avatar';
        $storageImage = $storage . '/' . $hashFileName . $extension;
        $storageThumbnail = $storage . '/thumb_' . $hashFileName . $extension;
        $image = $config->getString('imagefolder') . '/avatar/' . $hashFileName . $extension;
        $thumbnail = $config->getString('imagefolder') . '/avatar/' . 'thumb_' . $hashFileName . $extension;
        $userModel = CFactory::getModel('user');

        // Only resize when the width exceeds the max.
        if (!CImageHelper::resizeProportional($photoPath, $storageImage, $info['mime'], $imageMaxWidth)) {
            $mainframe->enqueueMessage(JText::sprintf('COM_COMMUNITY_ERROR_MOVING_UPLOADED_FILE', $storageImage), 'error');
        }

        // Generate avatar
        if (!CImageHelper::createThumb(
            $photoPath,
            $storageImage,
            $info['mime'],
            COMMUNITY_AVATAR_PROFILE_WIDTH,
            COMMUNITY_AVATAR_PROFILE_HEIGHT
        )
        ) {
            $mainframe->enqueueMessage(JText::sprintf('COM_COMMUNITY_ERROR_MOVING_UPLOADED_FILE', $storageImage), 'error');
        }

        // Generate thumbnail
        if (!CImageHelper::createThumb($photoPath, $storageThumbnail, $info['mime'])) {
            $mainframe->enqueueMessage(JText::sprintf('COM_COMMUNITY_ERROR_MOVING_UPLOADED_FILE', $storageThumbnail), 'error');
        }

        if ($photo->storage != 'file') {
            //@rule: For non local storage, we need to remove the temporary photo
            JFile::delete($photoPath);
        }

        $userModel->setImage($my->id, $image, 'avatar');
        $userModel->setImage($my->id, $thumbnail, 'thumb');

        // Update the user object so that the profile picture gets updated.
        $my->set('_avatar', $image);
        $my->set('_thumb', $thumbnail);

        // Set album's default photo.
        $album = JTable::getInstance('Album', 'CTable');
        $album->load($photo->albumid);
        if ($album->id) {
            $album->photoid = $photo->id;
            $album->setParam('thumbnail', $photo->thumbnail);
            $album->store();
        }

        // Generate activity stream.
        $this->_addAvatarUploadActivity($my->id, $thumbnail);

        $mainframe->redirect(CRoute::_('index.php?option=com_community&view=profile&task=uploadAvatar', false), JText::_('COM_COMMUNITY_PHOTOS_SET_AVATAR_SUCCESS'));
    }

    /**
     * Upload a new user avatar, called from the profile/change avatar page
     */
    public function uploadAvatar() {

        CFactory::setActiveProfile();

        jimport('joomla.filesystem.file');
        jimport('joomla.utilities.utility');

        $view = $this->getView('profile');
        $mainframe = JFactory::getApplication();
        $jinput = $mainframe->input;

        $my = CFactory::getUser();

        if ($my->id == 0) {
            return $this->blockUnregister();
        }

        // If uplaod is detected, we process the uploaded avatar
        if ($jinput->post->get('action', '')) {
            $mainframe = JFactory::getApplication();

            $fileFilter = new JInput($jinput->files->getArray());
            $file = $fileFilter->get('Filedata', '', 'array');

            $userid = $my->id;

            if ($jinput->post->getInt('userid', '') != '') {
                $userid = $jinput->post->getInt('userid', '');
                $url = CRoute::_('index.php?option=com_community&view=profile&userid=' . $userid);

                $my = CFactory::getUser($userid);
            }

            if (!isset($file['tmp_name']) || empty($file['tmp_name'])) {
                $mainframe->enqueueMessage(JText::_('COM_COMMUNITY_NO_POST_DATA'), 'error');

                if (isset($url)) {
                    $mainframe->redirect($url);
                }
            } else {
                $config = CFactory::getConfig();
                $uploadLimit = (double) $config->get('maxuploadsize');
                $uploadLimit = ( $uploadLimit * 1024 * 1024 );

                // @rule: Limit image size based on the maximum upload allowed.
                if (filesize($file['tmp_name']) > $uploadLimit && $uploadLimit != 0) {
                    $mainframe->enqueueMessage(JText::sprintf('COM_COMMUNITY_VIDEOS_IMAGE_FILE_SIZE_EXCEEDED_MB',CFactory::getConfig()->get('maxuploadsize')), 'error');

                    if (isset($url)) {
                        $mainframe->redirect($url);
                    }

                    $mainframe->redirect(CRoute::_('index.php?option=com_community&view=profile&userid=' . $userid . '&task=uploadAvatar', false));
                }

                if (!CImageHelper::isValidType($file['type'])) {
                    $mainframe->enqueueMessage(JText::_('COM_COMMUNITY_IMAGE_FILE_NOT_SUPPORTED'), 'error');

                    if (isset($url)) {
                        $mainframe->redirect($url);
                    }

                    $mainframe->redirect(CRoute::_('index.php?option=com_community&view=profile&userid=' . $userid . '&task=uploadAvatar', false));
                }

                if (!CImageHelper::isValid($file['tmp_name'])) {
                    $mainframe->enqueueMessage(JText::_('COM_COMMUNITY_IMAGE_FILE_NOT_SUPPORTED'), 'error');

                    if (isset($url)) {
                        $mainframe->redirect($url);
                    }
                } else {
                    // @todo: configurable width?
                    //$imageMaxWidth    = 160;
                    //$imageMaxHeight   = 240;
                    // Get a hash for the file name.
                    $profileType = $my->getProfileType();
                    $fileName = JApplicationHelper::getHash($file['tmp_name'] . time());
                    $hashFileName = JString::substr($fileName, 0, 24);
                    $multiprofile = JTable::getInstance('MultiProfile', 'CTable');
                    $multiprofile->load($profileType);

                    $useWatermark = $profileType != COMMUNITY_DEFAULT_PROFILE && $config->get('profile_multiprofile') && !empty($multiprofile->watermark) ? true : false;
                    //@todo: configurable path for avatar storage?

                    $storage = JPATH_ROOT . '/' . $config->getString('imagefolder') . '/avatar';
                    /* physical path */
                    $storageImage = $storage . '/' . $hashFileName . CImageHelper::getExtension($file['type']);
                    $storageThumbnail = $storage . '/thumb_' . $hashFileName . CImageHelper::getExtension($file['type']);
                    /**
                     * reverse image use for cropping feature
                     * @uses <type>-<hashFileName>.<ext>
                     */
                    $storageReserve = $storage . '/profile-' . $hashFileName . CImageHelper::getExtension($file['type']);

                    /* relative path to save in database */
                    $image = $config->getString('imagefolder') . '/avatar/' . $hashFileName . CImageHelper::getExtension($file['type']);
                    $thumbnail = $config->getString('imagefolder') . '/avatar/' . 'thumb_' . $hashFileName . CImageHelper::getExtension($file['type']);

                    // filename for stream attachment
                    $imageAttachment = $config->getString('imagefolder') . '/avatar/' . $hashFileName . '_stream_' . CImageHelper::getExtension($file['type']);

                    $userModel = CFactory::getModel('user');

                    //Minimum height/width checking for Avatar uploads
                    list($currentWidth, $currentHeight) = getimagesize($file['tmp_name']);
                    /**
                     * Do square avatar 160x160
                     * @since 3.0
                     */
                    if ($currentWidth < COMMUNITY_AVATAR_PROFILE_WIDTH || $currentHeight < COMMUNITY_AVATAR_PROFILE_HEIGHT) {
                        $mainframe->enqueueMessage(JText::sprintf('COM_COMMUNITY_ERROR_MINIMUM_AVATAR_DIMENSION', COMMUNITY_AVATAR_PROFILE_WIDTH, COMMUNITY_AVATAR_PROFILE_HEIGHT), 'error');

                        $mainframe->redirect(CRoute::_('index.php?option=com_community&view=profile&task=uploadAvatar', false));
                    }


//					// Only resize when the width exceeds the max.
//					if ( ! CImageHelper::resizeProportional($file['tmp_name'], $storageImage, $file['type'], $imageMaxWidth, $imageMaxHeight))
//					{
//						$mainframe->enqueueMessage(JText::sprintf('COM_COMMUNITY_ERROR_MOVING_UPLOADED_FILE', $storageImage), 'error');
//
//						if (isset($url))
//						{
//							$mainframe->redirect($url);
//						}
//					}

                    /**
                     * Generate square avatar
                     */
                    if (!CImageHelper::createThumb($file['tmp_name'], $storageImage, $file['type'], COMMUNITY_AVATAR_PROFILE_WIDTH, COMMUNITY_AVATAR_PROFILE_HEIGHT)) {
                        $mainframe->enqueueMessage(JText::sprintf('COM_COMMUNITY_ERROR_MOVING_UPLOADED_FILE', $storageImage), 'error');

                        if (isset($url)) {
                            $mainframe->redirect($url);
                        }
                    }

                    // Generate thumbnail
                    if (!CImageHelper::createThumb($file['tmp_name'], $storageThumbnail, $file['type'])) {
                        $mainframe->enqueueMessage(JText::sprintf('COM_COMMUNITY_ERROR_MOVING_UPLOADED_FILE', $storageThumbnail), 'error');

                        if (isset($url)) {
                            $mainframe->redirect($url);
                        }
                    }

                    /**
                     * Generate large image use for avatar thumb cropping
                     * It must be larget than profile avatar size because we'll use it for profile avatar recrop also
                     */
                    if ($currentWidth >= $currentHeight) {
                        if (!CImageHelper::resizeProportional($file['tmp_name'], $storageReserve, $file['type'], 0, COMMUNITY_AVATAR_RESERVE_HEIGHT)) {
                            $this->_showUploadError(true, JText::sprintf('COM_COMMUNITY_ERROR_MOVING_UPLOADED_FILE', $storageReserve));
                            return;
                        }
                    } else {
                        if (!CImageHelper::resizeProportional($file['tmp_name'], $storageReserve, $file['type'], COMMUNITY_AVATAR_RESERVE_WIDTH, 0)) {
                            $this->_showUploadError(true, JText::sprintf('COM_COMMUNITY_ERROR_MOVING_UPLOADED_FILE', $storageReserve));
                            return;
                        }
                    }

                    if ($useWatermark) {
                        // @rule: Before adding the watermark, we should copy the user's original image so that when the admin tries to reset the avatar,
                        // it will be able to grab the original picture.
                        if (!JFolder::exists(JPATH_ROOT . '/images/watermarks/original/')) {
                            JFolder::create(JPATH_ROOT . '/images/watermarks/original/');
                        }
                        JFile::copy($storageImage, JPATH_ROOT . '/images/watermarks/original/' . md5($my->id . '_avatar') . CImageHelper::getExtension($file['type']));
                        JFile::copy($storageThumbnail, JPATH_ROOT . '/images/watermarks/original/' . md5($my->id . '_thumb') . CImageHelper::getExtension($file['type']));

                        $watermarkPath = JPATH_ROOT . '/' . CString::str_ireplace('/', '/', $multiprofile->watermark);

                        list($watermarkWidth, $watermarkHeight) = getimagesize($watermarkPath);
                        list($avatarWidth, $avatarHeight) = getimagesize($storageImage);
                        list($thumbWidth, $thumbHeight) = getimagesize($storageThumbnail);

                        $watermarkImage = $storageImage;
                        $watermarkThumbnail = $storageThumbnail;

                        // Avatar Properties
                        $avatarPosition = CImageHelper::getPositions($multiprofile->watermark_location, $avatarWidth, $avatarHeight, $watermarkWidth, $watermarkHeight);

                        // The original image file will be removed from the system once it generates a new watermark image.
                        CImageHelper::addWatermark($storageImage, $watermarkImage, $file['type'], $watermarkPath, $avatarPosition->x, $avatarPosition->y);

                        //Thumbnail Properties
                        $thumbPosition = CImageHelper::getPositions($multiprofile->watermark_location, $thumbWidth, $thumbHeight, $watermarkWidth, $watermarkHeight);

                        // The original thumbnail file will be removed from the system once it generates a new watermark image.
                        CImageHelper::addWatermark($storageThumbnail, $watermarkThumbnail, $file['type'], $watermarkPath, $thumbPosition->x, $thumbPosition->y);

                        $my->set('_watermark_hash', $multiprofile->watermark_hash);
                        $my->save();
                    }

                    // Autorotate avatar based on EXIF orientation value
                    if ($file['type'] == 'image/jpeg') {
                        $orientation = CImageHelper::getOrientation($file['tmp_name']);
                        CImageHelper::autoRotate($storageImage, $orientation);
                        CImageHelper::autoRotate($storageThumbnail, $orientation);
                        CImageHelper::autoRotate($storageReserve, $orientation);
                    }


                    // @todo: Change to use table code and get rid of model code
                    $userModel->setImage($userid, $image, 'avatar');
                    $userModel->setImage($userid, $thumbnail, 'thumb');

                    // Update the user object so that the profile picture gets updated.
                    $my->set('_avatar', $image);
                    $my->set('_thumb', $thumbnail);

                    // @rule: once user changes their profile picture, storage method should always be file.
                    $my->set('_storage', 'file');

                    if (isset($url)) {
                        $mainframe->redirect($url);
                    }

                    // Generate activity stream.
                    $this->_addAvatarUploadActivity($userid, $thumbnail);

                    $this->cacheClean(array(COMMUNITY_CACHE_TAG_ACTIVITIES, COMMUNITY_CACHE_TAG_FRONTPAGE));
                }
            }
        }

        echo $view->get(__FUNCTION__);
    }

    private function _addAvatarUploadActivity($userid, $thumbnail) {
        if(CUserPoints::assignPoint('profile.avatar.upload')){
            // Generate activity stream.
            $act = new stdClass();
            $act->cmd = 'profile.avatar.upload';
            $act->actor = $userid;
            $act->target = 0;
            $act->title = '';
            $act->content = '';
            $act->app = 'profile.avatar.upload';
            $act->cid = 0;
            $act->comment_id = CActivities::COMMENT_SELF;
            $act->comment_type = 'profile.avatar.upload';

            $act->like_id = CActivities::LIKE_SELF;
            ;
            $act->like_type = 'profile.avatar.upload';

            // We need to make a copy of current avatar and set it as stream 'attachement'
            // which will only gets deleted once teh stream is deleted
            $params = new JRegistry();

            // store a copy of the avatar
            $imageAttachment = str_replace('thumb_', 'stream_', $thumbnail);
            $thumbnail = str_replace('thumb_', '', $thumbnail);

            JFile::copy($thumbnail, $imageAttachment);
            $params->set('attachment', $imageAttachment);

            // Add activity logging
            CActivityStream::add($act, $params->toString());
        }
    }

    /**
     * Upload a new user video.
     */
    public function linkVideo() {
        CFactory::setActiveProfile();
        $my = CFactory::getUser();
        $config = CFactory::getConfig();

        if ($my->id == 0) {
            return $this->blockUnregister();
        }

        if (!$config->get('enableprofilevideo')) {
            echo JText::_('COM_COMMUNITY_VIDEOS_PROFILE_VIDEO_DISABLE');
            return;
        }

        $view = $this->getView('profile');

        echo $view->get(__FUNCTION__);
    }

    public function editPage() {
        $my = CFactory::getUser();

        if ($my->id == 0) {
            return $this->blockUnregister();
        }

        $view = $this->getView('profile');

        echo $view->get(__FUNCTION__);
    }

    /**
     * Display drag&drop layout editing inetrface
     */
    public function editLayout() {
        $my = CFactory::getUser();

        if ($my->id == 0) {
            return $this->blockUnregister();
        }

        $view = $this->getView('profile');

        echo $view->get(__FUNCTION__);
    }

    /**
     * Full application view
     */
    public function app() {
        require_once JPATH_COMPONENT . '/libraries/apps.php';

        $view = $this->getView('profile');
        echo $view->get('appFullView');
    }

    /**
     * Show pop up error message screen
     * for invalid image file upload
     */
    public function ajaxErrorFileUpload() {
        $objResponse = new JAXResponse();

        $html = '<div style="overflow:auto; height:200px; position: absolute-nothing;">' . JText::_('COM_COMMUNITY_PHOTOS_UPLOAD_DESC') . '</div>';
        $actions = '<button class="btn" onclick="javascript:cWindowHide();" name="close">' . JText::_('COM_COMMUNITY_BUTTON_CLOSE_BUTTON') . '</button>';

        $objResponse->addScriptCall('cWindowAddContent', $html, $actions);

        return $objResponse->sendResponse();
    }

    /*
     * Allow users to delete their own profile
     *
     */

    public function deleteProfile() {
        $jinput = JFactory::getApplication()->input;
        $view = $this->getView('profile');
        $method = $jinput->getMethod();
        $my = CFactory::getUser();
        $config = CFactory::getConfig();

        if ($my->id == 0) {
            return $this->blockUnregister();
        }

        //not allow to delete admin profile
        if (COwnerHelper::isCommunityAdmin($my->id)) {
            echo JText::_('COM_COMMUNITY_CANNOT_DELETE_PROFILE_ADMIN');
            return;
        }

        if (!$my->authorise('community.delete', 'profile.' . $my->id, $my)) {
            echo JText::_('COM_COMMUNITY_RESTRICTED_ACCESS');
            return;
        }


        if ($method == 'POST') {
            // Instead of delete the user straight away,
            // we'll block the user and notify the admin.
            // Admin then would delete the user from backend
            JSession::checkToken() or jexit(JText::_('COM_COMMUNITY_INVALID_TOKEN'));
            $my->set('block', 1);
            $my->save();

            // Remove profile connect.
            $connectTable = JTable::getInstance('Connect', 'CTable');
            $connectTable->delete($my->id);

            // send notification email
            $model = CFactory::getModel('profile');
            $emails = $model->getAdminEmails();
            $url = JURI::root() . 'administrator/index.php?option=com_community&view=users&layout=edit&id=' . $my->id;

            // Add notification


            $params = new CParameter('');
            $params->set('userid', $my->id);
            $params->set('username', $my->getDisplayName());
            $params->set('url', $url);

            $subject = JText::sprintf('COM_COMMUNITY_USER_ACCOUNT_DELETED_SUBJECT');
            CNotificationLibrary::add('user_profile_delete', $my->id, $emails, $subject, '', 'user.deleted', $params);

            //reduce counter for group member
            $groupTable = JTable::getInstance('Group', 'CTable');
            $groupsModel = CFactory::getModel('groups');
            $groups = $groupsModel->getGroups($my->id);

            //do processing
            foreach ($groups as $group) {
                $group->membercount -=1;
                $groupTable->bind($group);
                $groupTable->store();

                //Delete Group Member
                $groupTable->deleteMember($group->id, $my->id);
            }

            //reduce counter for event member count
            $eventTable = JTable::getInstance('Event', 'CTable');
            $eventModel = CFactory::getModel('events');
            $events = $eventModel->getEvents(null, $my->id);

            foreach ($events as $event) {
                $event->confirmedcount -= 1;
                $eventTable->bind($event);
                $eventTable->store();

                //remove guest
                $eventTable->removeGuest($my->id, $event->id);
            }

            // logout and redirect the user
            $mainframe = JFactory::getApplication();
            $mainframe->logout($my->id);
            $mainframe->redirect(CRoute::_('index.php?option=com_community', false));
        }

        echo $view->get(__FUNCTION__);
    }

    /**
     * Ajax retreive Featured Profile Information
     * @since 2.6
     */
    public function ajaxShowProfileFeatured($userId) {


        $my = CFactory::getUser();
        $objResponse = new JAXResponse();
        $featureduser = CFactory::getUser($userId);
        $user = JTable::getInstance('MemberList', 'CTable');
        $user->load($userId);
        // Get group link
        // Get Avatar
        $avatar = $featureduser->getAvatar('avatar');

        // Get random picture
        // Get group link
        $userLink = CRoute::_('index.php?option=com_community&view=profile&userid=' . $userId);

        // Get unfeature icon
        $userUnfeature = '<a class="album-action remove-featured" title="' . JText::_('COM_COMMUNITY_REMOVE_FEATURED') . '" onclick="joms.featured.remove(\'' . $userId . '\',\'search\');" href="javascript:void(0);">' . JText::_('COM_COMMUNITY_REMOVE_FEATURED') . '</a>';
        $userStatus = $featureduser->getStatus();

        //Get Friend List
        $view = $this->getView('profile');
        $friendList = $view->modGetFriendsFeaturedHTML($userId);

        // Get like
        $likes = new CLike();
        $likesHTML = $likes->getHTML('profile', $userId, $my->id);

        $objResponse->addScriptCall('updateFeaturedProfile', $userId, $featureduser->getDisplayName(), $likesHTML, $avatar, $userLink, $userUnfeature, $userStatus, $friendList);
        $objResponse->sendResponse();
    }

    /**
     * Do show ignore confirm dialog
     */
    public function ajaxConfirmBlockUser($userId) {
        /* Get and clean input */
        $filter = JFilterInput::getInstance();
        $userId = $filter->clean($userId, 'int');
        /* Init variables & object */
        $my = CFactory::getUser();
        $config = CFactory::getConfig();

        /* Do not allow guest do this process */
        if ($my->id == 0) {
            return $this->ajaxBlockUnregister();
        }

        /* Can't ignore admin also */
        if (COwnerHelper::isCommunityAdmin($userId)) {
            return $this->ajaxRestrictBlockAdmin();
        }

        $json = array(
            'title'  => $config->get('sitename'),
            'html'   => JText::_('COM_COMMUNITY_CONFIRM_BLOCK_USER'),
            'btnYes' => JText::_('COM_COMMUNITY_YES_BUTTON'),
            'btnNo'  => JText::_('COM_COMMUNITY_NO_BUTTON')
        );

        die( json_encode($json) );
    }

    /**
     * Block a user
     */
    public function ajaxBlockUser($userId) {
        $filter = JFilterInput::getInstance();
        $userId = $filter->clean($userId, 'int');

        $my = CFactory::getUser();

        if ($my->id == 0) {
            return $this->ajaxBlockUnregister();
        }

        if (COwnerHelper::isCommunityAdmin($userId)) {
            return $this->ajaxRestrictBlockAdmin();
        }

        $my->blockUser($userId, 'block');

        die( json_encode( array('success' => true) ) );
    }

    /**
     * Do show ignore confirm dialog
     */
    public function ajaxConfirmUnBlockUser($userId) {
        /* Get and clean input */
        $filter = JFilterInput::getInstance();
        $userId = $filter->clean($userId, 'int');
        /* Init variables & object */
        $my = CFactory::getUser();
        $response = new JAXResponse();
        $config = CFactory::getConfig();

        /* Do not allow guest do this process */
        if ($my->id == 0) {
            return $this->ajaxBlockUnregister();
        }

        $json = array(
            'title'  => $config->get('sitename'),
            'html'   => JText::_('COM_COMMUNITY_CONFIRM_UNBLOCK_USER'),
            'btnYes' => JText::_('COM_COMMUNITY_YES_BUTTON'),
            'btnNo'  => JText::_('COM_COMMUNITY_NO_BUTTON')
        );

        die( json_encode($json) );
    }

    public function ajaxUnblockUser($userId, $layout = null) {
        $filter = JFilterInput::getInstance();
        $userId = $filter->clean($userId, 'int');

        $my = CFactory::getUser();
        $config = CFactory::getConfig();

        if ($my->id == 0) {
            return $this->ajaxBlockUnregister();
        }

        if (COwnerHelper::isCommunityAdmin($userId)) {
            return $this->ajaxRestrictBlockAdmin();
        }

        $my->unblockUser($userId);

        die( json_encode( array('success' => true) ) );
    }

    /**
     * Do show ignore confirm dialog
     */
    public function ajaxConfirmIgnoreUser($userId) {
        /* Get and clean input */
        $filter = JFilterInput::getInstance();
        $userId = $filter->clean($userId, 'int');
        /* Init variables & object */
        $my = CFactory::getUser();
        $config = CFactory::getConfig();
        /* Do not allow guest do this process */
        if ($my->id == 0) {
            return $this->ajaxBlockUnregister();
        }
        /* Can't ignore admin also */
        if (COwnerHelper::isCommunityAdmin($userId)) {
            return $this->ajaxRestrictBlockAdmin();
        }

        $json = array(
            'title'  => $config->get('sitename'),
            'html'   => JText::_('COM_COMMUNITY_CONFIRM_IGNORE_USER'),
            'btnYes' => JText::_('COM_COMMUNITY_YES_BUTTON'),
            'btnNo'  => JText::_('COM_COMMUNITY_NO_BUTTON')
        );

        die( json_encode($json) );
    }

    /**
     *
     * @param type $userId
     * @return typeDo ignore user
     */
    public function ajaxIgnoreUser($userId) {
        $me = CFactory::getUser();
        $me->blockUser($userId, 'ignore');

        $json = array( 'success' => true );

        die( json_encode($json) );
    }

    /**
     * Do show ignore confirm dialog
     */
    public function ajaxConfirmUnIgnoreUser($userId) {
        /* Get and clean input */
        $filter = JFilterInput::getInstance();
        $userId = $filter->clean($userId, 'int');
        /* Init variables & object */
        $my = CFactory::getUser();
        $response = new JAXResponse();
        $config = CFactory::getConfig();
        /* Do not allow guest do this process */
        if ($my->id == 0) {
            return $this->ajaxBlockUnregister();
        }
        /* Can't ignore admin also */
        if (COwnerHelper::isCommunityAdmin($userId)) {
            return $this->ajaxRestrictBlockAdmin();
        }

        $json = array(
            'title'  => $config->get('sitename'),
            'html'   => JText::_('COM_COMMUNITY_CONFIRM_UNIGNORE_USER'),
            'btnYes' => JText::_('COM_COMMUNITY_YES_BUTTON'),
            'btnNo'  => JText::_('COM_COMMUNITY_NO_BUTTON')
        );

        die( json_encode($json) );
    }

    public function ajaxUnIgnoreUser($userId, $layout = null) {
        $filter = JFilterInput::getInstance();
        $userId = $filter->clean($userId, 'int');

        $my = CFactory::getUser();
        $config = CFactory::getConfig();

        if ($my->id == 0) {
            return $this->ajaxBlockUnregister();
        }

        if (COwnerHelper::isCommunityAdmin($userId)) {
            return $this->ajaxRestrictBlockAdmin();
        }

        $my->unblockUser($userId);

        $json = array( 'success' => true );

        die( json_encode($json) );
    }

    /**
     * Un Ban member or friend (for ajax remove only)
     */
    public function unBlockUser() {
        $my = CFactory::getUser();
        $mainframe = JFactory::getApplication();
        $jinput = $mainframe->input;

        if ($my->id == 0) {
            return $this->blockUnregister();
        }

        $userId = $jinput->getInt('userid', '');
        $layout = $jinput->getString('layout', '');

        CFactory::load('libraries', 'block');
        $blockUser = new blockUser;
        $blockUser->unBlock($userId, $layout);
    }

    /**
     * Method to view profile video
     */
    public function video() {
        $view = $this->getView('profile');
        echo $view->get(__FUNCTION__);
    }

    /**
     * Method to view profile notification
     */
    public function notifications() {
        $my = CFactory::getUser();

        if ($my->id == 0) {
            return $this->blockUnregister();
        }

        $view = $this->getView('profile');
        echo $view->get(__FUNCTION__);
    }

    public function ajaxRemoveCover($userId) {
        $filter = JFilterInput::getInstance();
        $userId = $filter->clean($userId, 'int');

        $json = array();

        $my = CFactory::getUser();

        if ($my->id == 0) {
            return $this->ajaxBlockUnregister();
        }

        // @rule: Site admin & owner can access this function.
        if (COwnerHelper::isCommunityAdmin($my->id) || $userId == $my->id) {
            $json = array(
                'title'    => JText::_('COM_COMMUNITY_REMOVE_PROFILE_COVER'),
                'html'     => JText::_('COM_COMMUNITY_REMOVE_COVER_CONFIRMATION'),
                'btnNo'    => JText::_('COM_COMMUNITY_NO_BUTTON'),
                'btnYes'   => JText::_('COM_COMMUNITY_YES_BUTTON'),
                'redirUrl' => CRoute::_('index.php?option=com_community&view=profile&task=removecover', false),
            );
        }

        die( json_encode($json) );
    }

    public function removecover() {
        $mainframe = JFactory::getApplication();
        $jinput = $mainframe->input;

        $message = '';
        $userId = $jinput->post->get('userid', 0, 'INT');
        $my = CFactory::getUser();

        if ($userId == 0) {
            /* redirect back to current user profile */
            $url = CRoute::_('index.php?option=com_community&view=profile', false);
        } else {
            /* redirect back to viewing user profile */
            $url = CRoute::_('index.php?option=com_community&view=profile&userid=' . $userId, false);
        }

        if ($my->id == 0) {
            return $this->blockUnregister();
        }

        //Admin and owner can use this function
        if (COwnerHelper::isCommunityAdmin($my->id) || $userId == $my->id) {
            $user = CFactory::getUser($userId);

            // User id should be valid and admin should not be allowed to block themselves.
            if ($user->id) {
                $userModel = CFactory::getModel('User');
                $userModel->removeProfileCover($user->id);

                $userTable = JTable::getInstance('Profile', 'CTable');
                $userTable->load($user->id);

                $params = new CParameter($userTable->params);
                $params->set('coverPosition', 0);

                $userTable->params = $params->toString();
                $userTable->store();

                $message = JText::_('COM_COMMUNITY_PROFILE_COVER_REMOVED');
            } else {
                $message = JText::_('COM_COMMUNITY_INVALID_PROFILE');
            }
        } else {
            $message = JText::_('COM_COMMUNITY_ADMIN_ACCESS_ONLY');
        }

        $mainframe->redirect($url, $message);
    }

    public function ajaxFetchCard( $userId ) {
        $user = CFactory::getUser($userId);

        $params = $user->getParams();
        $config = CFactory::getConfig();
        // do not show if the settings is disabled
        if(!$config->get('show_profile_card')){
            die;
        }

        $tmpl = new CTemplate();
        $html = $tmpl
            ->set('user', $user)
            ->fetch('profile/hovercard');

        $json = array('html' => $html);

        die( json_encode($json) );

    }

    public function ajaxRotateAvatar( $type, $id, $direction = 'right' ) {
        $my = CFactory::getUser();

        if ( $my->id == 0 ) {
            return $this->blockUnregister();
        }

        if ( !( COwnerHelper::isCommunityAdmin() || $my->id == $id ) ) {
            $json = array( 'error' => JText::_('Forbidden access.') );
            die( json_encode( $json ) );
        }

        $config = CFactory::getConfig();
        $avatar = $my->get('_avatar');

        if ( !$avatar ) {
            $json = array( 'error' => JText::_('Avatar not set.') );
            die( json_encode( $json ) );
        }

        $storage = JPATH_ROOT . '/' . $config->getString('imagefolder') . '/avatar/';
        $avatarFilename = $storage . basename( $avatar );
        $cropperFilename = $storage . 'profile-' . basename( $avatar );

        if ( $direction === 'left' ) {
            $direction = 90;
        } else {
            $direction = -90;
        }

        CImageHelper::rotate( $avatarFilename, $avatarFilename, $direction );
        CImageHelper::rotate( $cropperFilename, $cropperFilename, $direction );

        $json = array(
            'success' => true,
            'avatar' => JURI::root(true) . '/' . $avatar
        );

        die( json_encode( $json ) );
    }

}
