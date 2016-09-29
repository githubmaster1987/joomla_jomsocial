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

$count = count($photos);
$page = 0;
$i = 0;

?>
<div class="photoalbumlist">
<?php if ( $count > 0 ) { ?>

    <?php

        foreach ($photos as $p) {
            if ( $i % 12 == 0 ) {

    ?><ul class="joms-gallery joms-js--photos-page-<?php echo ++$page; ?>" <?php echo $page > 1 ? 'style="display:none"' : '' ?>><?php }
    ?><li class="joms-gallery__item joms-js--select-photo" data-photo="<?php echo $p->id; ?>">
            <div class="joms-gallery__thumbnail" style="cursor:pointer">
                <a href="javascript:" class="joms-relative">
                    <img src="<?php echo $p->getThumbURI(); ?>" alt="thumbnail" >
                </a>
            </div>
        </li><?php if ( ($i % 12 == 11) || ($i == $count - 1) ) {
    ?></ul><?php }
    ?><?php $i++; ?><?php

        }

    ?>

<?php } ?>
</div>

<?php $split = 5; if ( $page > 1 ) { ?>
<div class="joms-pagination" style="margin-top:15px">
    <ul class="pagination-list joms-js--photos-pagination">
        <li><a href="javascript:" data-page="1" title="<?php echo JText::_('COM_COMMUNITY_FIRST') ?>"><i class="icon-first"></i></a></li
        ><li><a href="javascript:" data-page="-1" title="<?php echo JText::_('COM_COMMUNITY_PREV') ?>"><i class="icon-previous"></i></a></li
        ><?php for ( $i = 1; $i <= $page; $i++) {

            $pageGroupId = ceil( $i / $split );

        ?><?php if ( $i % $split === 1 && $i > 1 ) {
        ?><li class="hidden-phone joms-js--photos-pg joms-js--photos-pg-<?php echo $pageGroupId ?>"<?php if ( $i > $split ) echo ' style="display:none"' ?>><a href="javascript:" data-page-group="<?php echo $pageGroupId - 1; ?>" data-page="<?php echo $i - 1; ?>" title="<?php echo $i - 1; ?>">&hellip;</a></li
        ><?php }
        ?><li class="hidden-phone joms-js--photos-pg joms-js--photos-pg-<?php echo $pageGroupId ?> joms-js--photos-p<?php echo $i; ?> <?php echo $i === 1 ? 'active' : '' ?>"<?php if ( $i > $split ) echo ' style="display:none"' ?>><a href="javascript:" data-page-group="<?php echo $pageGroupId; ?>" data-page="<?php echo $i; ?>" title="<?php echo $i; ?>"><?php echo $i; ?></a></li
        ><?php if ( $i % $split === 0 && $i < $page ) {
        ?><li class="hidden-phone joms-js--photos-pg joms-js--photos-pg-<?php echo $pageGroupId ?>"<?php if ( $i > $split ) echo ' style="display:none"' ?>><a href="javascript:" data-page-group="<?php echo $pageGroupId + 1; ?>" data-page="<?php echo $i + 1; ?>" title="<?php echo $i + 1; ?>">&hellip;</a></li
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
            $ct = joms.jQuery('.joms-js--photos-pagination');

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

            $ct.children( '.joms-js--photos-pg' ).not( '.joms-js--photos-pg-' + group ).hide();
            $ct.children( '.joms-js--photos-pg-' + group ).show();
            $ct.children( '.active' ).removeClass('active');
            $ct.children( '.joms-js--photos-p' + page ).addClass('active');

            page = Math.max( 1, Math.min( page, maxPage ) );
            joms.jQuery( '.joms-js--photos-page-' + page ).show()
                .siblings().hide();
        });

    })();
</script>
<?php } ?>

<!-- back to album button -->
<div style="padding-top:10px">
    <a class="joms-button--neutral joms-js--back-to-album" href="javascript:">&laquo; <?php echo JText::_('COM_COMMUNITY_PHOTOS_BACK_TO_ALBUM'); ?></a>
</div>
