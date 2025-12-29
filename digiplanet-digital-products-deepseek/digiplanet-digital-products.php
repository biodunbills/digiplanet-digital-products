<?php
/**
 * Plugin Name: Digiplanet Digital Products
 * Plugin URI: https://digiplanetsolutions.com
 * Description: A complete digital product sales solution with Stripe and Paystack integration
 * Version: 1.0.0
 * Author: Digiplanet Solutions LLC
 * Author URI: https://digiplanetsolutions.com
 * License: GPL v2 or later
 * Text Domain: digiplanet-digital-products
 * Domain Path: /languages
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('DIGIPLANET_VERSION', '1.0.0');
define('DIGIPLANET_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('DIGIPLANET_PLUGIN_URL', plugin_dir_url(__FILE__));
define('DIGIPLANET_PLUGIN_BASENAME', plugin_basename(__FILE__));
define('DIGIPLANET_ASSETS_URL', DIGIPLANET_PLUGIN_URL . 'assets/');
define('DIGIPLANET_TEMPLATES_DIR', DIGIPLANET_PLUGIN_DIR . 'templates/');

// Plugin requirements
define('DIGIPLANET_MIN_PHP_VERSION', '7.4');
define('DIGIPLANET_MIN_WP_VERSION', '5.8');
define('DIGIPLANET_MIN_ELEMENTOR_VERSION', '3.5.0');

// Autoloader
spl_autoload_register(function ($class) {
    $prefix = 'Digiplanet_';
    $base_dir = DIGIPLANET_PLUGIN_DIR . 'includes/';
    
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    
    $relative_class = substr($class, $len);
    $file = $base_dir . 'class-' . str_replace('_', '-', strtolower($relative_class)) . '.php';
    
    if (file_exists($file)) {
        require $file;
    }
});

/**
 * Check system requirements
 */
function digiplanet_check_requirements() {
    $errors = array();
    
    // Check PHP version
    if (version_compare(PHP_VERSION, DIGIPLANET_MIN_PHP_VERSION, '<')) {
        $errors[] = sprintf(
            __('Digiplanet Digital Products requires PHP version %s or higher. You are running version %s.', 'digiplanet-digital-products'),
            DIGIPLANET_MIN_PHP_VERSION,
            PHP_VERSION
        );
    }
    
    // Check WordPress version
    if (version_compare(get_bloginfo('version'), DIGIPLANET_MIN_WP_VERSION, '<')) {
        $errors[] = sprintf(
            __('Digiplanet Digital Products requires WordPress version %s or higher. You are running version %s.', 'digiplanet-digital-products'),
            DIGIPLANET_MIN_WP_VERSION,
            get_bloginfo('version')
        );
    }
    
    // Check for required extensions
    $required_extensions = ['json', 'mbstring', 'curl'];
    foreach ($required_extensions as $extension) {
        if (!extension_loaded($extension)) {
            $errors[] = sprintf(
                __('Digiplanet Digital Products requires the %s PHP extension to be installed.', 'digiplanet-digital-products'),
                $extension
            );
        }
    }
    
    return $errors;
}

/**
 * Initialize the plugin
 */
