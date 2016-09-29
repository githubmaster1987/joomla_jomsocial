(function( root, $, factory ) {

    joms.view || (joms.view = {});
    joms.view.page = factory( root, $ );

    define([ 'utils/hovercard', 'popups/page' ], function() {
        return joms.view.page;
    });

})( window, joms.jQuery, function( window, $ ) {

function initialize() {
    // joms.util.hovercard.initialize();
}

function like( type, id ) {
    joms.ajax({
        func: 'system,ajaxLike',
        data: [ type, id ],
        callback: function( json ) {
            if ( json.success ) {
                update( 'like', type, id, json.likeCount );
            }
        }
    });
}

function unlike( type, id ) {
    joms.ajax({
        func: 'system,ajaxUnlike',
        data: [ type, id ],
        callback: function( json ) {
            if ( json.success ) {
                update( 'unlike', type, id, json.likeCount );
            }
        }
    });
}

function share( url ) {
    joms.popup.page.share( url );
}

function update( action, type, id, count ) {
    var elem;

    elem = $( '.joms-js--like-' + type + '-' + id );
    elem.each(function() {
        var tagName = this.tagName.toLowerCase(),
            elem = $( this );

        if ( tagName === 'a' ) {
            if ( elem.hasClass('joms-popup__btn-like') ) {
                updatePopupButton( elem, action, type, id, count );
            } else {
                updateFocusButton( elem, action, type, id, count );
            }
        } else if ( tagName === 'button' ) {
            if ( elem.hasClass('joms-popup__btn-like') ) {
                updatePopupButton( elem, action, type, id, count );
            } else {
                updateButton( elem, action, type, id, count );
            }
        }
    });
}

function updatePopupButton( elem, action, type, id, count ) {
    var icon = '<svg viewBox="0 0 16 16" class="joms-icon"><use xlink:href="#joms-icon-thumbs-up"></use></svg>',
        lang;

    if ( action === 'like' ) {
        elem.attr( 'onclick', 'joms.view.page.unlike("' + type + '", "' + id + '");' );
        elem.addClass('liked');
        lang = elem.data('lang-liked');
    } else if ( action === 'unlike' ) {
        elem.attr( 'onclick', 'joms.view.page.like("' + type + '", "' + id + '");' );
        elem.removeClass('liked');
        lang = elem.data('lang-like');
    }

    lang = lang || elem.data('lang');
    count = +count;
    if ( count > 0 ) {
        lang += ' (' + count + ')';
    }

    elem.html( icon + ' <span>' + lang + '</span>' );
}

function updateFocusButton( elem, action, type, id, count ) {
    var lang;

    elem.find('span').html( count );

    if ( action === 'like' ) {
        elem.attr( 'onclick', 'joms.view.page.unlike("' + type + '", "' + id + '");' );
        elem.addClass('liked');
        if ( lang = elem.data('lang-liked') ) {
            elem.find('.joms-js--lang').text( lang );
        }
    } else if ( action === 'unlike' ) {
        elem.attr( 'onclick', 'joms.view.page.like("' + type + '", "' + id + '");' );
        elem.removeClass('liked');
        if ( lang = elem.data('lang-like') ) {
            elem.find('.joms-js--lang').text( lang );
        }
    }
}

function updateButton( elem, action, type, id, count ) {
    var lang;

    if ( action === 'like' ) {
        elem.attr( 'onclick', 'joms.view.page.unlike("' + type + '", "' + id + '");' );
        elem.removeClass('joms-button--neutral');
        elem.addClass('joms-button--primary');
        lang = elem.data('lang-liked');
    } else if ( action === 'unlike' ) {
        elem.attr( 'onclick', 'joms.view.page.like("' + type + '", "' + id + '");' );
        elem.addClass('joms-button--neutral');
        elem.removeClass('joms-button--primary');
        lang = elem.data('lang-like');
    }

    lang = lang || elem.data('lang') || '';
    count = +count;
    if ( count > 0 ) {
        lang += ' (' + count + ')';
    }

    elem.html( lang );
}

// Exports.
return {
    initialize: initialize,
    like: like,
    unlike: unlike,
    share: share
};

});
