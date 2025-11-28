<?php
/**
 * Plugin Name: SkyDonate License
 * Plugin URI:  https://skyweb.com/
 * Description: License management and update server for SkyDonate plugins.
 * Version:     1.0.0
 * Author:      Skyweb
 * Author URI:  https://skyweb.com/
 * Text Domain: skydonate-license
 * Domain Path: /languages
 *
 * @package SkyDonate\License
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

/**
 * Plugin constants
 */
define( 'SKY_LICENSE_VERSION', '1.0.0' );
define( 'SKY_LICENSE_SLUG', 'skydonate-license' );
define( 'SKY_LICENSE_FILE', __FILE__ );
define( 'SKY_LICENSE_PATH', plugin_dir_path( SKY_LICENSE_FILE ) );
define( 'SKY_LICENSE_URL', plugin_dir_url( SKY_LICENSE_FILE ) );
define( 'SKY_LICENSE_INC', SKY_LICENSE_PATH . 'includes/' );
define( 'SKY_LICENSE_API', SKY_LICENSE_PATH . 'api/' );
define( 'SKY_LICENSE_ASSETS', SKY_LICENSE_URL . 'assets/' );

/**
 * Load textdomain for translations.
 */
function sky_license_load_textdomain() {
    load_plugin_textdomain( 'skydonate-license', false, dirname( plugin_basename( SKY_LICENSE_FILE ) ) . '/languages/' );
}
add_action( 'plugins_loaded', 'sky_license_load_textdomain' );

/**
 * Require includes safely.
 */
$required_includes = [
    'class-skylicense-activator.php',
    'class-skylicense-deactivator.php',
    'class-skylicense-database.php',
    'class-skylicense-admin.php',
    'class-skylicense-licenses.php',
    'class-skylicense-updates.php',
    'class-skylicense-api-info.php',
    'class-skylicense-generator.php',
    'class-skylicense-validator.php',
    'class-skylicense-updater.php',
    'helpers.php',
];

foreach ( $required_includes as $file ) {
    $path = SKY_LICENSE_INC . $file;
    if ( file_exists( $path ) ) {
        require_once $path;
    } else {
        /* phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- admin debug */
        error_log( sprintf( 'SkyDonate License: Missing include file: %s', $path ) );
    }
}

/**
 * Load API endpoints (if present)
 */
$api_files = [
    'api-endpoint.php',
    'update-handler.php',
    'download-handler.php',
    'legacy-endpoint.php',
];

foreach ( $api_files as $file ) {
    $path = SKY_LICENSE_API . $file;
    if ( file_exists( $path ) ) {
        require_once $path;
    } else {
        /* phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log */
        error_log( sprintf( 'SkyDonate License: Missing api file: %s', $path ) );
    }
}

/**
 * Activation callback wrapper
 */
function sky_license_activate() {
    if ( class_exists( 'SkyLicense_Activator' ) ) {
        SkyLicense_Activator::activate();
    } else {
        // Fallback: create minimal DB tables if activator missing
        if ( class_exists( 'SkyLicense_Database' ) ) {
            SkyLicense_Database::maybe_create_tables();
        }
    }
}
register_activation_hook( SKY_LICENSE_FILE, 'sky_license_activate' );

/**
 * Deactivation callback wrapper
 */
function sky_license_deactivate() {
    if ( class_exists( 'SkyLicense_Deactivator' ) ) {
        SkyLicense_Deactivator::deactivate();
    }
}
register_deactivation_hook( SKY_LICENSE_FILE, 'sky_license_deactivate' );

/**
 * Main bootstrap class.
 */
