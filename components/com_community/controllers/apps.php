<?php
/**
 * @copyright (C) 2013 iJoomla, Inc. - All rights reserved.
 * @license GNU General Public License, version 2 (http://www.gnu.org/licenses/gpl-2.0.html)
 * @author iJoomla.com <webmaster@ijoomla.com>
 * @url https://www.jomsocial.com/license-agreement
 * The PHP code portions are distributed under the GPL license. If not otherwise stated, all images, manuals, cascading style sheets, and included JavaScript *are NOT GPL, and are released under the IJOOMLA Proprietary Use License v1.0
 * More info at https://www.jomsocial.com/license-agreement
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

class CommunityAppsController extends CommunityBaseController
{
    var $_name = "Application";
    var $_icon = 'apps';
    var $_pagination = '';

    public function display($cacheable = false, $urlparams = false)
    {
        $appsView = CFactory::getView('apps');
        echo $appsView->get('edit');
    }

    /**
     * Browse all available application in the system
     */
    public function browse()
    {
        // Get the proper views and models
        $view = CFactory::getView('apps');
        $appsModel = CFactory::getModel('apps');
        $my = CFactory::getUser();
        $data = new stdClass();

        // Check permissions
        if ($my->id == 0) {
            return $this->blockUnregister();
        }

        // Get the application listing
        $apps = $appsModel->getAvailableApps();

        for ($i = 0; $i < count($apps); $i++) {
            $app = $apps[$i];
            $app->title = $app->title;
            $app->added = $appsModel->isAppUsed($my->id, $app->name) ? true : false;
        }

        $data->applications = $apps;
        $data->pagination = $appsModel->getPagination();

        echo $view->get(__FUNCTION__, $data);
    }

    /**
     *    Displays the application author info which is fetched from the manifest / .xml file
     *
     * @params    $appName    String    Application element name
     */
    public function ajaxShowAbout($appName)
    {
        $my = CFactory::getUser();

        $filter = JFilterInput::getInstance();
        $appName = $filter->clean($appName, 'string');

        // Check permissions
        if ($my->id == 0) {
            return $this->ajaxBlockUnregister();
        }

        $appLib = CAppPlugins::getInstance();
        $html = $appLib->showAbout($appName);

        $json = array();
        $json['title'] = JText::_('COM_COMMUNITY_ABOUT_APPLICATION_TITLE');
        $json['html'] = $html;

        die( json_encode($json) );
    }

    /**
     * Save Profile ordering
     */
    public function ajaxSaveOrder($newOrder)
    {
        $filter = JFilterInput::getInstance();
        $newOrder = $filter->clean($newOrder, 'string');

        // Check permissions
        $my = CFactory::getUser();
        if ($my->id == 0) {
            return $this->ajaxBlockUnregister();
        }

        $objResponse = new JAXResponse();

        $newOrder = explode('&', $newOrder);

        $appsModel = CFactory::getModel('apps');
        $appsModel->setOrder($my->id, $newOrder);

        $objResponse->addScriptCall('joms.editLayout.doneSaving');
        return $objResponse->sendResponse();
    }

    /**
     * Store new apps positions in database
     */
    public function ajaxSavePosition($position, $newOrder)
    {
        $filter = JFilterInput::getInstance();
        $newOrder = $filter->clean($newOrder, 'string');
        $position = $filter->clean($position, 'string');

        // Check permissions
        $my = CFactory::getUser();

        if ($my->id == 0) {
            return $this->ajaxBlockUnregister();
        }

        $objResponse = new JAXResponse();
        if (!empty($newOrder)) {

            $appsModel = CFactory::getModel('apps');
            $ordering = array();
            $newOrder = explode('&', $newOrder);
            $i = 0;

            foreach ($newOrder as $order) {
                $data = explode('=', $order);
                $ordering[$data[1]] = $i;
                $i++;
            }

            $appsModel->setOrdering($my->id, $position, $ordering);
        }

        $objResponse->addScriptCall('void', 0);

        return $objResponse->sendResponse();
    }

    /**
     *    Ajax method to display the application settings
     *
     * @params    $id    Int    Application id.
     * @params    $appName    String    Application element
     **/
    public function ajaxShowSettings($id, $appName)
    {
        $filter = JFilterInput::getInstance();
        $id = $filter->clean($id, 'int');
        $appName = $filter->clean($appName, 'string');

        // Check permissions
        $my = CFactory::getUser();

        if ($my->id == 0) {
            return $this->ajaxBlockUnregister();
        }

        $json = array();

        $appsModel = CFactory::getModel('apps');
        $lang = JFactory::getLanguage();

        $lang->load('com_community');
        $lang->load('plg_community_' . JString::strtolower($appName));
        $lang->load('plg_community_' . JString::strtolower($appName), JPATH_ROOT . '/administrator');

        $xmlPath = CPluginHelper::getPluginPath('community', $appName) . '/config.xml';
        jimport('joomla.filesystem.file');

        $actions = '';
        if (JFile::exists($xmlPath)) {
            $paramStr = $appsModel->getUserAppParams($id);
            try {
                $paramStr = html_entity_decode($paramStr);
                $paramStr = preg_replace('/<br\s*(\\\\\/)?>/i', '\n', $paramStr);
            } catch(Exception $e) {}
            $params = new CParameter($paramStr, $xmlPath);
            //$paramData = (isset($params->_xml['_default']->param)) ? $params->_xml['_default']->param : array();
            //$paramData = $params->getParams();

            $html = '<form method="POST" action="" name="appSetting" id="appSetting" class="reset-gap">';
            $html .= $params->render();
            $html .= '<input type="hidden" value="' . $id . '" name="appid"/>';
            $html .= '<input type="hidden" value="' . $appName . '" name="appname"/>';
            $html .= '</form>';

            //if(!empty($paramData) && $paramData !==false)
            {
                $actions = '<input onclick="joms.apps.saveSettings()" type="submit" value="' . JText::_('COM_COMMUNITY_APPS_SAVE_BUTTON') . '" class="joms-button--primary joms-button--full-small" name="Submit"/>';
            }

            $json['html'] = $html;
            $json['btnSave'] = JText::_('COM_COMMUNITY_APPS_SAVE_BUTTON');

        } else {
            $html = '<div class-"ajax-notice-apps-configure">' . JText::_('COM_COMMUNITY_APPS_AJAX_NO_CONFIG') . '</div>';
            $json['html'] = $html;
        }

        $json['title'] = JText::_('COM_COMMUNITY_APPS_SETTINGS_TITLE');
        $json['paramStr'] = isset($paramStr) ? $paramStr : '';
        $json['xmlPath'] = $xmlPath;

        die( json_encode($json) );
    }

    /**
     *
     */
    public function ajaxSaveSettings($postvars)
    {
        // Check permissions
        $my = CFactory::getUser();

        $filter = JFilterInput::getInstance();
        $postvars = $filter->clean($postvars, 'array');

        if ($my->id == 0) {
            return $this->ajaxBlockUnregister();
        }

        $json = array();
        $appsModel = CFactory::getModel('apps');

        $appName = $postvars['appname'];
        $id = $postvars['appid'];

        // @rule: Test if app is core app as we need to add into the db
        $pluginId = $appsModel->getPluginId($appName);
        $appParam = new CParameter($appsModel->getPluginParams($pluginId));

        if ($pluginId && $my->id != 0 && $appParam->get('coreapp')) {
            // Add new app in the community plugins table
            $appsModel->addApp($my->id, $appName);

            // @rule: For core applications, the ID might be referring to Joomla's id. Get the correct id if needed.
            $id = $appsModel->getUserApplicationId($appName, $my->id);
        }

        // Make sure this is valid for current user.
        if (!$appsModel->isOwned($my->id, $id)) {
            $json['error'] = JText::_('COM_COMMUNITY_PERMISSION_ERROR');
            die( json_encode($json) );
        }

        $post = array();

        // convert $postvars to normal post
        $pattern = "'params\[(.*?)\]'s";
        for ($i = 0; $i < count($postvars); $i++) {
            if (!empty($postvars[$i]) && is_array($postvars[$i])) {
                $key = $postvars[$i][0];
                // Blogger view

                preg_match($pattern, $key, $matches);
                if ($matches) {
                    $key = $matches[1];
                }
                $post[$key] = $postvars[$i][1];
            }
        }

        //$xmlPath = JPATH_COMPONENT.'/applications/'.$appName.'/'.$appName.'.xml';
        $xmlPath = CPluginHelper::getPluginPath('community', $appName) . '/config.xml';
        $params = new CParameter($appsModel->getUserAppParams($id), $xmlPath);

        //@since 4.1, we must make sure that count parameter must be in numeric
        if(isset($post['count']) && !is_numeric($post['count'])){
            // if count is not numeric, we will reset the count to the default value
            $post['count'] = $appParam->get('count');
        }

        $params->bind($post);
        //echo $params->toString();

        $appsModel->storeParams($id, $params->toString());

        $json['success'] = true;
        die( json_encode($json) );
    }

    /**
     * Show privacy options for apps
     */
    public function ajaxShowPrivacy($appName)
    {
        $filter = JFilterInput::getInstance();
        $appName = $filter->clean($appName, 'string');

        // Check permissions
        $my = CFactory::getUser();

        if ($my->id == 0) {
            return $this->ajaxBlockUnregister();
        }

        $appLib = CAppPlugins::getInstance();
        $html = $appLib->showPrivacy($appName);

        $json = array(
            'title'   => JText::_('COM_COMMUNITY_APPS_PRIVACY_TITLE'),
            'html'    => $html,
            'btnSave' => JText::_('COM_COMMUNITY_APPS_SAVE_BUTTON')
        );

        die( json_encode($json) );
    }

    /**
     * Show privacy options for apps
     */
    public function ajaxSavePrivacy($appName, $val)
    {
        // Check permissions
        $my = CFactory::getUser();

        $filter = JFilterInput::getInstance();
        $appName = $filter->clean($appName, 'string');
        $val = $filter->clean($val, 'string');

        if ($my->id == 0) {
            return $this->ajaxBlockUnregister();
        }

        $appsModel = CFactory::getModel('apps');

        // @rule: Test if app is core app as we need to add into the db
        $pluginId = $appsModel->getPluginId($appName);
        $appParam = new CParameter($appsModel->getPluginParams($pluginId));

        if ($pluginId && $my->id != 0 && $appParam->get('coreapp')) {
            // Add new app in the community plugins table
            $appsModel->addApp($my->id, $appName);
        }

        $appsModel->setPrivacy($my->id, $appName, $val);

        $json = array('success' => true);
        die( json_encode($json) );

    }

    /**
     * Remove an application from the users list.
     *
     * @param    $id    int    Application id
     */
    public function ajaxRemove($id)
    {
        // Check permissions
        $my = CFactory::getUser();

        $filter = JFilterInput::getInstance();
        $id = $filter->clean($id, 'string');

        if ($my->id == 0) {
            return $this->ajaxBlockUnregister();
        }

        $appModel = CFactory::getModel('apps');
        $name = $appModel->getAppName($id);

        // also need delete the oauth
        if ($name == 'twitter') {
            $oauth = JTable::getInstance('Oauth', 'CTable');
            $oauth->userid = $my->id;
            $oauth->delete();
        }

        $appModel->deleteApp($my->id, $id);

        CUserPoints::assignPoint('application.remove');

        $json = array(
            'title' => JText::_('COM_COMMUNITY_APPS_AJAX_REMOVED'),
            'html'  => JText::_('COM_COMMUNITY_APPS_AJAX_REMOVED')
        );

        die( json_encode($json) );
    }

    /**
     * Add an application for the user
     *
     * @param    $name    string Application name / element
     */
    public function ajaxAdd($name)
    {
        // Check permissions
        $my = CFactory::getUser();

        $filter = JFilterInput::getInstance();
        $name = $filter->clean($name, 'string');

        if ($my->id == 0) {
            return $this->ajaxBlockUnregister();
        }

        $objResponse = new JAXResponse();
        $appModel = CFactory::getModel('apps');

        // Get List of added apps
        $apps = $appModel->getAvailableApps();
        $addedApps = array();

        for ($i = 0; $i < count($apps); $i++) {
            $app = $apps[$i];

            if ($appModel->isAppUsed($my->id, $app->name))
                $addedApps[] = $app;
        }

        $appModel->addApp($my->id, $name);
        $theApp = $appModel->getAppInfo($name);
        $appId = $appModel->getUserApplicationId($name, $my->id);

        $act = new stdClass();
        $act->cmd = 'application.add';
        $act->actor = $my->id;
        $act->target = 0;
        $act->title = ''; //JText::_('COM_COMMUNITY_ACTIVITIES_APPLICATIONS_ADDED');
        $act->content = '';
        $act->app = 'app.install.' . $name;
        $act->cid = 0;

        $params = new JRegistry('');
        $params->set('app', $name);


        CActivityStream::addActor($act, $params->toString());

        //CFactory::load( 'libraries' , 'userpoints' );
        CUserPoints::assignPoint('application.add');

        // Change cWindow title
        $objResponse->addAssign('cwin_logo', 'innerHTML', JText::_('COM_COMMUNITY_ADD_APPLICATION_TITLE'));

        $formAction = CRoute::_('index.php?option=com_community&view=friends&task=deleteSent', false);
        $action = '<form name="cancelRequest" action="" method="POST">';
        $action .= '<input type="button" class="input button" name="save" onclick="joms.apps.showSettingsWindow(\'' . $appId . '\',\'' . $name . '\');" value="' . JText::_('COM_COMMUNITY_VIDEOS_SETTINGS_BUTTON') . '" />&nbsp;';
        $action .= '<input type="button" class="input button" onclick="cWindowHide();return false;" name="cancel" value="' . JText::_('COM_COMMUNITY_BUTTON_CLOSE_BUTTON') . '" />';
        $action .= '</form>';

        $html = '<div class="ajax-notice-apps-added">' . JText::_('COM_COMMUNITY_APPS_AJAX_ADDED') . '</div>';

        $objResponse->addScriptCall('cWindowAddContent', $html, $action);

        $objResponse->addScriptCall("joms.jQuery('." . $name . " .added-button').remove();");
        $objResponse->addScriptCall("joms.jQuery('." . $name . "').append('<span class=\"added-ribbon\">" . JText::_('COM_COMMUNITY_APPS_LIST_ADDED') . "</span>');");

        return $objResponse->sendResponse();
    }

    public function ajaxRefreshLayout($id, $position)
    {
        $objResponse = new JAXResponse();

        $filter = JFilterInput::getInstance();
        $id = $filter->clean($id, 'string');
        $position = $filter->clean($position, 'string');

        $my = CFactory::getUser();

        $appsModel = CFactory::getModel('apps');
        $element = $appsModel->getAppName($id);
        $pluginId = $appsModel->getPluginId($element);

        $params = JPluginHelper::getPlugin('community', JString::strtolower($element));
        $dispatcher = JDispatcher::getInstance();

        $pluginClass = 'plgCommunity' . $element;

        //$plugin = new $pluginClass($dispatcher, (array)($params));
        $plugin = JTable::getInstance('App', 'CTable');
        $plugin->loadUserApp($my->id, $element);


        switch ($position) {
            case "apps-sortable-side-top":
                $position = "sidebar-top";
                break;
            case "apps-sortable-side-bottom":
                $position = "sidebar-bottom";
                break;
            case "apps-sortable":
            default:
                $position = "content";
                break;
        }

        $appInfo = $appsModel->getAppInfo($element);

        //$plugin->setNewLayout($position);
        $plugin->postion = $position;

        $appsLib = CAppPlugins::getInstance();
        $app = $appsLib->triggerPlugin('onProfileDisplay', $appInfo->name, $my->id);

        $tmpl = new CTemplate();
        $tmpl->set('app', $app);
        $tmpl->set('isOwner', $appsModel->isOwned($my->id, $id));


        switch ($position) {
            case 'sidebar-top':
            case 'sidebar-bottom':
                $wrapper = $tmpl->fetch('application.widget');
                break;
            default:
                $wrapper = $tmpl->fetch('application.box');
        }


        $wrapper = str_replace("\r\n", "", $wrapper);
        $wrapper = str_replace("\n", "", $wrapper);
        $wrapper = addslashes($wrapper);

        $objResponse->addScriptCall("jQuery('#jsapp-" . $id . "').before('$wrapper').remove();");
        //$objResponse->addScriptCall('joms.plugin.'.$element.'.refresh()');

        //$refreshActions = $plugin->getRefreshAction();

        return $objResponse->sendResponse();
    }

    public function ajaxBrowse($position = 'content')
    {
        $filter = JFilterInput::getInstance();
        $position = $filter->clean($position, 'string');

        // Get the proper views and models
        $view = CFactory::getView('apps');
        $appsModel = CFactory::getModel('apps');
        $my = CFactory::getUser();
        $data = new stdClass();

        // Check permissions
        if ($my->id == 0) {
            return $this->blockUnregister();
        }

        // Get the application listing
        $apps = $appsModel->getAvailableApps(false);
        $realApps = array();
        for ($i = 0; $i < count($apps); $i++) {
            $app = $apps[$i];

            // Hide wall apps
            if (!$appsModel->isAppUsed($my->id, $app->name) && $app->coreapp != '1' && $app->name != 'walls') {
                $app->position = $position;
                $realApps[] = $app;
            }
        }

        $data->applications = $realApps;

        $html = $view->get('ajaxBrowse', $data);

        $json = array(
            'title' => JText::_('COM_COMMUNITY_APPS_BROWSE'),
            'html'  => $html
        );

        die( json_encode($json) );
    }

    // TODO: Put back COMMUNITY_FREE constrains.
    public function ajaxAddApp($name, $position)
    {
        // Check permissions
        $my = CFactory::getUser();
        if ($my->id == 0) {
            return $this->ajaxBlockUnregister();
        }

        $filter = JFilterInput::getInstance();
        $name = $filter->clean($name, 'string');
        $position = $filter->clean($position, 'string');

        // Add application
        $appModel = CFactory::getModel('apps');
        $appModel->addApp($my->id, $name, $position);

        // Activity stream
        $act = new stdClass();
        $act->cmd = 'application.add';
        $act->actor = $my->id;
        $act->target = 0;
        $act->title = ''; //JText::_('COM_COMMUNITY_ACTIVITIES_APPLICATIONS_ADDED');
        $act->content = '';
        $act->app = 'app.install';
        $act->cid = 0; // application id

        $params = new JRegistry('');
        $params->set('app', $name);

        //CActivityStream::addActor( $act, $params->toString() );

        // User points
        //CFactory::load( 'libraries' , 'userpoints' );
        CUserPoints::assignPoint('application.add');

        // Get application
        $id = $appModel->getUserApplicationId($name, $my->id);
        $appInfo = $appModel->getAppInfo($name);
        $params = new CParameter($appModel->getPluginParams($id, null));
        $isCoreApp = $appInfo->coreapp;

        $app = new stdClass();

        $app->id = $id;
        $app->title = isset($appInfo->title) ? $appInfo->title : '';
        $app->description = isset($appInfo->description) ? $appInfo->description : '';
        $app->isCoreApp = $isCoreApp;
        $app->name = $name;

        if($appInfo->customFavicon != ''){
            $app->favicon['64'] = JURI::root(true) . '/' .$appInfo->customFavicon;
        }elseif (JFile::exists(CPluginHelper::getPluginPath('community', $name) . '/favicon_64.png')) {
            $app->favicon['64'] = JURI::root(true) . CPluginHelper::getPluginURI('community', $name) . '/' . $name . '/favicon_64.png';
        } else {
            $app->favicon['64'] = JURI::root(true) . '/components/com_community/assets/app_avatar.png';
        }

        $tmpl = new CTemplate();
        $tmpl->set('apps', array($app));
        $tmpl->set('itemType', 'edit');
        $tmpl->set('position', $position);
        $html = $tmpl->fetch('application.item');

        $json = array(
            'success' => true,
            'title' => $app->title,
            'item' => $html,
            'id' => $app->id
        );

        die( json_encode($json) );
    }

}
