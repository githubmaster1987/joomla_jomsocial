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
    $action = $param->get('action');

    if (is_array($param->get('actors'))) {
        $users = $param->get('actors');
        $user = CFactory::getUser($users[0]);
    } else { //backwards compatibility
        $users = array($this->act->actor);
        $user = CFactory::getUser($this->act->actor);
    }

    $wall = JTable::getInstance('Wall', 'CTable');
    $wall->load($param->get('wallid'));

    $photo = JTable::getInstance('Photo', 'CTable');
    $photo->load($act->cid);

    $url = $photo->getPhotoLink();

    $date = JDate::getInstance($act->created);
    if ($config->get('activitydateformat') == "lapse") {
        $createdTime = CTimeHelper::timeLapse($date);
    } else {
        $createdTime = $date->format($config->get('profileDateFormat'));
    }

    $photo_info = $photo->getInfo();
    $photo_size = $photo_info['size'];

    if ($photo->permissions == 30 && !CFriendsHelper::isConnected($my->id, $photo->creator)) {
        return false;
    }

    //generate activity based on the photo owner
    $ownerUrl = CUrlHelper::userLink($act->actor);
    $target = CFactory::getUser($photo->creator);
    $targetName = $user->getDisplayName();
    $targetUrl = CUrlHelper::userLink($photo->creator);

    $isPhotoModal = $config->get('album_mode') == 1;

    $attr = CRoute::_('index.php?option=com_community&view=photos&task=photo&albumid=' . $photo->albumid . '&photoid=' . $photo->id);
    if ( $isPhotoModal ) {
        $attr = 'javascript:" onclick="joms.api.photoOpen(\'' . $photo->albumid . '\', \'' . $photo->id . '\');';
    }

    if (count($users) > 1) {
        if(count($users) == 2){
            $activityString = JText::sprintf('COM_COMMUNITY_ACTIVITIES_COMMENT_OTHERS_PHOTO_TWO',
                CUrlHelper::userLink($user->id), $user->getDisplayName(),
                CUrlHelper::userLink($users[count($users)-1]), CFactory::getUser($users[count($users)-1])->getDisplayName(), $targetUrl,
                $target->getDisplayName(), $attr);
        }else{
            $activityString = JText::sprintf('COM_COMMUNITY_ACTIVITIES_COMMENT_OTHERS_PHOTO_MORE',
                CUrlHelper::userLink($user->id), $user->getDisplayName(),
                'onclick="joms.api.streamShowOthers(' . $act->id . ');return false;"', count($users) - 1, $targetUrl,
                $target->getDisplayName(), $attr);
        }
        // Commented on own photo.
    } else {
        if ($target->id == $user->id) {
            $activityString = JText::sprintf(
                'COM_COMMUNITY_ACTIVITIES_COMMENT_OWN_PHOTO',
                $targetUrl,
                $target->getDisplayName(),
                $attr
            );

            // Commented on other photo.
        } else {
            $activityString = JText::sprintf(
                'COM_COMMUNITY_ACTIVITIES_COMMENT_OTHERS_PHOTO',
                $ownerUrl,
                $user->getDisplayName(),
                $targetUrl,
                $target->getDisplayName(),
                $attr
            );

        }
    }

?>

<div class="joms-stream__header">
    <div class= "joms-avatar--stream <?php echo CUserHelper::onlineIndicator($user); ?>">
        <a href="<?php echo CUrlHelper::userLink($user->id); ?>">
            <img data-author="<?php echo $user->id; ?>" src="<?php echo $user->getThumbAvatar(); ?>" alt=
            "<?php echo $user->getDisplayName(); ?>">
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

    <a
        <?php if ($isPhotoModal) { ?>
        href="javascript:" onclick="joms.api.photoOpen('<?php echo $photo->albumid; ?>', '<?php echo $photo->id; ?>');"
        <?php } else { ?>
        href="<?php echo CRoute::_('index.php?option=com_community&view=photos&task=photo&albumid=' . $photo->albumid . '&photoid=' . $photo->id); ?>"
        <?php } ?>
    >
        <div class="joms-media--image" data-column="">
            <img src="<?php echo $photo->getImageURI(); ?>" alt="<?php echo $photo->caption; ?>" />
        </div>
    </a>
</div>

<?php $this->load('stream/footer'); ?>
