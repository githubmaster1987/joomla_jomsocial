(function( root, factory ) {

    joms.popup || (joms.popup = {});
    joms.popup.location || (joms.popup.location = {});
    joms.popup.location.view = factory( root );

    define([ 'utils/popup' ], function() {
        return joms.popup.location.view;
    });

})( window, function() {

var popup, elem, id;

function render( _popup, _id ) {
    if ( elem ) elem.off();
    popup = _popup;
    id = _id;

    joms.ajax({
        func: 'activities,ajaxShowMap',
        data: [ id ],
        callback: function( json ) {
            // var latlng = json.latitude + ',' + json.longitude,
            //     location = json.location,
            //     src;

            // src = '//maps.googleapis.com/maps/api/staticmap?center=' + latlng +
            //     '&markers=color:red%7Clabel:S%7C' + latlng + '&zoom=14&size=600x350&maptype=roadmap';

            // popup.items[0] = {
            //     type: 'image',
            //     src: src,
            //     tError: '<a href="%url%">The image</a> could not be loaded.'
            // };

            // popup.st.image.titleSrc = function() {
            //     return location;
            // };

            // popup.st.callbacks = {
            //     imageLoadComplete: function() {
            //         var img = this.contentContainer.find('.mfp-img');
            //         img.wrap('<a href="//www.google.com/maps/@' + latlng + ',19z" target="_blank"></a>');
            //     }
            // };

            popup.items[0] = {
                type: 'inline',
                src: buildHtml( json )
            };

            popup.updateItemHTML();

            elem = popup.contentContainer;
        }
    });
}

/**
    var mfp = this.showPopup();
    joms.ajax({
        func: 'activities,ajaxShowMap',
        data: [ id ],
        callback: function( json ) {
            if ( json.error ) {
                mfp.close();
                alert( json.error );
                return;
            }

            var latlng = json.latitude + ',' + json.longitude,
                location = json.location,
                src;

            src = '//maps.googleapis.com/maps/api/staticmap?center=' + latlng +
                '&markers=color:red%7Clabel:S%7C' + latlng + '&zoom=14&size=600x350&maptype=roadmap';

            mfp.items[0] = {
                type: 'image',
                src: src,
                tError: '<a href="%url%">The image</a> could not be loaded.'
            };

            mfp.st.image.titleSrc = function() {
                return location;
            };

            mfp.st.callbacks = {
                imageLoadComplete: function() {
                    var img = this.contentContainer.find('.mfp-img');
                    img.wrap('<a href="//www.google.com/maps/@' + latlng + ',19z" target="_blank"></a>');
                }
            };

            mfp.updateItemHTML();
        }
    });
*/

function buildHtml( json ) {
    var latlng, location, src;

    json || (json = {});

    latlng = json.latitude + ',' + json.longitude;
    location = json.location;
    src = '//maps.googleapis.com/maps/api/staticmap?center=' + latlng +
        '&markers=color:red%7Clabel:S%7C' + latlng + '&zoom=14&size=600x350&maptype=roadmap';

    return [
        '<div class="joms-popup joms-popup--location-view">',
        '<div', ( json.error ? ' class="joms-popup__hide"' : '' ), '>',
            '<a href="//www.google.com/maps/@', latlng, ',19z" target="_blank">',
            '<img src="', src, '">',
            '</a>',
        '</div>',
        '<div', ( json.error ? '' : ' class="joms-popup__hide"' ), '>',
            '<div class="joms-popup__content joms-popup__content--single">', json.error, '</div>',
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
