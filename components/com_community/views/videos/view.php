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

class CommunityViewVideosHelper extends CommunityView
{
    var $_videoLib  = null;
    var $model      = '';

    public function CommunityViewVideos()
    {
        //CFactory::load( 'helpers', 'videos' );
        //CFactory::load( 'libraries' , 'videos' );
        $this->model    = CFactory::getModel('videos');
        $this->videoLib = new CVideoLibrary();
    }

    public function _getVideosHTML( $videos )
    {
        $mainframe  = JFactory::getApplication();
        $jinput     = $mainframe->input;

        $videos = $videos ? CVideosHelper::prepareVideos($videos) : array();
        $my     = CFactory::getUser();
        $user   = CFactory::getUser($jinput->get('userid',0,'Int'));

        // for featured/unfeatured link
        $featured   = new CFeatured( FEATURED_VIDEOS );
        $featuredVideos = $featured->getItemIds();
        $featuredList   = array();

        foreach($featuredVideos as $videoId )
        {
            $featuredList[] = $videoId;
        }

        $allowManageVideos  = true;
        $groupVideo         = false;
        $groupId            = $jinput->get->get('groupid', '' , 'INT');

        $task           = $jinput->get->get('task', '' , 'WORD');
        $redirectUrl    = CRoute::getURI( false );

        if( !empty($groupId) )
        {
            $allowManageVideos  = CGroupHelper::allowManageVideo($groupId);
            $groupVideo         = true;
        }

        $tmpl   = new CTemplate();
        $tmpl->set( 'sort'              , $jinput->get('sort', 'latest', 'STRING') );
        $tmpl->set( 'currentTask'       , JInput::get( 'task' , '') );
        $tmpl->set( 'videos'            , $videos );
        $tmpl->set( 'videoThumbWidth'   , CVideoLibrary::thumbSize('width') );
        $tmpl->set( 'videoThumbHeight'  , CVideoLibrary::thumbSize('height') );
        $tmpl->set( 'redirectUrl'       , $redirectUrl );
        $tmpl->set( 'my'                , $my );
        $tmpl->set( 'user'              , $user );
        $tmpl->set( 'featuredList'      , $featuredList );
        $tmpl->set( 'isCommunityAdmin'  , COwnerHelper::isCommunityAdmin() );
        $tmpl->set( 'allowManageVideos' , $allowManageVideos );
        $tmpl->set( 'groupVideo'        , $groupVideo);

        return $tmpl->fetch( 'videos/list' );
    }

    /**
     *  Get Featured Videos
     *
     *  @return     array   Objects of random featured videos
     *  @since      1.5
     */
    public function _getFeatVideos()
    {
        $featured   = new CFeatured( FEATURED_VIDEOS );
        $featuredVideos = $featured->getItemIds();
        $featuredList   = array();

        foreach($featuredVideos as $videoId )
        {
            $table          = JTable::getInstance( 'Video' , 'CTable' );
            $table->load($videoId);
            $table->loadExtra();

            $featuredList[] = $table;
        }

        return $featuredList;
    }

    /**
     *  Generate Featured Videos HTML
     *
     *  @param      array   Array of video objects
     *  @return     string  HTML
     *  @since      1.2
     */
    public function _getFeatHTML($videos)
    {
        $tmpl   = new CTemplate();
        $tmpl->set( 'videos'        , $videos );
        $tmpl->set( 'isCommunityAdmin' , COwnerHelper::isCommunityAdmin() );
        $tmpl->set( 'videoThumbWidth'   , CVideoLibrary::thumbSize('width') );
        $tmpl->set( 'videoThumbHeight'  , CVideoLibrary::thumbSize('height') );

        return $tmpl->fetch( 'videos.featured' );
    }

