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

jimport('joomla.filesystem.file');
jimport('joomla.filesystem.folder');

// Deprecated since 1.8.x to support older modules / plugins
class CommunityModelPhotos extends JCCModel implements CLimitsInterface {

    var $_pagination;
    var $total;
    var $test;

    public function __construct() {
        parent::__construct();
        $config = CFactory::getConfig();
        $mainframe = JFactory::getApplication();
        $jinput = $mainframe->input;

        // Get the pagination request variables
        $limit = $config->get('pagination');
        $limitstart = $jinput->request->get('limitstart', 0, 'INT');

        if (empty($limitstart)) {
            $limitstart = $jinput->get('limitstart', 0, 'uint');
        }

        // In case limit has been changed, adjust it
        $limitstart = ($limit != 0 ? (floor($limitstart / $limit) * $limit) : 0);

        $this->setState('limit', $limit);
        $this->setState('limitstart', $limitstart);
    }

    /**
     * Retrieves total number of photos from the site.
     * @param   none
     *
     * @return  int $total  Total number of photos.
     * */
    public function getTotalSitePhotos() {
        $db = $this->getDBO();

        $query = 'SELECT COUNT(1) FROM ' . $db->quoteName('#__community_photos') . ' '
                . 'WHERE ' . $db->quoteName('published') . '=' . $db->Quote(1);

        $db->setQuery($query);
        try {
            $total = $db->loadResult();
        } catch (Exception $e) {
            JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
        }

        return $total;
    }

    public function cleanUpTokens() {
        $date = JDate::getInstance();
        $db = $this->getDBO();

        $query = 'DELETE FROM ' . $db->quoteName('#__community_photos_tokens') . ' '
                . 'WHERE ' . $db->quoteName('datetime') . '<= DATE_SUB(' . $db->Quote($date->toSql()) . ', INTERVAL 1 HOUR)';

        $db->setQuery($query);
        try {
            $db->execute();
        } catch (Exception $e) {
            JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
        }
    }

    public function getUserUploadToken($userId) {
        $db = JFactory::getDBO();

        $query = 'SELECT * FROM '
                . $db->quoteName('#__community_photos_tokens') . ' '
                . 'WHERE ' . $db->quoteName('userid') . '=' . $db->Quote($userId);

        $db->setQuery($query);
        $result = $db->loadObject();

        return $result;
    }

    public function addUserUploadSession($token) {
        $db = JFactory::getDBO();

        $db->insertObject('#__community_photos_tokens', $token);

        return $this;
    }

    public function update($data, $type = 'photo') {
        // Set creation date
        if (!isset($data->created)) {
            $today = JDate::getInstance();
            $data->created = $today->toSql();
        }

        if (isset($data->id) && $data->id != 0)
            $func = '_update' . JString::ucfirst($type);
        else
            $func = '_create' . JString::ucfirst($type);

        return $this->$func($data);
    }

    // A user updated his view permission, change the permission level for
    // all album and photos
    public function updatePermission($userid, $permission) {
        $db = $this->getDBO();
        $query = 'UPDATE #__community_photos_albums SET '.$db->quoteName('permissions').'='
                . $db->Quote($permission)
                . ' WHERE '.$db->quoteName('creator').'='
                . $db->Quote($userid);
        $db->setQuery($query);
        try {
            $db->execute();
        } catch (Exception $e) {
            JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
        }

        $query = 'UPDATE #__community_photos SET '.$db->quoteName('permissions').'='
                . $db->Quote($permission)
                . ' WHERE '.$db->quoteName('creator').'='
                . $db->Quote($userid);
        $db->setQuery($query);
        try {
            $db->execute();
        } catch (Exception $e) {
            JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
        }

        // update permissions in activity streams as well
        $activityModel = CFactory::getModel('activities');
        $activityModel->updatePermission($permission, null, $userid, 'photos');

        return $this;
    }

    public function updatePermissionByGroup($groupid, $permission) {
        $db = $this->getDBO();
        $query = 'UPDATE ' . $db->quoteName('#__community_photos_albums')
                . ' SET ' . $db->quoteName('permissions') . ' = '
                . $db->Quote($permission)
                . ' WHERE ' . $db->quoteName('groupid') . ' = '
                . $db->Quote($groupid);
        $db->setQuery($query);
        try {
            $db->execute();
        } catch (Exception $e) {
            JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
        }

        $query = 'SELECT ' . $db->quoteName('id')
                . ' FROM ' . $db->quoteName('#__community_photos_albums')
                . ' WHERE ' . $db->quoteName('groupid') . ' = '
                . $db->Quote($groupid);
        $db->setQuery($query);
        try {
            $albumIDs = $db->loadColumn();
        } catch (Exception $e) {
            JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
        }

        $albumIDs = implode(', ', $albumIDs);
        if ($albumIDs) {
            $query = 'UPDATE ' . $db->quoteName('#__community_photos')
                    . ' SET ' . $db->quoteName('permissions') . ' = '
                    . $db->Quote($permission)
                    . ' WHERE ' . $db->quoteName('albumid')
                    . ' IN (' . $albumIDs . ') ';
            $db->setQuery($query);
            try {
                $db->execute();
            } catch (Exception $e) {
                JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
            }
        }

        // update permissions in activity streams as well
        $activityModel = CFactory::getModel('activities');
        $activityModel->updatePermissionByCid($permission, null, $albumIDs, 'photos');

        return $this;
    }

    public function updatePermissionByAlbum($albumid, $permissions) {
        $db = $this->getDBO();
        $query = 'UPDATE #__community_photos SET '.$db->quoteName('permissions').'=' . $db->Quote($permissions) . ' WHERE '.$db->quoteName('albumid').'=' . $db->Quote($albumid);
        $db->setQuery($query);
        try {
            $db->execute();
        } catch (Exception $e) {
            JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
        }

        return $this;
    }

    private function _createPhoto($data) {
        $db = $this->getDBO();

        // Fix the directory separators.
        $data->image = CString::str_ireplace('\\', '/', $data->image);
        $data->thumbnail = CString::str_ireplace('\\', '/', $data->thumbnail);

        try {
            $db->insertObject('#__community_photos', $data);
        } catch (Exception $e) {
            JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
        }

        $data->id = $db->insertid();

        return $data;
    }

    private function _createAlbum($data) {
        $db = $this->getDBO();

        // New record, insert it.
        try {
            $db->insertObject('#__community_photos_albums', $data);
        } catch (Exception $e) {
            JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
        }

        $data->id = $db->insertid();

        return $data;
    }

    /**
     * Removes a photo from the database and the file.
     *
     * @access	public
     * @param	string 	User's id.
     * @returns boolean true upon success.
     */
    public function removePhoto($id, $type = PHOTOS_USER_TYPE) {
        $photo = JTable::getInstance('Photo', 'CTable');
        $photo->load($id);
        $photo->delete();
    }

    public function get($id, $type = 'photos') {
        $func = '_get' . JString::ucfirst($type);
        return $this->$func($id);
    }

    public function getPagination() {
        return $this->_pagination;
    }

