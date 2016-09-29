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
<div class="joms-page">
    <form action="<?php echo CRoute::getURI(); ?>" method="post" id="jomsForm" name="jomsForm" class="community-form-validate">
    <div class="jsProfileType">
    	<ul class="joms-list">
    	<?php
    		foreach($profileTypes as $profile)
    		{
    	?>
    		<li>
    			<input class="joms-input--radio" id="profile-<?php echo $profile->id;?>" type="radio" value="<?php echo $profile->id;?>" name="profileType" <?php echo $default == $profile->id ? ' disabled CHECKED' :'';?> />
                <div class="joms-input--radio-content">
                    <label for="profile-<?php echo $profile->id;?>">
                    <h5 class="reset-gap"><?php echo JText::_($profile->name);?></h5>
                    <?php if( $profile->approvals ){?>
                        <span class="joms-text--light joms-block"><?php echo JText::_('COM_COMMUNITY_REQUIRE_APPROVAL');?></span>
                    <?php } ?>
                    <p><?php echo JText::_($profile->description);?></p>
                    <?php if( $default == $profile->id ){?>
                        <?php echo JText::_('COM_COMMUNITY_ALREADY_USING_THIS_PROFILE_TYPE');?>
                    <?php } ?>
                    </label>
                </div>
    		</li>
    	<?php
    		}
    	?>
    	</ul>
    </div>
    <?php if( (count($profileTypes) == 1 && $profileTypes[0]->id != $default) || count($profileTypes) > 1 ){?>
    	<?php if( $showNotice ){ ?>
    	<div class="joms-gap"></div>
        <span style="color: red;"><?php echo JText::_('COM_COMMUNITY_NOTE');?>:</span>
    	<span><?php echo $message;?></span>
        <div class="joms-gap"></div>
    	<?php } ?>
    <input class="joms-button joms-button--primary joms-button--small" type="submit" id="btnSubmit" value="<?php echo JText::_('COM_COMMUNITY_NEXT'); ?>" name="submit">
    <?php } ?>
    <input type="hidden" name="id" value="0" />
    <input type="hidden" name="gid" value="0" />
    <input type="hidden" id="authenticate" name="authenticate" value="0" />
    <input type="hidden" id="authkey" name="authkey" value="" />
    </form>
</div>
