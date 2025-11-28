<?php
/**
 * Admin Page: Update Settings
 */

if ( ! defined( 'ABSPATH' ) ) exit;

$updater = new SkyLicense_Updater();
$latest_version = $updater->get_latest_version();
$zip_file = $updater->get_zip_path();
$zip_filename = $zip_file ? basename($zip_file) : __('No ZIP uploaded', 'skydonate-license');

// Handle ZIP upload
if ( isset($_POST['skydonate_upload_nonce']) && check_admin_referer('skydonate_upload_action', 'skydonate_upload_nonce') ) {

    if (!empty($_FILES['plugin_zip']['name'])) {
        $result = $updater->upload_zip($_FILES['plugin_zip']);
        if (!empty($result['success'])) {
            echo '<div class="notice notice-success is-dismissible"><p>' . esc_html($result['message']) . '</p></div>';
            $latest_version = $result['version'] ?? $latest_version;
            $zip_filename = basename($updater->get_zip_path());
        } else {
            echo '<div class="notice notice-error is-dismissible"><p>' . esc_html($result['message']) . '</p></div>';
        }
    }
}
?>

<div class="wrap">
    <h1><?php esc_html_e('SkyDonate License - Update Settings', 'skydonate-license'); ?></h1>

    <h2><?php esc_html_e('Latest Uploaded ZIP', 'skydonate-license'); ?></h2>
    <table class="widefat striped">
        <tbody>
            <tr>
                <th><?php esc_html_e('Filename', 'skydonate-license'); ?></th>
                <td><?php echo esc_html($zip_filename); ?></td>
            </tr>
            <tr>
                <th><?php esc_html_e('Version', 'skydonate-license'); ?></th>
                <td><?php echo esc_html($latest_version); ?></td>
            </tr>
        </tbody>
    </table>

    <h2><?php esc_html_e('Upload New Plugin ZIP', 'skydonate-license'); ?></h2>
    <form method="post" enctype="multipart/form-data">
        <?php wp_nonce_field('skydonate_upload_action', 'skydonate_upload_nonce'); ?>
        <input type="file" name="plugin_zip" accept=".zip" required />
        <?php submit_button(__('Upload ZIP', 'skydonate-license')); ?>
    </form>
</div>

<style>
.wrap table.widefat th { width: 200px; }
</style>
