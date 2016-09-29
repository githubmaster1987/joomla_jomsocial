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

$isAjax = false;
$jinput = JFactory::getApplication()->input;
if ( $jinput->get('task', '') == 'azrul_ajax') {
    $isAjax = true;
}

?>

<div class="joms-comment__reply joms-js--newcomment joms-js--newcomment-<?php echo $uniqueId; ?>">
    <div class="joms-textarea__wrapper">
        <div class="joms-textarea joms-textarea__beautifier"></div>
        <textarea name="comment" class="joms-textarea"
            data-id="<?php echo $uniqueId; ?>"
            data-func="<?php echo $ajaxAddFunction; ?>"
            value=""
            placeholder="<?php echo JText::_('COM_COMMUNITY_WRITE_A_COMMENT'); ?>"></textarea>
        <div class="joms-textarea__loading"><img src="<?php echo JURI::root(true); ?>/components/com_community/assets/ajax-loader.gif" alt="loader" ></div>
        <div class="joms-textarea joms-textarea__attachment">
            <button onclick="joms.view.comment.removeAttachment(this);">×</button>
            <div class="joms-textarea__attachment--loading"><img src="<?php echo JURI::root(true); ?>/components/com_community/assets/ajax-loader.gif" alt="loader"></div>
            <div class="joms-textarea__attachment--thumbnail"><img src="#" alt="attachment"></div>
        </div>
    </div>
    <svg viewBox="0 0 16 16" class="joms-icon joms-icon--add" onclick="joms.view.comment.addAttachment(this);">
        <use xlink:href="<?php echo $isAjax ? '' : CRoute::getURI(); ?>#joms-icon-camera"></use>
    </svg>
    <span><input class="joms-button--comment joms-button--small joms-js--btn-send" type="submit" value="<?php echo JText::_('COM_COMMUNITY_SEND'); ?>"></span>
</div>
<script>
    joms_wall_remove_func = '<?php echo $ajaxRemoveFunc; ?>';
</script>
