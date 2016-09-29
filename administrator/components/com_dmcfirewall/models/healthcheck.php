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

define('PHPLASTEDVERSION', '5.4.11');
define('MYSQLLATESTVERSION', '5.5.29');

class DmcfirewallModelHealthcheck extends FOFModel {
	public function hasInstallationCheck() {
		$_osCheck = strtoupper(substr(PHP_OS, 0, 3));
		
		if ($_osCheck == 'LIN') {
			$installationArray = array();
		
			$installationOutput = exec('find ' . JPATH_ROOT . '/ -name "*installation*" | wc -l');
			
			if ($installationOutput > 1) {
				$installationArray['image'] = 'not-ok';
				$installationArray['message'] = JText::_('INSTALLATION_FOLDER_TEXT_BAD');
				$installationArray['tooltip'] = JHTML::tooltip(JText::_('INSTALLTION_FOLDER_FOUND_INFO'), JText::_('INSTALLTION_FOLDER_FOUND_TITLE'), dirname(JURI::base()) . '/media/com_dmcfirewall/images/icon-16-warning.png');
			}
			else {
				$installationArray['image'] = 'ok';
				$installationArray['message'] = JText::_('INSTALLATION_FOLDER_TEXT_GOOD');
				$installationArray['tooltip'] = '';
			}
			
			return $installationArray;
		}
		else {
			$installationArray['image'] = 'not-ok';
			$installationArray['message'] = JText::_('INSTALLATION_FOLDER_WINDOWS_HOSTING');
			$installationArray['tooltip'] = '';
			
			return $installationArray;
		}
	}
	
	public function hasWeakPasswordCheck() {
		$weakPasswordArray = array();
		$db = JFactory::getDBO();
		$query = "SELECT user.block, user.username, user.password, ugroup.group_id FROM #__users user JOIN #__user_usergroup_map ugroup ON user.id = ugroup.user_id WHERE user.block = 0 AND ugroup.group_id = 8";
		$db->setQuery($query);
		$db->query();
		$gotBackUsers = $db->loadAssocList();
		
		$easyPasswords = 'dev_admin,password,drowssap,adminpassword,nimda,secret,admin,password123';
		
		foreach ($gotBackUsers as $user) {
			$explodedPassword = explode(",", $easyPasswords);
			if (strpos($user['password'],':') !== false) {
				foreach ($explodedPassword as $seperatePassword) {
					$userPassparts   	= explode( ':', $user['password'] );
					$crypt   			= $userPassparts[0];
					$salt   			= @$userPassparts[1];
					$encryptedPassword 	= JUserHelper::getCryptedPassword($seperatePassword, $salt);
				
					if ($crypt == $encryptedPassword) {
						$weakPasswordArray['image'] = 'not-ok';
						$weakPasswordArray['message'] = JText::_('WEAK_PASSWORD_TEXT_BAD');
						$weakPasswordArray['tooltip'] = JHTML::tooltip(JText::_('WEAK_PASSWORD_FOUND_INFO'), JText::_('WEAK_PASSWORD_FOUND_TITLE'), dirname(JURI::base()) . '/media/com_dmcfirewall/images/icon-16-warning.png');
					}
					else {
						$weakPasswordArray['image'] = 'ok';
						$weakPasswordArray['message'] = JText::_('WEAK_PASSWORD_TEXT_GOOD');
						$weakPasswordArray['tooltip'] = '';
					}
				}
			}
			elseif (strpos($user['password'],':') == true) {
				foreach ($explodedPassword as $seperatePassword) {
					if (md5($seperatePassword) == $user['password']) {
						$weakPasswordArray['image'] = 'not-ok';
						$weakPasswordArray['message'] = JText::_('WEAK_PASSWORD_TEXT_BAD');
						$weakPasswordArray['tooltip'] = JHTML::tooltip(JText::_('WEAK_PASSWORD_FOUND_INFO'), JText::_('WEAK_PASSWORD_FOUND_TITLE'), dirname(JURI::base()) . '/media/com_dmcfirewall/images/icon-16-warning.png');
					}
					else {
						$weakPasswordArray['image'] = 'ok';
						$weakPasswordArray['message'] = JText::_('WEAK_PASSWORD_TEXT_GOOD');
						$weakPasswordArray['tooltip'] = '';
					}
				}
			}
			else {
				$weakPasswordArray['image'] = 'ok';
				$weakPasswordArray['message'] = JText::_('WEAK_PASSWORD_TEXT_GOOD');
				$weakPasswordArray['tooltip'] = '';
			}
		}
		
		return $weakPasswordArray;
	}
	
