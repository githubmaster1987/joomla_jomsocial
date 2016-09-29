(function( root, factory ) {

    joms.popup || (joms.popup = {});
    joms.popup.stream = factory( root, joms.popup.stream || {});

    define([
        'popups/stream.remove',
        'popups/stream.editlocation',
        'popups/stream.addfeatured',
        'popups/stream.removefeatured',
        'popups/stream.removelocation',
        'popups/stream.removemood',
        'popups/stream.report',
        'popups/stream.selectprivacy',
        'popups/stream.share',
        'popups/stream.showcomments',
        'popups/stream.showlikes',
        'popups/stream.showothers'
    ], function() {
        return joms.popup.stream;
    });

})( window, function( window, sub ) {

// Exports.
return joms._.extend({}, sub );

});
