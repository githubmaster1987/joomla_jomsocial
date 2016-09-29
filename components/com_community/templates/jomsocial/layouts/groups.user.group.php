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
<?php if($usergroups) { ?>
<div class="joms-module__wrapper">
	<div class="joms-tab__bar">
		<a href="#joms-group--groups" class="active"><?php echo JText::_('COM_COMMUNITY_GROUPS_MY_GROUPS'); ?></a>
	</div>

	<div id="#joms-group--groups" class="joms-tab__content">
		<ul class="joms-list--photos">
		<?php foreach($usergroups as $grp): ?>
			<li class="joms-list__item">
				<a href="<?php echo $grp['group_url'] ?>"><img src="<?php echo $grp['avatar']; ?>" title="<?php echo $grp['group_name']; ?>" /></a>
			</li>
		<?php endforeach;?>
		</ul>

		<div class="joms-module__footer">
			<a class="joms-button--link" href="<?php echo CRoute::_('index.php?option=com_community&view=groups&task=mygroups'); ?>">
				<?php echo JText::_('COM_COMMUNITY_GROUPS_MY_GROUPS'); ?>
			</a>
		</div>
	</div>
<?php
	} 
?>
</div>