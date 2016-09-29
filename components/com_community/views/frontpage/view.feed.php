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

class CommunityViewFrontpage extends CommunityView
{
	public function display($data = null)
	{
		$mainframe	= JFactory::getApplication();
		$config		= CFactory::getConfig();
		$document	= JFactory::getDocument();
		$document->setTitle( JText::sprintf('COM_COMMUNITY_FRONTPAGE_TITLE', $config->get('sitename')));
		$document->setLink(CRoute::_('index.php?option=com_community'));
		$my         = CFactory::getUser();
		
		//CFactory::load( 'libraries' , 'activities' );
		//CFactory::load( 'helpers' , 'string' );
		//CFactory::load( 'helpers' , 'time' );

		$act    = new CActivityStream();
		$rows	= $act->getFEED('', '', null, $mainframe->get('feed_limit'));

		if ( $config->get('showactivitystream') == COMMUNITY_SHOW 
			|| $config->get('showactivitystream') == COMMUNITY_MEMBERS_ONLY 
			&& $my->id != 0 
		)
		{
			foreach($rows->data as $row)
			{
				if($row->type != 'title')
				{
					// load individual item creator class
					$item = new JFeedItem(); 
					// cannot escape the title. it's already formated. we should 
					// escape it during CActivityStream::add 
					//$item->title 		= CStringHelper::escape($row->title);
					$item->title 		= $row->title;
					$item->link 		= CRoute::_('index.php?option=com_community&view=profile&userid='.$row->actor);
					$item->description 	= "<img src=\"{$row->favicon}\" alt=\"\"/>&nbsp;".$row->title;
					$item->date			= CTimeHelper::getDate($row->createdDateRaw)->toRFC822();
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
}