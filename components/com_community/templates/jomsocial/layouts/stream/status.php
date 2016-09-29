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

/**
 * @since 3.2 we'll use CActivity for each activity object
 * @todo in sprint 3 we must move everything into CActivity while passing into template layout
 */
/* Temporary fix for sprint 2 */
if ($this->act instanceof CTableActivity) {
    /* If this's CTableActivity then we use getProperties() */
    $activity = new CActivity($this->act->getProperties());
} else {
    /* If it's standard object than we just passing it */
    $activity = new CActivity($this->act);
}

//print_r($user);

$mainframe	= JFactory::getApplication();
$jinput 	= $mainframe->input;
$isSingleAct= ($jinput->get->get('actid',0) > 0) ? true : false;

$address = $activity->getLocation();
$user = $activity->getActor();
$target = $activity->getTarget();
$headMetas = $activity->getParams('headMetas');
/* We do convert into JRegistry to make it easier to use */
if ($headMetas) {
    $headMetaParams = new JRegistry($headMetas);
}
if ($act->app == 'profile.avatar.upload') {
    if ($my->id > 0) {
        $this->load('activities.stream.options');
    }
    $this->load('activities.profile.avatar.upload');
    return;
}


if (!empty($act->params)) {
    if (!is_object($act->params)) {
        $act->params = new JRegistry($act->params);
    }
    $mood = $act->params->get('mood', null);
} else {
    $mood = null;
}
$title = $activity->get('title');

?>

<div class="joms-stream__header">
    <div class= "joms-avatar--stream <?php echo CUserHelper::onlineIndicator($user); ?>">
        <?php if($user->id > 0) :?>
            <a href="<?php echo CUrlHelper::userLink($user->id); ?>">
                <img data-author="<?php echo $user->id; ?>" src="<?php echo $user->getThumbAvatar(); ?>" alt="<?php echo $user->getDisplayName(); ?>">
            </a>
        <?php endif; ?>
    </div>
    <div class="joms-stream__meta">

        <?php if($user->id > 0) :?>
            <a href="<?php echo CUrlHelper::userLink($user->id); ?>" data-joms-username class="joms-stream__user active"><?php echo $user->getDisplayName(); ?></a>
        <?php else :
            echo $user->getDisplayName();
        endif;

        if ($activity->get('eventid')) {
            $event = $this->event;
            ?>
            <span class="joms-stream__reference">
                ▶ <a href="<?php echo CUrlHelper::eventLink($event->id); ?>"><?php echo $event->title; ?></a>
            </span>
        <?php
        } else if ($activity->get('groupid')) {
            $group = $this->group;
            ?>
            <span class="joms-stream__reference">
                ▶ <a href="<?php echo CUrlHelper::groupLink($group->id); ?>"><?php echo $group->name; ?></a>
            </span>
            <!-- Target is user profile -->
        <?php } else if ( ( $activity->get('app') == 'profile' ) && ( $activity->get('target') != 0 ) && $activity->get('target') != $user->id ) { ?>
            <span class="joms-stream__reference">
                ▶ <a href="<?php echo CUrlHelper::userLink($activity->target); ?>"><?php echo CFactory::getUser($activity->get('target'))->getDisplayName(); ?></a>
            </span>
        <?php } ?>

        <a href="<?php echo CRoute::_('index.php?option=com_community&view=profile&userid='.$activity->actor.'&actid='.$activity->id); ?>">
            <span class="joms-stream__time">
                <small><?php echo $activity->getCreateTimeFormatted(); ?></small>
                <?php if ( strpos($activity->get('app'), 'events') === false  && strpos($activity->get('app'), 'groups') === false ) { ?>
                    <?php ( $activity->get('groupid') || ($activity->get('app') == 'profile') && $activity->get('target') != $activity->get('actor') ) ? '' : $this->load('/privacy/show'); ?>
                <?php } ?>
            </span>
        </a>
    </div>

    <?php

    $my = CFactory::getUser();
    $this->load('activities.stream.options');

    ?>

