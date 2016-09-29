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

class CommunityRegisterController extends CommunityBaseController
{

    /**
     * First step of registration
     * Display the new user registration form
     */
    public function register()
    {

        $my = CFactory::getUser();

        if ($my->id != 0) {
            $mainframe = JFactory::getApplication();
            $mainframe->redirect(CRoute::_('index.php?option=com_community&view=frontpage', false));
        }

        //run this silently to clean up the 'left-over' temp user.
        $rModel = CFactory::getModel('register');
        $rModel->cleanTempUser();

        $view = $this->getView('register');
        echo $view->get('register');
    }

    /**
     * Step 2: Save register information
     * @return boolean
     */
    public function register_save()
    {
        $mainframe = JFactory::getApplication();
        $jinput = $mainframe->input;
        $modelRegister = CFactory::getModel('register');

        // Check for request forgeries
        $mySess = JFactory::getSession();

        if (!$mySess->has('JS_REG_TOKEN')) {
            echo '<div class="error-box">' . JText::_('COM_COMMUNITY_INVALID_SESSION') . '</div>';
            return;
        }

        $token = $mySess->get('JS_REG_TOKEN', '');
        $ipAddress = isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR'];
        $authKey = $modelRegister->getAssignedAuthKey($token, $ipAddress);
        $formToken = $jinput->request->get('authkey', '', 'STRING');

        if (empty($formToken) || empty($authKey) || ($formToken != $authKey)) {
            echo '<div class="error-box">' . JText::_('COM_COMMUNITY_INVALID_TOKEN') . '</div>';
            return;
        }

        //update the auth key life span to another 180 sec.
        $modelRegister->updateAuthKey($token, $authKey, $ipAddress);

        // Get required system objects
        $config = CFactory::getConfig();
        $post = $jinput->post->getArray();

        // If user registration is not allowed, show 403 not authorized.
        $usersConfig = JComponentHelper::getParams('com_users');

        /* Do not allow for user registration */
        if ($usersConfig->get('allowUserRegistration') == '0') {
            //show warning message
            $view = $this->getView('register');
            $view->addWarning(JText::_('COM_COMMUNITY_REGISTRATION_DISABLED'));
            echo $view->get('register');
            return;
        }

        //perform forms validation before continue further.
        /*
         * Rules:
         * First we let 3rd party plugin to intercept the validation.
         * if there is not error return, we then proceed with our validation.
         */
        $errMsg = array();
        $errTrigger = null;

        $appsLib = CAppPlugins::getInstance();
        $appsLib->loadApplications();

        $params = array();
        $params[] = $post;
        $errTrigger = $appsLib->triggerEvent('onRegisterValidate', $params);

        if (is_null($errTrigger)) {
            //no trigger found.
            $errMsg = $this->_validateRegister($post);
        } else {
            if (!empty($errTrigger[0])) {
                $errMsg = $errTrigger[0];
            } else {
                // trigger found but no error return.
                $errMsg = $this->_validateRegister($post);
            }
        }

        if (count($errMsg) > 0) {
            //validation failed. show error message.
            foreach ($errMsg as $err) {
                $mainframe->enqueueMessage($err, 'error');
            }

            $this->register();
            return false;
        }

        // @rule: check with recaptcha
        $recaptcha = new CRecaptchaHelper();

        if (!$recaptcha->verify()) {
            JFactory::getApplication()->enqueueMessage(JText::_('COM_COMMUNITY_RECAPTCHA_MISMATCH'), 'error');
            $this->register();
            return false;
        }


        //adding to temp reg table.
        if (!isset($modelRegister->addTempUser($post)->return_value['addTempUser']) || !$modelRegister->addTempUser($post)->return_value['addTempUser']) {
            JFactory::getApplication()->enqueueMessage(JText::_('COM_COMMUNITY_ERROR_IN_REGISTRATION'), 'error');
            $this->register();
            return false;
        }

        // Send the first email to inform user of their username and password
        $tmpUser = $modelRegister->getTempUser($token);
        $password = (string)$post['jspassword2'];

        //now we check whether there is any custom profile? if not, then we do the actual save here.
        $modelProfile = CFactory::getModel('profile');

        //get all published custom field for profile
        $filter = array('published' => '1', 'registration' => '1');
        $fields = $modelProfile->getAllFields($filter);

        $model = CFactory::getModel('Profile');
        $profileTypes = $model->getProfileTypes();

        // If there are no fields, we do not want to move to the edit profile area.
        if (count($fields) <= 0 && (!$profileTypes || !$config->get('profile_multiprofile'))) {
            //do the actual user save.
            $user = $this->_createUser($tmpUser);

            //update the first/last name if it exist in the profile configuration
            $this->_updateFirstLastName($user);
            $this->sendEmail('registration', $user, $password);

            // now we need to set it for later avatar upload page
            // do the clear up job for tmp user.
            $mySess->set('tmpUser', $user);

            $modelRegister->removeTempUser($token);
            $modelRegister->removeAuthKey($token);

            $usersConfig = $usersConfig = JComponentHelper::getParams('com_users');
            $useractivation = ($usersConfig->get('useractivation') == 2) ? true : false;

            $this->sendEmail('registration_complete', $user, null, $useractivation);

            //redirect to avatar upload page.
            $mainframe->redirect(CRoute::_('index.php?option=com_community&view=register&task=registerAvatar', false));
        } else {
            $this->sendEmail('registration_uncomplete', $tmpUser, $password);

            //redirect to profile update page.
            // @rule: When there are no defined profile types, we will use the default.
            if (!$profileTypes || !$config->get('profile_multiprofile')) {
                $mainframe->redirect(
                    CRoute::_(
                        'index.php?option=com_community&view=register&task=registerProfile&profileType=' . COMMUNITY_DEFAULT_PROFILE,
                        false
                    )
                );
            } else {
                // Now that the username and name are properly entered, redirect them to select the profile type.
                $mainframe->redirect(
                    CRoute::_('index.php?option=com_community&view=register&task=registerProfileType', false)
                );
            }
        }
    }

    /**
     * Step 3
     * Display and process the multiple profile types.
     * @return type
     */
    public function registerProfileType()
    {
        $mainframe = JFactory::getApplication();
        $jinput = $mainframe->input;
        $mySess = JFactory::getSession();
        $config = CFactory::getConfig();

        if (!$config->get('profile_multiprofile')) {
            echo JText::_('COM_COMMUNITY_MULTIPROFILE_IS_CURRENTLY_DISABLED');
            return;
        }

        if (!$mySess->has('JS_REG_TOKEN')) {
            $mainframe->redirect(CRoute::_('index.php?option=com_community&view=register', false));
            return;
        }

        $view = $this->getView('register');

        if ($jinput->getMethod() == 'POST') {
            $type = $jinput->get('profileType', 0, 'INT');
            // @rule: When multiple profile is enabled, and profile type is not selected, we should trigger an error.
            if ($config->get('profile_multiprofile') && $type == COMMUNITY_DEFAULT_PROFILE) {
                $mainframe->enqueueMessage(JText::_('COM_COMMUNITY_NO_PROFILE_TYPE_SELECTED'), 'error');
            } else {
                $mainframe->redirect(
                    CRoute::_(
                        'index.php?option=com_community&view=register&task=registerProfile&profileType=' . $type,
                        false
                    )
                );
            }
        }

        echo $view->get(__FUNCTION__);
    }

