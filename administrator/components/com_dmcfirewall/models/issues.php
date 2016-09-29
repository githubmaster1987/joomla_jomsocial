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

class DmcfirewallModelIssues extends FOFModel {
	private $allOkFlag = FALSE;
	private $weakPasswordFlag = 0;
	private $title = '';
	
	public function getCurrentTablePrefix() {
		$config = JFactory::getConfig();
		$prefix = $config->get('dbprefix','');
		
		return $prefix;
	}
	
	public function validatePrefix() {
		$config = JFactory::getConfig();
		$prefix = $config->get('dbprefix','');
		
		/* Common table prefixs
		 * 'jos_' - default in Joomla 1.0 and 1.5
		 * 'j25_' - default when using jUpdate to migrate from Joomla 1.5 to 2.5
		 */
		if ($prefix == 'jos_' || $prefix == 'bak_' || $prefix == 'j25_' || $prefix == 'j30_' || $prefix == 'truejos_') {
			return JText::sprintf('ISSUE_TABLE_PREFIX_ERROR', $prefix);
		}
	}
	
	public function hasAkeebaBackup() {
		/* Simple check to see if Akeeba Backup is installed
		 * https://www.akeebabackup.com/
		 */
		$db = JFactory::getDBO();
		$query = "SELECT * FROM `#__extensions` WHERE `element` = 'com_akeeba' AND `enabled` = 1";
		$db->setQuery($query);
		$db->query();
		$countAkeeba = $db->getNumRows();
		
		if (!JFile::exists(JPATH_BASE . '/components/com_akeeba/akeeba.xml') || !$countAkeeba) {
			return JText::_('HAS_AKKEBA_ERROR');
		}
	}
	
	public function userID() {
		// Check if a user with a low ID is present
		$id = 42;
		$db = JFactory::getDBO();
		$query = "SELECT * FROM `#__users` WHERE `id` = $id AND `block` = 0";
		$db->setQuery($query);
		$db->query();
		$num_rows = $db->getNumRows();
		
		if ($num_rows) {
			return JText::_('USER_42_ERROR');
		}
	}
	
	public function serverFileEdits() {
		//$osCheck				= strtoupper(substr(php_uname(), 0, 3));
		$osCheck				= DmcfirewallHelperServersoftware::serverSoftware();
		
		if ($osCheck == 'IIS') {
			if (!JFile::exists(JPATH_SITE . '/web.config')) {
				return JText::_('NO_WEBCONFIG_FOUND');
			}
			else {
				$webconfigContent = file_get_contents(JPATH_SITE . '/web.config');
				
				if (
					!stripos($webconfigContent, JText::_('WEBCONFIG_DEFAULT_SECURITY_BLOCK_REMOVED_FLAG')) ||
					!stripos($webconfigContent, JText::_('WEBCONFIG_BANNED_IP_FLAG'))
				) {
					return JText::_('WEBCONFIG_ERRORS_FOUND');
				}
			}
		}
		elseif ($osCheck == 'APA') {
			if (!JFile::exists(JPATH_SITE . '/.htaccess')) {
				return JText::_('NO_HTACCESS_FILE');
			}
			else {
				$htaccessContent = file_get_contents(JPATH_SITE . '/.htaccess');
				
				if (
					!stripos($htaccessContent, JText::_('HTACCESS_REMOVED_SECURITY_BLOCK_FLAG')) ||
					!stripos($htaccessContent, JText::_('HTACCESS_LIMIT_FLAG'))
				) {
					return JText::_('HTACCESS_ISSUES_FOUND');
				}
			}
		}
		else {
			return JText::_('SERVER_NOT_IDENTIFIED');
		}
	}
	
	public function getPluginStatus() {
		$db = $this->getDbo();
		$plugin_query = $db->getQuery(true)
			->select('enabled')->from($db->qn('#__extensions'))
			->where($db->qn('element').' = \'dmcfirewall\' LIMIT 1');
		$db->setQuery($plugin_query);
		$plugin_result = $db->loadAssoc();
	
		if (!$plugin_result) {
			return JText::_('PLUGIN_NOT_INSTALLED');
		}
		elseif ($plugin_result && $plugin_result['enabled'] == 0) {
			return JText::_('PLUGIN_INSTALLED_BUT_NOT_ACTIVE');
		}
	}
	
	public function getWeakPasswords() {
		$returnOutput = '';
		$db = JFactory::getDBO();
		$query = "SELECT user.block, user.username, user.password, ugroup.group_id FROM #__users user JOIN #__user_usergroup_map ugroup ON user.id = ugroup.user_id WHERE user.block = 0 AND ugroup.group_id = 8";
		$db->setQuery($query);
		$db->query();
		$gotBackUsers = $db->loadAssocList();
		
		$easyPasswords = 'dev_admin,password,drowssap,adminpassword,nimda,secret,admin,password123,dev_password,administrator';
		
		foreach ($gotBackUsers as $user) {
			$explodedPassword = explode(",", $easyPasswords);
			if (strpos($user['password'], ':') !== false) {
				
				foreach ($explodedPassword as $seperatePassword) {
					$userPassparts   	= explode(':', $user['password']);
					$crypt   			= $userPassparts[0];
					$salt   			= @$userPassparts[1];
					$encryptedPassword 	= JUserHelper::getCryptedPassword($seperatePassword, $salt);
				
					if ($crypt == $encryptedPassword) {
						$this->weakPasswordFlag = 1;
						$returnOutput .= JText::sprintf('WEAK_ADMIN_PASSWORD_FOUND', $user['username']);
					}
				}
			}
			else {
				foreach ($explodedPassword as $seperatePassword) {
					if (md5($seperatePassword) == $user['password']) {
						$this->weakPasswordFlag = 1;
						$returnOutput .= JText::sprintf('WEAK_ADMIN_PASSWORD_FOUND', $user['username']);
					}
				}
			}
		}
		
		if ($this->weakPasswordFlag) {
			return $returnOutput;
		}
	}
	
	public function getIssues($withoutTitle = NULL) {
		if (
			!$this->validatePrefix()	&&
			!$this->getWeakPasswords()	&&
			!$this->getPluginStatus()	&&
			!$this->hasAkeebaBackup()	&&
			!$this->userID()			&&
			!$this->serverFileEdits()
		) {
			$this->allOkFlag = JText::_('DMCFIREWALL_ALL_OK');//'No errors';
		}
		
		if (!$withoutTitle) {
			if ($this->allOkFlag) {
				$this->title = '<span class="heading textLeft">' . JText::_('COM_DMCFIREWALL_DMC_FIREWALL_STATUS_HEADER') . '</span>';
			}
			else {
				$this->title = '<span class="heading textLeft">' . JText::_('COM_DMCFIREWALL_ATTENTION_NEEDED') . '</span>';
			}
		}
		$table_view = <<<VIEW
		{$this->title}
		{$this->allOkFlag}
		{$this->getWeakPasswords()}
		{$this->validatePrefix()}
		{$this->getPluginStatus()}
		{$this->hasAkeebaBackup()}
		{$this->userID()}
		{$this->serverFileEdits()}
VIEW;

		return $table_view;
	}
}