<?php
/**
* @copyright (C) 2013 iJoomla, Inc. - All rights reserved.
* @license GNU General Public License, version 2 (http://www.gnu.org/licenses/gpl-2.0.html)
* @author iJoomla.com <webmaster@ijoomla.com>
* @url https://www.jomsocial.com/license-agreement
* The PHP code portions are distributed under the GPL license. If not otherwise stated, all images, manuals, cascading style sheets, and included JavaScript *are NOT GPL, and are released under the IJOOMLA Proprietary Use License v1.0
* More info at https://www.jomsocial.com/license-agreement
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport('joomla.application.component.controller');

/**
 * Content Component Controller
 *
 * @package		Joomla
 * @subpackage	Content
 * @since 1.5
 */
class CommunityBaseController extends JControllerLegacy
{
	var $_modelInstances = array();
	var $_libraryInstances = array();
	var $_viewInstances = array();
	var $_name;
	private $_icon = 'generic';
	var $my = null;

	public function getName(){
		return $this->_name;
	}

	public function __construct($config = array()){
		if(!empty($config)){
			$this->_name = $config['name'];
		}
		$this->my = CFactory::getUser();
	}

	/**
	 * Deprecated since 1.8.x
	 */
	public function _notify($cmd, $from, $to, $subject, $body, $template='', $params = '')
	{
		//CFactory::load( 'libraries' , 'notification' );
		return CNotificationLibrary::add( $cmd , $from , $to , $subject , $body , $template , $params );
	}

	/**
	 * A guest trying to use registered-only part of the system via ajax. Display
	 * a link to register
	 */
	public function ajaxBlockUnregister()
	{
		$objResponse	= new JAXResponse();
		$uri			= CFactory::getLastURI();
		$uri			= base64_encode($uri);
		$config			= CFactory::getConfig();

		$usersConfig = JComponentHelper::getParams('com_users');

		$fbHtml	= '';

		if ($config->get('fbconnectkey') && $config->get('fbconnectsecret') && !$config->get('usejfbc')) {
            $facebook = new CFacebook();
            $fbHtml = $facebook->getLoginHTML();
        }

        if ($config->get('usejfbc')) {
            if (class_exists('JFBCFactory')) {
               $providers = JFBCFactory::getAllProviders();
               $fbHtml = '';
               foreach($providers as $p){
                    $fbHtml .= $p->loginButton();
               }
            }
        }

		$tmpl = new CTemplate();
		$tmpl->set( 'fbHtml', $fbHtml );
		$tmpl->set( 'useractivation', $usersConfig->get( 'useractivation' ));
		$tmpl->set( 'allowUserRegister', $usersConfig->get('allowUserRegistration'));
		$tmpl->set( 'useractivation', $usersConfig->get( 'useractivation' ));
		$tmpl->set( 'return', $uri );
		$html = $tmpl->fetch( 'ajax.login' );

        $json = array(
            'title'   => '&nbsp;',
            'html'    => $html,
            'noLogin' => true
        );

        die( json_encode($json) );
	}

	/**
	 * Block user access to the  controller method.
	 */
	public function blockUserAccess()
	{
		$document 	= JFactory::getDocument();
		$document->setTitle(JText::_('COM_COMMUNITY_ACCESS_FORBIDDEN'));
		//echo JText::_('COM_COMMUNITY_ACCESS_FORBIDDEN');

		$tmpl = new CTemplate();
		$notice	= JText::_('COM_COMMUNITY_NOT_ALLOWED_TO_VIEW_PAGE');

		$tmpl->set( 'notice' , $notice );
		echo $tmpl->fetch('notice.access');

		return true;

	}

	// Block non-login mebers
	public function blockUnregister( $uri=null )
	{
		$my				= CFactory::getUser();
		$config			= CFactory::getConfig();
		$usersConfig	= JComponentHelper::getParams( 'com_users' );

		if($my->id == 0)
		{
			$config	= CFactory::getConfig();

			if(empty($uri))
			{
				$uri	= CRoute::getURI( false );
			}
			$uri	= base64_encode($uri);
			$tmpl	= new CTemplate();

			$fbHtml	= '';

			if ($config->get('fbconnectkey') && $config->get('fbconnectsecret') && !$config->get('usejfbc')) {
	            $facebook = new CFacebook();
	            $fbHtml = $facebook->getLoginHTML();
        	}

	        if ($config->get('usejfbc')) {
	            if (class_exists('JFBCFactory')) {
	               $providers = JFBCFactory::getAllProviders();
	               $fbHtml = '';
	               foreach($providers as $p){
	               		$fbHtml .= $p->loginButton();
	               }
	            }
	        }

            //hero image
            $heroImage = JURI::root() . 'components/com_community/assets/frontpage-image-default.jpg';
            if (file_exists(COMMUNITY_PATH_ASSETS . 'frontpage-image.jpg')) {
                $heroImage = JURI::root() . 'components/com_community/assets/frontpage-image.jpg';
            } else if (file_exists(COMMUNITY_PATH_ASSETS . 'frontpage-image.png')) {
                $heroImage = JURI::root() . 'components/com_community/assets/frontpage-image.png';
            }

            $themeModel = CFactory::getModel('theme');
            $settings = $themeModel->getSettings();

            $tmpl->set('settings', $settings);
            $tmpl->set('heroImage', $heroImage);
			$tmpl->set( 'fbHtml' , $fbHtml );
			$tmpl->set( 'return' , $uri );
			$tmpl->set( 'allowUserRegister' , $usersConfig->get('allowUserRegistration'));
			$tmpl->set( 'useractivation' , $usersConfig->get( 'useractivation' ));
			$html	= $tmpl->fetch( 'guests.denied' );
			echo $html;
			return true;
		}

		return false;
	}

