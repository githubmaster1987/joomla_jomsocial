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

require_once( JPATH_ROOT . '/components/com_community/libraries/core.php' );

/**
 * JomSocial Component Controller
 */
class CommunityControllerDigest extends CommunityController
{
	public function __construct()
	{
		parent::__construct();

        $jinput = JFactory::getApplication()->input;
		$save = $jinput->post->getArray();

		if(isset($save['task']) && $save['task'] == 'digest.apply'){
			$this->save($save);
		}

	}

    public function ajaxGetPreview($userid, $days){
		// Load frontend language file.
		$lang   = JFactory::getLanguage();
		$lang->load( 'com_community' , JPATH_ROOT );

        $cron = new CCron();
        $content = $cron->processDigestMail(false, true, $userid, $days);

		if(!$content){
			$content = JText::_('COM_COMMUNITY_DIGEST_USER_ACTIVE_NOTICE');
		}elseif($content===true){
			$content = JText::_('COM_COMMUNITY_DIGEST_NO_NEW_DATA_NOTICE');
		}

        $response = new JAXResponse();
        $response->addAssign( 'cWindowContent' , 'innerHTML' , $content );
        $response->addAssign( 'cwin_logo' , 'innerHTML' , JText::_('COM_COMMUNITY_DIGEST_PREVIEW') );
        $response->addScriptCall( 'cWindowActions' , '' );
        return $response->sendResponse();
    }

	public function testDisplay(){
		// Load frontend language file.
		$lang   = JFactory::getLanguage();
		$lang->load( 'com_community' , JPATH_ROOT );

		$cron = new CCron();
		$cron->processDigestMail(true);
		die;
	}

	public function display($cachable = false, $urlparams = array()){
        $jinput = JFactory::getApplication()->input;
        // Set the default layout and view name
        $layout  = $jinput->get( 'layout' , 'default' );
        $viewName = $jinput->get( 'view' , 'community' );

        // Get the document object
        $document	= JFactory::getDocument();

        // Get the view type
        $viewType	= $document->getType();

        // Get the view
        $view		= $this->getView( $viewName , $viewType );
        $model		= $this->getModel( $viewName );

        if( $model )
        {
            $view->setModel( $model , $viewName );
        }

        // Set the layout
        $view->setLayout( $layout );

        // Display the view
        $view->display();

        // Display Toolbar. View must have setToolBar method
        if( method_exists( $view , 'setToolBar') )
        {
            $view->setToolBar();
        }
	}

	/**
	 * @param $save POST data
	 */
	private function save($save)
	{
		$model	= $this->getModel( 'Configuration' );

		// Try to save configurations
		if( $model->save()){
			$message	= JText::_('COM_COMMUNITY_CONFIGURATION_UPDATED');
			$mainframe	= JFactory::getApplication();

			// Try to save network configurations
			if( $model->save() )
			{
				$mainframe->redirect( 'index.php?option=com_community&view=digest', $message, 'message' );
			}
			else
			{
				JFactory::getApplication()->enqueueMessage(JText::_('COM_COMMUNITY_CONFIGURATION_NETWORK_SAVE_FAIL'), 'error');
			}
		}
	}

}
