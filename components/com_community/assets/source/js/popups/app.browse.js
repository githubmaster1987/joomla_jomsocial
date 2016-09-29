(function( root, $, factory ) {

    joms.popup || (joms.popup = {});
    joms.popup.app || (joms.popup.app = {});
    joms.popup.app.browse = factory( root, $ );

    define([ 'utils/popup' ], function() {
        return joms.popup.app.browse;
    });

})( window, joms.jQuery, function( window, $ ) {

var popup, elem, pos;

function render( _popup, _pos ) {
    if ( elem ) elem.off();
    popup = _popup;
    pos = _pos;

    joms.ajax({
        func: 'apps,ajaxBrowse',
        data: [ pos ],
        callback: function( json ) {
            popup.items[0] = {
                type: 'inline',
                src: buildHtml( json )
            };

            popup.updateItemHTML();

            elem = popup.contentContainer;
            elem.on( 'click', 'a[data-appname]', save );
            elem.on( 'click', '.joms-js--btn-view-all', viewAll );

        }
    });
}

function save( e ) {
    var el = $( e.target ),
        appName = el.data('appname'),
        position = el.data('position'),
        stacked = position.indexOf('-stacked') >= 0,
        tabTemplate = '<a href="#joms-js--app-app_id" class="no-padding joms-js--app-tab-app_id active"><div class="joms-tab__bar--button"><span class="title">app_title</span></div></a>';

    joms.ajax({
        func: 'apps,ajaxAddApp',
        data: [ appName, position ],
        callback: function( json ) {
            var $pos, $btn, $tab;
            if ( json.success ) {
                $pos = $( '.joms-js--app-pos-' + pos );
                $tab = $pos.find('.joms-tab__bar').eq(0);
                if (stacked) {
                    $tab.before( $(json.item).show() );
                } else {
                    $btn = $tab.find('.joms-js--app-new');
                    $btn.prevAll().removeClass('active');
                    $btn.before( tabTemplate.replace( /app_id/g, json.id ).replace( /app_title/, json.title ) );
                    $pos.find('.joms-tab__content').hide();
                    $pos.append( $(json.item).show() );
                }
                getSetting( json.id, appName );
            }
        }
    });
}

function getSetting( appId, appName ) {
    joms.ajax({
        func: 'apps,ajaxShowSettings',
        data: [ appId, appName ],
        callback: function( json ) {
            elem.off( 'click', 'a[data-appname]' );
            elem.html( buildHtml( json, 'setting' ) );
            elem.on( 'click', '.joms-popup__content,.joms-popup__action', function( e ) {
                e.stopPropagation();
                return false;
            });
            elem.on( 'click', '[data-ui-object=popup-button-save]', function() {
                saveSetting();
            });
        }
    });
}

function saveSetting() {
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

function viewAll() {
    var $ct = elem.find('.joms-popup__content').eq(0),
        height = $ct.innerHeight();

    $ct.css({
        height: height,
        overflow: 'auto'
    });

    elem.find('.joms-js--btn-view-all').parent().remove();
    elem.find('.joms-js--app').show();

    $ct.animate({ scrollTop: $ct[0].scrollHeight });
}

function buildHtml( json, type ) {
    var action = '';

    json || (json = {});

    if ( type === 'setting' && json.btnSave ) {
        action = [
            '<div class="joms-popup__action">',
            '<button class="joms-button--neutral joms-button--small joms-js--button-close joms-left">', window.joms_lang.COM_COMMUNITY_CLOSE_BUTTON, '</button>',
            '<button class="joms-button--primary joms-button--small" data-ui-object="popup-button-save">', json.btnSave, '</button>',
            '</div>'
        ].join('');
    } else {
        action = [
            '<div class="joms-popup__action">',
            '<button class="joms-button--neutral joms-button--small joms-js--button-close">', window.joms_lang.COM_COMMUNITY_CLOSE_BUTTON, '</button>',
            '</div>'
        ].join('');
    }

    return [
        '<div class="joms-popup joms-popup--whiteblock">',
        '<div class="joms-popup__title"><button class="mfp-close" type="button" title="Close (Esc)">×</button>', json.title, '</div>',
        '<div class="joms-popup__content joms-popup__content--single">', ( json.html || '' ), '</div>',
        action,
        '</div>'
    ].join('');
}

// Exports.
return function( pos ) {
    joms.util.popup.prepare(function( mfp ) {
        render( mfp, pos );
    });
};

});
