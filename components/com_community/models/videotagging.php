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

jimport('joomla.application.component.model');

require_once( JPATH_ROOT .'/components/com_community/models/models.php' );

/**
 * JomSocial Model file for video tagging
 */

class CommunityModelVideoTagging extends JCCModel
{

	var	$_error	= null;

	/* public array to retrieve return value */
	public $return_value = array();

	public function getError( $i = null, $toString = true )
	{
		return $this->_error;
	}

	public function isTagExists($videoId, $userId)
	{
		$db		= $this->getDBO();

		$query	= 'SELECT COUNT(1) AS CNT FROM '.$db->quoteName('#__community_videos_tag');
		$query .= ' WHERE '.$db->quoteName('videoid').' = ' . $db->Quote($videoId);
		$query .= ' AND '.$db->quoteName('userid').' = ' . $db->Quote($userId);

		$db->setQuery($query);

		try {
			$result = $db->loadResult();
		} catch (Exception $e) {
			JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
		}
		return (empty($result)) ? false : true;
	}


	public function addTag( $videoId, $userId)
	{
		$db		= $this->getDBO();
		$my		= CFactory::getUser();
		$date	= JDate::getInstance(); //get the time without any offset!

		$data				= new stdClass();
		$data->videoid		= $videoId;
		$data->userid		= $userId;
		$data->created_by	= $my->id;
		$data->created		= $date->toSql();

        try {
            $db->insertObject('#__community_videos_tag', $data);
            $this->_error	= null;
            $this->return_value[__FUNCTION__] = true;
        } catch (Exception $e) {
            $this->return_value[__FUNCTION__] = false;
            throw $e;
        }

		return $this;
	}

	public function removeTag( $videoId, $userId )
	{
		$db		= $this->getDBO();

		$query = 'DELETE FROM '.$db->quoteName('#__community_videos_tag');
		$query .= ' WHERE '.$db->quoteName('videoid').' = ' . $db->Quote($videoId);
		$query .= ' AND '.$db->quoteName('userid').' = ' . $db->Quote($userId);

		$db->setQuery($query);
		try {
			$db->execute();
		} catch (Exception $e) {
			JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
			return false;
		}

		return true;
	}

	public function removeTagByVideo($videoId)
	{
		$db		= $this->getDBO();

		$query = 'DELETE FROM '.$db->quoteName('#__community_videos_tag');
		$query .= ' WHERE '.$db->quoteName('videoid').' = ' . $db->Quote($videoId);

		$db->setQuery($query);
		try {
			$db->execute();
		} catch (Exception $e) {
			JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
			return false;
		}

		return true;
	}

	public function getTagId( $videoId, $userId )
	{
		$db		= $this->getDBO();

		$query = 'SELECT '.$db->quoteName('id').' FROM '.$db->quoteName('#__community_videos_tag');
		$query .= ' WHERE '.$db->quoteName('videoid').' = ' . $db->Quote($videoId);
		$query .= ' AND '.$db->quoteName('userid').' = ' . $db->Quote($userId);

		$db->setQuery($query);

		try {
			$result = $db->loadResult();
		} catch (Exception $e) {
			JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
		}

		return $result;
	}

	/*
	 * @since 2.6
	 * To retrieve videos that a user has been tagged in
	 */

	public function getTaggedVideosByUser( $userId ){
		$db =$this->getDBO();

		$query = "SELECT b.* FROM ".$db->quoteName('#__community_videos_tag')." as a, "
				 . $db->quoteName('#__community_videos') ." as b WHERE a.".$db->quoteName('userid')."=".$db->Quote($userId)
				 . " AND b.".$db->quoteName('id')." = a.".$db->quoteName('videoid');

		$db->setQuery($query);
		try {
			$result = $db->loadObjectList();
		} catch (Exception $e) {
			JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
		}

		return $result;

	}


	public function getTaggedList( $videoId )
	{
		$db = $this->getDBO();

		$query = 'SELECT a.* FROM '.$db->quoteName('#__community_videos_tag').' as a';
		$query .= ' JOIN '.$db->quoteName('#__users').'as b ON a.'.$db->quoteName('userid').'=b.'.$db->quoteName('id').' AND b.'.$db->quoteName('block').'=0';
		$query .= ' WHERE a.'.$db->quoteName('videoid').' = ' . $db->Quote($videoId);
		$query .= ' ORDER BY a.'.$db->quoteName('id');

		$db->setQuery($query);
		try {
			$result = $db->loadObjectList();
		} catch (Exception $e) {
			JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
		}
		return $result;
	}

	public function getFriendList( $videoId )
	{
		$db = $this->getDBO();
		$my	= CFactory::getUser();

		$query	= 'SELECT DISTINCT(a.'.$db->quoteName('connect_to').') AS id ';
		$query .= ' FROM '.$db->quoteName('#__community_connection').' AS a';
		$query .= ' INNER JOIN '.$db->quoteName('#__users').' AS b';
		$query .= ' ON a.'.$db->quoteName('connect_from').' = ' . $db->Quote( $my->id ) ;
		$query .= ' AND a.'.$db->quoteName('connect_to').' = b.'.$db->quoteName('id');
		$query .= ' AND a.'.$db->quoteName('status').' = '.$db->Quote('1');
		$query .= ' AND NOT EXISTS (';
		$query .= ' SELECT '.$db->quoteName('userid').' FROM '.$db->quoteName('#__community_videos_tag').' AS c'
					.' WHERE c.'.$db->quoteName('userid').' = a.`'.$db->quoteName('connect_to')
					.' AND c.'.$db->quoteName('videoid').' = ' . $db->Quote( $videoId );
		$query .= ')';

		$db->setQuery($query);
		try {
			$result = $db->loadObjectList();
		} catch (Exception $e) {
			JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
		}

		return $result;
	}




}

?>
