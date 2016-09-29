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

require_once( JPATH_ROOT . '/components/com_community/models/models.php' );

class CommunityModelUser extends JCCModel {

    var $_data = null;
    var $_userpref = array();

    /**
     * Returns the total number of users registered for specific months.
     * @param int	$month	Month in integer
     * @return int  Total number of users.
     *
     * */
    public function getTotalRegisteredByMonth($YearMonth) {
        $db = $this->getDBO();
        $start = $YearMonth . '-01';
        $end = $YearMonth . '-31';

        $query = 'SELECT COUNT(1) FROM ' . $db->quoteName('#__users')
                . ' WHERE ' . $db->quoteName('registerDate') . ' >= ' . $db->Quote($start)
                . ' AND ' . $db->quoteName('registerDate') . ' <= ' . $db->Quote($end);
        $db->setQuery($query);
        $total = $db->loadResult();

        return $total;
    }

    /**
     * Returns the users registered for specific months.
     * @param int	$month	Month in integer
     * @return int  Total number of users.
     *
     * */
    public function getUserRegisteredByMonth($YearMonth) {
        $db = $this->getDBO();
        $start = $YearMonth . '-01';
        $end = $YearMonth . '-31';

        $query = 'SELECT id FROM ' . $db->quoteName('#__users')
                . ' WHERE ' . $db->quoteName('registerDate') . ' >= ' . $db->Quote($start)
                . ' AND ' . $db->quoteName('registerDate') . ' <= ' . $db->Quote($end)
                . ' AND ' . $db->quoteName('block') . ' = ' . $db->Quote(0);

        $db->setQuery($query);
        $userIds = $db->loadColumn();

        CFactory::loadUsers($userIds);

        $users = array();
        foreach ($userIds as $id) {
            $users[] = CFactory::getUser($id);
        }

        return $users;
    }

    /**
     * Return the username given its userid
     * @param int	userid
     */
    public function getUsername($id) {
        $db = $this->getDBO();
        $sql = 'SELECT ' . $db->quoteName('username') . ' FROM ' . $db->quoteName('#__users')
                . ' WHERE ' . $db->quoteName('id') . '=' . $db->Quote($id);
        $db->setQuery($sql);

        try {
            $result = $db->loadResult();
        } catch (Exception $e) {
            JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
        }

        return $result;
    }

    /**
     * Return the user fullname given its userid
     * @param int	userid
     */
    public function getUserFullname($id) {
        $db = $this->getDBO();
        $sql = 'SELECT ' . $db->quoteName('name') . ' FROM ' . $db->quoteName('#__users')
                . ' WHERE ' . $db->quoteName('id') . '=' . $db->Quote($id);
        $db->setQuery($sql);

        try {
            $result = $db->loadResult();
        } catch (Exception $e) {
            JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
        }

        return $result;
    }

    /**
     * Return the userid given its name
     */
    public function getUserId($username, $useRealName = false) {
        $db = $this->getDBO();

        $param = 'username';

        if ($useRealName)
            $param = 'name';

        $sql = 'SELECT ' . $db->quoteName('id') . ' FROM ' . $db->quoteName('#__users')
                . ' WHERE ' . $db->quoteName($param) . '=' . $db->Quote($username);

        $db->setQuery($sql);
        try {
            $result = $db->loadResult();
        } catch (Exception $e) {
            JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
        }

        return $result;
    }

    /**
     * Return the user's email given its id
     */
    public function getUserEmail($id) {
        $db = $this->getDBO();

        $query = 'SELECT ' . $db->quoteName('email') . ' FROM ' . $db->quoteName('#__users')
                . ' WHERE ' . $db->quoteName('id') . '=' . $db->Quote($id);
        $db->setQuery($query);
        try {
            $result = $db->loadResult();
        } catch (Exception $e) {
            JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
        }
        return $result;
    }

    public function getMembersCount() {
        static $members ;

        if ( !isset($members)) {

            $db = $this->getDBO();

            $query = 'SELECT COUNT(*) FROM ' . $db->quoteName('#__users')
                    . ' WHERE ' . $db->quoteName('block') . '=' . $db->Quote(0);

            $db->setQuery($query);

            try {
                $members = $db->loadResult();
            } catch (Exception $e) {
                JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
            }
        }

        return $members;
    }


