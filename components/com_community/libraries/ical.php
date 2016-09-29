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

if (!class_exists('CICal')) {

    class CICal {

        /**
         * @access private
         * @var string
         */
        private $_content = '';

        /**
         * @access private
         * @var string
         */
        private $_items = array();

        /**
         * Object construct.
         *
         * @param	string	$content	The raw contents of the calendar data.
         * */
        public function __construct($content) {
            $this->_content = $content;
        }

        /**
         * Initializes and processes the raw contents provided.
         *
         * @return	Boolean		True on success and false otherwise.
         * */
        public function init() {
            preg_match_all('/BEGIN:VEVENT(.*)END:VEVENT\s/isU', $this->_content, $matches);

            if (!empty($matches[1])) {
                foreach ($matches[0] as $raw) {
                    $this->_items[] = new CIcalItem($raw);
                }
                return true;
            }
            return false;
        }

        /**
         * Retrieves all the children items in the given calendar
         *
         * @return	Array	An array of CICalItem objects.
         * */
        public function getItems() {
            // For now, we will just return whatever that is needed. It could be
            // improvised in the future say triggering some apps?
            return $this->_items;
        }

    }

}

if (!class_exists('CICalItem')) {

    class CICalItem {

        /**
         * @access private
         * @var string
         */
        private $_raw = '';
        private $_title = '';
        private $_summary = '';
        private $_description = '';
        private $_location = '';
        private $_startdate = '';
        private $_enddate = '';
        private $_rule = '';
        private $_repeat = '';
        private $_repeatend = '';
        private $_repeatlimit = '';

        public function __construct($raw) {
            // Raw codes
            $this->_raw = $raw;
        }

        /**
         * Retrieve the item's title
         *
         * @return	string	The calendar's item title
         * */
        public function getTitle() {
            if (empty($this->_title)) {
                // @rule: Match the title
                preg_match('/SUMMARY(.*):(.*)/i', $this->_raw, $match);

                $index = count($match) - 1;
                if (isset($match[$index])) {
                    $this->_title = JString::trim($match[$index]);
                }
            }
            return $this->_title;
        }

        /**
         * Retrieve the item's description
         *
         * @return	string	The calendar's item's description
         * */
        public function getDescription() {
            if (empty($this->_description)) {
                // @rule: Match the description
                $match = array();
                //Description in multiple line and begin with a space
                preg_match('/DESCRIPTION:((.*\n .*)*)\n/ismU', $this->_raw, $match);
                if (isset($match[1])) {
                    $this->_description = JString::trim($match[1]);
                } else {
                    //single line description
                    unset($match);
                    preg_match('/DESCRIPTION:(.*)/i', $this->_raw, $match);

                    if (isset($match[1])) {
                        $this->_description = JString::trim($match[1]);
                    }
                }
            }

            //strip out new line character
//		eval("\$str = \"$this->_description\";"); //Evaluate a string as PHP code
            $this->_description = str_replace('\,', ',', $this->_description);
            return str_replace("\r\n ", '', str_replace('\n', "\n", $this->_description));
        }

        /**
         * Retrieve the item's location
         *
         * @return	string	The calendar's item's location
         * */
        public function getLocation() {
            if (empty($this->_location)) {
                // @rule: Match the description
                $match = array();
                //Description in multiple line and begin with a space
                preg_match('/LOCATION:((.*\n .*)*)/', $this->_raw, $match);

                //var_dump($match);exit;
                if (isset($match[1]) && !empty($match[1])) {
                    $this->_location = JString::trim($match[1]);
                } else {
                    //single line description
                    unset($match);
                    preg_match('/LOCATION:(.*)/i', $this->_raw, $match);

                    if (isset($match[1])) {
                        $this->_location = JString::trim($match[1]);
                    }
                }
            }

            return str_replace(array("\r", "\n", '\,'), array('', '', ','), $this->_location);
        }

        /**
         * Retrieve the item's start date
         *
         * @return	JDate	The calendar start date
         * */
        public function getStartDate() {
            if (empty($this->_startdate)) {
                // @rule: Match the start date
                preg_match('/DTSTART;TZID=(.*)/i', $this->_raw, $match);
                if (isset($match[1])) {
                    $timestamp = JString::trim($match[1]);

                    preg_match('/(.*\/.*):(.*)/i', $timestamp, $match);
                    $timezone = $match[1];
                    $startTime = $match[2];

                    $date = JDate::getInstance($startTime);
                    $this->_startdate = $date->toSql();
                } else {
                    //all day event format
                    preg_match('/DTSTART;VALUE=DATE:(.*)/i', $this->_raw, $match);
                    if (isset($match[1])) {
                        $startTime = $match[1];


                        //$date	= JDate::getInstance( $startTime . 'T000000Z' );
                        //$startTime = $startTime . 'T000000Z';
                        $date = JDate::getInstance($startTime);

                        $this->_startdate = $date->toSql();
                    } else {
                        preg_match('/DTSTART:(.*)/i', $this->_raw, $match);
                        if (isset($match[1])) {
                            $startTime = $match[1];

                            $date = JDate::getInstance($startTime);
                            $this->_startdate = $date->toSql();
                        }
                    }
                }
            }
            return $this->_startdate;
        }

        /**
         * Retrieve the item's end date
         *
         * @return	JDate	The calendar end date
         * */
        public function getEndDate() {
            if (empty($this->_enddate)) {
                // @rule: Match the start date
                preg_match('/DTEND;TZID=(.*)/i', $this->_raw, $match);
                if (isset($match[1])) {
                    $timestamp = JString::trim($match[1]);

                    preg_match('/(.*\/.*):(.*)/i', $timestamp, $match);
                    $timezone = $match[1];
                    $startTime = $match[2];

                    $date = JDate::getInstance($startTime);
                    $this->_enddate = $date->toSql();
                } else {
                    //all day event format
                    preg_match('/DTEND;VALUE=DATE:(.*)/i', $this->_raw, $match);
                    if (isset($match[1])) {
                        $endTime = $match[1];
                        $date = JDate::getInstance(trim($endTime) . 'T235959Z');
                        $this->_enddate = $date->toSql();
                    } else {
                        preg_match('/DTEND:(.*)/i', $this->_raw, $match);

                        if (isset($match[1])) {
                            $endTime = $match[1];
                            $date = JDate::getInstance($endTime);
                            $this->_enddate = $date->toSql();
                        }
                    }
                }
            }
            return $this->_enddate;
        }

        /**
         * Retrieve the item's Summary
         *
         * @return	string	The calendar's item's Summary
         * */
        public function getSummary() {
            if (empty($this->_summary)) {
                // @rule: Match the description
                preg_match('/SUMMARY(.*):(.*)/i', $this->_raw, $match);

                $index = count($match) - 1;
                if (isset($match[$index])) {
                    $this->_summary = $match[$index];
                }
            }

            return str_replace("\r\n ", '', str_replace('\n', "\n", $this->_summary));
        }

        /**
         * Retrieve the repeat ruls
         *
         * @return	array The repeat rules
         * */
        public function _getRule() {
            if (empty($this->_rule)) {
                // @rule: Match the repeat rule
                preg_match('/RRULE:(.*)/i', $this->_raw, $match);

                if (isset($match[1])) {
                    $rule = str_replace(';', '&', strtolower($match[1]));
                    parse_str($rule, $this->_rule);
                }
            }

            return $this->_rule;
        }

        /**
         * Retrieve the repeat type
         *
         * @return	string	The repeat type
         * */
        public function getRepeat() {
            if (empty($this->_repeat)) {
                $this->_getRule();
                if (isset($this->_rule['freq'])) {
                    if (in_array($this->_rule['freq'], array('daily', 'weekly', 'monthly'))) {
                        $this->_repeat = strtolower($this->_rule['freq']);
                    }
                }
            }

            return $this->_repeat;
        }

        /**
         * Retrieve the repeat end date
         *
         * @return	date repeat end date
         * */
        public function getRepeatEnd() {
            if (empty($this->_repeatend)) {
                $this->_getRule();
                if (isset($this->_rule['until'])) {
                    //$repeatend = substr($this->_rule['until'], 0, strpos($this->_rule['until'], 't'));

                    $repeatend = $this->_rule['until'] . ' '; // to convert it to string.
                    $date = JDate::getInstance($repeatend);
                    $this->_repeatend = $date->toSql();

                    $this->_repeatend = CTimeHelper::getFormattedTime($this->_repeatend, '%Y-%m-%d');
                }
            }

            return $this->_repeatend;
        }

        /**
         * Retrieve the repeat occurrence limit
         *
         * @return	number repeat occurrence limit
         * */
        public function getRepeatLimit() {
            if (empty($this->_repeatlimit)) {
                $this->_getRule();
                if (isset($this->_rule['count'])) {
                    $this->_repeatlimit = $this->_rule['count'];
                }
            }

            return $this->_repeatlimit;
        }

    }

}

