(function( root, $, factory ) {

    joms.fn || (joms.fn = {});
    joms.fn.invitation = factory( root, $ );

    define([ 'functions/notification' ], function() {
        return joms.fn.invitation;
    });

})( window, joms.jQuery, function( window, $ ) {

function accept( type, id ) {
    var func = type === 'group' ? 'notification,ajaxGroupJoinInvitation' : 'events,ajaxJoinInvitation',
        data = [ id ];

    joms.ajax({
        func: func,
        data: data,
        callback: function( json ) {
            _update( type, id, json );
        }
    });
}

function reject( type, id ) {
    var func = type === 'group' ? 'notification,ajaxGroupRejectInvitation' : 'events,ajaxRejectInvitation',
        data = [ id ];

    joms.ajax({
        func: func,
        data: data,
        callback: function( json ) {
            _update( type, id, json );
        }
    });
}

function _update( type, id, json ) {
    $( '.joms-js--invitation-buttons-' + type + '-' + id ).remove();
    $( '.joms-js--invitation-notice-' + type + '-' + id ).html( json && json.message || '' );
    joms.fn.notification.updateCounter( 'general', id, -1 );
}

// Exports.
return {
    accept: accept,
    reject: reject
};

});
