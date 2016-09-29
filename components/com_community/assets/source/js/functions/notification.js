(function( root, $, factory ) {

    joms.fn || (joms.fn = {});
    joms.fn.notification = factory( root, $ );

    define(function() {
        return joms.fn.notification;
    });

})( window, joms.jQuery, function( window, $ ) {

var requests = [];

function updateCounter( type, id, count ) {
    var $el;

    id = type + '-' + id;

    // Prevent double/multiple request for one notification.
    if ( requests.indexOf( id ) >= 0 ) {
        return;
    }

    $el = $( '.joms-js--notiflabel-' + type );

    if ( requests.indexOf( id ) < 0 ) {
        requests.push( id );
        count = +$el.eq(0).text() + count;
        $el.html( count > 0 ? count : '' );
    }
}

// Exports.
return {
    updateCounter: updateCounter
};

});
