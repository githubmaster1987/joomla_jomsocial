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

jimport( 'joomla.application.component.controller' );

/**
 * JomSocial Profile Controller
 */
class CommunityControllerProfiles extends CommunityController
{
    public function __construct()
    {
        parent::__construct();

        $this->registerTask( 'publish' , 'savePublish' );
        $this->registerTask( 'unpublish' , 'savePublish' );
        $this->registerTask( 'orderup' , 'saveOrder' );
        $this->registerTask( 'orderdown' , 'saveOrder' );
    }

    /**
     * Removes the specific field
     *
     * @access public
     *
     **/
    public function removeField()
    {
        $mainframe  = JFactory::getApplication();
        $jinput     = $mainframe->input;
        $ids    = $jinput->post->get( 'cid', array(), 'array' );
        $count  = count($ids);

        foreach( $ids as $id )
        {
            $table  = JTable::getInstance( 'profiles', 'CommunityTable' );
            $table->load( $id );

            if(!$table->delete( $id ))
            {
                // If there are any error when deleting, we just stop and redirect user with error.
                $message    = JText::_('COM_COMMUNITY_PROFILE_FIELD_DELETE_ERROR');
                $mainframe->redirect( 'index.php?option=com_community&task=profile' , $message, 'error');
                exit;
            }
        }

        $cache = JFactory::getCache('com_content');
        $cache->clean();
        $message    = JText::sprintf('COM_COMMUNITY_PROFILE_FIELDS_REMOVED', $count );
        $mainframe->redirect( 'index.php?option=com_community&view=profiles' , $message, 'message' );
    }

