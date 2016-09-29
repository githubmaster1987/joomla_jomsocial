(function( root, $, factory ) {

    joms.util || (joms.util = {});
    joms.util.photos = factory( root, $ );

})( window, joms.jQuery, function( window, $ ) {

var containers = [],
    maxHeightThreshold = 180;

$( window ).resize( joms._.debounce(function() {
    _rearrange();
}, 500 ));

function arrange( $ct ) {
    var $children = $ct.children(),
        width = $ct.width();

    $children.css({
        display: 'block',
        'float': 'left',
        margin: '1px 0',
        overflow: 'hidden',
        padding: 0,
        position: 'relative'
    }).each(function() {
        var $el = $( this );
        $el.data({
            width: $el.width(),
            height: $el.height()
        });
    });

    _arrange( width, $children );

    // Add to registered container.
    containers.push( $ct );
}

function _arrange( ctWidth, $children ) {
    var from = 0,
        len = $children.length;

    $children.each(function( index ) {
        var divider, data, height, i;

        divider = 0;
        for ( i = from; i <= index; i++ ) {
            data = $children.eq(i).data();
            divider += data.width / data.height;
        }

        height = ctWidth / divider;

        if ( height <= maxHeightThreshold ) {
            for ( i = from; i <= index; i++ ) {
                $children.eq( i ).find('img').css({ height: height });
            }
            from = index + 1;
        } else if ( i === len ) {
            for ( i = from; i <= len; i++ ) {
                $children.eq( i ).nextAll().andSelf()
                    .css({ height: maxHeightThreshold })
                    .find('img').css({ height: height });
            }
        }
    });

    // fix ff issue
    $children.css('border', '1px solid transparent');
    setTimeout(function() {
        $children.css('border', '');
    });
}

function _rearrange() {
    var i, $ct;

    for ( i = 0; i < containers.length; i++ ) {
        $ct = containers[i];
        _arrange( $ct.width(), $ct.children() );
    }
}

// Exports.
return {
    arrange: arrange
};

});
