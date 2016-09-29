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

    jimport('joomla.application.component.controller' );
    jimport('joomla.filesystem.folder');
    jimport('joomla.filesystem.file');
    jimport('joomla.filesystem.archive');

    require_once JPATH_COMPONENT.'/controllers/controller.php';
    require_once JPATH_ROOT.'/administrator/components/com_community/defaultItems.php';
    require_once JPATH_ROOT.'/administrator/components/com_community/installer.updater.php';


    define('DBVERSION', '20'); // update db based on its version, remember to increase to 21 for next version
    define("JOOMLA_MENU_PARENT", 'parent_id');
    define("JOOMLA_MENU_COMPONENT_ID", 'component_id');
    define("JOOMLA_MENU_LEVEL", 'level');
    define('JOOMLA_MENU_NAME' , 'title');
    define('JOOMLA_MENU_ROOT_PARENT', 1);
    define('JOOMLA_MENU_LEVEL_PARENT', 1);
    define('JOOMLA_PLG_TABLE', '#__extensions');

    /**
     * JomSocial Component Controller
     */
    class CommunityControllerInstaller extends CommunityController
    {
        public function __construct()
        {
            // Clean any buffer. (From ajax script in admin.community.php)
            $out = ob_get_contents();
            ob_end_clean();
            parent::__construct();
        }

        public function display($cachable = false, $urlparams = false){
            $check = array();

            // All this file must exist before we can continue. Should not be an issue
            $check['backend'] 	= file_exists(JPATH_ROOT . '/administrator/components/com_community/backend.zip');
            $check['ajax'] 		= file_exists(JPATH_ROOT . '/components/com_community/azrul.zip');
            $check['frontend']	= file_exists(JPATH_ROOT . '/components/com_community/frontend.zip');
            $check['template'] 	= file_exists(JPATH_ROOT . '/components/com_community/templates.zip');
            $check['plugins'] 	= true; //file_exists(JPATH_ROOT . '/components/com_community/ai_plugins.zip');

            // Supported image function
            $check['lib_jpeg']	= function_exists( 'imagecreatefromjpeg' );
            $check['lib_png']	= function_exists( 'imagecreatefrompng' );
            $check['lib_gif']	= function_exists( 'imagecreatefromgif' );
            $check['lib_gd']	= function_exists( 'imagecreatefromgd' );
            $check['lib_gd2']	= function_exists( 'imagecreatefromgd2' );
            $check['lib_curl']	= in_array  ('curl', get_loaded_extensions());

            // Folder permission
            $check['writable_backend']	= is_writable( JPATH_ROOT . '/administrator/components/com_community/' );
            $check['writable_frontend']	= is_writable( JPATH_ROOT . '/components/com_community/' );
            $check['writable_plugin']	= is_writable( JPATH_ROOT . '/plugins/' );


            // Supported php.ini
            $check['php_min_version']			= $this->_phpMinVersion('5.2.4');
            $check['php_max_execution_time']	= ini_get('max_execution_time');
            $check['php_max_input_time']		= ini_get('max_input_time');
            $check['php_memory_limit']			= ini_get('memory_limit');
            $check['php_post_max_size']			= ini_get('post_max_size');
            $check['php_upload_max_filesize']	= ini_get('upload_max_filesize');

            // Supported mysql configuration
            $db = JFactory::getDBO();
            $db->setQuery("show variables like 'wait_timeout'");
            $info = $db->loadRow();
            $check['my_wait_timeout'] = count($info) > 1? $info[1] :'n/a';

            $db->setQuery("show variables like 'connect_timeout'");
            $info = $db->loadRow();
            $check['my_connect_timeout'] = count($info) > 1? $info[1] :'n/a';

            $db->setQuery("show variables like 'connect_timeout'");
            $info = $db->loadRow();
            $check['my_connect_timeout'] = count($info) > 1? $info[1] :'n/a';

            // Do not allow installation to continue
            $allowContinue = ($check['backend'] && $check['ajax'] && $check['frontend'] && $check['template'] && $check['plugins']);
            $allowContinue = ($allowContinue && $check['writable_backend'] && $check['writable_frontend'] && $check['writable_plugin']);

            include_once(JPATH_ROOT . '/administrator/components/com_community/installer/welcome.html');
            exit;
        }

        private function _phpMinVersion($v)
        {
            $phpV = PHP_VERSION;

            if ($phpV[0] >= $v[0]) {
                if (empty($v[2]) || $v[2] == '*') {
                    return true;
                } elseif ($phpV[2] >= $v[2]) {
                    if (empty($v[4]) || $v[4] == '*' || $phpV[4] >= $v[4]) {
                        return true;
                    }
                }
            }

            return false;
        }

        /**
         * Unpacked file stage
         */
        public function unpack(){
            $allowContinue = true;
            include_once(JPATH_ROOT . '/administrator/components/com_community/installer/unpack.html');
            exit;
        }

        public function ajax_unpack(){
            $jinput     = JFactory::getApplication()->input;
            $filename 		= $jinput->getWord('filename');
            $destination	= '';
            $source			= JPATH_ROOT . '/components/com_community/'.$filename.'.zip';

            switch($filename){
                case 'backend':
                    $source	= JPATH_ROOT . '/administrator/components/com_community/backend.zip';
                    $destination = JPATH_ROOT . '/administrator/components/com_community/';
                    break;
                case 'frontend':
                case 'templates':
                    $destination = JPATH_ROOT . '/components/com_community/';
                    break;
                case 'azrul':
                    $this->_installAjax();
                    exit;
                    break;
                case 'all_plugins':
                    $this->_installAiPlugin();
                    exit;
                    break;
                case 'modules':
                    $this->_installModules();
                    exit;
                    break;
            }
            $destination	= JPath::clean( $destination );
            $source			= JPath::clean( $source );

            JArchive::extract($source, $destination);

            //replace default mood_ if it already exists in the system
            // default moods starts from 19 to 46
            for($i = 19; $i < 47; $i++){
                $copyImgPath = COMMUNITY_PATH_ASSETS.'/mood_'.$i."_new.jpeg";
                if(file_exists($copyImgPath)){
                    copy($copyImgPath,COMMUNITY_PATH_ASSETS.'/mood_'.$i.".jpeg");
                }
            }
            exit;
        }

        /**
         * Prepare database stage
         */
        public function prepdatabase(){
            $allowContinue = true;
            include_once(JPATH_ROOT . '/administrator/components/com_community/installer/prepdatabase.html');
            exit;
        }

        /**
         *
         * Update Database
         * @todo Upgrade process from older version of JomSocial.
         *
         */

        public function ajax_prepdatabase(){
            $mainframe	= JFactory::getApplication();
            $jinput 	= $mainframe->input;
            $stage = $jinput->get('stage', NULL, 'STRING') ;
            $allowContinue = true;

            switch($stage){
                case 'installschema':
                    $this->_installSchema();
                    break;
                case 'updateconfig':
                    $this->_updateconfig();
                    break;
                case 'createmenu':
                    $this->_createMenu();
                    break;
                case 'createtoolbar':
                    $this->_createToolbar();
                    break;
                case 'customfields':
                    $this->_customFields();
                    break;
                case 'upgrade':
                    $this->_updateDb();
                    break;
                case 'scssupdate':
                    $this->_updateSCSS();
                    break;
            }
            exit;
        }

        /**
         * Install plugins
         */
        public function plugins(){
            $allowContinue = true;
            include_once(JPATH_ROOT . '/administrator/components/com_community/installer/plugins.html');
            exit;
        }

        /**
         * Install modules
         */
        public function modules(){
            $allowContinue = true;
            include_once(JPATH_ROOT . '/administrator/components/com_community/installer/modules.html');
            exit;
        }

        public function ajax_plugins(){
            $allowContinue = true;
            include_once(JPATH_ROOT . '/administrator/components/com_community/installer/plugins.html');
            exit;
        }

        /**
         * Final stage. Completed
         */
        public function done(){
            $allowContinue = true;
            include_once(JPATH_ROOT . '/administrator/components/com_community/installer/done.html');

            // remove dummy instaler marker file
            $file       = JPATH_ROOT.'/administrator/components/com_community/installer.dummy.ini';
            if (JFile::exists($file))
                JFile::delete($file);
            exit;
        }

        /**
         * Install default schema
         */
        private function _installSchema()
        {
            $db		= JFactory::getDBO();

            $buffer = file_get_contents(JPATH_ROOT.'/administrator/components/com_community/install.mysql.utf8.sql');
            jimport('joomla.installer.helper');
            $queries = JDatabaseDriver::splitSql($buffer);

            if (count($queries) != 0)
            {
                // Process each query in the $queries array (split out of sql file).
                foreach ($queries as $query)
                {
                    $query = trim($query);
                    if ($query != '' && $query{0} != '#')
                    {
                        $db->setQuery($query);
                        try {
                            $db->execute();
                        } catch (Exception $e) {
                            return $e->getCode().':'.$e->getMessage();
                        }
                    }
                }
            }

            return false;
        }

        private function _installAjax(){
            if($this->_ajaxSystemNeedsUpdate()){
                $source	= JPATH_ROOT . '/components/com_community/azrul.zip';
                jimport('joomla.installer.installer');
                jimport('joomla.installer.helper');

                $package   = JInstallerHelper::unpack($source);
                $installer = JInstaller::getInstance();

                if ( ! $installer->install($package['dir']))
                {
                    // There was an error installing the package

                }

                // Cleanup the install files
                if ( ! is_file($package['packagefile']))
                {
                    //$config                 = JFactory::getConfig();
                    //$package['packagefile'] = $config->get('tmp_path').'/'.$package['packagefile'];

                    $app = JFactory::getApplication();
                    $package['packagefile'] = JFactory::getConfig()->get('tmp_path').'/'.$package['packagefile'];
                }

                JInstallerHelper::cleanupInstall('', $package['extractdir']);
            }

            //we will need to disable azrul.system if its previously installed
            $this->_disablePlugin('azrul.system', true, 'system/azrul.system');

            //enable plugin
            $this->_enablePlugin('jomsocial.system');
        }

        /**
         * Method to check if the system plugins exists
         *
         * @returns boolean	True if system plugin needs update, false otherwise.
         **/
        private function _ajaxSystemNeedsUpdate()
        {
            $xml	= JPATH_PLUGINS.'/system/jomsocial.system.xml';

            // Check if the record also exists in the database.
            $db		= JFactory::getDBO();

            $query	= 'SELECT COUNT(1) FROM '.$db->quoteName(JOOMLA_PLG_TABLE) .' WHERE '
                . $db->quoteName( 'element' ).'='.$db->Quote( 'jomsocial.system' );
            $db->setQuery( $query );
            $dbExists	= $db->loadResult() > 0;

            if( !$dbExists )
            {
                return true;
            }

            // Test if file exists
            if( file_exists( $xml ) )
            {
                $parser = new SimpleXMLElement($xml, NULL, TRUE);
                $version = $parser->version;

                if( $version >= '3.2' && $version != 0 )
                    return false;
            }

            return true;
        }

        /**
         * @param $plugin
         * @param bool $delete
         * @param string $path path to the plugin, example, system/jomsocial.system
         * @return bool|string
         */
        private function _disablePlugin($plugin, $delete = false, $path = '')
        {
            $db         = JFactory::getDBO();

            if($delete){
                $query	= 'DELETE FROM '.$db->quoteName('#__extensions')
                    .' WHERE '.$db->quoteName('element').' = '.$db->quote($plugin);
            }else{
                $query	= 'UPDATE '.$db->quoteName('#__extensions').' SET '.$db->quoteName('enabled').' = '.$db->quote(0)
                    .' WHERE '.$db->quoteName('element').' = '.$db->quote($plugin);
            }

            $db->setQuery($query);

            try {
                $db->execute();
                //delete if applicable
                if($delete){
                    $dir = JPATH_PLUGINS.DIRECTORY_SEPARATOR.$path;
                    if(is_dir($dir)){
                        JFolder::delete($dir);
                    }
                }
                return true;
            } catch (Exception $e) {
                return $e->getCode().':'.$e->getMessage();
            }
            return false;
        }

        /**
         * Enable installed plugins
         */
        private function _enablePlugin($plugin)
        {
            $db         = JFactory::getDBO();
            $version    = new JVersion();
            $joomla_ver = $version->getHelpVersion();

            $query	= 'UPDATE '.$db->quoteName('#__extensions').' SET '.$db->quoteName('enabled').' = '.$db->quote(1)
                .' WHERE '.$db->quoteName('element').' = '.$db->quote($plugin);

            $db->setQuery($query);

            try {
                $db->execute();
                return true;
            } catch (Exception $e) {
                return $e->getCode().':'.$e->getMessage();
            }
        }

        /**
         * Update configuration and default categories
         */
        private function _updateconfig()
        {

            if(CommunityDefaultItem::checkDefaultCategories('events')){
                CommunityDefaultItem::addDefaultEventsCategories();
            }

            if(CommunityDefaultItem::checkDefaultCategories('groups')){
                CommunityDefaultItem::addDefaultGroupCategories();
            }

            if(CommunityDefaultItem::checkDefaultCategories('videos')){
                CommunityDefaultItem::addDefaultVideosCategories();
            }

            if(CommunityDefaultItem::checkDefaultCategories('userpoints')){
                CommunityDefaultItem::addDefaultUserPoints();
            }

            return true;
        }

        /**
         * Create 1 entry in main menu and a series of menu items of 'JomSocial' type
         */
        private function _createMenu()
        {

            if(!CommunityDefaultItem::menuTypesExist() && !CommunityDefaultItem::menuExist()){
                CommunityDefaultItem::addDefaultMenuTypes();
                CommunityDefaultItem::addMenuItems();
            }

            if (CommunityDefaultItem::menuExist()){
                //we no longer update the menu items if menu exists
                //CommunityDefaultItem::updateMenuItems();
            }

            return true;
        }

        /**
         * Unused
         */
        private function _createToolbar()
        {
            return true;
        }

        /**
         * Insert custom fields
         */
        private function _customFields()
        {
            if(CommunityDefaultItem::checkDefaultCategories('fields'))
            {
                CommunityDefaultItem::addDefaultCustomFields();
            }
            return;
        }

        /**
         * Run Database upgrade script
         */

        private function _updateDB()
        {
            $dbVersion = $this->_getDBVersion();

            if($dbVersion)
            {
                while($dbVersion < DBVERSION)
                {
                    //it dosent check any error
                    call_user_func(array('communityInstallerUpdate', "update_".$dbVersion));

                    // increment db version
                    $dbVersion++;
                }

                communityInstallerUpdate::updateDBVersion( DBVERSION );

                return;
            }

            communityInstallerUpdate::insertDBVersion( DBVERSION );
            communityInstallerUpdate::insertBasicConfig();
        }

        /**
         * update SCSS if applicable
         */
        private function _updateSCSS(){
            // Regenerate scss if this is an upgrade
            require_once JPATH_ROOT.'/components/com_community/libraries/core.php';
            JTable::addIncludePath(JPATH_COMPONENT.'/tables');
            $themeTable = JTable::getInstance('Theme', 'CommunityTable');
            $themeTable->load('scss');

            if(!is_null($themeTable->value)){
                require_once JPATH_COMPONENT.'/helpers/theme.php';
                // Set the tables path
                $scssSettings = json_decode($themeTable->value,true);
                $colorSettings = $scssSettings['colors'];
                $generalSettings = $scssSettings['general'];
                if(!is_null($colorSettings)) {
                    CommunityThemeHelper::parseScss($colorSettings, 'colors');
                }
                if(!is_null($generalSettings)) {
                    CommunityThemeHelper::parseScss($generalSettings, 'general');
                }
            }
        }

        /**
         * Get current db version
         */

        protected function _getDBVersion()
        {
            $db		= JFactory::getDBO();

            $sql = 'SELECT '.$db->quoteName('params').' '
                .'FROM '.$db->quoteName('#__community_config').' '
                .'WHERE '.$db->quoteName('name').' = '.$db->quote('dbversion') .' '
                .'LIMIT 1';
            $db->setQuery($sql);
            $result = $db->loadResult();

            return $result;
        }

        private function _installAiPlugin()
        {

            //check if old plugin exists and remove them first
            require_once JPATH_ROOT.'/administrator/components/com_community/helpers/community.php';
            list($installedPlugin, $installedModule) = CommunityHelper::preinstallExtensionCheck();
            $db = JFactory::getDbo();
            if(count($installedPlugin) > 0) {
                $inPlugin = "'".implode("', '", $installedPlugin)."'";
                //delete the old installed plugins
                $query = "DELETE FROM " . $db->quoteName('#__extensions') . " WHERE "
                    . $db->quoteName("element") . " IN (" . $inPlugin . ") AND "
                    . $db->quoteName('type') . "=" . $db->quote('plugin')
                    . " AND (".$db->quoteName('folder')." = ".$db->quote('community')
                        . " OR (" // we have to be very strict here, which mean we only search for jomsocialconnect plugin in system to avoid conflict such as kunena plg that is suppose to be removed from community, not system
                        . $db->quoteName('folder')." = ".$db->quote('system')." AND "
                        . $db->quoteName('element')."=".$db->quote('jomsocialconnect')
                        .")"
                      .")";
                $db->setQuery($query);
                $db->execute();

                //lets remove the directories [should we remove all the inactive plugin instead?]
                foreach ($installedPlugin as $plugin) {
                    $pluginPath = JPATH_ROOT . '/plugins/community/' . $plugin;
                    if (JFolder::exists($pluginPath)) {
                        JFolder::delete($pluginPath);
                    }
                }
            }

            $source			= JPATH_ROOT . '/components/com_community/all_plugins.zip';
            $destination	= JPATH_ROOT . '/components/com_community/all_plugins/';
            $plugins = array();

            if (!JFolder::exists($destination))
            {
                JFolder::create($destination);
            }

            if(JArchive::extract($source, $destination))
            {
                $listPlugins = JFolder::files($destination);
                foreach($listPlugins as $row){
                    $plugins[] = $destination.$row;
                }
            }

            jimport('joomla.installer.installer');
            jimport('joomla.installer.helper');

            $app = JFactory::getApplication();

            $depricatedPlugins = array(
                JPATH_ROOT . '/components/com_community/all_plugins/plg_jomsocial_kunenagroups_v2.0.3.zip',
                JPATH_ROOT . '/components/com_community/all_plugins/plg_jomsocial_kunenamenu_v2.0.3.zip',
                JPATH_ROOT . '/components/com_community/all_plugins/plg_jomsocial_mykunena_v2.0.3.zip'
            );

            foreach($plugins as $plugin)
            {
                if(in_array($plugin,$depricatedPlugins)){
                    continue;
                }

                $package   = JInstallerHelper::unpack($plugin);
                $installer = JInstaller::getInstance();

                if ( ! $installer->install($package['dir']))
                {
                    // There was an error installing the package

                }

                // Cleanup the install files
                if ( ! is_file($package['packagefile']))
                {

                    $package['packagefile'] = JFactory::getConfig()->get('tmp_path').'/'.$package['packagefile'];
                }

                JInstallerHelper::cleanupInstall('', $package['extractdir']);
            }

            $this->_enablePlugin('jomsocialuser');
            $this->_enablePlugin('walls');
            $this->_enablePlugin('jomsocialconnect');
            $this->_enablePlugin('jomsocialupdate');

            //remove temp folder
            JFolder::delete($destination);
        }

        private function _installModules()
        {

            //before installing, lets check if there's any old module that need to be removed
            require_once JPATH_ROOT.'/administrator/components/com_community/helpers/community.php';
            list($installedPlugin, $installedModule) = CommunityHelper::preinstallExtensionCheck();
            $db=JFactory::getDbo();
            if(count($installedModule) > 0){

                $inModule = "'".implode("', '", $installedModule)."'";;

                //delete the old installed module
                $query = "DELETE FROM ".$db->quoteName('#__modules')." WHERE ".$db->quoteName("module")." IN (".$inModule.")";
                $db->setQuery($query);
                $db->execute();

                $query = "DELETE FROM ".$db->quoteName('#__extensions')." WHERE ".$db->quoteName("element")." IN (".$inModule.") AND "
                    .$db->quoteName('type')."=".$db->quote('module');
                $db->setQuery($query);
                $db->execute();

                //lets remove the directories [should we remove all the inactive module instead?]
                foreach($installedModule as $mod){
                    $modPath = JPATH_ROOT.'/modules/'.$mod;
                    if(JFolder::exists($modPath)){
                        JFolder::delete($modPath);
                    }
                }
            }

            // get modules from packages
            $modules = array();
            $sourceModules			= JPATH_ROOT . '/components/com_community/all_modules.zip';
            $destinationModules	= JPATH_ROOT . '/components/com_community/all_modules/';
            if(JArchive::extract($sourceModules, $destinationModules)){
                $listModules = JFolder::files($destinationModules);
                foreach($listModules as $row){
                    $modules[] = $destinationModules.$row;
                }
            }

            jimport('joomla.installer.installer');
            jimport('joomla.installer.helper');

            $app = JFactory::getApplication();

            foreach($modules as $module)
            {
                $package   = JInstallerHelper::unpack($module);
                $installer = JInstaller::getInstance();

                if ( ! $installer->install($package['dir']))
                {
                    // There was an error installing the package
                }

                // Cleanup the install files
                if ( ! is_file($package['packagefile']))
                {
                    //$config					= JFactory::getConfig();
                    //$package['packagefile']	= $config->get('tmp_path').'/'.$package['packagefile'];

                    $package['packagefile'] = JFactory::getConfig()->get('tmp_path').'/'.$package['packagefile'];
                }

                JInstallerHelper::cleanupInstall('', $package['extractdir']);
            }
            //remove temp folder
            JFolder::delete($destinationModules);

            $this->_enableModule('members');
            $this->_enableModule('photos');
            $this->_enableModule('videos');
            $this->_enableModule('groups');
            $this->_enableModule('events');
        }

        private function _enableModule($module)
        {
            $db         = JFactory::getDBO();
            $module = 'mod_community_'.$module;

            $default = array(
                'mod_community_memberssearch'=>'',
                'mod_community_eventscalendar'=>'',
                'mod_community_members'=>'',
                'mod_community_photos'=>'',
                'mod_community_videos'=>'',
                'mod_community_groups'=>'',
                'mod_community_events'=>''
            );

            $defaultPosition = array(
                'mod_community_memberssearch'     => 'js_side_frontpage_top',
                'mod_community_members'=>'js_side_frontpage_top',
                'mod_community_photos'=>'js_side_frontpage',
                'mod_community_videos'=>'js_side_frontpage',
                'mod_community_groups'=>'js_side_frontpage_bottom',
                'mod_community_events'=>'js_side_frontpage_bottom'
            );

            $params = new CParameter( '' );
            $params->set('default',$default[$module]);

            $query	= 'SELECT * FROM ' . $db->quoteName( '#__modules' ) . ' WHERE ';
            $query	.= $db->quoteName( 'module' ) . '=' . $db->Quote( $module );

            $db->setQuery( $query );
            $result = $db->loadObject();

            if(empty($result->position))
            {
                $query	= 'UPDATE '.$db->quoteName('#__modules').' SET '.$db->quoteName('published').' = '.$db->quote(1)
                    .' , '.$db->quoteName('position').' = '. $db->quote($defaultPosition[$module])
                    .' , '.$db->quoteName('ordering'). ' = '.$db->quote(1)
                    .' , '.$db->quoteName('params'). ' = '.$db->quote($params->toString())
                    .' WHERE '.$db->quoteName('module').' = '.$db->quote($module);

                $db->setQuery($query);

                try {
                    $db->execute();
                } catch (Exception $e) {
                    return $e->getCode().':'.$e->getMessage();
                }

            }

            $query = 'SELECT COUNT(*) FROM ' .$db->quoteName( '#__modules_menu' )
                .' WHERE ' .$db->quoteName( 'moduleid' ) .' = ' . $db->quote( $result->id );

            $db->setQuery($query);

            $count = $db->loadResult();

            if($count < 1)
            {

                $query	= 'INSERT INTO ' . $db->quoteName( '#__modules_menu' )
                    . '(' . $db->quoteName( 'moduleid' ) . ', '. $db->quoteName( 'menuid' ). ')'
                    . 'VALUES('. $db->quote( $result->id ) . ', '. $db->quote( '' ). ')';

                $db->setQuery( $query );
                $db->execute();
            }
        }
    }