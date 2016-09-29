;(function( root ) {

/**
 * Relative path for assets loading.
 * @name joms.BASE_URL
 * @const {string}
 */
joms.BASE_URL = root.joms_base_url;
delete root.joms_base_url;

// Fix www/non-www redirection.
var reDomain = /https?:\/\/[^/]+/;
var baseDomain = joms.BASE_URL.match( reDomain );
var realDomain = (location.href).match( reDomain );
if ( baseDomain && realDomain && baseDomain[0] !== realDomain[0] ) {
    joms.BASE_URL.replace( reDomain, realDomain[0] );
}

/**
 * Relative path for assets loading.
 * @name joms.ASSETS_URL
 * @const {string}
 */
joms.ASSETS_URL = root.joms_assets_url;
delete root.joms_assets_url;

/**
 * Detect mobile browser.
 * @name joms.mobile
 * @const {boolean}
 */
joms.mobile = (function() {
    var mobile = /android|webos|iphone|ipad|ipod|blackberry|iemobile|opera mini/i;
    return mobile.test( navigator.userAgent );
})();

/**
 * Detect mobile safari (iOS) browser.
 * @name joms.ios
 * @const {boolean}
 */
joms.ios = (function() {
    var ios = /iphone|ipad|ipod/i;
    return ios.test( navigator.userAgent );
})();

/**
 * Detect screen size based on it's width and css breakpoint rule.
 * Breakpoints: 0 - 480 | 481 - 991 | 992 - ~
 * @function joms.screenSize
 */
joms.screenSize = function() {
    var width = window.innerWidth;
    if ( width <= 480 ) return 'small';
    if ( width <= 991 ) return 'medium';
    return 'large';
};

/**
 * jQuery.ajax wrapper to perform ajax request
 * @function joms.ajax
 * @param {object} options - Ajax call options
 */
joms.ajax = function( options ) {
    var url   = root.jax_live_site || '',
        token = root.jax_token_var || '_no_token_found_',
        data  = {};

    options || (options = {});

    // Match jax.call parameters.
    data[token]  = 1;
    data.task    = 'azrul_ajax';
    data.option  = options.option || 'community';
    data.func    = options.func;
    data.no_html = 1;

    delete options.option;
    delete options.func;

    // Build arguments.
    if ( options.data && options.data.length ) {
        for ( var i = 0, arg; i < options.data.length; i++ ) {
            arg = options.data[ i ];
            if ( typeof arg === 'string' )
                arg = arg.replace( /"/g, '&quot;' );
            if ( !joms._.isArray( arg ) )
                arg = [ '_d_', encodeURIComponent( arg ) ];
            data[ 'arg' + ( i + 2 ) ] = JSON.stringify( arg );
        }
    }

    var response;

    // Override options.
    options.url      = url;
    options.type     = 'post';
    options.dataType = 'json';
    options.data     = data;

    options.success = function( json ) {
        if ( json ) response = json;
    };

    options.complete = function() {
        response || (response = { error: 'Undefined error.' });

        if ( response.noLogin ) {
            joms.api && joms.api.login( response );
            joms.view.misc.fixSVG();
            return;
        }

        // Execute additional callbacks (if any).
        var stop;
        if ( joms._onAjaxReponseQueue &&
             joms._onAjaxReponseQueue[ data.func ] &&
             joms._onAjaxReponseQueue[ data.func ].length ) {
            for ( var i = 0; i < joms._onAjaxReponseQueue[ data.func ].length; i++ )  {
                if ( typeof joms._onAjaxReponseQueue[ data.func ][i] === 'function' ) {
                    if ( joms._onAjaxReponseQueue[ data.func ][i]( response ) === false ) {
                        stop = true;
                    }
                }
            }
        }

        if ( typeof options.callback === 'function' && ( !stop ) ) {
            options.callback( response );
        }

        joms.view.misc.fixSVG();
    };

    // Perform ajax request.
    return joms.jQuery.ajax( options );
};

/**
 * Hide non-jomsocial contents.
 * @function joms.____
 */
joms.____ = function() {
    var node = joms.jQuery('#community-wrap');
    while ( node.length && node[0].tagName.toLowerCase() !== 'body' ) {
        node.siblings().hide();
        node = node.parent();
        node.css({
            border: '0 none',
            padding: 0,
            marginTop: 0,
            marginBottom: 0,
            width: 'auto'
        });
    }
};

/**
 * Prints SVG icons into document.body for testing purpose.
 * @function joms._printSVGIcons
 */
joms._printSVGIcons = function() {
    var icons = [ 'home', 'newspaper', 'pencil', 'image', 'images', 'camera', 'play', 'film', 'camera2', 'bullhorn', 'library', 'profile', 'support', 'envelope', 'location', 'clock', 'bell', 'calendar', 'box-add', 'box-remove', 'bubble', 'bubbles', 'user', 'users', 'spinner', 'search', 'key', 'lock', 'wrench', 'cog', 'gift', 'remove', 'briefcase', 'switch', 'signup', 'list', 'menu', 'earth', 'link', 'eye', 'star', 'star2', 'star3', 'thumbs-up', 'happy', 'smiley', 'tongue', 'sad', 'wink', 'grin', 'cool', 'angry', 'evil', 'shocked', 'confused', 'neutral', 'wondering', 'warning', 'info', 'blocked', 'spam', 'close', 'checkmark', 'plus', 'arrow-right', 'arrow-left', 'tab', 'filter', 'console', 'share', 'facebook', 'libreoffice', 'file-zip', 'arrow-down', 'redo', 'tag', 'search-user' ];
    var $ct = joms.jQuery('<div>');

    for ( var i = 0; i < icons.length; i++ ) {
        $ct.append(
            '<svg viewBox="0 0 30 30" class="joms-icon" style="width:30px;height:30px">' +
            '<use xlink:href="#joms-icon-' + icons[i] +  '"></use>' +
            '</svg>'
        );
    }

    $ct.appendTo( document.body );
};

// AMD-style output.
if ( typeof define === 'function' ) define(function () {
	return root.joms;
});

})( this );
