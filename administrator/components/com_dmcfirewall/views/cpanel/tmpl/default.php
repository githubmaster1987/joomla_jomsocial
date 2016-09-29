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

/* Temp */
$app 								= JApplication::getInstance('site');//;JFactory::getApplication('admin');
$componentParams					= JComponentHelper::getParams('com_dmcfirewall');
$dlid								= $componentParams->get('dlid', ''); 
/* End of temp */

$configPath = 'index.php?option=com_config&view=component&component=com_dmcfirewall&path=&return=' . base64_encode(JURI::getInstance()->toString());
?>
<div id="j-sidebar-container" class="span2">
	<?php echo $this->sidebar; ?>
</div>
<div id="j-main-container" class="span10">
	<div class="span8">
		<?php
		if(ISPRO && !$dlid){
			echo '<div class="alert alert-danger"><p style="margin-bottom:0;">' . JText::sprintf('COM_DMCFIREWALL_ENTER_DOWNLOAD_ID', $configPath) . '</p></div>';
		}
		?>
		<h2><?php echo JText::_('COM_DMCFIREWALL') . ' ' . DMCFIREWALL . ' ' . JText::_('RELEASE'); ?></h2>
		<div class="row-fluid">
			<div class="icon span2">
				<a href="index.php?option=com_dmcfirewall&view=config">
					<div style="text-align: center;">
						<span class="fa fa-cog"></span>
					</div>
					<span><?php echo JText::_('CPANEL_INTERNAL_CONFIG'); ?></span>
				</a>
			</div>
		
			<div class="icon span2">
				<a href="index.php?option=com_dmcfirewall&view=log">
					<div style="text-align: center;">
						<span class="fa fa-clipboard"></span>
					</div>
					<span style="display:block;padding-top:15px;"><?php echo JText::_('CPANEL_VIEW_ATTACK_LOG'); ?></span>
				</a>
			</div>
			
			<div class="icon span2">
				<a href="<?php echo $configPath; ?>">
					<div style="text-align: center;">
						<span class="fa fa-cogs"></span>
					</div>
					<span style="display:block;padding-top:15px;"><?php echo JText::_('CPANEL_COMPONENT_CONFIG'); ?></span>
				</a>
			</div>
		</div>
		<h2 style="clear:left;"><?php echo JText::_('OPERATIONS'); ?></h2>
		
		<div class="row-fluid">
			<?php echo $this->hasAkeeba; ?>
			
			<div class="icon span2">
				<a href="index.php?option=com_dmcfirewall&view=healthcheck">
					<div style="text-align: center;">
						<span class="fa fa-ambulance"></span>
					</div>
					<span><?php echo JText::_('CPANEL_HEALTH_CHECK'); ?></span>
				</a>
			</div>
			
			<div class="icon span2">
				<a href="index.php?option=com_dmcfirewall&view=scheduledreporting">
					<div style="text-align: center;">
						<span class="fa fa-bar-chart-o"></span>
					</div>
					<span style="display:block;padding-top:15px;"><?php echo JText::_('CPANEL_SCHEDULED_REPORTING'); ?></span>
				</a>
			</div>
		</div>
	</div>
		
	<div class="span4" style="float:right;">
		<div class="row-fluid">
		<!-- Right-hand issues -->
			<?php echo $this->firewallissues; ?>
		<!-- End of right-hand issues -->
		
		<!-- Right-hand stats -->	
			<?php echo $this->generalstats; ?>
		<!-- End of right-hand status -->
		</div>
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