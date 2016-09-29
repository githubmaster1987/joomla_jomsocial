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
$showGroupDetails = ($group->approvals == 0 || ( $group->approvals == 1 && $isMember ) || $isSuperAdmin); //who can see the group details
$allowDiscussion = $config->get('creatediscussion');
//($config->get('creatediscussion') && (($group->isMember($my->id) && !$isBanned) || !COwnerHelper::isCommunityAdmin()));
$allowAnnouncement = $config->get('createannouncement');

$titleLength = $config->get('header_title_length', 60);
$summaryLength = $config->get('header_summary_length', 100);

$enableReporting = false;
if ( $config->get('enablereporting') == 1 && ( $my->id > 0 || $config->get('enableguestreporting') == 1 ) ) {
    $enableReporting = true;
}

?>

<div class="joms-body">

<!-- focus area -->
<div class="joms-focus">
<div class="joms-focus__cover">
    <?php  if (in_array($group->id, $featuredList)) { ?>
    <div class="joms-ribbon__wrapper">
        <span class="joms-ribbon joms-ribbon--full"><?php echo JText::_('COM_COMMUNITY_FEATURED'); ?></span>
    </div>
    <?php } ?>

    <div class="joms-focus__cover-image joms-js--cover-image">
        <img src="<?php echo $group->getCover(); ?>" alt="<?php echo $group->name; ?>"
        <?php if (!$group->defaultCover && $group->coverAlbum) { ?>
            style="width:100%;top:<?php echo $group->coverPostion; ?>;cursor:pointer"
            onclick="joms.api.coverClick(<?php echo $group->coverAlbum ?>, <?php echo $group->coverPhoto ?>);"
        <?php } else { ?>
            style="width:100%;top:<?php echo $group->coverPostion; ?>"
        <?php } ?>>
    </div>

    <div class="joms-focus__cover-image--mobile joms-js--cover-image-mobile"
        <?php if (!$group->defaultCover && $group->coverAlbum) { ?>
            style="background:url(<?php echo $group->getCover(); ?>) no-repeat center center;cursor:pointer"
            onclick="joms.api.coverClick(<?php echo $group->coverAlbum ?>, <?php echo $group->coverPhoto ?>);"
        <?php } else { ?>
            style="background:url(<?php echo $group->getCover(); ?>) no-repeat center center"
        <?php } ?>>
    </div>

    <div class="joms-focus__header">
        <div class="joms-avatar--focus">
            <a <?php if ( !$group->defaultAvatar && $group->avatarAlbum ) { ?>
                href="<?php echo CRoute::_('index.php?option=com_community&view=photos&task=photo&albumid=' . $group->avatarAlbum); ?>" style="cursor:default"
                onclick="joms.api.photoOpen(<?php echo $group->avatarAlbum ?>); return false;"
            <?php } ?>>
                <img src="<?php echo $group->getAvatar('avatar') . '?_=' . time(); ?>" alt="<?php echo $this->escape($group->name); ?>">
                <?php if ($isAdmin || $isSuperAdmin || $isMine) { ?>
                <svg class="joms-icon" viewBox="0 0 16 16" onclick="joms.api.avatarChange('group', '<?php echo $group->id ?>', arguments && arguments[0]);">
                    <use xlink:href="#joms-icon-camera"></use>
                </svg>
                <?php } ?>
            </a>
        </div>
        <div class="joms-focus__title">
            <h2><?php echo CActivities::truncateComplex($group->name, $titleLength, true); ?></h2>
            <div class="joms-focus__header__actions">
                <a class="joms-button--viewed nolink" title="<?php echo JText::sprintf( $group->hits > 0 ? 'COM_COMMUNITY_VIDEOS_HITS_COUNT_MANY' : 'COM_COMMUNITY_VIDEOS_HITS_COUNT', $group->hits ); ?>">
                    <svg viewBox="0 0 16 16" class="joms-icon">
                        <use xlink:href="<?php echo CRoute::getURI(); ?>#joms-icon-eye"></use>
                    </svg>
                    <span><?php echo $group->hits; ?></span>
                </a>

                <?php if ($config->get('enablesharethis') == 1) { ?>
                    <a class="joms-button--shared" title="<?php echo JText::_('COM_COMMUNITY_SHARE_THIS'); ?>"
                       href="javascript:" onclick="joms.api.pageShare('<?php echo CRoute::getExternalURL('index.php?option=com_community&view=groups&task=viewgroup&groupid=' . $group->id); ?>')">
                        <svg viewBox="0 0 16 16" class="joms-icon">
                            <use xlink:href="<?php echo CRoute::getURI(); ?>#joms-icon-redo"></use>
                        </svg>
                    </a>
                <?php } ?>

                <?php if ($enableReporting) { ?>
                    <a class="joms-button--viewed" title="<?php echo JText::_('COM_COMMUNITY_REPORT_GROUP'); ?>"
                       href="javascript:" onclick="joms.api.groupReport('<?php echo $group->id; ?>');">
                        <svg viewBox="0 0 16 16" class="joms-icon">
                            <use xlink:href="<?php echo CRoute::getURI(); ?>#joms-icon-warning"></use>
                        </svg>
                    </a>
                <?php } ?>

            </div>
            <p class="joms-focus__info--desktop">
                <?php echo CActivities::truncateComplex(strip_tags($group->summary), $summaryLength, true); ?>
            </p>
        </div>
        <div class="joms-focus__actions__wrapper">
            <div class="joms-focus__actions--desktop" style="position:relative">
                <?php if ($isMember) { ?>
                    <!-- invite friend button -->
                    <a href="javascript:" class="joms-focus__button--add" onclick="joms.api.groupInvite('<?php echo $group->id; ?>')">
                        <?php echo JText::_('COM_COMMUNITY_INVITE_FRIENDS'); ?>
                    </a>
                <?php } else if ($waitingApproval) { ?>
                    <!-- awaiting approval -->
                    <a href="javascript:" class="joms-focus__button--message">
                        <?php echo JText::_('COM_COMMUNITY_FRIENDS_AWAITING_AUTHORIZATION'); ?>
                    </a>
                    <div style="position:absolute;top:0;left:0;right:0;bottom:0"></div>
                <?php } elseif(!$isBanned) { ?>
                    <!-- join button -->
                    <a href="javascript:" class="joms-focus__button--message" onclick="joms.api.groupJoin('<?php echo $group->id; ?>')">
                        <?php echo JText::_('COM_COMMUNITY_GROUPS_JOIN'); ?>
                    </a>
                <?php } ?>
            </div>

            <div class="joms-focus__header__actions--desktop">

                <a class="joms-button--viewed nolink" title="<?php echo JText::sprintf( $group->hits > 0 ? 'COM_COMMUNITY_VIDEOS_HITS_COUNT_MANY' : 'COM_COMMUNITY_VIDEOS_HITS_COUNT', $group->hits ); ?>">
                    <svg viewBox="0 0 16 16" class="joms-icon">
                        <use xlink:href="<?php echo CRoute::getURI(); ?>#joms-icon-eye"></use>
                    </svg>
                    <span><?php echo $group->hits; ?></span>
                </a>

                <?php if ($config->get('enablesharethis') == 1) { ?>
                    <a class="joms-button--shared" title="<?php echo JText::_('COM_COMMUNITY_SHARE_THIS'); ?>"
                       href="javascript:" onclick="joms.api.pageShare('<?php echo CRoute::getExternalURL('index.php?option=com_community&view=groups&task=viewgroup&groupid=' . $group->id); ?>')">
                        <svg viewBox="0 0 16 16" class="joms-icon">
                            <use xlink:href="<?php echo CRoute::getURI(); ?>#joms-icon-redo"></use>
                        </svg>
                    </a>
                <?php } ?>

                <?php if ($enableReporting) { ?>
                    <a class="joms-button--viewed" title="<?php echo JText::_('COM_COMMUNITY_REPORT_GROUP'); ?>"
                       href="javascript:" onclick="joms.api.groupReport('<?php echo $group->id; ?>');">
                        <svg viewBox="0 0 16 16" class="joms-icon">
                            <use xlink:href="<?php echo CRoute::getURI(); ?>#joms-icon-warning"></use>
                        </svg>
                    </a>
                <?php } ?>

            </div>
        </div>
    </div>
    <div class="joms-focus__actions--reposition">
        <input type="button" class="joms-button--neutral" data-ui-object="button-cancel" value="<?php echo JText::_('COM_COMMUNITY_CANCEL'); ?>"> &nbsp;
        <input type="button" class="joms-button--primary" data-ui-object="button-save" value="<?php echo JText::_('COM_COMMUNITY_SAVE'); ?>">
    </div>
    <?php if ($isMember && !$isBanned || $isSuperAdmin) { ?>
        <div class="joms-focus__button--options--desktop">
            <a href="javascript:" data-ui-object="joms-dropdown-button">
                <svg viewBox="0 0 16 16" class="joms-icon">
                    <use xlink:href="<?php echo CRoute::getURI(); ?>#joms-icon-cog"></use>
                </svg>
            </a>
            <!-- No need to populate menus as it is cloned from mobile version. -->
            <ul class="joms-dropdown"></ul>
        </div>
    <?php } ?>
