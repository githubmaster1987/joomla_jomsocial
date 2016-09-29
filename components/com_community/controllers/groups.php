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

/**
 *
 */
class CommunityGroupsController extends CommunityBaseController {

    /**
     * Call the View object to compose the resulting HTML display
     *
     * @param string View function to be called
     * @param mixed extra data to be passed to the View
     */
    public function renderView($viewfunc, $var = NULL) {

        $my = CFactory::getUser();
        $jinput = JFactory::getApplication()->input;
        $document = JFactory::getDocument();
        $viewType = $document->getType();
        $viewName = $jinput->get('view', $this->getName());
        $view = $this->getView($viewName, '', $viewType);

        echo $view->get($viewfunc, $var);
    }

    /**
     * Responsible to return necessary contents to the Invitation library
     * so that it can add the mails into the queue
     * */
    public function inviteUsers($cid, $users, $emails, $message) {
        $group = JTable::getInstance('Group', 'CTable');
        $group->load($cid);
        $content = '';
        $text = '';
        $title = JText::sprintf('COM_COMMUNITY_GROUPS_JOIN_INVITATION_MESSAGE', $group->name);
        $params = '';
        $my = CFactory::getUser();

        if (!$my->authorise('community.view', 'groups.invite.' . $cid, $group)) {
            return false;
        }

        $params = new CParameter('');
        $params->set('url', 'index.php?option=com_community&view=groups&task=viewgroup&groupid=' . $group->id);
        $params->set('groupname', $group->name);
        $params->set('group', $group->name);
        $params->set('group_url', 'index.php?option=com_community&view=groups&task=viewgroup&groupid=' . $group->id);

        if ($users) {
            foreach ($users as $id) {
                $groupInvite = JTable::getInstance('GroupInvite', 'CTable');
                $groupInvite->groupid = $group->id;
                $groupInvite->userid = $id;
                $groupInvite->creator = $my->id;

                $groupInvite->store();
            }
        }
        $htmlTemplate = new CTemplate();
        $htmlTemplate->set('groupname', $group->name);
        $htmlTemplate->set('url', CRoute::getExternalURL('index.php?option=com_community&view=groups&task=viewgroup&groupid=' . $group->id));
        $htmlTemplate->set('message', $message);

        $html = $htmlTemplate->fetch('email.groups.invite.html');

        $textTemplate = new CTemplate();
        $textTemplate->set('groupname', $group->name);
        $textTemplate->set('url', CRoute::getExternalURL('index.php?option=com_community&view=groups&task=viewgroup&groupid=' . $group->id));
        $textTemplate->set('message', $message);
        $text = $textTemplate->fetch('email.groups.invite.text');

        return new CInvitationMail('groups_invite', $html, $text, $title, $params);
    }

    public function editGroupWall($wallId) {
        $wall = JTable::getInstance('Wall', 'CTable');
        $wall->load($wallId);

        $my = CFactory::getUser();

        if ($my->authorise('community.edit', 'groups.wall.' . $wall->contentid, $wall)) {
            return true;
        }
        return false;
    }

    public function editDiscussionWall($wallId) {
        $wall = JTable::getInstance('Wall', 'CTable');
        $wall->load($wallId);

        $discussion = JTable::getInstance('Discussion', 'CTable');
        $discussion->load($wall->contentid);

        $my = CFactory::getUser();

        if ($my->authorise('community.edit', 'groups.discussion.' . $discussion->groupid, $wall)) {
            return true;
        }
        return false;
    }

    public function ajaxRemoveFeatured($groupId) {
        $filter = JFilterInput::getInstance();
        $groupId = $filter->clean($groupId, 'int');

        $json = array();

        if (COwnerHelper::isCommunityAdmin()) {
            $model = CFactory::getModel('Featured');

            $featured = new CFeatured(FEATURED_GROUPS);
            $my = CFactory::getUser();

            if ($featured->delete($groupId)) {
                $json['success'] = true;
                $json['html'] = JText::_('COM_COMMUNITY_GROUP_REMOVED_FROM_FEATURED');
            } else {
                $json['error'] = JText::_('COM_COMMUNITY_REMOVING_GROUP_FROM_FEATURED_ERROR');
            }
        } else {
            $json['error'] = JText::_('COM_COMMUNITY_NOT_ALLOWED_TO_ACCESS_SECTION');
        }

        //ClearCache in Featured List
        $this->cacheClean(array(COMMUNITY_CACHE_TAG_FEATURED, COMMUNITY_CACHE_TAG_GROUPS));

        die( json_encode($json) );
    }

    /**
     * Admin feature the given group
     */
    public function ajaxAddFeatured($groupId) {
        $filter = JFilterInput::getInstance();
        $groupId = $filter->clean($groupId, 'int');

        $json = array();

        if (COwnerHelper::isCommunityAdmin()) {
            $model = CFactory::getModel('Featured');

            if (!$model->isExists(FEATURED_GROUPS, $groupId)) {

                $featured = new CFeatured(FEATURED_GROUPS);
                $table = JTable::getInstance('Group', 'CTable');
                $table->load($groupId);
                $my = CFactory::getUser();
                $config = CFactory::getConfig();
                $limit = $config->get( 'featured' . FEATURED_GROUPS . 'limit' , 10 );

                if($featured->add($groupId, $my->id)===true){
                    $json['success'] = true;
                    $json['html'] = JText::sprintf('COM_COMMUNITY_GROUP_IS_FEATURED', $table->name);
                }else{
                    $json['error'] = JText::sprintf('COM_COMMUNITY_GROUP_LIMIT_REACHED_FEATURED', $table->name, $limit);
                }
            } else {
                $json['error'] = JText::_('COM_COMMUNITY_GROUPS_ALREADY_FEATURED');
            }
        } else {
            $json['error'] = JText::_('COM_COMMUNITY_NOT_ALLOWED_TO_ACCESS_SECTION');
        }

        //ClearCache in Featured List
        $this->cacheClean(array(COMMUNITY_CACHE_TAG_FEATURED, COMMUNITY_CACHE_TAG_GROUPS));

        die( json_encode($json) );
    }

    /**
     * Method is called from the reporting library. Function calls should be
     * registered here.
     *
     * return	String	Message that will be displayed to user upon submission.
     * */
    public function reportDiscussion($link, $message, $discussionId) {
        $report = new CReportingLibrary();

        $report->createReport(JText::_('COM_COMMUNITY_INVALID_DISCUSSION'), $link, $message);

        $action = new stdClass();
        $action->label = 'Remove discussion';
        $action->method = 'groups,removeDiscussion';
        $action->parameters = $discussionId;
        $action->defaultAction = true;

        $report->addActions(array($action));

        return JText::_('COM_COMMUNITY_REPORT_SUBMITTED');
    }

    public function removeDiscussion($discussionId) {
        $model = CFactory::getModel('groups');
        $my = CFactory::getUser();

        if ($my->id == 0) {
            return $this->blockUnregister();
        }

        //CFactory::load( 'models' , 'discussions' );
        $discussion = JTable::getInstance('Discussion', 'CTable');

        $discussion->load($discussionId);
        $discussion->delete();

        //Clear Cache for groups
        $this->cacheClean(array(COMMUNITY_CACHE_TAG_FRONTPAGE, COMMUNITY_CACHE_TAG_GROUPS, COMMUNITY_CACHE_TAG_FEATURED, COMMUNITY_CACHE_TAG_GROUPS_CAT, COMMUNITY_CACHE_TAG_ACTIVITIES));

        return JText::_('COM_COMMUNITY_DISCUSSION_REMOVED');
    }

    /**
     * Method is called from the reporting library. Function calls should be
     * registered here.
     *
     * return	String	Message that will be displayed to user upon submission.
     * */
    public function reportGroup($link, $message, $groupId) {
        //CFactory::load( 'libraries' , 'reporting' );
        $config = CFactory::getConfig();
        $my = CFactory::getUser();
        $report = new CReportingLibrary();

        if (!$my->authorise('community.view', 'groups.report')) {
            return '';
        }

        $report->createReport(JText::_('Bad group'), $link, $message);

        $action = new stdClass();
        $action->label = 'COM_COMMUNITY_GROUPS_UNPUBLISH';
        $action->method = 'groups,unpublishGroup';
        $action->parameters = $groupId;
        $action->defaultAction = true;

        $report->addActions(array($action));

        return JText::_('COM_COMMUNITY_REPORT_SUBMITTED');
    }

    public function unpublishGroup($groupId) {
        $group = JTable::getInstance('Group', 'CTable');
        $group->load($groupId);
        if($group->published == 1)
        {
            $group->published = '0';
            $msg = JText::_('COM_COMMUNITY_GROUPS_UNPUBLISH_SUCCESS');
        }
        else
        {
            $group->published = 1;
            $msg = JText::_('COM_COMMUNITY_GROUPS_PUBLISH_SUCCESS');
        }
        $group->store();

        return $msg;
    }

    /**
     * Displays the default groups view
     * */
    public function display($cacheable = false, $urlparams = false) {
        $config = CFactory::getConfig();
        $my = CFactory::getUser();

        if (!$my->authorise('community.view', 'groups.list')) {
            echo JText::_('COM_COMMUNITY_GROUPS_DISABLE');
            return;
        }

        $this->renderView(__FUNCTION__);
    }

    /**
     * Full application view
     */
    public function app() {
        $view = $this->getView('groups');

        echo $view->get('appFullView');
    }

    /**
     * Full application view for discussion
     */
    public function discussApp() {
        $view = $this->getView('groups');

        echo $view->get('discussAppFullView');
    }

    public function ajaxAcceptInvitation($groupId) {
        $filter = JFilterInput::getInstance();
        $groupId = $filter->clean($groupId, 'int');

        $response = new JAXResponse();
        $my = CFactory::getUser();
        $table = JTable::getInstance('GroupInvite', 'CTable');
        $keys = array('groupid' => $groupId, 'userid' => $my->id);
        $table->load($keys);

        if (!$table->isOwner()) {
            $response->addScriptCall('COM_COMMUNITY_INVALID_ACCESS');
            return $response->sendResponse();
        }

        $this->_saveMember($groupId);
        // delete invitation after approve
        $table->delete();

        $group = JTable::getInstance('Group', 'CTable');
        $group->load($table->groupid);
        $url = CRoute::_('index.php?option=com_community&view=groups&task=viewgroup&groupid=' . $group->id);
        $response->addScriptCall("joms.jQuery('#groups-invite-" . $groupId . "').html('<span class=\"community-invitation-message\">" . JText::sprintf('COM_COMMUNITY_GROUPS_ACCEPTED_INVIT', $group->name, $url) . "</span>');location.reload(true)");

        return $response->sendResponse();
    }

    public function ajaxRejectInvitation($groupId) {
        $filter = JFilterInput::getInstance();
        $groupId = $filter->clean($groupId, 'int');

        $response = new JAXResponse();
        $my = CFactory::getUser();
        $table = JTable::getInstance('GroupInvite', 'CTable');
        $keys = array('groupid' => $groupId, 'userid' => $my->id);
        $table->load($keys);

        if (!$table->isOwner()) {
            // when the user is the owner group we need avoid the invitation
            $table->delete();

            $response->addScriptCall('COM_COMMUNITY_INVALID_ACCESS');
            return $response->sendResponse();
        }

        if ($table->delete()) {
            $group = JTable::getInstance('Group', 'CTable');
            $group->load($table->groupid);
            $url = CRoute::_('index.php?option=com_community&view=groups&task=viewgroup&groupid=' . $group->id);
            $response->addScriptCall("joms.jQuery('#groups-invite-" . $groupId . "').html('<span class=\"community-invitation-message\">" . JText::sprintf('COM_COMMUNITY_GROUPS_REJECTED_INVIT', $group->name, $url) . "</span>')");
        }

        return $response->sendResponse();
    }

    public function ajaxUnpublishGroup($groupId=null) {
		$mainframe = JFactory::getApplication();
        $jinput = $mainframe->input;
        $groupId = $jinput->post->get('groupid', '', 'INT');
        $response = new JAXResponse();

        CError::assert($groupId, '', '!empty', __FILE__, __LINE__);

        if ( ! COwnerHelper::isCommunityAdmin()) {
            $mainframe->enqueueMessage(JText::_('COM_COMMUNITY_ACCESS_FORBIDDEN'), 'error');
            return false;
        } else {
            $group = JTable::getInstance('Group', 'CTable');
            $group->load($groupId);

            if ($group->id == 0) {
                $response->addScriptCall('alert', JText::_('COM_COMMUNITY_GROUPS_ID_NOITEM'));
            } else {
                $group->published = 0;

                if ($group->store()) {
                    //trigger for onGroupDisable
                    $this->triggerGroupEvents('onGroupDisable', $group);
			        $mainframe->redirect(CRoute::_('index.php?option=com_community&view=groups', false), JText::_('COM_COMMUNITY_GROUPS_UNPUBLISH_SUCCESS'));
                } else {
                    $mainframe->enqueueMessage(JText::_('COM_COMMUNITY_GROUPS_SAVE_ERROR'), 'error');
                    return false;
                }
            }
        }
    }

    /**
     *  Ajax function to delete a group
     *
     * @param	$groupId	The specific group id to unpublish
     * */
    public function ajaxDeleteGroup($groupId, $step = 1) {
        $filter = JFilterInput::getInstance();
        $groupId = $filter->clean($groupId, 'int');
        $step = $filter->clean($step, 'int');

        $json = array();
        $response = new JAXResponse();

        $group = JTable::getInstance('Group', 'CTable');
        $group->load($groupId);

        $groupModel = CFactory::getModel('groups');
        $membersCount = $groupModel->getMembersCount($groupId);
        $my = CFactory::getUser();

        // @rule: Do not allow anyone that tries to be funky!
        if (!$my->authorise('community.delete', 'groups.' . $groupId, $group)) {
            $json['error'] = JText::_('COM_COMMUNITY_GROUPS_NOT_ALLOWED_DELETE');
            die( json_encode($json) );
        }

        $doneMessage = ' - <span class=\'success\'>' . JText::_('COM_COMMUNITY_DONE') . '</span><br />';
        $failedMessage = ' - <span class=\'failed\'>' . JText::_('COM_COMMUNITY_FAILED') . '</span><br />';
        $childId = 0;
        switch ($step) {
            case 1:
                // Nothing gets deleted yet. Just show a messge to the next step
                if (empty($groupId)) {
                    $json['error'] = JText::_('COM_COMMUNITY_GROUPS_ID_NOITEM');
                } else {
                    $json['message']  = '<strong>' . JText::sprintf('COM_COMMUNITY_GROUPS_DELETE_GROUP', $group->name) . '</strong><br/>';
                    $json['message'] .= JText::_('COM_COMMUNITY_GROUPS_DELETE_BULLETIN');
                    $json['next'] = 2;

                    //trigger for onBeforeGroupDelete
                    $this->triggerGroupEvents('onBeforeGroupDelete', $group);
                }
                break;

            case 2:
                // Delete all group bulletins
                CommunityModelGroups::getGroupChildId($groupId);
                if (CommunityModelGroups::deleteGroupBulletins($groupId)) {
                    $content = $doneMessage;
                } else {
                    $content = $failedMessage;
                }

                $content .= JText::_('COM_COMMUNITY_GROUPS_DELETE_GROUP_MEMBERS');

                $json['message'] = $content;
                $json['next'] = 3;
                break;

            case 3:
                // Delete all group members
                if (CommunityModelGroups::deleteGroupMembers($groupId)) {
                    $content = $doneMessage;
                } else {
                    $content = $failedMessage;
                }

                $content .= JText::_('COM_COMMUNITY_GROUPS_WALLS_DELETE');

                $json['message'] = $content;
                $json['next'] = 4;
                break;

            case 4:
                // Delete all group wall
                if (CommunityModelGroups::deleteGroupWall($groupId)) {
                    $content = $doneMessage;
                } else {
                    $content = $failedMessage;
                }

                $content .= JText::_('COM_COMMUNITY_GROUPS_DISCUSSIONS_DELETEL');

                $json['message'] = $content;
                $json['next'] = 5;
                break;

            case 5:
                // Delete all group discussions
                if (CommunityModelGroups::deleteGroupDiscussions($groupId)) {
                    $content = $doneMessage;
                } else {
                    $content = $failedMessage;
                }

                $content .= JText::_('COM_COMMUNITY_GROUPS_DELETE_MEDIA');

                $json['message'] = $content;
                $json['next'] = 6;
                break;

            case 6:
                // Delete all group's media files
                if (CommunityModelGroups::deleteGroupMedia($groupId)) {
                    $content = $doneMessage;
                } else {
                    $content = $failedMessage;
                }

                $json['message'] = $content;
                $json['next'] = 7;
                break;

            case 7:
                // Delete group
                $group = JTable::getInstance('Group', 'CTable');
                $group->load($groupId);
                $groupData = $group;

                if ($group->delete($groupId)) {
                    //CFactory::load( 'libraries' , 'featured' );
                    $featured = new CFeatured(FEATURED_GROUPS);
                    $featured->delete($groupId);

                    jimport('joomla.filesystem.file');

                    //@rule: Delete only thumbnail and avatars that exists for the specific group
                    if ($groupData->avatar != "components/com_community/assets/group.jpg" && !empty($groupData->avatar)) {
                        $path = explode('/', $groupData->avatar);
                        $file = JPATH_ROOT . '/' . $path[0] . '/' . $path[1] . '/' . $path[2] . '/' . $path[3];
                        if (JFile::exists($file)) {
                            JFile::delete($file);
                        }
                    }

                    if ($groupData->thumb != "components/com_community/assets/group_thumb.jpg" && !empty($groupData->thumb)) {
                        $path = explode('/', $groupData->thumb);
                        $file = JPATH_ROOT . '/' . $path[0] . '/' . $path[1] . '/' . $path[2] . '/' . $path[3];
                        if (JFile::exists($file)) {
                            JFile::delete($file);
                        }
                    }

                    $db = JFactory::getDbo();
                    //remove all stats from the group
                    $query = "DELETE FROM ".$db->quoteName('#__community_group_stats')
                        ." WHERE ".$db->quoteName('gid')."=".$db->quote($groupId);
                    $db->setQuery($query);
                    $db->execute();

                    $content = JText::_('COM_COMMUNITY_GROUPS_DELETED');

                    //trigger for onGroupDelete
                    $this->triggerGroupEvents('onAfterGroupDelete', $groupData);
                } else {
                    $content = JText::_('COM_COMMUNITY_GROUPS_DELETE_ERROR');
                }

                $redirect = CRoute::_('index.php?option=com_community&view=groups');

                $json['message'] = $content;
                $json['redirect'] = $redirect;
                $json['btnDone'] = JText::_('COM_COMMUNITY_DONE_BUTTON');
                break;

            default:
                break;
        }
        //Clear Cache for groups
        $this->cacheClean(array(COMMUNITY_CACHE_TAG_FRONTPAGE, COMMUNITY_CACHE_TAG_GROUPS, COMMUNITY_CACHE_TAG_FEATURED, COMMUNITY_CACHE_TAG_GROUPS_CAT, COMMUNITY_CACHE_TAG_ACTIVITIES));

        die( json_encode($json) );

        // return $response->sendResponse();
    }