	public function adminUsernameCheck() {
		$adminArray = array();
		
		$db = JFactory::getDBO();
		$query = "SELECT * FROM `#__users` WHERE `username` LIKE '%admin%'";
		$db->setQuery($query);
		$db->query();
		$num_rows = $db->getNumRows();
		
		if ($num_rows) {
			$adminArray['image'] = 'not-ok';
			$adminArray['message'] = JText::sprintf('ADMIN_USER_FOUND_TEXT_BAD', number_format($num_rows));
			$adminArray['tooltip'] = JHTML::tooltip(JText::_('ADMIN_USER_FOUND_INFO'), JText::_('ADMIN_USER_FOUND_TITLE'), dirname(JURI::base()) . '/media/com_dmcfirewall/images/icon-16-warning.png');
		}
		else {
			$adminArray['image'] = 'ok';
			$adminArray['message'] = JText::_('ADMIN_USER_FOUND_TEXT_GOOD');
			$adminArray['tooltip'] = '';
		}
		
		return $adminArray;
	}
	
	public function multipleJoomlaInstallsCheck() {
		$_osCheck = strtoupper(substr(PHP_OS, 0, 3));
		
		if ($_osCheck == 'LIN') {
			$administratorArray = array();
			
			$administratorOutput = exec('find ' . JPATH_ROOT . '/ -name "administrator" | wc -l');
			
			if ($administratorOutput > 1) {
				$administratorArray['image'] = 'not-ok';
				$administratorArray['message'] = JText::_('MULTIPLE_JOOMLA_INSTALLS_TEXT_BAD');
				$administratorArray['tooltip'] = JHTML::tooltip(JText::_('MULTIPLE_JOOMLA_INSTALLS_FOUND_INFO'), JText::_('MULTIPLE_JOOMLA_INSTALLS_FOUND_TITLE'), dirname(JURI::base()) . '/media/com_dmcfirewall/images/icon-16-warning.png');
			}
			else {
				$administratorArray['image'] = 'ok';
				$administratorArray['message'] = JText::_('MULTIPLE_JOOMLA_INSTALLS_TEXT_GOOD');
				$administratorArray['tooltip'] = '';
			}
			
			return $administratorArray;
		}
		else {
			$administratorArray['image'] = 'not-ok';
			$administratorArray['message'] = JText::_('MULTIPLE_JOOMLA_INSTALLS_WINDWOS_HOSTING');
			$administratorArray['tooltip'] = '';
			
			return $administratorArray;
		}
	}
	/*
	public function hasHtaccessCheck() {
		$hasHtaccessCheck = array();
		
		$htaccessOutput = exec('find ' . JPATH_BASE . '/ \( -name ".htaccess" \) -type f | wc -l');
		
		if ($htaccessOutput) {
			$hasHtaccessCheck['image'] = 'ok';
			$hasHtaccessCheck['message'] = JText::sprintf('NO_HTACCESS_FOUND_TEXT_GOOD', number_format($htaccessOutput));
			$hasHtaccessCheck['tooltip'] = '';
		}
		else {
			$hasHtaccessCheck['image'] = 'not-ok';
			$hasHtaccessCheck['message'] = JText::_('NO_HTACCESS_FOUND_TEXT_BAD');
			$hasHtaccessCheck['tooltip'] = JHTML::tooltip(JText::_('NO_HTACCESS_FOUND_INFO'), JText::_('NO_HTACCESS_FOUND_TITLE'), dirname(JURI::base()) . '/media/com_dmcfirewall/images/icon-16-warning.png');
		}
		
		return $hasHtaccessCheck;
	}
	*/
	public function hasServerFileCheck() {
		$_osCheck = strtoupper(substr(PHP_OS, 0, 3));
		$hasServerFileCheck = array();
		
		if ($_osCheck == 'LIN') {
			if (JFile::exists(JPATH_SITE . '/.htaccess')) {
				$hasServerFileCheck['image'] = 'ok';
				$hasServerFileCheck['message'] = JText::_('HTACCESS_FILE_FOUD_IN_ROOT');
				$hasServerFileCheck['tooltip'] = '';
			}
			else {
				$hasServerFileCheck['image'] = 'not-ok';
				$hasServerFileCheck['message'] = JText::_('HTACCESS_FILE_NOT_FOUD_IN_ROOT');
				$hasServerFileCheck['tooltip'] = JHTML::tooltip(JText::_('NO_HTACCESS_FOUND_INFO'), JText::_('NO_HTACCESS_FOUND_TITLE'), dirname(JURI::base()) . '/media/com_dmcfirewall/images/icon-16-warning.png');
			}
		}
		elseif ($_osCheck == 'WIN') {
			if (JFile::exists(JPATH_SITE . '/web.config')) {
				$hasServerFileCheck['image'] = 'ok';
				$hasServerFileCheck['message'] = JText::_('WEBCONFIG_FILE_FOUD_IN_ROOT');
				$hasServerFileCheck['tooltip'] = '';
			}
			else {
				$hasServerFileCheck['image'] = 'not-ok';
				$hasServerFileCheck['message'] = JText::_('WEBCONFIG_FILE_NOT_FOUD_IN_ROOT');
				$hasServerFileCheck['tooltip'] = JHTML::tooltip(JText::_('NO_HTACCESS_FOUND_INFO'), JText::_('NO_HTACCESS_FOUND_TITLE'), dirname(JURI::base()) . '/media/com_dmcfirewall/images/icon-16-warning.png');
			}
		}
		
		return $hasServerFileCheck;
	}
	
