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
            <h3 class="joms-page__title"><?php echo $pageTitle; ?></h3>
        </div>

        <div class="joms-list__utilities">
            <form method="GET" class="joms-inline--desktop" action="<?php echo CRoute::_('index.php?option=com_community&view=events&task=search'); ?>">
                <span>
                    <input type="text" class="joms-input--search" name="search" placeholder="<?php echo JText::_('COM_COMMUNITY_SEARCH_EVENT_PLACEHOLDER'); ?>">
                </span>
                <?php echo JHTML::_( 'form.token' ) ?>
                <span>
                    <button class="joms-button--neutral"><?php echo JText::_('COM_COMMUNITY_SEARCH_GO'); ?></button>
                </span>
                <input type="hidden" name="option" value="com_community" />
                <input type="hidden" name="view" value="events" />
                <input type="hidden" name="task" value="search" />
                <input type="hidden" name="Itemid" value="<?php echo CRoute::getItemId();?>" />
                <input type="hidden" name="posted" value="1" />
            </form>
            <!--<?php if($canCreate) { ?>
            <button class="joms-button--primary" onclick="window.location='<?php echo $createLink; ?>';"><?php echo (isset($isGroup) && $isGroup) ? JText::_('COM_COMMUNITY_CREATE_GROUP_EVENT') : JText::_('COM_COMMUNITY_CREATE_EVENT'); ?></button>
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
		if( $events )
		{
		?>
		<div class="joms-alert joms-alert--info">
			<?php echo JText::sprintf( CStringHelper::isPlural( $count ) ? 'COM_COMMUNITY_EVENTS_INVITATION_COUNT_MANY' : 'COM_COMMUNITY_EVENTS_INVITATION_COUNT_SINGLE' , $count ); ?>
		</div>

		<ul class="joms-list--card">
		<?php
			for( $i = 0; $i < count( $events ); $i++ )
			{
				$event	=& $events[$i];
                $user = CFactory::getUser($event->creator);

                $isGroup = false;
                if($event->type == 'group' && $event->contentid){
                    $isGroup = true;
                    $groupModel = CFactory::getModel('groups');
                    $groupName = $groupModel->getGroupName($event->contentid);
                    $groupLink = CRoute::_( 'index.php?option=com_community&view=groups&task=viewgroup&groupid='.$event->contentid);
                }
		?>
			<li class="joms-list__item" id="events-invite-<?php echo $event->id;?>">
				<div class="joms-list__cover">
					<a href="<?php echo CRoute::_( 'index.php?option=com_community&view=events&task=viewevent&eventid=' . $event->id );?>" />
						<div class="joms-list__cover-image" style="background-image: url(<?php echo $event->getCover(); ?>);"></div>
					</a>
				</div>
				<div class="joms-list__content">
					<h4 class="joms-list__title">
						<a href="<?php echo CRoute::_( 'index.php?option=com_community&view=events&task=viewevent&eventid=' . $event->id );?>"><?php echo $this->escape($event->title); ?></a>
					</h4>
					<ul class="joms-list">
                        <?php if($isGroup){ ?>
                            <li>
                                <svg class="joms-icon" viewBox="0 0 16 16">
                                    <use xlink:href="<?php echo CRoute::getURI(); ?>#joms-icon-users"></use>
                                </svg>
                                <a href="<?php echo $groupLink; ?>">
                                    <?php echo $this->escape($groupName); ?>
                                </a>
                            </li>
                        <?php } ?>
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
                            ); ?>"><?php echo JText::sprintf(
                                    (!CStringHelper::isSingular(
                                        $event->getMembersCount(COMMUNITY_EVENT_STATUS_ATTEND)
                                    )) ? 'COM_COMMUNITY_EVENTS_ATTANDEE_COUNT_MANY_NUMBER' : 'COM_COMMUNITY_EVENTS_ATTANDEE_COUNT_NUMBER',
                                    $event->getMembersCount(COMMUNITY_EVENT_STATUS_ATTEND)
                                ); ?></a>
                        </li>
                        <li>
                        	<div class="joms-gap"></div>
                            <svg class="joms-icon" viewBox="0 0 16 16">
                                <use xlink:href="<?php echo CRoute::getURI(); ?>#joms-icon-clock"></use>
                            </svg>
                        	<small><?php echo JText::sprintf('COM_COMMUNITY_EVENTS_DURATION', JHTML::_('date', $event->startdate, JText::_('DATE_FORMAT_LC3')), JHTML::_('date', $event->enddate, JText::_('DATE_FORMAT_LC3'))); ?></small>
                        </li>
					</ul>

                    <div class="joms-popover__content joms-js--invitation-notice-event-<?php echo $event->id; ?>"></div>
				</div>

                <div class="joms-list__footer joms-padding">
                    <div class="<?php echo CUserHelper::onlineIndicator($user); ?>">
                    <a class="joms-avatar" href="<?php echo CUrlHelper::userLink($user->id);?>"><img src="<?php echo $user->getAvatar();?>" alt="avatar" data-author="<?php echo $user->id; ?>" ></a>
                    </div>
                    <?php echo JText::_('COM_COMMUNITY_GROUPS_CREATED_BY'); ?> <a href="<?php echo CUrlHelper::userLink($user->id);?>"><?php echo $user->getDisplayName(); ?></a>
                </div>

                <span class="joms-list__permission joms-js--invitation-buttons-event-<?php echo $event->id; ?> top-gap" style="white-space:nowrap">
                    <button class="joms-button--neutral joms-button--smallest" onclick="joms.api.invitationReject('event', '<?php echo $event->id; ?>');"><?php echo JText::_('COM_COMMUNITY_EVENTS_REJECT'); ?></button>
                    <button class="joms-button--primary joms-button--smallest" onclick="joms.api.invitationAccept('event', '<?php echo $event->id; ?>');"><?php echo JText::_('COM_COMMUNITY_EVENTS_ACCEPT'); ?></button>
                </span>


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

	<?php
		}
		else
		{
	?>
		<div class="cEmpty cAlert"><?php echo JText::_('COM_COMMUNITY_EVENTS_NO_INVITATIONS'); ?></div>
	<?php
		}
	?>

	<?php if ($pagination->getPagesLinks() && ($pagination->pagesTotal > 1 || $pagination->total > 1) ) { ?>
	    <div class="joms-pagination">
	        <?php echo $pagination->getPagesLinks(); ?>
	    </div>
	<?php } ?>
</div>
