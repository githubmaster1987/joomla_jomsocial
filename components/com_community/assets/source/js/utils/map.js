define([ 'core' ], function() {

// Export as `joms.util.map`.
joms.util || (joms.util = {});
joms.util.map = function( callback ) {
    if ( typeof callback !== 'function' )
        return;

    if ( window.google && window.google.map && window.google.map.places ) {
        callback();
        return;
    }

    if ( joms.util.map.loading ) {
        joms.util.map.queue.push( callback );
        return;
    }

    joms.util.map.loading = true;
    joms.util.map.queue = [ callback ];
    joms.util.map.execQueue = function( status ) {
        while ( joms.util.map.queue.length ) ( joms.util.map.queue.shift() )( status );
        joms.util.map.loading = false;
    };

    joms.util.map.gmapcallback = function() {
        joms.util.map.execQueue();
    };

    var script  = document.createElement( 'script' );
    script.type = 'text/javascript';
    script.src  = 'https://maps.googleapis.com/maps/api/js?libraries=places' + ( window.joms_gmap_key ? ( '&key=' + window.joms_gmap_key ) : '' ) + '&callback=joms.util.map.gmapcallback';
    document.body.appendChild( script );
};

joms.util.map.nearbySearch = function( map, position, callback ) {
    var service = new window.google.maps.places.PlacesService( map );
    var request = {
        location: position,
        radius: 2000
    };

    service.nearbySearch( request, function( results, status ) {
        var items, i, loc;

        if ( status !== window.google.maps.places.PlacesServiceStatus.OK ) {
            callback({ error: 'Unable to find your nearest location.' });
            return;
        }

        if ( !results || !results.length ) {
            callback({ error: 'Unable to find your nearest location.' });
            return;
        }

        items = [];
        for ( i = 0, loc; i < results.length; i++ ) {
            loc = results[i];
            items.push({
                lat: loc.geometry.location.lat(),
                lng: loc.geometry.location.lng(),
                name: loc.name,
                vicinity: loc.vicinity
            });
        }

        callback(items);
    });
};

});
