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
echo CommunityLicenseHelper::disabledHtml();
?>

<style>
    .container-main {
        padding-bottom: 0 !important;
    }
</style>
<form name="adminForm" id="adminForm" action="index.php?option=com_community" method="POST" enctype="multipart/form-data">
<div class="widget-box">
    <div class="widget-header widget-header-flat">
        <h5><?php echo JText::_('COM_COMMUNITY_CONFIGURATION_TITLE_PAGE_SETTINGS') ?></h5>
    </div>
    <div class="widget-body">
        <div class="widget-main">
            <table cellspacing="5" cellpadding="5">
                <tr>
                    <td class="key" width="100"><?php echo JText::_('COM_COMMUNITY_PREFERENCE_DEFAULT_TAB'); ?></td>
                    <td>
                        <select name="config[default_profile_tab]">
                            <option value="0"><?php echo JText::_('COM_COMMUNITY_PREFERENCE_ACTIVITY_STREAM'); ?></option>
                            <option value="1" <?php echo (CFactory::getConfig()->get('default_profile_tab')) ? 'selected' : ''?>><?php echo JText::_('COM_COMMUNITY_PREFERENCE_ACTIVITY_ABOUT_ME'); ?></option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td class="key">
							<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_ENABLE_PROFILE_CARD_TIPS'); ?>">
								<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_ENABLE_PROFILE_CARD' ); ?>
							</span>
                    </td>
                    <td>
                        <?php echo CHTMLInput::checkbox('config[show_profile_card]' ,'ace-switch ace-switch-5', null , CFactory::getConfig()->get('show_profile_card') ); ?>
                    </td>
                </tr>
            </table>
        </div>
    </div>
</div>
<div class="space-8"></div>

<div class="widget-box">
    <div class="widget-header widget-header-flat">
        <h5><?php echo JText::_('COM_COMMUNITY_USER_INFO');?></h5>
    </div>
    <div class="widget-body">
        <div class="widget-main">
            <table class="table table-bordered table-hover">
                <thead>
                <tr class="title">
                    <th><?php echo JText::_('COM_COMMUNITY_TEXT_SPACE_BEFORE');?></th>
                    <th><?php echo JText::_('COM_COMMUNITY_TEXT_BEFORE');?></th>
                    <th><?php echo JText::_('COM_COMMUNITY_TEXT_FIELD');?></th>
                    <th><?php echo JText::_('COM_COMMUNITY_TEXT_AFTER');?></th>
                    <th><?php echo JText::_('COM_COMMUNITY_TEXT_SPACE_AFTER');?></th>
                </tr>
                </thead>
                <?php

                if(isset($this->settings['profile']) && isset($this->settings['profile']['tagline']) && strlen($this->settings['profile']['tagline'])) {
                    $blocks = json_decode($this->settings['profile']['tagline'], true);
                    foreach ($blocks as $key => $block) {
                        $blocks[$key] = $block;
                    }
                }
                for($i=0;$i<6;$i++) {
                    ?>
                    <tr>
                        <td align="center"><?php
                            echo CHTMLInput::checkbox('settings[profileSpaceBefore'.$i.']' ,'ace-switch ace-switch-5', null , isset($blocks[$i]['spacebefore']) ? $blocks[$i]['spacebefore'] : 0, "profileSpaceBefore$i");
                            ?></td>
                        <td><input type="text" name="settings[profileBefore<?php echo $i;?>]" value="<?php
                            echo (isset($blocks[$i]['before'])) ? $blocks[$i]['before'] : "";
                            ?>"/></td>
                        <td>
                            <select name="settings[profileField<?php echo $i;?>]">
                                <option value=""></option>
                                <?php
                                $group = false;
                                foreach($this->fields as $field) {

                                if($field->type == 'group') {
                                if($group) echo "</optgroup>";
                                ?>
                                <optgroup label="<?php echo $field->name;?>">
                                    <?php
                                    } else {
                                        ?>
                                        <option value="<?php echo $field->id ?>" <?php

                                        if (isset($blocks[$i]['field']) && $blocks[$i]['field'] == $field->id) echo "selected";

                                        ?>>
                                            <?php echo $field->name; ?>
                                        </option>
                                    <?php
                                    }
                                    }
                                    ?>
                            </select>
                        </td>
                        <td><input type="text" name="settings[profileAfter<?php echo $i;?>]" value="<?php
                            echo (isset($blocks[$i]['after'])) ? $blocks[$i]['after'] : "";
                            ?>"/></td>
                        </td>
                        <td><?php
                            echo CHTMLInput::checkbox('settings[profileSpaceAfter'.$i.']' ,'ace-switch ace-switch-5', null , isset($blocks[$i]['spaceafter']) ?$blocks[$i]['spaceafter'] : 0, "profileSpaceAfter$i");
                            ?></td>
                    </tr>
                <?php
                }
                ?>
            </table>
        </div>
    </div>
