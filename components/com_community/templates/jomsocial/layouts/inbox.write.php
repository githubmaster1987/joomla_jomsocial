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

$showForm = 1;

$params = JComponentHelper::getParams('com_media');
$fileExtensions = $params->get('upload_extensions');

$config = CFactory::getConfig();
$enableFileSharing = (int) $config->get('message_file_sharing');
$maxFileSize = (int) $config->get('message_file_maxsize');

?>

<div class="joms-page">
    <h3 class="joms-page__title"><?php echo JText::_('COM_COMMUNITY_INBOX_TITLE_WRITE'); ?></h3>

    <?php echo $submenu; ?>

    &nbsp;

    <?php if ($totalSent >= $maxSent && $maxSent != 0) {
        $showForm = 0;
        ?>

        <div class="cAlert"><?php echo JText::_('COM_COMMUNITY_PM_LIMIT_REACHED'); ?></div>

    <?php }?>

    <?php if(!$friendsCount) {
       $showForm = 0;
    ?>
    <div class="cAlert"><?php echo JText::_('COM_COMMUNITY_PM_NO_FRIENDS'); ?></div>
    <?php
    }
    ?>
    <?php if($showForm) { ?>

        <form method="POST" action="<?php echo CRoute::getURI(); ?>" onsubmit="return joms_prepare_submit( this );">

            <?php if ($beforeFormDisplay) { ?>
                <div class="joms-form__group">
                    <?php echo $beforeFormDisplay; ?>
                </div>
            <?php } ?>

            <div class="joms-form__group">
                <span><?php echo JText::_($useRealName == '1' ? 'COM_COMMUNITY_COMPOSE_TO_REALNAME' : 'COM_COMMUNITY_COMPOSE_TO_USERNAME'); ?></span>
                <input type="button" class="joms-button--neutral joms-button--full-small" value="<?php echo JText::_('COM_COMMUNITY_INBOX_ADD_RECIPIENT'); ?>"
                       onclick="joms.popup.inbox.addRecipient();">
                <div id="joms-js--compose-to" class="joms-input" type="text" style="margin-top:10px; display:none"></div>
                <div class="joms-js--error-message" style="display:none; color:red;"><?php echo JText::_('COM_COMMUNITY_INBOX_RECEIVER_MISSING'); ?></div>
            </div>

            <div class="joms-form__group">
                <span><?php echo JText::_('COM_COMMUNITY_COMPOSE_SUBJECT'); ?></span>
                <input class="joms-input" type="text" name="subject" value="<?php echo htmlspecialchars($data->subject); ?>">
                <div class="joms-js--error-message" style="display:none; color:red;"><?php echo JText::_('COM_COMMUNITY_INBOX_SUBJECT_MISSING'); ?></div>
            </div>

            <div class="joms-form__group">
                <span><?php echo JText::_('COM_COMMUNITY_COMPOSE_MESSAGE'); ?></span>
                <div class="joms-js--pm-message" style="position:relative">
                    <div class="joms-textarea__wrapper">
                        <textarea class="joms-textarea"><?php echo $data->body; ?></textarea>
                        <input type="hidden" class="joms-textarea__hidden" name="body">
                        <div class="joms-textarea joms-textarea__attachment">
                            <button onclick="joms.view.comment.removeAttachment(this);">×</button>
                            <div class="joms-textarea__attachment--loading"><img src="<?php echo JURI::root(true); ?>/components/com_community/assets/ajax-loader.gif" alt="loader"></div>
                            <div class="joms-textarea__attachment--thumbnail"><img src="#" alt="attachment"></div>
                        </div>
                    </div>
                    <svg viewBox="0 0 16 16" class="joms-icon joms-icon--add" style="position:absolute;top:10px;right:10px"
                         onclick="joms.view.comment.addAttachment(this);">
                        <use xlink:href="<?php echo CRoute::getURI(); ?>#joms-icon-camera"></use>
                    </svg>

                    <?php if ($enableFileSharing) { ?>
                    <svg viewBox="0 0 16 16" class="joms-icon joms-icon--add"
                         onclick="joms.view.comment.addAttachment(this, 'file', { type: 'message', max_file_size: '<?php echo $maxFileSize; ?>', exts: '<?php echo $fileExtensions ?>' });" style="position:absolute;top:10px;right:30px">
                        <use xlink:href="<?php echo CRoute::getURI(); ?>#joms-icon-file-zip"></use>
                    </svg>
                    <?php } ?>

                </div>
                <div class="joms-js--error-message" style="display:none; color:red;"><?php echo JText::_('COM_COMMUNITY_INBOX_MESSAGE_MISSING'); ?></div>
            </div>

            <?php if ($afterFormDisplay) { ?>
                <div class="joms-form__group">
                    <?php echo $afterFormDisplay; ?>
                </div>
            <?php } ?>

            <div class="joms-form__group">
                <span></span>
                <input type="hidden" name="action" value="doSubmit">
                <input type="submit" name="submitBtn" class="joms-button--primary joms-button--full-small" value="<?php echo JText::_('COM_COMMUNITY_INBOX_SEND_MESSAGE'); ?>">
            </div>

        </form>

    <?php } ?>

</div>

<script>
    function joms_prepare_submit( form ) {
        var att, photo, file, field, input, error;

        form = joms.jQuery( form );

        // Check receiver.
        field = form.find('#joms-js--compose-to').closest('.joms-form__group');
        input = field.find('[type=checkbox]:checked');
        if ( input.length ) {
            field.find('.joms-js--error-message').hide();
        } else {
            field.find('.joms-js--error-message').show();
            error = true;
        }

        // Check subject.
        input = form.find('[name=subject]');
        field = input.closest('.joms-form__group');
        if ( joms.jQuery.trim( input.val() ) ) {
            field.find('.joms-js--error-message').hide();
        } else {
            field.find('.joms-js--error-message').show();
            error = true;
        }

        // Check attachment.
        att = form.find('.joms-textarea__attachment');
        if ( att.is(':visible') ) {
            photo = att.find('.joms-textarea__attachment--thumbnail img');
            file = photo.siblings('b');
            if ( photo.is(':visible') ) {
                photo = photo.data('photo_id');
                file = false;
            } else if ( file.is(':visible') ) {
                photo = false;
                file = file.data('id');
            }
        }

        // Check message.
        input = form.find('[name=body]');
        field = input.closest('.joms-form__group');
        if ( !joms.jQuery.trim( input.val() ) && !photo && !file ) {
            field.find('.joms-js--error-message').show();
            error = true;
        } else {
            field.find('.joms-js--error-message').hide();
        }

        if ( error ) {
            return false;
        }

        // Prevents repeated click.
        if ( form.data('saving') )
            return false;

        form.data( 'saving', 1 );

        // Add attachment if available.
        if ( photo ) {
            form.append('<input type="hidden" name="photo" value="' + photo + '">');
        } else if ( file ) {
            form.append('<input type="hidden" name="file_id" value="' + file + '">');
        }

        // Submit.
        return true;
    }

    (function( w ) {
        w.joms_queue || (w.joms_queue = []);
        w.joms_queue.push(function() {

            // Initialize tagging.
            setTimeout(function() {
                joms.jQuery('.joms-textarea').jomsTagging();
            }, 1000 );

        });
    })( window );
</script>
