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

<link href="libraries/emoji/reaction.css" rel="stylesheet" type="text/css">
<script src="https://code.jquery.com/jquery-2.1.4.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/velocity/1.2.3/velocity.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/velocity/1.2.3/velocity.ui.min.js "></script>
<script src="libraries/emoji/reaction.js"></script>
<script type="text/javascript">
	
var isAnimationEnable = 0;
var interVal;

$(document).ready(function() {

  $(".showEmotions").hover(function() {

    if (isAnimationEnable == 0) {
      $(".emoji-reactions").show().css('opacity', '1');
      $(".emoji-reactions span").velocity("transition.bounceUpIn", {
        stagger: 80
      });
      isAnimationEnable = 1;
      interVal = setInterval(function() {
        if (isAnimationEnable == 1) {
          cursorListener();
        }
      }, 500);
    }

  }, function() {

  });

  function cursorListener() {
    var isHovered = !!$('.emoji-reactions , .actionBox').
    filter(function() {
      return $(this).is(":hover");
    }).length;
    console.log(isHovered);
    if (!isHovered) {
      $(".emoji-reactions").velocity("transition.fadeOut", {
        delay: 10
      });
      clearInterval(interVal);
      isAnimationEnable = 0;

    }
  }

});

</script>


<?php if ($allowComment || $allowLike || $showLike) { ?>
<div class="joms-stream__status--mobile">

<?php if ($allowLike || $showLike) { ?>
<?php if ($act->likeCount > 0 && $showLike) { ?>


    <a href="javascript:" onclick="joms.api.streamShowLikes('<?php echo $act->id; ?>', 'popup');">
        <span class="joms-like__counter--<?php echo $act->id; ?>"><?php echo $act->likeCount; ?></span>
        <svg viewBox="0 0 16 16" class="joms-icon">
            <use xlink:href="<?php echo JUri::getInstance(); ?>#joms-icon-thumbs-up"></use>
        </svg>
    </a>
	
    <a href="javascript:" onclick="joms.api.streamShowLikes('<?php echo $act->id; ?>', 'popup');">
        <span class="joms-love__counter--<?php echo $act->id; ?>"><?php echo $act->likeCount; ?></span>
        <svg viewBox="0 0 16 16" class="joms-icon">
            <use xlink:href="<?php echo JUri::getInstance(); ?>#joms-icon-thumbs-up"></use>
        </svg>
    </a>
	
    <a href="javascript:" onclick="joms.api.streamShowLikes('<?php echo $act->id; ?>', 'popup');">
        <span class="joms-haha__counter--<?php echo $act->id; ?>"><?php echo $act->likeCount; ?></span>
        <svg viewBox="0 0 16 16" class="joms-icon">
            <use xlink:href="<?php echo JUri::getInstance(); ?>#joms-icon-thumbs-up"></use>
        </svg>
    </a>
	
    <a href="javascript:" onclick="joms.api.streamShowLikes('<?php echo $act->id; ?>', 'popup');">
        <span class="joms-sorry__counter--<?php echo $act->id; ?>"><?php echo $act->likeCount; ?></span>
        <svg viewBox="0 0 16 16" class="joms-icon">
            <use xlink:href="<?php echo JUri::getInstance(); ?>#joms-icon-thumbs-up"></use>
        </svg>
    </a>
	
    <a href="javascript:" onclick="joms.api.streamShowLikes('<?php echo $act->id; ?>', 'popup');">
        <span class="joms-angry__counter--<?php echo $act->id; ?>"><?php echo $act->likeCount; ?></span>
        <svg viewBox="0 0 16 16" class="joms-icon">
            <use xlink:href="<?php echo JUri::getInstance(); ?>#joms-icon-thumbs-up"></use>
        </svg>
    </a>
	
    <a href="javascript:" onclick="joms.api.streamShowLikes('<?php echo $act->id; ?>', 'popup');">
        <span class="joms-dislike__counter--<?php echo $act->id; ?>"><?php echo $act->likeCount; ?></span>
        <svg viewBox="0 0 16 16" class="joms-icon">
            <use xlink:href="<?php echo JUri::getInstance(); ?>#joms-icon-thumbs-up"></use>
        </svg>
    </a>
	       
<?php } ?>
<?php } ?>

<?php if ($allowComment) { ?>

    <a href="javascript:" onclick="joms.api.streamShowComments('<?php echo $act->id; ?>');">
        <span class="joms-comment__counter--<?php echo $act->id; ?>"><?php echo $act->commentCount; ?></span>
        <svg viewBox="0 0 16 16" class="joms-icon">
            <use xlink:href="<?php echo JUri::getInstance(); ?>#joms-icon-bubble"></use>
        </svg>
    </a>

<?php } ?>

</div>
<?php } ?>

