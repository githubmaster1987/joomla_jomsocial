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

$version = new JVersion();
$version = floatval($version->RELEASE);

if($version >= 3) {
    $saveOrderingUrl = 'index.php?option=com_community&view=moods&task=ajaxReorder';
    echo JHtml::_('sortablelist.sortable', 'moodList', 'adminForm', '', $saveOrderingUrl);
}
?>
<style type="text/css">
    .moodImage {
        max-width:30px;
        max-height:30px;
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
        if( action == 'newMood' )
        {
            window.location	= 'index.php?option=com_community&view=moods&layout=edit';
            return;
        }
        submitform( action );
    }
</script>
<form action="index.php?option=com_community" method="post" name="adminForm" id="adminForm">
    <table class="table table-striped" id="moodList">
        <thead>
        <tr>
            <th width="10"></th>
            <th width="10">
                <input type="checkbox" name="toggle" value="" onclick="Joomla.checkAll(this)" />
                <span class="lbl"></span>
            </th>
            <th width="10">
                <?php echo JText::_('COM_COMMUNITY_MOODS_IMAGE')?>
            </th>
            <th width="200">
                <?php echo JText::_('COM_COMMUNITY_TITLE');?>
            </th>
            <th>
                <?php echo JText::_('COM_COMMUNITY_MOODS_DESCRIPTION'); ?>
            </th>
            <th width="50">
                <?php echo JText::_('COM_COMMUNITY_PUBLISHED'); ?>
            </th>
        </tr>
        </thead>
        <tbody>
        <?php $i = 0; ?>
        <?php
        if( empty( $this->moods ) )
        {
            //this should never happen
            ?>
            <tr>
                <td colspan="7" align="center"><?php echo JText::_('COM_COMMUNITY_MOODS_NONE_CREATED');?></td>
            </tr>
        <?php
        }
        ?>
        <?php foreach( $this->moods as $row ): ?>
            <tr>
                <td align="center">
                   <span class="sortable-handler" style="cursor: move;">
                       <i class="icon-menu"></i>
                   </span>
                </td>
                <td>
                    <?php echo JHTML::_('grid.id', $i++, $row->id); ?>
                    <span class="lbl"></span>
                </td>
                <td>
                    <?php if($row->image):?>
                        <a href="index.php?option=com_community&view=moods&layout=edit&moodid=<?php echo $row->id;?>">
                            <img src="<?php echo $row->image; ?>" class="moodImage">
                        </a>
                    <?php endif; ?>
                </td>
                <td>
                    <?php if($row->custom): ?>
                        <a href="index.php?option=com_community&view=moods&layout=edit&moodid=<?php echo $row->id;?>"><?php echo $row->title; ?></a>
                    <?php else:
                        echo $row->title;
                    endif; ?>
                </td>
                <td>
                    <a href="index.php?option=com_community&view=moods&layout=edit&moodid=<?php echo $row->id;?>"><?php echo $row->description; ?></a>
                </td>

                <td id="published<?php echo $row->id;?>" align="center" class='center'>
                    <?php echo $this->getPublish( $row , 'published' , 'moods,ajaxTogglePublish' );?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <input type="hidden" name="view" value="moods" />
    <input type="hidden" name="option" value="com_community" />
    <input type="hidden" name="task" value="moods" />
    <input type="hidden" name="boxchecked" value="0" />
    <input type="hidden" name="filter_order" value="<?php echo $this->lists['order']; ?>" />
    <input type="hidden" name="filter_order_Dir" value="<?php echo $this->lists['order_Dir']; ?>" />
</form>
