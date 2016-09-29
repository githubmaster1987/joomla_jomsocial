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
if(!class_exists('modCommunityStatistics'))
{
    class modCommunityStatistics
	{
		var $db;
		
		public function __construct(){
			$this->db = JFactory::getDBO();
		}
		
		static public function &getInstance(){
			static $instance;
			if(!$instance){
				$instance = new modCommunityStatistics();
			}
			return $instance;
		}
		
		static public function getStatisticsData(&$params)
		{
			$stats = modCommunityStatistics::getInstance();
			
			$stats_arr = array();

			if($params->get('show_members', 1)){
				$members = $stats->getMemberCount();
				$params->def('t_members', $members);
				array_push($stats_arr, 't_members');
			}
			
			if($params->get('show_groups', 1)){
				$params->def('t_groups', $stats->getGroupCount());
				array_push($stats_arr, 't_groups');
			}
			
			if($params->get('show_discussions', 1)){
				$params->def('t_discussions', $stats->geDiscussionCount());
				array_push($stats_arr, 't_discussions');
			}
			
			if($params->get('show_albums', 1)){
				$params->def('t_albums', $stats->getAlbumsCount());
				array_push($stats_arr, 't_albums');
			}
			
			if($params->get('show_photos', 1)){
				$params->def('t_photos', $stats->getPhotosCount());
				array_push($stats_arr, 't_photos');
			}
			
			if($params->get('show_videos', 1)){
				$params->def('t_videos', $stats->getVideosCount());
				array_push($stats_arr, 't_videos');
			}
			
			if($params->get('show_announcements', 1)){
				$params->def('t_bulletins', $stats->getBulletinsCount());
				array_push($stats_arr, 't_bulletins');
			}
			
			if($params->get('show_activities', 1)){
				$params->def('t_activities', $stats->getActivitiesCount());
				array_push($stats_arr, 't_activities');
			}
			
			if($params->get('show_walls', 1)){
				$params->def('t_walls', $stats->getWallsCount());
				array_push($stats_arr, 't_walls');	
			}

			if($params->get('show_upcoming_events', 1)){
				$params->def('t_events', $stats->getEventsCount());
				array_push($stats_arr, 't_events');
			}

			if($params->get('show_males',1) || $params->get('show_females',1) || $params->get('show_unspecified',1))
			{
				$gender_fieldcode 	= $params->get('jsgender','FIELD_GENDER');
				$gender_male 		= strtolower($params->get('genders_male_display','Male'));
				$gender_female 		= strtolower($params->get('genders_female_display','Female'));
				$genders 			= $stats->getGendersCount($gender_fieldcode, $gender_male, $gender_female);
				
				if(!empty($genders))
				{
					if(empty($members)){
						$members = $stats->getMemberCount();
					}
					$unspecified = $members - ($genders["male"] + $genders["female"]);
					$params->def('t_gender_males', $genders["male"]);
					$params->def('t_gender_females', $genders["female"]);
					$params->def('t_gender_unspecified', $unspecified);
					
					array_push($stats_arr, 'genders');
				}
			}
			
			return $stats_arr;
		}
		
		public function getEventsCount()
		{
			$query	= 'SELECT COUNT(*) FROM '
					. $this->db->quoteName( '#__community_events' ) . ' '
					. 'WHERE '.$this->db->quoteName('published').'=' . $this->db->Quote( 1 );
			$this->db->setQuery( $query );
			
			return $this->db->loadResult();
		}
		
		public function getMemberCount(){
		
			$sql = "SELECT 
							COUNT(".$this->db->quoteName('id').") AS total
					FROM	
							".$this->db->quoteName('#__users')."  
					WHERE
							".$this->db->quoteName('block')." = 0";
							
			$this->db->setQuery($sql);
			try {
				$row = $this->db->loadObject();
			} catch (Exception $e) {
				JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
			}
		    $total = $row->total;
		    
			return $total;
		    
		}
		
		public function getGroupCount(){
		
			$sql = "SELECT 
							COUNT(".$this->db->quoteName('id').") AS total
					FROM	
							".$this->db->quoteName('#__community_groups')."
					WHERE
							".$this->db->quoteName('published')." = 1";
							
			$this->db->setQuery($sql);
			try {
				$row = $this->db->loadObject();
			} catch (Exception $e) {
				JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
			}

		    $total = $row->total;
		    
			return $total;
		    
		}
		
		public function geDiscussionCount(){
		
			$sql = "SELECT 
							COUNT(".$this->db->quoteName('id').") AS total
					FROM	
							".$this->db->quoteName('#__community_groups_discuss');
							
			$this->db->setQuery($sql);
			try {
				$row = $this->db->loadObject();
			} catch (Exception $e) {
				JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
			}

		    $total = $row->total;
		    
			return $total;
		    
		}
		
		public function getAlbumsCount(){
		
			$sql = "SELECT 
							COUNT(".$this->db->quoteName('id').") AS total
					FROM	
							".$this->db->quoteName('#__community_photos_albums');

			$this->db->setQuery($sql);
			try {
				$row = $this->db->loadObject();
			} catch (Exception $e) {
				JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
			}

		    $total = $row->total;
		    
			return $total;
		}
		
		public function getPhotosCount(){
		
			$sql = "SELECT 
							COUNT(".$this->db->quoteName('id').") AS total
					FROM	
							".$this->db->quoteName('#__community_photos')."
					WHERE
							".$this->db->quoteName('published')." = 1";

			$this->db->setQuery($sql);
			try {
				$row = $this->db->loadObject();
			} catch (Exception $e) {
				JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
			}
		    $total = $row->total;
		    
			return $total;
		    
		}
		
		public function getVideosCount(){
			$sql = "SELECT 
							COUNT(".$this->db->quoteName('id').") AS total
					FROM	
							".$this->db->quoteName('#__community_videos')."
					WHERE
							".$this->db->quoteName('published')." = 1 AND 
							".$this->db->quoteName('status')." = ".$this->db->quote('ready');

			$this->db->setQuery($sql);
			try {
				$row = $this->db->loadObject();
			} catch (Exception $e) {
				JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
			}
		    $total = $row->total;
		    
			return $total;		  
		}
		
		public function getBulletinsCount(){
		
			$sql = "SELECT 
							COUNT(".$this->db->quoteName('id').") AS total
					FROM	
							".$this->db->quoteName('#__community_groups_bulletins')."
					WHERE
							".$this->db->quoteName('published')." = 1";

			$this->db->setQuery($sql);
			try {
				$row = $this->db->loadObject();
			} catch (Exception $e) {
				JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
			}
		    
		    $total = $row->total;
		    
			return $total;
		    
		}
		
		public function getActivitiesCount(){
		
			$sql = "SELECT 
							COUNT(".$this->db->quoteName('id').") AS total
					FROM	
							".$this->db->quoteName('#__community_activities');

            $this->db->setQuery($sql);
            try {
                $row = $this->db->loadObject();
            } catch (Exception $e) {
                JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
            }
		    
		    $total = $row->total;
		    
			return $total;		    
		}
		
		public function getWallsCount(){
		
			$sql = "SELECT 
							COUNT(".$this->db->quoteName('id').") AS total
					FROM	
							".$this->db->quoteName('#__community_wall')."
					WHERE
							".$this->db->quoteName('published')." = 1 AND
							".$this->db->quoteName('type')." != ".$this->db->quote('discussions');

            $this->db->setQuery($sql);
            try {
                $row = $this->db->loadObject();
            } catch (Exception $e) {
                JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
            }
		    
		    $total = $row->total;
		    
			return $total;		    
		}
		
		public function getGendersCount($gender_fieldcode, $gender_male, $gender_female)
		{
			$sql = "SELECT 
							".$this->db->quoteName('id')."
					FROM	
							".$this->db->quoteName('#__community_fields')."
					WHERE
							".$this->db->quoteName('fieldcode')." = ".$this->db->quote($gender_fieldcode);

            $this->db->setQuery($sql);
            try {
                $row = $this->db->loadObject();
            } catch (Exception $e) {
                JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
            }
		    // the result might return empty records. If thats the case, then
		    // return male and female to zero.
		    if(empty($row))
		    {
		    	$gender = array('female' => 0, 'male' => 0);
		    	return $gender;
		    }
		    
		    $gender_id = $row->id;
		    if(!empty($gender_id)){
				$sql = "SELECT 
								".$this->db->quoteName('value').", 
								COUNT(a.".$this->db->quoteName('id').") AS total
						FROM	
								".$this->db->quoteName('#__community_fields_values')." a,
								".$this->db->quoteName('#__users')." b
						WHERE
								b.".$this->db->quoteName('id')." = a.".$this->db->quoteName('user_id')." AND
								".$this->db->quoteName('block')." = 0 AND
								".$this->db->quoteName('field_id')." = ".$this->db->quote($gender_id)."
						GROUP 
								BY ".$this->db->quoteName('value');

                $this->db->setQuery($sql);
                try {
                    $row = $this->db->loadObjectList();
                } catch (Exception $e) {
                    JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
                }

			    $gender = array(
                    'male' => 0,
                    'female' => 0
                );
			    
			    foreach($row as $data)
				{
			    	$case = JString::strtolower($data->value);

					switch($case)
					{
                        case 'com_community_female':
						case $gender_female:
							$gender['female'] = $gender['female'] + $data->total;
							break;
                        case 'com_community_male':
						case $gender_male:
							$gender['male'] = $gender['male']+ $data->total;
							break;
					}
				}

				return $gender;    
			}
		}
	}
}
