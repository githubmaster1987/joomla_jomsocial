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
?>
<!-- Tabs header -->
<ul id="myTab" class="nav nav-tabs">
    <!-- System requirement -->
    <li class="active">
        <a href="#requirements" data-toggle="tab"><?php echo JText::_('COM_COMMUNITY_TROUBLESHOOTS_SYSTEM_REQUIREMENTS'); ?></a>
    </li>
    <!-- File modifications -->
    <li class="dropdown">
        <a href="#" class="dropdown-toggle" data-toggle="dropdown"><?php echo JText::_('COM_COMMUNITY_TROUBLESHOOTS_CHECK_FILE_MODIFICATIONS'); ?><b class="caret"></b></a>
        <ul class="dropdown-menu" style="background:white !important;">
            <li style="background:white;"><a href="#files-frontend" data-toggle="tab"><?php echo JText::_('COM_COMMUNITY_TROUBLESHOOTS_FRONTEND'); ?></a></li>
            <li style="background:white;"><a href="#files-backend" data-toggle="tab"><?php echo JText::_('COM_COMMUNITY_TROUBLESHOOTS_BACKEND'); ?></a></li>
        </ul>
    </li>
    <!-- jQuery instance check -->
    <li class=""><a href="#jQuery" data-toggle="tab"><?php echo JText::_('COM_COMMUNITY_TROUBLESHOOTS_JQUERY'); ?></a></li>
    <!-- Installed plugins -->
    <li class=""><a href="#plugins" data-toggle="tab"><?php echo JText::_('COM_COMMUNITY_TROUBLESHOOTS_PLUGINS_INSTALLED'); ?></a></li>
    <li class=""><a href="#stream_cleanup" data-toggle="tab"><?php echo JText::_('COM_COMMUNITY_TROUBLESHOOTS_CLEANUP'); ?></a></li>
</ul>
<!-- Tabs content -->
<div id="myTabContent" class="tab-content" style="padding-top:24px;">
    <!-- System requirements -->
    <div class="tab-pane fade active in" id="requirements">
        <div class="alert alert-info"><?php echo JText::_('COM_COMMUNITY_TROUBLESHOOTS_SYSTEMREQUIREMENTS_TIPS'); ?></div>
        <?php include_once 'systemrequirements.php'; ?>
    </div>
    <!-- Core files checking -->
    <div class="tab-pane fade" id="files-frontend">
        <div class="alert alert-info"><?php echo JText::_('COM_COMMUNITY_TROUBLESHOOTS_FILESCHECKING_FRONTEND_TIPS'); ?></div>
        <?php $this->troubleshoots->coreFilesCheck(JPATH_ROOT . '/components/com_community'); ?>
    </div>
    <!-- Core files checking -->
    <div class="tab-pane fade" id="files-backend">
        <div class="alert alert-info"><?php echo JText::_('COM_COMMUNITY_TROUBLESHOOTS_FILESCHECKING_BACKEND_TIPS'); ?></div>
        <?php $this->troubleshoots->coreFilesCheck(JPATH_ADMINISTRATOR . '/components/com_community'); ?>
    </div>
    <!-- jQuery detection -->
    <div class="tab-pane fade" id="jQuery">
        <div class="alert alert-info"><?php echo JText::_('COM_COMMUNITY_TROUNLESHOOTS_JQUERY_INSTANCES_TIPS'); ?></div>
        <?php $this->troubleshoots->filesCheck(JPATH_ROOT); ?>
    </div>
    <!-- Extensions -->
    <div class="tab-pane fade" id="plugins">
        <div class="alert alert-info"><?php echo JText::_('COM_COMMUNITY_TROUBLESHOOTS_EXTENSIONS_TIPS'); ?></div>
        <?php include_once 'plugins.php'; ?>
    </div>
    <!-- Stream cleanup -->
    <div class="tab-pane fade" id="stream_cleanup">
        <div class="alert alert-info"><?php echo JText::_('COM_COMMUNITY_TROUBLESHOOTS_CLEANUP_TIPS'); ?></div>
        <?php include_once 'cleanup.php'; ?>
    </div>
</div>

