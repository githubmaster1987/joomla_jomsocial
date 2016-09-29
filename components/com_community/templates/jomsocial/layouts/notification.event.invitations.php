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

<?php foreach ($rows as $row) { ?>
<li class="joms-js__event-invite-<?php echo $row->eventid; ?>">
    <div class="joms-popover__content joms-js--invitation-notice-event-<?php echo $row->eventid; ?>">
        <?php
            if($row->isGroupEvent){
                echo JText::sprintf(
                    'COM_COMMUNITY_EVENTS_GROUP_INVITED_NOTIFICATION',
                    '<a href="' . CUrlHelper::userLink($row->invitor->id) . '">' . $row->invitor->getDisplayName() . '</a>',
                    '<a href="' . $row->url . '">' . $row->title . '</a>',
                    '<a href="' . $row->grouplink . '">' . $row->groupname . '</a>');
            }else{
                echo JText::sprintf(
                    'COM_COMMUNITY_EVENTS_INVITED_NOTIFICATION',
                    '<a href="' . CUrlHelper::userLink($row->invitor->id) . '">' . $row->invitor->getDisplayName() . '</a>',
                    '<a href="' . $row->url . '">' . $row->title . '</a>');
            }

        ?>
    </div>
    <div class="joms-popover__actions joms-js--invitation-buttons-event-<?php echo $row->eventid; ?>" style="white-space:nowrap">
        <button class="joms-button__reject" onclick="joms.api.invitationReject('event', '<?php echo $row->eventid; ?>');"><?php echo JText::_('COM_COMMUNITY_EVENTS_REJECT'); ?></button>
        <button class="joms-button__approve" onclick="joms.api.invitationAccept('event', '<?php echo $row->eventid; ?>');"><?php echo JText::_('COM_COMMUNITY_EVENTS_ACCEPT'); ?></button>
    </div>
</li>
<?php } ?>
