<?php
namespace PostManagerOOP;

class Autoloader {
    public static function register() {
        spl_autoload_register([__CLASS__, 'autoload']);
    }

    private static function autoload($class) {
        if (strpos($class, 'PostManagerOOP\\') === 0) {
            $class_path = str_replace('PostManagerOOP\\', '', $class);
            $class_path = str_replace('\\', '/', $class_path);
            $file = plugin_dir_path(__DIR__) . 'includes/class-' . $class_path . '.php';

            if (!file_exists($file)) {
                $sub_dirs = ['Admin', 'Api'];
                foreach ($sub_dirs as $sub_dir) {
                    $file = plugin_dir_path(__DIR__) . 'includes/' . $sub_dir . '/' . basename($class_path) . '.php';
                    if (file_exists($file)) {
                        break;
                    }
                }
            }

            if (file_exists($file)) {
                require $file;
            } else {
                error_log("Autoload error: Unable to load class '$class'. File '$file' not found.");
            }
        }
    }
}