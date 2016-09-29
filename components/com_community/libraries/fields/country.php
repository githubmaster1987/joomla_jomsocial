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

class CFieldsCountry extends CProfileField
{
    /**
     * Method to format the specified value for text type
     **/
    public function getFieldData( $field )
    {
        $lang = JFactory::getLanguage();
        $locale = $lang->getLocale();
        $lang->load( 'com_community.country');

        $value = $field['value'];
        if( empty( $value ) )
            return JText::_($value);

        return JText::_($value);
    }

    public function getCountriesList(){
        $lang = JFactory::getLanguage();
        $locale = $lang->getLocale();
        $countryCode = $locale[2];
        $countryLangExtension = "";

        $lang->load( 'com_community.country');

        if (!defined('COUNTRY_LANG_AVAILABLE')) {
            define('COUNTRY_LANG_AVAILABLE', 1);
        }

        $countryListLanguage =   explode(',', trim(COUNTRY_LIST_LANGUAGE) );
        if(in_array($countryCode,$countryListLanguage)==COUNTRY_LANG_AVAILABLE){
            $countryLangExtension = "_".$countryCode;
        }
        jimport( 'joomla.filesystem.file' );
        $file   = JPATH_ROOT .'/components/com_community/libraries/fields/countries'.$countryLangExtension.'.xml';

        if( !JFile::exists( $file ) )
        {
            //default country list file
            $file = JPATH_ROOT .'/components/com_community/libraries/fields/countries.xml';
        }

        $contents   = file_get_contents( $file );
        $parser     = new SimpleXMLElement($file,NULL,true);
        $document   = $parser->document;
        $countries      = $parser->countries;

        // build an array with TRANSLATED country names as keys...

        if(in_array('de_DE', $locale) || in_array('es_ES',$locale)){
            //special case to sort german language
            foreach($countries->country as $country){
                $unwanted_array = array(    'Š'=>'S', 'š'=>'s', 'Ž'=>'Z', 'ž'=>'z', 'À'=>'A', 'Á'=>'A', 'Â'=>'A', 'Ã'=>'A', 'Ä'=>'A', 'Å'=>'A', 'Æ'=>'A', 'Ç'=>'C', 'È'=>'E', 'É'=>'E',
                    'Ê'=>'E', 'Ë'=>'E', 'Ì'=>'I', 'Í'=>'I', 'Î'=>'I', 'Ï'=>'I', 'Ñ'=>'N', 'Ò'=>'O', 'Ó'=>'O', 'Ô'=>'O', 'Õ'=>'O', 'Ö'=>'O', 'Ø'=>'O', 'Ù'=>'U',
                    'Ú'=>'U', 'Û'=>'U', 'Ü'=>'U', 'Ý'=>'Y', 'Þ'=>'B', 'ß'=>'Ss', 'à'=>'a', 'á'=>'a', 'â'=>'a', 'ã'=>'a', 'ä'=>'a', 'å'=>'a', 'æ'=>'a', 'ç'=>'c',
                    'è'=>'e', 'é'=>'e', 'ê'=>'e', 'ë'=>'e', 'ì'=>'i', 'í'=>'i', 'î'=>'i', 'ï'=>'i', 'ð'=>'o', 'ñ'=>'n', 'ò'=>'o', 'ó'=>'o', 'ô'=>'o', 'õ'=>'o',
                    'ö'=>'o', 'ø'=>'o', 'ù'=>'u', 'ú'=>'u', 'û'=>'u', 'ý'=>'y', 'þ'=>'b', 'ÿ'=>'y' );
                $countriesSorted[strtr( trim(JText::_($country->name)), $unwanted_array )] = $country->name;
            }
            ksort($countriesSorted);

            //after sorting, we need to bring back the umlaut character
            $tmpArr = array();
            foreach($countriesSorted as $key=>$country){
                $tmpArr[JText::_($country)] = trim($country);
            }

            $countriesSorted = $tmpArr;

        }else{
            foreach($countries->country as $country){
                $countriesSorted[JText::_($country->name)] = $country->name;
            }
            uasort($countriesSorted, 'CStringHelper::compareAscii');
        }

        return $countriesSorted;
    }

