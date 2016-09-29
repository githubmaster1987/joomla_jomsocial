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

<?php if(isset($groupMiniHeader) && $groupMiniHeader){ ?>
    <div class="joms-body">
        <?php echo $groupMiniHeader; ?>
    </div>
<?php } ?>

<div class="joms-page">
    <div class="joms-list__search">
        <div class="joms-list__search-title">
            <h3 class="joms-page__title"><?php echo $pageTitle; ?></h3>
        </div>

        <div class="joms-list__utilities">
            <form method="GET" class="joms-inline--desktop" action="<?php echo CRoute::_('index.php?option=com_community&view=events&task=search'); ?>">
                <span>
                    <input type="text" class="joms-input--search" name="search" placeholder="<?php echo JText::_('COM_COMMUNITY_SEARCH_EVENT_PLACEHOLDER'); ?>">
                </span>
                <?php echo JHTML::_( 'form.token' ) ?>
                <span>
                    <button class="joms-button--neutral"><?php echo JText::_('COM_COMMUNITY_SEARCH_GO'); ?></button>
                </span>
                <input type="hidden" name="option" value="com_community" />
                <input type="hidden" name="view" value="events" />
                <input type="hidden" name="task" value="search" />
                <input type="hidden" name="Itemid" value="<?php echo CRoute::getItemId();?>" />
                <input type="hidden" name="posted" value="1" />
            </form>
            <?php if($canCreate) { ?>
            <button onclick="window.location='<?php echo $createLink; ?>';" class="joms-button--add">
                <span><?php echo (isset($isGroup) && $isGroup) ? JText::_('COM_COMMUNITY_CREATE_GROUP_EVENT') : JText::_('COM_COMMUNITY_CREATE_EVENT'); ?></span>
                <svg class="joms-icon" viewBox="0 0 16 16">
                    <use xlink:href="<?php echo CRoute::getURI(); ?>#joms-icon-plus"></use>
                </svg>
            </button>
            <?php } ?>
        </div>
    </div>

    <?php //echo $submenu;?>

    <h4 class="joms-text--title"><?php echo JText::_('COM_COMMUNITY_EVENTS_IMPORT_ICAL_DESCRIPTION'); ?></h4>

    <form class="joms-form" name="jsforms-events-import" action="<?php echo CRoute::getURI(); ?>" method="post"
          enctype="multipart/form-data">

        <div class="joms-form__group">
            <div>
                <div class="joms-input--radio">
                    <label>
                        <input type="radio" name="type" class="radio" onclick="joms_import_switch('file');" checked="checked">
                        <span><?php echo JText::_('COM_COMMUNITY_EVENTS_INPORT_LOCAL'); ?></span>
                    </label>
                </div>
            </div>
            <div>
                <div class="joms-input--radio">
                    <label>
                        <input type="radio" name="type" class="radio" onclick="joms_import_switch('url');">
                        <span><?php echo JText::_('COM_COMMUNITY_EVENTS_IMPORT_EXTERNAL'); ?></span>
                    </label>
                </div>
            </div>
        </div>

        <div class="joms-form__group">
            <input type="file" class="joms-input joms-js--input-file" name="file">
            <input type="text" class="joms-input joms-js--input-url" name="url" style="display:none">
        </div>

        <div class="joms-form__group">
            <small><?php echo JText::_('COM_COMMUNITY_EVENTS_IMPORT_ERROR'); ?></small>
        </div>

        <div class="joms-from__group">
            <input type="submit" class="joms-button joms-button--primary" value="<?php echo JText::_('COM_COMMUNITY_EVENTS_IMPORT'); ?>">
            <input type="hidden" name="type" class="joms-js--input-hidden" value="file">
        </div>

    </form>

    <script>
        function joms_import_switch( type ) {
            var file = document.getElementsByClassName('joms-js--input-file')[0],
                url = document.getElementsByClassName('joms-js--input-url')[0],
                hidden = document.getElementsByClassName('joms-js--input-hidden')[0];

            file.style.display = type === 'file' ? '' : 'none';
            url.style.display = type === 'file' ? 'none' : '';
            hidden.value = type === 'file' ? 'file' : 'url';
        }
    </script>


    <?php if ($events) { ?>
        <div class="joms-gap"></div>
        <form action="<?php echo $saveimportlink; ?>" method="post" class="joms-form">
            <h4 class="joms-page__title">
                <?php echo JText::_('COM_COMMUNITY_EVENTS_EXPORTED'); ?>
            </h4>

            <p class="joms-text--desc"><?php echo JText::_('COM_COMMUNITY_EVENTS_IMPORT_SELECT'); ?></p>

            <div class="joms-gap"></div>
            <?php
                $i = 1;
                foreach ($events as $event) {
                    ?>
                    <div class="joms-event--import">

                        <div class="joms-form__group">
                            <span><?php echo $event->getTitle(); ?></span>
                            <input type="checkbox" name="events[]" id="event-<?php echo $i; ?>" class="joms-input"
                                   value="<?php echo $i?>">
                        </div>

                        <div class="joms-form__group">
                            <span><?php echo JText::_('COM_COMMUNITY_PHOTOS_ALBUM_DESC'); ?></span>
                            <?php if ($event->getDescription()) { ?>
                                <p><?php echo $event->getDescription(); ?></p>
                            <?php } else { ?>
                                <p><?php echo JText::_('COM_COMMUNITY_EVENTS_DESCRIPTION_ERR0R'); ?></p>
                            <?php } ?>
                        </div>

                        <div class="joms-form__group">
                            <span><?php echo JText::_('COM_COMMUNITY_EVENTS_START_TIME'); ?></span>
                            <?php echo CTimeHelper::getFormattedUTC($event->getStartDate(), $offsetValue); ?>
                        </div>

                        <div class="joms-form__group">
                            <span><?php echo JText::_('COM_COMMUNITY_EVENTS_END_TIME'); ?></span>
                            <?php echo CTimeHelper::getFormattedUTC($event->getEndDate(), $offsetValue); ?>
                        </div>

                        <div class="joms-form__group">
                            <span><?php echo JText::_('COM_COMMUNITY_TIMEZONE'); ?></span>
                            <?php

                                $time = new DateTime('now', new DateTimeZone($offset));
                                $time = (int)$time->format('P');
                            ?>

                            <input class="joms-input" name="event-<?php echo $i; ?>-offset-text" type="text"
                                   value="<?php echo $offset; ?>" disabled="disabled" class="disable">
                            <input class="joms-input" name="event-<?php echo $i; ?>-offset" type="hidden"
                                   value="<?php echo $time; ?>">
                        </div>

                        <div class="joms-form__group">
                            <span><?php echo JText::_('COM_COMMUNITY_EVENTS_LOCATION'); ?></span>
                            <?php echo ($event->getLocation() != '') ? $event->getLocation() : JText::_(
                                'COM_COMMUNITY_EVENTS_LOCATION_NOT_AVAILABLE'
                            ); ?>
                        </div>

                        <div class="joms-form__group">
                            <span><?php echo JText::_('COM_COMMUNITY_EVENTS_CATEGORY'); ?></span>
                            <select name="event-<?php echo $i; ?>-catid" id="event-<?php echo $i; ?>-catid"
                                    class="joms-select">
                                <?php foreach ($categories as $category) { ?>
                                    <option value="<?php echo $category->id; ?>"><?php echo JText::_(
                                            $this->escape($category->name)
                                        ); ?></option>
                                <?php } ?>
                            </select>
                        </div>

                        <div class="joms-form__group">
                            <span><?php echo JText::_('COM_COMMUNITY_EVENTS_GUEST_INVITE'); ?></span>

                            <div class="joms-input--radio">
                                <input type="radio" class="joms-input" name="event-<?php echo $i; ?>-invite"
                                       id="event-<?php echo $i; ?>-invite-allowed" value="1" checked="checked"/>
                                <span
                                    for="event-<?php echo $i; ?>-invite-allowed"><?php echo JText::_('COM_COMMUNITY_YES'); ?></span>
                            </div>
                            <div class="joms-input--radio">
                                <input type="radio" class="joms-input" name="event-<?php echo $i; ?>-invite"
                                       id="event-<?php echo $i; ?>-invite-disallowed" value="0"/>
                                <span
                                    for="event-<?php echo $i; ?>-invite-disallowed"><?php echo JText::_('COM_COMMUNITY_NO'); ?></span>
                            </div>
                        </div>

                        <div class="joms-form__group">
                            <span><?php echo JText::_('COM_COMMUNITY_EVENTS_TYPE'); ?></span>

                            <div class="joms-input--radio">
                                <input type="radio" class="joms-input" name="event-<?php echo $i; ?>-permission"
                                       id="event-<?php echo $i; ?>-permission-open" value="0" checked="checked"/>
                                <span
                                    for="event-<?php echo $i; ?>-permission-open"><?php echo JText::_('COM_COMMUNITY_EVENTS_OPEN_EVENT'); ?></span>
                            </div>
                            <div class="joms-input--radio">
                                <input type="radio" class="joms-input" name="event-<?php echo $i; ?>-permission"
                                       id="event-<?php echo $i; ?>-permission-private" value="1"/>
                        <span
                            for="event-<?php echo $i; ?>-permission-private"><?php echo JText::_('COM_COMMUNITY_EVENTS_PRIVATE_EVENT'); ?>
                    </span>
                            </div>
                        </div>

                        <div class="joms-form__group">
                            <span><?php echo JText::_('COM_COMMUNITY_EVENTS_NO_SEAT'); ?></span>
                            <input type="text" class="joms-input" name="event-<?php echo $i; ?>-ticket"
                                   id="event-<?php echo $i; ?>-ticket" value="0" size="10" maxlength="5"/>
                        </div>

                        <div class="joms-form__group">
                            <span><?php echo JText::_('COM_COMMUNITY_EVENTS_REPEAT'); ?></span>
                            <?php $repeat = $event->getRepeat(); ?>
                            <select name="event-<?php echo $i; ?>-repeat" class="joms-select"
                                    id="event-<?php echo $i; ?>-repeat">
                                <option value=""><?php echo JText::_('COM_COMMUNITY_EVENTS_REPEAT_NONE'); ?></option>
                                <option
                                    value="daily" <?php echo $repeat == 'daily' ? 'selected' : ''; ?>><?php echo JText::_('COM_COMMUNITY_EVENTS_REPEAT_DAILY'); ?></option>
                                <option
                                    value="weekly" <?php echo $repeat == 'weekly' ? 'selected' : ''; ?>><?php echo JText::_('COM_COMMUNITY_EVENTS_REPEAT_WEEKLY'); ?></option>
                                <option
                                    value="monthly" <?php echo $repeat == 'monthly' ? 'selected' : ''; ?>><?php echo JText::_('COM_COMMUNITY_EVENTS_REPEAT_MONTHLY'); ?></option>
                            </select>
                        </div>

                        <div class="joms-form__group">
                            <span><?php echo JText::_('COM_COMMUNITY_EVENTS_REPEAT_END'); ?></span>
                            <?php echo $event->getRepeatEnd(); ?>
                        </div>

                        <div class="joms-form__group">
                            <span><?php echo JText::_('COM_COMMUNITY_EVENTS_REPEAT_LIMIT'); ?></span>
                            <input type="text" class="joms-input" name="event-<?php echo $i; ?>-limit"
                                   id="event-<?php echo $i; ?>-limit" value="<?php echo $event->getRepeatLimit(); ?>"
                                   size="10" maxlength="5"/>
                        </div>

                        <input name="event-<?php echo $i; ?>-startdate"
                               value="<?php echo $event->getStartDate(); ?>" type="hidden"/>
                        <input name="event-<?php echo $i; ?>-enddate" value="<?php echo $event->getEndDate(); ?>"
                               type="hidden"/>
                        <input name="event-<?php echo $i; ?>-title" value="<?php echo $event->getTitle(); ?>"
                               type="hidden"/>
                        <input name="event-<?php echo $i; ?>-location"
                               value="<?php echo $this->escape($event->getLocation()); ?>" type="hidden"/>
                        <input name="event-<?php echo $i; ?>-description"
                               value="<?php echo $this->escape($event->getDescription()); ?>" type="hidden"/>
                        <input name="event-<?php echo $i; ?>-summary" value="<?php echo $event->getSummary(); ?>"
                               type="hidden"/>
                        <input name="event-<?php echo $i; ?>-repeatend"
                               value="<?php echo $event->getRepeatEnd(); ?>" type="hidden"/>

                    </div>
                    <?php $i++;
                } ?>

            <input type="submit" value="<?php echo JText::_('COM_COMMUNITY_EVENTS_IMPORT'); ?>"
                   class="joms-button--primary"/>
        </form>
    <?php } ?>

</div>
