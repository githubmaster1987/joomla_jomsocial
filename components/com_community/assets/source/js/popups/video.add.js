(function( root, $, factory ) {

    joms.popup || (joms.popup = {});
    joms.popup.video || (joms.popup.video = {});
    joms.popup.video.add = factory( root, $ );

    define([ 'utils/loadlib', 'utils/popup' ], function() {
        return joms.popup.video.add;
    });

})( window, joms.jQuery, function( window, $ ) {

var popup, elem, uploader, uploaderButton, contextid, context;

function render( _popup, _contextid, _context ) {
    var data;

    if ( elem ) elem.off();
    popup = _popup;
    context = _context || false;
    contextid = _contextid || false;

    data = [];
    if ( contextid ) {
        data.push( context || '' );
        data.push( contextid || '' );
    }

    joms.ajax({
        func: 'videos,ajaxAddVideo',
        data: data,
        callback: function( json ) {
            popup.items[0] = {
                type: 'inline',
                src: buildHtml( json )
            };

            popup.updateItemHTML();

            elem = popup.contentContainer;

            elem.on( 'submit', '.joms-js--form-link', link );
            elem.on( 'click', '.joms-js--select-file', upload );
            elem.on( 'submit', '.joms-js--form-upload', uploadStart );

            // Init uploader upon render.
            uploadInit();
        }
    });
}

function link( e ) {
    e.preventDefault();
    var form = $( e.currentTarget ),
        rTrim = /^\s+|\s+$/g,
        url = form.find('[name=videoLinkUrl]'),
        cat = form.find('[name=category_id]'),
        urlVal = url.val().trim( rTrim, '' ),
        catVal = +cat.val(),
        btnSubmit = form.find('[type=submit]');

    url.siblings('[data-elem=form-warning]')[ urlVal ? 'hide' : 'show' ]();
    cat.siblings('[data-elem=form-warning]')[ catVal ? 'hide' : 'show' ]();

    if ( urlVal && catVal ) {
        form.removeAttr('onsubmit');
        btnSubmit.val('Linking...');
        btnSubmit.prop('disabled', true);
        elem.off( 'submit', '.joms-js--form-link' );
        setTimeout(function() {
           form.submit();
        }, 300 );
    }
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

        container = $('<div id="joms-js--videoupload-uploader" aria-hidden="true" style="width:1px; height:1px; overflow:hidden">').appendTo( document.body );
        button    = $('<button id="joms-js--videoupload-uploader-button">').appendTo( container );
        uploader  = new window.plupload.Uploader({
            url: 'index.php?option=com_community&view=videos&task=uploadvideo',
            filters: [{ title: 'Video files', extensions: '3g2,3gp,asf,asx,avi,flv,mov,mp4,mpg,rm,swf,vob,wmv,m4v' }],
            container: 'joms-js--videoupload-uploader',
            browse_button: 'joms-js--videoupload-uploader-button',
            runtimes: 'html5,html4',
            multi_selection: false
        });

        uploader.bind( 'FilesAdded', uploadAdded );
        uploader.bind( 'BeforeUpload', uploadBeforeUpload );
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
    if ( !(files && files.length) )
        return;

    var span = elem.find('.joms-js--select-file'),
        file = files[0],
        name = '<span>' + file.name + '</span>',
        size = file.size || 0,
        unit = 'Bytes';

    for ( var units = [ 'KB', 'MB', 'GB' ]; size >= 1000 && units.length; ) {
        unit = units.shift();
        size = Math.ceil( size / 1000 );
    }

    if ( size )
        name += ' <span>(' + size + ' ' + unit + ')</span>';

    span.html( name );
}

function uploadStart( e ) {
    e.preventDefault();
    var form = $( e.currentTarget ),
        rTrim = /^\s+|\s+$/g,
        title = form.find('[name=title]'),
        cat = form.find('[name=category_id]'),
        titleVal = title.val().trim( rTrim, '' ),
        catVal = +cat.val(),
        bar;

    title.siblings('[data-elem=form-warning]')[ titleVal ? 'hide' : 'show' ]();
    cat.siblings('[data-elem=form-warning]')[ catVal ? 'hide' : 'show' ]();

    if ( !titleVal || !catVal ) {
        return false;
    }

    bar = form.find('.joms-progressbar__progress');
    bar.css({ width: 0 });

    uploader.refresh();
    uploader.start();
}

function uploadBeforeUpload() {
    var raw = elem.find('.joms-js--form-upload').serializeArray(),
        params = {},
        i;

    for ( i = 0; i < raw.length; i++ ) {
        params[ raw[i].name ] = raw[i].value;
    }

    // Attach parameters to uploader.
    uploader.settings.multipart_params = params;
}

function uploadError( up, error ) {
    var message = 'Undefined error.';
    if ( error && error.code && error.message ) {
        message = '(' + error.code + ') ' + error.message;
    }

    elem.find('.joms-js--select-file').html('&nbsp;');
    uploader.splice();
    uploader.refresh();
    window.alert( message );
}

function uploadProgress( up, file ) {
    var percent, form, bar;

    percent = Math.min( 100, Math.floor( file.loaded / file.size * 100 ) );
    form = elem.find('.joms-js--form-upload');
    bar = form.find( '.joms-progressbar__progress' );
    bar.stop().animate({ width: percent + '%' });
}

function uploadUploaded( up, file, resp ) {
    var json = {};

    try {
        json = JSON.parse( resp.response );
    } catch (e) {}

    if ( json.status !== 'success' ){
        window.alert( json.message || 'Undefined error.' );
        return;
    }

    setTimeout(function() {
        window.alert( json.processing_str );
        popup.close();
    }, 1000 );
}

function uploadComplete() {
}

function buildHtml( json ) {
    json || (json = {});

    return [
        '<div class="joms-popup joms-popup--whiteblock joms-popup--videoupload">',
        '<div class="joms-popup__title"><button class="mfp-close" type="button" title="Close (Esc)">×</button>', json.title, '</div>',
        ( json.html ? json.html : '<div class="joms-popup__content joms-popup__content--single">' + json.error + '</div>' ),
        '</div>'
    ].join('');
}

// Exports.
return function( contextid, context ) {
    joms.util.popup.prepare(function( mfp ) {
        render( mfp, contextid, context );
    });
};

});
