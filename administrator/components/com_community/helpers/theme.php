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

class CommunityThemeHelper
{

    private $matches;

    /**
     * Handles storage of SCSS related POST data into SCSS overrides file and JSON encoded database entries
     * @param $postScss - array of SCSS POST data
     * @param $saveKey - which primary storage key within JSON structure is used
     */
    public static function parseScss($postScss, $saveKey)
    {
        $mainframe = JFactory::getApplication();
        $saveKeyUp = strtoupper($saveKey);

        // String for SCSS override file
        $scssString = '';

        // Array for JSON storage
        $scssArray = array();

        $safeHtmlFilter = CFactory::getInputFilter();

        foreach ($postScss as $key => $value) {

            if (!strlen($key)) continue;
            unset($color);

            // Build result array
            if (strlen($value)) {
                $key = $safeHtmlFilter->clean($key);
                $value = $safeHtmlFilter->clean($value);
                $scssArray[$key] = $value;
            }

            if ($value != '') {
                $scssString .= "\n" . '$' . $key . ": ";
                if ($saveKey == 'colors') $scssString .= "#";
                $scssString .= "$value;";
            }
        }

        // Store SCSS override JSON encoded in the database
        $themeTable = JTable::getInstance('Theme', 'CommunityTable');
        $themeTable->load('scss');
        $themeTable->key = 'scss'; // needed for new record

        if (strlen($themeTable->value)) {
            $oldScss = json_decode($themeTable->value, true);
        } else {
            // Fallback if the JSON settings don't exist yet
            $oldScss = array(
                'colors' => array(),
                'general' => array(),
            );
        }

        $oldScss[$saveKey] = $scssArray;

        $themeTable->value = json_encode($oldScss);

        $themeTable->store();

        // Find the _variables.scss
        $variablesFile = CFactory::getPath('template://scss/_variables.scss');
        jimport('joomla.file');

        // FAIL if it doesn't exist
        if (!strlen($variablesFile) || !JFile::exists($variablesFile)) {
            $message = JText::_('COM_COMMUNITY_THEME_' . $saveKeyUp . '_COULD_NOT_COMPILE_SCSS');
            $mainframe->redirect('index.php?option=com_community&view=theme' . $saveKey, $message, 'message');
        }

        // Read the contents of the file
        $variablesContent = file_get_contents($variablesFile);

        // Find the beginninng and end of the overrides area
        $startTag = '//OVERRIDES_' . $saveKeyUp . '_START';
        $endTag = '//OVERRIDES_' . $saveKeyUp . '_END';

        $overrideStart = strpos($variablesContent, $startTag);
        $overrideEnd = strpos($variablesContent, $endTag);

        // FAIL if can't find both
        if (!is_int($overrideStart) || !is_int($overrideEnd) || !$overrideStart > 0 || !$overrideEnd > 0) {
            $message = JText::_('COM_COMMUNITY_THEME_' . $saveKeyUp . 'S_COULD_NOT_COMPILE_SCSS');
            $mainframe->redirect('index.php?option=com_community&view=theme' . $saveKey, $message, 'message');
        }

        // Exctract the file contents before existing overrides
        $variablesHead = substr($variablesContent, 0, $overrideStart);

        // Extract the file contents after existing overrides
        $variablesTail = substr($variablesContent, $overrideEnd + strlen($endTag), strlen($variablesContent) - $overrideEnd);

        // Stitch the Head, new SCSS, and Tail together, effectively getting rid of old SCSS overrides (if any)
        $variablesNew =
            $variablesHead . "\n" .
            $startTag .
            $scssString . "\n" .
            $endTag . "\n" .
            $variablesTail;

        // Write to file
        JFile::write($variablesFile, $variablesNew);

        // Run the compiler
        $compiler = new CCompiler();
        $compiler = $compiler->buildSCSS();
        $compiler->saveCSSFile(JFactory::getApplication()->input->get('theme_profile', 'style'));

        /************** SCSS DIRECTION OVERRIDES HAPPEN HERE ***************/
        $variablesContent = $variablesNew;
        // Find the beginninng and end of the RTL overrides area
        $startTag = '//OVERRIDES_RTL_START';
        $endTag = '//OVERRIDES_RTL_END';

        $overrideStart = strpos($variablesContent, $startTag);
        $overrideEnd = strpos($variablesContent, $endTag);

        // FAIL if can't find both
        if (!is_int($overrideStart) || !is_int($overrideEnd) || !$overrideStart > 0 || !$overrideEnd > 0) {
            $message = JText::_('COM_COMMUNITY_THEME_RTL_COULD_NOT_COMPILE_SCSS');
            $mainframe->redirect('index.php?option=com_community&view=theme' . $saveKey, $message, 'message');
        }

        // Exctract the file contents before existing overrides
        $variablesHead = substr($variablesContent, 0, $overrideStart);

        // Extract the file contents after existing overrides
        $variablesTail = substr($variablesContent, $overrideEnd + strlen($endTag), strlen($variablesContent) - $overrideEnd);

        /********************** LTR ********************/
        // Stitch the Head, Direction, and Tail together, adding LTR
        $variablesNewLTR =
            $variablesHead . "\n" .
            $startTag . "\n" .
            '$scss-direction: ltr;'. "\n" .
            $endTag . "\n" .
            $variablesTail;

        // Write to file
        JFile::write($variablesFile, $variablesNewLTR);
        // Run the compiler
        $compiler = new CCompiler();
        $compiler = $compiler->buildSCSS();
        $compiler->saveCSSFile(JFactory::getApplication()->input->get('theme_profile', 'style'));

        /********************** RTL ********************/
        // Stitch the Head, Direction, and Tail together, adding RTL
        $variablesNewRTL =
            $variablesHead . "\n" .
            $startTag . "\n" .
            '$scss-direction: rtl;'. "\n" .
            $endTag . "\n" .
            $variablesTail;

        // Write to file
        JFile::write($variablesFile, $variablesNewRTL);
        // Run the compiler
        $compiler = new CCompiler();
        $compiler = $compiler->buildSCSS();
        $compiler->saveCSSFile(JFactory::getApplication()->input->get('theme_profile', 'style.rtl'));
    }

