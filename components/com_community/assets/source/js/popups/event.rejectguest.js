(function( root, $, factory ) {

    joms.popup || (joms.popup = {});
    joms.popup.event || (joms.popup.event = {});
    joms.popup.event.rejectGuest = factory( root, $ );

    define([ 'utils/popup' ], function() {
        return joms.popup.event.rejectGuest;
    });

})( window, joms.jQuery, function() {

var popup, elem, id, userid;

function render( _popup, _id, _userid ) {
    if ( elem ) elem.off();
    popup = _popup;
    id = _id;
    userid = _userid;

    joms.ajax({
        func: 'events,ajaxConfirmRemoveGuest',
        data: [ userid, id ],
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
    var checked = elem.find('input:checkbox')[0].checked || false;

    joms.ajax({
        func: checked ? 'events,ajaxBlockGuest' : 'events,ajaxRemoveGuest',
        data: [ userid, id ],
        callback: function( json ) {
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
return function( id, userid ) {
    joms.util.popup.prepare(function( mfp ) {
        render( mfp, id, userid );
    });
};

});
