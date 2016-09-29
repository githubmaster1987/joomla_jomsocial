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

class CLikesHelper
{
	/**
	 * Generate HTML for like
	 * @param  [object] $obj [description]
	 * @return [string]      [description]
	 */
	static public function generateHTML($obj, &$likedContent)
	{
		$data 			= self::generateHTMLData($obj);

		$data->users 	= self::getActor($obj->params);
		$data->userCount = count($data->users);
		$actorsHTML = array();
		$slice 		= 2;
		$others = '';

        if ( isset ( $data->likedContent) ) {
            if(isset($data->urlLink)){
                $likedContent = (object)array_merge((array)$data->likedContent,array('url_link' => $data->urlLink));
            }else{
                $likedContent = $data->likedContent;
            }
        }else {
            $likedContent = null;
        }

        if($data->userCount > 2)
        {
            $slice = 1;
            $others = JText::sprintf('COM_COMMUNITY_STREAM_OTHERS_JOIN_GROUP' , $data->userCount-1,'onClick="joms.api.streamShowOthers('.$obj->id.');return false;"');
        }

        $users = array_slice($data->users,0,$slice);

        foreach($users as $actor) {
            $user = CFactory::getUser($actor);
            $actorsHTML[] = '<a class="cStream-Author" href="'. CUrlHelper::userLink($user->id).'">'. $user->getDisplayName().'</a>';
        }

		$jtext =($data->userCount > 1) ? 'COM_COMMUNITY_STREAM_LIKES_PLURAL' : 'COM_COMMUNITY_STREAM_LIKES_SINGULAR';
        $connecting = '';
        /**
         * Get owner of item
         * @todo Need improve this later
         */
        if ( $data->userCount == 1 ) {
            $me = CFactory::getUser();
            $firstChar = strtolower(substr(JText::_($data->element),0,-(strlen(JText::_($data->element))-1)));
            $vowl = array('a','e','i','o','u');
            if(in_array($firstChar, $vowl)){
              $connecting = JText::_('COM_COMMUNITY_AN');
            } else {
              $connecting = JText::_('COM_COMMUNITY_A');
            }
        }

        switch ( $obj->app ) {
            case 'photo.like':
                /**
                 * @todo
                 * Replace language string with standard. For now using alt language string to prevent conflict
                 */
                $jtext = ($data->userCount > 1) ? 'COM_COMMUNITY_STREAM_LIKES_PLURAL_PHOTO' : 'COM_COMMUNITY_STREAM_LIKES_SINGULAR_PHOTO';
                /* Get photo record to know who's owner */
                $table = JTable::getInstance('Photo','CTable');
                $table->load($obj->cid);
                if ( $table ) {
                    $targetOwner = CFactory::getUser($table->creator);
                    $ownerText = JText::sprintf('COM_COMMUNITY_NOGENDER_OWNER', $targetOwner->getDisplayName());
                    return implode( ' '. JText::_('COM_COMMUNITY_AND') . ' ' , $actorsHTML)
                    . JText::sprintf($jtext,
                        CUrlHelper::userLink($targetOwner->id),
                        $ownerText,
                        $data->urlLink,
                        strtolower(JText::_($data->element))
                    );
                }
                break;
        }

        //special case for profile like
        if($obj->app == 'profile.like'){
            $jtext =($data->userCount > 1) ? 'COM_COMMUNITY_STREAM_LIKES_PROFILE_PLURAL' : 'COM_COMMUNITY_STREAM_LIKES_PROFILE_SINGULAR';
            return implode( ' '. JText::_('COM_COMMUNITY_AND') . ' ' , $actorsHTML).$others.JText::sprintf($jtext,$data->urlLink,$data->name,JText::_($data->element)) ;
        }

		return implode( ' '. JText::_('COM_COMMUNITY_AND') . ' ' , $actorsHTML).$others.JText::sprintf($jtext,$data->urlLink,JText::_($data->element),$connecting ) ;
	}
	/**
	 * Get Actor value
	 * @param  [JParameter] $params [description]
	 * @return [array]         [description]
	 */
	static public function getActor($params) {

		$users = $params->get('actors');

        //some are store in array
        if(is_array($users)){
            return $users; //since 3.3 array already sorted from latest at the front
        }
		return array_reverse(explode(',', $users));
	}

