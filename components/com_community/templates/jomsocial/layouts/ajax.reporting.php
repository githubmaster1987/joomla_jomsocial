 <?php
/**
* @copyright (C) 2013 iJoomla, Inc. - All rights reserved.
* @license GNU General Public License, version 2 (http://www.gnu.org/licenses/gpl-2.0.html)
* @author iJoomla.com <webmaster@ijoomla.com>
* @url https://www.jomsocial.com/license-agreement
* The PHP code portions are distributed under the GPL license. If not otherwise stated, all images, manuals, cascading style sheets, and included JavaScript *are NOT GPL, and are released under the IJOOMLA Proprietary Use License v1.0
* More info at https://www.jomsocial.com/license-agreement
*/
defined('_JEXEC') or die();
?>

<div class="joms-popup__content">
    <form onsubmit="return false;">
        <div class="joms-form__group">
            <span><?php echo JText::_('COM_COMMUNITY_PREDEFINED_REPORTS'); ?></span>
            <select class="joms-select">
                <option selected="selected" value=""><?php echo JText::_('COM_COMMUNITY_SELECT_PREDEFINED_REPORTS'); ?></option>
                <?php

                    if ($reports) {
                        foreach($reports as $report) {
                            $reportString = JText::_(trim($report));

                ?><option value="<?php echo $reportString; ?>"><?php echo $reportString; ?></option><?php

                    }
                }

                ?>
            </select>
        </div>
        <div class="joms-form__group">
            <span><?php echo JText::_('COM_COMMUNITY_REPORT_MESSAGE'); ?></span>
            <textarea class="joms-textarea" name="report-message"></textarea>
            <p class="joms-help joms-js--error" style="color:red; display:none"><?php echo JText::_('COM_COMMUNITY_REPORT_MESSAGE_CANNOT_BE_EMPTY'); ?></p>
        </div>
    </form>
</div>
