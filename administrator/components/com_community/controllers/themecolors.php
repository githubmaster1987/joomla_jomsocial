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

jimport( 'joomla.application.component.controller' );

/**
 * JomSocial Component Controller
 */
class CommunityControllerThemecolors extends CommunityController
{
    // defaults

    public function __construct()
    {
        parent::__construct();
    }

    public function display( $cachable = false, $urlparams = array() )
    {
        CommunityLicenseHelper::_();
        $jinput = JFactory::getApplication()->input;

        $viewName	= $jinput->get( 'view' , 'community' );

        // Set the default layout and view name
        $layout		= $jinput->get( 'layout' , 'default' );

        // Get the document object
        $document	= JFactory::getDocument();

        // Get the view type
        $viewType	= $document->getType();

        // Get the view
        $view		= $this->getView( $viewName , $viewType );

        // Set the layout
        $view->setLayout( $layout );

        // Display the view
        $view->display();
    }

    /**
     *  Save an existing or a new mood form POST
     */
    public function apply()
    {
        CommunityLicenseHelper::_();

        JSession::checkToken() or jexit( JText::_( 'COM_COMMUNITY_INVALID_TOKEN' ) );

        $mainframe = JFactory::getApplication();
        $jinput = $mainframe->input;

        $scss = $jinput->post->get('scss',null,'array');


        if( JString::strtoupper($jinput->getMethod()) != 'POST')
        {
            $mainframe->redirect( 'index.php?option=com_community&view=moods' , JText::_( 'COM_COMMUNITY_PERMISSION_DENIED' ) , 'error');
        }

        if(!is_null($scss)) {
            CommunityThemeHelper::parseScss($scss, 'colors');
        }

        $message = JText::_( 'COM_COMMUNITY_THEME_COLORS_UPDATED' );
        $mainframe->redirect( 'index.php?option=com_community&view=themecolors' , $message, 'message' );
    }
}