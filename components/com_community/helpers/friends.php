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

    class CFriendsHelper
    {
        /**
         * Check if 2 friends is connected or not
         * @param    int userid1
         * @param    int userid2
         * @return    bool
         */
        static public function isConnected($id1, $id2)
        {
            // Static caching for this session
            static $isFriend = array();
            if (!empty($isFriend[$id1 . '-' . $id2])) {
                return $isFriend[$id1 . '-' . $id2];
            }

            if (($id1 == $id2) && ($id1 != 0)) {
                return true;
            }

            if ($id1 == 0 || $id2 == 0) {
                return false;
            }

            /*
        $db = JFactory::getDBO();
        $sql = 'SELECT count(*) FROM ' . $db->quoteName('#__community_connection')
              .' WHERE ' . $db->quoteName('connect_from') .'=' . $db->Quote($id1) .' AND ' . $db->quoteName('connect_to') .'=' . $db->Quote($id2)
              .' AND ' . $db->quoteName('status') .' = ' . $db->Quote(1);

        $db->setQuery($sql);
        $result = $db->loadResult();


        $isFriend[$id1.'-'.$id2] = $result;
        */

            // change method to get connection since list friends stored in community_users as well
            $user = CFactory::getUser($id1);
            $isConnected = $user->isFriendWith($id2);

            return $isConnected;
        }

        static public function isWaitingApproval($id1, $id2)
        {
            $db = JFactory::getDBO();
            $sql = 'SELECT connection_id FROM ' . $db->quoteName('#__community_connection')
                . ' WHERE ' . $db->quoteName('connect_from') . '=' . $db->Quote($id1) . ' AND ' . $db->quoteName(
                    'connect_to'
                ) . '=' . $db->Quote($id2)
                . ' AND ' . $db->quoteName('status') . ' = ' . $db->Quote(0);

            $db->setQuery($sql);
            try {
                $result = $db->loadResult();
            } catch (Exception $e) {
                JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
            }

            return ($result) ? $result : false;
        }

        /**
         * * get the total mutual friends that i have with the target
         * @param $id the target id
         * @return int
         */
        static public function getTotalMutualFriends($id){
            $my = CFactory::getUser();
            $target = CFactory::getUser($id);
            if($my->id){
                $friends = CFactory::getModel('friends');
                return count($friends->getFriends($id, '', false, 'mutual'));
            }

            return 0;
        }

        static public function getUserFriendDropdown($targetId){
            $my = CFactory::getUser(); //current user
            $user = CFactory::getUser($targetId);

            //if user is not logged in, nothing should be displayed at all
            if (!$my->id || $my->id == $targetId) {
                return false;
            }

            $display = new stdClass();
            $display->canAddFriend = false;
            $display->canUnfriend = false;
            $display->canRemoveFriendRequest = false;

            $display->dropdown = false;
            $display->dropdownTrigger = false;
            $display->button = "COM_COMMUNITY_PROFILE_ADD_AS_FRIEND"; //by default
            $display->buttonTrigger = "joms.api.friendAdd('".$user->id."')";

            //is friend
            if(CFriendsHelper::isConnected($my->id,$targetId)){
                $display->button = "COM_COMMUNITY_FRIENDS_COUNT"; //friend
                $display->dropdown = 'COM_COMMUNITY_FRIENDS_REMOVE';
                $display->dropdownTrigger = "joms.api.friendRemove('".$user->id."');";
                $display->buttonTrigger = false;
            }else if(CFriendsHelper::isWaitingApproval($my->id,$user->id)){
                $display->button = "COM_COMMUNITY_PROFILE_CONNECT_REQUEST_SENT";
                $display->buttonTrigger = false;
                $display->dropdown = "COM_COMMUNITY_CANCEL_FRIEND_REQUEST";
                $display->dropdownTrigger = "joms.api.friendAddCancel('".$user->id."');";
            }else if($connectionId = CFriendsHelper::isWaitingApproval($user->id,$my->id)){
                $display->button = "COM_COMMUNITY_PENDING_APPROVAL";
                $display->buttonTrigger = false;
                $display->dropdown[] = "COM_COMMUNITY_FRIEND_ACCEPT_REQUEST";
                $display->dropdownTrigger[] = "joms.api.friendApprove('".$connectionId."');";
                $display->dropdown[] = "COM_COMMUNITY_FRIEND_REJECT_REQUEST";
                $display->dropdownTrigger[] = "joms.api.friendReject('".$connectionId."');";
            }

            $tmpl = new CTemplate();
            return $tmpl
                ->set('options', $display)
                ->fetch('general/friend-dropdown');
        }

        /**
         * This function is used to display the cog options for any users
         * @param $targetId
         * @param int $groupId
         * @param int $eventId
         * @param bool $getHTML
         * @return stdClass|type
         */
        static public function getUserCog($targetId, $groupId = 0, $eventId = 0, $getHTML = false){

            if(!$targetId){
                return false;
            }

            $my = CFactory::getUser(); //current user
            $user = CFactory::getUser($targetId);

            $display = new stdClass();
            $display->canFeature = false;
            $display->canBlock = false;
            $display->canIgnore = false;
            $display->canBan = false;
            $display->canUnfeature = false;
            $display->canUnblock = false;
            $display->canUnignore = false;
            $display->canSetGroupAdmin = false;
            $display->canUnsetGroupAdmin = false;
            $display->canSetEventAdmin = false;
            $display->canUnsetEventAdmin = false;
            $display->canBanFromGroup = false;
            $display->canUnbanFromGroup = false;
            $display->canRemoveFromGroup = false;
            $display->canBanFromGroup = false;
            $display->canBanFromEvent = false;
            $display->canUnbanFromEvent = false;

            $datas = array(
                'canFeature' => array(
                    'lang'=>'COM_COMMUNITY_MAKE_FEATURED',
                    'href'=>'joms.api.userAddFeatured("'.$targetId.'")'),
                'canUnfeature' => array(
                    'lang'=>'COM_COMMUNITY_REMOVE_FEATURED',
                    'href'=>'joms.api.userRemoveFeatured("'.$targetId.'")'),
                'canBlock' => array(
                    'lang'=>'COM_COMMUNITY_BLOCK_USER',
                    'href'=>'joms.api.userBlock("'.$targetId.'")'),
                'canUnblock' => array(
                    'lang'=>'COM_COMMUNITY_UNBLOCK_USER',
                    'href'=>'joms.api.userUnblock("'.$targetId.'")'),
                'canBan' => array(
                    'lang'=>'COM_COMMUNITY_BAN_USER',
                    'href'=>'joms.api.userBan("'.$targetId.'")'),
                'canIgnore' => array(
                    'lang'=>'COM_COMMUNITY_PREFERENCES_IGNORE',
                    'href'=>'joms.api.userIgnore("'.$targetId.'")'),
                'canUnignore' => array(
                    'lang'=>'COM_COMMUNITY_PREFERENCES_UNIGNORE',
                    'href'=>'joms.api.userUnignore("'.$targetId.'")'),
                'canSetGroupAdmin' => array(
                    'lang'=>'COM_COMMUNITY_GROUPS_ADMIN',
                    'href'=>'jax.call("community", "groups,ajaxAddAdmin", "'.$targetId.'", "'.$groupId.'")'),
                'canUnsetGroupAdmin' => array(
                    'lang'=>'COM_COMMUNITY_GROUPS_REVERT_ADMIN',
                    'href'=>'jax.call("community", "groups,ajaxRemoveAdmin", "'.$targetId.'", "'.$groupId.'")'),
                'canSetEventAdmin' => array(
                    'lang'=>'COM_COMMUNITY_EVENTS_ADMIN_SET',
                    'href'=>'jax.call("community","events,ajaxManageAdmin","'.$targetId.'", "'.$eventId.'","add")'),
                'canUnsetEventAdmin' => array(
                    'lang'=>'COM_COMMUNITY_EVENTS_ADMIN_REVERT',
                    'href'=>'jax.call("community","events,ajaxManageAdmin","'.$targetId.'", "'.$eventId.'","remove")'),
                'canBanFromGroup' => array(
                    'lang'=>'COM_COMMUNITY_GROUPS_BAN_FROM_GROUP',
                    'href'=>'joms.api.groupBanMember("'.$groupId.'", "'.$targetId.'")'),
                'canUnbanFromGroup' => array(
                    'lang'=>'COM_COMMUNITY_GROUPS_UNBAN_FROM_GROUP',
                    'href'=>'joms.api.groupUnbanMember("'.$groupId.'", "'.$targetId.'")'),
                'canRemoveFromGroup' => array(
                    'lang'=>'COM_COMMUNITY_GROUPS_REMOVE_FROM_GROUP',
                    'href'=>'joms.api.groupRemoveMember("'.$groupId.'", "'.$targetId.'")'),
                'canBanFromEvent' => array(
                    'lang'=>'COM_COMMUNITY_EVENTS_BAN_FROM_EVENT',
                    'href'=>'joms.api.eventBanMember("'.$eventId.'", "'.$targetId.'")'),
                'canUnbanFromEvent' => array(
                    'lang'=>'COM_COMMUNITY_EVENTS_UNBAN_FROM_EVENT',
                    'href'=>'joms.api.eventUnbanMember("'.$eventId.'", "'.$targetId.'")')
            );

            //if user is not logged in, nothing should be displayed at all
            if(!$my->id || $my->id == $targetId){
                return false;
            }

            if(COwnerHelper::isCommunityAdmin($my->id)){
                $featured = new CFeatured(FEATURED_USERS);
                $isFeatured = $featured->isFeatured($user->id);

                if($isFeatured){
                    $display->canUnfeature = true;
                }elseif(CFactory::getConfig()->get('show_featured')){
                    $display->canFeature = true;
                }

                $display->canBan = true; //always true because if useajaxStreamAddr is banned, he shouldn't be in the list anymore
            }

            //we can only ignore or block user. We cannot do both
            $blockModel	= CFactory::getModel('block');
            $block = $blockModel->getBlockStatus($my->id,$user->id,true);

            if($block){
                if(isset($block->type) && $block->type == 'block'){
                    $display->canUnblock = true;
                    //$display->canIgnore = true;
                }else{
                    //this is ignore list
                    $display->canUnignore = true;
                    $display->canBlock = true;
                }
            }else{
                //$display->canIgnore = true;
                $display->canBlock = true;
            }


            //For Group only
            if($groupId){
                $datas['canBan']['lang'] = 'COM_COMMUNITY_BAN_FROM_SITE';
                $group	= JTable::getInstance( 'Group' , 'CTable' );
                $group->load($groupId);
                if($group->isAdmin($my->id) || COwnerHelper::isCommunityAdmin()){
                    if($group->ownerid != $user->id){
                        //if user is admin, then we can unset him, unless the admin is the creator
                        if($group->isAdmin($user->id)){
                            $display->canUnsetGroupAdmin = true;
                        }else{
                            $display->canSetGroupAdmin = true;
                        }

                        //we can ban any member of the group
                        if($group->isBanned($user->id)){
                            $display->canUnbanFromGroup = true;
                        }else{
                            $display->canBanFromGroup = true;
                        }

                        $display->canRemoveFromGroup = true;
                    }
                }
            }

            //for event only
            if($eventId){
                $datas['canBan']['lang'] = 'COM_COMMUNITY_BAN_FROM_SITE';
                $event	= JTable::getInstance( 'Event' , 'CTable' );
                $event->load($eventId);
                if($event->isAdmin($my->id) || COwnerHelper::isCommunityAdmin()){
                    if($event->isMember($user->id) && !$event->isCreator($user->id)){
                        //if user is admin, then we can unset him, unless the admin is the creator
                        if($event->isAdmin($user->id)){
                            $display->canUnsetEventAdmin = true;
                        }else{
                            $display->canSetEventAdmin = true;
                        }
                    }

                    if(CEventHelper::isBanned($user->id, $event->id)){
                        $display->canUnbanFromEvent = true;
                    }else{
                        $display->canBanFromEvent = true;
                    }
                }
            }

            if($getHTML){
                $tmpl = new CTemplate();
                return $tmpl->set('options',$display)
                    ->set('datas', $datas)
                    ->set('groupid', ($groupId) ? $groupId : false)
                    ->set('eventid', ($eventId) ? $eventId : false)
                    ->fetch('general/user-cog');
            }

            return $display;
        }
    }


    /**
     * Deprecated since 1.8
     */
    function friendIsConnected($id1, $id2)
    {
        return CFriendsHelper::isConnected($id1, $id2);
    }

