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

/** detect and display facebook language **/
if (!defined('FACEBOOK_LANG_AVAILABLE')) {
define('FACEBOOK_LANG_AVAILABLE', 1);
}

$lang = JFactory::getLanguage();
$currentLang =  $lang->get('tag');

$fbLang =   explode(',', trim(FACEBOOK_LANGUAGE) );
$currentLang = str_replace('-','_',$currentLang);
$fbLangScript = '<script src="http://connect.facebook.net/en_GB/all.js" type="text/javascript"></script>';

if(in_array($currentLang,$fbLang)==FACEBOOK_LANG_AVAILABLE){
    $fbLangScript = '<script src="http://connect.facebook.net/'.$currentLang.'/all.js" type="text/javascript"></script>';
}

$fbLangScript = CUrlHelper::httpsURI($fbLangScript);
?>

<script>(function(d) {
    d.getElementById('fb-root') || d.write('<div id="fb-root"></div>'); // fix multiple #fb-root in a document
}(document));</script>
<?php echo $fbLangScript; ?>
<script type="text/javascript">
function jomFbButtonInit(){
    FB.init({
        appId: '<?php echo $config->get('fbconnectkey');?>',
        status: true,
        cookie: true,
        oauth: true,
        xfbml: true
    });
}

if( typeof window.FB != 'undefined' ) {
    jomFbButtonInit();
} else {
    window.fbAsyncInit = jomFbButtonInit;
}
<?php

    $fbScope = array('email');
    $config = CFactory::getConfig();
    if($config->get('fbconnectupdatestatus')){
        $fbScope[] = 'user_status';
        $fbScope[] = 'user_posts';
    }
    if($config->get('fbconnectpoststatus')){
        $fbScope[] = 'publish_actions';
    }
    if($config->get('fbsignupimport') || $config->get('fbloginimportprofile')){
        $fbScope[] = 'user_birthday';
        $fbScope[] = 'user_about_me';
        $fbScope[] = 'user_website';
        $fbScope[] = 'user_education_history';
    }
?>
</script>

<!-- validate w3c -->
<div class="joms-fb-login-button" data-scope="<?php echo implode($fbScope, ',')?>"><?php echo JText::_('COM_COMMUNITY_SIGN_IN_WITH_FACEBOOK');?></div>
<script>(function(d) {
    var o = d.getElementsByClassName('joms-fb-login-button')[0];
    o.setAttribute('onlogin', 'joms.api.fbcUpdate();');
    o.setAttribute('class', 'fb-login-button');
}(document));</script>
