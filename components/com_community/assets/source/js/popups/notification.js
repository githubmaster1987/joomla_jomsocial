(function( root, factory ) {

    joms.popup || (joms.popup = {});
    joms.popup.notification = factory( root, joms.popup.notification || {});

    define([
        'popups/notification.global',
        'popups/notification.friend',
        'popups/notification.pm'
    ], function() {
        return joms.popup.notification;
    });

})( window, function( window, sub ) {

// Exports.
return joms._.extend({}, sub );

});
