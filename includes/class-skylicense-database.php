<?php
/**
 * SkyLicense - Database Manager
 * Handles license table creation and queries
 */

if ( ! defined('ABSPATH') ) exit;

class SkyLicense_Database {

    /** @var string */
    private static $table = 'sky_licenses';

    /**
     * Create DB Table
     */
    public static function create_table() {
        global $wpdb;
        $table_name = $wpdb->prefix . self::$table;

        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table_name (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            license_key VARCHAR(64) NOT NULL,
            product_id VARCHAR(50) NOT NULL,
            client_name VARCHAR(150) DEFAULT NULL,
            client_email VARCHAR(150) DEFAULT NULL,
            domain VARCHAR(255) DEFAULT NULL,
            status VARCHAR(20) DEFAULT 'active',
            expiry_date DATETIME DEFAULT NULL,
            activation_date DATETIME DEFAULT CURRENT_TIMESTAMP,
            last_checked DATETIME DEFAULT NULL,

            UNIQUE KEY license_key_unique (license_key),
            PRIMARY KEY (id)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }


    /**
     * Insert License
     */
    public static function insert_license($data) {
        global $wpdb;

        $table_name = $wpdb->prefix . self::$table;

        $wpdb->insert($table_name, [
            'license_key'   => sanitize_text_field($data['license_key']),
            'product_id'    => sanitize_text_field($data['product_id']),
            'client_name'   => sanitize_text_field($data['client_name'] ?? ''),
            'client_email'  => sanitize_email($data['client_email'] ?? ''),
            'domain'        => sanitize_text_field($data['domain'] ?? ''),
            'status'        => sanitize_text_field($data['status'] ?? 'active'),
            'expiry_date'   => $data['expiry_date'] ?? null,
        ]);

        return $wpdb->insert_id;
    }


    /**
     * Update License
     */
    public static function update_license($license_key, $updates) {
        global $wpdb;

        $table_name = $wpdb->prefix . self::$table;

        return $wpdb->update(
            $table_name,
            $updates,
            ['license_key' => sanitize_text_field($license_key)]
        );
    }


    /**
     * Delete License
     */
    public static function delete_license($license_key) {
        global $wpdb;

        $table_name = $wpdb->prefix . self::$table;

        return $wpdb->delete(
            $table_name,
            ['license_key' => sanitize_text_field($license_key)]
        );
    }


    /**
     * Get License By Key
     */
    public static function get_license_by_key($license_key) {
        global $wpdb;

        $table_name = $wpdb->prefix . self::$table;

        return $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM $table_name WHERE license_key = %s", sanitize_text_field($license_key)),
            ARRAY_A
        );
    }


    /**
     * Get License By Key + Domain (for validation)
     */
    public static function get_license_by_key_and_domain($license_key, $domain) {
        global $wpdb;

        $table_name = $wpdb->prefix . self::$table;

        return $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM $table_name WHERE license_key = %s AND domain = %s",
                sanitize_text_field($license_key),
                sanitize_text_field($domain)
            ),
            ARRAY_A
        );
    }


    /**
     * Get All Licenses
     */
    public static function get_all_licenses() {
        global $wpdb;

        $table_name = $wpdb->prefix . self::$table;

        return $wpdb->get_results("SELECT * FROM $table_name ORDER BY id DESC", ARRAY_A);
    }


    /**
     * Count Total Licenses
     */
    public static function count_licenses() {
        global $wpdb;

        $table_name = $wpdb->prefix . self::$table;

        return (int) $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
    }
}
