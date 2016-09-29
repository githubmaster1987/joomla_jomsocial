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

class DmcfirewallSubmenuHelper {
	
	/**
	 *
	 */
	public static function addSubmenu($view = ''){
		JHtmlSidebar::addEntry(
			JText::_('COM_DMCFIREWALL_CONTROLPANEL'),
			'index.php?option=com_dmcfirewall&view=cpanel',
			$view == 'cpanel'
		);
		JHtmlSidebar::addEntry(
			JText::_('COM_DMCFIREWALL_CONFIGURATION'),
			'index.php?option=com_dmcfirewall&view=config',
			$view == 'config'
		);
		JHtmlSidebar::addEntry(
			JText::_('COM_DMCFIREWALL_CPANEL_VIEW_ATTACK_LOG'),
			'index.php?option=com_dmcfirewall&view=log',
			$view == 'log'
		);
		JHtmlSidebar::addEntry(
			JText::_('COM_DMCFIREWALL_HEALTH_CHECK'),
			'index.php?option=com_dmcfirewall&view=healthcheck',
			$view == 'healthcheck'
		);
		
		if(ISPRO){
			JHtmlSidebar::addEntry(
				JText::_('COM_DMCFIREWALL_CHANGE_DATABASE_PREFIX'),
				'index.php?option=com_dmcfirewall&view=dbchanger',
				$view == 'dbchanger'
			);
			JHtmlSidebar::addEntry(
				JText::_('COM_DMCFIREWALL_CPANEL_CHANGE_SUPER_ADMIN'),
				'index.php?option=com_dmcfirewall&view=superadmin',
				$view == 'superadmin'
			);
			JHtmlSidebar::addEntry(
				JText::_('COM_DMCFIREWALL_CPANEL_PASSWORD_PROTECT_ADMIN'),
				'index.php?option=com_dmcfirewall&view=psswdpradmin',
				$view == 'psswdpradmin'
			);
		}
		
		JHtmlSidebar::addEntry(
			JText::_('COM_DMCFIREWALL_CPANEL_SCHEDULED_REPORTING'),
			'index.php?option=com_dmcfirewall&view=scheduledreporting',
			$view == 'scheduledreporting'
		);
	}
}
