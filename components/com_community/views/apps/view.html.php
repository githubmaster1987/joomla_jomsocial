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

jimport('joomla.application.component.view');
jimport('joomla.utilities.arrayhelper');

class CommunityViewApps extends CommunityView
{
    /**
     * Deprecated since 2.2.x
     * Use index.php?option=com_community&view=profile&task=editPage instead
     */
    public function edit()
    {
        $mainframe = JFactory::getApplication();
        $mainframe->redirect(CRoute::_('index.php?option=com_community&view=profile&task=editPage', false));
    }

    /**
     * Browse all available apps
     */
    public function browse($data)
    {
        $this->addPathway(JText::_('COM_COMMUNITY_APPS_BROWSE'));

        // Load window library
        //CFactory::load( 'libraries' , 'window' );

        // Load necessary window css / javascript headers.
        CWindow::load();

        $mainframe = JFactory::getApplication();
        $my = CFactory::getUser();


        $pathway = $mainframe->getPathway();

        $document = JFactory::getDocument();
        $document->setTitle(JText::_('COM_COMMUNITY_APPS_BROWSE'));

        // Attach apps-related js
        $this->showSubMenu();

        // Get application's favicon
        $addedAppCount = 0;
        foreach ($data->applications as $appData) {
            if (JFile::exists(CPluginHelper::getPluginPath('community', $appData->name) . '/' . $appData->name . '/favicon_64.png')) {
                $appData->appFavicon = JURI::root(true) . CPluginHelper::getPluginURI('community', $appData->name) . '/' . $appData->name . '/favicon_64.png';
            } else {
                $appData->appFavicon = JURI::root(true) . '/components/com_community/assets/app_favicon.png';
            }

            // Get total added applications
            $addedAppCount = $appData->added == 1 ? $addedAppCount + 1 : $addedAppCount;
        }

        $tmpl = new CTemplate();
        echo $tmpl  ->set('applications', $data->applications)
            ->set('pagination', $data->pagination)
            ->set('addedAppCount', $addedAppCount)
            ->fetch('applications.browse');
    }

    public function ajaxBrowse($data)
    {
        $mainframe = JFactory::getApplication();
        $my = CFactory::getUser();
        $appsModel = CFactory::getModel('apps');

        // Get application's favicon
        $addedAppCount = 0;

        foreach ($data->applications as $appData) {
            if($appData->customFavicon != ''){
                $appData->favicon['64'] = JURI::root(true) . '/' .$appData->customFavicon;
            }elseif (JFile::exists(CPluginHelper::getPluginPath('community', $appData->name) . '/favicon_64.png')) {
                $appData->favicon['64'] = JURI::root(true) . CPluginHelper::getPluginURI('community', $appData->name) . '/' . $appData->name . '/favicon_64.png';
            } else {
                $appData->favicon['64'] = JURI::root(true) . '/components/com_community/assets/app_avatar.png';
            }
            // Get total added applications
            //$addedAppCount	= $appData->added == 1 ? $addedAppCount+1 : $addedAppCount;
        }

        $tmpl = new CTemplate();
        echo $tmpl  ->set('apps', $data->applications)
            ->set('itemType', 'browse')
            ->fetch('application.item');
    }

    public function _addSubmenu()
    {
        $this->addSubmenuItem('index.php?option=com_community&view=apps', JText::_('COM_COMMUNITY_APPS_MINE'));
    }

    public function showSubmenu($display=true)
    {
        $this->_addSubmenu();
        return parent::showSubmenu($display);
    }
}
