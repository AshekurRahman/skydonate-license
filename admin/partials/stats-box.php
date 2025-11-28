<?php
/**
 * Partial: Stats Box
 *
 * Usage:
 *   include 'partials/stats-box.php';
 *   Ensure $licenses = SkyLicense_Licenses::get_all_licenses();
 */

if ( ! defined( 'ABSPATH' ) ) exit;

$total = count($licenses);
$active = 0;
$expired = 0;

foreach ($licenses as $license) {
    if ($license['status'] === 'active') {
        $active++;
    } elseif ($license['status'] === 'expired') {
        $expired++;
    }
}
?>

<div class="skydonate-stats-box" style="display:flex; gap:20px; margin-bottom:20px;">
    <div style="flex:1; padding:15px; background:#f1f1f1; border-radius:6px; text-align:center;">
        <h3><?php echo esc_html($total); ?></h3>
        <p><?php esc_html_e('Total Licenses', 'skydonate-license'); ?></p>
    </div>
    <div style="flex:1; padding:15px; background:#d4edda; border-radius:6px; text-align:center;">
        <h3><?php echo esc_html($active); ?></h3>
        <p><?php esc_html_e('Active Licenses', 'skydonate-license'); ?></p>
    </div>
    <div style="flex:1; padding:15px; background:#f8d7da; border-radius:6px; text-align:center;">
        <h3><?php echo esc_html($expired); ?></h3>
        <p><?php esc_html_e('Expired Licenses', 'skydonate-license'); ?></p>
    </div>
</div>

<style>
.skydonate-stats-box h3 { margin:0; font-size:24px; }
.skydonate-stats-box p { margin:5px 0 0; font-size:14px; }
</style>
