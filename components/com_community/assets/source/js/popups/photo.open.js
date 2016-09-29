(function( root, $, factory ) {

    joms.popup || (joms.popup = {});
    joms.popup.photo || (joms.popup.photo = {});
    joms.popup.photo.open = factory( root, $ );

    define([ 'utils/popup', 'utils/phototag' ], function() {
        return joms.popup.photo.open;
    });

})( window, joms.jQuery, function( window, $ ) {

var iconCog       = '<svg viewBox="0 0 16 16" class="joms-icon"><use xlink:href="#joms-icon-cog"></use></svg>',
    iconBubble    = '<svg viewBox="0 0 16 16" class="joms-icon"><use xlink:href="#joms-icon-bubble"></use></svg>',
    iconThumbsUp  = '<svg viewBox="0 0 16 16" class="joms-icon"><use xlink:href="#joms-icon-thumbs-up"></use></svg>',
    iconTag       = '<svg viewBox="0 0 16 16" class="joms-icon"><use xlink:href="#joms-icon-tag"></use></svg>',
    iconNewspaper = '<svg viewBox="0 0 16 16" class="joms-icon"><use xlink:href="#joms-icon-newspaper"></use></svg>',

    popup, elem, img, spinner, caption, tagBtn, tags, tagLabel, tagRemoveLabel, album, id, list, index, lang,
    canEdit, canDelete, canTag, canMovePhoto, albumName, albumUrl, photoUrl, userId, groupId, eventId, isRegistered, isOwner, isAdmin, enableDownload, enableReporting, enableSharing, enableLike;

function render( _popup, _album, _id ) {
    if ( elem ) elem.off();
    popup = _popup;
    album = _album;
    id = _id;

    joms.ajax({
        func: 'photos,ajaxGetPhotosByAlbum',
        data: [ album, id ],
        callback: function( json ) {
            json || (json = {});
            lang = json.lang || {};
            canEdit = json.can_edit || false;
            canDelete = json.can_delete || false;
            canTag = json.can_tag || false;
            canMovePhoto = json.can_move_photo || false;
            albumName = json.album_name || 'Untitled';
            albumUrl = json.album_url;
            photoUrl = json.photo_url;

            // Priviliges.
            isRegistered = userId = +json.my_id;
            groupId = +json.groupid;
            eventId = +json.eventid;
            isOwner = isRegistered && ( +json.my_id === +json.owner_id );
            isAdmin = +json.is_admin;

            // Settings.
            enableDownload = +json.deleteoriginalphotos ? false : true;
            enableReporting = +json.enablereporting;
            enableSharing = +json.enablesharing;
            enableLike = +json.enablelike;

            if ( albumUrl ) {
                albumName = '<a href="' + albumUrl + '">' + albumName + '</a>';
            }

            popup.items[0] = {
                type: 'inline',
                src: json.error ? buildErrorHtml( json ) : buildHtml( json )
            };

            popup.updateItemHTML();

            // Override popup#close function.
            popup.close = closeOverride;

            elem = popup.contentContainer;

            // Break on error.
            if ( json.error ) {
                return;
            }

            img = elem.find('img');
            spinner = elem.find('.joms-spinner');
            caption = elem.find('.joms-popup__optcaption');
            tagBtn = elem.find('.joms-popup__btn-tag-photo');

            elem.on( 'click', '.mfp-arrow-left', prev );
            elem.on( 'click', '.mfp-arrow-right', next );
            elem.on( 'click', '.joms-popup__btn-tag-photo', tagPrepare );
            elem.on( 'click', '.joms-popup__btn-comments', toggleComments );
            elem.on( 'click', '.joms-popup__btn-comments .joms-icon', toggleComments );
            elem.on( 'click', '.joms-popup__btn-option', toggleDropdown );
            elem.on( 'click', '.joms-popup__btn-share', share );
            elem.on( 'click', '.joms-popup__btn-download', download );
            elem.on( 'click', '.joms-popup__btn-report', report );
            elem.on( 'click', '.joms-popup__btn-upload', upload );
            elem.on( 'click', '.joms-popup__btn-cover', setAsCover );
            elem.on( 'click', '.joms-popup__btn-profile', setAsProfilePicture );
            elem.on( 'click', '.joms-popup__btn-delete', _delete );
            elem.on( 'click', '.joms-popup__btn-move', moveToAnotherAlbum );
            elem.on( 'click', '.joms-popup__btn-like', like );
            elem.on( 'click', '.joms-popup__btn-dislike', dislike );
            elem.on( 'click', '.joms-popup__btn-rotate-left', rotateLeft );
            elem.on( 'click', '.joms-popup__btn-rotate-right', rotateRight );
            elem.on( 'mouseleave', '.joms-popup__dropdown--wrapper', hideDropdown );
            elem.on( 'click', '.joms-js--remove-tag', tagRemove );
            elem.on( 'click', '.joms-js--btn-desc-edit', editDescription );
            elem.on( 'click', '.joms-js--btn-desc-cancel', cancelDescription );
            elem.on( 'click', '.joms-js--btn-desc-save', saveDescription );

            // Hook arrow keys.
            $( document ).off('keyup.photomodal').on( 'keyup.photomodal', function( e ) {
                var key = e.keyCode;
                if ( key === 37 || key === 39 ) {
                    if ( key === 37 && index > 0 ) {
                        prev();
                    } else if ( key === 39 && index < list.length - 1 ) {
                        next();
                    }
                }
            });

            // Unhook arrow keys on close.
            popup.st.callbacks || (popup.st.callbacks = {});
            popup.st.callbacks.close = function() {
                $( document ).off('keyup.photomodal');
            };

            // In case no ID is profided.
            if ( !id ) {
                id = json.list[ json.index ].id;
            }

            fetchComments( id );
            toggleArrows();
            preloadNeighbourImages();
        }
    });
}

// Image load timer.
var loadImageTimer;
var loadSpinnerTimer;

// Image loader.
function loadImage( img, url ) {
    clearTimeout( loadImageTimer );
    clearTimeout( loadSpinnerTimer );

    img.hide();
    img.removeAttr('src');

    loadSpinnerTimer = setTimeout(function() {
        spinner.show();
    }, 100 );

    loadImageTimer = setTimeout(function() {
        $('<img>').load(function() {
            clearTimeout( loadSpinnerTimer );
            spinner.hide();
            img.attr( 'src', url );
            img.show();
        }).attr( 'src', url );
    }, 1 );
}

function prev() {
    index--;
    (index < 0) && (index = list.length - 1);
    id = list[index].id;
    loadImage( img, list[index].url );
    caption.html( albumName + ' <span class="joms-popup__optcapindex">' + ( index + 1 ) + ' ' + window.joms_lang.COM_COMMUNITY_OF + ' ' + list.length + '</span>' );
    tagCancel();
    fetchComments( id );
    toggleArrows();
    preloadNeighbourImages();
}

function next() {
    index++;
    (index >= list.length) && (index = 0);
    id = list[index].id;
    loadImage( img, list[index].url );
    caption.html( albumName + ' <span class="joms-popup__optcapindex">' + ( index + 1 ) + ' ' + window.joms_lang.COM_COMMUNITY_OF + ' ' + list.length + '</span>' );
    tagCancel();
    fetchComments( id );
    toggleArrows();
    preloadNeighbourImages();
}

function toggleArrows() {
    var noprev = index <= 0,
        nonext = index >= list.length - 1;

    elem.find('.mfp-arrow-left')[ noprev ? 'hide' : 'show' ]();
    elem.find('.mfp-arrow-right')[ nonext ? 'hide' : 'show' ]();
}

function preloadNeighbourImages() {
    var noprev = index <= 0,
        nonext = index >= list.length - 1,
        img;

    if ( !noprev ) {
        img = new Image();
        img.src = list[ index - 1 ].url;
    }

    if ( !nonext ) {
        img = new Image();
        img.src = list[ index + 1 ].url;
    }
}

function tagPrepare() {
    if ( tagBtn.data('tagging') ) {
        tagCancel();
        return;
    }

    tagBtn.data( 'tagging', 1 );

    elem.find( '.joms-phototag__tags' ).hide();
    elem.children('.joms-popup--photo').addClass('joms-popup--phototag');
    img.off('click.phototag').on( 'click.phototag', tagStart );
    img.addClass('joms-phototag__image');
    elem.find('.joms-popup__btn-tag-photo').html( iconTag + ' ' + lang.done_tagging );
}

function tagStart( e ) {
    var indices = joms._.map( tags, function( item ) {
        return item.userId + '';
    });

    joms.util.phototag.create( e, indices, false, groupId, eventId );
    joms.util.phototag.on( 'tagAdded', tagAdded );
    joms.util.phototag.on( 'destroy', function() {
        tagBtn.removeData('tagging');
        elem.children('.joms-popup--photo').removeClass('joms-popup--phototag');
        img.off('click.phototag');
        img.removeClass('joms-phototag__image');
        elem.find('.joms-popup__btn-tag-photo').html( iconTag + ' <span class="joms-popup__btn-overlay">' + lang.tag_photo + '</span>' );
    });

    elem.children('.joms-popup--photo').removeClass('joms-popup--phototag');
    img.off('click.phototag');
}

function tagAdded( userId, y, x, w, h ) {
    joms.ajax({
        func: 'photos,ajaxAddPhotoTag',
        data: [ id, userId, x, y, w, h ],
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
    joms.util.phototag.destroy();
}

function tagRemove( e ) {
    var el = $( e.currentTarget ),
        userId = el.data('id');

    joms.ajax({
        func: 'photos,ajaxRemovePhotoTag',
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
        tags = [];
    }

    joms.util.phototag.populate( img, tags, 'page' );

    if ( !tags.length ) {
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

function toggleComments( e ) {
    e.stopPropagation();
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
    var html = '',
        like = '',
        count, isPhotoOwner;

    json || (json = {});
    isPhotoOwner = json.is_photo_owner;

    // Dropdown.
    if ( enableSharing ) {
        html += '<a href="javascript:" class="joms-popup__btn-share">' + lang.share + '</a>';
    }

    if ( enableDownload ) {
        html += '<a href="javascript:" class="joms-popup__btn-download">' + lang.download + '</a>';
    }

    if ( isOwner || isAdmin || isPhotoOwner ) {
        html += ( enableSharing || enableDownload ? '<div class="sep"></div>' : '' );
        html += ( isOwner ? '<a href="javascript:" class="joms-popup__btn-upload">' + lang.upload_photos + '</a>' : '' );
        html += ( isOwner ? '<div class="sep"></div>' : '' );
        html += ( isOwner ? '<a href="javascript:" class="joms-popup__btn-profile">' + lang.set_as_profile_picture + '</a>' : '' );
        html += ( isOwner || isAdmin ? '<a href="javascript:" class="joms-popup__btn-cover">' + lang.set_as_album_cover + '</a>' : '' );
        html += '<a href="javascript:" class="joms-popup__btn-delete">' + lang.delete_photo + '</a>';
        html += ( canMovePhoto || isPhotoOwner ? '<a href="javascript:" class="joms-popup__btn-move">' + lang.move_to_another_album + '</a>' : '' );
        html += ( canMovePhoto ? '<div class="sep"></div>' : '' );
        html += ( canMovePhoto ? '<a href="javascript:" class="joms-popup__btn-rotate-left">' + lang.rotate_left + '</a>' : '' );
        html += ( canMovePhoto ? '<a href="javascript:" class="joms-popup__btn-rotate-right">' + lang.rotate_right + '</a>' : '' );
    } else {
        html += ( canDelete ? '<a href="javascript:" class="joms-popup__btn-delete">' + lang.delete_photo + '</a>' : '' );
        html += ( enableReporting ? '<a href="javascript:" class="joms-popup__btn-report">' + lang.report + '</a>' : '' );
    }

    html = '<div class="joms-popup__dropdown"><div class="joms-popup__ddcontent">' + html + '</div></div>';

    // Like.
    if ( enableLike && json && json.like ) {
        like += '<button class="joms-popup__btn-like joms-js--like-photo-' + id + ( json.like.is_liked ? ' liked' : '' ) + '"';
        like += ' onclick="joms.api.page' + ( json.like.is_liked ? 'Unlike' : 'Like' ) + '(\'photo\', \'' + id + '\');"';
        like += ' data-lang="' + ( json.like.lang || 'Like' ) + '"';
        like += ' data-lang-like="' + ( json.like.lang_like || 'Like' ) + '"';
        like += ' data-lang-liked="' + ( json.like.lang_liked || 'Liked' ) + '">';
        like += iconThumbsUp + ' ';
        like += '<span>';
        like += ( json.like.is_liked ? json.like.lang_liked : json.like.lang_like );

        count = +json.like.count;
        if ( count > 0 ) {
            like += ' (' + count + ')';
        }

        like += '</span></button>';
    }

    elem.find('.joms-popup__dropdown').replaceWith( html );
    elem.find('.joms-popup__btn-like').replaceWith( like );
}

function share() {
    joms.api.pageShare( photoUrl.replace( '___photo_id___', id ) );
}

function download() {
    window.open( list[index].original );
}

function report() {
    joms.api.photoReport( userId, photoUrl.replace( '___photo_id___', id ) );
}

function upload() {
    joms.api.photoUpload( album );
}

function setAsCover() {
    joms.ajax({
        func: 'photos,ajaxConfirmDefaultPhoto',
        data: [ album, id ],
        callback: function( json ) {
            if ( json.error ) {
                window.alert( stripTags( json.error ) );
                return;
            }

            if ( window.confirm( stripTags( json.message ) ) ) {
                setAsCoverConfirm();
            }
        }
    });
}

function setAsCoverConfirm() {
    joms.ajax({
        func: 'photos,ajaxSetDefaultPhoto',
        data: [ album, id ],
        callback: function( json ) {
            window.alert( stripTags( json.error || json.message ) );
        }
    });
}

function setAsProfilePicture() {
    joms.ajax({
        func: 'photos,ajaxLinkToProfile',
        data: [ id ],
        callback: function( json ) {
            var form, prop;

            if ( json.error ) {
                window.alert( stripTags( json.error ) );
                return;
            }

            if ( window.confirm( stripTags( json.message ) ) ) {
                json.formParams || (json.formParams = {});
                form = $('<form method=post action="' + json.formUrl + '" style="width:1px; height:1px; position:absolute"/>');
                for ( prop in json.formParams ) {
                    form.append('<input type=hidden name="' + prop + '" value="' + json.formParams[prop] + '"/>');
                }

                form.appendTo( document.body );
                form[0].submit();
            }
        }
    });
}

function _delete() {
    joms.ajax({
        func: 'photos,ajaxConfirmRemovePhoto',
        data: [ id ],
        callback: function( json ) {
            if ( json.error ) {
                window.alert( stripTags( json.error ) );
                return;
            }

            if ( window.confirm( stripTags( json.message ) ) ) {
                _deleteConfirm();
            }
        }
    });
}

function _deleteConfirm() {
    joms.ajax({
        func: 'photos,ajaxRemovePhoto',
        data: [ id ],
        callback: function( json ) {
            if ( json.error ) {
                window.alert( stripTags( json.error ) );
                return;
            }

            elem.off();
            popup.close();
            window.location.reload();
        }
    });
}

function moveToAnotherAlbum() {
    joms.api.photoSetAlbum( id );
}

function like() {

}

function dislike() {

}

function rotateLeft() {
    rotate('left');
}

function rotateRight() {
    rotate('right');
}

function rotate( direction ) {
    var id = list[index] && list[index].id;
    if ( !id ) return;

    joms.ajax({
        func: 'photos,ajaxRotatePhoto',
        data: [ id, direction ],
        callback: function( json ) {
            joms._.extend(list[index], json || {});
            img.attr( 'src', list[index].url );
        }
    });
}

function stripTags( html ) {
    html = html.replace( /<\/?[^>]+>/g, '' );
    return html;
}

function buildHtml( json ) {
    var sliderHtml, commentHtml,
        caption = '';

    json || (json = {});
    sliderHtml  = json.error || '';
    commentHtml = json.error ? '' : (json.commentHtml || '');

    if ( !json.error ) {
        list       = json.list || [];
        index      = json.index || 0;
        index      = Math.min( list.length, index );
        sliderHtml = '<img src="' + list[index].url + '" data-index="' + index + '"><div class="joms-spinner" style="display:none"></div>';
        caption    = albumName + ' <span class="joms-popup__optcapindex">' + ( index + 1 ) + ' ' + window.joms_lang.COM_COMMUNITY_OF + ' ' + list.length + '</span>';
    }

    return [
        '<div class="joms-popup joms-popup--photo">',
        '<div class="joms-popup__commentwrapper">',
        '<div class="joms-popup__content">',
        (list && ( list.length > 1 ) ? '<button class="mfp-arrow mfp-arrow-left" type="button" title="' + lang.prev + '"></button>' : ''),
        (list && ( list.length > 1 ) ? '<button class="mfp-arrow mfp-arrow-right" type="button" title="' + lang.next + '"></button>' : ''),
        sliderHtml,
        '<div class="joms-popup__option clearfix">',
        '<div class="joms-popup__optcaption">', ( caption || 'Untitled' ), '</div>',
        '<div class="joms-popup__optoption">',
        '<button class="joms-popup__btn-viewalbum" onclick="window.location=\'', albumUrl, '\'">', iconNewspaper, ' <span class="joms-popup__btn-overlay">', lang.view_album, '</span></button>',
        '<button class="joms-popup__btn-comments">', iconBubble, ' <span class="joms-popup__btn-overlay">', lang.comments, '</span></button>',
        '<button class="joms-popup__btn-like"></button>',
        ( canTag ? '<button class="joms-popup__btn-tag-photo">' + iconTag + ' <span class="joms-popup__btn-overlay">' + lang.tag_photo + '</span></button>' : '' ),
        '<div class="joms-popup__dropdown--wrapper"><div class="joms-popup__dropdown"></div><button class="joms-popup__btn-option">', iconCog, ' <span class="joms-popup__btn-overlay">', lang.options, '</span></button></div>',
        '</div>',
        '</div>',
        '</div>',
        '<div class="joms-popup__comment">', commentHtml, '</div>',
        '<button class="mfp-close" type="button" title="Close (Esc)">×</button>',
        '</div>',
        '</div>'
    ].join('');
}

function buildErrorHtml( json ) {
    json || (json = {});
    json.title || (json.title = '&nbsp;');

    return [
        '<div class="joms-popup joms-popup--whiteblock">',
        '<div class="joms-popup__title"><button class="mfp-close" type="button" title="Close (Esc)">×</button>', json.title, '</div>',
        '<div class="joms-popup__content joms-popup__content--single">', json.error, '</div>',
        '</div>'
    ].join('');
}

function fetchComments( id, showAllParams ) {
    var comments = elem.find('.joms-popup__comment');

    if ( !showAllParams ) {
        comments.empty();
    }

    joms.ajax({
        func: 'photos,ajaxSwitchPhotoTrigger',
        data: [ id, showAllParams ? 1 : 0 ],
        callback: function( json ) {
            var $tags;

            if ( !showAllParams ) {
                if ( json.comments && json.showall ) {
                    json.showall = '<div class="joms-comment__more joms-js--more-comments"><a href="javascript:">' + json.showall + '</a></div>';
                    json.comments = $( $.trim( json.comments ) );
                    json.comments.prepend( json.showall );
                }
            }

            if ( showAllParams ) {
                comments.find('.joms-comment').replaceWith( json.comments );
            } else {
                comments.html( json.head || '' );
                comments.append( json.comments );
                comments.append( json.form || '' );

                // Cache tag info.
                tags = json.tagged || [];
                tagLabel = json.tagLabel || '';
                tagRemoveLabel = json.tagRemoveLabel || '';

                // Render description.
                comments.find('.joms-js--description').html(
                    renderDescription( json.description || {} )
                );

                // Render tag info.
                $tags = comments.find('.joms-js--tag-info');
                $tags.html( _tagBuildHtml() );

                comments
                    .find('.joms-js--comments,.joms-js--newcomment')
                    .find('textarea.joms-textarea');

                joms.fn.tagging.initInputbox();
            }

            updateDropdownHtml( json );
            initVideoPlayers();
        }
    });
}

function initVideoPlayers() {
    var cssInitialized = '.joms-js--initialized',
        cssVideos = '.joms-js--video',
        videos = $('.joms-comment__body,.joms-js--inbox').find( cssVideos ).not( cssInitialized );

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
    if ( typeof json !== 'object' ) {
        json = {};
    }

    return [
        '<div class="joms-js--btn-desc-content">', ( json.content || '' ), '</div>',
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
        func: 'photos,ajaxSaveCaption',
        data: [ id, value ],
        callback: function( json ) {
            var a = button.find('a');

            if ( json.error ) {
                window.alert( json.error );
                return;
            }

            if ( json.success ) {
                editor.hide();
                content.html( json.caption ).show();
                a.html( a.data( 'lang-' + ( value ? 'edit' : 'add' ) ) );
                button.show();
            }
        }
    });
}

// Exports.
return joms._.debounce(function( album, id ) {
    joms.util.popup.prepare(function( mfp ) {
        render( mfp, album, id );
    });
}, 200 );

});
