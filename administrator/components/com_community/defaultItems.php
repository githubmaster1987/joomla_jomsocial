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
abstract class CommunityDefaultItem
{
	/**
	 * Check if default category exist
	 * @param  [string] $name [table name]
	 * @return [boolean]       [description]
	 */
	static public function checkDefaultCategories($name)
	{
		$db		= JFactory::getDBO();

		$extraquery	= '';
		$tableName	= '#__community_'.$name.'_category';

		switch ($name) {
			case 'userpoints':
				$tableName = '#__community_userpoints';
				$extraquery	= ' WHERE ' . $db->quoteName('system') .' = 1';
				break;
			case 'fields':
				$tableName = '#__community_fields';
				break;
		}

		$query	= 'SELECT COUNT(*) FROM ' . $db->quoteName( $tableName ) . $extraquery;
		$db->setQuery( $query );

		$result	= ( $db->loadResult() > 0 ) ? false : true;

		return $result;
	}

	/**
	 * Add default Event Categories
	 */
	static public function addDefaultEventsCategories()
	{
		$db		= JFactory::getDBO();

		$query	= 'INSERT INTO '.$db->quoteName("#__community_events_category").'(' . $db->quoteName('name') .', ' . $db->quoteName('description') .' ) VALUES ' .
					'( ' . $db->Quote('General') .', ' . $db->Quote('General events') .'),'.
					'( ' . $db->Quote('Birthday') .', ' . $db->Quote('Birthday events') .'),'.
					'( ' . $db->Quote('Party') .', ' . $db->Quote('Party events') .')';

		$db->setQuery( $query );

		try {
			$db->execute();
		} catch (Exception $e) {
			return false;
		}

		return true;
	}

	/**
	 * Add defautl Group Categories
	 */
	static public function addDefaultGroupCategories()
	{
		$db 	= JFactory::getDBO();

		$query 	= 'INSERT INTO ' . $db->quoteName('#__community_groups_category') . ' (' . $db->quoteName('id') .', ' . $db->quoteName('name') .', ' . $db->quoteName('description') .') VALUES ' .
					'(' . $db->Quote('1') .', ' . $db->Quote('General') .', ' . $db->Quote('General group category.') .'),'.
					'(' . $db->Quote('2') .', ' . $db->Quote('Internet') .', ' . $db->Quote('Internet group category.') . '),'.
					'(' . $db->Quote('3') .', ' . $db->Quote('Business') .', ' . $db->Quote('Business group category') .'),'.
					'(' . $db->Quote('4') .', ' . $db->Quote('Automotive') .', ' . $db->Quote('Automotive group category') .'),'.
					'(' . $db->Quote('5') .', ' . $db->Quote('Music') .', ' . $db->Quote('Music group category') .')';

		$db->setQuery( $query );

		try {
			$db->execute();
		} catch (Exception $e) {
			return false;
		}

		return true;
	}

