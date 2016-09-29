(function( root, $, factory ) {

    joms.popup || (joms.popup = {});
    joms.popup.file || (joms.popup.file = {});
    joms.popup.file.list = factory( root, $ );

    define([ 'utils/loadlib', 'utils/popup' ], function() {
        return joms.popup.file.list;
    });

})( window, joms.jQuery, function( window, $ ) {

var popup, elem, type, id, tab, start, $currentTab, $btnLoadMore;

function render( _popup, _type, _id ) {
    if ( elem ) elem.off();
    popup = _popup;
    type = _type;
    id = _id;

    joms.ajax({
        func: 'files,ajaxviewMore',
        data: [ type, id ],
        callback: function( json ) {
            popup.items[0] = {
                type: 'inline',
                src: buildHtml( json )
            };

            popup.updateItemHTML();

            elem = popup.contentContainer;
            $btnLoadMore = elem.find('.joms-js--btn-loadmore');

            elem.on( 'click', '.joms-js--tab-bar a', load );
            elem.on( 'click', '.joms-js--btn-loadmore', loadmore );

            tab = false;
            elem.find('.joms-js--tab-bar a.active').trigger('click');
        }
    });
}

function _load( tabid, callback ) {
    joms.ajax({
        func: 'files,ajaxgetFileList',
        data: [ tabid, id, start, 4, type ],
        callback: function( json ) {
            callback( json );
        }
    });
}

function load() {
    var $tab = $( this ),
        tabid = $tab.data('id');

    if ( tab === tabid ) {
        return;
    }

    tab = tabid;
    $currentTab = elem.find( '.joms-js--tab-' + tabid );

    $tab.addClass('active').siblings().removeClass('active');
    $btnLoadMore.css({ visibility: 'hidden' });
    $currentTab.empty().show().siblings('.joms-js--tab').hide();

    start = 0;
    _load( tab, function( json ) {
        $currentTab.html( json.html );
        if ( json.next && json.count ) {
            start = json.next;
            $btnLoadMore.css({ visibility: 'visible' });
            $btnLoadMore.html( window.joms_lang.COM_COMMUNITY_FILES_LOAD_MORE + ' (' + json.count + ')' );
        } else {
            $btnLoadMore.css({ visibility: 'hidden' });
        }
    });
}

function loadmore() {
    _load( tab, function( json ) {
        $currentTab.append( json.html );
        $currentTab[0].scrollTop = $currentTab[0].scrollHeight;
        if ( json.next && json.count ) {
            start = json.next;
            $btnLoadMore.css({ visibility: 'visible' });
            $btnLoadMore.html( window.joms_lang.COM_COMMUNITY_FILES_LOAD_MORE + ' (' + json.count + ')' );
        } else {
            $btnLoadMore.css({ visibility: 'hidden' });
        }
    });
}

function buildHtml( json ) {
    json || (json = {});

    return [
        '<div class="joms-popup joms-popup--whiteblock joms-popup--600">',
        '<div class="joms-popup__title"><button class="mfp-close" type="button" title="Close (Esc)">×</button>', json.title, '</div>',
        json.html,
        '</div>'
    ].join('');
}

// Exports.
return function( type, id ) {
    joms.util.popup.prepare(function( mfp ) {
        render( mfp, type, id );
    });
};

});
