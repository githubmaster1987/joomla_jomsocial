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
    echo CommunityLicenseHelper::disabledHtml();
?>

<style>
    .container-main {
        padding-bottom: 0 !important;
    }
</style>

<form name="adminForm" id="adminForm" action="index.php?option=com_community" method="POST" enctype="multipart/form-data">
    <div class="widget-box">
        <div class="widget-header widget-header-flat">
            <h5><?php echo JText::_('COM_COMMUNITY_CONFIGURATION_TITLE_PAGE_SETTINGS') ?></h5>
        </div>
        <div class="widget-body">
            <div class="widget-main">
                <table cellspacing="5" cellpadding="5">
                    <tr>
                        <td class="key" width="100"><?php echo JText::_('COM_COMMUNITY_PREFERENCE_DEFAULT_TAB'); ?></td>
                        <td>
                            <select name="config[default_event_tab]">
                                <option value="0"><?php echo JText::_('COM_COMMUNITY_PREFERENCE_ACTIVITY_STREAM'); ?></option>
                                <option value="1" <?php echo (CFactory::getConfig()->get('default_event_tab')) ? 'selected' : ''?>><?php echo JText::_('COM_COMMUNITY_PREFERENCE_ACTIVITY_DESC'); ?></option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td class="key" width="150"><?php echo JText::_('COM_COMMUNITY_CONFIGURATION_SHOW_TOTAL_MEMBERS'); ?></td>
                        <td>
                            <input type=text" name="config[event_sidebar_members_show_total]" value="<?php echo CFactory::getConfig()->get('event_sidebar_members_show_total', 12); ?>"/>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
    <div class="space-8"></div>
    <input type="hidden" name="view" value="themeevents" />
    <input type="hidden" name="task" value="apply" />
    <input type="hidden" name="option" value="com_community" />
    <?php echo JHTML::_( 'form.token' ); ?>
</form>