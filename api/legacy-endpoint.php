<?php
/**
 * SkyDonate License Legacy Endpoint
 * Maintains backward compatibility for older plugin versions
 */

if (!defined('ABSPATH')) {
    require_once(dirname(__DIR__, 2) . '/wp-load.php');
}

// Old plugins may send license_key or key, and domain as site
$license_key = '';
if (isset($_GET['license_key'])) {
    $license_key = SkyLicense_Helpers::sanitize_license_key($_GET['license_key']);
} elseif (isset($_GET['key'])) {
    $license_key = SkyLicense_Helpers::sanitize_license_key($_GET['key']);
}

$domain = '';
if (isset($_GET['domain'])) {
    $domain = SkyLicense_Helpers::extract_domain($_GET['domain']);
} elseif (isset($_GET['site'])) {
    $domain = SkyLicense_Helpers::extract_domain($_GET['site']);
}

if (empty($license_key)) {
    SkyLicense_Helpers::json_response([
        'success' => false,
        'message' => 'License key missing'
    ], 400);
}

// Validate license
$validation = SkyLicense_Validator::validate($license_key, $domain);

// Map response to old structure
$response = [
    'success' => $validation['valid'],
    'status'  => $validation['status'],
    'message' => $validation['message'],
    'license' => $validation['license'] ? [
        'key'       => $validation['license']['license_key'],
        'product'   => $validation['license']['product_id'],
        'client'    => $validation['license']['client_name'],
        'email'     => $validation['license']['client_email'],
        'domains'   => json_decode($validation['license']['domains'], true),
        'expiry'    => $validation['license']['expiry_date'],
    ] : null,
    'timestamp' => current_time('mysql')
];

// Log legacy API request
SkyLicense_Helpers::log('api.log', sprintf(
    '[LEGACY] License Key: %s | Domain: %s | Status: %s',
    $license_key,
    $domain,
    $validation['status']
));

// Return JSON
SkyLicense_Helpers::json_response($response);
