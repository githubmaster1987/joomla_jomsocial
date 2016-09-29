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

/*
 * Require our helpers
 */
require_once JPATH_ADMINISTRATOR . '/components/com_dmcfirewall/version.php';
require_once JPATH_ADMINISTRATOR . '/components/com_dmcfirewall/helpers/email.php';
require_once JPATH_ADMINISTRATOR . '/components/com_dmcfirewall/helpers/ping.php';
require_once JPATH_ADMINISTRATOR . '/components/com_dmcfirewall/helpers/serversoftware.php';
require_once JPATH_ADMINISTRATOR . '/components/com_dmcfirewall/helpers/getip.php';

class DmcfirewallHelperBlock
{
	static $entryTime;
	static $fileSize;
	
	public static function block($data)
	{
		$sendEmailCheck = true;
		jimport('joomla.version');
		
		if (!self::$entryTime)
		{
			self::$entryTime = time();
		}
		
		$serverFileMadeWritable			= false;
		
		// get our component params
		$componentParams 				= JComponentHelper::getParams('com_dmcfirewall');
		
		$jVersion						= new JVersion();
		$app							= JFactory::getApplication();
		$osCheck						= DmcfirewallHelperServersoftware::serverSoftware();
		$securityCheck					= md5(DMCFIREWALL_VERSION);
		
		$logFilePath					= JPATH_ADMINISTRATOR . '/components/com_dmcfirewall/logs/dmcfirewall_log_' . DMCFIREWALL_VERSION . '.php';
		$errorLogPath					= JPATH_ADMINISTRATOR . '/components/com_dmcfirewall/logs/dmcfirewall_error_log_' . DMCFIREWALL_VERSION . '.php';
		
		$ipAddress						= DmcfirewallHelperIp::getIP();
		
		$_SERVER['HTTP_REFERER']		= empty($_SERVER['HTTP_REFERER']) ? '' : $_SERVER['HTTP_REFERER'];
		$theDate						= date('Y-m-d');
		$theTime						= date('H:i:s');
		
		/*
		 * Set some 'flags' which relate to the type of server that we are running on (IIS or Apache)
		 */
		switch ($osCheck)
		{
			case 'APA':
				$serverFile				= JPATH_SITE . '/.htaccess';
				$fileFlag				= '.htaccess';
				$cleanFlag				= 'htaccess';
				$filePermission			= 0644;
				break;
			case 'IIS':
				$serverFile				= JPATH_SITE . '/web.config';
				$fileFlag				= 'web.config';
				$cleanFlag				= 'web.config';
				$filePermission			= 0700;
				break;
			case 'NA':
				/*
				 * If we can't identify the Operating System - let's perform some check to see if we can offer
				 * some protection
				 */
				if (file_exists(JPATH_SITE . '/.htaccess') && file_exists(JPATH_SITE . '/web.config'))
				{
					// bug out - let's not even get into this mess
					self::dieFriendly('contactUs');
				}
				elseif (file_exists(JPATH_SITE . '/.htaccess'))
				{
					// Apache (hopefully)
					$serverFile				= JPATH_SITE . '/.htaccess';
					$fileFlag				= '.htaccess';
					$cleanFlag				= 'htaccess';
					$filePermission			= 0644;
				}
				elseif (file_exists(JPATH_SITE . '/web.config'))
				{
					// IIS (hopefully)
					$serverFile				= JPATH_SITE . '/web.config';
					$fileFlag				= 'web.config';
					$cleanFlag				= 'web.config';
					$filePermission			= 0700;
				}
				else
				{
					// We've still got no idea on the OS - nothing else we can do except fail
					self::dieFriendly('contactUs');
				}
				break;
		}
		
		/*
		 * Do a little check to see if we've found the visitors' IP address
		 */
		if ($ipAddress === 'unknown' || empty($ipAddress))
		{
			self::dieFriendly('noIPAddress');
		}
		
		/*
		 * Some simple checks to see if we can read and write to either the '.htaccess' file or the 'web.config' file
		 */
		if (!file_exists($serverFile))
		{
			self::dieFriendly('noFile', $fileFlag);
		}
		
		if (!is_readable($serverFile))
		{
			self::dieFriendly('readIssue', $fileFlag);
		}
		
		if (!is_writeable($serverFile))
		{
			if ($osCheck == 'APA' && !chmod($serverFile, 0644))
			{
				self::dieFriendly('writeIssue', $fileFlag);
			}
			elseif ($osCheck == 'IIS' && !chmod($serverFile, 0744))
			{
				self::dieFriendly('writeIssue', $fileFlag);
			}
			else
			{
				/*
				 * We needed to chomd the file so we could make our edits, let's set a flag so we remember to set it back once we're done
				 */
				$serverFileMadeWritable = true;
			}
		}
		
		/*
		 * Get the size of the current server file so we can perform checks on our modified file compared to the
		 * original version
		 */
		if (!self::$fileSize)
		{
			self::$fileSize = filesize($serverFile);
		}
		
		/*
		 * Copy the server file (.htaccess/web.config) to the /tmp folder so we can do or edits safely
		 * For ease of use - we will define the location of the temp file
		 */
		define('FILELOCATION', JPATH_ROOT . '/tmp/dmc-firewall-' . $cleanFlag . '-' . self::$entryTime . '.txt');
		
		if (!copy($serverFile, FILELOCATION))
		{
			self::dieFriendly('couldntCopyFile', $fileFlag);
		}
		
		/*
		 * Because we've done all of our checks, we should now be able to get the contents of the file, let's do that and perform some
		 * more checks to see if our edits have been made
		 */
		$fileContents 					= self::getFileContents(FILELOCATION);
		
		/*
		 * If we can't get the contents of the '.htaccess' or the 'web.config' file, we need to exit
		 */
		if (!$fileContents)
		{
			self::dieFriendly('noContent', $fileFlag);
		}
		
		/*
         * Perform a check to see if the IP address is already in the server file - if it is, it means that we 
         * are getting bombarded
         */
        if (strpos($fileContents, $_SERVER['REMOTE_ADDR']) !== false)
        {
            self::dieFriendly('alreadyBlockedIP', $fileFlag);
        }
        
		/*
		 * Add the banned IP address to either the '.htacess' or 'web.config' file so they can't keep trying to hack Joomla!
		 * If we had to chmod '$serverFile' in order to edit it, we need to set it back to 'default'
		 */
		
		/*
		 * We are on an IIS system so we *should* have a 'web.config' file
		 */
		if ($osCheck == 'IIS')
		{
			if (stripos($serverFileContents, "<!-- DMC Firewall - web.config block delimiter -->") === FALSE) {
				self::dieFriendly('noWebConfigBlock', $fileFlag);
			}
			else
			{
				$webconfigOutput 				= preg_replace('@<!-- DMC Firewall - web.config block delimiter -->@i', '<add input="{REMOTE_ADDR}" pattern="' . $ipAddress . "\" />\n\t\t\t\t\t\t<!-- DMC Firewall - web.config block delimiter -->", $fileContents);
				self::saveFileContents(FILELOCATION, $webconfigOutput, false);
			}
		}
		/*
		 * We are on an APACHE system so we *should* have a '.htaccess' file
		 */
		elseif ($osCheck == 'APA')
		{
			if (stripos($fileContents, '<Limit ') === FALSE)
			{
				self::dieFriendly('noLimit', $fileFlag);
			}
			else
			{
				$htaccessOutput 				= str_ireplace('</Limit>', "deny from $ipAddress \n</Limit>", $fileContents);
				self::saveFileContents(FILELOCATION, $htaccessOutput, false);
			}
		}
		/* We have no idea what the OS is so we die! */
		else
		{
			self::dieFriendly('contactUs', null);
		}
		
		/*
		 * We now need to confirm that our edits were successful so we need to get a fresh copy of
		 * our file that is in the /tmp folder
		 */
		$newfileContents 				= self::getFileContents(FILELOCATION);
		
		if ($osCheck == 'IIS')
		{
			/*
			 * Because we don't use IIS - we don't know what happens when our modifications have gone wrong
			 * so unfortunately we will have to wait for someone to get in touch that is on IIS and they
			 * are having issues.
			 */
		}
		elseif ($osCheck == 'APA')
		{
			if (stripos($newfileContents, '</Limit>') === FALSE)
			{
				unlink(FILELOCATION);
				self::dieFriendly('noLimit', $fileFlag);
			}
			
			if ((substr_count($newfileContents, '</Limit>')) > 1)
			{
				unlink(FILELOCATION);
				self::dieFriendly('multipleLimit', $fileFlag);
			}
		}
		
		/*
		 * Lets see if our modified file is larger then the original
		 */
		if (filesize(FILELOCATION) < self::$fileSize)
		{
			self::dieFriendly('smallerFileSize', $fileFlag);
		}
		
		/*
		 * Tidy up - copy the file back to the root and remove the file that we copied into the /tmp folder
		 */
		if (!copy(FILELOCATION, $serverFile))
		{
			unlink(FILELOCATION);
		}
		else
		{
			unlink(FILELOCATION);
		}
		/*
		 * If we had to 'chmod' the file so we could work with it, we need to 'chmod' it back
		 */
		if ($serverFileMadeWritable)
		{
			chmod($serverFile, $filePermission);
		}
		
		/*
		 * Send a call to the Send helper
		 */
		DmcfirewallHelperEmail::send('ban', $data);
		
		/*
		 * This block makes sure that we don't produce any 'Undefined index' messages when error_reporting is set
		 */
		if (empty($data['illegalContent']))
		{
			$data['illegalContent'] = '';
		}
		if (empty($data['hackKey']))
		{
			$data['hackKey'] = '';
		}
		
		$expandedReasonArray = array
		(
			'SQL Injection Attempt'		=> 'Requested URI: ' . $_SERVER['REQUEST_URI'] . "<br />Illegal content: '" . $data['illegalContent'] . "'",
			'Known Bad Bot'				=> $_SERVER['HTTP_USER_AGENT'],
			'Failed Login'				=> $data['illegalContent'],
			'Hack Attempt'				=> 'Requested URI: ' . $_SERVER['REQUEST_URI'] . "<br />Illegal content within '" . $data['hackKey'] . "' data<br />'" . $data['hackKey'] . "' data contained '" . $data['illegalContent'] . "'"
		);
		
		$databaseColumnArray = array
		(
			'Known Bad Bot' 			=> 'bot_attempts_prevented',
			'SQL Injection Attempt'		=> 'sql_attempts_prevented',
			'Hack Attempt'				=> 'hack_attempts_prevented',
			'Failed Login'				=> 'bad_login_attempts'
		);
		
		/*
		 * Log the attempt in the log file
		 */
		/*
	 * Log Entry - The below lines add a record to the '$logFilePath' specified above
	 */
		$logEntry =<<<LOG_ENTRY

Date:			{$theDate}
Time: 			{$theTime}
IP Address: 	{$_SERVER['REMOTE_ADDR']}
Reason: 		{$data['reason']}
User Agent: 	{$_SERVER['HTTP_USER_AGENT']}
Request Method:	{$_SERVER['REQUEST_METHOD']}
Request URI: 	{$_SERVER['REQUEST_URI']}
Referer:		{$_SERVER['HTTP_REFERER']}

LOG_ENTRY;
		
		//Add the entry into the log file specified above - safely
		//$this->file_contents_write($logFilePath, $logEntry);
		self::saveFileContents($logFilePath, $logEntry, true);
		
		/*
		 * Ping http://www.webdevelopmentconsultancy.com so we can keep track of the hack attempts
		 */
		$domainName		= JURI::getInstance()->getHost() . ',' . $securityCheck;
		
		$vars = array(
			'reason'				=> $data['reason'],						// Reason for ban
			'useragent'				=> $_SERVER['HTTP_USER_AGENT'],			// User Agent
			'request_method'		=> $_SERVER['REQUEST_METHOD'],			// Request method (POST, GET)
			'request'				=> $_SERVER['REQUEST_URI'],				// Requested URI
 			'hackerIP'				=> $_SERVER['REMOTE_ADDR'],				// IP address of hacker
			
			'reporter'				=> $domainName,						// Web address of attacked server (reporter)
			'reporterIP'			=> $_SERVER['SERVER_ADDR'],				// IP address of attacked server (reporter)
			'version'				=> DMCFIREWALL_VERSION,					// The version of DMC Firewall
			'professional'			=> ISPRO								// Professional version or Core
		);
		$callBackData = DmcfirewallHelperPing::doCallBack($vars);
		
		/*
		 * With the returned '$callBackData', let's do a simple check to see if there is an update, if there is - send an email to
		 * the webmaster informing them
		 */
		$db = JFactory::getDBO();
		$db->setQuery("SELECT `last_update_email_time`, `last_scheduled_report_email_time` FROM `#__dmcfirewall_stats` WHERE `id` = 1");
		$db->execute();
		$statsRecord = $db->loadAssoc();
		
		$decodedData = json_decode($callBackData, true);
		$explodedJVersion = explode(',', $decodedData['joomlaVersions']);

		/*
		 * Let's check the returned data to see if an update is available, if there is an update available, let's see if our Joomla
		 * version matches the update and if it does, let's send the webmaster an email
		 */
		if (
				$decodedData['firewallVersion'] != DMCFIREWALL_VERSION &&
				in_array($jVersion->RELEASE, $explodedJVersion) &&
				$statsRecord['last_update_email_time'] <= time()
			)
		{
			switch($componentParams->get('updateEmailTime', 12))
			{
				case 1:
					$updateTime = time() + 3600;
					break;
				case 3:
					$updateTime = time() + (3600 * 3);
					break;
				case 7:
					$updateTime = time() + (3600 * 7);
					break;
				case 12:
					$updateTime = time() + (3600 * 12);
					break;
				case 24:
					$updateTime = time() + (3600 * 24);
					break;
			}
			
			DmcfirewallHelperEmail::send('update');
			
			$db->setQuery("UPDATE `#__dmcfirewall_stats` SET `last_update_email_time` = '$updateTime' WHERE `id` = 1");
			$db->execute();
		}
		
		/*
		 * Here we will see if 'Scheduled Reporting' is set to use the plugin and that we haven't sent them the report recently
		 */
		if (
				$componentParams->get('emailsEnableScheduledReporting', 1) == 1 &&
				$componentParams->get('emailsScheduledReportingType', 0) == 0 &&
				$statsRecord['last_scheduled_report_email_time'] <= time()
			)
		{
			switch ($componentParams->get('emailsScheduledReportingTime', 3))
			{
				case 1:
					$scheduledReportTime = time() + 86400;
					break;
				default:
				case 3:
					$scheduledReportTime = time() + (86400 * 3);
					break;
				case 7:
					$scheduledReportTime = time() + (86400 * 7);
					break;
				case 14:
					$scheduledReportTime = time() + (86400 * 14);
					break;
				case 31:
					$scheduledReportTime = time() + (86400 * 31);
					break;
			}
			
			require JPATH_ADMINISTRATOR . '/components/com_dmcfirewall/helpers/graphstats.php';
			DmcfirewallGraphStatsHelper::buildScheduledReport($componentParams->get('emailsScheduledReportingReportDuration', 7));
			
			$db->setQuery("UPDATE `#__dmcfirewall_stats` SET `last_scheduled_report_email_time` = '$scheduledReportTime' WHERE `id` = 1");
			$db->execute();
		}
		
		/*
		 * Create a database object and insert a new log within the `#__dmcfirewall_log` table
		 */
		$safeAdditionalInfo = $db->escape($expandedReasonArray[$data['reason']]);
		$db->setQuery("INSERT INTO `#__dmcfirewall_log` (`ip`, `reason`, `additional_information`, `time_date`) 
						VALUES ('$ipAddress', '" . $data['reason'] . "', '" . $safeAdditionalInfo . "', '" . date('Y-m-d') . ' - ' . date('H:i:s') . "')");
		$result = $db->execute();
		
		/*
		 * Update the `#__dmcfirewall_stats` table incrementing the relevant field
		 */
		$dbColumn = $databaseColumnArray[$data['reason']];
		$db->setQuery("UPDATE `#__dmcfirewall_stats` SET $dbColumn=$dbColumn+1, `attacks_prevented`=`attacks_prevented`+1 WHERE `id` = 1");
		$result = $db->execute();
		
		return die($componentParams->get('customBannedMessage', '') . '<br /><br />Our website is protected by DMC Firewall!<br />This script was created by Dean Marshall Consultancy Ltd who are <a href="http://www.webdevelopmentconsultancy.com/" target="_blank">Joomla Security Experts</a>');
	}

	
	private static function dieFriendly($errorType, $file = null)
	{
		// we need to log the error and pass the information to the email helper so it can send a nice email
		//$this->sendMailFunction('DMC Firewall - Error', $errorMsg, $status = 'Error');
		//$this->file_contents_write($error_log, 'Hacking attempt from IP Address: ' . $_SERVER['REMOTE_ADDR']. "\r\n" . $errorMsg, true);
		
        DmcfirewallHelperEmail::send($errorType, null);
        
		$componentParams				= JComponentHelper::getParams('com_dmcfirewall');
        unlink(FILELOCATION);
		return die($componentParams->get('customBannedMessage', '') . '<br /><br />Our website is protected by DMC Firewall!<br />This script was created by Dean Marshall Consultancy Ltd who are <a href="http://www.webdevelopmentconsultancy.com/" target="_blank">Joomla Security Experts</a>');
	}
	
	private static function saveFileContents($filename, $contents, $append = true)
	{
		$method						= ($append) ? 'a' : 'w';
	
		/*
		 * We need to open the server file ('.htaccess' or 'web.config') so we can save our edits.
		 */
		if ($fp = fopen($filename, $method))
		{
			$startTime = microtime();
			do
			{
				$canWrite = flock($fp, LOCK_EX);
				// If lock not obtained sleep for 0 - 100 milliseconds, to avoid collision and CPU load
				if (!$canWrite)
				{
					usleep(round(rand(0, 100) * 1000));
				}		
			}
			while ((!$canWrite) and ((microtime() - $startTime) < 1000));

			//file was locked so now we can store information
			if ($canWrite)
			{
				fwrite($fp, $contents);
			}
			flock($fp, LOCK_UN);
			fclose($fp);
		}
	}
	
	private static function getFileContents($fileName)
	{
		$contents				= '';
		$fp = fopen ($fileName, "r");
		$contents = file_get_contents($fileName);
		
		return $contents;
	}
}
