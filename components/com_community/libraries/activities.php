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

jimport('joomla.filesystem.file');

class CActivities {

    const COMMENT_SELF = -1;
    const LIKE_SELF = -1;

    /* apps activity code */
    const APP_EVENTS = 'events';
    const APP_GROUPS = 'groups';

    /**
     * Removes an existing activity from the system
     * @access    static
     * */
    static public function remove($appType, $uniqueId) {
        $activitiesModel = CFactory::getModel('activities');

        return $activitiesModel->removeActivity($appType, $uniqueId);
    }

    static public function removeGroup($ids) {
        $activitiesModel = CFactory::getModel('activities');

        return $activitiesModel->removeGroupActivity($ids);
    }

    /**
     * Similar to 'add' but will look for similar activity in the last 24 hours.
     * If exist, it will add the new actor into 'actor' params in csv format
     * It does matching for app and cid
     * We assume everything else is the same
     */
    static public function addActor($activity, $params = '', $points = 1) {

        // grab the similar object, ONLY if it is not archived. IE, not older than
        // 1 day old. We would inset this new user into the 'actors' params
        $table = JTable::getInstance('Activity', 'CTable');
        $table->load(array('app' => $activity->app, 'cid' => $activity->cid, 'archived' => 0));

        // Use newer params, but update the actor list
        $currentParam = new JRegistry($table->params);
        $newParam = new JRegistry($params);
        $actors = CCSV::insert($currentParam->get('actors'), $activity->actor);
        $newParam->set('actors', $actors);
        $params = $newParam->toString();


        // Add actors to actors list, so that it appear on personal stream
        // remove duplicate if it is already there
        $actors = new stdClass();
        $table->actors = str_replace('{"id":"' . $activity->actor . '"}', '""', $table->actors); // avoid duplicate.
        $actors = json_decode($table->actors);
        if (!is_object($actors) && empty($actors)) {
            $actors = new stdClass();
        }
        $actors->userid = (!empty($actors->userid)) ? array_filter($actors->userid) : array(); // clear out empty data
        $actors->userid[] = array('id' => $activity->actor);
        $activity->actors = json_encode($actors);

        // Set the activity actor to 0, hence, it won't appear on personal stream
        // only in public stream
        $activity->actor = 0;

        CActivityStream::add($activity, $params, $points);
        // Delete old ones
        if (!is_null($table->id))
            $table->delete();
    }

    /**
     * Add new activity,
     * @access     static
     *
     */
    static public function add($activity, $params = '', $points = 1) {
        CError::assert($activity, '', '!empty', __FILE__, __LINE__);

        $cmd = !empty($activity->cmd) ? $activity->cmd : '';

        if (!empty($cmd)) {
            $userPointModel = CFactory::getModel('Userpoints');

            $point = $userPointModel->getPointData($cmd);
            //no reason to disable activity if no points is given
            if ($point) {
                if (!$point->published) {
                    return;
                }
            }
        }

        // If params is an object, instead of a string, we convert it to string
        $cmd = !empty($activity->cmd) ? $activity->cmd : '';
        $actor = !empty($activity->actor) ? $activity->actor : '';
        $actors = !empty($activity->actors) ? $activity->actors : '';
        $target = !empty($activity->target) ? $activity->target : 0;
        $title = !empty($activity->title) ? $activity->title : '';
        $content = !empty($activity->content) ? $activity->content : '';
        $appname = !empty($activity->app) ? $activity->app : '';
        $cid = !empty($activity->cid) ? $activity->cid : 0;
        $groupid = !empty($activity->groupid) ? $activity->groupid : 0;
        $group_access = !empty($activity->group_access) ? $activity->group_access : 0;
        $event_access = !empty($activity->event_access) ? $activity->event_access : 0;
        $eventid = !empty($activity->eventid) ? $activity->eventid : 0;
        $points = !empty($activity->points) ? $activity->points : $points;
        $access = !empty($activity->access) ? $activity->access : 0;
        $location = !empty($activity->location) ? $activity->location : '';
        $comment_id = !empty($activity->comment_id) ? $activity->comment_id : 0;
        $comment_type = !empty($activity->comment_type) ? $activity->comment_type : '';
        $like_id = !empty($activity->like_id) ? $activity->like_id : 0;
        $like_type = !empty($activity->like_type) ? $activity->like_type : '';
        $latitude = !empty($activity->latitude) ? $activity->latitude : null;
        $longitude = !empty($activity->longitude) ? $activity->longitude : null;

        // If the params in embedded within the activity object, use it
        // if it is not explicitly overriden
        if (empty($params) && !empty($activity->params)) {
            $params = $activity->params;
        }

        // Update access for activity based on the user's profile privacy
        // since 2.4.2 if access is provided, dont use the default
        if (!empty($actor) && $actor != 0 && !isset($access)) {
            $user = CFactory::getUser($actor);
            $userParams = $user->getParams();
            $profileAccess = $userParams->get('privacyProfileView');

            // Only overwrite access if the user global profile privacy is higher
            // BUT, if access is defined as PRIVACY_FORCE_PUBLIC, do not modify it
            if (($access != PRIVACY_FORCE_PUBLIC) && ($profileAccess > $access)) {
                $access = $profileAccess;
            }
        }
        $table = JTable::getInstance('Activity', 'CTable');

        //before adding we must aggregate the videos/album/photo comments
        $aggregateApps = array('videos.comment', 'albums.comment', 'photos.comment');
        if(in_array($appname, $aggregateApps)){
            //we need to check if this activity exists
            $filter = array('app' => $appname, 'cid' => $cid);
            $activitiesModel = CFactory::getModel('activities');
            $activityId = $activitiesModel->getActivityId($filter);
            if($activityId){
                $table->load($activityId);
                $passedParams = new JRegistry($params); // update wall id if needed
                $wallid = $passedParams->get('wallid');
                $params = new JRegistry($table->params);
                $params->set('wallid', $wallid);
                $act = $params->get('actors');
                if(!is_array($act)){
                    $params->set('actors', array($actor));
                }else{
                    array_unshift($act,$actor);
                    $act = array_unique($act); // latest user must be on the first
                    $params->set('actors', $act);
                }
                $table->created = JDate::getInstance()->toSql();
                $table->params = $params->toString();
                $table->store();
                return $table;
            }else{
                //if this is the first creation
                $params = new JRegistry($params);
                $params->set('actors', array($actor));
                $params = $params->toString();
            }
        }

        $table->actor = $actor;
        $table->actors = $actors;
        $table->target = $target;
        $table->title = $title;
        $table->content = $content;
        $table->app = $appname;
        $table->cid = $cid;
        $table->groupid = $groupid;
        $table->group_access = $group_access;
        $table->eventid = $eventid;
        $table->event_access = $event_access;
        $table->points = $points;
        $table->access = $access;
        $table->location = $location;
        $table->params = $params;
        $table->comment_id = $comment_id;
        $table->comment_type = $comment_type;
        $table->like_id = $like_id;
        $table->like_type = $like_type;
        $table->latitude = $latitude;
        $table->longitude = $longitude;

        $table->store();

        //no matter what kind of message it is, always filter the hashtag if there's any
        if(!empty($table->title)&& isset($table->id)){
            //use model to check if this has a tag in it and insert into the table if possible
            $hashtags = CContentHelper::getHashTags($table->title);
            if(count($hashtags)){
                //$hashTag
                $hashtagModel = CFactory::getModel('hashtags');

                foreach($hashtags as $tag){
                    $hashtagModel->addActivityHashtag($tag, $table->id);
                }
            }
        }

        // Update comment id, if we comment on the stream itself
        if ($comment_id == CActivities::COMMENT_SELF) {
            $table->comment_id = $table->id;
        }

        // Update comment id, if we like on the stream itself
        if ($like_id == CActivities::LIKE_SELF) {
            $table->like_id = $table->id;
        }

        if ($comment_id == CActivities::COMMENT_SELF || $like_id == CActivities::LIKE_SELF) {
            $table->store($table);
        }

        return $table;
    }

    /**
     * Return HTML formatted activity title
     */
    static public function getActivityTitle($act) {

        $cache = CFactory::getFastCache();
        $cacheid = __FILE__ . __LINE__ . serialize(func_get_args());
        if ($data = $cache->get($cacheid)) {
            return $data;
        }

        $html = '';
        $user = CFactory::getUser($act->actor);

        // For known core, apps, we can simply call the content command
        switch ($act->app) {
            case 'videos':

                $html = CVideos::getActivityTitleHTML($act);
                break;

            case 'photos':

                $html = CPhotos::getActivityTitleHTML($act);
                break;

            default:
                // For everything else, use User > Group format
                $html = '<a href="' . CUrlHelper::userLink($act->actor) . '">' . CStringHelper::escape($user->getDisplayName()) . '</a>';
        }
        $cache->store($html, $cacheid, array('activities'));
        return $html;
    }

