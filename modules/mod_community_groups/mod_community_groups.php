<?php
    /**
     * @copyright (C) 2013 iJoomla, Inc. - All rights reserved.
     * @license GNU General Public License, version 2 (http://www.gnu.org/licenses/gpl-2.0.html)
     * @author iJoomla.com <webmaster@ijoomla.com>
     * @url https://www.jomsocial.com/license-agreement
     * The PHP code portions are distributed under the GPL license. If not otherwise stated, all images, manuals, cascading style sheets, and included JavaScript *are NOT GPL, and are released under the IJOOMLA Proprietary Use License v1.0
     * More info at https://www.jomsocial.com/license-agreement
     */
    defined('_JEXEC') or die('Restricted access');

    include_once(JPATH_BASE . '/components/com_community/defines.community.php');
    require_once(JPATH_BASE . '/components/com_community/libraries/core.php');

    //add style css
    JFactory::getLanguage()->isRTL() ? CTemplate::addStylesheet('style.rtl') : CTemplate::addStylesheet('style');

    $model = CFactory::getModel('groups');
    $limit = $params->get('limit',5);

    $groupType = $params->get('displaysetting',0);
    if($groupType){
        //1 = my groups
        if(!CFactory::getUser()->id){
            //since this is my group only and if there is no userid provided, it should be empty
            $tmpGroups = array();
        }else{
           // $model->setState('limit', $limit); // limit the results
            $tmpGroups = $model->getGroups(CFactory::getUser()->id);
        }

    }else{
        //0 = show all groups
        $tmpGroups = $model->getAllGroups(null, null, null, null, true, false, false, true);
    }

    $groups = array();
    $data = array();

    $featuredOnly = $params->get('filter_by',0);
    if($featuredOnly){
        //1 = featured only
        $featured = new CFeatured(FEATURED_GROUPS, $limit);
        $featuredGroups = $featured->getItemIds();
    }

    foreach ($tmpGroups as $row) {
        if($featuredOnly && !in_array($row->id, $featuredGroups)){
            //if we only show featured item, and the item does not exists.
            continue;
        }

        $group = JTable::getInstance('Group', 'CTable');
        $group->bind($row);
        $group->description = JHTML::_('string.truncate', $group->description, 30);
        $groups[] = $group;
    }

    $groups = array_slice($groups,0,$limit);

    require(JModuleHelper::getLayoutPath('mod_community_groups', $params->get('layout', 'default')));