    public function _updateFirstLastName($user, $profileType = COMMUNITY_DEFAULT_PROFILE)
    {
        $profileModel = CFactory::getModel('profile');
        $filter = array('fieldcode' => 'FIELD_FAMILYNAME');
        $fields = $profileModel->getAllFields($filter, $profileType);

        if (!empty($fields)) {

            $isUseFirstLastName = CUserHelper::isUseFirstLastName();

            if ($isUseFirstLastName) {
                $tmpUserModel = CFactory::getModel('register');
                $mySess = JFactory::getSession();
                $tmpUser = $tmpUserModel->getTempUser($mySess->get('JS_REG_TOKEN', ''));

                $fullname = array();
                $fullname[$profileModel->getFieldId('FIELD_GIVENNAME')] = $tmpUser->firstname;
                $fullname[$profileModel->getFieldId('FIELD_FAMILYNAME')] = $tmpUser->lastname;

                $pModel = $this->getModel('profile');
                $pModel->saveProfile($user->id, $fullname);
            }
        }
    }

    /**
     * Display custom profiles for the user during registrations.
     * */
    public function registerProfile()
    {
        $mainframe = JFactory::getApplication();
        $jinput = $mainframe->input;
        $mySess = JFactory::getSession();
        $config = CFactory::getConfig();

        if (!$mySess->has('JS_REG_TOKEN')) {
            $mainframe->redirect(CRoute::_('index.php?option=com_community&view=register', false));
            return;
        }

        // Get all published custom field for profile
        $filter = array('published' => '1', 'registration' => '1');

        $profileType = $jinput->get('profileType', 0, 'INT');
        $profileModel = CFactory::getModel('profile');
        $fields = $profileModel->getAllFields($filter, $profileType);

        //validate profile type, if profile type does not exist or multiple profile is disabled, we shouldnt do anything
        if((!$config->get('profile_multiprofile') || !CMultiprofileHelper::isActiveProfile($profileType)) && $profileType){
            //return to default profile
            $mainframe->redirect(CRoute::_('index.php?option=com_community&view=register&task=registerProfile&profileType=0', false));
            return true;
        }

        if (empty($fields)) {
            $mySess = JFactory::getSession();
            $token = $mySess->get('JS_REG_TOKEN', '');

            $multiprofile = JTable::getInstance('MultiProfile', 'CTable');
            $multiprofile->load($profileType);

            $model = CFactory::getModel('register');
            $tmpUser = $model->getTempUser($token);

            $user = $this->_createUser($tmpUser, $multiprofile->approvals, $multiprofile->id);
            //update the first/last name if it exist in the profile configuration
            $this->_updateFirstLastName($user);

            $mySess->set('tmpUser', $user);

            $model->removeTempUser($token);
            $model->removeAuthKey($token);

            $this->sendEmail('registration_complete', $user, null, $multiprofile->approvals);

            // If no fields created yet, the system should be intelligent enough to automatically sense it and redirect users to the register avatar page.
            $mainframe->redirect(
                CRoute::_(
                    'index.php?option=com_community&view=register&task=registerAvatar&profileType=' . $profileType,
                    false
                )
            );
        }


        $document = JFactory::getDocument();
        $view = $this->getView('register');

        echo $view->get('registerProfile', $fields);
    }

    /**
     * Private method to create a user in the site.
     * */
    private function _createUser($tmpUser, $requireApproval = false, $profileType = 0)
    {
        $mainframe = JFactory::getApplication();
        $jinput = $mainframe->input;

        if (empty($tmpUser) || !isset($tmpUser->username)) {
            JFactory::getApplication()->enqueueMessage(JText::_('COM_COMMUNITY_REGISTRATION_MISSING_USER_OBJ'), 'error');
            return;
        }

        //Remove whitespace infront of username
        $tmpUser->username = trim($tmpUser->username);

        $user = clone(JFactory::getUser());
        $usersConfig = JComponentHelper::getParams('com_users');
        $cacl = CACL::getInstance();
        $userObj = get_object_vars($tmpUser);

        // Get usertype from configuration. If tempty, user 'Registered' as default
        $newUsertype = $usersConfig->get('new_usertype');

        if (!$newUsertype) {
            $newUsertype = 'Registered';
        }

        // Bind the post array to the user object
        if (!$user->bind($userObj, 'usertype')) {
            JFactory::getApplication()->enqueueMessage(JText::_('COM_COMMUNITY_ERROR'), 'error');
        }

        // Initialize user default values
        $date = JDate::getInstance();

        if ($requireApproval) {
            $user->set('block', 1);
        }

        $user->set('id', 0);
        $user->set('usertype', $newUsertype);
        $user->set('gid', ($newUsertype));

        //set group for J1.6
        $user->set('groups', array($newUsertype => $newUsertype));

        $user->set('registerDate', $date->toSql());

        // If user activation is turned on, we need to set the activation information. In joomla 1.6, still need to send activation link email when admin approval is enable
        $useractivation = $usersConfig->get('useractivation');

        if ($useractivation != 0 && (!$requireApproval)) {
            jimport('joomla.user.helper');
            $user->set('block', '1');
        }

        //Jooomla 3.2.0 fix. TO be remove in future
        if (version_compare(JVERSION, '3.2.0', '=')) {
            $salt = JUserHelper::genRandomPassword(32);
            $crypt = JUserHelper::getCryptedPassword($userObj['password2'], $salt);
            $password = $crypt . ':' . $salt;
        } else {
            // Don't re-encrypt the password
            // JUser bind has encrypted the password
            $password = $userObj['password2'];
        }

        $user->set('password', $password);

        if($useractivation != 0) {
            $user->set('activation', JApplicationHelper::getHash(JUserHelper::genRandomPassword()));
        }

        // If there was an error with registration, set the message and display the form
        if (!$user->save()) {
            JFactory::getApplication()->enqueueMessage(JText::_($user->getError()), 'error');

            $this->register();
            return false;
        }

        if ($user->id == 0) {
            $uModel = $this->getModel('user');
            $newUserId = $uModel->getUserId($user->username);
            $user = JFactory::getUser($newUserId);
        }

        // Update the user's invite if any
        // @todo: move this into plugin. onUserCreated
        $inviteId = $jinput->cookie->get('inviteId', 0);
        $cuser = CFactory::getUser($user->id);

        if ($profileType != COMMUNITY_DEFAULT_PROFILE) {

            $multiprofile = JTable::getInstance('MultiProfile', 'CTable');
            $multiprofile->load($profileType);

            // @rule: set users profile type.
            $cuser->_profile_id = $profileType;
            //$cuser->_avatar = $multiprofile->avatar;
            //$cuser->_thumb = $multiprofile->thumb;
        }

        // @rule: increment user points for registrations.
        $cuser->_points += 2;

        // increase default value set by admin (only apply to new registration)
        $config = CFactory::getConfig();
        $default_points = $config->get('defaultpoint');

        if (isset($default_points) && $default_points > 0) {
            $cuser->_points += $config->get('defaultpoint');
        }

        $config = CFactory::getConfig();
        $cuser->_invite = $inviteId;
        $cuser->save();

        //create default albums for user
        $avatarAlbum = JTable::getInstance('Album', 'CTable');
        $avatarAlbum->addAvatarAlbum($cuser->id, 'profile');
        $coverAlbum = JTable::getInstance('Album', 'CTable');
        $coverAlbum->addCoverAlbum('profile',$cuser->id);
        $defaultAlbum = JTable::getInstance('Album', 'CTable');
        $defaultAlbum->addDefaultAlbum($cuser->id, 'profile');

        return $user;
    }

