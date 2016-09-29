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

$isGroup = isset($groupId) && $groupId;
$isEvent = isset($eventId) && $eventId;

$likeCountHTML = '';
if ($likeCount > 0) {
    $likeCountHTML = ' (' . $likeCount . ')';
}

$enableVideoLocation = ( ! empty( $video->location ) ) && ( $videoMapsDefault == 1 );

$enableReporting = false;
if ( $config->get('enablereporting') == 1 && $video->creator != $my->id && ( $my->id > 0 || $config->get('enableguestreporting') == 1 ) ) {
    $enableReporting = true;
}

$context = '';
$contextId = '';
if ($isGroup) {
    $context = VIDEO_GROUP_TYPE;
    $contextId = $groupId;
} else if ($isEvent) {
    $context = VIDEO_EVENT_TYPE;
    $contextId = $eventId;
}

?>

<div class="joms-page">
    <div class="joms-list__search">
        <div class="joms-list__search-title">
            <h3 class="joms-page__title" title="<?php echo $video->title; ?>"><?php echo JHTML::_('string.truncate', $video->title, 80); ?></h3>
        </div>

        <div class="joms-list__utilities">
            <?php if(isset($canSearch) && $canSearch) { ?>
                <form method="GET" class="joms-inline--desktop"
                      action="<?php echo CRoute::_('index.php?option=com_community&view=videos&task=search'); ?>">
                <span>
                    <input type="text" class="joms-input--search" name="search-text"
                           placeholder="<?php echo JText::_('COM_COMMUNITY_SEARCH_VIDEO_PLACEHOLDER'); ?>">
                </span>
                    <?php echo JHTML::_('form.token') ?>
                    <span>
                    <button class="joms-button--neutral"><?php echo JText::_('COM_COMMUNITY_SEARCH_GO'); ?></button>
                </span>
                    <input type="hidden" name="option" value="com_community"/>
                    <input type="hidden" name="view" value="videos"/>
                    <input type="hidden" name="task" value="search"/>
                    <input type="hidden" name="Itemid" value="<?php echo CRoute::getItemId(); ?>"/>
                </form>
            <?php

            }

            if($my->authorise('community.create', 'videos')) { ?>
            <button onclick="joms.api.videoAdd('<?php echo $contextId ?>', '<?php echo $context ?>');" class="joms-button--add">
                <span><?php echo JText::_( $isGroup ? 'COM_COMMUNITY_GROUP_VIDEOS_ADD' : ( $isEvent ? 'COM_COMMUNITY_EVENT_VIDEOS_ADD' : 'COM_COMMUNITY_VIDEOS_ADD' ) ); ?></span>
                <svg class="joms-icon" viewBox="0 0 16 16">
                    <use xlink:href="<?php echo CRoute::getURI(); ?>#joms-icon-plus"></use>
                </svg>
            </button>
            <?php } ?>
        </div>
    </div>

    <?php echo $submenu;?>

    <div class="joms-gap"></div>

    <div class="cVideo-Screen video-player">
        <div class="cVideo-Wrapper" style="margin: 0 auto; position: relative;">
            <?php echo $video->getPlayerHTML(); ?>
        </div>
    </div>
    <div class="joms-gap"></div>
    <h5 class="reset-gap">
    <svg viewBox="0 0 16 16" class="joms-icon">
        <use xlink:href="<?php echo CRoute::getURI(); ?>#joms-icon-eye"></use>
    </svg>
    <?php
        if (CStringHelper::isPlural($video->getHits())) {
            echo JText::sprintf('COM_COMMUNITY_VIDEOS_HITS_COUNT_MANY', $video->getHits());
        } else {
            echo JText::sprintf('COM_COMMUNITY_VIDEOS_HITS_COUNT', $video->getHits());
        }
     ?>
    </h5>
    <div class="joms-gap"></div>

    <button class="joms-button--small joms-button--<?php echo $likeLiked ? 'primary' : 'neutral' ?> joms-js--like-videos-<?php echo $video->id; ?>"
            onclick="joms.api.page<?php echo $likeLiked ? 'Unlike' : 'Like' ?>('videos', '<?php echo $video->id ?>');"
            data-lang="<?php echo JText::_('COM_COMMUNITY_LIKE'); ?>"
            data-lang-like="<?php echo JText::_('COM_COMMUNITY_LIKE'); ?>"
            data-lang-liked="<?php echo JText::_('COM_COMMUNITY_LIKED'); ?>"><?php
        echo JText::_( $likeLiked ? 'COM_COMMUNITY_LIKED' : 'COM_COMMUNITY_LIKE'); ?><?php echo $likeCountHTML; ?></button>

    <?php if ($config->get('enablesharethis') == 1) { ?>
    <button class="joms-button--neutral joms-button--small" onclick="joms.api.pageShare('<?php echo $video->getPermalink(); ?>')"><?php echo JText::_('COM_COMMUNITY_SHARE'); ?></button>
    <?php } ?>

    <?php if ($enableReporting) { ?>
    <button class="joms-button--neutral joms-button--small" onclick="joms.api.videoReport('<?php echo $video->id ?>');"><?php echo JText::_('COM_COMMUNITY_REPORT') ?></button>
    <?php } ?>

    <?php if (COwnerHelper::isCommunityAdmin() || ($my->id == $video->creator)) { ?>
    &nbsp;&nbsp; <button class="joms-button--neutral joms-button--small joms-js--btn-tag"><?php echo JText::_('COM_COMMUNITY_TAG_THIS_VIDEO'); ?></button>
    <?php } ?>

