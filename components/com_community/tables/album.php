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

class CTableAlbum extends JTable implements CTaggable_Item {

    var $id = null;

    /** Album cover , FK to the photo id * */
    var $photoid = null;
    var $creator = null;
    var $name = null;
    var $description = null;
    var $permissions = null;
    var $created = null;
    var $path = null;
    var $type = null;
    var $groupid = null;
    var $location = null;
    var $latitude = null;
    var $longitude = null;
    var $hits = 0;
    var $params = null;
    private $_params = null;

    /**
     * Constructor
     */
    public function __construct(&$db) {
        parent::__construct('#__community_photos_albums', 'id', $db);

        // General Permission as initial permission
        $this->permissions = 0;
        $my = CFactory::getUser();
        $params = $my->getParams();
        if ($my->id > 0 && $params->get('privacyPhotoView')) {
            $this->permissions = $params->get('privacyPhotoView');
        }

        $this->_params = new JRegistry($this->params);
    }

    /**
     * If caller access non-member variable, look it up in params
     * @todo: update params if not available
     */
    public function __get($name) {
        switch ($name) {
            case 'lastupdated':
                $lastupdated = $this->_params->get('lastupdated');
                return $lastupdated;
                break;

            case 'count':
                $count = $this->_params->get('count');
                return $count;

            case 'link':
                // return link to this album
                return CRoute::_("index.php?option=com_community&view=photos&task=album&albumid={$this->id}&userid={$this->creator}");
                break;

            case 'thumbnail':
                $thumbnail = $this->_params->get('thumbnail');
                return $thumbnail;

            case 'thumbnail_id':
                $thumbnail_id = $this->_params->get('thumbnail_id');
                return $thumbnail_id;
        }
    }

    public function bind($src, $ignore = array()) {

        parent::bind($src);
        $this->_params = new JRegistry($this->params);

        // Setup missing params
        $storeNow = false;
        $db = JFactory::getDBO();

        // Grab thumbnail from cover photo
        $thumbnail = $this->_params->get('thumbnail');
        if (empty($thumbnail)) {
            $photo = JTable::getInstance('Photo', 'CTable');
            $photo->load($this->photoid);
            $thumbnail = str_replace(JURI::root(), '', $photo->getThumbURI());
            $this->_params->set('thumbnail', $thumbnail);
            $storeNow = true;
        }

        // Grab last update from last uploaded photo
        $lastupdated = $this->_params->get('lastupdated');
        if (empty($lastupdated)) {
            $this->_updatelastUpdate();

            $storeNow = true;
        }

        // How many photos in this album
        $count = $this->_params->get('count', -1);
        if ($count != $this->getPhotosCount()) {
            $this->_updateCount();

            $storeNow = true;
        }


        // Do we need to store this album again?
        if ($storeNow) {
            $this->store();
        }

        return true;
    }

    /**
     *    Allows us to test if the user has access to the album
     * */
    public function hasAccess($userId, $permissionType) {

        // @rule: For super admin, regardless of what permission, they should be able to access
        if (COwnerHelper::isCommunityAdmin())
            return true;

        switch ($this->type) {
            case PHOTOS_PROFILE_TYPE:
            case PHOTOS_USER_TYPE:
            case 'profile.gif':

                if ($permissionType == 'upload') {
                    return $this->creator == $userId;
                }

                if ($permissionType == 'deletephotos') {
                    return $this->creator == $userId;
                }

                break;

            case PHOTOS_GROUP_TYPE:
            case 'group.gif':

                $group = JTable::getInstance('Group', 'CTable');
                $group->load($this->groupid);

                if ($permissionType == 'upload') {
                    return CGroupHelper::allowManagePhoto($group->id);
                }
                if ($permissionType == 'deletephotos') {
                    return $this->creator == $userId || $group->isAdmin($userId);
                }

                return false;
                break;

            case PHOTOS_EVENT_TYPE:
            case 'event.gif':

                $event = JTable::getInstance('Event', 'CTable');
                $event->load($this->eventid);

                if ($permissionType == 'upload') {
                    return CEventHelper::allowManagePhoto($event->id);
                }

                if ($permissionType == 'deletephotos') {
                    return $this->creator == $userId || $event->isAdmin($userId);
                }

                return false;
                break;
        }
    }

