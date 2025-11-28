<?php
/**
 * License Validator
 *
 * Validates license key, domain, and expiration.
 */

if ( ! defined('ABSPATH') ) exit;

class SkyLicense_Validator {

    /**
     * Validate license
     *
     * @param string $license_key
     * @param string $domain
     * @return array ['valid'=>bool, 'status'=>string, 'message'=>string, 'license'=>array|null]
     */
    public static function validate($license_key, $domain = '') {

        // Check empty key
        if (empty($license_key)) {
            return [
                'valid'   => false,
                'status'  => 'invalid',
                'message' => 'License key is missing',
                'license' => null
            ];
        }

        // Check checksum (optional)
        if (!SkyLicense_Generator::verify_checksum($license_key)) {
            return [
                'valid'   => false,
                'status'  => 'invalid',
                'message' => 'License key checksum failed',
                'license' => null
            ];
        }

        // Get license from DB
        $license = SkyLicense_Database::get_license_by_key($license_key);

        if (!$license) {
            return [
                'valid'   => false,
                'status'  => 'invalid',
                'message' => 'License key not found',
                'license' => null
            ];
        }

        // Check status
        if ($license['status'] !== 'active') {
            return [
                'valid'   => false,
                'status'  => $license['status'],
                'message' => 'License is ' . $license['status'],
                'license' => $license
            ];
        }

        // Check expiry
        if (!empty($license['expiry_date']) && strtotime($license['expiry_date']) < time()) {
            return [
                'valid'   => false,
                'status'  => 'expired',
                'message' => 'License has expired',
                'license' => $license
            ];
        }

        // Check domain if provided
        if (!empty($domain)) {
            $domains = json_decode($license['domains'], true);
            if (!is_array($domains)) $domains = [];

            if (!in_array($domain, $domains)) {
                return [
                    'valid'   => false,
                    'status'  => 'domain_mismatch',
                    'message' => 'License not valid for this domain',
                    'license' => $license
                ];
            }
        }

        // All checks passed
        return [
            'valid'   => true,
            'status'  => 'active',
            'message' => 'License is valid',
            'license' => $license
        ];
    }
}