    /**
     * Display all videos in the whole system
     **/
    public function display($id= null)
    {
        $mainframe  = JFactory::getApplication();
        $jinput     = $mainframe->input;

        $document   = JFactory::getDocument();
        $my         = CFactory::getUser();
        $model      = CFactory::getModel('videos');


        $this->_addSubmenu();
        $this->showSubmenu();


        // Get category id from the query string if there are any.
        $categoryId     = $jinput->get( 'catid' , 0 , 'Int' );
        $category       = JTable::getInstance( 'VideosCategory' , 'CTable' );
        $category->load( $categoryId );


        // If we are browing by category, add additional breadcrumb and add
        // category name in the page title
        if($categoryId != 0)
        {
            $this->addPathway( JText::_('COM_COMMUNITY_VIDEOS_CATEGORIES') , CRoute::_('index.php?option=com_community&view=videos') );
            $this->addPathway( JText::_( $this->escape( $category->name ) ) , '' );
            $document->setTitle(JText::_( 'COM_COMMUNITY_VIDEOS_CATEGORIES' ) . ' : ' . JText::_( $this->escape( $category->name ) ) );
        }
        else
        {
            $this->addPathway( JText::_( 'COM_COMMUNITY_VIDEOS' ) );
            $document->setTitle(JText::_( 'COM_COMMUNITY_VIDEOS_BROWSE_ALL_VIDEOS' ));
        }

        $groupId    = $jinput->get->get('groupid' , '', 'INT');

        // Featured Videos
        $featVideos     = '';

        if( !empty($groupId) )
        {
            $group      = JTable::getInstance( 'Group' , 'CTable' );
            $group->load( $groupId );

            $isMember   = $group->isMember( $my->id );
            $isMine     = ($my->id == $group->ownerid);
            if( !$isMember && !$isMine && !COwnerHelper::isCommunityAdmin() && $group->approvals == COMMUNITY_PRIVATE_GROUP )
            {
                echo JText::_('COM_COMMUNITY_GROUPS_PRIVATE_NOTICE');
                return;
            }

            $videos         = $model->getGroupVideos($groupId, $category->id);
            $allVideosUrl   = 'index.php?option=com_community&view=videos&task=display&groupid='.$groupId;
            $catVideoUrl    = 'index.php?option=com_community&view=videos&groupid='.$groupId.'&catid=';
            $categories     = $model->getCategories();
        }
        else
        {
            $filters    = array
            (
                'status'        => 'ready',
                'category_id'   => $category->id,
                'permissions'   => ($my->id==0) ? 0 : 20,
                'or_group_privacy'  => 0,
                'sorting'       => $jinput->get('sort', 'latest','STRING')
            );
            $videos         = $model->getVideos($filters);
            $allVideosUrl   = 'index.php?option=com_community&view=videos';
            $catVideoUrl    = 'index.php?option=com_community&view=videos&task=display&catid=';

            // Featured Videos
            $featVideos     = $this->_getFeatVideos();
            $categories = $model->getCategories();
        }

        $videosHTML     = $this->_getVideosHTML( $videos );
        $featuredHTML   = '';
        if ( $featVideos )
        {
            $featuredHTML   = $this->_getFeatHTML( $featVideos );
        }


        $pagination = $model->getPagination();

        $sortItems  = array
        (
            'latest'        => JText::_('COM_COMMUNITY_VIDEOS_SORT_LATEST'),
            'mostwalls'     => JText::_('COM_COMMUNITY_VIDEOS_SORT_MOST_WALL_POST'),
            'mostviews'     => JText::_('COM_COMMUNITY_VIDEOS_SORT_POPULAR'),
            'title'         => JText::_('COM_COMMUNITY_VIDEOS_SORT_TITLE')
        );

        $tmpl   = new CTemplate();
        $tmpl->set( 'sort'          , $jinput->get('sort', 'latest','STRING') );
        $tmpl->set( 'currentTask'   , JInput::get( 'task' , '') );
        $tmpl->set( 'featuredHTML'  , $featuredHTML );
        $tmpl->set( 'videosHTML'    , $videosHTML );
        $tmpl->set( 'categories'    , $categories );
        $tmpl->set( 'pagination'    , $pagination );
        $tmpl->set( 'sortings'      , CFilterBar::getHTML( CRoute::getURI(), $sortItems, 'latest') );
        $tmpl->set( 'allVideosUrl'  , $allVideosUrl );
        $tmpl->set( 'catVideoUrl'   , $catVideoUrl );

        echo $tmpl->fetch( 'videos/base' );
    }

