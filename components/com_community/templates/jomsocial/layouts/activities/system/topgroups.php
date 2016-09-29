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

$groupsModel = CFactory::getModel('groups');
$activeGroup = $groupsModel->getMostActiveGroup();

if( is_null($activeGroup)) {
	$title = JText::_('COM_COMMUNITY_GROUPS_NONE_CREATED');
} else {
	$title       = JText::_('COM_COMMUNITY_ACTIVITIES_POPULAR_GROUP');
}

?>

<div class="joms-stream__body joms-stream-box">

		<h4><?php echo $title; ?></h4>
		<?php if( !is_null($activeGroup)) {
			$memberCount = $activeGroup->getMembersCount(); ?>
            <div class="joms-stream__header system">
                <div class="joms-avatar--stream">
                    <img src="<?php echo $activeGroup->getThumbAvatar(); ?>" alt="<?php echo $this->escape($activeGroup->name); ?>" >
                </div>
                <div class="joms-stream__meta">
                    <a href="<?php echo CRoute::_('index.php?option=com_community&view=groups&task=viewgroup&groupid='.$activeGroup->id) ?>">
                        <h4 class="reset-gap"><?php echo $this->escape($activeGroup->name); ?></h4>
                    </a>
                    <p >
                        <?php echo JText::sprintf( (CStringHelper::isPlural( $memberCount)) ? 'COM_COMMUNITY_GROUPS_MEMBER_COUNT_MANY' : 'COM_COMMUNITY_GROUPS_MEMBER_COUNT' , $memberCount ); ?>
                    </p>
                </div>
            </div>

		<?php } ?>

</div>
