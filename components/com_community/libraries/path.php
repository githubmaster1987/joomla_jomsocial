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

/**
 * Class exists checking
 */
if (!class_exists('CPath')) {

    /**
     * CPath libraries
     * @uses Used to work with file / directory path with friendly format <namespace>://path
     * @uses It also used for overriden. To do override just register same namespace with different path
     */
    class CPath {

        /**
         * Array of namespaces
         * @var array
         */
        protected $_namespaces = array();

        /**
         * Do not allow construct directly
         */
        protected function __construct() {
            $this->_init();
        }

        /**
         *
         * @staticvar CPath $instances
         * @param type $name
         * @return \CPath
         */
        public static function &getInstance($name = 'jomsocial') {
            static $instances;
            if (!isset($instances[$name])) {
                $instances[$name] = new CPath();
            }
            return $instances[$name];
        }

        /**
         * Init default namespace
         */
        protected function _init() {
            $this->registerNamespace('jomsocial', JPATH_ROOT . '/components/com_community');
            /* Templates */
            $this->registerNamespace('template', JPATH_ROOT . '/components/com_community/templates/jomsocial'); /* JomSocial template */
            $this->registerNamespace('template', JPATH_ROOT . '/components/com_community/templates/' . CFactory::getConfig()->get('template', 'default')); /* JomSocial template */
            $this->registerNamespace('template', JPATH_ROOT . '/templates/' . JFactory::getApplication()->getTemplate() . '/html/com_community');
            /* Assets */
            $this->registerNamespace('assets', JPATH_ROOT . '/administrator/components/com_community/assets');
            $this->registerNamespace('assets', JPATH_ROOT . '/components/com_community/assets'); /* JomSocial assets */
            $this->registerNamespace('assets', JPATH_ROOT . '/administrator/templates/' . JFactory::getApplication()->getTemplate() . '/html/com_community/assets');


        }

        /**
         * Register namespace and path
         * @param type $namespace
         * @param type $path
         * @return \CPath
         */
        public function registerNamespace($namespace, $path) {
            if (!isset($this->_namespaces[$namespace])) {
                $this->_namespaces[$namespace] = array();
            }
            array_unshift($this->_namespaces[$namespace], $path);
            return $this;
        }

        /**
         * Get physical path ( folder or file )
         * @param type $key
         * @param type $showError
         * @return boolean|string
         */
        public function getPath($key, $showError = false) {
            /* Extract key to get namespace and path */
            $parts = explode('://', $key);
            if (is_array($parts) && count($parts) == 2) {
                $namespace = $parts[0];
                $path = $parts[1];
                /* Make sure this namespace is registered */
                if (isset($this->_namespaces[$namespace])) {
                    /* Find first exists filePath */
                    foreach ($this->_namespaces[$namespace] as $namespace) {
                        $physicalPath = $namespace . '/' . $path;
                        if (JFile::exists($physicalPath)) {
                            return str_replace('/', DIRECTORY_SEPARATOR, $physicalPath);
                        } elseif (JFolder::exists($physicalPath)) {
                            return str_replace('/', DIRECTORY_SEPARATOR, $physicalPath);
                        }
                    }
                }
            }
            if ($showError)
                JFactory::getApplication()->enqueueMessage('Path not found: ' . $key, 'error');
            return false;
        }

        /**
         *
         * @param type $key
         * @return string|boolean
         */
        public function getUrl($key) {
            /* Extract key to get namespace and path */

            $parts = explode('://', $key);
            if (is_array($parts) && count($parts) == 2) {
                $namespace = $parts[0];
                $path = $parts[1];
                /* Make sure this namespace is registered */
                if (isset($this->_namespaces[$namespace])) {
                    /* Find first exists filePath */
                    foreach ($this->_namespaces[$namespace] as $namespace) {
                        $realPath = $namespace . '/' . $path;
                        if (JFile::exists($realPath)) {
                            return $this->toUrl($realPath);
                        } elseif (JFolder::exists($realPath)) {
                            return $this->toUrl($realPath);
                        }
                    }
                }
            }
            return false;
        }

        /**
         * Convert physical path to URL
         * @param type $path
         * @return type
         */
        public function toUrl($path) {
            // http://stackoverflow.com/questions/1252693/using-str-replace-so-that-it-only-acts-on-the-first-match
            if (JPATH_ROOT != '') {
                $path = implode('', explode(JPATH_ROOT, $path, 2));
            }
            return JUri::root(true) . str_replace('\\', '/', $path);
        }

    }

}
