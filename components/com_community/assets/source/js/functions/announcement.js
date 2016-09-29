(function( root, $, factory ) {

    joms.fn || (joms.fn = {});
    joms.fn.announcement = factory( root, $ );

    define(function() {
        return joms.fn.announcement;
    });

})( window, joms.jQuery, function( window, $ ) {

function edit( groupid, id ) {
    $( '.joms-js--announcement-view-' + groupid + '-' + id ).hide();
    $( '.joms-js--announcement-edit-' + groupid + '-' + id ).show();
    $( '.joms-subnav,.joms-subnav--desktop' ).hide();
    $( '.joms-sidebar' ).hide();
    $( '.joms-main' ).css({ padding: 0, width: '100%' });
}

function editCancel( groupid, id ) {
    $( '.joms-js--announcement-edit-' + groupid + '-' + id ).hide();
    $( '.joms-js--announcement-view-' + groupid + '-' + id ).show();
    $( '.joms-subnav,.joms-subnav--desktop' ).css( 'display', '' );
    $( '.joms-main' ).css({ padding: '', width: '' });
    $( '.joms-sidebar' ).show();
}

// Exports.
return {
    edit: edit,
    editCancel: editCancel
};

});
