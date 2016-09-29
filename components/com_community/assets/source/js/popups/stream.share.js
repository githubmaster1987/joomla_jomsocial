(function( root, $, factory ) {

    joms.popup || (joms.popup = {});
    joms.popup.stream || (joms.popup.stream = {});
    joms.popup.stream.share = factory( root, $ );

    define([ 'utils/loadlib', 'utils/popup' ], function() {
        return joms.popup.stream.share;
    });

})( window, joms.jQuery, function( window, $ ) {

var popup, elem, id;

function render( _popup, _id ) {
    if ( elem ) elem.off();
    popup = _popup;
    id = _id;

    joms.ajax({
        func: 'activities,ajaxSharePopup',
        data: [ id ],
        callback: function( json ) {
            popup.items[0] = {
                type: 'inline',
                src: buildHtml( json )
            };

            popup.updateItemHTML();

            initPhotoArranger();
            initVideoPlayers();

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
    var attachment = {
        msg: elem.find('textarea.joms-textarea').val(),
        privacy: elem.find('[data-ui-object=joms-dropdown-value]').val()
    };

    joms.ajax({
        func: 'activities,ajaxAddShare',
        data: [ id, JSON.stringify( attachment ) ],
        callback: function( json ) {
            elem.off();
            popup.close();

            if ( json.success ) {
                $('.joms-stream__container').prepend( json.html );
                initPhotoArranger();
                initVideoPlayers();
            }
        }
    });
}

function initPhotoArranger() {
    var initialized = '.joms-js--initialized',
        $containers = $('.joms-media--images').not( initialized );

    $containers.each(function() {
        var $ct = $( this ),
            $imgs = $ct.find('img'),
            counter = 0;

        $imgs.each(function() {
            var $img = $( this );

            $('<img>').load(function() {
                counter++;
                if ( counter === $imgs.length ) {
                    $ct.siblings('.joms-media--loading').remove();
                    $ct.addClass( initialized.substr(1) );
                    $imgs.show();
                    joms.util.photos.arrange( $ct );
                }

            }).attr( 'src', $img.attr('src') );
        });
    });
}

function initVideoPlayers() {
    var initialized = '.joms-js--initialized',
        cssVideos = '.joms-js--video',
        videos = $( cssVideos ).not( initialized ).addClass( initialized.substr(1) );

    if ( !videos.length ) {
        return;
    }

    joms.loadCSS( joms.ASSETS_URL + 'vendors/mediaelement/mediaelementplayer.min.css' );
    videos.on( 'click.joms-video', cssVideos + '-play', function() {
        var $el = $( this ).closest( cssVideos );
        joms.util.video.play( $el, $el.data() );
    });

    if ( joms.ios ) {
        setTimeout(function() {
            videos.find( cssVideos + '-play' ).click();
        }, 2000 );
    }
}

function buildHtml( json ) {
    json || (json = {});

    return [
        '<div class="joms-popup joms-popup--whiteblock joms-popup--500">',
        '<div class="joms-popup__title"><button class="mfp-close" type="button" title="Close (Esc)">×</button>', json.title, '</div>',
        '<div>',
            '<div class="joms-popup__content joms-popup__content--single">', ( json.html || '' ), '</div>',
            '<div class="joms-popup__action">',
                '<button class="joms-button--neutral joms-button--small joms-left joms-js--button-cancel">', json.btnCancel, '</button> &nbsp;',
                '<button class="joms-button--primary joms-button--small joms-js--button-save">', json.btnShare, '</button> &nbsp;',
                '<div style="display:inline-block; position:relative;">',
                    '<div class="joms-button--privacy" data-ui-object="joms-dropdown-button" data-name="share-privacy">',
                        '<svg class="joms-icon" viewBox="0 0 16 16"><use xlink:href="#joms-icon-earth"></use></svg>',
                        '<input type="hidden" data-ui-object="joms-dropdown-value" value="10">',
                    '</div>',
                    '<ul class="joms-dropdown joms-dropdown--privacy" data-name="share-privacy">',
                        '<li data-classname="joms-icon-earth" data-value="10" style="white-space:nowrap">',
                            '<svg class="joms-icon" viewBox="0 0 16 16"><use xlink:href="#joms-icon-earth"></use></svg>',
                            ' <span>', window.joms_lang.COM_COMMUNITY_PRIVACY_PUBLIC, '</span>',
                        '</li>',
                        '<li data-classname="joms-icon-users" data-value="20" style="white-space:nowrap">',
                            '<svg class="joms-icon" viewBox="0 0 16 16"><use xlink:href="#joms-icon-users"></use></svg>',
                            ' <span>', window.joms_lang.COM_COMMUNITY_PRIVACY_SITE_MEMBERS, '</span>',
                        '</li>',
                        '<li data-classname="joms-icon-user" data-value="30" style="white-space:nowrap">',
                            '<svg class="joms-icon" viewBox="0 0 16 16"><use xlink:href="#joms-icon-user"></use></svg>',
                            ' <span>', window.joms_lang.COM_COMMUNITY_PRIVACY_FRIENDS, '</span>',
                        '</li>',
                        '<li data-classname="joms-icon-lock" data-value="40" style="white-space:nowrap">',
                            '<svg class="joms-icon" viewBox="0 0 16 16"><use xlink:href="#joms-icon-lock"></use></svg>',
                            ' <span>', window.joms_lang.COM_COMMUNITY_PRIVACY_ME, '</span>',
                        '</li>',
                    '</ul>',
                '</div>',
            '</div>',
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