    /**
     * Save the ordering of the entire records.
     *
     * @access public
     *
     **/
    public function saveOrder()
    {
        $mainframe  = JFactory::getApplication();
        $jinput     = $mainframe->input;

        // Determine whether to order it up or down
        $direction  = ( $jinput->getWord( 'task' , '' ) == 'orderup' ) ? -1 : 1;

        // Get the ID in the correct location
        $id         = $jinput->post->get('cid', array(), 'array');
        $db         = JFactory::getDBO();

        if( isset( $id[0] ) )
        {
            $id     = (int) $id[0];

            // Load the JTable Object.
            $table  = JTable::getInstance( 'profiles' , 'CommunityTable' );

            $table->load( $id );

            if( $table->type == 'group' )
            {
                $query  = 'SELECT * FROM ' . $db->quoteName( '#__community_fields' ) . ' '
                        . 'WHERE ' . $db->quoteName( 'ordering' ) . ' > ' . $db->Quote( $table->ordering ) . ' '
                        . 'AND ' . $db->quoteName( 'type' ) . '=' . $db->Quote( 'group' ) . ' '
                        . 'ORDER BY ' . $db->quoteName('ordering') . ' ASC '
                        . 'LIMIT 1';

                $db->setQuery( $query );
                $nextGroup  = $db->loadObject();

                if( $nextGroup || $direction == -1 )
                {
                    if( $direction == -1 )
                    {
                        // Get previous group in list
                        $query  = 'SELECT * FROM ' . $db->quoteName( '#__community_fields' ) . ' '
                                . 'WHERE ' . $db->quoteName( 'ordering' ) . ' < ' . $db->Quote( $table->ordering ) . ' '
                                . 'AND ' . $db->quoteName( 'type' ) . '=' . $db->Quote( 'group' ) . ' '
                                . 'ORDER BY ' . $db->quoteName('ordering') . ' DESC LIMIT 1';

                        $db->setQuery( $query );
                        $previousGroup  = $db->loadObject();

                        $query  = 'SELECT * FROM ' . $db->quoteName( '#__community_fields' ) . ' '
                                . 'WHERE ' . $db->quoteName( 'ordering' ) . ' >= ' . $db->Quote( $table->ordering);

                        if( $nextGroup )
                        {
                            $query  .= ' AND ' . $db->quoteName( 'ordering' ) . ' < ' . $db->Quote( $nextGroup->ordering );
                        }

                        $query .= 'ORDER BY ' . $db->quoteName('ordering') . ' ASC';

                        $db->setQuery( $query );
                        $currentFields  = $db->loadObjectList();

                        // Get previous fields in the group
                        $query      = 'SELECT * FROM ' . $db->quoteName( '#__community_fields' ) . ' '
                                    . 'WHERE ' . $db->quoteName( 'ordering' ) . ' >= ' . $db->Quote( $previousGroup->ordering ) . ' '
                                    . 'AND ' . $db->quoteName( 'ordering') . ' < ' . $db->Quote( $table->ordering ) . ' '
                                    . 'ORDER BY ' . $db->quoteName('ordering') . ' ASC';

                        $db->setQuery( $query );
                        $previousFields = $db->loadObjectList();

                        for( $i = 0; $i < count( $previousFields ); $i++ )
                        {
                            $row    = $previousFields[ $i ];

                            $row->ordering          = $row->ordering + count( $currentFields );

                            $query  = 'UPDATE ' . $db->quoteName( '#__community_fields' ) . ' '
                                    . 'SET ' . $db->quoteName('ordering') . '=' . $db->Quote( $row->ordering ) . ' '
                                    . 'WHERE ' . $db->quoteName( 'id' ) . '=' . $db->Quote( $row->id );
                            $db->setQuery( $query );
                            $db->execute();
                        }

                        for( $i = 0; $i < count( $currentFields ); $i ++ )
                        {
                            $row    = $currentFields[ $i ];

                            $row->ordering  = $row->ordering - count( $previousFields );

                            $query  = 'UPDATE ' . $db->quoteName( '#__community_fields' ) . ' '
                                    . 'SET ' . $db->quoteName('ordering') . '=' . $db->Quote( $row->ordering ) . ' '
                                    . 'WHERE ' . $db->quoteName( 'id' ) . '=' . $db->Quote( $row->id );
                            $db->setQuery( $query );
                            $db->execute();
                        }
                    }
                    else
                    {
                        // Get end
                        $query  = 'SELECT ordering FROM ' . $db->quoteName( '#__community_fields' ) . ' '
                                . 'WHERE ' . $db->quoteName( 'ordering' ) . ' > ' . $db->Quote( $nextGroup->ordering ) . ' '
                                . 'AND ' . $db->quoteName( 'type' ) . '=' . $db->Quote( 'group' ) . ' '
                                . 'ORDER BY ' . $db->quoteName('ordering') . ' ASC '
                                . 'LIMIT 1';
                        $db->setQuery( $query );
                        $nextGroupLimit = $db->loadResult();

                        // Get the next group childs
                        if( $nextGroupLimit )
                        {
                            $query      = 'SELECT * FROM ' . $db->quoteName( '#__community_fields' ) . ' '
                                        . 'WHERE ' . $db->quoteName('ordering') . ' >=' . $nextGroup->ordering . ' '
                                        . 'AND ' . $db->quoteName('ordering') . ' < ' . $nextGroupLimit . ' '
                                        . 'ORDER BY ' . $db->quoteName('ordering') . ' ASC';
                        }
                        else
                        {
                            $query      = 'SELECT * FROM ' . $db->quoteName( '#__community_fields' ) . ' '
                                        . 'WHERE ' . $db->quoteName('ordering') . ' >=' . $nextGroup->ordering . ' '
                                        . 'ORDER BY ' . $db->quoteName('ordering') . ' ASC';
                        }
                        $db->setQuery( $query );
                        $nextGroupChilds    = $db->loadObjectList();

                        $nextGroupsCount    = count( $nextGroupChilds );

                        // Get all childs of the current group field
                        $query      = 'SELECT * FROM ' . $db->quoteName( '#__community_fields' ) . ' '
                                    . 'WHERE ' . $db->quoteName('ordering') . ' >=' . $table->ordering . ' '
                                    . 'AND ' . $db->quoteName('ordering') . ' < ' . $nextGroup->ordering . ' '
                                    . 'ORDER BY '. $db->quoteName('ordering') . ' ASC';

                        $db->setQuery( $query );
                        $currentGroupChilds = $db->loadObjectList();
                        $currentGroupsCount = count( $currentGroupChilds );

                        for( $i = 0; $i < count( $nextGroupChilds ); $i++ )
                        {
                            $row    = $nextGroupChilds[ $i ];

                            //$row->ordering        = $row->ordering - $currentGroupsCount;
                            $row->ordering          = $table->ordering++;
                            $query  = 'UPDATE ' . $db->quoteName( '#__community_fields' ) . ' '
                                    . 'SET ' . $db->quoteName('ordering') . '=' . $db->Quote( $row->ordering ) . ' '
                                    . 'WHERE ' . $db->quoteName( 'id' ) . '=' . $db->Quote( $row->id );

                            $db->setQuery( $query );
                            $db->execute();
                        }

                        for( $i = 0; $i < count( $currentGroupChilds ); $i ++ )
                        {
                            $child  = $currentGroupChilds[ $i ];

                            $child->ordering    = $nextGroupsCount + $child->ordering;

                            $query  = 'UPDATE ' . $db->quoteName( '#__community_fields' ) . ' '
                                    . 'SET ' . $db->quoteName('ordering') . '=' . $db->Quote( $child->ordering ) . ' '
                                    . 'WHERE ' . $db->quoteName( 'id' ) . '=' . $db->Quote( $child->id );
                            $db->setQuery( $query );
                            $db->execute();
                        }
                    }
                }
            }
            else
            {
                $table->move( $direction );
            }

            $cache  = JFactory::getCache( 'com_content');
            $cache->clean();

            $mainframe->redirect( 'index.php?option=com_community&view=profiles' );
        }

    }

