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

$param = new CParameter($this->act->params);

$user = CFactory::getUser($this->act->actor);
$video = JTable::getInstance('Video', 'CTable');
$video->load($this->act->cid);

$videoUrl = $video->getURL();
$isVideoModal = $config->get('video_mode') == 1;
if ( $isVideoModal ) {
    $videoUrl = 'javascript:" onclick="joms.api.videoOpen(\'' . $video->getId() . '\');';
}

$date = JDate::getInstance($act->created);
if ( $config->get('activitydateformat') == "lapse" ) {
  $createdTime = CTimeHelper::timeLapse($date);
} else {
  $createdTime = $date->format($config->get('profileDateFormat'));
}

?>

<div class="joms-stream__header">
    <div class= "joms-avatar--stream <?php echo CUserHelper::onlineIndicator($user); ?>">
        <a href="<?php echo CUrlHelper::userLink($user->id); ?>">
            <img data-author="<?php echo $user->id; ?>" src="<?php echo $user->getThumbAvatar(); ?>" alt="<?php echo $user->getDisplayName(); ?>" >
        </a>
    </div>
    <div class="joms-stream__meta">
        <?php echo JText::sprintf('COM_COMMUNITY_VIDEOS_IS_FEATURED', '<a href="' .  $videoUrl . '" class="cStream-Title">' . $this->escape($video->title) . '</a>'); ?>
        <span class="joms-stream__time"><?php echo $createdTime; ?></span>
    </div>
</div>

<div class="joms-stream__body">
    <div class="joms-media--video joms-js--video"
            data-type="<?php echo $video->type; ?>"
            data-id="<?php echo $video->video_id; ?>"
            data-path="<?php echo ($video->type == 'file') ? CStorage::getStorage($video->storage)->getURI($video->path) : $video->path; ?>" >

        <div class="joms-media__thumbnail">
            <img src="<?php echo $video->getThumbnail(); ?>" alt="<?php echo $video->title; ?>" >
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

<?php
    $this->act->isFeatured = true;
    $this->load('stream/footer');
?>