    /**
     * Application full view
     **/
    public function appFullView()
    {
        $mainframe  = JFactory::getApplication();
        $jinput     = $mainframe->input;

        $document       = JFactory::getDocument();
        $document->setTitle( JText::_('COM_COMMUNITY_VIDEOS_WALL_TITLE') );

        $applicationName    = JString::strtolower( $jinput->get->get( 'app' , '' , 'STRING') );

        if(empty($applicationName))
        {
            JFactory::getApplication()->enqueueMessage(JText::_('COM_COMMUNITY_APP_ID_REQUIRED'), 'error');
        }

        $output = '';

        if( $applicationName == 'walls' )
        {
            //CFactory::load( 'libraries' , 'wall' );
            $limit      = $jinput->request->get( 'limit' , 5 ,'INT');
            $limitstart = $jinput->request->get( 'limitstart', 0 ,'INT');
            $videoId    = $jinput->get->get( 'videoid' , 0 , 'Int' );
            $my         = CFactory::getUser();
            $config     = CFactory::getConfig();

            $videoModel = CFactory::getModel( 'videos' );
            $video      = JTable::getInstance( 'Video' , 'CTable' );
            $video->load( $videoId );

            // Get the walls content
            $output         .='<div id="wallContent">';
            $output         .= CWallLibrary::getWallContents( 'videos' , $video->id , ( COwnerHelper::isCommunityAdmin() || COwnerHelper::isMine($my->id, $video->creator) ) , $limit , $limitstart );
            $output         .= '</div>';

            if( !$config->get('lockvideoswalls') || ($config->get('lockvideoswalls') && CFriendsHelper::isConnected($my->id, $video->creator) ) || COwnerHelper::isCommunityAdmin() )
            {
                $viewAllLink = false;
                if($jinput->request->get('task', '') != 'app')
                {
                    $viewAllLink    = CRoute::_('index.php?option=com_community&view=videos&task=app&videoid=' . $video->id . '&app=walls');
                }

                $output .= CWallLibrary::getWallInputForm( $video->id , 'videos,ajaxSaveWall', 'videos,ajaxRemoveWall' , $viewAllLink );
            }

            jimport('joomla.html.pagination');
            $wallModel      = CFactory::getModel('wall');
            $pagination     = new JPagination( $wallModel->getCount( $video->id , 'videos' ) , $limitstart , $limit );

            $output     .= '<div class="cPagination">' . $pagination->getPagesLinks() . '</div>';
        }
        else
        {
            $model              = CFactory::getModel('apps');
            $applications       = CAppPlugins::getInstance();
            $applicationId      = $model->getUserApplicationId( $applicationName );

            $application        = $applications->get( $applicationName , $applicationId );

            // Get the parameters
            $manifest           = CPluginHelper::getPluginPath('community',$applicationName) .'/'. $applicationName .'/'. $applicationName . '.xml';

            $params         = new CParameter( $model->getUserAppParams( $applicationId ) , $manifest );

            $application->params    = $params;
            $application->id        = $applicationId;

            $output = $application->onAppDisplay( $params );
        }

        echo $output;
    }

