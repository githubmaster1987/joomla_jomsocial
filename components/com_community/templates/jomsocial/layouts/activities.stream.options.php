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

$activity = new CActivity($act);
$permission = $activity->getPermission($my->id);
$allowPrivacy = true;
if ( !CActivitiesHelper::getStreamPermission($act->actor) ) {
    $allowPrivacy = false;
} else if (
    $act->app === 'albums.comment' ||
    $act->app === 'photos.comment' ||
    $act->app === 'videos.comment' ||
    $act->app === 'cover.upload'   ||
    $act->app === 'groups.discussion.reply' ||
    $act->app === 'groups.discussion' ||
    strpos($act->app,'like') !== false ||
    strpos($act->app,'system') !== false ||
    $act->groupid || //since the permission will follow group permission
    strpos($act->app,'events.') !== false ||
    $act->eventid // event activity should follow the event permission
) {
    $allowPrivacy = false;
}

?>

<div class="joms-list__options">

    <?php if ($permission->showButton || ($my->id == 0 && CFactory::getConfig()->get('enableguestreporting')) ) { ?>
    <a href="javascript:" class="joms-button--options" data-ui-object="joms-dropdown-button">
        <svg viewBox="0 0 16 16" class="joms-icon">
            <use xlink:href="<?php echo JUri::getInstance(); ?>#joms-icon-arrow-down"></use>
        </svg>
    </a>
    <?php } ?>

    <ul class="joms-dropdown">

        <?php if (CFactory::getConfig()->get('enablereporting') && (($my->id == 0 && CFactory::getConfig()->get('enableguestreporting')) || ($my->id > 0 && $my->id != $act->actor)) && strpos($act->app,'featured') === false && !COwnerHelper::isCommunityAdmin() ) { ?>
        <li>
            <a href="javascript:" data-propagate="1" onclick="joms.api.streamReport('<?php echo $act->id; ?>');">
                <?php echo JText::_('COM_COMMUNITY_REPORT'); ?>
            </a>
        </li>
        <li class="separator"></li>

        <?php } ?>

        <?php if ($allowPrivacy) { ?>
        <li>
            <a href="javascript:" data-propagate="1" onclick="joms.api.streamSelectPrivacy('<?php echo $act->id; ?>');">
                <?php echo JText::_('COM_COMMUNITY_PROFILE_PREFERENCES_PRIVACY'); ?>
            </a>
        </li>
        <?php } ?>

        <?php if ($permission->hideStream) { ?>
        <li>
            <a href="javascript:" data-propagate="1" onclick="joms.api.streamHide('<?php echo $act->id; ?>', '<?php echo $act->actor; ?>');">
                <?php echo JText::_('COM_COMMUNITY_HIDE_ACTIVITY'); ?>
            </a>
        </li>
        <?php if ($permission->ignoreStream  && !$activity->actors && $act->actor) { ?>
        <li>
            <a href="javascript:" data-propagate="1" onclick="joms.api.userIgnore('<?php echo $act->actor; ?>');">
                <?php echo JText::sprintf('COM_COMMUNITY_HIDE_ALL_FROM',CFactory::getUser($activity->actor)->getDisplayName()); ?>
            </a>
        </li>
        <?php } ?>
        <?php } ?>

        <?php if($permission->featureActivity){ ?>
        <li>
            <a href="javascript:" data-propagate="1" onclick="joms.api.streamAddFeatured('<?php echo $act->id; ?>');">
                <?php echo JText::_('COM_COMMUNITY_STREAM_ACTIVITY_FEATURE'); ?>
            </a>
        </li>
        <?php }elseif($permission->unfeatureActivity){ ?>
            <li>
                <a href="javascript:" data-propagate="1" onclick="joms.api.streamRemoveFeatured('<?php echo $act->id; ?>');">
                    <?php echo JText::_('COM_COMMUNITY_STREAM_ACTIVITY_UNFEATURE'); ?>
                </a>
            </li>
        <?php } ?>

        <?php if ($permission->editPost || $permission->deletePost) { ?>
        <?php if ($permission->editPost) { ?>
        <li>
            <a href="javascript:" data-propagate="1" onclick="joms.api.streamEdit('<?php echo $act->id; ?>', this);">
                <?php echo JText::_('COM_COMMUNITY_ACTIVITY_EDIT_POST'); ?>
            </a>
        </li>
        <?php } ?>
        <?php if ($permission->deletePost) { ?>
        <li>
            <a href="javascript:" data-propagate="1" onclick="joms.api.streamRemove('<?php echo $act->id; ?>');">
                <?php echo JText::_('COM_COMMUNITY_ACTIVITY_DELETE_POST'); ?>
            </a>
        </li>
        <?php } ?>
        <?php } ?>

        <?php if ($permission->deleteLocation) { ?>
        <li>
            <a href="javascript:" data-propagate="1" onclick="joms.api.streamEditLocation('<?php echo $act->id; ?>', this);">
                <?php echo JText::_('COM_COMMUNITY_ACTIVITY_EDIT_LOCATION'); ?>
            </a>
        </li>
        <?php } ?>

        <?php if ($permission->deleteLocation) { ?>
        <li>
            <a href="javascript:" data-propagate="1" onclick="joms.api.streamRemoveLocation('<?php echo $act->id; ?>');">
                <?php echo JText::_('COM_COMMUNITY_ACTIVITY_DELETE_LOCATION'); ?>
            </a>
        </li>
        <?php } ?>

        <?php if ($permission->deleteMood) { ?>
        <li>
            <a href="javascript:" data-propagate="1" onclick="joms.api.streamRemoveMood('<?php echo $act->id; ?>');">
                <?php echo JText::_('COM_COMMUNITY_ACTIVITY_REMOVE_MOOD'); ?>
            </a>
        </li>
        <?php } ?>

        <?php if ( CActivitiesHelper::hasTag($my->id, $act->title) ) { ?>
        <li class="joms-js--contextmenu-removetag">
            <a href="javascript:" data-propagate="1" onclick="joms.api.streamRemoveTag('<?php echo $act->id; ?>');">
                <?php echo JText::_('COM_COMMUNITY_ACTIVITY_REMOVE_TAG'); ?>
            </a>
        </li>
        <?php } ?>

    </ul>

</div>
