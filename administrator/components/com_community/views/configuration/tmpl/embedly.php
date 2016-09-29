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
        <h5><?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_EMBEDLY' ); ?></h5>
        <div class="widget-toolbar no-border">
            <a href="http://tiny.cc/js-embedly" target="_blank"><i class="js-icon-info-sign"></i> <?php echo JText::_('COM_COMMUNITY_DOC') ?></a>
        </div>
    </div>

    <div class="widget-body">
        <div class="widget-main">
            <table width="100%">
                <tr>
                    <td width="250" class="key">
                            <span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_ENABLE_EMBEDLY_DESCRIPTION'); ?>">
                                <?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_ENABLE_EMBEDLY' ); ?>
                            </span>
                    </td>
                    <td>
                        <?php echo CHTMLInput::checkbox('enable_embedly' ,'ace-switch ace-switch-5', null , $this->config->get('enable_embedly') ); ?>
                    </td>
                </tr>
                <tr>
                    <td class="key">
                            <span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_EMBEDLY_APIKEY_DESCRIPTION'); ?>">
                                <?php echo JText::_('COM_COMMUNITY_CONFIGURATION_EMBEDLY_APIKEY')?>
                            </span>
                    </td>
                    <td valign="top">
                        <div><input type="text" name="embedly_apikey" value="<?php echo $this->config->get('embedly_apikey'); ?>" size="5" /></div>
                    </td>
                </tr>
                <tr>
                    <td width="250" class="key">
                            <span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_EMBEDLY_CARD_POSITION_DESCRIPTION'); ?>">
                                <?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_EMBEDLY_CARD_POSITION' ); ?>
                            </span>
                    </td>
                    <td>
                        <select name="enable_embedly_card_position">
                            <option value="right"<?php echo $this->config->get('enable_embedly_card_position') == 'right' ? ' selected="selected"' : ''; ?>><?php echo JText::_('COM_COMMUNITY_RIGHT');?></option>
                            <option value="center"<?php echo $this->config->get('enable_embedly_card_position') == 'center' ? ' selected="selected"' : ''; ?>><?php echo JText::_('COM_COMMUNITY_CENTER');?></option>
                            <option value="left"<?php echo $this->config->get('enable_embedly_card_position') == 'left' ? ' selected="selected"' : ''; ?>><?php echo JText::_('COM_COMMUNITY_LEFT');?></option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td width="250" class="key">
                            <span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_EMBEDLY_CARD_TEMPLATE_DESCRIPTION'); ?>">
                                <?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_EMBEDLY_CARD_TEMPLATE' ); ?>
                            </span>
                    </td>
                    <td>
                        <select name="enable_embedly_card_template">
                            <option value="light"<?php echo $this->config->get('enable_embedly_card_template') == 'light' ? ' selected="selected"' : ''; ?>><?php echo JText::_('COM_COMMUNITY_CONFIGURATION_EMBEDLY_CARD_TEMPLATE_LIGHT_OPTION');?></option>
                            <option value="dark"<?php echo $this->config->get('enable_embedly_card_template') == 'dark' ? ' selected="selected"' : ''; ?>><?php echo JText::_('COM_COMMUNITY_CONFIGURATION_EMBEDLY_CARD_TEMPLATE_DARK_OPTION');?></option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td width="250" class="key">
                            <span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_EMBEDLY_CARD_MINIMALIST_DESIGN_DESCRIPTION'); ?>">
                                <?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_EMBEDLY_CARD_MINIMALIST_DESIGN' ); ?>
                            </span>
                    </td>
                    <td>
                        <?php echo CHTMLInput::checkbox('enable_embedly_card_minimalist' ,'ace-switch ace-switch-5', null , $this->config->get('enable_embedly_card_minimalist') ); ?>
                    </td>
                </tr>
            </table>
        </div>
    </div>

</div>
