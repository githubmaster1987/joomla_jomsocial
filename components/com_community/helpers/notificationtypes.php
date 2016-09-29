<?php
/**
* @copyright (C) 2013 iJoomla, Inc. - All rights reserved.
* @license GNU General Public License, version 2 (http://www.gnu.org/licenses/gpl-2.0.html)
* @author iJoomla.com <webmaster@ijoomla.com>
* @url https://www.jomsocial.com/license-agreement
* The PHP code portions are distributed under the GPL license. If not otherwise stated, all images, manuals, cascading style sheets, and included JavaScript *are NOT GPL, and are released under the IJOOMLA Proprietary Use License v1.0
* More info at https://www.jomsocial.com/license-agreement
*/

defined('_JEXEC') or die('Restricted access');

require_once( JPATH_ROOT .'/components/com_community/libraries/core.php' );

class CNotificationTypesHelper {
	
	const EMAIL_TYPE_PREFIX	= 'etype_';
	const GLOBAL_TYPE_PREFIX	= 'notif_';
	static public function convertEmailId($id){
		if($id){
			return CNotificationTypesHelper::EMAIL_TYPE_PREFIX . $id ;
		}
		return '';
	}
	static public function convertNotifId($id){
		if($id){
			return CNotificationTypesHelper::GLOBAL_TYPE_PREFIX . $id ;
		}	
		return '';
	}
}