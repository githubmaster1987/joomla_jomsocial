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

    require_once JPATH_ROOT . '/components/com_community/libraries/core.php';

    class CMiniHeader
    {

        public static function load()
        {
            $jspath = JPATH_BASE . '/components/com_community';
            include_once $jspath . '/libraries/template.php';

            $config = CFactory::getConfig();

            CTemplate::addStyleSheet('style');
        }

        public static function showMiniHeader($userId)
        {
            CMiniHeader::load();

            $mainframe = JFactory::getApplication();
            $jinput = $mainframe->input;

            JFactory::getLanguage()->load('com_community');

            $option = $jinput->get('option', '', 'STRING');
            $my = CFactory::getUser();
            $config = CFactory::getConfig();

            if (!empty($userId)) {
                $user = CFactory::getUser($userId);

                $params = $user->getParams();

                //links information
                $photoEnabled = ($config->get('enablephotos')) ? true : false;
                $eventEnabled = ($config->get('enableevents')) ? true : false;
                $groupEnabled = ($config->get('enablegroups')) ? true : false;
                $videoEnabled = ($config->get('enablevideos')) ? true : false;

                //likes
                CFactory::load('libraries', 'like');
                $like = new Clike();
                $isLikeEnabled = $like->enabled('profile') && $params->get('profileLikes', 1) ? 1 : 0;
                $isUserLiked = $like->userLiked('profile', $user->id, $my->id);
                /* likes count */
                $likes = $like->getLikeCount('profile', $user->id);

                //profile
                $profileModel = CFactory::getModel('profile');
                $profile = $profileModel->getViewableProfile($user->id, $user->getProfileType());
                $profile = Joomla\Utilities\ArrayHelper::toObject($profile);
                $profile->largeAvatar = $user->getAvatar();
                $profile->defaultAvatar = $user->isDefaultAvatar();

                // Find avatar album.
                $album = JTable::getInstance('Album', 'CTable');
                $albumId = $album->isAvatarAlbumExists($user->id, 'profile');
                $profile->avatarAlbum = $albumId ? $albumId : false;

                $profile->status = $user->getStatus();
                $profile->defaultCover = $user->isDefaultCover();
                $profile->cover = $user->getCover();
                $profile->coverPostion = $params->get('coverPosition', '');

                if (strpos($profile->coverPostion, '%') === false) {
                    $profile->coverPostion = 0;
                }

                $groupmodel = CFactory::getModel('groups');
                $profile->_groups = $groupmodel->getGroupsCount($profile->id);

                $eventmodel = CFactory::getModel('events');
                $profile->_events = $eventmodel->getEventsCount($profile->id);

                $profile->_friends = $user->_friendcount;

                $videoModel = CFactory::getModel('Videos');
                $profile->_videos = $videoModel->getVideosCount($profile->id);

                $photosModel = CFactory::getModel('photos');
                $profile->_photos = $photosModel->getPhotosCount($profile->id);

                /* is featured */
                $modelFeatured = CFactory::getModel('Featured');
                $profile->featured = $modelFeatured->isExists(FEATURED_USERS, $profile->id);

                $sendMsg = CMessaging::getPopup($user->id);
                $tmpl = new CTemplate();

                $tmpl->set('my', $my)
                    ->set('user', $user)
                    ->set('isBlocked', $user->isBlocked())
                    ->set('isMine', COwnerHelper::isMine($my->id, $user->id))
                    ->set('sendMsg', $sendMsg)
                    ->set('config', $config)
                    ->set('isWaitingApproval', CFriendsHelper::isWaitingApproval($my->id, $user->id))
                    ->set('isLikeEnabled', $isLikeEnabled)
                    ->set('photoEnabled', $photoEnabled)
                    ->set('eventEnabled', $eventEnabled)
                    ->set('groupEnabled', $groupEnabled)
                    ->set('videoEnabled', $videoEnabled)
                    ->set('profile', $profile)
                    ->set('isUserLiked', $isUserLiked)
                    ->set('likes', $likes)
                    ->set('isFriend', CFriendsHelper::isConnected($user->id, $my->id) && $user->id != $my->id);
                $showMiniHeader = $option == 'com_community' ? $tmpl->fetch('profile.miniheader') : '<div id="community-wrap" style="min-height:50px;">' . $tmpl->fetch('profile.miniheader') . '</div>';

                return $showMiniHeader;
            }
        }

        public static function showGroupMiniHeader($groupId)
        {
            CMiniHeader::load();

            $mainframe = JFactory::getApplication();
            $jinput = $mainframe->input;

            $option = $jinput->request->get('option', '', 'STRING');
            JFactory::getLanguage()->load('com_community');

            $group = JTable::getInstance('Group', 'CTable');
            $group->load($groupId);
            $my = CFactory::getUser();
            $isBanned = $group->isBanned($my->id);

            // @rule: Test if the group is unpublished, don't display it at all.
            if (!$group->published) {
                return '';
            }


            if (!empty($group->id) && $group->id != 0) {
                $fileModel = CFactory::getModel('files');
                $params = $group->getParams();
                $isMember = $group->isMember($my->id);
                $config = CFactory::getConfig();

                $eventsModel = CFactory::getModel('Events');
                $totalEvents = $eventsModel->getTotalGroupEvents($group->id);

                $discussModel = CFactory::getModel('discussions');
                $discussions = $discussModel->getDiscussionTopics($group->id, '10', 0);
                $totalDiscussion = $discussModel->total;

                $bulletinModel = CFactory::getModel('bulletins');
                $bulletins = $bulletinModel->getBulletins($groupId);
                $totalBulletin = $bulletinModel->total;

                $allowManagePhotos = CGroupHelper::allowManagePhoto($group->id);
                $allowManageVideos = CGroupHelper::allowManageVideo($group->id);
                $allowCreateEvent = CGroupHelper::allowCreateEvent($my->id, $group->id);

                $photosModel = CFactory::getModel('photos');
                $albums = $photosModel->getGroupAlbums($group->id, false, false);
                $totalPhotos = 0;
                foreach ($albums as $album) {
                    $albumParams = new CParameter($album->params);
                    $totalPhotos = $totalPhotos + $albumParams->get('count');
                }

                $videoModel = CFactory::getModel('videos');
                $tmpVideos = $videoModel->getGroupVideos($groupId, '',
                    $params->get('grouprecentvideos', GROUP_VIDEO_RECENT_LIMIT));
                $totalVideos = $videoModel->total ? $videoModel->total : 0;

                // Get like
                $likes = new CLike();
                $isUserLiked = false;
                if ($isLikeEnabled = $likes->enabled('groups')) {
                    $isUserLiked = $likes->userLiked('groups', $group->id, $my->id);
                }
                $totalLikes = $likes->getLikeCount('groups', $group->id);

                $tmpl = new CTemplate();

                $groupModel = CFactory::getModel('groups');

                $membersCount = $group->membercount;

                // If I have tried to join this group, but not yet approved, display a notice
                $waitingApproval = false;
                if ($groupModel->isWaitingAuthorization($my->id, $group->id)) {
                    $waitingApproval = true;
                }

                $groupsModel = CFactory::getModel('groups');
                $bannedMembers = $groupsModel->getBannedMembers($group->id);

                $tmpl->set('my', $my)
                    ->set('isBanned', $isBanned)
                    ->set('group', $group)
                    ->set('membersCount', $membersCount)
                    ->set('showEvents',
                        $config->get('group_events') && $config->get('enableevents') && $params->get('eventpermission',
                            1) >= 1)
                    ->set('totalEvents', $totalEvents)
                    ->set('totalDiscussion', $totalDiscussion)
                    ->set('totalBulletin', $totalBulletin)
                    ->set('showPhotos',
                        ($params->get('photopermission') != -1) && $config->get('enablephotos') && $config->get('groupphotos'))
                    ->set('showVideos',
                        ($params->get('videopermission') != -1) && $config->get('enablevideos') && $config->get('groupvideos'))
                    ->set('isSuperAdmin', COwnerHelper::isCommunityAdmin())
                    ->set('isMine', ($my->id == $group->ownerid))
                    ->set('totalVideos', $totalVideos)
                    ->set('totalPhotos', $totalPhotos)
                    ->set('isAdmin', $groupModel->isAdmin($my->id, $group->id))
                    ->set('isFile', $fileModel->isfileAvailable($group->id, 'group'))
                    ->set('isLikeEnabled', $isLikeEnabled)
                    ->set('totalLikes', $totalLikes)
                    ->set('isMember', $isMember)
                    ->set('config', $config)
                    ->set('totalBannedMembers', count($bannedMembers))
                    ->set('isUserLiked', $isUserLiked)
                    ->set('allowManagePhotos', $allowManagePhotos)
                    ->set('allowManageVideos', $allowManageVideos)
                    ->set('allowCreateEvent', $allowCreateEvent)
                    ->set('waitingApproval', $waitingApproval);

                $showMiniHeader = $option == 'com_community' ? $tmpl->fetch('groups/miniheader') : '<div id="community-wrap">' . $tmpl->fetch('groups/miniheader') . '</div>';

                return $showMiniHeader;
            }
        }

        public static function showEventMiniHeader($id){

            if(!$id){
                return;
            }
            $event = JTable::getInstance('Event', 'CTable');
            $event->load($id);
            $my = CFactory::getUser();
            $config = CFactory::getConfig();

            $mainframe = JFactory::getApplication();
            $jinput = $mainframe->input;
            $option = $jinput->request->get('option', '', 'STRING');

            $enableReporting = ( $config->get('enablereporting') == 1 && $config->get('enableguestreporting') != 1 && $my->id != 0 );
            $handler = CEventHelper::getHandler($event);

            $myStatus = $event->getUserStatus($my->id);
            $isEventGuest = $event->isMember($my->id);
            $isAdmin = $event->isAdmin($my->id);
            $unapprovedCount = $event->inviteRequestCount();
            $eventMembersCount = $event->getMembersCount(COMMUNITY_EVENT_STATUS_ATTEND);


            // Get like
            $likes = new CLike();
            $isUserLiked = false;
            if ($isLikeEnabled = $likes->enabled('events')) {
                $isUserLiked = $likes->userLiked('events', $event->id, $my->id);
            }
            $totalLikes = $likes->getLikeCount('events', $event->id);

            $params = new CParameter($event->params);

            $event->coverPostion = $params->get('coverPosition', '');
            $event->defaultCover = $event->isDefaultCover();

            //gets all the albums related to this photo
            $photosModel = CFactory::getModel('photos');
            $albums = $photosModel->getEventAlbums($event->id);
            $totalPhotos = 0;
            foreach($albums as $album){
                $albumParams = new CParameter($album->params);
                $totalPhotos = $totalPhotos + $albumParams->get('count');
            }

            //get total videos
            $videosModel = CFactory::getModel('videos');
            $totalVideos = count($videosModel->getEventVideos($event->id));

            $now = new JDate();
            $tmpl = new CTemplate();
            $tmpl->set('event', $event)
                ->set('isAdmin', $isAdmin)
                ->set('waitingRespond', false)
                ->set('isUserLiked', $isUserLiked)
                ->set('totalLikes', $totalLikes)
                ->set('creator', CFactory::getUser($event->creator))
                ->set('unapproved', $unapprovedCount)
                ->set('isLikeEnabled', $isLikeEnabled)
                ->set('eventMembersCount', $eventMembersCount)
                ->set('memberStatus', $myStatus)
                ->set('isEventGuest', $isEventGuest)
                ->set('enableReporting', $enableReporting)
                ->set('isPastEvent', ($event->getEndDate(false)->toSql() < $now->toSql(true)) ? true : false)
                ->set('isMine', $event->isCreator($my->id))
                ->set('showPhotos', ( $params->get('photopermission') != -1 ) && $config->get('enablephotos') && $config->get('eventphotos'))
                ->set('showVideos', ( $params->get('videopermission') != -1 ) && $config->get('enablevideos') && $config->get('eventvideos'))
                ->set('totalPhotos', $totalPhotos)
                ->set('totalVideos', $totalVideos)
                ->set('handler', $handler);

            return $option == 'com_community' ? $tmpl->fetch('events/miniheader') : '<div id="community-wrap">' . $tmpl->fetch('events/miniheader') . '</div>';
        }

    }