    /**
     * Step 4
     * Update the users profile.
     */
    public function registerUpdateProfile()
    {
        $mainframe = JFactory::getApplication();
        $jinput = $mainframe->input;
        $model = $this->getModel('register');

        // Check for request forgeries
        $mySess = JFactory::getSession();
        $ipAddress = isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR'];
        $token = $mySess->get('JS_REG_TOKEN', '');

        $formToken = $jinput->request->get('authkey', '', 'STRING');
        //$authKey   = $model->getAssignedAuthKey($token, $ipAddress);

        if (!$token) { //(empty($formToken) || empty($authKey) || ($formToken != $authKey))
            echo '<div class="error-box">' . JText::_('COM_COMMUNITY_INVALID_SESSION') . '</div>';
            return;
        }

        //intercept validation process in custom profile
        $post = $jinput->post->getArray();

        /*
         * Rules:
         * First we let 3rd party plugin to intercept the validation.
         * if there is not error return, we then proceed with our validation.
         */
        $errMsg = array();
        $errTrigger = null;

        $appsLib = CAppPlugins::getInstance();
        $appsLib->loadApplications();

        $params = array();
        $params[] = $post;
        $errTrigger = $appsLib->triggerEvent('onRegisterProfileValidate', $params);

        if (!is_null($errTrigger)) {
            if (!empty($errTrigger[0]) && count($errTrigger[0]) > 0) {
                //error found.
                foreach ($errTrigger[0] as $err) {
                    $mainframe->enqueueMessage($err, 'error');
                }

                $this->registerProfile();
                return;
            }
        }

        // get required obj for registration
        $pModel = $this->getModel('profile');
        $values = array();

        $filter = array('published' => '1', 'registration' => '1');
        $profileType = $jinput->post->getInt('profileType', 0);
        $profiles = $pModel->getAllFields($filter, $profileType);

        foreach ($profiles as $key => $groups) {
            foreach ($groups->fields as $data) {
                $fieldValue = new stdClass();

                // Get value from posted data and map it to the field.
                // Here we need to prepend the 'field' before the id because in the form, the 'field' is prepended to the id.
                $postData = $jinput->post->get(
                    'field' . $data->id,
                    '',
                    'NONE'
                );
                // Retrieve the privacy data for this particular field.
                $fieldValue->access = $jinput->post->getInt('privacy' . $data->id, 0);
                $fieldValue->value = CProfileLibrary::formatData($data->type, $postData);

                //@since 4.2 assign params if needed
                if($data->type == 'birthdate'){ //@since 4.2 date has special value if specified
                    //the third parameter should be hide year or not
                    $fieldValue->params = CFieldsDate::getHideYearParams($postData);
                }

                if (get_magic_quotes_gpc()) {
                    $fieldValue->value = stripslashes($fieldValue->value);
                }

                $values[$data->id] = $fieldValue;

                // @rule: Validate custom profile if necessary
                if (!CProfileLibrary::validateField(
                    $data->id,
                    $data->type,
                    $values[$data->id]->value,
                    $data->required
                )
                ) {
                    // If there are errors on the form, display to the user.
                    $message = JText::sprintf('COM_COMMUNITY_FIELD_CONTAIN_IMPROPER_VALUES', $data->name);
                    $mainframe->enqueueMessage($message, 'error');
                    $this->registerProfile();
                    return;
                }
            }
        }

        $profileType = $jinput->post->get('profileType', 0, 'NONE');
        $multiprofile = JTable::getInstance('MultiProfile', 'CTable');
        $multiprofile->load($profileType);

        $tmpUser = $model->getTempUser($token);
        $user = $this->_createUser($tmpUser, $multiprofile->approvals, $multiprofile->id);

        //update the first/last name if it exist in the profile configuration
        $this->_updateFirstLastName($user);

        $pModel->saveProfile($user->id, $values);

        // Update user location data
        $pModel->updateLocationData($user->id);

        $this->sendEmail('registration_complete', $user, null, $multiprofile->approvals);


        // now we need to set it for later avatar upload page
        // do the clear up job for tmp user.
        $mySess->set('tmpUser', $user);

        $model->removeTempUser($token);
        $model->removeAuthKey($token);

        //redirect to avatar upload page.
        $mainframe->redirect(
            CRoute::_(
                'index.php?option=com_community&view=register&task=registerAvatar&profileType=' . $profileType,
                false
            )
        );
    }

