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

jimport( 'joomla.plugin.plugin' );
jimport( 'joomla.filesystem.file' );

// We need to require our helper file
require_once JPATH_ADMINISTRATOR . '/components/com_dmcfirewall/helpers/block.php';

class plgSystemDmcfirewall extends JPlugin
{
	function onAfterInitialise()
	{
	/*
	 * DMC Firewall - Variables
	 */
		$app						= JFactory::getApplication();
		$ipAddress					= trim($_SERVER['REMOTE_ADDR']);
		$queryString				= $_SERVER['QUERY_STRING'];
		$userAgent					= $_SERVER['HTTP_USER_AGENT'];
		$requestURI					= $_SERVER['REQUEST_URI'];
		$dmcfirewallactivate		= false;
		$componentParams			= JComponentHelper::getParams('com_dmcfirewall');
		$lookUpResult				= gethostbyaddr($ipAddress);
		$goodBots					= array('google', 'msn', 'yahoo');
		
		$testMode					= $componentParams->get('testmode', 0);
		$whiteListedIPs				= $componentParams->get('whitelistIPs', '');
        $whiteListedIPs				= explode(',', $whiteListedIPs);	
		
		// We don't want to run in the administrator area
		if ($app->isAdmin())
		{
			return;
		}
		
        /*
         * If our IP address has been Whitelisted, skip processing
         */
        if (in_array($ipAddress, $whiteListedIPs))
		{
			return;
		}
		
		// cycle through all of the 'Good Bots' and turn them into individual entries
		foreach ($goodBots as $goodBot)
		{
			$isReallyGoodBot = stripos($lookUpResult, $goodBot);
			if ($isReallyGoodBot)
			{
				break;
			}
		}
		
		$botArray 						= array();
		/*
		 * The below bots are in the CORE release as well as PRO
		 */
		$componentParams->get('80legs', 0) == 0 ? 					$botArray[] = '80legs' : '';
		$componentParams->get('baiduspider', 0) == 0 ? 				$botArray[] = 'Baiduspider' : '';
		$componentParams->get('screamingfrogseospider', 0) == 0 ? 	$botArray[] = 'Screaming\ Frog\ SEO\ Spider' : '';
		$componentParams->get('verticalpigeon', 0) == 0 ? 			$botArray[] = 'verticalpigeon.com' : '';
		$componentParams->get('wget', 0) == 0 ? 					$botArray[] = 'Wget' : '';
		$componentParams->get('nutch', 0) == 0 ? 					$botArray[] = 'Nutch' : '';
		$componentParams->get('botforjce', 0) == 0 ? 				$botArray[] = 'BOT for JCE' : '';
		$componentParams->get('mozilla40', 0) == 0 ? 				$botArray[] = '^Mozilla\/4.0$' : '';

		/*
		 * The below bots are PRO ONLY!
		 */
		$componentParams->get('acoonbot', 0) == 0 ? 				$botArray[] = 'AcoonBot' : '';
		$componentParams->get('ahrefsbot', 0) == 0 ? 				$botArray[] = 'AhrefsBot' : '';
		$componentParams->get('analyticsseo', 0) == 0 ? 			$botArray[] = 'analyticsseo' : '';
		$componentParams->get('avantbrowser', 0) == 0 ? 			$botArray[] = 'Avant\ Browser' : '';
		$componentParams->get('avantbrowsercom', 0) == 0 ? 			$botArray[] = 'avantbrowser.com' : '';
		$componentParams->get('babyadiscoverer', 0) == 0 ? 			$botArray[] = 'Babya Discoverer' : '';
		$componentParams->get('blackwidow', 0) == 0 ? 				$botArray[] = 'blackwidow' : '';
		$componentParams->get('blekkobot', 0) == 0 ? 				$botArray[] = 'Blekkobot' : '';
		$componentParams->get('blexbot', 0) == 0 ? 					$botArray[] = 'BLEXBot' : '';
		$componentParams->get('blpbbot', 0) == 0 ? 					$botArray[] = 'BLP_bbot' : '';
		$componentParams->get('changedetectioncom', 0) == 0 ? 		$botArray[] = 'changedetection.com' : '';
		$componentParams->get('checkspanoptacom', 0) == 0 ? 		$botArray[] = 'checks.panopta.com' : '';
		$componentParams->get('chlooecom', 0) == 0 ? 				$botArray[] = 'chlooe.com' : '';
		$componentParams->get('coccoc', 0) == 0 ? 					$botArray[] = 'coccoc' : '';
		$componentParams->get('chinaclaw', 0) == 0 ? 				$botArray[] = 'ChinaClaw' : '';
		$componentParams->get('craftbot', 0) == 0 ? 				$botArray[] = 'Bot\ mailto:craftbot@yahoo.com' : '';
		$componentParams->get('custo', 0) == 0 ? 					$botArray[] = 'Custo' : '';
		$componentParams->get('digitalalphaserver', 0) == 0 ? 		$botArray[] = 'Digital\ AlphaServer' : '';
		$componentParams->get('deepnetexplorer', 0) == 0 ? 			$botArray[] = 'Deepnet\ Explorer' : '';
		$componentParams->get('disco', 0) == 0 ? 					$botArray[] = 'DISCo' : '';
		$componentParams->get('donkeybot', 0) == 0 ? 				$botArray[] = 'DonkeyBot' : '';
		$componentParams->get('digext', 0) == 0 ? 					$botArray[] = 'DigExt' : '';
		$componentParams->get('downloaddemon', 0) == 0 ? 			$botArray[] = 'Download\ Demon' : '';
		$componentParams->get('ecatch', 0) == 0 ? 					$botArray[] = 'eCatch' : '';
		$componentParams->get('eirgrabber', 0) == 0 ? 				$botArray[] = 'EirGrabber' : '';
		$componentParams->get('emailsiphon', 0) == 0 ? 				$botArray[] = 'EmailSiphon' : '';
		$componentParams->get('emailwolf', 0) == 0 ? 				$botArray[] = 'EmailWolf' : '';
		$componentParams->get('expresswebpictures', 0) == 0 ? 		$botArray[] = 'Express\ WebPictures' : '';
		$componentParams->get('exabot', 0) == 0 ? 					$botArray[] = 'Exabot' : '';
		$componentParams->get('extractorpro', 0) == 0 ? 			$botArray[] = 'ExtractorPro' : '';
		$componentParams->get('eyenetie', 0) == 0 ? 				$botArray[] = 'EyeNetIE' : '';
		$componentParams->get('flashget', 0) == 0 ? 				$botArray[] = 'FlashGet' : '';
		$componentParams->get('funwebproducts', 0) == 0 ? 			$botArray[] = 'FunWebProducts' : '';
		$componentParams->get('getright', 0) == 0 ? 				$botArray[] = 'GetRight' : '';
		$componentParams->get('getweb', 0) == 0 ? 					$botArray[] = 'GetWeb!' : '';
		$componentParams->get('gohttppackage', 0) == 0 ? 			$botArray[] = 'Go\ http\ package' : '';
		$componentParams->get('gozilla', 0) == 0 ?					$botArray[] = 'Go!Zilla' : '';
		$componentParams->get('goaheadgotit', 0) == 0 ? 			$botArray[] = 'Go-Ahead-Got-It' : '';
		$componentParams->get('grabnet', 0) == 0 ? 					$botArray[] = 'GrabNet' : '';
		$componentParams->get('grafula', 0) == 0 ? 					$botArray[] = 'Grafula' : '';
		$componentParams->get('hmview', 0) == 0 ? 					$botArray[] = 'HMView' : '';
		$componentParams->get('hatenaantenna', 0) == 0 ? 			$botArray[] = 'Hatena\ Antenna' : '';
		$componentParams->get('httrack', 0) == 0 ? 					$botArray[] = 'HTTrack' : '';
		$componentParams->get('innovantagebot', 0) == 0 ? 			$botArray[] = 'InnovantageBot' : '';
		$componentParams->get('imagestripper', 0) == 0 ? 			$botArray[] = 'Image\ Stripper' : '';
		$componentParams->get('imagesucker', 0) == 0 ? 				$botArray[] = 'Image\ Sucker' : '';
		$componentParams->get('integromedborg', 0) == 0 ? 			$botArray[] = 'integromedb.org' : '';
		$componentParams->get('indylibrary', 0) == 0 ? 				$botArray[] = 'Indy\ Library' : '';
		$componentParams->get('interget', 0) == 0 ? 				$botArray[] = 'InterGET' : '';
		$componentParams->get('internetninja', 0) == 0 ? 			$botArray[] = 'Internet\ Ninja' : '';
		$componentParams->get('jetcar', 0) == 0 ? 					$botArray[] = 'JetCar' : '';
		$componentParams->get('jocwebspider', 0) == 0 ? 			$botArray[] = 'JOC\ Web\ Spider' : '';
		$componentParams->get('jskit', 0) == 0 ? 					$botArray[] = 'JS-Kit' : '';
		$componentParams->get('larbin', 0) == 0 ? 					$botArray[] = 'larbin' : '';
		$componentParams->get('leechftp', 0) == 0 ? 				$botArray[] = 'LeechFTP' : '';
		$componentParams->get('libwww', 0) == 0 ? 					$botArray[] = 'libwww' : '';
		$componentParams->get('majestic', 0) == 0 ? 				$botArray[] = 'majestic12' : '';
		$componentParams->get('metauri', 0) == 0 ? 					$botArray[] = 'MetaURI' : '';
		$componentParams->get('myie2', 0) == 0 ? 					$botArray[] = 'MyIE2' : '';
		$componentParams->get('mysuperbot', 0) == 0 ? 				$botArray[] = 'MySuperBot' : '';
		$componentParams->get('massdownloader', 0) == 0 ? 			$botArray[] = 'Mass\ Downloader' : '';
		$componentParams->get('midowntool', 0) == 0 ? 				$botArray[] = 'MIDown\ tool' : '';
		$componentParams->get('magpiecrawler', 0) == 0 ? 			$botArray[] = 'magpie-crawler' : '';
		$componentParams->get('misterpix', 0) == 0 ? 				$botArray[] = 'Mister\ PiX' : '';
		$componentParams->get('navroad', 0) == 0 ? 					$botArray[] = 'Navroad' : '';
		$componentParams->get('nearsite', 0) == 0 ? 				$botArray[] = 'NearSite' : '';
		$componentParams->get('netants', 0) == 0 ? 					$botArray[] = 'NetAnts' : '';
		$componentParams->get('netspider', 0) == 0 ? 				$botArray[] = 'NetSpider' : '';
		$componentParams->get('netvampire', 0) == 0 ? 				$botArray[] = 'Net\ Vampire' : '';
		$componentParams->get('netzip', 0) == 0 ? 					$botArray[] = 'NetZIP' : '';
		$componentParams->get('octopus', 0) == 0 ? 					$botArray[] = 'Octopus' : '';
		$componentParams->get('offlineexplorer', 0) == 0 ? 			$botArray[] = 'Offline\ Explorer' : '';
		$componentParams->get('offlinenavigator', 0) == 0 ? 		$botArray[] = 'Offline\ Navigator' : '';
		$componentParams->get('pagegrabber', 0) == 0 ? 				$botArray[] = 'PageGrabber' : '';
		$componentParams->get('papafoto', 0) == 0 ? 				$botArray[] = 'Papa\ Foto' : '';
		$componentParams->get('pavuk', 0) == 0 ? 					$botArray[] = 'pavuk' : '';
		$componentParams->get('pcbrowser', 0) == 0 ? 				$botArray[] = 'pcBrowser' : '';
		$componentParams->get('realdownload', 0) == 0 ? 			$botArray[] = 'RealDownload' : '';
		$componentParams->get('reget', 0) == 0 ? 					$botArray[] = 'ReGet' : '';
		$componentParams->get('searcharoo', 0) == 0 ? 				$botArray[] = 'Searcharoo' : '';
		$componentParams->get('sitesnagger', 0) == 0 ? 				$botArray[] = 'SiteSnagger' : '';
		$componentParams->get('siteintelnetbot', 0) == 0 ? 			$botArray[] = 'SiteIntel.net Bot' : '';
		$componentParams->get('smartdownload', 0) == 0 ? 			$botArray[] = 'SmartDownload' : '';
		$componentParams->get('sogouwebspider', 0) == 0 ? 			$botArray[] = 'Sogou\ web\ spider' : '';
		$componentParams->get('sosospider', 0) == 0 ? 				$botArray[] = 'Sosospider' : '';
		$componentParams->get('spamblockerutility', 0) == 0 ? 		$botArray[] = 'SpamBlockerUtility' : '';
		$componentParams->get('spider', 0) == 0 ? 					$botArray[] = 'spider' : '';
		$componentParams->get('siclab', 0) == 0 ? 					$botArray[] = 'siclab' : '';
		$componentParams->get('showyoubot', 0) == 0 ? 				$botArray[] = 'ShowyouBot' : '';
		$componentParams->get('seznambot', 0) == 0 ? 				$botArray[] = 'SeznamBot' : '';
		$componentParams->get('seokicksrobot', 0) == 0 ? 			$botArray[] = 'SEOkicks-Robot' : '';
		$componentParams->get('seocompany', 0) == 0 ?	 			$botArray[] = 'seo\ company' : '';
		$componentParams->get('spbot', 0) == 0 ? 					$botArray[] = 'spbot' : '';
		$componentParams->get('superbot', 0) == 0 ? 				$botArray[] = 'SuperBot' : '';
		$componentParams->get('superhttp', 0) == 0 ? 				$botArray[] = 'SuperHTTP' : '';
		$componentParams->get('surfbot', 0) == 0 ? 					$botArray[] = 'Surfbot' : '';
		$componentParams->get('takeout', 0) == 0 ? 					$botArray[] = 'tAkeOut' : '';
		$componentParams->get('teleportpro', 0) == 0 ? 				$botArray[] = 'Teleport\ Pro' : '';
		$componentParams->get('timelyweb', 0) == 0 ? 				$botArray[] = 'TimelyWeb' : '';
		$componentParams->get('unwindfetchor', 0) == 0 ? 			$botArray[] = 'UnwindFetchor' : '';
		$componentParams->get('voideye', 0) == 0 ? 					$botArray[] = 'VoidEYE' : '';
		$componentParams->get('webimagecollector', 0) == 0 ? 		$botArray[] = 'Web\ Image\ Collector' : '';
		$componentParams->get('winhttprequest', 0) == 0 ? 			$botArray[] = 'WinHttpRequest' : '';
		$componentParams->get('webfilter', 0) == 0 ? 				$botArray[] = 'webfilter' : '';
		$componentParams->get('websucker', 0) == 0 ? 				$botArray[] = 'Web\ Sucker' : '';
		$componentParams->get('webauto', 0) == 0 ? 					$botArray[] = 'WebAuto' : '';
		$componentParams->get('webcopier', 0) == 0 ? 				$botArray[] = 'WebCopier' : '';
		$componentParams->get('webfetch', 0) == 0 ? 				$botArray[] = 'WebFetch' : '';
		$componentParams->get('webgois', 0) == 0 ? 					$botArray[] = 'WebGo\ IS' : '';
		$componentParams->get('webleacher', 0) == 0 ? 				$botArray[] = 'WebLeacher' : '';
		$componentParams->get('webreaper', 0) == 0 ? 				$botArray[] = 'WebReaper' : '';
		$componentParams->get('websauger', 0) == 0 ? 				$botArray[] = 'WebSauger' : '';
		$componentParams->get('websiteextractor', 0) == 0 ? 		$botArray[] = 'Website\ eXtractor' : '';
		$componentParams->get('websitequester', 0) == 0 ? 			$botArray[] = 'Website\ Quester' : '';
		$componentParams->get('webstripper', 0) == 0 ? 				$botArray[] = 'WebStripper' : '';
		$componentParams->get('webwhacker', 0) == 0 ? 				$botArray[] = 'WebWhacker' : '';
		$componentParams->get('webzip', 0) == 0 ? 					$botArray[] = 'WebZIP' : '';
		$componentParams->get('widow', 0) == 0 ? 					$botArray[] = 'Widow' : '';
		$componentParams->get('wwwoffle', 0) == 0 ? 				$botArray[] = 'WWWOFFLE' : '';
		$componentParams->get('webmoneyadvisor', 0) == 0 ? 			$botArray[] = 'WebMoney\ Advisor' : '';
		$componentParams->get('xaldonwebspider', 0) == 0 ? 			$botArray[] = 'Xaldon\ WebSpider' : '';
		$componentParams->get('yacybot', 0) == 0 ? 					$botArray[] = 'yacybot' : '';
		$componentParams->get('yandexbot', 0) == 0 ? 				$botArray[] = 'YandexBot' : '';
		$componentParams->get('yeti', 0) == 0 ? 					$botArray[] = 'Yeti' : '';
		$componentParams->get('zeus', 0) == 0 ? 					$botArray[] = 'Zeus' : '';
		
		$botArray = implode("|", $botArray);
		$botArray = '/\b(' . $botArray . ')\b/i';
		
		$containerArray = array();
		$containerArray['post'] = print_r($_POST, true); // posted data captured as a string
		$containerArray['get'] = print_r($_GET, true); // posted data captured as a string
	
		
		// We are not in test mode so we are free to ban
		if (!$testMode)
		{
			/*
			 * SQL Injection attempts
			 * The below sting is a base64_encoded string of /** /;UNION+SELECT;union all select;#__users;jos_users;concat(;0x26;0x25;0x3a5f;0x5f3a
			 * LyoqLztVTklPTitTRUxFQ1Q7dW5pb24gYWxsIHNlbGVjdDsjX191c2Vycztqb3NfdXNlcnM7Y29uY2F0KDsweDI2OzB4MjU7MHgzYTVmOzB4NWYzYQ==
			 */
			$sqlNeedles = explode(';', base64_decode($componentParams->get('sqlInjections', 'LyoqLztVTklPTitTRUxFQ1Q7dW5pb24gYWxsIHNlbGVjdDsjX191c2Vycztqb3NfdXNlcnM7Y29uY2F0KDsweDI2OzB4MjU7MHgzYTVmOzB4NWYzYQ==')));
			
			foreach ($containerArray as $sqlInjection)
			{
				$isRequestSQLNaughty = $this->strposArray($sqlInjection, $sqlNeedles);
				
				if ($isRequestSQLNaughty)
				{
					// The request came from a 'good' bot - we will deny the request without banning 'it'
					if ($isReallyGoodBot !== false)
					{
						break;
					}
					// We have a 'live' one, let's ban them and place an entry in the DB
					else
					{
						$explodedNaughtySQL = explode(',', $isRequestSQLNaughty);
						
						$firewallBanArray = array(
							'reason'			=> 'SQL Injection Attempt',
							'illegalContent'	=> $explodedNaughtySQL[1]
						);
						
						DmcfirewallHelperBlock::block($firewallBanArray);
						break;
					}
				}
			}
			
			/*
			 * Hack Attempts
			 * The below sting is a base64_encoded string of mosConfig_;proc/self/;proc/self/environ%0000;_REQUEST;GLOBALS;base64_encode;%0000;.txt?;../../../;path=http://
			 * bW9zQ29uZmlnXztwcm9jL3NlbGYvO3Byb2Mvc2VsZi9lbnZpcm9uJTAwMDA7X1JFUVVFU1Q7R0xPQkFMUztiYXNlNjRfZW5jb2RlOyUwMDAwOy50eHQ/Oy4uLy4uLy4uLztwYXRoPWh0dHA6Ly8=
			 */
			$hackNeedles = explode(';', base64_decode($componentParams->get('hackAttempts', 'bW9zQ29uZmlnXztwcm9jL3NlbGYvO3Byb2Mvc2VsZi9lbnZpcm9uJTAwMDA7X1JFUVVFU1Q7R0xPQkFMUztiYXNlNjRfZW5jb2RlOyUwMDAwOy50eHQ/Oy4uLy4uLy4uLztwYXRoPWh0dHA6Ly8=')));
			
			foreach ($containerArray as $key => $hackAttempt)
			{
				$isRequestHackNaughty = $this->strposArray($hackAttempt, $hackNeedles);
				
				if ($isRequestHackNaughty)
				{
					// The request came from a 'good' bot - we will deny the request without banning 'it'
					if ($isReallyGoodBot !== false)
					{
						break;
					}
					// We have a 'live' one, let's ban them and place an entry in the DB
					else
					{
						$explodedNaughtyHack = explode(',', $isRequestHackNaughty);
						
						$firewallBanArray = array(
							'reason'			=> 'Hack Attempt',
							'hackKey'			=> $key,
							'illegalContent'	=> $explodedNaughtyHack[1]
						);

						DmcfirewallHelperBlock::block($firewallBanArray);
						break;
					}
				}
			}
			
			/*
			 * Bad Bots
			 */
			if (preg_match($botArray, $_SERVER['HTTP_USER_AGENT']))
			{
				if ($isReallyGoodBot !== false)
				{
					true;
				}
				else
				{
					$firewallBanArray = array(
						'reason'			=> 'Known Bad Bot'
					);
						
					DmcfirewallHelperBlock::block($firewallBanArray);
				}
			}			
		} // end of testMode check
	}
	
	private function strposArray($haystacker, $needler)
	{
		foreach ($needler as $what) {
			if (($pos = stripos($haystacker, $what)) !== false) {
				return $pos . ',' . $what;
			}
		}
		return false;
	}
}