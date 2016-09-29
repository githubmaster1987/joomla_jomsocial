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
    $params = $this->act->params;
    $type = $params->get('type');

    $date = JDate::getInstance($act->created);
    if ($config->get('activitydateformat') == "lapse") {
        $createdTime = CTimeHelper::timeLapse($date);
    } else {
        $createdTime = $date->format($config->get('profileDateFormat'));
    }
    $url = CUrlHelper::userLink($user->id);
    $messageHTML = '';
    $extraMessage = '';
    if (strtolower($type) !== 'profile') {
        $id = $type . 'id';
        if ($type == 'group' || $type == 'event') {
            $cTable = JTable::getInstance(ucfirst($type), 'CTable');
            if ($cTable) { /* Make sure we had correct cTable */
                $cTable->load($this->act->$id);
                if ($type == 'group') {
                    $extraMessage = ', <a href="' . $cTable->getLink() . '">' . $cTable->name . '</a>';
                    $url = $cTable->getLink();
                }
                if ($type == 'event') {
                    $extraMessage = ', <a href="' . CUrlHelper::eventLink($cTable->id) . '">' . $cTable->title . '</a>';
                    $url = CUrlHelper::eventLink($cTable->id);
                }
            } else {
                $extraMessage = '';
            }
        }
        $messageHTML = JText::sprintf(
                'COM_COMMUNITY_PHOTOS_COVER_UPLOAD',
                strtolower(Jtext::_('COM_COMMUNITY_COVER_' . strtoupper($type)))
            ) . $extraMessage;
    } else {
        $messageHTML = JText::_('COM_COMMUNITY_PHOTOS_COVER_UPLOAD_PROFILE');
    }
    /**
     * Get cover path
     */
    $coverPath = $params->get('attachment');

    if (!file_exists($coverPath)) {
        $s3 = CStorage::getStorage('s3');
        $coverPath = $s3->getURI($coverPath);
    } else {
        $coverPath = JURI::root() . $coverPath;
    }

    $isPhotoModal = $config->get('album_mode') == 1;

?>

<div class="joms-stream__header">
    <div class= "joms-avatar--stream <?php echo CUserHelper::onlineIndicator($user); ?>">
        <a href="<?php echo CUrlHelper::userLink($user->id); ?>">
            <img data-author="<?php echo $user->id; ?>" src="<?php echo $user->getThumbAvatar(); ?>" alt="<?php echo $user->getDisplayName(); ?>" >
        </a>
    </div>
    <div class="joms-stream__meta">
        <a class="joms-stream__user" href="<?php echo CUrlHelper::userLink($user->id); ?>"><?php echo $user->getDisplayName(); ?></a>
        <?php echo $messageHTML; ?>
        <a href="<?php echo CRoute::_('index.php?option=com_community&view=profile&userid='.$act->actor.'&actid='.$act->id); ?>">
        <span class="joms-stream__time">
            <small><?php echo $createdTime; ?></small>
        </span>
        </a>
    </div>
    <?php
        $my = CFactory::getUser();
        $this->load('activities.stream.options');
    ?>
</div>

<div class="joms-stream__body">
    <?php
        $photoId = $act->params->get('photo_id',0);
        $albumId = $act->params->get('album_id',0);
        if($albumId && $photoId) {

    ?>
        <a
            <?php if ($isPhotoModal) { ?>
            href="javascript:" onclick="joms.api.photoOpen('<?php echo $albumId; ?>', '<?php echo $photoId; ?>');"
            <?php } else { ?>
            href="<?php echo CRoute::_('index.php?option=com_community&view=photos&task=photo&albumid=' . $albumId . '&photoid=' . $photoId); ?>"
            <?php } ?>
        >
            <div class="joms-media--image">
                <img src="<?php echo $coverPath; ?>" alt="<?php echo $albumId; ?>" >
            </div>
        </a>
    <?php } else { ?>
        <a href="javascript:" onclick="joms.api.photoZoom('<?php echo $coverPath; ?>');">
            <div class="joms-media--image">
                <img src="<?php echo $coverPath; ?>" alt="<?php echo $albumId; ?>" >
            </div>
        </a>
    <?php } ?>
</div>

<?php $this->load('stream/footer'); ?>
