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

$isDefaultAvatar = $user->isDefaultAvatar() ? true : false;

if ( $isDefaultAvatar ) {
    $img = isset($largeAvatar) ? $largeAvatar : $user->getAvatar();
} else {
    $cTable = JTable::getInstance('Profile', 'CTable');
    $cTable->load($user->id);
    $img = $cTable->getLargeAvatar();
}

$img = str_replace('profile-', '', $img);

?>

<div class="joms-page">
    <h3 class="joms-page__title">
        <?php echo JText::_('COM_COMMUNITY_CHANGE_PROFILE_PICTURE'); ?>
    </h3>

    <?php //echo $submenu; ?>

	<h4 class="joms-text--title">
        <?php echo JText::_('COM_COMMUNITY_PICTURE_LARGE_HEADING'); ?>
    </h4>

	<!-- avatar -->
	<div class="joms-form__group">
		<div>
            <div class="joms-choose--avatar">
                <div>
                    <img class="joms-js--avatar joms-js--avatar-<?php echo $user->id ?>" src="<?php echo $img; ?>"
                        alt="<?php echo JText::_('COM_COMMUNITY_LARGE_PICTURE_DESCRIPTION'); ?>"
                        title="<?php echo JText::_('COM_COMMUNITY_LARGE_PICTURE_DESCRIPTION'); ?>">
                </div>
            </div>
		</div>
	</div>

	<!-- remove avatar -->
	<div class="joms-form__group joms-js--no-default" <?php echo $isDefaultAvatar ? 'style="display:none"' : '' ?>>
		<a href="javascript:" onclick="joms.api.avatarRemove('profile', '<?php echo $user->id; ?>');" class="joms-button--neutral joms-button--full-small"><?php echo JText::_('COM_COMMUNITY_REMOVE_PROFILE_PICTURE'); ?></a>
	</div>

	<div class="joms-gap"></div>

    <div class="joms-form__group">
        <div>
            <div class="clearfix">
                <div style="float:left;width:40%;margin-right:4px;">
                    <input type="text" class="joms-input joms-input--select joms-js--btn-select" readonly="readonly" placeholder="<?php echo JText::_('COM_COMMUNITY_SELECT_FILE'); ?>.."
                        style="cursor:pointer;line-height:14px;margin-bottom:2px;">
                    <div class="joms-progressbar"><div class="joms-progressbar__progress"></div></div>
                </div>
                <div style="float:left">
                    <input type="button" class="joms-button--primary joms-js--btn-upload" value="<?php echo JText::_('COM_COMMUNITY_BUTTON_UPLOAD_PICTURE'); ?>"
                        style="line-height:14px;">
                </div>
            </div>
            <?php if ($uploadLimit != 0) { ?>
            <p class="joms-help"><?php echo JText::sprintf('COM_COMMUNITY_MAX_FILE_SIZE_FOR_UPLOAD', $uploadLimit); ?></p>
            <?php } ?>
        </div>
    </div>

    <hr class="joms-divider">

    <h4 class="joms-text--title"><?php echo JText::_('COM_COMMUNITY_PICTURE_THUMB_HEADING');?></h4>

	<div class="joms-form__group">
        <div>
            <div class="joms-avatar--focus">
                <img class="joms-js--avatar joms-js--avatar-<?php echo $user->id ?>" src="<?php echo $img; ?>" alt="avatar" >
            </div>
            <div class="joms-avatar--stream <?php echo CUserHelper::onlineIndicator($user); ?>" style="padding-left: 10px;">
                <img class="joms-js--avatar joms-js--avatar-<?php echo $user->id ?>" src="<?php echo $img; ?>" alt="avatar" data-author="<?php echo $user->id; ?>" >
            </div>
        </div>
	</div>

    <?php if (!$firstLogin) { ?>
	<div class="joms-form__group joms-js--no-default" <?php echo $isDefaultAvatar ? 'style="display:none"' : '' ?>>
		<a href="javascript:" class="joms-button--neutral joms-button--full-small" onclick="joms.api.avatarChange('profile', '<?php echo $user->id ?>');"><?php echo JText::_('COM_COMMUNITY_UPDATE_THUMBNAIL'); ?></a> &nbsp;
        <a href="javascript:" class="joms-button--neutral joms-button--full-small" onclick="joms.api.avatarRotate('profile', '<?php echo $user->id ?>', 'left');"><?php echo JText::_('COM_COMMUNITY_PHOTOS_ROTATE_LEFT'); ?></a>
        <a href="javascript:" class="joms-button--neutral joms-button--full-small" onclick="joms.api.avatarRotate('profile', '<?php echo $user->id ?>', 'right');"><?php echo JText::_('COM_COMMUNITY_PHOTOS_ROTATE_RIGHT'); ?></a>
	</div>
    <?php } ?>