    /**
     *  Ajax function to prompt warning during group deletion
     *
     * @param	$groupId	The specific group id to unpublish
     * */
    public function ajaxWarnGroupDeletion($groupId) {
        $filter = JFilterInput::getInstance();
        $groupId = $filter->clean($groupId, 'int');

        $group = JTable::getInstance('Group', 'CTable');
        $group->load($groupId);

        $json = array(
            'title' => JText::sprintf('COM_COMMUNITY_GROUPS_DELETE_GROUP', $group->name),
            'html'  => JText::_('COM_COMMUNITY_GROUPS_DELETE_WARNING'),
            'btnDelete' => JText::_('COM_COMMUNITY_DELETE'),
            'btnCancel' => JText::_('COM_COMMUNITY_CANCEL_BUTTON')
        );

        die( json_encode($json) );
    }

    /**
     * Ajax function to remove a reply from the discussions
     *
     * @params $discussId	An string that determines the discussion id
     * */
    public function ajaxRemoveReply($wallId) {
        require_once( JPATH_COMPONENT . '/libraries/activities.php' );

        $filter = JFilterInput::getInstance();
        $wallId = $filter->clean($wallId, 'int');

        CError::assert($wallId, '', '!empty', __FILE__, __LINE__);

        $response = new JAXResponse();
        $json = array();

        //@rule: Check if user is really allowed to remove the current wall
        $my = CFactory::getUser();
        $model = $this->getModel('wall');
        $wall = $model->get($wallId);
        //CFactory::load( 'models' , 'discussions' );

        $discussion = JTable::getInstance('Discussion', 'CTable');
        $discussion->load($wall->contentid);

        //CFactory::load( 'helpers' , 'owner' );

        if (!$my->authorise('community.delete', 'groups.discussion.' . $discussion->groupid) && $wall->post_by != $my->id) {
            $errorMsg = $my->authoriseErrorMsg();
            if ($errorMsg == 'blockUnregister') {
                return $this->ajaxBlockUnregister();
            } else {
                $json['error'] = $errorMsg;
            }
        } else {
            if (!$model->deletePost($wallId)) {
                $json['error'] = JText::_('COM_COMMUNITY_GROUPS_REMOVE_WALL_ERROR');
            } else {
                // Update activity.
                CActivities::removeWallActivities(array('app' => 'groups.discussion.reply', 'cid' => $wall->contentid, 'createdAfter' => $wall->date), $wallId);

                //add user points
                if ($wall->post_by != 0) {
                    //CFactory::load( 'libraries' , 'userpoints' );
                    CUserPoints::assignPoint('wall.remove', $wall->post_by);
                }
            }
        }

        $this->cacheClean(array(COMMUNITY_CACHE_TAG_GROUPS_DETAIL));

        if ( !isset($json['error']) ) {
            $json['success'] = true;
        }

        die( json_encode($json) );
    }

    /**
     * Ajax function to display the remove bulletin information
     * */
    public function ajaxShowRemoveBulletin($groupid, $bulletinId) {
        $filter = JFilterInput::getInstance();
        $groupid = $filter->clean($groupid, 'int');
        $bulletinId = $filter->clean($bulletinId, 'int');

        $contents  = JText::_('COM_COMMUNITY_GROUPS_BULLETIN_DELET_CONFIRMATION');
        $contents .= '<form method="POST" action="' . CRoute::_('index.php?option=com_community&view=groups&task=deleteBulletin') . '" style="margin:0;padding:0">';
        $contents .= '<input type="hidden" value="' . $groupid . '" name="groupid">';
        $contents .= '<input type="hidden" value="' . $bulletinId . '" name="bulletinid">';
        $contents .= '</form>';

        $json = array(
            'title'  => JText::_('COM_COMMUNITY_DELETE'),
            'html'   => $contents,
            'btnYes' => JText::_('COM_COMMUNITY_YES_BUTTON'),
            'btnNo'  => JText::_('COM_COMMUNITY_NO_BUTTON')
        );

        die( json_encode($json) );
    }

    /**
     * Ajax function to display the remove discussion information
     * */
    public function ajaxShowRemoveDiscussion($groupid, $topicid) {
        $filter = JFilterInput::getInstance();
        $groupid = $filter->clean($groupid, 'int');
        $topicid = $filter->clean($topicid, 'int');

        $contents  = JText::_('COM_COMMUNITY_GROUPS_DISCUSSION_DELETE_CONFIRMATION');
        $contents .= '<form method="POST" action="' . CRoute::_('index.php?option=com_community&view=groups&task=deleteTopic') . '" style="margin:0;padding:0">';
        $contents .= '<input type="hidden" value="' . $groupid . '" name="groupid">';
        $contents .= '<input type="hidden" value="' . $topicid . '" name="topicid">';
        $contents .= '</form>';

        $json = array(
            'title'  => JText::_('COM_COMMUNITY_DELETE'),
            'html'   => $contents,
            'btnYes' => JText::_('COM_COMMUNITY_YES_BUTTON'),
            'btnNo'  => JText::_('COM_COMMUNITY_NO_BUTTON')
        );

        die( json_encode($json) );
    }

    public function ajaxShowLockDiscussion($groupid, $topicid) {
        $filter = JFilterInput::getInstance();
        $groupid = $filter->clean($groupid, 'int');
        $topicid = $filter->clean($topicid, 'int');

        $discussion = JTable::getInstance('Discussion', 'CTable');
        $discussion->load($topicid);

        $titleLock = $discussion->lock ? JText::_('COM_COMMUNITY_UNLOCK_DISCUSSION') : JText::_('COM_COMMUNITY_LOCK_DISCUSSION');

        $questionLock  = $discussion->lock ? JText::_('COM_COMMUNITY_DISCUSSION_UNLOCK_MESSAGE') : JText::_('COM_COMMUNITY_DISCUSSION_LOCK_MESSAGE');
        $questionLock .= '<form method="POST" action="' . CRoute::_('index.php?option=com_community&view=groups&task=lockTopic') . '" style="margin:0;padding:0">';
        $questionLock .= '<input type="hidden" value="' . $groupid . '" name="groupid">';
        $questionLock .= '<input type="hidden" value="' . $topicid . '" name="topicid">';
        $questionLock .= '</form>';

        $json = array(
            'title'  => $titleLock,
            'html'   => $questionLock,
            'btnYes' => JText::_('COM_COMMUNITY_YES_BUTTON'),
            'btnNo'  => JText::_('COM_COMMUNITY_NO_BUTTON')
        );

        die( json_encode($json) );
    }

    /**
     * Ajax function to approve a specific member
     *
     * @params	string	id	The member's id that needs to be approved.
     * @params	string	groupid	The group id that the user is in.
     * */
    public function ajaxApproveMember($memberId, $groupId) {
        $filter = JFilterInput::getInstance();
        $groupId = $filter->clean($groupId, 'int');
        $memberId = $filter->clean($memberId, 'int');

        $json = array();

        $my = CFactory::getUser();
        $gMember = CFactory::getUser($memberId);
        //CFactory::load( 'helpers' , 'owner' );

        if (!$my->authorise('community.approve', 'groups.member.' . $groupId)) {
            $json['error'] = JText::_('COM_COMMUNITY_NOT_ALLOWED_TO_ACCESS_SECTION');
        } else {
            // Load required tables
            $member = JTable::getInstance('GroupMembers', 'CTable');
            $group = JTable::getInstance('Group', 'CTable');

            // Load the group and the members table
            $group->load($groupId);
            $keys = array('groupId' => $groupId, 'memberId' => $memberId);
            $member->load($keys);

            //trigger for onGroupJoinApproved
            $this->triggerGroupEvents('onGroupJoinApproved', $group, $memberId);
            $this->triggerGroupEvents('onGroupJoin', $group, $memberId);

            // Only approve members that is really not approved yet.
            if ($member->approved) {
                $json['error'] = JText::_('COM_COMMUNITY_MEMBER_ALREADY_APPROVED');
            } else {
                $member->approve();

                //Update member user table
                $gMember->updateGroupList(true);

                CGroups::joinApproved($group->id, $memberId);

                $json['success'] = true;
                $json['message'] = JText::_('COM_COMMUNITY_GROUPS_APPROVE_MEMBER');

                // email notification
                $params = new CParameter('');
                $params->set('url', CRoute::getExternalURL('index.php?option=com_community&view=groups&task=viewgroup&groupid=' . $group->id));
                $params->set('group', $group->name);
                $params->set('group_url', 'index.php?option=com_community&view=groups&task=viewgroup&groupid=' . $group->id);

                CNotificationLibrary::add('groups_member_approved', $group->ownerid, $memberId, JText::sprintf('COM_COMMUNITY_GROUP_MEMBER_APPROVED_EMAIL_SUBJECT'), '', 'groups.memberapproved', $params);
            }
        }

        $this->cacheClean(array(COMMUNITY_CACHE_TAG_GROUPS, COMMUNITY_CACHE_TAG_ACTIVITIES));

        die( json_encode($json) );
    }

    public function ajaxConfirmMemberRemoval($memberId, $groupId) {
        $filter = JFilterInput::getInstance();
        $groupId = $filter->clean($groupId, 'int');
        $memberId = $filter->clean($memberId, 'int');

        $json = array();

        // Get html
        $member = CFactory::getUser($memberId);
        $html  = JText::sprintf('COM_COMMUNITY_GROUPS_MEMBER_REMOVAL_WARNING', $member->getDisplayName());
        $html .= '<div><label><input type="checkbox" name="block" class="joms-checkbox">&nbsp;' . JText::_('COM_COMMUNITY_ALSO_BAN_MEMBER') . '</label></div>';

        $this->cacheClean(array(COMMUNITY_CACHE_TAG_GROUPS));

        $json = array(
            'title'  => JText::_('COM_COMMUNITY_REMOVE_MEMBER'),
            'html'   => $html,
            'btnYes' => JText::_('COM_COMMUNITY_YES_BUTTON'),
            'btnNo'  => JText::_('COM_COMMUNITY_NO_BUTTON')
        );

        die( json_encode($json) );
    }

    /**
     * Ajax method to remove specific member
     *
     * @params	string	id	The member's id that needs to be approved.
     * @params	string	groupid	The group id that the user is in.
     * */
    public function ajaxRemoveMember($memberId, $groupId) {
        $filter = JFilterInput::getInstance();
        $groupId = $filter->clean($groupId, 'int');
        $memberId = $filter->clean($memberId, 'int');

        $json = array();

        $model = $this->getModel('groups');
        $group = JTable::getInstance('Group', 'CTable');
        $group->load($groupId);

        $my = CFactory::getUser();

        if (!$my->authorise('community.remove', 'groups.member.' . $memberId, $group)) {
            $errorMsg = $my->authoriseErrorMsg();
            if ($errorMsg == 'blockUnregister') {
                return $this->ajaxBlockUnregister();
            } else {
                $json['error'] = $errorMsg;
            }
        } else {
            $groupMember = JTable::getInstance('GroupMembers', 'CTable');
            $keys = array('groupId' => $groupId, 'memberId' => $memberId);
            $groupMember->load($keys);

            $data = new stdClass();

            $data->groupid = $groupId;
            $data->memberid = $memberId;

            $model->removeMember($data);
            $user = CFactory::getUser($memberId);
            $user->updateGroupList(true);

            //trigger for onGroupLeave
            $this->triggerGroupEvents('onGroupLeave', $group, $memberId);

            //add user points
            CUserPoints::assignPoint('group.member.remove', $memberId);

            //delete invitation
            $invitation = JTable::getInstance('Invitation', 'CTable');
            $invitation->deleteInvitation($groupId, $memberId, 'groups,inviteUsers');

            $json['success'] = true;
            $json['message'] = JText::_('COM_COMMUNITY_GROUPS_MEMBERS_DELETE_SUCCESS');
        }

        // Store the group and update the data
        $group->updateStats();
        $group->store();

        die( json_encode($json) );
    }

    /**
     * Ajax method to display HTML codes to unpublish group
     *
     * @params	string	groupid	The group id that the user is in.
     * */
    public function ajaxShowUnpublishGroup($groupId) {
        $filter = JFilterInput::getInstance();
        $groupId = $filter->clean($groupId, 'int');

        $response = new JAXResponse();

        $model = $this->getModel('groups');
        $my = CFactory::getUser();

        $group = JTable::getInstance('Group', 'CTable');
        $group->load($groupId);

        $html  = JText::_('COM_COMMUNITY_GROUPS_UNPUBLISH_CONFIRMATION') . ' <strong>' . $group->name . '</strong>?';
        $html .= '<form method="POST" action="' . CRoute::_('index.php?option=com_community&view=groups&task=ajaxUnpublishGroup') . '" style="margin:0">';
        $html .= '<input type="hidden" value="' . $groupId . '" name="groupid">';
        $html .= '</form>';

        $json = array(
            'title'  => JText::_('COM_COMMUNITY_GROUPS_UNPUBLISH'),
            'html'   => $html,
            'btnYes' => JText::_('COM_COMMUNITY_YES_BUTTON'),
            'btnNo'  => JText::_('COM_COMMUNITY_NO_BUTTON'),
        );

        die( json_encode($json) );
    }