    /**
     * @param int $limit
     * get members and order by featured first
     */
    public function getFeaturedMember($limit = 15, $profileType = 0){
        if ($limit == 0)
            return array();
        $limit = ($limit < 0) ? 0 : $limit;
        $db = $this->getDBO();

        $featured = new CFeatured(FEATURED_USERS);
        $featuredUsers = implode(',',$featured->getItemIds());

        $order = ' ORDER BY '.$db->quoteName('id');

        if(!$featuredUsers || count($featuredUsers) == 0){
            return array();
        }

        $filterquery = '';
        $config = CFactory::getConfig();
        if (!$config->get('privacy_show_admins')) {
            $userModel = CFactory::getModel('User');
            $tmpAdmins = $userModel->getSuperAdmins();

            $admins = array();

            $filterquery .= ' AND ' . $db->quoteName('id') . ' NOT IN(';
            for ($i = 0; $i < count($tmpAdmins); $i++) {
                $admin = $tmpAdmins[$i];
                $filterquery .= $db->Quote($admin->id);
                $filterquery .= $i < count($tmpAdmins) - 1 ? ',' : '';
            }
            $filterquery .= ')';
        }

        //add in profile filter if needed
        $joinProfileTypeQuery = '';
        if($profileType){
            $joinProfileTypeQuery = ' INNER JOIN '.$db->quoteName('#__community_users').
                ' AS cu ON '.$db->quoteName('#__users').'.id = cu.userid';

            $filterquery .= ' AND cu.profile_id='.$profileType;
        }

        $query = 'SELECT * FROM ' . $db->quoteName('#__users') . ' '
            .$joinProfileTypeQuery
            . ' WHERE ' . $db->quoteName('block') . '=' . $db->Quote(0) . ' '
            . $filterquery
            . ' AND id IN ('.$featuredUsers.') '
            . ' LIMIT ' . $limit;

        $db->setQuery($query);

        try {
            $result = $db->loadObjectList();
        } catch (Exception $e) {
            JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
        }

        $latestMembers = array();

        $uids = array();
        foreach ($result as $m) {
            $uids[] = $m->id;
        }
        CFactory::loadUsers($uids);

        foreach ($result as $row) {
            $latestMembers[] = CFactory::getUser($row->id);
        }
        return $latestMembers;

    }

    /**
     * @param int $limit
     * @param bool|false $sortOnline
     * @param int $profileType added in 4.1 to retrieve user with particular profile id
     * @return array
     */
    public function getLatestMember($limit = 15, $sortOnline = false, $profileType = 0) {
        if ($limit == 0)
            return array();
        $limit = ($limit < 0) ? 0 : $limit;

        $config = CFactory::getConfig();
        $db = $this->getDBO();

        $filterquery = '';
        $config = CFactory::getConfig();
        if (!$config->get('privacy_show_admins')) {
            $userModel = CFactory::getModel('User');
            $tmpAdmins = $userModel->getSuperAdmins();

            $admins = array();

            $filterquery .= ' AND ' . $db->quoteName('id') . ' NOT IN(';
            for ($i = 0; $i < count($tmpAdmins); $i++) {
                $admin = $tmpAdmins[$i];
                $filterquery .= $db->Quote($admin->id);
                $filterquery .= $i < count($tmpAdmins) - 1 ? ',' : '';
            }
            $filterquery .= ')';
        }

        //add in profile filter if needed
        $joinProfileTypeQuery = '';
        if($profileType){
            $joinProfileTypeQuery = ' INNER JOIN '.$db->quoteName('#__community_users').
                ' AS cu ON a.id = cu.userid';

            $filterquery .= ' AND cu.profile_id='.$profileType;
        }

        if($sortOnline){
            $query = 'SELECT DISTINCT u.userid as online, a.* FROM '
                .$db->quoteName('#__session'). ' as u'
                .' LEFT JOIN '.$db->quoteName('#__users').' AS a ON '
                .' a.id = u.userid '
                . $joinProfileTypeQuery
                .' WHERE ' . $db->quoteName('block'). ' = '.$db->quote(0)
                . $filterquery
                .' ORDER BY ' . $db->quoteName('online') . ' '
                .' DESC LIMIT ' . $limit;
        }else{
            $query = 'SELECT * FROM ' . $db->quoteName('#__users') . ' AS a '
                . $joinProfileTypeQuery
                . ' WHERE ' . $db->quoteName('block') . '=' . $db->Quote(0) . ' '
                . $filterquery
                . ' ORDER BY ' . $db->quoteName('registerDate') . ' '
                . ' DESC LIMIT ' . $limit;
        }

        $db->setQuery($query);

        try {
            $result = $db->loadObjectList();
        } catch (Exception $e) {
            JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
        }

        $latestMembers = array();

        $uids = array();
        foreach ($result as $m) {
            $uids[] = $m->id;
        }
        CFactory::loadUsers($uids);

        foreach ($result as $row) {
            $latestMembers[] = CFactory::getUser($row->id);
        }
        return $latestMembers;
    }

