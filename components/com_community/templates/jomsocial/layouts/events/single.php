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

$showStream = ($isEventGuest || $isMine || $isAdmin || $isCommunityAdmin || $handler->manageable());
    $config = CFactory::getConfig();

$titleLength= $config->get('header_title_length', 60);
$summaryLength = $config->get('header_summary_length', 100);

$enableReporting = false;
if ( $config->get('enablereporting') == 1 && ( $my->id > 0 || $config->get('enableguestreporting') == 1 ) ) {
    $enableReporting = true;
}

$isGroupEvent = $event->type == CEventHelper::GROUP_TYPE;

$showRequestInvitationButton = false;
if (
    ($event->permission == COMMUNITY_PRIVATE_EVENT) &&
    (!$isEventGuest) &&
    (!$waitingApproval) &&
    (!$handler->isAllowed()) &&
    ($memberStatus != COMMUNITY_EVENT_STATUS_ATTEND) &&
    ($memberStatus != COMMUNITY_EVENT_STATUS_WONTATTEND) &&
    ($memberStatus != COMMUNITY_EVENT_STATUS_MAYBE) &&
    ($memberStatus != COMMUNITY_EVENT_STATUS_BLOCKED)
) {
    $showRequestInvitationButton = true;
}

?>

<div class="joms-body">

    <!-- focus area -->
    <div class="joms-focus">
        <div class="joms-focus__cover">
            <?php  if (in_array($event->id, $featuredList)) { ?>
            <div class="joms-ribbon__wrapper">
                <span class="joms-ribbon joms-ribbon--full"><?php echo JText::_('COM_COMMUNITY_FEATURED'); ?></span>
            </div>
            <?php } ?>

            <div class="joms-focus__cover-image joms-js--cover-image">
                <img src="<?php echo $event->getCover(); ?>" style="width:100%;top:<?php echo $event->coverPostion; ?>" alt="<?php echo $event->title; ?>"
                <?php if (!$event->defaultCover && $event->coverAlbum) { ?>
                    style="width:100%;top:<?php echo $event->coverPostion; ?>;cursor:pointer"
                    onclick="joms.api.coverClick(<?php echo $event->coverAlbum ?>, <?php echo $event->coverPhoto ?>);"
                <?php } else { ?>
                    style="width:100%;top:<?php echo $event->coverPostion; ?>"
                <?php } ?>>
            </div>

            <div class="joms-focus__cover-image--mobile joms-js--cover-image-mobile"
                <?php if (!$event->defaultCover && $event->coverAlbum) { ?>
                    style="background:url(<?php echo $event->getCover(); ?>) no-repeat center center;cursor:pointer"
                    onclick="joms.api.coverClick(<?php echo $event->coverAlbum ?>, <?php echo $event->coverPhoto ?>);"
                <?php } else { ?>
                    style="background:url(<?php echo $event->getCover(); ?>) no-repeat center center"
                <?php } ?>>
            </div>

            <div class="joms-focus__header">
                <div class="joms-focus__date">
                    <span><?php echo JText::_( CEventHelper::formatStartDate($event, 'M') ); ?></span>
                    <span><?php echo JText::_( CEventHelper::formatStartDate($event, 'd') ); ?></span>
                </div>
                <div class="joms-focus__title">
                    <h2><?php echo CActivities::truncateComplex($event->title , $titleLength, true); ?></h2>
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
                        <?php }elseif($waitingApproval){?>
                            <a href="javascript:" class="joms-button--primary joms-button--small">
                                <?php echo JText::_('COM_COMMUNITY_EVENTS_PENDING_INVITATIONS'); ?>
                            </a>
                        <?php } ?>

                        <?php if ( !$isPastEvent && CEventHelper::seatsAvailable($event) && ( $event->allowinvite || $event->isAdmin($my->id) || COwnerHelper::isCommunityAdmin() ) ) { ?>
                            <a href="javascript:" class="joms-focus__button--add joms-button--secondary" onclick="joms.api.eventInvite('<?php echo $event->id; ?>', '<?php echo $isGroupEvent ? "group" : "" ?>')">
                                <?php echo JText::_($isGroupEvent ? 'COM_COMMUNITY_EVENT_INVITE_GROUP_MEMBERS' : 'COM_COMMUNITY_TAB_INVITE'); ?>
                            </a>
                        <?php } ?>

                        <?php if ($showRequestInvitationButton) { ?>
                            <a href="javascript:" class="joms-focus__button--add" onclick="joms.api.eventJoin('<?php echo $event->id; ?>');"><?php echo JText::_('COM_COMMUNITY_EVENTS_INVITE_REQUEST'); ?></a>
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
            <div class="joms-focus__actions--reposition">
                <input type="button" class="joms-button--neutral" data-ui-object="button-cancel" value="<?php echo JText::_('COM_COMMUNITY_CANCEL'); ?>"> &nbsp;
                <input type="button" class="joms-button--primary" data-ui-object="button-save" value="<?php echo JText::_('COM_COMMUNITY_SAVE'); ?>">
            </div>
            <?php if ($my->id != 0) { ?>
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
        <div class="joms-focus__actions">
            <?php if ($my->id != 0) { ?>

                <?php if( $handler->isAllowed() && !$isPastEvent ) { ?>
                    <a href="javascript:" class="joms-focus__button--add joms-button--small mobile-only-landscape--show "
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
                            <use xlink:href="<?php echo CRoute::getURI(); ?>#joms-icon-arrow-down"/>
                        </svg>
                        <?php } ?>
                    </a>
                <?php }?>

                <?php if ( !$isPastEvent && CEventHelper::seatsAvailable($event) && ( $event->allowinvite || $event->isAdmin($my->id) || COwnerHelper::isCommunityAdmin() ) ) { ?>
                    <a href="javascript:" class="joms-focus__button--add joms-button--secondary" onclick="joms.api.eventInvite('<?php echo $event->id; ?>', '<?php echo $isGroupEvent ? "group" : "" ?>')">
                        <?php echo JText::_($isGroupEvent ? 'COM_COMMUNITY_EVENT_INVITE_GROUP_MEMBERS' : 'COM_COMMUNITY_TAB_INVITE'); ?>
                    </a>
                <?php } ?>

                <a class="joms-focus__button--options" data-ui-object="joms-dropdown-button"><?php echo JText::_('COM_COMMUNITY_GROUP_OPTIONS'); ?></a>
            <?php } ?>

            <ul class="joms-dropdown">

                <?php if ($memberStatus != COMMUNITY_EVENT_STATUS_BLOCKED) { ?>
                    <?php if ($handler->showPrint()) { ?>
                    <li><a tabindex="-1" href="javascript:void(0)" onclick="window.open('<?php echo $handler->getFormattedLink('index.php?option=com_community&view=events&task=printpopup&eventid='.$event->id); ?>','', 'menubar=no,width=600,height=700,toolbar=no');"><?php echo JText::_('COM_COMMUNITY_EVENTS_PRINT');?></a></li>
                    <?php } ?>

                    <?php if( $handler->showExport() && $config->get('eventexportical') ) { ?>
                    <li><a tabindex="-1" href="<?php echo $handler->getFormattedLink('index.php?option=com_community&view=events&task=export&format=raw&eventid=' . $event->id); ?>" ><?php echo JText::_('COM_COMMUNITY_EVENTS_EXPORT_ICAL');?></a></li>
                    <?php } ?>

                    <?php if( (!$isMine) && !($waitingRespond) && (COwnerHelper::isRegisteredUser()) ) { ?>
                    <li><a tabindex="-1" href="javascript:" onclick="joms.api.eventLeave('<?php echo $event->id; ?>');"><?php echo JText::_('COM_COMMUNITY_EVENTS_IGNORE');?></a></li>
                    <?php } ?>

                    <li class="divider"></li>
                <?php } ?>

                <?php if( $isMine || COwnerHelper::isCommunityAdmin() || $isAdmin ){?>
                    <?php if(false) { //hide for now ?>
                    <li><a href="javascript:" onclick="joms.api.avatarChange('event', '<?php echo $event->id; ?>');"><?php echo JText::_('COM_COMMUNITY_CHANGE_AVATAR')?></a></li>
                    <?php } ?>
                    <li class="joms-js--menu-reposition joms-hidden--small"<?php echo $event->defaultCover ? ' style="display:none"' : '' ?>><a href="javascript:" data-propagate="1" onclick="joms.api.coverReposition('event', '<?php echo $event->id; ?>');"><?php echo JText::_('COM_COMMUNITY_REPOSITION_COVER')?></a></li>
                    <li><a href="javascript:" onclick="joms.api.coverChange('event', '<?php echo $event->id; ?>');" ><?php echo JText::_('COM_COMMUNITY_CHANGE_COVER'); ?></a></li>
                    <li class="joms-js--menu-remove-cover"<?php echo $event->defaultCover ? ' style="display:none"' : '' ?>><a href="javascript:" data-propagate="1" onclick="joms.api.coverRemove('event', <?php echo $event->id; ?>);"><?php echo JText::_('COM_COMMUNITY_REMOVE_COVER'); ?></a></li>
                <?php } ?>

                <?php
                    $createPhoto = $my->authorise('community.create', 'events.photos.' . $event->id);
                    $createVideo = $my->authorise('community.create', 'events.videos.' . $event->id);
                ?>
                <?php // Add photos
                if ($createPhoto) { ?>
                    <li>
                        <a href="javascript:" data-propagate="1" onclick="joms.api.photoUpload('', '<?php echo $event->id; ?>', 'event');"><?php echo JText::_('COM_COMMUNITY_PHOTOS_UPLOAD_PHOTOS'); ?></a>
                    </li>
                    <?php if(!$createVideo){?>
                        <li class="divider"></li>
                    <?php } ?>
                <?php } ?>

                <?php // Add videos
                if ($createVideo) { ?>
                    <li>
                        <a href="javascript:" data-propagate="1" onclick="joms.api.videoAdd('<?php echo $event->id; ?>', 'event');"><?php echo JText::_('COM_COMMUNITY_VIDEOS_ADD'); ?></a>
                    </li>
                    <li class="divider"></li>
                <?php } ?>

                <!-- event administration -->
                <?php if($isMine || $isCommunityAdmin || $isAdmin || $handler->manageable()) { ?>
                    <?php if( $isMine || $isCommunityAdmin || $isAdmin) {?>
                        <li><a tabindex="-1" href="<?php echo $handler->getFormattedLink('index.php?option=com_community&view=events&task=edit&eventid=' . $event->id );?>"><?php echo JText::_('COM_COMMUNITY_EVENTS_EDIT');?></a></li>
                    <?php } ?>

                    <?php if( ($event->permission != COMMUNITY_PRIVATE_EVENT) && ($isMine || $isCommunityAdmin || $isAdmin) ){ ?>
                        <li><a tabindex="-1" href="<?php echo $handler->getFormattedLink('index.php?option=com_community&view=events&task=create&eventid=' . $event->id );?>"><?php echo JText::_('COM_COMMUNITY_EVENTS_DUPLICATE');?></a></li>
                    <?php } ?>

                    <?php if($totalMembers = count($event->getMembers(COMMUNITY_EVENT_STATUS_BANNED))){ ?>
                        <li><a tabindex="-1" href="<?php echo $handler->getFormattedLink('index.php?option=com_community&view=events&task=viewguest&type=8&eventid=' . $event->id );?>"><?php echo JText::_('COM_COMMUNITY_EVENTS_VIEW_BANNED_MEMBERS');?></a></li>
                    <?php } ?>

                    <li class="divider"></li>

                    <?php if($my->authorise('community.delete', 'events.' . $event->id, $event) ) { ?>
                        <li><a tabindex="-1" class="event-delete" href="javascript:" onclick="joms.api.eventDelete('<?php echo $event->id; ?>');"><?php echo JText::_('COM_COMMUNITY_EVENTS_DELETE'); ?></a></li>
                    <?php } ?>

                <?php } ?>

                <?php if( ( $isMine || $isAdmin || $isCommunityAdmin) && ( $unapproved > 0 ) ) { ?>
                    <li class="divider"></li>
                    <li>
                        <a tabindex="-1" href="<?php echo $handler->getFormattedLink('index.php?option=com_community&view=events&task=viewguest&type='.COMMUNITY_EVENT_STATUS_REQUESTINVITE.'&eventid=' . $event->id);?>">
                            <?php echo JText::sprintf((CStringHelper::isPlural($unapproved)) ? 'COM_COMMUNITY_EVENTS_PENDING_INVITE_MANY'    :'COM_COMMUNITY_EVENTS_PENDING_INVITE' , $unapproved ); ?>
                        </a>
                    </li>
                <?php } ?>
            </ul>
        </div>
        <p class="joms-focus__info">
            <?php echo JHTML::_('string.truncate', $this->escape(strip_tags($event->summary)), 100); ?>
        </p>
        <ul class="joms-focus__link">

            <?php if( $handler->isAllowed() && !$isPastEvent ) { ?>
            <li class="full mobile-only--show">
                <a href="javascript:" class="joms-focus__button--add joms-button--small "
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
                        <use xlink:href="<?php echo CRoute::getURI(); ?>#joms-icon-arrow-down"/>
                    </svg>
                    <?php } ?>
                </a>
            </li>
            <?php }?>

            <?php
                //only show to non-attendees
                if($event->ticket && !$isEventGuest) { ?>
                <li class="full">
                <?php
                    if(($event->ticket - $eventMembersCount) > 0 )
                        echo JText::sprintf('COM_COMMUNITY_EVENTS_TICKET_STATS', $event->ticket, $eventMembersCount, ($event->ticket - $eventMembersCount));
                    else
                        echo JText::_("COM_COMMUNITY_EVENTS_NO_SEAT_ERROR");
                ?>
                </li>
            <?php } ?>
            <li class="full"><a href="<?php echo CRoute::_('index.php?option=com_community&view=events&task=viewguest&eventid=' . $event->id . '&type='.COMMUNITY_EVENT_STATUS_ATTEND )?>"><?php
                    echo JText::sprintf((CStringHelper::isPlural($eventMembersCount)) ? 'COM_COMMUNITY_EVENTS_ATTANDEE_COUNT_MANY':'COM_COMMUNITY_EVENTS_ATTANDEE_COUNT', $eventMembersCount);
                    ?></a></li>
            <?php if(isset($groupEventDetails) && !empty($groupEventDetails->groupName)){ ?>
            <li class="full"><?php echo JText::sprintf('COM_COMMUNITY_GROUP_EVENT_HOSTED_BY',$groupEventDetails->groupLink,$groupEventDetails->groupName,CRoute::_('index.php?option=com_community&view=profile&userid=' . $groupEventDetails->creator->id),$groupEventDetails->creator->getDisplayName()) ?></li>
            <?php }else{ ?>
                <li class="full"><?php echo JText::sprintf('COM_COMMUNITY_EVENT_HOSTED_BY',CRoute::_('index.php?option=com_community&view=profile&userid=' . $creator->id), $creator->getDisplayName()) ?></li>
            <?php } ?>

            <li class="full">
                <a href="javascript:" data-ui-object="joms-dropdown-button">
                    <?php echo JTEXT::_('COM_COMMUNITY_MORE'); ?>
                    <svg viewBox="0 0 14 20" class="joms-icon">
                        <use xlink:href="<?php echo CRoute::getURI(); ?>#joms-icon-arrow-down"></use>
                    </svg>
                </a>
                <ul class="joms-dropdown more-button">
                    <?php if($showPhotos){ ?>
                        <li class="full">
                            <a href="<?php echo CRoute::_('index.php?option=com_community&view=photos&task=display&eventid=' . $event->id); ?>"><?php echo ($totalPhotos == 1) ?
                                    JText::_('COM_COMMUNITY_PHOTOS_COUNT_SINGULAR') . ' <span class="joms-text--light">' . $totalPhotos . '</span>' :
                                    JText::_('COM_COMMUNITY_PHOTOS_COUNT') . ' <span class="joms-text--light">' . $totalPhotos . '</span>' ; ?></a>
                        </li>
                    <?php } ?>
                    <?php if($showVideos){ ?>
                        <li class="full">
                            <a href="<?php echo CRoute::_('index.php?option=com_community&view=videos&task=display&eventid=' . $event->id); ?>"><?php echo ($totalVideos == 1) ?
                                    JText::_('COM_COMMUNITY_VIDEOS_COUNT') . ' <span class="joms-text--light">' . $totalVideos . '</span>' :
                                    JText::_('COM_COMMUNITY_VIDEOS_COUNT_MANY') . ' <span class="joms-text--light">' . $totalVideos . '</span>' ; ?></a>
                        </li>
                    <?php } ?>
                </ul>
            </li>


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


    <div class="joms-sidebar">
        <?php if($my->authorise('community.view','events.'.$event->id)){ ?>
        <div class="joms-module__wrapper"><?php $this->renderModules( 'js_side_top' ); ?></div>
        <div class="joms-module__wrapper--stacked"><?php $this->renderModules('js_side_top_stacked'); ?></div>
        <div class="joms-module__wrapper"><?php $this->renderModules( 'js_side_bottom' ); ?></div>
        <div class="joms-module__wrapper--stacked"><?php $this->renderModules('js_side_bottom_stacked'); ?></div>
        <div class="joms-module__wrapper"><?php $this->renderModules( 'js_events_side_top' ); ?></div>
        <div class="joms-module__wrapper--stacked"><?php $this->renderModules('js_events_side_top_stacked'); ?></div>

        <div class="joms-module__wrapper">
            <div class="joms-tab__bar">
                <a href="#joms-event--attend" class="active"><?php echo JText::_('COM_COMMUNITY_EVENTS_RSVP_ATTEND'); ?></a>
                <?php if ($maybeCount > 0) { ?>
                <a href="#joms-event--maybe"><?php echo JText::_('COM_COMMUNITY_EVENTS_RSVP_MAYBE_ATTEND'); ?></span></a>
                <?php } ?>
                <?php if ($wontAttendCount > 0) { ?>
                <a href="#joms-event--not-attend"><?php echo JText::_('COM_COMMUNITY_EVENTS_RSVP_NOT_ATTEND'); ?></span></a>
                <?php } ?>
                <?php if (($isMine || $isAdmin || $isCommunityAdmin) && count($pendingRequestGuests) > 0) { ?>
                <a href="#joms-event--waiting">
                    <?php echo JText::sprintf('COM_COMMUNITY_EVENTS_INVITATION_REQUEST',count($pendingRequestGuests)); ?>
                </a>
                <?php } ?>

            </div>

            <?php if(!$showRequestInvitationButton){ //hide attendees if this is a private event and viewer is not a member of the event?>
            <div id="joms-event--attend" class="joms-tab__content">
                <?php if ($eventMembersCount > 0) { ?>
                <ul class="joms-list--thumbnail">
                    <?php foreach($eventMembers as $member) { ?>
                    <li class="joms-list__item <?php echo CUserHelper::onlineIndicator($member); ?>">
                        <a href="<?php echo CUrlHelper::userLink($member->id); ?>" class="joms-avatar">
                            <img src="<?php echo $member->getThumbAvatar(); ?>" alt="<?php echo $member->getDisplayName(); ?>" data-author="<?php echo $member->id; ?>" />
                        </a>
                    </li>
                    <?php } ?>
                </ul>
                <div class="joms-gap"></div>
                <a class="joms-button--link" href="<?php echo $handler->getFormattedLink('index.php?option=com_community&view=events&task=viewguest&eventid=' . $event->id . '&type='.COMMUNITY_EVENT_STATUS_ATTEND ); ?>">
                    <?php echo JText::_('COM_COMMUNITY_VIEW_ALL') . ' (' . $eventMembersCount . ')'; ?>
                </a>

                <?php } else {
                    echo JText::_('COM_COMMUNITY_EVENTS_NO_USER_ATTENDING_MESSAGE');
                } ?>
            </div>
            <?php } ?>

            <?php if ($maybeCount > 0) { ?>
            <div id="joms-event--maybe" class="joms-tab__content" style="display:none;">
                <ul class="joms-list--thumbnail">
                    <?php foreach($maybeList as $member) { ?>
                    <li class="joms-list__item <?php echo CUserHelper::onlineIndicator($member); ?>">
                        <a href="<?php echo CUrlHelper::userLink($member->id); ?>" class="joms-avatar">
                            <img src="<?php echo $member->getThumbAvatar(); ?>" alt="<?php echo $member->getDisplayName(); ?>" data-author="<?php echo $member->id; ?>" />
                        </a>
                    </li>
                    <?php } ?>
                </ul>
                <div class="joms-gap"></div>
                <a class="joms-button--link" href="<?php echo $handler->getFormattedLink('index.php?option=com_community&view=events&task=viewguest&eventid=' . $event->id . '&type='.COMMUNITY_EVENT_STATUS_MAYBE ); ?>">
                    <?php echo JText::_('COM_COMMUNITY_VIEW_ALL') . ' (' . $maybeCount . ')'; ?>
                </a>
            </div>
            <?php } ?>

            <?php if ($wontAttendCount > 0) { ?>
            <div id="joms-event--not-attend" class="joms-tab__content" style="display:none;">
                <ul class="joms-list--thumbnail">
                    <?php foreach($wontAttendList as $member) { ?>
                    <li class="joms-list__item <?php echo CUserHelper::onlineIndicator($member); ?>">
                        <a href="<?php echo CUrlHelper::userLink($member->id); ?>" class="joms-avatar">
                            <img src="<?php echo $member->getThumbAvatar(); ?>" alt="<?php echo $member->getDisplayName(); ?>" data-author="<?php echo $member->id; ?>" />
                        </a>
                    </li>
                    <?php } ?>
                </ul>
                <div class="joms-gap"></div>
                <a class="joms-button--link" href="<?php echo $handler->getFormattedLink('index.php?option=com_community&view=events&task=viewguest&eventid=' . $event->id . '&type='.COMMUNITY_EVENT_STATUS_WONTATTEND ); ?>">
                    <?php echo JText::_('COM_COMMUNITY_VIEW_ALL') . ' (' . $wontAttendCount . ')'; ?>
                </a>
            </div>
            <?php } ?>

            <?php if (($isMine || $isAdmin || $isCommunityAdmin) && count($pendingRequestGuests) > 0) { ?>
            <div id="joms-event--waiting" class="joms-tab__content" style="display:none;">
                <div class="joms-module__body">
                    <ul class="joms-list--thumbnail">
                        <?php  foreach($pendingRequestGuests as $member) { ?>
                            <li class="joms-list__item">
                                <a href="<?php echo CUrlHelper::userLink($member->id); ?>" class="joms-avatar">
                                    <img src="<?php echo $member->getThumbAvatar(); ?>" alt="<?php echo $member->getDisplayName(); ?>" />
                                </a>
                            </li>
                        <?php } ?>
                    </ul>
                </div>

                <div class="joms-gap"></div>
                <a class="joms-button--link" href="<?php echo $handler->getFormattedLink('index.php?option=com_community&view=events&task=viewguest&type='.COMMUNITY_EVENT_STATUS_REQUESTINVITE.'&eventid=' . $event->id);?>">
                    <?php echo JText::sprintf((CStringHelper::isPlural($unapproved)) ? 'COM_COMMUNITY_EVENTS_PENDING_INVITE_MANY'    :'COM_COMMUNITY_EVENTS_PENDING_INVITE' , $unapproved ); ?>
                </a>

            </div>
            <?php } ?>
        </div>

        <!-- begin: map -->
        <?php if ( $config->get('eventshowmap') && ( $handler->isAllowed() || $event->permission != COMMUNITY_PRIVATE_EVENT ) || $eventSeries && $seriesCount > 1 ) { ?>


            <div class="joms-module__wrapper">
                <div class="joms-tab__bar">
                    <a class="active" href="#joms-app--event-map"><?php echo JText::_('COM_COMMUNITY_MAP_LOCATION');?></a>
                    <?php if ($eventSeries && $seriesCount > 1) { ?>
                    <a href="#joms-app--event-series"><?php echo JText::_('COM_COMMUNITY_EVENTS_SERIES');?></a>
                    <?php } ?>
                </div>
                <div id="joms-app--event-map" class="joms-tab__content">
                    <?php if ( CMapping::validateAddress($event->location) ) { ?>
                    <div id="community-event-map" >
                        <div class="app-box-content event-description">
                            <!-- begin: dynamic map -->
                            <?php // echo CMapping::drawMap('event-map', $event->location); ?>
                            <div id="event-map" style="height:210px;width:100%;">
                                <?php echo JText::_('COM_COMMUNITY_MAPS_LOADING'); ?>
                            </div>
                            <!-- end: dynamic map -->
                            <div class="joms-text--small"><?php echo CMapping::getFormatedAdd($event->location); ?></div>
                        </div>
                        <div class="joms-module__footer">
                            <a href="http://maps.google.com/?q=<?php echo urlencode($event->location); ?>" target="_blank" class="joms-button--link"><?php echo JText::_('COM_COMMUNITY_EVENTS_FULL_MAP'); ?></a>
                        </div>
                    </div>
                    <script>
                        (function( w ) {
                            w.joms_queue || (w.joms_queue = []);
                            w.joms_queue.push(function() {
                                var address, container, geocoder;

                                address = '<?php echo addslashes($event->location); ?>',
                                container = joms.jQuery('#event-map');

                                // Delay execution.
                                setTimeout(function() {
                                    joms.util.map(function() {
                                        geocoder = new google.maps.Geocoder();
                                        geocoder.geocode({ address: address }, function( results, status ) {
                                            var location, center, mapOptions, map, marker;

                                            if (status != google.maps.GeocoderStatus.OK) {
                                                container.html( 'Geocode was not successful for the following reason: ' + status );
                                                return;
                                            }

                                            location = results[0].geometry.location;
                                            center = new google.maps.LatLng( location.lat(), location.lng() );

                                            mapOptions = {
                                                zoom: 14,
                                                center: center,
                                                mapTypeId: google.maps.MapTypeId.ROADMAP
                                            };

                                            map = new google.maps.Map( container[0], mapOptions );

                                            marker = new google.maps.Marker({
                                                map: map,
                                                position: center
                                            });

                                        });
                                    });
                                }, 2000 );

                            });
                        })( window );
                    </script>
                    <?php } ?>
                </div>
                <?php if ($eventSeries && $seriesCount > 1) { ?>
                <div id="joms-app--event-series" class="joms-tab__content" style="display:none;">
                    <div id="community-event-series">

                        <?php
                        $grouplink = '';
                        if ($event->contentid > 0) {
                            $grouplink = '&groupid=' . $event->contentid;
                        }

                        foreach( $eventSeries as $series ) {
                        ?>
                        <div class="joms-stream__header no-gap">
                            <div class="joms-avatar--stream">
                                <div class="joms-media__calendar">

                                <?php
                                    $datestr = strtotime($series->getStartDate());
                                    $day = date('d', $datestr);
                                    $month = date('M', $datestr);
                                    $year = date('y', $datestr);
                                ?>
                                <span class="month"><?php echo JText::_( CEventHelper::formatStartDate($series, 'M') ); ?></span>
                                <span class="date"><?php echo JText::_( CEventHelper::formatStartDate($series, 'd') ); ?></span>

                                </div>
                            </div>
                            <div class="joms-popover__content">
                                <p class="reset-gap">
                                    <svg class="joms-icon" viewBox="0 0 14 20">
                                        <use xlink:href="<?php echo CRoute::getURI(); ?>#joms-icon-calendar"/>
                                    </svg>
                                <?php echo JText::_( CEventHelper::formatStartDate($series, $config->get('eventdateformat')) ); ?></p>
                                <a href="<?php echo CRoute::_('index.php?option=com_community&view=events&task=viewguest&eventid=' . $series->id . $grouplink);?>">
                                    <svg class="joms-icon" viewBox="0 0 14 20">
                                        <use xlink:href="<?php echo CRoute::getURI(); ?>#joms-icon-users"/>
                                    </svg>
                                <?php echo JText::sprintf((CStringHelper::isPlural($series->confirmedcount)) ? 'COM_COMMUNITY_EVENTS_ATTANDEE_COUNT_MANY':'COM_COMMUNITY_EVENTS_ATTANDEE_COUNT', $series->confirmedcount);?></a>
                            </div>
                        </div>
                        <?php } ?>
                        <div class="joms-gap"></div>
                        <a href="<?php echo CRoute::_('index.php?option=com_community&view=events' . $grouplink . '&parent=' . $event->parent);?>" class="joms-button--link"><?php echo JText::_('COM_COMMUNITY_EVENTS_VIEW_SERIES'). '(' . $seriesCount . ')';?></a>
                    </div>

                </div>
                <?php } ?>
            </div>

        <?php } ?>
        <!-- end: map -->

        <div class="joms-module__wrapper">

            <div class="joms-tab__bar">
                <?php if ($showPhotos) { ?>
                    <?php if ($albums) { ?>
                        <a href="#joms-event--photos" class="active"><?php echo JText::_('COM_COMMUNITY_PHOTOS_PHOTO_ALBUMS'); ?></a>
                    <?php } ?>
                <?php } ?>
                <?php if ($showVideos) { ?>
                    <?php if ($videos) { ?>
                        <a href="#joms-event--videos"><?php echo JText::_('COM_COMMUNITY_VIDEOS'); ?></a>
                    <?php } ?>
                <?php } ?>

            </div>

            <?php if ($showPhotos) { ?>
            <?php if ($albums) { ?>
                <div id="joms-event--photos" class="joms-tab__content">
                    <ul class="joms-list--photos">
                        <?php foreach ($albums as $album) { ?>
                            <li class="joms-list__item">
                                <a href="<?php echo CRoute::_(
                                    'index.php?option=com_community&view=photos&task=album&albumid=' . $album->id . '&eventid=' . $event->id
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
                            'index.php?option=com_community&view=photos&task=display&eventid=' . $event->id
                        ); ?>">
                            <?php echo JText::_('COM_COMMUNITY_VIEW_ALL_ALBUMS') . ' (' . count($albums) . ')'; ?>
                        </a>
                    </div>
                </div>
            <?php } ?>
            <?php } ?>

            <?php if ($showVideos) { ?>
            <?php if ($videos) { ?>

                <div id="joms-event--videos" class="joms-tab__content" style="display:none;">
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
                            'index.php?option=com_community&view=videos&eventid=' . $event->id
                        ); ?>">
                            <?php echo JText::_('COM_COMMUNITY_VIDEOS_ALL') . ' (' . $totalVideos . ')'; ?>
                        </a>
                    </div>
                </div>

            <?php } ?>
            <?php } ?>

        </div>

        <div class="joms-module__wrapper"><?php $this->renderModules( 'js_events_side_bottom' ); ?></div>
        <div class="joms-module__wrapper--stacked"><?php $this->renderModules('js_events_side_bottom_stacked'); ?></div>
        <?php } ?>
    </div>

    <div class="joms-main">

        <div class="joms-middlezone">
        <!-- Event Approval -->
        <?php if( $waitingApproval == COMMUNITY_EVENT_STATUS_REQUESTINVITE) {?>
        <div class="joms-alert--info">
            <span><?php echo JText::_('COM_COMMUNITY_EVENTS_APPROVEL_WAITING'); ?></span>
        </div>
        <?php }?>

        <?php if( $isInvited && CEventHelper::seatsAvailable($event)){ ?>
        <div id="events-invite-<?php echo $event->id; ?>" class="joms-page joms-js--invitation-notice-event-<?php echo $event->id; ?>">
            <div class="cInvite-Content">
                <div class="cInvite-Message">
                    <?php echo JText::sprintf( 'COM_COMMUNITY_EVENTS_YOUR_INVITED', $join ); $test = 1; ?>
                </div>
                <?php if ($friendsCount) { ?>
                <div class="cInvite-Relations">
                    <?php echo JText::sprintf( (CStringHelper::isPlural($friendsCount)) ? 'COM_COMMUNITY_EVENTS_FRIEND_MANY' : 'COM_COMMUNITY_EVENTS_FRIEND', $friendsCount ); ?>
                </div>
                <?php } ?>
                <div class="cInvite-Actions">
                    <?php echo JText::_( 'COM_COMMUNITY_EVENTS_RSVP_NOTIFICATION' ) . JText::_('COM_COMMUNITY_OR'); ?>
                    <a href="javascript:void(0);" onclick="joms.api.invitationReject('event', '<?php echo $event->id; ?>');">
                        <?php echo JText::_('COM_COMMUNITY_EVENTS_REJECT'); ?>
                    </a>
                </div>
            </div>
        </div>
        <div class="joms-gap"></div>
        <?php } ?>

        <div class="joms-tab__bar">
            <?php if( $showStream ) { ?>
                <a href="#joms-event--stream" class="<?php echo ($config->get('default_event_tab') == 0) ? 'active' : ''; ?>">
                    <?php echo JText::_('COM_COMMUNITY_FRONTPAGE_RECENT_ACTIVITIES');?>
                </a>
            <?php } ?>

                <a href="#joms-event--details" class="<?php echo (!$showStream || $config->get('default_event_tab') == 1) ? 'active' : '' ; ?>">
                    <?php echo JText::_('COM_COMMUNITY_EVENTS_DETAIL');?>
                </a>

        </div>

        <div class="joms-gap"></div>

            <?php if( $showStream ) { ?>
        <div id="joms-event--stream" class="joms-tab__content" style="<?php echo (!$showStream || $config->get('default_event_tab') == 1) ? 'display:none;' : '' ; ?>">
             <?php
                $status->render();
                echo $streamHTML;
            ?>
        </div>
            <?php } ?>

        <div id="joms-event--details" class="joms-tab__content" style="<?php echo ($showStream && $config->get('default_event_tab') == 0) ? 'display:none;' : '' ; ?>">

            <ul class="joms-list__row">
                <?php if( !CStringHelper::isHTML($event->description) ) {
                    $event->description = CStringHelper::nl2br($event->description);
                }?>
                <li>
                    <?php echo $event->description; ?>
                    <?php
                    //find out if there is any url here, if there is, run it via embedly when enabled
                    $params = new CParameter($event->params);
                    if($params->get('url') && $config->get('enable_embedly')){
                        ?>
                        <a href="<?php echo $params->get('url'); ?>" class="embedly-card" data-card-controls="0" data-card-recommend="0" data-card-theme="<?php echo $config->get('enable_embedly_card_template'); ?>" data-card-align="<?php echo $config->get('enable_embedly_card_position') ?>"><?php echo JText::_('COM_COMMUNITY_EMBEDLY_LOADING');?></a>
                    <?php } ?>
                </li>

                <li>
                    <h5 class="joms-text--light"><?php echo JText::_('COM_COMMUNITY_EVENTS_CATEGORY'); ?></h5>
                    <span><a href="<?php echo CRoute::_('index.php?option=com_community&view=events&categoryid=' . $event->catid);?>"><?php echo JText::_( $event->getCategoryName() ); ?></a></span>
                </li>
                <li>
                    <h5 class="joms-text--light"><?php echo JText::_('COM_COMMUNITY_EVENTS_TIME')?></h5>
                    <span>
                    <?php echo ($allday) ? JText::sprintf('COM_COMMUNITY_EVENTS_ALLDAY_DATE',$event->startdateHTML) : JText::sprintf('COM_COMMUNITY_EVENTS_DURATION',$event->startdateHTML,$event->enddateHTML); ?>
                        <?php if( $config->get('eventshowtimezone') ) {
                            echo $timezoneName; ?>
                        <?php } ?>
                    </span>
                </li>
                <li>
                    <h5 class="joms-text--light"><?php echo JText::_('COM_COMMUNITY_EVENTS_LOCATION');?></h5>
                    <span><a href="http://maps.google.com/?q=<?php echo urlencode($event->location); ?>" target="_blank"><?php echo $event->location; ?></a></span>
                </li>
                <li>
                    <h5 class="joms-text--light"><?php echo JText::_('COM_COMMUNITY_EVENTS_ADMINS')?></h5>
                    <span><?php echo $adminsList;?></span>
                </li>
                <?php if ($event->isRecurring()) { ?>
                <li>
                    <h5 class="joms-text--light"><?php echo JText::_('COM_COMMUNITY_EVENTS_OCCURENCE');?></h5>
                    <span><?php echo JText::_('COM_COMMUNITY_EVENTS_REPEAT_' . strtoupper($event->repeat)); ?></span>
                </li>
                <?php }?>
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

    joms.constants.eventid = <?php echo $event->id; ?>;
    joms.constants.videocreatortype = '<?php echo VIDEO_EVENT_TYPE ?>';

    joms.constants.conf.enablephotos = <?php echo (isset($showPhotos) && $showPhotos == 1 && (( $isAdmin && $photoPermission == 1 ) || ($isEventGuest && $photoPermission == 2) ) ) ? 1 : 0 ; ?>;
    joms.constants.conf.enablevideos = <?php echo (isset($showVideos) && $showVideos == 1 && (( $isAdmin && $videoPermission == 1 ) || ($isEventGuest && $videoPermission == 2) ) ) ? 1 : 0 ; ?>;

    joms.constants.conf.enablevideosupload  = <?php echo $config->get('enablevideosupload');?>;

</script>
