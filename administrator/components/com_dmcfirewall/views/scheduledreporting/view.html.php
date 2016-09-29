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

class DmcfirewallViewScheduledreporting extends FOFViewHtml {
	protected function onBrowse($tpl = null) {
		$model = $this->getModel();
		
		if(!class_exists('DmcfirewallModelStats')) {
			JLoader::import('models.stats', JPATH_COMPONENT_ADMINISTRATOR);
		}
		
		if(!class_exists('DmcfirewallModelIssues')) {
			JLoader::import('models.issues', JPATH_COMPONENT_ADMINISTRATOR);
		}
		
		DmcfirewallSubmenuHelper::addSubmenu('scheduledreporting');
		$this->sidebar = JHtmlSidebar::render();
		
		$statmodel = new DmcfirewallModelStats();
		$this->assign('generalstats', $statmodel->getGeneralStats());
		
		$issuesmodel = new DmcfirewallModelIssues();
		$this->assign('firewallissues', $issuesmodel->getIssues());

	}
}