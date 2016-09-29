<?php
/**
 * @copyright (C) 2013 iJoomla, Inc. - All rights reserved.
 * @license GNU General Public License, version 2 (http://www.gnu.org/licenses/gpl-2.0.html)
 * @author iJoomla.com <webmaster@ijoomla.com>
 * @url https://www.jomsocial.com/license-agreement
 * The PHP code portions are distributed under the GPL license. If not otherwise stated, all images, manuals, cascading style sheets, and included JavaScript *are NOT GPL, and are released under the IJOOMLA Proprietary Use License v1.0
 * More info at https://www.jomsocial.com/license-agreement
 */

// Check to ensure this file is included in Joomla!
defined ( '_JEXEC' ) or die ();

class CommunityInboxController extends CommunityBaseController
{
    var $_icon = 'inbox';

    private function _isSpam( $user , $data )
    {
        $config	= CFactory::getConfig();

        // @rule: Spam checks
        if( $config->get( 'antispam_akismet_messages') )
        {

            $filter				= CSpamFilter::getFilter();
            $filter->setAuthor( $user->getDisplayName() );
            $filter->setMessage( $data );
            $filter->setEmail( $user->email );
            $filter->setURL( JURI::root() );
            $filter->setType( 'message' );
            $filter->setIP( $_SERVER['REMOTE_ADDR'] );

            if( $filter->isSpam() )
            {
                return true;
            }
        }
        return false;
    }

    public function ajaxIphoneInbox()
    {
        $objResponse	= new JAXResponse();
        $document		= JFactory::getDocument();

        $viewType	= $document->getType();
        $view		=  $this->getView( 'inbox', '', $viewType );


        $html = '';

        ob_start();
        $this->display();
        $content = ob_get_contents();
        ob_end_clean();

        $tmpl			= new CTemplate();
        $tmpl->set('toolbar_active', 'inbox');
        $simpleToolbar	= $tmpl->fetch('toolbar.simple');

        $objResponse->addAssign('social-content', 'innerHTML', $simpleToolbar . $content);
        return $objResponse->sendResponse();
    }

    public function display($cacheable=false, $urlparams=false) {
        $model	=  $this->getModel ( 'inbox' );
        $msg	=  $model->getInbox ();
        $modMsg	= array ();

        $view	=  $this->getView ( 'inbox' );
        $my		= CFactory::getUser ();

        if($my->id == 0)
        {
            return $this->blockUnregister();
        }

        // Add small avatar to each image
        if (! empty ( $msg ))
        {
            foreach ( $msg as $key => $val )
            {
                // based on the grouped message parent. check the unread message
                // count for this user.
                $filter ['parent'] = $val->parent;
                $filter ['user_id'] = $my->id;
                $unRead = $model->countUnRead ( $filter );
                $msg [$key]->unRead = $unRead;
            }
        }
        $data = new stdClass ( );
        $data->msg = $msg;

        $newFilter ['user_id'] = $my->id;
        $data->inbox = $model->countUnRead ( $newFilter );
        $data->pagination =  $model->getPagination ();
        echo $view->get ( 'inbox', $data );
    }

