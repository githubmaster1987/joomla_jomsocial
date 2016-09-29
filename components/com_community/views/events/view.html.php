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

jimport('joomla.application.component.view');

if (!class_exists("CommunityViewEvents")) {

    class CommunityViewEvents extends CommunityView
    {

        /**
         *
         */
        public function _addSubmenu()
        {
            //CFactory::load( 'helpers' , 'event' );
            $mainframe = JFactory::getApplication();
            $jinput = $mainframe->input;
            $id = $jinput->request->get('eventid', '', 'INT');
            $event = JTable::getInstance('Event', 'CTable');
            $event->load($id);

            CEventHelper::getHandler($event)->addSubmenus($this);
        }

        /**
         *
         */
        public function showSubmenu($display=true)
        {
            $this->_addSubmenu();
            return parent::showSubmenu($display);
        }

        /**
         * Application full view
         * @return type
         */
        public function appFullView()
        {
            $document = JFactory::getDocument();
            $mainframe = JFactory::getApplication();
            $jinput = $mainframe->input;

            $this->showSubmenu();

            $applicationName = JString::strtolower($jinput->get->get('app', '', 'STRING'));

            if (empty($applicationName)) {
                JFactory::getApplication()->enqueueMessage(JText::_('COM_COMMUNITY_APP_ID_REQUIRED'), 'error');
            }

            if (!$this->accessAllowed('registered')) {
                return;
            }

            $output = '';

            //@todo: Since group walls doesn't use application yet, we process it manually now.
            if ($applicationName == 'walls') {
                //CFactory::load( 'libraries' , 'wall' );
                //$jConfig	= JFactory::getConfig();
                $limit = $jinput->request->getInt('limit', 5);
                $limitstart = $jinput->request->getInt('limitstart', 0);
                $eventId = $jinput->request->getInt('eventid');
                $my = CFactory::getUser();
                $config = CFactory::getConfig();

                $eventsModel = CFactory::getModel('Events');
                $event = JTable::getInstance('Event', 'CTable');
                $event->load($eventId);
                $config = CFactory::getConfig();

                /**
                 * Opengraph
                 */
                CHeadHelper::setType('website', JText::sprintf('COM_COMMUNITY_EVENTS_WALL_TITLE', $event->title));

                $guest = $event->isMember($my->id);
                $waitingApproval = $event->isPendingApproval($my->id);
                $status = $event->getUserStatus($my->id, 'events');
                $responded = (($status == COMMUNITY_EVENT_STATUS_ATTEND) || ($status == COMMUNITY_EVENT_STATUS_WONTATTEND) || ($status == COMMUNITY_EVENT_STATUS_MAYBE));

                if (!$config->get('lockeventwalls') || ($config->get(
                            'lockeventwalls'
                        ) && ($guest) && !($waitingApproval) && $responded) || COwnerHelper::isCommunityAdmin()
                ) {

                    // Get the walls content
                    $output .= '<div id="wallContent">';
                    $output .= CWallLibrary::getWallContents(
                        'events',
                        $event->id,
                        $event->isAdmin($my->id),
                        $limit,
                        $limitstart,
                        'wall/content',
                        'events,events'
                    );
                    $output .= '</div>';

                    $output .= CWallLibrary::getWallInputForm(
                        $event->id,
                        'events,ajaxSaveWall',
                        'events,ajaxRemoveWall'
                    );

                    jimport('joomla.html.pagination');
                    $wallModel = CFactory::getModel('wall');
                    $pagination = new JPagination($wallModel->getCount($event->id, 'events'), $limitstart, $limit);

                    $output .= '<div class="cPagination">' . $pagination->getPagesLinks() . '</div>';
                }
            } else {
                //CFactory::load( 'libraries' , 'apps' );
                $model = CFactory::getModel('apps');
                $applications = CAppPlugins::getInstance();
                $applicationId = $model->getUserApplicationId($applicationName);

                $application = $applications->get($applicationName, $applicationId);

                if (!$application) {
                    JFactory::getApplication()->enqueueMessage(JText::_('COM_COMMUNITY_APPS_NOT_FOUND'), 'error');
                }

                // Get the parameters
                $manifest = CPluginHelper::getPluginPath(
                        'community',
                        $applicationName
                    ) . '/' . $applicationName . '/' . $applicationName . '.xml';

                $params = new CParameter($model->getUserAppParams($applicationId), $manifest);

                $application->params = $params;
                $application->id = $applicationId;

                $output = $application->onAppDisplay($params);
            }

            echo $output;
        }

        /**
         *
         * @param type $tpl
         * @return type
         */
        public function display($tpl = null)
        {
            $mainframe = JFactory::getApplication();
            $jinput = $mainframe->input;

            $document = JFactory::getDocument();
            $config = CFactory::getConfig();
            $my = CFactory::getUser();

            $groupId = $jinput->get('groupid', '', 'INT');
            $eventparent = $jinput->get('parent', '', 'INT');

            if (!empty($groupId)) {
                $group = JTable::getInstance('Group', 'CTable');
                $group->load($groupId);

                // @rule: Test if the group is unpublished, don't display it at all.
                if (!$group->published) {
                    echo JText::_('COM_COMMUNITY_GROUPS_UNPUBLISH_WARNING');
                    return;
                }

                if ($group->isPrivate() && !$group->isMember($my->id)) {
                    $tmpl = new CTemplate();
                    echo $tmpl->fetch('events/missingevent');
                    return;
                }

                // Set pathway for group videos
                // Community > Groups > Group Name > Events
                $this->addPathway(
                    JText::_('COM_COMMUNITY_GROUPS'),
                    CRoute::_('index.php?option=com_community&view=groups')
                );
                $this->addPathway(
                    $group->name,
                    CRoute::_('index.php?option=com_community&view=groups&task=viewgroup&groupid=' . $groupId)
                );
            }

            //page title
            $this->addPathway(
                JText::_('COM_COMMUNITY_EVENTS'),
                CRoute::_('index.php?option=com_community&view=events')
            );

            // Get category id from the query string if there are any.
            $categoryId = $jinput->get('categoryid', 0, 'STRING'); //string because it might contain featured_only
            $limitstart = $jinput->get('limitstart', 0, 'INT');

            $showFeaturedOnly = false;
            if(!is_numeric($categoryId) && $categoryId == 'featured_only'){
                $categoryId = 0;
                $showFeaturedOnly = true;
            }

            $category = JTable::getInstance('EventCategory', 'CTable');
            $category->load($categoryId);

            if($groupId){
                CHeadHelper::setType('website', JText::_('COM_COMMUNITY_GROUP_EVENT'));
            }else if (isset($category) && $category->id != 0) {
                $title = JText::sprintf(
                    'COM_COMMUNITY_EVENTS_CATEGORY_NAME',
                    str_replace('&amp;', '&', JText::_($this->escape($category->name)))
                );
                CHeadHelper::setType('website', $title);
            } else {
                CHeadHelper::setType('website', JText::_('COM_COMMUNITY_EVENTS'));
            }

            $feedLink = CRoute::_('index.php?option=com_community&view=events&format=feed');
            $feed = '<link rel="alternate" type="application/rss+xml" title="' . JText::_(
                    'COM_COMMUNITY_SUBSCRIBE_ALL_EVENTS_FEED'
                ) . '" href="' . $feedLink . '"/>';
            $document->addCustomTag($feed);

            $data = new stdClass();
            $sorted = $jinput->get(
                'sort',
                'startdate',
                'STRING'
            );

            /* begin: UNLIMITED LEVEL BREADCRUMBS PROCESSING */
            if ($category->parent == COMMUNITY_NO_PARENT) {
                $this->addPathway(
                    JText::_($this->escape($category->name)),
                    CRoute::_('index.php?option=com_community&view=events&task=display&categoryid=' . $category->id)
                );
            } else {
                // Parent Category
                $parentsInArray = array();
                $n = 0;
                $parentId = $category->id;

                $parent = JTable::getInstance('EventCategory', 'CTable');

                do {
                    $parent->load($parentId);
                    $parentId = $parent->parent;

                    $parentsInArray[$n]['id'] = $parent->id;
                    $parentsInArray[$n]['parent'] = $parent->parent;
                    $parentsInArray[$n]['name'] = $parent->name;

                    $n++;
                } while ($parent->parent > COMMUNITY_NO_PARENT);

                for ($i = count($parentsInArray) - 1; $i >= 0; $i--) {
                    $this->addPathway(
                        $parentsInArray[$i]['name'],
                        CRoute::_(
                            'index.php?option=com_community&view=events&task=display&categoryid=' . $parentsInArray[$i]['id']
                        )
                    );
                }
            }
            /* end: UNLIMITED LEVEL BREADCRUMBS PROCESSING */

            $data->categories = $this->_cachedCall(
                '_getEventsCategories',
                array($category->id),
                '',
                array(COMMUNITY_CACHE_TAG_EVENTS_CAT)
            );

            $model = CFactory::getModel('events');

            // Get event in category and it's children.
            $categories = $model->getAllCategories();
            $categoryIds = CCategoryHelper::getCategoryChilds($categories, $category->id);
            if ($category->id > 0) {
                $categoryIds[] = (int)$category->id;
            }

            //CFactory::load( 'helpers' , 'event' );
            $event = JTable::getInstance('Event', 'CTable');
            $handler = CEventHelper::getHandler($event);

            // It is safe to pass 0 as the category id as the model itself checks for this value.
            $data->events = $model->getEvents(
                $categoryIds,
                null,
                $sorted,
                null,
                true,
                false,
                null,
                array('parent' => $eventparent),
                ($showFeaturedOnly) ? 'featured_only' : $handler->getContentTypes(),
                $handler->getContentId()
            );

            // Get pagination object
            $data->pagination = $model->getPagination();

            $eventsHTML = $this->_cachedCall(
                '_getEventsHTML',
                array($data->events, false, $data->pagination),
                '',
                array(COMMUNITY_CACHE_TAG_EVENTS)
            );
            //Cache Group Featured List
            $featuredEvents = $this->_cachedCall(
                'getEventsFeaturedList',
                array(),
                '',
                array(COMMUNITY_CACHE_TAG_FEATURED)
            );
            $featuredHTML = $featuredEvents['HTML'];

            //no Featured Event headline slideshow on Category filtered page
            if (!empty($categoryId)) {
                $featuredHTML = '';
            }

            $sortItems = array(
                //'latest' => JText::_('COM_COMMUNITY_EVENTS_SORT_CREATED'),
                'startdate' => JText::_('COM_COMMUNITY_EVENTS_SORT_COMING')
            );

            $title = JText::_('COM_COMMUNITY_EVENTS');
            if($groupId) {
                $title= JText::_('COM_COMMUNITY_GROUP_EVENTS');
            }

            $config = CFactory::getConfig();

            $canSearch= (
                (!$config->get('enableguestsearchevents') && COwnerHelper::isRegisteredUser()  )
                || $config->get('enableguestsearchevents') ) ? true :false;

            $tmpl = new CTemplate();
            $tmpl->set('handler', $handler)
                ->set('canSearch', $canSearch)
                ->set('pageTitle', $title)
                ->set('featuredHTML', $featuredHTML)
                ->set('index', true)
                ->set('categories', $data->categories)
                ->set('eventsHTML', $eventsHTML)
                ->set('isCommunityAdmin', COwnerHelper::isCommunityAdmin())
                ->set('sortings', CFilterBar::getHTML(CRoute::getURI(), $sortItems, 'startdate'))
                ->set('my', $my)
                ->set('isGroup', ($groupId) ? $groupId : false )
                ->set('submenu', $this->showSubmenu(false))
                ->set('createLink', ($groupId) ? CRoute::_('index.php?option=com_community&view=events&groupid='.$groupId.'&task=create') : CRoute::_('index.php?option=com_community&view=events&task=create') );

                if($groupId) {
                    $tmpl->set('canCreate', $my->authorise('community.create', 'groups.events.' . $groupId))
                         ->set('groupMiniHeader', CMiniHeader::showGroupMiniHeader($groupId));
                } else {
                    $tmpl->set('canCreate', $my->authorise('community.create', 'events'));
                }

                echo $tmpl->fetch('events/base');
        }

        /**
         * List All FEATURED EVENTS
         * @ since 2.4
         * */
        public function getEventsFeaturedList()
        {
            $featEvents = $this->_getEventsFeaturedList();

            if ($featEvents) {
                $featuredHTML['HTML'] = $this->_getFeatHTML($featEvents);
            } else {
                $featuredHTML['HTML'] = null;
            }

            return $featuredHTML;
        }

        /**
         *    Generate Featured Events HTML
         *
         * @param        array    Array of events objects
         * @return        string    HTML
         * @since        2.4
         */
        private function _getFeatHTML($events)
        {
            //CFactory::load( 'helpers' , 'owner' );
            //CFactory::load( 'libraries', 'events' );
            $my = CFactory::getUser();
            $config = CFactory::getConfig();
            $event = JTable::getInstance('Event', 'CTable');
            // Get the formated date & time
            $format = ($config->get('eventshowampm')) ? JText::_('COM_COMMUNITY_EVENTS_TIME_FORMAT_12HR') : JText::_(
                'COM_COMMUNITY_EVENTS_TIME_FORMAT_24HR'
            );

            $startDate = $event->getStartDate(false);
            $endDate = $event->getEndDate(false);
            $allday = false;

            if (($startDate->format('%Y-%m-%d') == $endDate->format('%Y-%m-%d')) && $startDate->format(
                    '%H:%M:%S'
                ) == '00:00:00' && $endDate->format('%H:%M:%S') == '23:59:59'
            ) {
                $format = JText::_('COM_COMMUNITY_EVENT_TIME_FORMAT_LC1');
                $allday = true;
            }

            $tmpl = new CTemplate();
            return $tmpl->set('events', $events)
                ->set('showFeatured', $config->get('show_featured'))
                ->set('isCommunityAdmin', COwnerHelper::isCommunityAdmin())
                ->set('my', $my)
                ->set('allday', $allday)
                ->fetch('events.featured');
        }

        /**
         * Display invite form
         * */
        public function invitefriends()
        {
            CHeadHelper::setType('website', JText::_('COM_COMMUNITY_EVENTS_INVITE_FRIENDS_TO_EVENT_TITLE'));

            if (!$this->accessAllowed('registered')) {
                return;
            }

            $this->showSubmenu();

            $mainframe = JFactory::getApplication();
            $jinput = $mainframe->input;
            $my = CFactory::getUser();
            $eventId = $jinput->get->get('eventid', '', 'INT');
            $this->_addEventInPathway($eventId);
            $this->addPathway(JText::_('COM_COMMUNITY_EVENTS_INVITE_FRIENDS_TO_EVENT_TITLE'));

            $friendsModel = CFactory::getModel('Friends');
            $model = CFactory::getModel('Events');
            $event = JTable::getInstance('Event', 'CTable');
            $event->load($eventId);

            $tmpFriends = $friendsModel->getFriends($my->id, 'name', false);

            $friends = array();

            for ($i = 0; $i < count($tmpFriends); $i++) {
                $friend = $tmpFriends[$i];
                $eventMember = JTable::getInstance('EventMembers', 'CTable');
                $keys = array('eventId' => $eventId, 'memberId' => $friend->id);
                $eventMember->load($keys);


                if (!$event->isMember($friend->id) && !$eventMember->exists()) {
                    $friends[] = $friend;
                }
            }
            unset($tmpFriends);

            $tmpl = new CTemplate();
            echo $tmpl->set('friends', $friends)
                ->set('event', $event)
                ->fetch('events.invitefriends');
        }

        public function pastevents()
        {
            $mainframe = JFactory::getApplication();
            $jinput = $mainframe->input;
            $document = JFactory::getDocument();
            $config = CFactory::getConfig();
            $my = CFactory::getUser();

            $groupId = $jinput->get->get('groupid', '', 'INT');
            if (!empty($groupId)) {
                $group = JTable::getInstance('Group', 'CTable');
                $group->load($groupId);

                // Set pathway for group videos
                // Community > Groups > Group Name > Events
                $this->addPathway(
                    JText::_('COM_COMMUNITY_GROUPS'),
                    CRoute::_('index.php?option=com_community&view=groups')
                );
                $this->addPathway(
                    $group->name,
                    CRoute::_('index.php?option=com_community&view=groups&task=viewgroup&groupid=' . $groupId)
                );
            } else {
                $this->addPathway(
                    JText::_('COM_COMMUNITY_EVENTS'),
                    CRoute::_('index.php?option=com_community&view=events')
                );
                $this->addPathway(JText::_('COM_COMMUNITY_EVENTS_PAST_TITLE'), '');
            }

            CHeadHelper::setType('website', JText::_('COM_COMMUNITY_EVENTS_PAST_TITLE'));

            $feedLink = CRoute::_('index.php?option=com_community&view=events&task=pastevents&format=feed');
            $feed = '<link rel="alternate" type="application/rss+xml" title="' . JText::_(
                    'COM_COMMUNITY_SUBSCRIBE_EXPIRED_EVENTS_FEED'
                ) . '"  href="' . $feedLink . '"/>';
            $document->addCustomTag($feed);

            // loading neccessary files here.
            //CFactory::load( 'libraries' , 'filterbar' );
            //CFactory::load( 'helpers' , 'event' );
            //CFactory::load( 'helpers' , 'owner' );
            //CFactory::load( 'models' , 'events');
            //$event		= JTable::getInstance( 'Event' , 'CTable' );

            $data = new stdClass();
            $sorted = $jinput->get->get('sort', 'latest', 'STRING');
            $model = CFactory::getModel('events');

            //CFactory::load( 'helpers' , 'event' );
            $event = JTable::getInstance('Event', 'CTable');
            $handler = CEventHelper::getHandler($event);

            // It is safe to pass 0 as the category id as the model itself checks for this value.
            $data->events = $model->getEvents(
                null,
                null,
                $sorted,
                null,
                false,
                true,
                null,
                null,
                $handler->getContentTypes(),
                $handler->getContentId()
            );

            // Get pagination object
            $data->pagination = $model->getPagination();

            // Get the template for the group lists
            $eventsHTML = $this->_cachedCall(
                '_getEventsHTML',
                array($data->events, true, $data->pagination),
                '',
                array(COMMUNITY_CACHE_TAG_EVENTS)
            );

            $sortItems = array(
                'latest' => JText::_('COM_COMMUNITY_EVENTS_SORT_CREATED'),
                'startdate' => JText::_('COM_COMMUNITY_EVENTS_SORT_START_DATE')
            );

            $title = JText::_('COM_COMMUNITY_EVENTS_PAST_TITLE');
            if($groupId) {
                $title= JText::_('COM_COMMUNITY_EVENTS_PAST_GROUP_TITLE');
            }

            $config = CFactory::getConfig();

            $canSearch= (
                (!$config->get('enableguestsearchevents') && COwnerHelper::isRegisteredUser()  )
                || $config->get('enableguestsearchevents') ) ? true :false;



            $tmpl = new CTemplate();
            $tmpl->set('eventsHTML', $eventsHTML)
                ->set('canSearch', $canSearch)
                ->set('pageTitle', $title)
                ->set('config', $config)
                ->set('isCommunityAdmin', COwnerHelper::isCommunityAdmin())
                ->set('sortings', CFilterBar::getHTML(CRoute::getURI(), $sortItems, 'startdate'))
                ->set('groupMiniHeader', ($groupId) ? CMiniHeader::showGroupMiniHeader($groupId) : false)
                ->set('my', $my)
                ->set('submenu', $this->showSubmenu(false))
                ->set('createLink', ($groupId) ? CRoute::_('index.php?option=com_community&view=events&groupid='.$groupId.'&task=create') : CRoute::_('index.php?option=com_community&view=events&task=create') )
            ->set('ispastevents', true);

                if($groupId) {
                    $tmpl->set('canCreate', $my->authorise('community.create', 'groups.events.' . $groupId));
                } else {
                    $tmpl->set('canCreate', $my->authorise('community.create', 'events'));
                }

                echo $tmpl->fetch('events/base');
        }

        /*
         * @since 2.4
         * To retrieve nearby events
         */

        public function modEventNearby()
        {
            return $this->_getNearbyEvent();
        }

        /*
         * @since 2.4
         */

        public function _getNearbyEvent()
        {
            $tmpl = new CTemplate();
            echo $tmpl->fetch('events.nearbysearch');
        }

        /*
         * @since 3.0
         * To get event category
         */

        public function modEventCategories($category, $categories)
        {
            return $this->_getEventCategories($category, $categories);
        }

        /*
         * @since 3.0
         */

        public function _getEventCategories($category, $categories)
        {
            $tmpl = new CTemplate();
            echo $tmpl->set('category', $category)
                ->set('categories', $categories)
                ->fetch('modules/events/categories');
        }

        /*
         * @since 2.4
         * To retrieve events on calendar
         */

        public function modEventCalendar()
        {
            return $this->_getEventCalendar();
        }

        /*
         * @since 2.4
         */

        private function _getEventCalendar()
        {
            $tmpl = new CTemplate();
            $mainframe = JFactory::getApplication();
            $jinput = $mainframe->input;

            //@since 2.6 if there is group id assigned, only display group's events.
            $gid = $jinput->request->get('groupid', '', 'INT'); //only display

            echo $tmpl->set('group_id', $gid)
                ->fetch('events.eventcalendar');
        }

        /*
         * @since 2.4
         * To retrieve event pending list
         */

        public function modEventPendingList()
        {
            $my = CFactory::getUser();
            return $this->_getPendingListHTML($my);
        }

        /**
         * Main events page display
         * @return type
         */
        public function myevents()
        {
            //if (!$this->accessAllowed('registered')) {
            //return;
            //}

            $mainframe = JFactory::getApplication();
            $jinput = $mainframe->input;
            $document = JFactory::getDocument();
            $config = CFactory::getConfig();
            $my = CFactory::getUser();
            $userid = $jinput->get('userid', $my->id, 'INT');
            $currentUser = CFactory::getUser($userid);
            $groupId = $jinput->get->get('groupid', '', 'INT');

            $this->addPathway(
                JText::_('COM_COMMUNITY_EVENTS'),
                CRoute::_('index.php?option=com_community&view=events')
            );
            $this->addPathway(JText::sprintf('COM_COMMUNITY_USER_EVENTS', $currentUser->getDisplayName()), '');

            /**
             * Opengraph
             */
            CHeadHelper::setType(
                'website',
                JText::sprintf('COM_COMMUNITY_USER_EVENTS', $currentUser->getDisplayName())
            );

            $feedLink = CRoute::_('index.php?option=com_community&view=events&userid=' . $userid . '&format=feed');
            $feed = '<link rel="alternate" type="application/rss+xml" title="' . JText::_(
                    'COM_COMMUNITY_SUBSCRIBE_MY_EVENTS_FEED'
                ) . '" href="' . $feedLink . '"/>';
            $document->addCustomTag($feed);

            $data = new stdClass();
            $sorted = $jinput->get->get(
                'sort',
                'startdate',
                'STRING'
            );
            $model = CFactory::getModel('events');

            // It is safe to pass 0 as the category id as the model itself checks for this value.
            $data->events = $model->getEvents(null, $userid, $sorted);
            // Get pagination object
            $data->pagination = $model->getPagination();

            // Get the template for the group lists
            $eventsHTML = $this->_cachedCall(
                '_getEventsHTML',
                array($data->events, false, $data->pagination),
                '',
                array(COMMUNITY_CACHE_TAG_EVENTS)
            );

            $tmpl = new CTemplate();

            $sortItems = array(
                'latest' => JText::_('COM_COMMUNITY_EVENTS_SORT_CREATED'),
                'startdate' => JText::_('COM_COMMUNITY_EVENTS_SORT_COMING')
            );
            $title = JText::_('COM_COMMUNITY_EVENTS_MINE');
            if($groupId) {
                $title= JText::_('COM_COMMUNITY_EVENT_GROUP_MINE');
            }elseif($userid != $my->id){
                $title = JText::sprintf('COM_COMMUNITY_USER_EVENTS',CFactory::getUser($userid)->getDisplayName());
            }

            $config = CFactory::getConfig();

            $canSearch= (
                (!$config->get('enableguestsearchevents') && COwnerHelper::isRegisteredUser()  )
                || $config->get('enableguestsearchevents') ) ? true :false;


            $tmpl->set('eventsHTML', $eventsHTML)
                ->set('canSearch', $canSearch)
                ->set('pageTitle', $title)
                ->set('config', $config)
                ->set('isCommunityAdmin', COwnerHelper::isCommunityAdmin())
                ->set('sortings', CFilterBar::getHTML(CRoute::getURI(), $sortItems, 'startdate'))
                ->set('submenu', $this->showSubmenu(false))
                ->set('createLink', ($groupId) ? CRoute::_('index.php?option=com_community&view=events&groupid='.$groupId.'&task=create') : CRoute::_('index.php?option=com_community&view=events&task=create') )
            ->set('my', $my);

            if($userid == $my->id)
            {
                $tmpl->set('canCreate', $my->authorise('community.create', 'events'));
            }
            else
            {
                $tmpl->set('canCreate', 0);
            }

            echo $tmpl->fetch('events/base');
        }

        public function myinvites()
        {
            if (!$this->accessAllowed('registered')) {
                return;
            }

            $mainframe = JFactory::getApplication();
            $jinput = $mainframe->input;
            $document = JFactory::getDocument();
            $config = CFactory::getConfig();
            $my = CFactory::getUser();
            $userid = $jinput->get('userid');

            $this->addPathway(
                JText::_('COM_COMMUNITY_EVENTS'),
                CRoute::_('index.php?option=com_community&view=events')
            );
            $this->addPathway(JText::_('COM_COMMUNITY_EVENTS_PENDING_INVITATIONS'), '');

            /**
             * Opengraph
             */
            CHeadHelper::setType('website', JText::_('COM_COMMUNITY_EVENTS_PENDING_INVITATIONS'));

            $feedLink = CRoute::_('index.php?option=com_community&view=events&userid=' . $userid . '&format=feed');
            $feed = '<link rel="alternate" type="application/rss+xml" title="' . JText::_(
                    'COM_COMMUNITY_SUBSCRIBE_TO_PENDING_INVITATIONS_FEED'
                ) . '"  href="' . $feedLink . '"/>';
            $document->addCustomTag($feed);


            //CFactory::load( 'libraries' , 'filterbar' );
            //CFactory::load( 'helpers' , 'event' );
            //CFactory::load( 'helpers' , 'owner' );
            //CFactory::load( 'models' , 'events');

            $sorted = $jinput->get->get(
                'sort',
                'startdate',
                'STRING'
            );
            $model = CFactory::getModel('events');
            $pending = COMMUNITY_EVENT_STATUS_INVITED;

            // It is safe to pass 0 as the category id as the model itself checks for this value.
            $rows = $model->getEvents(null, $my->id, $sorted, null, true, false, $pending);
            $pagination = $model->getPagination();
            $count = count($rows);
            $sortItems = array(
                'latest' => JText::_('COM_COMMUNITY_EVENTS_SORT_CREATED'),
                'startdate' => JText::_('COM_COMMUNITY_EVENTS_SORT_COMING')
            );

            $events = array();

            if ($rows) {
                foreach ($rows as $row) {
                    $event = JTable::getInstance('Event', 'CTable');
                    $event->bind($row);
                    $events[] = $event;
                }
                unset($eventObjs);
            }

            $tmpl = new CTemplate();
            $title = JText::_('COM_COMMUNITY_EVENTS_PENDING_INVITATIONS');

            echo $tmpl->set('events', $events)
                ->set('pageTitle', $title)
                ->set('pagination', $pagination)
                ->set('config', $config)
                ->set('isCommunityAdmin', COwnerHelper::isCommunityAdmin())
                ->set('sortings', CFilterBar::getHTML(CRoute::getURI(), $sortItems, 'startdate'))
                ->set('my', $my)
                ->set('count', $count)
                ->set('submenu', $this->showSubmenu(false))
                ->fetch('events.myinvites');
        }

        /**
         * Method to display the create / edit event's form.
         * Both views share the same template file.
         * */
        public function _displayForm($event)
        {
            $mainframe = JFactory::getApplication();
            $jinput = $mainframe->input;
            $my = CFactory::getUser();
            $config = CFactory::getConfig();
            $model = CFactory::getModel('events');
            $categories = $model->getCategories();
            $now = JDate::getInstance();
            $groupid = $jinput->get('groupid', '', 'INT');
            $task = $jinput->get('task');

            //J1.6 returns timezone as string, not integer offset.

            $systemOffset = $mainframe->get('offset');
            //$systemOffset = $systemOffset->getOffsetFromGMT(true);


            $editorType = ($config->get('allowhtml')) ? $config->get('htmleditor', 'none') : 'none';

            $editor = new CEditor($editorType);
            $totalEventCount = $model->getEventsCreationCount($my->id);

            if ($event->catid == null) {
                $event->catid = $jinput->getInt('categoryid', 0);
            }

            $event->startdatetime = $jinput->post->get('startdatetime', '00:01', 'NONE');
            $event->enddatetime = $jinput->post->get('enddatetime', '23:59', 'NONE');

            $timezones = CTimeHelper::getBeautifyTimezoneList();

            $helper = CEventHelper::getHandler($event);

            $startDate = $event->getStartDate(false);
            $endDate = $event->getEndDate(false);
            $repeatEndDate = $event->getRepeatEndDate();

            $dateSelection = CEventHelper::getDateSelection($startDate, $endDate);

            // Load category tree
            $cTree = CCategoryHelper::getCategories($categories);
            $lists['categoryid'] = CCategoryHelper::getSelectList('events', $cTree, $event->catid, true);

            $app = CAppPlugins::getInstance();
            $appFields = $app->triggerEvent('onFormDisplay', array('createEvent'));
            $beforeFormDisplay = CFormElement::renderElements($appFields, 'before');
            $afterFormDisplay = CFormElement::renderElements($appFields, 'after');

            //to invite group members if this event creation belongs to a group and members is more than 1
            $showGroupMemberInvitation = false;
            if($groupid){
                $group = JTable::getInstance('Group','CTable');
                $group->load($groupid);

                $showGroupMemberInvitation = ($group->getMembersCount() > 1) ? true : false;
            }

            $tmpl = new CTemplate();
            echo $tmpl->set('startDate', $startDate)
                ->set('showGroupMemberInvitation', $showGroupMemberInvitation)
                ->set('endDate', $endDate)
                ->set('enableRepeat', ($my->authorise('community.view', 'events.repeat') && $task != 'edit'))
                ->set('repeatEndDate', $repeatEndDate)
                ->set('startHourSelect', $dateSelection->startHour)
                ->set('endHourSelect', $dateSelection->endHour)
                ->set('startMinSelect', $dateSelection->startMin)
                ->set('endMinSelect', $dateSelection->endMin)
                ->set('startAmPmSelect', $dateSelection->startAmPm)
                ->set('endAmPmSelect', $dateSelection->endAmPm)
                ->set('timezones', $timezones)
                ->set('params', new CParameter($event->params))
                ->set('config', $config)
                ->set('systemOffset', $systemOffset)
                ->set('lists', $lists)
                ->set('categories', $categories)
                ->set('event', $event)
                ->set('editor', $editor)
                ->set('helper', $helper)
                ->set('now', $now->format('%Y-%m-%d'))
                ->set('eventCreated', $totalEventCount)
                ->set('eventcreatelimit', $config->get('eventcreatelimit'))
                ->set('beforeFormDisplay', $beforeFormDisplay)
                ->set('afterFormDisplay', $afterFormDisplay)
                ->fetch('events.forms');
        }

        /**
         * Display the form of the event import and the listing of events users can import
         * from the calendar file.
         * */
        public function import($data)
        {
            $jinput = JFactory::getApplication()->input;
            $events = $data['events'];
            $config = JFactory::getConfig();
            $offset = $config->get('offset');
            if (isset($data['icalParser'])) {
                $parser = $data['icalParser'];
                if (isset($parser->cal['VTIMEZONE']['TZID'])) {
                    $offset = $parser->cal['VTIMEZONE']['TZID'];
                }
            }


            $groupId = $jinput->getInt('groupid', 0);
            $groupLink = $groupId > 0 ? '&groupid=' . $groupId : '';
            $saveImportLink = CRoute::_('index.php?option=com_community&view=events&task=saveImport' . $groupLink);


            if (!$this->accessAllowed('registered')) {
                return;
            }

            //$this->showSubmenu();

            /**
             * Opengraph
             */
            CHeadHelper::setType('website', JText::_('COM_COMMUNITY_EVENTS_IMPORT_ICAL'));

            $model = CFactory::getModel('events');
            $categories = $model->getCategories();

            //CFactory::load( 'helpers' , 'event' );
            $event = JTable::getInstance('Event', 'CTable');

            //CFactory::load( 'helpers' , 'time' );
            $timezones = CTimeHelper::getTimezoneList();

            $offsetValue = new DateTime('now', new DateTimeZone($offset));
            $offsetValue = (int)$offsetValue->format('P');

            $tmpl = new CTemplate();
            echo CMiniHeader::showEventMiniHeader($event->id);
            echo $tmpl->set('events', $events)
                ->set('categories', $categories)
                ->set('timezones', $timezones)
                ->set('offset', $offset)
                ->set('saveimportlink', $saveImportLink)
                ->set('offsetValue', $offsetValue)
                ->set('submenu', $this->showSubmenu(false))
                ->set('pageTitle', ($groupId) ? JText::_('COM_COMMUNITY_EVENTS_IMPORT_GROUP_EVENT') : JText::_('COM_COMMUNITY_IMPORT_EVENTS') )
                ->set('canCreate', CFactory::getUser()->authorise('community.create', 'events'))
                //->set('groupMiniHeader', ($groupId) ? CMiniHeader::showGroupMiniHeader($groupId) : false)
                ->fetch('events.import');
        }

        /**
         * Displays the create event form
         * */
        public function create($event)
        {
            if (!$this->accessAllowed('registered')) {
                return;
            }

            $document = JFactory::getDocument();
            $config = CFactory::getConfig();
            $mainframe = JFactory::getApplication();
            $handler = CEventHelper::getHandler($event);

            if (!$handler->creatable()) {
                $document->setTitle('');
                $mainframe->enqueueMessage(JText::_('COM_COMMUNITY_EVENTS_DISABLE_CREATE'), 'error');
                return;
            }

            $this->addPathway(
                JText::_('COM_COMMUNITY_EVENTS'),
                CRoute::_('index.php?option=com_community&view=events')
            );
            $this->addPathway(JText::_('COM_COMMUNITY_EVENTS_CREATE_TITLE'), '');

            /**
             * Opengraph
             */
            CHeadHelper::setType('website', JText::_('COM_COMMUNITY_EVENTS_CREATE_TITLE'));

            // $js = 'assets/validate-1.5.min.js';
            // CFactory::attach($js, 'js');

            //$this->showSubmenu();
            $this->_displayForm($event);
            return;
        }

        public function edit($event)
        {
            if (!$this->accessAllowed('registered')) {
                return;
            }

            /**
             * Opengraph
             */
            CHeadHelper::setType('website', JText::_('COM_COMMUNITY_EVENTS_EDIT_TITLE'));

            $this->addPathway(
                JText::_('COM_COMMUNITY_EVENTS'),
                CRoute::_('index.php?option=com_community&view=events')
            );
            $this->addPathway(JText::_('COM_COMMUNITY_EVENTS_EDIT_TITLE'), '');

            // $file = 'assets/validate-1.5.min.js';
            // CFactory::attach($file, 'js');


            if (!$this->accessAllowed('registered')) {
                echo JText::_('COM_COMMUNITY_ACCESS_FORBIDDEN');
                return;
            }

            $this->showSubmenu();
            $this->_displayForm($event);
            return;
        }

        public function printpopup($event)
        {
            $config = CFactory::getConfig();
            $my = CFactory::getUser();
            // We need to attach the javascirpt manually

            // $js = JURI::root() . 'components/com_community/assets/joms.jquery-1.8.1.min.js';
            // $script = '<script type="text/javascript" src="' . $js . '"></script>';

            // $js = JURI::root() . 'components/com_community/assets/script-1.2.min.js';

            // $script .= '<script type="text/javascript" src="' . $js . '"></script>';

            $creator = CFactory::getUser($event->creator);
            $creatorUtcOffset = $creator->getUtcOffset();
            $creatorUtcOffsetStr = CTimeHelper::getTimezone($event->offset);

            // Get the formated date & time
            $format = ($config->get('eventshowampm')) ? JText::_('COM_COMMUNITY_DATE_FORMAT_LC2_12H') : JText::_(
                'COM_COMMUNITY_DATE_FORMAT_LC2_24H'
            );
            $event->startdateHTML = CTimeHelper::getFormattedTime($event->startdate, $format);
            $event->enddateHTML = CTimeHelper::getFormattedTime($event->enddate, $format);

            // Output to template
            $tmpl = new CTemplate();
            echo $tmpl->set('event', $event)
                ->set('script', $script)
                ->set('creatorUtcOffsetStr', $creatorUtcOffsetStr)
                ->fetch('events.print');
        }

        /**
         * Responsible for displaying the event page.
         * */
        public function viewevent()
        {
            $mainframe = JFactory::getApplication();
            $jinput = $mainframe->input;
            $document = JFactory::getDocument();
            $config = CFactory::getConfig();
            $my = CFactory::getUser();

            CWindow::load();

            $eventLib = new CEvents();
            $eventid = $jinput->getInt('eventid', 0);
            $eventModel = CFactory::getModel('events');
            $event = JTable::getInstance('Event', 'CTable');

            $handler = CEventHelper::getHandler($event);

            $event->load($eventid);

            if (empty($event->id)) {
                JFactory::getApplication()->enqueueMessage(JText::_('COM_COMMUNITY_EVENTS_NOT_AVAILABLE_ERROR'), 'warning');
            }

            if(!$my->authorise('community.view', 'events.' . $event->id)){
                $text = JText::_('COM_COMMUNITY_EVENTS_UNLISTED_ERROR');

                //check if the user has already request for invitation
                if($event->getUserStatus($my->id) == COMMUNITY_EVENT_STATUS_REQUESTINVITE){
                    $text.=JText::_('COM_COMMUNITY_EVENTS_AWAITING_APPROVAL');
                }else{
                    $text.=' <a href="javascript:" onclick="joms.api.eventJoin(\''.$event->id.'\');">Request Invitation</a>';
                }

                JFactory::getApplication()->enqueueMessage(JText::_('COM_COMMUNITY_EVENTS_NOT_AVAILABLE_ERROR'), 'warning');
                return;
            }

            if (!$handler->exists()) {
                JFactory::getApplication()->enqueueMessage(JText::_('COM_COMMUNITY_EVENTS_NOT_AVAILABLE_ERROR'), 'warning');
                    return;
            }

            // banned user cannot see this event
            if (!$handler->browsable() || $event->getUserStatus($my->id) == COMMUNITY_EVENT_STATUS_BANNED) {
                $tmpl = new CTemplate();
                echo $tmpl->fetch('events/missingevent');
                return;
            }

            // @rule: Test if the group is unpublished, don't display it at all.
            if (!$event->isPublished()) {
                echo JText::_('COM_COMMUNITY_EVENTS_UNDER_MODERATION');
                return;
            }
            //$this->showSubmenu();
            $event->hit();

            $isGroupAdmin = false;

            // Basic page presentation
            if ($event->type == 'group') {
                $groupId = $event->contentid;
                $group = JTable::getInstance('Group', 'CTable');
                $group->load($groupId);

                // Set pathway for group videos
                // Community > Groups > Group Name > Events
                $this->addPathway(
                    JText::_('COM_COMMUNITY_GROUPS'),
                    CRoute::_('index.php?option=com_community&view=groups')
                );
                $this->addPathway(
                    $group->name,
                    CRoute::_('index.php?option=com_community&view=groups&task=viewgroup&groupid=' . $groupId)
                );

                $groupEventDetails = new stdClass();
                $groupEventDetails->creator= CFactory::getUser($event->creator);
                $groupEventDetails->groupName = $group->name;
                $groupEventDetails->groupLink = CRoute::_('index.php?option=com_community&view=groups&task=viewgroup&groupid='.$group->id);

                $isGroupAdmin = $group->isAdmin($my->id);
            }

            $this->addPathway(
                JText::_('COM_COMMUNITY_EVENTS'),
                CRoute::_('index.php?option=com_community&view=events')
            );
            $this->addPathway($event->title);

            /**
             * Opengraph
             */
            CHeadHelper::setType(
                'website',
                JText::sprintf('COM_COMMUNITY_EVENT_PAGE_TITLE', $event->title),
                null,
                array($event->getCover())
            );

            // Permissions and privacies
            $isEventGuest = $event->isMember($my->id);
            $isMine = ($my->id == $event->creator);
            $isAdmin = $event->isAdmin($my->id) || $isGroupAdmin;
            $isCommunityAdmin = COwnerHelper::isCommunityAdmin();

            // Get Event Admins
            $eventAdmins = $event->getAdmins(12, CC_RANDOMIZE);
            $adminsInArray = array();

            // Attach avatar of the admin
            for ($i = 0; ($i < count($eventAdmins)); $i++) {
                $row = $eventAdmins[$i];
                $admin = CFactory::getUser($row->id);
                array_push(
                    $adminsInArray,
                    '<a href="' . CUrlHelper::userLink($admin->id) . '">' . $admin->getDisplayName() . '</a>'
                );
            }

            $adminsList = ltrim(implode(', ', $adminsInArray), ',');

            // Get Attending Event Guests
            $eventMembers = $event->getMembers(COMMUNITY_EVENT_STATUS_ATTEND, CFactory::getConfig()->get('event_sidebar_members_show_total',12) , CC_RANDOMIZE);
            $eventMembersCount = $event->getMembersCount(COMMUNITY_EVENT_STATUS_ATTEND);

            // Attach avatar of the admin
            // Pre-load multiple users at once
            $userids = array();
            foreach ($eventMembers as $uid) {
                $userids[] = $uid->id;
            }
            CFactory::loadUsers($userids);

            for ($i = 0; ($i < count($eventMembers)); $i++) {
                $row = $eventMembers[$i];
                $eventMembers[$i] = CFactory::getUser($row->id);
            }
            // Pre-load multiple users at once

            $waitingApproval = $event->isPendingApproval($my->id);
            $waitingRespond = false;

            $myStatus = $event->getUserStatus($my->id);

            $hasResponded = (($myStatus == COMMUNITY_EVENT_STATUS_ATTEND) || ($myStatus == COMMUNITY_EVENT_STATUS_WONTATTEND) || ($myStatus == COMMUNITY_EVENT_STATUS_MAYBE));

            // Get Bookmark HTML
            $bookmarks = new CBookmarks(
                CRoute::getExternalURL(
                    'index.php?option=com_community&view=events&task=viewevent&eventid=' . $event->id
                )
            );
            $bookmarksHTML = $bookmarks->getHTML();

            // Get the Wall
            $wallContent = CWallLibrary::getWallContents(
                'events',
                $event->id,
                $isAdmin,
                $config->get('stream_default_comments'),
                0,
                'wall/content',
                'events,events'
            );
            $wallCount = CWallLibrary::getWallCount('events', $event->id);
            $viewAllLink = false;

            if ($jinput->request->get('task', '', 'STRING') != 'app') {
                $viewAllLink = CRoute::_(
                    'index.php?option=com_community&view=events&task=app&eventid=' . $event->id . '&app=walls'
                );
            }

            $wallContent .= CWallLibrary::getViewAllLinkHTML($viewAllLink, $wallCount);

            $wallForm = '';

            // Construct the RVSP radio list
            $arr = array(
                JHTML::_('select.option', COMMUNITY_EVENT_STATUS_ATTEND, JText::_('COM_COMMUNITY_EVENTS_YES')),
                JHTML::_('select.option', COMMUNITY_EVENT_STATUS_WONTATTEND, JText::_('COM_COMMUNITY_EVENTS_NO')),
                JHTML::_('select.option', COMMUNITY_EVENT_STATUS_MAYBE, JText::_('COM_COMMUNITY_EVENTS_MAYBE'))
            );
            $status = $event->getMemberStatus($my->id);
            $radioList = JHTML::_('select.radiolist', $arr, 'status', '', 'value', 'text', $status, false);

            $unapprovedCount = $event->inviteRequestCount();
            //...
            $editEvent = $jinput->get->get('edit', false, 'NONE');
            $editEvent = ($editEvent == 1) ? true : false;

            // Am I invited in this event?
            $isInvited = false;
            $join = '';
            $friendsCount = 0;

            if ( $my->id > 0 ) {
                $isInvited = $eventModel->isInvitedMe(0, $my->id, $event->id);
            }

            // If I was invited, I want to know my invitation informations
            if ($isInvited) {
                $invitor = CFactory::getUser($isInvited[0]->invited_by);
                $join = '<a href="' . CUrlHelper::userLink($invitor->id) . '">' . $invitor->getDisplayName() . '</a>';

                // Get users friends in this group
                $friendsCount = $eventModel->getFriendsCount($my->id, $event->id);
            }

            // Get like
            $likes = new CLike();
            $isUserLiked = false;

            if ($isLikeEnabled = $likes->enabled('events')) {
                $isUserLiked = $likes->userLiked('events', $event->id, $my->id);
            }
            $totalLikes = $likes->getLikeCount('events', $event->id);

            // Is this event is a past event?
            $now = new JDate();
            $isPastEvent = ($event->getEndDate(false)->toSql() < $now->toSql(true)) ? true : false;

            // Get the formated date & time
            $format = ($config->get('eventshowampm')) ? JText::_('COM_COMMUNITY_EVENTS_TIME_FORMAT_12HR') : JText::_(
                'COM_COMMUNITY_EVENTS_TIME_FORMAT_24HR'
            );

            $startDate = $event->getStartDate(false);
            $endDate = $event->getEndDate(false);
            $allday = false;

            if (($startDate->format('%Y-%m-%d') == $endDate->format('%Y-%m-%d')) && $startDate->format(
                    '%H:%M:%S'
                ) == '00:00:00' && $endDate->format('%H:%M:%S') == '23:59:59'
            ) {
                $format = JText::_('COM_COMMUNITY_EVENT_TIME_FORMAT_LC1');
                $allday = true;
            }

            $event->startdateHTML = CTimeHelper::getFormattedTime($event->startdate, $format);
            $event->enddateHTML = CTimeHelper::getFormattedTime($event->enddate, $format);

            if (!isset($event->params)) {
                $event->params = '';
            }
            $params = new CParameter($event->params);

            $event->defaultCover = $event->isDefaultCover();

            // Cover position.
            $event->coverPostion = $params->get('coverPosition', '');
            if (strpos($event->coverPostion, '%') === false) {
                $event->coverPostion = 0;
            }

            // Find cover album and photo.
            $event->coverAlbum = false;
            $event->coverPhoto = false;
            $album = JTable::getInstance('Album', 'CTable');
            $albumId = $album->isCoverExist('event', $event->id);
            if ($albumId) {
                $album->load($albumId);
                $event->coverAlbum = $albumId;
                $event->coverPhoto = $album->photoid;
            }

            $inviteHTML = CInvitation::getHTML(
                null,
                'events,inviteUsers',
                $event->id,
                CInvitation::SHOW_FRIENDS,
                CInvitation::SHOW_EMAIL
            );

            $status = new CUserStatus($event->id, 'events');

            $tmpl = new CTemplate();
            $creator = new CUserStatusCreator('message');
            $creator->title = ($isMine) ? JText::_('COM_COMMUNITY_STATUS') : JText::_('COM_COMMUNITY_MESSAGE');
            $creator->html = $tmpl->fetch('status.message');
            $status->addCreator($creator);

            // Upgrade wall to stream @since 2.5
            $event->upgradeWallToStream();

            // Add custom stream
            $streamHTML = $eventLib->getStreamHTML($event);

            if ($event->getMemberStatus($my->id) == COMMUNITY_EVENT_STATUS_ATTEND) {
                $RSVPmessage = JText::_('COM_COMMUNITY_EVENTS_ATTENDING_EVENT_MESSAGE');
            } else {
                if ($event->getMemberStatus($my->id) == COMMUNITY_EVENT_STATUS_WONTATTEND) {
                    $RSVPmessage = JText::_('COM_COMMUNITY_EVENTS_NOT_ATTENDING_EVENT_MESSAGE');
                } else {
                    $RSVPmessage = JText::_('COM_COMMUNITY_EVENTS_NOT_RESPOND_RSVP_MESSAGE');
                }
            }

            // Get recurring event series
            $eventSeries = null;
            $seriesCount = 0;
            if ($event->isRecurring()) {
                $advance = array(
                    'expired' => false,
                    'return' => 'object',
                    'limit' => COMMUNITY_EVENT_SERIES_LIMIT,
                    'exclude' => $event->id,
                    'published' => 1
                );
                $tempseries = $eventModel->getEventChilds($event->parent, $advance);
                if($tempseries) {
                    foreach ($tempseries as $series) {
                        $table = JTable::getInstance('Event', 'CTable');
                        $table->bind($series);
                        $eventSeries[] = $table;
                    }
                }
                $seriesCount = $eventModel->getEventChildsCount($event->parent);
            }

            //pending request invitation guest
            $pendingRequestGuests = $event->getMembers(COMMUNITY_EVENT_STATUS_REQUESTINVITE, 0, false, false);

            // Pre-load multiple users at once
            $tempUserInfo = array();
            foreach ($pendingRequestGuests as $uid) {
                $tempUserInfo[] = CFactory::getUser($uid->id);
            }
            $pendingRequestGuests = $tempUserInfo;

            $featured = new CFeatured(FEATURED_EVENTS);
            $featuredList = $featured->getItemIds();

            // Get Attending Event Guests
            $maybeList = $event->getMembers(COMMUNITY_EVENT_STATUS_MAYBE, 12, CC_RANDOMIZE);
            $maybeCount = $event->getMembersCount(COMMUNITY_EVENT_STATUS_MAYBE);

            $tempUserInfo = array();
            foreach ($maybeList as $uid) {
                $tempUserInfo[] = CFactory::getUser($uid->id);
            }
            $maybeList = $tempUserInfo;

            $wontAttendList = $event->getMembers(COMMUNITY_EVENT_STATUS_WONTATTEND, 12, CC_RANDOMIZE);
            $wontAttendCount = $event->getMembersCount(COMMUNITY_EVENT_STATUS_WONTATTEND);

            $tempUserInfo = array();
            foreach ($wontAttendList as $uid) {
                $tempUserInfo[] = CFactory::getUser($uid->id);
            }
            $wontAttendList = $tempUserInfo;

            //gets all the albums related to this photo
            $photosModel = CFactory::getModel('photos');
            $allowShow = array(COMMUNITY_EVENT_STATUS_ATTEND,COMMUNITY_EVENT_STATUS_WONTATTEND,COMMUNITY_EVENT_STATUS_MAYBE);
            if(($event->permission == 1 || $event->unlisted == 1) && !in_array($event->getUserStatus($my->id),$allowShow)){
                //this is a invitation only group or unlisted event, so we only show covers album and exclude others
                $excludeType = array(
                    'event',
                    'event.gif',
                    'event.stream'
                );
                $albums = $photosModel->getEventAlbums($event->id, false, false, '', false, '', $excludeType);
            }else{
                $albums = $photosModel->getEventAlbums($event->id);
            }

            $totalPhotos = 0;
            foreach($albums as $album){
                $albumParams = new CParameter($album->params);
                $totalPhotos = $totalPhotos + $albumParams->get('count');
            }

            //get total videos
            $videosModel = CFactory::getModel('videos');
            $videos = $videosModel->getEventVideos($eventid);
            $totalVideos = count($videosModel->getEventVideos($eventid));

            // Output to template
            echo $tmpl->setMetaTags('event', $event)
                ->set('status', $status)
                ->set('albums', $albums)
                ->set('videos', $videos)
                ->set('timezoneName', $params->get('timezone'))
                ->set('pendingRequestGuests', $pendingRequestGuests)
                ->set('streamHTML', $streamHTML)
                ->set('timezone', CTimeHelper::getTimezone($event->offset))
                ->set('handler', $handler)
                ->set('isUserLiked', $isUserLiked)
                ->set('totalLikes', $totalLikes)
                ->set('inviteHTML', $inviteHTML)
                ->set('guestStatus', $event->getUserStatus($my->id))
                ->set('event', $event)
                ->set('radioList', $radioList)
                ->set('bookmarksHTML', $bookmarksHTML)
                ->set('isLikeEnabled', $isLikeEnabled)
                ->set('isEventGuest', $isEventGuest)
                ->set('isMine', $isMine)
                ->set('isAdmin', $isAdmin)
                ->set('isCommunityAdmin', $isCommunityAdmin)
                ->set('unapproved', $unapprovedCount)
                ->set('waitingApproval', $waitingApproval)
                ->set('wallContent', $wallContent)
                ->set('eventMembers', $eventMembers)
                ->set('eventMembersCount', $eventMembersCount)
                ->set('editEvent', $editEvent)
                ->set('my', $my)
                ->set('creator', CFactory::getUser($event->creator))
                ->set('memberStatus', $myStatus)
                ->set('waitingRespond', $waitingRespond)
                ->set('isInvited', $isInvited)
                ->set('join', $join)
                ->set('friendsCount', $friendsCount)
                ->set('isPastEvent', $isPastEvent)
                ->set('adminsList', $adminsList)
                ->set('RSVPmessage', $RSVPmessage)
                ->set('allday', $allday)
                ->set('eventSeries', $eventSeries)
                ->set('seriesCount', $seriesCount)
                ->set('groupEventDetails', isset($groupEventDetails) ? $groupEventDetails : null)
                ->set('featuredList', $featuredList)
                ->set('photoPermission', $params->get('photopermission'))
                ->set('videoPermission', $params->get('videopermission'))
                ->set('showPhotos', ( $params->get('photopermission') != -1 ) && $config->get('enablephotos') && $config->get('eventphotos'))
                ->set('showVideos', ( $params->get('videopermission') != -1 ) && $config->get('enablevideos') && $config->get('eventvideos'))
                ->set('totalPhotos', $totalPhotos)
                ->set('totalVideos', $totalVideos)
                ->set('maybeList', $maybeList)
                ->set('maybeCount', $maybeCount)
                ->set('wontAttendList', $wontAttendList)
                ->set('wontAttendCount', $wontAttendCount)
                ->fetch('events/single');
        }

        /**
         * Responsible to output the html codes for the task viewguest.
         * Outputs html codes for the viewguest page.
         *
         * @return    none.
         * */
        public function viewguest()
        {
            if (!$this->accessAllowed('registered')) {
                return;
            }

            $document = JFactory::getDocument();
            $jinput = JFactory::getApplication()->input;
            $config = CFactory::getConfig();
            $my = CFactory::getUser();
            $id = $jinput->getInt('eventid', 0);
            $type = $jinput->get('type');
            $approval = $jinput->get('approve');

            $event = JTable::getInstance('Event', 'CTable');
            $event->load($id);

            $handler = CEventHelper::getHandler($event);
            $types = array(
                COMMUNITY_EVENT_ADMINISTRATOR,
                COMMUNITY_EVENT_STATUS_INVITED,
                COMMUNITY_EVENT_STATUS_ATTEND,
                COMMUNITY_EVENT_STATUS_WONTATTEND,
                COMMUNITY_EVENT_STATUS_MAYBE,
                COMMUNITY_EVENT_STATUS_BLOCKED,
                COMMUNITY_EVENT_STATUS_REQUESTINVITE,
                COMMUNITY_EVENT_STATUS_BANNED
            );

            if (!in_array($type, $types)) {
                JFactory::getApplication()->enqueueMessage(JText::_('Invalid status type'), 'error');
            }

            // Set the guest type for the title purpose
            switch ($type) {
                case COMMUNITY_EVENT_ADMINISTRATOR:
                    $guestType = JText::_('COM_COMMUNITY_ADMINS');
                    break;
                case COMMUNITY_EVENT_STATUS_INVITED:
                    $guestType = JText::_('COM_COMMUNITY_EVENTS_PENDING_MEMBER');
                    break;
                case COMMUNITY_EVENT_STATUS_ATTEND:
                    $guestType = JText::_('COM_COMMUNITY_EVENTS_CONFIRMED_GUESTS');
                    break;
                case COMMUNITY_EVENT_STATUS_WONTATTEND:
                    $guestType = JText::_('COM_COMMUNITY_EVENTS_WONT_ATTEND');
                    break;
                case COMMUNITY_EVENT_STATUS_MAYBE:
                    $guestType = JText::_('COM_COMMUNITY_EVENTS_MAYBE_ATTEND');
                    break;
                case COMMUNITY_EVENT_STATUS_BLOCKED:
                    $guestType = JText::_('COM_COMMUNITY_EVENTS_BLOCKED');
                    break;
                case COMMUNITY_EVENT_STATUS_REQUESTINVITE:
                    $guestType = JText::_('COM_COMMUNITY_REQUESTED_INVITATION');
                    break;
                case COMMUNITY_EVENT_STATUS_BANNED:
                    $guestType = JText::_('COM_COMMUNITY_EVENTS_BANNED_MEMBERS');
                    break;
            }

            // Then we load basic page presentation
            $this->addPathway(
                JText::_('COM_COMMUNITY_EVENTS'),
                CRoute::_('index.php?option=com_community&view=events')
            );
            $this->addPathway(JText::sprintf('COM_COMMUNITY_EVENTS_TITLE_LABEL', $event->title), '');

            /**
             * Opengraph
             */
            CHeadHelper::setType(
                'website',
                JText::sprintf('COM_COMMUNTIY_EVENTS_GUESTLIST', $event->title, $guestType)
            );


            $status = $event->getUserStatus($my->id);
            $allowed = array(
                COMMUNITY_EVENT_STATUS_INVITED,
                COMMUNITY_EVENT_STATUS_ATTEND,
                COMMUNITY_EVENT_STATUS_WONTATTEND,
                COMMUNITY_EVENT_STATUS_MAYBE
            );
            $accessAllowed = ((in_array(
                    $status,
                    $allowed
                )) && $status != COMMUNITY_EVENT_STATUS_BLOCKED) ? true : false;

            if ($handler->hasInvitation() && (($accessAllowed && $event->allowinvite) || $event->isAdmin(
                        $my->id
                    ) || COwnerHelper::isCommunityAdmin())
            ) {
                $this->addSubmenuItem(
                    'javascript:void(0)',
                    JText::_('COM_COMMUNITY_TAB_INVITE'),
                    "joms.invitation.showForm('', 'events,inviteUsers','" . $event->id . "','1','1');",
                    SUBMENU_RIGHT
                );
            }
            $this->showSubmenu();

            $isSuperAdmin = COwnerHelper::isCommunityAdmin();

            // status = unsure | noreply | accepted | declined | blocked
            // permission = admin | guest |

            if ($type == COMMUNITY_EVENT_ADMINISTRATOR) {
                $guestsIds = $event->getAdmins(0);
            } else {
                $guestsIds = $event->getMembers($type, 0, false, $approval);
            }

            $guests = array();

            // Pre-load multiple users at once
            $userids = array();
            foreach ($guestsIds as $uid) {
                $userids[] = $uid->id;
            }
            CFactory::loadUsers($userids);

            for ($i = 0; $i < count($guestsIds); $i++) {
                $guests[$i] = CFactory::getUser($guestsIds[$i]->id);
                $guests[$i]->friendsCount = $guests[$i]->getFriendCount();
                $guests[$i]->isMe = ($my->id == $guests[$i]->id) ? true : false;
                $guests[$i]->isAdmin = $event->isAdmin($guests[$i]->id);
                $guests[$i]->statusType = $guestsIds[$i]->statusCode;
            }

            // Featured
            $featured = new CFeatured(FEATURED_USERS);
            $featuredList = $featured->getItemIds();


            $pagination = $event->getPagination();

            // Output to template
            $tmpl = new CTemplate();
            echo CMiniHeader::showEventMiniHeader($event->id);

            echo $tmpl->set('event', $event)
                ->set('type', $type)
                ->set('handler', $handler)
                ->set('guests', $guests)
                ->set('eventid', $event->id)
                ->set('isMine', $event->isCreator($my->id))
                ->set('isSuperAdmin', $isSuperAdmin)
                ->set('pagination', $pagination)
                ->set('my', $my)
                ->set('config', $config)
                ->set('isAdmin', $event->isAdmin($my->id))
                ->set('featuredList', $featuredList)
                ->fetch('events.viewguest');
        }

        public function search()
        {
            // Get the document object and set the necessary properties of the document
            $document = JFactory::getDocument();
            $this->addPathway(
                JText::_('COM_COMMUNITY_EVENTS'),
                CRoute::_('index.php?option=com_community&view=events')
            );
            $this->addPathway(JText::_('COM_COMMUNITY_EVENTS_SEARCH'), '');
            /**
             * Opengraph
             */
            CHeadHelper::setType('website', JText::_('COM_COMMUNITY_SEARCH_EVENTS_TITLE'));

            $mainframe = JFactory::getApplication();
            $jinput = $mainframe->input;
            // $script = '<script type="text/javascript" src="http://maps.google.com/maps/api/js?sensor=false"></script>';
            // $document->addCustomTag($script);

            $config = CFactory::getConfig();

            // Display the submenu
            $this->showSubmenu();

            //New search features
            $model = CFactory::getModel('events');
            $categories = $model->getCategories();

            // input filtered to remove tags
            $search = trim($jinput->get('search', '', 'STRING'));

            // Input for advance search
            $catId = $jinput->getInt('catid', '');
            $unit = $jinput->get('unit', $config->get('eventradiusmeasure'), 'NONE');

            $category = JTable::getInstance('EventCategory', 'CTable');
            $category->load($catId);

            $advance = array();
            $advance['startdate'] = $jinput->get('startdate', '', 'NONE');
            $advance['enddate'] = $jinput->get('enddate', '', 'NONE');
            $advance['radius'] = $jinput->get('radius', '', 'NONE');
            $advance['fromlocation'] = $jinput->get('location', '', 'NONE');

            if ($unit === COMMUNITY_EVENT_UNIT_KM) { //COM_COMMUNITY_EVENTS_MILES
                // Since our searching need a value in Miles unit, we need to convert the KM value to Miles
                // 1 kilometre	=   0.621371192 miles
                // 1 mile = 1.6093 km
                $advance['radius'] = $advance['radius'] * 0.621371192;
            }

            $events = '';
            $pagination = null;
            $posted = $jinput->getInt('posted', '');
            $count = 0;
            $eventsHTML = '';

            // Test if there are any post requests made
            if (!empty($search) || !empty($catId) || (!empty($advance['startdate']) || !empty($advance['enddate']) || !empty($advance['radius']) || !empty($advance['fromlocation']))) {
                // Check for request forgeries
                JSession::checkToken('get') or jexit(JText::_('COM_COMMUNITY_INVALID_TOKEN'));

                //CFactory::load( 'libraries' , 'apps' );
                $appsLib = CAppPlugins::getInstance();
                $saveSuccess = $appsLib->triggerEvent('onFormSave', array('jsform-events-search'));

                if (empty($saveSuccess) || !in_array(false, $saveSuccess)) {
                    $events = $model->getEvents($category->id,  null , null, $search, null, null, null, $advance);
                    $pagination = $model->getPagination();
                    $count = $model->getEventsSearchTotal();
                }
            }

            // Get the template for the events lists
            $eventsHTML = $this->_getEventsHTML($events, false, $pagination, true);

            $app = CAppPlugins::getInstance();
            $appFields = $app->triggerEvent('onFormDisplay', array('jsform-events-search'));
            $beforeFormDisplay = CFormElement::renderElements($appFields, 'before');
            $afterFormDisplay = CFormElement::renderElements($appFields, 'after');

            $searchLinks = parent::getAppSearchLinks('events');

            // Revert back the radius value
            $advance['radius'] = $jinput->get('radius', '', 'NONE');

            $tmpl = new CTemplate();
            echo $tmpl->set('beforeFormDisplay', $beforeFormDisplay)
                ->set('afterFormDisplay', $afterFormDisplay)
                ->set('posted', $posted)
                ->set('eventsCount', $count)
                ->set('eventsHTML', $eventsHTML)
                ->set('search', $search)
                ->set('catId', $category->id)
                ->set('categories', $categories)
                ->set('advance', $advance)
                ->set('unit', $unit)
                ->set('searchLinks', $searchLinks)
                ->fetch('events.search');
        }

        /**
         * An event has just been created, should we just show the album ?
         */
        public function created()
        {
            $jinput = JFactory::getApplication()->input;
            $eventid = $jinput->getInt('eventid', 0);

            //CFactory::load( 'models' , 'events');
            $event = JTable::getInstance('Event', 'CTable');

            $event->load($eventid);
            /**
             * Opengraph
             */
            CHeadHelper::setType('website', $event->title);

            $uri = JURI::base();
            $this->showSubmenu();

            $tmpl = new CTemplate();
            echo $tmpl->set(
                'link',
                CRoute::_('index.php?option=com_community&view=events&task=viewevent&eventid=' . $event->id)
            )
                ->set(
                    'linkUpload',
                    CRoute::_('index.php?option=com_community&view=events&task=uploadavatar&eventid=' . $event->id)
                )
                ->set(
                    'linkEdit',
                    CRoute::_('index.php?option=com_community&view=events&task=edit&eventid=' . $event->id)
                )
                ->set(
                    'linkInvite',
                    CRoute::_('index.php?option=com_community&view=events&task=invitefriends&eventid=' . $event->id)
                )
                ->fetch('events.created');
        }

        public function sendmail()
        {

            $mainframe = JFactory::getApplication();
            $jinput = $mainframe->input;
            /**
             * Opengraph
             */
            CHeadHelper::setType('website', JText::_('COM_COMMUNITY_EVENTS_EMAIL_SEND'));

            $this->addPathway(
                JText::_('COM_COMMUNITY_EVENTS'),
                CRoute::_('index.php?option=com_community&view=events')
            );
            $this->addPathway(JText::_('COM_COMMUNITY_EVENTS_EMAIL_SEND'));

            if (!$this->accessAllowed('registered')) {
                return;
            }

            // Display the submenu
            $this->showSubmenu();
            $eventId = $jinput->get('eventid', '', 'INT');
            $type = $jinput->get('type', COMMUNITY_EVENT_STATUS_ATTEND , 'INT');

            //CFactory::load( 'helpers', 'owner' );
            //CFactory::load( 'models' , 'events' );
            $event = JTable::getInstance('Event', 'CTable');
            $event->load($eventId);

            if (empty($eventId) || empty($event->title)) {
                echo JText::_('COM_COMMUNITY_INVALID_ID_PROVIDED');
                return;
            }

            $my = CFactory::getUser();
            $config = CFactory::getConfig();
            $mainframe = JFactory::getApplication();
            $jinput = $mainframe->input;

            //CFactory::load( 'libraries' , 'editor' );
            $editor = new CEditor($config->get('htmleditor'));

            //CFactory::load( 'helpers' , 'event' );
            $handler = CEventHelper::getHandler($event);
            if (!$handler->manageable()) {
                $this->noAccess();
                return;
            }

            $message = $jinput->post->get('message','','RAW');
            $title = $jinput->get('title', '', 'STRING');
            echo CMiniHeader::showEventMiniHeader($event->id);

            $tmpl = new CTemplate();
            echo $tmpl->set('editor', $editor)
                ->set('type',$type)
                ->set('event', $event)
                ->set('message', $message)
                ->set('title', $title)
                ->fetch('events.sendmail');
        }

        public function uploadAvatar()
        {
            /**
             * Opengraph
             */
            CHeadHelper::setType('website', JText::_('COM_COMMUNITY_EVENTS_AVATAR'));

            $mainframe = JFactory::getApplication();
            $jinput = $mainframe->input;

            $eventid = $jinput->get('eventid', '0', 'INT');
            $this->_addEventInPathway($eventid);
            $this->addPathway(JText::_('COM_COMMUNITY_EVENTS_AVATAR'));

            $this->showSubmenu();
            $event = JTable::getInstance('Event', 'CTable');
            $event->load($eventid);

            //CFactory::load( 'helpers' , 'event' );
            $handler = CEventHelper::getHandler($event);
            if (!$handler->manageable()) {
                $this->noAccess();
                return;
            }

            $config = CFactory::getConfig();
            $uploadLimit = (double)$config->get('maxuploadsize');
            $uploadLimit .= 'MB';

            //CFactory::load( 'models' , 'events' );
            $event = JTable::getInstance('Event', 'CTable');
            $event->load($eventid);

            //CFactory::load( 'libraries' , 'apps' );
            $app = CAppPlugins::getInstance();
            $appFields = $app->triggerEvent('onFormDisplay', array('jsform-events-uploadavatar'));
            $beforeFormDisplay = CFormElement::renderElements($appFields, 'before');
            $afterFormDisplay = CFormElement::renderElements($appFields, 'after');

            $tmpl = new CTemplate();
            echo $tmpl->set('beforeFormDisplay', $beforeFormDisplay)
                ->set('afterFormDisplay', $afterFormDisplay)
                ->set('eventId', $eventid)
                ->set('avatar', $event->getAvatar('avatar'))
                ->set('thumbnail', $event->getThumbAvatar())
                ->set('uploadLimit', $uploadLimit)
                ->fetch('events.uploadavatar');
        }

        public function _addEventInPathway($eventId)
        {
            //CFactory::load( 'models' , 'events' );
            $event = JTable::getInstance('Event', 'CTable');
            $event->load($eventId);

            $this->addPathway(
                $event->title,
                CRoute::_('index.php?option=com_community&view=events&task=viewevent&eventid=' . $event->id)
            );
        }

        public function _getEventsHTML($eventObjs, $isExpired = false, $pagination = null, $isSearch = false)
        {
            $jinput = JFactory::getApplication()->input;
            $categoryid = $jinput->get('categoryid', 0, 'SR');
            $groupid = $jinput->get('groupid', 0, 'INT');
            $task = $jinput->get('task', '', 'STRING');

            $events = array();

            $config = CFactory::getConfig();
            $format = ($config->get('eventshowampm')) ? JText::_('COM_COMMUNITY_DATE_FORMAT_LC2_12H') : JText::_(
                'COM_COMMUNITY_DATE_FORMAT_LC2_24H'
            );

            if ($eventObjs) {
                foreach ($eventObjs as $row) {
                    $event = JTable::getInstance('Event', 'CTable');
                    $event->bind($row);
                    $params = new JRegistry($event->params);
                    $event->showPhotos = ( $params->get('photopermission') != -1 ) && $config->get('enablephotos') && $config->get('eventphotos');
                    $event->showVideos = ( $params->get('videopermission') != -1 ) && $config->get('enablevideos') && $config->get('eventvideos');

                    if($event->showPhotos){
                        //gets all the albums related to this photo
                        $photosModel = CFactory::getModel('photos');
                        $albums = $photosModel->getEventAlbums($event->id);
                        $event->totalPhotos = 0;
                        foreach($albums as $album){
                            $albumParams = new CParameter($album->params);
                            $event->totalPhotos = $event->totalPhotos + $albumParams->get('count');
                        }
                    }

                    if($event->showVideos){
                        //get total videos
                        $videosModel = CFactory::getModel('videos');
                        $event->totalVideos = count($videosModel->getEventVideos($event->id));
                    }

                    $events[] = $event;
                }
                unset($eventObjs);
            }

            $featured = new CFeatured(FEATURED_EVENTS);
            $featuredList = $featured->getItemIds();


            $tmpl = new CTemplate();
            return $tmpl->set('showFeatured', $config->get('show_featured'))
                ->set('featuredList', $featuredList)
                ->set('isCommunityAdmin', COwnerHelper::isCommunityAdmin())
                ->set('events', $events)
                ->set('availableCategories', $this->getFullEventsCategories())
                ->set('isSearch', $isSearch)
                ->set('groupid',$groupid)
                ->set('task', $task)
                ->set('categoryId', $categoryid)
                ->set('isExpired', $isExpired)
                ->set('pagination', $pagination)
                ->set('timeFormat', $format)
                ->fetch('events/list');
        }

        public function _getEventsCategories($categoryId)
        {
            $model = CFactory::getModel('events');
            $categories = $model->getCategoriesCount();
            $categories = CCategoryHelper::getParentCount($categories, $categoryId);

            return $categories;
        }

        public function getEventsCategory($id){
            $model = CFactory::getModel('events');
            $categories = $model->getCategories(CEventHelper::ALL_TYPES, $id);

            return $categories;
        }

        /**
         * List all the category including the children and format it
         */
        public function getFullEventsCategories($id = 0, $level = 0, $categoryList = array()){
            $mainCategories = $this->getEventsCategory($id); // first level of video category

            if(count($mainCategories) > 0){
                foreach($mainCategories as $category){
                    $prefix = '';
                    for($i = 0; $i < $level; $i++){
                        $prefix = $prefix.'-'; // this will add the - in front of the category name
                    }

                    $category->name = $prefix.' '.JText::_($category->name);
                    $categoryList[] = $category;
                    $categoryList = $this->getFullEventsCategories($category->id, $level+1, $categoryList);
                }
            }

            return $categoryList;
        }

        public function _getPendingListHTML($user)
        {
            //CFactory::load( 'models', 'events' );
            $mainframe = JFactory::getApplication();
            $jinput = $mainframe->input;
            $model = CFactory::getModel('events');
            $sorted = $jinput->get->get(
                'sort',
                'startdate',
                'STRING'
            );
            $pending = COMMUNITY_EVENT_STATUS_INVITED;
            $rows = $model->getEvents(null, $user->id, $sorted, null, true, false, $pending);
            $events = array();

            if ($rows) {
                foreach ($rows as $row) {
                    $event = JTable::getInstance('Event', 'CTable');
                    $event->bind($row);
                    $events[] = $event;
                }
            }

            $tmpl = new CTemplate();
            return $tmpl->set('events', $events)
                ->fetch('events.pendinginvitelist');
        }

        /**
         * @param $activity
         * @throws Exception
         */
        public function singleActivity($activity)
        {
            // we will determine all the user settings based on the activity viewed
            $my = CFactory::getUser();
            $userId = $activity->actor;

            if($activity->id == 0 || empty($activity->id)){
                //redirect this to error : no activity found
                JFactory::getApplication()->enqueueMessage(JText::_('COM_COMMUNITY_ERROR_ACTIVITY_NOT_FOUND'), 'warning');
            }

            echo CMiniHeader::showEventMiniHeader($activity->eventid);

            $document = JFactory::getDocument();
            $document->setTitle(JHTML::_('string.truncate', $activity->title, 75));

            CHeadHelper::setDescription(JHTML::_('string.truncate', $activity->title, 300, true));
            //see if the user has blocked each other
            $getBlockStatus = new blockUser();
            $blocked = $getBlockStatus->isUserBlocked($userId, 'profile');
            if ($blocked && !COwnerHelper::isCommunityAdmin()) {
                JFactory::getApplication()->enqueueMessage(JText::_('COM_COMMUNITY_ERROR_ACTIVITY_NOT_FOUND'), 'warning');
            }

            //everything is fine, lets get to the activity
            echo $this->_getNewsfeedHTML();
        }

        private function _getNewsfeedHTML() {
            $my = CFactory::getUser();
            $mainframe = JFactory::getApplication();
            $jinput = $mainframe->input;

            $userId = $jinput->get('userid', $my->id, 'INT');

            return CActivities::getActivitiesByFilter('active-profile', $userId, 'profile', true, array('show_featured'=>true));
        }

        public function _getEventsFeaturedList()
        {
            //CFactory::load( 'libraries' , 'featured' );
            $featured = new CFeatured(FEATURED_EVENTS);
            $featuredEvents = $featured->getItemIds();
            $featuredList = array();
            $now = new JDate();

            foreach ($featuredEvents as $event) {
                $table = JTable::getInstance('Event', 'CTable');
                $table->load($event);
                $expiry = new JDate($table->enddate);
                if ($expiry->toUnix() >= $now->toUnix()) {
                    if ($table->id != '') {
                        $featuredList[] = $table;
                    }
                }
            }

            if (!empty($featuredList)) {
                foreach ($featuredList as $key => $row) {
                    $orderByDate[$key] = strtotime($row->startdate);
                }

                array_multisort($orderByDate, SORT_ASC, $featuredList);
            }


            return $featuredList;
        }

    }

}
