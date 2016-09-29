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
<script>
    (function( w ) {
        w.joms_queue || (w.joms_queue = []);
        w.joms_queue.push(function() {
            joms.jQuery('.joms-js--btn-join-group').on( 'click', function() {
                joms.jQuery( this ).val('<?php echo JText::_("COM_COMMUNITY_GROUPS_JOIN_PROCESS_NOTICE"); ?>');
            });
        });
    })( window );
</script>
<div class="joms-gap"></div>
<div id="community-groups-wrap">
    <div class="joms-alert joms-alert--info reset-gap">
        <div class="joms-alert__body">
            <div class="joms-alert__content"><?php echo JText::_('COM_COMMUNITY_GROUPS_JOIN_NOTICE'); ?></div>
            <div class="joms-alert__actions">
                <input type="button" class="joms-button--primary joms-button--small joms-js--btn-join-group" value="<?php echo JText::_('COM_COMMUNITY_GROUPS_JOIN_BUTTON'); ?>" onclick="joms.api.groupJoin('<?php echo $groupid; ?>');">
            </div>
            <div class="cNotice-Footer" id="add-reply" style="display:none">
                <a href="javascript:void(0)" ><?php echo JText::_('COM_COMMUNITY_GROUPS_JOIN_ADD_REPLY_NOTICE'); ?></a>
            </div>
        </div>
    </div>
</div>
