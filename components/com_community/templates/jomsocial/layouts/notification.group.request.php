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
<?php if (count ($oRows) > 0 ) { ?>
<?php foreach ( $oRows as $row ) : ?>
    <li id="noti-request-group-<?php echo $row->id; ?>">
        <div class="joms-popover__avatar">
            <div class="joms-avatar">
                <img src="<?php echo $row->groupAvatar; ?>" alt="<?php echo $row->name; ?>" >
            </div>
        </div>
        <div class="joms-popover__content">
            <div id="msg-request-<?php echo $row->id; ?>">
            <?php echo JText::sprintf('COM_COMMUNITY_GROUPS_REQUESTED_NOTIFICATION' , $row->name , $row->groupName); ?>
            </div>
            <div id="error-request-<?php echo $row->id; ?>"></div>
        </div>
        <div class="joms-popover__actions" style="white-space:nowrap" id="noti-answer-group-<?php echo $row->id; ?>">
            <button class="joms-button__approve" onclick="joms.jQuery('#noti-answer-group-<?php echo $row->id; ?>').remove(); jax.call('community','notification,ajaxGroupRejectRequest', '<?php echo $row->id ?>' , '<?php echo $row->groupId; ?>');"><?php echo JText::_('COM_COMMUNITY_FRIENDS_PENDING_ACTION_REJECT'); ?></button>
            <button class="joms-button__reject" onclick="joms.jQuery('#noti-answer-group-<?php echo $row->id; ?>').remove(); jax.call('community' , 'notification,ajaxGroupJoinRequest' , '<?php echo $row->id ?>' , '<?php echo $row->groupId; ?>');"><?php echo JText::_('COM_COMMUNITY_PENDING_ACTION_APPROVE'); ?></button>
        </div>
    </li>
<?php endforeach; ?>
<?php }//end if ?>
