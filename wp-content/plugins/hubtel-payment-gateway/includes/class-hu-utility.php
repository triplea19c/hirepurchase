<?php

if (!defined('ABSPATH'))
	exit("No script kiddies");

class WC_HubtelUtility {

	static function post_to_url($url, $credential, $data = false) {
        if($data){
	        if (version_compare(PHP_VERSION, '5.4.0') >= 0)
		        $json = json_encode($data, JSON_UNESCAPED_SLASHES);
	        else
		        $json = str_replace('\\/', '/', json_encode($data));
        }

        $response = wp_remote_post($url, array(
            'method' => isset($json) ? 'POST' : 'GET',
            'headers' => array(
                "Authorization" => $credential,
                "Cache-Control" => "no-cache",
                "Content-Type" => "application/json"
            ),
            'body' => isset($json) ? $json : ''
            )
        );

        if (!is_wp_error($response)) {
            $r = wp_remote_retrieve_body($response);
            return $r;
        }
        return false;
    }

	static function woo_hubtel_has_internet_connection() {
		$connected = @fsockopen( "www.example.com", 80 );
		if ( $connected ) {
			$is_conn = true;
			fclose( $connected );
		} else {
			$is_conn = false;
		}

		return $is_conn;

	}

	static function woo_hubtel_licenseKey_expiry( $key ) {
		if ( ! WC_HubtelUtility::woo_hubtel_has_internet_connection() ) {
			return -2;
		}

		$data     = array(
			"site"   => get_site_url(),
			"plugin" => "hubtel",
			"em"     => get_option( "admin_email", "" ),
			"key"    => $key
		);
		$config = include plugin_dir_path(__FILE__) . "settings.php";
		$response = WC_HubtelUtility::post_to_url( $config["license_baseapi"] . "license2.json", false, $data );
		$response_arr = json_decode( $response, true );
		if ( is_array( $response_arr ) ) {
			$key = isset( $response_arr["key"] ) ? $response_arr["key"] : $key;
			update_option( "hubtellicensekey", $key );
			$expiry = $response_arr["expiry"];
			return $expiry;
		}

		return -1;
	}
}

?>