    /**
     * Return the HTML formatted activity content
     */
    static public function getActivityContent($act) {

        $cache = CFactory::getFastCache();
        $cacheid = __FILE__ . __LINE__ . serialize(func_get_args());
        if ($data = $cache->get($cacheid)) {
            return $data;
        }

        // Return empty content or content with old, invalid data
        // In some old version, some content might have 'This is the body'
        if ($act->content == 'This is the body') {
            return '';
        }

        $html = $act->content;

        // For known core, apps, we can simply call the content command
        switch ($act->app) {
            case 'videos':

                //$html = CVideos::getActivityContentHTML($act);
                break;

            case 'photos':

                //$html = CPhotos::getActivityContentHTML($act);
                break;

            case 'events':

                //$html = CEvents::getActivityContentHTML($act);
                break;

            case 'groups.wall':
            case 'groups':

                //$html = CGroups::getActivityContentHTML($act);
                break;
            case 'groups.discussion.reply':
            case 'groups.discussion':

                //$html = CGroups::getActivityContentHTML($act);
                break;
            case 'groups.bulletin':

                //	$html = CGroups::getActivityContentHTML($act);
                break;
            case 'system':

                //	$html = CAdminstreams::getActivityContentHTML($act);
                break;
            case 'walls':
                // If a wall does not have any content, do not
                // display the summary
                if ($act->app == 'walls' && $act->cid == 0) {
                    $html = '';
                    return $html;
                }

                if ($act->cid != 0) {

                    //		$html = CWall::getActivityContentHTML($act);
                }
                break;
            default:
                // for other unknown apps, we include the plugin see if it is is callable
                // we call the onActivityContentDisplay();


                $apps = CAppPlugins::getInstance();
                $plugin = $apps->get($act->app);
                $method = 'onActivityContentDisplay';

                if (is_callable(array($plugin, $method))) {
                    $args = array();
                    $args[] = $act;

                    $html = call_user_func_array(array($plugin, $method), $args);
                } else {

                    $html = CStringHelper::escape($act->content);
                }
        }
        $cache->store($html, $cacheid, array('activities'));
        return $html;
    }

    /**
     * Return an array of activity data
     *
     * @param type $options
     * @return mixed $type string or arrayn or string
     */
    private function _getData($options) {
        $dispatcher = CDispatcher::getInstanceStatic();
        $observers = $dispatcher->getObservers();
        $plgObj = false;

        for ($i = 0; $i < count($observers); $i++) {
            if ($observers[$i] instanceof plgCommunityWordfilter) {
                $plgObj = $observers[$i];
            }
        }

        // Default params
        $default = array(
            'actid' => null,
            'actor' => 0,
            'target' => 0,
            'date' => null,
            'app' => null,
            'cid' => null, // don't filter with cid
            'groupid' => null,
            'eventid' => null,
            'maxList' => 20,
            'type' => '',
            'exclusions' => null,
            'displayArchived' => false
        );
        /* Merge with input options */
        $options = array_merge($default, $options);
        extract($options);

        /* Get models */
        $activities = CFactory::getModel('activities');

        /* Variables */


        $my = CFactory::getUser();

        $htmlData = array();
        $config = CFactory::getConfig();

        $blockLists = $my->getBlockedUsers();
        $blockedUserId = array();

        foreach ($blockLists as $blocklist) {
            $blockedUserId[] = $blocklist->blocked_userid;
        }

        // Exclude banned userid
        if (!empty($target) && !empty($blockedUserId)) {
            $target = array_diff($target, $blockedUserId);
        }

        if (!empty($app)) {
            $rows = $activities->getAppActivities($options);
        } else {
            $rows = $activities->getActivities($actor, $target, $date, $maxList, $config->get('respectactivityprivacy'), $exclusions, $displayArchived, $actid, $groupid, $eventid, $options);
        }

        $day = -1;

        // If exclusion is set, we need to remove activities that arrives
        // after the exclusion list is set.
        // Inject additional properties for processing
        for ($i = 0; $i < count($rows); $i++) {
            $row = $rows[$i];

            // A 'used' activities = activities that has been aggregated
            $row->used = false;

            // If the id is larger than any of the exclusion list,
            // we simply hide it
            if (isset($exclusion) && $exclusion > 0 && $row->id > $exclusions) {
                $row->used = true;
            }
        }

        unset($row);

        $dayinterval = ACTIVITY_INTERVAL_DAY;
        $lastTitle = '';

        for ($i = 0; $i < count($rows) && (count($htmlData) <= $maxList); $i++) {
            $row = $rows[$i];
            $oRow = $rows[$i]; // The original object
            // store aggregated activities
            $oRow->activities = array();

            if (!$row->used && count($htmlData) <= $maxList) {
                $oRow = $rows[$i];

                if (!isset($row->used)) {
                    $row->used = false;
                }

                if ($day != $row->getDayDiff()) {

                    $act = new stdClass();
                    $act->type = 'content';
                    $day = $row->getDayDiff();

                    if ($day == 0) {
                        $act->title = JText::_('TODAY');
                    } else if ($day == 1) {
                        $act->title = JText::_('COM_COMMUNITY_ACTIVITIES_YESTERDAY');
                    } else if ($day < 7) {
                        $act->title = JText::sprintf('COM_COMMUNITY_ACTIVITIES_DAYS_AGO', $day);
                    } else if (($day >= 7) && ($day < 30)) {
                        $dayinterval = ACTIVITY_INTERVAL_WEEK;
                        $act->title = (intval($day / $dayinterval) == 1 ? JText::_('COM_COMMUNITY_ACTIVITIES_WEEK_AGO') : JText::sprintf('COM_COMMUNITY_ACTIVITIES_WEEK_AGO_MANY', intval($day / $dayinterval)));
                    } else if (($day >= 30)) {
                        $dayinterval = ACTIVITY_INTERVAL_MONTH;
                        $act->title = (intval($day / $dayinterval) == 1 ? JText::_('COM_COMMUNITY_ACTIVITIES_MONTH_AGO') : JText::sprintf('COM_COMMUNITY_ACTIVITIES_MONTH_AGO_MANY', intval($day / $dayinterval)));
                    }

                    // set to a new 'title' type if this new one has a new title
                    // only add if this is a new title
                    if ($act->title != $lastTitle) {
                        $lastTitle = $act->title;
                        $act->type = 'title';
                        $htmlData[] = $act;
                    }
                }

                $act = new stdClass();
                $act->type = 'content';

                $title = $row->title;
                $app = $row->app;
                $cid = $row->cid;
                $actor = $row->actor;
                $commentTypeId = $row->comment_type . $row->comment_id;

                //Check for event or group title if exists
                if ($row->eventid) {
                    $eventModel = CFactory::getModel('events');
                    $act->appTitle = $eventModel->getTitle($row->eventid);
                } else if ($row->groupid) {
                    $groupModel = CFactory::getModel('groups');
                    $act->appTitle = $groupModel->getGroupName($row->groupid);
                }

                for ($j = $i; ($j < count($rows)) && ($row->getDayDiff() == $day); $j++) {
                    $row = $rows[$j];
                    // we aggregate stream that has the same content on the same day.
                    // we should not however aggregate content that does not support
                    // multiple content. How do we detect? easy, they don't have
                    // {multiple} in the title string
                    // However, if the activity is from the same user, we only want
                    // to show the laste acitivity
                    if (($row->getDayDiff() == $day) && ($row->title == $title) && ($app == $row->app) && ($cid == $row->cid) && (
                            (JString::strpos($row->title, '{/multiple}') !== FALSE) ||
                            ($row->actor == $actor)
                        )

                        // Aggregate the content only if the like/comment is the same
                        // we only perform test on comment which shoould be enough
                        && ($commentTypeId == ($row->comment_type . $row->comment_id)) && $row->app != "photos"
                    ) {
                        // @rule: If an exclusion is added, we need to fetch activities without these items.
                        // Aggregated activities should also be excluded.
                        // $row->used = true;
                        $oRow->activities[] = $row;
                    }
                }

                $app = !empty($oRow->app) ? $this->_appLink($oRow->app, $oRow->actor, $oRow->target, $oRow->title) : '';

                $oRow->title = CString::str_ireplace('{app}', $app, $oRow->title);

                $favicon = '';

                // this should not really be empty
                if (!empty($oRow->app)) {
                    // Favicon override with group image for known group stream data
                    //if(in_array($oRow->app, CGroups::getStreamAppCode())){
                    if ($oRow->groupid) {
                        // check if the image icon exist in template folder
                        $favicon = JURI::root() . 'components/com_community/assets/favicon/groups.png';
                        if (JFile::exists(JPATH_ROOT . '/components/com_community/templates' . '/' . $config->get('template') . '/images/favicon/groups.png')) {
                            $favicon = JURI::root(true) . '/components/com_community/templates/' . $config->get('template') . '/images/favicon/groups.png';
                        }
                    }

                    // Favicon override with event image for known event stream data
                    // This would override group favicon
                    if ($oRow->eventid) {
                        // check if the image icon exist in template folder
                        $favicon = JURI::root() . 'components/com_community/assets/favicon/events.png';
                        if (JFile::exists(JPATH_ROOT . '/components/com_community/templates' . '/' . $config->get('template') . '/images/favicon/groups.png')) {
                            $favicon = JURI::root(true) . '/components/com_community/templates/' . $config->get('template') . '/images/favicon/events.png';
                        }
                    }

                    // If it is not group or event stream, use normal favicon search
                    if (!($oRow->groupid || $oRow->eventid)) {
                        // check if the image icon exist in template folder
                        if (JFile::exists(JPATH_ROOT . '/components/com_community/templates' . '/' . $config->get('template') . '/images/favicon' . '/' . $oRow->app . '.png')) {
                            $favicon = JURI::root(true) . '/components/com_community/templates/' . $config->get('template') . '/images/favicon/' . $oRow->app . '.png';
                        } else {
                            $CPluginHelper = new CPluginHelper();
                            // check if the image icon exist in asset folder
                            if (JFile::exists(JPATH_ROOT . '/components/com_community/assets/favicon' . '/' . $oRow->app . '.png')) {
                                $favicon = JURI::root(true) . '/components/com_community/assets/favicon/' . $oRow->app . '.png';
                            } elseif (JFile::exists($CPluginHelper->getPluginPath('community', $oRow->app) . '/' . $oRow->app . '/favicon.png')) {
                                $favicon = JURI::root(true) ."/". $CPluginHelper->getPluginURI('community', $oRow->app) . '/' . $oRow->app . '/favicon.png';
                            } else {
                                $favicon = JURI::root(true) . '/components/com_community/assets/favicon/default.png';
                            }
                        }
                    }
                } else {
                    $favicon = JURI::root(true) . '/components/com_community/assets/favicon/default.png';
                }

                $act->favicon = $favicon;

                //$target = $this->_targetLink($oRow->target, true);

                $act->title = $oRow->title;
                $act->id = $oRow->id;
                $act->cid = $oRow->cid;
                $act->title = $oRow->title;
                $act->actor = $oRow->actor;
                $act->actors = $oRow->actors;
                $act->target = $oRow->target;
                $act->access = $oRow->access;

                $timeFormat = $config->get('activitiestimeformat');
                $dayFormat = $config->get('activitiesdayformat');
                $date = CTimeHelper::getDate($oRow->created);

                // Do not modify created time
                // $createdTime = '';
                // if ($config->get('activitydateformat') == COMMUNITY_DATE_FIXED) {
                // 	$createdTime = $date->format($dayinterval == ACTIVITY_INTERVAL_DAY ? $timeFormat : $dayFormat, true);
                // } else {
                // 	$createdTime = CTimeHelper::timeLapse($date);
                // }

                $act->created = $oRow->created;
                $act->createdDate = $date->Format(JText::_('DATE_FORMAT_LC2'));
                $act->createdDateRaw = $oRow->created;
                $act->app = $oRow->app;
                $act->eventid = $oRow->eventid;
                $act->groupid = $oRow->groupid;
                $act->group_access = $oRow->group_access;
                $act->event_access = $oRow->event_access;
                $act->location = $oRow->getLocation();
                $act->commentCount = $oRow->getCommentCount();
                $act->commentAllowed = $oRow->allowComment();
                $act->commentLast = $oRow->getLastComment();
                $act->commentsAll = $oRow->getCommentsAll();
                $act->likeCount = $oRow->getLikeCount();
                $act->likeAllowed = $oRow->allowLike();
                $act->isFriend = $my->isFriendWith($act->actor);
                $act->isMyGroup = $my->isInGroup($oRow->groupid);
                $act->isMyEvent = $my->isInEvent($oRow->eventid);
                $act->userLiked = $oRow->userLiked($my->id);
                $act->latitude = $oRow->latitude;
                $act->longitude = $oRow->longitude;

                $act->params = (!empty($oRow->params)) ? $oRow->params : '';

                // Create and pass album, videos, groups, event object
                switch ($act->app) {
                    case 'photos':
                        // Album object
                        $act->album = JTable::getInstance('Album', 'CTable');
                        $act->album->load($act->cid);
                        $oRow->album = $act->album;
                        break;
                    case 'videos':
                        // Album object
                        $act->video = JTable::getInstance('Video', 'CTable');
                        $act->video->load($act->cid);
                        $oRow->video = $act->video;
                        break;
                }

                // get the content
                $act->content = $this->getActivityContent($oRow);
                //$act->title		= $this->getActivityTitle($oRow);
                $act->title = $oRow->title;
                $act->content = $oRow->content;

                $act->isFeatured = isset($oRow->isFeatured) ? $oRow->isFeatured: false;
                $htmlData[] = $act;
            }
        }

        $objActivity = new stdClass();
        $objActivity->data = $htmlData;

        return $objActivity;
    }

