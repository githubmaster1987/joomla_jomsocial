(function( root, factory ) {

    joms.popup || (joms.popup = {});
    joms.popup.friend = factory( root, joms.popup.friend || {});

    define([
        'popups/friend.add',
        'popups/friend.addcancel',
        'popups/friend.approve',
        'popups/friend.reject',
        'popups/friend.remove',
        'popups/friend.response'
    ], function() {
        return joms.popup.friend;
    });

})( window, function( window, sub ) {

// Exports.
return joms._.extend({}, sub );

});
