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
jimport('joomla.utilities.date');
require_once (COMMUNITY_COM_PATH . '/libraries/fields/profilefield.php');

class CFieldsDate extends CProfileField {

    /**
     * The max year old can choose ( the low year )
     * @var array
     */
    protected $_maxRange = null;

    /**
     * The min year old can choose ( the max year )
     * @var int
     */
    protected $_minRange = null;

    /**
     * Construction
     * @param type $fieldId
     */
    public function __construct($fieldId = null) {
        parent::__construct($fieldId);
    }

    /**
     * Method to format the specified value for text type
     * @param type $field
     * @return type
     */
    public function getFieldData($field) {
        $value = $field['value'];
        if (empty($value))
            return $value;

        if (!class_exists('CFactory')) {
            require_once( JPATH_ROOT . '/components/com_community/libraries/core.php' );
        }
        require_once( JPATH_ROOT . '/components/com_community/models/profile.php' );
        $params = new CParameter($field['params']);
        $format = $params->get('date_format');
        $model = CFactory::getModel('profile');
        $myDate = $model->formatDate($value, $format);

        return $myDate;
    }

    /**
     *
     * @param type $field
     * @param type $required
     * @return string
     */
    public function getFieldHTML($field, $required) {
        /**
         * @todo For now year can't larger than 2038 we can provide another solution for this later
         * @link http://www.php.net/mktime
         */

        $config = CFactory::getConfig();

        /* Do parse max & min range into valid data */
        $minRange = $this->params->get('minrange', -10);
        $maxRange = $this->params->get('maxrange', 100);

        $minRange = $minRange === 'today' ? 0 : $minRange;
        $maxRange = $maxRange === 'today' ? 0 : $maxRange;

        $this->_maxRange = self::getRange($maxRange);
        $this->_minRange = self::getRange($minRange); /* maximum year can choose is +10 year from current year */

        $params = new CParameter($field->params);

        $html = '';

        $day = '';
        $month = 0;
        $year = '';

        $datepickerID = 'datePickerField' . $field->id;
        $showdate = '';

        $readonly = $params->get('readonly') && !COwnerHelper::isCommunityAdmin() ? ' disabled=""' : ' readonly=""';
        $style = $this->getStyle() ? $this->getStyle() : '';
        $style = ' style="width:auto; cursor:pointer; ' . $style . '" ';
        if (!empty($field->value)) {
            if (!is_array($field->value)) {
                $myDateArr = explode(' ', $field->value);
            } else {
                $myDateArr[0] = $field->value[2] . '-' . $field->value[1] . '-' . $field->value[0];
            }

            if (is_array($myDateArr) && count($myDateArr) > 0) {
                $myDate = explode('-', $myDateArr[0]);

                if (strlen($myDate[0]) > 2) {
                    $year = !empty($myDate[0]) ? $myDate[0] : '';
                    $day = !empty($myDate[2]) ? $myDate[2] : '';
                } else {
                    $day = !empty($myDate[0]) ? $myDate[0] : '';
                    $year = !empty($myDate[2]) ? $myDate[2] : '';
                }

                $month = !empty($myDate[1]) ? $myDate[1] : '';
            }
        }

        if (empty($day) || empty($month) || empty($year)) {
            $value = '';
            $showdate = $value;
        } else {
            $value = $this->_fillZero($year, 4) . '-' . $this->_fillZero($month, 2) . '-' . $this->_fillZero($day, 2);
            $showdate = $value;
            $date = new JDate($showdate);
            $showdate = $date->format($this->params->get('date_format', "Y-m-d"));
            $initData = "joms.jQuery(\"#" . $datepickerID . "\" ).datepicker (\"option\"," . $showdate . ");";
        }

        $class = ($field->required == 1) ? ' data-required="true"' : '';
        $class .=!empty($field->tips) ? ' jomNameTips tipRight' : '';
        $title = CStringHelper::escape( JText::_($field->tips) );
        $html .= '<div style="position:relative">';

        $html .= '<input type="hidden" id="dpField' . $field->id . 'day" name="field' . $field->id . '[]" value="' . $day . '"  />';
        $html .= '<input type="hidden" id="dpField' . $field->id . 'month" name="field' . $field->id . '[]" value="' . $month . '" />';
        $html .= '<input type="hidden" id="dpField' . $field->id . 'year" name="field' . $field->id . '[]" value="' . $year . '" />';
        $html .= '<span id="errfield' . $field->id . 'msg" style="display:none;">&nbsp;</span>';

        $html .= '<input type="text" id="' . $datepickerID . '" class="joms-input joms-input--datepicker"' . ($field->required == 1 ? ' required="required"' : '')
               . ' data-value="' . $value . '" title="' . $title . '" style="cursor:text">';

        //@since 4.2 When birthday field is set to show as date,
        // then on the edit profile/register page there should be an checkbox option under the field to hide a year
        if($this->params->get('display') == 'date' && $params->get('display') == 'date'){
            if(isset($field->fieldparams)) {
                $fieldParams = new CParameter($field->fieldparams);
                $isChecked = ($fieldParams->get('hideyear')) ? 'checked' : '';
            }else{
                $isChecked = '';
            }
            $html .= JText::_('COM_COMMUNITY_HIDE_YEAR') . ' <input type="checkbox" value="hide" name="field'.$field->id.'[]" '.$isChecked.'/>';
        }

        $html .= $this->getDatepickerOptionsHTML();

        $format = $this->params->get('date_format');
        if ( empty($format) ) {
            $format = 'Y-m-d';
        }

        // Change to pickadate format.
        $format = str_replace("y", "yy", $format);
        $format = str_replace("Y", "yyyy", $format);
        $format = str_replace("m", "mm", $format);
        $format = str_replace("F", "mmmm", $format);
        $format = str_replace("M", "mmm", $format);
        $format = str_replace("n", "m", $format);
        $format = str_replace("d", "dd", $format);
        $format = str_replace("D", "ddd", $format);
        $format = str_replace("j", "d", $format);
        $format = str_replace("l", "dddd", $format);

        $html .= PHP_EOL;
        $html .= '<script>' . PHP_EOL;
        $html .= 'function joms_dp' . $datepickerID . '_init( $ ) {' . PHP_EOL;
        $html .= "\t$('#" . $datepickerID . "').pickadate( $.extend({}, joms_datepicker_opts, {" . PHP_EOL;
        $html .= "\t\tformat: '" . $format . "'," . PHP_EOL;
        $html .= "\t\tformatSubmit: 'yyyy-mm-dd'," . PHP_EOL;
        $html .= "\t\tselectYears: 200," . PHP_EOL;
        $html .= "\t\tselectMonths: true," . PHP_EOL;

        // Flip date in case of invalid range.
        if ( isset( $this->_minRange['value'] ) && isset( $this->_maxRange['value'] ) ) {
            if ( ((int) $this->_minRange['year'] ) > ((int) $this->_maxRange['year'] ) ) {
                $temp = $this->_minRange;
                $this->_minRange = $this->_maxRange;
                $this->_maxRange = $temp;
            }
        }

        // Set minimum range.
        if ( isset( $this->_minRange['value'] ) ) {
            $value = explode('-', $this->_minRange['value']);
            $html .= "\t\tmin: [" . ((int) $value[0] ) . "," . ((int) $value[1] - 1 ) . "," . ((int) $value[2] ) . "]," . PHP_EOL;
        }

        // Set maximum range.
        if ( isset( $this->_maxRange['value'] ) ) {
            $value = explode('-', $this->_maxRange['value']);
            $html .= "\t\tmax: [" . ((int) $value[0] ) . "," . ((int) $value[1] - 1 ) . "," . ((int) $value[2] ) . "]," . PHP_EOL;
        }

        $html .= "\t\tonSet: function( o ) {" . PHP_EOL;
        $html .= "\t\t\tvar date = new Date(o.select);" . PHP_EOL;
        $html .= "\t\t\t$('#dpField" . $field->id . "day').val( date.getDate() );" . PHP_EOL;
        $html .= "\t\t\t$('#dpField" . $field->id . "month').val( date.getMonth() + 1 );" . PHP_EOL;
        $html .= "\t\t\t$('#dpField" . $field->id . "year').val( date.getFullYear() );" . PHP_EOL;
        $html .= "\t\t\t$('#" . $datepickerID . "').blur();" . PHP_EOL;
        $html .= "\t\t}" . PHP_EOL;
        $html .= "\t}) );" . PHP_EOL;
        $html .= '}' . PHP_EOL . PHP_EOL;
        $html .= 'var joms_dp' . $datepickerID . '_timer = setInterval(function() {' . PHP_EOL;
        $html .= "\tif ( window.jQuery ) {" . PHP_EOL;
        $html .= "\t\tclearInterval(joms_dp" . $datepickerID . "_timer);" . PHP_EOL;
        $html .= "\t\tjoms_dp" . $datepickerID . "_init( jQuery );" . PHP_EOL;
        $html .= "\t}" . PHP_EOL;
        $html .= '}, 500 );' . PHP_EOL;
        $html .= '</script>' . PHP_EOL;

        $html .= '</div>';

        return $html;
    }

