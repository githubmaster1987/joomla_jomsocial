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

jimport('joomla.utilities.date');

class CTimeHelper {

    /**
     *
     * @param JDate $date
     *
     */
    public static function timeLapse($date, $showFull = true) {

        $now = JDate::getInstance();
        $html = '';
        $diff = CTimeHelper::timeDifference($date->toUnix(), $now->toUnix());


        if (!empty($diff['days'])) {
            $days = $diff['days'];
            $months = ceil($days / 30);

            switch ($days) {
                case ($days == 1):

                    // @rule: Something that happened yesterday
                    $html .= JText::_('COM_COMMUNITY_LAPSED_YESTERDAY');

                    break;
                case ($days > 1 && $days <= 7 && $days < 30):

                    // @rule: Something that happened within the past 7 days
                    $html .= JText::sprintf('COM_COMMUNITY_LAPSED_DAYS', $days) . ' ';

                    break;
                case ($days > 1 && $days > 7 && $days < 30):

                    // @rule: Something that happened within the month but after a week
                    $weeks = round($days / 7);
                    $html .= JText::sprintf(CStringHelper::isPlural($weeks) ? 'COM_COMMUNITY_LAPSED_WEEK_MANY' : 'COM_COMMUNITY_LAPSED_WEEK', $weeks) . ' ';

                    break;
                case ($days >= 30 && $days < 365):

                    // @rule: Something that happened months ago
                    $months = round($days / 30);
                    $html .= JText::sprintf(CStringHelper::isPlural($months) ? 'COM_COMMUNITY_LAPSED_MONTH_MANY' : 'COM_COMMUNITY_LAPSED_MONTH', $months) . ' ';

                    break;
                case ($days > 365):

                    // @rule: Something that happened years ago
                    $years = round($days / 365);
                    $html .= JText::sprintf(CStringHelper::isPlural($years) ? 'COM_COMMUNITY_LAPSED_YEAR_MANY' : 'COM_COMMUNITY_LAPSED_YEAR', $years) . ' ';

                    break;
            }
        } else {
            // We only show he hours if it is less than 1 day
            if (!empty($diff['hours'])) {
                if (!empty($diff['minutes'])) {
                    if ( $diff['hours'] == 1 ) {
                        $html .= JText::sprintf('COM_COMMUNITY_LAPSED_HOUR', $diff['hours']) . ' ';
                    }else {
                        $html .= JText::sprintf('COM_COMMUNITY_LAPSED_HOURS', $diff['hours']) . ' ';
                    }
                } else {
                    $html .= JText::sprintf('COM_COMMUNITY_LAPSED_HOURS_AGO', $diff['hours']) . ' ';
                }
            }

            if (($showFull && !empty($diff['hours'])) || (empty($diff['hours']))) {
                if (!empty($diff['minutes'])) {
                    if ( $diff['minutes'] == 1 ) {
                        $html .= JText::sprintf('COM_COMMUNITY_LAPSED_MINUTE', $diff['minutes']) . ' ';
                    }else {
                        $html .= JText::sprintf('COM_COMMUNITY_LAPSED_MINUTES', $diff['minutes']) . ' ';
                    }
                }

            }
        }

        if (empty($html)) {
            $html .= JText::_('COM_COMMUNITY_LAPSED_LESS_THAN_A_MINUTE');
        }

        return $html;
    }