    /**
     * Return html formatted activity stream for apps stream
     *
     */
    public function getAppHTML($options) {
        // Default options
        $config = CFactory::getConfig();
        $default = array(
            'actor' => 0,
            'target' => 0,
            'app' => null,
            'cid' => null,
            'groupid' => null,
            'eventid' => null,
            'date' => null,
            'filter' => null,
            'latestId' => 0,
            'maxEntry' => $config->get('maxactivities'),
            'idprefix' => null,
            'showActivityContent' => true,
            'showMoreActivity' => true,
            'exclusions' => null,
            'displayArchived' => true
        );

        $options = array_merge($default, $options);
        extract($options);

        jimport('joomla.utilities.date');
        $mainframe = JFactory::getApplication();

        // Load the library needed
        $activities = CFactory::getModel('activities');
        $appModel = CFactory::getModel('apps');
        $html = '';
        $numLines = 0;
        $my = CFactory::getUser();
        $actorId = $actor;
        $htmlData = array();
        $tmpl = new CTemplate();

        // get the social object classname
        // It has to implement CStreamable
        $className = 'C' . ucfirst($app);
        $appLib = new $className();

        // Make sure the lib implement CStreamable
        if (!($appLib instanceof CStreamable)) {
            JFactory::getApplication()->enqueueMessage($className, 'error');
        }

        // Check if the current post is belong to the current user
        $isMine = false;
        $appCode = $appLib->getStreamAppCode();

        $maxList = ($maxEntry == 0) ? $config->get('maxactivities') : $maxEntry;

        $appId = (!$groupid) ? $eventid : $groupid;

        if (empty($filter)) {
            $filter = (!$groupid) ? 'active-event' : 'active-group';
        }

        $config = CFactory::getConfig();
        $isSuperAdmin = COwnerHelper::isCommunityAdmin();
        $isAppAdmin = $appLib->isAdmin($my->id, $appId);

        $data = $this->_getData(
            array(
                'actor' => $actor,
                'target' => $target,
                'date' => $date,
                'maxList' => $maxList,
                'app' => $appCode,
                'cid' => $cid,
                'groupid' => $groupid,
                'eventid' => $eventid,
                'exclusions' => $exclusions,
                'displayArchived' => $displayArchived));

        // We should also exclude any data that earlier (hence larger id) than any
        // of the current exclusion list
        $exclusions = isset($data->exclusions) ? $data->exclusions : null;
        $htmlData = $data->data;

        // Show welcome message on activity stream if this is a fresh installation
        if ($activities->getTotalActivities() == 0 && $my->id < 1) {
            $emptyActTmpl = new CTemplate();
            $freshInstall = $emptyActTmpl->fetch('activities/freshinstall');
            $tmpl->set('freshInstallMsg', $freshInstall);
        }

        //hide show more if there is no more results
        if ($activities->getTotalActivities() <= $config->get('maxactivities')) {
            $showMoreActivity = false;
        }

        $tmpl->set('showMoreActivity', $showMoreActivity)
            ->set('exclusions', $exclusions)
            ->set('isMine', $isMine)
            ->set('activities', $htmlData)
            ->set('idprefix', $idprefix)
            ->set('my', $my)
            ->set('apptype', $options['apptype'])
            ->set('isSuperAdmin', $isSuperAdmin)
            ->set('config', $config)
            ->set('showMore', $showActivityContent)
            ->set('filter', $filter)
            ->set('filterId', $appId)
            ->set('latestId', $latestId)
            ->set('groupId', $groupid)
            ->set('eventId', $eventid)
            ->set('isAppAdmin', $isAppAdmin)
            ->set('isMember', $appLib->isAllowStreamPost($my->id, $options));

        //if in module, disable Comment, Like
        if ($idprefix == '') {
            $showActivityComment = 1;
            $showActivityLike = 1;
        } else {
            $showActivityComment = 0;
            $showActivityLike = 0;
        }

        $data = $tmpl->set('showComment', $showActivityComment)
            ->set('showLike', $showActivityLike)
            ->fetch('stream/base');

        return $data;
    }