</div>

<div class="joms-stream__body">

    <div data-type="stream-editor" class="cStream-Respond" style="display:none">
        <textarea class="joms-textarea" style="margin-bottom:0"><?php echo $activity->get('title'); ?></textarea>
        <div style="text-align:right;">
            <button class="joms-button--neutral joms-button--small" onclick="joms.view.stream.cancel('<?php echo $activity->get('id'); ?>');"><?php echo JText::_('COM_COMMUNITY_CANCEL'); ?></button>&nbsp;
            <button class="joms-button--primary joms-button--small" onclick="joms.view.stream.save('<?php echo $activity->get('id'); ?>', this);"><?php echo JText::_('COM_COMMUNITY_SAVE'); ?></button>
        </div>
    </div>

    <p data-type="stream-content">
        <?php $title =  empty($title) ? ltrim(CActivities::format($activity->get('title'), $mood),' -') : CActivities::format($activity->get('title'), $mood);

        echo CActivities::shorten($title, $activity->get('id'), $isSingleAct, $config->getInt('streamcontentlength'));

        if ($address) { ?>
            <span class="joms-status-location"><?php if(!empty($title)){?>- <?php }?><?php echo JText::_('COM_COMMUNITY_AT'); ?>
                <a href="javascript:" onclick="joms.api.locationView('<?php echo $activity->get('id'); ?>');"><?php echo $address ?></a>
        </span>
        <?php } ?>
    </p>

    <!-- Fetched data -->
    <?php if ($headMetas) { ?>

        <?php if ($headMetaParams->get('title') || $headMetaParams->get('description')) { ?>

            <?php

            if($headMetaParams->get('type') == 'video'){
                if($headMetaParams->get('video_provider') == 'break'){
                    $href= 'href="'.$headMetaParams->get('link').'" target="_blank"';
                }else{
                    $href= 'href=\'javascript:jax.call("community" , "videos,ajaxShowStreamVideoWindow", "'.$activity->id.'");\'';
                }

            }else{
                $href = $headMetaParams->get('link') ? $headMetaParams->get('link') : '#';
                $href = "href='".$href."' target='_blank'";
            }
            ?>

            <?php if($headMetaParams->get('type') == 'video' && !$config->get('enable_embedly')){
                $video = JTable::getInstance('Video', 'CTable');
                if(!$video->init($headMetaParams->get('link'))) {
                    $video = false;
                }

                if (is_object($video)) {
                    ?>

                    <div class="joms-media--video joms-js--video"
                         data-type="<?php echo $video->type; ?>"
                         data-id="<?php echo $video->video_id; ?>"
                         data-path="<?php echo ($video->type == 'file') ? CStorage::getStorage($video->storage)->getURI($video->path) : $video->path; ?>">

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

                <?php } else { ?>

                    <div class="joms-stream-box joms-fetch-wrapper clearfix" >
                        <div style="position:relative;">
                            <div class="row-fluid">
                                <div class="span4">
                                    <h5>activities/stream/status.php</h5>
                                    <a <?php echo $href; ?> class="cVideo-Thumb">
                                        <div style="margin-bottom:12px; position:relative">
                                            <img src="<?php echo $headMetaParams->get('image'); ?>"
                                                 alt="<?php echo $this->escape($headMetaParams->get('title')); ?>"
                                                 style="max-width:100%" />
                                        </div>
                                    </a>
                                </div>
                                <div class="span8">
                                    <article class="joms-stream-fetch-content" style="margin-left:0; padding-top:0">
                                        <a <?php echo $href; ?>><?php echo $this->escape($headMetaParams->get('title')); ?></a>
                                        <div class="separator"></div>
                                        <p class="reset-gap">
                                            <?php echo JHTML::_('string.truncate', $headMetaParams->get('description'), $config->getInt('streamcontentlength'), true, false); ?>
                                        </p>
                                    </article>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php
                }
            }elseif($config->get('enable_embedly')){ ?>
                <a href="<?php echo $headMetaParams->get('link'); ?>" class="embedly-card" data-card-controls="0" data-card-recommend="0" data-card-theme="<?php echo $config->get('enable_embedly_card_template'); ?>" data-card-align="<?php echo $config->get('enable_embedly_card_position') ?>"><?php echo JText::_('COM_COMMUNITY_EMBEDLY_LOADING');?></a>
            <?php }else{ ?>
                <div class="joms-media--album">
                    <?php if ($headMetaParams->get('image')) { ?>
                        <div class="joms-media__thumbnail">
                            <a <?php echo $href; ?>>
                                <img src="<?php echo $headMetaParams->get('image'); ?>" alt="thumbnail" >
                            </a>
                        </div>
                    <?php } ?>
                    <div class="joms-media__body">
                        <h4 class="joms-media__title">
                            <a <?php echo $href; ?> ><?php echo $headMetaParams->get('title'); ?></a>
                        </h4>
                        <p class="joms-media__desc"><?php echo CStringHelper::trim_words($headMetaParams->get('description')); ?></p>
                    </div>
                </div>
            <?php } ?>
        <?php }
    }
    ?>
