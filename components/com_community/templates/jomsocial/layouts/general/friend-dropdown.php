<?php

    /**
     * @copyright (C) 2014 iJoomla, Inc. - All rights reserved.
     * @license GNU General Public License, version 2 (http://www.gnu.org/licenses/gpl-2.0.html)
     * @author iJoomla.com <webmaster@ijoomla.com>
     * @url https://www.jomsocial.com/license-agreement
     * The PHP code portions are distributed under the GPL license. If not otherwise stated, all images, manuals, cascading style sheets, and included JavaScript *are NOT GPL, and are released under the IJOOMLA Proprietary Use License v1.0
     * More info at https://www.jomsocial.com/license-agreement
     */
    defined('_JEXEC') or die('Restricted access');

?>

<div class="joms-list__button--dropdown">
    <a href="javascript:" class="joms-button--neutral joms-button--full-small joms-button--smallest" data-ui-object="joms-dropdown-button" onclick="<?php echo $options->buttonTrigger ? $options->buttonTrigger : '' ?>">
        <svg viewBox="0 0 14 20" class="joms-icon">
            <use xlink:href="<?php echo CRoute::getURI(); ?>#joms-icon-user"></use>
        </svg>
        <?php echo JText::_($options->button); ?>
    </a>
    <ul class="joms-dropdown">
    <?php
        if(is_array($options->dropdown)){
        foreach($options->dropdown as $key=>$option){
        ?>
            <li><a href="javascript:" onclick="<?php echo $options->dropdownTrigger[$key]; ?>"><?php echo JText::_($option); ?></a></li>

    <?php } }elseif($options->dropdown){ ?>
        <li><a href="javascript:" onclick="<?php echo $options->dropdownTrigger; ?>"><?php echo JText::_($options->dropdown); ?></a></li>
    <?php } ?>
    </ul>
</div>
