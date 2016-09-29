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

jimport('joomla.user.helper');

class CommunityConnectController extends CommunityBaseController {

    private $_facebook = null;

    /**
     * Constructor
     * @param type $config
     */
    public function __construct($config = array()) {
        parent::__construct($config);
        $this->_facebook = new CFacebook;
    }

    public function test() {
        //CFactory::load( 'libraries' , 'facebook' );
        $facebook = new CFacebook();

        $facebook->hasPermission('user_posts');

        //$facebook->setStatus( 'hello world again from Jomsocial API' );
    }

    /**
     * 	Validates an existing user account.
     * 	If their user / password combination is valid, import facebook data / profile into their account
     * */
    public function ajaxValidateLogin($username, $password) {
        //CFactory::load( 'libraries' , 'facebook' );

        $filter = JFilterInput::getInstance();
        $username = $filter->clean($username, 'string');
        $password = $filter->clean($password, 'string');

        $response = new JAXResponse();
        $mainframe = JFactory::getApplication();

        $login = $mainframe->login(array('username' => $username, 'password' => $password));

        /* Login success */
        if ($login === true) {
            $connectId = $this->_getFacebookUID();

            $my = CFactory::getUser();
            $connectTable = JTable::getInstance('Connect', 'CTable');
            $connectTable->load($connectId);
            //CFactory::load( 'helpers' , 'owner' );
            // Only allow linking for normal users.
            if (!COwnerHelper::isCommunityAdmin()) {
                // Update page token since the userid is changed now.
                $session = JFactory::getSession();
                $token = $session->getFormToken(false);

                $response->addScriptCall('jax_token_var="' . $token . '";');

                if (!$connectTable->userid) {
                    $connectTable->connectid = $connectId;
                    $connectTable->userid = $my->id;
                    $connectTable->type = 'facebook';
                    $connectTable->store();
                    $response->addScriptCall('joms.api.fbcUpdate();');
                    return $response->sendResponse();
                }
            } else {
                /* Do not link with Administrator */
                $mainframe->logout();

                $tmpl = new CTemplate();
                $content = $tmpl->fetch('facebook.link.notallowed');
                $actions = '<input type="button" value="' . JText::_('COM_COMMUNITY_BACK_BUTTON') . '" class="btn" onclick="joms.api.fbcUpdate();" />';
                $response->addScriptCall('joms.jQuery("#cwin_logo").html("' . JText::_('COM_COMMUNITY_ACCOUNT_MERGE_TITLE') . '");');
                $response->addScriptCall('cWindowAddContent', $content, $actions);

                return $response->sendResponse();
            }
        }

        $tmpl = new CTemplate();
        $tmpl->set('login', $login);
        $content = $tmpl->fetch('facebook.link.failed');
        $actions = '<input type="button" value="' . JText::_('COM_COMMUNITY_BACK_BUTTON') . '" class="btn" onclick="jax.call(\'community\',\'connect,ajaxShowExistingUserForm\');" />';
        $response->addScriptCall('cWindowResize', 150);
        $response->addScriptCall('joms.jQuery("#cwin_logo").html("' . JText::_('COM_COMMUNITY_ACCOUNT_MERGE_TITLE') . '");');
        $response->addScriptCall('cWindowAddContent', $content, $actions);

        return $response->sendResponse();
    }

    public function update() {
        $view = $this->getView('connect');
        echo $view->get(__FUNCTION__);
    }

