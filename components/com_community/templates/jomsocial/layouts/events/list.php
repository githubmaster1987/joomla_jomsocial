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

<script>
    function joms_change_filter_type(value) {
        var urls = {
            <?php
                $task = JFactory::getApplication()->input->getCmd('task');
                $isMyEventFilter = ($task == 'myevents' || ($task == 'pastevents' && JFactory::getApplication()->input->get('userid')) );
                if($task == 'display' || $task == 'myevents'){
            ?>
            all: '<?php echo html_entity_decode(CRoute::_("index.php?option=com_community&view=events&task=displaysort=upcoming")); ?>',
            mine: '<?php echo html_entity_decode(CRoute::_("index.php?option=com_community&view=events&task=myevents")); ?>'
            <?php
                }else{
            ?>
            all: '<?php echo html_entity_decode(CRoute::_("index.php?option=com_community&view=events&task=pastevents&sort=upcoming")); ?>',
            mine: '<?php echo html_entity_decode(CRoute::_("index.php?option=com_community&view=events&task=pastevents&userid=" . CFactory::getUser()->id)); ?>'
            <?php
                }
            ?>

        };

        window.location = urls[value] || '?';
    }

    function joms_change_filter( value, type ) {
        var url;

        // Category selector.
        if ( type === 'category' ) {
            if ( value ) {
                url = '<?php echo html_entity_decode(CRoute::_("index.php?option=com_community&view=events&task=display&categoryid=__cat__")); ?>';
                url = url.replace( '__cat__', value );
            } else {
                url = '<?php echo html_entity_decode(CRoute::_("index.php?option=com_community&view=events&task=display")); ?>';
            }

            window.location = url;
            return;
        }

        // Filter selector.
        // @todo
    }
</script>

<?php
// hide this is this is a search page
    if(!$isSearch && $task == 'display' && !$groupid){
?>
    <div class="joms-sortings">
        <select class="joms-select reset-gap" onchange="joms_change_filter(this.value, 'category');">
            <option value=""><?php echo JText::_('COM_COMMUNITY_EVENTS_ALL'); ?></option>
            <option value="featured_only" <?php echo ($categoryId === 'featured_only') ? 'selected' : '' ;  ?>><?php echo JText::_('COM_COMMUNITY_FEATURED'); ?></option>
            <?php foreach($availableCategories as $category){ ?>
                <option value="<?php echo $category->id ?>"<?php if ($categoryId == $category->id) echo ' selected'; ?>><?php echo JText::_( $this->escape(trim($category->name)) ); ?></option>
            <?php } ?>
        </select>
    </div>
    <div class="joms-gap"></div>
<?php } ?>

