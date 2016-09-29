(function( root, factory ) {

    joms.popup || (joms.popup = {});
    joms.popup.file || (joms.popup.file = {});
    joms.popup.file.updateHit = factory( root );

    define([ 'utils/popup' ], function() {
        return joms.popup.file.updateHit;
    });

})( window, function( window ) {

return function( id, location ) {
    joms.ajax({
        func: 'files,ajaxUpdateHit',
        data: [ id ],
        callback: function() {}
    });

    if ( typeof location === 'string' ) {
        window.open( location );
    }
};

});
