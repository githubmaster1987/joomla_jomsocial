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
<!DOCTYPE html>
<html style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6em; margin: 0; padding: 0;">
<head>
    <meta name="viewport" content="width=device-width">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title>Digest Email</title>
</head>
<body bgcolor="#e5e5e5" style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6em; -webkit-font-smoothing: antialiased; height: 100%; -webkit-text-size-adjust: none; width: 100% !important; margin: 0; padding: 0;">

<!-- body -->
<table class="container-table" style="width: 100%; margin: 0; padding: 0; border-spacing: 0; border:1px solid #eee;">
    <tr style="margin: 0; padding: 0;">
        <td style="margin: 0; padding: 0;"></td>
        <td bgcolor="#FFFFFF" style="clear: both !important; display: block !important; max-width: 600px !important; Margin: 0 auto; padding: 0;">

            <!-- content -->
            <div style="display: block; max-width: 600px; margin: 0 auto; padding: 0;">

                <table bgcolor="#fff" style="color:#81858A; width:100%; margin:0; border-spacing: 0;">
                    <tr>
                        <td style="padding:30px 30px 15px">
                            <p style="color:#333; font-size: 16px; line-height: 24px"><?php echo JText::_('COM_COMMUNITY_DIGEST_EMAIL_HELLO'); ?> <strong><?php echo $data['user_name'];?></strong>,</p>
                            <p style="font-size: 16px; line-height: 24px"><?php echo JText::sprintf('COM_COMMUNITY_DIGEST_EMAIL_GREETING_LOGIN',$data['inactive_days']) ?> <a href="<?php echo $data['siteurl']; ?>" style="color:#258BFF; text-decoration:none !important;"><?php echo $data['sitename']; ?></a>. <?php echo JText::_('COM_COMMUNITY_DIGEST_EMAIL_GREETING_MISSED') ?>:</p>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:30px; background: #f5f5f5">
                            <p style="font-size: 14px; line-height: 22px"><?php echo JText::_('COM_COMMUNITY_DIGEST_EMAIL_OUR_USER'); ?>
                                <?php
                                //building the stats
                                $statsText = array();
                                if($data['totalPosts'] > 0){
                                    $statsText[] = JText::sprintf('COM_COMMUNITY_DIGEST_EMAIL_USER_CREATED_POST', $data['totalPosts']);
                                }

                                if($data['totalPhotos'] > 0){
                                    $statsText[] = JText::sprintf('COM_COMMUNITY_DIGEST_EMAIL_USER_ADDED_PHOTO', $data['totalPhotos']);
                                }

                                if($data['totalVideos'] > 0){
                                    $statsText[] = JText::sprintf('COM_COMMUNITY_DIGEST_EMAIL_USER_SHARED_VIDEO', $data['totalVideos']);
                                }

                                if($data['totalEvents'] > 0){
                                    $statsText[] = JText::sprintf('COM_COMMUNITY_DIGEST_EMAIL_USER_STARTED_EVENT', $data['totalEvents']);
                                }

                                if($data['totalGroups'] > 0){
                                    $statsText[] = JText::sprintf('COM_COMMUNITY_DIGEST_EMAIL_USER_STARTED_GROUP', $data['totalGroups']);
                                }

                                if($data['totalDiscussions'] > 0){
                                    $statsText[] = JText::sprintf('COM_COMMUNITY_DIGEST_EMAIL_USER_STARTED_DISCUSSION', $data['totalDiscussions']);
                                }

                                if($data['totalAnnouncements'] > 0){
                                    $statsText[] = JText::sprintf('COM_COMMUNITY_DIGEST_EMAIL_USER_STARTED_ANNOUNCEMENT', $data['totalAnnouncements']);
                                }

                                $statsText = implode($statsText,', ');

                                echo $statsText.'.';
                                ?>
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding: 15px 30px; border-bottom: 1px solid #eee">
                            <p style="font-size: 16px;"><?php echo JText::_('COM_COMMUNITY_DIGEST_EMAIL_INTERESTING_STRING'); ?>:</p>
                        </td>
                    </tr>
                </table>

                <?php if(isset($data['posts'])){
                    ?>
                    <table bgcolor="#fff" style="color:#81858A; width:100%; margin:0; padding:0 30px; border-spacing: 0;" id="email_post">
                        <tr>
                            <td>
                                <h3 style="color: #333; margin-top:25px; margin-bottom: 20px;"><?php echo JText::_('COM_COMMUNITY_DIGEST_EMAIL_POSTS'); ?></h3>
                            </td>
                        </tr>
                        <?php foreach($data['posts'] as $post) { ?>
                            <tr>
                                <?php if($config->get('digest_email_post_include_avatar')){ ?>
                                <td style="max-width: 100px">
                                    <img style="border-radius: 6px; margin-bottom: 20px;" src="<?php echo $post['userthumb']; ?>" width="100" alt="">
                                </td>
                                <?php } ?>
                                <td style="width: 100%; padding-left: 15px; vertical-align: top;">
                                    <p style="color:#333; font-size: 16px; margin-top: 0; margin-bottom: 5px;">
                                        <strong><?php echo $post['displayName']; ?></strong> <?php echo JText::_('COM_COMMUNITY_DIGEST_EMAIL_SAID_THIS'); ?> <a href="<?php echo $post['postlink']; ?>" style="color:#258BFF; text-decoration:none !important;"><?php echo JText::_('COM_COMMUNITY_DIGEST_EMAIL_POST'); ?></a>.
                                    </p>
                                    <p style="font-size: 14px; line-height: 20px; margin: 0;">
                                        <i><?php echo $post['message'];?></i>
                                    </p>
                                    <p style="font-size: 14px; line-height: 20px; margin: 5px 0 0;">
                                        <a href="<?php echo $post['postlink']; ?>" style="color:#258BFF; text-decoration:none !important;"><?php echo JText::_('COM_COMMUNITY_DIGEST_EMAIL_READ_FULL_STORY'); ?></a>
                                    </p>
                                </td>
                            </tr>
                        <?php } ?>
                        <tr>
                            <td style="padding-top:10px;"></td>
                        </tr>
                    </table>
                <?php } ?>

                <?php

                /*
                 * Photo section
                 */
                if(isset($data['photos'])){
                    ?>
                    <table bgcolor="#f5f5f5" style="color:#81858A; width:100%; margin:0; padding:0 30px; border-spacing: 0;" id="email_photo">
                        <tr>
                            <td>
                                <h3 style="color: #333; margin-top:25px; margin-bottom: 20px;"><?php echo JText::_('COM_COMMUNITY_DIGEST_EMAIL_PHOTOS'); ?></h3>
                            </td>
                        </tr>

                        <tr>
                            <td>
                                <?php foreach($data['photos'] as $photo){ ?>
                                    <a href="<?php echo $photo->link; ?>"><img style="border-radius: 6px; margin: 0 6px 3px 0;" src="<?php echo $photo->externalUrl; ?>" width="75" alt=""></a>
                                <?php } ?>
                            </td>
                        </tr>
                        <tr>
                            <td style="padding-top:20px;"></td>
                        </tr>
                    </table>
                <?php } ?>

                <?php
                if(isset($data['videos'])){
                    ?>
                    <table bgcolor="#fff" style="color:#81858A; width:100%; margin:0; padding:0 30px; border-spacing: 0;" id="email_video">
                        <tr>
                            <td style="max-width: 160px">
                                <h3 style="color: #333; margin-top:25px; margin-bottom: 20px;"><?php echo JText::_('COM_COMMUNITY_DIGEST_EMAIL_VIDEOS'); ?></h3>

                                <!-- Video item -->
                                <?php foreach($data['videos'] as $video){?>
                                    <div style="margin-bottom: 20px;">
                                        <div style="clear:both;">
                                            <?php if($config->get('digest_email_video_include_thumb')){ ?>
                                            <img style="float:left; border-radius: 6px; margin-right: 20px; margin-bottom: 10px;" src="<?php echo $video['thumbnail'] ?>" width="160" alt="">
                                            <?php } ?>
                                            <p style="color:#333; font-size: 16px; margin-top: 0; margin-bottom: 5px;">
                                                <strong><a href="<?php echo $video['link']; ?>" style="color:#258BFF; text-decoration:none !important;"><?php echo $video['title']; ?></a></strong>
                                            </p>
                                            <p style="font-size: 14px; line-height: 20px; margin: 0;"><?php echo $video['desc']; ?></p>
                                            <p style="font-size: 12px; line-height: 20px; margin: 8px 0 0;">
                                                <strong><?php echo JText::_('COM_COMMUNITY_DIGEST_EMAIL_POSTED_BY'); ?>:</strong>
                                                <a href="<?php echo $video['user']->getProfileLink(true); ?>" style="color:#258BFF; text-decoration:none !important;"><?php echo $video['user']->getDisplayName(); ?></a>
                                                <?php echo JText::_('COM_COMMUNITY_DIGEST_EMAIL_VIEW_VIDEO'); ?> <a href="<?php echo $video['link']; ?>" style="color:#258BFF; text-decoration:none !important;"><?php echo JText::_('COM_COMMUNITY_DIGEST_EMAIL_HERE'); ?></a>.
                                            </p>
                                        </div>
                                    </div>
                                <?php } ?>
                            </td>
                        </tr>
                    </table>
                <?php } ?>

                <?php
                //groups
                if(isset($data['groups'])){
                    ?>
                <table style="color:#81858A; border-bottom: 1px solid rgba(0,0,0,.08); background: rgba(118, 182, 255, 0.13); width:100%; margin:0; padding:0 30px; border-spacing: 0;" id="email_group">
                    <tr>
                        <td style="max-width: 160px">
                            <h3 style="color: #333; margin-top:25px; margin-bottom: 20px;"><?php echo JText::_('COM_COMMUNITY_DIGEST_EMAIL_GROUPS'); ?></h3>

                            <!-- Group item -->
                            <?php foreach($data['groups'] as $group){?>
                            <div style="clear:both;">
                                <?php if($config->get('digest_email_group_include_cover')){ ?>
                                <img style="border:4px solid rgba(0,0,0,.1); float:left; border-radius: 6px; margin-right: 20px; margin-bottom: 30px;" src="<?php echo $group['cover']; ?>" width="160" alt="">
                                <?php } ?>
                                <p style="color:#333; font-size: 16px; margin-top: 0; margin-bottom: 5px;">
                                    <strong><a href="<?php echo $group['link']; ?>" style="color:#258BFF; text-decoration:none !important;"><?php echo $group['title']; ?></a></strong>
                                </p>
                                <p style="font-size: 14px; line-height: 20px; margin: 0;"><?php echo $group['summary']; ?></p>
                                <p style="font-size: 12px; line-height: 20px; margin: 8px 0 0;">
                                    <strong><?php echo JText::_('COM_COMMUNITY_DIGEST_EMAIL_GROUP_OWNER'); ?>:</strong>
                                    <a href="<?php echo $group['user']->getProfileLink(true); ?>" style="color:#258BFF; text-decoration:none !important;"><?php echo $group['user']->getDisplayName(); ?></a>,
                                    <?php echo JText::_('COM_COMMUNITY_DIGEST_EMAIL_MEMBERS'); ?>: <?php echo $group['total_members']; ?>
                                    <!--Posts: <a href="#" style="color:#258BFF; text-decoration:none !important;">1</a>-->
                                </p>
                            </div>
                            <?php } ?>
                        </td>
                    </tr>
                </table>
                <?php } ?>

                <?php
                //Events
                if(isset($data['events'])){
                    ?>
                <table style="color:#81858A; background: rgba(118, 182, 255, 0.13); width:100%; margin:0; padding:0 30px; border-spacing: 0;" id="email_event">
                    <tr>
                        <td style="max-width: 160px">
                            <h3 style="color: #333; margin-top:25px; margin-bottom: 20px;"><?php echo JText::_('COM_COMMUNITY_DIGEST_EMAIL_EVENTS'); ?></h3>

                            <!-- Event item -->
                            <?php foreach($data['events'] as $event){?>
                                <div style="clear:both;">
                                    <?php if($config->get('digest_email_event_include_cover')){ ?>
                                    <img style="border:4px solid rgba(0,0,0,.1); float:left; border-radius: 6px; margin-right: 20px; margin-bottom: 30px;" src="<?php echo $event['cover']; ?>" width="160" alt="">
                                    <?php } ?>
                                    <p style="color:#333; font-size: 16px; margin-top: 0; margin-bottom: 5px;">
                                        <strong><a href="<?php echo $event['link']; ?>" style="color:#258BFF; text-decoration:none !important;"><?php echo $event['title']; ?></a></strong>
                                    </p>
                                    <p style="font-size: 14px; line-height: 20px; margin: 0;"><?php echo $event['summary']; ?></p>
                                    <p style="font-size: 12px; line-height: 20px; margin: 8px 0 0;">
                                        <strong><?php echo JText::_('COM_COMMUNITY_DIGEST_EMAIL_EVENT_OWNER'); ?>:</strong>
                                        <a href="<?php echo $event['user']->getProfileLink(true); ?>" style="color:#258BFF; text-decoration:none !important;"><?php echo $event['user']->getDisplayName(); ?></a>,
                                        <?php echo JText::_('COM_COMMUNITY_DIGEST_EMAIL_MEMBERS'); ?>: <?php echo $event['total_members']; ?>
                                        <!--Posts: <a href="#" style="color:#258BFF; text-decoration:none !important;">1</a>-->
                                    </p>
                                </div>
                            <?php } ?>
                        </td>
                    </tr>
                </table>
                <?php } ?>

                <?php
                if(isset($data['discussions']) && $data['discussions']){
                ?>
                <table bgcolor="#fff" style="color:#81858A; width:100%; border-bottom: 1px solid #eee; margin:0; padding:0 30px; border-spacing: 0;" id="email_discussion">
                    <tr>
                        <td style="max-width: 100px">
                            <h3 style="color: #333; margin-top:25px; margin-bottom: 20px;"><?php echo JText::_('COM_COMMUNITY_DIGEST_EMAIL_DISCUSSIONS'); ?></h3>

                            <!-- Discussion item -->
                            <?php foreach($data['discussions'] as $discussion){ ?>
                            <div style="clear:both;">
                                <?php if($config->get('digest_email_disscussion_announcement_avatar')){ ?>
                                <img style="float:left; border-radius: 6px; margin-right: 20px; margin-bottom: 30px;" src="<?php echo $discussion['user']->getThumbAvatar(); ?>" width="100" alt="">
                                <?php } ?>

                                <p style="color:#333; font-size: 16px; margin-top: 0; margin-bottom: 5px;">
                                    <strong><?php echo $discussion['user']->getDisplayName(); ?></strong> <?php echo JText::_('COM_COMMUNITY_DIGEST_EMAIL_CREATE_DISCUSSION'); ?> <a href="<?php echo $discussion['link']; ?>" style="color:#258BFF; text-decoration:none !important;"><?php echo $discussion['title']; ?></a>.
                                </p>
                                <p style="font-size: 14px; line-height: 20px; margin: 0;">
                                    <i><?php echo $discussion['message']; ?></i>
                                </p>
                                <p style="font-size: 14px; line-height: 20px; margin: 5px 0 0;">
                                    <a href="<?php echo $discussion['link']; ?>" style="color:#258BFF; text-decoration:none !important;"><?php echo JText::_('COM_COMMUNITY_DIGEST_EMAIL_VIEW_DISCUSSION'); ?></a>
                                </p>
                            </div>
                            <?php } ?>
                        </td>
                    </tr>
                </table>
                <?php } ?>

                <?php
                if(isset($data['announcements']) && $data['announcements']){
                ?>
                <table bgcolor="#fff" style="color:#81858A; width:100%; margin:0; padding:0 30px; border-spacing: 0;" id="email_announcement">
                    <tr>
                        <td style="max-width: 100px">
                            <h3 style="color: #333; margin-top:25px; margin-bottom: 20px;"><?php echo JText::_('COM_COMMUNITY_DIGEST_EMAIL_ANNOUNCEMENTS'); ?></h3>

                            <!-- Announcement item -->
                            <?php foreach($data['announcements'] as $announcement){ ?>
                                <div style="clear:both;">
                                    <?php if($config->get('digest_email_disscussion_announcement_avatar')){ ?>
                                    <img style="float:left; border-radius: 6px; margin-right: 20px; margin-bottom: 30px;" src="<?php echo $announcement['user']->getThumbAvatar(); ?>" width="100" alt="">
                                    <?php } ?>

                                    <p style="color:#333; font-size: 16px; margin-top: 0; margin-bottom: 5px;">
                                        <strong><?php echo $announcement['user']->getDisplayName(); ?></strong> <?php echo JText::_('COM_COMMUNITY_DIGEST_EMAIL_CREATE_ANNOUNCEMENT'); ?> <a href="<?php echo $announcement['link']; ?>" style="color:#258BFF; text-decoration:none !important;"><?php echo $announcement['title']; ?></a>.
                                    </p>
                                    <p style="font-size: 14px; line-height: 20px; margin: 0;">
                                        <i><?php echo $announcement['message']; ?></i>
                                    </p>
                                    <p style="font-size: 14px; line-height: 20px; margin: 5px 0 0;">
                                        <a href="<?php echo $announcement['link']; ?>" style="color:#258BFF; text-decoration:none !important;"><?php echo JText::_('COM_COMMUNITY_DIGEST_EMAIL_VIEW_ANNOUNCEMENT'); ?></a>
                                    </p>
                                </div>
                            <?php } ?>
                        </td>
                    </tr>
                </table>
                <?php } ?>
            </div>
            <!-- /content -->

        </td>
        <td style="margin: 0; padding: 0;"></td>
    </tr>
</table>

<!-- footer -->

<!-- /footer -->
</body>
</html>
