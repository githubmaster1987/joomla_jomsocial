(function( root, factory ) {

    joms.popup || (joms.popup = {});
    joms.popup.event || (joms.popup.event = {});
    joms.popup.event['delete'] = factory( root );

    define([ 'utils/popup' ], function() {
        return joms.popup.event['delete'];
    });

})( window, function() {

var popup, elem, id;

function render( _popup, _id ) {
    if ( elem ) elem.off();
    popup = _popup;
    id = _id;

    joms.ajax({
        func: 'events,ajaxWarnEventDeletion',
        data: [ id ],
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

function save( e, step, action ) {
    var data, param;

    if ( step ) {
        data = [ id, step, action ];
    } else  {
        data = [ id, 1 ];
        param = elem.find('[name=recurring]:checked');
        data.push( param && param.length ? param.val() : '' );
    }

    joms.ajax({
        func: 'events,ajaxDeleteEvent',
        data: data,
        callback: function( json ) {
            var $ct;

            elem.find('.joms-js--step1').hide();
            elem.find('.joms-js--step2').show().children().first()
                .append( '<div>' + (json.error || json.message) + '</div>' );

            if ( json.next ) {
                save( null, json.next, data[2] );
            } else if ( json.redirect ) {
                $ct = elem.find('.joms-js--step2');
                $ct.find('.joms-js--button-done')
                    .html( json.btnDone )
                    .on( 'click', function() {
                        window.location = json.redirect;
                    });

                $ct.find('.joms-popup__action').show();
                $ct.find('.joms-popup__content').removeClass('joms-popup__content--single');
            }
        }
    });
}


function buildHtml( json ) {
    var form, rad, i;

    json || (json = {});

    form = '';
    if ( json.radios && json.radios.length ) {
        form  = '<div><form style="margin:5px;padding:0">';
        for ( i = 0; i < json.radios.length; i++ ) {
            rad = json.radios[i];
            form += '<div><label> <input type="radio" name="recurring" value="' + rad[0] + '"' + (rad[2] ? ' checked' : '') + '> ';
            form += rad[1] + '</label></div>';
        }
        form += '</form></div>';
    }

    return [
        '<div class="joms-popup joms-popup--whiteblock">',
        '<div class="joms-popup__title"><button class="mfp-close" type="button" title="Close (Esc)">×</button>', json.title, '</div>',
        '<div class="joms-js--step1', ( json.error ? ' joms-popup__hide' : '' ), '">',
            '<div class="joms-popup__content">', json.html, form, '</div>',
            '<div class="joms-popup__action">',
            '<button class="joms-button--neutral joms-button--small joms-left joms-js--button-cancel">', json.btnCancel, '</button> &nbsp;',
            '<button class="joms-button--primary joms-button--small joms-js--button-save">', json.btnDelete, '</button>',
            '</div>',
        '</div>',
        '<div class="joms-js--step2', ( json.error ? '' : ' joms-popup__hide' ), '">',
            '<div class="joms-popup__content joms-popup__content--single">', ( json.error || '' ), '</div>',
            '<div class="joms-popup__action joms-popup__hide">',
            '<button class="joms-button--primary joms-js--button-done"></button>',
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
