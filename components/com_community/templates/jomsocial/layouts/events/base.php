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

<?php if(isset($groupMiniHeader) && $groupMiniHeader){ ?>
<div class="joms-body">
    <?php echo $groupMiniHeader; ?>
</div>
<?php } ?>

<div class="joms-page">
    <div class="joms-list__search">
        <div class="joms-list__search-title">
            <h3 class="joms-page__title"><?php echo $pageTitle; ?></h3>
        </div>

        <div class="joms-list__utilities">
            <?php
            if(isset($canSearch) && $canSearch) {
                ?>
                <form method="GET" class="joms-inline--desktop"
                      action="<?php echo CRoute::_('index.php?option=com_community&view=events&task=search'); ?>">
                <span>
                    <input type="text" class="joms-input--search" name="search" placeholder="<?php

                    //echo (isset($isGroup) && $isGroup) ?  JText::_('COM_COMMUNITY_SEARCH_GROUP_EVENT_PLACEHOLDER') : JText::_('COM_COMMUNITY_SEARCH_EVENT_PLACEHOLDER');
                    echo JText::_('COM_COMMUNITY_SEARCH_EVENT_PLACEHOLDER');
                    ?>">
                </span>
                    <?php echo JHTML::_('form.token') ?>
                    <span>
                    <button class="joms-button--neutral"><?php echo JText::_('COM_COMMUNITY_SEARCH_GO'); ?></button>
                </span>
                    <input type="hidden" name="option" value="com_community"/>
                    <input type="hidden" name="view" value="events"/>
                    <input type="hidden" name="task" value="search"/>
                    <input type="hidden" name="Itemid" value="<?php echo CRoute::getItemId(); ?>"/>
                    <input type="hidden" name="posted" value="1"/>
                </form>
            <?php

            }

            if($canCreate) { ?>
            <button onclick="window.location='<?php echo $createLink; ?>';" class="joms-button--add">
                <?php echo (isset($isGroup) && $isGroup) ? JText::_('COM_COMMUNITY_CREATE_GROUP_EVENT') : JText::_('COM_COMMUNITY_CREATE_EVENT'); ?>
                <svg class="joms-icon" viewBox="0 0 16 16">
                    <use xlink:href="<?php echo CRoute::getURI(); ?>#joms-icon-plus"></use>
                </svg>
            </button>
            <?php } ?>
        </div>
    </div>

    <?php if($submenu){ ?>
        <?php echo $submenu;?>
        <div class="joms-gap"></div>
    <?php } ?>

    <?php echo $eventsHTML;?>
</div>
