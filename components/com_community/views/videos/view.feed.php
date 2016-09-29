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
include_once (COMMUNITY_COM_PATH.'/views/videos/view.php');

class CommunityViewVideos extends CommunityView
{
	var $_videoLib	= null;
	var $model		= '';
	
	public function __construct()
	{
		$this->model	= CFactory::getModel('videos');
		$this->videoLib	= new CVideoLibrary();
	}

	public function display($data = null)
	{
		$mainframe	= JFactory::getApplication();
		$document	= JFactory::getDocument();
        $jinput = JFactory::getApplication()->input;
		
		$my			= CFactory::getUser();
		$userid		= $jinput->get( 'userid' , '' );
		$groupId	= $jinput->get( 'groupid', '' );
		
		if( !empty($userid) ){  
			$user		= CFactory::getUser($userid);
			
			// Set document title
			//CFactory::load( 'helpers' , 'owner' );
			$blocked	= $user->isBlocked();
			
			if( $blocked && !COwnerHelper::isCommunityAdmin() )
			{
				$tmpl	= new CTemplate();
				echo $tmpl->fetch('profile.blocked');
				return;
			}
		
			if($my->id == $user->id){
				$title	= JText::_('COM_COMMUNITY_VIDEOS_MY');
			}else{
				$title	= JText::sprintf('COM_COMMUNITY_VIDEOS_USERS_VIDEO_TITLE', $user->getDisplayName());
			}
			
		}else{
				$title  = JText::_('COM_COMMUNITY_VIDEOS_ALL_DESC');
		}
		
		
		// list user videos or group videos
		if( !empty($groupId) ){   
			$title		= JText::_('COM_COMMUNITY_SUBSCRIBE_TO_GROUP_VIDEOS_FEEDS');
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
			
			$tmpVideos	= $this->model->getGroupVideos( $groupId, '', $mainframe->get('feed_limit') );
			$videos		= array();
			foreach ($tmpVideos as $videoEntry)
			{
				$video	= JTable::getInstance('Video','CTable');
				$video->bind( $videoEntry );
				$videos[]	= $video;
			}
			
		}else{
		
			$filters		= array
			(
				'creator'	=> $userid,
				'status'	=> 'ready',
				'groupid'	=> 0,
				'limit'		=> $mainframe->get('feed_limit'),
				'limitstart'=> 0,
				'sorting'	=> $jinput->get('sort', 'latest')
			);
			
			// list all user videos & all group videos
			if( empty($userid) ){
					unset($filters['creator']); 
					unset($filters['groupid']);
			}
			
			$videos = array();
			$tmpVideos = $this->model->getVideos($filters, true);
			
			foreach ($tmpVideos as $videoEntry)
			{
				$video	= JTable::getInstance('Video','CTable');
				$video->bind( $videoEntry );
				$videos[]	= $video;
			}
		}
		
		$videosCount	= count($videos);
		$feedLimit		= $mainframe->get('feed_limit');
		$limit			= ($videosCount < $feedLimit) ? $videosCount : $feedLimit;
		
		// Prepare feeds
		$document->setTitle($title);
		
		for($i = 0; $i < $limit; $i++)
		{
			$video = $videos[$i];
			
			$item = new JFeedItem();
			$item->title 		= $video->getTitle();
			$item->link 		= $video->getURL();
			$item->description 	= '<img src="' . $video->getThumbnail() . '" alt="" />&nbsp;'.$video->getDescription();
			$item->date			= $video->created;
			$item->author		= $video->getCreatorName();

			$item->description = CString::str_ireplace('_QQQ_', '"', $item->description);
			
			if( !empty($video->id) )
				$document->addItem( $item );
		}

	}
	
	public function myvideos(){
		return $this->display();
	}
	
}
