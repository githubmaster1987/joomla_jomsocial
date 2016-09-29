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

Class CVideosAccess implements CAccessInterface
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

	static public function videosTagRemove($userid, $asset){
		//condition: only owner can remove the tag
		$video	= JTable::getInstance( 'Video' , 'CTable' );
		$video->load( $asset );
		if($userid == $video->creator){
			return true;
		}else{
			return false;
		}
	}

    static public function videosCreate($userid)
    {
        $config = CFactory::getConfig();

        // FALSE user not logged in
        if(!$userid) {
            echo "<!--".__FUNCTION__.__LINE__."-->";
            return false;
        }
        // FALSE globally disabled
        if(!$config->get('enablevideos')) {
            echo "<!--".__FUNCTION__.__LINE__."-->";
            return false;
        }

        echo "<!--".__FUNCTION__.__LINE__."-->";
        return true;
    }

    static public function videosDelete($userid, $video)
    {
        if(!$userid){
            return false;
        }elseif(COwnerHelper::isCommunityAdmin() || $video->isOwner()){
            return true;
        }

        //now we need to check if the current video is group video or event video or not
        if($video->groupid){
            //this will cater both group and group event
            $groupTable = JTable::getInstance('Group', 'CTable');
            $groupTable->load($video->groupid);

            return $groupTable->isAdmin($userid) ? true : false;

        }elseif($video->eventid){
            $eventTable = JTable::getInstance('Event', 'CTable');
            $eventTable->load($video->eventid);

            return $eventTable->isAdmin($userid) ? true : false;

        }

        return false;
    }


    static public function videosEdit($userid, $video)
    {
        //only owner or community admin can edit
        if(!$userid){
            return false;
        }elseif(COwnerHelper::isCommunityAdmin() || $video->isOwner()){
            return true;
        }

        return false;
    }

    static public function videosUserVideoView($userid, $asset){
        //first
        $video	= JTable::getInstance( 'Video' , 'CTable' );
        $video->load( $asset );

        if($userid == $video->creator || COwnerHelper::isCommunityAdmin()){
            return true; // creator always be able to view his own album
        }

        $owner = CFactory::getUser($video->creator);
        $permission = $video->permissions;

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
}