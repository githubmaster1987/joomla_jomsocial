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

<form method="POST" action="<?php echo CRoute::_('index.php?option=com_community&view=profile&task=updateAlias'); ?>" style="margin:0">
    <div><?php echo JText::sprintf('COM_COMMUNITY_YOUR_CURRENT_PROFILE_URL', $prefixURL); ?></div>
    <input type="hidden" name="userid" value="<?php echo $user->id; ?>" />
</form>