    public function getActiveMember($limit = 15) {
        $uid = array();
        $limit = (int) $limit;
        $uid_str = "";
        $db = $this->getDBO();

        $filterquery = '';
        $config = CFactory::getConfig();
        if (!$config->get('privacy_show_admins')) {
            $userModel = CFactory::getModel('User');
            $tmpAdmins = $userModel->getSuperAdmins();
            $admins = array();

            $filterquery .= ' AND b.' . $db->quoteName('id') . ' NOT IN(';
            for ($i = 0; $i < count($tmpAdmins); $i++) {
                $admin = $tmpAdmins[$i];
                $filterquery .= $db->Quote($admin->id);
                $filterquery .= $i < count($tmpAdmins) - 1 ? ',' : '';
            }
            $filterquery .= ')';
        }
        $query = " 	 SELECT
							b.*,
							a." . $db->quoteName('actor') . ",
							COUNT(a." . $db->quoteName('id') . ") AS " . $db->quoteName('count') . "
					   FROM
							" . $db->quoteName('#__community_activities') . " a
				 INNER JOIN	" . $db->quoteName('#__users') . " b
					  WHERE
							a." . $db->quoteName('app') . " != " . $db->quote('groups') . " AND
							b." . $db->quoteName('block') . " = " . $db->quote('0') . " AND
							a." . $db->quoteName('archived') . " = " . $db->quote('0') . " AND
							a." . $db->quoteName('actor') . " = b." . $db->quoteName('id') .
                $filterquery . "
				   GROUP BY a." . $db->quoteName('actor') . "
				   ORDER BY " . $db->quoteName('count') . " DESC
				   LIMIT " . $limit;
        $db->setQuery($query);
        try {
            $result = $db->loadObjectList();
        } catch (Exception $e) {
            JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
        }

        $latestMembers = array();

        foreach ($result as $row) {
            $latestMembers[] = CFactory::getUser($row->id);
        }
        return $latestMembers;
    }

    public function getPopularMember($limit = 15, $profileType = 0) {
        $uid = array();
        $uid_str = "";
        $db = $this->getDBO();

        $filterquery = '';
        $config = CFactory::getConfig();
        if (!$config->get('privacy_show_admins')) {
            $userModel = CFactory::getModel('User');
            $tmpAdmins = $userModel->getSuperAdmins();
            $admins = array();

            $filterquery .= ' AND b.' . $db->quoteName('id') . ' NOT IN(';
            for ($i = 0; $i < count($tmpAdmins); $i++) {
                $admin = $tmpAdmins[$i];
                $filterquery .= $db->Quote($admin->id);
                $filterquery .= $i < count($tmpAdmins) - 1 ? ',' : '';
            }
            $filterquery .= ')';
        }

        if($profileType){
            $filterquery .= ' AND a.profile_id='.$profileType;
        }

        $query = " 	 SELECT b.*
					   FROM
							" . $db->quoteName('#__community_users') . " a
				 INNER JOIN	" . $db->quoteName('#__users') . " b
					  WHERE
							b." . $db->quoteName('block') . " = " . $db->quote('0') . " AND
							a." . $db->quoteName('userid') . " = b." . $db->quoteName('id') .
                $filterquery . "
				   ORDER BY a." . $db->quoteName('view') . " DESC
				   LIMIT " . $limit;
        $db->setQuery($query);
        try {
            $result = $db->loadObjectList();
        } catch (Exception $e) {
            JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
        }

        $latestMembers = array();

        foreach ($result as $row) {
            $latestMembers[] = CFactory::getUser($row->id);
        }
        return $latestMembers;
    }

