<?php
/**
 * SkyDonate License Download Handler
 * URL: /wp-content/plugins/skydonate-license/api/download-handler.php
 */

if (!defined('ABSPATH')) {
    require_once(dirname(__DIR__, 2) . '/wp-load.php');
}

if (!isset($_GET['license_key'])) {
    SkyLicense_Helpers::json_response([
        'success' => false,
        'message' => 'License key missing'
    ], 400);
}

// Input
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

// Path to ZIP
$updater = new SkyLicense_Updater();
$zip_file = $updater->get_zip_path();

if (!$zip_file || !file_exists($zip_file)) {
    SkyLicense_Helpers::json_response([
        'success' => false,
        'message' => 'Plugin ZIP not found on server'
    ], 404);
}

// Log download
SkyLicense_Helpers::log('downloads.log', sprintf(
    'License Key: %s | Domain: %s | Downloaded',
    $license_key,
    $domain
));

// Serve ZIP file
header('Content-Description: File Transfer');
header('Content-Type: application/zip');
header('Content-Disposition: attachment; filename="' . basename($zip_file) . '"');
header('Content-Transfer-Encoding: binary');
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');
header('Content-Length: ' . filesize($zip_file));

readfile($zip_file);
exit;
