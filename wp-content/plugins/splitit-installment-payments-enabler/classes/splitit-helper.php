<?php
/**
 * SplitIt_Helper class
 *
 * @class       SplitIt_Helper
 * @version     0.2.9
 * @package     SplitIt/Classes
 * @category    Helper
 * @author      By Splitit
 */

class SplitIt_Helper
{
    /**
     * Ajax handler for check api settings
     */
    public static function admin_js() {
        wp_enqueue_script( 'splitit-admin', plugins_url( '/assets/javascript/splitit-admin.js', dirname( __FILE__ ) ), array( 'jquery' ) );
    }

    /**
     * Checkout ajax and js scripts
     */
    public static function checkout_js() {
        wp_enqueue_script( 'splitit-checkout', plugins_url( '/assets/javascript/splitit-checkout.js', dirname( __FILE__ ) ), array( 'jquery' ) );
    }

    /**
     * Styles to hide checkout subtotal installment price
     */
    public static function front_css() {
        wp_enqueue_style( 'splitit-front', plugins_url( '/assets/css/splitit-front.css', dirname( __FILE__ ) ) );
    }

    /**
     * Error formatting function
     *
     * @param $error
     * @return string
     */
    public static function format_error($error) {
        return 'Error ' . $error['code'] . ': ' . $error['message'];
    }

    /**
     * Sanitize redirect url string
     */
    public static function sanitize_redirect_url($url) {
        if($url != '') {
            $checkout_url = explode('checkout', WC()->cart->get_checkout_url()); //using this way to get index.php if needed
            $base_url = rtrim($checkout_url[0],'/');
            if(strpos($url,'.') !== false) { //url contain file extension, like .php/.html etc.
                $url = strip_tags(trim($url,'/'));
            } else {
                $url = strip_tags(trim($url,'/')).'/';
            }

            return $base_url . '/' . $url;
        }
        return false;
    }

}