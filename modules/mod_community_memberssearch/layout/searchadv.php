<?php
/**
 * @copyright (C) 2015 iJoomla, Inc. - All rights reserved.
 * @license GNU General Public License, version 2 (http://www.gnu.org/licenses/gpl-2.0.html)
 * @author iJoomla.com <webmaster@ijoomla.com>
 * @url https://www.jomsocial.com/license-agreement
 * The PHP code portions are distributed under the GPL license. If not otherwise stated, all images, manuals, cascading style sheets, and included JavaScript *are NOT GPL, and are released under the IJOOMLA Proprietary Use License v1.0
 * More info at https://www.jomsocial.com/license-agreement
 */
defined('_JEXEC') or die('Unauthorized Access');

$uniqid = '_' . uniqid();

?>

<style>
    .joms-form__item {
        margin-bottom: 14px;
        border:1px solid rgba(0,0,0,0.04);
        padding: 14px;
        position: relative;
    }

</style>

<div class="joms-module">

<?php
    //@since 4.1 remove multiprofile support for module, code stays here for future reference
    if (false && $hasMultiprofile) {
        ?>
        <select class="joms-select" onchange="window.location=this.value;">
            <?php foreach ($multiprofileArr as $key => $value) { ?>
                <option value="<?php echo $value['url']; ?>" <?php if ($value['selected']) {
                    echo 'selected="selected"';
                } ?>>
                    <?php echo $value['name']; ?>
                </option>
            <?php } ?>
        </select>
    <?php } ?>

