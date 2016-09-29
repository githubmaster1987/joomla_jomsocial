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

jimport( 'joomla.application.component.view');
jimport( 'joomla.utilities.arrayhelper');

class CommunityViewEvents extends CommunityView
{

	public function display($data = null){
		$mainframe	= JFactory::getApplication();
        $jinput = JFactory::getApplication()->input;
		$document 	= JFactory::getDocument();
		$userid   	= $jinput->getInt('userid');
		$groupId	= $jinput->getInt('groupid');
		$my			= CFactory::getUser();

		$document->setLink(CRoute::_('index.php?option=com_community'));

		// list user events or group events
		if( !empty($groupId) ){

			$title		= JText::_('COM_COMMUNITY_SUBSCRIBE_TO_GROUP_EVENTS_FEEDS');
			$group		= JTable::getInstance( 'Group' , 'CTable' );
			$group->load( $groupId );

			//CFactory::load( 'helpers' , 'owner' );
			$isMember	= $group->isMember( $my->id );
			$isMine		= ($my->id == $group->ownerid);
			if( !$isMember && !$isMine && !COwnerHelper::isCommunityAdmin() && $group->approvals == COMMUNITY_PRIVATE_GROUP )
			{
				echo JText::_('COM_COMMUNITY_GROUPS_PRIVATE_NOTICE');
				return;
			}

			$eventModel	= CFactory::getModel('events');
			$tmpEvents	= $eventModel->getGroupEvents( $groupId, '', $mainframe->get('feed_limit') );
			$rows		= array();

			foreach ($tmpEvents as $eventEntry)
			{
				$event	= JTable::getInstance('Event','CTable');
				$event->bind( $eventEntry );
				$rows[]	= $event;
			}

		}else{

			include_once(JPATH_COMPONENT .'/libraries/events.php');
			$event    = new CEvents;

			$rows     = $event->getFEED($mainframe->get('feed_limit'), $userid);

		}

		foreach($rows as $row)
		{
			if($row->type != 'title')
			{
				$event		= JTable::getInstance( 'Event' , 'CTable' );
				$event->load( $row->id );

				$getGroupId	= !empty($groupId) ? '&groupid='.$groupId : '' ;

				$eventDetails        = '<div>'.JText::_('COM_COMMUNITY_EVENTS_NO_SEAT').': '. $row->ticket .'</div>';
				$eventDetails       .= '<div>'.JText::_('COM_COMMUNITY_EVENTS_LOCATION').': '. $row->location .'</div>';
				$eventDetails       .= '<div>'.JText::_('Start Date').': '. $row->startdate .'</div>';
				$eventDetails       .= '<div>'.JText::_('End Date').': '. $row->enddate .'</div>';
				$eventDetails       .= '<br />';
				$eventDetails       .= '<div>'.JText::_('COM_COMMUNITY_EVENTS_CONFIRMED').': '. $row->confirmedcount .', ';
				$eventDetails       .= JText::_('COM_COMMUNITY_EVENTS_MAYBE').': '. $row->maybecount .', ';
				$eventDetails       .= JText::_('COM_COMMUNITY_EVENTS_REJECTED').': '. $row->declinedcount .'</div>';

				// load individual item creator class
				$item = new JFeedItem();
				$item->title 		= $row->title;
				$item->link 		= CRoute::_('index.php?option=com_community&view=events&task=viewevent&eventid='.$row->id . $getGroupId);
				$item->description 	= '<img src="' . $event->getThumbAvatar() . '" alt="" />&nbsp;'.$row->description.$eventDetails;
				$item->date		    = $row->created;
				$item->category   	= '';//$row->category;

				$item->description = CString::str_ireplace('_QQQ_', '"', $item->description);
				// Make sure url is absolute
				$item->description = CString::str_ireplace('href="/', 'href="'. JURI::base(), $item->description);

				// loads item info into rss array
				$document->addItem( $item );
			}
		}
	}


	public function myevents()
	{
	    $this->display();
	}


	public function pastevents()
	{
		if(!$this->accessAllowed('registered'))	return;

		$mainframe	= JFactory::getApplication();
		$jinput 	= $mainframe->input;
		$document 	= JFactory::getDocument();
		$config		= CFactory::getConfig();
		$my			=	CFactory::getUser();

		$document->setTitle(JText::_('COM_COMMUNITY_EVENTS_PAST_TITLE'));

		$feedLink = CRoute::_('index.php?option=com_community&view=events&format=feed');
		$feed = '<link rel="alternate" type="application/rss+xml" href="'.$feedLink.'"/>';
		$document->addCustomTag( $feed );

		// loading neccessary files here.
		//CFactory::load( 'libraries' , 'filterbar' );
		//CFactory::load( 'helpers' , 'event' );
		//CFactory::load( 'helpers' , 'owner' );
		//CFactory::load( 'models' , 'events');

		$sorted		= $jinput->get->get('sort' , 'startdate', 'STRING');
		$model		= CFactory::getModel( 'events' );

 		// It is safe to pass 0 as the category id as the model itself checks for this value.
 		$rows        = $model->getEvents( null, $my->id , $sorted, null, false, true );

		$sortItems =  array(
				'latest' 		=> JText::_('COM_COMMUNITY_EVENTS_SORT_CREATED') ,
				'startdate'	=> JText::_('COM_COMMUNITY_EVENTS_SORT_START_DATE'));

		//CFactory::load( 'helpers' , 'string' );

		foreach($rows as $row)
		{
			if($row->type != 'title')
			{
				$event	= JTable::getInstance( 'Event' , 'CTable' );
				$event->load( $row->id );

        		$eventDetails        = '<div>'.JText::_('COM_COMMUNITY_EVENTS_NO_SEAT').': '. $row->ticket .'</div>';
        		$eventDetails       .= '<div>'.JText::_('COM_COMMUNITY_EVENTS_LOCATION').': '. $row->location .'</div>';
        		$eventDetails       .= '<div>'.JText::_('Start Date').': '. $row->startdate .'</div>';
        		$eventDetails       .= '<div>'.JText::_('End Date').': '. $row->enddate .'</div>';
        		$eventDetails       .= '<br />';
        		$eventDetails       .= '<div>'.JText::_('COM_COMMUNITY_EVENTS_CONFIRMED').': '. $row->confirmedcount .', ';
        		$eventDetails       .= JText::_('COM_COMMUNITY_EVENTS_MAYBE').': '. $row->maybecount .', ';
        		$eventDetails       .= JText::_('COM_COMMUNITY_EVENTS_REJECTED').': '. $row->declinedcount .'</div>';

				// load individual item creator class
				$item = new JFeedItem();
				$item->title 		= $row->title;
				$item->link 		= CRoute::_('index.php?option=com_community&view=events&task=viewevent&eventid='.$row->id);
				$item->description 	= '<img src="' . $event->getThumbAvatar() . '" alt="" />&nbsp;'.$row->description.$eventDetails;
				$item->date			= $row->created;
				$item->category   	= '';//$row->category;

				$item->description = CString::str_ireplace('_QQQ_', '"', $item->description);

				// Make sure url is absolute
				$item->description = CString::str_ireplace('href="/', 'href="'. JURI::base(), $item->description);

				// loads item info into rss array
				$document->addItem( $item );
			}
		}
	}
}