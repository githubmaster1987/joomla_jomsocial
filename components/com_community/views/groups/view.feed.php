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

class CommunityViewGroups extends CommunityView
{

	/**
	 * Display a list of bulletins from the specific group
	 **/
    public function display()
    {
		$mainframe	= JFactory::getApplication();
		$jinput 	= $mainframe->input;
		$document = JFactory::getDocument();
		$view     = $jinput->get->get('task', '');

		if($view == 'viewlatestdiscussions')
		{
		    $this->_viewlatestdiscussions();
		    return;
		}

		if($view == 'viewmylatestdiscussions')
		{
		    $this->_viewmylatestdiscussions();
		    return;
		}

		$document->setLink(CRoute::_('index.php?option=com_community'));

		$model  = CFactory::getModel('groups');
		$rows   = $model->getAllGroups();

		//CFactory::load( 'helpers' , 'string' );

		foreach($rows as $row){
			// load individual item creator class
			$item = new JFeedItem();
			$item->title 		= $row->name;
			$item->link 		= CRoute::_('index.php?option=com_community&view=groups&task=viewgroup&groupid='.$row->id);
			$item->description 	= '<img src="' . JURI::base() . $row->thumb . '" alt="" />&nbsp;'.$row->description;
			$item->date			= $row->created;
			$item->category   	= '';//$row->category;

			$item->description = CString::str_ireplace('_QQQ_', '"', $item->description);
			// Make sure url is absolute
			$item->description = CString::str_ireplace('href="/', 'href="'. JURI::base(), $item->description);

			// loads item info into rss array
			$document->addItem( $item );
		}
	}

	/**
	 * Display recent discussion replies
	 **/
	public function viewdiscussion()
	{
		$mainframe	= JFactory::getApplication();
		$jinput 	= $mainframe->input;
		$document 	= JFactory::getDocument();

		$model		= CFactory::getModel( 'Discussions' );
		$discussion	= JTable::getInstance( 'Discussion' , 'CTable' );
		$topicId	= $jinput->get('topicid' , 0, 'INT');
		$my			= CFactory::getUser();

		if( $topicId == 0 )
		{
			return;
		}
		$discussion->load( $topicId );

		$group		= JTable::getInstance( 'Group' , 'CTable' );
		$group->load( $discussion->groupid );

		if( $group->id == 0 )
		{
			echo JText::_('COM_COMMUNITY_GROUPS_ID_NOITEM');
			return;
		}

		//CFactory::load( 'helpers' , 'owner' );
		//CFactory::load( 'helpers' , 'string' );

		//display notice if the user is not a member of the group
		if( $group->approvals == 1 && !($group->isMember($my->id) ) && !COwnerHelper::isCommunityAdmin() )
		{
			echo JText::_('COM_COMMUNITY_GROUPS_PRIVATE_NOTICE');
			return;
		}


		$rows	= $model->getReplies( $topicId );

		$document->setTitle( JText::sprintf('COM_COMMUNITY_GROUPS_DISCUSSION_REPLIES_VIEW' , $discussion->title ) );
		$document->setDescription( strip_tags( $discussion->message ) );
		//CFactory::load( 'helpers' , 'string' );

		if( $rows )
		{
			foreach($rows as $row)
			{
				$date				= JDate::getInstance( $row->date );
				$user				= CFactory::getUser( $row->post_by );

				$item				= new JFeedItem();
				$item->title 		= JText::sprintf( 'COM_COMMUNITY_GROUPS_REPLY_FROM' , $user->getDisplayName() );
				$item->link 		= CRoute::_('index.php?option=com_community&view=groups&task=viewdiscussion&topicid=' . $topicId . '&groupid='.$group->id ) . '#wall_118';
				$item->description 	= $row->comment;
				$item->date			= $date->toSql( true );
				$item->category   	= '';

				$item->description = CString::str_ireplace('_QQQ_', '"', $item->description);
				// loads item info into rss array
				$document->addItem( $item );
			}
		}
	}

