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

    include_once(JPATH_BASE . '/components/com_community/defines.community.php');
    require_once(JPATH_BASE . '/components/com_community/libraries/core.php');
    include_once(COMMUNITY_COM_PATH . '/libraries/activities.php');
    include_once(COMMUNITY_COM_PATH . '/helpers/time.php');
    $svgPath = CFactory::getPath('template://assets/icon/joms-icon.svg');
    include_once $svgPath;
    JFactory::getLanguage()->isRTL() ? CTemplate::addStylesheet('style.rtl') : CTemplate::addStylesheet('style');

    $activities = new CActivityStream();
    $maxEntry = $params->get('limit', 20);
    $user = CFactory::getUser();
    //$stream = $activities->getHTML('', '', null, $maxEntry, '');
    switch($params->get('stream_type',0)){
        case 0 :
            $stream = $activities->getHTML('', '', null, $maxEntry, '');
            break;
        case 1 :
            $stream = $activities->getHTML(CFactory::getUser()->id, '', '', $maxEntry, '', '', true, false, null, false, 'active-profile', 0,array('apps'=>'profiles'));
            break;
        case 2 :
            $stream = $activities->getHTML(CFactory::getUser()->id, '', '', $maxEntry, '', '', true, false, null, false, 'active-profile', 0, array('apps'=>'groups'));
            break;
        case 3 :
            $stream = $activities->getHTML(CFactory::getUser()->id, '', '', $maxEntry, '', '', true, false, null, false, 'active-profile', 0, array('apps'=>'events'));
            break;
        default:
            $stream = $activities->getHTML('', '', null, $maxEntry, '');
    }

    require(JModuleHelper::getLayoutPath('mod_community_activitystream', $params->get('layout', 'default')));
