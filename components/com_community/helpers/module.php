<?php

    /**
     * @copyright (C) 2013 iJoomla, Inc. - All rights reserved.
     * @license GNU General Public License, version 2 (http://www.gnu.org/licenses/gpl-2.0.html)
     * @author iJoomla.com <webmaster@ijoomla.com>
     * @url https://www.jomsocial.com/license-agreement
     * The PHP code portions are distributed under the GPL license. If not otherwise stated, all images, manuals, cascading style sheets, and included JavaScript *are NOT GPL, and are released under the IJOOMLA Proprietary Use License v1.0
     * More info at https://www.jomsocial.com/license-agreement
     */
// no direct access
    defined('_JEXEC') or die('Restricted access');

    /* class exist checking */
    if (!class_exists('CModuleHelper')) {

        /**
         * HTML head helper
         * This class provide method to set HTML head and opengraph metas
         * @since 3.0.1
         */
        class CModuleHelper {

            public static function showTitle($position){
                $excludePosition = array(
                    'js_tabs_frontpage',
                    'js_side_frontpage',
                    'js_side_top',
                    'js_profile_side_top',
                    'js_profile_mine_side_top',
                    'js_profile_mine_side_bottom',
                    'js_profile_side_bottom',
                    'js_side_bottom',
                    'sidebar-top',
                    'sidebar-bottom',
                    'content'
                );//do not show in this position

                if(in_array($position,$excludePosition)){
                    return false;
                }
                return true;
            }
        }

    }