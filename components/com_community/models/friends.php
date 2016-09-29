<?php
/**
 * @copyright (C) 2013 iJoomla, Inc. - All rights reserved.
 * @license GNU General Public License, version 2 (http://www.gnu.org/licenses/gpl-2.0.html)
 * @author iJoomla.com <webmaster@ijoomla.com>
 * @url https://www.jomsocial.com/license-agreement
 * The PHP code portions are distributed under the GPL license. If not otherwise stated, all images, manuals, cascading style sheets, and included JavaScript *are NOT GPL, and are released under the IJOOMLA Proprietary Use License v1.0
 * More info at https://www.jomsocial.com/license-agreement
 */
defined('_JEXEC') or die ('Restricted access');

require_once (JPATH_ROOT.'/components/com_community/models/models.php');

class CommunityModelFriends extends JCCModel
    implements CLimitsInterface, CNotificationsInterface
{
    var $_data = null;
    var $_profile;
    var $_pagination;

    public function __construct()
    {
        parent::__construct();
        global $option;
        $mainframe	= JFactory::getApplication();
        $jinput 	= $mainframe->input;
        $config = CFactory::getConfig();

        // Get pagination request variables
        $limit = ($config->get('pagination') == 0)? 5 : $config->get('pagination');
        $limitstart = $jinput->request->get('limitstart', 0, 'INT');

        if(empty($limitstart))
        {
            $limitstart = $jinput->get->get('start',0,'INT');
        }

        // In case limit has been changed, adjust it
        $limitstart = ($limit != 0?(floor($limitstart/$limit)*$limit):
            0);

        $this->setState('limit', $limit);
        $this->setState('limitstart', $limitstart);
    }

    /**
     * Deprecated since 1.8
     */
    public function addFriendCount($userId)
    {
        $this->updateFriendCount($userId);
        return $this;
    }

    /**
     * Deprecated since 1.8
     */
    public function substractFriendCount($userId)
    {
        $this->updateFriendCount($userId);
    }

    public function updateFriendCount($userId)
    {
        $db= $this->getDBO();
        $count = $this->getFriendsCount($userId);

        $user = CFactory::getUser( $userId );
        $user->updateFriendList(true);
        $user->save();

        $query =    'UPDATE '.$db->quoteName('#__community_users')
            .'SET '.$db->quoteName('friendcount').'='.$db->Quote($count)
            .'WHERE '.$db->quoteName('userid').'='.$db->Quote($userId);

        $db->setQuery($query);
        try {
            $db->execute();
        } catch (Exception $e) {
            JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
        }

        return $this;
    }

    public function & getData()
    {
        if ( empty($this->_data))
        {
            $this->_data = array ();

            $this->_data['name'] = 'Testing';
            $this->_data['status'] = 'Alive';
        }

        return $this->_data;
    }

    public function &getFiltered($wheres = array (), $limit = null)
    {
        $db= $this->getDBO();

        $wheres[] = 'block = 0';
        $limit = '';
        if(!empty($limit) ){
            $limit = " LIMIT 0, {$limit} ";
        }

        $query = "SELECT *"
            .' FROM '. $db->quoteName('#__users')
            .' WHERE '.implode(' AND ', $wheres)
            .' ORDER BY '. $db->quoteName('id').' DESC '. $limit;

        $db->setQuery($query);

        try {
            $result = $db->loadObjectList();
        } catch (Exception $e) {
            JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
        }

        // preload all users
        $uids = array();
        foreach($result as $m)
        {
            $uids[] = $m->id;
        }

        CFactory::loadUsers($uids);
        $cusers = array();
        for ($i = 0; $i < count($result); $i++)
        {

            $usr = CFactory::getUser($result[$i]->id);
            $cusers[] = $usr;
        }

        return $cusers;
    }

    /**
     * Search based on current config
     * @param $query
     * @param array $rules
     * @return array
     */
    public function searchFriend($query, $rules = array()) {
        $config = CFactory::getConfig();
        $my = CFactory::getUser();
        $nameField = $config->getString('displayname');
        $data = array();

        $myFriends = $my->getFriendIds();
        $allowSelf = true; // to allow the own user in the list

        /*
         * type of rules,
         * public = friends only and whoever is in the id
         * private = only those who is in the id array
         */
        if(count($rules) > 1){
            switch($rules['case']) {
                case 'all-friends':
                    //return everyone, full list
                    // do nothing since everyone returned is in full list
                    $myFriends = $my->getFriendIds();
                    break;
                case 'private-message-tag':
                    // only return active participant in the conversation
                    $msgModel =  CFactory::getModel('inbox');
                    $membersIds = $msgModel->getParticipantsID( $rules['message_id'] , $my->id);
                    $myFriends = $membersIds;
                    break;
                case 'video-comment-tag':
                    //this is a video and we should return a list of user who has been tagged in the video
                    $tagModel = CFactory::getModel('videotagging');
                    $taggedList	= $tagModel->getTaggedList( $rules['video_id'] );

                    $membersIds = array();
                    foreach($taggedList as $member){
                        $membersIds[] = $member->userid;
                    }

                    //for commentors
                    $members = CWallLibrary::getWallUser($rules['video_id'], 'videos');
                    $membersIds = array_merge($membersIds, array_map('current', $members));

                    //we merge people who has been tagged in the video, commented and friends
                    $myFriends = array_merge($myFriends,$membersIds);
                    break;

                case 'photo-comment-tag':
                    $tagModel = CFactory::getModel('phototagging');
                    $taggedList	= $tagModel->getTaggedList( $rules['photo_id'] );

                    $membersIds = array();

                    foreach($taggedList as $member){
                        $membersIds[] = $member->userid;
                    }

                    // this is a photo comment tagging rules, not the photo tagging rules, so, include user who has been tagged in photo and whoever commented
                    $members = CWallLibrary::getWallUser($rules['photo_id'], 'photos');
                    $membersIds = array_merge($membersIds, array_map('current', $members));

                    //in this case, we merge people who has been tagged in the photo, commented and friends
                    $myFriends = array_merge($myFriends,$membersIds);

                    break;
                case 'photo-tag' :
                    $tagModel = CFactory::getModel('phototagging');
                    $taggedList	= $tagModel->getTaggedList( $rules['photo_id'] );

                    $membersIds = array();

                    foreach($taggedList as $member){
                        $membersIds[] = $member->userid;
                    }

                    //in this case, we remove all the member id who already been tagged
                    $myFriends[] = $my->id;
                    $myFriends = array_diff(array_unique($myFriends),$membersIds);
                    break;
                case 'public-comment' :
                    //we can get the poster as well
                    $table = JTable::getInstance('Activity', 'CTable');
                    $table->load($rules['activity_id']);

                    $wall = CWallLibrary::getWallUser($rules['activity_id']);
                    $memberIds = array_map('current', $wall);
                    $memberIds[] = $table->actor;

                    //in comment, only my friends or those who have commented can be tagged
                    $myFriends = array_unique(array_merge($memberIds, $myFriends));

                    $allowSelf = false;
                    break;
                case 'group':
                    $groupModel = CFactory::getModel('groups');
                    $approvedMembers = $groupModel->getMembers($rules['group_id'], 2000, true, false, true);
                    $membersIds = array();
                    foreach($approvedMembers as $member){
                        $membersIds[] = $member->id;
                    }

                    //now, we need to know if this group is private or not
                    $group = JTable::getInstance('Group', 'CTable');
                    $group->load($rules['group_id']);

                    if($group->approvals){
                        // can only tag members
                        $myFriends = $membersIds;
                    }else{
                        // can tag friends and members of the group
                        $myFriends = array_unique(array_merge($membersIds, $myFriends));
                    }

                    $allowSelf = false;
                    break;
                case 'discussion' :
                    //group discussion

                    // Load models
                    $discussion = JTable::getInstance('Discussion', 'CTable');
                    $discussion->load($rules['discussion_id']);

                    $groupModel = CFactory::getModel('groups');
                    $approvedMembers = $groupModel->getMembers($discussion->groupid, 2000, true, false, true);
                    $membersIds = array();
                    foreach($approvedMembers as $member){
                        $membersIds[] = $member->id;
                    }

                    //now, we need to know if this group is private or not
                    $group = JTable::getInstance('Group', 'CTable');
                    $group->load($discussion->groupid);

                    if($group->approvals){
                        // can only tag members
                        $myFriends = $membersIds;
                    }else{
                        // can tag friends and members of the group
                        $myFriends = array_unique(array_merge($membersIds, $myFriends));
                    }

                    $allowSelf = false;
                    break;
                case 'event' :
                    $event = JTable::getInstance('Event', 'CTable');
                    $event->load($rules['event_id']);
                    $eventMembers = $event->getMembers(COMMUNITY_EVENT_STATUS_ATTEND, 0, CC_RANDOMIZE);

                    $membersIds = array();
                    foreach ($eventMembers as $member) {
                        $membersIds[] = $member->id;
                    }

                    if($event->permission == 1){
                        // can only tag members
                        $myFriends = $membersIds;
                    }else{
                        $myFriends = array_unique(array_merge($membersIds, $myFriends));
                    }

                    $allowSelf = false;
                    break;
                case 'album' :
                    //able to tag any friends.

                    //for commentors
                    $membersIds = array();
                    $members = CWallLibrary::getWallUser($rules['album_id'], 'albums');
                    $membersIds = array_merge($membersIds, array_map('current', $members));

                    //we merge people who has been tagged in the video, commented and friends
                    $myFriends = array_merge($myFriends,$membersIds);
                    break;
            }
        }

        if (isset($rules['case']) && $rules['case'] != '') {
            //we always make sure that we load our own friends only.
            $result = array();
            $friends = array_merge(
                ($rules['case'] == 'group') || ($rules['case'] == 'event') ? array() : $my->getFriendIds(),
                array($my->id),
                $myFriends
            ); // add our own id in the list as well

            if (!$allowSelf) {
                //remove self tagging if needed
                $friends = array_diff($friends, array($my->id));
            }
            $friends = array_unique($friends);

            foreach ($friends as $friend) {
                $usr = CFactory::getUser($friend);
                if(!$usr->isBlocked()) {
                    $result[] = $usr;
                }
            }
        }

        return $result;
    }

    /**
     * Search for people
     * @param query	string	people's name to seach for
     */
    public function searchPeople($query)
    {
        $db= $this->getDBO();
        $filter = array ();
        $strict = true;
        $regex = $strict?
            '/^([.0-9a-z_-]+)@(([0-9a-z-]+\.)+[0-9a-z]{2,4})$/i':
            '/^([*+!.&#$ï¿½\'\\%\/0-9a-z^_`{}=?~:-]+)@(([0-9a-z-]+\.)+[0-9a-z]{2,4})$/i'
        ;
        if (preg_match($regex, JString::trim($query), $matches))
        {
            $query = array ($matches[1], $matches[2]);
            $filter = array ("`email`='{$matches[1]}@{$matches[2]}'");
        }
        else
        {
            $filter = array ($db->quoteName('username') ."=". $db->Quote($query));
        }

        $result = $this->getFiltered($filter);

        // for each one of these people, we need to load their relationship with
        // our current user
        // 		if(!empty($result)){
        // 			for($i = 0; $i = $result; $i++){
        //
        // 			}
        // 		}

        return $result;
    }


    /**
     * Save a friend request to stranger. Stranger will have to approve
     * @param	$id		int		stranger user id
     * @param   $fromid int     owner's id
     */
    public function addFriend($id, $fromid, $msg='', $status = 0)
    {
        $my = CFactory::getUser();
        $db= $this->getDBO();
        $wheres[] = 'block = 0';

        if ($my->id == $id)
        {
            JFactory::getApplication()->enqueueMessage(JText::_('COM_COMMUNITY_FRIEND_ADD_YOURSELF_ERROR'), 'error');
        }

        $date	= JDate::getInstance(); //get the time without any offset!
        $query	= 'INSERT INTO '. $db->quoteName('#__community_connection')
            .' SET ' . $db->quoteName('connect_from').' = '.$db->Quote($fromid)
            . ', '. $db->quoteName('connect_to').' = '.$db->Quote($id)
            . ', '. $db->quoteName('status').' = '. $db->Quote($status)
            . ', '. $db->quoteName('created').' = ' . $db->Quote($date->toSql())
            . ', '. $db->quoteName('msg').' = ' . $db->Quote($msg);

        $db->setQuery($query);
        try {
            $db->execute();
        } catch (Exception $e) {
            JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
        }

        return $this;
    }

    /**
     * Send a friend request to stranger. Stranger will have to approve
     * @param	$id		int		stranger user id
     */
    public function addFriendRequest($id, $fromid)
    {
        $my = CFactory::getUser();
        $db= $this->getDBO();
        $wheres[] = 'block = 0';

        if ($my->id == $id)
        {
            JFactory::getApplication()->enqueueMessage(JText::_('COM_COMMUNITY_FRIEND_ADD_YOURSELF_ERROR'), 'error');
        }

        $date	= JDate::getInstance(); //get the time without any offset!

        $query = 'INSERT INTO '. $db->quoteName('#__community_connection')
            . ' SET '. $db->quoteName('connect_from').'='.$db->Quote($fromid)
            . ', '. $db->quoteName('connect_to').'='.$db->Quote($id)
            . ', '. $db->quoteName('status').'='. $db->Quote(1)
            . ', '. $db->quoteName('created').' = ' . $db->Quote($date->toSql());

        $db->setQuery($query);
        try {
            $db->execute();
        } catch (Exception $e) {
            JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
        }

        //@todo escape code
        $query = 'INSERT INTO '. $db->quoteName('#__community_connection')
            .' SET '. $db->quoteName('connect_from').'='.$db->Quote($id)
            . ', '. $db->quoteName('connect_to').'='.$db->Quote($fromid)
            . ', '. $db->quoteName('status').'='. $db->Quote(1)
            . ', '. $db->quoteName('created').' = ' . $db->Quote($date->toSql());

        $db->setQuery($query);
        try {
            $db->execute();
        } catch (Exception $e) {
            JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
        }

        return $this;
    }


    /**
     *@param $id int user id
     *@param $groupid int group id
     *
     */
    public function deleteFriendGroup($id, $groupid)
    {
        $db= $this->getDBO();
        $query = 'DELETE FROM '. $db->quoteName('#__community_friendlist')
            .' WHERE '. $db->quoteName('user_id').'='.$db->Quote($id)
            .' AND '. $db->quoteName('group_id').'='.$db->Quote($groupid);
        $db->setQuery($query);
        try {
            $db->execute();
        } catch (Exception $e) {
            JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
        }
    }


    /**
     *@param $id int user id
     *@param $groupid int group id
     *
     */
    public function deleteFriendsTag($id, $groupid)
    {
        $db= $this->getDBO();
        $query = 'DELETE FROM '. $db->quoteName('#__community_friendgroup')
            .' WHERE '. $db->quoteName('user_id').'='.$db->Quote($id)
            .' AND '. $db->quoteName('group_id').'='.$db->Quote($groupid);
        $db->setQuery($query);
        try {
            $db->execute();
        } catch (Exception $e) {
            JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
        }

        return true;
    }


    /**
     * Delete sent request
     */
    public function deleteSentRequest($from, $to)
    {
        $db= $this->getDBO();

        $query = 'DELETE FROM '. $db->quoteName('#__community_connection')
            .' WHERE '. $db->quoteName('connect_from').' = '.$db->Quote($from)
            .' AND '. $db->quoteName('connect_to').' = '.$db->Quote($to)
            .' AND '. $db->quoteName('status').' = '. $db->Quote(0);

        $db->setQuery($query);
        try {
            $db->execute();
        } catch (Exception $e) {
            JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
        }

        return true;
    }

    /**
     *delete friend connection
     *@param $conn_from int user_id should use CFactory::getUser() id
     *@param $conn_to int user_id
     *@return true when delete success
     */

    public function deleteFriend($conn_from, $conn_to)
    {
        $db= $this->getDBO();
        //1- check connection exist or not
        $query = 'SELECT * FROM '. $db->quoteName('#__community_connection')
            .' WHERE '. $db->quoteName('connect_from').'= '.$db->Quote($conn_from)
            .' AND '. $db->quoteName('connect_to').' = '.$db->Quote($conn_to)
            .' AND '. $db->quoteName('status').'='. $db->Quote(1);

        $db->setQuery($query);

        try {
            $rows1 = $db->loadObject();
        } catch (Exception $e) {
            JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
        }

        $query = 'SELECT * FROM '. $db->quoteName('#__community_connection')
            .' WHERE '. $db->quoteName('connect_from').' = '.$db->Quote($conn_to)
            .' AND '. $db->quoteName('connect_to').' = '.$db->Quote($conn_from)
            .' AND '. $db->quoteName('status').'='. $db->Quote(1);

        $db->setQuery($query);

        try {
            $rows2 = $db->loadObject();
        } catch (Exception $e) {
            JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
        }

        //@todo avoid sql injection..
        //2- delete connection
        if (count($rows1) > 0 && count($rows2) > 0)
        {
            $query = 'DELETE FROM '. $db->quoteName('#__community_connection')
                .' WHERE '. $db->quoteName('connection_id').' in ('.$db->Quote($rows1->connection_id).','.$db->Quote($rows2->connection_id).')';
            $db->setQuery($query);
            try {
                $db->execute();
            } catch (Exception $e) {
                JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
            }

            //@todo remove friend's tag too..

            return true;
        }

    }

    /**
     *Retrieve friend assigned tag
     *@param $filter array, where statement
     *@return $result obj, records
     */
    public function &getFriendsTag($filter = array ())
    {
        $db= $this->getDBO();
        $query = 'SELECT * FROM '. $db->quoteName('#__community_friendgroup');

        $wheres = array ();
        foreach ($filter as $column=>$value)
        {
            $wheres[] = $db->quoteName($column).'='. $db->Quote($value);
        }


        if (count($wheres) > 0)
        {
            $query .= ' WHERE '.implode(' AND ', $wheres);
        }

        $db->setQuery($query);

        try {
            $result = $db->loadObjectList();
        } catch (Exception $e) {
            JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
        }

        return $result;
    }

    /**
     * Return friends with the given tag id
     */
    public function getFriendsWithTag($tagid)
    {
        $db= $this->getDBO();

        // select all frinds with the given group
        // @todo: check and make sure the given tagid belog to current user
        $query = 'SELECT '. $db->quoteName('user_id')
            .' FROM '. $db->quoteName('#__community_friendgroup')
            .' WHERE '. $db->quoteName('group_id').'='.$db->Quote($tagid);
        $db->setQuery($query);
        $result = $db->loadObjectList();

        foreach ($result as $row)
        {
            $userid[] = $row->user_id;
        }

        // With all the list of friends, we now need to load their info
        $userids = implode(',', $userid);
        $where = array ();
        $where[] = $db->quoteName('id') .' IN ($userids)';
        $result = $this->getFiltered($where);

        return $result;
    }

    /**
     *Retrieve friend's tagsname and name
     *@param $user_id int, user id
     *@return $tagNames array, return tag names
     */
    public function getFriendsTagNames($user_id)
    {
        $db= $this->getDBO();


        $query = 'SELECT fg.*,fl.'. $db->quoteName('group_name').',u.'. $db->quoteName('name')
            .' FROM	'. $db->quoteName('#__community_friendgroup').' fg'
            .' join '. $db->quoteName('#__community_friendlist').' fl on (fg.'. $db->quoteName('group_id').'=fl.'. $db->quoteName('group_id').')'
            .' join '. $db->quoteName('#__users').' u on (fg.'. $db->quoteName('user_id').'=u.'. $db->quoteName('id').')'
            .' WHERE fg.'. $db->quoteName('user_id').'='.$db->Quote($user_id);


        $db->setQuery($query);

        try {
            $rows = $db->loadObjectList();
        } catch (Exception $e) {
            JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
        }

        if (count($rows) > 0)
        {
            $tagNames = array();
            foreach ($rows as $row)
            {
                $tagNames[$row->group_id] = $row->group_name;

            }
            return $tagNames;
        }
        else
        {
            return array ();
        }
    }



    /**
     * Get all people what are waiting to get user's approval
     * @param	id	int		userid of the user responsible for approving it
     */
    public function getPending($id)
    {
        if($id == 0)
        {
            // guest obviouly hasn't send any request
            return null;
        }

        $db= $this->getDBO();

        $wheres[] = 'block = 0';

        $limit = $this->getState('limit');
        $limitstart = $this->getState('limitstart');

        $total = $this->countPending($id);

        // Apply pagination
        if ( empty($this->_pagination))
        {
            jimport('joomla.html.pagination');
            $this->_pagination = new JPagination($total, $limitstart, $limit);
        }

        $query = 'SELECT b.*, a.'. $db->quoteName('connection_id').', a.'. $db->quoteName('msg')
            .' FROM '. $db->quoteName('#__community_connection').' as a, '. $db->quoteName('#__users').' as b'
            .' WHERE a.'. $db->quoteName('connect_to').'='.$db->Quote($id)
            .' AND a.'. $db->quoteName('status').'='. $db->Quote(0)
            .' AND a.'. $db->quoteName('connect_from').'=b.'. $db->quoteName('id')
            .' ORDER BY a.'. $db->quoteName('connection_id').' DESC '
            ." LIMIT {$limitstart}, {$limit} ";

        $db->setQuery($query);

        try {
            $result = $db->loadObjectList();
        } catch (Exception $e) {
            JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
        }
        return $result;
    }

    /**
     * Count total pending request.
     **/
    public function countPending($id)
    {
        $db= $this->getDBO();

        $query = "SELECT count(*) "
            .' FROM '. $db->quoteName('#__community_connection').' as a, '. $db->quoteName('#__users').' as b'
            .' WHERE a.'. $db->quoteName('connect_to').'='.$db->Quote($id)
            .' AND a.'. $db->quoteName('status').'='. $db->Quote(0)
            .' AND a.'. $db->quoteName('connect_from').'=b.'. $db->quoteName('id')
            .' ORDER BY a.'. $db->quoteName('connection_id').' DESC ';

        $db->setQuery($query);

        try {
            return $db->loadResult();
        } catch (Exception $e) {
            JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
            return 0;
        }
    }

    /**
     * Lets caller know if the request really belongs to the UserId
     **/
    public function isMyRequest($requestId, $userId)
    {
        $db= $this->getDBO();

        $query = 'SELECT COUNT(*) FROM '
            .$db->quoteName('#__community_connection')
            .'WHERE '.$db->quoteName('connection_id').'='.$db->Quote($requestId).' '
            .'AND '.$db->quoteName('connect_to').'='.$db->Quote($userId);

        $db->setQuery($query);
        try {
            $status = ($db->loadResult() > 0) ? true : false;
        } catch (Exception $e) {
            JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
        }

        return $status;
    }

    /**
     * approve the requested friend connection
     * @param	id 	int		the connection request id
     * @return	true if everything is ok
     */
    public function approveRequest($id)
    {
        $connection = array ();
        $db= $this->getDBO();
        //get connect_from and connect_to
        $query = 'SELECT '. $db->quoteName('connect_from').','. $db->quoteName('connect_to')
            .' FROM '. $db->quoteName('#__community_connection')
            .' WHERE '. $db->quoteName('connection_id').'='.$db->Quote($id);

        $db->setQuery($query);
        $conn = $db->loadObject();

        if (! empty($conn))
        {
            $connect_from = $conn->connect_from;
            $connect_to = $conn->connect_to;

            $connection[] = $connect_from;
            $connection[] = $connect_to;

            //delete connection id
            $query = 'DELETE FROM '. $db->quoteName('#__community_connection')
                .' WHERE '. $db->quoteName('connection_id').'='.$db->Quote($id);

            $db->setQuery($query);
            try {
                $db->execute();
            } catch (Exception $e) {
                JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
            }

            $date	= JDate::getInstance(); //get the time without any offset!
            //do double entry
            //@todo escape code
            $query = 'INSERT INTO '. $db->quoteName('#__community_connection')
                .' SET '. $db->quoteName('connect_from').'='.$db->Quote($connect_from)
                .', '. $db->quoteName('connect_to').'='.$db->Quote($connect_to)
                .', '. $db->quoteName('status').'='. $db->Quote(1)
                .', '. $db->quoteName('created').' = ' . $db->Quote($date->toSql());

            $db->setQuery($query);
            try {
                $db->execute();
            } catch (Exception $e) {
                JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
            }

            //@todo escape code
            $query = 'INSERT INTO '. $db->quoteName('#__community_connection')
                .' SET '. $db->quoteName('connect_from').'='.$db->Quote($connect_to)
                . ', '. $db->quoteName('connect_to').'='.$db->Quote($connect_from)
                . ', '. $db->quoteName('status').'='. $db->Quote(1)
                . ', '. $db->quoteName('created').' = ' . $db->Quote($date->toSql());

            $db->setQuery($query);
            try {
                $db->execute();
            } catch (Exception $e) {
                JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
            }
            return $connection;
        }
        else
        {
            // Return null is null
            return null;
        }
    }


    /**
     * reject the requested friend connection
     * @param	id 	int		the connection request id
     * @return	true if everything is ok
     */
    public function rejectRequest($id)
    {
        $db= $this->getDBO();

        //validating the connection id to avoid injection
        $query = 'SELECT '. $db->quoteName('connect_from').','. $db->quoteName('connect_to')
            .' FROM '. $db->quoteName('#__community_connection')
            .' WHERE '. $db->quoteName('connection_id').' = '.$db->Quote($id);

        $db->setQuery($query);
        $conn = $db->loadObject();

        if (! empty($conn))
        {

            //delete connection id
            $query = 'DELETE FROM '. $db->quoteName('#__community_connection')
                .' WHERE '. $db->quoteName('connection_id').'='.$db->Quote($id);

            $db->setQuery($query);
            try {
                $db->execute();
            } catch (Exception $e) {
                JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
            }

            return true;
        } else
        {
            return false;
        }
    }

    public function getTotalToday($id)
    {
        // guest obviouly hasn't send any request
        if($id == 0)
        {
            return null;
        }

        $db 	=  $this->getDBO();
        $date	= JDate::getInstance();
        $query	= 'SELECT COUNT(*) FROM '
            . $db->quoteName('#__community_connection').' AS a '
            . ' WHERE a.'. $db->quoteName('connect_from').'=' . $db->Quote( $id )
            . ' AND TO_DAYS(' . $db->Quote( $date->toSql( true ) ) . ') - TO_DAYS( DATE_ADD( a.'. $db->quoteName('created').' , INTERVAL ' . $date->getOffset() . ' HOUR ) ) = 0 ';
        $db->setQuery( $query );

        try {
            $total = $db->loadResult();
        } catch (Exception $e) {
            JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
        }

        return $total;
    }

    /**
     * Get all request that the user has send but not yet approved
     */
    public function getSentRequest($id)
    {
        if($id == 0)
        {
            // guest obviouly hasn't send any request
            return null;
        }

        $db= $this->getDBO();

        $wheres = array();
        $wheres[] = 'block = 0';

        $limit = $this->getState('limit');
        $limitstart = $this->getState('limitstart');

        $query = 'SELECT count(*) '
            .' FROM '. $db->quoteName('#__community_connection').' as a, '. $db->quoteName('#__users').' as b'
            .' WHERE a.'. $db->quoteName('connect_from').'='.$db->Quote($id)
            .' AND a.'. $db->quoteName('status').'='. $db->Quote(0)
            .' AND a.'. $db->quoteName('connect_to').'=b.'. $db->quoteName('id')
            .' ORDER BY a.'. $db->quoteName('connection_id').' DESC ';

        $db->setQuery($query);

        try {
            $total = $db->loadResult();
        } catch (Exception $e) {
            JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
        }

        // Appy pagination
        if ( empty($this->_pagination))
        {
            jimport('joomla.html.pagination');
            $this->_pagination = new JPagination($total, $limitstart, $limit);
        }

        $query = CString::str_ireplace('count(*)', 'b.*', $query);
        $query .= " LIMIT {$limitstart}, {$limit} ";

        $db->setQuery($query);
        try {
            $result = $db->loadObjectList();
        } catch (Exception $e) {
            JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
        }
        return $result;
    }

    public function &getPagination()
    {
        return $this->_pagination;
    }

    /**
     * Return an array of friend id
     */
    public function getFriendIds($id)
    {
        if($id == 0)
        {
            // guest obviously has no frinds
            $fid = array();
            return $fid;
        }

        $db		= JFactory::getDBO();
        $query	= 'SELECT DISTINCT(a.'. $db->quoteName('connect_to').') AS '. $db->quoteName('id')
            .' FROM ' . $db->quoteName('#__community_connection') . ' AS a '
            .' INNER JOIN ' . $db->quoteName( '#__users' ) . ' AS b '
            .' ON a.'. $db->quoteName('connect_from').'=' . $db->Quote( $id ) . ' '
            .' AND a.'. $db->quoteName('connect_to').'=b.'. $db->quoteName('id')
            .' AND a.'. $db->quoteName('status').'=' . $db->Quote( 1 );
        $db->setQuery( $query );

        $friends	= $db->loadColumn();
        return $friends;
    }

    /**
     * Return an array of friend records
     * This is a temporary solution to for the performance issue on photo page view.
     * Todo: need to support pagination on the cWindow
     */
    public function getFriendRecords($id)
    {
        if($id == 0)
        {
            // guest obviously has no frinds
            $fid = array();
            return $fid;
        }

        $db		= JFactory::getDBO();
        $query	= 'SELECT DISTINCT(a.'. $db->quoteName('connect_to').') AS id , b.'. $db->quoteName('name') .' , b.'.$db->quoteName('username').' , u.'.$db->quoteName('params') . 'AS _cparams'.' , b.'.$db->quoteName('params') . 'AS params'
            . ' FROM ' . $db->quoteName('#__community_connection') .' AS a'
            . ' INNER JOIN ' . $db->quoteName('#__users') . ' AS b  '
            . ' ON a.'. $db->quoteName('connect_from') .'='. $db->Quote( $id )
            . ' AND b.'. $db->quoteName('block'). '=' . $db->Quote(0)
            . ' AND a.'. $db->quoteName('connect_to').'=b.'. $db->quoteName('id')
            . ' AND a.'. $db->quoteName('status').'=' . $db->Quote(1)
            . ' LEFT JOIN ' . $db->quoteName('#__community_users') .' u '
            . ' ON a.' . $db->quoteName('connect_to') .'=u.' . $db->quoteName('userid')
            . ' WHERE NOT EXISTS ( SELECT d.'. $db->quoteName('blocked_userid')
            . ' FROM '. $db->quoteName('#__community_blocklist')
            . ' AS d  WHERE d.'. $db->quoteName('userid').' = '. $db->Quote( $id )
            . ' AND d.'. $db->quoteName('blocked_userid').' = a.'. $db->quoteName('connect_to').')'
            . ' ORDER BY a.'. $db->quoteName('connection_id').' DESC';

        $db->setQuery( $query );

        $friends	= $db->loadObjectList();

        $users = array();
        foreach ($friends as $friend)
        {
            $user 			= new CUser($friend->id);
            $isNewUser		= $user->init($friend);
            $users[] = $user;
        }

        return $users;
    }
    /**
     * Return the total number of friend for the user
     * @paran int id	the user id
     */
    public function getFriendsCount($id)
    {
        // For visitor with id=0, obviously he won't have any friend!
        if ( empty($id))
            return 0;

        $db= $this->getDBO();

        // Search those we send connection
        $query = "SELECT count(distinct connect_to) "
            .' FROM '. $db->quoteName('#__community_connection').' as a, '. $db->quoteName('#__users').' as b'
            .' WHERE a.'. $db->quoteName('connect_from').'='.$db->Quote($id)
            .' AND b.block=0 '
            .' AND a.'. $db->quoteName('status').'='. $db->Quote(1)
            .' AND a.'. $db->quoteName('connect_to').'=b.'. $db->quoteName('id')
            .' AND NOT EXISTS ( SELECT d.'. $db->quoteName('blocked_userid')
            .' FROM ' . $db->quoteName( '#__community_blocklist' ) . ' AS d'
            .' WHERE d.'. $db->quoteName('userid').' = ' . $db->Quote( $id )
            .' AND d.'. $db->quoteName('blocked_userid').' = a.'. $db->quoteName('connect_to').') '
            .' ORDER BY a.'. $db->quoteName('connection_id').' DESC ';

        $db->setQuery($query);
        $total = $db->loadResult();
        return $total;
    }

    public function getInviteListByName($namePrefix ,$userid, $cid, $limitstart = 0, $limit = 8){
        $db	= $this->getDBO();

        $andName = '';
        $config = CFactory::getConfig();
        $nameField = $config->getString('displayname');
        if(!empty($namePrefix)){
            $andName	= ' AND b.' . $db->quoteName( $nameField ) . ' LIKE ' . $db->Quote( '%'.$namePrefix.'%' ) ;
        }
        $query	=   'SELECT DISTINCT(a.'.$db->quoteName('connect_to').') AS id  FROM ' . $db->quoteName('#__community_connection') . ' AS a '
            . ' INNER JOIN ' . $db->quoteName( '#__users' ) . ' AS b '
            . ' ON a.'.$db->quoteName('connect_from').'=' . $db->Quote( $userid )
            . ' AND a.'.$db->quoteName('connect_to').'=b.'.$db->quoteName('id')
            . ' AND a.'.$db->quoteName('status').'=' . $db->Quote( '1' )
            . ' AND b.'.$db->quoteName('block').'=' .$db->Quote('0')
            . ' WHERE NOT EXISTS ( SELECT d.'.$db->quoteName('blocked_userid') . ' as id'
            . ' FROM '.$db->quoteName('#__community_blocklist') . ' AS d  '
            . ' WHERE d.'.$db->quoteName('userid').' = '.$db->Quote($userid)
            . ' AND d.'.$db->quoteName('blocked_userid').' = a.'.$db->quoteName('connect_to').')'
            . $andName
            . ' ORDER BY b.' . $db->quoteName($nameField)
            . ' LIMIT ' . $limitstart.','.$limit
        ;
        $db->setQuery( $query );
        $friends = $db->loadColumn();
        //calculate total
        $query	=   'SELECT COUNT(DISTINCT(a.'.$db->quoteName('connect_to').'))  FROM ' . $db->quoteName('#__community_connection') . ' AS a '
            . ' INNER JOIN ' . $db->quoteName( '#__users' ) . ' AS b '
            . ' ON a.'.$db->quoteName('connect_from').'=' . $db->Quote( $userid )
            . ' AND a.'.$db->quoteName('connect_to').'=b.'.$db->quoteName('id')
            . ' AND a.'.$db->quoteName('status').'=' . $db->Quote( '1' )
            . ' AND b.'.$db->quoteName('block').'=' .$db->Quote('0')
            . ' WHERE NOT EXISTS ( SELECT d.'.$db->quoteName('blocked_userid') . ' as id'
            . ' FROM '.$db->quoteName('#__community_blocklist') . ' AS d  '
            . ' WHERE d.'.$db->quoteName('userid').' = '.$db->Quote($userid)
            . ' AND d.'.$db->quoteName('blocked_userid').' = a.'.$db->quoteName('connect_to').')'
            . $andName;

        $db->setQuery( $query );
        $this->total	=  $db->loadResult();

        return $friends;
    }

    /**
     * Get the friends of friends of current user
     * @param array $filter
     */
    public function getFriendsOfFriends($filter = array()){

        $my = CFactory::getUser();

        if(!$my->id){
            return array();
        }

        //get all my friends
        $friends = $this->getFriends($my->id, 'latest', false, 'all', 0, '', true);

        $thirdPartyFriends = array();
        //load all the friends of friends
        foreach($friends as $friend){
            $thirdPartyFriends = array_merge($thirdPartyFriends, $this->getFriends($friend, 'latest', false, 'all', 0, '', true));
        }

        $thirdPartyFriends = array_unique($thirdPartyFriends);

        //now that we have all the third party friends, we will filter out the value if the third party friends is our friend
        foreach($thirdPartyFriends as $key=>$friend){
            if(CFriendsHelper::isConnected($my->id,$friend)){
                unset($thirdPartyFriends[$key]);
            }
        }

        return $thirdPartyFriends;
    }

    /**
     * * return the list of friend from approved connections
     * controller need to set the id
     * @param $id user id of the person we want to searhc their friend
     * @param string $sorted
     * @param bool $useLimit
     * @param string $filter
     * @param int $maxLimit
     * @param string $namefilter
     * @param bool $idOnly will return id in array only if set to true
     * @return CUser objects
     */
    public function getFriends($id, $sorted = 'latest', $useLimit = true , $filter = 'all' , $maxLimit = 0, $namefilter = '', $idOnly = false )
    {
        $cusers = array ();

        // Deprecated since 1.8 .
        // Earlier versions the default $filter is empty but since we will now need to handle character filter,
        // we need to set the default to 'all'
        if( empty($filter) )
        {
            $filter	= 'all';
        }

        // For visitor with id=0, obviously he won't have any friend!
        if ( empty($id))
        {
            return $cusers;
        }

        $db= $this->getDBO();

        $wheres = array ();
        $wheres[] = 'block = 0';
        $limit = $this->getState('limit');
        $limitstart = $this->getState('limitstart');

        $query	= 'SELECT DISTINCT(a.'. $db->quoteName('connect_to').') AS id ';
        if($filter == 'suggestion')
        {
            $query	= 'SELECT DISTINCT(b.'. $db->quoteName('connect_to').') AS id ';
        }
        $query	.= ', CASE WHEN c.'. $db->quoteName('userid').' IS NULL THEN 0 ELSE 1 END AS online';

        switch( $filter )
        {
            case 'mutual':
                $user	= CFactory::getUser();

                $query	.= ' FROM ' . $db->quoteName( '#__community_connection' ) . ' AS a '
                    . ' INNER JOIN ' . $db->quoteName( '#__community_connection' ) . ' AS b ON ( a.'. $db->quoteName('connect_to').' = b.'. $db->quoteName('connect_to').' ) '
                    . ' AND a.'. $db->quoteName('connect_from').'=' . $db->Quote( $id )
                    . ' AND b.'. $db->quoteName('connect_from').'=' . $db->Quote( $user->id )
                    . ' AND a.'. $db->quoteName('status').'=' . $db->Quote( 1 )
                    . ' AND b.'. $db->quoteName('status').'=' . $db->Quote( 1 );
                $query	.= ' LEFT JOIN ' . $db->quoteName('#__session') . ' AS c ON a.'. $db->quoteName('connect_to').' = c.'. $db->quoteName('userid');
                $query  .= ' WHERE NOT EXISTS ( SELECT d.'. $db->quoteName('blocked_userid')
                    .' FROM ' . $db->quoteName( '#__community_blocklist' ) . ' AS d'
                    .' WHERE d.'. $db->quoteName('userid').' = ' . $db->Quote( $id )
                    .' AND d.'. $db->quoteName('blocked_userid').' = a.'. $db->quoteName('connect_to').') ';
                // Search those we send connection
                $total = $this->getFriendsCount($id);

                // Appy pagination
                if ( empty($this->_pagination))
                {
                    jimport('joomla.html.pagination');
                    $this->_pagination = new JPagination($total, $limitstart, $limit);
                }
                break;
            case 'suggestion':
                $user	= CFactory::getUser();
                $query	.= ', COUNT(1) AS totalFriends, b.'. $db->quoteName('connect_to').' AS id'
                    . ' FROM ' . $db->quoteName( '#__community_connection' ) . ' AS b'
                    . ' LEFT JOIN '. $db->quoteName('#__session') . ' AS c ON c.'. $db->quoteName('userid').' = b.'. $db->quoteName('connect_to')
                    . ' WHERE b.'. $db->quoteName('connect_to').' != ' . $db->Quote( $user->id )
                    . ' AND b.'. $db->quoteName('connect_from').' IN (SELECT a.'. $db->quoteName('connect_to')
                    . ' FROM ' . $db->quoteName( '#__community_connection' ) . ' a WHERE a.'. $db->quoteName('connect_from').' = ' . $db->Quote( $user->id )
                    . ' AND a.'. $db->quoteName('status').' = ' . $db->Quote( '1' ) . ')'
                    . ' AND NOT EXISTS(SELECT d.'. $db->quoteName('connect_to')
                    . ' FROM '. $db->quoteName('#__community_connection').' d '
                    . ' WHERE (d.'. $db->quoteName('connect_to').' = ' . $db->Quote( $user->id )
                    . ' AND d.'. $db->quoteName('connect_from').' = b.'. $db->quoteName('connect_to').')'
                    . ' OR (d.'. $db->quoteName('connect_to').' = b.'. $db->quoteName('connect_to')
                    . ' AND d.'. $db->quoteName('connect_from').' = ' . $db->Quote( $user->id ) . ') )'
                    . ' AND NOT EXISTS ( SELECT e.'. $db->quoteName('blocked_userid')
                    . ' FROM ' . $db->quoteName( '#__community_blocklist' ) . ' AS e '
                    . ' WHERE e.'. $db->quoteName('userid').' = ' . $db->Quote( $id )
                    . ' AND e.'. $db->quoteName('blocked_userid').' = b.'. $db->quoteName('connect_to').') ';

                // Search those we send connection
                $total = $this->getFriendsCount($id);

                // Appy pagination
                if ( empty($this->_pagination))
                {
                    jimport('joomla.html.pagination');
                    $this->_pagination = new JPagination($total, $limitstart, $limit);
                }
                break;
            case 'all':
                $query	.= ', b.name';
                $query	.= ' FROM ' . $db->quoteName( '#__community_connection' ) . ' AS a '
                    . ' INNER JOIN ' . $db->quoteName( '#__users' ) . ' AS b '
                    . ' ON a.'. $db->quoteName('connect_from').'=' . $db->Quote( $id )
                    . ' AND b.block=0 '
                    . ' AND a.'. $db->quoteName('connect_to').'=b.'. $db->quoteName('id')
                    . ' AND a.'. $db->quoteName('status').'=' . $db->Quote( '1' ) . ' '
                    . ' LEFT JOIN ' . $db->quoteName('#__session') . ' AS c ON a.'. $db->quoteName('connect_to').' = c.'. $db->quoteName('userid')
                    . ' WHERE NOT EXISTS ( SELECT d.'. $db->quoteName('blocked_userid')
                    . ' FROM ' . $db->quoteName( '#__community_blocklist' ) . ' AS d '
                    . ' WHERE d.'. $db->quoteName('userid').' = ' . $db->Quote( $id )
                    . ' AND d.'. $db->quoteName('blocked_userid').' = a.'. $db->quoteName('connect_to').') ';

                // Search those we send connection
                $total = $this->getFriendsCount($id);

                // Appy pagination
                if ( empty($this->_pagination))
                {
                    jimport('joomla.html.pagination');
                    $this->_pagination = new JPagination($total, $limitstart, $limit);
                }
                break;
            default:
                $filterCount	= JString::strlen( $filter );

                $filterQuery	= '';

                if( $filter == 'others' )
                {
                    $filterQuery	= ' AND b.name REGEXP "^[^a-zA-Z]."';
                }
                else
                {

                    $config         = CFactory::getConfig();

                    $filterQuery	= ' AND(';

                    if($namefilter != ''){
                        $nameField      = 'b.' . $db->quoteName( $config->get('displayname') );
                        $filterQuery	.= $nameField .' LIKE "%' . JString::strtoupper($namefilter) . '%" OR ' . $nameField . ' LIKE "%' . JString::strtolower($namefilter) . '%"';
                    }else{
                        for( $i = 0; $i < $filterCount; $i++ )
                        {
                            $char			= $filter{$i};
                            $filterQuery	.= $i != 0 ? ' OR ' : ' ';
                            $nameField      = 'b.' . $db->quoteName( $config->get('displayname') );
                            $filterQuery	.= $nameField .' LIKE "' . JString::strtoupper($char) . '%" OR ' . $nameField . ' LIKE "' . JString::strtolower($char) . '%"';
                        }
                    }

                    $filterQuery	.= ')';
                }

                $query	.= ', b.name';
                $query	.= ' FROM ' . $db->quoteName( '#__community_connection' ) . ' AS a '
                    . ' INNER JOIN ' . $db->quoteName( '#__users' ) . ' AS b '
                    . ' ON a.'. $db->quoteName('connect_from').'=' . $db->Quote( $id )
                    . ' AND a.'. $db->quoteName('connect_to').'=b.'. $db->quoteName('id')
                    . ' AND a.'. $db->quoteName('status').'=' . $db->Quote( '1' );
                $query	.= $filterQuery;
                $query	.= ' LEFT JOIN ' . $db->quoteName('#__session') . ' AS c ON a.connect_to = c.userid';
                $query  .= ' WHERE NOT EXISTS ( SELECT d.'. $db->quoteName('blocked_userid')
                    .' FROM ' . $db->quoteName( '#__community_blocklist' ) . ' AS d '
                    .' WHERE d.'. $db->quoteName('userid').' = ' . $db->Quote( $id )
                    .' AND d.'. $db->quoteName('blocked_userid').' = a.'. $db->quoteName('connect_to').') ';

                // Search those we send connection
                $pagingQuery = "SELECT count(*) "
                    .' FROM '. $db->quoteName('#__community_connection').' as a, '. $db->quoteName('#__users').' as b'
                    .' WHERE a.'. $db->quoteName('connect_from').'='.$db->Quote($id)
                    .' AND a.'. $db->quoteName('status').'='. $db->Quote(1)
                    .' AND a.'. $db->quoteName('connect_to').'=b.'. $db->quoteName('id')
                    . $filterQuery
                    .' AND NOT EXISTS ( SELECT d.'. $db->quoteName('blocked_userid')
                    .' FROM ' . $db->quoteName( '#__community_blocklist' ) . ' AS d'
                    .' WHERE d.'. $db->quoteName('userid').' = ' . $db->Quote( $id )
                    .' AND d.'. $db->quoteName('blocked_userid').' = a.'. $db->quoteName('connect_to').') '
                    .' ORDER BY a.'. $db->quoteName('connection_id').' DESC ';

                $db->setQuery($pagingQuery);
                $total = $db->loadResult();

                // Appy pagination
                if ( empty($this->_pagination))
                {
                    jimport('joomla.html.pagination');
                    $this->_pagination = new JPagination($total, $limitstart, $limit);
                }
                break;
        }

        switch($sorted)
        {
            // We only want the id since we use CFactory::getUser later to get their full details.
            case 'online':
                $query	.= ' ORDER BY '. $db->quoteName('online').' DESC';
                break;
            case 'suggestion':
                $query	.=	' GROUP BY (b.'. $db->quoteName('connect_to').')'
                    . ' HAVING (totalFriends >= ' . FRIEND_SUGGESTION_LEVEL . ')';

                break;
            case 'name':
                //sort by name only applicable to filter is not mutual and suggestion
                if($filter != 'mutual' && $filter != 'suggestion')
                {
                    $config	= CFactory::getConfig();
                    $query	.= ' ORDER BY b.' . $db->quoteName( $config->get( 'displayname' ) ) . ' ASC';
                }
                break;
            default:
                $query	.= ' ORDER BY a.'. $db->quoteName('connection_id').' DESC';
                break;
        }

        //do not limit the query if this is a search based on names
        if ($useLimit)
        {
            $query .= " LIMIT {$limitstart}, {$limit} ";
        }
        else if ($maxLimit > 0)
        {
            // we override the limit by specifying how many return need to be return.
            $query .= " LIMIT 0, {$maxLimit} ";
        }

        $db->setQuery($query);

        try {
            $result = $db->loadObjectList();
        } catch (Exception $e) {
            JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
        }

        // preload all users
        $uids = array();
        foreach($result as $m)
        {
            $uids[] = $m->id;
        }

        if($idOnly){
            return $uids;
        }

        CFactory::loadUsers($uids);

        for ($i = 0; $i < count($result); $i++)
        {

            $usr = CFactory::getUser($result[$i]->id);
            $cusers[] = $usr;
        }

        return $cusers;
    }

    /**
     * return the list of friends group
     * @param id int user id of that person we want to search for their friend group
     *
     */

    public function getFriendsGroup($id)
    {
        $db= $this->getDBO();

        // Search those we send connection
        $query = 'SELECT *	FROM '. $db->quoteName('#__community_friendlist')
            .' WHERE '. $db->quoteName('user_id').' = '.$db->Quote($id);

        $db->setQuery($query);

        try {
            $result = $db->loadObjectList();
        } catch (Exception $e) {
            JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
        }

        return $result;
    }

    /**
     *get Friend Connection
     *
     *@param connect_from int owner's id
     *@param connect_to stranger's id
     *return db object
     */

    public function getFriendConnection($connect_from, $connect_to)
    {

        $db= $this->getDBO();

        $query = 'SELECT * FROM '. $db->quoteName('#__community_connection')
            .' WHERE ('. $db->quoteName('connect_from').' = '.$db->Quote($connect_from).' AND '. $db->quoteName('connect_to').' ='.$db->Quote($connect_to).')'
            .' OR ( '. $db->quoteName('connect_from').' = '.$db->Quote($connect_to)
            .' AND '. $db->quoteName('connect_to').' ='.$db->Quote($connect_from).')';

        $db->setQuery($query);

        try {
            $result = $db->loadObjectList();
        } catch (Exception $e) {
            JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
        }

        return $result;
    }

    public function getPendingUserId($id)
    {
        $db= $this->getDBO();

        $query = 'SELECT '. $db->quoteName('connect_from')
            .' FROM '. $db->quoteName('#__community_connection')
            .' WHERE '. $db->quoteName('connection_id').' = '.$db->Quote($id);

        $db->setQuery($query);

        try {
            $result = $db->loadObject();
        } catch (Exception $e) {
            JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
        }

        return $result;
    }

    /**
     * Returns a list of pending friend requests for the user
     *
     * @param	int	$userId	The number of friend requests to lookup for this user.
     *
     * @return	int Total number of friend requests.
     **/
    public function getTotalNotifications( $user )
    {
        return (int) $this->countPending( $user->id );
    }
}//end of class
