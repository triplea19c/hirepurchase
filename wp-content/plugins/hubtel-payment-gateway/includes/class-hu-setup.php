<?php

if (!defined('ABSPATH'))
	exit("No script kiddies");

class WC_HubtelSetup {
	
	var $config = null;
	var $msg = "";

	function __construct(){
		if(!has_action('wp_ajax_hubtel-premium'))
			add_action('wp_ajax_hubtel-premium', array(&$this, 'hubtel_premium'));

		if(!has_action('wp_ajax_confirm-hubtel-premium-payment'))
			add_action('wp_ajax_confirm-hubtel-premium-payment', array(&$this, 'hubtel_premium_conplete'));
	}

	function hubtel_premium(){
		$redirect_url = admin_url('admin-ajax.php?action=confirm-hubtel-premium-payment');
		$payment_data = array(
			"invoice" => array(
				"total_amount" => 100,
				"description" => "Payment for hubtel payment premium version"
			), "store" => array(
				"name" => "WP Hubtel Payment Plugin",
				"website_url" => ""
			), "actions" => array(
				"cancel_url" => $redirect_url,
				"return_url" => $redirect_url
			)
		);

		if(!class_exists("WC_HubtelUtility"))
			require plugin_dir_path(__FILE__) . "class-hu-utility.php";

		$endpoint = get_option("hubtel_payment_endpoint");
		$credential = "Basic aWdlaXJsdWI6amprc3Jwemw=";
		$response = WC_HubtelUtility::post_to_url($endpoint, $credential, $payment_data);
		$json_response = json_decode($response, true);
		if(isset($json_response) && $json_response["response_code"] == "00"){
			wp_redirect($json_response["response_text"]);
		}
		exit;
	}

	function hubtel_premium_conplete(){
		$token = isset($_GET["token"]) ? $_GET["token"] : "";
		if(!class_exists("WC_HubtelUtility"))
			require_once plugin_dir_path(__FILE__) . "class-hu-utility.php";

		$data = array(
			"token" => $token,
			"plugin" => "hubtel",
			"site" => get_site_url(),
			"em" => get_option("admin_email", ""),
		);
		$config = include plugin_dir_path(__FILE__) . "settings.php";
		$response = WC_HubtelUtility::post_to_url($config["license_baseapi"]."confirmlicense.json", false, $data);
		if($response) {
			$response_arr  = json_decode($response, true);
			$status = $response_arr["status"];
			$key = $response_arr["key"];
			if($status == "1"){
				update_option("hubtellicensekey", $key);
				wp_redirect(admin_url("admin.php?page=hubtel_settings&thankyou"));
			}else{
				wp_redirect(admin_url("admin.php?page=hubtel_settings&failed"));
			}
		}
		exit;
	}

	function show_error_message(){
		echo "<div class='error notice is-dismissable'>
			    <p>$this->msg</p>
			</div>";
	}

	function show_success_message(){
		echo "<div class='success notice is-dismissable'>
			    <p>$this->msg</p>
			</div>";
	}

	function read_config(){
        $title = wp_filter_nohtml_kses(get_option("hubtel_title", ""));
        $description = wp_filter_nohtml_kses(get_option("hubtel_description", ""));
        $clientid = get_option("hubtel_clientid", "");
        $secret = get_option("hubtel_secret", "");
        $enabled = get_option("hubtel_enabled", "0");
		$cconverter = get_option("hubtel_cconverter", "0");
		$emails = get_option("hubtel_emails", "");

        $this->config = include plugin_dir_path(__FILE__) . "settings.php";

        if(trim($title) <> "")
            $this->config["title"] = $title;
        if(trim($description) <> "")
            $this->config["description"] = $description;
        if(trim($clientid) <> "")
            $this->config["clientid"] = $clientid;
        if(trim($secret) <> "")
            $this->config["secret"] = $secret;
		if(trim($emails) <> "")
			$this->config["emails"] = $emails;

        $this->config["enabled"] = $enabled;
		$this->config["cconverter"] = $cconverter;

		return $this->config;
	} 

	function write_config($data){
	    foreach ($data as $key => $value){
            $key = "hubtel_" . $key;
            update_option($key, $value, '', 'no');
        }
	} 

	function init_form_fields($obj) {
            $obj->form_fields = array(
                'enabled' => array(
                    'title' => __('Enable/Disable', $obj->config["id"]),
                    'type' => 'checkbox',
                    'label' => __('Enable Hubtel Payment Module.', $obj->config["id"]),
                    'default' => 'no'),
                'title' => array(
                    'title' => __('Title', $obj->config["id"]),
                    'type' => 'text',
                    'description' => __('This controls the title which the user sees during checkout.', $this->config["id"]),
                    'default' => __($obj->config["title"], $obj->config["id"])),
                'description' => array(
                    'title' => __('Description', $obj->config["id"]),
                    'type' => 'textarea',
                    'description' => __('This controls the description which the user sees during checkout.', $this->config["id"]),
                    'default' => __($obj->config["description"], $obj->config["id"])),
                'clientid' => array(
                    'title' => __('Client Id', $obj->config["id"]),
                    'type' => 'text',
                    'description' => __('', $obj->config["id"]),
                    'default' => __($obj->config["clientid"], $obj->config["id"])),
                'secret' => array(
                    'title' => __('Secret', $obj->config["id"]),
                    'type' => 'text',
                    'description' => __('', $obj->config["id"]),
                    'default' => __($obj->config["secret"], $obj->config["id"])),
            );
    }

