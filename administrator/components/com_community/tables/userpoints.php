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

/**
 * JomSocial Table Model
 */
class CommunityTableUserPoints extends JTable
{
	var $id					= null;
	var $rule_name			= null;
	var $rule_description	= null;
	var $rule_plugin		= null;
	var $action_string		= null;
	var $component			= null;
	var $access				= null;
	var $points				= null;
	var $published			= null;
	var $system				= null;

	public function __construct(&$db)
	{
		parent::__construct('#__community_userpoints','id', $db);
	}

	/**
	 * Bind AJAX data into object's property
	 *
	 * @param	array	data	The data for this field
	 **/
	public function bindAjaxPost( $data )
	{
		// @todo: Need to check if all fields are valid!
		//$this->rule_name		= trim($data['rule_name']);
		//$this->rule_description	= trim($data['rule_description']);
		//$this->rule_plugin		= trim($data['rule_plugin']);
		$this->access 			= trim($data['access']);
		$this->points			= trim($data['points']);
		$this->published		= trim($data['published']);

	}

	public function isRuleExist($rule)
	{
		$db		= JFactory::getDBO();

		$query = 'SELECT count(' . $db->quoteName('id') . ' ) as '. $db->quoteName('count') . '  FROM '.$db->quoteName('#__community_userpoints');
		$query .= ' WHERE ' . $db->quoteName('action_string') . ' = '.$db->Quote($rule);
		
		$db->setQuery( $query );
		$count	= $db->loadResult();

		return ($count > 0) ? true : false;
	}

}