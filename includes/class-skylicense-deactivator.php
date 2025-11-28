<?php
/**
 * Fired during plugin deactivation
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class SkyLicense_Deactivator {

    /**
     * Run deactivation tasks
     */
    public static function deactivate() {

        // Clear scheduled events (if any)
        $timestamp = wp_next_scheduled('skylicense_cron_check');
        if ($timestamp) {
            wp_unschedule_event($timestamp, 'skylicense_cron_check');
        }

        // Optional: Remove temporary options
        delete_option('skylicense_last_sync');
        delete_option('skylicense_temp_cache');

        // Plugin deactivated log (optional)
        error_log('SkyLicense Server plugin deactivated at ' . current_time('mysql'));
    }
}
