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

$profileFields = '';
$featured = new CFeatured(FEATURED_USERS);
$featuredList = $featured->getItemIds();

$enableReporting = false;
if ( !$isMine && $config->get('enablereporting') == 1 && ( $my->id > 0 || $config->get('enableguestreporting') == 1 ) ) {
    $enableReporting = true;
}

?>

<div class="joms-body">
    <div class="joms-focus">
        <div class="joms-focus__cover joms-focus--mini">
            <?php  if (in_array($profile->id, $featuredList)) { ?>
            <div class="joms-ribbon__wrapper">
                <span class="joms-ribbon"><?php echo JText::_('COM_COMMUNITY_FEATURED'); ?></span>
            </div>
            <?php } ?>

            <div class="joms-focus__cover-image--mobile" style="background:url(<?php echo $user->getCover(); ?>) no-repeat center center;">
            </div>

            <div class="joms-focus__header">
                <div class="joms-avatar--focus <?php echo CUserHelper::onlineIndicator($user); ?>">
                    <a <?php if ( $profile->avatarAlbum ) { ?>
                        href="<?php echo CRoute::_('index.php?option=com_community&view=photos&task=photo&albumid=' . $profile->avatarAlbum); ?>"
                        onclick="joms.api.photoOpen(<?php echo $profile->avatarAlbum ?>); return false;"
                    <?php } ?>>
                        <img src="<?php echo $profile->largeAvatar . '?_=' . time(); ?>" alt="<?php echo $this->escape( $user->getDisplayName() ); ?>">
                    </a>
                </div>

                <div class="joms-focus__title">
                    <h2>
                        <a href="<?php echo CRoute::_('index.php?option=com_community&view=profile&userid='.$user->id); ?>">
                            <?php echo $user->getDisplayName(); ?>
                        </a>
                    </h2>

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
                        <?php echo JHTML::_('string.truncate', $this->escape(strip_tags($profileFields)), 100); ?>
                    </div>

                </div>

                <div class="joms-focus__actions__wrapper">

                    <div class="joms-focus__actions--desktop">
                        <!--  add as friend buton -->
                        <?php if( !$isMine ): ?>
                        <?php if($isFriend): ?>
                                <a href="javascript:" class="joms-focus__button--add" onclick="joms.api.friendRemove('<?php echo $profile->id;?>')">
                                    <?php echo JText::_('COM_COMMUNITY_FRIENDS_REMOVE'); ?>
                                </a>
                            <?php endif;?>
                            <?php if(!$isFriend && !$isMine && !$isBlocked): ?>
                                <?php if(!$isWaitingApproval):?>
                                    <a href="javascript:" class="joms-focus__button--add" onclick="joms.api.friendAdd('<?php echo $profile->id;?>')">
                                        <?php echo JText::_('COM_COMMUNITY_PROFILE_ADD_AS_FRIEND'); ?>
                                    </a>
                                <?php else : ?>
                                    <a href="javascript:" class="joms-focus__button--add" onclick="joms.api.friendAdd('<?php echo $profile->id;?>')">
                                        <?php echo JText::_('COM_COMMUNITY_PROFILE_PENDING_FRIEND_REQUEST'); ?>
                                    </a>
                                <?php endif ?>
                            <?php endif; ?>
                        <?php endif ?>
                        <!-- Send message button -->
                        <?php if( !$isMine && $config->get('enablepm')){ ?>
                                <a href="javascript:void(0);" class="joms-focus__button--message" onclick="<?php echo $sendMsg; ?>">
                                    <?php echo JText::_('COM_COMMUNITY_INBOX_SEND_MESSAGE')?></a>
                        <?php }?>
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

            <div class="span4">
                <a class="cToolBox-Avatar cFloat-L" href="<?php echo CRoute::_('index.php?option=com_community&view=profile&userid='.$user->id); ?>">
                    <img src="<?php echo $user->getThumbAvatar(); ?>" alt="<?php echo $user->getDisplayName(); ?>" class="cAvatar" />
                </a>
                <b class="cToolBox-Name"><?php echo $user->getDisplayName(); ?></b>
                <div class="small">
                    <a href="<?php echo CRoute::_('index.php?option=com_community&view=profile&userid='.$user->id); ?>">
                        <?php echo JText::_('COM_COMMUNITY_GO_TO_PROFILE'); ?>
                    </a>
                </div>
            </div>
            <div class="span8">
                <ul class="cToolBox-Options unstyled">
                    <?php if(!$isFriend && !$isMine && !CFriendsHelper::isWaitingApproval($my->id, $user->id)) { ?>
                    <li>
                        <a href="javascript:void(0)" onclick="joms.api.friendAdd('<?php echo $user->id;?>')">
                            <i class="com-icon-user-plus"></i>
                            <span><?php echo JText::_('COM_COMMUNITY_PROFILE_ADD_AS_FRIEND'); ?></span>
                        </a>
                    </li>
                    <?php } ?>

                    <?php if($config->get('enablephotos')): ?>
                    <li>
                        <a href="<?php echo CRoute::_('index.php?option=com_community&view=photos&task=myphotos&userid='.$user->id); ?>">
                            <i class="com-icon-photos"></i>
                            <span><?php echo JText::_('COM_COMMUNITY_PHOTOS'); ?></span>
                        </a>
                    </li>
                    <?php endif; ?>

                    <?php if($config->get('enablevideos')): ?>
                    <li>
                        <a href="<?php echo CRoute::_('index.php?option=com_community&view=videos&task=myvideos&userid='.$user->id); ?>">
                            <i class="com-icon-videos"></i>
                            <span><?php echo JText::_('COM_COMMUNITY_VIDEOS_GALLERY'); ?></span>
                        </a>
                    </li>
                    <?php endif; ?>

                    <?php if( !$isMine && $config->get('enablepm') ): ?>
                    <li>
                        <a onclick="<?php echo $sendMsg; ?>" href="javascript:void(0);">
                            <i class="com-icon-mail-go"></i>
                            <span><?php echo JText::_('COM_COMMUNITY_INBOX_SEND_MESSAGE'); ?></span>
                        </a>
                    </li>
                    <?php endif; ?>
                </ul>
            </div>

        </div>

        <ul class="joms-focus__link">
            <li class="half"><a href="<?php echo CRoute::_('index.php?option=com_community&view=friends&userid='.$profile->id); ?>">
                <?php echo ($profile->_friends == 1) ? JText::_('COM_COMMUNITY_FRIENDS_COUNT') . ' <span class="joms-text--light">' . $profile->_friends . '</span>' : JText::_('COM_COMMUNITY_FRIENDS_COUNT_MANY') . ' <span class="joms-text--light">' . $profile->_friends . '</span>' ?> </a></li>

            <?php if($photoEnabled) {?>
            <li class="half hidden-mobile"><a href="<?php echo CRoute::_('index.php?option=com_community&view=photos&task=myphotos&userid='.$profile->id); ?>"><?php echo ($profile->_photos == 1) ? JText::_('COM_COMMUNITY_PHOTOS_COUNT_SINGULAR') . ' <span class="joms-text--light">' . $profile->_photos . '</span>' :  JText::_('COM_COMMUNITY_PHOTOS_COUNT') . ' <span class="joms-text--light">' . $profile->_photos . '</span>' ?></a></li>
            <?php }?>

            <?php if($videoEnabled) {?>
            <li class="half hidden-mobile"><a href="<?php echo CRoute::_('index.php?option=com_community&view=videos&task=myvideos&userid='.$profile->id); ?>"><?php echo ($profile->_videos == 1) ?  JText::_('COM_COMMUNITY_VIDEOS_COUNT') . ' <span class="joms-text--light">' . $profile->_videos . '</span>' : JText::_('COM_COMMUNITY_VIDEOS_COUNT_MANY') . ' <span class="joms-text--light">' . $profile->_videos . '</span>' ?></a></li>
            <?php }?>

            <?php if($groupEnabled) {?>
            <li class="half hidden-mobile"><a href="<?php echo CRoute::_('index.php?option=com_community&view=groups&task=mygroups&userid='.$profile->id); ?>"><?php echo ($profile->_groups == 1) ?  JText::_('COM_COMMUNITY_GROUPS_COUNT') . ' <span class="joms-text--light">' . $profile->_groups . '</span>' : JText::_('COM_COMMUNITY_GROUPS_COUNT_MANY') . ' <span class="joms-text--light">' . $profile->_groups . '</span>' ?></a></li>
            <?php }?>

            <?php if($eventEnabled) {?>
            <li class="half hidden-mobile"><a href="<?php echo CRoute::_('index.php?option=com_community&view=events&task=myevents&userid='.$profile->id); ?>"><?php echo ($profile->_events == 1) ? JText::_('COM_COMMUNITY_EVENTS_COUNT') . ' <span class="joms-text--light">' . $profile->_events . '</span>' : JText::_('COM_COMMUNITY_EVENTS_COUNT_MANY') . ' <span class="joms-text--light">' . $profile->_events . '</span>' ?></a></li>
            <?php }?>

            <?php if ($isLikeEnabled) { ?>
            <li class="half liked">
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

            <li class="full hidden-desktop">
                <a href="javascript:" data-ui-object="joms-dropdown-button">
                    <?php echo JTEXT::_('COM_COMMUNITY_MORE'); ?>
                    <svg viewBox="0 0 14 20" class="joms-icon">
                        <use xlink:href="<?php echo CRoute::getURI(); ?>#joms-icon-arrow-down"></use>
                    </svg>
                </a>
                <ul class="joms-dropdown more-button">
                    <li><a href="<?php echo CRoute::_('index.php?option=com_community&view=friends&userid='.$profile->id); ?>">
                        <?php echo ($profile->_friends == 1) ? JText::_('COM_COMMUNITY_FRIENDS_COUNT') . ' <span class="joms-text--light">' . $profile->_friends . '</span>' : JText::_('COM_COMMUNITY_FRIENDS_COUNT_MANY') . ' <span class="joms-text--light">' . $profile->_friends . '</span>' ?> </a></li>

                    <?php if($photoEnabled) {?>
                    <li><a href="<?php echo CRoute::_('index.php?option=com_community&view=photos&task=myphotos&userid='.$profile->id); ?>"><?php echo ($profile->_photos == 1) ? JText::_('COM_COMMUNITY_PHOTOS_COUNT_SINGULAR') . ' <span class="joms-text--light">' . $profile->_photos . '</span>' :  JText::_('COM_COMMUNITY_PHOTOS_COUNT') . ' <span class="joms-text--light">' . $profile->_photos . '</span>' ?></a></li>
                    <?php }?>

                    <?php if($videoEnabled) {?>
                    <li><a href="<?php echo CRoute::_('index.php?option=com_community&view=videos&task=myvideos&userid='.$profile->id); ?>"><?php echo ($profile->_videos == 1) ?  JText::_('COM_COMMUNITY_VIDEOS_COUNT') . ' <span class="joms-text--light">' . $profile->_videos . '</span>' : JText::_('COM_COMMUNITY_VIDEOS_COUNT_MANY') . ' <span class="joms-text--light">' . $profile->_videos . '</span>' ?></a></li>
                    <?php }?>

                    <?php if($groupEnabled) {?>
                    <li><a href="<?php echo CRoute::_('index.php?option=com_community&view=groups&task=mygroups&userid='.$profile->id); ?>"><?php echo ($profile->_groups == 1) ?  JText::_('COM_COMMUNITY_GROUPS_COUNT') . ' <span class="joms-text--light">' . $profile->_groups . '</span>' : JText::_('COM_COMMUNITY_GROUPS_COUNT_MANY') . ' <span class="joms-text--light">' . $profile->_groups . '</span>' ?></a></li>
                    <?php }?>

                    <?php if($eventEnabled) {?>
                    <li><a href="<?php echo CRoute::_('index.php?option=com_community&view=events&task=myevents&userid='.$profile->id); ?>"><?php echo ($profile->_events == 1) ? JText::_('COM_COMMUNITY_EVENTS_COUNT') . ' <span class="joms-text--light">' . $profile->_events . '</span>' : JText::_('COM_COMMUNITY_EVENTS_COUNT_MANY') . ' <span class="joms-text--light">' . $profile->_events . '</span>' ?></a></li>
                    <?php }?>
                </ul>
            </li>

        </ul>
    </div>
</div>
