(function( root, $, factory ) {

    joms.popup || (joms.popup = {});
    joms.popup.stream || (joms.popup.stream = {});
    joms.popup.stream.editLocation = factory( root, $ );

    define([ 'utils/popup' ], function() {
        return joms.popup.stream.editLocation;
    });

})( window, joms.jQuery, function( window, $ ) {

var popup, elem, id;

function render( _popup, _id ) {
    if ( elem ) elem.off();
    popup = _popup;
    id = _id;

    joms.ajax({
        func: 'activities,ajaxeditLocation',
        data: [ id ],
        callback: function( json ) {
            popup.items[0] = {
                type: 'inline',
                src: buildHtml( json )
            };

            popup.updateItemHTML();

            initMap( json );

            elem = popup.contentContainer;
            elem.on( 'click', '[data-ui-object=popup-button-cancel]', cancel );
            elem.on( 'click', '[data-ui-object=popup-button-save]', save );
        }
    });
}

function cancel() {
    elem.off();
    popup.close();
}

function save() {
    var item = elem.find('.joms-js--location-label'),
        name = item.val(),
        lat  = item.data('lat'),
        lng  = item.data('lng');

    joms.ajax({
        func: 'activities,ajaxSaveLocation',
        data: [ id, name, lat, lng ],
        callback: function( json ) {
            var stream;

            elem.off();
            popup.close();

            if ( json.success ) {
                stream = $('.joms-stream').filter('[data-stream-id=' + id + ']');
                stream.find('.joms-status-location a').html( name );
            }
        }
    });
}

function initMap( json ) {
    joms.util.map(function() {
        var el, input, selector, position, options, map, marker;

        el = elem.find('.joms-js--location-map');
        input = elem.find('.joms-js--location-label');
        selector = elem.find('.joms-js--location-selector');

        input.val( json.location );

        position = new window.google.maps.LatLng( json.latitude, json.longitude );

        options = {
            center: position,
            zoom: 14,
            mapTypeId: window.google.maps.MapTypeId.ROADMAD,
            mapTypeControl: false,
            disableDefaultUI: true,
            draggable: false,
            scaleControl: false,
            scrollwheel: false,
            navigationControl: false,
            streetViewControl: false,
            disableDoubleClickZoom: true
        };

        map = new window.google.maps.Map( el[0], options );
        marker = new window.google.maps.Marker({
            draggable: false,
            map: map
        });

        marker.setPosition( position );
        map.panTo( position );

        joms.util.map.nearbySearch( map, position, function( results ) {
            var html, i;

            if ( !(results && results.length) ) {
                html = results.error || 'Undefined error.';
                html = '<span class="joms-map--location-item--notice">' + html + '</span>';
                selector.html( html );
            }

            html = [];
            for ( i = 0; i < results.length; i++ ) {
                html.push([
                    '<a class="joms-map--location-item" data-lat="', results[i].lat, '" data-lng="', results[i].lng, '"><strong>',
                    results[i].name, '</strong><br><span>', ( results[i].vicinity || '&nbsp;' ), '</span></a>'
                ].join(''));
            }

            selector.html( html.join('') );

        });

        selector.on( 'click', 'a', function() {
            var elem = $( this ),
                data = elem.data(),
                name = elem.find('strong').text(),
                position;

            if ( data.lat && data.lng ) {
                input.val( name );
                input.data( 'lat', data.lat );
                input.data( 'lng', data.lng );
                position = new window.google.maps.LatLng( data.lat, data.lng );
                marker.setPosition( position );
                map.panTo( position );
            }

        });

    });
}

function buildHtml( json ) {
    json || (json = {});

    return [
        '<div class="joms-popup joms-popup--whiteblock">',
        '<div class="joms-popup__title"><button class="mfp-close" type="button" title="Close (Esc)">×</button>', json.title, '</div>',
        '<div>',
            '<div class="joms-popup__content">', ( json.html || json.error ), '</div>',
            '<div class="joms-popup__action">',
            '<a href="javascript:" class="joms-button--neutral joms-button--small joms-left" data-ui-object="popup-button-cancel">', json.btnCancel, '</a> &nbsp;',
            '<button class="joms-button--primary joms-button--small" data-ui-object="popup-button-save">', json.btnEdit, '</button>',
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
