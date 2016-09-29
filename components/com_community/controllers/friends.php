<?php
/**
 * @copyright (C) 2013 iJoomla, Inc. - All rights reserved.
 * @license GNU General Public License, version 2 (http://www.gnu.org/licenses/gpl-2.0.html)
 * @author iJoomla.com <webmaster@ijoomla.com>
 * @url https://www.jomsocial.com/license-agreement
 * The PHP code portions are distributed under the GPL license. If not otherwise stated, all images, manuals, cascading style sheets, and included JavaScript *are NOT GPL, and are released under the IJOOMLA Proprietary Use License v1.0
 * More info at https://www.jomsocial.com/license-agreement
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

class CommunityFriendsController extends CommunityBaseController
{
    var $task;
    var $_icon = 'friends';
    var $_name = 'friends';

    // Ajax call via jQuery
    public function ajaxAutocomplete()
    {
        $config = CFactory::getConfig();
        $my = CFactory::getUser();
        $nameField = $config->getString('displayname');
        $mainframe = JFactory::getApplication();
        $jinput = $mainframe->input;

        $query = $jinput->get('query', null, 'STRING');

        $rule = $jinput->get(
            'rule',
            null,
            'STRING'
        ); // extra rule to distinguish between photo tagging or photo comment tagging AND get full users

        $fullFriendList = $jinput->get('allfriends', 0, 'INT');
        $streamId = $jinput->get('streamid', null, 'STRING');
        $groupId = $jinput->get('groupid', null, 'STRING');
        $eventId = $jinput->get('eventid', null, 'STRING');
        $photoId = $jinput->get('photoid', null, 'STRING');
        $videoId = $jinput->get('videoid', null, 'STRING');
        $albumId = $jinput->get('albumid', null, 'STRING');
        $msgId = $jinput->get('msgid', null, 'STRING');
        $discussionId = $jinput->get('discussionid', null, 'STRING');

        $rules = array();

        if ($fullFriendList) {
            $rules = array(
                'case' => 'all-friends',
            );
        } elseif ($msgId > 0) {
            $rules = array(
                'case' => 'private-message-tag',
                'message_id' => $msgId
            );
        } elseif ($videoId > 0) {
            $rules = array(
                'case' => 'video-comment-tag',
                'video_id' => $videoId
            );
        } elseif ($photoId > 0) {
            //important rules!
            if ($rule == 'photo-comment') {
                $case = 'photo-comment-tag';
            } else {
                //this is a photo and we should return a list of photo excluding the tagged user
                $case = 'photo-tag';
            }

            $rules = array(
                'case' => $case,
                'photo_id' => $photoId
            );
        } elseif ($streamId > 0 && empty($groupId) && empty($eventId)) {
            //this is normal stream
            $rules = array(
                'case' => 'public-comment',
                'activity_id' => $streamId
            );
        } elseif ($groupId > 0) {
            $rules = array(
                'case' => 'group',
                'group_id' => $groupId
            );

        } elseif ($eventId > 0) {
            $rules = array(
                'case' => 'event',
                'event_id' => $eventId
            );
        } elseif ($albumId > 0) {
            $rules = array(
                'case' => 'album',
                'album_id' => $albumId
            );
        } elseif ($discussionId > 0) {
            $rules = array(
                'case' => 'discussion',
                'discussion_id' => $discussionId
            );
        }

        // If user has less than 5 friends, select them all
        $friendsModel = CFactory::getModel('friends');
        $friends = $friendsModel->searchFriend($query, $rules);

        $suggestions = array();
        $data = array();
        $img = array();

        if (!empty($friends)) {
            foreach ($friends as $friend) {
                $suggestions[] = $friend->$nameField;
                // $suggestions[] = $friend->$nameField;
                $img[] = '<img src="' . $friend->getThumbAvatar() . '" width="24"/>';
                $data[] = $friend->id;
            }

        }

        $response = new stdClass();
        $response->query = $query;
        $response->suggestions = $suggestions; //array('Libyan', 'Liberia', 'Lithuania');
        $response->data = $data; //array('LR', 'LI', 'LT');
        $response->img = $img;
        echo json_encode($response);
        exit;
    }

    public function ajaxIphoneFriends()
    {
        $objResponse = new JAXResponse();
        $document = JFactory::getDocument();

        $viewType = $document->getType();
        $view = $this->getView('friends', '', $viewType);


        $html = '';

        ob_start();
        $this->display();
        $content = ob_get_contents();
        ob_end_clean();

        $tmpl = new CTemplate();
        $tmpl->set('toolbar_active', 'friends');
        $simpleToolbar = $tmpl->fetch('toolbar.simple');

        $objResponse->addAssign('social-content', 'innerHTML', $simpleToolbar . $content);
        return $objResponse->sendResponse();
    }

    public function edit()
    {
        // Get/Create the model
        $model = $this->getModel('profile');
        $model->setProfile('hello me');

        $this->display(false, __FUNCTION__);
    }

    public function display($cacheable = false, $urlparams = false)
    {
        // By default, display the user profile page
        $this->friends();
    }

    /**
     * View all friends. Could be current user, if id is not defined
     * otherise, show your own friends
     */
    public function friends()
    {
        $mainframe = JFactory::getApplication();
        $jinput = $mainframe->input;

        $document = JFactory::getDocument();
        $my = CFactory::getUser();

        $viewType = $document->getType();
        $tagsFriends = $jinput->get->get('tags', '', 'NONE');

        $view = $this->getView('friends', '', $viewType);
        $model = $this->getModel('friends');

        // Get the friend id to be displayed
        $id = $jinput->get('userid', $my->id);

        // Check privacy setting
        if (!$my->authorise('community.view', 'friends.' . $id)) {

            if ($my->id == 0) {
                $this->blockUnregister();
            }
            echo "<div class=\"cEmpty cAlert\">" . JText::_('COM_COMMUNITY_PRIVACY_ERROR_MSG') . "</div>";
            return;
        }

        // The friend count might be out of date. Lets fix it now
        $model->updateFriendCount($id);

        $data = new stdClass();
        echo $view->get('friends');
    }

    /**
     * Search Within Friends
     */
    public function friendsearch()
    {
        $mainframe = JFactory::getApplication();
        $jinput = $mainframe->input;

        $data = new stdClass();
        $view = $this->getView('friends');
        $model = $this->getModel('search');
        $profileModel = $this->getModel('profile');

        $fields = $profileModel->getAllFields();

        $search = $jinput->request->getArray();
        $data->query = $jinput->request->get('q', '', 'STRING');
        $friendId = $jinput->request->get('userid', '', 'INT');
        $avatarOnly = $jinput->get('avatar', '', 'STRING');

        //prefill the search values.
        $fields = $this->_fillSearchValues($fields, $search);

        $data->fields = $fields;

        if (isset($search)) {
            $model = $this->getModel('search');
            $data->result = $model->searchPeople($search, $avatarOnly, $friendId);

            //pre-load cuser.
            $ids = array();
            if (!empty($data->result)) {
                foreach ($data->result as $item) {
                    $ids[] = $item->id;
                }

                CFactory::loadUsers($ids);
            }
        }

        $data->pagination = $model->getPagination();

        echo $view->get('friendsearch', $data);
    }

    /**
     * Show the user invite window
     */
    public function invite()
    {
        $view = CFactory::getView('friends');
        $validated = false;

        $my = CFactory::getUser();

        if ($my->id == 0) {
            return $this->blockUnregister();
        }

        $mainframe = JFactory::getApplication();
        $jinput = $mainframe->input;

        if ($jinput->post->get('action', '', 'STRING') == 'invite') {
            //CFactory::load( 'libraries' , 'apps' );
            $appsLib = CAppPlugins::getInstance();
            $saveSuccess = $appsLib->triggerEvent('onFormSave', array('jsform-friends-invite'));

            if (empty($saveSuccess) || !in_array(false, $saveSuccess)) {
                $validated = true;
                $emailExistError = array();
                $emailInvalidError = array();

                $emails = $jinput->post->get('emails', '', 'STRING');

                if (empty($emails)) {
                    $validated = false;
                    $mainframe->enqueueMessage(JText::_('COM_COMMUNITY_FRIENDS_EMAIL_CANNOT_BE_EMPTY'), 'error');
                } else {
                    $emails = explode(',', $emails);
                    $userModel = CFactory::getModel('user');

                    // Do simple email validation
                    // make sure user is not a member yet
                    // check for duplicate emails
                    // make sure email is valid
                    // make sure user is not already on the system

                    $actualEmails = array();
                    for ($i = 0; $i < count($emails); $i++) {
                        //trim the value
                        $emails[$i] = JString::trim($emails[$i]);

                        if (
                            !empty($emails[$i])
                            && (boolean)filter_var($emails[$i], FILTER_VALIDATE_EMAIL)
                        ) {
                            //now if the email already exist in system, alert the user.
                            if (!$userModel->userExistsbyEmail($emails[$i])) {
                                $actualEmails[$emails[$i]] = true;
                            } else {
                                $emailExistError[] = $emails[$i];
                            }
                        } else {
                            // log the error and display to user.
                            if (!empty($emails[$i])) {
                                $emailInvalidError[] = $emails[$i];
                            }
                        }
                    }

                    $emails = array_keys($actualEmails);
                    unset($actualEmails);

                    if (count($emails) <= 0) {
                        $validated = false;
                    }

                    if (count($emailInvalidError) > 0) {
                        for ($i = 0; $i < count($emailInvalidError); $i++) {
                            $mainframe->enqueueMessage(
                                JText::sprintf('COM_COMMUNITY_INVITE_EMAIL_INVALID', $emailInvalidError[$i]),
                                'error'
                            );
                        }
                        $validated = false;
                    }


                    if (count($emailExistError) > 0) {
                        for ($i = 0; $i < count($emailExistError); $i++) {
                            $mainframe->enqueueMessage(
                                JText::sprintf('COM_COMMUNITY_INVITE_EMAIL_EXIST', $emailExistError[$i]),
                                'error'
                            );
                        }
                        $validated = false;
                    }
                }

                $message = $jinput->post->get('message', '', 'STRING');

                $config = CFactory::getConfig();

                if ($validated) {
                    //CFactory::load( 'libraries' , 'notification' );

                    for ($i = 0; $i < count($emails); $i++) {
                        $emails[$i] = JString::trim($emails[$i]);

                        $params = new CParameter('');
                        //$params->set( 'url' , 'index.php?option=com_community&view=profile&userid='.$my->id.'&invite='.$my->id );
                        $params->set('url', 'index.php?option=com_community&view=register');
                        $params->set('message', $message);
                        CNotificationLibrary::add(
                            'friends_invite_users',
                            $my->id,
                            $emails[$i],
                            JText::sprintf(
                                'COM_COMMUNITY_INVITE_EMAIL_SUBJECT',
                                $my->getDisplayName(),
                                $config->get('sitename')
                            ),
                            '',
                            'friends.invite',
                            $params
                        );
                    }

                    $mainframe->enqueueMessage(
                        JText::sprintf(
                            (CStringHelper::isPlural(
                                count($emails)
                            )) ? 'COM_COMMUNITY_INVITE_EMAIL_SENT_MANY' : 'COM_COMMUNITY_INVITE_EMAIL_SENT',
                            count($emails)
                        )
                    );

                    //add user points - friends.invite removed @ 20090313
                    //clear the post value.
                    $jinput->set('emails', '');
                    $jinput->set('message', '');

                } else {
                    // Display error message
                }
            }
        }

        echo $view->get('invite');
    }

    public function online()
    {
        $view = $this->getView('friends');
        echo $view->get(__FUNCTION__);

    }

    public function news()
    {
        $view = $this->getView('friends');
        echo $view->get(__FUNCTION__);
    }

    /**
     * List down all request that you've sent but not approved by the other side yet
     */
    public function sent()
    {
        $my = CFactory::getUser();

        if ($my->id == 0) {
            return $this->blockUnregister();
        }

        $view = $this->getView('friends');
        $model = $this->getModel('friends');

        $data = new stdClass();
        $rsent = $model->getSentRequest($my->id);

        $data->sent = $rsent;
        $data->pagination = $model->getPagination();

        echo $view->get('sent', $data);
    }

    /**
     * Add new friend
     */
    public function add()
    {
        $view = $this->getView('friends');
        $mainframe = JFactory::getApplication();
        $jinput = $mainframe->input;

        $my = CFactory::getUser();
        if ($my->id == 0) {
            return $this->blockUnregister();
        }

        $model = $this->getModel('friends');
        $id = $jinput->get('userid');
        $data = CFactory::getUser($id);

        $task = $jinput->get->get('task', '');
        $task = $task . '()';
        $this->task = $task;

        // If a query is sent, seach for it
        if ($query = $jinput->post->get('userid', '', 'INT')) {
            $model->addFriend($id, $my->id);

            //trigger for onFriendRequest
            $eventObject = new stdClass();
            $eventObject->profileOwnerId = $my->id;
            $eventObject->friendId = $id;
            $this->triggerFriendEvents('onFriendRequest', $eventObject);
            unset($eventObject);

            echo $view->get('addSuccess', $data);
        } else {
            //disallow self add as a friend

            if ($my->id == $id) {
                $view->addInfo(JText::_('COM_COMMUNITY_FRIENDS_CANNOT_ADD_SELF'));
                $this->display();
            } //disallow add existing friend
            elseif (count($model->getFriendConnection($my->id, $id)) > 0) {

                $view->addInfo(JText::_('COM_COMMUNITY_FRIENDS_IS_ALREADY_FRIEND'));
                $this->display();

            } else {
                echo $view->get('add', $data);
            }

        }

    }

    public function remove()
    {
        $my = CFactory::getUser();
        $mainframe = JFactory::getApplication();
        $jinput = $mainframe->input;

        if ($my->id == 0) {
            return $this->blockUnregister();
        }

        $view = $this->getView('friends');
        $friendId = $jinput->get->get('fid', '', 'INT');
        $friend = CFactory::getUser($friendId);
        if ($this->delete($friendId)) {
            $view->addInfo(JText::sprintf('COM_COMMUNITY_FRIENDS_REMOVED', $friend->getDisplayName()));
        } else {
            $view->addinfo(JText::_('COM_COMMUNITY_FRIENDS_REMOVING_FRIEND_ERROR'));
        }

        $this->display();
    }

    /**
     * Method to cancel a friend request
     */
    public function deleteSent()
    {
        $my = CFactory::getUser();
        $mainframe = JFactory::getApplication();
        $jinput = $mainframe->input;

        if ($my->id == 0) {
            return $this->blockUnregister();
        }

        $view = $this->getView('friends');
        $model = $this->getModel('friends');

        $friendId = $jinput->post->get('fid', '', 'INT');
        $redirect = $jinput->post->get('redirect', '', 'STRING');
        $message = '';

        if ($model->deleteSentRequest($my->id, $friendId)) {
            $message = JText::_('COM_COMMUNITY_FRIENDS_REQUEST_CANCELED');

            if($redirect != ''){
                $mainframe->redirect( $redirect, $message, 'STRING');
            }
            //add user points - friends.request.cancel removed @ 20090313
        } else {
            $message = JText::_('COM_COMMUNITY_FRIENDS_REQUEST_CANCELLED_ERROR');
        }

        $view->addInfo($message);
        $this->sent();
    }

    /**
     * Ajax function to reject a friend request
     **/
    public function ajaxRejectRequest($requestId)
    {
        $filter       = JFilterInput::getInstance();
        $my           = CFactory::getUser();
        $friendsModel = CFactory::getModel('friends');

        $requestId    = $filter->clean($requestId, 'int');
        $json         = array();

        if ($my->id == 0) {
            return $this->ajaxBlockUnregister();
        }

        if ( $friendsModel->isMyRequest($requestId, $my->id)) {
            $pendingInfo = $friendsModel->getPendingUserId($requestId);

            if ( $friendsModel->rejectRequest($requestId)) {
                //add user points - friends.request.reject removed @ 20090313

                $friendId = $pendingInfo->connect_from;
                $friend = CFactory::getUser($friendId);
                $friendUrl = CRoute::_('index.php?option=com_community&view=profile&userid=' . $friendId);

                $json['success'] = true;
                $json['message'] = JText::sprintf('COM_COMMUNITY_FRIEND_REQUEST_DECLINED', $friend->getDisplayName(), $friendUrl);

                //trigger for onFriendReject
                $eventObject = new stdClass();
                $eventObject->profileOwnerId = $my->id;
                $eventObject->friendId = $friendId;
                $this->triggerFriendEvents('onFriendReject', $eventObject);
                unset($eventObject);

            } else {
                $json['error'] = JText::sprintf('COM_COMMUNITY_FRIEND_REQUEST_REJECT_FAILED', $requestId);
            }

        } else {
            $json['error'] = JText::_('COM_COMMUNITY_FRIENDS_NOT_YOUR_REQUEST');
        }

        die( json_encode($json) );
    }

    /**
     * Ajax function to approve a friend request
     **/
    public function ajaxApproveRequest($requestId)
    {
        $filter       = JFilterInput::getInstance();
        $my           = CFactory::getUser();
        $friendsModel = CFactory::getModel('friends');

        $requestId    = $filter->clean($requestId, 'int');
        $json         = array();

        if ($my->id == 0) {
            return $this->ajaxBlockUnregister();
        }

        if ($friendsModel->isMyRequest($requestId, $my->id)) {
            $connected = $friendsModel->approveRequest($requestId);

            if ($connected) {
                $act = new stdClass();
                $act->cmd = 'friends.request.approve';
                $act->actor = $connected[0];
                $act->target = $connected[1];
                $act->title = ''; //JText::_('COM_COMMUNITY_ACTIVITY_FRIENDS_NOW');
                $act->content = '';
                $act->app = 'friends.connect';
                $act->cid = 0;

                //add user points - give points to both parties
                //CFactory::load( 'libraries' , 'userpoints' );
                if(CUserPoints::assignPoint('friends.request.approve')){
                    CActivityStream::add($act);
                };

                $friendId = ($connected[0] == $my->id) ? $connected[1] : $connected[0];
                $friend = CFactory::getUser($friendId);
                $friendUrl = CRoute::_('index.php?option=com_community&view=profile&userid=' . $friendId);
                CUserPoints::assignPoint('friends.request.approve', $friendId);

                // need to both user's friend list
                $friendsModel->updateFriendCount($my->id);
                $friendsModel->updateFriendCount($friendId);

                $params = new CParameter('');
                $params->set('url', 'index.php?option=com_community&view=profile&userid=' . $my->id);
                $params->set('friend', $my->getDisplayName());
                $params->set('friend_url', 'index.php?option=com_community&view=profile&userid=' . $my->id);

                CNotificationLibrary::add(
                    'friends_create_connection',
                    $my->id,
                    $friend->id,
                    JText::_('COM_COMMUNITY_FRIEND_REQUEST_APPROVED'),
                    '',
                    'friends.approve',
                    $params
                );

                $json['success'] = true;
                $json['message'] = JText::sprintf('COM_COMMUNITY_FRIEND_REQUEST_ACCEPTED', $friend->getDisplayName(), $friendUrl);
                $json['display'] = CFriendsHelper::getUserFriendDropdown($friendId);

                //trigger for onFriendApprove
                $eventObject = new stdClass();
                $eventObject->profileOwnerId = $my->id;
                $eventObject->friendId = $friendId;
                $this->triggerFriendEvents('onFriendApprove', $eventObject);
                unset($eventObject);
            }

        } else {
            $json['error'] = JText::_('COM_COMMUNITY_FRIENDS_NOT_YOUR_REQUEST');
        }

        $this->cacheClean(array(COMMUNITY_CACHE_TAG_ACTIVITIES));

        die( json_encode($json) );
    }

    public function ajaxSaveFriend($postVars)
    {
        $filter = JFilterInput::getInstance();
        $postVars = $filter->clean($postVars, 'array');

        //@todo filter paramater
        $model = $this->getModel('friends');
        $my = CFactory::getUser();

        if ($my->id == 0) {
            return $this->ajaxBlockUnregister();
        }

        $postVars = CAjaxHelper::toArray($postVars);
        $id = $postVars['userid']; //get it from post
        $msg = strip_tags($postVars['msg']);
        $data = CFactory::getUser($id);

        $connection = $model->getFriendConnection($my->id, $id);
        if (!empty($connection)) {
            die();
        }

        // @rule: Do not allow users to request more friend requests as they are allowed to.
        if (CLimitsLibrary::exceedDaily('friends')) {
            $json = array( 'error' => JText::_('COM_COMMUNITY_LIMIT_FRIEND_REQUEST_REACHED') );
            die( json_encode($json) );
        }

        if (count($postVars) > 0) {
            $model->addFriend($id, $my->id, $msg);

            $json = array( 'message' => JText::sprintf('COM_COMMUNITY_FRIENDS_WILL_RECEIVE_REQUEST', $data->getDisplayName()) );

            // Add notification
            $params = new CParameter('');
            $params->set('url', 'index.php?option=com_community&view=friends&task=pending');
            $params->set('msg', $msg);

            CNotificationLibrary::add(
                'friends_request_connection',
                $my->id,
                $id,
                JText::_('COM_COMMUNITY_FRIEND_ADD_REQUEST'),
                '',
                'friends.request',
                $params
            );

            //add user points - friends.request.add removed @ 20090313
            //trigger for onFriendRequest
            $eventObject = new stdClass();
            $eventObject->profileOwnerId = $my->id;
            $eventObject->friendId = $id;
            $this->triggerFriendEvents('onFriendRequest', $eventObject);
            unset($eventObject);
        }

        die( json_encode($json) );
    }

    /**
     * Show internal invite
     * Internal invite is more like an internal messaging system
     */
    public function ajaxInvite()
    {
        return $objResponse->sendResponse();
    }

    /**
     * Displays a dialog to the user if he / she really wants to
     * cancel the friend request
     **/
    public function ajaxCancelRequest($friendsId, $redirect = null)
    {
        $my = CFactory::getUser();

        $filter = JFilterInput::getInstance();
        $friendsId = $filter->clean($friendsId, 'int');
        $redirect = $filter->clean($redirect, 'string');

        if ($my->id == 0) {
            return $this->ajaxBlockUnregister();
        }

        $html  = JText::_('COM_COMMUNITY_FRIENDS_CONFIRM_CANCEL_REQUEST');
        $html .= '<form method="POST" action="' . CRoute::_('index.php?option=com_community&view=friends&task=deleteSent', false) . '">';
        $html .= '<input type="hidden" name="fid" value="' . $friendsId . '">';
        $html .= '<input type="hidden" name="redirect" value="' . $redirect . '">';
        $html .= '</form>';

        $json = array(
            'title'  => JText::_('COM_COMMUNITY_CANCEL_FRIEND_REQUEST'),
            'html'   => $html,
            'btnYes' => JText::_('COM_COMMUNITY_YES_BUTTON'),
            'btnNo'  => JText::_('COM_COMMUNITY_NO_BUTTON')
        );

        die( json_encode($json) );
    }

    /**
     * Show the connection request box
     */
    public function ajaxConnect($friendId)
    {
        // Block unregistered users.
        if (!COwnerHelper::isRegisteredUser()) {
            return $this->ajaxBlockUnregister();
        }

        $filter = JFilterInput::getInstance();
        $friendId = $filter->clean($friendId, 'int');

        //@todo filter paramater
        $model = $this->getModel('friends');
        $blockModel = $this->getModel('block');

        $my = CFactory::getUser();
        $view = $this->getView('friends');
        $user = CFactory::getUser($friendId);


        $blockUser = new blockUser();
        $config = CFactory::getConfig();

        if (CLimitsLibrary::exceedDaily('friends')) {
            $json = array(
                'title' => JText::_('COM_COMMUNITY_FRIENDS_ADD_NEW_FRIEND'),
                'error' => JText::_('COM_COMMUNITY_LIMIT_FRIEND_REQUEST_REACHED')
            );
            die( json_encode($json) );
        }

        // Block blocked users
        if ($blockModel->getBlockStatus($my->id, $friendId) && !COwnerHelper::isCommunityAdmin()) {
            $blockUser->ajaxBlockMessage();
        }

        // Warn owner that the user has been blocked, cannot add as friend
        if ($blockModel->getBlockStatus($friendId, $my->id)) {
            $blockUser->ajaxBlockWarn();
        }


        $connection = $model->getFriendConnection($my->id, $friendId);

        $html = '';
        $actions = '';

        //@todo disallow self add as a friend
        //@todo disallow add existing friend
        if ($my->id == $friendId) {
            $json = array(
                'title' => JText::_('COM_COMMUNITY_FRIENDS_ADD_NEW_FRIEND'),
                'error' => JText::_('COM_COMMUNITY_FRIENDS_CANNOT_ADD_SELF')
            );
        } elseif ($user->isBlocked()) {
            $json = array(
                'title' => JText::_('COM_COMMUNITY_FRIENDS_ADD_NEW_FRIEND'),
                'error' => JText::_('COM_COMMUNITY_FRIENDS_CANNOT_ADD_INACTIVE_USER')
            );
        } elseif (count($connection) > 0) {
            if ($connection[0]->connect_from == $my->id) {
                $json = array(
                    'title' => JText::_('COM_COMMUNITY_FRIENDS_ADD_NEW_FRIEND'),
                    'error' => JText::sprintf('COM_COMMUNITY_FRIENDS_REQUEST_ALREADY_SENT', $user->getDisplayName())
                );
            } else {
                $json = array(
                    'title'         => JText::_('COM_COMMUNITY_PROFILE_PENDING_FRIEND_REQUEST'),
                    'avatar'        => $user->getThumbAvatar(),
                    'desc'          => str_replace('{actor}', '<strong>' . $user->getDisplayName() . '</strong>', JText::_('COM_COMMUNITY_FRIEND_ADD_REQUEST')),
                    'message'       => nl2br( $connection[0]->msg ),
                    'connection_id' => $connection[0]->connection_id,
                    'btnAccept'     => JText::_('COM_COMMUNITY_PENDING_ACTION_APPROVE'),
                    'btnReject'     => JText::_('COM_COMMUNITY_FRIENDS_PENDING_ACTION_REJECT'),
                    // Retain error message for backward compatibility.
                    'error'         => JText::sprintf('COM_COMMUNITY_FRIEND_REQUEST_ALREADY_RECEIVED', $user->getDisplayName())
                );
            }
        } else {
            $json = array(
                'title'     => JText::_('COM_COMMUNITY_FRIENDS_ADD_NEW_FRIEND'),
                'avatar'    => $user->getThumbAvatar(),
                'desc'      => JText::sprintf('COM_COMMUNITY_CONFIRM_ADD_FRIEND', $user->getDisplayName()),
                'message'   => JText::_('COM_COMMUNITY_PROFILE_ADD_FRIEND_DEFAULT'),
                'btnAdd'    => JText::_('COM_COMMUNITY_FRIENDS_ADD_BUTTON'),
                'btnCancel' => JText::_('COM_COMMUNITY_CANCEL_BUTTON')
            );
        }

        die( json_encode($json) );
    }

    public function ajaxConfirmFriendRemoval($id) {
        $filter = JFilterInput::getInstance();
        $id     = $filter->clean($id, 'int');
        $friend = CFactory::getUser($id);
        $html   = '';

        $html .= '<p>' . JText::sprintf('COM_COMMUNITY_FRIEND_REMOVAL_WARNING', $friend->getDisplayName()) . '</p>';
        $html .= '<p><label><input type="checkbox"> ' . JText::_('COM_COMMUNITY_ALSO_BLOCK_FRIEND') . '</label></p>';

        $json = array(
            'title'  => JText::_('COM_COMMUNITY_REMOVE_FRIEND'),
            'html'   => $html,
            'btnYes' => JText::_('COM_COMMUNITY_YES_BUTTON'),
            'btnNo'  => JText::_('COM_COMMUNITY_NO_BUTTON')
        );

        die( json_encode($json) );
    }

    public function ajaxBlockFriend($id) {
        $filter = JFilterInput::getInstance();
        $id     = $filter->clean($id, 'int');
        $json   = array();

        if ($this->block($id)) {
            $friend = CFactory::getUser($id);
            $json['success'] = true;
            $json['message'] = JText::sprintf('COM_COMMUNITY_FRIEND_BLOCKED', $friend->getDisplayName());
        } else {
            $json['error'] = JText::_('COM_COMMUNITY_ERROR_BLOCK_USER');
        }

        die( json_encode($json) );
    }

    public function ajaxRemoveFriend($id) {
        $filter = JFilterInput::getInstance();
        $id     = $filter->clean($id, 'int');
        $json   = array();

        if ($this->delete($id)) {
            $friend = CFactory::getUser($id);
            $my     = CFactory::getUser();

            // Update friend list of both current user and friend.
            $friend->updateFriendList(true);
            $my->updateFriendList(true);

            $json['success'] = true;
            $json['message'] = JText::sprintf('COM_COMMUNITY_FRIENDS_REMOVED', $friend->getDisplayName());

        } else {
            $json['error'] = JText::_('COM_COMMUNITY_FRIENDS_REMOVING_FRIEND_ERROR');
        }

        die( json_encode($json) );
    }

    /**
     * List down all connection request waiting for user to approve
     */
    public function pending()
    {
        $mainframe = JFactory::getApplication();
        $jinput = $mainframe->input;
        $my = CFactory::getUser();
        if ($my->id == 0) {
            return $this->blockUnregister();
        }

        $view = $this->getView('friends');
        $model = $this->getModel('friends');
        $usermodel = $this->getModel('user');

        // @todo: make sure the rejectId and approveId is valid for this user
        if ($id = $jinput->get->get('rejectId', 0, 'NONE')) {
            if (!$model->rejectRequest($id)) {
                $mainframe->enqueueMessage(JText::sprintf('COM_COMMUNITY_FRIENDS_REQUEST_REJECT_FAILED', $id));
            }
        }

        if ($id = $jinput->get->get('approveId', 0, 'NONE')) {
            $connected = $model->approveRequest($id);

            // If approbe id is not valid or already approve, $connected will
            // be null.. yuck
            if ($connected) {
                $act = new stdClass();
                $act->cmd = 'friends.request.approve';
                $act->actor = $connected[0];
                $act->target = $connected[1];
                $act->title = ''; //JText::_('COM_COMMUNITY_ACTIVITY_FRIENDS_NOW');
                $act->content = '';
                $act->app = 'friends.connect';
                $act->cid = 0;

                //add user points - give points to both parties
                //CFactory::load( 'libraries' , 'userpoints' );
                if(CUserPoints::assignPoint('friends.request.approve')){
                    CActivityStream::add($act);
                };

                $friendId = ($connected[0] == $my->id) ? $connected[1] : $connected[0];
                $friend = CFactory::getUser($friendId);
                CUserPoints::assignPoint('friends.request.approve', $friendId);

                $mainframe->enqueueMessage(JText::sprintf('COM_COMMUNITY_FRIENDS_NOW', $friend->getDisplayName()));
            }
        }

        $data = new stdClass();
        $rpending = $model->getPending($my->id);

        $data->pending = $rpending;
        $data->pagination = $model->getPagination();

        echo $view->get(__FUNCTION__, $data);
    }

    /**
     * Browse the active user's friends
     */
    public function browse()
    {
        $view = $this->getView('friends');
        echo $view->get('browse');

    }

    public function search()
    {
        $mainframe = JFactory::getApplication();
        $jinput = $mainframe->input;
        $view = $this->getView('friends');
        $data = array();
        $data['query'] = '';
        $data['result'] = null;

        // If a query is sent, seach for it
        if ($query = $jinput->post->get('q', '', 'STRING')) {
            $model = $this->getModel('friends');
            $data['result'] = $model->searchPeople($query);
            $data['query'] = $query;
        }

        echo $view->get(__FUNCTION__, $data);
    }

    /*
     * friends event name
     * object
     */
    public function triggerFriendEvents($eventName, &$args, $target = null)
    {
        CError::assert($args, 'object', 'istype', __FILE__, __LINE__);

        require_once(COMMUNITY_COM_PATH . '/libraries/apps.php');
        $appsLib = CAppPlugins::getInstance();
        $appsLib->loadApplications();

        $params = array();
        $params[] = $args;

        if (!is_null($target)) {
            $params[] = $target;
        }

        $appsLib->triggerEvent($eventName, $params);
        return true;
    }

    /**
     * Block user
     */
    public function blockUser()
    {
        $my = CFactory::getUser();
        $mainframe = JFactory::getApplication();
        $jinput = $mainframe->input;

        if ($my->id == 0) {
            return $this->blockUnregister();
        }

        $userId = $jinput->get->get('fid', '', 'INT');

        CFactory::load('libraries', 'block');
        $blockUser = new blockUser;
        $blockUser->block($userId);
    }

    public function delete($id)
    {
        $my = CFactory::getUser();
        $friend = CFactory::getUser($id);

        if (empty($my->id) || empty($friend->id)) {
            return false;
        }

        //CFactory::load( 'helpers' , 'friends' );
        $isFriend = $my->isFriendWith($friend->id);
        if (!$isFriend) {
            return true;
        }

        $model = CFactory::getModel('friends');

        if (!$model->deleteFriend($my->id, $friend->id)) {
            return false;
        }

        // Substract the friend count
        $model->updateFriendCount($my->id);
        $model->updateFriendCount($friend->id);

        // Add user points
        // We deduct points to both parties
        //CFactory::load( 'libraries' , 'userpoints' );
        CUserPoints::assignPoint('friends.remove');
        CUserPoints::assignPoint('friends.remove', $friend->id);

        // Trigger for onFriendRemove
        $eventObject = new stdClass();
        $eventObject->profileOwnerId = $my->id;
        $eventObject->friendId = $friend->id;
        $this->triggerFriendEvents('onFriendRemove', $eventObject);
        unset($eventObject);

        return true;
    }

    public function block($id)
    {
        $my = CFactory::getUser();
        $friend = CFactory::getUser($id);

        if (empty($my->id) || empty($friend->id)) {
            return false;
        }

        $model = CFactory::getModel('block');

        if (!$model->blockUser($my->id, $friend->id)) {
            return false;
        }

        $this->delete($friend->id);

        return true;
    }

    public function mutualFriends()
    {
        $mainframe = JFactory::getApplication();
        $jinput = $mainframe->input;

        $document = JFactory::getDocument();
        $my = CFactory::getUser();

        $viewType = $document->getType();
        $tagsFriends = $jinput->get->get('tags', '', 'NONE');

        $view = $this->getView('friends', '', $viewType);
        $model = $this->getModel('friends');

        // Get the friend id to be displayed
        $id = $jinput->get('userid', $my->id);

        // Check privacy setting
        $accesAllowed = CPrivacy::isAccessAllowed($my->id, $id, 'user', 'privacyFriendsView');
        if (!$accesAllowed || ($my->id == 0 && $id == 0)) {
            $this->blockUnregister();
            return;
        }

        $data = new stdClass();
        echo $view->get('friends');
    }

    private function _fillSearchValues(&$fields, $search)
    {
        if (isset($search)) {
            foreach ($fields as $group) {
                $field = $group->fields;

                for ($i = 0; $i < count($field); $i++) {
                    $fieldid = $field[$i]->id;
                    if (!empty($search['field' . $fieldid])) {
                        $tmpEle = $search['field' . $fieldid];
                        if (is_array($tmpEle)) {
                            $tmpStr = "";
                            foreach ($tmpEle as $ele) {
                                $tmpStr .= $ele . ',';
                            }
                            $field[$i]->value = $tmpStr;
                        } else {
                            $field[$i]->value = $search['field' . $fieldid];
                        }
                    }
                }
                //end for i
            }
            //end foreach
        }
        return $fields;
    }
}