	/**
	 * Add default videos categories
	 */
	static public function addDefaultVideosCategories()
	{
		$db		= JFactory::getDBO();

		$query	= 'INSERT INTO '.$db->quoteName('#__community_videos_category') .'(' . $db->quoteName('id') .', ' . $db->quoteName('name') .', ' . $db->quoteName('description') .', ' . $db->quoteName('published') .' )'.
					' VALUES ( NULL , ' . $db->Quote('General') .', ' . $db->Quote('General video channel') .', ' . $db->Quote('1') .')';

		$db->setQuery( $query );

		try {
			$db->execute();
		} catch (Exception $e) {
			return false;
		}

		return true;
	}
	/**
	 * Add default Userpoints
	 */
	static public function addDefaultUserPoints()
	{
		$db 	= JFactory::getDBO();

		$query = "INSERT INTO `#__community_userpoints` (`id`, `rule_name`, `rule_description`, `rule_plugin`, `action_string`, `component`, `access`, `points`, `published`, `system`) VALUES
					(1, 'Profile Application Added', 'Assign number of points and determine whether an activity stream item will be created when user adds a profile application.', 'com_community', 'application.add', '', 1, 0, 1, 1),
					(2, 'Profile Application Removed', 'Assign number of points and determine whether an activity stream item will be created when user removes a profile application.', 'com_community', 'application.remove', '', 1, 0, 0, 1),
					(3, 'Photo Upload', 'Assign number of points for the photo upload action and determine if an activity stream item will be created.', 'com_community', 'photo.upload', '', 1, 0, 1, 1),
					(4, 'New Group Created', 'Assign number of points when a user creates a new group and determines if an activity stream item will be created.', 'com_community', 'group.create', '', 1, 3, 1, 1),
					(5, 'New Group Discussion', 'Assign number of points when a user opens a new discussion in group and determine if an activity stream item for this action will be created.', 'com_community', 'group.discussion.create', '', 1, 2, 1, 1),
					(6, 'User Leave The Group', 'Deduct points when a registered user leaves a group. * There is no activity stream item for this action.', 'com_community', 'group.leave', '', 1, -1, 1, 1),
					(7, 'Friend Request Is Approved', 'Assign number of points when user approves a friend request and determine if an activity stream item for this action will be created.', 'com_community', 'friends.request.approve', '', 1, 1, 1, 1),
					(8, 'New Photo Album Created', 'Assign number of points when a user creates new album and determine if an activity stream item for this action will be created.', 'com_community', 'album.create', '', 1, 1, 1, 1),
					(9, 'Post Group Comment', 'Assign a number of points when a user posst from a group''s share box and determine if to create an activity stream item for this action.', 'com_community', 'group.wall.create', '', 1, 1, 1, 1),
					(10, 'Join Group', 'Assign a number of points when user joins the group and determine if to create an activity stream item for this action.', 'com_community', 'group.join', '', 1, 1, 1, 1),
					(11, 'Reply Group''s Discussion', 'Assign a number of points when a registered user replies on a group''s discussion and determine if to create an activity stream item for this action.', 'com_community', 'group.discussion.reply', '', 1, 1, 1, 1),
					(12, 'Comment Profile From Share Box', 'Assign a number of points when a user posts from a Share Box on other people''s profiles and determine if to create an activity stream item for this action.', 'com_community', 'profile.wall.create', '', 1, 1, 1, 1),
					(13, 'Post Status From Share Box', 'Assign a number of points when a user posts a status from a Share Box and determine if to create an activity stream item for this action.', 'com_community', 'profile.status.update', '', 1, 1, 1, 1),
					(14, 'Profile Updated', 'Assign a number of points when a profile is updated. There is no activity stream item for this action.', 'com_community', 'profile.save', '', 1, 1, 1, 1),
					(15, 'Group Updated', 'Assign a number of points when a user updates the group information and determine if to create an activity stream item for this action.', 'com_community', 'group.updated', '', 1, 1, 1, 1),
					(16, 'Change Group Avatar', 'Assign a number of points when a group admin or owner changes its avatar.', 'com_community', 'group.avatar.upload', '', 1, 0, 1, 1),
					(17, 'Create Group''s Announcement', 'Assign a number of points when a user publishes a new announcement in a group and determine if to create an activity stream item for this action.', 'com_community', 'group.news.create', '', 1, 1, 1, 1),
					(18, 'Comment Photo', 'Assign number of points when a new photo comment is added and determine if to create an activity stream item for this action', 'com_community', 'photos.wall.create', '', 1, 1, 1, 1),
					(19, 'Remove Friend', 'Deduct points when a registered user removes a friend. There is no activity stream item for this action', 'com_community', 'friends.remove', '', 1, 0, 1, 1),
					(20, 'Change Profile Picture', 'Assign a number of points when a user changes a profile picture and determine if to create an activity stream item for this action', 'com_community', 'profile.avatar.upload', '', 1, 1, 0, 1),
					(21, 'Update Profile Privacy', 'Assign a number of points when user update profile privacy. There is no activity stream item for this action.', 'com_community', 'profile.privacy.update', '', 1, 0, 1, 1),
					(22, 'Reply Private Message', 'Give points when a user replies to a private message. Activity stream items for this action do not exist.', 'com_community', 'inbox.message.reply', '', 1, 0, 1, 1),
					(23, 'New Private Message', 'Give points when a user sends a new private message.', 'com_community', 'inbox.message.send', '', 1, 0, 1, 1),
					(24, 'Remove Group Member', 'Assign a number of points when a group owner or admin user removes a group memeber.', 'com_community', 'group.member.remove', '', 1, 0, 1, 1),
					(25, 'Delete Announcement', 'Deduct points when a user removes an announcement in group.', 'com_community', 'group.news.remove', '', 1, 0, 1, 1),
					(26, 'Remove Wall Comment', 'Deduct points to the original poster when a wall comment is removed.', 'com_community', 'wall.remove', '', 1, 0, 1, 1),
					(27, 'Delete Photo Album', 'Deduct points when a user deletes a photo album.', 'com_community', 'album.remove', '', 1, 0, 1, 1),
					(28, 'Delete Photo', 'Deduct points when a user deletes a photo.', 'com_community', 'photo.remove', '', 1, 0, 1, 1),
					(29, 'Event Updated', 'Assign a number of points when a user updates event information.', 'com_community', 'events.update', '', 1, 1, 1, 1),
					(30, 'Post Event Wall', 'Give points when registered user post wall on event.', 'com_community', 'event.wall.create', '', 1, 1, 1, 1),
					(31, 'Remove video', 'Deduct points when user remove a video.', 'com_community', 'video.remove', '', 1, -1, 1, 1),
					(32, 'Add video', 'Add points when user adds a video.', 'com_community', 'video.add', '', 1, 1, 1, 1),
					(33, 'Add Event', 'Add points when user created new event.', 'com_community', 'events.create', '', 1, 1, 1, 1),
					(34, 'Like photo', 'Add points when usegr like a photo', 'com_community', 'photo.like', '', 1, 1, 1, 1),
					(35, 'Like album', 'Add points when user like an album', 'com_community', 'album.like', '', 1, 1, 1, 1),
					(36, 'Like videos', 'Add points when user like a video', 'com_community', 'videos.like', '', 1, 1, 1, 1),
					(37, 'Like profile', 'Add points when user like a profile', 'com_community', 'profile.like', '', 1, 1, 1, 1),
					(38, 'Like profile status', 'Add points when user like a profile status', 'com_community', 'profile.stream.like', '', 1, 1, 1, 1),
					(39, 'Change profile cover', 'Add points when user change the profile cover photo', 'com_community', 'profile.cover.upload', '', 1, 1, 1, 1),
					(40, 'Change group cover', 'Add points when user change the group cover photo', 'com_community', 'group.cover.upload', '', 1, 1, 1, 1),
					(41, 'Change event cover', 'Add points when user change the event cover photo', 'com_community', 'event.cover.upload', '', 1, 1, 1, 1),
					(42, 'Comment album', 'Add points of points when new album comment is added', 'com_community', 'albums.comment', '', 1, 1, 1, 1),
					(43, 'Comment video', 'Add points of points when new video comment is added', 'com_community', 'videos.comment', '', 1, 1, 1, 1),
					(44, 'Like group', 'Add points when user like a group', 'com_community', 'groups.like', '', 1, 1, 1, 1),
					(45, 'Like event', 'Add points when user like a event', 'com_community', 'events.like', '', 1, 1, 1, 1),
					(46, 'Unlike photo', 'Deduct points when user unlike a photo', 'com_community', 'photo.unlike', '', 1, -1, 1, 1),
					(47, 'Unlike album', 'Deduct points when user unlike an album', 'com_community', 'album.unlike', '', 1, -1, 1, 1),
					(48, 'Unlike video', 'Deduct points when user unlike a video', 'com_community', 'videos.unlike', '', 1, -1, 1, 1),
					(49, 'Unlike group', 'Deduct points when user unlike a group', 'com_community', 'groups.unlike', '', 1, -1, 1, 1),
					(50, 'Unlike event', 'Deduct points when user unlike a event', 'com_community', 'events.unlike', '', 1, -1, 1, 1),
					(51, 'Unlike profile', 'Deduct points when user unlike a profile', 'com_community', 'profile.unlike', '', 1, -1, 1, 1),
					(52, 'Unlike profile status', 'Deduct points when user unlike a profile status', 'com_community', 'profile.stream.unlike', '', 1, -1, 1, 1)";

		$db->setQuery( $query );
		try {
			$db->execute();
		} catch (Exception $e) {
			return false;
		}

		return true;
	}

