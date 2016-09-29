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
class CommunityControllerMoods extends CommunityController
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

        return parent::ajaxTogglePublish( $id , $type , 'moods' );
    }

    public function ajaxReorder()
    {
        CommunityLicenseHelper::_();

        $message = array('success'=>1);

        $mainframe	= JFactory::getApplication();
        $jinput 	= $mainframe->input;
        $moodids     = $jinput->request->get('cid' , '', 'ARRAY') ;

        $i = 0;

        $mood = JTable::getInstance( 'Moods' , 'CommunityTable' );

        if(sizeof($moodids)){
            foreach($moodids as $moodid) {
                if($mood->load($moodid)) {
                    $mood->ordering = $i++;
                    $mood->store();
                }
            }
        } else {
            $message['success'] = 0;
        }


        echo json_encode($message);
        return;
    }
    public function deleteMood()
    {
        CommunityLicenseHelper::_();

        $mood   = JTable::getInstance( 'Moods' , 'CommunityTable' );

        $mainframe	= JFactory::getApplication();
        $jinput 	= $mainframe->input;

        $id			= $jinput->post->get('cid' , '', 'NONE');

        if( empty($id) )
        {
            JFactory::getApplication()->enqueueMessage(JText::_('COM_COMMUNITY_INVALID_ID'), 'error');
            return false;
        }

        $skipped = 0;
        $deleted = 0;
        foreach($id as $data)
        {
            $mood->load($data );
            if($mood->custom) {
                $mood->delete($id);
                $deleted++;
            } else {
                $skipped++;
            }
        }


        $message = JText::sprintf(JText::_('COM_COMMUNITY_MOODS_DELETED'), $deleted);
        if($skipped) $message.=JText::sprintf(JText::_('COM_COMMUNITY_MOODS_DELETION_SKIPPED'), $skipped);

        $mainframe	= JFactory::getApplication();
        $mainframe->redirect( 'index.php?option=com_community&view=moods' , $message,'message');
    }

    // single moods / specific
    public function apply(){
        CommunityLicenseHelper::_();

        JSession::checkToken() or jexit( JText::_( 'COM_COMMUNITY_INVALID_TOKEN' ) );
        $mainframe	= JFactory::getApplication();
        $mood = $this->store();
        $mainframe->redirect( 'index.php?option=com_community&view=moods&layout=edit&moodid='.$mood->id , $mood->message, 'message' );
    }
    /**
     *  Save an existing or a new mood form POST
     */
    public function save()
    {
        CommunityLicenseHelper::_();

        JSession::checkToken() or jexit( JText::_( 'COM_COMMUNITY_INVALID_TOKEN' ) );
        $mainframe	= JFactory::getApplication();
        $mood = $this->store();
        $mainframe->redirect( 'index.php?option=com_community&view=moods' , $mood->message, 'message' );
    }

    public function store()
    {
        CommunityLicenseHelper::_();

        $mainframe	= JFactory::getApplication();

        $jinput 	= $mainframe->input;

        if( JString::strtoupper($jinput->getMethod()) != 'POST')
        {
            $mainframe->redirect( 'index.php?option=com_community&view=moods' , JText::_( 'COM_COMMUNITY_PERMISSION_DENIED' ) , 'error');
        }

        $mood = JTable::getInstance( 'Moods' , 'CommunityTable' );
        $mood->load($jinput->getInt( 'moodid' ));

        $mood->title        = $jinput->post->get('title' , '', 'STRING') ;
        $mood->published    = $jinput->post->get('published' , '', 'NONE') ;
        $mood->description	= $jinput->post->get('description' , '', 'STRING') ;
        $mood->custom       = 1;

        $isNew = $mood->id < 1;

        // handle image upload
        $moodImage= $jinput->files->get('mood_image' , '', 'NONE');

        if( !empty($moodImage['tmp_name']) && isset($moodImage['name']) && !empty($moodImage['name']) ) {

            $imagePath = COMMUNITY_PATH_ASSETS; // same as the image path

            //check the file extension first and only allow jpg or png
            $ext = strtolower(pathinfo($moodImage['name'], PATHINFO_EXTENSION));

            if(!in_array( $ext, array('jpg','png') ) || ($moodImage['type'] != 'image/png' && $moodImage['type'] != 'image/jpeg') ){
                $mainframe->redirect( 'index.php?option=com_community&view=moods&layout=edit&id=' . $element , JText::_('COM_COMMUNITY_MOODS_ERROR_IMAGE_TYPE') , 'error' );
            }else {
                $mood->image=$ext;
                $mood->store();
            }

            //check if existing image exist, if yes, delete it
            $finalPath = $imagePath.'/mood_'.$mood->id.".".$ext;

            if(file_exists($finalPath)){
                unlink($finalPath);
            }

            //let move the tmp image to the actual path
            move_uploaded_file($moodImage['tmp_name'],$finalPath);

            require(JPATH_ROOT."/components/com_community/helpers/image.php");
            CImageHelper::resizeProportional($finalPath, $finalPath, "image/$ext", 35, 35);

            //add another copy of the mood image, during reinstallation or upgrade, so that the default mood wont be replaced.
            $copyImgPath = $imagePath.'/mood_'.$mood->id."_new.".$ext;
            copy($finalPath, $copyImgPath);
            CImageHelper::resizeProportional($copyImgPath, $copyImgPath, "image/$ext", 35, 35);
        }

        $mood->store();

        $mood->message	= $isNew ? JText::_( 'COM_COMMUNITY_MOODS_CREATED' ) : JText::_( 'COM_COMMUNITY_MOODS_UPDATED' );
        return $mood;
    }
}