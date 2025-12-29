<?php
/**
 * Sales Tracker Module
 */

if (!defined('ABSPATH')) {
    exit;
}

class Digiplanet_Sales_Tracker {
    
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
        add_action('digiplanet_order_completed', [$this, 'track_order'], 10, 2);
        add_action('digiplanet_product_viewed', [$this, 'track_product_view'], 10, 2);
        add_action('digiplanet_cart_updated', [$this, 'track_cart_update'], 10, 2);
        add_action('wp_footer', [$this, 'add_tracking_pixel']);
        add_action('admin_init', [$this, 'register_sales_tracker_settings']);
        add_action('wp_ajax_digiplanet_track_event', [$this, 'ajax_track_event']);
        add_action('wp_ajax_nopriv_digiplanet_track_event', [$this, 'ajax_track_event']);
    }
    
    /**
     * Track order completion
     */
    public function track_order($order_id, $order_data) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'digiplanet_sales_tracking';
        
        // Track order
        $wpdb->insert($table_name, [
            'event_type' => 'purchase',
            'order_id' => $order_id,
            'product_id' => $order_data['product_id'] ?? 0,
            'customer_id' => $order_data['customer_id'] ?? 0,
            'amount' => $order_data['total_amount'] ?? 0,
            'currency' => get_option('digiplanet_currency', 'USD'),
            'ip_address' => $this->get_user_ip(),
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
            'referrer' => $_SERVER['HTTP_REFERER'] ?? '',
            'created_at' => current_time('mysql'),
        ]);
        
        // Update product sales count
        if (!empty($order_data['product_id'])) {
            $product_manager = Digiplanet_Product_Manager::get_instance();
            $product_manager->increment_sales_count($order_data['product_id']);
        }
        
        // Update customer lifetime value
        if (!empty($order_data['customer_id'])) {
            $this->update_customer_lifetime_value($order_data['customer_id'], $order_data['total_amount']);
        }
    }
    
    /**
     * Track product view
     */
    public function track_product_view($product_id, $user_id = 0) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'digiplanet_product_views';
        
        $wpdb->insert($table_name, [
            'product_id' => $product_id,
            'user_id' => $user_id,
            'ip_address' => $this->get_user_ip(),
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
            'viewed_at' => current_time('mysql'),
        ]);
        
        // Update product view count
        $product_manager = Digiplanet_Product_Manager::get_instance();
        $product_manager->increment_view_count($product_id);
    }
    
    /**
     * Track cart update
     */
    public function track_cart_update($cart_data, $action) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'digiplanet_cart_tracking';
        
        $wpdb->insert($table_name, [
            'action' => $action,
            'product_id' => $cart_data['product_id'] ?? 0,
            'customer_id' => $cart_data['customer_id'] ?? 0,
            'quantity' => $cart_data['quantity'] ?? 1,
            'cart_total' => $cart_data['cart_total'] ?? 0,
            'ip_address' => $this->get_user_ip(),
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
            'tracked_at' => current_time('mysql'),
        ]);
    }
    
    /**
     * Add tracking pixel
     */
    public function add_tracking_pixel() {
        if (is_singular('digiplanet_product')) {
            $product_id = get_the_ID();
            $nonce = wp_create_nonce('digiplanet_tracking_nonce');
            
            ?>
            <script>
            jQuery(document).ready(function($) {
                // Track product view
                $.ajax({
                    url: '<?php echo admin_url('admin-ajax.php'); ?>',
                    type: 'POST',
                    data: {
                        action: 'digiplanet_track_event',
                        nonce: '<?php echo esc_js($nonce); ?>',
                        event_type: 'product_view',
                        product_id: <?php echo esc_js($product_id); ?>,
                        user_id: <?php echo esc_js(get_current_user_id()); ?>
                    }
                });
                
                // Track add to cart
                $(document).on('click', '.digiplanet-add-to-cart', function() {
                    var productId = $(this).data('product-id');
                    
                    $.ajax({
                        url: '<?php echo admin_url('admin-ajax.php'); ?>',
                        type: 'POST',
                        data: {
                            action: 'digiplanet_track_event',
                            nonce: '<?php echo esc_js($nonce); ?>',
                            event_type: 'add_to_cart',
                            product_id: productId,
                            user_id: <?php echo esc_js(get_current_user_id()); ?>
                        }
                    });
                });
                
                // Track checkout initiation
                $(document).on('click', '.digiplanet-checkout-button', function() {
                    $.ajax({
                        url: '<?php echo admin_url('admin-ajax.php'); ?>',
                        type: 'POST',
                        data: {
                            action: 'digiplanet_track_event',
                            nonce: '<?php echo esc_js($nonce); ?>',
                            event_type: 'checkout_start',
                            user_id: <?php echo esc_js(get_current_user_id()); ?>
                        }
                    });
                });
            });
            </script>
            <?php
        }
    }
    
    /**
     * AJAX: Track event
     */
    public function ajax_track_event() {
        check_ajax_referer('digiplanet_tracking_nonce', 'nonce');
        
        $event_type = sanitize_text_field($_POST['event_type'] ?? '');
        $product_id = intval($_POST['product_id'] ?? 0);
        $user_id = intval($_POST['user_id'] ?? 0);
        
        switch ($event_type) {
            case 'product_view':
                $this->track_product_view($product_id, $user_id);
                break;
            case 'add_to_cart':
                $this->track_cart_update(['product_id' => $product_id], 'add');
                break;
            case 'checkout_start':
                $this->track_checkout_start($user_id);
                break;
        }
        
        wp_send_json_success(['message' => 'Event tracked']);
    }
    
    /**
     * Track checkout start
     */
    private function track_checkout_start($user_id) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'digiplanet_conversion_tracking';
        
        $wpdb->insert($table_name, [
            'event_type' => 'checkout_start',
            'user_id' => $user_id,
            'ip_address' => $this->get_user_ip(),
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
            'tracked_at' => current_time('mysql'),
        ]);
    }
    
    /**
     * Get conversion rate
     */
    public function get_conversion_rate($start_date, $end_date) {
        global $wpdb;
        
        $views_table = $wpdb->prefix . 'digiplanet_product_views';
        $sales_table = $wpdb->prefix . 'digiplanet_sales_tracking';
        
        // Get total product views
        $total_views = $wpdb->get_var($wpdb->prepare("
            SELECT COUNT(*) 
            FROM {$views_table}
            WHERE DATE(viewed_at) BETWEEN %s AND %s
        ", $start_date, $end_date));
        
        // Get total purchases
        $total_purchases = $wpdb->get_var($wpdb->prepare("
            SELECT COUNT(*) 
            FROM {$sales_table}
            WHERE event_type = 'purchase'
            AND DATE(created_at) BETWEEN %s AND %s
        ", $start_date, $end_date));
        
        if ($total_views > 0) {
            return ($total_purchases / $total_views) * 100;
        }
        
        return 0;
    }
    
    /**
     * Get average order value
     */
    public function get_average_order_value($start_date, $end_date) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'digiplanet_sales_tracking';
        
        $result = $wpdb->get_row($wpdb->prepare("
            SELECT 
                COUNT(*) as total_orders,
                SUM(amount) as total_revenue
            FROM {$table_name}
            WHERE event_type = 'purchase'
            AND DATE(created_at) BETWEEN %s AND %s
        ", $start_date, $end_date));
        
        if ($result && $result->total_orders > 0) {
            return $result->total_revenue / $result->total_orders;
        }
        
        return 0;
    }
    
    /**
     * Get customer lifetime value
     */
    public function get_customer_lifetime_value($customer_id) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'digiplanet_sales_tracking';
        
        $total_spent = $wpdb->get_var($wpdb->prepare("
            SELECT SUM(amount)
            FROM {$table_name}
            WHERE event_type = 'purchase'
            AND customer_id = %d
        ", $customer_id));
        
        return floatval($total_spent) ?: 0;
    }
    
    /**
     * Update customer lifetime value
     */
    private function update_customer_lifetime_value($customer_id, $amount) {
        $current_lifetime_value = get_user_meta($customer_id, 'digiplanet_lifetime_value', true);
        $new_lifetime_value = floatval($current_lifetime_value) + floatval($amount);
        
        update_user_meta($customer_id, 'digiplanet_lifetime_value', $new_lifetime_value);
    }
    
    /**
     * Get top converting products
     */
    public function get_top_converting_products($start_date, $end_date, $limit = 10) {
        global $wpdb;
        
        $views_table = $wpdb->prefix . 'digiplanet_product_views';
        $sales_table = $wpdb->prefix . 'digiplanet_sales_tracking';
        $products_table = $wpdb->prefix . 'digiplanet_products';
        
        return $wpdb->get_results($wpdb->prepare("
            SELECT 
                p.id,
                p.name,
                COALESCE(v.view_count, 0) as views,
                COALESCE(s.purchase_count, 0) as purchases,
                CASE 
                    WHEN COALESCE(v.view_count, 0) > 0 
                    THEN (COALESCE(s.purchase_count, 0) / COALESCE(v.view_count, 0)) * 100
                    ELSE 0
                END as conversion_rate
            FROM {$products_table} p
            LEFT JOIN (
                SELECT product_id, COUNT(*) as view_count
                FROM {$views_table}
                WHERE DATE(viewed_at) BETWEEN %s AND %s
                GROUP BY product_id
            ) v ON p.id = v.product_id
            LEFT JOIN (
                SELECT product_id, COUNT(*) as purchase_count
                FROM {$sales_table}
                WHERE event_type = 'purchase'
                AND DATE(created_at) BETWEEN %s AND %s
                GROUP BY product_id
            ) s ON p.id = s.product_id
            WHERE COALESCE(v.view_count, 0) > 0
            ORDER BY conversion_rate DESC
            LIMIT %d
        ", $start_date, $end_date, $start_date, $end_date, $limit));
    }
    
    /**
     * Get sales funnel data
     */
    public function get_sales_funnel_data($start_date, $end_date) {
        global $wpdb;
        
        $views_table = $wpdb->prefix . 'digiplanet_product_views';
        $cart_table = $wpdb->prefix . 'digiplanet_cart_tracking';
        $checkout_table = $wpdb->prefix . 'digiplanet_conversion_tracking';
        $sales_table = $wpdb->prefix . 'digiplanet_sales_tracking';
        
        $funnel = [
            'views' => 0,
            'add_to_cart' => 0,
            'checkout_start' => 0,
            'purchases' => 0,
        ];
        
        // Product views
        $funnel['views'] = $wpdb->get_var($wpdb->prepare("
            SELECT COUNT(DISTINCT CONCAT(product_id, '-', ip_address))
            FROM {$views_table}
            WHERE DATE(viewed_at) BETWEEN %s AND %s
        ", $start_date, $end_date));
        
        // Add to cart
        $funnel['add_to_cart'] = $wpdb->get_var($wpdb->prepare("
            SELECT COUNT(DISTINCT CONCAT(product_id, '-', ip_address))
            FROM {$cart_table}
            WHERE action = 'add'
            AND DATE(tracked_at) BETWEEN %s AND %s
        ", $start_date, $end_date));
        
        // Checkout start
        $funnel['checkout_start'] = $wpdb->get_var($wpdb->prepare("
            SELECT COUNT(DISTINCT ip_address)
            FROM {$checkout_table}
            WHERE event_type = 'checkout_start'
            AND DATE(tracked_at) BETWEEN %s AND %s
        ", $start_date, $end_date));
        
        // Purchases
        $funnel['purchases'] = $wpdb->get_var($wpdb->prepare("
            SELECT COUNT(DISTINCT ip_address)
            FROM {$sales_table}
            WHERE event_type = 'purchase'
            AND DATE(created_at) BETWEEN %s AND %s
        ", $start_date, $end_date));
        
        return $funnel;
    }
    
    /**
     * Get user IP address
     */
    private function get_user_ip() {
        $ip = '';
        
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } elseif (!empty($_SERVER['REMOTE_ADDR'])) {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        
        return sanitize_text_field($ip);
    }
    
    /**
     * Register sales tracker settings
     */
    public function register_sales_tracker_settings() {
        register_setting('digiplanet_analytics_settings', 'digiplanet_enable_tracking');
        register_setting('digiplanet_analytics_settings', 'digiplanet_tracking_cookies');
        register_setting('digiplanet_analytics_settings', 'digiplanet_anonymize_ip');
        register_setting('digiplanet_analytics_settings', 'digiplanet_tracking_retention_days');
    }
    
    /**
     * Clean up old tracking data
     */
    public function cleanup_old_tracking_data() {
        global $wpdb;
        
        $retention_days = get_option('digiplanet_tracking_retention_days', 365);
        $cutoff_date = date('Y-m-d H:i:s', strtotime("-{$retention_days} days"));
        
        $tables = [
            $wpdb->prefix . 'digiplanet_sales_tracking',
            $wpdb->prefix . 'digiplanet_product_views',
            $wpdb->prefix . 'digiplanet_cart_tracking',
            $wpdb->prefix . 'digiplanet_conversion_tracking',
        ];
        
        foreach ($tables as $table) {
            $wpdb->query($wpdb->prepare("
                DELETE FROM {$table}
                WHERE created_at < %s
            ", $cutoff_date));
        }
    }
}