    public function ajaxCreateNewAccount($name, $username, $email, $profileType = COMMUNITY_DEFAULT_PROFILE) {

        $filter = JFilterInput::getInstance();
        $name = $filter->clean($name, 'string');
        $username = $filter->clean($username, 'string');
        $email = $filter->clean($email, 'string');
        $profileType = $filter->clean($profileType, 'int');


        jimport('joomla.user.helper');

        $userModel = CFactory::getModel('User');
        $connectTable = JTable::getInstance('Connect', 'CTable');
        $mainframe = JFactory::getApplication();

        $userId = $this->_getFacebookUID();
        $response = new JAXResponse();
        $connectTable->load($userId);

        $config = CFactory::getConfig();

        // @rule: Ensure user doesn't really exists
        // BUT, even if it exist, if it is not linked to existing user,
        // it could be a login problem from previous attempt.
        // delete it and re-create user
        if ($connectTable->userid && !$userModel->exists($connectTable->userid)) {
            $connectTable->delete();
            $connectTable->userid = null;
        }

        if (!$connectTable->userid) {
            //@rule: Test if username already exists
            $username = $this->_checkUserName($username);
            $usersConfig = JComponentHelper::getParams('com_users');
            // Grab the new user type so we can get the correct gid for the ACL
            $newUsertype = $usersConfig->get('new_usertype');

            if (!$newUsertype)
                $newUsertype = 'Registered';

            // Generate a joomla password format for the user.
            $password = JUserHelper::genRandomPassword();

            $userData = array();
            $userData['name'] = $name;
            $userData['username'] = $username;
            $userData['email'] = $email;
            $userData['password'] = $password;
            $userData['password2'] = $password;

            // Update user's login to the current user
            $my = clone( JFactory::getUser() );
            $my->bind($userData);
            $my->set('id', 0);
            $my->set('usertype', '');
            $date = JDate::getInstance();
            $my->set('registerDate', $date->toSql());

            $my->set('gid', ( $newUsertype));

            //set group for J1.6
            $my->set('groups', array($newUsertype => $newUsertype));


            ob_start();
            try {
                $my->save();
            } catch (Exception $e) {
                $html  = '<div style="margin-bottom: 5px;">' . JText::_('COM_COMMUNITY_ERROR_VALIDATING_FACEBOOK_ACCOUNT') . '</div>';
                $html .= '<div><strong>' . JText::sprintf('Error: %1$s', $my->getError()) . '</strong></div>';

                $json = array(
                    'title' => $config->get('sitename'),
                    'error' => $html,
                    'btnBack' => JText::_('COM_COMMUNITY_BACK_BUTTON')
                );

                die( json_encode($json) );
            }

            $my = CFactory::getUser($my->id);
            $requireApproval = false;
            /* Update Profile Type -start-
             * mimic behavior from normal Jomsocial Registration
             */
            if ($profileType != COMMUNITY_DEFAULT_PROFILE) {

                $multiprofile = JTable::getInstance('MultiProfile', 'CTable');
                $multiprofile->load($profileType);

                // @rule: set users profile type.
                $my->_profile_id = $profileType;
                $my->_avatar = $multiprofile->avatar;
                $my->_thumb = $multiprofile->thumb;
                $requireApproval = $multiprofile->approvals;
            }

            // @rule: increment user points for registrations.
            $my->_points += 2;

            /* If Profile Type require approval, need to send approval email */
            if ($requireApproval) {
                jimport('joomla.user.helper');
                $my->set('activation', md5(JUserHelper::genRandomPassword()));
                $my->set('block', '1');
            }

            // increase default value set by admin (only apply to new registration)
            $default_points = $config->get('defaultpoint');
            if (isset($default_points) && $default_points > 0) {
                $my->_points += $config->get('defaultpoint');
            }

            if (!$my->save()) {
                ?>
                <div style="margin-bottom: 5px;"><?php echo JText::_('COM_COMMUNITY_ERROR_VALIDATING_FACEBOOK_ACCOUNT'); ?></div>
                <div><strong><?php echo JText::sprintf('Error: %1$s', $my->getError()); ?></strong></div>
                <div class="clear"></div>
                <?php
                $actions = '<input type="button" onclick="joms.api.fbcUpdate();" value="' . JText::_('COM_COMMUNITY_BACK_BUTTON') . '" class="btn" />';
                $content = ob_get_contents();
                @ob_end_clean();

                $response->addScriptCall('joms.jQuery("#cwin_logo").html("' . $config->get('sitename') . '");');
                $response->addScriptCall('cWindowAddContent', $content, $actions);

                return $response->sendResponse();
            }
            /* Update Profile Type -end- */

            $registerModel = CFactory::getModel('Register');
            $admins = $registerModel->getSuperAdministratorEmail();
            $sitename = $mainframe->get('sitename');
            $mailfrom = $mainframe->get('mailfrom');
            $fromname = $mainframe->get('fromname');
            $siteURL = JURI::root();
            $subject = JText::sprintf('COM_COMMUNITY_ACCOUNT_DETAILS_FOR', $name, $sitename);
            $subject = html_entity_decode($subject, ENT_QUOTES);

            //@rule: Send email notifications to site admin.
            foreach ($admins as $row) {
                if ($row->sendEmail) {
                    $message = JText::sprintf(JText::_('COM_COMMUNITY_SEND_MSG_ADMIN'), $row->name, $sitename, $my->name, $my->email, $my->username);
                    $message = html_entity_decode($message, ENT_QUOTES);

                    // Catch all email error message. Otherwise, it would cause
                    // fb connect to stall
                    ob_start();
                    $mail = JFactory::getMailer();
                    $mail->sendMail($mailfrom, $fromname, $row->email, $subject, $message);
                    ob_end_clean();
                }
            }

            // Store user mapping so the next time it will be able to detect this facebook user.
            $connectTable->connectid = $userId;
            $connectTable->userid = $my->id;
            $connectTable->type = 'facebook';
            $connectTable->store();

            $json = array( 'success' => true );
            die( json_encode( $json ) );
        }
    }

