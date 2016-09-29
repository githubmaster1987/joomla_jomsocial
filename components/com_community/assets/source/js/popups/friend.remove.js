(function( root, $, factory ) {

    joms.popup || (joms.popup = {});
    joms.popup.friend || (joms.popup.friend = {});
    joms.popup.friend.remove = factory( root, $ );

    define([ 'utils/popup' ], function() {
        return joms.popup.friend.remove;
    });

})( window, joms.jQuery, function( window, $ ) {

var popup, elem, id;

function render( _popup, _id ) {
    if ( elem ) elem.off();
    popup = _popup;
    id = _id;

    joms.ajax({
        func: 'friends,ajaxConfirmFriendRemoval',
        data: [ id ],
        callback: function( json ) {
            popup.items[0] = {
                type: 'inline',
                src: buildHtml( json )
            };

            popup.st.callbacks || (popup.st.callbacks = {});
            popup.st.callbacks.close = function() {
                window.location.reload();
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
    var checkbox = elem.find('input[type=checkbox]'),
        func;

    if ( checkbox[0].checked ) {
        func = 'friends,ajaxBlockFriend';
    } else {
        func = 'friends,ajaxRemoveFriend';
    }

    joms.ajax({
        func: func,
        data: [ id ],
        callback: function( json ) {
            var step1 = elem.find('[data-ui-object=popup-step-1]'),
                step2 = elem.find('[data-ui-object=popup-step-2]');

            step2.find('[data-ui-object=popup-message]').html( json.error || json.message );
            step1.hide();
            step2.show();

            if ( json && json.success ) {
                $( '#friend-' + id ).remove();
            }
        }
    });
}


function buildHtml( json ) {
    json || (json = {});

    return [
        '<div class="joms-popup joms-popup--whiteblock">',
        '<div class="joms-popup__title"><button class="mfp-close" type="button" title="Close (Esc)">×</button>', json.title, '</div>',
        '<div data-ui-object="popup-step-1"', ( json.error ? ' class="joms-popup__hide"' : '' ), '>',
            '<div class="joms-popup__content">', ( json.html || '' ), '</div>',
            '<div class="joms-popup__action">',
            '<a href="javascript:" class="joms-button--neutral joms-button--small joms-left" data-ui-object="popup-button-cancel">', json.btnNo, '</a> &nbsp;',
            '<button class="joms-button--primary joms-button--small" data-ui-object="popup-button-save">', json.btnYes, '</button>',
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
