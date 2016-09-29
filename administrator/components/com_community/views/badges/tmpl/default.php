<?php
/**
* @copyright (C) 2013 iJoomla, Inc. - All rights reserved.
* @license GNU General Public License, version 2 (http://www.gnu.org/licenses/gpl-2.0.html)
* @author iJoomla.com <webmaster@ijoomla.com>
* @url https://www.jomsocial.com/license-agreement
* The PHP code portions are distributed under the GPL license. If not otherwise stated, all images, manuals, cascading style sheets, and included JavaScript *are NOT GPL, and are released under the IJOOMLA Proprietary Use License v1.0
* More info at https://www.jomsocial.com/license-agreement
*/
// Disallow direct access to this file
defined('_JEXEC') or die('Restricted access');
CommunityLicenseHelper::disabledHtml();
?>
<style type="text/css">
    .badgeImage {
    max-width:75px;
        max-height:75px;
    }
    .container-main {
        padding-bottom: 0 !important;
    }
</style>

<script type="text/javascript" language="javascript">
    /**
     * This function needs to be here because, Joomla toolbar calls it
     **/
    Joomla.submitbutton = function(action){
        submitbutton( action );
    }

    function submitbutton( action )
    {
        if( action == 'newBadge' )
        {
            window.location	= 'index.php?option=com_community&view=badges&layout=edit';
            return;
        }
        submitform( action );
    }
</script>

<form action="index.php?option=com_community" method="post" name="adminForm" id="adminForm">
<table class="table table-bordered table-hover">
	<thead>
		<tr class="title">
			<th width="10">#</th>
			<th width="10">
				<input type="checkbox" name="toggle" value="" onclick="Joomla.checkAll(this)" />
				<span class="lbl"></span>
			</th>
			<th>
				<?php echo JText::_('COM_COMMUNITY_BADGES_IMAGE')?>
</th>
<th width="200">
    <?php echo JText::_('COM_COMMUNITY_TITLE');?>
</th>
<th>
    <?php echo JText::_('COM_COMMUNITY_BADGES_POINTS'); ?>
</th>
<th width="50">
    <?php echo JText::_('COM_COMMUNITY_PUBLISHED'); ?>
</th>
</tr>
</thead>
<?php $i = 0; ?>
<?php
if( empty( $this->badges ) )
{
    ?>
    <tr>
        <td colspan="7" align="center"><?php echo JText::_('COM_COMMUNITY_BADGES_NONE_CREATED');?></td>
    </tr>
<?php
}
?>
<?php foreach( $this->badges as $row ): ?>
    <tr>
        <td align="center">
            <?php echo ( $i + 1 ); ?>
        </td>
        <td>
            <?php echo JHTML::_('grid.id', $i++, $row->id); ?>
            <span class="lbl"></span>
        </td>
        <td>
            <?php if($row->image) { ?>
            <a href="index.php?option=com_community&view=badges&layout=edit&badgeid=<?php echo $row->id;?>">
                <img src="<?php echo $row->image;?>" class="badgeImage" />
            </a>
            <?php } ?>
        </td>
        <td>
                <a href="index.php?option=com_community&view=badges&layout=edit&badgeid=<?php echo $row->id;?>">
                    <?php echo $row->title; ?>
                </a>
        </td>
        <td>
            <?php echo $row->points; ?>
        </td>

        <td id="published<?php echo $row->id;?>" align="center" class='center'>
            <?php echo $this->getPublish( $row , 'published' , 'badges,ajaxTogglePublish' );?>
        </td>
    </tr>
<?php endforeach; ?>
</table>

<input type="hidden" name="view" value="badges" />
<input type="hidden" name="option" value="com_community" />
<input type="hidden" name="task" value="badges" />
<input type="hidden" name="boxchecked" value="0" />
<input type="hidden" name="filter_order" value="<?php echo $this->lists['order']; ?>" />
<input type="hidden" name="filter_order_Dir" value="<?php echo $this->lists['order_Dir']; ?>" />
</form>
