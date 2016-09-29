(function( root, factory ) {

    joms.popup || (joms.popup = {});
    joms.popup.group = factory( root, joms.popup.group || {});

    define([
        'popups/group.delete',
        'popups/group.invite',
        'popups/group.join',
        'popups/group.leave',
        'popups/group.addfeatured',
        'popups/group.removefeatured',
        'popups/group.report',
        'popups/group.unpublish',
        'popups/group.approve',
        'popups/group.removemember',
        'popups/group.banmember',
        'popups/group.unbanmember'
    ], function() {
        return joms.popup.group;
    });

})( window, function( window, sub ) {

// Exports.
return joms._.extend({}, sub );

});