    /**
     * Return the path to the cover photo
     * If no cover photo is specifies, we just load the first photo in the album
     */
    public function getCoverThumbPath() {

        //$this->params = new cParameter($this->params);
        // If there is a cached thumbnail path, it should match current photoid
        if ($this->_params->get('thumbnail') && ($this->photoid == $this->_params->get('thumbnail_id')) && file_exists(JPATH_ROOT.'/'.$this->_params->get('thumbnail'))) {
            if (strpos($this->_params->get('thumbnail'), 'assets') == false) {
                $thumbnail = '/'.$this->_params->get('thumbnail');
                $thumbnail =  str_replace("//", "/", $thumbnail);
                return JURI::root(true).$thumbnail;
            }
        }

        // If no photo id assigned yet, grab the first photo.
        // (assuming photo count is more than 0)
        if ( !file_exists(JPATH_ROOT.'/'.$this->_params->get('thumbnail')) ) {


            $db = JFactory::getDBO();
            $sql = "SELECT ".$db->quoteName('id')." FROM #__community_photos WHERE "
                . " ".$db->quoteName('albumid')." =" . $db->Quote($this->id)
                . " AND ".$db->quoteName('published')." =" . $db->Quote(1)
                . " AND ".$db->quoteName('status')." <> " . $db->Quote('temp')
                . " ORDER BY ".$db->quoteName('id')." ASC";

            $db->setQuery($sql);
            $result = $db->loadResult();
            if ($result) {
                $this->photoid = $result;
                /* Cannot be stored because this object has been modified in photo view and will results in "field not exists" */
                //$this->store();
            }
        }

        $photoModel = CFactory::getModel('photos');
        $photo = $photoModel->getPhoto($this->photoid);

        if (!empty($photo)) {
            $thumbUri = $photo->getThumbURI();
        } else {
            $thumbUri = JURI::root(true) . '/components/com_community/assets/photo_thumb.png';
        }

        // Now, cache this path and id in album params
        $this->_params->set('thumbnail', $thumbUri);
        $this->_params->set('thumbnail_id', $this->photoid);

        // If this photo doesn't exist, we need to select a new valid one
        // @todo: test and see if the photo actually exist

        return $thumbUri;
    }

    public function getCoverThumbURI() {
        return $this->getCoverThumbPath();
    }

    public function getURI() {
        $uri = 'index.php?option=com_community&view=photos&task=album&albumid=' . $this->id;

        switch ($this->type) {
            case PHOTOS_USER_TYPE:
                $uri .= '&userid=' . $this->creator;
                break;
            case PHOTOS_GROUP_TYPE:
                $uri .= '&groupid=' . $this->groupid;
                break;
            case PHOTOS_EVENT_TYPE:
                $uri .= '&eventid=' . $this->eventid;
                break;
        }

        return CRoute::_($uri);
    }

    public function getDescription() {
        return $this->description;
    }

