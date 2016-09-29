<?php
/**
* @copyright (C) 2013 iJoomla, Inc. - All rights reserved.
* @license GNU General Public License, version 2 (http://www.gnu.org/licenses/gpl-2.0.html)
* @author iJoomla.com <webmaster@ijoomla.com>
* @url https://www.jomsocial.com/license-agreement
* The PHP code portions are distributed under the GPL license. If not otherwise stated, all images, manuals, cascading style sheets, and included JavaScript *are NOT GPL, and are released under the IJOOMLA Proprietary Use License v1.0
* More info at https://www.jomsocial.com/license-agreement
*/
defined('_JEXEC') OR DIE();

?>

<div class="joms-body">

    <div id="js_profile_top" class="joms-module__wrapper"><?php $this->renderModules( 'js_profile_top' ); ?></div>
    <div id="js_profile_top_stacked" class="joms-module__wrapper--stacked"><?php $this->renderModules('js_profile_top_stacked'); ?></div>
    <?php if($isMine) { ?>
    <div id="js_profile_mine_top" class="joms-module__wrapper"><?php $this->renderModules( 'js_profile_mine_top' ); ?> </div>
    <div id="js_profile_mine_top_stacked" class="joms-module__wrapper--stacked"><?php $this->renderModules('js_profile_mine_top_stacked'); ?></div>
    <?php } ?>


    <!-- begin: focus area -->
    <?php $this->view('profile')->modProfileUserinfo(); ?>
    <!-- end: focus area -->

    <div class="joms-sidebar">

        <div id="js_side_top" class="joms-module__wrapper"><?php $this->renderModules( 'js_side_top' ); ?></div>
        <div id="js_side_top_stacked" class="joms-module__wrapper--stacked"><?php $this->renderModules('js_side_top_stacked'); ?></div>
        <div id="js_profile_side_top" class="joms-module__wrapper"><?php $this->renderModules( 'js_profile_side_top' ); ?> </div>
        <div id="js_profile_side_top_stacked" class="joms-module__wrapper--stacked"><?php $this->renderModules('js_profile_side_top_stacked'); ?></div>

        <div class="joms-module__wrapper"><?php echo $sidebarTop; ?></div>
        <div class="js_profile_side_top"><?php echo $sidebarTopStacked; ?></div>

        <?php if($isMine) { ?>
            <div id="js_profile_mine_side_top" class="joms-module__wrapper"><?php $this->renderModules( 'js_profile_mine_side_top' ); ?></div>
            <div id="js_profile_mine_side_top_stacked" class="joms-module__wrapper--stacked"><?php $this->renderModules('js_profile_mine_side_top_stacked'); ?></div>
        <?php } ?>

        <?php echo $this->view('profile')->modProfileUserVideo(); ?>

        <div id="js_profile_side_middle" class="joms-module__wrapper"><?php $this->renderModules( 'js_profile_side_middle' ); ?></div>
        <div id="js_profile_side_middle_stacked" class="joms-module__wrapper--stacked"><?php $this->renderModules('js_profile_side_middle_stacked'); ?></div>

        <?php if($isMine) { ?>
            <div id="js_profile_mine_side_middle" class="joms-module__wrapper"><?php $this->renderModules( 'js_profile_mine_side_middle' ); ?></div>
            <div id="js_profile_mine_side_middle_stacked" class="joms-module__wrapper--stacked"><?php $this->renderModules('js_profile_mine_side_middle_stacked'); ?></div>
        <?php } ?>

        <?php if($isMine) { ?>
            <div id="js_profile_mine_side_bottom" class="joms-module__wrapper"><?php  $this->renderModules( 'js_profile_mine_side_bottom' );?></div>
            <div id="js_profile_mine_side_bottom_stacked" class="joms-module__wrapper--stacked"><?php $this->renderModules('js_profile_mine_side_bottom_stacked'); ?></div>
        <?php } ?>
        <div class="joms-module__wrapper"><?php echo $sidebarBottom;?></div>
        <div class="js_profile_side_bottom"><?php echo $sidebarBottomStacked; ?></div>


        <div id="js_profile_side_bottom" class="joms-module__wrapper"><?php $this->renderModules( 'js_profile_side_bottom' ); ?></div>
        <div id="js_profile_side_bottom_stacked" class="joms-module__wrapper--stacked"><?php $this->renderModules('js_profile_side_bottom_stacked'); ?></div>
        <div id="js_side_bottom" class="joms-module__wrapper"><?php $this->renderModules( 'js_side_bottom' ); ?></div>
        <div id="js_side_bottom_stacked" class="joms-module__wrapper--stacked"><?php $this->renderModules('js_side_bottom_stacked'); ?></div>

    </div>

    <div class="joms-main">

        <div id="js_profile_feed_top" class="joms-module__wrapper"><?php $this->renderModules( 'js_profile_feed_top' ); ?></div>
        <div id="js_profile_feed_top_stacked" class="joms-module__wrapper--stacked"><?php $this->renderModules('js_profile_feed_top_stacked'); ?></div>

        <div data-ui-object="frontpage-main">
            <?php $this->view('profile')->modProfileUserstatus(); ?>
            <div class="joms-middlezone" data-ui-object="joms-tabs">
                <?php echo $content; ?>
            </div>
        </div>

        <div id="js_profile_feed_bottom" class="joms-module__wrapper"><?php $this->renderModules( 'js_profile_feed_bottom' ); ?></div>
        <div id="js_profile_feed_bottom_stacked" class="joms-module__wrapper--stacked"><?php $this->renderModules('js_profile_feed_bottom_stacked'); ?></div>

    </div>

    <?php if($isMine) { ?>
        <div id="js_profile_mine_bottom" class="joms-module__wrapper"><?php $this->renderModules( 'js_profile_mine_bottom' ); ?></div>
        <div id="js_profile_mine_bottom_stacked" class="joms-module__wrapper--stacked"><?php $this->renderModules('js_profile_mine_bottom_stacked'); ?></div>
    <?php } ?>

    <div id="js_profile_bottom" class="joms-module__wrapper"><?php $this->renderModules( 'js_profile_bottom' ); ?></div>
    <div id="js_profile_bottom_stacked" class="joms-module__wrapper--stacked"><?php $this->renderModules('js_profile_bottom_stacked'); ?></div>

</div>

