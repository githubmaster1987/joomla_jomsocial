<?php
/**
* @copyright (C) 2013 iJoomla, Inc. - All rights reserved.
* @license GNU General Public License, version 2 (http://www.gnu.org/licenses/gpl-2.0.html)
* @author iJoomla.com <webmaster@ijoomla.com>
* @url https://www.jomsocial.com/license-agreement
* The PHP code portions are distributed under the GPL license. If not otherwise stated, all images, manuals, cascading style sheets, and included JavaScript *are NOT GPL, and are released under the IJOOMLA Proprietary Use License v1.0
* More info at https://www.jomsocial.com/license-agreement
*/

defined('_JEXEC') or die();
jimport( 'joomla.application.component.controller' );

class CommunityBookmarksController extends CommunityBaseController
{
	public function ajaxShowBookmarks( $uri, $title, $description, $image )
	{
		$filter	    =	JFilterInput::getInstance();
		$uri	    =	$filter->clean( $uri, 'string' );

		$config		= CFactory::getConfig();
		$shareviaemail	= $config->get( 'shareviaemail' );

		//CFactory::load( 'libraries' , 'bookmarks' );
		$bookmarks	= new CBookmarks( $uri, $title, $description, $image );

        $tmpl = new CTemplate();
        $tmpl
            ->set( 'config', $config )
            ->set( 'bookmarks', $bookmarks->getBookmarks() );

        $json = array(
            'title'     => JText::_('COM_COMMUNITY_SHARE_THIS'),
            'html'      => $tmpl->fetch('bookmarks.list'),
            'btnShare'  => JText::_('COM_COMMUNITY_SHARE_BUTTON'),
            'btnCancel' => JText::_('COM_COMMUNITY_CANCEL_BUTTON'),
            'viaEmail'  => $shareviaemail ? true : false
        );

        die( json_encode($json) );
	}

	public function ajaxEmailPage( $uri , $emails , $message = '' )
	{
		$filter	    =	JFilterInput::getInstance();
		$uri	    =	$filter->clean( $uri, 'string' );
		$emails	    =	$filter->clean( $emails, 'string' );
		$message	    =	$filter->clean( $message, 'string' );

		$message	= stripslashes( $message );
		$mainframe	= JFactory::getApplication();
		$bookmarks	= CFactory::getBookmarks( $uri );
		$mailqModel = CFactory::getModel( 'mailq' );
		$config		= CFactory::getConfig();
		$response	= new JAXResponse();
        $json       = array();

		if ( empty($emails) ) {
            $json['error'] = JText::_('COM_COMMUNITY_SHARE_INVALID_EMAIL');
		} else {
			$emails = explode(',' , $emails);
			$errors = array();

			// Add notification
			//CFactory::load( 'libraries' , 'notification' );

			foreach( $emails as $email )
			{
				$email	= JString::trim($email);

				if(!empty($email) && CValidateHelper::email($email) )
				{
					$params			= new CParameter( '' );
					$params->set('uri' , $uri );
					$params->set('message' , $message );

					CNotificationLibrary::add( 'system_bookmarks_email' , '' , $email , JText::sprintf('COM_COMMUNITY_SHARE_EMAIL_SUBJECT', $config->get('sitename') ) , '' , 'bookmarks' , $params );
				}
				else
				{
					// If there is errors with email, inform the user.
					$errors[]	= $email;
				}
			}

			if ($errors) {
				$content = '<div>' . JText::_('COM_COMMUNITY_EMAILS_ARE_INVALID') . '</div>';
				foreach ($errors as $error) {
					$content .= '<div style="font-weight:bold; color:red;">' . $error . '</div>';
				}

                $json['error'] = $content;

			} else {
				$content = JText::_('COM_COMMUNITY_EMAIL_SENT_TO_RECIPIENTS');
                $json['message'] = $content;
			}

		}

        die( json_encode($json) );
	}
}
