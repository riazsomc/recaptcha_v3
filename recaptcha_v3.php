<?php
// File: recaptcha_v3.php

class recaptcha_v3 extends rcube_plugin
{
    public $task = 'login';

    private $recaptcha_site_key;
    private $recaptcha_secret_key;

    function init()
    {
        $this->load_config();

        // Load site key and secret key from config
        $this->recaptcha_site_key = rcmail::get_instance()->config->get('recaptcha_site_key');
        $this->recaptcha_secret_key = rcmail::get_instance()->config->get('recaptcha_secret_key');

        // Add JavaScript to the login page
        $this->add_hook('login_form', array($this, 'add_recaptcha_script'));
        $this->add_hook('authenticate', array($this, 'verify_recaptcha'));
    }

    /**
     * Adds the reCAPTCHA v3 JavaScript to the login form.
     */
    function add_recaptcha_script($args)
    {
        if ($this->recaptcha_site_key) {
            $args['content'] .= '<script src="https://www.google.com/recaptcha/api.js?render=' . $this->recaptcha_site_key . '"></script>';
            $args['content'] .= '
                <script>
                    grecaptcha.ready(function() {
                        grecaptcha.execute("' . $this->recaptcha_site_key . '", { action: "login" }).then(function(token) {
                            var recaptchaInput = document.createElement("input");
                            recaptchaInput.setAttribute("type", "hidden");
                            recaptchaInput.setAttribute("name", "g-recaptcha-response");
                            recaptchaInput.setAttribute("value", token);
                            document.forms[0].appendChild(recaptchaInput);
                        });
                    });
                </script>';
        }
        return $args;
    }

    /**
     * Verifies the reCAPTCHA response during authentication.
     */
    function verify_recaptcha($args)
    {
        $recaptcha_response = rcube_utils::get_input_value('g-recaptcha-response', rcube_utils::INPUT_POST);

        if ($this->recaptcha_secret_key && $recaptcha_response) {
            $verify_response = $this->recaptcha_verify($recaptcha_response);
            if (!$verify_response || $verify_response->score < 0.5) {
                // Reject login if score is below threshold
                rcube::raise_error(array('code' => 403, 'type' => 'php', 'file' => __FILE__,
                    'line' => __LINE__, 'message' => "reCAPTCHA verification failed"), true, false);
                $args['abort'] = true;
            }
        }

        return $args;
    }

    /**
     * Sends the reCAPTCHA response to Google for verification.
     */
    private function recaptcha_verify($response)
    {
        $url = 'https://www.google.com/recaptcha/api/siteverify';
        $data = array(
            'secret' => $this->recaptcha_secret_key,
            'response' => $response,
            'remoteip' => $_SERVER['REMOTE_ADDR'],
        );

        $options = array(
            'http' => array(
                'header' => "Content-type: application/x-www-form-urlencoded\r\n",
                'method' => 'POST',
                'content' => http_build_query($data),
            ),
        );

        $context = stream_context_create($options);
        $result = file_get_contents($url, false, $context);
        return json_decode($result);
    }
}
