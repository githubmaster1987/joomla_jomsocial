(function( root, $, IS_DESKTOP, factory ) {

    joms.util || (joms.util = {});
    joms.util.crop = factory( root, $, IS_DESKTOP );

})( window, joms.jQuery, !joms.mobile, function( window, $, IS_DESKTOP ) {

var cropper, wrapper, hammertime, measurements, resizeDirection;

function Cropper( elem ) {
    return Cropper.attach( elem );
}

Cropper.init = function() {
    wrapper || ( wrapper = $('<div class="joms-cropper__wrapper" />') );
    cropper || ( cropper = $('<div class="joms-cropper__box" />') );
};

Cropper.attach = function( elem ) {
    Cropper.init();
    Cropper.detach();
    reset();

    $( elem ).wrap( wrapper );

    // todo (rudy) change this hack
    wrapper = $( elem ).parent();

    cropper.insertAfter( elem );

    if ( !hammertime ) {
        hammertime = new joms.Hammer( cropper[0] );
        hammertime.on( 'touch drag release', function( e ) {
            e.stopPropagation();
            e.preventDefault();
            e.gesture.stopPropagation();
            e.gesture.preventDefault();

            if ( e.type === 'touch' ) {
                disableDesktopEvents();
                onTouch( e.gesture );
            } else if ( e.type !== 'release' ) {
                onDragOrResize( e.gesture );
            } else {
                onRelease( e.gesture );
                enableDesktopEvents();
            }
        });
    }

    enableDesktopEvents();

    return elem;
};

Cropper.detach = function() {
    Cropper.init();
    cropper.detach();
    wrapper.children().unwrap();
    wrapper.detach();
};

Cropper.getSelection = function() {
    var mea = measurements;

    return {
        x: mea.cropperLeft,
        y: mea.cropperTop,
        width: mea.cropperWidth,
        height: mea.cropperHeight
    };
};

function reset() {
    cropper.css({
        top: '',
        left: '',
        right: '',
        bottom: '',
        width: '',
        height: '',
        webkitTransform: '',
        mozTransform: '',
        transform: ''
    });
}

function measure() {
    var wrp = wrapper[0],
        img = wrapper.children('img'),
        pos = cropper.position();

    measurements = {
        imageWidth     : img.width(),
        imageHeight    : img.height(),
        wrapperTop     : wrp.scrollTop,
        wrapperLeft    : wrp.scrollLeft,
        wrapperWidth   : wrapper.width(),
        wrapperHeight  : wrapper.height(),
        cropperTop     : pos.top + wrp.scrollTop,
        cropperLeft    : pos.left + wrp.scrollLeft,
        cropperWidth   : cropper.outerWidth(),
        cropperHeight  : cropper.outerHeight()
    };
}

function onTouch( gesture ) {
    measure();
    resizeDirection = getResizeDirection( gesture );
}

var onDragOrResize = joms._.throttle(function( gesture ) {
    resizeDirection ? onResize( gesture ) : onDrag( gesture );
}, IS_DESKTOP ? 10 : 100 );

function onDrag( gesture ) {
    var mea = measurements,
        top = gesture.deltaY,
        left = gesture.deltaX,
        value;

    // Respect horizontal boundaries.
    left = Math.min( left, mea.imageWidth - mea.cropperWidth - mea.cropperLeft );
    left = Math.max( left, 0 - mea.cropperLeft );

    // Respect vertical boundaries.
    top = Math.min( top, mea.imageHeight - mea.cropperHeight - mea.cropperTop );
    top = Math.max( top, 0 - mea.cropperTop );

    value = 'translate3d(' + left + 'px, ' + top + 'px, 0)';

    cropper.css({
        webkitTransform: value,
        mozTransform: value,
        transform: value
    });
}

function onResize( gesture ) {
    var dir = resizeDirection,
        mea = measurements,
        css = {};

    if ( dir.match( /n/ ) ) {
        css.top    = 'auto';
        css.bottom = mea.wrapperHeight - mea.cropperTop - mea.cropperHeight;
        css.height = mea.cropperHeight - gesture.deltaY;
    } else if ( dir.match( /s/ ) ) {
        css.bottom = 'auto';
        css.top    = mea.cropperTop;
        css.height = mea.cropperHeight + gesture.deltaY;
    }

    if ( dir.match( /e/ ) ) {
        css.right = 'auto';
        css.left  = mea.cropperLeft;
        css.width = mea.cropperWidth + gesture.deltaX;
    } else if ( dir.match( /w/ ) ) {
        css.left  = 'auto';
        css.right = mea.wrapperWidth - mea.cropperLeft - mea.cropperWidth;
        css.width = mea.cropperWidth - gesture.deltaX;
    }

    // Restrict cropper box to 1:1 ratio.
    css.width = css.height = Math.max( css.width || 0, css.height || 0, 64 );

    // Respect vertical boundaries.
    if ( dir.match( /n/ ) ) {
        css.height = Math.min( css.height, mea.wrapperHeight - css.bottom );
    } else if ( dir.match( /s/ ) ) {
        css.height = Math.min( css.height, mea.imageHeight - css.top );
    } else if ( cropper[0].style.top !== 'auto' ) {
        css.height = Math.min( css.height, mea.imageHeight - parseInt( cropper.css('top') ) );
    } else {
        css.height = Math.min( css.height, mea.wrapperHeight - parseInt( cropper.css('bottom') ) );
    }

    // Respect horizontal boundaries.
    if ( dir.match( /e/ ) ) {
        css.width = Math.min( css.width, mea.imageWidth - css.left );
    } else if ( dir.match( /w/ ) ) {
        css.width = Math.min( css.width, mea.wrapperWidth - css.right );
    } else if ( cropper[0].style.left !== 'auto' ) {
        css.width = Math.min( css.width, mea.imageWidth - parseInt( cropper.css('left') ) );
    } else {
        css.width = Math.min( css.width, mea.wrapperWidth - parseInt( cropper.css('right') ) );
    }

    // Restrict cropper box to 1:1 ratio.
    css.width = css.height = Math.min( css.width, css.height );

    cropper.css( css );
}

function onRelease() {
    var pos = cropper.position(),
        mea = measurements;

    cropper.css({
        top: Math.max( pos.top + mea.wrapperTop, 0 ),
        left: Math.max( pos.left + mea.wrapperLeft, 0 ),
        right: '',
        bottom: '',
        webkitTransform: '',
        mozTransform: '',
        transform: ''
    });

    measure();
}

function getPointerPosition( pageX, pageY ) {
    var offset = cropper.offset();

    return {
        top  : pageY - offset.top,
        left : pageX - offset.left
    };
}

function getResizeDirection( gesture ) {
    var treshhold = IS_DESKTOP ? 15 : 20,
        pos = getPointerPosition( gesture.center.pageX, gesture.center.pageY ),
        mea = measurements,
        dir = '';

    if ( pos.top < treshhold ) {
        dir += 'n';
    } else if ( pos.top > mea.cropperHeight - treshhold ) {
        dir += 's';
    }

    if ( pos.left < treshhold ) {
        dir += 'w';
    } else if ( pos.left > mea.cropperWidth - treshhold ) {
        dir += 'e';
    }

    return dir;
}

function enableDesktopEvents() {
    if ( IS_DESKTOP ) {
        cropper.on( 'mousemove.joms-cropper', onMouseMove );
    }
}

function disableDesktopEvents() {
    if ( IS_DESKTOP ) {
        cropper.off( 'mousemove.joms-cropper' );
    }
}

function onMouseMove( e ) {
    var parentOffset = $( e.target ).parent().offset(),
        relX = e.pageX - parentOffset.left,
        relY = e.pageY - parentOffset.top,
        treshhold = 15,
        cursor = '',
        m;

    measure();
    m = measurements;

    if ( relY < m.cropperTop - m.wrapperTop + treshhold ) cursor += 'n';
    else if ( relY > m.cropperTop - m.wrapperTop + m.cropperHeight - treshhold ) cursor += 's';

    if ( relX < m.cropperLeft - m.wrapperLeft + treshhold ) cursor += 'w';
    else if ( relX > m.cropperLeft - m.wrapperLeft + m.cropperWidth - treshhold ) cursor += 'e';

    cropper.css({ cursor: cursor ? cursor + '-resize' : '' });
}

return Cropper;

});
