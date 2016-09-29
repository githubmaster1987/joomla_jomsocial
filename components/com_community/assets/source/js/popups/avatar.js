(function( root, factory ) {

    joms.popup || (joms.popup = {});
    joms.popup.avatar = factory( root, joms.popup.avatar || {});

    define([
        'popups/avatar.change',
        'popups/avatar.remove',
        'popups/avatar.rotate'
    ], function() {
        return joms.popup.avatar;
    });

})( window, function( window, sub ) {

// Exports.
return joms._.extend({}, sub );

});

