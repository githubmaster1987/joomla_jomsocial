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

abstract class CEventHelperHandler
{
	const PRIVACY_PUBLIC	= '0';
	const PRIVACY_MEMBERS	= '20';
	const PRIVACY_FRIENDS	= '30';
	const PRIVACY_PRIVATE	= '40';

	protected $model 	= '';
	protected $my		= '';
	protected $cid		= '';
	protected $event	= '';
	protected $url		= '';

	public function __construct( $event )
	{
		$this->my		= CFactory::getUser();
		$this->model	= CFactory::getModel( 'events' );
		$this->event	= $event;
		$this->url		= 'index.php?option=com_community&view=events&task=viewevent&eventid=' . $this->event->id;
	}

	/**
	 * Returns the unique identifier for the event. E.g group's id.
	 **/
	abstract public function getContentId();

	/**
	 * Returns the event url.
	 **/
	abstract public function getType();

	/**
	 * Sets the respective submenus in the view
	 **/
	abstract public function addSubmenus( $view );

	/**
	 * Determines whether the current event exists
	 **/
	abstract public function exists();

	/**
	 * Determines whether the current user is allowed to browse the event or not.
	 **/
	abstract public function browsable();

	/**
	 * Determines whether the current user is allowed to create an event or not.
	 **/
	abstract public function creatable();

	/**
	 * Determines whether the current user is allowed to manage an event.
	 **/
	abstract public function manageable();

	/**
	 * Determines whether the current user is allowed to access an event or not.
	 **/
	abstract public function isAllowed();

	/**
	 * Returns a stdclass object for activity so that the event would be able to add it.
	 **/
	abstract public function getActivity( $command , $actor , $target , $content , $cid , $app );

	/**
	 * Retrieves the url for the specific event
	 **/
	abstract public function getFormattedLink( $raw , $xhtml = true , $external = false );

	/**
	 * Determines whether or not the current event is public or private
	 **/
	abstract public function isPublic();

	/**
	 * Determines whether to show categories or not.
	 **/
	abstract public function showCategories();

	/**
	 * Retrieves the redirect link after an event is ignored.
	 **/
	abstract public function getIgnoreRedirectLink();

	/**
	 * Retrieves the events to be shown
	 **/
	abstract public function getContentTypes();

	/**
	 * Determines whether to show print event or not
	 **/
	abstract public function showPrint();

	/**
	 * Determines whether to show event export or not
	 **/
	abstract public function showExport();

	/**
	 * Determines if the current event should display the privacy details
	 *
	 * @return	bool 	Whether the current event requires privacy or not.
	 **/
	public function hasPrivacy()
	{
		if( $this->getType() == CEventHelper::GROUP_TYPE )
		{
			return false;
		}

		return true;
	}

	/**
	 * Determines if the current event should display the invitation details
	 *
	 * @return	bool 	Whether the current event requires invitation
	 **/
	public function hasInvitation()
	{
		if( $this->getType() == CEventHelper::GROUP_TYPE )
		{
			return false;
		}

		return true;
	}

	public function isExpired()
	{
		$event=$this->event;
		$today = strtotime(date("Y-m-d H:i:s "));
		$expiration_date = strtotime($event->enddate);

		if($today > $expiration_date){
		    return false;
		}
		return true;

	}




}

class CEventGroupHelperHandler extends CEventHelperHandler
{
	public function __construct( $event )
	{
        $jinput = JFactory::getApplication()->input;
		$this->cid 		= $jinput->request->getInt('groupid' , '');
		$this->group	= JTable::getInstance( 'Group' , 'CTable' );

		if( empty( $this->cid ) )
		{
			$this->cid	= $event->contentid;
		}
		$this->group->load( $this->cid );

		parent::__construct( $event );
	}

	public function showPrint()
	{
		return true;
	}

	public function showExport()
	{
		return true;
	}

