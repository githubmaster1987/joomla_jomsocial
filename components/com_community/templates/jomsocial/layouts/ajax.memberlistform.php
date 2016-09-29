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

<form method="POST" action="<?php echo CRoute::_('index.php?option=com_community&view=memberlist&task=save'); ?>">
    <div class="joms-form__group">
        <span style="width:90px"><?php echo JText::_('COM_COMMUNITY_FILTER_TITLE'); ?></span>
        <div>
            <input type="text" class="joms-input" name="title">
            <p class="joms-help" style="color:red; display:none"><?php echo JText::_('COM_COMMUNITY_MEMBERLIST_TITLE_REQUIRED'); ?></p>
        </div>
    </div>
    <div class="joms-form__group">
        <span style="width:90px"><?php echo JText::_('COM_COMMUNITY_FILTER_DESCRIPTION'); ?></span>
        <div>
            <textarea class="joms-textarea" name="description"></textarea>
            <p class="joms-help" style="color:red; display:none"><?php echo JText::_('COM_COMMUNITY_MEMBERLIST_DESCRIPTION_REQUIRED'); ?></p>
        </div>
    </div>
    <div class="joms-form__group">
        <span style="width:90px"><?php echo JText::_('COM_COMMUNITY_SELECT_MENU'); ?></span>
        <div><?php echo JHTML::_('select.genericlist', $menuTypes, 'menutype', ' size="1"', 'menutype', 'title', 1); ?></div>
    </div>
    <div class="joms-form__group">
        <span style="width:90px"><?php echo JText::_('COM_COMMUNITY_ACCESS_LEVEL'); ?></span>
        <div><?php echo JHTML::_('access.assetgrouplist', 'access', $menuAccess->access); ?></div>
    </div>

    <input type="hidden" name="totalfilters" value="<?php echo count($filters); ?>" />
    <input type="hidden" name="condition" value="<?php echo $condition; ?>" />
    <input type="hidden" name="avataronly" value="<?php echo $avatarOnly; ?>" />
    <?php for ($i = 0; $i < count($filters); $i++ ) { ?>
    <input type="hidden" name="filter<?php echo $i; ?>" value="<?php echo $filters[$i]; ?>" />
    <?php } ?>

</form>
