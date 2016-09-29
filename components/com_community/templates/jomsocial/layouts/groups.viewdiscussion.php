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

    $string = 'COM_COMMUNITY_GROUPS_DISCUSSION_CREATOR_TIME_LINK';

    if ($isTimeLapsed == 'lapse') {
        $string = 'COM_COMMUNITY_GROUPS_DISCUSSION_CREATOR_TIME_LINK_LAPSED';
    }
?>
<div class="joms-page">
    <?php echo $submenu;?>
    <h3 class="joms-page__title">
        <?php echo CActivities::truncateComplex($discussion->title, 60, true); ?>
    </h3>
    <?php if($canCreate) { ?>
    <button class="joms-button--primary joms-button--small joms-button--full-small" onclick="window.location='<?php echo CRoute::_('index.php?option=com_community&view=groups&groupid=' . $group->id . '&task=adddiscussion'); ?>';"><?php echo JText::_('COM_COMMUNITY_CREATE_ANOTHER_DISCUSSION') ?></button>
    <?php } ?>
</div>

<div class="joms-gap"></div>

<div class="joms-sidebar">
    <div class="joms-module__wrapper">
        <?php echo $filesharingHTML; ?>
    </div>
    <div class="joms-module__wrapper">
        <?php
            $keywords = explode(' ', $discussion->title);
            echo $this->view('groups')->modRelatedDiscussion($keywords);
        ?>
    </div>
</div>

<div class="joms-main">
    <div class="joms-page">

        <?php if ($config->get('enablesharethis') == 1) { ?>
        <!-- share button -->
        <button class="joms-button--smallest joms-button--primary joms-button--add-on-page" onclick="joms.api.pageShare('<?php echo CRoute::getURI(); ?>')"><?php echo JText::_('COM_COMMUNITY_SHARE'); ?></button>
        <?php } ?>

        <div class="joms-comment--bulletin">
            <div class="joms-comment__header">
                <div class="joms-avatar--comment <?php echo CUserHelper::onlineIndicator($creator); ?> ">
                    <a href="<?php echo CUrlHelper::userLink($creator->id); ?>">
                        <img src="<?php echo $creator->getThumbAvatar(); ?>" alt="<?php echo $creator->getDisplayName(); ?>" data-author="<?php echo $creator->id; ?>" />
                    </a>
                </div>
                <div class="joms-comment__meta">
                    <a href="<?php echo CUrlHelper::userLink($creator->id); ?>">
                        <?php echo JText::sprintf($creator->getDisplayName()); ?>
                    </a>
                    <span class="joms-comment__time">
                        <small>
                            <?php echo JText::sprintf(
                                $string,
                                $creatorLink,
                                $creator->getDisplayName(),
                                $discussion->created
                            ); ?>
                        </small>
                    </span>
                </div>
            </div>
            <div class="joms-gap"></div>
            <?php echo $discussion->message; ?>
            <?php
                //find out if there is any url here, if there is, run it via embedly when enabled
                $params = new CParameter($discussion->params);
            if($params->get('url') && $config->get('enable_embedly')){
            ?>
                <a href="<?php echo $params->get('url'); ?>" class="embedly-card" data-card-controls="0" data-card-recommend="0" data-card-theme="<?php echo $config->get('enable_embedly_card_template'); ?>" data-card-align="<?php echo $config->get('enable_embedly_card_position') ?>"><?php echo JText::_('COM_COMMUNITY_EMBEDLY_LOADING');?></a>
            <?php } ?>
        </div>

        <div class="joms-gap"></div>
        <h5 class="joms-text--title"><?php echo JText::_('COM_COMMUNITY_REPLIES'); ?></h5>
        <div class="joms-stream__status--mobile">
            <a href="javascript:" onclick="joms.api.streamShowComments('<?php echo $discussion->id ?>', 'discussions');">
                <span class="joms-comment__counter--<?php echo $discussion->id; ?>"><?php echo $wallCount; ?></span>
                <svg viewBox="0 0 16 16" class="joms-icon">
                    <use xlink:href="<?php echo CRoute::getURI(); ?>#joms-icon-bubble"></use>
                </svg>
            </a>
        </div>
        <div style="display:none"><?php echo $wallViewAll; ?></div>
        <?php echo $wallContent; ?>
        <?php echo $wallForm; ?>
        <script>
            (function( w ) {
                w.joms_queue || (w.joms_queue = []);
                w.joms_queue.push(function( $ ) {
                    $('.joms-js--comments').prepend( $('.joms-js--more-comments').parent().html() );
                });
            })( window );
        </script>
    </div>
</div>
