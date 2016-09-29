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

class CommunityViewFriends extends CommunityView
{

	public function friends($data = null){

		$mainframe	= JFactory::getApplication();
		$jinput 	= $mainframe->input;
		$document   = JFactory::getDocument();
        $my = CFactory::getUser();

		$id         = $jinput->getInt('userid', 0 );
		$sorted	    = $jinput->get->get('sort' , 'latest', 'STRING');
		$filter	    = $jinput->getWord( 'filter' , 'all' );
		$isMine	    = ( ($id == $my->id) && ($my->id != 0) );

		$id         = $id == 0 ? $my->id : $id;
		$user	    = CFactory::getUser($id);
		$friends    = CFactory::getModel('friends');
		$blockModel	= CFactory::getModel('block');

		$document->setLink(CRoute::_('index.php?option=com_community'));


		$rows 		= $friends->getFriends( $id , $sorted , true , $filter );

		// Hide submenu if we are viewing other's friends
		if( $isMine )
		{
			$document->setTitle(JText::_('COM_COMMUNITY_FRIENDS_MY_FRIENDS'));
		}
		else
		{
			$document->setTitle(JText::sprintf('COM_COMMUNITY_FRIENDS_ALL_FRIENDS', $user->getDisplayName()));
		}

		$sortItems =  array(
							'latest' 		=> JText::_('COM_COMMUNITY_SORT_RECENT_FRIENDS') ,
 							'online'		=> JText::_('COM_COMMUNITY_ONLINE') );

		$resultRows = array();

		// @todo: preload all friends
		foreach($rows as $row)
		{
			$user = CFactory::getUser($row->id);

			$obj = clone($row);
			$obj->friendsCount  = $user->getFriendCount();
			$obj->profileLink	= CUrlHelper::userLink($row->id);
			$obj->isFriend		= true;
			$obj->isBlocked		= $blockModel->getBlockStatus($user->id,$my->id);
			$resultRows[] = $obj;
		}
		unset($rows);

		foreach($resultRows as $row){
			if( !$row->isBlocked ) {
				// load individual item creator class
				$item = new JFeedItem();
				$item->title 		= strip_tags($row->name);
				$item->link 		= CRoute::_('index.php?option=com_community&view=profile&userid='.$row->id);
				$item->description 	= '<img src="' . JURI::base() . $row->_thumb . '" alt="" />&nbsp;'.$row->_status;
				$item->date			= $row->lastvisitDate;
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