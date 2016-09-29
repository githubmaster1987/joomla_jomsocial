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
        <a href="#requirements" data-toggle="tab"><?php echo JText::_('COM_COMMUNITY_CONFIGURATION'); ?></a>
    </li>
    <li class="">
        <a href="#preview" data-toggle="tab"><?php echo JText::_('COM_COMMUNITY_DIGEST_PREVIEW'); ?></a>
    </li>
<!--    <li class="">-->
<!--        <a href="#pending" data-toggle="tab">--><?php //echo JText::_('COM_COMMUNITY_DIGEST_PENDING_LIST'); ?><!--</a>-->
<!--    </li>-->
</ul>

<div id="myTabContent" class="tab-content" style="padding-top:24px;">
    <!-- System requirements -->
    <div class="tab-pane fade active in" id="requirements">
        <?php include_once 'configuration.php'; ?>
    </div>

    <!-- Preview -->
    <div class="tab-pane fade" id="preview">
        <?php include_once 'preview.php'; ?>
    </div>

    <!-- Pending List -->
<!--    <div class="tab-pane fade" id="pending">-->
<!--        --><?php //include_once 'pending.php'; ?>
<!--    </div>-->

</div>