    /**
     * Ajax method to display HTML codes to leave group
     *
     * @params	string	id	The member's id that needs to be approved.
     * @params	string	groupid	The group id that the user is in.
     * */
    public function ajaxShowLeaveGroup($groupId) {
        $filter = JFilterInput::getInstance();
        $groupId = $filter->clean($groupId, 'int');

        $json = array();

        $model = $this->getModel('groups');
        $my = CFactory::getUser();

        $group = JTable::getInstance('Group', 'CTable');
        $group->load($groupId);

        $html  = JText::_('COM_COMMUNITY_GROUPS_MEMBERS_LEAVE_CONFIRMATION') . ' <strong>' . $group->name . '</strong>?';
        $html .= '<form method="POST" action="' . CRoute::_('index.php?option=com_community&view=groups&task=leavegroup') . '" style="margin:0;padding:0;">';
        $html .= '<input type="hidden" value="' . $groupId . '" name="groupid">';
        $html .= '</form>';

        $json = array(
            'title'  => JText::_('COM_COMMUNITY_GROUPS_LEAVE'),
            'html'   => $html,
            'btnYes' => JText::_('COM_COMMUNITY_YES_BUTTON'),
            'btnNo'  => JText::_('COM_COMMUNITY_NO_BUTTON')
        );

        die( json_encode($json) );
    }

    /**
     * Ajax function to display the join group
     *
     * @params $groupid	A string that determines the group id
     * */
    public function ajaxShowJoinGroup($groupId, $redirectUrl) {
        $filter = JFilterInput::getInstance();
        $groupId = $filter->clean($groupId, 'int');
        $redirectUrl = $filter->clean($redirectUrl, 'string');

        if (!COwnerHelper::isRegisteredUser()) {
            return $this->ajaxBlockUnregister();
        }

        $response = new JAXResponse();

        $model = $this->getModel('groups');
        $my = CFactory::getUser();
        $group = JTable::getInstance('Group', 'CTable');
        $group->load($groupId);

        $members = $model->getMembersId($groupId);

        ob_start();
        ?>
        <div id="community-groups-join">
            <?php if (in_array($my->id, $members)): ?>
                <?php
                $buttons = '<input onclick="cWindowHide();" type="submit" value="' . JText::_('COM_COMMUNITY_BUTTON_CLOSE_BUTTON') . '" class="btn" name="Submit"/>';
                ?>
                <p><?php echo JText::_('COM_COMMUNITY_GROUPS_ALREADY_MEMBER'); ?></p>
            <?php else: ?>
                <?php
                $buttons = '<form class="reset-gap" name="jsform-groups-ajaxshowjoingroup" method="post" action="' . CRoute::_('index.php?option=com_community&view=groups&task=joingroup') . '">';
                $buttons .= '<input type="hidden" value="' . $groupId . '" name="groupid" />';
                $buttons .= '<input onclick="cWindowHide();" type="button" value="' . JText::_('COM_COMMUNITY_NO_BUTTON') . '" class="btn" name="Submit" />';
                $buttons .= '<input type="submit" value="' . JText::_('COM_COMMUNITY_YES_BUTTON') . '" class="btn btn-primary pull-right" name="Submit"/>';
                $buttons .= '</form>';
                ?>
                <p>
                    <?php echo JText::sprintf('COM_COMMUNITY_GROUPS_JOIN_CONFIRMATION', $group->name); ?>
                </p>
            <?php endif; ?>
        </div>
        <?php
        $contents = ob_get_contents();
        ob_end_clean();

        // Change cWindow title
        $response->addAssign('cwin_logo', 'innerHTML', JText::_('COM_COMMUNITY_GROUPS_JOIN'));
        $response->addScriptCall('cWindowAddContent', $contents, $buttons);

        return $response->sendResponse();
    }

    /**
     * Ajax Method to remove specific wall from the specific group
     *
     * @param wallId	The unique wall id that needs to be removed.
     * @todo: check for permission
     * */
    public function ajaxRemoveWall($wallId) {
        $filter = JFilterInput::getInstance();
        $wallId = $filter->clean($wallId, 'int');

        CError::assert($wallId, '', '!empty', __FILE__, __LINE__);

        $response = new JAXResponse();

        //@rule: Check if user is really allowed to remove the current wall
        $my = CFactory::getUser();
        $model = $this->getModel('wall');
        $wall = $model->get($wallId);

        $group = JTable::getInstance('Group', 'CTable');
        $group->load($wall->contentid);

        //CFactory::load( 'helpers' , 'owner' );

        if (!$my->authorise('community.delete', 'groups.wall.' . $group->id)) {
            $errorMsg = $my->authoriseErrorMsg();

            if ($errorMsg == 'blockUnregister') {
                return $this->ajaxBlockUnregister();
            } else {
                $response->addScriptCall('alert', $errorMsg);
            }
        } else {
            if (!$model->deletePost($wallId)) {
                $response->addAlert(JText::_('COM_COMMUNITY_GROUPS_REMOVE_WALL_ERROR'));
            } else {
                if ($wall->post_by != 0) {
                    //add user points
                    //CFactory::load( 'libraries' , 'userpoints' );
                    CUserPoints::assignPoint('wall.remove', $wall->post_by);
                }
            }

            $group->updateStats();
            $group->store();
        }
        $this->cacheClean(array(COMMUNITY_CACHE_TAG_GROUPS));
        return $response->sendResponse();
    }

    /**
     * Ajax function to add new admin to the group
     *
     * @param memberid	Members id
     * @param groupid	Groupid
     *
     * */
    public function ajaxRemoveAdmin($memberId, $groupId) {
        return $this->updateAdmin($memberId, $groupId, false);
    }

    /**
     * Ajax function to add new admin to the group
     *
     * @param memberid	Members id
     * @param groupid	Groupid
     *
     * */
    public function ajaxAddAdmin($memberId, $groupId) {
        return $this->updateAdmin($memberId, $groupId, true);
    }

    public function updateAdmin($memberId, $groupId, $doAdd = true) {
        $filter = JFilterInput::getInstance();
        $groupId = $filter->clean($groupId, 'int');
        $memberId = $filter->clean($memberId, 'int');

        $response = new JAXResponse();

        $my = CFactory::getUser();

        $model = $this->getModel('groups');
        $group = JTable::getInstance('Group', 'CTable');
        $group->load($groupId);

        //CFactory::load( 'helpers' , 'owner' );

        if (!$my->authorise('community.edit', 'groups.admin.' . $groupId, $group)) {
            $response->addScriptCall('joms.jQuery("#notice-message").html("' . JText::_('COM_COMMUNITY_PERMISSION_DENIED_WARNING') . '");');
            $response->addScriptCall('joms.jQuery("#notice").css("display","block");');
            $response->addScriptCall('joms.jQuery("#notice").attr("class","alert alert-danger");');
        } else {
            $member = JTable::getInstance('GroupMembers', 'CTable');

            $keys = array('groupId' => $group->id, 'memberId' => $memberId);
            $member->load($keys);
            $member->permissions = $doAdd ? 1 : 0;

            $member->store();
            $message = $doAdd ? JText::_('COM_COMMUNITY_GROUPS_NEW_ADMIN_MESSAGE') : JText::_('COM_COMMUNITY_GROUPS_NEW_USER_MESSAGE');
            $response->addScriptCall('joms.jQuery("#member_' . $memberId . '");');
            $response->addScriptCall('joms.jQuery("#notice-message").html("' . $message . '");');
            $response->addScriptCall('joms.jQuery("#notice").css("display","block");');

            if ($doAdd) {
                $response->addScriptCall('joms.jQuery("#member_' . $memberId . ' ul li.setAdmin")[0].addClass("hide");');
                $response->addScriptCall('joms.jQuery("#member_' . $memberId . ' ul li.setAdmin")[1].removeClass("hide");');
            } else {
                $response->addScriptCall('joms.jQuery("#member_' . $memberId . ' ul li.setAdmin")[1].addClass("hide");');
                $response->addScriptCall('joms.jQuery("#member_' . $memberId . ' ul li.setAdmin")[0].removeClass("hide");');
            }
        }

        return $response->sendResponse();
    }

    /**
     * Ajax function to save a new wall entry
     *
     * @param message	A message that is submitted by the user
     * @param uniqueId	The unique id for this group
     *
     * */
    public function ajaxSaveDiscussionWall($message, $uniqueId, $photoId = 0) {

        $filter = JFilterInput::getInstance();
        //$message = $filter->clean($message, 'string');
        $uniqueId = $filter->clean($uniqueId, 'int');

        if (!COwnerHelper::isRegisteredUser()) {
            return $this->ajaxBlockUnregister();
        }

        $response = new JAXResponse();
        $json = array();

        $my = CFactory::getUser();

        // Load models
        $group = JTable::getInstance('Group', 'CTable');
        $discussionModel = CFactory::getModel('Discussions');
        $discussion = JTable::getInstance('Discussion', 'CTable');
        //$message		= strip_tags( $message );
        $discussion->load($uniqueId);
        $group->load($discussion->groupid);

        // If the content is false, the message might be empty.
        if (empty($message) && $photoId == 0) {
            $json['error'] = JText::_('COM_COMMUNITY_EMPTY_MESSAGE');
            die( json_encode($json) );
        }
        $config = CFactory::getConfig();

        // @rule: Spam checks
        if ($config->get('antispam_akismet_walls')) {
            //CFactory::load( 'libraries' , 'spamfilter' );

            $filter = CSpamFilter::getFilter();
            $filter->setAuthor($my->getDisplayName());
            $filter->setMessage($message);
            $filter->setEmail($my->email);
            $filter->setURL(CRoute::_('index.php?option=com_community&view=groups&task=viewdiscussion&groupid=' . $discussion->groupid . '&topicid=' . $discussion->id));
            $filter->setType('message');
            $filter->setIP($_SERVER['REMOTE_ADDR']);

            if ($filter->isSpam()) {
                $json['error'] = JText::_('COM_COMMUNITY_WALLS_MARKED_SPAM');
                die( json_encode($json) );
            }
        }
        // Save the wall content
        $wall = CWallLibrary::saveWall($uniqueId, $message, 'discussions', $my, ($my->id == $discussion->creator), 'groups,discussion', 'wall/content', 0, $photoId);
        $date = JDate::getInstance();

        $discussion->lastreplied = $date->toSql();
        $discussion->store();

        // @rule: only add the activities of the wall if the group is not private.
        //if( $group->approvals == COMMUNITY_PUBLIC_GROUP ) {
        // Build the URL
        $discussURL = CUrl::build('groups', 'viewdiscussion', array('groupid' => $discussion->groupid, 'topicid' => $discussion->id), true);

        $act = new stdClass();
        $act->cmd = 'group.discussion.reply';
        $act->actor = $my->id;
        $act->target = 0;
        $act->title = '';
        $act->content = $message;
        $act->app = 'groups.discussion.reply';
        $act->cid = $discussion->id;
        $act->groupid = $group->id;
        $act->group_access = $group->approvals;

        $act->like_id = $wall->id;
        $act->like_type = 'groups.discussion.reply';

        $params = new CParameter('');
        $params->set('action', 'group.discussion.reply');
        $params->set('wallid', $wall->id);
        $params->set('group_url', 'index.php?option=com_community&view=groups&task=viewgroup&groupid=' . $group->id);
        $params->set('group_name', $group->name);
        $params->set('discuss_url', 'index.php?option=com_community&view=groups&task=viewdiscussion&groupid=' . $discussion->groupid . '&topicid=' . $discussion->id);

        // Add activity log
        CActivityStream::add($act, $params->toString());


        // Get repliers for this discussion and notify the discussion creator too
        $users = $discussionModel->getRepliers($discussion->id, $group->id);
        $users[] = $discussion->creator;

        // Make sure that each person gets only 1 email
        $users = array_unique($users);

        // The person who post this, should not be getting notification email
        $key = array_search($my->id, $users);

        if ($key !== false && isset($users[$key])) {
            unset($users[$key]);
        }

        // Add notification
        $params = new CParameter('');
        $params->set('url', 'index.php?option=com_community&view=groups&task=viewdiscussion&groupid=' . $discussion->groupid . '&topicid=' . $discussion->id);
        $params->set('message',  CUserHelper::replaceAliasURL($message));
        $params->set('title', $discussion->title);
        $params->set('discussion', $discussion->title);
        $params->set('discussion_url', 'index.php?option=com_community&view=groups&task=viewdiscussion&groupid=' . $discussion->groupid . '&topicid=' . $discussion->id);
        CNotificationLibrary::add('groups_discussion_reply', $my->id, $users, JText::_('COM_COMMUNITY_GROUP_NEW_DISCUSSION_REPLY_SUBJECT'), '', 'groups.discussion.reply', $params);

        //email and add notification if user are tagged
        CUserHelper::parseTaggedUserNotification($message, $my, null, array('type' => 'discussion-comment','group_id' => $group->id, 'discussion_id' => $discussion->id ));

        //add user points
        //CFactory::load( 'libraries' , 'userpoints' );
        CUserPoints::assignPoint('group.discussion.reply');

        $config = CFactory::getConfig();
        $order = $config->get('group_discuss_order');
        $order = ($order == 'DESC') ? 'prepend' : 'append';

        $json['html'] = $wall->content;
        $json['success'] = true;

        $this->cacheClean(array(COMMUNITY_CACHE_TAG_ACTIVITIES, COMMUNITY_CACHE_TAG_GROUPS_DETAIL));

        die( json_encode($json) );
    }

    /**
     * Ajax function to save a new wall entry
     *
     * @param message	A message that is submitted by the user
     * @param uniqueId	The unique id for this group
     * @deprecated since 2.4
     *
     * */
    public function ajaxSaveWall($message, $groupId) {
        $filter = JFilterInput::getInstance();
        $message = $filter->clean($message, 'string');
        $groupId = $filter->clean($groupId, 'int');

        $response = new JAXResponse();
        $my = CFactory::getUser();

        $groupModel = CFactory::getModel('groups');
        $group = JTable::getInstance('Group', 'CTable');
        $group->load($groupId);
        $config = CFactory::getConfig();

        // @rule: If configuration is set for walls in group to be restricted to memebers only,
        // we need to respect this.
        if (!$my->authorise('community.save', 'groups.wall.' . $groupId, $group)) {
            $response->addScriptCall('alert', JText::_('COM_COMMUNITY_ACCESS_FORBIDDEN'));
            return $response->sendResponse();
        }

        $message = strip_tags($message);
        // If the content is false, the message might be empty.
        if (empty($message)) {
            $response->addAlert(JText::_('COM_COMMUNITY_EMPTY_MESSAGE'));
        } else {
            $isAdmin = $groupModel->isAdmin($my->id, $group->id);

            // @rule: Spam checks
            if ($config->get('antispam_akismet_walls')) {
                //CFactory::load( 'libraries' , 'spamfilter' );

                $filter = CSpamFilter::getFilter();
                $filter->setAuthor($my->getDisplayName());
                $filter->setMessage($message);
                $filter->setEmail($my->email);
                $filter->setURL(CRoute::_('index.php?option=com_community&view=groups&task=viewgroup&groupid=' . $groupId));
                $filter->setType('message');
                $filter->setIP($_SERVER['REMOTE_ADDR']);

                if ($filter->isSpam()) {
                    $response->addAlert(JText::_('COM_COMMUNITY_WALLS_MARKED_SPAM'));
                    return $response->sendResponse();
                }
            }

            // Save the wall content
            $wall = CWallLibrary::saveWall($groupId, $message, 'groups', $my, $isAdmin, 'groups,group');

            // Store event will update all stats count data
            $group->updateStats();
            $group->store();

            // @rule: only add the activities of the wall if the group is not private.
            if ($group->approvals == COMMUNITY_PUBLIC_GROUP) {

                $params = new CParameter('');
                $params->set('action', 'group.wall.create');
                $params->set('wallid', $wall->id);
                $params->set('group_url', 'index.php?option=com_community&view=groups&task=viewgroup&groupid=' . $groupId);

                $act = new stdClass();
                $act->cmd = 'group.wall.create';
                $act->actor = $my->id;
                $act->target = 0;
                $act->title = JText::sprintf('COM_COMMUNITY_GROUPS_WALL_POST_GROUP', '{group_url}', $group->name);
                $act->content = $message;
                $act->app = 'groups.wall';
                $act->cid = $wall->id;
                $act->groupid = $group->id;

                // Allow comments
                $act->comment_type = 'groups.wall';
                $act->comment_id = $wall->id;

                CActivityStream::add($act, $params->toString());
            }

            // @rule: Add user points
            //CFactory::load( 'libraries' , 'userpoints' );
            CUserPoints::assignPoint('group.wall.create');

            // @rule: Send email notification to members
            $groupParams = $group->getParams();

            if ($groupParams->get('wallnotification') == '1') {
                $model = $this->getModel('groups');
                $members = $model->getMembers($groupId, null);
                $admins = $model->getAdmins($groupId, null);

                $membersArray = array();

                foreach ($members as $row) {
                    if ($my->id != $row->id) {
                        $membersArray[] = $row->id;
                    }
                }

                foreach ($admins as $row) {
                    if ($my->id != $row->id) {
                        $membersArray[] = $row->id;
                    }
                }
                unset($members);
                unset($admins);

                // Add notification
                //CFactory::load( 'libraries' , 'notification' );

                $params = new CParameter('');
                $params->set('url', 'index.php?option=com_community&view=groups&task=viewgroup&groupid=' . $groupId);
                $params->set('group', $group->name);
                $params->set('group_url', 'index.php?option=com_community&view=groups&task=viewgroup&groupid=' . $groupId);
                $params->set('message', $message);
                CNotificationLibrary::add('groups_wall_create', $my->id, $membersArray, JText::sprintf('COM_COMMUNITY_NEW_WALL_POST_NOTIFICATION_EMAIL_SUBJECT', $my->getDisplayName(), $group->name), '', 'groups.wall', $params);
            }
            $response->addScriptCall('joms.walls.insert', $wall->content);
        }

        $this->cacheClean(array(COMMUNITY_CACHE_TAG_GROUPS, COMMUNITY_CACHE_TAG_ACTIVITIES));

        return $response->sendResponse();
    }