    /**
     * @todo: user should be loaded from library or other model
     */
    public function write()
    {
        CFactory::setActiveProfile ();
        $mainframe	= JFactory::getApplication();
        $jinput 	= $mainframe->input;
        $my		= CFactory::getUser ();
        $view	=  $this->getView ( 'inbox' );
        $data	= new stdClass ( );

        if($my->id == 0)
        {
            return $this->blockUnregister();
        }
        $inputFilter = CFactory::getInputFilter(true);

		$data->to			= $jinput->post->get('friends', array(), 'array');
		$data->subject		= $jinput->post->get('subject', '', 'STRING');
		$data->body			= $jinput->post->get('body', '', 'STRING');
		$data->photo        = $jinput->post->get('photo', '', 'INT');
        $data->sent			= 0;
        $model				=  $this->getModel ( 'user' );
        $actualTo			= array ();

        // are we saving ??
        if ($saving = $jinput->post->get ( 'action', '' , 'STRING'))
        {

            $appsLib		= CAppPlugins::getInstance();
            $saveSuccess	= $appsLib->triggerEvent( 'onFormSave' , array('jsform-inbox-write' ));

            if( empty($saveSuccess) || !in_array( false , $saveSuccess ) )
            {
                // @rule: Check if user exceeded limit
                $inboxModel		=  $this->getModel ( 'inbox' );
                $config			= CFactory::getConfig();
                $useRealName	= ($config->get('displayname') == 'name') ? true : false;

                $maxSent		= $config->get('pmperday');
                $totalSent		= $inboxModel->getTotalMessageSent( $my->id );

                if( $totalSent >=  $maxSent && $maxSent != 0 )
                {
                    $mainframe->redirect(CRoute::_('index.php?option=com_community&view=inbox' , false) , JText::_('COM_COMMUNITY_PM_LIMIT_REACHED'));
                }

                $validated = true;

                // @rule: Spam checks
                if( $this->_isSpam( $my , $data->subject . ' ' . $data->body ) )
                {
                    $view->addWarning( JText::_('COM_COMMUNITY_INBOX_MESSAGE_MARKED_SPAM') );
                    $validated	= false;
                }

                // Block users
                $getBlockStatus		= new blockUser();

                // Enable multiple recipients
                // @since 2.4
                $actualTo = $data->to;
                $actualTo = array_unique($actualTo);

                if ( !( count($actualTo) > 0 ) )
                {
                    $view->addWarning ( JText::_('COM_COMMUNITY_INBOX_RECEIVER_MISSING') );
                    $validated = false;
                }

                $tempUser = array();
                foreach ( $actualTo as $recepientId ) {
                    // Get name for error message show
                    $user = CFactory::getUser($recepientId);
                    $name = $user->getDisplayName();
                    $thumb = $user->getThumbAvatar();

                    if( $getBlockStatus->isUserBlocked($recepientId,'inbox') && !COwnerHelper::isCommunityAdmin() ){
                        $view->addWarning ( JText::_('COM_COMMUNITY_YOU_ARE_BLOCKED_BY_USER') . ' - ' . $name);
                        $validated = false;
                    }

                    // restrict user to send message to themselve
                    if( $my->id == $recepientId )
                    {
                        $mainframe->redirect(CRoute::_('index.php?option=com_community&view=inbox&task=write' , false) , JText::_('COM_COMMUNITY_INBOX_MESSAGE_CANNOT_SEND_TO_SELF') , 'error' );
                        return;
                    }

                    $tempUser[] = array('rid'=>$recepientId, 'avatar' => $thumb , 'name' => $name); //since 2.4, to keep track previous 'to' info
                }

                $data->toUsersInfo = $tempUser;

                if (empty ( $data->subject ))
                {
                    $view->addWarning ( JText::_('COM_COMMUNITY_INBOX_SUBJECT_MISSING') );
                    $validated = false;
                }

                $fileId = $jinput->post->get('file_id', 0, 'INT');

                if (empty ( $data->body ) && !$data->photo && !$fileId)
                {
                    $view->addWarning ( JText::_('COM_COMMUNITY_INBOX_MESSAGE_EMPTY') );
                    $validated = false;
                }

                // store message
                if ($validated)
                {
                    $model =  $this->getModel ( 'inbox' );

                    $msgData		= $jinput->post->getArray();
                    $msgData ['to'] = $actualTo;

                    $msgid = $model->send ( $msgData );
                    $data->sent = 1;


                    if($fileId){
                        //if there is a file attached to this new message, assign the file
                        $fileModel = CFactory::getModel('files');
                        $fileModel->updateMessageFile($msgid, $fileId);
                    }

                    //add user points
                    CUserPoints::assignPoint('inbox.message.send');

                    // Add notification
                    $params			= new CParameter( '' );
                    $params->set('url' , 'index.php?option=com_community&view=inbox&task=read&msgid='. $msgid );
                    $params->set( 'message' , $data->body );
                    $params->set( 'title'	, $data->subject );
                    $params->set('msg_url' , CRoute::_('index.php?option=com_community&view=inbox&task=read&msgid='. $msgid ));
                    $params->set('msg' , JText::_('COM_COMMUNITY_PRIVATE_MESSAGE'));

                    foreach ( $actualTo as $recepientId ) {
                        CNotificationLibrary::add( 'inbox_create_message' , $my->id , $recepientId , JText::sprintf('COM_COMMUNITY_SENT_YOU_MESSAGE') , '' , 'inbox.sent' , $params );
                    }

                    $mainframe->redirect(CRoute::_('index.php?option=com_community&view=inbox&task=read&msgid=' . $msgid , false) , JText::_('COM_COMMUNITY_INBOX_MESSAGE_SENT'));
                    return;
                }
            }
        }
        $inModel	=  $this->getModel ( 'inbox' );

        $newFilter ['user_id'] = $my->id;
        $data->inbox = $inModel->countUnRead ( $newFilter );
        $this->_icon = 'compose';
        echo $view->get ( 'write', $data );
    }

    /**
     * Remove the selected message
     */
    public function remove() {
        $mainframe	= JFactory::getApplication();
        $jinput 	= $mainframe->input;

        $msgId = $jinput->get->get('msgid', '', 'INT');
        $my = CFactory::getUser ();
        $view =  $this->getView ( 'inbox' );
        $model =  $this->getModel ( 'inbox' );

        if($my->id == 0)
        {
            return $this->blockUnregister();
        }

        if ($model->removeReceivedMsg ( $msgId, $my->id )) {
            $view->addInfo ( JText::_('COM_COMMUNITY_INBOX_MESSAGE_REMOVED' ) );
        } else {
            $view->addInfo ( JText::_('COM_COMMUNITY_INBOX_MESSAGE_FAILED_REMOVE' ));
        }
        $this->display ();
    }

