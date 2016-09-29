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

<form class="joms-js--form-upload" method="POST" action="<?php echo CRoute::_('index.php?option=com_community&view=videos&task=upload'); ?>" onsubmit="return false;">
    <div class="joms-form__group" style="margin-bottom:2px; position:relative;">
        <span><?php echo JText::_('COM_COMMUNITY_VIDEOS_SELECT_VIDEO_FILE'); ?> <span class="joms-required">*</span></span>
        <div type="text" class="joms-input joms-js--select-file" style="cursor:text;">&nbsp;</div>
    </div>

    <div class="joms-form__group">
        <span></span>
        <div class="joms-progressbar"><div class="joms-progressbar__progress"></div></div>
    </div>

    <div class="joms-form__group">
        <span><?php echo JText::_('COM_COMMUNITY_VIDEOS_TITLE'); ?> <span class="joms-required">*</span></span>
        <input type="text" class="joms-input" name="title" value="" data-required="true">
        <p class="joms-help" data-elem="form-warning" style="display:none; color:red">
            <?php echo JText::_('COM_COMMUNITY_VIDEOS_TITLE') . ' ' . JText::_('COM_COMMUNITY_ENTRY_MISSING'); ?>
        </p>
    </div>

    <div class="joms-form__group">
        <span><?php echo JText::_('COM_COMMUNITY_VIDEOS_DESCRIPTION'); ?></span>
        <textarea class="joms-textarea" name="description"></textarea>
    </div>

    <?php if ($enableLocation) { ?>
    <div class="joms-form__group">
        <span><?php echo JText::_('COM_COMMUNITY_VIDEOS_LOCATION'); ?></span>
        <input type="text" class="joms-input" name="location" value=""
            placeholder="<?php echo JText::_('COM_COMMUNITY_VIDEOS_LOCATION_DESCRIPTION'); ?>">
    </div>
    <?php } ?>

    <div class="joms-form__group">
        <span><?php echo JText::_('COM_COMMUNITY_VIDEOS_CATEGORY'); ?> <span class="joms-required">*</span></span>
        <div class="joms-select--wrapper"><?php echo $categories; ?></div>
        <p class="joms-help" data-elem="form-warning" style="display:none; color:red">
            <?php echo JText::_('COM_COMMUNITY_VIDEOS_CATEGORY') . ' ' . JText::_('COM_COMMUNITY_ENTRY_MISSING'); ?>
        </p>
    </div>

    <?php if ($creatorType != VIDEO_GROUP_TYPE && $creatorType != VIDEO_EVENT_TYPE) { ?>
    <div class="joms-form__group">
        <span><?php echo JText::_('COM_COMMUNITY_VIDEOS_WHO_CAN_SEE'); ?></span>
        <?php echo CPrivacy::getHTML( 'permissions', $permissions, COMMUNITY_PRIVACY_BUTTON_LARGE, array(), 'select' ); ?>
    </div>
    <?php } ?>

    <?php if ($videoUploadLimit > 0 && $videoUploaded / $videoUploadLimit >= COMMUNITY_SHOW_LIMIT) { ?>
    <div class="joms-form__group">
        <span></span>
        <p class="joms-help"><?php echo JText::sprintf('COM_COMMUNITY_VIDEOS_UPLOAD_LIMIT_STATUS', $videoUploaded, $videoUploadLimit ); ?></p>
    </div>
    <?php } ?>

    <div class="joms-form__group">
        <span></span>
        <input type="submit" value="<?php echo JText::_('COM_COMMUNITY_VIDEOS_UPLOAD'); ?>" class="joms-button--primary joms-button--full-small">
        <input type="hidden" name="creatortype" value="<?php echo $creatorType; ?>" />
        <input type="hidden" name="groupid" value="<?php echo isset($groupid) ? $groupid : ''; ?>" />
        <input type="hidden" name="eventid" value="<?php echo isset($eventid) ? $eventid : ''; ?>" />
        <?php echo JHTML::_('form.token'); ?>
    </div>
</form>