<script>

    joms_tmp_pickadateOpts = {
        format: 'yyyy-mm-dd',
        firstDay: <?php echo $config->get('event_calendar_firstday') === 'Monday' ? 1 : 0 ?>,
        today: '<?php echo JText::_("COM_COMMUNITY_DATEPICKER_CURRENT", true) ?>',
        'clear': '<?php echo JText::_("COM_COMMUNITY_DATEPICKER_CLEAR", true) ?>'
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
    for (i = 0; i < joms_tmp_pickadateOpts.weekdaysFull.length; i++)
        joms_tmp_pickadateOpts.weekdaysShort[i] = joms_tmp_pickadateOpts.weekdaysFull[i].substr(0, 3);

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
    for (i = 0; i < joms_tmp_pickadateOpts.monthsFull.length; i++)
        joms_tmp_pickadateOpts.monthsShort[i] = joms_tmp_pickadateOpts.monthsFull[i].substr(0, 3);

</script>

<script>

modMemberSearch<?php echo $uniqid ?> = {
    action: {
        keynum: 0,
        dateFormatDesc: '<?php echo JText::_("COM_COMMUNITY_DATE_FORMAT_DESCRIPTION"); ?>',
        addCriteria: function () {
            var criteria = "";
            var keynum = modMemberSearch<?php echo $uniqid ?>.action.keynum;

            criteria += '<div id="modmembersearch_criteria<?php echo $uniqid ?>' + keynum + '" class="joms-form__item">';
            criteria += '<span id="selectfield<?php echo $uniqid ?>' + keynum + '" class="joms-select--wrapper">';
            criteria += '<select name="field' + keynum + '" id="field<?php echo $uniqid ?>' + keynum + '" onchange="modMemberSearch<?php echo $uniqid ?>.action.changeField(\'' + keynum + '\');" class="joms-select">';
            <?php
            foreach($fields as $label=>$data)
            {
                if($data->published && $data->visible)
                {
            ?>
            criteria += '<optgroup label="<?php echo addslashes( JText::_($label) );?>">';
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
            ?>
            criteria += '<option value="<?php echo addslashes($field->fieldcode); ?>" <?php echo $selected; ?>><?php echo JText::_(addslashes(JString::trim($field->name)));?></option>';
            <?php
                }
            }
            ?>
            criteria += '</optgroup>';
            <?php
                }
            }
            ?>
            criteria += '</select>';
            criteria += '</span>';
            criteria += '<span id="modmembersearch_selectcondition<?php echo $uniqid ?>' + keynum + '" class="joms-select--wrapper">';
            criteria += '<select name="condition' + keynum + '" id="condition<?php echo $uniqid ?>' + keynum + '" class="joms-select">';
            criteria += '<option value=""></option>';
            criteria += '</select>';
            criteria += '</span>';
            criteria += '<span id="modmembersearch_valueinput<?php echo $uniqid ?>' + keynum + '">';
            criteria += '<input type="text" name="value' + keynum + '" id="value<?php echo $uniqid ?>' + keynum + '" class="joms-input"/>';
            criteria += '</span>';
            criteria += '<span id="modmembersearch_valueinput<?php echo $uniqid ?>' + keynum + '_2">';
            criteria += '</span>';
            criteria += '<span id="typeinput<?php echo $uniqid ?>' + keynum + '" style="display:none;">';
            criteria += '<input type="hidden" name="fieldType' + keynum + '" id="modmembersearch_fieldType<?php echo $uniqid ?>' + keynum + '" value="" class="joms-input"/>';
            criteria += '</span>';
            criteria += '<span id="removelink<?php echo $uniqid ?>' + keynum + '" class="removelinkbtn">';
            criteria += '<a href="javascript:void(0);" onclick="modMemberSearch<?php echo $uniqid ?>.action.removeCriteria(\'' + keynum + '\');" class="joms-button--neutral joms-button--smallest">';
            criteria += '<?php echo JText::_('COM_COMMUNITY_REMOVE');?></a>';
            criteria += '</span>';
            criteria += '</div>';

            var comma = '';
            if (joms.jQuery('#modmembersearch_key-list<?php echo $uniqid ?>').val() != "") {
                var comma = ',';
            }
            joms.jQuery('#modmembersearch_key-list<?php echo $uniqid ?>').val(joms.jQuery('#modmembersearch_key-list<?php echo $uniqid ?>').val() + comma + keynum);


            joms.jQuery('#modMemberSearch<?php echo $uniqid ?>CriteriaContainer').append(criteria);
            modMemberSearch<?php echo $uniqid ?>.action.changeField(keynum);
            modMemberSearch<?php echo $uniqid ?>.action.keynum++;
        },
        removeCriteria: function (id) {
            var inputs = [];
            var _id, _id2;
            _id = joms.jQuery('#modmembersearch_key-list<?php echo $uniqid ?>').val();
            _id2 = _id.split(',');

            joms.jQuery(_id2).each(function () {
                if (this != id && this != "") {
                    // re-populate
                    inputs.push(this);
                }
            });

            joms.jQuery("#modmembersearch_criteria<?php echo $uniqid ?>" + id).remove();
            joms.jQuery('#modmembersearch_key-list<?php echo $uniqid ?>').val(inputs.join(','));
        },
        getFieldType: function (fieldcode) {
            var type;
            switch (fieldcode) {
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
        getListValue: function (id, fieldcode) {
            var list;
            switch (fieldcode) {
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

                    list = '';
                    list += '<ul class="joms-list--inline">';
                <?php
                foreach($field->options as $data)
                {
                ?>
                    list += '<li class="joms-list__item"><input type="checkbox" name="value' + id + '[]" value="<?php echo addslashes(JString::trim($data)); ?>"> <?php echo addslashes(JText::_(JString::trim($data))); ?></input></li>';
                <?php
                }
                ?>
                    list += '';
                    list += '</ul>'

                <?php } else {
                if ($field->type == 'list') { ?>

                    list = '<span class="joms-select--wrapper"><select name="value' + id + '[]" id="value<?php echo $uniqid ?>' + id + '" class="joms-select" multiple="multiple">';
                <?php foreach($field->options as $data) { ?>
                    list += '<option value="<?php echo addslashes(JString::trim($data)); ?>"><?php echo addslashes(JText::_(JString::trim($data))); ?></option>';
                <?php } ?>
                    list += '</select></span>';

                <?php } elseif($field->type == 'country'){
                    $countryLib = new CFieldsCountry();
                    $countryList = $countryLib->getCountriesList();
                ?>
                    list = '<span class="joms-select--wrapper"><select name="value'+id+'" id="value'+id+'" class="joms-select">';
                <?php
                foreach($countryList as $data=>$country){

                ?>
                    list +='<option value="<?php echo addslashes(JString::trim($country)); ?>"><?php echo addslashes(JString::trim($data)); ?></option>';
                <?php } ?>
                    list +='</select>';
                <?php }else{ ?>
                    list = '<span class="joms-select--wrapper"><select name="value' + id + '" id="value<?php echo $uniqid ?>' + id + '" class="joms-select"></span>';
                <?php
                foreach($field->options as $key=>$data)
                {
                $dataValue = ($field->fieldcode=="FIELD_PROFILE_ID_SPECIAL") ? $key : $data;
                ?>
                    list += '<option value="<?php echo addslashes(JString::trim($dataValue)); ?>"><?php echo ucfirst(strtolower(addslashes(JText::_(JString::trim($data))))); ?></option>';
                <?php
                }
                ?>
                    list += '</span></select>';

                <?php }} ?>
                    break;
                <?php
                    }
                }
            }
        }
    }
    ?>
                default :
                    list = '<input type="text" name="value' + id + '" id="value<?php echo $uniqid ?>' + id + '" class="joms-input"/>';
            }
            return list;
        },
        changeField: function (id) {
            var value, type, condHTML, listValue;
            var cond = [];
            var conditions = new Array();
            conditions['contain'] = "<?php echo addslashes(JString::trim(JText::_('COM_COMMUNITY_CONTAIN'))); ?>";
            conditions['between'] = "<?php echo addslashes(JString::trim(JText::_('COM_COMMUNITY_BETWEEN'))); ?>";
            conditions['equal'] = "<?php echo addslashes(JString::trim(JText::_('COM_COMMUNITY_EQUAL'))); ?>";
            conditions['notequal'] = "<?php echo addslashes(JString::trim(JText::_('COM_COMMUNITY_NOT_EQUAL'))); ?>";
            conditions['lessthanorequal'] = "<?php echo addslashes(JString::trim(JText::_('COM_COMMUNITY_LESS_THAN_OR_EQUAL'))); ?>";
            conditions['greaterthanorequal'] = "<?php echo addslashes(JString::trim(JText::_('COM_COMMUNITY_GREATER_THAN_OR_EQUAL'))); ?>";

            value = joms.jQuery('#field<?php echo $uniqid ?>' + id).val();
            type = modMemberSearch<?php echo $uniqid ?>.action.getFieldType(value);
            this.changeFieldType(type, id);

            switch (type) {
                case 'date'     :
                    cond = ['between', 'equal', 'notequal', 'lessthanorequal', 'greaterthanorequal'];
                    listValue = 0;
                    break;
                case 'time'     :
                    cond = ['equal', 'notequal'];
                    listValue = 0;
                    break;
                case 'birthdate':
                    cond = ['between', 'equal', 'lessthanorequal', 'greaterthanorequal'];
                    listValue = 0;
                    break;
                case 'checkbox' :
                case 'radio'    :
                case 'singleselect' :
                case 'select'   :
                case 'list'     :
                case 'country'  :
                case 'gender'   :
                    cond = ['equal', 'notequal'];
                    listValue = this.getListValue(id, value);
                    break;
                case 'email'    :
                case 'time'     :
                    cond = ['equal'];
                    listValue = 0;
                    break;
                case 'textarea' :
                case 'text'     :
                default :
                    if (value == 'useremail') {
                        cond = ['equal'];
                    }
                    else {
                        cond = ['contain', 'equal', 'notequal'];
                    }
                    listValue = 0;
                    break;
            }

            condHTML = '<select class="joms-select" name="condition' + id + '" id="condition<?php echo $uniqid ?>' + id + '" onchange="modMemberSearch<?php echo $uniqid ?>.action.changeCondition(' + id + ');">';
            joms.jQuery(cond).each(function () {
                condHTML += '<option value="' + this + '">' + conditions[this] + '</option>';
            });
            condHTML += '</select>';

            joms.jQuery('#modmembersearch_selectcondition<?php echo $uniqid ?>' + id).html(condHTML);
            modMemberSearch<?php echo $uniqid ?>.action.changeCondition(id);
            modMemberSearch<?php echo $uniqid ?>.action.calendar(type, id);
            if (listValue != 0) {
                joms.jQuery('#modmembersearch_valueinput<?php echo $uniqid ?>' + id).html(listValue);
            }
        },
        addAltInputField: function (type, id) {
            var cond = joms.jQuery('#condition<?php echo $uniqid ?>' + id).val(),
                inputField;

            if (cond === 'between') {
                if (type === 'birthdate' || type === 'date') {
                    inputField = '<input type="text" name="value' + id + '_2" id="value<?php echo $uniqid ?>' + id + '_2" class="joms-input" value="" title="' + this.dateFormatDesc + '" readonly="true"/>';
                } else if (type === 'time') {
                    inputField = this.getTimeField('value' + id + '_2');
                } else {
                    inputField = '<input type="text" name="value' + id + '_2" id="value<?php echo $uniqid ?>' + id + '_2" class="joms-input" value=""/>';
                }
            } else {
                inputField = '';
            }

            joms.jQuery('#modmembersearch_valueinput<?php echo $uniqid ?>' + id + '_2').html(inputField);
            if (cond === 'between') {
                if (type === 'birthdate' || type === 'date') {
                    joms.jQuery('#value<?php echo $uniqid ?>' + id + '_2').pickadate(joms.jQuery.extend({}, joms_tmp_pickadateOpts, {
                        selectYears: 200,
                        selectMonths: true
                    }));
                }
            }
        },
        getTimeField: function (name) {
            var html = '',
                label, i;

            // Hours.
            html += '<select class="joms-select" name="' + name + '[]">';
            for (i = 0; i < 24; i++) {
                label = ( i < 10 ? '0' : '' ) + i;
                html += '<option value="' + label + '">' + label + '</option>';
            }
            html += '</select> : ';

            // Minutes.
            html += '<select class="joms-select" name="' + name + '[]">';
            for (i = 0; i < 60; i++) {
                label = ( i < 10 ? '0' : '' ) + i;
                html += '<option value="' + label + '">' + label + '</option>';
            }
            html += '</select> : ';

            // Seconds.
            html += '<select class="joms-select" name="' + name + '[]">';
            for (i = 0; i < 60; i++) {
                label = ( i < 10 ? '0' : '' ) + i;
                html += '<option value="' + label + '">' + label + '</option>';
            }
            html += '</select>';

            return html;
        },
        calendar: function (type, id) {
            var inputField = '';

            if (type === 'birthdate' || type === 'date') {
                inputField += '<a href="javascript:" onclick="modMemberSearch<?php echo $uniqid ?>.action.toggleAgeSearch(' + id + ', 1);" title="<?php echo addslashes(JString::trim(JText::_('COM_COMMUNITY_ADVSEARCH_AGE_TITLE'))); ?>"> <?php echo JText::_('COM_COMMUNITY_ADVSEARCH_DATE'); ?> </a>'
                inputField += '<input type="text" name="value' + id + '" id="value<?php echo $uniqid ?>' + id + '" class="joms-input">';
            } else if (type === 'time') {
                inputField += this.getTimeField('value' + id);
            } else {
                inputField += '<input type="text" name="value' + id + '" id="value<?php echo $uniqid ?>' + id + '" class="joms-input">';
            }
            joms.jQuery('#modmembersearch_valueinput<?php echo $uniqid ?>' + id).html(inputField);
            if (type === 'birthdate' || type === 'date') {
                joms.jQuery('#value<?php echo $uniqid ?>' + id).pickadate(joms.jQuery.extend({}, joms_tmp_pickadateOpts, {
                    selectYears: 200,
                    selectMonths: true
                }));
            }
        },
        changeFieldType: function (type, id) {
            joms.jQuery('#modmembersearch_fieldType<?php echo $uniqid ?>' + id).val(type);
        },
        changeCondition: function (id) {
            var type = joms.jQuery('#modmembersearch_fieldType<?php echo $uniqid ?>' + id).val();
            this.addAltInputField(type, id);
        },
        toggleAgeSearch: function (id, mode) {
            var cond = joms.jQuery('#condition<?php echo $uniqid ?>' + id).val();
            if (mode == 1) {
                inputField = '<a onclick="modMemberSearch<?php echo $uniqid ?>.action.toggleAgeSearch(' + id + ',0);" href="javascript:void(0);" title="<?php echo addslashes(JString::trim(JText::_('COM_COMMUNITY_ADVSEARCH_DATE_TITLE'))); ?>"> <?php echo JText::_('COM_COMMUNITY_ADVSEARCH_AGE'); ?></a><input type="text" name="value' + id + '" id="value<?php echo $uniqid ?>' + id + '" class="joms-input" value="" />';
                joms.jQuery('#modmembersearch_valueinput<?php echo $uniqid ?>' + id).html(inputField);
                if (cond == "between") {
                    inputField = '<input type="text" name="value' + id + '_2" id="value<?php echo $uniqid ?>' + id + '_2" class="joms-input" value="" />';
                    joms.jQuery('#modmembersearch_valueinput<?php echo $uniqid ?>' + id + '_2').html(inputField);
                }
            } else {
                modMemberSearch<?php echo $uniqid ?>.action.calendar('birthdate', id);
                modMemberSearch<?php echo $uniqid ?>.action.addAltInputField('birthdate', id);
            }
        }
    }
};

