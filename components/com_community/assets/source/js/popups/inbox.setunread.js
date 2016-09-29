(function( root, $, factory ) {

    joms.popup || (joms.popup = {});
    joms.popup.inbox || (joms.popup.inbox = {});
    joms.popup.inbox.setUnread = factory( root, $ );

    define([ 'utils/popup' ], function() {
        return joms.popup.inbox.setUnread;
    });

})( window, joms.jQuery, function( window ) {

function render( msgids, error ) {
    var i;

    if ( !msgids.length ) {
        joms.util.popup.prepare(function( mfp ) {
            mfp.items[0] = { type: 'inline', src: buildHtml({ error: error }) };
            mfp.updateItemHTML();
        });
        return;
    }

    for ( i = 0; i < msgids.length; i++ ) {
        window.jax.call( 'community', 'inbox,ajaxMarkMessageAsUnread', msgids[ i ] );
    }
}

function buildHtml( json ) {
    json || (json = {});

    return [
        '<div class="joms-popup joms-popup--whiteblock">',
        '<div class="joms-popup__title"><button class="mfp-close" type="button" title="Close (Esc)">×</button>', ( json.title || '' ), '</div>',
        '<div class="joms-js--step1 ', ( json.error ? 'joms-popup__hide' : '' ), '">',
            '<div class="joms-popup__content">', ( json.html || '' ), '</div>',
            '<div class="joms-popup__action">',
            '<button class="joms-button--neutral joms-button--small joms-left joms-js--button-cancel">', json.btnNo, '</button> &nbsp;',
            '<button class="joms-button--primary joms-button--small joms-js--button-save">', json.btnYes, '</button>',
            '</div>',
        '</div>',
        '<div class="joms-js--step2 ', ( json.error ? '' : 'joms-popup__hide' ), '">',
            '<div class="joms-popup__content joms-popup__content--single">', ( json.error || '' ), '</div>',
        '</div>',
        '</div>'
    ].join('');
}

// Exports.
return function( msgids, error ) {
    render( msgids, error );
};

});
