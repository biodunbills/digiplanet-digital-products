<?php
/**
 * Admin main class
 */

if (!defined('ABSPATH')) {
    exit;
}

class Digiplanet_Admin {
    
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
        add_action('admin_menu', [$this, 'add_admin_menus']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_scripts']);
        add_action('admin_init', [$this, 'register_admin_hooks']);
        add_action('admin_notices', [$this, 'display_admin_notices']);
    }
    
    /**
     * Add admin menus
     */
    public function add_admin_menus() {
        // Main menu already added by Digiplanet_Settings
        // Add submenus
        
        // Products
        add_submenu_page(
            'digiplanet-dashboard',
            __('Products', 'digiplanet-digital-products'),
            __('Products', 'digiplanet-digital-products'),
            'digiplanet_manage_products',
            'digiplanet-products',
            [$this, 'render_products_page']
        );
        
        // Orders
        add_submenu_page(
            'digiplanet-dashboard',
            __('Orders', 'digiplanet-digital-products'),
            __('Orders', 'digiplanet-digital-products'),
            'digiplanet_manage_orders',
            'digiplanet-orders',
            [$this, 'render_orders_page']
        );
        
        // Licenses
        add_submenu_page(
            'digiplanet-dashboard',
            __('Licenses', 'digiplanet-digital-products'),
            __('Licenses', 'digiplanet-digital-products'),
            'digiplanet_manage_licenses',
            'digiplanet-licenses',
            [$this, 'render_licenses_page']
        );
        
        // Customers
        add_submenu_page(
            'digiplanet-dashboard',
            __('Customers', 'digiplanet-digital-products'),
            __('Customers', 'digiplanet-digital-products'),
            'digiplanet_manage_customers',
            'digiplanet-customers',
            [$this, 'render_customers_page']
        );
        
        // Reviews
        add_submenu_page(
            'digiplanet-dashboard',
            __('Reviews', 'digiplanet-digital-products'),
            __('Reviews', 'digiplanet-digital-products'),
            'digiplanet_manage_reviews',
            'digiplanet-reviews',
            [$this, 'render_reviews_page']
        );
        
        // Reports
        add_submenu_page(
            'digiplanet-dashboard',
            __('Reports', 'digiplanet-digital-products'),
            __('Reports', 'digiplanet-digital-products'),
            'digiplanet_view_reports',
            'digiplanet-reports',
            [$this, 'render_reports_page']
        );
    }
    
    /**
     * Enqueue admin scripts
     */
    public function enqueue_admin_scripts($hook) {
        if (strpos($hook, 'digiplanet') !== false) {
            wp_enqueue_style(
                'digiplanet-admin',
                DIGIPLANET_PLUGIN_URL . 'admin/css/admin-style.css',
                [],
                DIGIPLANET_VERSION
            );
            
            wp_enqueue_script(
                'digiplanet-admin',
                DIGIPLANET_PLUGIN_URL . 'admin/js/admin-script.js',
                ['jquery', 'jquery-ui-datepicker'],
                DIGIPLANET_VERSION,
                true
            );
            
            wp_localize_script('digiplanet-admin', 'digiplanet_admin', [
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('digiplanet_admin_nonce'),
                'confirm_delete' => __('Are you sure you want to delete this item?', 'digiplanet-digital-products'),
                'confirm_bulk_delete' => __('Are you sure you want to delete selected items?', 'digiplanet-digital-products'),
            ]);
            
            // Enqueue datepicker
            wp_enqueue_style('jquery-ui-datepicker');
            wp_enqueue_script('jquery-ui-datepicker');
            
            // Enqueue Chart.js for reports
            if (strpos($hook, 'digiplanet-reports') !== false) {
                wp_enqueue_script(
                    'chart-js',
                    'https://cdn.jsdelivr.net/npm/chart.js',
                    [],
                    '3.9.1'
                );
            }
        }
    }
    
    /**
     * Register admin hooks
     */
    public function register_admin_hooks() {
        // AJAX handlers
        add_action('wp_ajax_digiplanet_bulk_action', [$this, 'ajax_bulk_action']);
        add_action('wp_ajax_digiplanet_get_product_data', [$this, 'ajax_get_product_data']);
        add_action('wp_ajax_digiplanet_update_product', [$this, 'ajax_update_product']);
        add_action('wp_ajax_digiplanet_update_order_status', [$this, 'ajax_update_order_status']);
        add_action('wp_ajax_digiplanet_generate_license', [$this, 'ajax_generate_license']);
        add_action('wp_ajax_digiplanet_validate_license', [$this, 'ajax_validate_license']);
        add_action('wp_ajax_digiplanet_get_analytics', [$this, 'ajax_get_analytics']);
        add_action('wp_ajax_digiplanet_test_email', [$this, 'ajax_test_email']);
        add_action('wp_ajax_digiplanet_test_payment_gateway', [$this, 'ajax_test_payment_gateway']);
        
        // Export handlers
        add_action('wp_ajax_digiplanet_export_products', [$this, 'export_products']);
        add_action('wp_ajax_digiplanet_export_orders', [$this, 'export_orders']);
        add_action('wp_ajax_digiplanet_export_customers', [$this, 'export_customers']);
        
        // Import handlers
        add_action('wp_ajax_digiplanet_import_products', [$this, 'import_products']);
        add_action('wp_ajax_digiplanet_import_customers', [$this, 'import_customers']);
    }
    
