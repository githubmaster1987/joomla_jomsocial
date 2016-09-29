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
jimport('joomla.utilities.date');

class CommunitySearchController extends CommunityBaseController {

    var $_icon = 'search';

    public function ajaxRemoveFeatured($memberId) {
        $filter = JFilterInput::getInstance();
        $memberId = $filter->clean($memberId, 'int');

        $my = CFactory::getUser();
        if ($my->id == 0) {
            return $this->ajaxBlockUnregister();
        }

        if (COwnerHelper::isCommunityAdmin()) {
            $model = CFactory::getModel('Featured');

            //CFactory::load( 'libraries' , 'featured' );
            $featured = new CFeatured(FEATURED_USERS);
            $my = CFactory::getUser();

            if ($featured->delete($memberId)) {
                $html = JText::_('COM_COMMUNITY_USER_REMOVED_FROM_FEATURED');
            } else {
                $html = JText::_('COM_COMMUNITY_REMOVING_USER_FROM_FEATURED_ERROR');
            }
        } else {
            $html = JText::_('COM_COMMUNITY_NOT_ALLOWED_TO_ACCESS_SECTION');
        }
        $actions = '<input type="button" class="btn" onclick="window.location.reload();" value="' . JText::_('COM_COMMUNITY_BUTTON_CLOSE_BUTTON') . '"/>';

        $this->cacheClean(array(COMMUNITY_CACHE_TAG_FEATURED));

        $json = array();
        $json['title'] = '&nbsp;';
        $json['html'] = $html;

        die( json_encode($json) );
    }

    /**
     * Feature the given user
     *
     * @param  int $memberId userid to feature
     * @return [type]           [description]
     */
    public function ajaxAddFeatured($memberId) {
        $filter = JFilterInput::getInstance();
        $memberId = $filter->clean($memberId, 'int');

        $my = CFactory::getUser();
        if ($my->id == 0) {
            return $this->ajaxBlockUnregister();
        }

        if (COwnerHelper::isCommunityAdmin()) {
            $model = CFactory::getModel('Featured');

            if (!$model->isExists(FEATURED_USERS, $memberId)) {
                $featured = new CFeatured(FEATURED_USERS);
                $member = CFactory::getUser($memberId);
                $config = CFactory::getConfig();
                $limit = $config->get('featured' . FEATURED_USERS . 'limit', 10);

                if ($featured->add($memberId, $my->id) === true) {
                    $html = JText::sprintf('COM_COMMUNITY_MEMBER_IS_FEATURED', $member->getDisplayName());
                } else {
                    $html = JText::sprintf('COM_COMMUNITY_MEMBER_LIMIT_REACHED_FEATURED', $member->getDisplayName(), $limit);
                }
            } else {
                $html = JText::_('COM_COMMUNITY_USER_ALREADY_FEATURED');
            }
        } else {
            $html = JText::_('COM_COMMUNITY_NOT_ALLOWED_TO_ACCESS_SECTION');
        }

        $this->cacheClean(array(COMMUNITY_CACHE_TAG_FEATURED));

        $json = array();
        $json['title'] = '&nbsp;';
        $json['html'] = $html;

        die( json_encode($json) );
    }

    public function display($cacheable = false, $urlparams = false) {
        $this->search();
    }

    /**
     * Old advance search.
     */
    public function advsearch() {
        require_once (JPATH_COMPONENT . '/libraries/profile.php');
        $jinput = JFactory::getApplication()->input;

        global $option, $context;
        $mainframe = JFactory::getApplication();

        $data = new stdClass();
        $view = $this->getView('search');
        $model = $this->getModel('search');
        $profileModel = $this->getModel('profile');

        $document = JFactory::getDocument();

        $fields = $profileModel->getAllFields();

        $search = $jinput->getArray();

        //prefill the seach values.
        $fields = $this->_fillSearchValues($fields, $search);

        $data->fields = $fields;

        if (isset($search)) {
            $model = $this->getModel('search');
            $data->result = $model->searchPeople($search);
        }

        $data->pagination = $model->getPagination();

        echo $view->get('search', $data);
    }

    public function search() {
        $mainframe = JFactory::getApplication();
        $jinput = $mainframe->input;
        $config = CFactory::getConfig();

        if (CFactory::getUser()->id == 0 && !$config->get('guestsearch')) {
            return $this->blockUnregister();
        }

        $data = new stdClass();
        $view = $this->getView('search');
        $model = $this->getModel('search');
        $profileModel = $this->getModel('profile');

        $fields = $profileModel->getAllFields();

        $search = $jinput->request->getArray();
        $data->query = $jinput->request->get('q', '', 'STRING');
        $avatarOnly = $jinput->get('avatar', '', 'NONE');

        //prefill the seach values.
        $fields = $this->_fillSearchValues($fields, $search);

        $data->fields = $fields;

        if (isset($search)) {
            $model = $this->getModel('search');
            $data->result = $model->searchPeople($search, $avatarOnly);

            //pre-load cuser.
            $ids = array();
            if (!empty($data->result)) {
                foreach ($data->result as $item) {
                    $ids[] = $item->id;
                }

                CFactory::loadUsers($ids);
            }
        }

        $data->pagination = $model->getPagination();

        echo $view->get('search', $data);
    }

