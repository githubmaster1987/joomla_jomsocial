<?php
/**
* @copyright (C) 2013 iJoomla, Inc. - All rights reserved.
* @license GNU General Public License, version 2 (http://www.gnu.org/licenses/gpl-2.0.html)
* @author iJoomla.com <webmaster@ijoomla.com>
* @url https://www.jomsocial.com/license-agreement
* The PHP code portions are distributed under the GPL license. If not otherwise stated, all images, manuals, cascading style sheets, and included JavaScript *are NOT GPL, and are released under the IJOOMLA Proprietary Use License v1.0
* More info at https://www.jomsocial.com/license-agreement
*/
// Disallow direct access to this file
defined('_JEXEC') or die('Restricted access');

Class CRecaptchaHelper{

    public  $enabled = false;
    public  $ip;

    private $theme;

    private $privateKey;
    private $publicKey;

    private $apiUrl;
    private $verifyUrl;

    /*
     * Load config data and object vars
     */
    public function __construct()
    {
        // Config data
        $config             = CFactory::getConfig();
        $this->enabled      = $config->get('nocaptcha', false);
        $this->privateKey   = $config->get('nocaptchaprivate');
        $this->publicKey    = $config->get('nocaptchapublic');
        $this->theme        = $config->get('nocaptchatheme');
        $this->apiUrl       = $config->get('recaptcha_server');
        $this->verifyUrl    = $config->get('recaptcha_server_verify');

        // Grab the IP, remember load balancers
        // @todo is there a framework way to do it?
        $this->ip = isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR'];

        // If any of the vital vars is missing, disable the whole thing
        if(!$this->privateKey || !$this->publicKey || !$this->apiUrl || !$this->verifyUrl) {
            $this->enabled = false;
        }
    }

    /*
     * Return the Recaptcha HTML if enabled
     */
    public function html(){
        // start output buffer, if recaptcha is enabled add HTML and JS to the buffer
        ob_start();

        if($this->enabled) { ?>
            <div id="joms-recaptcha"></div>
            <script type="text/javascript">
                var jomsRecaptchaCallback = function() {
                    grecaptcha.render("joms-recaptcha", {
                        "sitekey" : "<?php echo $this->publicKey;?>",
                        "theme" : "<?php echo $this->theme;?>",
                        })
                };
            </script>
            <script src="<?php echo $this->apiUrl;?>?onload=jomsRecaptchaCallback&render=explicit&hl=<?php echo JFactory::getLanguage()->getTag() ?>" async defer></script>
            <?php
        }

        // get the contents of te buffer and return it
        $html = ob_get_contents();
        ob_end_clean();
        return $html;
    }

    /*
     * Send a verification request
     */
    public function verify ()
    {
        // if Recaptcha is not enabled, return true
        if(!$this->enabled) return true;

        // get the Recaptcha response from the form data
        $response = JFactory::getApplication()->input->get('g-recaptcha-response');
        if(!$response) return false;

        // send it to verification server for confirmation
        $http = new JHttp();

        $result = $http->post(
            $this->verifyUrl,
            array (
                'secret' => $this->privateKey,
                'remoteip' => $this->ip,
                'response' => $response,
            )
        );

        $result = json_decode($result->body);
        return ($result->success === true) ? true : false;
    }
}
