(function( root, $, factory ) {

    joms.popup || (joms.popup = {});
    joms.popup.file || (joms.popup.file = {});
    joms.popup.file.download = factory( root, $ );

    define([ 'utils/popup' ], function() {
        return joms.popup.file.download;
    });

})( window, joms.jQuery, function( window ) {

var popup, elem, type, id, path;

function render( _popup, _type, _id, _path ) {
    if ( elem ) elem.off();
    popup = _popup;
    type = _type;
    id = _id;
    path = _path;

    joms.ajax({
        func: 'files,ajaxFileDownload',
        data: [ type, id ],
        callback: function( json ) {
            popup.items[0] = {
                type: 'inline',
                src: buildHtml( json )
            };

            popup.updateItemHTML();

            if ( json.url ) {
                popup.close();
                window.open( path );
            }
        }
    });
}

function buildHtml( json ) {
    json || (json = {});

    return [
        '<div class="joms-popup joms-popup--whiteblock">',
        '<div class="joms-popup__title"><button class="mfp-close" type="button" title="Close (Esc)">×</button>', json.title, '</div>',
        '<div class="joms-popup__content joms-popup__content--single">', (json.message || json.error), '</div>',
        '<div class="joms-popup__action">',
        '<button class="joms-button--neutral joms-button--small joms-js--button-close">', window.joms_lang.COM_COMMUNITY_CLOSE_BUTTON, '</button>',
        '</div>',
        '</div>'
    ].join('');
}

// Exports.
return function( type, id, path ) {
    joms.util.popup.prepare(function( mfp ) {
        render( mfp, type, id, path );
    });
};

});