    /**
     * Step 5
     * Upload a new user avatar
     */
    public function registerAvatar()
    {
        $mainframe = JFactory::getApplication();
        $jinput = $mainframe->input;
        jimport('joomla.filesystem.file');
        jimport('joomla.utilities.utility');

        $mySess = JFactory::getSession();
        $user = $mySess->get('tmpUser', '');
        /* Just for incase this's incomplete object */
        if (!is_object($user) && gettype($user) == 'object') {
            $user = unserialize(serialize($user));
        }

        if (empty($user)) {
            //throw error.
            JFactory::getApplication()->enqueueMessage(JText::_('COM_COMMUNITY_REGISTRATION_MISSING_USER_OBJ'), 'error');
            return;
        }

        $view = $this->getView('register');

        $profileType = $jinput->getInt('profileType', 0);

        // If uplaod is detected, we process the uploaded avatar
        if ($jinput->post->get('action', '')) {
            $my = CFactory::getUser($user->id);
            $fileFilter = new JInput($jinput->files->getArray());
            $file = $fileFilter->get('Filedata', '', 'array');

            if ($my->id == 0) {
                return $this->blockUnregister();
            }
            if (!CImageHelper::isValidType($file['type'])) {
                $mainframe->enqueueMessage(JText::_('COM_COMMUNITY_IMAGE_FILE_NOT_SUPPORTED'), 'error');
                $url = ($profileType !== 0) ? CRoute::_(
                    'index.php?option=com_community&view=register&task=registerAvatar&profileType=' . $profileType,
                    false
                ) : CRoute::_('index.php?option=com_community&view=register&task=registerAvatar', false);
                $mainframe->redirect($url);
                return;
            }

            if (!isset($file['tmp_name']) || empty($file['tmp_name'])) {
                $mainframe->enqueueMessage(JText::_('COM_COMMUNITY_NO_POST_DATA'), 'error');
            } else {
                $config = CFactory::getConfig();
                $uploadLimit = (double)$config->get('maxuploadsize');
                $uploadLimit = ($uploadLimit * 1024 * 1024);

                if (filesize($file['tmp_name']) > $uploadLimit && $uploadLimit != 0) {
                    $mainframe->enqueueMessage(JText::sprintf('COM_COMMUNITY_VIDEOS_IMAGE_FILE_SIZE_EXCEEDED_MB',CFactory::getConfig()->get('maxuploadsize')), 'error');
                    $mainframe->redirect(
                        CRoute::_(
                            'index.php?option=com_community&view=register&task=registerAvatar&profileType=' . $profileType,
                            false
                        )
                    );
                }

                if (!CImageHelper::isValid($file['tmp_name'])) {
                    $mainframe->enqueueMessage(JText::_('COM_COMMUNITY_IMAGE_FILE_NOT_SUPPORTED'), 'error');
                } else {
                    $config = CFactory::getConfig();
                    $useWatermark = $profileType != COMMUNITY_DEFAULT_PROFILE && $config->get(
                        'profile_multiprofile'
                    ) ? true : false;

                    // @todo: configurable width?
                    $imageMaxWidth = 160;

                    // Get a hash for the file name.
                    $fileName = JApplicationHelper::getHash($file['tmp_name'] . time());
                    $hashFileName = JString::substr($fileName, 0, 24);

                    //@todo: configurable path for avatar storage?
                    $config = CFactory::getConfig();
                    $storage = JPATH_ROOT . '/' . $config->getString('imagefolder') . '/avatar';
                    $storageImage = $storage . '/' . $hashFileName . CImageHelper::getExtension($file['type']);
                    $storageThumbnail = $storage . '/thumb_' . $hashFileName . CImageHelper::getExtension(
                            $file['type']
                        );
                    $image = $config->getString(
                            'imagefolder'
                        ) . '/avatar/' . $hashFileName . CImageHelper::getExtension($file['type']);
                    $thumbnail = $config->getString(
                            'imagefolder'
                        ) . '/avatar/' . 'thumb_' . $hashFileName . CImageHelper::getExtension($file['type']);

                    $userModel = CFactory::getModel('user');

                    // Generate full image
                    if (!CImageHelper::resizeProportional(
                        $file['tmp_name'],
                        $storageImage,
                        $file['type'],
                        $imageMaxWidth
                    )
                    ) {
                        $mainframe->enqueueMessage(
                            JText::sprintf('COM_COMMUNITY_ERROR_MOVING_UPLOADED_FILE', $storageImage),
                            'error'
                        );
                    }

                    // Generate thumbnail
                    if (!CImageHelper::createThumb($file['tmp_name'], $storageThumbnail, $file['type'])) {
                        $mainframe->enqueueMessage(
                            JText::sprintf('COM_COMMUNITY_ERROR_MOVING_UPLOADED_FILE', $storageThumbnail),
                            'error'
                        );
                    }

                    if ($useWatermark) {
                        if (!JFolder::exists(JPATH_ROOT . '/images/watermarks/original')) {
                            JFolder::create(JPATH_ROOT . '/images/watermarks/original');
                        }
                        // @rule: Before adding the watermark, we should copy the user's original image so that when the admin tries to reset the avatar,
                        // it will be able to grab the original picture.
                        JFile::copy(
                            $storageImage,
                            JPATH_ROOT . '/images/watermarks/original/' . md5(
                                $my->id . '_avatar'
                            ) . CImageHelper::getExtension($file['type'])
                        );
                        JFile::copy(
                            $storageThumbnail,
                            JPATH_ROOT . '/images/watermarks/original/' . md5(
                                $my->id . '_thumb'
                            ) . CImageHelper::getExtension($file['type'])
                        );

                        $multiprofile = JTable::getInstance('MultiProfile', 'CTable');
                        $multiprofile->load($profileType);

                        if ($multiprofile->watermark) {
                            $watermarkPath = JPATH_ROOT . '/' . CString::str_ireplace(
                                    '/',
                                    '/',
                                    $multiprofile->watermark
                                );

                            list($watermarkWidth, $watermarkHeight) = getimagesize($watermarkPath);
                            list($avatarWidth, $avatarHeight) = getimagesize($storageImage);
                            list($thumbWidth, $thumbHeight) = getimagesize($storageThumbnail);

                            $watermarkImage = $storageImage;
                            $watermarkThumbnail = $storageThumbnail;

                            // Avatar Properties
                            $avatarPosition = CImageHelper::getPositions(
                                $multiprofile->watermark_location,
                                $avatarWidth,
                                $avatarHeight,
                                $watermarkWidth,
                                $watermarkHeight
                            );

                            // The original image file will be removed from the system once it generates a new watermark image.
                            CImageHelper::addWatermark(
                                $storageImage,
                                $watermarkImage,
                                $file['type'],
                                $watermarkPath,
                                $avatarPosition->x,
                                $avatarPosition->y
                            );

                            //Thumbnail Properties
                            $thumbPosition = CImageHelper::getPositions(
                                $multiprofile->watermark_location,
                                $thumbWidth,
                                $thumbHeight,
                                $watermarkWidth,
                                $watermarkHeight
                            );

                            // The original thumbnail file will be removed from the system once it generates a new watermark image.
                            CImageHelper::addWatermark(
                                $storageThumbnail,
                                $watermarkThumbnail,
                                $file['type'],
                                $watermarkPath,
                                $thumbPosition->x,
                                $thumbPosition->y
                            );

                            $my->set('_watermark_hash', $multiprofile->watermark_hash);
                            $my->save();
                        }
                    }
                    // Since this is a new registration, we definitely do not want to remove the old image.
                    $removeOldImage = false;

                    $userModel->setImage($my->id, $image, 'avatar', $removeOldImage);
                    $userModel->setImage($my->id, $thumbnail, 'thumb', $removeOldImage);

                    // Update the user object so that the profile picture gets updated.
                    $my->set('_avatar', $image);
                    $my->set('_thumb', $thumbnail);
                }
            }
        }

        echo $view->get(__FUNCTION__);
    }

    public function registerSucess()
    {
        // @rule: Clear any existing temporary session.
        $session = JFactory::getSession();
        $session->clear('tmpuser');
        $session->clear('JS_REG_TOKEN');

        $view = $this->getView('register');
        echo $view->get(__FUNCTION__);
    }