    /**
     * AJAX method to save a field
     *
     * @param   int id  Current field id
     * @param   Array data  The fields data
     *
     * @return  JAXResponse object  Azrul's AJAX Response object
     **/
    public function ajaxSaveGroup( $id , $data )
    {
        $user   = JFactory::getUser();

        if ( $user->get('guest'))
        {
            JFactory::getApplication()->enqueueMessage(JText::_('COM_COMMUNITY_ACCESS_FORBIDDEN'), 'error');
            return;
        }

        $response   = new JAXResponse();

        // Load the JTable Object.
        $row    = JTable::getInstance( 'profiles' , 'CommunityTable' );
        $row->load( $id );
        $isValid        = true;
        $data['type']   = 'group';
        $row->bind( $data );

        // Do some validation before blindly saving the profile.
        if( empty( $row->name ) )
        {
            $error      = JText::_('COM_COMMUNITY_PROFILE_NAME_EMPTY_WARN');

            $response->addScriptCall( 'joms.jQuery("#error-notice").html("' . $error . '");');
            $isValid    = false;
        }

        if( $isValid )
        {
            $groupOrdering  = isset($data['group']) ? $data['group'] : '';
            $row->store( $groupOrdering );
            $parent         = '';

            // Get the view
            $view       = $this->getView( 'profiles' , 'html' );

            if($id != 0)
            {
                $name   = '<a href="javascript:void(0);" onclick="azcommunity.editFieldGroup(\'' . $row->id . '\' , \'' . JText::_('COM_COMMUNITY_GROUPS_EDIT') . '\');">' . $row->name . '</a>';
                $type   = '<span id="type' . $row->id . '" onclick="$(\'typeOption\').style.display = \'block\';$(this).style.display = \'none\';">'
                        . JString::ucfirst($row->type)
                        . '</span>';

                $publish        = $view->getPublish( $row , 'published' , 'profiles,ajaxGroupTogglePublish' );
                $required       = $view->getPublish( $row , 'required' , 'profiles,ajaxGroupTogglePublish');
                $visible        = $view->getPublish( $row , 'visible' , 'profiles,ajaxGroupTogglePublish');
                $registration   = $view->getPublish( $row , 'registration' , 'profiles,ajaxGroupTogglePublish');

                // Set the parent id
                $parent     = $row->id;

                // Update the rows in the table at the page.
                //@todo: need to update the title in a way looks like Joomla initialize the tooltip on document ready
                $response->addAssign('name' . $row->id, 'innerHTML' , $name);

                $response->addAssign('type' . $row->id, 'innerHTML', $type);
                $response->addAssign('published' . $row->id, 'innerHTML', $publish);
                $response->addAssign('required' . $row->id, 'innerHTML', $required);
                $response->addAssign('visible' . $row->id, 'innerHTML', $visible);
                $response->addAssign('registration' . $row->id, 'innerHTML', $registration);
                $response->addAssign('min' . $row->id, 'value', $row->min);
                $response->addAssign('max' . $row->id, 'value', $row->max);

            }
            else
            {
                $response->addScriptCall('window.location.href = "' . JURI::base() . 'index.php?option=com_community&view=profiles";');
            }
            $response->addScriptCall('cWindowHide();');
        }

        $response->sendResponse();
    }

