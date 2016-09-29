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

<?php
if( !CStringHelper::isHTML($discussion->message)
	&& $config->get('htmleditor') != 'none'
	&& $config->getBool('allowhtml') )
{
	$discussion->message = CStringHelper::nl2br($discussion->message);
}

?>

<div class="joms-page">
	<form class="joms-form" name="jsform-groups-editdiscussion" action="<?php echo CRoute::getURI(); ?>" method="post" onsubmit="return joms_discussion_onsubmit();">

        <?php if ($beforeFormDisplay) { ?>
        <div class="joms-form__group"><?php echo $beforeFormDisplay; ?></div>
        <?php } ?>

        <div class="joms-form__group joms-required">
            <span><?php echo JText::_('COM_COMMUNITY_GROUPS_DISCUSSION_TITLE'); ?></span>
            <input type="text" name="title" id="title" class="joms-input" value="<?php echo $discussion->title;?>" />
        </div>

        <div class="joms-form__group joms-required">
            <span><?php echo JText::_('COM_COMMUNITY_GROUPS_DISCUSSION_BODY'); ?></span>
            <textarea name="message" id="message" class="joms-textarea" data-wysiwyg="trumbowyg" data-wysiwyg-type="discussion" data-wysiwyg-id="<?php echo $discussion->id; ?>"><?php echo $discussion->message; ?></textarea>
        </div>

		<div class="joms-form__group joms-required">
			<span><?php echo JText::_('COM_COMMUNITY_LOCK_DISCUSSION'); ?></span>
			<label for="lock-yes">
				<input type="radio" class="joms-input" name="lock" id="lock-yes" value="1"<?php echo ($discussion->lock == true ) ? ' checked="checked"' : '';?> />
				<?php echo JText::_('COM_COMMUNITY_YES');?>
			</label>
			<label for="lock-no">
				<input type="radio" class="joms-input" name="lock" id="lock-no" value="0"<?php echo ($discussion->lock == false ) ? ' checked="checked"' : '';?> />
				<?php echo JText::_('COM_COMMUNITY_NO');?>
			</label>
		</div>

		<?php if($gparams->get('groupdiscussionfilesharing') > 0) {?>
		<div class="joms-form__group joms-required">
			<span><?php echo JText::_('COM_COMMUNITY_FILES_ALLOW_MEMBERS')?></span>
			<label for="filepermission-member">
				<input type="checkbox" class="joms-input" value="1" name="filepermission-member" <?php echo ($params->get('filepermission-member') > 0) ? 'checked="checked"' : '' ?>/>
			</label>
		</div>
		<?php }?>

        <?php if ($afterFormDisplay) { ?>
        <div class="joms-form__group"><?php echo $afterFormDisplay; ?></div>
        <?php } ?>

        <div class="joms-gap"></div>

		<div class="joms-form__group joms-help">
			<span></span>
			<p><?php echo JText::_( 'COM_COMMUNITY_REGISTER_REQUIRED_FIELDS' ); ?></p>
		</div>

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
                <input type="hidden" value="<?php echo $discussion->id;?>" name="topicid" />
                <input type="button" name="cancel" value="<?php echo JText::_('COM_COMMUNITY_CANCEL_BUTTON'); ?>" onclick="javascript:history.go(-1);return false;" class="joms-button--neutral" />
                <input type="submit" class="joms-button--primary" value="<?php echo JText::_('COM_COMMUNITY_SAVE');?>" />
                <?php echo JHTML::_( 'form.token' ); ?>
            </div>
		</div>
	</form>
</div>
