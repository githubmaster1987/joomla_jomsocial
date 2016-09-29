(function( root, $, factory ) {

    joms.view || (joms.view = {});
    joms.view.stream = factory( root, $ );

    define([ 'popups/stream' ], function() {
        return joms.view.stream;
    });

})( window, joms.jQuery, function( window, $ ) {

var container;

function initialize() {
    uninitialize();
    container = $('.joms-stream__wrapper');
}

function uninitialize() {
    if ( container ) {
        container.off();
    }
}

function like( id ) {
    joms.ajax({
        func: 'system,ajaxStreamAddLike',
        data: [ id ],
        callback: function( json ) {
            var item, btn, info, counter;

            if ( json.success ) {
                item = container.find( '.joms-js--stream-' + id );
                if ( item.length ) {
                    btn = item.find('.joms-stream__actions').find('.joms-button--liked');
                    btn.attr( 'onclick', 'joms.api.streamUnlike(\'' + id + '\');' );
                    btn.addClass('liked');
                    btn.find('span').html( btn.data('lang-unlike') );
                    btn.find('use').attr( 'xlink:href', window.location + '#joms-icon-thumbs-down' );

                    info = item.find('.joms-stream__status');
                    if ( !json.html ) {
                        info.remove();
                    } else if ( info.length ) {
                        info.html( json.html );
                    } else {
                        info = item.find('.joms-stream__actions');
                        info = $('<div class=joms-stream__status />').insertAfter( info );
                        info.html( json.html );
                    }

                    counter = item.find('.joms-stream__status--mobile');
                    if ( counter.length ) {
                        counter = counter.find( '.joms-like__counter--' + id );
                        counter.html( +counter.eq(0).text() + 1 );
                    }
                }
            }
        }
    });
}

function unlike( id ) {
    joms.ajax({
        func: 'system,ajaxStreamUnlike',
        data: [ id ],
        callback: function( json ) {
            var item, btn, info, counter;

            if ( json.success ) {
                item = container.find( '.joms-js--stream-' + id );
                if ( item.length ) {
                    btn = item.find('.joms-stream__actions').find('.joms-button--liked');
                    btn.attr( 'onclick', 'joms.api.streamLike(\'' + id + '\');' );
                    btn.removeClass('liked');
                    btn.find('span').html( btn.data('lang-like') );
                    btn.find('use').attr( 'xlink:href', window.location + '#joms-icon-thumbs-up' );

                    info = item.find('.joms-stream__status');
                    if ( !json.html ) {
                        info.remove();
                    } else if ( info.length ) {
                        info.html( json.html );
                    } else {
                        info = item.find('.joms-stream__actions');
                        info = $('<div class=joms-stream__status />').insertAfter( info );
                        info.html( json.html );
                    }

                    counter = item.find('.joms-stream__status--mobile');
                    if ( counter.length ) {
                        counter = counter.find( '.joms-like__counter--' + id );
                        counter.html( +counter.eq(0).text() - 1 );
                    }
                }
            }
        }
    });
}

function edit( id ) {
    var $stream   = $( '.joms-js--stream-' + id ).eq(0),
        $sbody    = $stream.find('.joms-stream__body'),
        $scontent = $sbody.find('[data-type=stream-content]'),
        $seditor  = $sbody.find('[data-type=stream-editor]'),
        $textarea = $seditor.find('textarea'),
        origValue = $textarea.val();

    $scontent.hide();
    $seditor.show();
    $textarea.removeData('joms-tagging');
    $textarea.jomsTagging();
    $textarea.off( 'reset.joms-tagging' );
    $textarea.on( 'reset.joms-tagging', function() {
        $seditor.hide();
        $scontent.show();
        $textarea.val( origValue );
    });

    $textarea.focus();
}

function editSave( id, text, origText ) {

    // Don't send empty message.
    if ( text.replace( /^\s+|\s+$/g, '' ) === '' ) {
        return;
    }

    joms.ajax({
        func: 'activities,ajaxSaveStatus',
        data: [ id, text ],
        callback: function( json ) {
            var $stream   = $('.joms-stream').filter('[data-stream-id=' + id + ']'),
                $sbody    = $stream.find('.joms-stream__body'),
                $scontent = $sbody.find('[data-type=stream-content]'),
                $seditor  = $sbody.find('[data-type=stream-editor]'),
                $textarea = $seditor.find('textarea');

            if ( json.success ) {
                $scontent.html( '<span>' + json.data + '</span>' );
                $textarea.val( json.unparsed );
            } else {
                $textarea.val( origText );
            }

            $seditor.hide();
            $scontent.show();
        }
    });
}

function save( id, el ) {
    var $stream   = $( el ).closest('.joms-js--stream'),
        $sbody    = $stream.find('.joms-stream__body'),
        $seditor  = $sbody.find('[data-type=stream-editor]'),
        $textarea = $seditor.find('textarea'),
        value     = $textarea.val();

    if ($textarea[0].joms_hidden) {
        value = $textarea[0].joms_hidden.val();
    }

    editSave( id, value, value );
}

function cancel( id ) {
    var $stream   = $( '.joms-js--stream-' + id ),
        $sbody    = $stream.find('.joms-stream__body'),
        $scontent = $sbody.find('[data-type=stream-content]'),
        $seditor  = $sbody.find('[data-type=stream-editor]');

    $seditor.hide();
    $scontent.show();
}

function editLocation( id ) {
    joms.popup.stream.editLocation( id );
}

function remove( id ) {
    joms.popup.stream.remove( id );
}

function removeLocation( id ) {
    joms.popup.stream.removeLocation( id );
}

function removeMood( id ) {
    joms.popup.stream.removeMood( id );
}

function removeTag( id ) {
    joms.ajax({
        func: 'activities,ajaxRemoveUserTag',
        data: [ id, 'post' ],
        callback: function( json ) {
            var $stream, $sbody, $soptions, $scontent, $seditor, $textarea;

            if ( json.success ) {
                $stream   = $( '.joms-js--stream-' + id );
                $sbody    = $stream.find('.joms-stream__body');
                $soptions = $stream.find('.joms-list__options').find('.joms-dropdown').find('.joms-js--contextmenu-removetag');
                $scontent = $sbody.find('[data-type=stream-content]');
                $seditor  = $sbody.find('[data-type=stream-editor]');
                $textarea = $seditor.find('textarea');

                $scontent.html( '<span>' + json.data + '</span>' );
                $textarea.val( json.unparsed );
                $soptions.remove();
            }
        }
    });
}

function selectPrivacy( id ) {
    joms.popup.stream.selectPrivacy( id );
}

function share( id ) {
    joms.popup.stream.share( id );
}

function hide( streamId, userId ) {
    joms.ajax({
        func: 'activities,ajaxHideStatus',
        data: [ streamId, userId ],
        callback: function( json ) {
            var streams;

            if ( json.success ) {
                streams = container.find('.joms-stream[data-stream-id=' + streamId + ']');
                streams.fadeOut( 500, function() {
                    streams.remove();
                });
            }
        }
    });
}


function ignoreUser( id ) {
    joms.popup.stream.ignoreUser( id );
}

function showLikes( id, target ) {
    if ( target === 'popup' ) {
        joms.popup.stream.showLikes( id, target );
        return;
    }

    joms.ajax({
        func: 'system,ajaxStreamShowLikes',
        data: [ id ],
        callback: function( json ) {
            var streams;
            if ( json.success ) {
                streams = container.find('.joms-stream[data-stream-id=' + id + ']');
                streams.find('.joms-stream__status').html( json.html || '' );
            }
        }
    });
}

function showComments( id, type ) {
    joms.popup.stream.showComments( id, type );
}

function showOthers( id ) {
    joms.popup.stream.showOthers( id );
}

function report( id ) {
    joms.popup.stream.report( id );
}

function addFeatured( id ) {
    joms.popup.stream.addFeatured( id );
}

function removeFeatured( id ) {
    joms.popup.stream.removeFeatured( id );
}

function toggleText( id ) {
    var $text = $( '.joms-js--stream-text-' + id ),
        $full = $( '.joms-js--stream-textfull-' + id ),
        $btn  = $( '.joms-js--stream-texttoggle-' + id );

    if ( $full.is(':visible') ) {
        $full.hide();
        $text.show();
        $btn.html( $btn.data('lang-more') );
    } else {
        $text.hide();
        $full.show();
        $btn.html( $btn.data('lang-less') );
    }
}

// Exports.
return {
    start: initialize,
    stop: uninitialize,
    like: like,
    unlike: unlike,
    edit: edit,
    save: save,
    cancel: cancel,
    editLocation: editLocation,
    remove: remove,
    removeLocation: removeLocation,
    removeMood: removeMood,
    removeTag: removeTag,
    selectPrivacy: selectPrivacy,
    share: share,
    hide: hide,
    ignoreUser: ignoreUser,
    showLikes: showLikes,
    showComments: showComments,
    showOthers: showOthers,
    report: report,
    toggleText: toggleText,
    addFeatured: addFeatured,
    removeFeatured: removeFeatured
};

});
