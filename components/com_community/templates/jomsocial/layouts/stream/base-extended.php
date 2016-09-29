<?php
/**
 * @copyright (C) 2013 iJoomla, Inc. - All rights reserved.
 * @license GNU General Public License, version 2 (http://www.gnu.org/licenses/gpl-2.0.html)
 * @author iJoomla.com <webmaster@ijoomla.com>
 * @url https://www.jomsocial.com/license-agreement
 * The PHP code portions are distributed under the GPL license. If not otherwise stated, all images, manuals, cascading style sheets, and included JavaScript *are NOT GPL, and are released under the IJOOMLA Proprietary Use License v1.0
 * More info at https://www.jomsocial.com/license-agreement
 */
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

if (!isset($stream->groupid)) {
    $stream->groupid = '';
}
?>

<?php if (is_object($stream->actor)): ?>

<div class="joms-stream__header">
    <div class="joms-avatar--stream <?php echo CUserHelper::onlineIndicator($stream->actor); ?>">
        <a href="<?php echo ((int)$stream->actor->id !== 0) ? CUrlHelper::userLink(
            $stream->actor->id
        ) : 'javascript:void(0);'; ?>">
            <img data-author="<?php echo $stream->actor->id; ?>"
                 src="<?php echo $stream->actor->getThumbAvatar(); ?>" alt="<?php echo $stream->actor->getDisplayName(); ?>">
        </a>
    </div>

<?php else: ?>

<div class="joms-stream__header">
    <div class="joms-avatar--stream <?php echo CUserHelper::onlineIndicator($stream->actor); ?>">
        <img src="components/com_community/assets/user-Male-thumb.png" alt="male" data-author="<?php echo $stream->actor->id; ?>" />
    </div>

<?php endif; ?>

    <div class="joms-stream__meta">
        <?php echo $stream->headline; ?>
        <span class="joms-stream__time">
            <small>
                <?php echo(isset($stream->createdtime) ? $stream->createdtime : ''); ?>
            </small>

            <?php
                if(isset($stream->type) && $stream->type != 'discussion_reply'){ // do not show the privacy icon
                    $this->load('/privacy/show');
                }
            ?>

        </span>
    </div>

    <?php
        $my = CFactory::getUser();
        $this->load('activities.stream.options');
    ?>

</div>

<div class="joms-stream__body">

<?php if ($stream->message) {?>

    <?php if (isset($stream->sharedMessage)) {?>
    <div data-type="stream-editor" class="cStream-Respond" style="display:none">
        <textarea class="joms-textarea" style="margin-bottom:0"><?php echo $stream->message; ?></textarea>
        <div style="text-align:right;">
            <button class="joms-button--neutral joms-button--small" onclick="joms.view.stream.cancel('<?php echo $act->id; ?>');"><?php echo JText::_('COM_COMMUNITY_CANCEL'); ?></button>&nbsp;
            <button class="joms-button--primary joms-button--small" onclick="joms.view.stream.save('<?php echo $act->id; ?>', this);"><?php echo JText::_('COM_COMMUNITY_SAVE'); ?></button>
        </div>
    </div>
    <?php } ?>

    <p data-type="stream-content"><?php echo CStringHelper::getMood($stream->message, isset($stream->mood) ? $stream->mood : null); ?></p>
<?php } ?>

