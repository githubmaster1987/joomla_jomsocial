(function( root, $, factory ) {

    joms.view || (joms.view = {});
    joms.view.customize = factory( root, $ );

    define([ 'popups/stream' ], function() {
        return joms.view.stream;
    });

})( window, joms.jQuery, function(/* window, $ */) {

function initialize() {
    uninitialize();
}

function uninitialize() {
}

// Exports.
return {
    start: initialize,
    stop: uninitialize
};

});