</div>

<script>
(function( w ) {
    var url, uploader, uploadButton;

    // Upload url.
    url = '<?php echo CRoute::_("index.php?option=com_community&view=photos&task=changeAvatar&type=profile&id=" . $user->id); ?>';

    function uploadInit( callback ) {
        if ( typeof callback !== 'function' ) {
            callback = function() {};
        }

        if ( uploader ) {
            callback();
            return;
        }

        joms.util.loadLib( 'plupload', function () {
            container    = joms.jQuery('<div id="joms-js--change-profile-uploader" aria-hidden="true" style="width:1px; height:1px; position:absolute; overflow:hidden;">').appendTo( document.body );
            uploadButton = joms.jQuery('<div id="joms-js--change-profile-uploader-button">').appendTo( container );
            uploader     = new window.plupload.Uploader({
                url: url,
                filters: [{ title: 'Image files', extensions: 'jpg,jpeg,png,gif' }],
                container: 'joms-js--change-profile-uploader',
                browse_button: 'joms-js--change-profile-uploader-button',
                runtimes: 'html5,html4',
                multi_selection: false,
                file_data_name: 'filedata'
            });

            uploader.bind( 'FilesAdded', uploadAdded );
            uploader.bind( 'Error', function() {});
            uploader.bind( 'UploadProgress', uploadProgress );
            uploader.bind( 'FileUploaded', uploadUploaded );
            uploader.init();

            uploadButton = container.find('input[type=file]');
            callback();
        });
    }

    function uploadAdded( up, files ) {
        joms.jQuery('.joms-js--btn-select').val( files[0].name );
        joms.jQuery('.joms-progressbar__progress').css({ width: 0 });
    }

    function uploadProgress( up, file ) {
        var percent, bar;

        percent = Math.min( 100, Math.floor( file.loaded / file.size * 100 ) );
        bar = joms.jQuery('.joms-progressbar__progress');
        bar.stop().animate({ width: percent + '%' });
    }

    function uploadUploaded( up, files, data ) {
        var json = {},
            img, avatar, btn, thumbnails;

        // Parse json response.
        try {
            json = JSON.parse( data.response );
        } catch ( e ) {}

        if ( json.error ) {
            window.alert( json.msg );
            return;
        }

        if ( json.msg ) {
            img = json.info || '';
            img = img.replace( /#.*$/, '' );
            img = img.replace( 'thumb_', '' );
            img = img + '?_=' + (new Date).getTime();

            avatar = joms.jQuery('.joms-js--avatar');
            avatar.attr( 'src', img );

            joms.jQuery('.joms-js--no-default').show();
        }
    }

    w.joms_queue || (w.joms_queue = []);
    w.joms_queue.push(function() {
        uploadInit();

        joms.jQuery('.joms-js--btn-select').on( 'click', function() {
            uploadInit(function() {
                uploadButton.click();
            });
        });

        joms.jQuery('.joms-js--btn-upload').on( 'click', function() {
            window.setTimeout(function() {
                uploader.refresh();
                uploader.start();
            }, 0);
        });
    });

})( window );
</script>
