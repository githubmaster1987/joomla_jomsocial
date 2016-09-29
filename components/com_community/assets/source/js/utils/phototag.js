(function( root, $, factory ) {

    joms.util || (joms.util = {});
    joms.util.phototag = factory( root, $ );

})( window, joms.jQuery, function( window, $, undef ) {

var wrapper, elem, img, friends, callback, tagAdded, isPopup, noResult;

function populate( el, tags ) {
    var cssTags = '.joms-phototag__tags',
        cssTag = '.joms-phototag__tag',
        $tags, $tag, tag, pos, top, left, width, tagTop, tagLeft, tagWidth, tagHeight, height, i;

    $( cssTags ).remove();

    if ( tags && tags.length ) {
        // Image measurements.
        img    = $( el );
        pos    = img.position();
        top    = pos.top;
        left   = pos.left;
        width  = img.width();
        height = img.height();

        // Tags container.
        $tags = $( '<div class=' + cssTags.substr(1) + '></div>' );
        $tags.css({
            top: 0,
            left: 0,
            right: 0,
            bottom: 0,
            margin: 'auto',
            width: width,
            height: height
        });

        $tags.insertAfter( img );

        for ( i = 0; i < tags.length; i++ ) {
            tag       = tags[ i ];
            tagTop    = Math.round( height * tag.top );
            tagLeft   = Math.round( width * tag.left );
            tagWidth  = Math.round( width * tag.width );
            tagHeight = Math.round( height * tag.height );

            // Force square.
            tagHeight = tagWidth = Math.max( 10, Math.min( tagWidth, tagHeight ) );

            $tag = $( '<div class=' + cssTag.substr(1) + '><span>' + tag.displayName + '</span></div>' );
            $tag.css({
                top    : tagTop + 'px',
                left   : tagLeft + 'px',
                width  : tagWidth + 'px',
                height : tagHeight + 'px'
            });
            $tag.appendTo( $tags );
        }
    }
}

function create( e, tags, type, groupid, eventid ) {
    var imgOffset, parOffset, pos, top, left, width, height;

    destroy();

    // Hide tagging info.
    $( '.joms-phototag__tags' ).hide();

    wrapper   = $( buildHtml() );
    elem      = wrapper.find('.joms-phototag');
    img       = $( e.target );
    friends   = undef;
    callback  = {};
    width     = img.width();
    height    = img.height();
    imgOffset = img.offset();
    parOffset = img.parent().offset();
    top       = imgOffset.top - parOffset.top;
    left      = imgOffset.left - parOffset.left;
    tagAdded  = tags || [];
    isPopup   = type !== 'page' ? true : false;

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

    wrapper.insertAfter( img );

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
    // Show tagging info.
    $( '.joms-phototag__tags' ).show();

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

function calcClickPosition( e ) {
    var height = img.height(),
        width  = img.width(),
        offset, left, top;

    // Calculate offset position.
    if ( isPopup ) {
        top  = e.clientY - 45 - e.target.offsetTop - 43;
        left = e.clientX - 45 - e.target.offsetLeft - 43;
    } else {
        offset = img.offset();
        top    = e.pageY - offset.top - 43;
        left   = e.pageX - offset.left - 43;
    }

    // Respect wrapper boundaries.
    top  = Math.max( 0, Math.min( top, height - 86 ) );
    left = Math.max( 0, Math.min( left, width - 86 ) );

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
    boxLeft = pos.left;
    boxTop = pos.top;

    // Percentage (relative to wrapper height).
    boxWidth = boxWidth / ctWidth;
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
    populate: populate,
    create: create,
    destroy: destroy,
    on: on,
    off: off
};

});
