<?php

declare(strict_types=1);

/*
Plugin Name: GravityForms Restrict Forms
Plugin URI: https://infostreamusa.com
Description: Gravityforms block malicious IP addresses attempting bypassing the captcha
Version: 1.0
Author: Frederic Guzman
Author URI: https://infostreamusa.com
License: GNU GENERAL
*/

use GFRestrictIP\Main;

define('GFREIP_TABLE_NAME', 'gf_restrict_ip');
//define('GFREIP_token', '1ssdxkVe9T3nLYRWkF4Mnybasd');
defined('ABSPATH') or die();

if (!class_exists('GFRestrictIP\Main')) {
	$autoloader = require_once('autoload.php');
	$autoloader('GFRestrictIP\\Main', __DIR__ . '/src/Main');
}

if (!class_exists('\GForms')) {
	\esc_html('<div class="error notice"> <p><strong>Gravity Forms should be installed.</strong></p></div>');
}

function GFRestrictIPActivation() {
	global $wpdb;
	$charset_collate = $wpdb->get_charset_collate();

	$gfrestrictip_table_name  = $wpdb->prefix . GFREIP_TABLE_NAME;

	$query = $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->esc_like( $table_name ) );

	if ( $wpdb->get_var( $query ) != $gfrestrictip_table_name ) {
		
		$sql = "
			CREATE TABLE {$gfrestrictip_table_name} (
			id INT(255) NOT NULL AUTO_INCREMENT,
			ip VARCHAR(255) NOT NULL, 
			count INT(255) NOT NULL,
			day_date DATE NULL,
			PRIMARY KEY (id), 
			UNIQUE gfrestrictip (ip)
			) $charset_collate;
		";
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );

	}

}
register_activation_hook( __FILE__, 'GFRestrictIPActivation');

function GFRestrictIPClearIP( $links ) {
	$homeURL = get_home_url();
	$settings_link = "<a target='_blank' href='$homeURL/wp-json/gfrestrictip/v1/clear_ip_addresses?token=1ssdxkVe9T3nLYRWkF4Mnybasd'>Clear List of IPs</a>";
	array_unshift($links, $settings_link);
	return $links;
}

$plugin = plugin_basename(__FILE__);
add_filter("plugin_action_links_$plugin", 'GFRestrictIPClearIP' );


add_action('gform_loaded', function () {
	Main::init();
}, 4);
