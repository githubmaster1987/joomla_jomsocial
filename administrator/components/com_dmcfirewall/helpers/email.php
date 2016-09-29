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

jimport('joomla.application.component.helper');
require_once JPATH_ADMINISTRATOR . '/components/com_dmcfirewall/helpers/getip.php';

class DmcfirewallHelperEmail {
	
	/*
	 * Function to send an email
	 * $emailType	The type of email that is being sent
	 * $data		Some data that will be included within the email
	 */
	public static function send($emailType, $data = null){
		// get an instance of the getMailer() and getApplication()
		$mailer					= JFactory::getMailer();
		$app					= JFactory::getApplication('site');
		
		
		// get some config settings
		$configSitename			= $app->getCfg('sitename');
		$configMailfrom			= $app->getCfg('mailfrom');
		
		// get our component params
		$componentParams 		= JComponentHelper::getParams('com_dmcfirewall');
		$enableEmailsParam		= $componentParams->get('enableEmails', 2);
		$emailOverrideParam		= $componentParams->get('emailOverride', null);
		$emailAddress			= (!strstr($emailOverrideParam, '@')) || (!strstr($emailOverrideParam, '.')) ? $configMailfrom : $emailOverrideParam;
		
		$shouldSendEmailOnBan	= 1;
		
		if(isset($data['reason'])){
			switch($data['reason']){
				case 'Hack Attempt':
					$shouldSendEmailOnBan = $componentParams->get('emailsHackAttempts', 1);
					break;
				case 'Known Bad Bot':
					$shouldSendEmailOnBan = $componentParams->get('emailsBadBots', 1);
					break;
				case 'SQL Injection Attempt':
					$shouldSendEmailOnBan = $componentParams->get('emailsSQLInjections', 1);
					break;
				case 'Failed Login':
					$shouldSendEmailOnBan = $componentParams->get('emailsFailedLogins', 1);
					break;
			}
		}
		
		$mailer->ClearAllRecipients();
        
		$emailReason = '';
		$type = '';
		
        switch($emailType){
            case 'ban':
				$type = 'success';
                $title = 'DMC Firewall - DMC Firewall just banned someone from ' . $configSitename . '!';
				$emailReason = 'You are receiving this email as your website has just banned someone!';
                break;
			case 'sniffer':
				$type = 'success';
                $title = 'DMC Firewall - ' . $configSitename . ' Possibly Hacked!';
				$emailReason = 'You are receiving this email as your \'Bad Content Threshold\' has been exceeded!';
                break;
			case 'update':
                $type = 'success';
				$title = 'DMC Firewall - Update Available for DMC Firewall on ' . $configSitename;
				$emailReason = 'This email is sent from YOUR website!';
                break;
			case 'weeklyReport':
				$type = 'success';
				$title = 'DMC Firewall - Scheduled Report for ' . $configSitename;
				$emailReason = '';
				break;
			//errors
			case 'noIPAddress':
				$type = 'error';
                $title = 'DMC Firewall - Couldn\'t Identify IP Address!';
                break;
			case 'smallerFileSize':
                $type = 'error';
                $title = 'DMC Firewall - Server file smaller than original for ' . $configSitename . '!';
                break;
			case 'noFile':
                $type = 'error';
                $title = 'DMC Firewall - No server file found for ' . $configSitename . '!';
                break;
			case 'readIssue':
                $type = 'error';
                $title = 'DMC Firewall - We couldn\'t read your server file on ' . $configSitename . '!';
                break;
			case 'writeIssue':
                $type = 'error';
                $title = 'DMC Firewall - We couldn\'t write to your server file on ' . $configSitename . '!';
                break;
			case 'couldntCopyFile':
                $type = 'error';
                $title = 'DMC Firewall - We couldn\'t copy your server file on ' . $configSitename . '!';
                break;
			case 'noContent':
                $type = 'error';
                $title = 'DMC Firewall - There was no content within your server file on ' . $configSitename . '!';
                break;
            case 'contactUs':
                $type = 'error';
                $title = 'DMC Firewall - Please contact us regarding ' . $configSitename . '!';
				$emailReason = '';
                break;
			case 'multipleLimit':
				$type = 'error';
                $title = 'DMC Firewall - Multiple Limit blocks found on ' . $configSitename . '!';
			break;
			case 'noWebConfigBlock':
                $type = 'error';
                $title = 'DMC Firewall - No Web Config Block on ' . $configSitename . '!';
                break;
            case 'noLimit':
                $type = 'error';
                $title = 'DMC Firewall - No Limit Block on ' . $configSitename . '!';
                break;
            case 'alreadyBlockedIP':
                $type = 'error';
                $title = 'DMC Firewall - Website Bombardment on ' . $configSitename . '!';
                break;
        }
        
		$body = self::emailHeader($configSitename, $emailReason, $emailType) . self::emailContent($emailType, $data) . self::emailFooter();
		$mailer->setSender(array($configMailfrom, $configSitename));
		$mailer->addRecipient($emailAddress, $configSitename);
		$mailer->setSubject($title);
		$mailer->isHTML(true);
		$mailer->setBody($body);
		
		switch ($enableEmailsParam){
			case 0:
				// Don't send any emails
				break;
			case 1:
				if($type == 'error'){
					$mailer->Send();
				}
				break;
			case 2:
				if($shouldSendEmailOnBan){
					$mailer->Send();
				}
				break;
			case 3:
				if($type != 'error'){
					$mailer->Send();
				}
				break;
		}
		
		return true;
	}
	
