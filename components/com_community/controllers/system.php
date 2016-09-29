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

class CommunitySystemController extends CommunityBaseController {

    public function ajaxShowInvitationForm($friends, $callback, $cid, $displayFriends, $displayEmail, $type = '') {
        $displayFriends = (bool) $displayFriends;

        $config = CFactory::getConfig();
        $limit = $config->get('friendloadlimit', 8);

        $tmpl = new CTemplate();

        $tmpl->set('displayFriends', $displayFriends);
        $tmpl->set('displayEmail', $displayEmail);
        $tmpl->set('cid', $cid);
        $tmpl->set('callback', $callback);
        $tmpl->set('limit', $limit);
        $tmpl->set('type', $type);
        $html = $tmpl->fetch('ajax.showinvitation');

        $json = array(
            'html'        => $html,
            'limit'       => 200, // $limit,
            'title'       => JText::_( $type == 'group' ? 'COM_COMMUNITY_EVENT_INVITE_GROUP_MEMBERS' : 'COM_COMMUNITY_INVITE_FRIENDS' ),
            'btnInvite'   => JText::_('COM_COMMUNITY_SEND_INVITATIONS'),
            'btnLoadMore' => JText::_('COM_COMMUNITY_INVITE_LOAD_MORE')
        );

        die( json_encode($json) );
    }

    public function ajaxShowFriendsForm($friends, $callback, $cid, $displayFriends) {
        $displayFriends = (bool) $displayFriends;

        $config = CFactory::getConfig();
        $limit = $config->get('friendloadlimit', 8);

        $tmpl = new CTemplate();
        $tmpl->set('displayFriends', $displayFriends);
        $tmpl->set('cid', $cid);
        $tmpl->set('callback', $callback);
        $tmpl->set('limit', $limit);
        $html = $tmpl->fetch('ajax.showfriends');

        $json = array(
            'html'        => $html,
            'limit'       => $limit,
            'title'       => JText::_('COM_COMMUNITY_SELECT_FRIENDS_CAPTION'),
            'btnSelect'   => JText::_('COM_COMMUNITY_SELECT_FRIENDS'),
            'btnLoadMore' => JText::_('COM_COMMUNITY_INVITE_LOAD_MORE')
        );

        die( json_encode($json) );
    }

    public function ajaxLoadFriendsList($namePrefix, $callback, $cid, $limitstart = 0, $limit = 200) {
        // pending filter
        $objResponse = new JAXResponse();
        $filter = JFilterInput::getInstance();
        $callback = $filter->clean($callback, 'string');
        $cid = $filter->clean($cid, 'int');
        $namePrefix = $filter->clean($namePrefix, 'string');
        $my = CFactory::getUser();
        //get the handler
        $handlerName = '';

        $callbackOptions = explode(',', $callback);

        if (isset($callbackOptions[0])) {
            $handlerName = $callbackOptions[0];
        }

        $handler = CFactory::getModel($handlerName);

        $handlerFunc = 'getInviteListByName';
        $friends = '';
        $args = array();
        $friends = $handler->$handlerFunc($namePrefix, $my->id, $cid, $limitstart, $limit);

        $invitation = JTable::getInstance('Invitation', 'CTable');
        $invitation->load($callback, $cid);

        $tmpl = new CTemplate();
        $tmpl->set('friends', $friends);
        $tmpl->set('selected', $invitation->getInvitedUsers());
        $tmplName = 'ajax.friend.list.' . $handlerName;
        $html = $tmpl->fetch($tmplName);
        //calculate pending friend list
        $loadedFriend = $limitstart + count($friends);
        if ($handler->total > $loadedFriend) {
            //update limitstart
            $limitstart = $limitstart + count($friends);
            $moreCount = $handler->total - $loadedFriend;
            //load more option
            $loadMore = '<a onClick="joms.friends.loadMoreFriend(\'' . $callback . '\',\'' . $cid . '\',\'' . $limitstart . '\',\'' . $limit . '\');" href="javascript:void(0)">' . JText::_('COM_COMMUNITY_INVITE_LOAD_MORE') . '(' . $moreCount . ') </a>';
        } else {
            //nothing to load
            $loadMore = '';
        }

        $json = array(
            'html' => $html,
            'loadMore' => $loadMore ? true : false,
            'moreCount' => isset( $moreCount ) ? $moreCount : 0
        );

        die( json_encode($json) );
    }

    public function ajaxLoadGroupEventMembers($namePrefix, $cid, $limitstart = 0, $limit = 200)
    {
        // pending filter
        $objResponse = new JAXResponse();
        $filter = JFilterInput::getInstance();
        $callback = 'events,inviteUsers';
        $cid = $filter->clean($cid, 'int');
        $namePrefix = $filter->clean($namePrefix, 'string');
        $my = CFactory::getUser();
        //get the handler
        $handlerName = '';

        //load the event
        $event = JTable::getInstance('Event','CTable');
        $event->load($cid);

        //check permission here

        //get all the members of the group
        $groupid = $event->contentid;
        $groupsModel = CFactory::getModel('groups');
        $guestIds= $event->getMembers(COMMUNITY_EVENT_STATUS_ATTEND, 0, false, false, false); //get a list of attending users
        $userids = array();
        foreach ($guestIds as $uid) {
            $userids[] = $uid->id;
        }
        $members = $groupsModel->getMembers($groupid, 0, true, false, SHOW_GROUP_ADMIN, true);
        $memberList = array();
        foreach($members as $member){
            if($member->id == $my->id || in_array($member->id, $userids)){
                continue; //exclude myself and those who already attending
            }
            $memberList[] = $member->id;
        }

        //calculate pending group list
        $results = CUserHelper::filterUserByName($memberList, $namePrefix, $limitstart, $limit);
        $memberList = $results['users'];

        $invitation = JTable::getInstance('Invitation', 'CTable');
        $invitation->load($callback, $cid);

        $tmpl = new CTemplate();
        $tmpl   ->set('friends', $memberList)
                ->set('selected', $invitation->getInvitedUsers());
        $html = $tmpl->fetch('ajax.friend.list.events');

        $loadedFriend = $limitstart + count($memberList);
        if ($results['total'] > $loadedFriend) {
            //update limitstart
            $limitstart = $limitstart + count($memberList);
            $moreCount = $results['total'] - $loadedFriend;
            //load more option
            $loadMore = '<a onClick="joms.friends.loadMoreFriend(\'' . $callback . '\',\'' . $cid . '\',\'' . $limitstart . '\',\'' . $limit . '\');" href="javascript:void(0)">' . JText::_('COM_COMMUNITY_INVITE_LOAD_MORE') . '(' . $moreCount . ') </a>';
        } else {
            //nothing to load
            $loadMore = '';
        }

        $json = array(
            'html' => $html,
            'loadMore' => $loadMore ? true : false,
            'moreCount' => isset( $moreCount ) ? $moreCount : 0
        );

        die( json_encode($json) );

    }

    public function ajaxSubmitInvitation($callback, $cid, $values) {
        //CFactory::load( 'helpers' , 'validate' );
        $filter = JFilterInput::getInstance();
        $callback = $filter->clean($callback, 'string');
        $cid = $filter->clean($cid, 'int');
        $values = $filter->clean($values, 'array');
        $objResponse = new JAXResponse();
        $my = CFactory::getUser();
        $methods = explode(',', $callback);
        $emails = array();
        $recipients = array();
        $users = '';
        $message = $values['message'];
        $values['friends'] = isset($values['friends']) ? $values['friends'] : array();

        if (!is_array($values['friends'])) {
            $values['friends'] = array($values['friends']);
        }

        // This is where we process external email addresses
        if (!empty($values['emails'])) {
            $emails = explode(',', $values['emails']);
            foreach ($emails as $email) {
                if (!CValidateHelper::email($email)) {
                    $objResponse->addAssign('invitation-error', 'innerHTML', JText::sprintf('COM_COMMUNITY_INVITE_EMAIL_INVALID', $email));
                    return $objResponse->sendResponse();
                }
                $recipients[] = $email;
            }
        }

        // This is where we process site members that are being invited
        if (!empty($values['friends'][0])) {
            $users = explode(',', $values['friends'][0]);

            foreach($users as $id) {
                $recipients[] = $id;
            }
        }

        if (!empty($recipients)) {
            $arguments = array($cid, $recipients, $emails, $message);

            if (is_array($methods) && $methods[0] != 'plugins') {
                $controller = JString::strtolower(basename($methods[0]));
                $function = $methods[1];
                require_once( JPATH_ROOT . '/components/com_community/controllers/controller.php' );
                $file = JPATH_ROOT . '/components/com_community/controllers' . '/' . $controller . '.php';


                if (JFile::exists($file)) {
                    require_once( $file );

                    $controller = JString::ucfirst($controller);
                    $controller = 'Community' . $controller . 'Controller';
                    $controller = new $controller();

                    if (method_exists($controller, $function)) {
                        $inviteMail = call_user_func_array(array($controller, $function), $arguments);
                    } else {
                        $objResponse->addAssign('invitation-error', 'innerHTML', JText::_('COM_COMMUNITY_INVITE_EXTERNAL_METHOD_ERROR'));
                        return $objResponse->sendResponse();
                    }
                } else {
                    $objResponse->addAssign('invitation-error', 'innerHTML', JText::_('COM_COMMUNITY_INVITE_EXTERNAL_METHOD_ERROR'));
                    return $objResponse->sendResponse();
                }
            } else if (is_array($methods) && $methods[0] == 'plugins') {
                // Load 3rd party applications
                $element = JString::strtolower(basename($methods[1]));
                $function = $methods[2];
                $file = CPluginHelper::getPluginPath('community', $element) . '/' . $element . '.php';

                if (JFile::exists($file)) {
                    require_once( $file );
                    $className = 'plgCommunity' . JString::ucfirst($element);


                    if (method_exists($controller, $function)) {
                        $inviteMail = call_user_func_array(array($className, $function), $arguments);
                    } else {
                        $objResponse->addAssign('invitation-error', 'innerHTML', JText::_('COM_COMMUNITY_INVITE_EXTERNAL_METHOD_ERROR'));
                        return $objResponse->sendResponse();
                    }
                } else {
                    $objResponse->addAssign('invitation-error', 'innerHTML', JText::_('COM_COMMUNITY_INVITE_EXTERNAL_METHOD_ERROR'));
                    return $objResponse->sendResponse();
                }
            }

            //CFactory::load( 'libraries' , 'invitation' );
            // If the responsible method returns a false value, we should know that they want to stop the invitation process.

            if ($inviteMail instanceof CInvitationMail) {
                if ($inviteMail->hasError()) {
                    $objResponse->addAssign('invitation-error', 'innerHTML', $inviteMail->getError());

                    return $objResponse->sendResponse();
                } else {
                    // Once stored, we need to store selected user so they wont be invited again
                    $invitation = JTable::getInstance('Invitation', 'CTable');
                    $invitation->load($callback, $cid);

                    if (!empty($values['friends'])) {
                        if (!$invitation->id) {
                            // If the record doesn't exists, we need add them into the
                            $invitation->cid = $cid;
                            $invitation->callback = $callback;
                        }
                        $invitation->users = empty($invitation->users) ? implode(',', $values['friends']) : $invitation->users . ',' . implode(',', $values['friends']);
                        $invitation->store();
                    }

                    // Add notification
                    //CFactory::load( 'libraries' , 'notification' );
                    CNotificationLibrary::add($inviteMail->getCommand(), $my->id, $recipients, $inviteMail->getTitle(), $inviteMail->getContent(), '', $inviteMail->getParams());
                }
            } else {
                $objResponse->addScriptCall(JText::_('COM_COMMUNITY_INVITE_INVALID_RETURN_TYPE'));
                return $objResponse->sendResponse();
            }
        } else {
            $objResponse->addAssign('invitation-error', 'innerHTML', JText::_('COM_COMMUNITY_INVITE_NO_SELECTION'));
            return $objResponse->sendResponse();
        }

        $actions = '<input type="button" class="btn" onclick="cWindowHide();" value="' . JText::_('COM_COMMUNITY_BUTTON_CLOSE_BUTTON') . '"/>';
        $html = JText::_('COM_COMMUNITY_INVITE_SENT');

        $objResponse->addAssign('cwin_logo', 'innerHTML', JText::_('COM_COMMUNITY_INVITE_FRIENDS'));
        $objResponse->addScriptCall('cWindowAddContent', $html, $actions);

        return $objResponse->sendResponse();
    }

    public function ajaxReport() {
        $config = CFactory::getConfig();
        $reports = JString::trim($config->get('predefinedreports'));
        $reports = empty($reports) ? false : explode('\n', $reports);
        $tmpArray = array();

        $my = CFactory::getUser();
        if ( !$config->get('enablereporting') || ( ( $my->id == 0 ) && (!$config->get('enableguestreporting') ) ) ) {
            $json = array(
                'title' => JText::_('COM_COMMUNITY_REPORT_THIS'),
                'error' => JText::_('COM_COMMUNITY_ACCESS_FORBIDDEN')
            );

            die( json_encode($json) );
        }

        foreach ($reports as $_report) {
            $tmp = explode("\n", $_report);
            foreach ($tmp as $_tmp) {
                $tmpArray[] = $_tmp;
            }
        }
        $reports = $tmpArray;

        $html = '';

        $argsCount = func_num_args();

        $argsData = '';

        if ($argsCount > 1) {

            for ($i = 2; $i < $argsCount; $i++) {
                $argsData .= "\'" . func_get_arg($i) . "\'";
                $argsData .= ( $i != ( $argsCount - 1) ) ? ',' : '';
            }
        }

        $tmpl = new CTemplate();
        $tmpl->set('reports', $reports);

        $json = array(
            'html'      => $tmpl->fetch('ajax.reporting'),
            'title'     => JText::_('COM_COMMUNITY_REPORT_THIS'),
            'btnSend'   => JText::_('COM_COMMUNITY_SEND_BUTTON'),
            'btnCancel' => JText::_('COM_COMMUNITY_CANCEL_BUTTON')
        );

        die( json_encode($json) );
    }

