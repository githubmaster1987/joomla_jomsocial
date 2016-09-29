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

$config = CFactory::getConfig();
$isVideoModal = $config->get('video_mode') == 1;

?>

<div class="joms-module__wrapper">
    <div>
    <div class="joms-tab__bar"><a href="#" class="active"><?php echo JText::_('COM_COMMUNITY_PROFILE_VIDEO_TITLE'); ?></a></div>
	<div class="joms-tab__content">
		<div class="video-player joms-relative">
            <a
                <?php if ($isVideoModal) { ?>
                href="javascript:" onclick="joms.api.videoOpen('<?php echo $video->id; ?>');"
                <?php } else { ?>
                href="<?php echo $video->getURL(); ?>"
                <?php } ?>
            ><img src="<?php echo $video->getThumbnail(); ?>" width="<?php echo $videoThumbWidth; ?>" height="<?php echo $videoThumbHeight; ?>" alt="<?php echo $video->title; ?>" >
				<?php if ( !$video->isPending() ) { ?>
				<span class="joms-video__duration"><?php echo $video->getDurationInHMS(); ?></span>
				<?php } ?>
			</a>
		</div>
	</div>
    </div>
</div>
