(function( root, factory ) {

    joms.popup || (joms.popup = {});
    joms.popup.event = factory( root, joms.popup.event || {});

    define([
        'popups/event.delete',
        'popups/event.invite',
        'popups/event.join',
        'popups/event.leave',
        'popups/event.response',
        'popups/event.addfeatured',
        'popups/event.rejectguest',
        'popups/event.removefeatured',
        'popups/event.report',
        'popups/event.banmember',
        'popups/event.unbanmember'
    ], function() {
        return joms.popup.event;
    });

})( window, function( window, sub ) {

// Exports.
return joms._.extend({}, sub );

});

