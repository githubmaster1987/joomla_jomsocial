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
		<h5><?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_DISPLAY' ); ?></h5>
	</div>
	<div class="widget-body">
		<div class="widget-main">
			<table>
				<tbody>
					<tr>
						<td  width="250" class="key">
							<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_DISPLAY_HIDE_MENU_TIPS'); ?>">
							<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_DISPLAY_HIDE_MENU' ); ?>
							</span>
						</td>
						<td>
							<?php echo CHTMLInput::checkbox('show_toolbar' ,'ace-switch ace-switch-5', null , $this->config->get('show_toolbar') ); ?>
						</td>
					</tr>
					<tr>
						<td  class="key">
							<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_DISPLAY_NAME_TIPS'); ?>">
							<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_DISPLAY_NAME' ); ?>
							</span>
						</td>
						<td>
							<select name="displayname">
								<?php
									$selectedRealName	= ( $this->config->get('displayname') == 'name' ) ? 'selected="true"' : '';
									$selectedUserName	= ( $this->config->get('displayname') == 'username' ) ? 'selected="true"' : '';
								?>
								<option <?php echo $selectedRealName; ?> value="name"><?php echo JText::_('COM_COMMUNITY_REALNAME_OPTION');?></option>
								<option <?php echo $selectedUserName; ?> value="username"><?php echo JText::_('COM_COMMUNITY_USERNAME_OPTION');?></option>
							</select>
						</td>
					</tr>
                    <tr>
                        <td class="key">
							<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_DISPLAY_PAGINATION_TIPS'); ?>">
							<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_DISPLAY_PAGINATION' ); ?>
							</span>
                        </td>
                        <td>
                            <select name="pagination">
                                <?php
                                $selected2	= ( $this->config->get('pagination') == '2' ) ? 'selected="true"' : '';
                                $selected4	= ( $this->config->get('pagination') == '4' ) ? 'selected="true"' : '';
                                $selected6	= ( $this->config->get('pagination') == '6' ) ? 'selected="true"' : '';
                                $selected8	= ( $this->config->get('pagination') == '8' ) ? 'selected="true"' : '';
                                $selected10	= ( $this->config->get('pagination') == '10' ) ? 'selected="true"' : '';
                                $selected12	= ( $this->config->get('pagination') == '12' ) ? 'selected="true"' : '';
                                $selected14	= ( $this->config->get('pagination') == '14' ) ? 'selected="true"' : '';
                                $selected16	= ( $this->config->get('pagination') == '16' ) ? 'selected="true"' : '';
                                $selected18	= ( $this->config->get('pagination') == '18' ) ? 'selected="true"' : '';
                                $selected20	= ( $this->config->get('pagination') == '20' ) ? 'selected="true"' : '';
                                ?>
                                <option <?php echo $selected2; ?> value="2">2</option>
                                <option <?php echo $selected4; ?> value="4">4</option>
                                <option <?php echo $selected6; ?> value="6">6</option>
                                <option <?php echo $selected8; ?> value="8">8</option>
                                <option <?php echo $selected10; ?> value="10">10</option>
                                <option <?php echo $selected12; ?> value="12">12</option>
                                <option <?php echo $selected14; ?> value="14">14</option>
                                <option <?php echo $selected16; ?> value="16">16</option>
                                <option <?php echo $selected18; ?> value="18">18</option>
                                <option <?php echo $selected20; ?> value="20">20</option>

                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td class="key">
							<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_DISPLAY_ACTIVITY_DATE_STYLE_TIPS'); ?>">
							<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_DISPLAY_ACTIVITY_DATE_STYLE' ); ?>
							</span>
                        </td>
                        <td>
                            <select name="activitydateformat">
                                <?php
                                $selectedFixedDate	= ( $this->config->get('activitydateformat') == 'fixed' ) ? 'selected="true"' : '';
                                $selectedLapseDate	= ( $this->config->get('activitydateformat') == 'lapse' ) ? 'selected="true"' : '';
                                ?>
                                <option <?php echo $selectedFixedDate; ?> value="fixed"><?php echo JText::_('COM_COMMUNITY_FIXED_OPTION');?></option>
                                <option <?php echo $selectedLapseDate; ?> value="lapse"><?php echo JText::_('COM_COMMUNITY_LAPSED_OPTION');?></option>
                            </select>
                        </td>
                    </tr>
					<tr>
						<td class="key">
							<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_DISPLAY_ALLOW_HTML_TIPS'); ?>">
							<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_DISPLAY_ALLOW_HTML' ); ?>
							</span>
						</td>
						<td>
							<?php echo CHTMLInput::checkbox('allowhtml' ,'ace-switch ace-switch-5', null , $this->config->get('allowhtml') ); ?>
						</td>
					</tr>

		            <input type="hidden" name="showactivityavatar" value="1" />
					<tr>
						<td class="key">
							<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_DISPLAY_ACTIVITY_CONTENT_LENGTH_TIPS'); ?>">
							<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_DISPLAY_ACTIVITY_CONTENT_LENGTH' ); ?>
							</span>
						</td>
						<td>
							<input type="text" name="streamcontentlength" value="<?php echo $this->config->get('streamcontentlength');?>" size="20" /> 
						</td>
					</tr>
					<tr>
						<td  class="key">
							<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_DISPLAY_SINGULAR_NUMBER_TIPS'); ?>">
							<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_DISPLAY_SINGULAR_NUMBER' ); ?>
							</span>
						</td>
						<td>
							<input type="text" name="singularnumber" value="<?php echo $this->config->get('singularnumber');?>" size="20" /> 
						</td>
					</tr>
				</tbody>
			</table>
		</div>
	</div>
</div>