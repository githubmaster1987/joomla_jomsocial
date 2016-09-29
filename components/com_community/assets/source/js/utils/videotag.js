(function( root, $, factory ) {

    joms.util || (joms.util = {});
    joms.util.videotag = factory( root, $ );

})( window, joms.jQuery, function( window, $, undef ) {

var wrapper, elem, img, friends, callback, tagAdded, noResult;

function create( e, tags, groupid, eventid ) {
    var pos, top, left, width, height;

    destroy();

    wrapper  = $( buildHtml() );
    elem     = wrapper.find('.joms-phototag');
    img      = e && e.currentTarget ? $( e.currentTarget ).closest('.joms-popup--video').find('iframe,video,.joms-js--video').eq(0) : $( e );
    friends  = undef;
    callback = {};
    width    = img.width();
    height   = img.height();
    pos      = img.position();
    top      = pos.top;
    left     = pos.left;
    tagAdded = tags || [];

    elem.css({
        top: 0,
        left: 0
    });

    wrapper.css({
        top: top,
        left: left,
        width: width,
        height: height
    });

    wrapper.insertBefore( img );

    pos = calcClickPosition( e );

    elem.css({ top: pos.top, left: pos.left });
    elem.on( 'keyup', 'input', filter );
    elem.on( 'click', 'a[data-id]', select );
    elem.on( 'click', 'button', destroy );
    elem.on( 'click', function( e ) {
        e.stopPropagation();
    });
    wrapper.on( 'click', moveBoxPosition );

    if ( +groupid ) {
        joms.fn.tagging.fetchGroupMembers( groupid, function( members ) {
            friends = members;
            filter();
        });
    } else if ( +eventid ) {
        joms.fn.tagging.fetchEventMembers( eventid, function( members ) {
            friends = members;
            filter();
        });
    } else {
        filter();
    }

    if ( joms.ios ) {
        try {
            window.scrollTo( window.scrollLeft, elem.find('input').offset().top - 100 );
        } catch (e) {}
    }

    // Apparently Android (Chrome?) trigger "onresize" event when keypad being shown,
    // which make phototag immediately closed. Add resize handler only on desktop browser.
    if ( !joms.mobile ) {
        $( window ).on( 'resize.phototag', destroy );
    }
}

function filter( e ) {
    var input, keyword, filtered, ac;

    if ( !friends ) {
        friends = window.joms_friends || [];
    }

    input = $( e ? e.currentTarget : elem.find('input') );
    keyword = input.val().replace( /^\s+|\s+$/g, '' ).toLowerCase();
    filtered = friends;

    filtered = joms._.filter( friends, function( obj ) {
        if ( !obj ) return false;
        if ( !obj.name ) return false;
        if ( tagAdded && tagAdded.indexOf( obj.id + '' ) >= 0 ) return false;
        if ( keyword && obj.name.toLowerCase().indexOf( keyword ) < 0 ) return false;
        return true;
    });

    filtered = filtered.slice(0, 8);
    filtered = joms._.map( filtered, function( obj ) {
        return '<a href="javascript:" data-id="' + obj.id + '">' + obj.name + '</a>';
    });

    if ( !filtered.length ) {
        filtered = [ '<span><em>' + window.joms_lang.COM_COMMUNITY_NO_RESULT_FOUND + '</em></span>' ];
        noResult = true;
    } else {
        noResult = false;
    }

    ac = elem.find('.joms-phototag__autocomplete');
    ac.html( filtered.join('') );

    ac.append(
        '<div><button class="joms-button--neutral joms-button--small joms-button--full">' +
        window.joms_lang.COM_COMMUNITY_PHOTO_DONE_TAGGING +
        '</button></div>'
    );

    ac.show();
}

function select( e ) {
    var ac = elem.find('.joms-phototag__autocomplete'),
        el = $( e.currentTarget ),
        id = el.data('id') || '',
        pos;

    e.stopPropagation();
    ac.hide();

    if ( callback && callback.tagAdded ) {
        tagAdded || (tagAdded = []);
        tagAdded.push( id + '' );
        pos = calcBoxPosition();

        callback.tagAdded(
            id,
            pos.left,
            pos.top,
            pos.width,
            pos.height
        );

        filter();
    }
}

function destroy() {
    if ( elem ) {
        elem.remove();
        wrapper.remove();
        $( window ).off('resize.phototag');
        elem = undef;
        img = undef;

        if ( callback && callback.destroy ) {
            callback.destroy();
        }

        callback = undef;
    }
}

function on( eventType, fn ) {
    callback[ eventType ] = fn;
}

function off( eventType ) {
    if ( !eventType ) {
        callback = {};
    } else if ( callback[ eventType ] ) {
        callback[ eventType ] = undef;
    }
}

function calcClickPosition() {
    var height = img.height(),
        width  = img.width(),
        left, top;

    // Respect wrapper boundaries.
    top  = Math.max( 0, height - 86 ) / 4.5;
    left = Math.max( 0, width - 86 ) / 2;

    return {
        top: top,
        left: left
    };
}

function calcBoxPosition() {
    var pos, ctWidth, ctHeight, boxWidth, boxHeight, boxLeft, boxTop;

    ctWidth = wrapper.width();
    ctHeight = wrapper.height();

    pos = elem.position();
    boxWidth = elem.width();
    boxHeight = elem.height();
    boxLeft = pos.left + boxWidth / 2;
    boxTop = pos.top + boxHeight / 2;

    // Percentage (relative to wrapper height).
    boxWidth = boxWidth / ctHeight;
    boxHeight = boxHeight / ctHeight;

    // Percentage (relative to wrapper dimension).
    boxLeft = boxLeft / ctWidth;
    boxTop = boxTop / ctHeight;

    return {
        top    : boxTop,
        left   : boxLeft,
        width  : boxWidth,
        height : boxHeight
    };
}

function moveBoxPosition( e ) {
    var pos;

    if ( noResult ) {
        destroy();
        return;
    }

    pos = calcClickPosition( e );

    elem.css({
        top: pos.top,
        left: pos.left
    });
}

function buildHtml() {
    return [
        '<div class=joms-phototag__wrapper>',
        '<div class=joms-phototag>',
        '<div class=joms-phototag__input>',
        '<input type=text placeholder="', window.joms_lang.COM_COMMUNITY_SEARCH,'">',
        '<div class="joms-phototag__autocomplete"></div>',
        '</div>',
        '</div>',
        '</div>'
    ].join('');
}

// Exports.
return {
    create: create,
    destroy: destroy,
    on: on,
    off: off
};

});
