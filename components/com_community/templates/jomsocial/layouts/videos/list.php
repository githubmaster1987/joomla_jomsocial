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

$config = CFactory::getConfig();
$isVideoModal = $config->get('video_mode') == 1;

?>

<?php if ($videos) { ?>
    <div class="joms-gap"></div>
    <ul class="joms-list--video" >
        <?php $i = 0;
        foreach ($videos as $video) {
            $params = new CParameter($video->params);
            ?>

            <li class="joms-list__item video-permission-<?php echo $video->permissions; ?>">
                <a href="<?php echo $video->getURL(); ?>" title="<?php echo $video->getTitle() ?>"
                    <?php if ($isVideoModal) { ?>
                    onclick="joms.api.videoOpen('<?php echo $video->getId(); ?>'); return false;"
                    <?php } ?>>

                    <?php  if (in_array($video->id, $featuredList)) { ?>
                    <div class="joms-ribbon__wrapper">
                        <span class="joms-ribbon"><?php echo JText::_('COM_COMMUNITY_FEATURED'); ?></span>
                    </div>
                    <?php } ?>

                    <?php if ($video->status != 'pending') { ?>
                        <img src="<?php echo $video->getThumbnail(); ?>" class="joms-list__cover" alt="<?php echo $video->getTitle() ?>" >
                        <span class="joms-video__duration"><?php echo $video->getDurationInHMS(); ?></span>
                    <?php  } else { ?>
                        <img src="<?php echo JURI::root(true);?>/components/com_community/assets/video_thumb.png" alt="video" />
                    <?php } ?>
                </a>

                <?php if ($isCommunityAdmin || ($video->isOwner() && !$groupVideo) || ($groupVideo && $allowManageVideos) || $my->authorise('community.delete', 'videos', $video)) { ?>
                <div class="joms-focus__button--options--desktop">
                    <a class="joms-button--options" data-ui-object="joms-dropdown-button" href="javascript:">
                        <svg class="joms-icon" viewBox="0 0 16 16">
                            <use xlink:href="<?php echo CRoute::getURI(); ?>#joms-icon-cog"></use>
                        </svg>
                    </a>
                    <ul class="joms-dropdown">
                        <?php if ($my->authorise('community.edit', 'videos', $video)) { ?>
                        <li><a href="javascript:" onclick="joms.api.videoEdit('<?php echo $video->getId(); ?>');"><?php echo JText::_('COM_COMMUNITY_EDIT'); ?></a></li>
                        <?php } ?>
                        <?php if ($my->authorise('community.delete', 'videos', $video)) { ?>
                        <li><a href="javascript:" onclick="joms.api.videoRemove('<?php echo $video->getId(); ?>');"><?php echo JText::_('COM_COMMUNITY_DELETE'); ?></a></li>
                        <?php } ?>
                        <?php if ($isCommunityAdmin && !$groupVideo && $showFeatured && ($video->permissions == 0 || $video->permissions == 10)) { ?>
                        <?php if (!in_array($video->id, $featuredList)) { ?>
                        <li><a onclick="joms.api.videoAddFeatured('<?php echo $video->getId(); ?>');" href="javascript:"><?php echo JText::_('COM_COMMUNITY_MAKE_FEATURED'); ?></a></li>
                        <?php } else { ?>
                        <li><a onclick="joms.api.videoRemoveFeatured('<?php echo $video->getId(); ?>');" href="javascript:"><?php echo JText::_('COM_COMMUNITY_REMOVE_FEATURED'); ?></a></li>
                        <?php } ?>
                        <?php } ?>
                    </ul>
                </div>
                <?php } ?>

                <div class="joms-gap--small"></div>

                <?php
                // Show access class for "friends (30)" or "me only (40)"
                $accessClass = 'public'; // NO need to display this
                $accessClass = ($video->permissions == PRIVACY_MEMBERS) ? 'users' : $accessClass;
                $accessClass = ($video->permissions == PRIVACY_FRIENDS) ? 'user' : $accessClass;
                $accessClass = ($video->permissions == PRIVACY_PRIVATE) ? 'lock' : $accessClass;

                $accessTitle = "";
                $accessTitle = ($accessClass == 'users') ? JText::_('COM_COMMUNITY_PRIVACY_TITLE_SITE_MEMBERS') : $accessTitle;
                $accessTitle = ($accessClass == 'user') ? JText::_('COM_COMMUNITY_PRIVACY_TITLE_FRIENDS') : $accessTitle;
                $accessTitle = ($accessClass == 'lock') ? JText::_('COM_COMMUNITY_PRIVACY_TITLE_ME') : $accessTitle;
                ?>

                <?php if ($video->isPending()) { ?>
                    <h4 class="joms-text--title">
                    <?php echo $video->getTitle(); ?>
                    <?php if ($accessClass != 'public') { ?>
                    <svg viewBox="0 0 16 16" class="joms-icon joms-show-<?php echo $accessClass; ?>">
                        <use xlink:href="#joms-icon-<?php echo $accessClass; ?>"></use>
                    </svg>
                    <?php } ?>
                    </h4>
                <?php } else { ?>
                    <h4 class="joms-text--title">
                        <a href="<?php echo $video->getURL(); ?>" title="<?php echo $video->getTitle() ?>"
                            <?php if ($isVideoModal) { ?>
                            onclick="joms.api.videoOpen('<?php echo $video->getId(); ?>'); return false;"
                            <?php } ?>>
                            <?php echo JHTML::_('string.truncate', $video->getTitle(), 80); ?>
                        </a>

                    </h4>
                    <div class="joms-block joms-text--light">
                        <?php if ($accessClass != 'public') { ?>
                        <svg viewBox="0 0 16 20" class="joms-icon joms-show-<?php echo $accessClass; ?>">
                            <use xlink:href="#joms-icon-<?php echo $accessClass; ?>"></use>
                        </svg>
                        <?php } ?>
                        <span><?php
                        if (CStringHelper::isPlural($video->getHits())) {
                            echo JText::sprintf('COM_COMMUNITY_VIDEOS_HITS_COUNT_MANY', $video->getHits());
                        } else {
                            echo JText::sprintf('COM_COMMUNITY_VIDEOS_HITS_COUNT', $video->getHits());
                        }
                        ?></span> ·
                        <span><?php echo $video->getLastUpdated(); ?></span>
                    </div>

                <?php } ?>

                <small>
                    <a href="<?php echo CRoute::_('index.php?option=com_community&view=profile&userid=' . $video->creator); ?>"><?php echo $video->getCreatorName(); ?></a>
                    <?php if($video->groupid){
                        $group = JTable::getInstance('Group', 'CTable');
                        $group->load($video->groupid);
                        echo JText::sprintf('COM_COMMUNITY_VIDEOS_FROM_GROUP','<a href="' . CUrlHelper::groupLink($group->id) . '">' . $group->name. '</a>');
                    }elseif($video->eventid){
                        $event = JTable::getInstance('Event', 'CTable');
                        $event->load($video->eventid);
                        echo JText::sprintf('COM_COMMUNITY_VIDEOS_FROM_EVENT','<a href="' . CUrlHelper::eventLink($event->id) . '">' . $event->title. '</a>');
                    }elseif($params->get('activity_id')){
                        $targetUser = CFactory::getUser($params->get('target_id'));
                        ?>
                        ▶ <?php echo CLinkGeneratorHelper::getUserURL($targetUser->id, $targetUser->getDisplayName()); ?> <a href="<?php echo CUrlHelper::streamURI($params->get('activity_id'),$targetUser->id) ?>"><?php echo JText::_('COM_COMMUNITY_SINGULAR_STREAM'); ?></a>
                    <?php } ?>
                </small>

            </li>
            <?php } //end foreach ?>

    </ul>

    <?php
} else {
    $mainframe = JFactory::getApplication();
    $jinput = $mainframe->input;
    $task = $jinput->get('task');
    switch ($task) {
        case 'mypendingvideos':
            $msg = JText::_('COM_COMMUNITY_VIDEOS_PENDING_VIDEOS');
            break;
        case 'search':
            $msg = JText::_('COM_COMMUNITY_NO_RESULT');
            break;
        case 'myvideos':
            $isMine = ($user->id == $my->id);
            $msg = $isMine ? JText::_('COM_COMMUNITY_VIDEOS_NO_VIDEO') : JText::sprintf('COM_COMMUNITY_VIDEOS_NO_VIDEOS', $user->getDisplayName());
            break;
        default:
            $msg = JText::_('COM_COMMUNITY_VIDEOS_NO_VIDEO');
            break;
    }
    ?>
    <div class="cAlert cEmpty"><?php echo $msg; ?></div>
    <?php
}
?>

<?php if (isset($pagination) && $pagination->getPagesLinks() && ($pagination->pagesTotal > 1 || $pagination->total > 1) ) { ?>
    <div class="joms-pagination">
        <?php echo $pagination->getPagesLinks(); ?>
    </div>
<?php } ?>
