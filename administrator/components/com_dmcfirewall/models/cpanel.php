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

class DmcfirewallModelCpanel extends FOFModel
{
	public function hasAkeeba()
	{
		$db = JFactory::getDBO();
		$db->setQuery("SELECT * FROM `#__extensions` WHERE `element` = 'com_akeeba' AND `enabled` = 1");
		$db->execute();
		$countAkeeba = $db->getNumRows();
		
		if (JFile::exists(JPATH_ADMINISTRATOR . '/components/com_akeeba/akeeba.xml') && $countAkeeba)
		{
			return '<div class="icon span2"><a href="index.php?option=com_akeeba&view=backup&tmpl=component" class="modal" rel="{handler: \'iframe\', size: {x: 750, y: 500}}"><div style="text-align: center;"><img width="35" src="../media/com_dmcfirewall/images/akeebabackup-gray.png"></div><span>' . JText::_('CPANEL_HAS_AKEEBA') . '</span></a></div>';
		}
	}
}