	protected static function emailContent($type, $data = null){
		$content					= '';
		$contentToAdd				= '';
		$ua							= '';
		$requestedURI				= $_SERVER['REQUEST_URI'];
		$ipAddress					= DmcfirewallHelperIp::getIP();
		$userAgent					= $_SERVER['HTTP_USER_AGENT'];
		$requestMethod				= $_SERVER['REQUEST_METHOD'];
		$referrer					= empty($_SERVER['HTTP_REFERER']) ? 'NA' : $_SERVER['HTTP_REFERER'];
		
		if($type == 'ban'){
			$reason = $data['reason'];
		}
		
		switch($type){
			case 'noIPAddress':
				$content =<<<NOIPADDRESS
	<tr>
		<td colspan="2" style="border:1px solid #000;">We couldn't identify a visitors IP address.<br /><br />We have aborted or edits!</td>
	</tr>
NOIPADDRESS;
                break;
			case 'smallerFileSize':
				$content =<<<SMALLERFILESIZECONTENT
	<tr>
		<td colspan="2" style="border:1px solid #000;">After we modified your server file - the file size was smaller than the original file that we copied.<br /><br />We have aborted or edits!</td>
	</tr>
SMALLERFILESIZECONTENT;
				break;
			case 'update':
				$content =<<<UPDATECONTENT
	<tr>
		<td colspan="2" style="border:1px solid #000;">An update is available for DMC Firewall.<br /><br />DMC Firewall is a Joomla Security Extension that you have installed within your website!<br /><br />Please update as soon as possible!</td>
	</tr>
UPDATECONTENT;
				break;
			case 'noFile':
				$content =<<<READISSUECONTENT
<tr>
	<td style="border:1px solid #000;">Issue</td>
	<td style="border:1px solid #000;">You didn't have a server file (.htaccess or web.config) - please check the Configuration tab within DMC Firewall!</td>
</tr>

READISSUECONTENT;
				break;
			case 'readIssue':
				$content =<<<READISSUECONTENT
<tr>
	<td style="border:1px solid #000;">Issue</td>
	<td style="border:1px solid #000;">We couldn't read your server file so we couldn't block the IP address - this could be due to server load!</td>
</tr>

READISSUECONTENT;
				break;
			case 'writeIssue':
				$content =<<<WRITEISSUECONTENT
<tr>
	<td style="border:1px solid #000;">Issue</td>
	<td style="border:1px solid #000;">We couldn't make our edits to your server file - this could be due to server load!</td>
</tr>

WRITEISSUECONTENT;
				break;
			case 'couldntCopyFile':
				$content =<<<COULDNTCOPYFILECONTENT
<tr>
	<td style="border:1px solid #000;">Issue</td>
	<td style="border:1px solid #000;">We couldn't copy the contents of your server file - this could be due to server load!</td>
</tr>

COULDNTCOPYFILECONTENT;
				break;
			case 'noContent':
				$content =<<<NOCONTENT
<tr>
	<td style="border:1px solid #000;">Issue</td>
	<td style="border:1px solid #000;">No content was found in our copy of your server file - this could be due to server load!</td>
</tr>

NOCONTENT;
				break;
			case 'noWebConfigBlock':
				$content =<<<NOWEBCONFIGBLOCKCONTENT
<tr>
	<td style="border:1px solid #000;">Issue</td>
	<td style="border:1px solid #000;">You have a 'web.config' file - But you don't have the ban IP section.</td>
</tr>

NOWEBCONFIGBLOCKCONTENT;
				break;
			case 'multipleLimit':
				$content =<<<MULTIPLELIMITCONTENT
<tr>
	<td style="border:1px solid #000;">Issue</td>
	<td style="border:1px solid #000;">While modifying your .htaccess file - your server was being bombarded and we ended up with multiple / Limit blocks!<br /><br />We aborted our edits!</td>
</tr>

MULTIPLELIMITCONTENT;
				break;
			case 'noLimit':
				$content =<<<NOLIMITCONTENT
<tr>
	<td style="border:1px solid #000;">Issue</td>
	<td style="border:1px solid #000;">Your '.htaccess' file didn't have a / Limit block so we aborted blocking the IP address!</td>
</tr>

NOLIMITCONTENT;
				break;
			case 'contactUs':
				$content =<<<CONTACTUSCONTENT
<tr>
	<td style="border:1px solid #000;">Issue</td>
	<td style="border:1px solid #000;">There has been an issue while attempting to block an IP address!<br /><br />If you are willing to provide us with FTP access, Joomla access and hosting access, please contact us so we can improve DMC Firewall!</td>
</tr>

CONTACTUSCONTENT;
				break;
			case 'weeklyReport':
				$content = $data;
				break;
			case 'ban':
                $content =<<<BANCONTENT
	<tr>
		<td width="250px" style="border:1px solid #000;">Reason</td>
		<td width="450px" style="border:1px solid #000;">{$reason}</td>
	</tr>
	<tr>
		<td style="border:1px solid #000;">IP Address</td>
		<td style="border:1px solid #000;">{$ipAddress}</td>
	</tr>
	<tr>
		<td style="border:1px solid #000;">User Agent</td>
		<td style="border:1px solid #000;">{$userAgent}</td>
	</tr>
	<tr>
		<td style="border:1px solid #000;">Request Method</td>
		<td style="border:1px solid #000;">{$requestMethod}</td>
	</tr>
	<tr>
		<td style="border:1px solid #000;" valign="top">Request URI</td>
		<td style="border:1px solid #000;">{$requestedURI}</td>
	</tr>
	<tr>
		<td style="border:1px solid #000;">Referer</td>
		<td style="border:1px solid #000;">{$referrer}</td>
	</tr>
BANCONTENT;
				break;
			case 'alreadyBlockedIP':
				$content =<<<ALREADYBLOCKEDCONTENT
<tr>
	<td style="border:1px solid #000;">Issue</td>
	<td style="border:1px solid #000;">IP address is already in the server file</td>
</tr>
<tr>
	<td style="border:1px solid #000;">IP Address</td>
	<td style="border:1px solid #000;">{$ipAddress}</td>
</tr>

ALREADYBLOCKEDCONTENT;
				break;
			case 'sniffer':
				if($data['ua']){
					// if 'ua' is found, unset it so we don't use it within our calculations, place 'ua' in a variable so we can use it later
					$ua = $data['ua'];
					unset($data['ua']);
				}else{
					$ua = 'Not set!';
				}
				$contentToAdd .= "The 'terms found' have been reversed to prevent spam filters blocking this email. A full 'human readable' version can be located within DMC Firewall -> View Attack Log Summary!<br /><br />";
				
				foreach($data as $termFound => $times){
					$timesS = $times > 1 ? 's' : '';
					$contentToAdd .= strrev($termFound) . ' (found <strong>' . $times . '</strong> time' . $timesS . ')<br />';
				}
				
				$content =<<<SNIFFERCONTENT
	<tr>
		<td style="border:1px solid #000;">Requested URI</td>
		<td style="border:1px solid #000;">{$requestedURI}</td>
	</tr>
	<tr>
		<td style="border:1px solid #000;" valign="top">Visitors user agent</td>
		<td style="border:1px solid #000;">{$ua}</td>
	</tr>
	<tr>
		<td style="border:1px solid #000;" valign="top">Bad terms found</td>
		<td style="border:1px solid #000;">{$contentToAdd}</td>
	</tr>

SNIFFERCONTENT;
				break;
		}
		
		if($content){
			return $content;
		}
	}
	
