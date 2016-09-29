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

class CommunityModelProfiles extends JModelLegacy
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
		if ($this->_pagination == null)
		{
			$this->getFields();
		}
		return $this->_pagination;
	}

	/**
	 * Returns the Fields
	 *
	 * @return object	JParameter object
	 **/
	public function &getFields( $usePagination = false )
	{
		$mainframe	= JFactory::getApplication();

		static $fields;

		if( isset( $fields ) )
		{
			return $fields;
		}

		// Initialize variables
		$db			= JFactory::getDBO();

		// Get the limit / limitstart
		$limit		= $mainframe->getUserStateFromRequest('global.list.limit', 'limit', $mainframe->get('list_limit'), 'int');
		$limitstart	= $mainframe->getUserStateFromRequest('com_communitylimitstart', 'limitstart', 0, 'int');

		// In case limit has been changed, adjust limitstart accordingly
		$limitstart	= ($limit != 0) ? ($limitstart / $limit ) * $limit : 0;

        $query = $db->getQuery(true);
        $query->select(array('id', 'ordering'))
            ->from('#__community_fields')->order('ordering ASC');
        //get the ordering.
        $db->setQuery($query);

        //check if the ordering has any duplicates
        $result = $db->loadColumn(1); // array of ordering
        if(count($result) != count(array_unique($result))){
            //we need to rearrange the ordering to be unique
            $result = $db->loadColumn();// array or id

            //lets update the field now
            $user_record = new stdClass();
            foreach($result as $order=>$id){
                // Must be a valid primary key value.
                $user_record->id = $id;
                $user_record->ordering = $order;

                // Update their details in the users table using id as the primary key.
                $db->updateObject('#__community_fields', $user_record, 'id');
            }
        }

		// Get the total number of records for pagination
		$query	= 'SELECT COUNT(*) FROM ' . $db->quoteName( '#__community_fields' );
		$db->setQuery( $query );
		$total	= $db->loadResult();

		jimport('joomla.html.pagination');

		// Get the pagination object
		$this->_pagination	= new JPagination( $total , $limitstart , $limit );

		$query	= 'SELECT * FROM ' . $db->quoteName( '#__community_fields' ) . ' '
				. 'ORDER BY ' . $db->quoteName( 'ordering' );




		if( $usePagination )
		{
			$db->setQuery( $query , $this->_pagination->limitstart , $this->_pagination->limit );
		}
		else
		{
			$db->setQuery( $query );
		}

		$fields	= $db->loadObjectList();

		return $fields;
	}

	public function &getGroups()
	{
		static $fieldGroups;

		if( isset( $fieldGroups ) )
		{
			return $fieldGroups;
		}

		$db		= JFactory::getDBO();

		$query	= 'SELECT * '
				. 'FROM ' . $db->quoteName( '#__community_fields' )
				. 'WHERE ' . $db->quoteName( 'type' ) . '=' . $db->Quote( 'group' )
				. ' ORDER BY ' . $db->quoteName( 'ordering' );

		$db->setQuery( $query );

		$fieldGroups	= $db->loadObjectList();

		return $fieldGroups;
	}

	public function &getFieldGroup( $fieldId )
	{
		static $fieldGroup;

		if( isset( $fieldGroup ) )
		{
			return $fieldGroup;
		}

		$db		= JFactory::getDBO();

		$query	= 'SELECT * FROM ' . $db->quoteName( '#__community_fields' )
				. 'WHERE ' . $db->quoteName( 'ordering' ) . '<' . $db->Quote( $fieldId ) . ' '
				. 'AND ' . $db->quoteName( 'type' ) . '=' . $db->Quote( 'group' )
				. 'ORDER BY ordering DESC '
				. 'LIMIT 1';

		$db->setQuery( $query );

		$fieldGroup	= $db->loadObject();

		return $fieldGroup;
	}



	public function getGroupFields( $groupOrderingId )
	{
		$fieldArray	= array();
		$db			= JFactory::getDBO();

		$query	= 'SELECT * FROM ' . $db->quoteName( '#__community_fields' )
				. ' WHERE ' . $db->quoteName( 'ordering' ) . '>' . $db->Quote( $groupOrderingId )
				. ' ORDER BY ' . $db->quoteName( 'ordering' ) . ' ASC ';

		$db->setQuery( $query );

		$fieldGroup	= $db->loadObjectList();

		if(count($fieldGroup) > 0)
		{
			foreach($fieldGroup as $field)
			{
				if($field->type == 'group')
					break;
				else
				 	$fieldArray[]	= $field;
			}
		}

		return $fieldArray;
	}


	public function getFieldType()
	{
		static $types = false;

		if( !$types )
		{
			$path	= JPATH_ROOT . '/components/com_community/libraries/fields/customfields.xml';

			$parser	= new SimpleXMLElement( $path , NULL , true );

			$fields = $parser->fields;

            //to accomodate xipt component
            $xipt = CSystemHelper::isComponentExists('com_xipt') ? JComponentHelper::getComponent('com_xipt', true)->enabled : false;
            $filterField = array('Profiletypes','Templates');

			foreach($fields->children() as $field)
			{
                //if xipt component is not enabled, filter the field
                if(!$xipt && in_array($field->name,$filterField)){
                    continue;
                }

				$data[(string) $field->type] = $field->name;
			}

			$types = $data;
		}
		return $types;
	}


	/**
	 * Deprecated, misleading function name
	 */
	public function getProfileTypes()
	{
		return $this->getFieldType();
	}
}