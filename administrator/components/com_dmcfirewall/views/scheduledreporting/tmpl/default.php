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

$software = DmcfirewallHelperServersoftware::serverSoftware();

if($software == 'IIS'){
	$phpPath = 'c:\path\to\php.exe';
}else{
	$phpPath = '/path/to/php';
}

$absoluteRoot = rtrim(realpath(JPATH_ROOT), DIRECTORY_SEPARATOR);
$cliLocation = DIRECTORY_SEPARATOR . 'cli' . DIRECTORY_SEPARATOR . 'dmcfirewall-scheduledreporting.php';
?>
<div id="j-sidebar-container" class="span2">
	<?php echo $this->sidebar; ?>
</div>
<div id="j-main-container" class="span10">
	<div id="cpanel" class="row-fluid">
		<div class="span8">
			<h2><?php echo JText::_('COM_DMCFIREWALL_SCHEDULED_REPORTING'); ?></h2>
			<div class="alert alert-info"><?php echo JText::_('SCHEDULED_REPORTING_READ_CAREFULLY'); ?></div>
			<p>DMC Firewall enables you to receive weekly reports detailing what we have banned within the last seven days. By default, the <code>Plugin</code> mechanism is enabled.</p>
			<p>As standard, a weekly report is send out every week (or there abouts) with the <code>Plugin</code> option how ever, if you create and use the <code>Cron Job</code> option, you can specify the time and frequency that suits you. More information is provided within each section below.</p>
			<div class="well">
				<legend>Plugin</legend>
				<p>By default, this option is enabled as it is the easiest to 'set-up'. When DMC Firewall bans someone, a check is performed to see when you last received a report - if that time is greater than seven days, a new report will be generated and sent to you via email (providing that you have <code>Enable Emails</code> set to 'Yes'). Please note that this option relies on someone getting banned from your website so if no one is banned and the last report was generated seven or more days ago - you wont receive a report until a banning occurs.</p>
			</div>
			<div class="well">
				<legend>Cron Job</legend>
				<p>This is the preferred option as it will run at the time that you specify.</p>
				<p>Use the following command in your host's CRON interface: <code><?php echo $phpPath . ' ' . $absoluteRoot.$cliLocation; ?></code></p>
				<p><span class="label label-important">Important</span> Remember to substitute <code><?php echo $phpPath; ?></code> with the real path to your host's PHP CLI (Command Line Interface) executable. Do remember that you must use the PHP CLI executable; the PHP CGI (Common Gateway Interface) executable will not work with our CRON scripts. If unsure what this means, please consult your host. They are the only people who can provide this information.</p>
			</div>
		</div>
		
		<div class="span4" style="float:right;">
		<!-- Right-hand issues -->
			<?php echo $this->firewallissues; ?>
		<!-- End of right-hand issues -->
		
		<!-- Right-hand stats -->	
			<?php echo $this->generalstats; ?>
		<!-- End of right-hand status -->
		</div>
		
		<?php echo DmcfirewallHelperFooter::buildFooter(); ?>
	
		<div style="display:none;">
			<div id="firewall-changelog">
				<?php
				require_once JPATH_COMPONENT_ADMINISTRATOR . '/helpers/coloriser.php';
				echo DmcfirewallChangelogColoriser::colorise(JPATH_COMPONENT_ADMINISTRATOR.'/CHANGELOG.php');
				?>
			</div>
		</div>
	</div>
</div>