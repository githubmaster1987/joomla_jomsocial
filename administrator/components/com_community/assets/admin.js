function AzrulCommunity() {

    this.reportAutoUpdateProgress = function(percent){
        joms.jQuery('#autoupdate-progress').html(percent+'%');
    }

    this.runAutoUpdate = function(){
        var dosave = false;
        if( (joms.jQuery('#autoupdateordercode').data('orig') != joms.jQuery('#autoupdateordercode').val()) ||
            (joms.jQuery('#autoupdateemail').data('orig') != joms.jQuery('#autoupdateemail').val()) ){

            if(confirm('There has been a change in the license information. Would you like to save the change first ?')){
                dosave = true;
            }
        }
        joms.jQuery('.autoupdate-loader').show();
        joms.jQuery('#autoupdateordercode,#autoupdateemail,#autoupdatesubmit').attr('disabled', 'disabled');
        joms.jQuery('#autoupdatesubmit').val(joms.jQuery('#autoupdatesubmit').data('inprogresstext'));
        joms.jQuery('.do-download-update').remove();
        joms.jQuery('#autoupdate-progress').empty();
        if(!dosave){
            jax.call( 'community', 'admin,system,ajaxAutoupdate');
        }else{
            joms.jQuery('#autoupdateordercode').data('orig', joms.jQuery('#autoupdateordercode').val());
            joms.jQuery('#autoupdateemail').data('orig', joms.jQuery('#autoupdateemail').val());
            jax.call( 'community', 'admin,system,ajaxAutoupdate', joms.jQuery('#autoupdateordercode').val(), joms.jQuery('#autoupdateemail').val());
        }
    }

    this.resetprivacy   = function(){
        var profilePrivacy  = joms.jQuery('input[name=privacyprofile]').val();
        var friendPrivacy   = joms.jQuery('input[name=privacyfriends]').val();
        var photoPrivacy    = joms.jQuery('input[name=privacyphotos]').val();
        var privacyvideos   = joms.jQuery('input[name=privacyvideos]').val();
        var privacy_groups_list = joms.jQuery('input[name=privacy_groups_list]').val();

        jax.call( 'community' , 'admin,configuration,ajaxResetPrivacy' , photoPrivacy , profilePrivacy , friendPrivacy, privacyvideos , privacy_groups_list);
    }
    this.resetnotification  = function(label){
        var params = new Array();
        joms.jQuery(".notification_cfg").each(function(){
            if(joms.jQuery(this).attr('checked')=='checked'){
                params.push(joms.jQuery(this).attr('name') + '=1')
            } else {
                params.push(joms.jQuery(this).attr('name') + '=0')
            }
        });
        // joms.jQuery('#notification-update-result').parent().find('input').val(label);
        jax.call( 'community' , 'admin,configuration,ajaxResetNotification' , params.toString());
    }

    this.redirect       = function( url ){
        window.location.href = url;
    }

    this.removeOption   = function(){
        $('options').getElements('option').each(function(element, count){
            if(element.selected){
                element.remove();

                // Remove this value's from the hidden form so that when the user saves,
                // this element which is removed will not be saved.
                var childrens   = $('childrens').value.split(',');

                childrens.splice(childrens.indexOf(element.value), 1);

                $('childrens').value    = childrens.join();

                //console.log(chil);
                //console.log(childrens.splice(childrens.indexOf(element.value), 1).join());


            }
        });

        //console.log(childrens);
    }

    this.showAddOption  = function(){

        if($('showOption').getStyle('display') == 'none'){
            $('showOption').setStyle('display','inline');
            $('hideOption').setStyle('display','none');
            $('addOption').setStyle('display','none');
        } else {
            $('showOption').setStyle('display','none');
            $('hideOption').setStyle('display','inline');
            $('addOption').setStyle('display','inline');
        }
        //alert($('addOption').getStyle('display'));
        //$('addOption').setStyle('display','block');
    }

    this.saveGroupCategory  = function(){
        var values  = jax.getFormValues('editGroupCategory');

        jax.call('community','admin,groupcategories,ajaxSaveCategory', values);
    }

    this.editGroupCategory  = function(isEdit , windowTitle){
        var ajaxCall    = 'jax.call("community","admin,groupcategories,ajaxEditCategory" , ' + isEdit + ');';

        cWindowShow(ajaxCall , windowTitle , 430 , 280);
    }

    this.saveVideosCategory = function(){
        var values  = jax.getFormValues('editVideosCategory');

        jax.call('community','admin,videoscategories,ajaxSaveCategory', values);
    }

    this.editVideosCategory = function(isEdit , windowTitle){
        var ajaxCall    = 'jax.call("community","admin,videoscategories,ajaxEditCategory" , ' + isEdit + ');';

        cWindowShow(ajaxCall , windowTitle , 430 , 280);
    }

    this.newField = function(){

        cWindowShow('jax.call("community","admin,profiles,ajaxEditField","0");', '' , 650 ,420 );

        return false;
    }

    this.newFieldGroup = function(){

        cWindowShow('jax.call("community","admin,profiles,ajaxEditGroup","0");', '' , 450 ,200 );

        return false;
    }

    this.editField = function( id , title )
    {
        cWindowShow( 'jax.call("community", "admin,profiles,ajaxEditField", "' + id + '");' , '' , 650 , 420 );
        return false;
    }

    this.editFieldGroup = function( id , title )
    {
        cWindowShow( 'jax.call("community", "admin,profiles,ajaxEditGroup", "' + id + '");' , '' , 450 , 200 );
        return false;
    }

    this.addOption = function(parent){

        var addable = $('options').getElements('option').every( function(element, count){
            if(element.value == $('newoption').value){
                return false
            }
            return true;
        });

        if(addable){
            var el = new Element('option', {'value': $('newoption').value});

            el.setHTML($('newoption').value);
            el.setProperty('value', '0');

            // Clone element to the 'defaults' select list
            var defaultEl   = el.clone();

            el.injectInside($('options'));
            defaultEl.injectInside($('default'));
            // If parent is 0 we know this is a new record, so we dont add the options
            // in the database yet. We should only add the options once a user hit the 'save' button.
    //      if(parent != 0 || parent != '0'){
    //          // Call ajax function to add the options for this parent item.
    //          jax.call('community','cxAddOption', $('newoption').value, parent);
    //      }
        } else {
            $('ajaxResponse').setHTML('Option exists');
        }

    }

    this.togglePublish  = function( ajaxTask , id , type ){
        jax.call( 'community' , 'admin,' + ajaxTask , id , type );
    }

    this.changeType = function(type){
//      if( type == 'group' )
//      {
//          jQuery('.fieldGroups').css('display', 'none');
//      }
//      else
//      {
            jQuery('.fieldGroups').css('display', 'table-row');
//      }

                //Hide tooltip field for checkbox
        if(type=='checkbox'){
            jQuery('.fieldToolTip').css('display', 'none');
        }else{
            jQuery('.fieldToolTip').css('display', 'table-row');
        }

        if( type == 'select' || type == 'singleselect' || type == 'radio' || type == 'list' || type == 'checkbox' )
//      if(type == 'text' || type == 'group' || type == 'textarea' || type =='date' )
        {
            jQuery('.fieldSizes').css('display', 'none');
            jQuery('.fieldOptions').css('display', 'table-row');
        }
        else
        {
            jQuery('.fieldOptions').css('display', 'none');
            if( type == 'text' || type == 'textarea' )
            {
                jQuery('.fieldSizes').css('display', 'table-row');
            }
            else
            {
                jQuery('.fieldSizes').css('display', 'none');
            }
        }
        jax.call( 'community' , 'admin,profiles,ajaxGetFieldParams' , type );

    }

    this.insertParams = function( val ){
        joms.jQuery( '#fieldParams' ).html( val );
    }

    this.saveField = function(id){
        var values = jax.getFormValues('editField');

        jax.call('community','admin,profiles,ajaxSaveField', id , values);
    }

    this.saveFieldGroup = function(id){
        var values = jax.getFormValues('editField');

        jax.call('community','admin,profiles,ajaxSaveGroup', id , values);
    }

    this.showRemoveOption = function(){
        if($('addOption').getStyle('display') == 'inline'){
            // Hide the add option and show the remove option
            $('removeOption').setStyle('display','inline');
            $('addOption').setStyle('display','none');
        }
    }

    this.updateAttribute = function(id, type){
        jax.call('community','cxUpdateAttribute', id, type, $(type + id).value);
    }

    this.changeTemplate = function( templateName ){
        jax.call( 'community' , 'admin,templates,ajaxChangeTemplate' , templateName );
    }

    this.editTemplate = function( templateName , fileName , override ){
        jax.call( 'community' , 'admin,templates,ajaxLoadTemplateFile', templateName, fileName , override );
    }

    this.resetTemplateForm = function(){
        joms.jQuery('#data').val('');
        joms.jQuery('#filePath').html('');
    }

    this.resetTemplateFiles = function(){
        joms.jQuery('#templates-files-container').html('');
    }

    this.saveTemplateFile = function( override ){
        var fileData        = joms.jQuery( '#data' ).val();
        var fileName        = joms.jQuery( '#fileName' ).val();
        var templateName    = joms.jQuery( '#templateName' ).val();

        jax.call('community', 'admin,templates,ajaxSaveTemplateFile', templateName , fileName, fileData , override );
    }

    this.assignGroup = function( memberId ){
        cWindowShow('jax.call("community","admin,groups,ajaxAssignGroup", ' + memberId + ');', '' , 550 , 170 );

        var $ = joms.jQuery;

        var counter = 0;
        var timer = setInterval(function() {
            if ( $('select#groupid').length ) {
                clearInterval( timer );
                initForm();
            } else if ( ++counter >= 60 ) {
                clearInterval( timer );
            }
        }, 500 );

        var initForm = function() {
            jQuery('select#groupid').chosen({
                disable_search_threshold: 1,
                allow_single_deselect: true,
                placeholder_text_multiple: 'Select some options',
                placeholder_text_single: 'Select an option',
                no_results_text: 'No results match'
            });
            setTimeout(function() {
                jQuery('select#groupid').closest('.modal-body').css({ overflowY: 'visible' });
            }, 1000 );
        };
    }

    this.saveAssignGroup = function( memberId ){
        var group   = joms.jQuery('#groupid').val();

        if( group == '-1' )
        {
            joms.jQuery('#group-error-message').html('Please select a group');
            return false;
        }
        joms.jQuery('#assignGroup').submit();
    }

    this.editGroup = function( groupId ){
        cWindowShow('jax.call("community","admin,groups,ajaxEditGroup", ' + groupId + ');', joms_lang.COM_COMMUNITY_EDITING_GROUP , 550 , 450 );

        var $ = joms.jQuery;

        var counter = 0;
        var timer = setInterval(function() {
            if ( $('.joms-js--group-photo-flag').length ) {
                clearInterval( timer );
                initForm();
            } else if ( ++counter >= 60 ) {
                clearInterval( timer );
            }
        }, 500 );

        var initForm = function() {
            $('.joms-js--group-photo-flag').on( 'click', function() {
                var $div = $('.joms-js--group-photo-setting'),
                    $checkbox = $div.find('input');

                if ( this.checked ) {
                    $checkbox.removeAttr('disabled');
                    $div.show();
                } else {
                    $checkbox[0].checked = false;
                    $checkbox.attr('disabled', 'disabled');
                    $div.hide();
                }
            }).triggerHandler('click');

            $('.joms-js--group-video-flag').on( 'click', function() {
                var $div = $('.joms-js--group-video-setting'),
                    $checkbox = $div.find('input');

                if ( this.checked ) {
                    $checkbox.removeAttr('disabled');
                    $div.show();
                } else {
                    $checkbox[0].checked = false;
                    $checkbox.attr('disabled', 'disabled');
                    $div.hide();
                }
            }).triggerHandler('click');

            $('.joms-js--group-event-flag').on( 'click', function() {
                var $div = $('.joms-js--group-event-setting'),
                    $checkbox = $div.find('input');

                if ( this.checked ) {
                    $checkbox.removeAttr('disabled');
                    $div.show();
                } else {
                    $checkbox[0].checked = false;
                    $checkbox.attr('disabled', 'disabled');
                    $div.hide();
                }
            }).triggerHandler('click');
        };
    }

    this.changeGroupOwner = function( groupId ){
        cWindowShow('jax.call("community","admin,groups,ajaxChangeGroupOwner",' + groupId + ');', joms_lang.COM_COMMUNITY_CHANGE_GROUP_OWNER , 480 , 250 );
    }

    this.saveGroup = function(){
        joms.jQuery('#editgroup').submit();
    }

    this.saveGroupOwner = function(){
        document.forms['editgroup'].submit();
    }

    this.checkVersion = function(){
        cWindowShow('jax.call("community","admin,update,ajaxCheckVersion");', 'JomSocial' , 450 , 200 );
    }

    this.reportAction = function( actionId, ignore ){
        cWindowShow( 'jax.call("community","admin,reports,ajaxPerformAction", "' + actionId + '", "' + ignore + '");' , 'Report' , 450 , 200 );
    }

    this.ruleScan = function(){
        cWindowShow('jax.call("community","admin,userpoints,ajaxRuleScan");', 'User Rule Scan' , 450 ,400 );
        return false;
    }

    this.importUsers = function(){
        cWindowShow('jax.call("community","admin,users,importUsersForm");', joms_lang.COM_COMMUNITY_CONFIGURATION_IMPORT_USERS , 450 ,400 );

        if ( !window.joms_js_import_users ) {
            window.joms_js_import_users = function( form ) {
                if ( joms.jQuery(form).find('[name=csv]').val() ) {
                    return true;
                }
                return false;
            };
        }
    }

    this.importGroups = function(){
        cWindowShow('jax.call("community","admin,groups,importGroupsForm");', joms_lang.COM_COMMUNITY_CONFIGURATION_IMPORT_GROUPS , 450 ,400 );

        if ( !window.joms_js_import_users ) {
            window.joms_js_import_users = function( form ) {
                if ( joms.jQuery(form).find('[name=csv]').val() ) {
                    return true;
                }
                return false;
            };
        }
    }

    this.editRule = function( ruleId ){
        cWindowShow( 'jax.call("community","admin,userpoints,ajaxEditRule","' + ruleId + '");' , 'Edit Rule' , 450 , 300 );
        return false;
    }

    this.saveRule = function( ruleId ){
        var values = jax.getFormValues('editRule');
        jax.call('community','admin,userpoints,ajaxSaveRule', ruleId , values);
    }

    this.updateField = function (sourceId, targetId){
        joms.jQuery('#' + targetId).val( jQuery('#' + sourceId).val() );
    }

    this.editEvent = function( eventId ){
        cWindowShow('jax.call("community","admin,events,ajaxEditEvent", ' + eventId + ');', 'Editing Event' , 450 , 350 );

        var $ = joms.jQuery;

        var counter = 0;
        var timer = setInterval(function() {
            if ( $('.joms-js--event-photo-flag').length ) {
                clearInterval( timer );
                initForm();
            } else if ( ++counter >= 60 ) {
                clearInterval( timer );
            }
        }, 500 );

        var initForm = function() {
            $('.joms-js--event-private-flag').on( 'click', function() {
                var unlisted = $('.joms-js--event-unlisted-flag');
                if ( this.checked ) {
                    unlisted.removeAttr('disabled');
                } else {
                    unlisted[0].checked = false;
                    unlisted.attr('disabled', 'disabled');
                }
            });

            $('.joms-js--event-photo-flag').on( 'click', function() {
                var $div = $('.joms-js--event-photo-setting'),
                    $checkbox = $div.find('input');

                if ( this.checked ) {
                    $checkbox.removeAttr('disabled');
                    $div.show();
                } else {
                    $checkbox[0].checked = false;
                    $checkbox.attr('disabled', 'disabled');
                    $div.hide();
                }
            }).triggerHandler('click');

            $('.joms-js--event-video-flag').on( 'click', function() {
                var $div = $('.joms-js--event-video-setting'),
                    $checkbox = $div.find('input');

                if ( this.checked ) {
                    $checkbox.removeAttr('disabled');
                    $div.show();
                } else {
                    $checkbox[0].checked = false;
                    $checkbox.attr('disabled', 'disabled');
                    $div.hide();
                }
            }).triggerHandler('click');
        };
    }

    this.saveEvent = function(){
        joms.jQuery('#editevent').submit();
    }

    this.editEventCategory = function( catId , windowTitle ){
        cWindowShow('jax.call("community","admin,eventcategories,ajaxEditCategory", ' + catId + ');', windowTitle, 450 , 350 );
    }

    this.saveEventCategory  = function(){
        var values  = jax.getFormValues('editEventCategory');
        jax.call('community','admin,eventcategories,ajaxSaveCategory', values);
    }

    this.toggleMultiProfileChild = function( fieldId ){
        var element = '#publish' + fieldId;
        var image   = "images/tick.png";
        var hidden  = '';

        if( joms.jQuery( element ).children('input[@name=fields]').val() )
        {
            image   = "images/publish_x.png";
        }
        else
        {
            hidden  = '<input type="hidden" name="fields[]" value="' + fieldId + '" />';
        }
        var val = '<a href="javascript:void(0);" onclick="azcommunity.toggleMultiProfileChild('+ fieldId + ');"><img src="' + image + '"/></a>' + hidden;

        joms.jQuery( element ).html( val );
    }

    this.registerZencoderAccount    = function(){
        cWindowShow('jax.call("community","admin,zencoder,ajaxShowForm");', '' , 400 ,220 );
        return false;
    }

    this.submitZencoderAccount  = function(){
        var values  = jax.getFormValues('registerZencoderAccount');
        jax.call('community','admin,zencoder,ajaxSubmitForm', values);
    }

    /**
     * Used by Joomla elements such as the 'users' element
     **/
    this.selectUser = function( id , title , object ){
        document.getElementById(object + '_id').value = id;
        document.getElementById(object + '_name').value = title;
        document.getElementById('sbox-window').close();
    }

    this.editVideo = function( videoId ){
        cWindowShow('jax.call("community","admin,videos,ajaxEditVideo", ' + videoId + ');', joms_lang.COM_COMMUNITY_EDITING_VIDEO , 450 , 350 );
    }

    this.saveVideo = function(){
        joms.jQuery('#editvideo').submit();
    }

    this.viewVideo = function(videoId){
        cWindowShow('jax.call("community","admin,videos,ajaxViewVideo", ' + videoId + ');', joms_lang.COM_COMMUNITY_VIEW_VIDEO , 450 , 350 );
    }

    this.toggleStatus = function(userid,status){
        jax.call('community','admin,users,ajaxToggleStatus', userid,status);
    }
    this.showImage = function(id)
    {
        cWindowShow('jax.call("community","admin,photos,ajaxViewPhoto", ' + id + ');', joms_lang.COM_COMMUNITY_VIEW_PHOTO , 450 , 350 );
    }
    this.editPhoto = function(id)
    {
        cWindowShow('jax.call("community","admin,photos,ajaxEditPhoto", ' + id + ');', joms_lang.COM_COMMUNITY_EDITING_PHOTO, 450, 350 );
    }
    this.savePhoto = function(){
        joms.jQuery('#editphoto').submit();
    }
}

var azcommunity = new AzrulCommunity();

if( typeof( Joomla ) != 'object' )
{
    var Joomla  = new Object();
}

// #881 Fixed isis template issue
setTimeout(function() {
    window.jQuery && jQuery('.subhead-collapse').hide();
}, 1 );
