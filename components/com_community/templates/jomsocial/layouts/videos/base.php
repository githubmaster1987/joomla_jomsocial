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

    $isGroup = isset($groupId) && $groupId;
    $isEvent = isset($eventId) && $eventId;
    $currentTask = $mainframe = JFactory::getApplication()->input->get('task');

    if(!isset($title)){
        if (isset($isMyVideo) && $isMyVideo) {
            $title = JText::_('COM_COMMUNITY_VIDEOS_MY');
        } elseif ($isGroup) {
            $title = JText::_('COM_COMMUNITY_GROUP_VIDEOS');
        } elseif ($currentTask == 'search') {
            $title = JText::_('COM_COMMUNITY_VIDEOS_SEARCH_VIDEOS');
        } elseif($isEvent) {
            $title = JText::_('COM_COMMUNITY_EVENT_VIDEOS');
        } else {
            $title = JText::_('COM_COMMUNITY_VIDEOS');
        }
    }


    $context = '';
    $contextId = '';
    if ($isGroup) {
        $context = VIDEO_GROUP_TYPE;
        $contextId = $groupId;
    } else if ($isEvent) {
        $context = VIDEO_EVENT_TYPE;
        $contextId = $eventId;
    }

?>

<script>

    function joms_change_filter(value) {
        window.location = value;
    }

</script>

<div class="joms-page joms-page--mobile">
    <div class="joms-list__search">
        <div class="joms-list__search-title">
            <h3 class="joms-page__title"><?php echo $title; ?></h3>
        </div>

        <div class="joms-list__utilities">
            <?php
                if (isset($canSearch) && $canSearch) {
                    ?>
                    <form method="GET" class="joms-inline--desktop"
                          action="<?php echo CRoute::_('index.php?option=com_community&view=videos&task=search'); ?>">
                <span>
                    <input type="text" class="joms-input--search" name="search-text"
                           placeholder="<?php echo JText::_('COM_COMMUNITY_SEARCH_VIDEO_PLACEHOLDER'); ?>"
                           value="<?php echo isset($search) ? $search : ''; ?>">
                </span>
                        <?php echo JHTML::_('form.token') ?>
                        <span>
                    <button class="joms-button--neutral"><?php echo JText::_('COM_COMMUNITY_SEARCH_GO'); ?></button>
                </span>
                        <input type="hidden" name="option" value="com_community"/>
                        <input type="hidden" name="view" value="videos"/>
                        <input type="hidden" name="task" value="search"/>
                        <input type="hidden" name="Itemid" value="<?php echo CRoute::getItemId(); ?>"/>
                    </form>
                <?php
                }

                if ($canCreate) {

                    ?>
                    <button onclick="joms.api.videoAdd('<?php echo $contextId; ?>', '<?php echo $context; ?>');"
                            class="joms-button--add">
                        <?php echo JText::_( $isGroup ? 'COM_COMMUNITY_GROUP_VIDEOS_ADD' : ( $isEvent ? 'COM_COMMUNITY_EVENT_VIDEOS_ADD' : 'COM_COMMUNITY_VIDEOS_ADD' ) ); ?>
                        <svg class="joms-icon" viewBox="0 0 16 16">
                            <use xlink:href="<?php echo CRoute::getURI(); ?>#joms-icon-plus"></use>
                        </svg>
                    </button>
                <?php } ?>
        </div>
    </div>

    <?php if ($submenu) { ?>
        <?php echo $submenu; ?>
    <?php } ?>

    <!-- sorting -->
    <?php if ($currentTask == 'display' || $currentTask == 'myvideos' || $currentTask == '') { ?>
        <div class="joms-sortings">
            <?php if ($currentTask != 'mypendingvideos' && $currentTask != 'search') {
                echo $sortings;
            } ?>

            <?php if (($currentTask == 'display' || $currentTask == '' || $currentTask == 'myvideos') && !$isGroup && !$isEvent) { ?>
                <select class="joms-select" onchange="joms_change_filter(this.value);">
                    <option
                        value="<?php echo CRoute::_($allVideosUrl); ?>"><?php echo JText::_('COM_COMMUNITY_ALL_CATEGORIES'); ?></option>
                    <?php if ($category->parent == COMMUNITY_NO_PARENT && $category->id == COMMUNITY_NO_PARENT) { ?>

                    <?php
                    } else {
                        $catid = '';
                        if ($category->parent != 0) {
                            $catid = '&catid=' . $category->parent;
                        }
                        ?>
                        <!--                    <option value="--><?php //echo CRoute::_($parentUrl . $catid ); ?><!--">--><?php //echo JText::_( 'COM_COMMUNITY_BACK_TO_PARENT' ); ?><!--</option>-->
                    <?php } ?>

                    <?php if ($categories) { ?>
                        <?php foreach ($categories as $row): ?>
                            <option <?php if ($categoryId == $row->id){ ?>selected="selected" <?php } ?>
                                    value="<?php echo CRoute::_($catVideoUrl . $row->id); ?>"><?php echo JText::_($this->escape(trim($row->name))); ?></option>
                        <?php endforeach; ?>
                    <?php } ?>
                </select>
            <?php } ?>
        </div>

        <div class="joms-gap"></div>

    <?php } else { ?>
        <?php if ($currentTask != 'mypendingvideos' && $currentTask != 'search') {
            echo $sortings;
        } ?>
    <?php } ?>

    <?php echo $videosHTML; ?>
</div>