	/**
	 * Check if JomSocial Menu type exits
	 * @return [boolean] [return true/false]
	 */
    static public function menuTypesExist()
    {
        $db		= JFactory::getDBO();

        $query	= 'SELECT COUNT(*) FROM ' . $db->quoteName( '#__menu_types' ) . ' '
            . 'WHERE ' . $db->quoteName( 'menutype' ) . ' = ' .  $db->Quote( 'jomsocial');

        $db->setQuery( $query );

        $needUpdate	= ( $db->loadResult() >= 1 ) ? true : false;

        return $needUpdate;
    }

	/**
	 * Add Default Menu types
	 */
	static public function addDefaultMenuTypes()
	{
		$db		= JFactory::getDBO();
		$query	= 'INSERT INTO ' . $db->quoteName( '#__menu_types' ) . ' (' . $db->quoteName('menutype') .',' . $db->quoteName('title') .',' . $db->quoteName('description') .') VALUES '
		    			. '( ' . $db->Quote( 'jomsocial' ) . ',' . $db->Quote( 'JomSocial toolbar' ) . ',' . $db->Quote( 'Toolbar items for JomSocial toolbar') . ')';
		$db->setQuery( $query );
		try {
			$db->execute();
		} catch (Exception $e) {
			return false;
		}

		return true;
	}

