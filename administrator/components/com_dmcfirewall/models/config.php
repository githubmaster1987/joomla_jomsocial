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

jimport('joomla.filesystem.folder');
jimport('joomla.filesystem.file');

require_once JPATH_ADMINISTRATOR . '/components/com_dmcfirewall/helpers/serversoftware.php';

class DmcfirewallModelConfig extends FOFModel
{
	public function serverFileIssues()
	{
		$osCheck		= DmcfirewallHelperServersoftware::serverSoftware();
		
		switch ($osCheck)
		{
			case 'IIS':
				$serverFile				= 'web.config';
				$noServerFileJText		= JText::_('CONFIG_NO_WEBCONFIG_FILE');
				$fileContentIfOne		= JText::_('WEBCONFIG_ADDED_IP_BLOCK');
				$fileContentIfTwo		= JText::_('WEBCONFIG_REMOVED_JOOMLA_BLOCKS');
				$fileHasErrotsJText		= JText::_('CONFIG_WEBCONFIG_FILE_ERROR');
				$fileAllGood			= 'CONFIG_WEBCONFIG_FILE_ALL_GOOD';
				$addFileButton			= JText::_('CONFIG_WEBCONFIG_ADD_FILE');
				$editFileButton			= JText::_('CONFIG_WEBCONFIG_EDIT_FILE');
				break;
			case 'APA':
				$serverFile				= '.htaccess';
				$noServerFileJText		= JText::_('CONFIG_NO_HTACCESS_FILE');
				$fileContentIfOne		= JText::_('HTACCESS_REMOVED_SECURITY_BLOCK_FLAG');
				$fileContentIfTwo		= JText::_('HTACCESS_LIMIT_FLAG');
				$fileHasErrotsJText		= JText::_('CONFIG_HTACCESS_FILE_ERROR');
				$fileAllGood			= 'CONFIG_HTACESS_FILE_ALL_GOOD';
				$addFileButton			= JText::_('CONFIG_HTACCESS_ADD_FILE');
				$editFileButton			= JText::_('CONFIG_HTACCESS_EDIT_FILE');
				break;
		}
		
		if (!JFile::exists(JPATH_SITE . '/' . $serverFile))
		{
			$noFileOutput =<<<NOFILE
<form name="adminForm" action="index.php" action="post" id="adminForm" class="form form-horizontal">
	<input type="hidden" name="option" value="com_dmcfirewall" />
	<input type="hidden" name="view" value="config" />
	<input type="hidden" name="task" value="addFile" />
	<div class="alert alert-error">
		$noServerFileJText
		<span style="display:block;" class="standard-button-form-actions">
			<input type="submit" class="btn btn-danger btn-large" value="$addFileButton" />
		</span>
	</div>
</form>
NOFILE;

			return $noFileOutput;
		}
		elseif (JFile::exists(JPATH_SITE . '/' . $serverFile))
		{
			$fileContent = file_get_contents(JPATH_SITE . '/' . $serverFile);
			
			if (
				!stripos($fileContent, $fileContentIfOne) ||
				!stripos($fileContent, $fileContentIfTwo)
			)
			{
				$fileHasErrors =<<<CORRECTERRORS
<form name="adminForm" action="index.php" action="post" id="adminForm" class="form form-horizontal">
	<input type="hidden" name="option" value="com_dmcfirewall" />
	<input type="hidden" name="view" value="config" />
	<input type="hidden" name="task" value="makeEdits" />
	<div class="alert alert-error">
		$fileHasErrotsJText
		<span style="display:block;" class="standard-button-form-actions">
			<input type="submit" class="btn btn-danger btn-large" value="$editFileButton" />
		</span>
	</div>
</form>
CORRECTERRORS;
					
				return $fileHasErrors;
			}
			else
			{
				return JText::sprintf($fileAllGood, "<h2>'" . $serverFile . "' File</h2>");
			}
		}
		else
		{
			return JText::_('UNABLE_TO_FIND_SERVER');
		}
	}
	
