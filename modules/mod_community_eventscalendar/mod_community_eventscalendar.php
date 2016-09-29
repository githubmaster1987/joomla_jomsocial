<?php
/**
* @copyright (C) 2015 iJoomla, Inc. - All rights reserved.
* @license GNU General Public License, version 2 (http://www.gnu.org/licenses/gpl-2.0.html)
* @author iJoomla.com <webmaster@ijoomla.com>
* @url https://www.jomsocial.com/license-agreement
* The PHP code portions are distributed under the GPL license. If not otherwise stated, all images, manuals, cascading style sheets, and included JavaScript *are NOT GPL, and are released under the IJOOMLA Proprietary Use License v1.0
* More info at https://www.jomsocial.com/license-agreement
*/
defined( '_JEXEC' ) or die( 'Unauthorized Access' );

	// Check if JomSocial core file exists
	$corefile 	= JPATH_ROOT . '/components/com_community/libraries/core.php';

	jimport( 'joomla.filesystem.file' );
	if( !JFile::exists( $corefile ) )
	{
		return;
	}

	// Include JomSocial's Core file, helpers, settings...
	require_once( $corefile );
	require_once dirname(__FILE__) . '/helper.php';

	// Add proper stylesheet
    JFactory::getLanguage()->isRTL() ? CTemplate::addStylesheet('style.rtl') : CTemplate::addStylesheet('style');

	$user = CFactory::getUser();

    $document = JFactory::getDocument();
    $document->addStyleSheet(JURI::root(true) . '/modules/mod_community_eventscalendar/style.css');
    // $document->addScript('http://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js');
    // $document->addScript(JURI::root(true) . '/modules/mod_community_eventscalendar/calendar.js');

    //first, lets see what is the type of the events that we should show
    $displaySettings = $params->get('displaysetting',0);
    $firstDay = $params->get('firstday',0);
    $dateFormat = $params->get('dateformat','d/m');
    $timeFormat = $params->get('timeformat','g:i A');

    // 0 = allevents, 1 = group events only, 2 - specific group, 3 - specific event
    $eventModel = CFactory::getModel('events');
    $eventModel->setState('limit', 10000);//set the default state to 10k events.
    switch($displaySettings){
        case 0:
            //all events
            $events = $eventModel->getEvents(null,null,null,null,true,false,null,null,CEventHelper::ALL_TYPES,0,null,false,false);
            break;
        case 1:
            //group events only
            $events = $eventModel->getEvents(null,null,null,null,true,false,null,null,CEventHelper::GROUP_TYPE,0,null,false,false);
            break;
        case 2:
            //specific group
            $groupEventId = $params->get('jsgroup');
            $events = $eventModel->getGroupEvents($groupEventId);
            break;
        case 3:
            //specific event category
            $eventCategory = $params->get('jseventcategory');
            $events = $eventModel->getEvents($eventCategory,null,null,null,true,false,null,null,CEventHelper::ALL_TYPES,0,null,false,false);
            break;
    }

    //only show the events that the user is able to view
    if(!COwnerHelper::isCommunityAdmin()){ // admin can always see all events, so no need to filter
        foreach($events as $key=>$event){
            // if private and not a member
            $eventTable = JTable::getInstance('Event','CTable');
            $eventTable->load($event->id);
            if($event->permission && !$eventTable->isMember($user->id) ){
                unset($events[$key]);
                continue;
            }
        }
    }


    require(JModuleHelper::getLayoutPath('mod_community_eventscalendar', $params->get('layout', 'default')));