    public function ajaxSendReport() {
        $reportFunc = func_get_arg(0);
        $pageLink = func_get_arg(1);
        $message = func_get_arg(2);

        $argsCount = func_num_args();
        $method = explode(',', $reportFunc);

        $args = array();
        $args[] = $pageLink;
        $args[] = $message;

        for ($i = 3; $i < $argsCount; $i++) {
            $args[] = func_get_arg($i);
        }

        // Reporting should be session sensitive
        // Construct $output
        if ($reportFunc == 'activities,reportActivities' && strpos($pageLink, 'actid') === false) {
            $pageLink = $pageLink . '&actid=' . func_get_arg(3);
        }

        $uniqueString = md5($reportFunc . $pageLink);
        $session = JFactory::getSession();


        if ($session->has('action-report-' . $uniqueString)) {
            $output = JText::_('COM_COMMUNITY_REPORT_ALREADY_SENT');
        } else {
            if (is_array($method) && $method[0] != 'plugins') {
                $controller = JString::strtolower(basename($method[0]));

                require_once( JPATH_ROOT . '/components/com_community/controllers/controller.php' );
                require_once( JPATH_ROOT . '/components/com_community/controllers' . '/' . $controller . '.php' );

                $controller = JString::ucfirst($controller);
                $controller = 'Community' . $controller . 'Controller';
                $controller = new $controller();


                $output = call_user_func_array(array(&$controller, $method[1]), $args);
            } else if (is_array($method) && $method[0] == 'plugins') {
                // Application method calls
                $element = JString::strtolower($method[1]);
                require_once( CPluginHelper::getPluginPath('community', $element) . '/' . $element . '.php' );
                $className = 'plgCommunity' . JString::ucfirst($element);
                $output = call_user_func_array(array($className, $method[2]), $args);
            }
        }
        $session->set('action-report-' . $uniqueString, true);

        $json = array( 'message' => $output );

        die( json_encode($json) );
    }

    public function ajaxEditWall($wallId, $editableFunc) {
        $filter = JFilterInput::getInstance();
        $wallId = $filter->clean($wallId, 'int');
        $editableFunc = $filter->clean($editableFunc, 'string');

        $objResponse = new JAXResponse();
        $wall = JTable::getInstance('Wall', 'CTable');
        $wall->load($wallId);

        //CFactory::load( 'libraries' , 'wall' );
        $isEditable = CWall::isEditable($editableFunc, $wall->id);

        if (!$isEditable) {
            $objResponse->addAlert(JText::_('COM_COMMUNITY_NOT_ALLOWED_TO_EDIT'));
            return $objResponse->sendResponse();
        }

        //CFactory::load( 'libraries' , 'comment' );
        $tmpl = new CTemplate();
        $message = CComment::stripCommentData($wall->comment);
        $tmpl->set('message', $message);
        $tmpl->set('editableFunc', $editableFunc);
        $tmpl->set('id', $wall->id);

        $content = $tmpl->fetch('wall/edit');

        $objResponse->addScriptCall('joms.jQuery("#wall-message-' . $wallId . '").hide();');
        $objResponse->addScriptCall('joms.jQuery("#wall-edit-container-' . $wallId . '").show();');
        $objResponse->addScriptCall('joms.jQuery("#wall-edit-container-' . $wallId . '").find("textarea").val("' . str_replace(array("\r\n", "\r", "\n"), '\n', $message) . '");');
        $objResponse->addScriptCall('joms.jQuery("#wall_' . $wallId . '").find("[data-action=edit]").trigger("start");');

        return $objResponse->sendResponse();
    }

    public function ajaxUpdateWall($wallId, $message, $editableFunc, $photoId = 0) {
        $filter = JFilterInput::getInstance();
        $wallId = $filter->clean($wallId, 'int');
        $editableFunc = $filter->clean($editableFunc, 'string');

        $wall = JTable::getInstance('Wall', 'CTable');
        $wall->load($wallId);
        $objResponse = new JAXresponse();
        $json = array();

        if (empty($message)) {
            $json['error'] = JText::_('COM_COMMUNITY_EMPTY_MESSAGE');
            die( json_encode($json) );
        }

        $isEditable = CWall::isEditable($editableFunc, $wall->id);

        if (!$isEditable) {
            $json['error'] = JText::_('COM_COMMUNITY_NOT_ALLOWED_TO_EDIT');
            die( json_encode($json) );
        }

        // We don't want to touch the comments data.
        $comments = CComment::getRawCommentsData($wall->comment);
        $wall->comment = $message;
        $wall->comment .= $comments;
        $my = CFactory::getUser();
        $data = CWallLibrary::saveWall($wall->contentid, $wall->comment, $wall->type, $my, false, $editableFunc, 'wall/content', $wall->id, $photoId);

        $wall->originalComment = $wall->comment;

        $CComment = new CComment();
        $wall->comment = $CComment->stripCommentData($wall->comment);
        $wall->comment = CStringHelper::autoLink($wall->comment);
        $wall->comment = nl2br($wall->comment);
        $wall->comment = CUserHelper::replaceAliasURL($wall->comment);
        $wall->comment = CStringHelper::getEmoticon($wall->comment);
        $wall->comment = CStringHelper::converttagtolink($wall->comment); // convert to hashtag

        $json['success'] = true;
        $json['comment'] = $wall->comment;
        $json['originalComment'] = $wall->originalComment;

        die( json_encode($json) );
    }

    public function ajaxRemoveWallPreview($wallId) {
        $filter = JFilterInput::getInstance();
        $wallId = $filter->clean($wallId, 'int');

        $wall = JTable::getInstance('Wall', 'CTable');
        $wall->load($wallId);

        //make sure this item id belongs to the current user
        $my = CFactory::getUser();
        if ($my->id == $wall->post_by || COwnerHelper::isCommunityAdmin()) {
            $wall->params = '';
            $wall->store();
        }

        $json = array( 'success' => true );
        die( json_encode( $json ) );
    }

    public function ajaxGetOlderWalls($groupId, $discussionId, $limitStart) {
        $filter = JFilterInput::getInstance();
        $groupId = $filter->clean($groupId, 'int');
        $discussionId = $filter->clean($discussionId, 'int');
        $limitStart = $filter->clean($limitStart, 'int');

        $limitStart = max(0, $limitStart);
        $response = new JAXResponse();

        $app = JFactory::getApplication();
        $my = CFactory::getUser();
        //$jconfig  = JFactory::getConfig();

        $groupModel = CFactory::getModel('groups');
        $isGroupAdmin = $groupModel->isAdmin($my->id, $groupId);

        $html = CWall::getWallContents('discussions', $discussionId, $isGroupAdmin, JFactory::getConfig()->get('list_limit'), $limitStart, 'wall/content', 'groups,discussion', $groupId);

        // parse the user avatar
        $html = CStringHelper::replaceThumbnails($html);
        $html = CString::str_ireplace(array('{error}', '{warning}', '{info}'), '', $html);


        $config = CFactory::getConfig();
        $order = $config->get('group_discuss_order');

        if ($order == 'ASC') {
            // Append new data at Top.
            $response->addScriptCall('joms.walls.prepend', $html);
        } else {
            // Append new data at bottom.
            $response->addScriptCall('joms.walls.append', $html);
        }

        return $response->sendResponse();
    }

    public function ajaxRemoveCommentPreview($itemId) {
        $filter = JFilterInput::getInstance();
        $itemId = $filter->clean($itemId, 'int');

        $wall = JTable::getInstance('Wall', 'CTable');
        $wall->load($itemId);

        //make sure this item id belongs to the current user
        $my = CFactory::getUser();
        if ($my->id == $wall->post_by || COwnerHelper::isCommunityAdmin()) {
            $wall->params = '';
            $wall->store();
        }

        $json = array( 'success' => true );
        die( json_encode( $json ) );
    }

    /**
     * Like an item. Update ajax count
     * @param string $element   Can either be core object (photos/videos) or a plugins (plugins,plugin_name)
     * @param mixed $itemId     Unique id to identify object item
     *
     */
    public function ajaxLike($element, $itemId) {

        $filter = JFilterInput::getInstance();
        $element = $filter->clean($element, 'string');
        $itemId = $filter->clean($itemId, 'int');


        if (!COwnerHelper::isRegisteredUser()) {
            return $this->ajaxBlockUnregister();
        }

        $like = new CLike();

        if (!$like->enabled($element)) {
            // @todo: return proper ajax error
            return;
        }

        $my = CFactory::getUser();
        $objResponse = new JAXResponse();


        $like->addLike($element, $itemId);
        $likeCount = $like->getLikeCount($element, $itemId);

        $html = $like->getHTML($element, $itemId, $my->id);

        $act = new stdClass();
        $act->cmd = $element . '.like';
        $act->actor = $my->id;
        $act->target = 0;
        $act->title = '';
        $act->content = '';
        $act->app = $element . '.like';
        $act->cid = $itemId;

        if($element == 'album'){
            $album = JTable::getInstance('Album', 'CTable');
            $album->load($itemId);
            if($album->type == 'event'){
                $act->eventid=$album->eventid;
            }
        }elseif($element == 'photo'){
            $photo = JTable::getInstance('Photo', 'CTable');
            $photo->load($itemId);
            $album = JTable::getInstance('Album', 'CTable');
            $album->load($photo->albumid);
            if($album->type == 'event'){
                $act->eventid=$album->eventid;
            }
        }elseif($element == 'videos'){
            $video = JTable::getInstance('Video', 'CTable');
            $video->load($itemId);
            $act->eventid = $video->eventid;
            $act->groupid = $video->groupid;
        }

        // load item-specific privacy settings, if available
        $elementTable = $element=='videos'?'video':$element;
        $table = JTable::getInstance($elementTable, 'CTable');

        if(is_object($table)) {

            $table->load($itemId);

            if (isset($table->permissions)) {
                $act->access = $table->permissions;
            }
        }

        $params = new CParameter('');

        switch ($element) {

            case 'groups':
                $act->groupid = $itemId;
                //@since 4.1 when a group is liked, dump the data into photo stats
                $statsModel = CFactory::getModel('stats');
                $statsModel->addGroupStats($itemId, 'like');
                break;
            case 'events':
                $act->eventid = $itemId;
                $eventTable = JTable::getInstance('Event', 'CTable');
                $eventTable->load($act->eventid);
                $act->event_access = $eventTable->permission;

                //@since 4.1 when an event is liked, dump the data into event stats
                $statsModel = CFactory::getModel('stats');
                $statsModel->addEventStats($itemId, 'like');
                break;
        }

        $params->set('action', $element . '.like');

        // Add logging
        CActivityStream::addActor($act, $params->toString());

        $json = array(
            'success' => true,
            'likeCount' => $likeCount
        );

        die( json_encode($json) );
    }

    /**
     * Dislike an item
     * @param string $element   Can either be core object (photos/videos) or a plugins (plugins,plugin_name)
     * @param mixed $itemId     Unique id to identify object item
     *
     */
    public function ajaxDislike($element, $itemId) {
        $filter = JFilterInput::getInstance();
        $itemId = $filter->clean($itemId, 'int');
        $element = $filter->clean($element, 'string');

        if (!COwnerHelper::isRegisteredUser()) {
            return $this->ajaxBlockUnregister();
        }

        $dislike = new CLike();

        if (!$dislike->enabled($element)) {
            // @todo: return proper ajax error
            return;
        }

        $my = CFactory::getUser();
        $objResponse = new JAXResponse();


        $dislike->addDislike($element, $itemId);
        $html = $dislike->getHTML($element, $itemId, $my->id);

        $objResponse->addScriptCall('__callback', $html);

        return $objResponse->sendResponse();
    }

    /**
     * Unlike an item
     * @param string $element   Can either be core object (photos/videos) or a plugins (plugins,plugin_name)
     * @param mixed $itemId     Unique id to identify object item
     *
     */
    public function ajaxUnlike($element, $itemId) {
        $filter = JFilterInput::getInstance();
        $itemId = $filter->clean($itemId, 'int');
        $element = $filter->clean($element, 'string');

        if (!COwnerHelper::isRegisteredUser()) {
            return $this->ajaxBlockUnregister();
        }

        $my = CFactory::getUser();
        $objResponse = new JAXResponse();

        // Load libraries
        $unlike = new CLike();

        if (!$unlike->enabled($element)) {

        } else {
            $unlike->unlike($element, $itemId);
            $likeCount = $unlike->getLikeCount($element, $itemId);

            $html = $unlike->getHTML($element, $itemId, $my->id);

            $objResponse->addScriptCall('__callback', $html);
        }

        $act = new stdClass();
        $act->cmd = $element . '.like';
        $act->actor = $my->id;
        $act->target = 0;
        $act->title = '';
        $act->content = '';
        $act->app = $element . '.like';
        $act->cid = $itemId;

        $params = new CParameter('');

        switch ($element) {

            case 'groups':
                $act->groupid = $itemId;
                break;
            case 'events':
                $act->eventid = $itemId;
                break;
        }

        $params->set('action', $element . '.like');

        // Remove logging
        CActivityStream::removeActor($act, $params->toString());

        $json = array(
            'success' => true,
            'likeCount' => $likeCount
        );

        die( json_encode($json) );
    }

