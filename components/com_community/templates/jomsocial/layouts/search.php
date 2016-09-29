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
<div class="joms-page <?php echo (isset($results) && $results) ? 'joms-page--search' : ''; ?>">
	<h3 class="joms-page__title"><?php echo JText::_('COM_COMMUNITY_SEARCH');?></h3>

	<?php echo $submenu;?>

	<form name="jsform-search" method="get" action="" class="js-form reset-gap">

		<div class="joms-form__group">
			<input type="text" id="q" class="joms-input" name="q" value="<?php echo $this->escape($query);?>" placeholder="<?php echo JText::_('COM_COMMUNITY_SEARCH_PEOPLE_PLACEHOLDER');?>" />
		</div>

		<div class="joms-form__group">
			<ul class="joms-list--inline">
				<li>
					<input type="submit" value="<?php echo JText::_('COM_COMMUNITY_SEARCH_BUTTON_TEMP');?>" class="joms-button--primary joms-button--small" name="Search" />
				</li>
				<li class="joms-checkbox">
					<input type="checkbox" name="avatar" id="avatar" value="1"<?php echo ($avatarOnly) ? ' checked="checked"' : '';?>>
					<span><?php echo JText::_('COM_COMMUNITY_EVENTS_AVATAR_ONLY');?></span>
				</li>
			</ul>
		</div>

		<input type="hidden" name="option" value="com_community" />
		<input type="hidden" name="view" value="search" />
		<input type="hidden" name="Itemid" value="<?php echo CRoute::_getDefaultItemid();?>">
	</form>
</div>
<div class="joms-page--search__results">
	<?php if ($results) {
	    echo $resultHTML;
	} else if (empty($results) && !empty($query)) { ?>
	    <div class="joms-gap"></div>
	    <div class="joms-alert--warning">
	    <?php echo JText::_('COM_COMMUNITY_NO_RESULT_FROM_SEARCH');?>
	    </div>
	<?php } ?>
</div>
