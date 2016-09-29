<?php
/**
* @copyright (C) 2013 iJoomla, Inc. - All rights reserved.
* @license GNU General Public License, version 2 (http://www.gnu.org/licenses/gpl-2.0.html)
* @author iJoomla.com <webmaster@ijoomla.com>
* @url https://www.jomsocial.com/license-agreement
* The PHP code portions are distributed under the GPL license. If not otherwise stated, all images, manuals, cascading style sheets, and included JavaScript *are NOT GPL, and are released under the IJOOMLA Proprietary Use License v1.0
* More info at https://www.jomsocial.com/license-agreement
*/
// Disallow direct access to this file
defined('_JEXEC') or die('Restricted access');
?>

<style type="text/css">
    #js-cpanel .ace-file-input {
        margin-bottom: 0;
    }
    .ace-file-input .icon-picture, .ace-file-input .icon-upload-alt {
        height: 24px;
    }
    #js-cpanel .ace-file-input label.selected .icon-picture {
        line-height: 25px !important;
    }
</style>
<div class="widget-box">
	<div class="widget-header widget-header-flat">
		<h5>&nbsp;</h5>
		<div class="widget-toolbar no-border">
			<a href="http://tiny.cc/jsphotosetup" target="_blank"><i class="js-icon-info-sign"></i> <?php echo JText::_('COM_COMMUNITY_DOC'); ?></a>
		</div>
	</div>

	<fieldset class="adminform">

		<div class="widget-body">
			<div class="widget-main">
				<h3 class="reset-gap"><?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_PHOTOS' ); ?></h3>
				<table class="admintable" cellspacing="1">
					<tbody>
						<tr>
							<td width="300" class="key">
								<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_PHOTOS_ENABLE_TIPS'); ?>">
									<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_PHOTOS_ENABLE' ); ?>
								</span>
							</td>
							<td valign="top">
								<?php echo CHTMLInput::checkbox('enablephotos' ,'ace-switch ace-switch-5', null , $this->config->get('enablephotos') ); ?>
							</td>
						</tr>
						<tr>
							<td width="300" class="key">
								<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_PHOTOS_GIF_ANIMATION_ENABLE_TIPS'); ?>">
									<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_PHOTOS_GIF_ANIMATION_ENABLE' ); ?>
								</span>
							</td>
							<td valign="top">
								<?php echo CHTMLInput::checkbox('enable_animated_gif' ,'ace-switch ace-switch-5', null , $this->config->get('enable_animated_gif') ); ?>
							</td>
						</tr>
						<tr>
							<td class="key">
								<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_PHOTOS_CREATION_LIMIT_TIPS'); ?>">
									<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_PHOTOS_CREATION_LIMIT' ); ?>
								</span>
							</td>
							<td valign="top">
								<input type="text" name="photouploadlimit" value="<?php echo $this->config->get('photouploadlimit' );?>" size="10" />
							</td>
						</tr>
						<tr>
							<td class="key">
								<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_PHOTOS_MAP_DEFAULT_TIPS'); ?>">
									<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_PHOTOS_MAP_DEFAULT' ); ?>
								</span>
							</td>
							<td valign="top">
								<?php echo CHTMLInput::checkbox('photosmapdefault' ,'ace-switch ace-switch-5', null , $this->config->get('photosmapdefault') ); ?>
							</td>
						</tr>
						<tr>
							<td class="key">
								<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_PHOTOS_MAXIMUM_UPLOAD_SIZE_TIPS'); ?>">
									<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_PHOTOS_MAXIMUM_UPLOAD_SIZE' ); ?>
								</span>
							</td>
							<td valign="top">
								<div><input type="text" size="3" name="maxuploadsize" value="<?php echo $this->config->get('maxuploadsize');?>" /> (MB)</div>
								<div><?php echo JText::sprintf('COM_COMMUNITY_CONFIGURATION_PHOTOS_MAXIMUM_UPLOAD_SIZE_FROM_PHP', $this->uploadLimit );?></div>
							</td>
						</tr>
						<tr>
							<td class="key">
								<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_PHOTOS_DELETE_ORIGINAL_TIPS'); ?>">
									<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_PHOTOS_DELETE_ORIGINAL' ); ?>
								</span>
							</td>
							<td valign="top">
								<?php echo CHTMLInput::checkbox('deleteoriginalphotos' ,'ace-switch ace-switch-5', null , $this->config->get('deleteoriginalphotos') ); ?>
							</td>
						</tr>
						<tr>
							<td class="key">
								<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_PHOTOS_IMAGEMAGICK_PATH_TIPS'); ?>">
									<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_PHOTOS_IMAGEMAGICK_PATH' ); ?>
								</span>
							</td>
							<td valign="top">
								<input name="magickPath" type="text" size="60" value="<?php echo $this->config->get('magickPath');?>" />
							</td>
						</tr>

						<tr>
							<td class="key">
								<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_PHOTOS_AUTO_SET_COVER_TIPS'); ?>">
									<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_PHOTOS_AUTO_SET_COVER' ); ?>
								</span>
							</td>
							<td valign="top">
								<?php echo CHTMLInput::checkbox('autoalbumcover' ,'ace-switch ace-switch-5', null , $this->config->get('autoalbumcover') ); ?>
							</td>
						</tr>
						<tr>
							<td class="key">
								<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_PHOTOS_AUTO_ROTATE_TIPS'); ?>">
									<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_PHOTOS_AUTO_ROTATE' ); ?>
								</span>
							</td>
							<td valign="top">
								<?php echo CHTMLInput::checkbox('photos_auto_rotate' ,'ace-switch ace-switch-5', null , $this->config->get('photos_auto_rotate') ); ?>
							</td>
						</tr>
						<tr>
                            <td class="key">
								<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_PHOTOS_OUTPUT_QUALITY_TIPS'); ?>">
									<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_PHOTOS_OUTPUT_QUALITY' ); ?>
								</span>
                            </td>
                            <td valign="top">
                                <?php echo $this->lists['imgQuality']; ?>
                            </td>
                        </tr>
                        <tr>
                            <td class="key">
								<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_PHOTOS_WINDOW_MODE_TIPS'); ?>">
									<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_PHOTOS_WINDOW_MODE' ); ?>
								</span>
                            </td>
                            <td valign="top">
                                <?php echo $this->lists['albumMode']; ?>
                            </td>
                        </tr>
					</tbody>
				</table>

				<h3 class="reset-gap"><?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_LOCATION' ); ?></h3>
				<table class="admintable" cellspacing="1">
					<tbody>
						<tr>
							<td width="300" class="key">
								<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_LOCATION_PHOTOS_ENABLE_TIPS'); ?>">
									<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_LOCATION_PHOTOS_ENABLE' ); ?>
								</span>
							</td>
							<td valign="top">
								<?php echo CHTMLInput::checkbox('enable_photos_location' ,'ace-switch ace-switch-5', null , $this->config->get('enable_photos_location') ); ?>
							</td>
						</tr>
					</tbody>
				</table>

                <!-- Photo's watermark settings-->
                <h3 class="reset-gap"><?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_PHOTOS_WATERMARK' ); ?></h3>
                <table class="admintable" cellspacing="1">
                    <tbody>
                    <tr>
                        <td width="300" class="key">
								<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_PHOTOS_WATERMARK_SETTINGS_TIPS'); ?>">
									<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_PHOTOS_WATERMARK_SETTINGS' ); ?>
								</span>
                        </td>
                        <td valign="top">
                            <?php echo CHTMLInput::checkbox('photo_watermark' ,'ace-switch ace-switch-5', null , $this->config->get('photo_watermark') ); ?>
                        </td>
                    </tr>
                    <tr>
                        <td class="key">
								<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_PHOTOS_MINIMUM_PHOTO_X_SIZE_TIPS'); ?>">
									<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_PHOTOS_MINIMUM_PHOTO_X_SIZE' ); ?>
								</span>
                        </td>
                        <td valign="top">
                            <input type="text" name="min_width_img_watermark" value="<?php echo $this->config->get('min_width_img_watermark' );?>" size="10" />
                        </td>
                    </tr>
                    <tr>
                        <td class="key">
								<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_PHOTOS_MINIMUM_PHOTO_Y_SIZE_TIPS'); ?>">
									<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_PHOTOS_MINIMUM_PHOTO_Y_SIZE' ); ?>
								</span>
                        </td>
                        <td valign="top">
                            <input type="text" name="min_height_img_watermark" value="<?php echo $this->config->get('min_height_img_watermark' );?>" size="10" />
                        </td>
                    </tr>
                    <tr>
                        <td  class="key">
                            <span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_MULTIPROFILE_WATERMARK_TIPS'); ?>"><?php echo JText::_( 'COM_COMMUNITY_MULTIPROFILE_WATERMARK' ); ?></span>
                        </td>
                        <td>
                            <div>
                                <div class="ace-file-input">
                                    <input type="file" name="watermark" id="watermark">
                                </div>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td class="key">
								<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_PHOTOS_WATERMARK_POSITION_SETTINGS_TIPS'); ?>">
									<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_PHOTOS_WATERMARK_POSITION_SETTINGS' ); ?>
								</span>
                        </td>
                        <td valign="top">
                            <?php echo $this->lists['watermarkPosition']; ?>
                        </td>
                    </tr>
                    <tr>
                        <td class="key">
                            <span class="js-tooltip" title=""><?php echo JText::_( 'COM_COMMUNITY_MULTIPROFILE_WATERMARK_PREVIEW' ); ?></span>
                        </td>
                        <td>
                            <div class="space-12"></div>
                            <?php if( file_exists(JPATH_ROOT .'/'. COMMUNITY_WATERMARKS_PATH .'/'.WATERMARK_DEFAULT_NAME.'.png') ){ ?>
                                <img src="<?php echo JURI::root() .'/'.COMMUNITY_WATERMARKS_PATH .'/'.WATERMARK_DEFAULT_NAME.'.png';?>" style="border: 1px solid #eee;" />
                            <?php } else { ?>
                                <?php echo JText::_('-');?>
                            <?php } ?>
                        </td>
                    </tr>
                    </tbody>
                </table>
			</div>
		</div>
	</fieldset>
</div>
<script>
    jQuery('#watermark').ace_file_input({
        no_file:'No File ...',
        btn_choose:'Choose',
        btn_change:'Change'
    }).on('change', function(){
        // var files = $(this).data('ace_input_files');
        //or
        var files = $(this).ace_file_input('files');

        // var method = $(this).data('ace_input_method');
        //method will be either 'drop' or 'select'
        //or
        var method = $(this).ace_file_input('method');
    });
</script>