    public function ajaxUpdateCount($type, $groupid) {
        $response = new JAXResponse();
        $my = CFactory::getUser();

        if ($my->id) {
            //CFactory::load( 'libraries' , 'groups' );
            $group = JTable::getInstance('Group', 'CTable');
            $group->load($groupid);

            switch ($type) {
                case 'discussion':
                    $discussModel = CFactory::getModel('discussions');
                    $discussions = $discussModel->getDiscussionTopics($groupid, '10');
                    $totalDiscussion = $discussModel->total;

                    $my->setCount('group_discussion_' . $groupid, $totalDiscussion);

                    break;
                case 'bulletin':
                    $bulletinModel = CFactory::getModel('bulletins');
                    $bulletins = $bulletinModel->getBulletins($groupid);
                    $totalBulletin = $bulletinModel->total;

                    $my->setCount('group_bulletin_' . $groupid, $totalBulletin);
                    break;
            }
        }

        return $response->sendResponse();
    }

    public function ajaxUnbanMember($memberId, $groupId) {
        return $this->updateMemberBan($memberId, $groupId, FALSE);
    }

    /**
     * Ban the member from the group
     * @param type $memberId
     * @param type $groupId
     * @return type
     */
    public function ajaxBanMember($memberId, $groupId) {
        return $this->updateMemberBan($memberId, $groupId, TRUE);
    }

    /**
     * Refactored from AjaxUnBanMember and AjaxBanMember
     */
    public function updateMemberBan($memberId, $groupId, $doBan = true) {
        $filter = JFilterInput::getInstance();
        $groupId = $filter->clean($groupId, 'int');
        $memberId = $filter->clean($memberId, 'int');

        if (!COwnerHelper::isRegisteredUser()) {
            return $this->ajaxBlockUnregister();
        }

        $json = array();
        $my = CFactory::getUser();

        $groupModel = CFactory::getModel('groups');
        $group = JTable::getInstance('Group', 'CTable');
        $group->load($groupId);

        if (!$my->authorise('community.update', 'groups.member.ban.' . $groupId, $group)) {
            $json['error'] = JText::_('COM_COMMUNITY_PERMISSION_DENIED_WARNING');
        } else {
            $member = JTable::getInstance('GroupMembers', 'CTable');
            $keys = array('groupId' => $group->id, 'memberId' => $memberId);
            $member->load($keys);

            $member->permissions = ($doBan) ? COMMUNITY_GROUP_BANNED : COMMUNITY_GROUP_MEMBER;

            $member->store();

            $group->updateStats();

            $group->store();

            if ($doBan) { //if user is banned, display the appropriate response and color code
                //trigger for onGroupBanned
                $this->triggerGroupEvents('onGroupBanned', $group, $memberId);
                $json['success'] = true;
                $json['message'] = JText::_('COM_COMMUNITY_GROUPS_MEMBER_BEEN_BANNED');
            } else {
                //trigger for onGroupUnbanned
                $this->triggerGroupEvents('onGroupUnbanned', $group, $memberId);
                $json['success'] = true;
                $json['message'] = JText::_('COM_COMMUNITY_GROUPS_MEMBER_BEEN_UNBANNED');
            }
        }

        die( json_encode($json) );
    }

    /**
     * Ajax retreive Featured Group Information
     * @since 2.6
     */
    public function ajaxShowGroupFeatured($groupId) {
        $my = CFactory::getUser();
        $objResponse = new JAXResponse();

        $group = JTable::getInstance('Group', 'CTable');
        $group->load($groupId);
        $group->updateStats(); //ensure that stats are up-to-date
        // Get Avatar
        $avatar = $group->getAvatar('avatar');

        // group date
        $config = CFactory::getConfig();
        $groupDate = JHTML::_('date', $group->created, JText::_('DATE_FORMAT_LC'));

        // Get group link
        $groupLink = CRoute::_('index.php?option=com_community&view=groups&task=viewgroup&groupid=' . $group->id);

        // Get unfeature icon
        $groupUnfeature = '<a class="album-action remove-featured" title="' . JText::_('COM_COMMUNITY_REMOVE_FEATURED') . '" onclick="joms.featured.remove(\'' . $group->id . '\',\'groups\');" href="javascript:void(0);">' . JText::_('COM_COMMUNITY_REMOVE_FEATURED') . '</a>';

        // Get misc data
        $membercount = JText::sprintf((CStringHelper::isPlural($group->membercount)) ? 'COM_COMMUNITY_GROUPS_MEMBER_COUNT_MANY' : 'COM_COMMUNITY_GROUPS_MEMBER_COUNT', $group->membercount);
        $discussion = JText::sprintf((!CStringHelper::isPlural($group->discusscount)) ? 'COM_COMMUNITY_GROUPS_DISCUSSION_COUNT_MANY' : 'COM_COMMUNITY_GROUPS_DISCUSSION_COUNT', $group->discusscount);
        $wallposts = JText::sprintf((CStringHelper::isPlural($group->wallcount)) ? 'COM_COMMUNITY_GROUPS_WALL_COUNT_MANY' : 'COM_COMMUNITY_GROUPS_WALL_COUNT', $group->wallcount);
        $memberCountLink = CRoute::_('index.php?option=com_community&view=groups&task=viewmembers&groupid=' . $group->id);

        // Get like
        $likes = new CLike();
        $likesHTML = $likes->getHTML('groups', $groupId, $my->id);

        $objResponse->addScriptCall('updateGroup', $groupId, $group->name, $group->getCategoryName(), $likesHTML, $avatar, $groupDate, $groupLink, JHTML::_('string.truncate', strip_tags($group->description), 300), $membercount, $discussion, $wallposts, $memberCountLink, $groupUnfeature);
        $objResponse->sendResponse();
    }

    public function edit() {
        $document = JFactory::getDocument();
        $viewType = $document->getType();
        $jinput = JFactory::getApplication()->input;
        $viewName = $jinput->get('view', $this->getName());
        $config = CFactory::getConfig();

        $view = $this->getView($viewName, '', $viewType);
        $mainframe = JFactory::getApplication();
        $jinput = $mainframe->input;
        $groupId = $jinput->get('groupid', '', 'INT');
        $model = $this->getModel('groups');
        $my = CFactory::getUser();
        $group = JTable::getInstance('Group', 'CTable');
        $group->load($groupId);

        if (empty($group->id)) {
            return JFactory::getApplication()->enqueueMessage(JText::_('COM_COMMUNITY_GROUPS_NOT_FOUND_ERROR'), 'error');
        }

        if (!$my->authorise('community.edit', 'groups.' . $groupId, $group)) {
            $errorMsg = $my->authoriseErrorMsg();
            if ($errorMsg == 'blockUnregister') {
                return $this->blockUnregister();
            } else {
                echo $errorMsg;
            }
            return;
        }

        if ($jinput->getMethod() == 'POST') {
            JSession::checkToken() or jexit(JText::_('COM_COMMUNITY_INVALID_TOKEN'));
            $data = $jinput->post->getArray();

            $config = CFactory::getConfig();
            $inputFilter = CFactory::getInputFilter($config->get('allowhtml'));
            $description = $jinput->post->get('description', '', 'RAW');
            $data['description'] = $inputFilter->clean($description);

            $summary = $jinput->post->get('summary', '', 'string');
            $data['summary'] = $inputFilter->clean($summary);

            $data['unlisted'] = $jinput->post->get('unlisted', 0, 'int');
            if (!isset($data['approvals'])) {
                $data['approvals'] = 0;
            }

            $group->bind($data);

            //CFactory::load( 'libraries' , 'apps' );
            $appsLib = CAppPlugins::getInstance();
            $saveSuccess = $appsLib->triggerEvent('onFormSave', array('jsform-groups-forms'));

            if (empty($saveSuccess) || !in_array(false, $saveSuccess)) {
                $redirect = CRoute::_('index.php?option=com_community&view=groups&task=edit&groupid=' . $groupId, false);

                $removeActivity = $config->get('removeactivities');

                if ($removeActivity) {
                    $activityModel = CFactory::getModel('activities');

                    $activityModel->removeActivity('groups', $group->id);
                }

                // validate all fields
                if (empty($group->name)) {
                    $mainframe->redirect($redirect, JText::_('COM_COMMUNITY_GROUPS_EMPTY_NAME_ERROR'),'error');
                    return;
                }

                if ($model->groupExist($group->name, $group->id)) {
                    $mainframe->redirect($redirect, JText::_('COM_COMMUNITY_GROUPS_NAME_TAKEN_ERROR'),'error');
                    return;
                }

                if (empty($group->description)) {
                    $mainframe->redirect($redirect, JText::_('COM_COMMUNITY_GROUPS_DESCRIPTION_EMPTY_ERROR'),'error');
                    return;
                }

                if (empty($group->categoryid)) {
                    $mainframe->redirect($redirect, JText::_('COM_COMMUNITY_GROUP_CATEGORY_NOT_SELECTED'),'error');
                    return;
                }

                // @rule: Retrieve params and store it back as raw string

                $params = $this->_bindParams();
                $oldParams = new CParameter($group->params);
                if ( $oldParams->get('coverPosition') ) {
                    $params->set('coverPosition', $oldParams->get('coverPosition'));
                }

                if (( preg_match("/\b(?:(?:https?|ftp):\/\/|www\.)[-a-z0-9+&@#\/%?=~_|!:,.;]*[-a-z0-9+&@#\/%=~_|]/i", $description))) {
                    $graphObject = CParsers::linkFetch($description);
                    if ($graphObject){
                        $params->merge($graphObject);
                    }
                }

                $group->params = $params->toString();

                $group->updateStats();
                $group->store();

                $params = new CParameter('');
                $params->set('action', 'group.update');
                $params->set('group_url', 'index.php?option=com_community&view=groups&task=viewgroup&groupid=' . $groupId);

                //add user points
                if(CUserPoints::assignPoint('group.updated')){
                    $act = new stdClass();
                    $act->cmd = 'group.update';
                    $act->actor = $my->id;
                    $act->target = 0;
                    $act->title = ''; //JText::sprintf('COM_COMMUNITY_GROUPS_GROUP_UPDATED' , '{group_url}' , $group->name );
                    $act->content = '';
                    $act->app = 'groups.update';
                    $act->cid = $group->id;
                    $act->groupid = $group->id;
                    $act->group_access = $group->approvals;

                    // Add activity logging. Delete old ones
                    CActivityStream::remove($act->app, $act->cid);
                    CActivityStream::add($act, $params->toString());
                }

                // Update photos privacy
                $photoPermission = $group->approvals ? PRIVACY_GROUP_PRIVATE_ITEM : 0;
                $photoModel = CFactory::getModel('photos');
                $photoModel->updatePermissionByGroup($group->id, $photoPermission);

                // Reupdate the display.
                $mainframe->redirect(CRoute::_('index.php?option=com_community&view=groups&task=viewgroup&groupid=' . $group->id, false), JText::_('COM_COMMUNITY_GROUPS_UPDATED'));
                return;
            }
        }
        $this->cacheClean(array(COMMUNITY_CACHE_TAG_GROUPS_CAT, COMMUNITY_CACHE_TAG_GROUPS, COMMUNITY_CACHE_TAG_ACTIVITIES));
        echo $view->get(__FUNCTION__);
    }

    /**
     * Method to display the create group form
     * */
    public function create() {
        $my = CFactory::getUser();
        $mainframe = JFactory::getApplication();
        $jinput = $mainframe->input;
        $config = CFactory::getConfig();

        if ($my->authorise('community.add', 'groups')) {
            $model = CFactory::getModel('Groups');
            if (CLimitsLibrary::exceedDaily('groups')) {
                $mainframe->redirect(CRoute::_('index.php?option=com_community&view=groups', false), JText::_('COM_COMMUNITY_GROUPS_LIMIT_REACHED'), 'error');
            }

            $model = $this->getModel('groups');
            $data = new stdClass();
            $data->categories = $model->getCategories();

            if ($jinput->post->get('action', '', 'STRING') == 'save') {
                $appsLib = CAppPlugins::getInstance();
                $saveSuccess = $appsLib->triggerEvent('onFormSave', array('jsform-groups-forms'));

                if (empty($saveSuccess) || !in_array(false, $saveSuccess)) {
                    $gid = $this->save();

                    if ($gid !== FALSE) {
                        $mainframe = JFactory::getApplication();

                        $group = JTable::getInstance('Group', 'CTable');
                        $group->load($gid);

                        // Set the user as group member
                        $my->updateGroupList();

                        //lets create the default avatar for the group
                        $avatarAlbum = JTable::getInstance('Album', 'CTable');
                        $avatarAlbum->addAvatarAlbum($group->id, 'group');
                        $coverAlbum = JTable::getInstance('Album', 'CTable');
                        $coverAlbum->addCoverAlbum('group',$group->id);
                        $defaultAlbum = JTable::getInstance('Album', 'CTable');
                        $defaultAlbum->addDefaultAlbum($group->id, 'group');


                        //trigger for onGroupCreate
                        $this->triggerGroupEvents('onGroupCreate', $group);

                        if ($config->get('moderategroupcreation')) {
                            $mainframe->enqueueMessage(JText::sprintf('COM_COMMUNITY_GROUPS_MODERATION_MSG', $group->name), $group->name);
                            $mainframe->redirect(CRoute::_('index.php?option=com_community&view=groups'));
                            return;
                        }

                        $url = CRoute::_('index.php?option=com_community&view=groups&task=created&groupid=' . $gid, false);
                        $mainframe->redirect($url);
                        return;
                    }
                }
            }
        } else {
            $errorMsg = $my->authoriseErrorMsg();
            if ($errorMsg == 'blockUnregister') {
                return $this->blockUnregister();
            } else {
                echo $errorMsg;
            }
            return;
        }
        //Clear Cache in front page
        $this->cacheClean(array(COMMUNITY_CACHE_TAG_FRONTPAGE, COMMUNITY_CACHE_TAG_GROUPS, COMMUNITY_CACHE_TAG_GROUPS_CAT, COMMUNITY_CACHE_TAG_ACTIVITIES));
        $this->renderView(__FUNCTION__, $data);
    }

