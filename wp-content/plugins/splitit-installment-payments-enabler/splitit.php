<?php

/*
Plugin Name: Splitit
Plugin URI: http://wordpress.org/plugins/splitit/
Description: Integrates Splitit payment method into your WooCommerce installation.
Version: 2.0.9
Author: Splitit
Text Domain: splitit
Author URI: https://www.splitit.com/
*/

if(is_admin()){

    error_reporting(0);
}

add_action('plugins_loaded', 'init_splitit_method', 0);

function add_notice_function(){
    if(is_checkout()==false && is_cart() ==false ){
        wc_print_notices();
    }
}

/*code to create new table and maintain IPN logss for Async*/
function create_plugin_database_table()
{
    global $wpdb;

    $table_name = $wpdb->prefix . 'splitit_logs';
    if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
        $charset_collate = $wpdb->get_charset_collate();
        $sql = "CREATE TABLE ".$table_name." (
             `id` int(11) NOT NULL AUTO_INCREMENT,
             `user_id` int(11) DEFAULT 0,
              `shipping_method_cost` varchar(255) DEFAULT NULL,
              `shipping_method_title` varchar(255) DEFAULT NULL,
              `shipping_method_id` varchar(255) DEFAULT NULL,
              `coupon_amount` varchar(255) DEFAULT NULL,
              `coupon_code` varchar(255) DEFAULT NULL,
              `tax_amount` varchar(255) DEFAULT NULL,
              `set_shipping_total` varchar(255) DEFAULT NULL,
              `set_discount_total` varchar(255) DEFAULT NULL,
              `set_discount_tax` varchar(255) DEFAULT NULL,
              `set_cart_tax` varchar(255) DEFAULT NULL,
              `set_shipping_tax` varchar(255) DEFAULT NULL,
              `set_total` varchar(255) DEFAULT NULL, 
              `wc_cart` longtext,
              `get_packages` longtext,
              `chosen_shipping_methods_data` longtext,
              `ipn` varchar(255) DEFAULT NULL,
              `session_id` varchar(255) DEFAULT NULL,
              `user_data` longtext,
              `cart_items` longtext,
              `updated_at` datetime NOT NULL,               
              PRIMARY KEY (`id`)
        ) $charset_collate;";

      //  echo $sql;die;

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $sql );
    }else{
        $row = $wpdb->get_results( "SELECT shipping_method_cost FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = ".$table_name." AND column_name = 'shipping_method_cost'"  );
        if(empty($row)){
           $wpdb->query("ALTER TABLE ".$table_name." ADD COLUMN `shipping_method_cost` varchar(255) DEFAULT NULL");
        }
        $row = $wpdb->get_results( "SELECT shipping_method_title FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = ".$table_name." AND column_name = 'shipping_method_title'"  );
        if(empty($row)){
           $wpdb->query("ALTER TABLE ".$table_name." ADD COLUMN `shipping_method_title` varchar(255) DEFAULT NULL");
        }
        $row = $wpdb->get_results( "SELECT shipping_method_id FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = ".$table_name." AND column_name = 'shipping_method_id'"  );
        if(empty($row)){
           $wpdb->query("ALTER TABLE ".$table_name." ADD COLUMN `shipping_method_id` varchar(255) DEFAULT NULL");
        }
        $row = $wpdb->get_results( "SELECT coupon_amount FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = ".$table_name." AND column_name = 'coupon_amount'"  );
        if(empty($row)){
           $wpdb->query("ALTER TABLE ".$table_name." ADD COLUMN `coupon_amount` varchar(255) DEFAULT NULL");
        }
        $row = $wpdb->get_results( "SELECT coupon_code FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = ".$table_name." AND column_name = 'coupon_code'"  );
        if(empty($row)){
           $wpdb->query("ALTER TABLE ".$table_name." ADD COLUMN `coupon_code` varchar(255) DEFAULT NULL");
        }
        $row = $wpdb->get_results( "SELECT tax_amount FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = ".$table_name." AND column_name = 'tax_amount'"  );
        if(empty($row)){
           $wpdb->query("ALTER TABLE ".$table_name." ADD COLUMN `tax_amount` varchar(255) DEFAULT NULL");
        }
        $row = $wpdb->get_results( "SELECT set_shipping_total FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = ".$table_name." AND column_name = 'set_shipping_total'"  );
        if(empty($row)){
           $wpdb->query("ALTER TABLE ".$table_name." ADD COLUMN `set_shipping_total` varchar(255) DEFAULT NULL");
        }
        $row = $wpdb->get_results( "SELECT set_discount_total FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = ".$table_name." AND column_name = 'set_discount_total'"  );
        if(empty($row)){
           $wpdb->query("ALTER TABLE ".$table_name." ADD COLUMN `set_discount_total` varchar(255) DEFAULT NULL");
        }
        $row = $wpdb->get_results( "SELECT set_discount_tax FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = ".$table_name." AND column_name = 'set_discount_tax'"  );
        if(empty($row)){
           $wpdb->query("ALTER TABLE ".$table_name." ADD COLUMN `set_discount_tax` varchar(255) DEFAULT NULL");
        }
        $row = $wpdb->get_results( "SELECT set_cart_tax FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = ".$table_name." AND column_name = 'set_cart_tax'"  );
        if(empty($row)){
           $wpdb->query("ALTER TABLE ".$table_name." ADD COLUMN `set_cart_tax` varchar(255) DEFAULT NULL");
        }
        $row = $wpdb->get_results( "SELECT set_shipping_tax FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = ".$table_name." AND column_name = 'set_shipping_tax'"  );
        if(empty($row)){
           $wpdb->query("ALTER TABLE ".$table_name." ADD COLUMN `set_shipping_tax` varchar(255) DEFAULT NULL");
        }
        $row = $wpdb->get_results( "SELECT set_total FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = ".$table_name." AND column_name = 'set_total'"  );
        if(empty($row)){
           $wpdb->query("ALTER TABLE ".$table_name." ADD COLUMN `set_total` varchar(255) DEFAULT NULL");
        }
        $row = $wpdb->get_results( "SELECT wc_cart FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = ".$table_name." AND column_name = 'wc_cart'"  );
        if(empty($row)){
           $wpdb->query("ALTER TABLE ".$table_name." ADD COLUMN `wc_cart` longtext DEFAULT NULL");
        }
        $row = $wpdb->get_results( "SELECT get_packages FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = ".$table_name." AND column_name = 'get_packages'"  );
        if(empty($row)){
           $wpdb->query("ALTER TABLE ".$table_name." ADD COLUMN `get_packages` longtext DEFAULT NULL");
        }
      
        $row = $wpdb->get_results( "SELECT chosen_shipping_methods_data FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = ".$table_name." AND column_name = 'chosen_shipping_methods_data'"  );
        if(empty($row)){
           $wpdb->query("ALTER TABLE ".$table_name." ADD COLUMN `chosen_shipping_methods_data` longtext DEFAULT NULL");
        }



    }



}

//register_activation_hook( __FILE__, 'create_plugin_database_table' );
add_action( "admin_init", 'create_plugin_database_table' );
/*end*/

