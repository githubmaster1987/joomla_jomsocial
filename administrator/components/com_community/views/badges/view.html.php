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
class CommunityViewBadges extends JViewLegacy
{
    public function display( $tpl = null )
    {
        if( $this->getLayout() == 'edit' )
        {
            $this->_displayEditLayout( $tpl );
            return;
        }

        // Set the titlebar text
        JToolBarHelper::title( JText::_('COM_COMMUNITY_CONFIGURATION_BADGES'), 'badges');

        // Add the necessary buttons
        JToolBarHelper::addNew( 'newBadge' , JText::_('COM_COMMUNITY_BADGES_NEW_BADGE') );
        JToolBarHelper::deleteList( JText::_('COM_COMMUNITY_BADGES_DELETION_WARNING') , 'deleteBadge' , JText::_('COM_COMMUNITY_DELETE') );
        JToolBarHelper::divider();
        JToolBarHelper::publishList( 'publish' , JText::_('COM_COMMUNITY_PUBLISH') );
        JToolBarHelper::unpublishList( 'unpublish' , JText::_('COM_COMMUNITY_UNPUBLISH') );

        // Get badges
        $badgesTable = JTable::getInstance( 'Badges' , 'CommunityTable' );
        $this->set('badges', $this->prepare($badgesTable->getBadges()));

        $mainframe	= JFactory::getApplication();
        $filter_order		= $mainframe->getUserStateFromRequest( "com_community.badges.filter_order",		'filter_order',		'a.points',	'cmd' );
        $filter_order_Dir	= $mainframe->getUserStateFromRequest( "com_community.badges.filter_order_Dir",	'filter_order_Dir',	'',			'word' );

        // table ordering
        $lists['order_Dir']	= $filter_order_Dir;
        $lists['order']		= $filter_order;

        $this->set('lists', $lists);

        parent::display($tpl);
    }

    public function _displayEditLayout( $tpl )
    {
        // Load frontend language file.
        $lang	= JFactory::getLanguage();
        $lang->load( 'com_community' , JPATH_ROOT );
        $jinput = JFactory::getApplication()->input;

        // Add the necessary buttons
        JToolBarHelper::back('Back' , 'index.php?option=com_community&view=badges');
        JToolBarHelper::divider();
        JToolBarHelper::apply();
        JToolBarHelper::save();

        $badge = JTable::getInstance( 'Badges' , 'CommunityTable' );

        $badge->load( $jinput->getInt( 'badgeid') );

        // Set the titlebar text
        JToolBarHelper::title( JText::_('COM_COMMUNITY_BADGES_ADD'), 'badges');
        if($badge->id) {
            JToolBarHelper::title( JText::_('COM_COMMUNITY_BADGES_EDIT'), 'badges');
            $badge->image = $this->getImage($badge);
        }

        $post	= $jinput->post->getArray();
        $badge->bind( $post );

        $this->set( 'badge'	, $badge);

        $badgesTable = JTable::getInstance( 'Badges' , 'CommunityTable' );
        $this->set('badges', $this->prepare($badgesTable->getBadges()));

        parent::display( $tpl );
    }

    public function getImage($badge) {

        $filename = "badge_".$badge->id.".".$badge->image;

        if(file_exists(COMMUNITY_PATH_ASSETS.$filename))
        {
            return JUri::root().str_replace(JPATH_ROOT,'',COMMUNITY_PATH_ASSETS).$filename;
        }

        return null;
    }

    /**
     * Method to get the publish status HTML
     *
     * @param	object	Field object
     * @param	string	Type of the field
     * @param	string	The ajax task that it should call
     * @return	string	HTML source
     **/
    public function getPublish( &$row , $type , $ajaxTask )
    {

        $version = new Jversion();
        $currentV = $version->getHelpVersion();

        $class = 'jgrid';

        $alt	= $row->$type ? JText::_('COM_COMMUNITY_PUBLISHED') : JText::_('COM_COMMUNITY_UNPUBLISH');
        $state = $row->$type == 1 ? 'publish' : 'unpublish';
        $span = '<span class="state '.$state.'"><span class="text">'.$alt.'</span></span></a>';

        if($currentV >= '0.30')
        {
            $class = $row->$type == 1 ? 'jgrid': '';

            $span = '<i class="icon-'.$state.'""></i>';
        }

        $href = '<a class="'.$class.'" href="javascript:void(0);" onclick="azcommunity.togglePublish(\'' . $ajaxTask . '\',\'' . $row->id . '\',\'' . $type . '\');">';

        $href .= $span;

        return $href;
    }

    /**
     * Loop through badge array and apply translations and image URLs to titles and descriptions
     *
     * @param $badges
     * @return mixed
     */
    private function prepare($badges) {
        // @todo maybe the model should handle the translations?
        $lang	= JFactory::getLanguage();
        $lang->load( 'com_community' , JPATH_ROOT );

        foreach($badges as $id=>$badge){
                $badges[$id]->title     =   JText::_($badge->title);
                $badges[$id]->image      =   $this->getImage($badges[$id]);
        }

        return $badges;
    }
}