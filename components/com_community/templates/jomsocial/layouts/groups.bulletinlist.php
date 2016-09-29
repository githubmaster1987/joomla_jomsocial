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
    <h3 class="joms-page__title"><?php echo JText::_('COM_COMMUNITY_GROUP_ANNOUNCEMENTS'); ?></h3>
    <?php if($isAdmin):?>
    <button class="joms-button--add-on-page joms-button--primary joms-button--small" onclick="window.location='<?php echo CRoute::_(
        'index.php?option=com_community&view=groups&groupid=' . $groupId . '&task=addnews'); ?>';"><?php echo JText::_('COM_COMMUNITY_CREATE_GROUP_ANNOUNCEMENT') ?></button>
    <?php endif?>
    <div>

        <?php
            if ($bulletins) {
                for ($i = 0; $i < count($bulletins); $i++) {
                    $row =& $bulletins[$i];
                    ?>
                    <div class="joms-stream__container joms-stream--discussion">
                        <div class="joms-stream__header ">
                            <div class="joms-avatar--stream <?php echo CUserHelper::onlineIndicator($row->creator); ?>">
                                <a href="<?php echo CUrlHelper::userLink($row->creator->id); ?>">
                                    <img data-author="<?php echo $row->creator->id; ?>"
                                         src="<?php echo $row->creator->getThumbAvatar(); ?>"
                                         alt="<?php echo $row->creator->getDisplayName(); ?>"/>
                                </a>
                            </div>
                            <div class="joms-stream__meta">
                                <a class="joms-stream__user"
                                   href="<?php echo CRoute::_('index.php?option=com_community&view=groups&task=viewbulletin&groupid=' . $groupId . '&bulletinid=' . $row->id); ?>">
                                    <?php echo $row->title; ?>
                                </a>
                                <span class="joms-stream__time">
                                    <small>
                                        <?php echo JHTML::_('date', $row->date, JText::_('DATE_FORMAT_LC2')); ?>
                                        <?php echo JText::sprintf('COM_COMMUNITY_BULLETIN_CREATED_BY',
                                        $row->creator->getDisplayName(),
                                        CRoute::_('index.php?option=com_community&view=profile&userid=' . $row->creator->id)); ?>
                                    </small>
                                </span>
                            </div>
                        </div>
                    </div>
                <?php
                } //end for
            } // end if
            else {
                ?>
                <p>
                    <?php echo JText::_('COM_COMMUNITY_GROUPS_BULLETIN_NOITEM'); ?>
                </p>
            <?php
            }
        ?>
    </div>
</div>
