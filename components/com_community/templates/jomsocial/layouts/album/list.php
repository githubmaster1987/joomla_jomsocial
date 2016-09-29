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
    $isPhotoModal = $config->get('album_mode') == 1;

    if($type == PHOTOS_GROUP_TYPE){
        $title = JText::_('COM_COMMUNITY_GROUP_PHOTOS');
    }elseif($isOwner){
        $title = JText::_('COM_COMMUNITY_PHOTOS_MY_PHOTOS_TITLE');
    }else{
        $title = JText::_('COM_COMMUNITY_PHOTOS');
    }

?>

<script>
    function joms_change_filter(value) {
        var urls = {
            date: '<?php echo html_entity_decode(CRoute::_($baseLink.'&sort=date')); ?>',
            hit: '<?php echo html_entity_decode(CRoute::_($baseLink.'&sort=hit')); ?>',
            name: '<?php echo html_entity_decode(CRoute::_($baseLink.'&sort=name')); ?>',
            featured_only: '<?php echo html_entity_decode(CRoute::_($baseLink.'&sort=featured_only')); ?>',
            featured: '<?php echo html_entity_decode(CRoute::_($baseLink.'&sort=featured')); ?>',
            like: '<?php echo html_entity_decode(CRoute::_($baseLink.'&sort=like')); ?>',
            comment: '<?php echo html_entity_decode(CRoute::_($baseLink.'&sort=comment')); ?>'
        };

        window.location = urls[value] || '?';
    }
</script>


