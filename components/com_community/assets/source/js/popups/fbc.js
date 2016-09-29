(function( root, factory ) {

    joms.popup || (joms.popup = {});
    joms.popup.fbc = factory( root, joms.popup.fbc || {});

    define([
        'popups/fbc.update'
    ], function() {
        return joms.popup.fbc;
    });

})( window, function( window, sub ) {

// Exports.
return joms._.extend({}, sub );

});

