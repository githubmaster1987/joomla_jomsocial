<?php
/**
* @copyright (C) 2015 iJoomla, Inc. - All rights reserved.
* @license GNU General Public License, version 2 (http://www.gnu.org/licenses/gpl-2.0.html)
* @author iJoomla.com <webmaster@ijoomla.com>
* @url https://www.jomsocial.com/license-agreement
* The PHP code portions are distributed under the GPL license. If not otherwise stated, all images, manuals, cascading style sheets, and included JavaScript *are NOT GPL, and are released under the IJOOMLA Proprietary Use License v1.0
* More info at https://www.jomsocial.com/license-agreement
*/
defined( '_JEXEC' ) or die( 'Unauthorized Access' );

	// Check if JomSocial core file exists
	$corefile 	= JPATH_ROOT . '/components/com_community/libraries/core.php';

	jimport( 'joomla.filesystem.file' );
	if( !JFile::exists( $corefile ) )
	{
		return;
	}

	// Include JomSocial's Core file, helpers, settings...
	require_once( $corefile );
	require_once dirname(__FILE__) . '/helper.php';

	// Add proper stylesheet
    JFactory::getLanguage()->isRTL() ? CTemplate::addStylesheet('style.rtl') : CTemplate::addStylesheet('style');

	/*
	* If we need any other additional configuration we can add it here like this
	$document = JFactory::getDocument();
    $document->addStyleSheet(JURI::root(true) . '/example/example.css');
    $config = CFactory::getConfig();
	Alex's reference for doing ajax calls through module or plugin.
	https://github.com/Joomla-Ajax-Interface/component
	*/

    $mainframe = JFactory::getApplication();
    $jinput    = $mainframe->input;
    $profileType = $jinput->get('profiletype', 0, 'INT');
    $my         = CFactory::getUser();
    $config     = CFactory::getConfig();
    $result     = null;
    $fields     = CAdvanceSearch::getFields($profileType);
    $data       = new stdClass();
    $post       = $jinput->getArray();
    $keyList    = isset($post['key-list']) ? $post['key-list'] : '';
    $avatarOnly = $jinput->get('avatar', '', 'NONE');


    if (JString::strlen($keyList) > 0) {
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
                if ($obj->fieldType == 'date') {
                    $startDate = (empty($post['value' . $idx])) ? '01/01/1970' : $post['value' . $idx];
                    $endDate = (empty($post['value' . $idx . '_2'])) ? '01/01/1970' : $post['value' . $idx . '_2'];

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
                if ($obj->fieldType == 'date') {
                    $startDate = (empty($post['value' . $idx])) ? '01/01/1970' : $post['value' . $idx];
                    $delimeter = '-';
                    if (strpos($startDate, '/')) {
                        $delimeter = '/';
                    }
                    $sdate = explode($delimeter, $startDate);
                    if (isset($sdate[2])) {
                        $obj->value = $sdate[2] . '-' . $sdate[1] . '-' . $sdate[0] . ' 00:00:00';
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
        $data->search = CAdvanceSearch::getResult($filter, $joinOperator, $avatarOnly,'',$profileType);
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

	$user = CFactory::getUser();

    $displayLayout = $params->get('search_layout', 0);

    ob_start();
    if($displayLayout == 1){
        //1 = advanced
        $isMobile = preg_match('/android|webos|iphone|ipad|ipod|blackberry|iemobile|opera mini/i', $_SERVER['HTTP_USER_AGENT']);
        $css = 'assets/pickadate/themes/' . ( $isMobile ? 'default' : 'classic' ) . '.combined.css';
        CFactory::attach($css, 'css');
        require(JPATH_BASE.'/modules/mod_community_memberssearch/layout/searchadv.php');
    }else{
        //0 = simple
        require(JPATH_BASE.'/modules/mod_community_memberssearch/layout/searchsimple.php');
    }
    $layoutContent = ob_get_clean();

    require(JModuleHelper::getLayoutPath('mod_community_memberssearch', $params->get('layout', 'default')));