    /**
     *
     * @param  int $id       most recent stream id in browser
     * @param  string $filter   the filter name
     * @param  int $filterId either group, event of profileId, depending on filter
     * @return type
     */
    public function getLatestStreamCount($id, $filter, $filterId, $filterValue) {
        $my = CFactory::getUser();
        $activitiesModel = CFactory::getModel('activities');

        $actor = '';
        $friendsIds = '';
        $groupid = '';
        $eventid = '';

        $filters = array();
        /**
         * @todo It's temporary solution until we do all of these code refactor !!!
         * $filter is using to know filter type
         * $filterValue is using to know which value to do filter
         */
        if ($filter == 'privacy') {
            $filter = $filterValue;
        }
        switch ($filter) {
            case 'all':
                /* there are no filter here */
                break;
            case 'active-profile':
                $user = CFactory::getUser($filterId);
                $actor = $user->id;
                $target = $user->id;
                break;
            case 'me-and-friends':
                $actor = $my->id;
                $friendsIds = $my->getFriendIds();
            case "active-profile-and-friends" :
            case 'me-and-friends-activity':
                $friendIds = $my->getFriendIds();
                // add myself as a friend
                $actor = $my->id;
                break;

            case "active-user-and-friends" :
                break;
            case 'active-event':
                $eventid = $filterId;
                break;
            case 'active-group':
                $groupid = $filterId;
                break;
            case 'apps':
                $filters = array(
                    'apps' => array($filterValue)
                );
                break;
        }

        $result = $activitiesModel->countActivities($actor, $friendsIds, null, 0, true, $id * (-1), true, null, $groupid, $eventid, $filters);

        return $result;
    }

    /**
     * Return all the latest stream
     * @param  int $id       most recent stream id in browser
     * @param  string $filter   the filter name
     * @param  int $filterId either group, event of profileId, depending on filter
     * @return string           html of the stream
     */
    public function getLatestStream($id, $filter, $filterId = 0, $filterValue) {

        $my = CFactory::getUser();
        $activitiesModel = CFactory::getModel('activities');

        $actor = '';
        $friendsIds = '';
        $groupid = '';
        $eventid = '';

        $filters = array();
        if ($filter == 'privacy') {
            $filter = $filterValue;
        }
        switch ($filter) {
            case 'all':
                # code...
                break;

            case 'me-and-friends':
                $friendsIds = $my->getFriendIds();
                // add myself as a friend
                $actor = $my->id;
            case 'me-and-friends-activity':
                $friendsIds = $my->getFriendIds();
                // add myself as a friend
                $actor = $my->id;
                break;

            case "active-user-and-friends" :
            case "active-profile-and-friends" :
                $user = CFactory::getUser($filterId);
                $friendsIds = $user->getFriendIds();
                $actor = $user->id;
                break;

            case 'active-event':
                $eventid = $filterId;
                break;
            case 'active-group':
                $groupid = $filterId;
                break;
            case 'apps':
                $filters = array(
                    'apps' => array($filterValue)
                );
                break;
        }

        $result = $activitiesModel->getActivities($actor, $friendsIds, null, 0, true, $id * (-1), true, null, $groupid, $eventid, $filters);

        $tmpl = new CTemplate();
        $html = $tmpl->set('activities', $result)
            ->set('showLike', true)
            ->set('showMoreActivity', false)
            ->set('filter', $filter)
            ->set('groupId', $groupid)
            ->set('eventId', $eventid)
            ->set('filterId', $filterId)
            ->fetch('stream/base');

        return $html;
    }

    public function getOlderStream($id, $filter, $filterId = 0, $filterValue = null, $filters = array()) {
        $my = CFactory::getUser();
        $config = CFactory::getConfig();
        $activitiesModel = CFactory::getModel('activities');

        $actor = '';
        $friendsIds = '';
        $groupid = '';
        $eventid = '';

        if ($filter == 'privacy') {
            $filter = $filterValue;
        }
        switch ($filter) {
            case 'all':
                # code...
                break;
            case 'active-event':
                $session = JFactory::getSession();
                $session->set('com_community.filter', '');
                $session->set('com_community.filter.value', '');
                $eventid = $filterId;
                break;

            case 'active-group':
                $session = JFactory::getSession();
                $session->set('com_community.filter', '');
                $session->set('com_community.filter.value', '');
                $groupid = $filterId;
                break;

            case 'me-and-friends':
            case 'me-and-friends-activity':
                $friendsIds = $my->getFriendIds();
                // add myself as a friend
                $actor = $my->id;
                //exit;
                break;

            case "active-user-and-friends" :
            case "active-profile-and-friends" :
                break;
            case "active-profile":
                $session = JFactory::getSession();
                $session->set('com_community.filter', '');
                $session->set('com_community.filter.value', '');
                $actor = $filterId;
                break;
            case 'apps':
                $filters = array_merge($filters, array(
                    'apps' => array($filterValue)
                ));
                break;
            case 'hashtag':
                $filters = array_merge($filters, array(
                    'hashtag' => $filterValue
                ));
                break;
            default:
                # code...
                break;
        }
#var_dump($filters);die();
        //arrange by id might not be accurate, use created time instead
        $activity = JTable::getInstance('Activity', 'CTable');
        $activity->load($id);
        $filters['beforeDate'] = $activity->created;

        $filters['show_featured'] = true;
        $result = $activitiesModel->getActivities($actor, $friendsIds, null, $config->get('maxactivities'), true, $id, true, null, $groupid, $eventid, $filters);

        foreach ($result as $key => $val) {
            if (is_object($val->params)) {
                if (($val->params->get('action') == 'group.create' && $filter == 'active-group') || ($val->params->get('action') == 'events.create' && $filter == 'active-event' )) {
                    unset($result[$key]);
                }
            }
        }

        $showMoreActivity = false;
        // show more if there is more results
        if (count($result) > 0) {
            $showMoreActivity = true;
        }


        $tmpl = new CTemplate();
        $html = $tmpl->set('activities', $result)
            ->set('showLike', true)
            ->set('showMoreActivity', $showMoreActivity)
            ->set('filter', $filter)
            ->set('groupId', $groupid)
            ->set('eventId', $eventid)
            ->set('filterId', $filterId)
            ->fetch('stream/base');

        return $html;
    }

