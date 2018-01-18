<?php

if (!defined('ABSPATH')) 
    exit("No script kiddies");

function hubtel_payment_button_init() {

    $plugin = plugin_basename(__FILE__);
    if (!class_exists('WC_HubtelSetup')) {
        require plugin_dir_path(__FILE__) . "/includes/class-hu-setup.php";
    }
    $setup = new WC_HubtelSetup();
    $setup->__initialize($plugin);

    class WC_HubtelPaymentButton {
        var $config = null;

        public function __construct() {
            if (!class_exists('WC_HubtelSetup')) {
                require plugin_dir_path(__FILE__) . "/includes/class-hu-setup.php";
            }
            $setup = new WC_HubtelSetup();
            $this->config = $setup->read_config();

            add_shortcode('HubtelPaymentButton', array(&$this, 'HubtelPaymentButton'));
            add_action('wp_ajax_delhubtelbutton', array('WC_HubtelPaymentButton', 'DeleteButton'));
            add_action('wp_ajax_nopriv_hubtelinitpayment', array('WC_HubtelPaymentButton', 'InitHubtelPayment'));
            add_action('wp_ajax_hubtelinitpayment', array('WC_HubtelPaymentButton', 'InitHubtelPayment'));
	        add_action('wp_ajax_new-hubtel-button', array('WC_HubtelPaymentButton', 'NewHubtelButton'));
        }

        function NewHubtelButton(){
	        $config = include plugin_dir_path(__FILE__) . "includes/settings.php";
	        $site = "site=" . get_option("siteurl", "");
			$newbtn_api = str_replace("####", get_option("hubtellicensekey", "N.A"), $config["license_baseapi"]."newbutton?plugin=hubtel&key=####");
			$newbtn_api .= "&" . $site;

	        if (!class_exists('WC_HubtelUtility')) {
		        require plugin_dir_path(__FILE__) . "/includes/class-hu-utility.php";
	        }

	        $data = isset($_POST["submit"]) ? $_POST : false;
	        $response = WC_HubtelUtility::post_to_url($newbtn_api, false, $data);
	        if($response){
		        echo $response;
	        }
        	exit;
        }

        function InitHubtelPayment(){
	        $s_currency_arr = explode(",", get_option("hubtel_supported_currency", ""));
	        $code = isset($_POST["code"]) ? intval(filter_var($_POST["code"], FILTER_VALIDATE_INT)) : "";
            $name = isset($_POST["name"]) ? filter_var($_POST["name"], FILTER_SANITIZE_STRING) : "";
            $email = isset($_POST["email"]) ? sanitize_email(filter_var($_POST["email"], FILTER_SANITIZE_EMAIL)) : "";
            $mobile = isset($_POST["mobile"]) ? filter_var($_POST["mobile"], FILTER_SANITIZE_STRING) : "";
	        $currency = isset($_POST["currency"]) ? filter_var($_POST["currency"], FILTER_SANITIZE_STRING) : "GHS";
	        $redirect_url = isset($_POST["p"]) ? esc_url_raw(filter_var($_POST["p"], FILTER_SANITIZE_URL)) : get_site_url();
            $amt = isset($_POST["amount"]) ? floatval($_POST["amount"]) : 0;
            $customer = array(
            	            "name" => $name,
	                        "email" => $email,
	                        "mobile" => $mobile
                        );


            $endpoint = get_option("hubtel_payment_endpoint");
            $credential = 'Basic ' . base64_encode(get_option("hubtel_clientid", "") . ":" . get_option("hubtel_secret", ""));

	        if(!class_exists("WC_HubtelUtility"))
		        require plugin_dir_path(__FILE__) . "/includes/class-hu-utility.php";

	        $ex_rate = 1;
	        if($s_currency_arr && in_array($currency, $s_currency_arr) && isset($s_currency_arr[0]) && $s_currency_arr[0] <> $currency){
		        $data = array(
			        "key" => get_option("hubtellicensekey", "N.A"),
			        "plugin" => "hubtel",
			        "site" => get_site_url(),
			        "em" => get_option("admin_email"),
			        "curr" => $currency
		        );
		        $config = include plugin_dir_path(__FILE__) . "includes/settings.php";
		        $response = WC_HubtelUtility::post_to_url($config["license_baseapi"]."currencyrate.json", false, $data);
		        if($response) {
			        $ex_rate = json_decode( $response );
		        }
		        $amt = $amt / $ex_rate;
	        }

            #payment payload
            $payload = array(
                "invoice" => array(
                    "total_amount" => $amt,
                    "description" => "Donation/Contribution",
                ), "store" => array(
                    "name" => get_bloginfo("name"),
                    "website_url" => get_site_url()
                ), "actions" => array(
                    "cancel_url" => $redirect_url,
                    "return_url" => $redirect_url
                )
            );

            #post payload
            $data = array(
	            "key" => get_option("hubtellicensekey", "N.A"),
	            "code" => $code,
	            "plugin" => "hubtel"
            );
	        $config = include plugin_dir_path(__FILE__) . "includes/settings.php";
	        $response = WC_HubtelUtility::post_to_url($config["license_baseapi"]."clickbutton.json", false, $data);
	        $response = WC_HubtelUtility::post_to_url($endpoint, $credential, $payload);
            $json_response = json_decode($response, true);
            if(isset($json_response) && $json_response["response_code"] == "00"){
                $token = $json_response["token"];
                $postarr = array(
                    "post_author" => 1,
                    "post_type" => "hubtelbutton",
	                "post_status" => "wc-pending",
	                "post_date_gmt" => date("Y-m-d h:i:s"),
                );
                $post_id = wp_insert_post($postarr);
                update_post_meta($post_id, "HubtelToken", $token);
                update_post_meta($post_id, "_donation_customer", $customer);
	            update_post_meta($post_id, "_order_total", round($amt, 2));
	            update_post_meta($post_id, "_hubteltotal", $amt);
                echo $json_response["response_text"];
            }
            exit;
        }

        function DeleteButton(){
            require_once plugin_dir_path(__FILE__) . "includes/class-hu-utility.php";
            $data = array(
                "key" => get_option("hubtellicensekey", "N.A"),
                "plugin" => "hubtel",
                "code" => $_GET["code"]
            );
	        $config = include plugin_dir_path(__FILE__) . "includes/settings.php";
            $response = WC_HubtelUtility::post_to_url($config["license_baseapi"]."delbutton.json", false, $data);
            echo "1";
            exit;
        }

        function VerifyPayment($token){
        	if(!class_exists("WC_HubtelUtility"))
	            require_once plugin_dir_path(__FILE__) . "includes/class-hu-utility.php";

        	$endpoint = get_option("hubtel_response_endpoint", "") . $token;
        	$credential = "Basic " . base64_encode(get_option("hubtel_clientid", "") . ":" . get_option("hubtel_secret", ""));
        	$response = WC_HubtelUtility::post_to_url($endpoint, $credential);
            if($response){
            	$response_arr = json_decode($response, true);
            	$response_code = isset($response_arr["response_code"]) ? $response_arr["response_code"] : "";

	            global $wpdb;
	            $results = $wpdb->get_results( "select post_id from $wpdb->postmeta where meta_value = '$token' and meta_key = 'HubtelToken'", ARRAY_A );

	            if($response_code == "00"){
            		$status = $response_arr["status"];
		            if($results){
			            $post_id = $results[0]["post_id"];
			            wp_update_post(
				            array(
					            "ID" => $post_id,
					            "post_status" => ($status == "completed") ? "wc-completed" : "wc-failed"
				            )
			            );
		            }
		            echo "<div style='background-color: #46c9b6; color: #ffffff; text-align: center;padding: 20px;'>Your payment was successfully recieved, thank you.</div><br>";
		            #send email
		            $to_arr = explode(",", get_option("hubtel_emails", ""));
		            $amount = get_post_meta($results[0]["post_id"], "_order_total", true);
		            $website = get_site_url();
		            if(sizeof($to_arr) > 0){
		            	$data = array(
		            		"key" => get_option("hubtellicensekey", "N.A"),
				            "plugin" => "hubtel",
				            "site" => get_site_url(),
				            "em" => get_option("admin_email"),
				            "tos" => implode(",", $to_arr),
				            "subject" => "Payment Received",
				            "content" => "Donation received on $website.<br><br>Amount: <b>GHS $amount</b><br>Time: " . date("Y-m-d h:ia") . ".<br>Customer: N/A<br>Token: $token<br><br>Login to the admin panel to see full details of the transaction"
			            );
			            $config = include plugin_dir_path(__FILE__) . "includes/settings.php";
			            WC_HubtelUtility::post_to_url($config["license_baseapi"]."pluginsendmail.json", false, $data);
		            }
	            }else{
		            $post_id = $results["post_id"];
		            wp_update_post(
			            array(
				            "ID" => $post_id,
				            "post_status" => "wc-failed"
			            )
		            );
		            echo "<div style='background-color: #f2dede;text-align: center;padding: 20px;'>Your payment failed to complete</div><br>";
	            }
            }else{
	            echo "<div style='background-color: #f2dede;text-align: center;padding: 20px;'>Payment request could not be completed, reload the page</div><br>";
            }
        }

        function HubtelPaymentButton($atts){
        	$token = isset($_GET["token"]) ? $_GET["token"] : "";
        	if(trim($token) <> ""){
        		$this->VerifyPayment($token);
	        }
            $code = "";
            $icon = plugin_dir_url(__FILE__) . "/assets/images/button.png";
            $atts = shortcode_atts(array(
                'code' => $code,
            ), $atts);

            $code = $atts["code"];
            $plugin = "hubtel";
            $key = get_option("hubtellicensekey", "N.A");

            require_once plugin_dir_path(__FILE__) . "includes/class-hu-utility.php";
            $data = array(
              "code" => $code,
              "plugin" => "hubtel",
              "key" => $key
            );
	        $config = include plugin_dir_path(__FILE__) . "includes/settings.php";
            $response = WC_HubtelUtility::post_to_url($config["license_baseapi"]."getbutton.json", false, $data);

            $card_logos = plugin_dir_url(__FILE__) . "/assets/images/logo.png";
            $api_url = admin_url('admin-ajax.php') . '?action=hubtelinitpayment';
            $response = str_replace("XXXXX", $card_logos, $response);
            $response = str_replace("#####", $api_url, $response);

            $str = <<<HTML
    $response
HTML;
            return $str;
        }
    }

    new WC_HubtelPaymentButton();
}
