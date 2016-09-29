<?php
    /**
     * @copyright (C) 2014 iJoomla, Inc. - All rights reserved.
     * @license GNU General Public License, version 2 (http://www.gnu.org/licenses/gpl-2.0.html)
     * @author iJoomla.com <webmaster@ijoomla.com>
     * @url https://www.jomsocial.com/license-agreement
     * The PHP code portions are distributed under the GPL license. If not otherwise stated, all images, manuals, cascading style sheets, and included JavaScript *are NOT GPL, and are released under the IJOOMLA Proprietary Use License v1.0
     * More info at https://www.jomsocial.com/license-agreement
     */

    defined('_JEXEC') or die('Restricted access');

    $access = (isset($stream->access)) ? $stream->access : (isset($activity->access) ? $activity->access : $act->access)  ;

    $icon = "earth";
    $show = "public";
    switch($access){
        case '20' :
            $icon = "users";
            $show = "members";
            break;
        case '30' :
            $icon = "user";
            $show = "friends";
            break;
        case '40' :
            $icon = "lock";
            $show = "meonly";
            break;
        default:
            break;
    }
?>

<svg viewBox="0 0 16 16" class="joms-icon joms-show-<?php echo $icon; ?>">
    <use xlink:href="<?php echo JUri::getInstance(); ?>#joms-icon-<?php echo $icon; ?>"></use>
</svg>