<?php if ( $enableVideoLocation ) { ?>
</div>

&nbsp;<br>
<?php } else { ?>
&nbsp;<br><br>
<?php } ?>

<?php if ( $enableVideoLocation ) { ?>
<div class="joms-sidebar">
    <div class="joms-module__wrapper">
        <div class="joms-tab__bar">
            <a class="active"><?php echo JText::sprintf('COM_COMMUNITY_PHOTOS_ALBUM_TAKEN_AT_DESC', ''); ?></a>
        </div>
        <div class="joms-tab__content">
            <div class="joms-js--video-map-wrapper">
                <div class="app-box-content event-description">
                    <!-- begin: dynamic map -->
                    <?php // echo CMapping::drawMap('event-map', $event->location); ?>
                    <div class="joms-js--video-map" style="height:210px;width:100%;">
                        <?php echo JText::_('COM_COMMUNITY_MAPS_LOADING'); ?>
                    </div>
                    <!-- end: dynamic map -->
                    <div class="joms-text--small"><?php echo CMapping::getFormatedAdd($video->location); ?></div>
                </div>
                <div class="joms-module__footer">
                    <a href="http://maps.google.com/?q=<?php echo urlencode($video->location); ?>" target="_blank" class="joms-button--link"><?php echo JText::_('COM_COMMUNITY_EVENTS_FULL_MAP'); ?></a>
                </div>
            </div>
            <script>
                (function( w ) {
                    w.joms_queue || (w.joms_queue = []);
                    w.joms_queue.push(function() {
                        var address, container, geocoder;

                        // Disable map on mobile.
                        if ( joms.mobile ) {
                            joms.jQuery('.joms-js--video-map-wrapper').remove();
                            return;
                        }

                        address = '<?php echo addslashes($video->location); ?>',
                        container = joms.jQuery('.joms-js--video-map');

                        // Delay execution.
                        setTimeout(function() {
                            joms.util.map(function() {
                                geocoder = new google.maps.Geocoder();
                                geocoder.geocode({ address: address }, function( results, status ) {
                                    var location, center, mapOptions, map, marker;

                                    if (status != google.maps.GeocoderStatus.OK) {
                                        container.html( 'Geocode was not successful for the following reason: ' + status );
                                        return;
                                    }

                                    location = results[0].geometry.location;
                                    center = new google.maps.LatLng( location.lat(), location.lng() );

                                    mapOptions = {
                                        zoom: 14,
                                        center: center,
                                        mapTypeId: google.maps.MapTypeId.ROADMAP
                                    };

                                    map = new google.maps.Map( container[0], mapOptions );

                                    marker = new google.maps.Marker({
                                        map: map,
                                        position: center
                                    });

                                });
                            });
                        }, 2000 );

                    });
                })( window );
            </script>
        </div>
    </div>
</div>
<?php } ?>