    /**
     *
     * Return html formatted activity stream
     * @access     public
     * @todo    Add caching    - Improve performance via caching
     *
     * @param type : can be a single string or array or string
     * @param type $actor
     * @param type $target
     * @param type $date
     * @param type $maxEntry
     * @param type $type
     * @param type $idprefix
     * @param type $showActivityContent
     * @param boolean $showMoreActivity
     * @param type $exclusions
     * @param type $displayArchived
     * @param type $filter
     * @param type $latestId
     * @param type $options
     * @return type
     */
    public function getHTML($actor, $target, $date = null, $maxEntry = 0, $type = '', $idprefix = '', $showActivityContent = true, $showMoreActivity = false, $exclusions = null, $displayArchived = false, $filter = 'all', $latestId = 0, $options = array()) {
        jimport('joomla.utilities.date');

        $mainframe = JFactory::getApplication();
        $jinput = $mainframe->input;
        $activities = CFactory::getModel('activities');
        $config = CFactory::getConfig();
        $my = CFactory::getUser();
        $htmlData = array();
        $tmpl = new CTemplate();
        $isSuperAdmin = COwnerHelper::isCommunityAdmin();

        //maxlist from profile will respect the max activities on the profile settings
        $targetUser = CFactory::getUser($actor);
        $params = $targetUser->getParams();

        if ($maxEntry == 0) {
            $maxList = ($type == 'profile') ? $params->get('activityLimit', $config->get('maxactivities')) : $config->get('maxactivities');
        } else {
            $maxList = $maxEntry;
        }

        /**
         * Get activities
         */
        $actid = $jinput->get('actid', null, 'INT');
        $default = array(
            'actid' => $actid,
            'actor' => $actor,
            'target' => $target,
            'date' => $date,
            'maxList' => $maxList,
            'type' => $type,
            'exclusions' => $exclusions,
            'displayArchived' => $displayArchived);
        $options = array_merge($default, $options);
        $data = $this->_getData($options);

        // Triggers to filter data to display on stream
        $apps = CAppPlugins::getInstance();
        $apps->loadApplications();
        $apps->triggerEvent('onActivityDisplay', (array)$data);

        $htmlData = $data->data;

        $totalActivities = $activities->getTotalActivities($options);
        // Show welcome message on activity stream if this is a fresh installation
        if ($totalActivities == 0 && $my->id < 1) {
            $emptyActTmpl = new CTemplate();
            $freshInstall = $emptyActTmpl->fetch('activities/freshinstall');
            $tmpl->set('freshInstallMsg', $freshInstall);
        }

        // hide show more if there is no more results
        if (($type == 'frontpage' && $totalActivities <= $maxList) || ($type == 'profile' && count($htmlData) <= $maxList)) {
            $showMoreActivity = false;
        }

        $tmpl
            ->set('showMoreActivity', $showMoreActivity)
            ->set('activities', $htmlData)
            ->set('idprefix', $idprefix)
            ->set('my', $my)
            ->set('isSuperAdmin', $isSuperAdmin)
            ->set('config', $config)
            ->set('showMore', $showActivityContent)
            ->set('filter', $filter)
            ->set('latestId', $latestId);


        // set up the filter id
        $filterId = 0;
        switch ($filter) {
            case "active-profile" :
                $profileUserId = $jinput->getInt('userid', $my->id);
                $activeProfile = CFactory::getUser($profileUserId);
                $filterId = $activeProfile->id;
                break;

            case "me-and-friends" :
                $filterId = $my->id;
                break;

            case "active-user-and-friends" :
            case "active-profile-and-friends" :
                $filterId = $my->id;
                break;

            case "all":
            default :
                break;
        }
        $tmpl->set('filterId', $filterId);

        //if in module, disable Comment, Like
        if ($idprefix == '') {
            $showActivityComment = 1;
            $showActivityLike = 1;
        } else {
            $showActivityComment = 0;
            $showActivityLike = 0;
        }

        $data = $tmpl
            ->set('showComment', $showActivityComment)
            ->set('showLike', $showActivityLike)
            ->set('actorId', $actor)
            ->set('isSingleActivity',$actid)
            ->fetch('stream/base');

        return $data;
    }

    /**
     * Return array of rss-feed compatible data
     */
    public function getFEED($actor, $target, $date = null, $maxEntry = 20, $type = '') {
        jimport('joomla.utilities.date');
        $mainframe = JFactory::getApplication();

        $activities = CFactory::getModel('activities');
        $appModel = CFactory::getModel('apps');
        $html = '';
        $numLines = 0;
        $my = CFactory::getUser();
        $actorId = $actor;
        $feedData = array();

        $htmlData = $this->_getData(
            array('actor' => $actor,
                'target' => $target,
                'date' => $date,
                'maxList' => $maxEntry,
                'type' => $type));
        return $htmlData;
    }

    /**
     * Return how many days has lapse since
     * @param    JDate date The date you want to compare
     * @access     private
     */
    static private function _daysLapse($date) {
        require_once (JPATH_COMPONENT . '/helpers/time.php');
        $now = JDate::getInstance();

        $html = '';
        $diff = CTimeHelper::timeDifference($date->toUnix(), $now->toUnix());
        return $diff['days'];
    }

    /**
     * Return html formatted lapse time
     * @param    JDate date The date you want to compare
     * @param boolean $showFull default to be true to show xx Hours xx Minutes, if false will only show Hours or Minutes
     * @access     private
     */
    static public function _createdLapse(&$date, $showFull = true) {


        $now = JDate::getInstance();
        $html = '';
        $diff = CTimeHelper::timeDifference($date->toUnix(), $now->toUnix());


        if (!empty($diff['days'])) {
            $days = $diff['days'];
            $months = ceil($days / 30);

            switch ($days) {
                case ($days == 1):

                    // @rule: Something that happened yesterday
                    $html .= JText::_('COM_COMMUNITY_LAPSED_YESTERDAY');

                    break;
                case ($days > 1 && $days <= 7 && $days < 30):

                    // @rule: Something that happened within the past 7 days
                    $html .= JText::sprintf('COM_COMMUNITY_LAPSED_DAYS', $days) . ' ';

                    break;
                case ($days > 1 && $days > 7 && $days < 30):

                    // @rule: Something that happened within the month but after a week
                    $weeks = round($days / 7);
                    $html .= JText::sprintf(CStringHelper::isPlural($weeks) ? 'COM_COMMUNITY_LAPSED_WEEK_MANY' : 'COM_COMMUNITY_LAPSED_WEEK', $weeks) . ' ';

                    break;
                case ($days > 30 && $days < 365):

                    // @rule: Something that happened months ago
                    $months = round($days / 30);
                    $html .= JText::sprintf(CStringHelper::isPlural($months) ? 'COM_COMMUNITY_LAPSED_MONTH_MANY' : 'COM_COMMUNITY_LAPSED_MONTH', $months) . ' ';

                    break;
                case ($days > 365):

                    // @rule: Something that happened years ago
                    $years = round($days / 365);
                    $html .= JText::sprintf(CStringHelper::isPlural($years) ? 'COM_COMMUNITY_LAPSED_YEAR_MANY' : 'COM_COMMUNITY_LAPSED_YEAR', $years) . ' ';

                    break;
            }
        } else {
            // We only show he hours if it is less than 1 day
            if (!empty($diff['hours'])) {
                if (!empty($diff['minutes'])) {
                    $html .= JText::sprintf('COM_COMMUNITY_LAPSED_HOURS', $diff['hours']) . ' ';
                } else {
                    $html .= JText::sprintf('COM_COMMUNITY_LAPSED_HOURS_AGO', $diff['hours']) . ' ';
                }
            }

            if (($showFull && !empty($diff['hours'])) || (empty($diff['hours']))) {
                if (!empty($diff['minutes']))
                    $html .= JText::sprintf(CStringHelper::isPlural($diff['minutes']) ? 'COM_COMMUNITY_LAPSED_MINUTES' : 'COM_COMMUNITY_LAPSED_MINUTE', $diff['minutes']) . ' ';
            }
        }

        if (empty($html)) {
            $html .= JText::_('COM_COMMUNITY_LAPSED_LESS_THAN_A_MINUTE');
        }

        if ($html != JText::_('COM_COMMUNITY_LAPSED_YESTERDAY'))
            //$html .= JText::_('COM_COMMUNITY_LAPSED_AGO');
            return $html;
    }

    /**
     * Return html formatted link to actor
     * @param    integer id Actor/user id
     * @access     private
     */
    private function _actorLink($id) {
        static $instances = array();
        $mainframe = JFactory::getApplication();
        $jinput = $mainframe->input;

        if (empty($instances[$id])) {
            $my = JFactory::getUser();
            $view = $jinput->request->get('view', 'frontpage', 'NONE');
            $format = $jinput->request->get('format', 'html', 'NONE');
            $linkName = ($id == 0) ? false : true;
            $user = CFactory::getUser($id);
            $name = $user->getDisplayName();

            // Wrap the name with link to his/her profile
            $html = $name;

            if ($linkName) {
                $html = '<a class="actor-link" href="' . CUrlHelper::userLink($id) . '">' . $name . '</a>';
            }

            $instances[$id] = $html;
        }

        return $instances[$id];
    }

    /**
     * Return html formatted link to target
     * @param    integer id Target/user id
     * @access     private
     */
    private function _targetLink($id, $onApp = false) {
        static $instances = array();

        if (empty($instances[$id])) {

            $my = JFactory::getUser();
            $linkName = ($id == 0) ? false : true;

// 		if(($id == $my->id) && ($id == $user->id)){
// 			$name = $onApp ? 'your' : 'you';
// 			$linkName = false;
// 		} else
            //{
            $user = CFactory::getUser($id);
            $name = $user->getDisplayName();
            //}
            // Wrap the name with link to his/her profile
            $html = $name;
            if ($linkName)
                $html = '<a href="' . CUrlHelper::userLink($id) . '">' . $name . '</a>';

            $instances[$id] = $html;
        }
        return $instances[$id];
    }

    /**
     * Perform necessary formatting on the title for display in the stream
     * @param type $row
     */
    private function _formatTitle($row) {
        // We will need to replace _QQQ_ here since
        // CKses will reformat the link
        $row->title = CTemplate::quote($row->title);

        // If the title start with actor's name, and it is a normal status, remove them!
        // Otherwise, leave it as the old style
        if (strpos($row->title, '<a class="actor-link"') !== false && isset($row->actor) && ($row->app == 'profile')) {
            $pattern = '/(<a class="actor-link".*?' . '>.*?<\/a>)/';
            $row->title = preg_replace($pattern, '', $row->title);
        }

        return CKses::kses($row->title, CKses::allowed());
    }

