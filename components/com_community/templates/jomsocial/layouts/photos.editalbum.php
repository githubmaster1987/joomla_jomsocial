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
    <h3 class="joms-page__title"><?php echo isset($title) ? $title : JText::_('COM_COMMUNITY_PHOTOS_CREATE_NEW_ALBUM_TITLE')  ?></h3>

    <?php echo $submenu;?>

    <div class="joms-gap"></div>

    <form method="POST" action="<?php echo CRoute::getURI(); ?>">

        <div class="joms-gap"></div>

        <?php if ($beforeFormDisplay) { ?>
        <div class="joms-form__group">
            <?php echo $beforeFormDisplay; ?>
        </div>
        <?php } ?>

        <div class="joms-form__group">
            <span><?php echo JText::_('COM_COMMUNITY_PHOTOS_ALBUM_NAME'); ?> <span class="joms-required">*</span></span>
            <input type="text" class="joms-input" name="name" value="<?php echo $this->escape($album->name); ?>" <?php echo (isset($album) && $album->type == 'profile.avatar' ) ? 'disabled' : 'required' ; ?>>
        </div>

        <?php if ($enableLocation) { ?>
        <div class="joms-form__group">
            <span><?php echo JText::_('COM_COMMUNITY_PHOTOS_ALBUM_LOCATION'); ?></span>
            <input type="text" class="joms-input" name="location" value="<?php echo $this->escape($album->location); ?>"
                placeholder="<?php echo JText::_('COM_COMMUNITY_PHOTOS_ALBUM_LOCATION_DESC'); ?>">
        </div>
        <?php } ?>

        <div class="joms-form__group">
            <span><?php echo JText::_('COM_COMMUNITY_PHOTOS_ALBUM_DESC'); ?></span>
            <textarea name="description" class="joms-textarea"><?php echo $this->escape($album->description); ?></textarea>
        </div>

        <div class="joms-form__group">
            <span><?php echo JText::_('COM_COMMUNITY_PHOTOS_PRIVACY_VISIBILITY'); ?></span>
            <?php if ($type == 'group') { ?>
            <p class="joms-help"><?php echo JText::_('COM_COMMUNITY_PHOTOS_GROUP_MEDIA_PRIVACY_TIPS'); ?></p>
            <?php } elseif ($type == 'event') { ?>
            <p class="joms-help"><?php echo JText::_('COM_COMMUNITY_PHOTOS_EVENT_MEDIA_PRIVACY_TIPS'); ?></p>
            <?php } else { ?>
            <?php echo CPrivacy::getHTML('permissions', $permissions, COMMUNITY_PRIVACY_BUTTON_LARGE); ?>
            <?php } ?>
        </div>

        <?php if ($afterFormDisplay) { ?>
        <div class="joms-form__group">
            <?php echo $afterFormDisplay; ?>
        </div>
        <?php } ?>

        <div class="joms-form__group">
            <span></span>
            <input type="hidden" name="albumid" value="<?php echo $album->id; ?>" />
            <input type="hidden" name="referrer" value="<?php echo $referrer; ?>" />
            <input type="hidden" name="type" value="<?php echo $album->type; ?>" />
            <input type="hidden" name="groupid" value="<?php echo $album->groupid; ?>" />
            <input type="hidden" name="eventid" value="<?php echo $album->eventid; ?>" />
            <?php echo JHTML::_('form.token'); ?>
            <input type="button" value="<?php echo JText::_('COM_COMMUNITY_CANCEL_BUTTON'); ?>" class="joms-button--neutral joms-button--full-small" onclick="history.go(-1); return false;">
            <input type="submit" value="<?php echo JText::_($album->id ? 'COM_COMMUNITY_PHOTOS_SAVE_ALBUM_BUTTON' : 'COM_COMMUNITY_PHOTOS_CREATE_ALBUM_BUTTON'); ?>" class="joms-button--primary joms-button--full-small">
        </div>

    </form>

</div>
