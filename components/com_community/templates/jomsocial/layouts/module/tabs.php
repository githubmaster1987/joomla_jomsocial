<?php
    /**
     * @copyright (C) 2014 iJoomla, Inc. - All rights reserved.
     * @license GNU General Public License, version 2 (http://www.gnu.org/licenses/gpl-2.0.html)
     * @author iJoomla.com <webmaster@ijoomla.com>
     * @url https://www.jomsocial.com/license-agreement
     * The PHP code portions are distributed under the GPL license. If not otherwise stated, all images, manuals, cascading style sheets, and included JavaScript *are NOT GPL, and are released under the IJOOMLA Proprietary Use License v1.0
     * More info at https://www.jomsocial.com/license-agreement
     */
    // Check to ensure this file is included in Joomla!
    defined('_JEXEC') or die();
    $i = 0;
?>
<div class="joms-tab__bar">
    <?php if (count($modules) == 1 && $modules[0]->showtitle) { ?>
        <a href="#joms-app--<?php echo $modules[0]->id; ?>" class="active">
            <?php echo ($modules[0]->showtitle) ? $modules[0]->title : ''; ?>
        </a>
    <?php } else {
        if (count($modules) > 1) { ?>
            <?php foreach ($modules as $module) { ?>
                <a<?php echo $i++ ? '' : ' class="active"' ?>
                    href="#joms-app--<?php echo $module->id; ?>"><?php echo (isset($module->showtitle) && $module->showtitle) ? $module->title : '';
                    ?></a>
            <?php
            }
        }
    }?>
</div>
