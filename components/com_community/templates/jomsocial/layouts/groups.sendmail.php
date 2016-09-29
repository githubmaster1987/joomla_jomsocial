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

<div class="joms-page">
    <form name="jsform-groups-sendmail" action="<?php echo CRoute::getURI();?>" method="post" class="joms-form">
        <div class="joms-form__group">
            <span></span>
            <?php echo JText::sprintf('COM_COMMUNITY_GROUP_SEND_EMAIL_TO_MEMBERS_DESCRIPTION', $group->getMembersCount() );?>
        </div>

        <div class="joms-form__group">
            <span><?php echo JText::_('COM_COMMUNITY_TITLE'); ?></span>
            <input type="text" name="title" value="<?php echo $this->escape($title);?>" class="joms-input" required />
        </div>

        <div class="joms-form__group">
            <span><?php echo JText::_('COM_COMMUNITY_MESSAGE'); ?></span>
            <textarea class="joms-textarea" data-wysiwyg="trumbowyg" name="message"></textarea>
        </div>

        <div class="joms-form__group">
            <span></span>
            <input type="submit" class="joms-button--primary" value="<?php echo JText::_('COM_COMMUNITY_SEND'); ?>">
            <input type="hidden" name="groupid" value="<?php echo $group->id;?>">
            <?php echo JHTML::_( 'form.token' ); ?>
        </div>
    </form>
</div>
