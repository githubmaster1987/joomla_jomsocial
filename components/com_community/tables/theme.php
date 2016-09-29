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

class CommunityTableTheme extends JTable {

    var $id = null;
    var $key = null;
    var $value = null;

    /**
     * Constructor
     */
    public function __construct(&$db) {
        parent::__construct('#__community_theme', 'id', $db);
    }

    public function load($keys = NULL, $reset = true) {
        $db		= JFactory::getDBO();
        $query	= 'SELECT * FROM ' . $db->quoteName( '#__community_theme') . ' WHERE '.$db->quoteName('key').'=\''.$keys.'\'';

        $db->setQuery( $query );

        $result=$db->loadObjectList();

        if(sizeof($result)) {
            $this->id = $result[0]->id;
            $this->key = $result[0]->key;
            $this->value = $result[0]->value;
        }
    }
    /**
     * Binds an array into this object's property
     *
     * @access	public
     * @param	$data	mixed	An associative array or object
     * */
    public function store($updateNulls = false) {
        return parent::store();
    }

    /**
     * Return list of custom or preset moods
     *
     * @return array
     */
    public function getColors() {

        $db		= JFactory::getDBO();

        $query	= 'SELECT * FROM ' . $db->quoteName( '#__community_theme') . ' WHERE '.$db->quoteName('key').' = \'scss-color\'';

        $db->setQuery( $query );

        $result=$db->loadObjectList();

        $return = array();

        if(sizeof($result)) {
            $return = json_decode($result[0]->value,true);
        }

        return $return;
    }

    public function getByKey($key) {
        $db		= JFactory::getDBO();

        $query	= 'SELECT * FROM ' . $db->quoteName( '#__community_theme') . ' WHERE '.$db->quoteName('key')." = '$key'";

        $db->setQuery( $query );

        $result=$db->loadObjectList();

        $return = array();

        if(sizeof($result)) {
            $return = json_decode($result[0]->value,true);
        }

        return $return;
    }
}
