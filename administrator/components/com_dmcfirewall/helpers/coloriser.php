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

class DmcfirewallChangelogColoriser {
	public static function colorise($file, $onlyLast = false) {
		$ret = '';
		$currentLineNum = 0;
		$lines = @file($file);
		
		if (empty($lines)) {
			return $ret;
		}
		
		array_shift($lines);
		
		foreach($lines as $line) {
			$currentLineNum++;
			
			if ($currentLineNum >= 12) {
				$line = trim($line);
				if(empty($line)) continue;
				$type = substr($line,0,1);
				switch($type) {
					case '=':
						continue;
						break;
						
					case '+':
						$ret .= "\t".'<li class="firewall-changelog-added"><span></span>' . substr($line, 2) . "</li>\n";
						break;
					
					case '-':
						$ret .= "\t".'<li class="firewall-changelog-removed"><span></span>' . substr($line, 2) . "</li>\n";
						break;
					
					case '~':
						$ret .= "\t".'<li class="firewall-changelog-changed"><span></span>' . substr($line, 2) . "</li>\n";
						break;
					
					case '!':
						$ret .= "\t".'<li class="firewall-changelog-important"><span></span>' . substr($line, 2) . "</li>\n";
						break;
					
					case '#':
						$ret .= "\t".'<li class="firewall-changelog-fixed"><span></span>' . substr($line, 2) . "</li>\n";
						break;
					
					default:
						if(!empty($ret)) {
							$ret .= "</ul>";
							if($onlyLast) return $ret;
						}
						
						if(!$onlyLast) {
							$ret .= "<h3 class=\"firewall-changelog\">$line</h3>\n";
							$ret .= "<ul class=\"firewall-changelog\">\n";
						}
						
					break;
				}
			}
		}
		$ret .= '</ul>';
		return $ret;
	}
}