    public function sendEmail($type, $user, $password = null, $requireApproval = false)
    {
        $mainframe = JFactory::getApplication();
        $config = CFactory::getConfig();
        $modelRegister = $this->getModel('register');

        $sitename = $mainframe->get('sitename');
        $mailfrom = $mainframe->get('mailfrom');
        $fromname = $mainframe->get('fromname');
        $siteURL = JURI::base();

        $name = $user->get('name');
        $email = $user->get('email');
        $username = $user->get('username');

        $com_user_config = JComponentHelper::getParams('com_users');
        $com_user_activation_type = $com_user_config->get('useractivation');

        if (is_null($password)) {
            $password = $user->get('password');
        }

        //Disallow control chars in the email
        $password = preg_replace('/[\x00-\x1F\x7F]/', '', $password);

        $params = JComponentHelper::getParams('com_users');
        if ($params->get('sendpassword', 1) == 0) {
            $password = '***';
        }

        // Load Super Administrator email list
        $rows = $modelRegister->getSuperAdministratorEmail();

        //getting superadmin email address.
        if (!$mailfrom || !$fromname) {
            foreach ($rows as $row) {
                if ($row->sendEmail) {
                    $fromname = $row->name;
                    $mailfrom = $row->email;
                    break;
                }
            }

            //if still empty, then we just pick one of the admin email
            if (!$mailfrom || !$fromname) {
                $fromname = $rows[0]->name;
                $mailfrom = $rows[0]->email;
            }
        }

        $subject = JText::sprintf('COM_COMMUNITY_ACCOUNT_DETAILS_FOR', $name, $sitename);
        $subject = html_entity_decode($subject, ENT_QUOTES);

        $baseUrl = JUri::base();
        $activationURL = $baseUrl . 'index.php?option=' . COM_COMMUNITY_NAME . '&view=register&task=activate&' . ACTIVATION_KEYNAME . '=' . $user->get(
                'activation'
            );

        switch ($type) {
            case 'registration' :

                // This section will only be called when there are no custom fields created and we just proceed like how Joomla
                // registers a user.
                if ($requireApproval || $com_user_activation_type == 1) {
                    $message = JText::sprintf(
                        'COM_COMMUNITY_EMAIL_REGISTRATION_REQUIRES_ACTIVATION',
                        $name,
                        $sitename,
                        $activationURL,
                        $siteURL,
                        $username,
                        $password
                    );

                    //@since 4.1, no need to send email because the user will still get it when the registration is complete to avoid multiple email, just in case if this is needed, just uncomment the return;
                    return;
                } else {
                    $message = JText::sprintf(
                        'COM_COMMUNITY_EMAIL_REGISTRATION',
                        $name,
                        $sitename,
                        $username,
                        $password
                    );
                }

                break;
            case 'registration_uncomplete' :
                $subject = JText::sprintf('COM_COMMUNITY_ACCOUNT_DETAILS_FOR_WELCOME', $sitename);
                $subject = html_entity_decode($subject, ENT_QUOTES);

                if ($requireApproval || $com_user_activation_type == 2) {
                    $message = JText::sprintf(
                        'COM_COMMUNITY_EMAIL_REGISTRATION_ACCOUNT_DETAILS_REQUIRES_ACTIVATION',
                        $name,
                        $sitename,
                        $username,
                        $password
                    );
                } else {
                    $message = JText::sprintf(
                        'COM_COMMUNITY_EMAIL_REGISTRATION_ACCOUNT_DETAILS',
                        $name,
                        $sitename,
                        $username,
                        $password
                    );
                }

                break;
            case 'registration_complete' :

                if ($requireApproval || $com_user_activation_type == 2) {
                    // if approval is required, send an email to both admin and user, admin - to activate the user, user - to wait for admin approval

                    /* @since 4.1, there is a new requirement to send the activation to user to make sure the email is valid first before letting admin verify the account
                     *
                     * so we will make the user activate first but still in disabled state
                     *
                     * */

                    $activationURL = $baseUrl . 'index.php?option=' . COM_COMMUNITY_NAME . '&view=register&task=verifyemail&' . ACTIVATION_KEYNAME . '=' . $user->get(
                            'activation'
                        );
                    $message = JText::sprintf(
                        'COM_COMMUNITY_EMAIL_REGISTRATION_COMPLETED_REQUIRES_ADMIN_ACTIVATION',
                        $name,
                        $sitename,
                        $activationURL
                    );
                } else {
                    $message = JText::sprintf(
                        'COM_COMMUNITY_EMAIL_REGISTRATION_COMPLETED_REQUIRES_ACTIVATION',
                        $name,
                        $sitename,
                        $activationURL,
                        $siteURL
                    );
                }

                break;
            case 'resend_activation' :

                if ($config->get('activationresetpassword')) {
                    $message = JText::sprintf(
                        'COM_COMMUNITY_ACTIVATION_MSG_WITH_PWD',
                        $name,
                        $sitename,
                        $activationURL,
                        $siteURL,
                        $username,
                        $password
                    );
                } else {
                    $message = JText::sprintf(
                        'COM_COMMUNITY_ACTIVATION_MSG',
                        $name,
                        $sitename,
                        $activationURL,
                        $siteURL
                    );
                }
                break;
        }

        $message = html_entity_decode($message, ENT_QUOTES);
        $sendashtml = false;
        $copyrightemail = JString::trim($config->get('copyrightemail'));

        // this is used to send the username and password email if the settings of user configuration -> send username and password is enabled from the backend
        if ($type == 'registration_uncomplete' && $com_user_config->get('sendpassword')) {
            //check if HTML emails are set to ON
            if ($config->get('htmlemail')) {
                $sendashtml = true;
                $tmpl = new CTemplate();
                $message = CString::str_ireplace(array("\r\n", "\r", "\n"), '<br />', $message);

                $tmpl->set('name', $name);
                $tmpl->set('email', $email);

                $message = $tmpl->set(
                    'unsubscribeLink',
                    CRoute::getExternalURL('index.php?option=com_community&view=profile&task=email'),
                    false
                )
                    ->set('content', $message)
                    ->set('copyrightemail', $copyrightemail)
                    ->set('sitename', $config->get('sitename'))
                    ->set('recepientemail',$email)
                    ->fetch('email.html');
            }
            $mail = JFactory::getMailer();
            $mail->sendMail($mailfrom, $fromname, $email, $subject, $message, $sendashtml);
        }

        // Send email to user
        //if ( ! ($type == 'registration_complete' && !$requireApproval && !$needActivation))
        if ($type != 'registration_uncomplete' && ($com_user_activation_type != 0 || $requireApproval)) {
            if ($requireApproval || $com_user_activation_type == 2) {
                $subject = JText::sprintf('COM_COMMUNITY_USER_REGISTERED_WAITING_APPROVAL_TITLE', $sitename);
            }

            //check if HTML emails are set to ON
            if ($config->get('htmlemail')) {
                $sendashtml = true;
                $tmpl = new CTemplate();
                $message = CString::str_ireplace(array("\r\n", "\r", "\n"), '<br />', $message);

                $tmpl->set('name', $name);
                $tmpl->set('email', $email);

                $message = $tmpl->set(
                    'unsubscribeLink',
                    CRoute::getExternalURL('index.php?option=com_community&view=profile&task=email'),
                    false
                )
                    ->set('content', $message)
                    ->set('copyrightemail', $copyrightemail)
                    ->set('sitename', $config->get('sitename'))
                    ->set('recepientemail',$email)
                    ->fetch('email.html');
            }

            $mail = JFactory::getMailer();
            $mail->sendMail($mailfrom, $fromname, $email, $subject, $message, $sendashtml);
        }

        // after registration(even without any activation yet) it will trigger a message to admin
        if ($type == 'registration_complete') {

            //reset the subject just in case it has been set for the user above
            $subject = JText::sprintf('COM_COMMUNITY_ACCOUNT_DETAILS_FOR', $name, $sitename);
            $subject = html_entity_decode($subject, ENT_QUOTES);

            foreach ($rows as $row) {
                if ($row->sendEmail) {
                    $model = CFactory::getModel('Profile');
                    $profileTypes = $model->getProfileTypes();
                    $message2 = JText::sprintf(
                        JText::_('COM_COMMUNITY_SEND_MSG_ADMIN'),
                        $row->name,
                        $sitename,
                        $name,
                        $email,
                        $username
                    );

                    $message2 = html_entity_decode($message2, ENT_QUOTES);

                    //check if HTML emails are set to ON
                    if ($config->get('htmlemail')) {
                        $sendashtml = true;
                        $tmpl = new CTemplate();
                        $message2 = CString::str_ireplace(array("\r\n", "\r", "\n"), '<br />', $message2);

                        $tmpl->set('name', $row->name);
                        $tmpl->set('email', $row->email);

                        $message2 = $tmpl->set(
                            'unsubscribeLink',
                            CRoute::getExternalURL('index.php?option=com_community&view=profile&task=privacy'),
                            false
                        )
                            ->set('recepientemail', $row->email)
                            ->set('content', $message2)
                            ->set('copyrightemail', $copyrightemail)
                            ->set('sitename', $config->get('sitename'))
                            ->fetch('email.html');
                    }
                    $mail = JFactory::getMailer();
                    $mail->sendMail($mailfrom, $fromname, $row->email, $subject, $message2, $sendashtml);
                }
            }
        }

    }