	public function findArchiveCheck() {
		$_osCheck = strtoupper(substr(PHP_OS, 0, 3));
		
		if ($_osCheck == 'LIN') {
			$archiveArray = array();
			
			$archiveOutput = exec('find ' . JPATH_ROOT . '/ \( -iname "*.zip" -o -iname "*.gz" -o -iname "*.tar" -o -iname "*.jpa" -o -iname "*.rar" \) | wc -l');
			
			if ($archiveOutput) {
				$archiveArray['image'] = 'not-ok';
				$archiveArray['message'] = JText::sprintf('ARCHIVES_FOUND_TEXT_BAD', number_format($archiveOutput));
				$archiveArray['tooltip'] = JHTML::tooltip(JText::_('ARCHIVES_FOUND_INFO'), JText::_('ARCHIVES_FOUND_TITLE'), dirname(JURI::base()) . '/media/com_dmcfirewall/images/icon-16-warning.png');
			}
			else {
				$archiveArray['image'] = 'ok';
				$archiveArray['message'] = JText::_('ARCHIVES_FOUND_TEXT_GOOD');
				$archiveArray['tooltip'] = '';
			}
			
			return $archiveArray;
		}
		else {
			$archiveArray['image'] = 'not-ok';
			$archiveArray['message'] = JText::_('ARCHIVES_CHECK_WINDOWS_HOSTING');
			$archiveArray['tooltip'] = '';
			
			return $archiveArray;
		}
	}
	