</div>
<div class="joms-focus__actions" style="position:relative;">


    <?php // Invite
    if ($isMember) { ?>
        <a href="javascript:" class="joms-focus__button--add" onclick="joms.api.groupInvite('<?php echo $group->id; ?>')">
            <?php echo JText::_('COM_COMMUNITY_INVITE_FRIENDS'); ?>
        </a>
    <?php } ?>


    <?php // Awaiting approval
    if ($waitingApproval) { ?>
        <a href="javascript:" class="joms-focus__button--message">
            <?php echo JText::_('COM_COMMUNITY_FRIENDS_AWAITING_AUTHORIZATION'); ?>
        </a>
        <div style="position:absolute;top:0;left:0;right:0;bottom:0"></div>
    <?php } ?>


    <?php // Join
    if(!$isMember && !$waitingApproval) { ?>
        <a href="javascript:" class="joms-focus__button--message" onclick="joms.api.groupJoin('<?php echo $group->id; ?>')">
            <?php echo JText::_('COM_COMMUNITY_GROUPS_JOIN'); ?>
        </a>
    <?php } ?>

    <?php if ( ($isMember && !$isBanned && !$waitingApproval) || $isSuperAdmin) { ?>
        <a class="joms-focus__button--options" data-ui-object="joms-dropdown-button"><?php echo JText::_('COM_COMMUNITY_GROUP_OPTIONS'); ?></a>
    <?php } ?>

    <ul class="joms-dropdown">

        <?php // @TODO: CAccess - Disable all options for non-members and non-superadmins
        if ( ($isMember && !$isBanned && !$waitingApproval) || $isSuperAdmin) { ?>

            <?php // Create discussion
            if ($my->authorise('community.create', 'groups.discussions.' . $group->id)) { ?>
                <li><a href="<?php echo CRoute::_('index.php?option=com_community&view=groups&task=adddiscussion&groupid=' . $group->id); ?>"><?php echo JText::_('COM_COMMUNITY_GROUPS_DISCUSSION_CREATE'); ?></a></li>
            <?php } ?>

            <?php // Create event
            if ($my->authorise('community.create', 'groups.events.' . $group->id)) { ?>
                <li><a href="<?php echo CRoute::_('index.php?option=com_community&view=events&task=create&groupid=' . $group->id); ?>"><?php echo JText::_('COM_COMMUNITY_GROUPS_CREATE_EVENT'); ?></a></li>
                <li><a href="<?php echo CRoute::_('index.php?option=com_community&view=events&task=import&groupid=' . $group->id); ?>"><?php echo JText::_('COM_COMMUNITY_GROUPS_IMPORT_EVENT'); ?></a></li>
            <?php } ?>



            <?php // @TODO: CAccess - Leave group
            if ($isMember && !$isMine && !$waitingApproval && COwnerHelper::isRegisteredUser()) { ?>
                <li><a href="javascript:" onclick="joms.api.groupLeave('<?php echo $group->id ?>');"><?php echo JText::_('COM_COMMUNITY_GROUPS_LEAVE'); ?></a></li>
            <?php } ?>


            <?php // @TODO: CAccess - Grou padmin actions
            if ($isAdmin || $isSuperAdmin || $isMine) { ?>
                <li><a href="javascript:" onclick="joms.api.avatarChange('group', '<?php echo $group->id; ?>');"><?php echo JText::_('COM_COMMUNITY_CHANGE_AVATAR'); ?></a></li>
                <li class="joms-js--menu-reposition joms-hidden--small"<?php echo $group->defaultCover ? ' style="display:none"' : '' ?>>
                    <a href="javascript:" data-propagate="1" onclick="joms.api.coverReposition('group', <?php echo $group->id; ?>);"><?php echo JText::_('COM_COMMUNITY_REPOSITION_COVER'); ?></a>
                </li>
                <li><a href="javascript:" onclick="joms.api.coverChange('group', <?php echo $group->id; ?>);"><?php echo JText::_('COM_COMMUNITY_CHANGE_COVER'); ?></a></li>
                <li class="joms-js--menu-remove-cover"<?php echo $group->defaultCover ? ' style="display:none"' : '' ?>>
                    <a href="javascript:" data-propagate="1" onclick="joms.api.coverRemove('group', <?php echo $group->id; ?>);"><?php echo JText::_('COM_COMMUNITY_REMOVE_COVER'); ?></a>
                </li>
                <li class="divider"></li>
            <?php } ?>



            <?php // Add photos
            if ($my->authorise('community.create', 'groups.photos.' . $group->id)) { ?>
                <li>
                    <a href="javascript:" data-propagate="1" onclick="joms.api.photoUpload('', '<?php echo $group->id; ?>', 'group');"><?php echo JText::_('COM_COMMUNITY_PHOTOS_UPLOAD_PHOTOS'); ?></a>
                </li>
            <?php } ?>

            <?php // Add videos
            if ($my->authorise('community.create', 'groups.videos.' . $group->id)) { ?>
                <li>
                    <a href="javascript:" data-propagate="1" onclick="joms.api.videoAdd('<?php echo $group->id; ?>', '<?php echo VIDEO_GROUP_TYPE; ?>');"><?php echo JText::_('COM_COMMUNITY_VIDEOS_ADD'); ?></a>
                </li>
            <?php } ?>

            <li>
                <a href="javascript:" onclick="joms.api.groupInvite('<?php echo $group->id ?>');"><?php echo JText::_('COM_COMMUNITY_INVITE_FRIENDS'); ?></a>
            </li>

            <?php // @TODO CAccess - More admin actions
            if ($isMine || $isSuperAdmin || $isAdmin) { ?>

                <li class="divider"></li>

                <li>
                    <a href="<?php echo CRoute::_('index.php?option=com_community&view=groups&task=edit&groupid=' . $group->id); ?>"><?php echo JText::_('COM_COMMUNITY_GROUPS_EDIT'); ?></a>
                </li>

                <li>
                    <a href="<?php echo CRoute::_('index.php?option=com_community&view=groups&task=sendmail&groupid=' . $group->id); ?>"><?php echo JText::_('COM_COMMUNITY_GROUPS_SENDMAIL'); ?></a>
                </li>

                <?php if($config->get('createannouncement',0)){?>
                    <li>
                        <a href="<?php echo CRoute::_('index.php?option=com_community&view=groups&task=addnews&groupid=' . $group->id); ?>"><?php echo JText::_('COM_COMMUNITY_GROUPS_BULLETIN_CREATE'); ?></a>
                    </li>
                <?php } ?>

                <?php // TODO: CAccess - super admin group unpublish
                if ($isSuperAdmin) { ?>
                    <li>
                        <a href="javascript:" onclick="joms.api.groupUnpublish('<?php echo $group->id; ?>');"><?php echo JText::_('COM_COMMUNITY_GROUPS_UNPUBLISH'); ?></a>
                    </li>
                <?php } ?>
                <?php if($my->authorise('community.delete', 'groups.' . $group->id, $group) ) { ?>
                <li class="divider"></li>
                <li>
                    <a href="javascript:" onclick="joms.api.groupDelete('<?php echo $group->id; ?>');"><?php echo JText::_('COM_COMMUNITY_GROUPS_DELETE_GROUP_BUTTON'); ?></a>
                </li>
                    <?php } ?>
            <?php } ?>

        <?php } ?>
    </ul>
