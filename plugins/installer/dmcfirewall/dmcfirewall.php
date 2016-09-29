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

class plgInstallerDmcfirewall extends JPlugin
{
	private $extension = 'com_dmcfirewall';

	/**
	 * Handle adding credentials to package download request
	 *
	 * @param   string  $url        url from which package is going to be downloaded
	 * @param   array   $headers    headers to be sent along the download request (key => value format)
	 *
	 * @return  boolean true if credentials have been added to request or not our business, false otherwise (credentials not set by user)
	 *
	 * @since   2.5
	 */
	public function onInstallerBeforePackageDownload(&$url, &$headers)
	{
		$uri = JUri::getInstance($url);
		$host = $uri->getHost();
		
		if (!in_array($host, array('www.webdevelopmentconsultancy.com', 'www.dmc-svn.com')))
		{
			return true;
		}

		// Get the download ID
		JLoader::import('joomla.application.component.helper');
		$component = JComponentHelper::getComponent($this->extension);

		$dlid = $component->params->get('dlid', '');
		
		// If the download ID is invalid, return without any further action
		if (!preg_match('/^([0-9]{1,}:)?[0-9a-f]{32}$/i', trim($dlid)))
		{
			return true;
		}

		// Append the Download ID to the download URL
		if (!empty($dlid))
		{
			$uri->setVar('dlid', $dlid);
			$url = $uri->toString();
		}
		
		return true;
	}
}
