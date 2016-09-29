(function( root, factory ) {

    joms.popup || (joms.popup = {});
    joms.popup.album = factory( root, joms.popup.album || {});

    define([
        'popups/album.addfeatured',
        'popups/album.removefeatured',
        'popups/album.remove'
    ], function() {
        return joms.popup.album;
    });

})( window, function( window, sub ) {

// Exports.
return joms._.extend({}, sub );

});
