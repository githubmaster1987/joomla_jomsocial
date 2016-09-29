<?php
/**
 * @copyright (C) 2014 iJoomla, Inc. - All rights reserved.
 * @license GNU General Public License, version 2 (http://www.gnu.org/licenses/gpl-2.0.html)
 * @author iJoomla.com <webmaster@ijoomla.com>
 * @url https://www.jomsocial.com/license-agreement
 * The PHP code portions are distributed under the GPL license. If not otherwise stated, all images, manuals, cascading style sheets, and included JavaScript *are NOT GPL, and are released under the IJOOMLA Proprietary Use License v1.0
 * More info at https://www.jomsocial.com/license-agreement
 */
defined('_JEXEC') or die();

$permissionIcons = array(
    PRIVACY_PUBLIC  => 'joms-icon-earth',
    PRIVACY_MEMBERS => 'joms-icon-users',
    PRIVACY_FRIENDS => 'joms-icon-user',
    PRIVACY_PRIVATE => 'joms-icon-lock'
);

$permissionIcon = $permissionIcons[ isset($permissionIcons[$permission]) ? $permission : PRIVACY_PUBLIC ];

?>

<div class="joms-stream__header">
    <div class="joms-avatar--stream <?php echo CUserHelper::onlineIndicator($creator); ?>">
        <a href="<?php echo CRoute::_('index.php?option=com_community&view=profile&userid=' . $creator->id); ?>">
            <img src="<?php echo $creator->getThumbAvatar(); ?>" alt="<?php echo $creator->getDisplayName(); ?>" data-author="<?php echo $creator->id; ?>">
        </a>
    </div>
    <div class="joms-stream__meta">
        <a href="<?php echo CRoute::_('index.php?option=com_community&view=profile&userid=' . $creator->id); ?>"
            class="joms-stream__user"><?php echo $creator->getDisplayName(); ?></a>
        <span class="joms-stream__time">
            <small><?php echo $created; ?></small>
            <svg class="joms-icon" viewBox="0 0 16 16">
                <use xlink:href="<?php echo CRoute::getURI(); ?>#<?php echo $permissionIcon; ?>"></use>
            </svg>
        </span>
    </div>
</div>
<div class="joms-stream__body">
    <div class="joms-js--description"></div>
    <div class="joms-js--tag-info" style="margin-top:10px;"></div>
</div>
