(function( root, $, factory ) {

    joms.popup || (joms.popup = {});
    joms.popup.login = factory( root, $ );

    define([ 'utils/popup' ], function() {
        return joms.popup.login;
    });

})( window, joms.jQuery, function() {

var popup, elem;

function render( _popup, _json ) {
    if ( elem ) elem.off();

    popup = _popup;
    popup.items[0] = {
        type: 'inline',
        src: buildHtml( _json )
    };

    popup.updateItemHTML();

    elem = popup.contentContainer;
    elem.find('form').on( 'submit', send );
}

function send( e ) {
    e.preventDefault();
    e.stopPropagation();
    joms.ajax({
        func: 'system,ajaxGetLoginFormToken',
        data: [],
        callback: function( json ) {
            var form = elem.find('form');
            if ( json.token ) {
                form.find('.joms-js--token input').prop('name', json.token);
            }
            form.off('submit');
            form.find('[name=submit]').click();
        }
    });
}

function buildHtml( json ) {
    json || (json = {});

    return [
        '<div class="joms-popup joms-popup--whiteblock joms-popup--500">',
        '<div class="joms-popup__title"><button class="mfp-close" type="button" title="Close (Esc)">×</button>', json.title, '</div>',
        '<div class="joms-popup__content joms-popup__content--single">', ( json.html || '' ), '</div>',
        '</div>'
    ].join('');
}

// Exports.
return function( html ) {
    joms.util.popup.prepare(function( mfp ) {
        render( mfp, html );
    });
};

});
