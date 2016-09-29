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

$params = $user->getParams();
$config = CFactory::getConfig();
$my = CFactory::getUser();
$isMine = COwnerHelper::isMine($my->id, $user->id);
$isFriend = CFriendsHelper::isConnected($user->id, $my->id) && $user->id != $my->id;
$isWaitingApproval = CFriendsHelper::isWaitingApproval($my->id, $user->id);
$isWaitingResponse = CFriendsHelper::isWaitingApproval($user->id, $my->id);
$isBlocked = $user->isBlocked();

//links information
$photoEnabled = ($config->get('enablephotos')) ? true : false;
$eventEnabled = ($config->get('enableevents')) ? true : false;
$groupEnabled = ($config->get('enablegroups')) ? true : false;
$videoEnabled = ($config->get('enablevideos')) ? true : false;

//likes
CFactory::load('libraries', 'like');
$like = new Clike();
$isLikeEnabled = $like->enabled('profile') && $params->get('profileLikes', 1) ? 1 : 0;
$isUserLiked = $like->userLiked('profile', $user->id, $my->id);
/* likes count */
$likes = $like->getLikeCount('profile', $user->id);

$profileFields = '';
$themeModel = CFactory::getModel('theme');
$profileModel = CFactory::getModel('profile');
$settings = $themeModel->getSettings('profile');

$profile = $profileModel->getViewableProfile($user->id, $user->getProfileType());
$profile = Joomla\Utilities\ArrayHelper::toObject($profile);

$groupmodel = CFactory::getModel('groups');
$profile->_groups = $groupmodel->getGroupsCount($profile->id);

$eventmodel = CFactory::getModel('events');
$profile->_events = $eventmodel->getEventsCount($profile->id);

$profile->_friends = $user->_friendcount;

$videoModel = CFactory::getModel('Videos');
$profile->_videos = $videoModel->getVideosCount($profile->id);

$photosModel = CFactory::getModel('photos');
$profile->_photos = $photosModel->getPhotosCount($profile->id);

if(isset($settings['profile']['tagline']) && strlen($settings['profile']['tagline'])) {

    $blocks = json_decode($settings['profile']['tagline'], true);

    $blockEnabled = true;
    foreach ($blocks as $block) {

        $blockString = "";

        if($block['spacebefore']) $blockString .= " ";

        if(strlen($block['before'])) $blockString .= JText::_($block['before']) . " ";

        if(strlen($block['field'])) {

            if (
                isset($profile->fieldsById->{$block['field']}) &&
                strlen($profile->fieldsById->{$block['field']}->value)
            ) {
               # var_dump($profile->fieldsById->{$block['field']});
                $blockString .= $themeModel->formatField($profile->fieldsById->{$block['field']});
            } else {
                $blockEnabled = false;
            }
        }
        if(strlen($block['after'])) $blockString .= " ".JText::_($block['after']);

        if($block['spaceafter']) $blockString .= " ";

        if($blockEnabled) {
            $profileFields .= $blockString;
        }
    }
}

?>