    /**
     *
     * @return string
     */
    public function getDatepickerOptionsHTML() {
        if ( defined('DATEPICKER_OPTIONS') ) {
            return '';
        }

        define('DATEPICKER_OPTIONS', true);

        $html   = array('');
        $config = CFactory::getConfig();

        $html[] = '<script>';
        $html[] = '(function( global ) {';
        $html[] = '';

        $html[] = 'var opts, i;';
        $html[] = '';

        $format = $this->params->get('date_format');
        $format = str_replace("y", "yy", $format);
        $format = str_replace("Y", "yyyy", $format);
        $format = str_replace("m", "mm", $format);
        $format = str_replace("F", "mmmm", $format);
        $format = str_replace("M", "mmm", $format);
        $format = str_replace("n", "m", $format);
        $format = str_replace("d", "dd", $format);
        $format = str_replace("D", "ddd", $format);
        $format = str_replace("j", "d", $format);
        $format = str_replace("l", "dddd", $format);

        $html[] = 'opts = {';
        $html[] = "\tformat   : 'yyyy-mm-dd',";
        $html[] = "\tfirstDay : " . ($config->get('event_calendar_firstday') === 'Monday' ? 1 : 0) . ',';
        $html[] = "\ttoday    : '" . JText::_('COM_COMMUNITY_DATEPICKER_CURRENT', true) . "',";
        $html[] = "\t'clear'  : '" . JText::_('COM_COMMUNITY_DATEPICKER_CLEAR', true) . "'";
        $html[] = '};';
        $html[] = '';

        $html[] = 'opts.weekdaysFull = [';
        $html[] = "\t'" . JText::_("COM_COMMUNITY_DATEPICKER_DAY_1", true) . "',";
        $html[] = "\t'" . JText::_("COM_COMMUNITY_DATEPICKER_DAY_2", true) . "',";
        $html[] = "\t'" . JText::_("COM_COMMUNITY_DATEPICKER_DAY_3", true) . "',";
        $html[] = "\t'" . JText::_("COM_COMMUNITY_DATEPICKER_DAY_4", true) . "',";
        $html[] = "\t'" . JText::_("COM_COMMUNITY_DATEPICKER_DAY_5", true) . "',";
        $html[] = "\t'" . JText::_("COM_COMMUNITY_DATEPICKER_DAY_6", true) . "',";
        $html[] = "\t'" . JText::_("COM_COMMUNITY_DATEPICKER_DAY_7", true) . "'";
        $html[] = '];';
        $html[] = '';

        $html[] = 'opts.monthsFull = [';
        $html[] = "\t'" . JText::_("COM_COMMUNITY_DATEPICKER_MONTH_1", true) . "',";
        $html[] = "\t'" . JText::_("COM_COMMUNITY_DATEPICKER_MONTH_2", true) . "',";
        $html[] = "\t'" . JText::_("COM_COMMUNITY_DATEPICKER_MONTH_3", true) . "',";
        $html[] = "\t'" . JText::_("COM_COMMUNITY_DATEPICKER_MONTH_4", true) . "',";
        $html[] = "\t'" . JText::_("COM_COMMUNITY_DATEPICKER_MONTH_5", true) . "',";
        $html[] = "\t'" . JText::_("COM_COMMUNITY_DATEPICKER_MONTH_6", true) . "',";
        $html[] = "\t'" . JText::_("COM_COMMUNITY_DATEPICKER_MONTH_7", true) . "',";
        $html[] = "\t'" . JText::_("COM_COMMUNITY_DATEPICKER_MONTH_8", true) . "',";
        $html[] = "\t'" . JText::_("COM_COMMUNITY_DATEPICKER_MONTH_9", true) . "',";
        $html[] = "\t'" . JText::_("COM_COMMUNITY_DATEPICKER_MONTH_10", true) . "',";
        $html[] = "\t'" . JText::_("COM_COMMUNITY_DATEPICKER_MONTH_11", true) . "',";
        $html[] = "\t'" . JText::_("COM_COMMUNITY_DATEPICKER_MONTH_12", true) . "'";
        $html[] = '];';
        $html[] = '';

        $html[] = 'opts.weekdaysShort = [];';
        $html[] = 'for ( i = 0; i < opts.weekdaysFull.length; i++ )';
        $html[] = "\topts.weekdaysShort[i] = opts.weekdaysFull[i].substr( 0, 3 );";
        $html[] = '';

        $html[] = 'opts.monthsShort = [];';
        $html[] = 'for ( i = 0; i < opts.monthsFull.length; i++ )';
        $html[] = "\topts.monthsShort[i] = opts.monthsFull[i].substr( 0, 3 );";
        $html[] = '';

        $html[] = 'global.joms_datepicker_opts = opts;';
        $html[] = '';

        $html[] = '})( window );';
        $html[] = '</script>';
        $html[] = '';

        return implode(PHP_EOL, $html);
    }

