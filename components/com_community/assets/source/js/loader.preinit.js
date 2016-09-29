;(function( root ) {

/**
 * Application global object.
 * @namespace joms
 */
if ( typeof root.joms !== 'object' ) {
    root.joms = {};
}

/**
 * Debug flag.
 * @name joms.DEBUG
 * @const {boolean}
 */
root.joms.DEBUG = true; // @@release@@root.joms.DEBUG = false;@@

/**
 * Application logger.
 * @function joms.log
 * @param {mixed} data
 */
root.joms.log = root.joms.info = root.joms.warn = function( data ) {
    if ( root.joms.DEBUG && root.console && root.console.log ) { // @@release@@@@
        root.console.log( 'JOMS: ', data ); // @@release@@@@
    } // @@release@@@@
};

// Temporary variables to reserve some current application libraries in case of override.
root.joms_cache_$LAB = root.$LAB;
root.joms_cache_Hammer = root.Hammer;

})( this );
