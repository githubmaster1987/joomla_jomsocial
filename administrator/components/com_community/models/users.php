<?php

/**
 * @copyright (C) 2013 iJoomla, Inc. - All rights reserved.
 * @license GNU General Public License, version 2 (http://www.gnu.org/licenses/gpl-2.0.html)
 * @author iJoomla.com <webmaster@ijoomla.com>
 * @url https://www.jomsocial.com/license-agreement
 * The PHP code portions are distributed under the GPL license. If not otherwise stated, all images, manuals, cascading style sheets, and included JavaScript *are NOT GPL, and are released under the IJOOMLA Proprietary Use License v1.0
 * More info at https://www.jomsocial.com/license-agreement
 */
// Disallow direct access to this file
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.model');
jimport('joomla.filesystem.file');
jimport('joomla.filesystem.folder');

class CommunityModelUsers extends JModelLegacy {

    /**
     * Configuration data
     *
     * @var object  JPagination object
     * */
    var $_pagination;

    /**
     * Constructor
     */
    public function __construct() {
        $mainframe = JFactory::getApplication();

        // Call the parents constructor
        parent::__construct();

        // Get the pagination request variables
        $limit = $mainframe->getUserStateFromRequest('com_community.users.list.limit', 'limit', $mainframe->get('list_limit'), 'int');
        $limitstart = $mainframe->getUserStateFromRequest('com_community.users.limitstart', 'limitstart', 0, 'int');

        // In case limit has been changed, adjust limitstart accordingly
        $limitstart = ($limit != 0 ? (floor($limitstart / $limit) * $limit) : 0);

        $this->setState('limit', $limit);
        $this->setState('limitstart', $limitstart);
    }

