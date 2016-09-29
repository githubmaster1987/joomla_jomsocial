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

$featured = new CFeatured(FEATURED_EVENTS);
$featuredList = $featured->getItemIds();

$titleLength= $config->get('header_title_length', 30);
$summaryLength = $config->get('header_summary_length', 80);

$enableReporting = false;
if ( $config->get('enablereporting') == 1 && ( $my->id > 0 || $config->get('enableguestreporting') == 1 ) ) {
    $enableReporting = true;
}

$isGroupEvent = $event->type == CEventHelper::GROUP_TYPE;

?>
<div class="joms-focus">
    <div class="joms-focus__cover joms-focus--mini">
        <?php  if (in_array($event->id, $featuredList)) { ?>
        <div class="joms-ribbon__wrapper">
            <span class="joms-ribbon"><?php echo JText::_('COM_COMMUNITY_FEATURED'); ?></span>
        </div>
        <?php } ?>

        <div class="joms-focus__cover-image--mobile" style="background:url(<?php echo $event->getCover(); ?>) no-repeat center center;">
        </div>

        <div class="joms-focus__header">
            <div class="joms-focus__date">
                <span><?php echo JText::_( CEventHelper::formatStartDate($event, 'M') ); ?></span>
                <span><?php echo JText::_( CEventHelper::formatStartDate($event, 'd') ); ?></span>
            </div>
            <div class="joms-focus__title">
                <h3><a href="<?php echo $event->getLink(); ?>">
                <?php echo CActivities::truncateComplex($event->title , $titleLength, true); ?>
                </a></h3>
                <div class="joms-focus__header__actions">

                    <a class="joms-button--viewed nolink" title="<?php echo JText::sprintf( $event->hits > 0 ? 'COM_COMMUNITY_VIDEOS_HITS_COUNT_MANY' : 'COM_COMMUNITY_VIDEOS_HITS_COUNT', $event->hits ); ?>">
                        <svg viewBox="0 0 16 16" class="joms-icon">
                            <use xlink:href="<?php echo CRoute::getURI(); ?>#joms-icon-eye"></use>
                        </svg>
                        <span><?php echo $event->hits; ?></span>
                    </a>

                    <?php if ($config->get('enablesharethis') == 1) { ?>
                        <a class="joms-button--shared" title="<?php echo JText::_('COM_COMMUNITY_SHARE_THIS'); ?>"
                           href="javascript:" onclick="joms.api.pageShare('<?php echo CRoute::getExternalURL( 'index.php?option=com_community&view=events&task=viewevent&eventid=' . $event->id ); ?>')">
                            <svg viewBox="0 0 16 16" class="joms-icon">
                                <use xlink:href="<?php echo CRoute::getURI(); ?>#joms-icon-redo"></use>
                            </svg>
                        </a>
                    <?php } ?>

                    <?php if ($enableReporting) { ?>
                        <a class="joms-button--viewed" title="<?php echo JText::_('COM_COMMUNITY_EVENTS_REPORT'); ?>"
                           href="javascript:" onclick="joms.api.eventReport('<?php echo $event->id; ?>');">
                            <svg viewBox="0 0 16 16" class="joms-icon">
                                <use xlink:href="<?php echo CRoute::getURI(); ?>#joms-icon-warning"></use>
                            </svg>
                        </a>
                    <?php } ?>

                </div>
                <p class="joms-focus__info--desktop">
                    <?php echo CActivities::truncateComplex($event->summary, $summaryLength, true); ?>
                </p>
            </div>
            <div class="joms-focus__actions__wrapper">
                <?php if ($my->id != 0) { ?>
                    <div class="joms-focus__actions--desktop">

                        <?php if( $handler->isAllowed() && !$isPastEvent && CEventHelper::showAttendButton($event)) { ?>
                            <a href="javascript:" class="joms-focus__button--add"
                               onclick="joms.api.eventResponse('<?php echo $event->id; ?>',
                                   ['<?php echo COMMUNITY_EVENT_STATUS_ATTEND; ?>', '<?php echo JText::_('COM_COMMUNITY_EVENTS_RSVP_ATTEND', true); ?>'],
                                   ['<?php echo COMMUNITY_EVENT_STATUS_MAYBE; ?>', '<?php echo JText::_('COM_COMMUNITY_EVENTS_RSVP_MAYBE_ATTEND', true); ?>'],
                                   ['<?php echo COMMUNITY_EVENT_STATUS_WONTATTEND; ?>', '<?php echo JText::_('COM_COMMUNITY_EVENTS_RSVP_NOT_ATTEND', true); ?>']);">
                                <?php if ($event->getMemberStatus($my->id) == COMMUNITY_EVENT_STATUS_ATTEND) { ?>
                                    <span class="joms-icon__attending"></span>
                                    <?php echo JText::_('COM_COMMUNITY_EVENTS_RSVP_ATTEND'); ?>
                                <?php } else if ($event->getMemberStatus($my->id) >= COMMUNITY_EVENT_STATUS_MAYBE) { ?>
                                    <span class="joms-icon__maybe-attending"></span>
                                    <?php echo JText::_('COM_COMMUNITY_EVENTS_RSVP_MAYBE_ATTEND'); ?>
                                <?php } else if ($event->getMemberStatus($my->id) >= COMMUNITY_EVENT_STATUS_WONTATTEND) { ?>
                                    <span class="joms-icon__not-attending"></span>
                                    <?php echo JText::_('COM_COMMUNITY_EVENTS_RSVP_NOT_ATTEND'); ?>
                                <?php } else { ?>
                                    <?php echo JText::_('COM_COMMUNITY_GROUPS_INVITATION_RESPONSE'); ?>
                                    <svg class="joms-icon" viewBox="0 0 16 16">
                                        <use xlink:href="<?php echo CRoute::getURI(); ?>#joms-icon-arrow-down"></use>
                                    </svg>
                                <?php } ?>
                            </a>
                        <?php }?>

                        <?php if ( !$isPastEvent && CEventHelper::seatsAvailable($event) && ($event->allowinvite || $event->isAdmin($my->id) || COwnerHelper::isCommunityAdmin()) ) { ?>
                            <a href="javascript:" class="joms-focus__button--add joms-button--secondary" onclick="joms.api.eventInvite('<?php echo $event->id; ?>', '<?php echo $isGroupEvent ? "group" : "" ?>')">
                                <?php echo JText::_($isGroupEvent ? 'COM_COMMUNITY_EVENT_INVITE_GROUP_MEMBERS' : 'COM_COMMUNITY_TAB_INVITE'); ?>
                            </a>
                        <?php } ?>

                    </div>
                <?php } ?>

                <div class="joms-focus__header__actions--desktop">

                    <a class="joms-button--viewed nolink" title="<?php echo JText::sprintf( $event->hits > 0 ? 'COM_COMMUNITY_VIDEOS_HITS_COUNT_MANY' : 'COM_COMMUNITY_VIDEOS_HITS_COUNT', $event->hits ); ?>">
                        <svg viewBox="0 0 16 16" class="joms-icon">
                            <use xlink:href="<?php echo CRoute::getURI(); ?>#joms-icon-eye"></use>
                        </svg>
                        <span><?php echo $event->hits; ?></span>
                    </a>

                    <?php if ($config->get('enablesharethis') == 1) { ?>
                        <a class="joms-button--shared" title="<?php echo JText::_('COM_COMMUNITY_SHARE_THIS'); ?>"
                           href="javascript:" onclick="joms.api.pageShare('<?php echo CRoute::getExternalURL( 'index.php?option=com_community&view=events&task=viewevent&eventid=' . $event->id ); ?>')">
                            <svg viewBox="0 0 16 16" class="joms-icon">
                                <use xlink:href="<?php echo CRoute::getURI(); ?>#joms-icon-redo"></use>
                            </svg>
                        </a>
                    <?php } ?>

                    <?php if ($enableReporting) { ?>
                        <a class="joms-button--viewed" title="<?php echo JText::_('COM_COMMUNITY_EVENTS_REPORT'); ?>"
                           href="javascript:" onclick="joms.api.eventReport('<?php echo $event->id; ?>');">
                            <svg viewBox="0 0 16 16" class="joms-icon">
                                <use xlink:href="<?php echo CRoute::getURI(); ?>#joms-icon-warning"></use>
                            </svg>
                        </a>
                    <?php } ?>

                </div>
            </div>
        </div>
    </div>
    <ul class="joms-focus__link">
        <li class="half"><?php echo ($event->ticket) ? JText::sprintf('COM_COMMUNITY_EVENTS_TICKET_STATS', $event->ticket, $eventMembersCount, ($event->ticket - $eventMembersCount)) : JText::sprintf('COM_COMMUNITY_EVENTS_UNLIMITED_SEAT'); ?></li>
        <?php if($showPhotos){ ?>
            <li class="full">
                <a href="<?php echo CRoute::_('index.php?option=com_community&view=photos&task=display&eventid=' . $event->id); ?>"><?php echo ($totalPhotos == 1) ?
                        JText::_('COM_COMMUNITY_PHOTOS_COUNT_SINGULAR') . ' <span class="joms-text--light">' . $totalPhotos . '</span>' :
                        JText::_('COM_COMMUNITY_PHOTOS_COUNT') . ' <span class="joms-text--light">' . $totalPhotos . '</span>' ; ?></a>
            </li>
        <?php } ?>
        <?php if($showVideos){ ?>
            <li class="half">
                <a href="<?php echo CRoute::_('index.php?option=com_community&view=videos&task=display&eventid=' . $event->id); ?>"><?php echo ($totalVideos == 1) ?
                        JText::_('COM_COMMUNITY_VIDEOS_COUNT') . ' <span class="joms-text--light">' . $totalVideos . '</span>' :
                        JText::_('COM_COMMUNITY_VIDEOS_COUNT_MANY') . ' <span class="joms-text--light">' . $totalVideos . '</span>' ; ?></a>
            </li>
        <?php } ?>
        <li class="half"><a href="<?php echo CRoute::_('index.php?option=com_community&view=events&task=viewguest&eventid=' . $event->id . '&type='.COMMUNITY_EVENT_STATUS_ATTEND )?>"><?php
                echo JText::sprintf((CStringHelper::isPlural($eventMembersCount)) ? 'COM_COMMUNITY_EVENTS_ATTANDEE_COUNT_MANY':'COM_COMMUNITY_EVENTS_ATTANDEE_COUNT', $eventMembersCount);
                #echo $eventMembersCount > 1 ? JText::_('COM_COMMUNITY_EVENTS_ATTANDEE_COUNT_MANY') . ' <span class="joms-text--light">' . $eventMembersCount . '</span>' : JText::_('COM_COMMUNITY_EVENTS_ATTANDEE_COUNT') . ' <span class="joms-text--light">' . $eventMembersCount . '</span>'

                ?></a></li>

        <?php if(isset($groupEventDetails) && !empty($groupEventDetails->groupName)){ ?>
            <li class="full"><?php echo JText::sprintf('COM_COMMUNITY_GROUP_EVENT_HOSTED_BY',$groupEventDetails->groupLink,$groupEventDetails->groupName,CRoute::_('index.php?option=com_community&view=profile&userid=' . $groupEventDetails->creator->id),$groupEventDetails->creator->getDisplayName()) ?></li>
        <?php }else{ ?>
            <li class="full"><?php echo JText::sprintf('COM_COMMUNITY_EVENT_HOSTED_BY',CRoute::_('index.php?option=com_community&view=profile&userid=' . $creator->id), $creator->getDisplayName()) ?></li>
        <?php } ?>

        <?php if ($isLikeEnabled) { ?>
            <li class="full liked">
                <a href="javascript:"
                   class="joms-js--like-events-<?php echo $event->id; ?><?php echo $isUserLiked > 0 ? ' liked' : ''; ?>"
                   onclick="joms.api.page<?php echo $isUserLiked > 0 ? 'Unlike' : 'Like' ?>('events', '<?php echo $event->id; ?>');"
                   data-lang-like="<?php echo JText::_('COM_COMMUNITY_LIKE'); ?>"
                   data-lang-liked="<?php echo JText::_('COM_COMMUNITY_LIKED'); ?>">
                    <svg viewBox="0 0 14 20" class="joms-icon">
                        <use xlink:href="<?php echo CRoute::getURI(); ?>#joms-icon-thumbs-up"></use>
                    </svg>
                    <span class="joms-js--lang"><?php echo ($isUserLiked > 0) ? JText::_('COM_COMMUNITY_LIKED') : JText::_('COM_COMMUNITY_LIKE'); ?></span>
                    <span class="joms-text--light"> <?php echo $totalLikes; ?></span>
                </a>
            </li>
        <?php } ?>
    </ul>
</div>