	protected static function emailHeader($siteName, $emailReason, $emailType){
		$theDate		= date('M d Y');
		$theTime		= date('H:i:s');
		$addContent		= '';
		$leftAddContent = '';
		$rightAddContent = '';
		
		if($emailType == 'weeklyReport'){
			$leftAddContent = ' colspan="2"';
			$rightAddContent = ' colspan="3"';
		}
		
		return <<<EMAILHEADER
This is a security alert from the automated 'DMC Firewall Script' installed on your Joomla powered website:<br /><br />
{$emailReason}
<table width="750px" style="margin-left:25px; margin-top:15px; margin-bottom:15px; border-collapse:collapse;">
	<tr>
		<td width="150px" $leftAddContent style="border:1px solid #000;">Site Name</td>
		<td width="550px" $rightAddContent style="border:1px solid #000;">{$siteName}</td>
	</tr>
	<tr>
		<td style="border:1px solid #000;" $leftAddContent>Date - Time</td>
		<td style="border:1px solid #000;" $rightAddContent>{$theDate} - {$theTime}</td>
	</tr>
EMAILHEADER;
	}
	
	protected static function emailFooter(){
		return <<<EMAILFOOTER
</table>
DMC Firewall is a Joomla Security extension by<br />
Dean Marshall Consultancy Ltd<br />
http://www.deanmarshall.co.uk/<br />
http://www.webdevelopmentconsultancy.com/
EMAILFOOTER;
	}
}