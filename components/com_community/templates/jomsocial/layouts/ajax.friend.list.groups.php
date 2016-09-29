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

$i = 0;

foreach ($friends as $id) {
    $user = CFactory::getUser($id);
    $invited = in_array($user->id, $selected);

?><div class="joms-js--friend joms-js--friend-<?php echo $user->id; ?>">
    <div class="joms-stream__header" style="padding: 2px 0;">
        <div class="joms-avatar--comment <?php echo CUserHelper::onlineIndicator($user); ?>">
            <a href="#">
                <img alt="<?php echo $user->getDisplayName(); ?>" src="<?php echo $user->getThumbAvatar(); ?>" data-author="<?php echo $user->id; ?>">
            </a>
        </div>
        <div class="joms-stream__meta">
            <?php echo $user->getDisplayName(); ?>
            <?php if ($invited) { ?>
            <span class="joms-stream__time"><small><?php echo JText::_('COM_COMMUNITY_INVITE_INVITED'); ?></small></span>
            <?php } else { ?>
            <span class="joms-stream__time">
                <label>
                    <input type="checkbox" class="joms-checkbox" name="friends[]" value="<?php echo $user->id; ?>">
                    <?php echo JText::_('COM_COMMUNITY_INVITE_SELECTED'); ?>
                </label>
            </span>
            <?php } ?>
        </div>
    </div>
</div>
<?php } ?>