<?php if ($events) { ?>
    <ul class="joms-list--card">

        <?php

            $my = CFactory::getUser();
            $now = new JDate();


            for ($i = 0; $i < count($events); $i++) {
                $event =& $events[$i];

                $isMine = $my->id == $event->creator;
                $isAdmin = $event->isAdmin($my->id);
                $user = CFactory::getUser($event->creator);
                $isPastEvent = $event->getEndDate(false)->toSql() < $now->toSql(true) ? true : false;
                $handler	= CEventHelper::getHandler( $event );
                $memberStatus = $event->getUserStatus($my->id);
                $isEventGuest	= $event->isMember( $my->id );
                $waitingApproval = $event->isPendingApproval($my->id);
                $isGroupEvent = $event->type == CEventHelper::GROUP_TYPE;

                //check if this event is group event
                $isGroup = false;
                $privateGroup = false;
                if($event->type == 'group' && $event->contentid){
                    $isGroup = true;
                    $groupModel = CFactory::getModel('groups');
                    $groupName = $groupModel->getGroupName($event->contentid);
                    $groupLink = CRoute::_( 'index.php?option=com_community&view=groups&task=viewgroup&groupid='.$event->contentid);
                    $privateGroup = $groupModel->needsApproval($event->contentid);
                }

                // Check if "Feature this" button should be added or not.
                $addFeaturedButton = false;
                $isFeatured = false;
                if ($isCommunityAdmin && $showFeatured) {
                    $addFeaturedButton = true;
                    if (in_array($event->id, $featuredList)) {
                        $isFeatured = true;
                    }
                }

                // Check if "Invite friends" and "Settings" buttons should be added or not.
                $canInvite = false;
                $canEdit = false;

                if ($isMine || $isAdmin || $isCommunityAdmin) {
                    if (!$isPastEvent && CEventHelper::seatsAvailable($event)) {
                        $canInvite = true;
                    }
                    $canEdit = true;
                }

                $showRequestInvitationButton = false;
                if (
                    ($event->permission == COMMUNITY_PRIVATE_EVENT) &&
                    (!$isEventGuest) &&
                    (!$waitingApproval) &&
                    (!$handler->isAllowed()) &&
                    ($memberStatus != COMMUNITY_EVENT_STATUS_ATTEND) &&
                    ($memberStatus != COMMUNITY_EVENT_STATUS_WONTATTEND) &&
                    ($memberStatus != COMMUNITY_EVENT_STATUS_MAYBE) &&
                    ($memberStatus != COMMUNITY_EVENT_STATUS_BLOCKED) &&
                    ($memberStatus != COMMUNITY_EVENT_STATUS_BANNED)
                ) {
                    $showRequestInvitationButton = true;
                }

                ?>

                <li class="joms-list__item <?php echo $event->permission == 1 ? 'event-private' : 'event-public' ?>">
                    <div class="joms-list__cover">
                        <a href="<?php echo $event->getLink(); ?>" >
                            <?php  if (in_array($event->id, $featuredList)) { ?>
                            <div class="joms-ribbon__wrapper">
                                <span class="joms-ribbon"><?php echo JText::_('COM_COMMUNITY_FEATURED'); ?></span>
                            </div>
                            <?php } ?>
                            <div class="joms-list__cover-image" style="background-image:url(<?php echo $event->getCover(); ?>);"></div>
                        </a>

                        <div class="joms-focus__date cover">
                            <span><?php echo JText::_( CEventHelper::formatStartDate($event, 'M') ); ?></span>
                            <span><?php echo JText::_( CEventHelper::formatStartDate($event, 'd') ); ?></span>
                        </div>



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
                                            <li><a href="javascript:"
                                                   onclick="joms.api.eventRemoveFeatured('<?php echo $event->id; ?>');"><?php echo JText::_(
                                                        'COM_COMMUNITY_REMOVE_FEATURED'
                                                    ); ?></a></li>
                                        <?php } else { ?>
                                            <li><a href="javascript:"
                                                   onclick="joms.api.eventAddFeatured('<?php echo $event->id; ?>');"><?php echo JText::_(
                                                        'COM_COMMUNITY_EVENT_FEATURE'
                                                    ); ?></a></li>
                                        <?php } ?>
                                    <?php } ?>
                                    <?php if ($canInvite && CEventHelper::seatsAvailable($event)) { ?>
                                        <li><a href="javascript:"
                                               onclick="joms.api.eventInvite('<?php echo $event->id; ?>');"><?php echo JText::_(
                                                    'COM_COMMUNITY_INVITE_FRIENDS'
                                                ); ?></a></li>
                                    <?php } ?>
                                    <?php if ($canEdit) { ?>
                                        <li><a href="<?php echo CRoute::_(
                                                'index.php?option=com_community&view=events&task=edit&eventid=' . $event->id
                                            ); ?>"><?php echo JText::_('COM_COMMUNITY_SETTINGS'); ?></a></li>
                                    <?php } ?>
                                </ul>
                            </div>
                        <?php } ?>
                    </div>

                    <div class="joms-list__content">
                        <?php if($isGroup){ ?>
                            <h4>
                                <a href="<?php echo $event->getLink(); ?>">
                                    <?php echo $this->escape(
                                        $event->title
                                    ); ?>
                                </a>
                            </h4>
                            <svg class="joms-icon" viewBox="0 0 16 16">
                                <use xlink:href="<?php echo CRoute::getURI(); ?>#joms-icon-users"></use>
                            </svg>
                            <a href="<?php echo $groupLink; ?>">
                                <?php echo $this->escape($groupName); ?>
                            </a>
                        <?php } else { ?>
                            <h4 class="joms-list__title">
                                <a href="<?php echo $event->getLink(); ?>">
                                    <?php echo $this->escape(
                                        $event->title
                                    ); ?>
                                </a>
                            </h4>
                        <?php } ?>

                        <ul class="joms-list--table">
                            <li>
                                <svg class="joms-icon" viewBox="0 0 16 16">
                                    <use xlink:href="<?php echo CRoute::getURI(); ?>#joms-icon-location"></use>
                                </svg>
                                <a href="http://maps.google.com/?q=<?php echo urlencode($event->location); ?>" target="_blank"><?php echo $event->location; ?></a>
                            </li>
                            <li>
                                <svg class="joms-icon" viewBox="0 0 16 16">
                                    <use xlink:href="<?php echo CRoute::getURI(); ?>#joms-icon-user"></use>
                                </svg>
                                <a href="<?php echo $event->getGuestLink(
                                    COMMUNITY_EVENT_STATUS_ATTEND
                                ); ?>"><?php $membercount = $event->getMembersCount(COMMUNITY_EVENT_STATUS_ATTEND);
                                        echo JText::sprintf(
                                            (!CStringHelper::isSingular(
                                                $membercount
                                            )) ? 'COM_COMMUNITY_EVENTS_ATTANDEE_COUNT_MANY_NUMBER' : 'COM_COMMUNITY_EVENTS_ATTANDEE_COUNT_NUMBER',
                                            $membercount
                                        ); ?></a>
                            </li>
                            <?php if($event->showPhotos){ ?>
                            <li>
                                <svg class="joms-icon" viewBox="0 0 16 16">
                                    <use xlink:href="<?php echo CRoute::getURI(); ?>#joms-icon-images"></use>
                                </svg>
                                <a href="<?php echo CRoute::_('index.php?option=com_community&view=photos&task=display&eventid=' . $event->id); ?>">
                                    <?php echo $event->totalPhotos; ?> <?php echo ($event->totalPhotos == 1) ?
                                        JText::_('COM_COMMUNITY_PHOTOS_COUNT_SINGULAR') : JText::_('COM_COMMUNITY_PHOTOS'); ?>
                                </a>
                            </li>
                            <?php } ?>

                            <?php if($event->showVideos){ ?>
                            <li>
                                <svg class="joms-icon" viewBox="0 0 16 16">
                                    <use xlink:href="<?php echo CRoute::getURI(); ?>#joms-icon-film"></use>
                                </svg>
                                <a href="<?php echo CRoute::_('index.php?option=com_community&view=videos&task=display&eventid=' . $event->id); ?>"><?php echo $event->totalVideos; ?>
                                    <?php echo ($event->totalVideos == 1) ?
                                        JText::_('COM_COMMUNITY_VIDEOS_COUNT') : JText::_('COM_COMMUNITY_VIDEOS_COUNT_MANY'); ?>
                                </a>
                            </li>
                            <?php } ?>
                        </ul>

                        <div class="joms-gap--small"></div>

                        <div class="joms-list__body">
                            <?php if( $handler->isAllowed() && !$isPastEvent && CEventHelper::showAttendButton($event)) { ?>
                                <a href="javascript:" class="joms-button--primary joms-button--small"
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
                            <?php }
                            ?>

                            <?php if ( !$isPastEvent && CEventHelper::seatsAvailable($event) && ( $event->allowinvite || $event->isAdmin($my->id) || $canInvite ) ) { ?>
                                <a href="javascript:" class="joms-button--secondary joms-button--small" onclick="joms.api.eventInvite('<?php echo $event->id; ?>', '<?php echo $isGroupEvent ? "group" : "" ?>')">
                                    <?php echo JText::_($isGroupEvent ? 'COM_COMMUNITY_EVENT_INVITE_GROUP_MEMBERS' : 'COM_COMMUNITY_TAB_INVITE'); ?>
                                </a>
                            <?php } ?>

                            <?php if ($showRequestInvitationButton) { ?>
                                <a href="javascript:" class="joms-focus__button--add joms-button--small" onclick="joms.api.eventJoin('<?php echo $event->id; ?>');"><?php echo JText::_('COM_COMMUNITY_EVENTS_INVITE_REQUEST'); ?></a>
                            <?php } else if ($waitingApproval) { ?>
                                <span class="joms-alert--info" style="margin:0;width:auto;"><?php echo JText::_('COM_COMMUNITY_PENDING_APPROVAL'); ?></span>
                            <?php } ?>
                        </div>
                        <div class="joms-gap--small"></div>
                        <ul class="joms-list--inline">
                            <li>
                                <svg class="joms-icon" viewBox="0 0 16 16">
                                    <use xlink:href="<?php echo CRoute::getURI(); ?>#joms-icon-user"></use>
                                </svg>
                                <a href="<?php echo $event->getGuestLink(
                                    COMMUNITY_EVENT_STATUS_ATTEND
                                ); ?>"><?php $membercount = $event->getMembersCount(COMMUNITY_EVENT_STATUS_ATTEND);
                                        echo JText::sprintf(
                                            (!CStringHelper::isSingular(
                                                $membercount
                                            )) ? 'COM_COMMUNITY_EVENTS_ATTANDEE_COUNT_MANY_NUMBER' : 'COM_COMMUNITY_EVENTS_ATTANDEE_COUNT_NUMBER',
                                            $membercount
                                        ); ?></a>
                            </li>
                            <?php
                            if($event->permission == 1 || (isset($privateGroup) && $privateGroup == 1) ){ ?>
                                <li>
                                    <svg class="joms-icon" viewBox="0 0 16 16">
                                        <use xlink:href="<?php echo CRoute::getURI(); ?>#joms-icon-lock"></use>
                                    </svg>
                                    <?php echo JText::_('COM_COMMUNITY_EVENT_PRIVATE'); ?>
                                </li>
                            <?php } else { ?>
                                <li>
                                    <svg class="joms-icon" viewBox="0 0 16 16">
                                        <use xlink:href="<?php echo CRoute::getURI(); ?>#joms-icon-lock"></use>
                                    </svg>
                                    <?php echo JText::_('COM_COMMUNITY_GROUPS_OPEN'); ?>
                                </li>
                            <?php } ?>
                        </ul>
                    </div>
                    <div class="joms-list__footer joms-padding">
                        <div class="<?php echo CUserHelper::onlineIndicator($user); ?>">
                            <a class="joms-avatar" href="<?php echo CUrlHelper::userLink($user->id); ?>"><img src="<?php echo $user->getAvatar(); ?>" alt="avatar" data-author="<?php echo $user->id; ?>" ></a>
                        </div>
                        <div class="joms-block">
                            <?php echo JText::_('COM_COMMUNITY_GROUPS_CREATED_BY'); ?> <a href="<?php echo CUrlHelper::userLink($user->id); ?>"><?php echo $user->getDisplayName();?></a>
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
    <div class="cEmpty cAlert"><?php echo JText::_('COM_COMMUNITY_EVENTS_NO_EVENTS_ERROR'); ?></div>
<?php } ?>

<?php if (isset($pagination) && $pagination->getPagesLinks() && ($pagination->pagesTotal > 1 || $pagination->total > 1) ) { ?>
    <div class="joms-pagination">
        <?php echo $pagination->getPagesLinks(); ?>
    </div>
<?php } ?>
