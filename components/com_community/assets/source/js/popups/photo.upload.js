(function( root, $, factory ) {

    joms.popup || (joms.popup = {});
    joms.popup.photo || (joms.popup.photo = {});
    joms.popup.photo.upload = factory( root, $ );

    define([ 'utils/loadlib', 'utils/popup' ], function() {
        return joms.popup.photo.upload;
    });

})( window, joms.jQuery, function( window, $ ) {

var popup, elem, uploader, uploaderButton, uploaderPreview, albumid, contextid, context, lang, files, newalbumid;

function render( _popup, _albumid, _contextid, _context ) {
    var data;

    if ( elem ) elem.off();
    popup = _popup;
    albumid = _albumid || false;
    context = _context || false;
    contextid = _contextid || false;

    data = [];
    data.push( albumid || '' );
    data.push( contextid || '' );
    data.push( context || '' );

    joms.ajax({
        func: 'photos,ajaxUploadPhoto',
        data: data,
        callback: function( json ) {
            popup.items[0] = {
                type: 'inline',
                src: buildHtml( json )
            };

            popup.updateItemHTML();

            lang = json.lang || {};

            elem = popup.contentContainer;
            uploaderPreview = elem.find('.joms-gallery');

            files = [];

            elem.on( 'click', '.joms-tab__bar a', changeTab );
            elem.on( 'click', '.joms-js--form-toggle', toggleForm );
            elem.on( 'click', '.joms-js--btn-create', createAlbum );
            elem.on( 'click', '.joms-js--btn-add', upload );
            elem.on( 'click', '.joms-js--btn-view', viewAlbum );

            // Init uploader upon render.
            uploadInit();
        }
    });
}

function changeTab( e ) {
    var $btncreate = elem.find('.joms-js--btn-create'),
        $btnadd = elem.find('.joms-js--btn-add'),
        $el = $( e.target ),
        href = $el.attr('href');

    if ( newalbumid || href.match(/select-album/) ) {
        $btncreate.hide();
        $btnadd.show();
    } else {
        $btnadd.hide();
        $btncreate.show();
    }
}

function toggleForm( state ) {
    var $btn = elem.find('.joms-js--form-toggle');

    if ( state !== 'show' && state !== 'hide' ) {
        state = $btn.data('hidden') ? 'show' : 'hide';
    }

    if ( state === 'show' ) {
        $btn.removeData('hidden');
        elem.find('.joms-js--thumbnails').hide();
        elem.find('.joms-js--form-detail').css({ height: '' });
    } else if ( state === 'hide' ) {
        $btn.data('hidden', 'hidden');
        elem.find('.joms-js--form-detail').css({ height: 0 });
        elem.find('.joms-js--thumbnails').show();
    }
}

function createAlbum() {
    var album = $.trim( elem.find('[name=name]').val() ),
        location = $.trim( elem.find('[name=location]').val() ),
        description = $.trim( elem.find('[name=description]').val() ),
        permission = elem.find('[name=permissions]').val(),
        $albumerrormsg = elem.find('[name=name]').siblings('.joms-help').hide(),
        $loading = elem.find('.joms-js--btn-create img');

    if ( !album.length ) {
        $albumerrormsg.show();
        return;
    }

    if ( $loading.is(':visible') ) {
        return;
    }

    $loading.show();

    joms.ajax({
        func: 'photos,ajaxCreateAlbum',
        data: [ album, contextid || '', context || '', location || '', description || '', permission || '' ],
        callback: joms._.debounce(function( json ) {
            $loading.hide();

            if ( json.error ) {
                window.alert( json.error );
                return;
            }

            if ( json.albumid ) {
                albumid = newalbumid = json.albumid;
                elem.find('.joms-js--btn-create').hide();
                elem.find('.joms-js--btn-add').show();

                // Disable input elements on new album tab.
                elem.find('#joms-js__new-album').find('input.joms-input, textarea.joms-textarea, select.joms-select')
                    .attr('disabled', 'disabled');

                toggleForm('hide');
            }
        }, 500 )
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

    joms.util.loadLib( 'plupload', function () {
        var container, button;

        container = $('<div id="joms-js--photoupload-uploader" aria-hidden="true" style="width:1px; height:1px; overflow:hidden">').appendTo( document.body );
        button    = $('<button id="joms-js--photoupload-uploader-button">').appendTo( container );
        uploader  = new window.plupload.Uploader({
            url: 'index.php?option=com_community&view=photos&task=multiUpload',
            filters: [{ title: 'Image files', extensions: 'jpg,jpeg,png,gif' }],
            container: 'joms-js--photoupload-uploader',
            browse_button: 'joms-js--photoupload-uploader-button',
            runtimes: 'html5,html4'
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
        html += '<li class="joms-gallery__item joms-file--' + files[i].id + '">';
        html += '<div class="joms-gallery__thumbnail"><img src="' + joms.ASSETS_URL + 'photo_thumb.png"></div>';
        html += '<div class="joms-gallery__body">';
        html += '<a class="joms-gallery__title">' + files[i].name + '</a> <span>(' + Math.round( files[i].size / 1024 ) + ' KB)</span>';
        html += '<div class="joms-progressbar"><div class="joms-progressbar__progress" style="width:0%"></div></div>';
        html += '</div>';
        html += '</li>';
    }

    uploaderPreview.append( html );

    elem.find('.joms-js--btn-add').css({ visibility: 'visible' });
    elem.find('.joms-js--btn-view').hide();

    setTimeout(function() {
        uploadStartProxy();
    }, 1000);
}

function uploadStartProxy() {
    var $album = elem.find('[name=name]');
    if ( !$album.is(':visible') ) {
        albumid = elem.find('[name=album-id]').val();
    } else if ( newalbumid ) {
        albumid = newalbumid;
    }

    uploadStart();
}

function uploadStart() {
    elem.find('.joms-js--btn-add').css({ visibility: 'hidden' });
    elem.find('.joms-js--btn-view').hide();
    uploader.settings.url = joms.BASE_URL + 'index.php?option=com_community&view=photos&task=multiUpload&albumid=' + albumid;
    uploader.refresh();
    uploader.start();
}

function uploadError() {
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
        if ( json.canContinue ) {
            elem.find('.joms-js--btn-add').css({ visibility: 'visible' });
        } else {
            elem.find('.joms-js--btn-add').css({ visibility: 'hidden' });
        }

        item = elem.find( '.joms-file--' + file.id );
        if ( item.prevAll().length ) {
            elem.find('.joms-js--btn-view').show();
        }
        item.nextAll().andSelf().remove();
        window.alert( json.msg );
        return;
    }

    if ( json.info ) {
        files.push({ photoId: json.photoId });
        item = elem.find( '.joms-file--' + file.id );
        item = item.find('img');
        item.attr( 'src', json.info );
        elem.find('.joms-js--btn-add').html( elem.find('.joms-js--btn-add').data('lang-more') );
        elem.find('.joms-js--btn-view').show();

        // Disable tabs and input elements if images are successfully uploaded.
        elem.off('click', '.joms-tab__bar a');
        elem.find('.joms-tab__bar a').removeAttr('href');
        elem.find('input.joms-input, textarea.joms-textarea, select.joms-select').attr('disabled', 'disabled');
    }
}

function uploadComplete() {
    elem.find('.joms-js--btn-add').css({ visibility: 'visible' });
    elem.find('.joms-js--btn-view').show();

    joms.ajax({
        func: 'photos,ajaxUpdateCounter',
        data: [ albumid, JSON.stringify({ files: files }) ],
        callback: function() {}
    });
}

function viewAlbum() {
    joms.ajax({
        func: 'photos,ajaxGetAlbumURL',
        data: [ albumid || '', contextid || '', context || '' ],
        callback: function( json ) {
            if ( json.url ) {
                window.location = json.url;
            }
        }
    });
}

function buildHtml( json ) {
    json || (json = {});

    return [
        '<div class="joms-popup joms-popup--whiteblock joms-popup--photoupload">',
        '<div class="joms-popup__title"><button class="mfp-close" type="button" title="Close (Esc)">×</button>', json.title, '</div>',
        json.html,
        '</div>'
    ].join('');
}

// Exports.
return function( albumid, contextid, context ) {
    joms.util.popup.prepare(function( mfp ) {
        render( mfp, albumid, contextid, context );
    });
};

});