    /**
     * Function to find time different
     * @param  [type] $start [description]
     * @param  [type] $end   [description]
     * @return [type]        [description]
     */
    static public function timeDifference($start, $end) {
        jimport('joomla.utilities.date');

        if (is_string($start) && ($start != intval($start))) {
            $start = new JDate($start);
            $start = $start->toUnix();
        }

        if (is_string($end) && ($end != intval($end) )) {
            $end = new JDate($end);
            $end = $end->toUnix();
        }

        $uts = array();
        $uts['start'] = $start;
        $uts['end'] = $end;
        if ($uts['start'] !== -1 && $uts['end'] !== -1) {
            if ($uts['end'] >= $uts['start']) {
                $diff = $uts['end'] - $uts['start'];
                if ($days = intval((floor($diff / 86400))))
                    $diff = $diff % 86400;
                if ($hours = intval((floor($diff / 3600))))
                    $diff = $diff % 3600;
                if ($minutes = intval((floor($diff / 60))))
                    $diff = $diff % 60;
                $diff = intval($diff);
                return( array('days' => $days, 'hours' => $hours, 'minutes' => $minutes, 'seconds' => $diff) );
            } else {

                //trigger_error( JText::_("COM_COMMUNITY_TIME_IS_EARLIER_THAN_START_WARNING"), E_USER_WARNING );
            }
        } else {
            trigger_error(JText::_("COM_COMMUNITY_INVALID_DATETIME"), E_USER_WARNING);
        }
        return( false );
    }

    static public function timeIntervalDifference($start, $end) {
        jimport('joomla.utilities.date');


        $start = new JDate($start);
        $start = $start->toUnix();

        $end = new JDate($end);
        $end = $end->toUnix();


        if ($start !== -1 && $end !== -1) {
            return ($start - $end);
        } else {
            trigger_error(JText::_("COM_COMMUNITY_INVALID_DATETIME"), E_USER_WARNING);
        }
        return( false );
    }

    static public function formatTime($jdate) {
        jimport('joomla.utilities.date');
        return JString::strtolower($jdate->format('%I:%M %p'));
    }

    static public function getInputDate($str = '') {
        require_once( JPATH_ROOT . '/components/com_community/libraries/core.php' );

        $mainframe = JFactory::getApplication();
        $config = CFactory::getConfig();

        $timeZoneOffset = $mainframe->get('offset');
        $dstOffset = $config->get('daylightsavingoffset');

        $date = new JDate($str);
        $my = CFactory::getUser();
        $cMy = CFactory::getUser();

        if ($my->id) {
            if (!empty($my->params)) {
                $timeZoneOffset = $my->getParam('timezone', $timeZoneOffset);

                $myParams = $cMy->getParams();
                $dstOffset = $myParams->get('daylightsavingoffset', $dstOffset);
            }
        }

        $timeZoneOffset = (-1) * $timeZoneOffset;
        $dstOffset = (-1) * $dstOffset;
        $date->setTimezone($timeZoneOffset + $dstOffset);

        return $date;
    }

    static public function getDate($str = 'Now', $off = 0) {
        $config = CFactory::getConfig();
        $mainframe = JFactory::getApplication();
        $my = CFactory::getUser();
        $cMy = CFactory::getUser();

        $extraOffset = $config->get('daylightsavingoffset');

        $date = new Jdate($str);

        $systemOffset = new JDate('now', $mainframe->get('offset'));
        $systemOffset = $systemOffset->getOffsetFromGMT(true);

        if (!$my->id) {
            $date->setTimezone(new DateTimeZone(self::getTimezone($systemOffset + $extraOffset)));
        } else {
            if (!empty($my->params)) {
                $pos = JString::strpos($my->params, 'timezone');
                $offset = $systemOffset + $extraOffset;

                if ($pos === false) {
                    $offset = $systemOffset + $extraOffset;
                } else {
                    $offset = $my->getParam('timezone', -100);
                    $myParams = $cMy->getParams();
                    $myDTS = $myParams->get('daylightsavingoffset');
                    $cOffset = (!empty($myDTS)) ? $myDTS : $config->get('daylightsavingoffset');

                    if ($offset == -100)
                        $offset = $systemOffset + $extraOffset;
                    else
                        $offset = $offset + $cOffset;
                }

                $date->setTimezone(new DateTimeZone(self::getTimezone($offset)));
            } else
                $date->setTimezone(new DateTimeZone(self::getTimezone($systemOffset + $extraOffset)));
        }

        return $date;
    }

