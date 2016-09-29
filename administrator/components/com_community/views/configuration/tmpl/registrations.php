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
		<h5><?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_REGISTRATIONS' ); ?></h5>
	</div>
	<div class="widget-body">
		<div class="widget-main">
			<table>
				<tbody>
				<tr>
					<td width="250" class="key">
							<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_REGISTRATIONS_ENABLE_TERMS_TIPS'); ?>">
								<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_REGISTRATIONS_ENABLE_TERMS' ); ?>
							</span>
					</td>
					<td>
						<?php echo CHTMLInput::checkbox('enableterms' ,'ace-switch ace-switch-5', null , $this->config->get('enableterms') ); ?>
					</td>
				</tr>
				<tr>
					<td class="key">
							<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_REGISTRATIONS_TERMS_TIPS'); ?>">
							<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_REGISTRATIONS_TERMS' ); ?>
							</span>
					</td>
					<td>
						<textarea name="registrationTerms" cols="30" rows="5"><?php echo $this->config->get('registrationTerms');?></textarea>
					</td>
				</tr>
				<tr>
					<td class="key">
							<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_REGISTRATIONS_REPEAT_EMAIL_TIPS'); ?>">
								<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_REGISTRATIONS_REPEAT_EMAIL' ); ?>
							</span>
					</td>
					<td>
						<?php echo CHTMLInput::checkbox('check_register_multiple_email' ,'ace-switch ace-switch-5', null , $this->config->get('check_register_multiple_email') ); ?>
					</td>
				</tr>
				<tr>
					<td class="key">
							<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_REGISTRATIONS_ALLOW_PROFILE_DELETION_TIPS'); ?>">
								<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_REGISTRATIONS_ALLOW_PROFILE_DELETION' ); ?>
							</span>
					</td>
					<td>
						<?php echo CHTMLInput::checkbox('profile_deletion' ,'ace-switch ace-switch-5', null , $this->config->get('profile_deletion') ); ?>
					</td>
				</tr>
				<tr>
					<td class="key">
							<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_REGISTRATIONS_ALLOWEDDOMAINS_TIPS'); ?>">
								<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_REGISTRATIONS_ALLOWEDDOMAINS' ); ?>
							</span>
					</td>
					<td>
						<input type="text" name="alloweddomains" value="<?php echo $this->config->get('alloweddomains'); ?>" size="35" />
					</td>
				</tr>
				<tr>
					<td class="key">
							<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_REGISTRATIONS_DENIEDDOMAINS_TIPS'); ?>">
								<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_REGISTRATIONS_DENIEDDOMAINS' ); ?>
							</span>
					</td>
					<td>
						<input type="text" name="denieddomains" value="<?php echo $this->config->get('denieddomains'); ?>" size="35" />
					</td>
				</tr>
				</tbody>
			</table>
		</div>
	</div>
</div>
<div class="space-12"></div>
<div class="widget-box">
<div class="widget-header widget-header-flat">
	<h5><?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_NOCAPTCHA' ); ?></h5>
</div>
<div class="widget-body">
	<div class="widget-main">
		<table>
			<tbody>

			<tr>
				<td width="250" class="key">
							<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_REGISTRATIONS_NOCAPTCHA_ENABLE_TIPS'); ?>">
								<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_REGISTRATIONS_NOCAPTCHA_ENABLE' ); ?>
							</span>
				</td>
				<td>
					<?php echo CHTMLInput::checkbox('nocaptcha' ,'ace-switch ace-switch-5', null , $this->config->get('nocaptcha') ); ?>
				</td>
			</tr>
			<tr>
				<td class="key">
							<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_REGISTRATIONS_NOCAPTCHA_PUBLIC_KEY_TIPS'); ?>">
								<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_REGISTRATIONS_NOCAPTCHA_PUBLIC_KEY' ); ?>
							</span>
				</td>
				<td>
					<input type="text" name="nocaptchapublic" value="<?php echo $this->config->get('nocaptchapublic'); ?>" size="35" />
				</td>
			</tr>
			<tr>
				<td class="key">
							<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_REGISTRATIONS_NOCAPTCHA_PRIVATE_KEY_TIPS'); ?>">
								<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_REGISTRATIONS_NOCAPTCHA_PRIVATE_KEY' ); ?>
							</span>
				</td>
				<td>
					<input type="text" name="nocaptchaprivate" value="<?php echo $this->config->get('nocaptchaprivate'); ?>" size="35" />
				</td>
			</tr>
			<tr>
				<td class="key">
							<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_REGISTRATIONS_NOCAPTCHA_LANGUAGE_TIPS'); ?>">
								<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_REGISTRATIONS_NOCAPTCHA_STYLE' ); ?>
							</span>
				</td>
				<td>
					<select name="nocaptchatheme">
						<option value="light"<?php echo $this->config->get('nocaptchatheme') == 'light' ? ' selected="selected"' : ''; ?>><?php echo JText::_('COM_COMMUNITY_LIGHT');?></option>
						<option value="dark"<?php echo $this->config->get('nocaptchatheme') == 'dark' ? ' selected="selected"' : ''; ?>><?php echo JText::_('COM_COMMUNITY_DARK');?></option>
					</select>
				</td>
			</tr>
			</tbody>
		</table>
	</div>
</div>
</div>