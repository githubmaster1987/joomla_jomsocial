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
		<h5><?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_FEATURED_LISTING' ); ?></h5>
	</div>
	<div class="widget-body">
		<div class="widget-main">
			<table>
				<tbody>
					<tr>
						<td width="200" class="key">
							<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_SHOW_FEATURED_TIPS'); ?>">
							<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_ENABLED_FEATURED' ); ?>
							</span>
						</td>
						<td>
							<?php echo CHTMLInput::checkbox('show_featured' ,'ace-switch ace-switch-5', null , $this->config->get('show_featured') ); ?>
						</td>
					</tr>
					<tr>
						<td class="key">
							<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_FEATURED_MAXIMUM_USERS_TIPS'); ?>">
								<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_FEATURED_MAXIMUM_USERS' ); ?>
							</span>
						</td>
						<td>
							<input type="text" name="featureduserslimit" value="<?php echo $this->config->get('featureduserslimit' );?>" size="4" />
							<?php // echo JText::_('COM_COMMUNITY_USERS');?>
						</td>
					</tr>
					<tr>
						<td class="key">
							<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_FEATURED_MAXIMUM_VIDEOS_TIPS'); ?>">
								<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_FEATURED_MAXIMUM_VIDEOS' ); ?>
							</span>
						</td>
						<td>
							<input type="text" name="featuredvideoslimit" value="<?php echo $this->config->get('featuredvideoslimit');?>" size="4" /> <?php // echo JText::_('COM_COMMUNITY_VIDEOS');?>
						</td>
					</tr>
					<tr>
						<td class="key">
							<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_FEATURED_MAXIMUM_GROUPS'); ?>">
								<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_FEATURED_MAXIMUM_GROUPS' ); ?>
							</span>
						</td>
						<td>
							<input type="text" name="featuredgroupslimit" value="<?php echo $this->config->get('featuredgroupslimit' );?>" size="4" /> <?php // echo JText::_('COM_COMMUNITY_GROUPS');?>
						</td>
					</tr>
					<tr>
						<td class="key">
							<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_FEATURED_MAXIMUM_ALBUMS_TIPS'); ?>">
								<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_FEATURED_MAXIMUM_ALBUMS' ); ?>
							</span>
						</td>
						<td>
							<input type="text" name="featuredalbumslimit" value="<?php echo $this->config->get('featuredalbumslimit' );?>" size="4" /> <?php //echo JText::_('COM_COMMUNITY_ALBUMS');?>
						</td>
					</tr>
					<tr>
						<td class="key">
							<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_FEATURED_MAXIMUM_EVENTS_TIPS'); ?>">
								<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_FEATURED_MAXIMUM_EVENTS' ); ?>
							</span>
						</td>
						<td>
							<input type="text" name="featuredeventslimit" value="<?php echo $this->config->get('featuredeventslimit' );?>" size="4" /> <?php // echo JText::_('COM_COMMUNITY_EVENTS');?>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
	</div>
</div>