    /**
     * View all sent emails
     */
    public function sent() {
        CFactory::setActiveProfile ();
        $model =  $this->getModel ( 'inbox' );
        $msg =  $model->getSent ();
        $modMsg = array ();

        $view =  $this->getView ( 'inbox' );

        // Add small avatar to each image
        $avatarModel =  $this->getModel ( 'avatar' );
        if (! empty ( $msg )) {
            foreach ( $msg as $key => $val ) {

                if (is_array ( $val->to )) { // multiuser


                    $tmpNameArr = array ();
                    $tmpAvatar = array ();

                    //avatar
                    foreach ( $val->to as $toId ) {
                        $user			= CFactory::getUser( $toId );
                        $tmpAvatar []	= $user->getThumbAvatar();
                        $tmpNameArr [] 	= $user->getDisplayName();
                    }

                    $msg [$key]->smallAvatar	= $tmpAvatar;
                    $msg [$key]->to_name 		= $tmpNameArr;
                }
            }
        }

        $data = new stdClass ( );
        $data->msg = $msg;

        $my = CFactory::getUser ();
        $newFilter ['user_id'] = $my->id;

        if($my->id == 0)
        {
            return $this->blockUnregister();
        }

        $data->inbox = $model->countUnRead ( $newFilter );
        $data->pagination =  $model->getPagination ();
        $this->_icon = 'sent';
        echo $view->get ( 'sent', $data );
    }

    /**
     * Open the message thread for reading
     */
    public function read() {
        //Load Link Generator Helpers
        $mainframe	= JFactory::getApplication();
        $jinput 	= $mainframe->input;

        $msgId = $jinput->request->get( 'msgid', '' , 'INT');
        $my = CFactory::getUser ();

        if($my->id == 0)
        {
            return $this->blockUnregister();
        }

        $filter = array ();

        $filter ['msgId'] = $msgId;
        $filter ['to'] = $my->id;

        $model =  $this->getModel ( 'inbox' );
        $view =  $this->getView ( 'inbox' );
        $data	= new stdClass();
        $data->messages = $model->getMessages ( $filter );

        // mark as "read"
        $filter ['parent'] = $msgId;
        $filter ['user_id'] = $my->id;
        $model->markMessageAsRead ( $filter );
        // ok done. display the messages.
        echo $view->get ( 'read', $data );

    }

    /**
     * Reply a message
     */
    public function reply() {

        $mainframe	= JFactory::getApplication();
        $jinput 	= $mainframe->input;

        $msgId = $jinput->request->get( 'msgid', '' ,'INT' );

        $my = CFactory::getUser ();
        $model =  $this->getModel ( 'inbox' );
        $view =  $this->getView ( 'inbox' );
        $allowReply = 1;

        if($my->id == 0)
        {
            return $this->blockUnregister();
        }

        $message = $model->getMessage ( $msgId );
        $messageRecipient = $model->getUserMessage ( $msgId );

        // make sure we can only reply to message that belogn to current user
        $myMsg = true;
        if (! empty ( $message )) {
            $myMsg = ($my->id == $message->from);
        }

        if (! empty ( $messageRecipient )) {
            $myMsg = ($my->id == $messageRecipient->to);
        }

        if (! $myMsg) {
            //show warning
            $view->addWarning ( 'COM_COMMUNITY_INBOX_NOT_ALLOWED_TO_REPLY_MESSAGE' );
            $allowReply = 0;
        }

        $cDate = JDate::getInstance (); //get the current date from system.
        $obj = new stdClass ( );
        $obj->id        = null;
        $obj->from      = $my->id;
        $obj->posted_on = $cDate->toSql ();
        $obj->from_name = $my->name;
        $obj->subject   = 'RE:' . $message->subject;
        $obj->body      = $jinput->post->get( 'body', '' , 'STRING');

        if ('doSubmit' == $jinput->post->get( 'action', '' )) {
            $model->sendReply ( $obj, $msgId );
            $view->addInfo ( JText::_('COM_COMMUNITY_INBOX_MESSAGE_SENT'));

            CUserPoints::assignPoint('inbox.message.reply');
        }

        $data = array ();
        $data ['reply_to'] = $message->from_name;
        $data ['allow_reply'] = $allowReply;

        echo $view->get ( 'reply', $data );
    }

    /**
     * Remove a message via ajax
     * A user can only remove a message that he can read/reply to.
     */
    public function ajaxRemoveFullMessages($msgIds){
        $JFilter = JFilterInput::getInstance();
        $msgIds = $JFilter->clean($msgIds, 'string');

        $objResponse = new JAXResponse ( );
        $json = array();

        $my 	= CFactory::getUser ();
        $view 	=  $this->getView ( 'inbox' );
        $model 	=  $this->getModel ( 'inbox' );

        if($my->id == 0)
        {
            return $this->ajaxBlockUnregister();
        }
        $msgList = explode(',',$msgIds);
        foreach ($msgList as $msgId) {
            $msgId = $JFilter->clean($msgId, 'int');
            $conv	= $model->getFullMessages($msgId);
            $delCnt = 0;
            $filter = array ();
            $parentId = $model->getParent ( $msgId );

            $filter ['msgId'] = $parentId;
            $filter ['to'] = $my->id;


            $data	= new stdClass();
            $data->messages = $model->getMessages ( $filter , true);

            $childCount = count($data->messages);

            if(! empty($conv))
            {
                foreach($conv as $msg)
                {
                    if($model->canReply($my->id, $msg->id)) {
                        if ($model->removeReceivedMsg ( $msg->id, $my->id )) {
                            $delCnt++;
                        }//end if
                    }//end if
                }//end foreach
            }//end if

        }

        $json['message'] = JText::_('COM_COMMUNITY_INBOX_MESSAGES_REMOVED');
        $json['success'] = true;

        die( json_encode($json) );
    }

