(function( root, factory ) {

    joms.popup || (joms.popup = {});
    joms.popup.discussion || (joms.popup.discussion = {});
    joms.popup.discussion.remove = factory( root );

    define([ 'utils/popup' ], function() {
        return joms.popup.discussion.remove;
    });

})( window, function() {

var popup, elem, groupid, id;

function render( _popup, _groupid, _id ) {
    if ( elem ) elem.off();
    popup = _popup;
    groupid = _groupid;
    id = _id;

    joms.ajax({
        func: 'groups,ajaxShowRemoveDiscussion',
        data: [ groupid, id ],
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
    elem.find('form')[0].submit();
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
return function( groupid, id ) {
    joms.util.popup.prepare(function( mfp ) {
        render( mfp, groupid, id );
    });
};

});
