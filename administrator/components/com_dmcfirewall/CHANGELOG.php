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
?>
DMC Firewall - 1.4.2
================================================================================
! JavaScript issue within the Health Checker

DMC Firewall - 1.4.1
================================================================================
! Issue using 'Akeeba Strapper' would cause a Fatal Error if F0F wasn't installed as a library
# Compatibility issue with Nginx Web Server

DMC Firewall - 1.4.0
================================================================================
+ Support for Joomla! 2.5 dropped
+ Support for Zeus, LiteSpeed and Nginx Web Servers
+ [CORE|PRO] Additional Bad Bots added
~ Icons changed in administrator area
~ 'View Attack Log Summary' changed to 'Attack Log'
~ Language string changes
~ Layout changes
# Compatibility issue with PHP 7
# Scheduled Report CLI fix (Joomla! 3.5+)
! Security issue relating to 'backup.htaccess' and 'backup.web.config' files that were located in the root of website - <a href="http://www.webdevelopmentconsultancy.com/blog/dmc-firewall-security-issue.html" target="_blank">read more here</a>

DMC Firewall - 1.3.0
================================================================================
+ A Log entry is now added when an email is sent relating to DMC Content Sniffer
+ Integration with the Joomla! Update Manager (Joomla! 2.5.19+ and Joomla 3.2.2+)
+ You can now set a message that will be displayed when some one gets banned (initial ban)
+ You can now Whitelist multiple IP addresses so they won't get banned
+ You can now receive 'all emails except errors'
+ Better error reporting for any errors that arise
+ Better protection to the 'blocking' system so there is less chance that your '.htaccess' file becomes corrupt
+ [PRO] Additional 'bad bots' added
+ You can now set the 'bad terms' that Content Sniffer will look for in content
# Fixed an issue with 'Change Table Prefix' in Joomla! 3.3
# Fixed an issue where the Configuration wouldn't save if 'mod_security' was installed on the server - Thanks Milan
# Fixed an issue where Scheduled Email Reports were sent every time someone was banned - Thanks MJ
# Fixed an issue with pagination in Joomla! 3.x
# The Scheduled Report would always be sent to the email address specified within Global Config and not the override email address within DMC Firewall
~ DMC Content Sniffer now tells you what 'bad term' was found on the page along with the number of times it was found
~ Duplicate code removal

DMC Firewall - 1.2.2
================================================================================
! Compatibility issue Joomla! 3.2.1 Authentication

DMC Firewall - 1.2.1
================================================================================
! Compatibility issue relating to DMC Content Sniffer and third party extensions thanks @jacob_dixon

DMC Firewall - 1.2.0
================================================================================
+ Compatibility with Joomla! 3.2
+ You can now add/remove common hacker 'terms' that DMC Firewall will block or allow
+ You can now add/remove common SQL Injection 'terms' that DMC Firewall will block or allow
+ You can now set what emails to receive from DMC Firewall when someone is banned - thanks @jacob_dixon
+ You now receive an email when an update is available
+ You can now allow comments within your website without users being banned
+ Full compliance with the Joomla! Extension Directory [Core and Pro]
+ [PRO] You are now able to password protect the 'administrator' area of your website
+ You are now able to view a 'run down' of what DMC Firewall has blocked in the last week
+ DMC Firewall Statistics Module now displays 4 icons for ease of use (update status, configuration, attack summary, global configuration)
+ DMC Firewall Statistics Module now displays the 'Attack Summary' information
+ Additional words added to 'DMC Content Sniffer'
+ [PRO] Additional 'bad bots' added
+ [CORE] A number of 'bad bots' are now included within the CORE release
+ You can now configure what information is displayed within the 'Statistics Module'
+ Scheduled Reporting has been added, this sends a breakdown of what's been banned in the last x days
! [PRO] Error relating to 'Unable to load view' when you tried to change the default Super Admin ID
# Improvements to the 'Bad Bot' detection
# Edits to your '.htaccess' file after using Akeeba's Admin Tools 'Htaccess Maker' - thanks dpottier
# Content Sniffer now obeys the 'Email Override' setting with DMC Firewall Configuration
# [PRO] Better log details for Failed Login attempts
# The 'Take a backup' button now pops-up instead of taking you directly to the Akeeba backup control panel
# Language string fixes (spellings) - thanks @TJDixonLimited - @jacob_dixon - dpottier
# [PRO] The centralised server now stores bad username and password combinations
# HTML mark-up corrected within 'Content Sniffer'
# Language string fixes (wrong language strings loading)
# Issue when you had Akeeba Backup installed but disabled - the 'back up' icon would be displayed but would return a blank page once clicked
# Fixed display issue within the error email that gets sent out when an error occurs
~ Emails are now sent with JMailer instead of PHP's mail within 'plg_dmcfirewall'
~ 'Bad Content Settings' have moved to a new tab titled 'Security Settings' with additional settings
~ Better reporting with regards to SQL Injection attempts

DMC Firewall - 1.1.0
================================================================================
! 'plg_dmcfirewall' reported that every bad attempt was a 'good bad' so wouldn't ban the bad attempts [Core]

DMC Firewall - 1.0.0
================================================================================
+ DMC Firewall version 1.0 released!