function init_splitit_method(){


    add_action('wp_head', 'add_notice_function');

    if ( ! class_exists( 'WC_Payment_Gateway' )) { return; }

    define( 'Splitit_VERSION', '2.0.9' );

    // Import helper classes
    require_once('classes/splitit-log.php');
    require_once('classes/splitit-settings.php');
    require_once('classes/splitit-helper.php');
    require_once('classes/splitit-api.php');
    require_once('classes/splitit-checkout.php');

    // Main class
    class SplitIt extends WC_Payment_Gateway
    {

        /**
         * $_instance
         * @var mixed
         * @access public
         * @static
         */
        public static $_instance = NULL;
        private static $_maxInstallments = NULL;
        protected $_API = null;

        /**
         * Returns a new instance of self, if it does not already exist.
         *
         * @access public
         * @static
         * @return object SplitIt
         */
        public static function get_instance()
        {
            if (is_null(self::$_instance)) {
                self::$_instance = new self();
            }
            return self::$_instance;
        }

        public $log;

        /**
         * The class construct
         *
         * @access public
         */
        public function __construct()
        {
            $this->id = 'splitit';
            $this->method_title = 'Splitit'; //checkout payment method tab title
            $this->icon = '';
            $this->order_button_text  = __( ' Proceed to Monthly Payment', 'woocommerce' );
            $this->has_fields = false; //Can be set to true if you want payment fields to show on the checkout (if doing a direct integration).
            $this->test_item = "testing";
            $this->supports = array(
                'products',
//                'refunds'
            );

            $this->log = new SplitIt_Log();

            // Load the form fields and settings
            $this->init_form_fields();
            $this->init_settings();

            // Get gateway variables: displayed as payment method title and description on front
            //$this->title = $this->s('title') . '<span id="pis_anchor" style="display:none;"></span>';
            $this->title = $this->s('title');
            $this->description = $this->s('description');
            $this->instructions = $this->s('instructions');
            // if($this->s('splitit_max_installments') && $this->s('splitit_max_installments') != '' && $this->s('splitit_max_installments') <= $this->s('splitit_max_installments_limit')) {
            //     self::$_maxInstallments = $this->settings['splitit_max_installments'];
            // } else {
                self::$_maxInstallments = $this->s('splitit_max_installments_limit'); //set maximum installments //number by default
           // }
        }

        /**
         * Initiates the plugin settings form fields
         *
         * @access public
         * @return array
         */
        public function init_form_fields()
        {
            $this->form_fields = SplitIt_Settings::get_fields();
        }

        /**
         * Applies plugin hooks and filters
         *
         * @access public
         * @return string
         */
        public function hooks_and_filters()
        {
            //TODO: Translation?
            //add_action( 'init', 'Splitit_Helper::load_i18n' );
            //admin functions init
            if( is_admin() ) {
                add_action( 'admin_enqueue_scripts', 'SplitIt_Helper::admin_js' );
                //add_action( 'wp_ajax_my_action', 'check_credentials' );
                add_action( 'wp_ajax_my_action', array($this, 'splitit_check_api_credentials') );
                add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options' ) );
                add_action( 'woocommerce_admin_order_data_after_billing_address', array($this, 'splitit_add_installment_plan_number_data'), 10, 1 );

                //order shipped logic
                add_action( 'init', array($this,'splitit_register_shipped_order_status'));
                if($this->s('splitit_payment_action') == 'shipped') {
                    add_action('woocommerce_order_actions', array($this, 'splitit_add_order_meta_box_actions'));
                }
                add_filter( 'wc_order_statuses', array($this,'splitit_add_shipped_to_order_statuses') );
                //Add callback if Shipped action called
                add_action( 'woocommerce_order_action_charge', array($this, 'splitit_customer_charge_callback' ), 10, 1);
                //Add callback if Status changed to Shipping
                add_action('woocommerce_order_status_shipped', array($this,'splitit_order_status_shipped_callback'), 10, 1);
            }

            //API request handlers
            add_action( 'woocommerce_api_splitit_payment_cancel', array( $this, 'splitit_payment_cancel' ) );
            add_action( 'woocommerce_api_splitit_payment_success', array( $this, 'splitit_payment_success' ) );
            add_action( 'woocommerce_api_splitit_payment_error', array( $this, 'splitit_payment_error' ) );
            add_action( 'woocommerce_api_splitit_payment_success_async', array( $this, 'splitit_payment_success_async' ) );
          
            add_action( 'woocommerce_api_splitit_checkout_validate', array( $this, 'splitit_checkout_validate' ) );
            add_action( 'woocommerce_api_splitit_help', array( $this, 'splitit_help' ) );

            //SplitIt session init and button inserting, gateway cc icons
            add_action( 'woocommerce_after_checkout_form', 'SplitIt_Helper::checkout_js' );
            add_action( 'woocommerce_after_checkout_form', array($this, 'splitit_pass_cdn_urls'));
            add_action( 'woocommerce_api_splitit_scripts_on_checkout', array( $this, 'splitit_scripts_on_checkout' ) );
            add_filter( 'woocommerce_gateway_icon', array( $this, 'splitit_gateway_icons' ), 2, 3 );
            if($this->s('splitit_discount_type') == 'depending_on_cart_total') {
                add_filter( 'woocommerce_available_payment_gateways', array( $this, 'change_payment_gateway' ), 20, 1 );
            }

            //Installment price functionality init
            if($this->s('splitit_enable_installment_price') == 'yes') {
                add_filter('woocommerce_get_price_html', array($this, 'splitit_installment_price'), 10, 3);
                add_filter('woocommerce_get_price', array($this, 'splitit_installment_price'), 10, 3);
                add_filter('woocommerce_get_regular_price', array($this, 'splitit_installment_price'), 10, 3);
                add_filter('woocommerce_get_sale_price', array($this, 'splitit_installment_price'), 10, 3);
                add_filter('woocommerce_order_amount_item_subtotal', array($this, 'splitit_installment_price'), 10, 3);
                add_filter('woocommerce_cart_product_price', array($this, 'splitit_installment_price'), 10, 3); //cart price column
                add_filter('woocommerce_cart_total', array($this, 'splitit_installment_total_price'), 10, 3); //cart and checkout totals
            }

            //Debug mode init
            if($this->s('splitit_mode_debug') == 'yes') {
                add_action('http_api_debug', array($this, 'splitit_api_request_debug' ), 10, 5);
            }

            //hook for using is_checkout() inside plugins page - triggers right after template become available
            //add_action('woocommerce_after_checkout_form', array($this, 'splitit_scripts_on_checkout'));

            //front styles
            add_action('wp_enqueue_scripts', 'SplitIt_Helper::front_css' );
            
        }
        /**
         * Adds action links inside the plugin overview
         *
         * @access public static
         * @return array
         */
        public static function add_action_links( $links )
        {
            $links = array_merge( array(
                '<a href="' . admin_url( 'admin.php?page=wc-settings&tab=checkout&section=splitit' ) . '">' . __( 'Settings', 'splitit' ) . '</a>',
            ), $links );

            return $links;
        }

        /**
         * Prints the admin settings form
         *
         * @access public
         * @return string
         */
        public function admin_options()
        {
            echo "<h3>Splitit, v" . Splitit_VERSION . "</h3>";
            echo '<a target="_blank" href="https://www.splitit.com/register?source=woo_plugin">' . __('Click here to sign up for a Splitit account.', 'splitit') . '</a>';

            do_action('splitit_settings_table_before');

            echo "<table class=\"form-table\">";
            $this->generate_settings_html();
            echo "</table";

            do_action('splitit_settings_table_after');
        }


        public function generate_settings_html( $form_fields = array(), $echo = true ) { 
            if ( empty( $form_fields ) ) {
                $form_fields = $this->get_form_fields();
            }

            $html = '';
            foreach ( $form_fields as $k => $v ) {
                if($k == 'splitit_doct'){ 
                    $html .= '<tr valign="top" class="custom_settings" id="main_ct_container"> 
                            <th>Depending on cart total</th> 
                            <td>
                                <table id = "tier_price_container">
                                    <tr>   
                                        <th>From</th> 
                                        <th>To</th> 
                                        <th>Installment</th> 
                                        <th>Currency</th>
                                    </tr> 
                                ';
                    foreach ( $v as $k1 => $v1 ) { 
                        $i=0;
                         
                        if(count((array)$v1) == 4 ){
                            if($k1 == 0){
                                 
                            } 
                            foreach ( (array)$v1 as $k2 => $v2 ) {  
                                if($i == 0){ 
                                    $html .= '<tr class="ct_tr">';
                                }   
                                $type = $this->get_field_type( $v2 );

                                if ( method_exists( $this, 'generate_' . $type . '_html' ) ) {
                                    $html .= $this->{'generate_' . $type . '_html'}( $k2, $v2, 'cart_total', $k1 );
                                } else {
                                    $html .= $this->generate_text_html( $k2, $v2, 'cart_total', $k1 );
                                }
                                
                                if($i == 3){
                                    $html .= '</tr>';
                                } 
                                $i++;
                            }  
                        }
                    }
                    $html .= '</table>
                            </td> 
                            </tr>';
                }else{
                    $type = $this->get_field_type( $v );

                    if ( method_exists( $this, 'generate_' . $type . '_html' ) ) {
                        $html .= $this->{'generate_' . $type . '_html'}( $k, $v );
                    } else {
                        $html .= $this->generate_text_html( $k, $v, '', '' );
                    }
                }
            }

            /*foreach ( $form_fields as $k => $v ) {
                $type = $this->get_field_type( $v );

                if ( method_exists( $this, 'generate_' . $type . '_html' ) ) {
                    $html .= $this->{'generate_' . $type . '_html'}( $k, $v );
                } else {
                    $html .= $this->generate_text_html( $k, $v );
                }
            }*/

            if ( $echo ) {
                echo $html;
            } else {
                return $html;
            }
        }

        public function generate_text_html( $key, $data, $ct = '', $i = '' ) {  

            if($ct == 'cart_total'){
                $field_key = $this->get_field_key( $key ); 
                //echo '<pre>';print_r($this->settings['splitit_doct']);die;
                //echo $key.'--'.$i.'--'.$this->settings['splitit_doct'][$key][$i].'<br/>';
                $defaults  = array(
                    'title'             => '',
                    'disabled'          => false,
                    'class'             => '',
                    'css'               => '',
                    'placeholder'       => '',
                    'type'              => 'text',
                    'desc_tip'          => false,
                    'description'       => '',
                    'custom_attributes' => array(),
                );

                $data = wp_parse_args( $data, $defaults );

                ob_start();
                $readonly = "";
                if($key == 'ct_currency'){
                    $txtValue = get_woocommerce_currency();   
                    $readonly = "readonly";                 
                }else{
                    if(isset($this->settings['splitit_doct'][$key][$i])){
                        $txtValue = $this->settings['splitit_doct'][$key][$i];
                    }else{
                        $txtValue = $data['default'];
                    }
                }
                
                ?> 
                <td style="padding:0;">
                    <fieldset> 
                        <input width="5" class="input-text <?php echo esc_attr( $data['class'] ); ?>" type="<?php echo esc_attr( $data['type'] ); ?>" <?php echo $readonly; ?> name="<?php echo esc_attr( $field_key ); ?>[]" id="<?php echo esc_attr( $field_key ); ?>" style="<?php echo esc_attr( $data['css'] ); ?>" value="<?php echo $txtValue; ?>" placeholder="<?php echo esc_attr( $data['placeholder'] ); ?>" <?php disabled( $data['disabled'], true ); ?> <?php echo $this->get_custom_attribute_html( $data ); ?> />
                        <?php echo $this->get_description_html( $data ); ?>
                    </fieldset>
                </td> 
                <?php

                return ob_get_clean();
            }else{  
                $field_key = $this->get_field_key( $key ); 
                $defaults  = array(
                    'title'             => '',
                    'disabled'          => false,
                    'class'             => '',
                    'css'               => '',
                    'placeholder'       => '',
                    'type'              => 'text',
                    'desc_tip'          => false,
                    'description'       => '',
                    'custom_attributes' => array(),
                );

                $data = wp_parse_args( $data, $defaults );

                ob_start();
                ?>
                <tr valign="top">
                    <th scope="row" class="titledesc">
                        <label for="<?php echo esc_attr( $field_key ); ?>"><?php echo wp_kses_post( $data['title'] ); ?></label>
                        <?php echo $this->get_tooltip_html( $data ); ?>
                    </th>
                    <td class="forminp">
                        <fieldset>
                            <legend class="screen-reader-text"><span><?php echo wp_kses_post( $data['title'] ); ?></span></legend>
                            <input class="input-text regular-input <?php echo esc_attr( $data['class'] ); ?>" type="<?php echo esc_attr( $data['type'] ); ?>" name="<?php echo esc_attr( $field_key ); ?>" id="<?php echo esc_attr( $field_key ); ?>" style="<?php echo esc_attr( $data['css'] ); ?>" value="<?php echo esc_attr( $this->get_option( $key ) ); ?>" placeholder="<?php echo esc_attr( $data['placeholder'] ); ?>" <?php disabled( $data['disabled'], true ); ?> <?php echo $this->get_custom_attribute_html( $data ); ?> />
                            <?php echo $this->get_description_html( $data ); ?>
                        </fieldset>
                    </td>
                </tr>
                <?php

                return ob_get_clean();
            }
        }

        public function generate_select_html( $key, $data, $ct = '', $i = '' ) {
            if($ct == 'cart_total'){
                $field_key = $this->get_field_key( $key );
                //echo $key.'--'.$i.'--'.$this->settings['splitit_doct'][$key][$i].'<br/>';
                $defaults  = array(
                    'title'             => '',
                    'disabled'          => false,
                    'class'             => '',
                    'css'               => '',
                    'placeholder'       => '',
                    'type'              => 'text',
                    'desc_tip'          => false,
                    'description'       => '',
                    'custom_attributes' => array(),
                    'options'           => array(),
                );

                $data = wp_parse_args( $data, $defaults );

                ob_start();
                ?> 
                <td style="padding:0;">
                    <fieldset> 
                        <select class="select <?php echo esc_attr( $data['class'] ); ?>" name="<?php echo esc_attr( $field_key ); ?>[]" id="<?php echo esc_attr( $field_key ); ?>" style="<?php echo esc_attr( $data['css'] ); ?>" <?php disabled( $data['disabled'], true ); ?> <?php echo $this->get_custom_attribute_html( $data ); ?>>
                            <?php foreach ( (array) $data['options'] as $option_key => $option_value ) : ?>
                                <option value="<?php echo esc_attr( $option_key ); ?>" <?php selected( $option_key, esc_attr( $this->settings['splitit_doct'][$key][$i] ) ); ?>><?php echo esc_attr( $option_value ); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <?php echo $this->get_description_html( $data ); ?>
                    </fieldset>
                </td> 
                <?php

                return ob_get_clean();
            }else{
                $field_key = $this->get_field_key( $key );
                $defaults  = array(
                    'title'             => '',
                    'disabled'          => false,
                    'class'             => '',
                    'css'               => '',
                    'placeholder'       => '',
                    'type'              => 'text',
                    'desc_tip'          => false,
                    'description'       => '',
                    'custom_attributes' => array(),
                    'options'           => array(),
                );

                $data = wp_parse_args( $data, $defaults );

                ob_start();
                ?>
                <tr valign="top">
                    <th scope="row" class="titledesc">
                        <label for="<?php echo esc_attr( $field_key ); ?>"><?php echo wp_kses_post( $data['title'] ); ?></label>
                        <?php echo $this->get_tooltip_html( $data ); ?>
                    </th>
                    <td class="forminp">
                        <fieldset>
                            <legend class="screen-reader-text"><span><?php echo wp_kses_post( $data['title'] ); ?></span></legend>
                            <select class="select <?php echo esc_attr( $data['class'] ); ?>" name="<?php echo esc_attr( $field_key ); ?>" id="<?php echo esc_attr( $field_key ); ?>" style="<?php echo esc_attr( $data['css'] ); ?>" <?php disabled( $data['disabled'], true ); ?> <?php echo $this->get_custom_attribute_html( $data ); ?>>
                                <?php foreach ( (array) $data['options'] as $option_key => $option_value ) : ?>
                                    <option value="<?php echo esc_attr( $option_key ); ?>" <?php selected( $option_key, esc_attr( $this->get_option( $key ) ) ); ?>><?php echo esc_attr( $option_value ); ?></option>
                                <?php endforeach; ?>
                            </select>
                            <?php echo $this->get_description_html( $data ); ?>
                        </fieldset>
                    </td>
                </tr>
                <?php

                return ob_get_clean();
            }
        }

        public function generate_multiselect_html( $key, $data, $ct = '', $i = ''  ) {
            if($ct == 'cart_total'){
                $field_key = $this->get_field_key( $key );
                $defaults  = array(
                    'title'             => '',
                    'disabled'          => false,
                    'class'             => '',
                    'css'               => '',
                    'placeholder'       => '',
                    'type'              => 'text',
                    'desc_tip'          => false,
                    'description'       => '',
                    'custom_attributes' => array(),
                    'select_buttons'    => false,
                    'options'           => array(),
                );
                $data  = wp_parse_args( $data, $defaults );
                $value = (array) $this->get_option( $key, array() ); 
                ob_start();
                if($key == 'ct_instllment'){
                    if(isset($this->settings['splitit_doct'][$key][$i])){
                        $mulSelValue = $this->settings['splitit_doct'][$key][$i];
                    }else{
                        $mulSelValue = $data['default'];
                    }                
                }else{
                    $mulSelValue = $this->settings['splitit_doct'][$key][$i];
                }
                ?> 
                    <td class="forminp">
                        <fieldset>
                            <legend class="screen-reader-text"><span><?php echo wp_kses_post( $data['title'] ); ?></span></legend>
                            <select multiple="multiple" class="multiselect <?php echo esc_attr( $data['class'] ); ?>" name="<?php echo esc_attr( $field_key ).'_'.$i; ?>[]" id="<?php echo esc_attr( $field_key ); ?>" style="<?php echo esc_attr( $data['css'] ); ?>" <?php disabled( $data['disabled'], true ); ?> <?php echo $this->get_custom_attribute_html( $data ); ?>>
                                <?php if(isset($data)){
                                foreach ( (array) $data['options'] as $option_key => $option_value ) : ?>
                                    <option value="<?php echo esc_attr( $option_key ); ?>" <?php if(count($this->settings['splitit_doct'][$key][$i])>0){ selected( in_array( $option_key, $mulSelValue ), true ); } ?>><?php echo esc_attr( $option_value ); ?></option>
                                <?php endforeach; } ?>
                            </select>
                            <?php echo $this->get_description_html( $data ); ?>
                            <?php if ( $data['select_buttons'] ) : ?>
                                <br/><a class="select_all button" href="#"><?php _e( 'Select all', 'woocommerce' ); ?></a> <a class="select_none button" href="#"><?php _e( 'Select none', 'woocommerce' ); ?></a>
                            <?php endif; ?>
                        </fieldset>
                    </td> 
                <?php

                return ob_get_clean();
            }else{

                $field_key = $this->get_field_key( $key );
                $defaults  = array(
                    'title'             => '',
                    'disabled'          => false,
                    'class'             => '',
                    'css'               => '',
                    'placeholder'       => '',
                    'type'              => 'text',
                    'desc_tip'          => false,
                    'description'       => '',
                    'custom_attributes' => array(),
                    'select_buttons'    => false,
                    'options'           => array(),
                );

                $data  = wp_parse_args( $data, $defaults );
                $value = (array) $this->get_option( $key, array() );
                if($key == 'splitit_cc' && count($value)<=0){
                    $value = $data['default'];
                }

                ob_start();
                ?>
                <tr valign="top">
                    <th scope="row" class="titledesc">
                        <label for="<?php echo esc_attr( $field_key ); ?>"><?php echo wp_kses_post( $data['title'] ); ?></label>
                        <?php echo $this->get_tooltip_html( $data ); ?>
                    </th>
                    <td class="forminp">
                        <fieldset>
                            <legend class="screen-reader-text"><span><?php echo wp_kses_post( $data['title'] ); ?></span></legend>
                            <select multiple="multiple" class="multiselect <?php echo esc_attr( $data['class'] ); ?>" name="<?php echo esc_attr( $field_key ); ?>[]" id="<?php echo esc_attr( $field_key ); ?>" style="<?php echo esc_attr( $data['css'] ); ?>" <?php disabled( $data['disabled'], true ); ?> <?php echo $this->get_custom_attribute_html( $data ); ?>>
                                <?php foreach ( (array) $data['options'] as $option_key => $option_value ) : ?>
                                    <option value="<?php echo esc_attr( $option_key ); ?>" <?php selected( in_array( $option_key, $value ), true ); ?>><?php echo esc_attr( $option_value ); ?></option>
                                <?php endforeach; ?>
                            </select>
                            <?php echo $this->get_description_html( $data ); ?>
                            <?php if ( $data['select_buttons'] ) : ?>
                                <br/><a class="select_all button" href="#"><?php _e( 'Select all', 'woocommerce' ); ?></a> <a class="select_none button" href="#"><?php _e( 'Select none', 'woocommerce' ); ?></a>
                            <?php endif; ?>
                        </fieldset>
                    </td>
                </tr>
                <?php

                return ob_get_clean();
            }
        }

        public function process_admin_options() {
            $this->init_settings();

            $post_data = $this->get_post_data();
             
            foreach ( $this->get_form_fields() as $key => $field ) { 
                
                if ( 'title' !== $this->get_field_type( $field ) ) {
                    try {
                        if($key == 'splitit_doct'){ 
                            if(isset($post_data['woocommerce_splitit_ct_instllment_0'])){
                                $instArr[] = $post_data['woocommerce_splitit_ct_instllment_0'];
                            }else{
                                $instArr[] = array();
                            }
                            if(isset($post_data['woocommerce_splitit_ct_instllment_1'])){
                                $instArr[] = $post_data['woocommerce_splitit_ct_instllment_1'];
                            }else{
                                $instArr[] = array();
                            }
                            if(isset($post_data['woocommerce_splitit_ct_instllment_2'])){
                                $instArr[] = $post_data['woocommerce_splitit_ct_instllment_2'];
                            }else{
                                $instArr[] = array();
                            }
                            if(isset($post_data['woocommerce_splitit_ct_instllment_3'])){
                                $instArr[] = $post_data['woocommerce_splitit_ct_instllment_3'];
                            }else{
                                $instArr[] = array();
                            }
                            if(isset($post_data['woocommerce_splitit_ct_instllment_4'])){
                                $instArr[] = $post_data['woocommerce_splitit_ct_instllment_4'];
                            }else{
                                $instArr[] = array();
                            }
                            $newArr = array(
                                'ct_from' => $post_data['woocommerce_splitit_ct_from'],
                                'ct_to' => $post_data['woocommerce_splitit_ct_to'],
                                'ct_instllment' => $instArr,
                                'ct_currency' => $post_data['woocommerce_splitit_ct_currency']
                                );
                            $this->settings[ $key ] = $newArr; 
                        }else{ 
                            $this->settings[ $key ] = $this->get_field_value( $key, $field, $post_data );
                        }
                    } catch ( Exception $e ) {
                        $this->add_error( $e->getMessage() );
                    }
                }
            }
            //echo '<pre>';print_r($this->settings);die;
            return update_option( $this->get_option_key(), apply_filters( 'woocommerce_settings_api_sanitized_fields_' . $this->id, $this->settings ) );
        }

        //----------------------------------
        //START FOR WOOCOMMERCE VERION 2.5.3
        //----------------------------------
        /*
        public function get_field_type( $field ) {
            return empty( $field['type'] ) ? 'text' : $field['type'];
        }

        public function get_post_data() {
            if ( ! empty( $this->data ) && is_array( $this->data ) ) {
                return $this->data;
            }
            return $_POST;
        }

        public function get_field_value( $key, $field, $post_data = array() ) {
            $type      = $this->get_field_type( $field );
            $field_key = $this->get_field_key( $key );
            $post_data = empty( $post_data ) ? $_POST : $post_data;
            $value     = isset( $post_data[ $field_key ] ) ? $post_data[ $field_key ] : null;

            // Look for a validate_FIELDID_field method for special handling
            if ( is_callable( array( $this, 'validate_' . $key . '_field' ) ) ) {
                return $this->{'validate_' . $key . '_field'}( $key, $value );
            }

            // Look for a validate_FIELDTYPE_field method
            if ( is_callable( array( $this, 'validate_' . $type . '_field' ) ) ) {
                return $this->{'validate_' . $type . '_field'}( $key, $value );
            }

            // Fallback to text
            return $this->validate_text_field( $key, $value );
        }

        public function get_option_key() {
            return $this->plugin_id . $this->id . '_settings';
        }*/
        //---------------------------------
        //ENDS FOR WOOCOMMERCE VERION 2.5.3
        //---------------------------------

        /**
         * Prints out the description of the gateway. Also adds two checkboxes for viaBill/creditcard for customers to choose how to pay.
         *
         * @access public
         * @return void
         */
        public function payment_fields()
        {
            if ( $this->description) echo wptexturize( $this->description );
            echo '<div class="powered-by-splitit"><img src="data:image/png;base64,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"/></div>';
        }


        /***************************************************************************************************************
         * Purchase on shipped logic
         **************************************************************************************************************/

        /**
         * Add Order action to Order action meta box
         */

        public function splitit_add_order_meta_box_actions($actions)
        {
            $actions['charge'] = __( '[Splitit] Charge customer', 'splitit');
            return $actions;
        }

        /**
         * Register new status
         */
        public function splitit_register_shipped_order_status()
        {
            register_post_status( 'wc-shipped', array(
                'label' => __('Shipped','splitit'),
                'public' => true,
                'exclude_from_search' => false,
                'show_in_admin_all_list' => true,
                'show_in_admin_status_list' => true,
                'label_count' => _n_noop( 'Shipped <span class="count">(%s)</span>', 'Shipped <span class="count">(%s)</span>' )
            ));
        }

        /**
         * Adds new Order status - Shipped in Order statuses
         */
        public function splitit_add_shipped_to_order_statuses($order_statuses)
        {
            $new_order_statuses = array();
            // add new order status after Completed
            foreach ( $order_statuses as $key => $status )
            {
                $new_order_statuses[ $key ] = $status;
                if ( 'wc-completed' === $key )
                {
                    $new_order_statuses['wc-shipped'] = __('Shipped','splitit');
                }
            }
            return $new_order_statuses;
        }

        public function splitit_customer_charge_callback($order)
        {
            if($this->s('splitit_payment_action') == 'shipped') {
                //Here order id is sent as parameter
                $order_meta = get_post_custom($order->id);
                if (isset($order_meta['installment_plan_number']) && !empty($order_meta['installment_plan_number'][0])) {
                    if (is_null($this->_API)) {
                        $this->_API = new SplitIt_API($this->settings); //passing settings to API
                    }
                    $session = $this->_API->login();
                    $result = $this->_API->capture($order_meta['installment_plan_number'][0], $session);

                    if(is_array($result) && isset($result['error'])) { //error
                        $order->add_order_note('[Splitit] ' . $result['error']);
                    } else {
                        $order->add_order_note('[Splitit] Order successfully captured!');
                    }
                }
            }
        }

        public function splitit_order_status_shipped_callback($order_id)
        {
            if($this->s('splitit_payment_action') == 'shipped') {
                //Here order id is sent as parameter
                $order_meta = get_post_custom($order_id);
                if (isset($order_meta['installment_plan_number']) && !empty($order_meta['installment_plan_number'][0])) {
                    if (is_null($this->_API)) {
                        $this->_API = new SplitIt_API($this->settings); //passing settings to API
                    }
                    $session = $this->_API->login();
                    $this->_API->capture($order_meta['installment_plan_number'][0], $session);
                }
            }
        }

        /***************************************************************************************************************
         * API/AJAX call handlers section
         **************************************************************************************************************/

        /**
         * Called by ajax from checkout.
         * Initialize SplitIt scripts via ajax request
         *
         * @access public
         */
        public function splitit_scripts_on_checkout() {
            $checkout_fields_post = $_POST;

            //trying to receive checkout fields data from post
            if(count($checkout_fields_post)) {
                $checkout_fields = array();

                //add billing data to shipping if shipping is same as billing
                $skip_shipping = $checkout_fields_post['ship-to-different-address'][1] == 1 ? false : true;
                unset($checkout_fields['ship-to-different-address']);
                if($skip_shipping) {
                    foreach ($checkout_fields_post as $f => $d) {
                        $type = explode('_', $f);
                        if($type[0] == 'shipping') {
                            $billing_field = str_replace('shipping', 'billing', $f);
                            $checkout_fields_post[$field] = $checkout_fields_post[$billing_field];
                        }
                    }
                }

                foreach ($checkout_fields_post as $field_name => $label_value) {
                    $checkout_fields[$field_name] = $label_value[1];
                }
               //echo "<pre>"; print_r($checkout_fields);die;
                $order_data = array(
                    'Address' => trim( $checkout_fields['billing_address_1_field'] ) ,
                    'Address2'=>  trim( isset($checkout_fields['billing_address_2_field']) ? $checkout_fields['billing_address_2_field'] : '' ), 
                    'Zip'     => trim( $checkout_fields['billing_postcode_field']),
                    'AmountBeforeFees' => WC()->cart->total,
                    'ConsumerFullName' => trim( $checkout_fields['billing_first_name_field'] . ' ' . $checkout_fields['billing_last_name_field'] ),
                    'Email'      => trim( $checkout_fields['billing_email_field'] ),
                    'City'=> trim( $checkout_fields['billing_city_field'] ),
                    'State'=> trim( $checkout_fields['billing_state_field'] ),
                    'Country'=>trim( $checkout_fields['billing_country_field'] ),
                    'Phone'=>trim( $checkout_fields['billing_phone_field'] )
                );

                //this shouldn`t happen, but in case of: no post data contained (user flushed cookie?)
                // create empty address data, so user will be able to fill it on Splitit popup
            } else {
                $order_data = array(
                    'Address' => '',
                    'Address2' => '',
                    'Zip'     => '',
                    'AmountBeforeFees' => WC()->cart->total
                );
            }

            $this->_API = new SplitIt_API($this->settings); //passing settings to API
            $session = $this->_API->login();

            if(!is_array($session)) {
                $ec_session_id = $this->_API->getEcSession($order_data);
                if(!(is_null($ec_session_id->{'EcSessionId'}))) {
                    return wp_send_json(array('ec_session_id' => $ec_session_id->{'EcSessionId'}, 'sandbox_mode' => $this->settings['splitit_mode_sandbox']));
                } else {
                    $this->log->info(__FILE__, __LINE__, __METHOD__);
                    $this->log->add($ec_session_id);
                    return wp_send_json($ec_session_id);
                }
            }
            return wp_send_json($session['error']);
        }

        /**
         * Called by ajax from checkout.
         * Validates checkout fields.
         *
         * @access public
         */
        public function splitit_checkout_validate() {
            // Get posted checkout_fields and do validation
            if(isset($_POST)) {

                unset($_POST['account_password_field']); //not needed field
                $checkout_fields = $_POST;
                $validate_errors = '';
              //  print_r($checkout_fields);
                //echo "comig";
                 if ( ! is_user_logged_in() && isset($checkout_fields['billing_email_field']) && $checkout_fields['billing_email_field'][1]!="" ) {
                    //echo "entered";
                    if(email_exists($checkout_fields['billing_email_field'][1])){
                        //echo "condition";
                        $validate_errors[] = '<li>An account is already registered with your email address. Please login. </li>';
                    }

                }
                //die("done");
                //add billing data to shipping if shipping is same as billing
                $skip_shipping = $checkout_fields['ship-to-different-address'][1] == 1 ? false : true;
                unset($checkout_fields['ship-to-different-address']);
                if($skip_shipping) {
                    foreach ($checkout_fields as $f => $d) {
                        $type = 'shipping';
                        $pos = strpos($f, 'ship');
                        if($pos === false) {
                            $type = 'billing';
                        }
                        if($type == 'shipping') {
                            $billing_field = str_replace('shipping', 'billing', $f);
                            $checkout_fields[$f] = $checkout_fields[$billing_field];
                        }
                    }
                }

                foreach ($checkout_fields as $field => $data) {

                    // Validation: Required fields
                    if(!isset($data[1]) || $data[1] == '') {
                        $type = 'shipping';
                        $pos = strpos($field, 'ship');
                        if($pos === false) {
                            $type = 'billing';
                        }

                        if($skip_shipping && $type == 'shipping') {
                            //we don`t validate shipping fields as they are skipped
                        } else {
                            $validate_errors[] = '<li>' . ucfirst($type) . ' <strong>' . $data[0] . '</strong> ' . __('is a required field.', 'woocommerce') . '</li>';
                        }
                    }

                    if(count($validate_errors) == 0) {
                        // Validation rules
                        $field_type = str_replace(array('shipping_', 'billing_'), '', $field);
                        switch ($field_type) {
                            case 'postcode_field' :
                                $checkout_fields[$field][1] = strtoupper(str_replace(' ', '', $data[1]));
                                if (!WC_Validation::is_postcode($checkout_fields[$field][1], $checkout_fields[$field][1])) :
                                    $validate_errors[] = '<li><strong>' . $data[0] . '/Postcode</strong> ' . __('is not valid.', 'woocommerce') . '</li>';
                                else :
                                    $checkout_fields[$field][1] = wc_format_postcode($checkout_fields[$field][1], $checkout_fields[$field][1]);
                                endif;
                                break;
                            case 'phone_field' :
                                $checkout_fields[$field][1] = wc_format_phone_number($data[1]);
                                if (!WC_Validation::is_phone($checkout_fields[$field][1]))
                                    $validate_errors[] = '<li><strong>' . $data[0] . '</strong> ' . __('is not a valid phone number.', 'woocommerce') . '</li>';
                                break;
                            case 'email_field' :
                                $checkout_fields[$field][1] = strtolower($data[1]);
                                if (!is_email($checkout_fields[$field][1]))
                                    $validate_errors[] = '<li><strong>' . $data[0] . '</strong> ' . __('is not a valid email address.', 'woocommerce') . '</li>';
                                break;
                            case 'state_field' :
                                // Get valid states
                                $valid_states = WC()->countries->get_states(isset($checkout_fields[$field]) ? $checkout_fields[$field][1] : WC()->customer->get_country());
                                if (!empty($valid_states) && is_array($valid_states)) {
                                    $valid_state_values = array_flip(array_map('strtolower', $valid_states));
                                    // Convert value to key if set
                                    if (isset($valid_state_values[strtolower($data[1])])) {
                                        $checkout_fields[$field][1] = $valid_state_values[strtolower($data[1])];
                                    }
                                }
                                // Only validate if the country has specific state options
                                if (!empty($valid_states) && is_array($valid_states) && sizeof($valid_states) > 0) {
                                    if (!in_array($checkout_fields[$field][1], array_keys($valid_states))) {
                                        $validate_errors[] = '<li><strong>' . $data[0] . '</strong> ' . __('is not valid. Please enter one of the following:', 'woocommerce') . ' ' . implode(', ', $valid_states) . '</li>';
                                    }
                                }
                                break;
                        }
                    }
                }

                if(isset($checkout_fields['terms'])) {
                    if($checkout_fields['terms'][1] == 0) {
                        $validate_errors[] = '<li>' . __( 'You must accept our Terms &amp; Conditions.', 'woocommerce' ) . '</li>';
                    }
                }

                //$checkout_fields now contain validated data

                if(is_array($validate_errors)) {
                    $validate_errors = array_unique($validate_errors);
                    $response = array(
                        'result' => 'failure',
                        'messages' => implode('', $validate_errors)
                    );
                } else {
                    $response = array(
                        'result' => 'success'
                    );
                }

            } else {
                $response = array(
                    'result' => 'failure',
                    'messages' => 'No data has been sent from form'
                );
            }

            wp_send_json($response);
        }



        public function splitit_payment_error(){
            $ipn = isset($_GET['InstallmentPlanNumber']) ? $_GET['InstallmentPlanNumber'] : false;
            $esi = isset($_COOKIE["splitit_checkout_session_id_data"]) ? $_COOKIE["splitit_checkout_session_id_data"] : false;

            $this->_API = new SplitIt_API($this->settings); //passing settings to API

            if(!isset($this->settings['splitit_cancel_url']) || $this->settings['splitit_cancel_url'] == '') {
                $this->settings['splitit_cancel_url'] = 'checkout/';
            }

           // $result = $this->_API->cancel($ipn, $esi);
             //if($result == false) {
                setcookie('splitit_checkout', null, strtotime('-1 day'));
                setcookie('splitit_checkout_session_id_data', null, strtotime('-1 day'));
                wp_redirect(SplitIt_Helper::sanitize_redirect_url($this->settings['splitit_cancel_url']));
                exit;
           // }

            setcookie('splitit_checkout', null, strtotime('-1 day'));
            setcookie('splitit_checkout_session_id_data', null, strtotime('-1 day'));

        }

       public function get_post_id_by_meta_value($value) {
            global $wpdb;
            $meta = $wpdb->get_results("SELECT * FROM `".$wpdb->postmeta."` WHERE meta_key='".$wpdb->escape("installment_plan_number")."' AND meta_value='".$wpdb->escape($value)."'");      
            return $meta;
         }


        /**
         * Api success redirect handler
         * captures order in merchant account if necessary, and creates new order in WP
         *
         * @access public
         */


        public function splitit_payment_success($flag=NULL){
            //print_r($);
           // print_r(WC()->session->cart);

          // die("did not create the order it will be created automatically");

            global $wpdb;
            $ipn = isset($_GET['InstallmentPlanNumber']) ? $_GET['InstallmentPlanNumber'] : false;
            $esi = isset($_COOKIE["splitit_checkout_session_id_data"]) ? $_COOKIE["splitit_checkout_session_id_data"] : false;
            $exists_data_array = $this->get_post_id_by_meta_value($ipn);   
            //print_r($exists_data_array);die;       
             if (empty($exists_data_array)) {     
                    $this->_API = new SplitIt_API($this->settings); //passing settings to API
                    if(!isset($this->settings['splitit_cancel_url']) || $this->settings['splitit_cancel_url'] == '') {
                        $this->settings['splitit_cancel_url'] = 'checkout/';
                    }
                    $table_name = $wpdb->prefix . 'splitit_logs';
                    $fetch_items = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM ".$table_name." WHERE ipn =".$ipn ), ARRAY_A );
                    //checking for user entered data
                    if(isset($fetch_items['user_data']) && $fetch_items['user_data'] !="") {
                        $checkout_fields_array = explode('&', $fetch_items['user_data']);
                        $checkout_fields = array();
                        foreach($checkout_fields_array as $row) {
                            $key_value = explode('=', $row);
                            $checkout_fields[$key_value[0]] = $key_value[1];
                        }
                        $checkout_fields['payment_method'] = 'splitit'; 
                        $criteria = array('InstallmentPlanNumber' => $ipn);
                        $installment_data = $this->_API->get($esi, $criteria);   
                        $checkout = new SplitIt_Checkout();
                        $checkout->process_splitit_checkout($checkout_fields, $this, $installment_data,$ipn,$esi,$this->settings);
                        setcookie('splitit_checkout', null, strtotime('-1 day'));
                        setcookie('splitit_checkout_session_id_data', null, strtotime('-1 day'));
                        wc_clear_notices();                     
                    } else {
                        wc_clear_notices();
                        wc_add_notice('Sorry, there was no checkout data received to create order! It was not placed. Please try to order again.','error');
                        wp_redirect(SplitIt_Helper::sanitize_redirect_url($this->settings['splitit_cancel_url']));
                        exit;

                    }
            }


        }

        /**
         *
         * Check if Payment was success but order was not created
         *
         * @access public
         */
        public function splitit_payment_success_async() {
            global $wpdb;
            $ipn = isset($_GET['InstallmentPlanNumber']) ? $_GET['InstallmentPlanNumber'] : false;

            //echo $ipn."---";die;
           // $ipn = "67757642666443565703";
            $exists_data_array = $this->get_post_id_by_meta_value($ipn);
           // echo "<pre>";print_r($exists_data_array);die;
             // do something if the meta-key-value-pair exists in another post
             if (empty($exists_data_array) ) {
                $table_name = $wpdb->prefix . 'splitit_logs';
                $fetch_items = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM ".$table_name." WHERE ipn =".$ipn ), ARRAY_A );
                if(!empty($fetch_items)){
                    $user_data = $fetch_items['user_data'];
                    $user_id   = $fetch_items['user_id'];
                    $cart_items = $fetch_items['cart_items'];
                    $shipping_method = $fetch_items['shipping_method_id'];
                    $shipping_cost = $fetch_items['shipping_method_cost'];
                    $shipping_title = $fetch_items['shipping_method_title'];
                    $coupon_amount = $fetch_items['coupon_amount'];
                    $coupon_code = $fetch_items['coupon_code'];
                    $cart_items = json_decode($fetch_items['cart_items'],true);
                    //print_r($cart_items);die;
                    $this->_API = new SplitIt_API($this->settings); //passing settings to API
                    $session = $this->_API->login();
                    if(!isset($this->settings['splitit_cancel_url']) || $this->settings['splitit_cancel_url'] == '') {
                        $this->settings['splitit_cancel_url'] = 'checkout/';
                    }
                    if($user_data!="") {
                        $checkout_fields_array = explode('&', $user_data);
                        $checkout_fields = array();
                        foreach($checkout_fields_array as $row) {
                            $key_value = explode('=', $row);
                            $checkout_fields[$key_value[0]] = $key_value[1];
                        }
                        $checkout_fields['payment_method'] = 'splitit'; //override default method as it is not correct                
                        $criteria = array('InstallmentPlanNumber' => $ipn);
                        $installment_data = $this->_API->get($session, $criteria);   
                        $checkout = new SplitIt_Checkout();
                        $checkout->async_process_splitit_checkout($checkout_fields, $this, $installment_data,$ipn,$session,$this->settings,$user_id,$cart_items,$shipping_method,$shipping_cost,$shipping_title,$coupon_amount,$coupon_code);
                        wc_clear_notices();
                      
                    } 

                }
                
             }else{
                echo "Order has been already created";die;
             }
             return true;
           
        }



        /**
         * Called from admin settings, clicking on "Check API credentials" link
         * Check if API credentials are correct
         *
         * @access public
         */
        public function splitit_check_api_credentials() {
            
            if (is_null($this->_API)) {
                $this->_API = new SplitIt_API($this->settings); //passing settings to API
            }
            
            $session = $this->_API->login();

            $message = ($this->s('splitit_mode_sandbox') == 'yes')?'[Sandbox Mode] ':'[Production mode] ';
            if (!isset($session['error'])) {
                $message .= 'Successfully login! API available!';
            } else {
                $message .= 'code: ' . $session['error']['code'] . ' - ERROR: ' . $session['error']['message'];
            }

            echo $message;
            wp_die();
        }


        /***************************************************************************************************************
         * Payment process logic
         **************************************************************************************************************/

        /**
         * Process the payment and return the result
         */
        public function process_payment( $order_id )
        {
            global $woocommerce;
            try {

                $order = wc_get_order( $order_id );
                // Get redirect
                $return_url = SplitIt_Helper::sanitize_redirect_url($this->settings['splitit_success_url']);
                if(!$return_url) {
                    $return_url = $order->get_checkout_order_received_url();
                }
                $woocommerce->cart->empty_cart();
                // Redirect to success/confirmation/payment page
                if ( is_ajax() ) {
                    wp_send_json( array(
                        'result'    => 'success',
                        'redirect'  => apply_filters( 'woocommerce_checkout_no_payment_needed_redirect', $return_url, $order )
                    ) );
                } else {
                    global $wp;
                    // redirect to checkout success page.
                    if ( $order_id  ) {
                        
                        $order = new WC_Order($order_id);
                        $order_key = $order->order_key;
                        //$order_key = wc_clean( $_GET['key'] );

                        /**
                         * Replace {PAGE_ID} with the ID of your page
                         */
                        //$redirect  = get_permalink(6);
                        //$redirect .= get_option( 'permalink_structure' ) === '' ? '&' : '?';
                        global $woocommerce;
                        $checkout_url = $woocommerce->cart->get_checkout_url();
                        $redirect = $checkout_url.'/order-received/' . $order_id . '/?key=' . $order_key;

                        wp_redirect( $redirect );
                        exit;
                    }


                    wp_safe_redirect(
                        apply_filters( 'woocommerce_checkout_no_payment_needed_redirect', $return_url, $order )
                    );
                    exit;
                }
            }
            catch( Exception $e)
            {
                $this->log->info(__FILE__, __LINE__, __METHOD__);
                $this->log->add($e->getMessage());
            }
        }

        /***************************************************************************************************************
         * Installment price logic
         **************************************************************************************************************/

        /**
         * Split price functionality wrapper
         *
         * @param $price
         * @param $product
         * @return string
         */
        public function splitit_installment_price($price, $product) {
            if(isset($this->settings['splitit_installment_price_sections'])) {
                $sections = $this->settings['splitit_installment_price_sections'];
                //checking if any options selected in admin
                if (is_array($sections)) {
                    if (is_product() && in_array('product', $sections)) {
                        return $price . $this->get_formatted_installment_price($product);
                    }
                    if (is_shop() && in_array('category', $sections)) {
                        return $price . $this->get_formatted_installment_price($product);
                    }
                    if (is_cart() && in_array('cart', $sections)) {
                        return $price . $this->get_formatted_installment_price($product);
                    }
                    if (is_checkout() && in_array('checkout', $sections)) {
                        return $price . $this->get_formatted_installment_price($product);
                    }
                }
            }
            return $price;
        }

        /**
         * Split total functionality wrapper
         *
         * @param $price
         * @return string
         */
        public function splitit_installment_total_price($price) {
            global $woocommerce;
            if(is_array($this->settings['splitit_installment_price_sections'])) {
                $sections = $this->settings['splitit_installment_price_sections'];
                if ((is_cart() && in_array('cart', $sections)) || (is_checkout() && in_array('checkout', $sections))) {
                    $split_price = round($woocommerce->cart->total / self::$_maxInstallments, 3);
                    return $price . '<span style="display:block;" class="splitit-installment-price">' . self::$_maxInstallments . ' x ' . wc_price($split_price, array('decimals'=>2)) . ' ' . $this->s('splitit_without_interest') . '</span>';
                }
            }
            return $price;
        }

        /**
         * Returns formatted installment price
         *
         * @param $price
         * @param $return
         * @return string
         */
        public function get_formatted_installment_price($product) {
            $split_price = round($product->price / self::$_maxInstallments, 3);
            return '<span style="display:block;" class="splitit-installment-price">' . self::$_maxInstallments . ' x ' . wc_price($split_price, array('decimals'=>2)) . ' ' . $this->s('splitit_without_interest') . '</span>';
        }

        /***************************************************************************************************************
         * Helper functions
         **************************************************************************************************************/

        /**
         * s function.
         *
         * Returns a setting if set. Introduced to prevent undefined key when introducing new settings.
         *
         * @access public
         * @return string
         */
        public function s( $key )
        {
            if( isset( $this->settings[$key] ) ) {
                return $this->settings[$key];
            }

            return '';
        }

        /**
         * Passing cdn urls to splitit-checkout.js script
         *
         * @access public
         * @return array
         */
        public function splitit_pass_cdn_urls()
        {
            $params = array('prod' => rtrim($this->s('splitit_cdn_prod_url'), '/') . '/', 'sand' => rtrim($this->s('splitit_cdn_sand_url'), '/') . '/');
            wp_localize_script('splitit-checkout', 'cdn_urls', $params);
        }

        /**
         * FILTER: splitit_gateway_icons function.
         *
         * Sets gateway icons on frontend
         *
         * @access public
         * @return void
         */
        public function splitit_gateway_icons( $icon, $id ) {
            if($id == $this->id) {
                $icon = '';
                $icons = $this->s('splitit_cc');

                if( is_array($icons) && count($icons) ) {
                    foreach( $icons as $key => $item ) {
                        $icon .= $this->gateway_icon_create($item, '30');
                    }
                }

                $icon .= '<a href="#" id="tell-me-more">' . $this->s('splitit_help_title') . '</a>';
            }

            return $icon;
        }

        /**
         * remove splitit gateway if cart total > max or < min
         * @param $gateways
         * @return mixed
         */
        public function change_payment_gateway($gateways) {

            foreach ($this->settings['splitit_doct']['ct_from'] as $key => $value) {
                                if (empty($value)) {
                                   unset($this->settings['splitit_doct']['ct_from'][$key]);
                                }
                            }
            foreach ($this->settings['splitit_doct']['ct_to'] as $key1 => $value1) {
                                if (empty($value1)) {
                                   unset($this->settings['splitit_doct']['ct_to'][$key1]);
                                }
                            }    
              $min = min($this->settings['splitit_doct']['ct_from']);
              $max = max($this->settings['splitit_doct']['ct_to']);
            // Compare cart subtotal (without shipment fees)
            if( WC()->cart->subtotal > $max or WC()->cart->subtotal < $min ){
                unset( $gateways['splitit'] );
            }
            return $gateways;
        }


        /**
         * Helper to get the a gateway icon image tag
         *
         * @access protected
         * @return string
         */
        protected function gateway_icon_create($icon, $max_height) {
            $icon_url = WC_HTTPS::force_https_url( plugin_dir_url( __FILE__ ) . 'assets/images/cards/' . $icon . '.png' );
            return '<img src="' . $icon_url . '" alt="' . esc_attr( $icon ) . '" style="max-height:' . $max_height . 'px; "/>';
        }

        /**
         * Tell me more link
         *
         * @access public
         * @return string
         */
        public function splitit_help()
        {
            return wp_send_json( plugin_dir_url( __FILE__ ).'assets/images/tellmemore.png' );
        }

        /**
         * Adds installment_plan_number value to order edit page
         *
         * @param $order
         */
        public function splitit_add_installment_plan_number_data($order){
            echo '<p><strong>'.__('Installment plan number').':</strong> ' . get_post_meta( $order->id, 'installment_plan_number', true ) . '</p>';
            echo '<p><strong>'.__('Number of installments').':</strong> ' . get_post_meta( $order->id, 'number_of_installments', true ) . '</p>';
        }

        /**
         * Enable request logging
         *
         * @param $response array
         * @param $type string
         * @param $class obj
         * @param $args array
         * @param $url string
         */
        public function splitit_api_request_debug($response, $type, $class, $args, $url) {
            if (strpos($url,'splitit') !== false) {
                $this->log->info(__FILE__, __LINE__, __METHOD__);
                $this->log->add('Request URL: ' . var_export($url, true));
                $this->log->add('Request Args: ' . var_export($args, true));
                $this->log->add('Response: ' . var_export($response, true));
                $this->log->separator();
            }
        }
    }

    // Make the object available for later use
    function SplitIt() {
        return SplitIt::get_instance();
    }

    SplitIt();
    SplitIt()->hooks_and_filters(); //saves admin settings data



    // Add the gateway to WooCommerce
    function add_splitit_gateway( $methods )
    {
        $methods[] = 'SplitIt';
        return apply_filters('splitit_load_instances', $methods);
    }

    add_filter('woocommerce_payment_gateways', 'add_splitit_gateway' );
    add_filter('plugin_action_links_' . plugin_basename( __FILE__ ), 'SplitIt::add_action_links');
}
