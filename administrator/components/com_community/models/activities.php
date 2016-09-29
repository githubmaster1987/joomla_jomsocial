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


class CommunityModelActivities extends JModelLegacy
{
	/**
	 * Configuration data
	 *
	 * @var object	JPagination object
	 **/
	var $_pagination;

	/**
	 * Constructor
	 */
	public function __construct()
	{
		$mainframe	= JFactory::getApplication();

		// Call the parents constructor
		parent::__construct();

		// Get the pagination request variables
		$limit		= $mainframe->getUserStateFromRequest( 'global.list.limit', 'limit', $mainframe->get('list_limit'), 'int' );
		$limitstart	= $mainframe->getUserStateFromRequest( 'com_community.limitstart', 'limitstart', 0, 'int' );

		// In case limit has been changed, adjust limitstart accordingly
		$limitstart = ($limit != 0 ? (floor($limitstart / $limit) * $limit) : 0);

		$this->setState('limit', $limit);
		$this->setState('limitstart', $limitstart);
	}

	/**
	 * Retrieves the JPagination object
	 *
	 * @return object	JPagination object
	 **/
	public function &getPagination()
	{
		// Lets load the content if it doesn't already exist
		if ( empty( $this->_pagination ) )
		{
			jimport('joomla.html.pagination');
			$this->_pagination = new JPagination( $this->getTotal(), $this->getState('limitstart'), $this->getState('limit') );
		}

		return $this->_pagination;
	}

	public function getTotal()
	{
		// Load total number of rows
		if( empty($this->_total) )
		{
			$this->_total	= $this->_getListCount( $this->_buildQuery() );
		}

		return $this->_total;
	}

	public function _buildQuery()
	{
		$db			= JFactory::getDBO();
		$mainframe	= JFactory::getApplication();
		$jinput 	= $mainframe->input;

		$actor		= $jinput->get('actor', '', 'NONE');
		$archived	= $jinput->getInt( 'archived' , 0 );
		$app		= $jinput->get('app' , 'none', 'NONE');
		$where		= array();
		$userId 	= 0;

		if(!empty($actor)){
			$userId		= CUserHelper::getUserId( $actor );
		}
		if( $userId != 0 )
		{
			$where[]	= 'actor=' . $db->Quote( $userId ) . ' ';
		}

		if( $archived != 0 )
		{
			$archived	= $archived - 1;
			$where[]	= 'archived=' . $db->Quote( $archived ) . ' ';
		}

		if( $app != 'none' )
		{

			$where[]	= 'app=' . $db->Quote( $app );
		}

		$query	= 'SELECT * FROM ' . $db->quoteName( '#__community_activities' );

		if( !empty($where) )
		{
			for( $i = 0; $i < count( $where ); $i++ )
			{
				if( $i == 0 )
				{
					$query	.= ' WHERE ';
				}
				else
				{
					$query	.= ' AND ';
				}
				$query	.= $where[ $i ];
			}
		}

		$query	.= ' ORDER BY created DESC';
		return $query;
	}

	public function getFilterApps()
	{
		$db		= $this->getDBO();

		$query	= 'SELECT DISTINCT app FROM ' . $db->quoteName( '#__community_activities' );
		$db->setQuery( $query );
		try {
			$result = $db->loadObjectList();
		} catch (Exception $e) {
			return false;
		}

		return $result;
	}

	public function getActivities()
	{
		if(empty($this->_data))
		{
			$query			= $this->_buildQuery();
			$this->_data	= $this->_getList( $query , $this->getState('limitstart'), $this->getState('limit') );
		}
		return $this->_data;
	}

	public function delete( $activityId )
	{
		$db		= JFactory::getDBO();
		$query	= 'DELETE FROM ' . $db->quoteName( '#__community_activities' ) . ' WHERE '
				. $db->quoteName( 'id' ) . '=' . $db->Quote( $activityId );
		$db->setQuery( $query );
		try {
			$db->execute();
		} catch (Exception $e) {
			return false;
		}

		$query  = 'DELETE FROM ' . $db->quoteName( '#__community_featured' ) . ' WHERE '
		        . $db->quoteName( 'cid' ) . '=' . $db->Quote( $activityId );
		$db->setQuery( $query );
		$db->Query();

		return true;
	}

	public function purge()
	{
		$db		= JFactory::getDBO();
		$query	= 'DELETE FROM ' . $db->quoteName( '#__community_activities' );
		$db->setQuery( $query );

		try {
			$db->execute();
		} catch (Exception $e) {
			return false;
		}

		$query  = 'DELETE FROM ' . $db->quoteName( '#__community_featured' );
		$db->setQuery( $query );
		$db->Query();

		return true;
	}

	public function getUserStatus()
	{
		$db		= JFactory::getDBO();
		$query	= 'SELECT * FROM ' . $db->quoteName( '#__community_activities' ) . ' WHERE '
				. $db->quoteName( 'app' ) . '=' . $db->Quote( 'profile' )
				. 'AND '.$db->quoteName('comment_type') .' = '. $db->Quote('profile.status')
				. 'AND '.$db->quoteName('actor').' = '. $db->quoteName('target')
				. 'ORDER BY '.$db->quoteName('id').'DESC LIMIT 0, 5';

		$db->setQuery( $query );
		$result	= $db->loadObjectList();

		return $result;
	}