	/**
	 * Method to display groups that belongs to a user.
	 *
	 * @access public
	 */
	public function mygroups()
	{
		$mainframe	= JFactory::getApplication();
		$jinput 	= $mainframe->input;
		$document  = JFactory::getDocument();
		$userId    = $jinput->get('userid','', 'INT');

		$document->setTitle(JText::_('COM_COMMUNITY_GROUPS_MY_GROUPS'));
		$document->setLink(CRoute::_('index.php?option=com_community'));

		$groupsModel	= CFactory::getModel('groups');

		$sorted			= $jinput->get->get('sort' , 'latest', 'STRING');
		$rows			= $groupsModel->getGroups( $userId , $sorted );

		//CFactory::load( 'helpers' , 'string' );

		foreach($rows as $row){
			// load individual item creator class
			$item = new JFeedItem();
			$item->title 		= $row->name;
			$item->link 		= CRoute::_('index.php?option=com_community&view=groups&task=viewgroup&groupid='.$row->id);
			$item->description 	= '<img src="' . JURI::base() . $row->thumb . '" alt="" />&nbsp;'.$row->description;
			$item->date			= $row->created;
			$item->category   	= '';//$row->category;

			$item->description = CString::str_ireplace('_QQQ_', '"', $item->description);
			// Make sure url is absolute
			$item->description = CString::str_ireplace('href="/', 'href="'. JURI::base(), $item->description);

			// loads item info into rss array
			$document->addItem( $item );
		}

	}

	/**
	 * Display a list of bulletins from the specific group
	 **/
	public function viewbulletins()
	{
		$mainframe  = JFactory::getApplication();
		$document	= JFactory::getDocument();
        $jinput = JFactory::getApplication()->input;
		// Load necessary files
		//CFactory::load( 'models' , 'groups' );
		//CFactory::load( 'helpers' , 'owner' );

		$id			= $jinput->getInt('groupid');
		$my			= CFactory::getUser();

		// Load the group
		$group		= JTable::getInstance( 'Group' , 'CTable' );
		$group->load( $id );

		if( $group->id == 0 )
		{
			echo JText::_('COM_COMMUNITY_GROUPS_ID_NOITEM');
			return;
		}

		//display notice if the user is not a member of the group
		if( $group->approvals == 1 && !($group->isMember($my->id) ) && !COwnerHelper::isCommunityAdmin() )
		{
			echo JText::_('COM_COMMUNITY_GROUPS_PRIVATE_NOTICE');
			return;
		}

		// Set page title
		$document->setTitle( JText::sprintf('COM_COMMUNITY_GROUPS_VIEW_ALL_BULLETINS_TITLE' , $group->name) );

		// Load submenu
		$this->showSubMenu();

		$model			= CFactory::getModel( 'bulletins');
		$bulletins		= $model->getBulletins( $group->id, $mainframe->get('feed_limit') );

		//$jConfig		= JFactory::getConfig();

		// Get the creator of the bulletins
		for( $i = 0; $i < count( $bulletins ); $i++ )
		{
			$row			= $bulletins[ $i ];
			$row->creator	= CFactory::getUser( $row->created_by );
			$date		= JDate::getInstance( $row->date );
			//$date->setTimezone( $jConfig->getValue('offset') );
			$date->setTimezone( $mainframe->get('offset') );

			$item				= new JFeedItem();
			$item->title 		= $row->title;
			$item->link 		= CRoute::_('index.php?option=com_community&view=groups&task=viewbulletin&groupid='. $group->id . '&bulletinid=' . $row->id );
			$item->description 	= $row->message;
			$item->date			= $row->date;

			$item->description = CString::str_ireplace('_QQQ_', '"', $item->description);

			$document->addItem( $item );
		}
	}

