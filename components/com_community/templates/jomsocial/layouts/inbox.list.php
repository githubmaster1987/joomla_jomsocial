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

$jinput = JFactory::getApplication()->input;
$task = $jinput->get('task');
$title = JText::_($task == 'sent' ? 'COM_COMMUNITY_INBOX_SENT_MESSAGES_TITLE' : 'COM_COMMUNITY_INBOX_TITLE');

?>

<div class="joms-page joms-page--mobile">
    <h3 class="joms-page__title"><?php echo $title; ?></h3>

    <a class="joms-button--add-on-page joms-button--primary joms-button--small" href="<?php echo CRoute::_('index.php?option=com_community&view=inbox&task=write'); ?>">
        <?php echo JText::_('COM_COMMUNITY_NEW_MESSAGE'); ?>
    </a>

    <?php echo $submenu; ?>

    <?php if ( empty($messages) ) { ?>

    <div class="cAlert"><?php echo JText::_('COM_COMMUNITY_INBOX_MESSAGE_EMPTY'); ?></div>

    <?php } else { ?>

    <div id="inbox-listing" class="joms-list--message joms-js--message-ct">
        <div class="joms-postbox-tab">
            <ul class="joms-list">
            <?php if ( $jinput->get('task') != 'sent' ) { ?>
                <li>
                    <a href="javascript:" onclick="joms_set_all_read();">
                        <svg class="joms-icon" viewBox="0 0 16 16">
                            <use xlink:href="<?php echo CRoute::getURI(); ?>#joms-icon-checkmark"></use>
                        </svg>
                        <?php echo JText::_('COM_COMMUNITY_INBOX_MARK_READ'); ?>
                    </a>
                </li>
                <li>
                    <a href="javascript:" onclick="joms_set_all_unread();">
                        <svg class="joms-icon" viewBox="0 0 16 16">
                            <use xlink:href="<?php echo CRoute::getURI(); ?>#joms-icon-redo"></use>
                        </svg>
                        <?php echo JText::_('COM_COMMUNITY_INBOX_MARK_UNREAD'); ?>
                    </a>
                </li>
                <li>
                    <a href="javascript:" onclick="joms_delete_marked('inbox');">
                        <svg class="joms-icon" viewBox="0 0 16 16">
                            <use xlink:href="<?php echo CRoute::getURI(); ?>#joms-icon-remove"></use>
                        </svg>
                        <?php echo JText::_('COM_COMMUNITY_INBOX_REMOVE_MESSAGE'); ?>
                    </a>
                </li>
            <?php } else { ?>
                <li>
                    <a href="javascript:" onclick="joms_delete_marked('sent');">
                        <svg class="joms-icon" viewBox="0 0 16 16">
                            <use xlink:href="<?php echo CRoute::getURI(); ?>#joms-icon-remove"></use>
                        </svg>
                        <?php echo JText::_('COM_COMMUNITY_INBOX_REMOVE_MESSAGE'); ?>
                    </a>
                </li>
            <?php } ?>
            </ul>
        </div>

        <div class="joms-list--message__checkbox">
            <input type="checkbox" class="joms-checkbox joms-js--message-checkall" name="select" id="checkall" title="<?php echo JText::_('COM_COMMUNITY_INOBOX_SELECT_ALL'); ?>" onclick="checkAll();">
        </div>

        <?php foreach ($messages as $message) { ?>
            <div class="joms-list__item joms-js--message-item joms-js--message-item-<?php echo $message->id; ?> js-mail-item<?php echo $message->isUnread ? ' unread' : ' read'; ?>" id="message-<?php echo $message->id; ?>" >
                <div class="joms-list--message__body" onclick="location.href='<?php echo CRoute::_('index.php?option=com_community&view=inbox&task=read&msgid=' . $message->parent); ?>';" style="cursor: pointer;">
                    <div class="joms-comment__header">
                        <div class="joms-avatar--comment <?php echo isset($message->to[0]) ? CUserHelper::onlineIndicator(CFactory::getUser($message->to[0])) : ''; ?>">
                            <a href="<?php echo ($task == 'sent' && (!empty($message->smallAvatar[0]))) ? CUrlHelper::userLink($message->to[0]) : CUrlHelper::userLink($message->user->id); ?>">
                                <?php if ( ($task == 'sent') && ( !empty($message->smallAvatar[0]) ) ) { ?>
                                    <img src="<?php echo $message->smallAvatar[0]; ?>" alt="avatar" data-author="<?php echo $message->to[0]; ?>" >
                                <?php } else { ?>
                                    <img src="<?php echo $message->avatar; ?>" alt="avatar" data-author="<?php echo $message->user->id; ?>" >
                                <?php } ?>
                            </a>
                        </div>

                        <div class="joms-comment__body">
                            <a href="<?php echo ($task == 'sent' && (!empty($message->smallAvatar[0]))) ? CUrlHelper::userLink($message->to[0]) : CUrlHelper::userLink($message->user->id); ?>">
                                <strong>
                                    <?php
                                        if ( ($task == 'sent') && ( !empty($message->smallAvatar[0]) ) ) {
                                            echo $message->to_name[0];
                                        } else {
                                            echo $message->from_name;
                                        }
                                    ?>
                                </strong>
                            </a>
                            <?php
                                $count = --$message->recipientCount;

                                if($count>0) {
                                    $textOther = $count > 1 ? 'COM_COMMUNITY_MSG_OTHER' : 'COM_COMMUNITY_MSG_OTHER_SINGULAR';
                                    echo ", ".sprintf(JText::_($textOther), $count);
                                }
                            ?>
                            <div class="joms-stream__time">
                                <a href="<?php echo CRoute::_('index.php?option=com_community&view=inbox&task=read&msgid=' . $message->parent); ?>"><?php echo filter_var($message->subject, FILTER_SANITIZE_STRING); ?></a>
                                <small class="joms-list--message__time">
                                    <?php
                                        $postdate = CTimeHelper::timeLapse(CTimeHelper::getDate($message->posted_on));
                                        echo $postdate;
                                    ?>
                                   <svg class="joms-icon" viewBox="0 0 16 16">
                                        <use xlink:href="<?php echo CRoute::getURI(); ?>#joms-icon-clock"></use>
                                    </svg>
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="joms-list--message__remove">
                    <a href="javascript:" onclick="joms.api.inboxRemove( '<?php echo isset($task) ? $task : 'inbox';?>', [ '<?php echo $message->id; ?>' ]);" class="joms-button--neutral joms-button--smallest" title="<?php echo JText::_('COM_COMMUNITY_INBOX_REMOVE_CONVERSATION'); ?>">
                        <svg class="joms-icon" viewBox="0 0 16 16">
                            <use xlink:href="<?php echo CRoute::getURI(); ?>#joms-icon-close"></use>
                        </svg>
                        <span><?php echo JText::_('COM_COMMUNITY_INBOX_REMOVE'); ?></span>
                    </a>
                </div>
                <div class="joms-list--message__checkbox js-mail-checkbox">
                    <span>
                        <input type="checkbox" class="joms-checkbox" name="message[]" value="<?php echo $message->id; ?>" onclick="checkSelected();">
                    </span>
                </div>
                <small class="joms-list--message__time">
                    <?php
                        $postdate = CTimeHelper::timeLapse(CTimeHelper::getDate($message->posted_on));
                        echo $postdate;
                    ?>
                   <svg class="joms-icon" viewBox="0 0 16 16">
                        <use xlink:href="<?php echo CRoute::getURI(); ?>#joms-icon-clock"></use>
                    </svg>
                </small>
            </div>
        <?php } ?>
    </div>

    <?php } ?>

    <?php if ($pagination) { ?>
    <div class="joms-pagination">
        <?php echo $pagination; ?>
    </div>
    <?php } ?>