	public function hasKickstartCheck() {
		$_osCheck = strtoupper(substr(PHP_OS, 0, 3));
		
		if ($_osCheck == 'LIN') {
			$kickstartArray = array();
			
			$kickstartOutput = exec('find ' . JPATH_ROOT . '/ -name "kickstart.php" | wc -l');
			
			if ($kickstartOutput) {
				$kickstartArray['image'] = 'not-ok';
				$kickstartArray['message'] = JText::_('KICKSTART_FOUND_TEXT_BAD');
				$kickstartArray['tooltip'] = JHTML::tooltip(JText::_('KICKSTART_FOUND_INFO'), JText::_('KICKSTART_FOUND_TITLE'), dirname(JURI::base()) . '/media/com_dmcfirewall/images/icon-16-warning.png');
			}
			else {
				$kickstartArray['image'] = 'ok';
				$kickstartArray['message'] = JText::_('KICKSTART_FOUND_TEXT_GOOD');
				$kickstartArray['tooltip'] = '';
			}
			
			return $kickstartArray;
		}
		else {
			$kickstartArray['image'] = 'not-ok';
			$kickstartArray['message'] = JText::_('KICKSTART_CHECK_WONDOWS_HOSTING');
			$kickstartArray['tooltip'] = '';
			
			return $kickstartArray;
		}
	}
	
	public function knownBadFilesCheck() {
		$_osCheck = strtoupper(substr(PHP_OS, 0, 3));
		
		if ($_osCheck == 'LIN') {
			$badFilesArray = array();
			
			$badFilesOutput = exec('find ' . JPATH_ROOT . '/ \( -name "mod_joomla" -o -name "com_article" -o -name "LICESNE.php" -o -name "zzzzx.php" -o -name "index.old.php" -o -name "x.txt" \) | wc -l');
			
			if ($badFilesOutput) {
				$badFilesArray['image'] = 'not-ok';
				$badFilesArray['message'] = JText::_('BAD_FILES_FOUND_TEXT_BAD');
				$badFilesArray['tooltip'] = JHTML::tooltip(JText::_('BAD_FILES_FOUND_INFO'), JText::_('BAD_FILES_FOUND_TITLE'), dirname(JURI::base()) . '/media/com_dmcfirewall/images/icon-16-warning.png');
			}
			else {
				$badFilesArray['image'] = 'ok';
				$badFilesArray['message'] = JText::_('BAD_FILES_FOUND_TEXT_GOOD');
				$badFilesArray['tooltip'] = '';
			}
			
			return $badFilesArray;
		}
		else {
			$badFilesArray['image'] = 'not-ok';
			$badFilesArray['message'] = JText::_('BAD_FILES_WINDOWS_HOSTING');
			$badFilesArray['tooltip'] = '';
			
			return $badFilesArray;
		}
	}
	
	public function modifiedFilesCheck() {
		$_osCheck = strtoupper(substr(PHP_OS, 0, 3));
		
		if ($_osCheck == 'LIN') {
			$modifiedArray = array();
			
			$modifiedOutput = exec("find " . JPATH_ROOT . "/ -type f -ctime -3 | wc -l");
			
			if ($modifiedOutput >= 10) {
				$modifiedArray['image'] = 'not-ok';
				$modifiedArray['message'] = JText::_('MODIFIED_FILES_FOUND_TEXT_BAD');
				$modifiedArray['tooltip'] = JHTML::tooltip(JText::_('MODIFIED_FILES_FOUND_INFO'), JText::_('MODIFIED_FILES_FOUND_TITLE'), dirname(JURI::base()) . '/media/com_dmcfirewall/images/icon-16-warning.png');
			}
			else {
				$modifiedArray['image'] = 'ok';
				$modifiedArray['message'] = JText::_('MODIFIED_FILES_FOUND_TEXT_GOOD');
				$modifiedArray['tooltip'] = '';
			}
			
			return $modifiedArray;
		}
		else {
			$modifiedArray['image'] = 'not-ok';
			$modifiedArray['message'] = JText::_('MODIFIED_FILES_WINDOWS_HOSTING');
			$modifiedArray['tooltip'] = '';
			
			return $modifiedArray;
		}
	}
	
