<?php
/**
 * SkyDonate License API Endpoint
 * URL: /wp-content/plugins/skydonate-license/api/api-endpoint.php
 */

if ( ! defined( 'ABSPATH' ) ) {
    require_once( dirname(__DIR__, 2) . '/wp-load.php' );
}

if (!isset($_GET['license_key'])) {
    SkyLicense_Helpers::json_response([
        'success' => false,
        'message' => 'License key missing'
    ], 400);
}

// Get input
$license_key = SkyLicense_Helpers::sanitize_license_key($_GET['license_key']);
$domain      = !empty($_GET['domain']) ? SkyLicense_Helpers::extract_domain($_GET['domain']) : '';

// Validate license
$validation = SkyLicense_Validator::validate($license_key, $domain);

// Prepare response
$response = [
    'success' => $validation['valid'],
    'status'  => $validation['status'],
    'message' => $validation['message'],
    'license' => $validation['license'] ? [
        'license_key' => $validation['license']['license_key'],
        'product_id'  => $validation['license']['product_id'],
        'client_name' => $validation['license']['client_name'],
        'client_email'=> $validation['license']['client_email'],
        'max_domains' => $validation['license']['max_domains'],
        'domains'     => json_decode($validation['license']['domains'], true),
        'expiry_date' => $validation['license']['expiry_date'],
    ] : null,
    'timestamp' => current_time('mysql')
];

// Log API request
SkyLicense_Helpers::log('api.log', sprintf(
    'License Key: %s | Domain: %s | Status: %s',
    $license_key,
    $domain,
    $validation['status']
));

// Return JSON response
SkyLicense_Helpers::json_response($response);
