<?php

/**
 * @copyright (C) 2013 iJoomla, Inc. - All rights reserved.
 * @license GNU General Public License, version 2 (http://www.gnu.org/licenses/gpl-2.0.html)
 * @author iJoomla.com <webmaster@ijoomla.com>
 * @url https://www.jomsocial.com/license-agreement
 * The PHP code portions are distributed under the GPL license. If not otherwise stated, all images, manuals, cascading style sheets, and included JavaScript *are NOT GPL, and are released under the IJOOMLA Proprietary Use License v1.0
 * More info at https://www.jomsocial.com/license-agreement
 */
defined('_JEXEC') or die();
jimport('joomla.application.component.view');

if (!class_exists('CommunityView')) {

    /**
     * Base community view
     */
    class CommunityView extends JViewLegacy {

        var $_info = array();
        var $_warning = array();
        var $_error = array();
        var $_submenu = array();
        var $title = '';
        var $_mini = '';
        var $params = array();
        var $_showMiniHeaderUser = '';

        public function __construct($config = array()) {
            $mainframe = JFactory::getApplication();
            $jinput = $mainframe->input;
            $this->my = CFactory::getUser();
            parent::__construct($config);

            $view = $jinput->request->get('view', '');
            $this->set('assets', CAssets::getInstance());
        }

        /**
         *
         * */
        public function setTitle($title) {
            $this->title = CStringHelper::escape($title);
        }

        /**
         * Append the given message into a global info messages
         * @param string	the final message
         */
        public function addInfo($message) {
            $mainframe = JFactory::getApplication();
            $mainframe->enqueueMessage($message);
        }

        /**
         * Adds a pathway item to the breadcrumbs
         * */
        public function addPathway($text, $link = '') {
            // Set pathways
            $mainframe = JFactory::getApplication();
            $pathway = $mainframe->getPathway();

            $pathwayNames = $pathway->getPathwayNames();

            // Test for duplicates before adding the pathway
            if (!in_array($text, $pathwayNames)) {
                $pathway->addItem($text, $link);
            }
        }

        /**
         * Display no access notice
         */
        public function noAccess($notice = '') {
            $tmpl = new CTemplate();
            $notice = empty($notice) ? JText::_('COM_COMMUNITY_NOT_ALLOWED_TO_VIEW_PAGE') : $notice;

            $tmpl->set('notice', $notice);
            echo $tmpl->fetch('notice.access');
        }

        /**
         * Append the given message into a global warning messages
         * @param string	the final message
         */
        public function addWarning($message) {
            //$this->_warning[] = $message;
            $mainframe = JFactory::getApplication();
            $mainframe->enqueueMessage($message, 'notice');
        }

        public function attachMiniHeaderUser($userId) {
            $this->_showMiniHeaderUser = $userId;
        }

        /**
         *
         */
        public function addSubmenuItem($link = '', $title = '', $onclick = '', $isAction = false, $childItem = '', $class = '') {
            $obj = new stdClass();
            $obj->link = $link;

            // If onclick is used, completely ignore the following
            if (!empty($onclick)) {
                $obj->onclick = $onclick;
            } else {
                // We need to get the view & task from the link that is provided in $link
                // Remove default 'index.php?option=com_community&' from $link
                $link = CString::str_ireplace('index.php?option=com_community&', '', $link);
                $links = explode('&', $link);

                // Pre-set the task so that links that does not contain task will not fail.
                $obj->task = '';
                $obj->view = '';
                foreach ($links as $row) {
                    $active = explode('=', $row);

                    if ($active[0] == 'view')
                        $obj->view = $active[1];

                    if ($active[0] == 'task')
                        $obj->task = $active[1];
                }
            }

            $obj->action = $isAction;
            $obj->title = $title;
            $obj->childItem = $childItem;
            $obj->class = $class;

            $this->_submenu[] = $obj;
        }

        /**
         * Deprecated since 2.2
         * Should use CToolbarLibrary::getHTML instead
         * */
        public function showToolbar($data = null) {
            $toolbar_lib = CToolbarLibrary::getInstance();
            echo $toolbar_lib->getHTML($this->_showMiniHeaderUser);
        }

        /*
         * Temporary replacement as we don't want
         * showToolbar() to load all the unnecessary
         * scripts & styles.
         *
         * */

        public function showToolbarMobile() {
            $tmpl = new CTemplate();
            $searchform = $tmpl->fetch('search.form');
            $tmpl->set('searchform', $searchform);
            echo $tmpl->fetch('toolbar/base');
        }

        /**
         *
         */
        public function showSubmenu($display=true) {
            /*
             * @since 3.3 submenu is no longer needed
             * @todo Remove all function call to this method
             */

            $submenu = &$this->_submenu;
            $mainframe = JFactory::getApplication();
            $jinput = $mainframe->input;

            if (!empty($submenu)) {
                $view = $jinput->get('view', '', 'STRING');
                $task = $jinput->get('task', '', 'STRING');
                $noActive = false;

                // Don't highlight active link if we're not viewing ours
                if ($task == 'myphotos' || $task == 'myvideos' || $task == 'mygroups' || $task == 'myevents') {
                    $my = CFactory::getUser();
                    $userid = $jinput->get('userid', 0, 'INT');

                    if ($my->id != $userid && $userid != 0) {
                        $noActive = true;
                    }
                }

                // Shift action menu items to the back
                $i = 0;
                $shifted = 0;
                $total = count($submenu);
                while ($i + $shifted < $total) {
                    if ($submenu[$i]->action) {
                        $menu = array_splice($submenu, $i, 1);
                        $submenu = array_merge($submenu, $menu);
                        $i--;
                        $shifted++;
                    }
                    $i++;
                }

                $tmpl = new CTemplate();
                $tmpl->set('submenu', $submenu);
                $tmpl->set('view', $view);
                $tmpl->set('task', $task);
                $tmpl->set('noActive', $noActive);
                if($display){
                    echo $tmpl->fetch('toolbar.submenu');
                    unset($tmpl);
                }else{
                    return $tmpl->fetch('toolbar.submenu');
                }

            }

            return;
        }

        /**
         * Return the page header
         * @return	string
         */
        public function showHeader($title, $icon = null, $buttons = null) {
            /*
             * @since 4.0 for third party template to place their header script within jomsocial
             * @todo remove all the function to this method
             */
            $tmpl = new CTemplate();
            echo $tmpl->fetch('general.header');
            return;
        }

        /**
         * Return page submenu
         */
        public function getSubMenu() {

        }

        /**
         * Get the processed content
         *
         * @param	string	$tplName	method name to call
         * @param	array	$data		data for the template
         * @param	string	$cached		should we result be cached?
         * @return	string				the final output
         */
        public function get($tplName, $data = null, $cached = false) {

            if (!empty($tplName) && is_callable(array($this, $tplName))) {

                ob_start();
                $this->$tplName($data);
                $html = ob_get_contents();
                ob_end_clean();


                $info = '';
                if (!empty($this->_info)) {
                    foreach ($this->_info as $msg) {
                        $info .= $this->info($msg);
                    }
                }

                $warning = '';
                if (!empty($this->_warning)) {
                    foreach ($this->_warning as $msg) {
                        $warning .= $this->warning($msg);
                    }
                }

                $error = '';
                $messages = array($error, $warning, $info);

                // append all warning, error and info
                $html = CString::str_ireplace(array('{error}', '{warning}', '{info}'), $messages, $html);
                return $html;
            } else {
                JFactory::getApplication()->enqueueMessage(JText::sprintf('COM_COMMUNITY_VIEW_NOT_FOUND', $tplName), 'error');
            }
        }

        /**
         * Show profile with limited access view.
         * Just the box with links to add as friend etc
         */
        public function showLimitedProfile($userid) {
            $my = CFactory::getUser();
            $user = CFactory::getUser($userid);
            $mainframe = JFactory::getApplication();
            $mainframe->enqueueMessage(JText::_('COM_COMMUNITY_PROFILE_RESTRICTED_VIEW_MESSAGE'), 'notice');
            $tmpl = new CTemplate();

            $userInfo = $this->_prepUser($user);
            if (!empty($userInfo)) {
                $tmpl->set('data', $userInfo);
                //$tmpl->set('alreadyfriend', array());
                $tmpl->set('sortings', ''); // add this variable to avoid error thrown.
                $tmpl->set('featuredList', '');
                $tmpl->set('isCommunityAdmin', '');
                $tmpl->set('pagination', '');
                $tmpl->set('my', $my);
                echo $tmpl->fetch('people.browse');
            }
        }

        /**
         * Check if current user has the correct permission to view the page.
         * We will validate access based on the current profile privacy setting and
         * the access type give. Should be called by view
         *
         * @param string type The access type, one of CUser param variables or
         * 	 mine (active profile = my id)/registered(any registered user)
         * @param bool $showWarning
         * @return	bool true if access is OK
         */
        public function accessAllowed($type = '', $showWarning = true) {
            if (empty($type))
                return true;

            $my = CFactory::getUser();
            $mainframe = JFactory::getApplication();
            $jinput = $mainframe->input;
            $userid = $jinput->get('userid', '', 'INT');
            $user = CFactory::getUser($userid);


            // @rule: For site administrators / community admin, we should allow access
            // no matter what the privacy is.
            if (COwnerHelper::isCommunityAdmin()) {
                return true;
            }

            if ($type == 'registered') {
                if (!$my->id) {
                    $mainframe->enqueueMessage(JText::_('COM_COMMUNITY_RESTRICTED_ACCESS'), 'notice');
                    $this->noAccess();
                    return false;
                } else
                    return true;
            }

            // you can always view your own profile
            if (COwnerHelper::isMine($my->id, $user->id)) {
                return true;
            }

            $param = & $user->getParams();

            if ($type == 'mine')
                $access = PRIVACY_PRIVATE;
            else
                $access = $param->get($type);

            switch ($access) {
                case PRIVACY_PUBLIC:
                    return true;
                    break;

                case PRIVACY_MEMBERS:
                    if ($my->id == 0) {
                        $mainframe = JFactory::getApplication();
                        $mainframe->enqueueMessage(JText::_('COM_COMMUNITY_RESTRICTED_ACCESS'), 'notice');
                        $tmpl = new CTemplate();
                        $notice = '';

                        if ($type == 'privacyProfileView') {
                            $userInfo = $this->_prepUser($user);
                            if (!empty($userInfo)) {
                                $tmpl->set('data', $userInfo);
                                $tmpl->set('sortings', '');
                                $tmpl->set('featuredList', '');
                                $tmpl->set('isCommunityAdmin', '');
                                //$tmpl->set('alreadyfriend', array());
                                $tmpl->set('my', $my);
                                echo $tmpl->fetch('people.browse');
                            } else
                            //user object not found.
                                $notice = JText::_('COM_COMMUNITY_NOT_ALLOWED_TO_VIEW_PAGE');

                            $tmpl->set('notice', $notice);
                            echo $tmpl->fetch('notice.access');
                        }
                        else {
                            $this->noAccess();
                        }
                        return false;
                    }
                    return true;
                    break;

                case PRIVACY_FRIENDS:


                    if (!CFriendsHelper::isConnected($my->id, $user->id)) {
                        $mainframe = JFactory::getApplication();
                        $mainframe->enqueueMessage(JText::_('COM_COMMUNITY_RESTRICTED_ACCESS'), 'notice');
                        $tmpl = new CTemplate();

                        if ($type == 'privacyProfileView') {
                            $userInfo = $this->_prepUser($user);
                            if (!empty($userInfo)) {
                                $tmpl->set('data', $userInfo);
                                $tmpl->set('sortings', ''); // add this variable to avoid error thrown.
                                $tmpl->set('featuredList', '');
                                $tmpl->set('isCommunityAdmin', '');
                                //$tmpl->set('alreadyfriend', array());
                                $tmpl->set('my', $my);
                                echo $tmpl->fetch('people.browse');
                            } else {
                                //user object not found.
                                $this->noAccess();
                            }
                        } else {
                            $this->noAccess();
                        }
                        return false;
                    } else
                        return true;

                    break;

                case PRIVACY_PRIVATE:

                    if ($my->id != $user->id) {
                        $mainframe = JFactory::getApplication();
                        $mainframe->enqueueMessage(JText::_('COM_COMMUNITY_RESTRICTED_ACCESS'), 'error');
                        $this->noAccess();
                        return false;
                    } else
                        return true;

                    break;
            }

            return true;
        }

        /**
         * Test if the application is viewable by the current browser.
         *
         * @param string $privacy The privacy settings for the app.
         * @return	bool true if access is OK
         */
        public function appPrivacyAllowed($privacy = 0) {
            if ($privacy == 0) {
                return true;
            }

            $mainframe = JFactory::getApplication();
            $jinput = $mainframe->input;

            $my = CFactory::getUser();
            $userid = $jinput->get('userid', '', 'INT');
            $user = CFactory::getUser($userid);

            switch ($privacy) {
                case PRIVACY_APPS_FRIENDS:
                    if (!CFriendsHelper::isConnected($my->id, $user->id)) {
                        return false;
                    }
                    break;
                case PRIVACY_APPS_SELF:
                    if ($my->id != $user->id) {
                        return false;
                    }
                    break;
            }
            return true;
        }

        /**
         * Show profile miniheader
         */
        public function _getMiniHeader() {
            $my = CFactory::getUser();
            $mh = new CMiniHeader();
            return $mh->showMiniHeader($my->id);
        }

        /**
         *
         */
        public function _newNotification() {
            $my = CFactory::getUser();

            $inboxModel = CFactory::getModel('inbox');
            $friendModel = CFactory::getModel('friends');
            $eventModel = CFactory::getModel('events');

            $filter = array();
            $filter ['user_id'] = $my->id;
            $inboxAlert = $inboxModel->countUnRead($filter);
            $frenAlert = $friendModel->countPending($my->id);
            $eventAlert = $eventModel->countPending($my->id);

            return ($inboxAlert + $frenAlert + $eventAlert);
        }

        /**
         * enqueue redirect for unpublish groups
         */
        protected function _redirectUnpublishGroup() {
            $mainframe = JFactory::getApplication();
            $mainframe->redirect(CRoute::_('index.php?option=com_community&view=groups'), JText::_('COM_COMMUNITY_GROUPS_UNPUBLISH_WARNING'));
        }

        /**
         * This function will prep user info so that it can display user mini header in privacy warning template.
         * Do not call this function outside this view.php
         */
        public function _prepUser($user) {
            if (!empty($user)) {
                $obj = new stdClass();
                $my = CFactory::getUser();
                $user = CFactory::getUser($user->id);
                $obj->friendsCount = $user->getFriendCount();
                $obj->user = $user;
                $obj->profileLink = CUrl::build('profile', '', array('userid' => $user->id));
                $isFriend = CFriendsHelper::isConnected($user->id, $my->id);

                $obj->addFriend = ((!$isFriend) && ($my->id != 0) && $my->id != $user->id) ? true : false;
                return array($obj);
            }

            return false;
        }

        /*
         * This function will return the apps' links
         */

        public function getAppSearchLinks($currentApp = NULL) {
            $config = CFactory::getConfig();

            $confSetting = array(
                JText::_('COM_COMMUNITY_SEARCH_USERS_TITLE') => '1',
                JText::_('COM_COMMUNITY_SEARCH_GROUPS_TITLE') => $config->get('enablegroups'),
                JText::_('COM_COMMUNITY_SEARCH_VIDEOS_TITLE') => $config->get('enablevideos'),
                JText::_('COM_COMMUNITY_SEARCH_EVENTS_TITLE') => $config->get('enableevents'),
            );

            $apps = array(
                JText::_('COM_COMMUNITY_SEARCH_USERS_TITLE') => CRoute::_('index.php?option=com_community&view=search'),
                JText::_('COM_COMMUNITY_SEARCH_GROUPS_TITLE') => CRoute::_('index.php?option=com_community&view=groups&task=search'),
                JText::_('COM_COMMUNITY_SEARCH_VIDEOS_TITLE') => CRoute::_('index.php?option=com_community&view=videos&task=search'),
                JText::_('COM_COMMUNITY_SEARCH_EVENTS_TITLE') => CRoute::_('index.php?option=com_community&view=events&task=search')
            );

            $key = array_key_exists($currentApp, $apps);

            if ($key) {
                unset($apps[$currentApp]);
            }

            foreach ($confSetting as $confKey => $value) {
                if (!$value) {
                    unset($apps[$confKey]);
                }
            }

            return $apps;
        }

        /**
         * Cached the function call
         */
        protected function _cachedCall($func, $params, $id, $tags) {
            //$jConfig    = JFactory::getConfig();
            $app = JFactory::getApplication();

            // Check if the caching is enabled
            //if($jConfig->getValue('caching')){
            if (JFactory::getConfig()->get('caching')) {
                $cache = CFactory::getFastCache();

                $cacheid = __FILE__ . __LINE__ . $func . md5(serialize($params) . serialize($id));

                if ($result = $cache->get($cacheid)) {
                    return $result;
                }

                $result = call_user_func_array(array($this, $func), $params);
                $cache->store($result, $cacheid, $tags);
            } else {
                $result = call_user_func_array(array($this, $func), $params);
            }

            return $result;
        }

        public function attachHeaders() {
            $document = JFactory::getDocument();
            $config = CFactory::getConfig();
            $mainframe = JFactory::getApplication();
            $jinput = $mainframe->input;
            $view = $jinput->request->get('view', '');

            $my = CFactory::getUser();
            $userid = $jinput->get('userid', '', 'INT');
            $user = CFactory::getUser($userid);

            if ($document->getType() != 'html') {
                return;
            }

            // Mobile devices flag.
            $isMobile = preg_match('/android|webos|iphone|ipad|ipod|blackberry|iemobile|opera mini/i', $_SERVER['HTTP_USER_AGENT']);
            $isIOS = preg_match('/iphone|ipad|ipod/i', $_SERVER['HTTP_USER_AGENT']);

            // Disable zooming on mobile devices.
            if ( $isMobile ) {
                $document->setMetaData('viewport', 'width=device-width, initial-scale=1, user-scalable=no');
            }

            // Output view.
            $document->addScriptDeclaration("joms_page = '" . $view . "';");

            $document->addScriptDeclaration("joms_prev_comment_load = +'" . $config->get('prev_comment_load', 10) . "';");

            // Group's ID.
            if ( $view === 'groups' ) {
                $groupid = $jinput->get('groupid', '', 'INT');
                $group = JTable::getInstance('Group', 'CTable');
                $group->load($groupid);
                $document->addScriptDeclaration("joms_group_id = +'" . $group->id . "';");
            }

            // Event's ID.
            if ( $view === 'events' ) {
                $eventid = $jinput->get('eventid', '', 'INT');
                $event = JTable::getInstance('Event', 'CTable');
                $event->load($eventid);
                $document->addScriptDeclaration("joms_event_id = +'" . $event->id . "';");
            }

            // Two-factor authentication.
            $document->addScriptDeclaration('joms_use_tfa = ' . (CSystemHelper::tfaEnabled() ? 'true' : 'false') . ';');

            // Google Maps API key
            $document->addScriptDeclaration("joms_gmap_key = '" . $config->get('googleapikey', '') . "';");

            // Flag to check if we should use native video player.
            if ( $config->get('video_native') == 1 ) {
                $document->addScriptDeclaration('joms_videoplayer_native = 1;');
            }

            CTemplate::addStylesheet('style');

            $templateParams = CTemplate::getTemplateParams();
            CTemplate::addStylesheet('style.' . $templateParams->get('colorTheme', 'green'));

            // Load rtl stylesheet
            if ($document->direction == 'rtl') {
                CTemplate::addStylesheet('style.rtl');
            }

            // Datepicker style.
            $css = 'assets/pickadate/themes/' . ( $isMobile ? 'default' : 'classic' ) . '.combined.css';
            CFactory::attach($css, 'css');

            // Fluid video on small screen.
            $css = '<style type="text/css">video { width: 100% !important; height: auto !important; }</style>';
            $document->addCustomTag($css);

            // Current URL
            $document->addScriptDeclaration('joms_current_url = "' . JUri::getInstance() . '";');
        }

    }

}
