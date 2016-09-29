(function( root, $, factory ) {

    joms.view || (joms.view = {});
    joms.view.cover = factory( root, $ );

    define(function() {
        return joms.view.cover;
    });

})( window, joms.jQuery, function( window, $ ) {

var type, id, cover, img, hammertime, repositioning;

function reposition( _type, _id ) {
    var top, maxHeight;

    type  = _type;
    id    = _id;
    cover = $('.joms-focus__cover');
    img   = cover.children('.joms-js--cover-image').children('img');

    if ( !img ) return;

    cover.css('cursor', 'move');
    cover.children('.joms-focus__header').hide();
    cover.children('.joms-focus__actions--reposition')
        .on( 'click', 'input', repositionAction )
        .show();

    img.data( 'top', img.position().top );

    // set reposition flag
    repositioning = true;

    hammertime = new joms.Hammer( img[0] );
    hammertime.on( 'dragstart dragup dragdown dragend', function( e ) {
        var newTop;
        if ( e.type === 'dragstart' ) {
            top = img.position().top;
            maxHeight = cover.height() - img.height();
        } else if ( e.type !== 'dragend' ) {
            newTop = Math.min( 0, top + e.gesture.deltaY );
            newTop = Math.max( maxHeight, newTop );
            img.css({ top: newTop });
        }
    });
}

function repositionAction( e ) {
    var elem;
    elem = $( e.target );
    cover.children('.joms-focus__actions--reposition').off( 'click', 'input' );
    elem.data('ui-object') === 'button-save' ? repositionSave() : repositionCancel();
}

function repositionSave() {
    var top;

    top = pixelToPercent( img.position().top, cover.height() );
    repositionReset();

    joms.ajax({
        func: 'photos,ajaxSetPhotoPhosition',
        data: [ type, id, top ]
    });
}

function repositionCancel() {
    img.css({ top: img.data('top') });
    repositionReset();
}

function repositionReset() {
    cover.css('cursor', '');
    cover.children('.joms-focus__actions--reposition').hide();
    cover.children('.joms-focus__header').show();
    cover = null;
    img = null;
    hammertime = null;
    repositioning = null;
}

function pixelToPercent( imgTop, coverHeight ) {
    var percent;
    percent = imgTop * 100 / coverHeight;
    percent = Math.round( percent * 10000 ) / 10000;
    return percent + '%';
}

function click( albumId, photoId ) {
    if ( !repositioning ) {
        joms.api.photoOpen( albumId, photoId );
    }
}

// Exports.
return {
    reposition: reposition,
    click: click
};

});
