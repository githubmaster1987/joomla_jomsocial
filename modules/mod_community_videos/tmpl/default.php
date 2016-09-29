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
$document = JFactory::getDocument();
$document->addScriptDeclaration("joms_prev_comment_load = +'" . $config->get('prev_comment_load', 10) . "';");

?>
<div class="joms-js--video-module">

    <?php if(!empty($videos)) { ?>
    <ul class="joms-list--half clearfix">
        <?php foreach( $videos as $video ) { ?>
        <li class="joms-list__item">
            <a
                <?php if ( $isVideoModal ) { ?>
                    href="javascript:" onclick="joms.api.videoOpen('<?php echo $video->id; ?>');"
                <?php } else { ?>
                    href="<?php echo $video->getURL(); ?>"
                <?php } ?>
                >
                <img src="<?php echo $video->getThumbNail(); ?>" alt="<?php echo $video->getTitle(); ?>"   title="<?php echo CStringHelper::escape($video->title); ?>" />
                <span class="joms-video__duration"><small><?php echo $video->getDurationInHMS(); ?></small></span>
            </a>
        </li>
        <?php } ?>
    </ul>
    <?php } else { ?>
    <div class="joms-blankslate"><?php echo JText::_('COM_COMMUNITY_VIDEOS_NO_VIDEO'); ?></div>
    <?php } ?>
</div>

<div class="joms-gap"></div>
<a href="<?php echo CRoute::_('index.php?option=com_community&view=videos&task=display'); ?>" class="joms-button--link">
    <small><?php echo JText::_('COM_COMMUNITY_VIDEOS_ALL'); ?></small>
</a>

<script>
    (function( w ) {
        w.joms_queue || (w.joms_queue = []);
        w.joms_queue.push(function( $ ) {
            var $ct = $('#latest-videos-nav'),
                $loading = $ct.find('.joms-js--loading');

            function render( json ) {
                $list = $('.joms-js--video-module').find('.joms-list--half');
                $list.html( json.html || '&nbsp;' );
            }

            $ct.on( 'click', '.newest-videos', function( e ) {
                $loading.show();
                joms.ajax({
                    func: 'frontpage,ajaxGetNewestVideos',
                    data: [ frontpageVideos ],
                    callback: function( json ) {
                        $( e.target ).addClass('active-state').siblings('a').removeClass('active-state');
                        $loading.hide();
                        render( json );
                    }
                });
            });

            $ct.on( 'click', '.featured-videos', function( e ) {
                $loading.show();
                joms.ajax({
                    func: 'frontpage,ajaxGetFeaturedVideos',
                    data: [ frontpageVideos ],
                    callback: function( json ) {
                        $( e.target ).addClass('active-state').siblings('a').removeClass('active-state');
                        $loading.hide();
                        render( json );
                    }
                });
            });

            $ct.on( 'click', '.popular-videos', function( e ) {
                $loading.show();
                joms.ajax({
                    func: 'frontpage,ajaxGetPopularVideos',
                    data: [ frontpageVideos ],
                    callback: function( json ) {
                        $( e.target ).addClass('active-state').siblings('a').removeClass('active-state');
                        $loading.hide();
                        render( json );
                    }
                });
            });
        });
    })( window );
</script>
