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

class DmcfirewallHelperIp
{
	public static function getIP()
	{
		if (array_key_exists('HTTP_X_FORWARDED_FOR', $_SERVER))
		{
			return trim($_SERVER['HTTP_X_FORWARDED_FOR']);
		}
		elseif (array_key_exists('REMOTE_ADDR', $_SERVER))
		{
			return trim($_SERVER['REMOTE_ADDR']);
		}
		elseif (array_key_exists('HTTP_CLIENT_IP', $_SERVER))
		{
			return trim($_SERVER['HTTP_CLIENT_IP']);
		}
		else
		{
			return 'unknown';
		}
	}
}	