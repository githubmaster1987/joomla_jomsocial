<?php
/**
* @copyright (C) 2013 iJoomla, Inc. - All rights reserved.
* @license GNU General Public License, version 2 (http://www.gnu.org/licenses/gpl-2.0.html)
* @author iJoomla.com <webmaster@ijoomla.com>
* @url https://www.jomsocial.com/license-agreement
* The PHP code portions are distributed under the GPL license. If not otherwise stated, all images, manuals, cascading style sheets, and included JavaScript *are NOT GPL, and are released under the IJOOMLA Proprietary Use License v1.0
* More info at https://www.jomsocial.com/license-agreement
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

class CBookmarks
{
	var $_bookmarks		= array();
	var $currentURI	= null;
    var $defaultImage = '';
    var $defaultDesc = '';
    var $defaultTitle = '';

	public function __construct( $currentURI, $defaultTitle = '', $defaultDesc='', $defaultImg = '' )
	{
		$this->currentURI	= urlencode( $currentURI );
        $this->defaultImage = urlencode($defaultImg);
        $this->defaultDesc = urlencode($defaultDesc);
        $this->defaultTitle = urlencode($defaultTitle);

		$this->_addDefaultBookmarks();
	}

	public function _addDefaultBookmarks()
	{
		$imageURL	= JURI::root(true) . '/components/com_community/templates/default/images/bookmarks/';

		$this->add( 'Facebook' , 'facebook' , 'http://www.facebook.com/sharer.php?u={uri}' );
		$this->add( 'Google+' , 'google' , 'https://plus.google.com/share?url={uri}' );
		$this->add( 'LinkedIn' , 'linkedin' , 'http://www.linkedin.com/shareArticle?mini=true&url={uri}' );
		$this->add( 'Pintrest' , 'pintrest' , 'http://pinterest.com/pin/create/link/?url={uri}&media={img}&description={desc}' );
		$this->add( 'Tumblr' , 'tumblr' , 'http://tumblr.com/share/link/?url={uri}' );
		$this->add( 'VKontakte' , 'vkontakte' , 'http://vk.com/share.php?url={uri}&title={title}&description={desc}&image={img}' );
		$this->add( 'Weibo' , 'weibo' , 'http://service.weibo.com/share/share.php?url={uri}&title={title}' );
		$this->add( 'Twitter' , 'twitter' , 'http://twitter.com/intent/tweet?url={uri}' );
	}

	public function getTotalBookmarks()
	{
		return count($this->_bookmarks );
	}

	/**
	 * Add sharing sites into bookmarks
	 * @params	string	$providerName	Pass the provider name to be displayed
	 * @params	string	$imageURL	 	 Image that needs to be displayed beside the provider
	 * @params	string	$apiURL			Api URL that JomSocial should link to
	 **/
	public function add( $providerName , $className , $apiURL )
	{
		$apiURL				= CString::str_ireplace( '{uri}' , $this->currentURI , $apiURL );
        $apiURL				= CString::str_ireplace( '{img}' , $this->defaultImage , $apiURL );
        $apiURL				= CString::str_ireplace( '{desc}' , $this->defaultDesc , $apiURL );
        $apiURL				= CString::str_ireplace( '{title}' , $this->defaultTitle , $apiURL );
		$obj				= new stdClass();
		$obj->name			= $providerName;
		$obj->className		= $className;
		$obj->link			= $apiURL;

		$this->_bookmarks[ JString::strtolower( $providerName ) ]	= $obj;
	}

	/**
	 * Remove sharing site from bookmarks
	 * @params	string	$providerName	Pass the provider name to be displayed
	 **/
	public function remove( $providerName )
	{
		$providerName	= JString::strtolower( $providerName );

		if( isset( $this->_bookmarks[ $providerName ] ) )
		{
			unset( $this->_bookmarks[ $providerName ] );
			return true;
		}
		return false;
	}

	public function getBookmarks()
	{
		return $this->_bookmarks;
	}

	public function getHTML()
	{
		$config	= CFactory::getConfig();

		if( $config->get('enablesharethis') )
		{
			$tmpl	= new CTemplate();

			$tmpl->set( 'uri' , $this->currentURI );
			return $tmpl->fetch( 'bookmarks' );
		}
		else
		{
			return '';
		}
	}
}