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

jimport('joomla.application.component.model');

if(defined('PHP_VERSION')){
	$version = PHP_VERSION;
}elseif (function_exists('phpversion')){
	$version = phpversion();
}else{
	// No version info
	$version = '5.2.0';
}

if(!version_compare($version, '5.3.0', '>=')){
	return JError::raise(E_ERROR, 500, 'Your server needs to be running PHP 5.3.x or greater in order to use DMC Firewall!<br />Please upgrade your PHP version - you may be able to do this within your Hosting Control panel!');
}

// Load FOF
include_once JPATH_LIBRARIES . '/fof/include.php';
if(!defined('FOF_INCLUDED')){
	JError::raiseError ('500', 'Error: FOF library not found!<br />Please try re-installing DMC Firewall to fix this issue!');
}

FOFDispatcher::getTmpInstance('com_dmcfirewall')->dispatch();
