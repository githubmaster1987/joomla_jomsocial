<?php
/**
 * @copyright (C) 2013 iJoomla, Inc. - All rights reserved.
 * @license GNU General Public License, version 2 (http://www.gnu.org/licenses/gpl-2.0.html)
 * @author iJoomla.com <webmaster@ijoomla.com>
 * @url https://www.jomsocial.com/license-agreement
 * The PHP code portions are distributed under the GPL license. If not otherwise stated, all images, manuals, cascading style sheets, and included JavaScript *are NOT GPL, and are released under the IJOOMLA Proprietary Use License v1.0
 * More info at https://www.jomsocial.com/license-agreement
 */
defined('_JEXEC') or die('Restricted access');
?>
<div class="alert alert-info">
    <?php echo JText::_('COM_COMMUNITY_DIGEST_PENDING_LIST_INFO');?>
</div>

<div class="joms-js--digestpending">
<?php if(count($this->pendingList) > 0){ ?>
    <?php foreach($this->pendingList as $item){ ?>
    <div>
        <?php echo "Username : ".$item->username.", Email : ".$item->email; ?>
        <button data-id="<?php echo $item->id ?>" data-last-visit="<?php echo $item->lastVisitDate; ?>"><?php echo JText::_('COM_COMMUNITY_DIGEST_PREVIEW') ?></button>
    </div>
    <?php } ?>
<?php } ?>
</div>

<script>
    (function() {
        var now = new Date('<?php echo date("Y-m-d H:i:s"); ?>');

        function init() {
            jQuery('.joms-js--digestpending').on('click', 'button', function() {
                var $btn = jQuery( this ),
                    id = $btn.data('id'),
                    lastVisit = $btn.data('last-visit'),
                    inactive;

                inactive = (new Date(lastVisit)) - now;
                inactive = Math.round( Math.abs( inactive ) / ( 1000 * 60 * 60 * 24 ));

                cWindowShow('jax.call("community", "admin,digest,ajaxGetPreview", ' + id + ', ' + inactive + ');', '&nbsp;' , 800 , 450 );
                jQuery('#js-cpanel .modal').css({
                    width: 800,
                    marginLeft: 0,
                    left: '50%',
                    transform: 'translate(-50%, 0)'
                });
            });
        }

        var timer = setInterval(function() {
            if ( window.jQuery ) {
                clearInterval( timer );
                init();
            }
        }, 1000 );
    })();
</script>
