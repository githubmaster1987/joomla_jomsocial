(function( root, $, factory ) {

    joms.view || (joms.view = {});
    joms.view.misc = factory( root, $ );

    define(function() {
        return joms.view.misc;
    });

})( window, joms.jQuery, function( window, $ ) {

var $main, $sidebar;

function initialize() {
    $main = $('.joms-main');
    $sidebar = $('.joms-sidebar');

    rearrangeModuleDiv();
    $( window ).on( 'resize', rearrangeModuleDiv );
}

var rearrangeModuleDiv = joms._.debounce(function() {
    if ( joms.screenSize() !== 'large' ) {
        if ( $sidebar.nextAll('.joms-main').length ) {
            $sidebar.insertAfter( $main );
        }
    } else {
        if ( $sidebar.prevAll('.joms-main').length ) {
            $sidebar.insertBefore( $main );
        }
    }
}, 500 );

var fixSVG = joms._.debounce(function() {
    var url = window.joms_current_url,
        svgFixClass = 'joms-icon--svg-fixed',
        svg;

    if ( !url ) {
        return;
    }

    svg = $('.joms-icon use').not('.' + svgFixClass );
    svg.each(function() {
        var href = ( this.getAttribute('xlink:href') || '' ),
            path = href.replace( /^[^#]*#/, '#' );

        if ( href === url + path ) {
            svgFixClass += ' joms-icon--svg-unmodified';
        } else {
            this.setAttribute( 'xlink:href', url + path );
        }

        this.setAttribute( 'class', svgFixClass );
    });
}, 200 );

// Exports.
return {
    start: initialize,
    fixSVG: fixSVG
};

});
