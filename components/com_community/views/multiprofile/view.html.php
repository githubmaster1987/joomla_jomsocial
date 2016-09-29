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
jimport('joomla.application.component.view');

if (!class_exists('CommunityViewMultiprofile')) {

    class CommunityViewMultiprofile extends CommunityView {

        public function _addSubmenu() {
            $config = CFactory::getConfig();

            $this->addSubmenuItem('index.php?option=com_community&view=profile&task=uploadAvatar', JText::_('COM_COMMUNITY_CHANGE_AVATAR'));

            if ($config->get('enableprofilevideo')) {
                $this->addSubmenuItem('index.php?option=com_community&view=profile&task=linkVideo', JText::_('COM_COMMUNITY_VIDEOS_EDIT_PROFILE_VIDEO'));
            }

            $this->addSubmenuItem('index.php?option=com_community&view=profile&task=edit', JText::_('COM_COMMUNITY_PROFILE_EDIT'));
            $this->addSubmenuItem('index.php?option=com_community&view=profile&task=editDetails', JText::_('COM_COMMUNITY_EDIT_DETAILS'));
            $this->addSubmenuItem('index.php?option=com_community&view=profile&task=privacy', JText::_('COM_COMMUNITY_PROFILE_PRIVACY_EDIT'));
            $this->addSubmenuItem('index.php?option=com_community&view=profile&task=preferences', JText::_('COM_COMMUNITY_EDIT_PREFERENCES'));

            if ($config->get('profile_deletion')) {
                $this->addSubmenuItem('index.php?option=com_community&view=profile&task=deleteProfile', JText::_('COM_COMMUNITY_DELETE_PROFILE'), '', SUBMENU_RIGHT);
            }
        }

        public function display($tpl = null) {
            $this->changeProfile();
        }

        /**
         * Allows user to change their profile type
         * */
        public function changeProfile() {

            /**
             * Opengraph
             */
            CHeadHelper::setType('website', JText::_('COM_COMMUNITY_MULTIPROFILE_CHANGE_TYPE'));

            $my = CFactory::getUser();

            $this->addPathway(JText::_('COM_COMMUNITY_PROFILE'), CRoute::_('index.php?option=com_community&view=profile&userid=' . $my->id));
            $this->addPathway(JText::_('COM_COMMUNITY_MULTIPROFILE_CHANGE_TYPE'));
            $model = CFactory::getModel('Profile');
            $tmp = $model->getProfileTypes();
            $profileTypes = array();

            $showNotice = false;

            foreach ($tmp as $profile) {
                $table = JTable::getInstance('MultiProfile', 'CTable');
                $table->load($profile->id);

                if ($table->approvals)
                    $showNotice = true;

                $profileTypes[] = $table;
            }

            $tmpl = new CTemplate();
            echo $tmpl->set('showNotice', $showNotice)
                    ->set('profileTypes', $profileTypes)
                    ->set('default', $my->getProfileType())
                    ->set('message', JText::_('COM_COMMUNITY_MULTIPROFILE_SWITCH_INFO'))
                    ->fetch('register.profiletype');
        }

        /**
         * Once a user changed their profile, request them to update their profile
         * */
        public function updateProfile() {
            $jinput = JFactory::getApplication()->input;
            /**
             * Opengraph
             */
            CHeadHelper::setType('website', JText::_('COM_COMMUNITY_MULTIPROFILE_UPDATE'));

            $profileType = $jinput->get('profileType', '');
            $my = CFactory::getUser();

            $this->addPathway(JText::_('COM_COMMUNITY_PROFILE'), CRoute::_('index.php?option=com_community&view=profile&userid=' . $my->id));
            $this->addPathway(JText::_('COM_COMMUNITY_MULTIPROFILE_CHANGE_TYPE'), CRoute::_('index.php?option=com_community&view=multiprofile&task=changeprofile'));
            $this->addPathway(JText::_('COM_COMMUNITY_MULTIPROFILE_UPDATE'));

            $model = CFactory::getModel('profile');
            $profileType = $jinput->get('profileType', 0);

            // Get all published custom field for profile
            $filter = array('published' => '1', 'registration' => '1');
//		$fields		= $model->getAllFields( $filter , $profileType );
            $result = $model->getEditableProfile($my->id, $profileType);

            $empty_html = array();
            $post = $jinput->post->getArray();

            // Bind result from previous post into the field object
            if (!empty($post)) {

                foreach ($fields as $group) {
                    $field = $group->fields;
                    for ($i = 0; $i < count($field); $i++) {
                        $fieldid = $field[$i]->id;
                        $fieldType = $field[$i]->type;

                        if (!empty($post['field' . $fieldid])) {
                            if (is_array($post['field' . $fieldid])) {
                                if ($fieldType != 'date') {
                                    $values = $post['field' . $fieldid];
                                    $value = '';
                                    foreach ($values as $listValue) {
                                        $value .= $listValue . ',';
                                    }
                                    $field[$i]->value = $value;
                                } else {
                                    $field[$i]->value = $post['field' . $fieldid];
                                }
                            } else {
                                $field[$i]->value = $post['field' . $fieldid];
                            }
                        }
                    }
                }
            }

            $js = 'assets/validate-1.5.min.js';
            CFactory::attach($js, 'js');

            $profileType = $jinput->get('profileType', 0);

            //CFactory::load( 'libraries' , 'profile' );
            $tmpl = new CTemplate();
            echo $tmpl->set('fields', $result['fields'])
                    ->set('profileType', $profileType)
                    ->fetch('multiprofile.update');
        }

        /**
         * Displays message for the user when their profile is updated.
         * */
        public function profileUpdated() {
            $jinput = JFactory::getApplication()->input;
            $profileType = $jinput->get('profileType', COMMUNITY_DEFAULT_PROFILE);
            $multiprofile = JTable::getInstance('Multiprofile', 'CTable');
            $multiprofile->load($profileType);
            //CFactory::load( 'helper' , 'owner' );

            $tmpl = new CTemplate();
            echo $tmpl->set('multiprofile', $multiprofile)
                    ->set('isCommunityAdmin', COwnerHelper::isCommunityAdmin())
                    ->fetch('multiprofile.message');
        }

    }

}
