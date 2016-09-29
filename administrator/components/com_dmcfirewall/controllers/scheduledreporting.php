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

class DmcfirewallControllerScheduledreporting extends FOFController {
	public function __construct($config = array()) {
		parent::__construct($config);
		
		$this->modelName = 'scheduledreporting';
	}
	
	public function execute($task) {
		$task = 'browse';
		
		parent::execute($task);
	}
	
	/*public function onBeforeBrowse() {
		$result = parent::onBeforeBrowse();
	
		if($result) {
			$model = $this->getThisModel();
			$view = $this->getThisView();
			$view->setModel($model);
		}
		
		return $result;
	}*/
}