    /**
     * Remove a sent message via ajax
     * A user can only remove a sent message that he can read/reply to.
     */
    public function ajaxRemoveSentMessages($msgIds){
        $JFilter = JFilterInput::getInstance();
        $msgIds = $JFilter->clean($msgIds, 'string');

        $objResponse = new JAXResponse ( );
        $json = array();

        $my 	= CFactory::getUser ();
        $view 	=  $this->getView ( 'inbox' );
        $model 	=  $this->getModel ( 'inbox' );

        if($my->id == 0)
        {
            return $this->ajaxBlockUnregister();
        }
        $msgList = explode(',',$msgIds);
        foreach ($msgList as $msgId) {
            $msgId = $JFilter->clean($msgId, 'int');

            $conv	= $model->getSentMessages($msgId);
            $delCnt = 0;

            if(! empty($conv))
            {
                foreach($conv as $msg)
                {
                    if($model->canReply($my->id, $msg->id)) {
                        if ($model->removeReceivedMsg ( $msg->id, $my->id )) {
                            $delCnt++;
                        }//end if
                    }//end if
                }//end foreach
            }//end if
        }

        $json['message'] = JText::_('COM_COMMUNITY_INBOX_MESSAGES_REMOVED');
        $json['success'] = true;

        die( json_encode($json) );
    }

    /**
     * Remove a message via ajax
     * A user can only remove a message that he can read/reply to.
     */
    public function ajaxRemoveMessage($msgId){
        $filter = JFilterInput::getInstance();
        $msgId = $filter->clean($msgId, 'int');

        $json  = array();
        $my    = CFactory::getUser ();
        $view  =  $this->getView ( 'inbox' );
        $model =  $this->getModel ( 'inbox' );

        if($my->id == 0)
        {
            return $this->ajaxBlockUnregister();
        }

        if($model->canReply($my->id, $msgId)) {
            if ($model->removeReceivedMsg ( $msgId, $my->id )) {
                $json['success'] = true;
            }
        } else {
            $json['error'] = JText::_('COM_COMMUNITY_PERMISSION_DENIED_WARNING');
        }

        die( json_encode($json) );
    }

    /**
     * To remove thumbnail preview
     * @param $msgId
     */
    public function ajaxRemoveThumbnail($msgId){
        $filter = JFilterInput::getInstance();
        $msgId = $filter->clean($msgId, 'int');

        $my = CFactory::getUser ();

        $messageTable = $photo = JTable::getInstance('Message', 'CTable');
        $messageTable->load($msgId);

        $params = new CParameter($messageTable->body);

        //if there is a photo attached
        if(!$params->get('content')) {
            $this->ajaxRemoveMessage($msgId);
        }else if($params->get('attached_photo_id')){
            //delete from db and files
            $photoModel = CFactory::getModel('photos');
            $photoTable = $photoModel->getPhoto($params->get('attached_photo_id'));

            if($photoTable->creator == $my->id){
                $photoTable->delete();

                $params->set('attached_photo_id' , 0);

                $messageTable->body = $params->toString();
                $messageTable->store();
            }

        }

        $deleteLink = CRoute::_('index.php?option=com_community&view=inbox&task=remove&msgid='.$messageTable->id);
        $authorLink	= CRoute::_('index.php?option=com_community&view=profile&userid=' . $messageTable->from );

        // Escape content
        $content = $originalContent = $params->get('content');
        $content = CTemplate::escape($content);
        $content = CStringHelper::autoLink($content);
        $content = nl2br($content);
        $content = CStringHelper::getEmoticon($content);
        $content = CStringHelper::converttagtolink($content);

        //get thumbnail if available
        $photoThumbnail = '';
        if ( $params->get('attached_photo_id') ) {
            $photo = JTable::getInstance('Photo', 'CTable');
            $photo->load($params->get('attached_photo_id'));
            $photoThumbnail = $photo->getThumbURI();
        }

        $tmpl = new CTemplate();
        $tmpl->set( 'user', CFactory::getUser($messageTable->from) );
        $tmpl->set( 'msg', $messageTable );
        $tmpl->set( 'originalContent', $originalContent );
        $tmpl->set( 'content', $content );
        $tmpl->set( 'params', $params );
        $tmpl->set( 'removeLink', $deleteLink );
        $tmpl->set( 'authorLink', $authorLink );
        $tmpl->set( 'photoThumbnail', $photoThumbnail );
        $html = $tmpl->fetch( 'inbox.message' );

        $json = array();
        $json['success'] = true;
        $json['html'] = $html;

        die( json_encode($json) );
    }