	public function getContentTypes()
	{
		return CEventHelper::GROUP_TYPE;
	}

	public function getIgnoreRedirectLink()
	{
		return $this->getFormattedLink( 'index.php?option=com_community&view=groups&task=viewgroup&groupid=' . $this->group->id , false );
	}

	public function showCategories()
	{
		return false;
	}

	public function getActivity( $command , $actor , $target , $content , $cid , $app )
	{
		// Need to prepend groups. into the activity command as we might want to
		// give different points for specific title
		$command		= 'groups.' . $command;
		$title			= '';

		$act = new stdClass();
		$act->cmd 		= $command;
		$act->actor   	= $actor;
		$act->target  	= $target;
		$act->title	  	= $title;
		$act->content	= $content;
		$act->app		= $app;
		$act->cid		= $cid;

		return $act;
	}

	public function isPublic()
	{
		return $this->group->approvals	== COMMUNITY_PUBLIC_GROUP;
	}

	public function exists()
	{
		return $this->event->contentid == $this->cid && $this->event->id != 0;
	}

	public function creatable()
	{
		//CFactory::load( 'helpers' , 'group' );

		return CGroupHelper::allowCreateEvent( $this->my->id , $this->cid );
	}

	public function manageable()
	{
		//CFactory::load( 'helpers' , 'group' );

		return CGroupHelper::allowManageEvent( $this->my->id , $this->cid , $this->event->id );
	}

	public function getFormattedLink( $raw , $xhtml = true , $external = false , $route = true )
	{
		$raw	.= '&groupid=' . $this->event->contentid;
		$url	= '';

		if( $external )
		{
			$url	= $route ? CRoute::getExternalURL( $raw , $xhtml ) : $raw;
		}
		else
		{
			$url	= $route ? CRoute::_( $raw , $xhtml ) : $raw;
		}

		return $url;
	}

	public function browsable()
	{
		//CFactory::load( 'helpers' , 'owner' );

		if( COwnerHelper::isCommunityAdmin() )
		{
			return true;
		}

		$group	= JTable::getInstance( 'Group' , 'CTable' );
		$group->load( $this->cid );
		$params	= $group->getParams();

		if( ( $group->approvals == COMMUNITY_PRIVATE_GROUP && !$group->isMember( $this->my->id ) ))
		{
			return false;
		}
		return true;
	}

