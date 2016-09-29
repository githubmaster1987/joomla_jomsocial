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

jimport('joomla.filesystem.file');

if (!class_exists('CAssets')) {

    /**
     * Global Asset manager
     */
    class CAssets {

        /**
         * Construct
         * @param type $name
         */
        protected function __construct($name = 'default') {
            $this->_init($name);
        }

        /**
         *
         * @staticvar CPath $instances
         * @param type $name
         * @return \CPath
         */
        public static function &getInstance($name = 'default') {
            static $instances;
            if (!isset($instances[$name])) {
                $instances[$name] = new CAssets();
            }
            return $instances[$name];
        }

        /**
         * Centralized location to attach asset to any page. It avoids duplicate
         * attachement
         * @staticvar boolean $added
         * @param type $path
         * @param type $type
         * @param type $assetPath
         * @return type
         */
        public function attach($path, $type, $assetPath = '') {
            $document = JFactory::getDocument();
            if ($document->getType() != 'html')
                return;

            if (!empty($assetPath)) {
                $path = $assetPath . $path;
            } else {
                $path = JURI::root(true) . '/components/com_community/' . JString::ltrim($path, '/');
            }

            switch ($type) {
                case 'js':
                    $document->addScript($path);
                    break;
                case 'css':
                    //do not attach style.css if current direction is rtl (style.rtl is loaded from views/view)
                    if($document->direction == 'rtl' && strpos($path,'style.css') !== false){
                        break;
                    }
                    $document->addStyleSheet($path);
                    break;
            }
        }

        /**
         * Init assets
         * @param type $name
         */
        public function _init($name) {
            $document = JFactory::getDocument();
            if ($document->getType() == 'html') {
                $document->addScriptDeclaration("joms_base_url = '" . JURI::root() . "';");
                $document->addScriptDeclaration("joms_assets_url = '" . JURI::root(true) . "/components/com_community/assets/';");
                // Legacy script relative path.
                $document->addScriptDeclaration("joms_script_url = '" . JURI::root(true) . "/components/com_community/assets/_release/js/';");
                // Language translation.
                $this->_loadLanguageTranslation();
                // Print IDs.
                $my = CFactory::getUser();
                $userid = JFactory::getApplication()->input->get('userid', '', 'INT');
                $user = CFactory::getUser($userid);
                $document->addScriptDeclaration('joms_my_id = ' . $my->id . ';');
                $document->addScriptDeclaration('joms_user_id = ' . $user->id . ';');
            }

            //embedly card loader, included this everywhere if enabled
            if(CFactory::getConfig()->get('enable_embedly')){
                $document->addScript('//cdn.embedly.com/widgets/platform.js');
            }

            // $this->attach('assets/joms.jquery-1.8.1.min.js', 'js');
            // $this->attach('assets/script-1.2.js', 'js');
            /* Assets init */
            $assetFile = CFactory::getPath('assets://default.json');
            if ($assetFile) {
                $assets = json_decode(file_get_contents($assetFile));
                foreach ($assets->core->css as $css) {
                    $cssFile = CFactory::getPath('assets://' . $css . '.css');
                    if ($cssFile) {
                        $this->attach(basename($css) . '.css', 'css', CPath::getInstance()->toUrl(dirname($cssFile)) . '/');
                    }
                }
                foreach ($assets->core->js as $js) {
                    $jsFile = CFactory::getPath('assets://' . $js . '.js');
                    if ($jsFile) {
                        $this->attach(basename($js) . '.js', 'js', CPath::getInstance()->toUrl(dirname($jsFile)) . '/');
                    }
                }
            }

            if (JFactory::getApplication()->isSite()) {
                /* Template init */
                $lang = JFactory::getLanguage();
                $templateFile = CFactory::getPath('template://assets/' . $name . '.json');


                if ($templateFile) {
                    $assets = json_decode(file_get_contents($templateFile));
                    /* Load template core files */
                    foreach ($assets->core->css as $css) {
                        $cssFile = CFactory::getPath('template://assets/css/' . $css . '.css');
                        if ($cssFile) {
                            $this->attach(basename($css) . '.css', 'css', CPath::getInstance()->toUrl(dirname($cssFile)) . '/');
                        }
                    }
                    foreach ($assets->core->js as $js) {
                        $jsFile = CFactory::getPath('template://assets/js/' . $js . '.js');
                        if ($jsFile) {
                            $this->attach(basename($js) . '.js', 'js', CPath::getInstance()->toUrl(dirname($jsFile)) . '/');
                        }
                    }
                    /* Load template view files */
                    $view = JFactory::getApplication()->input->getWord('view');
                    if (isset($assets->views->$view)) {
                        if (isset($assets->views->$view->css)) {
                            foreach ($assets->views->$view->css as $css) {
                                $cssFile = CFactory::getPath('template://assets/css/view.' . $css . '.css');
                                if ($cssFile) {
                                    $this->attach('view.' . basename($css) . '.css', 'css', CPath::getInstance()->toUrl(dirname($cssFile)) . '/');
                                }
                            }
                        }
                    }
                    if (isset($assets->views->$view)) {
                        if (isset($assets->views->$view->js)) {
                            foreach ($assets->views->$view->js as $js) {
                                $jsFile = CFactory::getPath('template://assets/js/view.' . $js . '.js');
                                if ($jsFile) {
                                    $this->attach('view.' . basename($js) . '.js', 'js', CPath::getInstance()->toUrl(dirname($jsFile)) . '/');
                                }
                            }
                        }
                    }
                }
            }
        }

        protected function _loadLanguageTranslation() {
            $languages = array(
                'COM_COMMUNITY_PHOTO_DONE_TAGGING',
                'COM_COMMUNITY_SEARCH',
                'COM_COMMUNITY_NO_COMMENTS_YET',
                'COM_COMMUNITY_NO_LIKES_YET',
                'COM_COMMUNITY_SELECT_ALL',
                'COM_COMMUNITY_UNSELECT_ALL',
                'COM_COMMUNITY_SHOW_MORE',
                'COM_COMMUNITY_SHOW_LESS',
                'COM_COMMUNITY_FILES_LOAD_MORE',
                'COM_COMMUNITY_INVITE_LOAD_MORE',
                'COM_COMMUNITY_PRIVACY_PUBLIC',
                'COM_COMMUNITY_PRIVACY_SITE_MEMBERS',
                'COM_COMMUNITY_PRIVACY_FRIENDS',
                'COM_COMMUNITY_PRIVACY_ME',
                'COM_COMMUNITY_MOVE_TO_ANOTHER_ALBUM',
                'COM_COMMUNITY_POPUP_LOADING',
                'COM_COMMUNITY_CLOSE_BUTTON',
                'COM_COMMUNITY_SELECT_FILE',
                'COM_COMMUNITY_AUTHENTICATION_KEY',
                'COM_COMMUNITY_NEXT',
                'COM_COMMUNITY_SKIP_BUTTON',
                'COM_COMMUNITY_AUTHENTICATION_KEY_LABEL',
                'COM_COMMUNITY_NO_RESULT_FOUND',
                'COM_COMMUNITY_OF',
                'COM_COMMUNITY_EDITING_GROUP',
                'COM_COMMUNITY_CHANGE_GROUP_OWNER',
                'COM_COMMUNITY_CONFIGURATION_IMPORT_GROUPS',
                'COM_COMMUNITY_CONFIGURATION_IMPORT_USERS',
                'COM_COMMUNITY_EDITING_PHOTO',
                'COM_COMMUNITY_VIEW_PHOTO',
                'COM_COMMUNITY_EDITING_VIDEO',
                'COM_COMMUNITY_VIEW_VIDEO',
                'COM_COMMUNITY_SHOW_PREVIOUS_COMMENTS',
            );

            $translation = array();
            for ( $i = 0; $i < count( $languages ); $i++ ) {
                $translation[ $languages[$i] ] = JText::_( $languages[$i] );
            }

            // Rich editor translation.
            $translation['wysiwyg'] = array(
                'viewHTML' => JText::_('COM_COMMUNITY_EDITOR_VIEW_HTML'),
                'bold' => JText::_('COM_COMMUNITY_EDITOR_BOLD'),
                'italic' => JText::_('COM_COMMUNITY_EDITOR_ITALIC'),
                'underline' => JText::_('COM_COMMUNITY_EDITOR_UNDERLINE'),
                'orderedList' => JText::_('COM_COMMUNITY_EDITOR_ORDERED_LIST'),
                'unorderedList' => JText::_('COM_COMMUNITY_EDITOR_UNORDERED_LIST'),
                'link' => JText::_('COM_COMMUNITY_EDITOR_LINK'),
                'createLink' => JText::_('COM_COMMUNITY_EDITOR_INSERT_LINK'),
                'unlink' => JText::_('COM_COMMUNITY_EDITOR_REMOVE_LINK'),
                'image' => JText::_('COM_COMMUNITY_EDITOR_IMAGE'),
                'insertImage' => JText::_('COM_COMMUNITY_EDITOR_INSERT_IMAGE'),
                'description' => JText::_('COM_COMMUNITY_EDITOR_DESCRIPTION'),
                'title' => JText::_('COM_COMMUNITY_EDITOR_TITLE'),
                'text' => JText::_('COM_COMMUNITY_EDITOR_TEXT'),
                'submit' => JText::_('COM_COMMUNITY_CONFIRM'),
                'reset' => JText::_('COM_COMMUNITY_CANCEL')
            );

            $document = JFactory::getDocument();
            $document->addScriptDeclaration('joms_lang = ' . json_encode( $translation ) . ';');
        }

    }

}
