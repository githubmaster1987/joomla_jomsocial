(function( root, factory ) {

    joms.popup || (joms.popup = {});
    joms.popup.pm = factory( root, joms.popup.pm || {});

    define([ 'popups/pm.send' ], function() {
        return joms.popup.pm;
    });

})( window, function( window, sub ) {

// Exports.
return joms._.extend({}, sub );

});
