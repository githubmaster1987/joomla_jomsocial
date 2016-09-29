(function( root, factory ) {

    joms.popup || (joms.popup = {});
    joms.popup.notification || (joms.popup.notification = {});
    joms.popup.notification.pm = factory( root );

    define([ 'utils/popup' ], function() {
        return joms.popup.notification.pm;
    });

})( window, function() {

function render( popup ) {
    joms.ajax({
        func: 'notification,ajaxGetInbox',
        data: [ '' ],
        callback: function( json ) {
            popup.items[0] = {
                type: 'inline',
                src: buildHtml( json )
            };

            popup.updateItemHTML();
        }
    });
}

function buildHtml( json ) {
    json || (json = {});

    return [
        '<div class="joms-popup joms-popup--whiteblock">',
        '<div class="joms-popup__title">', ( json.title || '' ), '</div>',
        '<div class="joms-popup__content joms-popup__content--single" style="max-height:400px;overflow:auto">',
        '<ul style="margin: 0; list-style: none;">', ( json.html || '' ), '</ul>',
        '</div>',
        '</div>'
    ].join('');
}

// Exports.
return function() {
    joms.util.popup.prepare(function( mfp ) {
        render( mfp);
    });
};

});
