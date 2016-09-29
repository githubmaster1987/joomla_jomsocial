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
    <h4 class="joms-page__title reset-gap"><?php echo $bulletin->title; ?></h4>
    <?php if($canCreate) { ?>
    <div class="joms-gap"></div>
    <button class="joms-button--primary joms-button--small" onclick="window.location='<?php echo CRoute::_('index.php?option=com_community&view=groups&groupid=' . $group->id . '&task=addnews'); ?>';"><?php echo JText::_('COM_COMMUNITY_CREATE_ANOTHER_GROUP_ANNOUNCEMENT') ?></button>
    <div class="joms-gap"></div>
    <?php } ?>
    <?php echo $submenu;?>
</div>

<div class="joms-gap"></div>

<div class="joms-sidebar">
    <div class="joms-module__wrapper">
        <?php echo $filesharingHTML;?>
    </div>
</div>

<div class="joms-main">
    <div class="joms-page">

        <?php if ($config->get('enablesharethis') == 1) { ?>
        <!-- share button -->
        <button class="joms-button--add-on-page joms-button--primary joms-button--small" onclick="joms.api.pageShare('<?php echo CRoute::getURI(); ?>')"><?php echo JText::_('COM_COMMUNITY_SHARE'); ?></button>
        <?php } ?>

        <div class="joms-comment--bulletin">
            <div class="joms-comment__header">
                <div class="joms-avatar--stream <?php echo CUserHelper::onlineIndicator($creator); ?>">
                    <!--cPageStory-Author-->
                    <a class="joms-avatar" href="<?php echo CUrlHelper::userLink($creator->id); ?>">
                        <img data-author="<?php echo $creator->id; ?>" src="<?php echo $creator->getThumbAvatar(); ?>" border="0" alt="avatar" />
                    </a>
                </div>
                <div class="joms-comment__meta">
                    <a href="<?php echo CUrlHelper::userLink($creator->id); ?>">
                        <?php echo JText::sprintf($creator->getDisplayName()); ?>
                    </a>
                    <span class="joms-comment__time">
                        <small>
                            <?php echo JHTML::_('date' , $bulletin->date, JText::_('DATE_FORMAT_LC2')); ?>
                        </small>
                    </span>
                </div>
            </div>
            <div class="joms-gap"></div>
            <div class="joms-js--announcement-view-<?php echo $bulletin->groupid ?>-<?php echo $bulletin->id ?>">
                <p>
                    <?php echo $bulletin->message;?>
                    <?php
                    //find out if there is any url here, if there is, run it via embedly when enabled
                    $params = new CParameter($bulletin->params);
                    if($params->get('url') && $config->get('enable_embedly')){
                        ?>
                        <a href="<?php echo $params->get('url'); ?>" class="embedly-card" data-card-controls="0" data-card-recommend="0" data-card-theme="<?php echo $config->get('enable_embedly_card_template'); ?>" data-card-align="<?php echo $config->get('enable_embedly_card_position') ?>"><?php echo JText::_('COM_COMMUNITY_EMBEDLY_LOADING');?></a>
                    <?php } ?>
                </p>
            </div>
            <div class="joms-js--announcement-edit-<?php echo $bulletin->groupid ?>-<?php echo $bulletin->id ?>" style="display:none;">
                <form method="POST" action="<?php echo CRoute::_('index.php?option=com_community&view=groups&task=editnews'); ?>">
                    <div class="joms-form__group">
                        <span><?php echo JText::_( 'COM_COMMUNITY_GROUPS_BULETIN_TITLE' ); ?></span>
                        <input type="text" name="title" class="joms-input" value="<?php echo $bulletin->title; ?>" required />
                    </div>
                    <div class="joms-form__group">
                        <span><?php echo JText::_('COM_COMMUNITY_GROUPS_BULLETIN_DESCRIPTION'); ?> <span class="joms-required">*</span></span>
                        <textarea name="message" class="joms-textarea" data-wysiwyg="trumbowyg" data-wysiwyg-type="bulletin" data-wysiwyg-id="<?php echo $bulletin->id ?>"><?php echo $editorMessage; ?></textarea>
                    </div>

                    <?php if ($gparams->get('groupannouncementfilesharing') > 0) { ?>
                    <div class="joms-form__group">
                        <span></span>
                        <label>
                            <input type="checkbox" name="filepermission-member" class="joms-checkbox" value="1" <?php echo $params->get('filepermission-member') > 0 ? 'checked="checked"' : '' ?>>
                            <span title="<?php echo JText::_('COM_COMMUNITY_FILES_ALLOW_MEMBERS'); ?>"><?php echo JText::_('COM_COMMUNITY_FILES_ALLOW_MEMBERS'); ?></span>
                        </label>
                    </div>
                    <?php } ?>

                    <div class="joms-form__group">
                        <span></span>
                        <input type="hidden" value="<?php echo $bulletin->groupid; ?>" name="groupid">
                        <input type="hidden" value="<?php echo $bulletin->id; ?>" name="bulletinid">
                        <input type="button" class="joms-button--neutral joms-button--full-small" name="cancel" value="<?php echo JText::_('COM_COMMUNITY_CANCEL_BUTTON'); ?>" onclick="joms.api.announcementEditCancel('<?php echo $bulletin->groupid ?>', '<?php echo $bulletin->id ?>');" />
                        <input type="submit" class="joms-button--primary joms-button--full-small" value="<?php echo JText::_('COM_COMMUNITY_SAVE_BUTTON');?>">
                        <?php echo JHTML::_( 'form.token' ); ?>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>