    /**
     * AJAX method to save a field
     *
     * @param   int id  Current field id
     * @param   Array data  The fields data
     *
     * @return  JAXResponse object  Azrul's AJAX Response object
     **/
    public function ajaxSaveField( $id , $data )
    {
        $user   = JFactory::getUser();

        if ( $user->get('guest')) {
            JFactory::getApplication()->enqueueMessage(JText::_('COM_COMMUNITY_ACCESS_FORBIDDEN'), 'error');
            return;
        }

        $response   = new JAXResponse();

        // Load the JTable Object.
        $row    = JTable::getInstance( 'profiles' , 'CommunityTable' );
        $row->load( $id );
        $isValid    = true;
        $row->bindAjaxPost( $data );


        // override the option visiable, registration and required for label type.
        if($row->type == 'label')
        {
            //$row->visible         = 0;
            $row->required      = 0;
        }


        // Do some validation before blindly saving the profile.
        if( empty( $row->name ) )
        {
            $error      = JText::_('COM_COMMUNITY_PROFILE_NAME_EMPTY_WARN');

            $response->addScriptCall( 'joms.jQuery("#error-notice").html("' . $error . '");');
            $isValid    = false;
        }

        if( empty( $row->fieldcode ) )
        {
            $error      = JText::_('COM_COMMUNITY_PROFILE_FIELD_CODE_EMPTY_WARN');

            $response->addScriptCall( 'joms.jQuery("#error-notice").html("' . $error . '");');
            $isValid    = false;
        }

        if(
            ( $row->type == 'select'
            || $row->type == 'singleselect'
            || $row->type == 'list'
            || $row->type == 'radio'
            || $row->type == 'checkbox' )
            && empty($row->options)
        )
        {
            $error      = JText::_('COM_COMMUNITY_PROFILE_FIELD_OPTIONS_EMPTY_WARN');

            $response->addScriptCall( 'joms.jQuery("#error-notice").html("' . $error . '");');
            $isValid    = false;
        }

        if($row->required && !$row->registration)
        {
            $error      = JText::_('COM_COMMUNITY_PROFILE_FIELD_REQUIRED_CHECK_WARN');

            $response->addScriptCall( 'joms.jQuery("#error-notice").html("' . $error . '");');
            $isValid    = false;
        }

        if( $isValid )
        {
            $groupOrdering  = isset($data['group']) ? $data['group'] : '';

            /* Now, save optional params items */

            $xmlPath = JPATH_ROOT . '/components/com_community/libraries/fields/'. $row->type.'.xml';


            if( JFile::exists($xmlPath) )
            {
                $postvars = $data;
                $post = array();
                // convert $postvars to normal post
                $pattern    = "'params\[(.*?)\]'s";
                for($i =0; $i< count($postvars); $i++)
                {
                    if(!empty($postvars[$i]) && is_array($postvars[$i])){
                        $key = $postvars[$i][0];

                        // @TODO: support 'usergroup' param type
                        preg_match($pattern, $key, $matches);
                        if($matches){
                            $key = $matches[1];
                            $post[$key] = $postvars[$i][1];
                        }

                    }
                }

                $params = new CParameter('', $xmlPath);
                $params->bind($post);
                $row->params = $params->toString();
            }

            if($row->type == 'gender'){
                $row->options = "COM_COMMUNITY_MALE\nCOM_COMMUNITY_FEMALE";
            }elseif($row->type == 'country'){
                $row->options = 'Afghanistan'."\n".'Albania'."\n".'Algeria'."\n".'American Samoa'."\n".'Andorra'."\n".'Angola'."\n".'Anguilla'."\n".'Antarctica'."\n".'Antigua and Barbuda'."\n".'Argentina'."\n".'Armenia'."\n".'Aruba'."\n".'Australia'."\n".'Austria'."\n".'Azerbaijan'."\n".'Bahamas'."\n".'Bahrain'."\n".'Bangladesh'."\n".'Barbados'."\n".'Belarus'."\n".'Belgium'."\n".'Belize'."\n".'Benin'."\n".'Bermuda'."\n".'Bhutan'."\n".'Bolivia'."\n".'Bosnia and Herzegovina'."\n".'Botswana'."\n".'Bouvet Island'."\n".'Brazil'."\n".'British Indian Ocean Territory'."\n".'Brunei Darussalam'."\n".'Bulgaria'."\n".'Burkina Faso'."\n".'Burundi'."\n".'Cambodia'."\n".'Cameroon'."\n".'Canada'."\n".'Cape Verde'."\n".'Cayman Islands'."\n".'Central African Republic'."\n".'Chad'."\n".'Chile'."\n".'China'."\n".'Christmas Island'."\n".'Cocos (Keeling) Islands'."\n".'Colombia'."\n".'Comoros'."\n".'Congo'."\n".'Cook Islands'."\n".'Costa Rica'."\n".'Cote D\'Ivoire (Ivory Coast)'."\n".'Croatia (Hrvatska)'."\n".'Cuba'."\n".'Cyprus'."\n".'Czechoslovakia (former)'."\n".'Czech Republic'."\n".'Denmark'."\n".'Djibouti'."\n".'Dominica'."\n".'Dominican Republic'."\n".'East Timor'."\n".'Ecuador'."\n".'Egypt'."\n".'El Salvador'."\n".'Equatorial Guinea'."\n".'Eritrea'."\n".'Estonia'."\n".'Ethiopia'."\n".'Falkland Islands (Malvinas)'."\n".'Faroe Islands'."\n".'Fiji'."\n".'Finland'."\n".'France'."\n".'France, Metropolitan'."\n".'French Guiana'."\n".'French Polynesia'."\n".'French Southern Territories'."\n".'Gabon'."\n".'Gambia'."\n".'Georgia'."\n".'Germany'."\n".'Ghana'."\n".'Gibraltar'."\n".'Great Britain (UK)'."\n".'Greece'."\n".'Greenland'."\n".'Grenada'."\n".'Guadeloupe'."\n".'Guam'."\n".'Guatemala'."\n".'Guinea'."\n".'Guinea-Bissau'."\n".'Guyana'."\n".'Haiti'."\n".'Heard and McDonald Islands'."\n".'Honduras'."\n".'Hong Kong'."\n".'Hungary'."\n".'Iceland'."\n".'India'."\n".'Indonesia'."\n".'Iran'."\n".'Iraq'."\n".'Ireland'."\n".'Israel'."\n".'Italy'."\n".'Jamaica'."\n".'Japan'."\n".'Jordan'."\n".'Kazakhstan'."\n".'Kenya'."\n".'Kiribati'."\n".'Korea, North'."\n".'South Korea'."\n".'Kuwait'."\n".'Kyrgyzstan'."\n".'Laos'."\n".'Latvia'."\n".'Lebanon'."\n".'Lesotho'."\n".'Liberia'."\n".'Libya'."\n".'Liechtenstein'."\n".'Lithuania'."\n".'Luxembourg'."\n".'Macau'."\n".'Macedonia'."\n".'Madagascar'."\n".'Malawi'."\n".'Malaysia'."\n".'Maldives'."\n".'Mali'."\n".'Malta'."\n".'Marshall Islands'."\n".'Martinique'."\n".'Mauritania'."\n".'Mauritius'."\n".'Mayotte'."\n".'Mexico'."\n".'Micronesia'."\n".'Moldova'."\n".'Monaco'."\n".'Mongolia'."\n".'Montserrat'."\n".'Morocco'."\n".'Mozambique'."\n".'Myanmar'."\n".'Namibia'."\n".'Nauru'."\n".'Nepal'."\n".'Netherlands'."\n".'Netherlands Antilles'."\n".'Neutral Zone'."\n".'New Caledonia'."\n".'New Zealand'."\n".'Nicaragua'."\n".'Niger'."\n".'Nigeria'."\n".'Niue'."\n".'Norfolk Island'."\n".'Northern Mariana Islands'."\n".'Norway'."\n".'Oman'."\n".'Pakistan'."\n".'Palau'."\n".'Panama'."\n".'Papua New Guinea'."\n".'Paraguay'."\n".'Peru'."\n".'Philippines'."\n".'Pitcairn'."\n".'Poland'."\n".'Portugal'."\n".'Puerto Rico'."\n".'Qatar'."\n".'Reunion'."\n".'Romania'."\n".'Russian Federation'."\n".'Rwanda'."\n".'Saint Kitts and Nevis'."\n".'Saint Lucia'."\n".'Saint Vincent and the Grenadines'."\n".'Samoa'."\n".'San Marino'."\n".'Sao Tome and Principe'."\n".'Saudi Arabia'."\n".'Senegal'."\n".'Seychelles'."\n".'S. Georgia and S. Sandwich Isls.'."\n".'Sierra Leone'."\n".'Singapore'."\n".'Slovak Republic'."\n".'Slovenia'."\n".'Solomon Islands'."\n".'Somalia'."\n".'South Africa'."\n".'Spain'."\n".'Sri Lanka'."\n".'St. Helena'."\n".'St. Pierre and Miquelon'."\n".'Sudan'."\n".'Suriname'."\n".'Svalbard and Jan Mayen Islands'."\n".'Swaziland'."\n".'Sweden'."\n".'Switzerland'."\n".'Syria'."\n".'Taiwan'."\n".'Tajikistan'."\n".'Tanzania'."\n".'Thailand'."\n".'Togo'."\n".'Tokelau'."\n".'Tonga'."\n".'Trinidad and Tobago'."\n".'Tunisia'."\n".'Turkey'."\n".'Turkmenistan'."\n".'Turks and Caicos Islands'."\n".'Tuvalu'."\n".'Uganda'."\n".'Ukraine'."\n".'United Arab Emirates'."\n".'United Kingdom'."\n".'United States'."\n".'Uruguay'."\n".'US Minor Outlying Islands'."\n".'USSR (former)'."\n".'Uzbekistan'."\n".'Vanuatu'."\n".'Vatican City State (Holy Sea)'."\n".'Venezuela'."\n".'Viet Nam'."\n".'Virgin Islands (British)'."\n".'Virgin Islands (U.S.)'."\n".'Wallis and Futuna Islands'."\n".'Western Sahara'."\n".'Yemen'."\n".'Yugoslavia'."\n".'Zaire'."\n".'Zambia'."\n".'Zimbabwe';
            }


            $row->store( $groupOrdering );
            $parent         = '';

            // Get the view
            $view       = $this->getView( 'profiles' , 'html' );

            if($id != 0)
            {
                $name   = '<a href="javascript:void(0);" onclick="azcommunity.editField(\'' . $row->id . '\');">' . $row->name . '</a>';
                $type   = '<span id="type' . $row->id . '" onclick="$(\'typeOption\').style.display = \'block\';$(this).style.display = \'none\';">'
                        . JString::ucfirst($row->type)
                        . '</span>';

                $publish        = $view->getPublish( $row , 'published' , 'profiles,ajaxTogglePublish' );
                if($row->type == 'label')
                {
                    $required       = $view->showPublish( $row , 'required');
                    //$visible      = $view->showPublish( $row , 'visible');
                    $visible        = $view->getPublish( $row , 'visible' , 'profiles,ajaxTogglePublish');
                }
                else
                {
                    $required       = $view->getPublish( $row , 'required' , 'profiles,ajaxTogglePublish');
                    $visible        = $view->getPublish( $row , 'visible' , 'profiles,ajaxTogglePublish');
                }

                $registration   = $view->getPublish( $row , 'registration' , 'profiles,ajaxTogglePublish');

                // Set the parent id
                $parent     = $row->id;
                $response->addScriptCall('location.reload();');
            }
            else
            {
                $response->addScriptCall('location.reload();');
            }
            $response->addScriptCall('cWindowHide();');
        }
        else
        {
            //release the form input back to enabled.
            $response->addScriptCall('joms.jQuery(\'#cWindowContent\').find(\'input, textarea, button\').attr(\'disabled\', false)');
        }

        $response->sendResponse();
    }