    /**
     * View to display the search form
     **/
    public function search()
    {
        $document   = JFactory::getDocument();
        $mainframe  = JFactory::getApplication();
        $jinput     = $mainframe->input;

        $this->addPathway( JText::_( 'COM_COMMUNITY_VIDEOS' ) , CRoute::_('index.php?option=com_community&view=videos' ) );
        $this->addPathway( JText::_('COM_COMMUNITY_SEARCH') , '' );
        $document->setTitle(JText::_( 'COM_COMMUNITY_SEARCH' ));

        $this->_addSubmenu();
        $this->showSubmenu();

        $search     = $jinput->request->get('search-text' , '', 'STRING');
        $result     = array();
        $pagination = null;
        $total      = 0;

        if( !empty( $search ) )
        {
            $searchModel    = CFactory::getModel( 'Search' );
            $result         = $searchModel->searchVideo( $search );
            $pagination     = $searchModel->getPagination();
            $total          = $searchModel->getTotal();
        }

        $pagination = is_null($pagination) ? '' : $pagination->getPagesLinks();

        $videosHTML = $this->_getVideosHTML($result);

        $tmpl       = new CTemplate();
        $tmpl->set( 'videosHTML'    , $videosHTML );
        $tmpl->set( 'pagination'    , $pagination );
        $tmpl->set( 'videosCount'   , $total );
        $tmpl->set( 'search'        , $search);

        echo $tmpl->fetch( 'videos.search' );
    }

    public function myvideos($id = null)
    {
        $document   = JFactory::getDocument();
        $my         = CFactory::getUser();
        $mainframe  = JFactory::getApplication();
        $jinput     = $mainframe->input;

        $userid     = $jinput->get( 'userid' , 0 , 'Int' );
        $user       = CFactory::getUser($userid);

        $blocked    = $user->isBlocked();

        if( $blocked && !COwnerHelper::isCommunityAdmin() )
        {
            $tmpl   = new CTemplate();
            echo $tmpl->fetch('profile.blocked');
            return;
        }

        if($my->id == $user->id)
            $title  = JText::_('COM_COMMUNITY_VIDEOS_MY');
        else
            $title  = JText::sprintf('COM_COMMUNITY_VIDEOS_USERS_VIDEO_TITLE', $user->getDisplayName());

        $document->setTitle($title);

        // Set pathway
        $mainframe  = JFactory::getApplication();
        $this->addPathway( JText::_( 'COM_COMMUNITY_VIDEOS' ) , CRoute::_('index.php?option=com_community&view=videos' ) );
        $this->addPathway( $title );


        // Show the mini header when viewing other's photos
        if($my->id != $user->id)
            $this->attachMiniHeaderUser($user->id);

        // Display submenu
        $this->_addSubmenu();
        $this->showSubmenu();

        // Get data from DB
        $model          = CFactory::getModel('videos');

        $filters        = array
        (
            'creator'   => $user->id,
            'status'    => 'ready',
            'groupid'   => 0,
            'sorting'   => $jinput->get('sort', 'latest', 'STRING')
        );
        $videos         = $model->getVideos($filters);

        $sortItems  = array
        (
            'latest'    => JText::_('COM_COMMUNITY_VIDEOS_SORT_LATEST'),
            'mostwalls' => JText::_('COM_COMMUNITY_VIDEOS_SORT_MOST_WALL_POST'),
            'mostviews' => JText::_('COM_COMMUNITY_VIDEOS_SORT_POPULAR'),
            'title'     => JText::_('COM_COMMUNITY_VIDEOS_SORT_TITLE')
        );

        //pagination
        $pagination     = $model->getPagination();

        $videosHTML     = $this->_getVideosHTML( $videos );

        $tmpl       = new CTemplate();
        $tmpl->set( 'user'          , $user );
        $tmpl->set( 'sort'          , $jinput->get('sort', 'latest','STRING') );
        $tmpl->set( 'currentTask'   , JInput::get( 'task' , '') );
        $tmpl->set( 'videosHTML'    , $videosHTML );
        $tmpl->set( 'sortings'      , CFilterBar::getHTML( CRoute::getURI(), $sortItems, 'latest') );
        $tmpl->set( 'pagination'    , $pagination );

        echo $tmpl->fetch( 'videos.myvideos' );
    }

