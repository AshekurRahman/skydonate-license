<?php
/**
 * SkyLicense Updates Manager
 * Handles plugin version info storage and retrieval
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class SkyLicense_Updates {

    /** @var string DB option key to store version info */
    private static $option_key = 'skydonate_plugin_versions';

    /**
     * Save or update plugin version info
     *
     * @param string $plugin_slug Plugin identifier
     * @param string $version Version number (ex: 1.0.3)
     * @param string $changelog Optional changelog
     */
    public static function save_version($plugin_slug, $version, $changelog = '') {
        $all_versions = get_option(self::$option_key, []);

        $all_versions[$plugin_slug] = [
            'version'   => $version,
            'changelog' => $changelog,
            'updated'   => current_time('mysql'),
        ];

        update_option(self::$option_key, $all_versions);
    }

    /**
     * Get version info for a plugin
     *
     * @param string $plugin_slug Plugin identifier
     * @return array|null
     */
    public static function get_version($plugin_slug) {
        $all_versions = get_option(self::$option_key, []);

        return $all_versions[$plugin_slug] ?? null;
    }

    /**
     * Get all plugin versions
     *
     * @return array
     */
    public static function get_all_versions() {
        return get_option(self::$option_key, []);
    }

    /**
     * Check if a version is newer than current
     *
     * @param string $plugin_slug
     * @param string $version_to_check
     * @return bool
     */
    public static function is_update_available($plugin_slug, $version_to_check) {
        $current = self::get_version($plugin_slug);

        if (!$current || !isset($current['version'])) return true;

        return version_compare($version_to_check, $current['version'], '>');
    }

    /**
     * Delete version info (if plugin removed)
     *
     * @param string $plugin_slug
     */
    public static function delete_version($plugin_slug) {
        $all_versions = get_option(self::$option_key, []);
        if (isset($all_versions[$plugin_slug])) {
            unset($all_versions[$plugin_slug]);
            update_option(self::$option_key, $all_versions);
        }
    }
}
