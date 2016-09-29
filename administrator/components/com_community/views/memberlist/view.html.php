<?php
/**
* @copyright (C) 2013 iJoomla, Inc. - All rights reserved.
* @license GNU General Public License, version 2 (http://www.gnu.org/licenses/gpl-2.0.html)
* @author iJoomla.com <webmaster@ijoomla.com>
* @url https://www.jomsocial.com/license-agreement
* The PHP code portions are distributed under the GPL license. If not otherwise stated, all images, manuals, cascading style sheets, and included JavaScript *are NOT GPL, and are released under the IJOOMLA Proprietary Use License v1.0
* More info at https://www.jomsocial.com/license-agreement
*/

// Disallow direct access to this file
defined('_JEXEC') or die('Restricted access');

jimport( 'joomla.application.component.view' );

/**
 * Configuration view for JomSocial
 */
class CommunityViewMemberlist extends JViewLegacy
{
	public function display( $tmpl = null )
	{
		$mainframe	= JFactory::getApplication();
		$jinput 	= $mainframe->input;
		$memberlist	= $this->get( 'MemberList' );
		$pagination	= $this->get( 'Pagination' );

		$ordering		= $mainframe->getUserStateFromRequest( "com_community.memberlist.filter_order",		'filter_order',		'a.title',	'cmd' );
		$orderDirection	= $mainframe->getUserStateFromRequest( "com_community.memberlist.filter_order_Dir",	'filter_order_Dir',	'',			'word' );
		$search			= $mainframe->getUserStateFromRequest( "com_community.memberlist.search", 'search', '', 'string' );
		$object			= $jinput->get( 'object' , '' , 'NONE');
		$requestType	= $jinput->get( 'tmpl' , NULL, 'NONE');

		$this->set( 'requestType'	, $requestType );
		$this->set( 'object'		, $object );
		$this->set( 'memberlist'	, $memberlist );
		$this->set( 'ordering'	, $ordering );
		$this->set( 'orderDirection'	, $orderDirection );
		$this->set( 'memberlist'	, $memberlist );
		$this->set( 'pagination'	, $pagination );
		parent::display( $tmpl );
	}

	public function setToolBar()
	{
		// Set the titlebar text
		JToolBarHelper::title( JText::_('COM_COMMUNITY_MEMBERLIST'), 'memberlist');

		// Add the necessary buttons
		JToolBarHelper::deleteList( JText::_('COM_COMMUNITY_MEMBERLIST_DELETION_WARNING') , 'delete' , JText::_('COM_COMMUNITY_DELETE') );
	}
}