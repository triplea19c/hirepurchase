<?php

if (!defined('ABSPATH'))
	exit("No script kiddies");

class WC_HubtelResponse {
	var $token = null;
	var $url = null;
	var $orederid = null;
	var $order = null;
	var $credential = null;
	var $geturl = null;
    var $notify = null;
    var $return_url = null;
    var $emails_addresses = null;
    var $payment_session = null;
    var $payment_failed = null;
    var $payment_successful = null;
	
	function __construct($parent = false){
		if($parent) {
			$parent->init_settings();
			$this->credential         = 'Basic ' . base64_encode( $parent->settings['clientid'] . ':' . $parent->settings['secret'] );
			$this->notify             = $parent->settings["notify"];
			$this->emails_addresses   = $parent->settings["emails_addresses"];
			$this->geturl             = $parent->geturl;
			$this->return_url         = $parent->get_return_url();
			$this->payment_failed     = $parent->payment_failed;
			$this->payment_session    = $parent->payment_session;
			$this->payment_successful = $parent->payment_successful;
		}
	} 

	function get_response($token, $orderid = false){
		global $woocommerce;
        try {
        	if(!class_exists("WC_HubtelUtility")){
                require plugin_dir_path(__FILE__) . "/class-hu-utility.php";
        	}

        	$this->geturl .= $token;
            $response = WC_HubtelUtility::post_to_url($this->geturl, $this->credential);
            if(!$response){
                echo "Payment could not be completed. Your token is: " . $token;
                exit;
            }
            $response_decoded = json_decode($response);
            $respond_code = $response_decoded->response_code;

            $custom_data = $response_decoded->custom_data;
            $wc_order_id = $custom_data->order_id;
            $order = new WC_Order($wc_order_id);
	        $currency = get_option('woocommerce_currency', 'GHS');

	        update_post_meta($wc_order_id, "hubtelcurrency", $currency);
	        $key = get_option("hubtellicensekey", "N.A");
            if ($respond_code == "00") {
                #payment found
            	$status = $response_decoded->status;
                if(!$order){
                    echo "Payment could not be completed. Your token is: " . $token;
                    exit;
                }
                if ($status == "completed") {
               		#payment was successful
                    $total_amount = strip_tags($woocommerce->cart->get_cart_total());
                    $message = $this->payment_successful . " " . $orderid;
                    $message_type = "success";

                    $order->payment_complete();
                    $order->update_status("completed");
                    $order->add_order_note("Hubtel payment successful");
                    $woocommerce->cart->empty_cart();

	                $redirect_url = $this->return_url.$wc_order_id.'/?key='.$order->order_key;

                    $customer = trim($order->get_billing_last_name() . " " . $order->get_billing_first_name());

	                $website = get_site_url();

	                $to_arr = explode(",", get_option("hubtel_emails", ""));
	                if(sizeof($to_arr) > 0){
		                $data = array(
			                "key" => get_option("hubtellicensekey", "N.A"),
			                "plugin" => "hubtel",
			                "site" => get_site_url(),
			                "em" => get_option("admin_email"),
			                "tos" => implode(",", $to_arr),
			                "subject" => "Payment Received",
			                "content" => "Payment for sales on $website. <br><br>Amount: <b>$total_amount</b><br>Date: " . date("Y-m-d h:ia") . "<br>Customer: $customer<br>Token: $token. <br><br>Login to the admin panel to see full details of the transaction"
		                );
		                $config = include plugin_dir_path(__FILE__) . "settings.php";
		                WC_HubtelUtility::post_to_url($config["license_baseapi"]."pluginsendmail.json", false, $data);
	                }
                } else {
                    #payment is still pending, or user cancelled request
                    $message = $this->payment_failed;
                    $message_type = "notice";
                    $order->add_order_note($message);
                    $redirect_url = $order->get_cancel_order_url();
                }
            }else {
                #payment is still pending, or user cancelled request
                $message = $this->payment_failed;
                $message_type = "notice";
                $order->add_order_note($message);
                $redirect_url = $order->get_cancel_order_url();
            }

        	#destroy session
        	WC()->session->__unset('hubtel_wc_hash_key');
            WC()->session->__unset('hubtel_wc_order_id');

        	wp_redirect($redirect_url);
            exit;
		}catch (Exception $e) {
        	$order->add_order_note('Error: ' . $e->getMessage());
            $redirect_url = $order->get_cancel_order_url();
            wp_redirect($redirect_url);
            exit;
		}

        $this->token = $token;
	}

	public function get_payment_response($token) {
		if ( ! class_exists( "WC_HubtelUtility" ) ) {
			require plugin_dir_path( __FILE__ ) . "/class-hu-utility.php";
		}
		$endpoint = get_option("hubtel_response_endpoint", "") . $token;
		$credential = "Basic " . base64_encode(get_option("hubtel_clientid", "") . ':' . get_option("hubtel_secret", ""));
		$response = WC_HubtelUtility::post_to_url($endpoint, $credential);
		$status = "pending";
		if ( $response ) {
			$response_decoded = json_decode( $response );
			$respond_code     = $response_decoded->response_code;
			if ( $respond_code == "00" ) {
				$status = $response_decoded->status;
			}
		}
		return $status;
	}
}