</div>
<?php
//do not show this to non-members if this is a private group
if($showGroupDetails){
    ?>
    <ul class="joms-focus__link">
        <li class="half">
            <a href="<?php echo CRoute::_('index.php?option=com_community&view=groups&task=viewmembers&groupid=' . $group->id); ?>"><?php echo ($membersCount == 1)
                    ? JText::_('COM_COMMUNITY_GROUPS_MEMBER') . ' <span class="joms-text--light">' . $membersCount . '</span>'
                    : JText::_('COM_COMMUNITY_GROUPS_MEMBERS') . ' <span class="joms-text--light">' . $membersCount . '</span>'; ?></a>
        </li>

        <?php if ($showEvents) { ?>
            <li class="half">
                <a href="<?php echo CRoute::_('index.php?option=com_community&view=events&task=display&groupid=' . $group->id); ?>"><?php echo ($totalEvents == 1)
                        ? JText::_('COM_COMMUNITY_EVENTS_COUNT') . ' <span class="joms-text--light">' . $totalEvents . '</span>'
                        : JText::_('COM_COMMUNITY_EVENTS_COUNT_MANY') . ' <span class="joms-text--light">' . $totalEvents . '</span>' ; ?></a>
            </li>
        <?php } ?>

        <?php if ($showPhotos) { ?>
            <li class="half">
                <a href="<?php echo CRoute::_('index.php?option=com_community&view=photos&task=display&groupid=' . $group->id); ?>"><?php echo ($totalPhotos == 1) ?
                        JText::_('COM_COMMUNITY_PHOTOS_COUNT_SINGULAR') . ' <span class="joms-text--light">' . $totalPhotos . '</span>' :
                        JText::_('COM_COMMUNITY_PHOTOS_COUNT') . ' <span class="joms-text--light">' . $totalPhotos . '</span>' ; ?></a>
            </li>
        <?php } ?>

        <?php if ($showVideos) { ?>
            <li class="half">
                <a href="<?php echo CRoute::_('index.php?option=com_community&view=videos&task=display&groupid=' . $group->id); ?>">
                    <?php echo ($totalVideos == 1)
                        ? JText::_('COM_COMMUNITY_VIDEOS_COUNT') . ' <span class="joms-text--light">' . $totalVideos . '</span>'
                        : JText::_('COM_COMMUNITY_VIDEOS_COUNT_MANY') . ' <span class="joms-text--light">' . $totalVideos . '</span>' ; ?>
                </a>
            </li>
        <?php } ?>

        <li class="full">
            <a href="javascript:" data-ui-object="joms-dropdown-button">
                <?php echo JTEXT::_('COM_COMMUNITY_MORE'); ?>
                <svg viewBox="0 0 14 20" class="joms-icon">
                    <use xlink:href="<?php echo CRoute::getURI(); ?>#joms-icon-arrow-down"></use>
                </svg>
            </a>
            <ul class="joms-dropdown more-button">
                <?php if ($config->get('creatediscussion')) { ?>
                    <li><a href="<?php echo CRoute::_('index.php?option=com_community&view=groups&task=viewdiscussions&groupid=' . $group->id); ?>">
                            <?php echo ($totalDiscussion == 1)
                                ? JText::_('COM_COMMUNITY_GROUPS_DISCUSSION') . ' <span class="joms-text--light">' . $totalDiscussion . '</span>'
                                : JText::_('COM_COMMUNITY_GROUPS_DISCUSSIONS') . ' <span class="joms-text--light">' . $totalDiscussion . '</span>'; ?>
                        </a></li>
                <?php } ?>
                <?php if ($config->get('createannouncement') && ($isMember || $isSuperAdmin)) { ?>
                    <li>
                        <a href="<?php echo CRoute::_('index.php?option=com_community&view=groups&task=viewbulletins&groupid=' . $group->id); ?>">
                            <?php echo ($totalBulletin == 1)
                                ? JText::_('COM_COMMUNITY_GROUPS_ANNOUNCEMENT_COUNT') . ' <span class="joms-text--light">' . $totalBulletin . '</span>'
                                : JText::_('COM_COMMUNITY_GROUPS_ANNOUNCEMENT_COUNT_MANY') . ' <span class="joms-text--light">' . $totalBulletin . '</span>'; ?>
                        </a>
                    </li>
                <?php } ?>
                <?php if ((($isAdmin) || ($isMine) || ($isMember && !$isBanned)) && $isFile) { ?>
                    <li>
                        <a href="javascript:" onclick="joms.api.fileList('group',<?php echo $group->id ?>)">
                        <?php echo ($isFile == 1)
                            ? JText::_('COM_COMMUNITY_GROUPS_FILE_COUNT') . ' <span class="joms-text--light">' . $isFile . '</span>'
                            : JText::_('COM_COMMUNITY_GROUPS_FILE_COUNT_MANY') . ' <span class="joms-text--light">' . $isFile . '</span>'; ?>
                        </a>
                    </li>
                <?php } ?>

                <?php if ($isAdmin || $isSuperAdmin) { ?>
                    <li>
                        <a href="<?php echo CRoute::_('index.php?option=com_community&view=groups&task=banlist&list=-1&groupid='.$group->id) ?>">
                            <?php echo JText::_('COM_COMMUNITY_GROUPS_BANNED_MEMBERS') ; ?>
                            <span class="joms-text--light"><?php echo $totalBannedMembers; ?></span>
                        </a>
                    </li>
                <?php } ?>
            </ul>
        </li>

        <?php if ($isLikeEnabled) { ?>
            <li class="full liked">
                <a href="javascript:"
                   class="joms-js--like-groups-<?php echo $group->id; ?><?php echo $isUserLiked > 0 ? ' liked' : ''; ?>"
                   onclick="joms.api.page<?php echo $isUserLiked > 0 ? 'Unlike' : 'Like' ?>('groups', '<?php echo $group->id; ?>');"
                   data-lang-like="<?php echo JText::_('COM_COMMUNITY_LIKE'); ?>"
                   data-lang-liked="<?php echo JText::_('COM_COMMUNITY_LIKED'); ?>">
                    <svg viewBox="0 0 14 20" class="joms-icon">
                        <use xlink:href="<?php echo CRoute::getURI(); ?>#joms-icon-thumbs-up"></use>
                    </svg>
                    <span class="joms-js--lang"><?php echo ($isUserLiked > 0) ? JText::_('COM_COMMUNITY_LIKED') : JText::_('COM_COMMUNITY_LIKE'); ?></span>
                    <span class="joms-text--light"> <?php echo $totalLikes ?></span>
                </a>
            </li>
        <?php }?>

    </ul>
<?php } ?>
</div>