    /**
     * Return locale date
     *
     * @param	null
     * @return	date object
     * @since   2.4.2
     * */
    static public function getLocaleDate($date = 'now') {
        $mainframe = JFactory::getApplication();
        $systemOffset = $mainframe->get('offset');

        $now = new JDate($date, $systemOffset); // // Joomla 1.6

        $timezone = new DateTimeZone($systemOffset);

        $now->setTimezone($timezone);

        return $now;
    }

    /**
     * Retrieve timezones List.
     *
     * @param string offset
     * @return null|str Timezone.
     * */
    static public function getTimezone($offset) {
       $list = self::getTimezoneList();
       return (isset($list[$offset]))?$list[$offset]:null;
    }

    /**
     * Retrieve timezones List.
     *
     * @param
     * @return	array	The list of timezones available.
     * */
    static public function getTimezoneList() {
        /* Return array */
        $retArray = array();
        /* List all avariable timezone */
        $listTimeZone = DateTimeZone::listIdentifiers(DateTimeZone::ALL);

        /* Check per timezone */
        foreach($listTimeZone as $count => $timeZoneStr){
            /* Get offset from timezone string */
            $dateTimeZone = new DateTimeZone($timeZoneStr);
            $dateTime = new DateTime("now", $dateTimeZone);
            $tmpOffset = $dateTimeZone->getOffset($dateTime);
            $resultOffset = ($tmpOffset < 0?'-':'').abs($tmpOffset)/3600;

            /* Futher concept
            $retArray[$count]['zone'] = '(' . $dateTime->format('P') . ') ' . $timeZoneStr;
            $retArray[$count]['offset'] = $resultOffset;
            */
            $retArray[$resultOffset] = $timeZoneStr;
        }

        return $retArray;
    }

    static public function getOffsetByTimezone($timezone){
        $time = new DateTime('now', new DateTimeZone($timezone));
        $timezoneOffset = ($time->getOffset() < 0?'-':'').abs($time->getOffset())/3600;

        return $timezoneOffset;
    }

    static public function getBeautifyTimezoneList(){
            static $regions = array(
                DateTimeZone::AFRICA,
                DateTimeZone::AMERICA,
                DateTimeZone::ANTARCTICA,
                DateTimeZone::ASIA,
                DateTimeZone::ATLANTIC,
                DateTimeZone::AUSTRALIA,
                DateTimeZone::EUROPE,
                DateTimeZone::INDIAN,
                DateTimeZone::PACIFIC,
            );

            $timezones = array();
            foreach( $regions as $region )
            {
                $timezones = array_merge( $timezones, DateTimeZone::listIdentifiers( $region ) );
            }

            $timezone_offsets = array();
            foreach( $timezones as $timezone )
            {
                $tz = new DateTimeZone($timezone);
                $timezone_offsets[$timezone] = $tz->getOffset(new DateTime("now", $tz));
            }

            // sort timezone by offset
            asort($timezone_offsets);

            $timezone_list = array();
            foreach( $timezone_offsets as $timezone => $offset )
            {
                $offset_prefix = $offset < 0 ? '-' : '+';
                $offset_formatted = gmdate( 'H:i', abs($offset) );

                $pretty_offset = "UTC${offset_prefix}${offset_formatted}";

                $timezone_list[$timezone] = "(${pretty_offset}) $timezone";
            }

            return $timezone_list;
    }

    static public function getFormattedTime($time, $format, $offset = 0) {
        $time = strtotime($time);

        // Manually modify the month and day strings in the format.
        if (strpos($format, '%a') !== false) {
            $format = str_replace('%a', CTimeHelper::dayToString(date('w', $time), true), $format);
        }
        if (strpos($format, '%A') !== false) {
            $format = str_replace('%A', CTimeHelper::dayToString(date('w', $time)), $format);
        }
        if (strpos($format, '%b') !== false) {
            $format = str_replace('%b', CTimeHelper::monthToString(date('n', $time), true), $format);
        }
        if (strpos($format, '%B') !== false) {
            $format = str_replace('%B', CTimeHelper::monthToString(date('n', $time)), $format);
        }

        return strftime($format, $time);
    }