    /**
     * Called by status box to add new stream data
     *
     * @param type $message
     * @param type $attachment
     * @return type
     */
    public function ajaxStreamAdd($message, $attachment, $streamFilter = FALSE) {
        $streamHTML = '';
        // $attachment pending filter

        $cache = CFactory::getFastCache();
        $cache->clean(array('activities'));

        $my = CFactory::getUser();
        $userparams = $my->getParams();

        if (!COwnerHelper::isRegisteredUser()) {
            return $this->ajaxBlockUnregister();
        }

        //@rule: In case someone bypasses the status in the html, we enforce the character limit.
        $config = CFactory::getConfig();
        if (JString::strlen($message) > $config->get('statusmaxchar')) {
            $message = JHTML::_('string.truncate', $message, $config->get('statusmaxchar'));
        }

        $message = JString::trim($message);
        $objResponse = new JAXResponse();
        $rawMessage = $message;

        // @rule: Autolink hyperlinks
        // @rule: Autolink to users profile when message contains @username
        // $message     = CUserHelper::replaceAliasURL($message); // the processing is done on display side
        $emailMessage = CUserHelper::replaceAliasURL($rawMessage, true);

        // @rule: Spam checks
        if ($config->get('antispam_akismet_status')) {
            $filter = CSpamFilter::getFilter();
            $filter->setAuthor($my->getDisplayName());
            $filter->setMessage($message);
            $filter->setEmail($my->email);
            $filter->setURL(CRoute::_('index.php?option=com_community&view=profile&userid=' . $my->id));
            $filter->setType('message');
            $filter->setIP($_SERVER['REMOTE_ADDR']);

            if ($filter->isSpam()) {
                $objResponse->addAlert(JText::_('COM_COMMUNITY_STATUS_MARKED_SPAM'));
                return $objResponse->sendResponse();
            }
        }

        $attachment = json_decode($attachment, true);

        switch ($attachment['type']) {
            case 'message':
                //if (!empty($message)) {
                switch ($attachment['element']) {

                    case 'profile':
                        //only update user status if share messgage is on his profile
                        if (COwnerHelper::isMine($my->id, $attachment['target'])) {

                            //save the message
                            $status = $this->getModel('status');
                            /* If no privacy in attachment than we apply default: Public */
                            if (!isset($attachment['privacy']))
                                $attachment['privacy'] = COMMUNITY_STATUS_PRIVACY_PUBLIC;
                            $status->update($my->id, $rawMessage, $attachment['privacy']);

                            //set user status for current session.
                            $today = JDate::getInstance();
                            $message2 = (empty($message)) ? ' ' : $message;
                            $my->set('_status', $rawMessage);
                            $my->set('_posted_on', $today->toSql());

                            // Order of replacement
                            $order = array("\r\n", "\n", "\r");
                            $replace = '<br />';

                            // Processes \r\n's first so they aren't converted twice.
                            $messageDisplay = str_replace($order, $replace, $message);
                            $messageDisplay = CKses::kses($messageDisplay, CKses::allowed());

                            //update user status
                            $objResponse->addScriptCall("joms.jQuery('#profile-status span#profile-status-message').html('" . addslashes($messageDisplay) . "');");
                        }

                        //if actor posted something to target, the privacy should be under target's profile privacy settings
                        if (!COwnerHelper::isMine($my->id, $attachment['target']) && $attachment['target'] != '') {
                            $attachment['privacy'] = CFactory::getUser($attachment['target'])->getParams()->get('privacyProfileView');
                        }

                        //push to activity stream
                        $act = new stdClass();
                        $act->cmd = 'profile.status.update';
                        $act->actor = $my->id;
                        $act->target = $attachment['target'];
                        $act->title = $message;
                        $act->content = '';
                        $act->app = $attachment['element'];
                        $act->cid = $my->id;
                        $act->access = $attachment['privacy'];
                        $act->comment_id = CActivities::COMMENT_SELF;
                        $act->comment_type = 'profile.status';
                        $act->like_id = CActivities::LIKE_SELF;
                        $act->like_type = 'profile.status';

                        $activityParams = new CParameter('');

                        /* Save cords if exists */
                        if (isset($attachment['location'])) {
                            /* Save geo name */
                            $act->location = $attachment['location'][0];
                            $act->latitude = $attachment['location'][1];
                            $act->longitude = $attachment['location'][2];
                        };

                        $headMeta = new CParameter('');

                        if (isset($attachment['fetch'])) {
                            $headMeta->set('title', $attachment['fetch'][2]);
                            $headMeta->set('description', $attachment['fetch'][3]);
                            $headMeta->set('image', $attachment['fetch'][1]);
                            $headMeta->set('link', $attachment['fetch'][0]);

                            //do checking if this is a video link
                            $video = JTable::getInstance('Video', 'CTable');
                            $isValidVideo = @$video->init($attachment['fetch'][0]);
                            if ($isValidVideo) {
                                $headMeta->set('type', 'video');
                                $headMeta->set('video_provider', $video->type);
                                $headMeta->set('video_id', $video->getVideoId());
                                $headMeta->set('height', $video->getHeight());
                                $headMeta->set('width', $video->getWidth());
                            }

                            $activityParams->set('headMetas', $headMeta->toString());
                        }
                        //Store mood in paramm
                        if (isset($attachment['mood']) && $attachment['mood'] != 'Mood') {
                            $activityParams->set('mood', $attachment['mood']);
                        }
                        $act->params = $activityParams->toString();

                        //CActivityStream::add($act);
                        //check if the user points is enabled
                        if(CUserPoints::assignPoint('profile.status.update')){
                            /* Let use our new CApiStream */
                            $activityData = CApiActivities::add($act);
                            CTags::add($activityData);

                            $recipient = CFactory::getUser($attachment['target']);
                            $params = new CParameter('');
                            $params->set('actorName', $my->getDisplayName());
                            $params->set('recipientName', $recipient->getDisplayName());
                            $params->set('url', CUrlHelper::userLink($act->target, false));
                            $params->set('message', $message);
                            $params->set('stream', JText::_('COM_COMMUNITY_SINGULAR_STREAM'));
                            $params->set('stream_url',CRoute::_('index.php?option=com_community&view=profile&userid='.$activityData->actor.'&actid='.$activityData->id));

                            CNotificationLibrary::add('profile_status_update', $my->id, $attachment['target'], JText::sprintf('COM_COMMUNITY_FRIEND_WALL_POST', $my->getDisplayName()), '', 'wall.post', $params);

                            //email and add notification if user are tagged
                            CUserHelper::parseTaggedUserNotification($message, $my, $activityData, array('type' => 'post-comment'));
                        }

                        break;
                    // Message posted from Group page
                    case 'groups':
                        //
                        $groupLib = new CGroups();
                        $group = JTable::getInstance('Group', 'CTable');
                        $group->load($attachment['target']);

                        // Permission check, only site admin and those who has
                        // mark their attendance can post message
                        if (!COwnerHelper::isCommunityAdmin() && !$group->isMember($my->id) && $config->get('lockgroupwalls')) {
                            $objResponse->addScriptCall("alert('permission denied');");
                            return $objResponse->sendResponse();
                        }

                        $act = new stdClass();
                        $act->cmd = 'groups.wall';
                        $act->actor = $my->id;
                        $act->target = 0;

                        $act->title = $message;
                        $act->content = '';
                        $act->app = 'groups.wall';
                        $act->cid = $attachment['target'];
                        $act->groupid = $group->id;
                        $act->group_access = $group->approvals;
                        $act->eventid = 0;
                        $act->access = 0;
                        $act->comment_id = CActivities::COMMENT_SELF;
                        $act->comment_type = 'groups.wall';
                        $act->like_id = CActivities::LIKE_SELF;
                        $act->like_type = 'groups.wall';

                        $activityParams = new CParameter('');

                        /* Save cords if exists */
                        if (isset($attachment['location'])) {
                            /* Save geo name */
                            $act->location = $attachment['location'][0];
                            $act->latitude = $attachment['location'][1];
                            $act->longitude = $attachment['location'][2];
                        };

                        $headMeta = new CParameter('');

                        if (isset($attachment['fetch'])) {
                            $headMeta->set('title', $attachment['fetch'][2]);
                            $headMeta->set('description', $attachment['fetch'][3]);
                            $headMeta->set('image', $attachment['fetch'][1]);
                            $headMeta->set('link', $attachment['fetch'][0]);

                            //do checking if this is a video link
                            $video = JTable::getInstance('Video', 'CTable');
                            $isValidVideo = @$video->init($attachment['fetch'][0]);
                            if ($isValidVideo) {
                                $headMeta->set('type', 'video');
                                $headMeta->set('video_provider', $video->type);
                                $headMeta->set('video_id', $video->getVideoId());
                                $headMeta->set('height', $video->getHeight());
                                $headMeta->set('width', $video->getWidth());
                            }

                            $activityParams->set('headMetas', $headMeta->toString());
                        }

                        //Store mood in paramm
                        if (isset($attachment['mood']) && $attachment['mood'] != 'Mood') {
                            $activityParams->set('mood', $attachment['mood']);
                        }

                        $act->params = $activityParams->toString();

                        $activityData = CApiActivities::add($act);

                        CTags::add($activityData);
                        CUserPoints::assignPoint('group.wall.create');

                        $recipient = CFactory::getUser($attachment['target']);
                        $params = new CParameter('');
                        $params->set('message', $emailMessage);
                        $params->set('group', $group->name);
                        $params->set('group_url', 'index.php?option=com_community&view=groups&task=viewgroup&groupid=' . $group->id);
                        $params->set('url', CRoute::getExternalURL('index.php?option=com_community&view=groups&task=viewgroup&groupid=' . $group->id, false));

                        //Get group member emails
                        $model = CFactory::getModel('Groups');
                        $members = $model->getMembers($attachment['target'], null, true, false, true);

                        $membersArray = array();
                        if (!is_null($members)) {
                            foreach ($members as $row) {
                                if ($my->id != $row->id) {
                                    $membersArray[] = $row->id;
                                }
                            }
                        }
                        $groupParams = new CParameter($group->params);

                        if($groupParams->get('wallnotification')) {
                            CNotificationLibrary::add('groups_wall_create', $my->id, $membersArray, JText::sprintf('COM_COMMUNITY_NEW_WALL_POST_NOTIFICATION_EMAIL_SUBJECT', $my->getDisplayName(), $group->name), '', 'groups.post', $params);
                        }

                        //@since 4.1 when a there is a new post in group, dump the data into group stats
                        $statsModel = CFactory::getModel('stats');
                        $statsModel->addGroupStats($group->id, 'post');

                        // Add custom stream
                        // Reload the stream with new stream data
                        $streamHTML = $groupLib->getStreamHTML($group, array('showLatestActivityOnTop'=>true));

                        break;

                    // Message posted from Event page
                    case 'events' :

                        $eventLib = new CEvents();
                        $event = JTable::getInstance('Event', 'CTable');
                        $event->load($attachment['target']);

                        // Permission check, only site admin and those who has
                        // mark their attendance can post message
                        if ((!COwnerHelper::isCommunityAdmin() && !$event->isMember($my->id) && $config->get('lockeventwalls'))) {
                            $objResponse->addScriptCall("alert('permission denied');");
                            return $objResponse->sendResponse();
                        }

                        // If this is a group event, set the group object
                        $groupid = ($event->type == 'group') ? $event->contentid : 0;
                        //
                        $groupLib = new CGroups();
                        $group = JTable::getInstance('Group', 'CTable');
                        $group->load($groupid);

                        $act = new stdClass();
                        $act->cmd = 'events.wall';
                        $act->actor = $my->id;
                        $act->target = 0;
                        $act->title = $message;
                        $act->content = '';
                        $act->app = 'events.wall';
                        $act->cid = $attachment['target'];
                        $act->groupid = ($event->type == 'group') ? $event->contentid : 0;
                        $act->group_access = $group->approvals;
                        $act->eventid = $event->id;
                        $act->event_access = $event->permission;
                        $act->access = 0;
                        $act->comment_id = CActivities::COMMENT_SELF;
                        $act->comment_type = 'events.wall';
                        $act->like_id = CActivities::LIKE_SELF;
                        $act->like_type = 'events.wall';

                        $activityParams = new CParameter('');

                        /* Save cords if exists */
                        if (isset($attachment['location'])) {
                            /* Save geo name */
                            $act->location = $attachment['location'][0];
                            $act->latitude = $attachment['location'][1];
                            $act->longitude = $attachment['location'][2];
                        };

                        $headMeta = new CParameter('');

                        if (isset($attachment['fetch'])) {
                            $headMeta->set('title', $attachment['fetch'][2]);
                            $headMeta->set('description', $attachment['fetch'][3]);
                            $headMeta->set('image', $attachment['fetch'][1]);
                            $headMeta->set('link', $attachment['fetch'][0]);

                            //do checking if this is a video link
                            $video = JTable::getInstance('Video', 'CTable');
                            $isValidVideo = @$video->init($attachment['fetch'][0]);
                            if ($isValidVideo) {
                                $headMeta->set('type', 'video');
                                $headMeta->set('video_provider', $video->type);
                                $headMeta->set('video_id', $video->getVideoId());
                                $headMeta->set('height', $video->getHeight());
                                $headMeta->set('width', $video->getWidth());
                            }

                            $activityParams->set('headMetas', $headMeta->toString());
                        }

                        //Store mood in paramm
                        if (isset($attachment['mood']) && $attachment['mood'] != 'Mood') {
                            $activityParams->set('mood', $attachment['mood']);
                        }

                        $act->params = $activityParams->toString();

                        $activityData = CApiActivities::add($act);
                        CTags::add($activityData);

                        // add points
                        CUserPoints::assignPoint('event.wall.create');

                        $params = new CParameter('');
                        $params->set('message', $emailMessage);
                        $params->set('event', $event->title);
                        $params->set('event_url', 'index.php?option=com_community&view=events&task=viewevent&eventid=' . $event->id);
                        $params->set('url', CRoute::getExternalURL('index.php?option=com_community&view=events&task=viewevent&eventid=' . $event->id, false));

                        //Get event member emails
                        $members = $event->getMembers(COMMUNITY_EVENT_STATUS_ATTEND, 12, CC_RANDOMIZE);

                        $membersArray = array();
                        if (!is_null($members)) {
                            foreach ($members as $row) {
                                if ($my->id != $row->id) {
                                    $membersArray[] = $row->id;
                                }
                            }
                        }

                        CNotificationLibrary::add('events_wall_create', $my->id, $membersArray, JText::sprintf('COM_COMMUNITY_NEW_WALL_POST_NOTIFICATION_EMAIL_SUBJECT_EVENTS', $my->getDisplayName(), $event->title), '', 'events.post', $params);

                        //@since 4.1 when a there is a new post in event, dump the data into event stats
                        $statsModel = CFactory::getModel('stats');
                        $statsModel->addEventStats($event->id, 'post');

                        // Reload the stream with new stream data
                        $streamHTML = $eventLib->getStreamHTML($event, array('showLatestActivityOnTop'=>true));
                        break;
                }

                $objResponse->addScriptCall('__callback', '');
                // /}

                break;

            case 'photo':

                if (!isset($attachment['id'][0]) || $attachment['id'][0] <= 0) {
                    //$objResponse->addScriptCall('__callback', JText::sprintf('COM_COMMUNITY_PHOTO_UPLOADED_SUCCESSFULLY', $photo->caption));
                    exit;
                }

                $photo = JTable::getInstance('Photo', 'CTable');
                $photo->load($attachment['id'][0]);
                $photoParams = new CParameter($photo->params);

                //before anything else, lets check if this photo is a gif type, if it is, we will automatically assign the album id to it
                if($photoParams->get('animated_gif') != ''){
                    $attachment['album_id'] = $photo->albumid;
                }

                switch ($attachment['element']) {

                    case 'profile':
                        $photoIds = $attachment['id'];
                        //use User Preference for Privacy
                        //$privacy = $userparams->get('privacyPhotoView'); //$privacy = $attachment['privacy'];

                        $photo = JTable::getInstance('Photo', 'CTable');

                        //always get album id from the photo itself, do not let it assign by params from user post data
                        $photoModel = CFactory::getModel('photos');
                        $photo = $photoModel->getPhoto($photoIds[0]);
                        /* OK ! If album_id is not provided than we use album id from photo ( it should be default album id ) */
                        $albumid = (isset($attachment['album_id'])) ? $attachment['album_id'] : $photo->albumid;

                        $album = JTable::getInstance('Album', 'CTable');
                        $album->load($albumid);

                        $privacy = $album->permissions;

                        //limit checking
//                        $photoModel = CFactory::getModel( 'photos' );
//                        $config       = CFactory::getConfig();
//                        $total        = $photoModel->getTotalToday( $my->id );
//                        $max      = $config->getInt( 'limit_photo_perday' );
//                        $remainingUploadCount = $max - $total;
                        $params = array();
                        foreach ($photoIds as $key => $photoId) {
                            if (CLimitsLibrary::exceedDaily('photos')) {
                                unset($photoIds[$key]);
                                continue;
                            }
                            $photo->load($photoId);
                            $photo->permissions = $privacy;
                            $photo->published = 1;
                            $photo->status = 'ready';
                            $photo->albumid = $albumid; /* We must update this photo into correct album id */
                            $photo->store();
                            $params[] = clone($photo);
                        }

                        if ($config->get('autoalbumcover') && !$album->photoid) {
                            $album->photoid = $photoIds[0];
                            $album->store();
                        }

                        // Break if no photo added, which is likely because of daily limit.
                        if ( count($photoIds) < 1 ) {
                            $objResponse->addScriptCall( '__throwError', JText::_('COM_COMMUNITY_PHOTO_UPLOAD_LIMIT_EXCEEDED') );
                            return $objResponse->sendResponse();
                        }

                        // Trigger onPhotoCreate
                        //
                        $apps = CAppPlugins::getInstance();
                        $apps->loadApplications();
                        $apps->triggerEvent('onPhotoCreate', array($params));

                        $act = new stdClass();
                        $act->cmd = 'photo.upload';
                        $act->actor = $my->id;
                        $act->access = $privacy; //$attachment['privacy'];
                        $act->target = ($attachment['target'] == $my->id) ? 0 : $attachment['target'];
                        $act->title = $message;
                        $act->content = ''; // Generated automatically by stream. No need to add anything
                        $act->app = 'photos';
                        $act->cid = $albumid;
                        $act->location = $album->location;

                        /* Comment and like for individual photo upload is linked
                         * to the photos itsel
                         */
                        $act->comment_id = $photo->id;
                        $act->comment_type = 'photos';
                        $act->like_id = $photo->id;
                        $act->like_type = 'photo';

                        $albumUrl = 'index.php?option=com_community&view=photos&task=album&albumid=' . $album->id . '&userid=' . $my->id;
                        $albumUrl = CRoute::_($albumUrl);

                        $photoUrl = 'index.php?option=com_community&view=photos&task=photo&albumid=' . $album->id . '&userid=' . $photo->creator . '&photoid=' . $photo->id;
                        $photoUrl = CRoute::_($photoUrl);

                        $params = new CParameter('');
                        $params->set('multiUrl', $albumUrl);
                        $params->set('photoid', $photo->id);
                        $params->set('action', 'upload');
                        $params->set('stream', '1');
                        $params->set('photo_url', $photoUrl);
                        $params->set('style', COMMUNITY_STREAM_STYLE);
                        $params->set('photosId', implode(',', $photoIds));
                        $params->set('albumType',$album->type);

                        if (count($photoIds > 1)) {
                            $params->set('count', count($photoIds));
                            $params->set('batchcount', count($photoIds));
                        }

                        //Store mood in param
                        if (isset($attachment['mood']) && $attachment['mood'] != 'Mood') {
                            $params->set('mood', $attachment['mood']);
                        }

                        // Add activity logging
                        // CActivityStream::remove($act->app, $act->cid);
                        $activityData = CActivityStream::add($act, $params->toString());

                        // Add user points
                        CUserPoints::assignPoint('photo.upload');

                        //add a notification to the target user if someone posted photos on target's profile
                        if($my->id != $attachment['target']){
                            $recipient = CFactory::getUser($attachment['target']);
                            $params = new CParameter('');
                            $params->set('actorName', $my->getDisplayName());
                            $params->set('recipientName', $recipient->getDisplayName());
                            $params->set('url', CUrlHelper::userLink($act->target, false));
                            $params->set('message', $message);
                            $params->set('stream', JText::_('COM_COMMUNITY_SINGULAR_STREAM'));
                            $params->set('stream_url',CRoute::_('index.php?option=com_community&view=profile&userid='.$activityData->actor.'&actid='.$activityData->id));

                            CNotificationLibrary::add('profile_status_update', $my->id, $attachment['target'], JText::sprintf('COM_COMMUNITY_NOTIFICATION_STREAM_PHOTO_POST', count($photoIds)), '', 'wall.post', $params);
                        }

                        //email and add notification if user are tagged
                        CUserHelper::parseTaggedUserNotification($message, $my, $activityData, array('type' => 'post-comment'));

                        $objResponse->addScriptCall('__callback', JText::sprintf('COM_COMMUNITY_PHOTO_UPLOADED_SUCCESSFULLY', $photo->caption));
                        break;
                    case 'events':
                        $event = JTable::getInstance('Event', 'CTable');
                        $event->load($attachment['target']);

                        $groupPrivacy = 0;
                        $eventPrivacy = 0;
                        $privacy = 0;
                        $groupId = 0;
                        //if this is a group event, we need to follow the group privacy
                        if($event->type == 'group' && $event->contentid){
                            $group = JTable::getInstance('Group', 'CTable');
                            $group->load($event->contentid);
                            $groupPrivacy = $privacy = $group->approvals ? PRIVACY_GROUP_PRIVATE_ITEM : 0;
                            $groupId = $group->id;
                        }else{
                            $eventPrivacy = $privacy = $event->permission;
                        }

                        $photoIds = $attachment['id'];
                        $photo = JTable::getInstance('Photo', 'CTable');
                        $photo->load($photoIds[0]);

                        $albumid = (isset($attachment['album_id'])) ? $attachment['album_id'] : $photo->albumid;
                        $album = JTable::getInstance('Album', 'CTable');
                        $album->load($albumid);

                        if ($config->get('autoalbumcover') && !$album->photoid) {
                            $album->photoid = $photoIds[0];
                            $album->store();
                        }

                        $params = array();
                        foreach ($photoIds as $photoId) {
                            $photo->load($photoId);

                            $photo->caption = $message;
                            $photo->permissions = $privacy;
                            $photo->published = 1;
                            $photo->status = 'ready';
                            $photo->albumid = $albumid;
                            $photo->store();
                            $params[] = clone($photo);
                        }

                        // Trigger onPhotoCreate
                        //
                        $apps = CAppPlugins::getInstance();
                        $apps->loadApplications();
                        $apps->triggerEvent('onPhotoCreate', array($params));

                        $act = new stdClass();
                        $act->cmd = 'photo.upload';
                        $act->actor = $my->id;
                        $act->access = 0; //always 0 because this is determined by event_access
                        $act->target = ($attachment['target'] == $my->id) ? 0 : $attachment['target'];
                        $act->title = $message; //JText::sprintf('COM_COMMUNITY_ACTIVITIES_UPLOAD_PHOTO' , '{photo_url}', $album->name );
                        $act->content = ''; // Generated automatically by stream. No need to add anything
                        $act->app = 'photos';
                        $act->cid = $album->id;
                        $act->location = $album->location;
                        $act->groupid = $groupId;

                        $act->eventid = $event->id;
                        $act->group_access = $groupPrivacy; // just in case this event belongs to a group
                        $act->event_access = $eventPrivacy;
                        //$act->access      = $attachment['privacy'];

                        /* Comment and like for individual photo upload is linked
                         * to the photos itsel
                         */
                        $act->comment_id = $photo->id;
                        $act->comment_type = 'photos';
                        $act->like_id = $photo->id;
                        $act->like_type = 'photo';

                        $albumUrl = 'index.php?option=com_community&view=photos&task=album&albumid=' . $album->id . '&userid=' . $my->id;
                        $albumUrl = CRoute::_($albumUrl);

                        $photoUrl = 'index.php?option=com_community&view=photos&task=photo&albumid=' . $album->id . '&userid=' . $photo->creator . '&photoid=' . $photo->id;
                        $photoUrl = CRoute::_($photoUrl);

                        $params = new CParameter('');
                        $params->set('multiUrl', $albumUrl);
                        $params->set('photoid', $photo->id);
                        $params->set('action', 'upload');
                        $params->set('stream', '1'); // this photo uploaded from status stream
                        $params->set('photo_url', $photoUrl);
                        $params->set('style', COMMUNITY_STREAM_STYLE); // set stream style
                        $params->set('photosId', implode(',', $photoIds));
                        $params->set('albumType',$album->type);

                        // Add activity logging
                        if (count($photoIds > 1)) {
                            $params->set('count', count($photoIds));
                            $params->set('batchcount', count($photoIds));
                        }
                        //Store mood in paramm
                        if (isset($attachment['mood']) && $attachment['mood'] != 'Mood') {
                            $params->set('mood', $attachment['mood']);
                        }
                        // CActivityStream::remove($act->app, $act->cid);
                        $activityData = CActivityStream::add($act, $params->toString());

                        // Add user points
                        CUserPoints::assignPoint('photo.upload');

                        // Reload the stream with new stream data
                        $eventLib = new CEvents();
                        $event = JTable::getInstance('Event', 'CTable');
                        $event->load($attachment['target']);
                        $streamHTML = $eventLib->getStreamHTML($event, array('showLatestActivityOnTop'=>true));

                        $objResponse->addScriptCall('__callback', JText::sprintf('COM_COMMUNITY_PHOTO_UPLOADED_SUCCESSFULLY', $photo->caption));

                        break;
                    case 'groups':
                        //
                        $groupLib = new CGroups();
                        $group = JTable::getInstance('Group', 'CTable');
                        $group->load($attachment['target']);

                        $photoIds = $attachment['id'];
                        $privacy = $group->approvals ? PRIVACY_GROUP_PRIVATE_ITEM : 0;

                        $photo = JTable::getInstance('Photo', 'CTable');
                        $photo->load($photoIds[0]);

                        $albumid = (isset($attachment['album_id'])) ? $attachment['album_id'] : $photo->albumid;

                        $album = JTable::getInstance('Album', 'CTable');
                        $album->load($albumid);

                        if ($config->get('autoalbumcover') && !$album->photoid) {
                            $album->photoid = $photoIds[0];
                            $album->store();
                        }

                        $params = array();
                        foreach ($photoIds as $photoId) {
                            $photo->load($photoId);

                            $photo->caption = $message;
                            $photo->permissions = $privacy;
                            $photo->published = 1;
                            $photo->status = 'ready';
                            $photo->albumid = $albumid;
                            $photo->store();
                            $params[] = clone($photo);
                        }
                        // Trigger onPhotoCreate
                        //
                        $apps = CAppPlugins::getInstance();
                        $apps->loadApplications();
                        $apps->triggerEvent('onPhotoCreate', array($params));

                        $act = new stdClass();
                        $act->cmd = 'photo.upload';
                        $act->actor = $my->id;
                        $act->access = $privacy;
                        $act->target = ($attachment['target'] == $my->id) ? 0 : $attachment['target'];
                        $act->title = $message; //JText::sprintf('COM_COMMUNITY_ACTIVITIES_UPLOAD_PHOTO' , '{photo_url}', $album->name );
                        $act->content = ''; // Generated automatically by stream. No need to add anything
                        $act->app = 'photos';
                        $act->cid = $album->id;
                        $act->location = $album->location;

                        $act->groupid = $group->id;
                        $act->group_access = $group->approvals;
                        $act->eventid = 0;
                        //$act->access      = $attachment['privacy'];

                        /* Comment and like for individual photo upload is linked
                         * to the photos itsel
                         */
                        $act->comment_id = $photo->id;
                        $act->comment_type = 'photos';
                        $act->like_id = $photo->id;
                        $act->like_type = 'photo';

                        $albumUrl = 'index.php?option=com_community&view=photos&task=album&albumid=' . $album->id . '&userid=' . $my->id;
                        $albumUrl = CRoute::_($albumUrl);

                        $photoUrl = 'index.php?option=com_community&view=photos&task=photo&albumid=' . $album->id . '&userid=' . $photo->creator . '&photoid=' . $photo->id;
                        $photoUrl = CRoute::_($photoUrl);

                        $params = new CParameter('');
                        $params->set('multiUrl', $albumUrl);
                        $params->set('photoid', $photo->id);
                        $params->set('action', 'upload');
                        $params->set('stream', '1'); // this photo uploaded from status stream
                        $params->set('photo_url', $photoUrl);
                        $params->set('style', COMMUNITY_STREAM_STYLE); // set stream style
                        $params->set('photosId', implode(',', $photoIds));
                        $params->set('albumType',$album->type);
                        // Add activity logging
                        if (count($photoIds > 1)) {
                            $params->set('count', count($photoIds));
                            $params->set('batchcount', count($photoIds));
                        }
                        //Store mood in paramm
                        if (isset($attachment['mood']) && $attachment['mood'] != 'Mood') {
                            $params->set('mood', $attachment['mood']);
                        }
                        // CActivityStream::remove($act->app, $act->cid);
                        $activityData = CActivityStream::add($act, $params->toString());

                        //add notifcation to all the members
                        $params = new CParameter('');
                        $params->set('message', $emailMessage);
                        $params->set('group', $group->name);
                        $params->set('group_url', 'index.php?option=com_community&view=groups&task=viewgroup&groupid=' . $group->id);
                        $params->set('url', CRoute::getExternalURL('index.php?option=com_community&view=groups&task=viewgroup&groupid=' . $group->id, false));
                        //Get group member emails
                        $model = CFactory::getModel('Groups');
                        $members = $model->getMembers($attachment['target'], null, true, false, true);

                        $membersArray = array();
                        if (!is_null($members)) {
                            foreach ($members as $row) {
                                if ($my->id != $row->id) {
                                    $membersArray[] = $row->id;
                                }
                            }
                        }
                        $groupParams = new CParameter($group->params);

                        if($groupParams->get('wallnotification')) {
                            CNotificationLibrary::add('groups_wall_create', $my->id, $membersArray, JText::sprintf('COM_COMMUNITY_NEW_WALL_POST_NOTIFICATION_EMAIL_SUBJECT', $my->getDisplayName(), $group->name), '', 'groups.post', $params);
                        }

                        // Add user points
                        CUserPoints::assignPoint('photo.upload');

                        // Reload the stream with new stream data
                        $streamHTML = $groupLib->getStreamHTML($group, array('showLatestActivityOnTop'=>true));

                        $objResponse->addScriptCall('__callback', JText::sprintf('COM_COMMUNITY_PHOTO_UPLOADED_SUCCESSFULLY', $photo->caption));

                        break;
                        dafault:
                        return;
                }

                break;

            case 'video':
                switch ($attachment['element']) {
                    case 'profile':
                        // attachment id
                        $fetch = $attachment['fetch'];
                        $cid = $fetch[0];
                        $privacy = isset($attachment['privacy']) ? $attachment['privacy'] : COMMUNITY_STATUS_PRIVACY_PUBLIC;

                        $video = JTable::getInstance('Video', 'CTable');
                        $video->load($cid);
                        $video->set('creator_type', VIDEO_USER_TYPE);
                        $video->set('status', 'ready');
                        $video->set('permissions', $privacy);
                        $video->set('title', $fetch[3]);
                        $video->set('description', $fetch[4]);
                        $video->set('category_id', $fetch[5]);
                        /* Save cords if exists */
                        if (isset($attachment['location'])) {
                            $video->set('location', $attachment['location'][0]);
                            $video->set('latitude', $attachment['location'][1]);
                            $video->set('longitude', $attachment['location'][2]);
                        };

                        // Add activity logging
                        $url = $video->getViewUri(false);

                        $act = new stdClass();
                        $act->cmd = 'videos.linking';
                        $act->actor = $my->id;
                        $act->target = ($attachment['target'] == $my->id) ? 0 : $attachment['target'];
                        $act->access = $privacy;

                        //filter empty message
                        $act->title = $message;
                        $act->app = 'videos.linking';
                        $act->content = '';
                        $act->cid = $video->id;
                        $act->location = $video->location;

                        /* Save cords if exists */
                        if (isset($attachment['location'])) {
                            /* Save geo name */
                            $act->location = $attachment['location'][0];
                            $act->latitude = $attachment['location'][1];
                            $act->longitude = $attachment['location'][2];
                        };

                        $act->comment_id = $video->id;
                        $act->comment_type = 'videos.linking';

                        $act->like_id = $video->id;
                        $act->like_type = 'videos.linking';

                        $params = new CParameter('');
                        $params->set('video_url', $url);
                        $params->set('style', COMMUNITY_STREAM_STYLE); // set stream style
                        //Store mood in paramm
                        if (isset($attachment['mood']) && $attachment['mood'] != 'Mood') {
                            $params->set('mood', $attachment['mood']);
                        }

                        //
                        $activityData = CActivityStream::add($act, $params->toString());

                        //this video must be public because it's posted on someone else's profile
                        if($my->id != $attachment['target']){
                            $video->set('permissions', COMMUNITY_STATUS_PRIVACY_PUBLIC);
                            $params = new CParameter();
                            $params->set('activity_id', $activityData->id); // activity id is used to remove the activity if someone deleted this video
                            $params->set('target_id', $attachment['target']);
                            $video->params = $params->toString();

                            //also send a notification to the user
                            $recipient = CFactory::getUser($attachment['target']);
                            $params = new CParameter('');
                            $params->set('actorName', $my->getDisplayName());
                            $params->set('recipientName', $recipient->getDisplayName());
                            $params->set('url', CUrlHelper::userLink($act->target, false));
                            $params->set('message', $message);
                            $params->set('stream', JText::_('COM_COMMUNITY_SINGULAR_STREAM'));
                            $params->set('stream_url',CRoute::_('index.php?option=com_community&view=profile&userid='.$activityData->actor.'&actid='.$activityData->id));

                            CNotificationLibrary::add('profile_status_update', $my->id, $attachment['target'], JText::_('COM_COMMUNITY_NOTIFICATION_STREAM_VIDEO_POST'), '', 'wall.post', $params);
                        }

                        $video->store();

                        // @rule: Add point when user adds a new video link
                        //
                        CUserPoints::assignPoint('video.add', $video->creator);

                        //email and add notification if user are tagged
                        CUserHelper::parseTaggedUserNotification($message, $my, $activityData, array('type' => 'post-comment'));

                        // Trigger for onVideoCreate
                        //
                        $apps = CAppPlugins::getInstance();
                        $apps->loadApplications();
                        $params = array();
                        $params[] = $video;
                        $apps->triggerEvent('onVideoCreate', $params);

                        $this->cacheClean(array(COMMUNITY_CACHE_TAG_VIDEOS, COMMUNITY_CACHE_TAG_FRONTPAGE, COMMUNITY_CACHE_TAG_FEATURED, COMMUNITY_CACHE_TAG_VIDEOS_CAT, COMMUNITY_CACHE_TAG_ACTIVITIES));

                        $objResponse->addScriptCall('__callback', JText::sprintf('COM_COMMUNITY_VIDEOS_UPLOAD_SUCCESS', $video->title));

                        break;

                    case 'groups':
                        // attachment id
                        $fetch = $attachment['fetch'];
                        $cid = $fetch[0];
                        $privacy = 0; //$attachment['privacy'];

                        $video = JTable::getInstance('Video', 'CTable');
                        $video->load($cid);
                        $video->set('status', 'ready');
                        $video->set('groupid', $attachment['target']);
                        $video->set('permissions', $privacy);
                        $video->set('creator_type', VIDEO_GROUP_TYPE);
                        $video->set('title', $fetch[3]);
                        $video->set('description', $fetch[4]);
                        $video->set('category_id', $fetch[5]);

                        /* Save cords if exists */
                        if (isset($attachment['location'])) {
                            $video->set('location', $attachment['location'][0]);
                            $video->set('latitude', $attachment['location'][1]);
                            $video->set('longitude', $attachment['location'][2]);
                        };

                        $video->store();

                        //
                        $groupLib = new CGroups();
                        $group = JTable::getInstance('Group', 'CTable');
                        $group->load($attachment['target']);

                        // Add activity logging
                        $url = $video->getViewUri(false);

                        $act = new stdClass();
                        $act->cmd = 'videos.linking';
                        $act->actor = $my->id;
                        $act->target = ($attachment['target'] == $my->id) ? 0 : $attachment['target'];
                        $act->access = $privacy;

                        //filter empty message
                        $act->title = $message;
                        $act->app = 'videos';
                        $act->content = '';
                        $act->cid = $video->id;
                        $act->groupid = $video->groupid;
                        $act->group_access = $group->approvals;
                        $act->location = $video->location;

                        /* Save cords if exists */
                        if (isset($attachment['location'])) {
                            /* Save geo name */
                            $act->location = $attachment['location'][0];
                            $act->latitude = $attachment['location'][1];
                            $act->longitude = $attachment['location'][2];
                        };

                        $act->comment_id = $video->id;
                        $act->comment_type = 'videos';

                        $act->like_id = $video->id;
                        $act->like_type = 'videos';

                        $params = new CParameter('');
                        $params->set('video_url', $url);
                        $params->set('style', COMMUNITY_STREAM_STYLE); // set stream style
                        //Store mood in paramm
                        if (isset($attachment['mood']) && $attachment['mood'] != 'Mood') {
                            $params->set('mood', $attachment['mood']);
                        }

                        $activityData = CActivityStream::add($act, $params->toString());

                        // @rule: Add point when user adds a new video link
                        CUserPoints::assignPoint('video.add', $video->creator);

                        //add notifcation to all the members
                        $params = new CParameter('');
                        $params->set('message', $emailMessage);
                        $params->set('group', $group->name);
                        $params->set('group_url', 'index.php?option=com_community&view=groups&task=viewgroup&groupid=' . $group->id);
                        $params->set('url', CRoute::getExternalURL('index.php?option=com_community&view=groups&task=viewgroup&groupid=' . $group->id, false));
                        //Get group member emails
                        $model = CFactory::getModel('Groups');
                        $members = $model->getMembers($attachment['target'], null, true, false, true);

                        $membersArray = array();
                        if (!is_null($members)) {
                            foreach ($members as $row) {
                                if ($my->id != $row->id) {
                                    $membersArray[] = $row->id;
                                }
                            }
                        }
                        $groupParams = new CParameter($group->params);

                        if($groupParams->get('wallnotification')) {
                            CNotificationLibrary::add('groups_wall_create', $my->id, $membersArray, JText::sprintf('COM_COMMUNITY_NEW_WALL_POST_NOTIFICATION_EMAIL_SUBJECT', $my->getDisplayName(), $group->name), '', 'groups.post', $params);
                        }

                        // Trigger for onVideoCreate
                        $apps = CAppPlugins::getInstance();
                        $apps->loadApplications();
                        $params = array();
                        $params[] = $video;
                        $apps->triggerEvent('onVideoCreate', $params);

                        $this->cacheClean(array(COMMUNITY_CACHE_TAG_VIDEOS, COMMUNITY_CACHE_TAG_FRONTPAGE, COMMUNITY_CACHE_TAG_FEATURED, COMMUNITY_CACHE_TAG_VIDEOS_CAT, COMMUNITY_CACHE_TAG_ACTIVITIES));

                        $objResponse->addScriptCall('__callback', JText::sprintf('COM_COMMUNITY_VIDEOS_UPLOAD_SUCCESS', $video->title));

                        // Reload the stream with new stream data
                        $streamHTML = $groupLib->getStreamHTML($group, array('showLatestActivityOnTop'=>true));

                        break;
                    case 'events':
                        //event videos
                        $fetch = $attachment['fetch'];
                        $cid = $fetch[0];

                        $privacy = 0;
                        $groupId = 0;
                        $groupPrivacy = 0;
                        $eventPrivacy = 0;

                        $eventLib = new CEvents();
                        $event = JTable::getInstance('Event', 'CTable');
                        $event->load($attachment['target']);
                        //if this is a group event, we need to follow the group privacy
                        if($event->type == 'group' && $event->contentid){
                            $group = JTable::getInstance('Group', 'CTable');
                            $group->load($event->contentid);
                            $groupPrivacy = $privacy = $group->approvals ? PRIVACY_GROUP_PRIVATE_ITEM : 0;
                        }else{
                            $eventPrivacy = $privacy = $event->permission;
                        }

                        $video = JTable::getInstance('Video', 'CTable');
                        $video->load($cid);
                        $video->set('status', 'ready');
                        $video->set('eventid', $attachment['target']);
                        $video->set('permissions', $privacy);
                        $video->set('creator_type', VIDEO_EVENT_TYPE);
                        $video->set('title', $fetch[3]);
                        $video->set('description', $fetch[4]);
                        $video->set('category_id', $fetch[5]);

                        /* Save cords if exists */
                        if (isset($attachment['location'])) {
                            $video->set('location', $attachment['location'][0]);
                            $video->set('latitude', $attachment['location'][1]);
                            $video->set('longitude', $attachment['location'][2]);
                        };

                        $video->store();

                        // Add activity logging
                        $url = $video->getViewUri(false);

                        $act = new stdClass();
                        $act->cmd = 'videos.linking';
                        $act->actor = $my->id;
                        $act->target = ($attachment['target'] == $my->id) ? 0 : $attachment['target'];
                        $act->access = 0; //always 0 because this is determined by event_access

                        //filter empty message
                        $act->title = $message;
                        $act->app = 'videos';
                        $act->content = '';
                        $act->cid = $video->id;
                        $act->groupid = 0;
                        $act->group_access = $groupPrivacy; // if this is a group event
                        $act->event_access = $eventPrivacy;
                        $act->location = $video->location;

                        /* Save cords if exists */
                        if (isset($attachment['location'])) {
                            /* Save geo name */
                            $act->location = $attachment['location'][0];
                            $act->latitude = $attachment['location'][1];
                            $act->longitude = $attachment['location'][2];
                        };

                        $act->eventid = $event->id;

                        $act->comment_id = $video->id;
                        $act->comment_type = 'videos';

                        $act->like_id = $video->id;
                        $act->like_type = 'videos';

                        $params = new CParameter('');
                        $params->set('video_url', $url);
                        $params->set('style', COMMUNITY_STREAM_STYLE); // set stream style
                        //Store mood in paramm
                        if (isset($attachment['mood']) && $attachment['mood'] != 'Mood') {
                            $params->set('mood', $attachment['mood']);
                        }

                        $activityData = CActivityStream::add($act, $params->toString());

                        // @rule: Add point when user adds a new video link
                        CUserPoints::assignPoint('video.add', $video->creator);

                        // Trigger for onVideoCreate
                        $apps = CAppPlugins::getInstance();
                        $apps->loadApplications();
                        $params = array();
                        $params[] = $video;
                        $apps->triggerEvent('onVideoCreate', $params);

                        $this->cacheClean(array(COMMUNITY_CACHE_TAG_VIDEOS, COMMUNITY_CACHE_TAG_FRONTPAGE, COMMUNITY_CACHE_TAG_FEATURED, COMMUNITY_CACHE_TAG_VIDEOS_CAT, COMMUNITY_CACHE_TAG_ACTIVITIES));

                        $objResponse->addScriptCall('__callback', JText::sprintf('COM_COMMUNITY_VIDEOS_UPLOAD_SUCCESS', $video->title));

                        // Reload the stream with new stream data
                        $streamHTML = $eventLib->getStreamHTML($event, array('showLatestActivityOnTop'=>true));
                        break;
                    default:
                        return;
                }

                break;

            case 'event':
                switch ($attachment['element']) {

                    case 'profile':
                        require_once(COMMUNITY_COM_PATH . '/controllers/events.php');

                        $eventController = new CommunityEventsController();

                        // Assign default values where necessary
                        $attachment['description'] = $message;
                        $attachment['ticket'] = 0;
                        $attachment['offset'] = 0;

                        $event = $eventController->ajaxCreate($attachment, $objResponse);

                        $objResponse->addScriptCall('window.location="' . $event->getLink() . '";');

                        if (CFactory::getConfig()->get('event_moderation')) {
                            $objResponse->addAlert(JText::sprintf('COM_COMMUNITY_EVENTS_MODERATION_NOTICE', $event->title));
                        }

                        break;

                    case 'groups':
                        require_once(COMMUNITY_COM_PATH . '/controllers/events.php');

                        $eventController = new CommunityEventsController();

                        //
                        $groupLib = new CGroups();
                        $group = JTable::getInstance('Group', 'CTable');
                        $group->load($attachment['target']);

                        // Assign default values where necessary
                        $attachment['description'] = $message;
                        $attachment['ticket'] = 0;
                        $attachment['offset'] = 0;

                        $event = $eventController->ajaxCreate($attachment, $objResponse);

                        CEvents::addGroupNotification($event);

                        $objResponse->addScriptCall('window.location="' . $event->getLink() . '";');

                        // Reload the stream with new stream data
                        $streamHTML = $groupLib->getStreamHTML($group, array('showLatestActivityOnTop'=>true));

                        if (CFactory::getConfig()->get('event_moderation')) {
                            $objResponse->addAlert(JText::sprintf('COM_COMMUNITY_EVENTS_MODERATION_NOTICE', $event->title));
                        }

                        break;
                }

                break;

            case 'link':
                break;
        }

        //no matter what kind of message it is, always filter the hashtag if there's any
        if(!empty($act->title)&& isset($activityData->id) && $activityData->id){
            //use model to check if this has a tag in it and insert into the table if possible
            $hashtags = CContentHelper::getHashTags($act->title);
            if(count($hashtags)){
                //$hashTag
                $hashtagModel = CFactory::getModel('hashtags');

                foreach($hashtags as $tag){
                    $hashtagModel->addActivityHashtag($tag, $activityData->id);
                }
            }
        }

        // Frontpage filter
        if ($streamFilter != false) {
            $streamFilter = json_decode($streamFilter);
            $filter = $streamFilter->filter;
            $value = $streamFilter->value;
            $extra = false;

            // Append added data to the list.
            if (isset($activityData) && $activityData->id) {
                $model = CFactory::getModel('Activities');
                $extra = $model->getActivity($activityData->id);
            }

            switch ($filter) {
                case 'privacy':
                    if ($value == 'me-and-friends' && $my->id != 0) {
                        $streamHTML = CActivities::getActivitiesByFilter('active-user-and-friends', $my->id, 'frontpage', true, array(), $extra);
                    } else {
                        $streamHTML = CActivities::getActivitiesByFilter('all', $my->id, 'frontpage', true, array(), $extra);
                    }
                    break;

                case 'apps':
                    $streamHTML = CActivities::getActivitiesByFilter('all', $my->id, 'frontpage', true, array('apps' => array($value)), $extra);
                    break;

                case 'hashtag';
                    $streamHTML = CActivities::getActivitiesByFilter('all', $my->id, 'frontpage', true, array($filter => $value), $extra);
                    break;

                default:
                    $defaultFilter = $config->get('frontpageactivitydefault');
                    if ($defaultFilter == 'friends' && $my->id != 0) {
                        $streamHTML = CActivities::getActivitiesByFilter('active-user-and-friends', $my->id, 'frontpage', true, array(), $extra);
                    } else {
                        $streamHTML = CActivities::getActivitiesByFilter('all', $my->id, 'frontpage', true, array(), $extra);
                    }
                    break;
            }
        }

        if (!isset($attachment['filter'])) {
            $attachment['filter'] = '';
            $filter = $config->get('frontpageactivitydefault');
            $filter = explode(':', $filter);

            $attachment['filter'] = (isset($filter[1])) ? $filter[1] : $filter[0];
        }

        if (empty($streamHTML)) {
            if (!isset($attachment['target']))
                $attachment['target'] = '';
            if (!isset($attachment['element']))
                $attachment['element'] = '';
            $streamHTML = CActivities::getActivitiesByFilter($attachment['filter'], $attachment['target'], $attachment['element'], true, array('show_featured'=>true,'showLatestActivityOnTop'=>true));
        }

        $objResponse->addAssign('activity-stream-container', 'innerHTML', $streamHTML);

        // Log user engagement
        CEngagement::log($attachment['type'] . '.share', $my->id);

        return $objResponse->sendResponse();
    }

