<?php
/**
* @copyright (C) 2013 iJoomla, Inc. - All rights reserved.
* @license GNU General Public License, version 2 (http://www.gnu.org/licenses/gpl-2.0.html)
* @author iJoomla.com <webmaster@ijoomla.com>
* @url https://www.jomsocial.com/license-agreement
* The PHP code portions are distributed under the GPL license. If not otherwise stated, all images, manuals, cascading style sheets, and included JavaScript *are NOT GPL, and are released under the IJOOMLA Proprietary Use License v1.0
* More info at https://www.jomsocial.com/license-agreement
*/
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');
?>
<script>
joms.jQuery(document).ready(function($){
  $(document).on("click", ".joms-share-status-privacy ul.dropdown-menu a", function(e) {
        e.preventDefault();
        var val = $(this).data('option-value');
        var icon = $(this).find('i').attr('class');
        $('input[name="joms-postbox-privacy"]').val(val);
        $(this).parents('ul.dropdown-menu').siblings('button').find('span.dropdown-value i').attr('class', icon);
    });
  $(document).on("click","#cWindowAction button.btn-primary",function(e){
      var data = {
                  msg:joms.jQuery("div#joms-share-popup textarea#joms-write-status").val(),
                  privacy:joms.jQuery('div#joms-share-popup input[name="joms-postbox-privacy"]').val()
                };
        data =  JSON.stringify(data);
      joms.share.add(<?php echo $this->act->id;?>,data);
  });
});
</script>

    <div class="joms-share-status-popup" style="position:relative">
        <textarea class="joms-textarea joms-postbox-input" name="joms-write-status" data-minlength="0" data-maxlength="200" id="joms-write-status" placeholder="<?php echo JText::_('COM_COMMUNITY_SAY_SOMETHING')?>" style="min-height: 60px"></textarea>
        <div id="joms-write-status-charcounter" class="joms-postbox-charcount"></div>

      <script>
        joms.jQuery(function($) {
          var maxchar = +'<?php echo CFactory::getConfig()->get("statusmaxchar");?>',
              el = $('#joms-write-status'),
              cn = $('#joms-write-status-charcounter');
          el.off().on( 'keyup', function() {
            var text = el.val(),
                counter = Math.max( 0, maxchar - text.length );
            cn.html( counter );
          }).trigger('keyup');
        });
      </script>

    </div>
    <div class="joms-share-status-preview clearfix"></div>


    <?php if ($this->act->app == 'groups.discussion') { ?>
        <!-- Group discussion -->
      <div class="joms-media">

          <?php $link = CRoute::_('index.php?option=com_community&view=groups&task=viewdiscussion&groupid=' . $this->data->id. '&topicid=' . $this->act->cid );?>
          <a href="<?php echo $link; ?>"><?php echo $this->data->title; ?></a>
          <p><?php echo CActivities::format($this->act->content); ?></p>
          <div class="content-details"><?php echo $this->data->name; ?></div>

      </div>

    <?php } else { ?>

    <div class="joms-media">
        <?php if ( $this->act->app == 'profile' || $this->act->app == 'videos' || $this->act->app == 'groups.wall' || $this->act->app == 'events.wall') { ?>
        <p>
            <?php
                $title = CActivities::format($this->act->title, $mood);
                echo CActivities::shorten($title, $this->act->id, 0, $config->getInt('streamcontentlength'));
            ?>
            <?php if (!empty($this->act->location)) { //show location if needed?>
                <span class="joms-status-location"> -
                    <a href="javascript:" onclick="joms.api.locationView('<?php echo $this->act->get('id'); ?>');">
                        <?php echo $this->act->location; ?>
                    </a>
                </span>
            <?php } ?>
        </p>

        <?php
            // profile avatar upload
            }elseif($this->act->app == 'profile.avatar.upload'){
        ?>
            <div class="joms-avatar single">
                <img src="<?php echo JURI::root() . $this->act->params->get('attachment'); ?>" alt="avatar" />
            </div>
        <?php }elseif($this->act->app == 'videos.linking') {

            $video = JTable::getInstance('Video', 'CTable');

            if($video->load($this->act->cid)) {
                $video->isValid();
            } else {
                $video = false;
            }
            ?>

            <div class="joms-media--video joms-js--video"
                 data-type="<?php echo $video->type; ?>"
                 data-id="<?php echo $video->id; ?>"
                 data-path="<?php echo ($video->type == 'file') ? CStorage::getStorage($video->storage)->getURI($video->path) : $video->path; ?>">

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

        <p class="joms-share-status-content"><?php echo $this->act->content; ?></p>

        <div class="joms-share-status-action">
          <span class="joms-share-status-username"> by <?php echo CFactory::getUser($this->act->actor)->getDisplayName();?></span>
        </div>
    </div>

    <?php } ?>


