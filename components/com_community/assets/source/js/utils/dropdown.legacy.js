(function( root, factory ) {

    // Factory.
    joms.util || (joms.util = {});
    joms.util.dropdownLegacy = factory( root );

})( window, function() {

var
    // Css classes.
    cssDropdown = 'joms-legacy-dropdown',
    cssOpened   = cssDropdown + '--opened',

    // Dot-prefixed css classes.
    dotDropdown = '.' + cssDropdown,
    dotOpened   = '.' + cssOpened,

    // Document.
    doc;

function toggle( e ) {
    var el = joms.jQuery( e.target ),
        ct = el.closest( dotDropdown ),
        isOpened = false;

    if ( ct.length ) {
        isOpened = ct.hasClass( cssOpened );
        ct[ isOpened ? 'removeClass' : 'addClass' ]( cssOpened );
    }

    if ( !isOpened ) {
        doc.find( dotOpened ).not( ct ).removeClass( cssOpened );
    }
}

function initialize() {
    uninitialize();
    doc || (doc = joms.jQuery( document.body ));
    doc.on( 'click.' + cssDropdown, toggle );
}

function uninitialize() {
    doc && doc.off( 'click.' + cssDropdown );
}

// Public methods.
return {

    start: function() {
        initialize();
    },

    stop: function() {
        uninitialize();
    }

};

});
