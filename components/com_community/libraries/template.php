<?php

/**
 * @copyright (C) 2013 iJoomla, Inc. - All rights reserved.
 * @license GNU General Public License, version 2 (http://www.gnu.org/licenses/gpl-2.0.html)
 * @author iJoomla.com <webmaster@ijoomla.com>
 * @url https://www.jomsocial.com/license-agreement
 * The PHP code portions are distributed under the GPL license. If not otherwise stated, all images, manuals, cascading style sheets, and included JavaScript *are NOT GPL, and are released under the IJOOMLA Proprietary Use License v1.0
 * More info at https://www.jomsocial.com/license-agreement
 */
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.view');


require_once(COMMUNITY_COM_PATH . '/libraries/browser.php');

$browser = CBrowser::getInstance();
$screen = 'desktop';

$mainframe = JFactory::getApplication();
$jinput = $mainframe->input;

/**
 * @todo code clean up this part
 */
if ($browser->isMobile()) {
    // Determine whether to display
    // desktop or mobile screen.
    $mySess = JFactory::getSession();

    // If a screen was given from the URL,
    // it means we are switching screens,
    // so we'll save preferred screen to session data.
    $screen = $jinput->get->get('screen', NULL, 'STRING');
    if (!empty($screen)) {
        $mySess->set('screen', $screen);
    }

    // Get preferred screen from session data
    $screen = $mySess->get('screen');

    // If preferred screen was not found in session data,
    // get it from user preferences.
    if (empty($screen)) {
        $my = CFactory::getUser();
        if ($my->id == 0) {
            // Use 'mobile' as default screen for guests
            $screen = 'mobile';
        } else {
            $params = $my->getParams();
            $screen = ($params->get('mobileView')) ? 'mobile' : 'desktop';
        }
    }
}
define('COMMUNITY_TEMPLATE_SCREEN', $screen);

