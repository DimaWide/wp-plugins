<?php

namespace PostManagerOOP\Admin;

class AdminPage {
    public function init() {
        add_action('admin_menu', [$this, 'add_admin_menu']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_scripts']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_google_font_for_post_manager']);
    }
    

    public function add_admin_menu() {
        add_menu_page(
            __('Post Manager', 'post-manager-oop'),
            __('Post Manager', 'post-manager-oop'),
            'manage_options',
            'post-manager-oop',
            [$this, 'render_admin_page'],
            'dashicons-admin-post',
            6
        );
    }

    public function enqueue_admin_scripts($hook) {
        if ($hook != 'toplevel_page_post-manager-oop') {
            return;
        }

        wp_enqueue_script('jquery');
        wp_enqueue_script('wp-util'); 

        add_editor_style();

        wp_enqueue_style('main-style', plugin_dir_url(__FILE__) . '../../assets/css/main-style.min.css', array(), WCL_THEME_VERSION);
        wp_enqueue_script('bundle-js', plugin_dir_url(__FILE__) . '../../dist/bundle.js', array(), WCL_THEME_VERSION, true);

        wp_localize_script('bundle-js', 'postManagerApi', array(
            'api_url' => esc_url_raw(rest_url('post-manager-oop/v1/')),
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('wp_rest'),
            'nonce_02' => wp_create_nonce('aiml_generate_nonce')
        ));
    }


    public function render_admin_page() {
        include plugin_dir_path(__DIR__) . '../views/admin-page.php';
    }


    function enqueue_google_font_for_post_manager() {
        if (isset($_GET['page']) && $_GET['page'] === 'post-manager-oop') {
            wp_enqueue_style(
                'google-fonts',
                'https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap',
                array(),
                null
            );
        }
    }
}
