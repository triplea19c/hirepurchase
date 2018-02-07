=== Splitit Installment Payments Enabler ===
Contributors: splitit
Tags: ecommerce, e-commerce, commerce, wordpress ecommerce, sales, sell, shop, shopping, checkout, payment, splitit
Requires at least: 3.0.1
Tested up to: 4.3.1
Stable tag: 2.0.9
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Enables offering shoppers monthly payments on their existing Visa and Master Card credit cards in WooCommerce - Level 1 PCI DSS compliant

== Description ==

Splitit – Interest-Free Monthly Payments plugin for WooCommerce<br>
Start offering your customers **interest-free installment payments** on their existing credit cards today!<br>
The Splitit  WooCommerce plugin lets your customers pay for your goods and services via interest-free monthly installments on the Visa and Master Card credit cards they already have in their wallets.
No credit checks, applications or new credit cards.
Works as long as your customer has available credit on their card equal to the amount of the purchase.
Interest-free installments appear on their regular credit card statement, under your store name.
Your customers continue to enjoy the benefits of their credit cards such as mileage, cash back, and points with no additional billing cycle to manage.
Interest-free installment payments make great business sense!<br><br>
Ecommerce merchants that offer Splitit to their customers enjoy:<br>
-Increased sales <br>
-Higher average tickets<br>
 -Increased conversion rates <br>
-A better alternative to discounts and promotions<br>
-Stronger brand value<br>
<br>
Some more good stuff to know about the Splitit plugin for WooCommerce:<br>
-Installment transactions are validated and guaranteed by the credit card issuer<br>
-Merchants pay a small processing fee and can choose to receive the payments by installment<br>
Or you can receive the total amount upfront after paying a low discount fee<br>
-We are pre-integrated with all major credit card processors and gateways and are Level 1 PCI DSS compliant.

== Installation ==
1. Requires WooCommerce extension to be installed/updated at least to 2.6.5 version first! 
https://wordpress.org/plugins/woocommerce/
2. Upload `splitit` folder to the `/wp-content/plugins/` directory.
3. Activate the plugin through the 'Plugins' menu in WordPress.
4. Configure Splitit API keys and enable the module in WooCommerce -> Settings -> Checkout -> Splitit (tab) before it appears on the checkout page.

<a href="https://www.splitit.com/merchant-application/" target=_blank>Register for Free to Get Your API Keys</a><br>
<a href="https://www.splitit.com/">Read more about Splitit</a>

== Frequently Asked Questions ==

= When I need to charge customer? =

You need to manually charge customer if you`ve set Payment action option in WooCommerce -> Settings -> Checkout -> Splitit (tab) to "Charge my consumer when the shipment is ready".
If option is set to "Charge my consumer at the time of the purchase" then client will be charged automatically in a time of purchase.

= How to charge customer? =

To charge customer you need to open order edit page and select "[Splitit] Charge customer" action in Order Actions section as shown on screenshot.

== Screenshots ==

1. General settings
2. Splitit payment method on checkout
3. Splitit popup wizard
4. Customer charge action

== Changelog ==

= 2.0.9 =
*Fixed coupon,shipping, taxes in Async also handled if fraud cases comes out.

= 2.0.8 =
*Fixed shipping charges in Async URL.

= 2.0.7 =
* Async URL fixed.

= 2.0.6 =
* Validation error fixes.

= 2.0.5 =
* Change redirection after order success.

= 2.0.4 =
* Fixed Plugin collision with other plugins
* Fixed Billing and Shipping address  

= 2.0.3 =
* Images updated


= 2.0.0 =
* Implemented depending on cart total functionality
* Latest Splitit API installation
 
= 0.2.6 =
* Fixed bug when payment wizard popup didn`t load if user cleared Splitit payment method description field in admin. Highly recommended to update up to this version.

= 0.2.5 =
* Reduced number of digits in price displaying to 2 symbols after comma.

= 0.2.4 =
* Fixed terms and conditions field checking.

= 0.2.3 =
* IMPORTANT: Fixed incorrect cookie setting in IE 11 browser.

= 0.2.2 =
* IMPORTANT: Fixed popup wizard not appearing.

= 0.2.1 =
* Improved installment price settings. Code improvements.

= 0.2.0 =
* IMPORTANT: Improved error handling when receive error from API. "-1" response on success page fixed.

= 0.1.9 =
* IMPORTANT: Fixed shipping data missing issue. Checkout process improvements.

= 0.1.8 =
* Fixed price locale.

= 0.1.7 =
* IMPORTANT: fixed error on checkout success action, when "ship to diffetent address" checkbox wasn`t selected.

= 0.1.6 =
* IMPORTANT: checkout fields validation improvements, splitit popup wizard logic improvements.

= 0.1.5 =
* Minor fixes

= 0.1.4 =
* Fixed plugin incorrect behaviour with "Ship to different address" option enabled

= 0.1.3 =
* Added cdn url configuration fields

= 0.1.2 =
* IMPORTANT: splitit payment handler was used by default for all checkout methods. It caused calling Splitit popup wizard even when user selected other than Splitit payment method for checkout. Fixed.

= 0.1.1 =
* Added API response error showing to customer

= 0.1.0 =
* First release
