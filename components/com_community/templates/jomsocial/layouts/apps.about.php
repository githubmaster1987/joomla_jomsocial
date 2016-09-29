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

<div class="joms-form__group">
    <span ><?php echo JText::_('COM_COMMUNITY_APPS_NAME');?></span>
    <?php echo $this->escape($app->name); ?>
</div>

<?php if($this->params->get('appsShowAuthor')) { ?>
<div class="joms-form__group">
    <span ><?php echo JText::_('COM_COMMUNITY_APPS_AUTHOR');?></span>
    <?php echo $this->escape($app->author); ?>
</div>
<?php } ?>

<div class="joms-form__group">
    <span ><?php echo JText::_('COM_COMMUNITY_APPS_VERSION');?></span>
    <?php echo $this->escape($app->version); ?>
</div>

<div class="joms-form__group">
    <span ><?php echo JText::_('COM_COMMUNITY_APPS_DESCRIPTION');?></span>
    <?php echo $this->escape( JText::_( $app->description ) ); ?>
</div>
