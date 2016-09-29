<?php
/**
 * @copyright (C) 2013 iJoomla, Inc. - All rights reserved.
 * @license GNU General Public License, version 2 (http://www.gnu.org/licenses/gpl-2.0.html)
 * @author iJoomla.com <webmaster@ijoomla.com>
 * @url https://www.jomsocial.com/license-agreement
 * The PHP code portions are distributed under the GPL license. If not otherwise stated, all images, manuals, cascading style sheets, and included JavaScript *are NOT GPL, and are released under the IJOOMLA Proprietary Use License v1.0
 * More info at https://www.jomsocial.com/license-agreement
 */
defined('_JEXEC') OR DIE();

// Event categories.
$rawEventCategories = CFactory::getModel('events')->getCategories();
$eventCategories = array();
if ( count($rawEventCategories) >= 1 ) {
  foreach ($rawEventCategories as $index => $value) {
    $eventCategories[] = array(
      'id' => $value->id,
      'name' => JText::_( $value->name )
    );
  }
}

// Video categories.
$rawVideoCategories = CFactory::getModel('videos')->getAllCategories();
$rawVideoCategories = CCategoryHelper::getCategories($rawVideoCategories);
foreach ( $rawVideoCategories as $key => $row ) {
  $nodeText[$key]  = $row['nodeText'];
}
array_multisort(array_map('strtolower', $nodeText), SORT_ASC, $rawVideoCategories);

$videoCategories = array();
if ( count($rawVideoCategories) >= 1 ) {
  foreach ($rawVideoCategories as $index => $value) {
    $videoCategories[] = array(
      'id' => $value['id'],
      'name' => JText::_( $value['name'] ),
      'parent' => $value['parent']
    );
  }
}

?>

