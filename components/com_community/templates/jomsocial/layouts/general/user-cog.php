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

?>

<?php if($options){?>
<div class="joms-list__options">
    <a href="javascript:" data-ui-object="joms-dropdown-button">
        <svg viewBox="0 0 14 20" class="joms-icon">
            <use xlink:href="<?php echo CRoute::getURI(); ?>#joms-icon-cog"></use>
        </svg>
    </a>
    <ul class="joms-dropdown">
        <?php foreach($options as $key=>$value){
            if($value){
            ?>
            <li><a href="javascript:" onclick='<?php echo trim($datas[$key]['href']); ?>'><?php echo JText::_($datas[$key]['lang']); ?></a></li>
        <?php
            }
        }
        ?>
    </ul>
</div>
<?php } ?>