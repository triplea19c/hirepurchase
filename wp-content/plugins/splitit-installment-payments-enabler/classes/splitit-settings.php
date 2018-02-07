<?php
/**
 * SplitIt_Settings class
 *
 * @class       SplitIt_Settings
 * @version     0.2.9
 * @package     SplitIt/Classes
 * @category    Settings
 * @author      By Splitit
 */
class SplitIt_Settings {

    /**
     * Returns an array of available admin settings fields
     *
     * @access public static
     * @return array
     */
    public static function get_fields()
    {


        $fields =
            array(
                '_General_settings' => array(
                    'type' => 'title',
                    'title' => __( 'General settings', 'splitit' ),
                    'description' => __('Api and debug settings')
                ),
                'enabled' => array(
                    'title' => __( 'Enable/Disable', 'splitit' ),
                    'type' => 'checkbox',
                    'label' => __( 'Enable Splitit Payment', 'splitit' ),
                    'default' => 'yes'
                ),
                'splitit_api_prod_url' => array(
                    'title' => __( 'API Production URL', 'splitit' ),
                    'type' => 'text',
                    'default' => 'https://web-api.splitit.com/'
                ),
                'splitit_cdn_prod_url' => array(
                    'title' => __( 'CDN Production URL', 'splitit' ),
                    'type' => 'text',
                    'default' => 'https://cdn.splitit.com/'
                ),
                'splitit_api_sand_url' => array(
                    'title' => __( 'API Sandbox URL', 'splitit' ),
                    'type' => 'text',
                    'default' => 'https://web-api-sandbox.splitit.com/'
                ),
                'splitit_cdn_sand_url' => array(
                    'title' => __( 'CDN Sandbox URL', 'splitit' ),
                    'type' => 'text',
                    'default' => 'https://cdn-sandbox.splitit.com/'
                ),
                'splitit_api_terminal_key' => array(
                    'title' => __( 'Terminal API key', 'splitit' ),
                    'type' => 'text'
                ),
                'splitit_api_username' => array(
                    'title' => __( 'API Username', 'splitit' ),
                    'type' => 'text'
                ),
                'splitit_api_password' => array(
                    'title' => __( 'API Password', 'splitit' ),
                    'type' => 'text'
                ),
                'splitit_discount_type' => array(
                    'title' => __('Select installment setup', 'splitit'),
                    'desc_tip' => true,
                    'type' => 'select',
                    'options' => array(
                        'fixed' => 'Fixed',
                        'depending_on_cart_total' => 'Depending on cart total'
                    )
                ),               
                'splitit_discount_type_fixed' => array(
                    'title'     => __('Select Installment options', 'splitit'),
                    'desc_tip' => true,
                    'type'    => 'multiselect',
                    'css'     => 'width: 350px; height: 185px;',
                    'options' => array(
                        '2'   => '2 Installments',
                        '3'   => '3 Installments',
                        '4'   => '4 Installments',
                        '5'   => '5 Installments',
                        '6'   => '6 Installments',
                        '7'   => '7 Installments',
                        '8'   => '8 Installments',
                        '9'   => '9 Installments',
                        '10'  => '10 Installments',
                        '11'  => '11 Installments',
                        '12'  => '12 Installments' 
                    )
                ),
                'splitit_doct' => array(
                    array( 
                        'ct_from' => array( 
                            'title' => __( 'test1', 'splitit' ),
                            'type' => 'number',
                            'class' => 'doctv_from',
                            'default' => '0'
                        ),
                        'ct_to' => array( 
                            'title' => __( 'test2', 'splitit' ),
                            'type' => 'number',
                            'class' => 'doctv_to',
                            'default' => '300'
                        ),
                        'ct_instllment' => array( 
                            'title' => __( 'test3', 'splitit' ),
                            'type' => 'multiselect',
                            'class' => 'doctv_installments',
                            'options' => array(
                                '2'   => '2 Installments',
                                '3'   => '3 Installments',
                                '4'   => '4 Installments',
                                '5'   => '5 Installments',
                                '6'   => '6 Installments',
                                '7'   => '7 Installments',
                                '8'   => '8 Installments',
                                '9'   => '9 Installments',
                                '10'  => '10 Installments',
                                '11'  => '11 Installments',
                                '12'  => '12 Installments'
                            ),
                            'default' => array('2','3')
                        ),
                        'ct_currency' => array( 
                            'title' => __( 'test4', 'splitit' ),
                            'type' => 'text',
                            'default' => get_woocommerce_currency()
                        )
                    ), 
                    array( 
                        'ct_from' => array( 
                            'title' => __( 'test12', 'splitit' ),
                            'type' => 'number',
                            'class' => 'doctv_from',
                            'default' => '301'
                        ),
                        'ct_to' => array( 
                            'title' => __( 'test22', 'splitit' ),
                            'type' => 'number',
                            'class' => 'doctv_to',
                            'default' => '500'
                        ),
                        'ct_instllment' => array( 
                            'title' => __( 'test32', 'splitit' ),
                            'type' => 'multiselect',
                            'class' => 'doctv_installments',
                            'options' => array(
                                '2'   => '2 Installments',
                                '3'   => '3 Installments',
                                '4'   => '4 Installments',
                                '5'   => '5 Installments',
                                '6'   => '6 Installments',
                                '7'   => '7 Installments',
                                '8'   => '8 Installments',
                                '9'   => '9 Installments',
                                '10'  => '10 Installments',
                                '11'  => '11 Installments',
                                '12'  => '12 Installments'
                            ),
                            'default' => array('2','3','4')
                        ),
                        'ct_currency' => array( 
                            'title' => __( 'test42', 'splitit' ),
                            'type' => 'text',
                            'default' => get_woocommerce_currency()
                        )
                    ), 
                    array( 
                        'ct_from' => array( 
                            'title' => __( 'test12', 'splitit' ),
                            'type' => 'number',
                            'class' => 'doctv_from',
                            'default' => '501'
                        ),
                        'ct_to' => array( 
                            'title' => __( 'test22', 'splitit' ),
                            'type' => 'number',
                            'class' => 'doctv_to',
                            'default' => '700'
                        ),
                        'ct_instllment' => array( 
                            'title' => __( 'test32', 'splitit' ),
                            'type' => 'multiselect',
                            'class' => 'doctv_installments',
                            'options' => array(
                                '2'   => '2 Installments',
                                '3'   => '3 Installments',
                                '4'   => '4 Installments',
                                '5'   => '5 Installments',
                                '6'   => '6 Installments',
                                '7'   => '7 Installments',
                                '8'   => '8 Installments',
                                '9'   => '9 Installments',
                                '10'  => '10 Installments',
                                '11'  => '11 Installments',
                                '12'  => '12 Installments'
                            ),
                            'default' => array('2','3','4','5')
                        ),
                        'ct_currency' => array( 
                            'title' => __( 'test42', 'splitit' ),
                            'type' => 'text',
                            'default' => get_woocommerce_currency()
                        )
                    ),
                    array( 
                        'ct_from' => array( 
                            'title' => __( 'test12', 'splitit' ),
                            'type' => 'number',
                            'class' => 'doctv_from',
                            'default' => '701'
                        ),
                        'ct_to' => array( 
                            'title' => __( 'test22', 'splitit' ),
                            'type' => 'number',
                            'class' => 'doctv_to',
                            'default' => '1000'
                        ),
                        'ct_instllment' => array( 
                            'title' => __( 'test32', 'splitit' ),
                            'type' => 'multiselect',
                            'class' => 'doctv_installments',
                            'options' => array(
                                '2'   => '2 Installments',
                                '3'   => '3 Installments',
                                '4'   => '4 Installments',
                                '5'   => '5 Installments',
                                '6'   => '6 Installments',
                                '7'   => '7 Installments',
                                '8'   => '8 Installments',
                                '9'   => '9 Installments',
                                '10'  => '10 Installments',
                                '11'  => '11 Installments',
                                '12'  => '12 Installments'
                            ),
                            'default' => array('2','3','4','5','6','7')

                        ),
                        'ct_currency' => array( 
                            'title' => __( 'test42', 'splitit' ),
                            'type' => 'text',
                            'default' => get_woocommerce_currency()
                        )
                    ),
                    array( 
                        'ct_from' => array( 
                            'title' => __( 'test12', 'splitit' ),
                            'type' => 'number',
                            'class' => 'doctv_from',
                            'default' => '1001'
                        ),
                        'ct_to' => array( 
                            'title' => __( 'test22', 'splitit' ),
                            'type' => 'number',
                            'class' => 'doctv_to',
                            'default' => '10000'
                        ),
                        'ct_instllment' => array( 
                            'title' => __( 'test32', 'splitit' ),
                            'type' => 'multiselect',
                            'class' => 'doctv_installments',
                            'options' => array(
                                '2'   => '2 Installments',
                                '3'   => '3 Installments',
                                '4'   => '4 Installments',
                                '5'   => '5 Installments',
                                '6'   => '6 Installments',
                                '7'   => '7 Installments',
                                '8'   => '8 Installments',
                                '9'   => '9 Installments',
                                '10'  => '10 Installments',
                                '11'  => '11 Installments',
                                '12'  => '12 Installments'
                            ),
                            'default' => array('2','3','4','5','6','7','8','9','10','11','12')
                        ),
                        'ct_currency' => array( 
                            'title' => __( 'test42', 'splitit' ),
                            'type' => 'text',
                            'default' => get_woocommerce_currency()
                        )
                    ),
                ),


                'splitit_mode_sandbox' => array(
                    'title' => __('Sandbox Mode', 'splitit'),
                    'description' => __('Sandbox Mode for testing purposes (uses API Sandbox URL).', 'splitit'),
                    'desc_tip' => true,
                    'type' => 'select',
                    'options' => array(
                        'no' => 'No',
                        'yes' => 'Yes'
                    )
                ),
                'splitit_mode_debug' => array(
                    'title' => __('Debug Mode', 'splitit'),
                    'description' => __('Enables Splitit request data logging.', 'splitit'),
                    'desc_tip' => true,
                    'type' => 'select',
                    'options' => array(
                        'no' => 'No',
                        'yes' => 'Yes'
                    )
                ),
                'splitit_test_api' => array(
                    'title' => '<a href="" id="checkApiCredentials">Check Credential API</a>',
                    'css' => 'display:none;'
                ),

                '_Shop_setup' => array(
                    'type' => 'title',
                    'title' => __( 'Shop setup', 'splitit' ),
                    'description' => __('Splitit settings visible on frontend')
                ),
                'title' => array(
                    'title' => __( 'Title', 'splitit' ),
                    'type' => 'text',
                    'description' => __( 'This controls the title which the user sees during checkout.', 'splitit' ),
                    'default' => __( 'INTEREST FREE Monthly Payment', 'splitit' ),
                    'desc_tip' => true
                ),
                'description' => array(
                    'title' => __( 'Customer Message', 'splitit' ),
                    'type' => 'textarea',
                    'description' => __( 'This controls the description which the user sees during checkout.', 'splitit' ),
                    'default' => 'Split your purchase easily into Free Interest Monthly Payment, using your existing credit card - Instantly and without credit check.',
                    'desc_tip' => true
                ),
                'splitit_enable_help' => array(
                    'title' => __( 'Help link enabled', 'splitit' ),
                    'type' => 'checkbox',
                    'default' => 'yes'
                ),
                'splitit_help_title' => array(
                    'title' => __( 'Help link title', 'splitit' ),
                    'type' => 'text',
                    'default' => __( 'Tell me more', 'splitit' ),
                ),
//                'splitit_order_status' => array(
//                    'title' => __( 'New order status', 'splitit' ),
//                    'type' => 'select',
//                    'description' => __('Select status for PayItSimple orders', 'splitit'),
//                    'desc_tip'          => true,
//                    'class'             => 'wc-enhanced-select',
//                    'css'               => 'width: 450px;',
//                    'custom_attributes' => array(
//                        'data-placeholder' => __( 'Select order status', 'splitit' )
//                    ),
//                    'default' => '',
//                    'options' => wc_get_order_statuses()
//                ),
            'splitit_payment_action' => array(
                'title' => __( 'Payment action', 'splitit' ),
                'type'  => 'select',
                'class'             => 'wc-enhanced-select',
                'css'               => 'width: 450px;',
//                    'custom_attributes' => array(
//                        'data-placeholder' => __( 'Select order status', 'splitit' )
//                    ),
                'default' => '',
                'options' => array(
                    'purchase' => 'Charge my consumer at the time of the purchase',
                    'shipped' => 'Charge my consumer when the shipment is ready'
                )
            ),
            'splitit_cc' => array(
                'title' => __( 'Credit card types', 'splitit' ),
                'type' => 'multiselect',
                'description' => __( 'Choose the card icons you wish to show next to the Splitit payment option in your shop.', 'splitit' ),
                'desc_tip' => true,
                'class'             => 'wc-enhanced-select',
                'css'               => 'width: 450px;',
                'custom_attributes' => array(
                    'data-placeholder' => __( 'Choose credit cards', 'splitit' )
                ),
                'options' => array(
                    'visa'       => 'Visa',
                    'mastercard' => 'Mastercard',
                ),
                'default'=>array('visa','mastercard')
            ),
            'splitit_max_installments_limit' => array(
                'title' => __('Number of installment for display', 'splitit'),
                'type' => 'select',
                'default' => 12,
                'class'             => 'wc-enhanced-select',
                'css'               => 'width: 450px;',
                'options' => self::get_available_installments()
            ),
            'splitit_without_interest' => array(
                'title' => __( 'Installment price text', 'splitit' ),
                'type' => 'text',
                'description' => 'Default is "without interest"',
                'default' => 'without interest'
            ),
            'custom_urls' => array(
                    'title' => __('Define default/custom URL', 'splitit'),
                    'desc_tip' => true,
                    'type' => 'select',
                    'options' => array(
                        'default' => 'Default',
                        'custom' => 'Custom'
                    )
                ),
            'splitit_cancel_url' => array(
                'title' => __( 'Cancel payment url', 'splitit' ),
                'type' => 'text',
                'default' => 'checkout/',
                'class' => 'custom_urls',
                'placeholder' => __( 'Default url is "checkout/"', 'splitit' ),
                'description' => __( 'Enter url (without domain) which will be used for redirect on Splitit cancel payment action.', 'splitit' ),
                'desc_tip' => true
            ),
            'splitit_error_url' => array(
                'title' => __( 'Error payment url', 'splitit' ),
                'type' => 'text',
                'default' => 'checkout/',
                'class' => 'custom_urls',
                'placeholder' => __( 'Default url is "checkout/"', 'splitit' ),
                'description' => __( 'Enter url (without domain) which will be used for redirect on Splitit error payment action.', 'splitit' ),
                'desc_tip' => true
            ),
            'splitit_success_url' => array(
                'title' => __( 'Success payment url', 'splitit' ),
                'type' => 'text',
                'class' => 'custom_urls',
                'default' => 'checkout/',
                'placeholder' => __( 'Default url is "checkout/"', 'splitit' ),
                'description' => __( 'Enter url (without domain) which will be used for redirect on Splitit success payment action.', 'splitit' ),
                'desc_tip' => true
            ),

            '_Installment_price_setup' => array(
                'type' => 'title',
                'title' => __( 'Installment price setup', 'splitit' ),
                'description' => __('Installment price functionality settings')
            ),

            'splitit_enable_installment_price' => array(
                'title' => __( 'Enable/Disable', 'splitit' ),
                'description' => __('Installment price will be calculated based on max installment value'),
                'type' => 'checkbox',
                'label' => __( 'Enable Installment price functionality', 'splitit' ),
                'default' => 'yes'
            ),

            'splitit_installment_price_sections' => array(
                'title'     => __('Sections/pages where to display installment price', 'splitit'),
                'description' => __( 'Select pages to show installment prices.', 'splitit' ),
                'desc_tip' => true,
                'type'    => 'multiselect',
                'css'     => 'width: 350px; height: 185px;',
                'options' => self::get_installment_price_sections()
            ),
           

            '_Splitit_banners' => array(
                'type' => 'title',
                'title' => __( 'Splitit banners', 'splitit' ),
                'description' => __('Choose your Splitit Banner.<br><a href="https://www.splitit.com/for-developers/how-to-promote/banners-library/" target="_blank">Click here to see all Splitit banners</a><p>Copy and paste this code block in a place (template/page) you want to display banner.</p>')
            ),
            //below parameters doesn`t need to be processed
            'splitit_banner1' => array(
                'title' => __( '<img src="https://www.splitit.com/wp-content/uploads/2015/10/120x240.jpg" />', 'splitit' ),
                'type' => 'textarea',
                'css' => 'height: 50px; position: absolute; top: 15px; width: 800px;', //dirty way to align code blocks in a way we need
                'default' => "<div class='pisAncorBanner' PISdata='PIS_120x240_1'></div>
<script id='pisBannersScript' src='https://cdn.splitit.com/Scripts/banners/banners.js'></script>",
            ),
            'splitit_banner2' => array(
                'title' => __( '', 'splitit' ),
                'type' => 'textarea',
                'css' => 'height: 50px; position: absolute; top: 75px; width: 800px;',
                'default' => "<div class='pisAncorBanner' PISdata='PIS_120x600_1'></div>
<script id='pisBannersScript' src='https://cdn.splitit.com/Scripts/banners/banners.js'></script>",
            ),
            'splitit_banner3' => array(
                'title' => __( '<img src="https://www.splitit.com/wp-content/uploads/2015/10/250x250.jpg" />', 'splitit' ),
                'type' => 'textarea',
                'css' => 'height: 50px; position: absolute; width: 800px; top: 340px;',
                'default' => "<div class='pisAncorBanner' PISdata='PIS_250x250_1'></div>
<script id='pisBannersScript' src='https://cdn.splitit.com/Scripts/banners/banners.js'></script>",
            ),
            'splitit_banner4' => array(
                'title' => __( '', 'splitit' ),
                'type' => 'textarea',
                'css' => 'height: 50px; width: 800px; position: absolute; top: 400px;',
                'default' => "<div class='pisAncorBanner' PISdata='PIS_300x250_1'></div>
<script id='pisBannersScript' src='https://cdn.splitit.com/Scripts/banners/banners.js'></script>",
            ),
            'splitit_banner5' => array(
                'title' => __( '', 'splitit' ),
                'type' => 'textarea',
                'css' => 'height: 50px; width: 800px; position: absolute; top: 460px;',
                'default' => "<div class='pisAncorBanner' PISdata='PIS_468x60_1'></div>
<script id='pisBannersScript' src='https://cdn.splitit.com/Scripts/banners/banners.js'></script>",
            ),
            'splitit_banner6' => array(
                'title' => __( '', 'splitit' ),
                'type' => 'textarea',
                'css' => 'height: 50px; position: absolute; top: 520px; width: 800px;',
                'default' => "<div class='pisAncorBanner' PISdata='PIS_728x90_1'></div>
<script id='pisBannersScript' src='https://cdn.splitit.com/Scripts/banners/banners.js'></script>",
            ),
        );

        return $fields;
    }

    /**
     * Provides a list of installments
     *
     * @access private
     * @return array
     */
    private static function get_available_installments() {
        $installments_left_limit = 2;
        $installments_right_limit = 12;
        $installments = array();
        for($i = $installments_left_limit; $i <= $installments_right_limit; $i++) {
            $installments[$i] = $i . ' Installments';
        }
        return $installments;
    }

    /**
     * Provides a list of countires
     *
     * @access private
     * @return array
     */
    private static function get_countries() {
        $countries = new WC_Countries;
        return $countries->get_countries();
    }

    /**
     * Avaliable sections to show installment price
     *
     * @return array
     */
    private static function get_installment_price_sections() {
        return array(
            'product'  => 'Product page',
            'category' => 'Category page',
            'cart'     => 'Shopping cart',
            'checkout' => 'Checkout'
        );
    }
}
?>