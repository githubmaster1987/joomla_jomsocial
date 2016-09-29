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


// $user = CFactory::getUser($this->act->actor);
// $date = JDate::getInstance($act->created);
// if ( $config->get('activitydateformat') == "lapse" ) {
//   $createdTime = CTimeHelper::timeLapse($date);
// } else {
//   $createdTime = $date->format($config->get('profileDateFormat'));
// }

    $params = $act->params;

    $users = $params->get('actors');
    if ( !is_array($users) ) {
        $users = array_reverse( explode(',', $users) );
    }

    $user = CFactory::getUser( $users[0] );

    $date = JDate::getInstance($act->created);
    if ($config->get('activitydateformat') == "lapse") {
        $createdTime = CTimeHelper::timeLapse($date);
    } else {
        $createdTime = $date->format($config->get('profileDateFormat'));
    }

    // Setup video table
    $video = JTable::getInstance('Video', 'CTable');
    $video->load($act->cid);
    $this->set('video', $video);

?>

<div class="joms-stream__header">
    <div class= "joms-avatar--stream <?php echo CUserHelper::onlineIndicator($user); ?>">
        <?php if (count($users) > 1 && false) { // added false for now because we have to show the last user avatar ?>
            <svg class="joms-icon" viewBox="0 0 16 16">
                <use xlink:href="<?php echo CRoute::getURI(); ?>#joms-icon-users"></use>
            </svg>
        <?php } else { ?>
            <a href="<?php echo CUrlHelper::userLink($user->id); ?>">
                <img data-author="<?php echo $user->id; ?>" src="<?php echo $user->getThumbAvatar(); ?>" alt="<?php echo $user->getDisplayName(); ?>">
            </a>
        <?php } ?>
    </div>
    <div class="joms-stream__meta">
        <?php echo CLikesHelper::generateHTML($act, $likedContent); ?>
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
    <div class="joms-media--video joms-js--video"
            data-type="<?php echo $video->type; ?>"
            data-id="<?php echo $video->video_id; ?>"
            data-path="<?php echo ($video->type == 'file') ?  CStorage::getStorage($video->storage)->getURI($video->path) : $video->path; ?>" >

        <div class="joms-media__thumbnail">
            <img src="<?php echo $video->getThumbnail(); ?>" alt="video thumbnail" >
            <a href="javascript:" class="mejs-overlay mejs-layer mejs-overlay-play joms-js--video-play joms-js--video-play-<?php echo $act->id ?>">
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
