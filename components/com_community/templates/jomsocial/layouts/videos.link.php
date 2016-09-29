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

<form class="joms-js--form-link" method="POST" action="<?php echo CRoute::_('index.php?option=com_community&view=videos&task=link'); ?>" onsubmit="return false;">
    <div class="joms-form__group" style="margin-bottom:0">
        <span><?php echo JText::_('COM_COMMUNITY_VIDEOS_LINK_URL'); ?> <span class="joms-required">*</span></span>
        <input type="text" class="joms-input" name="videoLinkUrl" value="" data-required="true">
        <p class="joms-help" data-elem="form-warning" style="display:none; color:red">
            <?php echo JText::_('COM_COMMUNITY_VIDEOS_LINK_URL') . ' ' . JText::_('COM_COMMUNITY_ENTRY_MISSING'); ?>
        </p>
    </div>

    <div class="joms-form__group">
        <span></span>
        <p class="joms-help">
            <?php echo JText::_('COM_COMMUNITY_VIDEOS_LINK_ADDTYPE_DESC'); ?>
            <div style="margin-top: 5px">
                <span><img src="<?php echo JURI::root(true) . '/components/com_community/assets/videoicons/youtube.png' ?>" title="YouTube" alt="YouTube"/></span>
                <span><img src="<?php echo JURI::root(true) . '/components/com_community/assets/videoicons/yahoo.png' ?>" title="Yahoo Video" alt="Yahoo Video" /></span>
                <span><img src="<?php echo JURI::root(true) . '/components/com_community/assets/videoicons/myspace.png' ?>" title="MySpace Video" alt="MySpace Video" /></span>
                <span><img src="<?php echo JURI::root(true) . '/components/com_community/assets/videoicons/flickr.png' ?>" title="Flickr" alt="Flickr" /></span>
                <span><img src="<?php echo JURI::root(true) . '/components/com_community/assets/videoicons/vimeo.png' ?>" title="Vimeo" alt="Vimeo" /></span>
                <span><img src="<?php echo JURI::root(true) . '/components/com_community/assets/videoicons/dailymotion.png' ?>" title="Dailymotion" alt="Dailymotion" /></span>
                <span><img src="<?php echo JURI::root(true) . '/components/com_community/assets/videoicons/liveleak.png' ?>" title="Live Leak" alt="Live Leak" /></span>
                <span><img src="<?php echo JURI::root(true) . '/components/com_community/assets/videoicons/metacafe.png' ?>" title="Metacafe" alt="Metacafe" /></span>
            </div>
        </p>
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
        <input type="submit" value="<?php echo JText::_('COM_COMMUNITY_VIDEOS_LINK'); ?>" class="joms-button--primary joms-button--full-small">
        <input type="hidden" name="creatortype" value="<?php echo $creatorType; ?>" />
        <input type="hidden" name="groupid" value="<?php echo isset($groupid) ? $groupid : ''; ?>" />
        <input type="hidden" name="eventid" value="<?php echo isset($eventid) ? $eventid : ''; ?>" />
        <?php echo JHTML::_('form.token'); ?>
    </div>
</form>