    public function mypendingvideos($id= null)
    {
        $document   = JFactory::getDocument();
        $my         = CFactory::getUser();
        $mainframe  = JFactory::getApplication();
        $jinput     = $mainframe->input;

        $userid     = $jinput->get( 'userid' , 0 , 'Int' );
        $user       = CFactory::getUser($userid);

        $this->_addSubmenu();
        $this->showSubmenu();

        // Set pathway
        $pathway    = $mainframe->getPathway();
        $pathway->addItem( 'My Pending Videos', '' );

        // Get data from DB
        $model          = CFactory::getModel('videos');

        // Group video pending
        $groupid = $jinput->get('groupid', '0','INT');
        if(!empty($groupid))
        {
            $filters        = array
            (
                'groupid'   => $groupid,
                'status'    => 'pending'
            );
        }
        else
        {
            $filters        = array
            (
                'creator'   => $user->id,
                'groupid'   => 0,
                'status'    => 'pending'
            );
        }

        $pendingVideos  = $model->getVideos($filters);

        // Substitute permission in text form
        foreach ($pendingVideos as $video) {
            $video->isOwner = COwnerHelper::isMine($my->id, $video->creator);
        }

        $videosHTML     = $this->_getVideosHTML( $pendingVideos );

        $pagination     = $model->getPagination();


        $tmpl   = new CTemplate();

        $tmpl->set( 'videosHTML'    , $videosHTML );
        $tmpl->set( 'sort'          , $jinput->get('sort', 'latest','STRING') );
        $tmpl->set( 'currentTask'   , JInput::get( 'task' , '') );
        $tmpl->set( 'pendingVideos' , $pendingVideos );
        $tmpl->set( 'pagination'    , $pagination );

        $tmpl->set( 'params'        , $this->videoLib );

        echo $tmpl->fetch( 'videos.pending' );
    }

    /**
     * Method to display video
     *
     **/
    public function video()
    {
        $mainframe  = JFactory::getApplication();
        $jinput     = $mainframe->input;
        $document   = JFactory::getDocument();
        $my         = CFactory::getUser();
        $videoId    = $jinput->get->get('videoid' , '', 'INT');

        // Load necessary window css / javascript headers.
        CWindow::load();

        // Load the video table
        $video      = JTable::getInstance( 'Video' , 'CTable' );

        if (!$video->load($videoId)) {
            $url    = CRoute::_('index.php?option=com_community&view=videos', false);
            $mainframe->redirect($url, JText::_('COM_COMMUNITY_VIDEOS_NOT_AVAILABLE'), 'warning');
        }


        // Set video thumbnail and description for social bookmarking sites linking
        $document->addHeadLink($video->getThumbnail(), 'image_src', 'rel');
        $document->setDescription( CStringHelper::escape($video->getDescription()) );

        //CFactory::load( 'helpers' , 'owner' );


        // Only add these links when there are photos in the album
        if( COwnerHelper::isCommunityAdmin() || ($my->id == $video->creator && ($my->id != 0)) )
        {
            $this->addSubmenuItem('' , JText::_('COM_COMMUNITY_VIDEOS_FETCH_THUMBNAIL'), 'joms.videos.fetchThumbnail(\'' . $video->id . '\')', true);

            // Only add the set as profile video for video owner
            if( $my->id == $video->creator )
            {
                $this->addSubmenuItem('' , JText::_('COM_COMMUNITY_VIDEOS_SET_AS_PROFILE'), 'joms.videos.linkConfirmProfileVideo(\'' . $video->id . '\')', true);
            }
            $this->addSubmenuItem('' , JText::_('COM_COMMUNITY_DELETE'), 'joms.videos.deleteVideo(\'' . $video->id . '\')', true);
        }

        $this->_addSubmenu();
        $this->showSubmenu();

        if($video->creator_type == VIDEO_GROUP_TYPE)
        {
            $this->_groupVideo();
        }
        else
        {
            $this->_userVideo();
        }
    }

