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
$params = $this->act->params;
$activityId = $params->get('activityId');

$activity = JTable::getInstance('Activity', 'CTable');
$activity->load($activityId);

$date = JDate::getInstance($act->created);
if ($config->get('activitydateformat') == "lapse") {
    $createdTime = CTimeHelper::timeLapse($date);
} else {
    $createdTime = $date->format($config->get('profileDateFormat'));
}

$actor = CFactory::getUser($activity->actor);
$attachment = '';

$activityParam = new CParameter($activity->params);

$type = $activityParam->get('type', NULL);

$count = $activityParam->get('batchcount');

$app = array('events' => JText::_('COM_COMMUNITY_EVENT_SMALLCAP'),
    'groups' => JText::_('COM_COMMUNITY_GROUP_SMALLCAP'),
    'profile' => JText::_('COM_COMMUNITY_STATUS_SMALLCAP'),
    'events.wall' => JText::_('COM_COMMUNITY_EVENT_STATUS'),
    'groups.wall' => JText::_('COM_COMMUNITY_GROUP_STATUS'),
    'photos' => ($count > 1) ? JText::_('COM_COMMUNITY_PHOTOS_SMALLCAP') : JText::_('COM_COMMUNITY_PHOTO_SMALLCAP'),
    'cover.upload' => JText::_('COM_COMMUNITY_COVER_PHOTO_SMALLCAP_' . strtoupper($type)),
    'videos' => JText::_('COM_COMMUNITY_VIDEO_SMALLCAP'),
    'profile.status.share' => JText::_('COM_COMMUNITY_STATUS_SMALLCAP'),
    'groups.discussion' => JText::_('COM_COMMUNITY_GROUPS_GROUP_DISCUSSION'),
    'profile.avatar.upload' => JText::_('COM_COMMUNITY_AVATAR_SMALLCAP'),
    'wall' => JText::_('COM_COMMUNITY_ACTIVITY'),
    'videos.linking' => JText::_('COM_COMMUNITY_VIDEO_SMALLCAP')
);

if (!is_null($activity->id)) {
    /**
     * Get more data for sharing
     */
    $attachment = new stdClass();
    /* Reference activty */
    switch ($activity->app) {
        case 'groups.discussion':
            $attachment->type = 'share.' . $activity->app;
            $table = JTable::getInstance('Discussion', 'CTable');
            $table->load(array(
                'id' => $activity->cid,
                'groupid' => $activity->groupid));
            $attachment->discussion_title = $table->title;
            $attachment->discussion_message = $table->message;
            /* Get discussion information */
            $attachment->link = CRoute::_($activityParam->get('topic_url'));

            $table = JTable::getInstance('Group', 'CTable');
            $table->load($activity->groupid);
            $attachment->group_name = $table->name;
            break;
        case 'groups.discussion.reply':
            /**
             * @todo If we keep this way of code than we also need improve query here instead use JTable
             */
            $attachment->type = 'share.' . $activity->app;
            $table = JTable::getInstance('Discussion', 'CTable');
            $table->load(array(
                'id' => $activity->cid,
                'groupid' => $activity->groupid));
            $attachment->discussion_title = $table->title;
            $attachment->discussion_message = $table->message;
            $table = JTable::getInstance('Wall', 'CTable');
            /* Get discussion information */
            $tParams = new JRegistry($activity->params);
            $table->load($tParams->get('wallid'));
            $attachment->comment = $table->comment;
            $table = JTable::getInstance('Group', 'CTable');
            $table->load($activity->groupid);
            $attachment->group_name = $table->name;
            break;
        case 'wall' :

            //from third party app
            $attachment->content = $activity->content;
            $attachment->type='general';
            break;
    }

    (!isset($attachment->type)) ? $attachments = array() : $attachments[] = $attachment;

    $groupString = "";

    if(isset($activity->groupid) && !empty($activity->groupid)){
        $groupTable = JTable::getInstance('Group', 'CTable');
        $groupTable->load($activity->groupid);
        $groupString = JText::sprintf('COM_COMMUNITY_SHARE_VIDEO_FROM_GROUP',$groupTable->getLink(),$groupTable->name);
    }

    $stream = new stdClass();
    $stream->actor = $user;
    $stream->target = null;
    $stream->headline = JText::sprintf('COM_COMMUNITY_ACTIVITY_SHARE_STATUS', CUrlHelper::userLink($user->id), $user->getDisplayName(), CUrlHelper::userLink($actor->id), $actor->getDisplayName(), (isset($app[$activity->app])) ? $app[$activity->app] : '').$groupString;
    $stream->message = CActivities::format($act->title);
    $stream->mood = $params->get('mood', NULL);
    $stream->sharedMessage = CActivities::format($activity->title);
    $stream->sharedMood= $activityParam->get('mood', NULL);
    $stream->groupid = $activity->groupid;
    $stream->eventid = "";
    $stream->access = $this->act->access;
    $stream->attachments = $attachments;
    /**
     * @todo Need to clearly this one
     * Right now it's return on right data
     */
    $stream->attachments[] = CActivityStream::formatStreamAttachment($activity);
    $stream->createdtime = $createdTime;

    $this->set('stream', $stream);
    $this->load('stream/base-extended');
}
