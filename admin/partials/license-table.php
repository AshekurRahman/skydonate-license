<?php
/**
 * Partial: License Table
 *
 * Usage:
 *   $licenses = (array) SkyLicense_Licenses::get_all_licenses();
 *   include 'partials/license-table.php';
 */

if ( ! defined( 'ABSPATH' ) ) exit;

if ( empty($licenses) ) {
    echo '<p>' . esc_html__('No licenses found.', 'skydonate-license') . '</p>';
    return;
}
?>

<table class="widefat striped">
    <thead>
        <tr>
            <th><?php esc_html_e('License Key', 'skydonate-license'); ?></th>
            <th><?php esc_html_e('Product', 'skydonate-license'); ?></th>
            <th><?php esc_html_e('Client', 'skydonate-license'); ?></th>
            <th><?php esc_html_e('Domains', 'skydonate-license'); ?></th>
            <th><?php esc_html_e('Status', 'skydonate-license'); ?></th>
            <th><?php esc_html_e('Expires', 'skydonate-license'); ?></th>
            <th><?php esc_html_e('Actions', 'skydonate-license'); ?></th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ( $licenses as $license ): ?>
            <?php
            $domains = json_decode($license['domains'], true);
            $domains_list = is_array($domains) ? implode(', ', $domains) : '-';
            ?>
            <tr>
                <td><code><?php echo esc_html($license['license_key']); ?></code></td>
                <td><?php echo esc_html($license['product_id']); ?></td>
                <td>
                    <?php echo esc_html($license['client_name']); ?><br />
                    <small><?php echo esc_html($license['client_email']); ?></small>
                </td>
                <td><?php echo esc_html($domains_list); ?></td>
                <td>
                    <?php
                    $status_class = 'inactive';
                    if ($license['status'] === 'active') $status_class = 'active';
                    elseif ($license['status'] === 'expired') $status_class = 'expired';
                    echo '<span class="status-' . esc_attr($status_class) . '">' . esc_html(ucfirst($license['status'])) . '</span>';
                    ?>
                </td>
                <td>
                    <?php
                    echo !empty($license['expiry_date'])
                        ? esc_html(date_i18n('Y-m-d', strtotime($license['expiry_date'])))
                        : esc_html__('N/A','skydonate-license');
                    ?>
                </td>
                <td>
                    <a href="<?php echo esc_url(admin_url('admin.php?page=edit-license&license=' . $license['license_key'])); ?>">
                        <?php esc_html_e('Edit', 'skydonate-license'); ?>
                    </a> |
                    <a href="<?php echo esc_url(admin_url('admin.php?page=delete-license&license=' . $license['license_key'])); ?>" onclick="return confirm('<?php esc_attr_e('Are you sure you want to delete this license?', 'skydonate-license'); ?>');">
                        <?php esc_html_e('Delete', 'skydonate-license'); ?>
                    </a>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<style>
.status-active { color: green; font-weight: bold; }
.status-expired { color: red; font-weight: bold; }
.status-inactive { color: gray; font-weight: bold; }
</style>