<div class="joms-sidebar">
<div class="joms-module__wrapper"><?php $this->renderModules('js_side_top'); ?></div>
<div class="joms-module__wrapper--stacked"><?php $this->renderModules('js_side_top_stacked'); ?></div>
<div class="joms-module__wrapper"><?php $this->renderModules('js_groups_side_top'); ?></div>
<div class="joms-module__wrapper--stacked"><?php $this->renderModules('js_groups_side_top_stacked'); ?></div>

<?php
//do not show this to non-members if this is a private group
if($showGroupDetails){
    $isFirstTab = 1;
    ?>
    <div class="joms-module__wrapper">
        <div class="joms-tab__bar">
            <?php if($allowDiscussion) { ?>
                <a href="#joms-group--discussion" class="active no-padding">
                    <div class="joms-tab__bar--button">
                        <span class="title"><?php echo JText::_('COM_COMMUNITY_DISCUSSIONS'); ?></span>
                        <?php if($my->authorise('community.create', 'groups.discussions.' . $group->id)){ ?>
                        <span class="add" onclick="window.location='<?php echo CRoute::_('index.php?option=com_community&view=groups&task=adddiscussion&groupid='.$group->id); ?>'">
                            <svg class="joms-icon" viewBox="0 -5 15 30">
                                <use xlink:href="<?php echo CRoute::getURI(); ?>#joms-icon-plus"></use>
                            </svg>
                        </span>
                        <?php } ?>
                    </div>
                </a>
                <?php
                $isFirstTab = 0;
            }?>

            <?php if($allowAnnouncement) { ?>
                <a href="#joms-group--announcement" class="<?php if($isFirstTab) echo "active";?> no-padding">
                    <div class="joms-tab__bar--button">
                        <span class="title"><?php echo JText::_('COM_COMMUNITY_ANNOUNCEMENTS'); ?></span>
                        <?php if($my->authorise('community.create', 'groups.announcement.' . $group->id)){ ?>
                        <span class="add" onclick="window.location='<?php echo CRoute::_('index.php?option=com_community&view=groups&task=addnews&groupid='.$group->id); ?>'">
                            <svg class="joms-icon" viewBox="0 -5 15 30">
                                <use xlink:href="<?php echo CRoute::getURI(); ?>#joms-icon-plus"></use>
                            </svg>
                        </span>
                        <?php } ?>
                    </div>
                </a>
            <?php } ?>
        </div>

        <?php if($allowDiscussion) { ?>
            <div id="joms-group--discussion" class="joms-tab__content">
                <?php
                if ($group->approvals == '0' || $isMine || ($isMember && !$isBanned) || $isSuperAdmin) {
                    if ($config->get('creatediscussion')) {
                        ?>

                        <?php
                        if ($discussions) {
                            foreach ($discussions as $row) {

                                ?>
                                <div class="joms-stream__container">

                                    <div class="joms-stream__header">
                                        <div class="joms-avatar--stream <?php echo CUserHelper::onlineIndicator($row->user); ?>">
                                            <a href="<?php echo CUrlHelper::userLink($row->user->id); ?>">
                                                <img data-author="<?php echo $row->user->id; ?>" src="<?php echo $row->user->getThumbAvatar(); ?>"
                                                     alt="<?php echo $row->user->getDisplayName(); ?>"/>
                                            </a>
                                        </div>
                                        <div class="joms-stream__meta">
                                            <a class="joms-stream__user"
                                               href="<?php echo CRoute::_('index.php?option=com_community&view=groups&task=viewdiscussion&groupid=' . $group->id . '&topicid=' . $row->id); ?>">
                                                <?php echo $row->title; ?>
                                            </a>
                                        <span class="joms-stream__time">
                                            <small>
                                                <?php echo JText::sprintf('COM_COMMUNITY_GROUPS_DISCUSSION_CREATOR',
                                                    '<a href="' . CUrlHelper::userLink($row->user->id) . '">' . $row->user->getDisplayName() . '</a>'); ?>
                                                <?php echo JHTML::_('date', $row->created, JText::_('DATE_FORMAT_LC')); ?>
                                            </small>
                                        </span>
                                        <span>
                                            <small>
                                                <a href="<?php echo CRoute::_('index.php?option=com_community&view=groups&task=viewdiscussion&groupid=' . $group->id . '&topicid=' . $row->id); ?>">
                                                    <?php echo JText::sprintf((CStringHelper::isPlural($row->count)) ? 'COM_COMMUNITY_TOTAL_REPLIES_MANY' : 'COM_COMMUNITY_GROUPS_DISCUSSION_REPLY_COUNT',
                                                        $row->count); ?>
                                                </a>
                                            </small>
                                        </span>
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
                                    'index.php?option=com_community&view=groups&groupid=' . $group->id . '&task=adddiscussion')); ?>
                            </p>
                        <?php
                        }
                        ?>

                        <div class="cUpdatesHelper clearfull">
                          <span class="updates-options cFloat-R">
                            <?php if (($isMember && !$isBanned) && !($waitingApproval) || $isSuperAdmin): ?>
                                <a class="app-box-action" href="<?php echo CRoute::_(
                                    'index.php?option=com_community&view=groups&groupid=' . $group->id . '&task=adddiscussion'
                                ); ?>">
                                    <?php echo JText::_('COM_COMMUNITY_GROUPS_DISCUSSION_CREATE'); ?>
                                </a>
                            <?php endif; ?>
                              <?php if ($discussions): ?>
                                  <a class="app-box-action" href="<?php echo CRoute::_(
                                      'index.php?option=com_community&view=groups&task=viewdiscussions&groupid=' . $group->id
                                  ); ?>">
                                      <?php echo JText::_('COM_COMMUNITY_GROUPS_VIEW_ALL_DISCUSSIONS'); ?>
                                  </a>
                              <?php endif; ?>
                          </span>
                          <span class="updates-pagination"><?php if (count($discussions) > 1) {
                                  echo JText::sprintf(
                                      'COM_COMMUNITY_GROUPS_DISCUSSION_COUNT_OF',
                                      count($discussions),
                                      $totalDiscussion
                                  );
                              } ?>
                          </span>
                        </div>
                    <?php
                    }
                }
                ?>
            </div>
        <?php } ?>

        <?php if($allowAnnouncement) { ?>
            <div id="joms-group--announcement" class="joms-tab__content" <?php if(!$isFirstTab) echo 'style="display:none;"';?>>
                <?php
                if ($group->approvals == '0' || $isMine || ($isMember && !$isBanned) || $isSuperAdmin) {
                    if ($config->get('createannouncement')) {
                        ?>

                        <?php
                        if ($bulletins) {
                            for ($i = 0; $i < count($bulletins); $i++) {
                                $row =& $bulletins[$i];
                                ?>
                                <div class="joms-list">
                                    <div class="joms-comment__item">
                                        <div class="joms-comment__header">
                                            <div class="joms-avatar--comment <?php echo CUserHelper::onlineIndicator($row->creator); ?>">
                                                <a href="<?php echo CUrlHelper::userLink($row->creator->id); ?>">
                                                    <img data-author="<?php echo $row->creator->id; ?>" src="<?php echo $row->creator->getThumbAvatar(); ?>" alt="<?php echo $row->creator->getDisplayName(); ?>"/>
                                                </a>
                                            </div>
                                            <div class="joms-comment__meta">
                                                <h4 class="reset-gap">
                                                    <a href="<?php echo CRoute::_('index.php?option=com_community&view=groups&task=viewbulletin&groupid=' . $group->id . '&bulletinid=' . $row->id); ?>">
                                                        <?php echo $row->title; ?>
                                                    </a>
                                                </h4>
                                            <span class="joms-comment__time">
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

                        <div class="cUpdatesHelper clearfull">
                        <span class="updates-options cFloat-R">
                            <?php if ($isAdmin || $isSuperAdmin): ?>
                                <a class="app-box-action" href="<?php echo CRoute::_(
                                    'index.php?option=com_community&view=groups&task=addnews&groupid=' . $group->id
                                ); ?>"><?php echo JText::_('COM_COMMUNITY_GROUPS_BULLETIN_CREATE'); ?></a>
                            <?php endif; ?>

                            <?php if ($bulletins): ?>
                                <a class="app-box-action" href="<?php echo CRoute::_(
                                    'index.php?option=com_community&view=groups&task=viewbulletins&groupid=' . $group->id
                                ); ?>"><?php echo JText::_('COM_COMMUNITY_GROUPS_BULLETIN_VIEW_ALL'); ?></a>
                            <?php endif; ?>
                        </span>
                        <span class="updates-pagination"><?php if (count($bulletins) > 1) {
                                echo JText::sprintf(
                                    'COM_COMMUNITY_GROUPS_BULLETIN_COUNT_OF',
                                    count($bulletins),
                                    $totalBulletin
                                );
                            } ?>
                        </span>
                        </div>
                    <?php
                    }
                }
                ?>
            </div>
        <?php } ?>

    </div>

