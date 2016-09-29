(function( root, $, factory ) {

    joms.popup || (joms.popup = {});
    joms.popup.info = factory( root, $ );

    define([ 'utils/popup' ], function() {
        return joms.popup.info;
    });

})( window, joms.jQuery, function() {

function render( popup, title, content ) {
    popup.items[0] = {
        type: 'inline',
        src: buildHtml( title, content )
    };

    popup.updateItemHTML();
}

function buildHtml( title, content ) {
    return [
        '<div class="joms-popup joms-popup--whiteblock">',
        '<div class="joms-popup__title"><button class="mfp-close" type="button" title="Close (Esc)">×</button>', ( title || '&nbsp;' ), '</div>',
        '<div class="joms-popup__content joms-popup__content--single">', ( content || '' ), '</div>',
        '<div class="joms-popup__action">',
        '<button class="joms-button--neutral joms-button--small joms-js--button-close">', window.joms_lang.COM_COMMUNITY_CLOSE_BUTTON, '</button>',
        '</div>',
        '</div>'
    ].join('');
}

// Exports.
return function( title, content ) {
    joms.util.popup.prepare(function( mfp ) {
        render( mfp, title, content );
    });
};

});
