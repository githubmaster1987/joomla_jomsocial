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

require_once( JPATH_ROOT .'/components/com_community/models/models.php' );

class CommunityModelWall extends JCCModel
{
	var $_pagination	= '';

	/**
	 * Return 1 wall object
	 */
	public function get($id , $default = null ){
		$db= JFactory::getDBO();

		$strSQL	= 'SELECT a.* , b.' . $db->quoteName('name').' FROM ' . $db->quoteName('#__community_wall').' AS a '
				. ' INNER JOIN ' . $db->quoteName('#__users').' AS b '
				. ' WHERE b.' . $db->quoteName('id').'=a.' . $db->quoteName('post_by')
				. ' AND a.' . $db->quoteName('id').'=' . $db->Quote( $id ) ;

		$db->setQuery( $strSQL );

		try {
			$result = $db->loadObjectList();
		} catch (Exception $e) {
			JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
		}
		if(empty($result)){
            JFactory::getApplication()->enqueueMessage('Invalid id', 'error');
        }

		return $result[0];
	}

	/**
	 * Return an array of wall post
	 */
	public function getPost($type, $cid, $limit, $limitstart, $order = 'DESC'){
		$db= JFactory::getDBO();

		$strSQL	= 'SELECT a.* , b.' . $db->quoteName('name').' FROM ' . $db->quoteName('#__community_wall').' AS a '
				. ' INNER JOIN ' . $db->quoteName('#__users').' AS b '
				. ' WHERE b.' . $db->quoteName('id').'=a.' . $db->quoteName('post_by')
				. ' AND a.' . $db->quoteName('type').'=' . $db->Quote( $type ) . ' '
				. ' AND a.' . $db->quoteName('contentid').'=' . $db->Quote( $cid )
				. ' ORDER BY a.' . $db->quoteName('date').' '. $order;

		$strSQL.= " LIMIT $limitstart , $limit ";

		$db->setQuery( $strSQL );

        try {
            $result = $db->loadObjectList();
        } catch (Exception $e) {
            JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
        }
		return $result;
	}

	/*
	 * Count the total results from the post
	 */
	public function getPostCount($type, $cid, $params = ''){
		$db= JFactory::getDBO();

		$params = ($params == '') ? '' : ' AND a.'.$db->quoteName('params').'='.$db->quote($params).' ';
		$query	= 'SELECT count(a.id) FROM ' . $db->quoteName('#__community_wall').' AS a '
				. ' INNER JOIN ' . $db->quoteName('#__users').' AS b '
				. ' WHERE b.' . $db->quoteName('id').'=a.' . $db->quoteName('post_by')
				. ' AND a.' . $db->quoteName('type').'=' . $db->Quote( $type ) . ' '
				. $params
				. ' AND a.' . $db->quoteName('contentid').'=' . $db->Quote( $cid );

		$db->setQuery( $query );

        try {
            $result = $db->loadResult();
			return $result;
        } catch (Exception $e) {
            JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
        }
	}


	/**
	 * Store wall post
	 */
	public function addPost($type, $cid, $post_by, $message){
		$table = JTable::getInstance('Wall', 'CTable');
		$table->type = $type;
		$table->contentid = $cid;
		$table->post_by = $post_by;
		$table->message = $message;
		$table->store();

		return $table->id;
	}

	/**
	 * Return all the CTableWall object with the given type/cid
	 *
	 */
	public function getAllPost($type, $cid, $limit = 0, $limitstart = 0, $params = '')
	{
		/**
		 * Modified by Adam Lim on 14 July 2011
		 * Added ORDER BY date ASC to avoid messed up message display possibility
		 */
		$db		= JFactory::getDBO();

		$params = ($params == '') ? '' : ' AND '.$db->quoteName('params').'='.$db->quote($params).' ';

		$query	= 'SELECT * FROM ' . $db->quoteName( '#__community_wall' ) . ' '
				. 'WHERE ' . $db->quoteName( 'contentid' ) . '=' . $db->Quote( $cid ) . ' '
				. 'AND ' . $db->quoteName( 'type' ) . '=' . $db->Quote( $type ) . ' '
				.$params
				. 'ORDER BY date ASC';

		if($limit){
			$query.= " LIMIT $limitstart , $limit ";
		}

		$db->setQuery( $query );
        try {
            $results = $db->loadObjectList();
        } catch (Exception $e) {
            JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
        }

		 $posts = array();
		 foreach($results as $row)
		 {
		 	$table = JTable::getInstance('Wall', 'CTable');
		 	$table->bind($row);
		 	$posts[] = $table;
		 }

		 return $posts;
	}

