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

$svgPath = CFactory::getPath('template://assets/icon/joms-icon.svg');
include_once $svgPath;

?>

<?php if ( !empty( $groups ) ) { ?>

<ul class="joms-list--group">
    <?php foreach ( $groups as $group ) {

    $table  =  JTable::getInstance( 'Group' , 'CTable' );
    $table->load($group->id);

    ?>

        <li class="joms-stream__header no-gap">
            <div class="joms-popover__avatar">
                <div class="joms-avatar">
                    <img src="<?php echo $table->getThumbAvatar(); ?>" alt="<?php echo CStringHelper::escape($group->name); ?>" >
                </div>
            </div>
            <div class="joms-popover__content">
                <h5 class="reset-gap"><a href="<?php echo $group->getLink(); ?>"><?php echo CStringHelper::escape($group->name); ?></a></h5>
                <div class="joms-gap--small"></div>
                <div>
                    <ul class="joms-list joms-list--inline joms-text--light">
                        <li>
                            <svg class="joms-icon" viewBox="0 0 14 20">
                                <use xlink:href="<?php echo CRoute::getURI(); ?>#joms-icon-users"/>
                            </svg>
                            <?php echo $group->membercount; ?>
                        </li>
                        <li>
                            <svg class="joms-icon" viewBox="0 0 14 20">
                                <use xlink:href="<?php echo CRoute::getURI(); ?>#joms-icon-eye"/>
                            </svg>
                            <?php echo $group->hits; ?>
                        </li>
                        <li>
                            <svg class="joms-icon" viewBox="0 0 14 20">
                                <use xlink:href="<?php echo CRoute::getURI(); ?>#joms-icon-bubble"/>
                            </svg>
                            <?php echo $group->discusscount; ?>
                        </li>
                        <li>
                            <svg class="joms-icon" viewBox="0 0 14 20">
                                <use xlink:href="<?php echo CRoute::getURI(); ?>#joms-icon-pencil"/>
                            </svg>
                            <?php echo $group->wallcount; ?>
                        </li>
                    </ul>
                </div>
            </div>
        </li>
    <?php } ?>
</ul>

<?php } else { ?>
    <div class="joms-blankslate"><?php echo JText::_('COM_COMMUNITY_GROUPS_NOITEM'); ?></div>
<?php } ?>

<div class="joms-gap"></div>
<a href="<?php echo CRoute::_('index.php?option=com_community&view=groups&task=display'); ?>" class="joms-button--link" ><small><?php echo JText::_('COM_COMMUNITY_GROUPS_VIEW_ALL'); ?></small></a>
