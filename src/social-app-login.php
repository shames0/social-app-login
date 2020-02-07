<?php
/**
 * Plugin name: Social App Login
 * Plugin URI: https://james.jacobson.app/social-app-login
 * Version: 0.1
*/

// So this file can not be executed independently
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

require_once dirname(__FILE__) .'/class.php';

$salPlug = SocialAppLogin::getInstance();

$salPlug::init_apps();

?>
