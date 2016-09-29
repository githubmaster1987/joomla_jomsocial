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

    require_once( JPATH_ROOT .'/components/com_community/libraries/core.php' );

    class modCommunityNearbyeventsHelper
    {
        static public function getStream()
        {

        }

        public static function searchEventsAjax(){
            // Location.
            $mainframe = JFactory::getApplication();
            $jinput    = $mainframe->input;
            $location  = $jinput->request->get('location', '', 'STRING');

            $module = JModuleHelper::getModule('mod_community_nearbyevents');
            $params = new JRegistry($module->params);

            $advance = array();
            $advance['radius'] = $params->get('event_nearby_radius');

            if ($params->get('eventradiusmeasure') == COMMUNITY_EVENT_UNIT_KM) { //find out if radius is in km or miles
                $advance['radius'] = $advance['radius'] * 0.621371192;
            }


            $advance['fromlocation'] = $location;

            $model = CFactory::getModel('events');
            $objs = $model->getEvents(null, null, null, null, true, null, null, $advance);

            $events = array();

            $tmpl = new CTemplate();

            if ($objs) {
                foreach ($objs as $row) {
                    $event = JTable::getInstance('Event', 'CTable');
                    $event->bind($row);
                    $events[] = $event;
                }
                unset($objs);
            }

            // Get list of nearby events
            $tmpl->set('events', $events);
            $tmpl->set('radius', $params->get('event_nearby_radius'));
            $tmpl->set('measurement', $params->get('eventradiusmeasure'));
            $tmpl->set('location', $location);
            $html = $tmpl->fetch('events.nearbylist');

            $json = array(
                'success' => true,
                'html' => $html
            );

            die( json_encode( $json ) );
        }
    }
