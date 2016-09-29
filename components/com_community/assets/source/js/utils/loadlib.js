(function( root, factory ) {

    joms.util || (joms.util = {});
    joms.util.loadLib = factory( root );

})( window, function( window ) {

// @todo: Google Map library loader.
function loadGmap( fn ) {
    return fn();
}

// MediaElement.js library loader.
function loadMediaElement( fn ) {
    if ( window.MediaElement ) {
        return fn();
    }

    joms.$LAB.script( joms.ASSETS_URL + 'vendors/mediaelement/mediaelement-and-player.min.js' ).wait(function () {
        fn();
    });
}

// Flowplayer library loader.
function loadFlowplayer( fn ) {
    if ( window.flowplayer ) {
        return fn();
    }

    joms.$LAB.script( joms.ASSETS_URL + 'flowplayer/flowplayer-3.2.6.min.js' ).wait(function () {
        fn();
    });
}

// Plupload library loader.
function loadPlupload( fn ) {
    if ( window.plupload ) {
        return fn();
    }

    joms.$LAB.script( joms.ASSETS_URL + 'vendors/plupload.min.js' ).wait(function() {
        fn();
    });
}

// Trumbowyg rich editor loader.
function loadTrumbowyg( fn ) {
    if ( !joms.jQuery ) {
        return false;
    }

    if ( joms.jQuery.trumbowyg ) {
        return fn();
    }

    joms.loadCSS( joms.ASSETS_URL + 'vendors/trumbowyg/ui/trumbowyg.min.css' );
    joms.$LAB.script( joms.ASSETS_URL + 'vendors/trumbowyg/trumbowyg.min.js' ).wait()
        .script( joms.ASSETS_URL + 'vendors/trumbowyg/plugins/base64/trumbowyg.base64.min.js' ).wait()
        .script( joms.ASSETS_URL + 'vendors/trumbowyg/plugins/upload/trumbowyg.upload.js' ).wait(function() {
            fn();
    });
}

// Load dragsort library.
function loadDragsort( fn ) {
    if ( window.Sortable ) {
        return fn();
    }

    joms.$LAB.script( joms.ASSETS_URL + 'dragsort/jquery.dragsort-0.5.1.min.js' ).wait(function() {
        fn();
    });
}

function load( lib, fn ) {
    if ( lib === 'gmap' ) {
        return loadGmap( fn );
    }

    if ( lib === 'mediaelement' ) {
        return loadMediaElement( fn );
    }

    if ( lib === 'flowplayer' ) {
        return loadFlowplayer( fn );
    }

    if ( lib === 'plupload' ) {
        return loadPlupload( fn );
    }

    if ( lib === 'trumbowyg' ) {
        return loadTrumbowyg( fn );
    }

    if ( lib === 'dragsort' ) {
        return loadDragsort( fn );
    }

    fn();
}

// Exports.
return load;

});