    /**
     * Delete an album
     * Set all photo within the album to have albumid = 0
     * Do not yet delete the photos, this could be very slow on an album that
     * has huge amount of photos
     */
    public function delete($pk = null) {

        //lets get all the photo info under the album
        $photoModel = CFactory::getModel('photos');
        $photos = $photoModel->getAllPhotos($this->id, $this->type);

        $db = JFactory::getDBO();
        $strSQL = 'UPDATE ' . $db->quoteName('#__community_photos')
                . ' SET ' . $db->quoteName('albumid') . '=' . $db->Quote(0) .', '.$db->quoteName('status').' = '.$db->quote('temp')
                . ' WHERE ' . $db->quoteName('albumid') . '=' . $db->Quote($this->id);

        $db->setQuery($strSQL);
        $result = $db->execute();

        // The whole local folder should be deleted, regardless of the storage type
        // BUT some old version of JomSocial might store other photo in the same
        // folder, we check in db first
        $strSQL = 'SELECT count(*) FROM ' . $db->quoteName('#__community_photos')
                . ' WHERE ' . $db->quoteName('image') . ' LIKE ' . $db->Quote('%' . dirname($this->path) . '%');
        $db->setQuery($strSQL);
        $result = $db->loadResult();

        if ($result == 0) {
            if (JFolder::exists(JPATH_ROOT . '/' . rtrim($this->path, '/') . '/' . $this->id)) {
                JFolder::delete(JPATH_ROOT . '/' . rtrim($this->path, '/') . '/' . $this->id);
            }
        }

        //we need to delete all activity related to the photos inside the album as well
        if(isset($photos) && count($photos) > 0) {
            foreach ($photos as $photo) {
                CActivityStream::remove('photos', $photo->id);
            }
        }

        /* Delete album directory */
        $config = CFactory::getConfig();
        $dirPath = JPATH_ROOT . '/' . $config->getString('imagefolder') . '/photos/' . $this->creator . '/' . $this->id;
        if (JFolder::exists($dirPath)) {
            JFolder::delete($dirPath);
        }

        $appsLib = CAppPlugins::getInstance();
        $appsLib->loadApplications();
        $appsLib->triggerEvent('onAfterAlbumDelete', array($this));

        // @rule: remove from featured item if item is featured

        $featured = new CFeatured(FEATURED_ALBUMS);
        $featured->delete($this->id);

        // if this is an avatar, we have to remove the avatar from the respective user
        if($this->type == 'profile.avatar'){
            $user = CFactory::getUser($this->creator);
            $userModel = CFactory::getModel('User');
            $userModel->removeProfilePicture($user->id, 'avatar');
            $userModel->removeProfilePicture($user->id, 'thumb');
            $activityModel = CFactory::getModel('activities');
            $activityModel->removeAvatarActivity('profile.avatar.upload', $user->id);
        }elseif($this->type == 'event.avatar'){
            $eventTable = JTable::getInstance('Event', 'CTable');
            $eventTable->load($this->eventid);
            $eventTable->removeAvatar();
            $activityModel = CFactory::getModel('activities');
            $activityModel->removeAvatarActivity('events.avatar.upload', $this->eventid);
        }elseif($this->type == 'group.avatar'){
            $eventTable = JTable::getInstance('Group', 'CTable');
            $eventTable->load($this->groupid);
            $eventTable->removeAvatar();
            $activityModel = CFactory::getModel('activities');
            $activityModel->removeAvatarActivity('groups.avatar.upload', $this->groupid);
        }

        //add user points
        CUserPoints::assignPoint('album.remove');

        // Remove from activity stream
        //CActivityStream::remove('photos', $this->id);

        // Remove from activity stream
        CActivityStream::remove('albums', $this->id);

        // Remove likes activity
        $likeModel = CFactory::getModel('like');
        $likeModel->removeLikes('album', $this->id);

        // Remove comment
        $wallModel = CFactory::getModel('wall');
        $wallModel->deletePostByType('albums', $this->id);

        return parent::delete();
    }

    public function check() {
        // Santinise data
        $safeHtmlFilter = CFactory::getInputFilter();
        $this->name = $safeHtmlFilter->clean($this->name);
        $this->description = $safeHtmlFilter->clean($this->description);

        if (empty($this->creator)) {
            return false;
        }

        return true;
    }

    public function store($updateNulls = false) {
        if (!$this->check()) {
            return false;
        }

        //Update Count
        $this->_updateCount();

        //Update lastUpdate
        $this->_updatelastUpdate();

        // Fix error in cover photo. If it is still referring to photo_thumb.png , then update with photo id
        $coverPath = $this->_params->get('thumbnail');
        if (strrpos($coverPath, 'photo_thumb.png') && !empty($this->photoid)) {
            $this->_params->set('thumbnail', str_replace(JURI::root(), '/', $this->getCoverThumbPath()));
        }

        // Store params
        $this->params = $this->_params->toString();
        return parent::store();
    }

    /**
     * Override parent's hit method as we don't really want to
     * carefully increment it every time a photo is viewed.
     * */
    public function hit($pk = null) {
        $session = JFactory::getSession();

        if ($session->get('album-view-' . $this->id, false) == false) {
            parent::hit();
            $session->set('album-view-' . $this->id, true);
        }
    }

    /**
     * Update album statistics
     */
    public function updateStats() {

    }

    /**
     * Return the title of the object
     */
    public function tagGetTitle() {
        return $this->description;
    }

    /**
     * Return the HTML summary of the object
     */
    public function tagGetHtml() {
        return '';
    }

    /**
     * Return the internal link of the object
     *
     */
    public function tagGetLink() {
        return $this->getViewURI();
    }

    /**
     * Return true if the user is allow to modify the tag
     *
     */
    public function tagAllow($userid) {
        // @todo: only admin and album owner can edit this
        return true;
    }

    /**
     * Method to set a parameter
     *
     * @param   string  $key    Parameter key
     * @param   mixed   $value  Parameter value
     */
    public function setParam($key, $value) {
        return $this->_params->set($key, $value);
    }

