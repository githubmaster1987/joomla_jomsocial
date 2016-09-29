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
?>
<div id="j-sidebar-container" class="span2">
	<?php echo $this->sidebar; ?>
</div>
<div id="j-main-container" class="span10">
	<div id="cpanel" class="row-fluid">
		<div class="span8">
			<h2><?php echo JText::_('COM_DMCFIREWALL') . ' ' . JText::_('COM_DMCFIREWALL_CONFIGURATION'); ?></h2>
			<?php
		// load the results of our checks that we performed within the model
			echo $this->serverfileoptions;
		// load the 'plg_dmclogin' plugin info
			echo ISPRO ? $this->loginPluginStatus : '';
		// load the 'plg_dmcfirewall' plugin info
			echo $this->firewallPluginStatus;
		// load the 'plg_dmccontentsniffer' plugin info
			echo $this->snifferPluginStatus;
			?>
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