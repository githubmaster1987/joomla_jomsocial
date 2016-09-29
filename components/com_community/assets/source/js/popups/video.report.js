(function( root, factory ) {

    joms.popup || (joms.popup = {});
    joms.popup.video || (joms.popup.video = {});
    joms.popup.video.report = factory( root );

    define([ 'utils/popup' ], function() {
        return joms.popup.video.report;
    });

})( window, function() {

var popup, elem, id, url;

function render( _popup, _id, _url ) {
    if ( elem ) elem.off();
    popup = _popup;
    id = _id;
    url = _url;

    joms.ajax({
        func: 'system,ajaxReport',
        data: [],
        callback: function( json ) {
            popup.items[0] = {
                type: 'inline',
                src: buildHtml( json )
            };

            popup.updateItemHTML();

            elem = popup.contentContainer;
            elem.on( 'change', 'select', changeText );
            elem.on( 'click', '.joms-js--button-cancel', cancel );
            elem.on( 'click', '.joms-js--button-save', save );
        }
    });
}

function changeText( e ) {
    elem.find('textarea').val( e.target.value );
}

function cancel() {
    elem.off();
    popup.close();
}

function save() {
    var rTrim = /^\s+|\s+$/g,
        message;

    message = elem.find('textarea').val();
    message = message.replace( rTrim, '' );

    if ( !message ) {
        elem.find('.joms-js--error').show();
        return;
    }

    elem.find('.joms-js--error').hide();

    joms.ajax({
        func: 'system,ajaxSendReport',
        data: [ 'videos,reportVideo', url || window.location.href, message, id ],
        callback: function( json ) {
            elem.find('.joms-js--step1').hide();
            elem.find('.joms-js--step2').show().children().html( json.error || json.message );
        }
    });
}


function buildHtml( json ) {
    json || (json = {});

    return [
        '<div class="joms-popup joms-popup--whiteblock joms-popup--500">',
        '<div class="joms-popup__title"><button class="mfp-close" type="button" title="Close (Esc)">×</button>', json.title, '</div>',
        '<div class="joms-js--step1', ( json.error ? ' joms-popup__hide' : '' ), '">',
            json.html,
            '<div class="joms-popup__action">',
            '<button class="joms-button--neutral joms-button--small joms-left joms-js--button-cancel">', json.btnCancel, '</button> &nbsp;',
            '<button class="joms-button--primary joms-button--small joms-js--button-save">', json.btnSend, '</button>',
            '</div>',
        '</div>',
        '<div class="joms-js--step2', ( json.error ? '' : ' joms-popup__hide' ), '">',
            '<div class="joms-popup__content joms-popup__content--single">', ( json.error || '' ), '</div>',
        '</div>',
        '</div>'
    ].join('');
}

// Exports.
return function( id, url ) {
    joms.util.popup.prepare(function( mfp ) {
        render( mfp, id, url );
    });
};

});