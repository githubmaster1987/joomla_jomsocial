<?php
    /**
     * @copyright (C) 2014 iJoomla, Inc. - All rights reserved.
     * @license GNU General Public License, version 2 (http://www.gnu.org/licenses/gpl-2.0.html)
     * @author iJoomla.com <webmaster@ijoomla.com>
     * @url https://www.jomsocial.com/license-agreement
     * The PHP code portions are distributed under the GPL license. If not otherwise stated, all images, manuals, cascading style sheets, and included JavaScript *are NOT GPL, and are released under the IJOOMLA Proprietary Use License v1.0
     * More info at https://www.jomsocial.com/license-agreement
     */
// no direct access
    defined('_JEXEC') or die('Restricted access');

    require_once(JPATH_ROOT . '/components/com_community/libraries/core.php');

    if (!class_exists('plgCommunityMutualFriends')) {

        class plgCommunityMutualFriends extends CApplications
        {
            var $_user = null;
            var $name = "JS Mutual Friends";
            var $_name = 'mutualfriends';

            function __construct(& $subject, $config)
            {
                $this->_user = CFactory::getRequestUser();
                parent::__construct($subject, $config);
            }

            function onProfileDisplay()
            {
                JPlugin::loadLanguage( 'plg_community_mutualfriends', JPATH_ADMINISTRATOR );

                $friendsModel = CFactory::getModel('Friends');
                $friends = $friendsModel->getFriends($this->_user->id,'latest',false,'mutual');

                if($this->params->get('hide_empty', 0) && !count($friends)) return '';

                return $this->_getMutualFriendsHTML($this->_user->id);
            }

            static public function _getMutualFriendsHTML($userid = null)
            {
                $my = CFactory::getUser();
                if($my->id == $userid) return;


                $friendsModel = CFactory::getModel('Friends');
                $friends = $friendsModel->getFriends($userid,'latest',false,'mutual');



                $html ="<ul class='joms-list--friend single-column'>";
                if(sizeof($friends)) {

                    foreach($friends as $friend) {

                        $html .= "<li class='joms-list__item'>";
                        $html .= "<div class='joms-list__avatar'>";
                        $html .= '<div class="joms-avatar '.CUserHelper::onlineIndicator($friend).'"><a href="'. CRoute::_('index.php?option=com_community&view=profile&userid='.$friend->id ) . '">';
                        $html .= '<img src="' . $friend->getThumbAvatar() . '" data-author="'.$friend->id.'" />';
                        $html .= "</a></div></div>";
                        $html .= "<div class='joms-list__body'>";
                        $html .= CFriendsHelper::getUserCog($friend->id,null,null,true);
                        $html .= CFriendsHelper::getUserFriendDropdown($friend->id);
                        $html .= '<a href="'. CRoute::_('index.php?option=com_community&view=profile&userid='.$friend->id ).'">';
                        $html .= '<h4 class="joms-text--username">' . $friend->getDisplayName() . '</h4></a>';
                        $html .= '<span class="joms-text--title">' . JText::sprintf('COM_COMMUNITY_TOTAL_MUTUAL_FRIENDS',
                                    CFriendsHelper::getTotalMutualFriends($friend->id)) . '</span>';
                        $html .= "</div></li>";

                    ;


                    }
                $html .="</ul>";

                } else {
                    $html .= JText::_('COM_COMMUNITY_NO_MUTUAL_FRIENDS');
                }

                return $html;
            }

        }
    }
