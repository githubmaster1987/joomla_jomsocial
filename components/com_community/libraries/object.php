<?php

/**
 * @copyright (C) 2015 iJoomla, Inc. - All rights reserved.
 * @license GNU General Public License, version 2 (http://www.gnu.org/licenses/gpl-2.0.html)
 * @author iJoomla.com <webmaster@ijoomla.com>
 * @url https://www.jomsocial.com/license-agreement
 * The PHP code portions are distributed under the GPL license. If not otherwise stated, all images, manuals, cascading style sheets, and included JavaScript *are NOT GPL, and are released under the IJOOMLA Proprietary Use License v1.0
 * More info at https://www.jomsocial.com/license-agreement
 */
defined('_JEXEC') or die('Restricted access');

if (!class_exists('CObject')) {

    // this class is used to replace all the parts of jomsocial that needs getter and setter method from deprecated jobject
    class CObject{
        public function __construct($properties = null)
        {
            if ($properties !== null)
            {
                $this->setProperties($properties);
            }
        }

        public function __toString()
        {
            return get_class($this);
        }

        public function def($property, $default = null)
        {
            $value = $this->get($property, $default);

            return $this->set($property, $value);
        }

        public function get($property, $default = null)
        {
            if (isset($this->$property))
            {
                return $this->$property;
            }

            return $default;
        }

        public function getProperties($public = true)
        {
            $vars = get_object_vars($this);

            if ($public)
            {
                foreach ($vars as $key => $value)
                {
                    if ('_' == substr($key, 0, 1))
                    {
                        unset($vars[$key]);
                    }
                }
            }

            return $vars;
        }

        public function set($property, $value = null)
        {
            $previous = isset($this->$property) ? $this->$property : null;
            $this->$property = $value;

            return $previous;
        }


        public function setProperties($properties)
        {
            if (is_array($properties) || is_object($properties))
            {
                foreach ((array) $properties as $k => $v)
                {
                    // Use the set function which might be overridden.
                    $this->set($k, $v);
                }

                return true;
            }

            return false;
        }
    }

}