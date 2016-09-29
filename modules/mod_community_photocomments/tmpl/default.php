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

<?php

    $config = CFactory::getConfig();
    $document = JFactory::getDocument();
    $document->addScriptDeclaration("joms_prev_comment_load = +'" . $config->get('prev_comment_load', 10) . "';");

    if ($comments) {
        $i = 1;
        $total = count($comments);
        $char_limit = intval($params->get('char_limit', 50));
        $captionLimit = intval($params->get('caption_limit'));

        foreach ($comments as $comment) {

            //get the time
            $date = JDate::getInstance($comment->date);
            if ( $config->get('activitydateformat') == "lapse" ) {
                $createdTime = CTimeHelper::timeLapse($date);
            } else {
                $createdTime = $date->format($config->get('profileDateFormat'));
            }


            if (($char_limit > 0) && (strlen($comment->comment) > $char_limit)) {
                $comment->comment = substr(CUserHelper::replaceAliasURL($comment->comment,false,true), 0, $char_limit) . '...';
            }
            if (($captionLimit > 0) && (strlen($comment->caption) > $captionLimit)) {
                $comment->caption = substr($comment->caption, 0, $captionLimit) . '...';
            }
            $poster = CFactory::getUser($comment->post_by);

            if ( $isPhotoModal ) {
                $link = 'javascript:" onclick="joms.api.photoOpen(\'' . $comment->albumid . '\', \'' . $comment->contentid . '\');';
            } else if ($comment->phototype == PHOTOS_USER_TYPE) {
                $link = CRoute::_('index.php?option=com_community&view=photos&task=photo&albumid=' . $comment->albumid . '&photoid=' . $comment->contentid . '&userid=' . $comment->creator); // . '#photoid=' . $comment->contentid;
            } else {
                $link = CRoute::_('index.php?option=com_community&view=photos&task=photo&albumid=' . $comment->albumid . '&photoid=' . $comment->contentid . '&groupid=' . $comment->groupid); // . '#photoid=' . $comment->contentid;
            }
            ?>
            <div class="joms-stream__header no-gap">

                <?php if($params->get('show_image',2)){
                    // 1 = avatar, 2 = image thumbnail
                    ?>
                <div class="joms-avatar--stream <?php echo ($params->get('show_image', 2) == 1) ? CUserHelper::onlineIndicator(CFactory::getUser($comment->post_by)) : 'square'; ?> ">
                    <a href="<?php echo ($params->get('show_image',2) == 1) ? CRoute::_('index.php?option=com_community&view=profile&userid='.$comment->post_by) : $link; ?>" >
                        <img src="<?php echo ($params->get('show_image', 2) == 1) ? CFactory::getUser($comment->post_by)->getAvatar() : $comment->thumbnail; ?>"
                        alt="<?php echo CFactory::getUser($comment->post_by)->getDisplayName(); ?>"
                        <?php echo ($params->get('show_image', 2) == 1) ? 'data-author="'.$comment->post_by.'"' : ''; ?>
                         />
                    </a>
                </div>
                <?php } ?>
                <div class="joms-stream__meta">
                    "<?php echo CStringHelper::converttagtolink(CUserHelper::replaceAliasURL($comment->comment)); ?>" <?php echo JText::_('MOD_COMMUNITY_PHOTOCOMMENTS_BY') ?>
                    <a href="<?php echo CRoute::_('index.php?option=com_community&view=profile&userid='.$comment->post_by) ?>"><?php echo CFactory::getUser($comment->post_by)->getDisplayName(); ?></a>
                    <div class="joms-text--light"><small><?php echo $createdTime; ?></small></div>
                </div>
            </div>
            <?php
            $i++;
        }
    } else {
        ?>
        <div class="joms-blankslate"><?php echo JText::_('MOD_COMMUNITY_PHOTOCOMMENTS_NO_COMMENTS'); ?></div>
        <?php
    }
?>