function digiplanet_init_plugin() {
    // Check requirements first
    $errors = digiplanet_check_requirements();
    if (!empty($errors) && is_admin()) {
        add_action('admin_notices', function() use ($errors) {
            ?>
            <div class="notice notice-error">
                <p><strong><?php _e('Digiplanet Digital Products - Requirements Error:', 'digiplanet-digital-products'); ?></strong></p>
                <ul style="list-style-type: disc; padding-left: 20px;">
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo esc_html($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php
        });
        return;
    }
    
    // Load text domain for translations
    load_plugin_textdomain(
        'digiplanet-digital-products',
        false,
        dirname(plugin_basename(__FILE__)) . '/languages'
    );
    
    // Initialize core
    Digiplanet_Core::get_instance();
}

/**
 * Activation hook
 */
register_activation_hook(__FILE__, function() {
    // Check requirements
    $errors = digiplanet_check_requirements();
    if (!empty($errors)) {
        deactivate_plugins(plugin_basename(__FILE__));
        wp_die(implode('<br>', $errors));
    }
    
    // Include and run database setup
    require_once DIGIPLANET_PLUGIN_DIR . 'includes/class-database.php';
    Digiplanet_Database::create_tables();
    
    // Create user roles
    digiplanet_create_user_roles();
    
    // Set default options
    update_option('digiplanet_version', DIGIPLANET_VERSION);
    update_option('digiplanet_currency', 'USD');
    update_option('digiplanet_currency_position', 'left');
    update_option('digiplanet_enable_stripe', 'no');
    update_option('digiplanet_enable_paystack', 'no');
    update_option('digiplanet_decimal_places', 2);
    update_option('digiplanet_thousand_separator', ',');
    update_option('digiplanet_decimal_separator', '.');
    
    // Create pages
    digiplanet_create_pages();
    
    // Flush rewrite rules
    flush_rewrite_rules();
});

/**
 * Deactivation hook
 */
register_deactivation_hook(__FILE__, function() {
    // Clear scheduled tasks
    wp_clear_scheduled_hook('digiplanet_daily_cleanup');
    
    // Flush rewrite rules
    flush_rewrite_rules();
});

/**
 * Uninstall hook
 */
register_uninstall_hook(__FILE__, 'digiplanet_uninstall_plugin');

function digiplanet_uninstall_plugin() {
    // Clean up options if needed
    if (get_option('digiplanet_remove_data_on_uninstall') === 'yes') {
        // Remove all plugin data
        global $wpdb;
        
        $tables = [
            $wpdb->prefix . 'digiplanet_products',
            $wpdb->prefix . 'digiplanet_categories',
            $wpdb->prefix . 'digiplanet_orders',
            $wpdb->prefix . 'digiplanet_order_items',
            $wpdb->prefix . 'digiplanet_licenses',
            $wpdb->prefix . 'digiplanet_reviews',
            $wpdb->prefix . 'digiplanet_download_logs',
        ];
        
        foreach ($tables as $table) {
            $wpdb->query("DROP TABLE IF EXISTS $table");
        }
        
        // Remove options
        $options = [
            'digiplanet_version',
            'digiplanet_db_version',
            'digiplanet_currency',
            'digiplanet_currency_position',
            'digiplanet_enable_stripe',
            'digiplanet_stripe_test_secret_key',
            'digiplanet_stripe_test_publishable_key',
            'digiplanet_stripe_live_secret_key',
            'digiplanet_stripe_live_publishable_key',
            'digiplanet_enable_paystack',
            'digiplanet_paystack_test_secret_key',
            'digiplanet_paystack_test_public_key',
            'digiplanet_paystack_live_secret_key',
            'digiplanet_paystack_live_public_key',
            'digiplanet_decimal_places',
            'digiplanet_thousand_separator',
            'digiplanet_decimal_separator',
            'digiplanet_tax_rate',
            'digiplanet_enable_tax',
            'digiplanet_checkout_page_id',
            'digiplanet_cart_page_id',
            'digiplanet_account_page_id',
            'digiplanet_products_page_id',
            'digiplanet_terms_page_id',
            'digiplanet_privacy_page_id',
            'digiplanet_remove_data_on_uninstall',
        ];
        
        foreach ($options as $option) {
            delete_option($option);
        }
        
        // Remove user roles
        remove_role('digital_customer');
        remove_role('software_client');
    }
}

/**
 * Create custom user roles
 */
function digiplanet_create_user_roles() {
    // Add Digital Customer role
    add_role('digital_customer', __('Digital Customer', 'digiplanet-digital-products'), [
        'read' => true,
        'edit_posts' => false,
        'upload_files' => false,
        'digiplanet_view_products' => true,
        'digiplanet_purchase_products' => true,
        'digiplanet_view_orders' => true,
        'digiplanet_view_licenses' => true,
        'digiplanet_download_products' => true,
        'digiplanet_submit_reviews' => true,
        'digiplanet_view_account' => true,
    ]);
    
    // Add Software Client role
    add_role('software_client', __('Software Client', 'digiplanet-digital-products'), [
        'read' => true,
        'edit_posts' => false,
        'upload_files' => true,
        'digiplanet_view_client_portal' => true,
        'digiplanet_access_support' => true,
        'digiplanet_view_documents' => true,
        'digiplanet_submit_tickets' => true,
        'digiplanet_manage_projects' => false,
        'digiplanet_view_reports' => false,
        'digiplanet_view_account' => true,
    ]);
    
    // Add capabilities to administrator
    $admin_role = get_role('administrator');
    if ($admin_role) {
        $admin_capabilities = [
            'digiplanet_manage_products',
            'digiplanet_manage_orders',
            'digiplanet_manage_licenses',
            'digiplanet_manage_customers',
            'digiplanet_manage_settings',
            'digiplanet_view_reports',
            'digiplanet_manage_software_clients',
            'digiplanet_manage_categories',
            'digiplanet_manage_reviews',
            'digiplanet_export_data',
            'digiplanet_import_data',
        ];
        
        foreach ($admin_capabilities as $cap) {
            $admin_role->add_cap($cap);
        }
    }
}

/**
 * Create default pages
 */
function digiplanet_create_pages() {
    $pages = [
        'cart' => [
            'title' => __('Cart', 'digiplanet-digital-products'),
            'content' => '[digiplanet_cart]',
            'option' => 'digiplanet_cart_page_id'
        ],
        'checkout' => [
            'title' => __('Checkout', 'digiplanet-digital-products'),
            'content' => '[digiplanet_checkout]',
            'option' => 'digiplanet_checkout_page_id'
        ],
        'account' => [
            'title' => __('My Account', 'digiplanet-digital-products'),
            'content' => '[digiplanet_account]',
            'option' => 'digiplanet_account_page_id'
        ],
        'products' => [
            'title' => __('Digital Products', 'digiplanet-digital-products'),
            'content' => '[digiplanet_products]',
            'option' => 'digiplanet_products_page_id'
        ],
    ];
    
    foreach ($pages as $key => $page) {
        $existing_page = get_page_by_title($page['title']);
        
        if (!$existing_page) {
            $page_id = wp_insert_post([
                'post_title' => $page['title'],
                'post_content' => $page['content'],
                'post_status' => 'publish',
                'post_type' => 'page',
                'comment_status' => 'closed'
            ]);
            
            if (!is_wp_error($page_id)) {
                update_option($page['option'], $page_id);
            }
        } else {
            update_option($page['option'], $existing_page->ID);
        }
    }
}

// Initialize plugin
add_action('plugins_loaded', 'digiplanet_init_plugin');