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

class Com_DmcfirewallInstallerScript {
	
	/**
	 *
	 */
	protected $_dmcfirewall_extension = 'com_dmcfirewall';
	
	/*
	 * Prepare to install any modules/plugins
	 */
	private $installation_queue = array(
		// modules => { (folder) => { (module) => { (position), (published) } }* }*
		'modules' => array(
			'admin' => array(
				'firewallstats' 		=> array('cpanel', 1)
			),
			'site' => array(
			)
		),
		// plugins => { (folder) => { (element) => (published) }* }*
		'plugins' => array(
			'system' => array(
				'dmcfirewall'			=> 1,
				'dmccontentsniffer'		=> 1,
			),
			'installer'	=> array(
				'dmcfirewall' => 1,
			),
		)
	);
	
	/*
	 * Build a list of files and folders that we should remove during install
	 */
	private $removeFilesAndFolders = array(
        'files'    => array(
			// removed files
            'administrator/components/com_dmcfirewall/models/superadmins.php',
            'administrator/components/com_dmcfirewall/models/dbchangers.php',
            'administrator/components/com_dmcfirewall/models/cpanels.php',
            'administrator/components/com_dmcfirewall/models/configs.php',
			
			// FOF 1.x files
            'libraries/fof/controller.php',
            'libraries/fof/dispatcher.php',
            'libraries/fof/inflector.php',
            'libraries/fof/input.php',
            'libraries/fof/model.php',
            'libraries/fof/query.abstract.php',
            'libraries/fof/query.element.php',
            'libraries/fof/query.mysql.php',
            'libraries/fof/query.mysqli.php',
            'libraries/fof/query.sqlazure.php',
            'libraries/fof/query.sqlsrv.php',
            'libraries/fof/render.abstract.php',
            'libraries/fof/render.joomla.php',
            'libraries/fof/render.joomla3.php',
            'libraries/fof/render.strapper.php',
            'libraries/fof/string.utils.php',
            'libraries/fof/table.php',
            'libraries/fof/template.utils.php',
            'libraries/fof/toolbar.php',
            'libraries/fof/view.csv.php',
            'libraries/fof/view.html.php',
            'libraries/fof/view.json.php',
            'libraries/fof/view.php',

        ),
        'folders' => array(
        )
    );
	
	private $firewallCliScripts = array(
		'dmcfirewall-scheduledreporting.php'
	);
	
	/**
	 * Joomla! pre-flight event
	 * 
	 * @param string $type Installation type (install, update, discover_install)
	 * @param JInstaller $parent Parent object
	 */
	public function preflight($type, $parent){
		// Only allow to install on Joomla! 2.5.6 or later with PHP 5.3.0 or later
		if(defined('PHP_VERSION')){
			$version = PHP_VERSION;
		}elseif (function_exists('phpversion')){
			$version = phpversion();
		}else{
			$version = '5.0.0'; // all bets are off!
		}
		
		if(!version_compare(JVERSION, '3.0', 'ge')){
			$msg = "<p>You need Joomla! 3.0 or later to install this component</p>";
			JError::raiseWarning(100, $msg);
			return false;
		}
		
		if(!version_compare($version, '5.3.3', 'ge')){
			$msg = "<p>You need PHP 5.3.1 or later to install this component</p>";
			if(version_compare(JVERSION, '3.0', 'gt')){
				JLog::add($msg, JLog::WARNING, 'jerror');
			}else{
				JError::raiseWarning(100, $msg);
			}
			return false;
		}
		
		// Modify the saved FW config and base64_encode the 'hackAttempts, sqlInjections' so users can
		// save the config if they have 'mod_security' installed on their server
		// Added FW v1.3
		if(in_array($type, array('update'))){
			$this->_modifySavedConfigForModSecurity();
		}
		
		// Bugfix for "Can not build admin menus"
		if(in_array($type, array('install'))){
			$this->_bugfixDBFunctionReturnedNoError();
		}elseif($type != 'discover_install'){
			$this->_bugfixCantBuildAdminMenus();
			$this->_fixBrokenSQLUpdates($parent);
			$this->_fixSchemaVersion();
			$this->_resetLiveUpdate();
		}
		
		return true;
	}
	
	/**
     * Runs after install, update or discover_update
     * @param string $type install, update or discover_update
     * @param JInstaller $parent
     */
	function postflight($type, $parent){
		// Make the necessary changes to '.htaccess' or 'web.config'
		$serverFileEdits = $this->_serverFileEdits();
		
		// Install subextensions
		$status = $this->_installSubextensions($parent);
		
		// Install FOF
		// REMOVED - FOF is in the core
		//$fofStatus = $this->_installFOF($parent);
		
		// Install Akeeba Straper
		//$straperStatus = $this->_installStraper($parent);
		
		// Remove obsolete files and folders
		$removeFilesFolders = $this->removeFilesAndFolders;
		$this->_removeObsoleteFilesAndFolders($removeFilesFolders);
		
		// Enable 'dmclogin'
		$isPro = is_file($parent->getParent()->getPath('source') . '/models/dbchanger.php');
		if($isPro){
			$enableLoginPlugin = $this->_enableLoginPlugin();
		}
		
		// Move the htaccess backup to a secure location
		$backupStatus = $this->_moveServerFile($parent);
		
		// Copy the CLI files to the root/cli folder
		$this->_copyCliFiles($parent);
		
		// Show the post-installation page
		$this->_renderPostInstallation($status, $serverFileEdits, $parent, $backupStatus);
	}
	
	/**
	 * Runs on uninstallation
	 * 
	 * @param JInstaller $parent 
	 */
	function uninstall($parent){
		// Revert the necessary edits
		$firewallEdits = $this->revertServerFileEdits();
		
		// Uninstall subextensions
		$status = $this->_uninstallSubextensions($parent);
		
		//re-enable Joomla's core authentication plugin so the user can log into their website
		$isPro = is_file($parent->getParent()->getPath('source') . '/models/dbchanger.php');
		if($isPro){
			$enableJoomlaLogin = $this->_disableLoginPlugin();
		}
		
		// Show the post-uninstallation page
		$this->_renderPostUninstallation($status, $firewallEdits, $parent);
	}
	
