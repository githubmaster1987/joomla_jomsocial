(function( root, factory ) {

    joms.popup || (joms.popup = {});
    joms.popup.inbox = factory( root, joms.popup.inbox || {});

    define([
        'popups/inbox.addrecipient',
        'popups/inbox.remove',
        'popups/inbox.setread',
        'popups/inbox.setunread'
    ], function() {
        return joms.popup.inbox;
    });

})( window, function( window, sub ) {

// Exports.
return joms._.extend({}, sub );

});

