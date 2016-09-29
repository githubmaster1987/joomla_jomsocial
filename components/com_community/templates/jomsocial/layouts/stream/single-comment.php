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
?>

<div class="joms-comment__item joms-js--comment joms-js--comment-<?php echo $wall->id; ?>" data-id="<?php echo $wall->id; ?>" data-parent="<?php echo $wall->contentid; ?>" data-comment-id="<?php echo $wall->id; ?>">
    <div class="joms-comment__header">
        <div class="joms-avatar--comment <?php echo CUserHelper::onlineIndicator($user); ?>">
            <a href="<?php echo CUrlHelper::userLink($user->id); ?>">
                <img src="<?php echo $user->getThumbAvatar(); ?>" alt="<?php echo $user->getDisplayName(); ?>" data-author="<?php echo $user->id; ?>">
            </a>
        </div>
        <div class="joms-comment__body joms-js--comment-body">
            <a class="joms-comment__user" href="<?php echo CUrlHelper::userLink($user->id); ?>"><?php echo $user->getDisplayName(); ?></a>
            <span class="joms-js--comment-content"><?php

                echo CActivities::shorten($wall->comment, $wall->id, 0, $config->getInt('stream_comment_length'),'comment');



                ?></span>
            <?php if ( !empty( $photoThumbnail ) ) { ?>
            <div style="padding: 5px 0">
                <a href="javascript:" onclick="joms.api.photoZoom('<?php echo $photoThumbnail; ?>');">
                    <img class="joms-stream-thumb" src="<?php echo $photoThumbnail; ?>" alt="photo thumbnail" >
                </a>
            </div>
            <?php } else if ($paramsHTML) { ?>
            <?php echo $paramsHTML; ?>
            <?php } ?>

            <span class="joms-comment__time"><small><?php echo CTimeHelper::timeLapse($date); ?></small></span>

            <div class="joms-comment__actions joms-js--comment-actions">
                <?php

                    // this is for like button
                    if ($my->id) {
                        if ($isLiked != COMMUNITY_LIKE) {

                ?>

                        <a class="joms-button--liked" href="javascript:" onclick="joms.api.commentLike('<?php echo $wall->id; ?>');"
                                data-lang-like="<?php echo JText::_('COM_COMMUNITY_LIKE'); ?>"
                                data-lang-unlike="<?php echo JText::_('COM_COMMUNITY_UNLIKE'); ?>">
                            <svg viewBox="0 0 16 16" class="joms-icon">
                                <use xlink:href="#joms-icon-thumbs-up"></use>
                            </svg>
                            <span><?php echo JText::_('COM_COMMUNITY_LIKE') ?></span>
                        </a>

                <?php
                        } else if ($my->id) {
                ?>

                        <a class="joms-button--liked liked" href="javascript:" onclick="joms.api.commentUnlike('<?php echo $wall->id; ?>');"
                                data-lang-like="<?php echo JText::_('COM_COMMUNITY_LIKE'); ?>"
                                data-lang-unlike="<?php echo JText::_('COM_COMMUNITY_UNLIKE'); ?>">
                            <svg viewBox="0 0 16 16" class="joms-icon">
                                <use xlink:href="#joms-icon-thumbs-down"></use>
                            </svg>
                            <span><?php echo JText::_('COM_COMMUNITY_UNLIKE') ?></span>
                        </a>

                <?php
                        }
                    }
                ?>

                <?php
                    //display like if needed
                    if($likeCount > 0)
                    {?>
                        <a href="javascript:" class="liked" data-action="showlike" onclick="joms.api.commentShowLikes('<?php echo $wall->id; ?>');">
                            <i class="joms-icon-thumbs-up"></i><span><?php echo (!CFactory::getUser()->id) ? JText::sprintf( CStringHelper::isPlural($likeCount) ? 'COM_COMMUNITY_GUEST_LIKES' : 'COM_COMMUNITY_GUEST_LIKE' , $likeCount ) : $likeCount; ?></span></a>
                <?php } ?>

                <?php
                    if ($canEdit) {
                        ?>
                    <a href="javascript:" class="joms-button--edit" onclick="joms.api.commentEdit('<?php echo $wall->id; ?>', this);">
                        <svg viewBox="0 0 16 16" class="joms-icon"><use xlink:href="#joms-icon-pencil"></use></svg>
                        <span><?php echo JText::_('COM_COMMUNITY_EDIT'); ?></span>
                    </a>

                    <?php }?>

                <?php if($canRemove) { ?>
                    <a href="javascript:" class="joms-button--remove" onclick="joms.api.commentRemove('<?php echo $wall->id; ?>');">
                        <svg viewBox="0 0 16 16" class="joms-icon"><use xlink:href="#joms-icon-remove"></use></svg>
                        <span><?php echo JText::_('COM_COMMUNITY_WALL_REMOVE'); ?></span>
                    </a>
                <?php } ?>

                <?php if (CActivitiesHelper::hasTag($my->id, $originalComment)) { ?>
                    <a href="javascript:" class="joms-button--remove-tag"
                       onclick="joms.api.commentRemoveTag('<?php echo $wall->id; ?>');">
                        <svg viewBox="0 0 16 16" class="joms-icon">
                            <use xlink:href="#joms-icon-remove"></use>
                        </svg>
                        <span><?php echo JText::_('COM_COMMUNITY_WALL_REMOVE_TAG'); ?></span>
                    </a>
                <?php } ?>

            </div>
        </div>
    </div>
    <div class="joms-comment__reply joms-js--comment-editor">
        <div class="joms-textarea__wrapper" style="display:block">
            <div class="joms-textarea joms-textarea__beautifier"></div>
            <textarea class="joms-textarea" name="comment" data-id="<?php echo $wall->id; ?>" data-edit="1"
               <?php

                    // We need to do this because photo upload stream comments saved with reference to album->id, not stream->id.
                    if ( $wall->type === 'albums' ) {
                        echo ' data-tag-func="album" data-tag-id="' . $wall->contentid . '"';
                    } else if ( $wall->type === 'videos' ) {
                       echo 'data-tag-func="video" data-tag-id="' . $wall->contentid . '"';
                    }

                ?>
                placeholder="<?php echo JText::_('COM_COMMUNITY_WRITE_A_COMMENT'); ?>"><?php echo $originalComment; ?></textarea>
            <div class="joms-textarea__loading"><img src="<?php echo JURI::root(true); ?>/components/com_community/assets/ajax-loader.gif" alt="loader" ></div>
            <div class="joms-textarea joms-textarea__attachment"<?php echo $photoThumbnail ? ' style="display:block"' : ' data-no_thumb="1"' ?>>
                <button onclick="joms.view.comment.removeAttachment(this);"<?php echo $photoThumbnail ? ' style="display:block"' : '' ?>>×</button>
                <div class="joms-textarea__attachment--loading"><img src="<?php echo JURI::root(true); ?>/components/com_community/assets/ajax-loader.gif" alt="loader"></div>
                <div class="joms-textarea__attachment--thumbnail"<?php echo $photoThumbnail ? ' style="display:block"' : '' ?>>
                    <img<?php echo $photoThumbnail ? (' src="' . $photoThumbnail . '" data-photo_id="0"') : ' src="#"' ?> alt="Attachment">
                </div>
            </div>
        </div>
        <svg viewBox="0 0 16 16" class="joms-icon joms-icon--add" onclick="joms.view.comment.addAttachment(this);" style="right:24px">
            <use xlink:href="<?php echo JUri::getInstance(); ?>#joms-icon-camera"></use>
        </svg>
        <div style="text-align:right;margin-top:4px">
            <button class="joms-button--small joms-button--neutral" onclick="joms.view.comment.cancel('<?php echo $wall->id ?>');"><?php echo JText::_('COM_COMMUNITY_CANCEL'); ?></button>
            <button class="joms-button--small joms-button--primary joms-js--btn-send"><?php echo JText::_('COM_COMMUNITY_SEND'); ?></button>
        </div>
    </div>
</div>