    /**
     * Add comment to the stream
     *
     * @param int   $actid acitivity id
     * @param string $comment
     * @return obj
     */
    public function ajaxStreamAddComment($actid, $comment, $photoId = 0) {
        $filter = JFilterInput::getInstance();
        $actid = $filter->clean($actid, 'int');
        $my = CFactory::getUser();

        $wallModel = CFactory::getModel('wall');
        $rawComment = $comment;

        $json = array();

        $photoId = $filter->clean($photoId, 'int');

        // Pull the activity record and find out the actor
        // only allow comment if the actor is a friend of current user
        $act = JTable::getInstance('Activity', 'CTable');
        $act->load($actid);

        //who can add comment
        $obj = $act;

        if ($act->groupid > 0) {
            $obj = JTable::getInstance('Group', 'CTable');
            $obj->load($act->groupid);
        } else if ($act->eventid > 0) {
            $obj = JTable::getInstance('Event', 'CTable');
            $obj->load($act->eventid);
        }

        //link the actual comment from video page itself to the stream
        if(isset($obj->comment_type) && $obj->comment_type == 'videos.linking'){
            $obj->comment_type = 'videos';
        }

        $params = new CParameter($act->params);

        $batchcount = $params->get('batchcount', 0);
        $wallParam = new CParameter('');
        if ($act->app == 'photos' && $batchcount > 1) {
            $photo = JTable::getInstance('Photo', 'CTable');
            $photo->load($params->get('photoid'));

            $act->comment_type = 'albums';
            $act->comment_id = $photo->albumid;

            $wallParam->set('activityId', $act->id);
        }

        //if photo id is not 0, this wall is appended with a picture
        if($photoId > 0){
            //lets check if the photo belongs to the uploader
            $photo = JTable::getInstance('Photo', 'CTable');
            $photo->load($photoId);

            if($photo->creator == $my->id && $photo->albumid == '-1'){
                $wallParam->set('attached_photo_id', $photoId);

                //sets the status to ready so that it wont be deleted on cron run
                $photo->status = 'ready';
                $photo->store();
            }
        }

        // Allow comment for system post
        $allowComment = false;
        if ($act->app == 'system') {
            $allowComment = !empty($my->id);
        }

        $commentType = $act->comment_type;
        if($act->comment_type == 'videos.linking'){
            //we convert videos.linking type to videos because we want to merge both of the comment together
            $commentType = 'videos';
        }

        if ($my->authorise('community.add', 'activities.comment.' . $act->actor, $obj) || $allowComment) {

            $table = JTable::getInstance('Wall', 'CTable');
            $table->type = $commentType;
            $table->contentid = $act->comment_id;
            $table->post_by = $my->id;
            $table->comment = $comment;
            $table->params = $wallParam->toString();

            //fetch url if there is any
            if (( preg_match("/\b(?:(?:https?|ftp):\/\/|www\.)[-a-z0-9+&@#\/%?=~_|!:,.;]*[-a-z0-9+&@#\/%=~_|]/i", $comment))) {

                $graphObject = CParsers::linkFetch($comment);
                if ($graphObject){
                    $graphObject->merge($wallParam);
                    $table->params = $graphObject->toString();
                }
            }

            $table->store();

            $cache = CFactory::getFastCache();
            $cache->clean(array('activities'));

            if ($act->app == 'photos') {
                $table->contentid = $act->id;
            }
            $table->params = new CParameter($table->get('params'));
            $args[] = $table;
            CWall::triggerWallComments($args, false);
            $comment = CWall::formatComment($table);

            $json['html'] = $comment;

            //notification for activity comment
            //case 1: user's activity
            //case 2 : group's activity
            //case 3 : event's activity
            if ($act->groupid == 0 && $act->eventid == 0) {
                // //CFactory::load( 'libraries' , 'notification' );
                $params = new CParameter('');
                $params->set('message', $table->comment);
                $url = 'index.php?option=com_community&view=profile&userid=' . $act->actor . '&actid=' . $actid;
                $params->set('url', $url);
                $params->set('actor', $my->getDisplayName());
                $params->set('actor_url', CRoute::_('index.php?option=com_community&view=profile&userid=' . $my->id));
                $params->set('stream', JText::_('COM_COMMUNITY_SINGULAR_STREAM'));
                $params->set('stream_url', $url);

                if ($my->id != $act->actor) {
                    /* Notifications to all poster in this activity except myself */
                    $users = $wallModel->getAllPostUsers($act->comment_type, $act->id, $my->id);
                    if (!empty($users)) {
                        if(!in_array($act->actor, $users)) {
                            array_push($users,$act->actor);
                        }
                        $commenters = array_diff($users, array($act->actor));
                        // this will sent notification to the participant only
                        CNotificationLibrary::add('profile_activity_add_comment', $my->id, $commenters, JText::sprintf('COM_COMMUNITY_ACTIVITY_WALL_PARTICIPANT_EMAIL_SUBJECT'), '', 'profile.activityreply', $params);

                        // this will sent a notification to the poster, reason is that the title should be different
                        CNotificationLibrary::add('profile_activity_add_comment', $my->id, $act->actor, JText::sprintf('COM_COMMUNITY_ACITIVY_WALL_EMAIL_SUBJECT'), '', 'profile.activityreply', $params);
                    } else {
                        CNotificationLibrary::add('profile_activity_add_comment', $my->id, $act->actor, JText::sprintf('COM_COMMUNITY_ACITIVY_WALL_EMAIL_SUBJECT'), '', 'profile.activitycomment', $params);
                    }
                } else {
                    //for activity reply action
                    //get relevent users in the activity
                    $users = $wallModel->getAllPostUsers($act->comment_type, $act->id, $act->actor);
                    if (!empty($users)) {
                        CNotificationLibrary::add('profile_activity_reply_comment', $my->id, $users, JText::sprintf('COM_COMMUNITY_ACITIVY_WALL_USER_REPLY_EMAIL_SUBJECT'), '', 'profile.activityreply', $params);
                    }
                }
            } elseif ($act->groupid != 0 && $act->eventid == 0) { /* Group activity */

                $params = new CParameter('');
                $params->set('message', $table->comment);
                $url = 'index.php?option=com_community&view=groups&task=viewgroup&groupid=' . $act->groupid . '&actid=' . $actid;
                $params->set('url', $url);
                $params->set('actor', $my->getDisplayName());
                $params->set('actor_url', CRoute::_('index.php?option=com_community&view=profile&userid=' . $my->id));
                $params->set('stream', JText::_('COM_COMMUNITY_SINGULAR_STREAM'));
                $params->set('stream_url', $url);

                if ($my->id != $act->actor) {
                    /* Notifications to all poster in this activity except myself */
                    $users = $wallModel->getAllPostUsers($act->comment_type, $act->id, $my->id);
                    if (!empty($users)) {
                        if(!in_array($act->actor, $users)) {
                            array_push($users,$act->actor);
                        }
                        $commenters = array_diff($users, array($act->actor));
                        // this will sent notification to the participant only
                        CNotificationLibrary::add('groups_activity_add_comment', $my->id, $commenters, JText::sprintf('COM_COMMUNITY_ACTIVITY_GROUP_WALL_PARTICIPANT_EMAIL_SUBJECT'), '', 'profile.activityreply', $params);

                        // this will sent a notification to the poster, reason is that the title should be different
                        CNotificationLibrary::add('groups_activity_add_comment', $my->id, $act->actor, JText::sprintf('COM_COMMUNITY_ACITIVY_WALL_USER_REPLY_EMAIL_SUBJECT'), '', 'profile.activityreply', $params);
                    } else {
                        CNotificationLibrary::add('groups_activity_add_comment', $my->id, $act->actor, JText::sprintf('COM_COMMUNITY_ACITIVY_WALL_USER_REPLY_EMAIL_SUBJECT'), '', 'profile.activitycomment', $params);
                    }
                } else {
                    //for activity reply action
                    //get relevent users in the activity
                    $users = $wallModel->getAllPostUsers($act->comment_type, $act->id, $act->actor);
                    if (!empty($users)) {
                        CNotificationLibrary::add('groups_activity_add_comment', $my->id, $users, JText::sprintf('COM_COMMUNITY_ACITIVY_WALL_USER_REPLY_EMAIL_SUBJECT'), $table->comment, 'group.activityreply', $params);
                    }
                }
            } elseif ($act->eventid != 0) {
                $event = JTable::getInstance('Event','CTable');
                $event->load($act->eventid);
                $params = new CParameter('');
                $params->set('message', $table->comment);
                $url = 'index.php?option=com_community&view=events&task=viewevent&eventid=' . $act->eventid . '&actid=' . $actid;
                $params->set('url', $url);
                $params->set('actor', $my->getDisplayName());
                $params->set('actor_url', CRoute::_('index.php?option=com_community&view=profile&userid=' . $my->id));
                $params->set('stream', JText::_('COM_COMMUNITY_SINGULAR_STREAM'));
                $params->set('stream_url', $url);
                $params->set('event', $event->title);

                if ($my->id != $act->actor) {
                    CNotificationLibrary::add('events_submit_wall_comment', $my->id, $act->actor, JText::sprintf('COM_COMMUNITY_ACITIVY_WALL_EVENT_EMAIL_SUBJECT'), '', 'events.wallcomment', $params);
                } else {
                    //for activity reply action
                    //get relevent users in the activity
                    $users = $wallModel->getAllPostUsers($act->comment_type, $act->id, $act->actor);
                    if (!empty($users)) {
                        CNotificationLibrary::add('events_activity_reply_comment', $my->id, $users, JText::sprintf('COM_COMMUNITY_ACITIVY_WALL_USER_REPLY_EMAIL_SUBJECT'), '', 'event.activityreply', $params);
                    }
                }
            }

            //notifications
            CUserHelper::parseTaggedUserNotification($rawComment, $my, $act, array('type' => 'post-comment'));

            //Add tag
            CTags::add($table);
            // Log user engagement
            CEngagement::log($act->app . '.comment', $my->id);
        } else {
            $json['error'] = 'Permission denied.';
        }

        if ( !isset($json['error']) ) {
            $json['success'] = true;
        }

        die( json_encode($json) );
    }

