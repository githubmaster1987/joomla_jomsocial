(function( root ) {

function S() {
    this.init();
};

S.prototype.init = function() {
    this.$ct = $('.joms-js--mod-search-nearbyevents');
    this.$location = this.$ct.find('.joms-js--location');
    this.$search = this.$ct.find('.joms-js--btn-search');
    this.$autodetect = this.$ct.find('.joms-js--btn-autodetect');
    this.$loading = this.$ct.find('.joms-js--loading');
    this.$result = this.$ct.find('.joms-js--result').hide();

    this.$search.on( 'click', $.proxy( this.search, this ) );
    this.$autodetect.on( 'click', $.proxy( this.autodetect, this ) );
};

S.prototype.search = function() {
    var location = $.trim( this.$location.val() ),
        json = {},
        url;

    // Do not send empty location.
    if ( !location ) {
        this.$loading.hide();
        return;
    }

    url = root.joms_mod_community_nearbyevents_url || '';
    url = url.replace( '___location___', location );

    this.$result.hide();
    this.$loading.show();

    $.ajax({
        url: url,
        type: 'GET',
        dataType: 'json',
        success: function( resp ) {
            if ( resp ) json = resp;
        },
        complete: $.proxy(function() {
            this.$loading.hide();

            if ( json.success ) {
                this.$result.html( json.html );
                this.$result.show();
            } else {
                this.$result.hide();
                this.$result.empty();
            }
        }, this )
    });
};

S.prototype.autodetect = function() {
    this.mapload( $.proxy( this.autodetectCallback, this ) );
};

S.prototype.autodetectCallback = function() {
    if ( !navigator.geolocation ) {
        alert('Sorry, your browser does not support this feature.');
        return;
    }

    this.$result.hide();
    this.$loading.show();

    navigator.geolocation.getCurrentPosition( $.proxy(function( location ) {
        var lat = location.coords.latitude,
            lng = location.coords.longitude,
            latLng = new google.maps.LatLng(lat, lng),
            geocoder = new google.maps.Geocoder(),
            location;

        geocoder.geocode({ latLng: latLng }, $.proxy(function( results, status ) {
            if ( status !== google.maps.GeocoderStatus.OK ) {
                alert( 'Geocoder failed due to: ' + status );
                this.$loading.hide();
                return;
            }

            if ( results && results.length ) {
                location = results[0].formatted_address;
                this.$location.val( location );
                this.search();
            }
        }, this ) );
    }, this ) );
};

S.prototype.mapload = function( callback ) {
    var script;

    if ( typeof callback !== 'function' ) {
        return;
    }

    if ( root.google && root.google.maps && root.google.maps.places ) {
        callback();
        return;
    }

    root.joms_mod_community_nearbyevents_cb = function() {
        callback();
        root.joms_mod_community_nearbyevents_cb = undefined;
    }

    script = document.createElement( 'script' );
    script.type = 'text/javascript';
    script.src = '//maps.googleapis.com/maps/api/js?libraries=places&callback=joms_mod_community_nearbyevents_cb';
    document.body.appendChild( script );
};

// jQuery cache;
var $;

// Initialize on DOM ready event.
var timer = setInterval(function() {
    if ( root.jQuery ) {
        clearInterval( timer );
        $ = root.jQuery;
        $(function() {
            new S();
        });
    }
}, 1000 );

})( window );
