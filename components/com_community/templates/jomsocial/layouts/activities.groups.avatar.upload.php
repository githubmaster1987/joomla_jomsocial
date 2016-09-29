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

$user = CFactory::getUser($act->actor);

$date = JDate::getInstance($act->created);
if ( $config->get('activitydateformat') == "lapse" ) {
  $createdTime = CTimeHelper::timeLapse($date);
} else {
  $createdTime = $date->format($config->get('profileDateFormat'));
}

$groupLink = CRoute::_('index.php?option=com_community&view=groups&task=viewgroup&groupid='.$act->groupid);
$group = JTable::getInstance('Group', 'CTable');
$group->load($act->groupid);
    $groupParams = new JRegistry($group->params);

$isPhotoModal = $config->get('album_mode') == 1;

if(!isset($act->appTitle)) {
  $act->appTitle = $group->name;
}

?>

<div class="joms-stream__header">
    <div class="joms-avatar--comment <?php echo CUserHelper::onlineIndicator($user); ?>">
        <img src="<?php echo $user->getThumbAvatar(); ?>" alt="<?php echo $user->getDisplayName(); ?>" data-author="<?php echo $user->id; ?>">
    </div>
    <div class="joms-stream__meta">
        <a href="<?php echo CUrlHelper::userLink($user->id); ?>"><?php echo $user->getDisplayName(); ?></a>
        <span><?php echo JText::sprintf('COM_COMMUNITY_CHANGE_GROUP_AVATAR', $groupLink, $group->name); ?></span>
        <span class="joms-stream__time"><small><?php echo $createdTime; ?></small></span>
    </div>
    <?php
        $my = CFactory::getUser();
        $this->load('activities.stream.options');
    ?>
</div>

<div class="joms-stream__body">
    <div class="joms-avatar">
        <a href="javascript:" onclick="joms.api.photoZoom('<?php echo $group->getAvatar(); ?>');">
            <img src="<?php echo $group->getAvatar('avatar'); ?>" alt="<?php echo $group->name; ?>" >
        </a>
    </div>
</div>

<?php $this->load('stream/footer'); ?>
