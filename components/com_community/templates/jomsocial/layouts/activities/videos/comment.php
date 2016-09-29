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

    // Load params
    $param = $act->params;

    if (is_array($param->get('actors'))) {
        $users = $param->get('actors');
        $user = CFactory::getUser($users[0]);
    } else { //backwards compatibility
        $users = array($this->act->actor);
        $user = CFactory::getUser($this->act->actor);

    }

    $wall = JTable::getInstance('Wall', 'CTable');
    $wall->load($param->get('wallid'));

    $date = JDate::getInstance($act->created);
    if ($config->get('activitydateformat') == "lapse") {
        $createdTime = CTimeHelper::timeLapse($date);
    } else {
        $createdTime = $date->format($config->get('profileDateFormat'));
    }

    if($video->permissions == 30 && !CFriendsHelper::isConnected($my->id,$video->creator)){
        return false;
    }

    //generate activity based on the video owner
    $url = $this->video->getViewURI();
    $isVideoModal = $config->get('video_mode') == 1;
    if ( $isVideoModal ) {
        $url = 'javascript:" onclick="joms.api.videoOpen(\'' . $this->video->id . '\');';
    }

    if ($video->creator != $act->actor || count($users) > 1) {
        //if user a commented on user b photo, we need to pass in the user info
        $ownerUrl = CUrlHelper::userLink($act->actor);
        $target = CFactory::getUser($video->creator);
        $targetName = $user->getDisplayName();
        $targetUrl = CUrlHelper::userLink($video->creator);

        if (count($users) > 1) {
            if(count($users) == 2){
                $activityString = JText::sprintf('COM_COMMUNITY_ACTIVITIES_COMMENT_OTHERS_VIDEO_TWO',
                    CUrlHelper::userLink($user->id), $user->getDisplayName(),
                    CUrlHelper::userLink($users[count($users)-1]), CFactory::getUser($users[count($users)-1])->getDisplayName(), $targetUrl,
                    $target->getDisplayName(), $url);
            }else{
                $activityString = JText::sprintf('COM_COMMUNITY_ACTIVITIES_COMMENT_OTHERS_VIDEO_MORE',
                    CUrlHelper::userLink($user->id), $user->getDisplayName(),
                    'onclick="joms.api.streamShowOthers(' . $act->id . ');return false;"', count($users) - 1, $targetUrl,
                    $target->getDisplayName(), $url);
            }

        } else {
            $activityString = Jtext::sprintf('COM_COMMUNITY_ACTIVITIES_COMMENT_OTHERS_VIDEO', $ownerUrl, $targetName,
                $targetUrl, $target->getDisplayName(), $url);
        }


    } else {
        //user comment on his own photo
        $activityString = Jtext::sprintf('COM_COMMUNITY_ACTIVITIES_COMMENT_OWN_VIDEO', CUrlHelper::userLink($user->id),
            $user->getDisplayName(), $url);
    }

    $isVideoModal = $config->get('video_mode') == 1;

    if($video->type ==='file'){
        $storage = CStorage::getStorage($video->storage);
        $path = $storage->getURI($video->path);
    } else {
        $path = $video->path;
    }

?>

<div class="joms-stream__header">
    <div class= "joms-avatar--stream <?php echo CUserHelper::onlineIndicator($user); ?>">
        <a href="<?php echo CUrlHelper::userLink($user->id); ?>">
            <img data-author="<?php echo $user->id; ?>" src="<?php echo $user->getThumbAvatar(); ?>" alt="<?php echo $user->getDisplayName(); ?>">
        </a>
    </div>
    <div class="joms-stream__meta">
        <?php echo $activityString; ?>
        <span class="joms-stream__time">
            <small><?php echo $createdTime; ?></small>
        </span>
    </div>
    <?php

        $my = CFactory::getUser();
        if ($my->id > 0) {
            $this->load('activities.stream.options');
        }

    ?>
</div>

<div class="joms-stream__body">
    <p>
        <span>
    <?php

        $comment = JHTML::_('string.truncate', $wall->comment, $config->getInt('streamcontentlength'));
        $comment = CActivities::format($comment);
        echo $comment;
    ?>
            </span>
    </p>

    <div class="joms-media--video joms-js--video"
            data-type="<?php echo $video->type; ?>"
            data-id="<?php echo $video->video_id; ?>"
            data-path="<?php echo $path ?>" >

        <div class="joms-media__thumbnail">
            <img src="<?php echo $video->getThumbnail(); ?>" alt="<?php echo $video->title; ?>" >
            <a href="javascript:" class="mejs-overlay mejs-layer mejs-overlay-play joms-js--video-play joms-js--video-play-<?php echo $wall->id ?>">
                <div class="mejs-overlay-button"></div>
            </a>
        </div>
        <div class="joms-media__body">
            <h4 class="joms-media__title">
                <?php echo JHTML::_('string.truncate', $video->title, 50, true, false); ?>
            </h4>
            <p class="joms-media__desc">
                <?php echo JHTML::_('string.truncate', $video->description, $config->getInt('streamcontentlength'), true, false); ?>
            </p>
        </div>
    </div>

</div>

<?php $this->load('stream/footer'); ?>
