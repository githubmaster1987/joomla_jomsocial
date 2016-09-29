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

<?php foreach ($gRows as $row) { ?>
<li class="joms-js__group-invite-<?php echo $row->groupid; ?>">
    <div class="joms-popover__avatar">
        <div class="joms-avatar">
            <img src="<?php echo $row->groupAvatar; ?>" alt="<?php echo $row->name; ?>">
        </div>
    </div>
    <div class="joms-popover__content joms-js--invitation-notice-group-<?php echo $row->groupid; ?>">
        <?php echo JText::sprintf(
            'COM_COMMUNITY_GROUPS_INVITED_NOTIFICATION',
            $row->invitor->getDisplayName(),
            '<a href="' . $row->url . '">' . $row->name . '</a>');
        ?>
    </div>
    <div class="joms-popover__actions joms-js--invitation-buttons-group-<?php echo $row->groupid; ?>" style="white-space:nowrap">
        <button class="joms-button__approve" onclick="joms.api.invitationReject('group', '<?php echo $row->groupid; ?>');"><?php echo JText::_('COM_COMMUNITY_EVENTS_REJECT'); ?></button>
        <button class="joms-button__reject" onclick="joms.api.invitationAccept('group', '<?php echo $row->groupid; ?>');"><?php echo JText::_('COM_COMMUNITY_EVENTS_ACCEPT'); ?></button>
    </div>
</li>
<?php } ?>