    public function getFirstPhoto($albumId = null) {
        $db = $this->getDBO();

        $query = 'SELECT a.* FROM ' . $db->quoteName('#__community_photos') . ' AS a WHERE albumid=' . $db->Quote($albumId) . ' ' .
                ' ORDER BY a.'.$db->quoteName('ordering').' LIMIT 1 ';

        $db->setQuery($query);
        $result = $db->loadObject();

        $album = JTable::getInstance('Photo', 'CTable');
        $album->bind($result);

        return $album;
    }

    /**
     * Return a list of photos from specific album
     *
     * @param int $albumId	The album id that we want to retrieve photos from
     * @param string $photoType
     * @param null $limit
     * @param null $permission
     * @param string $orderType
     * @param string $primaryOrdering
     * @param bool $hidePrivateGroupPhotos, if set to true, the results will filter out private group photo
     * @return array
     */
    public function getAllPhotos($albumId = null, $photoType = PHOTOS_USER_TYPE, $limit = null, $permission = null, $orderType = 'DESC', $primaryOrdering = 'ordering', $hidePrivateGroupPhotos = false) {
        $db = $this->getDBO();

        if(!$photoType){
            //in no photo type is set, will grab all the photos
            $where = ' WHERE 1 ';
        }else{
            $where = ' WHERE b.'.$db->quoteName('type').' = ' . $db->Quote($photoType);
        }


        if (!is_null($albumId)) {
            $where .= ' AND b.'.$db->quoteName('id')
                    . '=' . $db->Quote($albumId)
                    . ' AND a.'.$db->quoteName('albumid')
                    . '=' . $db->Quote($albumId);
        }

        // Only apply the permission if explicitly specified
        if (!is_null($permission)) {
            if(is_array($permission)){
                $permissions = implode(',', $permission);
                $where .= 'AND b.'.$db->quoteName('permissions').' IN ('.$permissions.') ';
            }else{
                $where .= ' AND b.'.$db->quoteName('permissions')
                    . '=' . $db->Quote($permission);
            }
        }

        $where .= ' AND a.'.$db->quoteName('published').'=' . $db->Quote(1);
        $limitWhere = '';

        if (!is_null($limit)) {
            $limit = ($limit < 0) ? 0 : $limit;
            $limitWhere .= ' LIMIT ' . $limit;
        }

        $query = 'SELECT a.* FROM ' . $db->quoteName('#__community_photos') . ' AS a';
        $query .= ' INNER JOIN ' . $db->quoteName('#__community_photos_albums') . ' AS b';
        $query .= ' ON a.'.$db->quoteName('albumid').' = b.'.$db->quoteName('id');
        $query .= $where;
        $query .= ' AND '.$db->quoteName('status').' <> '.$db->quote('temp');

        //hide all photos from private group
        if($hidePrivateGroupPhotos){
            $query .= " AND b.".$db->quoteName('groupid')." NOT IN(SELECT id FROM ".$db->quoteName('#__community_groups')." WHERE ".$db->quoteName('approvals')."='1') ";
        }

        $query .= ' ORDER BY ';
        switch($primaryOrdering){
            case 'ordering':
                $query .= 'a.'.$db->quoteName('ordering').', a.'.$db->quoteName('created').' ';
                break;
            case 'hits':
            case 'hit' :
                $query .= 'a.'.$db->quoteName('hits').' ';
                break;
            default:
                $query .=  'a.'.$db->quoteName('created').' ';
        }

        $query .= $orderType;

        $query .= $limitWhere;

        $db->setQuery($query);

        try {
            $result = $db->loadObjectList();
        } catch (Exception $e) {
            JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
        }

        $data = array();
        foreach ($result as $row) {
            $album = JTable::getInstance('Photo', 'CTable');
            $album->bind($row);
            $data[] = $album;
        }
        return $data;
    }

    /**
     * Return a list of photos from specific album
     *
     * @param	int	$id	The album id that we want to retrieve photos from
     * @param boolean $includeUnpublished will include the photos which is unpublished
     */
    public function getPhotos($id, $limit = null, $limitstart = null, $includeUnpublished = false) {
        $db = $this->getDBO();

        // Get limit
        $limit = ( is_null($limit) ) ? $this->getState('limit') : $limit;
        $limitstart = ( is_null($limitstart) ) ? $this->getState('limitstart') : $limitstart;

        // Get total photos from specific album
        $query = 'SELECT COUNT(*) FROM ' . $db->quoteName('#__community_photos') . ' '
                . 'WHERE ' . $db->quoteName('albumid') . '=' . $db->Quote($id);

        $db->setQuery($query);
        try {
            $total = $db->loadResult();
        } catch (Exception $e) {
            JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
        }

        // Apply pagination
        if (empty($this->_pagination)) {
            jimport('joomla.html.pagination');
            $this->_pagination = new JPagination($total, $limitstart, $limit);
        }

        //include unpublished photo or not
        if (!$includeUnpublished) {
            $includeUnpublished = ' AND '.$db->quoteName('published').'=' . $db->Quote(1);
        } else {
            $includeUnpublished = ''; //include
        }

        //var_dump($limitstart);
        // Get all photos from specific albumid
        $query = 'SELECT * FROM ' . $db->quoteName('#__community_photos') . ' '
                . 'WHERE ' . $db->quoteName('albumid') . '=' . $db->Quote($id) . ' '
                . $includeUnpublished
                . ' ORDER BY '.$db->quoteName('ordering').' ASC, '.$db->quoteName('created').' DESC '
                . 'LIMIT ' . $limitstart . ',' . $limit;

        $db->setQuery($query);
        try {
            $result = $db->loadObjectList();
        } catch (Exception $e) {
            JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
        }

        $data = array();
        foreach ($result as $row) {
            $photo = JTable::getInstance('Photo', 'CTable');
            $photo->bind($row);
            $data[] = $photo;
        }

        return $data;
    }

    /**
     * @param	integer albumid Unique if of the album
     */
    public function getAlbum($albumId) {
        $album = JTable::getInstance('Album', 'CTable');
        $album->load($albumId);

        return $album;
    }

    /**
     * Return the
     * @param type $typeId is userid for user type and group id for group type
     * @param type $type
     * @return type
     */
    public function getDefaultAlbum($typeId, $type = PHOTOS_USER_TYPE) {
        $db = $this->getDBO();
        $query = '';
        switch ($type) {
            case PHOTOS_GROUP_TYPE:
                $query = 'SELECT * FROM ' . $db->quoteName('#__community_photos_albums') . ' '
                        . 'WHERE ' . $db->quoteName('groupid') . '=' . $db->Quote($typeId) . ' '
                        . 'AND ' . $db->quoteName('default') . '=' . '1';
                break;
            case PHOTOS_EVENT_TYPE:
                $query = 'SELECT * FROM ' . $db->quoteName('#__community_photos_albums') . ' '
                    . 'WHERE ' . $db->quoteName('eventid') . '=' . $db->Quote($typeId) . ' '
                    . 'AND ' . $db->quoteName('default') . '=' . '1';
                break;
            default:
                $query = 'SELECT * FROM ' . $db->quoteName('#__community_photos_albums') . ' '
                        . 'WHERE '
                        . $db->quoteName('creator') . '=' . $db->Quote($typeId) . ' '
                        . 'AND ' . $db->quoteName('groupid') . '=' . '0' . ' '
                        . 'AND ' . $db->quoteName('eventid') . '=' . '0' . ' '
                        . 'AND ' . $db->quoteName('default') . '=' . '1';
        }

        $db->setQuery($query);

        $result = $db->loadObject();

        // if default album exist, return as album type
        if ($result) {
            $album = JTable::getInstance('Album', 'CTable');
            $album->bind($result);
            $result = $album;
        }

        return $result;
    }

