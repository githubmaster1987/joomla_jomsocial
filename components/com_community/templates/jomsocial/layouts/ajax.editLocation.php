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

<div>
	<div class="joms-js--location-map" style="height:110px"></div>
	<div style="padding-top:10px">
        <input type="text" class="joms-input joms-js--location-label" readonly="readonly">
		<div class="joms-map--location-selector joms-js--location-selector">
			<span class="joms-map--location-item--notice"><?php echo JText::_('COM_COMMUNITY_LOCATING_PLEASE_WAIT'); ?></span>
		</div>
	</div>
</div>
