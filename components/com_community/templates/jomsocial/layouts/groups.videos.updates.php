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

<?php if ( $videos ) { ?>
<div class="joms-module__wrapper">
    <div class="joms-tab__bar">
        <a href="#joms-group--videos" class="active"><?php echo JText::_('COM_COMMUNITY_GROUPS_VIDEO_UPDATES'); ?></a>
    </div>

    <div id="#joms-group--videos" class="joms-tab__content">
        <ul class="joms-list--videos">
        <?php   foreach($videos as $video){ ?>
            <li class="joms-list__item">
                <a
                    <?php if ( $isVideoModal ) { ?>
                    href="javascript:" onclick="joms.api.videoOpen('<?php echo $video->getId(); ?>');"
                    <?php } else { ?>
                    href="<?php echo CRoute::_('index.php?option=com_community&view=videos&task=video&groupid=' . $video->getId() . '&videoid=' . $video->getId()); ?>"
                    <?php } ?>
                >
                    <img class="joms-list__cover" src="<?php echo $video->getThumbnail(); ?>" title="<?php echo $video->getTitle(); ?>" />
                    <span class="joms-video__duration"><?php echo $video->getDurationInHMS(); ?></span>
                </a>
            </li>
        <?php } ?>
        </ul>
    </div>
</div>
<?php } ?>
