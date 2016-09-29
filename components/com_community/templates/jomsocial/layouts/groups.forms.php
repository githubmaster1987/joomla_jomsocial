<?php
/**
* @copyright (C) 2013 iJoomla, Inc. - All rights reserved.
* @license GNU General Public License, version 2 (http://www.gnu.org/licenses/gpl-2.0.html)
* @author iJoomla.com <webmaster@ijoomla.com>
* @url https://www.jomsocial.com/license-agreement
* The PHP code portions are distributed under the GPL license. If not otherwise stated, all images, manuals, cascading style sheets, and included JavaScript *are NOT GPL, and are released under the IJOOMLA Proprietary Use License v1.0
* More info at https://www.jomsocial.com/license-agreement
*/
defined('_JEXEC') or die();
?>

<div class="joms-page">
    <h3 class="joms-page__title"><?php echo JText::_($isNew ? 'COM_COMMUNITY_GROUPS_CREATE_NEW_GROUP' : 'COM_COMMUNITY_GROUPS_EDIT_TITLE'); ?></h3>
    <form method="POST" action="<?php echo CRoute::getURI(); ?>" onsubmit="return joms_validate_form( this );">

        <div class="joms-form__group">
            <?php if ($isNew) { ?>
                <p><?php echo JText::_('COM_COMMUNITY_GROUPS_CREATE_DESC'); ?></p>
                <?php if ($groupCreationLimit != 0 && $groupCreated / $groupCreationLimit >= COMMUNITY_SHOW_LIMIT) { ?>
                <p><?php echo JText::sprintf('COM_COMMUNITY_GROUPS_LIMIT_STATUS', $groupCreated, $groupCreationLimit); ?></p>
                <?php } ?>
            <?php } ?>
        </div>

        <?php if ($beforeFormDisplay) { ?>
        <div class="joms-form__group"><?php echo $beforeFormDisplay; ?></div>
        <?php } ?>

        <div class="joms-form__group" style="margin-bottom:5px">
            <span><?php echo JText::_('COM_COMMUNITY_GROUPS_TITLE'); ?> <span class="joms-required">*</span></span>
            <input type="text" class="joms-input" name="name" required=""
                title="<?php echo JText::_('COM_COMMUNITY_GROUPS_TITLE_TIPS'); ?>"
                value="<?php echo $this->escape($group->name); ?>">
        </div>

        <div class="joms-form__group">
            <span></span>
            <label class="joms-checkbox">
                <input type="checkbox" class="joms-checkbox" name="approvals" onclick="joms_checkPrivacy();" value="1"
                    <?php echo ($group->approvals == COMMUNITY_PRIVATE_GROUP) ? ' checked="checked"' : ''; ?>>
                <span title="<?php echo JText::_('COM_COMMUNITY_GROUPS_APPROVAL_TIPS'); ?>">
                    <?php echo JText::_('COM_COMMUNITY_GROUPS_PRIVATE_LABEL'); ?></span>
            </label>

            <label class="joms-checkbox">
                <input type="checkbox" class="joms-checkbox" name="unlisted" value="1"
                    <?php echo ($group->approvals == COMMUNITY_PRIVATE_GROUP) ? '' : ' disabled="disabled"'; ?>
                    <?php echo ($group->unlisted == 1 && $group->approvals == COMMUNITY_PRIVATE_GROUP) ? ' checked="checked"' : ''; ?>>
                <span title="<?php echo JText::_('COM_COMMUNITY_GROUPS_UNLISTED_TIPS'); ?>">
                    <?php echo JText::_('COM_COMMUNITY_GROUPS_UNLISTED'); ?>
                </span>
            </label>

        </div>

        <script type="text/javascript">
            function joms_checkPrivacy() {
                var eventClosedCheckbox = joms.jQuery('[name=approvals]');
                var eventUnlistedCheckbox = joms.jQuery('[name=unlisted]');

                if( eventClosedCheckbox.prop('checked') === true ) {
                    eventUnlistedCheckbox.removeAttr('disabled');
                } else {
                    eventUnlistedCheckbox[0].checked = false;
                    eventUnlistedCheckbox.attr('disabled', 'disabled');
                }
            }
        </script>

        <div class="joms-form__group">
            <span title="<?php echo JText::_('COM_COMMUNITY_GROUPS_SUMMARY_TIPS')?>"><?php echo JText::_('COM_COMMUNITY_GROUPS_SUMMARY'); ?></span>
            <textarea class="joms-textarea" name="summary" data-maxchars="120"><?php echo $this->escape($group->summary); ?></textarea>
        </div>

        <div class="joms-form__group joms-textarea--mobile">
            <span title="<?php echo JText::_('COM_COMMUNITY_GROUPS_DESCRIPTION_TIPS')?>"><?php echo JText::_('COM_COMMUNITY_GROUPS_DESCRIPTION'); ?> <span class="joms-required">*</span></span>
            <textarea class="joms-textarea" name="description" data-wysiwyg="trumbowyg" data-wysiwyg-type="group" data-wysiwyg-id="<?php echo $isNew ? 0 : $group->id ?>"><?php echo $this->escape($group->description); ?></textarea>
        </div>

        <div class="joms-form__group">
            <span><?php echo JText::_('COM_COMMUNITY_GROUPS_CATEGORY'); ?> <span class="joms-required">*</span></span>
            <?php echo $lists['categoryid']; ?>
        </div>

        <?php if ($config->get('enablephotos') && $config->get('groupphotos')) { ?>
        <?php $photoAllowed = $params->get('photopermission', 1) >= 1; ?>

        <div class="joms-form__group">
            <span><?php echo JText::_('COM_COMMUNITY_GROUPS_RECENT_PHOTO'); ?></span>
            <div>
                <label class="joms-checkbox">
                    <input type="checkbox" class="joms-checkbox joms-js--group-photo-flag" name="photopermission-admin" value="1"<?php echo $photoAllowed ? ' checked="checked"' : ''; ?>>
                    <span title="<?php echo JText::_('COM_COMMUNITY_GROUPS_PHOTO_PERMISSION_TIPS'); ?>"><?php echo JText::_('COM_COMMUNITY_GROUPS_PHOTO_UPLOAD_ALOW_ADMIN'); ?></span>
                </label>
            </div>
            <div class="joms-js--group-photo-setting" style="display:none">
                <label class="joms-checkbox">
                    <input type="checkbox" class="joms-checkbox" name="photopermission-member" value="1"<?php echo $photoAllowed ? '' : ' disabled="disabled"'; ?><?php echo $photoAllowed && ( $params->get('photopermission') == GROUP_PHOTO_PERMISSION_ALL ) ? ' checked="checked"' : ''; ?>>
                    <span title="<?php echo JText::_('COM_COMMUNITY_GROUPS_PHOTO_UPLOAD_ALLOW_MEMBER_TIPS')?>"><?php echo JText::_('COM_COMMUNITY_GROUPS_PHOTO_UPLOAD_ALLOW_MEMBER'); ?></span>
                </label>
                <select type="text" class="joms-select" name="grouprecentphotos" title="<?php echo JText::_('COM_COMMUNITY_GROUPS_RECENT_PHOTOS_TIPS'); ?>">
                    <?php for($i = 2; $i <= 10; $i = $i+2){ ?>
                    <option value="<?php echo $i; ?>" <?php echo ($group->grouprecentphotos == $i || ($i == 6 && $group->grouprecentphotos == 0)) ? 'selected': ''; ?>><?php echo $i; ?></option>
                    <?php } ?>
                </select>
            </div>
        </div>

        <?php } ?>

        <?php if ($config->get('enablevideos') && $config->get('groupvideos')) { ?>
        <?php $videoAllowed = $params->get('videopermission', 1) >= 1; ?>

        <div class="joms-form__group">
            <span><?php echo JText::_('COM_COMMUNITY_GROUPS_RECENT_VIDEO'); ?></span>
            <div>
                <label class="joms-checkbox">
                    <input type="checkbox" class="joms-checkbox joms-js--group-video-flag" name="videopermission-admin" value="1"<?php echo $videoAllowed ? ' checked="checked"' : ''; ?>>
                    <span title="<?php echo JText::_('COM_COMMUNITY_GROUPS_VIDEOS_PERMISSION_TIPS'); ?>"><?php echo JText::_('COM_COMMUNITY_GROUPS_VIDEO_UPLOAD_ALLOW_ADMIN'); ?></span>
                </label>
            </div>
            <div class="joms-js--group-video-setting" style="display:none">
                <label class="joms-checkbox">
                    <input type="checkbox" class="joms-checkbox" name="videopermission-member" value="1"<?php echo $videoAllowed ? '' : ' disabled="disabled"'; ?><?php echo $videoAllowed && ( $params->get('videopermission') == GROUP_VIDEO_PERMISSION_ALL ) ? ' checked="checked"' : ''; ?>>
                    <span title="<?php echo JText::_('COM_COMMUNITY_GROUPS_VIDEO_UPLOAD_ALLOW_MEMBER_TIPS')?>"><?php echo JText::_('COM_COMMUNITY_GROUPS_VIDEO_UPLOAD_ALLOW_MEMBER'); ?></span>
                </label>
                <select type="text" class="joms-select" name="grouprecentvideos" title="<?php echo JText::_('COM_COMMUNITY_GROUPS_RECENT_VIDEO_TIPS'); ?>">
                    <?php for ($i = 2; $i <= 10; $i = $i+2) { ?>
                        <option value="<?php echo $i; ?>" <?php echo ($group->grouprecentvideos == $i || ($i == 6 && $group->grouprecentvideos == 0)) ? 'selected': ''; ?>><?php echo $i; ?></option>
                    <?php } ?>
                </select>
            </div>
        </div>

        <?php } ?>

        <?php if ($config->get('enableevents') && $config->get('group_events')) { ?>
        <?php $eventAllowed = $params->get('eventpermission', 1) >= 1; ?>

        <div class="joms-form__group">
            <span><?php echo JText::_('COM_COMMUNITY_GROUP_EVENTS'); ?></span>
            <div>
                <label class="joms-checkbox">
                    <input type="checkbox" class="joms-checkbox joms-js--group-event-flag" name="eventpermission-admin" value="1"<?php echo $eventAllowed ? ' checked="checked"' : ''; ?>>
                    <span title="<?php echo JText::_('COM_COMMUNITY_GROUP_EVENTS_PERMISSIONS'); ?>"><?php echo JText::_('COM_COMMUNITY_GROUP_EVENTS_ADMIN_CREATION'); ?></span>
                </label>
            </div>
            <div class="joms-js--group-event-setting" style="display:none">
                <label class="joms-checkbox">
                    <input type="checkbox" class="joms-checkbox" name="eventpermission-member" value="1"<?php echo $eventAllowed ? '' : ' disabled="disabled"'; ?><?php echo $eventAllowed && ( $params->get('eventpermission') == GROUP_EVENT_PERMISSION_ALL ) ? ' checked="checked"' : ''; ?>>
                    <span title="<?php echo JText::_('COM_COMMUNITY_GROUPS_EVENTS_MEMBERS_CREATION_TIPS')?>"><?php echo JText::_('COM_COMMUNITY_GROUP_EVENTS_MEMBERS_CREATION'); ?></span>
                </label>
                <select type="text" class="joms-select" name="grouprecentevents" title="<?php echo JText::_('COM_COMMUNITY_GROUPS_EVENT_TIPS'); ?>">
                    <?php for ($i = 2; $i <= 10; $i = $i+2) { ?>
                        <option value="<?php echo $i; ?>" <?php echo ($group->grouprecentevents == $i || ($i == 6 && $group->grouprecentevents == 0)) ? 'selected': ''; ?>><?php echo $i; ?></option>
                    <?php } ?>
                </select>
            </div>
        </div>

        <?php } ?>

        <script>
            joms.onStart(function( $ ) {
                $('.joms-js--group-photo-flag').on( 'click', function() {
                    var $div = $('.joms-js--group-photo-setting'),
                        $checkbox = $div.find('input');

                    if ( this.checked ) {
                        $checkbox.removeAttr('disabled');
                        $div.show();
                    } else {
                        $checkbox[0].checked = false;
                        $checkbox.attr('disabled', 'disabled');
                        $div.hide();
                    }
                }).triggerHandler('click');

                $('.joms-js--group-video-flag').on( 'click', function() {
                    var $div = $('.joms-js--group-video-setting'),
                        $checkbox = $div.find('input');

                    if ( this.checked ) {
                        $checkbox.removeAttr('disabled');
                        $div.show();
                    } else {
                        $checkbox[0].checked = false;
                        $checkbox.attr('disabled', 'disabled');
                        $div.hide();
                    }
                }).triggerHandler('click');

                $('.joms-js--group-event-flag').on( 'click', function() {
                    var $div = $('.joms-js--group-event-setting'),
                        $checkbox = $div.find('input');

                    if ( this.checked ) {
                        $checkbox.removeAttr('disabled');
                        $div.show();
                    } else {
                        $checkbox[0].checked = false;
                        $checkbox.attr('disabled', 'disabled');
                        $div.hide();
                    }
                }).triggerHandler('click');
            });
        </script>

        <?php if ($config->get('groupdiscussfilesharing') && $config->get('creatediscussion')) { ?>

        <div class="joms-form__group">
            <span><?php echo JText::_('COM_COMMUNITY_DISCUSSION'); ?></span>
            <label class="joms-checkbox">
                <input type="checkbox" class="joms-checkbox" name="groupdiscussionfilesharing" value="1"<?php echo ($params->get('groupdiscussionfilesharing') >= 1 || $isNew) ? ' checked="checked"' : ''; ?>>
                <span title="<?php echo JText::_('COM_COMMUNITY_GROUPS_DISCUSSION_ENABLE_FILE_SHARING_TIPS'); ?>"><?php echo JText::_('COM_COMMUNITY_GROUPS_DISCUSSION_ENABLE_FILE_SHARING'); ?></span>
            </label>
            <input type="hidden" name="discussordering" value="0" />
            <!--<label>
                <input type="checkbox" class="joms-checkbox" name="discussordering" value="1"<?php echo ($group->discussordering == 1 || $isNew) ? ' checked="checked"' : ''; ?>>
                <span title="<?php echo JText::_('COM_COMMUNITY_GROUPS_ORDERING_TIPS'); ?>"><?php echo JText::_('COM_COMMUNITY_GROUPS_DISCUSS_ORDER_CREATION_DATE'); ?></span>
            </label>-->
        </div>

        <?php } ?>

        <?php if ($config->get('createannouncement')) { ?>

        <div class="joms-form__group">
            <span><?php echo JText::_('COM_COMMUNITY_ANNOUNCEMENT'); ?></span>
            <label class="joms-checkbox">
                <input type="checkbox" class="joms-checkbox" name="groupannouncementfilesharing" value="1"<?php echo ($params->get('groupannouncementfilesharing') >= 1 || $isNew) ? ' checked="checked"' : ''; ?>>
                <span title="<?php echo JText::_('COM_COMMUNITY_GROUPS_ANNOUNCEMENT_ENABLE_FILE_SHARING_TIPS'); ?>"><?php echo JText::_('COM_COMMUNITY_GROUPS_ANNOUNCEMENT_ENABLE_FILE_SHARING'); ?></span>
            </label>
        </div>

        <?php } ?>

        <div class="joms-form__group">
            <span><?php echo JText::_('COM_COMMUNITY_GROUPS_NOTIFICATION'); ?></span>
            <label class="joms-checkbox">
                <input type="checkbox" class="joms-checkbox" name="newmembernotification" value="1"<?php echo ($params->get('newmembernotification') || $isNew) ? ' checked="checked"' : ''; ?>>
                <span title="<?php echo JText::_('COM_COMMUNITY_GROUPS_NEW_MEMBER_NOTIFICATION_TIPS'); ?>"><?php echo JText::_('COM_COMMUNITY_GROUPS_NEW_MEMBER_NOTIFICATION'); ?></span>
            </label>
            <label class="joms-checkbox">
                <input type="checkbox" class="joms-checkbox" name="joinrequestnotification" value="1"<?php echo $params->get('joinrequestnotification', '1') == true  ? ' checked="checked"' : ''; ?>>
                <span title="<?php echo JText::_('COM_COMMUNITY_GROUPS_JOIN_REQUEST_NOTIFICATION_TIPS'); ?>"><?php echo JText::_('COM_COMMUNITY_GROUPS_JOIN_REQUEST_NOTIFICATION'); ?></span>
            </label>
            <label class="joms-checkbox">
                <input type="checkbox" class="joms-checkbox" name="wallnotification" value="1"<?php echo $params->get('wallnotification', '1') == true ? ' checked="checked"' : ''; ?>>
                <span title="<?php echo JText::_('COM_COMMUNITY_GROUPS_WALL_NOTIFICATION_TIPS'); ?>"><?php echo JText::_('COM_COMMUNITY_GROUPS_WALL_NOTIFICATION'); ?></span>
            </label>
        </div>

        <?php if ($afterFormDisplay) { ?>
        <div class="joms-form__group"><?php echo $afterFormDisplay; ?></div>
        <?php } ?>

        <div class="joms-form__group">
            <span></span>
            <div>
                <?php if ($isNew) { ?>
                <input name="action" type="hidden" value="save">
                <?php } ?>
                <input type="hidden" name="groupid" value="<?php echo $group->id; ?>">
                <?php echo JHTML::_('form.token'); ?>
                <input type="button" value="<?php echo JText::_('COM_COMMUNITY_CANCEL_BUTTON'); ?>" class="joms-button--neutral joms-button--full-small" onclick="history.go(-1); return false;">
                <input type="submit" value="<?php echo JText::_($isNew ? 'COM_COMMUNITY_GROUPS_CREATE_GROUP' : 'COM_COMMUNITY_SAVE_BUTTON'); ?>" class="joms-button--primary joms-button--full-small">
            </div>
        </div>

    </form>
</div>
<script>
    function joms_validate_form() {
        return false;
    }

    (function( w ) {
        w.joms_queue || (w.joms_queue = []);
        w.joms_queue.push(function() {
            joms_validate_form = function( $form ) {
                var errors = 0;

                $form = joms.jQuery( $form );
                $form.find('[required]').each(function() {
                    var $el = joms.jQuery( this );
                    if ( !joms.jQuery.trim( $el.val() ) ) {
                        $el.triggerHandler('blur');
                        errors++;
                    }
                });

                return !errors;
            }
        });
    })( window );
</script>