    /**
     * Popup window to invite fb friends
     */
    public function ajaxInvite() {
        $response = new JAXResponse();
        $connectFrameURL = CRoute::_('index.php?option=com_community&view=connect&task=inviteFrame');
        $content = '<iframe src="' . $connectFrameURL . '" width="620" height="410"  style="border:0px">';
        $response->addScriptCall('cWindowAddContent', $content);

        return $response->sendResponse();
    }

    /**
     *
     */
    public function inviteend() {
        $mainframe = JFactory::getApplication();
        $jinput = $mainframe->input;

        // If ids contains value, FB connect has successfully send some invite
        $ids = $jinput->get('ids',NULL,'NONE');
        if ($ids != null) {
            $mainframe->enqueueMessage(JText::sprintf( (CStringHelper::isPlural(count($ids))) ? 'COM_COMMUNITY_INVITE_EMAIL_SENT_MANY' : 'COM_COMMUNITY_INVITE_EMAIL_SENT', count($ids)));
        }

        // Queue the message back.
        // This method is similar to $mainframe->redirect();
        $_messageQueue = $mainframe->getMessageQueue();
        if (count($_messageQueue)) {
            $session = JFactory::getSession();
            $session->set('application.queue', $_messageQueue);
        }

        echo '<script>window.opener.location.reload();</script>';
        echo '<script>window.close();</script>';
        exit;
    }

    public function ajaxShowNewUserForm() {
        $response = new JAXResponse();
        $json = array();
        jimport('joomla.user.helper');
        $config = CFactory::getConfig();
        $profileTypes = array();
        $showNotice = false;

        if($config->get('profile_multiprofile',0)){
            $model = CFactory::getModel('Profile');
            $tmp = $model->getProfileTypes();

            foreach ($tmp as $profile) {
                $table = JTable::getInstance('MultiProfile', 'CTable');
                $table->load($profile->id);

                if ($table->approvals)
                    $showNotice = true;

                $profileTypes[] = $table;
            }
        }

        $connectTable = JTable::getInstance('Connect', 'CTable');


        $userId = $this->_getFacebookUID();
        $userInfo = $this->_getFacebookUser();

        $connectTable->load($userId);

        $tmpl = new CTemplate();
        $tmpl->set('userInfo', $userInfo)
            ->set('default', COMMUNITY_DEFAULT_PROFILE)
            ->set('profileTypes', $profileTypes);

        $json = array(
            'title'     => JText::_('COM_COMMUNITY_ACCOUNT_SIGNUP_FROM_FACEBOOK'),
            'html'      => $tmpl->fetch('facebook.newuserform'),
            'btnBack'   => JText::_('COM_COMMUNITY_BACK_BUTTON'),
            'btnCreate' => JText::_('COM_COMMUNITY_CREATE')
        );

        die( json_encode($json) );
    }

