(function( root, $, factory ) {

    joms.popup || (joms.popup = {});
    joms.popup.stream || (joms.popup.stream = {});
    joms.popup.stream.selectPrivacy = factory( root, $ );

    define([ 'utils/popup' ], function() {
        return joms.popup.stream.selectPrivacy;
    });

})( window, joms.jQuery, function( window, $ ) {

var popup, elem, id;

function render( _popup, _id ) {
    if ( elem ) elem.off();
    popup = _popup;
    id = _id;

    popup.items[0] = {
        type: 'inline',
        src: buildHtml()
    };

    popup.updateItemHTML();

    elem = popup.contentContainer;
    elem.on( 'click', 'a', save );
}

function save( e ) {
    var el = $( e.currentTarget ),
        privacy = el.data('value'),
        className = el.data('classname');

    joms.ajax({
        func: 'activities,ajaxUpdatePrivacyActivity',
        data: [ id, privacy ],
        callback: function( json ) {
            var item;

            elem.off();
            popup.close();

            if ( json.success ) {
                item = $('.joms-stream').filter('[data-stream-id=' + id + ']');
                item.find('.joms-stream__meta use').attr( 'xlink:href', window.location + '#' + className );
            }
        }
    });
}

function buildHtml() {
    var privacies, filter, base, html, i;

    privacies = [
        [ 'public', 10, window.joms_lang.COM_COMMUNITY_PRIVACY_PUBLIC, 'earth' ],
        [ 'site_members', 20, window.joms_lang.COM_COMMUNITY_PRIVACY_SITE_MEMBERS, 'users' ],
        [ 'friends', 30, window.joms_lang.COM_COMMUNITY_PRIVACY_FRIENDS, 'user' ],
        [ 'me', 40, window.joms_lang.COM_COMMUNITY_PRIVACY_ME, 'lock' ]
    ];

    // Filter.
    filter = window.joms_privacylist;
    if ( filter && filter.length ) {
        for ( i = privacies.length - 1; i >= 0; i-- ) {
            if ( filter.indexOf( privacies[i][0] ) < 0 ) {
                privacies.splice( i, 1 );
            }
        }
    }

    base = window.location.href;
    base = base.replace( /#.*$/, '' );

    html = '';
    for ( i = 0; i < privacies.length; i++ ) {
        html += '<a href="javascript:" data-value="' + privacies[i][1] + '" data-classname="joms-icon-' + privacies[i][3] + '">';
        html += '<svg viewBox="0 0 16 16" class="joms-icon"><use xlink:href="' + base + '#joms-icon-' + privacies[i][3] + '"></use></svg> ';
        html += '<span>' + privacies[i][2] + '</span></a>';
    }

    return [
        '<div class="joms-popup joms-popup--whiteblock joms-popup--privacy">',
        '<div><div class="joms-popup__content joms-popup__content--single">', html, '</div></div>',
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
