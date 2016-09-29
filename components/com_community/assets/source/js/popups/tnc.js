(function( root, factory ) {

    joms.popup || (joms.popup = {});
    joms.popup.tnc = factory( root );

})( window, function() {

var popup;

function render( _popup ) {
    popup = _popup;

    joms.ajax({
        func: 'register,ajaxShowTnc',
        data: [ 0 ],
        callback: function( json ) {
            popup.items[0] = {
                type: 'inline',
                src: buildHtml( json )
            };

            popup.updateItemHTML();
        }
    });
}

function buildHtml( json ) {
    json || (json = {});

    return [
        '<div class="joms-popup joms-popup--whiteblock">',
        '<div class="joms-popup__title"><button class="mfp-close" type="button" title="Close (Esc)">×</button>', json.title, '</div>',
        '<div class="joms-popup__content joms-popup__content--single" style="max-height:400px;overflow:auto;">', ( json.html || '&nbsp;' ), '</div>',
        '</div>'
    ].join('');
}

// Exports.
return function() {
    joms.util.popup.prepare(function( mfp ) {
        render( mfp );
    });
};

});