    /**
     * A new group has been created
     */
    public function created() {
        $this->renderView(__FUNCTION__);
    }

    private function _bindParams() {
        $params = new CParameter('');
        $jinput = JFactory::getApplication()->input;
        $groupId = $jinput->request->getInt('groupid', '');
        $mainframe = JFactory::getApplication();
        $redirect = CRoute::_('index.php?option=com_community&view=groups&task=edit&groupid=' . $groupId, false);

        $params->set('discussordering', 0);

        // Set the group photo permission
        if (array_key_exists('photopermission-admin', $jinput->post->getArray())) {
            $params->set('photopermission', GROUP_PHOTO_PERMISSION_ADMINS);

            if (array_key_exists('photopermission-member', $jinput->post->getArray())) {
                $params->set('photopermission', GROUP_PHOTO_PERMISSION_ALL);
            }
        } else {
            $params->set('photopermission', GROUP_PHOTO_PERMISSION_DISABLE);
        }

        // Set the group video permission
        if (array_key_exists('videopermission-admin', $jinput->post->getArray())) {
            $params->set('videopermission', GROUP_VIDEO_PERMISSION_ADMINS);
            if (array_key_exists('videopermission-member', $jinput->post->getArray())) {
                $params->set('videopermission', GROUP_VIDEO_PERMISSION_ALL);
            }
        } else {
            $params->set('videopermission', GROUP_VIDEO_PERMISSION_DISABLE);
        }


        // Set the group event permission
        if (array_key_exists('eventpermission-admin', $jinput->post->getArray())) {
            $params->set('eventpermission', GROUP_EVENT_PERMISSION_ADMINS);

            if (array_key_exists('eventpermission-member', $jinput->post->getArray())) {
                $params->set('eventpermission', GROUP_EVENT_PERMISSION_ALL);
            }
        } else {
            $params->set('eventpermission', GROUP_EVENT_PERMISSION_DISABLE);
        }

        $config = CFactory::getConfig();
        $grouprecentphotos = $jinput->request->getInt('grouprecentphotos', GROUP_PHOTO_RECENT_LIMIT);
        if ($grouprecentphotos < 1 && $config->get('enablephotos')) {
            $mainframe->redirect($redirect, JText::_('COM_COMMUNITY_GROUP_RECENT_ALBUM_SETTING_ERROR'));
            return;
        }
        $params->set('grouprecentphotos', $grouprecentphotos);

        $grouprecentvideos = $jinput->request->getInt('grouprecentvideos', GROUP_VIDEO_RECENT_LIMIT);
        if ($grouprecentvideos < 1 && $config->get('enablevideos')) {
            $mainframe->redirect($redirect, JText::_('COM_COMMUNITY_GROUP_RECENT_VIDEOS_SETTING_ERROR'));
            return;
        }
        $params->set('grouprecentvideos', $grouprecentvideos);

        $grouprecentevent = $jinput->request->getInt('grouprecentevents', GROUP_EVENT_RECENT_LIMIT);
        if ($grouprecentevent < 1) {
            $mainframe->redirect($redirect, JText::_('COM_COMMUNITY_GROUP_RECENT_EVENTS_SETTING_ERROR'));
            return;
        }
        $params->set('grouprecentevents', $grouprecentevent);

        $newmembernotification = $jinput->post->getInt('newmembernotification', 0);
        $params->set('newmembernotification', $newmembernotification);

        $joinrequestnotification = $jinput->post->getInt('joinrequestnotification', 0);
        $params->set('joinrequestnotification', $joinrequestnotification);

        $wallnotification = $jinput->post->getInt('wallnotification', 0);
        $params->set('wallnotification', $wallnotification);

        $groupdiscussionfilesharing = $jinput->post->getInt('groupdiscussionfilesharing', 0);
        $params->set('groupdiscussionfilesharing', $groupdiscussionfilesharing);

        $groupannouncementfilesharing = $jinput->post->getInt('groupannouncementfilesharing', 0);
        $params->set('groupannouncementfilesharing', $groupannouncementfilesharing);

        return $params;
    }

    /**
     * Method to save the group
     * @return false if create fail, return the group id if create is successful
     * */
    public function save() {
        $mainframe = JFactory::getApplication();
        $jinput = $mainframe->input;

        if (JString::strtoupper($jinput->getMethod()) != 'POST') {
            $document = JFactory::getDocument();
            $viewType = $document->getType();
            $viewName = $jinput->get('view', $this->getName());
            $view = $this->getView($viewName, '', $viewType);
            $view->addWarning(JText::_('COM_COMMUNITY_PERMISSION_DENIED_WARNING'));
            return false;
        }

        $mainframe = JFactory::getApplication();
        $jinput = $mainframe->input;
        JSession::checkToken() or jexit(JText::_('COM_COMMUNITY_INVALID_TOKEN'));

        // Get my current data.
        $my = CFactory::getUser();
        $validated = true;

        $group = JTable::getInstance('Group', 'CTable');
        $model = $this->getModel('groups');

        $name = $jinput->post->get('name', '', 'STRING');

        $config = CFactory::getConfig();
        $inputFilter = CFactory::getInputFilter($config->get('allowhtml'));

        $description = $jinput->post->get('description', '', 'RAW');
        $description = $inputFilter->clean($description);

        $summary = $jinput->post->get('summary', '', 'RAW');
        $summary = $inputFilter->clean($summary);

        $categoryId = $jinput->post->get('categoryid', '', 'INT');
        $website = $jinput->post->get('website', '', 'RAW');
        $grouprecentphotos = $jinput->post->get('grouprecentphotos', '', 'NONE');
        $grouprecentvideos = $jinput->post->get('grouprecentvideos', '', 'NONE');
        $grouprecentevents = $jinput->post->get('grouprecentevents', '', 'NONE');

        // @rule: Test for emptyness
        if (empty($name)) {
            $validated = false;
            $mainframe->enqueueMessage(JText::_('COM_COMMUNITY_GROUPS_EMPTY_NAME_ERROR'), 'error');
        }

        // @rule: Test if group exists
        if ($model->groupExist($name)) {
            $validated = false;
            $mainframe->enqueueMessage(JText::_('COM_COMMUNITY_GROUPS_NAME_TAKEN_ERROR'), 'error');
        }

        // @rule: Test for emptyness
        if (empty($description)) {
            $validated = false;
            $mainframe->enqueueMessage(JText::_('COM_COMMUNITY_GROUPS_DESCRIPTION_EMPTY_ERROR'), 'error');
        }

        if (empty($categoryId)) {
            $validated = false;
            $mainframe->enqueueMessage(JText::_('COM_COMMUNITY_GROUP_CATEGORY_NOT_SELECTED'), 'error');
        }

        if ($grouprecentphotos < 1 && $config->get('enablephotos') && $config->get('groupphotos')) {
            $validated = false;
            $mainframe->enqueueMessage(JText::_('COM_COMMUNITY_GROUP_RECENT_ALBUM_SETTING_ERROR'), 'error');
        }

        if ($grouprecentvideos < 1 && $config->get('enablevideos') && $config->get('groupvideos')) {
            $validated = false;
            $mainframe->enqueueMessage(JText::_('COM_COMMUNITY_GROUP_RECENT_VIDEOS_SETTING_ERROR'), 'error');
        }

        if ($grouprecentevents < 1 && $config->get('enableevents') && $config->get('group_events')) {
            $validated = false;
            $mainframe->enqueueMessage(JText::_('COM_COMMUNITY_GROUP_RECENT_EVENTS_SETTING_ERROR'), 'error');
        }

        if ($validated) {
            // Assertions
            // Category Id must not be empty and will cause failure on this group if its empty.
            CError::assert($categoryId, '', '!empty', __FILE__, __LINE__);

            // @rule: Retrieve params and store it back as raw string
            $params = $this->_bindParams();


            if (( preg_match("/\b(?:(?:https?|ftp):\/\/|www\.)[-a-z0-9+&@#\/%?=~_|!:,.;]*[-a-z0-9+&@#\/%=~_|]/i", $description))) {
                $graphObject = CParsers::linkFetch($description);
                if ($graphObject){
                    $params->merge($graphObject);
                }
            }

            $now = new JDate();

            // Bind the post with the table first
            $group->name = $name;
            $group->description = $description;
            $group->summary= $summary;
            $group->categoryid = $categoryId;
            $group->website = $website;
            $group->ownerid = $my->id;
            $group->created = $now->toSql();
            $group->unlisted = $jinput->post->get('unlisted', 0, 'INT');
            if (array_key_exists('approvals', $jinput->post->getArray())) {
                $group->approvals = $jinput->post->get('approvals', '0', 'INT');
            } else {
                $group->approvals = 0;
            }

            $group->params = $params->toString();

            // @rule: check if moderation is turned on.
            $group->published = ( $config->get('moderategroupcreation') ) ? 0 : 1;

            // we here save the group 1st. else the group->id will be missing and causing the member connection and activities broken.
            $group->store();

            // Since this is storing groups, we also need to store the creator / admin
            // into the groups members table
            $member = JTable::getInstance('GroupMembers', 'CTable');
            $member->groupid = $group->id;
            $member->memberid = $group->ownerid;

            // Creator should always be 1 as approved as they are the creator.
            $member->approved = 1;

            // @todo: Setup required permissions in the future
            $member->permissions = '1';
            $member->store();

            // @rule: Only add into activity once a group is created and it is published.
            if ($group->published && !$group->unlisted) {

                $act = new stdClass();
                $act->cmd = 'group.create';
                $act->actor = $my->id;
                $act->target = 0;
                //$act->title	  	= JText::sprintf('COM_COMMUNITY_GROUPS_NEW_GROUP' , '{group_url}' , $group->name );
                $act->title = JText::sprintf('COM_COMMUNITY_GROUPS_NEW_GROUP_CATEGORY', '{group_url}', $group->name, '{category_url}', $group->getCategoryName());
                $act->content = ( $group->approvals == 0) ? $group->description : '';
                $act->app = 'groups';
                $act->cid = $group->id;
                $act->groupid = $group->id;
                $act->group_access = $group->approvals;

                // Allow comments
                $act->comment_type = 'groups.create';
                $act->like_type = 'groups.create';
                $act->comment_id = CActivities::COMMENT_SELF;
                $act->like_id = CActivities::LIKE_SELF;

                // Store the group now.
                $group->updateStats();
                $group->store();

                $params = new CParameter('');
                $params->set('action', 'group.create');
                $params->set('group_url', 'index.php?option=com_community&view=groups&task=viewgroup&groupid=' . $group->id);
                $params->set('category_url', 'index.php?option=com_community&view=groups&task=display&categoryid=' . $group->categoryid);

                // Add activity logging
                CActivityStream::add($act, $params->toString());
            }

            // if need approval should send email notification to admin
            if ($config->get('moderategroupcreation')) {
                $title_email = JText::_('COM_COMMUNITY_EMAIL_NEW_GROUP_NEED_APPROVAL_TITLE');
                $message_email = JText::sprintf('COM_COMMUNITY_EMAIL_NEW_GROUP_NEED_APPROVAL_MESSAGE', $my->getDisplayName(), $group->name);
                $from = $mainframe->get('mailfrom'); //$jConfig->getValue( 'mailfrom' );
                $to = $config->get('notifyMaxReport');
                CNotificationLibrary::add('groups_create', $from, $to, $title_email, $message_email, '', '');
            }

            //add user points
            CUserPoints::assignPoint('group.create');

            $validated = $group->id;
        }

        return $validated;
    }

    /**
     * Method to search for a group based on the parameter given
     * in a POST request
     * */
    public function search() {
        $my = CFactory::getUser();
        $mainframe = JFactory::getApplication();
        $config = CFactory::getConfig();

        if (!$my->authorise('community.view', 'groups.search')) {
            $errorMsg = $my->authoriseErrorMsg();
            if ($errorMsg == 'blockUnregister') {
                $mainframe->enqueueMessage(JText::_('COM_COMMUNITY_RESTRICTED_ACCESS'), 'notice');
                return $this->blockUnregister();
            } else {
                echo $errorMsg;
            }
            return;
        }

        $this->renderView(__FUNCTION__);
    }

    /**
     * Ajax function call that allows user to leave group
     *
     * @param groupId	The groupid that the user wants to leave from the group
     *
     * */
    public function leaveGroup() {
        $mainframe = JFactory::getApplication();
        $jinput = $mainframe->input;
        $groupId = $jinput->post->get('groupid', '', 'INT');

        CError::assert($groupId, '', '!empty', __FILE__, __LINE__);

        $model = $this->getModel('groups');
        $my = CFactory::getUser();

        if (!$my->authorise('community.leave', 'groups.' . $groupId)) {
            $errorMsg = $my->authoriseErrorMsg();
            if ($errorMsg == 'blockUnregister') {
                return $this->blockUnregister();
            }
        }

        $group = JTable::getInstance('Group', 'CTable');
        $group->load($groupId);

        $data = new stdClass();
        $data->groupid = $groupId;
        $data->memberid = $my->id;

        $model->removeMember($data);

        //trigger for onGroupLeave
        $this->triggerGroupEvents('onGroupLeave', $group, $my->id);

        //add user points
        CUserPoints::assignPoint('group.leave');

        $mainframe = JFactory::getApplication();

        $my->updateGroupList();

        // STore the group and update the data
        $group->updateStats();
        $group->store();

        //delete invitation
        $invitation = JTable::getInstance('Invitation', 'CTable');
        $invitation->deleteInvitation($groupId, $my->id, 'groups,inviteUsers');

        $this->cacheClean(array(COMMUNITY_CACHE_TAG_GROUPS));

        $mainframe->redirect(CRoute::_('index.php?option=com_community&view=groups', false), JText::_('COM_COMMUNITY_GROUPS_LEFT_MESSAGE'));
    }

    /**
     * @since 2.6
     * mygroupupdates page
     *
     */
    public function myGroupUpdate() {
        $config = CFactory::getConfig();
        $my = CFactory::getUser();

        if (!$my->authorise('community.view', 'groups.list')) {
            echo JText::_('COM_COMMUNITY_GROUPS_DISABLE');
            return;
        }

        $this->renderView(__FUNCTION__);
    }

    /**
     * Method is used to receive POST requests from specific user
     * that wants to join a group
     *
     * @return	void
     * */
    public function joinGroup() {
        $mainframe = JFactory::getApplication();
        $jinput = $mainframe->input;

        $groupId = $jinput->post->get('groupid', '', 'INT');
        // Add assertion to the group id since it must be specified in the post request
        CError::assert($groupId, '', '!empty', __FILE__, __LINE__);

        // Get the current user's object
        $my = CFactory::getUser();

        if (!$my->authorise('community.join', 'groups.' . $groupId)) {
            return $this->blockUnregister();
        }

        // Load necessary tables
        $groupModel = CFactory::getModel('groups');

        if ($groupModel->isMember($my->id, $groupId)) {

            $url = CRoute::_('index.php?option=com_community&view=groups&task=viewgroup&groupid=' . $groupId, false);
            $mainframe->redirect($url, JText::_('COM_COMMUNITY_GROUPS_ALREADY_MEMBER'));
        } else {
            $url = CRoute::getExternalURL('index.php?option=com_community&view=groups&task=viewgroup&groupid=' . $groupId, false);

            $member = $this->_saveMember($groupId);
            $this->cacheClean(array(COMMUNITY_CACHE_TAG_GROUPS, COMMUNITY_CACHE_TAG_ACTIVITIES));
            if ($member->approved) {
                $mainframe->redirect($url, JText::_('COM_COMMUNITY_GROUPS_JOIN_SUCCESS'));
            }
            $mainframe->redirect($url, JText::_('COM_COMMUNITY_GROUPS_APPROVAL_NEED'));
        }
    }

