<?php
namespace PostManagerOOP\Helpers;

class Helpers {
    public static function sanitize_input($input) {
        return sanitize_text_field($input);
    }
}