    public function getDefaultGifAlbum($typeId, $type = PHOTOS_USER_TYPE){
        $db = $this->getDbo();
        $query = '';
        $albumType = '';
        $eventid = 0;
        $groupid = 0;
        $defaultAlbumName = '';
        $partialPath = '';

        switch($type){
            case PHOTOS_GROUP_TYPE :
                $query = 'SELECT * FROM ' . $db->quoteName('#__community_photos_albums') . ' '
                    . 'WHERE ' . $db->quoteName('groupid') . '=' . $db->Quote($typeId) . ' '
                    . 'AND ' . $db->quoteName('type') . '=' . $db->quote('group.gif');
                $albumType = 'group.gif';
                $groupid = $typeId;
                $defaultAlbumName = JText::_('COM_COMMUNITY_GROUP_GIF_ALBUM_NAME');
                $partialPath = '/groupphotos' . '/' . $groupid . '/';
                break;
            case PHOTOS_EVENT_TYPE:
                $query = 'SELECT * FROM ' . $db->quoteName('#__community_photos_albums') . ' '
                    . 'WHERE ' . $db->quoteName('eventid') . '=' . $db->Quote($typeId) . ' '
                    . 'AND ' . $db->quoteName('type') . '=' . $db->quote('event.gif');
                $albumType = 'event.gif';
                $eventid = $typeId;
                $defaultAlbumName = JText::_('COM_COMMUNITY_EVENT_GIF_ALBUM_NAME');
                $partialPath = '/eventphotos' . '/' . $eventid . '/';
                break;
            default:
                //user type
                $query = 'SELECT * FROM ' . $db->quoteName('#__community_photos_albums') . ' '
                    . 'WHERE ' . $db->quoteName('creator') . '=' . $db->Quote($typeId) . ' '
                    . 'AND ' . $db->quoteName('type') . '=' . $db->quote('profile.gif');
                $albumType = 'profile.gif';
                $defaultAlbumName = JText::_('COM_COMMUNITY_PROFILE_GIF_ALBUM_NAME');
                $partialPath = '/photos' . '/' .  CFactory::getUser()->id. '/';
                break;
        }

        $db->setQuery($query);

        $result = $db->loadObject();

        // if album exist, return as album type
        if ($result) {
            $album = JTable::getInstance('Album', 'CTable');
            $album->bind($result);
        }else{
            //if album doesn't exists, lets create a new one for them
            $album = JTable::getInstance('Album', 'CTable');
            $now = new JDate();

            $album->creator = CFactory::getUser()->id;
            $album->type = $albumType;
            $album->created = $now->toSql();
            $album->groupid = $groupid;
            $album->eventid = $eventid;
            $album->name = $defaultAlbumName;

            $album->store(); //we must save first to get the id for path

            //album path
            $config = CFactory::getConfig();
            $storage = JPATH_ROOT . '/' . $config->getString('photofolder');
            $albumPath = $storage . $partialPath . $album->id;
            $albumPath = CString::str_ireplace(JPATH_ROOT . '/', '', $albumPath);
            $albumPath = CString::str_ireplace('\\', '/', $albumPath);
            $album->path = $albumPath;

            $album->store();//store one more time for the album id

            return $album;
        }

        return $album;
    }

    /**
     * Return total photos in a given album id.
     *
     * @param	int	$id	The album id.
     */
    public function getTotalPhotos($albumId) {
        $db = $this->getDBO();

        $query = 'SELECT COUNT(1) FROM ' . $db->quoteName('#__community_photos') . ' '
                . 'WHERE ' . $db->quoteName('albumid') . '=' . $db->Quote($albumId);

        $db->setQuery($query);
        try {
            $total = $db->loadResult();
        } catch (Exception $e) {
            JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
        }

        return $total;
    }

