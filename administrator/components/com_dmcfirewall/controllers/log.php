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

class DmcfirewallControllerLog extends FOFController
{
	public function  __construct($config = array())
	{
		parent::__construct($config);
	}
	
	public function execute($task)
	{
		switch($task)
		{
			case 'add':
			case 'default':
			case 'browse':
				$this->task = 'browse';
				break;
			case 'deleteEntry':
				$this->task = 'deleteEntry';
				break;
			case 'deleteEntryandIPAddress':
				$this->task = 'deleteEntryandIPAddress';
				break;
			default:
				$this->task = $task;
				break;
		}
		
		FOFInput::setVar('task', $this->task, $this->input);
		parent::execute($this->task);
	}
	
	public function deleteEntry()
	{
		if ($this->input instanceof FOFInput)
		{
			$cid = $this->input->get('cid', array(), 'array');
		}
		else
		{
			$cid = FOFInput::getArray('cid', array(), $this->input);
		}
		
		$id = FOFInput::getInt('id', 0, $this->input);
		
		if (empty($id))
		{
			if (!empty($cid) && is_array($cid))
			{
				$entryIDs = '';
				$countIDs = 0;
				foreach ($cid as $id)
				{
					$countIDs++;
					if ($entryIDs)
					{
						$entryIDs .= ',';
					}
					$entryIDs .= $id;
					
					/* Final fallback - just in case we don't have any id's */
					if (!$entryIDs)
					{
						$this->setRedirect(JURI::base() . 'index.php?option=com_dmcfirewall&view=log', JText::_('LOG_ENTRY_DELETE_ERROR'), 'error');
					}
				}
				
				$this->delete($entryIDs);
				$entry = $countIDs > 1 ? JText::_('ENTRIES') : JText::_('ENTRY');
				$this->setRedirect(JURI::base() . 'index.php?option=com_dmcfirewall&view=log', $entry . ' ' . JText::_('LOG_ENTRY_DELETE_SUCCESS'));
			}
		}
		else
		{
			$this->setRedirect(JURI::base() . 'index.php?option=com_dmcfirewall&view=log', JText::_('LOG_ENTRY_DELETE_ERROR'), 'error');
		}
		
		return;
	}
	
	protected function delete($ids)
	{
		$db = JFactory::getDBO();
		$db->setQuery("DELETE FROM `#__dmcfirewall_log` WHERE `id` IN ($ids)");
		$result = $db->execute();
	}
}