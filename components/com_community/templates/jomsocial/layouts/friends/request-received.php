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
            <h3 class="joms-page__title"><?php echo JText::_('COM_COMMUNITY_FRIENDS_REQUEST_PENDING_APPROVAL'); ?></h3>
        </div>

        <div class="joms-list__utilities">
            <form method="post" class="joms-inline--desktop" action="<?php echo CRoute::_('index.php?option=com_community&view=friends') ?>">
                <span>
                    <input type="text" name="q" class="joms-input--search" value="<?php echo (isset($searchQuery)) ? $searchQuery : ''; ?>"
                       placeholder="<?php echo JText::_('COM_COMMUNITY_SEARCH_FRIENDS'); ?>">
                </span>
                <span>
                    <button class="joms-button--neutral"><?php echo JText::_('COM_COMMUNITY_SEARCH_GO'); ?></button>
                </span>
                <input type="hidden" name="search" value="friends">
            </form>
            <button onclick="window.location='<?php echo CRoute::_('index.php?option=com_community&view=friends&task=invite'); ?>';" class="joms-button--add">
                <span><?php echo JText::_('COM_COMMUNITY_INVITE_FRIENDS'); ?></span>
                <svg class="joms-icon" viewBox="0 0 16 16">
                    <use xlink:href="<?php echo CRoute::getURI(); ?>#joms-icon-plus"></use>
                </svg>
            </button>
        </div>
    </div>

    <?php echo $submenu;?>

    <div class="joms-gap"></div>

    <div class="joms-tab__content">

    <?php if ( $rows ) { ?>

        <ul class="joms-list--friend">
            <?php foreach ( $rows as $row ) { ?>
            <?php $connectionId = CFriendsHelper::isWaitingApproval( $row->user->id, $my->id ); ?>
            <li class="joms-list__item joms-js--frequest-<?php echo $connectionId; ?>">
                <?php if ( in_array( $row->user->id, $featuredList ) ) { ?>
                <div class="joms-ribbon__wrapper">
                    <span class="joms-ribbon"><?php echo JText::_('COM_COMMUNITY_FEATURED'); ?></span>
                </div>
                <?php } ?>
                <div class="joms-list__avatar <?php echo CUserHelper::onlineIndicator($row->user); ?>">
                    <a href="<?php echo $row->user->profileLink; ?>" class="joms-avatar">
                        <img data-author="<?php echo $row->user->id; ?>" src="<?php echo $row->user->getThumbAvatar(); ?>" alt="<?php echo $row->user->getDisplayName(); ?>" />
                    </a>
                </div>
                <div class="joms-list__body joms-js--frequest-msg-<?php echo $connectionId; ?>">
                    <a href="<?php echo $row->user->profileLink; ?>" ><h4 class="joms-text--username"><?php echo $row->user->getDisplayName(); ?></h4></a>
                    <span class="joms-text--title"><?php echo JText::sprintf('COM_COMMUNITY_TOTAL_MUTUAL_FRIENDS',
                    CFriendsHelper::getTotalMutualFriends($row->user->id)); ?></span>
                </div>
                <div class="joms-list__actions joms-js--frequest-btn-<?php echo $connectionId; ?>">
                    <?php echo CFriendsHelper::getUserCog($row->user->id,null,null,true); ?>
                    <?php $connectionId = CFriendsHelper::isWaitingApproval($row->user->id,$my->id) ?>
                    <div class="joms-list__button--dropdown">
                        <button onclick="joms.api.friendReject('<?php echo $connectionId;?>');" class="joms-button--neutral joms-button--full-small joms-button--smallest"><?php echo JText::_('COM_COMMUNITY_FRIENDS_PENDING_ACTION_REJECT'); ?></button>
                        <button onclick="joms.api.friendApprove('<?php echo $connectionId;?>');" class="joms-button--primary joms-button--full-small joms-button--smallest"><?php echo JText::_('COM_COMMUNITY_PENDING_ACTION_APPROVE'); ?></button>
                    </div>
                </div>
            </li>
            <?php } ?>
        </ul>
    <?php } else { ?>
        <div class="cEmpty cAlert"><?php echo JText::_('COM_COMMUNITY_PENDING_REQUEST_EMPTY'); ?></div>
    <?php } ?>
    </div>
    <?php if ( !empty($pagination) && ($pagination->pagesTotal > 1 || $pagination->total > 1) ) { ?>
    <div class="joms-pagination">
        <?php echo $pagination->getPagesLinks(); ?>
    </div>
    <?php } ?>
</div>





