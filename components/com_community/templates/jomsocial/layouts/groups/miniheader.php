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

$featured = new CFeatured(FEATURED_GROUPS);
$featuredList = $featured->getItemIds();

$titleLength= $config->get('header_title_length', 30);
$summaryLength = $config->get('header_summary_length', 80);

$enableReporting = false;
if ( $config->get('enablereporting') == 1 && ( $my->id > 0 || $config->get('enableguestreporting') == 1 ) ) {
    $enableReporting = true;
}

?>

<div class="joms-body">
<div class="joms-focus">
<div class="joms-focus__cover joms-focus--mini">
    <?php  if (in_array($group->id, $featuredList)) { ?>
    <div class="joms-ribbon__wrapper">
        <span class="joms-ribbon"><?php echo JText::_('COM_COMMUNITY_FEATURED'); ?></span>
    </div>
    <?php } ?>

    <div class="joms-focus__cover-image--mobile" style="background:url(<?php echo $group->getCover(); ?>) no-repeat center center;">
    </div>

    <div class="joms-focus__header">
        <div class="joms-avatar--focus">
            <a href="<?php echo CRoute::_('index.php?option=com_community&view=groups&task=viewgroup&groupid=' . $group->id); ?>">
                <img src="<?php echo $group->getAvatar('avatar') . '?_=' . time(); ?>"
                     alt="<?php echo $group->name; ?>"/>
            </a>
        </div>

        <div class="joms-focus__title">
            <h3>
                <a href="<?php echo CRoute::_('index.php?option=com_community&view=groups&task=viewgroup&groupid=' . $group->id); ?>">
                    <?php echo CActivities::truncateComplex($this->escape($group->name), $titleLength, true); ?>
                </a>
            </h3>

            <div class="joms-focus__header__actions">
                <a class="joms-button--viewed nolink"
                   title="<?php echo JText::sprintf($group->hits > 0 ? 'COM_COMMUNITY_VIDEOS_HITS_COUNT_MANY' : 'COM_COMMUNITY_VIDEOS_HITS_COUNT',
                       $group->hits); ?>">
                    <svg viewBox="0 0 16 16" class="joms-icon">
                        <use xlink:href="<?php echo CRoute::getURI(); ?>#joms-icon-eye"></use>
                    </svg>
                    <span><?php echo $group->hits; ?></span>
                </a>

                <?php if ($config->get('enablesharethis') == 1) { ?>
                    <a class="joms-button--shared" title="<?php echo JText::_('COM_COMMUNITY_SHARE_THIS'); ?>"
                       href="javascript:"
                       onclick="joms.api.pageShare('<?php echo CRoute::getExternalURL('index.php?option=com_community&view=groups&task=viewgroup&groupid=' . $group->id); ?>')">
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
                <?php echo CActivities::truncateComplex($this->escape(strip_tags($group->summary)), $summaryLength); ?>
            </p>
        </div>

        <div class="joms-focus__actions__wrapper">
            <div class="joms-focus__actions--desktop">
                <?php if ($isMember) { ?>
                    <!-- invite friend button -->
                    <a href="javascript:" class="joms-focus__button--add"
                       onclick="joms.api.groupInvite('<?php echo $group->id; ?>')">
                        <?php echo JText::_('COM_COMMUNITY_INVITE_FRIENDS'); ?>
                    </a>
                <?php } elseif(!$isBanned) { ?>
                    <!-- join button -->
                    <a href="javascript:" class="joms-focus__button--message"
                       onclick="joms.api.groupJoin('<?php echo $group->id; ?>')">
                        <?php echo JText::_('COM_COMMUNITY_GROUPS_JOIN'); ?>
                    </a>
                <?php } ?>
            </div>

            <div class="joms-focus__header__actions--desktop">

                <a class="joms-button--viewed nolink"
                   title="<?php echo JText::sprintf($group->hits > 0 ? 'COM_COMMUNITY_VIDEOS_HITS_COUNT_MANY' : 'COM_COMMUNITY_VIDEOS_HITS_COUNT',
                       $group->hits); ?>">
                    <svg viewBox="0 0 16 16" class="joms-icon">
                        <use xlink:href="<?php echo CRoute::getURI(); ?>#joms-icon-eye"></use>
                    </svg>
                    <span><?php echo $group->hits; ?></span>
                </a>

                <?php if ($config->get('enablesharethis') == 1) { ?>
                    <a class="joms-button--shared" title="<?php echo JText::_('COM_COMMUNITY_SHARE_THIS'); ?>"
                       href="javascript:"
                       onclick="joms.api.pageShare('<?php echo CRoute::getExternalURL('index.php?option=com_community&view=groups&task=viewgroup&groupid=' . $group->id); ?>')">
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
</div>

<?php
    //do not show this to non-members if this is a private group
    if ($group->approvals == 0 || ($group->approvals == 1 && $isMember) || $isSuperAdmin) {
        ?>
        <ul class="joms-focus__link">
            <li class="half hidden-mobile">
                <a href="<?php echo CRoute::_('index.php?option=com_community&view=groups&task=viewmembers&groupid=' . $group->id); ?>">
                    <?php echo ($membersCount == 1)
                        ? JText::_('COM_COMMUNITY_GROUPS_MEMBER') . ' <span class="joms-text--light">' . $membersCount . '</span>'
                        : JText::_('COM_COMMUNITY_GROUPS_MEMBERS') . ' <span class="joms-text--light">' . $membersCount . '</span>'; ?>
                </a>
            </li>

            <?php if ($showEvents) { ?>
                <li class="half hidden-mobile">
                    <a href="<?php echo CRoute::_('index.php?option=com_community&view=events&task=display&groupid=' . $group->id); ?>">
                        <?php echo ($totalEvents == 1)
                            ? JText::_('COM_COMMUNITY_EVENTS_COUNT') . ' <span class="joms-text--light">' . $totalEvents . '</span>'
                            : JText::_('COM_COMMUNITY_EVENTS_COUNT_MANY') . ' <span class="joms-text--light">' . $totalEvents . '</span>'; ?>
                    </a>
                </li>
            <?php } ?>

            <?php if ($showPhotos) { ?>
                <li class="half hidden-mobile">
                    <a href="<?php echo CRoute::_('index.php?option=com_community&view=photos&task=display&groupid=' . $group->id); ?>">
                        <?php echo ($totalPhotos == 1) ?
                            JText::_('COM_COMMUNITY_PHOTOS_COUNT_SINGULAR') . ' <span class="joms-text--light">' . $totalPhotos . '</span>' :
                            JText::_('COM_COMMUNITY_PHOTOS_COUNT') . ' <span class="joms-text--light">' . $totalPhotos . '</span>'; ?>
                    </a>
                </li>
            <?php } ?>

            <?php if ($showVideos) { ?>
                <li class="half hidden-mobile">
                    <a href="<?php echo CRoute::_('index.php?option=com_community&view=videos&task=display&groupid=' . $group->id); ?>">
                        <?php echo ($totalVideos == 1)
                            ? JText::_('COM_COMMUNITY_VIDEOS_COUNT') . ' <span class="joms-text--light">' . $totalVideos . '</span>'
                            : JText::_('COM_COMMUNITY_VIDEOS_COUNT_MANY') . ' <span class="joms-text--light">' . $totalVideos . '</span>'; ?>
                    </a>
                </li>
            <?php } ?>

            <li class="half">
                <a href="javascript:" data-ui-object="joms-dropdown-button">
                    <?php echo JTEXT::_('COM_COMMUNITY_MORE'); ?>
                    <svg viewBox="0 0 14 20" class="joms-icon">
                        <use xlink:href="<?php echo CRoute::getURI(); ?>#joms-icon-arrow-down"></use>
                    </svg>
                </a>
                <ul class="joms-dropdown more-button">
                    <li class="hidden-desktop">
                        <a href="<?php echo CRoute::_('index.php?option=com_community&view=groups&task=viewmembers&groupid=' . $group->id); ?>">
                            <?php echo ($membersCount == 1)
                                ? JText::_('COM_COMMUNITY_GROUPS_MEMBER') . ' <span class="joms-text--light">' . $membersCount . '</span>'
                                : JText::_('COM_COMMUNITY_GROUPS_MEMBERS') . ' <span class="joms-text--light">' . $membersCount . '</span>'; ?>
                        </a>
                    </li>

                    <?php if ($showEvents) { ?>
                        <li class="hidden-desktop">
                            <a href="<?php echo CRoute::_('index.php?option=com_community&view=events&task=display&groupid=' . $group->id); ?>">
                                <?php echo ($totalEvents == 1)
                                    ? JText::_('COM_COMMUNITY_EVENTS_COUNT') . ' <span class="joms-text--light">' . $totalEvents . '</span>'
                                    : JText::_('COM_COMMUNITY_EVENTS_COUNT_MANY') . ' <span class="joms-text--light">' . $totalEvents . '</span>'; ?>
                            </a>
                        </li>
                    <?php } ?>

                    <?php if ($showPhotos) { ?>
                        <li class="hidden-desktop">
                            <a href="<?php echo CRoute::_('index.php?option=com_community&view=photos&task=display&groupid=' . $group->id); ?>">
                                <?php echo ($totalPhotos == 1) ?
                                    JText::_('COM_COMMUNITY_PHOTOS_COUNT_SINGULAR') . ' <span class="joms-text--light">' . $totalPhotos . '</span>' :
                                    JText::_('COM_COMMUNITY_PHOTOS_COUNT') . ' <span class="joms-text--light">' . $totalPhotos . '</span>'; ?>
                            </a>
                        </li>
                    <?php } ?>

                    <?php if ($showVideos) { ?>
                        <li class="hidden-desktop">
                            <a href="<?php echo CRoute::_('index.php?option=com_community&view=videos&task=display&groupid=display&groupid=' . $group->id); ?>">
                                <?php echo ($totalVideos == 1)
                                    ? JText::_('COM_COMMUNITY_VIDEOS_COUNT') . ' <span class="joms-text--light">' . $totalVideos . '</span>'
                                    : JText::_('COM_COMMUNITY_VIDEOS_COUNT_MANY') . ' <span class="joms-text--light">' . $totalVideos . '</span>'; ?>
                            </a>
                        </li>
                    <?php } ?>
                    <?php if ($config->get('creatediscussion')) { ?>
                        <li>
                            <a href="<?php echo CRoute::_('index.php?option=com_community&view=groups&task=viewdiscussions&groupid=' . $group->id); ?>">
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
                <li class="half liked">
                    <a href="javascript:"
                       class="joms-js--like-groups-<?php echo $group->id; ?><?php echo $isUserLiked > 0 ? ' liked' : ''; ?>"
                       onclick="joms.api.page<?php echo $isUserLiked > 0 ? 'Unlike' : 'Like' ?>('groups', '<?php echo $group->id; ?>');"
                       data-lang-like="<?php echo JText::_('COM_COMMUNITY_LIKE'); ?>"
                       data-lang-liked="<?php echo JText::_('COM_COMMUNITY_LIKED'); ?>">
                        <svg viewBox="0 0 14 20" class="joms-icon">
                            <use xlink:href="<?php echo CRoute::getURI(); ?>#joms-icon-thumbs-up"></use>
                        </svg>
                        <span
                            class="joms-js--lang"><?php echo ($isUserLiked > 0) ? JText::_('COM_COMMUNITY_LIKED') : JText::_('COM_COMMUNITY_LIKE'); ?></span>
                        <span class="joms-text--light"> <?php echo $totalLikes ?></span>
                    </a>
                </li>
            <?php } ?>

        </ul>
    <?php } ?>

</div>
</div>