    private function _saveMember($groupId) {

        $group = JTable::getInstance('Group', 'CTable');
        $member = JTable::getInstance('GroupMembers', 'CTable');

        $group->load($groupId);
        $params = $group->getParams();
        $my = CFactory::getUser();

        // Set the properties for the members table
        $member->groupid = $group->id;
        $member->memberid = $my->id;

        // @rule: If approvals is required, set the approved status accordingly.
        $member->approved = ( $group->approvals == COMMUNITY_PRIVATE_GROUP ) ? '0' : 1;

        // @rule: Special users should be able to join the group regardless if it requires approval or not
        $member->approved = COwnerHelper::isCommunityAdmin() ? 1 : $member->approved;

        // @rule: Invited users should be able to join the group immediately.
        $groupInvite = JTable::getInstance('GroupInvite', 'CTable');
        $keys = array('groupid' => $groupId, 'userid' => $my->id);
        if ($groupInvite->load($keys)) {
            $member->approved = 1;
        }

        //@todo: need to set the privileges
        $member->permissions = '0';

        $member->store();
        $owner = CFactory::getUser($group->ownerid);

        // Update user group list
        $my->updateGroupList();

        // Test if member is approved, then we add logging to the activities.
        if ($member->approved) {
            CGroups::joinApproved($groupId, $my->id);

            //trigger for onGroupJoin
            $this->triggerGroupEvents('onGroupJoin', $group, $my->id);
        }
        return $member;
    }

    public function uploadAvatar() {
        $mainframe = JFactory::getApplication();
        $jinput = $mainframe->input;

        $document = JFactory::getDocument();
        $viewType = $document->getType();
        $viewName = $jinput->get('view', $this->getName());
        $view = $this->getView($viewName, '', $viewType);
        $my = CFactory::getUser();
        $config = CFactory::getConfig();

        $groupid = $jinput->request->get('groupid', '', 'INT');
        $data = new stdClass();
        $data->id = $groupid;

        $groupsModel = $this->getModel('groups');
        $group = JTable::getInstance('Group', 'CTable');
        $group->load($groupid);

        if (!$my->authorise('community.upload', 'groups.avatar.' . $groupid, $group)) {
            $errorMsg = $my->authoriseErrorMsg();
            if (!$errorMsg) {
                return $this->blockUnregister();
            } else {
                echo $errorMsg;
            }
            return;
        }

        if ($jinput->getMethod() == 'POST') {
            //CFactory::load( 'helpers' , 'image' );
            $fileFilter = new JInput($jinput->files->getArray());
            $file = $fileFilter->get('filedata', '', 'array');

            if (!CImageHelper::isValidType($file['type'])) {
                $mainframe->enqueueMessage(JText::_('COM_COMMUNITY_IMAGE_FILE_NOT_SUPPORTED'), 'error');
                $mainframe->redirect(CRoute::_('index.php?option=com_community&view=groups&task=viewgroup&groupid=' . $group->id . '&task=uploadAvatar', false));
            }

            //CFactory::load( 'libraries' , 'apps' );
            $appsLib = CAppPlugins::getInstance();
            $saveSuccess = $appsLib->triggerEvent('onFormSave', array('jsform-groups-uploadavatar'));

            if (empty($saveSuccess) || !in_array(false, $saveSuccess)) {
                if (empty($file)) {
                    $mainframe->enqueueMessage(JText::_('COM_COMMUNITY_NO_POST_DATA'), 'error');
                } else {
                    $uploadLimit = (double) $config->get('maxuploadsize');
                    $uploadLimit = ( $uploadLimit * 1024 * 1024 );

                    // @rule: Limit image size based on the maximum upload allowed.
                    if (filesize($file['tmp_name']) > $uploadLimit && $uploadLimit != 0) {
                        $mainframe->enqueueMessage(JText::sprintf('COM_COMMUNITY_VIDEOS_IMAGE_FILE_SIZE_EXCEEDED_MB',CFactory::getConfig()->get('maxuploadsize')), 'error');
                        $mainframe->redirect(CRoute::_('index.php?option=com_community&view=groups&task=uploadavatar&groupid=' . $group->id, false));
                    }

                    if (!CImageHelper::isValid($file['tmp_name'])) {
                        $mainframe->enqueueMessage(JText::_('COM_COMMUNITY_IMAGE_FILE_NOT_SUPPORTED'), 'error');
                    } else {
                        // @todo: configurable width?
                        $imageMaxWidth = 160;

                        // Get a hash for the file name.
                        $fileName = JApplicationHelper::getHash($file['tmp_name'] . time());
                        $hashFileName = JString::substr($fileName, 0, 24);

                        // @todo: configurable path for avatar storage?
                        $storage = JPATH_ROOT . '/' . $config->getString('imagefolder') . '/avatar/groups';
                        $storageImage = $storage . '/' . $hashFileName . CImageHelper::getExtension($file['type']);
                        $storageThumbnail = $storage . '/thumb_' . $hashFileName . CImageHelper::getExtension($file['type']);
                        $image = $config->getString('imagefolder') . '/avatar/groups/' . $hashFileName . CImageHelper::getExtension($file['type']);
                        $thumbnail = $config->getString('imagefolder') . '/avatar/groups/' . 'thumb_' . $hashFileName . CImageHelper::getExtension($file['type']);

                        // Generate full image
                        if (!CImageHelper::resizeProportional($file['tmp_name'], $storageImage, $file['type'], $imageMaxWidth)) {
                            $mainframe->enqueueMessage(JText::sprintf('COM_COMMUNITY_ERROR_MOVING_UPLOADED_FILE', $storageImage), 'error');
                        }

                        // Generate thumbnail
                        if (!CImageHelper::createThumb($file['tmp_name'], $storageThumbnail, $file['type'])) {
                            $mainframe->enqueueMessage(JText::sprintf('COM_COMMUNITY_ERROR_MOVING_UPLOADED_FILE', $storageThumbnail), 'error');
                        }

                        // Autorotate avatar based on EXIF orientation value
                        if ($file['type'] == 'image/jpeg') {
                            $orientation = CImageHelper::getOrientation($file['tmp_name']);
                            CImageHelper::autoRotate($storageImage, $orientation);
                            CImageHelper::autoRotate($storageThumbnail, $orientation);
                        }

                        // Update the group with the new image
                        $groupsModel->setImage($groupid, $image, 'avatar');
                        $groupsModel->setImage($groupid, $thumbnail, 'thumb');

                        // add points and generate stream if needed
                        $generateStream = CUserPoints::assignPoint('group.avatar.upload');
                        // @rule: only add the activities of the news if the group is not private.
                        if ($group->approvals == COMMUNITY_PUBLIC_GROUP && $generateStream) {
                            $url = CRoute::_('index.php?option=com_community&view=groups&task=viewgroup&groupid=' . $groupid);
                            $act = new stdClass();
                            $act->cmd = 'group.avatar.upload';
                            $act->actor = $my->id;
                            $act->target = 0;
                            $act->title = JText::sprintf('COM_COMMUNITY_GROUPS_NEW_GROUP_AVATAR', '{group_url}', $group->name);
                            $act->content = '<img src="' . JURI::root(true) . '/' . $thumbnail . '" style="border: 1px solid #eee;margin-right: 3px;" />';
                            $act->app = 'groups';
                            $act->cid = $group->id;
                            $act->groupid = $group->id;

                            $params = new CParameter('');
                            $params->set('group_url', 'index.php?option=com_community&view=groups&task=viewgroup&groupid=' . $group->id);


                            CActivityStream::add($act, $params->toString());
                        }

                        $mainframe = JFactory::getApplication();
                        $mainframe->redirect(CRoute::_('index.php?option=com_community&view=groups&task=viewgroup&groupid=' . $groupid, false), JText::_('COM_COMMUNITY_GROUPS_AVATAR_UPLOADED'));
                        exit;
                    }
                }
            }
        }
        //ClearCache in frontpage
        $this->cacheClean(array(COMMUNITY_CACHE_TAG_FRONTPAGE, COMMUNITY_CACHE_TAG_GROUPS, COMMUNITY_CACHE_TAG_FEATURED, COMMUNITY_CACHE_TAG_ACTIVITIES));

        echo $view->get(__FUNCTION__, $data);
    }

    /**
     * Method that loads the viewing of a specific group
     * */
    public function viewGroup() {
        $mainframe = JFactory::getApplication();
        $jinput = $mainframe->input;
        $config = CFactory::getConfig();
        $my = CFactory::getUser();
        if (!$my->authorise('community.view', 'groups.list')) {
            echo JText::_('COM_COMMUNITY_GROUPS_DISABLE');
            return;
        }

        // Load the group table.
        $groupid = $jinput->getInt('groupid', '');
        $group = JTable::getInstance('Group', 'CTable');
        $group->load($groupid);

        $activityId = $jinput->get->get('actid', 0, 'INT');
        if($activityId){
            $activity = JTable::getInstance('Activity', 'CTable');
            $activity->load($activityId);
            $jinput->set('userid', $activity->actor);
            $userid = $activity->actor;
        }

        if (empty($group->id)) {
            return JFactory::getApplication()->enqueueMessage(JText::_('COM_COMMUNITY_GROUPS_NOT_FOUND_ERROR'), 'error');
        }

        $groupModel = CFactory::getModel('groups');
        if($group->unlisted && !$groupModel->isMember($my->id, $group->id) && !$groupModel->isInvited($my->id, $group->id) && !COwnerHelper::isCommunityAdmin()){
            return JFactory::getApplication()->enqueueMessage(JText::_('COM_COMMUNITY_GROUPS_UNLISTED_ERROR'), 'error');
        }
        if($activityId) {
            $activity->group = $group;
            echo $this->renderView('singleActivity', $activity);
        } else {
            $this->renderView(__FUNCTION__, $group);
        }
    }

    /**
     * Show only current user group
     */
    public function mygroups() {
        $jinput = JFactory::getApplication()->input;
        $my = CFactory::getUser();

        if (!$my->authorise('community.view', 'groups.my')) {
            $errorMsg = $my->authoriseErrorMsg();
            if ($errorMsg == 'blockUnregister') {
                return $this->blockUnregister();
            } else {
                echo $errorMsg;
            }
            return;
        }

        $userid = $jinput->getInt('userid',$my->id);
        $this->renderView(__FUNCTION__, $userid);
    }

    public function myinvites() {
        $config = CFactory::getConfig();
        $my = CFactory::getUser();

        if (!$my->authorise('community.view', 'groups.invitelist')) {
            $errorMsg = $my->authoriseErrorMsg();
            echo $errorMsg;
            return;
        }
        $this->renderView(__FUNCTION__);
    }

    public function viewmembers() {
        $mainframe = JFactory::getApplication();
        $jinput = $mainframe->input;

        $config = CFactory::getConfig();
        $my = CFactory::getUser();
        $data = new stdClass();
        $data->id = $jinput->get('groupid', '', 'INT');

        if (!$my->authorise('community.view', 'groups.member.' . $data->id)) {
            $errorMsg = $my->authoriseErrorMsg();
            echo $errorMsg;
            return;
        }

        $this->renderView(__FUNCTION__, $data);
    }

    /**
     * Show full view of the news for the group
     * */
    public function viewbulletin() {
        $config = CFactory::getConfig();
        $my = CFactory::getUser();
        $jinput = JFactory::getApplication()->input;
        $id = $jinput->get('bulletinid', 0, 'INT');

        if (!$my->authorise('community.view', 'groups.bulletin.' . $id)) {
            $erroMsg = $my->authoriseErrorMsg();
            echo $erroMsg;
            return;
        }

        $this->renderView(__FUNCTION__);
    }

    /**
     * Show all news from specific groups
     * */
    public function viewbulletins() {
        $config = CFactory::getConfig();
        $my = CFactory::getUser();

        if (!$my->authorise('community.view', 'groups.bulletins')) {
            $errorMsg = $my->authoriseErrorMsg();
            echo $errorMsg;
            return;
        }

        $this->renderView(__FUNCTION__);
    }

    /**
     * Show all discussions from specific groups
     * */
    public function viewdiscussions() {
        $this->renderView(__FUNCTION__);
    }

    /**
     * Save a new discussion
     * @param type $discussion
     * @return boolean
     *
     */
    private function _saveDiscussion(&$discussion) {
        $mainframe = JFactory::getApplication();
        $jinput = $mainframe->input;

        $topicId = $jinput->get('topicid', '', 'NONE');
        $postData = $jinput->post->getArray();
        $inputFilter = CFactory::getInputFilter(true, array('b','u','i','li','ul','ol', 'br', 'div', 'p', 'img', 'a', 'strong', 'em'));
        $groupid = $jinput->request->get('groupid', '', 'INT');

        $my = CFactory::getUser();
        $mainframe = JFactory::getApplication();
        $groupsModel = $this->getModel('groups');
        $group = JTable::getInstance('Group', 'CTable');
        $group->load($groupid);

        $discussion->bind($postData);

        //CFactory::load( 'helpers' , 'owner' );

        $creator = CFactory::getUser($discussion->creator);

        if ($my->id != $creator->id && !empty($discussion->creator) && !$groupsModel->isAdmin($my->id, $discussion->groupid) && !COwnerHelper::isCommunityAdmin()) {
            $mainframe->enqueueMessage(JText::_('COM_COMMUNITY_ACCESS_FORBIDDEN'), 'error');
            return false;
        }

        $isNew = is_null($discussion->id) || !$discussion->id ? true : false;

        if ($isNew) {
            $discussion->creator = $my->id;
        }

        $now = new JDate();

        $discussion->groupid = $groupid;
        $discussion->created = isset($discussion->created) && $discussion->created != '' ? $discussion->created : $now->toSql();
        $discussion->lastreplied = (isset($discussion->lastreplied)) ? $discussion->lastreplied : $discussion->created;
        $discussion->message = $jinput->post->get('message', '', 'RAW');
        $discussion->message = $inputFilter->clean($discussion->message);

        // @rule: do not allow html tags in the title
        $discussion->title = strip_tags($discussion->title);

        //CFactory::load( 'libraries' , 'apps' );
        $appsLib = CAppPlugins::getInstance();
        $saveSuccess = $appsLib->triggerEvent('onFormSave', array('jsform-groups-discussionform'));
        $validated = true;

        if (empty($saveSuccess) || !in_array(false, $saveSuccess)) {
            $config = CFactory::getConfig();

            // @rule: Spam checks
            if ($config->get('antispam_akismet_discussions')) {
                //CFactory::load( 'libraries' , 'spamfilter' );

                $filter = CSpamFilter::getFilter();
                $filter->setAuthor($my->getDisplayName());
                $filter->setMessage($discussion->title . ' ' . $discussion->message);
                $filter->setEmail($my->email);
                $filter->setURL(CRoute::_('index.php?option=com_community&view=groups&task=viewgroup&groupid=' . $group->id));
                $filter->setType('message');
                $filter->setIP($_SERVER['REMOTE_ADDR']);

                if ($filter->isSpam()) {
                    $validated = false;
                    $mainframe->enqueueMessage(JText::_('COM_COMMUNITY_DISCUSSIONS_MARKED_SPAM'), 'error');
                }
            }

            if (empty($discussion->title)) {
                $validated = false;
                $mainframe->enqueueMessage(JText::_('COM_COMMUNITY_GROUPS_DISCUSSION_TITLE_EMPTY'), 'error');
            }

            if (empty($discussion->message)) {
                $validated = false;
                $mainframe->enqueueMessage(JText::_('COM_COMMUNITY_GROUPS_DISCUSSION_BODY_EMPTY'), 'error');
            }

            if ($validated) {
                //CFactory::load( 'models' , 'discussions' );

                $params = new CParameter('');
                $params->set('filepermission-member', $jinput->getInt('filepermission-member', 0));
                //fetch url if there is any
                if (( preg_match("/\b(?:(?:https?|ftp):\/\/|www\.)[-a-z0-9+&@#\/%?=~_|!:,.;]*[-a-z0-9+&@#\/%=~_|]/i", $discussion->message))) {

                    $graphObject = CParsers::linkFetch($discussion->message);
                    if ($graphObject){
                        $graphObject->merge($params);
                        $discussion->params = $graphObject->toString();
                    }
                }else{
                    $discussion->params = $params->toString();
                }

                $discussion->store();

                if ($isNew) {
                    $group = JTable::getInstance('Group', 'CTable');
                    $group->load($groupid);

                    // Add logging.
                    $url = CRoute::_('index.php?option=com_community&view=groups&task=viewgroup&groupid=' . $groupid);


                    $act = new stdClass();
                    $act->cmd = 'group.discussion.create';
                    $act->actor = $my->id;
                    $act->target = 0;
                    $act->title = ''; //JText::sprintf('COM_COMMUNITY_GROUPS_NEW_GROUP_DISCUSSION' , '{group_url}' , $group->name );
                    $act->content = $discussion->message;
                    $act->app = 'groups.discussion';
                    $act->cid = $discussion->id;
                    $act->groupid = $group->id;
                    $act->group_access = $group->approvals;

                    $act->like_id = CActivities::LIKE_SELF;
                    $act->comment_id = CActivities::COMMENT_SELF;
                    $act->like_type = 'groups.discussion';
                    $act->comment_type = 'groups.discussion';

                    $params = new CParameter('');
                    $params->set('action', 'group.discussion.create');
                    $params->set('topic_id', $discussion->id);
                    $params->set('topic', $discussion->title);
                    $params->set('group_url', 'index.php?option=com_community&view=groups&task=viewgroup&groupid=' . $group->id);
                    $params->set('topic_url', 'index.php?option=com_community&view=groups&task=viewdiscussion&groupid=' . $group->id . '&topicid=' . $discussion->id);

                    $activity = CActivityStream::add($act, $params->toString());

                    $hashtags = CContentHelper::getHashTags($discussion->message); //find the hashtags in the discussion message if there is any
                    //$oldHashtags = CContentHelper::getHashTags($activity->title); //old hashtag from the prebious message or title if there is any

                    //$removeTags = array_diff($oldHashtags, $hashtags); // this are the tags need to be removed
                    //$addTags = array_diff($hashtags, $oldHashtags); // tags that need to be added
                    // add new tags if there's any
                    if(count($hashtags)){
                        $hashtagModel = CFactory::getModel('hashtags');
                        foreach($hashtags as $tag){
                            $hashtagModel->addActivityHashtag($tag, $activity->id);
                        }
                    }

                    //@rule: Add notification for group members whenever a new discussion created.
                    $config = CFactory::getConfig();

                    if ($config->get('groupdiscussnotification') == 1) {
                        $model = $this->getModel('groups');
                        $members = $model->getMembers($groupid, null);
                        $admins = $model->getAdmins($groupid, null);

                        $membersArray = array();

                        foreach ($members as $row) {
                            $membersArray[] = $row->id;
                        }

                        foreach ($admins as $row) {
                            $membersArray[] = $row->id;
                        }
                        unset($members);
                        unset($admins);

                        // Add notification
                        //CFactory::load( 'libraries' , 'notification' );

                        $params = new CParameter('');
                        $params->set('url', 'index.php?option=com_community&view=groups&task=viewdiscussion&groupid=' . $group->id . '&topicid=' . $discussion->id);
                        $params->set('group', $group->name);
                        $params->set('group_url', 'index.php?option=com_community&view=groups&task=viewgroup&groupid=' . $group->id);
                        $params->set('discussion', $discussion->title);
                        $params->set('discussion_url', 'index.php?option=com_community&view=groups&task=viewdiscussion&groupid=' . $group->id . '&topicid=' . $discussion->id);
                        $params->set('user', $my->getDisplayName());
                        $params->set('subject', $discussion->title);
                        $params->set('message', $discussion->message);

                        CNotificationLibrary::addMultiple('groups_create_discussion', $discussion->creator, $membersArray, JText::sprintf('COM_COMMUNITY_NEW_DISCUSSION_NOTIFICATION_EMAIL_SUBJECT', $group->name), '', 'groups.discussion', $params);
                    }
                }

                //add user points
                //CFactory::load( 'libraries' , 'userpoints' );
                CUserPoints::assignPoint('group.discussion.create');
            }
        } else {
            $validated = false;
        }

        return $validated;
    }