    /**
     * Translates day of week number to a string.
     *
     * @param	integer	The numeric day of the week.
     * @param	boolean	Return the abreviated day string?
     * @return	string	The day of the week.
     * @since	1.5
     */
    static protected function dayToString($day, $abbr = false) {
        switch ($day) {
            case 0: return $abbr ? JText::_('SUN') : JText::_('SUNDAY');
            case 1: return $abbr ? JText::_('MON') : JText::_('MONDAY');
            case 2: return $abbr ? JText::_('TUE') : JText::_('TUESDAY');
            case 3: return $abbr ? JText::_('WED') : JText::_('WEDNESDAY');
            case 4: return $abbr ? JText::_('THU') : JText::_('THURSDAY');
            case 5: return $abbr ? JText::_('FRI') : JText::_('FRIDAY');
            case 6: return $abbr ? JText::_('SAT') : JText::_('SATURDAY');
        }
    }

    /**
     * Translates month number to a string.
     *
     * @param	integer	The numeric month of the year.
     * @param	boolean	Return the abreviated month string?
     * @return	string	The month of the year.
     * @since	1.5
     */
    static protected function monthToString($month, $abbr = false) {
        switch ($month) {
            case 1: return $abbr ? JText::_('JANUARY_SHORT') : JText::_('JANUARY');
            case 2: return $abbr ? JText::_('FEBRUARY_SHORT') : JText::_('FEBRUARY');
            case 3: return $abbr ? JText::_('MARCH_SHORT') : JText::_('MARCH');
            case 4: return $abbr ? JText::_('APRIL_SHORT') : JText::_('APRIL');
            case 5: return $abbr ? JText::_('MAY_SHORT') : JText::_('MAY');
            case 6: return $abbr ? JText::_('JUNE_SHORT') : JText::_('JUNE');
            case 7: return $abbr ? JText::_('JULY_SHORT') : JText::_('JULY');
            case 8: return $abbr ? JText::_('AUGUST_SHORT') : JText::_('AUGUST');
            case 9: return $abbr ? JText::_('SEPTEMBER_SHORT') : JText::_('SEPTEMBER');
            case 10: return $abbr ? JText::_('OCTOBER_SHORT') : JText::_('OCTOBER');
            case 11: return $abbr ? JText::_('NOVEMBER_SHORT') : JText::_('NOVEMBER');
            case 12: return $abbr ? JText::_('DECEMBER_SHORT') : JText::_('DECEMBER');
        }
    }

    /*
     * Get the exact time from the UTC00:00 time & the offset/timezone given
     * @param   $datetime	datetime is UTC00:00
     * @param   $offset	offset/timezone
     *
     */

    static public function getFormattedUTC($datetime, $offset) {
        $date = new DateTime($datetime);

        $splitTime = explode(".", $offset);
        $begin = new DateTime($datetime);

        // Modify the hour
        $begin->modify($splitTime[0] . ' hour');

        // Modify the minutes
        if (isset($splitTime[1])) {
            // The offset is actually a in 0.x hours. Convert to minute
            $splitTime[1] = $splitTime[1] * 6; // = percentage x 60 minues x 0.1
            $isMinus = ($splitTime[0][0] == '-') ? '-' : '+';
            $begin->modify($isMinus . $splitTime[1] . ' minute');
        }

        return $begin->format('Y-m-d H:i:s');
    }

    static public function convertSQLtimetoChunk($datetime){
        $str = strtotime($datetime);
        $date = array();
        $date['year'] =  date('Y', $str);
        $date['month'] =  date('m', $str);
        $date['day'] =  date('d', $str);
        $date['hour'] =  date('H', $str);
        $date['minute'] =  date('i', $str);
        $date['second'] =  date('s', $str);

        return $date;
    }

}
