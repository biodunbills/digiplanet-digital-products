<?php
/**
 * Core plugin class
 */

if (!defined('ABSPATH')) {
    exit;
}

class Digiplanet_Core {
    
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
        $this->load_dependencies();
        $this->init_hooks();
    }
    
    /**
     * Load required dependencies
     */
    private function load_dependencies() {
        // Load includes
        require_once DIGIPLANET_PLUGIN_DIR . 'includes/class-product-manager.php';
        require_once DIGIPLANET_PLUGIN_DIR . 'includes/class-cart-manager.php';
        require_once DIGIPLANET_PLUGIN_DIR . 'includes/class-order-manager.php';
        require_once DIGIPLANET_PLUGIN_DIR . 'includes/class-account-manager.php';
        require_once DIGIPLANET_PLUGIN_DIR . 'includes/class-payments.php';
        
        // Load admin if in admin area
        if (is_admin()) {
            require_once DIGIPLANET_PLUGIN_DIR . 'admin/class-admin.php';
            require_once DIGIPLANET_PLUGIN_DIR . 'admin/class-products-admin.php';
            require_once DIGIPLANET_PLUGIN_DIR . 'admin/class-settings.php';
        }
        
        // Load public-facing
        require_once DIGIPLANET_PLUGIN_DIR . 'public/class-public.php';
        
        // Load modules
        require_once DIGIPLANET_PLUGIN_DIR . 'modules/user-portal/class-user-portal.php';
        require_once DIGIPLANET_PLUGIN_DIR . 'modules/product-display/class-product-display.php';
        
        // Load Elementor integration if available
        if (defined('ELEMENTOR_VERSION')) {
            require_once DIGIPLANET_PLUGIN_DIR . 'modules/elementor/class-elementor-integration.php';
        }
    }
    
    /**
     * Initialize hooks
     */
    private function init_hooks() {
        add_action('init', array($this, 'init'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_public_assets'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
        add_action('wp_head', array($this, 'add_meta_tags'));
        
        // Ajax handlers
        add_action('wp_ajax_digiplanet_add_to_cart', array($this, 'ajax_add_to_cart'));
        add_action('wp_ajax_nopriv_digiplanet_add_to_cart', array($this, 'ajax_add_to_cart'));
        add_action('wp_ajax_digiplanet_remove_from_cart', array($this, 'ajax_remove_from_cart'));
        add_action('wp_ajax_nopriv_digiplanet_remove_from_cart', array($this, 'ajax_remove_from_cart'));
        add_action('wp_ajax_digiplanet_update_cart', array($this, 'ajax_update_cart'));
        add_action('wp_ajax_nopriv_digiplanet_update_cart', array($this, 'ajax_update_cart'));
        
        // Shortcodes
        add_shortcode('digiplanet_products', array($this, 'products_shortcode'));
        add_shortcode('digiplanet_cart', array($this, 'cart_shortcode'));
        add_shortcode('digiplanet_checkout', array($this, 'checkout_shortcode'));
        add_shortcode('digiplanet_account', array($this, 'account_shortcode'));
    }
    
    /**
     * Initialize plugin
     */
    public function init() {
        // Register custom post types
        $this->register_post_types();
        
        // Register taxonomies
        $this->register_taxonomies();
        
        // Register endpoints
        $this->register_endpoints();
    }
    
    /**
     * Register custom post types
     */
    private function register_post_types() {
        // Digital Products CPT
        register_post_type('digiplanet_product', array(
            'labels' => array(
                'name' => __('Digital Products', 'digiplanet-digital-products'),
                'singular_name' => __('Digital Product', 'digiplanet-digital-products'),
                'menu_name' => __('Digital Products', 'digiplanet-digital-products'),
                'add_new' => __('Add New Product', 'digiplanet-digital-products'),
                'add_new_item' => __('Add New Digital Product', 'digiplanet-digital-products'),
                'edit_item' => __('Edit Digital Product', 'digiplanet-digital-products'),
                'new_item' => __('New Digital Product', 'digiplanet-digital-products'),
                'view_item' => __('View Digital Product', 'digiplanet-digital-products'),
                'search_items' => __('Search Digital Products', 'digiplanet-digital-products'),
                'not_found' => __('No digital products found', 'digiplanet-digital-products'),
                'not_found_in_trash' => __('No digital products found in trash', 'digiplanet-digital-products'),
            ),
            'public' => true,
            'has_archive' => true,
            'rewrite' => array('slug' => 'digital-products'),
            'supports' => array('title', 'editor', 'thumbnail', 'excerpt', 'comments'),
            'show_in_rest' => true,
            'menu_icon' => 'dashicons-products',
            'capability_type' => 'digiplanet_product',
            'map_meta_cap' => true,
        ));
        
        // Orders CPT
        register_post_type('digiplanet_order', array(
            'labels' => array(
                'name' => __('Orders', 'digiplanet-digital-products'),
                'singular_name' => __('Order', 'digiplanet-digital-products'),
                'menu_name' => __('Orders', 'digiplanet-digital-products'),
                'add_new' => __('Add New Order', 'digiplanet-digital-products'),
                'add_new_item' => __('Add New Order', 'digiplanet-digital-products'),
                'edit_item' => __('Edit Order', 'digiplanet-digital-products'),
                'new_item' => __('New Order', 'digiplanet-digital-products'),
                'view_item' => __('View Order', 'digiplanet-digital-products'),
                'search_items' => __('Search Orders', 'digiplanet-digital-products'),
                'not_found' => __('No orders found', 'digiplanet-digital-products'),
                'not_found_in_trash' => __('No orders found in trash', 'digiplanet-digital-products'),
            ),
            'public' => false,
            'show_ui' => true,
            'show_in_menu' => 'digiplanet-dashboard',
            'supports' => array('title'),
            'capability_type' => 'digiplanet_order',
            'map_meta_cap' => true,
        ));
    }
    
    /**
     * Register taxonomies
     */
    private function register_taxonomies() {
        // Product Categories
        register_taxonomy('digiplanet_category', 'digiplanet_product', array(
            'labels' => array(
                'name' => __('Product Categories', 'digiplanet-digital-products'),
                'singular_name' => __('Product Category', 'digiplanet-digital-products'),
                'search_items' => __('Search Categories', 'digiplanet-digital-products'),
                'all_items' => __('All Categories', 'digiplanet-digital-products'),
                'parent_item' => __('Parent Category', 'digiplanet-digital-products'),
                'parent_item_colon' => __('Parent Category:', 'digiplanet-digital-products'),
                'edit_item' => __('Edit Category', 'digiplanet-digital-products'),
                'update_item' => __('Update Category', 'digiplanet-digital-products'),
                'add_new_item' => __('Add New Category', 'digiplanet-digital-products'),
                'new_item_name' => __('New Category Name', 'digiplanet-digital-products'),
                'menu_name' => __('Categories', 'digiplanet-digital-products'),
            ),
            'hierarchical' => true,
            'show_ui' => true,
            'show_admin_column' => true,
            'query_var' => true,
            'rewrite' => array('slug' => 'product-category'),
            'show_in_rest' => true,
        ));
        
        // Product Tags
        register_taxonomy('digiplanet_tag', 'digiplanet_product', array(
            'labels' => array(
                'name' => __('Product Tags', 'digiplanet-digital-products'),
                'singular_name' => __('Product Tag', 'digiplanet-digital-products'),
                'search_items' => __('Search Tags', 'digiplanet-digital-products'),
                'all_items' => __('All Tags', 'digiplanet-digital-products'),
                'edit_item' => __('Edit Tag', 'digiplanet-digital-products'),
                'update_item' => __('Update Tag', 'digiplanet-digital-products'),
                'add_new_item' => __('Add New Tag', 'digiplanet-digital-products'),
                'new_item_name' => __('New Tag Name', 'digiplanet-digital-products'),
                'menu_name' => __('Tags', 'digiplanet-digital-products'),
            ),
            'hierarchical' => false,
            'show_ui' => true,
            'show_admin_column' => true,
            'query_var' => true,
            'rewrite' => array('slug' => 'product-tag'),
            'show_in_rest' => true,
        ));
    }
    
    /**
     * Register custom endpoints
     */
    private function register_endpoints() {
        add_rewrite_endpoint('account', EP_ROOT);
        add_rewrite_endpoint('download', EP_ROOT);
        add_rewrite_endpoint('checkout', EP_ROOT);
        add_rewrite_endpoint('cart', EP_ROOT);
        
        // API endpoints
        add_rewrite_rule('^digiplanet-api/([^/]+)/?', 'index.php?digiplanet_api=$1', 'top');
    }
    
    /**
     * Enqueue public assets
     */
    public function enqueue_public_assets() {
        // CSS
        wp_enqueue_style(
            'digiplanet-frontend',
            DIGIPLANET_ASSETS_URL . 'css/frontend-style.css',
            array(),
            DIGIPLANET_VERSION
        );
        
        // Responsive CSS
        wp_enqueue_style(
            'digiplanet-responsive',
            DIGIPLANET_ASSETS_URL . 'css/responsive.css',
            array('digiplanet-frontend'),
            DIGIPLANET_VERSION
        );
        
        // JavaScript
        wp_enqueue_script(
            'digiplanet-frontend',
            DIGIPLANET_ASSETS_URL . 'js/frontend-script.js',
            array('jquery'),
            DIGIPLANET_VERSION,
            true
        );
        
        // Cart JavaScript
        wp_enqueue_script(
            'digiplanet-cart',
            DIGIPLANET_ASSETS_URL . 'js/cart.js',
            array('jquery', 'digiplanet-frontend'),
            DIGIPLANET_VERSION,
            true
        );
        
        // Localize script with AJAX URL
        wp_localize_script('digiplanet-frontend', 'digiplanet_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('digiplanet_nonce'),
            'cart_url' => $this->get_cart_url(),
            'checkout_url' => $this->get_checkout_url(),
            'account_url' => $this->get_account_url(),
        ));
    }
    
    /**
     * Enqueue admin assets
     */
    public function enqueue_admin_assets($hook) {
        if (strpos($hook, 'digiplanet') !== false) {
            // Admin CSS
            wp_enqueue_style(
                'digiplanet-admin',
                DIGIPLANET_ASSETS_URL . 'css/admin-style.css',
                array(),
                DIGIPLANET_VERSION
            );
            
            // Admin JavaScript
            wp_enqueue_script(
                'digiplanet-admin',
                DIGIPLANET_ASSETS_URL . 'js/admin-script.js',
                array('jquery', 'jquery-ui-sortable'),
                DIGIPLANET_VERSION,
                true
            );
        }
    }
    
    /**
     * Add meta tags
     */
    public function add_meta_tags() {
        echo '<meta name="digiplanet-plugin-version" content="' . esc_attr(DIGIPLANET_VERSION) . '">';
    }
    
    /**
     * Get cart URL
     */
    public function get_cart_url() {
        return home_url('/cart/');
    }
    
    /**
     * Get checkout URL
     */
    public function get_checkout_url() {
        return home_url('/checkout/');
    }
    
    /**
     * Get account URL
     */
    public function get_account_url() {
        return home_url('/account/');
    }
    
    /**
     * Shortcode: Products Grid
     */
    public function products_shortcode($atts) {
        $atts = shortcode_atts(array(
            'category' => '',
            'tag' => '',
            'limit' => 12,
            'columns' => 4,
            'orderby' => 'date',
            'order' => 'DESC',
            'ids' => '',
            'exclude' => '',
            'featured' => false,
            'onsale' => false,
        ), $atts, 'digiplanet_products');
        
        ob_start();
        include DIGIPLANET_PLUGIN_DIR . 'public/views/product-grid.php';
        return ob_get_clean();
    }
    
    /**
     * Shortcode: Cart
     */
    public function cart_shortcode($atts) {
        ob_start();
        include DIGIPLANET_PLUGIN_DIR . 'public/views/cart.php';
        return ob_get_clean();
    }
    
    /**
     * Shortcode: Checkout
     */
    public function checkout_shortcode($atts) {
        ob_start();
        include DIGIPLANET_PLUGIN_DIR . 'public/views/checkout.php';
        return ob_get_clean();
    }
    
    /**
     * Shortcode: Account
     */
    public function account_shortcode($atts) {
        if (!is_user_logged_in()) {
            return $this->get_login_form();
        }
        
        $user = wp_get_current_user();
        ob_start();
        
        if (in_array('software_client', $user->roles)) {
            include DIGIPLANET_PLUGIN_DIR . 'modules/user-portal/templates/software-client-dashboard.php';
        } elseif (in_array('digital_customer', $user->roles) || in_array('administrator', $user->roles)) {
            include DIGIPLANET_PLUGIN_DIR . 'modules/user-portal/templates/digital-customer-dashboard.php';
        } else {
            echo '<div class="digiplanet-alert digiplanet-alert-warning">';
            echo __('You do not have access to any account area.', 'digiplanet-digital-products');
            echo '</div>';
        }
        
        return ob_get_clean();
    }
    
    /**
     * Get login form
     */
    private function get_login_form() {
        ob_start();
        ?>
        <div class="digiplanet-login-form">
            <h2><?php _e('Login to Your Account', 'digiplanet-digital-products'); ?></h2>
            <?php
            $args = array(
                'redirect' => $this->get_account_url(),
                'form_id' => 'digiplanet-login-form',
                'label_username' => __('Email Address', 'digiplanet-digital-products'),
                'label_password' => __('Password', 'digiplanet-digital-products'),
                'label_remember' => __('Remember Me', 'digiplanet-digital-products'),
                'label_log_in' => __('Login', 'digiplanet-digital-products'),
                'remember' => true,
            );
            wp_login_form($args);
            ?>
            <div class="digiplanet-login-links">
                <a href="<?php echo wp_lostpassword_url(); ?>">
                    <?php _e('Lost your password?', 'digiplanet-digital-products'); ?>
                </a>
                <?php if (get_option('users_can_register')): ?>
                    | <a href="<?php echo wp_registration_url(); ?>">
                        <?php _e('Create an Account', 'digiplanet-digital-products'); ?>
                    </a>
                <?php endif; ?>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
}