    /**
     *
     */
    public function ajaxShowExistingUserForm() {
        jimport('joomla.user.helper');
        $response = new JAXResponse();
        $config = CFactory::getConfig();

        $userId = $this->_getFacebookUID();

        $connectTable = JTable::getInstance('Connect', 'CTable');
        $connectTable->load($userId);

        $tmpl = new CTemplate();
        $tmpl->set('config', $config);

        $json = array(
            'title'    => JText::_('COM_COMMUNITY_ACCOUNT_SIGNUP_FROM_FACEBOOK'),
            'html'     => $tmpl->fetch('facebook.existinguserform'),
            'btnBack'  => JText::_('COM_COMMUNITY_BACK_BUTTON'),
            'btnLogin' => JText::_('COM_COMMUNITY_LOGIN')
        );

        die( json_encode($json) );
    }

    private function _getInvalidResponse($response) {
        $response->addAssign('cwin_logo', 'innerHTML', JText::_('COM_COMMUNITY_ERROR'));

        $html = JText::_('COM_COMMUNITY_FBCONNECT_LOGIN_DETECT_ERROR');

        $response->addScriptCall('cWindowAddContent', $html);

        return $response;
    }

    public function inviteFrame() {
        $view = $this->getView('connect');
        $my = CFactory::getUser();

        // Although user is signed on in Facebook, we should never allow them to view this page if
        // they are not logged into the site.
        if ($my->id == 0) {
            return $this->blockUnregister();
        }
        echo $view->get(__FUNCTION__);
        exit;
    }

    /**
     * Ajax method to update user's authentication via Facebook
     * */
    public function ajaxUpdate($secretKey = 0) {
        $response = new JAXResponse();
        $json = array();
        $config = CFactory::getConfig();
        $mainframe = JFactory::getApplication();

        $connectTable = JTable::getInstance('Connect', 'CTable');

        $userId = $this->_getFacebookUID();

        if (!$userId) {
            $json['title'] = JText::_('COM_COMMUNITY_ERROR');
            $json['error'] = JText::_('COM_COMMUNITY_FBCONNECT_LOGIN_DETECT_ERROR');
            die( json_encode($json) );
        }

        $connectTable->load($userId);
        $userInfo = $this->_getFacebookUser();

        $redirect = CRoute::_('index.php?option=com_community&view=' . $config->get('redirect_login'), false);
        $error = false;
        $content = '';

        if (!$connectTable->userid) {
            $tmpl = new CTemplate();
            $tmpl->set('userInfo', $userInfo);

            $json['title'] = JText::_('COM_COMMUNITY_ACCOUNT_SIGNUP_FROM_FACEBOOK');
            $json['html'] = $tmpl->fetch('facebook.firstlogin');
            $json['btnNext'] = JText::_('COM_COMMUNITY_NEXT');
            $json['lang'] = array(
                'selectProfileType' => JText::_('COM_COMMUNITY_SELECT_PROFILE_TYPE'),
                'btnBack' => JText::_('COM_COMMUNITY_BACK_BUTTON')
            );
            die( json_encode($json) );

        } else {
            $my = CFactory::getUser($connectTable->userid);

            if ( COwnerHelper::isCommunityAdmin($connectTable->userid)) {
                $tmpl = new CTemplate();

                $json['title'] = JText::_('COM_COMMUNITY_ERROR');
                $json['html'] = $tmpl->fetch('facebook.link.notallowed');
                die( json_encode($json) );
            }

            // Generate a joomla password format for the user so we can log them in.
            $password = JUserHelper::genRandomPassword();

            $userData = array();
            $userData['password'] = $password;
            $userData['password'] = $password;
            $userData['password2'] = $password;
            $my->set('password', JUserHelper::hashPassword($password));
            $options = array();
            $options['remember'] = true;
            //$options['return']   = $data['return'];

            // Get the log in credentials.
            $credentials = array();
            $credentials['username']  = $my->username;
            $credentials['password']  = $password;
            $credentials['secretkey'] = $secretKey;

            JFactory::getApplication()->login($credentials, $options);

            // User object must be saved again so the password change get's reflected.
            $my->save();

            JFactory::getApplication()->login($credentials, $options);

            $loginStatus = $mainframe->login(array('username' => $my->username, 'password' => $password, 'secretkey'=>$secretKey));

            if(!$loginStatus){
                //if login failed, return the error
                $json['title'] = JText::_('COM_COMMUNITY_ERROR');
                $json['error'] = JText::_('COM_COMMUNITY_FBCONNECT_LOGIN_DETECT_ERROR');
                die( json_encode($json) );
            }

            if ($config->get('fbloginimportprofile')) {
                $this->_facebook->mapProfile($userInfo, $my->id);
            }

            // Update page token since the userid is changed now.
            $session = JFactory::getSession();
            $token = $session->getFormToken(false);

            $tmpl = new CTemplate();
            $tmpl->set('my', $my);
            $tmpl->set('userInfo', $userInfo);

            $json = array(
                'title'         => $config->get('sitename'),
                'html'          => $tmpl->fetch('facebook.existinguser'),
                'btnContinue'   => JText::_('COM_COMMUNITY_CONTINUE_BUTTON'),
                'jax_token_var' => $token,
                'lang'          => array(
                    'selectProfileType' => JText::_('COM_COMMUNITY_SELECT_PROFILE_TYPE'),
                    'btnBack' => JText::_('COM_COMMUNITY_BACK_BUTTON')
                )
            );

            die( json_encode($json) );
        }
    }