</div>

<link href="<?php echo JURI::base(); ?>/libraries/emoji-sys/css/reaction.css" rel="stylesheet" type="text/css">
<script src="<?php echo JURI::base(); ?>/libraries/emoji-sys/js/jquery.min.js"></script>
<script src="<?php echo JURI::base(); ?>/libraries/emoji-sys/js/reaction.js"></script>


<div class="facebook-reaction"><!-- container div for reaction system -->
    <span class="like-btn"> <!-- Default like button -->
        <span class="like-btn-emo love-btn-default"></span> <!-- Default like button emotion-->
        <span class="like-btn-text">Love</span> <!-- Default like button text,(Like, wow, sad..) default:Like  -->
          <ul class="reactions-box"> <!-- Reaction buttons container-->
                <li class="reaction reaction-love" data-reaction="Love"></li>
                <li class="reaction reaction-like" data-reaction="Like"></li>
                <li class="reaction reaction-haha" data-reaction="HaHa"></li>
                <li class="reaction reaction-wow" data-reaction="Wow"></li>
                <li class="reaction reaction-sad" data-reaction="Sad"></li>
                <li class="reaction reaction-angry" data-reaction="Angry"></li>
          </ul>
    </span>
    <div class="like-stat" style="display:"> <!-- Like statistic container-->
        <span class="like-emo"> <!-- like emotions container -->
            <!-- given emotions like, wow, sad (default:Like) -->
            <span class="like-btn-default"></span>
        </span>
        <span class="like-details">1000</span>

        <span class="love-emo"> <!-- like emotions container -->
            <!-- given emotions like, wow, sad (default:Like) -->
            <span class="love-btn-default"></span>
        </span>
        <span class="love-details">2000</span>

        <span class="haha-emo"> <!-- like emotions container -->
            <!-- given emotions like, wow, sad (default:Like) -->
            <span class="haha-btn-default"></span>
        </span>
        <span class="haha-details">3000</span>
        <span class="wow-emo"> <!-- like emotions container -->
            <!-- given emotions like, wow, sad (default:Like) -->
            <span class="wow-btn-default"></span>
        </span>
        <span class="wow-details">4000</span>
        <span class="sad-emo"> <!-- like emotions container -->
            <!-- given emotions like, wow, sad (default:Like) -->
            <span class="sad-btn-default"></span>
        </span>
        <span class="sad-details">5000</span>
        <span class="angry-emo"> <!-- like emotions container -->
            <!-- given emotions like, wow, sad (default:Like) -->
            <span class="angry-btn-default"></span>
        </span>
        <span class="angry-details">6000</span>
    </div>
</div>

<?php $this->load('stream/footer'); ?>