    public function isValid($value, $required) {
        if (($required && empty($value)) || !isset($this->fieldId)) {
            return false;
        }

        $db = JFactory::getDBO();
        $query = 'SELECT * FROM ' . $db->quoteName('#__community_fields')
            . ' WHERE ' . $db->quoteName('id') . '=' . $db->quote($this->fieldId);
        $db->setQuery($query);
        $field = $db->loadAssoc();

        $params = new CParameter($field['params']);
        $max_range = $params->get('maxrange');
        $min_range = $params->get('minrange');
        $value = JDate::getInstance(strtotime($value))->toUnix();
        $max_ok = true;
        $min_ok = true;

        //$ret = true;

        if ($max_range) {
            $max_range = JDate::getInstance(strtotime($max_range))->toUnix();
            $max_ok = ($value < $max_range);
        }
        if ($min_range) {
            $min_range = JDate::getInstance(strtotime($min_range))->toUnix();
            $min_ok = ($value > $min_range);
        }

        return ($max_ok && $min_ok) ? true : false;
        //return $ret;
    }

    public function formatdata($value) {
        $finalvalue = '';

        if (is_array($value)) {
            if (empty($value[0]) || empty($value[1]) || empty($value[2])) {
                $finalvalue = '';
            } else {
                $day = intval($value[0]);
                $month = intval($value[1]);
                $year = intval($value[2]);

                $day = !empty($day) ? $day : 1;
                $month = !empty($month) ? $month : 1;
                $year = !empty($year) ? $year : 1970;

                if (!checkdate($month, $day, $year)) {
                    return $finalvalue;
                }

                //value 3 is either hide or there is nothing. if set to hide.
                $finalvalue = $year . '-' . $month . '-' . $day . ' 23:59:59';
            }
        }

        return $finalvalue;
    }

