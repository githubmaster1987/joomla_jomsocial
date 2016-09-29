(function( root, factory ) {

    joms.popup || (joms.popup = {});
    joms.popup.search = factory( root, joms.popup.search || {});

    define([ 'popups/search.save' ], function() {
        return joms.popup.search;
    });

})( window, function( window, sub ) {

// Exports.
return joms._.extend({}, sub );

});
