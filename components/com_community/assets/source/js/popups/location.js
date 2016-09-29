(function( root, factory ) {

    joms.popup || (joms.popup = {});
    joms.popup.location = factory( root, joms.popup.location || {});

    define([ 'popups/location.view' ], function() {
        return joms.popup.location;
    });

})( window, function( window, sub ) {

// Exports.
return joms._.extend({}, sub );

});