    /**
     * Display admin notices
     */
    public function display_admin_notices() {
        if (isset($_GET['digiplanet_message'])) {
            $type = $_GET['digiplanet_message_type'] ?? 'success';
            $message = urldecode($_GET['digiplanet_message']);
            
            printf(
                '<div class="notice notice-%s is-dismissible"><p>%s</p></div>',
                esc_attr($type),
                esc_html($message)
            );
        }
    }
    
    /**
     * Render products page
     */
    public function render_products_page() {
        if (!current_user_can('digiplanet_manage_products')) {
            wp_die(__('You do not have permission to access this page.', 'digiplanet-digital-products'));
        }
        
        include DIGIPLANET_PLUGIN_DIR . 'admin/views/products.php';
    }
    
    /**
     * Render orders page
     */
    public function render_orders_page() {
        if (!current_user_can('digiplanet_manage_orders')) {
            wp_die(__('You do not have permission to access this page.', 'digiplanet-digital-products'));
        }
        
        include DIGIPLANET_PLUGIN_DIR . 'admin/views/orders.php';
    }
    
    /**
     * Render licenses page
     */
    public function render_licenses_page() {
        if (!current_user_can('digiplanet_manage_licenses')) {
            wp_die(__('You do not have permission to access this page.', 'digiplanet-digital-products'));
        }
        
        include DIGIPLANET_PLUGIN_DIR . 'admin/views/licenses.php';
    }
    
    /**
     * Render customers page
     */
    public function render_customers_page() {
        if (!current_user_can('digiplanet_manage_customers')) {
            wp_die(__('You do not have permission to access this page.', 'digiplanet-digital-products'));
        }
        
        $action = $_GET['action'] ?? 'list';
        
        switch ($action) {
            case 'edit':
                $this->render_edit_customer();
                break;
            case 'create':
                $this->render_create_customer();
                break;
            default:
                include DIGIPLANET_PLUGIN_DIR . 'admin/views/customers.php';
        }
    }
    
    /**
     * Render reviews page
     */
    public function render_reviews_page() {
        if (!current_user_can('digiplanet_manage_reviews')) {
            wp_die(__('You do not have permission to access this page.', 'digiplanet-digital-products'));
        }
        
        include DIGIPLANET_PLUGIN_DIR . 'admin/views/reviews.php';
    }
    
    /**
     * Render reports page
     */
    public function render_reports_page() {
        if (!current_user_can('digiplanet_view_reports')) {
            wp_die(__('You do not have permission to access this page.', 'digiplanet-digital-products'));
        }
        
        include DIGIPLANET_PLUGIN_DIR . 'admin/views/reports.php';
    }
    
    /**
     * Render edit customer page
     */
    private function render_edit_customer() {
        $customer_id = intval($_GET['customer_id'] ?? 0);
        
        if (!$customer_id) {
            wp_die(__('Invalid customer ID.', 'digiplanet-digital-products'));
        }
        
        $customer = get_userdata($customer_id);
        
        if (!$customer) {
            wp_die(__('Customer not found.', 'digiplanet-digital-products'));
        }
        
        include DIGIPLANET_PLUGIN_DIR . 'admin/views/edit-customer.php';
    }
    
    /**
     * Render create customer page
     */
    private function render_create_customer() {
        include DIGIPLANET_PLUGIN_DIR . 'admin/views/create-customer.php';
    }
    
    /**
     * AJAX: Bulk action
     */
    public function ajax_bulk_action() {
        check_ajax_referer('digiplanet_admin_nonce', 'nonce');
        
        if (!current_user_can('digiplanet_manage_products')) {
            wp_die(__('Unauthorized.', 'digiplanet-digital-products'));
        }
        
        $product_ids = $_POST['product_ids'] ?? [];
        $bulk_action = $_POST['bulk_action'] ?? '';
        
        if (empty($product_ids) || empty($bulk_action)) {
            wp_send_json_error(['message' => __('Invalid request.', 'digiplanet-digital-products')]);
        }
        
        global $wpdb;
        
        switch ($bulk_action) {
            case 'delete':
                foreach ($product_ids as $product_id) {
                    $wpdb->delete(
                        $wpdb->prefix . 'digiplanet_products',
                        ['id' => $product_id],
                        ['%d']
                    );
                }
                break;
                
            case 'publish':
                $wpdb->query($wpdb->prepare("
                    UPDATE {$wpdb->prefix}digiplanet_products 
                    SET status = 'published' 
                    WHERE id IN (" . implode(',', array_fill(0, count($product_ids), '%d')) . ")
                ", $product_ids));
                break;
                
            case 'draft':
                $wpdb->query($wpdb->prepare("
                    UPDATE {$wpdb->prefix}digiplanet_products 
                    SET status = 'draft' 
                    WHERE id IN (" . implode(',', array_fill(0, count($product_ids), '%d')) . ")
                ", $product_ids));
                break;
        }
        
        wp_send_json_success(['message' => __('Bulk action completed.', 'digiplanet-digital-products')]);
    }
    
    // Other AJAX methods...
}