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

<table class="table table-condensed">
    <thead>
        <tr>
            <th><?php echo JText::_('COM_COMMUNITY_TROUBLESHOOTS_ID'); ?></th>
            <th><?php echo JText::_('COM_COMMUNITY_TROUBLESHOOTS_ORDERING'); ?></th>
            <th><?php echo JText::_('COM_COMMUNITY_TROUBLESHOOTS_FOLDER'); ?></th>
            <th><?php echo JText::_('COM_COMMUNITY_TROUBLESHOOTS_NAME'); ?></th>
            <th><?php echo JText::_('COM_COMMUNITY_TROUBLESHOOTS_ELEMENT'); ?></th>
            <th><?php echo JText::_('COM_COMMUNITY_TROUBLESHOOTS_VERSION'); ?></th>
            <th><?php echo JText::_('COM_COMMUNITY_TROUBLESHOOTS_DEVELOPER'); ?></th>
            <th><?php echo JText::_('COM_COMMUNITY_TROUBLESHOOTS_ENABLED'); ?></th>
        </tr>
    </thead>
    <tbody>
        <?php $communityPlugins = $this->troubleshoots->extensions['plugins']; ?>
        <?php foreach ($this->plugins as $plugin) { ?>
            <?php
            $plugin->manifest_cache = new JRegistry($plugin->manifest_cache);
            $language = JFactory::getLanguage();
            $language->load('plg_' . $plugin->folder . '_' . $plugin->element);
            ?>
            <tr class="<?php echo (in_array($plugin->element, $communityPlugins[$plugin->folder])) ? '' : 'error'; ?>">
                <td>
                    <?php echo $plugin->extension_id; ?>
                </td>
                <td>
                    <?php echo $plugin->ordering; ?>
                </td>
                <td>
                    <?php echo $plugin->folder; ?>
                </td>
                <td>
                    <a href="<?php echo JRoute::_('index.php?option=com_plugins&task=plugin.edit&extension_id=' . $plugin->extension_id); ?>" target="_blank"><?php echo JText::_($plugin->name); ?></a>
                </td>
                <td>
                    <?php echo $plugin->element; ?>
                </td>
                <td>
                    <?php echo $plugin->manifest_cache->get('version'); ?>
                </td>
                <td>
                    <?php echo (in_array($plugin->element, $communityPlugins[$plugin->folder])) ? 'JomSocial' : $plugin->manifest_cache->get('author'); ?>
                </td>
                <td>
                    <?php if ($plugin->enabled) : ?>
                        <i class="icon-publish"></i>
                    <?php else : ?>
                        <i class="icon-unpublish"></i>
                    <?php endif; ?>
                </td>
            </tr>
        <?php } ?>
    </tbody>
</table>