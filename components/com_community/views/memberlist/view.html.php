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

jimport('joomla.application.component.view');
jimport('joomla.utilities.arrayhelper');

if (!class_exists("CommunityViewMemberList")) {

    class CommunityViewMemberList extends CommunityView {

        public function display($tpl = null) {
            $mainframe = JFactory::getApplication();
            $jinput = $mainframe->input;

            $id = $jinput->get('listid', '', 'INT');
            $list = JTable::getInstance('MemberList', 'CTable');
            $list->load($id);

            if (empty($list->id) || is_null($list->id)) {
                echo JText::_('COM_COMMUNITY_INVALID_ID');
                return;
            }

            /**
             * Opengraph
             */
            CHeadHelper::setType('website', $list->getTitle());

            $tmpCriterias = $list->getCriterias();
            $criterias = array();

            foreach ($tmpCriterias as $criteria) {
                $obj = new stdClass();
                $obj->field = $criteria->field;
                $obj->condition = $criteria->condition;
                $obj->fieldType = $criteria->type;

                switch ($criteria->type) {
                    case 'date':
                    case 'birthdate':
                        if ($criteria->condition == 'between') {
                            $date = explode(',', $criteria->value);
                            if (isset($date[1])) {
                                $delimeter = '-';
                                if (strpos($date[0], '/')) {
                                    $delimeter = '/';
                                }
                                $startDate = explode($delimeter, $date[0]);
                                $endDate = explode($delimeter, $date[1]);
                                if (isset($startDate[2]) && isset($endDate[2])) {
                                    //date format
                                    $obj->value = array($startDate[2] . '-' . intval($startDate[1]) . '-' . $startDate[0] . ' 00:00:00',
                                        $endDate[2] . '-' . intval($endDate[1]) . '-' . $endDate[0] . ' 23:59:59');
                                } else {
                                    //age format
                                    $obj->value = array($date[0], $date[1]);
                                }
                            } else {
                                //wrong data, set to default
                                $obj->value = array(0, 0);
                            }
                        } else {
                            $delimeter = '-';
                            if (strpos($criteria->value, '/')) {
                                $delimeter = '/';
                            }
                            $startDate = explode($delimeter, $criteria->value);
                            if (isset($startDate[2])) {
                                //date format
                                $obj->value = $startDate[2] . '-' . intval($startDate[1]) . '-' . $startDate[0] . ' 00:00:00';
                            } else {
                                //age format
                                $obj->value = $criteria->value;
                            }
                        }
                        break;
                    case 'checkbox':
                    default:
                        $obj->value = $criteria->value;
                        break;
                }


                $criterias[] = $obj;
            }
            //CFactory::load( 'helpers' , 'time');
            $created = CTimeHelper::getDate($list->created);

            //CFactory::load( 'libraries' , 'advancesearch' );
            //CFactory::load( 'libraries' , 'filterbar' );

            $sortItems = array(
                'latest' => JText::_('COM_COMMUNITY_SORT_LATEST'),
                'online' => JText::_('COM_COMMUNITY_SORT_ONLINE'),
                'alphabetical' => JText::_('COM_COMMUNITY_SORT_ALPHABETICAL')
            );
            $sorting = $jinput->get->get('sort', 'latest', 'STRING');
            $data = CAdvanceSearch::getResult($criterias, $list->condition, $list->avataronly, $sorting);

            $tmpl = new CTemplate();
            $html = $tmpl->set('list', $list)
                    ->set('created', $created)
                    ->set('sorting', CFilterBar::getHTML(CRoute::getURI(), $sortItems, 'latest'))
                    ->fetch('memberlist.result');
            unset($tmpl);

            //CFactory::load( 'libraries' , 'tooltip' );
            //CFactory::load( 'helpers' , 'owner' );
            //CFactory::load( 'libraries' , 'featured' );


            $featured = new CFeatured(FEATURED_USERS);
            $featuredList = $featured->getItemIds();
            $my = CFactory::getUser();

            $resultRows = array();
            $friendsModel = CFactory::getModel('friends');
            $alreadyfriend = array();

            //CFactory::load( 'helpers' , 'friends' );
            foreach ($data->result as $user) {
                $obj = new stdClass();
                $obj->user = $user;
                $obj->friendsCount = $user->getFriendCount();
                $obj->profileLink = CRoute::_('index.php?option=com_community&view=profile&userid=' . $user->id);
                $isFriend = CFriendsHelper::isConnected($user->id, $my->id);

                $obj->addFriend = ((!$isFriend) && ($my->id != 0) && $my->id != $user->id) ? true : false;
                //record friends
                if ($obj->addFriend) {
                    $alreadyfriend[$user->id] = $user->id;
                }

                $resultRows[] = $obj;
            }

            $tmpl = new CTemplate();

            echo $tmpl->set('data', $resultRows)
                    ->set('alreadyfriend', $alreadyfriend)
                    ->set('sortings', '')
                    ->set('pagination', $data->pagination)
                    ->set('filter', '')
                    ->set('featuredList', $featuredList)
                    ->set('my', $my)
                    ->set('showFeaturedList', false)
                    ->set('isCommunityAdmin', COwnerHelper::isCommunityAdmin())
                    // ->set('pageTitle', $mainframe->getMenu()->getActive()->title)
                    ->set('pageTitle', $list->getTitle())
                    ->fetch('people.browse');
        }

    }

}