    /**
     * @since 3.3
     * Quick search to retrieve users
     */
    public function ajaxSearch($query = '') {
        $search = array(
            'q' => $query,
            'search' => 'search'
        );

        $model = $this->getModel('search');
        $results = $model->searchPeople($search, false);

        $info = array();

        foreach($results as $result){
            $user = CFactory::getUser($result->id);

            $info[] = array(
                'id' => $user->id,
                'name' => $user->getDisplayName(),
                'thumb' => $user->getThumbAvatar(),
                'url' => CRoute::_('index.php?option=com_community&view=profile&userid='.$user->id)
            );
        }

        if ( count($info) < 1 ) {
            $info['error'] = JText::_('COM_COMMUNITY_NO_RESULT_FROM_SEARCH');
        }

        $info = json_encode($info);

        die($info);

    }

    /**
     * Site wide people browser
     */
    public function browse() {
        $view = $this->getView('search');
        echo $view->get(__FUNCTION__, null);
    }

    // search by a single field
    public function field() {
        require_once (JPATH_COMPONENT . '/libraries/profile.php');

        global $option, $context;
        $mainframe = JFactory::getApplication();
        $jinput = JFactory::getApplication()->input;

        $data = new stdClass();
        $view = $this->getView('search');
        $searchModel = $this->getModel('search');
        $profileModel = $this->getModel('profile');

        $document = JFactory::getDocument();

        $fields = $profileModel->getAllFields();
        $searchFields = $jinput->getArray();

        // Remove non-search field
        $remove_field = array('option','view','task','Itemid','format','lang');
        foreach($searchFields as $key=>$field){
            if(in_array($key,$remove_field)){
                unset($searchFields[$key]);
            }else if($key === 'FIELD_SELECT'){
                $searchFields[$key] = htmlspecialchars(urldecode($searchFields[$key]));
            }
        }

        if (strpos($jinput->server->get("QUERY_STRING",'','STRING'), "+") !== false) {
            $jinput->server->set("QUERY_STRING",str_replace("+", "%2B", $jinput->server->get("QUERY_STRING",'','STRING')));
            parse_str($_SERVER["QUERY_STRING"], $jinput->get->getArray());
        }
        /**
         * This is small code to prevent + in query
         */
        if (count($searchFields) > 0) {
            $keys = array_keys($searchFields);
            $vals = array_values($searchFields);
            $model = CFactory::getModel('Profile');
            $table = JTable::getInstance('ProfileField', 'CTable');
            $table->load($model->getFieldId($keys[0]));

            if (!$table->visible || !$table->published) {
                $mainframe->enqueueMessage(JText::_('COM_COMMUNITY_FIELD_NOT_SEARCHABLE'), 'error');
                return;
            }

            if (isset($searchFields['type']) && $searchFields['type'] == 'checkbox') {
                $field = new stdClass();
                $field->field = $keys[0];
                $field->condition = 'equal';
                $field->fieldType = $searchFields['type'];
                $field->value = $vals[0];
                $filter = array($field);

                $data->result = $searchModel->getAdvanceSearch($filter);
            } else {
                $data->result = $searchModel->searchByFieldCode($searchFields);
            }

            echo $view->get('field', $data);
        }
    }

    /**
     * New custom search which renamed to advance search.
     */
    public function advanceSearch() {
        $view   = $this->getView('search');
        $my     = CFactory::getUser();
        $config = CFactory::getConfig();

        if ($my->id == 0 && !$config->get('guestsearch')) {
            return $this->blockUnregister();
        }

        echo $view->get('advanceSearch');
    }

    private function _fillSearchValues(&$fields, $search) {
        if (isset($search)) {
            foreach ($fields as $group) {
                $field = $group->fields;

                for ($i = 0; $i < count($field); $i++) {
                    $fieldid = $field[$i]->id;
                    if (!empty($search['field' . $fieldid])) {
                        $tmpEle = $search['field' . $fieldid];
                        if (is_array($tmpEle)) {
                            $tmpStr = "";
                            foreach ($tmpEle as $ele) {
                                $tmpStr .= $ele . ',';
                            }
                            $field[$i]->value = $tmpStr;
                        } else {
                            $field[$i]->value = $search['field' . $fieldid];
                        }
                    }
                }//end for i
            }//end foreach
        }
        return $fields;
    }

}
