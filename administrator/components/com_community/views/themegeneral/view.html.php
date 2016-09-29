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
class CommunityViewThemegeneral extends JViewLegacy
{
    public function display( $tpl = null )
    {
        // Set the titlebar text
        JToolBarHelper::title( JText::_('COM_COMMUNITY_CONFIGURATION_THEME_GENERAL'), 'general');
        JToolBarHelper::apply();
        JToolBarHelper::cancel();
        JToolBarHelper::custom('reset','','',JText::_('COM_COMMUNITY_THEME_GENERAL_RESET'),false);

        // Get Moods by type (preset & custom)
        $scssTable= JTable::getInstance( 'Theme' , 'CommunityTable' );
        $this->set('scss', $scssTable->getByKey('scss'));
        $this->set('settings', $scssTable->getByKey('settings'));

        $defaults = array(
            'scss' => array(
                'scss-style'            => 'boxy',
                'scss-stream-position'  => 'right',
                'scss-button-style'     => 'flat',
                'scss-avatar-shape'     => 'circle',
                'scss-avatar-style'     => 'bordered',
                'scss-direction'        => 'ltr',
            ),
            'settings'=> array(
                'enable-frontpage-login'=> 1,
                'enable-frontpage-image'     => 1,
                'enable-frontpage-paragraph' => 1,
            ),
        );

        $this->set('defaults', $defaults);

        parent::display( $tpl );
    }

    /**
     * @param $key
     * @param $value
     * @param bool $scss
     *
     * @return void
     *
     * Renders a checkbox (radio) for given key and value
     * Prefill with value from $this->scss or $this->settings depending on scss flag
     */
    public function renderCheckbox($key, $value, $scss=true) {
        $checked = false;
        $isDefault = 'resetFalse';

        $setting = "settings";
        if($scss) $setting = "scss";

        if(isset($this->{$setting}['general'][$key]) && $this->{$setting}['general'][$key] == $value) $checked = true;
        if(!isset($this->{$setting}['general'][$key]) && $value===$this->defaults{$setting}[$key]) $checked = true; // default values

        if($value===$this->defaults{$setting}[$key]) $isDefault = 'resetTrue';
        ?>
        <div class="radio">
            <label>
                <input name="<?php echo ($scss) ? "scss" : "settings"   ;?>[<?php echo $key;?>]" id="<?php echo $key;?>" value="<?php echo $value;?>" type="radio" class="ace <?php echo $isDefault;?>" <?php echo $checked ? "checked" : "";?>>
                <span class="lbl"> <?php
       echo isset($value) ? JText::_('COM_COMMUNITY_THEME_'.strtoupper($value)) : JText::_('COM_COMMUNITY_THEME_DEFAULT');
        ?></span>
            </label>
        </div>
    <?php
    }
}