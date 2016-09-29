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
$noData = true;
?>

<div class="app-box-content">
    <?php
    foreach ($profile['fields'] as $groupName => $items) {
        // Gather display data for the group. If there is no data, we can
        // later completely hide the whole segment
        $hasData = false;
        ob_start();
        ?>

            <ul class="joms-list__row joms-push">

                <?php if ($groupName != 'ungrouped') { ?>
                    <li><h4 class="joms-text--title joms-text--bold"><?php echo JText::_($groupName); ?></h4></li>
                <?php } ?>

                <?php foreach ($items as $item) { ?>
                    <?php
                    if (CPrivacy::isAccessAllowed($my->id, $profile['id'], 'custom', $item['access'])) {
                        // There is some displayable data here
                        $hasData = $hasData || CProfileLibrary::getFieldData($item) != '';
                        ?>

                        <?php
                        $fieldData = CProfileLibrary::getFieldData($item);

                        // Escape unless it is URL type, since URL type is in HTML format
                        if ($item['type'] != 'url' && $item['type'] != 'email' && $item['type'] != 'list' && $item['type'] != 'checkbox' && $item['type'] != 'select') {
                            $fieldData = $this->escape($fieldData);
                        }

                        // If textarea, we need to support multiline entry
                        if ($item['type'] == 'textarea') {
                            $fieldData = nl2br($fieldData);
                        }

                        if (!empty($fieldData)) {

                            ?>
                            <li>
                                <h5 class="joms-text--light"><?php echo JText::_($item['name']); ?></h5>
                                <?php if (!empty($item['searchLink']) && is_array($item['searchLink'])) { ?>
                                    <span>
                                        <?php
                                        foreach ($item['searchLink'] as $linkKey => $linkValue) {
                                            $item['value'] = $linkKey;
                                            if ($item['type'] == 'checkbox') {
                                                echo '<a href="' . $linkValue . '">' . JText::_($item['value']) . '</a><br />';
                                            } else {
                                                echo '<a href="' . $linkValue . '">' . $fieldData . '</a><br />';
                                            }
                                        }
                                        ?>
                                    </span>
                                <?php } else { ?>
                                    <span>
                                        <?php echo (!empty($item['searchLink'])) ? '<a href="' . $item['searchLink'] . '"> ' . $fieldData . ' </a>' : $fieldData; ?>
                                    </span>
                                <?php } ?>
                            </li>
                            <?php
                        }
                    }
                    ?>
                <?php } ?>
            </ul>

        <?php
        $html = ob_get_contents();
        ob_end_clean();

        // We would only display the profile data in the group if there is actually some
        // data to be displayed
        if ($hasData) {
            echo $html;
            $noData = false;
        }
    }

    if ($noData) {
        if ($isMine) {
            ?>
            <div class="joms-list__row">
                <?php echo JText::_('COM_COMMUNITY_PROFILES_SHARE_ABOUT_YOURSELF'); ?>
            </div>
            <?php
        } else {
            ?>
            <div class="joms-list__row">
                <?php echo JText::_('COM_COMMUNITY_PROFILES_NO_INFORMATION_SHARE'); ?>
            </div>
            <?php
        }
    }
    ?>
</div>
<div class="joms-module__footer">
    <?php if ($isMine && !$hideButton){ ?>
        <a class="joms-button--primary joms-button--full" href="<?php echo CRoute::_('index.php?option=com_community&view=profile&task=edit'); ?>" title="<?php echo JText::_('COM_COMMUNITY_PROFILE_EDIT'); ?>"><?php echo JText::_('COM_COMMUNITY_PROFILE_EDIT'); ?></a>
    <?php }elseif(COwnerHelper::isCommunityAdmin()){
        //admin can actually edit others' profile
        ?>
        <a class="joms-button--primary joms-button--full" href="<?php echo CRoute::_('index.php?option=com_community&view=profile&task=edit&userid='.$profile['id']); ?>" title="<?php echo JText::_('COM_COMMUNITY_PROFILE_EDIT'); ?>"><?php echo JText::_('COM_COMMUNITY_PROFILE_EDIT'); ?></a>
    <?php } ?>
</div>
