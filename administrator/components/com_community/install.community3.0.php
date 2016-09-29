<?php
/**
* @copyright (C) 2013 iJoomla, Inc. - All rights reserved.
* @license GNU General Public License, version 2 (http://www.gnu.org/licenses/gpl-2.0.html)
* @author iJoomla.com <webmaster@ijoomla.com>
* @url https://www.jomsocial.com/license-agreement
* The PHP code portions are distributed under the GPL license. If not otherwise stated, all images, manuals, cascading style sheets, and included JavaScript *are NOT GPL, and are released under the IJOOMLA Proprietary Use License v1.0
* More info at https://www.jomsocial.com/license-agreement
*/

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

/**
 * Script file of Ola component
 */
class com_communityInstallerScript
{
	/**
	 * method to install the component
	 *
	 * @return void
	 */
	function install($parent)
	{
		// $parent is the class calling this method
		//$parent->getParent()->setRedirectURL('index.php?option=com_community');
	}

	/**
	 * method to uninstall the component
	 *
	 * @return void
	 */
	function uninstall($parent)
	{
		// $parent is the class calling this method
		//echo '<p>' . JText::_('com_community uninstall script') . '</p>';
	}

	/**
	 * method to update the component
	 *
	 * @return void
	 */
	function update($parent)
	{
		// $parent is the class calling this method
		//echo '<p>' . JText::_('com_community update script') . '</p>';
	}

	/**
	 * method to run before an install/update/uninstall method
	 *
	 * @return void
	 */
	function preflight($type, $parent)
	{
		// $parent is the class calling this method
		// $type is the type of change (install, update or discover_install)
		//echo '<p>' . JText::_('com_community pre flight script') . '</p>';
	}

