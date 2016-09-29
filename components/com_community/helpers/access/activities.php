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

Class CActivitiesAccess implements CAccessInterface{

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
	 * @param : int(activity_id)
	 * This function will get the permission to add for profile/mainstream activity
	 *
	 * @return : bool
	 */
	static public function activitiesCommentAdd($userId, $assetId, $obj=NULL){

		//$obj = func_get_arg(2);
		$params = func_get_args();
		$obj = (!isset($params[2])) ? NULL : $params[2] ;
		$model		= CFactory::getModel('activities');
		$result		= false;
		$config		= CFactory::getConfig();

		// Guest can never leave a comment
		if( $userId == 0){
			return false;
		}

		// If global config allow all members to comment, allow it
		if( $config->get( 'allmemberactivitycomment' ) == '1')
		{
			return true;
		}

		$allow_comment = false;

		// if all activity comment is allowed, return true
		$config			= CFactory::getConfig();
		if($config->get( 'allmemberactivitycomment' ) == '1' && COwnerHelper::isRegisteredUser()){
			$allow_comment = true;
		}

        if($obj instanceof CTableActivity || is_object($obj)){
            //lets check if this table activity belongs to group or events
            $tempObj = $obj;
            if(isset($obj->groupid) && $obj->groupid){
                $tempObj  = JTable::getInstance('Group','CTable');
                $tempObj->load($obj->groupid);
            }elseif(isset($obj->eventid) && $obj->eventid){
                $tempObj  = JTable::getInstance('Event','CTable');
                $tempObj->load($obj->eventid);
            }

            $obj = $tempObj;
        }

		if($obj instanceof CTableEvent || $obj instanceof CTableGroup){
			//event or group activities only
			if($obj->isMember($userId)){
				$allow_comment = true;
			}
		}else if($config->get( 'allmemberactivitycomment' ) == '1' && COwnerHelper::isRegisteredUser()){
			// if all activity comment is allowed, return true
			$allow_comment = true;
		}
        if ( !isset($obj->params)) {
            $params = '{}';
        }else {
            $params = $obj->params;
        }

		$params = new CParameter($params);
		$commentPermission = $params->get('commentPermission',NULL);

        /*
		if( !$commentPermission  && !is_null($commentPermission))
		{
			$allow_comment = false;
		}*/

		if($allow_comment || CFriendsHelper::isConnected($assetId, $userId) || COwnerHelper::isCommunityAdmin()){
			$result = true;
		}

		return $result;
	}

	/*
	 * @param : int(activity_id)
	 * This function will get the permission to delete for profile/mainstream activity
	 *
	 * @return : bool
	 */
	static public function activitiesDelete($userId, $assetId, $activity = array()){
		$obj = func_get_arg(0);
		$model		  = CFactory::getModel('activities');
		$result = false;

        if(COwnerHelper::isCommunityAdmin()){
            //always return true if this is community admin
            return true;
        }

		if($obj instanceof CTableEvent || $obj instanceof CTableGroup){
			//event or group activities only
			$isAppOwner =  $obj->isAdmin($userId);
			if($isAppOwner || COwnerHelper::isCommunityAdmin() || $model->getActivityOwner($assetId) == $userId){
				$result = true;
			}
		}else{
			if($model->getActivityOwner($assetId) == $userId || $activity->target == $userId){
				$result = true;
			}else if($activity instanceof CTableActivity && ($activity->eventid != 0 || $activity->groupid != 0)){
                //we can check if this activity belongs to any group or event and check if the user is authorized to delete the post
                if($activity->eventid){
                    //this activity belongs to an event
                    $event = JTable::getInstance('Event','CTable');
                    $event->load($activity->eventid);
                    $result = ($event->isAdmin($userId)) ? true : false;
                }elseif($activity->groupid){
                    $group = JTable::getInstance('Group','CTable');
                    $group->load($activity->groupid);
                    $result = ($group->isAdmin($userId)) ? true : false;
                }
            }
		}

		return $result;
	}

    /**
     * check permission to hide stream
     * @param $userId
     * @param $actorId
     * @param $obj
     * @return bool
     */
	static public function activitiesStreamHide($userId, $actorId, $obj= null){

        if($obj != null) {
            //we cant hide this if this is a featured item
            $featuredModel = CFactory::getModel('featured');
            $featuredActivities = $featuredModel->getAllStreamFeaturedId();

            if (in_array($obj->id, $featuredActivities)) {
                return false;
            }
        }

		if($userId){
			return true;
		}

		return false;
	}

	/**
	 * This function to check permission to add Mood in sthe stream
	 * @param  [string] $userId  [description]
	 * @param  [string] $actorId [description]
	 * @param  [object] $obj     [description]
	 * @return [boolean]          [description]
	 */
	static public function activitiesStreamAddMood($userId,$actorId,$obj = NULL){

		if(!$obj->params instanceof JRegistry)
			$obj->params = new JRegistry;

		$mood = $obj->params->get('mood',null);

		$allowapp = array(	'profile',
							'groups.wall',
    						'events.wall'
    					);
		$isAllowedApp = in_array($obj->app,$allowapp);

		if(($userId == $actorId ||  COwnerHelper::isCommunityAdmin()) && is_null($mood) && $isAllowedApp){
			return true;
		}

		return false;
	}

	/**
	 * This function to check permission for user to edit mood;
	 * @param  [string] $userId  [description]
	 * @param  [string] $actorId [description]
	 * @param  [object] $obj     [description]
	 * @return [boolean]          [description]
	 */
	static public function activitiesStreamEditMood($userId,$actorId,$obj = NULL){

		$allowapp = array(	'profile',
							'groups.wall',
    						'events.wall'
    					);
		$isAllowedApp = in_array($obj->app,$allowapp);

		$mood = $obj->params->get('mood',null);

		if(($userId == $actorId ||  COwnerHelper::isCommunityAdmin()) && !is_null($mood) && $isAllowedApp){
			return true;
		}
		return false;
	}

	/**
	 * This function to check permission user to edit post
	 * @param  [string] $userId  [description]
	 * @param  [string] $actorId [description]
	 * @param  [object] $obj     [description]
	 * @return [boolean]          [description]
	 */
	static public function activitiesStreamEditPost($userId,$actorId,$obj = NULL){
		$allowapp = array(	'profile',
							'groups.wall',
    						'events.wall',
                            'profile.status.share'
    					);

		$isAllowedApp = in_array($obj->app,$allowapp);

		if(( ($actorId == $userId) || COwnerHelper::isCommunityAdmin() || ( $obj->target == $userId && $actorId == $userId )) && $isAllowedApp){
			return true;
		}

		return false;
	}

	/**
	 * This Function to check permission for delete post
	 * @param  [string] $userId  [description]
	 * @param  [string] $actorId [description]
	 * @param  [object] $obj     [description]
	 * @return [boolean]          [description]
	 */
	static public function activitiesStreamDeletetPost($userId,$actorId,$obj = NULL){

        if(!$userId){
            return false;
        }

		if(COwnerHelper::isCommunityAdmin()){
			return true;
		}

		if(COwnerHelper::isCommunityAdmin() && $obj->app == 'users.featured'){
			return true;
		}

        //admin can delete system generated post
        $appType = isset($obj->app) ? explode('.',$obj->app) : false ;
        if(COwnerHelper::isCommunityAdmin() && $appType && $appType[0] == 'system'){
            return true;
        }

        $allowapp = array(	'profile',
            'groups.wall',
            'events.wall',
            'profile.status.share',
            'videos',
            'photos'
        );

        $disallowApp = array('photo.like', 'album.like');

        if(in_array($obj->app, $disallowApp)){
            return false;
        }

        $isAllowedApp = in_array($obj->app,$allowapp);
        $appAdmin = false;
        if($obj->app == "groups.wall"){
            $gTable = JTable::getInstance('Group','CTable');
            $gTable->load($obj->groupid);

            if($gTable->ownerid == $userId || $gTable->isAdmin($userId)){
                $appAdmin = true; //group admin
            }
        }elseif($obj->app=='events.wall'){
            $eventTable = JTable::getInstance('Event','CTable');
            $eventTable->load($obj->eventid);

            if($eventTable->creator == $userId || $eventTable->isAdmin($userId)){
                $appAdmin = true; // event admin
            }
        }

        if(( ($actorId == $userId) || COwnerHelper::isCommunityAdmin() || ( $obj->target == $userId )) || $appAdmin /*&& $isAllowedApp*/){
            return true;
        }

        return false;
	}

	static public function activitiesStreamAddLocation($userId,$actorId,$obj = NULL){

		$allowapp = array(	'profile',
							'groups.wall',
    						'events.wall'
    					);

		$isAllowedApp = in_array($obj->app,$allowapp);

		if(($userId == $actorId ||  COwnerHelper::isCommunityAdmin()) && empty($obj->location) && $isAllowedApp && CFactory::getConfig()->get('streamlocation',0)){
			return true;
		}

		return false;
	}

	static public function activitiesStreamEditLocation($userId,$actorId,$obj = NULL){

		$allowapp = array(	'profile',
							'groups.wall',
    						'events.wall'
    					);
		$isAllowedApp = in_array($obj->app,$allowapp);

		if(($userId == $actorId ||  COwnerHelper::isCommunityAdmin()) && !empty($obj->location) && $isAllowedApp && CFactory::getConfig()->get('streamlocation',0)){
			return true;
		}

		return false;
	}

    /**
     * @param $userid
     * @param $obj
     * @since 4.1
     */
    static public function activitiesStreamFeature($userid, $obj){
        $config = CFactory::getConfig();

        $streamInfo = isset($obj->extraInfo) ? $obj->extraInfo : '';

        //if feature stream is disabled, this will always be false
        if(!$config->get('featured_stream')){
            return false;
        }

        //determine the view
        $jinput = JFactory::getApplication()->input;
        $streamType = $jinput->get('view','','STRING');

        //higher precedence as this might be from ajax call where we cant ge the view type correctly
        if(isset($streamInfo['stream_type'])){
            $streamType = $streamInfo['stream_type']; //fortunately, we have the stream type from ajax
        }

        $featuredModel = CFactory::getModel('featured');
        $featuredLists = $featuredModel->getStreamFeaturedList();//current featured counts
        $totalFeatured = 0; //actual count of the featured item
        $limitCount = 0;

        $otherFlag = false;
        //check if the limit is over or not
        switch($streamType){
            case 'profile' :
                $limitCount = $config->get('stream_profile_featured');

                $profileId = (isset($streamInfo['profile_id'])) ? $streamInfo['profile_id'] : $userid;
                //for profile, if there is no userid, this should be my own profile
                $profileId = $jinput->get('userid',$profileId,'INT');

                if(isset($featuredLists['stream.profile'][$profileId])){
                    //we need to know which profile is this

                    foreach($featuredLists['stream.profile'][$profileId] as $profile){
                        if($profile->target_id == $profileId){
                            if($profile->cid == $obj->id){
                                return false; // indicates this stream has been featured, so it cannot be featured again
                            }
                            $totalFeatured++;
                        }
                    }
                }

                //echo $profileId;die;

                //if this is own profile
                if($userid == $profileId && $totalFeatured < $limitCount){
                    return true;
                }

                break;
            case 'frontpage' :
                $limitCount = $config->get('stream_frontpage_featured');

                if(!COwnerHelper::isCommunityAdmin()){
                    //instantly return false because only community admin can feature this
                    return false;
                }else{
                    //passed the identity check
                    $otherFlag = true;
                }

                //array 0 because it doesnt have any target id
                if(isset($featuredLists['stream.frontpage'][0])){
                    foreach($featuredLists['stream.frontpage'][0] as $feature){
                        if($feature->cid == $obj->id){
                            return false; // indicates this stream has been featured, so it cannot be featured again
                        }
                        $totalFeatured++;
                    }
                }
                break;
            case 'group' :
            case 'groups' :
                $limitCount = $config->get('stream_group_featured');
                //only community admin and group admin can do this
                $groupId = $obj->groupid;
                if(!$groupId){
                    return false;
                }
                $groupTable = JTable::getInstance('Group','CTable');
                $groupTable->load($groupId);

                if(!$groupTable->isAdmin($userid) && !COwnerHelper::isCommunityAdmin()){
                    return false;
                }else{
                    $otherFlag = true;
                }

                $limitCount = $config->get('stream_group_featured');

                if(isset($featuredLists['stream.group'][$groupId])){
                    foreach($featuredLists['stream.group'][$groupId] as $feature){
                        if($feature->cid == $obj->id){
                            return false; // indicates this stream has been featured, so it cannot be featured again
                        }
                        $totalFeatured++;
                    }
                }

                break;
            case 'event':
            case 'events' :
                $limitCount = $config->get('stream_event_featured');

                $eventId = $obj->eventid;
                if(!$eventId){
                    return false;
                }
                $eventTable = JTable::getInstance('Event','CTable');
                $eventTable->load($eventId);

                if(!$eventTable->isAdmin($userid) && !COwnerHelper::isCommunityAdmin()){
                    return false;
                }else{
                    $otherFlag = true;
                }

                $limitCount = $config->get('stream_event_featured');

                if(isset($featuredLists['stream.event'][$eventId])){
                    foreach($featuredLists['stream.event'][$eventId] as $feature){
                        if($feature->cid == $obj->id){
                            return false; // indicates this stream has been featured, so it cannot be featured again
                        }
                        $totalFeatured++;
                    }
                }

                break;
            default :
                return false;
        }

        //if the total featured item on specific stream type meet the quota, it cannot be featured anymore
        if($totalFeatured >= $limitCount){
            return false;
        }

        //community admin can feature as long as the permission passed on top of this
        if(COwnerHelper::isCommunityAdmin() || $otherFlag){
            return true;
        }

    }

    /**
     * check if current user can unfeature the stream
     * @param $userId
     * @param $obj
     */
    static public function activitiesStreamUnfeature($userId, $obj){

        //before we unfeature, we must make sure the already been featured
        $config = CFactory::getConfig();

        $streamInfo = isset($obj->extraInfo) ? $obj->extraInfo : '';

        //if feature stream is disabled, this will always be false
        if(!$config->get('featured_stream')){
            return false;
        }

        //determine the view
        $jinput = JFactory::getApplication()->input;
        $streamType = $jinput->get('view','','STRING');

        //higher precedence as this might be from ajax call where we cant ge the view type correctly
        if(isset($streamInfo['stream_type'])){
            $streamType = $streamInfo['stream_type']; //fortunately, we have the stream type from ajax
        }

        $featuredModel = CFactory::getModel('featured');
        $featuredLists = $featuredModel->getStreamFeaturedList();//current featured counts

        $lists = array();

        $otherFlag = false;
        switch($streamType){
            case 'frontpage':
                $lists = isset($featuredLists['stream.frontpage'][0]) ? $featuredLists['stream.frontpage'][0] : array();
                break;
            case 'profile':
                $profileId = (isset($streamInfo['profile_id'])) ? $streamInfo['profile_id'] : $userId;
                //for profile, if there is no userid, this should be my own profile
                $profileId = $jinput->get('userid',$profileId,'INT');

                if($profileId == $userId){
                    $otherFlag = true;
                }

                $lists = isset($featuredLists['stream.profile'][$profileId]) ? $featuredLists['stream.profile'][$profileId] : array();
                break;
            case 'group':
            case 'groups':
                $groupId = $obj->groupid;
                if(!$groupId){
                    return false;
                }
                $groupTable = JTable::getInstance('Group','CTable');
                $groupTable->load($groupId);

                if(!$groupTable->isAdmin($userId) && !COwnerHelper::isCommunityAdmin()){
                    return false;
                }else{
                    $otherFlag = true;
                }

                $lists = isset($featuredLists['stream.group'][$obj->groupid]) ? $featuredLists['stream.group'][$obj->groupid] : array();
                break;
            case 'event':
            case 'events':
                $eventId = $obj->eventid;
            if(!$eventId){
                    return false;
                }
                $eventTable = JTable::getInstance('Event','CTable');
                $eventTable->load($eventId);

                if(!$eventTable->isAdmin($userId) && !COwnerHelper::isCommunityAdmin()){
                    return false;
                }else{
                    $otherFlag = true;
                }

                $lists = isset($featuredLists['stream.event'][$obj->eventid]) ? $featuredLists['stream.event'][$obj->eventid] : array();
                break;
            default :
                $lists = isset($featuredLists['stream.frontpage'][0]) ? $featuredLists['stream.frontpage'][0] : array();
                break;
        }

        foreach($lists as $list){
            if($list->cid == $obj->id){
                //found this id in the feature list
                if(COwnerHelper::isCommunityAdmin() || $otherFlag){
                    return true;
                }else{
                    return false;
                }
            }
        }

        return false;
    }

	static public function activitiesStreamPermission($userId,$actorId,$obj){

		$permission = new StdClass();

		$permission->editPost		= self::activitiesStreamEditPost($userId,$actorId,$obj);
		$permission->deletePost		= self::activitiesStreamDeletetPost($userId,$actorId,$obj);
		$permission->addLocation	= self::activitiesStreamAddLocation($userId,$actorId,$obj);
		$permission->deleteLocation	= self::activitiesStreamEditLocation($userId,$actorId,$obj);
		$permission->addMood		= self::activitiesStreamAddMood($userId,$actorId,$obj);
		$permission->deleteMood		= self::activitiesStreamEditMood($userId,$actorId,$obj);
		$permission->hideStream		= self::activitiesStreamHide($userId,$actorId, $obj);
        $permission->featureActivity= self::activitiesStreamFeature($userId,$obj);
        $permission->unfeatureActivity= self::activitiesStreamUnfeature($userId, $obj);
        $permission->ignoreStream   = self::activitiesStreamIgnore($userId,$actorId,$obj);
		$permission->showButton 	= ($permission->editPost || $permission->deletePost || $permission->addLocation ||
										$permission->deleteLocation || $permission->addMood || $permission->deleteMood || $permission->hideStream
        || $permission->featureActivity || $permission->unfeatureActivity || $permission->ignoreStream
        );

		return $permission;
	}

    static public function activitiesStreamIgnore($userId, $actorId, $obj = NULL){
        //ignore anything related to comment
        if(isset($obj->app) && strpos($obj->app, 'comment') !== false){
            return false;
        }

        if($userId != $actorId && !COwnerHelper::isCommunityAdmin($actorId)){
            return true;
        }

        if($obj->target == 0){
            return false;
        }

        return false;
    }

	static public function activitiesLikeAdd($userId, $assetId, $obj=NULL){

        //some activity that cannot be liked
        $cannotLikeApp = array('photos.comment');
        if(isset($obj->app) && in_array($obj->app, $cannotLikeApp)){
            return false;
        }

		// Guest can never leave a comment
		if( $userId == 0 ){
			return false;
		}

		if(isset($obj->params)) {
			$params = new CParameter($obj->params);
			$likesPermission = $params->get('likesPermission',NULL);

			if( $likesPermission == false  && !is_null($likesPermission))
			{
				return false;
			}
		}

		return true;
	}
}