    /**
     * AJAX method to toggle publish group and its associated fields status
     *
     * @param   int id  Current field id
     * @param   string field    The field publish type
     *
     * @return  JAXResponse object  Azrul's AJAX Response object
     **/
    public function ajaxGroupTogglePublish( $id , $field )
    {

        $user   = JFactory::getUser();

        $images = array(    0   =>  'publish_x.png',
                            1   =>  'tick.png',
                            2   =>  'publish_y.png');

        // @rule: Disallow guests.
        if ( $user->get('guest'))
        {
            JFactory::getApplication()->enqueueMessage(JText::_('COM_COMMUNITY_ACCESS_FORBIDDEN'), 'error');
            return;
        }

        $this->_registerFieldCheck($id , $field);

        // Get the view
        $view       = $this->getView( 'profiles' , 'html' );
        $response   = new JAXResponse();

        $gRow   = JTable::getInstance( 'profiles' , 'CommunityTable' );
        $gRow->load( $id );

        $model          = $this->getModel( 'profiles' );
        $groupFields    = $model->getGroupFields($gRow->ordering);

        if($field ==='visible'){
            $gRow->$field   = ($gRow->$field + 1) % 3;
        } else {
            $gRow->$field   = ($gRow->$field + 1) % 2;
        }
        //update all the fields
        if(count($groupFields) > 0)
        {
            foreach($groupFields as $item)
            {
                // Load the JTable Object.
                $row    = JTable::getInstance( 'profiles' , 'CommunityTable' );
                $row->load( $item->id );

                $row->$field    = $gRow->$field;
                $row->store();
                $image          = $images[$row->$field];
                $html   = $view->getPublish( $row , $field , 'profiles' . ',ajaxTogglePublish' );
                $response->addAssign( $field . $row->id , 'innerHTML' , $html );

            }
        }


        //now update group
        $gRow->store();
        $image          = $images[$gRow->$field];

        $html   = $view->getPublish( $gRow , $field , 'profiles' . ',ajaxGroupTogglePublish' );
        $response->addAssign( $field . $gRow->id , 'innerHTML' , $html );

        return $response->sendResponse();

    }

    public function ajaxGetFieldParams( $type )
    {
        $html   = $this->_buildFieldParams( $type );

        $response = new JAXResponse();

        $response->addScriptCall( 'azcommunity.insertParams' , $html );
        return $response->sendResponse();

    }

