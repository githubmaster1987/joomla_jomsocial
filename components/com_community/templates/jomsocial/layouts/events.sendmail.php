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
    <div class="joms-list__search">
        <div class="joms-list__search-title">
            <h3 class="joms-page__title"><?php echo JText::_('COM_COMMUNITY_SEND_EMAIL_TO_GROUP_MEMBERS'); ?></h3>
        </div>
    </div>
    <div class="joms-gap"></div>
    <form name="jsform-events-sendmail" action="<?php echo CRoute::getURI();?>" method="post" class="joms-form">
        <div class="joms-form__group">
            <span></span>
            <?php
                $topTitle = 'COM_COMMUNITY_EVENTS_GOING_EMAIL_DESCRIPTION'; // type 1 = going
                if($type == 2){ // not going
                    $topTitle = 'COM_COMMUNITY_EVENTS_NOTGOING_EMAIL_DESCRIPTION';
                }elseif($type == 3){ //maybe going
                    $topTitle = 'COM_COMMUNITY_EVENTS_MAYBE_EMAIL_DESCRIPTION';
                }
                echo JText::sprintf($topTitle, $event->getMembersCount( $type ) );
            ?>
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
            <input type="hidden" name="eventid" value="<?php echo $event->id;?>">
            <?php echo JHTML::_( 'form.token' ); ?>
        </div>
    </form>
</div>

