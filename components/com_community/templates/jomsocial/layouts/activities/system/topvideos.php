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

$model		= CFactory::getModel( 'videos');
$videos		= $model->getPopularVideos( 3 );

$tmpl		=	new CTemplate();
?>

<div class="joms-stream__body joms-stream-box" >

	<h4><?php echo JText::_('COM_COMMUNITY_ACTIVITIES_TOP_VIDEOS'); ?></h4>

        <?php
		foreach( $videos as $video ) {
			$user = CFactory::getUser( $video->creator );
		?>

        <div class="joms-media--video joms-js--video"
                data-type="<?php echo $video->type; ?>"
                data-id="<?php echo $video->video_id; ?>"
                data-path="<?php echo ($video->type == 'file') ? CStorage::getStorage($video->storage)->getURI($video->path) : $video->path; ?>" >

            <div class="joms-media__thumbnail">
                <img src="<?php echo $video->getThumbnail(); ?>" alt="<?php echo $video->title; ?>">
                <a href="javascript:" class="mejs-overlay mejs-layer mejs-overlay-play joms-js--video-play">
                    <div class="mejs-overlay-button"></div>
                </a>
            </div>
            <div class="joms-media__body">
                <h4 class="joms-media__title">
                    <?php echo JHTML::_('string.truncate', $video->title, 50, true, false); ?>
                </h4>
                <p class="joms-media__desc">
                    <?php echo JHTML::_('string.truncate', $video->description, $config->getInt('streamcontentlength'), true, false); ?>
                </p>
            </div>
        </div>

        <div class="joms-gap"></div>

		<?php
		}
		?>

</div>
