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

/**
 * Class exists checking
 */
if (!class_exists('CParserMetas')) {

    /* Use 3rd Simple HTML DOM */
    require_once JPATH_ROOT . '/components/com_community/libraries/vendor/simple_html_dom.php';

    /**
     * Extract data in <head>
     */
    class CParserMetas extends CParsers {

        /**
         *
         * @var SimpleHtmlDOM
         */
        private $_dom;

        /**
         *
         * @var array
         */
        private $_domBlocks = array();

        /**
         * Parsed properties
         * @var JRegistry
         */
        private $_extracted;

        /**
         *
         * @param type $properties
         */
        public function __construct($properties = null) {
            parent::__construct($properties);
            $this->_extracted = new JRegistry();
            $this->_init();
        }

        /**
         *
         * @return \CParserMetas
         */
        protected function _init() {
            $content = $this->get('content');
            $this->_dom = str_get_html($content);

            if ($this->_dom) {
                $this->_domBlocks['head'] = $this->_dom->find('head', 0);
                $this->_domBlocks['body'] = $this->_dom->find('body', 0);
                $this->_manualParse = false;
            } else {
                $this->_manualParse = true;
                $this->_parseHead($content);
                $this->_parseBody($content);
            }

            $this->_extracted->def('type', 'website');
            return $this;
        }

        /**
         *
         * @param type $name
         * @param type $array
         * @return \CParserMetas
         */
        private function _addArray($name, $array) {
            if (!is_array($array))
                $array = array($array);
            $data = $this->_extracted->get($name, array());
            $data = (is_array($data)) ? array_unique(array_merge_recursive($data, $array)) : $array;
            $this->_add($name, $data);
            return $this;
        }

        /**
         *
         * @param type $name
         * @param type $value
         * @return \CParserMetas
         */
        private function _add($name, $value) {
            $this->_extracted->set($name, $value);
            return $this;
        }

        /**
         * Extract meta elements
         * @return \CParserMetas
         */
        private function _extractMeta() {
            /**
             * Init default values
             */
            $title = $this->_domBlocks['head']->find('title', 0);
            if ($title) {
                $this->_extracted->def('title', $title->plaintext);
            }

            $meta = $this->_domBlocks['head']->find('meta');

            /* Process all meta elements */
            foreach ($meta as $element) {
                $attributes = $element->attr;

                /**
                 * Opengraph
                 * @todo We can improve to get more opengraph information
                 */
                if (isset($attributes['property'])) {
                    /* We need to work with same specific property */
                    switch ($attributes['property']) {
                        case 'og:image':
                            $this->_addArray('image', $attributes['content']);
                            break;
                        case 'og:title':
                            $this->_add('title', $attributes['content']);
                            break;
                        case 'og:description':
                            $this->_add('description', $attributes['content']);
                            break;
                        /* add more opengraph here if need */
                        default:
                            $this->_add($attributes['property'], $attributes['content']);
                            break;
                    }
                } else {
                    /* meta with attribute "name" */
                    if (isset($attributes['name'])) {
                        if (isset($attributes['content']))
                            $this->_add($attributes['name'], $attributes['content']);
                        if (isset($attributes['value']))
                            $this->_add($attributes['name'], $attributes['value']);
                    } elseif (isset($attributes['http-equiv'])) { /* meta with attribute "http-equiv" */
                        $this->_add($attributes['http-equiv'], $attributes['content']);
                    }
                    if (isset($attributes['itemprop'])) {
                        switch ($attributes['itemprop']) {
                            case 'image':
                                $this->_addArray('image', $attributes['content']);
                                break;
                        }
                    }
                    /**
                     * Put your extend parsing here
                     */
                }
            }
            return $this;
        }

        /**
         * Extract elements in body
         * @return \CParserMetas
         */
        private function _extractBody() {
            if (isset($this->_domBlocks['body'])) {
                $image = $this->_domBlocks['body']->find('img');
                foreach ($image as $element) {
                    $attributes = $element->attr;
                    if (isset($attributes['src'])) {
                        $this->_addArray('image', $attributes['src']);
                    }
                }
            }

            /**
             * @todo extract css with background have image
             * @todo extract a href with image link
             */
            return $this;
        }

        /**
         * @todo extract more information by know well about html standard
         * @return \JObject
         */
        public function extract() {
            if ( !$this->_manualParse ) {
                $this->_extractMeta()->_extractBody();
            }

            return $this->_extracted;
        }

        /**
         *
         * @return \CParserMetas
         */
        protected function _parseHead($content) {
            preg_match('/<head[^>]*>.*<\/head>/is', $content, $head);
            $head = count($head) > 0 ? $head[0] : '';

            // get title
            preg_match('/<title[^>]*>(.*)<\/title>/is', $head, $title);
            if (count($title) > 1) {
                $this->_extracted->def('title', utf8_encode($title[1]));
            }

            // get metas
            preg_match_all('/<meta[^>]*>/is', $head, $metas);
            if (count($metas) > 0) {
                $metas = $metas[0];

                // parse metas
                for ($i = 0, $len = count($metas); $i < $len; $i++) {
                    preg_match_all('/\s([a-z-]+)\s*=\s*["\']([^"\']+)["\']/is', $metas[$i], $matches);
                    if (count($matches[1]) > 0) {
                        $meta = array();
                        for ($j = 0, $mlen = count($matches[1]); $j < $mlen; $j++) {
                            $meta[ strtolower( $matches[1][$j] ) ] = utf8_encode($matches[2][$j]);
                        }
                        $metas[$i] = $meta;
                    } else {
                        unset($metas[$i]);
                    }
                }

                // assign metas
                foreach ($metas as $meta) {
                    if (isset($meta['property'])) {
                        if ($meta['property'] == 'og:image') {
                            $this->_addArray('image', $meta['content']);
                        } else if ($meta['property'] == 'og:title') {
                            $this->_add('title', $meta['content']);
                        } else if ($meta['property'] == 'og:description') {
                            $this->_add('description', $meta['content']);
                        } else {
                            $this->_add($meta['property'], $meta['content']);
                        }
                    } else if (isset($meta['name'])) {
                        if (isset($meta['content'])) {
                            $this->_add($meta['name'], $meta['content']);
                        } else if (isset($meta['value'])) {
                            $this->_add($meta['name'], $meta['value']);
                        }
                    } else if (isset($meta['http-equiv'])) {
                        $this->_add($meta['http-equiv'], $meta['content']);
                    } else if (isset($meta['itemprop'])) {
                        if ($meta['itemprop'] == 'image') {
                            $this->_addArray('image', $meta['content']);
                        }
                    }
                }
            }

            return $this;
        }

        /**
         *
         * @return \CParserMetas
         */
        protected function _parseBody($content) {
            return $this;
        }

    }

}
