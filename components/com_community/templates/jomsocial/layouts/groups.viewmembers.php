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
    <h3 class="joms-page__title"><?php echo $title; ?></h3>
    <?php echo $submenu;?>
    <div class="joms-gap"></div>
    <?php if ($type == '1' && !( $isMine || $isAdmin || $isSuperAdmin )) { ?>
        <div>
            <?php echo JText::_('COM_COMMUNITY_PERMISSION_DENIED_WARNING'); ?>
        </div>
    <?php } else { ?>
        <?php if ($members): ?>
            <div id="notice" class="alert alert-notice" style="display:none;">
                <a class="close" data-dismiss="alert">×</a>
                <div id="notice-message"></div>
            </div>
            <ul class="joms-list--friend">
                <?php foreach ($members as $member) { ?>
                    <?php
                    /* do not display banned users but not mine || admin || superadmin */
                    if ($member->isBanned && !( $isMine || $isAdmin || $isSuperAdmin )) {
                        continue;
                    }
                    ?>
                    <li id="member_<?php echo $member->id; ?>" class="joms-list__item joms-js--member-group-<?php echo $groupid; ?>-<?php echo $member->id; ?>">
                        <?php  if (in_array($member->id, $featuredList)) { ?>
                        <div class="joms-ribbon__wrapper">
                            <span class="joms-ribbon"><?php echo JText::_('COM_COMMUNITY_FEATURED'); ?></span>
                        </div>
                        <?php } ?>
                        <div class="joms-list__avatar <?php echo CUserHelper::onlineIndicator($member); ?>">
                            <a class="joms-avatar" href="<?php echo CRoute::_('index.php?option=com_community&view=profile&userid=' . $member->id); ?>">
                                <img src="<?php echo $member->getThumbAvatar(); ?>" alt="<?php echo $member->getDisplayName(); ?>" data-author="<?php echo $member->id; ?>" />
                            </a>
                        </div>
                        <div class="joms-list__body">
                            <a href="<?php echo CRoute::_('index.php?option=com_community&view=profile&userid=' . $member->id); ?>">
                                <h4 class="joms-text--username"><?php echo $member->getDisplayName(); ?></h4>
                            </a>

                            <span class="joms-text--light joms-block">
                                <?php echo $member->friendsCount;?> <?php  echo JText::sprintf( (CStringHelper::isPlural($member->friendsCount)) ? 'COM_COMMUNITY_FRIENDS_COUNT_MANY' : 'COM_COMMUNITY_FRIENDS_COUNT', $member->friendsCount);?>
                            </span>
                            <?php if($member->isAdmin): ?>
                                <span class="joms-label is-admin"><?php echo JText::_('COM_COMMUNITY_ADMINISTRATOR') ?></span>
                            <?php endif; ?>
                            <?php if($member->isOwner): ?>
                                <span class="joms-label is-owner"><?php echo JText::_('COM_COMMUNITY_OWNER') ?></span>
                            <?php endif; ?>
                            <div class="joms-gap"></div>
                            <?php
                                // this is for pending approval member only
                                if ($type) { ?>

                            <div class="joms-js--request-notice-group-<?php echo $groupid; ?>-<?php echo $member->id; ?>"></div>
                            <div class="joms-js--request-buttons-group-<?php echo $groupid; ?>-<?php echo $member->id; ?>" style="white-space:nowrap">
                                <a href="javascript:" onclick="joms.api.groupApprove('<?php echo $groupid; ?>', '<?php echo $member->id; ?>');">
                                    <button class="joms-button--primary joms-button--smallest joms-button--full-small"><?php echo JText::_('COM_COMMUNITY_PENDING_ACTION_APPROVE'); ?></button>
                                </a>
                                <a href="javascript:" onclick="joms.api.groupRemoveMember('<?php echo $groupid; ?>', '<?php echo $member->id; ?>');">
                                    <button class="joms-button--neutral joms-button--smallest joms-button--full-small"><?php echo JText::_('COM_COMMUNITY_FRIENDS_PENDING_ACTION_REJECT'); ?></button>
                                </a>
                            </div>
                            <?php } ?>

                        </div>
                        <div class="joms-list__actions">
                            <?php echo CFriendsHelper::getUserCog($member->id,$groupid,null,true); ?>
                            <?php //echo CFriendsHelper::getUserFriendDropdown($member->id); ?>
                        </div>
                    </li>
                    <?php
                }
                ?>
            </ul>
        <?php endif; ?>
        <?php
    }
    ?>

<?php if ($pagination->getPagesLinks() && ($pagination->pagesTotal > 1 || $pagination->total > 1) ) { ?>
    <div class="joms-pagination">
        <?php echo $pagination->getPagesLinks(); ?>
    </div>
<?php } ?>

</div>
