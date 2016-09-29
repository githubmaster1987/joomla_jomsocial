(function( root, $, factory ) {

    joms.popup || (joms.popup = {});
    joms.popup.cover || (joms.popup.cover = {});
    joms.popup.cover.change = factory( root, $ );

    define([ 'utils/loadlib', 'utils/popup' ], function() {
        return joms.popup.cover.change;
    });

})( window, joms.jQuery, function( window, $ ) {

var popup, elem, type, id, uploader, container, button, result;

function render( _popup, _type, _id ) {
    if ( elem ) elem.off();
    popup = _popup;
    type = _type;
    id = _id;

    joms.ajax({
        func: 'photos,ajaxChangeCover',
        data: [ type, id ],
        callback: function( json ) {
            popup.items[0] = {
                type: 'inline',
                src: buildHtml( json )
            };

            popup.updateItemHTML();

            elem = popup.contentContainer;
            elem.on( 'click', '.joms-js--album', getPhotoList );
            elem.on( 'click', '.joms-js--back-to-album', backToAlbum );
            elem.on( 'click', '.joms-js--select-photo', selectPhoto );
            elem.on( 'click', '[data-ui-object=popup-button-upload]', upload );

            // Init uploader upon render.
            uploadInit();
        }
    });
}

function getPhotoList() {
    var $el = $( this ),
        album = $el.data('album'),
        total = $el.data('total');

    joms.ajax({
        func: 'photos,ajaxGetPhotoList',
        data: [ album, total ],
        callback: function( json ) {
            if ( json && json.html ) {
                $('.joms-js--album-list').hide();
                $('.joms-js--photo-list').html( json.html ).show();
            }
        }
    });
}

function backToAlbum() {
    $('.joms-js--photo-list').hide();
    $('.joms-js--album-list').show();
}

function selectPhoto() {
    var $el = $( this ),
        photo = $el.data('photo');

    joms.ajax({
        func: 'photos,ajaxSetPhotoCover',
        data: [ type, photo, id ],
        callback: function( json ) {
            if ( json && json.path ) {
                $('.joms-js--cover-image > img')
                    .attr( 'src', json.path )
                    .css({ top: 0 });

                $('.joms-js--cover-image-mobile')
                    .css({ background: 'url(' + json.path + ') no-repeat center center' });

                popup.close();
                $('.joms-js--menu-reposition').show();
            }
        }
    });
}

function upload() {
    uploadInit(function() {
        button.click();
    });
}

function uploadInit( callback ) {
    if ( typeof callback !== 'function' ) {
        callback = function() {};
    }

    if ( uploader ) {
        callback();
        return;
    }

    joms.util.loadLib( 'plupload', function () {
        var url;

        url       = elem.find('form').attr('action');
        container = $('<div id="joms-js--cover-uploader" aria-hidden="true" style="width:1px; height:1px; position:absolute; overflow:hidden;">').appendTo( document.body );
        button    = $('<div id="joms-js--cover-uploader-button">').appendTo( container );
        uploader  = new window.plupload.Uploader({
            url: url,
            filters: [{ title: 'Image files', extensions: 'jpg,jpeg,png,gif' }],
            container: 'joms-js--cover-uploader',
            browse_button: 'joms-js--cover-uploader-button',
            runtimes: 'html5,html4',
            multi_selection: false,
            file_data_name: 'uploadCover'
        });

        uploader.bind( 'FilesAdded', uploadAdded );
        uploader.bind( 'UploadProgress', uploadProgress );
        uploader.bind( 'Error', function() {});
        uploader.bind( 'FileUploaded', uploadUploaded );
        uploader.bind( 'UploadComplete', uploadComplete );
        uploader.init();

        button = container.find('input[type=file]');
        callback();
    });
}

function uploadAdded( up ) {
    window.setTimeout(function() {
        up.refresh();
        up.start();
    }, 0);
}

function uploadProgress( up, file ) {
    var percent, bar;
    percent = Math.min( 100, Math.floor( file.loaded / file.size * 100 ) );
    bar = elem.find( '.joms-progressbar__progress' );
    bar.stop().animate({ width: percent + '%' });
}

function uploadUploaded( up, files, data ) {
    var json = {};

    // Parse json response.
    try {
        json = JSON.parse( data.response );
    } catch ( e ) {}

    result = json;

    if ( json.path ) {
        $('.joms-js--cover-image > img')
            .attr( 'src', json.path )
            .css({ top: 0 });

        $('.joms-js--cover-image-mobile')
            .css({ background: 'url(' + json.path + ') no-repeat center center' });

        popup.close();
        $('.joms-js--menu-reposition').show();
    }
}

function uploadComplete() {
    if ( ! result.error ) {
        elem.find('.joms-js--cover-uploader-error').hide();
    } else {
        elem.find('.joms-js--cover-uploader-error').html( result.error ).show();
        elem.find('.joms-progressbar__progress').stop().animate({ width: '0%' });
    }
}

function buildHtml( json ) {
    json || (json = {});

    return [
        '<div class="joms-popup joms-popup--whiteblock joms-popup--500">',
        '<div class="joms-popup__title"><button class="mfp-close" type="button" title="Close (Esc)">×</button>', json.title, '</div>',
        ( json.html || '' ),
        '<div class="joms-popup__action">',
        '<button class="joms-button--neutral joms-button--small joms-js--button-close">', window.joms_lang.COM_COMMUNITY_CLOSE_BUTTON, '</button>',
        '</div>',
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