    public function adddiscussion() {
        $mainframe = JFactory::getApplication();
        $jinput = $mainframe->input;
        $document = JFactory::getDocument();
        $viewType = $document->getType();
        $viewName = $jinput->get('view', $this->getName());
        $view = $this->getView($viewName, '', $viewType);
        $my = CFactory::getUser();
        $groupid = $jinput->get('groupid', '', 'INT');
        $groupsModel = $this->getModel('groups');
        $group = JTable::getInstance('Group', 'CTable');
        $group->load($groupid);

        $config = CFactory::getConfig();

        // Check if the user is banned
        $isBanned = $group->isBanned($my->id);

        if ($my->id == 0) {
            return $this->blockUnregister();
        }


        $config = CFactory::getConfig();

        if (!$config->get('creatediscussion') || (!$group->isMember($my->id) || $isBanned) && !COwnerHelper::isCommunityAdmin()) {
            echo $view->noAccess();
            return;
        }

        $discussion = JTable::getInstance('Discussion', 'CTable');

        if ($jinput->getMethod() == 'POST') {
            JSession::checkToken() or jexit(JText::_('COM_COMMUNITY_INVALID_TOKEN'));

            if ($this->_saveDiscussion($discussion) !== false) {
                $redirectUrl = CRoute::_('index.php?option=com_community&view=groups&task=viewdiscussion&topicid=' . $discussion->id . '&groupid=' . $groupid, false);
                $this->cacheClean(array(COMMUNITY_CACHE_TAG_GROUPS, COMMUNITY_CACHE_TAG_ACTIVITIES, COMMUNITY_CACHE_TAG_GROUPS_DETAIL));
                $mainframe->redirect($redirectUrl, JText::_('COM_COMMUNITY_GROUPS_DISCUSSION_CREATE_SUCCESS'));
                exit;
            }
        }

        echo $view->get(__FUNCTION__, $discussion);
    }

    /**
     * Show discussion
     */
    public function viewdiscussion() {
        $config = CFactory::getConfig();
        if (!$config->get('enablegroups')) {
            echo JText::_('COM_COMMUNITY_GROUPS_DISABLE');
            return;
        }

        $this->renderView(__FUNCTION__);
    }

    /**
     * Show Invite
     */
    public function invitefriends() {
        $mainframe = JFactory::getApplication();
        $jinput = $mainframe->input;

        $document = JFactory::getDocument();
        $viewType = $document->getType();
        $viewName = $jinput->get('view', $this->getName());
        $view = $this->getView($viewName, '', $viewType);

        $my = CFactory::getUser();
        $invited = $jinput->post->get('invite-list', '', 'NONE');
        $inviteMessage = $jinput->post->get('invite-message', '', 'STRING');
        $groupId = $jinput->request->get('groupid', '', 'INT');
        $groupsModel = $this->getModel('groups');
        $group = JTable::getInstance('Group', 'CTable');
        $group->load($groupId);

        // Check if the user is banned
        $isBanned = $group->isBanned($my->id);

        if ($my->id == 0) {
            return $this->blockUnregister();
        }

        if ((!$group->isMember($my->id) || $isBanned) && !COwnerHelper::isCommunityAdmin()) {
            echo JText::_('COM_COMMUNITY_ACCESS_FORBIDDEN');
            return;
        }

        if ($jinput->getMethod() == 'POST') {
            JSession::checkToken() or jexit(JText::_('COM_COMMUNITY_INVALID_TOKEN'));
            if (!empty($invited)) {
                $mainframe = JFactory::getApplication();
                $groupsModel = CFactory::getModel('Groups');
                $group = JTable::getInstance('Group', 'CTable');
                $group->load($groupId);


                foreach ($invited as $invitedUserId) {
                    $groupInvite = JTable::getInstance('GroupInvite', 'CTable');
                    $groupInvite->groupid = $group->id;
                    $groupInvite->userid = $invitedUserId;
                    $groupInvite->creator = $my->id;

                    $groupInvite->store();
                }
                // Add notification
                //CFactory::load( 'libraries' , 'notification' );

                $params = new CParameter('');
                $params->set('url', 'index.php?option=com_community&view=groups&task=viewgroup&groupid=' . $group->id);
                $params->set('groupname', $group->name);
                $params->set('message', $inviteMessage);
                $params->set('group', $group->name);
                $params->set('group_url', 'index.php?option=com_community&view=groups&task=viewgroup&groupid=' . $group->id);

                CNotificationLibrary::add('groups_invite', $my->id, $invited, JText::sprintf('COM_COMMUNITY_GROUPS_JOIN_INVITATION_MESSAGE'), '', 'groups.invite', $params);

                $mainframe->redirect(CRoute::_('index.php?option=com_community&view=groups&task=viewgroup&groupid=' . $group->id, false), JText::_('COM_COMMUNITY_GROUPS_INVITATION_SEND_MESSAGE'));
            } else {
                $view->addWarning(JText::_('COM_COMMUNITY_INVITE_NEED_AT_LEAST_1_FRIEND'));
            }
        }
        echo $view->get(__FUNCTION__);
    }

    public function editDiscussion() {
        $mainframe = JFactory::getApplication();
        $jinput = $mainframe->input;
        $topicId = $jinput->get('topicid', '', 'INT');

        $discussion = JTable::getInstance('Discussion', 'CTable');
        $discussion->load($topicId);

        $groupId = $jinput->get('groupid', '', 'INT');
        $groupsModel = CFactory::getModel('Groups');
        $my = CFactory::getUser();
        $creator = CFactory::getUser($discussion->creator);
        $isGroupAdmin = $groupsModel->isAdmin($my->id, $discussion->groupid);

        if ($my->id == 0) {
            return $this->blockUserAccess();
        }

        // Make sure this user is a member of this group
        if ($my->id != $creator->id && !$isGroupAdmin && !COwnerHelper::isCommunityAdmin()) {

            $mainframe->enqueueMessage(JText::_('COM_COMMUNITY_ACCESS_FORBIDDEN'), 'error');
        } else {
            if ($jinput->getMethod() == 'POST') {
                JSession::checkToken() or jexit(JText::_('COM_COMMUNITY_INVALID_TOKEN'));

                if ($this->_saveDiscussion($discussion) !== false) {
                    $redirectUrl = CRoute::_('index.php?option=com_community&view=groups&task=viewdiscussion&topicid=' . $discussion->id . '&groupid=' . $groupId, false);

                    $mainframe = JFactory::getApplication();
                    $mainframe->redirect($redirectUrl, JText::_('COM_COMMUNITY_GROUPS_DISCUSSION_UPDATED'));
                }
            }
            $this->renderView(__FUNCTION__, $discussion);
        }
    }

    public function editNews() {
        $mainframe = JFactory::getApplication();
        $jinput = $mainframe->input;
        $my = CFactory::getUser();

        if ($my->id == 0) {
            return $this->blockUnregister();
        }

        // Load necessary models
        $groupsModel = CFactory::getModel('groups');
        //CFactory::load( 'models' , 'bulletins' );

        $groupId = $jinput->request->getInt('groupid', '');

        $group = JTable::getInstance('Group', 'CTable');
        $group->load($groupId);
        //CFactory::load( 'helpers' , 'owner' );
        // Ensure user has really the privilege to view this page.
        if ($my->id != $group->ownerid && !COwnerHelper::isCommunityAdmin() && !$groupsModel->isAdmin($my->id, $groupId)) {
            echo JText::_('COM_COMMUNITY_PERMISSION_DENIED_WARNING');
            return;
        }

        if ($jinput->getMethod() == 'POST') {
            JSession::checkToken() or jexit(JText::_('COM_COMMUNITY_INVALID_TOKEN'));
            // Get variables from query
            $bulletin = JTable::getInstance('Bulletin', 'CTable');
            $bulletinId = $jinput->post->get('bulletinid', '', 'INT');

            $bulletin->load($bulletinId);
            $bulletin->message = $jinput->post->get('message', '', 'RAW');
            $bulletin->title = $jinput->post->get('title', '', 'string');
            // Groupid should never be empty. Add some assert codes here
            CError::assert($groupId, '', '!empty', __FILE__, __LINE__);
            CError::assert($bulletinId, '', '!empty', __FILE__, __LINE__);

            if (empty($bulletin->message)) {
                $mainframe->redirect(CRoute::_('index.php?option=com_community&view=groups&task=viewbulletin&bulletinid=' . $bulletinId . '&groupid=' . $groupId, false), JText::_('COM_COMMUNITY_GROUPS_BULLETIN_BODY_EMPTY'));
            }
            $params = new CParameter('');
            $params->set('filepermission-member', $jinput->getInt('filepermission-member', 0));
            $bulletin->params = $params->toString();

            $bulletin->store();
            $mainframe->redirect(CRoute::_('index.php?option=com_community&view=groups&task=viewbulletin&bulletinid=' . $bulletinId . '&groupid=' . $groupId, false), JText::_('COM_COMMUNITY_BULLETIN_UPDATED'));
        }
    }

    /**
     * Method to add a new discussion
     * */
    public function addNews() {
        $mainframe = JFactory::getApplication();
        $jinput = $mainframe->input;

        $my = CFactory::getUser();

        if ($my->id == 0) {
            return $this->blockUnregister();
        }

        $document = JFactory::getDocument();
        $viewType = $document->getType();
        $viewName = $jinput->get('view', $this->getName());
        $view = $this->getView($viewName, '', $viewType);

        // Load necessary models
        $groupsModel = CFactory::getModel('groups');
        $groupId = $jinput->request->get('groupid', '', 'INT');
        $config = CFactory::getConfig();
        if (!$config->get('createannouncement')) {
            $mainframe->redirect(CRoute::_('index.php?option=com_community&view=groups&task=viewgroup&groupid=' . $groupId));
        }

        $group = JTable::getInstance('Group', 'CTable');
        $group->load($groupId);

        // Ensure user has really the privilege to view this page.
        if ($my->id != $group->ownerid && !COwnerHelper::isCommunityAdmin() && !$groupsModel->isAdmin($my->id, $groupId)) {
            echo $view->noAccess();
            return;
        }

        $title = '';
        $message = '';

        if ($jinput->getMethod() == 'POST') {
            JSession::checkToken() or jexit(JText::_('COM_COMMUNITY_INVALID_TOKEN'));

            // Get variables from query
            $bulletin = JTable::getInstance('Bulletin', 'CTable');
            $bulletin->title = $jinput->post->get('title', '', 'STRING');
            $bulletin->message = $jinput->post->get('message', '', 'RAW');

            // Groupid should never be empty. Add some assert codes here
            CError::assert($groupId, '', '!empty', __FILE__, __LINE__);

            $validated = true;

            if (empty($bulletin->title)) {
                $validated = false;
                $mainframe->enqueueMessage(JText::_('COM_COMMUNITY_GROUPS_BULLETIN_EMPTY'), 'notice');
            }

            if (empty($bulletin->message)) {
                $validated = false;
                $mainframe->enqueueMessage(JText::_('COM_COMMUNITY_GROUPS_BULLETIN_BODY_EMPTY'), 'notice');
            }

            if ($validated) {
                $params = new CParameter('');
                $bulletin->groupid = $groupId;
                $bulletin->date = gmdate('Y-m-d H:i:s');
                $bulletin->created_by = $my->id;

                // @todo: Add moderators for the groups.
                // Since now is default to the admin, default to publish the news
                $bulletin->published = 1;
                $params->set('filepermission-member', $jinput->getInt('filepermission-member', 0));
                //fetch url if there is any
                if (( preg_match("/\b(?:(?:https?|ftp):\/\/|www\.)[-a-z0-9+&@#\/%?=~_|!:,.;]*[-a-z0-9+&@#\/%=~_|]/i", $bulletin->message))) {

                    $graphObject = CParsers::linkFetch($bulletin->message);
                    if ($graphObject){
                        $graphObject->merge($params);
                        $bulletin->params = $graphObject->toString();
                    }
                }else{
                    $bulletin->params = $params->toString();
                }
                $bulletin->store();

                // Send notification to all user
                $model = $this->getModel('groups');
                $memberCount = $model->getMembersCount($groupId);
                $members = $model->getMembers($groupId, $memberCount, true, false, SHOW_GROUP_ADMIN);

                $membersArray = array();

                foreach ($members as $row) {
                    $membersArray[] = $row->id;
                }
                unset($members);

                // Add notification
                //CFactory::load( 'libraries' , 'notification' );


                $params->set('url', 'index.php?option=com_community&view=groups&task=viewgroup&groupid=' . $groupId);
                $params->set('group', $group->name);
                $params->set('group_url', 'index.php?option=com_community&view=groups&task=viewgroup&groupid=' . $groupId);
                $params->set('subject', $bulletin->title);
                $params->set('announcement', $bulletin->title);
                $params->set('announcement_url', 'index.php?option=com_community&view=groups&task=viewbulletin&groupid=' . $group->id . '&bulletinid=' . $bulletin->id);

                CNotificationLibrary::add('groups_create_news', $my->id, $membersArray, JText::sprintf('COM_COMMUNITY_GROUPS_EMAIL_NEW_BULLETIN_SUBJECT'), '', 'groups.bulletin', $params);

                // Add logging to the bulletin
                $url = CRoute::_('index.php?option=com_community&view=groups&task=viewbulletin&groupid=' . $group->id . '&bulletinid=' . $bulletin->id);

                // Add activity logging

                $act = new stdClass();
                $act->cmd = 'group.news.create';
                $act->actor = $my->id;
                $act->target = 0;
                $act->title = ''; //JText::sprintf('COM_COMMUNITY_GROUPS_NEW_GROUP_NEWS' , '{group_url}' , $bulletin->title );
                $act->content = ( $group->approvals == 0 ) ? JString::substr(strip_tags($bulletin->message), 0, 100) : '';
                $act->app = 'groups.bulletin';
                $act->cid = $bulletin->id;
                $act->groupid = $group->id;
                $act->group_access = $group->approvals;

                $act->comment_id = CActivities::COMMENT_SELF;
                $act->comment_type = 'groups.bulletin';
                $act->like_id = CActivities::LIKE_SELF;
                $act->like_type = 'groups.bulletin';

                $params = new CParameter('');
//				$params->set( 'group_url' , 'index.php?option=com_community&view=groups&task=viewgroup&groupid=' . $group->id );
                $params->set('group_url', 'index.php?option=com_community&view=groups&task=viewbulletin&groupid=' . $group->id . '&bulletinid=' . $bulletin->id);


                CActivityStream::add($act, $params->toString());

                //add user points
                CUserPoints::assignPoint('group.news.create');
                $this->cacheClean(array(COMMUNITY_CACHE_TAG_ACTIVITIES, COMMUNITY_CACHE_TAG_GROUPS_DETAIL));
                //$mainframe->redirect( CRoute::_('index.php?option=com_community&view=groups&task=viewgroup&groupid=' . $groupId , false ), JText::_('COM_COMMUNITY_GROUPS_BULLETIN_CREATE_SUCCESS') );
                $mainframe->redirect(CRoute::_('index.php?option=com_community&view=groups&task=viewbulletin&groupid=' . $groupId . '&bulletinid=' . $bulletin->id, false), JText::_('COM_COMMUNITY_GROUPS_BULLETIN_CREATE_SUCCESS'));
            } else {
                echo $view->get(__FUNCTION__, $bulletin);
                return;
            }
        }

        echo $view->get(__FUNCTION__, false);
    }