    // Return JDate object of last login date
    public function lastLogin($userid) {

    }

    /**
     * is the email exits
     */
    public function userExistsbyEmail($email) {
        $db = $this->getDBO();
        $sql = 'SELECT count(*) from ' . $db->quoteName('#__users')
                . ' WHERE ' . $db->quoteName('email') . '= ' . $db->Quote($email);

        $db->setQuery($sql);
        $result = $db->loadResult();
        return $result;
    }

    /**
     * Save user data.
     */
    public function updateUser($obj) {
        $db = $this->getDBO();
        return $db->updateObject('#__community_users', $obj, 'userid');
    }

    public function removeProfilePicture($id, $type = 'thumb') {
        $db = $this->getDBO();
        $type = JString::strtolower($type);

        // Test if the record exists.
        $query = 'SELECT ' . $db->quoteName($type) . ' FROM ' . $db->quoteName('#__community_users')
                . 'WHERE ' . $db->quoteName('userid') . '=' . $db->Quote($id);

        $db->setQuery($query);
        $oldFile = $db->loadResult();

        $query = 'UPDATE ' . $db->quoteName('#__community_users') . ' '
                . 'SET ' . $db->quoteName($type) . '=' . $db->Quote('') . ' '
                . 'WHERE ' . $db->quoteName('userid') . '=' . $db->Quote($id);

        $db->setQuery($query);
        try {
            $db->execute();
        } catch (Exception $e) {
            JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
        }

        // remove the stream activity too if there is any
        if($type == "avatar"){ // to avoid being called twice
            $user = CFactory::getUser($id);
            $avatarid = $user->_cparams->get('avatar_photo_id');
            $query = "SELECT ".$db->quoteName('id')." FROM ".$db->quoteName('#__community_activities'). " WHERE "
                . $db->quoteName('app') ." = " .$db->Quote('profile.avatar.upload') . " AND ".$db->quoteName('cid')
                ." = ".$db->Quote($avatarid);
            $db->setQuery($query);
            $streamid = $db->loadResult();

            if($streamid){
                $activity = JTable::getInstance("Activity","CTable");
                $activity->load($streamid);
                $activity->delete();

            }
        }

        // If old file is default_thumb or default, we should not remove it.
        // Need proper way to test it
        if (!JString::stristr($oldFile, 'components/com_community/assets/default.jpg') && !JString::stristr($oldFile, 'components/com_community/assets/default_thumb.jpg') && !JString::stristr($oldFile, 'avatar_')) {
            // File exists, try to remove old files first.
            $oldFile = CString::str_ireplace('/', '/', $oldFile);

            if (JFile::exists($oldFile)) {
                return JFile::delete($oldFile);
            } else {

            }
        }

        return true;
    }