    /**
     *
     * @param type $importStatus
     * @param type $importAvatar
     * @return type
     */
    public function ajaxImportData($importStatus, $importAvatar) {
        jimport('joomla.user.helper');
        $response = new JAXResponse();

        $filter = JFilterInput::getInstance();
        $importStatus = $filter->clean($importStatus, 'boolean');
        $importAvatar = $filter->clean($importAvatar, 'boolean');
        $config = CFactory::getConfig();

        // @rule: When administrator disables status imports, we should not allow user to import status
        /* temporary force no import status */

        if (!$config->get('fbconnectupdatestatus')) {
            $importStatus = false;
        }

        $userId = $this->_getFacebookUID();
        $userInfo = $this->_getFacebookUser();

        $connectTable = JTable::getInstance('Connect', 'CTable');
        $connectTable->load($userId);

        $my = CFactory::getUser($connectTable->userid);

        //trigger redirect login event
        $dispatcher = JDispatcher::getInstance();
        $redirect_url = $dispatcher->trigger('onAfterFBUserLogin', array());

        $multiprofile = JTable::getInstance('MultiProfile', 'CTable');
        $multiprofile->load($my->getProfileType());

        if (empty($redirect_url)) {
            if ($my->block == 1) {
                $redirect = CRoute::_('index.php?option=com_community&view=register&task=registerSucess&profileType=' . $my->getProfileType());
            } else {
                $redirect = CRoute::_('index.php?option=com_community&view=' . $config->get('redirect_login'), false);
            }
        } else {
            if (is_array($redirect_url)) {
                $redirect = $redirect_url[0];
            } else {
                $redirect = $redirect_url;
            }

            if ($my->block == 1) {
                $redirect = CRoute::_('index.php?option=com_community&view=register&task=registerSucess&profileType=' . $my->getProfileType());
            }
        }

        if ( COwnerHelper::isCommunityAdmin($connectTable->userid) ) {
            $tmpl = new CTemplate();
            $content = $tmpl->fetch('facebook.link.notallowed');
            $json = array(
                'title'   => $config->get('sitename'),
                'error'   => $content,
                'btnNext' => JText::_('COM_COMMUNITY_BUTTON_CLOSE_BUTTON')
            );

            die( json_encode( $json ) );
        }

        if ($importAvatar) {
            $this->_facebook->mapAvatar($userInfo['pic_big'], $my->id, $config->get('fbwatermark'));
        }

        if ($importStatus) {
            $this->_facebook->mapStatus($my->id);
        }

        if ($multiprofile->approvals && $my->block == 1) {
            $usersConfig = JComponentHelper::getParams('com_users');
            $jAdminApproval = $usersConfig->get('useractivation') == '2' ? 1 : 0;

            //Dirty hack to send email. Need to move it to library.
            require_once(JPATH_ROOT . '/components/com_community/controllers/register.php');

            $registerController = new CommunityRegisterController();
            $registerController->sendEmail('registration_complete', $my, null, $multiprofile->approvals | $jAdminApproval);
        }


        if ( !JString::stristr($my->email, '@foo.bar') ) {
            $json = array( 'redirect' => $redirect );
            die( json_encode( $json ) );
        }

        // Deprecated since 1.6.x
        // In older releases, connected users uses the email @foo.bar by default.
        // If it passes the above, the user definitely needs to edit the e-mail.
        $tmpl = new CTemplate();
        $tmpl->set('my', $my);
        $content = $tmpl->fetch('facebook.emailupdate');

        $json = array(
            'title'     => $config->get('sitename'),
            'html'      => $content,
            'btnSkip'   => JText::_('COM_COMMUNITY_SKIP_BUTTON'),
            'btnUpdate' => JText::_('COM_COMMUNITY_UPDATE_EMAIL_BUTTON'),
            'redirect'  => $redirect
        );

        die( json_encode( $json ) );
    }