    /**
     * Return html formatted link to application
     * @param    integer id Actor/user id
     * @access     private
     * @todo    Add link to known application/views
     */
    private function _appLink($name, $actor = 0, $userid = 0, $title = '') {

        if (empty($name))
            return '';

// 		if( empty($instances[$id.$actor.$userid]) )
// 		{
        $appModel = CFactory::getModel('apps');
        $url = '';

        // @todo: check if this app exist
        if (true) {
            // if no target specified, we use actor
            if ($userid == 0)
                $userid = $actor;

            $excludeName = array(
                'profile',
                'news_feed',
                'photos',
                'friends',
                'videos'
            );

            if (!in_array($name,$excludeName)) {

                $url = CUrlHelper::userLink($userid) . '#app-' . $name;
                if ($title == JText::_('COM_COMMUNITY_ACTIVITIES_APPLICATIONS_REMOVED')) {
                    $url = $appModel->getAppTitle($name);
                } else {
                    $url = '<a href="' . $url . '" >' . $appModel->getAppTitle($name) . '</a>';
                }
            } else {
                $url = $appModel->getAppTitle($name);
            }
        }
        return $url;
    }

    /**
     * Retrieve a list of custom activities which the admin can push
     *
     * @return  Array   An array of custom activities
     * */
    static public function getCustomActivities() {
        // These are default activities pre-defined by the system
        $messages = array();
        $messages['system.registered'] = JText::sprintf('COM_COMMUNITY_LAST_10_USERS_REGISTERED');
        $messages['system.populargroup'] = JText::sprintf('COM_COMMUNITY_ACTIVITIES_POPULAR_GROUP');
        $messages['system.totalphotos'] = JText::sprintf('COM_COMMUNITY_ACTIVITIES_TOTAL_PHOTOS');
        $messages['system.popularprofiles'] = JText::sprintf('COM_COMMUNITY_ACTIVITIES_TOP_PROFILES', 5);
        $messages['system.popularphotos'] = JText::sprintf('COM_COMMUNITY_ACTIVITIES_TOP_PHOTOS', 5);
        $messages['system.popularvideos'] = JText::sprintf('COM_COMMUNITY_ACTIVITIES_TOP_VIDEOS', 5);

        // Triggers to allow 3rd party to push their custom messages as well.
        $apps = CAppPlugins::getInstance();
        $apps->loadApplications();

        $args = array();
        $args[] = $messages;

        $apps->triggerEvent('onCustomActivityDisplay', $args);

        return $messages;
    }

    /**
     *
     * @param type $filter
     * @param type $userId
     * @param type $view
     * @param type $showMore
     * @return type
     */
    static public function getActivitiesByFilter($filter = 'all', $userId = 0, $view = '', $showMore = true, $filters = array(), $extra = false) {
        jimport('joomla.utilities.date');
        $config = CFactory::getConfig();

        $act = new CActivityStream();

        if ($userId == 0) {
            // Legacy code, some module might still use the old code
            $user = CFactory::getRequestUser();
        } else {
            $user = CFactory::getUser($userId);
        }

        $memberSince = CTimeHelper::getDate($user->registerDate);
        $friendIds = $user->getFriendIds();

        /**
         * Filter
         * @todo This's applied into the old code we need improve it later
         */
        switch ($filter) {
            case 'photo':
            case 'group':
            case 'status':
            case 'video':
            case 'event' :
                //$html = $act->getHTML('', '', null, 0, $view, '', true, $showMore, null, false, 'all', 0);
                break;
            case "active-profile" :
                $target = array($user->id);
                $params = $user->getParams();
                $actLimit = ($view == 'profile') ? $params->get('activityLimit', $config->get('maxactivities')) : $config->get('maxactivities');
                $html = $act->getHTML($user->id, $target, '', $actLimit, $view, '', true, $showMore, null, false, 'active-profile', 0, $filters, $extra);
                break;

            case "me-and-friends" :
                $user = JFactory::getUser();
                $html = $act->getHTML($user->id, $friendIds, $memberSince, 0, $view, '', true, $showMore, null, false, 'me-and-friends', null, array(), $extra);
                break;

            case "active-user-and-friends" :
            case "active-profile-and-friends" :
                $params = $user->getParams();
                $actLimit = ($view == 'profile') ? $params->get('activityLimit', $config->get('maxactivities')) : $config->get('maxactivities');
                $html = $act->getHTML($user->id, $friendIds, $memberSince, $actLimit, $view, '', true, $showMore, null, false, 'active-profile-and-friends', null, array(), $extra);
                break;
            case "all":
            default :
                $html = $act->getHTML('', '', null, 0, $view, '', true, $showMore, null, false, 'all', 0, $filters, $extra);
                break;
        }

        return $html;
    }

    /**
     * Remove activities by the given apps and wall id
     *
     * @param type $option array search criteria.
     * @param type $wallId int Wall post id.
     * @since 2.4
     *
     */
    static public function removeWallActivities($option, $wallId) {
        // Return all activities by the given apps and specific criteria.
        $activitiesModel = CFactory::getModel('activities');
        $activities = $activitiesModel->getAppActivities($option);

        // Generate target activity id from param's wall id
        $activityID = 0;

        foreach ($activities as $objAct) {

            if ($objAct->params->get('wallid') == $wallId) {
                $activityID = $objAct->id;
                break;
            }
        }

        // Remove activity.
        if ($activityID > 0) {
            $activity = JTable::getInstance('Activity', 'CTable');
            $activity->load($activityID);
            $activity->delete($option['app']);
        }
    }

    /**
     * General purpose stream formatting function
     */
    static public function format($str, $mood = null) {
        // Some database actually already stored some URL already linked! Such as @mention format
        // To handle this, we strip to to the base format. and apply the linking later
        $str = preg_replace('|@<a href="(.*?)".*>(.*)</a>|', '@${2}', $str);

        //Strip html href tag
        $str = preg_replace('|<a href="(.*?)".*>(.*)</a>|', '${1}', $str);

        // Escape it first
        $str = CStringHelper::escape(rtrim(str_replace('&nbsp;', '', $str)));

        $str = str_replace('&amp;quot;','"',$str);

        // Autolink url
        $str = CStringHelper::autoLink($str);

        // Nl2Br
        $str = nl2br($str);

        // Autolinked username
        $str = CUserHelper::replaceAliasURL($str);

        $str = CStringHelper::getEmoticon($str);

        $str = CStringHelper::getMood($str, $mood);

        $str = CStringHelper::converttagtolink($str);

        //onstream comment filter

        // onMessageDisplay Event trigger
        if($str) {
            $appsLib = CAppPlugins::getInstance();
            $appsLib->loadApplications();
            $strObj = new stdClass();
            $strObj->body = $str;
            $arg[] = $strObj;
            $appsLib->triggerEvent('onFormatConversion', $arg);

            $str = $arg[0]->body; // reassign back to string
        }

        return $str;
    }


