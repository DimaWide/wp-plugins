<?php
/*
 * Plugin Name: WooCommerce Masonix Gateway
 * Description: Integration with masonix payment service
 * Author: Kamm Store
 * Author URI: https://kamm.store/
 * Version: 1.0.0
 */



/*
* The class itself, please note that it is inside plugins_loaded action hook
*/

function masonix_init_gateway_class() {

    class WC_Masonix_Gateway extends WC_Payment_Gateway {

        /**
         * Class constructor (payment gateway options)
         */
        public function __construct() {

            $this->id = 'masonix-payment'; // payment gateway plugin ID
            $this->icon = ''; // URL of the icon that will be displayed on checkout page near your gateway name
            $this->has_fields = true; // in case you need a custom credit card form
            $this->method_title = 'Masonix Gateway';
            $this->method_description = 'Integration with masonix payment service'; // will be displayed on the options page

            // gateways can support subscriptions, refunds, saved payment methods,
            // but here begin with simple payments
            $this->supports = array(
                'products'
            );

            // Method with all the options fields
            $this->init_form_fields();

            // Load the settings.
            $this->init_settings();
            $this->title = $this->get_option('title');
            $this->description = $this->get_option('description');
            $this->enabled = $this->get_option('enabled');
            $this->testmode = 'yes' === $this->get_option('testmode');
            $this->publishable_key = $this->testmode ? $this->get_option('test_publishable_key') : $this->get_option('publishable_key');
            $this->masonix_curl = $this->testmode ? 'https://staging.banking.embily.com/api/' : 'https://banking.embily.com/api/';

            // This action hook saves the settings
            add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
        }

        /**
         * Plugin options, we deal with it in Step 3 too
         */
        public function init_form_fields() {

            $this->form_fields = array(
                'enabled' => array(
                    'title'       => 'Enable/Disable',
                    'label'       => 'Enable Masonix Gateway',
                    'type'        => 'checkbox',
                    'description' => '',
                    'default'     => 'yes'
                ),
                'title' => array(
                    'title'       => 'Title',
                    'type'        => 'text',
                    'description' => 'This controls the title which the user sees during checkout.',
                    'default'     => 'Credit Card',
                    'desc_tip'    => true,
                ),
                'description' => array(
                    'title'       => 'Description',
                    'type'        => 'textarea',
                    'description' => 'This controls the description which the user sees during checkout.',
                    'default'     => 'Pay with your credit card via our super-cool payment gateway.',
                ),
                'testmode' => array(
                    'title'       => 'Test mode',
                    'label'       => 'Enable Test Mode',
                    'type'        => 'checkbox',
                    'description' => 'Place the payment gateway in test mode using test API keys.',
                    'default'     => 'yes',
                    'desc_tip'    => true,
                ),
                'test_publishable_key' => array(
                    'title'       => 'Staging API Key',
                    'type'        => 'text'
                ),
                'publishable_key' => array(
                    'title'       => 'API Key',
                    'type'        => 'text'
                ),
            );
        }



        /**
         * You will need it if you want your custom credit card form, Step 4 is about it
         */
        public function payment_fields() {

            // ok, let's display some description before the payment form
            if ($this->description) {
                // you can instructions for test mode, I mean test card numbers etc.
                if ($this->testmode) {
                    $this->description .= ' TEST MODE ENABLED. In test mode, you can use the card numbers listed in <a href="#">documentation</a>.';
                    $this->description  = trim($this->description);
                }
                // display the description with <p> tags etc.
                echo wpautop(wp_kses_post($this->description));
            }
        }





        /*
        * We're processing the payments here, everything about it is in Step 5
        */

        public function process_payment($order_id) {

            $order = wc_get_order($order_id);

            $body = array(
                'swap' => array(
                    'debit_currency'               => 'SGD',
                    'credit_currency'              => 'SGDX',
                    'debit_amount'                 => number_format(($order->get_total() * 100) * 1.11, 0, '', ''),
                    'topup_debit_account_provider' => 'PSTRIPE',
                    'redirect_url'                 => wc_get_checkout_url(),
                ),
            );

            $url = $this->masonix_curl . 'v2/swaps/invoices';

            $response = wp_remote_post($url, array(
                'headers' => array(
                    'Content-Type'  => 'application/json',
                    'Authorization' => 'Token ' . $this->publishable_key,
                ),
                'body'        => json_encode($body),
                'method'      => 'POST',
                'data_format' => 'body',
            ));

            $response_body = wp_remote_retrieve_body($response);
            $data          = json_decode($response_body, true);

            // Check if 'uuid' is set and not empty
            if (isset($data['data']['invoice']['uuid']) && !empty($data['data']['invoice']['uuid'])) {
                $uuid             = $data['data']['invoice']['uuid'];
                $pay_redirect_url = isset($data['data']['invoice']['pay_redirect_url']) ? $data['data']['invoice']['pay_redirect_url'] : null;

                update_post_meta($order_id, '_invoice_uuid', $uuid);
                update_post_meta($order_id, '_invoice_order_complete_return_url',  $this->get_return_url($order));

                if (! empty($pay_redirect_url)) {
                    $cookie_name       = 'wcl_wc_current_order_id';
                    $cookie_value      = $order_id;
                    $cookie_expiration = time() + DAY_IN_SECONDS;

                    setcookie($cookie_name, $cookie_value, $cookie_expiration, COOKIEPATH, COOKIE_DOMAIN);

                    return array(
                        'result'   => 'success',
                        'redirect' => isset($pay_redirect_url) ? $pay_redirect_url : '',
                    );
                }
            } else {
                wc_add_notice('Invalid card details.', 'error');
                return array('result' => 'fail');
            }
        }
    }
}