<div class="joms-stream__actions">

    <!-- liked -->
    <?php if ($allowLike) { ?>
        <?php $userLiked = $act->userLiked == COMMUNITY_LIKE; ?>
	 <div class="actionBox">
	<span class="showEmotions">
        <a class=""
            data-lang-like="<?php echo JText::_('COM_COMMUNITY_LIKE'); ?>"
            data-lang-unlike="<?php echo JText::_('COM_COMMUNITY_UNLIKE'); ?>"
            <svg viewBox="0 0 16 16" class="joms-icon">
                <use xlink:href="<?php echo JUri::getInstance(); ?>#joms-icon-thumbs-up"></use>
            </svg>
            <span><?php echo JText::_('COM_COMMUNITY_LIKE'); ?></span>
        </a>
		<div class="emoji-reactions">
      <span>
        <a href="javascript:" class="reaction joms-button--liked" onclick="joms.api.streamLike('<?php echo $act->id; ?>');">
          <i class="react like"></i>
        </a>
        <div class="text">
          <div>Like</div>
        </div>
      </span>

    <span>
        <a href="javascript:" class="reaction joms-button--haha" onclick="joms.api.streamHaha('<?php echo $act->id; ?>');">
          <i class="react love"></i>
        </a>
        <div class="text">
          <div>Love</div>
        </div>
      </span>

    <span>
        <a href="javascript:" class="reaction joms-button--liked<?php echo $userLiked ? ' liked' : '' ?>" onclick="joms.api.stream<?php echo $userLiked ? 'Unlike' : 'Like' ?>('<?php echo $act->id; ?>');">
          <i class="react haha"></i>
        </a>
        <div class="text">
          <div>Haha</div>
        </div>
      </span>

    <span>
        <a href="javascript:" class="reaction joms-button--liked<?php echo $userLiked ? ' liked' : '' ?>" onclick="joms.api.stream<?php echo $userLiked ? 'Unlike' : 'Like' ?>('<?php echo $act->id; ?>');">
          <i class="react sorry"></i>
        </a>
        <div class="text">
          <div>Sorry</div>
        </div>
      </span>

    <span>
        <a href="javascript:" class="reaction joms-button--liked<?php echo $userLiked ? ' liked' : '' ?>" onclick="joms.api.stream<?php echo $userLiked ? 'Unlike' : 'Like' ?>('<?php echo $act->id; ?>');">
          <i class="react anger"></i>
        </a>
        <div class="text">
          <div>Angry</div>
        </div>
      </span>

    <span>
        <a href="javascript:" class="reaction joms-button--liked<?php echo $userLiked ? ' liked' : '' ?>" onclick="joms.api.streamUnlike('<?php echo $act->id; ?>');">
          <i class="react dislike"></i>
        </a>
        <div class="text">
          <div>Dislike</div>
        </div>
      </span>

  </div>
	
</span>
</div>

    <?php } ?>

    <!-- share -->
    <?php
    //the only thing that we are able to share
    $allowShare = array(
        'groups.wall', //group status - plain text, fetched content, with text and mood, location, fetched content
        'profile',
        'events.wall',
        'profile.avatar.upload', //profile avatar update
        'photos',
        'videos.linking', //linked videos
        'videos', //uploaded videos
        'groups', //group creation
        'groups.avatar.upload',
        'events',
        'events.avatar.upload'
    );

    if ($my->id > 0 && $my->id != $act->actor &&
        ( ($act->access == 0 || $act->access == 10) && ($act->group_access == 0 && $act->event_access == 0))
        && in_array($act->app, $allowShare)
        //anything below this is no longer used, just for reference
        /*
        && $act->app != 'groups.bulletin'
        && $act->app != 'cover.upload'
        && strpos($act->app,'comment') === false
        && strpos($act->app,'featured') === false
        && $act->app != 'groups.discussion.reply'*/
    ) {
        ?>

    <?php

        // Re-share shared stream will share the original stream.
        $shareId = $act->id;
        if ( $act->app == 'profile.status.share' ) {
            $shareId = $act->params->get('activityId');
        }

    ?>

    <a class="joms-button--share" href="javascript:" onclick="joms.api.streamShare('<?php echo $shareId; ?>');">
        <svg viewBox="0 0 16 16" class="joms-icon">
            <use xlink:href="<?php echo JUri::getInstance(); ?>#joms-icon-redo"></use>
        </svg>
        <span><?php echo JText::_('COM_COMMUNITY_SHARE'); ?></span>
    </a>

    <?php } ?>

</div>

<?php if ($allowLike || $showLike) { ?>
<?php if ($act->likeCount > 0 && $showLike) { ?>

<div class="joms-stream__status">
    <a href="javascript:" onclick="joms.api.streamShowLikes('<?php echo $act->id; ?>');"><?php
        echo ($act->likeCount > 1)
            ? JText::sprintf('COM_COMMUNITY_LIKE_THIS_MANY', $act->likeCount)
            : JText::sprintf('COM_COMMUNITY_LIKE_THIS', $act->likeCount);
    ?></a>
</div>

<?php } ?>
<?php } ?>
