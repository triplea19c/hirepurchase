<?php

defined('ABSPATH') or exit;

/**
 * Paysera Gateway Admin settings
 */
class Wc_Paysera_Settings extends WC_Payment_Gateway
{
    /**
     * Default project id
     */
    const DEFAULT_PROJECT = 0;

    /**
     * @var integer
     */
    protected $projectID;

    /**
     * @var string
     */
    protected $currency;

    /**
     * @var string
     */
    protected $lang;

    /**
     * @var array
     */
    protected $formFields;

    /**
     * Wc_Paysera_Settings constructor.
     *
     * @param integer $projectID
     * @param string  $currency
     * @param string  $lang
     */
    public function __construct($projectID, $currency, $lang)
    {
        $this->projectID  = $projectID;
        $this->currency   = $currency;
        $this->lang       = $lang;
        $this->formFields = array(
            'enabled' => array(
                'title' => __('Enable Paysera', 'woocommerce'),
                'label' => __('Enable Paysera payment', 'woocommerce'),
                'type' => 'checkbox',
                'description' => '',
                'default' => 'no'
            ),
            'projectid' => array(
                'title' => __('Project ID', 'woocommerce'),
                'type' => 'number',
                'description' => __('Project id', 'woocommerce'),
                'default' => __('', 'woocommerce')
            ),
            'password' => array(
                'title' => __('Sign', 'woocommerce'),
                'type' => 'text',
                'description' => __('Paysera sign password', 'woocommerce'),
                'default' => __('', 'woocommerce')
            ),
            'test' => array(
                'title' => __('Test', 'woocommerce'),
                'type' => 'checkbox',
                'label' => __('Enable test mode', 'woocommerce'),
                'default' => 'yes',
                'description' => __('Enable this to accept test payments', 'woocommerce'),
            ),
            'title' => array(
                'title' => __('Title', 'woocommerce'),
                'type' => 'text',
                'description' => __('Payment method title that the customer will see on your website.', 'woocommerce'),
                'default' => __('Paysera', 'woocommerce')
            ),
            'description' => array(
                'title' => __('Description', 'woocommerce'),
                'type' => 'textarea',
                'description' => __('This controls the description which the user sees during checkout.', 'woocommerce'),
                'default' => __('Make payment method choice on Paysera page')
            ),
            'paymentType' => array(
                'title' => __('List of payments', 'woocommerce'),
                'type' => 'checkbox',
                'label' => __('Display payment methods list', 'woocommerce'),
                'default' => 'no',
                'description' => __('Enable this to display payment methods list at checkout page', 'woocommerce'),
            ),
            'countriesSelected' => array(
                'title' => __('Specific countries', 'woocommerce'),
                'type' => 'multiselect',
                'class'	=> 'wc-enhanced-select',
                'css' => 'width: 400px;',
                'default' => '',
                'description' => __('Select which country payments to display (empty means all)', 'woocommerce'),
                'options' => array(),
                'custom_attributes' => array(
                    'data-placeholder' => __('All countries', 'woocommerce'),
                ),
            ),
            'style' => array(
                'title' => __('Grid view', 'woocommerce'),
                'type' => 'checkbox',
                'label' => __('Enable grid view', 'woocommerce'),
                'default' => 'no',
                'description' => __('Enable this to use payment methods grid view', 'woocommerce'),
            ),
            'paymentNewOrderStatus' => array(
                'title' => __('New Order Status', 'woocommerce'),
                'type' => 'select',
                'class'	=> 'wc-enhanced-select',
                'default' => '',
                'description' => __('New order creation status', 'woocommerce'),
                'options' => array(),
            ),
            'paymentCompletedStatus' => array(
                'title' => __('Paid Order Status', 'woocommerce'),
                'type' => 'select',
                'class'	=> 'wc-enhanced-select',
                'default' => '',
                'description' => __('Order status after completing payment', 'woocommerce'),
                'options' => array(),
            ),
            'paymentCanceledStatus' => array(
                'title' => __('Pending checkout', 'woocommerce'),
                'type' => 'select',
                'class'	=> 'wc-enhanced-select',
                'default' => '',
                'description' => __('Order status with not finished checkout', 'woocommerce'),
                'options' => array(),
            )
        );
    }

    /**
     * @param object $tabs
     * @param boolean [Optional] $print
     *
     * @return boolean|string
     */
    public function buildAdminFormHtml($tabs, $print = true)
    {
        $htmlData = $this->generateFormFields($tabs);

        $html  = '<div class="plugin_config">';
        $html .= '<h2>' . $htmlData['links'] . '</h2>';
        $html .= '<div style="clear:both;"><hr /></div>';
        $html .= $htmlData['tabs'];
        $html .= '</div>';

        if ($print) {
            print_r($html);
            return $print;
        } else {
            return $html;
        }
    }

    /**
     * @return array
     */
    public function generateNewSettings()
    {
        return [
            'countries' => $this->getPayseraListCountries(),
            'statuses'  => $this->getStatusList()
        ];
    }

    /**
     * @return array
     */
    protected function getPayseraListCountries()
    {
        $validProjectID = $this->getValidProject($this->getProjectID());

        $pmethods = WebToPay::getPaymentMethodList(
            $validProjectID,
            $this->getCurrency()
        )->setDefaultLanguage(
            $this->getLang()
        )->getCountries();

        $langList = array();
        foreach ($pmethods as $country) {
            $langList[$country->getCode()] = $country->getTitle();
        }

        return $langList;
    }

    /**
     * @return array
     */
    protected function getStatusList()
    {
        $wcStatus = array_keys(wc_get_order_statuses());
        $orderStatus = array();
        foreach ($wcStatus as $key => $value) {
            $orderStatus[$wcStatus[$key]] = wc_get_order_status_name($wcStatus[$key]);
        }

        return $orderStatus;
    }

    /**
     * @param integer $projectID
     *
     * @return integer
     */
    protected function getValidProject($projectID)
    {
        if ($projectID) {
            $definedProjectID = $projectID;
        } else {
            $definedProjectID = $this::DEFAULT_PROJECT;
        }

        return $definedProjectID;
    }

    /**
     * @param object $tabs
     *
     * @return array
     */
    protected function generateFormFields($tabs)
    {
        $tabsLink = '';
        $tabsContent = '';
        foreach ($tabs as $key => $value) {
            $tabsLink .= '<a href="javascript:void(0)"';
            $tabsLink .= ' id="tab' . $key . '" class="nav-tab"';
            $tabsLink .= ' data-cont="content' . $key . '">';
            $tabsLink .=  $value['name'] . '</a>';

            $tabsContent .= '<div id="content' . $key . '" class="tabContent">';
            $tabsContent .= '<table class="form-table">' . $value['slice'] . '</table>';
            $tabsContent .= '</div>';
        }

        return [
            'links' => $tabsLink,
            'tabs'  => $tabsContent
        ];
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
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * @param string $currency
     */
    public function setCurrency($currency)
    {
        $this->currency = $currency;
    }

    /**
     * @return string
     */
    public function getLang()
    {
        return $this->lang;
    }

    /**
     * @param string $lang
     */
    public function setLang($lang)
    {
        $this->lang = $lang;
    }

    /**
     * @return array
     */
    public function getFormFields()
    {
        return $this->formFields;
    }

    /**
     * @param array $formFields
     */
    public function setFormFields($formFields)
    {
        $this->formFields = $formFields;
    }
}
