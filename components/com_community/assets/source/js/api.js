(function( root, factory ) {

    joms.api = factory( root );

    define([
        'functions/announcement',
        'functions/facebook',
        'functions/invitation',
        'utils/field',
        'popups/info',
        'popups/login',
        'popups/album',
        'popups/announcement',
        'popups/app',
        'popups/avatar',
        'popups/comment',
        'popups/cover',
        'popups/discussion',
        'popups/event',
        'popups/fbc',
        'popups/file',
        'popups/friend',
        'popups/group',
        'popups/inbox',
        'popups/location',
        'popups/notification',
        'popups/photo',
        'popups/pm',
        'popups/search',
        'popups/tnc',
        'popups/user',
        'popups/video',
        'views/cover',
        'views/page',
        'views/stream'
    ], function() {
        return joms.api;
    });

})( window, function() {

// Exports.
return {

    /** Login form. */
    login: function( json ) {
        joms.popup.login( json );
    },

    /** User. */
    userChangeVanityURL: function( id ) {
        joms.popup.user.changeVanityURL( id );
    },
    userAddFeatured: function( id ) {
        joms.popup.user.addFeatured( id );
    },
    userRemoveFeatured: function( id ) {
        joms.popup.user.removeFeatured( id );
    },
    userBan: function( id ) {
        joms.popup.user.ban( id );
    },
    userUnban: function( id ) {
        joms.popup.user.unban( id );
    },
    userBlock: function( id ) {
        joms.popup.user.block( id );
    },
    userUnblock: function( id ) {
        joms.popup.user.unblock( id );
    },
    userIgnore: function( id ) {
        joms.popup.user.ignore( id );
    },
    userUnignore: function( id ) {
        joms.popup.user.unignore( id );
    },
    userReport: function( id ) {
        joms.popup.user.report( id );
    },

    /** Avatar (profile picture). */
    avatarChange: function( type, id, e ) {
        joms.popup.avatar.change( type, id );
        if ( e ) {
            e.stopPropagation();
            e.preventDefault();
        }
    },
    avatarRemove: function( type, id ) {
        joms.popup.avatar.remove( type, id );
    },
    avatarRotate: function( type, id, direction, callback ) {
        joms.popup.avatar.rotate( type, id, direction, callback );
    },

    /** Profile cover. */
    coverChange: function( type, id ) {
        joms.popup.cover.change( type, id );
    },
    coverRemove: function( type, id ) {
        joms.popup.cover.remove( type, id );
    },
    coverReposition: function( type, id ) {
        joms.view.cover.reposition( type, id );
    },
    coverClick: function( albumId, photoId ) {
        joms.view.cover.click( albumId, photoId );
    },

    /** Events. */
    eventInvite: function( id, type ) {
        joms.popup.event.invite( id, type );
    },
    eventJoin: function( id ) {
        joms.popup.event.join( id );
    },
    eventLeave: function( id ) {
        joms.popup.event.leave( id );
    },
    eventResponse: function() {
        joms.popup.event.response.apply( this, arguments );
    },
    eventAddFeatured: function( id ) {
        joms.popup.event.addFeatured( id );
    },
    eventRemoveFeatured: function( id ) {
        joms.popup.event.removeFeatured( id );
    },
    eventReport: function( id ) {
        joms.popup.event.report( id );
    },
    eventDelete: function( id ) {
        joms.popup.event[ 'delete' ]( id );
    },
    eventRejectGuest: function( id, userId ) {
        joms.popup.event.rejectGuest( id, userId );
    },
    eventBanMember: function( id, userId ) {
        joms.popup.event.banMember( id, userId );
    },
    eventUnbanMember: function( id, userId ) {
        joms.popup.event.unbanMember( id, userId );
    },

    /** Friends. */
    friendAdd: function( id ) {
        joms.popup.friend.add( id );
    },
    friendAddCancel: function( id ) {
        joms.popup.friend.addCancel( id );
    },
    friendRemove: function( id ) {
        joms.popup.friend.remove( id );
    },
    friendResponse: function( id ) {
        joms.popup.friend.response( id );
    },
    friendApprove: function( id ) {
        joms.popup.friend.approve( id );
    },
    friendReject: function( id ) {
        joms.popup.friend.reject( id );
    },

    /** Groups. */
    groupInvite: function( id ) {
        joms.popup.group.invite( id, 1, 1 );
    },
    groupJoin: function( id ) {
        joms.popup.group.join( id );
    },
    groupLeave: function( id ) {
        joms.popup.group.leave( id );
    },
    groupAddFeatured: function( id ) {
        joms.popup.group.addFeatured( id );
    },
    groupRemoveFeatured: function( id ) {
        joms.popup.group.removeFeatured( id );
    },
    groupReport: function( id ) {
        joms.popup.group.report( id );
    },
    groupUnpublish: function( id ) {
        joms.popup.group.unpublish( id );
    },
    groupDelete: function( id ) {
        joms.popup.group[ 'delete' ]( id );
    },
    groupApprove: function( id, userId ) {
        joms.popup.group.approve( id, userId );
    },
    groupRemoveMember: function( id, userId ) {
        joms.popup.group.removeMember( id, userId );
    },
    groupBanMember: function( id, userId ) {
        joms.popup.group.banMember( id, userId );
    },
    groupUnbanMember: function( id, userId ) {
        joms.popup.group.unbanMember( id, userId );
    },

    /** Notifications. */
    notificationGeneral: function() {
        joms.view.toolbar.notificationGeneral();
    },
    notificationFriend: function() {
        joms.view.toolbar.notificationFriend();
    },
    notificationPm: function() {
        joms.view.toolbar.notificationPm();
    },

    /** Photos. */
    photoUpload: function( albumId, contextId, context ) {
        joms.popup.photo.upload( albumId, contextId, context );
    },
    photoOpen: function( albumId, id ) {
        joms.popup.photo.open( albumId, id );
    },
    photoRemove: function( id ) {
        joms.popup.photo.remove( id );
    },
    photoZoom: function( url ) {
        joms.popup.photo.zoom( url );
    },
    photoSetAvatar: function( id ) {
        joms.popup.photo.setAvatar( id );
    },
    photoSetCover: function( albumId, id ) {
        joms.popup.photo.setCover( albumId, id );
    },
    photoSetAlbum: function( id ) {
        joms.popup.photo.setAlbum( id );
    },
    photoReport: function( id, url ) {
        joms.popup.photo.report( id, url );
    },

    /** Photo albums. */
    albumRemove: function( id ) {
        joms.popup.album.remove( id );
    },
    albumAddFeatured: function( id ) {
        joms.popup.album.addFeatured( id );
    },
    albumRemoveFeatured: function( id ) {
        joms.popup.album.removeFeatured( id );
    },

    /** Private messages. */
    pmSend: function( id ) {
        joms.popup.pm.send( id );
    },

    /** Videos. */
    videoAdd: function( contextId, context ) {
        joms.popup.video.add( contextId, context );
    },
    videoOpen: function( id ) {
        joms.popup.video.open( id );
    },
    videoEdit: function( id ) {
        joms.popup.video.edit( id );
    },
    videoRemove: function( id, redirect ) {
        joms.popup.video.remove( id, redirect );
    },
    videoAddFeatured: function( id ) {
        joms.popup.video.addFeatured( id );
    },
    videoRemoveFeatured: function( id ) {
        joms.popup.video.removeFeatured( id );
    },
    videoLinkToProfile: function( id ) {
        joms.popup.video.linkToProfile( id );
    },
    videoRemoveLinkFromProfile: function( id, userId ) {
        joms.popup.video.removeLinkFromProfile( id, userId );
    },
    videoFetchThumbnail: function( id ) {
        joms.popup.video.fetchThumbnail( id );
    },
    videoReport: function( id, url ) {
        joms.popup.video.report( id, url );
    },

    locationView: function( id ) {
        joms.popup.location.view( id );
    },

    /** Stream. */
    streamLike: function( id ) {
        joms.view.stream.like( id );
    },
    streamUnlike: function( id ) {
        joms.view.stream.unlike( id );
    },
    streamEdit: function( id, el ) {
        joms.view.stream.edit( id, el );
    },
    streamEditLocation: function( id ) {
        joms.view.stream.editLocation( id );
    },
    streamRemove: function( id ) {
        joms.view.stream.remove( id );
    },
    streamRemoveLocation: function( id ) {
        joms.view.stream.removeLocation( id );
    },
    streamRemoveMood: function( id ) {
        joms.view.stream.removeMood( id );
    },
    streamRemoveTag: function( id ) {
        joms.view.stream.removeTag( id );
    },
    streamSelectPrivacy: function( id ) {
        joms.view.stream.selectPrivacy( id );
    },
    streamShare: function( id ) {
        joms.view.stream.share( id );
    },
    streamHide: function( id, userId ) {
        joms.view.stream.hide( id, userId );
    },
    streamShowLikes: function( id, target ) {
        joms.view.stream.showLikes( id, target );
    },
    streamShowComments: function( id, type ) {
        joms.view.stream.showComments( id, type );
    },
    streamShowOthers: function( id ) {
        joms.view.stream.showOthers( id );
    },
    streamReport: function( id ) {
        joms.view.stream.report( id );
    },
    streamToggleText: function( id ) {
        joms.view.stream.toggleText( id );
    },
    streamAddFeatured: function( id ) {
        joms.view.stream.addFeatured( id );
    },
    streamRemoveFeatured: function( id ) {
        joms.view.stream.removeFeatured( id );
    },

    /** Streams. */
    streamsLoadMore: function() {
        joms.view.streams.loadMore();
    },

    /** Comment system. */
    commentLike: function( id ) {
        joms.view.comment.like( id );
    },
    commentUnlike: function( id ) {
        joms.view.comment.unlike( id );
    },
    commentEdit: function( id, el, type ) {
        joms.view.comment.edit( id, el, type );
    },
    commentCancel: function( id ) {
        joms.view.comment.cancel( id );
    },
    commentRemove: function( id, type ) {
        joms.view.comment.remove( id, type );
    },
    commentRemoveTag: function( id, type ) {
        joms.view.comment.removeTag( id, type );
    },
    commentRemovePreview: function( id, type ) {
        joms.view.comment.removePreview( id, type );
    },
    commentRemoveThumbnail: function( id, type ) {
        joms.view.comment.removeThumbnail( id, type );
    },
    commentShowLikes: function( id ) {
        joms.popup.comment.showLikes( id );
    },
    commentToggleText: function( id ) {
        joms.view.comment.toggleText( id );
    },

    /** Application */
    appAbout: function( name ) {
        joms.popup.app.about( name );
    },
    appBrowse: function( pos ) {
        joms.popup.app.browse( pos );
    },
    appPrivacy: function( name ) {
        joms.popup.app.privacy( name );
    },
    appRemove: function( id ) {
        joms.popup.app.remove( id );
    },
    appSetting: function( id, name ) {
        joms.popup.app.setting( id, name );
    },

    /** Search. */
    searchSave: function( data ) {
        joms.popup.search.save( data );
    },

    /** Page */
    pageLike: function( type, id ) {
        joms.view.page.like( type, id );
    },
    pageUnlike: function( type, id ) {
        joms.view.page.unlike( type, id );
    },
    pageShare: function( url ) {
        joms.view.page.share( url );
    },

    /** Invitation */
    invitationAccept: function( type, id ) {
        joms.fn.invitation.accept( type, id );
    },
    invitationReject: function( type, id ) {
        joms.fn.invitation.reject( type, id );
    },

    /** File */
    fileUpload: function( type, id ) {
        joms.popup.file.upload( type, id );
    },
    fileList: function( type, id ) {
        joms.popup.file.list( type, id );
    },
    fileDownload: function( type, id, path ) {
        joms.popup.file.download( type, id, path );
    },
    fileRemove: function( type, id ) {
        joms.popup.file.remove( type, id );
    },
    fileUpdateHit: function( id, location ) {
        joms.popup.file.updateHit( id, location );
    },

    /** Discussion */
    discussionLock: function( groupId, id ) {
        joms.popup.discussion.lock( groupId, id );
    },
    discussionRemove: function( groupId, id ) {
        joms.popup.discussion.remove( groupId, id );
    },

    /** Announcement */
    announcementEdit: function( groupId, id ) {
        joms.fn.announcement.edit( groupId, id );
    },
    announcementEditCancel: function( groupId, id ) {
        joms.fn.announcement.editCancel( groupId, id );
    },
    announcementRemove: function( groupId, id ) {
        joms.popup.announcement.remove( groupId, id );
    },

    /** Inbox */
    inboxRemove: function( task, msgIds ) {
        joms.popup.inbox.remove( task, msgIds );
    },
    inboxSetRead: function( msgIds, error ) {
        joms.popup.inbox.setRead( msgIds, error );
    },
    inboxSetUnread: function( msgIds, error ) {
        joms.popup.inbox.setUnread( msgIds, error );
    },

    /** Terms of services */
    tnc: function() {
        joms.popup.tnc();
    },

    /** Facebook connect */
    fbcUpdate: function() {
        joms.popup.fbc.update();
    }

};

});