	public function archiveAll()
	{
		$db = $this->getDBO();

	 	$sql = 'UPDATE ' . $db->quoteName('#__community_activities') . ' act'
                . ' SET act.' . $db->quoteName('archived') . ' = ' . $db->Quote(1)
                . ' WHERE '
                /* Only archive those not archived yet */
                . $db->quoteName('archived') . '=' .
                $db->Quote(0);
        $db->setQuery($sql);

        return $db->execute();
	}

	public function archiveSelected($id)
	{
		$db = $this->getDBO();

	 	$sql = 'UPDATE ' . $db->quoteName('#__community_activities') . ' act'
                . ' SET act.' . $db->quoteName('archived') . ' = ' . $db->Quote(1)
                . ' WHERE '
                /* Only archive those not archived yet */
                . $db->quoteName('archived') . '=' . $db->Quote(0)
                . ' AND act.'.$db->quoteName('id') . '='.$db->Quote($id);
        $db->setQuery($sql);

        return $db->execute();
	}

	public function getActCount(){
		$db = $this->getDBO();

		$sql = 'SELECT COUNT(*) FROM '. $db->quoteName('#__community_activities');

		$db->setQuery($sql);
		return $db->loadResult();
	}

	public function removeActivity($app, $uniqueId)
            {
                $db = $this->getDBO();

                /*
                 * @todo add in additional info if needed
                 * when removing photo app, we need to remove the likes and comments as well
                 */

                $additionalQuery = '';

                switch ($app) {
                    case 'photos' :
                        //before we remove anything, lets check if this photo is included in the params of activity
                        // that might be more than one photo
                        $db->setQuery(
                            "SELECT albumid FROM ".$db->quoteName('#__community_photos')." WHERE id=".$db->quote($uniqueId)
                        );

                        $albumId = $db->loadResult();

                        $db->setQuery(
                          "SELECT id, params FROM ".$db->quoteName('#__community_activities'). " WHERE "
                            .$db->quoteName('app') . '=' . $db->Quote($app)." AND "
                            .$db->quoteName('cid') . '=' . $db->Quote($albumId)
                        );

                        $activities = $db->loadObjectList();

                        if(count($activities) == 0){
                            return;
                        }

                        //search through the parameters of the activities
                        foreach($activities as $activity){
                            $params = new CParameter($activity->params);
                            $photoIds = $params->get('photosId');
                            $photoIds = explode(',',$photoIds);

                            if(in_array($uniqueId, $photoIds)){
                                if(count($photoIds) > 1){
                                    //do not delete this activities as there is another photo associated with this activity
                                    if(($key = array_search($uniqueId, $photoIds)) !== false) {
                                        unset($photoIds[$key]);
                                    }

                                    $params->set('photosId',implode(',',$photoIds));
                                    $activityTable = JTable::getInstance('Activity', 'CTable');
                                    $activityTable->load($uniqueId);

                                    //just update the activity will do
                                    $activityTable->params = $params->toString();
                                    $activityTable->store();
                                }else{
                                    // just delete the activity
                                    $db->setQuery(
                                        "DELETE FROM ".$db->quoteName('#__community_activities')." WHERE "
                                        .$db->quoteName('id').' = '.$db->quote($activity->id)
                                    );
                                    $db->execute();
                                }
                            }
                        }


                        return;//return as the additional steps are not needed

                        //we should remove the likes and comments
                        $additionalQuery = '(' . $db->quoteName('app') . '=' . $db->Quote($app) .
                            ' OR ' . $db->quoteName('app') . '=' . $db->Quote('photos.comment') .
                            ' OR ' . $db->quoteName('app') . '=' . $db->Quote('album.like') .
                            ' OR ' . $db->quoteName('app') . '=' . $db->Quote('profile.avatar.upload') .
                            ' OR ' . $db->quoteName('app') . '=' . $db->Quote('photo.like') . ')';
                        break;
                    case 'videos' :
                        $additionalQuery = '(' . $db->quoteName('app') . '=' . $db->Quote($app) .
                            ' OR ' . $db->quoteName('app') . '=' . $db->Quote('videos.linking') .
                            ' OR ' . $db->quoteName('app') . '=' . $db->Quote('videos.comment') .
                            ' OR ' . $db->quoteName('app') . '=' . $db->Quote('videos.like') . ')';
                        break;
                    case 'albums':
                        $additionalQuery = $db->quoteName('app') . ' like' . $db->Quote('%photos%');
                        break;
                    default :
                        // this is the default state
                        $additionalQuery = $db->quoteName('app') . '=' . $db->Quote($app);
                }

                $query = 'DELETE FROM ' . $db->quoteName('#__community_activities') . ' '
                    . 'WHERE ' .$additionalQuery . ' '
                    . 'AND ' . $db->quoteName('cid') . '=' . $db->Quote($uniqueId);

                $db->setQuery($query);
                try {
                    $status = $db->execute();
                } catch (Exception $e) {
                    JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
                }

                return $status;
            }

