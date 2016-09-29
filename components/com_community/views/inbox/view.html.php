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
defined('_JEXEC') or die();

jimport('joomla.application.component.view');
jimport('joomla.utilities.arrayhelper');

class CommunityViewInbox extends CommunityView
{

    public function _addSubmenu()
    {
        $this->addSubmenuItem('index.php?option=com_community&view=inbox', JText::_('COM_COMMUNITY_INBOX'));
        $this->addSubmenuItem(
            'index.php?option=com_community&view=inbox&task=sent',
            JText::_('COM_COMMUNITY_INBOX_SENT')
        );

        $mainframe = JFactory::getApplication();
        $jinput = $mainframe->input;
        $task = $jinput->request->get('task', '');

        if (!empty($task) && $task == 'read') {
            $msgid = $jinput->request->get('msgid', '', 'INT'); //Request::getVar('msgid' , '' , 'REQUEST');
            $this->addSubmenuItem(
                'index.php?option=com_community&view=inbox&task=markUnread&msgid=' . $msgid,
                JText::_('COM_COMMUNITY_INBOX_MARK_UNREAD'),
                '',
                true
            );
            $this->addSubmenuItem('index.php?option=com_community&view=inbox&task=write', JText::_('COM_COMMUNITY_NEW_MESSAGE'));
        }
    }

    public function showSubmenu($display=true)
    {
        $this->_addSubmenu();
       return parent::showSubmenu($display);
    }

    public function display($tpl = null)
    {
        $this->inbox();
    }

    public function inbox($data)
    {
        if (!$this->accessAllowed('registered')) {
            return;
        }

        $mainframe = JFactory::getApplication();
        $my = CFactory::getUser();
        $config = CFactory::getConfig();
        if (!$config->get('enablepm')) {
            echo JText::_('COM_COMMUNITY_PRIVATE_MESSAGING_DISABLED');
            return;
        }

        //page title
        $this->addPathway(JText::_('COM_COMMUNITY_INBOX_TITLE'));

        $inboxModel = CFactory::getModel('inbox');

        $document = JFactory::getDocument();
        $document->setTitle(JText::_('COM_COMMUNITY_INBOX_TITLE'));

        if (!empty($data->msg)) {
            for ($i = 0; $i < count($data->msg); $i++) {
                $row = $data->msg[$i];
                $user = CFactory::getUser($row->from);
                $row->user = $user;
                $row->avatar = $user->getThumbAvatar();
                $row->isUnread = ($row->unRead > 0) ? true : false;
                $row->from_name = $user->getDisplayName();
            }
        }

        $tmpl = new CTemplate();
        echo $tmpl
            ->set('totalMessages', $inboxModel->getUserInboxCount())
            ->set('messages', $data->msg)
            ->set('pagination', $data->pagination->getPagesLinks())
            ->set('submenu', $this->showSubmenu(false))
            ->fetch('inbox.list');
    }


    public function sent($data)
    {
        if (!$this->accessAllowed('registered')) {
            return;
        }

        $mainframe = JFactory::getApplication();
        $my = CFactory::getUser();
        $config = CFactory::getConfig();

        if (!$config->get('enablepm')) {
            echo JText::_('COM_COMMUNITY_PRIVATE_MESSAGING_DISABLED');
            return;
        }

        //page title
        $pathway = $mainframe->getPathway();

        $pathway->addItem(
            JText::_('COM_COMMUNITY_INBOX_TITLE'),
            CRoute::_('index.php?option=com_community&view=inbox')
        );
        $pathway->addItem(JText::_('COM_COMMUNITY_INBOX_SENT_MESSAGES_TITLE'), '');

        $document = JFactory::getDocument();
        $document->setTitle(JText::_('COM_COMMUNITY_INBOX_SENT_MESSAGES_TITLE'));

        $appsLib = CAppPlugins::getInstance();
        $appsLib->loadApplications();

        if ( !empty($data->msg) ) {
            for ($i = 0; $i < count($data->msg); $i++) {
                $row = $data->msg[$i];

                // onMessageDisplay Event trigger
                $args = array();
                $args[] = $row;
                $appsLib->triggerEvent('onMessageDisplay', $args);

                $user = CFactory::getUser($row->from);
                $row->user = $user;
                $row->from_name = $user->getDisplayName();
                $row->avatar = $user->getThumbAvatar();
                $row->isUnread = false; // for sent item, always set to false.
            }
        }

        $tmpl = new CTemplate();
        echo $tmpl
            ->set('messages', $data->msg)
            ->set('pagination', $data->pagination->getPagesLinks())
            ->set('submenu', $this->showSubmenu(false))
            ->fetch('inbox.list');
    }


