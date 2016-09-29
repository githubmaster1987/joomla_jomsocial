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
<ol>
    <li> <?php echo JText::_('COM_COMMUNITY_TROUBLESHOOTS_CLEANUP_STREAM'); ?> <button id="clean_stream"><?php echo JText::_('COM_COMMUNITY_CLEAN'); ?></button><p class="clean_stream alert-success"></p></li>
    <li> <?php echo JText::_('COM_COMMUNITY_TROUBLESHOOTS_CLEANUP_AVATAR'); ?> <button id="clean_avatar_local"><?php echo JText::_('COM_COMMUNITY_CLEAN'); ?></button><p class="clean_avatar_local alert-success"></p></li>
    <li> <?php echo JText::_('COM_COMMUNITY_TROUBLESHOOTS_CLEANUP_AVATAR_S3'); ?> <button id="clean_avatar_s3"><?php echo JText::_('COM_COMMUNITY_CLEAN'); ?></button><p class="clean_avatar_s3 alert-success"></p></li>
    <li> <?php echo JText::_('COM_COMMUNITY_TROUBLESHOOTS_CLEANUP_COVER'); ?> <button id="clean_cover_local"><?php echo JText::_('COM_COMMUNITY_CLEAN'); ?></button><p class="clean_cover_local alert-success"></p></li>
</ol>

<script type="application/javascript">

    //clean stream
    $('#clean_stream').click(function(){
       $(this).hide();
        jax.call('community' , 'admin,troubleshoots,ajaxCleanStream');
    });

    //clean orphaned avatar files in local
    $('#clean_avatar_local').click(function(){
        $(this).hide();
        jax.call('community' , 'admin,troubleshoots,ajaxCleanLocalOrphanedAvatar');
    });

    //clean orphaned avatar files in s3
    $('#clean_avatar_s3').click(function(){
        $(this).hide();
        jax.call('community' , 'admin,troubleshoots,ajaxCleanS3OrphanedAvatar');
    });

    //clean orphaned cover files in local
    $('#clean_cover_local').click(function(){
        $(this).hide();
        jax.call('community' , 'admin,troubleshoots,ajaxCleanLocalOrphanedCover');
    });

    //clean orphaned cover files in s3
    $('#clean_cover_s3').click(function(){
        $(this).hide();
        jax.call('community' , 'admin,troubleshoots,ajaxCleanS3OrphanedCover');
    });
</script>