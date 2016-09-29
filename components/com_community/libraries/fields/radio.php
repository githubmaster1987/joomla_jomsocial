<?php
/**
 * @copyright (C) 2013 iJoomla, Inc. - All rights reserved.
 * @license GNU General Public License, version 2 (http://www.gnu.org/licenses/gpl-2.0.html)
 * @author iJoomla.com <webmaster@ijoomla.com>
 * @url https://www.jomsocial.com/license-agreement
 * The PHP code portions are distributed under the GPL license. If not otherwise stated, all images, manuals, cascading style sheets, and included JavaScript *are NOT GPL, and are released under the IJOOMLA Proprietary Use License v1.0
 * More info at https://www.jomsocial.com/license-agreement
 */
// no direct access
defined('_JEXEC') or die('Restricted access');

require_once (COMMUNITY_COM_PATH.'/libraries/fields/profilefield.php');
class CFieldsRadio extends CProfileField
{
    public function getFieldHTML( $field , $required )
    {
        $html				= '';
        $selectedElement	= 0;
        $elementSelected	= 0;
        $elementCnt	        = 0;
        $params				= new CParameter($field->params);

        $readonly = $params->get('readonly') && !COwnerHelper::isCommunityAdmin() ? ' disabled="disabled"' : '';

        for( $i = 0; $i < count( $field->options ); $i++ )
        {
            $option		= $field->options[ $i ];
            $selected	= ( $option == $field->value ) ? ' checked="checked"' : '';

            if( empty( $selected ) )
            {
                $elementSelected++;
            }
            $elementCnt++;
        }


        $cnt = 0;
        $html .= '<div style="display:inline-block" title="' . CStringHelper::escape( JText::_( $field->tips ) ). '">';
        for( $i = 0; $i < count( $field->options ); $i++ )
        {
            $option		= $field->options[ $i ];

            $selected	= ( html_entity_decode($option) == html_entity_decode($field->value) ) ? ' checked="checked"' : '';

            $html 	.= '<label class="lblradio-block">';
            $html	.= '<input type="radio" name="field' . $field->id . '" value="' . $option . '"' . $selected . $readonly . ' style="margin: 2px 5px 0 0" />';
            $html	.= JText::_( $option ) . '</label>';
        }
        $html	.= '</div>';

        return $html;
    }

    public function isValid( $value , $required )
    {
        if( $required && empty($value))
        {
            return false;
        }
        return true;
    }
}
