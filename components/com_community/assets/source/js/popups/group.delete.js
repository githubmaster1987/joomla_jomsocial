(function( root, factory ) {

    joms.popup || (joms.popup = {});
    joms.popup.group || (joms.popup.group = {});
    joms.popup.group['delete'] = factory( root );

    define([ 'utils/popup' ], function() {
        return joms.popup.group['delete'];
    });

})( window, function() {

var popup, elem, id;

function render( _popup, _id ) {
    if ( elem ) elem.off();
    popup = _popup;
    id = _id;

    joms.ajax({
        func: 'groups,ajaxWarnGroupDeletion',
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

function save( e, step ) {
    joms.ajax({
        func: 'groups,ajaxDeleteGroup',
        data: [ id, step || 1 ],
        callback: function( json ) {
            var $ct;

            elem.find('.joms-js--step1').hide();
            elem.find('.joms-js--step2').show().children().first()
                .append( '<div>' + (json.error || json.message) + '</div>' );

            if ( json.next ) {
                save( null, json.next );
            } else if ( json.redirect ) {
                $ct = elem.find('.joms-js--step2');
                $ct.find('.joms-js--button-done')
                    .html( json.btnDone )
                    .on( 'click', function() {
                        window.location = json.redirect;
                    });

                $ct.find('.joms-popup__action').show();
                $ct.find('.joms-popup__content').removeClass('joms-popup__content--single');
            }
        }
    });
}

function buildHtml( json ) {
    json || (json = {});

    return [
        '<div class="joms-popup joms-popup--whiteblock">',
        '<div class="joms-popup__title"><button class="mfp-close" type="button" title="Close (Esc)">×</button>', json.title, '</div>',
        '<div class="joms-js--step1', ( json.error ? ' joms-popup__hide' : '' ), '">',
            '<div class="joms-popup__content">', json.html, '</div>',
            '<div class="joms-popup__action">',
            '<button class="joms-button--neutral joms-button--small joms-left joms-js--button-cancel">', json.btnCancel, '</button> &nbsp;',
            '<button class="joms-button--primary joms-button--small joms-js--button-save">', json.btnDelete, '</button>',
            '</div>',
        '</div>',
        '<div class="joms-js--step2', ( json.error ? '' : ' joms-popup__hide' ), '">',
            '<div class="joms-popup__content joms-popup__content--single">', ( json.error || '' ), '</div>',
            '<div class="joms-popup__action joms-popup__hide">',
            '<button class="joms-button--primary joms-js--button-done"></button>',
            '</div>',
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