    /**
     * Displays the XDReceiver data for Facebook to connect
     * */
    public function receiver() {
        $view = $this->getView('connect');
        echo $view->get('receiver');

        // Exit here so joomla will not process anything.
        exit;
    }

    public function logout() {
        $my = CFactory::getUser();
        $mainframe = JFactory::getApplication();

        // Double check that user is really logged in
        if ($my->id != 0) {
            $mainframe->logout();

            // Return to JomSocial front page.
            // @todo: configurable?
            $url = CRoute::_('index.php?option=com_community&view=frontpage', false);

            $mainframe->redirect($url, JText::_('COM_COMMUNITY_SUCCESSFULL_LOGOUT'));
        }
    }

    /**
     * 	Method to test if username already exists
     * */
    private function _checkUserName($username) {
        $model = CFactory::getModel('register');

        $originalUsername = $username;
        $exists = $model->isUserNameExists(array('username' => $username));

        if ($exists) {
            //@rule: If user exists, generate random username for the user by appending some integer
            $i = 1;
            while ($exists) {
                $username = $originalUsername . $i;
                $exists = $model->isUserNameExists(array('username' => $username));
                $i++;
            }
        }
        return $username;
    }

    /**
     * 	Checks the validity of the email via AJAX calls
     * */
    public function ajaxCheckEmail($email) {
        $response = new JAXResponse();
        $model = $this->getModel('user');

        $filter = JFilterInput::getInstance();
        $email = $filter->clean($email, 'string');

        // @rule: Check email format
        //CFactory::load( 'helpers' , 'validate' );

        $valid = CValidateHelper::email($email);

        if ((!$valid && !empty($email) ) || empty($email)) {
            $response->addScriptCall('joms.jQuery("#newemail").addClass("invalid");');
            $response->addScriptCall('joms.jQuery("#error-newemail").show();');
            $response->addScriptCall('joms.jQuery("#error-newemail").html("' . JText::sprintf('COM_COMMUNITY_INVALID_FB_EMAIL', htmlspecialchars($email)) . '");');
            return $response->sendResponse();
        }

        $exists = $model->userExistsbyEmail($email);

        if ($exists) {
            $response->addScriptCall('joms.jQuery("#newemail").addClass("invalid");');
            $response->addScriptCall('joms.jQuery("#error-newemail").show();');
            $response->addScriptCall('joms.jQuery("#error-newemail").html("' . JText::sprintf('COM_COMMUNITY_INVITE_EMAIL_EXIST', htmlspecialchars($email)) . '");');
            return $response->sendResponse();
        }

        $response->addScriptCall('joms.jQuery("#newemail").removeClass("invalid");');
        $response->addScriptCall('joms.jQuery("#error-newemail").html("&nbsp");');
        $response->addScriptCall('joms.jQuery("#error-newemail").hide();');
        return $response->sendResponse();
    }

