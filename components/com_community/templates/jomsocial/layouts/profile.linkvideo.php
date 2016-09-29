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

$config = CFactory::getConfig();
$isVideoModal = $config->get('video_mode') == 1;

?>
<div class="joms-page">
    <h3 class="joms-page__title">
        <?php echo JText::_('COM_COMMUNITY_VIDEOS_EDIT_PROFILE_VIDEO'); ?>
    </h3>

    <?php //echo $submenu; ?>



    <?php if(!empty($video->id)){ ?>
        <h4 class="joms-text--title"><?php echo JText::_('COM_COMMUNITY_VIDEOS_CURRENT_PROFILE_VIDEO_HEADING');?></h4>
        <div class="joms-form__group">
        <a class="joms-button--primary joms-button--full-small" onclick="joms.api.videoRemoveLinkFromProfile('<?php echo $video->getId(); ?>', '<?php echo $video->creator; ?>');" href="javascript:">
            <?php echo JText::_('COM_COMMUNITY_VIDEOS_REMOVE_PROFILE_VIDEO'); ?>
        </a>
    </div>
    <?php } ?>

    <?php if(!empty($video->id)){ ?>
        <div class="cVideo-Screen video-player clearfix" style="position:relative;">
            <?php echo $video->getPlayerHTML(); ?>
        </div>
        <div class="joms-form__group">
            <p><b><?php echo JText::_('COM_COMMUNITY_VIDEOS_PROFILE_VIDEO_DESCRIPTION'); ?></b></p>
            <p><?php echo $this->escape($video->getDescription()); ?></p>
        </div>

    <?php } else { ?>
    <div class="joms-form__group">
        <p><?php echo JText::_('COM_COMMUNITY_VIDEOS_NO_USER_PROFILE_VIDEO'); ?></p>
    </div>
    <?php } ?>

    <?php
    echo $sortings;

    if ($videos) { ?>
        <div class="joms-gap"></div>
        <ul class="joms-list--video">
            <?php
            $x = 1;
            $i = 0;
            foreach($videos as $vid) {
                $v = JTable::getInstance( 'Video' , 'CTable' );
                $v->load($vid->id);
                $v->_wallcount = $vid->_wallcount;
            ?>
                <li class="joms-list__item">

                    <div class="cMedia-VideoCover">
                        <?php if ( $v->status == 'pending' ) { ?>
                            <img class="cVideo-Thumb" src="<?php echo JURI::root(true);?>/components/com_community/assets/video_thumb.png" style="width: <?php echo $videoThumbWidth; ?>px; height:<?php echo $videoThumbHeight; ?>px;" alt="<?php echo $v->getTitle(); ?>" />
                        <?php } else { ?>
                            <a class="cVideo-Thumb"
                                <?php if ($isVideoModal) { ?>
                                href="javascript:" onclick="joms.api.videoOpen('<?php echo $v->getId(); ?>');"
                                <?php } else { ?>
                                href="<?php echo $v->getURL(); ?>"
                                <?php } ?>
                            ><img src="<?php echo $v->getThumbnail(); ?>" width="<?php echo $videoThumbWidth; ?>" height="<?php echo $videoThumbHeight; ?>" alt="<?php echo $v->getTitle(); ?>">
                            </a>
                            <span class="joms-video__duration"><?php echo $v->getDurationInHMS(); ?></span>
                        <?php } ?>
                    </div>

                    <div class="joms-gap"></div>
                    <!-- SELECT BUTTON -->
                    <span>
                        <a class="joms-button--neutral joms-button--full joms-button--small" href="javascript:" onclick="joms.api.videoLinkToProfile('<?php echo $v->getId(); ?>');" title="<?php echo JText::_('COM_COMMUNITY_VIDEOS_PROFILE_VIDEO_LINK') ?>">
                            <?php echo JText::_('COM_COMMUNITY_VIDEOS_SET_AS_PROFILE'); ?>
                        </a>
                    </span>
                    <div class="joms-gap"></div>

                    <?php if ( $v->status == 'pending' ) { ?>
                        <h4 class="joms-text--title">
                            <?php echo JHTML::_('string.truncate', $this->escape(strip_tags($v->getTitle())), 50); ?>
                        </h4>
                    <?php } else { ?>
                        <h4 class="joms-text--title">
                            <a
                                <?php if ($isVideoModal) { ?>
                                href="javascript:" onclick="joms.api.videoOpen('<?php echo $v->getId(); ?>');"
                                <?php } else { ?>
                                href="<?php echo $v->getURL(); ?>"
                                <?php } ?>
                            ><?php echo JHTML::_('string.truncate', $this->escape(strip_tags($v->getTitle())), 50); ?></a>
                        </h4>
                    <?php } ?>

                    <span><?php echo JText::sprintf('COM_COMMUNITY_VIDEOS_LAST_UPDATED', $v->getLastUpdated());?></span>
                </li>
            <?php
            } // end foreach
            ?>
        </ul><!-- end .cVideoItems -->

    <?php
    }
    else
    {
        $isMine = ( isset($video) && $video->creator==$my->id);
        $msg    = $isMine ? JText::_('COM_COMMUNITY_VIDEOS_NO_VIDEO') : JText::sprintf('COM_COMMUNITY_VIDEOS_NO_VIDEOS', $my->getDisplayName());
        ?>
            <div><?php echo $msg; ?></div>
        <?php
    }
    ?>

    <?php
    if ( $pagination->getPagesLinks() )
    {
    ?>
    <div class="cPagination">
        <?php echo $pagination->getPagesLinks(); ?>
    </div>
    <?php
    }
    ?>
</div>
<script>
joms.onStart(function( $ ) {
    function initVideo() {
        var cssVideo = '.joms-js--video',
            video = joms.jQuery('.cVideo-Screen').find( cssVideo );

        if ( !video.length ) {
            return;
        }

        joms.loadCSS( joms.ASSETS_URL + 'vendors/mediaelement/mediaelementplayer.min.css' );
        video.on( 'click.joms-video', cssVideo + '-play', function() {
            var $el = jQuery( this ).closest( cssVideo );
            joms.util.video.play( $el, $el.data() );
        });
    }

    initVideo();
});
</script>