	/**
	 * method to run after an install/update/uninstall method
	 *
	 * @return void
	 */
	function postflight($type, $parent)
	{
		// get the installing com_community version
		$installer			= JInstaller::getInstance();
		$path				= $installer->getPath('manifest');
		$communityVersion	= $installer->getManifest()->version;

		if ( version_compare(JVERSION,'3.0','<') && $communityVersion >= '4.2')
		{
			JFactory::getApplication()->enqueueMessage('JomSocial 2.8.x require minimum Joomla! CMS 2.5.6', 'notice');

			return false;
		}

		$lang = JFactory::getLanguage();
		$lang->load('com_community', JPATH_ROOT.'/administrator');

		$destination = JPATH_ROOT.'/administrator/components/com_community/';
		$buffer      = "installing";

		if( ! JFile::write($destination.'installer.dummy.ini', $buffer))
		{
			ob_start();
			?>
			<table width="100%" border="0">
				<tr>
					<td>
						There was an error while trying to create an installation file.
						Please ensure that the path <strong><?php echo $destination; ?></strong> has correct permissions and try again.
					</td>
				</tr>
			</table>
			<?php
			$html = ob_get_contents();
			@ob_end_clean();
		}
		else
		{
			$link = rtrim(JURI::root(), '/').'/administrator/index.php?option=com_community';

            //before the installation, check if old modules are installed, if yes, we have to provide an option for the user to cancel
            $db = JFactory::getDBO();

            //module to be removed
            $modules = array(
                'mod_activegroups',
                'mod_activitystream',
                'mod_community_quicksearch',
                'mod_community_search_nearbyevents',
                'mod_community_whosonline',
                'mod_datingsearch',
                'mod_hellome',
                'mod_jomsocialconnect',
                'mod_latestdiscussion',
                'mod_latestgrouppost',
                'mod_notify',
                'mod_photocomments',
                'mod_statistics',
                'mod_topmembers',
                'mod_videocomments'
            );

            //plugins to be removed
            $plugins = array(
                'invite', //its not plg_invite because its stored in db as invite as the element
                'input',
                'friendslocation',
				'kunena',
                'events',
                'feeds',
                'latestphoto',
				'jomsocialconnect'
            );

            $installedModules = array();
            $installedPlugins = array();

            //JInstaller
            foreach($modules as $module){
                //check if the module is installed
                $query = "SELECT id FROM ".$db->quoteName('#__modules')." WHERE ".$db->quoteName('module')."=".$db->quote($module);
                $db->setQuery($query);
                $installed = $db->loadResult();

                if($installed){
                    $installedModules[] = $module;
                }
            }

            foreach($plugins as $plugin){
                //check if the plugin is installed
				$query = "SELECT extension_id FROM ".$db->quoteName('#__extensions')
						." WHERE ("
						.$db->quoteName('folder')." = ".$db->quote('community')
						." OR (" // we have to be very strict here, which mean we only search for jomsocialconnect plugin in system to avoid conflict such as kunena plg that is suppose to be removed from community, not system
							. $db->quoteName('folder')." = ".$db->quote('system')." AND "
							. $db->quoteName('element')."=".$db->quote('jomsocialconnect')
							.")) AND "
						.$db->quoteName('element')." = ".$db->quote($plugin)." AND "
						.$db->quoteName('type')." = ".$db->quote('plugin');

                $db->setQuery($query);
                $installed = $db->loadResult();
                if($installed){
                    $installedPlugins[] = 'plg_'.$plugin;
                }
            }

            $installedExtensions = array_merge($installedModules,$installedPlugins);


			ob_start();
			?>
			<style type="text/css">

				#j-main-container .span12 {
					margin-left: 0 !important;
				}

				.adminform {
					width: 100%;
				}
				.adminform th {
					display: none;
				}
				.joms-install {
					margin-bottom: 30px;
					text-align: center;
					width: 100%;
				}
				.joms-install__wrapper {
					margin: 0 auto;
					max-width: 1140px;
					min-height: 400px;
					background: url(<?php echo JURI::root(); ?>administrator/components/com_community/installer/img/install-bg-4.jpg) no-repeat top center;
					position: relative;
					overflow: hidden;
				}
				.joms-install__wrapper h2,
				.joms-install__wrapper h3 {
					color: white;
					font-weight: normal;
				}
				.joms-install__content {
					position: absolute;
					bottom: 0;
					width: 100%;
					background: rgba(0,0,0,0.7);
					padding: 20px 0;
				}
				.joms-install__button {
					padding: 12px 24px;
					border: 0;
					background: #17cd30;
					color: white;
					font-size: 20px;
					margin-top: 15px;
				}
				.joms-install__button:hover {
					background: #45c644;
					color: white;
				}

				.label-module {
					margin:4px;
					background: #FF5722;
				    padding: 6px 12px;
				    font-size: 12px;
				    text-shadow: none;
				    display: inline-block;
				    color: white;
				}

			</style>
			<!-- Installation message // -->
			<div class="joms-install">
				<div class="joms-install__wrapper js-header">
					<img style="margin-top: 75px;" src="<?php echo JURI::root(); ?>administrator/components/com_community/installer/img/logo.png">
					<div class="joms-install__content">
						<h2>JomSocial is a social networking component for Joomla!</h2>
						<h3>Thank you for choosing JomSocial, please click on the following button to complete your installation.</h3>
						<input type="button" class="joms-install__button" onclick="window.location = '<?php echo $link; ?>'" value="<?php echo JText::_('COM_COMMUNITY_INSTALLATION_COMPLETE_YOUR_INSTALLATION');?>"/>
					</div>
				</div>
				<div style="height:16px;"></div>
                <?php if(count($installedExtensions) > 0){ ?>
                    <div>
                        <h2>These extensions are not compatible anymore.</h2>
                        <p style="font-size:16px; margin-bottom:24px;">Compatible extensions will be installed back, but you'll have to configure them again.</p>
                        <?php echo '<span class="label label-module">' . implode('</span><span class="label label-module">',$installedExtensions) . '</span>' ; ?>
                    </div>

                <?php } ?>
			</div>
			<!-- \\ -->
			<?php
			$html = ob_get_contents();
			@ob_end_clean();
		}

		echo $html;
	}
}
