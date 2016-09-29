(function( root, $, factory ) {

    joms.popup || (joms.popup = {});
    joms.popup.search || (joms.popup.search = {});
    joms.popup.search.save = factory( root, $ );

    define(function() {
        return joms.popup.search.save;
    });

})( window, joms.jQuery, function( window, $ ) {

var popup, elem, data;

function render( _popup, _data ) {
    var json, keys, key, values, value, i;

    if ( elem ) elem.off();
    popup = _popup;
    data = _data || {};
    json = data.json || {};
    keys = ( data.keys || '' ).split(',');
    values = [];

    for ( i = 0; i < keys.length; i++ ) {
        key = keys[i];

        if (( json['fieldType' + key] === 'date' ) || ( json['fieldType' + key] === 'birthdate' ) || ( json['condition' + key] === 'between' )) {
            value = json['value' + key] + ',' + json['value' + key + '_2'];
        } else {
            value = json['value' + key];
        }

        values[i] = [
            'field=' + json[ 'field' + key ] + ',' +
            'condition=' + json[ 'condition' + key ] + ',' +
            'fieldType=' + json[ 'fieldType' + key ] + ',' +
            'value=' + value
        ];
    }

    joms.ajax({
        func: 'memberlist,ajaxShowSaveForm',
        data: [ data.operator, data.avatar_only ? 1 : 0 ].concat( values ),
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
    var $title = elem.find('[name=title]'),
        $description = elem.find('[name=description]'),
        error = false;

    if ( !$.trim( $title.val() ) ) {
        $title.siblings('.joms-help').show();
        error = true;
    } else {
        $title.siblings('.joms-help').hide();
    }

    if ( !$.trim( $description.val() ) ) {
        $description.siblings('.joms-help').show();
        error = true;
    } else {
        $description.siblings('.joms-help').hide();
    }

    if ( error ) {
        return;
    }

    elem.find('form').submit();
}

function buildHtml( json ) {
    json || (json = {});

    return [
        '<div class="joms-popup joms-popup--whiteblock">',
        '<div class="joms-popup__title"><button class="mfp-close" type="button" title="Close (Esc)">×</button>', json.title, '</div>',
        '<div class="joms-js--step1', ( json.error ? ' joms-popup__hide' : '' ), '">',
            '<div class="joms-popup__content">', json.html, '</div>',
            '<div class="joms-popup__action">',
            '<button class="joms-button--neutral joms-button--small joms-left joms-js--button-cancel">', json.btnCancel, '</button> &nbsp;',
            '<button class="joms-button--primary joms-button--small joms-js--button-save">', json.btnSave, '</button>',
            '</div>',
        '</div>',
        '<div class="joms-js--step2', ( json.error ? '' : ' joms-popup__hide' ), '">',
            '<div class="joms-popup__content joms-popup__content--single">', ( json.error || '' ), '</div>',
        '</div>',
        '</div>'
    ].join('');
}

// Exports.
return function( data ) {
    joms.util.popup.prepare(function( mfp ) {
        render( mfp, data );
    });
};

});

