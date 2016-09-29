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
<script language="javascript" type="text/javascript">
joms.jQuery.noConflict();

function submitbutton() {
	var r = new RegExp("[\<|\>|\"|\'|\%|\;|\(|\)|\&|\+|\-]", "i");

	//hide all the error messsage span 1st
	joms.jQuery('#jsemail').removeClass('invalid');
	joms.jQuery('#errjsemailmsg').hide();
	joms.jQuery('#errjsemailmsg').html('&nbsp');

	// do field validation
	var isValid	= true;

	if(joms.jQuery('#jsemail').val() !=  joms.jQuery('#email').val())
	{
		regex=/^[a-zA-Z0-9._-]+@([a-zA-Z0-9.-]+\.)+[a-zA-Z0-9.-]{2,4}$/;
	   	isValid = regex.test(joms.jQuery('#jsemail').val());

		var fieldname = joms.jQuery('#jsemail').attr('name');;
		if(isValid == false){
			joms.jQuery('#jsemail').addClass('invalid');
			cvalidate.setMessage(fieldname, '', 'COM_COMMUNITY_INVALID_EMAIL');
		}
   	}

	if(! isValid) {
	    joms.jQuery('#btnSubmit').show();
		joms.jQuery('#cwin-wait').hide();
 	}

	return isValid;
}
</script>

<div class="joms-page">

<form action="" method="post" id="jomsForm" class="joms-form">
    <h3 class="joms-page__title"><?php echo JText::_('COM_COMMUNITY_RESEND_ACTIVATION') ?></h3>
    <p><?php echo JText::_('COM_COMMUNITY_ACTIVATION_ENTER_EMAIL'); ?></p>
    <div class="joms-gap"></div>
    <div class="joms-form__group">
        <span><?php echo JText::_( 'COM_COMMUNITY_EMAIL' ); ?></span>
        <input class="joms-input" type="text" id="jsemail" name="jsemail" required>
    </div>

    <div class="joms-form__group">
        <span></span>
        <input class="joms-button--primary" type="submit" id="btnSubmit" value="<?php echo JText::_('COM_COMMUNITY_SEND_ACTIVATION'); ?>" name="submit">
    </div>

    <input type="hidden" name="option" value="com_community" />
    <input type="hidden" name="view" value="register" />
    <input type="hidden" name="task" value="activationResend" />
    <?php echo JHTML::_( 'form.token' ); ?>
</form>

</div>



<script type="text/javascript">
    cvalidate.init();
    cvalidate.setSystemText('REM','<?php echo addslashes(JText::_("COM_COMMUNITY_ENTRY_MISSING")); ?>');

	joms.jQuery( '#jomsForm' ).submit( function() {
	    joms.jQuery('#btnSubmit').hide();
		joms.jQuery('#cwin-wait').show();

		return submitbutton();
	});
</script>
