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

$inlineStyle =<<<INLINESTYLE
body.component { padding-top:0;}
INLINESTYLE;
$inlineJavaScript =<<<INLINEJAVASCRIPT
window.addEvent( 'domready' ,  function() {
	var options = {'height': 440, 'width':700};
	var chart = new MilkChart.Column('com_dmcfirewall_loggraph', options);
});
INLINEJAVASCRIPT;

JFactory::getDocument()->addStyleDeclaration($inlineStyle);
JFactory::getDocument()->addScript(JURI::root() . 'media/com_dmcfirewall/js/milkchart.yc.js');
JFactory::getDocument()->addScriptDeclaration($inlineJavaScript,'text/javascript');

$jVersion = new JVersion();
if ($jVersion->RELEASE == '2.5') {
	JHTML::_('behavior.mootools');
}

?>
<table id="com_dmcfirewall_loggraph">
	<thead>
		<tr>
			<th>Bad Bots</th><th>SQL Injections</th><th>Hack Attempts</th><th>Bad Logins</th> 
		</tr>
	</thead>
	<tbody>
		<?php
		foreach($this->weekStats as $day) {
			echo '<tr><td>';
			echo $day['badBots'];
			echo '</td><td>';
			echo $day['sqlInjection'];
			echo '</td><td>';
			echo $day['hackInjection'];
			echo '</td><td>';
			echo $day['badLogins'];
			echo '</td></tr>';
		}
		?>
	</tbody>
	<tfoot>
		<tr>
		<?php
		foreach($this->weekStats as $day) {
			echo '<td>';
			echo date("l", strtotime($day['date']));
			echo '</td>';
		}
		?>
		</tr>
	</tfoot>
</table>