	/**
	 * Generate like string
	 * @param  [object] $obj [description]
	 * @return [object]      [description]
	 */
	static public function generateHTMLData($obj) {
		$dataObj = new stdClass();

		switch($obj->app){
			case 'profile.like':
				$cid 		= CFactory::getUser($obj->cid);

				$dataObj->urlLink 	= CUrlHelper::userLink($cid->id);
				$dataObj->name 		= $cid->getDisplayName();
				$dataObj->element	= 'COM_COMMUNITY_STREAM_LIKES_ELEMENT_PROFILE';

                                /* Do prepare content for liked item
                                * @since 3.2
                                * @todo Need check if load success
                                */
                               $likedItem = new stdClass();
                               $likedItem->title = $cid->name;
                               $likedItem->content = $cid->get('description');
                               $likedItem->thumb = $cid->getAvatar();
                               $dataObj->likedContent = $likedItem;
			break;
			case 'groups.like':
				$cid = JTable::getInstance('Group', 'CTable');
				$cid->load($obj->groupid);

				$dataObj->urlLink 	= $cid->getLink();
				$dataObj->name 		= $cid->name;
				$dataObj->element	= 'COM_COMMUNITY_SINGULAR_GROUP';

			break;
			case 'events.like':
				$cid = JTable::getInstance('Event','CTable');
				$cid->load($obj->eventid);

				$dataObj->urlLink 	= $cid->getLink();
				$dataObj->name 		= $cid->title;
				$dataObj->element	= 'COM_COMMUNITY_SINGULAR_EVENT';
                                /* Do prepare content for liked item
                                * @since 3.2
                                * @todo Need check if load success
                                */
                               $likedItem = new stdClass();
                               $likedItem->title = $cid->title;
                               $likedItem->content = $cid->description;
                               $likedItem->thumb = $cid->getAvatar();
                               $dataObj->likedContent = $likedItem;
			break;
			case 'photo.like':
				$cid = JTable::getInstance('Photo','CTable');
				$cid->load($obj->cid);

                $config = CFactory::getConfig();
                $isPhotoModal = $config->get('album_mode') == 1;

                $dataObj->urlLink   = $cid->getPhotoLink();
				$dataObj->urlLink 	= $isPhotoModal ? ( 'javascript:" onclick="joms.api.photoOpen(\'' . $cid->albumid . '\', \'' . $cid->id . '\');' ) : $dataObj->urlLink;
				$dataObj->name 		= $cid->caption;
				$dataObj->element	= 'COM_COMMUNITY_STREAM_LIKES_ELEMENT_PHOTO_SINGLE';
                                /* Do prepare content for liked item
                                * @since 3.2
                                * @todo Need check if load success
                                */
                               $likedItem = new stdClass();
                               $likedItem->title = $cid->caption;
                               $likedItem->content = '';
                               $likedItem->thumb = $cid->getImageURI();
                               $dataObj->likedContent = $likedItem;
			break;
			case 'videos.like':
				$cid = JTable::getInstance('Video','CTable');
				$cid->load($obj->cid);

                $config = CFactory::getConfig();
                $isVideoModal = $config->get('video_mode') == 1;

				$dataObj->urlLink 	= $cid->getViewURI();
                $dataObj->urlLink   = $isVideoModal ? ( 'javascript:" onclick="joms.api.videoOpen(\'' . $cid->id . '\');' ) : $dataObj->urlLink;
				$dataObj->name 		= $cid->getTitle();
				$dataObj->element	= 'COM_COMMUNITY_SINGULAR_VIDEO';
                                /* Do prepare content for liked item
                                * @since 3.2
                                * @todo Need check if load success
                                */
                               $likedItem = new stdClass();
                               $likedItem->title = $cid->title;
                               $likedItem->content = $cid->description;
                               $likedItem->thumb = $cid->getThumbnail();
                               $likedItem->media = $cid->getPlayerHTML();
                               $dataObj->likedContent = $likedItem;
			break;
			case 'album.like':
				$cid = JTable::getInstance('Album','CTable');
				$cid->load($obj->cid);

        $config = CFactory::getConfig();
        $isPhotoModal = $config->get('album_mode') == 1;

        $dataObj->urlLink   = $cid->getURI();
        $dataObj->urlLink   = $isPhotoModal ? ( 'javascript:" onclick="joms.api.photoOpen(\'' . $cid->id . '\', \'\');' ) : $dataObj->urlLink;

				$dataObj->name 		= $cid->name;
				$dataObj->element	= 'COM_COMMUNITY_STREAM_LIKES_ELEMENT_ALBUM';
        /* Do prepare content for liked item
        * @since 3.2
        * @todo Need check if load success
        */
       $likedItem = new stdClass();
       $likedItem->title = $cid->name;
       $likedItem->content = $cid->description;
       $likedItem->thumb = $cid->getCoverThumbPath();
       $dataObj->likedContent = $likedItem;
			break;
		}
		return $dataObj;
	}
}