	/**
	 * Check if menu exist
	 * @return [boolean] [return true/false]
	 */
	static public function menuExist()
	{
		$db		= JFactory::getDBO();

		$query	= 'SELECT COUNT(*) FROM ' . $db->quoteName( '#__menu' ) . ' '
				. 'WHERE ' . $db->quoteName( 'link' ) . ' LIKE ' .  $db->Quote( '%option=com_community%')
                . ' AND '. $db->quoteName('title').' <> '. $db->Quote('COM_COMMUNITY');
				//. 'AND ' . $db->quoteName('menutype ') . ' = ' . $db->Quote('jomSocial');

		$db->setQuery( $query );

		$needUpdate	= ( $db->loadResult() >= 1 ) ? true : false;

		return $needUpdate;
	}

	/**
	 * Update Menu Item
	 * @return [boolean] [return true/false]
	 */
	static public function updateMenuItems()
	{
		// Get new component id.
		$component		= JComponentHelper::getComponent('com_community');
		$component_id	= 0;
		if (is_object($component) && isset($component->id)){
			$component_id 	= $component->id;
		}

		if ($component_id > 0)
		{
			// Update the existing menu items.
			$db 	= JFactory::getDBO();

			$query 	= 'UPDATE ' . $db->quoteName( '#__menu' ) . ' '
					. 'SET '.$db->quoteName(JOOMLA_MENU_COMPONENT_ID).'=' . $db->Quote( $component_id ) . ' '
					. 'WHERE ' . $db->quoteName('link') .' LIKE ' . $db->Quote('%option=com_community%');

			$db->setQuery( $query );
			try {
				$db->execute();
			} catch (Exception $e) {
				return false;
			}


			$query 	= 'UPDATE ' . $db->quoteName( '#__menu' ) . ' '
					. 'SET '.$db->quoteName('level').'=' . $db->Quote( 1 ) . ' '
					. 'WHERE ' . $db->quoteName('link') .' LIKE ' . $db->Quote('%option=com_community%')
					. 'AND '.$db->quoteName( 'level' ).'= 0';

			$db->setQuery( $query );
			try {
				$db->execute();
			} catch (Exception $e) {
				return false;
			}
		}
		return true;
	}
	/**
	 * Add Menu items
	 */
	static public function addMenuItems()
	{
		$db = JFactory::getDBO();

		// Get new component id.
		$component    = JComponentHelper::getComponent('com_community');
		$component_id = 0;

		if (is_object($component) && isset($component->id))
		{
			$component_id = $component->id;
		}

		$column_name = JOOMLA_MENU_NAME;
		$column_cid  = JOOMLA_MENU_COMPONENT_ID;

		// Get the default menu type
		// 2 Joomla bugs occur in /Administrator mode
		// Bug 1: JFactory::getApplication('site') failed. It always return id = 'administrator'.
		// Bug 2: JMenu::getDefault('*') failed. JAdministrator::getLanguageFilter() doesn't exist.
		// If these 2 bugs are fixed, we can call the following syntax:
		// $defaultMenuType	= JFactory::getApplication('sites')->getMenu()->getDefault()->menutype;
		jimport('joomla.application.application');
		$defaultMenuType = JApplicationCms::getInstance('site')->getMenu()->getDefault('workaround_joomla_bug')->menutype;

		// Update the existing menu items.
		$row				= JTable::getInstance ( 'menu', 'JTable' );
		$row->menutype		= $defaultMenuType;
		$row->$column_name	= 'JomSocial';
		$row->alias			= 'JomSocial';
		$row->access		= 1;
		$row->link			= 'index.php?option=com_community&view=frontpage';
		$row->type			= 'component';
		$row->published		= '1';
		$row->$column_cid	= $component_id;
		$row->id			= null; //new item
		$row->language		= '*';

		$row->check();
		$row->store();
		// if ( !$row->store() )
		// {
		// 	return false;
		// }

		$query = 'UPDATE '. $db->quoteName( '#__menu' )
				 . ' SET `parent_id` = ' .$db->quote(1)
				 . ', `level` = ' . $db->quote(1)
				 . ' WHERE `id` = ' . $db->quote($row->id) ;
		$db->setQuery( $query );
		$db->execute();

		// if ($db->getErrorNum())
		// {
		// 	return false;
		// }

		if ( ! self::addDefaultToolbarMenus())
		{
			return false;
		}

		// update memu items with component id
		if ( ! self::updateMenuItems())
		{
			return false;
		}

		return true;
	}
	/**
	 * Add default customs Fields
	 */
	static public function addDefaultCustomFields()
	{
		$db		= JFactory::getDBO();
		$query	= 'INSERT INTO ' . $db->quoteName('#__community_fields') . ' (' . $db->quoteName('id') . ', ' . $db->quoteName('type') . ', ' . $db->quoteName('ordering') . ', ' . $db->quoteName('published') . ', ' . $db->quoteName('min') . ', ' . $db->quoteName('max') . ', ' . $db->quoteName('name') . ', ' . $db->quoteName('tips') . ', ' . $db->quoteName('visible') . ', ' . $db->quoteName('required') . ', ' . $db->quoteName('searchable') . ', ' . $db->quoteName('options') . ', ' . $db->quoteName('fieldcode') . ') VALUES '.
					'(' . $db->Quote('1') . ', ' . $db->Quote('group') . ', ' . $db->Quote('1') . ', ' . $db->Quote('1') . ', ' . $db->Quote('10') . ', ' . $db->Quote('100') . ', ' . $db->Quote('Basic Information') . ', ' . $db->Quote('Basic information for user') . ', ' . $db->Quote('1') . ', ' . $db->Quote('1') . ', ' . $db->Quote('1') . ', ' . $db->Quote('') . ', ' . $db->Quote('') . '),' .
					'(' . $db->Quote('2') . ', ' . $db->Quote('gender') . ', ' . $db->Quote('2') . ', ' . $db->Quote('1') .', ' . $db->Quote('10') . ', ' . $db->Quote('100') . ', ' . $db->Quote('Gender') . ', ' . $db->Quote('Select gender') . ', ' . $db->Quote('1') . ', ' . $db->Quote('1') . ', ' . $db->Quote('1') . ', ' . $db->Quote('COM_COMMUNITY_MALE'."\n".'COM_COMMUNITY_FEMALE') . ', ' . $db->Quote('FIELD_GENDER') . '),' .
					'(' . $db->Quote('3') . ', ' . $db->Quote('birthdate') .', ' . $db->Quote('3') .', ' . $db->Quote('1') .', ' . $db->Quote('10') .', ' . $db->Quote('100') .', ' . $db->Quote('Birthdate') .', ' . $db->Quote('Enter your date of birth so other users will know when to wish you a happy birthday ') .', ' . $db->Quote('1') .', ' . $db->Quote('1') .', ' . $db->Quote('1') .', ' . $db->Quote('') .', ' . $db->Quote('FIELD_BIRTHDATE') .'),' .
					'(' . $db->Quote('4') . ', ' . $db->Quote('textarea') . ', ' . $db->Quote('4') . ', ' . $db->Quote('1') . ', ' . $db->Quote('1') . ', ' . $db->Quote('800') . ', ' . $db->Quote('About me') . ', ' . $db->Quote('Tell us more about yourself') . ', ' . $db->Quote('1') . ', ' . $db->Quote('1') . ', ' . $db->Quote('1') . ', ' . $db->Quote('') . ', ' . $db->Quote('FIELD_ABOUTME') . '),' .
					'(' . $db->Quote('5') . ', ' . $db->Quote('group') . ', ' . $db->Quote('5') . ', ' . $db->Quote('1') . ', ' . $db->Quote('10') . ', ' . $db->Quote('100') . ', ' . $db->Quote('Contact Information') . ', ' . $db->Quote('Specify your contact details') . ', ' . $db->Quote('1') . ', ' . $db->Quote('1') . ', ' . $db->Quote('1') . ', ' . $db->Quote('') . ', ' . $db->Quote('') . '),' .
					'(' . $db->Quote('6') . ', ' . $db->Quote('text') . ', ' . $db->Quote('6') . ', ' . $db->Quote('1') . ', ' . $db->Quote('10') . ', ' . $db->Quote('100') . ', ' . $db->Quote('Mobile phone') .', ' . $db->Quote('Mobile carrier number so other users can contact you.') . ', ' . $db->Quote('1') . ', ' . $db->Quote('0') . ', ' . $db->Quote('1') . ', ' . $db->Quote('') . ', ' . $db->Quote('FIELD_MOBILE') . '),' .
					'(' . $db->Quote('7') . ', ' . $db->Quote('text') . ', ' . $db->Quote('7') . ', ' . $db->Quote('1') . ', ' . $db->Quote('10') . ', ' . $db->Quote('100') . ', ' . $db->Quote('Land phone') . ', ' . $db->Quote('Contact number so other users can contact you.') . ', ' . $db->Quote('1') . ', ' . $db->Quote('0') . ', ' . $db->Quote('1') . ', ' . $db->Quote('') . ', ' . $db->Quote('FIELD_LANDPHONE') . '),' .
					'(' . $db->Quote('8') . ', ' . $db->Quote('textarea') . ', ' . $db->Quote('8') . ', ' . $db->Quote('1') . ', ' . $db->Quote('10') . ', ' . $db->Quote('100') . ', ' . $db->Quote('Address') .', ' . $db->Quote('Your Address') . ', ' . $db->Quote('1') . ', ' . $db->Quote('1') . ', ' . $db->Quote('1') . ', ' . $db->Quote('') . ', ' . $db->Quote('FIELD_ADDRESS') . '),' .
					'(' . $db->Quote('9') . ', ' . $db->Quote('text') . ', ' . $db->Quote('9') . ', ' . $db->Quote('1') . ', ' . $db->Quote('10') . ', ' . $db->Quote('100') . ', ' . $db->Quote('State') . ', ' . $db->Quote('Your state') . ', ' . $db->Quote('1') . ', ' . $db->Quote('0') . ', ' . $db->Quote('1') . ', ' . $db->Quote('') . ', ' . $db->Quote('FIELD_STATE') . '),' .
					'(' . $db->Quote('10') . ', ' . $db->Quote('text') . ', ' . $db->Quote('10') .', ' . $db->Quote('1') .', ' . $db->Quote('10') .', ' . $db->Quote('100') .', ' . $db->Quote('City / Town') .', ' . $db->Quote('Your city or town name') .', ' . $db->Quote('1') .', ' . $db->Quote('1') .', ' . $db->Quote('1') .', ' . $db->Quote('') .', ' . $db->Quote('FIELD_CITY') . '),' .
					'(' . $db->Quote('11') . ', ' . $db->Quote('country') .', ' . $db->Quote('11') .', ' . $db->Quote('1') .', ' . $db->Quote('10') .', ' . $db->Quote('100') .', ' . $db->Quote('Country') .', ' . $db->Quote('Your country') .', ' . $db->Quote('1') .', ' . $db->Quote('1') .', ' . $db->Quote('1') .', ' . $db->Quote('Afghanistan'."\n".'Albania'."\n".'Algeria'."\n".'American Samoa'."\n".'Andorra'."\n".'Angola'."\n".'Anguilla'."\n".'Antarctica'."\n".'Antigua and Barbuda'."\n".'Argentina'."\n".'Armenia'."\n".'Aruba'."\n".'Australia'."\n".'Austria'."\n".'Azerbaijan'."\n".'Bahamas'."\n".'Bahrain'."\n".'Bangladesh'."\n".'Barbados'."\n".'Belarus'."\n".'Belgium'."\n".'Belize'."\n".'Benin'."\n".'Bermuda'."\n".'Bhutan'."\n".'Bolivia'."\n".'Bosnia and Herzegovina'."\n".'Botswana'."\n".'Bouvet Island'."\n".'Brazil'."\n".'British Indian Ocean Territory'."\n".'Brunei Darussalam'."\n".'Bulgaria'."\n".'Burkina Faso'."\n".'Burundi'."\n".'Cambodia'."\n".'Cameroon'."\n".'Canada'."\n".'Cape Verde'."\n".'Cayman Islands'."\n".'Central African Republic'."\n".'Chad'."\n".'Chile'."\n".'China'."\n".'Christmas Island'."\n".'Cocos (Keeling) Islands'."\n".'Colombia'."\n".'Comoros'."\n".'Congo'."\n".'Cook Islands'."\n".'Costa Rica'."\n".'Cote D\'Ivoire (Ivory Coast)'."\n".'Croatia (Hrvatska)'."\n".'Cuba'."\n".'Cyprus'."\n".'Czechoslovakia (former)'."\n".'Czech Republic'."\n".'Denmark'."\n".'Djibouti'."\n".'Dominica'."\n".'Dominican Republic'."\n".'East Timor'."\n".'Ecuador'."\n".'Egypt'."\n".'El Salvador'."\n".'Equatorial Guinea'."\n".'Eritrea'."\n".'Estonia'."\n".'Ethiopia'."\n".'Falkland Islands (Malvinas)'."\n".'Faroe Islands'."\n".'Fiji'."\n".'Finland'."\n".'France'."\n".'France, Metropolitan'."\n".'French Guiana'."\n".'French Polynesia'."\n".'French Southern Territories'."\n".'Gabon'."\n".'Gambia'."\n".'Georgia'."\n".'Germany'."\n".'Ghana'."\n".'Gibraltar'."\n".'Great Britain (UK)'."\n".'Greece'."\n".'Greenland'."\n".'Grenada'."\n".'Guadeloupe'."\n".'Guam'."\n".'Guatemala'."\n".'Guinea'."\n".'Guinea-Bissau'."\n".'Guyana'."\n".'Haiti'."\n".'Heard and McDonald Islands'."\n".'Honduras'."\n".'Hong Kong'."\n".'Hungary'."\n".'Iceland'."\n".'India'."\n".'Indonesia'."\n".'Iran'."\n".'Iraq'."\n".'Ireland'."\n".'Israel'."\n".'Italy'."\n".'Jamaica'."\n".'Japan'."\n".'Jordan'."\n".'Kazakhstan'."\n".'Kenya'."\n".'Kiribati'."\n".'Korea, North'."\n".'South Korea'."\n".'Kuwait'."\n".'Kyrgyzstan'."\n".'Laos'."\n".'Latvia'."\n".'Lebanon'."\n".'Lesotho'."\n".'Liberia'."\n".'Libya'."\n".'Liechtenstein'."\n".'Lithuania'."\n".'Luxembourg'."\n".'Macau'."\n".'Macedonia'."\n".'Madagascar'."\n".'Malawi'."\n".'Malaysia'."\n".'Maldives'."\n".'Mali'."\n".'Malta'."\n".'Marshall Islands'."\n".'Martinique'."\n".'Mauritania'."\n".'Mauritius'."\n".'Mayotte'."\n".'Mexico'."\n".'Micronesia'."\n".'Moldova'."\n".'Monaco'."\n".'Mongolia'."\n".'Montserrat'."\n".'Morocco'."\n".'Mozambique'."\n".'Myanmar'."\n".'Namibia'."\n".'Nauru'."\n".'Nepal'."\n".'Netherlands'."\n".'Netherlands Antilles'."\n".'Neutral Zone'."\n".'New Caledonia'."\n".'New Zealand'."\n".'Nicaragua'."\n".'Niger'."\n".'Nigeria'."\n".'Niue'."\n".'Norfolk Island'."\n".'Northern Mariana Islands'."\n".'Norway'."\n".'Oman'."\n".'Pakistan'."\n".'Palau'."\n".'Panama'."\n".'Papua New Guinea'."\n".'Paraguay'."\n".'Peru'."\n".'Philippines'."\n".'Pitcairn'."\n".'Poland'."\n".'Portugal'."\n".'Puerto Rico'."\n".'Qatar'."\n".'Reunion'."\n".'Romania'."\n".'Russian Federation'."\n".'Rwanda'."\n".'Saint Kitts and Nevis'."\n".'Saint Lucia'."\n".'Saint Vincent and the Grenadines'."\n".'Samoa'."\n".'San Marino'."\n".'Sao Tome and Principe'."\n".'Saudi Arabia'."\n".'Senegal'."\n".'Seychelles'."\n".'S. Georgia and S. Sandwich Isls.'."\n".'Sierra Leone'."\n".'Singapore'."\n".'Slovak Republic'."\n".'Slovenia'."\n".'Solomon Islands'."\n".'Somalia'."\n".'South Africa'."\n".'Spain'."\n".'Sri Lanka'."\n".'St. Helena'."\n".'St. Pierre and Miquelon'."\n".'Sudan'."\n".'Suriname'."\n".'Svalbard and Jan Mayen Islands'."\n".'Swaziland'."\n".'Sweden'."\n".'Switzerland'."\n".'Syria'."\n".'Taiwan'."\n".'Tajikistan'."\n".'Tanzania'."\n".'Thailand'."\n".'Togo'."\n".'Tokelau'."\n".'Tonga'."\n".'Trinidad and Tobago'."\n".'Tunisia'."\n".'Turkey'."\n".'Turkmenistan'."\n".'Turks and Caicos Islands'."\n".'Tuvalu'."\n".'Uganda'."\n".'Ukraine'."\n".'United Arab Emirates'."\n".'United Kingdom'."\n".'United States'."\n".'Uruguay'."\n".'US Minor Outlying Islands'."\n".'USSR (former)'."\n".'Uzbekistan'."\n".'Vanuatu'."\n".'Vatican City State (Holy Sea)'."\n".'Venezuela'."\n".'Viet Nam'."\n".'Virgin Islands (British)'."\n".'Virgin Islands (U.S.)'."\n".'Wallis and Futuna Islands'."\n".'Western Sahara'."\n".'Yemen'."\n".'Yugoslavia'."\n".'Zaire'."\n".'Zambia'."\n".'Zimbabwe') .', ' . $db->Quote('FIELD_COUNTRY'). '),' .
					'(' . $db->Quote('12') . ', ' . $db->Quote('url') . ', ' . $db->Quote('12') .', ' . $db->Quote('1') .', ' . $db->Quote('10') .', ' . $db->Quote('100') .', ' . $db->Quote('Website') .', ' . $db->Quote('Your website') .', ' . $db->Quote('1') .', ' . $db->Quote('1') .', ' . $db->Quote('1') .', ' . $db->Quote('') .', ' . $db->Quote('FIELD_WEBSITE') . '),' .
					'(' . $db->Quote('13') . ', ' . $db->Quote('group') . ', ' . $db->Quote('13') .', ' . $db->Quote('1') . ', ' . $db->Quote('10') .', ' . $db->Quote('100') .', ' . $db->Quote('Education') .', ' . $db->Quote('Educations') .', ' . $db->Quote('1') .', ' . $db->Quote('1') .', ' . $db->Quote('1') .', ' . $db->Quote('') . ', ' . $db->Quote('') .'),' .
					'(' . $db->Quote('14') . ', ' . $db->Quote('text') .', ' . $db->Quote('14') .', ' . $db->Quote('1') .', ' . $db->Quote('10') .', ' . $db->Quote('200') .', ' . $db->Quote('College / University') .', ' . $db->Quote('Your college or university name') .', ' . $db->Quote('1') .', ' . $db->Quote('1') .', ' . $db->Quote('1') .', ' . $db->Quote('') .', ' . $db->Quote('FIELD_COLLEGE') . '),' .
					'(' . $db->Quote('15') . ', ' . $db->Quote('text') . ', ' . $db->Quote('15') .', ' . $db->Quote('1') .', ' . $db->Quote('5') .', ' . $db->Quote('100') .', ' . $db->Quote('Graduation Year') .', ' . $db->Quote('Graduation year') .', ' . $db->Quote('1') .', ' . $db->Quote('1') .', ' . $db->Quote('1') .', ' . $db->Quote('') .', ' . $db->Quote('FIELD_GRADUATION') .')';

		$db->setQuery( $query );
		$db->execute();

		$hide_ids = array(6,7,8,12,13,14,15);

		$query= "UPDATE  "
			. $db->quoteName('#__community_fields')
			. " SET " 	. $db->quoteName('published') 	. "=" . $db->quote(0)
			. ", "		. $db->quoteName('visible') 	. "=" . $db->quote(0)
			. ", " 		. $db->quoteName('required') 	. "=" . $db->quote(0)
			. " WHERE " . $db->quoteName('id') ." IN (" . implode(',',$hide_ids) .")";

		$db->setQuery( $query );
		try {
			$db->execute();
		} catch (Exception $e) {
			return false;
		}

		return true;
	}
	/**
	 * Add default toolbar  menu
	 */
	static public function addDefaultToolbarMenus()
	{
		$db				= JFactory::getDBO();
		$file			= JPATH_ROOT . '/administrator/components/com_community/toolbar.xml';
		$menu_name		= JOOMLA_MENU_NAME;
		$menu_parent	= JOOMLA_MENU_PARENT;
		$menu_level		= JOOMLA_MENU_LEVEL;
		$items			= new SimpleXMLElement( $file , NULL , true );
		$items			= $items->items;

		$i	= 1;
		foreach( $items->children() as $item )
		{
			$obj				= new stdClass();
			$obj->$menu_name	= (string) $item->name;
			$obj->alias			= (string) $item->alias;
			$obj->link			= (string) $item->link;
			$obj->access		= (string) $item->access;
			$obj->menutype		= 'jomsocial';
			$obj->type			= 'component';
			$obj->published		= 1;
			$obj->$menu_parent	= JOOMLA_MENU_ROOT_PARENT;
			$obj->language		= '*';

			$childs	= $item->childs;
			//J1.6: menu item ordering follow lft and rgt
			$query 	= 'SELECT ' . $db->quoteName( 'rgt' ) . ' '
					. 'FROM ' . $db->quoteName( '#__menu' ) . ' '
					. 'ORDER BY ' . $db->quoteName( 'rgt' ) . ' DESC LIMIT 1';
			$db->setQuery( $query );
			$obj->lft 	= $db->loadResult() + 1;
			$totalchild = $childs?count($childs->children()):0;
			$obj->rgt	= $obj->lft + $totalchild * 2 + 1;

			try {
				$db->insertObject('#__menu', $obj);
			} catch (Exception $e) {
				return false;
			}

			$parentId		= $db->insertid();

			if( $childs )
			{
				$x	= 1;
				foreach( $childs->children() as $child )
				{
					$childObj		= new stdClass();

					$childObj->$menu_name	= (string) $child->name;
					$childObj->alias		= (string) $child->alias;
					$childObj->link			= (string) $child->link;
					$childObj->access		= (string) $item->access;
					$childObj->menutype		= 'jomsocial';
					$childObj->type			= 'component';
					$childObj->published	= 1;
					$childObj->$menu_parent	= $parentId;
					$childObj->$menu_level	= JOOMLA_MENU_LEVEL_PARENT + 1;
					$childObj->language		= '*';
					//J1.6: menu item ordering follow lft and rgt
					$childObj->lft			= $obj->lft + ($x - 1)* 2 + 1;
					$childObj->rgt			= $childObj->lft + 1;

					try {
						$db->insertObject('#__menu', $childObj);
					} catch (Exception $e) {
						return false;
					}

					$x++;
				}
			}
			$i++;
		}
		return true;
	}
}