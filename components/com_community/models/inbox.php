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

jimport('joomla.application.component.model');
require_once( JPATH_ROOT .'/components/com_community/models/models.php' );

// Deprecated since 1.8.x to support older modules / plugins
//CFactory::load( 'tables' , 'message' );

class CommunityModelInbox extends JCCModel
    implements CNotificationsInterface
{

    var $_data = null;
    var $_pagination = null;

    /**
     *  Constructor to set the limit
     */

    public function __construct(){
        parent::__construct();
        global $option;
        $mainframe = JFactory::getApplication();
        $jinput 	= $mainframe->input;
        $config = CFactory::getConfig();

        // Get pagination request variables
        $limit		= ($config->get('pagination') == 0) ? 5 : $config->get('pagination');
        $limitstart	= $jinput->request->get('limitstart', 0, 'INT');

        if(empty($limitstart))
        {
            $limitstart = $jinput->get('limitstart', 0, 'uint');
        }

        // In case limit has been changed, adjust it
        $limitstart = ($limit != 0 ? (floor($limitstart / $limit) * $limit) : 0);

        $this->setState('limit',$limit);
        $this->setState('limitstart',$limitstart);
    }

    /**
     * Return the conversation list
     */
    public function &getInbox($_isread=true)
    {
        jimport('joomla.html.pagination');
        $my = CFactory::getUser();
        $to = $my->id;

        if (empty($this->_data))
        {
            $this->_data = array();

            $db = $this->getDBO();

            $sql = 'SELECT MAX(b.'.$db->quoteName('id').') AS '.$db->quoteName('bid');
            $sql .= ' FROM '.$db->quoteName('#__community_msg_recepient').' as a, '.$db->quoteName('#__community_msg').' as b';
//			$sql .= ' WHERE (a.'.$db->quoteName('to').' = '.$db->Quote($to) . ' OR a.'.$db->quoteName('msg_from').' = '.$db->Quote($to) . ')';
            $sql .= ' WHERE (a.'.$db->quoteName('to').' = '.$db->Quote($to) . ')';
            $sql .= ' AND b.'.$db->quoteName('id').' = a.'.$db->quoteName('msg_id');
            $sql .= ' AND (a.'.$db->quoteName('deleted').'='.$db->Quote(0) . ' || (a.' . $db->quoteName('deleted') . '=' . $db->Quote(1) . ' && b.from =' . $to . '))';
            $sql .= ' AND (b.'.$db->quoteName('deleted').'='.$db->Quote(0) . ' || (b.'.$db->quoteName('deleted').'='.$db->Quote(1) . ' && b.from !=' . $to . '))';
            $sql .= ' GROUP BY b.'.$db->quoteName('parent');
            $db->setQuery($sql);
            $tmpResult = $db->loadObjectList();
            $strId = '';
            foreach ($tmpResult as $tmp)
            {
                if (empty($strId)) $strId = $tmp->bid;
                else $strId = $strId . ',' . $tmp->bid;
            }

            $result	= null;

            if( ! empty($strId) )
            {
                $sql = 'SELECT b.'.$db->quoteName('id').', b.'.$db->quoteName('from').', b.'.$db->quoteName('parent').', b.'.$db->quoteName('from_name').', b.'.$db->quoteName('posted_on').', b.'.$db->quoteName('subject').', a.'.$db->quoteName('to');
                $sql .= ' FROM '.$db->quoteName('#__community_msg').' as b, '.$db->quoteName('#__community_msg_recepient').' as a ';
                $sql .= ' WHERE b.'.$db->quoteName('id').' in ('.$strId.')';
                $sql .= ' AND b.'.$db->quoteName('id').' = a.'.$db->quoteName('msg_id');
                if(!$_isread)
                {
                    $sql .= ' AND a.'.$db->quoteName('is_read').' = '.$db->Quote('0');
                }
                $sql .= ' AND (a.'.$db->quoteName('deleted').'='.$db->Quote(0) . ' || (a.' . $db->quoteName('deleted') . '=' . $db->Quote(1) . ' && b.from =' . $to . '))';
                $sql .= ' AND (b.'.$db->quoteName('deleted').'='.$db->Quote(0) . ' || (b.'.$db->quoteName('deleted').'='.$db->Quote(1) . ' && b.from !=' . $to . '))';



                $sql .= ' ORDER BY b.'.$db->quoteName('posted_on').' DESC';
                $db->setQuery($sql);
                try {
                    $result = $db->loadObjectList();
                } catch (Exception $e) {
                    JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
                }
            }

            // For each message, find the parent+from, group them together
            $inboxResult =  array();
            if(!empty($result)){
                $inboxModel = CFactory::getModel('inbox');

                foreach($result as $row) {
                    $inboxResult[$row->parent] = $row;
                    $recipient = $inboxModel->getRecepientMessage($row->parent);
                    $inboxResult[$row->parent]->recipientCount = count($recipient);
                }
            }

            $limit 		= $this->getState('limit');
            $limitstart	= $this->getState('limitstart');
            if (empty($this->_pagination)) {
                $this->_pagination = new JPagination(count($inboxResult), $limitstart, $limit );
                $inboxResult = array_slice($inboxResult, $limitstart, $limit);
            }

            return $inboxResult;
        }

        return null;
    }

    /**
     * get Parent
     *
     */
    public function getParent($msgId){
        $db = $this->getDBO();

        if(empty($msgId))
            return 0;

        $sql = 'select parent';
        $sql .= ' from '.$db->quoteName('#__community_msg');
        $sql .= ' where '.$db->quoteName('id').' = '.$db->Quote($msgId);
        $db->setQuery($sql);
        try {
            $result = $db->loadObject();
        } catch (Exception $e) {
            JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
        }

        return $result->parent;
    }

    /**
     * get pagination
     */
    public function &getPagination()
    {
        return $this->_pagination;
    }

    /**
     * Return list of sent items
     */
    public function &getSent()
    {
        jimport('joomla.html.pagination');
        $my = CFactory::getUser();
        $from = $my->id;

        $limit 		= $this->getState('limit');
        $limitstart	= $this->getState('limitstart');

        if (empty($this->_data))
        {
            $this->_data = array();

            $db = $this->getDBO();

            // Get threads
            $sql = 'SELECT MAX(b.'.$db->quoteName('id').') AS '.$db->quoteName('bid');
            $sql .= ' FROM '.$db->quoteName('#__community_msg_recepient').' as a, '.$db->quoteName('#__community_msg').' as b';
//			$sql .= ' WHERE (a.'.$db->quoteName('to').' = '.$db->Quote($to) . ' OR a.'.$db->quoteName('msg_from').' = '.$db->Quote($to) . ')';
            $sql .= ' WHERE (b.'.$db->quoteName('from').' = '.$db->Quote($from) . ')';
            $sql .= ' AND b.'.$db->quoteName('id').' = a.'.$db->quoteName('msg_id');
            $sql .= ' AND b.'.$db->quoteName('deleted').'='.$db->Quote(0);
            $sql .= ' GROUP BY b.'.$db->quoteName('parent');
            $db->setQuery($sql);
            $tmpResult = $db->loadObjectList();
            $strId = '';

            if(count($tmpResult)) {
                foreach ($tmpResult as $tmp) {
                    if (empty($strId)) {
                        $strId = $tmp->bid;
                    }else{
                        $strId = $strId . ',' . $tmp->bid;
                    }
                }
            }

            // OLD STUFF
            $sql = 'SELECT b.*, a.'.$db->quoteName('to').', c.'.$db->quoteName('name').' as '.$db->quoteName('to_name')
                .' FROM '.$db->quoteName('#__community_msg_recepient').' as a, '
                . $db->quoteName('#__community_msg').' as b, '.$db->quoteName('#__users').' c '
                .' WHERE ';


            $sql .= (strlen($strId)) ? " b.".$db->quoteName('id')." IN (".$strId.") AND  " : ' 0 AND ';

            $sql .= '  b.'.$db->quoteName('id').' = a.'.$db->quoteName('msg_id')
                .' AND a.'.$db->quoteName('to').' = c.'.$db->quoteName('id')
                .' GROUP BY b.'.$db->quoteName('parent')
                .' ORDER BY b.'.$db->quoteName('posted_on').' DESC LIMIT '.$limitstart.','.$limit;

            $db->setQuery($sql);
            try {
                $result = $db->loadObjectList();
            } catch (Exception $e) {
                JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
            }

            $sql = 'SELECT b.parent'
                .' FROM '.$db->quoteName('#__community_msg_recepient').' as a, '
                . $db->quoteName('#__community_msg').' as b, '.$db->quoteName('#__users').' c '
                .' WHERE ';

            $sql .= (strlen($strId)) ? " b.".$db->quoteName('id')." IN ('.$strId.') AND " : "" ;

            $sql .= ' b.'.$db->quoteName('id').' = a.'.$db->quoteName('msg_id')
                .' AND a.'.$db->quoteName('to').' = c.'.$db->quoteName('id')
                .' GROUP BY b.'.$db->quoteName('parent');

            $db->setQuery($sql);
            $res = $db->loadObjectList();
            $numresult = count($tmpResult);

            // For each message, find the parent+from, group them together
            $inboxResult	=  array();
            $inToName 	=  array();
            $inToId   	=  array();

            if(!empty($result))
            {
                foreach($result as $row)
                {
                    if( !isset( $inboxResult[ $row->parent ] ) )
                    {
                        $inToName[$row->parent][$row->to_name] = $row->to_name;
                        $inToId[$row->parent][]	= $row->to;
                        $inboxResult[$row->parent] = $row;
                    }
                }
            }

            //now rewrite back the to / to_name
            $inboxModel = CFactory::getModel('inbox');

            foreach($inboxResult as $row)
            {
                $recipient = $inboxModel->getRecepientMessage($row->parent);
                $inboxResult[$row->parent]->recipientCount = count($recipient);
                $inboxResult[$row->parent]->to = $inToId[$row->parent];
                $inboxResult[$row->parent]->to_name = $inToName[$row->parent];
            }

            if(empty($this->_pagination))
            {
                $this->_pagination = new JPagination($numresult, $limitstart, $limit );
                $inboxResult = array_values($inboxResult);
            }

            return $inboxResult;
        }

        return null;
    }
    /**
     * Return the full messages
     */
    public function getFullMessages($id){
        $db = $this->getDBO();

        $parentmsgid = $this->getParent($id);

        $query	= 'SELECT * FROM '.$db->quoteName('#__community_msg')
            .' WHERE '.$db->quoteName('parent').'=' . $db->Quote($parentmsgid);
        $query .= ' ORDER BY '.$db->quoteName('id');
        $db->setQuery($query);

        $result = $db->loadObjectList();

        return $result;
    }

    /**
     * Return the sent messages for later removal.
     */
    public function getSentMessages($id)
    {
        $db = $this->getDBO();
        $my	= CFactory::getUser();

        $parentmsgid = $this->getParent($id);

        $query	= 'SELECT * FROM '.$db->quoteName('#__community_msg')
            .' WHERE '.$db->quoteName('parent').'=' . $db->Quote($parentmsgid);
        $query	.= ' AND '.$db->quoteName('from').' = ' . $db->Quote($my->id);
        $query	.= ' ORDER BY '.$db->quoteName('id');
        $db->setQuery($query);

        $result = $db->loadObjectList();

        return $result;
    }


    /**
     * Return the message
     */
    public function &getMessage($id){
        $db = $this->getDBO();
        $sql = 'SELECT * FROM '.$db->quoteName('#__community_msg')
            .' WHERE '.$db->quoteName('id').'=' . $db->Quote($id);

        $db->setQuery($sql);
        try {
            $result = $db->loadObject();
        } catch (Exception $e) {
            JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
        }

        return $result;
    }

    /**
     * Return the recepient message
     * @param $id
     * @param bool $forceShow - this is used to force the msg to be shown, e.g. : used in remove message
     * @return array
     */
    public function &getRecepientMessage($id, $forceShow = false){
        $db = $this->getDBO();
        $sql = 'SELECT * FROM '.$db->quoteName('#__community_msg_recepient')
            .' WHERE '.$db->quoteName('msg_id').'=' . $db->Quote($id);

        $db->setQuery($sql);
        try {
            $result = $db->loadObjectList();
        } catch (Exception $e) {
            JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
        }

        $return = array();

        foreach($result as $recipient) {
            $getBlockStatus		= new blockUser();
            if($getBlockStatus->isUserBlocked($recipient->msg_from, 'inbox') && !$forceShow) continue;
            if($getBlockStatus->isUserBlocked($recipient->to, 'inbox') && !$forceShow) continue;
            $return[] = $recipient;
        }

        return $return;
    }

    /**
     * Return the time the given user send the last message
     */
    public function getLastSentTime($id){
        $user = CFactory::getUser($id);
        $db = $this->getDBO();
        $sql = 'SELECT '.$db->quoteName('posted_on')
            .' FROM '.$db->quoteName('#__community_msg')
            .' WHERE '.$db->quoteName('from').'=' . $db->Quote($id)
            .' ORDER BY '.$db->quoteName('posted_on').' DESC';

        $db->setQuery($sql);
        $postedOn = $db->loadResult();

        if(empty($postedOn)){
            // set to a far distance past to indicate last sent time was
            // very far away in the past
            return new JDate('1990-01-01 10:00:00');
        } else {
            return new JDate($postedOn);
        }
    }

    /**
     * Return the latest recepient message based on parent message id.
     */
    public function &getUserMessage($id){
        $my = CFactory::getUser();
        $to = $my->id;

        $db = $this->getDBO();

        $sql = 'select a.* from '.$db->quoteName('#__community_msg_recepient').' a';
        $sql .= " where a.".$db->quoteName('to')." = {$to} and a.".$db->quoteName('msg_parent')." = (select distinct b.".$db->quoteName('msg_parent');
        $sql .= ' from '.$db->quoteName('#__community_msg_recepient').' b where b.'.$db->quoteName('msg_id').' = ' . $db->Quote($id) . ')';
        $sql .= ' order by '.$db->quoteName('msg_id').' desc limit 1';

        $db->setQuery($sql);
        try {
            $result = $db->loadObject();
        } catch (Exception $e) {
            JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
        }

        return $result;
    }

    public function &getMessages($filter = array(), $read = false)
    {

        $my = CFactory::getUser();
        $db = $this->getDBO();

        if (empty($this->_data))
        {
            $this->_data = array();

            $isRead = "";
            if($read){
                $isRead = ' AND b.is_read=0';
            }

            $sql = 'SELECT a.*, b.'.$db->quoteName('to').', b.'.$db->quoteName('deleted').' as '.$db->quoteName('to_deleted').', b.'.$db->quoteName('is_read')
                .' FROM '.$db->quoteName('#__community_msg').' a, '.$db->quoteName('#__community_msg_recepient').' b'
                .' where a.'.$db->quoteName('parent').' = ' . $db->Quote($filter['msgId'])

                // only messages targetted at me or sent by me
                .' and (b.to='.$my->id.' or a.from='.$my->id.') '

                .' and  b.'.$db->quoteName('msg_parent').' = ' . $db->Quote($filter['msgId'])
                .' and  a.'.$db->quoteName('id').' = b.'.$db->quoteName('msg_id').$isRead
                .' order by a.'.$db->quoteName('id').' desc, a.'.$db->quoteName('deleted').' desc, b.'.$db->quoteName('deleted').' desc';

            $db->setQuery($sql);

            // Now, we get all the conversation within this discussion
            try {
                $allMsgFromMe = $db->loadObjectList();
            } catch (Exception $e) {
                JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
            }

            // perform further filtering
            $prev_id = 0;
            foreach($allMsgFromMe as $row){
                $showMsg = true;

                if($row->to == $my->id){ //message for me.
                    $showMsg = ($row->to_deleted == 0);
                } else if($row->from == $my->id){ // message from me
                    $showMsg = ($row->deleted == 0);
                }

                // check whether this message id is the same as previous one or not.
                // if yes...mean the message send to multiple users. We need to show
                // only one time.
                if($showMsg){
                    $showMsg = ($row->id != $prev_id);
                }

                //update the flag for next checking.
                $prev_id = $row->id;

                if($showMsg){
                    //append message into array object
                    $this->_data[] = $row;
                }
            }

            //reverse the array so that it show the old to latest.
            $this->_data = array_reverse($this->_data);

        }

        return $this->_data;
    }


    public function send($vars)
    {
        $db = $this->getDBO();
        $my	= CFactory::getUser();

        // @todo: user db table later on
        //$cDate = JDate::getInstance(gmdate('Y-m-d H:i:s'), $mainframe->get('offset'));//get the current date from system.
        //$date	= cGetDate();
        $date	= JDate::getInstance(); //get the time without any offset!
        $cDate	=$date->toSql();

        $obj = new stdClass();
        $obj->id = null;
        $obj->from = $my->id;
        $obj->posted_on = $date->toSql();
        $obj->from_name	= $my->name;
        $obj->subject	= $vars['subject'];
        $obj->body		= $vars['body'];

        $body = new JRegistry();
        $body->set( 'content', $obj->body );

        if(isset($vars['file_id']) && $vars['file_id']){
            $body->set('file_id', $vars['file_id']);
        }

        // photo attachment
        if(isset($vars['photo'])){
            $photoId = $vars['photo'];
            if($photoId > 0){
                //lets check if the photo belongs to the uploader
                $photo = JTable::getInstance('Photo', 'CTable');
                $photo->load($photoId);

                if($photo->creator == $my->id && $photo->albumid == '-1'){
                    $body->set('attached_photo_id', $photoId);

                    //sets the status to ready so that it wont be deleted on cron run
                    $photo->status = 'ready';
                    $photo->store();
                }
            }
        }

        /**
         * @since 3.2.1
         * Message URL fetching
         */
        if (( preg_match("/\b(?:(?:https?|ftp):\/\/|www\.)[-a-z0-9+&@#\/%?=~_|!:,.;]*[-a-z0-9+&@#\/%=~_|]/i", $obj->body))) {
            $graphObject = CParsers::linkFetch($obj->body);
            if ($graphObject){
                $graphObject->merge($body);
                //reappend the message so the the emoji wont disappeared upon using toString function
                $obj->body = json_encode($graphObject);
            }
        } else {
            $obj->body = json_encode($body); // we using this to prevent emoji loss when using toString function from jregistry
        }

        // Don't add message if user is sending message to themselve
        if( $vars['to']!=$my->id ){

            $db->insertObject('#__community_msg', $obj, 'id');

            // Update the parent
            $obj->parent = $obj->id;
            $db->updateObject('#__community_msg', $obj, 'id');
        }

        if(is_array($vars['to'])){

            //multiple recepint
            foreach($vars['to'] as $sToId){
                if( $vars['to']!=$my->id )
                    $this->addReceipient($obj, $sToId);
            }
        } else {

            //single recepient
            if( $vars['to']!=$my->id )
                $this->addReceipient($obj, $vars['to']);
        }

        return $obj->id;
    }

    /**
     *
     */
    public function sendReply($obj, $replyMsgId){
        $db = $this->getDBO();
        $my	= CFactory::getUser();

        // get original sender from obj
        $originalMsg  = new CTableMessage($db);
        $originalMsg->load($replyMsgId);

        $recepientMsg = $this->getRecepientMessage($replyMsgId);
        $parentId = $originalMsg->parent;

        $db->insertObject('#__community_msg', $obj, 'id');

        // Update the parent
        $obj->parent = $parentId;
        $db->updateObject('#__community_msg', $obj, 'id');

        if(is_array($recepientMsg)){
            $recepientId = $this->getParticipantsID($replyMsgId, $my->id);

            foreach($recepientId as $sToId){
                $this->addReceipient($obj, $sToId);
            }

        } else {

            // add receipient, get the 'to' address from the original
            // sender. BUT, in some case where user try to post two message in
            // a row, the 'from' will failed. instead, we need to use 'to' from
            // the original message.
            $recepientId = $originalMsg->from;
            if($my->id == $originalMsg->from){
                $recepientId = $recepientMsg->to;
            }
            $this->addReceipient($obj, $recepientId);

        }

        return $obj->id;
    }

    /**
     * Add receipient
     */
    public function addReceipient($msgObj, $recepientId){
        $getBlockStatus		= new blockUser();
        if($getBlockStatus->isUserBlocked($recepientId, 'inbox')) {
            return $this;
        }
        $db = $this->getDBO();
        $my	= CFactory::getUser();

        $recepient = new stdClass();
        $recepient->msg_id = $msgObj->id;
        $recepient->msg_parent = $msgObj->parent;
        $recepient->msg_from = $msgObj->from;
        $recepient->to	= $recepientId;

        if( $my->id != $recepientId ) {
            try {
                $db->insertObject('#__community_msg_recepient', $recepient);
            } catch (Exception $e) {
                JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
            }
        }
        return $this;
    }

    /**
     * Remove the received message
     */
    public function removeReceivedMsg($msgId, $userid){
        $db = $this->getDBO();

        // get original sender and recepient
        $originalMsg  = new CTableMessage($db);
        $originalMsg->load($msgId);

        $recepientMsg = $this->getRecepientMessage($msgId, true);

        // we need to determind which table we needed for message removal.
        // we 1st check on the original message 'from', current user id matched,
        // then we remove from master table.
        // ELSE, we remove from child table.

        $sql = "";
        $delFrom = false;
        $delTo   = false;
        if($originalMsg->from == $userid){
            $sql = 'UPDATE '.$db->quoteName('#__community_msg')
                .' SET '.$db->quoteName('deleted').'='.$db->Quote('1')
                .' WHERE '.$db->quoteName('id').'=' . $db->Quote($msgId) . ' AND '.$db->quoteName('from').'=' . $db->Quote($userid);

            //executing update query
            $db->setQuery($sql);
            $db->execute();
            $delFrom = true;
        }

        if(is_array($recepientMsg)){
            //multi recepient
            //echo "array";

            foreach($recepientMsg as $row){
                if($row->to == $userid) {
                    $sql = 'UPDATE '.$db->quoteName('#__community_msg_recepient')
                        .' SET '.$db->quoteName('deleted').'='.$db->Quote('1')
                        .' WHERE '.$db->quoteName('msg_id').'=' . $db->Quote($msgId) . ' AND '.$db->quoteName('to').'=' . $db->Quote($userid);
                    //executing update query
                    $db->setQuery($sql);
                    $db->execute();
                    $delTo = true;
                }
            }
        } else {
            if($recepientMsg->to == $userid) {
                $sql = 'UPDATE '.$db->quoteName('#__community_msg_recepient')
                    .' SET '.$db->quoteName('deleted').'='.$db->Quote('1')
                    .' WHERE '.$db->quoteName('msg_id').'=' . $db->Quote($msgId) . ' AND '.$db->quoteName('to').'=' . $db->Quote($userid);
                //executing update query
                $db->setQuery($sql);
                $db->execute();
                $delTo = true;
            }
        }


        if($delFrom == false && $delTo == false) {
            //both oso not matched. return false.
            return false;
        }

        return true;
    }

    public function &getUserId($param = array()){

        $db= $this->getDBO();
        $userId = 0;
        $sql = "";

        if(! empty($param['name'])){
            // get from users table
            $sql = 'select '.$db->quoteName('id')
                .' from '.$db->quoteName('#__users')
                .' where '.$db->quoteName('username').' = '.$db->Quote($param['name']);
        } else {
            // get from community_message table
            $sql = 'select '.$db->quoteName('from').' as '.$db->quoteName('id')
                .' from '.$db->quoteName('#__community_message')
                .' where '.$db->quoteName('id').' = '.$db->Quote($param['id']);
        }

        $db->setQuery($sql);

        try {
            $result = $db->loadResult();
        } catch (Exception $e) {
            JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
        }

        if(! empty($result)) $userId = $result;

        return $userId;
    }

    /**
     * Mark a message as "read" (opened)
     * @param	object 		parent message id
     * @param	object 		current user id
     */
    public function markMessageAsRead($filter){
        $db= $this->getDBO();
        $my = CFactory::getUser();

        // update all the messages that belong to current user.
        $sql = 'UPDATE '.$db->quoteName('#__community_msg_recepient')
            .' SET '.$db->quoteName('is_read').'= '.$db->Quote('1')
            .' WHERE '.$db->quoteName('msg_parent').'=' . $db->Quote($filter['parent']) . ' AND '.$db->quoteName('to').'=' . $db->Quote($filter['user_id'])
            .' AND '.$db->quoteName('is_read').'= '.$db->Quote('0');

        //executing update query
        $db->setQuery($sql);
        $db->execute();

        return true;
    }

    /**
     * Mark a message as "new"
     * @param	object 		parent message id
     * @param	object 		current user id
     */
    public function markMessageAsUnread($filter){
        $db= $this->getDBO();
        $my = CFactory::getUser();

        // update all the messages that belong to current user.
        $sql = 'UPDATE '.$db->quoteName('#__community_msg_recepient')
            .' SET '.$db->quoteName('is_read').'='.$db->Quote('0')
            .' WHERE '.$db->quoteName('msg_parent').'=' . $db->Quote($filter['parent']) . ' AND '.$db->quoteName('to').'=' . $db->Quote($filter['user_id'])
            .' AND '.$db->quoteName('is_read').'= '.$db->Quote('1');

        //executing update query
        $db->setQuery($sql);
        $db->execute();

        return true;
    }

    /**
     * Mark a message as "read" (opened) from Inbox page
     * @param	object 		message id
     * @param	object 		current user id
     */
    public function markAsRead($filter){
        $db= $this->getDBO();
        $my = CFactory::getUser();

        // update all the messages that belong to current user.
        $sql = 'UPDATE '.$db->quoteName('#__community_msg_recepient')
            .' SET '.$db->quoteName('is_read').'= '.$db->Quote('1')
            .' WHERE '.$db->quoteName('msg_id').'=' . $db->Quote($filter['parent']) . ' AND '.$db->quoteName('to').'=' . $db->Quote($filter['user_id'])
            .' AND '.$db->quoteName('is_read').'= '.$db->Quote('0');

        //executing update query
        $db->setQuery($sql);
        $db->execute();

        return true;
    }

    /**
     * Mark a message as "read" (opened) from Inbox page
     * @param	object 		message id
     * @param	object 		current user id
     */
    public function markAsUnread($filter){
        $db= $this->getDBO();
        $my = CFactory::getUser();

        // update all the messages that belong to current user.
        $sql = 'UPDATE '.$db->quoteName('#__community_msg_recepient')
            .' SET '.$db->quoteName('is_read').'= '.$db->Quote('0')
            .' WHERE '.$db->quoteName('msg_id').'=' . $db->Quote($filter['parent']) . ' AND '.$db->quoteName('to').'=' . $db->Quote($filter['user_id'])
            .' AND '.$db->quoteName('is_read').'= '.$db->Quote('1');

        //executing update query
        $db->setQuery($sql);
        $db->execute();

        return true;
    }

    /**
     * Check if the user can reply to this message thread
     */
    public function canReply( $userid, $msgId ){
        $db= $this->getDBO();
        $sql = 'SELECT COUNT(*) FROM '.$db->quoteName('#__community_msg_recepient')
            .' WHERE ('.$db->quoteName('msg_parent').'=' . $db->Quote($msgId) . ' OR '.$db->quoteName('msg_id').'=' . $db->Quote($msgId) . ' ) '
            .' AND ( '.$db->quoteName('to').'=' . $db->Quote($userid) .' OR '.$db->quoteName('msg_from').'=' . $db->Quote($userid) .' )';

        $db->setQuery($sql);
        //echo $db->getQuery();

        return $db->loadResult();
    }

    /**
     * Check if user can read this message.
     *
     * @param	string 	userid
     * @param 	string	msgID : should be the parent message
     */
    public function canRead( $userid, $msgId ) {
        // really, if the user can reply to this message, then he can read it
        return $this->canReply( $userid, $msgId );
    }

    public function getTotalMessageSent( $userId )
    {
        //CFactory::load( 'helpers' , 'time' );
        $date		= CTimeHelper::getDate();
        $db			= $this->getDBO();

        //Joomla 1.6 JDate::getOffset returns in second while in J1.5 it's in hours
        $query	= 'SELECT COUNT(*) FROM ' . $db->quoteName( '#__community_msg' ) . ' AS a '
            . 'WHERE a.'.$db->quoteName('from').'=' . $db->Quote( $userId )
            . ' AND TO_DAYS(' . $db->Quote( $date->toSql( true ) ) . ') - TO_DAYS( DATE_ADD( a.'.$db->quoteName('posted_on')
            .' , INTERVAL ' . ($date->getOffset() / 3600) . ' HOUR ) ) = '.$db->Quote('0')
            . ' AND a.'.$db->quoteName('parent').'=a.'.$db->quoteName('id');
        $db->setQuery( $query );

        $count		= $db->loadResult();

        return $count;
    }

    /**
     * Get unread message count for current user
     * @param	int		parent message id
     * @param	int		current user id
     * @return  int     unread message count
     */
    public function countUnRead($filter){
        $db= $this->getDBO();
        $unRead = 0;
        if(!CFactory::getConfig()->get('enablepm')){
            return $unRead;
        }
        // Skip the whole db query if no user specified
        if(empty($filter['user_id']))
            return 0;

        $sql = 'select count('.$db->Quote('1').') as '.$db->quoteName('unread_count');
        $sql .= ' from '.$db->quoteName('#__community_msg_recepient');
        $sql .= ' where '.$db->quoteName('is_read').' = '.$db->Quote('0');
        if(! empty($filter['parent']))
            $sql .= ' and '.$db->quoteName('msg_parent').' =' . $db->Quote($filter['parent']);
        if(! empty($filter['user_id']))
            $sql .= ' and '.$db->quoteName('to').' =' . $db->Quote($filter['user_id']);

        $sql .= ' and '.$db->quoteName('deleted').' = '.$db->Quote('0');
        $db->setQuery($sql);
        $result = $db->loadObject();

        if(! empty($result)){
            $unRead = $result->unread_count;
        }

        return $unRead;
    }

    /**
     * Get total recepient conversation message count for a message.
     */
    public function getRecepientCount($filter){
        $db= $this->getDBO();
        $msgCnt = 0;

        $sql = 'select count('.$db->Quote('1').') as '.$db->quoteName('recepient_count');
        $sql .= ' from '.$db->quoteName('#__community_msg_recepient');
        $sql .= ' where '.$db->quoteName('msg_parent').' = ' . $db->Quote($filter['parent']);
        if(! empty($filter['user_id']))
            $sql .= " and ".$db->quoteName('to')." !=" . $db->Quote($filter['user_id']);

        $db->setQuery($sql);
        $result = $db->loadObject();

        if(! empty($result)){
            $msgCnt = $result->unread_count;
        }

        return $msgCnt;
    }

    /**
     * Given any message id, return an array of userid that are involved in the
     * conversation, be it recipient or sender.
     *
     */
    public function getParticipantsID($msgid, $exclusion=0){
        $getParticipantsIDs = array();
        $db= $this->getDBO();

        // with the given msgid, get the parent.
        $sql = 'SELECT '.$db->quoteName('parent');
        $sql .= ' FROM '.$db->quoteName('#__community_msg');
        $sql .= ' WHERE '.$db->quoteName('id').' = '. $db->Quote($msgid);

        $db->setQuery($sql);
        try {
            $parentId = $db->loadResult();
        } catch (Exception $e) {
            JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
        }


        // with the parentid, get all the recipient and the senderid
        $sql = 'SELECT '.$db->quoteName('msg_from').', '.$db->quoteName('to');
        $sql .= ' FROM '.$db->quoteName('#__community_msg_recepient');
        $sql .= ' WHERE '.$db->quoteName('msg_parent').' = '. $db->Quote($parentId);
        $db->setQuery($sql);
        try {
            $result = $db->loadObjectList();
        } catch (Exception $e) {
            JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
        }

        if($result){
            foreach($result as $row){
                if($exclusion != $row->to){
                    $getParticipantsIDs[] = $row->to;
                }

                if($exclusion != $row->msg_from){
                    $getParticipantsIDs[] = $row->msg_from;
                }
            }

            $getParticipantsIDs = array_unique($getParticipantsIDs);
        }

        return $getParticipantsIDs;
    }

    /**
     * Get all recepient user id for a message except the current userid.
     *
     * @depreciated, use getParticipantsID instead
     */
    public function &getMultiRecepientID($filter = array()){
        $db= $this->getDBO();
        $my = CFactory::getUser();

        $originalMsg  = new CTableMessage($db);
        $originalMsg->load($filter['reply_id']);

        $RecepientMsg = $this->getRecepientMessage($filter['reply_id']);

        $recepient = array();

        if($my->id != $originalMsg->from){
            $recepient[] = $originalMsg->from; // the original sender
        }

        foreach($RecepientMsg as $row){
            if($my->id != $row->to){
                $recepient[] = $row->to; // the original sender
            }
        }

        return $recepient;
    }


    /**
     * Get current user all the unread messages
     * param user_id
     */
    public function &getUnReadInbox()
    {
        $db= $this->getDBO();
        $my = CFactory::getUser();

        $sql = 'SELECT b.'.$db->quoteName('id').', b.'.$db->quoteName('from').', b.'.$db->quoteName('parent').', b.'.$db->quoteName('from_name').', b.'.$db->quoteName('posted_on').', b.'.$db->quoteName('subject');
        $sql .= ' FROM '.$db->quoteName('#__community_msg_recepient').' as a, '.$db->quoteName('#__community_msg').' as b';
        $sql .= ' WHERE a.'.$db->quoteName('to').' = '.$db->Quote($my->id);
        $sql .= ' AND '.$db->quoteName('is_read').' = '.$db->Quote('0');
        $sql .= ' AND a.'.$db->quoteName('deleted').' = '.$db->Quote('0');
        $sql .= ' AND b.'.$db->quoteName('id').' = a.'.$db->quoteName('msg_id');
        $sql .= ' ORDER BY b.'.$db->quoteName('posted_on').' DESC';

        $db->setQuery($sql);
        $result = $db->loadObjectList();

        return $result;
    }


    /**
     * Get current user latest messages
     * param user_id
     * param limit (optional)
     */
    public function &getLatestMessage($filter = array(), $limit = 5){
        $db= $this->getDBO();
        $my = CFactory::getUser();

        $user_id = (empty($filter['user_id'])) ? $my->id : $filter['user_id'];

        $sql = 'select a.'.$db->quoteName('msg_id').', a.'.$db->quoteName('msg_parent').' , b.'.$db->quoteName('from').', b.'.$db->quoteName('from_name').',';
        $sql .= ' b.'.$db->quoteName('posted_on').', b.'.$db->quoteName('body');
        $sql .= ' from '.$db->quoteName('#__community_msg_recepient').' a, '.$db->quoteName('#__community_msg').' b';
        $sql .= ' where a.'.$db->quoteName('to').' =' . $db->Quote($user_id);
        $sql .= ' and a.'.$db->quoteName('deleted').' = '.$db->Quote('0');
        $sql .= ' and a.'.$db->quoteName('msg_id').' = b.'.$db->quoteName('id');
        $sql .= ' order by '.$db->quoteName('msg_id').' desc';
        $sql .= ' limit {$limit}';

        $db->setQuery($sql);

        try {
            $result = $db->loadObjectList();
        } catch (Exception $e) {
            JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
        }

        return $result;
    }

    public function getUserInboxCount()
    {
        $db				= $this->getDBO();
        $my				= CFactory::getUser();
        $inboxResult	= array();

        // Select all recent message to the user
        $sql = 'SELECT MAX(b.'.$db->quoteName('id').') AS '.$db->quoteName('bid');
        $sql .= ' FROM '.$db->quoteName('#__community_msg_recepient').' as a, '.$db->quoteName('#__community_msg').' as b';
        $sql .= ' WHERE a.'.$db->quoteName('to').' = ' .$db->Quote($my->id);
        $sql .= ' AND b.'.$db->quoteName('id').' = a.'.$db->quoteName('msg_id');
        $sql .= ' AND a.'.$db->quoteName('deleted').'='.$db->Quote('0');
        $sql .= ' GROUP BY b.'.$db->quoteName('parent');
        $db->setQuery($sql);
        $tmpResult = $db->loadObjectList();

        $strId = '';
        foreach ($tmpResult as $tmp)
        {
            if (empty($strId)) $strId = $tmp->bid;
            else $strId = $strId . ',' . $tmp->bid;
        }

        $result	= null;
        if( ! empty($strId) )
        {
            $sql = 'SELECT b.'.$db->quoteName('id').', b.'.$db->quoteName('parent').', b.'.$db->quoteName('posted_on');
            $sql .= ' FROM '.$db->quoteName('#__community_msg').' as b';
            $sql .= ' WHERE b.'.$db->quoteName('id').' in ('.$strId.')';
            $sql .= ' ORDER BY b.'.$db->quoteName('posted_on').' DESC';

            $db->setQuery($sql);
            try {
                $result = $db->loadObjectList();
            } catch (Exception $e) {
                JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
            }
        }

        // For each message, find the parent+from, group them together
        if(!empty($result)){
            foreach($result as $row) {
                $inboxResult[$row->parent] = $row;
            }
        }

        return count($inboxResult);
    }

    /**
     * Returns a list of unread or notifications for the users inbox
     *
     **/
    public function getTotalNotifications( $user )
    {
        return (int) $this->countUnRead( array( 'user_id' => $user->id ) );
    }
}
