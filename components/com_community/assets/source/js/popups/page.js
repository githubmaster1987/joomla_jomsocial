(function( root, factory ) {

    joms.popup || (joms.popup = {});
    joms.popup.page = factory( root, joms.popup.page || {});

    define([ 'popups/page.share' ], function() {
        return joms.popup.page;
    });

})( window, function( window, sub ) {

// Exports.
return joms._.extend({}, sub );

});
