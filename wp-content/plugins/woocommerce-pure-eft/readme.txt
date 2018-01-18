=== WooCommerce Pure EFT Gateway ===
Contributors: webunraveling
Tags: woocommerce, eft, payment, gateway, ecommerce, bank, electronic, bank, transfer funds
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=LJAT6WCWJCQUW
Requires at least: 4.0
Tested up to: 4.4
Stable tag: 1.2
License: GPLv2
License URI: http://www.gnu.org/licenses/gpl-2.0.html

This WooCommerce payment gateway plugin accepts account information for you to process Electronic Funds Transfer (EFT) payments.

== Description ==
A very straight forward plugin/payment gateway for accepting Electronic Funds Transfer (EFT) in WooCommerce. It provides a checkout option for a customer to enter their bank account and routing numbers and choose the account type (savings or checking). When the customer checks out, the account information is added to the notes on the order (and optionally the admin email). The customer's email shows the last 3 digits of their account number for their own records.

You can then use this information to submit and EFT to your bank.

This plugin has been tested on WordPress 4.0 and up.

If you found this useful, please buy me a cup of tea:

* **[Donate with PayPal](https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=LJAT6WCWJCQUW)**
* **[Donate with Bitcoin](http://webunraveling.com/public/business/donate/pure-eft.html)**

== Installation ==
1. Upload the ZIP file to your `/wp-content/plugins/` directory and extract it there.
1. Activate the plugin through the 'Plugins' menu in WordPress
1. From the WordPress dashboard, click WooCommerce > Settings > Checkout
1. Look for the section for "Payment Gateways". Under that section, click the settings for "Pure EFT" and click "Settings"
1. Make sure the checkbox for "Enable EFT Payment" is checked, to activate the payment gateway


== Frequently Asked Questions ==
= Have any questions? =

Please let me know by [contacting me by email](https://webunraveling.com/contact).

== Screenshots ==
1. User checkout
2. Admin options

== Changelog ==
= 1.2 =
* Added Feature: Enable/Disable displaying of example check.
* Added Feature: Enable/Disable account numbers being fully displayed in admin emails. [Disabled by default]
* Fix: The example check is now displayed within the form fieldset during checkout.

= 1.1 =
* Added option to enter your own placeholder for the account and routing number field.
* Cleaned up some code.

= 1.0 =
* First release!


== Upgrade Notice ==
Fixed how the example check is displayed during checkout. Added option to enable/disable displaying entire account numbers in emails. Added option to enable/disable displaying the example check during checkout.
