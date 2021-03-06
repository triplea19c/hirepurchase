<?php
/**
 * Plugin Name: Mollie Payments for WooCommerce
 * Plugin URI: https://github.com/mollie/WooCommerce
 * Description: Accept payments in WooCommerce with the official Mollie plugin
 * Version: 2.8.2
 * Author: Mollie
 * Author URI: https://www.mollie.com
 * Requires at least: 3.8
 * Tested up to: 4.9
 * Text Domain: mollie-payments-for-woocommerce
 * Domain Path: /i18n/languages/
 * License: GPLv2 or later
 * WC requires at least: 2.2.0
 * WC tested up to: 3.3
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
require_once 'includes/mollie/wc/autoload.php';

// TODO: Add more constants WP-style, and move from classes to here.

// Plugin folder URL.
if ( ! defined( 'M4W_PLUGIN_URL' ) ) {
	define( 'M4W_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
}

// Plugin directory
if ( ! defined( 'M4W_PLUGIN_DIR' ) ) {
	define( 'M4W_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
}

/**
 * Check if WooCommerce is active and of a supported version
 */
function mollie_wc_check_woocommerce_status() {
	if ( ! class_exists( 'WooCommerce' ) || version_compare( get_option( 'woocommerce_db_version' ), '2.2', '<' ) ) {
		remove_action('init', 'mollie_wc_plugin_init');
		add_action( 'admin_notices', 'mollie_wc_plugin_inactive' );
		return;
	}
}
add_action( 'plugins_loaded', 'mollie_wc_check_woocommerce_status' );

/**
 * Called when plugin is loaded
 */
function mollie_wc_plugin_init() {

	// Register Mollie autoloader
	Mollie_WC_Autoload::register();

	// Setup and start plugin
	Mollie_WC_Plugin::init();
}

/**
 * Called when plugin is activated
 */
function mollie_wc_plugin_activation_hook ()
{

	if ( ! class_exists( 'WooCommerce' ) || version_compare( get_option( 'woocommerce_db_version' ), '2.2', '<' ) ) {
		remove_action('init', 'mollie_wc_plugin_init');
		add_action( 'admin_notices', 'mollie_wc_plugin_inactive' );
		return;
	}

    // Register Mollie autoloader
    Mollie_WC_Autoload::register();

    $status_helper = Mollie_WC_Plugin::getStatusHelper();

    if (!$status_helper->isCompatible())
    {
        $title   = 'Could not activate plugin ' . Mollie_WC_Plugin::PLUGIN_TITLE;
        $message = '<h1><strong>Could not activate plugin ' . Mollie_WC_Plugin::PLUGIN_TITLE . '</strong></h1><br/>'
                 . implode('<br/>', $status_helper->getErrors());

        wp_die($message, $title, array('back_link' => true));
        return;
    }
}

register_activation_hook(__FILE__, 'mollie_wc_plugin_activation_hook');

function mollie_wc_plugin_inactive() {

	$nextScheduledTime = wp_next_scheduled( 'pending_payment_confirmation_check' );
	if ( $nextScheduledTime ) {
		wp_unschedule_event( $nextScheduledTime, 'pending_payment_confirmation_check' );
	}

	if ( ! is_admin() ) {
		return;
	}

	if ( ! is_plugin_active( 'woocommerce/woocommerce.php' ) ) {

		echo '<div class="error"><p>';
		echo sprintf( esc_html__( '%1$sMollie Payments for WooCommerce is inactive.%2$s The %3$sWooCommerce plugin%4$s must be active for it to work. Please %5$sinstall & activate WooCommerce &raquo;%6$s', 'mollie-payments-for-woocommerce' ), '<strong>', '</strong>', '<a href="https://wordpress.org/plugins/woocommerce/">', '</a>', '<a href="' . esc_url( admin_url( 'plugins.php' ) ) . '">', '</a>' );
		echo '</p></div>';
	}

	if ( version_compare( get_option( 'woocommerce_db_version' ), '2.2', '<' ) ) {

		echo '<div class="error"><p>';
		echo sprintf( esc_html__( '%1$sMollie Payments for WooCommerce is inactive.%2$s This version requires WooCommerce 2.2 or newer. Please %3$supdate WooCommerce to version 2.2 or newer &raquo;%4$s', 'mollie-payments-for-woocommerce' ), '<strong>', '</strong>', '<a href="' . esc_url( admin_url( 'plugins.php' ) ) . '">', '</a>' );
		echo '</p></div>';

	}
}

add_action('init', 'mollie_wc_plugin_init');

/**
 * Load the plugin text domain for translations.
 */
function mollie_add_plugin_textdomain() {

	load_plugin_textdomain( 'mollie-payments-for-woocommerce', false, M4W_PLUGIN_DIR . 'i18n/languages/' );

}

add_action( 'plugins_loaded', 'mollie_add_plugin_textdomain' );
