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

$profileModel = CFactory::getModel('profile');
$profile = $profileModel->getViewableProfile($my->id, $my->getProfileType());
$profile = Joomla\Utilities\ArrayHelper::toObject($profile);

$featured = new CFeatured(FEATURED_USERS);
$featuredList = $featured->getItemIds();
$isFeatured = in_array($my->id, $featuredList);

$params = $my->getParams();

$enableSharing = $config->get('enablesharethis') == 1;
$enableReporting = false; // Own's profile, no need to show report button.

$photoModel = CFactory::getModel('photos');
$videoModel = CFactory::getModel('videos');
$groupModel = CFactory::getModel('groups');
$eventModel = CFactory::getModel('events');

$friendCount = $my->_friendcount;
$photoCount = $photoModel->getPhotosCount($my->id);
$videoCount = $videoModel->getVideosCount($my->id);
$groupCount = $groupModel->getGroupsCount($my->id);
$eventCount = $eventModel->getEventsCount($my->id);

$profileFields = '';
$themeModel = CFactory::getModel('theme');
$settings = $themeModel->getSettings('profile');

if (isset($settings['profile']['tagline']) && strlen($settings['profile']['tagline']) ) {
    $blocks = json_decode($settings['profile']['tagline'], true);
    $blockEnabled = true;

    foreach ($blocks as $block) {
        $blockString = "";
        if ($block['spacebefore']) $blockString .= " ";
        if (strlen($block['before'])) $blockString .= JText::_($block['before']) . " ";
        if (strlen($block['field'])) {
            if ( isset($profile->fieldsById->{$block['field']}) && strlen($profile->fieldsById->{$block['field']}->value) ) {
                $blockString .= $themeModel->formatField($profile->fieldsById->{$block['field']});
            } else {
                $blockEnabled = false;
            }
        }
        if (strlen($block['after'])) $blockString .= " " . JText::_($block['after']);
        if ($block['spaceafter']) $blockString .= " ";
        if ($blockEnabled) {
            $profileFields .= $blockString;
        }
    }
}

?>

<div class="joms-module__wrapper"><?php $this->renderModules('js_profile_top'); ?></div>
<div class="joms-module__wrapper--stacked"><?php $this->renderModules('js_profile_top_stacked'); ?></div>
<div class="joms-module__wrapper"><?php $this->renderModules('js_profile_mine_top'); ?></div>
<div class="joms-module__wrapper--stacked"><?php $this->renderModules('js_profile_mine_top_stacked'); ?></div>

