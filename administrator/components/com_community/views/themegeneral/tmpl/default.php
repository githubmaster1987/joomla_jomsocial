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
CommunityLicenseHelper::disabledHtml();
?>

<style>

    .container-main {
        padding-bottom: 0 !important;
    }


</style>


<form name="adminForm" id="adminForm" action="index.php?option=com_community" method="POST" enctype="multipart/form-data">

    <h4 class="blue"><?php echo JText::_('COM_COMMUNITY_THEME_GLOBAL_SETTINGS');?></h4>


    <div class="row-fluid">
        <div class="span12">
            <div class="control-group">
                <label class="control-label" for="scss-stream-position"><?php echo JText::_('COM_COMMUNITY_THEME_STREAM_POSITION');?></label>
                <div class="controls">
                    <?php echo $this->renderCheckbox('scss-stream-position','right'); ?>
                    <?php echo $this->renderCheckbox('scss-stream-position','left'); ?>
                </div>
                <div class="control-group">
                    <label class="control-label" for="ui-style"><?php echo JText::_('COM_COMMUNITY_THEME_BUTTON_STYLE');?></label>
                    <div class="controls">
                        <?php echo $this->renderCheckbox('scss-button-style','flat'); ?>
                        <?php echo $this->renderCheckbox('scss-button-style','subtle'); ?>
                    </div>
                </div>
                <div class="control-group">
                    <label class="control-label" for="avatar-shape"><?php echo JText::_('COM_COMMUNITY_THEME_AVATAR_SHAPE');?></label>
                    <div class="controls">
                        <?php echo $this->renderCheckbox('scss-avatar-shape','circle'); ?>
                        <?php echo $this->renderCheckbox('scss-avatar-shape','square'); ?>
                    </div>
                </div>
                <div class="control-group">
                    <label class="control-label" for="scss-avatar-style"><?php echo JText::_('COM_COMMUNITY_AVATAR_STYLE');?></label>
                    <div class="controls">
                        <?php echo $this->renderCheckbox('scss-avatar-style','bordered'); ?>
                        <?php echo $this->renderCheckbox('scss-avatar-style','plain'); ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="span12">
            <div class="control-group">
                <label class="control-label" for="enable-frontpage-login"><?php echo JText::_('COM_COMMUNITY_THEME_ENABLE_LOGIN_AREA');?></label>
                <div class="controls">
                    <?php echo $this->renderCheckbox('enable-frontpage-login',1,false); ?>
                    <?php echo $this->renderCheckbox('enable-frontpage-login',0,false); ?>
                </div>
            </div>
            <div class="control-group">
                <label class="control-label" for="enable-frontpage-image"><?php echo JText::_('COM_COMMUNITY_THEME_SHOW_FRONTPAGE_IMAGE');?></label>
                <div class="controls">
                    <?php echo $this->renderCheckbox('enable-frontpage-image',1,false); ?>
                    <?php echo $this->renderCheckbox('enable-frontpage-image',0,false); ?>
                </div>
            </div>
            <div class="control-group">
                <label class="control-label" for="-enable-frontpage-paragraph"><?php echo JText::_('COM_COMMUNITY_THEME_SHOW_FRONTPAGE_PARAGRAPH');?></label>
                <div class="controls">
                    <?php echo $this->renderCheckbox('enable-frontpage-paragraph',1,false); ?>
                    <?php echo $this->renderCheckbox('enable-frontpage-paragraph',0,false); ?>
                </div>
            </div>
        </div>
    </div>

    <div class="space-16"></div>

    <h5 class="blue"><?php echo JText::_('COM_COMMUNITY_BRANDING');?></h5>
    <div class="hr dotted"></div>

    <div class="row-fluid">
        <div class="span12">
            <div class="control-group">
                <label class="control-label" for="frontpage-image-new"><?php echo JText::_('COM_COMMUNITY_THEME_UPLOAD_FRONTPAGE_IMAGE');?></label>
                <div class="controls">
                    <?php
                    $image = JURI::root() . 'components/com_community/assets/frontpage-image-default.jpg';
                    if(isset($this->settings['general']['frontpage-image'])) {
                        $image = JUri::root().str_replace(JPATH_ROOT,'',COMMUNITY_PATH_ASSETS).'frontpage-image.'.$this->settings['general']['frontpage-image'];
                    }
                    ?>
                    <img src="<?=$image;?>" alt="" style="max-width:100%;">
                        <input type="file" name="frontpage-image-new" id="frontpage-image-new">
                        <input type="hidden" name="settings[frontpage-image]" value="<?php echo isset($this->settings['general']['frontpage-image']) ? $this->settings['general']['frontpage-image'] : "";?>" />
                </div>
            </div>
        </div>
    </div>
    <input type="hidden" name="view" value="themegeneral" />
    <input type="hidden" name="task" value="apply" />
    <input type="hidden" name="option" value="com_community" />
    <?php echo JHTML::_( 'form.token' ); ?>
</form>

<script>
$( document ).ready(function() {
    Joomla.submitbutton = function( action ) {
        if ( action === 'apply' && !checkFiles() ) {
            window.alert('<?php echo JText::_("COM_COMMUNITY_THEME_IMAGE_ERROR"); ?>')
            return false;
        }
        submitform(action);
    }

    function checkFiles() {
        var isValid = true;
        $('#adminForm input[type=file]').each(function() {
            if ( this.value && !this.value.match(/\.(jpg|jpeg|png)$/i) ) {
                isValid = false;
                return false;
            }
        });
        return isValid;
    }

    $('#frontpage-image-new').ace_file_input({
        no_file:'No File ...',
        btn_choose:'Choose',
        btn_change:'Change',
        droppable:false,
        onchange:null,
        thumbnail:false
    });

});
</script>
