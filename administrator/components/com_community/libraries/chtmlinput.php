<?php
/**
* @copyright (C) 2013 iJoomla, Inc. - All rights reserved.
* @license GNU General Public License, version 2 (http://www.gnu.org/licenses/gpl-2.0.html)
* @author iJoomla.com <webmaster@ijoomla.com>
* @url https://www.jomsocial.com/license-agreement
* The PHP code portions are distributed under the GPL license. If not otherwise stated, all images, manuals, cascading style sheets, and included JavaScript *are NOT GPL, and are released under the IJOOMLA Proprietary Use License v1.0
* More info at https://www.jomsocial.com/license-agreement
*/
defined('JPATH_PLATFORM') or die;

class CHTMLInput
{
	public static function checkbox($name, $class, $attribs = array(), $selected = null, $id = false)
	{
		$selectedHtml = '';
		$html = '';

		if (is_array($attribs)) {
			$attribs = Joomla\Utilities\ArrayHelper::toString($attribs);
		}

		if($selected) {
			$selectedHtml .= " checked=\"checked\"";
		}

		$html .= "\n<input type='hidden' value='0' name=\"$name\">"; // Self destruct
		$html .= "\n<input type=\"checkbox\" name=\"$name\" class=\"$class\" value=\"1\" $attribs $selectedHtml />";
		$html .= "\n<span class=\"lbl\"></span>";

		return $html;
	}
}