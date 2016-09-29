(function( root, $, factory ) {

    joms.util || (joms.util = {});
    joms.util.wysiwyg = factory( root, $ );

    define([ 'utils/loadlib' ], function() {
        return joms.util.wysiwyg;
    });

})( window, joms.jQuery, function( window, $ ) {

function start() {
    var editor = $('textarea').filter('[data-wysiwyg=trumbowyg]');

    if ( !editor.length ) {
        return;
    }

    joms.util.loadLib( 'trumbowyg', function() {
        trumbowygTranslate();

        // Check RTL.
        var isRTL = false;
        if ( $('html').attr('dir') === 'rtl' ) {
            isRTL = true;
        }

        // TODO: Set upload path.
        $.extend( jQuery.trumbowyg, {
            upload: {
                serverPath: joms.BASE_URL + 'index.php?option=com_community&view=photos&task=ajaxPreviewComment&isEditor=1'
            }
        });

        editor.each(function() {
            var btns, config, instance;

            btns = $( this ).data( 'btns' );
            btns = btns || 'viewHTML,|,bold,italic,underline,|,unorderedList,orderedList,|,link,image';
            btns = btns.split(',');

            config = {
                btnsDef: {
                    image: {
                        dropdown: [ 'insertImage', 'upload' ],
                        ico: 'insertImage'
                    }
                },
                btns: btns,
                fullscreenable: false,
                mobile: false,
                tablet: false,
                removeformatPasted: true,
                autogrow: true
            };

            if ( isRTL ) {
                config.dir = 'rtl';
            }

            instance = $( this ).trumbowyg( config )
                .on('tbwblur', function() {
                    var t = $( this ).data('trumbowyg');
                    t.syncCode();
                })
                .data('trumbowyg');

            // Override modal button render.
            instance.buildModalBtn = trumbowygBuildModalBtn;

            // Override modal input.
            instance._openModalInsert = instance.openModalInsert;
            instance.openModalInsert = function( title, fields, cmd ) {
                var modBox = instance._openModalInsert( title, fields, cmd );

                modBox.find('label').each(function() {
                    var label = $( this ),
                        input = label.find('input'),
                        name = input.attr('name'),
                        type = input.attr('type'),
                        html;

                    if ([ 'url', 'file', 'title', 'text', 'target', 'alt' ].indexOf( name ) >= 0 ) {
                        html  = '<div class="joms-form__group" style="text-align:left">';
                        html += '<span style="width:90px;text-align:center">' + label.find('.trumbowyg-input-infos').text() + '</span>';
                        if ( type === 'file' ) {
                            html += '<input name="' + name + '" class="joms-input" value="' + ( input.val() || '' ) + '" type="file"';
                            html += ' accept="image/png,image/jpeg,image/gif,image/bmp">';
                        } else {
                            html += '<input name="' + name + '" class="joms-input" value="' + ( input.val() || '' ) + '" type="' + ( type || 'text' ) + '">';
                        }
                        html += '</div>';
                        label.replaceWith( $(html) );
                    }
                });

                return modBox;
            };
        });
    });
}

function trumbowygBuildModalBtn( name, modal ) {
    return $('<button/>', {
        'class': 'joms-button--full-small joms-button--' + ( name === 'submit' ? 'primary' : 'neutral' ),
        'type': name,
        'text': this.lang[name] || name
    }).appendTo( modal.find('form') );
}

function trumbowygTranslate() {
    $.extend( jQuery.trumbowyg.langs.en, window.joms_lang.wysiwyg || {});
}

// Exports.
return {
    start: start
};

});