	/**
	 * Renders the post-installation message 
	 */
	private function _renderPostInstallation($status, $serverFileEdits, $parent, $backupStatus){
		require_once JPATH_ADMINISTRATOR . '/components/com_dmcfirewall/helpers/serversoftware.php';
		$rows = 1;
		$osCheck = DmcfirewallHelperServersoftware::serverSoftware();
		
		switch($osCheck){
			case 'IIS':
				$serverOutputFile = 'web.config';
				break;
			case 'APA':
				$serverOutputFile = '.htaccess';
				break;
		}
?>
<style type="text/css">
.list-striped, .row-striped {
    border-top: 1px solid #DDDDDD;
    line-height: 18px;
    list-style: none outside none;
    margin-left: 0;
    text-align: left;
    vertical-align: middle;
}
fieldset.dmc-install {border:1px solid #ddd !important; padding:3px !important; margin-bottom:15px !important; background-color:#eee !important;}
table.adminlist {margin-bottom:15px !important;}
table.adminlist,table.adminlist tr, table.adminlist th, table.adminlist td {border-collapse:collapse !important; border:1px solid #ddd !important;}
table.adminlist td, table.adminlist th { padding:4px !important; height:auto !important;}
table.adminlist tbody tr:nth-child(2n+1){background-color: #eeeeee !important;}
</style>
<h1 style="width: 50%; float: left; margin: 0px;">Thank you for installing DMC Firewall!</h1>
<span style="float: right; margin-bottom: 10px;">
	<img src="../media/com_dmcfirewall/images/dmc-logo.png">
</span>

<table class="adminlist" style="width:100%;">
	<thead>
		<tr>
			<th class="title" colspan="2">Extension</th>
			<th width="40%">Status</th>
		</tr>
	</thead>
	<tfoot>
		<tr>
			<td colspan="3"></td>
		</tr>
	</tfoot>
	<tbody>
		<tr class="row0">
			<td class="key" colspan="2">DMC Firewall component</td>
			<td><strong style="color: green">Installed</strong></td>
		</tr>
		<?php
		/*
		<tr class="row1">
			<td class="key" colspan="2">
				<strong>Framework on Framework (FOF) <?php echo $fofStatus['version']?></strong> [<?php echo $fofStatus['date'] ?>]
			</td>
			<td><strong>
				<span style="color: <?php echo $fofStatus['required'] ? ($fofStatus['installed']?'green':'red') : '#660' ?>; font-weight: bold;">
					<?php echo $fofStatus['required'] ? ($fofStatus['installed'] ?'Installed':'Not Installed') : 'Already up-to-date'; ?>
				</span>	
			</strong></td>
		</tr>
		
		
		<tr class="row1">
			<td class="key" colspan="2">
				<strong>Akeeba Strapper <?php echo $straperStatus['version']?></strong> [<?php echo $straperStatus['date'] ?>]
			</td>
			<td><strong>
				<span style="color: <?php echo $straperStatus['required'] ? ($straperStatus['installed']?'green':'red') : '#660' ?>; font-weight: bold;">
					<?php echo $straperStatus['required'] ? ($straperStatus['installed'] ? 'Installed':'Not Installed') : 'Already up-to-date'; ?>
				</span>	
			</strong></td>
		</tr>
		*/
		?>
		<tr class="row1">
			<td class="key" colspan="2">'<?php echo $serverOutputFile; ?>' backup status<br /><?php echo JPATH_ADMINISTRATOR . '/components/com_dmcfirewall/backups/'; ?></td>
			<td><strong><?php
				switch($backupStatus){
					case 'copied/moved':
						echo '<span style="color:green;">Backup file moved successfully</span>';
						break;
					case 'copied':
						echo '<span style="color:red;">Backup file only copied to new location - please manually remove</span>';
						break;
					case 'no backup':
						echo '<span style="color:green;">No backup found</span>';
						break;
				}
			?></strong></td>
		</tr>
		<tr class="row0">
			<td class="key" colspan="2">'<?php echo $serverOutputFile; ?>' edit status</td>
			<td><strong><?php echo $serverFileEdits; ?></strong></td>
		</tr>
		<?php if (count($status->modules)) : ?>
		<tr class="row1">
			<th>Module</th>
			<th>Client</th>
			<th></th>
		</tr>
		<?php foreach ($status->modules as $module) : ?>
		<tr class="row<?php echo ($rows++ % 2); ?>">
			<td class="key"><?php echo $module['name']; ?></td>
			<td class="key"><?php echo ucfirst($module['client']); ?></td>
			<td><strong style="color: <?php echo ($module['result'])? "green" : "red"?>"><?php echo ($module['result'])?'Installed':'Not installed'; ?></strong></td>
		</tr>
		<?php endforeach;?>
		<?php endif;?>
		<?php if (count($status->plugins)) : ?>
		<tr>
			<th>Plugin</th>
			<th width="30%">Group</th>
			<th></th>
		</tr>
		<?php foreach ($status->plugins as $plugin) : ?>
		<tr class="row<?php echo ($rows++ % 2); ?>">
			<td class="key"><?php echo $plugin['name']; ?></td>
			<td class="key"><?php echo ucfirst($plugin['group']); ?></td>
			<td><strong style="color: <?php echo ($plugin['result'])? "green" : "red"?>"><?php echo ($plugin['result'])?'Installed':'Not installed'; ?></strong></td>
		</tr>
		<?php endforeach; ?>
		<?php endif; ?>
	</tbody>
</table>

<fieldset class="dmc-install">
	<p>
		Thank you for installing DMC Firewall within your Joomla powered website. DMC Firewall expands on our very successful 'Hacker Beware' script which was developed by Dean Marshall himself in 2006.
	</p>	
	<h2>History of Hacker Beware</h2>
	<p>
		Hacker Beware was developed to enhance security within the Joomla 1.0 branch. Joomla had standard 'blocking' where if a hacker tried to hack your website, the request was forbidden but didn't block the hacker so they could keep trying. With Hacker Beware installed those hack attempts would be recorded and the IP would be banned so they couldn't access your website which also meant that they couldn't keep attempting to hack your website. Hacker Beware was an external stand-alone script which required the webmaster to upload the script and manually make edits to the '.htaccess' file in order to improve security.
	</p>
	<h2>DMC Firewall</h2>
	<p>
		DMC Firewall brings Hacker Beware up-to-date and easy to manage. DMC Firewall also introduces a number of new features which aid in the protection of your website from notifying you if your Super Administrator accounts have 'weak password', the ability to change your Joomla installations database table prefix, configure the security settings with just a couple of clicks on your mouse.
	</p>
	<h2>Trouble Shooting</h2>	
	<p>
		We recommend reading all of the documentation related to DMC Firewall! Our documentation can be found by <a href="http://www.webdevelopmentconsultancy.com/joomla-extensions/dmc-firewall.html" target="_blank">clicking here</a>! If you require additional support, support subscriptions can be purchased which enables you to seven days of Pro support.
	</p>
	<p>Regards<br />Dean Marshall Consultancy Ltd<br /><br /><a href="http://www.deanmarshall.co.uk/" target="_blank">http://www.deanmarshall.co.uk/</a><br /><a href="http://www.webdevelopmentconsultancy.com/" target="_blank">http://www.webdevelopmentconsultancy.com/</a>
</fieldset>
<?php
	}
	
	private function _renderPostUninstallation($status, $firewallEdits, $parent){
		$rows = 1;
		$osCheck = $this->serverSoftware();
		
		switch($osCheck){
			case 'IIS':
				$serverOutputFile = 'web.config';
				break;
			case 'APA':
				$serverOutputFile = '.htaccess';
				break;
		}
?>
<style type="text/css">
.list-striped, .row-striped {
    border-top: 1px solid #DDDDDD;
    line-height: 18px;
    list-style: none outside none;
    margin-left: 0;
    text-align: left;
    vertical-align: middle;
}
fieldset.dmc-uninstall {border:1px solid #ddd !important; padding:3px !important; margin-bottom:15px !important; background-color:#eee !important;}
table.adminlist {margin-bottom:15px !important;}
table.adminlist,table.adminlist tr, table.adminlist th, table.adminlist td {border-collapse:collapse !important; border:1px solid #ddd !important;}
table.adminlist td, table.adminlist th { padding:4px !important; height:auto !important;}
table.adminlist tbody tr:nth-child(2n+1){background-color: #eeeeee !important;}
</style>
<h1 style="width: 50%; float: left; margin: 0px;">Thank you for using DMC Firewall!</h1>
<span style="float: right; margin-bottom: 10px;">
	<img src="http://www.dmc-svn.com/media/com_dmcfirewall/images/dmc-logo.png">
</span>

<table class="adminlist" style="width:100%;">
	<thead>
		<tr>
			<th class="title" colspan="2">Extension</th>
			<th width="40%">Status</th>
		</tr>
	</thead>
	<tfoot>
		<tr>
			<td colspan="3"></td>
		</tr>
	</tfoot>
	<tbody>
		<tr class="row0">
			<td class="key" colspan="2">DMC Firewall</td>
			<td><strong style="color: green">Removed</strong></td>
		</tr>
		<tr class="row1">
			<td class="key" colspan="2">'<?php echo $serverOutputFile; ?>' edits status</td>
			<td><strong><?php echo $firewallEdits; ?></strong></td>
		</tr>
		<?php if (count($status->modules)) : ?>
		<tr>
			<th><?php echo JText::_('Module'); ?></th>
			<th><?php echo JText::_('Client'); ?></th>
			<th></th>
		</tr>
		<?php foreach ($status->modules as $module) : ?>
		<tr class="row<?php echo (++ $rows % 2); ?>">
			<td class="key"><?php echo $module['name']; ?></td>
			<td class="key"><?php echo ucfirst($module['client']); ?></td>
			<td><strong style="color: <?php echo ($module['result'])? "green" : "red"?>"><?php echo ($module['result'])?JText::_('Removed'):JText::_('Not removed'); ?></strong></td>
		</tr>
		<?php endforeach;?>
		<?php endif;?>
		<?php if (count($status->plugins)) : ?>
		<tr>
			<th><?php echo JText::_('Plugin'); ?></th>
			<th width="30%"><?php echo JText::_('Group'); ?></th>
			<th></th>
		</tr>
		<?php foreach ($status->plugins as $plugin) : ?>
		<tr class="row<?php echo (++ $rows % 2); ?>">
			<td class="key"><?php echo $plugin['name']; ?></td>
			<td class="key"><?php echo ucfirst($plugin['group']); ?></td>
			<td><strong style="color: <?php echo ($plugin['result'])? "green" : "red"?>"><?php echo ($plugin['result'])?JText::_('Removed'):JText::_('Not removed'); ?></strong></td>
		</tr>
		<?php endforeach; ?>
		<?php endif; ?>
	</tbody>
</table>
<fieldset class="dmc-uninstall">
	<p>
		We are sorry to see that you have uninstalled DMC Firewall from your website. We hope that DMC Firewall provided sufficient protection to your Joomla powered website.
	</p>
	<p>We are always looking for ways to improve DMC Firewall so if you have any thoughts please let us know.</p>
	
	<p>Thank you for using DMC Firewall<br />Regards<br />Dean Marshall Consultancy Ltd<br /><br /><a href="http://www.deanmarshall.co.uk/" target="_blank">http://www.deanmarshall.co.uk/</a><br /><a href="http://www.webdevelopmentconsultancy.com/" target="_blank">http://www.webdevelopmentconsultancy.com/</a>
</fieldset>
<?php
	}
	
	/**
	 * Installs subextensions (modules, plugins) bundled with the main extension
	 * 
	 * @param JInstaller $parent 
	 * @return JObject The subextension installation status
	 */
	private function _installSubextensions($parent){
		$src = $parent->getParent()->getPath('source');
		
		$db = JFactory::getDbo();
		
		$status = new JObject();
		$status->modules = array();
		$status->plugins = array();
		
		// Modules installation
		if(count($this->installation_queue['modules'])){
			foreach($this->installation_queue['modules'] as $folder => $modules){
				if(count($modules)){
					foreach($modules as $module => $modulePreferences){
						// Install the module
						if(empty($folder)){
							$folder = 'site';
						}
						$path = "$src/modules/$folder/$module";
						if(!is_dir($path)){
							$path = "$src/modules/$folder/mod_$module";
						}
						if(!is_dir($path)){
							$path = "$src/modules/$module";
						}
						if(!is_dir($path)){
							$path = "$src/modules/mod_$module";
						}
						if(!is_dir($path)){
							continue;
						}
						
						// Was the module already installed?
						$sql = $db->getQuery(true)
							->select('COUNT(*)')
							->from('#__modules')
							->where($db->qn('module').' = '.$db->q('mod_'.$module));
						$db->setQuery($sql);
						$count = $db->loadResult();
						$installer = new JInstaller;
						$result = $installer->install($path);
						$status->modules[] = array(
							'name'=>'mod_'.$module,
							'client'=>$folder,
							'result'=>$result
						);
						// Modify where it's published and its published state
						if(!$count){
							// A. Position and state
							list($modulePosition, $modulePublished) = $modulePreferences;
							if($modulePosition == 'cpanel'){
								$modulePosition = 'cpanel';
							}
							$sql = $db->getQuery(true)
								->update($db->qn('#__modules'))
								->set($db->qn('position').' = '.$db->q($modulePosition))
								->where($db->qn('module').' = '.$db->q('mod_'.$module));
							if($modulePublished){
								$sql->set($db->qn('published').' = '.$db->q('1'));
							}
							$db->setQuery($sql);
							$db->query();
							
							// B. Change the ordering of back-end modules to 1 + max ordering
							if($folder == 'admin'){
								$query = $db->getQuery(true);
								$query->select('MAX('.$db->qn('ordering').')')
									->from($db->qn('#__modules'))
									->where($db->qn('position').'='.$db->q($modulePosition));
								$db->setQuery($query);
								$position = $db->loadResult();
								$position++;

								$query = $db->getQuery(true);
								$query->update($db->qn('#__modules'))
									->set($db->qn('ordering').' = '.$db->q($position))
									->where($db->qn('module').' = '.$db->q('mod_'.$module));
								$db->setQuery($query);
								$db->query();
							}
							
							// C. Link to all pages
							$query = $db->getQuery(true);
							$query->select('id')->from($db->qn('#__modules'))
								->where($db->qn('module').' = '.$db->q('mod_'.$module));
							$db->setQuery($query);
							$moduleid = $db->loadResult();

							$query = $db->getQuery(true);
							$query->select('*')->from($db->qn('#__modules_menu'))
								->where($db->qn('moduleid').' = '.$db->q($moduleid));
							$db->setQuery($query);
							$assignments = $db->loadObjectList();
							$isAssigned = !empty($assignments);
							if(!$isAssigned){
								$o = (object)array(
									'moduleid'	=> $moduleid,
									'menuid'	=> 0
								);
								$db->insertObject('#__modules_menu', $o);
							}
						}
					}
				}
			}
		}

		// Plugins installation
		if(count($this->installation_queue['plugins'])){
			foreach($this->installation_queue['plugins'] as $folder => $plugins){
				if(count($plugins)){
					foreach($plugins as $plugin => $published){
						$path = "$src/plugins/$folder/$plugin";
						if (!is_dir($path))
						{
							$path = "$src/plugins/$folder/plg_$plugin";
						}
						if (!is_dir($path))
						{
							$path = "$src/plugins/$plugin";
						}
						if (!is_dir($path))
						{
							$path = "$src/plugins/plg_$plugin";
						}
						if (!is_dir($path)) continue;

						// Was the plugin already installed?
						$query = $db->getQuery(true)
							->select('COUNT(*)')
							->from($db->qn('#__extensions'))
							->where($db->qn('element').' = '.$db->q($plugin))
							->where($db->qn('folder').' = '.$db->q($folder));
						$db->setQuery($query);
						$count = $db->loadResult();

						$installer = new JInstaller;
						$result = $installer->install($path);
						
						$status->plugins[] = array('name'=>'plg_'.$plugin,'group'=>$folder, 'result'=>$result);

						if ($published && !$count)
						{
							$query = $db->getQuery(true)
								->update($db->qn('#__extensions'))
								->set($db->qn('enabled').' = '.$db->q('1'))
								->where($db->qn('element').' = '.$db->q($plugin))
								->where($db->qn('folder').' = '.$db->q($folder));
							$db->setQuery($query);
							$db->query();
						}
					}
				}
			}
		}
		
		return $status;
	}
	
	/**
	 * Copies the CLI scripts into Joomla!'s cli directory
	 *
	 * @param JInstaller $parent
	 */
	private function _copyCliFiles($parent){
		$src = $parent->getParent()->getPath('source');

		JLoader::import("joomla.filesystem.file");
		JLoader::import("joomla.filesystem.folder");

		foreach($this->firewallCliScripts as $script){
			if(JFile::exists(JPATH_ROOT . '/cli/' . $script)){
				JFile::delete(JPATH_ROOT . '/cli/' . $script);
			}
			
			if(JFile::exists($src . '/cli/' . $script)){
				JFile::move($src . '/cli/' . $script, JPATH_ROOT . '/cli/' . $script);
			}
		}
	}
	
	/**
	 * Moves the backup of the server file to a secure location
	 *
	 * @param JInstaller $parent
	 */
	private function _moveServerFile($parent){
		require_once JPATH_ADMINISTRATOR . '/components/com_dmcfirewall/helpers/serversoftware.php';
		$osCheck = DmcfirewallHelperServersoftware::serverSoftware();
		
		switch($osCheck){
			case 'IIS':
				$serverFile = 'web.config';
			break;
			case 'APA':
				$serverFile = '.htaccess';
			break;
		}
		/* We are on a Windows server */
		if($osCheck == 'IIS'){
			/* No 'backup.web.config' file found so we will just continue */
			if(JFile::exists(JPATH_SITE . '/backup.web.config')){
				if(copy(JPATH_SITE . '/backup.web.config', JPATH_ADMINISTRATOR . '/components/com_dmcfirewall/backups/backup.web.config-' . date('Y-m-d-h-i-s'))){
					if(JFile::delete(JPATH_SITE . '/backup.web.config')){
						return 'copied/moved';
					}
					
					return 'copied';
				}else{
					return true;
				}
			}else{
				return 'no backup';
			}
		}//end of windows server
		elseif($osCheck == 'APA'){
			/* No 'backup.htaccess' file found so we will just continue */
			if(JFile::exists(JPATH_SITE . '/backup.htaccess')){
				if(copy(JPATH_SITE . '/backup.htaccess', JPATH_ADMINISTRATOR . '/components/com_dmcfirewall/backups/backup.htaccess-' . date('Y-m-d-h-i-s'))){
					if(JFile::delete(JPATH_SITE . '/backup.htaccess')){
						return 'copied/moved';
					}
					
					return 'copied';
				}else{
					return true;
				}
			}else{
				return 'no backup';
			}
		}
	}
	
	/**
	 * Uninstalls subextensions (modules, plugins) bundled with the main extension
	 * 
	 * @param JInstaller $parent 
	 * @return JObject The subextension uninstallation status
	 */
	private function _uninstallSubextensions($parent){
		jimport('joomla.installer.installer');
		
		$db = JFactory::getDBO();
		
		$status = new JObject();
		$status->modules = array();
		$status->plugins = array();
		
		$src = $parent->getParent()->getPath('source');

		// Modules uninstallation
		if(count($this->installation_queue['modules'])){
			foreach($this->installation_queue['modules'] as $folder => $modules){
				if(count($modules)) foreach($modules as $module => $modulePreferences){
					// Find the module ID
					$sql = $db->getQuery(true)
						->select($db->qn('extension_id'))
						->from($db->qn('#__extensions'))
						->where($db->qn('element').' = '.$db->q('mod_'.$module))
						->where($db->qn('type').' = '.$db->q('module'));
					$db->setQuery($sql);
					$id = $db->loadResult();
					// Uninstall the module
					if($id){
						$installer = new JInstaller;
						$result = $installer->uninstall('module',$id,1);
						$status->modules[] = array(
							'name'=>'mod_'.$module,
							'client'=>$folder,
							'result'=>$result
						);
					}
				}
			}
		}

		// Plugins uninstallation
		if(count($this->installation_queue['plugins'])){
			foreach($this->installation_queue['plugins'] as $folder => $plugins){
				if(count($plugins)) foreach($plugins as $plugin => $published){
					$sql = $db->getQuery(true)
						->select($db->qn('extension_id'))
						->from($db->qn('#__extensions'))
						->where($db->qn('type').' = '.$db->q('plugin'))
						->where($db->qn('element').' = '.$db->q($plugin))
						->where($db->qn('folder').' = '.$db->q($folder));
					$db->setQuery($sql);

					$id = $db->loadResult();
					if($id){
						$installer = new JInstaller;
						$result = $installer->uninstall('plugin',$id,1);
						$status->plugins[] = array(
							'name'=>'plg_'.$plugin,
							'group'=>$folder,
							'result'=>$result
						);
					}			
				}
			}
		}
		
		return $status;
	}
	
	/*
	 * Install Akeeba FOF
	 */
	/*
	private function _installFOF($parent){
		$src = $parent->getParent()->getPath('source');

		// Install the FOF framework
		JLoader::import('joomla.filesystem.folder');
		JLoader::import('joomla.filesystem.file');
		JLoader::import('joomla.utilities.date');
		$source = $src . '/fof';

		if(!defined('JPATH_LIBRARIES')){
			$target = JPATH_ROOT . '/libraries/fof';
		}else{
			$target = JPATH_LIBRARIES . '/fof';
		}

		$haveToInstallFOF = false;

		if(!JFolder::exists($target)){
			$haveToInstallFOF = true;
		}else{
			$fofVersion = array();

			if(JFile::exists($target . '/version.txt')){
				$rawData				 = JFile::read($target . '/version.txt');
				$info					 = explode("\n", $rawData);
				$fofVersion['installed'] = array(
					'version'	 => trim($info[0]),
					'date'		 => new JDate(trim($info[1]))
				);
			}else{
				$fofVersion['installed'] = array(
					'version'	 => '0.0',
					'date'		 => new JDate('2011-01-01')
				);
			}

			$rawData				 = JFile::read($source . '/version.txt');
			$info					 = explode("\n", $rawData);

			$fofVersion['package']	 = array(
				'version'	 => trim($info[0]),
				'date'		 => new JDate(trim($info[1]))
			);

			$haveToInstallFOF = $fofVersion['package']['date']->toUNIX() > $fofVersion['installed']['date']->toUNIX();

			// Do not install FOF on Joomla! 3.2.0 beta 1 or later
			if(version_compare(JVERSION, '3.1.999', 'gt')){
				$haveToInstallFOF = false;
			}
		}

		$installedFOF = false;

		if($haveToInstallFOF){
			$versionSource	 = 'package';
			$installer		 = new JInstaller;
			$installedFOF	 = $installer->install($source);
		}else{
			$versionSource = 'installed';
		}

		if(!isset($fofVersion)){
			$fofVersion = array();

			if(JFile::exists($target . '/version.txt')){
				$rawData				 = JFile::read($target . '/version.txt');
				$info					 = explode("\n", $rawData);
				$fofVersion['installed'] = array(
					'version'	 => trim($info[0]),
					'date'		 => new JDate(trim($info[1]))
				);
			}else{
				$fofVersion['installed'] = array(
					'version'	 => '0.0',
					'date'		 => new JDate('2011-01-01')
				);
			}

			$rawData				 = JFile::read($source . '/version.txt');
			$info					 = explode("\n", $rawData);

			$fofVersion['package']	 = array(
				'version'	 => trim($info[0]),
				'date'		 => new JDate(trim($info[1]))
			);

			$versionSource			 = 'installed';
		}

		if(!($fofVersion[$versionSource]['date'] instanceof JDate)){
			$fofVersion[$versionSource]['date'] = new JDate();
		}

		return array(
			'required'	 => $haveToInstallFOF,
			'installed'	 => $installedFOF,
			'version'	 => $fofVersion[$versionSource]['version'],
			'date'		 => $fofVersion[$versionSource]['date']->format('Y-m-d'),
		);
	}
	*/
	
	/*
	 * Install Akeeba Strapper
	 * NOTE Removed 1.4.1
	 * /
	private function _installStraper($parent){
		$src = $parent->getParent()->getPath('source');

		// Install the FOF framework
		JLoader::import('joomla.filesystem.folder');
		JLoader::import('joomla.filesystem.file');
		JLoader::import('joomla.utilities.date');
		$source	 = $src . '/strapper';
		$target	 = JPATH_ROOT . '/media/akeeba_strapper';

		$haveToInstallStraper = false;

		if(!JFolder::exists($target)){
			$haveToInstallStraper = true;
		}else{
			$straperVersion = array();

			if(JFile::exists($target . '/version.txt')){
				$rawData					 = JFile::read($target . '/version.txt');
				$info						 = explode("\n", $rawData);
				$straperVersion['installed'] = array(
					'version'	 => trim($info[0]),
					'date'		 => new JDate(trim($info[1]))
				);
			}else{
				$straperVersion['installed'] = array(
					'version'	 => '0.0',
					'date'		 => new JDate('2011-01-01')
				);
			}

			$rawData					 = JFile::read($source . '/version.txt');
			$info						 = explode("\n", $rawData);
			$straperVersion['package']	 = array(
				'version'	 => trim($info[0]),
				'date'		 => new JDate(trim($info[1]))
			);

			$haveToInstallStraper = $straperVersion['package']['date']->toUNIX() > $straperVersion['installed']['date']->toUNIX();
		}

		$installedStraper = false;

		if($haveToInstallStraper){
			$versionSource		 = 'package';
			$installer			 = new JInstaller;
			$installedStraper	 = $installer->install($source);
		}else{
			$versionSource = 'installed';
		}

		if(!isset($straperVersion)){
			$straperVersion = array();

			if(JFile::exists($target . '/version.txt')){
				$rawData					 = JFile::read($target . '/version.txt');
				$info						 = explode("\n", $rawData);
				$straperVersion['installed'] = array(
					'version'	 => trim($info[0]),
					'date'		 => new JDate(trim($info[1]))
				);
			}else{
				$straperVersion['installed'] = array(
					'version'	 => '0.0',
					'date'		 => new JDate('2011-01-01')
				);
			}
			
			$rawData					 = JFile::read($source . '/version.txt');
			$info						 = explode("\n", $rawData);
			$straperVersion['package']	 = array(
				'version'	 => trim($info[0]),
				'date'		 => new JDate(trim($info[1]))
			);
			$versionSource				 = 'installed';
		}

		if(!($straperVersion[$versionSource]['date'] instanceof JDate)){
			$straperVersion[$versionSource]['date'] = new JDate();
		}

		return array(
			'required'	 => $haveToInstallStraper,
			'installed'	 => $installedStraper,
			'version'	 => $straperVersion[$versionSource]['version'],
			'date'		 => $straperVersion[$versionSource]['date']->format('Y-m-d'),
		);
	}
	*/
	
	/*
	 * Revert our edits to the '.htaccess' or 'web.config' file - keeping the IP block section
	 */
	private function revertServerFileEdits(){
		require_once JPATH_ADMINISTRATOR . '/components/com_dmcfirewall/helpers/serversoftware.php';
		$osCheck = DmcfirewallHelperServersoftware::serverSoftware();
		
		switch($osCheck){
			case 'IIS':
				$serverFile = 'web.config';
			break;
			case 'APA':
				$serverFile = '.htaccess';
			break;
		}
		/* We are on a Windows server */
		if($osCheck == 'IIS'){
			/* No 'web.config' file found so we will add a standard Joomla! file */
			if(!JFile::exists(JPATH_SITE.'/web.config')){
				if(copy(JPATH_ADMINISTRATOR . '/components/com_dmcfirewall/assets/servertype/iis/joomla.web.config', JPATH_SITE . '/web.config')){
					return "<span style=\"color:green;\">You didn't have a 'web.config' file so we have added a Joomla standard one!</span>";
				}else{
					return "<span style=\"color:red;\">Something went wrong and we couldn't add a 'web.config' file to your web-space! Attention may be needed!</span>";
				}
			}else{
				$webConfigContents 			= $this->file_contents_read(JPATH_SITE . '/web.config');
				$joomlaStandardCode =<<<JOOMLASTANDARDWEBCONFIGCONTENTS
<rule name="Joomla! Rule 1" stopProcessing="true">
                   <match url="^(.*)$" ignoreCase="false" />
                   <conditions logicalGrouping="MatchAny">
                       <add input="{QUERY_STRING}" pattern="base64_encode[^(]*\([^)]*\)" ignoreCase="false" />
                       <add input="{QUERY_STRING}" pattern="(&gt;|%3C)([^s]*s)+cript.*(&lt;|%3E)" />
                       <add input="{QUERY_STRING}" pattern="GLOBALS(=|\[|\%[0-9A-Z]{0,2})" ignoreCase="false" />
                       <add input="{QUERY_STRING}" pattern="_REQUEST(=|\[|\%[0-9A-Z]{0,2})" ignoreCase="false" />
                   </conditions>
                   <action type="CustomResponse" url="index.php" statusCode="403" statusReason="Forbidden" statusDescription="Forbidden" />
               </rule>
JOOMLASTANDARDWEBCONFIGCONTENTS;
				
				$webConfigOutput 			= preg_replace('@<!-- DMC Firewall removed standard Joomla blocking! -->@i', $joomlaStandardCode, $webConfigContents);
				$webConfigEdits				= $this->file_contents_write(JPATH_SITE . '/web.config', $webConfigOutput, false);
				
				if($webConfigEdits == 'edits-made'){
					return "<span style=\"color:green;\">We successfully removed our edits from your 'web.config' file!<br />The IP addresses that have been banned by DMC Firewall are still in-place and won't be able to access your website.</span>";
				}else{
					return '<span style="color:red;">We couldn\'t add Joomla\'s standard blocking functionality to your \'web.config\' file!';
				}
			}
		}//end of windows server
		elseif($osCheck == 'APA'){
			/* No '.htaccess' file found so we will add a standard Joomla! file */
			if(!JFile::exists(JPATH_SITE.'/.htaccess')){
				if(copy(JPATH_ADMINISTRATOR . '/components/com_dmcfirewall/assets/servertype/apache/joomla.htaccess', JPATH_SITE . '/.htaccess')){
					return "<span style=\"color:green;\">You didn't have a '.htaccess' file so we have added a Joomla standard one!</span>";
				}else{
					return "<span style=\"color:red;\">Something went wrong and we couldn't add a '.htaccess' file to your web-space! Attention may be needed!</span>";
				}
			}else{
				$htaccessFileContents 		= $this->file_contents_read(JPATH_SITE . '/.htaccess');
				$joomlaStandardCode =<<<JOOMLASTANDARDHTACCESSONTENTS
## Begin - Rewrite rules to block out some common exploits.
# If you experience problems on your site block out the operations listed below
# This attempts to block the most common type of exploit `attempts` to Joomla!
#
# Block out any script trying to base64_encode data within the URL.
RewriteCond %{QUERY_STRING} base64_encode[^(]*\([^)]*\) [OR]
# Block out any script that includes a <script> tag in URL.
RewriteCond %{QUERY_STRING} (<|%3C)([^s]*s)+cript.*(>|%3E) [NC,OR]
# Block out any script trying to set a PHP GLOBALS variable via URL.
RewriteCond %{QUERY_STRING} GLOBALS(=|\[|\%[0-9A-Z]{0,2}) [OR]
# Block out any script trying to modify a _REQUEST variable via URL.
RewriteCond %{QUERY_STRING} _REQUEST(=|\[|\%[0-9A-Z]{0,2})
# Return 403 Forbidden header and show the content of the root homepage
RewriteRule .* index.php [F]
#
## End - Rewrite rules to block out some common exploits.
JOOMLASTANDARDHTACCESSONTENTS;
				
				$htaccessOutput 			= preg_replace('@### DMC Firewall - Removed Joomla bad requests htaccess block ###@i', $joomlaStandardCode, $htaccessFileContents);
				$htaccessEdits				= $this->file_contents_write(JPATH_SITE . '/.htaccess', $htaccessOutput, false);
				
				if($htaccessEdits == 'edits-made'){
					return "<span style=\"color:green;\">We successfully removed our edits from your '.htaccess' file!<br />The IP addresses that have been banned by DMC Firewall are still in-place and won't be able to access your website.</span>";
				}else{
					return '<span style="color:red;">We couldn\'t add Joomla\'s standard blocking functionality to your \'.htaccess\' file!';
				}
			}
		}//end of linux server
		/* We have no idea what server we are on so don't do anything! */
		else{
			return '<span style="color:red;">We couldn\'t revert our edits to your \'' . $serverFile . '\' file!</span>';
		}
	}
	
	/**
	 * Make the edits to the '.htaccess' or 'web.config' file
	 */
	private function _serverFileEdits(){
		require_once JPATH_ADMINISTRATOR . '/components/com_dmcfirewall/helpers/serversoftware.php';
		$osCheck = DmcfirewallHelperServersoftware::serverSoftware();
		
		/* We are on a Windows server */
		if($osCheck == 'IIS'){
			/* No 'web.config' file found so we will add our own customised file */
			if(!JFile::exists(JPATH_SITE.'/web.config')){
				if(copy(JPATH_ADMINISTRATOR . '/components/com_dmcfirewall/assets/servertype/iis/firewall.webconfig', JPATH_SITE . '/web.config')){
					return "<span style=\"color:green;\">You didn't have a 'web.config' so we have added our own!</span>";
				}else{
					return "<span style=\"color:red;\">We couldn't create a 'web.config' file for you - ATTENTION REQUIRED!</span>";
				}
			}//end of no 'web.config'
			else{
				$webconfigContents 			= $this->file_contents_read(JPATH_SITE . '/web.config');
				$webconfigFirstLine			= '<rule name="Joomla! Rule 1" stopProcessing="true">';
				$webconfigLastline			= '</rule>';
				
				if(stripos($webconfigContents, "<!-- DMC Firewall removed standard Joomla blocking! -->") !== FALSE){
					return '<span style="color:orange;">Edits previously made to your \'web.config\' file!</span>';
				}else{
					copy(JPATH_SITE . '/web.config', JPATH_ADMINISTRATOR . '/components/com_dmcfirewall/backups/backup.web.config-' . date('Y-m-d-h-i-s'));
					
					if(!is_readable(JPATH_SITE . '/web.config')){
						return '<span style="color:red;">You have a \'web.config\' file but I couldn\'t read it to make my edits! Attention required!</span>';
					}
					
					if(!is_writeable(JPATH_SITE . '/web.config')){
						if(!chmod(JPATH_SITE . '/web.config', 0744)){
							return '<span style="color:red;">You have a \'web.config\' file, I can read it but I couldn\'t write to it! Attention required!</span>';
						}else{
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
					$webconfigEditsMade = $this->file_contents_write(JPATH_SITE.'/web.config', $webconfigPerformReplace, false);
				
					//make sure we chmod the file back
					if($webconfigWritable){
						chmod(JPATH_SITE . '/web.config', 0500);
					}
					
					//pass back a responce to the user!
					if($webconfigEditsMade == 'edits-made'){
						return '<span style="color:green;">We have successfully made the edits to your \'web.config\' file!</span>';
					}else{
						return '<span style="color:red;">We couldn\'t make the edits to your \'web.config\' file - attention required!</span>';
					}
				}
			}//end of else
		}
		/* We are on a Linux server */
		elseif($osCheck == 'APA'){
			/* No '.htaccess' file found so we will add our own customised file */
			if(!JFile::exists(JPATH_SITE.'/.htaccess')){
				if(copy(JPATH_ADMINISTRATOR . '/components/com_dmcfirewall/assets/servertype/apache/firewall.htaccess', JPATH_SITE . '/.htaccess')){
					return "<span style=\"color:green;\">You didn't have a '.htaccess' so we have added our own!</span>";
				}else{
					return "<span style=\"color:red;\">We couldn't create a '.htaccess' file for you - ATTENTION REQUIRED!</span>";
				}
			}//end of no '.htaccess'
			else{
				$htaccessWritable = false;
				copy(JPATH_SITE . '/.htaccess', JPATH_ADMINISTRATOR . '/components/com_dmcfirewall/backups/backup.htaccess-' . date('Y-m-d-h-i-s'));
				
				if(!is_readable(JPATH_SITE . '/.htaccess')){
					return '<span style="color:red;">You have a \'.htaccess\' file but I couldn\'t read it to make my edits! Attention required!</span>';
				}
				
				if(!is_writeable(JPATH_SITE . '/.htaccess')){
					if(!chmod(JPATH_SITE . '/.htaccess', 0644)){
						return '<span style="color:red;">You have a \'.htaccess\' file, I can read it but I couldn\'t write to it! Attention required!</span>';
					}else{
						$htaccessWritable = true;
					}
				}
				
				$htaccessEditsFlag			= 0;
				$htaccessContents 			= $this->file_contents_read(JPATH_SITE . '/.htaccess');
				$htaccessStarBlock			= '## Begin - Rewrite rules to block out some common exploits';
				$htaccessEndBlock			= '## End - Rewrite rules to block out some common exploits';
				$htaccessRegex				= "/^" . preg_quote( $htaccessStarBlock, '/') .".*?". preg_quote( $htaccessEndBlock, '/') . "/sm";
				$htaccessPregReplace		= preg_replace ($htaccessRegex, "### DMC Firewall - Removed Joomla bad requests htaccess block ###", $htaccessContents);
				
				if(stripos($htaccessContents, "### DMC Firewall - Removed Joomla bad requests htaccess block ###") !== FALSE &&
					stripos($htaccessContents, "<Limit ") !== FALSE){
					return '<span style="color:orange;">Edits previously made to your \'.htaccess\' file!</span>';
				}else{
					if(stripos($htaccessContents, "### DMC Firewall - Removed Joomla bad requests htaccess block ###") === FALSE){
						$htaccessPregReplaceCallBack = $this->file_contents_write(JPATH_SITE . '/.htaccess', $htaccessPregReplace, false);
					
						if($htaccessPregReplaceCallBack == 'edits-unsuccessful'){
							$this->htaccessEditsFlag = 1;//error
						}else{
							$this->htaccessEditsFlag = 2;//successful
						}
					}else{
						$this->htaccessEditsFlag = 2;
					}
					// we keep the edits separate (no if elseif)
					$htaccessContentsFresh			= $this->file_contents_read(JPATH_SITE . '/.htaccess');
					
					if(stripos($htaccessContentsFresh, "<Limit ") === FALSE){
						//no limit
						$limitEditsBlock =<<<LIMIT_SECTION

<Limit GET POST>
order allow,deny
allow from all

</Limit>
LIMIT_SECTION;
						$limitEdits = $this->file_contents_write(JPATH_SITE . '/.htaccess', $limitEditsBlock, true);
						
						if($limitEdits == 'edits-unsuccessful'){
							$this->htaccessEditsFlag = 1;
						}else{
							$this->htaccessEditsFlag += 1;
						}						
					}else{
						$this->htaccessEditsFlag += 1;
					}
					
					//make sure we chmod the file back
					if($htaccessWritable){
						chmod(JPATH_SITE . '/.htaccess', 0444);
					}
					
					if($this->htaccessEditsFlag == 3){
						return '<span style="color:green;">We have successfully edited your \'.htaccess\' file!</span>';
					}elseif($this->htaccessEditsFlag = 2){
						return '<span style="color:red;">Something went wrong while we were making edits to your \'.htaccess\' file! Attention needed!</span>';
					}else{
						return '<span style="color:red;">We couldn\'t edit your \'.htaccess\' file! Attention needed!</span>';
					}
				}
			}
		}
		/* We have no idea what server we are on so don't do anything! */
		else{
			return '<span style="color:red;">We couldn\'t identify your server, please check the \'Configuration\' tab within DMC Firewall!</span>';
		}
	}
	
	/**
	 * Disable our DMC Login plugin and re-enable Joomla's core authentication plugin
	 */
	private function _disableLoginPlugin(){
		$db = JFactory::getDBO();
		
		$query = "UPDATE `#__extensions` SET `enabled` = 1 WHERE `name` = 'plg_authentication_joomla' AND `type` = 'plugin' AND `element` = 'joomla' AND `folder` = 'authentication' LIMIT 1";
		$db->setQuery($query);
		$db->query();
	
	}
	
	/**
	 * Enable DMC Login plugin if PRO
	 */
	private function _enableLoginPlugin(){
		if(
				(JFile::exists(JPATH_BASE . '/components/com_dmcfirewall/models/dbchanger.php')) &&
				(JFile::exists(JPATH_SITE . '/plugins/authentication/dmclogin/dmclogin.xml'))
			){
			$db = JFactory::getDBO();
			
			$query = "SELECT * FROM `#__extensions` WHERE `type` = 'plugin' AND `element` = 'dmclogin' AND `folder` = 'authentication' LIMIT 1";
			$db->setQuery($query);
			$db->query();
			$countLoginPlugin = $db->getNumRows();
			$loginPlugin = $db->loadAssoc();
			
			if($countLoginPlugin && $loginPlugin['enabled'] == 1){
				$query = "SELECT * FROM `#__extensions` WHERE `enabled` = 1 AND `type` = 'plugin' AND `folder` = 'authentication' AND `element` != 'dmclogin'";
				$db->setQuery($query);
				$db->query();
				$countPlugins = $db->getNumRows();
				$loginPlugins = $db->loadAssocList();
				
				if($countPlugins){
					foreach($loginPlugins as $plugin){
						$query = "UPDATE `#__extensions` SET `enabled` = 0 WHERE `extension_id` = '" . $plugin['extension_id'] . "'";
						$db->setQuery($query);
						$db->query();
					}
				}
			}
		}else{
			return 'NOT-PRO';
		}
	}
	
	/**
	 *
	 */
	private function file_contents_read($filename){
		$contents = '';
		$fp = fopen ($filename, "r");
		if($fp){
			$startTime = microtime();
			do{
				$canRead = flock($fp, LOCK_EX);
				// If lock not obtained sleep for 0 - 100 milliseconds, to avoid collision and CPU load
				if(!$canRead){
					usleep(round(rand(0, 100)*1000));
				}
			}
			while((!$canRead)and((microtime()-$startTime) < 1000));
			
			//file was locked so now we can store information
			if($canRead){
				$contents = fread($fp, filesize($filename));
			}
			fclose ($fp); 
		}else{
			$contents = '';
		}
		
		return $contents;
	}
	
	/**
	 *
	 */
	private function file_contents_write($filename, $contents, $append=true){
		$method = ($append) ? 'a' : 'w';
	
		// waiting until file will be locked for writing (1000 milliseconds as timeout)
		if($fp = fopen($filename, $method)){
			$startTime = microtime();
			do{
				$canWrite = flock($fp, LOCK_EX);
				// If lock not obtained sleep for 0 - 100 milliseconds, to avoid collision and CPU load
				if(!$canWrite) usleep(round(rand(0, 100)*1000));
		
			}
			while((!$canWrite)and((microtime()-$startTime) < 1000));

			//file was locked so now we can store information
			if($canWrite){
				fwrite($fp, $contents);
			}
			fclose($fp);
			
			return 'edits-made';
		}else{
			return 'edits-unsuccessful';
		}
	}
	
	/**
	 * Removes obsolete files and folders
	 *
	 * @param array $removeFilesFolders
	 */
	private function _removeObsoleteFilesAndFolders($removeFilesFolders){
		// Remove files
		jimport('joomla.filesystem.file');
		if(!empty($removeFilesFolders['files'])){
			foreach($removeFilesFolders['files'] as $file){
				$f = JPATH_ROOT . '/' . $file;
				if(!JFile::exists($f)){
					continue;
				}else{
					JFile::delete($f);
				}
			}
        }

		// Remove folders
		jimport('joomla.filesystem.file');
		if(!empty($removeFilesFolders['folders'])){
			foreach($removeFilesFolders['folders'] as $folder){
				$f = JPATH_ROOT . '/' . $folder;
				if(!JFolder::exists($f)){
					continue;
				}else{
					JFolder::delete($f);
				}
			}
		}
	}
	
	/**
	 * Joomla! 1.6+ bugfix for "DB function returned no error"
	 */
	private function _bugfixDBFunctionReturnedNoError(){
		$db = JFactory::getDbo();
		
		// Fix broken #__assets records
		$query = $db->getQuery(true);
		$query->select('id')
			->from('#__assets')
			->where($db->qn('name').' = '.$db->q($this->_dmcfirewall_extension));
		$db->setQuery($query);
		$ids = $db->loadColumn();
		if(!empty($ids)) foreach($ids as $id){
			$query = $db->getQuery(true);
			$query->delete('#__assets')
				->where($db->qn('id').' = '.$db->q($id));
			$db->setQuery($query);
			$db->query();
		}

		// Fix broken #__extensions records
		$query = $db->getQuery(true);
		$query->select('extension_id')
			->from('#__extensions')
			->where($db->qn('element').' = '.$db->q($this->_dmcfirewall_extension));
		$db->setQuery($query);
		$ids = $db->loadColumn();
		if(!empty($ids)) foreach($ids as $id){
			$query = $db->getQuery(true);
			$query->delete('#__extensions')
				->where($db->qn('extension_id').' = '.$db->q($id));
			$db->setQuery($query);
			$db->query();
		}

		// Fix broken #__menu records
		$query = $db->getQuery(true);
		$query->select('id')
			->from('#__menu')
			->where($db->qn('type').' = '.$db->q('component'))
			->where($db->qn('menutype').' = '.$db->q('main'))
			->where($db->qn('link').' LIKE '.$db->q('index.php?option='.$this->_dmcfirewall_extension));
		$db->setQuery($query);
		$ids = $db->loadColumn();
		if(!empty($ids)) foreach($ids as $id){
			$query = $db->getQuery(true);
			$query->delete('#__menu')
				->where($db->qn('id').' = '.$db->q($id));
			$db->setQuery($query);
			$db->query();
		}
	}
	
	/** Akeeba Backup Fix Schema Version
	 * When you are upgrading from an old version of the component or when your
	 * site is upgraded from Joomla! 1.5 there is no "schema version" for our
	 * component's tables. As a result Joomla! doesn't run the database queries
	 * and you get a broken installation.
	 *
	 * This method detects this situation, forces a fake schema version "0.0.1"
	 * and lets the crufty mess Joomla!'s extensions installer is to bloody work
	 * as anyone would have expected it to do!
	 */
	private function _fixSchemaVersion(){
		// Get the extension ID
		$db = JFactory::getDbo();

		$query = $db->getQuery(true);
		$query->select('extension_id')
			->from('#__extensions')
			->where($db->qn('element').' = '.$db->q($this->_dmcfirewall_extension));
		$db->setQuery($query);
		$eid = $db->loadResult();

		$query = $db->getQuery(true);
		$query->select('version_id')
			->from('#__schemas')
			->where('extension_id = ' . $eid);
		$db->setQuery($query);
		$version = $db->loadResult();

		if(!$version){
			// No schema version found. Fix it.
			$o = (object)array(
				'version_id'	=> '0.0.1-2007-08-15',
				'extension_id'	=> $eid,
			);
			$db->insertObject('#__schemas', $o);
		}
	}
	
	/**
	 * Let's say that a user tries to install a component and it somehow fails
	 * in a non-graceful manner, e.g. a server timeout error, going over the
	 * quota etc. In this case the component's administrator directory is
	 * created and not removed (because the installer died an untimely death).
	 * When the user retries installing the component JInstaller sees that and
	 * thinks it's an update. This causes it to neither run the installation SQL
	 * file (because it's not supposed to run on extension update) nor the
	 * update files (because there is no schema version defined). As a result
	 * the files are installed, the database tables are not, the component is
	 * broken and I have to explain to non-technical users how to edit their
	 * database with phpMyAdmin.
	 *
	 * This method detects this stupid situation and attempts to execute the
	 * installation file instead.
	 */
	private function _fixBrokenSQLUpdates($parent){
		// Get the extension ID
		$db = JFactory::getDbo();

		$query = $db->getQuery(true);
		$query->select('extension_id')
			->from('#__extensions')
			->where($db->qn('element').' = '.$db->q($this->_dmcfirewall_extension));
		$db->setQuery($query);
		$eid = $db->loadResult();

		// Get the schema version
		$query = $db->getQuery(true);
		$query->select('version_id')
			->from('#__schemas')
			->where('extension_id = ' . $eid);
		$db->setQuery($query);
		$version = $db->loadResult();

		// If there is a schema version it's not a false update
		if($version){
			return;
		}

		// Execute the installation SQL file. Since I don't have access to
		// the manifest, I will improvise (again!)
		$dbDriver = strtolower($db->name);

		if($dbDriver == 'mysqli'){
			$dbDriver = 'mysql';
		}elseif($dbDriver == 'sqlsrv'){
			$dbDriver = 'sqlazure';
		}

		// Get the name of the sql file to process
		$sqlfile = $parent->getParent()->getPath('extension_root') . 'admin/sql/install/' . $dbDriver . '/install.sql';
		if(file_exists($sqlfile)){
			$buffer = file_get_contents($sqlfile);
			if($buffer === false){
				return;
			}

			$queries = JInstallerHelper::splitSql($buffer);

			if(count($queries) == 0){
				// No queries to process
				return;
			}

			// Process each query in the $queries array (split out of sql file).
			foreach($queries as $query){
				$query = trim($query);

				if($query != '' && $query{0} != '#'){
					$db->setQuery($query);

					if(!$db->execute()){
						JError::raiseWarning(1, JText::sprintf('JLIB_INSTALLER_ERROR_SQL_ERROR', $db->stderr(true)));

						return false;
					}
				}
			}
		}

		// Update #__schemas to the latest version. Again, since I don't have
		// access to the manifest I have to improvise...
		$path = $parent->getParent()->getPath('extension_root') . '/sql/update/' . $dbDriver;
		$files = str_replace('.sql', '', JFolder::files($path, '\.sql$'));
		if(count($files) > 0){
			usort($files, 'version_compare');
			$version = array_pop($files);
		}else{
			$version = '0.0.1-2007-08-15';
		}

		$query = $db->getQuery(true);
		$query->insert($db->quoteName('#__schemas'));
		$query->columns(array($db->quoteName('extension_id'), $db->quoteName('version_id')));
		$query->values($eid . ', ' . $db->quote($version));
		$db->setQuery($query);
		$db->execute();
	}
	
	/**
	 * Joomla! 1.6+ bugfix for "Can not build admin menus"
	 */
	private function _bugfixCantBuildAdminMenus(){
		$db = JFactory::getDbo();
		
		// If there are multiple #__extensions record, keep one of them
		$query = $db->getQuery(true);
		$query->select('extension_id')
			->from('#__extensions')
			->where($db->qn('element').' = '.$db->q($this->_dmcfirewall_extension));
		$db->setQuery($query);
		$ids = $db->loadColumn();
		if(count($ids) > 1){
			asort($ids);
			$extension_id = array_shift($ids); // Keep the oldest id
			
			foreach($ids as $id){
				$query = $db->getQuery(true);
				$query->delete('#__extensions')
					->where($db->qn('extension_id').' = '.$db->q($id));
				$db->setQuery($query);
				$db->query();
			}
		}
		
		// @todo
		
		// If there are multiple assets records, delete all except the oldest one
		$query = $db->getQuery(true);
		$query->select('id')
			->from('#__assets')
			->where($db->qn('name').' = '.$db->q($this->_dmcfirewall_extension));
		$db->setQuery($query);
		$ids = $db->loadObjectList();
		if(count($ids) > 1){
			asort($ids);
			$asset_id = array_shift($ids); // Keep the oldest id
			
			foreach($ids as $id){
				$query = $db->getQuery(true);
				$query->delete('#__assets')
					->where($db->qn('id').' = '.$db->q($id));
				$db->setQuery($query);
				$db->query();
			}
		}

		// Remove #__menu records for good measure!
		$query = $db->getQuery(true);
		$query->select('id')
			->from('#__menu')
			->where($db->qn('type').' = '.$db->q('component'))
			->where($db->qn('menutype').' = '.$db->q('main'))
			->where($db->qn('link').' LIKE '.$db->q('index.php?option='.$this->_dmcfirewall_extension));
		$db->setQuery($query);
		$ids1 = $db->loadColumn();
		if (empty($ids1)) $ids1 = array();
		$query = $db->getQuery(true);
		$query->select('id')
			->from('#__menu')
			->where($db->qn('type').' = '.$db->q('component'))
			->where($db->qn('menutype').' = '.$db->q('main'))
			->where($db->qn('link').' LIKE '.$db->q('index.php?option='.$this->_dmcfirewall_extension.'&%'));
		$db->setQuery($query);
		$ids2 = $db->loadColumn();
		if (empty($ids2)) $ids2 = array();
		$ids = array_merge($ids1, $ids2);
		if (!empty($ids)) foreach($ids as $id)
		{
			$query = $db->getQuery(true);
			$query->delete('#__menu')
				->where($db->qn('id').' = '.$db->q($id));
			$db->setQuery($query);
			$db->query();
		}
	}
	
	/**
	 * Deletes the Live Update information, forcing its reload during the first
	 * run of the component. This makes sure that the Live Update doesn't show
	 * an update available right after installing the component.
	 */
	private function _resetLiveUpdate(){
		// Load the component parameters, not using JComponentHelper to avoid conflicts ;)
		JLoader::import('joomla.html.parameter');
		JLoader::import('joomla.application.component.helper');
		$db			 = JFactory::getDbo();
		$sql		 = $db->getQuery(true)
			->select($db->qn('params'))
			->from($db->qn('#__extensions'))
			->where($db->qn('type') . ' = ' . $db->q('component'))
			->where($db->qn('element') . ' = ' . $db->q($this->_dmcfirewall_extension));
		$db->setQuery($sql);

		try{
			$rawparams	 = $db->loadResult();
		}catch(Exception $exc){
			return;
		}

		$params		 = new JRegistry();

		if(version_compare(JVERSION, '3.0', 'ge')){
			$params->loadString($rawparams, 'JSON');
		}else{
			$params->loadJSON($rawparams);
		}

		// Reset the liveupdate key
		$params->set('liveupdate', null);

		// Save the modified component parameters
		$data	 = $params->toString();
		$sql	 = $db->getQuery(true)
			->update($db->qn('#__extensions'))
			->set($db->qn('params') . ' = ' . $db->q($data))
			->where($db->qn('type') . ' = ' . $db->q('component'))
			->where($db->qn('element') . ' = ' . $db->q($this->_dmcfirewall_extension));

		$db->setQuery($sql);

		try{
			$db->execute();
		}catch(Exception $exc){
			// Nothing
		}
	}
	
	/*
	 * 'mod_security' prevents users from saving the Global Configuration for DMC Firewall because of the
	 * data that is stored within 'hackAttempts, sqlInjections' - therefore we need to take their current
	 * settings and base64_encode them.
	 */
	private function _modifySavedConfigForModSecurity(){
		$db = JFactory::getDbo();
		$sql = $db->getQuery(true)
			->select($db->qn('params'))
			->from($db->qn('#__extensions'))
			->where($db->qn('type') . ' = ' . $db->q('component'))
			->where($db->qn('element') . ' = ' . $db->q($this->_dmcfirewall_extension));
		$db->setQuery($sql);

		try{
			$rawparams		= $db->loadResult();
		}catch(Exception $exc){
			return;
		}
		
		$params		 		= new JRegistry();

		if(version_compare(JVERSION, '3.0', 'ge')){
			$params->loadString($rawparams, 'JSON');
		}else{
			$params->loadJSON($rawparams);
		}
		
		$hackAttempts	= $params->get('hackAttempts', 'mosConfig_;proc/self/;proc/self/environ%0000;_REQUEST;GLOBALS;base64_encode;%0000;.txt?;../../../;path=http://');
		$sqlInjections	= $params->get('sqlInjections', '/**/;UNION+SELECT;union all select;#__users;jos_users;concat(;0x26;0x25;0x3a5f;0x5f3a');
		
		$params->set('hackAttempts', base64_encode($hackAttempts));
		$params->set('sqlInjections', base64_encode($sqlInjections));
		
		$data = $params->toString();
		$sql = $db->getQuery(true)
			->update($db->qn('#__extensions'))
			->set($db->qn('params') . ' = ' . $db->q($data))
			->where($db->qn('type') . ' = ' . $db->q('component'))
			->where($db->qn('element') . ' = ' . $db->q($this->_dmcfirewall_extension));

		$db->setQuery($sql);

		try{
			$db->execute();
		}catch(Exception $exc){
			// Nothing
		}
	}
	
	/**
	 * This is a copy of the serversoftware.php class located in admin/components/com_dmcfirewall/helpers/
	 */
	private function serverSoftware(){
		$system = '';
		
		if(isset($_SERVER['SERVER_SOFTWARE'])){
			$serverSoftware = $_SERVER['SERVER_SOFTWARE'];
		}else{
			$serverSoftware = getenv('SERVER_SOFTWARE');
		}
		
		if(stripos($serverSoftware, 'IIS') !== FALSE){
			$system = 'IIS';
		}elseif(stripos($serverSoftware, 'Apache') !== FALSE || stripos($serverSoftware, 'Zeus') !== FALSE || stripos($serverSoftware, 'LiteSpeed') !== FALSE || stripos($serverSoftware, 'nginx-webserver') !== FALSE){
			$system = 'APA';
		}else{
			$system = 'NA';
		}
		
		return $system;
	}
}
