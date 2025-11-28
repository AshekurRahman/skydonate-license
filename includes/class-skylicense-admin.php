<?php
/**
 * Admin loader for SkyDonate License plugin.
 *
 * @package SkyDonate\License
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class SkyLicense_Admin {

    /**
     * Constructor.
     */
    public function __construct() {
        $this->init_hooks();
    }

    /**
     * Initialize admin hooks.
     */
    private function init_hooks() {

        // Admin menu
        add_action( 'admin_menu', [ $this, 'register_admin_menu' ] );

        // AJAX actions
        add_action( 'wp_ajax_skydonate_add_license', [ $this, 'ajax_add_license' ] );
        add_action( 'wp_ajax_skydonate_delete_license', [ $this, 'ajax_delete_license' ] );
        add_action( 'wp_ajax_skydonate_generate_key', [ $this, 'ajax_generate_key' ] );
    }

    /**
     * Register top-level and sub-menu admin pages.
     */
    public function register_admin_menu() {

        // Top Level Menu
        add_menu_page(
            __( 'SkyDonate License', 'skydonate-license' ),
            __( 'SkyDonate License', 'skydonate-license' ),
            'manage_options',
            'skydonate-license',
            [ $this, 'load_page_license_manager' ],
            SKY_LICENSE_ASSETS . 'img/icon.svg',
            56
        );

        // Sub Page: Update Settings
        add_submenu_page(
            'skydonate-license',
            __( 'Update Settings', 'skydonate-license' ),
            __( 'Update Settings', 'skydonate-license' ),
            'manage_options',
            'skydonate-license-update-settings',
            [ $this, 'load_page_update_settings' ]
        );

        // Sub Page: API Info
        add_submenu_page(
            'skydonate-license',
            __( 'API Info', 'skydonate-license' ),
            __( 'API Info', 'skydonate-license' ),
            'manage_options',
            'skydonate-license-api-info',
            [ $this, 'load_page_api_info' ]
        );
    }

    /**
     * Load License Manager page.
     */
    public function load_page_license_manager() {
        $view = SKY_LICENSE_PATH . 'admin/views/page-license-manager.php';
        if ( file_exists( $view ) ) {
            include $view;
        } else {
            $this->missing_view_notice( 'page-license-manager.php' );
        }
    }

    /**
     * Load Update Settings page.
     */
    public function load_page_update_settings() {
        $view = SKY_LICENSE_PATH . 'admin/views/page-update-settings.php';
        if ( file_exists( $view ) ) {
            include $view;
        } else {
            $this->missing_view_notice( 'page-update-settings.php' );
        }
    }

    /**
     * Load API Info page.
     */
    public function load_page_api_info() {
        $view = SKY_LICENSE_PATH . 'admin/views/page-api-info.php';
        if ( file_exists( $view ) ) {
            include $view;
        } else {
            $this->missing_view_notice( 'page-api-info.php' );
        }
    }

    /**
     * Helper function for missing view templates.
     */
    private function missing_view_notice( $file ) {
        echo '<div class="notice notice-error"><p>';
        echo sprintf(
            esc_html__( 'Error: The admin view file "%s" was not found.', 'skydonate-license' ),
            esc_html( $file )
        );
        echo '</p></div>';
    }

    /* ======================================================
     * ===============   AJAX HANDLERS   =====================
     * ====================================================== */

    /**
     * AJAX: Add license.
     */
    public function ajax_add_license() {
        check_ajax_referer( 'skydonate_license_nonce', 'nonce' );

        $data = [
            'license_key'    => sanitize_text_field( $_POST['license_key'] ?? '' ),
            'product_slug'   => sanitize_text_field( $_POST['product_slug'] ?? '' ),
            'customer_name'  => sanitize_text_field( $_POST['customer_name'] ?? '' ),
            'customer_email' => sanitize_text_field( $_POST['customer_email'] ?? '' ),
            'allowed_domains'=> intval( $_POST['allowed_domains'] ?? 1 ),
            'expiry_date'    => sanitize_text_field( $_POST['expiry_date'] ?? '' ),
        ];

        if ( empty( $data['license_key'] ) ) {
            wp_send_json_error( [ 'message' => 'License key is required.' ] );
        }

        if ( class_exists( 'SkyLicense_Database' ) ) {
            $insert = SkyLicense_Database::insert_license( $data );
            wp_send_json_success( $insert );
        }

        wp_send_json_error( [ 'message' => 'Database class not found.' ] );
    }

    /**
     * AJAX: Delete license.
     */
    public function ajax_delete_license() {
        check_ajax_referer( 'skydonate_license_nonce', 'nonce' );

        $license_key = sanitize_text_field( $_POST['license_key'] ?? '' );

        if ( empty( $license_key ) ) {
            wp_send_json_error( [ 'message' => 'License key missing.' ] );
        }

        if ( class_exists( 'SkyLicense_Database' ) ) {
            $delete = SkyLicense_Database::delete_license( $license_key );
            wp_send_json_success( $delete );
        }

        wp_send_json_error( [ 'message' => 'Database class not found.' ] );
    }

    /**
     * AJAX: Generate license key.
     */
    public function ajax_generate_key() {
        check_ajax_referer( 'skydonate_license_nonce', 'nonce' );

        if ( class_exists( 'SkyLicense_Generator' ) ) {
            $key = SkyLicense_Generator::generate();
            wp_send_json_success( [ 'key' => $key ] );
        }

        wp_send_json_error( [ 'message' => 'Generator class missing.' ] );
    }
}
