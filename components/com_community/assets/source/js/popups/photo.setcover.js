(function( root, $, factory ) {

    joms.popup || (joms.popup = {});
    joms.popup.photo || (joms.popup.photo = {});
    joms.popup.photo.setCover = factory( root, $ );

    define([ 'utils/popup' ], function() {
        return joms.popup.photo.setCover;
    });

})( window, joms.jQuery, function() {

var popup, elem, album, id;

function render( _popup, _album, _id ) {
    if ( elem ) elem.off();
    popup = _popup;
    album = _album;
    id = _id;

    joms.ajax({
        func: 'photos,ajaxConfirmDefaultPhoto',
        data: [ album, id ],
        callback: function( json ) {
            popup.items[0] = {
                type: 'inline',
                src: buildHtml( json )
            };

            popup.updateItemHTML();

            elem = popup.contentContainer;
            elem.on( 'click', '.joms-js--button-cancel', cancel );
            elem.on( 'click', '.joms-js--button-save', save );
        }
    });
}

function cancel() {
    elem.off();
    popup.close();
}

function save() {
    joms.ajax({
        func: 'photos,ajaxSetDefaultPhoto',
        data: [ album, id ],
        callback: function( json ) {
            elem.find('.joms-js--step1').hide();
            elem.find('.joms-js--step2').show().children().html( json.error || json.message );
        }
    });
}

function buildHtml( json ) {
    json || (json = {});

    return [
        '<div class="joms-popup joms-popup--whiteblock">',
        '<div class="joms-popup__title"><button class="mfp-close" type="button" title="Close (Esc)">×</button>', json.title, '</div>',
        '<div class="joms-js--step1', ( json.error ? ' joms-popup__hide' : '' ), '">',
            '<div class="joms-popup__content">', json.message, '</div>',
            '<div class="joms-popup__action">',
            '<button class="joms-button--neutral joms-button--small joms-left joms-js--button-cancel">', json.btnNo, '</button> &nbsp;',
            '<button class="joms-button--primary joms-button--small joms-js--button-save">', json.btnYes, '</button>',
            '</div>',
        '</div>',
        '<div class="joms-js--step2', ( json.error ? '' : ' joms-popup__hide' ), '">',
            '<div class="joms-popup__content joms-popup__content--single">', ( json.error || '' ), '</div>',
        '</div>',
        '</div>'
    ].join('');
}

// Exports.
return function( album, id ) {
    joms.util.popup.prepare(function( mfp ) {
        render( mfp, album, id );
    });
};

});