    /**
     * Validate registration form
     */
    private function _validateRegister($post = array())
    {
        //check the user infor


        $mainframe = JFactory::getApplication();
        $config = CFactory::getConfig();
        $errMsg = array();
        $data = array();

        if (!empty($post)) {
            //manual array_walk to trim
            foreach ($post as $key => $value) {
                if (is_array($value)) {
                    $data[$key] = $value; // dun do anything here.
                } else {
                    $data[$key] = JString::trim($value);
                }
            } //end of
        }

        $isUseFirstLastName = $data['isUseFirstLastName'];

        if ($isUseFirstLastName) {
            if (empty($data['jsfirstname'])) {
                $errMsg[] = JText::_('COM_COMMUNITY_FIELD_ENTRY') . ' \'' . JText::_(
                        'COM_COMMUNITY_FIRST_NAME'
                    ) . '\' ' . JText::_('COM_COMMUNITY_IS_EMPTY') . '.';
            }
            if (empty($data['jslastname'])) {
                $errMsg[] = JText::_('COM_COMMUNITY_FIELD_ENTRY') . ' \'' . JText::_(
                        'COM_COMMUNITY_LAST_NAME'
                    ) . '\' ' . JText::_('COM_COMMUNITY_IS_EMPTY') . '.';
            }
        } else {
            if (empty($data['jsname'])) {
                $errMsg[] = JText::_('COM_COMMUNITY_FIELD_ENTRY') . ' \'' . JText::_(
                        'COM_COMMUNITY_NAME'
                    ) . '\' ' . JText::_('COM_COMMUNITY_IS_EMPTY') . '.';
            }
        }

        if (empty($data['jsusername'])) {
            $errMsg[] = JText::_('COM_COMMUNITY_FIELD_ENTRY') . ' \'' . JText::_(
                    'COM_COMMUNITY_USERNAME'
                ) . '\' ' . JText::_('COM_COMMUNITY_IS_EMPTY') . '.';
        }
        if (empty($data['jsemail'])) {
            $errMsg[] = JText::_('COM_COMMUNITY_FIELD_ENTRY') . ' \'' . JText::_(
                    'COM_COMMUNITY_EMAIL'
                ) . '\' ' . JText::_('COM_COMMUNITY_IS_EMPTY') . '.';
        }
        if (empty($data['jspassword'])) {
            $errMsg[] = JText::_('COM_COMMUNITY_FIELD_ENTRY') . ' \'' . JText::_(
                    'COM_COMMUNITY_PASSWORD'
                ) . '\' ' . JText::_('COM_COMMUNITY_IS_EMPTY') . '.';
        }
        if (empty($data['jspassword2'])) {
            $errMsg[] = JText::_('COM_COMMUNITY_FIELD_ENTRY') . ' \'' . JText::_(
                    'COM_COMMUNITY_VERIFY_PASSWORD'
                ) . '\' ' . JText::_('COM_COMMUNITY_IS_EMPTY') . '.';
        }

        if (!empty($data['jsusername'])) {
            if (!CValidateHelper::username($data['jsusername'])) {
                $errMsg[] = JText::_('COM_COMMUNITY_IMPROPER_USERNAME');
            }
        }

        if ($config->get('enableterms')) {
            if (empty($data['tnc'])) {
                $errMsg[] = JText::_('COM_COMMUNITY_REGISTER_ACCEPT_TNC');
            }
        }

        return $errMsg;
    }

    /**
     * Validate registration form
     */
    private function _validateProfile($post = array())
    {
        $mainframe = JFactory::getApplication();
        $pModel = $this->getModel('profile');
        $errMsg = array();
        $data = array();

        if (!empty($post)) {
            //manual array_walk to trim
            foreach ($post as $key => $value) {
                if (is_array($value)) {
                    $data[$key] = $value; // dun do anything here.
                } else {
                    $data[$key] = JString::trim($value);
                }
            } //end of
        }

        //get all published custom field for profile
        $filter = array('published' => '1', 'registration' => '1');
        $groups = $pModel->getAllFields($filter);

        // Bind result from previous post into the field object
        if (!empty($data)) {
            foreach ($groups as $group) {
                $fields = $group->fields;

                for ($i = 0; $i < count($fields); $i++) {
                    $fieldid = $fields[$i]->id;
                    $fieldname = $fields[$i]->name;
                    $isRequired = $fields[$i]->required;
                    $fieldType = $fields[$i]->type;

                    if ($isRequired == 1) {
                        if ($fieldType == 'date') {
                            if (JString::trim($data['field' . $fieldid][0]) == '' || JString::trim(
                                    $data['field' . $fieldid][2]
                                ) == ''
                            ) {
                                $errMsg[] = JText::_(
                                        'COM_COMMUNITY_FIELD_ENTRY'
                                    ) . ' \'' . $fieldname . '\' ' . JText::_('COM_COMMUNITY_IS_EMPTY') . '.';
                            }
                        } else {
                            if (empty($data['field' . $fieldid])) {
                                $errMsg[] = JText::_(
                                        'COM_COMMUNITY_FIELD_ENTRY'
                                    ) . ' \'' . $fieldname . '\' ' . JText::_('COM_COMMUNITY_IS_EMPTY') . '.';
                            }
                        } //end if else
                    } //ebd if
                } //end for i
            } //end foreach
        } //end if


        return $errMsg;
    }

    public function lostPassword()
    {

    }

    public function forgotUsername()
    {

    }

    /**
     * First step of registration
     * @param type $cacheable
     * @param type $urlparams
     */
    public function display($cacheable = false, $urlparams = false)
    {
        $this->register();
    }

    /*
     * Leave this function here until it get stable. If someting is wrong, revert
     * the function to use back this one.
     */

    public function ajaxCheckUserName($username = '', $current = '')
    {
        $json = array();
        $isInvalid = false;
        $msg = '';

        if (!empty($current) && !empty($username) && ($username == $current)) {
            $json['success'] = true;
            die( json_encode($json) );
        }

        if (!empty($username)) {
            if (!CValidateHelper::username($username)) {
                $json['error'] = JText::_('COM_COMMUNITY_IMPROPER_USERNAME');
                die( json_encode($json) );
            }

            $model = $this->getModel('register');
            $ipaddress = isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR'];
            if ( $model->isUserNameExists( array('username' => $username, 'ip' => $ipaddress) ) ) {
                $json['error'] = JText::sprintf('COM_COMMUNITY_USERNAME_EXIST', $username);
                die( json_encode($json) );
            }
        }

        $json['success'] = true;
        die( json_encode($json) );
    }

    public function ajaxCheckEmail($email = '')
    {
        $ipaddress = isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR'];
        $model = $this->getModel('register');

        $isValid = false;

        if (!empty($email)) {
            $isExists = $model->isEmailExists(array('email' => $email, 'ip' => $ipaddress));
            $isValid = $isExists ? false : true;
            $msg = JText::sprintf('COM_COMMUNITY_EMAIL_EXIST', $email);
        }

        if ($isValid && !$model->isEmailAllowed($email)) {
            $isValid = false;
            $msg = JText::sprintf('COM_COMMUNITY_EMAILDOMAIN_DISALLOWED', $email);
        }

        if ($isValid && $model->isEmailDenied($email)) {
            $isValid = false;
            $msg = JText::sprintf('COM_COMMUNITY_EMAILDOMAIN_DENIED', $email);
        }

        $json = array();
        if (!$isValid) {
            $json['error'] = ($msg) ? $msg : ' ';
        } else {
            $json['success'] = true;
        }

        die(json_encode($json));
    }

