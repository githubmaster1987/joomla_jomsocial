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

// All the module logic should be placed here
if(!class_exists('modCommunityTopMembers'))
{
    class modCommunityTopMembers
	{
        /**
         * @param int $days
         * @param $type
         */
        private function formatData($type = 'all', $days = 0){
            $db = JFactory::getDbo();

            $extraSql = '';
            if($days){
                $dateToday = date("Y-m-d");
                $daysAgo = date('Y-m-d', strtotime('-'.$days.' days', strtotime($dateToday)));
                $extraSql = ' AND date > '.$db->quote($daysAgo);
            }

            if($type != 'all'){
                $extraSql .= " AND ".$db->quoteName('type')."=".$db->quote($type);
            }

            $query = "SELECT * FROM ".$db->quoteName('#__community_profile_stats').
                " WHERE 1 ". $extraSql;

            $db->setQuery($query);

            return $db->loadObjectList();
        }

		public function getMembersData( &$params ){
			$model	= CFactory::getModel( 'user' );
			$db 	= JFactory::getDBO();
			
			$limit	= $params->get('limit', 10);

            $timespan = $params->get('timespan',0);
            $daysAgo = (!$timespan) ? 0 : $params->get('custom_days', 0);


            $type = 'points';
            if($params->get('sort_by') == 1){
                $type = 'like';
            }elseif($params->get('sort_by') == 2){
                $type = 'view';
            }

            $results = $this->formatData($type, $daysAgo);

            $formattedResults = array(); //[id][type] = count
            foreach($results as $result){
                if(isset($formattedResults[$result->uid][$result->type])){
                    $formattedResults[$result->uid][$result->type] += $result->count;
                }else{
                    $formattedResults[$result->uid][$result->type] = $result->count;
                }
            }

            //after we format the results, lets sort the by the type
            $topMembersArr = array();

            foreach($formattedResults as $key=>$result){
                if(isset($result[$type])){
                    $topMembersArr[$key] = $result[$type];
                }else{
                    $topMembersArr[$key] = 0;
                }
            }
            arsort($topMembersArr);

            $members = array();
            //after sorted, rearrange the array
            $i = 0;
            foreach($topMembersArr as $key=>$arr){

                $user = CFactory::getUser( $key );

                if(!$user->username || $user->block || !$user->id){
                    //do not include deleted or blocked users
                    continue;
                }

                if($i++ == $limit){
                    break;
                }

                $arr = $formattedResults[$key];


                $obj				= new stdClass();
                $obj->id    		= $user->id;
                $obj->name      	= $user->getDisplayName();
                $obj->avatar    	= $user->getThumbAvatar();
                $obj->userpoints	= (isset($formattedResults[$key]['points'])) ? $formattedResults[$key]['points'] : 0;
                $obj->views         = (isset($formattedResults[$key]['view'])) ? $formattedResults[$key]['view'] : 0;
                $obj->likes         = (isset($formattedResults[$key]['like'])) ? $formattedResults[$key]['like'] : 0;
                $obj->link			= CRoute::_( 'index.php?option=com_community&view=profile&userid=' . $user->id );

                $members[]	= $obj;

            }

			return array_slice($members, 0, $limit);
		}
	}
}
