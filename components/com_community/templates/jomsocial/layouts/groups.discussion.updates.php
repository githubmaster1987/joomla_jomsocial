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
<?php if ($discussions) { ?>
	<div class="joms-stream__container">
		<?php foreach ($discussions as $discussion) {
            $params = new CParameter($discussion['params']);
            $user = CFactory::getUser($discussion['post_by']);
            ?>

		<div class="joms-stream">
			<div class="joms-stream__header">
				<div class="joms-avatar--stream <?php echo CUserHelper::onlineIndicator($user); ?>">
                    <a href="<?php echo CRoute::_('index.php?option=com_community&view=profile&userid='.$user) ?>">
					   <img src="<?php echo $user->getThumbAvatar(); ?>" alt="<?php echo $discussion['group_name']; ?>" data-author="<?php echo $user->id; ?>" />
                    </a>
				</div>
				<div class="joms-stream__meta">
					<span class="joms-stream__user">
						<?php echo $discussion['created_by']; ?>
						&nbsp;▶
						<a href="<?php echo $discussion['group_link']; ?>" ><?php echo $discussion['group_name']; ?></a>
						&nbsp;▶
						<a href="<?php echo $discussion['discussion_link'] ?>" ><?php echo $discussion['title']; ?></a>
					</span>
					<span class="joms-stream__time">
						<small>
							<?php echo $discussion['created_interval']; ?>
						</small>
					</span>
				</div>
			</div>
			<div class="joms-stream__body">
				<?php
                    // Escape content
                    $discussion['comment'] = CTemplate::escape($discussion['comment']);
                    $discussion['comment'] = CStringHelper::autoLink($discussion['comment']);
                    $discussion['comment'] = nl2br($discussion['comment']);
                    $discussion['comment'] = CStringHelper::getEmoticon($discussion['comment']);
                    $discussion['comment'] = CStringHelper::converttagtolink($discussion['comment']);
                    $discussion['comment'] =  CUserHelper::replaceAliasURL($discussion['comment']);

                    echo substr( $discussion['comment'], 0, 250 );
                    if ( strlen( $discussion['comment'] ) > 250 ) {
                        echo ' ...';
                    }

                    // @TODO: DRY
                    $video = JTable::getInstance('Video', 'CTable');
                    if( $video->init($params->get('url')) ) {
                        $video->isValid();
                    } else {
                        $video = false;
                    }

                    if ( is_object($video) ) {

                ?>
                    <div class="joms-media--video joms-js--video"
                            data-type="<?php echo $video->type; ?>"
                            data-id="<?php echo $video->video_id; ?>"
                            data-path="<?php echo ($video->type == 'file') ? CStorage::getStorage($video->storage)->getURI($video->path) : $video->path; ?>"
                            style="margin-top:10px;">
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

                <?php } ?>

			</div>
		</div>

		<?php } ?>
	</div>
<?php } else {?>
	<div class="joms-alert--info"><?php echo JText::_('COM_COMMUNITY_GROUP_NO_UPDATE'); ?></div>
<?php } ?>
