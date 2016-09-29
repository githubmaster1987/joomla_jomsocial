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

class CTableMultiProfileFields extends JTable
{
	var $id			= null;
	var $parent		= null;
	var $field_id	= null;

	public function __construct( &$db )
	{
		parent::__construct( '#__community_profiles_fields', 'id', $db );
	}

    public function cleanField($parentId){
        $db = $this->getDBO();
        $sql = 'DELETE FROM '.$db->quotename('#__community_profiles_fields')
                .' WHERE '.$db->quotename('parent').' = '.$db->quote($parentId);
        $db->setQuery( $sql );
        $db->execute();
    }
}