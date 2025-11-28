<?php
/**
 * SkyDonate License Update Handler
 * URL: /wp-content/plugins/skydonate-license/api/update-handler.php
 */

if (!defined('ABSPATH')) {
    require_once(dirname(__DIR__, 2) . '/wp-load.php');
}

// Check for license_key
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

if (!$validation['valid']) {
    SkyLicense_Helpers::json_response([
        'success' => false,
        'message' => 'License not valid: ' . $validation['message']
    ], 403);
}

// Get update data
$updater = new SkyLicense_Updater();
$update_data = $updater->get_update_data();

// Log update request
SkyLicense_Helpers::log('api.log', sprintf(
    'License Key: %s | Domain: %s | Checked update | Version: %s',
    $license_key,
    $domain,
    $update_data['version']
));

// Return update info
SkyLicense_Helpers::json_response([
    'success' => true,
    'version' => $update_data['version'],
    'download' => $update_data['download'],
    'timestamp' => $update_data['timestamp'],
    'message' => 'Update info retrieved'
]);
