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
<?php if( $announcements ) {?>
	<div class="joms-stream__container">
		<?php foreach($announcements as $announcement){ ?>

        <?php
            $user = CFactory::getUser($announcement['created_by']);
        ?>

		<div class="joms-stream">
			<div class="joms-stream__header">
				<div class="joms-avatar--stream <?php echo CUserHelper::onlineIndicator($user); ?>">
                    <a href="<?php echo CRoute::_('index.php?option=com_community&view=profile&userid='.$user) ?>">
					   <img src="<?php echo $announcement['user_avatar']; ?>" alt="<?php echo $announcement['user_name'];?>" data-author="<?php echo $user->id; ?>" />
                    </a>
				</div>
				<div class="joms-stream__meta">
					<span class="joms-stream__user">
						<a href="<?php echo $announcement['group_link']; ?>" ><?php echo $announcement['group_name']; ?></a>
					</span>
					<span class="joms-stream__time">
						<small>
							<?php echo $announcement['user_name'];?>
							<?php echo $announcement['created_interval']; ?>
						</small>
					</span>
				</div>
			</div>
			<div class="joms-stream__body">
				<div class="joms-media">
					<h4 class="joms-text--title">
						<a href="<?php echo $announcement['announcement_link'] ?>" ><?php echo $announcement['title']; ?></a>
					</h4>
					<?php echo $announcement['message']; ?>
				</div>
			</div>
		</div>
		<?php } ?>
	</div>
<?php } else {?>
	<div class="joms-alert--info"><?php echo JText::_('COM_COMMUNITY_GROUP_NO_UPDATE'); ?></div>
<?php } ?>