    /**
     * Method to get/update count
     * @return true
     */
    private function _updateCount() {
        $count = $this->getPhotosCount();

        $this->setParam('count', $count);

        return true;
    }

    /**
     * Method to get Photo Count per Album
     * @return Photo count
     */
    private function getPhotosCount() {
        $photoModel = CFactory::getModel('photos');
        return $photoModel->getAlbumPhotosCount($this->id);
    }

    /**
     * Method to set last update
     * @return true
     */
    private function _updatelastUpdate() {
        $db = JFactory::getDBO();

        $sql = "SELECT ".$db->quoteName('created')." FROM ".$db->quoteName('#__community_photos')." WHERE "
                . " ".$db->quoteName('albumid')." =" . $db->Quote($this->id)
                . " AND ".$db->quoteName('published')." =" . $db->Quote(1)
                . " ORDER BY ".$db->quoteName('id')." DESC";

        $db->setQuery($sql);

        $result = $db->loadResult();

        $now = new JDate();
        $lastupdated = $now->toSql();

        // New album will have null last updated value. Revert to current time
        if (!is_null($result)) {
            $lastupdated = $result;
        }

        $this->setParam('lastupdated', $lastupdated);

        return true;
    }

    /**
     * Method to get the last update
     *
     * @return type
     */
    public function getLastUpdate() {
        // If new albums that has just been created and
        // does not contain any images, the lastupdated will always be 0000-00-00 00:00:00:00
        // Try to use the albums creation date instead.
        if ($this->lastupdated == '0000-00-00 00:00:00' || $this->lastupdated == '') {
            $lastupdated = $this->created;

            if ($this->lastupdated == '' || $this->lastupdated == '0000-00-00 00:00:00') {
                $lastupdated = JText::_('COM_COMMUNITY_PHOTOS_NO_ACTIVITY');
            } else {
                $lastUpdated = new JDate($this->lastupdated);
                $lastupdated = CTimeHelper::timeLapse($lastUpdated, false);
            }
        } else {
            $lastUpdated = new JDate($this->lastupdated);
            $lastupdated = CTimeHelper::timeLapse($lastUpdated, false);
        }

        return $lastupdated;
    }

    /**
     * Check if cover album is created
     * @param  ['String']  $type     [Profile/Group/Event]
     * @param  [Int]  $parentId [Profile/Group/Event id]
     * @return boolean           [description]
     */
    public function isCoverExist($type, $parentId) {
        $db = JFactory::getDBO();
        $field = array('profile' => 'creator', 'group' => 'groupid', 'event' => 'eventid');

        $query = 'SELECT ' . $db->quoteName('id') . ' FROM '
                . $db->quoteName('#__community_photos_albums')
                . ' WHERE ' . $db->quoteName($field[strtolower($type)]) . ' = ' . $db->Quote($parentId)
                . ' AND ' . $db->quoteName('type') . ' = ' . $db->Quote($type . '.cover');

        $db->setQuery($query);

        return $db->loadResult();
    }

    /**
     * Create album for cover
     * @param [string] $type     [description]
     * @param [int] $photoid  [description]
     * @param [int] $parentId [description]
     */
    public function addCoverAlbum($type, $parentId) {
        $my = CFactory::getUser();
        if($type == 'profile'){
            $my = CFactory::getUser($parentId);
        }
        $now = new JDate();
        $this->creator = $my->id;
        $this->type = $type . '.Cover';
        $this->path = 'images/cover/' . $type . '/' . $parentId . '/';
        $this->created = $now->toSql();
        $this->permissions = 0; //permission is always public

        if ($type == 'group' || $type == 'event') {
            $cTable = JTable::getInstance(ucfirst($type), 'CTable');
            $cTable->load($parentId);

            $name = property_exists($cTable, 'name') ? $cTable->name : $cTable->title;

            $this->name = ($type == 'group') ? JText::sprintf('COM_COMMUNITY_GROUP_COVER_NAME',ucfirst($name)) : JText::sprintf('COM_COMMUNITY_EVENT_COVER_NAME',ucfirst($name));
        }else{
            //profile
            $this->name = JText::sprintf('COM_COMMUNITY_PROFILE_COVER_NAME',ucfirst($my->getDisplayName()));
        }

        if ($type == 'group' || $type == 'event') {
            $fieldId = $type . 'id';
            $this->$fieldId = $parentId;
        }

        $this->store();

        return $this->id;
    }

