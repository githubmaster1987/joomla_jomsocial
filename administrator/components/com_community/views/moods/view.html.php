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
class CommunityViewMoods extends JViewLegacy
{
    public function display( $tpl = null )
    {
        if( $this->getLayout() == 'edit' )
        {
            $this->_displayEditLayout( $tpl );
            return;
        }

        // Set the titlebar text
        JToolBarHelper::title( JText::_('COM_COMMUNITY_CONFIGURATION_MOODS'), 'moods');

        // Add the necessary buttons
        JToolBarHelper::addNew( 'newMood' , JText::_('COM_COMMUNITY_MOODS_NEW_MOOD') );
        JToolBarHelper::deleteList( JText::_('COM_COMMUNITY_MOODS_DELETION_WARNING') , 'deleteMood' , JText::_('COM_COMMUNITY_DELETE') );
        JToolBarHelper::divider();
        JToolBarHelper::publishList( 'publish' , JText::_('COM_COMMUNITY_PUBLISH') );
        JToolBarHelper::unpublishList( 'unpublish' , JText::_('COM_COMMUNITY_UNPUBLISH') );

        // Get Moods by type (preset & custom)
        $moodsTable = JTable::getInstance( 'Moods' , 'CommunityTable' );
        $this->set('moods', $this->prepare($moodsTable->getMoods()));

        $mainframe	= JFactory::getApplication();
        $filter_order		= $mainframe->getUserStateFromRequest( "com_community.moods.filter_order",		'filter_order',		'a.title',	'cmd' );
        $filter_order_Dir	= $mainframe->getUserStateFromRequest( "com_community.moods.filter_order_Dir",	'filter_order_Dir',	'',			'word' );

        // table ordering
        $lists['order_Dir']	= $filter_order_Dir;
        $lists['order']		= $filter_order;

        $this->set('lists', $lists);

        parent::display( $tpl );
    }

    public function _displayEditLayout( $tpl )
    {
        // Load frontend language file.
        $lang	= JFactory::getLanguage();
        $lang->load( 'com_community' , JPATH_ROOT );
        $jinput = JFactory::getApplication()->input;

        // Add the necessary buttons
        JToolBarHelper::back('Back' , 'index.php?option=com_community&view=moods');
        JToolBarHelper::divider();
        JToolBarHelper::apply();
        JToolBarHelper::save();


        $mood = JTable::getInstance( 'Moods' , 'CommunityTable' );

        $mood->load( $jinput->getInt( 'moodid') );

        // Set the titlebar text
        JToolBarHelper::title( JText::_('COM_COMMUNITY_MOODS_ADD'), 'moods');
        if($mood->id) {
            JToolBarHelper::title( JText::_('COM_COMMUNITY_MOODS_EDIT'), 'moods');
            $mood->image = $this->getImage($mood);
        }

        $post	= $jinput->post->getArray();
        $mood->bind( $post );

        $this->set( 'mood'	, $mood);

        parent::display( $tpl );
    }

    public function getImage($mood) {

        if($mood->custom) {

            $filename = "mood_".$mood->id.".".$mood->image;

            if(file_exists(COMMUNITY_PATH_ASSETS.$filename))
            {
                return JUri::root().str_replace(JPATH_ROOT,'',COMMUNITY_PATH_ASSETS).$filename;
            }

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
     * Loop through mood array and apply translations and image URLs to titles and descriptions
     *
     * @param $moods
     * @return mixed
     */
    private function prepare($moods) {
        // @todo maybe the model should handle the translations?
        $lang	= JFactory::getLanguage();
        $lang->load( 'com_community' , JPATH_ROOT );

        foreach($moods as $id=>$mood){
            if(!$mood->custom) {
                $moods[$id]->title       =   JText::_('COM_COMMUNITY_MOOD_SHORT_'.$mood->title);
            } else {
                $moods[$id]->title       =   JText::_($mood->title);
                $moods[$id]->image       =   $this->getImage($mood);
            }


            $moods[$id]->description =   JText::_($mood->description);

        }

        return $moods;
    }
}