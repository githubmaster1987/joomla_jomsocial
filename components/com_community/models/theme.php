<?php

/**
 * @copyright (C) 2013 iJoomla, Inc. - All rights reserved.
 * @license GNU General Public License, version 2 (http://www.gnu.org/licenses/gpl-2.0.html)
 * @author iJoomla.com <webmaster@ijoomla.com>
 * @url https://www.jomsocial.com/license-agreement
 * The PHP code portions are distributed under the GPL license. If not otherwise stated, all images, manuals, cascading style sheets, and included JavaScript *are NOT GPL, and are released under the IJOOMLA Proprietary Use License v1.0
 * More info at https://www.jomsocial.com/license-agreement
 */
// no direct access
defined('_JEXEC') or die('Restricted access');

class CommunityModelTheme extends JCCModel {

    private $settings = array();
    private $scss = array();

    /**
     * Load up all published moods on startup
     */
    public function __construct() {

        $db = JFactory::getDBO();
        $sql = 'SELECT * FROM ' . $db->quoteName('#__community_theme') . ' WHERE ' . $db->quoteName('key') . '=\'settings\' LIMIT 1';
        $db->setQuery($sql);

        $result = $db->loadObjectList();

        if(isset($result[0]) && strlen($result[0]->value)) {
            $this->settings = json_decode($result[0]->value, true);
        }

        if(!isset($this->settings['general']['enable-frontpage-image'])) $this->settings['general']['enable-frontpage-image'] = 1;
        if(!isset($this->settings['general']['enable-frontpage-paragraph'])) $this->settings['general']['enable-frontpage-paragraph'] = 1;
        if(!isset($this->settings['general']['enable-frontpage-login'])) $this->settings['general']['enable-frontpage-login'] = 1;
    }

    public function getSettings() {
        return $this->settings;
    }

    public function getSetting($group, $key, $default = null) {

        if(isset($this->settings[$group]) && is_array($this->settings[$group]) &&
            isset($this->settings[$group][$key]) && strlen($this->settings[$group][$key]))
            return $this->settings[$group][$key];

        if($default) return $default;

        return null;
    }

    public function formatField($field)
    {
        return CProfile::getFieldData((array) $field);
    }

    public function getFieldsById()
    {
        $profile = CFactory::getModel('Profiles');

        $fields  = $profile->getFields();

        foreach($fields as $field) {
            $fieldsById[$field->id] = $field;
        }

        return $fieldsById;
    }
}