    /**
     * @since 4.2 to format the value of date, with the fourth slot of time with hide field input or not
     * @param $value
     * @param $convertToParams will allows the return value as params
     */
    public static function getHideYearParams($value, $convertToParams = true){

        $hideYear = false;
        if(is_array($value) && isset($value[3]) && $value[3]=='hide'){
            $hideYear = true;
        }

        if($convertToParams){
            $params = new CParameter();
            $params->set('hideyear',$hideYear);
            return $params->toString();
        }

        return $hideYear;
    }

    public function getType() {
        return 'date';
    }

    /**
     * Fill string with zeros until touch limit
     * @param any $val
     * @param int $limit
     * @return string
     */
    private function _fillZero($val, $limit) {
        /* Convert to string */
        $val = (string) $val;
        /* While strlen untouch limit */
        while (strlen($val) < $limit) {
            $val = '0' . $val;
        }
        return $val;
    }

    /**
     *
     * @param type $value
     * @return string
     */
    public static function getRange($value) {
        $range = array();
        /* We did enter age */
        if (is_numeric($value) || $value == '') {
            /* Convert into YYYY-MM-DD */
            $value = date("Y") - (int) $value . '-' . date('m') . '-' . date('d');
        }

        /* Extract YYYY-MM-DD */
        $parts = explode('-', $value);
        /* Make sure it's valid */
        if (is_array($parts) && count($parts) == 3 && is_int((int) $parts[1])) {
            /* Convert into timestamp */
            $now = time();
            $unixTimestamp = mktime(0, 0, 0, $parts[1], $parts[2], $parts[0]);
            /* Calc different time from now */
            $diffUnixTimestamp = $now - $unixTimestamp;
            /* Store data */
            $range['value'] = $value;
            $range['unix'] = $unixTimestamp;
            $range['now'] = $now;
            $range['year'] = $parts[0];
            $range['date'] = round($diffUnixTimestamp / 60 / 60 / 24, 0);

            if ($range['date'] > 0) /* past */
                $range['date'] = '-' . $range['date'] . 'd';
            else { /* future */
                if ($range['date'] == 0) {
                    $range['date'] = '+' . ( $range['date'] ) . 'd';
                } else {
                    $range['date'] = '+' . ( $range['date'] * -1 ) . 'd';
                }
            }
            return $range;
        }
    }

}
