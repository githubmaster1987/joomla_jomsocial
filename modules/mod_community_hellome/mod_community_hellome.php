<?php
/**
 * @copyright (C) 2015 iJoomla, Inc. - All rights reserved.
 * @license GNU General Public License, version 2 (http://www.gnu.org/licenses/gpl-2.0.html)
 * @author iJoomla.com <webmaster@ijoomla.com>
 * @url https://www.jomsocial.com/license-agreement
 * The PHP code portions are distributed under the GPL license. If not otherwise stated, all images, manuals, cascading style sheets, and included JavaScript *are NOT GPL, and are released under the IJOOMLA Proprietary Use License v1.0
 * More info at https://www.jomsocial.com/license-agreement
 */
defined('_JEXEC') or die('Unauthorized Access');

// Check if JomSocial core file exists
$corefile = JPATH_ROOT . '/components/com_community/libraries/core.php';

jimport('joomla.filesystem.file');
if (!JFile::exists($corefile)) {
    return;
}

// Include JomSocial's Core file, helpers, settings...
require_once($corefile);
require_once dirname(__FILE__) . '/helper.php';
require_once JPATH_ROOT . '/components/com_community/controllers/controller.php';

// Add proper stylesheet
JFactory::getLanguage()->isRTL() ? CTemplate::addStylesheet('style.rtl') : CTemplate::addStylesheet('style');
$jinput = JFactory::getApplication()->input;

$moduleParams = $params; //assign params to new module params
$additionalParams = new CParameter();

$user = $my = CFactory::getUser();
$config = CFactory::getConfig();

$document = JFactory::getDocument();
$document->addStyleSheet(JURI::root(true) . '/modules/mod_community_hellome/style.css');

if ($my->id) {
    $inboxModel = CFactory::getModel('inbox');
    $notifModel = CFactory::getModel('notification');
    $friendModel = CFactory::getModel('friends');
    $profileid = $jinput->get('userid', 0);
    $filter = array('user_id' => $my->id);

    $toolbar = CToolbarLibrary::getInstance();
    $newMessageCount = $toolbar->getTotalNotifications('inbox');
    $newEventInviteCount = $toolbar->getTotalNotifications('events');
    $newFriendInviteCount = $toolbar->getTotalNotifications('friends');
    $newGroupInviteCount = $toolbar->getTotalNotifications('groups');

    $myParams = $my->getParams();
    $newNotificationCount = $notifModel->getNotificationCount($my->id, '0', $myParams->get('lastnotificationlist', ''));
    $newEventInviteCount = $newEventInviteCount + $newNotificationCount;

    $additionalParams->def('unreadCount', $inboxModel->countUnRead($filter));
    $additionalParams->def('pending', $friendModel->countPending($my->id));
    $additionalParams->def('myLink', CRoute::_('index.php?option=com_community&view=profile&userid=' . $my->id));
    $additionalParams->def('myName', $my->getDisplayName());
    $additionalParams->def('myAvatar', $my->getAvatar());
    $additionalParams->def('myId', $my->id);
    $CUserPoints = new CUserPoints();
    $additionalParams->def('myKarma', $CUserPoints->getPointsImage($my));
    $additionalParams->def('enablephotos', $config->get('enablephotos'));
    $additionalParams->def('enablevideos', $config->get('enablevideos'));
    $additionalParams->def('enablegroups', $config->get('enablegroups'));
    $additionalParams->def('enableevents', $config->get('enableevents'));

    $enablekarma = $config->get('enablekarma') ? $additionalParams->get('show_karma', 1) : $config->get('enablekarma');
    $additionalParams->def('enablekarma', $enablekarma);

    $unreadCount = $params->get('unreadCount', 1);
    $pending = $params->get('pending', 1);
    $myLink = $params->get('myLink', 1);
    $myName = $params->get('myName', 1);
    $myAvatar = $params->get('myAvatar', 1);
    $myId = $params->get('myId', 1);
    $myKarma = $params->get('myKarma', 1);
    $enablephotos = $params->get('enablephotos', 1);
    $enablevideos = $params->get('enablevideos', 1);
    $enablegroups = $params->get('enablegroups', 1);
    $enableevents = $params->get('enableevents', 1);
    $show_avatar = $params->get('show_avatar', 1);
    $show_karma = $params->get('show_karma', 1);

    $facebookuser = $params->get('facebookuser', false);
    $config = CFactory::getConfig();
    $uri = 'index.php?option=com_community';
    $uri = base64_encode($uri);

    $badge = new CBadge($my);
    $badge = $badge->getBadge();

    // IDs.
    $mainframe = JFactory::getApplication();
    $jinput = $mainframe->input;
    $my = CFactory::getUser();

    //video count
    $videoModel = CFactory::getModel('Videos');
    $totalVideos = $videoModel->getVideosCount($user->id);

    //photo count
    $photosModel = CFactory::getModel('photos');
    $totalPhotos = $photosModel->getPhotosCount($user->id);

    //group count
    $groupmodel = CFactory::getModel('groups');
    $totalGroups = $groupmodel->getGroupsCount($user->id);

    //event count
    $eventmodel = CFactory::getModel('events');
    $totalEvents = $eventmodel->getEventsCount($user->id);

    if ($moduleParams->get('logout')) {
        $app = JFactory::getApplication();
        $item = $app->getMenu()->getItem($moduleParams->get('logout'));

        if ($item) {
            $url = 'index.php?Itemid=' . $item->id;
        } else {
            // Stay on the same page
            $url = JUri::getInstance()->toString();
        }

        $logoutlink = base64_encode($url);
    } else {
        $logoutlink = base64_encode(CRoute::_('index.php?option=com_community&view=' . CFactory::getConfig()->get('redirect_logout'),
            false));
    }


}

if ($moduleParams->get('login', 0)) {
    $app = JFactory::getApplication();
    $item = $app->getMenu()->getItem($moduleParams->get('login'));

    if ($item) {
        $url = 'index.php?Itemid=' . $item->id;
    } else {
        // Stay on the same page
        $url = JUri::getInstance()->toString();
    }

    $loginLink = base64_encode($url);
} else {
    $loginLink = base64_encode('index.php?option=com_community&view=' . CFactory::getConfig()->get('redirect_logout'));
}

require(JModuleHelper::getLayoutPath('mod_community_hellome', $params->get('layout', 'default')));