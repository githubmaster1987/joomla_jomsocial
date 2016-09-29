<?php
/**
* @copyright (C) 2013 iJoomla, Inc. - All rights reserved.
* @license GNU General Public License, version 2 (http://www.gnu.org/licenses/gpl-2.0.html)
* @author iJoomla.com <webmaster@ijoomla.com>
* @url https://www.jomsocial.com/license-agreement
* The PHP code portions are distributed under the GPL license. If not otherwise stated, all images, manuals, cascading style sheets, and included JavaScript *are NOT GPL, and are released under the IJOOMLA Proprietary Use License v1.0
* More info at https://www.jomsocial.com/license-agreement
*/

// Disallow direct access to this file
defined('_JEXEC') or die('Restricted access');

jimport( 'joomla.application.component.model' );

class CommunityModelManualDbUpgrade extends JModelLegacy
{
	/**
	 * Configuration data
	 *
	 * @var object
	 **/
	var $_params;

	/**
	 * Configuration for ini path
	 *
	 * @var string
	 **/
// 	var $_ini	= '';

	/**
	 * Configuration for xml path
	 *
	 * @var string
	 **/
	var $_xml	= '';

	/**
	 * Constructor
	 */
	public function __construct()
	{
		$mainframe	= JFactory::getApplication();


		// Call the parents constructor
		parent::__construct();

	}