    public function deleteTopic() {
        $mainframe = JFactory::getApplication();
        $jinput = $mainframe->input;
        //CFactory::load( 'libraries' , 'activities' );
        $my = CFactory::getUser();
        if ($my->id == 0) {
            return $this->blockUnregister();
        }

        $topicid = $jinput->post->get('topicid', '', 'INT');
        $groupid = $jinput->post->get('groupid', '', 'INT');

        if (empty($topicid) || empty($groupid)) {
            echo JText::_('COM_COMMUNITY_INVALID_ID');
            return;
        }

        //CFactory::load( 'helpers' , 'owner' );
        //CFactory::load( 'models' , 'discussions' );

        $groupsModel = CFactory::getModel('groups');
        $wallModel = CFactory::getModel('wall');
        $activityModel = CFactory::getModel('activities');
        $fileModel = CFactory::getModel('files');
        $discussion = JTable::getInstance('Discussion', 'CTable');
        $discussion->load($topicid);

        $group = JTable::getInstance('Group', 'CTable');
        $group->load($groupid);
        $isGroupAdmin = $groupsModel->isAdmin($my->id, $group->id);

        if ($my->id == $discussion->creator || $isGroupAdmin || COwnerHelper::isCommunityAdmin()) {


            if ($discussion->delete()) {
                // Remove the replies to this discussion as well since we no longer need them
                $wallModel->deleteAllChildPosts($topicid, 'discussions');
                // Remove from activity stream
                CActivityStream::remove('groups.discussion', $topicid);
                // Remove Discussion Files
                $fileModel->alldelete($topicid, 'discussion');
                // Assuming all files are deleted, remove the folder if exists
                if(JFolder::exists(JPATH_ROOT . '/' . 'images/files/discussion/'.$topicid)){
                    JFolder::delete(JPATH_ROOT . '/' . 'images/files/discussion/'.$topicid);
                }

                $this->cacheClean(array(COMMUNITY_CACHE_TAG_GROUPS, COMMUNITY_CACHE_TAG_GROUPS_DETAIL, COMMUNITY_CACHE_TAG_ACTIVITIES));
                $mainframe->redirect(CRoute::_('index.php?option=com_community&view=groups&task=viewgroup&groupid=' . $groupid, false), JText::_('COM_COMMUNITY_DISCUSSION_REMOVED'));
            }
        } else {
            $mainframe->redirect(CRoute::_('index.php?option=com_community&view=groups&task=viewgroup&groupid=' . $groupid, false), JText::_('COM_COMMUNITY_GROUPS_DISCUSSION_DELETE_WARNING'));
        }
    }

    public function lockTopic() {
        $mainframe = JFactory::getApplication();
        $jinput = JFactory::getApplication()->input;

        $my = CFactory::getUser();
        if ($my->id == 0) {
            return $this->blockUnregister();
        }

        $topicid = $jinput->post->getInt('topicid', '');
        $groupid = $jinput->post->getInt('groupid', '');

        if (empty($topicid) || empty($groupid)) {
            echo JText::_('COM_COMMUNITY_INVALID_ID');
            return;
        }

        //CFactory::load( 'helpers' , 'owner' );
        //CFactory::load( 'models' , 'discussions' );

        $groupsModel = CFactory::getModel('groups');
        $wallModel = CFactory::getModel('wall');
        $discussion = JTable::getInstance('Discussion', 'CTable');
        $discussion->load($topicid);

        $group = JTable::getInstance('Group', 'CTable');
        $group->load($groupid);
        $isGroupAdmin = $groupsModel->isAdmin($my->id, $group->id);


        if ($my->id == $discussion->creator || $isGroupAdmin || COwnerHelper::isCommunityAdmin()) {
            $lockStatus = $discussion->lock ? false : true;
            $confirmMsg = $lockStatus ? JText::_('COM_COMMUNITY_DISCUSSION_LOCKED') : JText::_('COM_COMMUNITY_DISCUSSION_UNLOCKED');

            if ($discussion->lock($topicid, $lockStatus)) {
                $mainframe->redirect(CRoute::_('index.php?option=com_community&view=groups&task=viewdiscussion&groupid=' . $groupid . '&topicid=' . $topicid, false), $confirmMsg);
            }
        } else {
            $mainframe->redirect(CRoute::_('index.php?option=com_community&view=groups&task=viewdiscussion&groupid=' . $groupid . '&topicid=' . $topicid, false), JText::_('COM_COMMUNITY_NOT_ALLOWED_TO_LOCK_GROUP_TOPIC'));
        }
    }

    public function deleteBulletin() {
        $mainframe = JFactory::getApplication();
        $jinput = JFactory::getApplication()->input;
        $my = CFactory::getUser();

        //CFactory::load( 'libraries' , 'activities' );
        if ($my->id == 0) {
            return $this->blockUnregister();
        }

        $bulletinId = $jinput->post->getInt('bulletinid', '');
        $groupid = $jinput->post->getInt('groupid', '');

        if (empty($bulletinId) || empty($groupid)) {
            echo JText::_('COM_COMMUNITY_INVALID_ID');
            return;
        }

        //CFactory::load( 'helpers' , 'owner' );
        //CFactory::load( 'models' , 'bulletins' );

        $groupsModel = CFactory::getModel('groups');
        $bulletin = JTable::getInstance('Bulletin', 'CTable');
        $group = JTable::getInstance('Group', 'CTable');
        $group->load($groupid);

        $fileModel = CFactory::getModel('files');

        if ($groupsModel->isAdmin($my->id, $group->id) || COwnerHelper::isCommunityAdmin()) {
            $bulletin->load($bulletinId);

            if ($bulletin->delete()) {

                //add user points
                //CFactory::load( 'libraries' , 'userpoints' );
                CUserPoints::assignPoint('group.news.remove');
                CActivityStream::remove('groups.bulletin', $bulletinId);

                // Remove Bulletin Files
                $fileModel->alldelete($bulletinId, 'bulletin');

                $this->cacheClean(array(COMMUNITY_CACHE_TAG_ACTIVITIES, COMMUNITY_CACHE_TAG_GROUPS_DETAIL));
                $mainframe->redirect(CRoute::_('index.php?option=com_community&view=groups&task=viewgroup&groupid=' . $groupid, false), JText::_('COM_COMMUNITY_BULLETIN_REMOVED'));
            }
        } else {
            $mainframe->redirect(CRoute::_('index.php?option=com_community&view=groups&task=viewgroup&groupid=' . $groupid, false), JText::_('COM_COMMUNITY_GROUPS_DISCUSSION_DELETE_WARNING'));
        }
    }

    /**
     * Displays send email form and processes the sendmail
     * */
    public function sendmail() {
        $mainframe = JFactory::getApplication();
        $jinput = $mainframe->input;
        $id = $jinput->get('groupid', '', 'INT');
        $message = $jinput->post->get('message', '', 'RAW');
        $title = $jinput->get('title', '', 'STRING');
        $my = CFactory::getUser();

        $group = JTable::getInstance('Group', 'CTable');
        $group->load($id);

        //CFactory::load( 'helpers' , 'owner' );

        if (empty($id) || (!$group->isAdmin($my->id) && !COwnerHelper::isCommunityAdmin() )) {
            echo JText::_('COM_COMMUNITY_ACCESS_FORBIDDEN');
            return;
        }

        if ($jinput->getMethod() == 'POST') {
            // Check for request forgeries
            JSession::checkToken() or jexit(JText::_('COM_COMMUNITY_INVALID_TOKEN'));

            $model = CFactory::getModel('Groups');
            $members = $model->getMembers($group->id, COMMUNITY_GROUPS_NO_LIMIT, COMMUNITY_GROUPS_ONLY_APPROVED, COMMUNITY_GROUPS_NO_RANDOM, COMMUNITY_GROUPS_SHOW_ADMINS);

            $errors = false;

            if (empty($message)) {
                $errors = true;
                $mainframe->enqueueMessage(JText::_('COM_COMMUNITY_INBOX_MESSAGE_REQUIRED'), 'error');
            }

            if (empty($title)) {
                $errors = true;
                $mainframe->enqueueMessage(JText::_('COM_COMMUNITY_TITLE_REQUIRED'), 'error');
            }

            if (!$errors) {
                // Add notification
                //CFactory::load( 'libraries' , 'notification' );
                $emails = array();
                $total = 0;
                foreach ($members as $member) {
                    $total += 1;
                    $user = CFactory::getUser($member->id);
                    $emails[] = $user->id;

                    // Exclude the actor
                    if ($user->id == $my->id) {
                        $total -= 1;
                    }
                }

                $params = new CParameter('');
                $params->set('url', 'index.php?option=com_community&view=groups&task=viewgroup&groupid=' . $group->id);
                $params->set('group', $group->name);
                $params->set('group_url', 'index.php?option=com_community&view=groups&task=viewgroup&groupid=' . $group->id);
                $params->set('email', $title);
                $params->set('title', $title);
                $params->set('message', $message);
                CNotificationLibrary::add('groups_sendmail', $my->id, $emails, JText::sprintf('COM_COMMUNITY_GROUPS_SENDMAIL_SUBJECT'), '', 'groups.sendmail', $params, true, '', JText::sprintf('COM_COMMUNITY_GROUPS_SENDMAIL_NOTIFICATIONS', CRoute::_('index.php?option=com_community&view=groups&task=viewgroup&groupid=' . $group->id),$group->name));

                $mainframe->redirect(CRoute::_('index.php?option=com_community&view=groups&task=viewgroup&groupid=' . $group->id, false), JText::sprintf('COM_COMMUNITY_EMAIL_SENT_TO_GROUP_MEMBERS', $total));
            }
        }

        $this->renderView(__FUNCTION__);
    }

    /*
     * group event name
     * object array
     */

    public function triggerGroupEvents($eventName, &$args, $target = null) {
        CError::assert($args, 'object', 'istype', __FILE__, __LINE__);

        require_once( COMMUNITY_COM_PATH . '/libraries/apps.php' );
        $appsLib = CAppPlugins::getInstance();
        $appsLib->loadApplications();

        $params = array();
        $params[] = $args;

        if (!is_null($target))
            $params[] = $target;

        $appsLib->triggerEvent($eventName, $params);
        return true;
    }

    public function banlist() {
        $mainframe = JFactory::getApplication();
        $jinput = $mainframe->input;

        $data = new stdClass();
        $data->id = $jinput->get->get('groupid', '', 'INT');
        $this->renderView(__FUNCTION__, $data);
    }

    /**
     * Method is used to receive POST requests from specific user
     * that wants to join a group
     * @param $groupId
     * @param string $fastJoin join from discussion page
     */
    public function ajaxJoinGroup($groupId, $fastJoin = 'no') {
        $json = array();
        $objResponse = new JAXResponse();

        $filter = JFilterInput::getInstance();

        $groupId = $filter->clean($groupId, 'int');
        if (empty($fastJoin)) {
            $fastJoin = 'no';
        }
        $fastJoin = $filter->clean($fastJoin, 'string');

        // Add assertion to the group id since it must be specified in the post request
        CError::assert($groupId, '', '!empty', __FILE__, __LINE__);

        // Get the current user's object
        $my = CFactory::getUser();

        if (!$my->authorise('community.join', 'groups.' . $groupId)) {
            return $this->ajaxBlockUnregister();
        }

        // Load necessary tables
        $group = JTable::getInstance('Group', 'CTable');
        $group->load($groupId);

        $groupModel = CFactory::getModel('groups');
        if ($fastJoin == 'yes') {
            $member = $this->_saveMember($groupId);
            $this->cacheClean(array(COMMUNITY_CACHE_TAG_GROUPS, COMMUNITY_CACHE_TAG_ACTIVITIES));

            if ($member->approved) {
                $objResponse->addScriptCall("joms.groups.joinComplete('" . JText::_('COM_COMMUNITY_GROUPS_JOIN_SUCCESS_BUTTON', true) . "'); location.reload(true);");
            } else {
                $objResponse->addScriptCall("joms.groups.joinComplete('" . JText::_('COM_COMMUNITY_GROUPS_JOIN_SUCCESS_BUTTON', true) . "'); location.reload(true);");
                //$objResponse->addScriptCall("joms.jQuery('.group-top').prepend('<div class=\"info\">".JText::_('COM_COMMUNITY_GROUPS_APPROVAL_NEED')."</div>');");
            }
        } else {
            if ($groupModel->isMember($my->id, $groupId)) {
                $json['message'] = JText::_('COM_COMMUNITY_GROUPS_ALREADY_MEMBER');
            } else {
                $member = $this->_saveMember($groupId);
                $this->cacheClean(array(COMMUNITY_CACHE_TAG_GROUPS, COMMUNITY_CACHE_TAG_ACTIVITIES));

                if ($member->approved) {
                    $json['message'] = JText::_('COM_COMMUNITY_GROUPS_JOIN_SUCCESS');
                } else {
                    $json['message'] = JText::_('COM_COMMUNITY_GROUPS_APPROVAL_NEED');

                    $params = new CParameter('');
                    $params->set('group',$group->name);
                    $params->set('url', 'index.php?option=com_community&view=groups&task=viewgroup&groupid=' . $group->id);

                    CNotificationLibrary::add( 'groups_join_request', $my->id, $group->ownerid, JText::sprintf('COM_COMMUNITY_GROUP_JOIN_REQUEST_SUBJECT'), '', 'groups.joinrequest', $params );
                }
            }
        }

        die( json_encode($json) );
    }

}