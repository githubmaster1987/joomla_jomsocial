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

$param = $this->act->params;

if ( $users = $param->get('actors') ) {
    $users = explode( ',', $users );
    $user = CFactory::getUser($users[count($users)-1]);
} else {
    $users = array($this->act->actor);
    $user = CFactory::getUser($this->act->actor);
}

$truncateVal = 60;
$date = JDate::getInstance($act->created);
if ( $config->get('activitydateformat') == "lapse" ) {
  $createdTime = CTimeHelper::timeLapse($date);
} else {
  $createdTime = $date->format($config->get('profileDateFormat'));
}

// Setup album table
$album = JTable::getInstance('Album', 'CTable');

$album->load($act->cid);
$this->set('album', $album);

if($album->permissions == 30 && !CFriendsHelper::isConnected($my->id,$album->creator)){
    return false;
}

$albumUrl = CRoute::_($album->getURI());
$isPhotoModal = $config->get('album_mode') == 1;
if ( $isPhotoModal ) {
    $albumUrl = 'javascript:" onclick="joms.api.photoOpen(\'' . $album->id . '\', \'\');';
}


?>

<div class="joms-stream__header">
    <div class= "joms-avatar--stream <?php echo CUserHelper::onlineIndicator($user); ?>">
        <a href="<?php echo CUrlHelper::userLink($user->id); ?>">
            <img data-author="<?php echo $user->id; ?>" src="<?php echo $user->getThumbAvatar(); ?>" alt="<?php echo $user->getDisplayName(); ?>">
        </a>
    </div>
    <div class="joms-stream__meta">
        <?php echo CLikesHelper::generateHTML($act, $likedContent) ?>
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
    <div class="joms-media--album">
        <div class="joms-media__thumbnail">
            <a href="<?php echo $albumUrl; ?>">
                <img src="<?php echo $album->getCoverThumbURI();?>" alt="<?php echo $album->name; ?>" />
            </a>
        </div>
        <div class="joms-media__body">
            <h4 class="joms-media__title">
                <a href="<?php echo $albumUrl; ?>">
                    <?php echo $album->name; ?>
                </a>
            </h4>
            <p class="joms-media__desc"><?php echo $album->description; ?></p>
        </div>
    </div>
</div>
