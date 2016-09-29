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

$defaultFilter = $my->_cparams->get('frontpageactivitydefault');

?>

<script type="text/javascript">joms.filters && joms.filters.bind();</script>

<?php
/**
 * if user logged in
 * 		load frontpage.members.php
 * else
 * 		load frontpage.guest.php
 */
echo $header;
?>

<div class="joms-body">

    <?php if ($moduleCount > 0) { ?>
    <div class="joms-sidebar">

        <div class="joms-module__wrapper"><?php $this->renderModules('js_side_top'); ?></div>
        <div class="joms-module__wrapper--stacked"><?php $this->renderModules('js_side_top_stacked'); ?></div>
        <div class="joms-module__wrapper"><?php $this->renderModules('js_side_bottom'); ?></div>
        <div class="joms-module__wrapper--stacked"><?php $this->renderModules('js_side_bottom_stacked'); ?></div>
        <div class="joms-module__wrapper"><?php $this->renderModules('js_side_frontpage_top'); ?></div>
        <div class="joms-module__wrapper--stacked"><?php $this->renderModules('js_side_frontpage_top_stacked'); ?></div>
        <div class="joms-module__wrapper"><?php $this->renderModules('js_side_frontpage'); ?></div>
        <div class="joms-module__wrapper--stacked"><?php $this->renderModules('js_side_frontpage_stacked'); ?></div>
        <div class="joms-module__wrapper"><?php $this->renderModules('js_side_frontpage_bottom'); ?></div>
        <div class="joms-module__wrapper--stacked"><?php $this->renderModules('js_side_frontpage_bottom_stacked'); ?></div>

    </div>
    <?php } ?>

    <div class="joms-main <?php echo $is_full = ($moduleCount == 0 ? 'joms-main--full' : ''); ?>">
        <?php if ($config->get('showactivitystream') == '1' || ($config->get('showactivitystream') == '2' && $my->id != 0 )) { ?>
            <?php ($my->id) ? $userstatus->render() : ''; ?>
            <!-- User logged than display filterbar -->
            <?php if ($alreadyLogin == 1) : ?>
                <div class="joms-activity-filter clearfix">
                    <div class="joms-activity-filter-action">

                        <a><svg viewBox="0 0 16 16" class="joms-icon">
                            <use xlink:href="<?php echo CRoute::getURI(); ?>#joms-icon-arrow-down"></use>
                        </svg>
                        <span class="joms-gap--inline-small"></span>
                        <?php echo JText::_("COM_COMMUNITY_FILTERBAR_FILTERBY"); ?></a>
                        <span class="joms-activity-filter-status" data-default="<?php echo JText::_("COM_COMMUNITY_FILTERBAR_ALL"); ?>"><?php echo $filterText; ?></span>
                    </div>
                    <form class="reset-gap">
                        <ul class="unstyled joms-activity-filter-dropdown joms-postbox-dropdown" style="display: none">
                            <li <?php echo ($filterKey == 'all') ? 'class="active"' : ''; ?>
                                data-filter="all"
                                data-url="<?php echo CRoute::_('index.php?option=com_community&view=frontpage&filter=all'); ?>">
                                <svg viewBox="0 0 16 18" class="joms-icon">
                                    <use xlink:href="<?php echo CRoute::getURI(); ?>#joms-icon-star3"></use>
                                </svg>
                                <?php echo JText::_("COM_COMMUNITY_FILTERBAR_ALL"); ?>
                            </li>
                            <li <?php echo ($filterKey == 'apps' && $filterValue == "profile") ? 'class="active"' : ''; ?>
                                data-filter="apps" data-value="profile"
                                data-url="<?php echo CRoute::_('index.php?option=com_community&view=frontpage&filter=apps&value=profile'); ?>">
                                <svg viewBox="0 0 16 18" class="joms-icon">
                                    <use xlink:href="<?php echo CRoute::getURI(); ?>#joms-icon-pencil"></use>
                                </svg>
                                <?php echo JText::_("COM_COMMUNITY_FILTERBAR_STATUS"); ?>
                            </li>
                            <?php if ($config->get('enablephotos')) { ?>
                                <li <?php echo ($filterKey == 'apps' && $filterValue == "photo") ? 'class="active"' : ''; ?>
                                    data-filter="apps" data-value="photo"
                                    data-url="<?php echo CRoute::_('index.php?option=com_community&view=frontpage&filter=apps&value=photo'); ?>">
                                    <svg viewBox="0 0 16 18" class="joms-icon">
                                        <use xlink:href="<?php echo CRoute::getURI(); ?>#joms-icon-images"></use>
                                    </svg>
                                    <?php echo JText::_("COM_COMMUNITY_FILTERBAR_PHOTO"); ?>
                                </li>
                            <?php } ?>
                            <?php if ($config->get('enablevideos') == 1) { ?>
                                <li <?php echo ($filterKey == 'apps' && $filterValue == "video") ? 'class="active"' : ''; ?>
                                    data-filter="apps" data-value="video"
                                    data-url="<?php echo CRoute::_('index.php?option=com_community&view=frontpage&filter=apps&value=video'); ?>">
                                    <svg viewBox="0 0 16 18" class="joms-icon">
                                        <use xlink:href="<?php echo CRoute::getURI(); ?>#joms-icon-camera2"></use>
                                    </svg>
                                    <?php echo JText::_("COM_COMMUNITY_FILTERBAR_VIDEO"); ?>
                                </li>
                            <?php } ?>
                            <?php if ($config->get('enablegroups') == 1) { ?>
                                <li <?php echo ($filterKey == 'apps' && $filterValue == "group") ? 'class="active"' : ''; ?>
                                    data-filter="apps" data-value="group"
                                    data-url="<?php echo CRoute::_('index.php?option=com_community&view=frontpage&filter=apps&value=group'); ?>">
                                    <svg viewBox="0 0 16 18" class="joms-icon">
                                        <use xlink:href="<?php echo CRoute::getURI(); ?>#joms-icon-users"></use>
                                    </svg>
                                    <?php echo JText::_("COM_COMMUNITY_FILTERBAR_GROUP"); ?>
                                </li>
                            <?php } ?>
                            <?php if ($config->get('enableevents') == 1) { ?>
                                <li <?php echo ($filterKey == 'apps' && $filterValue == "event") ? 'class="active"' : ''; ?>
                                    data-filter="apps" data-value="event"
                                    data-url="<?php echo CRoute::_('index.php?option=com_community&view=frontpage&filter=apps&value=event'); ?>">
                                    <svg viewBox="0 0 16 18" class="joms-icon">
                                        <use xlink:href="<?php echo CRoute::getURI(); ?>#joms-icon-calendar"></use>
                                    </svg>
                                    <?php echo JText::_("COM_COMMUNITY_FILTERBAR_EVENT"); ?>
                                </li>
                            <?php } ?>
                            <li <?php echo ($filterKey == 'privacy' && $filterValue == "me-and-friends") ? 'class="active"' : ''; ?>
                                data-filter="privacy" data-value="me-and-friends"
                                data-url="<?php echo CRoute::_('index.php?option=com_community&view=frontpage&filter=privacy&value=me-and-friends'); ?>">
                                <svg viewBox="0 0 16 18" class="joms-icon">
                                    <use xlink:href="<?php echo CRoute::getURI(); ?>#joms-icon-user"></use>
                                </svg>
                                <?php echo JText::_("COM_COMMUNITY_FILTERBAR_MEANDFRIENDS"); ?>
                            </li>
                            <li <?php echo ($filterKey == 'keyword' || $filterKey == 'hashtag') ? 'class="active"' : '' ?>
                                data-filter="__filter__" data-value="__value__"
                                data-url="<?php echo CRoute::_('index.php?option=com_community&view=frontpage&filter=__filter__&value=__value__'); ?>"
                                style="margin-top:20px">
                                <div class="joms-select--wrapper" style="margin-bottom:4px">
                                    <select class="joms-select">
                                        <option value="keyword" <?php echo ( $filterKey == 'keyword' ) ? 'selected="selected"' : '' ?>><?php echo JText::_("COM_COMMUNITY_FILTERBY_KEYWORD"); ?></option>
                                        <option value="hashtag" <?php echo ( $filterKey == 'hashtag' ) ? 'selected="selected"' : '' ?>><?php echo JText::_("COM_COMMUNITY_FILTERBY_HASHTAG"); ?></option>
                                    </select>
                                </div>
                                <input type="text" class="joms-input" value="<?php echo ($filterKey == 'keyword' || $filterKey == 'hashtag') ? $filterValue : '' ?>"
                                    placeholder="<?php echo JText::_("COM_COMMUNITY_ENTER_KEYWORD"); ?>"
                                    data-label-keyword="<?php echo JText::_("COM_COMMUNITY_ENTER_KEYWORD"); ?>"
                                    data-label-hashtag="<?php echo JText::_("COM_COMMUNITY_ENTER_HASHTAG"); ?>"
                                    style="margin-bottom:8px">
                                <button class="joms-button--primary joms-button--full"
                                    data-label-keyword="<?php echo JText::_("COM_COMMUNITY_SEARCH_KEYWORD"); ?>"
                                    data-label-hashtag="<?php echo JText::_("COM_COMMUNITY_SEARCH_HASHTAG"); ?>"
                                    style="margin-bottom:3px"><?php echo JText::_( $filterKey == "hashtag" ? "COM_COMMUNITY_SEARCH_HASHTAG" : "COM_COMMUNITY_SEARCH_KEYWORD"); ?></button>
                            </li>
                        </ul>
                    </form>
                    <div class="joms-activity-filter__options" style="text-align:right">
                        <a href="javascript:" data-ui-object="joms-dropdown-button">
                            <svg viewBox="0 0 14 20" class="joms-icon">
                                <use xlink:href="<?php echo CRoute::getURI(); ?>#joms-icon-cog"></use>
                            </svg>
                        </a>
                        <ul class="joms-dropdown" style="text-align:left">
                            <li class="noselect">
                                <strong><?php echo JText::_("COM_COMMUNITY_SET_AS_DEFAULT"); ?> :</strong> &nbsp;
                                <img src="<?php echo JURI::root(true) ?>/components/com_community/assets/ajax-loader.gif" alt="loader" style="visibility:hidden">
                            </li>
                            <li <?php echo ($defaultFilter == 'all') ? 'class="active"' : ''; ?>>
                                <a href="javascript:" data-value="all">
                                    <svg viewBox="0 0 16 18" class="joms-icon">
                                        <use xlink:href="<?php echo CRoute::getURI(); ?>#joms-icon-star3"></use>
                                    </svg>
                                    <?php echo JText::_("COM_COMMUNITY_FILTERBAR_ALL"); ?>
                                </a>
                            </li>
                            <li <?php echo ($defaultFilter == 'apps:profile') ? 'class="active"' : ''; ?>>
                                <a href="javascript:" data-value="apps:profile">
                                    <svg viewBox="0 0 16 18" class="joms-icon">
                                        <use xlink:href="<?php echo CRoute::getURI(); ?>#joms-icon-pencil"></use>
                                    </svg>
                                    <?php echo JText::_("COM_COMMUNITY_FILTERBAR_STATUS"); ?>
                                </a>
                            </li>
                            <?php if ($config->get('enablephotos')) { ?>
                            <li <?php echo ($defaultFilter == 'apps:photo') ? 'class="active"' : ''; ?>>
                                <a href="javascript:" data-value="apps:photo">
                                    <svg viewBox="0 0 16 18" class="joms-icon">
                                        <use xlink:href="<?php echo CRoute::getURI(); ?>#joms-icon-images"></use>
                                    </svg>
                                    <?php echo JText::_("COM_COMMUNITY_FILTERBAR_PHOTO"); ?>
                                </a>
                            </li>
                            <?php } ?>
                            <?php if ($config->get('enablevideos') == 1) { ?>
                            <li <?php echo ($defaultFilter == 'apps:video') ? 'class="active"' : ''; ?>>
                                <a href="javascript:" data-value="apps:video">
                                    <svg viewBox="0 0 16 18" class="joms-icon">
                                        <use xlink:href="<?php echo CRoute::getURI(); ?>#joms-icon-camera2"></use>
                                    </svg>
                                    <?php echo JText::_("COM_COMMUNITY_FILTERBAR_VIDEO"); ?>
                                </a>
                            </li>
                            <?php } ?>
                            <?php if ($config->get('enablegroups') == 1) { ?>
                            <li <?php echo ($defaultFilter == 'apps:group') ? 'class="active"' : ''; ?>>
                                <a href="javascript:" data-value="apps:group">
                                    <svg viewBox="0 0 16 18" class="joms-icon">
                                        <use xlink:href="<?php echo CRoute::getURI(); ?>#joms-icon-users"></use>
                                    </svg>
                                    <?php echo JText::_("COM_COMMUNITY_FILTERBAR_GROUP"); ?>
                                </a>
                            </li>
                            <?php } ?>
                            <?php if ($config->get('enableevents') == 1) { ?>
                            <li <?php echo ($defaultFilter == 'apps:event') ? 'class="active"' : ''; ?>>
                                <a href="javascript:" data-value="apps:event">
                                    <svg viewBox="0 0 16 18" class="joms-icon">
                                        <use xlink:href="<?php echo CRoute::getURI(); ?>#joms-icon-calendar"></use>
                                    </svg>
                                    <?php echo JText::_("COM_COMMUNITY_FILTERBAR_EVENT"); ?>
                                </a>
                            </li>
                            <?php } ?>
                            <li <?php echo ($defaultFilter == 'privacy:me-and-friends') ? 'class="active"' : ''; ?>>
                                <a href="javascript:" data-value="privacy:me-and-friends">
                                    <svg viewBox="0 0 16 18" class="joms-icon">
                                        <use xlink:href="<?php echo CRoute::getURI(); ?>#joms-icon-user"></use>
                                    </svg>
                                    <?php echo JText::_("COM_COMMUNITY_FILTERBAR_MEANDFRIENDS"); ?>
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            <?php endif; ?>
             <div><?php echo $userActivities; ?></div>
        <?php } ?>

    </div>
</div>

<script>
    joms_filter_params = <?php echo json_encode(
        array(
            'filter'  => isset($filter) ? $filter : '',
            'value'   => isset($filterValue) ? $filterValue : '',
            'hashtag' => isset($filterHashtag) ? $filterHashtag : ''
        )
    ) ?>;
</script>

<?php if ($filterHashtag) { ?>
<script>joms_filter_hashtag = true;</script>
<?php } else if ($filterKeyword) { ?>
<script>joms_filter_keyword = true;</script>
<?php } ?>
