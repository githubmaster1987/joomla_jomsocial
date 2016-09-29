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

<input type="hidden" name="url" value="<?php echo $url; ?>">
<input type="hidden" name="filetype" value="<?php echo $fileType; ?>">
<input type="hidden" name="maxfilesize" value="<?php echo $maxFileSize; ?>">

<div class="joms-popup__content">
    <div class="joms-js--upload-preview" style="min-height:150px">
        <div class="joms-js--upload-placeholder" style="text-align:center;padding-top:70px"><?php echo JText::sprintf('COM_COMMUNITY_MAX_FILE_SIZE_FOR_UPLOAD', $maxFileSize . 'MB'); ?></div>
    </div>
</div>

<div class="joms-popup__action">
    <button class="joms-button--neutral joms-js--btn-add" data-lang-more="<?php echo JText::_('COM_COMMUNITY_PHOTOS_ADD_MORE_FILES') ?>"><?php echo JText::_('COM_COMMUNITY_PHOTOS_MULTIUPLOAD_ADD_FILES'); ?></button>
    <button class="joms-button--primary joms-js--btn-upload" style="display:none"><?php echo JText::_("COM_COMMUNITY_PHOTOS_MULTIUPLOAD_START_UPLOAD"); ?></button>
    <button class="joms-button--primary joms-js--btn-done" style="display:none"><?php echo JText::_("COM_COMMUNITY_DONE_BUTTON"); ?></button>
</div>