window.joms_queue || (joms_queue = []);
joms_queue.push(function () {

    joms.jQuery(document).ready(function () {
        var searchHistory, operator;
        <?php if(!empty($filterJson)){?>
        searchHistory = eval(<?php echo $filterJson; ?>);
        <?php }else{?>
        searchHistory = '';
        <?php }?>

        joms.jQuery('#memberlist-save').click(function () {
            joms.memberlist.showSaveForm('<?php echo $keyList;?>', searchHistory);
        });

        if (searchHistory != '') {
            var keylist = searchHistory['key-list'].split(',');
            var num;

            joms.jQuery(keylist).each(function () {
                num = modMemberSearch<?php echo $uniqid ?>.action.keynum;
                modMemberSearch<?php echo $uniqid ?>.action.addCriteria();
                joms.jQuery('#field<?php echo $uniqid ?>' + num).val(searchHistory['field' + this]);
                modMemberSearch<?php echo $uniqid ?>.action.changeField(num);
                joms.jQuery('#condition<?php echo $uniqid ?>' + num).val(searchHistory['condition' + this]);
                modMemberSearch<?php echo $uniqid ?>.action.changeCondition(num);

                if (searchHistory['fieldType' + num] == "birthdate" && searchHistory['datingsearch_agefrom'] && searchHistory['datingsearch_ageto']) {
                    modMemberSearch<?php echo $uniqid ?>.action.toggleAgeSearch(num, 1);
                } else if (searchHistory['condition' + this] == 'between' && searchHistory['fieldType' + num] == "birthdate" && ( joms.jQuery.isNumeric(searchHistory['value' + this]) || joms.jQuery.isNumeric(searchHistory['value' + this + '_2']) )) {
                    modMemberSearch<?php echo $uniqid ?>.action.toggleAgeSearch(num, 1);
                }

                if (searchHistory['fieldType' + this] == 'checkbox') {
                    var myVal = searchHistory['value' + this];
                    if (joms.jQuery.isArray(myVal)) {
                        joms.jQuery.each(myVal, function (i, chkVal) {
                            joms.jQuery('input[name="value' + num + '[]"]').each(function () {
                                if (this.value == chkVal) {
                                    this.checked = "checked";
                                }
                            });
                        });

                    }
                }
                else if (searchHistory['fieldType' + num] == "time") {
                    joms.jQuery('select[name="value' + num + '[]"]').each(function (i) {
                        this.value = searchHistory['value' + num][i];
                    });
                } else {
                    joms.jQuery('#value<?php echo $uniqid ?>' + num).val(searchHistory['value' + this]);
                }

                if (searchHistory['condition' + this] == 'between') {
                    joms.jQuery('#value<?php echo $uniqid ?>' + num + '_2').val(searchHistory['value' + this + '_2']);
                }
            })

            if (searchHistory.operator == 'and') {
                operator = 'modmembersearch_operator_all';
            } else {
                operator = 'modmembersearch_operator_any';
            }
        } else {
            operator = 'modmembersearch_operator_all';
            modMemberSearch<?php echo $uniqid ?>.action.addCriteria();
        }
        joms.jQuery('#' + operator).attr("checked", true);
    });

});

