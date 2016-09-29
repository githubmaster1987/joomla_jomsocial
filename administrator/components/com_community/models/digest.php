<?php
/**
* @copyright (C) 2013 iJoomla, Inc. - All rights reserved.
* @license GNU General Public License, version 2 (http://www.gnu.org/licenses/gpl-2.0.html)
* @author iJoomla.com <webmaster@ijoomla.com>
* @url https://www.jomsocial.com/license-agreement
* The PHP code portions are distributed under the GPL license. If not otherwise stated, all images, manuals, cascading style sheets, and included JavaScript *are NOT GPL, and are released under the IJOOMLA Proprietary Use License v1.0
* More info at https://www.jomsocial.com/license-agreement
*/

// Disallow direct access to this file
defined('_JEXEC') or die('Restricted access');

jimport( 'joomla.application.component.model' );

class CommunityModelDigest extends JModelLegacy
{
	/**
	 * Configuration data
	 *
	 * @var object
	 **/
	var $_params;

	/**
	 * Configuration for ini path
	 *
	 * @var string
	 **/
// 	var $_ini	= '';

	/**
	 * Configuration for xml path
	 *
	 * @var string
	 **/
	var $_xml	= '';

	/**
	 * Constructor
	 */
	public function __construct()
	{
		// Call the parents constructor
		parent::__construct();
	}

	public function getPendingList(){

		$db = JFactory::getDbo();
		$inactiveDays = CFactory::getConfig()->get('digest_email_inactivity'); //days of inactivity
		$limit = CFactory::getConfig()->get('digest_email_cron_email_run');

		//get all the users that is inactive for x days
		$query = "SELECT id, username, lastVisitDate,email FROM ".$db->quoteName('#__users')." as u "
				." INNER JOIN ".$db->quoteName('#__community_users')." as a "
				//we might want to exclude the user who already been notified
				." ON u.id=a.userid"
				." LEFT JOIN ".$db->quoteName('#__community_digest_email')." as d"
				." ON d.user_id=a.userid"
				." WHERE "
				."lastvisitDate <= DATE_SUB(CURDATE(), INTERVAL ".$inactiveDays." DAY)"
				." AND lastvisitDate <> ".$db->quote('0000-00-00 00:00:00')
				." AND (last_sent <= DATE_SUB(CURDATE(), INTERVAL ".$inactiveDays." DAY) OR d.last_sent IS NULL)"
				." AND a.".$db->quoteName('params')." NOT LIKE ".$db->quote('%"etype_system_reports_threshold":0%')
				." AND a.".$db->quoteName('params')." NOT LIKE ".$db->quote('%"etype_system_reports_threshold":"0"%')
				." LIMIT 0, ".$limit;

		$db->setQuery($query);
		$inactiveUsers = $db->loadObjectList();

		return $inactiveUsers;
	}

}