    /**
     * AJAX method to toggle publish status
     *
     * @param   int id  Current field id
     * @param   string field    The field publish type
     *
     * @return  JAXResponse object  Azrul's AJAX Response object
     **/
    public function ajaxTogglePublish( $id , $field, $viewName=false )
    {
        $user   = JFactory::getUser();
        $images = array(    0   =>  'publish_x.png',
                            1   =>  'tick.png',
                            2   =>  'publish_y.png');

        // @rule: Disallow guests.
        if ( $user->get('guest'))
        {
            JFactory::getApplication()->enqueueMessage(JText::_('COM_COMMUNITY_ACCESS_FORBIDDEN'), 'error');
            return;
        }


        $this->_registerFieldCheck($id , $field);

        $response   = new JAXResponse();
        $view       = $this->getView( 'profiles' , 'html' );

        $row        = JTable::getInstance( 'profiles' , 'CommunityTable' );
        $row->load( $id );
        if($field ==='visible'){
            $row->$field    = ($row->$field + 1) % 3;
        } else {
            $row->$field    = ($row->$field + 1) % 2;
        }
        $row->store();
        $image = $images[$row->$field];

        $html   = $view->getPublish( $row , $field , 'profiles' . ',ajaxTogglePublish' );
        $response->addAssign( $field . $id , 'innerHTML' , $html );

        //we need to check group status as well.
        $model  = $this->getModel( 'profiles' );
        $group  = $model->getFieldGroup($row->ordering);

        $gRow   = JTable::getInstance( 'profiles' , 'CommunityTable' );
        $gRow->load( $group->id );

        if( !$gRow->$field && $row->$field)
        {
            //this mean group currently was disabled. and the new status for child is enabled.
            //so we need to enable this group as well.

            $gRow->$field   = $row->$field;
            $gRow->store();
            $image          = $images[$gRow->$field];

            $html   = $view->getPublish( $gRow , $field , 'profiles' . ',ajaxGroupTogglePublish' );
            $response->addAssign( $field . $gRow->id , 'innerHTML' , $html );
        }
        else
        {
            //check if all the fields under this group are disabled.
            $groupFields    = $model->getGroupFields($gRow->ordering);
            $countDisabled  = 0;

            //update all the fields
            if(count($groupFields) > 0)
            {
                foreach($groupFields as $item)
                {
                    if(! $item->$field)
                        $countDisabled++;
                }

                if( count($groupFields) == $countDisabled)
                {

                    $gRow->$field   = 0;
                    $gRow->store();
                    $image          = $images[$gRow->$field];

                    $html   = $view->getPublish( $gRow , $field , 'profiles' . ',ajaxGroupTogglePublish' );
                    $response->addAssign( $field . $gRow->id , 'innerHTML' , $html );
                }
            }
        }

        //return parent::ajaxTogglePublish( $id , $field , 'profiles' );
        return $response->sendResponse();
    }

    public function _registerFieldCheck($id , $field)
    {
        $failed = false;

        if($field == 'registration' || $field == 'required')
        {
            //if current toggle field is registration, we need to check on 'required'
            // if user disabled this registration field.
            $html   = '';
            $row    = JTable::getInstance( 'profiles' , 'CommunityTable' );
            $row->load( $id );

            if(($field == 'registration') && ($row->required && $row->registration))
            {
                // this mean the action is to toggle registration from true to false when required is true.
                // if thats the case, it defeated the rules where required field must
                // be a registration field as well.
                $html   = JText::_('COM_COMMUNITY_PROFILE_ERROR_REGISTRATION_FIELD_DISABLE');
                $failed = true;
            }
            else if(($field == 'required') && (!$row->required && !$row->registration))
            {
                // this mean the action is to toggle required from false to true when registration is false
                // if thats the case, it defeated the rules where required field must
                // be a registration field as well.
                $html   = JText::_('COM_COMMUNITY_PROFILE_ERROR_REQUIRED_FIELD_DISABLE');
                $failed = true;
            }


            if($failed)
            {
                $response   = new JAXResponse();

                $response->addScriptCall('cAdminWindowShow','','Error');
                $response->addAssign('cWindowContent', 'innerHTML', $html);
                $response->addScriptCall("joms.jQuery('#cWindowContent').css('overflow','auto');");
                return $response->sendResponse();
            }
        }
    }

