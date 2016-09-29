<?php

/**
 * @copyright (C) 2013 iJoomla, Inc. - All rights reserved.
 * @license GNU General Public License, version 2 (http://www.gnu.org/licenses/gpl-2.0.html)
 * @author iJoomla.com <webmaster@ijoomla.com>
 * @url https://www.jomsocial.com/license-agreement
 * The PHP code portions are distributed under the GPL license. If not otherwise stated, all images, manuals, cascading style sheets, and included JavaScript *are NOT GPL, and are released under the IJOOMLA Proprietary Use License v1.0
 * More info at https://www.jomsocial.com/license-agreement
 */
// no direct access
defined('_JEXEC') or die('Restricted access');

if (!class_exists('CParsers')) {

    /**
     * Parser abstract class
     * @uses Used to get parser and provide abstract structure
     */
    abstract class CParsers extends cobject {

        /**
         *
         * @param type $name
         * @param type $data
         * @return boolean|\Parser Object
         */
        public static function getParser($name, $data = array()) {
            $parserFile = __DIR__ . '/parsers/' . $name . '.php';
            if (JFile::exists($parserFile)) {
                $className = 'CParser' . ucfirst($name);
                if (class_exists($className)) {
                    $class = new $className($data);
                    return $class;
                }
                return false;
            }
        }

        public abstract function extract();

        /**
         * Get array of url in input content string
         * @param string $content
         * @return array
         */
        public static function getUrls($content) {
            $regex = "((https?|ftp)\:\/\/)?"; // SCHEME
            $regex .= "([A-Za-z0-9+!*(),;?&=\$_.-]+(\:[A-Za-z0-9+!*(),;?&=\$_.-]+)?@)?"; // User and Pass
            $regex .= "([A-Za-z0-9-.]*)\.([A-Za-z]{2,18})"; // Host or IP
            $regex .= "(\:[0-9]{2,5})?"; // Port
            $regex .= "(\/([A-Za-z0-9+\$_-]\.?)+)*\/?"; // Path
            $regex .= "(\?[A-Za-z+&\$_.-][A-Za-z0-9;:@&%=+\/\$_.-]*)?"; // GET Query
            $regex .= "(#[A-Za-z_.-][A-Za-z0-9+\$_.-]*)?"; // Anchor
            //$return = preg_match_all('#\bhttps?://[^\s()<>]+(?:\([\w\d]+\)|([^[:punct:]\s]|/))#', $this->get('content'), $matchs);
            $return = preg_match_all("/$regex/", $content, $matchs);

            if ($return !== false) {
                return array_unique($matchs[0]);
            }
            return array();
        }

        /**
         * Extract url from input content and do fetch link
         * @param string $content
         * @return boolean|JRegistry
         */
        public static function linkFetch($content)
        {
            $urls = self::getUrls($content);
            /**
             * Crawle data
             * We only work with first url
             */

            $config = CFactory::getConfig();

            if (count($urls) > 0) {
                $url = array_shift($urls);

                if ($config->get('enable_embedly') && $config->get('embedly_apikey')) {
                    //this is using oembed
                    $api = new CEmbedly(
                        array(
                            'user_agent' => 'Mozilla/5.0 (compatible; embedly/example-app; support@embed.ly)',
                            'key' => $config->get('embedly_apikey')
                        )
                    );

                    $res = $api->oembed($url);

                    $data = new JRegistry();
                    switch ($res->type) {
                        case 'photo':
                            $data = $data->loadArray(
                                array(
                                    'type' => isset($res->type) ? $res->type : '',
                                    'title' => isset($res->title) ? $res->title : preg_replace( '/^.*\/([^\/]+)$/i', '$1', $url),
                                    'image' => isset($res->thumbnail_url) ? array($res->thumbnail_url) : array($url),
                                    'url' => isset($res->url) ? $res->url : '',
                                    'description' => isset($res->description) ? $res->description : ''
                                )
                            );
                            break;
                        case 'link':
                        case 'rich':
                        case 'video':
                            $data = $data->loadArray(
                                array(
                                    'type' => isset($res->type) ? $res->type : '',
                                    'title' => isset($res->title) ? $res->title : '',
                                    'image' => isset($res->thumbnail_url) ? array($res->thumbnail_url) : '',
                                    'url' => isset($res->url) ? $res->url : $url,
                                    'description' => isset($res->description) ? $res->description : ''
                                )
                            );
                            break;
                        case 'error':
                        default:
                        $crawl = CCrawler::getCrawler();
                        $data = $crawl->crawl('GET', $url);
                        $data = $data->parse();
                    }
                } else {

                    // Check for image urls.
                    if ( preg_match('/\.(gif|jpg|jpeg|png)$/i', $url) ) {
                        $data = new JRegistry();
                        $data = $data->loadArray(
                            array(
                                'type' => 'photo',
                                'title' => preg_replace( '/^.*\/([^\/]+)$/i', '$1', $url),
                                'image' => array($url),
                                'url' => $url,
                                'description' => ''
                            )
                        );

                    // Everyhing else.
                    } else {
                        $crawl = CCrawler::getCrawler();
                        $data = $crawl->crawl('GET', $url);
                        $data = $data->parse();
                        if (isset($data)) {
                            $data->set('url',$url);
                        }
                    }
                }
                return $data;
            }
            return false;
        }
    }

}
