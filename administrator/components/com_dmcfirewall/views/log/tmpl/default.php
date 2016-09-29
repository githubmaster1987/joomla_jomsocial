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
	<div id="dmc-wrapper">
		<div id="dmc-cpanel-left" style="width:100%; padding:0 0px 10px 0;">
			<form action="index.php" method="post" name="adminForm" id="adminForm">
				<input type="hidden" name="option" id="option" value="com_dmcfirewall" />
				<input type="hidden" name="view" id="view" value="log" />
				<input type="hidden" name="boxchecked" id="boxchecked" value="0" />
				<input type="hidden" name="task" id="task" value="default" />
				<input type="hidden" name="<?php echo JFactory::getSession()->getToken()?>" value="1" />
		
			<div class="row-striped">
				<div class="row-fluid">
					<div class="span2"><input type="checkbox" name="toggle" value="" onclick="Joomla.checkAll(this);" /><strong>Reason</strong></div>
					<div class="span6"><strong>Additional Information</strong></div>
					<div class="span2" style="float:right;"><strong>Time / Date</strong></div>
					<div class="span2" style="float:right;"><strong>IP Address</strong></div>
				</div>
			<?php
			if(!empty($this->list)) {
				$id = 1; $i = 0;
				foreach($this->list as $record):
					$id = 1 - $id;
			?>
			
				<div class="row-fluid">
					<div class="span2"><?php
						$check = JHTML::_('grid.id', ++$i, $record['id']);
						
						echo $check . $record['reason']; ?></div>
					<div class="span6"><?php echo $record['additional_information']; ?></div>
					<div class="span2" style="float:right;"><?php echo $record['time_date']; ?></div>
					<div class="span2" style="float:right;"><?php echo $record['ip']; ?></div>
				</div>
			<?php
				endforeach;
			} ?>
			</div>
			
			<div id="logPagination">
			<?php echo $this->pagination->getListFooter(); ?>
			</div>
		
			</form>
		</div>
		
		<div id="footer" class="well well-small">
			<?php echo JText::_('FOOTER_COPYRIGHT'); ?>
			<?php echo JText::_('FOOTER_VERSION_TEXT') . ' ' . DMCFIREWALL_VERSION; ?><br />
			<?php echo JText::_('FOOTER_RELEASE_DATE_TEXT') . ' ' . DMCFIREWALL_RELEASE_DATE . ' ' . JTEXT::_('FOOTER_RELEASE_NOTES'); ?>
			<?php echo JText::_('FOOTER_DISCLAIMER_HEADER') . JTEXT::_('FOOTER_DISCLAIMER'); ?>
		</div>
		<div style="display:none;">
			<div id="firewall-changelog">
				<?php
				require_once JPATH_COMPONENT_ADMINISTRATOR . '/helpers/coloriser.php';
				echo DmcfirewallChangelogColoriser::colorise(JPATH_COMPONENT_ADMINISTRATOR . '/CHANGELOG.php');
				?>
			</div>
		</div>
	</div>
</div>