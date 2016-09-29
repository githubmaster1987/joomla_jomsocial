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
?>
<style type="text/css">
    label { float:left; clear:none; display:block; padding: 2px 1em 0 0; }
    #js-cpanel .ace-file-input {
        margin-bottom: 0;
    }
    .ace-file-input .icon-picture, .ace-file-input .icon-upload-alt {
        height: 24px;
    }
    #js-cpanel .ace-file-input label.selected .icon-picture {
        line-height: 25px !important;
    }

    .badgeImage {
        max-width:  100px;
        max-height: 100px;
        margin-bottom:20px;
    }
</style>

<form name="adminForm" id="adminForm" action="index.php?option=com_community" method="POST" enctype="multipart/form-data">
<table  width="100%" class="paramlist admintable" cellspacing="1">
    <tr>
        <td class="paramlist_key">
            <label for="name" class="title" title="<?php echo JText::_('COM_COMMUNITY_BADGES_TITLE_TIPS'); ?>">
                <span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_BADGES_TITLE_TIPS'); ?>"><?php echo JText::_('COM_COMMUNITY_BADGES_TITLE'); ?></span>
                <span class="required-sign"> *</span>
            </label>
        </td>
        <td class="paramlist_value">
            <input type="text" name="title" id="title" value="<?php echo $this->badge->title; ?>" style="width: 200px;" />
        </td>
    </tr>
    <tr>
        <td class="paramlist_key">
            <label for="name" class="title" title="<?php echo JText::_('COM_COMMUNITY_BADGES_POINTS_TIPS'); ?>">
                <span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_BADGES_POINTS_TIPS'); ?>"><?php echo JText::_('COM_COMMUNITY_BADGES_POINTS'); ?></span>
                <span class="required-sign"> *</span>
            </label>
        </td>
        <td class="paramlist_value">
            <input type="text" name="points" id="points" value="<?php echo $this->badge->points; ?>" style="width: 200px;" />
        </td>
    </tr>
    <tr>
        <td class="paramlist_key">
            <label for="image"><span class="js-tooltip" title="<?php echo JText::_( 'COM_COMMUNITY_BADGES_IMAGE_TIPS' );?>"><?php echo JText::_( 'COM_COMMUNITY_BADGES_IMAGE' );?></span></label>
        </td>
        <td class="paramlist_value">
            <?php if($this->badge->image) { ?>
                <img src="<?php echo $this->badge->image;?>" class="badgeImage" /><br/>
            <?php } ?>
            <div class="ace-file-input">

                <input type="file" name="badge_image" id="badge_image"/>
            </div>
        </td>
    </tr>
    <tr>
        <td class="paramlist_key">
            <label for="published"><span class="js-tooltip" title="<?php echo JText::_( 'COM_COMMUNITY_BADGES_PUBLISH_TIPS' );?>"><?php echo JText::_( 'COM_COMMUNITY_ENABLE' );?></span></label>
        </td>
        <td class="paramlist_value">
            <?php echo CHTMLInput::checkbox('published' ,'ace-switch ace-switch-5', null , isset($this->badge->published) ? $this->badge->published : 1); ?>
        </td>
    </tr>
</table>
<input type="hidden" name="view" value="badges" />
<input type="hidden" name="task" value="save" />
<input type="hidden" name="badgeid" value="<?php echo $this->badge->id; ?>" />
<input type="hidden" name="option" value="com_community" />
<input type="hidden" name="boxchecked" value="0" />
<?php echo JHTML::_( 'form.token' ); ?>
</form>

<script>

    var badges = [];
    <?php
    // output a javascript array of points already used in other badges
    if(sizeof($this->badges)) {
        foreach($this->badges as $badge) {
        if($badge->id == $this->badge->id) continue;
            echo "\nbadges.push('{$badge->points}');";
        }
    }
    ?>

    $( document ).ready(function() {

        Joomla.submitbutton = function(action){

            error = '';
            points = $('#points').val();

            // empty title
            if($('#title').val() == '')                 error = error + '\n<?php echo JText::_('COM_COMMUNITY_BADGES_ERROR_TITLE_EMPTY');?>';

            // points empty or not a umber
            if(points == '' || isNaN(points))           error = error + '\n<?php echo JText::_('COM_COMMUNITY_BADGES_ERROR_POINTS_NUMBER');?>';

            // points already taken
            if(points != '' && badges.indexOf(points)>=0)   error = error + '\n<?php echo JText::_('COM_COMMUNITY_BADGES_ERROR_POINTS_TAKEN');?>';

            if(['apply', 'save'].indexOf(action) >= 0 && !checkFiles()) {
                error = error + '\n<?php echo JText::_('COM_COMMUNITY_THEME_IMAGE_ERROR');?>';
            }

            if(!error) submitform(action);
            if(error) alert(error);
        }

        function checkFiles() {
            var isValid = true;
            $('#adminForm input[type=file]').each(function() {
                if ( this.value && !this.value.match(/\.(jpg|jpeg|png)$/i) ) {
                    isValid = false;
                    return false;
                }
            });
            return isValid;
        }

        $('#badge_image').ace_file_input({
            no_file:'No File ...',
            btn_choose:'Choose',
            btn_change:'Change'
        }).on('change', function(){
            // var files = $(this).data('ace_input_files');
            //or
            var files = $(this).ace_file_input('files');

            // var method = $(this).data('ace_input_method');
            //method will be either 'drop' or 'select'
            //or
            var method = $(this).ace_file_input('method');
        });

    });

    function js_Show(){
        joms.jQuery("#sbox-window, #sbox-overlay").show();
    }


</script>