	/*
	 * Do a number of checks on the 'plg_dmcfirewall' plugin
	 */
	public function getFirewallPluginStatus()
	{
		$db = $this->getDbo();
		$firewallPluginQuery = $db->getQuery(true)
			->select('enabled')->from($db->qn('#__extensions'))
			->where($db->qn('element').' = \'dmcfirewall\' LIMIT 1');
		$db->setQuery($firewallPluginQuery);
		$firewallPluginResult = $db->loadAssoc();
		
		if (!$firewallPluginResult)
		{
			$firewallPluginMessage = JText::sprintf('CONFIG_FIREWALL_PLUGIN_NOT_INSTALLED_REINSTALL', "<h2>'plg_dmcfirewall' Status</h2>");	//Plugin NOT installed
		}
		elseif ($firewallPluginResult && $firewallPluginResult['enabled'] == 0)
		{
			$firewallPluginMessage = JText::sprintf('CONFIG_FIREWALL_PLUGIN_NOT_ENABLED', "<h2>'plg_dmcfirewall' Status</h2>");				//Plugin installed BUT NOT enabled
		}
		else
		{
			$firewallPluginMessage = JText::sprintf('CONFIG_FIREWALL_PLUGIN_INSTALLED_AND_ACTIVE', "<h2>'plg_dmcfirewall' Status</h2>");	//Plugin installed AND enabled
		}
		
		return $firewallPluginMessage;
	}
	
	/*
	 * Do a number of checks on the 'plg_dmccontentsniffer' plugin
	 */
	public function getSnifferPluginStatus()
	{
		$db = $this->getDbo();
		$snifferPluginQuery = $db->getQuery(true)
			->select('enabled')->from($db->qn('#__extensions'))
			->where($db->qn('element').' = \'dmccontentsniffer\' LIMIT 1');
		$db->setQuery($snifferPluginQuery);
		$snifferPluginResult = $db->loadAssoc();
		
		if (!$snifferPluginResult)
		{
			$snifferPluginMessage = JText::sprintf('CONFIG_SNIFFER_PLUGIN_NOT_INSTALLED_REINSTALL', "<h2>'plg_dmccontentsniffer' Status</h2>");	//Plugin NOT installed
		}
		elseif ($snifferPluginResult && $snifferPluginResult['enabled'] == 0)
		{
			$snifferPluginMessage = JText::sprintf('CONFIG_SNIFFER_PLUGIN_NOT_ENABLED', "<h2>'plg_dmccontentsniffer' Status</h2>");				//Plugin installed BUT NOT enabled
		}
		else
		{
			$snifferPluginMessage = JText::sprintf('CONFIG_SNIFFER_PLUGIN_INSTALLED_AND_ACTIVE', "<h2>'plg_dmccontentsniffer' Status</h2>");	//Plugin installed AND enabled
		}
		
		return $snifferPluginMessage;
	}
	
	/*
	 * Do a number of checks on the 'plg_dmclogin' plugin
	 */
	public function getLoginPluginStatus()
	{
		$db = $this->getDbo();
		$loginPluginQuery = $db->getQuery(true)
			->select('enabled')->from($db->qn('#__extensions'))
			->where($db->qn('element').' = \'dmclogin\' LIMIT 1');
		$db->setQuery($loginPluginQuery);
		$loginPluginResult = $db->loadAssoc();
		
		if (!$loginPluginResult)
		{
			$loginPluginMessage = JText::sprintf('CONFIG_LOGIN_PLUGIN_NOT_INSTALLED_REINSTALL', "<h2>'plg_dmclogin' Status</h2>");	//Plugin NOT installed
		}
		elseif ($loginPluginResult && $loginPluginResult['enabled'] == 0)
		{
			$loginPluginMessage = JText::sprintf('CONFIG_LOGIN_PLUGIN_NOT_ENABLED', "<h2>'plg_dmclogin' Status</h2>");				//Plugin installed BUT NOT enabled
		}
		else
		{
			$loginPluginMessage = JText::sprintf('CONFIG_LOGIN_PLUGIN_INSTALLED_AND_ACTIVE', "<h2>'plg_dmclogin' Status</h2>");		//Plugin installed AND enabled
		}
		
		return $loginPluginMessage;
	}
	
	/*
	 * Add a '.htaccess' or a 'web.config' file from our 'assets' table
	 */
	public function addServerFile()
	{
		$osCheck		= DmcfirewallHelperServersoftware::serverSoftware();
		
		if ($osCheck == 'IIS')
		{
		// double check that there isn't a 'web.config' file on the server
			if (!JFile::exists(JPATH_SITE . '/web.config'))
			{
				if (copy(JPATH_ADMINISTRATOR . '/components/com_dmcfirewall/assets/servertype/iis/firewall.webconfig', JPATH_SITE . '/web.config'))
				{
					return '001';
				}
				else
				{
					return '002';
				}
			}
			else
			{
				return '003'; // web.config file exists
			}
		}
		elseif ($osCheck == 'APA')
		{
			if (!JFile::exists(JPATH_SITE . '/.htaccess'))
			{
				if (copy(JPATH_ADMINISTRATOR . '/components/com_dmcfirewall/assets/servertype/apache/firewall.htaccess', JPATH_SITE . '/.htaccess'))
				{
					return '004';
				}
				else
				{
					return '005';
				}
			}
			else
			{
				return '006'; // .htaccess file exists
			}
		}
		else
		{
			return '007'; // couldn't identify server
		}
	}
	
