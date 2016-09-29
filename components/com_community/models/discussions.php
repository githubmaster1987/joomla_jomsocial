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

require_once ( JPATH_ROOT .'/components/com_community/models/models.php');

class CommunityModelDiscussions extends JCCModel
{
	/**
	 * Configuration data
	 *
	 * @var object	JPagination object
	 **/
	var $_pagination	= '';

	/**
	 * Configuration data
	 *
	 * @var object	JPagination object
	 **/
	var $total			= '';

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();

		$mainframe	= JFactory::getApplication();
		$jinput 	= $mainframe->input;

		// Get pagination request variables
 	 	$limit		= ($mainframe->get('list_limit') == 0) ? 5 : $mainframe->get('list_limit');
	    $limitstart = $jinput->request->get('limitstart', 0, 'INT');

	    if(empty($limitstart))
 	 	{
 	 		$limitstart = $jinput->get('limitstart', 0, 'uint');
 	 	}

		// In case limit has been changed, adjust it
		$limitstart = ($limit != 0 ? (floor($limitstart / $limit) * $limit) : 0);

		$this->setState('limit', $limit);
		$this->setState('limitstart', $limitstart);
	}

	/**
	 * Method to get a pagination object for the events
	 *
	 * @access public
	 * @return integer
	 */
	public function getPagination()
	{
		return $this->_pagination;
	}

	/**
	 * Get list of discussion topics
	 *
	 * @param	$id	The group id
	 * @param	$limit Limit
	 **/
	public function getDiscussionTopics( $groupId , $limit = 0 , $order = '' )
	{
		$db			= $this->getDBO();
		$limit		= ($limit == 0) ? $this->getState( 'limit' ) : $limit;
		$limitstart	= $this->getState( 'limitstart' );

		$query	= 'SELECT COUNT(*) FROM ' . $db->quoteName('#__community_groups_discuss') . ' '
				. 'WHERE ' . $db->quoteName( 'groupid' ) . '=' . $db->Quote( $groupId )
				. 'AND ' . $db->quoteName('parentid') .'=' . $db->Quote( '0' );

		$db->setQuery( $query );
		try {
			$total = $db->loadResult();
		} catch (Exception $e) {
			JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
		}
		$this->total	= $total;

		if( empty($this->_pagination) )
		{
			jimport('joomla.html.pagination');

			$this->_pagination	= new JPagination( $total , $limitstart , $limit);
		}

		$orderByQuery	= '';
		switch( $order )
		{
            case 1:
                $orderByQuery = 'ORDER BY a.' . $db->quoteName('id') .' DESC ';
                break;
			default:
				$orderByQuery = 'ORDER BY a.' . $db->quoteName('lastreplied') .' DESC ';
				break;
		}

		$query		= 'SELECT a.*, COUNT( b.' . $db->quoteName('id').' ) AS count, b.' . $db->quoteName('comment') .' AS lastmessage , b.' . $db->quoteName('post_by') .' AS lastmessageby '
					. ' FROM ' . $db->quoteName( '#__community_groups_discuss' ) . ' AS a '
					. ' LEFT JOIN ' . $db->quoteName( '#__community_wall' ) . ' AS b ON b.' . $db->quoteName('contentid') .'=a.' . $db->quoteName('id')
					. ' AND b.' . $db->quoteName('date') .'=( SELECT max( date ) FROM ' . $db->quoteName('#__community_wall').' WHERE ' . $db->quoteName('contentid').'=a.' . $db->quoteName('id').' ) '
					. ' AND b.' . $db->quoteName('type').'=' . $db->Quote( 'discussions' )
					. ' LEFT JOIN ' . $db->quoteName( '#__community_wall' ) . ' AS c ON c.' . $db->quoteName('contentid').'=a.' . $db->quoteName('id')
					. ' AND c.' . $db->quoteName('type').'=' . $db->Quote( 'discussions')
					. ' WHERE a.' . $db->quoteName('groupid').'=' . $db->Quote( $groupId )
					. ' AND a.' . $db->quoteName('parentid').'=' . $db->Quote( '0' )
					. ' GROUP BY a.' . $db->quoteName('id')
					. $orderByQuery
					. 'LIMIT ' . $limitstart . ',' . $limit;

		$db->setQuery( $query );
		try {
			$result = $db->loadObjectList();
		} catch (Exception $e) {
			JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
		}

		return $result;
	}

	/**
	 * Method to get the last replier information from specific discussion
	 *
	 * @params $discussionId	The specific discussion row id
	 **/
	public function getLastReplier( $discussionId )
	{
		$db		= $this->getDBO();

		$query	= 'SELECT * FROM ' . $db->quoteName( '#__community_wall' ) . ' '
				. 'WHERE ' . $db->quoteName( 'contentid' ) . '=' . $db->Quote( $discussionId ) . ' '
				. 'AND ' . $db->quoteName( 'type' ) . '=' . $db->Quote( 'discussions' )
				. 'ORDER BY ' . $db->quoteName('date').' DESC LIMIT 1';
		$db->setQuery( $query );
		try {
			$result = $db->loadObject();
		} catch (Exception $e) {
			JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
		}

		return $result;
	}

	public function getRepliers( $discussionId , $groupId )
	{
		$db		= JFactory::getDBO();
		$query	= 'SELECT DISTINCT(a.' . $db->quoteName('post_by').') FROM ' . $db->quoteName( '#__community_wall' ) . ' AS a '
				. ' INNER JOIN ' . $db->quoteName('#__community_groups_members').' AS b '
				. ' ON b.' . $db->quoteName('groupid').'=' . $db->Quote( $groupId )
				. ' WHERE a.' . $db->quoteName( 'contentid' ) . '=' . $db->Quote( $discussionId )
				. ' AND a.' . $db->quoteName( 'type' ) . '=' . $db->Quote( 'discussions' )
				. ' AND a.' . $db->quoteName('post_by').'=b.' . $db->quoteName('memberid');

		$db->setQuery( $query );
		return $db->loadColumn();
	}

	/**
	 * Return a list of discussion replies.
	 *
	 * @param	int		$topicId	The replies for specific topic id.
	 * @return	Array	An array of database objects.
	 **/
	public function getReplies( $topicId )
	{
		$db		= JFactory::getDBO();

		$query	= 'SELECT a.* , b.' . $db->quoteName('name').' FROM ' . $db->quoteName('#__community_wall').' AS a '
				. ' INNER JOIN ' . $db->quoteName('#__users').' AS b '
				. ' WHERE b.' . $db->quoteName('id').'=a.' . $db->quoteName('post_by')
				. ' AND a.' . $db->quoteName('type').'=' . $db->Quote( 'discussions' )
				. ' AND a.' . $db->quoteName('contentid').'=' . $db->Quote( $topicId )
				. ' ORDER BY a.' . $db->quoteName('date').' DESC ';

		try {
			$db->setQuery($query);
		} catch (Exception $e) {
			JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
		}

		$result	= $db->loadObjectList();

		return $result;
	}

	/*
	 * @since 2.4
	 * @param keywords : array of keyword to be searched to match the title
	 * @param exclude : exclude the id of the discussion
	 */
	public function getRelatedDiscussion( $keywords = array(''), $exclude = array(''))
	{
        $jinput = JFactory::getApplication()->input;
		$db	= $this->getDBO();
		$topicid = $jinput->getInt('topicid',0);
        $matchQuery = '';
		$excludeQuery = '';

		if(!empty($keywords))
		{
			foreach($keywords as $words)
			{
				$matchQuery .= '  a.' . $db->quoteName('title').' LIKE '.$db->Quote( '%'.$words.'%' ). ' OR ';
			}
			$matchQuery = ' AND ('.$matchQuery.' 0 ) ';
		}

		if(!empty($exclude))
		{
			foreach($exclude as $id)
			{
				$excludeQuery .= '  a.' . $db->quoteName('id').' <> '.$db->Quote( $id ). ' AND ';
			}
			$excludeQuery = ' AND ('.$excludeQuery.' 1 ) ';
		}

		$query		= 'SELECT a.*,d.name as group_name, COUNT( b.' . $db->quoteName('id').' ) AS count, b.' . $db->quoteName('comment') .' AS lastmessage '
					. ' FROM ' . $db->quoteName( '#__community_groups_discuss' ) . ' AS a '
					. ' LEFT JOIN ' . $db->quoteName( '#__community_wall' ) . ' AS b ON b.' . $db->quoteName('contentid') .'=a.' . $db->quoteName('id')
					. ' AND b.' . $db->quoteName('date') .'=( SELECT max( date ) FROM ' . $db->quoteName('#__community_wall').' WHERE ' . $db->quoteName('contentid').'=a.' . $db->quoteName('id').' ) '
					. ' AND b.' . $db->quoteName('type').'=' . $db->Quote( 'discussions' )
					. ' LEFT JOIN ' . $db->quoteName( '#__community_wall' ) . ' AS c ON c.' . $db->quoteName('contentid').'=a.' . $db->quoteName('id')
					. ' AND c.' . $db->quoteName('type').'=' . $db->Quote( 'discussions')
					. ' LEFT JOIN ' . $db->quoteName( '#__community_groups' ) . ' AS d ON a.' . $db->quoteName('groupid').'=d.' . $db->quoteName('id')
					. ' WHERE a.' . $db->quoteName('lock').'=' . $db->Quote( '0' )
					. ' AND d.' . $db->quoteName('approvals').'=' . $db->Quote( '0' )
					. ' AND a.' . $db->quoteName('parentid').'=' . $db->Quote( '0' )
                    . ' AND a.' . $db->quoteName('id').'!=' . $db->Quote($topicid)
					. ' AND d.' . $db->quoteName('published') . '=' .$db->Quote('1')
					. $matchQuery
					. $excludeQuery
					. ' GROUP BY a.' . $db->quoteName('id');

		try {
			$db->setQuery($query);
		} catch (Exception $e) {
			JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
		}

		$result	= $db->loadObjectList();

		return $result;
	}

	public function getSiteDiscussionCount()
	{
		$db	= $this->getDBO();

		$sql = 'SELECT COUNT(*) FROM '. $db->quoteName('#__community_groups_discuss');

		$db->setQuery($sql);

		$result = $db->loadResult();

		return $result;
	}
}
