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
    <div class= "joms-avatar--stream <?php echo CUserHelper::onlineIndicator($stream->actor); ?>">
        <a href="<?php echo ((int)$stream->actor->id !== 0) ? CUrlHelper::userLink(
            $stream->actor->id
        ) : 'javascript:void(0);'; ?>">
            <img data-author="<?php echo $stream->actor->id; ?>"
                 src="<?php echo $stream->actor->getThumbAvatar(); ?>" alt="<?php echo $stream->actor->getDisplayName(); ?>">
        </a>
    </div>

<?php else: ?>

<div class="joms-stream__header">
    <div class= "joms-avatar--stream <?php echo CUserHelper::onlineIndicator($user); ?>">
        <img src="components/com_community/assets/user-Male-thumb.png"/ alt="<?php echo JText::_('COM_COMMUNITY_MALE'); ?>" data-author="<?php echo $user->id; ?>" >
    </div>

<?php endif; ?>

    <div class="joms-stream__meta">
        <?php echo $stream->headline; ?>
        <?php echo(isset($stream->createdtime) ? $stream->createdtime : ''); ?>
        <?php if ( $act->app != 'groups.discussion' && $act->app != 'groups.bulletin' ) {
            $this->load('privacy/show');
        } ?>

    </div>

    <?php

        $my = CFactory::getUser();
        $this->load('activities.stream.options');

    ?>

</div>

<div class="joms-stream__body">

<?php if ($stream->message) {?>

     <div data-type="stream-editor" class="cStream-Respond" style="display:none">
        <div class="cStream-Form" style="display:block">
            <div class="cStream-FormInput"><textarea><?php echo $stream->message;; ?></textarea></div>
            <div class="cStream-FormSubmit">
                <a data-action="cancel" href="javascript:" class="cStream-FormCancel"><?php echo JText::_('COM_COMMUNITY_CANCEL_BUTTON') ?></a>
                <button data-action="save" class="btn btn-primary btn-small"><?php echo JText::_('COM_COMMUNITY_EDIT_POST_BUTTON') ?></button>
            </div>
        </div>
    </div>

    <p data-type="stream-content"><span><?php echo $stream->message; ?></span></p>

<?php } ?>

