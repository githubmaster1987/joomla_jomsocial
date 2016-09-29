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
<form name="privacyForm" class="joms-form reset-gap">

    <div class="joms-form__group">
        <span class="smallest"><input type="radio" class="joms-input " value="0" name="privacy"<?php echo $showCheck0;?> /></span>
        <h5 class="reset-gap"><?php echo JText::_('COM_COMMUNITY_APPS_PRIVACY_EVERYONE');?></h5>
        <?php echo JText::_('COM_COMMUNITY_APPS_PRIVACY_EVERYONE_DESC');?>
    </div>

    <div class="joms-form__group">
        <span class="smallest"><input type="radio" class="joms-input " value="10" name="privacy"<?php echo $showCheck1;?> /></span>
        <h5 class="reset-gap"><?php echo JText::_('COM_COMMUNITY_APPS_PRIVACY_FRIENDS');?></h5>
        <?php echo JText::_('COM_COMMUNITY_APPS_PRIVACY_FRIENDS_DESC');?>
    </div>

    <div class="joms-form__group">
        <span class="smallest"><input type="radio" class="joms-input " value="20" name="privacy"<?php echo $showCheck2;?> /></span>
        <h5 class="reset-gap"><?php echo JText::_('COM_COMMUNITY_PRIVACY_ME');?></h5>
        <?php echo JText::_('COM_COMMUNITY_APPS_PRIVACY_ME_DESC');?>
    </div>
	<input type="hidden" name="appname" value="<?php echo $appName;?>" />
</form>
