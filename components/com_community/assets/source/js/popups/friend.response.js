(function( root, factory ) {

    joms.popup || (joms.popup = {});
    joms.popup.friend || (joms.popup.friend = {});
    joms.popup.friend.response = factory( root );

    define([ 'utils/popup', 'functions/notification' ], function() {
        return joms.popup.friend.response;
    });

})( window, function() {

var popup, elem, id, connection;

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

            connection = json.connection_id;

            elem = popup.contentContainer;
            elem.on( 'click', '.joms-js--button-cancel', reject );
            elem.on( 'click', '.joms-js--button-save', approve );
        }
    });
}

function reject() {
    joms.ajax({
        func: 'friends,ajaxRejectRequest',
        data: [ connection ],
        callback: function( json ) {
            update( json );
        }
    });
}

function approve() {
    joms.ajax({
        func: 'friends,ajaxApproveRequest',
        data: [ connection ],
        callback: function( json ) {
            update( json );
        }
    });
}

function update( json ) {
    var step1 = elem.find('.joms-js--step1'),
        step2 = elem.find('.joms-js--step2');

    if ( !json.error ) {
        popup.st.callbacks || (popup.st.callbacks = {});
        popup.st.callbacks.close = function() {
            window.location.reload();
        };
    }

    step2.find('.joms-popup__content').html( json.error || json.message );
    step1.hide();
    step2.show();
}

function buildHtml( json ) {
    var error = false;

    json || (json = {});
    if ( json.error && !json.desc ) {
        error = json.error;
    }

    return [
        '<div class="joms-popup joms-popup--whiteblock">',
        '<div class="joms-popup__title"><button class="mfp-close" type="button" title="Close (Esc)">×</button>', json.title, '</div>',
        '<div class="joms-js--step1', ( error ? ' joms-popup__hide' : '' ), '">',
            '<div class="joms-popup__content">',
                '<div class="joms-stream__header" style="padding:0">',
                    '<div class="joms-avatar--stream"><img src="', json.avatar, '"></div>',
                    '<div class="joms-stream__meta"><span>', json.desc, '</span></div>',
                '</div>',
            '</div>',
            '<div class="joms-popup__content">',
                '<div class="cStream-Quote">', json.message, '</div>',
            '</div>',
            '<div class="joms-popup__action">',
                '<button class="joms-button--neutral joms-button--small joms-left joms-js--button-cancel">', json.btnReject, '</button>',
                '<button class="joms-button--primary joms-button--small joms-js--button-save">', json.btnAccept, '</button>',
            '</div>',
        '</div>',
        '<div class="joms-js--step2', ( error ? '' : ' joms-popup__hide' ), '">',
            '<div class="joms-popup__content joms-popup__content--single">', ( error || '' ), '</div>',
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
