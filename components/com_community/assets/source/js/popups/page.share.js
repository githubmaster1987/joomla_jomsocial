(function( root, $, factory ) {

    joms.popup || (joms.popup = {});
    joms.popup.page || (joms.popup.page = {});
    joms.popup.page.share = factory( root, $ );

    define([ 'utils/popup' ], function() {
        return joms.popup.page.share;
    });

})( window, joms.jQuery, function( window, $ ) {

var popup, elem, url;

function render( _popup, _url ) {
    var title, description, image;

    if ( elem ) elem.off();
    popup = _popup;
    url = _url;

    $.ajax({
        url: url,
        success: function( response ) {
            title = response.match(/<meta property="og:title" content="([^"]+)"/i) || false;
            title = title && title[1];
            description = response.match(/<meta property="og:description" content="([^"]+)"/i) || false;
            description = description && description[1];
            image = response.match(/<meta property="og:image" content="([^"]+)"/i) || false;
            image = image && image[1];
        },
        complete: function() {
            var params = [ url, title || '', description || '', image || '' ];
            joms.ajax({
                func: 'bookmarks,ajaxShowBookmarks',
                data: params,
                callback: function( json ) {
                    popup.items[0] = {
                        type: 'inline',
                        src: buildHtml( json )
                    };

                    popup.updateItemHTML();

                    elem = popup.contentContainer;
                    elem.on( 'click', '.joms-bookmarks a', openPopup );
                    elem.on( 'click', '.joms-js--button-cancel', cancel );
                    elem.on( 'click', '.joms-js--button-save', save );
                }
            });
        }
    });
}

function openPopup( e ) {
    var $a, url, title;

    e.preventDefault();
    e.stopPropagation();

    $a = $( this );
    url = $a.attr('href');
    title = $a.text();

    elem.off();
    popup.close();
    window.open( url, title, 'top=150, left=150, width=650, height=330, scrollbars=yes' );
}

function cancel() {
    elem.off();
    popup.close();
}

function save() {
    var $form = elem.find('form'),
        email = $form.find('[name=bookmarks-email]').val(),
        message = $form.find('[name=bookmarks-message]').val();

    joms.ajax({
        func: 'bookmarks,ajaxEmailPage',
        data: [ url, email, message ],
        callback: function( json ) {
            elem.find('.joms-js--step1').hide();
            elem.find('.joms-js--step2').show().children().html( json.error || json.message );
        }
    });
}

function buildHtml( json ) {
    json || (json = {});

    return [
        '<div class="joms-popup joms-popup--whiteblock joms-popup--500">',
        '<div class="joms-popup__title"><button class="mfp-close" type="button" title="Close (Esc)">×</button>', json.title, '</div>',
        '<div class="joms-js--step1">',
            '<div class="joms-popup__content">', json.html, '</div>',
            '<div class="joms-popup__action">',
            '<button class="joms-button--neutral joms-button--small joms-left joms-js--button-cancel">', json.btnCancel, '</button>',
            ( json.viaEmail ? ' &nbsp;<button class="joms-button--primary joms-button--small joms-js--button-save">' + json.btnShare + '</button>' : '' ),
            '</div>',
        '</div>',
        '<div class="joms-popup__hide joms-js--step2">',
            '<div class="joms-popup__content joms-popup__content--single"></div>',
        '</div>',
        '</div>'
    ].join('');
}

// Exports.
return function( url ) {
    joms.util.popup.prepare(function( mfp ) {
        render( mfp, url );
    });
};

});