</div>
<div class="space-8"></div>
<div class="row-fluid">
    <div class="span24">
        <div class="widget-box">
            <div class="widget-header widget-header-flat">
                <h5><?php echo JText::_('COM_COMMUNITY_BASIC');?></h5>
            </div>
            <div class="widget-body">
                <div class="widget-main">
                    <table cellspacing="5" cellpadding="5">
                        <thead>
                        <tr>
                            <th>
                            </th>
                            <th>
                                <?php echo JText::_('COM_COMMUNITY_DEFAULT_COVER');?>
                            </th>
                            <th>
                                <?php echo JText::_('COM_COMMUNITY_DEFAULT_AVATAR');?>
                            </th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td class="key" width="10%" valign="top">
                                <?php echo JText::_('COM_COMMUNITY_MALE');?>
                            </td>
                            <td>
                                <?php
                                $image = JURI::root() . 'components/com_community/assets/cover-male-default.png';
                                if(isset($this->settings['profile']['default-cover-male']))
                                    $image = JUri::root().str_replace(JPATH_ROOT,'',COMMUNITY_PATH_ASSETS).'default-cover-male.'.$this->settings['profile']['default-cover-male'];
                                ?><img src="<?=$image;?>?ts=<?php echo time();?>" alt="" class="preview-cover">
                            </td>
                            <td width="45%" valign="top">
                                <?php
                                $image = JURI::root() . 'components/com_community/assets/user-Male.png';
                                $thumb = JURI::root() . 'components/com_community/assets/user-Male-thumb.png';

                                if(isset($this->settings['profile']['default-male-avatar'])) {
                                    $image = JUri::root() . str_replace(JPATH_ROOT, '', COMMUNITY_PATH_ASSETS) . 'default-male-avatar.' . $this->settings['profile']['default-male-avatar'];
                                    $thumb = JUri::root() . str_replace(JPATH_ROOT, '', COMMUNITY_PATH_ASSETS) . 'default-male-avatar-thumb.' . $this->settings['profile']['default-male-avatar'];
                                }
                                ?><img src="<?=$image;?>?ts=<?php echo time();?>" alt="" class="preview-avatar">
                                <img src="<?=$thumb;?>?ts=<?php echo time();?>" alt="" class="preview-avatar-thumb">
                            </td>
                        </tr>
                        <tr>
                            <td></td>
                            <td>
                                <div class="space-6"></div>
                                <input type="file" name="default-cover-male-new" id="default-cover-male-new">
                                <input type="hidden" name="settings[default-cover-male]" value="<?php echo isset($this->settings['profile']['default-cover-male']) ? $this->settings['profile']['default-cover-male'] : "";?>" />
                            </td>
                            <td>
                                <div class="space-6"></div>
                                <input type="file" name="default-male-avatar-new" id="default-male-avatar-new">
                                <input type="hidden" name="settings[default-male-avatar]" value="<?php echo isset($this->settings['profile']['default-male-avatar']) ? $this->settings['profile']['default-male-avatar'] : "";?>" />
                            </td>
                        </tr>
                        <tr>
                            <td class="key" valign="top">
                                <?php echo JText::_('COM_COMMUNITY_FEMALE');?>
                            </td>
                            <td>
                                <?php
                                $image = JURI::root() . 'components/com_community/assets/cover-female-default.png';
                                if(isset($this->settings['profile']['default-cover-female'])) {
                                    $image = JUri::root().str_replace(JPATH_ROOT,'',COMMUNITY_PATH_ASSETS).'default-cover-female.'.$this->settings['profile']['default-cover-female'];
                                }
                                ?>
                                <img src="<?=$image;?>?ts=<?php echo time();?>" alt=""  class="preview-cover">
                            </td>
                            <td>
                                <?php
                                $image = JURI::root() . 'components/com_community/assets/user-Female.png';
                                $thumb = JURI::root() . 'components/com_community/assets/user-Female-thumb.png';
                                if(isset($this->settings['profile']['default-female-avatar'])) {
                                    $image = JUri::root().str_replace(JPATH_ROOT,'',COMMUNITY_PATH_ASSETS).'default-female-avatar.'.$this->settings['profile']['default-female-avatar'];
                                    $thumb = JUri::root().str_replace(JPATH_ROOT,'',COMMUNITY_PATH_ASSETS).'default-female-avatar-thumb.'.$this->settings['profile']['default-female-avatar'];
                                }
                                ?>
                                <img src="<?=$image;?>?ts=<?php echo time();?>" alt="" class="preview-avatar">
                                <img src="<?=$thumb;?>?ts=<?php echo time();?>" alt="" class="preview-avatar-thumb">
                            </td>
                        </tr>
                        <tr>
                            <td></td>
                        <td>
                            <div class="space-6"></div>
                            <input type="file" name="default-cover-female-new" id="default-cover-female-new">
                            <input type="hidden" name="settings[default-cover-female]" value="<?php echo isset($this->settings['profile']['default-cover-female']) ? $this->settings['profile']['default-cover-female'] : "";?>" />
                        </td>
                        <td>
                            <div class="space-6"></div>
                            <input type="file" name="default-female-avatar-new" id="default-female-avatar-new">
                            <input type="hidden" name="settings[default-female-avatar]" value="<?php echo isset($this->settings['profile']['default-female-avatar']) ? $this->settings['profile']['default-female-avatar'] : "";?>" />
                        </td>
                        <tr>
                            <td class="key" valign="top">
                                <?php echo JText::_('COM_COMMUNITY_UNDEFINED');?>
                            </td>
                            <td>
                                <?php
                                $image = JURI::root() . 'components/com_community/assets/cover-undefined-default.png';
                                if(isset($this->settings['profile']['default-cover'])) {
                                    $image = JUri::root().str_replace(JPATH_ROOT,'',COMMUNITY_PATH_ASSETS).'default-cover.'.$this->settings['profile']['default-cover'];
                                }
                                ?>
                                <img src="<?=$image;?>?ts=<?php echo time();?>" alt=""  class="preview-cover">
                                <div class="space-6"></div>
                                <input type="file" name="default-cover-new" id="default-cover-new">
                                <input type="hidden" name="settings[default-cover]" value="<?php echo isset($this->settings['profile']['default-cover']) ? $this->settings['profile']['default-cover'] : "";?>" />
                            </td>
                            <td>
                                <?php
                                $image = JURI::root() . 'components/com_community/assets/user-Male.png';
                                $thumb = JURI::root() . 'components/com_community/assets/user-Male-thumb.png';
                                if(isset($this->settings['profile']['default-general-avatar'])) {
                                    $image = JUri::root().str_replace(JPATH_ROOT,'',COMMUNITY_PATH_ASSETS).'default-general-avatar.'.$this->settings['profile']['default-general-avatar'];
                                    $thumb = JUri::root().str_replace(JPATH_ROOT,'',COMMUNITY_PATH_ASSETS).'default-general-avatar-thumb.'.$this->settings['profile']['default-general-avatar'];
                                }
                                ?>
                                <img src="<?=$image;?>?ts=<?php echo time();?>" alt="" class="preview-avatar">
                                <img src="<?=$thumb;?>?ts=<?php echo time();?>" alt="" class="preview-avatar-thumb">
                                <div class="space-6"></div>
                                <input type="file" name="default-general-avatar-new" id="default-general-avatar-new">
                                <input type="hidden" name="settings[default-general-avatar]" value="<?php echo isset($this->settings['profile']['default-general-avatar']) ? $this->settings['profile']['default-general-avatar'] : "";?>" />
                            </td>
                        </tr>


                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="span12">

    </div>
</div>



<input type="hidden" name="view" value="themeprofile" />
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

    $('#default-cover-new,#default-cover-female-new,#default-cover-male-new,#default-male-avatar-new,#default-female-avatar-new,#default-general-avatar-new').ace_file_input({
        no_file:'No File ...',
        btn_choose:'Choose',
        btn_change:'Change',
        droppable:false,
        onchange:null,
        thumbnail:false
    });

});
</script>

<style type="text/css">

    .preview-cover {
        max-width: 100%;
        height: 150px;
    }

    .preview-avatar {
        max-width: 100%;
        height: 150px;
    }


</style>
