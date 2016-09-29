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

// Include interface definition
//CFactory::load( 'models' , 'tags' );

class CTableVideo extends JTable  implements CTaggable_Item
{
    //Table's field
    var $id             = null;
    var $title          = null;
    var $type           = null;
    var $video_id       = null;
    var $description    = null;
    var $creator        = null;
    var $creator_type   = null;
    var $created        = null;
    var $permissions    = null;
    var $category_id    = null;
    var $hits           = null;
    var $published      = null;
    var $featured       = null;
    var $duration       = null;
    var $status         = null;
    var $thumb          = null;
    var $path           = null;
    var $groupid        = null;
    var $storage        = null;
    var $location       = null;
    var $latitude       = null;
    var $longitude      = null;

    //non-table fields
    var $_wallcount     = 0;
    var $_size          = 0;
    var $_width         = 0;
    var $_height        = 0;
    var $_lastupdated   = null;

    var $_videoUrl      = null;
    var $_videoId       = null;
    var $_thumbnail     = null;
    var $_provider      = null;

    /**
     * Constructor
     */
    public function __construct(&$db)
    {
        parent::__construct( '#__community_videos', 'id', $db );

        require_once(JPATH_ROOT .'/components/com_community/libraries/core.php');


        $config         = CFactory::getConfig();
        $this->_size    = $config->get('videosSize');
        $this->_width   = CVideosHelper::getVideoSize('width');
        $this->_height  = CVideosHelper::getVideoSize('height');
        $this->storage  = 'file';

        $this->hits     = 0;

        // load helpers

    }


    /**
     * Load the object and the video provider as well
     */
    public function load( $oid = null, $reset = true)
    {
        if( parent::load( $oid ) )
        {
            // @todo: make sure loading is done ok
            $providerName   = JString::strtolower($this->type);
            if (empty($providerName)) {
                return false;
            }
            $libraryPath    = COMMUNITY_COM_PATH .'/libraries/videos' .'/'. $providerName . '.php';

            require_once($libraryPath);
            $className       = 'CTableVideo' . JString::ucfirst($providerName);
            $this->_provider = new $className( $this->_db );

            return true;
        }
        return false;

    }

    /**
     * Initialize the video with a new url
     */
    public function init($url)
    {
        // create the provider
        // $this->_provider should be null here

        $videoLib   = new CVideoLibrary();

        $this->_provider = $videoLib->getProvider($url);

        try {
            $isValid = $this->_provider->isValid();
        } catch (Exception $e) {
            $isValid = false;
        }

        if($isValid)
        {
            $this->title    = $this->_provider->getTitle();
            $this->type     = $this->_provider->getType();
            $this->video_id = $this->_provider->getId();
            $this->duration = $this->_provider->getDuration();
            $this->status   = 'ready';
            $this->thumb    = $this->_provider->getThumbnail();
            $this->path     = $url;
            $this->description= str_replace(array("\r", "\n"), " ", $this->_provider->getDescription()); // remove line break from description
            $this->status   = 'ready';
        }

        return $isValid;
    }

    /**
     * Make sure hits are user and session sensitive
     */
    public function hit($pk = null)
    {
        $session = JFactory::getSession();
        if( $session->get('view-video-'. $this->id, false) == false ) {
            parent::hit();

            //@since 4.1 we dump the info into video stats
            $statsModel = CFactory::getModel('stats');
            $statsModel->addVideoStats($this->id,'view');
        }
        $session->set('view-video-'. $this->id, true);
    }

    /**
     * Verify whether weblinks is accessible
     *
     * @param $url
     * @return boolean
     */
    public function isValid() {
    }

    public function getId() {
        return $this->id;
    }

    public function getType() {
        return $this->type;
    }

    /**
     * Get video's title from videoid
     *
     * @access  public
     * @param   videoid
     * @return video title
     */
    public function getTitle($escape = true)
    {
        //CError::assert($this->title, '', '!empty');
        $this->title    = $this->title ? $this->title : JText::_('COM_COMMUNITY_VIDEOS_TITLE_EMPTY');

        return $escape ? CStringHelper::escape($this->title) : $this->title;
    }

