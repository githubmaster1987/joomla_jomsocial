<?php
/**
 * @Package			DMC Firewall
 * @Copyright		Dean Marshall Consultancy Ltd
 * @license			http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * Email			software@deanmarshall.co.uk
 * web:				http://www.deanmarshall.co.uk/
 * web:				http://www.webdevelopmentconsultancy.com/
 */

define('_JEXEC', 1);

/*
 * Load Joomla system files
 */
$path = rtrim(__DIR__, DIRECTORY_SEPARATOR);
$rpos = strrpos($path, DIRECTORY_SEPARATOR);
$path = substr($path, 0, $rpos);
define('JPATH_BASE', $path);
require_once JPATH_BASE . '/includes/defines.php';

// Load the rest of the framework include files
if(file_exists(JPATH_LIBRARIES . '/import.legacy.php')){
	require_once JPATH_LIBRARIES . '/import.legacy.php';
}else{
	require_once JPATH_LIBRARIES . '/import.php';
}
require_once JPATH_LIBRARIES . '/cms.php';

// Load the JApplicationCli class
JLoader::import('joomla.application.cli');
JLoader::import('joomla.version');

if(version_compare(JVERSION, '3.4.9999', 'ge')){
	// Joomla! 3.5 and later does not load the configuration.php unless you explicitly tell it to.
	JFactory::getConfig(JPATH_CONFIGURATION . '/configuration.php');
}

/*
 * DMC Firewall Scheduled Reporting CLI application
 */
class DMCFirewallScheduledReportingCLI extends JApplicationCli {

	/*
	 * @param JInputCli $input
	 * @param JRegistry $config
	 * @param JDispatcher $dispatcher
	 */
	public function __construct(JInputCli $input = null, JRegistry $config = null, JDispatcher $dispatcher = null){
		// Close the application if we are not executed from the command line, Akeeba style (allow for PHP CGI)
		if(array_key_exists('REQUEST_METHOD', $_SERVER)){
			die('You are not supposed to access this script from the web. You have to run it from the command line.');
		}

		$cgiMode = false;

		if(!defined('STDOUT') || !defined('STDIN') || !isset($_SERVER['argv'])){
			$cgiMode = true;
		}

		// If a input object is given use it.
		if($input instanceof JInput){
			$this->input = $input;
		}
		// Create the input based on the application logic.
		else{
			if(class_exists('JInput')){
				if($cgiMode){
					$query = "";
					if(!empty($_GET)){
						foreach($_GET as $k => $v){
							$query .= " $k";
							if($v != ""){
								$query .= "=$v";
							}
						}
					}
					$query	 = ltrim($query);
					$argv	 = explode(' ', $query);
					$argc	 = count($argv);

					$_SERVER['argv'] = $argv;
				}

				$this->input = new JInputCLI();
			}
		}

		// If a config object is given use it.
		if($config instanceof JRegistry){
			$this->config = $config;
		}
		// Instantiate a new configuration object.
		else{
			$this->config = new JRegistry;
		}

		// If a dispatcher object is given use it.
		if($dispatcher instanceof JDispatcher){
			$this->dispatcher = $dispatcher;
		}
		// Create the dispatcher based on the application logic.
		else{
			$this->loadDispatcher();
		}
		
		// Load the configuration object.
		$this->loadConfiguration($this->fetchConfigurationData());

		// Set the execution datetime and timestamp;
		$this->set('execution.datetime', gmdate('Y-m-d H:i:s'));
		$this->set('execution.timestamp', time());

		// Set the current directory.
		$this->set('cwd', getcwd());
	}
	
	/*
	 * Execute the Scheduled Report
	 */
	public function execute(){
		$component				= JComponentHelper::getComponent('com_dmcfirewall');
		$componentParams		= $component->params;
		$reportDays				= $componentParams->get('emailsScheduledReportingReportDuration', 7);
			
		require JPATH_ADMINISTRATOR . '/components/com_dmcfirewall/helpers/graphstats.php';
		DmcfirewallGraphStatsHelper::buildScheduledReport($reportDays);
	}
}

// Instanciate and run the application
JApplicationCli::getInstance('DMCFirewallScheduledReportingCLI')->execute();