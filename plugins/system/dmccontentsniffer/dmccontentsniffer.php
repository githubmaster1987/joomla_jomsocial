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

jimport('joomla.plugin.plugin');
jimport('joomla.version');

$jVersion = new JVersion();/*
if ($jVersion->RELEASE == '2.5')
{
	jimport('joomla.environment.response');
}
else
{
	jimport('legacy.environment.response');
}*/
jimport('joomla.application.web');

class plgSystemDmccontentsniffer extends JPlugin
{
	public function __construct(&$subject, $config)
	{
		parent::__construct($subject, $config);
	}
	
	function onAfterRender($toArray = false)
	{
		$siteApplication				= JApplication::getInstance('site');
		$app							= JFactory::getApplication();
		$componentParams 				= JComponentHelper::getParams('com_dmcfirewall');
		$testMode						= $componentParams->get('testmode', 0);
		$securityOutput					= $componentParams->get('securitynotice', 1);
		$thresholdLimit					= $componentParams->get('thresholdLimit', 5);
		$snifferBadTerms				= $componentParams->get('snifferBadTerms', 'viagra,cialis,payday,loans,insurance');
		$snifferBadTerms				= explode(',', $snifferBadTerms);
		$contentToAdd					= '';
		$ipAddress						= trim($_SERVER['REMOTE_ADDR']);
		
		/*
		 * If we are in the 'administrator' area, exit
		 */
		if($app->isAdmin())
		{
			return;
		}
 $jVersion = new JVersion();
		
		//die(JApplicationWeb::getBody());
		/* Get the contents of the page */
		$html = '';
		$html							= JResponse::getBody();
		if ($jVersion->RELEASE == '2.5')
		{
			$html						= JResponse::getBody();
		}
		else
		{
			//$html						= JApplicationWeb::getBody();
		}
		//echo ' <!-- ' . $html . ' --> ';
		
		//$snifferNeedles					= array('viagra', 'cialis', 'payday', 'loans', 'insurance');
		
		$snifferNeedles					= implode("|", $snifferBadTerms);
		$snifferNeedles					= '/\b('.$snifferNeedles.')\b/i';
		
		// cycle through each of the bad content words
		$badContentCounter				= preg_match_all($snifferNeedles, $html, $matches);
		
		/*
		 * If we hit the threshold limit set within DMC Firewall, let's convert them into a useable format, increasing a 
		 * counter for every instance and send the data to the helper file so we can send the webmaster an email
		 */
		if ($badContentCounter >= $thresholdLimit)
		{
			$foundArray = array();
			foreach ($matches[1] as $val)
			{
				if (array_key_exists(strtolower($val), $foundArray))
				{
					$foundArray[strtolower($val)] = $foundArray[strtolower($val)] + 1;
				}
				else
				{
					$foundArray[strtolower($val)] = 1;
				}
			}
			
			/*
			 * See if we should send an email to the webmaster of the bad terms that we found, if we can't
			 * send an email yet - add the data to the database for later
			 */
			$db = JFactory::getDBO();
			$db->setQuery("SELECT `last_bad_content_email`, `last_bad_content_pages` FROM `#__dmcfirewall_stats` WHERE `id` = 1");
			$db->execute();
			$badContentData = $db->loadAssoc();
			
			if ($badContentData['last_bad_content_email'] <= time())
			{
				switch ($componentParams->get('emailBadContentTime', 12))
				{
					case 1:
						$emailTime = time() + 3600;
						break;
					case 3:
						$emailTime = time() + (3600 * 3);
						break;
					case 7:
						$emailTime = time() + (3600 * 7);
						break;
					case 12:
						$emailTime = time() + (3600 * 12);
						break;
					case 24:
						$emailTime = time() + (3600 * 24);
						break;
				}
				//we need to get the stored data from the DB
				//$emailBadContentTimer
				$db->setQuery("UPDATE `#__dmcfirewall_stats` SET `last_bad_content_email` = '$emailTime' WHERE `id` = 1");
				$db->execute();
				
				foreach($foundArray as $termFound => $times)
				{
					$timesS = $times > 1 ? 's' : '';
					$contentToAdd .= $termFound . ' (found <strong>' . $times . '</strong> time' . $timesS . ')<br />';
				}
				
				// Let's add the visitors UA to the array so we can add it to the email content
				if ($_SERVER['HTTP_USER_AGENT'])
				{
					$foundArray['ua'] = $_SERVER['HTTP_USER_AGENT'];
				}
				
				$snifferLogData =<<<SNIFFERLOGDATA
				Requested URI: {$_SERVER['REQUEST_URI']}</br >
				Visitors user agent: {$_SERVER['HTTP_USER_AGENT']}<br />
				{$contentToAdd}
SNIFFERLOGDATA;
				
				$db->setQuery("INSERT INTO `#__dmcfirewall_log` (`ip`, `reason`, `additional_information`, `time_date`) 
						VALUES ('$ipAddress', 'Bad Content Sniffer', '$snifferLogData', '" . date('Y-m-d') . ' - ' . date('H:i:s') . "')");
				$result = $db->execute();
				
				require_once JPATH_ADMINISTRATOR . '/components/com_dmcfirewall/helpers/email.php';
				DmcfirewallHelperEmail::send('sniffer', $foundArray);
			}
			else
			{
				//add it to the database for later	
			}
		}
	
		/*
		 * Let's make the webmaster aware that DMC Firewall is running in 'Test Mode' by placing a nice notice at the top of every page
		 */
		if ($testMode)
		{
			$messageOutput = '<div style="position:fixed; text-align:center; border:2px solid rgb(254, 123, 122); background-color:rgb(255, 214, 214); color:rgb(204, 0, 0); box-shadow:1px 1px 0px rgb(239, 246, 255) inset, -1px -1px 0px rgb(239, 246, 255) inset; text-shadow:1px 1px 0px rgb(239, 246, 255); top:0px; margin:0px auto 10px; width:100%; z-index:9999999;"><a target="_blank" href="http://www.webdevelopmentconsultancy.com/joomla-security-tools/dmc-firewall.html">DMC Firewall</a> is currently in \'Test Mode\'!<br>Please ensure that you re-enable DMC Firewall once you have finished testing!<span style="display: block; text-indent: -5555px; height: 0px;"><a target="_blank" href="http://www.webdevelopmentconsultancy.com/joomla-security-tools/dmc-firewall.html">DMC Firewall</a> is a <a href="http://www.webdevelopmentconsultancy.com/joomla-security.html" target="_blank">Joomla Security</a> extension!</span></div>';
			$html = preg_replace('/(<body[^>]*)(.*?)("[>])/is', '$1' . '>' . $messageOutput, $html);
		}

		if ($securityOutput != 2)
		{
			if ($securityOutput == 0)
			{
				$sOutput = " display:none;";
			}
			else
			{
				$sOutput = '';
			}
			$randomNotice = rand(1,3);

			$securityNotice = '<div style="font-size:11px; margin:5px auto 0; clear:both; text-align:center;' . $sOutput . '">';
			switch ($randomNotice)
			{
				case 1:
					$securityNotice .= 'Our website is protected by <a href="http://www.webdevelopmentconsultancy.com/joomla-extensions/dmc-firewall.html" target="_blank">DMC Firewall!</a>';
					break;
				case 2:
					$securityNotice .= '<a href="http://www.webdevelopmentconsultancy.com/joomla-extensions/dmc-firewall.html" target="_blank">DMC Firewall</a> is a <a href="http://www.webdevelopmentconsultancy.com/joomla-security.html" target="_blank">Joomla Security</a> extension!';
					break;
				case 3:
					$securityNotice .= '<a href="http://www.webdevelopmentconsultancy.com/joomla-extensions/dmc-firewall.html" target="_blank">DMC Firewall</a> is developed by <a href="http://www.deanmarshall.co.uk/" target="_blank">Dean Marshall Consultancy Ltd</a>';
					break;
			}
			
			$securityNotice .= "</div>\n</body>";
			
			$html = preg_replace('@<\/body>@i', $securityNotice, $html, 1);
		}
		
		JResponse::setBody($html);
	}
}