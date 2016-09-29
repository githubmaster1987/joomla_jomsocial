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
$user = CFactory::getUser($this->act->actor);
$date = JDate::getInstance($act->created);
if ($config->get('activitydateformat') == "lapse") {
    $createdTime = CTimeHelper::timeLapse($date);
} else {
    $createdTime = $date->format($config->get('profileDateFormat'));
}

$photo = $my->getAvatarInfo();
$isPhotoModal = $config->get('album_mode') == 1;

?>

<div class="joms-stream__header">
    <div class= "joms-avatar--stream <?php echo CUserHelper::onlineIndicator($user); ?>">
        <a href="<?php echo CUrlHelper::userLink($user->id); ?>">
            <img data-author="<?php echo $user->id; ?>" src="<?php echo $user->getThumbAvatar(); ?>" alt=
            "<?php echo $user->getDisplayName(); ?>">
        </a>
    </div>
    <div class="joms-stream__meta">
        <a href="<?php echo CUrlHelper::userLink($user->id); ?>" class="joms-stream__user">
            <?php echo $user->getDisplayName(); ?>
        </a>

        <?php echo JText::_('COM_COMMUNITY_ACTIVITIES_NEW_AVATAR'); ?>
        <span class="joms-stream__time">
            <small><?php echo $createdTime; ?></small>
        </span>
    </div>
</div>

<div class="joms-stream__body">

<?php

    $avatarPath = $act->params->get('attachment');
    $avatarPath = rtrim( JURI::root(), '/' ) . '/' . $avatarPath;
    $photoId = $act->params->get('photo_id', 0);
    $albumId = $act->params->get('album_id', 0);

    if ($albumId && $photoId) {
        $photoTable = JTable::getInstance('Photo', 'CTable');
        $photoTable->load($photoId);
        $thumbnailPath = $photoTable->getThumbURI();

?>
    <div class="joms-avatar">
        <a href="javascript:" onclick="joms.api.photoZoom('<?php echo $thumbnailPath; ?>');">
            <img src="<?php echo $photoTable->getThumbURI(); ?>" alt="<?php echo $user->getDisplayName(); ?>" >
        </a>
    </div>
<?php } else { ?>
    <div class="joms-avatar">
        <?php if ($photo) { ?><a href="javascript:" onclick="joms.api.photoZoom('<?php echo $avatarPath; ?>');"><?php } ?>
            <img src="<?php echo $avatarPath; ?>" alt="<?php echo $user->getDisplayName(); ?>">
        <?php if ($photo) { ?></a><?php } ?>
    </div>
<?php } ?>

</div>

<?php $this->load('stream/footer'); ?>
