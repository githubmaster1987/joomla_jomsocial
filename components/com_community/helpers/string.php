<?php

/**
 * @copyright (C) 2013 iJoomla, Inc. - All rights reserved.
 * @license GNU General Public License, version 2 (http://www.gnu.org/licenses/gpl-2.0.html)
 * @author iJoomla.com <webmaster@ijoomla.com>
 * @url https://www.jomsocial.com/license-agreement
 * The PHP code portions are distributed under the GPL license. If not otherwise stated, all images, manuals, cascading style sheets, and included JavaScript *are NOT GPL, and are released under the IJOOMLA Proprietary Use License v1.0
 * More info at https://www.jomsocial.com/license-agreement
 */
defined('_JEXEC') or die('Restricted access');

class CStringHelper {

    /**
     * Tests a bunch of text and see if it contains html tags.
     *
     * @param	$text	String	A text value.
     * @return	$text	Boolean	True if the text contains html tags and false otherwise.
     * */
    static public function isHTML($text) {
        $pattern = '/\<p\>|\<br\>|\<br \/\>|\<b\>|\<div\>/i';
        preg_match($pattern, JString::strtolower($text), $matches);

        return empty($matches) ? false : true;
    }

    /**
     *  Auto-link the given string
     */
    static function autoLink($text) {
        /* subdomain must be taken into consideration too */
        $pattern = '~(
					  (
					   #(?<=([^[:punct:]]{1})|^)			# that must not start with a punctuation (to check not HTML)
					   	(https?://)|(www)[^-][a-zA-Z0-9-]*?[.]	# normal URL lookup
					   )
					   [^\s()<>]+						# characters that satisfy SEF url
					   (?:								# followed by
					   		\([\w\d]+\)					# common character
					   		|							# OR
					   		([^[:punct:]\s]|/)			# any non-punctuation character followed by space OR forward slash
					   )
					 )~x';
        $callback = create_function('$matches', '
            $url = array_shift( $matches );
            $url_parts = parse_url( $url );

            $text = parse_url( $url, PHP_URL_HOST ) . parse_url( $url, PHP_URL_PATH );
            $substr = substr( $text, 0, 50 );

            if ( $substr !== $text ) {
                $text = $substr . "&hellip;";
            }

            if(strpos($url,\'www\')!==false && strpos($url,\'http://\')===false && strpos($url,\'https://\')===false)
            {
            	$url = \'http://\'.$url;
            }

            $isNewTab = CFactory::getConfig()->get(\'newtab\',false);
            $isInternal = !$isNewTab ? \'\': \'target="_blank" \';
            return sprintf(\'<a rel="nofollow" \'.$isInternal .\' href="%s">%s</a>\', $url, $text);
	   ');
        return preg_replace_callback($pattern, $callback, $text);
    }

    /**
     * Automatically converts new line to html break tag.
     *
     * @param	$text	String	A text value.
     * @return	$text	String	A formatted data which contains html break tags.
     * */
    static public function nl2br($text) {
        $text = CString::str_ireplace(array("\r\n", "\r", "\n"), "<br />", $text);
        return preg_replace("/(<br\s*\/?>\s*){3,}/", "<br /><br />", $text);
    }

    static public function isPlural($num) {
        return !CStringHelper::isSingular($num);
    }

    static public function isSingular($num) {
        $config = CFactory::getConfig();
        $singularnumbers = $config->get('singularnumber');
        $singularnumbers = explode(',', $singularnumbers);

        return in_array($num, $singularnumbers);
    }

    static public function escape($var, $function = 'htmlspecialchars') {
        $disabledFunctions = array('eval', 'exec', 'passthru', 'system', 'shell_exec');
        if (!in_array($function, $disabledFunctions)) {
            if (in_array($function, array('htmlspecialchars', 'htmlentities'))) {
                return call_user_func($function, $var, ENT_COMPAT, 'UTF-8');
            }
            return call_user_func($function, $var);
        }
    }

    /**
     * @deprecated
     */
    static public function clean($string) {
        jimport('joomla.filter.filterinput');
        $safeHtmlFilter = JFilterInput::getInstance();
        return $safeHtmlFilter->clean($string);
    }

    /**
     * @todo: this would fail if the username contains {} char
     */
    static public function replaceThumbnails($data) {
        // Replace matches for {user:thumbnail:ID} so that this can be fixed even if the caching is enabled.
        $html = preg_replace_callback('/\{user:thumbnail:(.*)\}/', array('CStringHelper', 'replaceThumbnail'), $data);

        return $html;
    }

    static public function replaceThumbnail($matches) {
        static $data = array();

        if (!isset($data[$matches[1]])) {
            $user = CFactory::getUser($matches[1]);
            $data[$matches[1]] = $user->getThumbAvatar();
        }

        return $data[$matches[1]];
    }

    /**
     * Truncate the given text
     * @deprecated Use truncate instead. Trim has different meaning in PHP
     * @param string	$value
     * @param int		$length
     * @return string
     */
    static public function trim($value, $length) {
        return JHTML::_('string.truncate', $value, $length);
    }

    /**
     * Truncate the given text and append with '...' if necessary
     * @param string $str			string to truncate
     * @param int	 $lenght		length of the final string
     * @deprecated in 2.8. Removed in 3.0
     */
    static public function truncate($value, $length, $wrapSuffix = '<span>...</span>', $excludeImg = true) {
        if ($excludeImg) {
            $value = preg_replace("/<img[^>]+\>/i", " ", $value);
        }

        if (JString::strlen($value) > $length) {
            return JString::substr($value, 0, $length) . ' ' . $wrapSuffix;
        }
        return $value;
    }

    /**
     * Trims text to a certain number of words.
     *
     *
     * @since 3.2
     *
     * @param string $text Text to trim.
     * @param int $num_words Number of words. Default 55.
     * @param string $more Optional. What to append if $text needs to be trimmed. Default '&hellip;'.
     * @return string Trimmed text.
     */
    static public function trim_words($text, $num_words = 25, $more = null) {
        if (null === $more)
            $more = '&hellip;';
        $original_text = $text;
        $text = strip_tags($text);
        /* translators: If your word count is based on single characters (East Asian characters),
          enter 'characters'. Otherwise, enter 'words'. Do not translate into your own language. */

        $words_array = preg_split("/[\n\r\t ]+/", $text, $num_words + 1, PREG_SPLIT_NO_EMPTY);
        $sep = ' ';

        if (count($words_array) > $num_words) {
            array_pop($words_array);
            $text = implode($sep, $words_array);
            $text = $text . $more;
        } else {
            $text = implode($sep, $words_array);
        }
        return $text;
    }

    static public function getRandom($length = 11) {
        $map = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $len = strlen($map);
        $stat = stat(__FILE__);
        $randomString = '';

        if (empty($stat) || !is_array($stat))
            $stat = array(php_uname());

        mt_srand(crc32(microtime() . implode('|', $stat)));
        for ($i = 0; $i < $length; $i ++) {
            $randomString .= $map[mt_rand(0, $len - 1)];
        }

        return $randomString;
    }

    /**
     * Get emoticon
     * @param  [string] $str [Status message]
     * @return [string]      [Imoticon icon]
     */
    static public function getEmoticon($str) {

        if(!CFactory::getConfig()->get('statusemoticon')){
          return $str;
        }
        /* Parse emotion icons */
        $emoticons = array(
            '<i class="joms-icon-happy2 joms-status-emoticon"></i>' => array(':happy:', ':))'),
            '<i class="joms-icon-smiley2 joms-status-emoticon"></i>' => array(':smile:', ':)',':-)'),
            '<i class="joms-icon-tongue2 joms-status-emoticon"></i>' => array(':tongue:', ':p', ':P'),
            '<i class="joms-icon-sad2 joms-status-emoticon"></i>' => array(':sad:', ':('),
            '<i class="joms-icon-wink2 joms-status-emoticon"></i>' => array(':wink:', ';)'),
            '<i class="joms-icon-grin2 joms-status-emoticon"></i>' => array(':grin:', ':D'),
            '<i class="joms-icon-cool2 joms-status-emoticon"></i>' => array(':cool:', 'B)'),
            '<i class="joms-icon-angry2 joms-status-emoticon"></i>' => array(':angry:', '>:('),
            '<i class="joms-icon-evil2 joms-status-emoticon"></i>' => array(':evil:', '>:D'),
            '<i class="joms-icon-shocked2 joms-status-emoticon"></i>' => array(':shocked:', ':o', ':O'),
            '<i class="joms-icon-confused2 joms-status-emoticon"></i>' => array(':confused:', ':?'),
            '<i class="joms-icon-neutral2 joms-status-emoticon"></i>' => array(':neutral:', ':|'),
            '<i class="joms-icon-heart joms-status-emoticon"></i>' => array(':love:', '&lt;3'),
                /* Add more emotion array here */
        );

        foreach ($emoticons as $key => $emotion) {
            $str = str_replace($emotion, $key, $str);
        }
        return $str;
    }

    /*
     * Attaches mood emoticon
     */
    static public function getMood($str, $mood = null) {
        require_once( JPATH_ROOT .'/components/com_community/models/moods.php' );

        $moodsModel = new CommunityModelMoods();
        $mood = $moodsModel->getMoodString($mood);

        if ($mood !== '') {
            $mood = ' - ' . $mood;
        }

        return $str . $mood;
    }

    static public function converttagtolink($str)
    {
        $parsedMessage = preg_replace('/(^|[^a-z0-9_])#([^\s[:punct:]]+)/i', '$1<a href="'.CRoute::_('index.php?option=com_community&view=frontpage&filter=hashtag&value=$2').'"><strong>#$2</strong></a>', $str);
        return $parsedMessage;
    }

    /**
     * Auto make links from input text
     * @param string $text
     * @return string
     */
    public static function formatLinks($text) {
        $regex = "( )"; /* Force to have space at begining */
        $regex .= "((https?|ftp)\:\/\/)?"; // SCHEME
        $regex .= "([A-Za-z0-9+!*(),;?&=\$_.-]+(\:[A-Za-z0-9+!*(),;?&=\$_.-]+)?@)?"; // User and Pass
        $regex .= "([A-Za-z0-9-.]*)\.([A-Za-z]{2,4})"; // Host or IP
        $regex .= "(\:[0-9]{2,5})?"; // Port
        $regex .= "(\/([A-Za-z0-9+\$_-]\.?)+)*\/?"; // Path
        $regex .= "(\?[A-Za-z+&\$_.-][A-Za-z0-9;:@&%=+\/\$_.-]*)?"; // GET Query
        /* Find all url */
        $regex .= "(#[A-Za-z_.-][A-Za-z0-9+\$_.-]*)?"; // Anchor

        if (preg_match_all("/$regex/", $text, $matches)) {
            foreach ($matches[0] as $match) {
                /* Find and adding protocol if needed */
                if (strpos($match, 'http://') !== false || strpos($match, 'https://') !== false) {
                    $url = $match;
                } else {
                    $url = JUri::getInstance()->getScheme() . '://' . $match;
                }
                $url = trim($url);
                /* Link to open new tab if it's not internal link */
                if (JUri::isInternal($url)) {
                    $text = str_replace($match, '<a href="' . $url . '">' . $match . '</a>', $text);
                } else {
                    $text = str_replace($match, '<a href="' . $url . '" target="_blank" rel="nofollow" >' . $match . '</a>', $text);
                }
            }
        }
        return $text;
    }

    /**
     * Used to compare two string with ascii support.
     * @param $a
     * @param $b
     * @return int
     */
    public static function compareAscii($a, $b){
        $at = iconv('UTF-8', 'ASCII//TRANSLIT', $a);
        $bt = iconv('UTF-8', 'ASCII//TRANSLIT', $b);
        return strcmp($at, $bt);
    }
}
