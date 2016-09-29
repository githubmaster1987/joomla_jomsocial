<?php
/**
* @copyright (C) 2013 iJoomla, Inc. - All rights reserved.
* @license GNU General Public License, version 2 (http://www.gnu.org/licenses/gpl-2.0.html)
* @author iJoomla.com <webmaster@ijoomla.com>
* @url https://www.jomsocial.com/license-agreement
* The PHP code portions are distributed under the GPL license. If not otherwise stated, all images, manuals, cascading style sheets, and included JavaScript *are NOT GPL, and are released under the IJOOMLA Proprietary Use License v1.0
* More info at https://www.jomsocial.com/license-agreement
*/
defined('_JEXEC') or die('Restricted access');
?>
<?php
	if($groups)
	{
?>
<div class="joms-module__wrapper">
	<div class="joms-tab__bar">
		<a href="#joms-group--pending" class="active"><?php echo JText::_('COM_COMMUNITY_GROUPS_PENDING_INVITATIONS');?></a>
	</div>

	<div id="#joms-group--pending" class="joms-tab__content">
		<ul class="joms-list--photos">
		<?php
			for( $i = 0; $i < count( $groups ); $i++ )
			{
				$group	=&  $groups[$i];
		?>
		<li class="joms-list__item">
			<div class="joms-badge">
				<a href="<?php echo CRoute::_( 'index.php?option=com_community&view=groups&task=viewgroup&groupid=' . $group->id ); ?>">
					<img src="<?php echo $group->getThumbAvatar(); ?>" alt="<?php echo $group->name; ?>" />
				</a>
				<div class="joms-badge__item">
					<?php echo JText::sprintf((CStringHelper::isPlural($group->membercount)) ? 'COM_COMMUNITY_GROUPS_MEMBER_COUNT_NUMBER':'COM_COMMUNITY_GROUPS_MEMBER_COUNT_NUMBER', $group->membercount);?>
				</div>
			</div>
		</li>
		<?php
			}
		?>
		</ul>
		<div class="joms-module__footer">
			<a class="joms-button--link" href="<?php echo CRoute::_('index.php?option=com_community&view=groups'); ?>"><?php echo JText::_('COM_COMMUNITY_GROUPS_VIEW_ALL');?></a>
		</div>
	</div>
</div>
<?php
	}
?>