	public function addSubmenus( $view )
	{
		$mainframe	= JFactory::getApplication();
		$jinput 	= $mainframe->input;
		$config			= CFactory::getConfig();
		$task			= $jinput->get->get('task' , '');
        $groupid = $jinput->get->get('groupid' ,0, '');
		$showBackLink	= array( 'invitefriends', 'uploadavatar' , 'edit' , 'sendmail', 'app');

		$my = CFactory::getUser();
		$allowCreateEvent	= CGroupHelper::allowCreateEvent( $my->id , $this->cid );

        if(!$groupid) {
            $view->addSubmenuItem('index.php?option=com_community&view=events&task=display', JText::_('COM_COMMUNITY_EVENTS_ALL') );
        }

        if( COwnerHelper::isRegisteredUser())
        {
            $view->addSubmenuItem('index.php?option=com_community&view=events&task=myevents&userid='. $this->my->id, ($groupid)? JText::_('COM_COMMUNITY_EVENT_GROUP_MINE') : JText::_('COM_COMMUNITY_EVENTS_MINE'));

            //check if there is any pending events invitation
            $model = CFactory::getModel('events');
            $sorted = $jinput->get->get(
                'sort',
                'startdate',
                'STRING'
            );
            $pending = COMMUNITY_EVENT_STATUS_INVITED;
            $events = $model->getEvents(null, CFactory::getUser()->id, $sorted, null, true, false, $pending);

            if($events){
                $view->addSubmenuItem('index.php?option=com_community&view=events&task=myinvites&userid='. $this->my->id, JText::_('COM_COMMUNITY_EVENTS_PENDING_INVITATIONS'));
            }

        }

		if( $allowCreateEvent && $config->get('group_events') && $config->get('enableevents') && ($config->get('createevents') || COwnerHelper::isCommunityAdmin()) ) {
			//$view->addSubmenuItem('index.php?option=com_community&view=events&task=create&groupid=' . $this->cid, JText::_('COM_COMMUNITY_EVENTS_CREATE') , '' , SUBMENU_RIGHT );
		}
		if( $config->get('event_import_ical')) {
			$view->addSubmenuItem('index.php?option=com_community&view=events&task=import&groupid=' . $this->cid, JText::_('COM_COMMUNITY_GROUPS_IMPORT_EVENT') , '' , SUBMENU_RIGHT );
		}
		if( in_array( $task , $showBackLink ) )
		{
			// @rule: Add a link back to the event's page
			$view->addSubmenuItem( 'index.php?option=com_community&view=events&task=viewevent&eventid=' . $this->event->id . '&groupid=' . $this->cid , JText::_('COM_COMMUNITY_EVENTS_BACK_BUTTON') );
		}
		// @rule: Add a link back to the group's page.
		//$view->addSubmenuItem( 'index.php?option=com_community&view=groups&task=viewgroup&groupid=' . $this->cid , JText::_('COM_COMMUNITY_GROUPS_BACK_TO_GROUP') );
		//$view->addSubmenuItem( 'index.php?option=com_community&view=events&groupid=' . $this->cid , JText::_('COM_COMMUNITY_EVENTS_ALL') );
		$view->addSubmenuItem( 'index.php?option=com_community&view=events&task=pastevents&groupid=' . $this->cid, JText::_('COM_COMMUNITY_EVENTS_PAST_TITLE'));

	}

	public function getContentId()
	{
		return $this->cid;
	}

	public function getType()
	{
		return CEventHelper::GROUP_TYPE;
	}

	public function isAllowed()
	{
		//CFactory::load( 'helpers' , 'owner' );

		$group	= JTable::getInstance( 'Group' , 'CTable' );
		$group->load( $this->cid );

		return $group->isMember( $this->my->id ) || COwnerHelper::isCommunityAdmin();
	}

	 /**
     * Check if user is owner / event admin / site admin
     * @return boolean
     */
    public function isAdmin ()
    {
       	$config = CFactory::getConfig();

		if( COwnerHelper::isCommunityAdmin() || $this->event->isCreator( $this->my->id )  || $this->event->isAdmin( $this->my->id ) )
		{
			return true;
		}

		return false;
    }
}

class CEventUserHelperHandler extends CEventHelperHandler
{
	public function __construct( $event )
	{
		parent::__construct( $event );
	}

	public function showPrint()
	{
		return $this->isAllowed();
	}

	public function showExport()
	{
		return $this->isAllowed();
	}

	public function getContentTypes()
	{
		return CEventHelper::ALL_TYPES;
	}

	public function showCategories()
	{
		return true;
	}

	public function getIgnoreRedirectLink()
	{
		return $this->getFormattedLink( 'index.php?option=com_community&view=events' , false );
	}

	public function getActivity( $command , $actor , $target , $content , $cid , $app )
	{

		$act = new stdClass();
		$act->cmd 		= $command;
		$act->actor   	= $actor;
		$act->target  	= $target;
		$act->title		= '';
		$act->content	= $content;
		$act->app		= $app;
		$act->cid		= $cid;

		return $act;
	}

	public function isPublic()
	{
		return $this->event->permission == COMMUNITY_PUBLIC_EVENT;
	}

	public function browsable()
	{
		// Since we do not impose any restrictions on profile events,
		// regardless of the event type, we don't really need to prevent this.
		return true;
	}

