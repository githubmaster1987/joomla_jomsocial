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

<?php if ($displayFriends) { ?>
<div>
    <input type="text" class="joms-input" data-search="1"
        placeholder="<?php echo JText::_('COM_COMMUNITY_INVITE_TYPE_YOUR_FRIEND_NAME') ?>">
    <div>
        <div class="joms-tab__bar">
            <a class="active" href="#joms-popup-tab-all"><?php echo JText::_('COM_COMMUNITY_INVITATION_SEARCH_RESULT'); ?></a>
            <a href="#joms-popup-tab-selected"><?php echo JText::_('COM_COMMUNITY_INVITATION_SELECTED_FRIENDS'); ?></a>
        </div>
        <div class="joms-tab__content" id="joms-popup-tab-all" style="height:225px; overflow:auto"></div>
        <div class="joms-tab__content" id="joms-popup-tab-selected" style="height:225px; overflow:auto; display:none"></div>
    </div>
    <div class="clearfix" style="margin-bottom:10px">
        <button class="joms-button--neutral joms-button--small pull-left" data-btn-select="1" style="width:100%"><?php echo JText::_('COM_COMMUNITY_SELECT_ALL'); ?></button>
        <button class="joms-button--neutral joms-button--small pull-left" data-btn-load="1" style="display:none"><?php echo JText::_('COM_COMMUNITY_INVITE_LOAD_MORE'); ?></button>
    </div>
</div>
<?php } ?>
