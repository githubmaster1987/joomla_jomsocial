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

    $config = CFactory::getConfig();
    $lang	= JFactory::getLanguage();
    $lang->load( 'com_community.country',JPATH_ROOT);

?>

<div class="joms-page <?php echo (isset($postresult)&& $postresult) ? 'joms-page--search' : ''; ?>">
    <h3 class="joms-page__title"><?php echo JText::_('COM_COMMUNITY_TITLE_CUSTOM_SEARCH'); ?></h3>
    <?php echo $submenu; ?>

    <?php if ($hasMultiprofile && count($multiprofileArr) > 0) { ?>
    <select class="joms-select" onchange="window.location=this.value;">
        <?php foreach ($multiprofileArr as $key => $value) { ?>
        <option value="<?php echo $value['url']; ?>" <?php if ($value['selected']) echo 'selected="selected"'; ?>>
        <?php echo $value['name']; ?>
        </option>
        <?php } ?>
    </select>
    <?php } ?>

<script>

    joms_tmp_pickadateOpts = {
        format   : 'yyyy-mm-dd',
        firstDay : <?php echo $config->get('event_calendar_firstday') === 'Monday' ? 1 : 0 ?>,
        today    : '<?php echo JText::_("COM_COMMUNITY_DATEPICKER_CURRENT", true) ?>',
        'clear'  : '<?php echo JText::_("COM_COMMUNITY_DATEPICKER_CLEAR", true) ?>'
    };

    joms_tmp_pickadateOpts.weekdaysFull = [
        '<?php echo JText::_("COM_COMMUNITY_DATEPICKER_DAY_1", true) ?>',
        '<?php echo JText::_("COM_COMMUNITY_DATEPICKER_DAY_2", true) ?>',
        '<?php echo JText::_("COM_COMMUNITY_DATEPICKER_DAY_3", true) ?>',
        '<?php echo JText::_("COM_COMMUNITY_DATEPICKER_DAY_4", true) ?>',
        '<?php echo JText::_("COM_COMMUNITY_DATEPICKER_DAY_5", true) ?>',
        '<?php echo JText::_("COM_COMMUNITY_DATEPICKER_DAY_6", true) ?>',
        '<?php echo JText::_("COM_COMMUNITY_DATEPICKER_DAY_7", true) ?>'
    ];

    joms_tmp_pickadateOpts.weekdaysShort = [];
    for ( i = 0; i < joms_tmp_pickadateOpts.weekdaysFull.length; i++ )
        joms_tmp_pickadateOpts.weekdaysShort[i] = joms_tmp_pickadateOpts.weekdaysFull[i].substr( 0, 3 );

    joms_tmp_pickadateOpts.monthsFull = [
        '<?php echo JText::_("COM_COMMUNITY_DATEPICKER_MONTH_1", true) ?>',
        '<?php echo JText::_("COM_COMMUNITY_DATEPICKER_MONTH_2", true) ?>',
        '<?php echo JText::_("COM_COMMUNITY_DATEPICKER_MONTH_3", true) ?>',
        '<?php echo JText::_("COM_COMMUNITY_DATEPICKER_MONTH_4", true) ?>',
        '<?php echo JText::_("COM_COMMUNITY_DATEPICKER_MONTH_5", true) ?>',
        '<?php echo JText::_("COM_COMMUNITY_DATEPICKER_MONTH_6", true) ?>',
        '<?php echo JText::_("COM_COMMUNITY_DATEPICKER_MONTH_7", true) ?>',
        '<?php echo JText::_("COM_COMMUNITY_DATEPICKER_MONTH_8", true) ?>',
        '<?php echo JText::_("COM_COMMUNITY_DATEPICKER_MONTH_9", true) ?>',
        '<?php echo JText::_("COM_COMMUNITY_DATEPICKER_MONTH_10", true) ?>',
        '<?php echo JText::_("COM_COMMUNITY_DATEPICKER_MONTH_11", true) ?>',
        '<?php echo JText::_("COM_COMMUNITY_DATEPICKER_MONTH_12", true) ?>'
    ];

    joms_tmp_pickadateOpts.monthsShort = [];
    for ( i = 0; i < joms_tmp_pickadateOpts.monthsFull.length; i++ )
        joms_tmp_pickadateOpts.monthsShort[i] = joms_tmp_pickadateOpts.monthsFull[i].substr( 0, 3 );