    /**
     * 	Set the avatar for specific application. Caller must have a database table
     * 	that is named after the appType. E.g, users should have jos_community_users
     *
     * @param	appType		Application type. ( users , groups )
     * @param	path		The relative path to the avatars.
     * @param	type		The type of Image, thumb or avatar.
     *
     * */
    public function setImage($id, $path, $type = 'thumb', $removeOldImage = true) {
        CError::assert($id, '', '!empty', __FILE__, __LINE__);
        CError::assert($path, '', '!empty', __FILE__, __LINE__);

        $db = $this->getDBO();

        // Fix the back quotes
        $path = CString::str_ireplace('\\', '/', $path);
        $type = JString::strtolower($type);

        // Test if the record exists.
        $query = 'SELECT ' . $db->quoteName($type) . ' FROM ' . $db->quoteName('#__community_users')
                . 'WHERE ' . $db->quoteName('userid') . '=' . $db->Quote($id);

        $db->setQuery($query);
        try {
            $oldFile = $db->loadResult();
        } catch (Exception $e) {
            JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
        }
        $appsLib = CAppPlugins::getInstance();
        $appsLib->loadApplications();
        $args = array();
        $args[] = $id;   // userid
        $args[] = $oldFile; // old path
        $args[] = $path;  // new path
        $appsLib->triggerEvent('onProfileAvatarUpdate', $args);


        if (!$oldFile) {
            $query = 'UPDATE ' . $db->quoteName('#__community_users') . ' '
                    . 'SET ' . $db->quoteName($type) . '=' . $db->Quote($path) . ' '
                    . 'WHERE ' . $db->quoteName('userid') . '=' . $db->Quote($id);

            $db->setQuery($query);
            try {
                $db->execute();
            } catch (Exception $e) {
                JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
            }

        } else {
            $query = 'UPDATE ' . $db->quoteName('#__community_users') . ' '
                    . 'SET ' . $db->quoteName($type) . '=' . $db->Quote($path) . ' '
                    . 'WHERE ' . $db->quoteName('userid') . '=' . $db->Quote($id);

            $db->setQuery($query);
            try {
                $db->execute();
            } catch (Exception $e) {
                JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
            }

            // If old file is default_thumb or default, we should not remove it.
            // Need proper way to test it
            if (!JString::stristr($oldFile, 'components/com_community/assets/default.jpg') && !JString::stristr($oldFile, 'components/com_community/assets/default_thumb.jpg') && $removeOldImage && !JString::stristr($oldFile, 'avatar_')) {
                // File exists, try to remove old files first.
                $oldFile = CString::str_ireplace('/', '/', $oldFile);

                if (JFile::exists($oldFile)) {
                    JFile::delete($oldFile);
                }
            }
        }

        return $this;
    }

    /**
     * Return array of profile variables
     */
    public function getProfile() {

    }

    /**
     * Store user latitude/longitude data
     */
    public function storeLocation($userid, $lat = null, $long = null) {
        $db = $this->getDBO();
        $query = "UPDATE " . $db->quoteName('#__community_users')
                . " SET " . $db->quoteName('latitude') . "=" . $db->Quote($lat) . " , "
                . $db->quoteName('longitude') . "=" . $db->Quote($long)
                . " WHERE "
                . $db->quoteName('userid') . "=" . $db->Quote($userid);
        $db->setQuery($query);
        $db->execute();
    }

    public function getOnlineUsers($limit = 15, $backendUsers = false) {
        $db = $this->getDBO();


        $query = 'SELECT DISTINCT(a.' . $db->quoteName('id') . ')'
                . ' FROM ' . $db->quoteName('#__users') . ' AS a '
                . ' INNER JOIN ' . $db->quoteName('#__session') . ' AS b '
                . ' ON a.' . $db->quoteName('id') . '=b.' . $db->quoteName('userid')
                . ' INNER JOIN ' . $db->quoteName('#__community_users') . ' AS c '
                . ' ON a.' . $db->quoteName('id') . '=c.' . $db->quoteName('userid')
                . ' WHERE a.' . $db->quoteName('block') . '=' . $db->Quote('0') . ' '
                . ' AND c.' . $db->quoteName('params') .' NOT LIKE '.$db->Quote('%"showOnlineStatus":0%').' '; //do not show those user with this option

        if (!$backendUsers) {
            $query .= 'AND ' . $db->quoteName('client_id') . ' != ' . $db->Quote(1);
        }

        $query .= 'ORDER BY b.' . $db->quoteName('time') . ' DESC '
                . 'LIMIT ' . $limit;

        $db->setQuery($query);
        $result = $db->loadObjectList();

        return $result;
    }

    public function getSuperAdmins() {
        $db = $this->getDBO();

        $query = 'SELECT a.*'
                . ' FROM ' . $db->quoteName('#__users') . ' as a, '
                . $db->quoteName('#__user_usergroup_map') . ' as b'
                . ' WHERE a.' . $db->quoteName('id') . '= b.' . $db->quoteName('user_id')
                . ' AND b.' . $db->quoteName('group_id') . '=' . $db->Quote(8);
        $db->setQuery($query);
        $users = $db->loadObjectList();
        return $users;
    }

