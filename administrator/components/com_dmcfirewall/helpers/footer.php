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

class DmcfirewallHelperFooter {
	
	/**
	 *
	 */
	public static function buildFooter(){
		$socialHeading = JText::_('COM_DMCFIREWALL_FOOTER_SOCIAL_HEADER');
		$copyright = JText::_('COM_DMCFIREWALL_FOOTER_COPYRIGHT');
		$versionText = JText::_('COM_DMCFIREWALL_FOOTER_VERSION_TEXT');
		$version = DMCFIREWALL_VERSION;
		$releaseDateText = JText::_('COM_DMCFIREWALL_FOOTER_RELEASE_DATE_TEXT');
		$releaseDate = DMCFIREWALL_RELEASE_DATE;
		$releaseNotes = JText::_('COM_DMCFIREWALL_FOOTER_RELEASE_NOTES');
		$disclaimerHeader = JText::_('COM_DMCFIREWALL_FOOTER_DISCLAIMER_HEADER');
		$disclaimer = JText::_('COM_DMCFIREWALL_FOOTER_DISCLAIMER');
		
		$html =<<<FOOTERHTML
<div id="footer" class="well well-small">
	$copyright
	$versionText $version<br />
	$releaseDateText $releaseDate $releaseNotes
	$disclaimerHeader
	$disclaimer
	<!-- Social -->
	$socialHeading
	<div id="fb-root"></div>
	<script>(function(d, s, id) {
	  var js, fjs = d.getElementsByTagName(s)[0];
	  if (d.getElementById(id)) return;
	  js = d.createElement(s); js.id = id;
	  js.src = "//connect.facebook.net/en_GB/all.js#xfbml=1";
	  fjs.parentNode.insertBefore(js, fjs);
	}(document, 'script', 'facebook-jssdk'));</script>

	<div style="float:left; margin-right:5px; margin-top:7px; display:inline-block;" class="fb-like" data-href="http://www.facebook.com/DeanMarshallConsultancyLtd" data-send="false" data-layout="button_count" data-width="91" data-show-faces="false" data-font="arial"></div>
	
	<span style="margin-top:7px; display:inline-block;">
		<a href="https://twitter.com/DMConsultancy" class="twitter-follow-button" data-show-count="false">Follow @DMConsultancy</a>
		<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?'http':'https';if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+'://platform.twitter.com/widgets.js';fjs.parentNode.insertBefore(js,fjs);}}(document, 'script', 'twitter-wjs');</script>
	</span>
</div>
FOOTERHTML;
		
		return $html;
	}
}