</script>

<script>

    jsAdvanceSearch = {
        action: {
            keynum : 0,
            dateFormatDesc : '<?php echo JText::_("COM_COMMUNITY_DATE_FORMAT_DESCRIPTION"); ?>',
            addCriteria: function ( ) {
                var criteria = "";
                var keynum = jsAdvanceSearch.action.keynum;

                criteria +='<div id="criteria'+keynum+'" class="joms-form__item">';
                    criteria +='<span id="selectfield'+keynum+'">';
                        criteria +='<select name="field'+keynum+'" id="field'+keynum+'" onchange="jsAdvanceSearch.action.changeField(\''+keynum+'\');" class="joms-input">';
                            <?php
                            foreach($fields as $label=>$data)
                            {
                                if($data->published && $data->visible)
                                {
                            ?>
                                    criteria +='<optgroup label="<?php echo addslashes( JText::_($label) );?>">';
                                        <?php
                                        foreach($data->fields as $key=>$field)
                                        {
                                            if($field->published && $field->visible && $field->searchable)
                                            {
                                                $selected = "";
                                                if($field->fieldcode == 'username')
                                                {
                                                    $selected = "SELECTED";
                                                }
                                                $html_data = "";
                                                $display = "";
                                                if (in_array($field->type, array('date', 'birthdate')))
                                                {
                                                    $params = new CParameter($field->params);
                                                    $date_format = $params->get('date_format');
                                                    $date_format = str_replace("y", "yy", $date_format);
                                                    $date_format = str_replace("Y", "yyyy", $date_format);
                                                    $date_format = str_replace("m", "mm", $date_format);
                                                    $date_format = str_replace("F", "mmmm", $date_format);
                                                    $date_format = str_replace("M", "mmm", $date_format);
                                                    $date_format = str_replace("n", "m", $date_format);
                                                    $date_format = str_replace("d", "dd", $date_format);
                                                    $date_format = str_replace("D", "ddd", $date_format);
                                                    $date_format = str_replace("j", "d", $date_format);
                                                    $date_format = str_replace("l", "dddd", $date_format);

                                                    $min_range = $params->get('minrange', -10);
                                                    $min_range = $min_range === 'today' ? 0 : $min_range;
                                                    $min_range = CFieldsDate::getRange($min_range);

                                                    $max_range = $params->get('maxrange', 100);
                                                    $max_range = $max_range === 'today' ? 0 : $max_range;
                                                    $max_range = CFieldsDate::getRange($max_range);

                                                    // flip date in case of invalid range.
                                                    if ( isset( $min_range['value'] ) && isset( $max_range['value'] ) ) {
                                                        if ( ((int) $min_range['year'] ) > ((int) $max_range['year'] ) ) {
                                                            $temp = $min_range;
                                                            $min_range = $max_range;
                                                            $max_range = $temp;
                                                        }
                                                    }

                                                    // set minimum range.
                                                    if ( isset( $min_range['value'] ) ) {
                                                        $value = explode('-', $min_range['value']);
                                                        $min_range = ((int) $value[0] ) . '-' . ((int) $value[1] - 1 ) . '-' . ((int) $value[2] );
                                                    }

                                                    // set maximum range.
                                                    if ( isset( $max_range['value'] ) ) {
                                                        $value = explode('-', $max_range['value']);
                                                        $max_range = ((int) $value[0] ) . '-' . ((int) $value[1] - 1 ) . '-' . ((int) $value[2] );
                                                    }

                                                    $html_data .= ' data-dateformat="' . $date_format . '"';
                                                    $html_data .= ' data-minrange="' . $min_range . '"';
                                                    $html_data .= ' data-maxrange="' . $max_range . '"';

                                                    if ( $params->get('display', 0) == 1 || $params->get('display', 0) == 'date' ) {
                                                        $html_data .= ' data-display="1"';
                                                    }
                                                }
                                        ?>
                                                criteria +='<option value="<?php echo addslashes($field->fieldcode); ?>" <?php echo $selected; ?> <?php echo $html_data; ?>><?php echo JText::_(addslashes(JString::trim($field->name)));?></option>';
                                        <?php
                                            }
                                        }
                                        ?>
                                    criteria +='</optgroup>';
                            <?php
                                }
                            }
                            ?>
                        criteria +='</select>';
                    criteria +='</span>';
                    criteria +='<span id="selectcondition'+keynum+'">';
                        criteria +='<select name="condition'+keynum+'" id="condition'+keynum+'" class="joms-input">';
                            criteria +='<option value=""></option>';
                        criteria +='</select>';
                    criteria +='</span>';
                    criteria +='<span id="valueinput'+keynum+'" class="joms-input--small">';
                        criteria +='<input type="text" name="value'+keynum+'" id="value'+keynum+'" class="joms-input"/>';
                    criteria +='</span>';
                    criteria +='<span id="valueinput'+keynum+'_2" class="joms-input--small">';
                    criteria +='</span>';
                    criteria +='<span id="typeinput'+keynum+'" style="display:none;">';
                        criteria +='<input type="hidden" name="fieldType'+keynum+'" id="fieldType'+keynum+'" value="" class="joms-input"/>';
                    criteria +='</span>';
                    criteria +='<span id="removelink'+keynum+'">';
                        criteria +='<a href="javascript:void(0);" onclick="jsAdvanceSearch.action.removeCriteria(\''+keynum+'\');" class="joms-button--neutral joms-button--small">';
                            // criteria +='<?php echo JText::_('COM_COMMUNITY_HIDE_CRITERIA');?>';
                            criteria +='<svg class="joms-icon" viewBox="0 0 16 18"><use xlink:href="<?php echo CRoute::getURI(); ?>#joms-icon-close"></use></svg>';
                        criteria +='</a>';
                    criteria +='</span>';
                criteria +='</div>';

                var comma = '';
                if(joms.jQuery('#key-list').val()!="")
                {
                    var comma = ',';
                }
                joms.jQuery('#key-list').val(joms.jQuery('#key-list').val()+comma+keynum);



                joms.jQuery('#criteriaContainer').append(criteria);
                jsAdvanceSearch.action.changeField(keynum);
                jsAdvanceSearch.action.keynum++;
            },
            removeCriteria: function ( id ) {
                var inputs = [];
                var _id, _id2;
                _id = joms.jQuery('#key-list').val();
                _id2 = _id.split(',');

                joms.jQuery(_id2).each(function() {
                    if ( this != id && this != "") {
                        // re-populate
                        inputs.push(this);
                    }
                });

                joms.jQuery("#criteria"+id).remove();
                joms.jQuery('#key-list').val(inputs.join(','));
            },
            getFieldType: function ( fieldcode ) {
                var type;
                switch(fieldcode)
                {
                    <?php
                    foreach($fields as $label=>$data)
                    {
                        if($data->published && $data->visible)
                        {
                            foreach($data->fields as $key=>$field)
                            {
                                if($field->published && $field->visible)
                                {
                            ?>
                                    case "<?php echo $field->fieldcode; ?>":
                                        type = "<?php echo $field->type; ?>";
                                        break;
                            <?php
                                }
                            }
                        }
                    }
                    ?>
                    default :
                        type = "default";
                }
                return type;
            },
            getListValue: function ( id, fieldcode ) {
                var list;
                switch(fieldcode)
                {
                    <?php
                    foreach($fields as $label=>$data)
                    {
                        if($data->published && $data->visible)
                        {
                            foreach($data->fields as $key=>$field)
                            {
                                if($field->published && $field->visible)
                                {
                                    if(!empty($field->options))
                                    {
                                ?>
                                        case "<?php echo $field->fieldcode; ?>":
                                            <?php if ($field->type == 'checkbox') { ?>

                                                list    = '';
                                                list    += '<ul class="joms-list--inline">';
                                                <?php
                                                foreach($field->options as $data)
                                                {
                                                ?>
                                                    list += '<li class="joms-list__item"><input type="checkbox" name="value'+id+'[]" value="<?php echo addslashes(JString::trim($data)); ?>"> <?php echo addslashes(JText::_(JString::trim($data))); ?></input></li>';
                                                <?php
                                                }
                                                ?>
                                                list    += '';
                                                list    += '</ul>'

                                            <?php } else if ($field->type == 'list') { ?>

                                                list = '<select name="value'+id+'[]" id="value'+id+'" class="joms-input" multiple="multiple">';
                                                <?php foreach($field->options as $data) { ?>
                                                    list += '<option value="<?php echo addslashes(JString::trim($data)); ?>"><?php echo addslashes(JText::_(JString::trim($data))); ?></option>';
                                                <?php } ?>
                                                list +='</select>';

                                            <?php }elseif($field->type == 'country'){
                                                $countryLib = new CFieldsCountry();
                                                $countryList = $countryLib->getCountriesList();
                                            ?>
                                            list = '<select name="value'+id+'" id="value'+id+'" class="joms-input">';
                                            <?php
                                            foreach($countryList as $data=>$country){

                                            ?>
                                            list +='<option value="<?php echo addslashes(JString::trim($country)); ?>"><?php echo addslashes(JString::trim($data)); ?></option>';
                                            <?php } ?>
                                            list +='</select>';
                                            <?php } else { ?>
                                                list = '<select name="value'+id+'" id="value'+id+'" class="joms-input">';
                                                <?php
                                                foreach($field->options as $key=>$data)
                                                {

                                                $dataValue = ($field->fieldcode=="FIELD_PROFILE_ID_SPECIAL") ? $key : $data;

                                                $displayData = $data;
                                                    if($field->type == 'country'){
                                                        //display
                                                        $displayData = str_replace(' ','',$data);
                                                        $displayData = 'COM_COMMUNITY_LANG_NAME_'.strtoupper($displayData);
                                                    }
                                                ?>
                                                    list +='<option value="<?php echo addslashes(JString::trim($dataValue)); ?>"><?php echo ucfirst(strtolower(addslashes(JText::_(JString::trim($displayData))))); ?></option>';
                                                <?php
                                                }
                                                ?>
                                                list +='</select>';

                                            <?php } ?>
                                            break;
                                <?php
                                    }
                                }
                            }
                        }
                    }
                    ?>
                    default :
                        list = '<input type="text" name="value'+id+'" id="value'+id+'" class="joms-input"/>';
                }
                return list;
            },
            changeField: function ( id ) {
                var value, type, condHTML, listValue;
                var cond = [];
                var conditions = new Array();
                conditions['contain']               = "<?php echo addslashes(JString::trim(JText::_('COM_COMMUNITY_CONTAIN'))); ?>";
                conditions['between']               = "<?php echo addslashes(JString::trim(JText::_('COM_COMMUNITY_BETWEEN'))); ?>";
                conditions['equal']                 = "<?php echo addslashes(JString::trim(JText::_('COM_COMMUNITY_EQUAL'))); ?>";
                conditions['notequal']              = "<?php echo addslashes(JString::trim(JText::_('COM_COMMUNITY_NOT_EQUAL'))); ?>";
                conditions['lessthanorequal']       = "<?php echo addslashes(JString::trim(JText::_('COM_COMMUNITY_LESS_THAN_OR_EQUAL'))); ?>";
                conditions['greaterthanorequal']    = "<?php echo addslashes(JString::trim(JText::_('COM_COMMUNITY_GREATER_THAN_OR_EQUAL'))); ?>";

                value   = joms.jQuery('#field'+id).val();
                type    = jsAdvanceSearch.action.getFieldType(value);
                this.changeFieldType(type, id);

                switch(type)
                {
                    case 'date'     :
                        cond        = ['between', 'equal', 'notequal', 'lessthanorequal', 'greaterthanorequal'];
                        listValue   = 0;
                        break;
                    case 'time'     :
                        cond        = ['equal', 'notequal'];
                        listValue   = 0;
                        break;
                    case 'birthdate':
                        cond        = ['between', 'equal', 'lessthanorequal', 'greaterthanorequal'];
                        listValue   = 0;
                        break;
                    case 'checkbox' :
                    case 'radio'    :
                    case 'singleselect' :
                    case 'select'   :
                    case 'list'     :
                    case 'country'  :
                    case 'gender'   :
                        cond      = ['equal', 'notequal'];
                        listValue = this.getListValue(id, value);
                        break;
                    case 'email'    :
                    case 'time'     :
                        cond      = ['equal'];
                        listValue = 0;
                        break;
                    case 'textarea' :
                    case 'text'     :
                    default :
                        if(value == 'useremail')
                        {
                            cond    = ['equal'];
                        }
                        else
                        {
                            cond    = ['contain', 'equal', 'notequal'];
                        }
                        listValue = 0;
                        break;
                }

                condHTML = '<select class="joms-input" name="condition'+id+'" id="condition'+id+'" onchange="jsAdvanceSearch.action.changeCondition('+id+');">';
                joms.jQuery(cond).each(function(){
                    condHTML +='<option value="'+this+'">'+conditions[this]+'</option>';
                });
                condHTML +='</select>';

                joms.jQuery('#selectcondition'+id).html(condHTML);
                jsAdvanceSearch.action.changeCondition(id);
                jsAdvanceSearch.action.calendar(type, id);
                if(listValue!=0){
                    joms.jQuery('#valueinput'+id).html(listValue);
                }
            },
            addAltInputField: function(type, id) {
                var cond = joms.jQuery( '#condition' + id ).val(),
                    inputField;

                if ( cond === 'between' ) {
                    if ( type === 'birthdate' || type === 'date' ) {
                        inputField = '<input type="text" name="value' + id + '_2" id="value' + id + '_2" class="joms-input" value="" title="' + this.dateFormatDesc + '" readonly="true"/>';
                    } else if ( type === 'time' ) {
                        inputField = this.getTimeField( 'value' + id + '_2' );
                    } else {
                        inputField = '<input type="text" name="value' + id + '_2" id="value' + id + '_2" class="joms-input" value=""/>';
                    }
                } else {
                    inputField = '';
                }

                joms.jQuery('#valueinput'+id+'_2').html(inputField);
                if ( cond === 'between' ) {
                    if ( type === 'birthdate' || type === 'date' ) {
                        var opts = joms.jQuery('#field'+ id);
                        var opt = joms.jQuery(opts[0].options[ opts[0].options.selectedIndex ]);
                        var date_format = opt.data('dateformat');
                        var min = opt.data('minrange');
                        var max = opt.data('maxrange');
                        var display = opt.data('display');
                        joms.jQuery( '#value' + id + '_2' ).pickadate( joms.jQuery.extend({}, joms_tmp_pickadateOpts, {
                            selectYears: 200,
                            selectMonths: true
                        }, {
                            format: date_format ? date_format : joms_tmp_pickadateOpts.format,
                            min: min ? min.split('-') : undefined,
                            max: max ? max.split('-') : undefined
                        }) );
                    }
                }
            },
            getTimeField: function( name ) {
                var html = '',
                    label, i;

                // Hours.
                html += '<select class="joms-select" name="' + name + '[]">';
                for ( i = 0; i < 24; i++ ) {
                    label = ( i < 10 ? '0' : '' ) + i;
                    html += '<option value="' + label + '">' + label + '</option>';
                }
                html += '</select> : ';

                // Minutes.
                html += '<select class="joms-select" name="' + name + '[]">';
                for ( i = 0; i < 60; i++ ) {
                    label = ( i < 10 ? '0' : '' ) + i;
                    html += '<option value="' + label + '">' + label + '</option>';
                }
                html += '</select> : ';

                // Seconds.
                html += '<select class="joms-select" name="' + name + '[]">';
                for ( i = 0; i < 60; i++ ) {
                    label = ( i < 10 ? '0' : '' ) + i;
                    html += '<option value="' + label + '">' + label + '</option>';
                }
                html += '</select>';

                return html;
            },
            calendar: function(type, id) {
                var inputField = '';
                if ( type === 'birthdate' || type === 'date' ) {
                    var opts = joms.jQuery('#field'+ id);
                    var opt = joms.jQuery(opts[0].options[ opts[0].options.selectedIndex ]);
                    var display = +opt.data('display');
                    var date_format = opt.data('dateformat');
                }
                if ( type === 'birthdate' || type === 'date' ) {
                    if ( !display ) {
                        inputField += '<a href="javascript:" onclick="jsAdvanceSearch.action.toggleAgeSearch(' + id + ', 1);" title="<?php echo addslashes(JString::trim(JText::_('COM_COMMUNITY_ADVSEARCH_AGE_TITLE'))); ?>"> <?php echo JText::_('COM_COMMUNITY_ADVSEARCH_DATE'); ?> </a>'
                    }
                    inputField += '<input type="text" name="value' + id + '" id="value' + id + '" class="joms-input">';
                } else if ( type === 'time' ) {
                    inputField += this.getTimeField( 'value' + id );
                } else {
                    inputField += '<input type="text" name="value' + id + '" id="value' + id + '" class="joms-input">';
                }
                joms.jQuery('#valueinput'+id).html(inputField);
                if ( type === 'birthdate' || type === 'date' ) {
                    var opts = joms.jQuery('#field'+ id);
                    var opt = joms.jQuery(opts[0].options[ opts[0].options.selectedIndex ]);
                    var date_format = opt.data('dateformat');
                    var min = opt.data('minrange');
                    var max = opt.data('maxrange');
                    joms.jQuery( '#value' + id ).pickadate( joms.jQuery.extend({}, joms_tmp_pickadateOpts, {
                        selectYears: 200,
                        selectMonths: true
                    }, {
                        format: date_format ? date_format : joms_tmp_pickadateOpts.format,
                        min: min ? min.split('-') : undefined,
                        max: max ? max.split('-') : undefined
                    }) );
                }
            },
            changeFieldType: function(type, id) {
                joms.jQuery('#fieldType'+id).val(type);
            },
            changeCondition: function(id) {
                var type = joms.jQuery('#fieldType'+id).val();
                this.addAltInputField(type, id);
            },
            toggleAgeSearch: function(id,mode) {
                var cond = joms.jQuery('#condition'+id).val();
                if(mode == 1){
                    inputField  = '<a onclick="jsAdvanceSearch.action.toggleAgeSearch('+id+',0);" href="javascript:void(0);" title="<?php echo addslashes(JString::trim(JText::_('COM_COMMUNITY_ADVSEARCH_DATE_TITLE'))); ?>"> <?php echo JText::_('COM_COMMUNITY_ADVSEARCH_AGE'); ?> </a><input type="text" name="value'+id+'" id="value'+id+'" class="joms-input" value="" />';
                    joms.jQuery('#valueinput'+id).html(inputField);
                    if(cond == "between"){
                        inputField  = '<input type="text" name="value'+id+'_2" id="value'+id+'_2" class="joms-input" value="" />';
                        joms.jQuery('#valueinput'+id+'_2').html(inputField);
                    }
                } else {
                    jsAdvanceSearch.action.calendar('birthdate',id);
                    jsAdvanceSearch.action.addAltInputField('birthdate',id);
                }
            }
        }
    };

    window.joms_queue || (joms_queue = []);
    joms_queue.push(function() {

        joms.jQuery(document).ready( function() {
            var searchHistory, operator;
        <?php if(!empty($filterJson)){?>
            searchHistory = eval(<?php echo $filterJson; ?>);
        <?php }else{?>
            searchHistory = '';
        <?php }?>

            joms.jQuery('#memberlist-save').click( function(){
                joms.memberlist.showSaveForm('<?php echo $keyList;?>' , searchHistory );
            });

            if(searchHistory != ''){
                var keylist = searchHistory['key-list'].split(',');
                var num;

                joms.jQuery(keylist).each(function(){
                    num = jsAdvanceSearch.action.keynum;
                    jsAdvanceSearch.action.addCriteria();
                    joms.jQuery('#field'+num).val(searchHistory['field'+this]);
                    jsAdvanceSearch.action.changeField(num);
                    joms.jQuery('#condition'+num).val(searchHistory['condition'+this]);
                    jsAdvanceSearch.action.changeCondition(num);

                    if(searchHistory['fieldType'+num] == "birthdate" && searchHistory['datingsearch_agefrom'] && searchHistory['datingsearch_ageto'] )
                    {
                        jsAdvanceSearch.action.toggleAgeSearch(num,1);
                    }else if( searchHistory['condition'+this] == 'between' && searchHistory['fieldType'+num] == "birthdate" && ( joms.jQuery.isNumeric(searchHistory['value'+this]) || joms.jQuery.isNumeric(searchHistory['value'+this+'_2']) )){
                        jsAdvanceSearch.action.toggleAgeSearch(num,1);
                    }

                    if(searchHistory['fieldType'+this] == 'checkbox')
                    {
                        var myVal   = searchHistory['value'+this];
                        if(joms.jQuery.isArray(myVal))
                        {
                            joms.jQuery.each(myVal, function(i, chkVal) {
                                joms.jQuery('input[name="value'+num+'[]"]').each(function() {
                                    if(this.value == chkVal)
                                    {
                                        this.checked = "checked";
                                    }
                                });
                            });

                        }
                    }
                    else if(searchHistory['fieldType'+num] == "time")
                    {
                        joms.jQuery('select[name="value'+num+'[]"]').each(function( i ) {
                            this.value = searchHistory['value'+num][i];
                        });
                    }else
                    {
                        joms.jQuery('#value'+num).val(searchHistory['value'+this]);
                    }

                    if(searchHistory['condition'+this] == 'between'){
                        joms.jQuery('#value'+num+'_2').val(searchHistory['value'+this+'_2']);
                    }
                })

                if(searchHistory.operator == 'and'){
                    operator = 'operator_all';
                }else{
                    operator = 'operator_any';
                }
            }else{
                operator = 'operator_all';
                jsAdvanceSearch.action.addCriteria();
            }
            joms.jQuery('#'+operator).attr("checked", true);
        });

    });