	public function lastAkeebaBackupCheck() {
		$backupArray = array();
		
		if (JFile::exists(JPATH_BASE . '/components/com_akeeba/akeeba.xml')) {
			$db = $this->getDBO();
			$query = "SELECT * FROM `#__ak_stats` WHERE `origin` != 'restorepoint' ORDER BY `id` DESC LIMIT 1";
			$db->setQuery($query);
			$akkebaStats = $db->loadAssoc();
			
			$explodeBackupDate = explode(" ", $akkebaStats['backupstart']);
			$todaysDateMinusSeven = strtotime('-7 day', strtotime(date("Y-m-d")));
			$dateCompleted = date('Y-m-d', $todaysDateMinusSeven);
		
			if ($explodeBackupDate[0] > $dateCompleted) {
				$backupArray['image'] = 'ok';
				$backupArray['message'] = JText::_('NO_BACKUP_TEXT_GOOD');
				$backupArray['tooltip'] = '';
			}
			else {
				$backupArray['image'] = 'not-ok';
				$backupArray['message'] = JText::_('NO_BACKUP_TEXT_BAD');
				$backupArray['tooltip'] = JHTML::tooltip(JText::_('NO_BACKUP_FOUND_INFO'), JText::_('NO_BACKUP_FOUND_TITLE'), dirname(JURI::base()) . '/media/com_dmcfirewall/images/icon-16-warning.png');
			}
			
			return $backupArray;
		}
		else {
			return 'No Akeeba';
		}
	}
	
	public function defaultTemplateCheck() {
		$templateArray = array();
		
		$db = JFactory::getDBO();
		$query = "SELECT * FROM `#__template_styles` WHERE `client_id` = 0 AND `home` = 1 LIMIT 1";
		$db->setQuery($query);
		$db->query();
		$templateStyle = $db->loadAssoc();
		
		$defaultTemplates = array('atomic', 'beez_20', 'beez5', 'protostar');
		
		if (in_array($templateStyle['template'], $defaultTemplates)) {
			$templateArray['image'] = 'not-ok';
			$templateArray['message'] = JText::_('DEFAULT_TEMPLATE_TEXT_BAD');
			$templateArray['tooltip'] = JHTML::tooltip(JText::_('DEFAULT_TEMPLATE_USED_INFO'), JText::_('DEFAULT_TEMPLATE_USED_TITLE'), dirname(JURI::base()) . '/media/com_dmcfirewall/images/icon-16-warning.png');
		}
		else {
			$templateArray['image'] = 'ok';
			$templateArray['message'] = JText::_('DEFAULT_TEMPLATE_TEXT_GOOD');
			$templateArray['tooltip'] = '';
		}
		
		return $templateArray;
	}
	
	public function mysqlVersionCheck() {
		$mysqlArray = array();
		if (version_compare(mysqli_get_client_info(), MYSQLLATESTVERSION) <= 0) {
			$mysqlArray['image'] = 'not-ok';
			$mysqlArray['message'] = JText::sprintf('MYSQL_OUTOFDATE_TEXT_BAD', mysqli_get_client_info(), MYSQLLATESTVERSION);'';
			$mysqlArray['tooltip'] = JHTML::tooltip(JText::_('MYSQL_OUTOFDATE_INFO'), JText::_('MYSQL_OUTOFDATE_TITLE'), dirname(JURI::base()) . '/media/com_dmcfirewall/images/icon-16-warning.png');
		}
		else {
			$mysqlArray['image'] = 'ok';
			$mysqlArray['message'] = JText::sprintf('MYSQL_OUTOFDATE_TEXT_GOOD', mysqli_get_client_info());
			$mysqlArray['tooltip'] = '';
		}
		
		return $mysqlArray;
	}
	
