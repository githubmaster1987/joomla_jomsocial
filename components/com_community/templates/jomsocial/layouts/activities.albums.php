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

    // Load params
    $param = $act->params;
    if (is_array($param->get('actors'))) {
        $users = $param->get('actors');
        $user = CFactory::getUser($users[0]);
    } else { //backwards compatibility
        $users = array($this->act->actor);
        $user = CFactory::getUser($this->act->actor);
    }

    $album = JTable::getInstance('Album', 'CTable');
    $album->load($act->cid);
    $wall = JTable::getInstance('Wall', 'CTable');
    $wall->load($param->get('wallid'));

    if($album->permissions == 30 && !CFriendsHelper::isConnected($my->id,$album->creator)){
        return false;
    }

    $date = JDate::getInstance($act->created);
    if ($config->get('activitydateformat') == "lapse") {
        $createdTime = CTimeHelper::timeLapse($date);
    } else {
        $createdTime = $date->format($config->get('profileDateFormat'));
    }

    $ownerUrl = CUrlHelper::userLink($act->actor);
    $target = CFactory::getUser($album->creator);
    $targetName = $user->getDisplayName();
    $targetUrl = CUrlHelper::userLink($album->creator);

    $isPhotoModal = $config->get('album_mode') == 1;
    $albumUrl = CRoute::_($album->getURI());

    if ( $isPhotoModal ) {
        $albumUrl = 'javascript:" onclick="joms.api.photoOpen(\'' . $album->id . '\', \'\');';
    }

    if (count($users) > 1) {
        // if we have two users only, lets use the name instead of x others
        if(count($users) == 2){
            $activityString = JText::sprintf('COM_COMMUNITY_ACTIVITIES_COMMENT_OTHERS_ALBUM_TWO',
                CUrlHelper::userLink($user->id), $user->getDisplayName(),
                CUrlHelper::userLink($users[count($users)-1]), CFactory::getUser($users[count($users)-1])->getDisplayName(), $targetUrl,
                $target->getDisplayName(), $albumUrl);
        }else{
            $activityString = JText::sprintf('COM_COMMUNITY_ACTIVITIES_COMMENT_OTHERS_ALBUM_MORE',
                CUrlHelper::userLink($user->id), $user->getDisplayName(),
                'onclick="joms.api.streamShowOthers(' . $act->id . '); return false;"', count($users) - 1, $targetUrl,
                $target->getDisplayName(), $albumUrl);
        }
    }else{
        $activityString = JText::sprintf('COM_COMMUNITY_ACTIVITIES_WALL_POST_ALBUM', $targetUrl, $user->getDisplayName(), $albumUrl,
            $this->escape($album->name));
    }

?>

<div class="joms-stream__header">
    <div class= "joms-avatar--stream <?php echo CUserHelper::onlineIndicator($user); ?>">
        <a href="<?php echo CUrlHelper::userLink($user->id); ?>">
            <img data-author="<?php echo $user->id; ?>" src="<?php echo $user->getThumbAvatar(); ?>" alt="<?php echo $user->getDisplayName(); ?>" >
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
    <div class="joms-media--album">
        <div class="joms-media__thumbnail">
            <a href="<?php echo $albumUrl; ?>">
                <img src="<?php echo $album->getCoverThumbURI()?>" alt="<?php echo $this->escape($album->name); ?>" >
            </a>
        </div>
        <div class="joms-media__body">
            <h4 class="joms-media__title">
                <a href="<?php echo $albumUrl; ?>">
                <?php echo $this->escape($album->name)?></a>
            </h4>
            <p class="joms-media__desc"><?php echo JHTML::_('string.truncate', $album->description, $config->getInt('streamcontentlength') );?></p>
        </div>
    </div>
</div>

<?php $this->load('stream/footer'); ?>
