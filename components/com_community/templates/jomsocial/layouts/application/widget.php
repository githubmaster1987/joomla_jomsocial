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

<div id="joms-app--<?php echo $app->id ?>" class="joms-tab__content <?php if($app->core) echo " app-core"; ?> " <?php echo $first ? ' style="display:none"'  : '' ?>>

    <div class="joms-plugin__body">
        <?php echo $app->data; ?>

        <?php if( $isOwner && $app->hasConfig ){ ?>
        <a class="joms-button--neutral" title="<?php echo JText::_('COM_COMMUNITY_EDIT');?>" href="javascript:void(0);" class="app-box-cog cFloat-R" onclick="joms.api.appSetting('<?php echo $app->id;?>','<?php echo $app->name;?>');">
        </a>
        <?php } ?>
    </div>

</div>


