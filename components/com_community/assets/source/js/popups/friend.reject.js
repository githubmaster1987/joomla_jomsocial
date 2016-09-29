(function( root, $, factory ) {

    joms.popup || (joms.popup = {});
    joms.popup.friend || (joms.popup.friend = {});
    joms.popup.friend.reject = factory( root, $ );

    define([ 'utils/popup', 'functions/notification' ], function() {
        return joms.popup.friend.reject;
    });

})( window, joms.jQuery, function( window, $ ) {

var id;

function render( _id ) {
    id = _id;

    joms.ajax({
        func: 'friends,ajaxRejectRequest',
        data: [ id ],
        callback: function( json ) {
            if ( json.success ) {
                update( json );
                return;
            }

            // On error response.
            joms.util.popup.prepare(function( mfp ) {
                mfp.items[0] = {
                    type: 'inline',
                    src: buildErrorHtml( json )
                };

                mfp.updateItemHTML();
            });
        }
    });
}

function update( json ) {
    $( '.joms-js--frequest-msg-' + id ).html( json.message );
    $( '.joms-js--frequest-btn-' + id ).remove();
    joms.fn.notification.updateCounter( 'frequest', id, -1 );
}

function buildErrorHtml( json ) {
    json || (json = {});

    return [
        '<div class="joms-popup joms-popup--whiteblock">',
        '<div class="joms-popup__title"><button class="mfp-close" type="button" title="Close (Esc)">×</button>', json.title, '</div>',
        '<div class="joms-popup__content joms-popup__content--single">', ( json.error || json.message ), '</div>',
        '<div class="joms-popup__action">',
        '<button class="joms-button--neutral joms-button--small joms-js--button-close">', window.joms_lang.COM_COMMUNITY_CLOSE_BUTTON, '</button>',
        '</div>',
        '</div>'
    ].join('');
}

// Exports.
return function( id ) {
    render( id );
};

});
