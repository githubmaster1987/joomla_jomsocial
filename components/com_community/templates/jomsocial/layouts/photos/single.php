<?php
/**
 * @copyright (C) 2013 iJoomla, Inc. - All rights reserved.
 * @license GNU General Public License, version 2 (http://www.gnu.org/licenses/gpl-2.0.html)
 * @author iJoomla.com <webmaster@ijoomla.com>
 * @url https://www.jomsocial.com/license-agreement
 * The PHP code portions are distributed under the GPL license. If not otherwise stated, all images, manuals, cascading style sheets, and included JavaScript *are NOT GPL, and are released under the IJOOMLA Proprietary Use License v1.0
 * More info at https://www.jomsocial.com/license-agreement
 */
defined('_JEXEC') or die();

$enableReporting = false;
if ( $config->get('enablereporting') == 1 && ( $my->id > 0 || $config->get('enableguestreporting') == 1 ) ) {
    $enableReporting = true;
}

$canEdit =
    (!CAlbumsHelper::isFixedAlbum($album)) &&
    (
        (COwnerHelper::isCommunityAdmin()) ||
        ($album->creator == $my->id) ||
        (isset($groupId) && $groupId && $my->authorise('community.create', 'groups.photos.' . $groupId))
    );

?>

<div class="joms-page">
    <div class="joms-js--photo-info">
        <div class="joms-stream__header" style="padding:0; visibility:hidden">
            <div class="joms-avatar--stream">
                <a href="javascript:"><img></a>
            </div>
            <div class="joms-stream__meta">
                <a class="joms-stream__user">&nbsp;</a>
                <span class="joms-stream__time">
                    <small>&nbsp;</small>
                    <svg viewBox="0 0 16 16" class="joms-icon">
                        <use xlink:href="#joms-icon-earth"></use>
                    </svg>
                </span>
            </div>
        </div>
        <div class="joms-stream__body" style="visibility:hidden"></div>
    </div>
    <div class="joms-js--photo-ct" style="position:relative; height:404px; line-height:400px; text-align:center; background: rgba(0,0,0,1);">
        <button title="<?php echo JText::_('COM_COMMUNITY_PHOTOS_PREVIOUS');?>" type="button" class="mfp-arrow mfp-arrow-left joms-js--btn-prev" style="display:none"></button>
        <button title="<?php echo JText::_('COM_COMMUNITY_PHOTOS_NEXT');?>" type="button" class="mfp-arrow mfp-arrow-right joms-js--btn-next" style="display:none"></button>
        <img class="joms-js--photo-image" style="width:auto; height:auto; max-width:100%; max-height:100%; vertical-align:middle;" alt="photo" >
    </div>

    <div class="joms-gap"></div>

    <?php if ($config->get('likes_photo') == 1) { ?>
    <button class="joms-button--neutral joms-button--small joms-js--btn-like" data-lang="<?php echo JText::_('COM_COMMUNITY_LIKE'); ?>">
        <?php echo JText::_('COM_COMMUNITY_LIKE'); ?><?php echo isset( $likeCountHTML ) ? $likeCountHTML : ''; ?>
    </button>
    <?php } ?>

    <?php if ($config->get('enablesharethis') == 1) { ?>
    <button class="joms-button--neutral joms-button--small joms-js--btn-share"><?php echo JText::_('COM_COMMUNITY_SHARE'); ?></button>
    <?php } ?>

    <?php if ($enableReporting) { ?>
    <button class="joms-button--neutral joms-button--small" onclick="joms.api.photoReport('<?php echo $photo->id ?>');"><?php echo JText::_('COM_COMMUNITY_REPORT') ?></button>
    <?php } ?>

    &nbsp;&nbsp;

    <?php if ( COwnerHelper::isMine($my->id, $album->creator) || CFriendsHelper::isConnected($my->id, $album->creator) ) { ?>
    <button class="joms-button--neutral joms-button--small joms-js--btn-tag"><?php echo JText::_('COM_COMMUNITY_TAG_THIS_PHOTO'); ?></button>
    <?php } ?>

    <?php if ( $canEdit ) { ?>
    <button class="joms-button--neutral joms-button--small joms-js--btn-move"><?php echo JText::_('COM_COMMUNITY_MOVE_TO_ANOTHER_ALBUM'); ?></button>
    <?php } ?>

    <?php if ( COwnerHelper::isCommunityAdmin() || ($my->id == $album->creator) ) { ?>
    &nbsp;&nbsp;
    <button class="joms-button--neutral joms-button--small joms-js--btn-rotate-left"><?php echo JText::_('COM_COMMUNITY_PHOTOS_ROTATE_LEFT'); ?></button>
    <button class="joms-button--neutral joms-button--small joms-js--btn-rotate-right"><?php echo JText::_('COM_COMMUNITY_PHOTOS_ROTATE_RIGHT'); ?></button>
    <?php } ?>

    <div class="joms-js--photo-tag-ct"></div>

    <div class="joms-gap"></div>
    <div>
        <h5 class="joms-text--title"><?php echo JText::_('COM_COMMUNITY_PHOTOS_ALBUM_DESC') ?></h5>
        <div class="joms-js--photodesc"></div>
    </div>

    <div class="joms-gap"></div>
    <h5 class="joms-text--title"><?php echo JText::_('COM_COMMUNITY_COMMENTS') ?></h5>
    <div class="joms-stream__status--mobile">
        <a href="javascript:" onclick="joms.api.streamShowComments('<?php echo ''; ?>', 'albums');">
            <span class="joms-comment__counter--<?php echo ''; ?>"></span>
            <svg viewBox="0 0 16 16" class="joms-icon">
                <use xlink:href="#joms-icon-bubble"></use>
            </svg>
        </a>
    </div>
    <div class="joms-js--photo-comment"></div>
