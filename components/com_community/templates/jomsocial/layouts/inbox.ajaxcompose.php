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

$timeZone = new DateTimeZone(JFactory::getConfig()->get('offset'));
$jnow = new JDate();
$jnow->setTimezone($timeZone);

$params = JComponentHelper::getParams('com_media');
$fileExtensions = $params->get('upload_extensions');

$config = CFactory::getConfig();
$enableFileSharing = (int) $config->get('message_file_sharing');
$maxFileSize = (int) $config->get('message_file_maxsize');

?>

<div class="joms-popup__content">
    <div class="joms-stream__header" style="padding:0">
        <div class= "joms-avatar--stream <?php echo CUserHelper::onlineIndicator($user); ?>"><img src="<?php echo $user->getThumbAvatar(); ?>" alt="<?php echo $user->getDisplayName(); ?>" ></div>
        <div class="joms-stream__meta">
            <span><?php echo $user->getDisplayName(); ?></span>
            <span class="joms-stream__time">
                <small><?php echo $jnow->format( JText::_('DATE_FORMAT_LC2'), true ); ?></small>
            </span>
        </div>
    </div>
</div>
<div class="joms-popup__content">
    <form action="" method="post">
        <div class="joms-form__group">
            <span class="small"></span>
            <div class="joms-popup__hide" data-ui-object="popup-message" style="color:red"></div>
        </div>
        <div class="joms-form__group">
            <span class="small"><?php echo JText::_('COM_COMMUNITY_COMPOSE_SUBJECT'); ?></span>
            <input type="text" class="joms-input" name="subject" value="<?php echo empty($subject) ? '' : $subject; ?>">
        </div>
        <div class="joms-form__group">
            <span class="small"><?php echo JText::_('COM_COMMUNITY_COMPOSE_MESSAGE'); ?></span>
            <div class="joms-js--pm-message" style="position:relative">
                <div class="joms-textarea__wrapper">
                    <textarea class="joms-textarea"><?php echo empty($body) ? '' : $body; ?></textarea>
                    <input type="hidden" class="joms-textarea__hidden" name="body">
                    <div class="joms-textarea joms-textarea__attachment">
                        <button onclick="joms.view.comment.removeAttachment(this);">×</button>
                        <div class="joms-textarea__attachment--loading"><img src="<?php echo JURI::root(true); ?>/components/com_community/assets/ajax-loader.gif" alt="loader" ></div>
                        <div class="joms-textarea__attachment--thumbnail"><img src="#" alt="attachment"></div>
                    </div>
                </div>
                <svg viewBox="0 0 16 16" class="joms-icon joms-icon--add" onclick="joms.view.comment.addAttachment(this);"
                        style="position:absolute;top:10px;right:10px">
                    <use xlink:href="#joms-icon-camera"></use>
                </svg>

                <?php if ($enableFileSharing) { ?>
                <svg viewBox="0 0 16 16" class="joms-icon joms-icon--add"
                     onclick="joms.view.comment.addAttachment(this, 'file', { type: 'message', max_file_size: '<?php echo $maxFileSize; ?>', exts: '<?php echo $fileExtensions ?>' });" style="position:absolute;top:10px;right:30px">
                    <use xlink:href="<?php echo CRoute::getURI(); ?>#joms-icon-file-zip"></use>
                </svg>
                <?php } ?>

            </div>
        </div>
        <input type="hidden" value="<?php echo $user->id; ?>" name="to">
    </form>
</div>
