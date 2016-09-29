(function( root, $, factory ) {

    joms.util || (joms.util = {});
    joms.util.video = factory( root, $ );

    define([ 'utils/loadlib' ], function() {
        return joms.util.video;
    });

})( window, joms.jQuery, function( window, $ ) {

function play( $ct, data ) {
    if ( data.type === 'file' ) {
        _playFile( $ct, data );
    } else if ( data.type === 'youtube' ) {
        window.joms_videoplayer_native ?  _playYoutubeNativePlayer( $ct, data ) : _playYoutube( $ct, data );
    } else if ( data.type === 'vimeo' ) {
        _playVimeo( $ct, data );
    } else if ( data.type === 'myspace' ) {
        _playMySpace( $ct, data );
    } else if ( data.type === 'blip' ) {
        _playBlip( $ct, data );
    } else if ( data.type === 'dailymotion' ) {
        _playDailyMotion( $ct, data );
    } else if ( data.type === 'liveleak' ) {
        _playLiveLeak( $ct, data );
    } else if ( data.type === 'flickr' ) {
        _playFlickr( $ct, data );
    } else if ( data.type === 'yahoo' ) {
        _playYahoo( $ct, data );
    } else if ( data.type === 'metacafe' ) {
        _playMetacafe( $ct, data );
    } else {
        _playOther( $ct, data );
    }
}

function _playFile( $ct, data ) {
    var $player = $ct.find('.joms-media__thumbnail'),
        $video, id, fileType;

    id = joms._.uniqueId('joms-js--video-');
    fileType = data.path.match(/\.flv$/) ? 'flv' : 'mp4';

    if ( fileType === 'flv' ) {
        $video = $(
            '<div class="flowplayer" id="' + id + '" style="width:100%;height:281px;"></div>');
    } else {
        $video = $(
            '<video id="' + id + '" width="480" height="360" preload="metadata" autoplay="autoplay">' +
            '<source src="' + data.path + '" type="video/mp4" />' +
            '</video>'
        );
    }

    $ct.addClass('being-played');
    $player.html( $video );
    _initMediaElement( id, data.type, {
        fileType: fileType,
        filePath: data.path
    });
}

function _playYoutube( $ct, data ) {
    var id, path, $video, $player;

    id = joms._.uniqueId('joms-js--video-');

    path = data.path;
    if (joms.ios) {
        path = path.replace(/#.*$/, '');
        path = path.replace(/&t=\d+/, '');
    }

    $video = $(
        '<video id="' + id + '" controls="control" preload="none">' +
        '<source src="' + path + '" type="video/youtube" />' +
        '</video>'
    );

    $video.css({ visibility: 'hidden' });

    $player = $ct.find('.joms-media__thumbnail');
    if ( ! $player.length ) {
        $player = $ct;
    }

    $ct.addClass('being-played');
    $player.html( $video );
    _initMediaElement( id, data.type );
}

function _playYoutubeNativePlayer( $ct, data ) {
    var $player = $ct.find('.joms-media__thumbnail');
    if ( ! $player.length ) {
        $player = $ct;
    }

    $ct.addClass('being-played joms-media--video-native');
    $player.html(
        '<iframe src="//www.youtube.com/embed/' + data.id +
        '?autoplay=1&rel=0" width="500" height="281" frameborder="0" allowfullscreen></iframe>'
    );
}

function _playVimeo( $ct, data ) {
    var $player = $ct.find('.joms-media__thumbnail');

    $ct.addClass('being-played');
    $player.html(
        '<iframe src="//player.vimeo.com/video/' + data.id +
        '?autoplay=1" width="500" height="281" frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>'
    );
}

function _playMySpace( $ct, data ) {
    var $player = $ct.find('.joms-media__thumbnail'),
        path = data.path;

    path = path.replace( /^https?:/, '' );
    path = path.replace( /myspace.com\/myspace\//, 'myspace.com/play/' );
    path = path.replace( /\/(\d+)$/, '-$1' );

    $ct.addClass('being-played');
    $player.html(
        '<iframe src="' + path +
        '" width="500" height="281" frameborder="0" allowtransparency="true" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>'
    );
}

function _playBlip( $ct, data ) {
    var $player = $ct.find('.joms-media__thumbnail'),
        path = '//blip.tv/play/' + data.id;

    $ct.addClass('being-played');
    $player.html( '<iframe src="' + path + '" width="500" height="281" frameborder="0" allowfullscreen></iframe>' );
}

function _playDailyMotion( $ct, data ) {
    var $player = $ct.find('.joms-media__thumbnail'),
        path = data.path;

    path = path.replace( /^https?:/, '' );
    path = path.replace( /\/video\/([^_]+)_.+$/, '/embed/video/$1' );

    $ct.addClass('being-played');
    $player.html( '<iframe src="' + path + '" width="500" height="281" frameborder="0" allowfullscreen></iframe>' );
}


function _playLiveLeak( $ct, data ) {
    var $player = $ct.find('.joms-media__thumbnail'),
        path = '//www.liveleak.com/ll_embed?i=' + data.id;

    $ct.addClass('being-played');
    $player.html( '<iframe src="' + path + '" width="500" height="281" frameborder="0" allowfullscreen></iframe>' );
}

function _playFlickr( $ct, data ) {
    var $player = $ct.find('.joms-media__thumbnail'),
        id = data.id.replace( /^.*\/(\d+)$/, '$1' ),
        path = 'https://www.flickr.com/apps/video/stewart.swf?photo_id=' + id;

    $ct.addClass('being-played');
    $player.html( '<embed src="' + path + '" width="500" height="281"  wmode="transparent" allowFullScreen="true" type="application/x-shockwave-flash" />' );
}

function _playYahoo( $ct, data ) {
    var $player = $ct.find('.joms-media__thumbnail'),
        path = data.path;

    path = path.replace('www.yahoo.com/movies/v', 'movies.yahoo.com/video');
    path = path + '?format=embed&player_autoplay=true';

    $ct.addClass('being-played');
    $player.html( '<iframe src="' + path + '" width="500" height="281" frameborder="0" allowfullscreen="true" mozallowfullscreen="true" webkitallowfullscreen="true" allowtransparency="true"></iframe>' );
}

function _playMetacafe( $ct, data ) {
    var $player = $ct.find('.joms-media__thumbnail'),
        path = 'http://www.metacafe.com/embed/' + data.id;

    $ct.addClass('being-played');
    $player.html( '<iframe src="' + path + '" width="500" height="281" frameborder="0" allowfullscreen></iframe>' );
}

function _playOther( $ct, data ) {
    window.open( data.path );
}

function _initMediaElement( id, type, data ) {
    if ( type === 'file' && data.fileType === 'flv' ) {
        joms.util.loadLib( 'flowplayer', function () {
            window.flowplayer( id, {
                    src: joms.ASSETS_URL + 'flowplayer/flowplayer-3.2.7.swf',
                    wmode: 'opaque'
                }, {
                    streamingServer: 'lighttpd',
                    playlist: [{
                        url: data.filePath,
                        autoPlay: false,
                        autoBuffering: true,
                        provider: 'lighttpd',
                        scaling: 'scale'
                    }],
                    plugins: {
                        lighttpd: {
                            url: joms.ASSETS_URL + 'flowplayer/flowplayer.pseudostreaming-3.2.7.swf',
                            queryString: window.escape('?target=${start}')
                        },
                        controls: {
                            url: joms.ASSETS_URL + 'flowplayer/flowplayer.controls-3.2.5.swf'
                        }
                    }

                }
            );
        });

    } else {
        joms.util.loadLib( 'mediaelement', function () {
            var $elem = $( '#' + id ).css({ visibility: '' });

            var options = {
                iPadUseNativeControls: type === 'file' ? true : false,
                iPhoneUseNativeControls: type === 'file' ? true : false,
                success: function( me, el, pl ) {
                    setTimeout(function() {
                        pl.disableControls();
                        pl.enableControls();
                    }, 1 );

                    if ( me.pluginType === 'flash' ) {
                        me.addEventListener( 'canplay', function() {
                            me.play();
                        }, false );
                    } else if ( joms.mobile && ( ( me.pluginType === 'youtube' ) || ( me.pluginType === 'vimeo' ) ) ) {
                        // do nothing
                    } else {
                        me.play();
                    }
                }
            };

            // #638 Play video on firefox is not as good as on chrome.
            if ( type === 'youtube' ) {
                options.defaultVideoWidth = $elem.width();
                options.defaultVideoHeight = $elem.height();
            }

            $elem.mediaelementplayer( options );
        });
    }
}

// Exports.
return {
    play: play
};

});
