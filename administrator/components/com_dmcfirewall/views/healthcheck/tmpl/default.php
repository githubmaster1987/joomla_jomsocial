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

JHTML::_('behavior.tooltip');

$software = DmcfirewallHelperServersoftware::serverSoftware();

JFactory::getDocument()->addScript(JURI::root() . 'media/com_dmcfirewall/js/jquery-ui.js');
JFactory::getDocument()->addStyleSheet(JURI::root() . 'media/com_dmcfirewall/css/jquery-ui.css');
?>
<style type="text/css">
#progressbar { width:90%; float:left; }
#progressbarval { width:5%; float:right; }
</style>
  
<script type="text/javascript" language="javascript">

jQuery(document).ready(function() {
	jQuery('#start-check').bind("click", function(e) {
		startHealthCheck();
	});
	
	function startHealthCheck() {
		
		jQuery("#performChecks").slideUp(500);
		jQuery("#performingChecks").slideDown(500);
		
	

		jQuery("#progressbar").progressbar({ value: 0 });

		setTimeout(updateProgress, 500);
	}
	
	function updateProgress() {
		var progress;
		progress = jQuery("#progressbar").progressbar("option","value");
		
		if (progress < 100) {
			var randomPercent = Math.floor((Math.random()*15)+1);
			
			if ((progress + randomPercent) >= 100) {
				progress = 100;
				jQuery("#performingChecks").slideUp(500);
				jQuery("#healthResults").slideDown(500);
			}
			else {
				progress = progress + randomPercent;
			}
			
			jQuery("#progressbar").progressbar("option", "value", progress);
			jQuery("#progressbarval").text(progress + '%');
			
			setTimeout(updateProgress, 500);
		}
	}
});
</script>

<script type="text/javascript">
    window.addEvent('domready', function(){ 
       var JTooltips = new Tips(jQuery('.hasTip'), 
       { maxTitleChars: 50, fixed: false}); 
    });
</script>

<div id="j-sidebar-container" class="span2">
	<?php echo $this->sidebar; ?>
