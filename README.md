# reCAPTCHA v3 Plugin for Roundcube

## Description
This plugin integrates Google reCAPTCHA v3 into the Roundcube webmail login page to enhance security by preventing automated login attempts (bots). It uses reCAPTCHA's action-based scoring to assess the likelihood of a request being legitimate, blocking logins that do not meet the specified score threshold.

---

## Features
- Adds reCAPTCHA v3 JavaScript to the login page.
- Validates reCAPTCHA tokens on the server-side.
- Configurable minimum score threshold for enhanced security.
- Logs all reCAPTCHA interactions for monitoring and debugging.

---

## Installation

### 1. Prerequisites
- Ensure you have a Google reCAPTCHA v3 site key and secret key. If not, [register your site](https://www.google.com/recaptcha/admin/create).

### 2. Installation Steps
1. **Copy the Plugin Files:**
   Place the plugin in the Roundcube `plugins` directory:
   ```bash
   cp -r recaptcha_v3 /path/to/roundcube/plugins/
   ```

2. **Copy Configuration File:**
   Copy the default configuration file and edit it:
   ```bash
   cp recaptcha_v3/config.inc.php.dist recaptcha_v3/config.inc.php
   ```

3. **Enable the Plugin:**
   Edit the Roundcube configuration file (`config/config.inc.php`) and add the plugin:
   ```php
   $config['plugins'] = array('recaptcha_v3');
   ```

4. **Configure the Plugin:**
   Edit the plugin configuration file (`recaptcha_v3/config.inc.php`) and set your reCAPTCHA site key, secret key, and minimum score threshold:
   ```php
   $config['recaptcha_site_key'] = 'your-site-key';
   $config['recaptcha_secret_key'] = 'your-secret-key';
   $config['recaptcha_min_score'] = 0.5; // Adjust as needed
   ```

5. **Clear Roundcube Cache:**
   Clear Roundcube’s cache to ensure the plugin is loaded:
   ```bash
   sudo rm -rf /path/to/roundcube/temp/*
   ```

---

## Usage
1. Navigate to the Roundcube login page.
2. The reCAPTCHA v3 script will run silently in the background, assessing login attempts.
3. Any login attempt that fails to meet the minimum score threshold will be blocked.

---

## Configuration Options
- **recaptcha_site_key:** Google reCAPTCHA v3 site key.
- **recaptcha_secret_key:** Google reCAPTCHA v3 secret key.
- **recaptcha_min_score:** Minimum acceptable score for login attempts. Default is `0.5` (range: 0.0 to 1.0).

---

## Troubleshooting
1. **JavaScript Not Loading:**
   - Ensure the `recaptcha_site_key` is correctly configured.
   - Check browser developer tools for errors.

2. **Token Not Submitted:**
   - Verify that the login form includes the hidden `g-recaptcha-response` input.

3. **Login Blocked Unexpectedly:**
   - Lower the `recaptcha_min_score` in `config.inc.php` to reduce false positives.

4. **Logs Missing:**
   - Ensure Roundcube logging is enabled and writable (`config/config.inc.php`).

---

## License
This plugin is licensed under the MIT License.

---

## Support
For issues or questions, please contact the plugin maintainer or consult the Roundcube documentation.