	public function tablePrefixCheck() {
		$config = JFactory::getConfig();
		$prefix = $config->get('dbprefix','');
		$prefixArray = array();
		
		if ($prefix == 'jos_' || $prefix == 'bak_' || $prefix == 'j25_' || $prefix == 'j30_' || $prefix == 'truejos_') {
			$prefixArray['image'] = 'not-ok';
			$prefixArray['message'] = JText::sprintf('PREFIX_COMMON_TEXT_BAD', $prefix);
			$prefixArray['tooltip'] = JHTML::tooltip(JText::_('PREFIX_COMMON_INFO'), JText::_('PREFIX_COMMON_TITLE'), dirname(JURI::base()) . '/media/com_dmcfirewall/images/icon-16-warning.png');
		}
		else {
			$prefixArray['image'] = 'ok';
			$prefixArray['message'] = JText::_('PREFIX_COMMON_TEXT_GOOD');
			$prefixArray['tooltip'] = '';
		}
		
		return $prefixArray;	
	}
	
	public function hasAkeebaCheck() {
		$akeebaArray = array();
		
		if (!JFile::exists(JPATH_BASE . '/components/com_akeeba/akeeba.xml')) {
			$akeebaArray['image'] = 'not-ok';
			$akeebaArray['message'] = JText::_('HAS_AKEEBA_TEXT_BAD');
			$akeebaArray['tooltip'] = JHTML::tooltip(JText::_('HAS_AKEEBA_INFO'), JText::_('HAS_AKEEBA_TITLE'), dirname(JURI::base()) . '/media/com_dmcfirewall/images/icon-16-warning.png');
		}
		else {
			$akeebaArray['image'] = 'ok';
			$akeebaArray['message'] = JText::_('HAS_AKEEBA_TEXT_GOOD');
			$akeebaArray['tooltip'] = '';
		}
		
		return $akeebaArray;
	}
	
	public function ftpDetailsCheck() {
		$ftpArray = array();
		
		$config = JFactory::getConfig();
		$ftp_enable = $config->get('ftp_enable');
		$ftp_host = $config->get('ftp_host');
		$ftp_port = $config->get('ftp_port');
		$ftp_user = $config->get('ftp_user');
		$ftp_pass = $config->get('ftp_pass');
		$ftp_root = $config->get('ftp_root');
		
		if ($ftp_enable == 1 || !empty($ftp_host) && $ftp_host != '127.0.0.1' || !empty($ftp_port) && $ftp_port != 21 || !empty($ftp_user) || !empty($ftp_pass) || !empty($ftp_root)) {
			$ftpArray['image'] = 'not-ok';
			$ftpArray['message'] = JText::_('FTP_DETAILS_TEXT_BAD');
			$ftpArray['tooltip'] = JHTML::tooltip(JText::_('FTP_DETAILS_INFO'), JText::_('FTP_DETAILS_TITLE'), dirname(JURI::base()) . '/media/com_dmcfirewall/images/icon-16-warning.png');
		}
		else {
			$ftpArray['image'] = 'ok';
			$ftpArray['message'] = JText::_('FTP_DETAILS_TEXT_GOOD');
			$ftpArray['tooltip'] = '';
		}
		
		return $ftpArray;
	}
	
	public function phpVersionCheck() {
		$phpArray = array();
		if (version_compare(PHP_VERSION, PHPLASTEDVERSION) <= 0) {
			$phpArray['image'] = 'not-ok';
			$phpArray['message'] =  JText::sprintf('PHP_VERSION_TEXT_BAD', PHP_VERSION, PHPLASTEDVERSION);
			$phpArray['tooltip'] = JHTML::tooltip(JText::_('PHP_VERSION_INFO'), JText::_('PHP_VERSION_TITLE'), dirname(JURI::base()) . '/media/com_dmcfirewall/images/icon-16-warning.png');
		}
		else {
			$phpArray['image'] = 'ok';
			$phpArray['message'] = JText::sprintf('PHP_VERSION_TEXT_GOOD', PHP_VERSION);
			$phpArray['tooltip'] = '';
		}
		
		return $phpArray;
	}
	