    public function getFieldHTML( $field , $required )
    {
        // If maximum is not set, we define it to a default
        $field->max = empty( $field->max ) ? 200 : $field->max;

        $required   = ($field->required == 1) ? ' data-required="true"' : '';

        // @since 2.4 detect language and call current language country list
        if (!defined('COUNTRY_LANG_AVAILABLE')) {
            define('COUNTRY_LANG_AVAILABLE', 1);
        }

        $lang = JFactory::getLanguage();
        $locale = $lang->getLocale();
        $countryCode = $locale[2];
        $countryLangExtension = "";

        $lang->load( 'com_community.country');

        $countryListLanguage =   explode(',', trim(COUNTRY_LIST_LANGUAGE) );
        if(in_array($countryCode,$countryListLanguage)==COUNTRY_LANG_AVAILABLE){
            $countryLangExtension = "_".$countryCode;
        }
        jimport( 'joomla.filesystem.file' );
        $file   = JPATH_ROOT .'/components/com_community/libraries/fields/countries'.$countryLangExtension.'.xml';

        if( !JFile::exists( $file ) )
        {
            //default country list file
            $file = JPATH_ROOT .'/components/com_community/libraries/fields/countries.xml';
        }

        $contents   = file_get_contents( $file );
        $parser     = new SimpleXMLElement($file,NULL,true);
        $document   = $parser->document;
        $countries      = $parser->countries;

        $tooltips       = !empty( $field->tips ) ? ' title="' .  CStringHelper::escape( JText::_( $field->tips ) ) . '"' : '';
        ob_start();

        if(in_array('de_DE', $locale) || in_array('es_ES',$locale)){
            //special case to sort german language
            foreach($countries->country as $country){
                $unwanted_array = array(    'Š'=>'S', 'š'=>'s', 'Ž'=>'Z', 'ž'=>'z', 'À'=>'A', 'Á'=>'A', 'Â'=>'A', 'Ã'=>'A', 'Ä'=>'A', 'Å'=>'A', 'Æ'=>'A', 'Ç'=>'C', 'È'=>'E', 'É'=>'E',
                    'Ê'=>'E', 'Ë'=>'E', 'Ì'=>'I', 'Í'=>'I', 'Î'=>'I', 'Ï'=>'I', 'Ñ'=>'N', 'Ò'=>'O', 'Ó'=>'O', 'Ô'=>'O', 'Õ'=>'O', 'Ö'=>'O', 'Ø'=>'O', 'Ù'=>'U',
                    'Ú'=>'U', 'Û'=>'U', 'Ü'=>'U', 'Ý'=>'Y', 'Þ'=>'B', 'ß'=>'Ss', 'à'=>'a', 'á'=>'a', 'â'=>'a', 'ã'=>'a', 'ä'=>'a', 'å'=>'a', 'æ'=>'a', 'ç'=>'c',
                    'è'=>'e', 'é'=>'e', 'ê'=>'e', 'ë'=>'e', 'ì'=>'i', 'í'=>'i', 'î'=>'i', 'ï'=>'i', 'ð'=>'o', 'ñ'=>'n', 'ò'=>'o', 'ó'=>'o', 'ô'=>'o', 'õ'=>'o',
                    'ö'=>'o', 'ø'=>'o', 'ù'=>'u', 'ú'=>'u', 'û'=>'u', 'ý'=>'y', 'þ'=>'b', 'ÿ'=>'y' );
                $countriesSorted[strtr( trim(JText::_($country->name)), $unwanted_array )] = $country->name;
            }

            ksort($countriesSorted);

            //after sorting, we need to bring back the umlaut character
            $tmpArr = array();
            foreach($countriesSorted as $key=>$country){
                $tmpArr[JText::_($country)] = trim($country);
            }

            $countriesSorted = $tmpArr;
        }else{
            foreach($countries->country as $country){
                $countriesSorted[JText::_($country->name)] = $country->name;
            }
            uasort($countriesSorted, 'CStringHelper::compareAscii');
        }

        ?>

        <select id="field<?php echo $field->id;?>" name="field<?php echo $field->id;?>" class="joms-select" <?php echo $required;?> >
            <option value=""<?php echo empty($field->value) ? ' selected="selected"' : '';?>><?php echo JText::_('COM_COMMUNITY_SELECT_A_COUNTRY');?></option>
        <?php
        foreach($countriesSorted as $countryName=>$countryCode)
        {
        ?>
            <option value="<?php echo $countryCode;?>"<?php echo ($field->value == $countryCode) ? ' selected="selected"' : '';?>><?php echo $countryName;?></option>
        <?php
        }
        ?>
        </select>

<?php
        $html   = ob_get_contents();
        ob_end_clean();

        return $html;
    }

    public function isValid( $value , $required )
    {
        if( empty($value) && $required ){
            return false;
        }
        return true;
    }

}
