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

class DmcfirewallViewWeekstats extends FOFViewHtml {	
	public function onBrowse($tpl = null) {
		$model = $this->getModel();
		
		$this->assign('weekStats', $model->getWeekRecords());
		//$this->assign('loginPluginStatus', $model->getLoginPluginStatus());
	}
}