<?php } ?>

<!-- Group's Module' -->
<?php if ($group->approvals == '0' || $isMine || ($isMember && !$isBanned) || $isSuperAdmin) { ?>
    <div class="joms-module__wrapper">

        <div class="joms-tab__bar">
            <a href="#joms-group--members" class="active"><?php echo JText::sprintf('COM_COMMUNITY_GROUPS_MEMBERS'); ?></a>
            <?php if($showEvents) { ?>
                <a href="#joms-group--events" ><?php echo JText::sprintf('COM_COMMUNITY_EVENTS'); ?></a>
            <?php } ?>
        </div>

        <!-- Group's Members @ Sidebar -->
        <?php if ($members) { ?>
            <div id="joms-group--members" class="joms-tab__content">

                <ul class="joms-list--photos clearfix">
                    <?php foreach ($members as $member) { ?>
                        <li class="joms-list__item">
                            <div class="joms-avatar <?php echo CUserHelper::onlineIndicator($member); ?>">
                                <a href="<?php echo CUrlHelper::userLink($member->id); ?>">
                                    <img
                                        src="<?php echo $member->getThumbAvatar(); ?>"
                                        title="<?php echo CTooltip::cAvatarTooltip($member); ?>"
                                        alt="<?php echo CTooltip::cAvatarTooltip($member); ?>" data-author="<?php echo $member->id; ?>" />
                                </a>
                            </div>
                        </li>
                        <?php if (--$limit < 1) {
                            break;
                        }
                    } ?>
                </ul>

                <div class="cUpdatesHelper clearfull">
                    <a href="<?php echo CRoute::_(
                        'index.php?option=com_community&view=groups&task=viewmembers&groupid=' . $group->id
                    ); ?>">
                        <?php echo JText::_('COM_COMMUNITY_VIEW_ALL'); ?> (<?php echo $membersCount; ?>)
                    </a>
                </div>
            </div>
        <?php } ?>
        <!-- Group's Members @ Sidebar -->

        <!-- Group Events @ Sidebar -->
        <?php if ($showEvents) { ?>
            <div id="joms-group--events" class="joms-tab__content" style="display:none;">

                <?php if ($events) { ?>
                    <ul class="joms-list--event">
                        <?php
                        foreach ($events as $event) {
                            $creator = CFactory::getUser($event->creator);
                            ?>
                            <li class="joms-media--event">
                                <div class="joms-media__calendar">
                                    <?php
                                    $datestr = strtotime($event->getStartDate());
                                    $day = date('d', $datestr);
                                    $month = date('M', $datestr);
                                    $year = date('y', $datestr);
                                    ?>
                                    <span class="month"><?php echo $month; ?></span>
                                    <span class="date"><?php echo $day; ?></span>
                                </div>

                                <div class="joms-media__body">
                                    <div class="event-detail">
                                        <a href="<?php echo CRoute::_(
                                            'index.php?option=com_community&view=events&task=viewevent&eventid=' . $event->id . '&groupid=' . $group->id
                                        ); ?>" class="cThumb-Title">
                                            <?php echo $event->title; ?>
                                        </a>

                                        <div class="cThumb-Location">
                                            <?php // echo $event->getCategoryName();?>
                                            <?php echo $event->location; ?>
                                        </div>
                                        <!-- <div class="eventTime"><?php echo JText::sprintf(
                                            'COM_COMMUNITY_EVENTS_DURATION',
                                            JHTML::_('date', $event->startdate, JText::_('DATE_FORMAT_LC2')),
                                            JHTML::_('date', $event->enddate, JText::_('DATE_FORMAT_LC2'))
                                        ); ?></div> -->
                                        <div class="cThumb-Members">
                                            <a href="<?php echo CRoute::_(
                                                'index.php?option=com_community&view=events&task=viewguest&groupid=' . $group->id . '&eventid=' . $event->id . '&type=' . COMMUNITY_EVENT_STATUS_ATTEND
                                            ); ?>"><?php echo JText::sprintf(
                                                    (!CStringHelper::isSingular(
                                                        $event->confirmedcount
                                                    )) ? 'COM_COMMUNITY_EVENTS_MANY_GUEST_COUNT' : 'COM_COMMUNITY_EVENTS_GUEST_COUNT',
                                                    $event->confirmedcount
                                                ); ?></a>
                                        </div>
                                    </div>
                                </div>
                            </li>
                        <?php } ?>
                    </ul>
                <?php } else { ?>
                    <div class="cEmpty"><?php echo JText::_('COM_COMMUNITY_EVENTS_NOT_CREATED'); ?></div>
                <?php } ?>

                <div class="cUpdatesHelper clearfull">
                    <a href="<?php echo CRoute::_(
                        'index.php?option=com_community&view=events&task=display&groupid=' . $group->id
                    ); ?>">
                        <?php echo JText::_('COM_COMMUNITY_EVENTS_ALL_EVENTS'); ?>
                    </a>
                </div>
            </div>

        <?php } ?>
    </div>
    <!-- Group Events @ Sidebar -->
    <div class="joms-module__wrapper">
        <div class="joms-tab__bar">

            <?php if ($showPhotos) { ?>
            <?php if ($albums) { ?>
                <a href="#joms-group--photos" class="active"><?php echo JText::_('COM_COMMUNITY_PHOTOS_PHOTO_ALBUMS'); ?></a>
            <?php } ?>
            <?php } ?>

            <?php if ($showVideos) { ?>
            <?php if ($videos) { ?>
                <a href="#joms-group--videos"><?php echo JText::_('COM_COMMUNITY_VIDEOS'); ?></a>
            <?php } ?>
            <?php } ?>

        </div>

        <?php if ($showPhotos) { ?>
        <?php if ($albums) { ?>
            <div id="joms-group--photos" class="joms-tab__content">
                <ul class="joms-list--photos">
                    <?php foreach ($albums as $album) { ?>
                        <li class="joms-list__item">
                            <a href="<?php echo CRoute::_(
                                'index.php?option=com_community&view=photos&task=album&albumid=' . $album->id . '&groupid=' . $group->id
                            ); ?>">
                                <img class="cAvatar cMediaAvatar jomNameTips"
                                     title="<?php echo $this->escape($album->name); ?>"
                                     src="<?php echo $album->getCoverThumbURI(); ?>"
                                     alt="<?php echo $album->getCoverThumbURI(); ?>"/>
                            </a>
                        </li>
                    <?php } ?>
                </ul>

                <div class="cUpdatesHelper clearfull">
                    <a href="<?php echo CRoute::_(
                        'index.php?option=com_community&view=photos&task=display&groupid=' . $group->id
                    ); ?>">
                        <?php echo JText::_('COM_COMMUNITY_VIEW_ALL_ALBUMS') . ' (' . $totalAlbums . ')'; ?>
                    </a>
                </div>
            </div>
        <?php } ?>
        <?php } ?>

        <?php if ($showVideos) { ?>
        <?php if ($videos) { ?>
            <div id="joms-group--videos" class="joms-tab__content" style="display:none;" >
                <ul class="joms-list--videos">
                    <?php foreach ($videos as $video) { ?>
                        <li class="joms-list__item">
                            <a href="<?php echo $video->getURL(); ?>"
                               title="<?php echo $video->title; ?>">
                                <img src="<?php echo $video->getThumbnail(); ?>" class="joms-list__cover" alt="<?php echo $video->title; ?>" />
                                <span class="joms-video__duration"><?php echo $video->getDurationInHMS(); ?></span>
                            </a>
                        </li>
                    <?php } ?>
                </ul>

                <div class="cUpdatesHelper clearfull">
                    <a href="<?php echo CRoute::_(
                        'index.php?option=com_community&view=videos&task=display&groupid=' . $group->id
                    ); ?>">
                        <?php echo JText::_('COM_COMMUNITY_VIDEOS_ALL') . ' (' . $totalVideos . ')'; ?>
                    </a>
                </div>
            </div>
        <?php } ?>
        <?php } ?>

    </div>

