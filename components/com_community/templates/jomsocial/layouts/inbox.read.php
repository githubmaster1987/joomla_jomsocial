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

if ( empty($messages) ) {
    echo $htmlContent;
} else {

    $messagesCount = count( $messages );

    $params = JComponentHelper::getParams('com_media');
    $fileExtensions = $params->get('upload_extensions');

    $config = CFactory::getConfig();
    $enableFileSharing = (int) $config->get('message_file_sharing');
    $maxFileSize = (int) $config->get('message_file_maxsize');

    $showSidebar = false;
    if (isset($files) && count($files) >= 1) {
        $showSidebar = true;
    }

?>

<div class="joms-page joms-page--inbox">
    <h3 class="joms-page__title"><?php echo htmlspecialchars_decode($parentData->subject); ?></h3>
    <?php echo $submenu; ?>
    <div>
        <?php echo $messageHeading; ?>
        <span id="cInbox-Recipients" style="display:none"><?php

            // Generate recipient names.

            $i = 0;
            $profile = 'index.php?option=com_community&view=profile&userid=';

            // Add owner name in the header.
            if ($parentData->from != $my->id) {
                $user = CFactory::getUser($parentData->from);
                $userLink = CRoute::_($profile . $parentData->from);
                echo '<a href="' . $userLink . '">' . $user->getDisplayName() . '</a>';
                $i++;
            }

            // Generate recipient name in the header.
            foreach ($recipient as $row) {
                if ($my->id != $row->to) {
                    if ($i >= 1) echo ', ';
                    $user = CFactory::getUser($row->to);
                    $userLink = CRoute::_($profile . $row->to);
                    echo '<a href="' . $userLink . '">' . $user->getDisplayName() . '</a>';
                    $i++;
                }
            }

        ?></span>
    </div>

<?php if ($showSidebar) { ?>

</div>

<div class="joms-gap"></div>

<div class="joms-sidebar">
    <div class="joms-module__wrapper">
        <div class="joms-tab__content">
            <h4 class="joms-text--title"><?php echo JText::_('COM_COMMUNITY_FILES_AVAILABLE')?></h4>
            <ul class="joms-list--files">
                <?php foreach ($files as $file) { ?>
                <?php

                    $filename = $file->name;
                    $filepath = $file->filepath;
                    $fileext  = strrpos( $filepath, '.' );
                    if ( $fileext !== false ) {
                        $filename .= substr( $filepath, $fileext );
                    }

                ?>
                <li class="joms-js--file-<?php echo $file->id; ?>">
                    <p>
                        <a href="<?php echo $file->filepath; ?>" target="_blank" title="<?php echo $file->name; ?>">
                            <?php echo $filename; ?>
                        </a><br>
                        <small class="joms-text--light">
                            <?php echo round($file->filesize/1048576,2) . 'MB'; ?>
                            <a href="javascript:" class="joms-button--link" onclick="joms.api.fileRemove('message', '<?php echo $file->id; ?>');">
                            <?php echo JText::_('COM_COMMUNITY_DELETE'); ?>
                            </a>
                        </small>
                    </p>
                </li>
                <?php } ?>
            </ul>
        </div>
    </div>
</div>

<div class="joms-main">

<?php } ?>

    <div class="joms-page joms-page--inbox" style="<?php echo $showSidebar ? '' : 'padding-left:0;padding-right:0' ?>">
        <?php if ( $messagesCount > $limit ) { ?>
        <div class="joms-js--inbox-more">
            <a href="javascript:" class="joms-button--neutral joms-button--full" data-count="<?php echo $messagesCount - $limit; ?>" onclick="joms_show_more_messages( this );">
                <?php echo JText::_('COM_COMMUNITY_SHOW_PREVIOUS_COMMENTS'); ?>
            </a>
            <script>
                function joms_show_more_messages( el ) {
                    var langSingle = '<?php echo addslashes( JText::_("COM_COMMUNITY_SHOW_PREVIOUS_COMMENTS") ); ?>',
                        langMultiple = '<?php echo addslashes( JText::_("COM_COMMUNITY_SHOW_PREVIOUS_COMMENTS") ); ?>',
                        limit = +'<?php echo $limit ?>',
                        $el = joms.jQuery( el ),
                        count = +$el.data('count'),
                        showCount;

                    if ( count < 1 ) {
                        return;
                    }

                    if ( count <= limit ) {
                        joms.jQuery('.joms-js--inbox-more').hide();
                    }

                    showCount = Math.min( count, limit );
                    $el.data( 'count', count - showCount );
                    $el.html(
                        count - showCount > 1 ?
                        langMultiple.replace('___val___', count - showCount) :
                        langSingle.replace('___val___', count - showCount)
                    );

                    // Show messages.
                    $el = joms.jQuery('.joms-js--inbox-item-hidden');
                    count = $el.length;

                    $el.each(function( i ) {
                        if ( i > count - showCount - 1 ) {
                            joms.jQuery( this ).removeClass('joms-js--inbox-item-hidden').fadeIn();
                        }
                    });
                }
            </script>

        </div> &nbsp;
        <?php } ?>

        <div class="joms-list--message joms-comment--inbox joms-js--inbox"><?php echo $htmlContent; ?></div>
        <div class="joms-gap"></div>
        <div class="joms-relative joms-js--inbox-reply joms-js--pm-message">
            <?php
            // if the conversation is just with 1 other person and at least one blocked another, disable textarea
            $readonly = 0;


                if(count($recipient) == 1) {

                    $getBlockStatus		= new blockUser();
                    if($getBlockStatus->isUserBlocked($recipient[0]->msg_from, 'inbox') && !COwnerHelper::isCommunityAdmin()) {
                        $readonly = 1;
                    }

                    if($getBlockStatus->isUserBlocked($recipient[0]->to, 'inbox') && !COwnerHelper::isCommunityAdmin()) {
                        $readonly = 1;
                    }
            }

            if(!$readonly) {
            ?>
            <div class="joms-textarea__wrapper">
                <div class="joms-textarea joms-textarea__beautifier"></div>
                <textarea class="joms-textarea" value=""
                          placeholder="<?php echo JText::_('COM_COMMUNITY_REPLY_MESSAGE'); ?>"
                          data-id="<?php echo $parentData->id; ?>"
                          data-func="inbox,ajaxAddReply"
                          data-tag-url="<?php echo CRoute::_('index.php?option=com_community&view=friends&task=ajaxAutocomplete') ?>"
                          data-noentersend="1"
                          style="min-height:80px;"></textarea>
                <div class="joms-textarea__loading"><img src="<?php echo JURI::root(true); ?>/components/com_community/assets/ajax-loader.gif" alt="loader" ></div>
                <div class="joms-textarea joms-textarea__attachment">
                    <button onclick="joms.view.comment.removeAttachment(this);">×</button>
                    <div class="joms-textarea__attachment--loading"><img src="<?php echo JURI::root(true); ?>/components/com_community/assets/ajax-loader.gif" alt="loader" ></div>
                    <div class="joms-textarea__attachment--thumbnail"><img src="#" alt="attachment"></div>
                </div>
            </div>

            <svg viewBox="0 0 16 16" class="joms-icon joms-icon--add" onclick="joms.view.comment.addAttachment(this, 'image');">
                <use xlink:href="<?php echo CRoute::getURI(); ?>#joms-icon-camera"></use>
            </svg>

            <?php if ($enableFileSharing) { ?>
            <svg viewBox="0 0 16 16" class="joms-icon joms-icon--add" onclick="joms.view.comment.addAttachment(this, 'file', { type: 'message', id: '<?php echo $parentData->id; ?>', max_file_size: '<?php echo $maxFileSize; ?>', exts: '<?php echo $fileExtensions ?>' });" style="right:33px">
                <use xlink:href="<?php echo CRoute::getURI(); ?>#joms-icon-file-zip"></use>
            </svg>
            <?php } ?>

            <div style="text-align:right; margin-top:10px;">
                <input type="button" class="joms-button--neutral joms-button--small joms-js--btn-send" value="<?php echo JText::_('COM_COMMUNITY_SEND'); ?>">
            </div>

            <?php } else {
            echo JText::_('COM_COMMUNITY_MESSAGE_DISABLED');
            }?>
        </div>
    </div>
</div>

<script type="text/javascript">
    (function( w ) {
        w.joms_queue || (w.joms_queue = []);
        w.joms_queue.push(function() {

            // Show all participants.
            joms.jQuery('a.cInbox-ShowMore').click(function(e) {
                e.preventDefault();
                joms.jQuery('#cInbox-Recipients').show();
                joms.jQuery(this).remove();
            });

            // Initialize tagging.
            setTimeout(function() {
                joms.jQuery('.joms-textarea').jomsTagging();
            }, 1000 );

        });
    })( window );
</script>

<?php } ?>