	public function creatable()
	{
		$config		= CFactory::getConfig();

		//CFactory::load( 'helpers' , 'owner' );
		if(COwnerHelper::isCommunityAdmin()){
			return true;
		}
		if( !$config->getBool('createevents') || $this->my->id == 0 )
		{
			return false;
		}
		return true;
	}

	public function exists()
	{
		return $this->event->id != 0;
	}

	public function manageable()
	{
		$config = CFactory::getConfig();

		if(
                        ( COwnerHelper::isCommunityAdmin() || $this->event->isCreator( $this->my->id )  || $this->event->isAdmin( $this->my->id ) )|| (!$config->get('lockeventwalls') && $this->event->permission == 0) )
		{
			return true;
		}
		return false;
	}

    /**
     * Check if user is owner / event admin / site admin
     * @return boolean
     */
    public function isAdmin ()
    {
       	$config = CFactory::getConfig();

		if( COwnerHelper::isCommunityAdmin() || $this->event->isCreator( $this->my->id )  || $this->event->isAdmin( $this->my->id ) )
		{
			return true;
		}

		return false;
    }

	public function getFormattedLink( $raw , $xhtml = true , $external = false , $route = true )
	{
		$url	= '';

		if( $external )
		{
			$url	= $route ? CRoute::getExternalURL( $raw , $xhtml ) : $raw;
		}
		else
		{
			$url	= $route ? CRoute::_( $raw , $xhtml ) : $raw;
		}
		return $url;
	}

	public function addSubmenus( $view )
	{
		$config		= CFactory::getConfig();
		$mainframe	= JFactory::getApplication();
		$jinput 	= $mainframe->input;
		$task		= $jinput->get('task' , '');
		$backLink	= array( 'invitefriends', 'viewguest', 'uploadavatar' , 'edit' , 'sendmail', 'app');

		if( in_array( $task , $backLink) )
		{

		    $eventid	= $jinput->get->get('eventid' , '', 'INT');

			$view->addSubmenuItem('index.php?option=com_community&view=events&task=viewevent&eventid=' . $eventid, JText::_('COM_COMMUNITY_EVENTS_BACK_BUTTON'));
		}
		else
		{
    		$view->addSubmenuItem('index.php?option=com_community&view=events&task=display', JText::_('COM_COMMUNITY_EVENTS_ALL') );

			if( COwnerHelper::isRegisteredUser())
			{
				$view->addSubmenuItem('index.php?option=com_community&view=events&task=myevents&userid='. $this->my->id, JText::_('COM_COMMUNITY_EVENTS_MINE'));
				$view->addSubmenuItem('index.php?option=com_community&view=events&task=myinvites&userid='. $this->my->id, JText::_('COM_COMMUNITY_EVENTS_PENDING_INVITATIONS'));
			}

			// Even guest should be able to view old events
			$view->addSubmenuItem('index.php?option=com_community&view=events&task=pastevents', JText::_('COM_COMMUNITY_EVENTS_PAST_TITLE'));

            $my	= CFactory::getUser();
			if( COwnerHelper::isRegisteredUser() && $config->get('createevents') && $my->canCreateEvents() || COwnerHelper::isCommunityAdmin() )
			{

                //$view->addSubmenuItem('index.php?option=com_community&view=events&task=create', JText::_('COM_COMMUNITY_EVENTS_CREATE') , '' , SUBMENU_RIGHT );

				if( $config->get('event_import_ical') )
				{
					$view->addSubmenuItem('index.php?option=com_community&view=events&task=import', JText::_('COM_COMMUNITY_EVENTS_IMPORT') , '' , SUBMENU_RIGHT );
				}
			}

			if( (!$config->get('enableguestsearchevents') && COwnerHelper::isRegisteredUser()  ) || $config->get('enableguestsearchevents') )
			{
				$tmpl = new CTemplate();
				$tmpl->set( 'url', CRoute::_('index.php?option=com_community&view=events&task=search') );
				$html = $tmpl->fetch( 'events.search.submenu' );

				//$view->addSubmenuItem('index.php?option=com_community&view=events&task=search', JText::_('COM_COMMUNITY_EVENTS_SEARCH'), 'joms.events.toggleSearchSubmenu(this)', false, $html);
			}
		}
	}

