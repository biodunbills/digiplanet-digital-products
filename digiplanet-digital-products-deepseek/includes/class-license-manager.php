<?php
/**
 * License management class
 */

if (!defined('ABSPATH')) {
    exit;
}

class Digiplanet_License_Manager {
    
    private static $instance = null;
    
    /**
     * Get singleton instance
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Generate new license key
     */
    public function generate_license_key($product_id, $type = 'single') {
        $prefix = 'DP';
        $product_code = str_pad($product_id, 4, '0', STR_PAD_LEFT);
        $random = strtoupper(wp_generate_password(12, false, false));
        
        $key = $prefix . $product_code . '-' . $random;
        
        // Format as XXXXX-XXXXX-XXXXX-XXXXX
        $key = implode('-', str_split($key, 5));
        
        // Ensure uniqueness
        while ($this->license_key_exists($key)) {
            $random = strtoupper(wp_generate_password(12, false, false));
            $key = $prefix . $product_code . '-' . $random;
            $key = implode('-', str_split($key, 5));
        }
        
        return $key;
    }
    
    /**
     * Check if license key exists
     */
    private function license_key_exists($license_key) {
        global $wpdb;
        
        $count = $wpdb->get_var($wpdb->prepare("
            SELECT COUNT(*) 
            FROM {$wpdb->prefix}digiplanet_licenses 
            WHERE license_key = %s
        ", $license_key));
        
        return $count > 0;
    }
    
    /**
     * Create license
     */
    public function create_license($product_id, $order_id, $customer_id, $type = 'single', $expires_at = null) {
        global $wpdb;
        
        $license_key = $this->generate_license_key($product_id, $type);
        
        $license_data = [
            'license_key' => $license_key,
            'product_id' => $product_id,
            'order_id' => $order_id,
            'customer_id' => $customer_id,
            'status' => 'active',
            'activation_count' => 0,
            'max_activations' => $this->get_max_activations($type),
            'expires_at' => $expires_at,
            'created_at' => current_time('mysql')
        ];
        
        $wpdb->insert(
            $wpdb->prefix . 'digiplanet_licenses',
            $license_data,
            ['%s', '%d', '%d', '%d', '%s', '%d', '%d', '%s', '%s']
        );
        
        return $license_key;
    }
    
    /**
     * Get max activations based on license type
     */
    private function get_max_activations($type) {
        $activations = [
            'single' => 1,
            'multi' => 5,
            'developer' => 999,
            'unlimited' => 0
        ];
        
        return $activations[$type] ?? 1;
    }
    
    /**
     * Validate license key
     */
    public function validate_license($license_key, $product_id = null) {
        global $wpdb;
        
        $query = "SELECT * FROM {$wpdb->prefix}digiplanet_licenses WHERE license_key = %s";
        $params = [$license_key];
        
        if ($product_id) {
            $query .= " AND product_id = %d";
            $params[] = $product_id;
        }
        
        $license = $wpdb->get_row($wpdb->prepare($query, $params));
        
        if (!$license) {
            return [
                'valid' => false,
                'message' => __('License key not found.', 'digiplanet-digital-products')
            ];
        }
        
        if ($license->status !== 'active') {
            return [
                'valid' => false,
                'message' => __('License is not active.', 'digiplanet-digital-products')
            ];
        }
        
        if ($license->expires_at && strtotime($license->expires_at) < time()) {
            return [
                'valid' => false,
                'message' => __('License has expired.', 'digiplanet-digital-products')
            ];
        }
        
        if ($license->max_activations > 0 && $license->activation_count >= $license->max_activations) {
            return [
                'valid' => false,
                'message' => __('Maximum activations reached.', 'digiplanet-digital-products')
            ];
        }
        
        return [
            'valid' => true,
            'license' => $license,
            'message' => __('License is valid.', 'digiplanet-digital-products')
        ];
    }
    
    /**
     * Activate license
     */
    public function activate_license($license_key, $activation_data = []) {
        $validation = $this->validate_license($license_key);
        
        if (!$validation['valid']) {
            return $validation;
        }
        
        $license = $validation['license'];
        
        global $wpdb;
        
        // Increment activation count
        $wpdb->update(
            $wpdb->prefix . 'digiplanet_licenses',
            [
                'activation_count' => $license->activation_count + 1,
                'last_activation' => current_time('mysql')
            ],
            ['id' => $license->id],
            ['%d', '%s'],
            ['%d']
        );
        
        // Log activation
        $this->log_activation($license->id, $activation_data);
        
        return [
            'valid' => true,
            'message' => __('License activated successfully.', 'digiplanet-digital-products'),
            'activations_remaining' => $license->max_activations - ($license->activation_count + 1)
        ];
    }
    
    /**
     * Deactivate license
     */
    public function deactivate_license($license_key, $activation_id = null) {
        global $wpdb;
        
        $license = $wpdb->get_row($wpdb->prepare("
            SELECT * FROM {$wpdb->prefix}digiplanet_licenses 
            WHERE license_key = %s
        ", $license_key));
        
        if (!$license) {
            return [
                'success' => false,
                'message' => __('License key not found.', 'digiplanet-digital-products')
            ];
        }
        
        if ($license->activation_count > 0) {
            $wpdb->update(
                $wpdb->prefix . 'digiplanet_licenses',
                [
                    'activation_count' => $license->activation_count - 1
                ],
                ['id' => $license->id],
                ['%d'],
                ['%d']
            );
        }
        
        // Remove activation log if provided
        if ($activation_id) {
            $wpdb->delete(
                $wpdb->prefix . 'digiplanet_license_activations',
                ['id' => $activation_id],
                ['%d']
            );
        }
        
        return [
            'success' => true,
            'message' => __('License deactivated successfully.', 'digiplanet-digital-products')
        ];
    }
    
    /**
     * Revoke license
     */
    public function revoke_license($license_id) {
        global $wpdb;
        
        $result = $wpdb->update(
            $wpdb->prefix . 'digiplanet_licenses',
            ['status' => 'revoked'],
            ['id' => $license_id],
            ['%s'],
            ['%d']
        );
        
        return $result !== false;
    }
    
    /**
     * Renew license
     */
    public function renew_license($license_id, $extension_days) {
        global $wpdb;
        
        $license = $wpdb->get_row($wpdb->prepare("
            SELECT * FROM {$wpdb->prefix}digiplanet_licenses 
            WHERE id = %d
        ", $license_id));
        
        if (!$license) {
            return false;
        }
        
        $new_expiry = date('Y-m-d H:i:s', strtotime("+{$extension_days} days"));
        
        $result = $wpdb->update(
            $wpdb->prefix . 'digiplanet_licenses',
            [
                'expires_at' => $new_expiry,
                'status' => 'active'
            ],
            ['id' => $license_id],
            ['%s', '%s'],
            ['%d']
        );
        
        return $result !== false;
    }
    
    /**
     * Log activation
     */
    private function log_activation($license_id, $data) {
        global $wpdb;
        
        $activation_data = [
            'license_id' => $license_id,
            'domain' => $data['domain'] ?? '',
            'ip_address' => Digiplanet_Security::get_client_ip(),
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
            'activated_at' => current_time('mysql')
        ];
        
        $wpdb->insert(
            $wpdb->prefix . 'digiplanet_license_activations',
            $activation_data,
            ['%d', '%s', '%s', '%s', '%s']
        );
        
        return $wpdb->insert_id;
    }
    
    /**
     * Get license activations
     */
    public function get_license_activations($license_id) {
        global $wpdb;
        
        return $wpdb->get_results($wpdb->prepare("
            SELECT * FROM {$wpdb->prefix}digiplanet_license_activations 
            WHERE license_id = %d 
            ORDER BY activated_at DESC
        ", $license_id));
    }
    
    /**
     * Check if license is valid for download
     */
    public function can_download_with_license($license_key, $product_id) {
        $validation = $this->validate_license($license_key, $product_id);
        
        if (!$validation['valid']) {
            return false;
        }
        
        return true;
    }
}