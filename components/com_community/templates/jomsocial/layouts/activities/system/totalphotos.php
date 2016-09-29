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

$photosModel = CFactory::getModel( 'photos' );
$total       = $photosModel->getTotalSitePhotos();
?>

<div class="joms-stream__body joms-stream-box" >
    <h4><?php echo JText::_('COM_COMMUNITY_ACTIVITIES_TOTAL_PHOTOS'); ?></h4>
    <p><?php echo JText::sprintf('COM_COMMUNITY_TOTAL_PHOTOS_ACTIVITY_TITLE', CRoute::_('index.php?option=com_community&view=photos') ,$total); ?></p>
</div>

