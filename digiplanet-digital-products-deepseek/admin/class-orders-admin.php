<?php
/**
 * Orders admin management
 */

if (!defined('ABSPATH')) {
    exit;
}

class Digiplanet_Orders_Admin {
    
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
        add_action('admin_init', [$this, 'handle_order_actions']);
        add_action('admin_post_digiplanet_update_order', [$this, 'update_order']);
        add_action('admin_post_digiplanet_delete_order', [$this, 'delete_order']);
    }
    
    /**
     * Handle order actions
     */
    public function handle_order_actions() {
        if (isset($_GET['page']) && $_GET['page'] === 'digiplanet-orders') {
            $action = $_GET['action'] ?? '';
            
            switch ($action) {
                case 'view':
                    $this->render_view_order();
                    exit;
                case 'edit':
                    $this->render_edit_order();
                    exit;
            }
        }
    }
    
    /**
     * Render view order page
     */
    private function render_view_order() {
        if (!current_user_can('digiplanet_manage_orders')) {
            wp_die(__('You do not have permission to access this page.', 'digiplanet-digital-products'));
        }
        
        $order_id = intval($_GET['order_id'] ?? 0);
        
        if (!$order_id) {
            wp_die(__('Invalid order ID.', 'digiplanet-digital-products'));
        }
        
        $order_manager = Digiplanet_Order_Manager::get_instance();
        $order = $order_manager->get_order($order_id);
        
        if (!$order) {
            wp_die(__('Order not found.', 'digiplanet-digital-products'));
        }
        
        include DIGIPLANET_PLUGIN_DIR . 'admin/views/view-order.php';
    }
    
    /**
     * Render edit order page
     */
    private function render_edit_order() {
        if (!current_user_can('digiplanet_manage_orders')) {
            wp_die(__('You do not have permission to access this page.', 'digiplanet-digital-products'));
        }
        
        $order_id = intval($_GET['order_id'] ?? 0);
        
        if (!$order_id) {
            wp_die(__('Invalid order ID.', 'digiplanet-digital-products'));
        }
        
        $order_manager = Digiplanet_Order_Manager::get_instance();
        $order = $order_manager->get_order($order_id);
        
        if (!$order) {
            wp_die(__('Order not found.', 'digiplanet-digital-products'));
        }
        
        include DIGIPLANET_PLUGIN_DIR . 'admin/views/edit-order.php';
    }
    
    /**
     * Update order
     */
    public function update_order() {
        check_admin_referer('digiplanet_update_order');
        
        if (!current_user_can('digiplanet_manage_orders')) {
            wp_die(__('Unauthorized.', 'digiplanet-digital-products'));
        }
        
        $order_id = intval($_POST['order_id'] ?? 0);
        $status = sanitize_text_field($_POST['status'] ?? '');
        $payment_status = sanitize_text_field($_POST['payment_status'] ?? '');
        $notes = sanitize_textarea_field($_POST['notes'] ?? '');
        
        if (!$order_id) {
            wp_die(__('Invalid order ID.', 'digiplanet-digital-products'));
        }
        
        $order_manager = Digiplanet_Order_Manager::get_instance();
        
        // Update status
        if ($status) {
            $order_manager->update_order_status($order_id, $status);
        }
        
        // Update payment status
        if ($payment_status) {
            $order_manager->update_payment_status($order_id, $payment_status);
        }
        
        // Update notes
        if ($notes) {
            global $wpdb;
            
            $wpdb->update(
                $wpdb->prefix . 'digiplanet_orders',
                ['notes' => $notes],
                ['id' => $order_id],
                ['%s'],
                ['%d']
            );
        }
        
        // Redirect with success message
        wp_redirect(add_query_arg([
            'page' => 'digiplanet-orders',
            'action' => 'view',
            'order_id' => $order_id,
            'digiplanet_message' => urlencode(__('Order updated successfully.', 'digiplanet-digital-products')),
            'digiplanet_message_type' => 'success'
        ], admin_url('admin.php')));
        exit;
    }
    
    /**
     * Delete order
     */
    public function delete_order() {
        check_admin_referer('digiplanet_delete_order');
        
        if (!current_user_can('digiplanet_manage_orders')) {
            wp_die(__('Unauthorized.', 'digiplanet-digital-products'));
        }
        
        $order_id = intval($_GET['order_id'] ?? 0);
        
        if (!$order_id) {
            wp_die(__('Invalid order ID.', 'digiplanet-digital-products'));
        }
        
        global $wpdb;
        
        // Delete order items first
        $wpdb->delete(
            $wpdb->prefix . 'digiplanet_order_items',
            ['order_id' => $order_id],
            ['%d']
        );
        
        // Delete order
        $wpdb->delete(
            $wpdb->prefix . 'digiplanet_orders',
            ['id' => $order_id],
            ['%d']
        );
        
        // Redirect with success message
        wp_redirect(add_query_arg([
            'page' => 'digiplanet-orders',
            'digiplanet_message' => urlencode(__('Order deleted successfully.', 'digiplanet-digital-products')),
            'digiplanet_message_type' => 'success'
        ], admin_url('admin.php')));
        exit;
    }
    
    /**
     * Get orders for listing
     */
    public function get_orders($filters = [], $limit = 20, $offset = 0) {
        global $wpdb;
        
        $where = ['1=1'];
        $params = [];
        
        if (!empty($filters['status'])) {
            $where[] = 'status = %s';
            $params[] = $filters['status'];
        }
        
        if (!empty($filters['payment_status'])) {
            $where[] = 'payment_status = %s';
            $params[] = $filters['payment_status'];
        }
        
        if (!empty($filters['customer_email'])) {
            $where[] = 'customer_email LIKE %s';
            $params[] = '%' . $wpdb->esc_like($filters['customer_email']) . '%';
        }
        
        if (!empty($filters['order_number'])) {
            $where[] = 'order_number LIKE %s';
            $params[] = '%' . $wpdb->esc_like($filters['order_number']) . '%';
        }
        
        if (!empty($filters['date_from'])) {
            $where[] = 'created_at >= %s';
            $params[] = $filters['date_from'];
        }
        
        if (!empty($filters['date_to'])) {
            $where[] = 'created_at <= %s';
            $params[] = $filters['date_to'];
        }
        
        $where_clause = implode(' AND ', $where);
        
        $query = $wpdb->prepare("
            SELECT * FROM {$wpdb->prefix}digiplanet_orders 
            WHERE {$where_clause}
            ORDER BY created_at DESC
            LIMIT %d OFFSET %d
        ", array_merge($params, [$limit, $offset]));
        
        return $wpdb->get_results($query);
    }
    
    /**
     * Get orders count
     */
    public function get_orders_count($filters = []) {
        global $wpdb;
        
        $where = ['1=1'];
        $params = [];
        
        if (!empty($filters['status'])) {
            $where[] = 'status = %s';
            $params[] = $filters['status'];
        }
        
        if (!empty($filters['payment_status'])) {
            $where[] = 'payment_status = %s';
            $params[] = $filters['payment_status'];
        }
        
        $where_clause = implode(' AND ', $where);
        
        $query = "SELECT COUNT(*) FROM {$wpdb->prefix}digiplanet_orders WHERE {$where_clause}";
        
        if (!empty($params)) {
            $query = $wpdb->prepare($query, $params);
        }
        
        return $wpdb->get_var($query);
    }
}