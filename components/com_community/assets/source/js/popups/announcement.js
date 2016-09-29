(function( root, factory ) {

    joms.popup || (joms.popup = {});
    joms.popup.announcement = factory( root, joms.popup.announcement || {});

    define([
        'popups/announcement.remove'
    ], function() {
        return joms.popup.announcement;
    });

})( window, function( window, sub ) {

// Exports.
return joms._.extend({}, sub );

});