    /**
     * AJAX method to display the form
     *
     * @param   int fieldId The fieldId that we are editing
     * @param   boolean isGroup Determines whether the current field is a group
     *
     * @return  JAXResponse object  Azrul's AJAX Response object
     **/
    public function ajaxEditGroup( $fieldId , $isGroup = false )
    {
        $user   = JFactory::getUser();

        if ( $user->get('guest'))
        {
            JFactory::getApplication()->enqueueMessage(JText::_('COM_COMMUNITY_ACCESS_FORBIDDEN'), 'error');
            return;
        }

        $response   = new JAXResponse();

        $model          = $this->getModel( 'profiles' );
        $fieldGroups    = $model->getGroups();

        // Load the JTable Object.
        $row    = JTable::getInstance( 'profiles' , 'CommunityTable' );
        $row->load( $fieldId );

        $windowTitle    = ($row->id == 0 ) ? JText::_('COM_COMMUNITY_PROFILES_NEW_GROUP') : JText::_('COM_COMMUNITY_GROUPS_EDIT');
        ob_start();
?>
<div style="background-color: #F9F9F9; border: 1px solid #D5D5D5; margin-bottom: 10px; padding: 5px;font-weight: bold;">
    <?php echo JText::_('COM_COMMUNITY_PROFILE_NEW_GROUP');?>
</div>
<div id="error-notice" style="color: red; font-weight:700;"></div>
<div style="clear: both;"></div>
<form action="#" method="post" name="editField" id="editField">
<table cellspacing="0" class="admintable" border="0" width="100%">
    <tbody>
        <tr>
            <td class="key"><span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_COSTUMPROFILE_NAME_TIPS');?>"><?php echo JText::_('COM_COMMUNITY_NAME');?></span></td>
            <td>
                <input type="text" value="<?php echo $row->name;?>" name="name" style="width:250px;" />
            </td>
        </tr>
        <tr>
            <td class="key"><span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_COSTUMPROFILE_PUBLISHED_TIPS');?>"><?php echo JText::_('COM_COMMUNITY_PUBLISHED');?></span></td>
            <td>
                <span><?php echo $this->_buildRadio($row->published, 'published', array( JText::_('COM_COMMUNITY_NO_OPTION'),JText::_('COM_COMMUNITY_YES_OPTION')));?></span>
            </td>
        </tr>
        <tr>
            <td class="key"><span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_COSTUMPROFILE_VISIBLE_TIPS');?>"><?php echo JText::_('COM_COMMUNITY_VISIBLE');?></span></td>
            <td><?php echo $this->_buildRadio($row->visible, 'visible', array( JText::_('COM_COMMUNITY_PROFILEFIELD_PERSONAL_OPTION'), JText::_('COM_COMMUNITY_PROFILEFIELD_ALL_OPTION'), JText::_('COM_COMMUNITY_PROFILEFIELD_ADMINONLY_OPTION')));?></td>
        </tr>
    </tbody>
</table>
</form>
<?php
        $contents   = ob_get_contents();
        ob_end_clean();

        $buttons    = '<input type="button" class="btn btn-small btn-primary pull-right" onclick="javascript:azcommunity.saveFieldGroup(\'' . $row->id . '\');return false;" value="' . JText::_('COM_COMMUNITY_SAVE') . '"/>';
        $buttons    .= '&nbsp;&nbsp;<input type="button" class="btn btn-small pull-left" onclick="javascript:cWindowHide();" value="' . JText::_('COM_COMMUNITY_CANCEL') . '"/>';
        $response->addAssign( 'cWindowContent' , 'innerHTML' , $contents );
        $response->addAssign('cwin_logo', 'innerHTML', $windowTitle );
        $response->addScriptCall( 'cWindowActions' , $buttons );
        return $response->sendResponse();
    }

    /**
     * AJAX method to display the form
     *
     * @param   int fieldId The fieldId that we are editing
     * @param   boolean isGroup Determines whether the current field is a group
     *
     * @return  JAXResponse object  Azrul's AJAX Response object
     **/
    public function ajaxEditField( $fieldId , $isGroup = false )
    {
        $user   = JFactory::getUser();
        //CFactory::load( 'helpers' , 'string' );

        if ( $user->get('guest')) {
            JFactory::getApplication()->enqueueMessage(JText::_('COM_COMMUNITY_ACCESS_FORBIDDEN'), 'error');
            return;
        }

        $response   = new JAXResponse();

        $model          = $this->getModel( 'profiles' );
        $fieldGroups    = $model->getGroups();

        // Load the JTable Object.
        $row    = JTable::getInstance( 'profiles' , 'CommunityTable' );

        $row->load( $fieldId );

        $windowTitle    = ($row->id == 0 ) ? JText::_('COM_COMMUNITY_NEW_FIELD') : JText::_('COM_COMMUNITY_PROFILE_EDIT_FIELD');
        $group          = $model->getFieldGroup( $row->ordering );

        ob_start();
?>
<div class="alert alert-info">
    <?php echo JText::_('COM_COMMUNITY_NEW_CUSTOM_PROFILE_LABEL');?>
</div>
<div id="error-notice" style="color:red"></div>
<div style="clear: both;"></div>
<form action="#" method="post" name="editField" id="editField">
<table cellspacing="0" border="0" width="100%">
    <tbody>
        <tr>
            <td class="key"><span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_PROFILE_NAME_TIPS');?> "><?php echo JText::_('COM_COMMUNITY_NAME');?></span> <span class="required-sign">*</span></td>
            <td>
                <input type="text" value="<?php echo CStringHelper::escape( $row->name );?>" name="name" />
            </td>
        </tr>
        <tr>
            <td class="key"><span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_PROFILE_TYPE_TIPS');?> "><?php echo JText::_('COM_COMMUNITY_TYPE');?></span></td>
            <td><?php echo $this->_buildTypes($row->type);?></td>
        </tr>
        <tr style="<?php echo ($row->type != 'group') ? 'display: table-row;' : 'display: none;'; ?>" class="fieldGroups">
            <td class="key"><span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_PROFILE_GROUPS_TIPS');?> "><?php echo JText::_('COM_COMMUNITY_GROUPS');?></span></td>
            <td colspan="4">
                <select name="group">
            <?php
                for( $i = 0; $i < count( $fieldGroups ); $i++ )
                {
                    $selected   = (isset($group->id) && $group->id == $fieldGroups[$i]->id ) ? ' selected="selected"' : '';
            ?>
                <option value="<?php echo $fieldGroups[$i]->ordering;?>"<?php echo $selected;?>><?php echo $fieldGroups[$i]->name; ?></option>
            <?php
                }
            ?>
                </select>
            </td>
        </tr>
        <tr>
            <td class="key"><span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_PROFILE_FIELD_TIPS');?> "><?php echo JText::_('COM_COMMUNITY_FIELD_CODE');?> </span><span class="required-sign">*</span></td>
            <td><input type="text" value="<?php echo CStringHelper::escape( $row->fieldcode ); ?>" name="fieldcode" maxlength="255" /></td>
        </tr>

        <tr>
            <td valign="top" class="key"><span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_PROFILE_REGISTRATION_TIPS');?> "><?php echo JText::_('COM_COMMUNITY_REGISTRATION');?></span></td>
            <td colspan="4">
                <?php echo $this->_buildRadio($row->registration, 'registration', array( JText::_('COM_COMMUNITY_NO_OPTION'),JText::_('COM_COMMUNITY_YES_OPTION')));?>
                <?php // echo CHTMLInput::checkbox('registration' ,'ace-switch ace-switch-5', null , $row->registration ); ?>
            </td>
        </tr>
        <tr>
            <td class="key"><span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_PROFILE_VISIBLE_TIPS');?> "><?php echo JText::_('COM_COMMUNITY_VISIBLE');?></span></td>
            <td><?php echo $this->_buildRadio($row->visible, 'visible', array( JText::_('COM_COMMUNITY_PROFILEFIELD_PERSONAL_OPTION'), JText::_('COM_COMMUNITY_PROFILEFIELD_ALL_OPTION'), JText::_('COM_COMMUNITY_PROFILEFIELD_ADMINONLY_OPTION')));?></td>
        </tr>
        <tr>
            <td class="key"><span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_PROFILE_PUBLISHED_TIPS');?> "><?php echo JText::_('COM_COMMUNITY_PUBLISHED');?></span></td>
            <td>
                <span><?php echo $this->_buildRadio($row->published, 'published', array( JText::_('COM_COMMUNITY_NO_OPTION'),JText::_('COM_COMMUNITY_YES_OPTION')));?></span>
            </td>
        </tr>
        <tr>
            <td class="key" width="200" ><span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_PROFILE_REQUIRED_TIPS');?> "><?php echo JText::_('COM_COMMUNITY_REQUIRED');?></span></td>
            <td><?php echo $this->_buildRadio($row->required, 'required', array( JText::_('COM_COMMUNITY_NO_OPTION'),JText::_('COM_COMMUNITY_YES_OPTION')));?></td>
        </tr>

        <?php echo $this->_buildOptions($row, $row->id, $row->type);?>
    </tbody>
</table>
<!-- Start custom params -->
<div id="fieldParams" class="fieldParams">
        <?php echo $this->_buildFieldParams( $row->type , $row->params); ?>
</div>
<!-- End custom params -->
</form>
<?php
        $contents   = ob_get_contents();
        ob_end_clean();
        $buttons    = '<input type="button" class="btn btn-small btn-primary pull-right" onclick="javascript:azcommunity.saveField(\'' . $row->id . '\');return false;" value="' . JText::_('COM_COMMUNITY_SAVE') . '"/>';
        $buttons    .= '&nbsp;&nbsp;<input type="button" class="btn btn-small pull-left" onclick="javascript:cWindowHide();" value="' . JText::_('COM_COMMUNITY_CANCEL') . '"/>';
        $response->addAssign( 'cWindowContent' , 'innerHTML' , $contents );
        $response->addAssign( 'cwin_logo' , 'innerHTML' , $windowTitle );
        $response->addScriptCall('jQuery(".js-tooltip, .hasTooltip").tooltip({html: true,placement:\'right\'});');
        $response->addScriptCall('if (window.MooTools) (function($) { $$(".js-tooltip, .hasTooltip").each(function (e) {e.hide = null;});})(MooTools);');
        $response->addScriptCall( 'cWindowActions' , $buttons );
        return $response->sendResponse();
    }

