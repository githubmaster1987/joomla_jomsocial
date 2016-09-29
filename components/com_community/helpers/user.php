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

    class CUserHelper
    {
        static public function getUserId($username)
        {
            $displayConfig = CFactory::getConfig()->get('displayname');

            $db = JFactory::getDBO();
            $query = 'SELECT ' . $db->quoteName('id') . ' '
                . 'FROM ' . $db->quoteName('#__users') . ' '
                . 'WHERE ' . $db->quoteName($displayConfig) . '=' . $db->Quote($username);

            $db->setQuery($query);

            $id = $db->loadResult();

            return $id;
        }

        static function getThumb($userId, $imageClass = '', $anchorClass = '')
        {
            //CFactory::load( 'helpers' , 'string' );
            $user = CFactory::getUser($userId);

            $imageClass = (!empty($imageClass)) ? ' class="' . $imageClass . '"' : '';
            $anchorClass = (!empty($anchorClass)) ? ' class="' . $anchorClass . '"' : '';

            $data = '<a href="' . CRoute::_(
                    'index.php?option=com_community&view=profile&userid=' . $user->id
                ) . '"' . $anchorClass . '>';
            $data .= '<img src="' . $user->getThumbAvatar() . '" alt="' . CStringHelper::escape(
                    $user->getDisplayName()
                ) . '"' . $imageClass . ' data-author="'.$user->id.'" />';
            $data .= '</a>';

            return $data;
        }

        /**
         * Get the html code to be added to the page
         *
         * return    $html    String
         */
        static public function getBlockUserHTML($userId, $isBlocked)
        {
            $my = CFactory::getUser();
            $html = '';

            if (!empty($my->id)) {

                $tmpl = new Ctemplate();

                $tmpl->set('userId', $userId);
                $tmpl->set('isBlocked', $isBlocked);
                $html = $tmpl->fetch('block.user');

            }

            return $html;
        }

        static public function isUseFirstLastName()
        {
            $isUseFirstLastName = false;

            // Firstname, Lastname for base on field code FIELD_GIVENNAME, FIELD_FAMILYNAME
            $modelProfile = CFactory::getModel('profile');

            $firstName = $modelProfile->getFieldId('FIELD_GIVENNAME');
            $lastName = $modelProfile->getFieldId('FIELD_FAMILYNAME');
            $isUseFirstLastName = ($firstName && $lastName);

            if ($isUseFirstLastName) {
                $table = JTable::getInstance('ProfileField', 'CTable');
                $table->load($firstName);
                $isFirstNamePublished = $table->published;
                $table->load($lastName);
                $isLastNamePublished = $table->published;
                $isUseFirstLastName = ($isFirstNamePublished && $isLastNamePublished);

                // we don't use this html because the generated class name doesn't match in this case
                //$firstNameHTML	= CProfile::getFieldHTML($firstName);
                //$lastNameHTML	= CProfile::getFieldHTML($lastName);
            }

            return $isUseFirstLastName;
        }

        /**
         * Add default items for status box
         */
        static function addDefaultStatusCreator(&$status)
        {
            $mainframe = JFactory::getApplication();
            $jinput = $mainframe->input;

            $my = CFactory::getUser();
            $userid = $jinput->get('userid', $my->id, 'INT');
            $user = CFactory::getUser($userid);
            $config = CFactory::getConfig();
            $template = new CTemplate();

            $isMine = COwnerHelper::isMine($my->id, $user->id);

            /* Message creator */
            $creator = new CUserStatusCreator('message');
            $creator->title = JText::_('COM_COMMUNITY_STATUS');
            $creator->html = $template->fetch('status.message');
            $status->addCreator($creator);

            if ($isMine) {
                if ($config->get('enablephotos')) {
                    /* Photo creator */
                    $creator = new CUserStatusCreator('photo');
                    $creator->title = JText::_('COM_COMMUNITY_SINGULAR_PHOTO');
                    $creator->html = $template->fetch('status.photo');

                    $status->addCreator($creator);
                }

                if ($config->get('enablevideos')) {
                    /* Video creator */
                    $creator = new CUserStatusCreator('video');
                    $creator->title = JText::_('COM_COMMUNITY_SINGULAR_VIDEO');
                    $creator->html = $template->fetch('status.video');

                    $status->addCreator($creator);
                }

                if ($config->get('enableevents') && ($config->get('createevents') || COwnerHelper::isCommunityAdmin())
                ) {
                    /* Event creator */
                    //CFactory::load( 'helpers' , 'event' );
                    $dateSelection = CEventHelper::getDateSelection();

                    $model = CFactory::getModel('events');
                    $categories = $model->getCategories();

                    // Load category tree

                    $cTree = CCategoryHelper::getCategories($categories);
                    $lists['categoryid'] = CCategoryHelper::getSelectList('events', $cTree);

                    $template->set('startDate', $dateSelection->startDate);
                    $template->set('endDate', $dateSelection->endDate);
                    $template->set('startHourSelect', $dateSelection->startHour);
                    $template->set('endHourSelect', $dateSelection->endHour);
                    $template->set('startMinSelect', $dateSelection->startMin);
                    $template->set('repeatEnd', $dateSelection->endDate);
                    $template->set('enableRepeat', $my->authorise('community.view', 'events.repeat'));
                    $template->set('endMinSelect', $dateSelection->endMin);
                    $template->set('startAmPmSelect', $dateSelection->startAmPm);
                    $template->set('endAmPmSelect', $dateSelection->endAmPm);
                    $template->set('lists', $lists);

                    $creator = new CUserStatusCreator('event');
                    $creator->title = JText::_('COM_COMMUNITY_SINGULAR_EVENT');
                    $creator->html = $template->fetch('status.event');

                    $status->addCreator($creator);
                }
            }
        }

        /**
         * //return the css class name if the user is online
         * @param user object $user
         * @return string
         */
        static function onlineIndicator($user){
            if($user->isOnline()){
                return 'joms-online';
            }
        }

        /**
         * @param $message
         * @param $actor
         * @param $object
         * @param array $info
         * @return bool
         */
        static function parseTaggedUserNotification($message, $actor, $object = null, $info = array())
        {
            $pattern = '/@\[\[(\d+):([a-z]+):([^\]]+)\]\]/';
            preg_match_all($pattern, $message, $matches);

            if (isset($matches[1]) && count($matches[1]) > 0) {
                //lets count total recipients and blast notifications
                $taggedIds = array();

                foreach ($matches[1] as $uid) {
                    $taggedIds[] = (CFactory::getUser($uid)->get('id') != 0) ? $uid : null;
                }

                if ($info['type'] == 'discussion-comment') {
                    //link to lead the user to the discussion
                    $url = CRoute::emailLink(
                        'index.php?option=com_community&view=groups&task=viewdiscussion&groupid=' . $info['group_id'] . '&topicid=' . $info['discussion_id'] . '#activity-stream-container'
                    );
                    $params = new CParameter();
                    $params->set('url', $url);
                    $params->set('content', $message);
                    $params->set(
                        'discussion',
                        '<a href="' . $url . '">' . JText::_('COM_COMMUNITY_DISCUSSION') . '</a>'
                    );

                    $emailSubject = JText::sprintf('COM_COMMUNITY_DISCUSSION_USER_TAGGED_EMAIL_SUBJECT');
                    $emailContent = JText::sprintf('COM_COMMUNITY_USER_TAGGED_COMMENT_EMAIL_CONTENT', $url);
                    $notificationMessage = JText::_('COM_COMMUNITY_NOTIFICATION_DISCUSSION_USER_TAGGED');
                } else {
                    if ($info['type'] == 'album-comment') {
                        //link to lead the user to the album
                        $url = CRoute::emailLink(
                            'index.php?option=com_community&view=photos&task=album&userid=' . $info['album_creator_id'] . '&albumid=' . $info['album_id'] . '#activity-stream-container'
                        );
                        $params = new CParameter();
                        $params->set('url', $url);
                        $params->set('content', $message);
                        $params->set(
                            'album',
                            '<a href="' . $url . '">' . JText::_('COM_COMMUNITY_SINGULAR_ALBUM') . '</a>'
                        );

                        $emailSubject = JText::sprintf('COM_COMMUNITY_ALBUM_USER_TAGGED_EMAIL_SUBJECT');
                        $emailContent = JText::sprintf('COM_COMMUNITY_USER_TAGGED_COMMENT_EMAIL_CONTENT', $url);
                        $notificationMessage = JText::_('COM_COMMUNITY_NOTIFICATION_ALBUM_USER_TAGGED');
                    } else {
                        if ($info['type'] == 'video-comment') {
                            //link to lead the user to the video
                            $url = CRoute::emailLink(
                                'index.php?option=com_community&view=videos&task=video&userid=' . $actor->id . '&videoid=' . $info['video_id'] . '#activity-stream-container'
                            );
                            $params = new CParameter();
                            $params->set('url', $url);
                            $params->set('content', $message);
                            $params->set(
                                'video',
                                '<a href="' . $url . '">' . JText::_('COM_COMMUNITY_SINGULAR_VIDEO') . '</a>'
                            );

                            $emailSubject = JText::sprintf('COM_COMMUNITY_VIDEO_USER_TAGGED_EMAIL_SUBJECT');
                            $emailContent = JText::sprintf('COM_COMMUNITY_USER_TAGGED_COMMENT_EMAIL_CONTENT', $url);
                            $notificationMessage = JText::_('COM_COMMUNITY_NOTIFICATION_VIDEO_USER_TAGGED');

                        } else {
                            if ($info['type'] == 'image-comment') {
                                //link to lead the user to the picture
                                $url = CRoute::emailLink(
                                    'index.php?option=com_community&view=photos&task=photo&userid=' . $actor->id . '&albumid=' . $info['album_id'] . '&photoid=' . $info['image_id'] . '#activity-stream-container'
                                );
                                $params = new CParameter();
                                $params->set('url', $url);
                                $params->set('content', $message);
                                $params->set(
                                    'photo',
                                    '<a href="' . $url . '">' . JText::_('COM_COMMUNITY_SINGULAR_PHOTO') . '</a>'
                                );

                                $emailSubject = JText::sprintf('COM_COMMUNITY_PHOTO_USER_TAGGED_EMAIL_SUBJECT');
                                $emailContent = JText::sprintf('COM_COMMUNITY_USER_TAGGED_COMMENT_EMAIL_CONTENT', $url);
                                $notificationMessage = JText::_('COM_COMMUNITY_NOTIFICATION_PHOTO_USER_TAGGED');

                            } else {
                                if ($info['type'] == 'post-comment' && $object != null) {
                                    //default notification, and object is activity
                                    //set parameter to be replaced in the template
                                    $url = CRoute::emailLink(
                                        'index.php?option=com_community&view=profile&userid=' . $object->actor . '&actid=' . $object->id . '#activity-stream-container'
                                    );
                                    $params = new CParameter();
                                    $params->set('url', $url);
                                    $params->set('content', $message);
                                    $params->set(
                                        'post',
                                        '<a href="' . $url . '">' . JText::_('COM_COMMUNITY_SINGULAR_POST') . '</a>'
                                    );

                                    $emailSubject = JText::sprintf('COM_COMMUNITY_PROFILE_USER_TAGGED_EMAIL_SUBJECT');
                                    $emailContent = JText::sprintf(
                                        'COM_COMMUNITY_PROFILE_USER_TAGGED_COMMENT_EMAIL_CONTENT',
                                        $url
                                    );
                                    $notificationMessage = JText::_('COM_COMMUNITY_NOTIFICATION_USER_TAGGED');
                                } else {
                                    return false;
                                }
                            }
                        }
                    }
                }

                //add to notifications
                CNotificationLibrary::add(
                    'users_tagged',
                    $actor->id,
                    $taggedIds,
                    $emailSubject,
                    $emailContent,
                    '',
                    $params,
                    true,
                    '',
                    $notificationMessage
                );
                return true;
            }
            return false;
        }

        /**
         * Automatically link username in the provided message when message contains @username
         * @param $message
         * @param bool $email
         * @param bool $usernameOnly only pass username without hyperlink
         * @return mixed $message A modified copy of the message with the proper hyperlinks or username.
         */
        static public function replaceAliasURL($message, $email = false, $usernameOnly = false)
        {
            $pattern = '/@\[\[(\d+):([a-z]+):([^\]]+)\]\]/';
            preg_match_all($pattern, $message, $matches);

            if (isset($matches[1]) && count($matches[1]) > 0) {
                foreach ($matches[1] as $key => $uid) {
                    $id = CFactory::getUser($uid)->get('id');
                    $username = $matches[3][$key];
                    if ($id != 0) {
                        if ($usernameOnly) {
                            $message = CString::str_ireplace($matches[0][$key], $username, $message);
                        } else {
                            $message = CString::str_ireplace(
                                $matches[0][$key],
                                CLinkGeneratorHelper::getUserURL($id, $username, $email),
                                $message
                            );
                        }
                    }
                }
            }

            return $message;
        }


        /**
         * Provide a list of user id and name to search, it will return the filtered user id
         * @param array $users
         * @param string $search
         * @return array of user id and total results
         */
        static public function filterUserByName($users, $search, $limitstart = 0, $limit = 8 ){
            $filteredUsers = array();

            if(empty($users)){
                $filteredUsers['users'] = array();
                $filteredUsers['total'] = 0;
                return $filteredUsers;
            }

            $db = JFactory::getDBO();
            $config = CFactory::getConfig();
            $nameField = $config->getString('displayname');

            $filterName = '';
            if($search != ''){
                $filterName = ' AND '. $db->quoteName($nameField) . ' LIKE ' . $db->Quote('%' . $search . '%');
            }

            // get all the id contains the search value
            $query = 'SELECT DISTINCT(' . $db->quoteName('id') . ') FROM '
                        . $db->quoteName('#__users').' WHERE '.$db->quoteName('id')
                        .' IN ('. implode(',',$users) .') '.$filterName
                        . ' ORDER BY ' . $db->quoteName($nameField)
                        . ' LIMIT ' . $limitstart . ',' . $limit;

            $db->setQuery($query);
            $filteredUsers['users'] = $db->loadColumn();

            // get all the id contains the search value
            $query = 'SELECT count(' . $db->quoteName('id') . ')  FROM '
                . $db->quoteName('#__users').' WHERE '.$db->quoteName('id')
                .' IN ('. implode(',',$users) .') '.$filterName;

            $db->setQuery($query);
            $filteredUsers['total'] = $db->loadResult();

            return $filteredUsers;
        }
    }
