<?php
/**
 * @copyright (C) 2015 iJoomla, Inc. - All rights reserved.
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
        <h5><?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_FEATURED_STREAM' ); ?></h5>
    </div>
    <div class="widget-body">
        <div class="widget-main">
            <table>
                <tbody>
                <tr>
                    <td width="200" class="key">
							<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_SHOW_FEATURED_TIPS'); ?>">
							<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_ENABLED_FEATURED_STREAM' ); ?>
							</span>
                    </td>
                    <td>
                        <?php echo CHTMLInput::checkbox('featured_stream' ,'ace-switch ace-switch-5', null , $this->config->get('featured_stream') ); ?>
                    </td>
                </tr>
                <tr>
                    <td class="key">
							<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_STREAM_FRONTPAGE_FEATURED_TIPS'); ?>">
								<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_STREAM_FRONTPAGE_FEATURED' ); ?>
							</span>
                    </td>
                    <td>
                        <input type="text" name="stream_frontpage_featured" value="<?php echo $this->config->get('stream_frontpage_featured' );?>" size="4" />
                    </td>
                </tr>
                <tr>
                    <td class="key">
							<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_STREAM_PROFILE_FEATURED_TIPS'); ?>">
								<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_STREAM_PROFILE_FEATURED' ); ?>
							</span>
                    </td>
                    <td>
                        <input type="text" name="stream_profile_featured" value="<?php echo $this->config->get('stream_profile_featured');?>" size="4" /> <?php // echo JText::_('COM_COMMUNITY_VIDEOS');?>
                    </td>
                </tr>
                <tr>
                    <td class="key">
							<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_STREAM_GROUP_FEATURED_TIPS'); ?>">
								<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_STREAM_GROUP_FEATURED' ); ?>
							</span>
                    </td>
                    <td>
                        <input type="text" name="stream_group_featured" value="<?php echo $this->config->get('stream_group_featured' );?>" size="4" /> <?php // echo JText::_('COM_COMMUNITY_GROUPS');?>
                    </td>
                </tr>
                <tr>
                    <td class="key">
							<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_STREAM_EVENT_FEATURED_TIPS'); ?>">
								<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_STREAM_EVENT_FEATURED' ); ?>
							</span>
                    </td>
                    <td>
                        <input type="text" name="stream_event_featured" value="<?php echo $this->config->get('stream_event_featured' );?>" size="4" /> <?php //echo JText::_('COM_COMMUNITY_ALBUMS');?>
                    </td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
