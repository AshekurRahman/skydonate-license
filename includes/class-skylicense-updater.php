<?php
/**
 * Handles plugin ZIP upload + update version storage
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class SkyLicense_Updater {

    private $upload_dir;
    private $file_path;

    public function __construct() {
        $this->upload_dir = WP_CONTENT_DIR . '/skydonate-license/uploads/';
        $this->file_path  = $this->upload_dir . 'skydonate-plugin.zip';
    }

    /**
     * Upload new plugin ZIP file
     */
    public function upload_zip($file) {

        // Validate
        if (!isset($file['tmp_name']) || empty($file['tmp_name'])) {
            return ['error' => true, 'message' => 'No file uploaded'];
        }

        // Check file type
        $check_zip = wp_check_filetype($file['name']);
        if ($check_zip['ext'] !== 'zip') {
            return ['error' => true, 'message' => 'Invalid file type. Only ZIP allowed.'];
        }

        // Create upload dir if not exists
        if (!file_exists($this->upload_dir)) {
            wp_mkdir_p($this->upload_dir);
        }

        // Move uploaded ZIP
        if (!move_uploaded_file($file['tmp_name'], $this->file_path)) {
            return ['error' => true, 'message' => 'Failed to upload ZIP file'];
        }

        // Auto save version inside DB
        $version = $this->extract_version_from_zip($this->file_path);

        if ($version) {
            update_option('skylicense_latest_version', $version);
        }

        return [
            'success' => true,
            'message' => 'Plugin ZIP successfully uploaded',
            'version' => $version
        ];
    }

    /**
     * Extract plugin version from ZIP (reads plugin main file)
     */
    private function extract_version_from_zip($zip_path) {

        if (!class_exists('ZipArchive')) return false;

        $zip = new ZipArchive;

        if ($zip->open($zip_path) === TRUE) {

            // Find plugin main file
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $file = $zip->getNameIndex($i);

                // Usually main plugin file ends with .php and has plugin header
                if (preg_match('/\.php$/', $file)) {

                    $content = $zip->getFromName($file);

                    // Extract version from header
                    if (preg_match('/Version:\s*(.*)/i', $content, $match)) {
                        $zip->close();
                        return trim($match[1]);
                    }
                }
            }

            $zip->close();
        }

        return false;
    }

    /**
     * Get current uploaded ZIP path
     */
    public function get_zip_path() {
        return file_exists($this->file_path) ? $this->file_path : false;
    }

    /**
     * Get latest version stored in database
     */
    public function get_latest_version() {
        return get_option('skylicense_latest_version', '1.0.0');
    }

    /**
     * Delete uploaded ZIP
     */
    public function delete_zip() {
        if (file_exists($this->file_path)) {
            unlink($this->file_path);
            return true;
        }
        return false;
    }

    /**
     * Prepare update response for API endpoint
     */
    public function get_update_data() {

        $zip_url = content_url('skydonate-license/uploads/skydonate-plugin.zip');
        $version = $this->get_latest_version();

        return [
            'version'   => $version,
            'download'  => $zip_url,
            'timestamp' => time(),
        ];
    }
}