    /**
     * Remove a wall comment
     *
     * @param int $actid
     * @param int $wallid
     */
    public function ajaxStreamRemoveComment($wallid) {
        $filter = JFilterInput::getInstance();
        $wallid = $filter->clean($wallid, 'int');

        $my = CFactory::getUser();

        //
        //@todo: check permission. Find the activity id that
        // has this wall's data. Make sure actor is friend with
        // current user

        $table = JTable::getInstance('Wall', 'CTable');
        $table->load($wallid);

        if(!$my->authorise('community.delete','walls', $table)){
            return false;
        }

        $actTable = JTable::getInstance('Activity','CTable');
        $actTable->load($table->contentid);

        if(COwnerHelper::isCommunityAdmin() || $table->post_by == $my->id || $actTable->actor == $my->id || $actTable->target == $my->id){

            //check if there is any image appended, if yes, remove the picture.
            $wallParam = new CParameter($table->params);
            if($wallParam->get('attached_photo_id') > 0 ){
                $photoModel = CFactory::getModel('photos');
                $photoTable = $photoModel->getPhoto($wallParam->get('attached_photo_id'));
                $photoTable->delete();
            }

            $table->delete();

            $json = array();
            $json['success'] = true;
            $json['parent_id'] = $wallParam->get('activityId');

            die( json_encode($json) );
        } else {
            $json = array();
            $json['success'] = false;
            $json['parent_id'] = null;

            die( json_encode($json) );
        }
    }

