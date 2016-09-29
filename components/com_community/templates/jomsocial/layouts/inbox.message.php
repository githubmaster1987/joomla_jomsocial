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

$hide = isset($hide) && $hide;

?>

<div class="joms-js--inbox-item-<?php echo $msg->id; ?> <?php echo $hide ? 'joms-js--inbox-item-hidden' : '' ?>" <?php echo $hide ? 'style="display:none"' : ''; ?>>
    <div class="joms-list__item">
        <div class="joms-list--message__body">
            <div class="joms-comment__header">
                <div class="joms-avatar--stream <?php echo CUserHelper::onlineIndicator($user); ?>">
                    <a href="<?php echo $authorLink; ?>">
                        <img alt="<?php echo $user->getDisplayName(); ?>" src="<?php echo $user->getThumbAvatar(); ?>" data-author="<?php echo $user->id; ?>">
                    </a>
                </div>
                <div class="joms-comment__meta">
                    <span class="joms-comment__time">
                    <a href="<?php echo $authorLink; ?>"><strong><?php echo $user->getDisplayName(); ?></strong></a>
                        <small>
                            <?php echo CTimeHelper::timeLapse( CTimeHelper::getDate($msg->posted_on) ); ?>
                        </small>
                    </span>
                    <div class="joms-js--inbox-content">
                        <?php
                            echo $content;
                        ?>
                    </div>
                    <div>
                        <?php if ( CActivitiesHelper::hasTag( $my->id, $originalContent ) ) { ?>
                        <div class="joms-comment__actions">
                            <a href="javascript:" class="joms-button--remove-tag" onclick="joms.api.commentRemoveTag('<?php echo $msg->id; ?>', 'inbox');">
                                <svg viewBox="0 0 16 16" class="joms-icon"><use xlink:href="<?php echo CRoute::getURI(); ?>#joms-icon-tag"></use></svg>
                                <span><?php echo JText::_('COM_COMMUNITY_WALL_REMOVE_TAG'); ?></span>
                            </a>
                        </div>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="joms-messages__actions joms-list--message__remove">
            <a onclick="joms.api.commentRemove('<?php echo $msg->id; ?>', 'inbox');" href="javascript:" class="joms-button--neutral joms-button--smallest">
                <svg class="joms-icon" viewBox="0 0 16 16">
                    <use xlink:href="<?php echo CRoute::getURI(); ?>#joms-icon-close"></use>
                </svg>
            </a>
        </div>
    </div>
    <div class="joms-list--message__media">
        <?php if ( $params->get('file_id') && isset($file) && $file->id ) {

            $filename = $file->name;
            $filepath = $file->filepath;
            $storage		= CStorage::getStorage( $file->storage );
            $fileext  = strrpos( $filepath, '.' );
            if ( $fileext !== false ) {
                $filename .= substr( $filepath, $fileext );
            }

        ?>

            <div class="joms-js--file-<?php echo $file->id; ?>">
                <ul class="joms-list--files">
                    <li class="joms-list__item">

                        <div class="joms-stream__header">
                            <div class="joms-avatar--comment">
                                <svg viewBox="0 0 16 16" class="joms-icon joms-icon--responsive">
                                    <use xlink:href="<?php echo CRoute::getURI(); ?>#joms-icon-file-zip" class="joms-icon--svg-fixed"></use>
                                </svg>
                            </div>
                            <div class="joms-stream__meta">
                                <h4 class="reset-gap"><a href="<?php echo $storage->getURI($filepath); ?>" target="_blank" title="<?php echo $file->name; ?>">
                                    <?php echo $filename; ?>
                                </a></h4>
                                <small class="joms-text--light">
                                    <?php echo round($file->filesize/1048576,2) . 'MB'; ?>
                                    <a href="javascript:" class="joms-button--link" onclick="joms.api.fileRemove('message', '<?php echo $file->id; ?>');">
                                    <?php echo JText::_('COM_COMMUNITY_DELETE'); ?>
                                    </a>
                                </small>
                            </div>
                        </div>
                    </li>
                </ul>
            </div>

        <?php } else if ( $photoThumbnail ) { ?>

            <div class="joms-stream-box joms-fetch-wrapper"
                style="display: inline-block; padding: 5px; position: relative">
                <?php if ( $msg->from == $my->id ) { ?>
                    <span class="joms-fetched-close" style="top:0;right:0;left:auto"
                        onclick="joms.api.commentRemoveThumbnail('<?php echo $msg->id; ?>', 'inbox');"><i class="joms-icon-remove">&times;</i></span>
                <?php } ?>

                <a href="javascript:" onclick="joms.api.photoZoom('<?php echo $photoThumbnail; ?>');">
                    <img src="<?php echo $photoThumbnail; ?>" alt="photo thumbnail" >
                </a>

            </div>

        <?php } else if ( $params->get('url') ) { ?>

            <?php
            $href = 'href="' . $params->get('url') . '" target="_blank"';

            $image = $params->get('image');
            if ($image && count($image) >= 1) {
                $image = $image[0];
            } else {
                $image = false;
            }

            $cite = $params->get('url');
            $cite = preg_replace('#^https?://#', '', $cite);
            $cite = preg_replace('#/$#', '', $cite);


            // @TODO: DRY
            $video = JTable::getInstance('Video', 'CTable');
            if($video->init($params->get('url'))) {
                $video->isValid();
            } else {
                $video = false;
            }

            if($config->get('enable_embedly')){ ?>
                <a href="<?php echo $params->get('url'); ?>" class="embedly-card" data-card-controls="0" data-card-recommend="0" data-card-theme="<?php echo $config->get('enable_embedly_card_template'); ?>" data-card-align="<?php echo $config->get('enable_embedly_card_position') ?>"><?php echo JText::_('COM_COMMUNITY_EMBEDLY_LOADING');?></a>
            <?php }elseif (is_object($video)) {

            ?>

            <div class="joms-media--video joms-js--video"
                    data-type="<?php echo $video->type; ?>"
                    data-id="<?php echo $video->video_id; ?>"
                    data-path="<?php echo ($video->type == 'file') ? CStorage::getStorage($video->storage)->getURI($video->path) : $video->path; ?>"
                    style="margin-top:10px;">

                <div class="joms-media__thumbnail">
                    <img src="<?php echo $video->getThumbnail(); ?>" alt="<?php echo $video->title; ?>" >
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

            <?php } else { ?>

            <div class="joms-stream-box joms-fetch-wrapper clearfix" style="position: relative">

                <?php if ($msg->from == $my->id) { ?>
                    <span class="joms-fetched-close" style="top:0;right:0;left:auto"
                          onclick="joms.api.commentRemovePreview('<?php echo $msg->id; ?>', 'inbox');"><i
                            class="joms-icon-remove"></i></span>
                <?php } ?>

                <div style="position:relative;">
                    <div class="row-fluid">
                        <?php if ($image) { ?>
                            <div class="span4">
                                <a <?php echo $href ?> onclick="joms.api.photoZoom('<?php echo $image; ?>');">
                                    <img class="joms-stream-thumb" src="<?php echo $image; ?>" alt="photo thumbnail" />
                                </a>
                            </div>
                        <?php } ?>
                        <div class="span<?php echo $image ? '8' : '12' ?>">
                            <article class="joms-stream-fetch-content" style="margin-left:0; padding-top:0">
                                <a <?php echo $href ?>><span
                                        class="joms-stream-fetch-title"><?php echo $params->get('title'); ?></span></a>
                                <span
                                    class="joms-stream-fetch-desc"><?php echo CStringHelper::trim_words($params->get('description')); ?></span>
                                <cite><?php echo $cite; ?></cite>
                            </article>
                        </div>
                    </div>
                </div>
            </div>

            <?php } ?>
        <?php } ?>

    </div>
</div>