    /**
     * Create a default album
     * @param $type
     * @param $parentId
     * @return null
     */
    public function addDefaultAlbum($parentId, $type) {
        $my = CFactory::getUser();
        if($type == 'profile'){
            $my = CFactory::getUser($parentId);
        }
        $now = new JDate();
        $this->creator = $my->id;
        $this->type = $type;
        $this->created = $now->toSql();
        $this->permissions = 0;
        $this->default = 1;

        switch($type){
            case 'profile':
                $this->path = 'images/photos/' . $parentId . '/';
                break;
            case 'event':
                $this->path = 'images/eventphotos/' . $parentId . '/';
                break;
            case 'group':
                $this->permissions = 0; //it should follow the permission from the group
                $this->path = 'images/groupphotos/' . $parentId . '/';
                break;
        }

        if ($type == 'group' || $type == 'event') {
            $cTable = JTable::getInstance(ucfirst($type), 'CTable');
            $cTable->load($parentId);

            $name = property_exists($cTable, 'name') ? $cTable->name : $cTable->title;

            $this->name = ($type == 'group') ? JText::_('COM_COMMUNITY_GROUP_DEFAULT_ALBUM_NAME') : JText::_('COM_COMMUNITY_EVENT_DEFAULT_ALBUM_NAME');
        }else{
            //profile
            $this->name = JText::_('COM_COMMUNITY_PROFILE_DEFAULT_ALBUM_NAME');
        }

        // reference to the group or event id
        if ($type == 'group' || $type == 'event') {
            $fieldId = $type . 'id';
            $this->$fieldId = $parentId;
        }

        $this->store();

        return $this->id;
    }

    public function getLatestPhoto($limit = 5) {
        $db = JFactory::getDBO();

        $sql = "SELECT * FROM #__community_photos WHERE ".$db->quoteName('albumid')."=".$db->quote($this->id)
                          ." AND ". $db->quoteName('status'). " != ".$db->quote('temp')
                          ." ORDER BY ". $db->quoteName('id') ."  DESC LIMIT 0,$limit";
        $db->setQuery($sql);
        $result = $db->loadObjectList();

        $_data = array();
        foreach($result as $data){
            $tmp = JTable::getInstance( 'Photo' , 'cTable' );
            $tmp->load($data->id);

            $_data[] = $tmp;
        }

        return $_data;
    }

    /**
     * checks if user default avatar album exists
     *
     * @param $id
     * @param $type
     * @return mixed
     */
    public function isAvatarAlbumExists($id,$type){
        $db = JFactory::getDBO();
        $field = array('profile' => 'creator', 'group' => 'groupid', 'event' => 'eventid');

        $query = 'SELECT ' . $db->quoteName('id') . ' FROM '
            . $db->quoteName('#__community_photos_albums')
            . ' WHERE ' . $db->quoteName($field[strtolower($type)]) . ' = ' . $db->Quote($id)
            . ' AND ' . $db->quoteName('type') . ' = ' . $db->Quote($type . '.avatar');

        $db->setQuery($query);

        return $db->loadResult();
    }

    public function addAvatarAlbum($id, $type) {

        $my = CFactory::getUser();
        //for profile type, the id is user id
        if($type == 'profile') {
            $my = CFactory::getUser($id);
        }

        $now = new JDate();
        $this->creator = $my->id;
        $this->name = JText::sprintf('COM_COMMUNITY_PROFILE_AVATAR_NAME', ucfirst($my->getDisplayName()) );
        $this->type = $type . '.avatar';
        $this->path = 'images/avatar' . '/' . $type .'/';
        $this->created = $now->toSql();
        $this->permissions = 0;

        if ($type != 'profile') {
            $cTable = JTable::getInstance(ucfirst($type), 'CTable');
            $cTable->load($id);
            $name = property_exists($cTable, 'name') ? $cTable->name : $cTable->title;
            $this->name = JText::sprintf('COM_COMMUNITY_'.$type.'_AVATAR_NAME', ucfirst($name));
            //overwrite
            if($type == 'group'){
                $this->groupid = $id;
            }else{
                //this must be event id
                $this->eventid = $id;
            }
        }else{
            $this->name = JText::sprintf('COM_COMMUNITY_ALBUMS_NAME_DEFAULT_AVATAR', $my->getDisplayName());
        }

        $this->store();

        return $this->id;
    }

}