    /**
     * To remove url fetching
     * @param $msgId
     */
    public function ajaxRemovePreview($msgId){
        $filter = JFilterInput::getInstance();
        $msgId = $filter->clean($msgId, 'int');

        $my = CFactory::getUser ();

        $messageTable = $photo = JTable::getInstance('Message', 'CTable');
        $messageTable->load($msgId);

        $params = new CParameter($messageTable->body);

        //if there is a fetched content
        if($params->get('url')){
            $newParam = new CParameter();
            $newParam->set('content',$params->get('content'));
            $messageTable->body = $newParam->toString();
            $messageTable->store();
            $params = $newParam;
        }

        $deleteLink = CRoute::_('index.php?option=com_community&view=inbox&task=remove&msgid='.$messageTable->id);
        $authorLink	= CRoute::_('index.php?option=com_community&view=profile&userid=' . $messageTable->from );

        // Escape content
        $content = $originalContent = $params->get('content');
        $content = CTemplate::escape($content);
        $content = CStringHelper::autoLink($content);
        $content = nl2br($content);
        $content = CStringHelper::getEmoticon($content);
        $content = CStringHelper::converttagtolink($content);

        //get thumbnail if available
        $photoThumbnail = '';
        if ( $params->get('attached_photo_id') ) {
            $photo = JTable::getInstance('Photo', 'CTable');
            $photo->load($params->get('attached_photo_id'));
            $photoThumbnail = $photo->getThumbURI();
        }

        $tmpl = new CTemplate();
        $tmpl->set( 'user', CFactory::getUser($messageTable->from) );
        $tmpl->set( 'msg', $messageTable );
        $tmpl->set( 'originalContent', $originalContent );
        $tmpl->set( 'content', $content );
        $tmpl->set( 'params', $params );
        $tmpl->set( 'removeLink', $deleteLink );
        $tmpl->set( 'authorLink', $authorLink );
        $tmpl->set( 'photoThumbnail', $photoThumbnail );
        $html = $tmpl->fetch( 'inbox.message' );

        $json = array();
        $json['success'] = true;
        $json['html'] = $html;

        die( json_encode($json) );
    }

