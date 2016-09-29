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

$isMine = COwnerHelper::isMine($my->id, $user->id);
$avatar = $user->getAvatarInfo();
$loggedIn = $my->id != 0;

$profileFields = '';
$themeModel = CFactory::getModel('theme');
$profileModel = CFactory::getModel('profile');
$settings = $themeModel->getSettings('profile');

$my = CFactory::getUser($profile->id);
$config = CFactory::getConfig();

$featured = new CFeatured(FEATURED_USERS);
$featuredList = $featured->getItemIds();

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

$enableReporting = false;
if ( !$isMine && $config->get('enablereporting') == 1 && ( $my->id > 0 || $config->get('enableguestreporting') == 1 ) ) {
    $enableReporting = true;
}

?>

<div class="joms-focus">

    <div class="joms-focus__cover">
        <?php  if (in_array($profile->id, $featuredList)) { ?>
        <div class="joms-ribbon__wrapper">
            <span class="joms-ribbon joms-ribbon--full"><?php echo JText::_('COM_COMMUNITY_FEATURED'); ?></span>
        </div>
        <?php } ?>

        <div class="joms-focus__cover-image joms-js--cover-image">
            <img src="<?php echo $profile->cover; ?>" alt="<?php echo $user->getDisplayName(); ?>"
            <?php if (!$profile->defaultCover && $profile->coverAlbum) { ?>
                style="width:100%;top:<?php echo $profile->coverPostion; ?>"
                onclick="joms.api.coverClick(<?php echo $profile->coverAlbum ?>, <?php echo $profile->coverPhoto ?>);"
            <?php } else { ?>
                style="width:100%;top:<?php echo $profile->coverPostion; ?>"
            <?php } ?>>
        </div>

        <div class="joms-focus__cover-image--mobile joms-js--cover-image-mobile"
            <?php if (!$profile->defaultCover && $profile->coverAlbum) { ?>
                style="background:url(<?php echo $profile->cover; ?>) no-repeat center center"
                onclick="joms.api.coverClick(<?php echo $profile->coverAlbum ?>, <?php echo $profile->coverPhoto ?>);"
            <?php } else { ?>
                style="background:url(<?php echo $profile->cover; ?>) no-repeat center center"
            <?php } ?>>
        </div>

        <div class="joms-focus__header">
            <div class="joms-avatar--focus <?php echo CUserHelper::onlineIndicator($user); ?>">
                <a <?php if ( !$profile->defaultAvatar && $profile->avatarAlbum ) { ?>
                    href="<?php echo CRoute::_('index.php?option=com_community&view=photos&task=photo&albumid=' . $profile->avatarAlbum); ?>" style="cursor:default"
                    onclick="joms.api.photoOpen(<?php echo $profile->avatarAlbum ?>, <?php echo $profile->avatarPhoto ?>); return false;"
                <?php } ?>>
                    <img src="<?php echo $user->getAvatar() . '?_=' . time(); ?>" alt="<?php echo $this->escape( $user->getDisplayName() ); ?>">
                    <?php if ($isMine || COwnerHelper::isCommunityAdmin()) { ?>
                    <svg class="joms-icon joms-avatar-change" viewBox="0 0 16 16" onclick="joms.api.avatarChange('profile', '<?php echo $profile->id; ?>', arguments && arguments[0]); return false; ">
                        <use xlink:href="#joms-icon-camera"></use>
                    </svg>
                    <?php } ?>
                </a>
            </div>

            <div class="joms-focus__title">
                <h2><?php echo $user->getDisplayName(); ?></h2>

                <div class="joms-focus__header__actions">

                    <a class="joms-button--viewed nolink" title="<?php echo JText::sprintf( $user->getViewCount() > 0 ? 'COM_COMMUNITY_VIDEOS_HITS_COUNT_MANY' : 'COM_COMMUNITY_VIDEOS_HITS_COUNT', $user->getViewCount() ); ?>">
                        <svg viewBox="0 0 16 16" class="joms-icon">
                            <use xlink:href="<?php echo CRoute::getURI(); ?>#joms-icon-eye"></use>
                        </svg>
                        <span><?php echo number_format($user->getViewCount()); ?></span>
                    </a>

                    <?php if ($config->get('enablesharethis') == 1) { ?>
                    <a class="joms-button--shared" title="<?php echo JText::_('COM_COMMUNITY_SHARE_THIS'); ?>"
                            href="javascript:" onclick="joms.api.pageShare('<?php echo CRoute::getExternalURL('index.php?option=com_community&view=profile&userid=' . $profile->id); ?>')">
                        <svg viewBox="0 0 16 16" class="joms-icon">
                            <use xlink:href="<?php echo CRoute::getURI(); ?>#joms-icon-redo"></use>
                        </svg>
                    </a>
                    <?php } ?>

                    <?php if ($enableReporting) { ?>
                    <a class="joms-button--viewed" title="<?php echo JText::_('COM_COMMUNITY_REPORT_USER'); ?>"
                            href="javascript:" onclick="joms.api.userReport('<?php echo $user->id; ?>');">
                        <svg viewBox="0 0 16 16" class="joms-icon">
                            <use xlink:href="<?php echo CRoute::getURI(); ?>#joms-icon-warning"></use>
                        </svg>
                    </a>
                    <?php } ?>

                </div>

                <div class="joms-focus__info--desktop">
                    <?php echo JHTML::_('string.truncate', $this->escape(strip_tags(str_replace("&quot;",'"',$profileFields))), 140); ?>
                </div>

            </div>

            <div class="joms-focus__actions__wrapper">

                <div class="joms-focus__actions--desktop">
                    <?php if ( !$isMine ) { ?>

                        <!-- Friending buton -->
                        <?php if ( $isFriend ) { ?>
                            <a href="javascript:" class="joms-focus__button--add" onclick="joms.api.friendRemove('<?php echo $profile->id;?>')">
                                <?php echo JText::_('COM_COMMUNITY_FRIENDS_REMOVE'); ?>
                            </a>
                        <?php } else if ( !$isBlocked ) { ?>
                            <?php if ( $isWaitingApproval ) { ?>
                                <a href="javascript:" class="joms-focus__button--add" onclick="joms.api.friendAdd('<?php echo $profile->id;?>')">
                                    <?php echo JText::_('COM_COMMUNITY_PROFILE_PENDING_FRIEND_REQUEST'); ?>
                                </a>
                            <?php } else if ( $isWaitingResponse ) { ?>
                                <a href="javascript:" class="joms-focus__button--add" onclick="joms.api.friendResponse('<?php echo $profile->id;?>')">
                                    <?php echo JText::_('COM_COMMUNITY_PROFILE_PENDING_FRIEND_REQUEST'); ?>
                                </a>
                            <?php } else { ?>
                                <a href="javascript:" class="joms-focus__button--add" onclick="joms.api.friendAdd('<?php echo $profile->id;?>')">
                                    <?php echo JText::_('COM_COMMUNITY_PROFILE_ADD_AS_FRIEND'); ?>
                                </a>
                            <?php } ?>
                        <?php } ?>

                        <!-- Send Message button -->
                        <?php if ( $config->get('enablepm') ) { ?>
                            <a href="javascript:" class="joms-focus__button--message" onclick="<?php echo $sendMsg; ?>">
                                <?php echo JText::_('COM_COMMUNITY_INBOX_SEND_MESSAGE'); ?>
                            </a>
                        <?php } ?>


                    <?php } ?>
                </div>

                <div class="joms-focus__header__actions--desktop">

                    <a class="joms-button--viewed nolink" title="<?php echo JText::sprintf( $user->getViewCount() > 0 ? 'COM_COMMUNITY_VIDEOS_HITS_COUNT_MANY' : 'COM_COMMUNITY_VIDEOS_HITS_COUNT', $user->getViewCount() ); ?>">
                        <svg viewBox="0 0 16 16" class="joms-icon">
                            <use xlink:href="<?php echo CRoute::getURI(); ?>#joms-icon-eye"></use>
                        </svg>
                        <span><?php echo number_format($user->getViewCount()) ;?></span>
                    </a>

                    <?php if ($config->get('enablesharethis') == 1) { ?>
                    <a class="joms-button--shared" title="<?php echo JText::_('COM_COMMUNITY_SHARE_THIS'); ?>"
                            href="javascript:" onclick="joms.api.pageShare('<?php echo CRoute::getExternalURL('index.php?option=com_community&view=profile&userid=' . $profile->id); ?>')">
                        <svg viewBox="0 0 16 16" class="joms-icon">
                            <use xlink:href="<?php echo CRoute::getURI(); ?>#joms-icon-redo"></use>
                        </svg>
                    </a>
                    <?php } ?>

                    <?php if ($enableReporting) { ?>
                    <a class="joms-button--viewed" title="<?php echo JText::_('COM_COMMUNITY_REPORT_USER'); ?>"
                            href="javascript:" onclick="joms.api.userReport('<?php echo $user->id; ?>');">
                        <svg viewBox="0 0 16 16" class="joms-icon">
                            <use xlink:href="<?php echo CRoute::getURI(); ?>#joms-icon-warning"></use>
                        </svg>
                    </a>
                    <?php } ?>

                </div>


            </div>

        </div>

        <div class="joms-focus__actions--reposition">
            <input type="button" class="joms-button--neutral" data-ui-object="button-cancel" value="<?php echo JText::_('COM_COMMUNITY_CANCEL'); ?>"> &nbsp;
            <input type="button" class="joms-button--primary" data-ui-object="button-save" value="<?php echo JText::_('COM_COMMUNITY_SAVE'); ?>">
        </div>

        <?php if ($loggedIn) { ?>
        <div class="joms-focus__button--options--desktop">
            <a href="javascript:" data-ui-object="joms-dropdown-button">
                <svg viewBox="0 0 16 16" class="joms-icon">
                    <use xlink:href="<?php echo CRoute::getURI(); ?>#joms-icon-cog"></use>
                </svg>
            </a>
            <!-- No need to populate menus as it is cloned from mobile version. -->
            <ul class="joms-dropdown"></ul>
        </div>
        <?php } ?>

    </div>

    <div class="joms-focus__actions">
        <?php if ( !$isMine ) { ?>

            <!-- Friending buton -->
            <?php if ( $isFriend ) { ?>
                <a href="javascript:" class="joms-focus__button--add" onclick="joms.api.friendRemove('<?php echo $profile->id;?>')">
                    <?php echo JText::_('COM_COMMUNITY_FRIENDS_REMOVE'); ?>
                </a>
            <?php } else if ( !$isBlocked ) { ?>
                <?php if ( $isWaitingApproval ) { ?>
                    <a href="javascript:" class="joms-focus__button--add" onclick="joms.api.friendAdd('<?php echo $profile->id;?>')">
                        <?php echo JText::_('COM_COMMUNITY_PROFILE_PENDING_FRIEND_REQUEST'); ?>
                    </a>
                <?php } else if ( $isWaitingResponse ) { ?>
                    <a href="javascript:" class="joms-focus__button--add" onclick="joms.api.friendResponse('<?php echo $profile->id;?>')">
                        <?php echo JText::_('COM_COMMUNITY_PROFILE_PENDING_FRIEND_REQUEST'); ?>
                    </a>
                <?php } else { ?>
                    <a href="javascript:" class="joms-focus__button--add" onclick="joms.api.friendAdd('<?php echo $profile->id;?>')">
                        <?php echo JText::_('COM_COMMUNITY_PROFILE_ADD_AS_FRIEND'); ?>
                    </a>
                <?php } ?>
            <?php } ?>

            <!-- Send Message button -->
            <?php if ( $config->get('enablepm') ) { ?>
                <a href="javascript:" class="joms-focus__button--message" onclick="<?php echo $sendMsg; ?>">
                    <?php echo JText::_('COM_COMMUNITY_INBOX_SEND_MESSAGE'); ?>
                </a>
            <?php } ?>


        <?php } ?>

        <?php if ($loggedIn) { ?>
        <a class="joms-focus__button--options" data-ui-object="joms-dropdown-button"><?php echo JText::_('COM_COMMUNITY_OPTIONS'); ?></a>
        <?php } ?>

        <ul class="joms-dropdown">
            <?php if ($isMine || COwnerHelper::isCommunityAdmin()) { ?>

                <li><a href="javascript:" onclick="joms.api.avatarChange('profile', '<?php echo $profile->id; ?>');"><?php echo JText::_('COM_COMMUNITY_CHANGE_AVATAR'); ?></a></li>
                <li class="joms-hidden--small"><a href="javascript:" data-propagate="1" onclick="joms.api.coverReposition('profile', '<?php echo $profile->id; ?>');"><?php echo JText::_('COM_COMMUNITY_REPOSITION_COVER'); ?></a></li>
                <li><a href="javascript:" onclick="joms.api.coverChange('profile', '<?php echo $profile->id; ?>');"><?php echo JText::_('COM_COMMUNITY_CHANGE_COVER'); ?></a></li>

                <?php if (COwnerHelper::isCommunityAdmin() && $showFeatured) { ?>
                    <?php if ($profile->featured) { ?>
                        <li><a href="javascript:" onclick="joms.api.userRemoveFeatured('<?php echo $profile->id; ?>');"><?php echo JText::_('COM_COMMUNITY_REMOVE_FEATURED'); ?></a></li>
                    <?php } else { ?>
                        <li><a href="javascript:" onclick="joms.api.userAddFeatured('<?php echo $profile->id; ?>');"><?php echo JText::_('COM_COMMUNITY_MAKE_FEATURED'); ?></a></li>
                    <?php } ?>
                <?php } ?>

                <?php if ($isSEFEnabled) { ?>
                    <li><a href="javascript:" onclick="joms.api.userChangeVanityURL('<?php echo $profile->id; ?>');"><?php echo JText::_('COM_COMMUNITY_PROFILE_CHANGE_ALIAS'); ?></a></li>
                <?php } ?>

                <?php if(!$profile->defaultAvatar) { ?>
                    <li><a href="javascript:" onclick="joms.api.avatarRemove(null, '<?php echo $profile->id; ?>');"><?php echo JText::_('COM_COMMUNITY_REMOVE_PROFILE_PICTURE'); ?></a></li>
                <?php } ?>

                <?php if(!strstr($profile->cover,'default')) { ;?>
                    <li><a href="javascript:" onclick="joms.api.coverRemove('profile', '<?php echo $profile->id; ?>');"><?php echo JText::_('COM_COMMUNITY_REMOVE_PROFILE_COVER'); ?></a></li>
                <?php } ?>

                <?php if ($isMine) { ?>

                    <?php if( !empty($profile->profilevideo) ) { ?>
                    <li><a href="javascript:" onclick="joms.api.videoRemoveLinkFromProfile('<?php echo $profile->profilevideo; ?>', '<?php echo $profile->id; ?>');"><?php echo JText::_('COM_COMMUNITY_VIDEOS_REMOVE_PROFILE_VIDEO'); ?></a></li>
                    <?php } ?>

                    <li><a href="<?php echo CRoute::_('index.php?option=com_community&view=profile&task=preferences'); ?>"><?php echo JText::_('COM_COMMUNITY_EDIT_PREFERENCES'); ?></a></li>

                <?php }?>

            <?php } ?>

            <?php if (!$isMine) { ?>

                <?php if($isFriend) { ?>
                    <li><a href="javascript:" onclick="joms.api.friendRemove('<?php echo $profile->id; ?>');"><?php echo JText::_('COM_COMMUNITY_FRIENDS_REMOVE'); ?></a></li>
                <?php } ?>
                <?php if ($isBlocked) { ?>
                    <li><a href="javascript:" onclick="joms.api.userUnblock('<?php echo $profile->id; ?>');"><?php echo JText::_('COM_COMMUNITY_UNBLOCK_USER'); ?></a></li>
                <?php } else { ?>
                    <li><a href="javascript:" onclick="joms.api.userBlock('<?php echo $profile->id; ?>');"><?php echo JText::_('COM_COMMUNITY_BLOCK_USER'); ?></a></li>
                <?php } ?>

                <?php if (COwnerHelper::isCommunityAdmin()) { ?>
                    <?php if ($blocked) { ?>
                        <li><a href="javascript:" onclick="joms.api.userUnban('<?php echo $profile->id; ?>');"><?php echo JText::_('COM_COMMUNITY_UNBAN_USER'); ?></a></li>
                    <?php } else { ?>
                        <li><a href="javascript:" onclick="joms.api.userBan('<?php echo $profile->id; ?>');"><?php echo JText::_('COM_COMMUNITY_BAN_USER'); ?></a></li>
                    <?php } ?>
                <?php } ?>

            <?php } ?>
        </ul>

    </div>
    <?php
    $badge = new CBadge($user);
    $badge = $badge->getBadge();

    if($badge->current && $config->get('enable_badges')) : ?>

    <img src="<?php echo $badge->current->image;?>" alt="<?php echo $badge->current->title;?>" class="joms-focus__badges <?php echo ($profile->featured == true) ? 'featured' : ' '; ?>"/>

    <?php endif; ?>

    <div class="joms-focus__info">
        <?php echo JHTML::_('string.truncate', $this->escape(strip_tags($profileFields)), 100); ?>
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

        <?php if ($isLikeEnabled) { ?>
        <li class="full liked">
            <a href="javascript:"
               class="joms-js--like-profile-<?php echo $profile->id; ?><?php echo $isUserLiked > 0 ? ' liked' : ''; ?>"
               onclick="joms.api.page<?php echo $isUserLiked > 0 ? 'Unlike' : 'Like' ?>('profile', '<?php echo $profile->id ?>');"
               data-lang-like="<?php echo JText::_('COM_COMMUNITY_LIKE'); ?>"
               data-lang-liked="<?php echo JText::_('COM_COMMUNITY_LIKED'); ?>">
                <svg viewBox="0 0 16 20" class="joms-icon">
                    <use xlink:href="<?php echo CRoute::getURI(); ?>#joms-icon-thumbs-up"></use>
                </svg>
                <span class="joms-js--lang"><?php echo ($isUserLiked > 0) ? JText::_('COM_COMMUNITY_LIKED') : JText::_('COM_COMMUNITY_LIKE'); ?></span>
                <span class="joms-text--light"> <?php echo $likes; ?></span>
            </a>
        </li>
        <?php }?>

    </ul>

</div>

<script>
    // Clone menu from mobile version to desktop version.
    (function( w ) {
        w.joms_queue || (w.joms_queue = []);
        w.joms_queue.push(function() {
            var src = joms.jQuery('.joms-focus__actions ul.joms-dropdown'),
                clone = joms.jQuery('.joms-focus__button--options--desktop ul.joms-dropdown');

            clone.html( src.html() );
        });
    })( window );
</script>
