(function( root, $, factory ) {

    joms.popup || (joms.popup = {});
    joms.popup.event || (joms.popup.event = {});
    joms.popup.event.join = factory( root, $ );

    define([ 'utils/popup' ], function() {
        return joms.popup.event.join;
    });

})( window, joms.jQuery, function() {

var popup, elem, id;

function render( _popup, _id ) {
    if ( elem ) elem.off();
    popup = _popup;
    id = _id;

    joms.ajax({
        func: 'events,ajaxRequestInvite',
        data: [ id ],
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
    elem.find('form')[0].submit();
}

function buildHtml( json ) {
    json || (json = {});

    return [
        '<div class="joms-popup joms-popup--whiteblock">',
        '<div class="joms-popup__title"><button class="mfp-close" type="button" title="Close (Esc)">×</button>', json.title, '</div>',
        '<div class="joms-js--step1 ', ( json.isMember ? 'joms-popup__hide' : '' ), '">',
            '<div class="joms-popup__content">', json.html, '</div>',
            '<div class="joms-popup__action">',
            '<button class="joms-button--neutral joms-button--small joms-left joms-js--button-cancel">', json.btnNo, '</button> &nbsp;',
            '<button class="joms-button--primary joms-button--small joms-js--button-save">', json.btnYes, '</button>',
            '</div>',
        '</div>',
        '<div class="joms-js--step2 ', ( json.isMember ? '' : 'joms-popup__hide' ), '">',
            '<div class="joms-popup__content joms-popup__content--single">', json.html, '</div>',
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
