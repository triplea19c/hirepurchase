<?php

defined('ABSPATH') or exit;

/**
 * Build payment request
 */
class Wc_Paysera_Request
{
    /**
     * Available languages for Paysera checkout page
     */
    const AVAILABLE_TRANSLATION = array(
        'lt' => 'LIT',
        'lv' => 'LAV',
        'ee' => 'EST',
        'ru' => 'RUS',
        'de' => 'GER',
        'pl' => 'POL',
        'en' => 'ENG'
    );
    /**
     * Default language for Paysera checkout page
     */
    const DEFAULT_LANG = 'ENG';

    /**
     * @var integer
     */
    protected $projectID;
    
    /**
     * @var string
     */
    protected $signature;
    
    /**
     * @var string
     */
    protected $returnUrl;
    
    /**
     * @var string
     */
    protected $callbackUrl;
    
    /**
     * @var boolean
     */
    protected $test;
    
    /**
     * @var string
     */
    protected $locale;

    /**
     * Wc_Paysera_Request constructor.
     * 
     * @param integer $projectID
     * @param string  $signature
     * @param string  $returnUrl
     * @param string  $callbackUrl
     * @param boolean $test
     * @param string  $locale
     */
    public function __construct(
        $projectID,
        $signature,
        $returnUrl,
        $callbackUrl,
        $test,
        $locale
    ) {
        $this->projectID = $projectID;
        $this->signature = $signature;
        $this->returnUrl = $returnUrl;
        $this->callbackUrl = $callbackUrl;
        $this->test = $test;
        $this->locale = $locale;
    }

    /**
     * Create request url
     * 
     * @param array $parameters
     * 
     * @return string
     */
    public function buildUrl($parameters)
    {
        if ($parameters['prebuild']) {
            $parameters = $this->buildParameters($parameters);
        }

        $request = WebToPay::buildRequest($parameters);
        $url = WebToPay::PAY_URL . '?' . http_build_query($request);

        return preg_replace('/[\r\n]+/is', '', $url);
    }

    /**
     * Get WooCommerce parameters for request
     * 
     * @param object $order
     * @param string $payment
     * 
     * @return array
     */
    public function getWooParameters($order, $payment)
    {
        if ($this::AVAILABLE_TRANSLATION[$this->getLocale()]) {
            $lang = $this::AVAILABLE_TRANSLATION[$this->getLocale()];
        } else {
            $lang = $this::DEFAULT_LANG;
        }

        return array (
            'prebuild'      => true,
            'order'         => $order->get_id(),
            'amount'        => intval(number_format($order->get_total(),2,'','')),
            'currency'      => $order->get_currency(),
            'country'       => $order->get_billing_country(),
            'cancel'        => $order->get_cancel_order_url(),
            'payment'       => $payment,
            'firstname'     => $order->get_billing_first_name(),
            'lastname'      => $order->get_billing_last_name(),
            'email'         => $order->get_billing_email(),
            'street'        => $order->get_billing_address_1(),
            'city'          => $order->get_billing_city(),
            'state'         => $order->get_billing_state(),
            'zip'           => $order->get_billing_postcode(),
            'countrycode'   => $order->get_billing_country(),
            'lang'          => $lang
        );
    }

    /**
     * Build parameters array, which meets Paysera requirements
     * 
     * @param array $parameters
     * 
     * @return array
     */
    protected function buildParameters($parameters)
    {
        return array(
            'projectid'     => $this->limitLenght($this->getProjectID(), 11),
            'sign_password' => $this->limitLenght($this->getSignature()),
            'orderid'       => $this->limitLenght($parameters['order'], 40),
            'amount'        => $this->limitLenght($parameters['amount'], 11),
            'currency'      => $this->limitLenght($parameters['currency'], 3),
            'country'       => $this->limitLenght($parameters['country'], 2),
            'accepturl'     => $this->limitLenght($this->getReturnUrl()),
            'cancelurl'     => $this->limitLenght($parameters['cancel']),
            'callbackurl'   => $this->limitLenght($this->getCallbackUrl()),
            'p_firstname'   => $this->limitLenght($parameters['firstname']),
            'p_lastname'    => $this->limitLenght($parameters['lastname']),
            'p_email'       => $this->limitLenght($parameters['email']),
            'p_street'      => $this->limitLenght($parameters['street']),
            'p_countrycode' => $this->limitLenght($parameters['country'], 2),
            'p_city'        => $this->limitLenght($parameters['city']),
            'p_state'       => $this->limitLenght($parameters['state'], 20),
            'payment'       => $this->limitLenght($parameters['payment'], 20),
            'p_zip'         => $this->limitLenght($parameters['zip'], 20),
            'lang'          => $this->limitLenght($parameters['lang'], 3),
            'test'          => $this->limitLenght((int)$this->getTest(), 1)
        );
    }

    /**
     * Limit lenght of the string
     *
     * @param  string  $string
     * @param  integer $limit
     *
     * @return string
     */
    protected function limitLenght($string, $limit = 255) {
        if (strlen($string) > $limit) {
            $string = substr($string, 0, $limit);
        }

        return $string;
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
    public function setProjectID($projectID)
    {
        $this->projectID = $projectID;
    }

    /**
     * @return string
     */
    public function getSignature()
    {
        return $this->signature;
    }

    /**
     * @param string $signature
     */
    public function setSignature($signature)
    {
        $this->signature = $signature;
    }

    /**
     * @return string
     */
    public function getReturnUrl()
    {
        return $this->returnUrl;
    }

    /**
     * @param string $returnUrl
     */
    public function setReturnUrl($returnUrl)
    {
        $this->returnUrl = $returnUrl;
    }

    /**
     * @return string
     */
    public function getCallbackUrl()
    {
        return $this->callbackUrl;
    }

    /**
     * @param string $callbackUrl
     */
    public function setCallbackUrl($callbackUrl)
    {
        $this->callbackUrl = $callbackUrl;
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
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * @param string $locale
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;
    }
}