	public function viewdiscussions()
	{
		$mainframe  = JFactory::getApplication();
		$document	= JFactory::getDocument();
        $jinput = JFactory::getApplication()->input;
		$id			= $jinput->getInt( 'groupid' , '');
		$my			= CFactory::getUser();

		// Load necessary models, libraries & helpers
		//CFactory::load( 'models' , 'groups' );
		//CFactory::load( 'helpers' , 'owner' );
		$model		= CFactory::getModel( 'discussions' );

		// Load the group
		$group		= JTable::getInstance( 'Group' , 'CTable' );
		$group->load( $id );
		$params		= $group->getParams();

		//check if group is valid
		if( $group->id == 0 )
		{
			echo JText::_('COM_COMMUNITY_GROUPS_ID_NOITEM');
			return;
		}

		// Set page title
		$document->setTitle( JText::sprintf('COM_COMMUNITY_GROUPS_VIEW_ALL_DISCUSSIONS_TITLE' , $group->name) );

		//display notice if the user is not a member of the group
		if( $group->approvals == 1 && !($group->isMember($my->id) ) && !COwnerHelper::isCommunityAdmin() )
		{
			echo JText::_('COM_COMMUNITY_GROUPS_PRIVATE_NOTICE');
			return;
		}

		$discussions	= $model->getDiscussionTopics( $group->id , $mainframe->get('feed_limit') ,  0);

		//$jConfig		= JFactory::getConfig();
		for( $i = 0; $i < count( $discussions ); $i++ )
		{
			$row		= $discussions[$i];
			$row->user	= CFactory::getUser( $row->creator );
			$date		= JDate::getInstance( $row->created );
			//$date->setTimezone( $jConfig->getValue('offset') );
			$date->setTimezone( $mainframe->get('offset') );

			$item				= new JFeedItem();
			$item->title 		= $row->title;
			$item->link 		= CRoute::_('index.php?option=com_community&view=groups&task=viewdiscussion&groupid=' . $group->id . '&topicid=' . $row->id );
			$item->description 	= $row->message;
			$item->date			= $date->format();

			$item->description = CString::str_ireplace('_QQQ_', '"', $item->description);

			$document->addItem( $item );
		}
	}

    private function _viewlatestdiscussions()
    {
        $jinput = JFactory::getApplication()->input;
		$categoryId   = $jinput->getInt( 'categoryid' , 0 );
		$document     = JFactory::getDocument();
		$config       = CFactory::getConfig();

		// getting group's latest discussion activities.
		$model    = CFactory::getModel('groups');
		$rows     =	$model->getGroupLatestDiscussion($categoryId);

		//CFactory::load( 'helpers' , 'string' );

		foreach($rows as $row){

		    $user               = Cfactory::getUser($row->creator);
		    $profileLink        = ltrim( CRoute::_('index.php?option=com_community&view=profile&userid='.$row->creator) ,'/' );

			// load individual item creator class
			$item = new JFeedItem();
			$item->title 		= $row->title;
			$item->link 		= CRoute::_('index.php?option=com_community&view=groups&task=viewdiscussion&groupid='.$row->groupid.'&topicid='.$row->id);
			$item->description  = JText::sprintf('COM_COMMUNITY_GROUPS_DISCUSSION_CREATOR_LINK' , $profileLink , $user->getDisplayName()) . '<br />' . JHTML::_('string.truncate',strip_tags($row->message), $config->getInt('streamcontentlength'));
			$item->date			= $row->lastreplied;

			$item->description = CString::str_ireplace('_QQQ_', '"', $item->description);

			// loads item info into rss array
			$document->addItem( $item );
		}
    }

    public function _viewmylatestdiscussions()
    {
		$mainframe	= JFactory::getApplication();
		$jinput 	= $mainframe->input;

		$categoryId   = $jinput->getInt( 'categoryid' , 0 );
		$groupIds     = $jinput->get('groupids' , 0);
		$document     = JFactory::getDocument();
		$config       = CFactory::getConfig();

		// getting group's latest discussion activities.
		$model    = CFactory::getModel('groups');
		$rows     =	$model->getGroupLatestDiscussion($categoryId,$groupIds);

		//CFactory::load( 'helpers' , 'string' );

		foreach($rows as $row)
		{
			$user               = Cfactory::getUser($row->creator);
			$profileLink        = ltrim( CRoute::_('index.php?option=com_community&view=profile&userid='.$row->creator) ,'/' );

			// load individual item creator class
			$item = new JFeedItem();
			$item->title 		= $row->title;
			$item->link 		= CRoute::_('index.php?option=com_community&view=groups&task=viewdiscussion&groupid='.$row->groupid.'&topicid='.$row->id);
			$item->description  = JText::sprintf('COM_COMMUNITY_GROUPS_DISCUSSION_CREATOR_LINK' , $profileLink , $user->getDisplayName()) . '<br />' . JHTML::_('string.truncate', strip_tags($row->message), $config->getInt('streamcontentlength'));
			$item->date			= $row->lastreplied;

			$item->description = CString::str_ireplace('_QQQ_', '"', $item->description);
			// loads item info into rss array
			$document->addItem( $item );
		}
    }
}