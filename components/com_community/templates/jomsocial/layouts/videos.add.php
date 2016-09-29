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
    <?php if ( $enableVideoUpload ) { ?>
    <div class="joms-tab__bar">
        <a href="#joms-js__link-video" class="active"><?php echo JText::_('COM_COMMUNITY_VIDEOS_LINK'); ?></a>
        <a href="#joms-js__upload-video"><?php echo JText::_('COM_COMMUNITY_VIDEOS_UPLOAD'); ?></a>
    </div>
    <div class="joms-tab__content" id="joms-js__link-video">
    <?php } ?>

    <?php echo $linkUploadHtml; ?>

    <?php if ( $enableVideoUpload ) { ?>
    </div>
    <div class="joms-tab__content joms-popup__hide" id="joms-js__upload-video">
        <?php echo $videoUploadHtml; ?>
    </div>
    <?php } ?>
</div>
