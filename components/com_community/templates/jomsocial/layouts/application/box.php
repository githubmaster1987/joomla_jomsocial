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
$url = CRoute::_('index.php?option=com_community');
?>

<!-- begin: .app-box -->
<div id="joms-app--<?php echo $app->id ?>" class="joms-tab__content joms-tab__content--stream <?php if($app->core) echo " app-core"; ?>" <?php echo $first ? ' style="display:none"'  : '' ?>>

    <?php if( $isOwner && $app->hasConfig ){ ?>
    <div class="app-widget-header">
        <div class="app-box-menus">
            <div class="app-box-menu options">
                <a class="app-box-menu-icon" href="javascript: void(0)" onclick="joms.api.appSetting('<?php echo $app->id;?>','<?php echo $app->name;?>');">
                    <svg viewBox="0 0 16 16" class="joms-icon">
                        <use xlink:href="<?php echo $url; ?>#joms-icon-cog"></use>
                    </svg>

                </a>
            </div>
        </div>
    </div>
    <?php } ?>
    <div class="joms-gap"></div>

    <!-- end: .app-box-header -->

    <!-- begin: .app-box-content -->
    <div class="joms-app--wrapper">
        <?php echo ($app->id=='feeds-special') ? $postBoxHTML : '';?>
        <?php echo $app->data; ?>
    </div>
    <!-- end: .app-box-content -->
</div>
<!-- end: .app-box -->
