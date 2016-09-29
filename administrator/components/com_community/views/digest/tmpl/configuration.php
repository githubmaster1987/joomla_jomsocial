<?php
/**
 * @copyright (C) 2013 iJoomla, Inc. - All rights reserved.
 * @license GNU General Public License, version 2 (http://www.gnu.org/licenses/gpl-2.0.html)
 * @author iJoomla.com <webmaster@ijoomla.com>
 * @url https://www.jomsocial.com/license-agreement
 * The PHP code portions are distributed under the GPL license. If not otherwise stated, all images, manuals, cascading style sheets, and included JavaScript *are NOT GPL, and are released under the IJOOMLA Proprietary Use License v1.0
 * More info at https://www.jomsocial.com/license-agreement
 */
defined('_JEXEC') or die('Restricted access');
?>
<form action="index.php" id="adminForm" method="post" name="adminForm" enctype="multipart/form-data">
    <div id="messaging-form">
        <div class="alert alert-info">
            <?php echo JText::_('COM_COMMUNITY_DIGEST_INTRO');?>
        </div>
        <div class="row-fluid">
            <div class="span14">
                <div class="widget-box">
                    <div class="widget-header widget-header-flat">
                        <h5><?php echo JText::_('');?></h5>
                    </div>
                    <div class="widget-body">
                        <div class="widget-main">
                            <table>
                                <tr>
                                    <td class="key">
                                        <span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_ENABLE_DIGEST_TIPS');?>">
                                        <?php echo JText::_('COM_COMMUNITY_ENABLE_DIGEST');?></td>
                                    </span>
                                    <td>
                                        <?php echo CHTMLInput::checkbox('enabledigest' ,'ace-switch ace-switch-5', null , $this->config->get('enabledigest') ); ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="key">
                                        <span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_DIGEST_FREQUENCY_TIPS');?>">
                                            <?php echo JText::_('COM_COMMUNITY_DIGEST_FREQUENCY');?>
                                        </span>
                                    </td>
                                    <td>
                                        <select name="digest_email_frequency">
                                            <option value="daily"<?php echo $this->config->get('digest_email_frequency') == 'daily' ? ' selected="selected"' : ''; ?>><?php echo JText::_('COM_COMMUNITY_DIGEST_DAILY_OPTION');?></option>
                                            <option value="weekly"<?php echo $this->config->get('digest_email_frequency') == 'weekly' ? ' selected="selected"' : ''; ?>><?php echo JText::_('COM_COMMUNITY_DIGEST_WEEKLY_OPTION');?></option>
                                            <option value="monthly"<?php echo $this->config->get('digest_email_frequency') == 'monthly' ? ' selected="selected"' : ''; ?>><?php echo JText::_('COM_COMMUNITY_DIGEST_MONTHLY_OPTION');?></option>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="key">
                                        <span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_DIGEST_EMAIL_CRON_FREQUENCY_TIPS');?>">
                                            <?php echo JText::_('COM_COMMUNITY_DIGEST_EMAIL_CRON_FREQUENCY');?>
                                        </span>
                                    </td>
                                    <td>
                                        <select name="digest_email_cron_email_run">
                                            <option value="10"<?php echo $this->config->get('digest_email_cron_email_run') == 10 ? ' selected="selected"' : ''; ?>><?php echo JText::_('10');?></option>
                                            <option value="20"<?php echo $this->config->get('digest_email_cron_email_run') == 20 ? ' selected="selected"' : ''; ?>><?php echo JText::_('20');?></option>
                                            <option value="50"<?php echo $this->config->get('digest_email_cron_email_run') == 50 ? ' selected="selected"' : ''; ?>><?php echo JText::_('50');?></option>
                                            <option value="100"<?php echo $this->config->get('digest_email_cron_email_run') == 100 ? ' selected="selected"' : ''; ?>><?php echo JText::_('100');?></option>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="key">
                                        <span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_DIGEST_INACTIVITY_PERIOD_SETTINGS_TIPS');?>">
                                            <?php echo JText::_('COM_COMMUNITY_DIGEST_INACTIVITY_PERIOD_SETTINGS');?>
                                        </span>
                                    </td>

                                    <td>
                                        <select name="digest_email_inactivity">
                                            <?php for($i=1; $i <= 30; $i++) {?>
                                                <option value="<?php echo $i; ?>"<?php echo $this->config->get('digest_email_inactivity') == $i ? ' selected="selected"' : ''; ?>><?php echo $i;?></option>
                                            <?php } ?>
                                        </select>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="space-12"></div>
                <div class="widget-box">
                    <div class="widget-header widget-header-flat">
                        <h5><?php echo JText::_('COM_COMMUNITY_DIGEST_INCLUDE_POSTS');?></h5>
                    </div>
                    <div class="widget-body">
                        <div class="widget-main">
                            <table>
                                <tr>
                                    <td class="key">
                                    <span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_DIGEST_INCLUDE_POSTS_SETTINGS_TIPS');?>">
                                        <?php echo JText::_('COM_COMMUNITY_DIGEST_INCLUDE_POSTS_SETTINGS');?>
                                    </span>
                                    </td>
                                    <td>
                                        <?php echo CHTMLInput::checkbox('digest_email_include_posts' ,'ace-switch ace-switch-5', null , $this->config->get('digest_email_include_posts') ); ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="key">
                                    <span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_DIGEST_POSTS_NUMBER_SETTINGS_TIPS');?>">
                                    <?php echo JText::_('COM_COMMUNITY_DIGEST_POSTS_NUMBER_SETTINGS');?>
                                    </span>
                                    </td>
                                    <td>
                                        <select name="digest_email_include_posts_count">
                                            <?php for($i=1; $i <= 5; $i++) {?>
                                                <option value="<?php echo $i; ?>"<?php echo $this->config->get('digest_email_include_posts_count') == $i ? ' selected="selected"' : ''; ?>><?php echo $i;?></option>
                                            <?php } ?>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="key">
                                        <span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_DIGEST_POSTS_SHOW_AVATAR_SETTINGS_TIPS');?>">
                                            <?php echo JText::_('COM_COMMUNITY_DIGEST_POSTS_SHOW_AVATAR_SETTINGS');?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php echo CHTMLInput::checkbox('digest_email_post_include_avatar' ,'ace-switch ace-switch-5', null , $this->config->get('digest_email_post_include_avatar') ); ?>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="space-12"></div>
                <div class="widget-box">
                    <div class="widget-header widget-header-flat">
                        <h5><?php echo JText::_('COM_COMMUNITY_DIGEST_INCLUDE_PHOTOS');?></h5>
                    </div>
                    <div class="widget-body">
                        <div class="widget-main">
                            <table>
                                <tr>
                                    <td class="key" width="250px"><b><?php echo JText::_('');?></b></td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td class="key">
                                    <span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_DIGEST_INCLUDE_PHOTOS_SETTINGS_TIPS');?>">
                                        <?php echo JText::_('COM_COMMUNITY_DIGEST_INCLUDE_PHOTOS_SETTINGS');?>
                                    </span>
                                    </td>
                                    <td>
                                        <?php echo CHTMLInput::checkbox('digest_email_include_photos' ,'ace-switch ace-switch-5', null , $this->config->get('digest_email_include_photos') ); ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="key">
                                    <span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_DIGEST_PHOTOS_NUMBER_SETTINGS_TIPS');?>">
                                        <?php echo JText::_('COM_COMMUNITY_DIGEST_PHOTOS_NUMBER_SETTINGS');?>
                                    </span>
                                    </td>
                                    <td>
                                        <select name="digest_email_include_photos_count">
                                            <?php for($i=1; $i <= 5; $i++) {?>
                                                <option value="<?php echo $i; ?>"<?php echo $this->config->get('digest_email_include_photos_count') == $i ? ' selected="selected"' : ''; ?>><?php echo $i;?></option>
                                            <?php } ?>
                                        </select>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="space-12"></div>
                <div class="widget-box">
                    <div class="widget-header widget-header-flat">
                        <h5><?php echo JText::_('COM_COMMUNITY_DIGEST_INCLUDE_VIDEOS');?></h5>
                    </div>
                    <div class="widget-body">
                        <div class="widget-main">
                            <table>
                                <tr>
                                    <td class="key">
                                    <span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_DIGEST_INCLUDE_VIDEOS_SETTINGS_TIPS');?>">
                                        <?php echo JText::_('COM_COMMUNITY_DIGEST_INCLUDE_VIDEOS_SETTINGS');?>
                                    </span>
                                    </td>
                                    <td>
                                        <?php echo CHTMLInput::checkbox('digest_email_include_videos' ,'ace-switch ace-switch-5', null , $this->config->get('digest_email_include_videos') ); ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="key">
                                    <span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_DIGEST_VIDEOS_NUMBER_SETTINGS_TIPS');?>">
                                        <?php echo JText::_('COM_COMMUNITY_DIGEST_VIDEOS_NUMBER_SETTINGS');?>
                                    </span>
                                    </td>
                                    <td>
                                        <select name="digest_email_include_videos_count">
                                            <?php for($i=1; $i <= 5; $i++) {?>
                                                <option value="<?php echo $i; ?>"<?php echo $this->config->get('digest_email_include_videos_count') == $i ? ' selected="selected"' : ''; ?>><?php echo $i;?></option>
                                            <?php } ?>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="key">
                                        <span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_DIGEST_VIDEOS_SHOW_THUMB_SETTINGS_TIPS');?>">
                                            <?php echo JText::_('COM_COMMUNITY_DIGEST_VIDEOS_SHOW_THUMB_SETTINGS');?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php echo CHTMLInput::checkbox('digest_email_video_include_thumb' ,'ace-switch ace-switch-5', null , $this->config->get('digest_email_video_include_thumb') ); ?>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="space-12"></div>
                <div class="widget-box">
                    <div class="widget-header widget-header-flat">
                        <h5><?php echo JText::_('COM_COMMUNITY_DIGEST_INCLUDE_GROUPS');?></h5>
                    </div>
                    <div class="widget-body">
                        <div class="widget-main">
                            <table>
                                <tr>
                                    <td class="key">
                                    <span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_DIGEST_INCLUDE_GROUPS_SETTINGS_TIPS');?>">
                                        <?php echo JText::_('COM_COMMUNITY_DIGEST_INCLUDE_GROUPS_SETTINGS');?>
                                    </span>
                                    </td>
                                    <td>
                                        <?php echo CHTMLInput::checkbox('digest_email_include_groups' ,'ace-switch ace-switch-5', null , $this->config->get('digest_email_include_groups') ); ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="key">
                                    <span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_DIGEST_GROUPS_NUMBER_SETTINGS_TIPS');?>">
                                        <?php echo JText::_('COM_COMMUNITY_DIGEST_GROUPS_NUMBER_SETTINGS');?>
                                    </span>
                                    </td>
                                    <td>
                                        <select name="digest_email_include_groups_count">
                                            <?php for($i=1; $i <= 5; $i++) {?>
                                                <option value="<?php echo $i; ?>"<?php echo $this->config->get('digest_email_include_groups_count') == $i ? ' selected="selected"' : ''; ?>><?php echo $i;?></option>
                                            <?php } ?>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="key">
                                        <span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_DIGEST_GROUPS_SHOW_COVER_SETTINGS_TIPS');?>">
                                            <?php echo JText::_('COM_COMMUNITY_DIGEST_GROUPS_SHOW_COVER_SETTINGS');?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php echo CHTMLInput::checkbox('digest_email_group_include_cover' ,'ace-switch ace-switch-5', null , $this->config->get('digest_email_group_include_cover') ); ?>
                                    </td>
                                </tr>

                                <?php /*
    Code already implemented for this, but we keep the email digest simple first.
 ?>
    <tr>
        <td class="key"><?php echo JText::_('COM_COMMUNITY_DIGEST_GROUPS_PHOTOS_SETTINGS');?></td>
        <td>
            <?php echo CHTMLInput::checkbox('digest_email_include_groups_photos' ,'ace-switch ace-switch-5', null , $this->config->get('digest_email_include_groups_photos') ); ?>
        </td>
    </tr>

    <tr>
        <td class="key"><?php echo JText::_('COM_COMMUNITY_DIGEST_GROUPS_VIDEOS_SETTINGS');?></td>
        <td>
            <?php echo CHTMLInput::checkbox('digest_email_include_groups_videos' ,'ace-switch ace-switch-5', null , $this->config->get('digest_email_include_groups_videos') ); ?>
        </td>
    </tr>
    <?php */ ?>
                                <tr>
                                    <td class="key">
                                    <span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_DIGEST_GROUPS_DISCUSSION_SETTINGS_TIPS');?>">
                                        <?php echo JText::_('COM_COMMUNITY_DIGEST_GROUPS_DISCUSSION_SETTINGS');?>
                                    </span>
                                    </td>
                                    <td>
                                        <?php echo CHTMLInput::checkbox('digest_email_include_groups_discussions' ,'ace-switch ace-switch-5', null , $this->config->get('digest_email_include_groups_discussions') ); ?>
                                    </td>
                                </tr>

                                <tr>
                                    <td class="key">
                                    <span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_DIGEST_GROUPS_ANNOUNCEMENT_SETTINGS_TIPS');?>">
                                    <?php echo JText::_('COM_COMMUNITY_DIGEST_GROUPS_ANNOUNCEMENT_SETTINGS');?>
                                    </span>
                                    </td>
                                    <td>
                                        <?php echo CHTMLInput::checkbox('digest_email_include_groups_announcements' ,'ace-switch ace-switch-5', null , $this->config->get('digest_email_include_groups_announcements') ); ?>
                                    </td>
                                </tr>

                                <tr>
                                    <td class="key">
                                        <span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_DIGEST_GROUPS_DISS_ANNOUNCE_SHOW_AVATAR_TIPS');?>">
                                            <?php echo JText::_('COM_COMMUNITY_DIGEST_GROUPS_DISS_ANNOUNCE_SHOW_AVATAR');?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php echo CHTMLInput::checkbox('digest_email_disscussion_announcement_avatar' ,'ace-switch ace-switch-5', null , $this->config->get('digest_email_disscussion_announcement_avatar') ); ?>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="space-12"></div>
                <div class="widget-box">
                    <div class="widget-header widget-header-flat">
                        <h5><?php echo JText::_('COM_COMMUNITY_DIGEST_INCLUDE_EVENTS');?></h5>
                    </div>
                    <div class="widget-body">
                        <div class="widget-main">
                            <table>
                                <tr>
                                    <td class="key">
                                    <span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_DIGEST_INCLUDE_EVENTS_SETTINGS_TIPS');?>">
                                        <?php echo JText::_('COM_COMMUNITY_DIGEST_INCLUDE_EVENTS_SETTINGS');?>
                                    </span>
                                    </td>
                                    <td>
                                        <?php echo CHTMLInput::checkbox('digest_email_include_events' ,'ace-switch ace-switch-5', null , $this->config->get('digest_email_include_events') ); ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="key">
                                    <span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_DIGEST_EVENTS_NUMBER_SETTINGS_TIPS');?>">
                                        <?php echo JText::_('COM_COMMUNITY_DIGEST_EVENTS_NUMBER_SETTINGS');?>
                                    </span>
                                    </td>
                                    <td>
                                        <select name="digest_email_include_events_count">
                                            <?php for($i=1; $i <= 5; $i++) {?>
                                                <option value="<?php echo $i; ?>"<?php echo $this->config->get('digest_email_include_events_count') == $i ? ' selected="selected"' : ''; ?>><?php echo $i;?></option>
                                            <?php } ?>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="key">
                                        <span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_DIGEST_EVENT_SHOW_COVER_SETTINGS_TIPS');?>">
                                            <?php echo JText::_('COM_COMMUNITY_DIGEST_EVENT_SHOW_COVER_SETTINGS');?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php echo CHTMLInput::checkbox('digest_email_event_include_cover' ,'ace-switch ace-switch-5', null , $this->config->get('digest_email_event_include_cover') ); ?>
                                    </td>
                                </tr>

                                <?php /* ?>
    <tr>
        <td class="key"><?php echo JText::_('COM_COMMUNITY_DIGEST_EVENTS_PHOTOS_SETTINGS');?></td>
        <td>
            <?php echo CHTMLInput::checkbox('digest_email_include_events_photos' ,'ace-switch ace-switch-5', null , $this->config->get('digest_email_include_events_photos') ); ?>
        </td>
    </tr>

    <tr>
        <td class="key"><?php echo JText::_('COM_COMMUNITY_DIGEST_EVENTS_VIDEOS_SETTINGS');?></td>
        <td>
            <?php echo CHTMLInput::checkbox('digest_email_include_events_videos' ,'ace-switch ace-switch-5', null , $this->config->get('digest_email_include_events_videos') ); ?>
        </td>
    </tr>
 <?php */ ?>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="span10">
                <div class="widget-box">
                    <div class="widget-header widget-header-flat">
                        <h5><?php echo JText::_('COM_COMMUNITY_DIGEST_WARNING');?></h5>
                        <div class="widget-toolbar no-border">
                            <a href="http://tiny.cc/js-email-digest" target="_blank">
                                <i class="js-icon-info-sign"></i>
                                <?php echo JText::_('COM_COMMUNITY_DOC'); ?></a>
                        </div>
                    </div>
                    <div class="widget-body">
                        <div class="widget-main">
                            <div class="alert alert-danger">
                                <strong><?php echo JText::_('COM_COMMUNITY_DIGEST_WARNING_INTRO');?></strong>
                            </div>
                            <ol>
                                <li><?php echo JText::_('COM_COMMUNITY_DIGEST_WARNING_EMAIL_LIMIT');?></li>
                                <li><?php echo JText::_('COM_COMMUNITY_DIGEST_WARNING_EMAIL_IMAGES');?></li>
                                <li><?php echo JText::_('COM_COMMUNITY_DIGEST_WARNING_DOCS');?></li>
                            </ol>
                            <div class="alert alert-warning">
                                <strong><?php echo JText::_('COM_COMMUNITY_DIGEST_WARNING_DISCLAIMER');?></strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div id="messaging-result" style="display: none;">
        <fieldset>
            <legend><?php echo JText::_('COM_COMMUNITY_MESSAGING_SENDING_MESSAGES');?></legend>
            <div><?php echo JText::_('COM_COMMUNITY_MESSAGING_DONT_REFRESH_PAGE');?></div>
            <div id="no-progress"><?php echo JText::_('COM_COMMUNITY_MESSAGING_NO_PROGRESS');?></div>
            <div id="progress-status" style="padding-top: 5px;"></div>
        </fieldset>
    </div>
    <?php echo JHTML::_( 'form.token' ); ?>
    <input type="hidden" name="task" value="display" />
    <input type="hidden" name="view" value="digest" />
    <input type="hidden" name="option" value="com_community" />
</form>

