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
if (!class_exists('CCompiler')) {


    /**
     * SCSS & Javascript complier
     */
    class CCompiler extends cobject {

        /**
         * Template assets object
         * @var object 
         */
        protected $_assets = null;

        /**
         * scssphp class object
         * @var object
         */
        protected $_scssphp = null;

        /**
         * Compiled scss into css
         * @var string
         */
        protected $_scss = '';

        /**
         * Adding scss
         * @var string
         */
        protected $_extraSCSS = '';

        /**
         * Compiled SCSS to CSS
         * @var string
         */
        protected $_css;

        /**
         * Constructor
         */
        public function __construct() {
            $scssphpFile = CFactory::getPath('jomsocial://libraries/vendor/scssphp/scss.inc.php');
            if ($scssphpFile)
                require_once $scssphpFile;
            $this->_scssphp = new scssc();
            $this->load('template://assets/build.json');
        }

        /**
         * Load build configuration
         * @param string $key
         */
        public function load($key) {
            /* Load build config */
            $assetsFile = CFactory::getPath($key);
            if ($assetsFile) {
                $this->_assets = json_decode(file_get_contents($assetsFile));
            }
            /**
             * http://leafo.net/scssphp/docs/#including
             * Add import path that will use for @import in scss files
             */
            $this->_scssphp->addImportPath(CFactory::getPath('template://scss'));
            foreach ($this->_assets->scss as $scssFile) {
                $scssFile = CFactory::getPath('template://scss/' . $scssFile);
                if ($scssFile) {
                    $buffer = file_get_contents($scssFile);
                    $this->_scss .= $buffer;
                }
            }
        }

        /**
         * Build css from provided scss file in template assets
         */
        public function buildSCSS() {
            /* Generate SCSS variables from properties */
            $properties = $this->getProperties();
            foreach ($properties as $key => $value) {
                if (is_string($value) && trim($value) != '')
                    $this->addSCSS('$' . $key . ": '" . $value . "';");
            }
            if (class_exists('scssc')) {
                $this->_css = $this->_scssphp->compile($this->_scss . "\n\r" . $this->_extraSCSS);
            }
            /* Do compress if required */
            $config = CFactory::getConfig();
            if ($config->get('compiler_css_compress', 0) == 1) {
                $cssMinFile = CFactory::getPath('jomsocial://libraries/vendor/cssmin/cssmin.php');
                if ($cssMinFile) {
                    require_once $cssMinFile;
                    $cssMin = new CSSmin();
                    $this->_css = $cssMin->run($this->_css);
                }
            }
            return $this;
        }

        /**
         * Adding extra SCSS to override assets
         * @param type $content
         */
        public function addSCSS($content) {
            $this->_extraSCSS .= "\n" . $content;
        }

        /**
         * Write compiled css into css file
         * @param string $fileName
         * @return boolean
         */
        public function saveCSSFile($fileName) {
            $templateAssetsDir = CFactory::getPath('template://assets/css');
            if ($templateAssetsDir) {
                $cssFile = $templateAssetsDir . '/' . $fileName . '.css';
                return JFile::write($cssFile, $this->_css);
            }
            return false;
        }

    }

}