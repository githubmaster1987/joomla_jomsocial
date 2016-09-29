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

$svgPath = CFactory::getPath('template://assets/icon/joms-icon.svg');
include_once $svgPath;

?>

<div class="joms-module--statistics">

	<ul class="joms-list joms-list--stats">

	<?php
		foreach($stats as $stat)
		{
			switch($stat)
			{
				case 't_members':
					$name = JText::_("MOD_COMMUNITY_STATISTICS_MEMBERS");
					$total = $params->get('t_members');
					$icon = 'user';
					break;
				case 't_groups':
					$name = JText::_("MOD_COMMUNITY_STATISTICS_GROUPS");
					$total = $params->get('t_groups');
					$icon = 'users';
					break;
				case 't_discussions':
					$name = JText::_("MOD_COMMUNITY_STATISTICS_DISCUSSIONS");
					$total = $params->get('t_discussions');
					$icon = 'bubble';
					break;
				case 't_albums':
					$name = JText::_("MOD_COMMUNITY_STATISTICS_ALBUMS");
					$total = $params->get('t_albums');
					$icon = 'image';
					break;
				case 't_photos':
					$name = JText::_("MOD_COMMUNITY_STATISTICS_PHOTOS");
					$total = $params->get('t_photos');
					$icon = 'images';
					break;
				case 't_videos':
					$name = JText::_("MOD_COMMUNITY_STATISTICS_VIDEOS");
					$total = $params->get('t_videos');
					$icon = 'film';
					break;
				case 't_bulletins':
					$name = JText::_("MOD_COMMUNITY_STATISTICS_BULLETINS");
					$total = $params->get('t_bulletins');
					$icon = 'bullhorn';
					break;
				case 't_activities':
					$name = JText::_("MOD_COMMUNITY_STATISTICS_ACTIVITIES");
					$total = $params->get('t_activities');
					$icon = 'star';
					break;
				case 't_walls':
					$name = JText::_("MOD_COMMUNITY_STATISTICS_WALLPOST");
					$total = $params->get('t_walls');
					$icon = 'pencil';
					break;
				case "t_events":
					$name	= JText::_("MOD_COMMUNITY_STATISTICS_EVENTS");
					$total	= $params->get('t_events');
					$icon = 'calendar';
					break;
				case 'genders':
					$male = JText::_("MOD_COMMUNITY_STATISTICS_MALES");
					$female = JText::_("MOD_COMMUNITY_STATISTICS_FEMALES");
					$unspecified = JText::_("MOD_COMMUNITY_STATISTICS_UNSPECIFIED");
					$total_males = $params->get('t_gender_males');
					$total_females = $params->get('t_gender_females');
					$total_unspecified = $params->get('t_gender_unspecified');
					$icon = 'user';
					break;
			}


			if($stat == "genders")
			{
				if($params->get('show_males', 1))
				{
	?>
		        <li class="joms-text--light" title="<?php echo JText::_("MOD_COMMUNITY_STATISTICS_TOTAL") . " " . $male; ?> : <?php echo $total_males; ?>">
		            <?php echo JText::_("MOD_COMMUNITY_STATISTICS_TOTAL") . " " . $male; ?>
		            <span><?php echo $total_males; ?>
	                <svg class="joms-icon" viewBox="0 0 14 20">
	                    <use xlink:href="<?php echo CRoute::getURI(); ?>#joms-icon-<?php echo $icon; ?>"/>
	                </svg>
		            </span>
		        </li>
	<?php
				}
				if($params->get('show_females', 1))
				{
	?>
		        <li class="joms-text--light" title="<?php echo JText::_("MOD_COMMUNITY_STATISTICS_TOTAL") . " " . $female; ?> : <?php echo $total_females; ?>">
		            <?php echo JText::_("MOD_COMMUNITY_STATISTICS_TOTAL") . " " . $female; ?>
		            <span><?php echo $total_females; ?>
					<svg class="joms-icon" viewBox="0 0 14 20">
	                    <use xlink:href="<?php echo CRoute::getURI(); ?>#joms-icon-<?php echo $icon; ?>"/>
	                </svg>
		        	</span>
		        </li>
	<?php
				}
				if($params->get('show_unspecified', 1)){
	?>
		        <li class="joms-text--light" title="<?php echo JText::_("MOD_COMMUNITY_STATISTICS_TOTAL") . " " . $unspecified; ?> : <?php echo $total_unspecified; ?>">
		            <?php echo JText::_("MOD_COMMUNITY_STATISTICS_TOTAL") . " " . $unspecified; ?>
		            <span><?php echo $total_unspecified; ?>
					<svg class="joms-icon" viewBox="0 0 14 20">
	                    <use xlink:href="<?php echo CRoute::getURI(); ?>#joms-icon-<?php echo $icon; ?>"/>
	                </svg>
		            </span>
		        </li>
	<?php
				}
			}
			else
			{
	?>
	        <li class="joms-text--light" title="<?php echo JText::_("MOD_COMMUNITY_STATISTICS_TOTAL") . " " . $name; ?> : <?php echo $total; ?>">
	            <?php echo JText::_("MOD_COMMUNITY_STATISTICS_TOTAL") . " " . $name; ?>
	            <span><?php echo $total; ?>
                <svg class="joms-icon" viewBox="0 0 14 20">
                    <use xlink:href="<?php echo CRoute::getURI(); ?>#joms-icon-<?php echo $icon; ?>"/>
                </svg>
	            </span>
	        </li>
	<?php
			}
		}
	?>

	</ul>

</div>
