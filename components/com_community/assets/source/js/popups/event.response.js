(function( root, $, factory ) {

    joms.popup || (joms.popup = {});
    joms.popup.event || (joms.popup.event = {});
    joms.popup.event.response = factory( root, $ );

    define([ 'utils/popup' ], function() {
        return joms.popup.event.response;
    });

})( window, joms.jQuery, function( window, $ ) {

var popup, elem, id;

function render( _popup, _id, _data ) {
    if ( elem ) elem.off();
    popup = _popup;
    id = _id;

    popup.items[0] = {
        type: 'inline',
        src: buildHtml( _data )
    };

    popup.updateItemHTML();

    elem = popup.contentContainer;

    elem.on( 'click', 'a[data-value]', save );
}

function save( e ) {
    var value = $( e.currentTarget ).data('value');

    joms.ajax({
        func: 'events,ajaxUpdateStatus',
        data: [ id, value ],
        callback: function() {
            window.location.reload();
        }
    });
}

function buildHtml( data ) {
    var options = '',
        i;

    for ( i = 0; i < data.length; i++ ) {
        options += '<li><a data-value="' + data[i][0] + '" href="javascript:">' + data[i][1] + '</a></li>';
    }

    return [
        '<div class="joms-popup joms-popup--dropdown">',
            '<ul class="joms-dropdown">', options, '</ul>',
            '<button class="mfp-close" type="button" title="Close (Esc)">×</button>',
        '</div>'
    ].join('');
}

// Exports.
return function( id ) {
    var data = [].slice.call( arguments );
    data.shift();

    joms.util.popup.prepare(function( mfp ) {
        render( mfp, id, data );
    });
};

});
