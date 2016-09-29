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

<?php if ($allowComment || $allowLike || $showLike) { ?>

    <div class="joms-comment joms-js--comments joms-js--comments-<?php echo $act->id; ?>" data-id="<?php echo $act->id; ?>">
        <?php

        $commentDiff = $act->commentCount - $config->get('stream_default_comments');
        if ($commentDiff > 0) { ?>
            <div class="joms-comment__more joms-js--more-comments">
                <a href="javascript:" data-lang="<?php echo JText::_("COM_COMMUNITY_SHOW_PREVIOUS_COMMENTS") . ' (%d)'  ?>"><?php
                    echo JText::_("COM_COMMUNITY_SHOW_PREVIOUS_COMMENTS") . ' (' . $commentDiff . ')'; ?></a>
            </div>
        <?php } ?>

        <?php if ($act->commentCount > 0) { ?>
            <?php

            #echo $act->commentLast;

            $comments = $act->commentsAll;
            #echo "<pre>";var_dump($comments);die();
            #$comments = $comments[$act->id];

            $commentLimit = $config->get('stream_default_comments');
            $comments = array_reverse($comments);

            if($act->commentCount > $commentLimit) {
                $comments = array_slice($comments, sizeof($comments) - $commentLimit, $commentLimit);
            }
            CWall::triggerWallComments($comments, false);
            foreach($comments as $comment) {
                $comment->params		= new CParameter($comment->params);
                echo CWall::formatComment($comment);
            }

            ?>
        <?php } ?>
    </div>

    <?php if ($allowComment) : ?>
        <div class="joms-comment__reply joms-js--newcomment joms-js--newcomment-<?php echo $act->id; ?>" data-id="<?php echo $act->id; ?>">
            <div class="joms-textarea__wrapper">
                <div class="joms-textarea joms-textarea__beautifier"></div>
                <textarea class="joms-textarea" name="comment" data-id="<?php echo $act->id; ?>"
                    <?php

                        // We need to do this because photo upload stream comments saved with reference to album->id, not stream->id.
                        if ( $act->app === 'photos' ) {
                            $photos = array();
                            if ( $act->params ) {
                                $photos = $act->params->get('photosId');
                                $photos = explode(',', $photos);
                            }
                            if ( count($photos) === 1 ) {
                                echo 'data-tag-func="photo" data-tag-id="' . $photos[0] . '"';
                            } else {
                                echo 'data-tag-func="album" data-tag-id="' . $act->cid . '"';
                            }
                        } else if ( $act->app === 'videos.linking' ) {
                            echo 'data-tag-func="video" data-tag-id="' . $act->cid . '"';
                        }

                    ?>
                    placeholder="<?php echo JText::_('COM_COMMUNITY_WRITE_A_COMMENT'); ?>"></textarea>
                <div class="joms-textarea__loading"><img src="<?php echo JURI::root(true); ?>/components/com_community/assets/ajax-loader.gif" alt="loader" ></div>
                <div class="joms-textarea joms-textarea__attachment">
                    <button onclick="joms.view.comment.removeAttachment(this);">×</button>
                    <div class="joms-textarea__attachment--loading"><img src="<?php echo JURI::root(true); ?>/components/com_community/assets/ajax-loader.gif" alt="loader" ></div>
                    <div class="joms-textarea__attachment--thumbnail"><img src="#" alt="attachment"></div>
                </div>
            </div>
            <svg viewBox="0 0 16 16" class="joms-icon joms-icon--add" onclick="joms.view.comment.addAttachment(this);">
                <use xlink:href="<?php echo JUri::getInstance(); ?>#joms-icon-camera"></use>
            </svg>
            <span>
                <button class="joms-button--comment joms-js--btn-send">
                    <?php echo JText::_('COM_COMMUNITY_SEND'); ?>
                </button>
            </span>
        </div>
    <?php endif; ?>

<?php } ?>
