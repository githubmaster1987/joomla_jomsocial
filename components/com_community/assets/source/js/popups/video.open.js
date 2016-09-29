(function( root, $, factory ) {

    joms.popup || (joms.popup = {});
    joms.popup.video || (joms.popup.video = {});
    joms.popup.video.open = factory( root, $ );

    define([ 'utils/popup', 'utils/videotag' ], function() {
        return joms.popup.video.open;
    });

})( window, joms.jQuery, function( window, $ ) {

var iconCog      = '<svg viewBox="0 0 16 16" class="joms-icon"><use xlink:href="#joms-icon-cog"></use></svg>',
    iconBubble   = '<svg viewBox="0 0 16 16" class="joms-icon"><use xlink:href="#joms-icon-bubble"></use></svg>',
    iconThumbsUp = '<svg viewBox="0 0 16 16" class="joms-icon"><use xlink:href="#joms-icon-thumbs-up"></use></svg>',
    iconTag      = '<svg viewBox="0 0 16 16" class="joms-icon"><use xlink:href="#joms-icon-tag"></use></svg>',

    popup, elem, tagBtn, tags, tagLabel, tagRemoveLabel, id, lang,
    canEdit, canDelete, videoUrl, userId, groupId, eventId, isRegistered, isOwner, isAdmin, enableProfileVideo, enableReporting, enableSharing;

function render( _popup, _id ) {
    if ( elem ) elem.off();
    popup = _popup;
    id = _id;

    joms.ajax({
        func: 'videos,ajaxShowVideoWindow',
        data: [ id ],
        callback: function( json ) {
            json || (json = {});
            lang = json.lang || {};
            canEdit = json.can_edit || false;
            canDelete = json.can_delete || false;
            videoUrl = json.video_url;

            // Priviliges.
            isRegistered = userId = +json.my_id;
            groupId = +json.groupid;
            eventId = +json.eventid;
            isOwner = isRegistered && ( +json.my_id === +json.owner_id );
            isAdmin = +json.is_admin;

            // Settings.
            enableProfileVideo = +json.enableprofilevideo;
            enableReporting = +json.enablereporting;
            enableSharing = +json.enablesharing;

            popup.items[0] = {
                type: 'inline',
                src: buildHtml( json )
            };

            popup.updateItemHTML();

            // Override popup#close function.
            popup.close = closeOverride;

            initVideo();

            elem = popup.contentContainer;
            tagBtn = elem.find('.joms-popup__btn-tag-video');

            elem.on( 'click', '.joms-popup__btn-tag-video', tagPrepare );
            elem.on( 'click', '.joms-popup__btn-comments', toggleComments );
            elem.on( 'click', '.joms-popup__btn-option', toggleDropdown );
            elem.on( 'click', '.joms-popup__btn-share', share );
            elem.on( 'click', '.joms-popup__btn-report', report );
            elem.on( 'click', '.joms-popup__btn-fetch', _fetch );
            elem.on( 'click', '.joms-popup__btn-profile', setAsProfileVideo );
            elem.on( 'click', '.joms-popup__btn-edit', _edit );
            elem.on( 'click', '.joms-popup__btn-delete', _delete );
            elem.on( 'mouseleave', '.joms-popup__dropdown--wrapper', hideDropdown );
            elem.on( 'click', '.joms-js--remove-tag', tagRemove );
            elem.on( 'click', '.joms-js--btn-desc-toggle', toggleDescription );
            elem.on( 'click', '.joms-js--btn-desc-edit', editDescription );
            elem.on( 'click', '.joms-js--btn-desc-cancel', cancelDescription );
            elem.on( 'click', '.joms-js--btn-desc-save', saveDescription );

            fetchComments( id );
        }
    });
}

function stripTags( html ) {
    html = html.replace( /<\/?[^>]+>/g, '' );
    return html;
}

function buildHtml( json ) {
    var playerHtml;

    json || (json = {});
    playerHtml = json.error || json.playerHtml || '';

    return [
        '<div class="joms-popup joms-popup--video">',
        '<div class="joms-popup__commentwrapper">',
        '<div class="joms-popup__content">',
        '<div class="joms-popup__video">',
        playerHtml,
        '</div>',
        '<div class="joms-popup__option clearfix">',
        '<div class="joms-popup__optcaption">',
        ' &nbsp;<svg viewBox="0 0 16 16" class="joms-icon"><use xlink:href="#joms-icon-eye"></use></svg> ',
        json.hits,
        '</div>',
        '<div class="joms-popup__optoption">',
        '<button class="joms-popup__btn-comments">', iconBubble, ' <span class="joms-popup__btn-overlay">', lang.comments, '</span></button>',
        getLikeHtml( json.like ),
        ( canEdit ? '<button class="joms-popup__btn-tag-video">' + iconTag + ' <span class="joms-popup__btn-overlay">' + lang.tag_video + '</span></button>' : '' ),
        '<div class="joms-popup__dropdown--wrapper">', updateDropdownHtml( json ), '<button class="joms-popup__btn-option">', iconCog, ' <span class="joms-popup__btn-overlay">', lang.options, '</span></button></div>',
        '</div>',
        '</div>',
        '</div>',
        '<div class="joms-popup__comment"></div>',
        '<button class="mfp-close" type="button" title="Close (Esc)">×</button>',
        '</div>',
        '</div>'
    ].join('');
}

function getLikeHtml( json ) {
    var html, count;

    // Like info.
    html = '';
    if ( json ) {
        html += '<button class="joms-popup__btn-like joms-js--like-videos-' + id + ( json.is_liked ? ' liked' : '' ) + '"';
        html += ' onclick="joms.api.page' + ( json.is_liked ? 'Unlike' : 'Like' ) + '(\'videos\', \'' + id + '\');"';
        html += ' data-lang="' + ( json.lang || 'Like' ) + '"';
        html += ' data-lang-like="' + ( json.lang_like || 'Like' ) + '"';
        html += ' data-lang-liked="' + ( json.lang_liked || 'Liked' ) + '">';
        html += iconThumbsUp + ' ';
        html += '<span>';
        html += ( json.is_liked ? json.lang_liked : json.lang_like );

        count = +json.count;
        if ( count > 0 ) {
            html += ' (' + count + ')';
        }

        html += '</span></button>';
    }

    return html;
}

function tagPrepare( e ) {
    if ( tagBtn.data('tagging') ) {
        tagBtn.removeData('tagging');
        tagBtn.html( iconTag + ' <span class="joms-popup__btn-overlay">' + lang.tag_video + '</span>' );
        tagCancel();
    } else {
        tagBtn.data( 'tagging', 1 );
        tagBtn.html( iconTag + ' ' + lang.done_tagging );
        tagStart( e );
    }
}

function tagStart( e ) {
    var indices = joms._.map( tags, function( item ) {
        return item.userId + '';
    });

    joms.util.videotag.create( e, indices, groupId, eventId );
    joms.util.videotag.on( 'tagAdded', tagAdded );
    joms.util.videotag.on( 'destroy', function() {
        tagBtn.removeData('tagging');
        tagBtn.html( iconTag + ' <span class="joms-popup__btn-overlay">' + lang.tag_video + '</span>' );
    });
}

function tagAdded( userId ) {
    joms.ajax({
        func: 'videos,ajaxAddVideoTag',
        data: [ id, userId ],
        callback: function( json ) {
            var $comments, $tags;

            if ( json.error ) {
                window.alert( stripTags( json.error ) );
                return;
            }

            if ( json.success ) {
                tags.push( json.data );

                // Render tag info.
                $comments = elem.find('.joms-popup__comment');
                $tags = $comments.find('.joms-js--tag-info');
                $tags.html( _tagBuildHtml() );
            }
        }
    });
}

function tagCancel() {
    joms.util.videotag.destroy();
}

function tagRemove( e ) {
    var el = $( e.currentTarget ),
        userId = el.data('id');

    joms.ajax({
        func: 'videos,ajaxRemoveVideoTag',
        data: [ id, userId ],
        callback: function( json ) {
            var $comments, $tags, i;

            if ( json.error ) {
                window.alert( stripTags( json.error ) );
                return;
            }

            if ( json.success ) {
                for ( i = 0; i < tags.length; i++ ) {
                    if ( +userId === +tags[i].userId ) {
                        tags.splice( i--, 1 );
                    }
                }

                // Render tag info.
                $comments = elem.find('.joms-popup__comment');
                $tags = $comments.find('.joms-js--tag-info');
                $tags.html( _tagBuildHtml() );
            }

        }
    });
}

function _tagBuildHtml() {
    var html, item, str, i;

    if ( !tags || !tags.length ) {
        return '';
    }

    html = [];

    for ( i = 0; i < tags.length; i++ ) {
        item = tags[i];
        str = '<a href="' + item.profileUrl + '">' + item.displayName + '</a>';

        if ( item.canRemove ) {
            str += ' (<a href="javascript:" class="joms-js--remove-tag" data-id="' + item.userId + '">' + tagRemoveLabel + '</a>)';
        }

        html.push( str );
    }

    html = html.join(', ');
    html = tagLabel + '<br>' + html;

    return html;
}

function toggleComments() {
    elem.children('.joms-popup').toggleClass('joms-popup--togglecomment');
}

function closeOverride() {
    var $ct = elem.children('.joms-popup'),
        className = 'joms-popup--togglecomment';

    if ( $ct.hasClass( className ) ) {
        $ct.removeClass( className );
        return;
    }

    $.magnificPopup.proto.close.call( this );
}

function toggleDropdown( e ) {
    var wrapper = $( e.target ).closest('.joms-popup__dropdown--wrapper'),
        dropdown = wrapper.children('.joms-popup__dropdown');

    dropdown.toggleClass('joms-popup__dropdown--open');
}

function hideDropdown( e ) {
    var wrapper = $( e.target ).closest('.joms-popup__dropdown--wrapper'),
        dropdown = wrapper.children('.joms-popup__dropdown');

    dropdown.removeClass('joms-popup__dropdown--open');
}

function updateDropdownHtml( json ) {
    var html = '';

    json || (json = {});

    if ( enableSharing ) {
        html += '<a href="javascript:" class="joms-popup__btn-share">' + lang.share + '</a>';
        html += '<div class="sep"></div>';
    }

    if ( isOwner || isAdmin ) {
        html += '<a href="javascript:" class="joms-popup__btn-fetch">' + lang.fetch + '</a>';
        html += ( isOwner && enableProfileVideo ? '<a href="javascript:" class="joms-popup__btn-profile">' + lang.set_as_profile_video + '</a>' : '' );
        html += '<div class="sep"></div>';
        html += '<a href="javascript:" class="joms-popup__btn-edit">' + lang.edit_video + '</a>';
        html += '<a href="javascript:" class="joms-popup__btn-delete">' + lang.delete_video + '</a>';
    } else {
        html += ( canDelete ? '<a href="javascript:" class="joms-popup__btn-delete">' + lang.delete_video + '</a>' : '' );
        html += ( enableReporting ? '<a href="javascript:" class="joms-popup__btn-report">' + lang.report + '</a>' : '' );
    }

    html = '<div class="joms-popup__dropdown"><div class="joms-popup__ddcontent">' + html + '</div></div>';

    return html;
}

function share() {
    joms.api.pageShare( videoUrl );
}

function report() {
    joms.api.videoReport( userId, videoUrl );
}

function _fetch() {
    joms.api.videoFetchThumbnail( id );
}

function setAsProfileVideo() {
    joms.ajax({
        func: 'profile,ajaxConfirmLinkProfileVideo',
        data: [ id ],
        callback: function( json ) {
            if ( json.error ) {
                window.alert( stripTags( json.error ) );
                return;
            }

            if ( window.confirm( stripTags( json.html ) ) ) {
                setAsProfileVideoConfirm();
            }
        }
    });
}

function setAsProfileVideoConfirm() {
    joms.ajax({
        func: 'profile,ajaxLinkProfileVideo',
        data: [ id ],
        callback: function( json ) {
            if ( json.error ) {
                window.alert( stripTags( json.error ) );
                return;
            }

            window.alert( stripTags( json.message ) );
            setTimeout(function() {
                window.location.reload();
            }, 500 );
        }
    });
}

function _edit() {
    joms.api.videoEdit( id );
}

function _delete() {
    joms.ajax({
        func: 'videos,ajaxConfirmRemoveVideo',
        data: [ id ],
        callback: function( json ) {
            if ( json.error ) {
                window.alert( stripTags( json.error ) );
                return;
            }

            if ( window.confirm( stripTags( json.html ) ) ) {
                _deleteConfirm();
            }
        }
    });
}

function _deleteConfirm() {
    joms.ajax({
        func: 'videos,ajaxRemoveVideo',
        data: [ id ],
        callback: function( json ) {
            if ( json.error ) {
                window.alert( stripTags( json.error ) );
                return;
            }

            window.alert( stripTags( json.message ) );
            setTimeout(function() {
                window.location.reload();
            }, 500 );
        }
    });
}

function fetchComments( id, showAllParams ) {
    var comments = elem.find('.joms-popup__comment');

    if ( !showAllParams ) {
        comments.empty();
    }

    joms.ajax({
        func: 'videos,ajaxGetInfo',
        data: [ id, showAllParams ? 1 : 0 ],
        callback: function( json ) {
            var $tags;

            if ( !showAllParams ) {
                if ( json.comments && json.showall ) {
                    json.showall = '<div class="joms-comment__more joms-js--more-comments"><a href="javascript:">' + json.showall + '</a></div>';
                    json.comments = $( json.comments );
                    json.comments.prepend( json.showall );
                }
            }

            if ( showAllParams ) {
                comments.find('.joms-comment').replaceWith( json.comments );
            } else {
                comments.html( json.head || '' );
                comments.append( json.comments );
                comments.append( json.form || '' );

                // Render description.
                comments.find('.joms-js--description').html(
                    renderDescription( json.description || {} )
                );

                // Cache tag info.
                tags = json.tagged || [];
                tagLabel = json.tagLabel || '';
                tagRemoveLabel = json.tagRemoveLabel || '';

                // Render tag info.
                $tags = comments.find('.joms-js--tag-info');
                $tags.html( _tagBuildHtml() );

                comments.find('textarea.joms-textarea');
                joms.fn.tagging.initInputbox();
            }

            initVideoPlayers();
        }
    });
}

function initVideo() {
    var cssVideo = '.joms-js--video',
        video = $('.joms-popup__content').find( cssVideo );

    if ( !video.length ) {
        return;
    }

    joms.loadCSS( joms.ASSETS_URL + 'vendors/mediaelement/mediaelementplayer.min.css' );
    video.on( 'click.joms-video', cssVideo + '-play', function() {
        var $el = $( this ).closest( cssVideo );
        joms.util.video.play( $el, $el.data() );
    });
}

function initVideoPlayers() {
    var initialized = '.joms-js--initialized',
        cssVideos = '.joms-js--video',
        videos = $('.joms-comment__body,.joms-js--inbox').find( cssVideos ).not( initialized ).addClass( initialized.substr(1) );

    if ( !videos.length ) {
        return;
    }

    joms.loadCSS( joms.ASSETS_URL + 'vendors/mediaelement/mediaelementplayer.min.css' );
    videos.on( 'click.joms-video', cssVideos + '-play', function() {
        var $el = $( this ).closest( cssVideos );
        joms.util.video.play( $el, $el.data() );
    });

    if ( joms.ios ) {
        setTimeout(function() {
            videos.find( cssVideos + '-play' ).click();
        }, 2000 );
    }
}

function renderDescription( json ) {
    var showExcerpt;

    if ( typeof json !== 'object' ) {
        json = {};
    }

    if ( json.content ) {
        if ( json.excerpt !== json.content ) {
            showExcerpt = true;
        }
    }

    return [
        '<div class="joms-js--btn-desc-content">',
            '<span class="joms-js--btn-desc-excerpt"', ( showExcerpt ? '' : ' style="display:none"' ), '>', ( json.excerpt || '' ), '</span>',
            '<span class="joms-js--btn-desc-fulltext"', ( showExcerpt ? ' style="display:none"' : '' ), '>', ( json.content || '' ), '</span>',
            ' <a href="javascript:" class="joms-js--btn-desc-toggle"', ( showExcerpt ? '' : ' style="display:none"' ), '>', window.joms_lang.COM_COMMUNITY_SHOW_MORE, '</a>',
        '</div>',
        '<div class="joms-js--btn-desc-editor joms-popup__hide">',
            '<textarea class="joms-textarea" style="margin:0" placeholder="', ( json.lang_placeholder || '' ), '">', br2nl( json.content || '' ), '</textarea>',
            '<div style="margin-top:5px;text-align:right">',
                '<button class="joms-button--neutral joms-button--small joms-js--btn-desc-cancel">', ( json.lang_cancel || 'Cancel' ), '</button> ',
                '<button class="joms-button--primary joms-button--small joms-js--btn-desc-save">', ( json.lang_save || 'Save' ), '</button>',
            '</div>',
        '</div>',
        '<div class="joms-js--btn-desc-edit"', ( canEdit ? '' : ' style="display:none"' ), '><a href="javascript:"',
            ' data-lang-add="', ( json.lang_add || 'Add description' ), '"',
            ' data-lang-edit="', ( json.lang_edit || 'Edit description' ), '">',
                ( json.content ? json.lang_edit : json.lang_add ),
            '</a>',
        '</div>'
    ].join('');
}

function br2nl( text ) {
    text = text || '';
    text = text.replace( /<br\s*\/?>/g, '\n' );
    return text;
}

function toggleDescription() {
    var $excerpt = elem.find('.joms-js--btn-desc-excerpt'),
        $fulltext = elem.find('.joms-js--btn-desc-fulltext'),
        $button = elem.find('.joms-js--btn-desc-toggle');

    if ( $fulltext.is(':visible') ) {
        $fulltext.hide();
        $excerpt.show();
        $button.html( window.joms_lang.COM_COMMUNITY_SHOW_MORE );
    } else {
        $excerpt.hide();
        $fulltext.show();
        $button.html( window.joms_lang.COM_COMMUNITY_SHOW_LESS );
    }
}

function editDescription() {
    elem.find('.joms-js--btn-desc-content').hide();
    elem.find('.joms-js--btn-desc-edit').hide();
    elem.find('.joms-js--btn-desc-editor').show();
}

function cancelDescription() {
    elem.find('.joms-js--btn-desc-editor').hide();
    elem.find('.joms-js--btn-desc-content').show();
    elem.find('.joms-js--btn-desc-edit').show();
}

function saveDescription() {
    var content  = elem.find('.joms-js--btn-desc-content'),
        editor   = elem.find('.joms-js--btn-desc-editor'),
        button   = elem.find('.joms-js--btn-desc-edit'),
        textarea = editor.find('textarea'),
        value    = $.trim( textarea.val() );

    joms.ajax({
        func: 'videos,ajaxSaveDescription',
        data: [ id, value ],
        callback: function( json ) {
            var a = button.find('a'),
                $cexcerpt, $cfulltext, $cbutton;

            if ( json.error ) {
                window.alert( json.error );
                return;
            }

            if ( json.success ) {

                $cexcerpt = content.find('.joms-js--btn-desc-excerpt');
                $cfulltext = content.find('.joms-js--btn-desc-fulltext');
                $cbutton = content.find('.joms-js--btn-desc-toggle');

                // Update content.
                if ( !json.caption || json.caption === json.excerpt ) {
                    $cexcerpt.hide();
                    $cbutton.hide();
                    $cfulltext.html( json.caption ).show();
                } else {
                    $cexcerpt.html( json.excerpt ).show();
                    $cbutton.html( window.joms_lang.COM_COMMUNITY_SHOW_MORE ).show();
                    $cfulltext.html( json.caption ).hide();
                }

                editor.hide();
                content.show();

                a.html( a.data( 'lang-' + ( value ? 'edit' : 'add' ) ) );
                button.show();
            }
        }
    });
}

// Exports.
return joms._.debounce(function( id ) {
    joms.util.popup.prepare(function( mfp ) {
        render( mfp, id );
    });
}, 200 );

});
