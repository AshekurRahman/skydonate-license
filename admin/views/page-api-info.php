<?php
/**
 * Admin Page: API Info
 */

if ( ! defined( 'ABSPATH' ) ) exit;

$info = SkyLicense_API_Info::get_info();
?>

<div class="wrap">
    <h1><?php esc_html_e( 'SkyDonate License - API Info', 'skydonate-license' ); ?></h1>

    <h2><?php esc_html_e('Plugin Version & Server', 'skydonate-license'); ?></h2>
    <table class="widefat striped">
        <tbody>
        <tr>
            <th><?php esc_html_e('Plugin Version', 'skydonate-license'); ?></th>
            <td><?php echo esc_html($info['plugin_version']); ?></td>
        </tr>
        <tr>
            <th><?php esc_html_e('Server Time', 'skydonate-license'); ?></th>
            <td><?php echo esc_html($info['server_time']); ?></td>
        </tr>
        </tbody>
    </table>

    <h2><?php esc_html_e('API Endpoints', 'skydonate-license'); ?></h2>
    <table class="widefat striped">
        <thead>
            <tr>
                <th><?php esc_html_e('Name', 'skydonate-license'); ?></th>
                <th><?php esc_html_e('URL', 'skydonate-license'); ?></th>
                <th><?php esc_html_e('Method', 'skydonate-license'); ?></th>
                <th><?php esc_html_e('Parameters', 'skydonate-license'); ?></th>
                <th><?php esc_html_e('Example', 'skydonate-license'); ?></th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($info['endpoints'] as $endpoint): ?>
            <tr>
                <td><?php echo esc_html($endpoint['name']); ?></td>
                <td><code><?php echo esc_url($endpoint['url']); ?></code></td>
                <td><?php echo esc_html($endpoint['method']); ?></td>
                <td><?php echo esc_html(implode(', ', $endpoint['params'])); ?></td>
                <td><code><?php echo esc_html($endpoint['example']); ?></code></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <h2><?php esc_html_e('System Info', 'skydonate-license'); ?></h2>
    <table class="widefat striped">
        <tbody>
        <?php foreach ($info['system'] as $key => $value): ?>
            <tr>
                <th><?php echo esc_html(ucwords(str_replace('_',' ',$key))); ?></th>
                <td><?php echo esc_html($value); ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <h2><?php esc_html_e('Uploads & Logs', 'skydonate-license'); ?></h2>
    <table class="widefat striped">
        <tbody>
        <tr>
            <th><?php esc_html_e('Uploads Directory', 'skydonate-license'); ?></th>
            <td><?php echo esc_html($info['files']['uploads_dir']); ?></td>
        </tr>
        <tr>
            <th><?php esc_html_e('Logs Directory', 'skydonate-license'); ?></th>
            <td><?php echo esc_html($info['files']['logs_dir']); ?></td>
        </tr>
        <tr>
            <th><?php esc_html_e('Plugin ZIP Present', 'skydonate-license'); ?></th>
            <td>
                <?php echo $info['files']['zip_present'] ? esc_html($info['files']['zip_present']) : esc_html__('No ZIP uploaded', 'skydonate-license'); ?>
            </td>
        </tr>
        </tbody>
    </table>
</div>
