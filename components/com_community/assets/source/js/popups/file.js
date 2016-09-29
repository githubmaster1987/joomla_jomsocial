(function( root, factory ) {

    joms.popup || (joms.popup = {});
    joms.popup.file = factory( root, joms.popup.file || {});

    define([
        'popups/file.download',
        'popups/file.list',
        'popups/file.remove',
        'popups/file.upload',
        'popups/file.updatehit'
    ], function() {
        return joms.popup.file;
    });

})( window, function( window, sub ) {

// Exports.
return joms._.extend({}, sub );

});
