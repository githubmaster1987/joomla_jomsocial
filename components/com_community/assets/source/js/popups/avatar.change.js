(function( root, $, factory ) {

    joms.popup || (joms.popup = {});
    joms.popup.avatar || (joms.popup.avatar = {});
    joms.popup.avatar.change = factory( root, $ );

    define([ 'utils/crop', 'utils/loadlib', 'utils/popup' ], function() {
        return joms.popup.avatar.change;
    });

})( window, joms.jQuery, function( window, $ ) {

var popup, elem, type, id, uploader, container, button, result;

function render( _popup, _type, _id ) {
    if ( elem ) elem.off();
    popup = _popup;
    type = _type;
    id = _id;

    joms.ajax({
        func: 'photos,ajaxUploadAvatar',
        data: [ type, id ],
        callback: function( json ) {
            popup.items[0] = {
                type: 'inline',
                src: buildHtml( json )
            };

            popup.updateItemHTML();

            elem = popup.contentContainer;
            elem.on( 'click', '.joms-js--button-upload', upload );
            elem.on( 'click', '.joms-js--button-save', save );
            elem.on( 'click', '.joms-js--button-rotate-left', rotateLeft );
            elem.on( 'click', '.joms-js--button-rotate-right', rotateRight );

            // Init uploader upon render.
            uploadInit();
        }
    });
}

function save() {
    var crop = joms.util.crop.getSelection();

    joms.ajax({
        func: 'photos,ajaxUpdateThumbnail',
        data: [ type, id, crop.x, crop.y, crop.width, crop.height ],
        callback: function( json ) {
            if ( json.success ) {
                window.location.reload( true );
            }
        }
    });
}

function rotateLeft() {
    joms.api.avatarRotate( type, id, 'left', function() {
        reloadImage();
    });
}

function rotateRight() {
    joms.api.avatarRotate( type, id, 'right', function() {
        reloadImage();
    });
}

function reloadImage() {
    var cropper = $('.joms-avatar__cropper'),
        img = cropper.find('img'),
        src = img.attr('src');

    src = src.replace(/\?.*$/, '');
    src = src + '?_=' + (new Date()).getTime();

    joms.util.crop.detach();
    img.removeAttr('src');
    img.attr( 'src', src );

    setTimeout(function() {
        joms.util.crop( img );
    }, 100 );
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
        container = $('<div id="joms-js--avatar-uploader" aria-hidden="true" style="width:1px; height:1px; position:absolute; overflow:hidden;">').appendTo( document.body );
        button    = $('<div id="joms-js--avatar-uploader-button">').appendTo( container );
        uploader  = new window.plupload.Uploader({
            url: url,
            filters: [{ title: 'Image files', extensions: 'jpg,jpeg,png,gif' }],
            container: 'joms-js--avatar-uploader',
            browse_button: 'joms-js--avatar-uploader-button',
            runtimes: 'html5,html4',
            multi_selection: false,
            file_data_name: 'filedata'
        });

        uploader.bind( 'FilesAdded', uploadAdded );
        uploader.bind( 'Error', uploadError );
        uploader.bind( 'UploadProgress', uploadProgress );
        uploader.bind( 'FileUploaded', uploadUploaded );
        uploader.bind( 'UploadComplete', uploadComplete );
        uploader.init();

        button = container.find('input[type=file]');
        callback();
    });
}

function uploadAdded( up ) {
    window.setTimeout(function() {
        elem.find('.joms-progressbar__progress').css({ width: 0 });
        up.refresh();
        up.start();
    }, 0);
}

function uploadError() {

}

function uploadProgress( up, file ) {
    var percent, bar;

    percent = Math.min( 100, Math.floor( file.loaded / file.size * 100 ) );
    bar = elem.find( '.joms-progressbar__progress' );
    bar.stop().animate({ width: percent + '%' });
}

function uploadUploaded( up, files, data ) {
    var json = {},
        cropper;

    // Parse json response.
    try {
        json = JSON.parse( data.response );
    } catch ( e ) {}

    result = json;

    if ( json.msg && !json.error ) {
        cropper = $('.joms-avatar__cropper');
        cropper.find('img').attr( 'src', json.msg );
        cropper.show();
        setTimeout(function() {
            var elem = cropper.find('img');
            joms.util.crop.detach();
            joms.util.crop( elem );
        }, 100 );
    }
}

function uploadComplete() {
    if ( ! result.error ) {
        elem.find('.joms-js--avatar-uploader-error').hide();
    } else {
        elem.find('.joms-js--avatar-uploader-error').html( result.msg ).show();
        elem.find('.joms-progressbar__progress').stop().animate({ width: '0%' });
    }
}

function buildHtml( json ) {
    json || (json = {});

    return [
        '<div class="joms-popup joms-popup--whiteblock">',
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
