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

class CommunityTableBadges extends JTable {

    var $id = null;
    var $title = null;
    var $image = null;
    var $points = null;
    var $published = null;

    /**
     * Constructor
     */
    public function __construct(&$db) {
        parent::__construct('#__community_badges', 'id', $db);
    }

    /**
     * Pre-store sanitization & check
     *
     * @access public
     * @return bool
     */
    public function check() {
        // Santinize data
        $safeHtmlFilter     = CFactory::getInputFilter();
        $this->title        = $safeHtmlFilter->clean($this->title);
        $this->points       = (int) $safeHtmlFilter->clean($this->points);

        return true;
    }

    /**
     * Binds an array into this object's property
     *
     * @access	public
     * @param	$data	mixed	An associative array or object
     * */
    public function store($updateNulls = false) {
        if (!$this->check()) {
            return false;
        }

        return parent::store();
    }

    /**
     * Params
     *
     * @access public
     * @return array
     */
    public function getParams() {
        return array();
    }

    /**
     * Return list of custom or preset badges
     *
     * @access public
     * @return array
     */
    public function getBadges() {
        $db		= JFactory::getDBO();

        $query	= 'SELECT * FROM ' . $db->quoteName( '#__community_badges') .
                  ' ORDER BY '.$db->quoteName('points').' ASC';

        $db->setQuery( $query );

        $result=$db->loadObjectList();

        return $result;
    }

    /**
     * Get a badge with a specific amount of points
     *
     * @access public
     * @return array
     */
    public function validateBadgePoints($badge) {

        $points = (int) $badge->points;
        $id = (int) $badge->id;

        $db		= JFactory::getDBO();

        $query	= 'SELECT * FROM ' . $db->quoteName( '#__community_badges') . ' WHERE '.$db->quoteName('points').' = '.$badge->points.' AND '.$db->quoteName('id').' != '.$id;

        $db->setQuery( $query );

        $result=$db->loadObjectList();

        return $result;
    }
}