    public function write($data)
    {
        if (!$this->accessAllowed('registered')) {
            return;
        }

        $mainframe = JFactory::getApplication();
        $my = CFactory::getUser();
        $config = CFactory::getConfig();

        if (!$config->get('enablepm')) {
            echo JText::_('COM_COMMUNITY_PRIVATE_MESSAGING_DISABLED');
            return;
        }
        //page title
        $pathway = $mainframe->getPathway();

        $pathway->addItem(
            JText::_('COM_COMMUNITY_INBOX_TITLE'),
            CRoute::_('index.php?option=com_community&view=inbox')
        );
        $pathway->addItem(JText::_('COM_COMMUNITY_INBOX_TITLE_WRITE'), '');

        $document = JFactory::getDocument();
        $document->setTitle(JText::_('COM_COMMUNITY_INBOX_TITLE_WRITE'));

        $autoCLink = CRoute::_(
            JURI::base() . ('index.php?option=com_community&view=inbox&task=ajaxAutoName&no_html=1&tmpl=component')
        );

        if ($data->sent) {
            return;
        }

        $inboxModel = CFactory::getModel('inbox');
        $totalSent = $inboxModel->getTotalMessageSent($my->id);


        //CFactory::load( 'libraries' , 'apps' );
        $app = CAppPlugins::getInstance();
        $appFields = $app->triggerEvent('onFormDisplay', array('jsform-inbox-write'));
        $beforeFormDisplay = CFormElement::renderElements($appFields, 'before');
        $afterFormDisplay = CFormElement::renderElements($appFields, 'after');

        $tmpl = new CTemplate();
        echo $tmpl->set('beforeFormDisplay', $beforeFormDisplay)
            ->set('afterFormDisplay', $afterFormDisplay)
            ->set('autoCLink', $autoCLink)
            ->set('data', $data)
            ->set('totalSent', $totalSent)
            ->set('maxSent', $config->get('pmperday'))
            ->set('useRealName', ($config->get('displayname') == 'name') ? '1' : '0')
            ->set('friendsCount', $my->getFriendCount())
            ->set('submenu', $this->showSubmenu(false))
            ->fetch('inbox.write');
    }

    public function reply($data)
    {
        $mainframe = JFactory::getApplication();

        //page title
        $document = JFactory::getDocument();
        $document->setTitle(JText::_('COM_COMMUNITY_INBOX_TITLE_REPLY'));

        ?>
        <fieldset>
            <form name="writeMessageForm" id="writeMessageForm" action="" method="POST">
                <input type="hidden" name="subject" value="RE :">

                <p>
                    Reply to: <?php echo $data['reply_to']; ?>
                </p>

                <div>
                    <label style="text-align:top;"><?php echo JText::_('COM_COMMUNITY_MESSAGE'); ?> :</label>
                    <textarea name="body"></textarea>
                </div>

                <div>
                    <?php if ($data['allow_reply']) { ?>
                        <input type="hidden" name="action" value="doSubmit"/>
                        <input type="submit" value="<?php echo JText::_('COM_COMMUNITY_SUBMIT_BUTTON'); ?>"/>
                    <?php }//end if ?>
                    <button name="cancel" onclick="javascript: history.go(-1); return false;"><?php echo JText::_(
                            'COM_COMMUNITY_CANCEL_BUTTON'
                        ); ?></button>
                </div>
            </form>
        </fieldset>
    <?php
    }

