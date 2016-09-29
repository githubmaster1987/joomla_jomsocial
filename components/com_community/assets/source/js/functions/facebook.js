(function( root, $, factory ) {

    joms.fn || (joms.fn = {});
    joms.fn.facebook = factory( root, $ );

    define(function() {
        return joms.fn.facebook;
    });

})( window, joms.jQuery, function() {

function update() {
    joms.ajax({
        func: 'connect,ajaxUpdate',
        data: [ '' ],
        callback: function( json ) {
            console.log( json );
        }
    });
}

// Exports.
return {
    update: update
};

});
