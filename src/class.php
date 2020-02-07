<?php
// So this file can not be executed independently
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

require_once dirname(__FILE__) .'/SocialApps/Google.php';

class SocialAppLogin {
    const NAME = 'Social App Login';
    const SLUG = 'social-app-login';
    const APP_LIST = [
        'Google',
    //    'Facebook',
    //    'Instagram',
    ];

    static $instance = false;
    public $options;

    public static function getInstance() {
        if (!self::$instance) {
            self::$instance = new self;
        }
        return self::$instance;
    }

    function __construct () {
        $this->init_options();

        // Register admin Settings menu page
        add_action('admin_menu', function() {
            add_options_page(
                self::NAME,
                self::NAME,
                'manage_options',
                self::SLUG,
                __CLASS__ .'::admin_settings',
            );
        });
    }

    private function init_options() {
        $default_options = [ 'apps' => array_fill_keys(self::APP_LIST, []) ];
        $this->options = get_option(self::SLUG, $default_options);
    }

    public static function register_app_hooks() {
        foreach (self::APP_LIST as $app) {
            $app_class = self::app_class($app);
            if (self::app_is_enabled($app)) {
                $app_class::register_hooks();
            }

            if (method_exists($app_class, 'register_admin_hooks')) {
                $app_class::register_admin_hooks();
            }
        }
    }

    private static function app_class($app) {
        return __CLASS__ .'_'. $app;
    }

    private static function app_options($app) {
        return self::$instance->options['apps'][ $app ];
    }

    public static function app_options_set(string $app, array $options) {
        $something_changed = False;
        foreach ($options as $key => $value) {
            // Set the value for the given option
            self::$instance->options['apps'][$app][ $key ] = $value;
            $something_changed = True;
        }

        // Save the new state of our plugin options
        if ($something_changed) {
            if (get_option(self::SLUG) !== False) {
                update_option(self::SLUG, self::$instance->options);
            }
            else {
                add_option(self::SLUG, self::$instance->options);
            }
        }

        return self::app_options($app);
    }

    private static function app_is_enabled($app) {
        if(!key_exists('is_enabled', self::app_options($app)))
            return False; 

        return  self::app_options($app)['is_enabled'];
    }

    public static function admin_settings() {
        ?>
        <h1>Social App Login - Settings</h1>
        <?php

        $apps = self::APP_LIST;

        $default = $apps[0];
        $req_tab     = empty($_GET['tab'])        ? $default : ucfirst(strtolower($_GET['tab']));
        $current_app = !in_array($req_tab, $apps) ? $default : $req_tab;

        ?>
        <h2 class="nav-tab-wrapper">
        <?php
        foreach( $apps as $app ){
            $class = ( $app == $current_app ) ? ' nav-tab-active' : '';
            echo "<a class='nav-tab$class' href='?page=". self::SLUG ."&tab=$app'>$app</a>";
        }
        ?>
        </h2>
        <?php

        // Show the content for the current app tab
        self::app_class($current_app)::admin_settings(self::app_options($current_app));
    }
}

?>
