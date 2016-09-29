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

<div class="joms-page">

    <?php
    if( $fields )
    {
    	$required	= false;
    ?>

        <form method="POST" action="<?php echo CRoute::getURI(); ?>" onsubmit="return joms_validate_form( this );">

    <?php
    	foreach( $fields as $group )
    	{
            // if there is no field for this group, move to next.
            if(count($group->fields) == 0){
                continue;
            }
    		$fieldName	= $group->name == 'ungrouped' ? '' : $group->name;
    ?>

            <legend class="joms-form__legend"><?php echo JText::_( $fieldName ); ?></legend>


    <?php
    		foreach($group->fields as $field )
    		{
    			if( !$required && $field->required == 1 )

                $required	= true;

    			$html = CProfileLibrary::getFieldHTML($field);
    ?>

				<div class="joms-form__group has-privacy">
					<span id="lblfield<?php echo $field->id; ?>"><?php

                        echo JText::_( $field->name );
                        if ( $field->required == 1 ) {
                            echo ' <span class="joms-required">*</span>';
                        }

                    ?></span>
					<?php echo $html; ?>
					<?php echo CPrivacy::getHTML('privacy' . $field->id); ?>
				</div>

    <?php
    		}
    ?>

    <?php
    	}
    ?>
        <div class="joms-form__group">
            <span></span>
            <?php echo JText::_('COM_COMMUNITY_REGISTER_REQUIRED_FIELDS'); ?>
        </div>
		<div class="joms-form__group">
			<span></span>
            <button type="submit" name="submit" id="btnSubmit" class="joms-button--primary joms-button--full-small">
                <?php echo JText::_('COM_COMMUNITY_REGISTER'); ?>
                <span class="joms-loading" style="display:none">&nbsp;
                    <img src="<?php echo JURI::root(true) ?>/components/com_community/assets/ajax-loader.gif" alt="loader">
                </span>
            </button>
		</div>

    	<input type="hidden" name="profileType" value="<?php echo $profileType;?>" />
    	<input type="hidden" name="task" value="registerUpdateProfile" />
    	<input type="hidden" id="authenticate" name="authenticate" value="0" />
    	<input type="hidden" id="authkey" name="authkey" value="" />
    	</form>

        <script>
            window.joms_queue || (joms_queue = []);
            joms_queue.push(function() {
                function insertAuthkey() {
                    joms.ajax({
                        func: 'register,ajaxAssignAuthKey',
                        data: [ '_dummy_' ],
                        callback: function( json ) {
                            joms.jQuery('#authenticate').val( 1 );
                            joms.jQuery('#authkey').val( json.authKey );
                        }
                    });
                }

                var timer = setInterval(function() {
                    if ( joms.ajax ) {
                        clearInterval( timer );
                        insertAuthkey();
                    }
                }, 100);
            });
        </script>
    <?php
    }
    else
    {
    ?>
    	<div class="cAlert"><?php echo JText::_('COM_COMMUNITY_NO_CUSTOM_PROFILE_CREATED_YET');?></div>
    <?php
    }
    ?>

</div>

<script>

    // Validate form before submit.
    function joms_validate_form( form ) {
        if ( window.joms && joms.util && joms.util.validation ) {
            joms.jQuery('.joms-loading').show();
            joms.util.validation.validate( form, function( errors ) {
                if ( !errors ) {
                    joms.jQuery( form ).removeAttr('onsubmit');
                    setTimeout(function() {
                        joms.jQuery( form ).find('button[type=submit]').click();
                    }, 500 );
                } else {
                    joms.jQuery('.joms-loading').hide();
                }
            });
        }
        return false;
    }

</script>
