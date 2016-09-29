(function( root, $, factory ) {

    joms.popup || (joms.popup = {});
    joms.popup.group || (joms.popup.group = {});
    joms.popup.group.approve = factory( root, $ );

    define([ 'functions/notification' ], function() {
        return joms.popup.group.approve;
    });

})( window, joms.jQuery, function( window, $ ) {

var id, userid;

function render( _id, _userid ) {
    id = _id;
    userid = _userid;

    joms.ajax({
        func: 'groups,ajaxApproveMember',
        data: [ userid, id ],
        callback: function( json ) {
            if ( json ) {
                $( '.joms-js--request-buttons-group-' + id + '-' + userid ).remove();
                $( '.joms-js--request-notice-group-' + id + '-' + userid ).html( json.message || json.error );
                json.success && joms.fn.notification.updateCounter( 'general', id, -1 );
            }
        }
    });
}

// Exports.
return function( id, userId ) {
    render( id, userId );
};

});
