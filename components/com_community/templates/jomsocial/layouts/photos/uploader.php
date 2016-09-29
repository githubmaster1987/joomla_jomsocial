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

$isAlbumExist = count($allAlbums) >= 1;

$totalFixedAlbum = 0;
foreach ($allAlbums as $index => $album) {
    if (CAlbumsHelper::isFixedAlbum($album)) {
        $totalFixedAlbum++;
    }
}

if (count($allAlbums) - $totalFixedAlbum <= 0) {
    $isAlbumExist = false;
}

?>

<?php if ($disableUpload) { ?>

<!-- disabled -->
<div class="joms-popup__content joms-popup__content--single">
    <?php echo $preMessage; ?>
</div>

<?php } else { ?>

<div style="min-height:250px">
    <div class="joms-popup__content">
        <div>

            <!-- tab bar -->
            <div class="joms-tab__bar">
                <a href="#joms-js__new-album" <?php echo $isAlbumExist && $selectedAlbum ? '' : 'class="active"' ?>><?php echo JText::_('COM_COMMUNITY_PHOTOS_CREATE_NEW_ALBUM_TITLE'); ?></a>
                <?php if ($isAlbumExist) { ?>
                <a href="#joms-js__select-album" <?php echo $selectedAlbum ? 'class="active"' : '' ?>><?php echo JText::_('COM_COMMUNITY_PHOTOS_ADD_TO_EXISTING_ALBUM'); ?></a>
                <?php } ?>
            </div>

            <!-- new album tab -->
            <div id="joms-js__new-album" class="joms-tab__content <?php echo $isAlbumExist && $selectedAlbum ? 'joms-popup__hide' : '' ?>">
                <div class="joms-form__group">
                    <span><?php echo JText::_('COM_COMMUNITY_PHOTOS_ALBUM_NAME'); ?> <span class="joms-required">*</span></span>
                    <input type="text" class="joms-input" name="name" value="">
                    <p class="joms-help" style="color:red;display:none"><?php echo JText::_('COM_COMMUNITY_ALBUM_NAME_REQUIRED'); ?></p>
                </div>
                <div class="joms-js--form-detail" style="overflow:hidden">
                    <?php if ($enableLocation) { ?>
                    <div class="joms-form__group">
                        <span><?php echo JText::_('COM_COMMUNITY_PHOTOS_ALBUM_LOCATION'); ?></span>
                        <input type="text" class="joms-input" name="location" value="" placeholder="<?php echo JText::_('COM_COMMUNITY_PHOTOS_ALBUM_LOCATION_DESC'); ?>">
                    </div>
                    <?php } ?>
                    <div class="joms-form__group">
                        <span><?php echo JText::_('COM_COMMUNITY_PHOTOS_ALBUM_DESC'); ?></span>
                        <textarea name="description" class="joms-textarea"></textarea>
                    </div>
                    <div class="joms-form__group">
                        <span><?php echo JText::_('COM_COMMUNITY_PHOTOS_PRIVACY_VISIBILITY'); ?></span>
                        <?php if ($context == 'group') { ?>
                        <p class="joms-help"><?php echo JText::_('COM_COMMUNITY_PHOTOS_GROUP_MEDIA_PRIVACY_TIPS'); ?></p>
                        <?php } else if ($context == 'event') { ?>
                        <p class="joms-help"><?php echo JText::_('COM_COMMUNITY_PHOTOS_EVENT_MEDIA_PRIVACY_TIPS'); ?></p>
                        <?php } else { ?>
                        <?php echo CPrivacy::getHTML('permissions', 0, COMMUNITY_PRIVACY_BUTTON_LARGE, array(), 'select'); ?>
                        <?php } ?>
                    </div>
                    <div></div>
                </div>
                <!--
                <div style="font-size:larger;text-align:center;letter-spacing:1px">
                    <strong class="joms-js--form-toggle" style="cursor:pointer">&bull;&bull;&bull;</strong>
                </div>
                -->
            </div>

            <!-- existing albums tab -->
            <div id="joms-js__select-album" class="joms-tab__content <?php echo $isAlbumExist && $selectedAlbum ? '' : 'joms-popup__hide' ?>">
                <div class="joms-form__group">
                    <span><?php echo JText::_('COM_COMMUNITY_PHOTOS_ALBUM_NAME'); ?></span>
                    <div class="joms-select--wrapper">
                        <select class="joms-select" name="album-id">
                            <?php foreach ($allAlbums as $index => $album) {
                                if (CAlbumsHelper::isFixedAlbum($album))
                                    continue;
                            ?>
                            <option value="<?php echo $album->id; ?>" <?php echo $album->id == $selectedAlbum ? 'selected="selected"' : '' ?>><?php echo trim($album->name); ?></option>
                            <?php } ?>
                        </select>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- photo thumbnails -->
    <div class="joms-popup__content joms-js--thumbnails">
        <div class="joms-gallery" style="max-height:390px;overflow:auto"></div>
    </div>
</div>

<!-- buttons -->
<div class="joms-popup__action">
    <button class="joms-button--primary joms-js--btn-create <?php echo $isAlbumExist && $selectedAlbum ? 'joms-popup__hide' : '' ?>">
        <?php echo JText::_('COM_COMMUNITY_PHOTOS_CREATE_ALBUM_BUTTON'); ?>
        <img src="<?php echo JURI::root(true) ?>/components/com_community/assets/ajax-loader.gif" alt="loader" style="display:none">
    </button>
    <button class="joms-button--primary joms-js--btn-add <?php echo $isAlbumExist && $selectedAlbum ? '' : 'joms-popup__hide' ?>" data-lang-more="<?php echo JText::_('COM_COMMUNITY_PHOTOS_ADD_MORE_FILES') ?>"><?php echo JText::_('COM_COMMUNITY_PHOTOS_MULTIUPLOAD_ADD_FILES'); ?></button>
    <button class="joms-button--primary joms-js--btn-view joms-popup__hide"><?php echo JText::_("COM_COMMUNITY_UPLOAD_VIEW_ALBUM"); ?></button>
</div>

<?php } ?>
