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

Class CActivitiesHelper {

    static protected $_permission = array(
        0               => 'COM_COMMUNITY_PRIVACY_PUBLIC',
        10              => 'COM_COMMUNITY_PRIVACY_PUBLIC',
        PRIVACY_MEMBERS => 'COM_COMMUNITY_PRIVACY_SITE_MEMBERS',
        PRIVACY_FRIENDS => 'COM_COMMUNITY_PRIVACY_FRIENDS',
        PRIVACY_PRIVATE => 'COM_COMMUNITY_PRIVACY_ME'
    );

    static protected $_icons = array(
        0               => 'joms-icon__globe',
        10              => 'joms-icon__globe',
        PRIVACY_MEMBERS => 'joms-icon__users',
        PRIVACY_FRIENDS => 'joms-icon__user',
        PRIVACY_PRIVATE => 'joms-icon__lock'
    );

    static protected $_apps = array(
        /* Groups */
        'groups' => array(
            'groups.bulletin' => array(
                'comment' => true,
                'like' => true
            ),
            'groups.discussion' => array(
                'comment' => true,
                'like' => true
            ),
            'groups.wall' => array(
                'comment' => true,
                'like' => true
            ),
            'groups.featured' => array(
                'comment' => false,
                'like' => true
            )
        ),
        /* Photos */
        'photos' => array(
            'photos.comment' => array(
                'comment' => false,
                'like' => true
            )
        ),
        /* videos */
        'videos' => array(
            'videos.comment' => array(
                'comment' => true,
                'like' => true
            ),
            'videos.comment' => array(
                'comment' => false,
                'like' => true
            )
        ),
        /* Kunena */
        'kunena' => array(
            'kunena.post' => array(
                'comment' => false,
                'like' => true
            ),
            'kunena.reply' => array(
                'comment' => false,
                'like' => true
            ),
            'kunena.thankyou' => array(
                'comment' => false,
                'like' => true
            )
        )
    );

    /**
     * Get array of children' apps
     * @param string $key
     * @return array
     */
    public static function getAppChildren($key) {
        if (isset(self::$_apps[$key])) {
            $children = array();
            foreach (self::$_apps as $key => $data) {
                $children[] = $key;
            }
            return $children;
        }
    }

    /**
     *
     * @param type $app
     * @param type $action
     * @return boolean
     */
    public static function isActionAllowed($app, $action) {
        $parts = explode('.', $app);
        $allowed = true; /* By default we allowed this action */
        /* This app have no sub apps */
        if (count($parts) == 1) {
            /* Make sure this app exists */
            if (isset(self::$_apps[$parts[0]])) {
                /* Make sure this app' action exists */
                if (isset(self::$_apps[$parts[0]][$action])) {
                    $allowed = (self::$_apps[$parts[0]][$action]);
                }
            }
        } else {
            /* This app have sub apps */
            if (isset(self::$_apps[$parts[0]])) {
                /* Make sure this chilapp exists */
                if (isset(self::$_apps[$parts[0]][$app])) {
                    $allowed = (self::$_apps[$parts[0]][$app][$action]);
                }
            }
        }

        return $allowed;
    }

    /**
     * Compare current user id with the actor id of the activity
     * @param $actorId
     * @return bool
     */
    static public function getStreamPermission($actorId){
        $my = CFactory::getUser();

        if (($my->id != $actorId && !is_null($actorId) ) && !COwnerHelper::isCommunityAdmin()) {
            return false;
        }

        return true;
    }

    static public function getStreamPermissionHTML($privacy, $actorId = NULL) {
        $my = CFactory::getUser();

        if (($my->id != $actorId && !is_null($actorId) ) && !COwnerHelper::isCommunityAdmin()) {
            return;
        }

        $html  = ' <span class="joms-stream__meta--separator">&bull;</span> ';
        $html .= '<span class="joms-stream__privacy" data-ui-object="stream-privacy">';
        $html .= '<button class="joms-stream__privacy--button" data-ui-object="stream-privacy-button"><i class="joms-icon ' . self::$_icons[$privacy] . '"></i>';
        $html .= '<span class="joms-icon joms-icon__caret-down"></span></button>';
        $html .= '<div class="joms-stream__privacy--dropdown" data-ui-object="stream-privacy-dropdown">';

        $permissions = self::$_permission;
        unset($permissions[0]);
        foreach ($permissions as $value => $permission) {
            $html .= '<a href="#" data-ui-object="stream-privacy-dropdown-item" data-value="' . $value . '"' . ( $value == $privacy ? ' class="active"' : '' ) . '>';
            $html .= '<i class="joms-icon ' . self::$_icons[$value] . '"></i><span>' . JText::_($permission) . '</span></a>';
        }

        $html .= '</div>';
        $html .= '</span>';

        return $html;
    }

    public static function hasTag($id, $message) {
        $pattern = '/@\[\[(\d+):([a-z]+):([^\]]+)\]\]/';
        preg_match_all($pattern, $message, $matches);

        if (isset($matches[1]) && count($matches[1]) > 0) {
            foreach ($matches[1] as $match) {
                if ($match == $id) {
                    return true;
                }
            }
        }

        return false;
    }


    /**
     * To regenerate the parameters for the attended members in activity to retrieve the right calculation
     * @param $actid
     * @param $event
     * @return bool|int
     */
    public static function updateEventAttendMembers($actid, $event){
        $members = $event->getMembers(COMMUNITY_EVENT_STATUS_ATTEND,10000,false,false,false);
        $membersArr = array();
        foreach($members as $member){
            $membersArr[] = $member->id;
        }

        $activity = JTable::getInstance('Activity','CTable');
        $activity->load($actid);

        $params = new CParameter($activity->params);
        $params->set('actors', implode(',',$membersArr));
        $activity->params = $params->toString();

        if($activity->store()){
            return $event->getMembersCount(COMMUNITY_EVENT_STATUS_ATTEND);
        }

        return false;
    }

    /**
     * To regenerate the parameters for the joined group members in activity to retrieve the right calculation
     * @param $actid
     * @param $event
     * @return bool|int
     */
    public static function updateGroupJoinMembers($actid, $group){
        $groupModel = CFactory::getModel('groups');
        $members = $groupModel->getMembersId($group->id, true);
        $membersArr = array();
        foreach($members as $member){
            $membersArr[] = $member;
        }

        $activity = JTable::getInstance('Activity','CTable');
        $activity->load($actid);

        $params = new CParameter($activity->params);
        $params->set('actors', implode(',',$membersArr));
        $activity->params = $params->toString();

        if($activity->store()){
            return $group->getMembersCount();
        }

        return false;
    }

    /**
     * check if the activity is a featured activity
     * cannot be used through ajax call because we dont know which view is that
     * if needed, the ajax need to pass the view [check access/activities]
     * @param $act
     * @param $targetId
     * @return bool
     * @throws Exception
     */
    public static function isFeaturedStream($act, $targetId){
        //we first determine the view first
        //determine the view
        $jinput = JFactory::getApplication()->input;
        $streamType = $jinput->get('view','','STRING');

        $featuredModel = CFactory::getModel('featured');
        $featuredLists = $featuredModel->getStreamFeaturedList();

        switch($streamType){
            case 'frontpage':
                $streamType = 'stream.frontpage';
                break;
            case 'profile':
                $streamType = 'stream.profile';
                break;
            case 'events':
            case 'event':
                $streamType = 'stream.event';
                break;
            case 'groups':
            case 'group':
                $streamType = 'stream.group';
                break;
            default:
                return false;
        }

        if(!isset($featuredLists[$streamType][$targetId])){
            return false;
        }

        foreach($featuredLists[$streamType][$targetId] as $featured){
            if($featured->cid == $act->id){
                return true;
            }
        }

        return false;
    }

}