<?php } ?>

<div class="joms-module__wrapper"><?php $this->renderModules('js_groups_side_bottom'); ?></div>
<div class="joms-module__wrapper--stacked"><?php $this->renderModules('js_groups_side_bottom_stacked'); ?></div>
<div class="joms-module__wrapper"><?php $this->renderModules('js_side_bottom'); ?></div>
<div class="joms-module__wrapper--stacked"><?php $this->renderModules('js_side_bottom_stacked'); ?></div>

</div>

<div class="joms-main">

    <div class="joms-middlezone">

        <!-- Group's Approval -->
        <?php if (($isMine || $isAdmin || $isSuperAdmin) && ($unapproved > 0)) { ?>
            <div id="joms-group--approval" class="joms-alert--info">
                <svg viewBox="0 0 20 20" class="joms-icon">
                    <use xlink:href="<?php echo CRoute::getURI(); ?>#joms-icon-user"></use>
                </svg>
                <a class="friend" href="<?php echo CRoute::_(
                    'index.php?option=com_community&view=groups&task=viewmembers&approve=1&groupid=' . $group->id
                ); ?>">
                    <?php echo JText::sprintf(
                        (CStringHelper::isPlural(
                            $unapproved
                        )) ? 'COM_COMMUNITY_GROUPS_APPROVAL_NOTIFICATION_MANY' : 'COM_COMMUNITY_GROUPS_APPROVAL_NOTIFICATION',
                        $unapproved
                    ); ?>
                </a>
            </div>
        <?php } ?>
        <!-- Group's Approval -->

        <!-- Waiting Approval -->
        <?php if ($waitingApproval) { ?>
            <div class="joms-alert--info">
                <svg viewBox="0 0 20 20" class="joms-icon">
                    <use xlink:href="<?php echo CRoute::getURI(); ?>#joms-icon-clock"></use>
                </svg>
                <span><?php echo JText::_('COM_COMMUNITY_GROUPS_APPROVAL_PENDING'); ?></span>
            </div>
        <?php } ?>

        <?php if ($isInvited) { ?>
            <div id="groups-invite-<?php echo $group->id; ?>" class="joms-alert--info">
                <h4 class="joms-alert__head">
                    <?php echo JText::sprintf('COM_COMMUNITY_GROUPS_INVITATION', $join); ?>
                </h4>

                <div class="joms-alert__body">
                    <div class="joms-alert__content">
                        <?php echo JText::sprintf('COM_COMMUNITY_GROUPS_YOU_INVITED', $join); ?>
                        <span>
                                <?php echo JText::sprintf(
                                    (CStringHelper::isPlural(
                                        $friendsCount
                                    )) ? 'COM_COMMUNITY_GROUPS_FRIEND' : 'COM_COMMUNITY_GROUPS_FRIEND_MANY',
                                    $friendsCount
                                ); ?>
                            </span>
                    </div>
                    <div class="joms-alert__actions">
                        <a href="javascript:void(0);"
                           onclick="jax.call('community','groups,ajaxRejectInvitation','<?php echo $group->id; ?>');" class="joms-button--neutral joms-button--small">
                            <?php echo JText::_('COM_COMMUNITY_EVENTS_REJECT'); ?>
                        </a>
                        <a href="javascript:void(0);"
                           onclick="jax.call('community','groups,ajaxAcceptInvitation','<?php echo $group->id; ?>');"
                           class="joms-button--primary joms-button--small">
                            <?php echo JText::_('COM_COMMUNITY_EVENTS_ACCEPT'); ?>
                        </a>
                    </div>
                </div>
            </div>
        <?php } ?>

        <div class="joms-tab__bar">
            <?php
            //do not show this to non-members if this is a private group
            if($showGroupDetails){
                ?>
                <a href="#joms-group--stream" class="<?php echo (!$showGroupDetails || $config->get('default_group_tab') == 0) ? 'active' : ''; ?>">
                    <?php echo JText::_('COM_COMMUNITY_ACTIVITIES'); ?>
                </a>
            <?php } ?>
            <a href="#joms-group--details" class="<?php echo (($showGroupDetails && $config->get('default_group_tab') == 1) || !$showGroupDetails) ? 'active' : ''; ?>">
                <?php echo JText::_('COM_COMMUNITY_GROUP_DETAILS'); ?>
            </a>
        </div>

        <div class="joms-gap"></div>

        <div id="joms-group--stream" class="joms-tab__content" style="<?php echo (!$showGroupDetails || $config->get('default_group_tab') == 0) ? '' : 'display:none'; ?>">
            <?php if($isMember || $isSuperAdmin || $isAdmin) {$status->render();} ?>
            <?php echo $streamHTML; ?>
        </div>
        <div id="joms-group--details" class="joms-tab__content" style="<?php echo (($showGroupDetails && $config->get('default_group_tab') == 1) || !$showGroupDetails) ? '' : 'display:none'; ?>">
            <ul class="joms-list__row">
                <li>
                    <span>
                        <?php echo $group->description; ?>
                        <?php
                        //find out if there is any url here, if there is, run it via embedly when enabled
                        $params = new CParameter($group->params);
                        if($params->get('url') && $config->get('enable_embedly')){
                            ?>
                            <a href="<?php echo $params->get('url'); ?>" class="embedly-card" data-card-controls="0" data-card-recommend="0" data-card-theme="<?php echo $config->get('enable_embedly_card_template'); ?>" data-card-align="<?php echo $config->get('enable_embedly_card_position') ?>"><?php echo JText::_('COM_COMMUNITY_EMBEDLY_LOADING');?></a>
                        <?php } ?>
                    </span>
                </li>
                <li>
                    <h5 class="joms-text--light"><?php echo JText::_('COM_COMMUNITY_GROUPS_CATEGORY'); ?></h5>
                        <span><a href="<?php echo CRoute::_(
                                'index.php?option=com_community&view=groups&task=display&categoryid=' . $group->categoryid
                            ); ?>"><?php echo JText::_($group->getCategoryName()); ?></a></span>
                </li>
                <li>
                    <h5 class="joms-text--light"><?php echo JText::_('COM_COMMUNITY_GROUPS_CREATE_TIME'); ?></h5>
                    <span><?php echo JHTML::_('date', $group->created, JText::_('DATE_FORMAT_LC')); ?></span>
                </li>
                <li>
                    <h5 class="joms-text--light"><?php echo JText::_('COM_COMMUNITY_GROUPS_ADMINS'); ?></h5>
                    <span><?php echo $adminsList; ?></span>
                </li>
            </ul>
        </div>

    </div>