    /**
     * 	Checks the validity of the username via AJAX calls
     *
     * 	@params	$username	String	The username that is passed.
     * */
    public function ajaxCheckUsername($username) {
        $response = new JAXResponse();

        $filter = JFilterInput::getInstance();
        $username = $filter->clean($username, 'string');

        //CFactory::load( 'helpers' , 'validate' );
        $valid = CValidateHelper::username($username);

        if ((!$valid && !empty($username)) || empty($username)) {
            $response->addScriptCall('joms.jQuery("#newusername").addClass("invalid");');
            $response->addScriptCall('joms.jQuery("#error-newusername").show();');
            $response->addScriptCall('joms.jQuery("#error-newusername").html("' . JText::sprintf('COM_COMMUNITY_INVALID_USERNAME', htmlspecialchars($username)) . '");');
            return $response->sendResponse();
        }

        $model = CFactory::getModel('register');
        $exists = $model->isUserNameExists(array('username' => $username));

        if ($exists) {
            $response->addScriptCall('joms.jQuery("#newusername").addClass("invalid");');
            $response->addScriptCall('joms.jQuery("#error-newusername").show();');
            $response->addScriptCall('joms.jQuery("#error-newusername").html("' . JText::sprintf('COM_COMMUNITY_USERNAME_EXISTS', htmlspecialchars($username)) . '");');
            return $response->sendResponse();
        }
        $response->addScriptCall('joms.jQuery("#newusername").removeClass("invalid");');
        $response->addScriptCall('joms.jQuery("#error-newusername").html("&nbsp");');
        $response->addScriptCall('joms.jQuery("#error-newusername").hide();');

        return $response->sendResponse();
    }

    /**
     * 	Checks the validity of the name via AJAX calls
     *
     * 	@params	$name	String	The name that is passed.
     * */
    public function ajaxCheckName($name) {
        $response = new JAXResponse();

        $filter = JFilterInput::getInstance();
        $name = $filter->clean($name, 'string');

        if (empty($name)) {
            $response->addScriptCall('joms.jQuery("#newname").addClass("invalid");');
            $response->addScriptCall('joms.jQuery("#error-newname").show();');
            $response->addScriptCall('joms.jQuery("#error-newname").html("' . JText::_('COM_COMMUNITY_PLEASE_ENTER_NAME') . '");');
            return $response->sendResponse();
        }

        $response->addScriptCall('joms.jQuery("#newname").removeClass("invalid");');
        $response->addScriptCall('joms.jQuery("#error-newname").html("&nbsp");');
        $response->addScriptCall('joms.jQuery("#error-newname").hide();');

        return $response->sendResponse();
    }

    private function _getFacebookUID() {
        return $this->_facebook->getUser();
    }

    private function _getFacebookUser($fields = array()) {
        //$defFields = array('email', 'first_name', 'last_name', 'birthday_date', 'current_location', 'pic', 'sex', 'name', 'pic_square', 'profile_url', 'pic_big', 'about_me', 'website', 'education');
        //$fields = array_merge($defFields, $fields);
        //$connectId = $this->_facebook->getUser();
        $userInfo = $this->_facebook->getUserInfo();
        if ($userInfo) {
            return $userInfo;
        }
        return false;
    }

    public function ajaxCheckProfileType(){
        $response = new JAXResponse();

        $response->addScriptCall("joms.jQuery('.jsProfileType').append",'<span style="color:red">'.JText::_('COM_COMMUNITY_NO_PROFILE_TYPE_SELECTED').'</span>');
        return $response->sendResponse();
    }

}
