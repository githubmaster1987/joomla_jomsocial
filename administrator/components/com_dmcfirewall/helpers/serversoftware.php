<?php
/**
 * @Package			DMC Firewall
 * @Copyright		Dean Marshall Consultancy Ltd
 * @license			http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * Email			software@deanmarshall.co.uk
 * web:				http://www.deanmarshall.co.uk/
 * web:				http://www.webdevelopmentconsultancy.com/
 */

defined('_JEXEC') or die('Direct access forbidden!');

jimport('joomla.application.component.helper');

class DmcfirewallHelperServersoftware {
	
	/**
	 *
	 */
	public static function serverSoftware(){
		$system = '';
		
		if(isset($_SERVER['SERVER_SOFTWARE'])){
			$serverSoftware = $_SERVER['SERVER_SOFTWARE'];
		}else{
			$serverSoftware = getenv('SERVER_SOFTWARE');
		}
		
		if(stripos($serverSoftware, 'IIS') !== FALSE){
			$system = 'IIS';
		}elseif(stripos($serverSoftware, 'Apache') !== FALSE || stripos($serverSoftware, 'Zeus') !== FALSE || stripos($serverSoftware, 'LiteSpeed') !== FALSE || stripos($serverSoftware, 'nginx') !== FALSE){
			$system = 'APA';
		}else{
			$system = 'NA';
		}
		
		return $system;
	}
}
