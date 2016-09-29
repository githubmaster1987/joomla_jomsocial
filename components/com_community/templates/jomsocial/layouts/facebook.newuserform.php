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

?>

<h2 style="text-decoration:underline; margin-bottom:10px"><?php echo JText::_('COM_COMMUNITY_NEW_MEMBER'); ?></h2>
<div style="margin-bottom:10px"><?php echo JText::_('COM_COMMUNITY_NEW_MEMBER_DESCRIPTION'); ?></div>

<div class="joms-form__group">
    <span class="small" style="width:80px"><?php echo JText::_('COM_COMMUNITY_NAME'); ?></span>
    <input type="text" class="joms-input" name="name" value="<?php echo $userInfo['name']; ?>">
    <p class="joms-help" style="color:red"></p>
</div>

<div class="joms-form__group">
    <span class="small" style="width:80px"><?php echo JText::_('COM_COMMUNITY_USERNAME'); ?></span>
    <input type="text" class="joms-input" name="username" value="">
    <p class="joms-help" style="color:red"></p>
</div>

<div class="joms-form__group">
    <span class="small" style="width:80px"><?php echo JText::_('COM_COMMUNITY_EMAIL'); ?></span>
    <input type="text" class="joms-input" name="email" value="<?php echo $userInfo['email']; ?>">
    <p class="joms-help" style="color:red"></p>
</div>

<?php if (isset($profileTypes) && count($profileTypes) > 0) { ?>
<div class="jsProfileType">
    <div><a href="javascript: void(0);" class="fb-hideshow-profiletype" style="font-style: none;"><?php echo JText::_('COM_COMMUNITY_INVALID_FB_PROFILE_TYPE_SELECT');?></a></div>
    <?php
        foreach($profileTypes as $profile)
        {
    ?>
    <div class="fb-connect-profiletype" style="display:none;">
        <label class="lblradio-block" style="font-weight:700;"><input type="radio" value="<?php echo $profile->id; ?>" id="profile-<?php echo $profile->id;?>" name="profiletype" style="margin-right:5px" />
            <span>
                <?php echo JText::_($profile->name);?>
                <?php if (false) {//( $profile->approvals ){?>
                <sup><?php echo JText::_('COM_COMMUNITY_REQUIRE_APPROVAL');?></sup>
                <?php } ?>
            </span>

            <?php if( $default == $profile->id ){?>
            <br />
            <span style="margin-left: 25px; font-style: none;">
                <?php echo JText::_('COM_COMMUNITY_ALREADY_USING_THIS_PROFILE_TYPE');?>
            </span>
            <?php } ?>
            <br />
            <span style="margin-left: 25px; font-weight: normal;">
                <?php echo JText::_($profile->description);?>
            </span>
        </label>
    </div>
    <?php
        }
    ?>
</div>
<?php } ?>

<script>
joms.jQuery(function() {
    joms.jQuery('.fb-hideshow-profiletype').click(function() {
        joms.jQuery('.fb-connect-profiletype').toggle();
    });
});
</script>