</script>
<!-- advanced search form -->
<form name="jsform-search-advancesearch" class="js-form joms-form--search" action="<?php echo CRoute::getURI(); ?>" method="GET">
    <div id="optionContainer">
        <!-- Criteria Container begin -->
        <div id="criteriaContainer"></div>
        <!-- end: Criteria Container -->
        <div class="joms-form__group">
            <a class="joms-button--neutral joms-button--small" href="javascript:void(0);" onclick="jsAdvanceSearch.action.addCriteria();">
                <?php echo JText::_("COM_COMMUNITY_ADD_CRITERIA"); ?>
            </a>
        </div>
        <div class="joms-form__group">
            <ul class="joms-list--inline">
                <li>
                    <input type="checkbox" name="avatar" id="avatar" value="1" class="joms-checkbox joms-js--"<?php echo ($avatarOnly) ? ' checked="checked"' : ''; ?>>
                    <?php echo JText::_('COM_COMMUNITY_EVENTS_AVATAR_ONLY'); ?>
                </li>
                <li>
                    <input type="radio" name="operator" id="operator_all" value="and" class="joms-input--radio">
                    <?php echo JText::_("COM_COMMUNITY_MATCH_ALL_CRITERIA"); ?>
                </li>
                <li>
                    <input type="radio" name="operator" id="operator_any" value="or" class="joms-input--radio">
                    <?php echo JText::_("COM_COMMUNITY_MATCH_ANY_CRITERIA"); ?>
                </li>
            </ul>
        </div>

        <div class="joms-form__group">
            <input type="hidden" name="profiletype" value="<?php echo $profileType; ?>"/>
            <input type="hidden" name="option" value="com_community" />
            <input type="hidden" name="view" value="search" />
            <input type="hidden" name="task" value="advancesearch" />
            <input type="hidden" name="Itemid" value="<?php echo CRoute::getItemId(); ?>" />
            <input type="submit" class="joms-button--primary joms-right" value="<?php echo JText::_("COM_COMMUNITY_SEARCH_BUTTON_TEMP");?>">

            <?php if ($postresult && COwnerHelper::isCommunityAdmin()) { ?>
            <a href="javascript:" onclick="joms_search_save();"><?php echo JText::_('COM_COMMUNITY_MEMBERLIST_SAVE_SEARCH');?></a>
            <script>
                joms_search_history = <?php echo empty($filterJson) ? "''" : $filterJson ?>;
                joms_search_save = function() {
                    joms.api.searchSave({
                        keys: '<?php echo $keyList ?>',
                        json: joms_search_history,
                        operator: joms.jQuery('[name=operator]:checked').val(),
                        avatar_only: joms.jQuery('[name=avatar]')[0].checked
                    });
                };
            </script>
            <?php } ?>

            <input type="hidden" id="key-list" name="key-list" value="" />
        </div>
    </div>
    <div id="criteriaList" style="clear:both;"></div>
</form>
</div>
