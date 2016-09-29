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
<div class="joms-page">
	<h3 class="joms-page__title"><?php echo JText::_('COM_COMMUNITY_DELETE_PROFILE_TITLE'); ?></h3>

	<p class="joms-alert--danger"><?php echo JText::_('COM_COMMUNITY_DELETE_PROFILE_DESCRIPTION'); ?></p>
	<p class="joms-text--small"><?php echo JText::_('COM_COMMUNITY_DELETE_WARNING'); ?></p>

	<form method="post" action="<?php echo CRoute::getURI();?>" name="deleteProfile">
        <input type="submit" class="joms-button joms-button--primary joms-button--small" value="<?php echo JText::_('COM_COMMUNITY_YES_DELETE_MY_PROFILE'); ?>" />
        <?php echo JHTML::_( 'form.token' ); ?>
	</form>
</div>
