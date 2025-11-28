<?php
/**
 * Activation handler for SkyDonate License plugin.
 *
 * @package SkyDonate\License
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class SkyLicense_Activator {

    /**
     * Run on plugin activation.
     */
    public static function activate() {
        self::create_tables();
        self::create_uploads_folder();
        self::create_logs_folder();
    }

    /**
     * Create required database tables.
     */
    private static function create_tables() {
        global $wpdb;

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        $charset_collate = $wpdb->get_charset_collate();
        $table_licenses  = $wpdb->prefix . 'skydonate_licenses';
        $table_logs      = $wpdb->prefix . 'skydonate_license_logs';
        $table_active    = $wpdb->prefix . 'skydonate_activations';

        /**
         * Licenses Table
         */
        $sql1 = "CREATE TABLE {$table_licenses} (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            license_key VARCHAR(100) NOT NULL,
            product_slug VARCHAR(100) NOT NULL,
            customer_name VARCHAR(150) DEFAULT NULL,
            customer_email VARCHAR(150) DEFAULT NULL,
            allowed_domains INT DEFAULT 1,
            expiry_date DATE DEFAULT NULL,
            status VARCHAR(20) DEFAULT 'active',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            UNIQUE KEY license_key (license_key)
        ) {$charset_collate};";

        /**
         * Activation Table
         */
        $sql2 = "CREATE TABLE {$table_active} (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            license_key VARCHAR(100) NOT NULL,
            domain VARCHAR(255) NOT NULL,
            ip_address VARCHAR(100) DEFAULT NULL,
            activated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            INDEX license_key (license_key)
        ) {$charset_collate};";

        /**
         * Logs Table
         */
        $sql3 = "CREATE TABLE {$table_logs} (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            license_key VARCHAR(100),
            event_type VARCHAR(50),
            message TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            INDEX license_key (license_key)
        ) {$charset_collate};";

        dbDelta( $sql1 );
        dbDelta( $sql2 );
        dbDelta( $sql3 );
    }

    /**
     * Ensure /uploads folder exists.
     */
    private static function create_uploads_folder() {
        $upload_dir = SKY_LICENSE_PATH . 'uploads';

        if ( ! file_exists( $upload_dir ) ) {
            wp_mkdir_p( $upload_dir );
        }
    }

    /**
     * Ensure /logs folder exists.
     */
    private static function create_logs_folder() {
        $log_dir = SKY_LICENSE_PATH . 'logs';

        if ( ! file_exists( $log_dir ) ) {
            wp_mkdir_p( $log_dir );
        }

        // Create empty log files if missing
        $logs = [
            'activations.log',
            'api.log',
            'downloads.log',
        ];

        foreach ( $logs as $file ) {
            $path = $log_dir . '/' . $file;

            if ( ! file_exists( $path ) ) {
                file_put_contents( $path, '' ); // silent create
            }
        }
    }
}
