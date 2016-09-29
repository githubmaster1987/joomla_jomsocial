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
    <div class="joms-tab__bar joms-js--tab-bar">
        <a href="javascript:" data-id="mostdownload" class="active" style="display:inline-block"><?php echo JText::_('COM_COMMUNITY_FILES_MOST_DOWNLOADED'); ?></a>
        <a href="javascript:" data-id="document" style="display:inline-block"><?php echo JText::_('COM_COMMUNITY_FILES_DOCUMENT'); ?></a>
        <a href="javascript:" data-id="archive" style="display:inline-block"><?php echo JText::_('COM_COMMUNITY_FILES_ARCHIVE'); ?></a>
        <a href="javascript:" data-id="images" style="display:inline-block"><?php echo JText::_('COM_COMMUNITY_FILES_IMAGES'); ?></a>
        <a href="javascript:" data-id="multimedia" style="display:inline-block"><?php echo JText::_('COM_COMMUNITY_FILES_AUDIO_VIDEO'); ?></a>
        <a href="javascript:" data-id="miscellaneous" style="display:inline-block"><?php echo JText::_('COM_COMMUNITY_FILES_OTHER'); ?></a>
    </div>
    <div class="joms-tab__content joms-js--tab joms-js--tab-mostdownload" style="height:250px;overflow:auto"></div>
    <div class="joms-tab__content joms-js--tab joms-js--tab-document joms-popup__hide" style="height:250px;overflow:auto"></div>
    <div class="joms-tab__content joms-js--tab joms-js--tab-archive joms-popup__hide" style="height:250px;overflow:auto"></div>
    <div class="joms-tab__content joms-js--tab joms-js--tab-images joms-popup__hide" style="height:250px;overflow:auto"></div>
    <div class="joms-tab__content joms-js--tab joms-js--tab-multimedia joms-popup__hide" style="height:250px;overflow:auto"></div>
    <div class="joms-tab__content joms-js--tab joms-js--tab-miscellaneous joms-popup__hide" style="height:250px;overflow:auto"></div>
</div>

<div class="joms-popup__action">
    <button class="joms-button--neutral joms-js--btn-loadmore" style="visibility:hidden"><?php echo JText::_('COM_COMMUNITY_FILES_LOAD_MORE'); ?></button>
</div>
