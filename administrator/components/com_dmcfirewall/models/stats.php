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
require_once JPATH_ADMINISTRATOR . '/components/com_dmcfirewall/version.php';

class DmcfirewallModelStats extends FOFModel
{
	public function getBlockAttempts()
	{
		$db = JFactory::getDBO();
		$db->setQuery("SELECT `attacks_prevented` FROM `#__dmcfirewall_stats`");
		$db->execute();
		
		return number_format((int)$db->loadResult());
	}
	
	public function getSQLAttempts()
	{
		$db = JFactory::getDBO();
		$db->setQuery("SELECT `sql_attempts_prevented` FROM `#__dmcfirewall_stats`");
		$db->execute();
		
		return number_format((int)$db->loadResult());
	}
	
	public function getLoginAttempts()
	{
		$db = JFactory::getDBO();
		$db->setQuery("SELECT `bad_login_attempts` FROM `#__dmcfirewall_stats`");
		$db->execute();
		
		return number_format((int)$db->loadResult());
	}
	
	public function getBotAttempts()
	{
		$db = JFactory::getDBO();
		$db->setQuery("SELECT `bot_attempts_prevented` FROM `#__dmcfirewall_stats`");
		$db->execute();
		
		return number_format((int)$db->loadResult());
	}
	
	public function getHackAttempts()
	{
		$db = JFactory::getDBO();
		$db->setQuery("SELECT `hack_attempts_prevented` FROM `#__dmcfirewall_stats`");
		$db->execute();
		
		return number_format((int)$db->loadResult());
	}
	
	public function getGeneralStats($withoutStatsTitle = false)
	{
		$attackTitle				= JText::_('ATTACK_SUMMARY');
		$statsTitle					= '';
		$badLoginAttempts			= '';
		$goProAd					= '';
		$displayWeekStatsButton		= '';
		$jedListing					= '';
		$badBotsUpgrade				= '';
		
		if ($withoutStatsTitle != true)
		{
			$statsTitle = "<span class=\"heading textLeft\">$attackTitle</span>";
			$displayWeekStatsButton = '<p class="content-go-right"><a href="index.php?option=com_dmcfirewall&view=weekstats&tmpl=component" class="modal btn btn-info" rel="{handler: \'iframe\', size: {x: 750, y: 445}}">' . JText::_('STATS_WEEK_STATS_BUTTON') . '</a></p>';
			
			$coreOrProText = ISPRO ? 'Pro' : 'Core';
			$coreOrProID = ISPRO ? 25051 : 23659;
			$jedListing = JText::sprintf('STATS_RATE_US_ON_JED', $coreOrProText, $coreOrProID);
		}
		
		if (ISPRO)
		{
			$badLoginAttempts = $this->getLoginAttempts();
		}
		
		if (!ISPRO)
		{
			if (!$withoutStatsTitle)
			{
				$goProAd = JText::_('GO_PRO');
			}
			
			$badBotsUpgrade = '<a href="http://www.webdevelopmentconsultancy.com/subscribe/levels.html" target="_blank" class="btn btn-inverse btn-mini" style="float:right;margin-left:5px;">Upgrade today!</a> <a href="http://www.webdevelopmentconsultancy.com/joomla-extensions/dmc-firewall/getting-started/configuring-dmc-firewall.html#badBots" class="btn btn-danger btn-mini" target="_blank" style="float:right; margin-top:5px; clear:both;">Minimal protection!</a>';
			$badLoginAttempts = 'NA <a href="http://www.webdevelopmentconsultancy.com/subscribe/levels.html" target="_blank" class="btn btn-inverse btn-mini" style="float:right;">Upgrade today!</a>';
		}
		
		$tableView = <<<TABLE_VIEW
		$statsTitle
		<table class="firewall-summary">
			<tr>
				<td width="50%">Attacks Prevented</td>
				<td width="50%">{$this->getBlockAttempts()}</td>
			</tr>
			<tr>
				<td>&nbsp;&nbsp;&nbsp;Hack Attempts Blocked</td>
				<td>&nbsp;&nbsp;&nbsp;{$this->getHackAttempts()}</td>
			</tr>
			<tr>
				<td>&nbsp;&nbsp;&nbsp;SQL Attempts Blocked</td>
				<td>&nbsp;&nbsp;&nbsp;{$this->getSQLAttempts()}</td>
			</tr>
			<tr>
				<td valign="top">&nbsp;&nbsp;&nbsp;Bots Blocked</td>
				<td>&nbsp;&nbsp;&nbsp;{$this->getBotAttempts()} $badBotsUpgrade</td>
			</tr>
			<tr>
				<td>&nbsp;&nbsp;&nbsp;Bad Login Attempts Blocked</td>
				<td>&nbsp;&nbsp;&nbsp;$badLoginAttempts</td>
			</tr>
		</table>
		$displayWeekStatsButton
		$goProAd
		$jedListing
TABLE_VIEW;

		return $tableView;
	}
}