    /**
     * Add reply via ajax
     * @todo: check permission and message ownership
     */
    public function ajaxAddReply($msgId, $reply, $photoId = 0, $fileId = 0)
    {
        $filter = JFilterInput::getInstance();
        $msgId = $filter->clean($msgId, 'int');
        //$reply = $filter->clean($reply, 'string');
        $photoId = $filter->clean($photoId, 'int');
        $fileId = $filter->clean($fileId, 'int');

        $my = CFactory::getUser ();
        $model =  $this->getModel ( 'inbox' );
        $message = $model->getMessage ( $msgId );
        $messageRecipient = $model->getParticipantsID ( $msgId , $my->id);

        if($my->id == 0)
        {
            return $this->ajaxBlockUnregister();
        }

        // Block users
        $getBlockStatus		= new blockUser();
        $userModel = CFactory::getModel('User');
        $bannedList = $userModel->getBannedUser();

        $newRecipient = array();
        foreach($messageRecipient as $recipient) {

            if ($getBlockStatus->isUserBlocked($recipient, 'inbox')) {
                continue;
            }

            $newRecipient[] = $recipient;
        }

        $messageRecipient = $newRecipient;

        // @rule: Spam checks
        if( $this->_isSpam( $my , $reply ) )
        {
            $json = array( 'error' => JText::_('COM_COMMUNITY_INBOX_MESSAGE_MARKED_SPAM') );
            die( json_encode($json) );
        }

        if (empty($reply) && $photoId == 0)
        {
            $json = array( 'error' => JText::_('COM_COMMUNITY_INBOX_MESSAGE_CANNOT_BE_EMPTY') );
            die( json_encode($json) );
        }

        if ( empty ( $messageRecipient ))
        {
            $json = array( 'error' => JText::_('COM_COMMUNITY_INBOX_MESSAGE_CANNOT_FIND_RECIPIENT') );
            die( json_encode($json) );
        }

        // make sure we can only reply to message that belogn to current user
        if ( !$model->canReply($my->id, $msgId) )
        {
            $json = array( 'error' => JText::_('COM_COMMUNITY_PERMISSION_DENIED_WARNING') );
            die( json_encode($json) );
        }

        if(in_array($messageRecipient[0],$bannedList)){
            $json = array( 'error' => JText::_('COM_COMMUNITY_USER_BANNED') );
            die( json_encode($json) );
        }

        $date	= JDate::getInstance(); //get the time without any offset!


        $obj 			= new stdClass ( );
        $obj->id		= null;
        $obj->from 		= $my->id;
        $obj->posted_on = $date->toSql();
        $obj->from_name = $my->name;
        $obj->subject 	= 'RE:' . $message->subject;
        $obj->body 		= $reply;

        $body = new JRegistry();
        $body->set( 'content', $obj->body );

        if($fileId){
            //add the file id
            $body->set('file_id',$fileId);
        }

        // photo attachment
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

        $replyId = $model->sendReply ( $obj, $msgId );

        if($fileId){
            //lets add the reference to the file
            $fileTable = JTable::getInstance('File','CTable');
            $fileTable->load($fileId);
            $fileTable->messageid = $replyId;
            $fileTable->store();
        }

        $deleteLink = CRoute::_('index.php?option=com_community&view=inbox&task=remove&msgid='.$obj->id);
        $authorLink	= CRoute::_('index.php?option=com_community&view=profile&userid=' . $my->id );

        //add user points
        CUserPoints::assignPoint('inbox.message.reply');

        // Add notification
        foreach($messageRecipient as $row)
        {
            $params = new CParameter( '' );
            $params->set( 'message' , $reply );
            $params->set( 'title'	, $obj->subject );
            $params->set('url' , 'index.php?option=com_community&view=inbox&task=read&msgid='. $msgId );
            $params->set('msg_url' , 'index.php?option=com_community&view=inbox&task=read&msgid='. $msgId );
            $params->set('msg' , JText::_('COM_COMMUNITY_PRIVATE_MESSAGE'));
            $params->set('msg' , JText::_('COM_COMMUNITY_PRIVATE_MESSAGE'));

            CNotificationLibrary::add( 'inbox_create_message' , $my->id , $row , JText::_('COM_COMMUNITY_SENT_YOU_MESSAGE') , '' , 'inbox.sent' , $params );
        }

        // onMessageDisplay Event trigger
        $appsLib = CAppPlugins::getInstance();
        $appsLib->loadApplications();
        $args = array();
        $args[] = $obj;
        $appsLib->triggerEvent( 'onMessageDisplay' , $args );

        $params = new JRegistry($obj->body);

        // Escape content
        $content = $originalContent = $params->get('content');

        $content = CTemplate::escape($content);
        $content = CStringHelper::autoLink($content);
        $content = nl2br($content);
        $content = CStringHelper::getEmoticon($content);
        $content = CStringHelper::converttagtolink($content);
        $content = CUserHelper::replaceAliasURL($content);

        $tmpl = new CTemplate();

        //get thumbnail if available
        $photoThumbnail = '';
        if ( $params->get('attached_photo_id') ) {
            $photo = JTable::getInstance('Photo', 'CTable');
            $photo->load($params->get('attached_photo_id'));
            $photoThumbnail = $photo->getThumbURI();
        }

        if( $params->get('file_id') ){
            $file = JTable::getInstance('File','CTable');
            $file->load($params->get('file_id'));
            $tmpl->set('file',$file);
        }


        $tmpl->set( 'user', CFactory::getUser($obj->from) );
        $tmpl->set( 'msg', $obj );
        $tmpl->set( 'originalContent', $originalContent );
        $tmpl->set( 'content', $content );
        $tmpl->set( 'params', $params );
        $tmpl->set( 'removeLink', $deleteLink );
        $tmpl->set( 'authorLink', $authorLink );
        $tmpl->set( 'photoThumbnail', $photoThumbnail );
        $html = $tmpl->fetch( 'inbox.message' );

        $json = array(
            'success' => true,
            'html' => $html
        );

        die( json_encode($json) );
    }

    /**
     * @todo: check permission and message ownership
     */
    public function ajaxCompose($id) {
        $filter = JFilterInput::getInstance();
        $id = $filter->clean($id, 'int');

        if (!COwnerHelper::isRegisteredUser()) {
            return $this->ajaxBlockUnregister();
        }

        $objResponse = new JAXResponse ( );
        $config = CFactory::getConfig();
        $user 	= CFactory::getUser($id);
        $my 	= CFactory::getUser();

        if($my->id == 0)
        {
            return $this->ajaxBlockUnregister();
        }

        //CFactory::load( 'helpers' , 'owner' );
        //CFactory::load( 'libraries' , 'block' );
        $getBlockStatus		= new blockUser();

        // Block banned users
        if( $getBlockStatus->isUserBlocked($id,'inbox') && !COwnerHelper::isCommunityAdmin() ){
            $this->ajaxblock();
        }

        $inboxModel = $this->getModel('inbox');
        $lastSent   = $inboxModel->getLastSentTime($my->id);
        $dateNow    = new JDate();

        // We need to make sure that this guy are not spamming other people inbox
        // by checking against his last message time. Make sure it doesn't exceed
        // pmFloodLimit config (in seconds).
        if ( ($dateNow->toUnix() - $lastSent->toUnix()) < $config->get('floodLimit') && !COwnerHelper::isCommunityAdmin() ) {
            $json = array();
            $json['title'] = JText::_('COM_COMMUNITY_NOTICE');
            $json['error'] = JText::sprintf('COM_COMMUNITY_PLEASE_WAIT_BEFORE_SENDING_MESSAGE', $config->get('floodLimit'));
            die( json_encode($json) );
        }

        // Check if user exceeded daily limit.
        $maxSent   = $config->get('pmperday');
        $totalSent = $inboxModel->getTotalMessageSent($my->id);

        if ($totalSent >= $maxSent && $maxSent != 0) {
            $json = array();
            $json['title'] = JText::_('COM_COMMUNITY_NOTICE');
            $json['error'] = JText::_('COM_COMMUNITY_PM_LIMIT_REACHED');
            die( json_encode($json) );
        }

        //====================================

        $tmpl = new CTemplate();
        $tmpl->set('user', $user);

        $json = array(
            'title'     => JText::_('COM_COMMUNITY_INBOX_TITLE_WRITE'),
            'html'      => $tmpl->fetch('inbox.ajaxcompose'),
            'btnSend'   => JText::_('COM_COMMUNITY_SEND_BUTTON'),
            'btnCancel' => JText::_('COM_COMMUNITY_CANCEL_BUTTON')
        );

        die( json_encode($json) );
    }

