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

require_once( JPATH_ROOT . '/components/com_community/libraries/core.php');

if (!class_exists('plgCommunityTwitter')) {

    class plgCommunityTwitter extends CApplications {

        public $name = "Twitter";
        public $_name = 'twitter';
        public $_path = '';
        public $timelines = array(
            'public' => '1.1/statuses/public_timeline.json',
            'friends' => '1.1/statuses/friends_timeline.json',
            'home' => '1.1/statuses/home_timeline.json',
            'user' => '1.1/statuses/user_timeline.json',
            'update' => '1.1/statuses/update.json'
        );
        public $users = array(
            'show' => '1.1/users/show.json'
        );

        static public function getConsumer() {
            static $consumer = null;

            if (is_null($consumer)) {
                $my = CFactory::getUser();
                //$consumer = new Zend_Oauth_Consumer(self::getConfiguration());
                $configuration = self::getConfiguration();
                //$consumer = new TwitterOAuth( $configuration['consumerKey'],$configuration['consumerSecret'] );
                $consumer = new tmhOAuth(self::getConfiguration());
            }

            return $consumer;
        }

        static public function getConfiguration($userid = '') {
            static $configuration = null;

            if (is_null($configuration)) {
                $plugin = JPluginHelper::getPlugin('community', 'twitter');
                $params = new CParameter($plugin->params);
                $my = CFactory::getUser($userid);

                $oauth = JTable::getInstance('Oauth', 'CTable');
                $loaded = $oauth->load($my->id, 'twitter');
                $accesstoken = unserialize($oauth->accesstoken);

                $consumer_key = $params->get('consumerKey');
                $consumer_secret = $params->get('consumerSecret');

                $configuration = array(
                    'consumer_key' => $consumer_key,
                    'consumer_secret' => $consumer_secret,
                    'user_token' => $accesstoken['oauth_token'],
                    'user_secret' => $accesstoken['oauth_token_secret'],
                    'bearer'          => base64_encode($consumer_key.':'.$consumer_secret),
                    'curl_ssl_verifypeer' => true
                );
            }
            return $configuration;
        }

        public function onProfileDisplay() {
            JPlugin::loadLanguage('plg_community_twitter', JPATH_ADMINISTRATOR);

            $user = CFactory::getRequestUser();

            $document = JFactory::getDocument();
            $css = JURI::base() . 'plugins/community/twitter/twitter/style.css';
            $document->addStyleSheet($css);

            $my = CFactory::getUser();
            $oauth = JTable::getInstance('Oauth', 'CTable');

            if (!$oauth->load($user->id, 'twitter')) {
                return JText::_('PLG_TWITTER_NOT_SET');
            }

            return $this->_getTwitterHTML($user->id);
        }

        protected function _getTwitterHTML($userId) {
            $this->loadUserParams();

            $my = CFactory::getUser($userId);
            $this->userparams = $my->getAppParams($this->_name);

            $showFriends = $this->userparams->get('showFriends', false);
            $oauth = JTable::getInstance('Oauth', 'CTable');
            $loaded = $oauth->load($my->id, 'twitter');
            $accesstoken = unserialize($oauth->accesstoken);

            ob_start();

            if ($loaded && !is_null($accesstoken) && !empty($accesstoken)) {

                $client = new tmhOAuth(self::getConfiguration($my->id));

                $timeline = $showFriends ? 'home' : 'user';
                $count = $this->userparams->get('count', 5);
                //echo $timeline;die();
                // get user info/
                $code = $client->request('GET', $client->url($this->users['show']), array(
                    'screen_name' => $accesstoken['screen_name']
                ));

                if ($code == 200) {
                    $userinfo = (json_decode($client->response['response']));

                    // get tweets
                    $code = $client->request('GET', $client->url($this->timelines[$timeline]), array(
                        'count' => $count,
                        'screen_name' => $userinfo->screen_name
                    ));
                    if ($code == 200) {
                        $data = (json_decode($client->response['response']));
                    } else {
                        $data = null;
                    }
                } else {
                    $userinfo = null;
                }

                if (!$userinfo) {
                    ?>
                    <div><?php echo JText::_('PLG_TWITTER_UNABLE_TO_CONTACT_SERVER'); ?></div>
                    <?php
                } else {
                    ?>
                    <div id="application-twitter">

                            <div class="joms-stream__header">
                                <div class="joms-avatar--stream">
                                    <a href="http://twitter.com/<?php echo $userinfo->screen_name; ?>" target="blank" class="joms-avatar">
                                    <img src="<?php echo $userinfo->profile_image_url; ?>" alt="<?php echo $userinfo->screen_name; ?>"/>
                                    </a>
                                </div>
                                <!--start twitter post-->
                                <div class="joms-stream__meta ">
                                    <a href="http://twitter.com/<?php echo $userinfo->screen_name; ?>" target="blank" class="cThumb-Title"><?php echo $userinfo->name; ?></a>
                                    <span class="joms-block"><?php echo $userinfo->statuses_count; ?> tweets, <?php echo $userinfo->followers_count; ?> followers</span>
                                    <small><?php echo $userinfo->description; ?></small>
                                </div>
                                <!--end twitter post-->
                            </div>
                            <div class="joms-gap"></div>
                            <?php
                            if (is_object($data)) {
                                if (isset($data->error)) {
                                    echo $data->error;
                                }
                            } else {
                                //CFactory::load( 'helpers' , 'linkgenerator' );

                                for ($i = 0; $i < count($data); $i++) {
                                    $tweet = $data[$i];
                                    //$date   = cGetDate($tweet->created_at); //JDate::getInstance( $tweet->created_at );
                                    $date = CTimeHelper::getDate($tweet->created_at);
                                    $text = CLinkGeneratorHelper::replaceURL($tweet->text, true, true);
                                    $text = $this->replaceAliasURL($text);
                                    ?>
                                    <div class="joms-stream__header">
                                        <?php if (($i == 0 && $showFriends) || $showFriends) { ?>
                                            <div class="joms-avatar--stream">
                                                <a href="http://twitter.com/<?php echo $tweet->user->screen_name; ?>" target="blank" ><img src="<?php echo $tweet->user->profile_image_url; ?>" alt="<?php echo $tweet->user->screen_name; ?>"></a>
                                            </div>
                                        <?php } ?>
                                        <div class="joms-stream__meta">
                                            <?php echo CStringHelper::formatLinks($text); ?>
                                            <div class="small joms-text--light"><?php echo $date->format(JText::_('DATE_FORMAT_LC2')); ?></div>
                                        </div>
                                        <!--twitter avatar-->
                                    </div>
                                    <div class="joms-gap"></div>
                                    <?php
                                }
                            }
                            ?>

                    </div>

                    <?php
                }
            } else {
                ?>
                <!-- <div class="icon-nopost">
                        <img src="<?php echo JURI::base() ?>components/com_community/assets/error.gif" alt="" />
                </div> -->
                <div class="content-nopost">
                    <?php echo JText::_('PLG_TWITTER_NOT_UPDATES'); ?>
                </div>
                <?php
            }
            $html = ob_get_contents();
            ob_end_clean();

            return $html;
        }

        static public function replaceAliasURL($message) {
            $pattern = '/@(("(.*)")|([A-Z0-9][A-Z0-9_-]+)([A-Z0-9][A-Z0-9_-]+))/i';

            preg_match_all($pattern, $message, $matches);

            if (isset($matches[0]) && !empty($matches[0])) {
                //CFactory::load('helpers', 'user');
                //CFactory::load('helpers', 'linkgenerator');

                $usernames = $matches[0];

                for ($i = 0; $i < count($usernames); $i++) {
                    $username = $usernames[$i];
                    $username = JString::str_ireplace('"', '', $username);
                    $username = explode('@', $username);
                    $username = $username[1];

                    $message = JString::str_ireplace($username, '<a href="http://twitter.com/' . $username . '" target="_blank" rel="nofollow">' . $username . '</a>', $message);
                }
            }

            return $message;
        }

        function onProfileStatusUpdate($userid, $old_status, $new_status) {
            $my = CFactory::getUser($userid);
            $this->userparams = $my->getAppParams($this->_name);
            $updateTwitter = $this->userparams->get('updateTwitter', 0);
            if ($updateTwitter) {
                $plugin = JPluginHelper::getPlugin('community', 'twitter');
                $params = new CParameter($plugin->params);
                $my = CFactory::getUser($userid);
                $oauth = JTable::getInstance('Oauth', 'CTable');
                $loaded = $oauth->load($my->id, 'twitter');
                $accesstoken = unserialize($oauth->accesstoken);
                if ($loaded && !is_null($accesstoken) && !empty($accesstoken)) {
                    $client = new tmhOAuth(self::getConfiguration());

                    $code = $client->request('POST', $client->url($this->timelines['update']), array(
                        'status' => $new_status
                    ));
                }
            }
        }

    }

}

