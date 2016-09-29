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
class CommunityControllerBadges extends CommunityController
{
    // defaults

    public function __construct()
    {
        parent::__construct();

        $this->registerTask( 'publish' , 'savePublish' );
        $this->registerTask( 'unpublish' , 'savePublish' );
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

    public function ajaxTogglePublish( $id , $type, $viewName = false )
    {
        CommunityLicenseHelper::_();

        return parent::ajaxTogglePublish( $id , $type , 'badges' );
    }

    public function deleteBadge()
    {
        CommunityLicenseHelper::_();

        $badge   = JTable::getInstance( 'Badges' , 'CommunityTable' );

        $mainframe	= JFactory::getApplication();
        $jinput 	= $mainframe->input;

        $id			= $jinput->post->get('cid' , '', 'NONE');

        if( empty($id) )
        {
            JFactory::getApplication()->enqueueMessage(JText::_('COM_COMMUNITY_INVALID_ID'), 'error');
            return false;
        }

        $deleted = 0;

        foreach($id as $data)
        {
            $badge->load($data );
            $badge->delete($id);
            $deleted++;
        }

        $message = JText::sprintf(JText::_('COM_COMMUNITY_BADGES_DELETED'), $deleted);

        $mainframe	= JFactory::getApplication();
        $mainframe->redirect( 'index.php?option=com_community&view=badges' , $message,'message');
    }

    // single badges / specific

    public function apply()
    {
        CommunityLicenseHelper::_();

        JSession::checkToken() or jexit( JText::_( 'COM_COMMUNITY_INVALID_TOKEN' ) );
        $mainframe	= JFactory::getApplication();
        $badge = $this->store();
        $mainframe->redirect( 'index.php?option=com_community&view=badges&layout=edit&badgeid='.$badge->id, $badge->message, 'message' );
    }

    public function save()
    {
        CommunityLicenseHelper::_();

        JSession::checkToken() or jexit( JText::_( 'COM_COMMUNITY_INVALID_TOKEN' ) );
        $mainframe	= JFactory::getApplication();
        $badge = $this->store();
        $mainframe->redirect( 'index.php?option=com_community&view=badges', $badge->message, 'message' );
    }

    public function store()
    {
        CommunityLicenseHelper::_();

        $mainframe	= JFactory::getApplication();
        $jinput 	= $mainframe->input;

        if( JString::strtoupper($jinput->getMethod()) != 'POST')
        {
            $mainframe->redirect( 'index.php?option=com_community&view=badges' , JText::_( 'COM_COMMUNITY_PERMISSION_DENIED' ) , 'error');
        }

        $badge = JTable::getInstance( 'Badges' , 'CommunityTable' );
        $badge->load($jinput->getInt( 'badgeid' ));

        $badge->title       = $jinput->post->get('title' , '', 'STRING') ;
        $badge->published   = $jinput->post->get('published' , '', 'NONE') ;
        $badge->points       = $jinput->post->get('points' , '', 'INT') ;

        $isNew = $badge->id < 1;

        // handle image upload
        $badgeImage= $jinput->files->get('badge_image' , '', 'NONE');

        if( !empty($badgeImage['tmp_name']) && isset($badgeImage['name']) && !empty($badgeImage['name']) ){

            $imagePath = COMMUNITY_PATH_ASSETS; // same as the image path

            //check the file extension first and only allow jpg or png
            $ext = strtolower(pathinfo($badgeImage['name'], PATHINFO_EXTENSION));

            if(!in_array( $ext, array('jpg','png') ) || ($badgeImage['type'] != 'image/png' && $badgeImage['type'] != 'image/jpeg') ){
                $mainframe->redirect( 'index.php?option=com_community&view=badges&layout=edit&id=' . $badge->id , JText::_('COM_COMMUNITY_BADGES_PARAMETERS_SAVE_ERROR') , 'error' );
            } else {
                $badge->image=$ext;
                $badge->store();
            }

            $finalPath = COMMUNITY_PATH_ASSETS."badge_".$badge->id.".$ext";

            //check if existing image exist, if yes, delete it
            if(file_exists($finalPath)){
                unlink($finalPath);
            }

            //let move the tmp image to the actual path
            move_uploaded_file($badgeImage['tmp_name'],$finalPath);


            $imgHeight = imagesx($finalPath);
            $imgWidth = imagesy($finalPath);

            if($imgHeight >= 256 && $imgWidth >= 256){
                //only resize if the width or height is larger
                require(JPATH_ROOT."/components/com_community/helpers/image.php");
                CImageHelper::resizeProportional($finalPath, $finalPath, "image/$ext", 256, 256);
            }


        }

        $badge->store();
        $badge->message = $isNew ? JText::_( 'COM_COMMUNITY_BADGES_CREATED' ) : JText::_( 'COM_COMMUNITY_BADGES_UPDATED' );
        return $badge;
    }
}