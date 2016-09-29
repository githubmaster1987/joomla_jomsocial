(function( root, $, factory ) {

    joms.popup || (joms.popup = {});
    joms.popup.app || (joms.popup.app = {});
    joms.popup.app.setting = factory( root, $ );

    define([ 'utils/popup' ], function() {
        return joms.popup.app.setting;
    });

})( window, joms.jQuery, function() {

var popup, elem, id, name;

function render( _popup, _id, _name ) {
    if ( elem ) elem.off();
    popup = _popup;
    id = _id;
    name = _name;

    joms.ajax({
        func: 'apps,ajaxShowSettings',
        data: [ id, name ],
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
    var $form = elem.find('form'),
        params = $form.serializeArray(),
        data = [],
        i;

    for ( i = 0; i < params.length; i++ ) {
        data.push([ params[i].name, params[i].value ]);
    }

    joms.ajax({
        func: 'apps,ajaxSaveSettings',
        data: [ data ],
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
    var action = '';

    json || (json = {});

    if ( json.btnSave ) {
        action = [
            '<div class="joms-popup__action">',
            '<button class="joms-button--neutral joms-button--small joms-js--button-close joms-left">', window.joms_lang.COM_COMMUNITY_CLOSE_BUTTON, '</button>',
            '<button class="joms-button--primary joms-button--small" data-ui-object="popup-button-save">', json.btnSave, '</button>',
            '</div>'
        ].join('');
    }

    return [
        '<div class="joms-popup joms-popup--whiteblock">',
        '<div class="joms-popup__title"><button class="mfp-close" type="button" title="Close (Esc)">×</button>', json.title, '</div>',
        '<div class="joms-popup__content joms-popup__content--single" style="max-height:315px; overflow:auto">', ( json.html || '' ), '</div>',
        action,
        '</div>'
    ].join('');
}

// Exports.
return function( id, name ) {
    joms.util.popup.prepare(function( mfp ) {
        render( mfp, id, name );
    });
};

});
