<?php

if (!defined('ABSPATH'))
	exit("No script kiddies");

$key = get_option( "hubtellicensekey", "N.A" );
$buynow_endpoint = admin_url( 'admin-ajax.php?action=hubtel-premium' );
$love_icon       = plugin_dir_url( __FILE__ ) . "../assets/images/love.png";
$smile_icon      = plugin_dir_url( __FILE__ ) . "../assets/images/smile.png";
$love            = '<img class="iconMe" width="24" height="24" src="' . $love_icon . '">';
$wink            = '<img class="iconMe" width="24" height="24" src="' . $smile_icon . '">';

if(!class_exists("WC_HubtelUtility"))
	require_once plugin_dir_path(__FILE__) . "../includes/class-hu-utility.php";
$expiry = WC_HubtelUtility::woo_hubtel_licenseKey_expiry($key);
if(intval($expiry) != 100) {
	#paid plugin, hide premium notice header
	if ( $expiry > 0 ) {
		$expiry = ( $expiry == 1 ) ? "1 day" : "$expiry days";
		echo "<div id='hubtel-buynow2' class='donate-container'><p>Your Hubtel Payment Plugin will expire in $expiry. Get a premium version of this plugin to keep enjoying all the features and updates for only <span class='h1'>GHS100</span> &nbsp; $wink $love  <a href='" . $buynow_endpoint . "' target='_blank' class='page-title-action' id='btn-buy-now' style='float: right;'>Buy Now</a></div>";
	} else if ( $expiry <= 0 && $expiry != - 2 ) {
		echo "<div id='hubtel-buynow' class='donate-container'><p>Your Hubtel Payment Plugin has expired. Get a premium version of this plugin to keep enjoying all the features and updates for only <span class='h1'>GHS100</span> &nbsp; $wink $love  <a href='" . $buynow_endpoint . "' target='_blank' class='page-title-action' id='btn-buy-now' style='float: right;'>Buy Now</a></div>";
	}
}


if ( isset( $_GET["failed"] ) ) {
	echo "<div class='error notice is-dismissible'><p>Your payment for <b>Hubtel Payment</b> plugin failed</p></div>";
}else{
	if ( isset( $_GET["thankyou"] ) ) {
		echo "<div class='updated notice is-dismissible'><p>Payment was received successfully. Thank you for purchasing the premium version of the <b>Hubtel Payment</b> plugin</p></div>";
	}
}

?>