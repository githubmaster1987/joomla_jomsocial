<?php
    /**
     * @copyright (C) 2015 iJoomla, Inc. - All rights reserved.
     * @license GNU General Public License, version 2 (http://www.gnu.org/licenses/gpl-2.0.html)
     * @author iJoomla.com <webmaster@ijoomla.com>
     * @url https://www.jomsocial.com/license-agreement
     * The PHP code portions are distributed under the GPL license. If not otherwise stated, all images, manuals, cascading style sheets, and included JavaScript *are NOT GPL, and are released under the IJOOMLA Proprietary Use License v1.0
     * More info at https://www.jomsocial.com/license-agreement
     */
    // Check to ensure this file is included in Joomla!
    defined('_JEXEC') or die();
    $i = 0;
?>

<?php
    foreach ($apps as $app) {
        if (trim($app->data) != '') {
            ?>
            <div class="joms-module--stacked">
                <div id="<?php echo $app->id; ?>" class="joms-module__header">
                    <?php //echo ($app->showtitle) ? $module->title : '';
                    echo JText::_($app->title);
                    ?>
                </div>
                <div id="joms-app--<?php echo $app->id ?>" class="joms-module__body <?php echo 'content_'.$app->name;?>">
                    <?php

                    // If style attributes are not given or set, we enforce it to use the xhtml style
                    // so the title will display correctly.
                    if (!isset($attribs['style'])) {
                        $attribs['style'] = 'none';
                    }

                    //customize the first div of the container to have joms-module and borderless class
                    //$modHtml = preg_replace('/<div>/', '<div class="joms-module borderless">', JModuleHelper::renderModule($module, $attribs), 1);
                    //$modHtml = '<div class="joms-module">'.JModuleHelper::renderModule($module, $attribs).'</div>';
                    echo $app->data;
                    ?>
                    <?php if($my->id == JFactory::getApplication()->input->get('userid')){ ?>
                    <small class="joms-block" style="text-align:right">
                        <a href="javascript:void(0);" class="joms-button--link" onclick="joms.api.appAbout('<?php echo $app->name; ?>');" title="<?php echo JText::_('COM_COMMUNITY_APPS_LIST_ABOUT'); ?>">
                            <svg viewBox="0 0 16 18" class="joms-icon ">
                                <use xlink:href="#joms-icon-info"></use>
                            </svg>
                        </a>
                        <a href="javascript:void(0);" class="joms-button--link" onclick="joms.api.appSetting('<?php echo $app->id; ?>','<?php echo $app->name; ?>');" title="<?php echo JText::_('COM_COMMUNITY_APPS_COLUMN_SETTINGS'); ?>" >
                            <svg viewBox="0 0 16 18" class="joms-icon ">
                                <use xlink:href="#joms-icon-cog"></use>
                            </svg>
                        </a>
                        <a href="javascript:void(0);" class="joms-button--link" onclick="joms.api.appPrivacy('<?php echo $app->name; ?>');" title="<?php echo JText::_('COM_COMMUNITY_APPS_COLUMN_PRIVACY'); ?>" >
                            <svg viewBox="0 0 16 18" class="joms-icon ">
                                <use xlink:href="#joms-icon-lock"></use>
                            </svg>
                        </a>
                    </small>
                    <?php } ?>
                </div>
            </div>
        <?php
        }
    }
?>
