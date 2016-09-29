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

class DmcfirewallToolbar extends FOFToolbar {
	
	/**
	 * Disable rendering a toolbar.
	 * 
	 * @return array
	 */
	protected function getMyViews(){
		return array();
	}
	
	public function onCpanelsBrowse(){
		JToolBarHelper::title(JText::_('COM_DMCFIREWALL') . ' &#187; ' . JText::_('COM_DMCFIREWALL_CONTROLPANEL'), 'lock');
		JToolBarHelper::preferences('com_dmcfirewall', '500', '800');
		
		//$this->_renderDefaultSubmenus('cpanel');
	}
	
	public function onLogsBrowse(){
		JToolBarHelper::title(JText::_('COM_DMCFIREWALL') . ' &#187; ' . JText::_('COM_DMCFIREWALL_LOGVIEW'), 'lock');
		JToolBarHelper::preferences('com_dmcfirewall', '500', '800');
	
        JToolBarHelper::back('COM_DMCFIREWALL_CONTROLPANEL', 'index.php?option=com_dmcfirewall');
		JToolBarHelper::custom('deleteEntry', 'delete.png', 'delete_f2.png', 'Delete Record', true);
		
		//$this->_renderDefaultSubmenus('log');
	}
	
	public function onConfigsBrowse(){
		JToolBarHelper::title(JText::_('COM_DMCFIREWALL') . ' &#187; ' . JText::_('COM_DMCFIREWALL_CONFIGURATION'), 'lock');
		JToolBarHelper::preferences('com_dmcfirewall', '500', '800');
		
        JToolBarHelper::back('COM_DMCFIREWALL_CONTROLPANEL', 'index.php?option=com_dmcfirewall');
		
		//$this->_renderDefaultSubmenus('config');
	}
	
	public function onHealthchecksBrowse(){
		JToolBarHelper::title(JText::_('COM_DMCFIREWALL') . ' &#187; ' . JText::_('COM_DMCFIREWALL_HEALTH_CHECK'), 'lock');
		JToolBarHelper::preferences('com_dmcfirewall', '500', '800');
	
        JToolBarHelper::back('COM_DMCFIREWALL_CONTROLPANEL', 'index.php?option=com_dmcfirewall');
		
		//$this->_renderDefaultSubmenus('healthcheck');
	}
	
	public function onDbchangersBrowse(){
		JToolBarHelper::title(JText::_('COM_DMCFIREWALL') . ' &#187; ' . JText::_('COM_DMCFIREWALL_DATABASE_PREFIX_CHANGER'), 'lock');
		JToolBarHelper::preferences('com_dmcfirewall', '500', '800');
	
        JToolBarHelper::back('COM_DMCFIREWALL_CONTROLPANEL', 'index.php?option=com_dmcfirewall');
	}
	
	public function onSuperadminsBrowse(){
		JToolBarHelper::title(JText::_('COM_DMCFIREWALL') . ' &#187; ' . JText::_('COM_DMCFIREWALL_SUPERADMIN_TITLE'), 'lock');
		JToolBarHelper::preferences('com_dmcfirewall', '500', '800');
	
        JToolBarHelper::back('COM_DMCFIREWALL_CONTROLPANEL', 'index.php?option=com_dmcfirewall');
	}
	
	public function onPsswdpradminsBrowse(){
		JToolBarHelper::title(JText::_('COM_DMCFIREWALL') . ' &#187; ' . JText::_('COM_DMCFIREWALL_PASSWORD_PROTECT_ADMIN_TITLE'), 'lock');
		JToolBarHelper::preferences('com_dmcfirewall', '500', '800');
	
        JToolBarHelper::back('COM_DMCFIREWALL_CONTROLPANEL', 'index.php?option=com_dmcfirewall');
	}
	
	public function onWeekstatsBrowse(){
		JToolBarHelper::title(JText::_('COM_DMCFIREWALL') . ' &#187; ' . JText::_('COM_DMCFIREWALL_WEEKSTATS_TITLE'), 'lock');
		JToolBarHelper::preferences('com_dmcfirewall', '500', '800');
	
        JToolBarHelper::back('COM_DMCFIREWALL_CONTROLPANEL', 'index.php?option=com_dmcfirewall');
	}
	
	public function onScheduledreportingsBrowse(){
		JToolBarHelper::title(JText::_('COM_DMCFIREWALL') . ' &#187; ' . JText::_('COM_DMCFIREWALL_SCHEDULED_REPORTING'), 'lock');
		JToolBarHelper::preferences('com_dmcfirewall', '500', '800');
	
        JToolBarHelper::back('COM_DMCFIREWALL_CONTROLPANEL', 'index.php?option=com_dmcfirewall');
	}
	
	/*
	 * @removed 1.4.0
	private function _renderDefaultSubmenus($active = ''){
		$submenus = array(
			'cpanel'		=>	'COM_DMCFIREWALL_CONTROLPANEL',
			'config'		=>	'COM_DMCFIREWALL_CONFIGURATION',
			'log'			=>	'COM_DMCFIREWALL_LOG',
			'healthcheck'	=>	'COM_DMCFIREWALL_HEALTH_CHECK'
		);
		
		foreach($submenus as $view => $key){
			$link = JURI::base() . 'index.php?option=' . $this->component . '&view=' . $view;
			$this->appendLink(JText::_($key), $link, $view == $active);
		}
	}
	*/
}
