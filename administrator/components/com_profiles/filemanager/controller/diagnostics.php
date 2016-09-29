<?php
/**
 * @package		Profiles
 * @subpackage	filemanger
 * @copyright	Copyright (C) 2013 - 2013 Mad4Media - Dipl. Informatiker(FH) Fahrettin Kutyol. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 * @license		Libraries can be under a different license in other environments
 * @license		Media files owned and created by Mad4Media such as 
 * @license 	Javascript / CSS / Shockwave or Images are licensed under GFML (GPL Friendly Media License). See GFML.txt.
 * @license		3rd party scripts are under the license of the copyright holder. See source header or license text file which is included in the appropriate folders
 * @version		1.0
 * @link		http://www.mad4media.de
 * Creation date 2013/02
 */

//CUSTOMPLACEHOLDER
//CUSTOMPLACEHOLDER2

defined('_JEXEC') or die;

require_once _FM_HOME_DIR .DS . 'includes'.DS.'diaghelper.php';

class diagnostics extends MTask{
	
	public function _default(){
		if(_FM_IS_DEMO){
			return $this->view->add2Content('<div style="display:block; padding: 10px; font-size: 16px; font-weight: bold;">' . MText::_("notfordemo") . '</div>' );
		}
		
		
		$this->includeLanguage();
		
		MDiagHelper::start();
		
		$this->view->add2Menu("");
		$this->view->add2Content(	MDiagHelper::generate() );
		MDiagHelper::save();
// 		$this->view->addPreToContent(MDiagHelper::getTests());
	}//EOF _default
	
	

	
	
}


	



?>