</div>

<script>
    (function( w ) {
        var album = '<?php echo $album->id ?>',
            id = '<?php echo $photo->id ?>',
            groupid = '<?php echo $album->groupid > 0 ? $album->groupid : '' ?>',
            eventid = '<?php echo $album->eventid > 0 ? $album->eventid : '' ?>',
            url = '<?php echo CRoute::getExternalURL("index.php?option=com_community&view=photos&task=photo&albumid=" . $album->id . "&photoid=___photo_id___" . ( $album->groupid > 0 ? "&groupid=" . $album->groupid : "" )); ?>',
            img, caption, btnPrev, btnNext, btnTag, btnMove, tags, tagLabel, tagRemoveLabel, list, index, lang, albumName;

        // Replace url.
        url = url.replace( /&amp;/g, '&' );

        function init() {
            joms.jQuery( document ).on( 'click', function( e ) {
                var $ct;
                try {
                    $ct = joms.jQuery( e.target ).closest('.joms-js--photo-ct,.joms-js--btn-tag');
                    $ct.length || tagCancel();
                } catch (e) {}
            });

            joms.ajax({
                func: 'photos,ajaxGetPhotosByAlbum',
                data: [ album, id ],
                callback: function( json ) {
                    json || (json = {});
                    lang = json.lang || {};
                    canEdit = <?php echo ( COwnerHelper::isCommunityAdmin() || ($my->id == $album->creator) ) ? 'true' : 'false' ?>;
                    albumName = json.album_name || 'Untitled';

                    if ( json.error ) {
                        w.alert( json.error );
                        return;
                    }

                    list = json.list || [];
                    index = json.index || [];
                    img = joms.jQuery('.joms-js--photo-image');
                    caption = joms.jQuery('.joms-js--photo-caption');
                    btnPrev = joms.jQuery('.joms-js--btn-prev');
                    btnNext = joms.jQuery('.joms-js--btn-next');
                    btnTag = joms.jQuery('.joms-js--btn-tag');
                    btnMove = joms.jQuery('.joms-js--btn-move');

                    if ( !list[ index ] ) {
                        index = 0;
                    }

                    img.attr( 'src', list[index].url ).data( 'index', index );
                    caption.html( ' - ' + list[index].caption );

                    if( list.length ) {
                        btnPrev.show().on( 'click', prev );
                        btnNext.show().on( 'click', next );
                        btnTag.on( 'click', tagPrepare );
                        btnMove.on( 'click', moveToAnotherAlbum );
                    }

                    fetchComments( id );
                    toggleArrows();
                    preloadNeighbourImages();

                    joms.jQuery('.joms-js--photo-tag-ct').on( 'click', '.joms-js--remove-tag', tagRemove );
                    joms.jQuery('.joms-js--photodesc').on( 'click', '.joms-js--btn-desc-edit', editDescription );
                    joms.jQuery('.joms-js--photodesc').on( 'click', '.joms-js--btn-desc-cancel', cancelDescription );
                    joms.jQuery('.joms-js--photodesc').on( 'click', '.joms-js--btn-desc-save', saveDescription );

                    if ( canEdit ) {
                        joms.jQuery('.joms-js--btn-rotate-left').on( 'click', rotateLeft );
                        joms.jQuery('.joms-js--btn-rotate-right').on( 'click', rotateRight );
                    }

                    joms.jQuery('.joms-js--btn-share').on( 'click', function() {
                        joms.api.pageShare( url.replace( '___photo_id___', id ) );
                    });
                }
            })
        }

        function prev() {
            index--;
            (index < 0) && (index = list.length - 1);
            id = list[index].id;
            img.removeAttr( 'src' );
            setTimeout(function() {
                img.attr( 'src', list[index].url );
            }, 1 );
            caption.html( albumName + ' <span class="joms-popup__optcapindex">' + ( index + 1 ) + ' of ' + list.length + '</span>' );
            tagCancel();
            fetchComments( id );
            toggleArrows();
            preloadNeighbourImages();
        }

        function next() {
            index++;
            (index >= list.length) && (index = 0);
            id = list[index].id;
            img.removeAttr( 'src' );
            setTimeout(function() {
                img.attr( 'src', list[index].url );
            }, 1 );
            caption.html( albumName + ' <span class="joms-popup__optcapindex">' + ( index + 1 ) + ' of ' + list.length + '</span>' );
            tagCancel();
            fetchComments( id );
            toggleArrows();
            preloadNeighbourImages();
        }

        function toggleArrows() {
            var noprev = index <= 0,
                nonext = index >= list.length - 1;

            btnPrev[ noprev ? 'hide' : 'show' ]();
            btnNext[ nonext ? 'hide' : 'show' ]();
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
            if ( btnTag.data('tagging') ) {
                tagCancel();
                return;
            }

            btnTag.data( 'tagging', 1 );

            joms.jQuery( '.joms-phototag__tags' ).hide();
            joms.jQuery('.joms-js--photo-ct').addClass('joms-popup--phototag');
            img.off('click.phototag').on( 'click.phototag', tagStart );
            img.addClass('joms-phototag__image');
            btnTag.html( lang.done_tagging );
        }

        function tagStart( e ) {
            var indices = joms._.map( tags, function( item ) {
                return item.userId + '';
            });

            joms.util.phototag.create( e, indices, 'page', groupid, eventid );
            joms.util.phototag.on( 'tagAdded', tagAdded );
            joms.util.phototag.on( 'destroy', function() {
                btnTag.removeData('tagging');
                joms.jQuery('.joms-js--photo-ct').removeClass('joms-popup--phototag');
                img.off('click.phototag');
                img.removeClass('joms-phototag__image');
                btnTag.html( lang.tag_photo );
            });

            joms.jQuery('.joms-phototag__wrapper').css({ lineHeight: '18px' });
            joms.jQuery('.joms-js--photo-ct').addClass('joms-popup--phototag');
            img.off('click.phototag');
        }

        function tagAdded( userId, y, x, w, h ) {
            joms.ajax({
                func: 'photos,ajaxAddPhotoTag',
                data: [ id, userId, x, y, w, h ],
                callback: function( json ) {
                    var $tags;

                    if ( json.error ) {
                        window.alert( stripTags( json.error ) );
                        return;
                    }

                    if ( json.success ) {
                        tags.push( json.data );

                        // Render tag info.
                        $tags = joms.jQuery('.joms-js--photo-tag-ct');
                        $tags.html( _tagBuildHtml() );
                    }
                }
            });
        }

        function tagCancel() {
            joms.util.phototag.destroy();
        }

        function tagRemove( e ) {
            var el = joms.jQuery( e.currentTarget ),
                userId = el.data('id');

            joms.ajax({
                func: 'photos,ajaxRemovePhotoTag',
                data: [ id, userId ],
                callback: function( json ) {
                    var $tags, i;

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
                        $tags = joms.jQuery('.joms-js--photo-tag-ct');
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
            html = '<div class="joms-gap"></div><strong>' + tagLabel + '</strong><br>' + html;

            return html;
        }

        function fetchComments( id, showAllParams ) {
            var info = joms.jQuery('.joms-js--photo-info'),
                comments = joms.jQuery('.joms-js--photo-comment');

            joms.ajax({
                func: 'photos,ajaxSwitchPhotoTrigger',
                data: [ id, showAllParams ? 1 : 0 ],
                callback: function( json ) {
                    var $info, $tags;

                    if ( !showAllParams ) {
                        if ( json.comments && json.showall ) {
                            json.showall = '<div class="joms-comment__more joms-js--more-comments"><a href="javascript:">' + json.showall + '</a></div>';
                            json.comments = joms.jQuery( joms.jQuery.trim( json.comments ) );
                            json.comments.prepend( json.showall );
                        }
                    }

                    if ( showAllParams ) {
                        comments.find('.joms-comment').replaceWith( json.comments );
                    } else {
                        $info = joms.jQuery( joms.jQuery.trim( json.head || '' ) );
                        $info.filter('.joms-stream__header').css( 'padding', 0 );

                        info.html( $info );
                        comments.html( json.comments ).append( json.form || '' );
                        comments.find('textarea.joms-textarea');
                        joms.fn.tagging.initInputbox();

                        // Render description.
                        joms.jQuery('.joms-js--photodesc').html(
                            renderDescription( json.description || {} )
                        );

                        // Cache tag info.
                        tags = json.tagged || [];
                        tagLabel = json.tagLabel || '';
                        tagRemoveLabel = json.tagRemoveLabel || '';

                        // Render tag info.
                        $tags = joms.jQuery('.joms-js--photo-tag-ct');
                        $tags.html( _tagBuildHtml() );

                    }

                    var $mobile = comments.siblings('.joms-stream__status--mobile');
                    $mobile.find('a').attr( 'onclick', 'joms.api.streamShowComments(\'' + id + '\', \'photos\')');
                    $mobile.find('span').html( json.comments_count || '0' )
                        .attr( 'class', 'joms-comment__counter--' + id );

                    like = '';
                    if ( json && json.like ) {
                        like += '<button class="joms-button--small joms-button--' + ( json.like.is_liked ? 'primary' : 'neutral' ) + ' joms-js--btn-like joms-js--like-photo-' + id + '"';
                        like += ' onclick="joms.api.page' + ( json.like.is_liked ? 'Unlike' : 'Like' ) + '(\'photo\', \'' + id + '\');"';
                        like += ' data-lang="' + ( json.like.lang || 'Like' ) + '"';
                        like += ' data-lang-like="' + ( json.like.lang_like || 'Like' ) + '"';
                        like += ' data-lang-liked="' + ( json.like.lang_liked || 'Liked' ) + '">';
                        like += ( json.like.is_liked ? json.like.lang_liked : json.like.lang_like );

                        count = +json.like.count;
                        if ( count > 0 ) {
                            like += ' (' + count + ')';
                        }

                        like += '</button>';
                    }

                    joms.jQuery('.joms-js--btn-like').replaceWith( like );
                    initVideoPlayers();
                }
            });
        }

        function setAsCover() {
            joms.api.photoSetCover( album, id );
        }

        function setAsProfilePicture() {
            joms.api.photoSetAvatar( id );
        }

        function moveToAnotherAlbum() {
            joms.api.photoSetAlbum( id );
        }

        function initVideoPlayers() {
            var initialized = '.joms-js--initialized',
                cssVideos = '.joms-js--video',
                videos = joms.jQuery('.joms-comment__body,.joms-js--inbox').find( cssVideos ).not( initialized ).addClass( initialized.substr(1) );

            if ( !videos.length ) {
                return;
            }

            joms.loadCSS( joms.ASSETS_URL + 'vendors/mediaelement/mediaelementplayer.min.css' );
            videos.on( 'click.joms-video', cssVideos + '-play', function() {
                var $el = joms.jQuery( this ).closest( cssVideos );
                joms.util.video.play( $el, $el.data() );
            });

            if ( joms.ios ) {
                setTimeout(function() {
                    videos.find( cssVideos + '-play' ).click();
                }, 2000 );
            }
        }

        function stripTags( html ) {
            html = html.replace( /<\/?[^>]+>/g, '' );
            return html;
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
            joms.jQuery('.joms-js--btn-desc-content').hide();
            joms.jQuery('.joms-js--btn-desc-edit').hide();
            joms.jQuery('.joms-js--btn-desc-editor').show();
        }

        function cancelDescription() {
            joms.jQuery('.joms-js--btn-desc-editor').hide();
            joms.jQuery('.joms-js--btn-desc-content').show();
            joms.jQuery('.joms-js--btn-desc-edit').show();
        }

        function saveDescription() {
            var content  = joms.jQuery('.joms-js--btn-desc-content'),
                editor   = joms.jQuery('.joms-js--btn-desc-editor'),
                button   = joms.jQuery('.joms-js--btn-desc-edit'),
                textarea = editor.find('textarea'),
                value    = joms.jQuery.trim( textarea.val() );

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

        // Trigger ini on ready.
        w.joms_queue || (w.joms_queue = []);
        w.joms_queue.push(function() {
            var timer = setInterval(function() {
                if ( joms.ajax ) {
                    clearInterval( timer );
                    init();
                }
            }, 200 );
        });

        w.joms_delete_photo = function() {
            if ( list && list[ index ] ) {
                joms.api.photoRemove( list[ index ].id );
            }
        };

        w.joms_set_as_profile_picture = function() {
            setAsProfilePicture();
        };

        w.joms_set_as_album_cover = function() {
            setAsCover();
        };

        w.joms_download_photo = function() {
            if ( list && list[ index ] ) {
                window.open( list[ index ].original );
            }
        };

    })( window );
</script>
