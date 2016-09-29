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

class CommunityModelMailqueue extends JModelLegacy
{
	/**
	 * Configuration data
	 *
	 * @var object	JPagination object
	 **/
	var $_pagination;

	/**
	 * Configuration data
	 *
	 * @var int	Total number of rows
	 **/
	var $_total;

	/**
	 * Configuration data
	 *
	 * @var int	Total number of rows
	 **/
	var $_data;

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
	 * Method to get a pagination object for the events
	 *
	 * @access public
	 * @return integer
	 */
	public function getPagination()
	{
		// Lets load the content if it doesn't already exist
		if ( empty( $this->_pagination ) )
		{
			jimport('joomla.html.pagination');
			$this->_pagination = new JPagination( $this->getTotal(), $this->getState('limitstart'), $this->getState('limit') );
		}

		return $this->_pagination;
	}

	/**
	 * Method to return the total number of rows
	 *
	 * @access public
	 * @return integer
	 */
	public function getTotal()
	{
		// Load total number of rows
		if( empty($this->_total) )
		{
			$db = JFactory::getDbo();
			$db->setQuery($this->_buildQuery(true));
			$this->_total = $db->loadResult();
		}

		return $this->_total;
	}

	/**
	 * @param bool|false $count
	 * @return string
	 * @throws Exception
	 */
	public function _buildQuery($count = false)
	{
        $mainframe	= JFactory::getApplication();
        $jinput = $mainframe->input;
		$db			= JFactory::getDBO();
		$status 	= $jinput->getInt( 'status' , 3 );
		$condition	= '';

		if( $status != 3 )
		{
			$condition .= ' AND status='. $db->Quote( $status );
		}

		$select = ($count) ? 'COUNT(*)' : '*';
		$query		= 'SELECT '.$select.' FROM '
					. $db->quoteName( '#__community_mailq' ) . ' WHERE 1'
					. $condition
					. ' ORDER BY created DESC';

		return $query;
	}

	/**
	 * Purges the sent items from the mail queue
	 **/
	public function purge()
	{
		$db		=& $this->getDBO();
		$query	= 'DELETE FROM '
				. $db->quoteName( '#__community_mailq' ) . ' '
				. 'WHERE ' . $db->quoteName( 'status' ) . ' != ' . $db->Quote( '0' );

		$db->setQuery( $query );
		$db->execute();

		return true;
	}

	/**
	 * Returns the Groups Categories list
	 *
	 * @return Array An array of group category objects
	 **/
	public function &getMailQueues()
	{
		$mainframe	= JFactory::getApplication();

		if(empty($this->_data))
		{

			$query			= $this->_buildQuery();
			$this->_data	= $this->_getList( $query , $this->getState( 'limitstart' ) , $this->getState( 'limit') );
		}
		return $this->_data;
	}

	public function getUnsendMail()
	{
		$db = $this->getDBO();

		$query = 'SELECT COUNT(*) FROM '.$db->quoteName('#__community_mailq')
				.' WHERE '.$db->quoteName('status') . '='.$db->Quote(0);

		$db->setQuery($query);

		return $db->loadResult();
	}

	public function getunsendMailList()
	{
		$db = $this->getDBO();

		$query = 'SELECT * FROM '.$db->quoteName('#__community_mailq')
				.' WHERE '.$db->quoteName('status') . '='.$db->Quote(0)
				.' LIMIT 0,5';

		$db->setQuery($query);

		return $db->loadObjectList();
	}

}
