(function( root, $, factory ) {

    joms.popup || (joms.popup = {});
    joms.popup.photo || (joms.popup.photo = {});
    joms.popup.photo.setAlbum = factory( root, $ );

    define([ 'utils/popup' ], function() {
        return joms.popup.photo.setAlbum;
    });

})( window, joms.jQuery, function( window, $ ) {

var popup, elem, id;

function render( _popup, _id ) {
    if ( elem ) elem.off();
    popup = _popup;
    id = _id;

    if ( Object.prototype.toString.call( id ) !== '[object Array]' ) {
        id = [ id ];
    }

    joms.ajax({
        func: 'photos,ajaxSetPhotoAlbum',
        data: [ id.join(',') ],
        callback: function( json ) {
            popup.items[0] = {
                type: 'inline',
                src: buildHtml( json )
            };

            popup.updateItemHTML();

            elem = popup.contentContainer;
            elem.on( 'click', '.joms-js--button-cancel', cancel );
            elem.on( 'click', '.joms-js--button-save', save );
        }
    });
}

function cancel() {
    elem.off();
    popup.close();
}

function save() {
    var albumid = elem.find('[name=albumid]').val();

    joms.ajax({
        func: 'photos,ajaxConfirmPhotoAlbum',
        data: [ albumid, id.join(',') ],
        callback: function( json ) {
            var message = [],
                $album, $photo, i;

            if ( json.message ) {
                message.push( json.message );
            }

            // Remove moved photos from album page.
            if ( json.moved && json.moved.length ) {
                for ( i = 0; i < json.moved.length; i++ ) {
                    $album = $( '.joms-js--album-' + json.moved[i].old_album );
                    if ( $album.length ) {
                        $photo = $album.find( '.joms-js--photo-' + json.moved[i].id );
                        $photo.remove();
                    }
                }
            }

            // Map errors.
            if ( Object.prototype.toString.call( json.error ) === '[object Array]' ) {
                if ( json.error.length ) {
                    for ( i = 0; i < json.error.length; i++ ) {
                        json.error[i] = '<li>ID: ' + json.error[i][0] + ' - ' + json.error[i][1] + '</li>';
                    }
                    message.push( '<ul>' + json.error.join('') + '</ul>' );
                }
            } else if ( typeof json.error === 'string' ) {
                message.push( json.error );
            }

            elem.find('.joms-js--step1').hide();
            elem.find('.joms-js--step2').show().children().html( message.join('<br/>') );
        }
    });
}

function buildHtml( json ) {
    json || (json = {});

    return [
        '<div class="joms-popup joms-popup--whiteblock">',
        '<div class="joms-popup__title"><button class="mfp-close" type="button" title="Close (Esc)">×</button>', json.title, '</div>',
        '<div class="joms-js--step1', ( json.error ? ' joms-popup__hide' : '' ), '">',
            '<div class="joms-popup__content joms-popup__content--single">', (json.html || ''), '</div>',
            '<div class="joms-popup__action">',
            '<button class="joms-button--neutral joms-button--small joms-left joms-js--button-cancel">', json.btnCancel, '</button> &nbsp;',
            '<button class="joms-button--primary joms-button--small joms-js--button-save">', json.btnYes, '</button>',
            '</div>',
        '</div>',
        '<div class="joms-js--step2', ( json.error ? '' : ' joms-popup__hide' ), '">',
            '<div class="joms-popup__content joms-popup__content--single">', ( json.error || '' ), '</div>',
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