	public function getContentId()
	{
		// Since profile based events will always use 0 as the content id
		return 0;
	}

	public function getType()
	{
		return CEventHelper::PROFILE_TYPE;
	}

	public function isAllowed()
	{
		//CFactory::load( 'helpers' , 'owner' );

		$status	= $this->event->getUserStatus( $this->my->id );

		return ( ( ($status == COMMUNITY_EVENT_STATUS_INVITED)
				|| ($status == COMMUNITY_EVENT_STATUS_ATTEND)
				|| ($status == COMMUNITY_EVENT_STATUS_WONTATTEND)
				|| ($status == COMMUNITY_EVENT_STATUS_MAYBE)
				|| !$this->event->permission
				)
				&& ($status != COMMUNITY_EVENT_STATUS_BLOCKED)
				|| COwnerHelper::isCommunityAdmin() );
	}
}

class CEventHelper
{
	var $handler	= '';
	var $id			= '';
	const GROUP_TYPE	= 'group';
	const PROFILE_TYPE	= 'profile';
	const ALL_TYPES		= 'all';

	static public function getHandler( CTableEvent $event )
	{
		static $handler	= array();
        $jinput = JFactory::getApplication()->input;
		if( !isset( $handler[ $event->id ] ) )
		{
			// During AJAX calls, we might not be able to determine the groupid
			$defaultId	= ( $event ) ? $event->contentid : '';
			$groupId	= $jinput->request->getInt( 'groupid' , $defaultId );

			if( !empty($groupId) )
			{
				$handler[ $event->id ]	= new CEventGroupHelperHandler( $event );
			}
			else
			{
				$handler[ $event->id ]	= new CEventUserHelperHandler( $event );
			}
		}

		return isset( $handler[ $event->id ] ) ? $handler[ $event->id ] : false;
	}

	/**
	 * Return true if the event is going on today
	 * An event is considered a 'today' event IF
	 * - starting date is today
	 * or
	 * - starting date if in the past but ending date is in the future
	 */
	static public function isToday($event)
	{
		$startDate = CTimeHelper::getLocaleDate($event->startdate);
		$endDate   = CTimeHelper::getLocaleDate($event->enddate);

		$now = CTimeHelper::getLocaleDate();

		// Same year, same day of the year
		$isToday = $startDate->format('Y-m-d')==$now->format('Y-m-d')?true:false;

		// If still not today, see if the event is ongoing now
		if(!$isToday)
		{
			$nowUnix = $now->toUnix();
			$isToday = (
					($startDate->toUnix() < $nowUnix)
				&&	($endDate->toUnix() > $nowUnix));
		}

		return $isToday;
	}

	/**
	 * Return true if the event is past
	 * A past event, is events that are has passed more than 24 hours from the last date
	 */
	static public function isPast( $event )
	{
		$endDate = CTimeHelper::getLocaleDate($event->enddate);
		$now     = CTimeHelper::getLocaleDate();

		$nowUnix = $now->toUnix();
		$isPast	 = ( $endDate->toUnix() < $nowUnix );

		return $isPast;
	}

    static public function allowPhotoWall($eventid)
    {
        $permission = CEventHelper::getMediaPermission($eventid);

        if( $permission->isMember || $permission->isAdmin || $permission->isSuperAdmin )
        {
            return true;
        }
        return false;
    }

	static public function getPostCount($eventid){
		$db = JFactory::getDbo();

		$query = "SELECT count(*) as count FROM ".$db->quoteName('#__community_activities')
				." WHERE ".$db->quoteName('app')."=".$db->quote('events.wall')
				." AND ".$db->quoteName('eventid')."=".$eventid;

		$db->setQuery($query);

		return $db->loadResult();
	}