	/**
	 * Remove an activity by ID
	 * @param type $activityId
	 * @return boolean
	 */
	public function hideActivityById($activityId)
	{
		$table = JTable::getInstance('Activity', 'CTable');
		$table->load($activityId);
		$archived = 1;

		$message = JText::_('COM_COMMUNITY_WALL_REMOVED');

		if ($table->archived == 1) {
			$archived = 0;

			$message = JText::_('COM_COMMUNITY_WALL_RESTORED');
		}

		$db = $this->getDBO();

		$query = 'UPDATE ' . $db->quoteName('#__community_activities') . ' SET  ' . $db->quoteName('archived') . ' = ' . $db->Quote($archived)
			. ' WHERE ' . $db->quoteName('id') . '=' . $db->quote($activityId) . ' ';

		$db->setQuery($query);
		try {
			$status = $db->execute();
		} catch (Exception $e) {
			JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
		}

		return $message;
	}

	/**
	 * This is used to remove all the activity related to avatar
	 * @param $app
	 * @param $id
	 * @return mixed
	 * @throws Exception
	 */
	public function removeAvatarActivity($app, $id){

		$db = $this->getDBO();

		switch($app){
			case 'profile.avatar.upload' :
				$query = 'AND '.$db->quoteName('actor').' = '.$db->quote($id);
				break;
			case 'events.avatar.upload' :
				$query = 'AND '.$db->quoteName('eventid').' = '.$db->quote($id);
				break;
			case 'groups.avatar.upload' :
				$query = 'AND '.$db->quoteName('groupid').' = '.$db->quote($id);
				break;
		}

		$query = 'DELETE FROM ' . $db->quoteName('#__community_activities') . ' '
			. 'WHERE ' . $db->quoteName('app') . '=' . $db->Quote($app) . $query;

		$db->setQuery($query);
		try {
			$status = $db->execute();
		} catch (Exception $e) {
			JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
		}

		return $status;
	}


	/**
	 * @param $access
	 * @param $previousAccess
	 * @param $actorId
	 * @param string $app
	 * @param string $cid
	 * @return $this
	 * @throws Exception
	 */
	public function updatePermission($access, $previousAccess, $actorId, $app = '', $cid = '')
	{
		$db = $this->getDBO();

		$query = 'UPDATE ' . $db->quoteName('#__community_activities') . ' SET ' . $db->quoteName('access') . ' = ' . $db->Quote($access);
		$query .= ' WHERE ' . $db->quoteName('actor') . ' = ' . $db->Quote($actorId);

		if ($previousAccess != null && $previousAccess > $access) {
			$query .= ' AND ' . $db->quoteName('access') . ' <' . $db->Quote($access);
		}

		if (!empty($app)) {
			$query .= ' AND ' . $db->quoteName('app') . ' = ' . $db->Quote($app);
		}

		if (!empty($cid)) {
			$query .= ' AND ' . $db->quoteName('cid') . ' = ' . $db->Quote($cid);
		}

		$db->setQuery($query);
		try {
			$db->execute();
		} catch (Exception $e) {
			JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
		}

		return $this;
	}

	public function updatePermissionByCid($access, $previousAccess = null, $cid, $app)
	{
		// if (is_array($cid)) {}

		$db = $this->getDBO();

		$query = 'UPDATE ' . $db->quoteName('#__community_activities') . ' SET ' . $db->quoteName('access') . ' = ' . $db->Quote($access);
		$query .= ' WHERE ' . $db->quoteName('cid') . ' IN (' . $db->Quote($cid) . ')';
		$query .= ' AND ' . $db->quoteName('app') . ' = ' . $db->Quote($app);

		if ($previousAccess != null && $previousAccess > $access) {
			$query .= ' AND ' . $db->quoteName('access') . ' <' . $db->Quote($access);
		}

		$db->setQuery($query);
		try {
			$db->execute();
		} catch (Exception $e) {
			JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
		}

		return $this;
	}

	/**
	 * Generic activity update code
	 *
	 * @param array $condition
	 * @param array $update
	 * @return CommunityModelActivities
	 */
	public function update($condition, $update)
	{
		$db = $this->getDBO();

		$where = array();
		foreach ($condition as $key => $val) {
			$where[] = $db->quoteName($key) . '=' . $db->Quote($val);
		}
		$where = implode(' AND ', $where);

		$set = array();
		foreach ($update as $key => $val) {
			$set[] = ' ' . $db->quoteName($key) . '=' . $db->Quote($val);
		}
		$set = implode(', ', $set);

		$query = 'UPDATE ' . $db->quoteName('#__community_activities') . ' SET ' . $set . ' WHERE ' . $where;

		$db->setQuery($query);
		try {
			$db->execute();
		} catch (Exception $e) {
			JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
		}

		return $this;
	}
}