    /**
     * @param $actid
     * @param bool|false $type
     * @param int $totalShown the total entries that we have currently
     * @param int $limit the limit per load, if set to zero, will load the entire stream
     */
    public function ajaxStreamShowComments($actid, $type = false, $totalShown = 0 ,$limit = 0) {
        if ( $type ) {
            $this->ajaxWallShowComments( $actid, $type, $totalShown ,$limit );
            return;
        }

        $limit = $totalShown + $limit;

        $filter = JFilterInput::getInstance();
        $actid = $filter->clean($actid, 'int');

        $wallModel = CFactory::getModel('wall');

        // Pull the activity record and find out the actor
        // only allow comment if the actor is a friend of current user
        $act = JTable::getInstance('Activity', 'CTable');
        $act->load($actid);
        $params = new CParameter($act->params);

        //if this is an album type, this is an unique wall because it's aggregated, so we must determine the walls based on the params
        $commentsParam = '';
        if($act->comment_type == 'photos' && $params->get('batchcount', 0) > 1){
            $commentsParam = json_encode(array('activityId'=>$act->id));
        }

        if ($act->comment_type == 'photos' && $params->get('batchcount', 0) > 1) {
            $act->comment_type = 'albums';
            $act->comment_id = $act->cid;
        } else if ($act->comment_type == 'videos.linking') {
            $act->comment_type = 'videos';
            $act->comment_id = $act->cid;
        }

        $model = CFactory::getModel('wall');
        $totalResults = $model->getPostCount($act->comment_type, $act->comment_id, $commentsParam);

        $limitStart = ($totalResults-$limit <= 0) ? 0 :  $totalResults-$limit;

        $comments = $wallModel->getAllPost($act->comment_type, $act->comment_id, $limit , $limitStart, $commentsParam);
        $commentsHTML = '';

        CWall::triggerWallComments($comments, false);

        $count = 0;
        foreach ($comments as $row) {

            if($limit && $limit == $count){
                break;
            }

            $row->params = new CParameter($row->get('params', '{}'));
            if ($row->type == 'albums' && $row->params->get('activityId', NULL) != $actid && $params->get('batchcount', 0) > 1) {
                continue;
            }
            $commentsHTML .= CWall::formatComment($row);

            $count += 1;
        }

        $json = array();
        $json['success'] = true;
        $json['html'] = $commentsHTML;

        $json['total'] = $totalResults;

        die( json_encode($json) );
    }

