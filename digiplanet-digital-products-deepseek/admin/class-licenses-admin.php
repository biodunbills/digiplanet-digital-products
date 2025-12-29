<?php
/**
 * Licenses admin management
 */

if (!defined('ABSPATH')) {
    exit;
}

class Digiplanet_Licenses_Admin {
    
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
     * Constructor
     */
    private function __construct() {
        add_action('admin_init', [$this, 'handle_license_actions']);
        add_action('admin_post_digiplanet_save_license', [$this, 'save_license']);
        add_action('admin_post_digiplanet_delete_license', [$this, 'delete_license']);
        add_action('admin_post_digiplanet_bulk_license_action', [$this, 'bulk_license_action']);
    }
    
    /**
     * Handle license actions
     */
    public function handle_license_actions() {
        if (isset($_GET['page']) && $_GET['page'] === 'digiplanet-licenses') {
            $action = $_GET['action'] ?? '';
            
            switch ($action) {
                case 'edit':
                    $this->render_edit_license();
                    exit;
                case 'create':
                    $this->render_create_license();
                    exit;
                case 'view':
                    $this->render_view_license();
                    exit;
            }
        }
    }
    
    /**
     * Render edit license page
     */
    private function render_edit_license() {
        if (!current_user_can('digiplanet_manage_licenses')) {
            wp_die(__('You do not have permission to access this page.', 'digiplanet-digital-products'));
        }
        
        $license_id = intval($_GET['license_id'] ?? 0);
        
        if (!$license_id) {
            wp_die(__('Invalid license ID.', 'digiplanet-digital-products'));
        }
        
        $license = $this->get_license($license_id);
        
        if (!$license) {
            wp_die(__('License not found.', 'digiplanet-digital-products'));
        }
        
        include DIGIPLANET_PLUGIN_DIR . 'admin/views/edit-license.php';
    }
    
    /**
     * Render create license page
     */
    private function render_create_license() {
        if (!current_user_can('digiplanet_manage_licenses')) {
            wp_die(__('You do not have permission to access this page.', 'digiplanet-digital-products'));
        }
        
        include DIGIPLANET_PLUGIN_DIR . 'admin/views/create-license.php';
    }
    
    /**
     * Render view license page
     */
    private function render_view_license() {
        if (!current_user_can('digiplanet_manage_licenses')) {
            wp_die(__('You do not have permission to access this page.', 'digiplanet-digital-products'));
        }
        
        $license_id = intval($_GET['license_id'] ?? 0);
        
        if (!$license_id) {
            wp_die(__('Invalid license ID.', 'digiplanet-digital-products'));
        }
        
        $license = $this->get_license($license_id);
        
        if (!$license) {
            wp_die(__('License not found.', 'digiplanet-digital-products'));
        }
        
        include DIGIPLANET_PLUGIN_DIR . 'admin/views/view-license.php';
    }
    