</div>

<script>
function checkAll()
{
    var checked = document.getElementById('checkall').checked;
    joms.jQuery('#inbox-listing').find('input:checkbox').not('[name=select]').each(function() {
        this.checked = checked;
    });
}

function checkSelected()
{
    var sel;
    sel = false;
    joms.jQuery("#inbox-listing INPUT[type='checkbox']").each( function() {
        if ( !joms.jQuery(this).attr('checked') )
            joms.jQuery('#checkall').attr('checked', false);
    });
}
function markAsRead( id )
{
    joms.jQuery('#message-'+id).removeClass('unread');
    joms.jQuery('#message-'+id).addClass('read');
    joms.jQuery('#new-message-'+id).hide();
    joms.jQuery("#message-"+id+" INPUT[type='checkbox']").attr('checked', false);
    joms.jQuery('#checkall').attr('checked', false);
}
function markAsUnread( id )
{
    joms.jQuery('#message-'+id).removeClass('read');
    joms.jQuery('#message-'+id).addClass('unread');
    joms.jQuery('#new-message-'+id).show();
    joms.jQuery("#message-"+id+" INPUT[type='checkbox']").attr('checked', false);
    joms.jQuery('#checkall').attr('checked', false);
}

function joms_set_all_read() {
    var error = '<?php echo JText::_("COM_COMMUNITY_INBOX_REMOVE_CONFIRM_NO_MESSAGE_CHECKED"); ?>',
        msgIds = [];

    if ( window.joms && joms.api && joms.api.inboxRemove ) {
        joms.jQuery('#inbox-listing').find('input:checkbox').not('[name=select]').each(function() {
            this.checked && msgIds.push( this.value );
        });
        joms.api.inboxSetRead( msgIds, error );
    }
}

function joms_set_all_unread() {
    var error = '<?php echo JText::_("COM_COMMUNITY_INBOX_REMOVE_CONFIRM_NO_MESSAGE_CHECKED"); ?>',
        msgIds = [];

    if ( window.joms && joms.api && joms.api.inboxRemove ) {
        joms.jQuery('#inbox-listing').find('input:checkbox').not('[name=select]').each(function() {
            this.checked && msgIds.push( this.value );
        });
        joms.api.inboxSetUnread( msgIds, error );
    }
}

function joms_delete_marked( task ) {
    var data = [ task ],
        msgIds = [];

    if ( window.joms && joms.api && joms.api.inboxRemove ) {
        joms.jQuery('#inbox-listing').find('input:checkbox').not('[name=select]').each(function() {
            this.checked && msgIds.push( this.value );
        });
        joms.api.inboxRemove( task, msgIds );
    }
}
</script>
