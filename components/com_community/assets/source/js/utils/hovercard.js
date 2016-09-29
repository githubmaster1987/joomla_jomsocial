(function( root, $, factory ) {

    joms.util || (joms.util = {});
    joms.util.hovercard = factory( root, $ );

})( window, joms.jQuery, function( window, $ ) {

var card, showTimer, hideTimer, cache = {};

var MOUSEOVER_EVENT = 'mouseover.joms-hcard',
    MOUSEOUT_EVENT = 'mouseout.joms-hcard',
    IMG_SELECTOR = 'img[data-author]';

function initialize() {
    // Only enable on desktop browser.
    if ( joms.mobile ) {
        return;
    }

    // Attach handler.
    $( document.body )
        .off( MOUSEOVER_EVENT ).off( MOUSEOUT_EVENT )
        .on( MOUSEOVER_EVENT, IMG_SELECTOR, onMouseOver )
        .on( MOUSEOUT_EVENT, IMG_SELECTOR, onMouseOut );
}

function onMouseOver( e ) {
    var img = $( e.target ),
        id = img.data('author');

    if ( !card ) {
        createCard();
    }

    clearTimeout( hideTimer );

    if ( cache[id] ) {
        clearTimeout( showTimer );
        showTimer = setTimeout(function() {
            updateCard( cache[id], img );
        }, 400 );
        return;
    }

    joms.ajax({
        func: 'profile,ajaxFetchCard',
        data: [ id ],
        callback: function( json ) {
            if ( json.html ) {
                cache[id] = json.html;
                clearTimeout( showTimer );
                updateCard( json.html, img );
            }
        }
    });
}

function onMouseOut() {
    clearTimeout( showTimer );
    hideTimer = setTimeout(function() {
        card && card.hide();
    }, 400 );
}

function createCard() {
    card = $('<div>Loading...</div>');
    card.css({ position: 'absolute', zIndex: 2000 });
    card.appendTo( document.body );

    card.on( MOUSEOVER_EVENT, function() { clearTimeout( hideTimer ); });
    card.on( MOUSEOUT_EVENT, onMouseOut );
}

function updateCard( html, img ) {
    var doc = $( document ),
        offset = img.offset(),
        width = img.width(),
        height = img.height(),
        maxWidth = window.innerWidth,
        maxHeight = window.innerHeight + doc.scrollTop(),
        cardHeight = 140,
        alignLeft = offset.left + 320 < maxWidth,
        top = offset.top + height + 10;

    card.html( html );
    card.css({
       top: -10000,
       left: alignLeft ? offset.left : '',
       right: alignLeft ? '' : maxWidth - offset.left - width
    });
    card.show();

    cardHeight = card.height();
    if ( top + cardHeight > maxHeight ) {
        top = offset.top - cardHeight - 4;
    }

    card.css({ top: top });
}

// Exports.
return {
    initialize: initialize
};

});
