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
?>

<div class="joms-popup__content">
    <form method="POST" enctype="multipart/form-data"
        action="<?php echo CRoute::_('index.php?option=com_community&view=photos&task=changeAvatar&type=' . $type . '&id=' . $id); ?>">
        <p><?php echo JText::_('COM_COMMUNIT_SELECT_IMAGE_INSTR'); ?></p>
        <div class="joms-js--avatar-uploader-error joms-alert--warning" style="display:none"></div>
        <label class="label-filetype">
            <a class="joms-button--primary joms-button--small joms-button--full joms-js--button-upload" href="javascript:">
                <?php echo JText::_('COM_COMMUNITY_PHOTOS_UPLOAD'); ?>
            </a>
            <div class="joms-progressbar" style="margin-top:4px;"><div class="joms-progressbar__progress"></div></div>
        </label>
    </form>
</div>
<div class="joms-avatar__cropper"<?php echo $img ? '' : ' style="display:none"' ?>>
    <div class="joms-popup__content">
        <div>
            <div class="joms-cropper--info">
                <strong><?php echo JText::_('COM_COMMUNITY_CROP_AVATAR_TITLE'); ?></strong>
                <p><?php echo JText::_('COM_COMMUNITY_CROP_AVATAR_INSTR'); ?></p>
            </div>
            <div class="joms-cropper"><img<?php echo $img ? ' src="' . $img . '" style="visibility:hidden"' : '' ?>></div>
        </div>
    </div>
    <div class="joms-popup__action">
        <div>
            <div class="pull-left">
                <button class="joms-button--neutral joms-button--small joms-js--button-rotate-left"><?php echo JText::_('COM_COMMUNITY_PHOTOS_ROTATE_LEFT'); ?></button>&nbsp;
                <button class="joms-button--neutral joms-button--small joms-js--button-rotate-right"><?php echo JText::_('COM_COMMUNITY_PHOTOS_ROTATE_RIGHT'); ?></button>
            </div>
            <button class="joms-button--primary joms-button--small joms-js--button-save"><?php echo JText::_('COM_COMMUNITY_THUMBNAIL_SAVE'); ?></button>
        </div>
        <div class="clearfix" style="margin-top:4px">
            <div class="pull-left">
                <button class="joms-button--neutral joms-button--small" onclick="window.location = '<?php echo CRoute::_('index.php?option=com_community&view=photos&task=removeAvatar&type=' . $type . '&id=' . $id); ?>';"><?php echo JText::_('COM_COMMUNITY_REMOVE_AVATAR_BUTTON'); ?></button> &nbsp;
            </div>
        </div>
    </div>
</div>
<?php if ($img) { ?>
<script>
    window.setTimeout(function() {
        var elem = joms.jQuery('.joms-cropper img').css( 'visibility', '' );
        joms.util.crop.detach();
        joms.util.crop( elem );
    }, 100 );
</script>
<?php } ?>
