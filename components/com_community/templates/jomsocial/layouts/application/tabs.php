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
    <?php

        foreach ($apps as $app) {
            //special case for active tab (about me and fix will follow the backend settings)
            $active = true;
            if($app->id == 'feeds-special'){
                $config = CFactory::getConfig();
                $active = ($config->get('default_profile_tab') == 0) ? true : false;
            }elseif($app->id == 'aboutme-special'){
                $config = CFactory::getConfig();
                $active = ($config->get('default_profile_tab') == 1) ? true : false;
            }
            if (trim($app->data) != '') {
                ?>
                <a<?php echo (!$active || $i++) ? '' : ' class="active"' ?>
                    href="#joms-app--<?php echo $app->id; ?>"><?php echo JText::_($app->title); ?></a>
            <?php
            }
        }
    ?>
</div>
