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

require_once JPATH_COMPONENT_ADMINISTRATOR . '/helpers/submenu.php';
require_once JPATH_COMPONENT_ADMINISTRATOR . '/helpers/footer.php';
require_once JPATH_COMPONENT_ADMINISTRATOR . '/version.php';
define('DMCFIREWALLNOCACHE', md5(DMCFIREWALL_VERSION . DMCFIREWALL_RELEASE_DATE));

class DmcfirewallDispatcher extends FOFDispatcher {
	
	/**
	 *
	 */
	public function onBeforeDispatch(){
		$result = parent::onBeforeDispatch();
		
		if($result){
			JHtml::_('behavior.modal');
			
			JFactory::getDocument()->addStyleSheet(JURI::root() . 'media/com_dmcfirewall/css/admin.css?=' . DMCFIREWALLNOCACHE);
			JFactory::getDocument()->addStyleSheet(JURI::root() . 'media/com_dmcfirewall/css/font-awesome.css?=' . DMCFIREWALLNOCACHE);
		}
		
		return $result;
	}
	
	/**
	 *
	 */
	public function dispatch(){
		FOFInput::setVar('view', $this->view, $this->input);
		
		parent::dispatch();
	}
}