    /**
     * Show the message reading window
     */
    public function read($data)
    {
        $mainframe = JFactory::getApplication();
        $jinput = $mainframe->input;
        if (!$this->accessAllowed('registered')) {
            return;
        }
        $config = CFactory::getConfig();
        if (!$config->get('enablepm')) {
            echo JText::_('COM_COMMUNITY_PRIVATE_MESSAGING_DISABLED');
            return;
        }

        //page title
        $document = JFactory::getDocument();

        $inboxModel = CFactory::getModel('inbox');
        $my = CFactory::getUser();
        $msgid = $jinput->request->get('msgid', 0, 'INT');

        if (!$inboxModel->canRead($my->id, $msgid)) {
            $mainframe->enqueueMessage(JText::_('COM_COMMUNITY_PERMISSION_DENIED_WARNING'), 'error');
            return;
        }

        $pathway = $mainframe->getPathway();
        $pathway->addItem(
            $this->escape(JText::_('COM_COMMUNITY_INBOX_TITLE')),
            CRoute::_('index.php?option=com_community&view=inbox')
        );

        $parentData = '';
        $html = '';
        $messageHeading = '';
        $recipient = array();
        $parentData = $inboxModel->getMessage($msgid);
        $messageIds = array(); // all the message under this inbox

        if (!empty($data->messages)) {
            $document = JFactory::getDocument();

            $pathway->addItem($this->escape(htmlspecialchars_decode($parentData->subject)));
            $document->setTitle(htmlspecialchars_decode($parentData->subject));

            require_once(COMMUNITY_COM_PATH . '/libraries/apps.php');
            $appsLib = CAppPlugins::getInstance();
            $appsLib->loadApplications();

            $config = CFactory::getConfig();
            $pagination = intval($config->get('stream_default_comments', 5));
            $count=count($data->messages);
            $hide = true;
            foreach ($data->messages as $row) {
                $count--;
                $messageIds[] = $row->id;
                if($count<$pagination) $hide = false;

                // onMessageDisplay Event trigger
                $args = array();
                $originalBodyContent = $row->body;

                $row->body = new JRegistry($row->body);

                if ($row->body == '{}') {
                    //backward compatibility, save the old data into content parameter if needed
                    $newParam = new CParameter();
                    $newParam->set('content', $originalBodyContent);
                    $table = JTable::getInstance('Message', 'CTable');
                    $table->load($row->id);
                    $table->body = $newParam->toString();
                    $table->store();
                    $row->body = new CParameter($table->body);
                }

                // Escape content
                $content = $originalContent = $row->body->get('content');
                $content = CTemplate::escape($content);
                $content = CStringHelper::autoLink($content);
                $content = nl2br($content);
                $content = CStringHelper::getEmoticon($content);
                $content = CStringHelper::converttagtolink($content);
                $content = CUserHelper::replaceAliasURL($content);

                $params = $row->body;

                $args[] = $row;
                $appsLib->triggerEvent('onMessageDisplay', $args);
                $user = CFactory::getUser($row->from);

                //construct the delete link
                $deleteLink = CRoute::_('index.php?option=com_community&view=inbox&task=remove&msgid=' . $row->id);
                $authorLink = CRoute::_('index.php?option=com_community&view=profile&userid=' . $user->id);

                $tmpl = new CTemplate();

                //get thumbnail if available
                $photoThumbnail = '';
                if ($params->get('attached_photo_id')) {
                    $photo = JTable::getInstance('Photo', 'CTable');
                    $photo->load($params->get('attached_photo_id'));
                    $photoThumbnail = $photo->getThumbURI();
                }elseif($params->get('file_id')){
                    $file = JTable::getInstance('File', 'CTable');
                    $file->load($params->get('file_id'));
                    $tmpl->set('file', $file);
                }

                $html .= $tmpl->set('user', $user)
                    ->set('msg', $row)
                    ->set('hide', $hide)
                    ->set('originalContent', $originalContent)
                    ->set('content', $content)
                    ->set('params', $params)
                    ->set('isMine', COwnerHelper::isMine($my->id, $user->id))
                    ->set('removeLink', $deleteLink)
                    ->set('authorLink', $authorLink)
                    ->set('photoThumbnail', $photoThumbnail)
                    ->fetch('inbox.message');
            }

            $myLink = CRoute::_('index.php?option=com_community&view=profile&userid=' . $my->id);

            $recipient = $inboxModel->getRecepientMessage($msgid);
            $recepientCount = count($recipient);
            $textOther = $recepientCount > 1 ? 'COM_COMMUNITY_MSG_OTHER' : 'COM_COMMUNITY_MSG_OTHER_SINGULAR';

            $messageHeading = JText::sprintf(
                'COM_COMMUNITY_MSG_BETWEEN_YOU_AND_USER',
                $myLink,
                '#',
                JText::sprintf($textOther, $recepientCount)
            );
        } else {
            $html = '<div class="text">' . JText::_('COM_COMMUNITY_INBOX_MESSAGE_EMPTY') . '</div>';

        }
        //end if

        $model  = CFactory::getModel( 'files' );
        $files = $model->getMessageFiles($messageIds);

        $tmplMain = new CTemplate();
        echo $tmplMain->set('messageHeading', $messageHeading)
            ->set('recipient', $recipient)
            ->set('limit', $pagination)
            ->set('messages', $data->messages)
            ->set('parentData', $parentData)
            ->set('htmlContent', $html)
            ->set('my', $my)
            ->set('files',$files)
            ->set('submenu', $this->showSubmenu(false))
            ->fetch('inbox.read');

    }

    //end messages

    public function successPage()
    {

        //page title
        $document = JFactory::getDocument();
        $document->setTitle(JText::_('COM_COMMUNITY_INBOX_TITLE_WRITE'));


        $msg = JText::_('COM_COMMUNITY_INBOX_MESSAGE_SENT');

        ?>
        <div>
            <div class="text"><?php echo $msg ?></div>
        </div>
        <form>
            <input type="hidden" name="option" value="com_community">
            <input type="hidden" name="view" value="inbox">
            <input type="hidden" name="task" value="write">

            <div>
                <input type="submit" class="button" value="<?php echo JText::_('COM_COMMUNITY_DONE_BUTTON'); ?>"/>
            </div>
        </form>
    <?php
    }
}
