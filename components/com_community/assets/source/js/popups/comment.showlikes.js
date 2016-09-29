(function( root, $, factory ) {

    joms.popup || (joms.popup = {});
    joms.popup.comment || (joms.popup.comment = {});
    joms.popup.comment.showLikes = factory( root, $ );

    define([ 'utils/popup' ], function() {
        return joms.popup.comment.showLikes;
    });

})( window, joms.jQuery, function() {

var popup, elem, id;

function render( _popup, _id ) {
    if ( elem ) elem.off();
    popup = _popup;
    id = _id;

    joms.ajax({
        func: 'activities,ajaxshowLikedUser',
        data: [ id ],
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
        '<div class="joms-popup joms-popup--whiteblock joms-popup--rounded joms-popup--80pc">',
        '<button class="mfp-close joms-hide"></button>',
        '<div class="joms-comment">', ( json.html || '' ), '</div>',
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
