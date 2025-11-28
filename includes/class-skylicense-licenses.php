<?php
/**
 * License Business Logic
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class SkyLicense_Licenses {

    private $db;

    public function __construct() {
        $this->db = new SkyLicense_Database();
    }

    /**
     * Create a new license
     */
    public function create_license($data) {

        $defaults = [
            'license_key'   => '',
            'product_name'  => '',
            'client_name'   => '',
            'client_email'  => '',
            'max_domains'   => 1,
            'domains'       => [],
            'status'        => 'active',
            'expire_date'   => '',
            'notes'         => '',
        ];

        $data = wp_parse_args($data, $defaults);

        // Convert domain array to JSON
        if (is_array($data['domains'])) {
            $data['domains'] = json_encode($data['domains']);
        }

        return $this->db->insert_license($data);
    }

    /**
     * Update an existing license
     */
    public function update_license($id, $data) {

        if (isset($data['domains']) && is_array($data['domains'])) {
            $data['domains'] = json_encode($data['domains']);
        }

        return $this->db->update_license($id, $data);
    }

    /**
     * Delete a license
     */
    public function delete_license($id) {
        return $this->db->delete_license($id);
    }

    /**
     * Get license by ID
     */
    public function get_license($id) {
        return $this->db->get_license($id);
    }

    /**
     * Get license by license key
     */
    public function get_license_by_key($key) {
        return $this->db->get_license_by_key($key);
    }

    /**
     * Add domain activation (domain → license mapping)
     */
    public function add_domain_activation($license_key, $domain) {

        $license = $this->get_license_by_key($license_key);

        if (!$license) return false;

        $domains = json_decode($license->domains, true);
        if (!is_array($domains)) $domains = [];

        // If domain already exists → skip
        if (!in_array($domain, $domains)) {
            $domains[] = $domain;
        }

        // Check max limit
        if (count($domains) > intval($license->max_domains)) {
            return [
                'error' => true,
                'message' => 'Domain limit exceeded'
            ];
        }

        // Save domain list
        $this->update_license($license->id, [
            'domains' => $domains,
        ]);

        // Add activation log
        $this->db->add_activation_log($license_key, $domain);

        return true;
    }

    /**
     * Remove domain activation
     */
    public function remove_domain($license_key, $domain) {
        $license = $this->get_license_by_key($license_key);
        if (!$license) return false;

        $domains = json_decode($license->domains, true);
        if (!is_array($domains)) return false;

        $domains = array_filter($domains, function($d) use ($domain) {
            return trim($d) !== trim($domain);
        });

        return $this->update_license($license->id, [
            'domains' => $domains,
        ]);
    }

    /**
     * Change license status
     */
    public function set_status($license_key, $status) {
        $license = $this->get_license_by_key($license_key);
        if (!$license) return false;

        return $this->update_license($license->id, [
            'status' => $status,
        ]);
    }

    /**
     * Extend license expiration
     */
    public function extend_license($license_key, $days) {
        $license = $this->get_license_by_key($license_key);
        if (!$license) return false;

        $current_exp = strtotime($license->expire_date);
        $new_exp = date('Y-m-d', strtotime("+$days days", $current_exp));

        return $this->update_license($license->id, [
            'expire_date' => $new_exp
        ]);
    }

    /**
     * Get all licenses
     */
    public function get_all_licenses($args = []) {
        return $this->db->get_licenses($args);
    }
}
