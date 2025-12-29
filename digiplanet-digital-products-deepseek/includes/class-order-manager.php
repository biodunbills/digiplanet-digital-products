<?php
/**
 * Order management class
 */

if (!defined('ABSPATH')) {
    exit;
}

class Digiplanet_Order_Manager {
    
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
     * Create new order
     */
    public function create_order($customer_data, $cart_items, $payment_method) {
        global $wpdb;
        
        $order_number = $this->generate_order_number();
        $cart_manager = Digiplanet_Cart_Manager::get_instance();
        $product_manager = Digiplanet_Product_Manager::get_instance();
        
        // Calculate totals
        $subtotal = $cart_manager->get_cart_subtotal();
        $tax = $cart_manager->get_cart_tax();
        $total = $cart_manager->get_cart_total_with_tax();
        
        // Insert order
        $order_data = [
            'order_number' => $order_number,
            'customer_id' => $customer_data['user_id'],
            'customer_email' => $customer_data['email'],
            'customer_name' => $customer_data['name'],
            'total_amount' => $total,
            'tax_amount' => $tax,
            'discount_amount' => 0,
            'payment_method' => $payment_method,
            'payment_status' => 'pending',
            'ip_address' => Digiplanet_Security::get_client_ip(),
            'user_agent' => isset($_SERVER['HTTP_USER_AGENT']) ? sanitize_text_field($_SERVER['HTTP_USER_AGENT']) : '',
            'status' => 'pending',
            'created_at' => current_time('mysql')
        ];
        
        $wpdb->insert(
            $wpdb->prefix . 'digiplanet_orders',
            $order_data,
            ['%s', '%d', '%s', '%s', '%f', '%f', '%f', '%s', '%s', '%s', '%s', '%s', '%s']
        );
        
        $order_id = $wpdb->insert_id;
        
        if (!$order_id) {
            return false;
        }
        
        // Insert order items
        foreach ($cart_items as $item) {
            $product = $product_manager->get_product($item['product_id']);
            
            if (!$product) {
                continue;
            }
            
            $license_key = $this->generate_license_key();
            
            $order_item_data = [
                'order_id' => $order_id,
                'product_id' => $item['product_id'],
                'product_name' => $item['name'],
                'product_price' => $item['price'],
                'quantity' => $item['quantity'],
                'subtotal' => $item['price'] * $item['quantity'],
                'license_key' => $license_key,
                'download_count' => 0,
                'created_at' => current_time('mysql')
            ];
            
            $wpdb->insert(
                $wpdb->prefix . 'digiplanet_order_items',
                $order_item_data,
                ['%d', '%d', '%s', '%f', '%d', '%f', '%s', '%d', '%s']
            );
            
            $order_item_id = $wpdb->insert_id;
            
            // Create license record
            $this->create_license($license_key, $item['product_id'], $order_id, $customer_data['user_id']);
            
            // Increment product sales count
            $product_manager->increment_sales_count($item['product_id']);
        }
        
        // Send order confirmation email
        $this->send_order_confirmation_email($order_id, $customer_data['email']);
        
        return [
            'order_id' => $order_id,
            'order_number' => $order_number
        ];
    }
    
    /**
     * Generate order number
     */
    private function generate_order_number() {
        $prefix = 'ORD';
        $year = date('Y');
        $month = date('m');
        $random = strtoupper(wp_generate_password(6, false));
        
        return $prefix . $year . $month . $random;
    }
    
    /**
     * Generate license key
     */
    private function generate_license_key() {
        $parts = [];
        
        for ($i = 0; $i < 4; $i++) {
            $parts[] = strtoupper(wp_generate_password(4, false));
        }
        
        return implode('-', $parts);
    }
    
    /**
     * Create license record
     */
    private function create_license($license_key, $product_id, $order_id, $customer_id) {
        global $wpdb;
        
        $license_data = [
            'license_key' => $license_key,
            'product_id' => $product_id,
            'order_id' => $order_id,
            'customer_id' => $customer_id,
            'status' => 'active',
            'activation_count' => 0,
            'max_activations' => 1,
            'created_at' => current_time('mysql')
        ];
        
        $wpdb->insert(
            $wpdb->prefix . 'digiplanet_licenses',
            $license_data,
            ['%s', '%d', '%d', '%d', '%s', '%d', '%d', '%s']
        );
    }
    
