<?php
/**
* @copyright (C) 2013 iJoomla, Inc. - All rights reserved.
* @license GNU General Public License, version 2 (http://www.gnu.org/licenses/gpl-2.0.html)
* @author iJoomla.com <webmaster@ijoomla.com>
* @url https://www.jomsocial.com/license-agreement
* The PHP code portions are distributed under the GPL license. If not otherwise stated, all images, manuals, cascading style sheets, and included JavaScript *are NOT GPL, and are released under the IJOOMLA Proprietary Use License v1.0
* More info at https://www.jomsocial.com/license-agreement
*/
defined('_JEXEC') OR DIE();
$my = CFactory::getUser();
?>
<div class="joms-module__wrapper"><?php $this->renderModules( 'js_noaccess_top' ); ?></div>
<div class="joms-module__wrapper--stacked"><?php $this->renderModules( 'js_noaccess_top_stacked' ); ?></div>
<?php if( isset( $notice ) && !empty( $notice ) ){ ?>
	<div class="joms-alert--danger">
		<h4 class="joms-alert__head"><?php echo JText::_('COM_COMMUNITY_PERMISSION_DENIED_WARNING');?></h4>
		<?php echo $notice;?>
	</div>
<?php } ?>
<?php if($my->id == 0) { ?>
	<div class="joms-alert--info">
		<?php echo JText::sprintf('COM_COMMUNITY_NOTICE_NO_ACCESS' , CRoute::_('index.php?option=com_community&view=frontpage') , CRoute::_('index.php?option=com_community&view=register') );?>
	</div>
<?php } ?>
<div class="joms-module__wrapper"><?php $this->renderModules( 'js_noaccess_bottom' ); ?></div>
<div class="joms-module__wrapper--stacked"><?php $this->renderModules( 'js_noaccess_bottom_stacked' ); ?></div>
