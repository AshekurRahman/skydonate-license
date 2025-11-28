<?php
/**
 * Helper functions for SkyDonate License plugin
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class SkyLicense_Helpers {

    /**
     * Sanitize license key
     */
    public static function sanitize_license_key($key) {
        $key = strtoupper(trim($key));
        $key = preg_replace('/[^A-Z0-9\-]/', '', $key);
        return $key;
    }

    /**
     * Log events to a file
     *
     * @param string $file_name 'activations.log', 'api.log', etc.
     * @param string $message
     */
    public static function log($file_name, $message) {
        $log_dir = SKY_LICENSE_PATH . 'logs/';
        if ( ! file_exists($log_dir) ) {
            wp_mkdir_p($log_dir);
        }

        $file = $log_dir . $file_name;
        $time = current_time('mysql');
        $entry = "[$time] $message\n";

        file_put_contents($file, $entry, FILE_APPEND | LOCK_EX);
    }

    /**
     * Format date for display
     */
    public static function format_date($date, $format = 'Y-m-d H:i:s') {
        if (empty($date)) return '-';
        return date_i18n($format, strtotime($date));
    }

    /**
     * Check if license is expired
     *
     * @param string $expiry_date
     * @return bool
     */
    public static function is_expired($expiry_date) {
        if (empty($expiry_date)) return false;
        return strtotime($expiry_date) < time();
    }

    /**
     * JSON response helper for API
     */
    public static function json_response($data, $status = 200) {
        if (!headers_sent()) {
            status_header($status);
            header('Content-Type: application/json; charset=utf-8');
        }
        echo wp_json_encode($data);
        exit;
    }

    /**
     * Return domain from URL
     */
    public static function extract_domain($url) {
        $host = parse_url($url, PHP_URL_HOST);
        return strtolower($host ?: $url);
    }

    /**
     * Generate random alphanumeric string
     */
    public static function random_string($length = 10) {
        $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        $str = '';
        for ($i = 0; $i < $length; $i++) {
            $str .= $chars[random_int(0, strlen($chars) - 1)];
        }
        return $str;
    }
}