if ( ! class_exists( 'SkyDonate_License' ) ) {

    class SkyDonate_License {

        /**
         * Plugin instance (singleton)
         *
         * @var SkyDonate_License|null
         */
        private static $instance = null;

        /**
         * Get singleton instance.
         *
         * @return SkyDonate_License
         */
        public static function instance() {
            if ( null === self::$instance ) {
                self::$instance = new self();
            }
            return self::$instance;
        }

        /**
         * Constructor.
         */
        private function __construct() {
            $this->setup_hooks();
        }

        /**
         * Setup WP hooks.
         */
        private function setup_hooks() {
            add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_admin_assets' ] );
            add_action( 'admin_menu', [ $this, 'register_admin_pages' ] );
            add_action( 'rest_api_init', [ $this, 'maybe_register_rest_routes' ] );
            // Optionally, scheduled tasks or cron hooks can be added here.
        }

        /**
         * Enqueue admin assets only on plugin pages.
         *
         * @param string $hook Hook suffix for the current admin page.
         */
        public function enqueue_admin_assets( $hook ) {
            // Quick check: only load assets on plugin admin pages by slug.
            $allowed_hooks = [
                'toplevel_page_skydonate-license', // top-level menu slug
                // If pages use nested slugs, adjust accordingly.
            ];

            if ( in_array( $hook, $allowed_hooks, true ) ) {
                wp_enqueue_style( 'sky-license-admin', SKY_LICENSE_ASSETS . 'css/admin.css', [], SKY_LICENSE_VERSION );
                wp_enqueue_script( 'sky-license-admin', SKY_LICENSE_ASSETS . 'js/admin.js', [ 'jquery' ], SKY_LICENSE_VERSION, true );
                wp_localize_script(
                    'sky-license-admin',
                    'SkyLicense',
                    [
                        'ajax_url' => admin_url( 'admin-ajax.php' ),
                        'nonce'    => wp_create_nonce( 'skydonate_license_nonce' ),
                    ]
                );
            }
        }

        /**
         * Register admin menu and pages.
         */
        public function register_admin_pages() {
            // Top-level menu
            add_menu_page(
                __( 'SkyDonate License', 'skydonate-license' ),
                __( 'SkyDonate License', 'skydonate-license' ),
                'manage_options',
                'skydonate-license',
                [ $this, 'render_license_manager_page' ],
                SKY_LICENSE_ASSETS . 'img/icon.svg',
                56
            );

            // Sub pages
            add_submenu_page(
                'skydonate-license',
                __( 'Update Settings', 'skydonate-license' ),
                __( 'Update Settings', 'skydonate-license' ),
                'manage_options',
                'skydonate-license-update-settings',
                [ $this, 'render_update_settings_page' ]
            );

            add_submenu_page(
                'skydonate-license',
                __( 'API Info', 'skydonate-license' ),
                __( 'API Info', 'skydonate-license' ),
                'manage_options',
                'skydonate-license-api-info',
                [ $this, 'render_api_info_page' ]
            );
        }

        /**
         * Render license manager admin page.
         */
        public function render_license_manager_page() {
            $view = SKY_LICENSE_PATH . 'admin/views/page-license-manager.php';
            if ( file_exists( $view ) ) {
                include $view;
            } else {
                echo '<div class="wrap"><h1>' . esc_html__( 'SkyDonate License', 'skydonate-license' ) . '</h1>';
                echo '<p>' . esc_html__( 'License manager view not found. Check your plugin files.', 'skydonate-license' ) . '</p></div>';
            }
        }

        /**
         * Render update settings page.
         */
        public function render_update_settings_page() {
            $view = SKY_LICENSE_PATH . 'admin/views/page-update-settings.php';
            if ( file_exists( $view ) ) {
                include $view;
            } else {
                echo '<div class="wrap"><h1>' . esc_html__( 'Update Settings', 'skydonate-license' ) . '</h1>';
                echo '<p>' . esc_html__( 'Update settings view not found. Check your plugin files.', 'skydonate-license' ) . '</p></div>';
            }
        }

        /**
         * Render API info page.
         */
        public function render_api_info_page() {
            $view = SKY_LICENSE_PATH . 'admin/views/page-api-info.php';
            if ( file_exists( $view ) ) {
                include $view;
            } else {
                echo '<div class="wrap"><h1>' . esc_html__( 'API Info', 'skydonate-license' ) . '</h1>';
                echo '<p>' . esc_html__( 'API info view not found. Check your plugin files.', 'skydonate-license' ) . '</p></div>';
            }
        }

        /**
         * Register REST routes if api-endpoint is present and desired.
         */
        public function maybe_register_rest_routes() {
            // If the API file registered routes itself (e.g. api-endpoint.php uses rest_api_init),
            // this can be left empty. Keep this hook to allow future route registrations.
            if ( function_exists( 'skydonate_register_rest_routes' ) ) {
                skydonate_register_rest_routes();
            }
        }
    } // end class
}

/**
 * Initialize plugin.
 */
function skydonate_license_init() {
    SkyDonate_License::instance();
}
add_action( 'init', 'skydonate_license_init', 5 );
