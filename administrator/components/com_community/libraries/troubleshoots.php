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
if (!class_exists('CTroubleshoots')) {

    /**
     * Troubleshoot library
     */
    class CTroubleshoots {

        /**
         *
         * @var array 
         */
        public $extensions = array();

        /**
         *
         * @var array 
         */
        public $hashList = array();

        /**
         * @todo Should we init scan and store list of files than just reuse it ?
         */
        public function __construct() {
            $communityPlugins['community'] = array(
                'allvideo',
                'editormyphotos',
                'events',
                'feeds',
                'friendslocation',
                'groups',
                'icontact',
                'input',
                'inputlink',
                'invite',
                'jsnote',
                'kunena',
                'kunenagroups',
                'kunenamenu',
                'latestphoto',
                'log',
                'myarticles',
                'myblog',
                'myblogtoolbar',
                'mycontacts',
                'mygoogleads',
                'mykunena',
                'mytaggedvideos',
                'myvideos',
                'nicetalk',
                'system',
                'twitter',
                'walls',
                'wordfilter',
                'mutualfriends',
                'myfriends',
                'mygroups'
            );
            $communityPlugins['content'] = array(
                'groupdiscuss',
                'jomsocial_fb_comments',
                'jomsocial_fb_likes'
            );
            $communityPlugins['editors-xtd'] = array(
                'myphotos'
            );
            $communityPlugins['kunena'] = array(
                'community'
            );
            $communityPlugins['system'] = array(
                'jomsocial.system',
                'jomsocial',
                'jomsocialconnect',
                'jomsocialinprofile',
                'jomsocialredirect',
                'jomsocialupdate'
            );
            $communityPlugins['user'] = array(
                'jomsocialuser',
                'registeractivity'
            );
            $this->extensions['plugins'] = $communityPlugins;
            $this->hashList = $this->getHash();
        }

        public function coreFilesCheck($path, $level = 0) {
            echo '<ul class="unstyled">';

            foreach($this->hashList as $subPath=>$hash){

                $fullpath = realpath(JPATH_ROOT.$subPath);

                if(substr_count($fullpath,realpath($path)) == 0){
                    continue;
                }

                if(file_exists($fullpath)){
                    //compare the hash if file exists
                    $currentHash = md5_file($fullpath);

                    /* Exception case for PRO / DEV version */
                    if (strpos($subPath, 'defines.community.php') !== false) {
                        $content = file_get_contents($fullpath);
                        $content = str_replace("define('COMMUNITY_PRO_VERSION', true);", "define('COMMUNITY_PRO_VERSION', false);", $content);
                        $currentHash = md5($content);
                    } elseif (strpos($subPath, 'community_version.php') !== false) {
                        /* Another exception */
                        $content = file_get_contents($fullpath);
                        $content = str_replace('define("COMMUNITY_INSTALLER_VERSION", "std");', 'define("COMMUNITY_INSTALLER_VERSION", "@jomversion@");', $content);
                        $content = str_replace('define("COMMUNITY_INSTALLER_VERSION", "pro");', 'define("COMMUNITY_INSTALLER_VERSION", "@jomversion@");', $content);
                        $content = str_replace('define("COMMUNITY_INSTALLER_VERSION", "dev");', 'define("COMMUNITY_INSTALLER_VERSION", "@jomversion@");', $content);
                        $currentHash = md5($content);
                    }

                    if($currentHash != $hash){
                        echo '<li>' . $subPath . '<small> ' . '<span class="label label-important">' . JText::_('COM_COMMUNITY_TROUBLESHOOTS_MODIFIED') . '</span></small></li>';
                    }
                }
            }


            echo '</ul>';
        }

        public function filesCheck($path = '.', $level = 0, $showTree = false) {

            if ($level == 0) {
                echo '<ul class="unstyled">';
            }
            $excludeDirs = array(
                '.hg',
                'bin',
                'build',
                'cache',
                'cli',
                'images',
                'language',
                'logs',
                'nbproject',
                'patches',
                'tmp',
                'tools',
                'unittest'
            );
            $di = new DirectoryIterator($path);
            foreach ($di as $child) {
                if (!$child->isDot()) {
                    $fileName = $child->getBasename();
                    if ($child->isDir() && !in_array($fileName, $excludeDirs)) {
                        /* We don't need to check into these directories */
                        $this->filesCheck($child->getPathname(), $level++);
                    } elseif ($child->isFile()) {
                        $ext = strtolower($child->getExtension());
                        /* Only need to check for php & js files */
                        if ($ext == 'php' || $ext == 'js') {
                            $filePath = str_replace('\\', '/', trim(str_replace(JPATH_ROOT, '', $child->getPathname())));
                            $content = file_get_contents($child->getPathname());
                            if (strpos($content, 'window.jQuery = window.$ = jQuery;') !== false) {
                                echo '<li class="warning">' . $filePath . ' <small><span class="label label-warning">' . JText::_('COM_COMMUNITY_TROUBLESHOOTS_JQUERY_DETECTED') . '</span></small></i></li>';
                            }
                        }
                    }
                }
            }
            if ($level == 0)
                echo '</ul>';
        }

        /**
         * @todo Read one time and store in as private variable to prevent so many read times
         * @staticvar type $list
         * @return type
         */
        public function getHash() {
            static $list;
            if (!isset($list)) {
                $content = file_get_contents(JPATH_COMPONENT_ADMINISTRATOR . '/hash.ini');
                $array = explode("\n", $content);
                foreach ($array as $el) {
                    $parts = explode('=', $el);
                    if (count($parts) == 2) {
                        $list[trim($parts[0])] = $parts[1];
                    }
                }
            }
            return $list;
        }

        /**
         *
         * @return type
         */
        public function getCommunityPlugins() {
            $db = JFactory::getDbo();
            $query = $db->getQuery(true)
                    ->select('*')
                    ->from('#__extensions')
                    ->where('type =' . $db->quote('plugin'))
                    ->where(' (
                        folder = ' . $db->quote('community')
                            . ' OR folder = ' . $db->quote('content')
                            . ' OR folder = ' . $db->quote('editors-xtd')
                            . ' OR folder = ' . $db->quote('kunena')
                            . ' OR folder = ' . $db->quote('system')
                            . ' OR folder = ' . $db->quote('user')
                            . ' ) ')
                    ->order('folder')
                    ->order('element')
                    ->order('ordering');
            $db->setQuery($query);
            return $db->loadObjectList();
        }

        /**
         * @todo Load from JSON
         * @return array
         */
        public function getSystemRequirements() {
            $db = JFactory::getDbo();
            $mySQLCheck[] = array(
                'minimum' => '5.0.4',
                'recommended' => '5.0.4 ' . JText::_('COM_COMMUNITY_TROUBLESHOOTS_OR_HIGHER'),
                'current' => array(version_compare($db->getVersion(), '5.0.4') >= 0, $db->getVersion()),
                'description' => JText::_('COM_COMMUNITY_TROUBLESHOOTS_APACHE_PHP_DESCRIPTION'),
                'help' => JText::_('COM_COMMUNITY_TROUBLESHOOTS_APACHE_HELP')
            );
            $phpChecks[] = array(
                'minimum' => '5.3.1',
                'recommended' => '5.3 ' . JText::_('COM_COMMUNITY_TROUBLESHOOTS_OR_HIGHER'),
                'current' => array(version_compare(PHP_VERSION, '5.3') >= 0, phpversion()),
                'description' => JText::_('COM_COMMUNITY_TROUBLESHOOTS_APACHE_PHP_DESCRIPTION'),
                'help' => JText::_('COM_COMMUNITY_TROUBLESHOOTS_PHP_HELP')
            );
            $phpChecks[] = array(
                'minimum' => 'imagecreatefromjpeg',
                'recommended' => 'imagecreatefromjpeg',
                'current' => array(function_exists('imagecreatefromjpeg')),
                'description' => JText::_('COM_COMMUNITY_TROUBLESHOOTS_PHP_IMAGE_DESCRIPTION'),
                'help' => JText::_('COM_COMMUNITY_TROUBLESHOOTS_PHP_GENERAL_HELP')
            );
            $phpChecks[] = array(
                'minimum' => 'imagecreatefrompng',
                'recommended' => 'imagecreatefrompng',
                'current' => array(function_exists('imagecreatefrompng')),
                'description' => JText::_('COM_COMMUNITY_TROUBLESHOOTS_PHP_IMAGE_DESCRIPTION'),
                'help' => JText::_('COM_COMMUNITY_TROUBLESHOOTS_PHP_GENERAL_HELP')
            );
            $phpChecks[] = array(
                'minimum' => 'imagecreatefromgif',
                'recommended' => 'imagecreatefromgif',
                'current' => array(function_exists('imagecreatefromgif')),
                'description' => JText::_('COM_COMMUNITY_TROUBLESHOOTS_PHP_IMAGE_DESCRIPTION'),
                'help' => JText::_('COM_COMMUNITY_TROUBLESHOOTS_PHP_GENERAL_HELP')
            );
            $phpChecks[] = array(
                'minimum' => 'imagecreatefromgd',
                'recommended' => 'imagecreatefromgd',
                'current' => array(function_exists('imagecreatefromgd')),
                'description' => JText::_('COM_COMMUNITY_TROUBLESHOOTS_PHP_IMAGE_DESCRIPTION'),
                'help' => JText::_('COM_COMMUNITY_TROUBLESHOOTS_PHP_GENERAL_HELP')
            );
            $phpChecks[] = array(
                'minimum' => 'imagecreatefromgd2',
                'recommended' => 'imagecreatefromgd2',
                'current' => array(function_exists('imagecreatefromgd2')),
                'description' => JText::_('COM_COMMUNITY_TROUBLESHOOTS_PHP_IMAGE_DESCRIPTION'),
                'help' => JText::_('COM_COMMUNITY_TROUBLESHOOTS_PHP_GENERAL_HELP')
            );
            $curlVersion = curl_version();
            $phpChecks[] = array(
                'minimum' => 'curl',
                'recommended' => 'curl',
                'current' => array(
                    in_array('curl', get_loaded_extensions()),
                    $curlVersion['version'] . '-' . $curlVersion['ssl_version'] . '-' . $curlVersion['libz_version']
                ),
                'description' => JText::_('COM_COMMUNITY_TROUBLESHOOTS_PHP_CURL_DESCRIPTION'),
                'help' => JText::_('COM_COMMUNITY_TROUBLESHOOTS_PHP_CURL_HELP')
            );
            $phpChecks[] = array(
                'minimum' => 'max_execution_time: 30',
                'recommended' => 'max_execution_time: 300',
                'current' => array(ini_get('max_execution_time') >= 30, ini_get('max_execution_time')),
                'description' => JText::_('COM_COMMUNITY_TROUBLESHOOTS_PHP_DESCRIPTION'),
                'help' => JText::_('COM_COMMUNITY_TROUBLESHOOTS_PHP_INI_HELP')
            );
            $phpChecks[] = array(
                'minimum' => 'max_input_time: 30',
                'recommended' => 'max_input_time: 300',
                'current' => array(ini_get('max_input_time') >= 30, ini_get('max_input_time')),
                'description' => JText::_('COM_COMMUNITY_TROUBLESHOOTS_PHP_DESCRIPTION'),
                'help' => JText::_('COM_COMMUNITY_TROUBLESHOOTS_PHP_INI_HELP')
            );
            $phpChecks[] = array(
                'minimum' => 'memory_limit: 128M',
                'recommended' => 'memory_limit: 1024M',
                'current' => array(ini_get('memory_limit') >= 128, ini_get('memory_limit')),
                'description' => JText::_('COM_COMMUNITY_TROUBLESHOOTS_PHP_DESCRIPTION'),
                'help' => JText::_('COM_COMMUNITY_TROUBLESHOOTS_PHP_INI_HELP')
            );
            $phpChecks[] = array(
                'minimum' => 'post_max_size: 8M',
                'recommended' => 'post_max_size: 4096M',
                'current' => array(ini_get('post_max_size') >= 8, ini_get('post_max_size')),
                'description' => JText::_('COM_COMMUNITY_TROUBLESHOOTS_PHP_DESCRIPTION'),
                'help' => JText::_('COM_COMMUNITY_TROUBLESHOOTS_PHP_INI_HELP')
            );
            $phpChecks[] = array(
                'minimum' => 'upload_max_filesize: 8M',
                'recommended' => 'upload_max_filesize: 4096M',
                'current' => array(ini_get('upload_max_filesize') >= 8, ini_get('upload_max_filesize')),
                'description' => JText::_('COM_COMMUNITY_TROUBLESHOOTS_PHP_DESCRIPTION'),
                'help' => JText::_('COM_COMMUNITY_TROUBLESHOOTS_PHP_INI_HELP')
            );
            $systemRequirements['MySQL'] = $mySQLCheck;
            $systemRequirements['PHP'] = $phpChecks;
            return $systemRequirements;
        }

    }

}