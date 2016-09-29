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
jimport('joomla.application.component.view');

if (!class_exists('CommunityViewProfile')) {

    class CommunityViewProfile extends CommunityView {

        private function _addSubmenu() {
            $config = CFactory::getConfig();

            $this->addSubmenuItem('index.php?option=com_community&view=profile&task=uploadAvatar', JText::_('COM_COMMUNITY_PROFILE_AVATAR_EDIT'));

            if ($config->get('enableprofilevideo')) {
                $this->addSubmenuItem('index.php?option=com_community&view=profile&task=linkVideo', JText::_('COM_COMMUNITY_VIDEOS_EDIT_PROFILE_VIDEO'));
            }

            $this->addSubmenuItem('index.php?option=com_community&view=profile&task=edit', JText::_('COM_COMMUNITY_PROFILE_EDIT'));
            $this->addSubmenuItem('index.php?option=com_community&view=profile&task=preferences', JText::_('COM_COMMUNITY_EDIT_PREFERENCES'));
            $this->addSubmenuItem('index.php?option=com_community&view=profile&task=notifications', JText::_('COM_COMMUNITY_PROFILE_NOTIFICATIONS'));

            if ($config->get('profile_deletion')) {
                $this->addSubmenuItem('index.php?option=com_community&view=profile&task=deleteProfile', JText::_('COM_COMMUNITY_DELETE_PROFILE'), '', SUBMENU_RIGHT);
            }
        }

        /**
         * Return friends html block
         * @since 2.2.4
         * @return string
         */
        public function modGetFriendsHTML($userid = null) {
            $html = '';
            $tmpl = new CTemplate ( );

            $friendsModel = CFactory::getModel('friends');

            $my = CFactory::getUser($userid);
            $user = CFactory::getRequestUser();

            $params = $user->getParams();

            // site visitor
            $relation = 10;

            // site members
            if ($my->id != 0)
                $relation = 20;

            // friends
            if (CFriendsHelper::isConnected($my->id, $user->id))
                $relation = 30;

            // mine
            if (COwnerHelper::isMine($my->id, $user->id))
                $relation = 40;

            // @todo: respect privacy settings
            if ($relation >= $params->get('privacyFriendsView')) {
                $friends = $friendsModel->getFriends($user->id, 'latest', false, '', PROFILE_MAX_FRIEND_LIMIT + PROFILE_MAX_FRIEND_LIMIT);

                // randomize the friend count
                if ($friends)
                    shuffle($friends);

                $html = $tmpl->setRef('friends', $friends)
                        ->set('total', $user->getFriendCount())
                        ->setRef('user', $user)
                        ->fetch('profile.friends');
            }

            return $html;
        }

        public function modGetFriendsFeaturedHTML($userid = null) {
            $html = '';
            $tmpl = new CTemplate ( );

            $friendsModel = CFactory::getModel('friends');

            $my = CFactory::getUser($userid);
            $user = CFactory::getUser($userid);

            $params = $user->getParams();

            // site visitor
            $relation = 10;

            // site members
            if ($my->id != 0)
                $relation = 20;

            // friends
            if (CFriendsHelper::isConnected($my->id, $user->id))
                $relation = 30;

            // mine
            if (COwnerHelper::isMine($my->id, $user->id))
                $relation = 40;

            // @todo: respect privacy settings
            if ($relation >= $params->get('privacyFriendsView')) {
                $friends = $friendsModel->getFriends($user->id, 'latest', false, '', PROFILE_MAX_FRIEND_LIMIT + PROFILE_MAX_FRIEND_LIMIT);

                // randomize the friend count
                if ($friends)
                    shuffle($friends);


                if (count($friends) > 0) {
                    $html = '<div id="cPhotoItems" class="photo-list-item">
					<p><strong>' . JText::_('COM_COMMUNITY_FRIENDS') . ':</strong></p>';

                    for ($i = 0; ($i < 4) && ($i < count($friends)); $i++) {
                        $friend = $friends[$i];
                        $html .= '<div class="cPhotoItem">
						<a href="' . CRoute::_('index.php?option=com_community&view=profile&userid=' . $friend->id) . '"><img src="' . $friend->getThumbAvatar() . '" alt="' . $friend->getDisplayName() . '" /></a>
					</div>';
                    }
                    $html .= '</div>';
                }
            }

            return $html;
        }

        /**
         * @deprecated
         * @param type $userid
         * @return type
         *
         */
        private function _getFriendsHTML($userid = null) {
            return $this->modGetFriendsHTML($userid);
        }

        /**
         * Return groups html block
         * @since 2.4
         */
        public function modGetGroupsHTML($userid = null) {
            $html = '';
            $my = CFactory::getUser($userid);
            $user = CFactory::getRequestUser();

            $params = $user->getParams();

            // site visitor
            $relation = 10;

            // site members
            if ($my->id != 0)
                $relation = 20;

            // friends
            if (CFriendsHelper::isConnected($my->id, $user->id))
                $relation = 30;

            // mine
            if (COwnerHelper::isMine($my->id, $user->id))
                $relation = 40;

            // Respect privacy settings
            if ($relation < $params->get('privacyGroupsView')) {
                return '';
            }

            $tmpl = new CTemplate();
            $mainframe = JFactory::getApplication();
            $jinput = $mainframe->input;

            $model = CFactory::getModel('groups');
            $userid = $jinput->get('userid', $my->id, 'INT');
            $user = CFactory::getUser($userid);

            $groups = $model->getGroups($user->id);
            $total = count($groups);

            // Randomize groups
            if ($groups)
                shuffle($groups);

            //CFactory::load( 'helpers' , 'url' );
            // Load the groups as proper CTableGroup object
            foreach ($groups as &$gr) {
                $groupTable = JTable::getInstance('Group', 'CTable');
                $groupTable->load($gr->id);
                $gr = $groupTable;
            }

            for ($i = 0; $i < count($groups); $i++) {
                $row = $groups[$i];
                $row->avatar = $row->getThumbAvatar();

                $row->link = CUrl::build('groups', 'viewgroup', array('groupid' => $row->id), true);
            }

            $html = $tmpl->set('user', $user)
                    ->set('total', $total)
                    ->set('groups', $groups)
                    ->fetch('profile.groups');

            return $html;
        }

        /**
         * @deprecated
         * @return type
         */
        private function _getGroupsHTML($userid = null) {
            return $this->modGetGroupsHTML($userid);
        }

        /**
         * Return the 'about us' html block
         */
        private function _getProfileHTML(&$profile, $hideButton = false) {
            $tmpl         = new CTemplate();
            $mainframe    = JFactory::getApplication();
            $jinput       = $mainframe->input;
            $profileModel = CFactory::getModel('profile');
            $my           = CFactory::getUser();
            $config       = CFactory::getConfig();
            $userid       = $jinput->get('userid', $my->id, 'INT');
            $user         = CFactory::getUser($userid);
            $profileField = $profile['fields'];

            $isAdmin = COwnerHelper::isCommunityAdmin();
            // Allow search only on profile with type text and not empty
            foreach ($profileField as $key => $val) {

                foreach ($profileField[$key] as $pKey => $pVal) {
                    $field = $profileField[$key][$pKey];
                    //check for admin only fields
                    if (!$isAdmin && $field['visible'] == 2) {
                        unset($profileField[$key][$pKey]);
                    } else {
                        // Remove this info if we don't want empty field displayed
                        if (!$config->get('showemptyfield') && ( empty($field['value']) && $field['value'] != "0")) {
                            unset($profileField[$key][$pKey]);
                        } else {
                            if ((!empty($field['value']) || $field['value'] == "0" ) && $field['searchable']) {

                                switch ($field['type']) {
                                    case 'birthdate':
                                        $params = new CParameter($field['params']);
                                        $format = $params->get('display');
                                        if ($format == 'age') {
                                            $profileField[$key][$pKey]['name'] = JText::_('COM_COMMUNITY_AGE');
                                        }

                                        break;
                                    case 'text':
                                        if (CValidateHelper::email($field['value'])) {
                                            $profileField[$key][$pKey]['value'] = CLinkGeneratorHelper::getEmailURL($field['value']);
                                        } else if (CValidateHelper::url($field['value'])) {
                                            $profileField[$key][$pKey]['value'] = CLinkGeneratorHelper::getHyperLink($field['value']);
                                        } else if (!CValidateHelper::phone($field['value']) && !empty($field['fieldcode'])) {
                                            $profileField[$key][$pKey]['searchLink'] = CRoute::_('index.php?option=com_community&view=search&task=field&' . $field['fieldcode'] . '=' . urlencode($field['value']));
                                        }
                                        break;
                                    case 'select':
                                    case 'singleselect':
                                        $profileField[$key][$pKey]['searchLink'] = CRoute::_('index.php?option=com_community&view=search&task=field&' . $field['fieldcode'] . '={{field_value}}&type=' . $field['type']);
                                        $profileField[$key][$pKey]['searchLink'] = str_replace('{{field_value}}', urlencode($field['value']), $profileField[$key][$pKey]['searchLink']);
                                        $profileField[$key][$pKey]['value'] = JText::_($field['value']);
                                        break;
                                    case 'radio':
                                    case 'checkbox':
                                        $profileField[$key][$pKey]['searchLink'] = array();
                                        $checkboxArray = explode(',', $field['value']);
                                        foreach ($checkboxArray as $item) {
                                            if (!empty($item))
                                                $profileField[$key][$pKey]['searchLink'][$item] = CRoute::_('index.php?option=com_community&view=search&task=field&' . $field['fieldcode'] . '=' . urlencode($item) . '&type=' . $field['type']);
                                        }
                                        break;
                                    case 'country':
                                        $lang = JFactory::getLanguage();
                                        $lang->load('com_community.country');
                                        //Commented the following line to use advance search #808
                                        //$profileField[$key][$pKey]['searchLink'] = CRoute::_('index.php?option=com_community&view=search&task=advancesearch&condition0=equal&field0=' . $field['fieldcode'] . '&value0=' . urlencode($field['value']));
                                        $profileField[$key][$pKey]['searchLink'] = CRoute::_('index.php?field0=FIELD_COUNTRY&condition0=equal&value0='.urlencode($field['value']).'&fieldType0='.$field['fieldcode'].'&operator=and&option=com_community&view=search&task=advancesearch&key-list=0');
                                        $profileField[$key][$pKey]['value'] = JText::_($field['value']);
                                        break;
                                     case 'gender':
                                        $profileField[$key][$pKey]['searchLink'] = CRoute::_('index.php?option=com_community&view=search&task=field&' . $field['fieldcode'] . '=' . urlencode($field['value']));
                                        break;
                                    default:
                                        break;
                                }
                            }
                        }
                    }
                }
            }

            $profile['fields'] = $profileField;
            $html = $tmpl->set('profile', $profile)
                    ->set('isMine', COwnerHelper::isMine($my->id, $user->id))
                    ->set('hideButton', $hideButton)
                    ->fetch('profile.about');

            return $html;
        }

        /**
         * Return newsfeed html block
         */
        private function _getNewsfeedHTML() {
            $my = CFactory::getUser();
            $mainframe = JFactory::getApplication();
            $jinput = $mainframe->input;

            $userId = $jinput->get('userid', $my->id, 'INT');

            return CActivities::getActivitiesByFilter('active-profile', $userId, 'profile', true, array('show_featured'=>true));
        }

        private function _getCurrentProfileVideo() {
            $my = CFactory::getUser();
            $params = $my->getParams();
            $videoid = $params->get('profileVideo', 0);

            // Return if 0(No profile video)
            if ($videoid == 0)
                return;

            $video = JTable::getInstance('Video', 'CTable');

            // If the video does not exists, set the profile video to 0(No profile video)
            if (!$video->load($videoid)) {
                $params->set('profileVideo', 0);
                $my->save('params');
                return;
            }

            return $video;
        }

        public function showSubmenu($display=true) {
            $this->_addSubmenu();
            return parent::showSubmenu($display);
        }

        private function _getAdminControlHTML($userid) {
            $adminControlHTML = '';

            if (COwnerHelper::isCommunityAdmin()) {
                $user = CFactory::getUser($userid);
                $params = $user->getParams();
                $videoid = $params->get('profileVideo', 0);

                $tmpl = new CTemplate();

                $isDefaultPhoto = ( $user->getThumbAvatar() == JURI::root(true) . '/components/com_community/assets/default_thumb.jpg' ) ? true : false;

                //CFactory::load( 'libraries' , 'featured' );
                $featured = new CFeatured(FEATURED_USERS);
                $isFeatured = $featured->isFeatured($user->id);
                $jConfig = JFactory::getConfig();
                $config = CFactory::getConfig();
                $showFeatured = $config->get('show_featured');

                $adminControlHTML = $tmpl->set('userid', $userid)
                        ->set('videoid', $videoid)
                        ->set('isCommunityAdmin', COwnerHelper::isCommunityAdmin())
                        ->set('blocked', $user->isBlocked())
                        ->set('showFeatured', $showFeatured)
                        ->set('isFeatured', $isFeatured)
                        ->set('isDefaultPhoto', $isDefaultPhoto)
                        ->set('jConfig', $jConfig)
                        ->fetch('admin.controls');
            }

            return $adminControlHTML;
        }

        /**
         * Show the main profile header
         */
        private function _showHeader(& $data) {
            jimport('joomla.utilities.arrayhelper');

            $mainframe = JFactory::getApplication();
            $jinput = $mainframe->input;

            $my = CFactory::getUser();
            $userid = $jinput->get('userid', $my->id, 'INT');
            $user = CFactory::getUser($userid);
            $params = $user->getParams();
            $userModel = CFactory::getModel('user');

            //CFactory::load ( 'libraries', 'messaging' );

            $isMine = COwnerHelper::isMine($my->id, $user->id);

            // Get the admin controls HTML data
            $adminControlHTML = '';

            $tmpl = new CTemplate ();

            // get how many unread message
            $filter = array();
            $inboxModel = CFactory::getModel('inbox');
            $filter['user_id'] = $my->id;
            $unread = $inboxModel->countUnRead($filter);

            // get how many pending connection
            $friendModel = CFactory::getModel('friends');
            $pending = $friendModel->countPending($my->id);

            $profile = Joomla\Utilities\ArrayHelper::toObject($data->profile);
            $profile->largeAvatar = $user->getAvatar();
            $profile->status = $user->getStatus();

            if ($profile->status !== '') {
                $postedOn = new JDate($user->_posted_on);
                $postedOn = CActivityStream::_createdLapse($postedOn);
                $profile->posted_on = $user->_posted_on == '0000-00-00 00:00:00' ? '' : $postedOn;
            } else {
                $profile->posted_on = '';
            }

            // Assign videoId
            $profile->profilevideo = $data->videoid;
            $video = JTable::getInstance('Video', 'CTable');
            $video->load($profile->profilevideo);
            $profile->profilevideoTitle = $video->getTitle();

            $addbuddy = "joms.api.friendAdd('{$profile->id}')";
            $sendMsg = CMessaging::getPopup($profile->id);

            $config = CFactory::getConfig();

            $lastLogin = JText::_('COM_COMMUNITY_PROFILE_NEVER_LOGGED_IN');
            if ($user->lastvisitDate != '0000-00-00 00:00:00') {
                //$now = JDate::getInstance();
                $userLastLogin = new JDate($user->lastvisitDate);
                //CFactory::load( 'libraries' , 'activities');
                $lastLogin = CActivityStream::_createdLapse($userLastLogin);
            }

            // @todo : beside checking the owner, maybe we want to check for a cookie,
            // say every few hours only the hit get increment by 1.
            if (!$isMine) {
                $user->viewHit();
            }

            // @rule: myblog integrations
            $showBlogLink = false;

            //CFactory::load( 'libraries' , 'myblog' );
            $myblog = CMyBlog::getInstance();
            if ($config->get('enablemyblogicon') && $myblog) {
                if ($myblog->userCanPost($user->id)) {
                    $showBlogLink = true;
                }
                $tmpl->set('blogItemId', $myblog->getItemId());
            }

            $multiprofile = JTable::getInstance('MultiProfile', 'CTable');
            $multiprofile->load($user->getProfileType());

            // Get like
            $likesHTML = '';
            if ($user->getParams()->get('profileLikes', true)) {
                //CFactory::load( 'libraries' , 'like' );
                $likes = new CLike();
                $likesHTML = ($my->id == 0) ? $likes->getHtmlPublic('profile', $user->id) : $likes->getHTML('profile', $user->id, $my->id);
            }

            $status = new CUserStatus($user->id, 'profile');

            //respect wall setting
            if ($my->id && ((!$config->get('lockprofilewalls')) || ( $config->get('lockprofilewalls') && CFriendsHelper::isConnected($my->id, $profile->id) ) ) || COwnerHelper::isCommunityAdmin()) {

                // Add default status box
                CUserHelper::addDefaultStatusCreator($status);
            }

            $isblocked = $user->isBlocked();

            return $tmpl->set('karmaImgUrl', CUserPoints::getPointsImage($user))
                            ->set('isMine', $isMine)
                            ->set('lastLogin', $lastLogin)
                            ->set('user', $user)
                            ->set('addBuddy', $addbuddy)
                            ->set('sendMsg', $sendMsg)
                            ->set('config', $config)
                            ->set('multiprofile', $multiprofile)
                            ->set('showBlogLink', $showBlogLink)
                            ->set('isFriend', CFriendsHelper::isConnected($user->id, $my->id) && $user->id != $my->id)
                            ->set('isWaitingApproval', CFriendsHelper::isWaitingApproval($my->id, $user->id))
                            ->set('isBlocked', $isblocked)
                            ->set('profile', $profile)
                            ->set('unread', $unread)
                            ->set('pending', $pending)
                            ->set('registerDate', $user->registerDate)
                            ->set('adminControlHTML', $adminControlHTML)
                            ->set('likesHTML', $likesHTML)
                            ->set('userstatus', $status)
                            ->fetch('profile.header');
        }

        public function singleActivity($activity){
            // we will determine all the user settings based on the activity viewed
            $actor = CFactory::getUser($activity->actor);
            $userId = $activity->actor;

            if($activity->id == 0 || empty($activity->id)){
                //redirect this to error : no activity found
                JFactory::getApplication()->enqueueMessage(JText::_('COM_COMMUNITY_ERROR_ACTIVITY_NOT_FOUND'), 'error');
            }

            $document = JFactory::getDocument();
            $document->setTitle(JHTML::_('string.truncate', $activity->title, 75));

            CHeadHelper::setDescription(JHTML::_('string.truncate', $activity->title, 300, true));

            //lets find the image if there is any
            $params = new CParameter($activity->params);
            $headMetaParams = new JRegistry($params->get('headMetas'));
            if($headMetaParams->get('image')){
                CHeadHelper::addOpengraph('og:image',$headMetaParams->get('image') );
            }else{
                if(($photo = $actor->getAvatarInfo()) && !$actor->isDefaultAvatar()){
                    CHeadHelper::addOpengraph('og:image', $photo->getImageURI(true));
                }else{
                    CHeadHelper::addOpengraph('og:image', JURI::base().$actor->getAvatar());
                }
            }

            if($headMetaParams->get('title')){
                CHeadHelper::addOpengraph('og:title',$headMetaParams->get('title') );
            }

            //see if the user has blocked each other
            $getBlockStatus = new blockUser();
            $blocked = $getBlockStatus->isUserBlocked($userId, 'profile');
            if ($blocked && !COwnerHelper::isCommunityAdmin()) {
                JFactory::getApplication()->enqueueMessage(JText::_('COM_COMMUNITY_ERROR_ACTIVITY_NOT_FOUND'), 'error');
            }

            //everything is fine, lets get to the activity
            echo $this->_getNewsfeedHTML();

        }

        /**
         * Displays the viewing profile page.
         *
         * @access	public
         * @param	array  An associative array to display the fields
         */
        public function profile($data) {
            $mainframe = JFactory::getApplication();
            $jinput    = $mainframe->input;
            $my        = CFactory::getUser();
            $config    = CFactory::getConfig();
            $userid    = $userId = $jinput->get('userid', $my->id, 'INT');
            $user      = CFactory::getUser($userid);

            /**
             * Opengraph
             */
            CHeadHelper::setType('profile', JText::sprintf('COM_COMMUNITY_USER_PROFILE_PAGE_TITLE', $user->getDisplayName()), $user->_status, array($user->getAvatar(), $user->getCover()));

            if ($my->id != 0 && empty($userId)) {
                CFactory::setActiveProfile($my->id);
                $user = $my;
            }

            // Display breadcrumb regardless whether the user is blocked or not
            $pathway = $mainframe->getPathway();

            $pathway->setPathway(array());
            $menu = JFactory::getApplication()->getMenu()->getActive();
            if(isset($menu->title)){
                $pathway->addItem(JText::_($menu->title), CRoute::getExternalURL($menu->link));
            }
            $pathway->addItem($user->getDisplayName(), '');

            $getBlockStatus = new blockUser();
            $blocked = $getBlockStatus->isUserBlocked($userId, 'profile');
            if ($blocked) {
                if(COwnerHelper::isCommunityAdmin()) {
                    #$this->addWarning(JText::_('COM_COMMUNITY_YOU_ARE_BLOCKED_BY_USER'));
                } else {
                    $tmpl	 = new CTemplate();
                    echo $tmpl->fetch( 'block.denied' );
                    return;
                }
            }

            // If the current browser is a site admin, display some notice that user is blocked.
            #if ($blocked) {
            #    $this->addWarning(JText::_('COM_COMMUNITY_USER_ACCOUNT_BANNED'));
            #}

            // access check
            //if(!$this->accessAllowed('privacyProfileView'))
            if (!$my->authorise('community.view', 'profile.' . $my->id, $user)) {
                // @todo: display the no access box like the old time
                $this->showLimitedProfile($user->id);
                return;
            }
            // Load user application
            $apps = $data->apps;

            // Load community applications plugin
            $app = CAppPlugins::getInstance();
            $appsModel = CFactory::getModel('apps');
            $tmpAppData = $app->triggerEvent('onProfileDisplay', '', true);

            $appData = array();

            // @rule: Only display necessary apps.
            $count = count($tmpAppData);

            for ($i = 0; $i < $count; $i++) {
                $app = $tmpAppData[$i];

                $privacy = $appsModel->getPrivacy($user->id, $app->name);

                if ($this->appPrivacyAllowed($privacy)) {
                    $appData[] = $app;
                }
            }
            unset($tmpAppData);

            // Split the apps into different list for different positon
            $appsInPositions = array();

            // we fix the stream and about me details into content
            //Stream
            $info = new stdClass();
            $info->title = ucfirst(JText::_('COM_COMMUNITY_SINGULAR_STREAM'));
            $info->name = ucfirst(JText::_('COM_COMMUNITY_SINGULAR_STREAM'));
            $info->data = $this->_getNewsfeedHTML();
            $info->position = 'content';
            $info->id = 'feeds-special';
            $info->core = true;
            $info->hasConfig = '';
            $appsInPositions['content'][] = $info;

            //about me
            $lastLogin = JText::_('COM_COMMUNITY_PROFILE_NEVER_LOGGED_IN');
            if ($user->lastvisitDate != '0000-00-00 00:00:00') {
                $userLastLogin = new JDate($user->lastvisitDate);
                $lastLogin = CActivityStream::_createdLapse($userLastLogin);
            }

            $multiprofile = JTable::getInstance('MultiProfile', 'CTable');
            $multiprofile->load($user->getProfileType());

            $info = new stdClass();
            $tmpl = new CTemplate();
            $info->title = ucfirst(JText::_('COM_COMMUNITY_ABOUT_ME'));
            $info->name = ucfirst(JText::_('COM_COMMUNITY_ABOUT_ME'));
            $info->data = $tmpl->set('registerDate',$user->registerDate)
                ->set('lastLogin',$lastLogin)
                ->set('about',$this->_getProfileHTML($data->profile))
                ->set('multiprofile', $multiprofile)
                ->fetch('profile/aboutme');
            $info->position = 'content';
            $info->id = 'aboutme-special';
            $info->core = true;
            $info->hasConfig = '';
            $appsInPositions['content'][] = $info;

            foreach ($appData as &$app) {
                // @rule: Try to get proper app id from #__community_users table first.
                $app_id = $appsModel->getUserApplicationId($app->name, $my->id);

                // @rule: If there aren't any records, we need to get it from #__plugins table.
                if (empty($id)) {
                    $app_id = $appsModel->getPluginId($app->name, null, true);
                }

                $params = new CParameter($appsModel->getPluginParams($app_id, null));

                // If title override has been set, pass it through JText::_ and use it instead
                if(strlen($params->get('title_override',''))) {
                    $app->title = JText::_($params->get('title_override'));
                }
                $isCoreApp = $params->get('coreapp');

                if (!in_array($app->position, array('content', 'sidebar-top', 'sidebar-bottom', 'sidebar-top-stacked', 'sidebar-bottom-stacked'))) {
                    $app->position = 'content';
                }

                $corePosition = $params->get('position');
                $app->position = $isCoreApp ? $corePosition : $app->position;

                $appsInPositions[$app->position][] = $app;
            }

            $tmpl = new CTemplate();
            $contenHTML = array();
            $contenHTML['content'] = '';

            $contenHTML['sidebar-top'] = '';
            $contenHTML['sidebar-bottom'] = '';
            $contenHTML['sidebar-top-stacked'] = '';
            $contenHTML['sidebar-bottom-stacked'] = '';
            $jscript = '';

            foreach ($appsInPositions as $position => $appData) {
                ob_start();

                //this will handle stacked position
                if($position == 'sidebar-top-stacked' || $position == 'sidebar-bottom-stacked'){
                    echo $tmpl->set('apps', $appData)->fetch('application/stack');
                    $contenHTML[$position] = ob_get_contents();
                    ob_end_clean();

                    continue;
                }

                //outer div
                echo '<div>';
                echo $tmpl->set('apps', $appData)->fetch('application/tabs');

                $first = 0;

                $special_first = false;
                foreach ($appData as $app) {
                    // If the apps content is empty, we ignore this app from showing
                    // the header in profile page.
                    if (JString::trim($app->data) == "") {
                        continue;
                    }

                    //special case for profile settings from the backend
                    $firstCase = true;
                    if($app->id == 'feeds-special'){
                        $config = CFactory::getConfig();
                        $special_first = true;
                        $firstCase = ($config->get('default_profile_tab') == 1) ? true : false;
                    }elseif($app->id == 'aboutme-special'){
                        $special_first = true;
                        $config = CFactory::getConfig();
                        $firstCase = ($config->get('default_profile_tab') == 0) ? true : false;
                    }

                    $tmpl->set('app', $app)
                        ->set('first', $first++)
                        ->set('isOwner', COwnerHelper::isMine($my->id, $user->id));

                    if($special_first){
                        $tmpl->set('first', $firstCase);
                    }

                    switch ($position) {
                        case 'sidebar-top':
                        case 'sidebar-bottom':
                            echo $tmpl->fetch('application/widget');
                            break;
                        default:
                            $userStatus = new CUserStatus($user->id, 'profile');
                            //respect wall setting
                            if ($user->id && ((!$config->get('lockprofilewalls')) || ( $config->get('lockprofilewalls') && CFriendsHelper::isConnected($user->id, $my->id) ) ) || COwnerHelper::isCommunityAdmin()) {

                                // Add default status box
                                CUserHelper::addDefaultStatusCreator($userStatus);
                            }
                            echo $tmpl->set('postBoxHTML',$userStatus->render(1))->fetch('application/box');
                    }
                }

                echo '</div>';

                $contenHTML[$position] = ob_get_contents();
                ob_end_clean();
            }

            $isMine = COwnerHelper::isMine($my->id, $user->id);

            $tmpl = new CTemplate( );
            echo $tmpl->set('newsfeed', $this->_getNewsfeedHTML())
                    ->set('content', $contenHTML['content'])
                    ->set('sidebarTop', $contenHTML['sidebar-top'])
                    ->set('sidebarTopStacked', $contenHTML['sidebar-top-stacked'])
                    ->set('sidebarBottom', $contenHTML['sidebar-bottom'])
                    ->set('sidebarBottomStacked', $contenHTML['sidebar-bottom-stacked'])
                    ->set('isMine', $isMine)
                    ->fetch('profile/base');
        }

        public function editPage() {
            if (!$this->accessAllowed('registered')) {
                return;
            }

            $my = CFactory::getUser();
            $appsModel = CFactory::getModel('apps');

            //------ pre-1.8 ------//
            // Get coreapps
            $coreApps = $appsModel->getCoreApps();
            for ($i = 0; $i < count($coreApps); $i++) {
                $appInfo = $appsModel->getAppInfo($coreApps[$i]->apps);

                // @rule: Try to get proper app id from #__community_users table first.
                $id = $appsModel->getUserApplicationId($coreApps[$i]->apps, $my->id);

                // @rule: If there aren't any records, we need to get it from #__plugins table.
                if (empty($id)) {
                    $id = $appsModel->getPluginId($coreApps[$i]->apps, null, true);
                }

                $coreApps[$i]->id = $id;
                $coreApps[$i]->title = $appInfo->title;
                $coreApps[$i]->description = $appInfo->description;
                $coreApps[$i]->name = $coreApps[$i]->apps;
                //$coreApps[$i]->coreapp		= $params->get( 'coreapp' );
                //Get application favicon
                if (JFile::exists(CPluginHelper::getPluginPath('community', $coreApps[$i]->apps) . '/' . $coreApps[$i]->apps . '/favicon_64.png')) {
                    $coreApps[$i]->appFavicon = JURI::root(true) . CPluginHelper::getPluginURI('community', $coreApps[$i]->apps) . '/' . $coreApps[$i]->apps . '/favicon_64.png';
                } else {
                    $coreApps[$i]->appFavicon = JURI::root(true) . '/components/com_community/assets/app_favicon.png';
                }
            }
            //------ pre-1.8 ------//
            // Get user apps
            $userApps = $appsModel->getUserApps($my->id);
            $appsList = array();

            for ($i = 0; $i < count($userApps); $i++) {
                // TODO: getUserApps should return all this value already
                $id = $appsModel->getPluginId($userApps[$i]->apps, null, true);
                $appInfo = $appsModel->getAppInfo($userApps[$i]->apps);
                $params = new CParameter($appsModel->getPluginParams($id, null));
                $isCoreApp = $params->get('coreapp');
                $corePosition = $params->get('position');

                $userApps[$i]->title = isset($appInfo->title) ? $appInfo->title : '';
                $userApps[$i]->description = isset($appInfo->description) ? $appInfo->description : '';
                $userApps[$i]->coreapp = $isCoreApp; // Pre 1.8x
                $userApps[$i]->isCoreApp = $isCoreApp;
                $userApps[$i]->name = $userApps[$i]->apps;
                $userApps[$i]->hide_empty = isset($appInfo->hide_empty) ? $appInfo->hide_empty : 0 ;

                //------ pre-1.8 ------//

                if($params->get('favicon') != ''){
                    $userApps[$i]->favicon['64'] = JURI::root(true) . '/' . $params->get('favicon');
                }elseif (JFile::exists(CPluginHelper::getPluginPath('community', $userApps[$i]->apps) . '/favicon_64.png')) {
                    $userApps[$i]->favicon['64'] = JURI::root(true) . CPluginHelper::getPluginURI('community', $userApps[$i]->apps) . '/' . $userApps[$i]->apps . '/favicon_64.png';
                } else {
                    $userApps[$i]->favicon['64'] = JURI::root(true) . '/components/com_community/assets/app_avatar.png';
                }

                if ($isCoreApp) {
                    $position = $corePosition . ( strpos($corePosition, '-core') === FALSE ? '-core' : '' );
                } else {
                    $position = !empty($userApps[$i]->position) ? $userApps[$i]->position : 'content';
                }

                $appsList[$position][] = $userApps[$i];
            }

            foreach (array('sidebar-top', 'sidebar-bottom', 'sidebar-top-stacked', 'sidebar-bottom-stacked', 'content') as $position) {
                if (isset($appsList[$position . '-core'])) {
                    if (!isset($appsList[$position])) {
                        $appsList[$position] = array();
                    }
                    $appsList[$position] = array_merge($appsList[$position . '-core'], $appsList[$position]);
                    unset($appsList[$position . '-core']);
                }
            }

            $appTitles = array();
            $appTitles['sidebar-top'] = '';
            $appTitles['sidebar-top-stacked'] = '';
            $appTitles['sidebar-bottom'] = '';
            $appTitles['sidebar-bottom-stacked'] = '';
            $appTitles['content'] = '';

            $appItems = array();
            $appItems['sidebar-top'] = '';
            $appItems['sidebar-top-stacked'] = '';
            $appItems['sidebar-bottom'] = '';
            $appItems['sidebar-bottom-stacked'] = '';
            $appItems['content'] = '';

            foreach ($appsList as $position => $apps) {
                $tmpl = new CTemplate();
                if (isset($appItems[$position])) {
                    $appTitles[$position] .= $tmpl->set('apps', $apps)->set('position', $position)->fetch('application.title');
                    $appItems[$position] .= $tmpl->set('apps', $apps)->set('position', $position)->set('itemType', 'edit')->fetch('application.item');
                }
            }

            // Get available apps for comparison
            $appsModel = CFactory::getModel('apps');
            $apps = $appsModel->getAvailableApps(false);
            $appsname = array();
            $availableApps = array();
            if (!empty($apps)) {
                foreach ($apps as $data) {
                    array_push($availableApps, $data->name);
                }
            }

            // Check if apps exist, if not delete it.
            $obsoleteApps = array();
            $obsoleteApps = array_diff($appsname, $availableApps);
            if (!empty($obsoleteApps)) {
                foreach ($obsoleteApps as $key => $obsoleteApp) {
                    $appRecords = $appsModel->checkObsoleteApp($obsoleteApp);

                    if (empty($appRecords)) {
                        if ($appRecords == NULL) {
                            $appsModel->removeObsoleteApp($obsoleteApp);
                        }

                        unset($userApps[$key]);
                    }
                }
                $userApps = array_values($userApps);
            }

            /**
             * Opengraph
             */
            CHeadHelper::setType('website', JText::_('COM_COMMUNITY_APPS_MINE'));

            $this->addPathway(JText::_('COM_COMMUNITY_APPS_MINE'));
            //$this->showSubMenu(); // pre-1.8
            //CFactory::load( 'libraries' , 'window' );
            CWindow::load();
            // CFactory::attach('assets/jquery.tablednd_0_5.js', 'js'); // pre-1.8
            // CFactory::attach('assets/ui.core.js', 'js');
            // CFactory::attach('assets/ui.sortable.js', 'js');
            // CFactory::attach('assets/applayout.js', 'js');

            // about me
            $tmpl = new CTemplate();

            $lastLogin = JText::_('COM_COMMUNITY_PROFILE_NEVER_LOGGED_IN');
            if ($my->lastvisitDate != '0000-00-00 00:00:00') {
                $myLastLogin = new JDate($my->lastvisitDate);
                $lastLogin = CActivityStream::_createdLapse($myLastLogin);
            }

            $profileModel = CFactory::getModel('profile');
            $profileData = $profileModel->getViewableProfile($my->id, $my->getProfileType());

            $multiprofile = JTable::getInstance('MultiProfile', 'CTable');
            $multiprofile->load($my->getProfileType());

            $aboutTitle = ucfirst(JText::_('COM_COMMUNITY_ABOUT_ME'));
            $aboutItem = $tmpl
                ->set('registerDate', $my->registerDate)
                ->set('lastLogin', $lastLogin)
                ->set('about', $this->_getProfileHTML($profileData, true))
                ->set('multiprofile', $multiprofile)
                ->fetch('profile/aboutme');

            $tmpl = new CTemplate();
            echo $tmpl->set('coreApplications', $coreApps) // pre-1.8
                    ->set('applications', $userApps) // pre-1.8
                    ->set('appItems', $appItems)
                    ->set('appTitles', $appTitles)
                    ->set('aboutTitle', $aboutTitle)
                    ->set('aboutItem', $aboutItem)
                    ->set('submenu', $this->showSubmenu(false))
                    ->fetch('applications.edit');
        }

        public function editLayout() {
            $tmpl = new CTemplate( );

            $content = '<div class="app-box-sortable"></div><div  class="app-box-sortable"><div>';

            echo $tmpl->set('content', $content)
                    ->fetch('profile.editlayout');
        }

        /**
         * Edits a user profile
         *
         * @access	public
         * @param	array  An associative array to display the editing of the fields
         */
        public function edit($data) {

            $mainframe = JFactory::getApplication();
            $jinput = $mainframe->input;
            $isAdminEdit = false; //indicates that admin is editing someone else account

            // access check
            CFactory::setActiveProfile();
            if (!$this->accessAllowed('registered'))
                return;

            $my = CFactory::getUser();

            $userid = $jinput->get('userid', $my->id);
            if($userid != $my->id){
                //this is where admin edit the user profile

                if(COwnerHelper::isCommunityAdmin()){
                    $my = CFactory::getUser($userid);
                    $isAdminEdit = true;
                }else{
                    // looks like someone is trying to edit someone elses acocunt
                    return false;
                }
            }

            $config = CFactory::getConfig();
            $userParams = $my->getParams();

            $pathway = $mainframe->getPathway();
            $menu = JFactory::getApplication()->getMenu()->getActive();
            if(isset($menu->title)){
                $pathway->addItem(JText::_($menu->title), CRoute::getExternalURL($menu->link));
            }

            $pathway->addItem(JText::_($my->getDisplayName()), CRoute::_('index.php?option=com_community&view=profile&userid=' . $my->id));
            $pathway->addItem(JText::_('COM_COMMUNITY_PROFILE_EDIT'), '');

            /**
             * Opengraph
             */
            CHeadHelper::setType('website', JText::_('COM_COMMUNITY_PROFILE_EDIT'));

            $miniheader = '';
            if($isAdminEdit){
                //show the miniheader of the user
                $miniheader = CMiniHeader::showMiniHeader($my->id);
                $title = JText::sprintf('COM_COMMUNITY_PROFILE_USER_EDIT', $my->getDisplayName());

                JFactory::getDocument()->setTitle($title);
            }else{
                $title = JText::_('COM_COMMUNITY_PROFILE_EDIT');
                $this->showSubmenu();
            }

            $jConfig = JFactory::getConfig();
            $app = CAppPlugins::getInstance();

            $appFields = $app->triggerEvent('onFormDisplay', array('jsform-profile-edit'));
            $beforeFormDisplay = CFormElement::renderElements($appFields, 'before');
            $afterFormDisplay = CFormElement::renderElements($appFields, 'after');

            $multiprofile = JTable::getInstance('MultiProfile', 'CTable');
            $multiprofile->load($my->getProfileType());

            $model = CFactory::getModel('Profile');
            $profileTypes = $model->getProfileTypes();

            // @rule: decide to show multiprofile or not.
            $showProfileType = ( $config->get('profile_multiprofile') && $profileTypes && count($profileTypes) >= 1 && !$multiprofile->profile_lock);

            $isAdmin = COwnerHelper::isCommunityAdmin();
            $profileField = $data->profile ['fields'];

            if (!is_null($profileField)) {
                foreach ($profileField as $key => $val) {
                    foreach ($val as $pkey => $field) {

                        if (!empty($field['options'])) {
                            $i = 0;
                            foreach ($field['options'] as $option) {
                                $profileField[$key][$pkey]['options'][$i] = htmlspecialchars($option);
                                $i++;
                            }
                        }

                        if (!$isAdmin && $field['visible'] == 2) {
                            unset($profileField[$key][$pkey]);
                        }


                    }
                }
            }

            $fbHtml = '';
            $connectModel = CFactory::getModel('Connect');
            $associated = $connectModel->isAssociated($my->id);

            if ($config->get('fbconnectkey') && $config->get('fbconnectsecret') && !$config->get('usejfbc')) {

                $facebook = new CFacebook();
                $fbHtml = $facebook->getLoginHTML();
            }

            if ($config->get('usejfbc')) {
                if (class_exists('JFBCFactory')) {
                   $providers = JFBCFactory::getAllProviders();

                   foreach($providers as $p){
                        $fbHtml .= $p->loginButton();
                   }
                }
            }

            $isUseFirstLastName = CUserHelper::isUseFirstLastName();

            $data->profile ['fields'] = $profileField;
            $tmpl = new CTemplate();

            if(CSystemHelper::tfaEnabled()){
                $tfaForm = $this->getTwofactorform();
                $userModel = new UsersModelUser();
                $otpConfig = $userModel->getOtpConfig(CFactory::getUser()->id);
                $tmpl->set('tfaForm', $tfaForm)
                     ->set('otpConfig', $otpConfig);
            }

            echo $tmpl->set('showProfileType', $showProfileType)
                    ->set('multiprofile', $multiprofile)
                    ->set('beforeFormDisplay', $beforeFormDisplay)
                    ->set('afterFormDisplay', $afterFormDisplay)
                    ->set('fields', $data->profile ['fields'])
                    ->set('user', $my)
                    ->set('fbHtml', $fbHtml)
                    ->set('miniheader', $miniheader)
                    ->set('canEditUsername', JComponentHelper::getParams('com_users')->get('change_login_name'))
                    ->set('fbPostStatus', $userParams->get('postFacebookStatus'))
                    ->set('jConfig', $jConfig)
                    ->set('params', $data->params)
                    ->set('config', $config)
                    ->set('associated', $associated)
                    ->set('isAdmin', COwnerHelper::isCommunityAdmin())
                    ->set('offsetList', $data->offsetList)
                    ->set('isUseFirstLastName', $isUseFirstLastName)
                    ->set('submenu', $this->showSubmenu(false))
                    ->set('title', $title)
                    ->fetch('profile/edit');
        }

        public function getTwofactorform($user_id = null)
        {

            if(!class_exists('UsersModelUser')){
                require(JPATH_ROOT.'/administrator/components/com_users/models/user.php');
            }

            $user_id = CFactory::getUser()->id;

            $userModel = new UsersModelUser();
            $otpConfig = $userModel->getOtpConfig($user_id);

            FOFPlatform::getInstance()->importPlugin('twofactorauth');

            return FOFPlatform::getInstance()->runPlugins('onUserTwofactorShowConfiguration', array($otpConfig, $user_id));
        }

        /**
         * Edits a user details
         *
         * @access	public
         * @param	array  An associative array to display the editing of the fields
         */
        public function editDetails(& $data) {
            $mainframe = JFactory::getApplication();

            // access check
            CFactory::setActiveProfile();
            if (!$this->accessAllowed('registered'))
                return;

            $my = CFactory::getUser();
            $config = CFactory::getConfig();
            $userParams = $my->getParams();

            $pathway = $mainframe->getPathway();
            $menu = JFactory::getApplication()->getMenu()->getActive();
            if(isset($menu->title)) {
                $pathway->addItem(JText::_($menu->title), CRoute::getExternalURL($menu->link));
            }
            $pathway->addItem(JText::_($my->getDisplayName()), CRoute::_('index.php?option=com_community&view=profile&userid=' . $my->id));
            $pathway->addItem(JText::_('COM_COMMUNITY_EDIT_DETAILS'), '');

            /**
             * Opengraph
             */
            CHeadHelper::setType('website', JText::_('COM_COMMUNITY_EDIT_DETAILS'));

            // $js = 'assets/validate-1.5.min.js';
            // CFactory::attach($js, 'js');

            $this->showSubmenu();

            $connectModel = CFactory::getModel('Connect');
            $associated = $connectModel->isAssociated($my->id);

            $fbHtml = '';

            if ($config->get('fbconnectkey') && $config->get('fbconnectsecret') && !$config->get('usejfbc')) {
                //CFactory::load( 'libraries' , 'facebook' );
                $facebook = new CFacebook();
                $fbHtml = $facebook->getLoginHTML();
            }

            if ($config->get('usejfbc')) {
                if (class_exists('JFBCFactory')) {
                   $providers = JFBCFactory::getAllProviders();

                   foreach($providers as $p){
                        $fbHtml .= $p->loginButton();
                   }
                }
            }
            // If FIELD_GIVENNAME & FIELD_FAMILYNAME is in use

            $isUseFirstLastName = CUserHelper::isUseFirstLastName();

            $jConfig = JFactory::getConfig();
            //CFactory::load( 'libraries' , 'apps' );
            $app = CAppPlugins::getInstance();
            $appFields = $app->triggerEvent('onFormDisplay', array('jsform-profile-editdetails'));

            $beforeFormDisplay = CFormElement::renderElements($appFields, 'before');
            $afterFormDisplay = CFormElement::renderElements($appFields, 'after');

            $tmpl = new CTemplate();
            echo $tmpl->set('beforeFormDisplay', $beforeFormDisplay)
                    ->set('afterFormDisplay', $afterFormDisplay)
                    ->set('fbHtml', $fbHtml)
                    ->set('fbPostStatus', $userParams->get('postFacebookStatus'))
                    ->set('jConfig', $jConfig)
                    ->set('params', $data->params)
                    ->set('user', $my)
                    ->set('config', $config)
                    ->set('associated', $associated)
                    ->set('isAdmin', COwnerHelper::isCommunityAdmin())
                    ->set('offsetList', $data->offsetList)
                    ->set('isUseFirstLastName', $isUseFirstLastName)
                    ->fetch('profile.edit.details');
        }

        public function connect() {

            /**
             * Opengraph
             */
            CHeadHelper::setType('website', JText::_('COM_COMMUNITY_PROFILE_CONNECT_REQUEST'));
            ?>
            <form name="jsform-profile-connect" method="post" action="">
                <input type="submit" name="yes" class="button" id="button_yes" value="<?php echo JText::_('COM_COMMUNITY_YES_BUTTON'); ?>" />
                <input type="submit" name="no" class="button" id="button_no" value="<?php echo JText::_('COM_COMMUNITY_NO_BUTTON'); ?>" />
            </form>

            <?php
        }

        public function connect_sent() {
            /**
             * Opengraph
             */
            CHeadHelper::setType('website', JText::_('COM_COMMUNITY_PROFILE_CONNECT_REQUEST_SENT'));
        }

        public function appFullView() {
            $mainframe = JFactory::getApplication();
            $jinput = $mainframe->input;
            $userid = $jinput->getInt('userid', null);
            $user = CFactory::getUser($userid);
            $profileModel = CFactory::getModel('profile');
            $avatarModel = CFactory::getModel('avatar');
            $applications = CAppPlugins::getInstance();
            $appName = JString::strtolower($jinput->get->get('app', '', 'STRING'));

            if (empty($appName)) {
                JFactory::getApplication()->enqueueMessage(JText::_('COM_COMMUNITY_APPS_ID_REQUIRED'), 'error');
            }

            if (is_null($userid)) {
                JFactory::getApplication()->enqueueMessage(JText::_('COM_COMMUNITY_APPS_ID_REQUIRED'), 'error');
            }

            /**
             * Opengraph
             */
            CHeadHelper::setType('website', $user->getDisplayName() . ' : ' . $user->getStatus());

            $appsModel = CFactory::getModel('apps');
            $appId = $appsModel->getUserApplicationId($appName);
            $plugin = $applications->get($appName, $appId);

            if (!$plugin) {
                JFactory::getApplication()->enqueueMessage(JText::_('COM_COMMUNITY_APPS_NOT_FOUND'), 'error');
            }

            $appObj = new stdClass();
            $data = new stdClass();
            $appObj->name = $plugin->name;
            $appObj->html = $plugin->onAppDisplay();
            $data->html = $appObj->html;

            $this->attachMiniHeaderUser($user->id);

            echo $data->html;
        }

        /**
         * Display Upload avatar form for user
         * */
        public function uploadAvatar() {
            $mainframe = JFactory::getApplication();
            if (!$this->accessAllowed('registered')) {
                echo JText::_('COM_COMMUNITY_MEMBERS_AREA');
                return;
            }

            $my = CFactory::getUser();
            $firstLogin = false;

            $pathway = $mainframe->getPathway();
            $menu = JFactory::getApplication()->getMenu()->getActive();
            if(isset($menu->title)) {
                $pathway->addItem(JText::_($menu->title), CRoute::getExternalURL($menu->link));
            }
            $pathway->addItem(JText::_($my->getDisplayName()), CRoute::_('index.php?option=com_community&view=profile&userid=' . $my->id));
            $pathway->addItem(JText::_('COM_COMMUNITY_CHANGE_AVATAR'), '');

            // Load the toolbar
            //$this->showSubmenu();
            /**
             * Opengraph
             */
            CHeadHelper::setType('website', JText::_('COM_COMMUNITY_CHANGE_AVATAR'));

            $config = CFactory::getConfig();
            $uploadLimit = (double) $config->get('maxuploadsize');
            $uploadLimit .= 'MB';

            $tmpl = new CTemplate();
            $skipLink = CRoute::_('index.php?option=com_community&view=frontpage&doSkipAvatar=Y&userid=' . $my->id);

            $largeAvatar = $my->getAvatar();
            $fileName = basename($largeAvatar);
            $avatarImageDir = $config->getString('imagefolder') . '/avatar/';

            if (JFile::exists($avatarImageDir . 'profile-' . $fileName)) {
                $largeAvatar = str_replace($fileName, 'profile-' . $fileName, $largeAvatar);
            }

            echo $tmpl->set('user', $my)
                    ->set('largeAvatar', $largeAvatar)
                    ->set('profileType', $my->getProfileType())
                    ->set('uploadLimit', $uploadLimit)
                    ->set('firstLogin', $firstLogin)
                    ->set('skipLink', $skipLink)
                    ->set('submenu', $this->showSubmenu(false))
                    ->fetch('profile.uploadavatar');
        }

        /**
         * Display Upload video form for user
         * */
        public function linkVideo() {
            if (!$this->accessAllowed('registered')) {
                echo JText::_('COM_COMMUNITY_MEMBERS_AREA');
                return;
            }

            $mainframe = JFactory::getApplication();
            $jinput = $mainframe->input;
            $document = JFactory::getDocument();
            $config = CFactory::getConfig();
            $my = CFactory::getUser();
            $videoModel = CFactory::getModel('videos');

            $pathway = $mainframe->getPathway();
            $menu = JFactory::getApplication()->getMenu()->getActive();
            if(isset($menu->title)) {
                $pathway->addItem(JText::_($menu->title), CRoute::getExternalURL($menu->link));
            }
            $pathway->addItem(JText::_($my->getDisplayName()), CRoute::_('index.php?option=com_community&view=profile&userid=' . $my->id));
            $pathway->addItem(JText::_('COM_COMMUNITY_VIDEOS_EDIT_PROFILE_VIDEO'), '');

            // Load the toolbar
            //$this->showSubmenu();
            /**
             * Opengraph
             */
            CHeadHelper::setType('website', JText::_('COM_COMMUNITY_VIDEOS_EDIT_PROFILE_VIDEO'));

            $video = $this->_getCurrentProfileVideo();

            $filters = array
                (
                'creator' => $my->id,
                'status' => 'ready',
                'sorting' => $jinput->get('sort', 'latest', 'STRING')
            );
            $videos = $videoModel->getVideos($filters, true);

            $sortItems = array
                (
                'latest' => JText::_('COM_COMMUNITY_VIDEOS_SORT_LATEST'),
                'mostwalls' => JText::_('COM_COMMUNITY_VIDEOS_SORT_MOST_WALL_POST'),
                'mostviews' => JText::_('COM_COMMUNITY_VIDEOS_SORT_POPULAR'),
                'title' => JText::_('COM_COMMUNITY_VIDEOS_SORT_TITLE')
            );

            // Pagination
            $pagination = $videoModel->getPagination();

            $redirectUrl = CRoute::getURI(false);

            $tmpl = new CTemplate();
            echo $tmpl->set('my', $my)
                    ->set('video', $video)
                    ->set('sort', $jinput->get('sort', 'latest', 'STRING'))
                    ->set('videos', $videos)
                    ->set('sortings', CFilterBar::getHTML(CRoute::getURI(), $sortItems, 'latest'))
                    ->set('pagination', $pagination)
                    ->set('videoThumbWidth', CVideoLibrary::thumbSize('width'))
                    ->set('videoThumbHeight', CVideoLibrary::thumbSize('height'))
                    ->set('redirectUrl', $redirectUrl)
                    ->set('submenu', $this->showSubmenu(false))
                    ->fetch('profile.linkvideo');
        }

        public function video() {
            $tmpl = new CTemplate();
            echo $tmpl->fetch('videos/single');
        }

        /**
         *
         */
        public function privacy() {
            $mainframe = JFactory::getApplication();

            if (!$this->accessAllowed('registered'))
                return;

            $pathway = $mainframe->getPathway();
            $my = CFactory::getUser();

            $menu = JFactory::getApplication()->getMenu()->getActive();
            if(isset($menu->title)) {
                $pathway->addItem(JText::_($menu->title), CRoute::getExternalURL($menu->link));
            }
            $pathway->addItem(JText::_($my->getDisplayName()), CRoute::_('index.php?option=com_community&view=profile&userid=' . $my->id));
            $pathway->addItem(JText::_('COM_COMMUNITY_PROFILE_PRIVACY_EDIT'), '');

            /**
             * Opengraph
             */
            CHeadHelper::setType('website', JText::_('COM_COMMUNITY_PROFILE_PRIVACY_EDIT'));

            $this->showSubmenu();
            $user = CFactory::getUser();
            $params = $user->getParams();
            $config = CFactory::getConfig();

            //Get blocked list
            $model = CFactory::getModel('block');
            $blocklists = $model->getBanList($my->id);

            foreach ($blocklists as $user) {
                $blockedUser = CFactory::getUser($user->blocked_userid);
                $user->avatar = $blockedUser->getThumbAvatar();
            }


            $app = CAppPlugins::getInstance();
            $appFields = $app->triggerEvent('onFormDisplay', array('jsform-profile-privacy'));
            $beforeFormDisplay = CFormElement::renderElements($appFields, 'before');
            $afterFormDisplay = CFormElement::renderElements($appFields, 'after');

            //user's email privacy setting
            $notificationTypes = new CNotificationTypes();

            $tmpl = new CTemplate();
            echo $tmpl->set('beforeFormDisplay', $beforeFormDisplay)
                    ->set('afterFormDisplay', $afterFormDisplay)
                    ->set('blocklists', $blocklists)
                    ->set('params', $params)
                    ->set('config', $config)
                    ->set('notificationTypes', $notificationTypes)
                    //->set('emailtypes', $emailtypes->getEmailTypes())
                    ->fetch('profile.privacy');
        }

        public function preferences() {
            $mainframe = JFactory::getApplication();

            if (!$this->accessAllowed('registered')) {
                return;
            }
            //$this->showSubmenu();

            /**
             * Opengraph
             */
            CHeadHelper::setType('website', JText::_('COM_COMMUNITY_EDIT_PREFERENCES'));

            $my = CFactory::getUser();
            $params = $my->getParams();
            $jConfig = JFactory::getConfig();
            $pathway = $mainframe->getPathway();

            $menu = JFactory::getApplication()->getMenu()->getActive();
            if($menu){
                $pathway->addItem(JText::_($menu->title), CRoute::getExternalURL($menu->link));
            }
            $pathway->addItem(JText::_($my->getDisplayName()), CRoute::_('index.php?option=com_community&view=profile&userid=' . $my->id));
            $pathway->addItem(JText::_('COM_COMMUNITY_EDIT_PREFERENCES'), '');

            $prefixURL = $my->getAlias();

            if ($mainframe->get('sef')) {
                $juriRoot = JURI::root(false);
                $juriPathOnly = JURI::root(true);
                $juriPathOnly = rtrim($juriPathOnly, '/');
                $profileURL = rtrim(str_replace($juriPathOnly, '', $juriRoot), '/');

                $profileURL .= CRoute::_('index.php?option=com_community&view=profile&userid=' . $my->id, false);
                $alias = $my->getAlias();

                $inputHTML = '<input id="alias" name="alias" type="text" class="joms-input" value="' . $alias . '" />';
                $prefixURL = str_replace($alias, $inputHTML, $profileURL);

                // For backward compatibility issues, as we changed from ID-USER to ID:USER in 2.0,
                // we also need to test older urls.
                if ($prefixURL == $profileURL) {
                    $prefixURL = CString::str_ireplace(CString::str_ireplace(':', '-', $alias), $inputHTML, $profileURL);
                }
            }

            $app = CAppPlugins::getInstance();
            $appFields = $app->triggerEvent('onFormDisplay', array('jsform-profile-preferences'));
            $beforeFormDisplay = CFormElement::renderElements($appFields, 'before');
            $afterFormDisplay = CFormElement::renderElements($appFields, 'after');

            //Get blocked list
            $blockLists = $my->getBlockedUsers();

            //user's email privacy setting
            $notificationTypes = new CNotificationTypes();

            $tmpl = new CTemplate();
            echo $tmpl->set('beforeFormDisplay', $beforeFormDisplay)
                    ->set('afterFormDisplay', $afterFormDisplay)
                    ->set('params', $params)
                    ->set('prefixURL', $prefixURL)
                    ->set('user', $my)
                    ->set('blockedUsers', $blockLists)
                    ->set('jConfig', $jConfig)
                    ->set('notificationTypes', $notificationTypes)
                    ->set('submenu', $this->showSubmenu(false))
                    ->fetch('profile.preferences');
        }

        public function deleteProfile() {
            if (!$this->accessAllowed('registered'))
                return;

            $config = CFactory::getConfig();

            if (!$config->get('profile_deletion')) {
                echo JText::_('COM_COMMUNITY_RESTRICTED_ACCESS');
                return;
            }

            /**
             * Opengraph
             */
            CHeadHelper::setType('website', JText::_('COM_COMMUNITY_DELETE_PROFILE'));

            $my = CFactory::getUser();
            $this->addPathWay(JText::_('COM_COMMUNITY_PROFILE'), CRoute::_('index.php?option=com_community&view=profile&userid=' . $my->id));
            $this->addPathWay(JText::_('COM_COMMUNITY_EDIT_PREFERENCES'), '');

            $tmpl = new CTemplate();
            echo $tmpl->fetch('profile.deleteprofile');
        }

        /**
         *
         */
        public function notifications() {
            $mainframe = JFactory::getApplication();

            if (!$this->accessAllowed('registered'))
                return;

            $pathway = $mainframe->getPathway();
            $my = CFactory::getUser();
            $menu = JFactory::getApplication()->getMenu()->getActive();
            $pathway->addItem(JText::_($menu->title), CRoute::getExternalURL($menu->link));
            $pathway->addItem(JText::_($my->getDisplayName()), CRoute::_('index.php?option=com_community&view=profile&userid=' . $my->id));
            $pathway->addItem(JText::_('COM_COMMUNITY_PROFILE_NOTIFICATIONS'), '');

            /**
             * Opengraph
             */
            CHeadHelper::setType('website', JText::_('COM_COMMUNITY_PROFILE_NOTIFICATIONS'));

            $user = CFactory::getUser();
            $params = $user->getParams();
            $config = CFactory::getConfig();

            $modelNotification = CFactory::getModel('notification');
            $notifications = $modelNotification->getNotification($my->id, '0', 0);

            $app = CAppPlugins::getInstance();
            $appFields = $app->triggerEvent('onFormDisplay', array('jsform-profile-notifications'));

            $beforeFormDisplay = CFormElement::renderElements($appFields, 'before');
            $afterFormDisplay = CFormElement::renderElements($appFields, 'after');

            $tmpl = new CTemplate();
            echo $tmpl->set('beforeFormDisplay', $beforeFormDisplay)
                    ->set('afterFormDisplay', $afterFormDisplay)
                    ->set('params', $params)
                    ->set('config', $config)
                    ->set('submenu', $this->showSubmenu(false))
                    ->set('pagination', $modelNotification->getPagination())
                    ->set('notifications', $notifications)
                    ->fetch('profile.notification');
        }

        /* Jomsocial 3.0 - Modules */

        // User info
        public function modProfileUserinfo() {
            jimport('joomla.utilities.arrayhelper');

            $mainframe    = JFactory::getApplication();
            $jinput       = $mainframe->input;
            $my           = CFactory::getUser();
            $userid       = $jinput->get('userid', $my->id, 'INT');
            $user         = CFactory::getUser($userid);
            $params       = $user->getParams();
            $userModel    = CFactory::getModel('user');
            $profileModel = CFactory::getModel('profile');

            //Reassign needed variable
            $data = new stdClass();
            $data->user = $user;
            $data->profile = $profileModel->getViewableProfile($userid, $user->getProfileType());
            $data->videoid = $params->get('profileVideo', 0);


            CFactory::load('libraries', 'messaging');

            $isMine = COwnerHelper::isMine($my->id, $user->id);

            // Get the admin controls HTML data
            $adminControlHTML = '';

            $tmpl = new CTemplate ();

            // get how many unread message
            $filter = array();
            $inboxModel = CFactory::getModel('inbox');
            $filter['user_id'] = $my->id;
            $unread = $inboxModel->countUnRead($filter);

            // get how many pending connection
            $friendModel = CFactory::getModel('friends');
            $pending = $friendModel->countPending($my->id);

            $profile = Joomla\Utilities\ArrayHelper::toObject($data->profile);
            $profile->largeAvatar = $user->getAvatar();
            $profile->defaultAvatar = $user->isDefaultAvatar();

            // Find avatar album and photo.
            $profile->avatarAlbum = false;
            $profile->avatarPhoto = false;
            $album = JTable::getInstance('Album', 'CTable');
            $albumId = $album->isAvatarAlbumExists($user->id, 'profile');
            if ($albumId) {
                $album->load($albumId);
                $profile->avatarAlbum = $albumId;
                $profile->avatarPhoto = $album->photoid;
            }

            $profile->status = $user->getStatus();
            $profile->defaultCover = $user->isDefaultCover();
            $profile->cover =  $user->getCover();

            // Cover position.
            $profile->coverPostion = $params->get('coverPosition', '');
            if ( strpos( $profile->coverPostion, '%' ) === false )
                $profile->coverPostion = 0;

            // Find cover album and photo.
            $profile->coverAlbum = false;
            $profile->coverPhoto = false;
            $album = JTable::getInstance('Album', 'CTable');
            $albumId = $album->isCoverExist('profile', $user->id);
            if ($albumId) {
                $album->load($albumId);
                $profile->coverAlbum = $albumId;
                $profile->coverPhoto = $album->photoid;
            }

            $groupmodel = CFactory::getModel('groups');
            $profile->_groups = $groupmodel->getGroupsCount($profile->id);

            $eventmodel = CFactory::getModel('events');
            $profile->_events = $eventmodel->getEventsCount($profile->id);

            $profile->_friends = $user->_friendcount;

            $videoModel = CFactory::getModel('Videos');
            $profile->_videos = $videoModel->getVideosCount($profile->id);

            $photosModel = CFactory::getModel('photos');
            $profile->_photos = $photosModel->getPhotosCount($profile->id);

            if ($profile->status !== '') {
                $postedOn = new JDate($user->_posted_on);
                $postedOn = CActivityStream::_createdLapse($postedOn);
                $profile->posted_on = $user->_posted_on == '0000-00-00 00:00:00' ? '' : $postedOn;
            } else {
                $profile->posted_on = '';
            }

            /* is featured */
            $modelFeatured = CFactory::getModel('Featured');
            $profile->featured = $modelFeatured->isExists(FEATURED_USERS, $profile->id);

            // Assign videoId
            $profile->profilevideo = $data->videoid;

            $video = JTable::getInstance('Video', 'CTable');
            $video->load($profile->profilevideo);
            $profile->profilevideoTitle = $video->getTitle();

            $addbuddy = "joms.api.friendAdd('{$profile->id}')";
            $sendMsg = CMessaging::getPopup($profile->id);

            $config = CFactory::getConfig();
            $jConfig = JFactory::getConfig();

            $lastLogin = JText::_('COM_COMMUNITY_PROFILE_NEVER_LOGGED_IN');
            if ($user->lastvisitDate != '0000-00-00 00:00:00') {
                $userLastLogin = new JDate($user->lastvisitDate);
                $lastLogin = CActivityStream::_createdLapse($userLastLogin);
            }

            // @todo : beside checking the owner, maybe we want to check for a cookie,
            // say every few hours only the hit get increment by 1.
            if (!$isMine) {
                $user->viewHit();
            }

            // @rule: myblog integrations
            $showBlogLink = false;
            $myblog = CMyBlog::getInstance();

            if ($config->get('enablemyblogicon') && $myblog) {
                if ($myblog->userCanPost($user->id)) {
                    $showBlogLink = true;
                }
                $tmpl->set('blogItemId', $myblog->getItemId());
            }

            $photoEnabled = ($config->get('enablephotos')) ? true : false;
            $eventEnabled = ($config->get('enableevents')) ? true : false;
            $groupEnabled = ($config->get('enablegroups')) ? true : false;
            $videoEnabled = ($config->get('enablevideos')) ? true : false;
            $isSEFEnabled = ($jConfig->get('sef')) ? true : false;

            $multiprofile = JTable::getInstance('MultiProfile', 'CTable');
            $multiprofile->load($user->getProfileType());

            CFactory::load('libraries', 'like');

            $like = new Clike();

            $isLikeEnabled = $like->enabled('profile') && $params->get('profileLikes', 1) ? 1 : 0;
            $isUserLiked = $like->userLiked('profile', $user->id, $my->id);

            /* likes count */
            $likes = $like->getLikeCount('profile', $user->id);

            /* User status */
            $status = new CUserStatus($user->id, 'profile');

            //respect wall setting
            if ($my->id && ((!$config->get('lockprofilewalls')) || ( $config->get('lockprofilewalls') && CFriendsHelper::isConnected($my->id, $profile->id) ) ) || COwnerHelper::isCommunityAdmin()) {
                // Add default status box

                CUserHelper::addDefaultStatusCreator($status);
            }

            //$isblocked = $user->isBlocked();

            $isMine = COwnerHelper::isMine($my->id, $user->id);
            $isCommunityAdmin = COwnerHelper::isCommunityAdmin($user->id);

            // Check if user is blocked
            $getBlockStatus = new blockUser();
            $isblocked = $getBlockStatus->isUserBlocked($user->id, 'profile');

            // Get block user html
            //$blockUserHTML = $isMine || $isCommunityAdmin ? '' : CUserHelper::getBlockUserHTML($user->id, $isBlocked);

            $isMine = COwnerHelper::isMine($my->id, $user->id);
            $isCommunityAdmin = COwnerHelper::isCommunityAdmin($user->id);

            // Get reporting html
            $report = new CReportingLibrary();
            $reportsHTML = $isMine ? '' : $report->getReportingHTML(JText::_('COM_COMMUNITY_REPORT_USER'), 'profile,reportProfile', array($user->id));

            $tmpl = new CTemplate();
            echo $tmpl->set('karmaImgUrl', CUserPoints::getPointsImage($user))
                    //->set('blockUserHTML', $blockUserHTML)
                    ->set('reportsHTML', $reportsHTML)
                    ->set('isMine', $isMine)
                    ->set('lastLogin', $lastLogin)
                    ->set('addBuddy', $addbuddy)
                    ->set('sendMsg', $sendMsg)
                    ->set('config', $config)
                    ->set('multiprofile', $multiprofile)
                    ->set('showBlogLink', $showBlogLink)
                    ->set('isFriend', CFriendsHelper::isConnected($user->id, $my->id) && $user->id != $my->id)
                    ->set('isWaitingApproval', CFriendsHelper::isWaitingApproval($my->id, $user->id))
                    ->set('isWaitingResponse', CFriendsHelper::isWaitingApproval($user->id, $my->id))
                    ->set('isBlocked', $isblocked)
                    ->set('profile', $profile)
                    ->set('unread', $unread)
                    ->set('pending', $pending)
                    ->set('registerDate', $user->registerDate)
                    ->set('adminControlHTML', $adminControlHTML)
                    ->set('userstatus', $status)
                    ->set('showFeatured', $config->get('show_featured'))
                    ->set('user', $user)
                    ->set('isUserLiked', $isUserLiked)
                    ->set('likes', $likes)
                    ->set('isLikeEnabled', $isLikeEnabled)
                    ->set('photoEnabled', $photoEnabled)
                    ->set('eventEnabled', $eventEnabled)
                    ->set('groupEnabled', $groupEnabled)
                    ->set('videoEnabled', $videoEnabled)
                    ->set('about', $this->_getProfileHTML($data->profile))
                    ->set('isSEFEnabled', $isSEFEnabled)
                    ->set('blocked', $user->isBlocked())
                    ->fetch('profile/focus');
        }

        // User Status
        public function modProfileUserstatus() {
            $my = CFactory::getUser();
            $mainframe = JFactory::getApplication();
            $jinput = $mainframe->input;

            $userid = $jinput->get('userid', $my->id, 'INT');
            $user = CFactory::getUser($userid);
            $config = CFactory::getConfig();

            //respect wall setting
            $status = new CUserStatus($user->id, 'profile');


            if ($my->id && ((!$config->get('lockprofilewalls')) || ( $config->get('lockprofilewalls') && CFriendsHelper::isConnected($my->id, $user->id) ) ) || COwnerHelper::isCommunityAdmin()) {
                // Add default status box
                CUserHelper::addDefaultStatusCreator($status);
            }

            #echo $status->render();
        }

        public function modProfileActivities() {
            $this->_getNewsfeedHTML();
        }

        public function modProfileUserVideo() {
            $my = CFactory::getUser();
            $jinput = JFactory::getApplication()->input;
            $userid = $jinput->get('userid', $my->id, 'INT');
            $user = CFactory::getUser($userid);
            $config = CFactory::getConfig();

            $params = $user->getParams();

            $profilevideoId = $params->get('profileVideo', 0);

            if ($config->get('enablevideos') && $config->get('enableprofilevideo') && $profilevideoId) {
                $video = JTable::getInstance('Video', 'CTable');
                $video->load($profilevideoId);

                $tmpl = new CTemplate();

                echo $tmpl->set('video', $video)
                        ->set('videoThumbWidth', CVideoLibrary::thumbSize('width'))
                        ->set('videoThumbHeight', CVideoLibrary::thumbSize('height'))
                        ->fetch('profile.video');
            }

            return false;
        }

    }

}