if (!class_exists('ICal')) {

    /**
     * This is the iCal-class
     *
     * @category Parser
     * @package  Ics-parser
     * @author   Martin Thoma <info@martin-thoma.de>
     * @license  http://www.opensource.org/licenses/mit-license.php  MIT License
     * @link     http://code.google.com/p/ics-parser/
     *
     * @param {string} filename The name of the file which should be parsed
     * @constructor
     */
    class ICal {
        /* How many ToDos are in this ical? */

        public /** @type {int} */ $todo_count = 0;

        /* How many events are in this ical? */
        public /** @type {int} */ $event_count = 0;

        /* The parsed calendar */
        public /** @type {Array} */ $cal;

        /* Which keyword has been added to cal at last? */
        private /** @type {string} */ $_lastKeyWord;

        /**
         * Creates the iCal-Object
         *
         * @param {string} $filename The path to the iCal-file
         *
         * @return Object The iCal-Object
         */
        public function __construct($filename) {
            if (JFile::exists($filename)) {
                $lines = file($filename, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            } else {
                $lines = $filename;
            }

            if (stristr($lines[0], 'BEGIN:VCALENDAR') === false) {
                return false;
            } else {

                // TODO: Fix multiline-description problem (see http://tools.ietf.org/html/rfc2445#section-4.8.1.5)
                foreach ($lines as $line) {
                    $line = trim($line);
                    $add = $this->keyValueFromString($line);
                    if ($add === false) {
                        $this->addCalendarComponentWithKeyAndValue($type, false, $line);
                        continue;
                    }

                    list($keyword, $value) = $add;

                    switch ($line) {
                        // http://www.kanzaki.com/docs/ical/vtodo.html
                        case "BEGIN:VTODO":
                            $this->todo_count++;
                            $type = "VTODO";
                            break;

                        // http://www.kanzaki.com/docs/ical/vevent.html
                        case "BEGIN:VEVENT":
                            //echo "vevent gematcht";
                            $this->event_count++;
                            $type = "VEVENT";
                            break;

                        //all other special strings
                        case "BEGIN:VCALENDAR":
                        case "BEGIN:DAYLIGHT":
                            // http://www.kanzaki.com/docs/ical/vtimezone.html
                        case "BEGIN:VTIMEZONE":
                        case "BEGIN:STANDARD":
                            $type = $value;
                            break;
                        case "END:VTODO": // end special text - goto VCALENDAR key 
                        case "END:VEVENT":
                        case "END:VCALENDAR":
                        case "END:DAYLIGHT":
                        case "END:VTIMEZONE":
                        case "END:STANDARD":
                            $type = "VCALENDAR";
                            break;
                        default:
                            $this->addCalendarComponentWithKeyAndValue($type, $keyword, $value);
                            break;
                    }
                }
                return $this->cal;
            }
        }

        /**
         * Add to $this->ical array one value and key.
         *
         * @param {string} $component This could be VTODO, VEVENT, VCALENDAR, ...
         * @param {string} $keyword   The keyword, for example DTSTART
         * @param {string} $value     The value, for example 20110105T090000Z
         *
         * @return {None}
         */
        public function addCalendarComponentWithKeyAndValue($component, $keyword, $value) {
            if ($keyword == false) {
                $keyword = $this->last_keyword;
                switch ($component) {
                    case 'VEVENT':
                        $value = $this->cal[$component][$this->event_count - 1]
                            [$keyword] . $value;
                        break;
                    case 'VTODO' :
                        $value = $this->cal[$component][$this->todo_count - 1]
                            [$keyword] . $value;
                        break;
                }
            }

            if (stristr($keyword, "DTSTART") or stristr($keyword, "DTEND")) {
                $keyword = explode(";", $keyword);
                $keyword = $keyword[0];
            }

            switch ($component) {
                case "VTODO":
                    $this->cal[$component][$this->todo_count - 1][$keyword] = $value;
                    //$this->cal[$component][$this->todo_count]['Unix'] = $unixtime;
                    break;
                case "VEVENT":
                    $this->cal[$component][$this->event_count - 1][$keyword] = $value;
                    break;
                default:
                    $this->cal[$component][$keyword] = $value;
                    break;
            }
            $this->last_keyword = $keyword;
        }

        /**
         * Get a key-value pair of a string.
         *
         * @param {string} $text which is like "VCALENDAR:Begin" or "LOCATION:"
         *
         * @return {array} array("VCALENDAR", "Begin")
         */
        public function keyValueFromString($text) {
            preg_match("/([^:]+)[:]([\w\W]*)/", $text, $matches);
            if (count($matches) == 0) {
                return false;
            }
            $matches = array_splice($matches, 1, 2);
            return $matches;
        }

        /**
         * Return Unix timestamp from ical date time format
         *
         * @param {string} $icalDate A Date in the format YYYYMMDD[T]HHMMSS[Z] or
         *                           YYYYMMDD[T]HHMMSS
         *
         * @return {int}
         */
        public function iCalDateToUnixTimestamp($icalDate) {
            $icalDate = str_replace('T', '', $icalDate);
            $icalDate = str_replace('Z', '', $icalDate);

            $pattern = '/([0-9]{4})';   // 1: YYYY
            $pattern .= '([0-9]{2})';    // 2: MM
            $pattern .= '([0-9]{2})';    // 3: DD
            $pattern .= '([0-9]{0,2})';  // 4: HH
            $pattern .= '([0-9]{0,2})';  // 5: MM
            $pattern .= '([0-9]{0,2})/'; // 6: SS
            preg_match($pattern, $icalDate, $date);

            // Unix timestamp can't represent dates before 1970
            if ($date[1] <= 1970) {
                return false;
            }
            // Unix timestamps after 03:14:07 UTC 2038-01-19 might cause an overflow
            // if 32 bit integers are used.
            $timestamp = mktime((int) $date[4], (int) $date[5], (int) $date[6], (int) $date[2], (int) $date[3], (int) $date[1]);
            return $timestamp;
        }

        /**
         * Returns an array of arrays with all events. Every event is an associative
         * array and each property is an element it.
         *
         * @return {array}
         */
        public function events() {
            $array = $this->cal;
            return $array['VEVENT'];
        }

        /**
         * Returns a boolean value whether thr current calendar has events or not
         *
         * @return {boolean}
         */
        public function hasEvents() {
            return ( count($this->events()) > 0 ? true : false );
        }

        /**
         * Returns false when the current calendar has no events in range, else the
         * events.
         *
         * Note that this function makes use of a UNIX timestamp. This might be a
         * problem on January the 29th, 2038.
         * See http://en.wikipedia.org/wiki/Unix_time#Representing_the_number
         *
         * @param {boolean} $rangeStart Either true or false
         * @param {boolean} $rangeEnd   Either true or false
         *
         * @return {mixed}
         */
        public function eventsFromRange($rangeStart = false, $rangeEnd = false) {
            $events = $this->sortEventsWithOrder($this->events(), SORT_ASC);

            if (!$events) {
                return false;
            }

            $extendedEvents = array();

            if ($rangeStart !== false) {
                $rangeStart = new DateTime();
            }

            if ($rangeEnd !== false or $rangeEnd <= 0) {
                $rangeEnd = new DateTime('2038/01/18');
            } else {
                $rangeEnd = new DateTime($rangeEnd);
            }

            $rangeStart = $rangeStart->format('U');
            $rangeEnd = $rangeEnd->format('U');



            // loop through all events by adding two new elements
            foreach ($events as $anEvent) {
                $timestamp = $this->iCalDateToUnixTimestamp($anEvent['DTSTART']);
                if ($timestamp >= $rangeStart && $timestamp <= $rangeEnd) {
                    $extendedEvents[] = $anEvent;
                }
            }

            return $extendedEvents;
        }

        /**
         * Returns a boolean value whether thr current calendar has events or not
         *
         * @param {array} $events    An array with events.
         * @param {array} $sortOrder Either SORT_ASC, SORT_DESC, SORT_REGULAR,
         *                           SORT_NUMERIC, SORT_STRING
         *
         * @return {boolean}
         */
        public function sortEventsWithOrder($events, $sortOrder = SORT_ASC) {
            $extendedEvents = array();

            // loop through all events by adding two new elements
            foreach ($events as $anEvent) {
                if (!array_key_exists('UNIX_TIMESTAMP', $anEvent)) {
                    $anEvent['UNIX_TIMESTAMP'] = $this->iCalDateToUnixTimestamp($anEvent['DTSTART']);
                }

                if (!array_key_exists('REAL_DATETIME', $anEvent)) {
                    $anEvent['REAL_DATETIME'] = date("d.m.Y", $anEvent['UNIX_TIMESTAMP']);
                }

                $extendedEvents[] = $anEvent;
            }

            foreach ($extendedEvents as $key => $value) {
                $timestamp[$key] = $value['UNIX_TIMESTAMP'];
            }
            array_multisort($timestamp, $sortOrder, $extendedEvents);

            return $extendedEvents;
        }

    }

}