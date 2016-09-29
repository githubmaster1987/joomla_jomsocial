(function( root, $, factory ) {

    joms.popup || (joms.popup = {});
    joms.popup.avatar || (joms.popup.avatar = {});
    joms.popup.avatar.rotate = factory( root, $ );

    define(function() {
        return joms.popup.avatar.rotate;
    });

})( window, joms.jQuery, function( window, $ ) {

function render( type, id, direction, callback ) {
    joms.ajax({
        func: 'profile,ajaxRotateAvatar',
        data: [ type, id, direction ],
        callback: function( json ) {
            if ( json.error ) {
                window.alert( json.error );
                return;
            }

            if ( json.success ) {
                $( '.joms-js--avatar-' + id )
                    .attr( 'src', json.avatar + '?_=' + (new Date()).getTime() );

                if ( typeof callback === 'function' ) {
                    callback( json );
                }
            }
        }
    });
}

// Exports.
return function( type, id, direction, callback ) {
    render( type, id, direction, callback );
};

});
