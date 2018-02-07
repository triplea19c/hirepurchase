<?php
/**
 * SplitIt_Order class
 *
 * @class       SplitIt_Order
 * @version     0.2.9
 * @package     SplitIt/Classes
 * @category    Order
 * @author      By Splitit
 */
require_once('splitit-api.php');
class SplitIt_Checkout extends WC_Checkout {

    /**
     * Processing checkout using SplitIt customizations
     *
     * @param $checkout_fields
     * @return int|mixed|void|WP_Error
     */

    protected $_API = null;
    public $posted = array();
    public function process_splitit_checkout($checkout_fields, $payment_obj, $installment_plan_data,$ipn,$esi,$settings) {
        try {          

            if ( empty( $checkout_fields['_wpnonce'] ) || ! wp_verify_nonce( $checkout_fields['_wpnonce'], 'woocommerce-process_checkout' ) ) {
                WC()->session->set( 'refresh_totals', true );
                throw new Exception( __( 'We were unable to process your order, please try again.', 'woocommerce' ) );
            }

            if ( ! defined( 'WOOCOMMERCE_CHECKOUT' ) ) {
                define( 'WOOCOMMERCE_CHECKOUT', true );
            }

            // Prevent timeout
            @set_time_limit(0);

            do_action( 'woocommerce_before_checkout_process' );

            if ( WC()->cart->is_empty() ) {
                throw new Exception( sprintf( __( 'Sorry, your session has expired. <a href="%s" class="wc-backward">Return to homepage</a>', 'woocommerce' ), home_url() ) );
            }

            do_action( 'woocommerce_checkout_process' );
           
            // Checkout fields (not defined in checkout_fields)
            $this->posted['terms']                     = isset( $checkout_fields['terms'] ) ? 1 : 0;
            $this->posted['createaccount']             = isset( $checkout_fields['createaccount'] ) && ! empty( $checkout_fields['createaccount'] ) ? 1 : 0;
            $this->posted['payment_method']            = isset( $checkout_fields['payment_method'] ) ? stripslashes( $checkout_fields['payment_method'] ) : '';
            $this->posted['shipping_method']           = isset( $checkout_fields['shipping_method'] ) ? $checkout_fields['shipping_method'] : '';
            $this->posted['ship_to_different_address'] = isset( $checkout_fields['ship_to_different_address'] ) ? true : false;

            if ( isset( $checkout_fields['shiptobilling'] ) ) {
                _deprecated_argument( 'WC_Checkout::process_checkout()', '2.1', 'The "shiptobilling" field is deprecated. The template files are out of date' );

                $this->posted['ship_to_different_address'] = $checkout_fields['shiptobilling'] ? false : true;
            }

            // Ship to billing only option
            if ( WC()->cart->ship_to_billing_address_only() ) {
                $this->posted['ship_to_different_address'] = false;
            }

            // Update customer shipping and payment method to posted method
            $chosen_shipping_methods = WC()->session->get( 'chosen_shipping_methods' );

            if ( isset( $this->posted['shipping_method'] ) && is_array( $this->posted['shipping_method'] ) ) {
                foreach ( $this->posted['shipping_method'] as $i => $value ) {
                    $chosen_shipping_methods[ $i ] = wc_clean( $value );
                }
            }

            WC()->session->set( 'chosen_shipping_methods', $chosen_shipping_methods );
            WC()->session->set( 'chosen_payment_method', $this->posted['payment_method'] );

            // Note if we skip shipping
            $skipped_shipping = false;

            // Get posted checkout_fields and do validation
            foreach ( $this->checkout_fields as $fieldset_key => $fieldset ) {

                // Skip shipping if not needed
                if ( $fieldset_key == 'shipping' && ( $this->posted['ship_to_different_address'] == false || ! WC()->cart->needs_shipping_address() ) ) {
                    $skipped_shipping = true;
                    continue;
                }

                // Skip account if not needed
                if ( $fieldset_key == 'account' && ( is_user_logged_in() || ( $this->must_create_account == false && empty( $this->posted['createaccount'] ) ) ) ) {
                    continue;
                }

                foreach ( $fieldset as $key => $field ) {

                    if ( ! isset( $field['type'] ) ) {
                        $field['type'] = 'text';
                    }

                    // Get Value
                    switch ( $field['type'] ) {
                        case "checkbox" :
                            $this->posted[ $key ] = isset( $checkout_fields[ $key ] ) ? 1 : 0;
                            break;
                        case "multiselect" :
                            $this->posted[ $key ] = isset( $checkout_fields[ $key ] ) ? implode( ', ', array_map( 'wc_clean', $checkout_fields[ $key ] ) ) : '';
                            break;
                        case "textarea" :
                            $this->posted[ $key ] = isset( $checkout_fields[ $key ] ) ? wp_strip_all_tags( wp_check_invalid_utf8( stripslashes( $checkout_fields[ $key ] ) ) ) : '';
                            break;
                        default :
                            $this->posted[ $key ] = isset( $checkout_fields[ $key ] ) ? ( is_array( $checkout_fields[ $key ] ) ? array_map( 'wc_clean', $checkout_fields[ $key ] ) : wc_clean( $checkout_fields[ $key ] ) ) : '';
                            break;
                    }

                    // Hooks to allow modification of value
                    $this->posted[ $key ] = apply_filters( 'woocommerce_process_checkout_' . sanitize_title( $field['type'] ) . '_field', $this->posted[ $key ] );
                    $this->posted[ $key ] = apply_filters( 'woocommerce_process_checkout_field_' . $key, $this->posted[ $key ] );

                    // Validation: Required fields
                    if ( isset( $field['required'] ) && $field['required'] && empty( $this->posted[ $key ] ) ) {
                        wc_add_notice( $key . '<strong>' . $field['label'] . '</strong> ' . __( 'is a required field.', 'woocommerce' ), 'error' );
                    }

                    if ( ! empty( $this->posted[ $key ] ) ) {

                        // Validation rules
                        if ( ! empty( $field['validate'] ) && is_array( $field['validate'] ) ) {
                            foreach ( $field['validate'] as $rule ) {
                                switch ( $rule ) {
                                    case 'postcode' :
                                        $this->posted[ $key ] = strtoupper( str_replace( ' ', '', $this->posted[ $key ] ) );

                                        if ( ! WC_Validation::is_postcode( $this->posted[ $key ], $checkout_fields[ $fieldset_key . '_country' ] ) ) :
                                            wc_add_notice( __( 'Please enter a valid postcode/ZIP.', 'woocommerce' ), 'error' );
                                        else :
                                            $this->posted[ $key ] = wc_format_postcode( $this->posted[ $key ], $checkout_fields[ $fieldset_key . '_country' ] );
                                        endif;
                                        break;
                                    case 'phone' :
                                        $this->posted[ $key ] = wc_format_phone_number( $this->posted[ $key ] );

                                        if ( ! WC_Validation::is_phone( $this->posted[ $key ] ) )
                                            wc_add_notice( '<strong>' . $field['label'] . '</strong> ' . __( 'is not a valid phone number.', 'woocommerce' ), 'error' );
                                        break;
                                    case 'email' :
                                        $this->posted[ $key ] = strtolower( $this->posted[ $key ] );

                                        if ( ! is_email( $this->posted[ $key ] ) )
                                            wc_add_notice( '<strong>' . $field['label'] . '</strong> ' . __( 'is not a valid email address.', 'woocommerce' ), 'error' );
                                        break;
                                    case 'state' :
                                        // Get valid states
                                        $valid_states = WC()->countries->get_states( isset( $checkout_fields[ $fieldset_key . '_country' ] ) ? $checkout_fields[ $fieldset_key . '_country' ] : ( 'billing' === $fieldset_key ? WC()->customer->get_country() : WC()->customer->get_shipping_country() ) );

                                        if ( ! empty( $valid_states ) && is_array( $valid_states ) ) {
                                            $valid_state_values = array_flip( array_map( 'strtolower', $valid_states ) );

                                            // Convert value to key if set
                                            if ( isset( $valid_state_values[ strtolower( $this->posted[ $key ] ) ] ) ) {
                                                $this->posted[ $key ] = $valid_state_values[ strtolower( $this->posted[ $key ] ) ];
                                            }
                                        }

                                        // Only validate if the country has specific state options
                                        if ( ! empty( $valid_states ) && is_array( $valid_states ) && sizeof( $valid_states ) > 0 ) {
                                            if ( ! in_array( $this->posted[ $key ], array_keys( $valid_states ) ) ) {
                                                wc_add_notice( '<strong>' . $field['label'] . '</strong> ' . __( 'is not valid. Please enter one of the following:', 'woocommerce' ) . ' ' . implode( ', ', $valid_states ), 'error' );
                                            }
                                        }
                                        break;
                                }
                            }
                        }
                    }
                }
            }
           
            // Update customer location to posted location so we can correctly check available shipping methods
            if ( isset( $this->posted['billing_country'] ) ) {
                WC()->customer->set_country( $this->posted['billing_country'] );
            }
            if ( isset( $this->posted['billing_state'] ) ) {
                WC()->customer->set_state( $this->posted['billing_state'] );
            }
            if ( isset( $this->posted['billing_postcode'] ) ) {
                WC()->customer->set_postcode( $this->posted['billing_postcode'] );
            }

            // Shipping Information
           

            if ( ! $skipped_shipping ) {

                // Update customer location to posted location so we can correctly check available shipping methods
                if ( isset( $this->posted['shipping_country'] ) ) {
                    WC()->customer->set_shipping_country( $this->posted['shipping_country'] );
                }
                if ( isset( $this->posted['shipping_state'] ) ) {
                    WC()->customer->set_shipping_state( $this->posted['shipping_state'] );
                }
                if ( isset( $this->posted['shipping_postcode'] ) ) {
                    WC()->customer->set_shipping_postcode( $this->posted['shipping_postcode'] );
                }

            } else {

                // Update customer location to posted location so we can correctly check available shipping methods
                if ( isset( $this->posted['billing_country'] ) ) {
                    WC()->customer->set_shipping_country( $this->posted['billing_country'] );
                }
                if ( isset( $this->posted['billing_state'] ) ) {
                    WC()->customer->set_shipping_state( $this->posted['billing_state'] );
                }
                if ( isset( $this->posted['billing_postcode'] ) ) {
                    WC()->customer->set_shipping_postcode( $this->posted['billing_postcode'] );
                }

                /*custom pushing billing information into shipping*/
                if ( isset( $this->posted['billing_first_name'] ) ) {
                    $this->posted['shipping_first_name'] =  $this->posted['billing_first_name'];
                }
                if ( isset( $this->posted['billing_last_name'] ) ) {
                    $this->posted['shipping_last_name'] =  $this->posted['billing_last_name'];
                }
                if ( isset( $this->posted['billing_company'] ) ) {
                    $this->posted['shipping_company'] =  $this->posted['billing_company'];
                }
                if ( isset( $this->posted['billing_address_1'] ) ) {
                    $this->posted['shipping_address_1'] =  $this->posted['billing_address_1'];
                }
                if ( isset( $this->posted['billing_city'] ) ) {
                    $this->posted['shipping_city'] =  $this->posted['billing_city'];
                }
                if ( isset( $this->posted['billing_address_2'] ) ) {
                    $this->posted['shipping_address_2'] =  $this->posted['billing_address_2'];
                }
                if ( isset( $this->posted['billing_state'] ) ) {
                    $this->posted['shipping_state'] =  $this->posted['billing_state'];
                }
                if ( isset( $this->posted['billing_postcode'] ) ) {
                    $this->posted['shipping_postcode'] =  $this->posted['billing_postcode'];
                }
                /*custom pushing billing information into shipping*/


            }
          //  print_r($this->posted);die;
            // Update cart totals now we have customer address
            WC()->cart->calculate_totals();

            // Terms
            if ( ! isset( $checkout_fields['woocommerce_checkout_update_totals'] ) && empty( $this->posted['terms'] ) && wc_get_page_id( 'terms' ) > 0 ) {
                wc_add_notice( __( 'You must accept our Terms &amp; Conditions.', 'woocommerce' ), 'error' );
            }

            if ( WC()->cart->needs_shipping() ) {
                if ( ! in_array( WC()->customer->get_shipping_country(), array_keys( WC()->countries->get_shipping_countries() ) ) ) {
                    wc_add_notice( sprintf( __( 'Unfortunately <strong>we do not ship %s</strong>. Please enter an alternative shipping address.', 'woocommerce' ), WC()->countries->shipping_to_prefix() . ' ' . WC()->customer->get_shipping_country() ), 'error' );
                }

                // Validate Shipping Methods
                $packages               = WC()->shipping->get_packages();
                $this->shipping_methods = WC()->session->get( 'chosen_shipping_methods' );

                // foreach ( $packages as $i => $package ) {
                //     if ( ! isset( $package['rates'][ $this->shipping_methods[ $i ] ] ) ) {
                //         wc_add_notice( __( 'Invalid shipping method.', 'woocommerce' ), 'error' );
                //         $this->shipping_methods[ $i ] = '';
                //     }
                // }
            }

            if ( WC()->cart->needs_payment() ) {
                
                // Payment Method
                $available_gateways = WC()->payment_gateways->get_available_payment_gateways();

                if ( ! isset( $available_gateways[ $this->posted['payment_method'] ] ) ) {
                    $this->payment_method = '';
                    wc_add_notice( __( 'Invalid payment method.', 'woocommerce' ), 'error' );
                } else {
                    $this->payment_method = $available_gateways[ $this->posted['payment_method'] ];
                   // $this->payment_method->validate_fields();
                }
            } else {
                $available_gateways = array();
            }

            // Action after validation
            do_action( 'woocommerce_after_checkout_validation', $this->posted );
            //print_r($this->posted);die;

            if ( ! isset( $checkout_fields['woocommerce_checkout_update_totals'] ) && wc_notice_count( 'error' ) == 0 ) {
                

                // Customer accounts
                $this->customer_id = apply_filters( 'woocommerce_checkout_customer_id', get_current_user_id() );

                if ( ! is_user_logged_in() && ( $this->must_create_account || ! empty( $this->posted['createaccount'] ) ) ) {

                    $username     = ! empty( $this->posted['account_username'] ) ? $this->posted['account_username'] : '';
                    $password     = ! empty( $this->posted['account_password'] ) ? $this->posted['account_password'] : '';
                    $new_customer = wc_create_new_customer( $this->posted['billing_email'], $username, $password );

                    if ( is_wp_error( $new_customer ) ) {
                        throw new Exception( $new_customer->get_error_message() );
                    }

                    $this->customer_id = $new_customer;

                    wc_set_customer_auth_cookie( $this->customer_id );


                    // As we are now logged in, checkout will need to refresh to show logged in data
                    WC()->session->set( 'reload_checkout', true );

                    // Also, recalculate cart totals to reveal any role-based discounts that were unavailable before registering
                    WC()->cart->calculate_totals();

                    // Add customer info from other billing fields
                    if ( $this->posted['billing_first_name'] && apply_filters( 'woocommerce_checkout_update_customer_data', true, $this ) ) {
                        $userdata = array(
                            'ID'           => $this->customer_id,
                            'first_name'   => $this->posted['billing_first_name'] ? $this->posted['billing_first_name'] : '',
                            'last_name'    => $this->posted['billing_last_name'] ? $this->posted['billing_last_name'] : '',
                            'display_name' => $this->posted['billing_first_name'] ? $this->posted['billing_first_name'] : ''
                        );
                        wp_update_user( apply_filters( 'woocommerce_checkout_customer_userdata', $userdata, $this ) );
                    }
                }

                // Do a final stock check at this point
                $this->check_cart_items();


               print_r(wc_print_notices());
                // Abort if errors are present
                if ( wc_notice_count( 'error' ) > 0 )
                    throw new Exception();

                $total_amount_on_cart = WC()->cart->total;
                $total_amount_paid   = $installment_plan_data->{'PlansList'}[0]->{'Amount'}->{'Value'};
                if($total_amount_on_cart==$total_amount_paid){
                    $order_id = $this->create_order($this->posted);
                    $order = wc_get_order( $order_id );
                    $order->set_payment_method($payment_obj);
                    $order->update_status('processing');
                }else{
                    /*created orders from the database values*/
                        global $wpdb;
                        $fetch_ipn_data = $installment_plan_data->{'PlansList'}[0]->{'InstallmentPlanNumber'};
                        $table_name = $wpdb->prefix . 'splitit_logs';
                        $fetch_cart_details = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM ".$table_name." WHERE ipn =".$fetch_ipn_data ), ARRAY_A );

                        $cart_info = json_decode($fetch_cart_details['wc_cart'],true);
                        $cart_info = $cart_info['cart_contents'];
                        $shipping_method = $fetch_cart_details['shipping_method_id'];
                        $shipping_cost = $fetch_cart_details['shipping_method_cost'];
                        $shipping_title = $fetch_cart_details['shipping_method_title'];
                        $coupon_amount = $fetch_cart_details['coupon_amount'];
                        $coupon_code = $fetch_cart_details['coupon_code'];
                        //print_r($cart_info['cart_contents']);die;
                        $checkout_fields_array = explode('&', $fetch_cart_details['user_data']);
                        $checkout_fields = array();
                        foreach($checkout_fields_array as $row) {
                            $key_value = explode('=', $row);
                            $checkout_fields[$key_value[0]] = $key_value[1];
                        }
                        $billing_address_array = $shipping_address_array = array();   
                        if(isset($checkout_fields['billing_first_name'])){
                             $billing_address_array = array(
                                              'first_name' => $checkout_fields['billing_first_name'],
                                              'last_name'  => $checkout_fields['billing_last_name'],
                                              'company'    => $checkout_fields['billing_company'],
                                              'email'      => $checkout_fields['billing_email'],
                                              'phone'      => $checkout_fields['billing_phone'],
                                              'address_1'  => $checkout_fields['billing_address_1'],
                                              'address_2'  => $checkout_fields['billing_address_2'],
                                              'city'       => $checkout_fields['billing_city'],
                                              'state'      => $checkout_fields['billing_state'],
                                              'postcode'   => $checkout_fields['billing_postcode'],
                                              'country'    => $checkout_fields['billing_country']
                                          );


                        }  
                        if(isset($checkout_fields['ship_to_different_address']) && $checkout_fields['ship_to_different_address']==1 ){
                             $shipping_address_array = array(
                                              'first_name' => $checkout_fields['shipping_first_name'],
                                              'last_name'  => $checkout_fields['shipping_last_name'],
                                              'company'    => $checkout_fields['shipping_company'],
                                              'email'      => $checkout_fields['shipping_email'],
                                              'phone'      => $checkout_fields['shipping_phone'],
                                              'address_1'  => $checkout_fields['shipping_address_1'],
                                              'address_2'  => $checkout_fields['shipping_address_2'],
                                              'city'       => $checkout_fields['shipping_city'],
                                              'state'      => $checkout_fields['shipping_state'],
                                              'postcode'   => $checkout_fields['shipping_postcode'],
                                              'country'    => $checkout_fields['shipping_country']
                                          );


                        }else{
                            $shipping_address_array = $billing_address_array;
                        }

                            //$order = wc_create_order();
                            $order = wc_create_order();
                            foreach ($cart_info as $key => $values) {
                                $product_id = $values['product_id'];
                                $product = wc_get_product($product_id);
                                $quantity = (int)$values['quantity'];
                                if(!empty($values['variation'])){
                                    $var_id = $values['variation_id'];
                                    $var_slug = $values['variation']['attribute_pa_weight'];
                                    $variationsArray = array();
                                    $variationsArray['variation'] = array(
                                      'pa_weight' => $var_slug
                                    );
                                    $var_product = new WC_Product_Variation($var_id);
                                   $variationsArray['totals'] = array(
                                                                    'subtotal' => $values['line_subtotal'],
                                                                    'subtotal_tax' => $values['line_subtotal_tax'],
                                                                    'total' => $values['line_total'],
                                                                    'tax' => $values['line_tax'],
                                                                    'tax_data' => $values['line_tax_data'] // Since 2.2
                                                                );

                                    
                                    $order->add_product(get_product($var_product), $quantity, $variationsArray);
                                }else{                        
                                   $price_params = array(
                                                        'totals' => array(
                                                                        'subtotal' => $values['line_subtotal'],
                                                                        'subtotal_tax' => $values['line_subtotal_tax'],
                                                                        'total' => $values['line_total'],
                                                                        'tax' => $values['line_tax'],
                                                                        'tax_data' => $values['line_tax_data'] 
                                                                    )
                                                        );

                                    $order->add_product(get_product($product_id), $quantity,$price_params);
                                }
                              
                            } 
                           $order->set_address( $billing_address_array, 'billing' );
                           $order->set_address( $shipping_address_array, 'shipping' ); 

                            if($shipping_method!=""){
                                $shipping_cost = wc_format_decimal($shipping_cost);
                                $shipping_rate = new WC_Shipping_Rate('', $shipping_title,$shipping_cost, "", $shipping_method );
                                $order->add_shipping($shipping_rate);                    
                            }
                            //$order->add_coupon($coupon_code,wc_format_decimal($coupon_amount));
                            $order->calculate_totals();
                            if($coupon_code!="" && $coupon_amount!=""){
                                $order->add_coupon($coupon_code,wc_format_decimal($coupon_amount));
                                $order->set_total($order->calculate_totals() - wc_format_decimal($coupon_amount));
                                $order->set_total($coupon_amount, 'cart_discount');
                            }
                            
                            $order->set_payment_method($payment_obj);
                            $order->update_status('processing');
                            $order_id = $order->get_id();

                        /*custom order creation end*/                

                }
                
                setcookie("order_id",$order_id);
                if (is_null($this->_API)) { 
                    $this->_API = new SplitIt_API($settings); //passing settings to API
                }
                $this->_API->installment_plan_update($order_id,$esi,$ipn);
                if ( !empty($installment_plan_data) ) {
                    update_post_meta( $order_id, 'installment_plan_number', sanitize_text_field( $installment_plan_data->{'PlansList'}[0]->{'InstallmentPlanNumber'} ) );
                    update_post_meta( $order_id, 'number_of_installments', sanitize_text_field( $installment_plan_data->{'PlansList'}[0]->{'NumberOfInstallments'} ) );
                }
                if ( is_wp_error( $order_id ) ) {
                    throw new Exception( $order_id->get_error_message() );
                }

                do_action( 'woocommerce_checkout_order_processed', $order_id, $this->posted );

                // Process payment
                if ( WC()->cart->needs_payment() ) {
                    $success_message = "";
                    $success_message .= "Congratulations you have successfully placed your order.<br/>Please find the details mentioned below.<br/>";
                    $order_details = wc_get_order( $order_id );
                    $success_message .= "Your Order number is #".$order_details->post->ID."<br/>";
                    $success_message .= "Your Installment number is #".$installment_plan_data->{'PlansList'}[0]->{'InstallmentPlanNumber'}."<br/>";
                    
                    $success_message .="Please contact us in case of any query.";
                    // hide success msg 
                    //wc_add_notice( __( $success_message, 'woocommerce' ), 'success' );
                    
                   
                    // Store Order ID in session so it can be re-used after payment failure
                    WC()->session->order_awaiting_payment = $order_id;

                    // Process Payment
                    $result = $available_gateways[ $this->posted['payment_method'] ]->process_payment( $order_id );

                    // Redirect to success/confirmation/payment page
                    if ( $result['result'] == 'success' ) {

                        $result = apply_filters( 'woocommerce_payment_successful_result', $result, $order_id );

                        if ( is_ajax() ) {
                            wp_send_json( $result );
                        } else {
                          //  wp_redirect( $result['redirect'] );
                            exit;
                        }

                    }

                } else {

                    if ( empty( $order ) ) {
                        $order = wc_get_order( $order_id );
                    }



                    // No payment was required for order
                    $order->payment_complete();


                    // Empty the Cart
                    WC()->cart->empty_cart();

                    // Get redirect
                    $return_url = $order->get_checkout_order_received_url();


                    // Redirect to success/confirmation/payment page
                    if ( is_ajax() ) {

                        wp_send_json( array(
                            'result'    => 'success',
                            'redirect'  => apply_filters( 'woocommerce_checkout_no_payment_needed_redirect', $return_url, $order )
                        ) );
                    } else {
                        wp_safe_redirect(
                            apply_filters( 'woocommerce_checkout_no_payment_needed_redirect', $return_url, $order )
                        );
                        exit;
                    }

                }

            } else {
              //  wp_redirect(SplitIt_Helper::sanitize_redirect_url('checkout/'));
            }

        } catch ( Exception $e ) {
            if ( ! empty( $e ) ) {
                wc_add_notice( $e->getMessage(), 'error' );
            }
        }

        // If we reached this point then there were errors
        if ( is_ajax() ) {

            // only print notices if not reloading the checkout, otherwise they're lost in the page reload
            if ( ! isset( WC()->session->reload_checkout ) ) {
                ob_start();
                wc_print_notices();
                $messages = ob_get_clean();
            }

            $response = array(
                'result'    => 'failure',
                'messages'  => isset( $messages ) ? $messages : '',
                'refresh'   => isset( WC()->session->refresh_totals ) ? 'true' : 'false',
                'reload'    => isset( WC()->session->reload_checkout ) ? 'true' : 'false'
            );

            unset( WC()->session->refresh_totals, WC()->session->reload_checkout );

            wp_send_json( $response );
        }
        
    }
    public function async_process_splitit_checkout($checkout_fields, $payment_obj, $installment_plan_data,$ipn,$esi,$settings,$user_id,$cart_items,$shipping_method,$shipping_cost,$shipping_title,$coupon_amount,$coupon_code) {

            /*created orders from the database values*/
                        global $wpdb;
                        $fetch_ipn_data = $ipn;
                        $table_name = $wpdb->prefix . 'splitit_logs';
                        $fetch_cart_details = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM ".$table_name." WHERE ipn =".$fetch_ipn_data ), ARRAY_A );

                        $cart_info = json_decode($fetch_cart_details['wc_cart'],true);
                        $cart_info = $cart_info['cart_contents'];
                        $shipping_method = $fetch_cart_details['shipping_method_id'];
                        $shipping_cost = $fetch_cart_details['shipping_method_cost'];
                        $shipping_title = $fetch_cart_details['shipping_method_title'];
                        $coupon_amount = $fetch_cart_details['coupon_amount'];
                        $coupon_code = $fetch_cart_details['coupon_code'];
                        //print_r($cart_info['cart_contents']);die;
                        $checkout_fields_array = explode('&', $fetch_cart_details['user_data']);
                        $checkout_fields = array();
                        foreach($checkout_fields_array as $row) {
                            $key_value = explode('=', $row);
                            $checkout_fields[$key_value[0]] = $key_value[1];
                        }
                        $billing_address_array = $shipping_address_array = array();   
                        if(isset($checkout_fields['billing_first_name'])){
                             $billing_address_array = array(
                                              'first_name' => $checkout_fields['billing_first_name'],
                                              'last_name'  => $checkout_fields['billing_last_name'],
                                              'company'    => $checkout_fields['billing_company'],
                                              'email'      => $checkout_fields['billing_email'],
                                              'phone'      => $checkout_fields['billing_phone'],
                                              'address_1'  => $checkout_fields['billing_address_1'],
                                              'address_2'  => $checkout_fields['billing_address_2'],
                                              'city'       => $checkout_fields['billing_city'],
                                              'state'      => $checkout_fields['billing_state'],
                                              'postcode'   => $checkout_fields['billing_postcode'],
                                              'country'    => $checkout_fields['billing_country']
                                          );


                        }  
                        if(isset($checkout_fields['ship_to_different_address']) && $checkout_fields['ship_to_different_address']==1 ){
                             $shipping_address_array = array(
                                              'first_name' => $checkout_fields['shipping_first_name'],
                                              'last_name'  => $checkout_fields['shipping_last_name'],
                                              'company'    => $checkout_fields['shipping_company'],
                                              'email'      => $checkout_fields['shipping_email'],
                                              'phone'      => $checkout_fields['shipping_phone'],
                                              'address_1'  => $checkout_fields['shipping_address_1'],
                                              'address_2'  => $checkout_fields['shipping_address_2'],
                                              'city'       => $checkout_fields['shipping_city'],
                                              'state'      => $checkout_fields['shipping_state'],
                                              'postcode'   => $checkout_fields['shipping_postcode'],
                                              'country'    => $checkout_fields['shipping_country']
                                          );


                        }else{
                            $shipping_address_array = $billing_address_array;
                        }

                            //$order = wc_create_order();
                             $order_data = array(
                                             'status' => apply_filters('woocommerce_default_order_status', 'processing'),
                                             'customer_id' => $user_id
                                        );
                            $order = wc_create_order($order_data);
                            foreach ($cart_info as $key => $values) {
                                $product_id = $values['product_id'];
                                $product = wc_get_product($product_id);
                                $quantity = (int)$values['quantity'];
                                if(!empty($values['variation'])){
                                    $var_id = $values['variation_id'];
                                    $var_slug = $values['variation']['attribute_pa_weight'];
                                    $variationsArray = array();
                                    $variationsArray['variation'] = array(
                                      'pa_weight' => $var_slug
                                    );
                                    $var_product = new WC_Product_Variation($var_id);
                                   $variationsArray['totals'] = array(
                                                                    'subtotal' => $values['line_subtotal'],
                                                                    'subtotal_tax' => $values['line_subtotal_tax'],
                                                                    'total' => $values['line_total'],
                                                                    'tax' => $values['line_tax'],
                                                                    'tax_data' => $values['line_tax_data'] // Since 2.2
                                                                );

                                    
                                    $order->add_product(get_product($var_product), $quantity, $variationsArray);
                                }else{                        
                                   $price_params = array(
                                                        'totals' => array(
                                                                        'subtotal' => $values['line_subtotal'],
                                                                        'subtotal_tax' => $values['line_subtotal_tax'],
                                                                        'total' => $values['line_total'],
                                                                        'tax' => $values['line_tax'],
                                                                        'tax_data' => $values['line_tax_data'] 
                                                                    )
                                                        );

                                    $order->add_product(get_product($product_id), $quantity,$price_params);
                                }
                              
                            } 
                           $order->set_address( $billing_address_array, 'billing' );
                           $order->set_address( $shipping_address_array, 'shipping' ); 

                            if($shipping_method!=""){
                                $shipping_cost = wc_format_decimal($shipping_cost);
                                $shipping_rate = new WC_Shipping_Rate('', $shipping_title,$shipping_cost, "", $shipping_method );
                                $order->add_shipping($shipping_rate);                    
                            }
                            //$order->add_coupon($coupon_code,wc_format_decimal($coupon_amount));
                            $order->calculate_totals();
                            if($coupon_code!="" && $coupon_amount!=""){
                                $order->add_coupon($coupon_code,wc_format_decimal($coupon_amount));
                                $order->set_total($order->calculate_totals() - wc_format_decimal($coupon_amount));
                                $order->set_total($coupon_amount, 'cart_discount');
                            }
                            
                            $order->set_payment_method($payment_obj);
                            $order->update_status('processing');
                            $order_id = $order->get_id();

                        /*custom order creation end*/                




            /*end*/


                setcookie("order_id",$order_id);
                
                if (is_null($this->_API)) { 
                        $this->_API = new SplitIt_API($settings); //passing settings to API
                    }
                    $this->_API->installment_plan_update($order_id,$esi,$ipn);
              
                if ( !empty($installment_plan_data) ) {
                  update_post_meta( $order_id, 'installment_plan_number', sanitize_text_field( $installment_plan_data->{'PlansList'}[0]->{'InstallmentPlanNumber'} ) );
                   update_post_meta( $order_id, 'number_of_installments', sanitize_text_field( $installment_plan_data->{'PlansList'}[0]->{'NumberOfInstallments'} ) );
                }

                if ( is_wp_error( $order_id ) ) {
                    throw new Exception( $order_id->get_error_message() );
                }

                do_action( 'woocommerce_checkout_order_processed', $order_id, $this->posted );

                // Process payment
              

                    if ( empty( $order ) ) {
                        $order = wc_get_order( $order_id );
                    }



                    // No payment was required for order
                    $order->payment_complete();


                    // Empty the Cart
                    WC()->cart->empty_cart();

                    // Get redirect
                    $return_url = $order->get_checkout_order_received_url();


                    // Redirect to success/confirmation/payment page
                    if ( is_ajax() ) {

                        wp_send_json( array(
                            'result'    => 'success',
                            'redirect'  => apply_filters( 'woocommerce_checkout_no_payment_needed_redirect', $return_url, $order )
                        ) );
                    } else {
                        wp_safe_redirect(
                            apply_filters( 'woocommerce_checkout_no_payment_needed_redirect', $return_url, $order )
                        );
                        exit;
                    }

                

           

       

        // If we reached this point then there were errors
        if ( is_ajax() ) {

            // only print notices if not reloading the checkout, otherwise they're lost in the page reload
            if ( ! isset( WC()->session->reload_checkout ) ) {
                ob_start();
                wc_print_notices();
                $messages = ob_get_clean();
            }

            $response = array(
                'result'    => 'failure',
                'messages'  => isset( $messages ) ? $messages : '',
                'refresh'   => isset( WC()->session->refresh_totals ) ? 'true' : 'false',
                'reload'    => isset( WC()->session->reload_checkout ) ? 'true' : 'false'
            );

            unset( WC()->session->refresh_totals, WC()->session->reload_checkout );

            wp_send_json( $response );
        }
        
    }
}

?>