    public function ajaxWallShowComments($uniqueId, $type = false, $totalShown = 0, $limit = 0) {
        $my = CFactory::getUser();
        $html = '';
        $model = CFactory::getModel('wall');

        $limit = $totalShown + $limit;

        if ( $type == 'albums' ) {
            $album = JTable::getInstance('Album', 'CTable');
            $album->load($uniqueId);
            $html = CWallLibrary::getWallContents(
                $type,
                $album->id,
                ( COwnerHelper::isCommunityAdmin() || COwnerHelper::isMine($my->id, $album->creator)),
                $limit,
                0
            );
        } else if ( $type == 'discussions' ) {
            $discussion = JTable::getInstance('Discussion', 'CTable');
            $discussion->load($uniqueId);
            $html = CWallLibrary::getWallContents(
                $type,
                $discussion->id,
                ( $my->id == $discussion->creator ),
                $limit,
                0,
                'wall/content',
                'groups,discussion'
            );
        } else if ( $type == 'videos' ) {
            $video = JTable::getInstance('Video', 'CTable');
            $video->load($uniqueId);
            $html = CWallLibrary::getWallContents(
                $type,
                $video->id,
                ( COwnerHelper::isCommunityAdmin() || ($my->id == $video->creator && ($my->id != 0))),
                $limit,
                0,
                'wall/content',
                'videos,video'
            );
        } else if ( $type == 'photos' ) {
            $photo = JTable::getInstance('Photo', 'CTable');
            $photo->load($uniqueId);
            $html = CWallLibrary::getWallContents(
                $type,
                $photo->id,
                ( COwnerHelper::isCommunityAdmin() || $my->id == $photo->creator ),
                $limit,
                0,
                'wall/content',
                'photos,photo'
            );
        }

        $json = array();
        $json['success'] = true;
        $json['html'] = $html;
        $json['total'] = $model->getPostCount($type, $uniqueId);

        die( json_encode($json) );
    }

    /**
     *
     */
    public function ajaxStreamAddLike($actid, $type = null) {
        $filter = JFilterInput::getInstance();
        $actid = $filter->clean($actid, 'int');
        $objResponse = new JAXResponse();
        $wallModel = CFactory::getModel('wall');
        $like = new CLike();

        $act = JTable::getInstance('Activity', 'CTable');
        $act->load($actid);

        //guest cannot like
        $user = CFactory::getUser();
        if(!$user->id){
            die;
        }

        /**
         * some like_type are missing and causing stream id cannot be like, in this case, group create.
         * This condition is used to fix the existing activity with such issue.
         */
        if($act->comment_type == 'groups.create' && empty($act->like_type)){
            $act->like_type = 'groups.create';
            $act->store();
        }

        if ($type == 'comment') {
            $act = JTable::getInstance('Wall', 'CTable');
            $act->load($actid);
            $act->like_type = 'comment';
            $act->like_id = $act->id;
        }

        $params = new CParameter($act->params);

        // this is used to seperate the like from the actual pictures
        if (isset($act->app) && $act->app == 'photos' && $params->get('batchcount', 0) >= 1) {
            $act->like_type = 'album.self.share';
            $act->like_id = $act->id;
        } else if (isset($act->app) && $act->app == 'videos') {
            $act->like_type = 'videos.self.share';
            $act->like_id = $act->id;
        }

        // Count before the add
        $oldLikeCount = $like->getLikeCount($act->like_type, $act->like_id);

        $like->addLike($act->like_type, $act->like_id);

        $likeCount = $like->getLikeCount($act->like_type, $act->like_id);

        $json = array();
        $json['success'] = true;

        // If the like count is 1, then, the like bar most likely not there before
        // but, people might just click twice, hence the need to compare it before
        // the actual like

        if ($likeCount == 1 && $oldLikeCount != $likeCount) {
            // Clear old like status
            $objResponse->addScriptCall("joms.jQuery('#wall-cmt-{$actid} .cStream-Likes').remove", '');
            $objResponse->addScriptCall("joms.jQuery('#wall-cmt-{$actid}').prepend", '<div class="cStream-Likes"></div>');
        }
        if ($type == 'comment') {
            $json['html'] = $this->_commentShowLikes($objResponse, $act->id);
        } else {
            $json['html'] = $this->_streamShowLikes($objResponse, $act->id, $act->like_type, $act->like_id);
        }

        die( json_encode($json) );
    }

    /**
     *
     */
    public function ajaxStreamUnlike($actid, $type = null) {
        $filter = JFilterInput::getInstance();
        $actid = $filter->clean($actid, 'int');
        $objResponse = new JAXResponse();
        $like = new CLike();

        $act = JTable::getInstance('Activity', 'CTable');
        $act->load($actid);

        if ($type == 'comment') {
            $act = JTable::getInstance('Wall', 'CTable');
            $act->load($actid);
            $act->like_type = 'comment';
            $act->like_id = $act->id;
        }

        $params = new CParameter($act->params);

        if (isset($act->app) && $act->app == 'photos') {
            $act->like_type = 'album.self.share';
            $act->like_id = $act->id;
        } else if (isset($act->app) && $act->app == 'videos') {
            $act->like_type = 'videos.self.share';
            $act->like_id = $act->id;
        }

        $like->unlike($act->like_type, $act->like_id);

        $json = array();
        $json['success'] = true;

        if ($type == 'comment') {
            $json['html'] = $this->_commentShowLikes($objResponse, $act->id);
        } else {
            $json['html'] = $this->_streamShowLikes($objResponse, $act->id, $act->like_type, $act->like_id);
        }

        die( json_encode($json) );
    }