</script>
<!-- advanced search form -->
<form name="jsform-search-advancesearch" class="js-form" action="<?php echo CRoute::_('index.php?option=com_community&view=search&task=advancesearch'); ?>"
      method="GET">
    <div id="modMemberSearch<?php echo $uniqid ?>OptionContainer">
        <!-- Criteria Container begin -->
        <div id="modMemberSearch<?php echo $uniqid ?>CriteriaContainer"></div>
        <!-- end: Criteria Container -->
        <div class="joms-form__group">
            <a class="joms-button--neutral joms-button--small" href="javascript:void(0);"
               onclick="modMemberSearch<?php echo $uniqid ?>.action.addCriteria();">
                <?php echo JText::_("COM_COMMUNITY_ADD_CRITERIA"); ?>
            </a>
        </div>
        <div class="joms-form__group">
            <ul class="joms-list">
                <li>
                    <input type="checkbox" name="avatar" id="avatar" value="1"
                           class="joms-checkbox joms-js--"<?php echo ($avatarOnly) ? ' checked="checked"' : ''; ?>>
                    <?php echo JText::_('COM_COMMUNITY_EVENTS_AVATAR_ONLY'); ?>
                </li>
                <li>
                    <input type="radio" name="operator" id="modmembersearch_operator_all" value="and" class="joms-input--radio">
                    <?php echo JText::_("COM_COMMUNITY_MATCH_ALL_CRITERIA"); ?>
                </li>
                <li>
                    <input type="radio" name="operator" id="modmembersearch_operator_any" value="or" class="joms-input--radio">
                    <?php echo JText::_("COM_COMMUNITY_MATCH_ANY_CRITERIA"); ?>
                </li>
            </ul>
        </div>

        <div class="joms-form__group">
            <input type="hidden" name="profiletype" value="<?php echo $profileType; ?>"/>
            <input type="hidden" name="option" value="com_community"/>
            <input type="hidden" name="view" value="search"/>
            <input type="hidden" name="task" value="advancesearch"/>
            <input type="hidden" name="Itemid" value="<?php echo CRoute::getItemId(); ?>"/>
            <input type="submit" class="joms-button--primary"
                   value="<?php echo JText::_("COM_COMMUNITY_SEARCH_BUTTON_TEMP"); ?>">

            <?php if (isset($postresult) && $postresult && COwnerHelper::isCommunityAdmin()) { ?>
                <a href="javascript:"
                   onclick="joms_search_save();"><?php echo JText::_('COM_COMMUNITY_MEMBERLIST_SAVE_SEARCH'); ?></a>
                <script>
                    joms_search_history = <?php echo empty($filterJson) ? "''" : $filterJson ?>;
                    joms_search_save = function () {
                        joms.api.searchSave({
                            keys: '<?php echo $keyList ?>',
                            json: joms_search_history,
                            operator: joms.jQuery('#modMemberSearch<?php echo $uniqid ?>OptionContainer').find('[name=operator]:checked').val(),
                            avatar_only: joms.jQuery('#modMemberSearch<?php echo $uniqid ?>OptionContainer').find('[name=avatar]')[0].checked
                        });
                    };
                </script>
            <?php } ?>

            <input type="hidden" id="modmembersearch_key-list<?php echo $uniqid ?>" name="key-list" value=""/>
        </div>
    </div>
    <div id="criteriaList" class="clearfix"></div>
</form>


</div>