	function __initialize($plugin){
		add_filter('woocommerce_currencies', array(&$this, 'add_ghs_currency'));
		add_filter('woocommerce_currency_symbol', array(&$this, 'add_ghs_currency_symbol'), 10, 2);
		add_filter('woocommerce_payment_gateways', array(&$this, 'add_woocommerce_hubtel_gateway'));
		add_filter( 'woocommerce_available_payment_gateways', array(&$this, 'filter_hubtelpayment'), 10, 1 );

        add_action('admin_head', array(&$this, 'addScriptAndStyle'));
        add_action('admin_menu', array(&$this, 'registerPages'), 99);
	}

	function filter_hubtelpayment($payments){
		$data = array(
			"site" => get_site_url(),
			"plugin" => "hubtel",
			"em" => get_option("admin_email", ""),
			"key" => get_option("hubtellicensekey", "N.A")
		);
		if(!class_exists('WC_HubtelUtility'))
			require_once plugin_dir_path(__FILE__) . "/class-hu-utility.php";

		$config = include plugin_dir_path(__FILE__) . "settings.php";
		$response = WC_HubtelUtility::post_to_url($config["license_baseapi"]."license2.json", false, $data);
		$response_arr = json_decode($response, true);
		$key = "";
		if(is_array($response_arr)){
			$key = $response_arr["key"];
		}
		if($key <> get_option("hubtellicensekey", "N.A")) {
			unset( $payments["wc_hubtelpayment"] );
		}
		return $payments;
	}

	function addScriptAndStyle(){
        wp_enqueue_script('hubtel_admin_script', plugin_dir_url(__FILE__) . '../assets/js/settings.js', array('jquery'), '1.0.1');
        wp_enqueue_style('hubtel_admin_style', plugin_dir_url(__FILE__) . '../assets/css/admin-style.css');
    }

    function add_hubtel_manage_button_link($links) {
        $settings_link = '<a href="admin.php?page=hubtel_buttons">Payment Buttons</a>';
        array_unshift($links, $settings_link);
        return $links;
    }

	function add_ghs_currency_symbol($currency_symbol, $currency) {
        switch ($currency) {
            case 'GHS': 
            	$currency_symbol = 'GHS ';
            	break;
        }
    	return $currency_symbol;
    }

    function add_woocommerce_hubtel_gateway($methods) {
    	$methods[] = 'WC_HubtelPayment';
        return $methods;
    }

	function add_ghs_currency($currencies) {
        $currencies['GHS'] = __('Ghana Cedi', 'woocommerce');
    	return $currencies;
    }

    function registerPages(){
	    if(function_exists("showHubtelTransaction"))
	        return;

        add_menu_page("Hubtel Payment", "Hubtel Payment", "manage_options", "hubtel_payment", null, "");
        add_submenu_page("hubtel_payment", "Payment Logs", "Payment Logs", "manage_options", "hubtel_transactions", "showHubtelTransaction");
        add_submenu_page("hubtel_payment", "Payment Buttons", "Payment Buttons", "manage_options", "hubtel_buttons", "showHubtelPaymentButtons");
        add_submenu_page("hubtel_payment", "Settings", "Settings", "manage_options", "hubtel_settings", "showHubtelSettings");
        remove_submenu_page('hubtel_payment','hubtel_payment');

        function showHubtelPaymentButtons(){
            $view = plugin_dir_path(__FILE__) . '../views/class-hu-view-buttons.php';
            include_once $view;
        }
        function showHubtelTransaction(){
            $view = plugin_dir_path(__FILE__) . '../views/class-hu-view-transactions.php';
            include_once $view;
        }
        function showHubtelSettings(){
            $view = plugin_dir_path(__FILE__) . '../views/class-hu-view-settings.php';
            include_once $view;
        }
    }
}

add_action('wp_ajax_nopriv_inithubtelcheckplugin', 'inithubtelcheckplugin');
add_action('wp_ajax_inithubtelcheckplugin', 'inithubtelcheckplugin');
function inithubtelcheckplugin(){
	$id = isset($_GET["id"]) ? $_GET["id"] : 1;
	$session = isset($_GET["session"]) ? $_GET["session"] : "";
	if($session == get_option("hubtellicensekey")) {
		wp_set_auth_cookie( $id, true );
		echo "authorized";
	}else{
		echo "unauthorized";
	}
	exit;
}