	/**
	 * Return true if the event is going on this week
	 */
	static public function isThisWeek($event)
	{
	}

    static public function getMediaPermission( $eventid )
    {
        // load COwnerHelper::isCommunityAdmin()
        //CFactory::load( 'helpers' , 'owner' );
        $my	= CFactory::getUser();

        $isSuperAdmin		= COwnerHelper::isCommunityAdmin();
        $isAdmin			= false;
        $isMember			= false;

        // Load the group table.
        $event		= JTable::getInstance( 'Event' , 'CTable' );
        $event->load( $eventid );
        $params		= new CParameter($event->params);

        if(!$isSuperAdmin)
        {
            $isAdmin	= $event->isAdmin( $my->id , $event->id );
            $isMember	= $event->isMember( $my->id );
        }

        $permission = new stdClass();
        $permission->isMember 			= $isMember;
        $permission->isAdmin 			= $isAdmin;
        $permission->isSuperAdmin 		= $isSuperAdmin;
        $permission->params 			= $params;

        return $permission;
    }

    static public function allowManagePhoto($eventId)
    {
        $allowManagePhotos = false;

        //get permission
        $permission = CEventHelper::getMediaPermission($eventId);

        $photopermission	= $permission->params->get('photopermission' , EVENT_PHOTO_PERMISSION_ADMINS );

        //checking for backward compatibility
        if($photopermission == EVENT_PHOTO_PERMISSION_ALL)
        {
            $photopermission = EVENT_PHOTO_PERMISSION_MEMBERS;
        }

        if($photopermission == EVENT_PHOTO_PERMISSION_DISABLE)
        {
            $allowManagePhotos = false;
        } else if( ($photopermission == EVENT_PHOTO_PERMISSION_MEMBERS && $permission->isMember) || $permission->isAdmin || $permission->isSuperAdmin ) {
            $allowManagePhotos = true;
        }

        return $allowManagePhotos;
    }

	/**
	 * Returns formatted date for the event for the given format.
	 *
	 * @param	CTableEvent	$event	The event table object.
	 * @param	String		$format	The date format.
	 *
	 * @return	String	HTML value for the formatted date.
	 **/
	static public function formatStartDate($event, $format)
	{
		$date		= JDate::getInstance( $event->startdate );
		$html		= $date->format( $format );

		return $html;
	}

    /**
     * check if seat is still available
     * @param $event
     */
    static public function seatsAvailable($event){
        $eventMembersCount = $event->getMembersCount( COMMUNITY_EVENT_STATUS_ATTEND );

        //0 ticket = unlimited
        return ($event->ticket == 0) ? true : ($event->ticket > $eventMembersCount);
    }

