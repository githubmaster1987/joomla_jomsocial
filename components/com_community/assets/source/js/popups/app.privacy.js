(function( root, $, factory ) {

    joms.popup || (joms.popup = {});
    joms.popup.app || (joms.popup.app = {});
    joms.popup.app.privacy = factory( root, $ );

    define([ 'utils/popup' ], function() {
        return joms.popup.app.privacy;
    });

})( window, joms.jQuery, function() {

var popup, elem, name;

function render( _popup, _name ) {
    if ( elem ) elem.off();
    popup = _popup;
    name = _name;

    joms.ajax({
        func: 'apps,ajaxShowPrivacy',
        data: [ name ],
        callback: function( json ) {
            popup.items[0] = {
                type: 'inline',
                src: buildHtml( json )
            };

            popup.updateItemHTML();

            elem = popup.contentContainer;
            elem.on( 'click', '[data-ui-object=popup-button-save]', save );
        }
    });
}

function save() {
    var $radio = elem.find('input[type=radio]:checked'),
        privacy = $radio.val();

    joms.ajax({
        func: 'apps,ajaxSavePrivacy',
        data: [ name, privacy ],
        callback: function( json ) {
            if ( json.error ) {
                elem.find('.joms-popup__content').html( json.error );
                elem.find('.joms-popup__action').remove();
                return;
            }

            popup.close();
        }
    });
}

function buildHtml( json ) {
    json || (json = {});

    return [
        '<div class="joms-popup joms-popup--whiteblock">',
        '<div class="joms-popup__title"><button class="mfp-close" type="button" title="Close (Esc)">×</button>', json.title, '</div>',
        '<div class="joms-popup__content joms-popup__content--single">', ( json.html || '' ), '</div>',
        '<div class="joms-popup__action">',
        '<button class="joms-button--primary joms-button--small" data-ui-object="popup-button-save">', json.btnSave, '</button>',
        '</div>',
        '</div>'
    ].join('');
}

// Exports.
return function( name ) {
    joms.util.popup.prepare(function( mfp ) {
        render( mfp, name );
    });
};

});
