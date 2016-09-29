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

$startDate = new JDate($event->startdate);
$endDate = new JDate($event->enddate);
$isReadOnlyDate = CEventHelper::isToday($event) || CEventHelper::isPast($event);
$isReadOnlyDate = $isReadOnlyDate && ( $event->id > 0 );

$repeatEndDate = false;

if ( $event->id && $event->repeat ) {
    $repeatEndDate = new JDate($event->repeatend);
}

?>

<div class="joms-page">
    <h3 class="joms-page__title"><?php echo JText::_($event->id ? 'COM_COMMUNITY_EVENTS_EDIT_TITLE' : 'COM_COMMUNITY_EVENTS_CREATE_TITLE'); ?></h3>
    <form method="POST" action="<?php echo CRoute::getURI(); ?>" onsubmit="return joms_validate_form( this );">

        <?php if (!$event->id && $eventcreatelimit != 0) { ?>
            <?php if ($eventCreated / $eventcreatelimit >= COMMUNITY_SHOW_LIMIT) { ?>
                <div class="joms-form__group">
                    <p><?php echo JText::sprintf('COM_COMMUNITY_EVENTS_CREATION_LIMIT_STATUS', $eventCreated, $eventcreatelimit); ?></p>
                </div>
            <?php } ?>
        <?php } ?>

        <?php if ($beforeFormDisplay) { ?>
        <div class="joms-form__group"><?php echo $beforeFormDisplay; ?></div>
        <?php } ?>

        <div class="joms-form__group"<?php echo $helper->hasPrivacy() ? ' style="margin-bottom:5px"' : ''; ?>>
            <span><?php echo JText::_('COM_COMMUNITY_EVENTS_TITLE_LABEL'); ?> <span class="joms-required">*</span></span>
            <input type="text" class="joms-input" name="title" required=""
                title="<?php echo JText::_('COM_COMMUNITY_EVENTS_TITLE_TIPS'); ?>"
                value="<?php echo $this->escape($event->title); ?>">
        </div>

        <?php if ( $helper->hasPrivacy() ) { ?>

        <div class="joms-form__group">
            <span></span>
            <label class="joms-checkbox">
                <input type="checkbox" class="joms-checkbox" name="permission" onclick="joms_checkPrivacy();" value="1"
                    <?php echo $event->permission == COMMUNITY_PRIVATE_EVENT ? ' checked="checked"' : ''; ?>>
                <span title="<?php echo JText::_('COM_COMMUNITY_EVENTS_TYPE_TIPS'); ?>">
                    <?php echo JText::_('COM_COMMUNITY_EVENTS_PRIVATE_EVENT'); ?></span>
            </label>
            <label class="joms-checkbox">
                <input type="checkbox" class="joms-checkbox" name="unlisted" value="1"
                    <?php echo ($event->permission == COMMUNITY_PRIVATE_EVENT) ? '' : ' disabled="disabled"'; ?>
                    <?php echo ($event->unlisted == 1 && $event->permission == COMMUNITY_PRIVATE_EVENT) ? ' checked="checked"' : ''; ?>>

                <span title="<?php echo JText::_('COM_COMMUNITY_EVENTS_UNLISTED_TIPS'); ?>">
                    <?php echo JText::_('COM_COMMUNITY_EVENTS_UNLISTED'); ?>
                </span>

            </label>
        </div>
            <script type="text/javascript">
            function joms_checkPrivacy() {
            var closedCheckbox = joms.jQuery('[name=permission]');
            var unlistedCheckbox = joms.jQuery('[name=unlisted]');

            if( closedCheckbox.prop('checked') === true ) {
                unlistedCheckbox.removeAttr('disabled');
            } else {
                unlistedCheckbox[0].checked = false;
                unlistedCheckbox.attr('disabled', 'disabled');
            }
            }
            </script>
        <?php } ?>

        <?php
            // only show if there are more than 1 member in the group
            if($showGroupMemberInvitation){ ?>
        <div class="joms-form__group">
            <span></span>
            <input type="checkbox" class="joms-checkbox" name="invitegroupmembers" onclick="joms_checkPrivacy();" value="1">
            <span title="Only group members will be able to see the group's content"><?php echo JText::_('COM_COMMUNITY_EVENT_INVITE_ALL_GROUP_MEMBERS'); ?></span>
        </div>
        <?php } ?>

        <div class="joms-form__group">
            <span><?php echo JText::_('COM_COMMUNITY_EVENTS_SUMMARY'); ?></span>
            <textarea class="joms-textarea" name="summary" title="<?php echo JText::_('COM_COMMUNITY_EVENTS_SUMMARY_TIPS'); ?>" data-maxchars="120"><?php echo $this->escape($event->summary); ?></textarea>
        </div>

        <div class="joms-form__group joms-textarea--mobile">
            <span><?php echo JText::_('COM_COMMUNITY_EVENTS_DESCRIPTION'); ?></span>
            <textarea class="joms-textarea" name="description" data-wysiwyg="trumbowyg" data-wysiwyg-type="event" data-wysiwyg-id="<?php echo ($event->id ? 0 : $event->id) ?>"><?php echo $this->escape($event->description); ?></textarea>
        </div>

        <div class="joms-form__group">
            <span><?php echo JText::_('COM_COMMUNITY_EVENTS_CATEGORY'); ?> <span class="joms-required">*</span></span>
            <?php echo $lists['categoryid']; ?>
        </div>

        <div class="joms-form__group">
            <span><?php echo JText::_('COM_COMMUNITY_EVENTS_LOCATION'); ?> <span class="joms-required">*</span></span>
            <input type="text" class="joms-input" name="location" required=""
                title="<?php echo JText::_('COM_COMMUNITY_EVENTS_LOCATION_TIPS'); ?>"
                value="<?php echo $this->escape($event->location); ?>"
                placeholder="<?php echo JText::_('COM_COMMUNITY_EVENTS_LOCATION_DESCRIPTION'); ?>">
        </div>

        <script>

            joms_tmp_pickadateOpts = {
                min      : true,
                format   : 'yyyy-mm-dd',
                firstDay : <?php echo $config->get('event_calendar_firstday') === 'Monday' ? 1 : 0 ?>,
                today    : '<?php echo JText::_("COM_COMMUNITY_DATEPICKER_CURRENT", true) ?>',
                'clear'  : '<?php echo JText::_("COM_COMMUNITY_DATEPICKER_CLEAR", true) ?>'
            };

            joms_tmp_pickadateOpts.weekdaysFull = [
                '<?php echo JText::_("COM_COMMUNITY_DATEPICKER_DAY_1", true) ?>',
                '<?php echo JText::_("COM_COMMUNITY_DATEPICKER_DAY_2", true) ?>',
                '<?php echo JText::_("COM_COMMUNITY_DATEPICKER_DAY_3", true) ?>',
                '<?php echo JText::_("COM_COMMUNITY_DATEPICKER_DAY_4", true) ?>',
                '<?php echo JText::_("COM_COMMUNITY_DATEPICKER_DAY_5", true) ?>',
                '<?php echo JText::_("COM_COMMUNITY_DATEPICKER_DAY_6", true) ?>',
                '<?php echo JText::_("COM_COMMUNITY_DATEPICKER_DAY_7", true) ?>'
            ];

            joms_tmp_pickadateOpts.weekdaysShort = [];
            for ( i = 0; i < joms_tmp_pickadateOpts.weekdaysFull.length; i++ )
                joms_tmp_pickadateOpts.weekdaysShort[i] = joms_tmp_pickadateOpts.weekdaysFull[i].substr( 0, 3 );

            joms_tmp_pickadateOpts.monthsFull = [
                '<?php echo JText::_("COM_COMMUNITY_DATEPICKER_MONTH_1", true) ?>',
                '<?php echo JText::_("COM_COMMUNITY_DATEPICKER_MONTH_2", true) ?>',
                '<?php echo JText::_("COM_COMMUNITY_DATEPICKER_MONTH_3", true) ?>',
                '<?php echo JText::_("COM_COMMUNITY_DATEPICKER_MONTH_4", true) ?>',
                '<?php echo JText::_("COM_COMMUNITY_DATEPICKER_MONTH_5", true) ?>',
                '<?php echo JText::_("COM_COMMUNITY_DATEPICKER_MONTH_6", true) ?>',
                '<?php echo JText::_("COM_COMMUNITY_DATEPICKER_MONTH_7", true) ?>',
                '<?php echo JText::_("COM_COMMUNITY_DATEPICKER_MONTH_8", true) ?>',
                '<?php echo JText::_("COM_COMMUNITY_DATEPICKER_MONTH_9", true) ?>',
                '<?php echo JText::_("COM_COMMUNITY_DATEPICKER_MONTH_10", true) ?>',
                '<?php echo JText::_("COM_COMMUNITY_DATEPICKER_MONTH_11", true) ?>',
                '<?php echo JText::_("COM_COMMUNITY_DATEPICKER_MONTH_12", true) ?>'
            ];

            joms_tmp_pickadateOpts.monthsShort = [];
            for ( i = 0; i < joms_tmp_pickadateOpts.monthsFull.length; i++ )
                joms_tmp_pickadateOpts.monthsShort[i] = joms_tmp_pickadateOpts.monthsFull[i].substr( 0, 3 );

        </script>

        <div class="joms-form__group">
            <span><?php echo JText::_('COM_COMMUNITY_EVENTS_START_TIME'); ?> <span class="joms-required">*</span></span>
            <input type="text" class="joms-input" id="startdate" name="startdate" required=""
                title="<?php echo JText::_('COM_COMMUNITY_EVENTS_START_TIME_TIPS'); ?>"
                placeholder="<?php echo JText::_('COM_COMMUNITY_POSTBOX_EVENT_START_DATE_HINT'); ?>"
                data-value="<?php echo $startDate->format('Y-m-d'); ?>"
                style="cursor:text">
            <div id="starttime" style="margin-top:5px;">
                <?php echo $startHourSelect; ?> :
                <?php echo $startMinSelect; ?>
                <?php echo $startAmPmSelect; ?>
            </div>
            <script>
                window.joms_queue || (joms_queue = []);
                joms_queue.push(function( $ ) {
                    joms_tmp_startDate = $('#startdate').pickadate( $.extend({}, joms_tmp_pickadateOpts, {
                        klass: { frame: 'picker__frame startDate' },
                        min: <?php echo $event->id > 0 ? 'false' : 'true' ?>,
                        onSet: function( o ) {
                            var min = new Date(o.select),
                                date, hour, minute;

                            if ( isNaN( min.getTime() ) ) {
                                min = joms_tmp_pickadateOpts.min;
                            }

                            if ( window.joms_tmp_endDate ) {
                                // Set min range.
                                joms_tmp_endDate.set({ min: min }, { muted: true });

                                // Set the field as well.
                                min = new Date( joms_tmp_endDate.get( 'min', 'yyyy-mm-dd' ) );
                                date = new Date( joms_tmp_endDate.get() );
                                if ( !date.getTime() || date.getTime() < min.getTime() ) {
                                    joms_tmp_endDate.set({ select: min }, { muted: true }, { format: 'yyyy-mm-dd' });
                                }

                                // Trigger validate time.
                                $('#starttime-hour').triggerHandler('change');
                            }
                        }
                    }) ).pickadate('picker');
                });
            </script>
        </div>

        <div class="joms-form__group">
            <span><?php echo JText::_('COM_COMMUNITY_EVENTS_END_TIME'); ?> <span class="joms-required">*</span></span>
            <input type="text" class="joms-input" id="enddate" name="enddate" required=""
                title="<?php echo JText::_('COM_COMMUNITY_EVENTS_END_TIME_TIPS'); ?>"
                placeholder="<?php echo JText::_('COM_COMMUNITY_POSTBOX_EVENT_END_DATE_HINT'); ?>"
                data-value="<?php echo $endDate->format('Y-m-d'); ?>"
                style="cursor:text">
            <div id="endtime" style="margin-top:5px">
                <?php echo $endHourSelect; ?> :
                <?php echo $endMinSelect; ?>
                <?php echo $endAmPmSelect; ?>
            </div>
            <script>
                window.joms_queue || (joms_queue = []);
                joms_queue.push(function( $ ) {
                    joms_tmp_endDate = $('#enddate').pickadate( $.extend({}, joms_tmp_pickadateOpts, {
                        klass: { frame: 'picker__frame endDate' },
                        min: <?php echo $event->id > 0 ? 'false' : 'true' ?>,
                        onSet: function( o ) {
                            // Trigger validate time.
                            $('#starttime-hour').triggerHandler('change');
                        }
                    }) ).pickadate('picker');
                });
            </script>
        </div>

        <script>
            window.joms_queue || (joms_queue = []);
            joms_queue.push(function( $ ) {
                var $shour = $('#starttime-hour'),
                    $smin  = $('#starttime-min'),
                    $sampm = $('#starttime-ampm'),
                    $ehour = $('#endtime-hour'),
                    $emin  = $('#endtime-min'),
                    $eampm = $('#endtime-ampm'),
                    isAmpm = $sampm.length;

                // Validate time.
                $shour.add( $smin ).add( $sampm ).add( $ehour ).add( $emin ).add( $eampm ).change(function() {
                    var sdate = new Date( $('#startdate').val() ).getTime(),
                        edate = new Date( $('#enddate').val() ).getTime(),
                        shour, smin, ehour, emin, nextDay;

                    if ( !sdate || !edate || edate > sdate ) {
                        return;
                    }

                    shour = +$shour.val();
                    smin  = +$smin.val();
                    ehour = +$ehour.val();
                    emin  = +$emin.val();

                    if ( isAmpm ) {
                        if ( $sampm.val() === 'PM' ) {
                            shour += shour < 12 ? 12 : 0;
                        } else if ( shour === 12 ) {
                            shour = 0;
                        }
                        if ( $eampm.val() === 'PM' ) {
                            ehour += ehour < 12 ? 12 : 0;
                        } else if ( ehour === 12 ) {
                            ehour = 0;
                        }
                    }

                    if ( ehour > shour || ( ehour === shour && emin > smin )) {
                        return;
                    }

                    ehour = shour;
                    emin = smin + 15;
                    if ( emin >= 60 ) {
                        emin = 0;
                        ehour += 1;
                        if ( ehour >= 24 ) {
                            ehour = 0;
                            nextDay = true;
                        }
                    }

                    $emin.val( emin );

                    if ( !isAmpm ) {
                        $ehour.val( ehour );
                    } else {
                        if ( ehour === 0 ) {
                            $ehour.val( 12 );
                            $eampm.val('AM');
                        } else if ( ehour < 12 ) {
                            $ehour.val( ehour );
                            $eampm.val('AM');
                        } else if ( ehour === 12 ) {
                            $ehour.val( 12 );
                            $eampm.val('PM');
                        } else {
                            $ehour.val( ehour - 12 );
                            $eampm.val('PM');
                        }
                    }

                    if ( nextDay ) {
                        edate = new Date( joms_tmp_startDate.get() );
                        edate.setDate( edate.getDate() + 1 );
                        joms_tmp_endDate.set({ select: edate }, { muted: true }, { format: 'yyyy-mm-dd' });
                    }

                });

            });
        </script>

        <?php if ($enableRepeat) { ?>

        <div class="joms-form__group">
            <span><?php echo JText::_('COM_COMMUNITY_EVENTS_REPEAT'); ?></span>
            <select class="joms-select" name="repeat" title="<?php echo JText::_('COM_COMMUNITY_EVENTS_REPEAT_TIPS'); ?>">
                <option value=""><?php echo JText::_('COM_COMMUNITY_EVENTS_REPEAT_NONE'); ?></option>
                <option value="daily"><?php echo JText::_('COM_COMMUNITY_EVENTS_REPEAT_DAILY'); ?></option>
                <option value="weekly"><?php echo JText::_('COM_COMMUNITY_EVENTS_REPEAT_WEEKLY'); ?></option>
                <option value="monthly"><?php echo JText::_('COM_COMMUNITY_EVENTS_REPEAT_MONTHLY'); ?></option>
            </select>
        </div>

        <div class="joms-form__group joms-form__group--repeatend" style="display:none">
            <span><?php echo JText::_('COM_COMMUNITY_EVENTS_REPEAT_END'); ?></span>
            <input type="text" class="joms-input" name="repeatend"
                title="<?php echo JText::_('COM_COMMUNITY_EVENTS_REPEAT_END_TIPS'); ?>"
                placeholder="<?php echo JText::_('COM_COMMUNITY_POSTBOX_EVENT_END_DATE_HINT'); ?>"
                data-value="<?php echo $repeatEndDate ? $repeatEndDate->format('Y-m-d') : ''; ?>"
                style="cursor:text">
            <p class="joms-help joms-help--repeatend-desc" style="display:none"></p>
        </div>

        <script>
            window.joms_queue || (joms_queue = []);
            joms_queue.push(function() {
                var select, wrapper, desc, descStr;

                // Cache variables.
                select  = joms.jQuery('.joms-form__group [name=repeat]');
                wrapper = joms.jQuery('.joms-form__group--repeatend');
                desc    = joms.jQuery('.joms-help--repeatend-desc');
                descStr = {
                    daily   : '<?php echo addslashes( sprintf( Jtext::_("COM_COMMUNITY_EVENTS_REPEAT_LIMIT_DESC"), COMMUNITY_EVENT_RECURRING_LIMIT_DAILY ) ); ?>',
                    weekly  : '<?php echo addslashes( sprintf( Jtext::_("COM_COMMUNITY_EVENTS_REPEAT_LIMIT_DESC"), COMMUNITY_EVENT_RECURRING_LIMIT_WEEKLY ) ); ?>',
                    monthly : '<?php echo addslashes( sprintf( Jtext::_("COM_COMMUNITY_EVENTS_REPEAT_LIMIT_DESC"), COMMUNITY_EVENT_RECURRING_LIMIT_MONTHLY ) ); ?>'
                };

                // Initialize repeat datepicker.
                joms_tmp_repeatEndDate = joms.jQuery('.joms-form__group [name=repeatend]').pickadate( joms.jQuery.extend({}, joms_tmp_pickadateOpts, {
                    klass: { frame: 'picker__frame repeatEndDate' }
                }) ).pickadate('picker');

                // Set initial value while in editing mode.
                select.val('<?php echo $event->repeat; ?>');

                // Initialize repeat onchange.
                select.change(function( e ) {
                    var val = e.target.value;
                    if ( descStr[ val ] ) {
                        desc.html( descStr[ val ] );
                        wrapper.show();
                    } else {
                        wrapper.hide();
                    }
                }).change();

            });
        </script>

        <?php } ?>

        <?php if ($config->get('eventshowtimezone')) {
            ?>

        <div class="joms-form__group">
            <span><?php echo JText::_('COM_COMMUNITY_TIMEZONE'); ?> <span class="joms-required">*</span></span>
            <select class="joms-select" name="offset" title="<?php echo JText::_('COM_COMMUNITY_EVENTS_SET_TIMEZONE'); ?>"><?php

                $defaultTimeZone = is_string($params->get('timezone')) ? $params->get('timezone') : $systemOffset;
                foreach ($timezones as $offset => $value) {

            ?><option value="<?php echo $offset; ?>"<?php echo $defaultTimeZone == $offset ? ' selected="selected"' : ''; ?>><?php echo $value; ?></option><?php

                }

            ?></select>
        </div>

        <?php } ?>

        <div class="joms-form__group"<?php echo $helper->hasInvitation() ? ' style="margin-bottom:5px"' : ''; ?>>
            <span><?php echo JText::_('COM_COMMUNITY_EVENTS_NO_SEAT'); ?></span>
            <input type="text" class="joms-input" name="ticket"
                title="<?php echo JText::_('COM_COMMUNITY_EVENTS_NO_SEAT_DESCRIPTION'); ?>"
                value="<?php echo empty($event->ticket) ? 0 : $this->escape($event->ticket); ?>">
        </div>

        <?php if ( $helper->hasInvitation() ) { ?>

        <div class="joms-form__group">
            <span></span>
            <label class="joms-checkbox">
                <input type="checkbox" class="joms-checkbox" name="allowinvite" value="1"<?php echo $event->allowinvite ? ' checked="checked"' : ''; ?>>
                <span title="<?php echo JText::_('COM_COMMUNITY_EVENTS_GUEST_INVITE_TIPS'); ?>">
                    <?php echo JText::_('COM_COMMUNITY_EVENTS_GUEST_INVITE'); ?></span>
            </label>
        </div>

        <?php } ?>

        <?php if($config->get('eventphotos')){ ?>
        <div class="joms-form__group">
            <span><?php echo JText::_('COM_COMMUNITY_EVENTS_RECENT_PHOTO'); ?></span>
            <div>
                <label class="joms-checkbox">
                    <input type="checkbox" class="joms-checkbox joms-js--event-photo-flag" name="photopermission-admin" <?php echo ($params->get('photopermission') != EVENT_PHOTO_PERMISSION_DISABLE || $params->get('photopermission') == '') ? 'checked' : '' ?> value="1">
                    <span title="<?php echo JText::_('COM_COMMUNITY_EVENTS_PHOTO_PERMISSION_TIPS'); ?>"><?php echo JText::_('COM_COMMUNITY_EVENTS_PHOTO_UPLOAD_ALLOW_ADMIN'); ?></span>
                </label>
            </div>
            <div class="joms-js--event-photo-setting" style="display:none">
                <label class="joms-checkbox">
                    <input type="checkbox" class="joms-checkbox" name="photopermission-member" <?php echo ($params->get('photopermission') == 2 || $params->get('photopermission') == '') ? 'checked' : '' ?> value="1">
                    <span title="<?php echo JText::_('COM_COMMUNITY_EVENTS_PHOTO_UPLOAD_ALLOW_MEMBER_TIPS'); ?>"><?php echo JText::_('COM_COMMUNITY_EVENTS_PHOTO_UPLOAD_ALLOW_MEMBER'); ?></span>
                </label>
                <select type="text" class="joms-select" name="eventrecentphotos" title="<?php echo JText::_('COM_COMMUNITY_EVENTS_RECENT_PHOTO_TIPS'); ?>">
                    <?php for($i = 2; $i <= 10; $i = $i+2){ ?>
                    <option value="<?php echo $i; ?>" <?php echo ($params->get('eventrecentphotos') == $i || ($i == 6 && $params->get('eventrecentphotos')==0)) ? 'selected': ''; ?>><?php echo $i; ?></option>
                    <?php } ?>
                </select>
            </div>
        </div>
        <?php } ?>

        <?php if($config->get('eventvideos')){ ?>
        <div class="joms-form__group">
            <span><?php echo JText::_('COM_COMMUNITY_EVENTS_RECENT_VIDEO'); ?></span>
            <div>
                <label class="joms-checkbox">
                    <input type="checkbox" class="joms-checkbox joms-js--event-video-flag" name="videopermission-admin" <?php echo ($params->get('videopermission') != EVENT_VIDEO_PERMISSION_DISABLE || $params->get('videopermission') == '') ? 'checked' : '' ?> value="1">
                    <span title="<?php echo JText::_('COM_COMMUNITY_EVENTS_VIDEO_PERMISSION_TIPS'); ?>"><?php echo JText::_('COM_COMMUNITY_EVENTS_VIDEO_UPLOAD_ALLOW_ADMIN'); ?></span>
                </label>
            </div>
            <div class="joms-js--event-video-setting" style="display:none">
                <label class="joms-checkbox">
                    <input type="checkbox" class="joms-checkbox" name="videopermission-member" <?php echo ($params->get('videopermission') == 2 || $params->get('videopermission') == '') ? 'checked' : '' ?> value="1">
                    <span title="<?php echo JText::_('COM_COMMUNITY_EVENTS_VIDEO_UPLOAD_ALLOW_MEMBER_TIPS'); ?>"><?php echo JText::_('COM_COMMUNITY_EVENTS_VIDEO_UPLOAD_ALLOW_MEMBER'); ?></span>
                </label>
                <select type="text" class="joms-select" name="eventrecentvideos" title="<?php echo JText::_('COM_COMMUNITY_EVENTS_RECENT_VIDEO_TIPS'); ?>">
                    <?php for($i = 2; $i <= 10; $i = $i+2){ ?>
                        <option value="<?php echo $i; ?>" <?php echo ($params->get('eventrecentvideos') == $i || ($i == 6 && $params->get('eventrecentvideos')==0)) ? 'selected': ''; ?>><?php echo $i; ?></option>
                    <?php } ?>
                </select>
            </div>
        </div>
        <?php } ?>

        <script>
            joms.onStart(function( $ ) {
                $('.joms-js--event-photo-flag').on( 'click', function() {
                    var $div = $('.joms-js--event-photo-setting'),
                        $checkbox = $div.find('input');

                    if ( this.checked ) {
                        $checkbox.removeAttr('disabled');
                        $div.show();
                    } else {
                        $checkbox[0].checked = false;
                        $checkbox.attr('disabled', 'disabled');
                        $div.hide();
                    }
                }).triggerHandler('click');

                $('.joms-js--event-video-flag').on( 'click', function() {
                    var $div = $('.joms-js--event-video-setting'),
                        $checkbox = $div.find('input');

                    if ( this.checked ) {
                        $checkbox.removeAttr('disabled');
                        $div.show();
                    } else {
                        $checkbox[0].checked = false;
                        $checkbox.attr('disabled', 'disabled');
                        $div.hide();
                    }
                }).triggerHandler('click');
            });
        </script>

        <?php if ($afterFormDisplay) { ?>
        <div class="joms-form__group"><?php echo $afterFormDisplay; ?></div>
        <?php } ?>

        <div class="joms-form__group">
            <span></span>
            <div>
                <?php if (!$event->id) { ?>
                <input name="action" type="hidden" value="save">
                <?php } ?>
                <input type="hidden" name="eventid" value="<?php echo $event->id; ?>">
                <input type="hidden" name="repeataction" id="repeataction" value="">
                <?php echo JHTML::_('form.token'); ?>
                <input type="button" value="<?php echo JText::_('COM_COMMUNITY_CANCEL_BUTTON'); ?>" class="joms-button--neutral joms-button--full-small" onclick="history.go(-1); return false;">
                <button type="submit" class="joms-button--primary joms-button--full-small">
                    <?php echo JText::_($event->id ? 'COM_COMMUNITY_SAVE_BUTTON' : 'COM_COMMUNITY_EVENTS_CREATE_BUTTON'); ?>
                    <span class="joms-loading" style="display:none">&nbsp;
                        <img src="<?php echo JURI::root(true) ?>/components/com_community/assets/ajax-loader.gif" alt="loader">
                    </span>
                </button>
            </div>
        </div>

    </form>
</div>
<script>
    // Validate form before submit.
    function joms_validate_form( form ) {
        if ( window.joms && joms.util && joms.util.validation ) {
            joms.jQuery('.joms-loading').show();
            joms.util.validation.validate( form, function( errors ) {
                if ( !errors ) {
                    joms.jQuery( form ).removeAttr('onsubmit');
                    setTimeout(function() {
                        joms.jQuery( form ).find('button[type=submit]').click();
                    }, 500 );
                } else {
                    joms.jQuery('.joms-loading').hide();
                }
            });
        }
        return false;
    }
</script>
