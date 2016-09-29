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
?>

<div class="widget-box">
	<div class="widget-header widget-header-flat">
		<h5><?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_MESSAGING' ); ?></h5>
		<div class="widget-toolbar no-border">
			<a href="http://documentation.jomsocial.com/wiki/Private_Messaging" target="_blank"><i class="js-icon-info-sign"></i> <?php echo JText::_('COM_COMMUNITY_DOC'); ?></a>
		</div>
	</div>
	<div class="widget-body">
		<div class="widget-main">

			<table>
				<tbody>
					<tr>
						<td width="250" class="key">
							<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_MESSAGING_ENABLE_TIPS'); ?>">
								<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_MESSAGING_ENABLE' ); ?>
							</span>
						</td>
						<td>
							<?php echo CHTMLInput::checkbox('enablepm' ,'ace-switch ace-switch-5', null , $this->config->get('enablepm') ); ?>
						</td>
					</tr>
                    <tr>
                        <td width="250" class="key">
							<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_MESSAGING_FILESHARING_ENABLE_TIPS'); ?>">
								<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_MESSAGING_FILESHARING_ENABLE' ); ?>
							</span>
                        </td>
                        <td>
                            <?php echo CHTMLInput::checkbox('message_file_sharing' ,'ace-switch ace-switch-5', null , $this->config->get('message_file_sharing') ); ?>
                        </td>
                    </tr>
                    <tr>
                        <td width="250" class="key">
							<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_MESSAGING_FILESHARING_MAXFILESIZE_TIPS'); ?>">
								<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_MESSAGING_FILESHARING_MAXFILESIZE' ); ?>
							</span>
                        </td>
                        <td>
                            <input type="text" name="message_file_maxsize" value="<?php echo $this->config->get('message_file_maxsize');?>" size="40" />
                        </td>
                    </tr>
				</tbody>
			</table>
		</div>
	</div>
</div>