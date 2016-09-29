<?php
    /**
     * @copyright (C) 2014 iJoomla, Inc. - All rights reserved.
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
    foreach ($modules as $module) {
        ?>
        <div id="joms-app--<?php echo $module->id ?>" class="joms-tab__content"<?php echo $i++ ? ' style="display:none"' : '' ?>>
            <?php
                //customize the first div of the container to have joms-module and borderless class
                //$modHtml = preg_replace('/<div>/', '<div class="joms-module borderless">', JModuleHelper::renderModule($module, $attribs), 1);
                $modHtml = '<div class="joms-module">'.$module->html.'</div>';
                echo $modHtml;
            ?>
        </div>
    <?php
    }
?>