    /**
     * Get license
     */
    private function get_license($license_id) {
        global $wpdb;
        
        return $wpdb->get_row($wpdb->prepare("
            SELECT l.*, p.name as product_name, u.user_email, u.display_name 
            FROM {$wpdb->prefix}digiplanet_licenses l
            LEFT JOIN {$wpdb->prefix}digiplanet_products p ON l.product_id = p.id
            LEFT JOIN {$wpdb->prefix}users u ON l.customer_id = u.ID
            WHERE l.id = %d
        ", $license_id));
    }
    
    /**
     * Save license
     */
    public function save_license() {
        check_admin_referer('digiplanet_save_license');
        
        if (!current_user_can('digiplanet_manage_licenses')) {
            wp_die(__('Unauthorized.', 'digiplanet-digital-products'));
        }
        
        $license_id = intval($_POST['license_id'] ?? 0);
        $data = $this->sanitize_license_data($_POST);
        
        global $wpdb;
        
        if ($license_id) {
            // Update existing license
            $wpdb->update(
                $wpdb->prefix . 'digiplanet_licenses',
                $data,
                ['id' => $license_id],
                $this->get_license_data_formats($data),
                ['%d']
            );
            
            $message = __('License updated successfully.', 'digiplanet-digital-products');
        } else {
            // Create new license
            $data['created_at'] = current_time('mysql');
            
            $wpdb->insert(
                $wpdb->prefix . 'digiplanet_licenses',
                $data,
                $this->get_license_data_formats($data)
            );
            
            $license_id = $wpdb->insert_id;
            $message = __('License created successfully.', 'digiplanet-digital-products');
        }
        
        // Redirect with success message
        wp_redirect(add_query_arg([
            'page' => 'digiplanet-licenses',
            'action' => 'view',
            'license_id' => $license_id,
            'digiplanet_message' => urlencode($message),
            'digiplanet_message_type' => 'success'
        ], admin_url('admin.php')));
        exit;
    }
    
    /**
     * Delete license
     */
    public function delete_license() {
        check_admin_referer('digiplanet_delete_license');
        
        if (!current_user_can('digiplanet_manage_licenses')) {
            wp_die(__('Unauthorized.', 'digiplanet-digital-products'));
        }
        
        $license_id = intval($_GET['license_id'] ?? 0);
        
        if (!$license_id) {
            wp_die(__('Invalid license ID.', 'digiplanet-digital-products'));
        }
        
        global $wpdb;
        
        $wpdb->delete(
            $wpdb->prefix . 'digiplanet_licenses',
            ['id' => $license_id],
            ['%d']
        );
        
        // Redirect with success message
        wp_redirect(add_query_arg([
            'page' => 'digiplanet-licenses',
            'digiplanet_message' => urlencode(__('License deleted successfully.', 'digiplanet-digital-products')),
            'digiplanet_message_type' => 'success'
        ], admin_url('admin.php')));
        exit;
    }
    
    /**
     * Bulk license action
     */
    public function bulk_license_action() {
        check_admin_referer('digiplanet_bulk_license_action');
        
        if (!current_user_can('digiplanet_manage_licenses')) {
            wp_die(__('Unauthorized.', 'digiplanet-digital-products'));
        }
        
        $license_ids = $_POST['license_ids'] ?? [];
        $action = $_POST['bulk_action'] ?? '';
        
        if (empty($license_ids) || empty($action)) {
            wp_die(__('Invalid request.', 'digiplanet-digital-products'));
        }
        
        global $wpdb;
        
        switch ($action) {
            case 'activate':
                $wpdb->query($wpdb->prepare("
                    UPDATE {$wpdb->prefix}digiplanet_licenses 
                    SET status = 'active' 
                    WHERE id IN (" . implode(',', array_fill(0, count($license_ids), '%d')) . ")
                ", $license_ids));
                break;
                
            case 'deactivate':
                $wpdb->query($wpdb->prepare("
                    UPDATE {$wpdb->prefix}digiplanet_licenses 
                    SET status = 'inactive' 
                    WHERE id IN (" . implode(',', array_fill(0, count($license_ids), '%d')) . ")
                ", $license_ids));
                break;
                
            case 'revoke':
                $wpdb->query($wpdb->prepare("
                    UPDATE {$wpdb->prefix}digiplanet_licenses 
                    SET status = 'revoked' 
                    WHERE id IN (" . implode(',', array_fill(0, count($license_ids), '%d')) . ")
                ", $license_ids));
                break;
                
            case 'delete':
                $wpdb->query($wpdb->prepare("
                    DELETE FROM {$wpdb->prefix}digiplanet_licenses 
                    WHERE id IN (" . implode(',', array_fill(0, count($license_ids), '%d')) . ")
                ", $license_ids));
                break;
        }
        
        // Redirect with success message
        wp_redirect(add_query_arg([
            'page' => 'digiplanet-licenses',
            'digiplanet_message' => urlencode(__('Bulk action completed.', 'digiplanet-digital-products')),
            'digiplanet_message_type' => 'success'
        ], admin_url('admin.php')));
        exit;
    }
    
    /**
     * Sanitize license data
     */
    private function sanitize_license_data($data) {
        return [
            'license_key' => sanitize_text_field($data['license_key'] ?? ''),
            'product_id' => intval($data['product_id'] ?? 0),
            'order_id' => !empty($data['order_id']) ? intval($data['order_id']) : null,
            'customer_id' => !empty($data['customer_id']) ? intval($data['customer_id']) : null,
            'status' => sanitize_text_field($data['status'] ?? 'active'),
            'max_activations' => intval($data['max_activations'] ?? 1),
            'expires_at' => !empty($data['expires_at']) ? sanitize_text_field($data['expires_at']) : null,
        ];
    }
    
    /**
     * Get license data formats
     */
    private function get_license_data_formats($data) {
        $formats = [];
        
        foreach ($data as $key => $value) {
            if ($key === 'product_id' || $key === 'order_id' || $key === 'customer_id' || $key === 'max_activations') {
                $formats[] = '%d';
            } else {
                $formats[] = '%s';
            }
        }
        
        return $formats;
    }
    
    /**
     * Get licenses for listing
     */
    public function get_licenses($filters = [], $limit = 20, $offset = 0) {
        global $wpdb;
        
        $where = ['1=1'];
        $params = [];
        
        if (!empty($filters['status'])) {
            $where[] = 'l.status = %s';
            $params[] = $filters['status'];
        }
        
        if (!empty($filters['product_id'])) {
            $where[] = 'l.product_id = %d';
            $params[] = $filters['product_id'];
        }
        
        if (!empty($filters['customer_email'])) {
            $where[] = 'u.user_email LIKE %s';
            $params[] = '%' . $wpdb->esc_like($filters['customer_email']) . '%';
        }
        
        if (!empty($filters['license_key'])) {
            $where[] = 'l.license_key LIKE %s';
            $params[] = '%' . $wpdb->esc_like($filters['license_key']) . '%';
        }
        
        $where_clause = implode(' AND ', $where);
        
        $query = $wpdb->prepare("
            SELECT l.*, p.name as product_name, u.user_email, u.display_name 
            FROM {$wpdb->prefix}digiplanet_licenses l
            LEFT JOIN {$wpdb->prefix}digiplanet_products p ON l.product_id = p.id
            LEFT JOIN {$wpdb->prefix}users u ON l.customer_id = u.ID
            WHERE {$where_clause}
            ORDER BY l.created_at DESC
            LIMIT %d OFFSET %d
        ", array_merge($params, [$limit, $offset]));
        
        return $wpdb->get_results($query);
    }
    
    /**
     * Get licenses count
     */
    public function get_licenses_count($filters = []) {
        global $wpdb;
        
        $where = ['1=1'];
        $params = [];
        
        if (!empty($filters['status'])) {
            $where[] = 'status = %s';
            $params[] = $filters['status'];
        }
        
        if (!empty($filters['product_id'])) {
            $where[] = 'product_id = %d';
            $params[] = $filters['product_id'];
        }
        
        $where_clause = implode(' AND ', $where);
        
        $query = "SELECT COUNT(*) FROM {$wpdb->prefix}digiplanet_licenses WHERE {$where_clause}";
        
        if (!empty($params)) {
            $query = $wpdb->prepare($query, $params);
        }
        
        return $wpdb->get_var($query);
    }
}