	/*
	 *
	 */
	public function modifyServerFile()
	{
		$osCheck		= DmcfirewallHelperServersoftware::serverSoftware();
		
		if ($osCheck == 'IIS')
		{
			$webconfigContents 			= self::file_contents_read(JPATH_SITE . '/web.config');
			$webconfigFirstLine			= '<rule name="Joomla! Rule 1" stopProcessing="true">';
			$webconfigLastline			= '</rule>';
			
			copy(JPATH_SITE . '/web.config', JPATH_SITE . '/backup.web.config');
				
			if (!is_readable(JPATH_SITE . '/web.config'))
			{
				return '001';//file isn't readable
			}
			if (!is_writeable(JPATH_SITE . '/web.config'))
			{
				if (!chmod(JPATH_SITE . '/web.config', 0744))
				{
					return '002';//couldn't write to the file
				}
				else
				{
					$webconfigWritable = true;
				}
			}
			
			$webconfigRegex = "/^" . preg_quote( $webconfigFirstLine, '/') .".*?". preg_quote( $webconfigLastline, '/') . "/sm";
			$webconfigToAdd =<<<WEBCONFIGTOADD
<rule name="DMC Firewall - banned IP address get inserted here!">
				<match url=".*" />
				<conditions logicalGrouping="MatchAny">
					<add input="{REMOTE_ADDR}" pattern="0.0.0.0" /> <!-- This is a dummy IP address so Windows doesn't fall over -->
					<!-- DMC Firewall - web.config block delimiter -->
				</conditions>
				<action type="CustomResponse" statusCode="403" statusReason="Forbidden" statusDescription="Forbidden" />
			</rule>
				
			<!-- DMC Firewall removed standard Joomla blocking! -->
				
WEBCONFIGTOADD;

			$webconfigPerformReplace = preg_replace("#<rule name=(.*)</rule>#iUs", $webconfigToAdd, $webconfigContents, 1);
			$webconfigEditsMade = self::file_contents_write(JPATH_SITE.'/web.config', $webconfigPerformReplace, false);
			
			//make sure we chmod the file back
			if ($webconfigWritable)
			{
				chmod(JPATH_SITE . '/web.config', 0500);
			}
				
			//pass back a responce to the user!
			if ($webconfigEditsMade == 'edits-made')
			{
				return '003';// successfully made the edits
			}
			else
			{
				return '004';//couldn't make the edits
			}
		}
		elseif ($osCheck == 'APA')
		{
			copy(JPATH_SITE . '/.htaccess', JPATH_SITE . '/backup.htaccess');
				
			if (!is_readable(JPATH_SITE . '/.htaccess'))
			{
				return '005';
			}
			if (!is_writeable(JPATH_SITE . '/.htaccess'))
			{
				if (!chmod(JPATH_SITE . '/.htaccess', 0644))
				{
					return '006';
				}
				else
				{
					$htaccessWritable = true;
				}
			}
			
			$htaccessEditsFlag			= 0;
			$htaccessContents 			= self::file_contents_read(JPATH_SITE . '/.htaccess');
			$htaccessStarBlock			= '## Begin - Rewrite rules to block out some common exploits.';
			$htaccessEndBlock			= '## End - Rewrite rules to block out some common exploits.';
			$htaccessRegex				= "/^" . preg_quote( $htaccessStarBlock, '/') .".*?". preg_quote( $htaccessEndBlock, '/') . "/sm";
			$htaccessPregReplace		= preg_replace ($htaccessRegex, "### DMC Firewall - Removed Joomla bad requests htaccess block ###", $htaccessContents);
		/*
		 * The block below targets the htaccess file AFTER the client has used Akeeba's Admin Tools htaccess maker
		 */
			$adminToolsHtaccessStarBlock		= '##### Rewrite rules to block out some common exploits -- BEGIN';
			$adminToolsHtaccessEndBlock			= '##### File injection protection -- END';
			$adminToolsHtaccessRegex			= "/^" . preg_quote( $adminToolsHtaccessStarBlock, '/') .".*?". preg_quote( $adminToolsHtaccessEndBlock, '/') . "/sm";
			$adminToolsHtaccessPregReplace		= preg_replace ($adminToolsHtaccessRegex, "### DMC Firewall - Removed Joomla bad requests htaccess block ###", $htaccessContents);
		/*
		 * End of Akeeba Admin Tools htaccess maker
		 */
			if(stripos($htaccessContents, "### DMC Firewall - Removed Joomla bad requests htaccess block ###") === FALSE){
				// do Joomla! standard
				$htaccessPregReplaceCallBack = self::file_contents_write(JPATH_SITE . '/.htaccess', $htaccessPregReplace, false);
				
				$db = $this->getDbo();
				$snifferPluginQuery = $db->getQuery(true)
					->select('enabled')->from($db->qn('#__extensions'))
					->where($db->qn('element').' = \'admintools\' LIMIT 1');
				$db->setQuery($snifferPluginQuery);
				$hasAdminTools = $db->loadresult();
				
				// If the have Admin Tools installed - perform the htaccess edits based on the Admin Tools htaccess maker
				if($hasAdminTools){
					$htaccessPregReplaceCallBack = self::file_contents_write(JPATH_SITE . '/.htaccess', $adminToolsHtaccessPregReplace, false);
				}
				
				if($htaccessPregReplaceCallBack == 'edits-unsuccessful'){
					$htaccessEditsFlag = 1;//error
				}else{
					$htaccessEditsFlag = 2;//successful
				}
			}else{
				$htaccessEditsFlag = 2;
			}
			// we keep the edits seperate (no if elseif)
			$htaccessContentsFresh			= self::file_contents_read(JPATH_SITE . '/.htaccess');
			
			if (stripos($htaccessContentsFresh, "<Limit ") === FALSE)
			{
				//no limit
				$limitEditsBlock =<<<LIMIT_SECTION

<Limit GET POST>
order allow,deny
allow from all

</Limit>
LIMIT_SECTION;
				$limitEdits = self::file_contents_write(JPATH_SITE . '/.htaccess', $limitEditsBlock, true);
				
				if ($limitEdits == 'edits-unsuccessful')
				{
					$htaccessEditsFlag = 1;
				}
				else
				{
					$htaccessEditsFlag += 1;
				}						
			}
			else
			{
				$htaccessEditsFlag += 1;
			}
			
			//make sure we chmod the file back
			if ($htaccessWritable)
			{
				chmod(JPATH_SITE . '/.htaccess', 0444);
			}
			
			if ($htaccessEditsFlag == 3)
			{
				return '007';
			}
			elseif ($htaccessEditsFlag = 2)
			{
				return '008';
			}
			else
			{
				return '009';
			}
		}
		else
		{
			return '010';
		}
	}
	
