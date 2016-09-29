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

$config	= CFactory::getConfig();
?>


<?php if( $groups ) { ?>

<div class="joms-gap"></div>

<ul class="joms-list--card">
    <?php

    $my = CFactory::getUser();
    $groupModel = CFactory::getModel('groups');

    for ( $i = 0; $i < count( $groups ); $i++ ) {
        $group =& $groups[$i];

        $isMine = $my->id == $group->ownerid;
        $isAdmin = $groupModel->isAdmin($my->id, $group->id);
        $isMember = $groupModel->isMember($my->id, $group->id);
        $isBanned = $group->isBanned($my->id);
        $creator = CFactory::getUser($group->ownerid);

        // Check if "Feature this" button should be added or not.
        $addFeaturedButton = false;
        $isFeatured = false;
        if ( $isCommunityAdmin && $showFeatured ) {
            $addFeaturedButton = true;
            if ( in_array($group->id, $featuredList) ) {
                $isFeatured = true;
            }
        }

        //all the information needed to fill up the summary
        $params = $group->getParams();

        $eventsModel = CFactory::getModel('Events');
        $totalEvents = $eventsModel->getTotalGroupEvents($group->id);
        $showEvents = ($config->get('group_events') && $config->get('enableevents') && $params->get('eventpermission',
            1) >= 1);

        $videoModel = CFactory::getModel('videos');
        $showVideo = ($params->get('videopermission') != -1) && $config->get('enablevideos') && $config->get('groupvideos');
        if($showVideo) {
            $videoModel->getGroupVideos($group->id, '',
                $params->get('grouprecentvideos', GROUP_VIDEO_RECENT_LIMIT));
            $totalVideos = $videoModel->total ? $videoModel->total : 0;
        }

        $showPhoto = ($params->get('photopermission') != -1) && $config->get('enablephotos') && $config->get('groupphotos');
        $photosModel = CFactory::getModel('photos');
        $albums = $photosModel->getGroupAlbums($group->id, false, false);
        $totalPhotos = 0;
        foreach ($albums as $album) {
            $albumParams = new CParameter($album->params);
            $totalPhotos = $totalPhotos + $albumParams->get('count');
        }

        $bulletinModel = CFactory::getModel('bulletins');
        $bulletins = $bulletinModel->getBulletins($group->id);
        $totalBulletin = $bulletinModel->total;



        // Check if "Invite friends" and "Settings" buttons should be added or not.
        $canInvite = false;
        $canEdit = false;

        if (($isMember && !$isBanned) || $isCommunityAdmin) {
            $canInvite = true;
            if ($isMine || $isAdmin || $isCommunityAdmin) {
                $canEdit = true;
            }
        }

    ?>

    <li class="joms-list__item <?php echo $group->approvals == COMMUNITY_PRIVATE_GROUP ? 'group-private' : 'group-public' ?>">
        <div class="joms-list__cover">
            <a href="<?php echo $group->getLink(); ?>">
                <?php  if (in_array($group->id, $featuredList)) { ?>
                <div class="joms-ribbon__wrapper">
                    <span class="joms-ribbon"><?php echo JText::_('COM_COMMUNITY_FEATURED'); ?></span>
                </div>
                <?php } ?>

                <div class="joms-list__cover-image" data-image="<?php echo $group->getCover(); ?>" style="background-image: url(<?php echo $group->getCover(); ?>);"></div>
            </a>
            <?php if ($addFeaturedButton || $canInvite || $canEdit) { ?>
            <div class="joms-focus__button--options--desktop">
                <a class="joms-button--options" data-ui-object="joms-dropdown-button" href="javascript:">
                    <svg class="joms-icon" viewBox="0 0 16 16">
                        <use xlink:href="<?php echo CRoute::getURI(); ?>#joms-icon-cog"></use>
                    </svg>
                </a>
                <ul class="joms-dropdown">
                    <?php if ($addFeaturedButton) { ?>
                    <?php if ($isFeatured) { ?>
                    <li><a href="javascript:" onclick="joms.api.groupRemoveFeatured('<?php echo $group->id; ?>');"><?php echo JText::_('COM_COMMUNITY_REMOVE_FEATURED'); ?></a></li>
                    <?php } else { ?>
                    <li><a href="javascript:" onclick="joms.api.groupAddFeatured('<?php echo $group->id; ?>');"><?php echo JText::_('COM_COMMUNITY_GROUP_FEATURE'); ?></a></li>
                    <?php } ?>
                    <?php } ?>
                    <?php if ($canInvite) { ?>
                    <li><a href="javascript:" onclick="joms.api.groupInvite('<?php echo $group->id; ?>');"><?php echo JText::_('COM_COMMUNITY_INVITE_FRIENDS'); ?></a></li>
                    <?php } ?>
                    <?php if ($canEdit) { ?>
                    <li><a href="<?php echo CRoute::_('index.php?option=com_community&view=groups&task=edit&groupid=' . $group->id); ?>"><?php echo JText::_('COM_COMMUNITY_SETTINGS'); ?></a></li>
                    <?php } ?>
                </ul>
            </div>
            <?php } ?>
        </div>

        <div class="joms-list__content">
            <h4 class="joms-list__title">
                <a href="<?php echo $group->getLink(); ?>">
                    <?php echo $this->escape($group->name); ?>
                </a>
            </h4>
            <ul class="joms-list--table">
                <?php if(($group->approvals == COMMUNITY_PRIVATE_GROUP && $isMember) || $group->approvals == COMMUNITY_PUBLIC_GROUP){ ?>
                <li>
                    <svg class="joms-icon" viewBox="0 0 16 16">
                        <use xlink:href="<?php echo CRoute::getURI(); ?>#joms-icon-users"></use>
                    </svg>
                    <a href="<?php echo CRoute::_('index.php?option=com_community&view=groups&task=viewmembers&groupid='.$group->id) ?>">
                    <?php echo JText::sprintf((CStringHelper::isPlural($group->membercount)) ? 'COM_COMMUNITY_GROUPS_MEMBER_COUNT_MANY':'COM_COMMUNITY_GROUPS_MEMBER_COUNT', $group->membercount);?>
                    </a>
                </li>
                <?php if($config->get('creatediscussion') ){?>
                <li>
                    <svg class="joms-icon" viewBox="0 0 18 18">
                        <use xlink:href="<?php echo CRoute::getURI(); ?>#joms-icon-bubbles"></use>
                    </svg>
                    <a href="<?php echo CRoute::_('index.php?option=com_community&view=groups&task=viewdiscussions&groupid='.$group->id) ?>">
                    <?php echo JText::sprintf((CStringHelper::isPlural($group->discusscount)) ? 'COM_COMMUNITY_GROUPS_DISCUSSION_COUNT_MANY' :'COM_COMMUNITY_GROUPS_DISCUSSION_COUNT', $group->discusscount);?>
                    </a>
                </li>
                <?php } ?>
                <?php if($showVideo){ ?>
                <li>
                    <svg class="joms-icon" viewBox="0 0 16 16">
                        <use xlink:href="<?php echo CRoute::getURI(); ?>#joms-icon-film"></use>
                    </svg>
                    <a href="<?php echo CRoute::_('index.php?option=com_community&view=videos&task=display&groupid=' . $group->id); ?>">
                    <?php echo ($totalVideos == 1)
                        ? $totalVideos.' '.JText::_('COM_COMMUNITY_VIDEOS_COUNT')
                        : $totalVideos.' '.JText::_('COM_COMMUNITY_VIDEOS_COUNT_MANY'); ?>
                    </a>
                </li>
                <?php } ?>
                <?php if($config->get('createannouncement')){ ?>
                <li>
                    <svg class="joms-icon" viewBox="0 0 16 16">
                        <use xlink:href="<?php echo CRoute::getURI(); ?>#joms-icon-bullhorn"></use>
                    </svg>
                    <a href="<?php echo CRoute::_('index.php?option=com_community&view=groups&task=viewbulletins&groupid=' . $group->id); ?>">
                        <?php echo ($totalBulletin == 1)
                            ? $totalBulletin.' '.JText::_('COM_COMMUNITY_GROUPS_ANNOUNCEMENT_COUNT')
                            : $totalBulletin.' '.JText::_('COM_COMMUNITY_GROUPS_ANNOUNCEMENT_COUNT_MANY'); ?>
                    </a>
                </li>
                <?php } ?>
                <?php if($showPhoto){ ?>
                <li>
                    <svg class="joms-icon" viewBox="0 0 16 16">
                        <use xlink:href="<?php echo CRoute::getURI(); ?>#joms-icon-image"></use>
                    </svg>
                    <a href="<?php echo CRoute::_('index.php?option=com_community&view=photos&task=display&groupid=' . $group->id); ?>">
                        <?php echo ($totalPhotos == 1) ?
                            $totalPhotos.' '.JText::_('COM_COMMUNITY_PHOTOS_COUNT_SINGULAR') :
                            $totalPhotos.' '.JText::_('COM_COMMUNITY_PHOTOS_COUNT'); ?>
                    </a>
                </li>
                <?php } ?>
                <?php if ($showEvents) { ?>
                    <li>
                        <svg class="joms-icon" viewBox="0 0 16 16">
                            <use xlink:href="<?php echo CRoute::getURI(); ?>#joms-icon-calendar"></use>
                        </svg>
                        <a href="<?php echo CRoute::_('index.php?option=com_community&view=events&task=display&groupid=' . $group->id); ?>">
                            <?php echo ($totalEvents == 1 || $totalEvents == 0)
                                ? $totalEvents.' '.JText::_('COM_COMMUNITY_EVENTS_COUNT')
                                : $totalEvents.' '.JText::_('COM_COMMUNITY_EVENTS_COUNT_MANY'); ?>
                        </a>
                    </li>
                <?php } ?>
                <?php } ?>
            </ul>
        </div>

        <?php
        if($group->approvals == COMMUNITY_PRIVATE_GROUP) { ?>
            <span class="joms-list__permission">
                <svg class="joms-icon" viewBox="0 0 16 16">
                    <use xlink:href="<?php echo CRoute::getURI(); ?>#joms-icon-lock"></use>
                </svg>
                <?php echo JText::_('COM_COMMUNITY_GROUPS_PRIVATE'); ?>
            </span>
        <?php } else { ?>
            <span class="joms-list__permission">
                <svg class="joms-icon" viewBox="0 0 16 16">
                    <use xlink:href="<?php echo CRoute::getURI(); ?>#joms-icon-earth"></use>
                </svg>
                <?php echo JText::_('COM_COMMUNITY_GROUPS_OPEN'); ?>
            </span>
        <?php } ?>

        <div class="joms-list__footer joms-padding">
            <div class="<?php echo CUserHelper::onlineIndicator($creator); ?>">
                <a class="joms-avatar" href="<?php echo CUrlHelper::userLink($creator->id);?>"><img src="<?php echo $creator->getAvatar();?>" alt="avatar" data-author="<?php echo $creator->id; ?>" ></a>
            </div>
            <div class="joms-block">
                <?php echo JText::_('COM_COMMUNITY_GROUPS_CREATED_BY'); ?> <a href="<?php echo CUrlHelper::userLink($creator->id);?>"><?php echo $creator->getDisplayName(); ?></a>
            </div>
        </div>
    </li>

    <?php } ?>
</ul>

<script>
// window.joms_queue || (window.joms_queue = []);
// window.joms_queue.push(function( $ ) {
//     $('.joms-list__cover-image').each(function( index, el ) {
//         el = $( el );
//         el.data('image') && el.backstretch( el.data('image') );
//     });
// });
</script>

<?php } else { ?>
    <div class="cEmpty cAlert"><?php echo JText::_('COM_COMMUNITY_GROUPS_NOITEM'); ?></div>
<?php } ?>

<?php if (isset($pagination) && $pagination->getPagesLinks() && ($pagination->pagesTotal > 1 || $pagination->total > 1) ) { ?>
    <div class="joms-pagination">
        <?php echo $pagination->getPagesLinks(); ?>
    </div>
<?php } ?>