<?php if ( $enableVideoLocation ) { ?>
<div class="joms-main">
    <div class="joms-page">
<?php } ?>

        <div class="cMedia-Author">
            <strong><?php echo JText::_('COM_COMMUNITY_VIDEOS_UPLOADED_BY'); ?></strong>
            <a href="<?php echo CUrlHelper::userLink($user->id); ?>">
                <?php echo $user->getDisplayName(); ?>
            </a>

            &nbsp;&bull;&nbsp;
            <strong><?php echo JText::_('COM_COMMUNITY_VIDEOS_CATEGORY'); ?>:</strong>
            <a href="<?php echo CRoute::_('index.php?option=com_community&view=videos&task=display&catid=' . $video->category_id); ?>">
                <?php echo JText::_($video->getCategoryName()); ?>
            </a>

            &nbsp;&bull;&nbsp;
            <strong><?php echo JText::_('COM_COMMUNITY_VIDEOS_CREATED'); ?></strong>
            <?php echo JHTML::_('date', $video->created, JText::_('DATE_FORMAT_LC3')); ?>

            <?php if (!empty($video->location) && $videoMapsDefault == 1) { ?>
                &nbsp;&bull;&nbsp;
                <strong><?php echo JText::_('COM_COMMUNITY_VIDEOS_LOCATION'); ?></strong>
                <a href="javascript:" title="<?php echo JText::_('COM_COMMUNITY_VIEW_LOCATION_TIPS'); ?>"><?php echo $video->location; ?></a>
            <?php } ?>
        </div>

        <div class="joms-js--video-tag-ct"></div>

        <?php

            $description = $video->getDescription();
            $excerpt = JHTML::_('string.truncate', $description, 300);

        ?>

        <br>
        <div>
            <h5 class="joms-text--title"><?php echo JText::_('COM_COMMUNITY_VIDEOS_PROFILE_VIDEO_DESCRIPTION') ?></h5>
            <div class="joms-js--desc-excerpt"><?php echo nl2br($excerpt); ?></div>
            <?php if ($description != $excerpt) { ?>
            <div class="joms-js--desc-fulltext" style="display:none;"><?php echo nl2br($description); ?></div>
            <button class="joms-button--neutral joms-js--btn-more" style="margin-top:5px;"><?php echo JText::_("COM_COMMUNITY_MORE"); ?></button>
            <?php } ?>
        </div>

        <?php if ( $wallCount > 0 || !empty($wallForm) ) { ?>
        <br>
        <div>
            <h5 class="joms-text--title"><?php echo JText::_('COM_COMMUNITY_COMMENTS') ?></h5>
            <div class="joms-stream__status--mobile">
                <a href="javascript:" onclick="joms.api.streamShowComments('<?php echo $video->id ?>', 'videos');">
                    <span class="joms-comment__counter--<?php echo $video->id; ?>"><?php echo $wallCount; ?></span>
                    <svg viewBox="0 0 16 16" class="joms-icon">
                        <use xlink:href="<?php echo CRoute::getURI(); ?>#joms-icon-bubble"></use>
                    </svg>
                </a>
            </div>
            <div style="display:none"><?php echo $wallViewAll; ?></div>
            <?php echo $wallContent; ?>
            <?php echo empty($wallForm) ? '' : $wallForm; ?>
        </div>
        <script>
            (function( w ) {
                w.joms_queue || (w.joms_queue = []);
                w.joms_queue.push(function( $ ) {
                    $('.joms-js--comments').prepend( $('.joms-js--more-comments').parent().html() );
                });
            })( window );
        </script>
        <?php } ?>

<?php if ( $enableVideoLocation ) { ?>
    </div>
</div>
<?php } else { ?>
</div>
<?php } ?>

