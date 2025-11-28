<?php
/**
 * API Info Data Provider
 *
 * Provides all public API endpoints & system info
 * for the SkyDonate License API Info admin page.
 *
 * @package SkyDonate\License
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class SkyLicense_API_Info {

    /**
     * Returns complete API info data for the UI.
     *
     * @return array
     */
    public static function get_info() {

        $base_url = site_url( '/' );

        $api_base = $base_url . 'wp-json/skydonate/v1/';

        return [
            'plugin_version' => SKY_LICENSE_VERSION,
            'server_time'    => current_time( 'mysql' ),

            'endpoints' => [
                'validate' => [
                    'name' => 'License Validation',
                    'url'  => $base_url . 'wp-json/skydonate/v1/validate',
                    'params' => [
                        'license_key',
                        'domain',
                        'product_slug',
                    ],
                    'method' => 'GET / POST',
                    'example' => $base_url . 'wp-json/skydonate/v1/validate?license_key=XXXX&domain=example.com',
                ],

                'check_update' => [
                    'name' => 'Check Plugin Update',
                    'url'  => $base_url . 'wp-json/skydonate/v1/update',
                    'params' => [
                        'license_key',
                        'product_slug',
                        'version',
                    ],
                    'method' => 'GET',
                    'example' => $base_url . 'wp-json/skydonate/v1/update?license_key=XXXX&product_slug=plugin&version=1.0.0',
                ],

                'download' => [
                    'name' => 'Download Plugin ZIP',
                    'url'  => $base_url . 'wp-json/skydonate/v1/download',
                    'params' => [
                        'license_key',
                        'product_slug',
                    ],
                    'method' => 'GET',
                    'example' => $base_url . 'wp-json/skydonate/v1/download?license_key=XXXX&product_slug=plugin',
                ],

                'legacy' => [
                    'name' => 'Legacy API (Backward Compatible)',
                    'url'  => $base_url . 'wp-json/skydonate/v1/legacy',
                    'params' => [
                        'license',
                        'domain',
                    ],
                    'method' => 'GET',
                    'example' => $base_url . 'wp-json/skydonate/v1/legacy?license=XXXX&domain=example.com',
                ],
            ],

            'system' => [
                'site_url'   => site_url(),
                'home_url'   => home_url(),
                'php_version'=> phpversion(),
                'wp_version' => get_bloginfo( 'version' ),
                'db_version' => get_option( 'skydonate_license_db_version', 'unknown' ),
            ],

            'files' => [
                'uploads_dir' => SKY_LICENSE_PATH . 'uploads',
                'logs_dir'    => SKY_LICENSE_PATH . 'logs',
                'zip_present' => self::check_zip_exists(),
            ],
        ];
    }

    /**
     * Checks if plugin ZIP exists in uploads folder.
     *
     * @return bool|string false or file path
     */
    private static function check_zip_exists() {

        $dir = SKY_LICENSE_PATH . 'uploads/';

        if ( ! is_dir( $dir ) ) {
            return false;
        }

        $files = glob( $dir . '*.zip' );

        if ( ! empty( $files ) ) {
            return basename( $files[0] );
        }

        return false;
    }
}