	public function filePermissionsCheck() {
		$_osCheck = strtoupper(substr(PHP_OS, 0, 3));
		
		if ($_osCheck == 'LIN') {
			$fileArray = array();
			
			$fileOutput = exec("find " . JPATH_ROOT . " -type f -perm 0777 |wc -l");
			
			if ($fileOutput) {
				$fileArray['image'] = 'not-ok';
				$fileArray['message'] = JText::sprintf('FILE_PERMISSIONS_TEXT_BAD', number_format($fileOutput));
				$fileArray['tooltip'] = JHTML::tooltip(JText::_('FILE_PERMISSIONS_INFO'), JText::_('FILE_PERMISSIONS_TITLE'), dirname(JURI::base()) . '/media/com_dmcfirewall/images/icon-16-warning.png');
			}
			else {
				$fileArray['image'] = 'ok';
				$fileArray['message'] = JText::_('FILE_PERMISSIONS_TEXT_GOOD');
				$fileArray['tooltip'] = '';
			}
			
			return $fileArray;
		}
		else {
			$fileArray['image'] = 'not-ok';
			$fileArray['message'] = JText::_('FILE_PERMISSIONS_WINDOWS_HOSTING');
			$fileArray['tooltip'] = '';
			
			return $fileArray;
		}
	}
	
	public function folderPermissionsCheck() {
		$_osCheck = strtoupper(substr(PHP_OS, 0, 3));
		
		if ($_osCheck == 'LIN') {
			$folderArray = array();
			
			$folderOutput = exec("find " . JPATH_ROOT . " -type d -perm 0777 |wc -l");
			
			if ($folderOutput) {
				$folderArray['image'] = 'not-ok';
				$folderArray['message'] = JText::sprintf('FOLDER_PERMISSIONS_TEXT_BAD', number_format($folderOutput));
				$folderArray['tooltip'] = JHTML::tooltip(JText::_('FOLDER_PERMISSIONS_INFO'), JText::_('FOLDER_PERMISSIONS_TITLE'), dirname(JURI::base()) . '/media/com_dmcfirewall/images/icon-16-warning.png');
			}
			else {
				$folderArray['image'] = 'ok';
				$folderArray['message'] = JText::_('FOLDER_PERMISSIONS_TEXT_GOOD');
				$folderArray['tooltip'] = '';
			}
			
			return $folderArray;
		}
		else {
			$folderArray['image'] = 'not-ok';
			$folderArray['message'] = JText::_('FOLDER_PERMISSIONS_WINDOWS_HOSTING');
			$folderArray['tooltip'] = '';
			
			return $folderArray;
		}
	}
	
	public function joomlaVersionCheck() {
		// Initialise the return array
		$ret = array(
			'installed'		=> JVERSION,
			'latest'		=> null,
			'object'		=> null
		);

		// Fetch the update information from the database
		$db = $this->getDbo();
		$query = $db->getQuery(true)
			->select('*')
			->from($db->qn('#__updates'))
			->where($db->qn('extension_id') . ' = ' . $db->q(700));
		$db->setQuery($query);
		$updateObject = $db->loadObject();

		if (is_null($updateObject)) {
			$ret['latest'] = JVERSION;
			$ret['image'] = 'ok';
			$ret['message'] = JText::sprintf('JOOMLA_VERSION_TEXT_GOOD', JVERSION);
			$ret['tooltip'] = '';
			return $ret;
		}
		else {
			$ret['latest'] = $updateObject->version;
			$ret['image'] = 'not-ok';
			$ret['message'] = JText::sprintf('JOOMLA_VERSION_TEXT_BAD', $updateObject->version);
			$ret['tooltip'] = JHTML::tooltip(JText::_('JOOMLA_VERSION_INFO'), JText::_('JOOMLA_VERSION_TITLE'), dirname(JURI::base()) . '/media/com_dmcfirewall/images/icon-16-warning.png');
		}

		// Fetch the full udpate details from the update details URL
		jimport('joomla.updater.update');
		$update = new JUpdate;
		$update->loadFromXML($updateObject->detailsurl);

		// Pass the update object
		if($ret['latest'] == JVERSION) {
			$ret['object'] = null;
		}
		else {
			$ret['object'] = $update;
		}

		return $ret;
	}
}