    /**
     * @param int $userId
     * @param int $limit
     * @param string $sortBy
     * @param string $albumFilter special : get all the albums except for user default albums/group and events
     * @return array
     */
    public function getAllAlbums($userId = 0, $limit = 0, $sortBy = 'date', $albumFilter = 'special') {
        $db = $this->getDBO();

        $isAdmin = (int) COwnerHelper::isCommunityAdmin();

        // Get limit
        $limit = $limit == 0 ? $this->getState('limit') : $limit;
        $limitstart = $this->getState('limitstart');
        $extraSQL = '';

        $permissions = ($userId == 0) ? 10 : 20;
        $permissions = COwnerHelper::isCommunityAdmin() ? 40 : $permissions;


        //$extraSQL .= ' WHERE (( permissions <=' . $db->Quote($permissions) . ' OR (creator=' . $db->Quote($userId) . ' AND permissions <=' . $db->Quote(40) . ') )';
        //need to grab friends' album that has "Friends only" permission as well
        $friendmodel = CFactory::getModel('friends');
        $friends = $friendmodel->getFriendIds($userId);

        if (!empty($friends)) {
            $extraSQL .= ' WHERE ( permissions <=' . $db->Quote($permissions) . ' OR (creator=' . $db->Quote($userId) . ' AND permissions <=' . $db->Quote(40) . ') ';
            $extraSQL .= ' OR (creator IN(' . implode(',', $friends) . ') AND permissions = ' . $db->Quote(30) . ') )';
        }else{
            $extraSQL .= ' WHERE ( permissions <=' . $db->Quote($permissions) . ' OR (creator=' . $db->Quote($userId) . ' AND permissions <=' . $db->Quote(40) . ') )';
        }

        /* if not administrator than we'll filter profile cover album */
        if (!$isAdmin) {
            $extraSQL .= ' AND ( ';
            /* get own profile' cover album */
            $extraSQL .= ' ( ' . $db->quoteName('type') . ' = ' . $db->quote('profile.cover') . ' AND ' . $db->quoteName('creator') . ' = ' . $userId . ' ) ';
            /* or get none profile' cover album */
            $extraSQL .= ' OR ( ' . $db->quoteName('type') . ' != ' . $db->quote('profile.cover') . ' ) ';
            $extraSQL .= ' ) ';
        }

        //we will filter the events and groups out if this is all albums or my albums category
        if($albumFilter == 'profile'){
            $extraSQL .= ' AND '. $db->quoteName('type') .' NOT LIKE '.$db->Quote('event%');
            $extraSQL .= ' AND '. $db->quoteName('type') .' NOT LIKE '.$db->Quote('group%');
        }elseif($albumFilter == 'group'){
            // filter group album only
            $extraSQL .= ' AND '. $db->quoteName('type') .' LIKE '.$db->Quote('group%');
            $extraSQL .= ' AND ('. $db->quoteName('type') .' = '.$db->Quote('group').' AND '.$db->quoteName('default').' <> 1 )';
            $extraSQL .= ' AND '. $db->quoteName('type') .' NOT LIKE '.$db->Quote('%avatar');
            $extraSQL .= ' AND '. $db->quoteName('type') .' NOT LIKE '.$db->Quote('%cover');
        }elseif($albumFilter == 'event'){
            $extraSQL .= ' AND '. $db->quoteName('eventid') .' > '.$db->Quote('1'); // first filter by event id first
            $extraSQL .= ' AND '. $db->quoteName('default') .' <> '.$db->Quote('1'); // not the default stream album
            $extraSQL .= ' AND '. $db->quoteName('type') .' LIKE '.$db->Quote('event'); //event type only
        }elseif($albumFilter == 'special'){
            // get all albums except for group/event albums and 3 default user albums
            $extraSQL .= ' AND '. $db->quoteName('type') .' NOT LIKE '.$db->Quote('event%');
            $extraSQL .= ' AND '. $db->quoteName('type') .' NOT LIKE '.$db->Quote('group%');
            $extraSQL .= ' AND ('. $db->quoteName('type') .' = '.$db->Quote('user').' AND '.$db->quoteName('default').' <> 1 )';
            $extraSQL .= ' AND '. $db->quoteName('type') .' NOT LIKE '.$db->Quote('profile%');
        }elseif($albumFilter == 'exclude_default'){
            $extraSQL .= ' AND '. $db->quoteName('type') .' NOT LIKE '.$db->Quote('%avatar');
            $extraSQL .= ' AND '. $db->quoteName('type') .' NOT LIKE '.$db->Quote('%cover');
            $extraSQL .= ' AND '. $db->quoteName('name') .' <> '.$db->Quote('Stream Photos');
            $extraSQL .= ' AND '. $db->quoteName('type') .' <> '.$db->Quote('user');
        }


        $query = 'SELECT * FROM ' . $db->quoteName('#__community_photos_albums');
        $query .= $extraSQL;

        switch($sortBy){
            case 'featured':
                $featured = new CFeatured(FEATURED_ALBUMS);
                $featuredAlbums = implode(',',$featured->getItemIds());
                if($featuredAlbums){
                    $query .= " ORDER BY (".$db->quoteName('id')." IN (".$featuredAlbums.")) DESC, id ";

                }
                break;
            case 'featured_only':
                $featured = new CFeatured(FEATURED_ALBUMS);
                $featuredAlbums = implode(',',$featured->getItemIds());
                if($featuredAlbums){
                    $query .= ' AND '.$db->quoteName('id').' IN ('.$featuredAlbums.')' ;
                }
                break;
            case 'hit' :
                $query .= " ORDER BY ".$db->quoteName('hits')." DESC ";
                break;
            case 'name' :
                $query .= " ORDER BY ".$db->quoteName('name')." ASC ";
                break;
            default:
                $query .= " ORDER BY ".$db->quoteName('created')." DESC ";
                break;
        }

        $db->setQuery($query);
        try {
            $result = $db->loadObjectList();
        } catch (Exception $e) {
            JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
        }
        $total = count($result);

        //do not limit if this is a sort by featured only because its supposed to be a filter
        if($sortBy == 'featured_only'){
            $db->setQuery($query);
        }else{
            $db->setQuery($query . 'LIMIT ' . $limitstart . ',' . $limit);
        }

        try {
            $result = $db->loadObjectList();
        } catch (Exception $e) {
            JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
        }

        // Update their correct Thumbnails and check album permissions
        //$this->_updateThumbnail($result);

        $data = $this->sortAlbums($result, $sortBy);

        jimport('joomla.html.pagination');
        // Apply pagination based on counted data
        $this->_pagination = new JPagination($total, $limitstart, $limit);

        $tmpData = array();

        foreach ($data as $_data) {
            $tmpData[] = $_data;
        }

        return $tmpData;
    }

    public function checkAlbumsPermissions($row, $myId) {
        switch ($row->permissions) {
            case 0:
                $result = true;
                break;
            case 20:
                $result = !empty($myId) ? true : false;
                break;
            case 30:
                $result = CFriendsHelper::isConnected($row->creator, $myId) ? true : false;
                break;
            case 40:
                $result = $row->creator == $myId ? true : false;
                break;
            default:
                $result = false;
                break;
        }

        return $result;
    }

    /**
     * Get site wide albums
     *
     * */
    public function getSiteAlbums($type = PHOTOS_USER_TYPE) {
        $db = $this->getDBO();
        $searchType = '';

        if ($type == PHOTOS_GROUP_TYPE) {
            $searchType = PHOTOS_GROUP_TYPE;
        } else {
            $searchType = PHOTOS_USER_TYPE;
        }

        // Get limit
        $limit = $this->getState('limit');
        $limitstart = $this->getState('limitstart');

        // Get total albums
        $query = 'SELECT COUNT(DISTINCT(a.id)) '
                . 'FROM ' . $db->quoteName('#__community_photos_albums') . ' AS a '
                . 'INNER JOIN ' . $db->quoteName('#__community_photos') . ' AS b '
                . 'ON a.id=b.albumid '
                . 'WHERE a.type=' . $db->Quote($searchType);

        $db->setQuery($query);
        try {
            $total = $db->loadResult();
        } catch (Exception $e) {
            JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
        }

        // Appy pagination
        if (empty($this->_pagination)) {
            jimport('joomla.html.pagination');
            $this->_pagination = new JPagination($total, $limitstart, $limit);
        }

        $where = ' WHERE a.type=' . $db->Quote($searchType);
        $result = $this->getAlbumPhotoCount($where, $limit, $limitstart);

        // Update their correct Thumbnails
        $this->_updateThumbnail($result);

        $data = array();
        foreach ($result as $row) {
            $album = JTable::getInstance('Album', 'CTable');
            $album->bind($row);
            $data[] = $album;
        }
        return $data;
    }

    public function getAlbumCount($where = '') {

        $db = $this->getDBO();
        // Get total albums
        $query = 'SELECT COUNT(*) '
                . 'FROM ' . $db->quoteName('#__community_photos_albums') . ' AS a '
                . $where;

        $db->setQuery($query);
        try {
            $total = $db->loadResult();
        } catch (Exception $e) {
            JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
        }

        return $total;
    }

    public function getAlbumPhotoCount($where = '', $limit = NULL, $limitstart = NULL, $orderBy = '') {

        $db = $this->getDBO();

        if($orderBy == ''){
            $orderBy =' ORDER BY a.'.$db->quoteName('created').' DESC';
        }

        $query = 'SELECT a.*, '
                . 'COUNT( DISTINCT(b.id) ) AS count, '
                . 'MAX(b.created) AS lastupdated, '
                . 'c.thumbnail as thumbnail, '
                . 'c.storage AS storage, '
                . 'c.id as photoid '
                . 'FROM ' . $db->quoteName('#__community_photos_albums') . ' AS a '
                . 'LEFT JOIN ' . $db->quoteName('#__community_photos') . ' AS b '
                . 'ON a.id=b.albumid '
                . 'LEFT JOIN ' . $db->quoteName('#__community_photos') . ' AS c '
                . 'ON a.photoid=c.id '
                . $where
                . 'GROUP BY a.id '
                . $orderBy;

        if (!is_null($limit) && !is_null($limitstart)) {
            $query .= ' LIMIT ' . $limitstart . ',' . $limit;
        }

        $db->setQuery($query);
        try {
            $result = $db->loadObjectList();
        } catch (Exception $e) {
            JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
        }

        return $result;
    }

