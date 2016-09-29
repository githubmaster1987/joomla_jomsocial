(function( root, factory ) {

    joms.popup || (joms.popup = {});
    joms.popup.video = factory( root, joms.popup.video || {});

    define([
        'popups/video.open',
        'popups/video.add',
        'popups/video.edit',
        'popups/video.fetchthumbnail',
        'popups/video.linktoprofile',
        'popups/video.remove',
        'popups/video.report',
        'popups/video.addfeatured',
        'popups/video.removefeatured',
        'popups/video.removelinkfromprofile'
    ], function() {
        return joms.popup.video;
    });

})( window, function( window, sub ) {

// Exports.
return joms._.extend({}, sub );

});