    /**
     * A new message submitted via ajax
     */
    public function ajaxSend($postVars)
    {
        //$postVars pending filtering
        $objResponse	= new JAXResponse ( );
        $config			= CFactory::getConfig();
        $my				= CFactory::getUser ();

        if($my->id == 0)
        {
            return $this->ajaxBlockUnregister();
        }

        //CFactory::load( 'helpers', 'time' );

        $inboxModel = $this->getModel('inbox');
        $lastSent   = $inboxModel->getLastSentTime($my->id);
        $dateNow    = new JDate();

        // We need to make sure that this guy are not spamming other people inbox
        // by checking against his last message time. Make sure it doesn't exceed
        // pmFloodLimit config (in seconds).
        if ( ($dateNow->toUnix() - $lastSent->toUnix()) < $config->get('floodLimit') && !COwnerHelper::isCommunityAdmin() ) {
            $json = array();
            $json['title'] = JText::_('COM_COMMUNITY_NOTICE');
            $json['error'] = JText::sprintf('COM_COMMUNITY_PLEASE_WAIT_BEFORE_SENDING_MESSAGE', $config->get('floodLimit'));
            die( json_encode($json) );
        }

        // Prevent users to send message to themselves.
        if ($postVars['to'] == $my->id) {
            $json = array();
            $json['title'] = JText::_('COM_COMMUNITY_NOTICE');
            $json['error'] = JText::_('COM_COMMUNITY_INBOX_MESSAGE_CANNOT_SEND_TO_SELF');
            die( json_encode($json) );
        }

        $postVars = CAjaxHelper::toArray ( $postVars );
        $doCont   = true;
        $errMsg   = "";
        $resizeH  = 0;

        if ( $this->_isSpam($my, $postVars['subject'] . ' ' . $postVars['body']) ) {
            $json = array();
            $json['title'] = JText::_('COM_COMMUNITY_NOTICE');
            $json['error'] = JText::_('COM_COMMUNITY_INBOX_MESSAGE_MARKED_SPAM');
            die( json_encode($json) );
        }

        if ( empty($postVars['subject']) || JString::trim($postVars['subject']) == '' ) {
            $json = array();
            $json['title']    = JText::_('COM_COMMUNITY_INBOX_TITLE_WRITE');
            $json['error']    = JText::_('COM_COMMUNITY_INBOX_SUBJECT_MISSING');
            $json['samestep'] = true;
            die( json_encode($json) );
        }

        if ( ( empty($postVars['body']) || JString::trim($postVars['body']) == '' ) && !isset($postVars['photo']) && !isset($postVars['file_id']) ) {
            $json = array();
            $json['title']    = JText::_('COM_COMMUNITY_INBOX_TITLE_WRITE');
            $json['error']    = JText::_('COM_COMMUNITY_INBOX_MESSAGE_MISSING');
            $json['samestep'] = true;
            die( json_encode($json) );
        }

        $fileId = (isset($postVars['file_id']) && $postVars['file_id']) ? $postVars['file_id'] : 0;

        $data          = $postVars;
        $model         = $this->getModel('inbox');
        $pattern       = "/<br \/>/i";
        $replacement   = "\r\n";
        $data['body']  = preg_replace($pattern, $replacement, $data['body']);
        $data['photo'] = isset($data['photo']) ? $data['photo'] : '';
        $data['file_id'] = $fileId;
        $msgid         = $model->send($data);

        if($fileId){
            //if there is a file attached to this new message, assign the file
            $fileModel = CFactory::getModel('files');
            $fileModel->updateMessageFile($msgid, $fileId);
        }

        // Add user points.
        CUserPoints::assignPoint('inbox.message.send');

        // Add notification.
        $params = new CParameter('');
        $params->set('url', 'index.php?option=com_community&view=inbox&task=read&msgid=' . $msgid);
        $params->set('message', $data['body']);
        $params->set('title', $data['subject']);
        $params->set('msg_url', 'index.php?option=com_community&view=inbox&task=read&msgid=' . $msgid);
        $params->set('msg', JText::_('COM_COMMUNITY_PRIVATE_MESSAGE'));

        CNotificationLibrary::add('inbox_create_message', $my->id, $data['to'], JText::sprintf('COM_COMMUNITY_SENT_YOU_MESSAGE'), '', 'inbox.sent', $params);

        // Send response.
        $json = array();
        $json['message'] = JText::_('COM_COMMUNITY_INBOX_MESSAGE_SENT');
        die( json_encode($json) );
    }