    public function getGroupAlbums($groupId = '', $pagination = false, $doubleLimit = false, $limit = "", $isAdmin = false, $creator = '', $excludeType = array()) {
        $creatorfilter = (!$isAdmin && !empty($creator)) ? $creator : '';
        $result = $this->_getAlbums($groupId, PHOTOS_GROUP_TYPE, $pagination, $doubleLimit, $limit, $creatorfilter, 'date', $excludeType);

        return $result;
    }

    public function getEventAlbums($eventId = '', $pagination = false, $doubleLimit = false, $limit = "", $isAdmin = false, $creator = '', $excludeType = array()) {
        $creatorfilter = (!$isAdmin && !empty($creator)) ? $creator : '';
        $result = $this->_getAlbums($eventId, PHOTOS_EVENT_TYPE, $pagination, $doubleLimit, $limit, $creatorfilter, 'date', $excludeType);

        return $result;
    }

    /**
     * Get the albums for specific user or site wide
     *
     * @param	userId	string	The specific user id
     *
     * */
    public function getAlbums($userId = '', $pagination = false, $doubleLimit = false, $sortBy = 'date', $excludeType = array()) {
        return $this->_getAlbums($userId, PHOTOS_USER_TYPE, $pagination, $doubleLimit, '', '', $sortBy, $excludeType);
    }

    /**
     * @param string $userId
     * @param bool $pagination
     * @param bool $doubleLimit
     * @param array $exclude exclude profile system album to be listed
     * @return array
     */
    public function getProfileAlbums($userId = '', $pagination = false, $doubleLimit = false, $sortBy = 'date', $exclude = array('profile.avatar', 'profile.Cover', 'profile.gif')){
        $albums = $this->_getAlbums($userId, PHOTOS_PROFILE_TYPE, $pagination, $doubleLimit, '', '', $sortBy, $exclude);
        return $albums;
    }

    /**
     * @param mixed $id
     * @param $type
     * @param bool $pagination
     * @param bool $doubleLimit
     * @param string $limit
     * @param string $creator
     * @param string $sort
     * @param array $excludeType Exclude the type of album
     * @return array
     */
    public function _getAlbums($id, $type, $pagination = false, $doubleLimit = false, $limit = "", $creator = '', $sort = 'date', $excludeType = array()) {
        $db = $this->getDBO();
        $extraSQL = ' WHERE a.type != ' . $db->Quote('');

        if($id && $type == PHOTOS_EVENT_TYPE){
            if(is_array($id)){
                $extraSQL .= ' AND a.eventid IN(' . implode(',',$id) . ') ';
            }else{
                $extraSQL .= ' AND a.eventid=' . $db->Quote($id) . ' ';
            }
            if (!empty($creator)) {
                $extraSQL .= ' AND a.creator=' . $db->Quote($creator) . ' ';
            }
        } elseif (!empty($id) && $type == PHOTOS_GROUP_TYPE) {
            if(is_array($id)){
                $extraSQL .= ' AND a.groupid IN(' . implode(',',$id) . ') ';
            }else{
                $extraSQL .= ' AND a.groupid=' . $db->Quote($id) . ' ';
            }
            if (!empty($creator)) {
                $extraSQL .= ' AND a.creator=' . $db->Quote($creator) . ' ';
            }
        } elseif (!empty($id) && $type == PHOTOS_USER_TYPE) {
            $extraSQL .= ' AND a.creator=' . $db->Quote($id) . ' ';
            // privacy

            $permission = CPrivacy::getAccessLevel(null, $id);
            $extraSQL .= ' AND a.permissions <=' . $db->Quote($permission) . ' ';
        }elseif( !empty($id) && $type == PHOTOS_PROFILE_TYPE ){
            $extraSQL .= ' AND a.creator=' . $db->Quote($id)
                      .' AND a.groupid=' . $db->Quote(0) . ' AND a.eventid= '. $db->Quote(0);
                     // .' AND a.type <> '.$db->quote('profile.cover');
        }

        if(is_array($excludeType) && count($excludeType) > 0){
            foreach($excludeType as $type){
                if($type == 'event.default' || $type == 'group.default' || $type == 'profile.default'){
                    //this is to filter out the default album of the event,group or profile
                    $extraSQL .= ' AND a.default=0 ';
                }
               $extraSQL .= ' AND a.type NOT LIKE '.$db->Quote(''.$type.'').' ';
            }
        }

        // Get limit
        $limit = (!empty($limit)) ? $limit : $this->getState('limit');
        $limit = ( $doubleLimit ) ? $this->getState('limit') : $limit;
        $limitstart = $this->getState('limitstart');

        // Get total albums
        $total = $this->getAlbumCount($extraSQL);
        $this->total = $total;

        //special case for featured only, it must not be paginated
        if($sort == 'featured'){
            $featured = new CFeatured(FEATURED_ALBUMS);
            $featuredAlbums = implode(',',$featured->getItemIds());
            if($featuredAlbums) {
                $order = " ORDER BY (a.".$db->quoteName('id')." IN (" . $featuredAlbums . ")) DESC, a.id ";
            }
            $result = ($pagination) ? $this->getAlbumPhotoCount($extraSQL, $limit, $limitstart,$order ) : $this->getAlbumPhotoCount($extraSQL, null, null, $order);

        }elseif($sort == 'featured_only'){
            $result = $this->getAlbumPhotoCount($extraSQL, NULL, NULL,  ' ORDER BY '.$db->quoteName('name').' ASC');
        }elseif($sort == 'name'){
            $result = ($pagination) ? $this->getAlbumPhotoCount($extraSQL, $limit, $limitstart, ' ORDER BY '.$db->quoteName('name').' ASC') : $this->getAlbumPhotoCount($extraSQL, NULL, NULL,  ' ORDER BY '.$db->quoteName('name').' ASC');
        }elseif($sort == 'hit') {
            $result = ($pagination) ? $this->getAlbumPhotoCount($extraSQL, $limit, $limitstart, ' ORDER BY '.$db->quoteName('hits').' DESC') : $this->getAlbumPhotoCount($extraSQL, null, null, ' ORDER BY '.$db->quoteName('hits').' DESC');
        }else{
            $result = ($pagination) ? $this->getAlbumPhotoCount($extraSQL, $limit, $limitstart) : $this->getAlbumPhotoCount($extraSQL);
        }

        /* filter results, album that has photos + all unpublished = not to be displayed
         * 				   album that has no photos = display
         */

        foreach ($result as $key => $res) {
            $temp = $this->getPhotos($res->id, null, null, true);
            $hasPhoto = true; //assume all photo is temp

            if (count($temp) > 0) {
                foreach ($temp as $tempPhoto) {
                    if ($tempPhoto->published == 1) {
                        $hasPhoto = false; // this album has photos, show this album
                        break;
                    } elseif($tempPhoto->published == 0 && $tempPhoto->status =="delete") {
                        $hasPhoto = false; // this album has photos, show this album
                        break;
                     }
                }
            } else {
                $hasPhoto = false;
            }

            if ($hasPhoto) {
                unset($result[$key]);
            }
        }

        // Update their correct Thumbnails
        $this->_updateThumbnail($result);

        //sort the albums
        $data = $this->sortAlbums($result,$sort);


        // Apply pagination based on counted data
        if($limit > count($data) && empty($this->_pagination)){
            jimport('joomla.html.pagination');
            $this->_pagination = new JPagination(count($data), $limitstart, $limit);
        }else{
            $this->_pagination = new JPagination($total, $limitstart, $limit);
        }

        $tmpData = array();

        if ($type == PHOTOS_PROFILE_TYPE) {
            $type = PHOTOS_USER_TYPE;
        }

        foreach ($data as $_data) {
            if ( ( $_data->default == 1 ) && ( $type == $_data->type ) && $sort != 'hit' ) {
                array_unshift($tmpData, $_data);
            } else {
                $tmpData[] = $_data;
            }
        }


        return $tmpData;
    }

