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

<form method="POST" action="<?php echo CRoute::getURI(); ?>" class="joms-js--form-preferences">
    <div class="joms-page">
        <h3 class="joms-page__title">
            <?php echo JText::_('COM_COMMUNITY_EDIT_PREFERENCES'); ?>
        </h3>

        <div class="joms-subnav__menu">
            <a data-ui-object="joms-dropdown-button" class="joms-button--neutral joms-button--full" href="javascript:">
                <span class="joms-js--tab-dd-label"><?php echo JText::_('COM_COMMUNITY_PROFILE_PREFERENCES_GENERAL'); ?></span>
                <svg class="joms-icon" viewBox="0 0 16 16">
                    <use xlink:href="#joms-icon-arrow-down" class="joms-icon--svg-fixed"></use>
                </svg>
            </a>
            <ul class="joms-dropdown">
                <li class="joms-js--tab-btn joms-js--tab-btn-general active"><a href="#general" onclick="joms_preferences_changetab('general');"><?php echo JText::_('COM_COMMUNITY_PROFILE_PREFERENCES_GENERAL'); ?></a></li>
                <li class="joms-js--tab-btn joms-js--tab-btn-privacy"><a href="#privacy" onclick="joms_preferences_changetab('privacy');"><?php echo JText::_('COM_COMMUNITY_PROFILE_PREFERENCES_PRIVACY'); ?></a></li>
                <li class="joms-js--tab-btn joms-js--tab-btn-email"><a href="#email" onclick="joms_preferences_changetab('email');"><?php echo JText::_('COM_COMMUNITY_PROFILE_PREFERENCES_EMAIL'); ?></a></li>
                <li class="joms-js--tab-btn joms-js--tab-btn-ignorelist"><a href="#ignorelist" onclick="joms_preferences_changetab('ignorelist');"><?php echo JText::_('COM_COMMUNITY_PROFILE_PREFERENCES_BLOCKIGNORELIST'); ?></a></li>
            </ul>
            <div class="joms-gap"></div>
        </div>

        <div class="joms-subnav--desktop">
            <ul>
                <li class="joms-js--tab-btn joms-js--tab-btn-general active"><a href="#general" onclick="joms_preferences_changetab('general');"><?php echo JText::_('COM_COMMUNITY_PROFILE_PREFERENCES_GENERAL'); ?></a></li>
                <li class="joms-js--tab-btn joms-js--tab-btn-privacy"><a href="#privacy" onclick="joms_preferences_changetab('privacy');"><?php echo JText::_('COM_COMMUNITY_PROFILE_PREFERENCES_PRIVACY'); ?></a></li>
                <li class="joms-js--tab-btn joms-js--tab-btn-email"><a href="#email" onclick="joms_preferences_changetab('email');"><?php echo JText::_('COM_COMMUNITY_PROFILE_PREFERENCES_EMAIL'); ?></a></li>
                <li class="joms-js--tab-btn joms-js--tab-btn-ignorelist"><a href="#ignorelist" onclick="joms_preferences_changetab('ignorelist');"><?php echo JText::_('COM_COMMUNITY_PROFILE_PREFERENCES_BLOCKIGNORELIST'); ?></a></li>
            </ul>
        </div>

        <div class="joms-js--tab-content joms-js--tab-content-general">
            <div class="joms-gap"></div>

            <?php if ($beforeFormDisplay) { ?>
            <div class="joms-form__group"><?php echo $beforeFormDisplay; ?></div>
            <?php } ?>

            <div class="joms-form__group">
                <span><?php echo JText::_('COM_COMMUNITY_PREFERENCES_ACTIVITY_LIMIT'); ?></span>
                <input type="text" class="joms-input" name="activityLimit" maxlength="3"
                    title="<?php echo JText::_('COM_COMMUNITY_PREFERENCES_ACTIVITY_LIMIT_DESC'); ?>"
                    value="<?php echo $params->get('activityLimit', 20); ?>">
            </div>

            <div class="joms-form__group">
                <span><?php echo JText::_('COM_COMMUNITY_PREFERENCES_PROFILE_LIKES'); ?></span>
                <input type="checkbox" class="joms-checkbox" name="profileLikes" value="1"
                    title="<?php echo JText::_('COM_COMMUNITY_PROFILE_LIKE_ENABLE_DESC'); ?>"
                    <?php echo $params->get('profileLikes', 1) == 1 ? 'checked="checked"' : ''; ?>>
            </div>

            <div class="joms-form__group">
                <span><?php echo JText::_('COM_COMMUNITY_PREFERENCES_PROFILE_SHOW_ONLINE_STATUS'); ?></span>
                <input type="checkbox" class="joms-checkbox" name="showOnlineStatus" value="1"
                    title="<?php echo JText::_('COM_COMMUNITY_PREFERENCES_PROFILE_SHOW_ONLINE_STATUS_DESC'); ?>"
                    <?php echo $params->get('showOnlineStatus', 1) == 1 ? 'checked="checked"' : ''; ?>>
            </div>

            <div class="joms-form__group">
                <span><?php echo JText::_('COM_COMMUNITY_PREFERENCES_PROFILE_VIEW'); ?></span>
                <?php echo CPrivacy::getHTML('privacyProfileView', $params->get('privacyProfileView'), COMMUNITY_PRIVACY_BUTTON_LARGE, array('public' => true, 'members' => true, 'friends' => true, 'self' => false)); ?>
            </div>

            <?php
            /*for future settings if online status is needed

            <div class="joms-form__group">
                <span><?php echo JText::_('COM_COMMUNITY_PREFERENCES_PROFILE_SHOW_ONLINE_STATUS'); ?></span>
                <input type="checkbox" class="joms-checkbox" name="showOnlineStatus" value="1"
                       title="<?php echo JText::_('COM_COMMUNITY_PREFERENCES_PROFILE_SHOW_ONLINE_STATUS_DESC'); ?>"
                    <?php echo $params->get('showOnlineStatus', 1) == 1 ? 'checked="checked"' : ''; ?>>
            </div>

            */ ?>



            <?php if ( $jConfig->get('sef') ) { ?>

            <div class="joms-form__group">
                <span><?php echo JText::_('COM_COMMUNITY_YOUR_PROFILE_URL'); ?></span>
                <?php echo JText::sprintf('COM_COMMUNITY_YOUR_CURRENT_PROFILE_URL', $prefixURL); ?>
            </div>

            <?php } ?>

            <div class="joms-form__group">
                <span></span>
                <input type="submit" value="<?php echo JText::_('COM_COMMUNITY_SAVE_CHANGES_BUTTON'); ?>" class="joms-button--primary joms-button--full-small">
            </div>

        </div>

        <div class="joms-js--tab-content joms-js--tab-content-privacy" style="display:none">
            <div class="joms-gap"></div>

            <div class="joms-form__group">
                <span><?php echo JText::_('COM_COMMUNITY_PRIVACY_FRIENDS_FIELD'); ?></span>
                <?php echo CPrivacy::getHTML('privacyFriendsView', $params->get('privacyFriendsView'), COMMUNITY_PRIVACY_BUTTON_LARGE); ?>
            </div>

            <?php if ($config->get('enablephotos')) { ?>

            <div class="joms-form__group">
                <span><?php echo JText::_('COM_COMMUNITY_PRIVACY_PHOTOS_FIELD'); ?></span>
                <?php echo CPrivacy::getHTML('privacyPhotoView', $params->get('privacyPhotoView'), COMMUNITY_PRIVACY_BUTTON_LARGE); ?>
                <label class="joms-checkbox">
                    <input type="checkbox" class="joms-checkbox" name="resetPrivacyPhotoView" value="1">
                    <span><?php echo JText::_('COM_COMMUNITY_PHOTOS_PRIVACY_APPLY_TO_ALL'); ?></span>
                </label>
            </div>

            <?php } ?>

            <?php if ($config->get('enablevideos')) { ?>

            <div class="joms-form__group">
                <span><?php echo JText::_('COM_COMMUNITY_PRIVACY_VIDEOS_FIELD'); ?></span>
                <?php echo CPrivacy::getHTML('privacyVideoView', $params->get('privacyVideoView'), COMMUNITY_PRIVACY_BUTTON_LARGE); ?>
                <label class="joms-checkbox">
                    <input type="checkbox" class="joms-checkbox" name="resetPrivacyVideoView" value="1">
                    <span><?php echo JText::_('COM_COMMUNITY_VIDEOS_PRIVACY_RESET_ALL'); ?></span>
                </label>
            </div>

            <?php } ?>

            <?php if ($config->get('enablegroups')) { ?>

            <div class="joms-form__group">
                <span><?php echo JText::_('COM_COMMUNITY_PRIVACY_GROUPS_FIELD'); ?></span>
                <?php echo CPrivacy::getHTML('privacyGroupsView', $params->get('privacyGroupsView'), COMMUNITY_PRIVACY_BUTTON_LARGE); ?>
            </div>

            <?php } ?>

            <?php if ($config->get('privacy_search_email') == 1) { ?>
            <div class="joms-form__group">
                <span></span>
                <label class="joms-checkbox">
                    <input type="checkbox" class="joms-checkbox" name="search_email" value="1" <?php if ($my->get('_search_email') == 1) { ?>checked="checked" <?php } ?>>
                    <span><?php echo JText::_('COM_COMMUNITY_RESPECT_EMAIL_PRIVACY'); ?></span>
                </label>
            </div>
            <?php } ?>

            <div class="joms-form__group">
                <span></span>
                <input type="submit" value="<?php echo JText::_('COM_COMMUNITY_SAVE_CHANGES_BUTTON'); ?>" class="joms-button--primary joms-button--full-small">
            </div>

        </div>

        <div class="joms-js--tab-content joms-js--tab-content-email" style="display:none">
            <div class="joms-gap"></div>

            <table class="joms-table">
                <thead>
                    <tr>
                        <th></th>
                        <th>
                            <?php echo JText::_('COM_COMMUNITY_PRIVACY_EMAIL_LABEL'); ?>
                        </th>
                        <th>
                            <?php echo JText::_('COM_COMMUNITY_PRIVACY_NOTIFICATION_LABEL'); ?>
                        </th>
                    </tr>
                </thead>

            <?php

            $isadmin = COwnerHelper::isCommunityAdmin();
            foreach ($notificationTypes->getTypes() as $group) {
                if ($notificationTypes->isAdminOnlyGroup($group->description) && !$isadmin) {
                    continue;
                }
                ?>
                <tr class="joms-table__head">
                    <td class="joms-table__title"><?php echo JText::_($group->description); ?></td>
                    <td class="joms-table__option"><input type="checkbox" class="joms-checkbox" onclick="toggleChecked('email<?php echo JText::_($group->description); ?>', this.checked)" ></td>
                    <td class="joms-table__option"><input type="checkbox" class="joms-checkbox" onclick="toggleChecked('global<?php echo JText::_($group->description); ?>', this.checked)" ></td>
                </tr>
                <?php
                foreach ($group->child as $id => $type) {
                    if ($type->adminOnly && !$isadmin)
                        continue;
                    $emailId = $notificationTypes->convertEmailId($id);
                    $emailset = $params->get($emailId, $config->get($emailId));
                    $notifId = $notificationTypes->convertNotifId($id);
                    $notifset = $params->get($notifId, $config->get($notifId));
                    ?>
                    <tr>
                        <td class="joms-table__desc"><?php echo JText::_($type->description); ?></td>
                        <td class="joms-table__option">
                            <input type="hidden" name="<?php echo $emailId; ?>" value="0" />
                            <input id="<?php echo $emailId; ?>" type="checkbox" name="<?php echo $emailId; ?>" value="1" <?php if ($emailset == 1) echo 'checked="checked"'; ?> class="joms-checkbox email<?php echo JText::_($group->description); ?>" />
                        </td>
                        <td class="joms-table__option">
                            <input type="hidden" name="<?php echo $notifId; ?>" value="0" />
                            <input id="<?php echo $notifId; ?>" type="checkbox" name="<?php echo $notifId; ?>" value="1" <?php if ($notifset == 1) echo 'checked="checked"'; ?> class="joms-checkbox global<?php echo JText::_($group->description); ?>" />
                        </td>
                    </tr>
                    <?php
                }
            }

            ?>
            </table>

            <div class="joms-gap"></div>

            <?php if ($afterFormDisplay) { ?>
            <div class="joms-form__group"><?php echo $afterFormDisplay; ?></div>
            <?php } ?>

            <div class="joms-gap"></div>

            <div class="joms-form__group">
                <span></span>
                <input type="submit" value="<?php echo JText::_('COM_COMMUNITY_SAVE_CHANGES_BUTTON'); ?>" class="joms-button--primary joms-button--full-small">
            </div>
        </div>

        <div class="joms-js--tab-content joms-js--tab-content-ignorelist" style="display:none">
            <div class="joms-gap"></div>

            <ul class="joms-list--friend">
                <?php
                foreach ($blockedUsers as $row) {
                    $user = CFactory::getUser($row->blocked_userid);
                    $user->profileLink = CUrl::build('profile', '', array('userid' => $user->id));
                    ?>
                    <li class="joms-list__item">
                        <div class="joms-list__avatar">
                            <a href="<?php echo $user->profileLink; ?>" class="joms-avatar">
                                <img src="<?php echo $user->getThumbAvatar(); ?>" alt="<?php echo $user->getDisplayName(); ?>" >
                            </a>
                        </div>
                        <div class="joms-list__body">
                            <a href="<?php echo $user->profileLink; ?>">
                                <h4 class="joms-text--username">
                                    <?php echo $user->getDisplayName(); ?>
                                </h4>
                            </a>
                            <span class="joms-text--title">
                                <?php echo JText::_('COM_COMMUNITY_PREFERENCES_' . strtoupper(($row->type == 'block') ? 'blocked' : 'ignored' )); ?>
                            </span>
                            <div class="joms-list__options">
                                <a href="javascript:" data-ui-object="joms-dropdown-button">
                                    <svg viewBox="0 0 14 20" class="joms-icon">
                                        <use xlink:href="#joms-icon-cog"></use>
                                    </svg>
                                </a>
                            <?php if ($row->type == 'block' || $row->type == '') { ?>
                                <ul class="joms-dropdown">
                                    <li><a href="javascript:" onclick="joms.api.userUnblock(<?php echo $user->id; ?>);"><?php echo JText::_('COM_COMMUNITY_PREFERENCES_UNBLOCK'); ?></a></li>
                                    <li><a href="javascript:" onclick="joms.api.userIgnore(<?php echo $user->id; ?>);"><?php echo JText::_('COM_COMMUNITY_PREFERENCES_IGNORE'); ?></a></li>
                            <?php } else { ?>
                                <ul class="joms-dropdown">
                                    <li><a href="javascript:" onclick="joms.api.userUnignore(<?php echo $user->id; ?>);" ><?php echo JText::_('COM_COMMUNITY_PREFERENCES_UNIGNORE'); ?></a></li>
                                    <li><a href="javascript:" onclick="joms.api.userBlock(<?php echo $user->id; ?>);"><?php echo JText::_('COM_COMMUNITY_PREFERENCES_BLOCK'); ?></a></li>
                                </ul>
                            <?php } ?>
                            </div>
                        </div>
                    </li>
                <?php } ?>
            </ul>
        </div>

    </div>
