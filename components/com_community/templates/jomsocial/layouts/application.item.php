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

if ( !empty($apps) ) {
    $i = 0;
    $position = isset($position) ? $position : 'content';
    $stacked = strpos($position, '-stacked') !== FALSE;

    foreach ($apps as $app) {
        $i++;

        if ($itemType == 'edit') {

?>

<?php if ($stacked) { ?>

<div id="joms-js--app-<?php echo $app->id; ?>" class="joms-module--stacked <?php echo 'content_'.$app->name;?>">
    <div id="75" class="joms-module__header"><?php echo $this->escape( JText::_( $app->title ) ); ?></div>
    <div class="joms-module__body">
        <div class="joms-app__icon">
            <img width="50" src="<?php echo $app->favicon['64']; ?>" alt="<?php echo $this->escape(JText::_( $app->title )); ?>"/>
        </div>
        <div class="joms-app__desc">
            <span class="joms-text--light"><?php echo $this->escape( JText::_( $app->description ) ); ?></span>
            <small class="joms-text--light"><?php if(property_exists($app, 'hide_empty') && $app->hide_empty) echo JText::_('COMMUNITY_APPS_HIDE_EMPTY');?></small>
        </div>
        <div class="joms-gap"></div>
        <small class="joms-block" style="text-align:right">
            <a href="javascript:void(0);" class="joms-button--link" onclick="joms.api.appAbout('<?php echo $app->name; ?>');" title="<?php echo JText::_('COM_COMMUNITY_APPS_LIST_ABOUT'); ?>">
                <svg viewBox="0 0 16 18" class="joms-icon ">
                    <use xlink:href="#joms-icon-info"></use>
                </svg>
            </a>
            <a href="javascript:void(0);" class="joms-button--link" onclick="joms.api.appSetting('<?php echo $app->id; ?>','<?php echo $app->name; ?>');" title="<?php echo JText::_('COM_COMMUNITY_APPS_COLUMN_SETTINGS'); ?>" >
                <svg viewBox="0 0 16 18" class="joms-icon ">
                    <use xlink:href="#joms-icon-cog"></use>
                </svg>
            </a>
            <a href="javascript:void(0);" class="joms-button--link" onclick="joms.api.appPrivacy('<?php echo $app->name; ?>');" title="<?php echo JText::_('COM_COMMUNITY_APPS_COLUMN_PRIVACY'); ?>" >
                <svg viewBox="0 0 16 18" class="joms-icon ">
                    <use xlink:href="#joms-icon-lock"></use>
                </svg>
            </a>
            <?php if( !$app->isCoreApp ) { ?>
            &nbsp; | &nbsp;
            <a class="joms-button--link" href="javascript:" onclick="joms.api.appRemove('<?php echo $app->id; ?>');" title="<?php echo JText::_('COM_COMMUNITY_APPS_LIST_REMOVE'); ?>" >
                <svg viewBox="0 0 16 18" class="joms-icon" style="fill:red">
                    <use xlink:href="#joms-icon-remove"></use>
                </svg>
            </a>
            <?php } ?>
        </small>
    </div>
</div>

<?php } else { ?>

<div id="joms-js--app-<?php echo $app->id; ?>" class="joms-tab__content <?php echo 'content_'.$app->name;?>"   <?php echo ($position == 'content') || ($i > 1) ? 'style="display:none"' : '' ;?>>
    <div class="<?php if ($app->isCoreApp) echo 'app-core'; ?> app-item-edit">
        <div class="joms-gap"></div>
        <?php if ($position == 'content') { ?><div class="joms-app--wrapper"><?php } ?>
            <div class="joms-app__icon">
                <img width="50" src="<?php echo $app->favicon['64']; ?>" alt="<?php echo $this->escape(JText::_( $app->title )); ?>"/>
            </div>
            <div class="joms-app__desc">
                <span class="joms-text--light"><?php echo $this->escape( JText::_( $app->description ) ); ?></span>
                <small class="joms-text--light"><?php if(property_exists($app, 'hide_empty') && $app->hide_empty) echo JText::_('COMMUNITY_APPS_HIDE_EMPTY');?></small>
            </div>
            <div class="joms-gap"></div>
            <small class="joms-block" style="text-align:right">
                <a href="javascript:void(0);" class="joms-button--link" onclick="joms.api.appAbout('<?php echo $app->name; ?>');" title="<?php echo JText::_('COM_COMMUNITY_APPS_LIST_ABOUT'); ?>">
                    <svg viewBox="0 0 16 18" class="joms-icon ">
                        <use xlink:href="#joms-icon-info"></use>
                    </svg>
                </a>
                <a href="javascript:void(0);" class="joms-button--link" onclick="joms.api.appSetting('<?php echo $app->id; ?>','<?php echo $app->name; ?>');" title="<?php echo JText::_('COM_COMMUNITY_APPS_COLUMN_SETTINGS'); ?>" >
                    <svg viewBox="0 0 16 18" class="joms-icon ">
                        <use xlink:href="#joms-icon-cog"></use>
                    </svg>
                </a>
                <a href="javascript:void(0);" class="joms-button--link" onclick="joms.api.appPrivacy('<?php echo $app->name; ?>');" title="<?php echo JText::_('COM_COMMUNITY_APPS_COLUMN_PRIVACY'); ?>" >
                    <svg viewBox="0 0 16 18" class="joms-icon ">
                        <use xlink:href="#joms-icon-lock"></use>
                    </svg>
                </a>
                <?php if( !$app->isCoreApp ) { ?>
                &nbsp; | &nbsp;
                <a class="joms-button--link" href="javascript:" onclick="joms.api.appRemove('<?php echo $app->id; ?>');" title="<?php echo JText::_('COM_COMMUNITY_APPS_LIST_REMOVE'); ?>" >
                    <svg viewBox="0 0 16 18" class="joms-icon" style="fill:red">
                        <use xlink:href="#joms-icon-remove"></use>
                    </svg>
                </a>
                <?php } ?>
            </small>
        <?php if ($position == 'content') { ?></div><?php } ?>
    </div>
</div>

<?php } ?>

<?php

        } else if ($itemType == 'browse') {

?>

    <div class="joms-app__item <?php echo $this->escape($app->name); ?> joms-js--app joms-js--app-<?php echo $i; ?>"
            <?php echo $i > 4 ? 'style="display:none"' : ''; ?>>
        <div class="joms-app__icon">
            <img width="50" src="<?php echo $app->favicon['64']; ?>" alt="<?php echo $this->escape(JText::_( $app->title )); ?>"/>
        </div>
        <div class="joms-app__desc">
            <h4><?php echo $this->escape(JText::_( $app->title )); ?></h4>
            <p><?php echo $this->escape(JText::_( $app->description )); ?></p>
        </div>
        <span>
            <a class="joms-button--neutral joms-button--small" href="javascript:" data-appname="<?php echo $this->escape($app->name); ?>" data-position="<?php echo $app->position; ?>">
                <?php echo JText::_('COM_COMMUNITY_APPS_LIST_ADD'); ?>
            </a>
        </span>
    </div>

<?php

        }
    }

    if ( $i > 4 && $itemType == 'browse' ) {

?>

    <div style="text-align:center;">
        <button class="joms-button--neutral joms-js--btn-view-all"><?php echo JText::_('COM_COMMUNITY_VIEW_ALL'); ?></button>
    </div>

<?php

    }

} else {

?>

<?php echo JText::_('COM_COMMUNITY_NO_MORE_APPS_TO_BE_ADDED'); ?>

<?php

}

?>
