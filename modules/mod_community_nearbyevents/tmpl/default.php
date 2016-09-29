<?php
/**
* @copyright (C) 2015 iJoomla, Inc. - All rights reserved.
* @license GNU General Public License, version 2 (http://www.gnu.org/licenses/gpl-2.0.html)
* @author iJoomla.com <webmaster@ijoomla.com>
* @url https://www.jomsocial.com/license-agreement
* The PHP code portions are distributed under the GPL license. If not otherwise stated, all images, manuals, cascading style sheets, and included JavaScript *are NOT GPL, and are released under the IJOOMLA Proprietary Use License v1.0
* More info at https://www.jomsocial.com/license-agreement
*/
defined('_JEXEC') or die('Restricted access');

$doc = JFactory::getDocument();
$app = JFactory::getApplication();
$menu = $app->getMenu()->getActive()->id;

$doc->addScript(JURI::root(true) . '/modules/mod_community_nearbyevents/assets/script.js');
$doc->addScriptDeclaration('joms_mod_community_nearbyevents_url = "' . JURI::root(true) . '/index.php?option=com_ajax&module=community_nearbyevents&format=json&method=searchEvents&location=___location___&Itemid=' . $menu . '"');

$autodetect = $params->get('auto_detect_location') > 0;

?>

<div class="joms-js--mod-search-nearbyevents">
    <form onsubmit="return false">
        <input type="text" class="joms-input joms-js--location" placeholder="<?php echo JText::_('COM_COMMUNITY_EVENTS_LOCATION_DESCRIPTION'); ?>">
        <button class="joms-button joms-button--neutral joms-button--small joms-js--btn-search"><?php echo JText::_('COM_COMMUNITY_SEARCH'); ?></button>
        <?php if ( $autodetect ) { ?>
        <a href="javascript:" class="joms-button joms-button--primary joms-button--small joms-js--btn-autodetect"><?php echo JText::_('COM_COMMUNITY_EVENTS_AUTODETECT'); ?></a>
        <?php } ?>
    </form>
    <div class="joms-js--loading" style="text-align:center; display:none;">
        <img src="<?php echo JURI::root(true); ?>/components/com_community/assets/ajax-loader.gif" alt="loader">
    </div>
    <div class="joms-js--result" style="display:none"></div>
</div>
