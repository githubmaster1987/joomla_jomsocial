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

<?php if ( empty($notifications) ) { ?>
    <div class="cEmpty cAlert" style="margin:0;"><?php echo JText::_('COM_COMMUNITY_PENDING_APPROVAL_EMPTY'); ?></div>
<?php } else { ?>

    <?php foreach ($notifications as $row) { ?>
    <li class="joms-js--frequest-<?php echo $row->connection_id; ?>">
        <div class="joms-popover__avatar">
            <div class="joms-avatar">
                <img src="<?php echo $row->user->getThumbAvatar(); ?>" alt="<?php echo $row->user->getDisplayName(); ?><" >
            </div>
        </div>
        <div class="joms-popover__content joms-js--frequest-msg-<?php echo $row->connection_id; ?>">
            <h5><a href="<?php echo CUrlHelper::userLink($row->user->id); ?>"><?php echo $row->user->getDisplayName(); ?></a></h5>
            <?php echo $row->title; ?>
        </div>
        <div class="joms-popover__actions joms-js--frequest-btn-<?php echo $row->connection_id; ?>">
            <button class="joms-button--neutral joms-button--small" onclick="joms.api.friendReject('<?php echo $row->connection_id; ?>');"><?php echo JText::_('COM_COMMUNITY_FRIENDS_PENDING_ACTION_REJECT'); ?></button>
            <button class="joms-button--primary joms-button--small" onclick="joms.api.friendApprove('<?php echo $row->connection_id; ?>');"><?php echo JText::_('COM_COMMUNITY_PENDING_ACTION_APPROVE'); ?></button>
        </div>
    </li>
    <?php } ?>

<?php } ?>

<a class="joms-button--neutral joms-button--full" href="<?php echo CRoute::_('index.php?option=com_community&view=friends&task=pending'); ?>">
    <?php echo JText::_('COM_COMMUNITY_NOTIFICATIONS_SHOW_ALL_MSG'); ?>
</a>
