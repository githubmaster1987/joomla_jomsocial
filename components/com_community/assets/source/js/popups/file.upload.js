(function( root, $, factory ) {

    joms.popup || (joms.popup = {});
    joms.popup.file || (joms.popup.file = {});
    joms.popup.file.upload = factory( root, $ );

    define([ 'utils/loadlib', 'utils/popup' ], function() {
        return joms.popup.file.upload;
    });

})( window, joms.jQuery, function( window, $ ) {

var popup, elem, type, id, uploader, uploaderUrl, uploaderButton, uploaderPreview, doReload;

function render( _popup, _type, _id ) {
    if ( elem ) elem.off();
    popup = _popup;
    type = _type;
    id = _id;

    joms.ajax({
        func: 'files,ajaxFileUploadForm',
        data: [ type, id ],
        callback: function( json ) {
            popup.items[0] = {
                type: 'inline',
                src: buildHtml( json )
            };

            popup.updateItemHTML();

            popup.st.callbacks || (popup.st.callbacks = {});
            popup.st.callbacks.close = function() {
                doReload && window.location.reload();
            };

            elem = popup.contentContainer;
            doReload = false;
            uploaderPreview = elem.find('.joms-js--upload-preview');

            elem.on( 'click', '.joms-js--btn-add', upload );
            elem.on( 'click', '.joms-js--btn-upload', uploadStart );
            elem.on( 'click', '.joms-js--btn-done', function() {
                doReload && window.location.reload();
            });

            // Init uploader upon render.
            uploadInit();
        }
    });
}

function upload() {
    uploadInit(function() {
        uploaderButton.click();
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

    uploaderUrl = joms.BASE_URL + elem.find('input[name=url]').val();

    joms.util.loadLib( 'plupload', function () {
        var container, button;

        container = $('<div id="joms-js--file-uploader" aria-hidden="true" style="width:1px; height:1px; overflow:hidden">').appendTo( document.body );
        button    = $('<button id="joms-js--file-uploader-button">').appendTo( container );
        uploader  = new window.plupload.Uploader({
            url: uploaderUrl,
            container: 'joms-js--file-uploader',
            browse_button: 'joms-js--file-uploader-button',
            runtimes: 'html5,html4',
            filters: [{ title: 'Document files', extensions: elem.find('input[name=filetype]').val() }],
            max_file_size: elem.find('input[name=maxfilesize]').val() + 'mb'
        });

        uploader.bind( 'FilesAdded', uploadAdded );
        uploader.bind( 'Error', uploadError );
        uploader.bind( 'UploadProgress', uploadProgress );
        uploader.bind( 'FileUploaded', uploadUploaded );
        uploader.bind( 'uploadComplete', uploadComplete );
        uploader.init();

        uploaderButton = container.find('input[type=file]');
        callback();
    });
}

function uploadAdded( up, files ) {
    var html = '',
        i;

    for ( i = 0; i < files.length; i++ ) {
        html += '<div class="joms-file--' + files[i].id + '" style="margin-bottom:5px">';
        html += '<div><strong>' + files[i].name + '</strong> <span>(' + Math.round( files[i].size / 1024 ) + ' KB)</span></div>';
        html += '<div class="joms-progressbar"><div class="joms-progressbar__progress" style="width:0%"></div></div>';
        html += '</div>';
    }

    uploaderPreview.find('.joms-js--upload-placeholder').remove();
    uploaderPreview.append( html );

    elem.find('.joms-js--btn-add').html( elem.find('.joms-js--btn-add').data('lang-more') ).css({ visibility: 'visible' });
    elem.find('.joms-js--btn-upload').show();
    elem.find('.joms-js--btn-done').hide();
}

function uploadError( up, info ) {
    uploaderPreview.find( '.joms-file--' + info.file.id ).remove();
    window.alert( info.message + ' (' + info.code + ')' );
}

function uploadStart() {
    elem.find('.joms-js--btn-add').css({ visibility: 'hidden' });
    elem.find('.joms-js--btn-upload').hide();
    elem.find('.joms-js--btn-done').hide();
    uploader.settings.url = uploaderUrl + '&type=' + type + '&id=' + id;
    uploader.refresh();
    uploader.start();
}

function uploadProgress( up, file ) {
    var percent, bar;
    percent = Math.min( 100, Math.floor( file.loaded / file.size * 100 ) );
    bar = elem.find( '.joms-file--' + file.id );
    bar = bar.find( '.joms-progressbar__progress' );
    bar.stop().animate({ width: percent + '%' });
}

function uploadUploaded( up, file, resp ) {
    var json = {},
        item;

    try {
        json = JSON.parse( resp.response );
    } catch (e) {}

    if ( json.error ) {
        uploader.stop();
        elem.find('.joms-js--btn-add').css({ visibility: 'hidden' });
        elem.find('.joms-js--btn-upload').hide();
        elem.find('.joms-js--btn-done').show();
        elem.find( '.joms-file--' + file.id ).nextAll().andSelf().remove();
        window.alert( json.msg );
        return;
    }

    if ( json.msg ) {
        item = elem.find( '.joms-file--' + file.id );
        item.css({ color: '#F00' });
    } else if ( json.id ) {
        doReload = true;
    }
}

function uploadComplete() {
    elem.find('.joms-js--btn-add').css({ visibility: 'visible' });
    elem.find('.joms-js--btn-upload').hide();
    elem.find('.joms-js--btn-done').show();
}

function buildHtml( json ) {
    json || (json = {});

    return [
        '<div class="joms-popup joms-popup--whiteblock joms-popup--500">',
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
