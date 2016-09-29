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

class CommunityHelper
{
    public static function preinstallExtensionCheck(){
        $db = JFactory::getDBO();

        //module to be removed
        $modules = array(
            'mod_activegroups',
            'mod_activitystream',
            'mod_community_quicksearch',
            'mod_community_search_nearbyevents',
            'mod_community_whosonline',
            'mod_datingsearch',
            'mod_hellome',
            'mod_jomsocialconnect',
            'mod_latestdiscussion',
            'mod_latestgrouppost',
            'mod_notify',
            'mod_photocomments',
            'mod_statistics',
            'mod_topmembers',
            'mod_videocomments'
        );

        //plugins to be removed
        $plugins = array(
            'invite', //its not plg_invite because its stored in db as invite as the element
            'input',
            'friendslocation',
			'kunena',
            'events',
            'feeds',
			'jomsocialconnect',
            'latestphoto'
        );

        $installedModules = array();
        $installedPlugins = array();

        //JInstaller
        foreach($modules as $module){
            //check if the module is installed
            $query = "SELECT id FROM ".$db->quoteName('#__modules')." WHERE ".$db->quoteName('module')."=".$db->quote($module);
            $db->setQuery($query);
            $installed = $db->loadResult();

            if($installed){
                $installedModules[] = $module;
            }
        }

        foreach($plugins as $plugin){
            //check if the plugin is installed
            $query = "SELECT extension_id FROM ".$db->quoteName('#__extensions')
                ." WHERE ("
					.$db->quoteName('folder')." = ".$db->quote('community')
							." OR (" // we have to be very strict here, which mean we only search for jomsocialconnect plugin in system to avoid conflict such as kunena plg that is suppose to be removed from community, not system
							. $db->quoteName('folder')." = ".$db->quote('system')." AND "
							. $db->quoteName('element')."=".$db->quote('jomsocialconnect')
							.")) AND "
                .$db->quoteName('element')." = ".$db->quote($plugin)." AND "
                .$db->quoteName('type')." = ".$db->quote('plugin');
            $db->setQuery($query);

            $installed = $db->loadResult();
            if($installed){
                $installedPlugins[] = $plugin;
            }
        }

        return array($installedPlugins, $installedModules);
    }

	public static function addSubmenu($view)
	{
		$views = array(
			'community'        => 'community',
			'users'            => 'users',
			'multiprofile'     => 'users',
			'configuration'    => 'community',
			'profiles'         => 'users',
			'groups'           => 'groups',
			'groupcategories'  => 'groups',
			'events'           => 'events',
			'eventcategories'  => 'events',
			'videoscategories' => 'community',
			'reports'          => 'community',
			'userpoints'       => 'users',
			'about'            => 'community'
		);

		$subViews = array(
			'community' => array(
				'community'        => JText::_('COM_COMMUNITY_TOOLBAR_HOME'),
				'configuration'    => JText::_('COM_COMMUNITY_TOOLBAR_CONFIGURATION'),
				'users'            => JText::_('COM_COMMUNITY_TOOLBAR_USERS'),
				'groups'           => JText::_('COM_COMMUNITY_TOOLBAR_GROUPS'),
				'events'           => JText::_('COM_COMMUNITY_TOOLBAR_EVENTS'),
				'videoscategories' => JText::_('COM_COMMUNITY_TOOLBAR_VIDEO_CATEGORIES'),
				'reports'          => JText::_('COM_COMMUNITY_TOOLBAR_REPORTINGS'),
				'about'            => JText::_('COM_COMMUNITY_TOOLBAR_ABOUT'),
			),
			'users' => array(
				'community'    => JText::_('COM_COMMUNITY_TOOLBAR_HOME'),
				'users'        => JText::_('COM_COMMUNITY_TOOLBAR_USERS'),
				'multiprofile' => JText::_('COM_COMMUNITY_TOOLBAR_MULTIPROFILES'),
				'profiles'     => JText::_('COM_COMMUNITY_TOOLBAR_CUSTOMPROFILES'),
				'userpoints'   => JText::_('COM_COMMUNITY_TOOLBAR_USERPOINTS'),
			),
			'groups' => array(
				'community'       => JText::_('COM_COMMUNITY_TOOLBAR_HOME'),
				'groups'          => JText::_('COM_COMMUNITY_TOOLBAR_GROUPS'),
				'groupcategories' => JText::_('COM_COMMUNITY_TOOLBAR_GROUP_CATEGORIES'),
			),
			'events' => array(
				'community'       => JText::_('COM_COMMUNITY_TOOLBAR_HOME'),
				'events'          => JText::_('COM_COMMUNITY_TOOLBAR_EVENTS'),
				'eventcategories' => JText::_('COM_COMMUNITY_TOOLBAR_EVENT_CATEGORIES')
			),
		);

		$currentView = '';

		if (array_key_exists($view, $views))
		{
			$currentView = $views[$view];
		}

		if ( ! array_key_exists($currentView, $subViews))
		{
			$currentView = 'community';
		}

		foreach ($subViews[$currentView] as $key => $val)
		{
			$isActive = ($view == $key);

			JHtmlSidebar::addEntry($val, 'index.php?option=com_community&view='.$key , $isActive);
		}
	}
}