    /**
     *  Check if permitted to play the video
     *
     *  @param  int     $myid       The current user's id
     *  @param  int     $userid     The active profile user's id
     *  @param  int     $permission The video's permission
     *  @return bool    True if it's permitted
     *  @since  1.2
     */
    public function isPermitted($myid=0, $userid=0, $permissions=0)
    {
        if ( $permissions == 0) return true; // public

        // Load Libraries



        if( COwnerHelper::isCommunityAdmin() ) {
            return true;
        }

        $relation   = 0;

        if( $myid != 0 )
            $relation = 20; // site members

        if( CFriendsHelper::isConnected($myid, $userid) )
            $relation   = 30; // friends

        if( COwnerHelper::isMine($myid, $userid) ){
            $relation   = 40; // mine
        }

        if( $relation >= $permissions ) {
            return true;
        }

        return false;
    }

    public function _addSubmenu()
    {
        $mainframe  = JFactory::getApplication();
        $jinput     = $mainframe->input;
        $my     = CFactory::getUser();

        $task   = $jinput->request->get('task' , '');
        $groupId =  $jinput->get->get('groupid' , '', 'INT');

        if( !empty($groupId) )
        {
            $this->addSubmenuItem( 'index.php?option=com_community&view=groups&task=viewgroup&groupid=' . $groupId, JText::_('COM_COMMUNITY_GROUPS_BACK_TO_GROUP'));
            $videos         = $this->model->hasPendingVideos( $groupId , VIDEO_GROUP_TYPE );

            if ($videos)
            {
                $this->addSubmenuItem('index.php?option=com_community&view=videos&task=mypendingvideos&groupid=' . $groupId, JText::_('COM_COMMUNITY_VIDEOS_GROUP_PENDING') , '' , SUBMENU_LEFT );
            }

            $allowManageVideos = CGroupHelper::allowManageVideo($groupId);

            if($allowManageVideos)
            {
                $this->addSubmenuItem( '', JText::_('COM_COMMUNITY_ADD'), 'joms.videos.addVideo(\''.VIDEO_GROUP_TYPE.'\', \''.$groupId.'\')', SUBMENU_RIGHT);
            }
        }
        else
        {
            $this->addSubmenuItem('index.php?option=com_community&view=videos&task=display', JText::_('COM_COMMUNITY_VIDEOS_ALL_DESC'), '', SUBMENU_LEFT);

            if(!empty($my->id))
            {
                $this->addSubmenuItem('index.php?option=com_community&view=videos&task=myvideos&userid=' . $my->id, JText::_('COM_COMMUNITY_VIDEOS_MY'), '', SUBMENU_LEFT);
                $this->addSubmenuItem( '' , JText::_('COM_COMMUNITY_ADD'), 'joms.videos.addVideo()', SUBMENU_RIGHT);
            }

            $this->addSubmenuItem('index.php?option=com_community&view=videos&task=search', JText::_('COM_COMMUNITY_SEARCH'), '', SUBMENU_LEFT);

            $videos         = $this->model->hasPendingVideos( $my->id , VIDEO_USER_TYPE );

            if (!empty($my->id) && $videos )
            {
                $this->addSubmenuItem('index.php?option=com_community&view=videos&task=mypendingvideos&userid=' . $my->id, JText::_('COM_COMMUNITY_VIDEOS_PENDING') , '' , SUBMENU_LEFT );
            }
        }
    }

