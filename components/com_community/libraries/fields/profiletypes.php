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

    require_once(COMMUNITY_COM_PATH . '/libraries/fields/profilefield.php');
    if(JFile::exists(JPATH_ROOT . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_xipt' . DIRECTORY_SEPARATOR . 'includes.php')){
        require_once(JPATH_ROOT . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_xipt' . DIRECTORY_SEPARATOR . 'includes.php');
    }

    class CFieldsProfiletypes extends CProfileField
    {
        var $_field;

        function __construct()
        {
            $this->_field = XiptFieldsProfiletypesBase::getInstance();
        }

        /* if data not available,
         * then find user's profiletype and return
         * else present defaultProfiletype to community
         *
         * So there will be always a valid value returned
         * */
        function formatData($value = 0)
        {
            return $this->_field->formatData($value);
        }

        /*
         * Convert stored profileType ID to profileTypeName
         *
         * */
        function getFieldData($field)
        {
            $value = isset($field['value']) ? $field['value'] : 0;
            return $this->_field->getFieldData($value);
        }

        /*
         * Generate input HTML for field
         */
        function getFieldHTML($field, $required)
        {
            return $this->_field->getFieldHTML($field, $required);
        }

        // Just an validation
        function isValid($value, $required)
        {
            return $this->_field->isValid($value, $required);
        }

    }
