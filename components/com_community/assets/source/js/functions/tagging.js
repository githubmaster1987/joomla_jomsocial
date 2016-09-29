(function( root, $, factory ) {

    joms.fn || (joms.fn = {});
    joms.fn.tagging = factory( root, $ );

    define([ 'utils/tagging' ], function() {
        return joms.fn.tagging;
    });

})( window, joms.jQuery, function( window, $ ) {

var groupMembers = {},
    eventMembers = {},
    groupMembersFetching = {},
    eventMembersFetching = {},
    groupMembersFetchCallback = {},
    eventMembersFetchCallback = {};

function initInputbox() {
    var inputbox = $( document.body )
        .find('.joms-js--newcomment')
        .find('textarea.joms-textarea');

    inputbox.each(function() {
        var el = $( this );
        if ( !el[0].joms_beautifier ) {
            el[0].joms_data = el.data();
            el.jomsTagging( fetchInputbox );
        }
    });
}

function fetchInputbox( callback ) {
    var that = this,
        data = this.textarea.joms_data,
        id = data.tagId || data.id,
        func = ( data.tagFunc || data.func || '' ).toLowerCase(),
        friends = [],
        url;

    if ( this.textarea.joms_friends ) {
        callback( this.textarea.joms_friends );
        return;
    }

    if ( !func ) {
        url = 'index.php?option=com_community&view=friends&task=ajaxAutocomplete&type=comment&streamid=' + id;
        if ( window.joms_group_id ) {
            url += '&groupid=' + window.joms_group_id;
        } else if ( window.joms_event_id ) {
            url += '&eventid=' + window.joms_event_id;
        }
    } else {
        url = 'index.php?option=com_community&view=friends&task=ajaxAutocomplete';
        if ( func.indexOf('album') > -1 ) {
            url += '&albumid=' + id;
        } else if ( func.indexOf('photo') > -1 ) {
            url += '&photoid=' + id + '&rule=photo-comment';
        } else if ( func.indexOf('video') > -1 ) {
            url += '&videoid=' + id;
        } else if ( func.indexOf('discussion') > -1 ) {
            url += '&discussionid=' + id;
        } else if ( func.indexOf('inbox') > -1 ) {
            url += '&msgid=' + id;
        }
    }

    this.fetchXHR && this.fetchXHR.abort();
    this.fetchXHR = $.ajax({
        url: joms.BASE_URL + url,
        dataType: 'json',
        success: function( json ) {
            that.textarea.joms_friends = friends = _parse( json );
        },
        complete: function() {
            var i, j, ilen, jlen;

            // Update (posibbly) old images and names.
            if ( friends.length && window.joms_friends.length ) {
                for ( i = 0, ilen = Math.min( friends.length, 30 ); i < ilen; i++ ) {
                    for ( j = 0, jlen = Math.min( window.joms_friends.length, 30 ); j < jlen; j++ ) {
                        if ( +friends[i].id === +window.joms_friends[j].id ) {
                            window.joms_friends[j].avatar = friends[i].avatar;
                            window.joms_friends[j].name = friends[i].name;
                        }
                    }
                }
            }

            that.fetchXHR = false;
            callback( friends );
        }
    });
}

function fetchFriendsInContext() {
    var url  = 'index.php?option=com_community&view=friends&task=ajaxAutocomplete',
        friends = [];

    if ( window.joms_group_id ) {
        url += '&groupid=' + window.joms_group_id;
    } else if ( window.joms_event_id ) {
        url += '&eventid=' + window.joms_event_id;
    } else {
        url += '&allfriends=1';
    }

    joms.jQuery.ajax({
        url: joms.BASE_URL + url,
        dataType: 'json',
        success: function( json ) {
            friends = _parse( json );
        },
        complete: function() {
            window.joms_friends = friends;
        }
    });
}

function fetchGroupMembers( groupid, callback ) {
    var url  = 'index.php?option=com_community&view=friends&task=ajaxAutocomplete&groupid=' + groupid;

    if ( !groupMembersFetchCallback[ groupid ] ) {
        groupMembersFetchCallback[ groupid ] = [];
    }

    if ( groupMembersFetching[ groupid ] ) {
        groupMembersFetchCallback[ groupid ].push( callback );
        return;
    }

    if ( groupMembers[ groupid ] ) {
        callback( groupMembers[ groupid ] );
        return;
    }

    groupMembersFetching[ groupid ] = true;
    joms.jQuery.ajax({
        url: joms.BASE_URL + url,
        dataType: 'json',
        success: function( json ) {
            groupMembers[ groupid ] = _parse( json );
        },
        complete: function() {
            callback( groupMembers[ groupid ] );
            while ( groupMembersFetchCallback[ groupid ].length ) {
                try {
                    ( groupMembersFetchCallback[ groupid ].shift() )( groupMembers[ groupid ] );
                } catch (e) {}
            }
            groupMembersFetching[ groupid ] = false;
        }
    });
}

function fetchEventMembers( eventid, callback ) {
    var url  = 'index.php?option=com_community&view=friends&task=ajaxAutocomplete&eventid=' + eventid;

    if ( !eventMembersFetchCallback[ eventid ] ) {
        eventMembersFetchCallback[ eventid ] = [];
    }

    if ( eventMembersFetching[ eventid ] ) {
        eventMembersFetchCallback[ eventid ].push( callback );
        return;
    }

    if ( eventMembers[ eventid ] ) {
        callback( eventMembers[ eventid ] );
        return;
    }

    eventMembersFetching[ eventid ] = true;
    joms.jQuery.ajax({
        url: joms.BASE_URL + url,
        dataType: 'json',
        success: function( json ) {
            eventMembers[ eventid ] = _parse( json );
        },
        complete: function() {
            callback( eventMembers[ eventid ] );
            while ( eventMembersFetchCallback[ eventid ].length ) {
                try {
                    ( eventMembersFetchCallback[ eventid ].shift() )( eventMembers[ eventid ] );
                } catch (e) {}
            }
            eventMembersFetching[ eventid ] = false;
        }
    });
}

function _parse( json ) {
    var uniques = [],
        friends = [],
        id, i;

    if ( json && json.suggestions && json.suggestions.length ) {
        for ( i = 0; i < json.suggestions.length; i++ ) {
            id = '' + json.data[i];
            if ( uniques.indexOf(id) >= 0 ) continue;
            uniques.push( id );
            friends.push({
                id: id,
                name: json.suggestions[i],
                avatar: json.img[i].replace( /^.+src="([^"]+)".+$/ , '$1'),
                type: 'contact'
            });
        }
    }

    return friends;
}

// Exports.
return {
    initInputbox: initInputbox,
    fetchInputbox: fetchInputbox,
    fetchFriendsInContext: fetchFriendsInContext,
    fetchGroupMembers: fetchGroupMembers,
    fetchEventMembers: fetchEventMembers
};

});