<div class="joms-page">
    <h3 class="joms-page__title"><?php echo $title; ?></h3>

    <div>
        <?php echo $submenu; ?>
    </div>

    <?php if ($groupId > 0 && $my->authorise('community.create', 'groups.photos.' . $groupId)) { ?>
        <button class="joms-button--add-on-page joms-button--primary joms-button--small"
                onclick="joms.api.photoUpload('', '<?php echo $groupId; ?>', 'group');"><?php echo JText::_('COM_COMMUNITY_GROUP_PHOTOS_UPLOAD_PHOTOS') ?></button>
    <?php } else if ($eventId > 0 && $my->authorise('community.create', 'events.photos.' . $eventId)) { ?>
        <button class="joms-button--add-on-page joms-button--primary joms-button--small"
                onclick="joms.api.photoUpload('', '<?php echo $eventId; ?>', 'event');"><?php echo JText::_('COM_COMMUNITY_EVENT_PHOTOS_UPLOAD_PHOTOS') ?></button>
    <?php } else if (JFactory::getApplication()->input->get('task') == 'myphotos' && $isOwner) { ?>
        <button class="joms-button--add-on-page joms-button--primary joms-button--small"
                onclick="joms.api.photoUpload();"><?php echo JText::_('COM_COMMUNITY_PHOTOS_UPLOAD_PHOTOS'); ?></button>
    <?php } ?>

    <div class="joms-tab__content">
        <?php if ($type != PHOTOS_GROUP_TYPE && $type != PHOTOS_EVENT_TYPE) { ?>
            <select class="joms-select" onchange="joms_change_filter(this.value);">
                <option value="date" <?php if($sortBy=='date') echo "selected='selected'";?>><?php echo JText::_('COM_COMMUNITY_ADVSEARCH_DATE'); ?></option>
                <option value="hit" <?php if($sortBy=='hit') echo "selected='selected'";?>><?php echo JText::_('COM_COMMUNITY_HITS'); ?></option>
                <option value="name" <?php if($sortBy=='name') echo "selected='selected'";?>><?php echo JText::_('COM_COMMUNITY_NAME'); ?></option>
                <?php if($config->get('show_featured')){ ?>
                <option value="featured_only" <?php if($sortBy=='featured_only') echo "selected='selected'";?>><?php echo JText::_('COM_COMMUNITY_FEATURED'); ?></option>
                <?php } ?>
            </select>
        <?php } ?>

        <div class="joms-gap"></div>

        <?php if ($albums) { ?>
            <ul class="joms-gallery">
                <?php
                    $i = 0;
                    foreach ($albums as $album) {
                        //check if current album is featured
                        $isFeatured = in_array($album->id, $featuredList);
                        $featurePermission = ($isCommunityAdmin && ($type == PHOTOS_USER_TYPE || $type == PHOTOS_GROUP_TYPE) && ($album->permissions == 0 || $album->permissions == 10) && $album->type != 'profile.avatar');
                        /* Filter normal user to view cover album */
                        if (($album->type == 'profile.Cover' || $album->type == 'profile.avatar') && !$album->isOwner && !$isSuperAdmin && !$isCommunityAdmin) {
                            continue;
                        }
                        CHeadHelper::addOpengraph('og:image', $album->getCoverThumbURI(), true);

                        $isAvatarAlbum = (strpos($album->type ,'.avatar') !== false) ? true : false;

                        ?>
                        <li class="joms-gallery__item album-permission-<?php echo $album->permissions ?>">
                            <div class="joms-gallery__thumbnail" >

                                <?php if (in_array($album->id, $featuredList)) { ?>
                                    <div class="joms-ribbon__wrapper">
                                        <span
                                            class="joms-ribbon"><?php echo JText::_('COM_COMMUNITY_FEATURED'); ?></span>
                                    </div>
                                <?php } ?>

                                <a class="joms-relative joms-block"
                                    <?php if ($isPhotoModal) { ?>
                                        onclick="joms.api.photoOpen('<?php echo $album->id; ?>', ''); return false;" style="cursor:pointer;"
                                    <?php } else { ?>
                                        href="<?php echo $album->getURI(); ?>"
                                    <?php } ?>>

                                    <img src="<?php echo $album->getCoverThumbURI(); ?>"
                                         alt="<?php echo $this->escape($album->name); ?>">
                                </a>

                            <?php if (($album->isOwner || $isSuperAdmin) && !CAlbumsHelper::isFixedAlbum($album)) { ?>
                                <div class="joms-gallery__options">
                                    <a class="joms-button--options" data-ui-object="joms-dropdown-button"
                                       href="javascript:">
                                        <svg class="joms-icon" viewBox="0 0 16 16">
                                            <use xlink:href="<?php echo CRoute::getURI(); ?>#joms-icon-arrow-down"></use>
                                        </svg>
                                    </a>
                                    <ul class="joms-dropdown">
                                            <li>
                                                <a href="<?php echo $album->editLink; ?>"><?php echo JText::_('COM_COMMUNITY_PHOTOS_EDIT'); ?></a>
                                            </li>
                                        <?php if ($album->isOwner && !CAlbumsHelper::isFixedAlbum($album)) { ?>
                                            <?php
                                                $albumContext = $album->type == 'group' || $album->type == 'event' ? $album->type : '';
                                                $albumContextId = $albumContext == 'group' ? $album->groupid : ($albumContext == 'event' ? $album->eventid : '');
                                            ?>
                                            <li><a href="javascript:"
                                                   onclick="joms.api.photoUpload('<?php echo $album->id; ?>', '<?php echo $albumContextId ?>', '<?php echo $albumContext ?>');"><?php echo JText::_('COM_COMMUNITY_PHOTOS_UPLOAD'); ?></a>
                                            </li>
                                        <?php } ?>

                                        <?php if(!CAlbumsHelper::isFixedAlbum($album)){ ?>
                                        <li><a href="javascript:"
                                               onclick="joms.api.albumRemove('<?php echo $album->id; ?>');"><?php echo JText::_('COM_COMMUNITY_PHOTOS_ALBUM_DELETE'); ?></a>
                                        </li>
                                        <?php } ?>

                                        <?php if ($showFeatured && $featurePermission && !$isFeatured && !$isAvatarAlbum) { ?>
                                            <li>
                                                <a href="javascript:"
                                                   onclick="joms.api.albumAddFeatured('<?php echo $album->id; ?>', 'photos');"><?php echo JText::_('COM_COMMUNITY_MAKE_FEATURED'); ?></a>
                                            </li>
                                        <?php
                                        } else {
                                            if ($showFeatured && $featurePermission && $isFeatured && !$isAvatarAlbum) {
                                                ?>
                                                <li>
                                                    <a href="javascript:"
                                                       onclick="joms.api.albumRemoveFeatured('<?php echo $album->id; ?>', 'photos');"><?php echo JText::_('COM_COMMUNITY_REMOVE_FEATURED'); ?></a>
                                                </li>
                                            <?php
                                            }
                                        } ?>
                                    </ul>
                                </div>
                            <?php } ?>

                            </div>
                            <div class="joms-gallery__body">
                                <a class="joms-gallery__title"
                                    <?php if ($isPhotoModal) { ?>
                                        onclick="joms.api.photoOpen('<?php echo $album->id; ?>', ''); return false;" style="cursor:pointer;"
                                    <?php } else { ?>
                                        href="<?php echo $album->getURI(); ?>"
                                    <?php } ?>>

                                    <?php
                                        // Show privacy icon
                                        $privacyIcon = 'earth'; // NO need to display this
                                        $privacyIcon = ($album->permissions == PRIVACY_FRIENDS) ? 'users' : $privacyIcon;
                                        $privacyIcon = ($album->permissions == PRIVACY_FRIENDS) ? 'user' : $privacyIcon;
                                        $privacyIcon = ($album->permissions == PRIVACY_PRIVATE) ? 'lock' : $privacyIcon;
                                        if ($privacyIcon != 'earth') {
                                            ?>
                                            <div class="joms-gallery__privacy">
                                                <svg viewBox="0 0 16 18" class="joms-icon">
                                                    <use
                                                        xlink:href="<?php echo CRoute::getURI(); ?>#joms-icon-<?php echo $privacyIcon; ?>"></use>
                                                </svg>
                                            </div>
                                        <?php } ?>

                                    <?php echo $this->escape($album->name); ?>
                                </a>



                                <div class="joms-gallery__status">

                                    <?php echo JText::sprintf(CStringHelper::isPlural($album->count) ? 'COM_COMMUNITY_PHOTO_COUNT_MANY' : 'COM_COMMUNITY_PHOTO_COUNT_SINGLE',
                                            $album->count) . ' ‧ ' . JText::sprintf(CStringHelper::isPlural($album->hits) ? 'COM_COMMUNITY_PHOTO_HIT_MANY' : 'COM_COMMUNITY_PHOTO_HIT_SINGLE',
                                            $album->hits).' ‧ '.JText::sprintf(CStringHelper::isPlural($album->totalComments) ? 'COM_COMMUNITY_PHOTO_COMMENT_MANY' : 'COM_COMMUNITY_PHOTO_COMMENT_SINGLE',
                                            $album->totalComments);
                                    ?>

                                </div>

                                <div class="joms-gap--small"></div>

                                <div class="joms-gallery__meta">
                                    <?php if ($currentTask != 'myphotos') { ?>
                                        <?php echo JTEXT::_('COM_COMMUNITY_PHOTOS_BY') ?>
                                        <a href="<?php echo CRoute::_('index.php?option=com_community&view=profile&userid=' . $album->creator); ?>"><?php echo $album->user->getDisplayName(); ?></a>
                                    <?php } ?>
                                    <span><?php echo $album->lastupdated ? $album->lastupdated : '&nbsp;'; ?></span>

                                    <?php if($album->groupid){
                                        $group = JTable::getInstance('Group', 'CTable');
                                        $group->load($album->groupid);
                                        echo JText::sprintf('COM_COMMUNITY_ALBUM_FROM_GROUP','<a href="' . CUrlHelper::groupLink($group->id) . '">' . $group->name. '</a>') ;
                                    }elseif($album->eventid) {
                                        $event = JTable::getInstance('Event', 'CTable');
                                        $event->load($album->eventid);
                                        echo JText::sprintf('COM_COMMUNITY_ALBUM_FROM_EVENT','<a href="' . CUrlHelper::eventLink($event->id) . '">' . $event->title. '</a>') ;
                                    }
                                    ?>



                                </div>


                            </div>

                        </li>

                    <?php } // end: foreach($albums as $album) ?>
            </ul>

        <?php } else { ?>
            <div class="cEmpty cAlert">
                <?php echo JText::_('COM_COMMUNITY_PHOTOS_NO_ALBUM_CREATED'); ?>
            </div>
        <?php } // end: if( $albums ) ?>

        <?php if (isset($pagination) && $pagination->getPagesLinks() && ($pagination->pagesTotal > 1 || $pagination->total > 1) ) { ?>
            <div class="joms-pagination">
                <?php echo $pagination->getPagesLinks(); ?>
            </div>
        <?php } ?>

    </div>

</div>


