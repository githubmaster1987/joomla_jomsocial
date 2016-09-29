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

class CWindow
{
	/**
	 * Load messaging javascript header
	 */
	static public function load()
	{
		static $loaded = false;

		if ( ! $loaded)
		{
			$config	= CFactory::getConfig();

			require_once JPATH_ROOT.'/components/com_community/libraries/core.php';

			// $js = 'assets/window-1.0.min.js';
			// CFactory::attach($js, 'js');


			CTemplate::addStyleSheet('style');

			// $css = 'assets/window.css';
			// CFactory::attach($css, 'css');
		}
	}
}