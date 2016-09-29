<?php
/**
 * @Package			DMC Firewall
 * @Copyright		Dean Marshall Consultancy Ltd
 * @license			http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * Email			software@deanmarshall.co.uk
 * web:				http://www.deanmarshall.co.uk/
 * web:				http://www.webdevelopmentconsultancy.com/
 */

defined('_JEXEC') or die('Direct access forbidden!');

jimport('joomla.form.formfield');

class JFormFieldHiddenjs extends JFormField
{
    protected $type = 'Hiddenjs';

    public function getInput()
	{
		$JS =<<<JS
window.addEventListener('DOMContentLoaded', function()
{
	var fwForm = document.getElementById('component-form');
	fwForm.onsubmit = function()
	{
		var sqlInjections = document.getElementById('jform_sqlInjections').value;
		document.getElementById('jform_sqlInjections').value = window.btoa(sqlInjections);
		var hackAttempts = document.getElementById('jform_hackAttempts').value;
		document.getElementById('jform_hackAttempts').value = window.btoa(hackAttempts);
	}
	
	window.onload = function()
	{
		var sqlInjections = document.getElementById('jform_sqlInjections').value;
		document.getElementById('jform_sqlInjections').value = window.atob(sqlInjections);
		var hackAttempts = document.getElementById('jform_hackAttempts').value;
		document.getElementById('jform_hackAttempts').value = window.atob(hackAttempts);
	}
});
JS;
		$CSS =<<<CSS
		#jform_Hidden_JS-lbl { display:none; }
CSS;
		
		JFactory::getDocument()->addScriptDeclaration($JS);
		JFactory::getDocument()->addStyleDeclaration($CSS);
		
		//$html = '';
		//$html .= "<textarea id=\"$elementForm\" rows=\"7\" cols=\"85\" name=\"jform[$elementName]\">$this->value</textarea>";
		//return $html;
		return '';
    }
}