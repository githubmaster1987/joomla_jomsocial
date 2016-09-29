joms || (joms = {});
joms.map = {

    // callback queue
    queue: [],

    // google map proxy
    execute: function( callback ) {
        if ( window.google && window.google.maps && window.google.maps.places ) {
            callback();
            return;
        }

        if ( joms.map.loading ) {
            joms.map.queue.push( callback );
            return;
        }

        joms.map.loading = true;
        joms.map.queue.push( callback );
        joms.map.loadScript();
    },

    // google map library loader
    loadScript: function() {
        var script = document.createElement('script');
        script.type = 'text/javascript';
        script.src = 'https://maps.googleapis.com/maps/api/js?libraries=places' + ( window.joms_gmap_key ? ( '&key=' + window.joms_gmap_key ) : '' ) + '&callback=joms.map.loadScriptCallback';
        document.body.appendChild(script);
    },

    // callback for google map library loader
    loadScriptCallback: function() {
        if ( joms.map.queue && joms.map.queue.length )
            while ( joms.map.queue.length )
                ( joms.map.queue.shift() )();

        joms.map.loading = false;
    }

};