	public function upgradeEmojiDB(){
		//lets do the emoji upgrade here
		$db = JFactory::getDbo();
		$stats = array();

		//lets get started with activities first

		$queries = array(
			//activities
				"ALTER TABLE ".$db->quoteName('#__community_activities')." CHANGE ".$db->quoteName('title')." ".$db->quoteName('title')." TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL",
				"ALTER TABLE ".$db->quoteName('#__community_activities')." CHANGE ".$db->quoteName('content')." ".$db->quoteName('content')." TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL",
			//message
				"ALTER TABLE ".$db->quoteName('#__community_msg')." CHANGE ".$db->quoteName('body')." ".$db->quoteName('body')." TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL",
				"ALTER TABLE ".$db->quoteName('#__community_msg')." CHANGE ".$db->quoteName('subject')." ".$db->quoteName('subject')." TINYTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL",
			//walls
				"ALTER TABLE ".$db->quoteName('#__community_wall')." CHANGE ".$db->quoteName('comment')." ".$db->quoteName('comment')." TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL, CHANGE ".$db->quoteName('params')." ".$db->quoteName('params')." TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL;",
			//groups
				"ALTER TABLE ".$db->quoteName('#__community_groups')." CHANGE ".$db->quoteName('name')." ".$db->quoteName('name')." VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL, CHANGE ".$db->quoteName('description')." ".$db->quoteName('description')." TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL, CHANGE ".$db->quoteName('summary')." ".$db->quoteName('summary')." TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL, CHANGE ".$db->quoteName('params')." ".$db->quoteName('params')." TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL",
			//events
				"ALTER TABLE ".$db->quoteName('#__community_events')." CHANGE ".$db->quoteName('title')." ".$db->quoteName('title')." VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL, CHANGE ".$db->quoteName('summary')." ".$db->quoteName('summary')." TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL, CHANGE ".$db->quoteName('description')." ".$db->quoteName('description')." TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE ".$db->quoteName('params')." ".$db->quoteName('params')." TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL",
			//field values
				"ALTER TABLE ".$db->quoteName('#__community_fields_values')." CHANGE ".$db->quoteName('value')." ".$db->quoteName('value')." TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL, CHANGE ".$db->quoteName('params')." ".$db->quoteName('params')." MEDIUMTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL",
			//videos
				"ALTER TABLE ".$db->quoteName('#__community_videos')." CHANGE ".$db->quoteName('title')." ".$db->quoteName('title')." VARCHAR(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL, CHANGE ".$db->quoteName('description')." ".$db->quoteName('description')." TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL, CHANGE ".$db->quoteName('location')." ".$db->quoteName('location')." TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL, CHANGE ".$db->quoteName('params')." ".$db->quoteName('params')." TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL",
			//photos
				"ALTER TABLE ".$db->quoteName('#__community_photos')." CHANGE ".$db->quoteName('caption')." ".$db->quoteName('caption')." TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL, CHANGE ".$db->quoteName('params')." ".$db->quoteName('params')." TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL",
			//albums
				"ALTER TABLE ".$db->quoteName('#__community_photos_albums')." CHANGE ".$db->quoteName('name')." ".$db->quoteName('name')." VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL, CHANGE ".$db->quoteName('description')." ".$db->quoteName('description')." TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL, CHANGE ".$db->quoteName('location')." ".$db->quoteName('location')." TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL, CHANGE ".$db->quoteName('params')." ".$db->quoteName('params')." TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL",
			//group announcement
				"ALTER TABLE ".$db->quoteName('#__community_groups_bulletins')." CHANGE ".$db->quoteName('title')." ".$db->quoteName('title')." VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL, CHANGE ".$db->quoteName('message')." ".$db->quoteName('message')." TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL, CHANGE ".$db->quoteName('params')." ".$db->quoteName('params')." TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL",
			//group discussion
				"ALTER TABLE ".$db->quoteName('#__community_groups_discuss')." CHANGE ".$db->quoteName('title')." ".$db->quoteName('title')." TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL, CHANGE ".$db->quoteName('message')." ".$db->quoteName('message')." TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL, CHANGE ".$db->quoteName('params')." ".$db->quoteName('params')." TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL",
			//mail queue
				"ALTER TABLE ".$db->quoteName('#__community_mailq')." CHANGE ".$db->quoteName('subject')." ".$db->quoteName('subject')." TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL, CHANGE ".$db->quoteName('body')." ".$db->quoteName('body')." TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL, CHANGE ".$db->quoteName('params')." ".$db->quoteName('params')." TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL",
			//notifications
				"ALTER TABLE ".$db->quoteName('#__community_notifications')." CHANGE ".$db->quoteName('content')." ".$db->quoteName('content')." TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL, CHANGE ".$db->quoteName('params')." ".$db->quoteName('params')." TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL",
			//profiles
				"ALTER TABLE ".$db->quoteName('#__community_profiles')." CHANGE ".$db->quoteName('description')." ".$db->quoteName('description')." TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL"
		);

		foreach($queries as $query){
			$db->setQuery($query);
			$result = $db->execute();
			if(!$result){
				return false;
			}
		}

		return true;
	}

