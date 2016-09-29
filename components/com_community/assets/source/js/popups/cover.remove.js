(function( root, $, factory ) {

    joms.popup || (joms.popup = {});
    joms.popup.cover || (joms.popup.cover = {});
    joms.popup.cover.remove = factory( root, $ );

    define([ 'utils/popup' ], function() {
        return joms.popup.cover.remove;
    });

})( window, joms.jQuery, function() {

var popup, elem, type, id;

function render( _popup, _type, _id ) {
    var func, data;

    if ( elem ) elem.off();
    popup = _popup;
    type = _type;
    id = _id;

    func = 'profile,ajaxRemoveCover';
    data = [ id ];

    if ( type === 'group' || type ==='event' ) {
        func = 'photos,ajaxRemoveCover';
        data = [ type, id ];
    }

    joms.ajax({
        func: func,
        data: data,
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
    var $form = elem.find('form');
    if ( ! $form.data('saving') ) {
        $form.data( 'saving', 1 );
        $form.submit();
    }
}

function buildHtml( json ) {
    json || (json = {});

    return [
        '<div class="joms-popup joms-popup--whiteblock">',
        '<div class="joms-popup__title"><button class="mfp-close" type="button" title="Close (Esc)">×</button>', json.title, '</div>',
        '<div class="joms-js--step1', ( json.error ? ' joms-popup__hide' : '' ), '">',
            '<div class="joms-popup__content joms-popup__content--single">', ( json.html || '' ), '</div>',
            '<div class="joms-popup__action">',
            '<button class="joms-button--neutral joms-button--small joms-left joms-js--button-cancel">', json.btnNo, '</button> &nbsp;',
            '<button class="joms-button--primary joms-button--small joms-js--button-save">', json.btnYes, '</button>',
            '<div class="joms-popup__hide">',
                '<form method="POST" action="', json.redirUrl, '">',
                    ( type === 'group' || type ==='event' ? '<input type="hidden" name="type" value="' + type + '">' : '' ),
                    ( type === 'group' || type ==='event' ? '<input type="hidden" name="id" value="' + id + '">' : '' ),
                    ( type === 'group' || type ==='event' ? '' : '<input type="hidden" name="userid" value="' + id + '">' ),
                '</form>',
            '</div>',
            '</div>',
        '</div>',
        '<div class="joms-js--step2', ( json.error ? '' : ' joms-popup__hide' ), '">',
            '<div class="joms-popup__content joms-popup__content--single">', (json.error || ''), '</div>',
        '</div>',
        '</div>'
    ].join('');
}

// Exports.
return function( type, id ) {
    joms.util.popup.prepare(function( mfp ) {
        render( mfp, type, id );
    });
};

});