	/**
	 * Return the view object, which will output the final html. The view object
	 * is a singleton
	 *
	 * @param	string		view name
	 * #param	string		view class prefix, optional
	 * @param	string		document type, html/pdf/etc/
	 * @return	object		the view object
	 */
	public function getView($viewName ='frontpage', $prefix = '', $viewType = '', $config=array())
	{
		return CFactory::getView($viewName, $prefix, $viewType);
	}


	public function loadHelper($name){
		include_once(JPATH_COMPONENT.'/helpers/'.$name.'.php');
	}

	public function getLibrary( $name = '', $prefix = '', $config = array() ){
		if(!isset($this->_libraryInstances[$name]))
		{
			include_once(JPATH_COMPONENT.'/libraries/'.$name.'.php');
			$classname = 'CommunityLib'.$name;
			$this->_libraryInstances[$name] = new $classname;
		}
		return $this->_libraryInstances[$name];
	}

	//debug data
	private function _dump(&$data){

	    echo '<pre>';
		print_r($data);
		echo '</pre>';
		exit;

	}

	/**
	 * Return the model object, responsible for all db manipulation. Singleton
	 *
	 * @param	string		model name
	 * @param	string		any class prefix
	 * @return	object		model object
	 */
	public function getModel( $name = '', $prefix = '', $config = array() )
	{
		return CFactory::getModel($name, $prefix, $config);
	}

	// Our own display function
	public function display($cachable = false, $urlparams = false)
	{
		$document = JFactory::getDocument();

		$viewType	= $document->getType();
 		$viewName	= JInput::get( 'view', $this->getName() );
 		$viewLayout	= JInput::get( 'layout', 'default' );

		$view = $this->getView( $viewName, '', $viewType);

		// Display the view
		if ($cachable) {
			global $option;
			$cache = JFactory::getCache($option, 'view');
			$cache->get($view, 'display');
		} else {
			$view->profile();
		}
	}

