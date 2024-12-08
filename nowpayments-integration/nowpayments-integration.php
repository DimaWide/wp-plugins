<?php
/*
Plugin Name: NOWPayments Integration
Description: A WordPress plugin for integrating NOWPayments API.
Version: 1.0
Author: Your Name
*/

// Вставьте этот код в файл вашего плагина или в functions.php
function send_order_confirmation_email($payload) {
    // Prepare to send email
    $current_user = get_user_by('id', $payload['user_id']);
    $recipient_email = $current_user->user_email; // Получаем email текущего пользователя

    // Для тестирования используйте тестовый email
    if (empty($recipient_email)) {
        $recipient_email = 'testuser@example.com'; // Замените на тестовый email
    }

    // Установите тему письма
    $subject = 'Your Order Confirmation'; // Тема письма

    // Включите шаблон письма
    ob_start();
    include plugin_dir_path(__FILE__) . 'inc/custom-email-template.php';
    $email_content = ob_get_clean();

    // Set headers
    $headers = array(
        'From: Pump.Black <wordpress@webcomplete.io>',
        'Reply-To: wordpress@webcomplete.io',
        'Content-Type: text/html; charset=UTF-8'
    );

    // Send the email using wp_mail()
    if (wp_mail($recipient_email, $subject, $email_content, $headers)) {
    } else {
    }
}




/* 
np_create_orders_table
 */
register_activation_hook(__FILE__, 'np_create_orders_table');

function np_create_orders_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'np_orders';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        order_id varchar(50) NOT NULL,
        user_id bigint(20) NOT NULL,
        package varchar(20) NOT NULL,
        amount float NOT NULL,
        mint varchar(255) NOT NULL,
        status varchar(20) DEFAULT 'pending',
        admin_payment_emulate TINYINT(1) DEFAULT 0,  /* New field for admin payment emulation */
        created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
        PRIMARY KEY (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}


// np_add_admin_payment_emulate_column
function np_add_admin_payment_emulate_column() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'np_orders';

    // Проверяем, существует ли уже поле admin_payment_emulate
    $column_exists = $wpdb->get_results("SHOW COLUMNS FROM $table_name LIKE 'admin_payment_emulate'");

    // Если поле не существует, добавляем его
    if (empty($column_exists)) {
        $wpdb->query("ALTER TABLE $table_name ADD admin_payment_emulate TINYINT(1) DEFAULT 0");
    }
}

// Вызываем функцию для обновления таблицы при активации плагина
register_activation_hook(__FILE__, 'np_add_admin_payment_emulate_column');







// Define the NOWPayments API Key
define('NOWPAYMENTS_API_KEY', 'Test');
define('NOWPAYMENTS_IPN_SECRET_KEY', 'Test');







// Enqueue styles and scripts
add_action('wp_enqueue_scripts', 'np_enqueue_scripts');
function np_enqueue_scripts() {
    wp_enqueue_script('np-main', plugins_url('js/np-main.js', __FILE__), array('jquery'), '1.0', true);

    // Localize script to pass the AJAX URL
    wp_localize_script('np-main', 'ajax_object', array(
        'ajaxurl' => admin_url('admin-ajax.php') // Define ajaxurl
    ));
}





// Handle Payment Creation
add_action('wp_ajax_np_create_payment', 'np_create_payment');
add_action('wp_ajax_nopriv_np_create_payment', 'np_create_payment');






/* 
np_create_payment
 */
function np_create_payment() {
    global $wpdb;

    $package = sanitize_text_field($_POST['plan']);
    $mint    = sanitize_text_field($_POST['mint']);     // Получаем значение mint из POST-запроса

    $sol_amount = get_plan_price_by_id($package);
    $is_admin = current_user_can('administrator'); // Проверка, является ли текущий пользователь администратором

    if (empty($sol_amount)) {
        wp_send_json_error(array('error' => 'Invalid package selected'));
    }

    $order_id = uniqid();
    $user_id = get_current_user_id();

    // Проверяем, включена ли опция имитации платежа
    $simulate_payment = get_option('simulate_payment');

    // Если пользователь администратор и опция включена, имитируем успешную оплату
    if (current_user_can('administrator') && $simulate_payment) {
        // Если администратор, устанавливаем флаг admin_payment_emulate в 1
        $admin_payment_emulate = $is_admin ? 1 : 0;

        // Сохраняем заказ как оплаченный для администратора
        $wpdb->insert("{$wpdb->prefix}np_orders", array(
            'order_id' => $order_id,
            'user_id'  => $user_id,
            'package'  => $package,
            'amount'   => $sol_amount,
            'mint'     => $mint,
            'status'   => 'finished', // Статус как "завершено"
            'admin_payment_emulate'   => $admin_payment_emulate, // Сохраняем значение поля
        ));

        handle_order_token_and_featured_post($order_id, $mint, $package);

        handle_payment_status('finished', $order_id);

        $payload_to_email = array(
            'order_id'       => $order_id,
            'user_id'        => $user_id,
            'package'        => $package,
            'price_amount'   => $sol_amount,
            'price_currency' => 'sol',
            'status'         => 'Finished',
        );

        send_order_confirmation_email($payload_to_email);

        wp_send_json_success(array('payment_url' => site_url('/?order-for-admin=successful')));
    }

    // Обычная логика для всех остальных пользователей
    $payload = array(
        'price_amount'      => $sol_amount,
        'price_currency'    => 'sol',
        'order_id'          => $order_id,
        'order_description' => 'Payment for ' . ucfirst($package) . ' package',
        'ipn_callback_url'  => esc_url_raw(rest_url('np/v1/ipn_callback')),
        'success_url'       => site_url('/?np=completion'),
        'cancel_url'        => site_url('/')
    );

    $headers = array(
        'x-api-key'    => NOWPAYMENTS_API_KEY,
        'Content-Type' => 'application/json'
    );

    $response = wp_remote_post('https://api.nowpayments.io/v1/invoice', array(
        'headers' => $headers,
        'body'    => json_encode($payload)
    ));

    if (is_wp_error($response)) {
        wp_send_json_error(array('error' => 'Error creating payment'));
    }

    $response_body = json_decode(wp_remote_retrieve_body($response), true);

    if (isset($response_body['invoice_url'])) {
        // Сохраняем заказ для обычного пользователя
        $wpdb->insert("{$wpdb->prefix}np_orders", array(
            'order_id' => $order_id,
            'user_id'  => $user_id,
            'package'  => $package,
            'amount'   => $sol_amount,
            'mint'     => $mint
        ));


        handle_order_token_and_featured_post($order_id, $mint, $package);

        $payload_to_email = array(
            'order_id'       => $order_id,
            'user_id'        => $user_id,
            'package'        => $package,
            'price_amount'   => $sol_amount,
            'price_currency' => 'sol',
            'status'         => 'Pending',
        );

        // Update user meta to reflect payment pending status
        set_transient('np_payment_status_' . $user_id, 'pending', 20 * MINUTE_IN_SECONDS);

        send_order_confirmation_email($payload_to_email);

        wp_send_json_success(array('payment_url' => $response_body['invoice_url']));
    } else {
        wp_send_json_error(array('error' => $response_body['message']));
    }
}





