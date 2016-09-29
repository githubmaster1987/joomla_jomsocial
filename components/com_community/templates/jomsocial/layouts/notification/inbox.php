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

<?php if ( empty($rows) ) { ?>
    <li>
        <div class="cEmpty cAlert" style="margin:0;"><?php echo JText::_('COM_COMMUNITY_NOTIFICATIONS_NO_MESSAGE'); ?></div>
    </li>
<?php } else { ?>

    <?php foreach ($rows as $row) { ?>
    <li>
        <div class="joms-popover__avatar">
            <div class="joms-avatar">
                <img src="<?php echo $row->user->getThumbAvatar(); ?>" alt="<?php echo $row->user->getDisplayName(); ?>" >
            </div>
        </div>
        <div class="joms-popover__content">
            <h5><a href="<?php echo CUrlHelper::userLink($row->user->id); ?>"><?php echo $row->user->getDisplayName(); ?></a></h5>
            <a href="<?php echo $row->link; ?>"><?php echo $row->subject; ?></a>
            <small><?php echo $row->created; ?></small>
        </div>
    </li>
    <?php } ?>

<?php } ?>

<a class="joms-button--neutral joms-button--full" href="<?php echo CRoute::_('index.php?option=com_community&view=inbox'); ?>">
    <?php echo JText::_('COM_COMMUNITY_NOTIFICATIONS_SHOW_ALL_MSG'); ?>
</a>
