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
    foreach ($apps as $app) {

?>
<a href="#joms-js--app-<?php echo $app->id; ?>" class="no-padding  <?php echo 'tab_'.$app->name;?> joms-js--app-tab-<?php echo $app->id; ?> <?php echo ($i++) || ($position == 'content') ? '' : 'active' ?>">
    <div class="joms-tab__bar--button">
        <span class="title"><?php echo $app->title; ?></span>
    </div>
</a>
<?php

    }
}

?>
