(function( root, factory ) {

    joms.popup || (joms.popup = {});
    joms.popup.pm || (joms.popup.pm = {});
    joms.popup.pm.send = factory( root );

    define([ 'utils/popup' ], function() {
        return joms.popup.pm.send;
    });

})( window, function() {

var popup, elem, id;

function render( _popup, _id ) {
    if ( elem ) elem.off();
    popup = _popup;
    id = _id;

    joms.ajax({
        func: 'inbox,ajaxCompose',
        data: [ id ],
        callback: function( json ) {
            popup.items[0] = {
                type: 'inline',
                src: buildHtml( json )
            };

            popup.updateItemHTML();

            elem = popup.contentContainer;

            elem.find('textarea.joms-textarea').jomsTagging();

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
    var subject = elem.find('input[name=subject]').val(),
        body = elem.find('[name=body]').val(),
        att = elem.find('.joms-textarea__attachment'),
        data, photo, file;

    body = body
        .replace( /\t/g, '\\t' )
        .replace( /\n/g, '\\n' )
        .replace( /&quot;/g,  '"' );

    if ( elem.data('saving') ) {
        return;
    }

    elem.data( 'saving', 1 );

    data = [[ 'subject', subject ], [ 'body', body ], [ 'to', id ]];

    if ( att.is(':visible') ) {
        photo = att.find('.joms-textarea__attachment--thumbnail img');
        file = photo.siblings('b');
        if ( photo.is(':visible') ) {
            photo = photo.data('photo_id');
            file = '';
        } else if ( file.is(':visible') ) {
            file = file.data('id');
            photo = '';
        }
        data.push([ 'photo', photo ]);
        data.push([ 'file_id', file ]);
    }

    joms.ajax({
        func: 'inbox,ajaxSend',
        data: [ data ],
        callback: function( json ) {
            var step1 = elem.find('[data-ui-object=popup-step-1]'),
                step2 = elem.find('[data-ui-object=popup-step-2]');

            elem.removeData('saving');

            if ( json.samestep ) {
                step1.find('[data-ui-object=popup-message]').html( json.error ).show();
            } else {
                step2.find('[data-ui-object=popup-message]').html( json.error || json.message );
                step1.hide();
                step2.show();
            }
        }
    });
}


function buildHtml( json ) {
    json || (json = {});

    return [
        '<div class="joms-popup joms-popup--whiteblock joms-popup--500">',
        '<div class="joms-popup__title"><button class="mfp-close" type="button" title="Close (Esc)">×</button>', json.title, '</div>',
        '<div data-ui-object="popup-step-1"', ( json.error ? ' class="joms-popup__hide"' : '' ), '>',
            (json.html || ''),
            '<div class="joms-popup__action">',
            '<a href="javascript:" class="joms-button--neutral joms-button--small joms-left" data-ui-object="popup-button-cancel">', json.btnCancel, '</a> &nbsp;',
            '<button class="joms-button--primary joms-button--small" data-ui-object="popup-button-save">', json.btnSend, '</button>',
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
