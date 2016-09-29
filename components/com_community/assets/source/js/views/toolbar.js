(function( root, $, factory ) {

    joms.view || (joms.view = {});
    joms.view.toolbar = factory( root, $ );

})( window, joms.jQuery, function( window, $ ) {

var wrapper, buttonMain, buttonUser, buttonSubMenu, xhr, lastbtn;

function hideMenu( e ) {
    var nohide = $( e.target ).closest('.joms-trigger__menu--main, .joms-trigger__menu--user, .joms-menu, .joms-menu--user');
    if ( nohide.length ) return;
    if ( wrapper.hasClass('show-menu') || wrapper.hasClass('show-menu--user') ) {
        e.preventDefault();
        e.stopPropagation();
        wrapper.removeClass('show-menu');
        wrapper.removeClass('show-menu--user');
    }
}

function toggleMenu( e ) {
    e.stopPropagation();
    wrapper.toggleClass('show-menu');
}

function toggleUserMenu( e ) {
    e.stopPropagation();
    wrapper.toggleClass('show-menu--user');
}

function toggleSubMenu( e ) {
    var el = $( e.currentTarget ).closest('li');
    if ( el.hasClass('show-submenu') ) {
        el.removeClass('show-submenu');
    } else {
        el.addClass('show-submenu').siblings().removeClass('show-submenu');
    }
}

function start() {
    if ( !wrapper ) wrapper = $('.jomsocial-wrapper');
    if ( !buttonMain ) buttonMain = $('.joms-trigger__menu--main');
    if ( !buttonUser ) buttonUser = $('.joms-trigger__menu--user');
    if ( !buttonSubMenu ) buttonSubMenu = $('.joms-menu__toggle');

    stop();

    wrapper.on( 'click.menu', hideMenu );

    wrapper.on( 'click.menu', '.joms-js--has-dropdown', function( e ) {
        e.preventDefault();
        e.stopPropagation();
        window.location = $( e.currentTarget ).attr('href');
    });

    wrapper.on( 'mouseenter.menu', '.joms-toolbar--desktop > ul > li > a.joms-js--has-dropdown', function( e ) {
        var btn = $( e.currentTarget );
        if ( ! btn.siblings('ul.joms-dropdown').is(':visible') ) {
            lastbtn = btn.trigger('click.dropdown');
        }
    });

    wrapper.on( 'mouseleave.menu', '.joms-toolbar--desktop', function() {
        if ( lastbtn ) {
            lastbtn.trigger('collapse.dropdown');
            lastbtn = undefined;
        }
    });

    buttonMain.on( 'click.menu', toggleMenu );
    buttonUser.on( 'click.menu', toggleUserMenu );
    buttonSubMenu.on( 'click.submenu', toggleSubMenu );

    getNotifications();
}

function stop() {
    if ( wrapper ) {
        wrapper.off( 'click.menu' );
        wrapper.off( 'click.menu', '.joms-js--has-dropdown' );
        wrapper.off( 'mouseenter.menu', '.joms-toolbar--desktop > ul > li > a.joms-js--has-dropdown' );
        wrapper.off( 'mouseleave.menu', '.joms-toolbar--desktop' );
    }

    if ( buttonMain ) buttonMain.off('click.menu');
    if ( buttonUser ) buttonUser.off('click.menu');
    if ( buttonSubMenu ) buttonSubMenu.off('click.submenu');
}

function notificationGeneral() {
    joms.ajax({
        func: 'notification,ajaxGetNotification',
        data: [ '' ],
        callback: function( json ) {
            var elem;
            if ( json.html ) {
                elem = $('.joms-popover--toolbar-general');
                elem.html( json.html );
            }
        }
    });
}

function notificationFriend() {
    joms.ajax({
        func: 'notification,ajaxGetRequest',
        data: [ '' ],
        callback: function( json ) {
            var elem;
            if ( json.html ) {
                elem = $('.joms-popover--toolbar-friendrequest');
                elem.html( json.html );
                elem.off( 'click', '.joms-button__approve' ).on( 'click', '.joms-button__approve', notificationFriendReject );
                elem.off( 'click', '.joms-button__reject' ).on( 'click', '.joms-button__reject', notificationFriendApprove );
            }
        }
    });
}

function notificationFriendReject( e ) {
    var elem = $( e.currentTarget ),
        id = elem.data('connection');

    joms.ajax({
        func: 'friends,ajaxRejectRequest',
        data: [ id ],
        callback: function( json ) {
            elem = $('.joms-js__friend-request-' + id);
            elem.find('.joms-popover__actions').remove();
            elem.find('.joms-popover__content').html( json.error || json.message );
            notificationCounter( 'friendrequest', -1 );
        }
    });
}

function notificationFriendApprove( e ) {
    var elem = $( e.currentTarget ),
        id = elem.data('connection');

    joms.ajax({
        func: 'friends,ajaxApproveRequest',
        data: [ id ],
        callback: function( json ) {
            elem = $('.joms-js__friend-request-' + id);
            elem.find('.joms-popover__actions').remove();
            elem.find('.joms-popover__content').html( json.error || json.message );
            notificationCounter( 'friendrequest', -1 );
        }
    });
}

function notificationPm() {
    joms.ajax({
        func: 'notification,ajaxGetInbox',
        data: [ '' ],
        callback: function( json ) {
            var elem;
            if ( json.html ) {
                elem = $('.joms-popover--toolbar-pm');
                elem.html( json.html );
            }
        }
    });
}

function notificationCounter( type, count ) {
    var counters;

    if ([ 'general', 'friendrequest', 'pm' ].indexOf( type ) < 0)
        return;

    counters = $( '.joms-notifications__label--' + type );
    count = +counters.eq(0).text() + count;
    counters.html( count > 0 ? count : '' );
}

function search( elem ) {
    var keyword = elem,
        rTrim = /^\s+|\s+$/g,
        field, loading, viewall;

    if ( typeof elem !== 'string' ) {
        keyword = $( elem ).val();
    }

    keyword = keyword || '';
    if ( !keyword.replace(rTrim, '') )
        return;

    if ( xhr ) {
        xhr.abort();
    }

    elem = $('.joms-popover--toolbar-search');
    field = elem.find('.joms-js--field');
    loading = elem.find('.joms-js--loading');
    viewall = elem.find('.joms-js--viewall');

    elem.find('li:not(.joms-js--noremove)').remove();
    viewall.hide();
    loading.show();

    xhr = joms.ajax({
        func: 'search,ajaxSearch',
        data: [ keyword ],
        callback: function( json ) {
            var html, i, form, max, btn;

            loading.hide();

            if ( json.error ) {
                html = '<li class="joms-js--error">' + json.error + '</li>';
                field.after( html );
                return;
            }

            if ( json.length ) {
                html = '';
                max = Math.min( 3, json.length );
                for ( i = 0; i < max; i++ ) {
                    html += '<li><div class="joms-popover__avatar"><div class="joms-avatar">';
                    html += '<img src="' + json[i].thumb + '"></div></div>';
                    html += '<div class="joms-popover__content">';
                    html += '<h5><a href="' + json[i].url + '">' + json[i].name + '</a></h5>';
                    html += '</div></li>';
                }

                form = viewall.find('form');
                form.find('input').val( keyword );
                viewall.off( 'click', 'a' ).on( 'click', 'a', function() {
                    form[0].submit();
                });

                btn = viewall.find('a');
                btn.html( btn.data('lang').replace( '%1$s', json.length ) );

                field.after( html );
                viewall.show();
                elem.show();
            }
        }
    });
}

function getNotifications() {
    var viewerId = +window.joms_my_id;
    if ( !viewerId )
        return;

    joms.ajax({
        func: 'activities,ajaxGetTotalNotifications',
        callback: function( json ) {
            var generals, friendrequests, pms, delay, title;

            json || (json = {});

            generals       = json.newNotificationCount;
            friendrequests = json.newFriendInviteCount;
            pms            = json.newMessageCount;
            delay          = +json.nextPingDelay;

            if ( typeof generals !== 'undefined' ) {
                generals = +generals || '';
                $('.joms-js--notiflabel-general').html( generals );

                // Also update document's title.
                title = document.title;
                title = title.replace( /^\(\d+\)\s/, '' );
                title = ( generals ? '(' + generals + ') ' : '' ) + title;
                document.title = title;
            }

            if ( typeof friendrequests !== 'undefined' ) {
                $('.joms-js--notiflabel-frequest').html( +friendrequests || '' );
            }

            if ( typeof pms !== 'undefined' ) {
                $('.joms-js--notiflabel-inbox').html( +pms || '' );
            }

            if ( delay > 0 ) {
                joms._.delay( getNotifications, delay );
            }
        }
    });
}

// Exports.
return {
    start: start,
    stop: stop,
    notificationGeneral: notificationGeneral,
    notificationFriend: notificationFriend,
    notificationPm: notificationPm,
    notificationCounter: notificationCounter,
    search: search
};

});
