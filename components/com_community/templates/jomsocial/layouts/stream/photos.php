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

$album = JTable::getInstance('Album', 'CTable');
$album->load($act->cid);
$act->album = $album;
$this->set('album', $album);


// get created date time
$date = JDate::getInstance($act->created);
if ($config->get('activitydateformat') == "lapse") {
    $createdTime = CTimeHelper::timeLapse($date);
} else {
    $createdTime = $date->format($config->get('profileDateFormat'));
}


$user = CFactory::getUser($this->act->actor);
if (is_object($act->params)) {
    $action = $act->params->get('action');
} else {
    $act->params = new CParameter($act->params);
    $action = $act->params->get('action');
}

//
// Load saperate template for featured photo
if ($act->app == 'albums.featured') {
    $this->load('activities.stream.options');
    $this->load('activities/photos/featured');
    return;
}

// Load saperate template for comment on a photo
// @since 2.8 .Newers stream uses 'photos.comment'
if ($action == 'wall' || $act->app == 'photos.comment') {
    $this->load('activities.photos.comment');
    return;
}

?>

<div class="joms-stream__header">
    <div class= "joms-avatar--stream <?php echo CUserHelper::onlineIndicator($user); ?>">
        <a href="<?php echo CUrlHelper::userLink($user->id); ?>">
            <img data-author="<?php echo $user->id; ?>" src="<?php echo $user->getThumbAvatar(); ?>" alt="<?php echo $user->getDisplayName(); ?>" >
        </a>
    </div>
    <div class="joms-stream__meta">
        <a class="joms-stream__user" href="<?php echo CUrlHelper::userLink($user->id); ?>"><?php echo $user->getDisplayName(); ?></a>

        <?php
        // If we're using new stream style or has old style data (which contains {multiple} )
        if ($act->params->get('style') == COMMUNITY_STREAM_STYLE || strpos($act->title, '{multiple}')) {
            // New style
            $count = $act->params->get('count', $act->params->get('count', 1));
            $albumUrl = CRoute::_($album->getURI());
            $isPhotoModal = $config->get('album_mode') == 1;

            if ( $isPhotoModal ) {
                $albumUrl = 'javascript:" onclick="joms.api.photoOpen(\'' . $album->id . '\', \'\');';
            }

            if (CStringHelper::isPlural($count)) {
                if($act->groupid){
                    $group = JTable::getInstance('Group', 'CTable');
                    $group->load($act->groupid);
                    $this->set('group', $group);
                    echo JText::sprintf('COM_COMMUNITY_ACTIVITY_GROUP_PHOTOS_UPLOAD_PLURAL', $count, $albumUrl, CStringHelper::escape($album->name), CUrlHelper::groupLink($group->id), CStringHelper::escape($group->name));
                }elseif($act->eventid){
                    $event = JTable::getInstance('Event', 'CTable');
                    $event->load($act->eventid);
                    $this->set('event', $event);
                    echo JText::sprintf('COM_COMMUNITY_ACTIVITY_EVENT_PHOTOS_UPLOAD_PLURAL', $count, $albumUrl, CStringHelper::escape($album->name), CUrlHelper::eventLink($event->id), CStringHelper::escape($event->title));
                }else{
                    if($act->actor != $act->target && $act->target != 0){
                        $target = CFactory::getUser($act->target);
                        echo JText::sprintf('COM_COMMUNITY_ACTIVITY_PHOTO_PROFILE_SHARE_PLURAL', $count, CUrlHelper::userLink($target->id), $target->getDisplayName());
                    }else{
                        echo JText::sprintf('COM_COMMUNITY_ACTIVITY_PHOTO_UPLOAD_TITLE_MANY', $count, $albumUrl, CStringHelper::escape($album->name));
                    }
                }
            } else {
                if($act->groupid){
                    $group = JTable::getInstance('Group', 'CTable');
                    $group->load($act->groupid);
                    $this->set('group', $group);
                    if(strpos($act->params->get('albumType'), '.gif')) {
                        echo JText::sprintf('COM_COMMUNITY_ACTIVITY_GROUP_ANIMATION_UPLOAD', CUrlHelper::groupLink($group->id), CStringHelper::escape($group->name));
                    }else {
                        echo JText::sprintf('COM_COMMUNITY_ACTIVITY_GROUP_PHOTO_UPLOAD_SINGLE', $albumUrl, CStringHelper::escape($album->name), CUrlHelper::groupLink($group->id), CStringHelper::escape($group->name));
                    }
                }elseif($act->eventid){
                    $event = JTable::getInstance('Event', 'CTable');
                    $event->load($act->eventid);
                    $this->set('event', $event);
                    if(strpos($act->params->get('albumType'), '.gif')) {
                        echo JText::sprintf('COM_COMMUNITY_ACTIVITY_EVENT_ANIMATION_UPLOAD', CUrlHelper::eventLink($event->id), CStringHelper::escape($event->title));
                    }else {
                        echo JText::sprintf('COM_COMMUNITY_ACTIVITY_EVENT_PHOTOS_UPLOAD_SINGLE', $albumUrl, CStringHelper::escape($album->name), CUrlHelper::eventLink($event->id), CStringHelper::escape($event->title));
                    }
                }else {
                    if ($act->actor != $act->target && $act->target != 0) {
                        $target = CFactory::getUser($act->target);
                        echo JText::sprintf('COM_COMMUNITY_ACTIVITY_PHOTO_PROFILE_SHARE_SINGLE', CUrlHelper::userLink($target->id), $target->getDisplayName());
                    } else {
                        if(strpos($act->params->get('albumType'), '.gif')) {
                            echo JText::_('COM_COMMUNITY_ACTIVITY_ANIMATION_UPLOAD_TITLE');
                        }else {
                            echo JText::sprintf('COM_COMMUNITY_ACTIVITY_PHOTO_UPLOAD_TITLE', $albumUrl,
                                CStringHelper::escape($album->name));
                        }
                    }
                }
            }
        }
        ?>

        <?php if(!$act->groupid) {?>
        <span class="joms-stream__time">
            <small><?php echo $createdTime; ?></small>
            <?php $this->load('privacy/show'); ?>
            <?php // echo CActivitiesHelper::getStreamPermissionHTML($act->access,$act->actor); ?>
        </span>
        <?php }?>
    </div>

    <?php

        $my = CFactory::getUser();
        $this->load('activities.stream.options');

    ?>
</div>

<div class="joms-stream__body">
    <?php
    //remove for now
    if ($act->groupid && false) {
        $group = JTable::getInstance('Group', 'CTable');
        $group->load($act->groupid);
        $this->set('group', $group);
        ?>


        <a class="joms-stream-reference" href="<?php echo CUrlHelper::groupLink($group->id); ?>"><i class="joms-icon-users"></i><?php echo $group->name; ?></a>

    <?php
    }

    $html = CPhotos::getActivityContentHTML($act);
    echo $html;
    ?>
</div>

<?php
if ($action != 'wall') {
    $this->load('stream/footer');
}
?>
