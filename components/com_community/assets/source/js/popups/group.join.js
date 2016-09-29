(function( root, $, factory ) {

    joms.popup || (joms.popup = {});
    joms.popup.group || (joms.popup.group = {});
    joms.popup.group.join = factory( root, $ );

    define([ 'utils/popup' ], function() {
        return joms.popup.group.join;
    });

})( window, joms.jQuery, function( window ) {

var popup, elem, id;

function render( _popup, _id ) {
    if ( elem ) elem.off();
    popup = _popup;
    id = _id;

    joms.ajax({
        func: 'groups,ajaxJoinGroup',
        data: [ id ],
        callback: function() {
            window.location.reload();
        }
    });
}

// Exports.
return function( id ) {
    joms.util.popup.prepare(function( mfp ) {
        render( mfp, id );
    });
};

});