    /**
     *  Set the avatar for specific application. Caller must have a database table
     *  that is named after the appType. E.g, users should have jos_community_users
     *
     * @param   appType     Application type. ( users , groups )
     * @param   path        The relative path to the avatars.
     * @param   type        The type of Image, thumb or avatar.
     *
     * */
    public function setImage($id, $path, $type = 'thumb') {
        CError::assert($id, '', '!empty', __FILE__, __LINE__);
        CError::assert($path, '', '!empty', __FILE__, __LINE__);

        $db = & $this->getDBO();

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
            if (!Jstring::stristr($oldFile, 'components/com_community/assets/default.jpg') && !Jstring::stristr($oldFile, 'components/com_community/assets/default_thumb.jpg')) {
                // File exists, try to remove old files first.
                $oldFile = CString::str_ireplace('/', '/', $oldFile);
                JFile::delete($oldFile);
            }
        }
    }

    /**
     * Retrieves the JPagination object
     *
     * @return object   JPagination object
     * */
    public function &getPagination() {
        return $this->_pagination;
    }

    public function getTotal() {
        // Load total number of rows
        if (empty($this->_total)) {
            $this->_total = $this->_getListCount($this->_buildQuery());
        }

        return $this->_total;
    }

    public function _buildQuery() {
        $db = JFactory::getDBO();
        $jinput = JFactory::getApplication()->input;
        $category = $jinput->getInt('category', 0);
        $condition = '';

        if ($category != 0) {
            $condition = ' WHERE a.categoryid=' . $db->Quote($category);
        }

        $query = 'SELECT * FROM ' . $db->quoteName('#__users') . ' '
                . 'WHERE ' . $db->quoteName('block') . '=' . $db->Quote('0') . ' '
                . 'ORDER BY ' . $db->quoteName('name');

        return $query;
    }

    /**
     *
     * @param type $useLimit
     * @param type $useSearch
     * @return type
     */
    public function getAllUsers($useLimit = true, $useSearch = true) {
        if (empty($this->_data)) {
            $db        = JFactory::getDBO();
            $session = JFactory::getSession();
            $mainframe = JFactory::getApplication();
            $jinput = JFactory::getApplication()->input;

            $status    = $jinput->getInt('status', $session->get('user_status_filter', 2));
            $usertype  = $jinput->getString('usertype', $session->get('user_type_filter', 'all'));
            $useSearch = $jinput->getString('usesearch',true);

            $session->set('user_status_filter', $status);
            $session->set('user_type_filter', $usertype);

            $limit = $this->getState('limit');
            $limitstart = $this->getState('limitstart');
            $search = $mainframe->getUserStateFromRequest("com_community.users.search", 'search', '', 'string');
            // $usertype        = $mainframe->getUserStateFromRequest( "com_community.users.usertype", 'usertype', 'joomla', 'string' );
            $profileType = $mainframe->getUserStateFromRequest("com_community.users.usertype", 'profiletype', $session->get('user_profile_filter', ''), 'int');
            /* by default order by registerDate */
            $ordering = $mainframe->getUserStateFromRequest("com_community.users.filter_order", 'filter_order', 'registerDate', 'cmd');
            $orderDirection = $mainframe->getUserStateFromRequest("com_community.users.filter_order_Dir", 'filter_order_Dir', 'DESC', 'word');

            $session->set('user_profile_filter', $usertype);

            $searchQuery = '';
            $joinQuery = '';
            $orderby = 'ORDER BY ' . $ordering . ' ' . $orderDirection;

            if (!empty($search) && $useSearch) {
                $searchQuery = 'WHERE (name LIKE ' . $db->Quote('%' . $search . '%') . ' '
                        . 'OR username LIKE ' . $db->Quote('%' . $search . '%').' ) ';
            }

            switch ($usertype) {
                case 'jomsocial':
                    $joinQuery = ' INNER JOIN ' . $db->quoteName('#__community_users') . ' AS b '
                            . ' ON a.' . $db->quoteName('id') . ' = b.' . $db->quoteName('userid');
                    break;
                case 'facebook':
                    $joinQuery = 'INNER JOIN ' . $db->quoteName('#__community_connect_users') . ' AS u '
                            . 'ON a.id=u.userid LEFT JOIN '
                            . $db->quoteName('#__community_users') .' AS b ON a.id=b.userid ';
                    break;
                default:
                    $joinQuery = 'LEFT JOIN ' . $db->quoteName('#__community_users') . ' AS b '
                            . 'ON a.id = b.userid ';
                    break;
            }

            if (!empty($profileType)) {
                $joinQuery .= 'INNER JOIN ' . $db->quoteName('#__community_users') . ' AS c '
                        . 'ON a.id = c.userid ';

                if (!empty($search)) {
                    $searchQuery .= ' AND c.profile_id=' . $db->Quote($profileType) . ' ';
                } else {
                    $searchQuery .= 'WHERE c.profile_id=' . $db->Quote($profileType) . ' ';
                }
            }

            if ($status != 2) {
                $statusBlocked = $status==3 || $status==1?1:$status;
                if (!empty($searchQuery)) {
                    $searchQuery .= ' AND a.block=' . $db->Quote($statusBlocked) . ' ';
                } else {
                    $searchQuery .= 'WHERE a.block=' . $db->Quote($statusBlocked) . ' ';
                }

                // blocked user $status=3, pending user status=1
                if($status==1){
                    $searchQuery .= ' AND a.lastvisitDate = '. $db->Quote('0000-00-00 00:00:00') . ' ';
                }elseif($status==3){
                    $searchQuery .= ' AND a.lastvisitDate != '. $db->Quote('0000-00-00 00:00:00') . ' ';
                }
            }

            if($usertype == 'facebook'){
                //filter by facebook as well
                $searchQuery .= ' AND (u.type=' . $db->Quote('facebook') . ') ';
            }elseif ($usertype == 'jomsocial') {
                $searchQuery .='AND a.'.$db->quoteName('id').' NOT IN(  SELECT '.$db->quoteName('userid').' FROM '. $db->quoteName('#__community_connect_users').' )';
            }

            $query = 'SELECT *, b.points FROM ' . $db->quoteName('#__users') . ' AS a '
                    . $joinQuery
                    . $searchQuery
                    . 'GROUP BY a.id '
                    . $orderby;

            if ($useLimit) {
                // Appy pagination
                if (empty($this->_pagination)) {
                    jimport('joomla.html.pagination');
                    $this->_pagination = new JPagination($this->_getListCount($query), $limitstart, $limit);
                }

                $this->_data = $this->_getList($query, $this->getState('limitstart'), $this->getState('limit'));
            } else {
                $db->setQuery($query);
                $this->_data = $db->loadObjectList();
            }
        }
        return $this->_data;
    }

    public function getUsers() {
        if (empty($this->_data)) {

            $query = $this->_buildQuery();

            $this->_data = $this->_getList($this->_buildQuery(), $this->getState('limitstart'), $this->getState('limit'));
        }
        return $this->_data;
    }

    public function getCommunityUser() {
        $db = JFactory::getDBO();

        $query = "SELECT * FROM " . $db->quoteName('#__community_users');

        $db->setQuery($query);
        $result = $db->loadObjectList();

        return $result;
    }

    public function getAllCommunityUsers() {
        $db = JFactory::getDBO();

        $query = "SELECT ".$db->quoteName('userid')." FROM " . $db->quoteName('#__community_users');

        $db->setQuery($query);
        $result = $db->loadObjectList();

        return $result;
    }

    /**
     * Method to retrieve all user's id from the site.
     */
    public function getSiteUsers($limitstart, $limit) {
        $db = JFactory::getDBO();

        $query = 'SELECT id FROM ' . $db->quoteName('#__users') . ' '
                . 'WHERE ' . $db->quoteName('block') . ' = ' . $db->quote(0)
                . ' LIMIT ' . $limitstart . ',' . $limit;

        $db->setQuery($query);

        $result = $db->loadResult();

        return $result;
    }

    public function isLatestTable() {
        $fields = $this->_getFields();

        if (!array_key_exists('friendcount', $fields)) {
            return false;
        }

        return true;
    }

    public function _getFields($table = '#__community_users') {
        $result = array();
        $db = JFactory::getDBO();

        $query = 'SHOW FIELDS FROM ' . $db->quoteName($table);

        $db->setQuery($query);

        $fields = $db->loadObjectList();

        foreach ($fields as $field) {
            $result[$field->Field] = preg_replace('/[(0-9)]/', '', $field->Type);
        }

        return $result;
    }

    /**
     *  Return connect type of specific user
     * */
    public function getUserConnectType($userId) {
        $db = JFactory::getDBO();

        $query = 'SELECT '.$db->quoteName('type').' FROM ' . $db->quoteName('#__community_connect_users') . ' '
                . 'WHERE ' . $db->quoteName('userid') . '=' . $db->quote($userId);

        $db->setQuery($query);

        $type = $db->loadResult();

        if (!$type) {
            $type = 'joomla';
        }

        return $type;
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

        // If old file is default_thumb or default, we should not remove it.
        // Need proper way to test it
        if (!JString::stristr($oldFile, 'components/com_community/assets/default.jpg') && !JString::stristr($oldFile, 'components/com_community/assets/default_thumb.jpg') && !JString::stristr($oldFile, 'avatar_')) {
            // File exists, try to remove old files first.
            $oldFile = CString::str_ireplace('/', '/', $oldFile);

            if (JFile::exists($oldFile)) {
                JFile::delete($oldFile);
            }
        }

        return true;
    }

    public function getMembersCount($type = 'all') {
        $db = $this->getDBO();

        switch ($type) {
            case 'jomsocial':
                $query = 'SELECT COUNT(*) FROM ' . $db->quoteName('#__users') . ' as a'
                        . ' INNER JOIN ' . $db->quoteName('#__community_users') . ' AS b '
                        . ' ON a.' . $db->quoteName('id') . ' = b.' . $db->quoteName('userid')
                        . ' AND ' . $db->quoteName('block') . '=' . $db->Quote(0)
                        . ' AND b.points > ' . $db->Quote(0);
                break;
            case 'all':
            default:
                $query = 'SELECT COUNT(*) FROM ' . $db->quoteName('#__users')
                        . ' WHERE ' . $db->quoteName('block') . '=' . $db->Quote(0);
        }

        $db->setQuery($query);

        try {
            $result = $db->loadResult();
        } catch (Exception $e) {
            JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
        }

        return $result;
    }

    public function getGenderInfo() {
        $db = $this->getDBO();

        $sql = "SELECT ".$db->quoteName('id')." FROM
                ".$db->quoteName('#__community_fields')."
				WHERE ".$db->quoteName('fieldcode')." = ".$db->quote('FIELD_GENDER');

        $db->setQuery($sql);
        try {
            $row = $db->loadObject();
        } catch (Exception $e) {
            JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
        }

        $data = new stdClass();
        $data->Male = 0;
        $data->Female = 0;

        // the result might return empty records. If thats the case, then
        // return male and female to zero.
        if(empty($row))
        {
            return $data;
        }

        $gender_id = $row->id;
        if(!empty($gender_id)) {
            $sql = "SELECT
								" . $db->quoteName('value') . ",
								COUNT(a." . $db->quoteName('id') . ") AS total
						FROM
								" . $db->quoteName('#__community_fields_values') . " a,
								" . $db->quoteName('#__users') . " b
						WHERE
								b." . $db->quoteName('id') . " = a." . $db->quoteName('user_id') . " AND
								" . $db->quoteName('block') . " = 0 AND
								" . $db->quoteName('field_id') . " = " . $db->quote($gender_id) . "
						GROUP
								BY " . $db->quoteName('value');

            $db->setQuery($sql);
            try {
                $row = $db->loadObjectList();
            } catch (Exception $e) {
                JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
            }

            foreach ($row as $res) {
                $case = JString::strtolower($res->value);

                switch ($case) {
                    case 'com_community_female':
                    case 'female':
                        $data->Female = $data->Female + $res->total;
                        break;
                    case 'com_community_male':
                    case 'male':
                        $data->Male = $data->Male + $res->total;
                        break;
                }
            }
            return $data;
        }
    }

    public function getLatestMembers() {
        $db = $this->getDBO();

        $query = 'SELECT ' . $db->quoteName('id') . ' FROM ' . $db->quoteName('#__users')
                . ' ORDER BY ' . $db->quoteName('id') . ' DESC'
                . ' LIMIT 0,10';

        $db->setQuery($query);

        $result = $db->loadObjectList();

        $userList = array();

        foreach ($result as $_result) {
            $user = CFactory::getUser($_result->id);

            $user->memberstatus = 'approved';

            if ($user->lastvisitDate == '0000-00-00 00:00:00' && $user->isBlocked()) {
                $user->memberstatus = 'pending';
            } elseif ($user->isBlocked()) {
                $user->memberstatus = 'blocked';
            }

            $userList[] = $user;
        }


        return $userList;
    }

    public function getUserCountry() {
        $config = JTable::getInstance( 'configuration' , 'CommunityTable' );
        $config->load( 'countryList' );

        $params = new CParameter($config->params);
        $country = $params->get('countryList');

        return $country;
    }

    public function getUserCity() {
        $config = JTable::getInstance( 'configuration' , 'CommunityTable' );
        $config->load( 'cityList' );

        $params = new CParameter($config->params);
        $city = $params->get('cityList');

        return $city;
    }

    public function getAllUserId() {
        $db = $this->getDBO();

        $query = 'SELECT ' . $db->quoteName('id') . ' FROM ' . $db->quoteName('#__users');

        $db->setQuery($query);

        $result = $db->loadObjectList();

        return $result;
    }

    public function getUserGenderList() {
        $db = $this->getDBO();

        $query = 'SELECT ' . $db->quoteName('id') . ' FROM ' . $db->quoteName('#__community_fields')
                . ' WHERE ' . $db->quoteName('fieldcode') . ' = ' . $db->quote('FIELD_GENDER');

        $db->setQuery($query);

        $result = $db->loadResult();

        if(empty($result)){
            return new stdClass();
        }

        $sql = 'SELECT * FROM' . $db->quoteName('#__community_fields_values')
                . ' WHERE ' . $db->quoteName('field_id') . ' = ' . $db->Quote($result);

        $db->setQuery($sql);

        return $db->loadObjectList();
    }

    public function getUserBirthDateList() {
        $db = $this->getDBO();

        $query = 'SELECT ' . $db->quoteName('id') . ' FROM ' . $db->quoteName('#__community_fields')
                . ' WHERE ' . $db->quoteName('fieldcode') . ' IN( ' . $db->quote('FIELD_BIRTHDATE') . ',' . $db->quote('FIELD_BIRTHDAY') . ' )';

        $db->setQuery($query);

        $result = $db->loadResult();

        if(empty($result)){
            return new stdClass();
        }

        $sql = 'SELECT * FROM' . $db->quoteName('#__community_fields_values')
                . ' WHERE ' . $db->quoteName('field_id') . ' = ' . $db->Quote($result);

        $db->setQuery($sql);

        return $db->loadObjectList();
    }

    public function getPendingMember() {
        $db = $this->getDBO();

        $query = 'SELECT a.' . $db->quoteName('id') . ' FROM ' . $db->quoteName('#__users') . ' AS ' . $db->quoteName('a')
                . ' LEFT JOIN ' . $db->quoteName('#__community_users') . ' AS b '
                . ' ON a.' . $db->quoteName('id') . ' = b.' . $db->quoteName('userid')
                . ' WHERE a.' . $db->quoteName('block') . '=' . $db->Quote(1)
               // . ' AND b.points > ' . $db->Quote(0)
                . ' AND a.' . $db->quoteName('lastvisitDate') . ' = ' . $db->quote('0000-00-00 00:00:00');

        $db->setQuery($query);

        try {
            $result = $db->loadRowList();
        } catch (Exception $e) {
            JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
        }

        return count($result);
    }

}