if (!class_exists('CTemplate')) {

    /**
     * Templating system for JomSocial
     */
    class CTemplate {

        /**
         * Holds all the template variables
         * @var array
         */
        protected $_vars;
        // The name is intentionally long to avoid collision
        protected $_internalView;

        /**
         *
         * @var string
         */
        public $file;

        /**
         *
         * @var CBrowser
         */
        public $browser;

        /**
         * Constructor
         *
         * @param $file string the file name you want to load
         */
        public function __construct($file = null) {
            // $file can also be a view object. If it is an object, assign to internal object
            if (is_object($file)) {
                $this->_internalView = $file;
            } else {
                $this->file = $file;
            }

            @ini_set('short_open_tag', 'On');

            // Extract template parameters for template providers.
            if (!isset($this->params) && empty($this->params)) {
                $this->params = $this->getTemplateParams();
            }
            $this->browser = CBrowser::getInstance();
        }

        /**
         *
         * @param type $type
         * @return object
         */
        public function view($type = null) {
            if ($type == null) {
                return $this->_internalView;
            } else {
                $view = CFactory::getView($type);
                return $view;
            }
        }

        /**
         * Set a template variable.
         * @param type $name
         * @param type $value
         * @return \CTemplate
         */
        public function set($name, $value) {
            $this->_vars[$name] = $value;

            // Return this object
            return $this;
        }

        /**
         * Set a template variable by reference
         * @param type $name
         * @param type $value
         * @return \CTemplate
         */
        public function setRef($name, &$value) {
            $this->_vars[$name] = $value;

            // Return this object
            return $this;
        }

        /**
         *
         * @param type $name
         * @param type $default
         * @return mixed
         */
        public function get($name, $default = null) {
            if (isset($this->_vars[$name])) {
                return $this->_vars[$name];
            }
            return $default;
        }

        /**
         * Return template variables
         * @param type $varname
         * @return mixed
         */
        public function __get($varname) {
            return $this->get($varname);
        }

        /**
         *
         * @return boolean
         */
        public function isDesktop() {
            return $this->browser->isDesktop();
        }

        /**
         *
         * @return boolean
         */
        public function isMobile() {
            return ($this->browser->isTablet() && $this->browser->isMobile());
        }

        /**
         * Get files under template directory
         * Overriden supported
         * @param string|boolean $file
         */
        protected function _getTemplateFile($file) {
            if ($this->isDesktop()) {
                $dekstopFile = $file . '.desktop';
            }

            if ($this->isDesktop()) { /* Get desktop file version */
                $tplFile = CFactory::getPath('template://' . $dekstopFile . '.php');
                if ($tplFile === false) { /* Desktop file does not exists than use default */
                    $tplFile = CFactory::getPath('template://' . $file . '.php');
                }
            } else {
                $tplFile = CFactory::getPath('template://' . $file . '.php');
            }
            return $tplFile;
        }

        /**
         * Open, parse, and return the template file.
         *
         * @param $file string the template file name
         * @param $folder if exists, like the file to view
         * @return type
         */
        public function fetch($file = null) {

            if (empty($file)) {
                $file = $this->file;
            }

            $tplFile = $this->_getTemplateFile('layouts/' . $file);

            if ($tplFile) {
                // Template variable: $my;
                $my = CFactory::getUser();
                $this->setRef('my', $my);

                // set Up admin var
                $this->set('isCommunityAdmin', COwnerHelper::isCommunityAdmin());

                // Template variable: $config;
                if (!isset($this->_vars['config']) && empty($this->vars['config'])) {
                    $this->_vars['config'] = CFactory::getConfig();
                }

                // Template variable: the rest.
                if ($this->_vars) {
                    extract($this->_vars, EXTR_REFS);
                }

                ob_start();                    // Start output buffering
                require($tplFile);                // Include the file
                $contents = ob_get_contents(); // Get the contents of the buffer
                ob_end_clean();                // End buffering and discard
                // Replace all _QQQ_ to "
                // Language file now uses new _QQQ_ to maintain Joomla 1.6 compatibility
                $contents = CTemplate::quote($contents);

                return $contents;              // Return the contents
            }

            return '';
        }

        /**
         * Allow a template to include other template and inherit all the variable
         * @param type $file
         * @return \CTemplate
         */
        public function load($file) {
            if ($this->_vars) {
                extract($this->_vars, EXTR_REFS);
            }

            $tplFile = $this->_getTemplateFile('layouts/' . $file);
            if ($tplFile)
                include($tplFile);

            return $this;
        }

        /**
         * Alias to $document->countModules function
         */
        public function countModules($condition) {
            $document = JFactory::getDocument();
            return $document->countModules($condition);
        }

        /**
         *
         * @return \CParameter
         */
        public static function getTemplateParams() {
            $template = new CTemplateHelper();

            $defaultParam = $template->getTemplatePath('params.ini', 'default');
            $templateParam = $template->getTemplatePath('params.ini');
            $overrideParam = $template->getOverrideTemplatePath('params.ini');

            $params = new CParameter('');

            if (JFile::exists($defaultParam)) {
                $params->bind(file_get_contents($defaultParam));
            }

            if (JFile::exists($templateParam)) {
                $params->bind(file_get_contents($templateParam));
            }

            if (JFile::exists($overrideParam)) {
                $params->bind(file_get_contents($overrideParam));
            }

            return $params;
        }

        /**
         * @todo Should we remove it and use $this->browser directly
         * @return \stdClass
         */
        public static function getTemplateEnvironment() {
            jimport('joomla.environment.browser');

            $app = JFactory::getApplication();
            $browser = JBrowser::getInstance();

            $environment = new stdClass();
            $environment->joomlaTemplate = $app->getTemplate();
            $environment->browserName = $browser->getBrowser();

            return $environment;
        }

        /**
         *
         * @param type $file
         */
        public static function addStylesheet($file) {
            $assetsFile = CFactory::getPath('template://assets/css/' . $file . '.css');
            if ($assetsFile) {
                $assetsDir = dirname($assetsFile);
            } else {
                /* This file is not located under template */
                $assetsFile = CFactory::getPath('assets://' . $file . '.css');
                if ($assetsFile) {
                    $assetsDir = dirname($assetsFile);
                }
            }
            if (isset($assetsDir)) {
                /**
                 * @todo Update CAssets
                 */
                CFactory::attach($file . '.css', 'css', CPath::getInstance()->toUrl($assetsDir) . '/');
            }
        }

        /**
         *
         * @param type $file
         */
        public static function addScript($file) {
            $assetsFile = CFactory::getPath('template://js/' . $file . '.js');
            if ($assetsFile) {
                $assetsDir = dirname($assetsFile);
            } else {
                /* This file is not located under template */
                $assetsFile = CFactory::getPath('assets://' . $file . '.js');
                if ($assetsFile) {
                    $assetsDir = dirname($assetsFile);
                }
            }
            if (isset($assetsDir)) {
                /**
                 * @todo Update CAssets
                 */
                CFactory::attach($file . '.js', 'js', CPath::getInstance()->toUrl($assetsDir) . '/');
            }
        }

        /**
         *
         * @param type $position
         * @param string $attribs
         */
        public static function renderModules($position, $attribs = array()) {
            jimport('joomla.application.module.helper');

            $modules = JModuleHelper::getModules($position);
            $modulehtml = '';

            // If style attributes are not given or set, we enforce it to use the xhtml style
            // so the title will display correctly.
            if (!isset($attribs['style'])) {
                $attribs['style'] = 'none';
            }

            foreach($modules as $key=>$mod){
                $mod->html = JModuleHelper::renderModule($mod, $attribs);

                //this is to hide the app if needed, just set $module->hide = true in module main file
                if(isset($mod->hide) && $mod->hide){
                    unset($modules[$key]);
                }
            }

            $template = new CTemplate();
            $html = $template->set('modules', $modules)
                ->set('attribs', $attribs)
                ->fetch('module/content');

            echo $html;
        }

        /**
         *
         * @param type $text
         * @return type
         */
        public static function escape($text) {

            return CStringHelper::escape($text);
        }

        /**
         * @todo Should we remove this
         * @return type
         */
        public function mobileTemplate() {
            return COMMUNITY_TEMPLATE_SCREEN == 'mobile';
        }

        /**
         *
         * @return string
         */
        public static function getPoweredByLink() {
            $powerBy = '';

            if (!COMMUNITY_PRO_VERSION) {
                //POWERED BY LINK//
                $app = JFactory::getApplication();
                //$jConfig  = JFactory::getConfig();
                //$siteName = $jConfig->getValue( 'sitename' );
                $siteName = JFactory::getConfig()->get('sitename');
                $powerBy = 'Powered by <a href="http://www.jomsocial.com/">JomSocial</a> for <a href="' . JURI::root() . '">' . $siteName . '</a>';
            }

            return $powerBy;
        }

        /**
         * Replace all _QQQ_ to "
         * Language file now uses new _QQQ_ to maintain Joomla 1.6 compatibility
         */
        public static function quote($str) {
            $str = str_replace('_QQQ_', '"', $str);
            return $str;
        }

        /**
         *
         * @param type $app
         * @param type $data
         * @return \CTemplate
         */
        public function setMetaTags($app, $data) {
            $document = JFactory::getDocument();
            $config = CFactory::getConfig();

            $description = '';
            $groupName = '';

            if (isset($data->description)) {
                $description = strip_tags($data->description);
            }

            switch ($app) {
                case 'event' :
                    $description = JHTML::_('string.truncate', CStringHelper::escape($description), $config->getInt('streamcontentlength'));
                    $document->addHeadLink($data->getThumbAvatar(), 'image_src', 'rel');
                    /* set head meta */
                    CHeadHelper::setTitle(JText::sprintf('COM_COMMUNITY_EVENT_PAGE_TITLE', $data->title));
                    break;
                case 'video' :
                    $description = JHTML::_('string.truncate', CStringHelper::escape($description), $config->getInt('streamcontentlength'));
                    $document->setMetaData('medium', 'video');
                    $document->addHeadLink($data->getThumbnail(), 'image_src', 'rel'); //cannot exceed 130x110 pixels (facebook)
                    /* set head meta */
                    CHeadHelper::setTitle($data->title);
                    break;
                case 'group' :

                    $data->title = $data->name;
                    $pageTitle = JText::sprintf('COM_COMMUNITY_GROUP_PAGE_TITLE', $data->name);
                    if($data->approvals == COMMUNITY_PRIVATE_GROUP) {
                        $pageTitle .= ' (' . JText::_('COM_COMMUNITY_GROUPS_PRIVATE') . ')';
                        $data->title .= ' (' . JText::_('COM_COMMUNITY_GROUPS_PRIVATE') . ')';
                    }
                    $description = JText::sprintf('COM_COMMUNITY_GROUP_META_DESCRIPTION', CStringHelper::escape($data->name), $config->get('sitename'), CStringHelper::escape($description));
                    $document->addHeadLink($data->getThumbAvatar(), 'image_src', 'rel');
                    /* set head meta */



                    CHeadHelper::setTitle($pageTitle);
                    break;
                default :
                    $description = JHTML::_('string.truncate', CStringHelper::escape($description), $config->getInt('streamcontentlength'));
                    CHeadHelper::setTitle($data->title); // JDocument will perform htmlspecialchars escape
            }

            $document->setMetaData('title', CStringHelper::escape($data->title)); // hack the above line
            CHeadHelper::setDescription($description);
            // Return this object
            return $this;
        }

        /**
         *
         * @param type $obj
         * @return array
         */
        public function object_to_array($obj) {
            $_arr = is_object($obj) ? get_object_vars($obj) : $obj;
            $arr = array();
            foreach ($_arr as $key => $val) {
                $val = (is_array($val) || is_object($val)) ? $this->object_to_array($val) : $val;
                $arr[$key] = $val;
            }
            return $arr;
        }

    }

}