<script>
  joms || (joms = {});
  joms.constants || (joms.constants = {});
  joms.language || (joms.language = {});

  joms.constants.uid                          = '<?php echo $my->id; ?>';

  joms.constants.album                        = <?php echo json_encode( $album ); ?>;
  joms.constants.eventCategories              = <?php echo json_encode( $eventCategories ); ?>;
  joms.constants.videoCategories              = <?php echo json_encode( $videoCategories ); ?>;
  joms.constants.customActivities             = <?php echo json_encode( CActivityStream::getCustomActivities() ); ?>;
  joms.constants.videocreatortype             = '<?php echo VIDEO_USER_TYPE ?>';

  joms.constants.juri || (joms.constants.juri = {});
  joms.constants.juri.base                    = '<?php echo JURI::base(); ?>';
  joms.constants.juri.root                    = '<?php echo JURI::root(); ?>';

  joms.constants.settings || (joms.constants.settings = {});
  joms.constants.settings.isProfile           = <?php echo ($type == 'profile') ? 1 : 0; ?>;
  joms.constants.settings.isMyProfile         = <?php echo ($my->id == $target) ? 1 : 0; ?>;
  joms.constants.settings.isGroup             = <?php echo ($type == 'groups') ? 1 : 0; ?>;
  joms.constants.settings.isEvent             = <?php echo ($type == 'events') ? 1 : 0; ?>;
  joms.constants.settings.isAdmin             = <?php echo (COwnerHelper::isCommunityAdmin() && $target == $my->id) ? 1 : 0; ?>;

  joms.constants.conf || (joms.constants.conf = {});

  joms.constants.conf.statusmaxchar           = +'<?php echo CFactory::getConfig()->get("statusmaxchar"); ?>';
  joms.constants.conf.profiledefaultprivacy   = +'<?php echo CFactory::getUser($my->id)->getParams()->get("privacyProfileView"); ?>';
  joms.constants.conf.maxvideouploadsize      = +'<?php echo CFactory::getConfig()->get("maxvideouploadsize"); ?>';
  joms.constants.conf.maxuploadsize           = +'<?php echo CFactory::getConfig()->get("maxuploadsize"); ?>';
  joms.constants.conf.enablephotos            = +'<?php echo $permission->enablephotos; ?>';
  joms.constants.conf.enablephotosgif         = +'<?php echo CFactory::getConfig()->get("enable_animated_gif"); ?>';
  joms.constants.conf.enablevideos            = +'<?php echo $permission->enablevideos; ?>';
  joms.constants.conf.enablevideosupload      = +'<?php echo $permission->enablevideosupload; ?>';
  joms.constants.conf.enablevideosmap         = +'<?php echo CFactory::getConfig()->get("videosmapdefault");?>';
  joms.constants.conf.enableevents            = +'<?php echo $permission->enableevents; ?>';
  joms.constants.conf.enablecustoms           = +'<?php echo CFactory::getConfig()->get("custom_activity") ? "1" : "0"; ?>';
  joms.constants.conf.limitphoto              = +'<?php echo CFactory::getConfig()->get("limit_photos_perday");?>';
  joms.constants.conf.uploadedphoto           = +'<?php echo CFactory::getModel("photos")->getTotalToday($my->id); ?>';
  joms.constants.conf.enablemood              = +'<?php echo CFactory::getConfig()->get("enablemood"); ?>';
  joms.constants.conf.enablelocation          = +'<?php echo CFactory::getConfig()->get("streamlocation"); ?>';
  joms.constants.conf.limitvideo              = +'<?php echo CFactory::getConfig()->get("limit_videos_perday");?>';
  joms.constants.conf.uploadedvideo           = +'<?php echo CFactory::getModel("videos")->getTotalToday($my->id); ?>';
  joms.constants.conf.limitevent              = +'<?php echo CFactory::getConfig()->get("limit_events_perday");?>';
  joms.constants.conf.createdevent            = +'<?php echo CFactory::getModel("events")->getTotalToday($my->id); ?>';
  joms.constants.conf.eventshowampm           = +'<?php echo CFactory::getConfig()->get("eventshowampm");?>';
  joms.constants.conf.firstday                = +'<?php echo CFactory::getConfig()->get("event_calendar_firstday") == "Monday" ? 1 : 0; ?>';

  joms.constants.postbox || (joms.constants.postbox = {});
  joms.constants.postbox.attachment           = {};
  joms.constants.postbox.attachment.element   = '<?php echo $type ?>';
  joms.constants.postbox.attachment.target    = '<?php echo $target ?>';

  <?php if(JFactory::getApplication()->input->get('view') == 'profile'){ ?>
  joms.constants.postbox.attachment.filter   = 'active-profile';
  <?php } ?>

  // Custom moods
  joms.constants.moods = [];<?php

    // Render custom moods.
    foreach ($moods as $key => $value) {
        if(!$value->custom) continue;
        $mood = array(
            'id'          => $value->id,
            'title'       => $value->title,
            'description' => $value->description,
            'custom'      => $value->custom ? true : false,
            'image'       => $value->custom ? $value->image : null
        );

        echo PHP_EOL . '  joms.constants.moods.push(' . json_encode($mood) . ');';
    }

  ?>


  joms.language.cancel                        = '<?php echo addslashes( JText::_("COM_COMMUNITY_CANCEL") ); ?>';
  joms.language.saving                        = '<?php echo addslashes( JText::_("COM_COMMUNITY_SAVING") ); ?>';
  joms.language.yes                           = '<?php echo addslashes( JText::_("COM_COMMUNITY_YES") ); ?>';
  joms.language.no                            = '<?php echo addslashes( JText::_("COM_COMMUNITY_NO") ); ?>';
  joms.language.at                            = '<?php echo addslashes( JText::_("COM_COMMUNITY_AT") ); ?>';
  joms.language.next                          = '<?php echo addslashes( JText::_("COM_COMMUNITY_NEXT") ); ?>';
  joms.language.prev                          = '<?php echo addslashes( JText::_("COM_COMMUNITY_PREV") ); ?>';
  joms.language.select_category               = '<?php echo addslashes( JText::_("COM_COMMUNITY_SELECT_CATEGORY") ); ?>';
  joms.language.and                           = '<?php echo addslashes( JText::_("COM_COMMUNITY_AND") ); ?>';

  joms.language.status || (joms.language.status = {});
  joms.language.status['status_hint']         = '<?php echo addslashes( JText::_("COM_COMMUNITY_STATUS_MESSAGE_HINT") ); ?>';
  joms.language.status['photo_hint']          = '<?php echo addslashes( JText::_("COM_COMMUNITY_STATUS_PHOTO_HINT") ); ?>';
  joms.language.status['photos_hint']         = '<?php echo addslashes( JText::_("COM_COMMUNITY_STATUS_PHOTOS_HINT") ); ?>';
  joms.language.status['video_hint']          = '<?php echo addslashes( JText::_("COM_COMMUNITY_STATUS_VIDEO_HINT") ); ?>';
  joms.language.status['event_hint']          = '<?php echo addslashes( JText::_("COM_COMMUNITY_STATUS_EVENT_HINT") ); ?>';
  joms.language.status['custom_hint']         = '<?php echo addslashes( JText::_("COM_COMMUNITY_STATUS_MESSAGE_HINT") ); ?>';
  joms.language.status.mood                   = '<?php echo addslashes( JText::_("COM_COMMUNITY_POSTBOX_STATUS_MOOD") ); ?>';
  joms.language.status.remove_mood_button     = '<?php echo addslashes( JText::_("COM_COMMUNITY_POSTBOX_STATUS_REMOVE_MOOD_BUTTON") ); ?>';
  joms.language.status.location               = '<?php echo addslashes( JText::_("COM_COMMUNITY_POSTBOX_STATUS_LOCATION") ); ?>';

  joms.language.postbox || (joms.language.postbox = {});
  joms.language.postbox.status                = '<?php echo addslashes( JText::_("COM_COMMUNITY_POSTBOX_STATUS") ); ?>';
  joms.language.postbox.photo                 = '<?php echo addslashes( JText::_("COM_COMMUNITY_POSTBOX_PHOTO") ); ?>';
  joms.language.postbox.video                 = '<?php echo addslashes( JText::_("COM_COMMUNITY_POSTBOX_VIDEO") ); ?>';
  joms.language.postbox.event                 = '<?php echo addslashes( JText::_("COM_COMMUNITY_POSTBOX_EVENT") ); ?>';
  joms.language.postbox.custom                = '<?php echo addslashes( JText::_("COM_COMMUNITY_POSTBOX_CUSTOM") ); ?>';
  joms.language.postbox.post_button           = '<?php echo addslashes( JText::_("COM_COMMUNITY_POSTBOX_POST_BUTTON") ); ?>';
  joms.language.postbox.cancel_button         = '<?php echo addslashes( JText::_("COM_COMMUNITY_POSTBOX_CANCEL_BUTTON") ); ?>';
  joms.language.postbox.upload_button         = '<?php echo addslashes( JText::_("COM_COMMUNITY_POSTBOX_UPLOAD_BUTTON") ); ?>';

  joms.language.photo || (joms.language.photo = {});
  joms.language.photo.batch_notice            = '<?php echo addslashes( JText::_("COM_COMMUNITY_PHOTO_BATCH_NOTICE") ); ?>';
  joms.language.photo.upload_button           = '<?php echo addslashes( JText::_("COM_COMMUNITY_POSTBOX_PHOTO_UPLOAD_BUTTON") ); ?>';
  joms.language.photo.upload_button_more      = '<?php echo addslashes( JText::_("COM_COMMUNITY_POSTBOX_PHOTO_UPLOAD_BUTTON_MORE") ); ?>';
  joms.language.photo.upload_limit_exceeded   = '<?php echo addslashes( JText::_("COM_COMMUNITY_PHOTO_UPLOAD_LIMIT_EXCEEDED") ); ?>';
  joms.language.photo.max_upload_size_error   = '<?php echo addslashes( JText::sprintf("COM_COMMUNITY_VIDEOS_IMAGE_FILE_SIZE_EXCEEDED_MB", CFactory::getConfig()->get("maxuploadsize")) ); ?>';
  joms.language.photo.upload_button_2         = '<?php echo addslashes( JText::_("COM_COMMUNITY_POSTBOX_PHOTO_UPLOAD_BUTTON_2") ); ?>';
  joms.language.photo.gif_upload_button       = '<?php echo addslashes( JText::_("COM_COMMUNITY_POSTBOX_GIF_UPLOAD_BUTTON") ); ?>';

  joms.language.video || (joms.language.video = {});
  joms.language.video.location                = '<?php echo addslashes( JText::_("COM_COMMUNITY_POSTBOX_STATUS_LOCATION") ); ?>';
  joms.language.video.category_label          = '<?php echo addslashes( JText::_("COM_COMMUNITY_POSTBOX_VIDEO_CATEGORY_LABEL") ); ?>';
  joms.language.video.category_notice         = '<?php echo addslashes( JText::_("COM_COMMUNITY_POSTBOX_VIDEO_CATEGORY_NOTICE") ); ?>';
  joms.language.video.url_notice              = '<?php echo addslashes( JText::_("COM_COMMUNITY_POSTBOX_VIDEO_URL_NOTICE") ); ?>';
  joms.language.video.share_button            = '<?php echo addslashes( JText::_("COM_COMMUNITY_POSTBOX_VIDEO_SHARE_BUTTON") ); ?>';
  joms.language.video.link_hint               = '<?php echo addslashes( JText::_("COM_COMMUNITY_POSTBOX_VIDEO_LINK_HINT") ); ?>';
  joms.language.video.upload_title            = '<?php echo addslashes( JText::_("COM_COMMUNITY_POSTBOX_VIDEO_UPLOAD_TITLE") ); ?>';
  joms.language.video.upload_button           = '<?php echo addslashes( JText::_("COM_COMMUNITY_POSTBOX_VIDEO_UPLOAD_BUTTON") ); ?>';
  joms.language.video.upload_hint             = '<?php echo addslashes( JText::_("COM_COMMUNITY_POSTBOX_VIDEO_UPLOAD_HINT") ); ?>';
  joms.language.video.upload_maxsize          = '<?php echo addslashes( JText::_("COM_COMMUNITY_POSTBOX_VIDEO_UPLOAD_MAXSIZE") ); ?>';
  joms.language.video.upload_limit_exceeded   = '<?php echo addslashes( JText::_("COM_COMMUNITY_VIDEO_UPLOAD_LIMIT_EXCEEDED") ); ?>';
  joms.language.video.select_category         = '<?php echo addslashes( JText::_("COM_COMMUNITY_VIDEO_SELECT_CATEGORY") ); ?>';
  joms.language.video.invalid_url             = '<?php echo addslashes( JText::_("COM_COMMUNITY_VIDEO_INVALID_URL") ); ?>';

  joms.language.event || (joms.language.event = {});
  joms.language.event.title_hint              = '<?php echo addslashes( JText::_("COM_COMMUNITY_POSTBOX_EVENT_TITLE_HINT") ); ?>';
  joms.language.event.date_and_time           = '<?php echo addslashes( JText::_("COM_COMMUNITY_POSTBOX_EVENT_DATE_AND_TIME") ); ?>';
  joms.language.event.event_detail            = '<?php echo addslashes( JText::_("COM_COMMUNITY_EVENTS_DETAIL") ); ?>';
  joms.language.event.category                = '<?php echo addslashes( JText::_("COM_COMMUNITY_EVENTS_CATEGORY") ); ?>';
  joms.language.event.location                = '<?php echo addslashes( JText::_("COM_COMMUNITY_EVENTS_LOCATION") ); ?>';
  joms.language.event.location_hint           = '<?php echo addslashes( JText::_("COM_COMMUNITY_EVENTS_LOCATION_DESCRIPTION") ); ?>';
  joms.language.event.start                   = '<?php echo addslashes( JText::_("COM_COMMUNITY_POSTBOX_EVENT_START") ); ?>';
  joms.language.event.start_date_hint         = '<?php echo addslashes( JText::_("COM_COMMUNITY_POSTBOX_EVENT_START_DATE_HINT") ); ?>';
  joms.language.event.start_time_hint         = '<?php echo addslashes( JText::_("COM_COMMUNITY_POSTBOX_EVENT_START_TIME_HINT") ); ?>';
  joms.language.event.end                     = '<?php echo addslashes( JText::_("COM_COMMUNITY_POSTBOX_EVENT_END") ); ?>';
  joms.language.event.end_date_hint           = '<?php echo addslashes( JText::_("COM_COMMUNITY_POSTBOX_EVENT_END_DATE_HINT") ); ?>';
  joms.language.event.end_time_hint           = '<?php echo addslashes( JText::_("COM_COMMUNITY_POSTBOX_EVENT_END_TIME_HINT") ); ?>';
  joms.language.event.done_button             = '<?php echo addslashes( JText::_("COM_COMMUNITY_POSTBOX_EVENT_DONE_BUTTON") ); ?>';
  joms.language.event.create_limit_exceeded   = '<?php echo addslashes( JText::_("COM_COMMUNITY_EVENTS_DAILY_LIMIT") ); ?>';
  joms.language.event.category_not_selected   = '<?php echo addslashes( JText::_("COM_COMMUNITY_EVENTS_CATEGORY_NOT_SELECTED") ); ?>';
  joms.language.event.location_not_selected   = '<?php echo addslashes( JText::_("COM_COMMUNITY_EVENTS_LOCATION_NOT_SELECTED") ); ?>';
  joms.language.event.start_date_not_selected = '<?php echo addslashes( JText::_("COM_COMMUNITY_EVENTS_START_DATE_NOT_SELECTED") ); ?>';
  joms.language.event.end_date_not_selected   = '<?php echo addslashes( JText::_("COM_COMMUNITY_EVENTS_END_DATE_NOT_SELECTED") ); ?>';
  joms.language.event.start_time_not_selected = '<?php echo addslashes( JText::_("COM_COMMUNITY_EVENTS_START_TIME_NOT_SELECTED") ); ?>';
  joms.language.event.end_time_not_selected   = '<?php echo addslashes( JText::_("COM_COMMUNITY_EVENTS_END_TIME_NOT_SELECTED") ); ?>';

  joms.language.custom || (joms.language.custom = {});
  joms.language.custom.predefined_button      = '<?php echo addslashes( JText::_("COM_COMMUNITY_POSTBOX_CUSTOM_PREDEFINED_BUTTON") ); ?>';
  joms.language.custom.predefined_label       = '<?php echo addslashes( JText::_("COM_COMMUNITY_POSTBOX_CUSTOM_PREDEFINED_LABEL") ); ?>';
  joms.language.custom.custom_button          = '<?php echo addslashes( JText::_("COM_COMMUNITY_POSTBOX_CUSTOM_CUSTOM_BUTTON") ); ?>';
  joms.language.custom.custom_label           = '<?php echo addslashes( JText::_("COM_COMMUNITY_POSTBOX_CUSTOM_CUSTOM_LABEL") ); ?>';

  joms.language.geolocation || (joms.language.geolocation = {});
  joms.language.geolocation.loading           = '<?php echo addslashes( JText::_("COM_COMMUNITY_POSTBOX_GEOLOCATION_LOADING") ); ?>';
  joms.language.geolocation.loaded            = '<?php echo addslashes( JText::_("COM_COMMUNITY_POSTBOX_GEOLOCATION_LOADED") ); ?>';
  joms.language.geolocation.error             = '<?php echo addslashes( JText::_("COM_COMMUNITY_POSTBOX_GEOLOCATION_ERROR") ); ?>';
  joms.language.geolocation.select_button     = '<?php echo addslashes( JText::_("COM_COMMUNITY_POSTBOX_GEOLOCATION_SELECT_BUTTON") ); ?>';
  joms.language.geolocation.remove_button     = '<?php echo addslashes( JText::_("COM_COMMUNITY_POSTBOX_GEOLOCATION_REMOVE_BUTTON") ); ?>';
  joms.language.geolocation.near_here         = '<?php echo addslashes( JText::_("COM_COMMUNITY_POSTBOX_GEOLOCATION_NEAR_HERE") ); ?>';
  joms.language.geolocation.empty             = '<?php echo addslashes( JText::_("COM_COMMUNITY_POSTBOX_GEOLOCATION_EMPTY") ); ?>';

  joms.language.fetch || (joms.language.fetch = {});
  joms.language.fetch['title_hint']           = '<?php echo addslashes( JText::_("COM_COMMUNITY_POSTBOX_FETCH_TITLE_HINT") ); ?>';
  joms.language.fetch['description_hint']     = '<?php echo addslashes( JText::_("COM_COMMUNITY_POSTBOX_FETCH_DESCRIPTION_HINT") ); ?>';

  joms.language.privacy || (joms.language.privacy = {});
  joms.language.privacy['public']             = '<?php echo addslashes( JText::_("COM_COMMUNITY_PRIVACY_PUBLIC") ); ?>';
  joms.language.privacy['public_desc']        = '<?php echo addslashes( JText::_("COM_COMMUNITY_PRIVACY_PUBLIC_DESC") ); ?>';
  joms.language.privacy['site_members']       = '<?php echo addslashes( JText::_("COM_COMMUNITY_PRIVACY_SITE_MEMBERS") ); ?>';
  joms.language.privacy['site_members_desc']  = '<?php echo addslashes( JText::_("COM_COMMUNITY_PRIVACY_SITE_MEMBERS_DESC") ); ?>';
  joms.language.privacy['friends']            = '<?php echo addslashes( JText::_("COM_COMMUNITY_PRIVACY_FRIENDS") ); ?>';
  joms.language.privacy['friends_desc']       = '<?php echo addslashes( JText::_("COM_COMMUNITY_PRIVACY_FRIENDS_DESC") ); ?>';
  joms.language.privacy['me']                 = '<?php echo addslashes( JText::_("COM_COMMUNITY_PRIVACY_ME") ); ?>';
  joms.language.privacy['me_desc']            = '<?php echo addslashes( JText::_("COM_COMMUNITY_PRIVACY_ME_DESC") ); ?>';

  joms.language.stream || (joms.language.stream = {});
  joms.language.stream.remove_comment         = '<?php echo addslashes( JText::_("COM_COMMUNITY_COMMENT_REMOVE") ); ?>';
  joms.language.stream.remove_comment_message = '<?php echo addslashes( JText::_("COM_COMMUNITY_COMMENT_REMOVE_MESSAGE") ); ?>';

  joms.language.datepicker || (joms.language.datepicker = {});
  joms.language.datepicker.sunday             = '<?php echo addslashes( JText::_("COM_COMMUNITY_DATEPICKER_DAY_1") ); ?>';
  joms.language.datepicker.monday             = '<?php echo addslashes( JText::_("COM_COMMUNITY_DATEPICKER_DAY_2") ); ?>';
  joms.language.datepicker.tuesday            = '<?php echo addslashes( JText::_("COM_COMMUNITY_DATEPICKER_DAY_3") ); ?>';
  joms.language.datepicker.wednesday          = '<?php echo addslashes( JText::_("COM_COMMUNITY_DATEPICKER_DAY_4") ); ?>';
  joms.language.datepicker.thursday           = '<?php echo addslashes( JText::_("COM_COMMUNITY_DATEPICKER_DAY_5") ); ?>';
  joms.language.datepicker.friday             = '<?php echo addslashes( JText::_("COM_COMMUNITY_DATEPICKER_DAY_6") ); ?>';
  joms.language.datepicker.saturday           = '<?php echo addslashes( JText::_("COM_COMMUNITY_DATEPICKER_DAY_7") ); ?>';
  joms.language.datepicker.january            = '<?php echo addslashes( JText::_("COM_COMMUNITY_DATEPICKER_MONTH_1") ); ?>';
  joms.language.datepicker.february           = '<?php echo addslashes( JText::_("COM_COMMUNITY_DATEPICKER_MONTH_2") ); ?>';
  joms.language.datepicker.march              = '<?php echo addslashes( JText::_("COM_COMMUNITY_DATEPICKER_MONTH_3") ); ?>';
  joms.language.datepicker.april              = '<?php echo addslashes( JText::_("COM_COMMUNITY_DATEPICKER_MONTH_4") ); ?>';
  joms.language.datepicker.may                = '<?php echo addslashes( JText::_("COM_COMMUNITY_DATEPICKER_MONTH_5") ); ?>';
  joms.language.datepicker.june               = '<?php echo addslashes( JText::_("COM_COMMUNITY_DATEPICKER_MONTH_6") ); ?>';
  joms.language.datepicker.july               = '<?php echo addslashes( JText::_("COM_COMMUNITY_DATEPICKER_MONTH_7") ); ?>';
  joms.language.datepicker.august             = '<?php echo addslashes( JText::_("COM_COMMUNITY_DATEPICKER_MONTH_8") ); ?>';
  joms.language.datepicker.september          = '<?php echo addslashes( JText::_("COM_COMMUNITY_DATEPICKER_MONTH_9") ); ?>';
  joms.language.datepicker.october            = '<?php echo addslashes( JText::_("COM_COMMUNITY_DATEPICKER_MONTH_10") ); ?>';
  joms.language.datepicker.november           = '<?php echo addslashes( JText::_("COM_COMMUNITY_DATEPICKER_MONTH_11") ); ?>';
  joms.language.datepicker.december           = '<?php echo addslashes( JText::_("COM_COMMUNITY_DATEPICKER_MONTH_12") ); ?>';
  joms.language.datepicker.today              = '<?php echo addslashes( JText::_("COM_COMMUNITY_DATEPICKER_CURRENT") ); ?>';
  joms.language.datepicker['clear']           = '<?php echo addslashes( JText::_("COM_COMMUNITY_DATEPICKER_CLEAR") ); ?>';

