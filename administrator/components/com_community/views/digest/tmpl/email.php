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
    <table class="container-table" bgcolor="#e5e5e5" style="width: 100%; margin: 0 0 30px; padding: 20px;">
        <tr style="margin: 0; padding: 0;">
            <td style="margin: 0; padding: 0;"></td>
            <td bgcolor="#FFFFFF" style="clear: both !important; display: block !important; max-width: 600px !important; Margin: 0 auto; padding: 0;">

                <!-- content -->
                <div style="display: block; max-width: 600px; margin: 0 auto; padding: 0; box-shadow: 0 15px 20px rgba(0, 0, 0, 0.04);">
                    <table bgcolor="#76B6FF" style="color: white; width: 100%; margin: 0; padding: 60px 30px; text-align:center;">
                        <tr>
                             <td>
                                <h1>LOGO</h1>
                             </td>
                         </tr>
                    </table>

                    <table bgcolor="#fff" style="color:#81858A; width:100%; margin:0; border-spacing: 0;">
                        <tr>
                            <td style="padding:30px 30px 0;">
                                <p style="color:#333; font-size: 16px; line-height: 24px">Hello <strong>User X</strong>,</p>
                                <p style="font-size: 16px; line-height: 24px">We notice you haven't logged in for X days on <a href="#" style="color:#258BFF; text-decoration:none !important;">sitename</a>. Here's what you missed:</p>
                            </td>
                        </tr>

                        <tr>
                            <td style="padding:0 30px; background: #f5f5f5">
                                <p style="font-size: 14px; line-height: 22px">Our users created <strong>123 posts</strong>, added <strong>77 new photos</strong>, shared <strong>9 new videos</strong>, started <strong>3 new groups</strong>, announced <strong>6 new events</strong> and started <strong>7 new discussions.</strong></p>
                            </td>
                        </tr>

                        <tr>
                            <td style="padding:0 30px; border-bottom: 1px solid #eee">
                                <p style="font-size: 16px;">Here are the most interesting ones we think you'd like:</p>
                            </td>
                        </tr>
                    </table>

                    <?php if(isset($data['posts'])){
                        ?>
                    <table bgcolor="#fff" style="color:#81858A; width:100%; margin:0; padding:0 30px; border-spacing: 0;">
                        <tr>
                            <td>
                                <h3 style="color: #333">Posts</h3>
                            </td>
                        </tr>
                        <?php foreach($data['posts'] as $post) { ?>
                        <tr>
                            <td style="max-width: 100px">
                                <img style="border-radius: 6px; margin-bottom: 20px;" src="<?php echo $post['userthumb']; ?>" width="100" alt="">
                            </td>
                            <td style="width: 100%; padding-left: 15px; vertical-align: top;">
                                <p style="color:#333; font-size: 16px; margin-top: 0; margin-bottom: 5px;">
                                    <strong><?php echo $post['displayName']; ?></strong> said in this <a href="<?php echo $post['postlink']; ?>" style="color:#258BFF; text-decoration:none !important;">post</a>.
                                </p>
                                <p style="font-size: 14px; line-height: 20px; margin: 0;">
                                    <i><?php echo $post['message'];?></i>
                                </p>
                                <p style="font-size: 14px; line-height: 20px; margin: 5px 0 0;">
                                    <a href="<?php echo $post['postlink']; ?>" style="color:#258BFF; text-decoration:none !important;">read the full story</a>
                                </p>
                            </td>
                        </tr>
                        <?php } ?>
                    </table>
                    <?php } ?>

                    <?php

                    /*
                     * Photo section
                     */
                    if(isset($data['photos'])){
                    ?>
                    <table bgcolor="#f5f5f5" style="color:#81858A; width:100%; margin:0; padding:0 30px; border-spacing: 0;">
                        <tr>
                            <td>
                                <h3 style="color: #333">Photos</h3>
                            </td>
                        </tr>

                        <tr>
                            <td>
                                <?php foreach($data['photos'] as $photo){ ?>
                                <img style="border-radius: 6px; margin: 0 6px 3px 0;" src="<?php echo $photo->externalUrl; ?>" width="75" alt="">
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
                    <table bgcolor="#fff" style="color:#81858A; width:100%; margin:0; padding:0 30px; border-spacing: 0;">
                        <tr>
                            <td style="max-width: 160px">
                                <h3 style="color: #333">Videos</h3>
                                
                                <!-- Video item -->
                                <?php foreach($data['videos'] as $video){?>
                                <div style="clear:both;">
                                    <img style="float:left; border-radius: 6px; margin-right: 20px; margin-bottom: 30px;" src="<?php echo $video['thumbnail'] ?>" width="160" alt="">
                                    <p style="color:#333; font-size: 16px; margin-top: 0; margin-bottom: 5px;">
                                        <strong><?php echo $video['title']; ?></strong>
                                    </p>
                                    <p style="font-size: 14px; line-height: 20px; margin: 0;"><?php echo $video['desc']; ?></p>
                                    <p style="font-size: 12px; line-height: 20px; margin: 8px 0 0;">
                                        <strong>Posted by:</strong>
                                        <a href="#" style="color:#258BFF; text-decoration:none !important;"><?php echo $video['user']->getDisplayName(); ?></a>
                                        view video <a href="<?php echo $video['link']; ?>" style="color:#258BFF; text-decoration:none !important;">here</a>.
                                    </p>
                                </div>
                                <?php } ?>
                            </td>
                        </tr>
                    </table>
                    <?php } ?>

                    <table style="color:#81858A; border-bottom: 1px solid rgba(0,0,0,.08); background: rgba(118, 182, 255, 0.13); width:100%; margin:0; padding:0 30px; border-spacing: 0;">
                        <tr>
                            <td style="max-width: 160px">
                                <h3 style="color: #333">Groups</h3>
                                
                                <!-- Group item -->
                                <div style="clear:both;">
                                    <img style="float:left; border-radius: 6px; margin-right: 20px; margin-bottom: 30px;" src="https://placehold.it/160x100" width="160" alt="">
                                    <p style="color:#333; font-size: 16px; margin-top: 0; margin-bottom: 5px;">
                                        <strong>Very awesome group of mine</strong>
                                    </p>
                                    <p style="font-size: 14px; line-height: 20px; margin: 0;">This is multiplelines description of the group trimmed to the size that will fit the email template just perfectly.</p>
                                    <p style="font-size: 12px; line-height: 20px; margin: 8px 0 0;">
                                        <strong>Group Owner:</strong>
                                        <a href="#" style="color:#258BFF; text-decoration:none !important;">Veronica</a>,
                                        Members: <a href="#" style="color:#258BFF; text-decoration:none !important;">9</a>,
                                        Posts: <a href="#" style="color:#258BFF; text-decoration:none !important;">1</a>
                                    </p>
                                </div>
                            </td>
                        </tr>
                    </table>

                    <table style="color:#81858A; background: rgba(118, 182, 255, 0.13); width:100%; margin:0; padding:0 30px; border-spacing: 0;">
                        <tr>
                            <td style="max-width: 160px">
                                <h3 style="color: #333">Events</h3>
                                
                                <!-- Event item -->
                                <div style="clear:both;">
                                    <img style="float:left; border-radius: 6px; margin-right: 20px; margin-bottom: 30px;" src="https://placehold.it/160x100" width="160" alt="">
                                    <p style="color:#333; font-size: 16px; margin-top: 0; margin-bottom: 5px;">
                                        <strong>Very awesome event of mine</strong>
                                    </p>
                                    <p style="font-size: 14px; line-height: 20px; margin: 0;">This is multiplelines description of the event trimmed to the size that will fit the email template just perfectly.</p>
                                    <p style="font-size: 12px; line-height: 20px; margin: 8px 0 0;">
                                        <strong>Event Owner:</strong>
                                        <a href="#" style="color:#258BFF; text-decoration:none !important;">Veronica</a>,
                                        Members: <a href="#" style="color:#258BFF; text-decoration:none !important;">9</a>,
                                        Posts: <a href="#" style="color:#258BFF; text-decoration:none !important;">1</a>
                                    </p>
                                </div>
                            </td>
                        </tr>
                    </table>

                    <table bgcolor="#fff" style="color:#81858A; width:100%; border-bottom: 1px solid #eee; margin:0; padding:0 30px; border-spacing: 0;">
                        <tr>
                            <td style="max-width: 100px">
                                <h3 style="color: #333">Discussions</h3>

                                <!-- Discussion item -->
                                <div style="clear:both;">
                                    <img style="float:left; border-radius: 6px; margin-right: 20px; margin-bottom: 30px;" src="https://placehold.it/100" width="100" alt="">

                                    <p style="color:#333; font-size: 16px; margin-top: 0; margin-bottom: 5px;">
                                        <strong>Leonardo</strong> replied to discussion <a href="#" style="color:#258BFF; text-decoration:none !important;">Some title</a>.
                                    </p>
                                    <p style="font-size: 14px; line-height: 20px; margin: 0;">
                                        <i>Krang got a new body.</i>
                                    </p>
                                    <p style="font-size: 14px; line-height: 20px; margin: 5px 0 0;">
                                        <a href="#" style="color:#258BFF; text-decoration:none !important;">view discussion</a>
                                    </p>
                                </div>
                            </td>
                        </tr>
                    </table>

                    <table bgcolor="#fff" style="color:#81858A; width:100%; margin:0; padding:0 30px; border-spacing: 0;">
                        <tr>
                            <td style="max-width: 100px">
                                <h3 style="color: #333">Announcements</h3>
                                
                                <!-- Announcement item -->
                                <div style="clear:both;">
                                    <img style="float:left; border-radius: 6px; margin-right: 20px; margin-bottom: 30px;" src="https://placehold.it/100" width="100" alt="">

                                    <p style="color:#333; font-size: 16px; margin-top: 0; margin-bottom: 5px;">
                                        <strong>Leonardo</strong> replied to announcement <a href="#" style="color:#258BFF; text-decoration:none !important;">Some title</a>.
                                    </p>
                                    <p style="font-size: 14px; line-height: 20px; margin: 0;">
                                        <i>Krang got a new body.</i>
                                    </p>
                                    <p style="font-size: 14px; line-height: 20px; margin: 5px 0 0;">
                                        <a href="#" style="color:#258BFF; text-decoration:none !important;">view announcement</a>
                                    </p>
                                </div>
                            </td>
                        </tr>
                    </table>
                </div>
                <!-- /content -->

            </td>
            <td style="margin: 0; padding: 0;"></td>
        </tr>
    </table>

    <!-- footer -->
    <table bgcolor="#fff" style="color:#81858A; width:100%; margin:0; padding:0 30px; border-spacing: 0;">
        <tr>
            <td style="margin: 0; padding: 0;"></td>
            <td>
                <div style="display: block; max-width: 600px; margin: 0 auto; padding: 30px;">
                    <table style="color:#81858A; width:100%; margin:0; padding:0 30px; border-spacing: 0;">
                        <tr>
                            <td style="text-align:center;">
                                <p style="font-size: 12px; line-height: 20px; margin: 0;">
                                    You're receving this newsletter because you subscribed.<br />
                                    <a href="#" style="color:#258BFF; text-decoration:none !important;">unsubscribe</a>
                                    if you don't want to receive it.
                                </p>
                            </td>
                        </tr>
                    </table>
                </div>
            </td>
            <td style="margin: 0; padding: 0;"></td>
        </tr>
    </table>
    <!-- /footer -->
</body>
</html>
