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

class DmcfirewallControllerConfig extends FOFController
{
	public function __construct($config = array())
	{
		parent::__construct($config);
		
		$this->modelName = 'config';
	}
	
	public function execute($task)
	{
		if (!in_array($task, array('addFile', 'makeEdits')))
		{
			$task = 'browse';
		}
		
		parent::execute($task);
	}
	
	/*
	 * Make the edits to the '.htaccess' or 'web.config' file
	 */
	public function makeEdits()
	{
		$model = $this->getThisModel();
		$editFileResult = $model->modifyServerFile();
		
		switch ($editFileResult)
		{
		// web.config
			case '001':
				$msg = JText::_('CONFIG_WEBCONFIG_EDITS_NOT_READABLE'); 		//file isn't readable
				$msgType = 'error';
				break;
			case '002':
				$msg = JText::_('CONFIG_WEBCONFIG_EDITS_NOT_WRITABLE');			//couldn't write to the file
				$msgType = 'error';
				break;
			case '003':
				$msg = JText::_('CONFIG_WEBCONFIG_EDITS_SUCCESSFUL');			//successfully made the edits
				$msgType = 'message';
				break;
			case '004':
				$msg = JText::_('CONFIG_WEBCONFIG_EDITS_COUNDNT_MAKE');			//couldn't make the edits
				$msgType = 'error';
				break;
		//.htaccess	
			case '005':
				$msg = JText::_('CONFIG_HTACCESS_EDITS_NOT_READABLE');			//htaccess not readable
				$msgType = 'error';
				break;
			case '006':
				$msg = JText::_('CONFIG_HTACCESS_EDITS_NOT_WRITABLE');			//htaccess not writeable
				$msgType = 'error';
				break;
			case '007':
				$msg = JText::_('CONFIG_HTACCESS_EDITS_SUCCESSFUL');			//edits successful
				$msgType = 'message';
				break;
			case '008':
				$msg = JText::_('CONFIG_HTACCESS_EDITS_SOMETHING_WENT_WRONG');	//something went wrong
				$msgType = 'error';
				break;
			case '009':
				$msg = JText::_('CONFIG_HTACCESS_EDITS_COULDNT_MAKE_EDITS');	//couldn't make any the edits
				$msgType = 'error';
				break;
		//couldn't identify server
			case '010':
				$msg = JText::_('CONFIG_MAKE_EDITS_NO_SERVER_FOUND');			//couldn't identify server
				$msgType = 'error';
				break;
		}
		
		$this->setRedirect('index.php?option=com_dmcfirewall&view=config', $msg, $msgType);
		$this->redirect();
	}
	
	/*
	 * Add the '.htaccess' or 'web.config' file from the 'assets/servertype/' folder
	 */
	public function addFile()
	{
		$model = $this->getThisModel();
		$addFileResult = $model->addServerFile();
		
		switch ($addFileResult)
		{
		// web.config
			case '001':
				$msg = JText::_('CONFIG_WEBCONFIG_SUCCESSFUL');			// web.config copied successfully
				$msgType = 'message';
				break;
			case '002':
				$msg = JText::_('CONFIG_WEBCONFIG_COPY_FAILED');		// web.config copy error
				$msgType = 'error';
				break;
			case '003':
				$msg = JText::_('CONFIG_WEBCONFIG_ALREADY_EXISTS');		// web.config already exists
				$msgType = 'error';
				break;
		// .htaccess
			case '004':
				$msg = JText::_('CONFIG_HTACCESS_SUCCESSFUL');			// .htaccess copied successfully
				$msgType = 'message';
				break;
			case '005':
				$msg = JText::_('CONFIG_HTACCESS_COPY_FAILED');			// .htaccess copy error
				$msgType = 'error';
				break;
			case '006':
				$msg = JText::_('CONFIG_HTACCESS_ALREADY_EXISTS');		// .htaccess already exists
				$msgType = 'error';
				break;
		// no idea what server your website is running on
			case '007':
				$msg = JText::_('CONFIG_SERVER_NOT_READABLE');			// couldn't identify server type
				$msgType = 'error';
				break;
		}
		
		$this->setRedirect('index.php?option=com_dmcfirewall&view=config', $msg, $msgType);
		$this->redirect();
	}
}