    static public function shorten($str, $id, $isSingleAct=false, $limit=0,  $type='stream')
    {
        $more = JText::_('COM_COMMUNITY_SHOW_MORE');
        $less = JText::_('COM_COMMUNITY_SHOW_LESS');

        $html = '';

        if ( $limit > 0 && strlen(str_replace('  ',' ', strip_tags($str))) > $limit ) {
            $strFull = $str;
            $str = self::truncatecomplex($str, $limit, true);

            // Remove broken tag like <b, <br, <spa, etc..
            $str = preg_replace('/<[a-z][^>]*$/i', '', $str);

            // @todo Close unclosed tag.
        }

        // STREAM
        if($type == 'stream') {

            $html .= '<span class="joms-js--'.$type.'-text-' . $id . '">' . $str . '</span>';

            if(isset($strFull) && ($strFull !== $str)) {

                $html .= '<span class="joms-js--stream-textfull-' . $id . '" style="display:none">' . $strFull . '</span>
            <a href="javascript:" class="joms-js--stream-texttoggle-' . $id . '"
               data-lang-more="' . $more . '"
               data-lang-less="' . $less . '"
               onclick="joms.api.streamToggleText(' . $id . ');">' . $more . '</a>';
            }
        }

        // COMMENT
        if($type == 'comment') {

            $html .= '<span class="joms-js--'.$type.'-text-' . $id . '">' . $str . '</span>';

            if(isset($strFull) && ($strFull !== $str)) {

                $html .= '<span class="joms-js--comment-textfull-' . $id . '" style="display:none">' . $strFull . '</span>
                    <a href="javascript:" class="joms-js--comment-texttoggle-' . $id . '"
                       data-lang-more="' . $more . '"
                       data-lang-less="' . $less . '"
                       onclick="joms.api.commentToggleText(' . $id . ');">' . $more . '</a>';
            }
        }



return $html;
}

static public function removeActor($activity, $params = '') {
    $table = JTable::getInstance('Activity', 'CTable');
    $table->load(array('app' => $activity->app, 'cid' => $activity->cid, 'archived' => 0));

    // Use newer params, but update the actor list
    $currentParam = new JRegistry($table->params);
    $newParam = new JRegistry($params);
    $actors = CCSV::remove($currentParam->get('actors'), $activity->actor);
    $newParam->set('actors', $actors);
    $params = $newParam->toString();

    if (!empty($actors)) {
        // Add actors to actors list, so that it appear on personal stream
        // remove duplicate if it is already there
        $actors = new stdClass();

        //$table->actors = str_replace('{"id":"'.$activity->actor.'"}', '""', $table->actors); // avoid duplicate.
        $actors = json_decode($table->actors);
        if (!is_object($actors) && empty($actors)) {
            $actors = new stdClass();
        }
        $actors->userid = (!empty($actors->userid)) ? array_filter($actors->userid) : array(); // clear out empty data
        $actors->userid[] = array('id' => $activity->actor);
        $activity->actors = json_encode($actors);

        // Set the activity actor to 0, hence, it won't appear on personal stream
        // only in public stream
        $activity->actor = 0;

        CActivityStream::add($activity, $params, 0);
    }

    // Delete old ones
    if (!is_null($table->id))
        $table->delete();
}

static public function formatSharePopup($act) {
    switch ($act->app) {
        case 'photos':
            $album = JTable::getInstance('Album', 'CTable');
            $album->load($act->cid);

            $act->album = $album;
            $act->params = new CParameter($act->params);
            $act->content = CPhotos::getActivityContentHTML($act);
            break;
        case 'videos':
            $video = JTable::getInstance('Video', 'CTable');
            $video->load($act->cid);

            $act->title = $video->title;
            $act->content = new stdClass();
            $act->content->title = $video->title;
            $act->content->duration = CVideosHelper::toNiceHMS(CVideosHelper::formatDuration($video->getDuration()));
            $act->content->thumb = $video->getThumbnail();
            $act->content->description = $video->description;
            break;
        case 'events.wall':
        case 'groups.wall':
        case 'profile':
            $param = new CParameter($act->params);
            $metas = $param->get('headMetas', NULL);
            $headers = new CParameter($metas);

            if (!is_null($headers->get('title'))) {
                $act->content = new stdClass();
                $act->content->title = $headers->get('title');
                $act->content->description = $headers->get('description');
                $act->content->thumb = $headers->get('image');
            }

            break;
        case 'groups':
            $group = JTable::getInstance('Group', 'CTable');
            $group->load($act->cid);

            $act->title = $group->name;
            $act->content = new stdClass();
            $act->content->title = $group->name;
            $act->content->description = $group->description;
            $act->content->thumb = $group->getThumbAvatar();
            break;
        case 'events':
            $event = JTable::getInstance('Event', 'CTable');
            $event->load($act->cid);

            $act->title = $event->title;
            $act->content = new stdClass();
            $act->content->title = $event->title;
            $act->content->description = $event->description;
            $act->content->thumb = $event->getThumbAvatar();
            break;
    }

    return self::generateShareHtml($act);
}

static public function generateShareHtml($act) {

    $param = new CParameter($act->params);
    $metas = $param->get('headMetas', NULL);
    $headers = new CParameter($metas);

    if (is_object($act->content) && ($act->app == 'cover.upload')) {
        $html = '';
        $html .= '<div class="joms-fetch-shared row-fluid">';
        $html .= '<div class="span12"><div class="joms-stream-single-photo joms-stream-box"><img src="' . $act->content->thumb . '" ></div></div></div>';

        // trim long description
        $description = JHTML::_('string.truncate', $act->content->description, CFactory::getConfig()->get('streamcontentlength'), true, false);
        $html .= '<span>' . $description . '</span>';

        unset($act->content);
        $act->content = $html;
    }

    if (is_object($act->content) && ($act->app == 'groups') || ($act->app == 'events')) {

        // $iconType = '';
        // if ($act->app == 'groups') {
        //     $iconType = 'joms-icon-users';
        // } else {
        //     $iconType = 'joms-icon-calendar-empty';
        // }

        $html = '';

        $html .='<h4 class="joms-text--title">' . $act->content->title . '</h5>';
        $html .='<span>' . $act->content->description . '</span>';



        unset($act->content);
        $act->content = $html;
    }

    if (is_object($act->content) && ($act->app == 'videos')) {
        $html = '';
        $html .='<div class="joms-media--video">';
        $html .='<div class="joms-media__thumbnail">';
        $html .='<img src="' . $act->content->thumb . '" ></div>';
        $html .='<div class="joms-media__body">';
        $html .='<h4 class="joms-media__title">' . JHTML::_('string.truncate', $act->content->title, 50 , true, false)  . '</h4>';
        $html .='<p class="joms-media__desc">' . JHTML::_('string.truncate', $act->content->description, CFactory::getConfig()->getInt('streamcontentlength'), true, false)  . '</p>';
        $html .='</div>';
        $html .='</div>';

        unset($act->content);
        $act->content = $html;
    }

    if (is_object($act->content)) {
        $config = CFactory::getConfig();

        if($config->get('enable_embedly') && $headers->get('link')) {
            $html = '<a href="'.$headers->get('link').'" class="embedly-card" data-card-controls="0" data-card-recommend="0" data-card-theme="'.$config->get('enable_embedly_card_template').'" data-card-align="'.$config->get('enable_embedly_card_position').'">
            '.JText::_('COM_COMMUNITY_EMBEDLY_LOADING').'</a>';
        }else{
            $html = '';
            $html .='<div class="joms-media--album">';
            $html .='<div class="joms-media__thumbnail"><img src="' . $act->content->thumb . '"  ></div>';
            $html .='<div class="joms-media__body">';
            $html .='<a class="joms-media__title" href="' . $headers->get('link', NULL) . '"' . (CFactory::getConfig()->get('newtab', false) ? ' target="_blank"' : '') . '>' . $act->content->title . '</a>';
            $html .='<p class="joms-media__desc">' . $act->content->description . '<p>';
            $html .='</div>';
            $html .='</div>';
        }

        unset($act->content);
        $act->content = $html;
    }

    if($headers->get('type') == 'video') {
        $video = JTable::getInstance('Video', 'CTable');
        if ($video->init($headers->get('link'))) {
            $video = false;
        }

        if (is_object($video)) {

            $html = '';

            ob_start();
            ?>
            <div class="joms-media--video joms-js--video"
                 data-type="<?php echo $video->type; ?>"
                 data-id="<?php echo $video->video_id; ?>"
                 data-path="<?php echo ($video->type == 'file') ? CStorage::getStorage($video->storage)->getURI($video->path) : $video->path; ?>">

                <div class="joms-media__thumbnail">
                    <img src="<?php echo $video->getThumbnail(); ?>">
                    <a href="javascript:" class="mejs-overlay mejs-layer mejs-overlay-play joms-js--video-play joms-js--video-play-<?php echo $act->id; ?>">
                        <div class="mejs-overlay-button"></div>
                    </a>
                </div>
                <div class="joms-media__body">
                    <h4 class="joms-media__title">
                        <?php echo JHTML::_('string.truncate', $video->title, 50, true, false); ?>
                    </h4>
                    <p class="joms-media__desc">
                        <?php echo JHTML::_('string.truncate', $video->description, CFactory::getConfig()->getInt('streamcontentlength'), true, false); ?>
                    </p>
                </div>

            </div>
            <?php
            $html = ob_get_contents();
            ob_end_clean();
            unset($act->content);
            $act->content = $html;
        }
    }

    return $act;
}

static public function formatStreamAttachment($obj) {
    switch ($obj->app) {

        case 'videos.linking':
            $video = JTable::getInstance( 'Video' , 'CTable' );
            $video->load( $obj->cid );
            $attachment = new stdClass();
            $attachment->type = 'videos.linking';
            $attachment->type = 'video';
            $attachment->id = $obj->cid;
            $attachment->title = $video->title;
            $attachment->thumbnail = $video->getThumbnail();
            $attachment->description = $video->description;
            $attachment->duration = CVideosHelper::toNiceHMS(CVideosHelper::formatDuration($video->getDuration()));
            $attachment->access = $obj->access;
            $attachment->video_type = $video->type;
            $attachment->link = $video->path;
            $attachment->video = $video;
            break;

        case 'profile.status.share':
            $params = new CParameter($obj->params);
            $act = JTable::getInstance('Activity', 'CTable');
            $act->load($params->get('activityId'));
            $attachment = self::formatStreamAttachment($act);
            break;

        case 'groups.wall':
        case 'events.wall':
        case 'profile':
            $params = new CParameter($obj->params);
            $headMetas = $params->get('headMetas', NULL);
            $headers = new CParameter($headMetas);

            if (!is_null($headers->get('title'))) {
                $attachment = new stdClass();
                $attachment->type = 'fetched';

                $data = new stdClass();
                $headers = new CParameter($headMetas);
                $data->title = $headers->get('title');
                $data->description = $headers->get('description');
                $data->thumb = $headers->get('image');
                $data->app = $obj->app;
                $data->params = $obj->params;

                $attachment->message = CActivityStream::formatSharePopup($data)->content;
            } else {
                $attachment = new stdClass();
                $attachment->type = 'quote';
                $attachment->id = $obj->id;
                $attachment->location = $obj->location;
                $attachment->message = CActivities::format($obj->title,$params->get('mood'));
            }
            break;

        case 'videos':
            $video = JTable::getInstance('Video', 'CTable');
            $video->load( $obj->cid );
            $attachment = new stdClass();
            $attachment->type = 'video';
            $attachment->id = $obj->cid;
            $attachment->title = $video->title;
            $attachment->thumbnail = $video->getThumbnail();
            $attachment->description = $video->description;
            $attachment->duration = CVideosHelper::toNiceHMS(CVideosHelper::formatDuration($video->getDuration()));
            $attachment->link = $video->getURL();
            $attachment->video_type = $video->type;
            $attachment->link = $video->path;
            $attachment->video = $video;
            break;

        case 'photos':
            $params = new CParameter($obj->params);
            $count = $params->get('count', 1);
            $photoId = $params->get('photoid', 0);
            $photoIds = explode(',', $params->get('photosId', 0));
            $attachment = new stdClass();

            if ($count == 1 && $photoId > 0) {
                $attachment->type = 'photo';
                $photo = JTable::getInstance('Photo', 'CTable');
                if ($photo->load($photoId) && $photo->status != 'delete') {
                    $attachment->singlephoto = $photo->getImageURI();
                    $attachment->caption = $photo->caption;
                    $attachment->thumbnail = $photo->getThumbURI();
                    $attachment->link = $params->get('photo_url');
                    $attachment->albumid = $photo->albumid;
                    $attachment->id = $photo->id;
                }
            } elseif ($count > 1 && $photoId > 0) {
                $attachment->type = 'photos';
                $album = JTable::getInstance('Album', 'CTable');
                $album->load($obj->cid);

                if (count($photoIds) > 1) {
                    foreach ($photoIds as $pid) {

                        $photo = JTable::getInstance('Photo', 'CTable');
                        $photo->load($pid);
                        /* Make sure photo is not deleted */
                        if ($photo->status != 'delete') {
                            $photos[] = $photo;
                        }
                        foreach ($photos as $key => $data) {
                            if ($data->id == $photoId) {
                                unset($photos[$key]); /* remove this photo */
                                array_unshift($photos, $data); /* move it to beginning of array */
                            }
                        }
                    }
                } else {
                    $photos = $album->getLatestPhoto($count);
                }

                $tmpIdArray = array();
                $tmpAlbumArray = array();
                $tmpUrlArray = array();
                $tmpThumbArray = array();
                $tmpCaptionArray = array();

                if ($count >= 5) {
                    $photos = array_slice($photos, 0, 5);
                }

                foreach ($photos as $photo) {
                    $tmpIdArray[] = $photo->id;
                    $tmpAlbumArray[] = $photo->albumid;
                    $tmpThumbArray[] = $photo->getImageURI();
                    $tmpUrlArray[] = $photo->getPhotoLink();
                    $tmpCaptionArray[] = $photo->caption;
                }

                $attachment->id = $tmpIdArray;
                $attachment->album = $tmpAlbumArray;
                $attachment->link = $tmpUrlArray;
                $attachment->thumbnail = $tmpThumbArray;
                $attachment->caption = $tmpCaptionArray;
            }
            break;
        case 'groups':
            $attachment = new stdClass();
            $attachment->type = 'group_share';

            $group = JTable::getInstance('Group', 'CTable');
            $group->load($obj->cid);
            $attachment->message = new stdClass;

            $attachment->message->title = $group->name;
            $attachment->message->description = $group->description;
            $attachment->message->link = $group->getLink();
            break;
        case 'events':
            $attachment = new stdClass();
            $attachment->type = 'event_share';

            $event = JTable::getInstance('Event', 'CTable');
            $event->load($obj->cid);

            $attachment->message = $event;
            break;
        case 'cover.upload':
            $params = new CParameter($obj->params);
            $attachment = new stdClass();
            $attachment->type = 'cover';
            $attachment->thumbnail = $params->get('attachment');
            break;
        case 'profile.avatar.upload':
            $params = new CParameter($obj->params);
            $attachment = new stdClass();
            $attachment->type = 'profile_avatar';
            $attachment->thumbnail = $params->get('attachment');
            break;
        default:
            $attachment = new stdClass();
            $attachment->content = isset($obj->content) ? $obj->content : '';
            $attachment->showInQuote = true; // to show in quote format in stream/base-extended template.
            $attachment->type = 'general';
            break;
    }

    return $attachment;
}



