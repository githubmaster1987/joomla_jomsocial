(function( root, $, factory ) {

    joms.util || (joms.util = {});
    joms.util.field = factory( root, $ );

})( window, joms.jQuery, function( window, $ ) {

function _createFileWrapper() {
    return [
        '<div data-wrap="file" style="width:350px;max-width:100%;position:relative;overflow:hidden">',
            '<input type="text" class="joms-input" readonly="readonly" placeholder="', (window.joms_lang && window.joms_lang.COM_COMMUNITY_SELECT_FILE || 'Select file'), '.."',
                'style="margin-bottom:2px">',
        '</div>'
    ].join('');
}

function _extractFileName( path ) {
    var matches = path.match( /[^\\\/]+$/ );
    if ( matches && matches[0] ) {
        return matches[0];
    }

    return '';
}

// Exports.
return {
    file: function( $elems ) {
        $elems = $( $elems );
        $elems.each(function( i, $elem ) {
            var $wrapper;

            $elem = $( $elem );
            $wrapper = $elem.parent();

            if ( ! $wrapper.data('wrap') ) {
                $wrapper = $( _createFileWrapper() );
                $elem.before( $wrapper );
                $elem.hide();
                $elem.appendTo( $wrapper );
                $elem.css({
                    cursor: 'pointer',
                    position: 'absolute',
                    right: 0,
                    top: 0,
                    width: '100%',
                    height: '100%',
                    opacity: 0
                });
                $elem.show();
            }

            // On file selection.
            $elem.off( 'change.joms-file' );
            $elem.on( 'change.joms-file', function() {
                $wrapper.find('.joms-input').val( _extractFileName( $(this).val() ) );
            });

        });
    }
};

});
