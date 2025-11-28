<?php
/**
 * License Key Generator
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class SkyLicense_Generator {

    /**
     * Generate a secure license key
     *
     * @param int $segments    Number of segments
     * @param int $segment_len Characters per segment
     * @param string $prefix   Optional product prefix
     * @return string
     */
    public static function generate_key( $segments = 4, $segment_len = 5, $prefix = '' ) {

        $key_segments = [];

        for ($i = 0; $i < $segments; $i++) {
            $key_segments[] = self::random_string($segment_len);
        }

        $license_key = implode('-', $key_segments);

        // Add prefix (ex: SKY-XXXXX-XXXXX...)
        if (!empty($prefix)) {
            $license_key = strtoupper($prefix) . '-' . $license_key;
        }

        // Add checksum for extra security
        $checksum = self::checksum($license_key);

        return $license_key . '-' . $checksum;
    }

    /**
     * Create a cryptographically secure random string
     */
    private static function random_string($length = 5) {
        $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789'; // No confusing chars
        $char_len = strlen($chars);
        $output = '';

        for ($i = 0; $i < $length; $i++) {
            $output .= $chars[random_int(0, $char_len - 1)];
        }

        return $output;
    }

    /**
     * Generate a checksum based on the key
     */
    private static function checksum($string) {
        $hash = md5($string);
        return strtoupper(substr($hash, 0, 4)); // 4-char checksum
    }

    /**
     * Validate checksum
     */
    public static function verify_checksum($license_key) {
        $parts = explode('-', $license_key);
        if (count($parts) < 2) return false;

        $checksum = array_pop($parts);
        $key_without_checksum = implode('-', $parts);

        return self::checksum($key_without_checksum) === $checksum;
    }
}
