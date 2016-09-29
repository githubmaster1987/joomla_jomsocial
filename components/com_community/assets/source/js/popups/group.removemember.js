(function( root, $, factory ) {

    joms.popup || (joms.popup = {});
    joms.popup.group || (joms.popup.group = {});
    joms.popup.group.removeMember = factory( root, $ );

    define([ 'utils/popup', 'functions/notification' ], function() {
        return joms.popup.group.removeMember;
    });

})( window, joms.jQuery, function( window, $ ) {

var popup, elem, id, userid;

function render( _popup, _id, _userid ) {
    if ( elem ) elem.off();
    popup = _popup;
    id = _id;
    userid = _userid;

    joms.ajax({
        func: 'groups,ajaxConfirmMemberRemoval',
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
    var checkbox = elem.find('input:checkbox'),
        func = checkbox[0].checked ? 'groups,ajaxBanMember' : 'groups,ajaxRemoveMember',
        data = [ userid, id ];

    joms.ajax({
        func: func,
        data: data,
        callback: function( json ) {
            elem.find('.joms-js--step1').hide();
            elem.find('.joms-js--step2').show().children().append( json.error || json.message );

            if ( json.success ) {
                $( '.joms-js--member-group-' + id + '-' + userid ).remove();
                $( '.joms-js--request-buttons-group-' + id + '-' + userid ).remove();
                $( '.joms-js--request-notice-group-' + id + '-' + userid ).html( json && json.message || '' );
                joms.fn.notification.updateCounter( 'general', id, -1 );
            }
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
return function( id, userId ) {
    joms.util.popup.prepare(function( mfp ) {
        render( mfp, id, userId );
    });
};

});