add_action('plugins_loaded', 'masonix_init_gateway_class');



/* 
 custom_hide_payment_methods
 */
function custom_hide_payment_methods() {
?>
    <style>
        .wc_payment_methods {
            display: none;
        }
    </style>
<?php
}
add_action('wp_head', 'custom_hide_payment_methods');





/*
* This action hook registers our PHP class as a WooCommerce payment gateway
*/
add_filter('woocommerce_payment_gateways', 'masonix_add_gateway_class');
function masonix_add_gateway_class($gateways) {
    $gateways[] = 'WC_Masonix_Gateway';
    return $gateways;
}



/* 
custom_woocommerce_redirects
 */
function custom_woocommerce_redirects() {
    if (is_checkout()) {
        $wcl_wc_current_order_id = isset($_COOKIE['wcl_wc_current_order_id']) ? $_COOKIE['wcl_wc_current_order_id'] : '';

        if (!empty($wcl_wc_current_order_id)) {
            setcookie('wcl_wc_current_order_id', '', time() - 3600, COOKIEPATH, COOKIE_DOMAIN);
            handle_order_invoice($wcl_wc_current_order_id);
        }
    }
}
add_action('template_redirect', 'custom_woocommerce_redirects');




/* 
handle_order_invoice
 */
function handle_order_invoice($wcl_wc_current_order_id) {
    $invoice_uuid              = get_post_meta($wcl_wc_current_order_id, '_invoice_uuid', true);
    $order_complete_return_url = get_post_meta($wcl_wc_current_order_id, '_invoice_order_complete_return_url', true);
    $invoice                   = get_invoice_status((string)$invoice_uuid);

    $invoice_status = $invoice['data']['invoice']['status'];

    if ($invoice_status === 'SUCCESS') {
        $order = wc_get_order($wcl_wc_current_order_id);

        if ($order) {
            $order->update_status('completed');
        }

        wp_redirect($order_complete_return_url);
        exit;
    }
}




/* 
get_invoice_status
 */
function get_invoice_status($invoice_uuid) {
    $gateway     = new WC_Masonix_Gateway();
    $publishable_key = $gateway->publishable_key;
    $url         = $gateway->masonix_curl . 'v2/invoices/' . $invoice_uuid;

    $response = wp_remote_get($url, array(
        'headers' => array(
            'Content-Type' => 'application/json',
            'Authorization' => 'Token ' . $publishable_key,
        ),
    ));

    if (is_wp_error($response)) {
        return 'Ошибка: ' . $response->get_error_message();
    }

    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);
    return $data;
}
