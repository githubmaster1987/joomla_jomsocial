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

if( !class_exists('Services_JSON') )
{
	require_once (AZRUL_SYSTEM_PATH.'/pc_includes/JSON.php');
}

interface CCommentInterface
{
	static public function sendCommentNotification( CTableWall $wall , $message );
}

class CComment
{
	/**
	 * Return an array of comment data within the content
	 */
	public function getCommentsData($content)
	{
		$json = new Services_JSON();
		$comments = array();

		// See if the content already has commment.
		// If not, create it and add to it
		$regex = '/\<comment\>(.*?)\<\/comment\>/i';

		if (preg_match($regex, $content, $matches)) {
			$comments = $json->decode($matches[1]);
		}

		return $comments;
	}

	/**
	 * Return an array of comment data within the content
	 */
	static public function getRawCommentsData($content)
	{
		$json = new Services_JSON();
		$comments = '';

		// See if the content already has commment.
		// If not, create it and add to it
		$regex = '/\<comment\>(.*?)\<\/comment\>/i';

		if(preg_match($regex, $content, $matches))
		{
			$comments	= '<comment>' . $matches[1] . '</comment>';
		}
		return $comments;
	}

	/**
	 * Append the given comment to the particular content.
	 *
	 * @return, full-text of the content
	 */
	public function add($actor, $comment, $content)
	{
		$commentJson = '';
		$json = new Services_JSON();


		$comments = $this->getCommentsData($content);

		// Once we retrive the comment, we can remove them
		$content = preg_replace('/\<comment\>(.*?)\<\/comment\>/i', '', $content);

		$newComment = new stdClass();
		$date		= new JDate();

		$newComment->creator = $actor;
		$newComment->text 	 = $comment;
		$newComment->date 	 = $date->toUnix();
		$comments[] = $newComment;

		$commentJson = $json->encode($comments);

		$content .= '<comment>'. $commentJson .'</comment>';
		return $content;
	}

	/**
	 * Remove the given indexed comment from the content
	 */
	public function remove($content, $index)
	{
		$comments = $this->getCommentsData($content);
		array_splice($comments, $index, 1);

		// Once we retrive the comment, we can remove them
		$content = preg_replace('/\<comment\>(.*?)\<\/comment\>/i', '', $content);

		$json = new Services_JSON();
		$commentJson = $json->encode($comments);

		$content .= '<comment>'. $commentJson .'</comment>';
		return $content;
	}

	/**
	 * Return html formatted comments given the content
	 */
	public function getHTML($content, $id , $canComment = true )
	{
		$my = CFactory::getUser();
		$comments = $this->getCommentsData($content);
		$html = '';

		if(!empty($comments))
		{
			foreach($comments as $row )
			{
				$html .= $this->renderComment($row);

			}
		}

		// Add the comment box
		if( $my->id != 0 )
		{
			$html .= '<form class="wall-coc-form" action=""><textarea name="comment" style="height:40px;" rows="" cols=""></textarea>';
			$html .= '<div class="wall-coc-form-actions">';
			$html .= '<button class="wall-coc-form-action add button" onclick="joms.comments.add(\''.$id.'\'); return false;" type="submit">' . JText::_('COM_COMMUNITY_COC_ADD') . '</button>';
			$html .= '<button class="wall-coc-form-action cancel button" onclick="joms.comments.cancel(\''.$id.'\'); return false;" type="submit">' . JText::_('COM_COMMUNITY_CANCEL_BUTTON') . '</button>';
			$html .= '<span class="wall-coc-errors" style="margin-left: 5px;"></span>';
			$html .= '</div></form>';

			if( $canComment )
			{
				$html .= '<span class="show-cmt"><a href="javascript:void(0)" onclick="joms.comments.show(\''. $id .'\');">' . JText::_('COM_COMMUNITY_COMMENT') . '</a></span>';
			}

		}

		// We need to hide the unnecessary 'remove' link
		$js = '<script type=\'text/javascript\'>';
		$js .= '/*<![CDATA[*/';
		$js .= 'if(window.joms_my_id == window.joms_user_id) {
				joms.jQuery("a.coc-remove").show();
			}

			if(window.joms_my_id !=0 ){
				joms.jQuery("a.coc-" + window.joms_my_id).show();

		} ';
		$js .= '/*]]>*/';
		$js .= '</script>';
		$html .= $js;

		if(!empty($html))
			$html = '<div id="'.$id.'" class="wall-cocs">'.$html . '</div>';
		return $html;
	}

	public function renderComment( $cmtObj )
	{
		$my = CFactory::getUser();
		$user = CFactory::getUser($cmtObj->creator);

		// Process the text
		//CFactory::load( 'helpers' , 'string' );
		$cmtObj->text = nl2br(CStringHelper::escape($cmtObj->text));

		//format the date
		$dateObject = CTimeHelper::getDate($cmtObj->date);
		$date = $dateObject->Format(JText::_('DATE_FORMAT_LC2'));

		$html = '';
		$html .= '<div class="cComment">';

		//CFactory::load( 'helpers' , 'user' );
		$html .= CUserHelper::getThumb( $user->id , 'wall-coc-avatar' );

		//CFactory::load( 'helpers' , 'string' );
		$html	= CStringHelper::replaceThumbnails($html);

		$html .= '<a class="wall-coc-author" href="' . CRoute::_('index.php?option=com_community&view=profile&userid='.$user->id) . '">' . $user->getDisplayName() . '</a> ';
		$html .= JText::sprintf('COM_COMMUNITY_COMMENT_POSTED_ON', '<span class="wall-coc-date">' . $date  . '</span>' );

		//CFactory::load( 'helpers' , 'owner' );

		if ($my->id==$user->id || COwnerHelper::isCommunityAdmin() )
			$html .= ' | <a class="coc-remove coc-'.$cmtObj->creator.'" onclick="joms.comments.remove(this);" href="javascript:void(0)">' . JText::_('COM_COMMUNITY_REMOVE') . '</a>';

		$html .= '<p>' . $cmtObj->text . '</p>';
		$html .= '</div>';
		return $html;
	}

	// remove the comment data from the given content
	static public function stripCommentData($content)
	{
		// Once we retrive the comment, we can remove them
		$content = preg_replace('/\<comment\>(.*?)\<\/comment\>/i', '', $content);
		return $content;
	}

	public function getCommentHandler( $type )
	{
		jimport( 'joomla.filesystem.file' );

		if( $type == 'user' )
		{
			$type	= 'profile';
		}
		$path	= JPATH_ROOT .'/components/com_community/libraries' .'/'. JString::strtolower( $type ) . '.php';

		if( !JFile::exists( $path ) )
		{
			return false;
		}
		require_once( $path );
		$class	= 'C' . ucfirst( $type );

		// Revert to the default object
		if( !class_exists( $class ) )
		{
			 $class	= 'CProfile';
		}
 		$obj	= new $class();

		if( $obj instanceof CCommentInterface )
		{
			return $obj;
		}
		return false;
	}
}
