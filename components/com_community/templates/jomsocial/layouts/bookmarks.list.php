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

<h5><?php echo JText::_('COM_COMMUNITY_SHARE_THIS_VIA_LINK');?></h5>

<p><b></b></p>

<ul class="joms-bookmarks joms-list">
	<?php foreach ($bookmarks as $bookmark) { ?>
	<li><a href="<?php echo $bookmark->link;?>" target="_blank" class="<?php echo $bookmark->className; ?>"><?php echo $this->escape($bookmark->name); ?></a></li>
	<?php } ?>
</ul>

<?php if ( $config->get('shareviaemail') ) { ?>
<hr class="cSeperator">

<form style="margin:0">
    <div class="joms-form__group" style="margin-bottom:0">
        <span><?php echo JText::_('COM_COMMUNITY_SHARE_THIS_VIA_EMAIL'); ?></span>
        <input type="text" class="joms-input" name="bookmarks-email">
    </div>
    <div class="joms-form__group">
        <span></span>
        <p class="joms-help"><?php echo JText::_('COM_COMMUNITY_SHARE_THIS_VIA_EMAIL_INFO'); ?></p>
    </div>
    <div class="joms-form__group">
        <span><?php echo JText::_('COM_COMMUNITY_SHARE_THIS_MESSAGE'); ?></span>
        <textarea class="joms-textarea" name="bookmarks-message"></textarea>
    </div>
</form>
<?php } ?>
