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
class CommunityControllerThemegeneral extends CommunityController
{
    // defaults

    public function __construct()
    {
        parent::__construct();
    }

    public function reset(){
        $defaults = array(
            'scss' => array(
                'scss-style'            => 'boxy',
                'scss-stream-position'  => 'right',
                'scss-button-style'     => 'flat',
                'scss-avatar-shape'     => 'circle',
                'scss-avatar-style'     => 'bordered',
                'scss-direction'        => 'ltr',
            ),
            'settings'=> array(
                'enable-frontpage-login'=> 1,
                'enable-frontpage-image'     => 1,
                'enable-frontpage-paragraph' => 1,
            ),
        );

        $this->apply($defaults);
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

        $model		= $this->getModel( $viewName ,'CommunityAdminModel' );


        if( $model )
        {
            $view->setModel( $model , $viewName );
        }

        // Set the layout
        $view->setLayout( $layout );

        // Display the view
        $view->display();
    }

    /**
     *  Save an existing or a new mood form POST
     */
    public function apply($default = false)
    {
        CommunityLicenseHelper::_();

        JSession::checkToken() or jexit( JText::_( 'COM_COMMUNITY_INVALID_TOKEN' ) );

        $mainframe = JFactory::getApplication();
        $jinput = $mainframe->input;


        if($default){
            $scss = $default['settings'];
            $settings = $default['scss'];
        }else{
            $scss = $jinput->post->get('scss',null,'array');
            $settings = $jinput->post->get('settings',null, 'array');
        }

        if( JString::strtoupper($jinput->getMethod()) != 'POST')
        {
            $mainframe->redirect( 'index.php?option=com_community&view=moods' , JText::_( 'COM_COMMUNITY_PERMISSION_DENIED' ) , 'error');
        }

        if(!is_null($scss)) {
            CommunityThemeHelper::parseScss($scss,'general');
        }

        // Handle image uploads
        $images['frontpage-image'] = $jinput->files->get('frontpage-image-new' , '', 'NONE');


        foreach($images as $key => $image) {

            if (!empty($image['tmp_name']) && isset($image['name']) && !empty($image['name'])) {

                $imagePath = COMMUNITY_PATH_ASSETS; // same as the image path

                //check the file extension first and only allow jpg or png
                $ext = strtolower(pathinfo($image['name'], PATHINFO_EXTENSION));

                if (!in_array($ext, array('jpg', 'png')) || ($image['type'] != 'image/png' && $image['type'] != 'image/jpeg')) {

                }

                $imageJpg = $imagePath . '/' . $key.'.jpg';
                $imagePng = $imagePath . '/' . $key.'.png';

                //check if existing image exist, if yes, delete it
                if (file_exists($imageJpg)) unlink($imageJpg);
                if (file_exists($imagePng)) unlink($imagePng);

                //let move the tmp image to the actual path
                $finalPath = $imagePath . $key . '.' . $ext;
                move_uploaded_file($image['tmp_name'], $finalPath);
                require(JPATH_ROOT."/components/com_community/helpers/image.php");
                CImageHelper::resizeProportional($finalPath, $finalPath, "image/$ext", 1000, 1000);
                $settings[$key]=$ext;
            }
        }

        // Parse settings finish
        CommunityThemeHelper::parseSettings($settings,'general');


        $message = JText::_( 'COM_COMMUNITY_THEME_GENERAL_UPDATED' );
        $mainframe->redirect( 'index.php?option=com_community&view=themegeneral' , $message, 'message' );


        // Get the view type
        $document	= JFactory::getDocument();
        $viewType	= $document->getType();

        // Get the view
        $viewName	= $jinput->get( 'view' , 'community' );
        $view		= $this->getView( $viewName , $viewType );
        $view->setLayout( 'edit' );

        $model		= $this->getModel( $viewName ,'CommunityAdminModel' );

        if( $model )
        {
            $view->setModel( $model , $viewName );
        }

        $view->display();
    }
}