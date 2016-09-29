<?php
/**
* @copyright (C) 2015 iJoomla, Inc. - All rights reserved.
* @license GNU General Public License, version 2 (http://www.gnu.org/licenses/gpl-2.0.html)
* @author iJoomla.com <webmaster@ijoomla.com>
* @url https://www.jomsocial.com/license-agreement
* The PHP code portions are distributed under the GPL license. If not otherwise stated, all images, manuals, cascading style sheets, and included JavaScript *are NOT GPL, and are released under the IJOOMLA Proprietary Use License v1.0
* More info at https://www.jomsocial.com/license-agreement
*/

defined('_JEXEC') or die();

?>
<div class="joms-js--member-module">
    <div class="joms-gap"></div>
    <ul class="joms-list--thumbnail clearfix">

        <?php
            if(count($members) > 0) {
                foreach ($members as $member) {
                    ?>
                    <li class="joms-list__item">
                        <div class="joms-avatar <?php echo ($filter == 4) ? 'joms-online' : CUserHelper::onlineIndicator($member); ?>">
                            <a href="<?php echo CRoute::_('index.php?option=com_community&view=profile&userid=' . $member->id); ?>">
                                <img src="<?php echo $member->getThumbAvatar(); ?>"
                                     title="<?php echo CTooltip::cAvatarTooltip($member); ?>"
                                     alt="<?php echo CStringHelper::escape($member->getDisplayName()) ?>"
                                     data-author="<?php echo $member->id; ?>">
                            </a>
                        </div>
                    </li>
                <?php
                }
            }elseif($params->get('sorting') == 3 && !CFactory::getUser()->id){
                echo '<div class="joms-blankslate">' . JText::_('MOD_COMMUNITY_MEMBERS_LOGIN_TO_VIEW') . '</div>';
            }else{
                echo '<div class="joms-blankslate">' . JText::_('MOD_COMMUNITY_MEMBERS_NO_MEMBERS') . '</div>';
            }?>
    </ul>
</div>

<div class="joms-gap"></div>
<a href="<?php echo CRoute::_('index.php?option=com_community&view=search&task=browse' ); ?>" class="joms-button--link">
    <small>
        <?php echo JText::_( 'MOD_COMMUNITY_MEMBERS_VIEW_ALL' ).$totalMembers; ?>
    </small>
</a>

