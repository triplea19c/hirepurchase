<?php

if (!defined('ABSPATH'))
	exit("No script kiddies");

$plugin = plugin_basename(__FILE__);
if (!class_exists('WC_HubtelSetup')) {
    require plugin_dir_path(__FILE__) . "../includes/class-hu-setup.php";
}
$setup = new WC_HubtelSetup();
$config = $setup->read_config();

if(isset($_POST["woocommerce_wc_hubtelpayment_save"]) && $_POST["woocommerce_wc_hubtelpayment_save"] == "Save Changes"){
    $nonce = isset($_POST["_wpnonce"]) ? $_POST["_wpnonce"] : "";
    if(!wp_verify_nonce($nonce) || !current_user_can("administrator")){
	    exit("unauthorized");
    }
    $title = isset($_POST["woocommerce_wc_hubtelpayment_title"]) ? wp_filter_nohtml_kses($_POST["woocommerce_wc_hubtelpayment_title"]) : "";
    $description = isset($_POST["woocommerce_wc_hubtelpayment_description"]) ? wp_filter_nohtml_kses($_POST["woocommerce_wc_hubtelpayment_description"]) : "";
    $clientid = isset($_POST["woocommerce_wc_hubtelpayment_clientid"]) ? $_POST["woocommerce_wc_hubtelpayment_clientid"] : "";
    $secret = isset($_POST["woocommerce_wc_hubtelpayment_secret"]) ? $_POST["woocommerce_wc_hubtelpayment_secret"] : "";
    $enabled = isset($_POST["woocommerce_wc_hubtelpayment_enabled"]) ? $_POST["woocommerce_wc_hubtelpayment_enabled"] : "0";
	$emails = isset($_POST["woocommerce_wc_hubtelpayment_emails"]) ? $_POST["woocommerce_wc_hubtelpayment_emails"] : "";
	$cconverter = isset($_POST["woocommerce_wc_hubtelpayment_cconverter"]) ? $_POST["woocommerce_wc_hubtelpayment_cconverter"] : "0";

	$emails_arr = explode(",", $emails);
	foreach ($emails_arr as $key => $em){
		$emails_arr[$key] = sanitize_email($em);
    }
	$emails = implode(",", $emails_arr);

    $config['title'] = $title;
    $config['description'] = $description;
    $config['clientid'] = $clientid;
    $config['secret'] = $secret;
    $config['enabled'] = $enabled;
	$config['emails'] = $emails;
	$config['cconverter'] = $cconverter;

    $setup->write_config($config);

}else{
    $title = wp_filter_nohtml_kses($config['title']);
    $description = wp_filter_nohtml_kses($config['description']);
    $clientid = $config['clientid'];
    $secret = $config['secret'];
    $enabled = $config['enabled'];
	$emails = $config['emails'];
	$cconverter = $config['cconverter'];
}

?>