    /**
     * @todo: need to filter this down. loading too many user at once
     */
    public function ajaxAutoName() {
        $my 			= CFactory::getUser();
        $config			= CFactory::getConfig();
        $displayName	= $config->get('displayname');
        $mainframe	= JFactory::getApplication();
        $jinput 	= $mainframe->input;

        $search_term = $jinput->get->get( 'q', '', 'STRING' );

        if($my->id == 0)
        {
            return $this->ajaxBlockUnregister();
        }

        $model =  $this->getModel ( 'user' );
        $friendsModel =  $this->getModel ( 'friends' );

        $friends =  $friendsModel->getFriends($my->id,'',false);

        $names = "";

        foreach( $friends as $row ){
            $cur_name = '';
            if($config->get('displayname') == 'name'){
                $cur_name = $row->name;
            }else{
                $cur_name = $row->username;
            }

            $filter = strrpos(strtolower($cur_name), strtolower($search_term));

            if ($filter !== false) {
                $user 	= CFactory::getUser( $row->id );
                $avatar = $user->getThumbAvatar();
                $names .= $cur_name."|".$row->id."|".$avatar."\n";
            }
        }

        echo $names;
        exit ();
    }


    /**
     * Set message as Read
     */
    public function ajaxMarkMessageAsRead($msgId){
        $filter = JFilterInput::getInstance();
        $msgId = $filter->clean($msgId, 'int');

        $objResponse = new JAXResponse ( );
        $my 	= CFactory::getUser ();
        $view 	=  $this->getView ( 'inbox' );
        $model 	=  $this->getModel ( 'inbox' );

        if($my->id == 0)
        {
            return $this->ajaxBlockUnregister();
        }

        $filter = array(
            'parent'    => $msgId,
            'user_id'   => $my->id
        );

        $model->markAsRead( $filter );
        $objResponse->addScriptCall ( 'markAsRead', $msgId );
        $objResponse->sendResponse ();
    }



    /**
     * Set message as Read
     */
    public function ajaxMarkMessageAsUnread($msgId)
    {
        $filter = JFilterInput::getInstance();
        $msgId = $filter->clean($msgId, 'int');

        $objResponse = new JAXResponse ( );
        $my 	= CFactory::getUser ();
        $view 	=  $this->getView ( 'inbox' );
        $model 	=  $this->getModel ( 'inbox' );

        if($my->id == 0)
        {
            return $this->ajaxBlockUnregister();
        }

        $filter = array(
            'parent'    => $msgId,
            'user_id'   => $my->id
        );

        $model->markAsUnread( $filter );
        $objResponse->addScriptCall ( 'markAsUnread', $msgId );
        $objResponse->sendResponse ();
    }


    public function markUnread()
    {
        $mainframe	= JFactory::getApplication();
        $jinput 	= $mainframe->input;
        $my 		= CFactory::getUser ();
        $model		=  $this->getModel ( 'inbox' );

        if($my->id == 0)
        {
            return $this->blockUnregister();
        }

        $msgId 	= $jinput->request->get( 'msgid', '' , 'INT');

        if(empty($msgId))
        {
            $mainframe->redirect(CRoute::_('index.php?option=com_community&view=inbox', false), JText::_('COM_COMMUNITY_INBOX_MARK_UNREAD_FAILED'), 'error');
        }
        else
        {
            $filter = array(
                'parent'    => $msgId,
                'user_id'   => $my->id
            );

            $model->markMessageAsUnread( $filter );
            $mainframe->redirect(CRoute::_('index.php?option=com_community&view=inbox', false), JText::_('COM_COMMUNITY_INBOX_MARK_UNREAD_SUCCESS'));
        }
    }

    public function ajaxDeleteMessages($task, $empty="")
    {
        $my   =	CFactory::getUser();
        $json = array();

        if ($my->id == 0) {
            return $this->ajaxBlockUnregister();
        }

        $json['title'] = JText::_('COM_COMMUNITY_DELETE_MESSAGE');

        if (!$empty) {
            $json['html']   = JText::_('COM_COMMUNITY_INBOX_REMOVE_CONFIRM');
            $json['btnYes'] = JText::_('COM_COMMUNITY_YES');
            $json['btnNo']  = JText::_('COM_COMMUNITY_NO');
        } else {
            $json['error'] = JText::_('COM_COMMUNITY_INBOX_REMOVE_CONFIRM_NO_MESSAGE_CHECKED');
        }

        die( json_encode($json) );
    }

}
