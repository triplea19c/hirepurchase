<?php
/*
  Plugin Name: WooCommerce Payment Gateway - Paysera
  Plugin URI: http://www.paysera.com
  Description: Accepts Paysera
  Version: 2.4.1
  Author: Paysera
  Author URI: http://www.paysera.com
  License: GPL version 3 or later - http://www.gnu.org/licenses/gpl-3.0.html

  @package WordPress
  @author Paysera (http://paysera.com)
  @since 2.0.0
 */

defined( 'ABSPATH' ) or exit;

if (!in_array(
    'woocommerce/woocommerce.php',
    apply_filters('active_plugins', get_option('active_plugins'))
)) {
    return;
}

/**
 * Add the gateway to WooCommerce
 *
 * @access public
 * @param array $methods
 * @package WooCommerce/Classes/Payment
 *
 * @return array $methods
 */
function add_paysera_gateway($methods)
{
    $methods[] = 'WC_Gateway_Paysera';
    return $methods;
}
add_filter('woocommerce_payment_gateways', 'add_paysera_gateway');
add_action('plugins_loaded', 'wc_gateway_paysera_init');

/**
 * INIT Paysera Gateway
 *
 * @access public
 */
function wc_gateway_paysera_init()
{
    if(!class_exists('WebToPay')) {
        require_once 'includes/libraries/WebToPay.php';
    }

    class WC_Gateway_Paysera extends WC_Payment_Gateway
    {
        /**
         * Paysera image location
         */
        const PAYSERA_LOGO = 'assets/images/paysera.png';

        /**
         * Default project id
         */
        const DEFAULT_PROJECT_ID = 0;

        /**
         * Default currency
         */
        const DEFAULT_CURRENCY = 'EUR';

        /**
         * Local language delimiter
         */
        const LANG_DELIMITER = '_';

        /**
         * Default language
         */
        const DEFAULT_LANG = 'en';

        /**
         * @var integer
         */
        protected $projectID;

        /**
         * @var string
         */
        protected $password;

        /**
         * @var boolean
         */
        protected $paymentType;

        /**
         * @var boolean
         */
        protected $gridView;

        /**
         * @var string|array
         */
        protected $countriesSelected;

        /**
         * @var boolean
         */
        protected $test;

        /**
         * @var string
         */
        protected $paymentNewOrderStatus;

        /**
         * @var string
         */
        protected $paymentCompletedStatus;

        /**
         * @var string
         */
        protected $paymentCanceledStatus;

        /**
         * @var object
         */
        protected $pluginSettings;

        /**
         * WC_Gateway_Paysera constructor.
         */
        public function __construct()
        {
            $this->id = 'paysera';
            $this->has_fields = true;
            $this->method_title = __('Paysera', 'woocommerce');
            $this->icon = apply_filters(
                'woocommerce_paysera_icon',
                plugin_dir_url(__FILE__) . $this::PAYSERA_LOGO
            );

            $this->init_form_fields();
            $this->init_settings();

            $this->title                  = $this->get_option('title');
            $this->description            = $this->get_option('description');
            $this->projectID              = $this->get_option('projectid');
            $this->password               = $this->get_option('password');
            $this->paymentType            = $this->get_option('paymentType') === 'yes';
            $this->gridView               = $this->get_option('style') === 'yes';
            $this->countriesSelected      = $this->get_option('countriesSelected');
            $this->test                   = $this->get_option('test') === 'yes';
            $this->paymentNewOrderStatus  = $this->get_option('paymentNewOrderStatus');
            $this->paymentCompletedStatus = $this->get_option('paymentCompletedStatus');
            $this->paymentCanceledStatus  = $this->get_option('paymentCanceledStatus');

            add_action('woocommerce_thankyou_paysera', array($this, 'thankyou'));
            add_action('woocommerce_api_wc_gateway_paysera', array($this, 'check_callback_request'));
            add_action(
                'woocommerce_update_options_payment_gateways_paysera',
                array($this, 'process_admin_options')
            );
        }

        public function init_form_fields()
        {
            if(!class_exists('Wc_Paysera_Settings')) {
                require_once 'includes/class-wc-paysera-settings.php';
            }

            $this->setPluginSettings(new Wc_Paysera_Settings(
                $this::DEFAULT_PROJECT_ID,
                $this::DEFAULT_CURRENCY,
                $this::DEFAULT_LANG
            ));

            $this->form_fields = $this->getPluginSettings()->getFormFields();
        }

        public function admin_options()
        {
            $this->getPluginSettings()->setLang($this->getLocalLang($this::LANG_DELIMITER));
            $this->getPluginSettings()->setCurrency(get_woocommerce_currency());
            $this->getPluginSettings()->setProjectID($this->getProjectID());
            $this->updateAdminSettings($this->getPluginSettings()->generateNewSettings());
            $all_fields = $this->get_form_fields();
            $tabs = $this->generateTabs(array(
                [
                    'name'  => 'Main Settings',
                    'slice' => array_slice($all_fields, 0, 4)
                ],
                [
                    'name'  => 'Extra Settings',
                    'slice' => array_slice($all_fields, 4, -3)
                ],
                [
                    'name'  => 'Order Status',
                    'slice' => array_slice($all_fields, -3, count($all_fields))
                ]
            ));
            $this->getPluginSettings()->buildAdminFormHtml($tabs);

            wp_enqueue_script(
                'custom-backend-script',
                plugin_dir_url(__FILE__) . 'assets/js/backend/action.js',
                array('jquery')
            );
        }

        public function validate_projectid_field($key, $value) {
            if (1 > strlen($value)) {
                WC_Admin_Settings::add_error(esc_html__(
                    'Project ID must be Not Empty',
                    'WC_Gateway_Paysera'
                ));
            }

            return $value;
        }

        public function validate_password_field($key, $value) {
            if (1 > strlen($value)) {
                WC_Admin_Settings::add_error(esc_html__(
                    'Password (sign) must be Not Empty',
                    'WC_Gateway_Paysera'
                ));
            }

            return $value;
        }

        public function payment_fields()
        {
            if(!class_exists('Wc_Paysera_Payment_Methods')) {
                require_once 'includes/class-wc-paysera-payment-methods.php';
            }

            if (defined('DOING_AJAX')) {
                $localLang = explode('_', get_locale());
                $billingCountry = WC()->customer->get_billing_country();
                $cartTotal = round(WC()->cart->total * 100);
                $currency = get_woocommerce_currency();

                $htmlFields = new Wc_Paysera_Payment_Methods(
                    $localLang[0],
                    strtolower($billingCountry),
                    $this->getPaymentType(),
                    $this->getCountriesSelected(),
                    $this->getGridView(),
                    $this->getDescription(),
                    $cartTotal,
                    $currency
                );

                $htmlFields->build(true);
            }

            wp_enqueue_style(
                'custom-frontend-style',
                plugin_dir_url(__FILE__) . 'assets/css/paysera.css'
            );

            wp_enqueue_script(
                'custom-frontend-script',
                plugin_dir_url(__FILE__) . 'assets/js/frontend/action.js',
                array('jquery')
            );
        }

        public function process_payment($order_id)
        {
            if(!class_exists('Wc_Paysera_Request')) {
                require_once 'includes/class-wc-paysera-request.php';
            }

            error_log(
                'Order #' . $order_id . ' is redirected to payment.'
                . 'Notify URL: ' . trailingslashit(home_url()) . '?payseraListener=paysera_callback'
            );

            $order = wc_get_order($order_id);
            $order->add_order_note(__('Paysera: Order checkout process is started', 'woocomerce'));
            $this->updateOrderStatus($order, $this->getPaymentCanceledStatus());

            $paysera_request = new Wc_Paysera_Request(
                $this->getProjectID(),
                $this->getPassword(),
                $this->get_return_url($order),
                (trailingslashit(get_bloginfo('wpurl')) . '?wc-api=wc_gateway_paysera'),
                $this->getTest(),
                $this->getLocalLang($this::LANG_DELIMITER)
            );

            if ($this->getPaymentType()) {
                $selectedPayment = $_REQUEST['payment']['pay_type'];
            } else {
                $selectedPayment = '';
            }
            $parameters = $paysera_request->getWooParameters($order, $selectedPayment);
            $url = $paysera_request->buildUrl($parameters);

            wc_reduce_stock_levels($order_id);

            return array(
                'result'   => 'success',
                'redirect' => $url,
            );
        }

        public function thankyou($order_id)
        {
            $order = wc_get_order($order_id);
            if ($order->get_status() != str_replace('wc-','', $this->getPaymentCompletedStatus())) {
                $message = 'Paysera: Customer came back to page';
                $this->getOrderLogMsg($order, $message, true);
                $order->add_order_note(__($message, 'woocomerce'));
                $this->updateOrderStatus($order, $this->getPaymentNewOrderStatus());
            }
        }

        public function check_callback_request()
        {
            try {
                $response = WebToPay::checkResponse(
                    $_REQUEST,
                    array(
                        'projectid'     => $this->projectID,
                        'sign_password' => $this->password,
                    )
                );

                if ($response['status'] == 1) {
                    $order = wc_get_order($response['orderid']);

                    $orderTotal = intval(number_format($order->get_total(), 2, '', ''));
                    if ($orderTotal != $response['amount']) {
                        $errorMsg = 'Amounts do not match';
                        throw new Exception($this->getOrderLogMsg($order, $errorMsg));
                    }

                    if ($order->get_currency() != $response['currency']) {
                        $errorMsg = 'Currencies do not match';
                        throw new Exception($this->getOrderLogMsg($order, $errorMsg));
                    }

                    $errorMsg = 'Payment confirmed with a callback';
                    $this->getOrderLogMsg($order, $errorMsg, true);

                    $order->add_order_note(__('Paysera: Callback order payment completed', 'woocomerce'));
                    $this->updateOrderStatus($order, $this->getPaymentCompletedStatus());

                    print_r('OK');
                }
            } catch (Exception $e) {
                $errorMsg = get_class($e) . ': ' . $e->getMessage();
                error_log($errorMsg);
                print_r($errorMsg);
            }

            exit();
        }

        protected function getOrderLogMsg($order, $errorMsg, $sendLog = false)
        {
            $fullLog = $errorMsg . ':'
                . ' Order #' . $order->get_id() . ';'
                . ' Amount: ' . $order->get_total() . $order->get_currency();

            if ($sendLog) {
                error_log($fullLog);
                return $sendLog;
            } else {
                return $fullLog;
            }
        }

        protected function getLocalLang($delimiter)
        {
            $lang = explode($delimiter, get_locale());

            return $lang[0];
        }

        protected function generateTabs($tabs)
        {
            $data = [];
            foreach ($tabs as $key => $value) {
                $data[$key]['name']  =  $value['name'];
                $data[$key]['slice'] =  $this->generate_settings_html($value['slice'], false);
            }

            return $data;
        }

        protected function updateAdminSettings($data)
        {
            $this->form_fields['countriesSelected']['options']      = $data['countries'];
            $this->form_fields['paymentNewOrderStatus']['options']  = $data['statuses'];
            $this->form_fields['paymentCompletedStatus']['options'] = $data['statuses'];
            $this->form_fields['paymentCanceledStatus']['options']  = $data['statuses'];
        }

        protected function updateOrderStatus($order, $status)
        {
            $orderStatusFiltered = str_replace("wc-", "", $status);
            $order->update_status(
                $orderStatusFiltered,
                'Paysera: Status changed to ' . $orderStatusFiltered . '<br />',
                true
            );
        }

        /**
         * @return string
         */
        public function getTitle()
        {
            return $this->title;
        }

        /**
         * @param string $title
         */
        public function setTitle($title)
        {
            $this->title = $title;
        }

        /**
         * @return string
         */
        public function getDescription()
        {
            return $this->description;
        }

        /**
         * @param string $description
         */
        public function setDescription($description)
        {
            $this->description = $description;
        }

        /**
         * @return integer
         */
        public function getProjectID()
        {
            return $this->projectID;
        }

        /**
         * @param integer $projectID
         */
        public function setProjectId($projectID)
        {
            $this->projectID = $projectID;
        }

        /**
         * @return string
         */
        public function getPassword()
        {
            return $this->password;
        }

        /**
         * @param string $password
         */
        public function setPassword($password)
        {
            $this->password = $password;
        }

        /**
         * @return boolean
         */
        public function getPaymentType()
        {
            return $this->paymentType;
        }

        /**
         * @param boolean $paymentType
         */
        public function setPaymentType($paymentType)
        {
            $this->paymentType = $paymentType;
        }

        /**
         * @return boolean
         */
        public function getGridView()
        {
            return $this->gridView;
        }

        /**
         * @param boolean $gridView
         */
        public function setGridView($gridView)
        {
            $this->gridView = $gridView;
        }

        /**
         * @return string|array
         */
        public function getCountriesSelected()
        {
            return $this->countriesSelected;
        }

        /**
         * @param string $countriesSelected
         */
        public function setCountriesSelected($countriesSelected)
        {
            $this->countriesSelected = $countriesSelected;
        }

        /**
         * @return boolean
         */
        public function getTest()
        {
            return $this->test;
        }

        /**
         * @param boolean $test
         */
        public function setTest($test)
        {
            $this->test = $test;
        }

        /**
         * @return string
         */
        public function getPaymentNewOrderStatus()
        {
            return $this->paymentNewOrderStatus;
        }

        /**
         * @param string $paymentNewOrderStatus
         */
        public function setPaymentNewOrderStatus($paymentNewOrderStatus)
        {
            $this->paymentNewOrderStatus = $paymentNewOrderStatus;
        }

        /**
         * @return string
         */
        public function getPaymentCompletedStatus()
        {
            return $this->paymentCompletedStatus;
        }

        /**
         * @param string $paymentCompletedStatus
         */
        public function setPaymentCompletedStatus($paymentCompletedStatus)
        {
            $this->paymentCompletedStatus = $paymentCompletedStatus;
        }

        /**
         * @return string
         */
        public function getPaymentCanceledStatus()
        {
            return $this->paymentCanceledStatus;
        }

        /**
         * @param string $paymentCanceledStatus
         */
        public function setPaymentCanceledStatus($paymentCanceledStatus)
        {
            $this->paymentCanceledStatus = $paymentCanceledStatus;
        }

        /**
         * @return object
         */
        public function getPluginSettings()
        {
            return $this->pluginSettings;
        }

        /**
         * @param object $pluginSettings
         */
        public function setPluginSettings($pluginSettings)
        {
            $this->pluginSettings = $pluginSettings;
        }
    }
}
