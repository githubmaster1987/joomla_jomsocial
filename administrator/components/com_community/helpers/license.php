<?php
/**
 * @copyright (C) 2013 iJoomla, Inc. - All rights reserved.
 * @license GNU General Public License, version 2 (http://www.gnu.org/licenses/gpl-2.0.html)
 * @author iJoomla.com <webmaster@ijoomla.com>
 * @url https://www.jomsocial.com/license-agreement
 * The PHP code portions are distributed under the GPL license. If not otherwise stated, all images, manuals, cascading style sheets, and included JavaScript *are NOT GPL, and are released under the IJOOMLA Proprietary Use License v1.0
 * More info at https://www.jomsocial.com/license-agreement
 */

// Disallow direct access to this file
defined('_JEXEC') or die('Restricted access');

/*
 * This class controls which class methods can be executed based on user's license
 * Primary keys are the class names (all lowercase)
 * Secondary keys are the disabled methods within these classes (all lowercase)
 */
class CommunityLicenseHelper
{
    private static $pro = array(

        /* Controllers */

        // badges
        'communitycontrollerbadges'         => array(
            'ajaxtogglepublish',
            'deletebadge',
            'apply',
            'save',
            'store',
        ),

        // moods
        'communitycontrollermoods'          => array(
            'ajaxreorder',
            'ajaxtogglepublish',
            'apply',
            'deletemood',
            'save',
            'store',
        ),

        // themeColors
        'communitycontrollerthemecolors'    => array(
            'apply',
            'save',
        ),

        // themeGeneral
        'communitycontrollerthemegeneral'   => array(
            'apply',
            'save',
        ),

        // themeProfile
        'communitycontrollerthemeprofile'    => array(
            'apply',
            'save',
        ),




    );
    public static function _()
    {
        // If this is the Pro version, nothing to do anyway
        if(COMMUNITY_PRO_VERSION) return true;

        // Load the blocklist, default class and method
        $pro = self::$pro;
        $class = $method = null;

        // Get backtrace
        $trace = debug_backtrace();

        // Get the caller class and method (lowercase)
        if (isset($trace[1])) {
            $class  = strtolower($trace[1]['class']);
            $method = strtolower($trace[1]['function']);
        }

        //
        if(!strlen($class) || !strlen($method) || !array_key_exists($class, $pro)) return true;


        if(array_search($method,$pro[$class]) !== false)
        {
            $mainframe	= JFactory::getApplication();
            $mainframe->redirect( 'index.php?option=com_community' , JText::_( 'COM_COMMUNITY_FEATURE_DISABLED_DESC' ) , 'error');
        }
    }

    public static function disabledHtml()
    {
        if (!COMMUNITY_PRO_VERSION) {
            echo '<div class="feature--disabled">
                <h3>'.JText::_('COM_COMMUNITY_FEATURE_DISABLED_TITLE').'</h3>
                <p>'.JText::_('COM_COMMUNITY_FEATURE_DISABLED_DESC').'</p>
                <div class="space-16"></div>
                <a href="http://tiny.cc/kwk0px" class="btn btn-primary">'.JText::_('COM_COMMUNITY_BUY').'</a>
                <a href="http://tiny.cc/cyk0px" class="btn btn-success">'.JText::_('COM_COMMUNITY_UPGRADE').'</a>
            </div>';
        }
    }

}