    /**
     * Get video's description from videoid
     *
     * @access  public
     * @return desctiption
     */
    public function getDescription($escape = true)
    {
        if(empty($this->description))
        {
            $this->description = JText::_('COM_COMMUNITY_VIDEOS_NO_DESCRIPTION');
        }

        return $escape ? CStringHelper::escape($this->description) : $this->description;
    }

    /**
     * Get video duration
     *
     * @return $duration seconds
     */
    public function getDuration()
    {
        //CError::assert($this->duration, '', '!empty');
        if (empty($this->duration))
        {
            $this->duration = 0;
        }
        return $this->duration;
    }

    public function getDurationInHMS()
    {
        if($this->duration != 0)
        {
            $duration = CVideosHelper::formatDuration( (int)($this->duration), 'HH:MM:SS' );
            $duration = CVideosHelper::toNiceHMS( $duration );
        }
        else
        {
            $duration = JText::_('COM_COMMUNITY_VIDEOS_DURATION_NOT_AVAILABLE');
        }

        return $duration;
    }

    /**
     * Get video's thumbnail URL from videoid
     *
     * @access  public
     * @param   videoid
     * @return url
     */
    public function getThumbnail()
    {
        $config = CFactory::getConfig();
        $file   = $this->thumb;

        // Site origin
        if (JString::substr($file, 0, 4)=='http')
        {
            $uri = $file;
            return $uri;
        }

        // Remote storage
        if($this->storage != 'file')
        {

            $storage = CStorage::getStorage($this->storage);
            $uri = $storage->getURI($file);
            return $uri;
        }

        // Default thumbnail
        if (empty($file) || !JFile::exists(JPATH_ROOT.'/'.$file))
        {

            $template = new CTemplateHelper();
            $asset = $template->getTemplateAsset('video_thumb.png', 'images');
            $uri = $asset->url;
            return $uri;
        }

        // Strip cdn path if exists.
        // Note: At one point, cdn path was stored along with the thumbnail path
        //       in the db which is the mistake we are trying to rectify here.
        $file   = str_ireplace($config->get('videocdnpath'), '', $file);

        // CDN or local
        $baseUrl = $config->get('videobaseurl') or
        $baseUrl = JURI::root();
        $uri = str_replace('\\', '/', rtrim($baseUrl, '/') . '/' . ltrim($file, '/'));
        return $uri;
    }

    public function getSize() {
        return $this->_size;
    }

    public function getWidth() {
        return $this->_width;
    }

    public function getHeight() {
        return $this->_height;
    }

    public function getWallCount()
    {
        $query  = ' SELECT COUNT(*)'
                . ' FROM ' . $this->_db->quoteName('#__community_wall')
                . ' WHERE ' . $this->_db->quoteName('type') . ' = ' . $this->_db->quote('videos')
                . ' AND ' . $this->_db->quoteName('published') . ' = ' . $this->_db->quote(1)
                . ' AND ' . $this->_db->quoteName('contentid') . ' = ' . $this->_db->quote($this->id)
                ;
        $this->_db->setQuery($query);
        $this->_wallcount   = $this->_db->loadResult();

        return $this->_wallcount;
    }

    public function getLastUpdated($raw = false)
    {
        $this->_lastupdated = $this->created;

        if($raw)
        {
            return $this->_lastupdated;
        }



        if($this->_lastupdated == '0000-00-00 00:00:00' || $this->_lastupdated == '')
        {
            $this->_lastupdated = $this->created;

            if($this->_lastupdated == '' || $this->_lastupdated == '0000-00-00 00:00:00')
            {
                $this->_lastupdated = JText::_( 'COM_COMMUNITY_NO_LAST_ACTIVITY' );
            }
            else
            {
                $lastUpdated    = new JDate( $this->_lastupdated );
                //$this->_lastupdated = CActivityStream::_createdLapse( $lastUpdated, false );
                $this->_lastupdated = CTimeHelper::timeLapse($lastUpdated);
            }
        }
        else
        {
            $lastUpdated    = new JDate( $this->_lastupdated );
            //$this->_lastupdated = CActivityStream::_createdLapse( $lastUpdated, false );
            $this->_lastupdated = CTimeHelper::timeLapse($lastUpdated);
        }



        return $this->_lastupdated;
    }

