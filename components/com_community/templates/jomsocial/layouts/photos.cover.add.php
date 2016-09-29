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

$count = count($albums);
$page = 0;
$i = 0;

?>

<div class="joms-popup__content">
    <?php if ($enablealbums) { ?>
    <div class="joms-tab__bar">
        <a href="#joms-tab--photo"><?php echo JText::_(
            $type === 'group' ? 'COM_COMMUNITY_PHOTOS_ALL_GROUP_PHOTOS' : (
                $type === 'event' ? 'COM_COMMUNITY_PHOTOS_ALL_EVENT_PHOTOS' : 'COM_COMMUNITY_PHOTOS_ALBUMS_LABEL'
            )
        ); ?></a>
        <a href="#joms-tab--cover" class="active"><?php echo JText::_('COM_COMMUNITY_PHOTOS_UPLOAD_PHOTOS'); ?></a>
    </div>
    <?php } ?>

    <?php if ($enablealbums) { ?>
    <!-- Photo album. -->
    <div id="joms-tab--photo" class="joms-tab__content" style="display:none;">
        <div class="joms-js--photo-list" style="display:none;"></div>
        <div class="joms-js--album-list">

            <div>
            <?php if ( $count > 0 ) { ?>

                <?php

                    foreach ($albums as $album) {
                        if ( $i % 8 == 0 ) {

                ?><ul class="joms-gallery joms-js--albums-page-<?php echo ++$page; ?>" <?php echo $page > 1 ? 'style="display:none"' : '' ?>><?php }
                ?><li class="joms-gallery__item joms-js--album" data-album="<?php echo $album->id ?>" data-total="<?php echo $album->total_photo; ?>">
                        <div class="joms-gallery__thumbnail" style="cursor:pointer">
                            <a href="javascript:" class="joms-relative">
                                <img src="<?php echo $album->thumbnail; ?>" title="<?php echo $this->escape( $album->name ); ?>">
                            </a>
                        </div>
                        <div class="joms-gallery__body">
                            <a href="javascript:" class="joms-gallery__title" title="<?php echo $this->escape( $album->name ); ?>">
                                <?php echo $this->escape( $album->name ); ?>
                            </a>
                            <div class="joms-gallery__status">
                                <?php echo $album->total_photo . ' ' . JText::_('COM_COMMUNITY_NOTIFICATIONGROUP_PHOTOS'); ?>
                            </div>
                        </div>
                    </li><?php if ( ($i % 8 == 7) || ($i == $count - 1) ) {
                ?></ul><?php }
                ?><?php $i++; ?><?php

                    }

                ?>

            <?php } else { ?>
                <div class="alert alert-info" align="center"><?php echo JText::_('COM_COMMUNITY_PHOTOS_NO_ALBUM_CREATED') ?></div>
            <?php } ?>
            </div>

            <?php $split = 5; if ( $page > 1 ) { ?>
            <div class="joms-pagination" style="margin-top:15px">
                <ul class="pagination-list joms-js--albums-pagination">
                    <li><a href="javascript:" data-page="1" title="<?php echo JText::_('COM_COMMUNITY_FIRST') ?>"><i class="icon-first"></i></a></li
                    ><li><a href="javascript:" data-page="-1" title="<?php echo JText::_('COM_COMMUNITY_PREV') ?>"><i class="icon-previous"></i></a></li
                    ><?php for ( $i = 1; $i <= $page; $i++) {

                        $pageGroupId = ceil( $i / $split );

                    ?><?php if ( $i % $split === 1 && $i > 1 ) {
                    ?><li class="hidden-phone joms-js--albums-pg joms-js--albums-pg-<?php echo $pageGroupId ?>"<?php if ( $i > $split ) echo ' style="display:none"' ?>><a href="javascript:" data-page-group="<?php echo $pageGroupId - 1; ?>" data-page="<?php echo $i - 1; ?>" title="<?php echo $i - 1; ?>">&hellip;</a></li
                    ><?php }
                    ?><li class="hidden-phone joms-js--albums-pg joms-js--albums-pg-<?php echo $pageGroupId ?> joms-js--albums-p<?php echo $i; ?> <?php echo $i === 1 ? 'active' : '' ?>"<?php if ( $i > $split ) echo ' style="display:none"' ?>><a href="javascript:" data-page-group="<?php echo $pageGroupId; ?>" data-page="<?php echo $i; ?>" title="<?php echo $i; ?>"><?php echo $i; ?></a></li
                    ><?php if ( $i % $split === 0 && $i < $page ) {
                    ?><li class="hidden-phone joms-js--albums-pg joms-js--albums-pg-<?php echo $pageGroupId ?>"<?php if ( $i > $split ) echo ' style="display:none"' ?>><a href="javascript:" data-page-group="<?php echo $pageGroupId + 1; ?>" data-page="<?php echo $i + 1; ?>" title="<?php echo $i + 1; ?>">&hellip;</a></li
                    ><?php }
                    ?><?php }
                    ?><li><a href="javascript:" data-page="+1" title="<?php echo JText::_('COM_COMMUNITY_NEXT') ?>"><i class="icon-next"></i></a></li
                    ><li><a href="javascript:" data-page="<?php echo $page; ?>" title="<?php echo JText::_('COM_COMMUNITY_LAST') ?>"><i class="icon-last"></i></a></li>
                </ul>
            </div>
            <script>
                (function() {
                    var page = 1,
                        maxPage = <?php echo $page ?>,
                        $ct = joms.jQuery('.joms-js--albums-pagination');

                    $ct.on( 'click', 'a', function() {
                        var $a = joms.jQuery( this ),
                            data = $a.data('page') + '',
                            group = +$a.data('page-group');

                        if ( data.match(/^[-+]/) ) {
                            data = +data;
                            page += data;
                        } else {
                            page = data;
                        }

                        $ct.children( '.joms-js--albums-pg' ).not( '.joms-js--albums-pg-' + group ).hide();
                        $ct.children( '.joms-js--albums-pg-' + group ).show();
                        $ct.children( '.active' ).removeClass('active');
                        $ct.children( '.joms-js--albums-p' + page ).addClass('active');

                        page = Math.max( 1, Math.min( page, maxPage ) );
                        joms.jQuery( '.joms-js--albums-page-' + page ).show()
                            .siblings().hide();
                    });

                })();
            </script>
            <?php } ?>


        </div>

    </div>
    <?php } ?>

    <!-- Upload cover. -->
    <div id="joms-tab--cover" class="joms-tab__content">
        <div class="joms-js--cover-uploader-error joms-alert--warning" style="display:none"></div>
        <form method="POST" enctype="multipart/form-data"
            action="<?php echo CRoute::_('index.php?option=com_community&view=photos&task=ajaxCoverUpload&type=' . ucfirst($type) . '&parentId=' . $parentId); ?>">
            <label class="label-filetype">
                <a class="joms-button--primary joms-button--small joms-button--full" href="javascript:" data-ui-object="popup-button-upload">
                    <?php echo JText::_('COM_COMMUNITY_PHOTOS_UPLOAD_PHOTOS'); ?>
                </a>
                <div style="margin-top:4px;">
                    <div class="joms-progressbar"><div class="joms-progressbar__progress"></div></div>
                </div>
            </label>
        </form>
    </div>

</div>