</form>

<script>

    function toggleChecked(className, status) {
        joms.jQuery("." + className).each(function() {
            joms.jQuery(this).attr("checked", status);
        })
    }

    function joms_preferences_changetab( id ) {
        var $tabcontent = joms.jQuery( '.joms-js--tab-content-' + id ),
            $tabbutton = joms.jQuery( '.joms-js--tab-btn-' + id ),
            $tabddlabel = joms.jQuery('.joms-js--tab-dd-label'),
            $form = joms.jQuery('.joms-js--form-preferences'),
            formAction = '<?php echo CRoute::getURI(); ?>',
            labels;

        labels = {
            general    : '<?php echo JText::_("COM_COMMUNITY_PROFILE_PREFERENCES_GENERAL", TRUE); ?>',
            privacy    : '<?php echo JText::_("COM_COMMUNITY_PROFILE_PREFERENCES_PRIVACY", TRUE); ?>',
            email      : '<?php echo JText::_("COM_COMMUNITY_PROFILE_PREFERENCES_EMAIL", TRUE); ?>',
            ignorelist : '<?php echo JText::_("COM_COMMUNITY_PROFILE_PREFERENCES_BLOCKIGNORELIST", TRUE); ?>'
        };

        if ( $tabcontent.length ) {
            $tabcontent.siblings('.joms-js--tab-content').hide();
            $tabcontent.show();
            $tabbutton.siblings('.joms-js--tab-btn').removeClass('active');
            $tabbutton.addClass('active');
            $tabddlabel.html( labels[ id ] );
            $form.attr( 'action', formAction + '#' + id );
        }
    }

    window.joms_queue || (window.joms_queue = []);
    window.joms_queue.push(function() {
        var hash = ( window.location.href.split('#')[1] || '' ),
            arr = [ 'privacy', 'email', 'ignorelist' ];

        if ( arr.indexOf( hash ) >= 0 ) {
            joms_preferences_changetab( hash );
        }
    });

</script>
