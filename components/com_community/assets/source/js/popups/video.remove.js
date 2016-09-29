(function( root, factory ) {

    joms.popup || (joms.popup = {});
    joms.popup.video || (joms.popup.video = {});
    joms.popup.video.remove = factory( root );

    define([ 'utils/popup' ], function() {
        return joms.popup.video.remove;
    });

})( window, function( window ) {

var popup, elem, id;

function render( _popup, _id, redirect ) {
    if ( elem ) elem.off();
    popup = _popup;
    id = _id;

    joms.ajax({
        func: 'videos,ajaxConfirmRemoveVideo',
        data: [ id ],
        callback: function( json ) {
            popup.items[0] = {
                type: 'inline',
                src: buildHtml( json )
            };

            popup.updateItemHTML();

            elem = popup.contentContainer;
            elem.on( 'click', '.joms-js--button-cancel', cancel );
            elem.on( 'click', '.joms-js--button-save', { redirect: redirect ? 1 : 0 }, save );
        }
    });
}

function cancel() {
    elem.off();
    popup.close();
}

function save( e ) {
    joms.ajax({
        func: 'videos,ajaxRemoveVideo',
        data: [ id, e.data.redirect ],
        callback: function( json ) {
            elem.find('.joms-js--step1').hide();
            elem.find('.joms-js--step2').show().children().html( json.error || json.message );

            if ( json.success ) {
                setTimeout(function() {
                    if ( json.redirect ) {
                        window.location = json.redirect;
                    } else {
                        window.location.reload();
                    }
                }, 1000 );
            }
        }
    });
}

function buildHtml( json ) {
    json || (json = {});

    return [
        '<div class="joms-popup joms-popup--whiteblock">',
        '<div class="joms-popup__title"><button class="mfp-close" type="button" title="Close (Esc)">×</button>', json.title, '</div>',
        '<div class="joms-js--step1">',
            '<div class="joms-popup__content">', json.html, '</div>',
            '<div class="joms-popup__action">',
            '<button class="joms-button--neutral joms-button--small joms-left joms-js--button-cancel">', json.btnNo, '</button> &nbsp;',
            '<button class="joms-button--primary joms-button--small joms-js--button-save">', json.btnYes, '</button>',
            '</div>',
        '</div>',
        '<div class="joms-popup__hide joms-js--step2">',
            '<div class="joms-popup__content joms-popup__content--single"></div>',
        '</div>',
        '</div>'
    ].join('');
}

// Exports.
return function( id, redirect ) {
    joms.util.popup.prepare(function( mfp ) {
        render( mfp, id, redirect );
    });
};

});