<div class="joms-focus">
    <div class="joms-focus__cover">
        <?php if ($isFeatured) { ?>
        <div class="joms-ribbon__wrapper">
            <span class="joms-ribbon joms-ribbon--full"><?php echo JText::_('COM_COMMUNITY_FEATURED'); ?></span>
        </div>
        <?php } ?>

        <div class="joms-focus__cover-image joms-js--cover-image">
            <img src="<?php echo $my->getCover(); ?>" style="width:100%;top:<?php echo $params->get('coverPosition'); ?>" alt="<?php echo $my->getDisplayName(); ?>">
        </div>

        <div class="joms-focus__cover-image--mobile" style="background:url(<?php echo $my->getCover(); ?>) no-repeat center center;"></div>

        <div class="joms-focus__header">
            <div class="joms-avatar--focus">
                <a><img src="<?php echo $my->getAvatar() ?>" alt="<?php echo $my->getDisplayName(); ?>"></a>
            </div>

            <div class="joms-focus__title">
                <h2><?php echo $my->getDisplayName(); ?></h2>

                <div class="joms-focus__header__actions">
                    <a class="joms-button--viewed nolink" title="<?php echo JText::sprintf( $my->getViewCount() > 0 ? 'COM_COMMUNITY_VIDEOS_HITS_COUNT_MANY' : 'COM_COMMUNITY_VIDEOS_HITS_COUNT', $my->getViewCount() ); ?>">
                        <svg viewBox="0 0 16 16" class="joms-icon">
                            <use xlink:href="<?php echo CRoute::getURI(); ?>#joms-icon-eye"></use>
                        </svg>
                        <span><?php echo number_format($my->getViewCount()); ?></span>
                    </a>

                    <?php if ($enableSharing) { ?>
                    <a class="joms-button--shared" title="<?php echo JText::_('COM_COMMUNITY_SHARE_THIS'); ?>">
                        <svg viewBox="0 0 16 16" class="joms-icon">
                            <use xlink:href="<?php echo CRoute::getURI(); ?>#joms-icon-redo"></use>
                        </svg>
                    </a>
                    <?php } ?>

                    <?php if ($enableReporting) { ?>
                    <a class="joms-button--viewed" title="<?php echo JText::_('COM_COMMUNITY_REPORT_USER'); ?>">
                        <svg viewBox="0 0 16 16" class="joms-icon">
                            <use xlink:href="<?php echo CRoute::getURI(); ?>#joms-icon-warning"></use>
                        </svg>
                    </a>
                    <?php } ?>
                </div>

                <div class="joms-focus__info--desktop">
                    <?php echo JHTML::_('string.truncate', $this->escape(strip_tags($profileFields)), 100); ?>
                </div>

            </div>

            <div class="joms-focus__actions__wrapper">

                <div class="joms-focus__actions--desktop"></div>

                <div class="joms-focus__header__actions--desktop">
                    <a class="joms-button--viewed nolink" title="<?php echo JText::sprintf( $my->getViewCount() > 0 ? 'COM_COMMUNITY_VIDEOS_HITS_COUNT_MANY' : 'COM_COMMUNITY_VIDEOS_HITS_COUNT', $my->getViewCount() ); ?>">
                        <svg viewBox="0 0 16 16" class="joms-icon">
                            <use xlink:href="<?php echo CRoute::getURI(); ?>#joms-icon-eye"></use>
                        </svg>
                        <span><?php echo number_format($my->getViewCount()) ;?></span>
                    </a>

                    <?php if ($enableSharing) { ?>
                    <a class="joms-button--shared" title="<?php echo JText::_('COM_COMMUNITY_SHARE_THIS'); ?>">
                        <svg viewBox="0 0 16 16" class="joms-icon">
                            <use xlink:href="<?php echo CRoute::getURI(); ?>#joms-icon-redo"></use>
                        </svg>
                    </a>
                    <?php } ?>

                    <?php if ($enableReporting) { ?>
                    <a class="joms-button--viewed" title="<?php echo JText::_('COM_COMMUNITY_REPORT_USER'); ?>">
                        <svg viewBox="0 0 16 16" class="joms-icon">
                            <use xlink:href="<?php echo CRoute::getURI(); ?>#joms-icon-warning"></use>
                        </svg>
                    </a>
                    <?php } ?>
                </div>

            </div>
        </div>
    </div>

    <?php
        if ($config->get('enable_badges')) {
            $badge = new CBadge($my);
            $badge = $badge->getBadge();
            if ($badge->current) {
    ?>
    <img src="<?php echo $badge->current->image; ?>" alt="<?php echo $badge->current->title; ?>"
        class="joms-focus__badges <?php echo $isFeatured ? 'featured' : ' '; ?>" />
    <?php
            }
        }
    ?>

    <div class="joms-focus__info">
        <?php echo JHTML::_('string.truncate', $this->escape(strip_tags($profileFields)), 100); ?>
    </div>

    <ul class="joms-focus__link">
        <li class="full"><a href="<?php echo CRoute::_('index.php?option=com_community&view=friends&userid='.$profile->id); ?>"><?php echo JText::_($friendCount == 1 ? 'COM_COMMUNITY_FRIENDS_COUNT' : 'COM_COMMUNITY_FRIENDS_COUNT_MANY'); ?> <span class="joms-text--light"><?php echo $friendCount ?></span></a></li>

        <?php if ($config->get('enablephotos')) { ?>
        <li class="half"><a href="<?php echo CRoute::_('index.php?option=com_community&view=photos&task=myphotos&userid='.$profile->id); ?>"><?php echo JText::_($photoCount == 1 ? 'COM_COMMUNITY_PHOTOS_COUNT_SINGULAR' : 'COM_COMMUNITY_PHOTOS_COUNT'); ?> <span class="joms-text--light"><?php echo $photoCount ?></span></a></li>
        <?php } ?>

        <?php if ($config->get('enablevideos')) { ?>
        <li class="half"><a href="<?php echo CRoute::_('index.php?option=com_community&view=videos&task=myvideos&userid='.$profile->id); ?>"><?php echo JText::_($videoCount == 1 ? 'COM_COMMUNITY_VIDEOS_COUNT' : 'COM_COMMUNITY_VIDEOS_COUNT_MANY'); ?> <span class="joms-text--light"><?php echo $videoCount ?></span></a></li>
        <?php } ?>

        <?php if ($config->get('enablegroups')) { ?>
        <li class="half"><a href="<?php echo CRoute::_('index.php?option=com_community&view=groups&task=mygroups&userid='.$profile->id); ?>"><?php echo JText::_($groupCount == 1 ? 'COM_COMMUNITY_GROUPS_COUNT' : 'COM_COMMUNITY_GROUPS_COUNT_MANY'); ?> <span class="joms-text--light"><?php echo $groupCount ?></span></a></li>
        <?php } ?>

        <?php if ($config->get('enableevents')) { ?>
        <li class="half"><a href="<?php echo CRoute::_('index.php?option=com_community&view=events&task=myevents&userid='.$profile->id); ?>"><?php echo JText::_($eventCount == 1 ? 'COM_COMMUNITY_EVENTS_COUNT' : 'COM_COMMUNITY_EVENTS_COUNT_MANY'); ?> <span class="joms-text--light"><?php echo $eventCount ?></span></a></li>
        <?php } ?>
    </ul>