    /**
     * List down all people who like it
     *
     */
    public function ajaxStreamShowLikes($actid, $target = '') {
        $filter = JFilterInput::getInstance();
        $actid = $filter->clean($actid, 'int');

        $objResponse = new JAXResponse();
        $wallModel = CFactory::getModel('wall');

        // Pull the activity record
        $act = JTable::getInstance('Activity', 'CTable');
        $act->load($actid);

        $params = new CParameter($act->params);

        if (isset($act->app) && $act->app == 'photos' && $params->get('batchcount', 0) > 0) {
            $act->like_type = 'album.self.share';
            $act->like_id = $act->id;
        } else if (isset($act->app) && $act->app == 'videos') {
            $act->like_type = 'videos.self.share';
            $act->like_id = $act->id;
        }

        $json = array(
            'success' => true,
            'html' => $this->_streamShowLikes($objResponse, $actid, $act->like_type, $act->like_id, $target)
        );

        die( json_encode( $json ) );
    }

    public function ajaxDeleteTempImage() {
        $jinput = JFactory::getApplication()->input;
        $photo_ids = $jinput->get('arg2', 'default_value', 'array');
        //$photo_ids = (!isset($photo_ids)) ? '' : explode(',', $photo_ids);

        $my = CFactory::getUser();

        if (isset($photo_ids) && count($photo_ids) > 0) {
            foreach ($photo_ids as $photoid) {
                $photo = JTable::getInstance('Photo', 'CTable');
                $photo->load($photoid);

                //we must make sure that the creator is the current user
                if ($photo->creator == $my->id && $photo->status == 'temp') {
                    $photo->delete();
                }
            }
        }

        exit;
    }

    /**
     * Display the full list of people who likes this stream item
     *
     * @param <type> $objResponse
     * @param <type> $actid
     * @param <type> $like_type
     * @param <type> $like_id
     */
    private function _streamShowLikes($objResponse, $actid, $like_type, $like_id, $target = '') {
        $my = CFactory::getUser();
        $like = new CLike();

        $likes = $like->getWhoLikes($like_type, $like_id);

        $canUnlike = false;
        $likeHTML = '';
        $likeUsers = array();

        foreach ($likes as $user) {
            $likeUsers[] = '<a href="' . CUrlHelper::userLink($user->id) . '">' . $user->getDisplayName() . '</a>';
            if ($my->id == $user->id)
                $canUnlike = true;
        }

        if (count($likeUsers) != 0) {
            if ( $target === 'popup' ) {
                $tmpl = new CTemplate();
                $tmpl->set('users', $likes);
                $likeHTML = $tmpl->fetch('ajax.stream.showothers');
            } else {
                $likeHTML .= implode(", ", $likeUsers);
                $likeHTML = CStringHelper::isPlural(count($likeUsers)) ? JText::sprintf('COM_COMMUNITY_LIKE_THIS_MANY_LIST', $likeHTML) : JText::sprintf('COM_COMMUNITY_LIKE_THIS_LIST', $likeHTML);
            }
        }

        return $likeHTML;
    }

    private function _commentShowLikes($obj, $actid) {
        $my = CFactory::getUser();
        $like = new CLike();

        $likeHTML = '';
        $likeCount = $like->getLikeCount('comment', $actid);

        if ($likeCount > 0) {
            $likeHTML = '<a href="javascript:" data-action="showlike" onclick="joms.api.commentShowLikes(\'' . $actid . '\');"><i class="joms-icon-thumbs-up"></i><span>' . $likeCount . '</span></a>';
        }

        return $likeHTML;
    }

    public function ajaxeditComment($id, $value, $photoId = 0) {
        $config = CFactory::getConfig();
        $my = CFactory::getUser();
        $actModel = CFactory::getModel('activities');
        $objResponse = new JAXResponse();
        $json = array();

        if ($my->id == 0) {
            $this->blockUnregister();
        }

        $wall = JTable::getInstance('wall', 'CTable');
        $wall->load($id);

        $cid = isset($wall->contentid) ? $wall->contentid : null;
        $activity = $actModel->getActivity($cid);
        $ownPost = ($my->id == $wall->post_by);
        $targetPost = ($activity->target == $my->id);
        $allowEdit = COwnerHelper::isCommunityAdmin() || ( ( $ownPost || $targetPost ) && !empty($my->id) );
        $value = trim($value);

        if (empty($value)) {
            $json['error'] = JText::_('COM_COMMUNITY_CANNOT_EDIT_COMMENT_ERROR');
        } else if ($config->get('wallediting') && $allowEdit) {
            $params = new CParameter($wall->params);

            //if photo id is not 0, this wall is appended with a picture
            if($photoId > 0 && $params->get('attached_photo_id') != $photoId ){
                //lets check if the photo belongs to the uploader
                $photo = JTable::getInstance('Photo', 'CTable');
                $photo->load($photoId);

                if($photo->creator == $my->id && $photo->albumid == '-1'){
                    $params->set('attached_photo_id', $photoId);

                    //sets the status to ready so that it wont be deleted on cron run
                    $photo->status = 'ready';
                    $photo->store();
                }
            }else if($photoId == -1 ){
                //if there is nothing, remove the param if applicable
                //delete from db and files
                $photoModel = CFactory::getModel('photos');
                $photoTable = $photoModel->getPhoto($params->get('attached_photo_id'));
                $photoTable->delete();

                $params->set('attached_photo_id' , 0);
            }

            $wall->params = $params->toString();
            $wall->comment = $value;
            $wall->store();

            $CComment = new CComment();
            $value = $CComment->stripCommentData($value);

            // Need to perform basic formatting here
            // 1. support nl to br,
            // 2. auto-link text
            $CTemplate = new CTemplate();
            $value = $origValue = $CTemplate->escape($value);
            $value = CStringHelper::autoLink($value);
            $value = nl2br($value);
            $value = CUserHelper::replaceAliasURL($value);
            $value = CStringHelper::getEmoticon($value);

            $json['comment'] = $value;
            $json['originalComment'] = $origValue;

            // $objResponse->addScriptCall("joms.jQuery('div[data-commentid=" . $id . "] .cStream-Content span.comment').html", $value);
            // $objResponse->addScriptCall('joms.jQuery("div[data-commentid=' . $id . '] [data-type=stream-comment-editor] textarea").val', $origValue);
            // $objResponse->addScriptCall('joms.jQuery("div[data-commentid=' . $id . '] [data-type=stream-comment-editor] textarea").removeData', 'initialized');

            // if ($photoId == -1) {
            //     $objResponse->addScriptCall('joms.jQuery("div[data-commentid=' . $id . '] .joms-stream-thumb").parent().remove', '');
            //     $objResponse->addScriptCall('joms.jQuery("div[data-commentid=' . $id . '] .joms-stream-attachment").css("display", "none").attr("data-no_thumb", 1);');
            //     $objResponse->addScriptCall('joms.jQuery("div[data-commentid=' . $id . '] .joms-thumbnail").html', '<img/>');
            // } else if ($photoId != 0) {
            //     $objResponse->addScriptCall('joms.jQuery("div[data-commentid=' . $id . '] .joms-fetch-wrapper").remove', '');
            //     $objResponse->addScriptCall('joms.jQuery("div[data-commentid=' . $id . '] .joms-stream-thumb").parent().remove', '');
            //     $objResponse->addScriptCall('joms.jQuery("div[data-commentid=' . $id . '] [data-type=stream-comment-content] .cStream-Meta").before', '<div style="padding: 5px 0"><img class="joms-stream-thumb" src="' . JUri::root(true) ."/". $photo->thumbnail . '" /></div>');
            //     $objResponse->addScriptCall('joms.jQuery("div[data-commentid=' . $id . '] .joms-stream-attachment").css("display", "block").removeAttr("data-no_thumb");');
            //     $objResponse->addScriptCall('joms.jQuery("div[data-commentid=' . $id . '] .joms-thumbnail img").attr("src", "' . JUri::root(true) ."/". $photo->thumbnail . '").attr("data-photo_id", "0").data("photo_id", 0);');
            // }

        } else {
            $json['error'] = JText::_('COM_COMMUNITY_NOT_ALLOWED_TO_EDIT');
        }


        if ( !isset($json['error']) ) {
            $json['success'] = true;
        }

        die( json_encode($json) );
    }

    /**
     *
     * @param type $text
     * @return type
     */
    public function ajaxGetFetchUrl($text) {
        $graphObject = CParsers::linkFetch($text);
        if ( $graphObject ) {
            $data = (array) $graphObject->toObject();
            die( json_encode($data) );
        }

        die( json_encode($graphObject) );
    }

    public function ajaxGetAdagency(){
        if (CSystemHelper::isComponentExists('com_adagency') && JComponentHelper::getComponent('com_adagency', true)->enabled) {

            $lang = JFactory::getLanguage();
            $extension = 'com_adagency';
            $base_dir = JPATH_SITE;
            $lang->load($extension, $base_dir);

            jimport('joomla.application.component.model');
            JModelLegacy::addIncludePath(JPATH_SITE . '/components/com_adagency/models');
            $agencyModel = JModelLegacy::getInstance('Adagencyadvertisement', 'AdagencyModel');

            $advertisement = $agencyModel->getJomsocialAds();
            $config = $agencyModel->getJomsocialAdSettings();

            // Add configs.
            $config['create_ad_link_text'] = JText::_('ADAG_CREATE_AN_AD');
            $config['sponsored_stream_info_text'] = JText::_('ADAG_SPONSORED_STREAM');

            die(json_encode(
                array(
                    'config' => $config,
                    'ads' => $advertisement
                )
            ));
        }
    }

    public function ajaxAdagencyGetImpression($adsId, $campaignId, $bannerId, $type){
        if (CSystemHelper::isComponentExists('com_adagency') && JComponentHelper::getComponent('com_adagency', true)->enabled) {
            jimport('joomla.application.component.model');
            JModelLegacy::addIncludePath(JPATH_SITE . '/components/com_adagency/models');
            $agencyModel = JModelLegacy::getInstance('Adagencyadvertisement', 'AdagencyModel');

            $agencyModel->increaseImpressions($adsId, $campaignId, $bannerId, $type); // call to increase impression
            die(json_encode(
                array(
                    'status' => true
                )
            ));
        }
    }

    /**
     * this function will call the function via ajax to the respective module
     */
    public function ajaxModuleCall(){
        $jinput = JFactory::getApplication()->input;

        $module = $jinput->get('module','','STRING');
        $method = $jinput->get('method','','STRING');

        //check if module exists
        $mod = JModuleHelper::getModule($module);
        if(isset($mod->id) && $mod->id && !empty($module) && !empty($method)){
            $helperFile = JPATH_ROOT . '/modules/mod_' . $mod->name.'/helper.php';
            if(file_exists($helperFile)){
                require_once($helperFile);
                $modName = explode('_',$mod->name);
                $className = 'Mod';
                foreach($modName as $name){
                    $className .= ucfirst($name);
                }
                $className .= 'Helper';

                if(class_exists($className) && method_exists($className, $method)){
                    return $className::$method();
                }
            }
        }

        return false;
    }

    //this will return the login token for the form
    public function ajaxGetLoginFormToken(){
        die( json_encode( array('token' => JSession::getFormToken() ) ) );
    }

    public function ajaxFeatureStream($streamType, $contextId, $actid){
        $my = CFactory::getUser();

        $extraInfo = array();
        $extraInfo['stream_type'] = $streamType;
        $extraInfo['profile_id'] = $contextId;

        $act = JTable::getInstance('activity','CTable');
        $act->load($actid);
        $act->extraInfo = $extraInfo;
        //display error if there is no id
        if(!$my->id || !$my->authorise('community.feature','activities.stream',$act)) {
            die(json_encode(array('error' => 'Invalid')));
        }

        $featuredModel = CFactory::getModel('featured');
        $featuredTable = $featuredModel->insertFeaturedStream($actid, $streamType, $contextId);

        if($featuredTable){
            die(json_encode(array('success' => TRUE)));
        }
        die(json_encode(array('error' => 'Error.')));
    }

    public function ajaxUnfeatureStream($streamType, $contextId, $actid){
        $my = CFactory::getUser();

        $extraInfo = array();
        $extraInfo['stream_type'] = $streamType;
        $extraInfo['profile_id'] = $contextId;

        $act = JTable::getInstance('activity','CTable');
        $act->load($actid);
        $act->extraInfo = $extraInfo;
        //display error if there is no id
        if(!$my->id || !$my->authorise('community.unfeature','activities.stream',$act)){
            die(json_encode(array('error' => 'Invalid')));
        }

        $featuredModel = CFactory::getModel('featured');
        $status = $featuredModel->deleteFeaturedStream($actid, 'stream.'.$streamType, $contextId);

        if($status){
            die(json_encode(array('success' => TRUE)));
        }
        die(json_encode(array('error' => 'Error.')));
    }

    public function ajaxDefaultUserStream($defaultFilter){
        //get current user
        $my = CFactory::getUser();


        $allowedFilter = array( 'all','privacy:me-and-friends','apps:profile','apps:photo','apps:video','apps:group','apps:event' );
        //quick validation
        if(!$my->id || !in_array($defaultFilter,$allowedFilter)){
            die( json_encode( array(
                'error' => JText::_('COM_COMMUNITY_SAVE_DEFAULT_FILTER_FAILED')
            ) ) );
        }

        //do a quick change here
        //$userParams = new CParameter($my->params);
        $my->_cparams->set('frontpageactivitydefault', $defaultFilter);
        //$my->params = $userParams->toString();
        $my->save();

        die( json_encode( array(
            'success' => true,
            'message' => JText::_('COM_COMMUNITY_SAVE_DEFAULT_FILTER_SUCCESS')
        ) ) );
    }
}
