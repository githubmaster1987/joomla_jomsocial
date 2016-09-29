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

class CommunityModelFiles extends JCCModel implements CLimitsInterface {

    /**
     * Get List of available Files
     * @param  [string] $type       [is it group,event,profile or etc]
     * @param  [int]    $id         [groupid,eventid,profile id]
     * @param  integer  $limitstart [start limit]
     * @param  integer  $limit      [limit]
     * @param  [string] $extension  [is it doc,img,multimedia]
     * @return [object] result of file
     */
    public function getFileList($type, $id, $limitstart = 0, $limit = 8, $extension = null) {
        $type = $type . 'id';
        $db = JFactory::getDBO();
        $defaultextension = array('document', 'archive', 'images', 'multimedia', 'miscellaneous');

        if ($extension && in_array($extension, $defaultextension, true)) {
            $extrasql = ' AND ' . $db->quoteName('type') . ' = ' . $db->Quote($extension);
        } elseif ($extension) {
            $extrasql = ' AND ' . $db->quoteName('name') . ' LIKE ' . $db->Quote('%' . $extension . '%');
        } else {
            $extrasql = '';
        }

        $query = 'SELECT * FROM '
                . $db->quoteName('#__community_files') . ' '
                . 'WHERE ' . $db->quoteName($type) . '=' . $db->Quote($id)
                . $extrasql;

        $db->setQuery($query, $limitstart, $limit);
        $result = $db->loadObjectList();

        return $result;
    }

    /**
     * Get Count for groups file listing for each section
     * @param  [int] $groupId   [group id]
     * @param  string $extension [mostdownload,img,doc]
     * @param  string $field     [which field to check]
     * @return [int]  no of file
     */
    public function getGroupFileCount($groupId, $extension = 'mostdownload', $field = 'groupid') {
        $db = JFactory::getDBO();

        if ($extension == 'mostdownload') {
            $extrasql = '';
        } else {
            $extrasql = ' AND ' . $db->quoteName('type') . ' = ' . $db->Quote($extension);
        }

        $query = 'SELECT COUNT(*) FROM ' . $db->quoteName('#__community_files')
                . ' WHERE ' . $db->quoteName($field) . ' = ' . $db->Quote($groupId)
                . $extrasql;


        $db->setQuery($query);

        $count = $db->loadResult();

        return $count;
    }

    /**
     * Return total photos for the day for the specific user.
     *
     * @param	string	$userId	The specific userid.
     * */
    function getTotalToday($userId) {
        $db = JFactory::getDBO();
        $date = JDate::getInstance();

        $query = 'SELECT COUNT(*) FROM ' . $db->quoteName('#__community_files')
                . ' AS a WHERE ' . $db->quoteName('creator') . '=' . $db->Quote($userId)
                . ' AND TO_DAYS(' . $db->Quote($date->toSql(true)) . ') - TO_DAYS( DATE_ADD( a.'.$db->quoteName('created').' , INTERVAL ' . $date->getOffset() . ' HOUR ) ) = 0 ';

        $db->setQuery($query);
        return $db->loadResult();
    }

    /**
     * Get top download file
     * @param  [int]    $groupId    [group id]
     * @param  integer  $limitstart [query start limit]
     * @param  [int]    $limit      [quer row limit]
     * @param  [string] $type       [which field need to search]
     * @return [object] Object list of top downloaded file
     */
    public function getTopDownload($groupId, $limitstart = 0, $limit, $type) {
        $db = JFactory::getDBO();

        $query = 'SELECT * FROM ' . $db->quoteName('#__community_files')
                . ' WHERE ' . $db->quoteName($type) . ' = ' . $db->Quote($groupId)
                . ' ORDER BY ' . $db->quoteName('hits') . ' DESC';
        $db->setQuery($query, $limitstart, $limit);

        return $db->loadObjectList();
    }

    /**
     * Delete all files
     * @param  [int] $typeId [group,event,profile id]
     * @param  [string] $type   [group,event,profile]
     */
    public function alldelete($typeId, $type) {
        $db = JFactory::getDBO();
        $type = $type . 'id';

        $query = 'SELECT ' . $db->quoteName('id') . ' FROM ' . $db->quoteName('#__community_files')
                . ' WHERE ' . $db->quoteName($type) . ' = ' . $db->Quote($typeId);

        $db->setQuery($query);

        $data = $db->loadObjectList();

        $file = JTable::getInstance('File', 'CTable');

        if (!empty($data)) {
            foreach ($data as $_data) {
                $file->load($_data->id);
                $file->delete();
            }
        }
    }

    /**
     * * Get if file is available for that specific type
     * @param $id   [group,event,profile id]
     * @param $type [group,event.profile]
     * @return bool/int
     */
    public function isfileAvailable($id, $type) {
        $db = JFactory::getDBO();

        $query = 'SELECT COUNT(*) FROM ' . $db->quoteName('#__community_files')
                . ' WHERE ' . $db->quoteName($type . 'id') . ' = ' . $db->Quote($id);

        $db->setQuery($query);
        $total = $db->loadResult();

        return ($total > 0 ) ? $total : false;
    }

    /**
     * get all the files within the message ids
     * @param array $messageIds
     * @return bool
     */
    public function getMessageFiles($messageIds = array()){

        if(count($messageIds) > 0){
            $db = JFactory::getDBO();

            $messageIds = implode(',',$messageIds);

            $query = 'SELECT * FROM ' . $db->quoteName('#__community_files')
                . ' WHERE ' . $db->quoteName('messageid') . ' IN (' . $messageIds.')';

            $db->setQuery($query);
            return $db->loadObjectList();
        }

        return false;
    }

    /**
     * Assign temporary file which belongs to a new message to the message id when the message is created
     * @param $messageid
     * @param $fileid
     */
    public function updateMessageFile($messageId, $fileId){
        //first verify if the file is valid and still unassigned
        $file = JTable::getInstance('File', 'CTable');
        $file->load($fileId);

        if($file->messageid != '-1' || $file->creator != CFactory::getUser()->id){
            //-1 means its unassigned
            return false;
        }

        $file->messageid = $messageId;
        $tempPathFile = $file->filepath;
        $file->filepath = str_replace("/temp/", "/".$messageId."/", $file->filepath);

        if (!JFolder::exists(JPATH_ROOT . '/message/' . $messageId)) {
            JFolder::create(JPATH_ROOT . '/images/files' . '/message/' . $messageId, (int) octdec(CFactory::getConfig()->get('folderpermissionsphoto')));
            JFile::copy(JPATH_ROOT . '/components/com_community/index.html', JPATH_ROOT . '/images/files/message/' . $messageId . '/index.html');
        }

        //lets move the folder of temp folder to the actual folder
        JFile::move(JPATH_ROOT . '/' .$tempPathFile, JPATH_ROOT . '/' . $file->filepath);

        $file->store();

        return true;
    }

}