<div class="joms-hcard">
    <div class="joms-hcard__cover">
        <img src="<?php echo $user->getCover(); ?>" alt="<?php echo $user->name; ?>" style="width:100%;top:<?php echo $params->get('coverPosition', ''); ?>">
        <div class="joms-hcard__info">
            <div class="joms-avatar">
                <a href="<?php echo CUrlHelper::userLink($user->id); ?>"><img src="<?php echo $user->getThumbAvatar(); ?>" alt="<?php echo $user->getDisplayName(); ?>"></a>
            </div>
            <div class="joms-hcard__info-content">
                <h3 class="reset-gap"><?php echo $user->getDisplayName(); ?></h3>
                <div class="joms-gap--small"></div>
                <span class="joms-text--small"><?php echo JHTML::_('string.truncate', $this->escape(strip_tags($profileFields)), 100); ?></span>
            </div>
        </div>

    </div>

    <ul class="joms-focus__link">

        <li class="full"><a href="<?php echo CRoute::_('index.php?option=com_community&view=friends&userid='.$profile->id); ?>">
            <?php echo ($profile->_friends == 1) ? JText::_('COM_COMMUNITY_FRIENDS_COUNT') . ' <span class="joms-text--light">' . $profile->_friends . '</span>' : JText::_('COM_COMMUNITY_FRIENDS_COUNT_MANY') . ' <span class="joms-text--light">' . $profile->_friends . '</span>' ?> </a></li>

        <?php if($photoEnabled) {?>
        <li class="half"><a href="<?php echo CRoute::_('index.php?option=com_community&view=photos&task=myphotos&userid='.$profile->id); ?>"><?php echo ($profile->_photos == 1) ? JText::_('COM_COMMUNITY_PHOTOS_COUNT_SINGULAR') . ' <span class="joms-text--light">' . $profile->_photos . '</span>' :  JText::_('COM_COMMUNITY_PHOTOS_COUNT') . ' <span class="joms-text--light">' . $profile->_photos . '</span>' ?></a></li>
        <?php }?>

        <?php if($videoEnabled) {?>
        <li class="half"><a href="<?php echo CRoute::_('index.php?option=com_community&view=videos&task=myvideos&userid='.$profile->id); ?>"><?php echo ($profile->_videos == 1) ?  JText::_('COM_COMMUNITY_VIDEOS_COUNT') . ' <span class="joms-text--light">' . $profile->_videos . '</span>' : JText::_('COM_COMMUNITY_VIDEOS_COUNT_MANY') . ' <span class="joms-text--light">' . $profile->_videos . '</span>' ?></a></li>
        <?php }?>

        <?php if($groupEnabled) {?>
        <li class="half"><a href="<?php echo CRoute::_('index.php?option=com_community&view=groups&task=mygroups&userid='.$profile->id); ?>"><?php echo ($profile->_groups == 1) ?  JText::_('COM_COMMUNITY_GROUPS_COUNT') . ' <span class="joms-text--light">' . $profile->_groups . '</span>' : JText::_('COM_COMMUNITY_GROUPS_COUNT_MANY') . ' <span class="joms-text--light">' . $profile->_groups . '</span>' ?></a></li>
        <?php }?>

        <?php if($eventEnabled) {?>
        <li class="half"><a href="<?php echo CRoute::_('index.php?option=com_community&view=events&task=myevents&userid='.$profile->id); ?>"><?php echo ($profile->_events == 1) ? JText::_('COM_COMMUNITY_EVENTS_COUNT') . ' <span class="joms-text--light">' . $profile->_events . '</span>' : JText::_('COM_COMMUNITY_EVENTS_COUNT_MANY') . ' <span class="joms-text--light">' . $profile->_events . '</span>' ?></a></li>
        <?php }?>
    </ul>

    <?php if ( !$isMine ) { ?>

        <div class="joms-hcard__actions">
            <!-- Friending buton -->
            <?php if ( $isFriend ) { ?>
                <a href="javascript:" class="joms-button--neutral joms-button--small" onclick="joms.api.friendRemove('<?php echo $profile->id;?>')">
                    <?php echo JText::_('COM_COMMUNITY_FRIENDS_REMOVE'); ?>
                </a>
            <?php } else if ( !$isBlocked ) { ?>
                <?php if ( $isWaitingApproval ) { ?>
                    <a href="javascript:" class="joms-button--neutral joms-button--small" onclick="joms.api.friendAdd('<?php echo $profile->id;?>')">
                        <?php echo JText::_('COM_COMMUNITY_PROFILE_PENDING_FRIEND_REQUEST'); ?>
                    </a>
                <?php } else if ( $isWaitingResponse ) { ?>
                    <a href="javascript:" class="joms-button--neutral joms-button--small" onclick="joms.api.friendResponse('<?php echo $profile->id;?>')">
                        <?php echo JText::_('COM_COMMUNITY_PROFILE_PENDING_FRIEND_REQUEST'); ?>
                    </a>
                <?php } else { ?>
                    <a href="javascript:" class="joms-button--neutral joms-button--small" onclick="joms.api.friendAdd('<?php echo $profile->id;?>')">
                        <?php echo JText::_('COM_COMMUNITY_PROFILE_ADD_AS_FRIEND'); ?>
                    </a>
                <?php } ?>
            <?php } ?>

            <!-- Send Message button -->
            <?php if ( $config->get('enablepm') ) { ?>
                <a href="javascript:" class="joms-button--neutral joms-button--small" onclick="joms.api.pmSend('<?php echo $profile->id;?>');">
                    <?php echo JText::_('COM_COMMUNITY_INBOX_SEND_MESSAGE'); ?>
                </a>
            <?php } ?>
        </div>
    <?php } ?>

</div>