    public function _userVideo()
    {
        $mainframe  = JFactory::getApplication();
        $jinput     = $mainframe->input;
        $document   = JFactory::getDocument();

        // Get necessary properties and load the libraries
        $my         = CFactory::getUser();
        $model      = CFactory::getModel('videos');
        $videoId    = $jinput->get('videoid' , '', 'INT');

        $video      = JTable::getInstance( 'Video' , 'CTable' );
        $video->load($videoId);
        $video->loadExtra();


        $user       = CFactory::getUser( $video->creator );
        $blocked    = $user->isBlocked();

        // Show the mini header when viewing other's photos
        if($my->id != $video->creator)
            $this->attachMiniHeaderUser($video->creator);

        if( $blocked && !COwnerHelper::isCommunityAdmin() )
        {
            $tmpl   = new CTemplate();
            echo $tmpl->fetch('profile.blocked');
            return;
        }

        if( empty( $videoId ) )
        {
            $url    = CRoute::_('index.php?option=com_community&view=videos', false);
            $mainframe->redirect($url, JText::_('COM_COMMUNITY_VIDEOS_ID_ERROR'), 'warning');
        }

        // Check permission
        if (!$this->isPermitted($my->id, $video->creator, $video->permissions))
        {
            // Set document title
            $document->setTitle( JText::_('COM_COMMUNITY_RESTRICTED_ACCESS') );
            $mainframe->enqueueMessage(JText::_('COM_COMMUNITY_RESTRICTED_ACCESS', 'notice'));

            switch ($video->permissions)
            {
                case '40':
                    echo JText::_('COM_COMMUNITY_VIDEOS_OWNER_ONLY', 'notice');
                    break;
                case '30':
                    $owner  = CFactory::getUser($video->creator);
                    echo JText::sprintf('COM_COMMUNITY_VIDEOS_FRIEND_PERMISSION_MESSAGE', $owner->getDisplayName());
                    break;
                default:
                    echo '<p>' . JText::_('COM_COMMUNITY_VIDEOS_LOGIN_MESSAGE', 'notice') . '</p>';
                    break;
            }
        }
        else
        {
            // Get extra properties
            $video->player = $video->getViewHTML($video->getWidth(), $video->getHeight());
            $video->hit();

            // Get reporting html
            $reportHTML     = '';

            $report         = new CReportingLibrary();

            $reportHTML     = $report->getReportingHTML( JText::_('COM_COMMUNITY_VIDEOS_REPORT_VIDEOS') , 'videos,reportVideo' , array( $video->id ) );

            // Set pathway
            $pathway        = $mainframe->getPathway();
            $pathway->addItem( 'Video', CRoute::_('index.php?option=com_community&view=videos') );
            $pathway->addItem( $video->title, '' );

            // Set the current user's active profile
            CFactory::setActiveProfile( $video->creator );

            // Set document title
            $document->setTitle( $video->title );

            //CFactory::load( 'libraries' , 'bookmarks' );
            $bookmarks      =new CBookmarks($video->permalink);
            $bookmarksHTML  = $bookmarks->getHTML();

            $tmpl   = new CTemplate();

            // Get the walls
            //CFactory::load( 'libraries' , 'wall' );

            $wallContent    = CWallLibrary::getWallContents( 'videos' , $video->id , ( COwnerHelper::isCommunityAdmin() || ($my->id == $video->creator && ($my->id != 0)) ) , 10 ,0);
            $wallForm       = '';

            $viewAllLink = false;
            if($jinput->request->get('task', '') != 'app')
            {
                $viewAllLink    = CRoute::_('index.php?option=com_community&view=videos&task=app&videoid=' . $video->id . '&app=walls');
            }

            $wallForm       = CWallLibrary::getWallInputForm( $video->id , 'videos,ajaxSaveWall', 'videos,ajaxRemoveWall' , $viewAllLink );
            $redirectUrl    = CRoute::getURI( false );


            $config = CFactory::getConfig();
            $canSearch = 1;
            if($my->id == 0 && !$config->get('enableguestsearchvideos')) $canSearch = 0;

            $tmpl->set('redirectUrl'    , $redirectUrl );
            $tmpl->set('wallForm'       , $wallForm);
            $tmpl->set('wallContent'    , $wallContent);
            $tmpl->set('bookmarksHTML'  , $bookmarksHTML );
            $tmpl->set('reportHTML'     , $reportHTML);
            $tmpl->set('video'          , $video);
            $tmpl->set('canSearch'      , $canSearch);

            echo $tmpl->fetch('videos/single');
        }
    }