	/*
	 *
	 */
	private static function file_contents_read($filename)
	{
		$contents = '';
		$fp = fopen ($filename, "r");
		if ($fp)
		{
			$startTime = microtime();
			do
			{
				$canRead = flock($fp, LOCK_EX);
				// If lock not obtained sleep for 0 - 100 milliseconds, to avoid collision and CPU load
				if (!$canRead)
				{
					usleep(round(rand(0, 100)*1000));
				}
			}
			while ((!$canRead)and((microtime()-$startTime) < 1000));
			
			//file was locked so now we can store information
			if ($canRead)
			{
				$contents = fread($fp, filesize($filename));
			}
			fclose ($fp); 
		}
		else
		{
			$contents = '';
		}
		return $contents;
	}
	
	private static function file_contents_write($filename, $contents, $append = true)
	{
		$method = ($append) ? 'a' : 'w';
	
		// waiting until file will be locked for writing (1000 milliseconds as timeout)
		if ($fp = fopen($filename, $method))
		{
			$startTime = microtime();
			do
			{
				$canWrite = flock($fp, LOCK_EX);
				// If lock not obtained sleep for 0 - 100 milliseconds, to avoid collision and CPU load
				if (!$canWrite) usleep(round(rand(0, 100)*1000));
		
			}
			while ((!$canWrite)and((microtime()-$startTime) < 1000));

			//file was locked so now we can store information
			if ($canWrite)
			{
				fwrite($fp, $contents);
			}
			fclose($fp);
			return 'edits-made';
		}
		else
		{
			return 'edits-unsuccessful';
		}
	}
}
