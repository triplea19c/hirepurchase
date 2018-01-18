<?php

/*
  Plugin Name: Hubtel Payment
  Plugin URI: https://wordpress.org/plugins/search/hubtel/
  Description: Accept payment from your woocommerce store. Create payment button for donation and fund raising.
  Version: 2.12
  Author: Delu Akin
  Author URI: https://www.facebook.com/deluakin
 */

if (!defined('ABSPATH'))
	exit("No script kiddies");


$config = include plugin_dir_path(__FILE__) . "includes/settings.php";

add_action('plugins_loaded', 'load_hubtel_payment_plugin', 0);

function load_hubtel_payment_plugin(){
	require plugin_dir_path(__FILE__) . "/hubtel-payment-button.php";
	require plugin_dir_path(__FILE__) . "/hubtel-payment.php";

	hubtel_payment_button_init();
	hubtel_payment_init();
}

$plugin = plugin_basename(__FILE__);
add_filter("plugin_action_links_$plugin", "add_hubtel_settings_link");
function add_hubtel_settings_link($links) {
	$settings_link = '<a href="admin.php?page=hubtel_settings">Settings</a>';
	array_unshift($links, $settings_link);
	return $links;
}

function hubtel_buynow_header() {
    require plugin_dir_path(__FILE__) . "/views/buynow-header.php";
}
add_action('admin_notices', 'hubtel_buynow_header');