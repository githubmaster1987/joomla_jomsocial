<?php
/**
* @copyright (C) 2015 iJoomla, Inc. - All rights reserved.
* @license GNU General Public License, version 2 (http://www.gnu.org/licenses/gpl-2.0.html)
* @author iJoomla.com <webmaster@ijoomla.com>
* @url https://www.jomsocial.com/license-agreement
* The PHP code portions are distributed under the GPL license. If not otherwise stated, all images, manuals, cascading style sheets, and included JavaScript *are NOT GPL, and are released under the IJOOMLA Proprietary Use License v1.0
* More info at https://www.jomsocial.com/license-agreement
*/
defined( '_JEXEC' ) or die( 'Unauthorized Access' ); ?>

<?php if($user->isOnline()):?>
<?php else:?>
<?php endif;?>

<?php

    $config = CFactory::getConfig();
    $document = JFactory::getDocument();
    $document->addScriptDeclaration("joms_prev_comment_load = +'" . $config->get('prev_comment_load', 10) . "';");

    if ($comments) {
        $i = 1;
        $total = count($comments);
        $char_limit = intval($params->get('char_limit',50));
        foreach ($comments as $comment) {
            //get the time
            $date = JDate::getInstance($comment->date);
            if ( $config->get('activitydateformat') == "lapse" ) {
                $createdTime = CTimeHelper::timeLapse($date);
            } else {
                $createdTime = $date->format($config->get('profileDateFormat'));
            }

            $comment->comment = CUserHelper::replaceAliasURL(CStringHelper::escape($comment->comment),false,true);

            if (($char_limit > 0) && (JString::strlen($comment->comment) > $char_limit)) {
                $comment->comment = JString::substr($comment->comment, 0, $char_limit) . '...';
            }

            $poster = CFactory::getUser($comment->post_by);

            if ( $isVideoModal ) {
                $link = 'javascript:" onclick="joms.api.videoOpen(\'' . $comment->contentid . '\');';
            } else if ($comment->creator_type == VIDEO_USER_TYPE) {
                $link = CRoute::_('index.php?option=com_community&view=videos&task=video&videoid=' . $comment->contentid . '&userid=' . $comment->creator);
            } else {
                $link = CRoute::_('index.php?option=com_community&view=videos&task=video&videoid=' . $comment->contentid . '&groupid=' . $comment->groupid);
            }
            ?>
            <div class="joms-stream__header no-gap">
            <?php if($params->get('show_image',2)){
                // 1 = avatar, 2 = video thumbnail
                ?>
                <div class="joms-avatar--stream <?php echo ($params->get('show_image', 2) == 1) ? CUserHelper::onlineIndicator(CFactory::getUser($comment->post_by)) : 'square video-thumb'; ?> ">
                    <a href="<?php echo $link;?>" >
                        <img src="<?php echo ($params->get('show_image', 2) == 1) ? CFactory::getUser($comment->post_by)->getAvatar() : $comment->thumb; ?>"
                        alt="<?php echo CFactory::getUser($comment->post_by)->getDisplayName(); ?>"
                        <?php echo ($params->get('show_image', 2) == 1) ? 'data-author="'.$comment->post_by.'"' : ''; ?>
                         />
                    </a>
                </div>
            <?php } ?>


                <div class="joms-stream__meta">
                    "<?php echo CUserHelper::replaceAliasURL(CStringHelper::escape($comment->comment),false,true); ?>" by
                    <a href="<?php echo CRoute::_('index.php?option=com_community&view=profile&userid='.$comment->post_by) ?>"><?php echo CFactory::getUser($comment->post_by)->getDisplayName(); ?></a>
                    <div class="joms-text--light"><small><?php echo $createdTime; ?></small></div>

                </div>
            </div>
            <?php
            $i++;
        }
    } else {
        ?>
        <div class="joms-blankslate"><?php echo JText::_('MOD_COMMUNITY_VIDEOCOMMENTS_NO_COMMENTS'); ?></div>
        <?php
    }
?>

