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

class DmcfirewallModelLogs extends FOFModel {
	/** @var JPagination The JPagination object, used in the GUI */
	private $_pagination = null;
	private $_total = null;

	public function __construct($config = array()) {
		parent::__construct($config);

		// Get the pagination request variables
		$app = JFactory::getApplication('administrator');
		//die($app->get('list_limit'));
		if(!($app instanceof JApplication || $app instanceof JApplicationAdministrator)) {
			$limit = 0;
			$limitstart = 0;
		}
		else {
			$limit = $app->getUserStateFromRequest('global.list.limit', 'limit', $app->getCfg('list_limit'));
			$limitstart = $app->getUserStateFromRequest('com_dmcfirewall.log.limitstart','limitstart',0);
		}

		// Set the page pagination variables
		$this->setState('limit',$limit);
		$this->setState('limitstart',$limitstart);
	}
	
	public function getLogData() {
		$db = JFactory::getDBO();
		$query = "SELECT * FROM `#__dmcfirewall_log`";
		$db->setQuery($query);
		$db->query();
		
		return $db->loadAssocList();
	}
	
	function getLogs() {
        $db = JFactory::getDBO();
		$query = "SELECT * FROM `#__dmcfirewall_log` ORDER BY `id` DESC";
		$db->setQuery($query, $this->getState('limitstart'), $this->getState('limit'));
		$db->query();
		
		return $db->loadAssocList();
	}
	
	/**
	 * Get a pagination object
	 *
	 * @access public
	 * @return JPagination
	 *
	 */
	 function getPagination() {
		// Load the content if it doesn't already exist
		if (empty($this->_pagination)) {
			jimport('joomla.html.pagination');
			$total = count($this->getLogData());
			$this->_pagination = new JPagination($total, $this->getState('limitstart'), $this->getState('limit') );
		}
		
		return $this->_pagination;
	}
}