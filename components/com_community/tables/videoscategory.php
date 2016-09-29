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

class CTableVideosCategory extends JTable
{
	var $id 			= null;
	var $parent			= null;
	var $name 			= null;
  	var $description	= null;
  	var $published		= null;

	/**
	 * Constructor
	 */
	public function __construct( &$db )
	{
		parent::__construct( '#__community_videos_category', 'id', $db );
	}

	public function delete( $id = null )
	{
		$db	= JFactory::getDBO();

		// Check if any groups are assigned into this category
		$query		= 'SELECT COUNT(*) FROM ' . $db->quoteName('#__community_videos') . ' '
					. 'WHERE ' . $db->quoteName('category_id') . '=' . $db->Quote($id);
		$db->setQuery( $query );
		$count		= $db->loadResult();

		if($count <= 0)
		{
			// Only delete if no groups are assigned to this category.
			parent::delete( $id );
			return true;
		}

		return false;
	}
}