	/**
	 * Execute a request
	 */
    public function execute($task)
    {
        $mainframe = JFactory::getApplication();
        $jinput = $mainframe->input;
        $document = JFactory::getDocument();
        $my = CFactory::getUser();
        $pathway = $mainframe->getPathway();

        $menus = $mainframe->getMenu();
        $menuitem = $menus->getActive();

        $userId = $jinput->get->get('userid', '', 'INT');
        $tmpl = $jinput->request->get('tmpl', '', 'STRING');
        $format = $jinput->request->get('format', '', 'STRING');
        $nohtml = $jinput->request->get('no_html', '', 'STRING');

        if ($tmpl != 'component' && $format != 'feed' && $format != 'ical' && $nohtml != 1 && $format != 'raw') {
            // This is to fix MSIE that has incorrect user agent because jquery doesn't detect correctly.
            $ieFix = "<!--[if IE 6]><script type=\"text/javascript\">var jomsIE6 = true;</script><![endif]-->";
            $document->addCustomTag($ieFix);
        }

        // Add custom css for the specific view if needed.
        $config = CFactory::getConfig();
        $viewName = JString::strtolower($jinput->request->get('view', '', 'STRING'));
        jimport('joomla.filesystem.file');

        if ($config->get('enablecustomviewcss')) {
            CTemplate::addStylesheet($viewName);
        }

        $template = JFactory::getApplication()->getTemplate();

        // echo var_dump(CTemplate::addStylesheet('beez2'));

        if($template == 'beez_20' && JVERSION > 2 && JVERSION < 3){
            CTemplate::addStylesheet('beez2');
        }

        $env = CTemplate::getTemplateEnvironment();

        $svgFile = CFactory::getPath('template://assets/icon/joms-icon.svg');

        // non ajax-prefixed tasks that should avoid svg
        $excludeSvgList = array(
            'changeAvatar',
            'multiUpload',
            'uploadvideo',
            'export'
        );

        // exclude svg file from ajax response
        if ($svgFile && substr($task, 0, 4) != 'ajax' && !in_array($task, $excludeSvgList)) {
            include_once $svgFile;
        }

        $html = '<div id="community-wrap" class="jomsocial-wrapper on-' . $env->joomlaTemplate . ' ' . $document->direction . ' c' . ucfirst($viewName) . '">';
        $html .= '<div class="jomsocial">';

        // Build the content HTML
        //CFactory::load( 'helpers' , 'azrul' );

        if (!empty($task) && method_exists($this, $task)) {
            ob_start();
            if (method_exists($this, '_viewEnabled') && !$this->_viewEnabled()) {
                echo (property_exists($this, '_disabledMessage')) ? $this->_disabledMessage : JText::_('COM_COMMUNITY_CONTROLLER_FUNCTION_DISABLED_WARNING');
            } else {
                $this->$task();
            }
            $output = ob_get_contents();
            ob_end_clean();
        } else {
            ob_start();
            $this->display();
            $output = ob_get_contents();
            ob_end_clean();
        }

        // Build toolbar HTML
        ob_start();
        $view = $this->getView(JString::strtolower($jinput->get('view', 'frontpage', 'STRING')));

        // Do not rely on the toolbar to include necessary css or javascripts.
        $view->attachHeaders();

        // Display the site's toolbar.
        $view->showToolbar();


        // Header title will use view->title. If not specified, we will
        // use current page title
        $headerTitle = !empty($view->title) ? $view->title : $document->getTitle();
        $view->showHeader($headerTitle, $this->_icon);

        $header = ob_get_contents();
        ob_end_clean();
        $html .= $header;

        // @rule: Super admin should always be allowed regardless of their block status
        // block member to access profile owner details
        //CFactory::load( 'helpers' , 'owner' );
        //CFactory::load( 'libraries' , 'block' );
        $getBlockStatus = new blockUser();
        $blocked = $getBlockStatus->isUserBlocked($userId, $viewName);
        if ($blocked) {
            if (COwnerHelper::isCommunityAdmin()) {
                $mainframe = JFactory::getApplication();
                $mainframe->enqueueMessage(JText::_('COM_COMMUNITY_YOU_ARE_BLOCKED_BY_USER', 'error'));
            } else {
                $tmpl = new CTemplate();
                $view->showToolbar();
                echo $tmpl->fetch('block.denied');
				return;
				$block = true;
            }
        }

        $html .= $output . '</div></div>';

        //CFactory::load( 'helpers' , 'string' );
        $html = CStringHelper::replaceThumbnails($html);
        $html = CString::str_ireplace(array('{error}', '{warning}', '{info}'), '', $html);

        // Trigger onModuleDisplay()
        //CFactory::load( 'libraries' , 'apps' );
        $appsLib = CAppPlugins::getInstance();
        $appsLib->loadApplications();

        $moduleHTML = $appsLib->triggerEvent('onModuleRender');
        $mods = array();
        if (is_array($moduleHTML)) {
            foreach ($moduleHTML as $modules) {
                if (is_array($modules)) {
                    foreach ($modules as $position => $content) {
                        if (empty($mods[$position]))
                            $mods[$position] = '';

                        $mods[$position] .= $content;
                    }
                }
            }
        }

        foreach ($mods as $position => $module) {
            $html = str_replace('<!-- ' . $position . ' -->', $module, $html);
        }
        echo $html;

        // Fix UI quirks.
        echo PHP_EOL;
        echo '<script>window.joms && joms.fixUI && joms.fixUI();</script>';
        echo PHP_EOL;
    }

	/**
	 * Execute ajax request
	 */
	public function executeAjax($method, $ajaxArg)
	{
		$filter	    =	JFilterInput::getInstance();
		$method	    =	$filter->clean( $method, 'string' );
		$ajaxArg	    =	$filter->clean( $ajaxArg, 'string' );

		if(!empty($method) && method_exists($this, $method))
		{
			$this->$method($ajaxArg);
			//call_user_func('$this->'.$method, $ajaxArg);
		}
		else
		{
			$this->display();
		}
	}

	/**
	 * restrict blocked user to access owner details
	 */
	public function ajaxBlock()
	{
		$objResponse	= new JAXResponse();
		// $uri			= CFactory::getLastURI();
		// $uri			= base64_encode($uri);
		// $config			= CFactory::getConfig();
		$tmpl			= new CTemplate();
		//$tmpl->set( 'uri' , $uri );
		//F$tmpl->set( 'config'	, $config );
		$html			= $tmpl->fetch( 'block.denied' );

		$objResponse->addScriptCall('cWindowAddContent', $html);

		return $objResponse->sendResponse();
	}

	/**
	 * restrict user to block community admin
	 */
	public function ajaxRestrictBlockAdmin()
	{
		$config = CFactory::getConfig();

        $json = array(
            'title'    => $config->get('sitename'),
            'error'    => JText::_('COM_COMMUNITY_CANNOT_BLOCK_COMMUNITY_ADMIN'),
            'btnClose' => JText::_('COM_COMMUNITY_BUTTON_CLOSE_BUTTON')
        );

        die( json_encode( $json ) );
	}

	public function cacheClean($cacheId){
		$cache = CFactory::getFastCache();

		$cache->clean($cacheId);
	}

}