<script>
    (function( w ) {
        var id = '<?php echo $video->id ?>',
            descHideLabel = '<?php echo JText::_("COM_COMMUNITY_HIDE", true) ?>',
            descMoreLabel = '<?php echo JText::_("COM_COMMUNITY_MORE", true) ?>',
            tagLabel = '<?php echo JText::_("COM_COMMUNITY_VIDEOS_IN_THIS_VIDEO", true) ?>',
            tagRemoveLabel = '<?php echo JText::_("COM_COMMUNITY_REMOVE", true) ?>',
            tagDoneLabel = '<?php echo JText::_("COM_COMMUNITY_PHOTO_DONE_TAGGING", true) ?>',
            tagTagBtnLabel = '<?php echo JText::_("COM_COMMUNITY_TAG_THIS_VIDEO", true) ?>',
            tags = [],
            groupid = '<?php echo $video->groupid > 0 ? $video->groupid : '' ?>',
            eventid = '<?php echo $video->eventid > 0 ? $video->eventid : '' ?>',
            btnTag;

        <?php

            foreach ($video->tagged as $tagItem) {
                echo PHP_EOL . '        tags.push(';
                echo json_encode(array(
                    'id' => $tagItem->id,
                    'userId' => $tagItem->userid,
                    'displayName' => addslashes( $tagItem->user->getDisplayName() ),
                    'profileUrl' => CRoute::_('index.php?option=com_community&view=profile&userid=' . $tagItem->userid, false),
                    'videoId' => $video->id,
                    'canRemove' => $tagItem->canRemoveTag
                ));
                echo ');';
            }

        ?>

        function init() {
            joms.jQuery( document ).on( 'click', function( e ) {
                var $ct;
                try {
                    $ct = joms.jQuery( e.target ).closest('.video-player,.joms-js--btn-tag');
                    $ct.length || tagCancel();
                } catch (e) {}
            });

            joms.jQuery('.joms-js--video-tag-ct').html( tagBuildHtml() );
            joms.jQuery('.joms-js--video-tag-ct').on( 'click', '.joms-js--video-tag-remove', tagRemove );
            joms.jQuery('.joms-js--btn-more').on( 'click', toggleDesc );

            btnTag = joms.jQuery('.joms-js--btn-tag');
            btnTag.on( 'click', tagToggle );

            initVideo();
        }

        function tagBuildHtml() {
            var html, item, str, i;

            if ( !tags || !tags.length ) {
                return '';
            }

            html = [];
            for ( i = 0; i < tags.length; i++ ) {
                item = tags[i];
                str = '<a href="' + item.profileUrl + '">' + item.displayName + '</a>';
                if ( item.canRemove ) {
                    str += ' (<a href="javascript:" class="joms-js--video-tag-remove" data-id="' + item.userId + '">' + tagRemoveLabel + '</a>)';
                }
                html.push( str );
            }

            html = html.join(', ');
            html = '<br><strong>' + tagLabel + '</strong><br>' + html;

            return html;
        }

        function tagRemove( e ) {
            var el = joms.jQuery( e.currentTarget ),
                userId = el.data('id');

            joms.ajax({
                func: 'videos,ajaxRemoveVideoTag',
                data: [ id, userId ],
                callback: function( json ) {
                    var i;

                    if ( json.error ) {
                        window.alert( stripTags( json.error ) );
                        return;
                    }

                    if ( json.success ) {
                        for ( i = 0; i < tags.length; i++ ) {
                            if ( +userId === +tags[i].userId ) {
                                tags.splice( i--, 1 );
                            }
                        }
                        joms.jQuery('.joms-js--video-tag-ct').html( tagBuildHtml() );
                    }
                }
            });
        }

        function tagToggle( e ) {
            if ( btnTag.data('tagging') ) {
                joms.util.videotag.destroy();
            } else {
                btnTag.data('tagging', 1);
                btnTag.html( tagDoneLabel );
                tagStart();
            }
        }

        function tagCancel() {
            joms.util.videotag.destroy();
        }

        function tagStart() {
            var indices = joms._.map( tags, function( item ) {
                return item.userId + '';
            });

            joms.util.videotag.create( joms.jQuery('.cVideo-Wrapper iframe,.cVideo-Wrapper video,.cVideo-Wrapper .joms-js--video').eq(0), indices, groupid, eventid );
            joms.util.videotag.on( 'tagAdded', tagAdded );
            joms.util.videotag.on( 'destroy', function() {
                btnTag.removeData('tagging');
                btnTag.html( tagTagBtnLabel );
            });
        }

        function tagAdded( userId ) {
            joms.ajax({
                func: 'videos,ajaxAddVideoTag',
                data: [ id, userId ],
                callback: function( json ) {
                    if ( json.error ) {
                        window.alert( stripTags( json.error ) );
                        return;
                    }

                    if ( json.success ) {
                        tags.push( json.data );
                        joms.jQuery('.joms-js--video-tag-ct').html( tagBuildHtml() );
                    }
                }
            });
        }

        function toggleDesc() {
            var $excerpt = joms.jQuery('.joms-js--desc-excerpt'),
                $fulltext = joms.jQuery('.joms-js--desc-fulltext'),
                $btn = joms.jQuery('.joms-js--btn-more');

            if ( $excerpt.is(':visible') ) {
                $excerpt.hide();
                $fulltext.show();
                $btn.html( descHideLabel );
            } else {
                $excerpt.show();
                $fulltext.hide();
                $btn.html( descMoreLabel );
            }
        }

        function initVideo() {
            var cssVideo = '.joms-js--video',
                video = joms.jQuery('.cVideo-Wrapper').find( cssVideo );

            if ( !video.length ) {
                return;
            }

            joms.loadCSS( joms.ASSETS_URL + 'vendors/mediaelement/mediaelementplayer.min.css' );
            video.on( 'click.joms-video', cssVideo + '-play', function() {
                var $el = joms.jQuery( this ).closest( cssVideo );
                joms.util.video.play( $el, $el.data() );
            });
        }

        w.joms_queue || (w.joms_queue = []);
        w.joms_queue.push(function() {
            init();
        });

    })( window );
</script>