	/**
	 * Return all the user  object with the given type/cid
	 *
	 */
	public function getAllPostUsers($type, $cid, $exclude=null)
	{
		/**
		 * Modified by Adam Lim on 14 July 2011
		 * Added ORDER BY date ASC to avoid messed up message display possibility
		 */
		$db		= JFactory::getDBO();
		$whereAnd = '';
		if($exclude!=null){
			$whereAnd = ' AND ' . $db->quoteName('post_by') . '!=' . $db->Quote( $exclude );
		}
		$query	= 'SELECT DISTINCT(post_by) FROM ' . $db->quoteName( '#__community_wall' ) . ' '
				. 'WHERE ' . $db->quoteName( 'contentid' ) . '=' . $db->Quote( $cid ) . ' '
				. $whereAnd
				. 'AND ' . $db->quoteName( 'type' ) . '=' . $db->Quote( $type ) . ' '
				. 'ORDER BY date ASC';

		$db->setQuery( $query );
        try {
            $results = $db->loadColumn();
        } catch (Exception $e) {
            JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
        }

		 return $results;
	}
	/**
	 * This function removes all wall post from specific contentid
	 **/
	public function deleteAllChildPosts( $uniqueId , $type )
	{
		CError::assert( $uniqueId , '' , '!empty' , __FILE__ , __LINE__ );
		CError::assert( $type , '' , '!empty' , __FILE__ , __LINE__ );

		$db	=   JFactory::getDBO();

		$query	=   'DELETE FROM ' . $db->quoteName( '#__community_wall' ) . ' '
			    . 'WHERE ' . $db->quoteName( 'contentid' ) . '=' . $db->Quote( $uniqueId ) . ' '
			    . 'AND ' . $db->quoteName( 'type' ) . '=' . $db->Quote( $type );

		$db->setQuery( $query );
        try {
            $db->execute();
        } catch (Exception $e) {
            JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
        }

		return true;
	}

    /**
     * @param $type
     * @param $contentId
     * @return mixed
     */
    public function deletePostByType($type, $contentId)
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        $conditions = array(
            $db->quoteName('type') . ' = ' . $db->quote($type),
            $db->quoteName('contentid') . ' = ' . $db->quote($contentId)
        );

        $query->delete($db->quoteName('#__community_wall'));
        $query->where($conditions);

        $db->setQuery($query);
        $result = $db->execute();

        return $result;
    }

    /**
     * Deletes a wall entry
     * @param $id
     * @return bool
     */
	 public function deletePost($id)
	 {
	 	CError::assert( $id , '' , '!empty' , __FILE__ , __LINE__ );

		$db = JFactory::getDBO();

		//@todo check content id belong valid user b4 delete
		$query	= 'DELETE FROM ' . $db->quoteName('#__community_wall') . ' '
				. 'WHERE ' . $db->quoteName('id') . '=' . $db->Quote( $id );

		 $db->setQuery($query);
         try {
             $db->execute();
         } catch (Exception $e) {
             JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
         }

		// Post an event trigger
		$args 	= array();
		$args[]	= $id;

		//CFactory::load( 'libraries' , 'apps' );
		$appsLib	= CAppPlugins::getInstance();
		$appsLib->loadApplications();
		$appsLib->triggerEvent( 'onAfterWallDelete' , $args );

		return true;
	}

	 /**
	  *	Gets the count of wall entries for specific item
	  *
	  * @params uniqueId	The unique id for the speicific item
	  * @params	type		The unique type for the specific item
	  **/
	 public function getCount( $uniqueId , $type )
	 {
         $jinput = JFactory::getApplication()->input;
	 	$cache = CFactory::getFastCache();
		$cacheid = __FILE__ . __LINE__ . serialize(func_get_args()) . serialize($jinput->getArray());

		if( $data = $cache->get( $cacheid ) )
		{
			return $data;
		}

	 	CError::assert( $uniqueId , '' , '!empty' , __FILE__ , __LINE__ );
	 	$db		= $this->getDBO();

	 	$query	= 'SELECT COUNT(*) FROM ' . $db->quoteName( '#__community_wall' )
	 			. 'WHERE ' . $db->quoteName('contentid') . '=' . $db->Quote( $uniqueId )
	 			. 'AND ' . $db->quoteName( 'type' ) . '=' . $db->Quote( $type );

	 	$db->setQuery( $query );
	 	$count	= $db->loadResult();

		$cache->store($count, $cacheid);
	 	return $count;
	 }


	public function getPagination() {
		return $this->_pagination;
	}

	public function getPostList()
	{
		$db = $this->getDBO();

		$query = 'SELECT * FROM '. $db->quoteName('#__community_wall') . ' ORDER BY id DESC LIMIT 0,5';

		$db->setQuery($query);

		$results = $db->loadObjectList();

		return $results;
	}

    /**
     * @param $contentid
     * @param string $type
     * @return mixed
     */
    public function getPostUserslist($contentid, $type = ''){
		$db = $this->getDBO();

        //type is added to differentiate the type!
        $extra = '';
        if($type != ''){
            $extra = ' AND '.$db->quoteName('type'). '='. $db->quote($type);
        }

		$query = 'SELECT DISTINCT '.$db->quoteName('post_by') .' as userId FROM '.$db->quoteName('#__community_wall').' WHERE '.$db->quoteName('contentid'). ' = '.$db->quote($contentid).$extra.' AND '.$db->quoteName('comment').' <> ""';

		$db->setQuery($query);

		return $db->loadAssocList();
	}
}