<?php if (class_exists('WC_Payment_Gateway') && class_exists('WC_HubtelPayment')): ?>
<div class="wrap">
    <h3>Hubtel Payment Gateway</h3>
    <p>Hubtel Payment is most popular payment gateway for online shopping in Ghana.</p>
    <?php echo (isset($_POST["woocommerce_wc_hubtelpayment_save"]) && $_POST["woocommerce_wc_hubtelpayment_save"] == "Save Changes") ? '<div class="notice notice-success is-dismissible"><p>Settings saved successfully</p></div>' : ""; ?>
    <form method="post">
        <table class="form-table">
            <?php
            class WC_HubtelPaymentSettings extends WC_Payment_Gateway{
                public function __construct() {
                    $plugin = plugin_basename(__FILE__);
                    if (!class_exists('WC_HubtelSetup')) {
                        require plugin_dir_path(__FILE__) . "../includes/class-hu-setup.php";
                    }
                    $setup = new WC_HubtelSetup();
                    $config = $setup->read_config();


                    $this->id = $config["id"];
                    $this->method_title = __($config["title"], 'woocommerce' );
                    $this->icon = $config["icon"];
                    $this->has_fields = false;

                    $this->form_fields = array(
                        'enabled' => array(
                            'title' => __('Enable/Disable', $config["id"]),
                            'type' => 'checkbox',
                            'label' => __('Add to WooCommerce Checkout Page', $config["id"]),
                            'default' => "no"),
                        'title' => array(
                            'title' => __('Title', $config["id"]),
                            'type' => 'text',
                            'description' => __('This controls the title which the user sees during checkout.', $config["id"]),
                            'default' => __($config["title"], $config["id"])),
                        'description' => array(
                            'title' => __('Description', $config["id"]),
                            'type' => 'textarea',
                            'description' => __('This controls the description which the user sees during checkout.', $config["id"]),
                            'default' => __($config["description"], $config["id"])),
                        'clientid' => array(
                            'title' => __('Client Id', $config["id"]),
                            'type' => 'text',
                            'description' => __('', $config["id"]),
                            'default' => __($config["clientid"], $config["id"])),
                        'secret' => array(
                            'title' => __('Secret', $config["id"]),
                            'type' => 'text',
                            'description' => __('', $config["id"]),
                            'default' => __($config["secret"], $config["id"])),
                        'cconverter' => array(
	                        'title' => __('Currency Conversion<br><small style="font-weight: 400;">Since Hubtel Payment supports only GHS, enable this option if your store/site currency is <b>USD, GBP, EUR or NGN</b></small>', $config["id"]),
	                        'type' => 'checkbox',
	                        'label' => __('Automatically convert to GHS before sending to hubtel', $config["id"]),
	                        'default' => "no"),
                        'emails' => array(
	                        'title' => __('Notification Email Addresses', $config["id"]),
	                        'type' => 'textarea',
	                        'description' => __('Send a mail to the email addresses provided above whenever payment is received. <br>Separate each email address with a comma.', $config["id"]),
	                        'default' => __($config["emails"], $config["id"])),
                        'save' => array(
                            'title' => __('', $config["id"]),
                            'class' => 'button-primary',
                            'type' => 'submit',
                            'description' => __('', $config["id"]),
                            'default' => __('Save Changes', $config["id"])),
                    );
                    $this->init_settings();

	                $this->settings["enabled"] = ($config['enabled']) ? "yes" : "no";
                    $this->settings["title"] = $config['title'];
                    $this->settings["description"] = $config['description'];
                    $this->settings["clientid"] = $config['clientid'];
                    $this->settings["secret"] = $config['secret'];
	                $this->settings["emails"] = $config['emails'];
	                $this->settings["cconverter"] = ($config['cconverter']) ? "yes" : "no";
	                //var_dump($this->settings);

                    $this->generate_settings_html();
                }

                function process_admin_options(){
                    parent::process_admin_options();
                }
            }

            $obj = new WC_HubtelPaymentSettings();
            if(isset($_POST["woocommerce_wc_hubtelpayment_save"]) && $_POST["woocommerce_wc_hubtelpayment_save"] == "Save Changes"){
                $obj->process_admin_options();
            }
            ?>
        </table>
	    <?php wp_nonce_field() ?>
    </form>
</div>
<?php else: ?>
    <div class="wrap">
        <h3>Hubtel Payment Gateway</h3>
        <p>Hubtel Payment is most popular payment gateway for online shopping in Ghana.</p>
        <?php echo (isset($_POST["woocommerce_wc_hubtelpayment_save"]) && $_POST["woocommerce_wc_hubtelpayment_save"] == "Save Changes") ? '<div class="notice notice-success is-dismissible"><p>Settings saved successfully</p></div>' : ""; ?>
        <form method="post">
            <table class="form-table">
                <tbody>
                <tr>
                    <th scope="row">
                        <label for="woocommerce_wc_hubtelpayment_clientid">Client Id</label>
                    </th>
                    <td class="forminp">
                        <input class="input-text" type="text" name="woocommerce_wc_hubtelpayment_clientid" value="<?php echo $clientid ?>">
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="woocommerce_wc_hubtelpayment_secret">Secret</label>
                    </th>
                    <td class="forminp">
                        <input class="input-text" type="text" name="woocommerce_wc_hubtelpayment_secret" value="<?php echo $secret ?>">
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="woocommerce_wc_hubtelpayment_cconverter">Currency Conversion<br/><small style="font-weight: 400;">Since Hubtel Payment supports only GHS, enable this option if your store/site currency is <b>USD, GBP, EUR or NGN</b></small></label>
                    </th>
                    <td class="forminp">
                        <fieldset>
                            <input type="checkbox" name="woocommerce_wc_hubtelpayment_cconverter" value="1" <?php echo ($cconverter <> "no") ? "checked" : "" ?> />
                            <label for="woocommerce_wc_hubtelpayment_cconverter">Automatically convert to GHS before sending to hubtel.</label>
                        </fieldset>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="woocommerce_wc_hubtelpayment_description">Notification Email Addresses</label>
                    </th>
                    <td class="forminp">
                        <fieldset>
                            <textarea type="textarea" rows="5" name="woocommerce_wc_hubtelpayment_emails"><?php echo $emails ?></textarea>
                            <p class="description">Send a mail to the email addresses provided above whenever payment is received. <br>Separate each email address with a comma.</p>
                        </fieldset>
                    </td>
                </tr>
                </tbody>
            </table>
            <p class="submit">
                <button type="submit" name="woocommerce_wc_hubtelpayment_save" class="button-primary" value="Save Changes"> Saved Changes</button>
            </p>
	        <?php wp_nonce_field() ?>
        </form>

    </div>

<?php endif; ?>