    /**
     * Update order payment status
     */
    public function update_payment_status($order_id, $status, $transaction_id = null) {
        global $wpdb;
        
        $update_data = [
            'payment_status' => $status,
            'status' => $status === 'completed' ? 'completed' : 'pending',
            'updated_at' => current_time('mysql')
        ];
        
        if ($transaction_id) {
            $update_data['transaction_id'] = $transaction_id;
        }
        
        return $wpdb->update(
            $wpdb->prefix . 'digiplanet_orders',
            $update_data,
            ['id' => $order_id]
        );
    }
    
    /**
     * Get order by ID
     */
    public function get_order($order_id) {
        global $wpdb;
        
        $order = $wpdb->get_row($wpdb->prepare("
            SELECT * FROM {$wpdb->prefix}digiplanet_orders 
            WHERE id = %d
        ", $order_id));
        
        if ($order) {
            $order->items = $this->get_order_items($order_id);
        }
        
        return $order;
    }
    
    /**
     * Get order by number
     */
    public function get_order_by_number($order_number) {
        global $wpdb;
        
        $order = $wpdb->get_row($wpdb->prepare("
            SELECT * FROM {$wpdb->prefix}digiplanet_orders 
            WHERE order_number = %s
        ", $order_number));
        
        if ($order) {
            $order->items = $this->get_order_items($order->id);
        }
        
        return $order;
    }
    
    /**
     * Get order items
     */
    public function get_order_items($order_id) {
        global $wpdb;
        
        return $wpdb->get_results($wpdb->prepare("
            SELECT oi.*, p.download_link, p.download_limit, p.license_type 
            FROM {$wpdb->prefix}digiplanet_order_items oi
            LEFT JOIN {$wpdb->prefix}digiplanet_products p ON oi.product_id = p.id
            WHERE oi.order_id = %d
        ", $order_id));
    }
    
    /**
     * Get customer orders
     */
    public function get_customer_orders($customer_id, $limit = 10, $offset = 0) {
        global $wpdb;
        
        return $wpdb->get_results($wpdb->prepare("
            SELECT * FROM {$wpdb->prefix}digiplanet_orders 
            WHERE customer_id = %d 
            ORDER BY created_at DESC
            LIMIT %d OFFSET %d
        ", $customer_id, $limit, $offset));
    }
    
    /**
     * Send order confirmation email
     */
    private function send_order_confirmation_email($order_id, $email) {
        $order = $this->get_order($order_id);
        
        if (!$order) {
            return false;
        }
        
        $subject = sprintf(__('Order Confirmation #%s', 'digiplanet-digital-products'), $order->order_number);
        
        $headers = [
            'Content-Type: text/html; charset=UTF-8',
            'From: ' . get_bloginfo('name') . ' <' . get_bloginfo('admin_email') . '>'
        ];
        
        // Load email template
        ob_start();
        include DIGIPLANET_TEMPLATES_DIR . 'email/order-confirmation.php';
        $message = ob_get_clean();
        
        return wp_mail($email, $subject, $message, $headers);
    }
    
    /**
     * Get order status badge
     */
    public function get_order_status_badge($status) {
        $statuses = [
            'pending' => ['label' => __('Pending', 'digiplanet-digital-products'), 'class' => 'pending'],
            'processing' => ['label' => __('Processing', 'digiplanet-digital-products'), 'class' => 'processing'],
            'completed' => ['label' => __('Completed', 'digiplanet-digital-products'), 'class' => 'completed'],
            'cancelled' => ['label' => __('Cancelled', 'digiplanet-digital-products'), 'class' => 'cancelled']
        ];
        
        $status_info = $statuses[$status] ?? ['label' => $status, 'class' => 'default'];
        
        return sprintf(
            '<span class="digiplanet-order-status digiplanet-status-%s">%s</span>',
            esc_attr($status_info['class']),
            esc_html($status_info['label'])
        );
    }
    
    /**
     * Get payment status badge
     */
    public function get_payment_status_badge($status) {
        $statuses = [
            'pending' => ['label' => __('Pending', 'digiplanet-digital-products'), 'class' => 'pending'],
            'completed' => ['label' => __('Paid', 'digiplanet-digital-products'), 'class' => 'completed'],
            'failed' => ['label' => __('Failed', 'digiplanet-digital-products'), 'class' => 'failed'],
            'refunded' => ['label' => __('Refunded', 'digiplanet-digital-products'), 'class' => 'refunded']
        ];
        
        $status_info = $statuses[$status] ?? ['label' => $status, 'class' => 'default'];
        
        return sprintf(
            '<span class="digiplanet-payment-status digiplanet-status-%s">%s</span>',
            esc_attr($status_info['class']),
            esc_html($status_info['label'])
        );
    }
}