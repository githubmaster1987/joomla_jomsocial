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

    <h3 class="joms-page__title"><?php echo JText::_('COM_COMMUNITY_GROUPS_BULLETIN_ADD'); ?></h3>

    <form class="js-form" name="addnews" method="post" action="<?php echo CRoute::getURI(); ?>">

        <div class="joms-form__group">
            <span><?php echo JText::_('COM_COMMUNITY_GROUPS_BULLETIN_TITLE'); ?> <span class="joms-required">*</span></span>
            <input type="text" name="title" id="title" class="joms-input" />
        </div>

        <div class="joms-form__group">
            <span><?php echo JText::_('COM_COMMUNITY_GROUPS_BULLETIN_DESC'); ?> <span class="joms-required">*</span></span>
            <textarea name="message" id="message" class="joms-textarea" data-wysiwyg="trumbowyg" data-wysiwyg-type="bulletin" data-wysiwyg-id="0"><?php echo $message; ?></textarea>
        </div>

        <?php if($params->get('groupannouncementfilesharing') > 0) { ?>
        <div class="joms-form__group">
            <span></span>
            <label for="filepermission-member" class="joms-checkbox">
                <input type="checkbox" name="filepermission-member" value="1" class="joms-checkbox" />
                <span><?php echo JText::_('COM_COMMUNITY_GROUPS_ALLOW_MEMBER_UPLOAD_FILE')?></span>
            </label>
        </div>
        <?php } ?>

        <div class="joms-form__group">
            <span></span>
            <input type="button" name="cancel" value="<?php echo JText::_('COM_COMMUNITY_CANCEL_BUTTON'); ?>" onclick="javascript:history.go(-1);return false;" class="joms-button--neutral joms-button--full-small" />
            <input type="submit" name="submit" value="<?php echo JText::_('COM_COMMUNITY_GROUPS_BULLETIN_ADD'); ?>" class="joms-button--primary joms-button--full-small" />
            <?php echo JHTML::_( 'form.token' ); ?>
        </div>

    </form>
</div>