    public function ajaxSetMessage($fieldName, $txtLabel = '', $strMessage, $strParam = '', $strParam2 = '')
    {
        $filter = JFilterInput::getInstance();
        $fieldName = $filter->clean($fieldName, 'string');
        $txtLabel = $filter->clean($txtLabel, 'string');
        $txtLabel = str_replace('Â', '', $txtLabel);
        $strParam = $filter->clean($strParam, 'string');
        $strParam2 = $filter->clean($strParam2, 'string');
        // $strParam pending filter

        $objResponse = new JAXResponse();

        $langMsg = '';

        if (!empty($strMessage)) {
            if ($strParam != '' && $strParam2 != '') {
                $langMsg = (empty($strParam)) ? JText::_($strMessage) : JText::sprintf(
                    $strMessage,
                    $strParam,
                    $strParam2
                );
            } else {
                $langMsg = (empty($strParam)) ? JText::_($strMessage) : JText::sprintf($strMessage, $strParam);
            }
        }

        $myLabel = ($txtLabel == 'Field') ? JText::_('COM_COMMUNITY_FIELD') : $txtLabel;

        $langMsg = (empty($txtLabel)) ? $langMsg : $myLabel . ' ' . $langMsg;

        $objResponse->addScriptCall('joms.jQuery("#err' . $fieldName . 'msg").html("<br />' . $langMsg . '");');
        $objResponse->addScriptCall('joms.jQuery("#err' . $fieldName . 'msg").css("color","red");');
        $objResponse->addScriptCall('joms.jQuery("#err' . $fieldName . 'msg").show();');

        $json = array();
        $json['message'] = $langMsg;
        die( json_encode( $json ) );

        // return $objResponse->sendResponse();
    }

    public function ajaxShowTnc($fb = false)
    {
        $objResponse = new JAXResponse();
        $config = CFactory::getConfig();
        $html = $config->get('registrationTerms');
        $actions = '';

        $json = array(
            'title' => JText::_('COM_COMMUNITY_TERMS_AND_CONDITION'),
            'html'  => nl2br( $html )
        );

        die( json_encode($json) );
    }

    public function ajaxGenerateAuthKey()
    {
        $mySess = JFactory::getSession();

        $newToken = $mySess->getToken(true);
        $mySess->set('JS_REG_TOKEN', $newToken);

        $json = array();

        if (!$mySess->has('JS_REG_TOKEN')) {
            $json['error'] = JText::_('COM_COMMUNITY_REGISTER_AUTH_ERROR');
            die(json_encode($json));
        }

        $authKey = JSession::getFormToken();
        $model = $this->getModel('register');

        if ($model->addAuthKey($authKey)->return_value['addAuthKey']) {
            $json['authKey'] = $authKey;
        } else {
            $json['error'] = JText::_('COM_COMMUNITY_REGISTER_AUTH_ERROR');
        }

        die(json_encode($json));
    }

    public function ajaxAssignAuthKey()
    {
        $objResponse = new JAXResponse();

        $authKey = "";
        $ipaddress = isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR'];

        $mySess = JFactory::getSession();
        $token = $mySess->get('JS_REG_TOKEN', '');

        $model = $this->getModel('register');
        $authKey = $model->getAuthKey($token, $ipaddress);

        $objResponse->addScriptCall("joms.registrations.assignAuthKey('jomsForm','authkey','" . $authKey . "');");
        $objResponse->addScriptCall("joms.jQuery('#authenticate').val('1');");
        $objResponse->addScriptCall("joms.jQuery('#btnSubmit').click();");

        $json = array();
        $json['authKey'] = $authKey;
        die( json_encode($json) );

        // return $objResponse->sendResponse();
    }


    //@since 4.1 this is used to verify the email of user that has registered
    public function verifyEmail(){
        $mainframe = JFactory::getApplication();
        $input = $mainframe->input;
        $token = $input->getAlnum('token');

        $db = JFactory::getDbo();
        $query = "SELECT id FROM ".$db->quoteName('#__users')." WHERE ".$db->quoteName('activation')." = ".$db->quote($token);
        $db->setQuery($query);
        $result = $db->loadResult();

        if($result){
            //if the email exists, send an email to admin to activate this user.

            $sitename = $mainframe->get('sitename');
            $mailfrom = $mainframe->get('mailfrom');
            $fromname = $mainframe->get('fromname');
            $siteURL = JURI::base();

            $user = JFactory::getUser($result);
            $name = $user->get('name');
            $email = $user->get('email');
            $username = $user->get('username');

            $modelRegister = $this->getModel('register');
            // Load Super Administrator email list
            $rows = $modelRegister->getSuperAdministratorEmail();

            $config = CFactory::getConfig();
            $copyrightemail = JString::trim($config->get('copyrightemail'));

            $activationURL = $siteURL . 'index.php?option=' . COM_COMMUNITY_NAME . '&view=register&task=activate&' . ACTIVATION_KEYNAME . '=' . $user->get(
                    'activation'
                );

            $subject = JText::sprintf('COM_COMMUNITY_USER_REGISTERED_NEEDS_APPROVAL_TITLE', $sitename);
            foreach ($rows as $row) {
                if ($row->sendEmail) {
                    $message2 = JText::sprintf(
                        JText::_('COM_COMMUNITY_USER_REGISTERED_NEEDS_APPROVAL'),
                        $row->name,
                        $sitename,
                        $activationURL,
                        $activationURL,
                        $name,
                        $email,
                        $username
                    );

                    $message2 = html_entity_decode($message2, ENT_QUOTES);

                    //check if HTML emails are set to ON
                    if ($config->get('htmlemail')) {
                        $sendashtml = true;
                        $tmpl = new CTemplate();
                        $message2 = CString::str_ireplace(array("\r\n", "\r", "\n"), '<br />', $message2);

                        $tmpl->set('name', $name);
                        $tmpl->set('email', $row->email);

                        $message2 = $tmpl->set(
                            'unsubscribeLink',
                            CRoute::getExternalURL('index.php?option=com_community&view=profile&task=privacy'),
                            false
                        )
                            ->set('content', $message2)
                            ->set('copyrightemail', $copyrightemail)
                            ->set('sitename', $config->get('sitename'))
                            ->set('recepientemail',$email)
                            ->fetch('email.html');
                    }
                    $mail = JFactory::getMailer();
                    $mail->sendMail($mailfrom, $fromname, $row->email, $subject, $message2, $sendashtml);
                }
            }

            $mainframe->redirect(
                'index.php?option=com_community',
                JText::_('COM_COMMUNITY_REGISTRATION_VERIFY_SUCCESS'),
                "message"
            );
        }else{

            // Redirect back to the homepage.
            $mainframe->redirect("index.php", JText::_('JINVALID_TOKEN'), "warning");

            return false;
        }
    }

    public function activate()
    {

        $mainframe = JFactory::getApplication();
        $input = $mainframe->input;

        $model = $this->getModel('register');
        $token = $input->getAlnum('token');

        // Check that the token is in a valid format.
        if ($token === null || strlen($token) !== 32) {
            JFactory::getApplication()->enqueueMessage(JText::_('JINVALID_TOKEN'), 'error');
            return false;
        }

        // Attempt to activate the user.
        $useractivation = $model->activate($token);

        if ($useractivation === false) {
            // Redirect back to the homepage.
            $mainframe->redirect("index.php", JText::_('COM_COMMUNITY_ERROR'), "warning");
            return false;
        }

        // Redirect to the login screen.
        if ($useractivation == 0) {
            $mainframe->redirect(CRoute::_(
                'index.php?option=com_community'),
                JText::_('COM_COMMUNITY_REGISTRATION_ACTIVATE_SUCCESS'),
                "message"
            );
        } elseif ($useractivation == 1) {
            $mainframe->redirect(CRoute::_(
                'index.php?option=com_community'),
                JText::_('COM_COMMUNITY_REGISTRATION_ADMINACTIVATE_SUCCESS'),
                "message"
            );
        }

        return true;
    }