</script>
<!-- dummy comment -->
<div class="joms-postbox" style="display:none;">
  <div class="joms-postbox-preview" style="display:none"></div>
  <div id="joms-postbox-status" class="joms-postbox-content">
    <div class="joms-postbox-tabs"></div>
  </div>
  <nav class="joms-postbox-tab joms-postbox-tab-root" style="display:none">
    <ul class="joms-list inline">
      <li data-tab="status">
        <svg viewBox="0 0 16 18" class="joms-icon">
          <use xlink:href="<?php echo CRoute::getURI(); ?>#joms-icon-pencil"></use>
        </svg>
        <span class="visible-desktop"><?php echo JText::_("COM_COMMUNITY_POSTBOX_STATUS"); ?></span>
      </li>
      <li data-tab="photo">
        <svg viewBox="0 0 16 18" class="joms-icon">
          <use xlink:href="<?php echo CRoute::getURI(); ?>#joms-icon-camera"></use>
        </svg>
        <span class="visible-desktop"><?php echo JText::_("COM_COMMUNITY_POSTBOX_PHOTO"); ?></span>
      </li>
      <li data-tab="video">
        <svg viewBox="0 0 16 18" class="joms-icon">
          <use xlink:href="<?php echo CRoute::getURI(); ?>#joms-icon-play"></use>
        </svg>
        <span class="visible-desktop"><?php echo JText::_("COM_COMMUNITY_POSTBOX_VIDEO"); ?></span>
      </li>
      <li data-tab="event">
        <svg viewBox="0 0 16 18" class="joms-icon">
          <use xlink:href="<?php echo CRoute::getURI(); ?>#joms-icon-calendar"></use>
        </svg>
        <span class="visible-desktop"><?php echo JText::_("COM_COMMUNITY_POSTBOX_EVENT"); ?></span>
      </li>
      <?php if ( CFactory::getConfig()->get("custom_activity") && COwnerHelper::isCommunityAdmin() && $target == $my->id ) { ?>
      <li data-tab="custom">
        <svg viewBox="0 0 16 18" class="joms-icon">
          <use xlink:href="<?php echo CRoute::getURI(); ?>#joms-icon-bullhorn"></use>
        </svg>
        <span class="visible-desktop"><?php echo JText::_("COM_COMMUNITY_POSTBOX_CUSTOM"); ?></span>
      </li>
      <?php } ?>
    </ul>
  </nav>
</div>
