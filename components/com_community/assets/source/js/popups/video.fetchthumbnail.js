(function( root, $, factory ) {

    joms.popup || (joms.popup = {});
    joms.popup.video || (joms.popup.video = {});
    joms.popup.video.fetchThumbnail = factory( root, $ );

    define([ 'utils/popup' ], function() {
        return joms.popup.video.fetchThumbnail;
    });

})( window, joms.jQuery, function( window ) {

var popup, elem, id;

function render( _popup, _id ) {
    if ( elem ) elem.off();
    popup = _popup;
    id = _id;

    joms.ajax({
        func: 'videos,ajaxFetchThumbnail',
        data: [ id, 'myvideos' ],
        callback: function( json ) {
            popup.items[0] = {
                type: 'inline',
                src: buildHtml( json )
            };

            if ( json.success ) {
                popup.st.callbacks || (popup.st.callbacks = {});
                popup.st.callbacks.close = function() {
                    window.location.reload();
                };
            }

            popup.updateItemHTML();
        }
    });
}

function buildHtml( json ) {
    json || (json = {});

    return [
        '<div class="joms-popup joms-popup--whiteblock">',
        '<div class="joms-popup__title"><button class="mfp-close" type="button" title="Close (Esc)">×</button>', json.title, '</div>',
        '<div class="joms-popup__content joms-popup__content--single">',
        ( json.message || json.error || '' ),
        ( json.thumbnail ? '<div style="padding-top:10px;"><img src="' + json.thumbnail + '" style="max-width:100%;"></div>' : '' ),
        '</div>',
        '</div>'
    ].join('');
}

// Exports.
return function( id ) {
    joms.util.popup.prepare(function( mfp ) {
        render( mfp, id );
    });
};

});
