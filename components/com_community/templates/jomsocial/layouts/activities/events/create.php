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

$user = CFactory::getUser($this->act->actor);
/* Temporary fix since we not yet complete move to CActivity */
if ( $this->act instanceof  CTableActivity ) {
    /* If this's CTableActivity then we use getProperties() */
    $activity = new CActivity($this->act->getProperties());
}else {
    /* If it's standard object than we just passing it */
    $activity = new CActivity($this->act);
}

$format = ($config->get('eventshowampm')) ? JText::_('COM_COMMUNITY_DATE_FORMAT_LC2_12H') : JText::_('COM_COMMUNITY_DATE_FORMAT_LC2_24H');

$browser = $_SERVER['HTTP_USER_AGENT'];
$truncateVal = 60;

?>

<div class="joms-stream__header">
    <div class= "joms-avatar--stream <?php echo CUserHelper::onlineIndicator($user); ?>">
        <a href="<?php echo CUrlHelper::userLink($user->id); ?>">
            <img data-author="<?php echo $user->id; ?>" src="<?php echo $user->getThumbAvatar(); ?>" alt="<?php echo $user->getDisplayName(); ?>">
        </a>
    </div>
    <div class="joms-stream__meta">
        <?php if($user->id > 0) : ?>
        <a href="<?php echo CUrlHelper::userLink($user->id); ?>" class="joms-stream__user"><?php echo $user->getDisplayName(); ?></a>
        <?php else: ?>
        <?php echo $user->getDisplayName(); ?>
        <?php endif;?>
        <?php
            echo ($event->type != 'group') ? JText::_('COM_COMMUNITY_EVENTS_ACTIVITIES_NEW_EVENT') : JText::sprintf('COM_COMMUNITY_EVENTS_ACTIVITIES_NEW_GROUP_EVENT', CRoute::_('index.php?option=com_community&view=groups&task=viewgroup&groupid='.$group->id), $this->escape($group->name));
        ?>
        <span class="joms-stream__time"><small>
            <?php echo $activity->getCreateTimeFormatted(); ?>
        </small></span>
    </div>
</div>
<div class="joms-stream__body">

    <div class="joms-media">
        <h4 class="joms-text--title"><a href="<?php echo $this->event->getLink();?>"><?php echo JHTML::_('string.truncate', $this->event->title, $truncateVal, true, false ); ?></a></h4>
        <p><?php echo JHTML::_('string.truncate',$this->event->description,$truncateVal,true,false); ?></p>
        <ul class="joms-list">
            <li class="joms-list__item">
                <svg viewBox="0 0 16 18" class="joms-icon">
                    <use xlink:href="<?php echo CRoute::getURI(); ?>#joms-icon-calendar"></use>
                </svg>
            <?php echo CTimeHelper::getFormattedTime($this->event->startdate, $format); ?></li>
            <li class="joms-list__item">
                <svg viewBox="0 0 16 16" class="joms-icon">
                    <use xlink:href="<?php echo CRoute::getURI(); ?>#joms-icon-location"></use>
                </svg>
            <?php echo CActivities::format($activity->get('location')); ?></li>
        </ul>
    </div>

</div>
<?php $this->load('stream/footer'); ?>

