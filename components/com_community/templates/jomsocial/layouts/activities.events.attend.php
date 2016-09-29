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

    $truncateVal = 60;
    $date = JDate::getInstance($act->created);
    if ($config->get('activitydateformat') == "lapse") {
        $createdTime = CTimeHelper::timeLapse($date);
    } else {
        $createdTime = $date->format($config->get('profileDateFormat'));
    }
    $format = ($config->get('eventshowampm')) ? JText::_('COM_COMMUNITY_DATE_FORMAT_LC2_12H') : JText::_('COM_COMMUNITY_DATE_FORMAT_LC2_24H');
    // Setup group table
    $event = JTable::getInstance('Event', 'CTable');
    $event->load($act->eventid);
    $this->set('event', $event);
    $membersCount = $event->getMembersCount(COMMUNITY_EVENT_STATUS_ATTEND);

    $user = CFactory::getUser($this->act->actor);
    $users = array_reverse(explode(',', $this->actors));
    $userCount = count($users);

    if($userCount != $membersCount){
        $userCount = CActivitiesHelper::updateEventAttendMembers($this->act->id, $event);
    }

    $actorsHTML = array();

    $slice = 2;

    if ($userCount > 2) {
        $slice = 1;
    }

    $users = array_slice($users, 0, $slice);
?>

<div class="joms-stream__body no-head">
    <p>
        <svg class="joms-icon" viewBox="0 0 16 16">
            <use xlink:href="<?php echo CRoute::getURI(); ?>#joms-icon-users"></use>
        </svg>
        <?php

            foreach ($users as $actor) {
                if (!$actor) {
                    $actor = $this->act->actor;
                }
                $user = CFactory::getUser($actor);
                $actorsHTML[] = '<a class="cStream-Author" href="' . CUrlHelper::userLink($user->id) . '">' . $user->getDisplayName() . '</a>';
            }

            $others = '';

            if ($userCount > 2) {
                $others = JText::sprintf('COM_COMMUNITY_STREAM_OTHERS_JOIN_EVENT' , $userCount-1, 'onclick="joms.api.streamShowOthers(' . $act->id . ');return false;"');
            }

            echo implode(', ', $actorsHTML) . $others;

            $jtext = ($userCount>1) ? 'COM_COMMUNITY_ACTIVITIES_EVENT_ATTEND_PLURAL' : 'COM_COMMUNITY_ACTIVITIES_EVENT_ATTEND';
            echo JText::sprintf($jtext, $this->event->getLink(), $this->event->title);
        ?>
    </p>
    <div class="joms-gap"></div>
    <div class="joms-media like">
        <a href="<?php echo $this->event->getLink();?>">
            <div class="joms-media__cover">
                <?php
                    $datestr = strtotime($this->event->startdate);
                    $day = date('d', $datestr);
                    $month = date('M', $datestr);
                ?>

                <div class="joms-focus__date cover">
                    <span><?php echo JText::_( CEventHelper::formatStartDate($event, 'M') ); ?></span>
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