</div>
<div id="j-main-container" class="span10">
	<div id="cpanel" class="row-fluid">
		<?php
		if($software == 'IIS') {
		?>
		<div class="alert alert-warning">
			<h4 class="alert-heading">Notice</h4>
			<p>This feature is only experimental on your Windows Hosting environment!</p>
		</div>
		<?php
		}
		?>
		<div class="span8">
			<h2><?php echo JText::_('COM_DMCFIREWALL') . ' ' . JText::_('COM_DMCFIREWALL_HEALTH_CHECK'); ?></h2>
			
			<div id="performChecks">
				<div class="row-striped">
					<div class="row-fluid">
						<?php echo JText::_('HEALTH_CHECKS_INTRO_TEXT'); ?>
					</div>
				</div>
				<div class="healthcheck-form-actions">
					<button class="btn btn-primary btn-large" id="start-check" onsubmit="return false;">
						<i class="icon-health icon-white"></i>
						<?php echo JText::_('HEALTH_CHECK_PERFORM'); ?>
					</button>
				</div>
			</div>
			
			<div id="performingChecks" style="display:none;">
				<?php echo JText::_('PERFORMING_CHECKS_PLEASE_WAIT'); ?>
				<div id="progressbar"></div>
				<div id="progressbarval">0%</div>
			</div>
			
			<div id="healthResults" style="display:none;">
				<h3>Joomla</h3>
				<div class="row-striped">
					<div class="row-fluid">
						<div class="span2"><img src="../media/com_dmcfirewall/images/icon-16-<?php echo $this->joomlaVersion['image']; ?>.png" /></div>
						<div class="span9"><?php echo $this->joomlaVersion['message']; ?></div>
						<div class="span1"><?php echo $this->joomlaVersion['tooltip']; ?></div>
					</div>
					<div class="row-fluid">
						<div class="span2"><img src="../media/com_dmcfirewall/images/icon-16-<?php echo $this->ftpDetails['image']; ?>.png" /></div>
						<div class="span9"><?php echo $this->ftpDetails['message']; ?></div>
						<div class="span1"><?php echo $this->ftpDetails['tooltip']; ?></div>
					</div>
					<div class="row-fluid">
						<div class="span2"><img src="../media/com_dmcfirewall/images/icon-16-ok.png" /></div>
						<div class="span9"><span style="color:green;">You have DMC Firewall installed!</span></div>
						<div class="span1"></div>
					</div>
					<div class="row-fluid">
						<div class="span2"><img src="../media/com_dmcfirewall/images/icon-16-<?php echo $this->hasAkeeba['image']; ?>.png" /></div>
						<div class="span9"><?php echo $this->hasAkeeba['message']; ?></div>
						<div class="span1"><?php echo $this->hasAkeeba['tooltip']; ?></div>
					</div>
					<?php if ($this->lastAkeebaBackup != 'No Akeeba') { ?>
					<div class="row-fluid">
						<div class="span2"><img src="../media/com_dmcfirewall/images/icon-16-<?php echo $this->lastAkeebaBackup['image']; ?>.png" /></div>
						<div class="span9"><?php echo $this->lastAkeebaBackup['message']; ?></div>
						<div class="span1"><?php echo $this->lastAkeebaBackup['tooltip']; ?></div>
					</div>
					<?php } ?>
					<div class="row-fluid">
						<div class="span2"><img src="../media/com_dmcfirewall/images/icon-16-<?php echo $this->tablePrefix['image']; ?>.png" /></div>
						<div class="span9"><?php echo $this->tablePrefix['message']; ?></div>
						<div class="span1"><?php echo $this->tablePrefix['tooltip']; ?></div>
					</div>
					<div class="row-fluid">
						<div class="span2"><img src="../media/com_dmcfirewall/images/icon-16-<?php echo $this->defaultTemplate['image']; ?>.png" /></div>
						<div class="span9"><?php echo $this->defaultTemplate['message']; ?></div>
						<div class="span1"><?php echo $this->defaultTemplate['tooltip']; ?></div>
					</div>
					<div class="row-fluid">
						<div class="span2"><img src="../media/com_dmcfirewall/images/icon-16-<?php echo $this->hasHtaccess['image']; ?>.png" /></div>
						<div class="span9"><?php echo $this->hasHtaccess['message']; ?></div>
						<div class="span1"><?php echo $this->hasHtaccess['tooltip']; ?></div>
					</div>
					<div class="row-fluid">
						<div class="span2"><img src="../media/com_dmcfirewall/images/icon-16-<?php echo $this->multipleJoomlaInstalls['image']; ?>.png" /></div>
						<div class="span9"><?php echo $this->multipleJoomlaInstalls['message']; ?></div>
						<div class="span1"><?php echo $this->multipleJoomlaInstalls['tooltip']; ?></div>
					</div>
					<div class="row-fluid">
						<div class="span2"><img src="../media/com_dmcfirewall/images/icon-16-<?php echo $this->adminUsername['image']; ?>.png" /></div>
						<div class="span9"><?php echo $this->adminUsername['message']; ?></div>
						<div class="span1"><?php echo $this->adminUsername['tooltip']; ?></div>
					</div>
					<div class="row-fluid">
						<div class="span2"><img src="../media/com_dmcfirewall/images/icon-16-<?php echo $this->hasWeakPassword['image']; ?>.png" /></div>
						<div class="span9"><?php echo $this->hasWeakPassword['message']; ?></div>
						<div class="span1"><?php echo $this->hasWeakPassword['tooltip']; ?></div>
					</div>
					<div class="row-fluid">
						<div class="span2"><img src="../media/com_dmcfirewall/images/icon-16-<?php echo $this->hasInstallation['image']; ?>.png" /></div>
						<div class="span9"><?php echo $this->hasInstallation['message']; ?></div>
						<div class="span1"><?php echo $this->hasInstallation['tooltip']; ?></div>
					</div>
				</div>
				<br />
				<h3>Server</h3>
				<div class="row-striped">
				<?php
				/*
					<div class="row-fluid">
						<div class="span2"><img src="../media/com_dmcfirewall/images/icon-16-<?php echo $this->phpVersion['image']; ?>.png" /></div>
						<div class="span9"><?php echo $this->phpVersion['message']; ?></div>
						<div class="span1"><?php echo $this->phpVersion['tooltip']; ?></div>
					</div>
				*/
				?>
					<div class="row-fluid">
						<div class="span2"><img src="../media/com_dmcfirewall/images/icon-16-<?php echo $this->folderPermissions['image']; ?>.png" /></div>
						<div class="span9"><?php echo $this->folderPermissions['message']; ?></div>
						<div class="span1"><?php echo $this->folderPermissions['tooltip']; ?></div>
					</div>
					<div class="row-fluid">
						<div class="span2"><img src="../media/com_dmcfirewall/images/icon-16-<?php echo $this->filePermissions['image']; ?>.png" /></div>
						<div class="span9"><?php echo $this->filePermissions['message']; ?></div>
						<div class="span1"><?php echo $this->filePermissions['tooltip']; ?></div>
					</div>
					<div class="row-fluid">
						<div class="span2"><img src="../media/com_dmcfirewall/images/icon-16-<?php echo $this->modifiedFiles['image']; ?>.png" /></div>
						<div class="span9"><?php echo $this->modifiedFiles['message']; ?></div>
						<div class="span1"><?php echo $this->modifiedFiles['tooltip']; ?></div>
					</div>
					<div class="row-fluid">
						<div class="span2"><img src="../media/com_dmcfirewall/images/icon-16-<?php echo $this->knownBadFiles['image']; ?>.png" /></div>
						<div class="span9"><?php echo $this->knownBadFiles['message']; ?></div>
						<div class="span1"><?php echo $this->knownBadFiles['tooltip']; ?></div>
					</div>
					<div class="row-fluid">
						<div class="span2"><img src="../media/com_dmcfirewall/images/icon-16-<?php echo $this->hasKickstart['image']; ?>.png" /></div>
						<div class="span9"><?php echo $this->hasKickstart['message']; ?></div>
						<div class="span1"><?php echo $this->hasKickstart['tooltip']; ?></div>
					</div>
					<div class="row-fluid">
						<div class="span2"><img src="../media/com_dmcfirewall/images/icon-16-<?php echo $this->findArchive['image']; ?>.png" /></div>
						<div class="span9"><?php echo $this->findArchive['message']; ?></div>
						<div class="span1"><?php echo $this->findArchive['tooltip']; ?></div>
					</div>
				</div>
				<?php
				/*<br />
				<h3>MySQL</h3>
				<div class="row-striped">
					<div class="row-fluid">
						<div class="span2"><img src="../media/com_dmcfirewall/images/icon-16-<?php echo $this->mysqlVersion['image']; ?>.png" /></div>
						<div class="span9"><?php echo $this->mysqlVersion['message']; ?></div>
						<div class="span1"><?php echo $this->mysqlVersion['tooltip']; ?></div>
					</div>
				</div>
				*/
				?>
				<div class="healthcheck-form-actions">
					<a href="index.php?option=com_dmcfirewall&view=healthcheck" class="btn btn-primary btn-large">
						<i class="icon-health icon-white"></i>
						<?php echo JText::_('HEALTH_CHECK_RE_PERFORM'); ?>
					</a>
				</div>
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