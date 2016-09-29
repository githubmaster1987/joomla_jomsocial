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
class CFieldsText extends CProfileField
{
    public function getFieldHTML( $field , $required )
    {
        $params = new CParameter($field->params);
        $readonly = $params->get('readonly') && !COwnerHelper::isCommunityAdmin() ? ' readonly=""' : '';
        $style = $this->getStyle() ? ' style="' .$this->getStyle() . '"' : '';
        $required = ($field->required == 1) ? ' data-required="true"' : '';

        // If maximum is not set, we define it to a default
        $field->max = empty( $field->max ) ? 200 : $field->max;

        $html = '<input type="text" value="' . $field->value . '" id="field' . $field->id . '" name="field' . $field->id . '" maxlength="' . $field->max . '" class="joms-input" '. $readonly . $required . $style .' />';

        return $html;
    }

    public function isValid( $value , $required )
    {
        if( $required && empty($value))
        {
            return false;
        }
        //validate string length
        if(!$this->validLength($value) && $required){

            return false;
        }
        return true;
    }
}
