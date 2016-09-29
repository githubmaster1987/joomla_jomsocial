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

class CommunityTableMoods extends JTable {

    var $id = null;
    var $title = null;
    var $description = null;
    var $image = null;
    var $custom = null;
    var $published = null;
    var $ordering = 0;

    /**
     * Constructor
     */
    public function __construct(&$db) {
        parent::__construct('#__community_moods', 'id', $db);
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
        $this->description  = $safeHtmlFilter->clean($this->description);
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

    //@todo images &  scss
    public function setImage($path, $type = 'thumb') {
        CError::assert($path, '', '!empty', __FILE__, __LINE__);

        $db = $this->getDBO();

        // Fix the back quotes
        $path = CString::str_ireplace('\\', '/', $path);
        $type = JString::strtolower($type);

        // Test if the record exists.
        $oldFile = $this->$type;

        if ($oldFile) {
            // File exists, try to remove old files first.
            $oldFile = CString::str_ireplace('/', '/', $oldFile);

            // If old file is default_thumb or default, we should not remove it.
            //
			// Need proper way to test it
            if (!JString::stristr($oldFile, 'group.jpg') && !JString::stristr($oldFile, 'group_thumb.jpg') && !JString::stristr($oldFile, 'default.jpg') && !JString::stristr($oldFile, 'default_thumb.jpg')) {
                jimport('joomla.filesystem.file');
                JFile::delete($oldFile);
            }
        }
        $this->$type = $path;
        $this->store();
    }

    /**
     * Return list of custom or preset moods
     *
     * @return array
     */
    public function getMoods() {
        $db		= JFactory::getDBO();

        $query	= 'SELECT * FROM ' . $db->quoteName( '#__community_moods') . ' where custom=1 ORDER BY '.$db->quoteName('ordering').' ASC';

        $db->setQuery( $query );

        $result=$db->loadObjectList();

        return $result;
    }
}
