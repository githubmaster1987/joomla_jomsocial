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
    <form class="joms-form" name="jsform-groups-discussionform" action="<?php echo CRoute::getURI(); ?>" method="post" onsubmit="return joms_discussion_onsubmit();">

        <h3 class="joms-page__title">
            <span><?php echo JText::_('COM_COMMUNITY_GROUPS_DISCUSSION_CREATE')?></span>
        </h3>

        <?php if ($beforeFormDisplay) { ?>
        <div class="joms-form__group"><?php echo $beforeFormDisplay; ?></div>
        <?php } ?>

        <div class="joms-form__group">
            <span><?php echo JText::_('COM_COMMUNITY_GROUPS_DISCUSSION_TITLE'); ?> <span class="joms-required">*</span></span>
            <input type="text" name="title" id="title" class="joms-input" value="<?php echo $discussion->title;?>" />
        </div>

        <div class="joms-form__group">
            <span><?php echo JText::_('COM_COMMUNITY_GROUPS_DISCUSSION_BODY'); ?> <span class="joms-required">*</span></span>
            <textarea name="message" id="message" class="joms-textarea" data-wysiwyg="trumbowyg" data-wysiwyg-type="discussion" data-wysiwyg-id="0"><?php echo $discussion->message; ?></textarea>
        </div>

        <?php if($params->get('groupdiscussionfilesharing') > 0) { ?>
        <div class="joms-form__group">
            <span></span>
            <label class="joms-checkbox">
                <input type="checkbox" class="joms-checkbox" value="1" name="filepermission-member"/>
                <span><?php echo JText::_('COM_COMMUNITY_GROUPS_ALLOW_MEMBER_UPLOAD_FILE')?></span>
            </label>
        </div>
        <?php } ?>

        <?php if ($afterFormDisplay) { ?>
        <div class="joms-form__group"><?php echo $afterFormDisplay; ?></div>
        <?php } ?>

        <script>
        function joms_discussion_onsubmit() {
            return false;
        }

        window.joms_queue || (window.joms_queue = []);
        window.joms_queue.push(function( $ ) {
            joms_discussion_onsubmit = function() {
                var $message = $('#message'),
                    value = $message.val();

                value = value.replace( /<\/p><p>/ig, '</div><div><br></div><div>' );
                value = value.replace( /<(\/?)p>/ig, '<$1div>' );
                $message.val( value );
                return true;
            };
        });
        </script>

        <div class="joms-form__group">
            <span></span>
            <div>
                <input type="hidden" value="<?php echo $group->id; ?>" name="groupid" />
                <input type="button" class="joms-button--neutral joms-button--full-small" name="cancel" value="<?php echo JText::_('COM_COMMUNITY_CANCEL_BUTTON'); ?>" onclick="javascript:history.go(-1);return false;" />
                <input type="submit" class="joms-button--primary joms-button--full-small" value="<?php echo JText::_('COM_COMMUNITY_GROUPS_ADD_DISCUSSION_BUTTON');?>" />
                <?php echo JHTML::_( 'form.token' ); ?>
            </div>
        </div>

    </form>
</div>
