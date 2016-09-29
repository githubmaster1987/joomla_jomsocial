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

jimport( 'joomla.application.component.view' );

/**
 * Configuration view for JomSocial
 */
class CommunityViewThemecolors extends JViewLegacy
{
    public function display( $tpl = null )
    {
        // Set the titlebar text
        JToolBarHelper::title( JText::_('COM_COMMUNITY_CONFIGURATION_THEME_COLORS'), 'colors');
        JToolBarHelper::apply();
        JToolBarHelper::cancel();
        JToolBarHelper::custom('reset','','',JText::_('COM_COMMUNITY_THEME_COLORS_RESET'),false);
        // Get Moods by type (preset & custom)
        $scssTable= JTable::getInstance( 'Theme' , 'CommunityTable' );
        $this->set('scss', $scssTable->getByKey('scss'));
        $this->set('scss_default', CommunityThemeHelper::getDefault('scss'));

        parent::display( $tpl );
    }

    /**
     * @param $key
     *
     * Render a text field for key, prefill with value from $this->scss
     */
    public function renderField($key) {

        $isDefault = false;

        // if the key is empty, load from the defaults
        if(!isset($this->scss['colors'][$key]) && isset($this->scss_default['colors'][$key])) {
            $this->scss['colors'][$key] = $this->scss_default['colors'][$key];
            $isDefault = true;
        }

        // if the value is identical as defaults
        if(isset($this->scss['colors'][$key]) && isset($this->scss_default['colors'][$key])) {
            if($this->scss['colors'][$key] == $this->scss_default['colors'][$key]) $isDefault = true;
        }

        // if both values are empty, means there is no default
        if(!isset($this->scss['colors'][$key]) && !isset($this->scss_default['colors'][$key])) {
            $isDefault=true;
        }
        ?>
        <input type="hidden" class="default" id="default-<?php echo $key;?>" value="<?php echo (isset($this->scss_default['colors'][$key])) ? $this->scss_default['colors'][$key] : '';?>" />
        <input type="text" maxlength="6" value="<?php

        echo isset($this->scss['colors'][$key]) ? $this->scss['colors'][$key] : "";

        ?>"  id="<?php

        echo $key;

        ?>" name="scss[<?php

        echo $key;

        ?>]" class="color resettable {required:false}">

            <a href="#"
               class="reset"
               id="reset-<?php echo $key;?>"
               style="display:<?php echo ($isDefault) ? "none" : "inline"; ?>">
                <?php echo JText::_('COM_COMMUNITY_THEME_COLORS_RESET_FIELD'); ?>
            </a>
    <?php
    }
}