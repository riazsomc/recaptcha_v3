<?php
// File: recaptcha_v3.php

class recaptcha_v3 extends rcube_plugin
{
    public $task = 'login';

    private $recaptcha_site_key;
    private $recaptcha_secret_key;

    public function init()
    {
        $this->rc = rcmail::get_instance();
        $this->load_config('config.inc.php');
        $this->load_config();

        $this->recaptcha_site_key = $this->rc->config->get('recaptcha_site_key');
        $this->recaptcha_secret_key = $this->rc->config->get('recaptcha_secret_key');


        // Replace 'login_form' with 'template_object_loginform'
        $this->add_hook('template_object_loginform', array($this, 'add_recaptcha_script'));
        $this->add_hook('authenticate', array($this, 'verify_recaptcha'));
    }

    /**
     * Adds the reCAPTCHA v3 JavaScript to the login form.
     */
    public function add_recaptcha_script($args)
    {
        // Inject the reCAPTCHA v3 JavaScript
        if ($this->rc->config->get('recaptcha_site_key')) {
            $args['content'] .= '<script src="https://www.google.com/recaptcha/api.js?render=' . $this->rc->config->get('recaptcha_site_key') . '"></script>';
            $args['content'] .= '
                <script>
                    grecaptcha.ready(function() {
                        grecaptcha.execute("' . $this->rc->config->get('recaptcha_site_key') . '", { action: "login" }).then(function(token) {
                            var recaptchaInput = document.createElement("input");
                            recaptchaInput.setAttribute("type", "hidden");
                            recaptchaInput.setAttribute("name", "g-recaptcha-response");
                            recaptchaInput.setAttribute("value", token);
                            document.forms[0].appendChild(recaptchaInput);
                        });
                    });
                </script>';
        } else {
            $args['content'] .= '<p style="color: red;">Missing reCAPTCHA site key in configuration!</p>';
        }

        return $args;
    }

    /**
     * Verifies the reCAPTCHA response during authentication.
     */
    function verify_recaptcha($args)
    {

        $recaptcha_response = rcube_utils::get_input_value('g-recaptcha-response', rcube_utils::INPUT_POST);

        error_log("Received reCAPTCHA token: " . $recaptcha_response);

        if ($this->recaptcha_secret_key && $recaptcha_response) {
            $verify_response = $this->recaptcha_verify($recaptcha_response);

            error_log("Google reCAPTCHA API response: " . json_encode($verify_response));

            if (!$verify_response || !isset($verify_response->success) || !$verify_response->success || $verify_response->score < 0.5) {
                rcube::raise_error(array(
                    'code' => 403,
                    'type' => 'php',
                    'file' => __FILE__,
                    'line' => __LINE__,
                    'message' => "reCAPTCHA verification failed"
                ), true, false);

                $args['abort'] = true; // Abort the login process
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
        $result = @file_get_contents($url, false, $context); // Use @ to suppress warnings
        if ($result === false) {
            return null; // Handle API request failure gracefully
        }

        return json_decode($result);
    }

}