	/**
	 * @since 4.2, to get all the initial stats of the db upgrade on emoji
	 */
	public function getEmojiInitialDBStats(){

		$db = JFactory::getDbo();

		$stats = array();
		$canUpgrade = false;
		//check for database version
		$mysqlVersion = CAdvanceSearch::getMySQLVersion(); //lets get the version via advance search static method

		$sqlCheck = array(
			JText::sprintf('COM_COMMUNITY_EMOJI_DB_COMPARISON', $mysqlVersion, '5.5.3')
		);

		if($mysqlVersion >= '5.5.3'){
			$canUpgrade = true;
			$sqlCheck[] = JText::_('COM_COMMUNITY_EMOJI_PASSED');
		}else{
			$sqlCheck[] = JText::_('COM_COMMUNITY_EMOJI_FAILED');
		}

		$stats[] = $sqlCheck;

		//lets check if activities tables are done
		$activityCheck = array(JText::_('COM_COMMUNITY_EMOJI_ACTIVITY_STREAM'));
		$query = "SHOW FULL COLUMNS FROM ".$db->quoteName('#__community_activities');
		$db->setQuery($query);

		$results = $db->loadObjectList();

		foreach($results as $result){
			if($result->Field == 'title'){
				//one result from this table will do because both should be updated at once
				if($result->Collation == 'utf8mb4_general_ci'){
					$activityCheck[] = JText::_('COM_COMMUNITY_EMOJI_UPDATE_UPGRADED');
				}elseif(!$canUpgrade){
					$activityCheck[] = JText::_('COM_COMMUNITY_EMOJI_CANNOT_UPGRADE');
				}else{
					$activityCheck[] = JText::_('COM_COMMUNITY_EMOJI_UPDATE_READY');
				}
			}
		}

		$stats[] = $activityCheck;

		//check for comments (walls)
		$wallCheck = array(JText::_('COM_COMMUNITY_EMOJI_COMMENTS'));
		$query = "SHOW FULL COLUMNS FROM ".$db->quoteName('#__community_wall');
		$db->setQuery($query);

		$results = $db->loadObjectList();

		foreach($results as $result){
			if($result->Field == 'comment'){
				//one result from this table will do because both should be updated at once
				if($result->Collation == 'utf8mb4_general_ci'){
					$wallCheck[] = JText::_('COM_COMMUNITY_EMOJI_UPDATE_UPGRADED');
				}elseif(!$canUpgrade){
					$activityCheck[] = JText::_('COM_COMMUNITY_EMOJI_CANNOT_UPGRADE');
				}else{
					$wallCheck[] = JText::_('COM_COMMUNITY_EMOJI_UPDATE_READY');
				}
			}
		}

		$stats[] = $wallCheck;

		//check for private messaging
		$msgCheck = array(JText::_('COM_COMMUNITY_EMOJI_MESSAGE'));
		$query = "SHOW FULL COLUMNS FROM ".$db->quoteName('#__community_msg');
		$db->setQuery($query);

		$results = $db->loadObjectList();

		foreach($results as $result){
			if($result->Field == 'subject'){
				//one result from this table will do because both should be updated at once
				if($result->Collation == 'utf8mb4_general_ci'){
					$msgCheck[] = JText::_('COM_COMMUNITY_EMOJI_UPDATE_UPGRADED');
				}elseif(!$canUpgrade){
					$activityCheck[] = JText::_('COM_COMMUNITY_EMOJI_CANNOT_UPGRADE');
				}else{
					$msgCheck[] = JText::_('COM_COMMUNITY_EMOJI_UPDATE_READY');
				}
			}
		}

		$stats[] = $msgCheck;

		//check for groups
		$groupCheck = array(JText::_('COM_COMMUNITY_EMOJI_GROUPS'));
		$query = "SHOW FULL COLUMNS FROM ".$db->quoteName('#__community_groups');
		$db->setQuery($query);

		$results = $db->loadObjectList();

		foreach($results as $result){
			if($result->Field == 'summary'){
				//one result from this table will do because both should be updated at once
				if($result->Collation == 'utf8mb4_general_ci'){
					$groupCheck[] = JText::_('COM_COMMUNITY_EMOJI_UPDATE_UPGRADED');
				}elseif(!$canUpgrade){
					$activityCheck[] = JText::_('COM_COMMUNITY_EMOJI_CANNOT_UPGRADE');
				}else{
					$groupCheck[] = JText::_('COM_COMMUNITY_EMOJI_UPDATE_READY');
				}
			}
		}

		$stats[] = $groupCheck;

		//check for events
		$eventCheck = array(JText::_('COM_COMMUNITY_EMOJI_EVENTS'));
		$query = "SHOW FULL COLUMNS FROM ".$db->quoteName('#__community_events');
		$db->setQuery($query);

		$results = $db->loadObjectList();

		foreach($results as $result){
			if($result->Field == 'summary'){
				//one result from this table will do because both should be updated at once
				if($result->Collation == 'utf8mb4_general_ci'){
					$eventCheck[] = JText::_('COM_COMMUNITY_EMOJI_UPDATE_UPGRADED');
				}elseif(!$canUpgrade){
					$activityCheck[] = JText::_('COM_COMMUNITY_EMOJI_CANNOT_UPGRADE');
				}else{
					$eventCheck[] = JText::_('COM_COMMUNITY_EMOJI_UPDATE_READY');
				}
			}
		}

		$stats[] = $eventCheck;

		//check for videos
		$videoCheck = array(JText::_('COM_COMMUNITY_EMOJI_VIDEOS'));
		$query = "SHOW FULL COLUMNS FROM ".$db->quoteName('#__community_videos');
		$db->setQuery($query);

		$results = $db->loadObjectList();

		foreach($results as $result){
			if($result->Field == 'description'){
				//one result from this table will do because both should be updated at once
				if($result->Collation == 'utf8mb4_general_ci'){
					$videoCheck[] = JText::_('COM_COMMUNITY_EMOJI_UPDATE_UPGRADED');
				}elseif(!$canUpgrade){
					$activityCheck[] = JText::_('COM_COMMUNITY_EMOJI_CANNOT_UPGRADE');
				}else{
					$videoCheck[] = JText::_('COM_COMMUNITY_EMOJI_UPDATE_READY');
				}
			}
		}

		$stats[] = $videoCheck;

		//check for photos
		$photoCheck = array(JText::_('COM_COMMUNITY_EMOJI_PHOTOS'));
		$query = "SHOW FULL COLUMNS FROM ".$db->quoteName('#__community_photos');
		$db->setQuery($query);

		$results = $db->loadObjectList();

		foreach($results as $result){
			if($result->Field == 'caption'){
				//one result from this table will do because both should be updated at once
				if($result->Collation == 'utf8mb4_general_ci'){
					$photoCheck[] = JText::_('COM_COMMUNITY_EMOJI_UPDATE_UPGRADED');
				}elseif(!$canUpgrade){
					$activityCheck[] = JText::_('COM_COMMUNITY_EMOJI_CANNOT_UPGRADE');
				}else{
					$photoCheck[] = JText::_('COM_COMMUNITY_EMOJI_UPDATE_READY');
				}
			}
		}

		$stats[] = $photoCheck;

		//check for fields
		$fieldCheck = array(JText::_('COM_COMMUNITY_EMOJI_CUSTOM_FIELDS'));
		$query = "SHOW FULL COLUMNS FROM ".$db->quoteName('#__community_fields_values');
		$db->setQuery($query);

		$results = $db->loadObjectList();

		foreach($results as $result){
			if($result->Field == 'value'){
				//one result from this table will do because both should be updated at once
				if($result->Collation == 'utf8mb4_general_ci'){
					$fieldCheck[] = JText::_('COM_COMMUNITY_EMOJI_UPDATE_UPGRADED');
				}elseif(!$canUpgrade){
					$activityCheck[] = JText::_('COM_COMMUNITY_EMOJI_CANNOT_UPGRADE');
				}else{
					$fieldCheck[] = JText::_('COM_COMMUNITY_EMOJI_UPDATE_READY');
				}
			}
		}

		$stats[] = $fieldCheck;

		//check for mailq
		$fieldCheck = array(JText::_('COM_COMMUNITY_EMOJI_EMAIL'));
		$query = "SHOW FULL COLUMNS FROM ".$db->quoteName('#__community_mailq');
		$db->setQuery($query);

		$results = $db->loadObjectList();

		foreach($results as $result){
			if($result->Field == 'subject'){
				//one result from this table will do because both should be updated at once
				if($result->Collation == 'utf8mb4_general_ci'){
					$fieldCheck[] = JText::_('COM_COMMUNITY_EMOJI_UPDATE_UPGRADED');
				}elseif(!$canUpgrade){
					$activityCheck[] = JText::_('COM_COMMUNITY_EMOJI_CANNOT_UPGRADE');
				}else{
					$fieldCheck[] = JText::_('COM_COMMUNITY_EMOJI_UPDATE_READY');
				}
			}
		}

		$stats[] = $fieldCheck;
		return $stats;
	}


}
