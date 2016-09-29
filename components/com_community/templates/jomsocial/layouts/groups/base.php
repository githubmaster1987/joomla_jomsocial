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

<script>

    function joms_change_filter( value, type ) {
        var url;

        // Category selector.
        if ( type === 'category' ) {
            if ( value ) {
                url = '<?php echo html_entity_decode(CRoute::_("index.php?option=com_community&view=groups&task=display&categoryid=__cat__")); ?>';
                url = url.replace( '__cat__', value );
            } else {
                url = '<?php echo html_entity_decode(CRoute::_("index.php?option=com_community&view=groups&task=display")); ?>';
            }

            window.location = url;
            return;
        }

        // Filter selector.
        // @todo
    }

</script>

<?php
    $task = JFactory::getApplication()->input->getCmd('task');

    if(!isset($title)) {
        if ($task == 'mygroups') {
            $title = JText::_('COM_COMMUNITY_GROUPS_MY_GROUPS');
        } else {
            $title = JText::_('COM_COMMUNITY_GROUPS');
        }
    }
 ?>

<div class="joms-page">
    <div class="joms-list__search">
        <div class="joms-list__search-title">
            <h3 class="joms-page__title"><?php echo $title; ?></h3>
        </div>

        <div class="joms-list__utilities">
            <?php
            if(CGroupsAccess::groupsSearchView()) {
                ?>
                <form method="GET" class="joms-inline--desktop"
                      action="<?php echo CRoute::_('index.php?option=com_community&view=groups&task=search'); ?>">
                <span>
                    <input type="text" class="joms-input--search" name="search"
                           placeholder="<?php echo JText::_('COM_COMMUNITY_SEARCH_GROUP_PLACEHOLDER'); ?>">
                </span>
                    <?php echo JHTML::_('form.token') ?>
                    <span>
                    <button class="joms-button--neutral"><?php echo JText::_('COM_COMMUNITY_SEARCH_GO'); ?></button>
                </span>
                    <input type="hidden" name="option" value="com_community"/>
                    <input type="hidden" name="view" value="groups"/>
                    <input type="hidden" name="task" value="search"/>
                    <input type="hidden" name="Itemid" value="<?php echo CRoute::getItemId(); ?>"/>
                </form>
            <?php
            }

            if ($my->authorise('community.create', 'groups')) { ?>
            <button onclick="window.location='<?php echo CRoute::_('index.php?option=com_community&view=groups&task=create'); ?>';" class="joms-button--add">
                <?php echo JText::_('COM_COMMUNITY_GROUPS_CREATE_GROUP'); ?>
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

    <?php if( isset($availableCategories) && $availableCategories ){ ?>
    <div class="joms-sortings">
        <?php echo $sortings; ?>
        <select class="joms-select" onchange="joms_change_filter(this.value, 'category');">
            <option value=""><?php echo JText::_('COM_COMMUNITY_ALL_CATEGORIES') ?></option>
            <?php foreach($availableCategories as $category){ ?>
            <option value="<?php echo $category->id ?>"<?php if ($categoryId == $category->id) echo ' selected'; ?>><?php echo JText::_( $this->escape(trim($category->name)) ); ?></option>
            <?php } ?>
        </select>
    </div>
    <div class="joms-gap"></div>

    <?php } else { ?>
        <?php echo $sortings; ?>
    <?php } ?>

    <?php echo $groupsHTML;?>
</div>
