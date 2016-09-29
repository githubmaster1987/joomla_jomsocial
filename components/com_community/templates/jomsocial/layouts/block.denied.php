<?php
/**
* @copyright (C) 2013 iJoomla, Inc. - All rights reserved.
* @license GNU General Public License, version 2 (http://www.gnu.org/licenses/gpl-2.0.html)
* @author iJoomla.com <webmaster@ijoomla.com>
* @url https://www.jomsocial.com/license-agreement
* The PHP code portions are distributed under the GPL license. If not otherwise stated, all images, manuals, cascading style sheets, and included JavaScript *are NOT GPL, and are released under the IJOOMLA Proprietary Use License v1.0
* More info at https://www.jomsocial.com/license-agreement
*/
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();
?>
<div class="joms-page" style="text-align: center;" >
    <h1><?php echo JText::_('COM_COMMUNITY_PROFILE_PAGE_NOT_AVAILABLE'); ?></h1>
    <p><?php echo JText::_('COM_COMMUNITY_PROFILE_PAGE_NOT_AVAILABLE_TIPS'); ?></p>
    <div class="joms-gap"></div>
    <svg viewBox="0 0 16 16" class="joms-icon" style="width:10%;height:10%;">
        <use xlink:href="<?php echo CRoute::getURI(); ?>#joms-icon-support"></use>
    </svg>
    <div class="joms-gap"></div>
    <div class="joms-gap"></div>
    <a href="<?php echo CRoute::_('index.php?option=com_community&view=frontpage') ?>" class="joms-button--primary"><?php echo JText::_('COM_COMMUNITY_GO_TO_FRONTPAGE'); ?></a>
    <a href="<?php echo CRoute::_('index.php?option=com_community&view=profile'); ?>" class="joms-button--neutral"><?php echo JText::_('COM_COMMUNITY_GO_TO_PROFILE'); ?></a>
    <div class="joms-gap"></div>
</div>