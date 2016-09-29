<?php

    /**
     * @copyright (C) 2014 iJoomla, Inc. - All rights reserved.
     * @license GNU General Public License, version 2 (http://www.gnu.org/licenses/gpl-2.0.html)
     * @author iJoomla.com <webmaster@ijoomla.com>
     * @url https://www.jomsocial.com/license-agreement
     * The PHP code portions are distributed under the GPL license. If not otherwise stated, all images, manuals, cascading style sheets, and included JavaScript *are NOT GPL, and are released under the IJOOMLA Proprietary Use License v1.0
     * More info at https://www.jomsocial.com/license-agreement
     */
    // no direct access

    defined('_JEXEC') or die('Restricted access');

    class CommunityModelHashtags extends JCCModel {
        /**
         * This function is used to remove the activity that involves the hash id
         * @param $tag
         * @param $activityId
         */
        public function removeActivityHashtag($tag, $activityId){
            $db = JFactory::getDBO();
            $query = 'SELECT id FROM ' . $db->quoteName('#__community_hashtag') . ' WHERE ' .$db->quoteName('tag'). ' LIKE '.$db->quote(strtolower($tag));
            $db->setQuery($query);
            $id = $db->loadResult();

            if($id){
                $hashtagTable = JTable::getInstance('Hashtag', 'CTable');
                $hashtagTable->load($id);

                //lets remove the activity from the params
                $params = new JRegistry($hashtagTable->params);
                $tempParam= array_values(array_diff($params->get('activity_id'), array($activityId)));
                $params->set('activity_id',$tempParam);
                $hashtagTable->params = $params->toString();
                $hashtagTable->store();
            }
        }

        /**
         * Add hashtag to an activity
         * @param $tag
         * @param $activityId
         */
        public function addActivityHashtag($tag, $activityId){
            $tag = trim($tag);
            if($tag{0} != '#'){
                $tag="#".$tag;
            }

            $db = JFactory::getDBO();
            $query = 'SELECT id FROM ' . $db->quoteName('#__community_hashtag') . ' WHERE ' .$db->quoteName('tag'). ' LIKE '.$db->quote(strtolower($tag));
            $db->setQuery($query);
            $id = $db->loadResult();

            if($id){
                $hashtagTable = JTable::getInstance('Hashtag', 'CTable');
                $hashtagTable->load($id);
                $hashtagTable->tag = $tag;
                $params = new JRegistry($hashtagTable->params);
                $tempParam= array_unique(array_merge($params->get('activity_id'), array($activityId)));
                $params->set('activity_id',$tempParam);
                $hashtagTable->params = $params->toString();
                $hashtagTable->store();
            }else{
                $hashtagTable = JTable::getInstance('Hashtag', 'CTable');
                $params = new JRegistry();
                $hashtagTable->tag = $tag;
                $params->set('activity_id', array($activityId));
                $hashtagTable->params = $params->toString();
                $hashtagTable->store();
            }
        }

        /**
         * Retrieve the list of activity id related to the hashtag
         * @param $hashtag
         * @return array of activity ids
         */
        public function getActivityIds($hashtag)
        {
            if($hashtag{0} != '#'){ $hashtag = '#'.$hashtag; }
            $db = JFactory::getDBO();
            $query = 'SELECT * FROM ' . $db->quoteName('#__community_hashtag') . ' WHERE ' .$db->quoteName('tag'). ' LIKE '.$db->quote(strtolower($hashtag));
            $db->setQuery($query);

            $results = $db->loadObject();

            if($results){
                $activityIds = null;
                $params = new JRegistry($results->params);
                $activityIds = $params->get('activity_id');
                return $activityIds;
            }

            return false;
        }
    }
