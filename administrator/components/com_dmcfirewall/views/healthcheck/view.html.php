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

class DmcfirewallViewHealthcheck extends FOFViewHtml {	
	public function onBrowse($tpl = null) {
		$model = $this->getModel();
		
		DmcfirewallSubmenuHelper::addSubmenu('healthcheck');
		$this->sidebar = JHtmlSidebar::render();
		
		if(!class_exists('DmcfirewallModelStats')) {
			JLoader::import('models.stats', JPATH_COMPONENT_ADMINISTRATOR);
		}
		
		if(!class_exists('DmcfirewallModelIssues')) {
			JLoader::import('models.issues', JPATH_COMPONENT_ADMINISTRATOR);
		}
		
		$statmodel = new DmcfirewallModelStats();
		$this->assign('generalstats', $statmodel->getGeneralStats());
		
		$issuesmodel = new DmcfirewallModelIssues();
		$this->assign('firewallissues', $issuesmodel->getIssues());
		
		$this->assign('joomlaVersion', $model->joomlaVersionCheck());
		$this->assign('folderPermissions', $model->folderPermissionsCheck());
		$this->assign('filePermissions', $model->filePermissionsCheck());
		$this->assign('phpVersion', $model->phpVersionCheck());
		$this->assign('ftpDetails', $model->ftpDetailsCheck());
		$this->assign('hasAkeeba', $model->hasAkeebaCheck());
		$this->assign('tablePrefix', $model->tablePrefixCheck());
		$this->assign('mysqlVersion', $model->mysqlVersionCheck());
		$this->assign('defaultTemplate', $model->defaultTemplateCheck());
		$this->assign('lastAkeebaBackup', $model->lastAkeebaBackupCheck());
		$this->assign('modifiedFiles', $model->modifiedFilesCheck());
		$this->assign('knownBadFiles', $model->knownBadFilesCheck());
		$this->assign('hasKickstart', $model->hasKickstartCheck());
		$this->assign('findArchive', $model->findArchiveCheck());
		$this->assign('hasHtaccess', $model->hasServerFileCheck());
		$this->assign('multipleJoomlaInstalls', $model->multipleJoomlaInstallsCheck());
		$this->assign('adminUsername', $model->adminUsernameCheck());
		$this->assign('hasWeakPassword', $model->hasWeakPasswordCheck());
		$this->assign('hasInstallation', $model->hasInstallationCheck());
	}
}