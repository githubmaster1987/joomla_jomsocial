(function( root, $, factory ) {

    joms.popup || (joms.popup = {});
    joms.popup.stream || (joms.popup.stream = {});
    joms.popup.stream.showLikes = factory( root, $ );

    define([ 'utils/popup' ], function() {
        return joms.popup.stream.showLikes;
    });

})( window, joms.jQuery, function( window, $ ) {

var popup, elem, id, target;

function render( _popup, _id, _target ) {
    if ( elem ) elem.off();
    popup = _popup;
    id = _id;
    target = _target;

    joms.ajax({
        func: 'system,ajaxStreamShowLikes',
        data: [ id, target ],
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
    var isEmpty = true,
        fragment;

    json || (json = {});

    fragment = $( $.trim( json.html || '' ) );
    if ( fragment.children().length ) {
        isEmpty = false;
    }

    return [
        '<div class="joms-popup joms-popup--whiteblock joms-popup--rounded joms-popup--80pc">',
        '<button class="mfp-close joms-hide"></button>',
        '<div class="joms-comment">', ( isEmpty ? window.joms_lang.COM_COMMUNITY_NO_LIKES_YET : json.html ), '</div>',
        '</div>'
    ].join('');
}

// Exports.
return function( id, target ) {
    joms.util.popup.prepare(function( mfp ) {
        render( mfp, id, target );
    });
};

});