    /**
     * //@todo Use usort to sort the data when PHP 5.3 is the minimal requirement
     * Sort album key based on the key data comparison given
     * @param $albums
     * @param string $sort
     * @return array
     */
    private function sortAlbums($albums, $sort = 'date'){
        $data = array();

        foreach ($albums as $row) {
            $album = JTable::getInstance('Album', 'CTable');
            $album->bind($row);
            $album->thumbnail = $album->getCoverThumbPath();

            $lastupdated = new DateTime($album->lastupdated);

            //sorting happens here
            switch($sort){
                case 'hit':
                    //$key = $this->getNextUniqueAlbumKey($album->hits,$data);
                    $data[] = $album;
                    break;
                case 'name' :
                    if (array_key_exists($album->name, $data)) {
                        $data[strtolower($album->name).(strtotime("now")+rand('1','100000'))] = $album;
                    } else {
                        $data[strtolower($album->name).strtotime("now")] = $album;
                    }
                    break;
                case 'featured_only' :
                    $featured = new CFeatured(FEATURED_ALBUMS);
                    $featuredAlbums = $featured->getItemIds();
                    foreach($featuredAlbums as $featuredAlbum){
                        if($album->id == $featuredAlbum){
                            $data[] = $album;
                        }
                    }
                    break;
                case 'featured' :
                    $data[] = $album;
                    break;
                /* removed in 4.0
                very heavy to filter
                case 'like' :
                    $like = new CLike();
                    $totalLikes = $like->getLikeCount('album', $album->id);
                    $key = $this->getNextUniqueAlbumKey($totalLikes,$data);
                    $data[$key] = $album;
                    break;
                case 'comment';
                    $totalComments = CWallLibrary::getWallCount('albums',$album->id);
                    $key = $this->getNextUniqueAlbumKey($totalComments,$data);
                    $data[$key] = $album;
                    break;
                */
                default : //date
                    $key = $this->getNextUniqueAlbumKey($lastupdated->format('U'),$data);
                    $data[$key] = $album;
            }
        }
        //sort by the key
        if($sort != 'featured'){
            krsort($data);
        }


        if($sort == 'name' || $sort == 'hit'){
            $data = array_reverse($data);
        }

        return $data;
    }

    /**
     * get the next key for the series of data
     * @todo Use usort to sort the data when PHP 5.3 is the minimal requirement
     * @param $key
     * @param $data
     * @return mixed
     */
    private function getNextUniqueAlbumKey($key, $data){
        if(array_key_exists($key, $data)){
            return $this->getNextUniqueAlbumKey(++$key,$data);
        }
        return $key;
    }

    /*
     * Since 2.4
     * Currently used for Single Photo Album view's other albums only
     */

    public function _getOnlyAlbums($id, $type, $limitstart = "", $limit = "") {
        $db = $this->getDBO();
        $extraSQL = ' WHERE a.type = ' . $db->Quote($type);


        $extraSQL .= ' AND a.creator=' . $db->Quote($id) . ' ';
        // privacy

        $permission = CPrivacy::getAccessLevel(null, $id);
        $extraSQL .= ' AND a.permissions <=' . $db->Quote($permission) . ' ';



        // Get limit
        $limit = ($limit !== '') ? $limit : '';
        $limitstart = ($limitstart !== '') ? $limitstart : '';

        // Get total albums
        $total = $this->getAlbumCount($extraSQL);
        $this->total = $total;

        $extraSQL .= ' AND b.published =' . $db->Quote(1) . ' ';

        $result = ($limit === '' || $limitstart === '') ? $this->getAlbumPhotoCount($extraSQL) : $this->getAlbumPhotoCount($extraSQL, $limit, $limitstart);

        /* filter results, album that has photos + all unpublished = not to be displayed
         * 				   album that has no photos = display
         */
        foreach ($result as $key => $res) {
            if ($res->count <= 0) {
                unset($result[$key]);
            }
        }

        // Update their correct Thumbnails
        $this->_updateThumbnail($result);
        $data = array();
        foreach ($result as $row) {
            $album = JTable::getInstance('Album', 'CTable');
            $album->bind($row);
            $data[] = $album;
        }
        return $data;
    }

    /*
     * Since 2.6
     * Currently used for Single Photo Album view's other group albums only
     */

    public function _getOnlyGroupAlbums($id, $groupid, $type, $limitstart = "", $limit = "") {
        $db = $this->getDBO();

        $extraSQL = ' WHERE a.groupid=' . $db->Quote($groupid) . ' ';
        // privacy

        $permission = CPrivacy::getAccessLevel(null, $id);
        $extraSQL .= ' AND a.permissions <=' . $db->Quote($permission) . ' ';



        // Get limit
        $limit = ($limit !== '') ? $limit : '';
        $limitstart = ($limitstart !== '') ? $limitstart : '';

        // Get total albums
        $total = $this->getAlbumCount($extraSQL);
        $this->total = $total;

        $extraSQL .= ' AND b.published =' . $db->Quote(1) . ' ';
        $result = ($limit === '' || $limitstart === '') ? $this->getAlbumPhotoCount($extraSQL) : $this->getAlbumPhotoCount($extraSQL, $limit, $limitstart);

        /* filter results, album that has photos + all unpublished = not to be displayed
         * 				   album that has no photos = display
         */
        foreach ($result as $key => $res) {
            if ($res->count <= 0) {
                unset($result[$key]);
            }
        }

        // Update their correct Thumbnails
        $this->_updateThumbnail($result);

        $data = array();
        foreach ($result as $row) {
            $album = JTable::getInstance('Album', 'CTable');
            $album->bind($row);
            $data[] = $album;
        }
        return $data;
    }

    /*
     * Return true is the user is the owner/creator of the photo
     */

    public function isCreator($photoId, $userId) {
        // Guest has no album
        if ($userId == 0)
            return false;

        $db = $this->getDBO();

        $strSQL = 'SELECT COUNT(*) FROM ' . $db->quoteName('#__community_photos') . ' '
                . 'WHERE ' . $db->quoteName('id') . '=' . $db->Quote($photoId) . ' '
                . 'AND creator=' . $db->Quote($userId);

        $db->setQuery($strSQL);
        $result = $db->loadResult();

        return $result;
    }

    /**
     * Return CPhoto object
     */
    public function getPhoto($id) {
        $photo = JTable::getInstance('Photo', 'CTable');
        $photo->load($id);

        return $photo;
    }

