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

jimport('joomla.application.component.view');
jimport('joomla.utilities.arrayhelper');

/**
 * Class exists checking
 */
if (!class_exists('CommunityViewFrontpage')) {

    /**
     * Community frontpage view class
     */
    class CommunityViewFrontpage extends CommunityView {

        /**
         * Frontpage display
         * @param type $tpl
         */
        public function display($tpl = null) {

            /**
             * Init variables
             */
            $config = CFactory::getConfig();
            $document = JFactory::getDocument();
            $usersConfig = JComponentHelper::getParams('com_users');
            $my = CFactory::getUser();
            $model = CFactory::getModel('user');

            /**
             * Opengraph
             */
            CHeadHelper::setType('website', JText::sprintf('COM_COMMUNITY_FRONTPAGE_TITLE', $config->get('sitename')));

            /**
             * Init document
             */
            $feedLink = CRoute::_('index.php?option=com_community&view=frontpage&format=feed');
            $feed = '<link rel="alternate" type="application/rss+xml" title="' . JText::_('COM_COMMUNITY_SUBSCRIBE_RECENT_ACTIVITIES_FEED') . '" href="' . $feedLink . '"/>';
            $document->addCustomTag($feed);

            // Process headers HTML output
            $headerHTML = '';
            $tmpl = new CTemplate();
            $alreadyLogin = 0;

            /* User is logged */
            if ($my->id != 0) {
                $headerHTML = $tmpl->fetch('frontpage.members');
                $alreadyLogin = 1;
            } else { /* User is not logged */
                $uri = 'index.php?option=com_community&view=' . $config->get('redirect_login');
                $uri = base64_encode($uri);

                $fbHtml = '';

                /* Facebook login */
                if ($config->get('fbconnectkey') && $config->get('fbconnectsecret') && !$config->get('usejfbc')) {
                    $facebook = new CFacebook();
                    $fbHtml = $facebook->getLoginHTML();
                }

                /* Joomla! Facebook Connect */
                if ($config->get('usejfbc')) {
                    if (class_exists('JFBCFactory')) {
                        $providers = JFBCFactory::getAllProviders();
                        foreach ($providers as $p) {
                            $fbHtml .= $p->loginButton();
                        }
                    }
                }

                //hero image
                $heroImage = JURI::root() . 'components/com_community/assets/frontpage-image-default.jpg';
                if (file_exists(COMMUNITY_PATH_ASSETS . 'frontpage-image.jpg')) {
                    $heroImage = JURI::root() . 'components/com_community/assets/frontpage-image.jpg';
                } else if (file_exists(COMMUNITY_PATH_ASSETS . 'frontpage-image.png')) {
                    $heroImage = JURI::root() . 'components/com_community/assets/frontpage-image.png';
                }


                //add the hero image as the image metatdata
                $imgMeta='<meta property="og:image" content="'.$heroImage.'"/>';
                $document->addCustomTag($imgMeta);

                $themeModel = CFactory::getModel('theme');
                $settings = $themeModel->getSettings();

                /* Generate header HTML for guest */
                if($settings['general']['enable-frontpage-login']) {
                    $headerHTML = $tmpl
                        ->set('allowUserRegister', $usersConfig->get('allowUserRegistration'))
                        ->set('heroImage', $heroImage)
                        ->set('fbHtml', $fbHtml)
                        ->set('useractivation', $usersConfig->get('useractivation'))
                        ->set('return', $uri)
                        ->set('settings', $settings)
                        ->fetch('frontpage/guest');
                } else {
                    $headerHTML = '';
                }
            }

            /* Get site members count */
            $totalMembers = $model->getMembersCount();

            $latestActivitiesData = $this->showLatestActivities();
            $latestActivitiesHTML = $latestActivitiesData['HTML'];

            $tmpl = new CTemplate();
            $tmpl
                    ->set('totalMembers', $totalMembers)
                    ->set('my', $my)
                    ->set('alreadyLogin', $alreadyLogin)
                    ->set('header', $headerHTML)
                    ->set('userActivities', $latestActivitiesHTML)
                    ->set('config', $config)
                    ->set('customActivityHTML', $this->getCustomActivityHTML());

            $status = new CUserStatus();

            if ($my->authorise('community.view', 'frontpage.statusbox')) {
                // Add default status box

                CUserHelper::addDefaultStatusCreator($status);

                if (COwnerHelper::isCommunityAdmin() && $config->get('custom_activity')) {
                    $template = new CTemplate();
                    $template->set('customActivities', CActivityStream::getCustomActivities());

                    $creator = new CUserStatusCreator('custom');
                    $creator->title = JText::_('COM_COMMUNITY_CUSTOM');
                    $creator->html = $template->fetch('status.custom');

                    $status->addCreator($creator);
                }
            }

            /**
             * Misc variables
             * @since 3.3
             * Move out variable init in side template into view
             */
            $moduleCount =  count(JModuleHelper::getModules('js_side_frontpage')) + count(JModuleHelper::getModules('js_side_top')) +
                            count(JModuleHelper::getModules('js_side_bottom')) + count(JModuleHelper::getModules('js_side_frontpage_top')) +
                            count(JModuleHelper::getModules('js_side_frontpage_bottom')) + count(JModuleHelper::getModules('js_side_frontpage_stacked')) +
                            count(JModuleHelper::getModules('js_side_top_stacked')) + count(JModuleHelper::getModules('js_side_bottom_stacked')) +
                            count(JModuleHelper::getModules('js_side_frontpage_top_stacked')) + count(JModuleHelper::getModules('js_side_frontpage_bottom_stacked'));

            $jinput = JFactory::getApplication()->input;
            /**
             * @todo 3.3
             * All of these code must be provided in object. DO NOT PUT ANY CODE LOGIC HERE !
             */
            $cconfig = CFactory::getConfig();
            $filter = $jinput->get('filter');
            $filterValue = $jinput->get('value', 'default_value', 'RAW');
            $filterText = JText::_("COM_COMMUNITY_FILTERBAR_ALL");
            $filterHashtag = false;
            $filterKeyword = false;
            if ($filter == 'apps') {
                switch ($filterValue) {
                    case 'profile':
                        $filterText = JText::_("COM_COMMUNITY_FILTERBAR_TYPE_STATUS");
                        break;
                    case 'photo':
                        $filterText = JText::_("COM_COMMUNITY_FILTERBAR_TYPE_PHOTO");
                        break;
                    case 'video':
                        $filterText = JText::_("COM_COMMUNITY_FILTERBAR_TYPE_VIDEO");
                        break;
                    case 'group':
                        $filterText = JText::_("COM_COMMUNITY_FILTERBAR_TYPE_GROUP");
                        break;
                    case 'event':
                        $filterText = JText::_("COM_COMMUNITY_FILTERBAR_TYPE_EVENT");
                        break;
                }
            } else if ($filter == 'hashtag') {
                $filterText = JText::_("COM_COMMUNITY_FILTERBAR_TYPE_HASHTAG") . ' #' . $filterValue;
                $filterHashtag = true;
            } else if ($filter == 'keyword') {
                $filterText = JText::_("COM_COMMUNITY_FILTERBAR_TYPE_KEYWORD") . ' ' . $filterValue;
                $filterKeyword = true;
            } else {
                switch ($filterValue) {
                    case 'me-and-friends':
                        $filterText = JText::_("COM_COMMUNITY_FILTERBAR_RELATIONSHIP_ME_AND_FRIENDS");
                        break;
                }
            }

            echo $tmpl
                    ->set('userstatus', $status)
                    ->set('moduleCount', $moduleCount)
                    ->set('class', ($moduleCount > 0) ? 'span8' : 'span12')
                    ->set('filterKey', $filter)
                    ->set('filter', $filter)
                    ->set('filterText', $filterText)
                    ->set('filterHashtag', $filterHashtag)
                    ->set('filterKeyword', $filterKeyword)
                    ->set('filterValue', $filterValue)
                    ->fetch('frontpage/base');
        }

        /**
         *
         * @return string
         */
        public function getCustomActivityHTML() {
            $tmpl = new CTemplate();
            return $tmpl
                            ->set('isCommunityAdmin', COwnerHelper::isCommunityAdmin())
                            ->set('customActivities', CActivityStream::getCustomActivities())
                            ->fetch('custom.activity');
        }

        /**
         * Get latest activities with HTML to render
         * @return array
         */
        public function showLatestActivities() {
            $config = CFactory::getConfig();
            $my = CFactory::getUser();
            $jinput = JFactory::getApplication()->input;

            /* We do store filters into session than we can reuse it under ajax */
            $defaultFilter = $my->_cparams->get('frontpageactivitydefault', $config->get('frontpageactivitydefault'));
            $filter = $jinput->get('filter', '', 'RAW');
            $value = $jinput->get('value','','RAW');

            if ( ( empty($filter) || strpos($filter, ':') !== false ) && $my->id != 0 ) {
                //filter overide, if user has set their own filter, it should rewrite the system default filter
                $myFilter = $my->_cparams->get('frontpageactivitydefault', 'all');
                if($my->id && $myFilter != ''){
                    $filter = $myFilter == 'all' ? $defaultFilter : $myFilter;
                }

                if(!is_array($filter)){
                    //break this filter down if needed
                    $filter = explode(':',$filter);
                }

                if($filter[0] != $defaultFilter){ //prevent unlimited loop if both are set to "all"
                    JFactory::getApplication()->redirect(CRoute::_('index.php?option=com_community&view=frontpage&filter=' . $filter[0] . '&value=' . $filter[1], false));
                }
            }
            $userActivities = '';

            /* Filtering */
            switch ($filter) {
                /* Filter by privacy */
                case 'privacy':
                    /* Filter by me and my friends */
                    if ($value == 'me-and-friends' && $my->id != 0) {
                        /**
                         *
                         * @param type $filter
                         * @param type $userId
                         * @param type $view
                         * @param type $showMore
                         */
                        $userActivities = CActivities::getActivitiesByFilter('active-user-and-friends', $my->id, 'frontpage', true);
                    } else {
                        /* No filter. Get all */
                        $userActivities = CActivities::getActivitiesByFilter('all', $my->id, 'frontpage', true);
                    }
                    break;
                /* Filter by type */
                case 'apps':
                    /* By default we use all */
                    $userActivities = CActivities::getActivitiesByFilter('all', $my->id, 'frontpage', true, array('apps' => array($value)));
                    break;
                /* By default we do filter by privacy and follow backend configured */
                case 'hashtag':
                    //filter by hashtag
                    $userActivities = CActivities::getActivitiesByFilter('all', $my->id, 'frontpage', true, array($filter => $value));
                    break;
                case 'keyword':
                    //filter by keyword
                    $userActivities = CActivities::getActivitiesByFilter('all', $my->id, 'frontpage', true, array($filter => $value));
                    break;
                default:
                    $defaultFilter = $config->get('frontpageactivitydefault');
                    /* Filter by me and my friends and of course not for guess */
                    if ($defaultFilter == 'friends' && $my->id != 0) {
                        $userActivities = CActivities::getActivitiesByFilter('active-user-and-friends', $my->id, 'frontpage', true);
                    } else {
                        $userActivities = CActivities::getActivitiesByFilter('all', $my->id, 'frontpage', true, array('show_featured'=>true));
                    }
                    break;
            }

            $activities = array();
            $activities['HTML'] = $userActivities;

            return $activities;
        }

        public function showFeaturedEvents($total = 5) {
            $session = JFactory::getSession();
            $html = ''; //$session->get('frontpage_events');
            if (!$html) {


                $tmpl = new CTemplate();
                $frontpage_latest_events = intval($tmpl->params->get('frontpage_latest_events'));
                $html = '';
                $data = array();

                if ($frontpage_latest_events != 0) {
                    $model = CFactory::getModel('Events');
                    $result = $model->getEvents(null, null, null, null, true, false, null, null, CEventHelper::ALL_TYPES, 0, $total);

                    $events = array();
                    $eventView = CFactory::getView('events');
                    $events = $eventView->_getEventsFeaturedList();

                    $tmpl = new CTemplate();
                    $tmpl->set('events', $events);

                    $html = $tmpl->fetch('frontpage.latestevents');
                }
            }
            $session->set('frontpage_events', $html);
            $data['HTML'] = $html;
            return $data;
        }

        public function showFeaturedGroups($total = 5) {
            $tmpl = new CTemplate();
            $config = CFactory::getConfig();
            $showlatestgroups = intval($tmpl->params->get('showlatestgroups'));
            $html = '';
            $data = array();

            if ($showlatestgroups != 0) {
                $groupModel = CFactory::getModel('groups');
                $tmpGroups = $groupModel->getAllGroups(null, null, null, $total);
                $groups = array();

                $data = array();
                $groupView = CFactory::getView('groups');
                $groups = $groupView->getGroupsFeaturedList();

                $tmpl = new CTemplate();
                $html = $tmpl->setRef('groups', $groups)
                        ->fetch('frontpage.latestgroup');
            }

            $data['HTML'] = $html;

            return $data;
        }

        public function getMembersHTML($data) {
            if (empty($data))
                return '';

            $members = array_slice($data['members'], 0, $data['limit']);
            //$limit = $data['limit'];

            $tmpl = new CTemplate();
            echo $tmpl->set('members', $members)
                    ->fetch('frontpage.latestmember.list');
        }

    }

}