    public function _groupVideo()
    {
        $mainframe  = JFactory::getApplication();
        $jinput     = $mainframe->input;
        $document   = JFactory::getDocument();

        // Get necessary properties and load the libraries
        $my         = CFactory::getUser();
        $model      = CFactory::getModel('videos');
        $videoId    = $jinput->get->get('videoid' , '', 'INT');
        $groupId    = $jinput->get->get('groupid' , '', 'INT');

        $video      = JTable::getInstance( 'Video' , 'CTable' );
        $video->load($videoId);
        $video->loadExtra();


        $user       = CFactory::getUser( $video->creator );
        $blocked    = $user->isBlocked();

        if( $blocked && !COwnerHelper::isCommunityAdmin() )
        {
            $tmpl   = new CTemplate();
            echo $tmpl->fetch('profile.blocked');
            return;
        }

        if( empty( $videoId ) )
        {
            $url    = CRoute::_('index.php?option=com_community&view=videos', false);
            $mainframe->redirect($url, JText::_('COM_COMMUNITY_VIDEOS_ID_ERROR'), 'warning');
        }

        // Check permission
        if(!CGroupHelper::allowViewMedia($groupId))
        {
            // Set document title
            $document->setTitle( JText::_('COM_COMMUNITY_RESTRICTED_ACCESS') );
            $mainframe->enqueueMessage(JText::_('COM_COMMUNITY_RESTRICTED_ACCESS', 'notice'));
            echo JText::_('COM_COMMUNITY_GROUPS_VIDEO_MEMBER_PERMISSION');
        }
        else
        {
            // Get extra properties
            $video->player = $video->getViewHTML($video->getWidth(), $video->getHeight());

            $video->hit();

            // Get reporting html
            $reportHTML     = '';

            $report         = new CReportingLibrary();

            $reportHTML     = $report->getReportingHTML( JText::_('COM_COMMUNITY_VIDEOS_REPORT_VIDEOS') , 'videos,reportVideo' , array( $video->id ) );


            // Set pathway
            $pathway        = $mainframe->getPathway();
            $pathway->addItem( 'Video', CRoute::_('index.php?option=com_community&view=videos') );
            $pathway->addItem( $video->title, '' );

            // Set the current user's active profile
            CFactory::setActiveProfile( $video->creator );

            // Set document title
            $document->setTitle( $video->title );

            $bookmarks      = new CBookmarks($video->permalink);
            $bookmarksHTML  = $bookmarks->getHTML();

            $tmpl   = new CTemplate();

            // Get the walls

            $wallContent    = CWallLibrary::getWallContents( 'videos' , $video->id , ( COwnerHelper::isCommunityAdmin() || ($my->id == $video->creator && ($my->id != 0)) ) , 10 ,0);
            $wallForm       = '';

            $viewAllLink = false;
            if($jinput->request->get('task', '') != 'app')
            {
                $viewAllLink    = CRoute::_('index.php?option=com_community&view=videos&task=app&videoid=' . $video->id . '&app=walls');
            }

            $wallForm       = CWallLibrary::getWallInputForm( $video->id , 'videos,ajaxSaveWall', 'videos,ajaxRemoveWall' , $viewAllLink );
            $redirectUrl    = CRoute::getURI( false );

            $config = CFactory::getConfig();
            $canSearch = 1;
            if($my->id == 0 && !$config->get('enableguestsearchvideos')) $canSearch = 0;

            $tmpl->set('redirectUrl'    , $redirectUrl );
            $tmpl->set('wallForm'       , $wallForm);
            $tmpl->set('wallContent'    , $wallContent);
            $tmpl->set('bookmarksHTML'  , $bookmarksHTML);
            $tmpl->set('reportHTML'     , $reportHTML);
            $tmpl->set('video'          , $video);
            $tmpl->set('canSearch'      , $canSearch);

            echo $tmpl->fetch('videos/single');
        }
    }
}
