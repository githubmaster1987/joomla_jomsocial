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

Class CGroupsAccess implements CAccessInterface
{

	/**
	 * Method to check if a user is authorised to perform an action in this class
	 *
	 * @param	integer	$userId	Id of the user for which to check authorisation.
	 * @param	string	$action	The name of the action to authorise.
	 * @param	mixed	$asset	Name of the asset as a string.
	 *
	 * @return	boolean	True if authorised.
	 * @since	Jomsocial 2.4
	 */
	static public function authorise()
	{
		$args      = func_get_args();
		$assetName = array_shift ( $args );

		if (method_exists(__CLASS__,$assetName)) {
			return call_user_func_array(array(__CLASS__, $assetName), $args);
		} else {
			return null;
		}
	}


	/**
	 *
	 * @since 2.4
	 * @param type $userId
	 * @param type $groupId
	 * @param type $group
	 */
	static public function groupsStreamView($userId, $groupId, $group)
	{
		return $group->isMember( $userId );
	}

	/*
	 * This function will get the permission to invite list
	 * @param type $userId
	 * @return : bool
	 */
    static public function groupsInvitelistView($userId)
    {
		$config = CFactory::getConfig();

		if( !$config->get('enablegroups') )
		{
			CAccess::setError(JText::_('COM_COMMUNITY_GROUPS_DISABLE'));
			return false ;
		} else {
			return true;
		}
    }


	/*
	 * This function will get the permission to invite user in a group
	 * @param type $userId
	 * @param type $assetId
	 * @param type $group object
	 * @return : bool
	 */
    static public function groupsInviteView($userId, $groupId, $group)
    {
		$config = CFactory::getConfig();

        if (!$group->isMember($userId) && !COwnerHelper::isCommunityAdmin()) {
            return false;
        } else {
          return true;
        }
    }

	/*
	 * Return true if can report on group
	 * @param type $userId
	 * @return : bool
	 */
	static public function groupsReportView($userId)
	{
		$config = CFactory::getConfig();

		if( !$config->get('enablereporting') || ( ( $userId == 0 ) && ( !$config->get('enableguestreporting') ) ) ) {
			return false;
		} else {
			return true;
		}
	}

	/*
	 * Return true if can report on group
	 * @param type $userId
	 * @return : bool
	 */
	static public function groupsSearchView($userId=0)
	{
		if(!$userId) {
			$my = CFactory::getUser();
			$userId = $my->id;
		}

		$config    = CFactory::getConfig();

		if( !$config->get('enablegroups') ){
			CAccess::setError(JText::_('COM_COMMUNITY_GROUPS_DISABLE'));
			return false;
		} else if( $userId == 0 && !$config->get('enableguestsearchgroups') ) {
			CAccess::setError('blockUnregister');
			return false;
		} else {
			return true;
		}
	}

	/*
	 * Return true if can view group
	 * @return : bool
	 */
	static public function groupsListView($userId)
	{
		$config = CFactory::getConfig();

		if( !$config->get('enablegroups') ) {
			return false;
		} else {
			return true;
		}
	}

	/**
         * Return true if can view my group
         * @param type $userId
         * @return boolean
         */
	static public function groupsMyView($userId)
	{
		$config = CFactory::getConfig();
                $requestUser = CFactory::getRequestUser();

		if ($userId == 0 && $requestUser->_cparams->get('privacyGroupsView') > 0 ) {
			CAccess::setError('blockUnregister');
			return false;
		} else if( !$config->get('enablegroups') ) {
			CAccess::setError(JText::_('COM_COMMUNITY_GROUPS_DISABLE'));
			return false;
		} else {
			return true;
		}
	}

	/*
	 * Return true if can edit group
	 * @param type $userId
	 * @return : bool
	 */
	static public function groupsAdd($userId)
	{
		$config = CFactory::getConfig();
		$my		= CFactory::getUser();


		return true;
		if ($userId == 0){
			CAccess::setError('blockUnregister');
			return false;
		} else if (!$config->get('enablegroups')) {
			CACCESS::setError(JText::_('COM_COMMUNITY_GROUPS_DISABLE'));
			return false;
		} else if( !$config->get('creategroups')  ||  !( COwnerHelper::isCommunityAdmin() || (COwnerHelper::isRegisteredUser() && $my->canCreateGroups())) ) {
			CACCESS::setError(JText::_('COM_COMMUNITY_GROUPS_DISABLE_CREATE_MESSAGE'));
			return false;
		} else if(CLimitsHelper::exceededGroupCreation($userId)) {
			$groupLimit	   = $config->get('groupcreatelimit');
			CACCESS::setError(JText::sprintf('COM_COMMUNITY_GROUPS_LIMIT' , $groupLimit));
			return false;
		} else {
			return true;
		}
	}

	/*
	 * Return true if can edit group
	 * @return : bool
	 */
	static public function groupsEdit($userId, $groupId, $group)
	{
		$document	= JFactory::getDocument();
		$viewType	= $document->getType();
        $jinput = JFactory::getApplication()->input;
		$viewName	= $jinput->get( 'view' );
		$view		= CFactory::getView($viewName, '', $viewType);

		if( $userId == 0 ) {
			CAccess::setError('blockUnregister');
			return false;
		} else if( !$group->isAdmin($userId) && !COwnerHelper::isCommunityAdmin() ) {
			CAccess::setError($view->noAccess());
			return false;
		} else {
			return true;
		}
	}

	/*
	 * Return true if can delete group
	 * @param type $userId
	 * @param type $groupId
	 * @param type $group object
	 * @return : bool
	 */
	static public function groupsDelete($userId, $groupId, $group)
	{
		if( !COwnerHelper::isCommunityAdmin() && !($userId == $group->ownerid) ) {
			return false;
		} else {
			return true;
		}
	}

	/*
	 * Return true if can join group
	 * @param type $userId
	 * @param type $groupId
	 * @return : bool
	 */
	static public function groupsJoin($userId, $groupId)
	{
		if( $userId == 0 ) {
			return false;
		} else {
			return true;
		}
	}

	/*
	 * Return true if can leave group
	 * @param type $userId
	 * @param type $groupId
	 * @return : bool
	 */
	static public function groupsLeave($userId, $groupId)
	{
		if( $userId == 0 ) {
			CAccess::setError('blockUnregister');
			return false;
		} else {
			return true;
		}
	}

	/*
	 * Return true if can leave group
	 * @param type $userId
	 * @param type $groupId
	 * @return : bool
	 */
	static public function groupsAvatarUpload($userId, $groupId, $group)
	{
		$document	= JFactory::getDocument();
		$viewType	= $document->getType();
        $jinput = JFactory::getApplication()->input;
		$viewName	= $jinput->get( 'view' );
		$view		= CFactory::getView($viewName, '', $viewType);

		if( $userId == 0 ) {
			CAccess::setError('blockUnregister');
			return false;
		} else if( !$group->isAdmin($userId) && !COwnerHelper::isCommunityAdmin() ) {
			CAccess::setError($view->noAccess());
			return false;
		} else {
			return true;
		}

	}

	/*
	 * Return true if can delete group discussion
	 * @param type $userId
	 * @param type $groupId
	 * @return : bool
	 */
	static public function groupsDiscussionDelete($userId, $groupId)
	{
		$group	= CFactory::getModel( 'groups' );

		if (!COwnerHelper::isRegisteredUser()) {
			CAccess::setError('blockUnregister');
			return false;
		} else if ( !COwnerHelper::isCommunityAdmin() && !$group->isAdmin( $userId , $groupId ) ) {
			CACCESS::setError(JText::_('COM_COMMUNITY_NOT_ALLOWED_TO_REMOVE_WALL'));
			return false;
		} else {
			return true;
		}
	}

	/*
	 * Return true if can approve group member
	 * @param type $userId
	 * @param type $groupId
	 * @return : bool
	 */
	static public function groupsMemberApprove($userId, $groupId)
	{
		$group = CFactory::getModel( 'groups' );

		if( !$group->isAdmin( $userId , $groupId ) && !COwnerHelper::isCommunityAdmin() ) {
			return false;
		} else {
			return true;
		}
	}

	/*
	 * Return true if can edit group wall.
	 * @param type $userId
	 * @param type $groupId
	 * @param type $wall object
	 * @return : bool
	 */
	static public function groupsWallEdit($userId, $groupId, $wall)
	{
		$group = CFactory::getModel( 'Groups' );

		if( $group->isAdmin( $userId , $groupId ) || COwnerHelper::isCommunityAdmin() || $userId == $wall->post_by ) {
			return true;
		} else {
			return false;
		}
	}

	/*
	 * Return true if can edit group discussion
	 * @param type $userId
	 * @param type $groupId
	 * @param type $wall object
	 * @return : bool
	 */
	static public function groupsDiscussionEdit($userId, $groupId, $wall)
	{
		$group = CFactory::getModel( 'Groups' );

		if( $group->isAdmin( $userId , $groupId ) || COwnerHelper::isCommunityAdmin() || $userId == $wall->post_by ) {
			return true;
		} else {
			return false;
		}
	}

	/*
	 * Return true if can remove member
	 * @param type $userId
	 * @param type $memberId
	 * @param type $group object
	 * @return : bool
	 */
	static public function groupsMemberRemove($userId, $memberId, $group)
	{
		if (!COwnerHelper::isRegisteredUser()) {
			CAccess::setError('blockUnregister');
			return false;
		} else if ($group->ownerid != $userId && !COwnerHelper::isCommunityAdmin()) {
			CAccess::setError(JText::_('COM_COMMUNITY_PERMISSION_DENIED_WARNING'));
			return false;
 		} else if ($group->ownerid == $memberId) {
			CAccess::setError(JText::_('COM_COMMUNITY_GROUPS_MEMBERS_DELETE_DENIED'));
			return false;
		} else {
			return true;
		}
	}

	/*
	 * Return true if can remove group wall.
	 * @param type $userId
	 * @param type $groupId
	 * @return : bool
	 */
	static public function groupsWallDelete($userId, $groupId)
	{
		$groupModel	= CFactory::getModel( 'groups' );

		if (!COwnerHelper::isRegisteredUser()) {
			CAccess::setError('blockUnregister');
			return false;
		} else if ( !COwnerHelper::isCommunityAdmin() && !$groupModel->isAdmin( $userId , $groupId ) ) {
			CAccess::setError(JText::_('COM_COMMUNITY_NOT_ALLOWED_TO_REMOVE_WALL'));
			return false;
		} else {
			return true;
		}
	}

	/*
	 * Return true if can edit group admin
	 * @param type $userId
	 * @param type $groupId
	 * @param type $group
	 * @return : bool
	 */
	static public function groupsAdminEdit($userId, $groupId, $group)
	{
		if( $group->ownerid != $userId && !COwnerHelper::isCommunityAdmin() ) {
			return false;
		} else {
			return true;
		}
	}

	/*
	 * Return true if can edit group admin
	 * @param type $userId
	 * @param type $groupId
	 * @param type $group
	 * @return : bool
	 */
	static public function groupsWallSave($userId, $groupId, $group)
	{
		$config	= CFactory::getConfig();

		if( $config->get('lockgroupwalls') && !$group->isMember( $userId ) ) {
			return false;
		} else {
			return true;
		}
	}

	/*
	 * Return true if can edit group admin
	 * @param type $userId
	 * @param type $groupId
	 * @param type $group
	 * @return : bool
	 */
	static public function groupsMemberBanUpdate($userId, $groupId, $group)
	{
		if( $group->ownerid != $userId && !COwnerHelper::isCommunityAdmin() ) {
			return false;
		} else {
			return true;
		}
	}

	/*
	 * Return true if can edit group admin
	 * @param type $userId
	 * @param type $groupId
	 * @return : bool
	 */
	static public function groupsMemberView($userId, $groupId)
	{
		$config	= CFactory::getConfig();
		if( !$config->get('enablegroups') )
		{
			CAccess::setError(JText::_('COM_COMMUNITY_GROUPS_DISABLE'));
			return false;
		} else {
			return true;
		}
	}

	/**
	 * @param $userId
	 * @param $group
	 * @return bool
	 */
	static public function groupsAnnouncementCreate($userId, $groupId){
		$group = JTable::getInstance('Group', 'CTable');
        $group->load($groupId);

		//only admins can create
		if(COwnerHelper::isCommunityAdmin() || $group->isAdmin($userId)){
			return true;
		}

		return false;
	}

	/*
	 * Return true if can view bulletins
	 * @param type $userId
	 * @return : bool
	 */
	static public function groupsBulletinsView($userId)
	{
		$config	= CFactory::getConfig();

		if( !$config->get('enablegroups') )
		{
			CAccess::setError(JText::_('COM_COMMUNITY_GROUPS_DISABLE'));
			return false;
		} else {
			return true;
		}
	}

	/*
	 * Return true if can view bulletin
	 * @param type $userId
	 * @param type $bullentinId
	 * @return : bool
	 */
	static public function groupsBulletinView($userId, $bullentinId)
	{
		$config	= CFactory::getConfig();

		if( !$config->get('enablegroups') )
		{
			CAccess::setError(JText::_('COM_COMMUNITY_GROUPS_DISABLE'));
			return false;
		} else {
			return true;
		}
	}

    /*
     * Return true if can create a discussion
     * @param type $userId
     * @param type $groupId
     * @return : bool
     *
     * Test cases (cascading - the next rule is only checked if the previous didn't kick in):
     *
     * 0 globally disabled
     * 0 not logged in
     * 1 super admin
     * 1 group owner
     * 0 not member
     * 0 member, banned
     * 0 member, awaiting approval
     * 1 member
     * 0 default (should not be the case)
     */
    static public function groupsDiscussionsCreate($userId, $groupId)
    {
        $config	= CFactory::getConfig();

        $group = JTable::getInstance('Group', 'CTable');
        $group->load($groupId);

        $groupModel = CFactory::getModel('groups');

        // FALSE globally disabled
        if(!$config->get('creatediscussion')) {
            echo "<!-- ".__FUNCTION__.__LINE__."-->";
            return false;
        }

        // FALSE not logged in
        if(!$userId) {
            echo "<!-- ".__FUNCTION__.__LINE__."-->";
            return false;
        }

        // TRUE Super Admin
        if(COwnerHelper::isCommunityAdmin($userId)) {
            echo "<!-- ".__FUNCTION__.__LINE__."-->";
            return true;
        }

        // TRUE owner
        if($group->ownerid == $userId) {
            echo "<!-- ".__FUNCTION__.__LINE__."-->";
            return true;
        }

        // FALSE not member
        if(!$group->isMember($userId)) {
            echo "<!-- ".__FUNCTION__.__LINE__."-->";
            return false;
        }

        // FALSE member, but banned
        if($group->isBanned($userId)) {
            echo "<!-- ".__FUNCTION__.__LINE__."-->";
            return false;
        }

        // FALSE member, but waiting approval
        if($groupModel->isWaitingAuthorization($userId, $groupId)) {
            echo "<!-- " . __FUNCTION__ . __LINE__ . "-->";
            return false;
        }

        // TRUE member
        if($group->isMember($userId)) {
            echo "<!-- ".__FUNCTION__.__LINE__."-->";
            return true;
        }

        // default (shouldn't really kick in)
        echo "<!-- ".__FUNCTION__.__LINE__."-->";
        return false;
    }

    /*
     * Return true if can create a video
     * @param type $userId
     * @param type $groupId
     * @return : bool
     *
     * Test cases (cascading - the next rule is only checked if the previous didn't kick in):
     *
     * 0 globally disabled
     * 0 disabled for group
     * 0 not logged in
     * 1 super admin
     * 1 group owner
     * 0 disabled for members
     * 0 not member
     * 0 member, banned
     * 0 member, awaiting approval
     * 1 member
     * 0 default (should not be the case)
     */
    static public function groupsVideosCreate($userId, $groupId)
    {
        $config	= CFactory::getConfig();

        $group = JTable::getInstance('Group', 'CTable');
        $group->load($groupId);

        $params = $group->getParams();

        $groupModel = CFactory::getModel('groups');

        // FALSE globally disabled
        if(!$config->get('groupvideos')) {
            echo "<!-- ".__FUNCTION__.__LINE__."-->";
            return false;
        }

        // FALSE group videos disabled
        if($params->get('videopermission') == -1) {
            echo "<!-- ".__FUNCTION__.__LINE__."-->";
            return false;
        }

        // FALSE not logged in
        if(!$userId) {
            echo "<!-- ".__FUNCTION__.__LINE__."-->";
            return false;
        }

        // TRUE Super Admin
        if(COwnerHelper::isCommunityAdmin($userId)) {
            echo "<!-- ".__FUNCTION__.__LINE__."-->";
            return true;
        }

        // TRUE owner
        if($group->ownerid == $userId) {
            echo "<!-- ".__FUNCTION__.__LINE__."-->";
            return true;
        }

        // FALSE only admins can post
        if($params->get('videopermission') == 1) {
            echo "<!-- ".__FUNCTION__.__LINE__."-->";
            return false;
        }

        // FALSE not member
        if(!$group->isMember($userId)) {
            echo "<!-- ".__FUNCTION__.__LINE__."-->";
            return false;
        }

        // FALSE member, but banned
        if($group->isBanned($userId)) {
            echo "<!-- ".__FUNCTION__.__LINE__."-->";
            return false;
        }

        // FALSE member, but waiting approval
        if($groupModel->isWaitingAuthorization($userId, $groupId)) {
            echo "<!-- " . __FUNCTION__ . __LINE__ . "-->";
            return false;
        }

        // TRUE member
        if($group->isMember($userId)) {
            echo "<!-- ".__FUNCTION__.__LINE__."-->";
            return true;
        }

        // default (shouldn't really kick in)
        echo "<!-- ".__FUNCTION__.__LINE__."-->";
        return false;
    }

    /*
     * Return true if can create an event
     * @param type $userId
     * @param type $groupId
     * @return : bool
     *
     * Test cases (cascading - the next rule is only checked if the previous didn't kick in):
     *
     * 0 globally disabled
     * 0 disabled for group
     * 0 not logged in
     * 1 super admin
     * 1 group owner
     * 0 disabled for members
     * 0 not member
     * 0 member, banned
     * 0 member, awaiting approval
     * 1 member
     * 0 default (should not be the case)
     */
    static public function groupsEventsCreate($userId, $groupId)
    {
        $config	= CFactory::getConfig();

        $group = JTable::getInstance('Group', 'CTable');
        $group->load($groupId);

        $params = $group->getParams();

        $groupModel = CFactory::getModel('groups');

        // FALSE globally disabled
        if(!$config->get('group_events')) {
            echo "<!-- ".__FUNCTION__.__LINE__."-->";
            return false;
        }

        // FALSE group events disabled
        if($params->get('eventpermission') == -1) {
            echo "<!-- ".__FUNCTION__.__LINE__."-->";
            return false;
        }

        // FALSE not logged in
        if(!$userId) {
            echo "<!-- ".__FUNCTION__.__LINE__."-->";
            return false;
        }

        // TRUE Super Admin
        if(COwnerHelper::isCommunityAdmin($userId)) {
            echo "<!-- ".__FUNCTION__.__LINE__."-->";
            return true;
        }

        // TRUE owner
        if($group->ownerid == $userId) {
            echo "<!-- ".__FUNCTION__.__LINE__."-->";
            return true;
        }

        // FALSE only admins can post
        if($params->get('eventpermission') == 1) {
            echo "<!-- ".__FUNCTION__.__LINE__."-->";
            return false;
        }

        // FALSE not member
        if(!$group->isMember($userId)) {
            echo "<!-- ".__FUNCTION__.__LINE__."-->";
            return false;
        }

        // FALSE member, but banned
        if($group->isBanned($userId)) {
            echo "<!-- ".__FUNCTION__.__LINE__."-->";
            return false;
        }

        // FALSE member, but waiting approval
        if($groupModel->isWaitingAuthorization($userId, $groupId)) {
            echo "<!-- " . __FUNCTION__ . __LINE__ . "-->";
            return false;
        }

        // TRUE member
        if($group->isMember($userId)) {
            echo "<!-- ".__FUNCTION__.__LINE__."-->";
            return true;
        }

        // default (shouldn't really kick in)
        echo "<!-- ".__FUNCTION__.__LINE__."-->";
        return false;
    }

    static public function groupsPhotosCreate($userId, $groupId)
    {
        $config	= CFactory::getConfig();

        $group = JTable::getInstance('Group', 'CTable');
        $group->load($groupId);

        $params = $group->getParams();

        $groupModel = CFactory::getModel('groups');

        // FALSE globally disabled
        if(!$config->get('groupphotos')) {
            echo "<!-- ".__FUNCTION__.__LINE__."-->";
            return false;
        }

        // FALSE group photos disabled
        if($params->get('photopermission') == -1) {
            echo "<!-- ".__FUNCTION__.__LINE__."-->";
            return false;
        }

        // FALSE not logged in
        if(!$userId) {
            echo "<!-- ".__FUNCTION__.__LINE__."-->";
            return false;
        }

        // TRUE Super Admin
        if(COwnerHelper::isCommunityAdmin($userId) || $group->isAdmin($userId)) {
            echo "<!-- ".__FUNCTION__.__LINE__."-->";
            return true;
        }

        // TRUE owner
        if($group->ownerid == $userId) {
            echo "<!-- ".__FUNCTION__.__LINE__."-->";
            return true;
        }

        // FALSE only admins can post
        if($params->get('photopermission') == 1) {
            echo "<!-- ".__FUNCTION__.__LINE__."-->";
            return false;
        }

        // FALSE not member
        if(!$group->isMember($userId)) {
            echo "<!-- ".__FUNCTION__.__LINE__."-->";
            return false;
        }

        // FALSE member, but banned
        if($group->isBanned($userId)) {
            echo "<!-- ".__FUNCTION__.__LINE__."-->";
            return false;
        }

        // FALSE member, but waiting approval
        if($groupModel->isWaitingAuthorization($userId, $groupId)) {
            echo "<!-- " . __FUNCTION__ . __LINE__ . "-->";
            return false;
        }

        // TRUE member
        if($group->isMember($userId)) {
            echo "<!-- ".__FUNCTION__.__LINE__."-->";
            return true;
        }

        // default (shouldn't really kick in)
        echo "<!-- ".__FUNCTION__.__LINE__."-->";
        return false;
    }

	static public function groupsCreate($userId)
	{
		$config = CFactory::getConfig();
		$my		= CFactory::getUser();

		//admin can always create group
		if(COwnerHelper::isCommunityAdmin()){
			return true;
		}

		return $config->get('creategroups') && (COwnerHelper::isRegisteredUser() && $my->canCreateGroups() );
	}

}