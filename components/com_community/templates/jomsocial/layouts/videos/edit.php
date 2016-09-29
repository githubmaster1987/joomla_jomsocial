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

<form method="POST" action="<?php echo CRoute::_('index.php?option=com_community&view=videos&task=saveVideo'); ?>">

    <div class="joms-form__group">
        <span><?php echo JText::_('COM_COMMUNITY_VIDEOS_TITLE'); ?> <span class="joms-required">*</span></span>
        <input type="text" class="joms-input" name="title" value="<?php echo $video->title; ?>" required="">
        <p class="joms-help" data-elem="form-warning" style="display:none; color:red">
            <?php echo JText::_('COM_COMMUNITY_VIDEOS_TITLE') . ' ' . JText::_('COM_COMMUNITY_ENTRY_MISSING'); ?>
        </p>
    </div>

    <div class="joms-form__group">
        <span><?php echo JText::_('COM_COMMUNITY_VIDEOS_DESCRIPTION'); ?></span>
        <textarea class="joms-textarea" name="description"><?php echo $video->description; ?></textarea>
    </div>

    <div class="joms-form__group">
        <span><?php echo JText::_('COM_COMMUNITY_VIDEOS_CATEGORY'); ?></span>
        <?php echo $categoryHTML; ?>
        <p class="joms-help" data-elem="form-warning" style="display:none; color:red">
            <?php echo JText::_('COM_COMMUNITY_VIDEOS_CATEGORY') . ' ' . JText::_('COM_COMMUNITY_ENTRY_MISSING'); ?>
        </p>
    </div>

    <?php if ($enableLocation) { ?>
    <div class="joms-form__group">
        <span><?php echo JText::_('COM_COMMUNITY_VIDEOS_LOCATION'); ?></span>
        <input type="text" class="joms-input" name="location" value="<?php echo $video->location; ?>"
            placeholder="<?php echo JText::_('COM_COMMUNITY_VIDEOS_LOCATION_DESCRIPTION'); ?>">
    </div>
    <?php } ?>

    <?php if ($showPrivacy) { ?>
    <div class="joms-form__group">
        <span><?php echo JText::_('COM_COMMUNITY_VIDEOS_WHO_CAN_SEE'); ?></span>
        <?php echo CPrivacy::getHTML( 'permissions', $video->permissions, COMMUNITY_PRIVACY_BUTTON_LARGE, ($isStreamVideo) ? array('public'=>true) : array(), 'select' ); ?>
    </div>
    <?php } ?>

    <div class="joms-form__group">
        <span></span>
        <input type="hidden" name="id" value="<?php echo $video->id; ?>">
        <input type="hidden" name="option" value="com_community">
        <input type="hidden" name="view" value="videos">
        <input type="hidden" name="task" value="saveVideo">
        <input type="hidden" name="redirectUrl" value="<?php echo $redirectUrl; ?>">
        <?php echo JHTML::_('form.token'); ?>
    </div>

</form>