    public function activation()
    {
        $view = $this->getView('register');
        echo $view->get('activation');
    }

    /**
     * Do resend activation
     * @return boolean
     */
    public function activationResend()
    {
        // Check for request forgeries
        JSession::checkToken() or jexit('Invalid Token');

        jimport('joomla.user.helper');

        $mainframe = JFactory::getApplication();
        $jinput = $mainframe->input;
        $config = CFactory::getConfig();
        $usersConfig = JComponentHelper::getParams('com_users');
        $regModel = $this->getModel('register');
        $jsEmail = $jinput->request->get('jsemail', '', 'STRING');

        $isExists = false;

        /* Email exists checking */
        if (!empty($jsEmail)) {
            $isExists = $regModel->isEmailExists(array('email' => $jsEmail));
        }

        /* This email is not exists than of course we do return error */
        if (!$isExists) {
            $mainframe->enqueueMessage(JText::sprintf('COM_COMMUNITY_ACTIVATION_EMAIL_INVALID', $jsEmail), 'error');
            $this->activation();
            return false;
        }

        //if user is already 'unblock', then no need to process email activation resend.
        $regUser = $regModel->getUserByEmail($jsEmail);
        $user = CFactory::getUser($regUser->id);

        if ($user->block != '1') {
            $mainframe->enqueueMessage(JText::sprintf('COM_COMMUNITY_ACTIVATION_ALREADY_ACTIVATED', $jsEmail));
            $this->activation();
            return false;
        }

        //we must check if this profile can be activated or not (profile type)
        if($config->get('profile_multiprofile')){
            $profileType    = JTable::getInstance( 'MultiProfile' , 'CTable' );
            $profileType->load( $user->getProfileType() );
            if(isset($profileType->approvals) && $profileType->approvals){
                $mainframe->enqueueMessage(JText::_('COM_COMMUNITY_ACTIVATION_WAIT_FOR_ADMIN_CONFIRMATION'));
                return false;
            }
        }else{
            $com_user_config = JComponentHelper::getParams('com_users');
            $com_user_activation_type = $com_user_config->get('useractivation');

            if($com_user_activation_type == 2){
                $mainframe->enqueueMessage(JText::_('COM_COMMUNITY_ACTIVATION_WAIT_FOR_ADMIN_CONFIRMATION'));
                return false;
            }
        }

        $isActivated = ($user->activation == '') ? true : false;

        /* We only do generate and resend activation if user is not activated before */
        if ($isActivated == false) {
            //if user activation disabled, show message to user.
            $useractivation = $usersConfig->get('useractivation');

            if ($useractivation == '0') {
                $mainframe->enqueueMessage(JText::_('COM_COMMUNITY_ACTIVATION_DISABLED'));
                $this->activation();
                return false;
            }

            $password = null;

            if ($config->get('activationresetpassword')) {
                $password = JUserHelper::genRandomPassword();
                $salt = JUserHelper::genRandomPassword(32);
                $crypt = JUserHelper::getCryptedPassword($password, $salt);
                $password = $crypt . ':' . $salt;

                $user->set('password', $password);
            }

            $user->set('activation', JApplicationHelper::getHash(JUserHelper::genRandomPassword()));

            if (!$user->save()) {
                $mainframe->enqueueMessage(
                    JText::sprintf('COM_COMMUNITY_ACTIVATION_FAILED', $jsEmail, $user->getError())
                );
                $this->activation();
                return false;
            }

            $this->sendEmail('resend_activation', $user, $password);

            $mainframe->enqueueMessage(JText::sprintf('COM_COMMUNITY_ACTIVATION_SUCCESS', $jsEmail));
            $this->activation();
        } else {
            $mainframe->enqueueMessage(JText::sprintf('COM_COMMUNITY_ACTIVATION_ALREADY_ACTIVATED', $jsEmail));
            $this->activation();
            return false;
        }
    }

    public function ajaxCheckPassLength($lenght, $fieldName)
    {

        $params = JComponentHelper::getParams('com_users');
        $objResponse = new JAXResponse();

        if ( $lenght < $params->get('minimum_length', 6) ) {
            $json = array( 'error' => JText::sprintf('COM_COMMUNITY_PASSWORD_TOO_SHORT', $params->get('minimum_length', 6)) );
        } else {
            $json = array( 'success' => true );
        }

        die( json_encode($json) );
    }

    public function ajaxCheckPass($value = '')
    {

        $params = JComponentHelper::getParams('com_users');
        $error = array();

        if (!empty($params))
        {
            $minimumLengthp = $params->get('minimum_length', 6);
            $minimumIntegersp = $params->get('minimum_integers');
            $minimumSymbolsp = $params->get('minimum_symbols');
            $minimumUppercasep = $params->get('minimum_uppercase');

            empty($minimumLengthp) ? : $minimumLength = (int) $minimumLengthp;
            empty($minimumIntegersp) ? : $minimumIntegers = (int) $minimumIntegersp;
            empty($minimumSymbolsp) ? : $minimumSymbols = (int) $minimumSymbolsp;
            empty($minimumUppercasep) ? : $minimumUppercase = (int) $minimumUppercasep;
        }

        $valueLength = strlen($value);

        if ($valueLength > 4096)
        {
            $error[] = JText::_('COM_COMMUNITY_PASSWORD_TOO_LONG');
        }

        $valueTrim = trim($value);

        if (strlen($valueTrim) != $valueLength)
        {
            $error[] = JText::_('COM_COMMUNITY_SPACES_IN_PASSWORD');
        }

        if (!empty($minimumIntegers))
        {
            $nInts = preg_match_all('/[0-9]/', $value, $imatch);

            if ($nInts < $minimumIntegers)
            {
                $error[] = JText::plural('COM_COMMUNITY_NOT_ENOUGH_INTEGERS_N', $minimumIntegers);
            }
        }

        if (!empty($minimumSymbols))
        {
            $nsymbols = preg_match_all('[\W]', $value, $smatch);

            if ($nsymbols < $minimumSymbols)
            {
                $error[] = JText::plural('COM_COMMUNITY_NOT_ENOUGH_SYMBOLS_N', $minimumSymbols);
            }
        }

        if (!empty($minimumUppercase))
        {
            $nUppercase = preg_match_all("/[A-Z]/", $value, $umatch);

            if ($nUppercase < $minimumUppercase)
            {
                $error[] = JText::plural('COM_COMMUNITY_NOT_ENOUGH_UPPERCASE_LETTERS_N', $minimumUppercase);
            }
        }

        if (!empty($minimumLength))
        {
            if (strlen((string) $value) < $minimumLength)
            {
                $error[] = JText::plural('COM_COMMUNITY_PASSWORD_TOO_SHORT_N', $minimumLength);
            }
        }

        if ( count($error) > 0 ) {
            $json = array( 'error' => implode(PHP_EOL, $error) );
        } else {
            $json = array( 'success' => true );
        }

        die( json_encode($json) );

    }

}
