(function( root, factory ) {

    joms.popup || (joms.popup = {});
    joms.popup.friend || (joms.popup.friend = {});
    joms.popup.friend.add = factory( root );

    define([ 'utils/popup' ], function() {
        return joms.popup.friend.add;
    });

})( window, function() {

var popup, elem, id;

function render( _popup, _id ) {
    if ( elem ) elem.off();
    popup = _popup;
    id = _id;

    joms.ajax({
        func: 'friends,ajaxConnect',
        data: [ id ],
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
    var message = elem.find('textarea').val()
        .replace( /\t/g, '\\t' )
        .replace( /\n/g, '\\n' )
        .replace( /&quot;/g,  '"' );

    joms.ajax({
        func: 'friends,ajaxSaveFriend',
        data: [[[ 'msg', message ], [ 'userid', id ]]],
        callback: function( json ) {
            var step1 = elem.find('[data-ui-object=popup-step-1]'),
                step2 = elem.find('[data-ui-object=popup-step-2]');

            if ( !json.error ) {
                popup.st.callbacks || (popup.st.callbacks = {});
                popup.st.callbacks.close = function() {
                    window.location.reload();
                };
            }

            step2.find('[data-ui-object=popup-message]').html( json.error || json.message );
            step1.hide();
            step2.show();
        }
    });
}


function buildHtml( json ) {
    json || (json = {});

    return [
        '<div class="joms-popup joms-popup--whiteblock">',
        '<div class="joms-popup__title"><button class="mfp-close" type="button" title="Close (Esc)">×</button>', json.title, '</div>',
        '<div data-ui-object="popup-step-1"', ( json.error ? ' class="joms-popup__hide"' : '' ), '>',
            '<div class="joms-popup__content">',
                '<div class="joms-stream__header" style="padding:0">',
                    '<div class="joms-avatar--stream"><img src="', json.avatar, '"></div>',
                    '<div class="joms-stream__meta"><span>', json.desc, '</span></div>',
                '</div>',
            '</div>',
            '<div class="joms-popup__content">',
                '<textarea class="joms-textarea" style="margin:0">', json.message, '</textarea>',
            '</div>',
            '<div class="joms-popup__action">',
                '<a href="javascript:" class="joms-button--neutral joms-button--small joms-left" data-ui-object="popup-button-cancel">', json.btnCancel, '</a> &nbsp;',
                '<button class="joms-button--primary joms-button--small" data-ui-object="popup-button-save">', json.btnAdd, '</button>',
            '</div>',
        '</div>',
        '<div data-ui-object="popup-step-2"', ( json.error ? '' : ' class="joms-popup__hide"' ), '>',
            '<div class="joms-popup__content joms-popup__content--single" data-ui-object="popup-message">', (json.error || ''), '</div>',
            '<div class="joms-popup__action">',
            '<button class="joms-button--neutral joms-button--small joms-js--button-close">', window.joms_lang.COM_COMMUNITY_CLOSE_BUTTON, '</button>',
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
