(function( root, factory ) {

    joms.popup || (joms.popup = {});
    joms.popup.cover = factory( root, joms.popup.cover || {});

    define([
        'popups/cover.change',
        'popups/cover.remove'
    ], function() {
        return joms.popup.cover;
    });

})( window, function( window, sub ) {

// Exports.
return joms._.extend({}, sub );

});