// IPN Callback
add_action('rest_api_init', function () {
    register_rest_route('np/v1', '/ipn_callback', array(
        'methods' => 'POST',
        'callback' => 'np_ipn_callback',
        'permission_callback' => '__return_true' // Позволяет всем пользователям доступ к этому маршруту
    ));
});





function np_ipn_callback(WP_REST_Request $request) {
    $received_hmac = $request->get_header('x-nowpayments-sig');
    $request_body = $request->get_body();
    $calculated_hmac = hash_hmac('sha512', $request_body, NOWPAYMENTS_IPN_SECRET_KEY);

    if (hash_equals($received_hmac, $calculated_hmac)) {
        $payment_data = $request->get_json_params();
        $order_id = $payment_data['order_id'];
        $payment_status = $payment_data['payment_status'];

        error_log('Request Body: ' . $payment_data);

        global $wpdb;
        $wpdb->update("{$wpdb->prefix}np_orders", array('status' => $payment_status), array('order_id' => $order_id));

        $odrer_data = np_get_orders_by_order_id($order_id);

        if ($payment_status == 'finished' || $payment_status == 'failed' || $payment_status == 'canceled' || $payment_status == 'completed') {
            set_transient('np_payment_status_' . $odrer_data['user_id'], '', 20 * MINUTE_IN_SECONDS);
        }

        if ($payment_status === 'finished') {
            // Получаем ID поста по ID заказа, предположим, что оно хранится в мета-данных
            $post_id = $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = 'order_id' AND meta_value = %s",
                    $order_id
                )
            );

            // Если пост найден, обновляем его статус
            if ($post_id) {
                update_post_meta($post_id, 'featured_status', 'active'); // Обновляем статус на 'active'
                update_post_meta($post_id, 'featured_activation_date', current_time('mysql'));

                $post_update = array(
                    'ID'           => $post_id,
                    'post_status'  => 'publish',
                );
                wp_update_post($post_update);


                $payload_to_email = array(
                    'order_id'       => $order_id,
                    'user_id'        => $odrer_data['user_id'],
                    'package'        => $odrer_data['package'],
                    'price_amount'   => $odrer_data['amount'],
                    'price_currency' => 'sol',
                    'status'         => 'Finished',
                );

                send_order_confirmation_email($payload_to_email);
            }
        }

        return new WP_REST_Response(null, 200);
    } else {
        error_log('Invalid signature 1');
        return new WP_REST_Response('Invalid signature', 400);
    }
}







/* 
handle_payment_status
 */
function handle_payment_status($payment_status, $order_id) {
    global $wpdb;

    // Проверяем, равен ли статус "finished"
    if ($payment_status === 'finished') {
        // Получаем ID поста по ID заказа, предположим, что оно хранится в мета-данных
        $post_id = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = 'order_id' AND meta_value = %s",
                $order_id
            )
        );

        // Если пост найден, обновляем его статус
        if ($post_id) {
            // Обновляем статус на 'active'
            update_post_meta($post_id, 'featured_status', 'active');
            update_post_meta($post_id, 'featured_activation_date', current_time('mysql'));

            $post_update = array(
                'ID'           => $post_id,
                'post_status'  => 'publish',
            );
            wp_update_post($post_update);

            // Получаем данные заказа
            $order_data = np_get_orders_by_order_id($order_id);

            // Подготовка данных для электронной почты
            $payload_to_email = array(
                'order_id'       => $order_id,
                'user_id'        => $order_data['user_id'],
                'package'        => $order_data['package'],
                'price_amount'   => $order_data['amount'],
                'price_currency' => 'sol',
                'status'         => 'Finished',
            );

            // Отправляем подтверждение заказа по электронной почте
            send_order_confirmation_email($payload_to_email);
        }
    }
}
