(function( root, factory ) {

    joms.popup || (joms.popup = {});
    joms.popup.comment = factory( root, joms.popup.comment || {});

    define([
        'popups/comment.showlikes'
    ], function() {
        return joms.popup.comment;
    });

})( window, function( window, sub ) {

// Exports.
return joms._.extend({}, sub );

});
