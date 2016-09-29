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

class DmcfirewallModelWeekstats extends FOFModel {
	public function getWeekRecords() {
		$db = JFactory::getDBO();
		$buildArray = array();
		$currentDateMinus = date('Y-m-d', strtotime('-6 day', strtotime(date("Y-m-d"))));
		
	//This finds all of the attempts within the last 6 days
	//We now need to loop round for the day specific values
		$query = "SELECT * FROM `#__dmcfirewall_log` WHERE `time_date` BETWEEN '" . $currentDateMinus . " - 00:00:00' AND '" . date('Y-m-d') . " - " . date('H:i:s') . "'";
		$db->setQuery($query);
		$db->execute();
		
		foreach ($db->loadAssocList() as $entry) {
			$buildArray[substr($entry['time_date'], 0, 10)]['date'] = substr($entry['time_date'], 0, 10);
				
			switch($entry['reason']) {
				case 'Known Bad Bot':
					$buildArray[substr($entry['time_date'], 0, 10)]['badBots'] += 1;
				break;
				case 'SQL Injection Attempt':
					$buildArray[substr($entry['time_date'], 0, 10)]['sqlInjection'] += 1;
				break;
				case 'Hack Attempt':
					$buildArray[substr($entry['time_date'], 0, 10)]['hackInjection'] += 1;
				break;
				case 'Failed Login':
					$buildArray[substr($entry['time_date'], 0, 10)]['badLogins'] += 1;
				break;
			}
		}
		
		return $buildArray;
	}
}