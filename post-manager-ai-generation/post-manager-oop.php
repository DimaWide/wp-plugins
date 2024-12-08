<?php
/**
 * Plugin Name: Post Manager OOP
 * Description: A plugin to manage posts with OOP structure.
 * Version: 1.0
 * Author: Your Name
 * Text Domain: post-manager-oop
 */

defined('ABSPATH') || exit;

define('WCL_THEME_VERSION', '0.101');

// Подключаем автозагрузчик
require_once plugin_dir_path(__FILE__) . 'includes/class-autoloader.php';
PostManagerOOP\Autoloader::register();

// Инициализируем плагин
add_action('plugins_loaded', function() {
    $plugin = new PostManagerOOP\Plugin();
    $plugin->init();
});