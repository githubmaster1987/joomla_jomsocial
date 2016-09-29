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

    class CFieldsTemplates extends CProfileField
    {
        var $_field;

        function __construct()
        {
            $this->_field = XiptFieldsTemplatesBase::getInstance();
        }

        //TODO : add FormatData and Validate

        function getFieldData($field)
        {
            return $this->_field->getFieldData($field['value']);
        }

        function getFieldHTML($field, $required)
        {
            return $this->_field->getFieldHTML($field, $required);
        }
    }
