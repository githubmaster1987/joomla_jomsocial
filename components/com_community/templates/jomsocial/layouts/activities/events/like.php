<?php
/**
* @copyright (C) 2013 iJoomla, Inc. - All rights reserved.
* @license GNU General Public License, version 2 (http://www.gnu.org/licenses/gpl-2.0.html)
* @author iJoomla.com <webmaster@ijoomla.com>
* @url https://www.jomsocial.com/license-agreement
* The PHP code portions are distributed under the GPL license. If not otherwise stated, all images, manuals, cascading style sheets, and included JavaScript *are NOT GPL, and are released under the IJOOMLA Proprietary Use License v1.0
* More info at https://www.jomsocial.com/license-agreement
*/

defined('_JEXEC') or die('Restricted access');

$user = CFactory::getUser($this->act->actor);
$truncateVal = 60;
$date = JDate::getInstance($act->created);
if ( $config->get('activitydateformat') == "lapse" ) {
  $createdTime = CTimeHelper::timeLapse($date);
} else {
  $createdTime = $date->format($config->get('profileDateFormat'));
}
$format = ($config->get('eventshowampm')) ? JText::_('COM_COMMUNITY_DATE_FORMAT_LC2_12H') : JText::_('COM_COMMUNITY_DATE_FORMAT_LC2_24H');
// Setup group table
$event = JTable::getInstance('Event', 'CTable');
$event->load($act->eventid);
$this->set('event', $event);

?>

<div class="joms-stream__header no-avatar">
    <div class="joms-stream__meta">
        <?php echo CLikesHelper::generateHTML($act, $likedContent) ?>
        <span class="joms-stream__time"><small><?php echo $createdTime; ?></small></span>
    </div>
    <?php

        $my = CFactory::getUser();
        $this->load('activities.stream.options');

    ?>
</div>


<div class="joms-stream__body">
    <div class="joms-media like">
        <a href="<?php echo $this->event->getLink();?>">
            <div class="joms-media__cover">
                <?php
                    $datestr = strtotime($this->event->startdate);
                    $day = date('d', $datestr);
                    $month = date('M', $datestr);
                ?>

                <div class="joms-focus__date cover">
                    <span><?php echo $month; ?></span>
                    <span><?php echo $day; ?></span>
                </div>
                <img src="<?php echo $this->event->getCover(); ?>" alt="<?php echo $this->event->title; ?>" />
            </div>
        </a>
        <h4 class="joms-text--title"><a href="<?php echo $this->event->getLink();?>"><?php echo JHTML::_('string.truncate',$this->event->title , $truncateVal); ?></a></h4>
        <p class="joms-text--desc"><?php echo JHTML::_('string.truncate',strip_tags($event->summary) , $config->getInt('streamcontentlength')); ?></p>
        <ul class="joms-list">
            <li class="joms-list__item">
                <svg viewBox="0 0 16 18" class="joms-icon">
                    <use xlink:href="<?php echo CRoute::getURI(); ?>#joms-icon-calendar"></use>
                </svg>
            <?php echo CTimeHelper::getFormattedTime($this->event->startdate, $format); ?></li>
            <li class="joms-list__item">
                <svg viewBox="0 0 16 16" class="joms-icon">
                    <use xlink:href="<?php echo CRoute::getURI(); ?>#joms-icon-location"></use>
                </svg>
            <?php echo $this->event->location; ?></li>
        </ul>
    </div>
</div>
