<?php
defined('ABSPATH') or exit;

if(!class_exists('Wc_Paysera_Html_Form')) {
    require_once 'class-wc-paysera-html-form.php';
}

/**
 * Build Paysera payment methods list
 */
class Wc_Paysera_Payment_Methods
{
    /**
     * Code used for empty fields
     */
    const EMPTY_CODE = '';

    /**
     * HTML NewLine break
     */
    const LINE_BREAK = '<div style="clear:both"><br /></div>';

    /**
     * Min. number of countries in list
     */
    const COUNTRY_SELECT_MIN = 1;

    /**
     * Available languages of payments
     */
    const LANGUAGES = array('lt', 'lv', 'ru', 'en', 'pl', 'bg', 'ee');

    /**
     * Default language if not in the list
     */
    const DEFAULT_LANG = 'en';

    /**
     * @var string
     */
    protected $billingCountry;

    /**
     * @var string
     */
    protected $lang;

    /**
     * @var boolean
     */
    protected $displayList;

    /**
     * @var array
     */
    protected $countriesSelected;

    /**
     * @var boolean
     */
    protected $gridView;

    /**
     * @var string
     */
    protected $description;

    /**
     * @var double
     */
    protected $cartTotal;

    /**
     * @var string
     */
    protected $cartCurrency;

    /**
     * Wc_Paysera_Payment_Methods constructor.
     *
     * @param string  $lang
     * @param string  $billingCountry
     * @param boolean $displayList
     * @param array   $countriesSelected
     * @param boolean $gridView
     * @param string  $description
     * @param double  $cartTotal
     * @param string  $cartCurrency
     */
    public function __construct(
        $lang,
        $billingCountry,
        $displayList,
        $countriesSelected,
        $gridView,
        $description,
        $cartTotal,
        $cartCurrency
    ) {
        $this->lang              = $lang;
        $this->billingCountry    = $billingCountry;
        $this->displayList       = $displayList;
        $this->countriesSelected = $countriesSelected;
        $this->gridView          = $gridView;
        $this->description       = $description;
        $this->cartTotal         = $cartTotal;
        $this->cartCurrency      = $cartCurrency;
    }

    /**
     * @param boolean [Optional] $print
     *
     * @return boolean|string
     */
    public function build($print = true)
    {
        $buildHtml = new Wc_Paysera_Html_Form();

        if ($this->isDisplayList()) {
            $payseraCountries = $this->getPayseraCountries(
                $this->getCartTotal(),
                $this->getCartCurrency(),
                $this->listLang()
            );

            $countries = $this->getCountriesList($payseraCountries);

            if (count($countries) > $this::COUNTRY_SELECT_MIN) {
                $paymentsHtml = $buildHtml->buildCountriesList(
                    $countries,
                    $this->getBillingCountry()
                );
                $paymentsHtml .= $this::LINE_BREAK;
            } else {
                $paymentsHtml = $this::EMPTY_CODE;
            }

            $paymentsHtml .= $buildHtml->buildPaymentsList(
                $countries,
                $this->isGridView(),
                $this->getBillingCountry()
            );
            $paymentsHtml .= $this::LINE_BREAK;
        } else {
            $paymentsHtml = $this->getDescription();
        }

        if ($print) {
            print_r($paymentsHtml);
            return $print;
        } else {
            return $paymentsHtml;
        }
    }

    /**
     * @param integer $project
     * @param string  $currency
     * @param string  $lang
     *
     * @return WebToPay_PaymentMethodCountry[]
     */
    protected function getPayseraCountries($project, $currency, $lang)
    {
        $countries = WebToPay::getPaymentMethodList(
            $project,
            $currency
        )->setDefaultLanguage(
            $lang
        )->getCountries();

        return $countries;
    }

    /**
     * @param array $countries
     *
     * @return array
     */
    protected function getCountriesList($countries)
    {
        $countriesList = [];
        $showSelectedCountries = is_array($this->getCountriesSelected());
        $selectedCountriesCodes = $this->getCountriesSelected();

        foreach ($countries as $country) {
            $checkForCountry = true;
            if ($showSelectedCountries) {
                $checkForCountry = in_array($country->getCode(), $selectedCountriesCodes);
            }

            if ($checkForCountry) {
                $countriesList[] = [
                    'code'   => $country->getCode(),
                    'title'  => $country->getTitle(),
                    'groups' => $country->getGroups()
                ];
            }
        }

        return $countriesList;
    }

    /**
     * @return string
     */
    protected function listLang()
    {
        if (in_array($this->getLang(), $this::LANGUAGES)) {
            $listLang = $this->getLang();
        } else {
            $listLang = $this::DEFAULT_LANG;
        }

        return $listLang;
    }

    /**
     * @return string
     */
    public function getBillingCountry()
    {
        return $this->billingCountry;
    }


    /**
     * @param string $billingCountry
     */
    public function setBillingCountry($billingCountry)
    {
        $this->billingCountry = $billingCountry;
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
     * @return boolean
     */
    public function isDisplayList()
    {
        return $this->displayList;
    }

    /**
     * @param boolean $displayList
     */
    public function setDisplayList($displayList)
    {
        $this->displayList = $displayList;
    }

    /**
     * @return array
     */
    public function getCountriesSelected()
    {
        return $this->countriesSelected;
    }

    /**
     * @param array $countriesSelected
     */
    public function setCountriesSelected($countriesSelected)
    {
        $this->countriesSelected = $countriesSelected;
    }

    /**
     * @return boolean
     */
    public function isGridView()
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
     * @return double
     */
    public function getCartTotal()
    {
        return $this->cartTotal;
    }

    /**
     * @param double $cartTotal
     */
    public function setCartTotal($cartTotal)
    {
        $this->cartTotal = $cartTotal;
    }

    /**
     * @return string
     */
    public function getCartCurrency()
    {
        return $this->cartCurrency;
    }

    /**
     * @param string $cartCurrency
     */
    public function setCartCurrency($cartCurrency)
    {
        $this->cartCurrency = $cartCurrency;
    }
}
