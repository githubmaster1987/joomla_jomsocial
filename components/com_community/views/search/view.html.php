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
jimport('joomla.html.html');

if (!class_exists('CommunityViewSearch')) {

    class CommunityViewSearch extends CommunityView {

        public function _addSubmenu() {
            $mySQLVer = 0;
            if (JFile::exists(JPATH_COMPONENT . '/libraries/advancesearch.php')) {
                require_once (JPATH_COMPONENT . '/libraries/advancesearch.php');
                $mySQLVer = CAdvanceSearch::getMySQLVersion();
            }

            // Only display related links for guests
            $my = CFactory::getUser();
            $config = CFactory::getConfig();

            if ($my->id == 0) {

//                $tmpl = new CTemplate();
//                $tmpl->set('url', CRoute::_('index.php?option=com_community&view=search'));
//                $html = $tmpl->fetch('search.submenu');
//                $this->addSubmenuItem('index.php?option=com_community&view=search', JText::_('COM_COMMUNITY_SEARCH_FRIENDS'), 'joms.videos.toggleSearchSubmenu(this)', SUBMENU_LEFT, $html);

                if ($mySQLVer >= 4.1 && $config->get('guestsearch'))
                    $this->addSubmenuItem('index.php?option=com_community&view=search&task=advancesearch', JText::_('COM_COMMUNITY_CUSTOM_SEARCH'));
            }
            else {
                $this->addSubmenuItem('index.php?option=com_community&view=search&task=browse', JText::_('COM_COMMUNITY_ALL_MEMBERS'));
               // $this->addSubmenuItem('index.php?option=com_community&view=search', JText::_('COM_COMMUNITY_SEARCH'));
                $tmpl = new CTemplate();
                $tmpl->set('url', CRoute::_('index.php?option=com_community&view=search'));
                if ($mySQLVer >= 4.1)
                    $this->addSubmenuItem('index.php?option=com_community&view=search&task=advancesearch', JText::_('COM_COMMUNITY_CUSTOM_SEARCH'));
            }
        }

        public function showSubmenu($display=true) {
            $this->_addSubmenu();
           return parent::showSubmenu($display);
        }

        public function search($data) {
            $mainframe = JFactory::getApplication();
            $jinput = $mainframe->input;

            /**
             * Opengraph
             */
            CHeadHelper::setType('website', JText::_('COM_COMMUNITY_SEARCH_FRIENDS_TITLE'));

            //$this->showSubMenu();

            $avatarOnly = $jinput->get('avatar', '', 'NONE');

            $this->addPathway(JText::_('COM_COMMUNITY_SEARCH_FRIENDS_TITLE'));

            $my = CFactory::getUser();
            $friendsModel = CFactory::getModel('friends');
            $resultRows = array();

            $pagination = (!empty($data)) ? $data->pagination : '';

            $tmpl = new CTemplate();
            for ($i = 0; $i < count($data->result); $i++) {
                $row = $data->result[$i];
                $user = CFactory::getUser($row->id);
                $row->profileLink = CRoute::_('index.php?option=com_community&view=profile&userid=' . $row->id);
                $row->friendsCount = $user->getFriendCount();
                $isFriend = CFriendsHelper::isConnected($row->id, $my->id);

                $row->user = $user;
                $row->addFriend = ((!$isFriend) && ($my->id != 0) && $my->id != $row->id) ? true : false;

                $resultRows[] = $row;
            }
            $tmpl   ->set('data', $resultRows)
                    ->set('sortings', '')
                    ->set('pagination', $pagination);

            $featured = new CFeatured(FEATURED_USERS);
            $featuredList = $featured->getItemIds();

            $tmpl->set('featuredList', $featuredList);

            $resultHTML = $tmpl->set('isCommunityAdmin', COwnerHelper::isCommunityAdmin())
                    ->set('showFeaturedList', false)
                    ->set('my', $my)
                    ->fetch('people.browse');
            unset($tmpl);

            $searchLinks = parent::getAppSearchLinks('people');

            $tmpl = new CTemplate();
            echo $tmpl->set('avatarOnly', $avatarOnly)
                    ->set('results', $data->result)
                    ->set('resultHTML', $resultHTML)
                    ->set('query', $data->query)
                    ->set('searchLinks', $searchLinks)
                    ->set('submenu', $this->showSubmenu(false))
                    ->fetch('search');
        }

        public function browse($data = null) {
            //require_once (JPATH_COMPONENT . '/libraries/template.php');

            $mainframe = JFactory::getApplication();
            $jinput = $mainframe->input;
            $document = JFactory::getDocument();

            $this->addPathway(JText::_('COM_COMMUNITY_GROUPS_MEMBERS'), '');

            /**
             * Opengraph
             */
            CHeadHelper::setType('website', JText::_('COM_COMMUNITY_GROUPS_MEMBERS'));

            $my = CFactory::getUser();
            $view = CFactory::getView('search');
            $searchModel = CFactory::getModel('search');
            $userModel = CFactory::getModel('user');
            $avatar = CFactory::getModel('avatar');
            $friends = CFactory::getModel('friends');

            $tmpl = new CTemplate();
            $sorted = $jinput->get->get('sort', 'latest', 'STRING');
            $filter = $jinput->get->getWord('filter', 'all');
            $profiletype = $jinput->getInt('profiletype', 0);

            $rows = $searchModel->getPeople($sorted, $filter, $profiletype);

            $sortItems = array(
                'online' => JText::_('COM_COMMUNITY_SORT_ONLINE'),
                'latest' => JText::_('COM_COMMUNITY_SORT_LATEST'),
                'alphabetical' => JText::_('COM_COMMUNITY_SORT_ALPHABETICAL')
            );

            if(CFactory::getConfig()->get('show_featured')){
                $sortItems['featured'] = JText::_('COM_COMMUNITY_SORT_FEATURED');
            }

            $filterItems = array();
            $config = CFactory::getConfig();
            if ($config->get('alphabetfiltering')) {
                $filterItems = array(
                    'all' => JText::_('COM_COMMUNITY_JUMP_ALL'),
                    'abc' => JText::_('COM_COMMUNITY_JUMP_ABC'),
                    'def' => JText::_('COM_COMMUNITY_JUMP_DEF'),
                    'ghi' => JText::_('COM_COMMUNITY_JUMP_GHI'),
                    'jkl' => JText::_('COM_COMMUNITY_JUMP_JKL'),
                    'mno' => JText::_('COM_COMMUNITY_JUMP_MNO'),
                    'pqr' => JText::_('COM_COMMUNITY_JUMP_PQR'),
                    'stu' => JText::_('COM_COMMUNITY_JUMP_STU'),
                    'vwx' => JText::_('COM_COMMUNITY_JUMP_VWX'),
                    'yz' => JText::_('COM_COMMUNITY_JUMP_YZ'),
                    'others' => JText::_('COM_COMMUNITY_JUMP_OTHERS')
                );
            }
            $html = '';
            $totalUser = $userModel->getMembersCount();
            $resultRows = array();
            $alreadyfriend = array();

            // No need to pre-load multiple users at once since $searchModel->getPeople
            // already did
            for ($i = 0; $i < count($rows); $i++) {
                $row = $rows[$i];

                $obj = clone($row);
                $user = CFactory::getUser($row->id);
                $obj->friendsCount = $user->getFriendCount();
                $obj->user = $user;
                $obj->profileLink = CUrl::build('profile', '', array('userid' => $row->id));
                $isFriend = CFriendsHelper::isConnected($row->id, $my->id);

                $connection = $friends->getFriendConnection($my->id, $row->id);
                $obj->isMyFriend = false;
                if (!empty($connection)) {
                    if ($connection[0]->connect_from == $my->id) {
                        $obj->isMyFriend = true;
                    }
                }

                $obj->addFriend = ((!$isFriend) && $my->id != $row->id) ? true : false;
                if ($obj->addFriend) {
                    $alreadyfriend[$row->id] = $row->id;
                }
                $resultRows[] = $obj;
            }
            $featuredList = $this->_cachedCall('getFeaturedMember', array(), '', array(COMMUNITY_CACHE_TAG_FEATURED));
            $config = CFactory::getConfig();

            $alphabetHTML = '';
            if ($config->get('alphabetfiltering')) {
                $sortingsHTML = CFilterBar::getHTML(CRoute::getURI(), $sortItems, 'latest');
                $alphabetHTML = CFilterBar::getHTML(CRoute::getURI(), '','', $filterItems, 'all');
            } else {
                $sortingsHTML = CFilterBar::getHTML(CRoute::getURI(), $sortItems, 'latest');
            }


            $multiprofileArr = array();
            $hasMultiprofile = false;

            //let see if we have any multiprofile enabled
            if($config->get('profile_multiprofile')){
                $hasMultiprofile = true;
                //lets get the available profile
                $profileModel = CFactory::getModel('Profile');
                $profiles = $profileModel->getProfileTypes();

                if($profiles){
                    $multiprofileArr[] =  array(
                        'url' => CRoute::_('index.php?option=com_community&view=search&task=browse&filter='.$filter.'&sort='.$sorted),
                        'name' => JText::_('COM_COMMUNITY_ALL_PROFILE'),
                        'selected' => (!$profiletype) ? 1 : 0
                    );
                    foreach($profiles as $profile){
                        $multiprofileArr[] = array(
                            'url' => CRoute::_('index.php?option=com_community&view=search&task=browse&filter='.$filter.'&sort='.$sorted.'&profiletype='.$profile->id),
                            'name' => $profile->name,
                            'selected' => ($profile->id == $profiletype) ? 1 : 0
                        );
                    }
                }
            }

            echo $tmpl->set('featuredList', $featuredList)
                    ->set('hasMultiprofile', $hasMultiprofile)
                    ->set('multiprofileArr', $multiprofileArr)
                    ->set('alreadyfriend', $alreadyfriend)
                    ->set('isCommunityAdmin', COwnerHelper::isCommunityAdmin())
                    ->set('data', $resultRows)
                    ->set('sortings', $sortingsHTML)
                    ->set('alphabet', $alphabetHTML)
                    ->set('my', $my)
                    ->set('submenu', $this->showSubmenu(false))
                    ->set('totalUser', $totalUser)
                    ->set('showFeaturedList', $config->get('show_featured'))
                    ->set('pagination', $searchModel->getPagination())
                    ->fetch('people.browse');
        }

        /**
         * Get list of featured member
         * @return [object] [description]
         */
        public function getFeaturedMember() {
            $featured = new CFeatured(FEATURED_USERS);
            $featuredList = $featured->getItemIds();
            $filterblocked = array();
            foreach ($featuredList as $id) {
                $user = CFactory::getUser($id);
                if ($user->block == 0)
                    $filterblocked[] = $id;
            }
            return $filterblocked;
            //return $featuredList;
        }

        /**
         * [field description]
         * @param  [type] $data [description]
         * @return [type]       [description]
         */
        public function field($data) {
            $jinput = JFactory::getApplication()->input;
            $lang = JFactory::getLanguage();
            $lang->load('com_community.country');

            $searchFields = $jinput->getArray();

            // Remove non-search field
            if (isset($searchFields['option']))
                unset($searchFields['option']);
            if (isset($searchFields['view']))
                unset($searchFields['view']);
            if (isset($searchFields['task']))
                unset($searchFields['task']);
            if (isset($searchFields['Itemid']))
                unset($searchFields['Itemid']);
            if (isset($searchFields['format']))
                unset($searchFields['format']);

            $keys = array_keys($searchFields);
            $vals = array_values($searchFields);


            $document = JFactory::getDocument();

            $searchModel = CFactory::getModel('search');
            $profileModel = CFactory::getModel('profile');
            $profileName = $profileModel->getProfileName($keys[0]);
            $profileName = JText::_($profileName);

            /**
             * Opengraph
             */
            CHeadHelper::setType('website', JText::sprintf('COM_COMMUNITY_MEMBERS_WITH_FIELD', JText::_($profileName), JText::_($vals[0])));

            $rows = $data->result;


            $my = CFactory::getUser();

            $resultRows = array();
            $friendsModel = CFactory::getModel('friends');

            $tmpl = new CTemplate();
            for ($i = 0; $i < count($rows); $i++) {

                $row = $rows[$i];

                $userObj = CFactory::getUser($row->id);
                $obj = new stdClass();
                $obj->user = $userObj;
                $obj->friendsCount = $userObj->getFriendCount();
                $obj->profileLink = CRoute::_('index.php?option=com_community&view=profile&userid=' . $row->id);
                $isFriend = CFriendsHelper::isConnected($row->id, $my->id);

                $obj->addFriend = ((!$isFriend) && ($my->id != 0) && $my->id != $row->id) ? true : false;

                $resultRows[] = $obj;
            }

            $pagination = $searchModel->getPagination();

            echo $tmpl->set('data', $resultRows)
                    ->set('sortings', '')
                    ->set('pagination', $pagination)
                    ->set('featuredList', array())
                    ->set('isCommunityAdmin', '')
                    ->set('my', $my)
                    ->fetch('people.browse');
        }

        public function advanceSearch() {
            $mainframe = JFactory::getApplication();
            $jinput    = $mainframe->input;

            $document = JFactory::getDocument();

            //load calendar behavior
            JHtml::_('behavior.calendar');
            JHtml::_('behavior.tooltip');

            /**
             * Opengraph
             */
            CHeadHelper::setType('website', JText::_('COM_COMMUNITY_TITLE_CUSTOM_SEARCH'));

            //$this->showSubMenu();

            $this->addPathway(JText::_('COM_COMMUNITY_TITLE_CUSTOM_SEARCH'));

            $profileType = $jinput->get('profiletype', 0, 'INT');
            $my         = CFactory::getUser();
            $config     = CFactory::getConfig();
            $result     = null;
            $fields     = CAdvanceSearch::getFields($profileType);
            $data       = new stdClass();
            $post       = $jinput->get->getArray();
            $keyList    = isset($post['key-list']) ? $post['key-list'] : '';
            $avatarOnly = $jinput->get('avatar', '');

            if (strlen($keyList) > 0) {
                //formatting the assoc array
                $filter = array();
                $key = explode(',', $keyList);
                $joinOperator = isset($post['operator']) ? $post['operator'] : '';

                foreach ($key as $idx) {
                    $obj = new stdClass();
                    $obj->field = $post['field' . $idx];
                    $obj->condition = $post['condition' . $idx];
                    $obj->fieldType = $post['fieldType' . $idx];

                    if ($obj->fieldType == 'email') {
                        $obj->condition = 'equal';
                    }

                    // we need to check whether the value contain start and end kind of values.
                    // if yes, make them an array.

                    if (isset($post['value' . $idx . '_2'])) {
                        if ($obj->fieldType == 'date' || $obj->fieldType == 'birthdate') {
                            $startDate = (empty($post['value' . $idx])) ? '01/01/1970' : $post['value' . $idx];
                            $endDate = (empty($post['value' . $idx . '_2'])) ? '01/01/1970' : $post['value' . $idx . '_2'];

                            $startDate = date('Y-m-d', strtotime($startDate));
                            $endDate = date('Y-m-d', strtotime($endDate));

                            // Joomla 1.5 uses "/"
                            // Joomla 1.6 uses "-"
                            $delimeter = '-';
                            if (strpos($startDate, '/')) {
                                $delimeter = '/';
                            }

                            $sdate = explode($delimeter, $startDate);
                            $edate = explode($delimeter, $endDate);
                            if (isset($sdate[2]) && isset($edate[2])) {
                                $obj->value = array($sdate[0] . '-' . $sdate[1] . '-' . $sdate[2] . ' 00:00:00',
                                    $edate[0] . '-' . $edate[1] . '-' . $edate[2] . ' 23:59:59');
                            } else {
                                $obj->value = array(0, 0);
                            }
                        } else {
                            $obj->value = array($post['value' . $idx], $post['value' . $idx . '_2']);
                        }
                    } else {
                        if ($obj->fieldType == 'date' || $obj->fieldType == 'birthdate') {
                            $startDate = (empty($post['value' . $idx])) ? '01/01/1970' : $post['value' . $idx];
                            $startDate = date('Y-m-d', strtotime($startDate));
                            $delimeter = '-';
                            if (strpos($startDate, '/')) {
                                $delimeter = '/';
                            }
                            $sdate = explode($delimeter, $startDate);
                            if (isset($sdate[2])) {
                                $obj->value = $sdate[0] . '-' . $sdate[1] . '-' . $sdate[2] . ' 23:59:59';
                            } else {
                                $obj->value = 0;
                            }
                        } else if ($obj->fieldType == 'checkbox') {
                            if (empty($post['value' . $idx])) {
                                //this mean user didnot check any of the option.
                                $obj->value = '';
                            } else {
                                $obj->value = isset($post['value' . $idx]) ? implode(',', $post['value' . $idx]) : '';
                            }
                        } else {
                            $obj->value = isset($post['value' . $idx]) ? $post['value' . $idx] : '';
                        }
                    }

                    $filter[] = $obj;
                }

                //sort by alphabetical
                $data->search = CAdvanceSearch::getResult($filter, $joinOperator, $avatarOnly, 'alphabetical', $profileType);
                $data->filter = $post;
            }

            $rows         = (!empty($data->search)) ? $data->search->result : array();
            $pagination   = (!empty($data->search)) ? $data->search->pagination : '';
            $filter       = (!empty($data->filter)) ? $data->filter : array();
            $resultRows   = array();
            $friendsModel = CFactory::getModel('friends');

            for ($i = 0; $i < count($rows); $i++) {
                $row = $rows[$i];

                //filter the user profile type
                if($profileType && $row->_profile_id != $profileType){
                    continue;
                }

                $obj = new stdClass();
                $obj->user = $row;
                $obj->friendsCount = $row->getFriendCount();
                $obj->profileLink = CRoute::_('index.php?option=com_community&view=profile&userid=' . $row->id);
                $isFriend = CFriendsHelper::isConnected($row->id, $my->id);

                $obj->addFriend = ((!$isFriend) && ($my->id != 0) && $my->id != $row->id) ? true : false;

                $resultRows[] = $obj;
            }

            if (class_exists('Services_JSON')) {
                $json = new Services_JSON();
            } else {
                require_once (AZRUL_SYSTEM_PATH . '/pc_includes/JSON.php');
                $json = new Services_JSON();
            }

            $tmpl = new CTemplate();

            $multiprofileArr = array();
            $hasMultiprofile = false;

            //let see if we have any multiprofile enabled
            if($config->get('profile_multiprofile')){
                $hasMultiprofile = true;
                //lets get the available profile
                $profileModel = CFactory::getModel('Profile');
                $profiles = $profileModel->getProfileTypes();

                if($profiles){
                    $multiprofileArr[] =  array(
                        'url' => CRoute::_('index.php?option=com_community&view=search&task=advancesearch'),
                        'name' => JText::_('COM_COMMUNITY_ALL_PROFILE'),
                        'selected' => (!$profileType) ? 1 : 0
                    );
                    foreach($profiles as $profile){
                        $multiprofileArr[] = array(
                            'url' => CRoute::_('index.php?option=com_community&view=search&task=advancesearch&profiletype='.$profile->id),
                            'name' => $profile->name,
                            'selected' => ($profile->id == $profileType) ? 1 : 0
                        );
                    }
                }
            }

            $searchForm = $tmpl->set('fields', $fields)
                    ->set('hasMultiprofile', $hasMultiprofile)
                    ->set('multiprofileArr', $multiprofileArr)
                    ->set('keyList', $keyList)
                    ->set('profileType', $profileType)
                    ->set('avatarOnly', $avatarOnly)
                    ->set('filterJson', $json->encode($filter))
                    ->set('postresult', isset($post['key-list']))
                    ->set('submenu', $this->showSubmenu(false))
                    ->fetch('search.advancesearch');

            if (isset($post['key-list'])) {
                //result template
                $tmplResult   = new CTemplate();
                $featured     = new CFeatured(FEATURED_USERS);
                $featuredList = $featured->getItemIds();

                $tmpl->set('featuredList', $featuredList);

                $searchForm .= $tmplResult->set('my', $my)
                        ->set('showFeaturedList', false)
                        ->set('multiprofileArr', $multiprofileArr)
                        ->set('featuredList', $featuredList)
                        ->set('data', $resultRows)
                        ->set('isAdvanceSearch', true)
                        ->set('hasMultiprofile', $hasMultiprofile)
                        ->set('sortings', '')
                        ->set('pagination', $pagination)
                        ->set('filter', $filter)
                        ->set('featuredList', $featuredList)
                        ->set('isCommunityAdmin', COwnerHelper::isCommunityAdmin())
                        ->fetch('people.browse');
            }

            echo $searchForm;
        }

    }

}
