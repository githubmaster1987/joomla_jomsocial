(function( root, factory ) {

    joms.popup || (joms.popup = {});
    joms.popup.app = factory( root, joms.popup.app || {});

    define([
        'popups/app.about',
        'popups/app.browse',
        'popups/app.privacy',
        'popups/app.remove',
        'popups/app.setting'
    ], function() {
        return joms.popup.app;
    });

})( window, function( window, sub ) {

// Exports.
return joms._.extend({}, sub );

});