<?php
if (!empty($stream->attachments)) {
    foreach ($stream->attachments as $attachment) {
        switch ($attachment->type) {
            case 'media':
                ?>
                <div class="cStream-Attachment">
                    <div class="joms-stream-single-photo clearfix">
                        <?php if (isset($attachment->thumbnail) && !is_array($attachment->thumbnail)) { ?>
                            <div class="joms-stream-multi-photo row-fluid">
                                <div class="span3">
                                    <a href="#"><img class="joms-stream-single-photo"
                                                     src="<?php echo (isset($attachment->thumbnail)) ? $attachment->thumbnail : ''; ?>"
                                                     alt="photo"></a>
                                </div>
                            </div>
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
                ?>
                <?php if (isset($attachment->link)) { ?>
                <div class="cStream-Attachment">
                    <div class="joms-stream-single-photo">
                        <a href="<?php echo $attachment->link; ?>">
                            <img src="<?php echo $attachment->singlephoto; ?>" alt="photo thumbnail" />
                        </a>

                        <p class="joms-stream-photo-caption"><?php echo $attachment->caption; ?></p>
                    </div>
                </div>
            <?php } else { ?>

                <div class="cEmpty small joms-rounded">
                    <?php echo JText::_('COM_COMMUNITY_PHOTO_REMOVED'); ?>
                </div>
            <?php } ?>
                <?php
                break;

            case 'photos':
                ?>
                <div class="cStream-Attachment">
                    <div class="clearfix">
                        <div class="row-fluid">
                            <?php foreach ($attachment->thumbnail as $key => $thumb) { ?>
                                <div class="span3">
                                    <a class="joms-stream-single-photo"
                                       href="<?php echo $attachment->link[$key] ?>"><img
                                            src="<?php echo (isset($thumb)) ? $thumb : ''; ?>" alt="photo"></a>
                                </div>
                            <?php } ?>
                        </div>
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
                // the id is optional. If avaiable, it linked to internal video id

                if (isset($attachment->video_type) && $attachment->video_type == 'break') {
                    $anchorAttr = 'href="' . $attachment->link . '" target="_blank"';
                } else {
                    if ($config->get('newtab')) {
                        if (isset($video)) {
                            $anchorAttr = 'href="' . $video->getURL() . '" target="_blank"';
                        } else {
                            $anchorAttr = 'href="' . $attachment->link . '" target="_blank"';
                        }
                    } else {
                        $anchorAttr = 'href="javascript:" class="cVideo-Thumb" data-action="video-show" data-id="' .
                            (empty($attachment->id) ? '' : $attachment->id) . '"';
                    }
                }


                ?>

                <div class="joms-stream-box joms-fetch-wrapper clearfix">
                    <div style="position:relative;">
                        <div class="row-fluid">
                            <div class="span4">
                                <h5>activities.stream.php</h5>
                                <a <?php echo $anchorAttr; ?>>
                                    <div style="margin-bottom:12px; position:relative">
                                        <img src="<?php echo $attachment->thumbnail; ?>"
                                             alt="<?php echo $this->escape($attachment->title); ?>"
                                             style="max-width:100%"/>
                                        <b><?php echo $attachment->duration; ?></b>
                                    </div>
                                </a>
                            </div>
                            <div class="span8">
                                <article class="joms-stream-fetch-content" style="margin-left:0; padding-top:0">
                                    <a <?php echo $anchorAttr; ?>><?php echo $attachment->title; ?></a>

                                    <div class="separator"></div>
                                    <p class="reset-gap">
                                        <?php echo JHTML::_(
                                            'string.truncate',
                                            $attachment->description,
                                            $config->getInt('streamcontentlength'),
                                            true,
                                            false
                                        ); ?>
                                    </p>
                                </article>
                            </div>
                        </div>
                    </div>
                </div>

                <?php
                break;

            case 'quote':
                if (strlen($attachment->message)):
                    ?>
                    <div class="cStream-Attachment">
                        <h3>test info!</h3>
                        <div class="cStream-Quote">
                            <?php echo $attachment->message; ?>
                        </div>
                    </div>
                <?php
                endif;
                break;

            case 'discussion_reply':
                ?>
                <div class="cStream-Attachment">
                    <div class="cStream-Quote">
                        <p><?php echo CActivities::format($attachment->message); ?></p>
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
                    <?php echo $attachment->message; ?>
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
                $this   ->set('attachment', $attachment)
                        ->set('stream', $stream)
                        ->load('activities/groups/discussion-create');
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
                <div class="joms-stream-box joms-responsive">
                    <article>
                        <a href="<?php echo $attachment->message->getLink(); ?>"> <i
                                class="joms-icon-calendar-empty portrait-phone-only"></i> <?php echo JHTML::_(
                                'string.truncate',
                                $attachment->message->title,
                                60,
                                true,
                                false
                            ); ?></a>

                        <div class="separator"></div>
                        <p><?php echo $attachment->message->summary ?></p>
                        <?php $format = ($config->get('eventshowampm')) ? JText::_(
                            'COM_COMMUNITY_DATE_FORMAT_LC2_12H'
                        ) : JText::_('COM_COMMUNITY_DATE_FORMAT_LC2_24H'); ?>
                        <ul class="list-unstyled content-details">
                            <li>
                                <i class="joms-icon-calendar"></i>
                                <?php echo CTimeHelper::getFormattedTime(
                                    $attachment->message->startdate,
                                    $format,
                                    false
                                ); ?>
                            </li>
                            <li>
                                <i class="joms-icon-map-marker"></i>
                                <?php echo $attachment->message->location; ?>
                            </li>
                        </ul>
                    </article>
                </div>
                <?php
                break;
            case 'group_share':
                ?>
                <div class="joms-stream-box joms-responsive clearfix">
                <h3>testtt</h3>
                    <article>
                        <a href="<?php echo $attachment->message->link; ?>">
                            <i class="joms-icon-users portrait-phone-only"></i>
                            <?php echo JHTML::_('string.truncate', $attachment->message->title, 60, true, false); ?>
                        </a>
                        <p><?php echo $attachment->message->description ?></p>
                    </article>
                </div>
                <?php
                break;
            case 'fetched':
                //this is the fecthed content from url
                echo $attachment->message;
                break;
            case 'share.groups.discussion':
                ?>
                <div class="joms-stream-box joms-responsive clearfix">
                    <article>
                        <a href="<?php echo $attachment->link; ?>"><?php echo $attachment->discussion_title; ?></a>

                        <div class="separator"></div>
                        <p class="reset-gap"><?php echo $attachment->discussion_message; ?></p>

                        <div class="content-details"><?php echo $attachment->group_name ?></div>
                    </article>
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
                        <a href="#"><img
                                src="<?php echo (isset($attachment->thumbnail)) ? $attachment->thumbnail : ''; ?>"
                                alt="photo"></a>
                    <?php } ?>
                </div>
                <?php
                break;
            default:
                # code...
                break;
        }
    } // end foreach
} // end if

?>

</div>

<?php $this->load('stream/footer'); ?>

