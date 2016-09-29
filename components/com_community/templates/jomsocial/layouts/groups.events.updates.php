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
<?php if( $events ){ ?>
<div class="joms-module__wrapper">
    <div class="joms-tab__bar">
        <a href="#joms-group--events" class="active"><?php echo JText::_('COM_COMMUNITY_EVENTS_UPCOMING'); ?></a>
    </div>

    <div id="#joms-group--events" class="joms-tab__content">
		<ul class="joms-list--event">
			<?php foreach($events as $event){ ?>
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
					<ul class="joms-list">
						<li class="joms-list__item">
							<a href="<?php echo CRoute::_('index.php?option=com_community&view=events&task=viewevent&eventid='.$event->id); ?>">
								<?php echo $this->escape($event->title); ?>
							</a>
						</li>
						<li class="joms-list__item">
							<div>
								<?php echo $this->escape( $event->location );?>
							</div>
						</li>
						<li class="joms-list__item">
							<small>
								<a href="<?php echo CRoute::_('index.php?option=com_community&view=events&task=viewguest&eventid=' . $event->id . '&type='.COMMUNITY_EVENT_STATUS_ATTEND);?>">
									<?php echo JText::sprintf((CStringHelper::isPlural($event->confirmedcount)) ? 'COM_COMMUNITY_EVENTS_MANY_GUEST_COUNT':'COM_COMMUNITY_EVENTS_GUEST_COUNT', $event->confirmedcount );?>
								</a>
							</small>
						</li>
					</ul>
				</div>
			</li>
			<?php } ?>
		</ul>
	</div>
</div>
<?php } ?>