    public function isPending()
    {
        return ($this->status == 'pending');
    }

        public function isExist () {
            $query =    ' SELECT ' . $this->_db->quoteName('id') .
                        ' FROM ' . $this->_db->quoteName('#__community_videos') .
                        ' WHERE ' . $this->_db->quoteName('creator') . ' = ' . (int)$this->creator .
                        ' AND ' . $this->_db->quoteName('creator_type') . ' = ' . $this->_db->quote($this->creator_type) .
                        ' AND ' . $this->_db->quoteName('path') . ' = ' . $this->_db->quote($this->path);
            $this->_db->setQuery($query);
            return $this->_db->loadResult();
        }
    public function check()
    {
        // Santinise data
        $safeHtmlFilter     = CFactory::getInputFilter();

        $this->title        = $safeHtmlFilter->clean($this->title);
        $this->description  = $safeHtmlFilter->clean($this->description);
        $this->category_id  = JString::trim((int)$this->category_id);
        $this->permissions  = JString::trim((int)$this->permissions);

        // Validate user information
        if ($this->title == '')
            $this->title = JText::_('COM_COMMUNITY_VIDEOS_TITLE_EMPTY');

        // if ($this->description == '')
        //  $this->description = JText::_('COM_COMMUNITY_VIDEOS_NO_DESCRIPTION');

        if ($this->created == null) {
            $now = JDate::getInstance();
            $this->created = $now->toSql();
        }

        if ($this->published == null)
            $this->published = 1;

        return true;
    }

    /**
     * @return $embedvideo specific embeded code to play the video
     */
    public function getViewHTML($videoWidth='' , $videoHeight='', $defaultView=true)
    {
        $id             = ($this->type=='file') ? $this->id : $this->video_id;
        $videoWidth     = $videoWidth ? $videoWidth : $this->getWidth();
        $videoHeight    = $videoHeight ? $videoHeight : $this->getHeight();

        if ($defaultView)
        {
            $html       = $this->_provider->getViewHTML($id, $videoWidth , $videoHeight );
        } else {
            $html       = $this->_provider->getEmbedCode($id, $videoWidth , $videoHeight );
        }

        return $html;
    }

    /**
     * Return the video provider object
     */
    public function getProvider()
    {
        return $this->_provider;
    }

    public function store( $updateNulls = false )
    {
        if (empty($source)) {
            $source = $this;
        }


        if (!$this->check()) {
            return false;
        }
        if (!parent::store()) {
            return false;
        }
        return true;
    }

    /**
     * Return true if it's not private video
     */
    public function isPublic()
    {
        if ($this->creator_type == VIDEO_USER_TYPE)
        {
            return ($this->permissions <= 20);
        }
        if ($this->creator_type == VIDEO_GROUP_TYPE)
        {
            $group  = JTable::getInstance( 'Group' , 'CTable' );
            $group->load($this->groupid);
            return ($group->approvals == COMMUNITY_PUBLIC_GROUP);
        }
        return false;
    }

    public function getViewURI($route = true)
    {
        $uri = '';
        switch($this->creator_type)
        {
            case VIDEO_GROUP_TYPE :
                $uri    = 'index.php?option=com_community&view=videos&task=video&groupid='.$this->groupid.'&videoid='.$this->id;
                break;
            case VIDEO_USER_TYPE :
            default :
                $uri    = 'index.php?option=com_community&view=videos&task=video&userid='.$this->creator.'&videoid='.$this->id;
                break;
        }

        return $route ? CRoute::_($uri) : $uri;
    }

