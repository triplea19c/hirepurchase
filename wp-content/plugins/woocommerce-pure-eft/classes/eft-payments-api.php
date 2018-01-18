<?php

/**
* Pure EFT Payment Gateway
*
* @class      WC_Gateway_Pure_EFT
* @extends    WC_Payment_Gateway
* @version    1.2
* @author     Jason Raveling
**/

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class WC_Gateway_Pure_EFT extends WC_Payment_Gateway {

	//Constructor for the gateway
	public function __construct() {
		$this->id                 = 'pure-eft';
		$this->icon               = apply_filters('woocommerce_gateway_icon', 'pure-eft');
		$this->has_fields         = true;
		$this->method_title       = __( 'Pure EFT', 'pure-eft' );
		$this->method_description = __( 'Adds ability to accept checking account information for manual Electronic Funds Transfers (ETF). Right now, this plugin <strong>only collects the information</strong> and sends it via email.<h3 style="margin-top:0;">Donate</h3>Pure EFT is free software but there is a lot of work that goes in to the addition of features keeping it up to date. Please consider making a donation so that I can continue providing updates.<br /><div style="float:left; display:inline; width:180px; margin:10px;"><a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=LJAT6WCWJCQUW" target="_blank"><img alt="PayPal" title="PayPal" style="border:0; width:100%;" src="http://webunraveling.com/public/business/donate/paypal-button.png" /></a></div><div style="float:left; display:inline; width:180px; margin:10px;"><a target="_blank" href="http://webunraveling.com/public/business/donate/pure-eft.html"><img alt="Bitcoin" title="Bitcoin" style="border:0; width:100%;" src="http://webunraveling.com/public/business/donate/btc-logo-words.png" /></a></div>', 'pure-eft' );

		// Load the fields and settings
		$this->init_form_fields();
		$this->init_settings();

		// Define user set variables
		$this->title       					= $this->get_option( 'title' );
		$this->description  				= $this->get_option( 'description' );
		$this->acct_placeholder  		= $this->get_option( 'acct-placeholder' );
		$this->routing_placeholder  = $this->get_option( 'routing-placeholder' );
		$this->check_example				= $this->get_option( 'check-example' );
		$this->email_acct_info			= $this->get_option( 'email-acct-info');

    // Actions
		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
	}

  /**********************************************************
  * Initialise Gateway Settings Form Fields
  **********************************************************/
  public function init_form_fields() {
		$this->form_fields = array(
			'enabled' => array(
				'title'   => __( 'Enable/Disable', 'pure-eft' ),
				'type'    => 'checkbox',
				'label'   => __( 'Enable EFT Payment', 'pure-eft' ),
				'default' => 'yes'
			),
			'title' => array(
				'title'       => __( 'Title', 'pure-eft' ),
				'type'        => 'text',
				'description' => __( 'This controls the title which the user sees during checkout.', 'pure-eft' ),
				'default'     => __( 'Electronic Funds Transfer (EFT)', 'pure-eft' ),
				'desc_tip'    => true,
			),
			'description' => array(
				'title'       => __( 'Description', 'pure-eft' ),
				'type'        => 'textarea',
				'description' => __( 'Payment method description that the customer will see on your checkout.', 'pure-eft' ),
				'default'     => __( 'Please enter your checking account and routing numbers.', 'pure-eft' ),
				'desc_tip'    => true,
			),
			'acct-placeholder' => array(
				'title'       => __( 'Account Field Placeholder', 'pure-eft' ),
				'type'        => 'text',
				'description' => __( 'Text that appears in the text field before data is entered. (Leave blank for no placeholder)', 'pure-eft' ),
				'default'     => __( '', 'pure-eft' ),
				'desc_tip'    => true,
			),
			'routing-placeholder' => array(
				'title'       => __( 'Routing Field Placeholder', 'pure-eft' ),
				'type'        => 'text',
				'description' => __( 'Text that appears in the text field before data is entered. (Leave blank for no placeholder)', 'pure-eft' ),
				'default'     => __( '', 'pure-eft' ),
				'desc_tip'    => true,
			),
			'check-example' => array(
				'title'   => __( 'Check Example', 'pure-eft' ),
				'type'    => 'checkbox',
				'label'   => __( 'Display the example of a check showing where the account and routing numbers are.', 'pure-eft' ),
				'default' => 'yes'
			),
			'email-acct-info' => array(
				'title'   => __( 'Email Account Info', 'pure-eft' ),
				'type'    => 'checkbox',
				'label'   => __( 'Email the full account and routing numbers to the admin email. (The account information can be found on the order while logged in to WordPress.)', 'pure-eft' ),
				'default' => 'no'
			),
		);
	}

  public function get_plugin_url() {
		return str_replace('/classes','',untrailingslashit( plugins_url( '/', __FILE__ ) ) );
	}

  /* Get the icon. Check if SSL is enabled and provide URL */
  public function get_icon() {
		global $woocommerce;

		if ( get_option('woocommerce_force_ssl_checkout') == 'no' ) {
			$icon = '<img src="' . esc_url( $this->get_plugin_url() . '/assets/logo-icon.png' ) . '" alt="Electronic Funds Transfer (EFT)" />';
		} else {
			$icon = '<img src="' . esc_url( WC_HTTPS::force_https_url( $this->get_plugin_url() ) . '/assets/logo-icon.png' ) . '" alt="Electronic Funds Transfer (EFT)" />';
		}

		return apply_filters( 'woocommerce_gateway_icon', $icon, $this->id );
	}

	//Bank Account Fields... the form for account and routing numbers
	public function pure_eft_account_form() {

		$fields = array(
			'account-number-field' => '<p class="form-row form-row-first">
			<label for="' . $this->id . '-account-number">' . __( "Account Number", 'pure-eft' ) . ' <span class="required">*</span></label>
			<input id="' . $this->id . '-account-number" class="input-text pure-eft-form-account-number" type="text" maxlength="25" autocomplete="off"
			placeholder="' . $this->acct_placeholder . '" name="' . $this->id . '-account-number" />
			</p>',
			'routing-number-field' => '<p class="form-row form-row-first">
			<label for="' . $this->id . '-routing-number">' . __( "Routing Number", 'pure-eft' ) . ' <span class="required">*</span></label>
			<input id="' . $this->id . '-routing-number" class="input-text pure-eft-form-routing-number" type="text" maxlength="9" autocomplete="off"
			placeholder="' . $this->routing_placeholder . '" name="' . $this->id . '-routing-number" />
			</p>',
			'account-type-field' => '<p class="form-row form-row-wide">
			<label for "' . $this->id . '-account-type">' . __( "Account Type", 'pure-eft' ). ' <span class="required">*</span></label>
			<input id="' . $this->id . '-account-type" class="pure-eft-form-account-type" type="radio" name="' . $this->id . '-account-type" value="Checking" /> Checking<br />
			<input id="' . $this->id . '-account-type" class="pure-eft-form-account-type" type="radio" name="' . $this->id . '-account-type" value="Savings" /> Savings
			</p>'
		); ?>
		<fieldset id="<?php echo $this->id; ?>-acct-form">
			<?php
			echo $fields['routing-number-field'];
			echo $fields['account-number-field'];
			echo $fields['account-type-field'];
			?>

			<div class="clear"></div>

			<?php // display the check example if enabled
			if ( $this->check_example == 'yes' ) {
				if ( get_option('woocommerce_force_ssl_checkout') == 'no' ) {
					echo '<img src="' . esc_url( $this->get_plugin_url() . '/assets/check-instructions.png' ) . '" alt="Example of numbers on check (Account, Routing, Check number)" />';
				} else {
					echo '<img src="' . esc_url( WC_HTTPS::force_https_url( $this->get_plugin_url() ) . '/assets/check-instructions.png' ) . '" alt="Example of numbers on check (Account, Routing, Check number)" />';
				}
			} ?>
		</fieldset>
	<?php }

	/**********************************************************
	* Payment form on the checkout page
	**********************************************************/
	public function payment_fields() {
		global $woocommerce;

		if ( $this->description ) {
			echo $this->description . "<br /><sup>Please enter only numbers (no spaces or dashes)</sup>";
		}

		// Print the customer input form
		$this->pure_eft_account_form();
	}

	/**********************************************************
  * Validate user input from the payment form
  **********************************************************/
  public function validate_fields() {
		global $woocommerce;

		// Get the input
		$account_number = isset($_POST[$this->id . '-account-number']) ? wc_clean($_POST[$this->id . '-account-number']) : '';
		$routing_number = isset($_POST[$this->id . '-routing-number']) ? wc_clean($_POST[$this->id . '-routing-number']) : '';
		$account_type = isset($_POST[$this->id . '-account-type']) ? wc_clean($_POST[$this->id . '-account-type']) : '';
		$account_type = strval($account_type);

		try {
			// Validate $routing_number
			if ( empty( $routing_number ) ) {
				throw new Exception( __( 'Routing number must be provided.', 'pure-eft' ) );
			} elseif ( !eregi( "^[0-9]+$", $routing_number ) ) {
				throw new Exception( __( 'Routing number must be numbers only.', 'pure-eft' ) );
			} elseif ( strlen( $routing_number ) != 9 ) {
				throw new Exception( __( 'Routing number is invalid (It must be 9 digits)', 'pure-eft' ) );
			}

			// Validate $account_number
			if ( empty( $account_number ) ) {
				throw new Exception( __( 'Account number must be provided.', 'pure-eft' ) );
			} elseif (!eregi( "^[0-9]+$", $account_number ) ) {
				throw new Exception( __( 'Account number must be numbers only.', 'pure-eft' ) );
			}

			if ( empty( $account_type ) ) {
				throw new Exception( __( 'Please select an account type.', 'pure-eft' ) );
			}
			return true;

		} catch( Exception $e ) {

			if ( function_exists( 'wc_add_notice' ) ) {
				wc_add_notice( $e->getMessage(), 'error' );
			} else {
				$message = ( $e->getMessage() );
				wc_add_notice( $message, 'error' );
			}
			return false;
		}
	}

	/**
	* Process the payment and return the result
	*
	* @param int $order_id
	* @return array
	**/
	public function process_payment( $order_id ) {
		global $woocommerce, $account_number, $routing_number, $account_type, $email_acct_info;
		$order = wc_get_order( $order_id );

		$account_number = isset($_POST[$this->id . '-account-number']) ? wc_clean($_POST[$this->id . '-account-number']) : '';
		$routing_number = isset($_POST[$this->id . '-routing-number']) ? wc_clean($_POST[$this->id . '-routing-number']) : '';
		$account_type = isset($_POST[$this->id . '-account-type']) ? wc_clean($_POST[$this->id . '-account-type']) : '';
		$email_acct_info = $this->email_acct_info;

		// Add the account info to the order email
		function add_account_info( $order, $is_admin_email ) {
			global $woocommerce, $account_number, $routing_number, $account_type, $email_acct_info;

			if ( $is_admin_email && $email_acct_info == 'yes' ) {
				echo '<h1> email_acct_info: ' . $email_acct_info . '</h1>';
				echo '<h4>Account: ' . $account_number . '</h4>';
				echo '<h4>Routing: ' . $routing_number . '</h4>';
			} else {
				echo '<h4>Account: XXXXXX' . substr( $account_number, -3 ) . '</h4>';
				echo '<h1> email_acct_info: ' . $email_acct_info . '</h1>';
			}
			echo '<h4>Type: ' . $account_type . '</h4>';
		}

		add_action( 'woocommerce_email_after_order_table', 'add_account_info', 12, 3 );

		// Mark as on-hold (we need to initiate the transfer)
		$order->update_status( 'processing', __( 'Awaiting EFT payment', 'pure-eft' ) );

		// Reduce stock levels
		$order->reduce_order_stock();

		// Add the account info to the order
		$order->add_order_note( __("Acct: {$account_number}; Routing: {$routing_number}; Type: {$account_type}", 'pure-eft') );

		// Remove cart
		WC()->cart->empty_cart();

		// Return thankyou redirect
		return array(
			'result' 	=> 'success',
			'redirect'	=> $this->get_return_url( $order )
		);
	}
}
