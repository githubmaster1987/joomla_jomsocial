(function( root, factory ) {

    joms.popup || (joms.popup = {});
    joms.popup.discussion = factory( root, joms.popup.discussion || {});

    define([
        'popups/discussion.lock',
        'popups/discussion.remove'
    ], function() {
        return joms.popup.discussion;
    });

})( window, function( window, sub ) {

// Exports.
return joms._.extend({}, sub );

});
