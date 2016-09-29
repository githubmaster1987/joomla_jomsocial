<?php
/**
* @copyright (C) 2013 iJoomla, Inc. - All rights reserved.
* @license GNU General Public License, version 2 (http://www.gnu.org/licenses/gpl-2.0.html)
* @author iJoomla.com <webmaster@ijoomla.com>
* @url https://www.jomsocial.com/license-agreement
* The PHP code portions are distributed under the GPL license. If not otherwise stated, all images, manuals, cascading style sheets, and included JavaScript *are NOT GPL, and are released under the IJOOMLA Proprietary Use License v1.0
* More info at https://www.jomsocial.com/license-agreement
*/
defined( '_JEXEC' ) or die( 'Restricted access' );


/** Define some constants that can be used by the system **/
if( !defined( 'AZRUL_SYSTEM_PATH' ) )
{
	// Get the real system path.
	$system	= rtrim(  dirname( __FILE__ ) , '/' );

	define( 'AZRUL_SYSTEM_PATH' , $system );
}


$helper	= AZRUL_SYSTEM_PATH . '/pc_includes/helper.php';

// Test if file exists before trying to include and generate errors on the entire site.
if( file_exists( $helper ) )
{
	include_once( $helper );
}
else
{
	// If file doesn't exists, just quit it now.
	return;
}

if( !defined( 'AZRUL_SYSTEM_LIVE' ) )
{
	if(basename(dirname(dirname(__FILE__))) == 'plugins'){
	//Joomla 1.5 and above
        define( 'AZRUL_SYSTEM_LIVE' , JURI::root(true)  . '/plugins/system' );
    } else {
        define( 'AZRUL_SYSTEM_LIVE' , JURI::root(true)  . '/plugins/system/jomsocial.system' );
	}
}

if( !defined( 'AZRUL_BASE_LIVE' ) )
{
	define( 'AZRUL_BASE_LIVE' , rtrim( JURI::root() , '/' ) );
}

/**
 * Register the respective events
 **/

$mainframe = JFactory::getApplication();
$mainframe->registerEvent( 'onAfterRoute' , 'azrulSysBot' );
$mainframe->registerEvent( 'onAfterRender', 'azrulOnAfterRender' );
$mainframe->registerEvent( 'onAfterInitialise', 'azrulOnAfterInitialise' );

// Include the template file as Jom Comment and My Blog needs this.
include_once( AZRUL_SYSTEM_PATH .'/pc_includes/template.php');

/**
 * Display required javascript codes for AJAX function calls
 **/
function azrulSysBot()
{
	static	$added	= false;
    $jinput = JFactory::getApplication()->input;
	if( !$added )
	{
		$format		= $jinput->getWord( 'format' , 'html' );

		if( $format == 'pdf' )
		{
			return;
		}

		// Include ajax file
		include_once( AZRUL_SYSTEM_PATH . '/pc_includes/ajax.php' );

		$jax	= new JAX( AZRUL_SYSTEM_LIVE . '/pc_includes' );
		$jax->setReqURI( AZRUL_BASE_LIVE . '/index.php' );
		$jax->process();

		$noHTML	= $jinput->getInt( 'no_html' , 0 );

		if( !$noHTML && $format == 'html' )
		{
			$document = JFactory::getDocument();
			if ($document->getType() == 'html') {
				$document->addCustomTag( $jax->getScript() );
			}
		}
		$added	= true;
	}
}

/**
 * We need to refresh the jax token.
 */
function azrulOnAfterRender()
{
	// We only need to do this replacement if cache is enabled
	// otherwise, just return. Faster, saves memory
	$app = JFactory::getApplication();
	//$config = JFactory::getConfig();
	//if( !$config->get('caching') )

	if( !JFactory::getConfig()->get('caching') )
	{
		return;
	}

	if ($app->getName() != 'site') {
		return true;
	}

	$session	= JFactory::getSession();
	$token		= $session->getFormToken();

	$buffer = JResponse::getBody();
	$tokenStr = 'var jax_token_var=\'' . $token . '\';';

	$regex =	'#var jax_token_var=\'([A-Za-z0-9-_]+)\';#m';
	$buffer	= preg_replace($regex, $tokenStr, $buffer);
	azrulCheckBuffer($buffer);

	JResponse::setBody($buffer);
	return true;
}

/**
 * We need to refresh the jax token in case System Cache plugin is enable
 */

function azrulOnAfterInitialise(){
    $jinput = JFactory::getApplication()->input;

	// If this is ajax call, no need to process this
	$task = $jinput->getWord( 'task' , '' );
	if($task == 'azrul_ajax'){
		return true;
	}

	//Fix the registration issue when the System Cache plugin is enable
	$app = JFactory::getApplication();
	if ($app->getName() != 'site') {
		return true;
	}


	//We only do the replacement for guest users
	$user	= JFactory::getUser();
	if ($user->get('guest') && $_SERVER['REQUEST_METHOD'] == 'GET') {
		//$config = JFactory::getConfig();
		$cache_plg = JPluginHelper::getPlugin('system','cache');
		if(!is_object($cache_plg)){
			//cache plugin is disable, no need futher processing
			return;
		}
		jimport('joomla.html.parameter');
		$params = new JRegistry($cache_plg->params);
		$options = array(
			'cachebase' 	=> JPATH_BASE.'/cache',
			'defaultgroup' 	=> 'page',
			'lifetime' 		=> $params->get('cachetime', 15) * 60,
			'browsercache'	=> false,
			'caching'		=> true,
			'language'		=> JFactory::getConfig()->get('config.language', 'en-GB') //$config->getValue('config.language', 'en-GB')
		);
		jimport('joomla.cache.cache');
		$cache = JCache::getInstance('page', $options);

		$data  = $cache->get();
		//Only replace if the page is cached before
		if ($data !== false)
		{
			// the following code searches for a token in the cached page and replaces it with the
			// proper token.

			$session	= JFactory::getSession();
			$token		= $session->getFormToken(false);

			$search = '#<input type="hidden" name="[0-9a-f]{32}" value="1" />#';
			$replacement = '<input type="hidden" name="'.$token.'" value="1" />';
			$data = preg_replace( $search, $replacement, $data );

			$tokenStr = 'var jax_token_var=\'' . $token . '\';';

			$regex =	'#var jax_token_var=\'([A-Za-z0-9-_]+)\';#m';
			$data	= preg_replace($regex, $tokenStr, $data);

			JResponse::setBody($data);

			echo JResponse::toString(JFactory::getConfig()->get('gzip'));

			$app->close();
		}
	}
	return true;
}


function azrulCheckBuffer($buffer) {
    if ($buffer === null) {
        switch (preg_last_error()) {
        case PREG_BACKTRACK_LIMIT_ERROR:
            $message = "PHP regular expression limit reached (pcre.backtrack_limit)";
            break;
        case PREG_RECURSION_LIMIT_ERROR:
            $message = "PHP regular expression limit reached (pcre.recursion_limit)";
            break;
        case PREG_BAD_UTF8_ERROR:
            $message = "Bad UTF8 passed to PCRE function";
            break;
        default:
            $message = "Unknown PCRE error calling PCRE function";
        }

		throw new Exception($message);
    }
}