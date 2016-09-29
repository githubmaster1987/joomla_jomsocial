(function( root, factory ) {

    joms.popup || (joms.popup = {});
    joms.popup.photo = factory( root, joms.popup.photo || {});

    define([
        'popups/photo.open',
        'popups/photo.remove',
        'popups/photo.report',
        'popups/photo.setavatar',
        'popups/photo.setcover',
        'popups/photo.upload',
        'popups/photo.zoom',
        'popups/photo.setalbum'
    ], function() {
        return joms.popup.photo;
    });

})( window, function( window, sub ) {

// Exports.
return joms._.extend({}, sub );

});