    public function getFlv()
    {
        $flv = '';

        if ($this->type != 'file') return $flv;

        $config     = CFactory::getConfig();
        $baseUrl    = $config->get( 'videobaseurl' );
        if ($config->get('enablevideopseudostream') && ($this->storage == 'file') && empty($baseUrl) )
        {
            $flv        = JURI::root() . 'components/com_community/libraries/streamer.php/'.urlencode($this->path);
        }
        else
        {
            if( !empty($baseUrl) )
            {
                $flv    = rtrim( $baseUrl , '/' ) . '/' . $this->path;
            }
            else
            {

                $storage    = CStorage::getStorage($this->storage);
                $flv        = $storage->getURI($this->path);
            }
        }
        return $flv;
    }

    public function getURL($raw=false)
    {
        $url    = 'index.php?option=com_community&view=videos&task=video';
        if ($this->creator_type == VIDEO_GROUP_TYPE || !empty($this->groupid)){
            $url .= '&groupid='.$this->groupid;
        }elseif($this->creator_type == VIDEO_EVENT_TYPE || $this->eventid){
            $url .= '&eventid='.$this->eventid;
        } else {
            // defaul as user type, VIDEO_USER_TYPE
            $url .= '&userid='.$this->creator;
        }
        $url    .= '&videoid='.$this->id;

        return CRoute::_( $url );
    }

    public function getPermalink()
    {
        $url    = 'index.php?option=com_community&view=videos&task=video';
        if ($this->creator_type == VIDEO_GROUP_TYPE)
        {
            $url .= '&groupid='.$this->groupid;
        }
        else
        {
            // defaul as user type, VIDEO_USER_TYPE
            $url .= '&userid='.$this->creator;
        }
        $url    .= '&videoid='.$this->id;

        return CRoute::getExternalURL( $url , false );
    }

    public function isOwner()
    {
        $my = CFactory::getUser();
        return COwnerHelper::isMine($my->id, $this->creator);
    }

    public function isAdmin()
    {
        return COwnerHelper::isCommunityAdmin();
    }

    public function canEdit()
    {
        return ($this->isOwner() || $this->isAdmin());
    }

    public function getCreatorName()
    {
        $user = CFactory::getUser($this->creator);
        return $user->getDisplayName();
    }

    public function getHits()
    {
        return $this->hits;
    }

    public function getPlayerHTML($width=null, $height=null, $defaultView=true)
    {
        $id     = ($this->type=='file') ? $this->id : $this->video_id;
        $width  = ($width) ? $width : $this->_width;
        $height = ($height) ? $height : $this->_height;

        $html = '';

        if ($defaultView) {
            if ( method_exists($this->_provider, 'getViewHTML') ) {
                $html = $this->_provider->getViewHTML($id, $width, $height, array(
                    'path' => ( $this->type === 'file' ? JURI::root(true) . '/' : '' ) . $this->path,
                    'thumbnail' => $this->getThumbnail()
                ));
            }
        } else {
            if ( method_exists($this->_provider, 'getEmbedCode') ) {
                $html = $this->_provider->getEmbedCode($id, $width, $height, array(
                    'path' => ( $this->type === 'file' ? JURI::root(true) . '/' : '' ) . $this->path,
                    'thumbnail' => $this->getThumbnail()
                ));
            }
        }

        return $html;
    }

    public function getVideoId(){
        return $this->video_id;
    }

    /**
     * Return the title of the object
     */
    public function tagGetTitle()
    {
        return $this->getTitle();
    }

    /**
     * Return the HTML summary of the object
     */
    public function tagGetHtml()
    {
        return '';
    }

    /**
     * Return the internal link of the object
     *
     */
    public function tagGetLink()
    {
        return $this->getViewURI();
    }

    /**
     * Return true if the user is allow to modify the tag
     *
     */
    public function tagAllow($userid)
    {
        return $this->canEdit($userid);
    }

    /**
    * Return the category name
    *
    */
    public function getCategoryName(){
        $category   = JTable::getInstance( 'VideosCategory' , 'CTable' );
        $category->load( $this->category_id );

        return $category->name;
    }
}
