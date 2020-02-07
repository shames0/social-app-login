<?php
// So this file can not be executed independently
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

class SocialAppLogin_Google {
    const APP_NAME = 'Google';

    public static function register_admin_hooks() {
        // Our admin javascript code
        add_action('admin_enqueue_scripts', __CLASS__ .'::enqueue_admin_scripts');

        // Our admin ajax action
        add_action('wp_ajax_socialappslogin_google_settings', __CLASS__ .'::admin_settings_ajax_handler');
    }

    public static function register_hooks() {
        add_action('login_head', __CLASS__ .'::login_head_additions');
        add_action('login_form', __CLASS__ .'::login_form_additions');
    }

    public static function enqueue_admin_scripts($hook) {
        // Include our JavaScript for our admin settings page
        $script_location = plugin_dir_url(__FILE__) .'Google/admin.js';
        wp_enqueue_script('SocialAppLogin_Google_Admin', $script_location, ['jquery']);

        $settings_nonce = wp_create_nonce('sal_google_settings');
        wp_localize_script('SocialAppLogin_Google_Admin', 'sal_google_settings_ajax_obj', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce'    => $settings_nonce,
        ]);
    }

    public static function login_head_additions(array $options) {
        $js_url = plugin_dir_url(__FILE__) .'Google/functions.js';
        $client_id = $options['client_id'];

        ?>
        <script src="<?php $js_url ?>" async></script>
        <script src="https://apis.google.com/js/platform.js?onload=renderButton" async defer></script>
        <meta name="google-signin-client_id" content="<?php $client_id ?>.apps.googleusercontent.com">
        <style>
            .mb-15 {
                margin-bottom: 15px;
            }
        </style>
        <?php
    }

    public static function login_form_additions() {
        // onGoogleSignIn is defined in Google/functions.js 
        ?>
        <hr class="mb-15"/>
        <div class="g-signin2 mb-15" id="my-signin"></div>
        <?php
    }

    public static function admin_settings(array $options) {
        ?>
        <div id="sal-waiting-msg" class="notice notice-info is-dismissible hidden">
            <p><strong>Updating...</strong></p>
            <button type="button" class="notice-dismiss">
                <span class="screen-reader-text">Dismiss this notice.</span>
            </button>
        </div>
        <div id="sal-error-msg" class="notice notice-error is-dismissible hidden">
            <p><strong>Failed to update settings.</strong></p>
            <div id="sal-error-details"></div>
            <button type="button" class="notice-dismiss">
                <span class="screen-reader-text">Dismiss this notice.</span>
            </button>
        </div>
        <div id="sal-success-msg" class="notice notice-success is-dismissible hidden">
            <p><strong>Settings updated.</strong></p>
            <button type="button" class="notice-dismiss">
                <span class="screen-reader-text">Dismiss this notice.</span>
            </button>
        </div>

        <table class="form-table">
            <tr>
                <th><label for="enable_google">Enable App</label></th>
                <td>
                    <input
                        type="checkbox"
                        id="enable_google"
                        name="enabled"
                        <?php if ($options['is_enabled']) echo 'checked'; ?>
                    >
                </td>
            </tr>
            <tr>
            </tr>
            <tr>
                <th><label for="client_id">Google Application Client ID</label></th>
                <td>
                    <input
                        type="text"
                        class="regular-text"
                        style="margin-top: 15px;"
                        id="client_id"
                        name="client_id"
                        placeholder="Client ID"
                        value="<?php echo $options['client_id']; ?>"
                    >
                    <p>A client ID can be obtained by following the "Get the API key" <a target="_blank" href="https://developers.google.com/maps/documentation/javascript/get-api-key#get-the-api-key">directions here</a></p>
                </td>
            </tr>
            <tr>
                <th><label for="client_secret">Google Application Client Secret</label></th>
                <td>
                    <input
                        type="text"
                        class="regular-text"
                        style="margin-top: 15px;"
                        id="client_secret"
                        name="client_secret"
                        placeholder="Client Secret"
                        value="<?php echo $options['client_secret']; ?>"
                    >
                </td>
            </tr>
            <tr>
                <td>
                    <input
                        type="submit"
                        class="button button-primary"
                        value="Save"
                        onClick="sal_update_google_settings()"
                    >
                </td>
            </tr>
        </table>
        <?php
    }

    public static function admin_settings_ajax_handler() {
        // Check the nonce
        check_ajax_referer('sal_google_settings');
        $settings = $_POST['settings'];

        $new_settings = [];

        // Do a bit of value validation and save the values
        if (in_array($settings['is_enabled'], ['true', 'false'])) {
            if ($settings['is_enabled'] == 'true') {
                $new_settings['is_enabled'] = True;
            }
            else {
                $new_settings['is_enabled'] = False;
            }
        }
        else {
            wp_send_json_error('Invalid value for enable flag', 400);
            wp_die();
        }

        $client_id = trim($settings['client_id']);
        if (empty($client_id) || preg_match('/^[-\w]+$/', $client_id) === 1) {
            $new_settings['client_id'] = $client_id;
        }
        else {
            wp_send_json_error('Invalid Application Client ID', 400);
            wp_die();
        }

        $secret = trim($settings['client_secret']);
        if (empty($secret) || preg_match('/^\w+$/', $secret) === 1) {
            $new_settings['client_secret'] = $secret;
        }
        else {
            wp_send_json_error("Invalid Application Client Secret: $secret", 400);
            wp_die();
        }

        // Adjust the settings to requested values
        $resp = SocialAppLogin::app_options_set(self::APP_NAME, $new_settings);

        // Reply with success
        wp_send_json_success(['settings' => $settings]);

        // Apparently this is expected of all ajax handlers
        wp_die();
    }
}

?>