    /** @todo REMOVE AFTER DROPPING 2.5 SUPPORT **/
    public static function truncateComplex($html, $maxLength = 0, $noSplit = true)
    {
        // Start with some basic rules.
        $baseLength = strlen($html);

        // If the original HTML string is shorter than the $maxLength do nothing and return that.
        if ($baseLength <= $maxLength || $maxLength == 0)
        {
            return $html;
        }

        // Take care of short simple cases.
        if ($maxLength <= 3 && substr($html, 0, 1) != '<' && strpos(substr($html, 0, $maxLength - 1), '<') === false && $baseLength > $maxLength)
        {
            return '...';
        }

        // Deal with maximum length of 1 where the string starts with a tag.
        if ($maxLength == 1 && substr($html, 0, 1) == '<')
        {
            $endTagPos = strlen(strstr($html, '>', true));
            $tag = substr($html, 1, $endTagPos);

            $l = $endTagPos + 1;

            if ($noSplit)
            {
                return substr($html, 0, $l) . '</' . $tag . '...';
            }

            // TODO: $character doesn't seem to be used...
            $character = substr(strip_tags($html), 0, 1);

            return substr($html, 0, $l) . '</' . $tag . '...';
        }

        // First get the truncated plain text string. This is the rendered text we want to end up with.
        $ptString = JHtml::_('string.truncate', $html, $maxLength, $noSplit, $allowHtml = false);

        // It's all HTML, just return it.
        if (strlen($ptString) == 0)
        {
            return $html;
        }

        // If the plain text is shorter than the max length the variable will not end in ...
        // In that case we use the whole string.
        if (substr($ptString, -3) != '...')
        {
            return $html;
        }

        // Regular truncate gives us the ellipsis but we want to go back for text and tags.
        if ($ptString == '...')
        {
            $stripped = substr(strip_tags($html), 0, $maxLength);
            $ptString = JHtml::_('string.truncate', $stripped, $maxLength, $noSplit, $allowHtml = false);
        }

        // We need to trim the ellipsis that truncate adds.
        $ptString = rtrim($ptString, '.');

        // Now deal with more complex truncation.
        while ($maxLength <= $baseLength)
        {
            // Get the truncated string assuming HTML is allowed.
            $htmlString = JHtml::_('string.truncate', $html, $maxLength, $noSplit, $allowHtml = true);

            if ($htmlString == '...' && strlen($ptString) + 3 > $maxLength)
            {
                return $htmlString;
            }

            $htmlString = rtrim($htmlString, '.');

            // Now get the plain text from the HTML string and trim it.
            $htmlStringToPtString = JHtml::_('string.truncate', $htmlString, $maxLength, $noSplit, $allowHtml = false);
            $htmlStringToPtString = rtrim($htmlStringToPtString, '.');

            // If the new plain text string matches the original plain text string we are done.
            if ($ptString == $htmlStringToPtString)
            {
                return $htmlString . '...';
            }

            // Get the number of HTML tag characters in the first $maxLength characters
            $diffLength = strlen($ptString) - strlen($htmlStringToPtString);

            if ($diffLength <= 0)
            {
                return $htmlString . '...';
            }

            // Set new $maxlength that adjusts for the HTML tags
            $maxLength += $diffLength;
        }
    }


}

/**
 * Maintain classname compatibility with JomSocial 1.6 below
 */
class CActivityStream extends CActivities {

}