</div>

<div class="clearfix">
    <div class="joms-sidebar">
        <div class="joms-module__wrapper"><?php $this->renderModules('js_side_top'); ?></div>
        <div class="joms-module__wrapper--stacked"><?php $this->renderModules('js_side_top_stacked'); ?></div>
        <div class="joms-module__wrapper"><?php $this->renderModules('js_profile_side_top'); ?></div>
        <div class="joms-module__wrapper--stacked"><?php $this->renderModules('js_profile_side_top_stacked'); ?></div>

        <div class="joms-module__wrapper" style="border:2px dashed #bbb">
            <div class="joms-js--app-pos-sidebar-top">
                <div class="joms-tab__bar">
                    <?php echo $appTitles['sidebar-top']; ?>
                    <a href="javascript:" class="no-padding joms-js--app-new" style="width:30px">
                        <div class="joms-tab__bar--button">
                            <span class="add" onclick="joms.api.appBrowse('sidebar-top');">
                                <svg class="joms-icon" viewBox="0 -5 15 30">
                                    <use xlink:href="#joms-icon-plus"></use>
                                </svg>
                            </span>
                        </div>
                    </a>
                </div>
                <?php echo $appItems['sidebar-top']; ?>
            </div>
        </div>

        <div class="joms-module__wrapper" style="border:2px dashed #bbb">
            <div class="joms-js--app-pos-sidebar-top-stacked">
                <?php echo $appItems['sidebar-top-stacked']; ?>
                <div class="joms-tab__bar">
                    <a href="javascript:" class="no-padding joms-js--app-new">
                        <div class="joms-tab__bar--button">
                            <span class="add" onclick="joms.api.appBrowse('sidebar-top-stacked');">
                                <svg class="joms-icon" viewBox="0 -5 15 30">
                                    <use xlink:href="#joms-icon-plus"></use>
                                </svg>
                            </span>
                        </div>
                    </a>
                </div>
            </div>
        </div>

        <div class="joms-module__wrapper"><?php $this->renderModules('js_profile_mine_side_top'); ?></div>
        <div class="joms-module__wrapper--stacked"><?php $this->renderModules('js_profile_mine_side_top_stacked'); ?></div>

        <?php echo $this->view('profile')->modProfileUserVideo(); ?>

        <div class="joms-module__wrapper"><?php $this->renderModules('js_profile_side_middle'); ?></div>
        <div class="joms-module__wrapper--stacked"><?php $this->renderModules('js_profile_side_middle_stacked'); ?></div>
        <div class="joms-module__wrapper"><?php $this->renderModules('js_profile_mine_side_middle'); ?></div>
        <div class="joms-module__wrapper--stacked"><?php $this->renderModules('js_profile_mine_side_middle_stacked'); ?></div>
        <div class="joms-module__wrapper"><?php  $this->renderModules('js_profile_mine_side_bottom'); ?></div>
        <div class="joms-module__wrapper--stacked"><?php $this->renderModules('js_profile_mine_side_bottom_stacked'); ?></div>

        <div class="joms-module__wrapper" style="border:2px dashed #bbb">
            <div class="joms-js--app-pos-sidebar-bottom">
                <div class="joms-tab__bar">
                    <?php echo $appTitles['sidebar-bottom']; ?>
                    <a href="javascript:" class="no-padding joms-js--app-new" style="width:30px">
                        <div class="joms-tab__bar--button">
                            <span class="add" onclick="joms.api.appBrowse('sidebar-bottom');">
                                <svg class="joms-icon" viewBox="0 -5 15 30">
                                    <use xlink:href="#joms-icon-plus"></use>
                                </svg>
                            </span>
                        </div>
                    </a>
                </div>
                <?php echo $appItems['sidebar-bottom']; ?>
            </div>
        </div>

        <div class="joms-module__wrapper" style="border:2px dashed #bbb">
            <div class="joms-js--app-pos-sidebar-bottom-stacked">
                <?php echo $appItems['sidebar-bottom-stacked']; ?>
                <div class="joms-tab__bar">
                    <a href="javascript:" class="no-padding joms-js--app-new">
                        <div class="joms-tab__bar--button">
                            <span class="add" onclick="joms.api.appBrowse('sidebar-bottom-stacked');">
                                <svg class="joms-icon" viewBox="0 -5 15 30">
                                    <use xlink:href="#joms-icon-plus"></use>
                                </svg>
                            </span>
                        </div>
                    </a>
                </div>
            </div>
        </div>

        <div class="joms-module__wrapper"><?php $this->renderModules('js_profile_side_bottom'); ?></div>
        <div class="joms-module__wrapper--stacked"><?php $this->renderModules('js_profile_side_bottom_stacked'); ?></div>
        <div class="joms-module__wrapper"><?php $this->renderModules('js_side_bottom'); ?></div>
        <div class="joms-module__wrapper--stacked"><?php $this->renderModules('js_side_bottom_stacked'); ?></div>
    </div>

    <div class="joms-main">

        <div class="joms-module__wrapper"><?php $this->renderModules('js_profile_feed_top'); ?></div>
        <div class="joms-module__wrapper--stacked"><?php $this->renderModules('js_profile_feed_top_stacked'); ?></div>

        <div data-ui-object="frontpage-main">
            <div class="joms-middlezone" data-ui-object="joms-tabs">
                <div class="joms-js--app-pos-content">
                    <div class="joms-tab__bar" style="border:2px dashed #bbb">
                        <a href="#joms-js--app-tab-feeds" class="no-padding active">
                            <div class="joms-tab__bar--button">
                                <span class="title"><?php echo ucfirst(JText::_('COM_COMMUNITY_SINGULAR_STREAM')); ?></span>
                            </div>
                        </a>
                        <a href="#joms-app--aboutme-special" class="no-padding">
                            <div class="joms-tab__bar--button">
                                <span class="title"><?php echo $aboutTitle; ?></span>
                            </div>
                        </a>
                        <?php echo $appTitles['content']; ?>
                        <a href="javascript:" class="no-padding joms-js--app-new" style="width:30px">
                            <div class="joms-tab__bar--button">
                                <span class="add" onclick="joms.api.appBrowse('content');">
                                    <svg class="joms-icon" viewBox="0 -5 15 30">
                                        <use xlink:href="#joms-icon-plus"></use>
                                    </svg>
                                </span>
                            </div>
                        </a>
                    </div>
                    <div class="joms-tab__content" id="joms-js--app-tab-feeds">
                        <div class="joms-gap"></div>
                        <div class="joms-stream">
                            <div class="joms-stream__header">
                                <div class="joms-avatar--stream">
                                    <a><img src="<?php echo $my->getAvatar() ?>" alt="<?php echo $my->getDisplayName(); ?>"></a>
                                </div>
                                <div class="joms-stream__meta">
                                    <a class="joms-stream__user active"><?php echo $my->getDisplayName(); ?></a>
                                    <a>
                                        <span class="joms-stream__time">
                                            <small><?php echo JText::_('COM_COMMUNITY_LAPSED_LESS_THAN_A_MINUTE'); ?></small>
                                        </span>
                                    </a>
                                </div>
                            </div>
                            <div class="joms-stream__body">
                                <p data-type="stream-content">
                                    <span style="opacity:.6"><?php echo JText::_('COM_COMMUNITY_STREAM_EXAMPLE_CONTENT'); ?></span>
                                </p>
                            </div>
                            <div class="joms-stream__actions">
                                <a><span><?php echo JText::_('COM_COMMUNITY_LIKE'); ?></span></a>
                            </div>
                            <div class="joms-comment__reply">
                                <div class="joms-textarea__wrapper">
                                    <div class="joms-textarea joms-textarea__beautifier"></div>
                                    <textarea class="joms-textarea" placeholder="<?php echo JText::_('COM_COMMUNITY_WRITE_A_COMMENT'); ?>" readonly="readonly"></textarea>
                                </div>
                                <svg viewBox="0 0 16 16" class="joms-icon joms-icon--add">
                                    <use xlink:href="#joms-icon-camera"></use>
                                </svg>
                                <span>
                                    <button class="joms-button--comment" readonly="readonly">
                                        <?php echo JText::_('COM_COMMUNITY_SEND'); ?>
                                    </button>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div id="joms-app--aboutme-special" class="joms-tab__content" style="display:none">
                        <div class="joms-gap"></div>
                        <div class="joms-app--wrapper"><?php echo $aboutItem; ?></div>
                    </div>
                    <?php echo $appItems['content']; ?>
                </div>
            </div>
        </div>

        <div class="joms-module__wrapper"><?php $this->renderModules('js_profile_feed_bottom'); ?></div>
        <div class="joms-module__wrapper--stacked"><?php $this->renderModules('js_profile_feed_bottom_stacked'); ?></div>
    </div>

    <div class="joms-module__wrapper"><?php $this->renderModules('js_profile_mine_bottom'); ?></div>
    <div class="joms-module__wrapper--stacked"><?php $this->renderModules('js_profile_mine_bottom_stacked'); ?></div>
    <div class="joms-module__wrapper"><?php $this->renderModules('js_profile_bottom'); ?></div>
    <div class="joms-module__wrapper--stacked"><?php $this->renderModules('js_profile_bottom_stacked'); ?></div>

</div>
