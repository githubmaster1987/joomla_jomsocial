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

Class CPhotosAccess implements CAccessInterface
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

	/*
	 * @param : $asset = group id, $album_obj = album object, $userid = userid
	 * @return : boolean / int
	 */
	static public function photosGroupAlbumView($userid, $asset, $album_obj = ''){
		$group	= JTable::getInstance( 'Group' , 'CTable' );
		$group->load($asset);

		$params	= $group->getParams();
		$photopermission = $params->get('photopermission', GROUP_PHOTO_PERMISSION_ADMINS);

		if($group->isMember($userid)){
            //all members should be able to view
            return true;
        }elseif( $photopermission == GROUP_PHOTO_PERMISSION_MEMBERS && $group->isMember($userid) ){
			return ((isset($album_obj) && $userid == $album_obj->creator )|| $group->isAdmin($userid ));
		}else if( ($photopermission == GROUP_PHOTO_PERMISSION_ADMINS && $group->isAdmin($userid) ) || COwnerHelper::isCommunityAdmin() ){
			return true;
		}elseif ($group->approvals == 0) {
			return true;
		}else{
			return false;
		}
	}

	/*
	 * @param : $asset = null, $wall_obj = wall object, $userid = userid
	 * @return : boolean / int
	 */
	static public function photosWallEdit($userid, $asset, $wall_obj){
		// @rule: We only allow editing of wall in 15 minutes
		$viewer		= CFactory::getUser($userid);
		$now		= JDate::getInstance();
		$interval	= CTimeHelper::timeIntervalDifference( $wall_obj->date , $now->toSql() );
		$interval	= abs( $interval );

		// Only owner and site admin can edit
		if( ( COwnerHelper::isCommunityAdmin() || $viewer->id == $wall_obj->post_by ))
		{
			return true;
		}
		return false;
	}

	/*
	 *	@param - asset as photo id
	 */
	static public function photosTagRemove($userid, $asset,$taggedUser){
		//condition: only owner can remove the tag
		$photo	= JTable::getInstance( 'Photo' , 'CTable' );
		$photo->load( $asset );
		if($userid == $photo->creator || $userid == $taggedUser->id){
			return true;
		}else{
			return false;
		}
	}

	/*
	 * @param - asset as album id
	 * @param - group_obj as group object
	 *
	 */

	static public function photosGroupAlbumManage($userid, $asset, $group_obj){
		$album	= JTable::getInstance( 'Album' , 'CTable' );
		$album->load( $asset );

		//condition: only owner of the album or community admin or album owner
		return ( COwnerHelper::isCommunityAdmin() || $group_obj->isAdmin( $userid ) || $album->creator == $userid );
	}

    /**
     * To check the permission for the respective user to edit album
     * @param $userid
     * @param $asset
     * @param $eventObj
     * @return bool
     */
    static public function photosEventAlbumManage($userid, $asset, $eventObj){
        $album	= JTable::getInstance( 'Album' , 'CTable' );
        $album->load( $asset );

        if($eventObj->isMember($userid) || $album->creator == $userid || COwnerHelper::isCommunityAdmin() || $eventObj->isAdmin($userid)){
            return true;
        }

        return false;
    }

	/*
	 * @param - asset as album id
	 *
	 */
	static public function photosUserAlbumManage($userid, $asset){
		$album	= JTable::getInstance( 'Album' , 'CTable' );
		$album->load( $asset );

		//condition: only owner of the album or community admin
		return ($album->creator == $userid || COwnerHelper::isCommunityAdmin());
	}

    static public function photosUserAlbumView($userid, $asset){
        //first
        $album	= JTable::getInstance( 'Album' , 'CTable' );
        $album->load( $asset );

        if($userid == $album->creator || COwnerHelper::isCommunityAdmin()){
            return true; // creator always be able to view his own album
        }

        $owner = CFactory::getUser($album->creator);
        $permission = $album->permissions;

        if($permission == COMMUNITY_STATUS_PRIVACY_FRIENDS && $owner->isFriendWith($userid)){
            return true;
        }

        if($permission == COMMUNITY_STATUS_PRIVACY_MEMBERS && $userid){
            return true;
        }

        if($permission <= COMMUNITY_STATUS_PRIVACY_PUBLIC){
            return true;
        }

        return false;
    }

	static public function photosAvatarUpload($myId, $userid)
	{

		if(COwnerHelper::isCommunityAdmin()){
			return true;
		}elseif($userid == CFactory::getUser()->id && $userid){
			return true;
		}else{
			//we need to check if this user is used in registration last step
			// so lets check if the user id belongs to a user that hasn't been activated yet which mean the last visit date is zero
			$db = JFactory::getDbo();

			$query = "SELECT id FROM ".$db->quoteName('#__users')
					." WHERE ".$db->quoteName('lastvisitDate')."=".$db->quote('0000-00-00 00:00:00');

			$db->setQuery($query);
			$result = $db->loadResult();

			return ($result) ? true : false;
		}

		return false;
	}

	static public function photosDelete($userId, $photoId){

		$photoTable = JTable::getInstance('Photo', 'CTable');
		$photoTable->load($photoId);

		//the creator can always delete the photo OR super admin
		if( ($userId && $photoTable->creator == $userId) || COwnerHelper::isCommunityAdmin()){
			return true;
		}

		$album	= JTable::getInstance( 'Album' , 'CTable' );
        $album->load( $photoTable->albumid );

		//check if this photo belongs to events/group albums
		if($album->eventid){
			$event	= JTable::getInstance( 'Event' , 'CTable' );
        	$event->load( $album->eventid );
			return $event->isAdmin($userId);
		}elseif($album->groupid){
			$group	= JTable::getInstance( 'Group' , 'CTable' );
        	$group->load( $album->groupid );
			return $group->isAdmin($userId);
		}

		return false;
	}

}