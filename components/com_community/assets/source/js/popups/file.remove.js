(function( root, $, factory ) {

    joms.popup || (joms.popup = {});
    joms.popup.file || (joms.popup.file = {});
    joms.popup.file.remove = factory( root, $ );

    define([ 'utils/popup' ], function() {
        return joms.popup.file.remove;
    });

})( window, joms.jQuery, function( window, $ ) {

var type, id;

function render( popup, json ) {
    popup.items[0] = {
        type: 'inline',
        src: buildHtml( json )
    };

    popup.updateItemHTML();
}

function _delete( _type, _id ) {
    type = _type;
    id = _id;

    joms.ajax({
        func: 'files,ajaxDeleteFile',
        data: [ type, id ],
        callback: function( json ) {
            if ( json.success ) {
                $( '.joms-js--file-' + id ).remove();
                return;
            }

            joms.util.popup.prepare(function( mfp ) {
                render( mfp, json );
            });
        }
    });
}

function buildHtml( json ) {
    json || (json = {});

    return [
        '<div class="joms-popup joms-popup--whiteblock">',
        '<div class="joms-popup__title"><button class="mfp-close" type="button" title="Close (Esc)">×</button> &nbsp; </div>',
        '<div class="joms-popup__content joms-popup__content--single">', (json.message || json.error), '</div>',
        '</div>'
    ].join('');
}

// Exports.
return function( type, id ) {
    _delete( type, id );
};

});
