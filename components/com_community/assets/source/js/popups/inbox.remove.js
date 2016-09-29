(function( root, $, factory ) {

    joms.popup || (joms.popup = {});
    joms.popup.inbox || (joms.popup.inbox = {});
    joms.popup.inbox.remove = factory( root, $ );

    define([ 'utils/popup' ], function() {
        return joms.popup.inbox.remove;
    });

})( window, joms.jQuery, function( window, $ ) {

var popup, elem, task, msgids;

function render( _popup, _task, _msgids ) {
    var data;

    if ( elem ) elem.off();
    popup = _popup;
    task = _task;
    msgids = _msgids;

    data = [ task ];
    msgids.length || data.push('empty');

    joms.ajax({
        func: 'inbox,ajaxDeleteMessages',
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
    joms.ajax({
        func: task === 'inbox' ? 'inbox,ajaxRemoveFullMessages' : 'inbox,ajaxRemoveSentMessages',
        data: [ msgids.join(',') ],
        callback: function( json ) {
            var i;

            elem.find('.joms-js--step1').hide();
            elem.find('.joms-js--step2').show().children().html( json.error || json.message );

            if ( json.success ) {
                $('.joms-js--message-checkall')[0].checked = false;
                for ( i = 0; i < msgids.length; i++ ) {
                    $( '.joms-js--message-item-' + msgids[i] ).remove();
                }
                if ( !$('.joms-js--message-item').length ) {
                    $('.joms-js--message-ct').remove();
                }
            }
        }
    });
}

function buildHtml( json ) {
    json || (json = {});

    return [
        '<div class="joms-popup joms-popup--whiteblock">',
        '<div class="joms-popup__title"><button class="mfp-close" type="button" title="Close (Esc)">×</button>', json.title, '</div>',
        '<div class="joms-js--step1 ', ( json.error ? 'joms-popup__hide' : '' ), '">',
            '<div class="joms-popup__content">', ( json.html || '' ), '</div>',
            '<div class="joms-popup__action">',
            '<button class="joms-button--neutral joms-button--small joms-left joms-js--button-cancel">', json.btnNo, '</button> &nbsp;',
            '<button class="joms-button--primary joms-button--small joms-js--button-save">', json.btnYes, '</button>',
            '</div>',
        '</div>',
        '<div class="joms-js--step2 ', ( json.error ? '' : 'joms-popup__hide' ), '">',
            '<div class="joms-popup__content joms-popup__content--single">', ( json.error || '' ), '</div>',
        '</div>',
        '</div>'
    ].join('');
}

// Exports.
return function( task, msgids ) {
    joms.util.popup.prepare(function( mfp ) {
        render( mfp, task, msgids );
    });
};

});
