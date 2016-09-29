(function( root, factory ) {

    joms.popup || (joms.popup = {});
    joms.popup.album || (joms.popup.album = {});
    joms.popup.album.remove = factory( root );

    define([ 'utils/popup' ], function() {
        return joms.popup.album.remove;
    });

})( window, function() {

var popup, elem, id;

function render( _popup, _id ) {
    if ( elem ) elem.off();
    popup = _popup;
    id = _id;

    joms.ajax({
        func: 'photos,ajaxRemoveAlbum',
        data: [ id, 'myphotos' ],
        callback: function( json ) {
            popup.items[0] = {
                type: 'inline',
                src: buildHtml( json )
            };

            popup.updateItemHTML();

            elem = popup.contentContainer;
            elem.on( 'click', '[data-ui-object=popup-button-cancel]', cancel );
            elem.on( 'click', '[data-ui-object=popup-button-save]', save );
        }
    });
}

function cancel() {
    elem.off();
    popup.close();
}

function save() {
    elem.find('form').submit();
}

function buildHtml( json ) {
    json || (json = {});

    return [
        '<div class="joms-popup joms-popup--whiteblock">',
        '<div class="joms-popup__title"><button class="mfp-close" type="button" title="Close (Esc)">×</button>', json.title, '</div>',
        '<div data-ui-object="popup-step-1"', ( json.error ? ' class="joms-popup__hide"' : '' ), '>',
            '<div class="joms-popup__content">', json.message, '</div>',
            '<div class="joms-popup__action">',
            '<a href="javascript:" class="joms-button--neutral joms-button--small joms-left" data-ui-object="popup-button-cancel">', json.btnCancel, '</a> &nbsp;',
            '<button class="joms-button--primary joms-button--small" data-ui-object="popup-button-save">', json.btnYes, '</button>',
            '</div>',
        '</div>',
        '<div data-ui-object="popup-step-2"', ( json.error ? '' : ' class="joms-popup__hide"' ), '>',
            '<div class="joms-popup__content joms-popup__content--single" data-ui-object="popup-message">', (json.error || ''), '</div>',
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