    /**
     * Get the count of the photos from specific user or groups.
     * @param id user or group id
     * */
    public function getPhotosCount($id, $photoType = PHOTOS_USER_TYPE) {
        $db = $this->getDBO();

        $query = 'SELECT COUNT(1) FROM '
                . $db->quoteName('#__community_photos') . ' AS a '
                . 'INNER JOIN ' . $db->quoteName('#__community_photos_albums') . ' AS b '
                . 'ON a.albumid=b.id '
                . 'WHERE 1 ';


        if ($photoType == PHOTOS_GROUP_TYPE) {
            $query .= ' AND b.groupid=' . $db->Quote($id);
            $query .= 'AND b.type LIKE ' . $db->Quote($photoType.'%');
        }elseif($photoType == PHOTOS_EVENT_TYPE){
            $query .= ' AND b.eventid=' . $db->Quote($id);
            $query .= 'AND b.type LIKE ' . $db->Quote($photoType.'%');
        } else {
            $query .= ' AND a.creator=' . $db->Quote($id);
            $query .= 'AND (b.type LIKE ' . $db->Quote($photoType.'%').' or b.type LIKE '.$db->quote('profile.%').')';
        }
        $query .= ' AND '.$db->quoteName('albumid').'<>0';


        $db->setQuery($query);
        $count = $db->loadResult();

        return $count;
    }

    public function getDefaultImage($albumId) {
        $db = $this->getDBO();

        $strSQL = 'SELECT b.* FROM ' . $db->quoteName('#__community_photos_albums') . ' AS a '
                . 'INNER JOIN ' . $db->quoteName('#__community_photos') . 'AS b '
                . 'WHERE a.id=' . $db->Quote($albumId) . ' '
                . 'AND a.photoid=b.id';

        //echo $strSQL;
        $db->setQuery($strSQL);
        $result = $db->loadObject();

        return $result;
    }

    /**
     *
     * Set the $photoId as the album cover of the album
     *
     * @param type $albumId
     * @param type $photoId
     * @return CommunityModelPhotos
     */
    public function setDefaultImage($albumId, $photoId) {
        $album = JTable::getInstance('Album', 'CTable');
        $album->load($albumId);

        $photo = JTable::getInstance('Photo', 'CTable');
        $photo->load($photoId);

        $thumbUri = str_replace(JURI::root(),'',$photo->getThumbURI());
        $album->setParam('thumbnail', $thumbUri);
        $album->setParam('thumbnail_id', $photoId);
        $album->photoid = $photoId;

        $album->store();

        return $this;
    }

    public function setOrdering($photos, $albumId) {
        $db = $this->getDBO();

        foreach ($photos as $id => $order) {
            $query = 'UPDATE ' . $db->quoteName('#__community_photos') . ' '
                    . 'SET ' . $db->quoteName('ordering') . '=' . $db->Quote($order) . ' '
                    . 'WHERE ' . $db->quoteName('id') . '=' . $db->Quote($id) . ' '
                    . 'AND ' . $db->quoteName('albumid') . '=' . $db->Quote($albumId);

            $db->setQuery($query);
            try {
                $db->execute();
            } catch (Exception $e) {
                JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
            }

        }

        return $this;
    }

    /**
     * Return true if the given id is a group photo
     * @param type $photoId
     * @return type
     */
    public function isGroupPhoto($photoId) {
        $db = $this->getDBO();

        $query = 'SELECT b.'.$db->quoteName('type').' FROM '.$db->quoteName('#__community_photos').' AS a';
        $query .= ' INNER JOIN '.$db->quoteName('#__community_photos_albums').' AS b';
        $query .= ' ON a.'.$db->quoteName('albumid').' = b.'.$db->quoteName('id');
        $query .= ' WHERE a.'.$db->quoteName('id').' = ' . $db->Quote($photoId);

        $db->setQuery($query);
        $type = $db->loadResult();

        return ($type == PHOTOS_GROUP_TYPE);
    }

    public function getPhotoGroupId($photoId) {
        $db = $this->getDBO();

        $query = 'SELECT b.'.$db->quoteName('groupid').' FROM '.$db->quoteName('#__community_photos').' AS a';
        $query .= ' INNER JOIN '.$db->quoteName('#__community_photos_albums').' AS b';
        $query .= ' ON a.'.$db->quoteName('albumid').' = b.'.$db->quoteName('id');
        $query .= ' WHERE a.'.$db->quoteName('id').' = ' . $db->Quote($photoId);
        $query .= ' AND b.'.$db->quoteName('type').' = ' . $db->Quote(PHOTOS_GROUP_TYPE);

        $db->setQuery($query);
        $type = $db->loadResult();

        return $type;
    }

    /**
     * Retrieve popular photos from the site.
     *
     * @param
     * @return
     * */
    public function getPopularPhotos($limit = 20, $permission = null) {
        $db = $this->getDBO();

        $query = 'SELECT * FROM #__community_photos '
                . 'WHERE ' . $db->quoteName('published') . '=' . $db->Quote(1);

        // Only apply the permission if explicitly specified
        if (!is_null($permission)) {
            $query .= ' AND' . $db->quoteName('permissions') . '=' . $db->Quote($permission);
        }

        $query .= ' ORDER BY ' . $db->quoteName('hits') . ' DESC '
                . 'LIMIT 0,' . $limit;

        $db->setQuery($query);
        $rows = $db->loadObjectList();
        $result = array();

        foreach ($rows as $row) {
            $photo = JTable::getInstance('Photo', 'CTable');
            $photo->bind($row);
            $result[] = $photo;
        }

        return $result;
    }

