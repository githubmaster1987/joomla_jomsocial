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

    $input = new JInput();
?>
<div class="joms-page">
    <h3 class="joms-page__title"><?php echo JText::_('COM_COMMUNITY_GROUPS_GROUP_DISCUSSION') ?></h3>
    <?php if($canCreate) { ?>
    <button class="joms-button--add-on-page joms-button--primary joms-button--small" onclick="window.location='<?php echo CRoute::_(
        'index.php?option=com_community&view=groups&groupid=' . $input->get('groupid') . '&task=adddiscussion'); ?>';"><?php echo JText::_('COM_COMMUNITY_CREATE_GROUP_DISCUSSION') ?></button>
    <?php } ?>
<?php
    if ($discussions) {
        foreach ($discussions as $row) {

            ?>
            <div class="joms-stream__container joms-stream--discussion">
                <div class="joms-stream__header">
                    <div class="joms-avatar--stream <?php echo CUserHelper::onlineIndicator($row->user); ?>">
                        <a href="<?php echo CUrlHelper::userLink($row->user->id); ?>">
                            <img src="<?php echo $row->user->getThumbAvatar(); ?>"
                                 alt="<?php echo $row->user->getDisplayName(); ?>"
                                 data-author="<?php echo $row->user->id; ?>" />
                        </a>
                    </div>
                    <div class="joms-stream__meta">
                        <a class="joms-stream__user"
                           href="<?php echo CRoute::_('index.php?option=com_community&view=groups&task=viewdiscussion&groupid=' . $groupId . '&topicid=' . $row->id); ?>">
                            <?php echo $row->title; ?>
                        </a>
                        <span class="joms-stream__time">
                            <small>
                                <?php echo JText::sprintf('COM_COMMUNITY_GROUPS_DISCUSSION_CREATOR',
                                    '<a href="' . CUrlHelper::userLink($row->user->id) . '">' . $row->user->getDisplayName() . '</a>'); ?>
                                <?php echo JHTML::_('date', $row->created, JText::_('DATE_FORMAT_LC')); ?>
                            </small>
                        </span>
                    </div>
                    <div class="joms-stream__replies">
                        <a class="joms-button--neutral joms-button--small" href="<?php echo CRoute::_('index.php?option=com_community&view=groups&task=viewdiscussion&groupid=' . $groupId . '&topicid=' . $row->id); ?>">
                            <?php echo JText::sprintf((CStringHelper::isPlural($row->count)) ? 'COM_COMMUNITY_TOTAL_REPLIES_MANY' : 'COM_COMMUNITY_GROUPS_DISCUSSION_REPLY_COUNT',
                                $row->count); ?>
                        </a>
                    </div>
                </div>
            </div>
            <?php
            }
            ?>
    <?php
    } else {
        ?>

            <p>
                <?php
                    echo sprintf(JText::_('COM_COMMUNITY_GROUPS_DISCUSSION_EMPTY_WARNING'), CRoute::_(
                        'index.php?option=com_community&view=groups&groupid=' . $input->get('groupid') . '&task=adddiscussion')); ?>
            </p>

    <?php
    }
?>
</div>
