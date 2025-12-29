<?php
/**
 * Admin settings page
 */

if (!defined('ABSPATH')) {
    exit;
}

class Digiplanet_Settings {
    
    private static $instance = null;
    private $settings_tabs = [];
    
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
        add_action('admin_menu', [$this, 'add_admin_menu']);
        add_action('admin_init', [$this, 'register_settings']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_scripts']);
    }
    
    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_menu_page(
            __('Digiplanet Digital Products', 'digiplanet-digital-products'),
            __('Digital Products', 'digiplanet-digital-products'),
            'digiplanet_manage_settings',
            'digiplanet-dashboard',
            [$this, 'render_dashboard'],
            'dashicons-products',
            30
        );
        
        add_submenu_page(
            'digiplanet-dashboard',
            __('Settings', 'digiplanet-digital-products'),
            __('Settings', 'digiplanet-digital-products'),
            'digiplanet_manage_settings',
            'digiplanet-settings',
            [$this, 'render_settings_page']
        );
        
        add_submenu_page(
            'digiplanet-dashboard',
            __('Documentation', 'digiplanet-digital-products'),
            __('Documentation', 'digiplanet-digital-products'),
            'read',
            'digiplanet-documentation',
            [$this, 'render_documentation']
        );
    }
    
    /**
     * Register settings
     */
    public function register_settings() {
        // General Settings
        register_setting('digiplanet_general_settings', 'digiplanet_currency');
        register_setting('digiplanet_general_settings', 'digiplanet_currency_position');
        register_setting('digiplanet_general_settings', 'digiplanet_decimal_places');
        register_setting('digiplanet_general_settings', 'digiplanet_thousand_separator');
        register_setting('digiplanet_general_settings', 'digiplanet_decimal_separator');
        register_setting('digiplanet_general_settings', 'digiplanet_enable_tax');
        register_setting('digiplanet_general_settings', 'digiplanet_tax_rate');
        
        // Stripe Settings
        register_setting('digiplanet_stripe_settings', 'digiplanet_enable_stripe');
        register_setting('digiplanet_stripe_settings', 'digiplanet_stripe_test_mode');
        register_setting('digiplanet_stripe_settings', 'digiplanet_stripe_test_publishable_key');
        register_setting('digiplanet_stripe_settings', 'digiplanet_stripe_test_secret_key');
        register_setting('digiplanet_stripe_settings', 'digiplanet_stripe_live_publishable_key');
        register_setting('digiplanet_stripe_settings', 'digiplanet_stripe_live_secret_key');
        register_setting('digiplanet_stripe_settings', 'digiplanet_stripe_webhook_secret');
        
        // Paystack Settings
        register_setting('digiplanet_paystack_settings', 'digiplanet_enable_paystack');
        register_setting('digiplanet_paystack_settings', 'digiplanet_paystack_test_mode');
        register_setting('digiplanet_paystack_settings', 'digiplanet_paystack_test_public_key');
        register_setting('digiplanet_paystack_settings', 'digiplanet_paystack_test_secret_key');
        register_setting('digiplanet_paystack_settings', 'digiplanet_paystack_live_public_key');
        register_setting('digiplanet_paystack_settings', 'digiplanet_paystack_live_secret_key');
        
        // Email Settings
        register_setting('digiplanet_email_settings', 'digiplanet_email_from_name');
        register_setting('digiplanet_email_settings', 'digiplanet_email_from_address');
        register_setting('digiplanet_email_settings', 'digiplanet_order_email_subject');
        register_setting('digiplanet_email_settings', 'digiplanet_order_email_template');
        
        // Pages Settings
        register_setting('digiplanet_pages_settings', 'digiplanet_cart_page_id');
        register_setting('digiplanet_pages_settings', 'digiplanet_checkout_page_id');
        register_setting('digiplanet_pages_settings', 'digiplanet_account_page_id');
        register_setting('digiplanet_pages_settings', 'digiplanet_products_page_id');
        
        // Advanced Settings
        register_setting('digiplanet_advanced_settings', 'digiplanet_remove_data_on_uninstall');
        register_setting('digiplanet_advanced_settings', 'digiplanet_enable_debug_log');
    }
    
    /**
     * Render settings page
     */
    public function render_settings_page() {
        $active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'general';
        ?>
        <div class="wrap digiplanet-settings">
            <h1><?php _e('Digiplanet Digital Products Settings', 'digiplanet-digital-products'); ?></h1>
            
            <h2 class="nav-tab-wrapper">
                <a href="?page=digiplanet-settings&tab=general" class="nav-tab <?php echo $active_tab == 'general' ? 'nav-tab-active' : ''; ?>">
                    <?php _e('General', 'digiplanet-digital-products'); ?>
                </a>
                <a href="?page=digiplanet-settings&tab=stripe" class="nav-tab <?php echo $active_tab == 'stripe' ? 'nav-tab-active' : ''; ?>">
                    <?php _e('Stripe', 'digiplanet-digital-products'); ?>
                </a>
                <a href="?page=digiplanet-settings&tab=paystack" class="nav-tab <?php echo $active_tab == 'paystack' ? 'nav-tab-active' : ''; ?>">
                    <?php _e('Paystack', 'digiplanet-digital-products'); ?>
                </a>
                <a href="?page=digiplanet-settings&tab=email" class="nav-tab <?php echo $active_tab == 'email' ? 'nav-tab-active' : ''; ?>">
                    <?php _e('Email', 'digiplanet-digital-products'); ?>
                </a>
                <a href="?page=digiplanet-settings&tab=pages" class="nav-tab <?php echo $active_tab == 'pages' ? 'nav-tab-active' : ''; ?>">
                    <?php _e('Pages', 'digiplanet-digital-products'); ?>
                </a>
                <a href="?page=digiplanet-settings&tab=advanced" class="nav-tab <?php echo $active_tab == 'advanced' ? 'nav-tab-active' : ''; ?>">
                    <?php _e('Advanced', 'digiplanet-digital-products'); ?>
                </a>
            </h2>
            
            <div class="digiplanet-settings-content">
                <form method="post" action="options.php">
                    <?php
                    switch ($active_tab) {
                        case 'general':
                            settings_fields('digiplanet_general_settings');
                            $this->render_general_settings();
                            break;
                            
                        case 'stripe':
                            settings_fields('digiplanet_stripe_settings');
                            $this->render_stripe_settings();
                            break;
                            
                        case 'paystack':
                            settings_fields('digiplanet_paystack_settings');
                            $this->render_paystack_settings();
                            break;
                            
                        case 'email':
                            settings_fields('digiplanet_email_settings');
                            $this->render_email_settings();
                            break;
                            
                        case 'pages':
                            settings_fields('digiplanet_pages_settings');
                            $this->render_pages_settings();
                            break;
                            
                        case 'advanced':
                            settings_fields('digiplanet_advanced_settings');
                            $this->render_advanced_settings();
                            break;
                    }
                    ?>
                    
                    <?php submit_button(); ?>
                </form>
            </div>
        </div>
        <?php
    }
    
    /**
     * Render general settings
     */
    private function render_general_settings() {
        ?>
        <table class="form-table">
            <tr>
                <th scope="row"><?php _e('Currency', 'digiplanet-digital-products'); ?></th>
                <td>
                    <select name="digiplanet_currency">
                        <option value="USD" <?php selected(get_option('digiplanet_currency'), 'USD'); ?>>USD ($)</option>
                        <option value="EUR" <?php selected(get_option('digiplanet_currency'), 'EUR'); ?>>EUR (€)</option>
                        <option value="GBP" <?php selected(get_option('digiplanet_currency'), 'GBP'); ?>>GBP (£)</option>
                        <option value="NGN" <?php selected(get_option('digiplanet_currency'), 'NGN'); ?>>NGN (₦)</option>
                        <option value="CAD" <?php selected(get_option('digiplanet_currency'), 'CAD'); ?>>CAD (C$)</option>
                        <option value="AUD" <?php selected(get_option('digiplanet_currency'), 'AUD'); ?>>AUD (A$)</option>
                    </select>
                </td>
            </tr>
            
            <tr>
                <th scope="row"><?php _e('Currency Position', 'digiplanet-digital-products'); ?></th>
                <td>
                    <select name="digiplanet_currency_position">
                        <option value="left" <?php selected(get_option('digiplanet_currency_position'), 'left'); ?>><?php _e('Left ($99.99)', 'digiplanet-digital-products'); ?></option>
                        <option value="right" <?php selected(get_option('digiplanet_currency_position'), 'right'); ?>><?php _e('Right (99.99$)', 'digiplanet-digital-products'); ?></option>
                        <option value="left_space" <?php selected(get_option('digiplanet_currency_position'), 'left_space'); ?>><?php _e('Left with space ($ 99.99)', 'digiplanet-digital-products'); ?></option>
                        <option value="right_space" <?php selected(get_option('digiplanet_currency_position'), 'right_space'); ?>><?php _e('Right with space (99.99 $)', 'digiplanet-digital-products'); ?></option>
                    </select>
                </td>
            </tr>
            
            <tr>
                <th scope="row"><?php _e('Decimal Places', 'digiplanet-digital-products'); ?></th>
                <td>
                    <input type="number" name="digiplanet_decimal_places" value="<?php echo esc_attr(get_option('digiplanet_decimal_places', 2)); ?>" min="0" max="4">
                </td>
            </tr>
            
            <tr>
                <th scope="row"><?php _e('Thousand Separator', 'digiplanet-digital-products'); ?></th>
                <td>
                    <input type="text" name="digiplanet_thousand_separator" value="<?php echo esc_attr(get_option('digiplanet_thousand_separator', ',')); ?>" maxlength="1">
                </td>
            </tr>
            
            <tr>
                <th scope="row"><?php _e('Decimal Separator', 'digiplanet-digital-products'); ?></th>
                <td>
                    <input type="text" name="digiplanet_decimal_separator" value="<?php echo esc_attr(get_option('digiplanet_decimal_separator', '.')); ?>" maxlength="1">
                </td>
            </tr>
            
            <tr>
                <th scope="row"><?php _e('Enable Tax', 'digiplanet-digital-products'); ?></th>
                <td>
                    <label>
                        <input type="checkbox" name="digiplanet_enable_tax" value="yes" <?php checked(get_option('digiplanet_enable_tax'), 'yes'); ?>>
                        <?php _e('Enable tax calculation', 'digiplanet-digital-products'); ?>
                    </label>
                </td>
            </tr>
            
            <tr>
                <th scope="row"><?php _e('Tax Rate (%)', 'digiplanet-digital-products'); ?></th>
                <td>
                    <input type="number" name="digiplanet_tax_rate" value="<?php echo esc_attr(get_option('digiplanet_tax_rate', 0)); ?>" min="0" max="100" step="0.1">
                    <p class="description"><?php _e('Enter tax rate percentage', 'digiplanet-digital-products'); ?></p>
                </td>
            </tr>
        </table>
        <?php
    }
    
    /**
     * Render Stripe settings
     */
    private function render_stripe_settings() {
        ?>
        <table class="form-table">
            <tr>
                <th scope="row"><?php _e('Enable Stripe', 'digiplanet-digital-products'); ?></th>
                <td>
                    <label>
                        <input type="checkbox" name="digiplanet_enable_stripe" value="yes" <?php checked(get_option('digiplanet_enable_stripe'), 'yes'); ?>>
                        <?php _e('Enable Stripe payment gateway', 'digiplanet-digital-products'); ?>
                    </label>
                </td>
            </tr>
            
            <tr>
                <th scope="row"><?php _e('Test Mode', 'digiplanet-digital-products'); ?></th>
                <td>
                    <label>
                        <input type="checkbox" name="digiplanet_stripe_test_mode" value="yes" <?php checked(get_option('digiplanet_stripe_test_mode'), 'yes'); ?>>
                        <?php _e('Enable test mode', 'digiplanet-digital-products'); ?>
                    </label>
                    <p class="description"><?php _e('Use test API keys for development', 'digiplanet-digital-products'); ?></p>
                </td>
            </tr>
            
            <tr>
                <th scope="row"><?php _e('Test Publishable Key', 'digiplanet-digital-products'); ?></th>
                <td>
                    <input type="text" name="digiplanet_stripe_test_publishable_key" value="<?php echo esc_attr(get_option('digiplanet_stripe_test_publishable_key')); ?>" class="regular-text">
                </td>
            </tr>
            
            <tr>
                <th scope="row"><?php _e('Test Secret Key', 'digiplanet-digital-products'); ?></th>
                <td>
                    <input type="password" name="digiplanet_stripe_test_secret_key" value="<?php echo esc_attr(get_option('digiplanet_stripe_test_secret_key')); ?>" class="regular-text">
                </td>
            </tr>
            
            <tr>
                <th scope="row"><?php _e('Live Publishable Key', 'digiplanet-digital-products'); ?></th>
                <td>
                    <input type="text" name="digiplanet_stripe_live_publishable_key" value="<?php echo esc_attr(get_option('digiplanet_stripe_live_publishable_key')); ?>" class="regular-text">
                </td>
            </tr>
            
            <tr>
                <th scope="row"><?php _e('Live Secret Key', 'digiplanet-digital-products'); ?></th>
                <td>
                    <input type="password" name="digiplanet_stripe_live_secret_key" value="<?php echo esc_attr(get_option('digiplanet_stripe_live_secret_key')); ?>" class="regular-text">
                </td>
            </tr>
            
            <tr>
                <th scope="row"><?php _e('Webhook Secret', 'digiplanet-digital-products'); ?></th>
                <td>
                    <input type="password" name="digiplanet_stripe_webhook_secret" value="<?php echo esc_attr(get_option('digiplanet_stripe_webhook_secret')); ?>" class="regular-text">
                    <p class="description">
                        <?php _e('Webhook URL:', 'digiplanet-digital-products'); ?>
                        <code><?php echo home_url('/?digiplanet_payment_callback=1&gateway=stripe'); ?></code>
                    </p>
                </td>
            </tr>
        </table>
        <?php
    }
    
    /**
     * Render Paystack settings
     */
    private function render_paystack_settings() {
        ?>
        <table class="form-table">
            <tr>
                <th scope="row"><?php _e('Enable Paystack', 'digiplanet-digital-products'); ?></th>
                <td>
                    <label>
                        <input type="checkbox" name="digiplanet_enable_paystack" value="yes" <?php checked(get_option('digiplanet_enable_paystack'), 'yes'); ?>>
                        <?php _e('Enable Paystack payment gateway', 'digiplanet-digital-products'); ?>
                    </label>
                </td>
            </tr>
            
            <tr>
                <th scope="row"><?php _e('Test Mode', 'digiplanet-digital-products'); ?></th>
                <td>
                    <label>
                        <input type="checkbox" name="digiplanet_paystack_test_mode" value="yes" <?php checked(get_option('digiplanet_paystack_test_mode'), 'yes'); ?>>
                        <?php _e('Enable test mode', 'digiplanet-digital-products'); ?>
                    </label>
                </td>
            </tr>
            
            <tr>
                <th scope="row"><?php _e('Test Public Key', 'digiplanet-digital-products'); ?></th>
                <td>
                    <input type="text" name="digiplanet_paystack_test_public_key" value="<?php echo esc_attr(get_option('digiplanet_paystack_test_public_key')); ?>" class="regular-text">
                </td>
            </tr>
            
            <tr>
                <th scope="row"><?php _e('Test Secret Key', 'digiplanet-digital-products'); ?></th>
                <td>
                    <input type="password" name="digiplanet_paystack_test_secret_key" value="<?php echo esc_attr(get_option('digiplanet_paystack_test_secret_key')); ?>" class="regular-text">
                </td>
            </tr>
            
            <tr>
                <th scope="row"><?php _e('Live Public Key', 'digiplanet-digital-products'); ?></th>
                <td>
                    <input type="text" name="digiplanet_paystack_live_public_key" value="<?php echo esc_attr(get_option('digiplanet_paystack_live_public_key')); ?>" class="regular-text">
                </td>
            </tr>
            
            <tr>
                <th scope="row"><?php _e('Live Secret Key', 'digiplanet-digital-products'); ?></th>
                <td>
                    <input type="password" name="digiplanet_paystack_live_secret_key" value="<?php echo esc_attr(get_option('digiplanet_paystack_live_secret_key')); ?>" class="regular-text">
                </td>
            </tr>
        </table>
        <?php
    }
    
    /**
     * Render dashboard
     */
    public function render_dashboard() {
        ?>
        <div class="wrap digiplanet-dashboard">
            <h1><?php _e('Digiplanet Digital Products Dashboard', 'digiplanet-digital-products'); ?></h1>
            
            <div class="digiplanet-dashboard-widgets">
                <div class="digiplanet-widget">
                    <h3><?php _e('Total Products', 'digiplanet-digital-products'); ?></h3>
                    <p class="digiplanet-widget-number"><?php echo $this->get_product_count(); ?></p>
                </div>
                
                <div class="digiplanet-widget">
                    <h3><?php _e('Total Orders', 'digiplanet-digital-products'); ?></h3>
                    <p class="digiplanet-widget-number"><?php echo $this->get_order_count(); ?></p>
                </div>
                
                <div class="digiplanet-widget">
                    <h3><?php _e('Total Revenue', 'digiplanet-digital-products'); ?></h3>
                    <p class="digiplanet-widget-number"><?php echo $this->get_total_revenue(); ?></p>
                </div>
                
                <div class="digiplanet-widget">
                    <h3><?php _e('Total Customers', 'digiplanet-digital-products'); ?></h3>
                    <p class="digiplanet-widget-number"><?php echo $this->get_customer_count(); ?></p>
                </div>
            </div>
            
            <div class="digiplanet-dashboard-actions">
                <a href="?page=digiplanet-products" class="button button-primary"><?php _e('Manage Products', 'digiplanet-digital-products'); ?></a>
                <a href="?page=digiplanet-orders" class="button"><?php _e('View Orders', 'digiplanet-digital-products'); ?></a>
                <a href="?page=digiplanet-settings" class="button"><?php _e('Settings', 'digiplanet-digital-products'); ?></a>
            </div>
        </div>
        <?php
    }
    
    /**
     * Get product count
     */
    private function get_product_count() {
        global $wpdb;
        return $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}digiplanet_products");
    }
    
    /**
     * Get order count
     */
    private function get_order_count() {
        global $wpdb;
        return $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}digiplanet_orders");
    }
    
    /**
     * Get total revenue
     */
    private function get_total_revenue() {
        global $wpdb;
        $total = $wpdb->get_var("SELECT SUM(total_amount) FROM {$wpdb->prefix}digiplanet_orders WHERE payment_status = 'completed'");
        return Digiplanet_Product_Manager::get_instance()->format_price($total ?: 0);
    }
    
    /**
     * Get customer count
     */
    private function get_customer_count() {
        $user_count = count_users();
        $count = 0;
        
        if (isset($user_count['avail_roles'])) {
            $count += $user_count['avail_roles']['digital_customer'] ?? 0;
            $count += $user_count['avail_roles']['software_client'] ?? 0;
        }
        
        return $count;
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
        }
    }
}