    public function getInviteListByName($namePrefix, $userid, $cid, $limitstart = 0, $limit = 8) {
        $db = $this->getDBO();
        $my = CFactory::getUser();
        $andName = '';
        $config = CFactory::getConfig();
        $nameField = $config->getString('displayname');
        if (!empty($namePrefix)) {
            $andName = ' AND b.' . $db->quoteName($nameField) . ' LIKE ' . $db->Quote('%' . $namePrefix . '%');
        }

        //we will treat differently for member's photo and group's photo
        $photo = JTable::getInstance('Photo', 'CTable');
        $photo->load($cid);
        $album = JTable::getInstance('Album', 'CTable');
        $album->load($photo->albumid);
        if ($album->groupid) {
            $countQuery = 'SELECT COUNT(DISTINCT(a.' . $db->quoteName('memberid') . '))  FROM ' . $db->quoteName('#__community_groups_members') . ' AS a ';
            $listQuery = 'SELECT DISTINCT(a.' . $db->quoteName('memberid') . ') AS id  FROM ' . $db->quoteName('#__community_groups_members') . ' AS a ';
            $joinQuery = ' INNER JOIN ' . $db->quoteName('#__users') . ' AS b '
                    . ' ON a.' . $db->quoteName('memberid') . '=b.' . $db->quoteName('id')
                    . ' AND a.' . $db->quoteName('approved') . '=' . $db->Quote(1)
                    . ' AND a.' . $db->quoteName('memberid') . '!=' . $db->Quote($my->id)
                    . ' AND a.' . $db->quoteName('groupid') . '=' . $db->Quote($album->groupid)
                    . ' WHERE NOT EXISTS (SELECT e.' . $db->quoteName('userid') . ' as id'
                    . ' FROM ' . $db->quoteName('#__community_photos_tag') . ' AS e  '
                    . ' WHERE e.' . $db->quoteName('photoid') . ' = ' . $db->Quote($cid)
                    . ' AND e.' . $db->quoteName('userid') . ' = a.' . $db->quoteName('memberid')
                    . ')';
        } else {
            $countQuery = 'SELECT COUNT(DISTINCT(a.' . $db->quoteName('connect_to') . ')) FROM ' . $db->quoteName('#__community_connection') . ' AS a ';
            $listQuery = 'SELECT DISTINCT(a.' . $db->quoteName('connect_to') . ') AS id  FROM ' . $db->quoteName('#__community_connection') . ' AS a ';
            $joinQuery = ' INNER JOIN ' . $db->quoteName('#__users') . ' AS b '
                    . ' ON a.' . $db->quoteName('connect_from') . '=' . $db->Quote($userid)
                    . ' AND a.' . $db->quoteName('connect_to') . '=b.' . $db->quoteName('id')
                    . ' AND a.' . $db->quoteName('status') . '=' . $db->Quote('1')
                    . ' AND b.' . $db->quoteName('block') . '=' . $db->Quote('0')
                    . ' WHERE NOT EXISTS ( SELECT d.' . $db->quoteName('blocked_userid') . ' as id'
                    . ' FROM ' . $db->quoteName('#__community_blocklist') . ' AS d  '
                    . ' WHERE d.' . $db->quoteName('userid') . ' = ' . $db->Quote($userid)
                    . ' AND d.' . $db->quoteName('blocked_userid') . ' = a.' . $db->quoteName('connect_to') . ')'
                    . ' AND NOT EXISTS (SELECT e.' . $db->quoteName('userid') . ' as id'
                    . ' FROM ' . $db->quoteName('#__community_photos_tag') . ' AS e  '
                    . ' WHERE e.' . $db->quoteName('photoid') . ' = ' . $db->Quote($cid)
                    . ' AND e.' . $db->quoteName('userid') . ' = a.' . $db->quoteName('connect_to')
                    . ')';
        }
        $query = $listQuery . $joinQuery . $andName
                . ' ORDER BY b.' . $db->quoteName($nameField)
                . ' LIMIT ' . $limitstart . ',' . $limit;
        $db->setQuery($query);
        $friends = $db->loadColumn();

        //calculate total
        $query = $countQuery . $joinQuery . $andName;
        $db->setQuery($query);
        $this->total = $db->loadResult();
        //friend yourself
        if ($my->id) {
            if ($namePrefix === '') {
                $found = false;
            } else {
                $found = JString::strpos($my->getDisplayName(), $namePrefix);
            }
            if ($namePrefix == '' || $found || $found === 0) {
                array_unshift($friends, $my->id);
                $this->total = $this->total + 1;
            }
        }
        return $friends;
    }

    /**
     * Return total photos for the day for the specific user.
     *
     * @param	string	$userId	The specific userid.
     * */
    function getTotalToday($userId) {
        $db = JFactory::getDBO();
        $date = JDate::getInstance();

        $query = 'SELECT COUNT(*) FROM #__community_photos AS a WHERE '
                . $db->quoteName('creator') . '=' . $db->Quote($userId) . ' '
                . 'AND TO_DAYS(' . $db->Quote($date->toSql(true)) . ') - TO_DAYS( DATE_ADD( a.'.$db->quoteName('created').' , INTERVAL ' . $date->getOffset() . ' HOUR ) ) = 0 '
                . ' AND a.status <> '.$db->Quote('temp');

        $db->setQuery($query);
        return $db->loadResult();
    }

    private function _updateThumbnail(&$photos) {
        if (!empty($photos)) {
            foreach ($photos as &$row) {
                $photo = JTable::getInstance('Photo', 'CTable');
                $photo->bind($row);
                $photo->id = $row->photoid; // the id was photo_album id, need to fix it
                $row->thumbnail = $photo->getThumbURI();
            }
        }
    }

    function getPhotoList($data) {

        $db = JFactory::getDBO();

        switch ($data['type']) {
            case 1:
                $extraSQL = 'WHERE ' . $db->quoteName('creator') . '=' . $db->Quote($data['id']) . ' LIMIT ' . $data['start'] . ' ,' . $data['no'];
                break;
            case 2:
                $extraSQL = 'WHERE ' . $db->quoteName('albumid') . '=' . $db->Quote($data['id']) . ' LIMIT ' . $data['start'] . ' ,' . $data['no'];
                break;
            case 3:
                $extraSQL = 'LIMIT 0,' . $data['no'];
                break;
        }
        $query = 'SELECT * FROM #__community_photos ' . $extraSQL;

        $db->setQuery($query);
        $result = $db->loadObjectList();
        $data = array();

        foreach ($result as $row) {
            $album = JTable::getInstance('Album', 'CTable');
            $album->bind($row);
            $data[] = $album;
        }
        return $data;
    }

    /**
     * Method to get Album Photo Count
     * @param $albumId -  Album id
     * @return photo count for specific album
     * */
    public function getAlbumPhotosCount($albumId) {
        $db = $this->getDBO();

        $query = "SELECT COUNT(1) FROM "
            .$db->quoteName('#__community_photos')." AS a "
            ." INNER JOIN ". $db->quoteName('#__community_photos_albums') . " AS b "
            ." ON a.albumid=b.id "
            ." WHERE b.".$db->quoteName('id')."=".$db->quote($albumId)
            ." AND a.".$db->quoteName('status')."<>".$db->quote('temp')
            ." AND a.".$db->quoteName('published')."=".$db->quote('1');

        $db->setQuery($query);
        $count = $db->loadResult();

        return $count;
    }

    public function getUserAllAlbums($userId) {
        $db = $this->getDBO();

        $query = 'SELECT * FROM '
                . $db->quoteName('#__community_photos_albums')
                . 'WHERE ' . $db->quoteName('creator') . ' = ' . $db->Quote($userId);

        $db->setQuery($query);
        $result = $db->loadObjectList();

        $data = array();
        foreach ($result as $row) {
            $album = JTable::getInstance('Album', 'CTable');
            $album->bind($row);
            $album->thumbnail = $album->getCoverThumbPath();
            $data[] = $album;
        }

        return $data;
    }

    /**
     * Update album name based on album id
     * @param $albumid
     * @param $name
     * @return $this
     */
    public function updateAlbumName($albumid, $name) {
        $db = $this->getDBO();
        $query = 'UPDATE #__community_photos_albums SET '.$db->quoteName('name').'=' . $db->Quote($name) . 'WHERE '.$db->quoteName('id').'=' . $db->Quote($albumid);
        $db->setQuery($query);
        try {
            $db->execute();
        } catch (Exception $e) {
            JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
            return false;
        }

        return true;
    }

    /**
     * Update album permission
     * @param $albumid
     * @param int $permission default public
     * @return bool
     */
    public function updateAlbumPermission($albumid, $permission = COMMUNITY_STATUS_PRIVACY_PUBLIC){
        $db = $this->getDBO();
        $query = 'UPDATE #__community_photos_albums SET '.$db->quoteName('permissions').'=' . $db->Quote($permission) . ' WHERE '.$db->quoteName('id').'=' . $db->Quote($albumid);
        $db->setQuery($query);
        try {
            $db->execute();
        } catch (Exception $e) {
            JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
            return false;
        }

        return true;
    }
}
