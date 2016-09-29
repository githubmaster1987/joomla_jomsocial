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

if ($this->act->app == 'groups.like') {
  $this->load('activities/groups/like');
} else if ($this->act->app == 'events.like') {
  $this->load('activities/events/like');
} else if ($this->act->app == 'album.like') {
  $this->load('activities/album/like');
} else if ($this->act->app == 'profile.like') {
  $this->load('activities/profile/like');
} else if ($this->act->app == 'videos.like') {
  $this->load('activities/videos/like');
} else {

    $photo = JTable::getInstance('Photo',  'CTable');
    $photo->load($act->cid);

    // if($album->permissions == 30 && !CFriendsHelper::isConnected($my->id,$album->creator)){
    //     return false;
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

    $isPhotoModal = $config->get('album_mode') == 1;

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
    <?php if ($likedContent !== null) { ?>
    <a
        <?php if ($isPhotoModal) { ?>
        href="javascript:" onclick="joms.api.photoOpen('<?php echo $photo->albumid; ?>', '<?php echo $photo->id; ?>');"
        <?php } else { ?>
        href="<?php echo CRoute::_('index.php?option=com_community&view=photos&task=photo&albumid=' . $photo->albumid . '&photoid=' . $photo->id); ?>"
        <?php } ?>
    >
        <div class="joms-media--image--half">
            <img src="<?php echo $likedContent->thumb; ?>" alt="<?php echo $likedContent->title; ?>" />
        </div>

    </a>
    <?php } ?>
</div>

<?php } ?>
