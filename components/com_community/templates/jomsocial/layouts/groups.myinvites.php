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

$config = CFactory::getConfig();
?>

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
        if ($isCommunityAdmin && $showFeatured) {
            $addFeaturedButton = true;
            if (in_array($group->id, $featuredList)) {
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
        if ($showVideo) {
            $videoModel->getGroupVideos($group->id, '',
                $params->get('grouprecentvideos', GROUP_VIDEO_RECENT_LIMIT));
            $totalVideos = $videoModel->total ? $videoModel->total : 0;
        }

        $showPhoto = ($params->get('photopermission') != -1) && $config->get('enablephotos') && $config->get('groupphotos');
        $photosModel = CFactory::getModel('photos');
        $albums = $photosModel->getGroupAlbums($group->id, true, false,
            $params->get('grouprecentphotos', GROUP_PHOTO_RECENT_LIMIT));
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
    }
?>

<div class="joms-page">
    <div class="joms-list__search">
        <div class="joms-list__search-title">
            <h3 class="joms-page__title"><?php echo JText::_('COM_COMMUNITY_GROUPS_MY_INVITES'); ?></h3>
        </div>

        <div class="joms-list__utilities">
            <form method="GET" class="joms-inline--desktop"
                  action="<?php echo CRoute::_('index.php?option=com_community&view=groups&task=search'); ?>">
                <span>
                    <input type="text" class="joms-input--search" name="search"
                       placeholder="<?php echo JText::_('COM_COMMUNITY_SEARCH_GROUP_PLACEHOLDER'); ?>">
                </span>
                <?php echo JHTML::_('form.token') ?>
                <span>
                    <button class="joms-button--neutral"><?php echo JText::_('COM_COMMUNITY_SEARCH_GO'); ?></button>
                </span>
                <input type="hidden" name="option" value="com_community"/>
                <input type="hidden" name="view" value="groups"/>
                <input type="hidden" name="task" value="search"/>
                <input type="hidden" name="Itemid" value="<?php echo CRoute::getItemId(); ?>"/>
            </form>
            <!--<?php if($canCreate) { ?>
            <button onclick="joms.api.videoAdd('<?php echo $isGroup ? $groupId : '' ?>', '<?php echo VIDEO_GROUP_TYPE ?>');" class="joms-button--primary"><?php echo ( !$isGroup ) ? JText::_('COM_COMMUNITY_VIDEOS_ADD') : JText::_('COM_COMMUNITY_GROUP_VIDEOS_ADD'); ?></button>
            <?php } ?>-->
        </div>
    </div>

    <?php if($submenu){ ?>
        <?php echo $submenu;?>
        <div class="joms-gap"></div>
    <?php } ?>

    <?php echo $sortings; ?>
    <div class="joms-gap"></div>
    <?php
    if( $groups )
    {
    ?>
    <div class="joms-alert joms-alert--info">
        <?php echo JText::sprintf( CStringHelper::isPlural( $count ) ? 'COM_COMMUNITY_GROUPS_INVIT_COUNT_MANY' : 'COM_COMMUNITY_GROUPS_INVIT_COUNT' , $count ); ?>
    </div>

    <ul class="joms-list--card">
    <?php
        for( $i = 0; $i < count( $groups ); $i++ )
        {
            $group  =& $groups[$i];
    ?>
        <li id="groups-invite-<?php echo $group->id;?>" class="joms-list__item">
            <div class="joms-list__cover">
                <a href="<?php echo $group->getLink(); ?>">
                    <div class="joms-list__cover-image" data-image="<?php echo $group->getCover(); ?>" style="background-image: url(<?php echo $group->getCover(); ?>);"></div>
                </a>
            </div>

            <div class="joms-list__content">
                <h4 class="joms-list__title">
                    <a href="<?php echo CRoute::_( 'index.php?option=com_community&view=groups&task=viewgroup&groupid=' . $group->id );?>"><?php echo $group->name; ?></a>
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
                        <a/>
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

                <div class="joms-js--invitation-notice-group-<?php echo $group->id; ?>"></div>
            </div>

            <div class="joms-list__footer joms-padding">
                <div class="<?php echo CUserHelper::onlineIndicator($creator); ?>">
                <a class="joms-avatar" href="<?php echo CUrlHelper::userLink($creator->id);?>"><img src="<?php echo $creator->getAvatar();?>" alt="avatar" data-author="<?php echo $creator->id; ?>" ></a>
                </div>
                <?php echo JText::_('COM_COMMUNITY_GROUPS_CREATED_BY'); ?> <a href="<?php echo CUrlHelper::userLink($creator->id);?>"><?php echo $creator->getDisplayName(); ?></a>
            </div>

            <span class="joms-list__permission joms-js--invitation-buttons-group-<?php echo $group->id; ?>">
                <a class="joms-button--neutral joms-button--smallest" href="javascript:" onclick="joms.api.invitationReject('group', '<?php echo $group->id; ?>');"><?php echo JText::_('COM_COMMUNITY_EVENTS_REJECT'); ?></a>
                <a class="joms-button--primary joms-button--smallest" href="javascript:" onclick="joms.api.invitationAccept('group', '<?php echo $group->id; ?>');"><?php echo JText::_('COM_COMMUNITY_EVENTS_ACCEPT'); ?></a>
            </span>
        </li>
    <?php
        }
    ?>
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

    <?php
    }else
    {
    ?>
    <div class="cEmpty cAlert"><?php echo JText::_('COM_COMMUNITY_GROUPS_NO_INVITATIONS'); ?></div>
    <?php
    }
    ?>

    <?php if ($pagination->getPagesLinks() && ($pagination->pagesTotal > 1 || $pagination->total > 1) ) { ?>
        <div class="joms-pagination">
            <?php echo $pagination->getPagesLinks(); ?>
        </div>
    <?php } ?>
</div>