    /**
     * Return userid from the given email. If none is found, return 0
     */
    public function getUserFromEmail($email) {
        $db = $this->getDBO();
        $email = strtolower($email);

        $query = 'SELECT ' . $db->quoteName('id') . ' FROM ' . $db->quoteName('#__users')
                . ' WHERE LOWER( ' . $db->quoteName('email') . ' ) = ' . $db->Quote($email);

        $db->setQuery($query);
        $userid = $db->loadResult();

        return $userid;
    }

    /**
     * Get the list of users from the site.
     *
     * @return	Array	An array of CUser objects.
     * */
    public function getUsers() {
        $db = JFactory::getDBO();
        $query = 'SELECT ' . $db->quoteName('id') . ' FROM ' . $db->quoteName('#__users');

        $db->setQuery($query);
        $db->execute();

        $ids = $db->loadColumn();

        CFactory::loadUsers($ids);

        $users = array();
        foreach ($ids as $id) {
            $users[] = CFactory::getUser($id);
        }

        return $users;
    }

    /**
     * Return true if the user exist. Can't test using CFactory::getUser
     * since it will throw a user-error
     */
    public function exists($userid) {
        $db = JFactory::getDBO();
        $query = 'SELECT COUNT(*) FROM ' . $db->quoteName('#__users')
                . ' WHERE ' . $db->quoteName('id') . '=' . $db->Quote($userid);

        $db->setQuery($query);
        $total = $db->loadResult();

        return ($total > 0);
    }

    /**
     * Return true if update successful
     * since 2.4
     */
    public function setDefProfileToUser($profileid, $default = COMMUNITY_DEFAULT_PROFILE) {
        $db = JFactory::getDBO();
        $query = 'UPDATE ' . $db->quoteName('#__community_users')
                . ' SET ' . $db->quoteName('profile_id') . '=' . $db->Quote($default)
                . ' WHERE ' . $db->quoteName('profile_id') . '=' . $db->Quote($profileid);

        $db->setQuery($query);
        try {
            $db->execute();
        } catch (Exception $e) {
            JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
            return false;
        }

        return true;
    }

    public function removeProfileCover($id) {
        $db = $this->getDBO();

        // Test if the record exists.
        $query = 'SELECT ' . $db->quoteName('cover') . ' FROM ' . $db->quoteName('#__community_users')
                . 'WHERE ' . $db->quoteName('userid') . '=' . $db->Quote($id);

        $db->setQuery($query);
        $oldFile = $db->loadResult();

        if(JFile::exists($oldFile)){
            $query = "SELECT ".$db->quoteName('id'). " FROM ". $db->quoteName('#__community_photos')
                ." WHERE ".$db->quoteName('image')." = ".$db->quote($oldFile);

            $db->setQuery($query);
            $pid = $db->loadResult();
            if($pid){
                $photoTable = JTable::getInstance('Photo','CTable');
                $photoTable->load($pid);
                $photoTable->delete();

                CActivityStream::remove('cover.upload', $pid);
            }
        }

        $query = 'UPDATE ' . $db->quoteName('#__community_users') . ' '
                . 'SET ' . $db->quoteName('cover') . '=' . $db->Quote('') . ' '
                . 'WHERE ' . $db->quoteName('userid') . '=' . $db->Quote($id);

        $db->setQuery($query);
        try {
            $db->execute();
        } catch (Exception $e) {
            JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
        }

        return true;
    }

    public function getBannedUser(){
        $db = $this->getDBO();

        $query = 'SELECT '.$db->quoteName('id').' FROM '.$db->quoteName('#__users')
                .' WHERE '.$db->quoteName('block'). '='.$db->quote(1);

        $db->setQuery($query);
        $userIds = $db->loadColumn();

        return $userIds;
    }

    /**
     * since 4.0 used to save user points
     * @param $userId
     * @param $points
     * @return int
     */
    public function updatePoints($userId, $points){
        $db = JFactory::getDbo();
        $user	= CFactory::getUser($userId);
        $points	+= $user->getKarmaPoint();

        $query = "UPDATE ".$db->quoteName('#__community_users')
            ." SET ".$db->quoteName('points')."=".$db->quote($points)
            ." WHERE ". $db->quoteName('userid') . '=' . $db->Quote($userId);

        $db->setQuery($query);
        if($db->execute()){
            CFactory::getUser($userId)->_points = $points;
            return $points;
        };
    }

}