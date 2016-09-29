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

class DmcfirewallControllerHealthcheck extends FOFController {
	public function __construct($config = array()) {
		parent::__construct($config);
		
		$this->modelName = 'healthcheck';
	}
	
	public function execute($task) {
		switch($task) {
			case 'add':
			case 'default':
			case 'browse':
				$this->task = 'browse';
			break;
			case 'performCheck':
				$this->task = 'performCheck';
			break;
			default:
				$this->task = $task;
			break;
		}
		
		FOFInput::setVar('task', $this->task, $this->input);
		parent::execute($this->task);
	}
	
	public function performCheck() {
		$this->setRedirect(JURI::base() . 'index.php?option=com_dmcfirewall&view=healthcheck', JText::_('LOG_ENTRY_DELETE_SUCCESS'));

		return;
	}
}