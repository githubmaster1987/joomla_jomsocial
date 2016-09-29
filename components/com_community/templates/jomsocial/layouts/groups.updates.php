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

if($userid!=0)
{
?>
<div class="joms-page">
    <h3 class="joms-page__title"><?php echo JText::_('COM_COMMUNITY_GROUPS_MY_GROUPS_UPDATE'); ?></h3>
    <?php echo $submenu;?>
</div>
<div class="joms-gap"></div>
<div class="joms-body">
	<?php if(sizeof($this->view('groups')->modGetUserGroups($userid))>0) { ?>

	<div class="joms-sidebar">
		<?php echo $this->view('groups')->modUserGroups($userid); ?>
		<?php echo $this->view('groups')->modUserGroupPending($userid); ?>
		<?php echo $this->view('groups')->modUserGroupUpcomingEvents($userid); ?>
		<?php echo $this->view('groups')->modUserGroupVideosUpdate($userid); ?>
		<?php echo $this->view('groups')->modUserAlbumsUpdate($userid); ?>
	</div>

	<div class="joms-main joms-middlezone">
		<div class="joms-stream__wrapper">
			<div class="joms-stream__container">
				<div class="joms-tab__bar">
					<a href="#joms-group--discussion" class="active"><?php echo JText::_('COM_COMMUNITY_GROUPS_PARTICIPATED_DISCUSSION_UPDATE'); ?></a>
					<a href="#joms-group--announcement"><?php echo JText::_('COM_COMMUNITY_GROUPS_ANNOUNCEMENT_UPDATE_TITLE'); ?></a>
				</div>
				<div class="joms-gap"></div>
				<?php
				$groupupdate = array_merge($this->view('groups')->modGetUserAnnouncement($userid),$this->view('groups')->modGetUserParticipatedDiscussion($userid));

				if(sizeof($groupupdate)>0) {
				?>
				<div id="joms-group--discussion" class="joms-tab__content">
					<?php echo $this->view('groups')->modUserParticipatedDiscussion($userid); ?>
				</div>
				<div id="joms-group--announcement" class="joms-tab__content" style="display:none;">
					<?php echo $this->view('groups')->modUserAnnouncement($userid); ?>
				</div>
				<?php } elseif(!empty($my->_groups)) { ?>
				<div class="joms-alert--info"><?php echo JText::_('COM_COMMUNITY_GROUP_NO_UPDATE'); ?></div>
				<?php }else {?>
				<div class="joms-alert--success"><?php echo JText::sprintf( 'COM_COMMUNITY_GROUPS_UPDATE_DEFAULT' , CRoute::_('index.php?option=com_community&view=groups') ); ?></div>
				<?php } ?>
			</div>
		</div>
	</div>

	<?php } elseif(!empty($my->_groups)) { ?>
	<div class="cEmpty cAlert"><?php echo JText::_('COM_COMMUNITY_GROUP_NO_UPDATE'); ?></div>
	<?php }else {?>
	<div class="cEmpty cAlert"><?php echo JText::sprintf( 'COM_COMMUNITY_GROUPS_UPDATE_DEFAULT' , CRoute::_('index.php?option=com_community&view=groups') ); ?></div>

    <?php } ?>

	<?php
	}
	else
	{
	?>
	<div class="cEmpty cAlert"><?php echo JText::sprintf( 'COM_COMMUNITY_GROUPS_UPDATE_NOT_LOGGED_IN' , CRoute::_( 'index.php?option=com_community&view=frontpage' ), CRoute::_( 'index.php?option=com_community&view=register' )) ?></div>
	<?php
	}
	?>

</div>
