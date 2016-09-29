(function( root, $, factory ) {

    joms.view || (joms.view = {});
    joms.view.streams = factory( root, $ );

    define([ 'utils/hovercard', 'utils/photos', 'utils/video', 'functions/tagging' ], function() {
        return joms.view.streams;
    });

})( window, joms.jQuery, function( window, $ ) {

var container, adAgencySettings, adAgencyImpressions;

function initialize() {
    uninitialize();
    container = $('.joms-stream__wrapper');

    // Initialize only when container is available.
    if ( !container.length )
        return;

    // Initialize comment box.
    initInputbox();

    // Initialize jquery montage plugin.
    initPhotoArranger();

    // Initialize media element.
    initVideoPlayers();

    // Disable adagency, infinite scroll, and recent activities on single activity page.
    if ( !window.joms_singleactivity && !window.joms_filter_hashtag && !window.joms_filter_keyword ) {

        // Initialize ad agency.
        if ( +window.joms_adagency ) {
            initAdAgency();
        }

        // Initialize infinite scroll.
        if ( +window.joms_infinitescroll ) {
            autoloadOlderActivities();
        }

        // Get recent activities.
        if ( +window.joms_enable_refresh ) {
            getRecentActivitiesCount();
        }
    }

    var filterbar = document.getElementsByClassName('joms-activity-filter-action');
    if ( filterbar && filterbar.length ) {
        window.FastClick.attach( filterbar[0] );
    }
}

function uninitialize() {
    if ( container ) {
        container.off();
    }
}

function initPhotoArranger() {
    var initialized = '.joms-js--initialized',
        $containers = $('.joms-media--images').not( initialized );

    $containers.each(function() {
        var $ct = $( this ),
            $imgs = $ct.find('img'),
            counter = 0;

        $imgs.each(function() {
            var $img = $( this );

            $('<img>').load(function() {
                counter++;
                if ( counter === $imgs.length ) {
                    $ct.siblings('.joms-media--loading').remove();
                    $ct.addClass( initialized.substr(1) );
                    $imgs.show();
                    joms.util.photos.arrange( $ct );
                }

            }).attr( 'src', $img.attr('src') );
        });
    });
}

function initVideoPlayers() {
    var initialized = '.joms-js--initialized',
        cssVideos = '.joms-js--video',
        videos = $('.joms-stream__body').find( cssVideos ).not( initialized ).addClass( initialized.substr(1) );

    if ( !videos.length ) {
        return;
    }

    joms.loadCSS( joms.ASSETS_URL + 'vendors/mediaelement/mediaelementplayer.min.css' );
    videos.on( 'click.joms-video', cssVideos + '-play', function() {
        var $el = $( this ).closest( cssVideos );
        joms.util.video.play( $el, $el.data() );
    });

    if ( joms.ios ) {
        setTimeout(function() {
            videos.find( cssVideos + '-play' ).click();
        }, 1000 );
    }
}

function initInputbox() {
    joms.fn.tagging.initInputbox();
}

function getEdgeStreamId( edge ) {
    var stream, ids;

    stream = container.find('.joms-stream').not('.joms-stream--adagency');
    if ( !stream.length )
        return 0;

    ids = [];
    stream.each(function() {
        ids.push( +$(this).data('stream-id') );
    });

    ids.sort(function( a, b ) {
        return a - b;
    });

    return ids[ edge === 'last' ? 0 : ids.length - 1 ];
}

function getFilter() {
    var $ct = container.children('.joms-stream__container');
    return {
        filter: $ct.data('filter'),
        filterId: $ct.data('filterid'),
        filterValue: $ct.data('filter-value')
    };
}

function getRecentActivitiesCount() {
    var o = getRecentActivitiesCount,
        id, filter;

    if ( o.loading )
        return;

    if ( !( id = getEdgeStreamId() ) )
        return;

    filter = getFilter();

    o.loading = true;
    o.xhr && o.xhr.abort();
    o.xhr = joms.ajax({
        func: 'activities,ajaxGetRecentActivitiesCount',
        data: [ id, filter.filter, filter.filterId, filter.filterValue ],
        callback: function( json ) {
            var count   = +json.count,
                delay   = +json.nextPingDelay,
                $latest = $('.joms-js--stream-latest'),
                $link;

            o.loading = false;
            o.xhr     = null;

            if ( count > 0 ) {
                $link = $( '<a href="javascript:">' + json.html + '</a>' );
                $link.on( 'click', getRecentActivities );
                $latest.html( $link ).show();
            } else {
                $latest.hide().empty();
            }

            if ( delay > 0 ) {
                joms._.delay( getRecentActivitiesCount, delay );
            }
        }
    });
}

function getRecentActivities() {
    var o = getRecentActivities,
        id, filter;

    if ( o.loading )
        return;

    if ( !( id = getEdgeStreamId() ) )
        return;

    filter = getFilter();

    o.loading = true;
    o.xhr && o.xhr.abort();
    o.xhr = joms.ajax({
        func: 'activities,ajaxGetRecentActivities',
        data: [ id, filter.filter, filter.filterId, filter.filterValue ],
        callback: function( json ) {
            var $items = $( $.trim( json.html ) ).filter('.joms-stream__wrapper').find('.joms-stream'),
                $latest = $('.joms-js--stream-latest'),
                i;

            o.loading = false;

            if ( $items.length ) {
                for ( i = $items.length - 1; i >= 0; i-- ) {
                    // Prevent duplicated stream.
                    if ( ! ( $('.joms-js--stream-' + $items.eq(i).data('stream-id') ).length ) ) {
                        container.find('.joms-stream__container').prepend( $items.eq( i ) );
                    }
                }
            }

            $latest.hide();

            initInputbox();
            initPhotoArranger();
            initVideoPlayers();
        }
    });
}

function getOlderActivities() {
    var o = getOlderActivities,
        id, filter, btn, loading;

    if ( o.loading )
        return;

    if ( !( id = getEdgeStreamId('last') ) )
        return;

    filter = getFilter();

    o.loading = true;
    btn = container.find('#activity-more');
    loading = btn.find('.loading');
    btn = btn.find('.joms-button--primary');

    btn.hide();
    loading.show();

    joms.ajax({
        func: 'activities,ajaxGetOlderActivities',
        data: [ id, filter.filter, filter.filterId, filter.filterValue ],
        callback: function( json ) {
            var isLast = false,
                $items;

            o.loading = false;
            loading.hide();

            if ( json.html ) {
                $items = $( $.trim( json.html ) ).filter('.joms-stream__wrapper').find('.joms-stream');
                if ( $items.length ) {
                    container.find('.joms-stream__container').append( $items );
                } else {
                    isLast = true;
                }
            }

            initInputbox();
            initPhotoArranger();
            initVideoPlayers();
            injectAdAgencyItem();

            if ( !isLast ) {
                btn.show();
            }
        }
    });
}

var autoloadOlderActivities = function() {
    var o, div, win, doc, loading, treshhold, lastScrollTop;

    if ( joms.mobile )
        return false;

    div = $('.joms-stream__loadmore');
    win = $( window );
    doc = $( document );
    loading = div.find('.loading');

    treshhold = Math.max( +window.joms_autoloadtrigger || 0, 20 );
    lastScrollTop = 0;

    div.find('a').hide();

    o = autoloadOlderActivities = joms._.debounce(function() {
        var scrollTop = win.scrollTop(),
            winHeight = win.height(),
            direction, id, filter;

        direction = scrollTop < lastScrollTop ? 'up' : 'down';
        lastScrollTop = scrollTop;

        if ( direction !== 'down' ) {
            return;
        }

        if ( ( scrollTop + winHeight ) < ( doc.height() - treshhold ) ) {
            return;
        }

        if ( o.loading ) {
            return;
        }

        o.loading = true;
        loading.show();

        if ( !( id = getEdgeStreamId('last') ) ) {
            return;
        }

        filter = getFilter();

        joms.ajax({
            func: 'activities,ajaxGetOlderActivities',
            data: [ id, filter.filter, filter.filterId, filter.filterValue ],
            callback: function( json ) {
                var isLast = false,
                    $items;

                loading.hide();

                if ( json.html ) {
                    $items = $( $.trim( json.html ) ).filter('.joms-stream__wrapper').find('.joms-stream');
                    if ( $items.length ) {
                        container.find('.joms-stream__container').append( $items );
                    } else {
                        isLast = true;
                    }
                }

                initInputbox();
                initPhotoArranger();
                initVideoPlayers();
                injectAdAgencyItem();

                if ( isLast )
                    return;

                setTimeout(function() {
                    o.loading = false;
                }, 800 );
            }
        });

    }, 50 );

    win.on( 'scroll', autoloadOlderActivities );
};

function initAdAgency() {
    joms.ajax({
        func: 'system,ajaxGetAdagency',
        callback: function( json ) {
            adAgencySettings = json || {};

            // Shuffle ads.
            if ( adAgencySettings.ads && adAgencySettings.ads.length ) {
                adAgencySettings.ads = joms._.shuffle( adAgencySettings.ads );
            }

            injectAdAgencyItem();
        }
    });
}

function createAdAgencyItem( config, ad ) {
    var html;

    html  = '<div data-stream-type="adagency" class="joms-stream joms-stream--adagency">';
    html +=   '<div class="joms-stream__header">';
    html +=     '<div class="joms-avatar--stream">';
    html +=       '<a href="' + ad.on_click_url + '" target="_blank" onclick="window.open(\'' + ad.on_click_url + '\'); return false;">';
    html +=         '<img src="' + ad.banner_avatar + '">';
    html +=       '</a>';
    html +=     '</div>';
    html +=     '<div class="joms-stream__meta">';
    html +=       '<a class="joms-stream__user" href="' + ad.on_click_url + '" target="_blank" onclick="window.open(\'' + ad.on_click_url + '\'); return false;">' + ad.banner_headline + '</a>';
    html +=       '<a href="' + ad.on_click_url + '" target="_blank" onclick="window.open(\'' + ad.on_click_url + '\'); return false;"><span class="joms-stream__time"><small>' + (ad.short_url_to_promote || ad.url_to_promote) + '</small></span></a>';
    html +=     '</div>';
    html +=   '</div>';
    html +=   '<div class="joms-stream__body">';
    html +=     '<p>' + ad.banner_text + '</p>';
    html +=     '<div class="joms-media--image">';
    html +=       '<a href="' + ad.on_click_url + '" target="_blank" onclick="window.open(\'' + ad.on_click_url + '\'); return false;">';
    html +=         '<img src="' + ad.banner_image_content + '">';
    html +=       '</a>';
    html +=     '</div>';
    html +=   '</div>';

    if ( +config.show_sponsored_stream_info || +config.show_create_ad_link ) {
        html += '<div class="joms-stream__actions">';
        if ( +config.show_sponsored_stream_info ) {
            html += '<span style="float:left">' + config.sponsored_stream_info_text + '</span>';
        }
        if ( +config.show_create_ad_link ) {
            html += '<a href="' + config.create_ad_link + '" style="float:right">' + config.create_ad_link_text + '</a>';
        }
        html += '<div style="clear:both"></div>';
        html += '</div>';
    }

    html += '</div>';
    return html;
}

function injectAdAgencyItem() {
    var ads, config, after, every, counter, isAfter, pageMap, isLoggedIn, isPublic;

    if ( !(adAgencySettings && adAgencySettings.config && adAgencySettings.ads && adAgencySettings.ads.length) ) {
        return;
    }

    ads     = adAgencySettings.ads;
    config  = adAgencySettings.config;
    after   = +config.display_stream_ads_after_value;
    every   = +config.display_stream_ads_every_value;
    isAfter = +config.display_stream_ads;
    counter = 0;

    isLoggedIn = +window.joms_my_id;
    if ( !isLoggedIn ) {
        for ( var i = ads.length - 1; i >= 0; i-- ) {
            isPublic = +ads[i].banner_access;
            if ( !isPublic ) {
                ads.splice( i, 1 );
            }
        }
    }

    if ( !ads.length ) {
        return;
    }

    pageMap = {
        frontpage : 'front_page_stream',
        profile   : 'profile_stream',
        groups    : 'group_stream',
        events    : 'event_stream'
    };

    if ( ( config.js_stream_ads_on || [] ).indexOf( pageMap[ window.joms_page ] ) < 0 ) {
        return;
    }

    container.find('.joms-stream').not('.joms-stream--adagency').each(function( i ) {
        var elem, next;

        // Show ad after 'x' stream items.
        if ( isAfter ) {

            if ( !after ) {
                return false;
            }

            if ( i === after - 1 ) {
                elem = $( this );
                next = elem.next();
                if ( !next.length || !next.hasClass('joms-stream--adagency') ) {
                    elem.after( createAdAgencyItem( config, ads[ counter ] ) );
                    increaseAdAgencyImpression( ads[ counter ] );
                }

                return false;
            }

        // Show ad every 'x' stream items.
        } else {

            if ( !every ) {
                return false;
            }

            if ( (i + 1) % every === 0 ) {
                elem = $( this );
                next = elem.next();
                if ( !next.length || !next.hasClass('joms-stream--adagency') ) {
                    elem.after( createAdAgencyItem( config, ads[ counter % ads.length ] ) );
                    increaseAdAgencyImpression( ads[ counter % ads.length ] );
                }

                counter++;
            }

        }
    });
}

function increaseAdAgencyImpression( ad ) {
    var id = [
        ad.advertiser_id,
        ad.campaign_id,
        ad.banner_id,
        ad.campaign_type
    ].join('-');

    adAgencyImpressions || (adAgencyImpressions = {});

    if ( adAgencyImpressions[ id ] ) {
        return;
    }

    adAgencyImpressions[ id ] = true;

    joms.ajax({
        func: 'system,ajaxAdagencyGetImpression',
        data: [ ad.advertiser_id, ad.campaign_id, ad.banner_id, ad.campaign_type ],
        callback: function() {}
    });
}

// Exports.
return {
    start: initialize,
    stop: uninitialize,
    loadMore: getOlderActivities
};

});
