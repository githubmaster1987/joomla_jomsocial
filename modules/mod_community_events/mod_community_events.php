<?php
/**
* @copyright (C) 2015 iJoomla, Inc. - All rights reserved.
* @license GNU General Public License, version 2 (http://www.gnu.org/licenses/gpl-2.0.html)
* @author iJoomla.com <webmaster@ijoomla.com>
* @url https://www.jomsocial.com/license-agreement
* The PHP code portions are distributed under the GPL license. If not otherwise stated, all images, manuals, cascading style sheets, and included JavaScript *are NOT GPL, and are released under the IJOOMLA Proprietary Use License v1.0
* More info at https://www.jomsocial.com/license-agreement
*/

    defined('_JEXEC') or die('Restricted access');

    // Check if JomSocial core file exists
    $corefile   = JPATH_ROOT . '/components/com_community/libraries/core.php';

    jimport( 'joomla.filesystem.file' );
    if( !JFile::exists( $corefile ) )
    {
        return;
    }

    // Include JomSocial's Core file, helpers, settings...
    require_once( $corefile );
    require_once dirname(__FILE__) . '/helper.php';
    
    JFactory::getLanguage()->isRTL() ? CTemplate::addStylesheet('style.rtl') : CTemplate::addStylesheet('style');

    $limit = $params->get('limit');

    $upcomingOnly = false;

    $eventType = $params->get('displaysetting',0);

    $userid = null; // null by default
    if($eventType){
        //1 = my events
        $userid = CFactory::getUser()->id;
    }else{
        //0 = show upcoming only
        $upcomingOnly = true;
    }

    $model = CFactory::getModel('Events');
    /* Follow component */

    $mainframe = JFactory::getApplication();
    $jinput = $mainframe->input;

    $categoryId = $jinput->getInt('categoryid', 0);
    $category = JTable::getInstance('EventCategory', 'CTable');
    $category->load($categoryId);

    $sorted = $jinput->get->get('sort', 'startdate', 'STRING');
    $eventparent = $jinput->get->get('parent', '', 'INT');

    $event = JTable::getInstance('Event', 'CTable');
    $handler = CEventHelper::getHandler($event);

    $categories = $model->getAllCategories();
    $categoryIds = CCategoryHelper::getCategoryChilds($categories, $category->id);

    $featuredOnly = $params->get('filter_by',0);

    if(!$eventType){

        //if filter is set to featured only, lets ignore the limit and count the limit later on
        $tempLimit = ($featuredOnly) ? 100000 : $limit;
        //upcoming events
        $result = $model->getEvents($categoryIds, $userid, $sorted, null, true, false, null, array('parent' => $eventparent), CEventHelper::ALL_TYPES, 0 ,$limit, false, false);
    }else{
        //my event
        if(!$userid){
            //if no id is provided and this is user type, the result should be empty
            $result = array();
        }else{
            $result = $model->getEvents(null, $userid, $sorted);
        }
    }

    $events = array();

    if($featuredOnly){
        //1 = featured only
        $featured = new CFeatured(FEATURED_EVENTS);
        $featuredEvents = $featured->getItemIds();
    }

    $count = 0;
    foreach ($result as $row) {
        if($featuredOnly && !in_array($row->id, $featuredEvents)){
            //if we only show featured item, and the item does not exists.
            continue;
        }elseif($count == $limit && $limit){
            break;
        }

        $count++;
        $event = JTable::getInstance('Event', 'CTable');
        $event->bind($row);
        $events[] = $event;
    }

    require(JModuleHelper::getLayoutPath('mod_community_events', $params->get('layout', 'default')));
