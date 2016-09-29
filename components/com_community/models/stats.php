<?php

/**
 * @copyright (C) 2015 iJoomla, Inc. - All rights reserved.
 * @license GNU General Public License, version 2 (http://www.gnu.org/licenses/gpl-2.0.html)
 * @author iJoomla.com <webmaster@ijoomla.com>
 * @url https://www.jomsocial.com/license-agreement
 * The PHP code portions are distributed under the GPL license. If not otherwise stated, all images, manuals, cascading style sheets, and included JavaScript *are NOT GPL, and are released under the IJOOMLA Proprietary Use License v1.0
 * More info at https://www.jomsocial.com/license-agreement
 */
defined('_JEXEC') or die('Restricted access');

class CommunityModelStats extends JCCModel {


    /**
     * @param $profileId
     * @param $type
     * @param $count
     * @return bool
     *
     * @since 4.1
     * To add profile stats into profiles_stats table
     */
    public function addProfileStats($profileId, $type, $count = 1){
        $db = JFactory::getDBO();
        $dateToday = date("Y-m-d");

        //we need to make sure that we only put in the entry once a day
        $query = "SELECT id, count FROM ".$db->quoteName('#__community_profile_stats').
            ' WHERE '.$db->quoteName('date') .' = '.$db->quote($dateToday).
            ' AND '.$db->quoteName('type').' LIKE '.$db->quote($type).
            ' AND '.$db->quoteName('uid'). ' = '.$db->quote($profileId);
        $db->setQuery($query);
        $result = $db->loadObject();

        $table = JTable::getInstance('profilestats','CTable');

        if(isset($result->id) && $result->id){
            //if the record already exists for the day, we might just update the count for today's record
            $table->load($result->id);
            $table->count = $table->count + $count;
        }else{
            $table->date = $dateToday;
            $table->count = $count;
            $table->uid = $profileId;
            $table->type = $type;
        }

        return $table->store();
    }


    /**
     * @param $photoId
     * @param $type
     * @param $count
     * @return bool
     *
     * @since 4.1
     * To add photo stats into photo_stats table
     */
    public function addPhotoStats($photoId, $type, $count = 1){
        $db = JFactory::getDBO();
        $dateToday = date("Y-m-d");

        //we need to make sure that we only put in the entry once a day
        $query = "SELECT id, count FROM ".$db->quoteName('#__community_photo_stats').
            ' WHERE '.$db->quoteName('date') .' = '.$db->quote($dateToday).
            ' AND '.$db->quoteName('type').' LIKE '.$db->quote($type).
            ' AND '.$db->quoteName('pid'). ' = '.$db->quote($photoId);
        $db->setQuery($query);
        $result = $db->loadObject();

        $table = JTable::getInstance('photostats','CTable');

        if(isset($result->id) && $result->id){
            //if the record already exists for the day, we might just update the count for today's record
            $table->load($result->id);
            $table->count = $table->count + $count;
        }else{
            $table->date = $dateToday;
            $table->count = $count;
            $table->pid = $photoId;
            $table->type = $type;
        }

        return $table->store();
    }

    /**
     * @param $videoId
     * @param $type
     * @param $count
     * @return bool
     *
     * @since 4.1
     * To add video stats into video_stats table
     */
    public function addVideoStats($videoId, $type, $count = 1){
        $db = JFactory::getDBO();
        $dateToday = date("Y-m-d");

        //we need to make sure that we only put in the entry once a day
        $query = "SELECT id, count FROM ".$db->quoteName('#__community_video_stats').
            ' WHERE '.$db->quoteName('date') .' = '.$db->quote($dateToday).
            ' AND '.$db->quoteName('type').' LIKE '.$db->quote($type).
            ' AND '.$db->quoteName('vid'). ' = '.$db->quote($videoId);
        $db->setQuery($query);
        $result = $db->loadObject();

        $table = JTable::getInstance('videostats','CTable');

        if(isset($result->id) && $result->id){
            //if the record already exists for the day, we might just update the count for today's record
            $table->load($result->id);
            $table->count = $table->count + $count;
        }else{
            $table->date = $dateToday;
            $table->count = $count;
            $table->vid = $videoId;
            $table->type = $type;
        }

        return $table->store();
    }

    /**
     * @param $groupId
     * @param $type
     * @param $count
     * @return bool
     *
     * @since 4.1
     * To add group stats into group_stats table
     */
    public function addGroupStats($groupId, $type, $count = 1){
        $db = JFactory::getDBO();
        $dateToday = date("Y-m-d");

        //we need to make sure that we only put in the entry once a day
        $query = "SELECT id, count FROM ".$db->quoteName('#__community_group_stats').
            ' WHERE '.$db->quoteName('date') .' = '.$db->quote($dateToday).
            ' AND '.$db->quoteName('type').' LIKE '.$db->quote($type).
            ' AND '.$db->quoteName('gid'). ' = '.$db->quote($groupId);
        $db->setQuery($query);
        $result = $db->loadObject();

        $table = JTable::getInstance('groupstats','CTable');

        if(isset($result->id) && $result->id){
            //if the record already exists for the day, we might just update the count for today's record
            $table->load($result->id);
            $table->count = $table->count + $count;
        }else{
            $table->date = $dateToday;
            $table->count = $count;
            $table->gid = $groupId;
            $table->type = $type;
        }

        return $table->store();
    }

    /**
     * @param $eventId
     * @param $type
     * @param $count
     * @return bool
     *
     * @since 4.1
     * To add event stats into event_stats table
     */
    public function addEventStats($eventId, $type, $count = 1){
        $db = JFactory::getDBO();
        $dateToday = date("Y-m-d");

        //we need to make sure that we only put in the entry once a day
        $query = "SELECT id, count FROM ".$db->quoteName('#__community_event_stats').
            ' WHERE '.$db->quoteName('date') .' = '.$db->quote($dateToday).
            ' AND '.$db->quoteName('type').' LIKE '.$db->quote($type).
            ' AND '.$db->quoteName('eid'). ' = '.$db->quote($eventId);
        $db->setQuery($query);
        $result = $db->loadObject();

        $table = JTable::getInstance('eventstats','CTable');

        if(isset($result->id) && $result->id){
            //if the record already exists for the day, we might just update the count for today's record
            $table->load($result->id);
            $table->count = $table->count + $count;
        }else{
            $table->date = $dateToday;
            $table->count = $count;
            $table->eid = $eventId;
            $table->type = $type;
        }

        return $table->store();
    }
}