</div>

</div>

<script>
    // Clone menu from mobile version to desktop version.
    (function( w ) {
        w.joms_queue || (w.joms_queue = []);
        w.joms_queue.push(function() {
            var src = joms.jQuery('.joms-focus__actions ul.joms-dropdown'),
                clone = joms.jQuery('.joms-focus__button--options--desktop ul.joms-dropdown');

            clone.html( src.html() );
        });
    })( window );
</script>

<script>

    // override config setting
    joms || (joms = {});
    joms.constants || (joms.constants = {});
    joms.constants.conf || (joms.constants.conf = {});

    joms.constants.groupid = <?php echo $group->id; ?>;
    joms.constants.videocreatortype = '<?php echo VIDEO_GROUP_TYPE ?>';
    joms.constants.conf.enablephotos = <?php echo (isset($showPhotos) && $showPhotos == 1 && (( $isAdmin && $photoPermission == 1 ) || ($isMember && $photoPermission == 2) ) ) ? 1 : 0 ; ?>;
    joms.constants.conf.enablevideos = <?php echo (isset($showVideos) && $showVideos == 1 && (( $isAdmin && $videoPermission == 1 ) || ($isMember && $videoPermission == 2) ) ) ? 1 : 0 ; ?>;
    joms.constants.conf.enablevideosupload  = <?php echo $config->get('enablevideosupload');?>;
    joms.constants.conf.enableevents = <?php echo (isset($showEvents) && $showEvents == 1 && (( $isAdmin && $eventPermission == 1 ) || ($isMember && $eventPermission == 2) ) && $my->canCreateEvents() ) ? 1 : 0 ; ?>;

</script>
