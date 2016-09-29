(function( root, factory ) {

    joms.popup || (joms.popup = {});
    joms.popup.user = factory( root, joms.popup.user || {});

    define([
        'popups/user.changevanityurl',
        'popups/user.addfeatured',
        'popups/user.removefeatured',
        'popups/user.ban',
        'popups/user.unban',
        'popups/user.block',
        'popups/user.unblock',
        'popups/user.ignore',
        'popups/user.report',
        'popups/user.unignore'
    ], function() {
        return joms.popup.user;
    });

})( window, function( window, sub ) {

// Exports.
return joms._.extend({}, sub );

});