    public static function parseSettings($settings, $saveKey)
    {
        $settingsArray = array();
        $safeHtmlFilter = CFactory::getInputFilter();

        foreach ($settings as $key => $value) {

            if (!strlen($key)) continue;

            // Build result array
            if (strlen($value)) {
                $key = $safeHtmlFilter->clean($key);
                $value = $safeHtmlFilter->clean($value);
                $settingsArray [$key] = $value;
            }
        }

        // Store SCSS override JSON encoded in the database
        $themeTable = JTable::getInstance('Theme', 'CommunityTable');
        $themeTable->load('settings');
        $themeTable->key = 'settings'; // needed for new record

        if (strlen($themeTable->value)) {
            $oldSettings = json_decode($themeTable->value, true);
        } else {
            $oldSettings = array(
                'profile' => array(),
                'general' => array(),
            );
        }

        $oldSettings[$saveKey] = $settingsArray;

        $themeTable->value = json_encode($oldSettings);
        $themeTable->store();
    }


    // Cover info parser
    public function prepareCoverInfo(&$settings)
    {


        for($key=0;$key<10;$key++) {
            if(!isset($settings['profileSpaceBefore'.$key])) break;
            $spacebefore = $settings['profileSpaceBefore'.$key];
            unset($settings['profileSpaceBefore'.$key]);

            $before = $settings['profileBefore'.$key];
            unset($settings['profileBefore'.$key]);

            $field = $settings['profileField'.$key];
            unset($settings['profileField'.$key]);

            $after = $settings['profileAfter'.$key];
            unset($settings['profileAfter'.$key]);

            $spaceafter = $settings['profileSpaceAfter'.$key];
            unset($settings['profileSpaceAfter'.$key]);

            $profileFields[] = array (
                'spacebefore' => $spacebefore,
                'before' => $before,
                'field' => $field,
                'after' => $after,
                'spaceafter' => $spaceafter,
            );
        }

        return json_encode($profileFields);
    }

    public static function getDefault($key)
    {
        /*
         * This is where we store our own defaults
         * in case the template file is broken or otherwise not available
         */
        $defaultSettings = JPATH_ROOT.'/components/com_community/templates/jomsocial/assets/default.' . $key . '.json';

        /*
         * Attempt to read and parse template settings
         */
        $templateSettings = CFactory::getPath('template://assets/default.' . $key . '.json');
        jimport('joomla.file');

        // If the file exists, and contains parseable JSON data
        if (strlen($templateSettings) &&  JFile::exists($templateSettings)) {

            $json = file_get_contents($templateSettings);

            if(strlen($json)) {
                $settings = json_decode($json, true);
            }
        }

        if(!isset($settings) || !is_array($settings)) {
            return json_decode(file_get_contents($defaultSettings), true);
        }

        return $settings;
    }

    public static function licenseViewDisable()
    {
        if (COMMUNITY_PRO_VERSION) {
            return '<div class="feature--disabled">
                <h3>'.JText::_('COM_COMMUNITY_FEATURE_DISABLED_TITLE').'</h3>
                <p>'.JText::_('COM_COMMUNITY_FEATURE_DISABLED_DESC').'</p>
                <div class="space-16"></div>
                <a href="http://tiny.cc/kwk0px" class="btn btn-primary">'.JText::_('COM_COMMUNITY_BUY').'</a>
                <a href="http://tiny.cc/cyk0px" class="btn btn-success">'.JText::_('COM_COMMUNITY_UPGRADE').'</a>
            </div>';
        }
    }

}