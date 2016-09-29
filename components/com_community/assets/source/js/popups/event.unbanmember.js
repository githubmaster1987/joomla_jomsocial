(function( root, $, factory ) {

    joms.popup || (joms.popup = {});
    joms.popup.event || (joms.popup.event = {});
    joms.popup.event.unbanMember = factory( root, $ );

    define([ 'utils/popup' ], function() {
        return joms.popup.event.unbanMember;
    });

})( window, joms.jQuery, function( window ) {

var popup, elem, id, userid;

function render( _popup, _id, _userid ) {
    if ( elem ) elem.off();
    popup = _popup;
    id = _id;
    userid = _userid;

    joms.ajax({
        func: 'events,ajaxUnbanMember',
        data: [ userid, id ],
        callback: function( json ) {
            popup.items[0] = {
                type: 'inline',
                src: buildHtml( json )
            };

            popup.updateItemHTML();

            if ( json.success ) {
                popup.st.callbacks || (popup.st.callbacks = {});
                popup.st.callbacks.close = function() {
                    window.location.reload();
                };
            }
        }
    });
}

function buildHtml( json ) {
    json || (json = {});

    return [
        '<div class="joms-popup joms-popup--whiteblock">',
        '<div class="joms-popup__title"><button class="mfp-close" type="button" title="Close (Esc)">×</button>', ( json.title || '' ), '</div>',
        '<div class="joms-popup__content joms-popup__content--single">', ( json.html || json.message || json.error || '' ), '</div>',
        '<div class="joms-popup__action">',
        '<button class="joms-button--neutral joms-button--small joms-js--button-close">', window.joms_lang.COM_COMMUNITY_CLOSE_BUTTON, '</button>',
        '</div>',
        '</div>'
    ].join('');
}

// Exports.
return function( id, userid ) {
    joms.util.popup.prepare(function( mfp ) {
        render( mfp, id, userid );
    });
};

});
