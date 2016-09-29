<?php
/**
* @copyright (C) 2013 iJoomla, Inc. - All rights reserved.
* @license GNU General Public License, version 2 (http://www.gnu.org/licenses/gpl-2.0.html)
* @author iJoomla.com <webmaster@ijoomla.com>
* @url https://www.jomsocial.com/license-agreement
* The PHP code portions are distributed under the GPL license. If not otherwise stated, all images, manuals, cascading style sheets, and included JavaScript *are NOT GPL, and are released under the IJOOMLA Proprietary Use License v1.0
* More info at https://www.jomsocial.com/license-agreement
*/
defined('_JEXEC') or die();
?>
<?php
    if( $sortItems )
    {
?>
<script>
    function joms_change_filterdropdown( value ) {
        var url = value;
        url = url.replace( '__sort__', value );

        window.location = url;
    }
</script>
    <select class="joms-select" onchange="joms_change_filterdropdown(this.value);">
        <!-- <li class="filter-label"><?php echo JText::_('COM_COMMUNITY_SORT_BY'); ?>:</li> -->
        <?php
        $categoryid = $jinput->get->get('categoryid','');
        $catId      = $jinput->get->get('catid','');
        $groupid    = $jinput->get->get('groupid','');
        $eventid    = $jinput->get->get('eventid','');

        foreach( $sortItems as $key => $option )
        {
            $categoryLink   = ($categoryid) ? '&categoryid='.$categoryid : '';
            $catLink        = ($catId) ? '&catid='.$catId : '';
            $groupLink      = ($groupid) ? '&groupid='.$groupid : '';
            $eventLink      = ($eventid) ? '&eventid='.$eventid : '';

            $queries['sort'] = $key;
            $link = 'index.php?'. $uri->buildQuery($queries).$categoryLink.$catLink.$groupLink.$eventLink;
            $link = CRoute::_($link);
        ?>
            <option <?php if($key==JString::trim($selectedSort)) { ?>selected<?php }?> value="<?php echo $link; ?>"><?php echo $option; ?></option>
        <?php
        }
        ?>
    </select>
<?php
        $queries['sort'] = $selectedSort;
    }
?>

<?php
    if( $filterItems )
    {
?>
    <ul class="joms-list--alphabet">
        <!-- <li class="filter-label"><?php echo JText::_('COM_COMMUNITY_FILTER_SHOW'); ?></li> -->
    <?php
    foreach( $filterItems as $key => $option )
    {
        $queries['filter']      = $key;

        // We need to reset the pagination limitstart so the pagination will not affect the filter
        unset($queries['limitstart']);
        $link = 'index.php?'. $uri->buildQuery($queries);

        $link = CRoute::_($link);
    ?>
        <li class="<?php if($key==JString::trim($selectedFilter)) { ?> active<?php } ?>">
            <a href="<?php echo $link; ?>"><?php echo $option; ?></a>
        </li>
    <?php
    }
    ?>
    </ul>
<?php
    }
?>