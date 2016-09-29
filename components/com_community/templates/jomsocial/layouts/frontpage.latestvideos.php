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

<?php if(!empty($data)) { ?>
<?php foreach( $data as $video ) { ?>
    <li class="joms-list__item">
        <a  href="<?php echo $video->getURL(); ?>">
            <img src="<?php echo $video->getThumbNail(); ?>" alt="<?php echo $video->getTitle(); ?>"   title="<?php echo $this->escape($video->title); ?>" />
            <span class="joms-video__duration"><small><?php echo $video->getDurationInHMS(); ?></small></span>
        </a>
    </li>
<?php } ?>
<?php } else {
?>
<div class="cEmpty"><?php echo JText::_('COM_COMMUNITY_VIDEOS_NO_VIDEO'); ?></div>
<?php } ?>
