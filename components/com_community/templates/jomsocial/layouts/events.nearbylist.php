<?php
/**
* @copyright (C) 2013 iJoomla, Inc. - All rights reserved.
* @license GNU General Public License, version 2 (http://www.gnu.org/licenses/gpl-2.0.html)
* @author iJoomla.com <webmaster@ijoomla.com>
* @url https://www.jomsocial.com/license-agreement
* The PHP code portions are distributed under the GPL license. If not otherwise stated, all images, manuals, cascading style sheets, and included JavaScript *are NOT GPL, and are released under the IJOOMLA Proprietary Use License v1.0
* More info at https://www.jomsocial.com/license-agreement
*/
defined('_JEXEC') or die( 'Restricted Access' );
?>

<p><?php echo JText::sprintf('COM_COMMUNITY_EVENTS_NEARBY_RADIUS', $location, $radius, $measurement); ?></p>

<?php if ( $events ) { ?>
    <ul class="joms-list--event">
        <?php foreach ($events as $event) { ?>
            <li class="joms-media--event" <?php if ( !empty($event->summary) ) { ?>title="<?php echo CStringHelper::escape($event->summary); ?>"<?php } ?>>
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
                    <a href="<?php echo $event->getLink(); ?>"><?php echo CStringHelper::escape($event->title); ?></a>
                        <span class="joms-block"> <?php echo CStringHelper::escape($event->location); ?></span>
                        <a href="<?php echo $event->getGuestLink(COMMUNITY_EVENT_STATUS_ATTEND); ?>">
                            <?php echo JText::sprintf((!CStringHelper::isSingular($event->confirmedcount)) ? 'COM_COMMUNITY_EVENTS_ATTANDEE_COUNT_MANY' : 'COM_COMMUNITY_EVENTS_ATTANDEE_COUNT', $event->confirmedcount); ?>
                    </a>
                </div>
            </li>
        <?php } ?>
    </ul>
<?php } else { ?>
    <div class="cEmpty"><?php echo JText::_('COM_COMMUNITY_EVENTS_NO_NEARBY') ?></div>
<?php } ?>
