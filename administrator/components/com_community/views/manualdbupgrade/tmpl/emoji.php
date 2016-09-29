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
<div id="messaging-form">
    <div class="alert alert-info">
        <?php echo JText::_('COM_COMMUNITY_EMOJI_DESC'); ?>
    </div>
    <div class="row-fluid">
        <div class="span24">
            <div class="widget-box">
                <div class="widget-header widget-header-flat">
                    <h5><?php echo JText::_('COM_COMMUNITY_EMOJI_DATABASE_UPGRADE_CHECK'); ?></h5>
                </div>
                <div class="widget-body">
                    <div class="widget-main">
                        <?php if($this->emojiDatabaseInfo[0][1] == JText::_('COM_COMMUNITY_EMOJI_PASSED')){
                            $needUpgrade = false;
                            ?>
                            <table>
                                <?php
                                foreach($this->emojiDatabaseInfo as $info){
                                    $needUpgrade = ($info[1] == JText::_('COM_COMMUNITY_EMOJI_UPDATE_READY')) ? true : $needUpgrade;
                                    ?>
                                    <tr>
                                        <td class="key">
                                            <?php echo $info[0]; ?> &nbsp;
                                        </td>
                                        <td class="info-status">
                                            <?php
                                                $className = '';
                                                if ( $info[1] == JText::_('COM_COMMUNITY_EMOJI_PASSED') ) {
                                                    $className = 'label-success';
                                                } else if ( $info[1] == JText::_('COM_COMMUNITY_EMOJI_FAILED') ) {
                                                    $className = 'label-important';
                                                } else if ( $info[1] == JText::_('COM_COMMUNITY_EMOJI_UPDATE_UPGRADED') ) {
                                                    $className = 'label-success';
                                                } else if ( $info[1] == JText::_('COM_COMMUNITY_EMOJI_CANNOT_UPGRADE') ) {
                                                    $className = 'label-important';
                                                } else if ( $info[1] == JText::_('COM_COMMUNITY_EMOJI_UPDATE_READY') ) {
                                                    $className = 'label-warning joms-js--status';
                                                }
                                            ?>
                                            <small>
                                                <span class="label <?php echo $className; ?>"><?php echo $info[1]; ?></span>
                                            </small>
                                        </td>
                                    </tr>
                                <?php } ?>
                            </table>

                            <?php if($needUpgrade){?>
                                <button class="btn btn-primary joms-js--btn-upgrade">
                                    <?php echo JText::_('COM_COMMUNITY_EMOJI_UPGRADE_NOW'); ?>
                                </button>
                            <?php } ?>
                            <div class="upgrade-success alert alert-success" style="display: none">
                                <?php echo JText::_('COM_COMMUNITY_EMOJI_UPGRADE_SUCCESS'); ?>
                            </div>
                            <div class="upgrade-failed alert alert-danger" style="display: none">
                                <?php echo JText::_('COM_COMMUNITY_EMOJI_UPGRADE_FAILED'); ?>
                            </div>
                        <?php }else{ ?>
                            <div class="alert alert-warning">
                                <?php echo JText::_('COM_COMMUNITY_EMOJI_INCOMPATIBLE'); ?>
                            </div>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
jQuery(function( $ ) {
    var textUpgrading = '<?php echo JText::_("COM_COMMUNITY_EMOJI_UPGRADING", TRUE); ?>';

    $('.joms-js--btn-upgrade').on('click', function() {
        var $btn = $( this );
        $btn.text( textUpgrading );
        $btn.attr('disabled', 'disabled');
        jax.call('community' , 'admin,manualdbupgrade,ajaxUpgradeEmojiDB');
    });
});

function response(status) {
    var textMessage = '<?php echo JText::_("MESSAGE", TRUE); ?>';
    var textSuccess = '<?php echo JText::_("COM_COMMUNITY_EMOJI_UPGRADE_SUCCESS", TRUE); ?>';
    var textFailed = '<?php echo JText::_("COM_COMMUNITY_EMOJI_UPGRADE_FAILED", TRUE); ?>';
    var textUpgradeNow = '<?php echo JText::_("COM_COMMUNITY_EMOJI_UPGRADE_NOW", TRUE); ?>';

    var $message = jQuery('#system-message-container').hide();
    $message.html([
        '<button type="button" class="close" data-dismiss="alert">×</button>',
        '<div class="alert alert-success">',
        '<h4 class="alert-heading">', textMessage, '</h4>',
        '<p class="alert-message"></p>',
        '</div>'
    ].join(''));

    var $btn = jQuery('.joms-js--btn-upgrade');

    if (status) {
        $btn.hide();
        $message.find('.alert').attr('class', 'alert alert-success');
        $message.find('.alert-message').html( textSuccess );
        $message.show();

        // update status
        jQuery('#requirements').find('.joms-js--status').each(function() {
            var $span = $(this);
            $span.attr('class', 'label label-success');
            $span.text( textSuccess );
        })
    } else {
        $btn.text( textUpgradeNow ).removeAttr('disabled').show();
        $message.find('.alert').attr('class', 'alert alert-error');
        $message.find('.alert-message').html( textFailed );
        $message.show();
    }
}

</script>