<?php
if (!empty($stream->attachments)) {
    $i=0;

    //we should quote this if this is a shared content, stream that has sharedMessage indicates that this is a shared stream
    if(isset($stream->sharedMessage)){
        echo '<div class="cStream-Quote">';
    }

    foreach ($stream->attachments as $attachment) {
        $i++;

        switch ($attachment->type) {
            case 'media':
                ?>
                <div class="cStream-Attachment">
                    <div class="joms-stream-single-photo clearfix">
                        <?php if (isset($attachment->thumbnail) && !is_array($attachment->thumbnail)) { ?>
                            <a href="javascript:" onclick="joms.api.photoZoom('<?php echo JURI::root() . $attachment->thumbnail; ?>');">
                                <img src="<?php echo JURI::root() . $attachment->thumbnail; ?>" alt="photo">
                            </a>
                        <?php
                        } else {
                            if (count($attachment->thumbnail) >= 5) {
                                ?>
                                <div class="joms-stream-multi-photo-hero">
                                    <a href="#">
                                        <img src="<?php echo $attachment->thumbnail[0]; ?>" alt="photo" />
                                    </a>
                                </div>

                                <?php
                                unset($attachment->thumbnail[0]);
                            } ?>
                            <div class="joms-stream-multi-photo row-fluid">
                                <?php foreach ($attachment->thumbnail as $key => $thumb) { ?>
                                    <div class="span3">
                                        <a href="<?php echo $attachment->link[$key]; ?>"><img
                                                src="<?php echo (isset($thumb)) ? $thumb : ''; ?>" alt="photo"></a>
                                    </div>
                                <?php } ?>
                            </div>
                        <?php } ?>
                    </div>
                </div>
                <?php
                break;

            case 'photo':

                $isPhotoModal = $config->get('album_mode') == 1;
                $link = $attachment->link;

                if ( $isPhotoModal ) {
                    $link = 'javascript:" onclick="joms.api.photoOpen(\'' . $attachment->albumid . '\', \'' . $attachment->id . '\');';
                }

                ?>

                <?php if (isset($attachment->link)) { ?>
                <a href="<?php echo $link; ?>">
                    <div class="joms-media--image--half">
                        <img src="<?php echo $attachment->singlephoto; ?>" alt="<?php echo $this->escape($attachment->caption); ?>" />
                    </div>
                </a>
                <?php } else { ?>
                    <div class="cEmpty small joms-rounded">
                        <?php echo JText::_('COM_COMMUNITY_PHOTO_REMOVED'); ?>
                    </div>
                <?php } ?>

                <?php
                break;

            case 'photos':

                $isPhotoModal = $config->get('album_mode') == 1;

                ?>
                <div class="joms-media--images">
                <?php foreach ($attachment->thumbnail as $idx => $thumb) { ?>
                    <a
                        <?php if ($isPhotoModal) { ?>
                        href="javascript:" onclick="joms.api.photoOpen('<?php echo $attachment->album[$idx]; ?>', '<?php echo $attachment->id[$idx]; ?>');"
                        <?php } else { ?>
                        href="<?php echo $attachment->link[$idx] ?>"
                        <?php } ?>
                    >
                        <img src="<?php echo (isset($thumb)) ? $thumb : ''; ?>"
                            alt="<?php echo $this->escape( $attachment->caption[$idx] ); ?>">
                    </a>
                <?php } ?>
                </div>

                <div class="joms-media--loading">
                    <div class="cEmpty small joms-rounded">
                        <?php echo JText::_('COM_COMMUNITY_PHOTOS_BEING_LOADED'); ?>
                    </div>
                </div>

                <?php
                break;

            case 'cover':
                ?>
                <div class="cStream-Attachment">
                    <div class="clearfix">
                        <?php if (isset($attachment->thumbnail) && !is_array($attachment->thumbnail)) { ?>
                            <a href="#"><img class="joms-stream-single-photo"
                                             src="<?php echo (isset($attachment->thumbnail)) ? $attachment->thumbnail : ''; ?>"
                                             alt="photo"></a>
                        <?php } ?>
                    </div>
                </div>
                <?php
                break;

            case 'album':
                ?>
                <div class="cStream-Attachment">
                    <div class="cStream-Photo">
                        <div class="cStream-PhotoRow row-fluid">
                            <div class="span3">
                                <a class="cPhoto-Thumb" href="#"><img
                                        src="<?php echo (isset($attachment->thumbnail)) ? $attachment->thumbnail : ''; ?>"
                                        alt="photo"></a>
                            </div>
                        </div>
                    </div>
                </div>

                <?php
                break;

            case 'video':
                if ( isset($attachment) && isset($attachment->video) ) {
                    $video = $attachment->video;
                }

                if ( isset($video) ) {
                    if($video->type ==='file'){
                        $storage    = CStorage::getStorage($video->storage);
                        $path = $storage->getURI($video->path);
                    } else {
                        $path = $video->path;
                    }
                ?>

                <div class="joms-media--video joms-js--video"
                        data-type="<?php echo $video->type; ?>"
                        data-id="<?php echo $video->video_id; ?>"
                        data-path="<?php echo $path ?>">

                    <div class="joms-media__thumbnail">
                        <img src="<?php echo $video->getThumbnail(); ?>" alt="<?php echo $attachment->title; ?>" >
                        <a href="javascript:" class="mejs-overlay mejs-layer mejs-overlay-play joms-js--video-play joms-js--video-play-<?php echo $act->id ?>">
                            <div class="mejs-overlay-button"></div>
                        </a>
                    </div>
                    <div class="joms-media__body">
                        <h4 class="joms-media__title">
                            <?php echo JHTML::_('string.truncate', $attachment->title, 50, true, false); ?>
                        </h4>
                        <p class="joms-media__desc">
                            <?php echo JHTML::_('string.truncate', $attachment->description, $config->getInt('streamcontentlength'), true, false); ?>
                        </p>
                    </div>

                </div>

                <?php

                }

                break;

            case 'quote':
                if (strlen($attachment->message)):
                    ?>
                    <div class="cStream-Attachment">
                        <p><?php //echo isset($stream->actor->_status) ? CActivities::format($stream->actor->_status) : ''; ?></p>
                        <div class="joms-quote joms-text--desc">
                            <?php echo CActivities::shorten($attachment->message, $attachment->id . '0000' . $stream->actor->id, 0, $config->getInt('streamcontentlength')); ?>
                            <?php if (!empty($attachment->location) && isset($attachment->id)) { //show location if needed?>
                                <span class="joms-status-location"> -
                                    <a href="javascript:" onclick="joms.api.locationView('<?php echo $attachment->id; ?>');">
                                        <?php echo $attachment->location; ?>
                                    </a>
                                </span>
                            <?php } ?>
                        </div>
                    </div>
                <?php
                endif;
                break;

            case 'discussion_reply':
                // @TODO: DRY
                $video = JTable::getInstance('Video', 'CTable');
                if($video->init($params->get('url'))) {
                    $video->isValid();
                } else {
                    $video = false;
                }

                ?>
                <div class="cStream-Attachment">
                    <div class="cStream-Quote">
                        <p><?php echo CActivities::format($attachment->message); ?></p>

                        <?php if (is_object($video) && !$config->get('enable_embedly')) { ?>
                        <div class="joms-media--video joms-js--video"
                                data-type="<?php echo $video->type; ?>"
                                data-id="<?php echo $video->video_id; ?>"
                                data-path="<?php echo ($video->type == 'file') ? CStorage::getStorage($video->storage)->getURI($video->path) : $video->path; ?>"
                                style="margin-top:10px;">
                            <div class="joms-media__thumbnail">
                                <img src="<?php echo $video->getThumbnail(); ?>" alt="<?php echo $video->title; ?>">
                                <a href="javascript:" class="mejs-overlay mejs-layer mejs-overlay-play joms-js--video-play">
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
                        <?php }elseif($video && $config->get('enable_embedly')){ ?>
                            <a href="<?php echo $params->get('url'); ?>" class="embedly-card" data-card-controls="0" data-card-recommend="0" data-card-theme="<?php echo $config->get('enable_embedly_card_template'); ?>" data-card-align="<?php echo $config->get('enable_embedly_card_position') ?>"><?php echo JText::_('COM_COMMUNITY_EMBEDLY_LOADING');?></a>
                        <?php } ?>

                        <?php if ($attachment->photoThumbnail) { ?>
                            <img src="<?php echo $attachment->photoThumbnail; ?>" alt="image-attachment">
                        <?php } ?>
                    </div>
                </div>

                <?php
                break;

            case 'text':
                ?>
                <p>
                    <?php $title = $attachment->message;

                    echo CActivities::shorten($title, $attachment->activity->get('id'), 0, $config->getInt('streamcontentlength'));

                    ?>
                    <?php if ($attachment->address) { ?>
                        <span class="joms-status-location"> -
                            <a href="javascript:" onclick="joms.api.locationView('<?php echo $attachment->activity->get('id'); ?>');">
                                <?php echo $attachment->address; ?>
                            </a>
                        </span>
                    <?php } ?>
                </p>
                <?php
                break;

            case 'create_discussion':
                ?>

                <div class="joms-media">
                    <a href="<?php echo $stream->link; ?>"><?php echo $stream->title; ?></a>
                    <p><?php echo CActivities::format($attachment->message); ?></p>
                    <div class="content-details"><?php echo $stream->group->name ?></div>
                </div>

                <?php
                break;

            case 'create_announcement':
                ?>

                <div class="joms-media">
                    <h4 class="joms-text--title"><a href="<?php echo $stream->link; ?>"><?php echo $stream->title; ?></a></h4>
                        <p class="joms-text--desc"><?php echo CActivities::format($attachment->message); ?></p>
                        <?php echo $stream->group->name ?>
                </div>

                <?php
                break;
            case 'event_share':
                ?>

                <div class="joms-media">
                    <h4 class="joms-text--title"><a href="<?php echo $attachment->message->getLink(); ?>"><?php echo JHTML::_('string.truncate', $attachment->message->title, 60,true,false); ?></a></h4>
                    <p><?php echo $attachment->message->summary ?></p>

                    <?php $format = ($config->get('eventshowampm')) ? JText::_('COM_COMMUNITY_DATE_FORMAT_LC2_12H') : JText::_('COM_COMMUNITY_DATE_FORMAT_LC2_24H'); ?>

                    <ul class="joms-list">
                        <li class="joms-list__item">
                            <svg viewBox="0 0 16 18" class="joms-icon">
                                <use xlink:href="<?php echo CRoute::getURI(); ?>#joms-icon-calendar"></use>
                            </svg>
                            <?php echo CTimeHelper::getFormattedTime($attachment->message->startdate,$format,false); ?></li>
                        <li class="joms-list__item">
                            <svg viewBox="0 0 16 16" class="joms-icon">
                                <use xlink:href="<?php echo CRoute::getURI(); ?>#joms-icon-location"></use>
                            </svg>
                        <?php echo $attachment->message->location; ?></li>
                    </ul>
                </div>

                <?php
                break;
            case 'group_share':
                ?>

                <div class="joms-media">
                    <h4 class="joms-text--title"><a href="<?php echo $attachment->message->link; ?>"><?php echo JHTML::_('string.truncate', $attachment->message->title, 60, true, false); ?></a></h4>
                    <span><?php echo JHTML::_('string.truncate',strip_tags($attachment->message->description) , $config->getInt('streamcontentlength')); ?></span>
                </div>
                <?php
                break;
            case 'fetched':
                //this is the fecthed content from url
                echo CStringHelper::getMood($stream->sharedMessage, $stream->sharedMood);
                echo $attachment->message;
                break;
            case 'share.groups.discussion':
                ?>
                <div class="joms-media">
                    <h4 class="joms-text--title"><a  href="<?php echo $attachment->link; ?>"><?php echo $attachment->discussion_title; ?></a></h4>
                    <p class="joms-text--desc"><?php echo $attachment->discussion_message; ?></p>
                    <span><?php echo $attachment->group_name ?></span>
                </div>
                <?php
                break;
            case 'share.groups.discussion.reply':
                ?>
                <div class="cStream-Attachment">
                    <div class="cStream-Quote">
                        <?php echo $attachment->comment; ?>
                    </div>
                </div>
                <?php
                break;
            case 'profile_avatar' :
                ?>
                <div class="cStream-Attachment">
                    <?php if (isset($attachment->thumbnail) && !is_array($attachment->thumbnail)) { ?>
                        <a href="javascript:" onclick="joms.api.photoZoom('<?php echo JURI::root() . $attachment->thumbnail; ?>');">
                            <img src="<?php echo JURI::root() . $attachment->thumbnail; ?>" alt="photo">
                        </a>
                    <?php } ?>
                </div>
                <?php
                break;
            case 'general': ?>
                <div class="cStream-Attachment <?php echo (isset($attachment->showInQuote) && $attachment->showInQuote) ? 'cStream-Quote' : '' ; ?>">
                    <?php echo $attachment->content; ?>
                </div>
            <?php
                break;
            default:
                # code...
                break;
        }
    } // end foreach
    //we should quote this if this is a shared content, stream that has sharedMessage indicates that this is a shared stream
    if(isset($stream->sharedMessage)){
        echo '</div>';
    }
} // end if

?>

</div>

<?php $this->load('stream/footer'); ?>