    /**
     * Method to build field types data
     *
     * @access  private
     * @param   string  Selected type
     *
     * @return  string  HTML output
     **/
    public function _buildTypes( $selectedType )
    {
        $model  = $this->getModel( 'profiles' );
        $html   = '';
        $types  = $model->getProfileTypes();

        $html   .= '<select name="type" onchange="azcommunity.changeType(this.value);" id="type">';

        foreach( $types as $type => $value)
        {
            $selected   = ( trim($type) == $selectedType ) ? ' selected="true"' : '';
            $html       .= '<option value="' . $type . '"' . $selected . '>' . $value . '</option>';
        }
        $html   .= '</select>';
        return $html;
    }

    /**
     * Method to build Radio fields
     *
     * @access  private
     * @param   string
     *
     * @return  string  HTML output
     **/
    public function _buildRadio($status, $fieldname, $values)
    {
        $html   = '';
        if(isset($values[$status])){
            foreach ($values as $index=>$val){
                $check  = ($index==$status)? ' checked="checked" ':'';
                $html   .= '<input type="radio" name="' . $fieldname . '" value="'.$index.'"'.$check.' /><span class="lbl">&nbsp;' . $val.'</span>&nbsp;&nbsp;';
            }
        }
        $html .='';
        return $html;
    }

    /**
     * Read custom params from XML file and render them
     **/
    public function _buildFieldParams( $type , $params = '' ) {
        $xmlPath = JPATH_ROOT . '/components/com_community/libraries/fields/'. $type.'.xml';

        $html = '';

        if( JFile::exists($xmlPath) )
        {
            $params = new CParameter($params, $xmlPath);
            $html   = $params->render_customform();

        }

        return $html;
    }

    public function _buildOptions(& $row, $id, $type)
    {
        if( $row->type == 'select' || $row->type == 'singleselect' || $row->type == 'radio' || $row->type == 'list' || $row->type == 'checkbox')
        {
            $html   = '<tr style="display: table-row;" class="fieldOptions">';
        }
        else
        {
            $html   = '<tr style="display: none;" class="fieldOptions">';
        }


        $html   .= '    <td class="key" valign="top">
                            <span class="js-tooltip" title="'.JText::_('COM_COMMUNITY_PROFILE_OPTIONS_TIPS').'">Option</span><br />
                        </td>
                        <td colspan="4">';
        $html   .= '<textarea rows="4" cols="50" name="options">' . $row->options . '</textarea><br />'
                 . '<span>Separate each options with a new line</span></td></tr>';

        return $html;
    }
}
