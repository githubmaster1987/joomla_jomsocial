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

    require_once(dirname(__FILE__) . '/helper.php');
    require_once(JPATH_ROOT . '/components/com_community/helpers/string.php');
    require_once(JPATH_BASE . '/components/com_community/libraries/core.php');

    $showavatar = $params->get('show_avatar', '1');
    $repeatAvatar = $params->get('repeat_avatar', '1');
    $showPrivateDiscussion = $params->get('show_private_discussion', '1');
    $done_group = array();
    $groupstr = array();

    $document = JFactory::getDocument();
    //add style css
    JFactory::getLanguage()->isRTL() ? CTemplate::addStylesheet('style.rtl') : CTemplate::addStylesheet('style');

    $dis = new modCommunityDiscussions($params);
    $latest = $dis->getDiscussion($showPrivateDiscussion);
    $user = CFactory::getUser();
    require(JModuleHelper::getLayoutPath('mod_community_discussions', $params->get('layout', 'default')));