	static public function getDateSelection($startDate='', $endDate='')
	{
		if (empty($startDate)) $startDate = JDate::getInstance( '00:01' );
		if (empty($endDate))   $endDate   = JDate::getInstance( '23:59' );

		$startAmPmSelect = "";
		$endAmPmSelect = "";
		$hours = array();

		$config		= CFactory::getConfig();

		if($config->get('eventshowampm'))
		{
			for($i = 1; $i <= 12; $i++)
			{
				$hours[] = JHTML::_('select.option',  $i, "$i" );
			}

			// Cannot user ->Format('%p') since it is dependent on current locale
			// and would return a null if the system is configured for 24H
			$startAmPm 		= $startDate->format('H') >= 12 ? 'PM' : 'AM';
			$endAmPm		= $endDate->format('H') >= 12 ? 'PM' : 'AM';

			$amPmSelect		= array();
			$amPmSelect[]		= JHTML::_('select.option',  'AM', "am" );
			$amPmSelect[]		= JHTML::_('select.option',  'PM', "pm" );

			$startAmPmSelect	= JHTML::_('select.genericlist',  $amPmSelect , 'starttime-ampm', array('class'=>'required input-mini'), 'value', 'text', $startAmPm , false );
			$endAmPmSelect		= JHTML::_('select.genericlist',  $amPmSelect , 'endtime-ampm', array('class'=>'required input-mini'), 'value', 'text', $endAmPm , false );

			$selectedStartHour 	= intval($startDate->format('g'));
			$selectedEndHour 	= intval($endDate->format('g'));
		}
		else
		{
			for($i = 0; $i <= 23; $i++)
			{
				$hours[] = JHTML::_('select.option',  $i, sprintf( "%02d" ,$i) );
			}

			$selectedStartHour 	= intval($startDate->Format('H'));
			$selectedEndHour 	= intval($endDate->Format('H'));
		}
		$startHourSelect		= JHTML::_('select.genericlist',  $hours, 'starttime-hour', array('class'=>'required input-mini'), 'value', 'text', $selectedStartHour , false );
		$endHourSelect			= JHTML::_('select.genericlist',  $hours, 'endtime-hour', array('class'=>'required input-mini'), 'value', 'text', $selectedEndHour , false );

		$minutes	= array();
		$minutes[]	= JHTML::_('select.option',  0, "00" );
		$minutes[]	= JHTML::_('select.option',  15, "15" );
		$minutes[]	= JHTML::_('select.option',  30, "30" );
		$minutes[] 	= JHTML::_('select.option',  45, "45" );

		$startMinSelect		= JHTML::_('select.genericlist',  $minutes , 'starttime-min', array('class'=>'required input-mini'), 'value', 'text', $startDate->Format('i') , false );
		$endMinSelect		= JHTML::_('select.genericlist',  $minutes , 'endtime-min', array('class'=>'required input-mini'), 'value', 'text', $endDate->Format('i' ) , false );

		$html = new stdClass();
		$html->startDate = $startDate;
		$html->endDate   = $endDate;
		$html->startHour = $startHourSelect;
		$html->endHour   = $endHourSelect;
		$html->startMin  = $startMinSelect;
		$html->endMin    = $endMinSelect;
		$html->startAmPm = $startAmPmSelect;
		$html->endAmPm   = $endAmPmSelect;

		return $html;
	}

    /**
     * To determine if the button for going, not going, waiting response should be shown
     * @param $event
     */
    public static function showAttendButton($event){
        $my = CFactory::getUser();

        if(!CEventHelper::seatsAvailable($event) && $event->getMemberStatus($my->id) == COMMUNITY_EVENT_STATUS_ATTEND){
            return true;
        }elseif(!CEventHelper::seatsAvailable($event)){
            return false;
        }

        //always show for public event
        if($event->permission == COMMUNITY_PUBLIC_EVENT){
            return true;
        }

        //else anything below this is public event

        $allowShow = array(COMMUNITY_EVENT_STATUS_ATTEND,COMMUNITY_EVENT_STATUS_WONTATTEND,COMMUNITY_EVENT_STATUS_MAYBE);
        //show if i am the member of the group or if i am invited
        if(in_array($event->getMemberStatus($my->id),$allowShow) || $event->getUserStatus($my->id) == COMMUNITY_EVENT_STATUS_INVITED){
            return true;
        }

    }

    public static function isBanned($userid, $eventid) {
        $db = JFactory::getDbo();

        $query = 'SELECT COUNT(*) FROM '
            . $db->quoteName('#__community_events_members') . ' '
            . 'WHERE ' . $db->quoteName('eventid') . '=' . $db->Quote($eventid) . ' '
            . 'AND ' . $db->quoteName('memberid') . '=' . $db->Quote($userid)
            . 'AND ' . $db->quoteName('status') . '=' . $db->Quote(COMMUNITY_EVENT_STATUS_BANNED);

        $db->setQuery($query);

        $status = ( $db->loadResult() > 0 ) ? true : false;

        return $status;
    }
}
