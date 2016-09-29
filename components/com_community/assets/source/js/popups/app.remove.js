(function( root, $, factory ) {

    joms.popup || (joms.popup = {});
    joms.popup.app || (joms.popup.app = {});
    joms.popup.app.remove = factory( root, $ );

    define([ 'utils/popup' ], function() {
        return joms.popup.app.remove;
    });

})( window, joms.jQuery, function( window, $ ) {

var popup, elem, id;

function render( _popup, _id ) {
    if ( elem ) elem.off();
    popup = _popup;
    id = _id;

    joms.ajax({
        func: 'apps,ajaxRemove',
        data: [ id ],
        callback: function( json ) {
            var $tab, $nexttab, $content;

            popup.items[0] = {
                type: 'inline',
                src: buildHtml( json )
            };

            popup.updateItemHTML();

            // locate the app
            $tab = $( '.joms-js--app-tab-' + id );
            $content = $( '#joms-js--app-' + id );

            // find next tab
            $nexttab = $tab.prev();
            if ( !$nexttab.length ) $nexttab = $tab.next().not('.joms-js--app-new');

            // remove the app
            $tab.remove();
            $content.remove();
            $nexttab.length && $nexttab.click();
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
        '<button class="joms-button--neutral joms-button--small joms-js--button-close">', window.joms_lang.COM_COMMUNITY_CLOSE_BUTTON, '</button>',
        '</div>',
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
