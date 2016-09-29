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

if( $fields )
{
	$required	= false;
?>

<div class="joms-page">

	<form action="<?php echo CRoute::getURI(); ?>" method="post" id="jomsForm" class="joms-forms" class="community-form-validate">
<?php
	foreach( $fields as $name => $fieldGroup )
	{
		$fieldName	= $name == 'ungrouped' ? '' : $name;
?>

		<legend class="joms-form__legend"><?php echo JText::_( $fieldName ); ?></legend>


<?php
		foreach($fieldGroup as $field )
		{
			$field = Joomla\Utilities\ArrayHelper::toObject ( $field );
			if( !$required && $field->required == 1 )
			{
				$required	= true;
			}

			$html = CProfileLibrary::getFieldHTML($field);
?>
            <div class="joms-form__group" >
                <span id="lblfield<?php echo $field->id;?>" for="field<?php echo $field->id;?>"   ><?php echo JText::_($field->name); ?>
                <?php if($field->required == 1) echo '<span class="joms-required">*</span>'; ?>
                </span>
                <?php echo $html; ?>
            </div>

<?php
		}
?>

<?php
	}
?>

<?php
	if( $required )
	{
?>
        <div class="joms-form__group">
            <span></span>
            <?php echo JText::_( 'COM_COMMUNITY_REGISTER_REQUIRED_FIELDS' ); ?>
        </div>

<?php } ?>

    <div class="joms-form__group">
        <span></span>
        <input class="joms-button joms-button--small joms-button--primary" type="submit" id="btnSubmit" value="<?php echo JText::_('COM_COMMUNITY_NEXT'); ?>" name="submit">
    </div>

	<input type="hidden" name="profileType" value="<?php echo $profileType;?>" />
	<input type="hidden" name="task" value="updateProfile" />
	</form>
	<script type="text/javascript">
	    cvalidate.init();
	    cvalidate.setSystemText('REM','<?php echo addslashes(JText::_("COM_COMMUNITY_ENTRY_MISSING")); ?>');

		joms.jQuery( '#jomsForm' ).submit( function() {
		    joms.jQuery('#btnSubmit').hide();
			joms.jQuery('#cwin-wait').show();
		});
	</script>
<?php
}
else
{
?>
	<div><?php echo JText::_('COM_COMMUNITY_NO_CUSTOM_PROFILE_CREATED_YET');?></div>
<?php
}
?>
</div>
