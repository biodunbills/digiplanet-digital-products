<?php
/**
 * User portal management
 */

if (!defined('ABSPATH')) {
    exit;
}

class Digiplanet_User_Portal {
    
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
        add_action('init', [$this, 'register_endpoints']);
        add_action('template_redirect', [$this, 'handle_portal_requests']);
        add_filter('query_vars', [$this, 'add_query_vars']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_portal_assets']);
    }
    
    /**
     * Register endpoints
     */
    public function register_endpoints() {
        add_rewrite_rule(
            '^account/([^/]+)/?$',
            'index.php?digiplanet_account_page=$matches[1]',
            'top'
        );
        
        add_rewrite_rule(
            '^account/?$',
            'index.php?digiplanet_account_page=dashboard',
            'top'
        );
    }
    
    /**
     * Add query vars
     */
    public function add_query_vars($vars) {
        $vars[] = 'digiplanet_account_page';
        return $vars;
    }
    
    /**
     * Handle portal requests
     */
    public function handle_portal_requests() {
        $account_page = get_query_var('digiplanet_account_page');
        
        if ($account_page) {
            $this->display_account_portal($account_page);
            exit;
        }
    }
    
    /**
     * Display account portal
     */
    private function display_account_portal($page = 'dashboard') {
        if (!is_user_logged_in()) {
            auth_redirect();
        }
        
        $user = wp_get_current_user();
        $allowed_pages = $this->get_allowed_pages($user);
        
        if (!in_array($page, $allowed_pages)) {
            $page = 'dashboard';
        }
        
        get_header();
        
        echo '<div class="digiplanet-account-portal">';
        echo '<div class="digiplanet-account-wrapper">';
        
        // Sidebar
        $this->display_account_sidebar($user, $page);
        
        // Main content
        echo '<div class="digiplanet-account-main">';
        $this->display_account_content($user, $page);
        echo '</div>';
        
        echo '</div>';
        echo '</div>';
        
        get_footer();
    }
    
    /**
     * Get allowed pages based on user role
     */
    private function get_allowed_pages($user) {
        $pages = ['dashboard', 'settings'];
        
        if (in_array('digital_customer', $user->roles) || in_array('administrator', $user->roles)) {
            $pages = array_merge($pages, ['products', 'orders', 'licenses', 'downloads', 'reviews']);
        }
        
        if (in_array('software_client', $user->roles) || in_array('administrator', $user->roles)) {
            $pages = array_merge($pages, ['projects', 'support', 'documents', 'billing']);
        }
        
        return array_unique($pages);
    }
    
    /**
     * Display account sidebar
     */
    private function display_account_sidebar($user, $current_page) {
        ?>
        <div class="digiplanet-account-sidebar">
            <div class="digiplanet-account-user">
                <div class="digiplanet-account-avatar">
                    <?php echo get_avatar($user->ID, 80); ?>
                </div>
                <div class="digiplanet-account-user-info">
                    <h3><?php echo esc_html($user->display_name); ?></h3>
                    <p><?php echo esc_html($this->get_user_role_label($user)); ?></p>
                </div>
            </div>
            
            <nav class="digiplanet-account-nav">
                <ul>
                    <li>
                        <a href="<?php echo $this->get_account_url('dashboard'); ?>" class="<?php echo $current_page === 'dashboard' ? 'active' : ''; ?>">
                            <span class="digiplanet-nav-icon dashicons dashicons-dashboard"></span>
                            <?php _e('Dashboard', 'digiplanet-digital-products'); ?>
                        </a>
                    </li>
                    
                    <?php if (in_array('digital_customer', $user->roles) || in_array('administrator', $user->roles)): ?>
                        <li>
                            <a href="<?php echo $this->get_account_url('products'); ?>" class="<?php echo $current_page === 'products' ? 'active' : ''; ?>">
                                <span class="digiplanet-nav-icon dashicons dashicons-cart"></span>
                                <?php _e('My Products', 'digiplanet-digital-products'); ?>
                            </a>
                        </li>
                        <li>
                            <a href="<?php echo $this->get_account_url('orders'); ?>" class="<?php echo $current_page === 'orders' ? 'active' : ''; ?>">
                                <span class="digiplanet-nav-icon dashicons dashicons-clipboard"></span>
                                <?php _e('Orders', 'digiplanet-digital-products'); ?>
                            </a>
                        </li>
                        <li>
                            <a href="<?php echo $this->get_account_url('licenses'); ?>" class="<?php echo $current_page === 'licenses' ? 'active' : ''; ?>">
                                <span class="digiplanet-nav-icon dashicons dashicons-lock"></span>
                                <?php _e('Licenses', 'digiplanet-digital-products'); ?>
                            </a>
                        </li>
                        <li>
                            <a href="<?php echo $this->get_account_url('downloads'); ?>" class="<?php echo $current_page === 'downloads' ? 'active' : ''; ?>">
                                <span class="digiplanet-nav-icon dashicons dashicons-download"></span>
                                <?php _e('Downloads', 'digiplanet-digital-products'); ?>
                            </a>
                        </li>
                    <?php endif; ?>
                    
                    <?php if (in_array('software_client', $user->roles) || in_array('administrator', $user->roles)): ?>
                        <li>
                            <a href="<?php echo $this->get_account_url('projects'); ?>" class="<?php echo $current_page === 'projects' ? 'active' : ''; ?>">
                                <span class="digiplanet-nav-icon dashicons dashicons-portfolio"></span>
                                <?php _e('Projects', 'digiplanet-digital-products'); ?>
                            </a>
                        </li>
                        <li>
                            <a href="<?php echo $this->get_account_url('support'); ?>" class="<?php echo $current_page === 'support' ? 'active' : ''; ?>">
                                <span class="digiplanet-nav-icon dashicons dashicons-sos"></span>
                                <?php _e('Support', 'digiplanet-digital-products'); ?>
                            </a>
                        </li>
                        <li>
                            <a href="<?php echo $this->get_account_url('documents'); ?>" class="<?php echo $current_page === 'documents' ? 'active' : ''; ?>">
                                <span class="digiplanet-nav-icon dashicons dashicons-media-document"></span>
                                <?php _e('Documents', 'digiplanet-digital-products'); ?>
                            </a>
                        </li>
                    <?php endif; ?>
                    
                    <li>
                        <a href="<?php echo $this->get_account_url('settings'); ?>" class="<?php echo $current_page === 'settings' ? 'active' : ''; ?>">
                            <span class="digiplanet-nav-icon dashicons dashicons-admin-generic"></span>
                            <?php _e('Settings', 'digiplanet-digital-products'); ?>
                        </a>
                    </li>
                    
                    <li class="digiplanet-logout">
                        <a href="<?php echo wp_logout_url(home_url()); ?>">
                            <span class="digiplanet-nav-icon dashicons dashicons-exit"></span>
                            <?php _e('Logout', 'digiplanet-digital-products'); ?>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
        <?php
    }
    
    /**
     * Display account content
     */
    private function display_account_content($user, $page) {
        switch ($page) {
            case 'dashboard':
                if (in_array('digital_customer', $user->roles)) {
                    include DIGIPLANET_PLUGIN_DIR . 'modules/user-portal/templates/digital-customer-dashboard.php';
                } elseif (in_array('software_client', $user->roles)) {
                    include DIGIPLANET_PLUGIN_DIR . 'modules/user-portal/templates/software-client-dashboard.php';
                }
                break;
                
            case 'products':
                include DIGIPLANET_PLUGIN_DIR . 'modules/user-portal/templates/my-products.php';
                break;
                
            case 'orders':
                $this->display_orders_page($user);
                break;
                
            case 'licenses':
                include DIGIPLANET_PLUGIN_DIR . 'modules/user-portal/templates/my-licenses.php';
                break;
                
            case 'settings':
                include DIGIPLANET_PLUGIN_DIR . 'modules/user-portal/templates/account-settings.php';
                break;
                
            default:
                echo '<div class="digiplanet-alert digiplanet-alert-info">';
                echo __('Page not found.', 'digiplanet-digital-products');
                echo '</div>';
        }
    }
    
    /**
     * Display orders page
     */
    private function display_orders_page($user) {
        $order_manager = Digiplanet_Order_Manager::get_instance();
        $orders = $order_manager->get_customer_orders($user->ID, 20);
        ?>
        <div class="digiplanet-account-orders">
            <h2><?php _e('My Orders', 'digiplanet-digital-products'); ?></h2>
            
            <?php if (empty($orders)): ?>
                <div class="digiplanet-no-orders">
                    <p><?php _e('You have not placed any orders yet.', 'digiplanet-digital-products'); ?></p>
                    <a href="<?php echo get_permalink(get_option('digiplanet_products_page_id')); ?>" class="digiplanet-btn digiplanet-btn-primary">
                        <?php _e('Browse Products', 'digiplanet-digital-products'); ?>
                    </a>
                </div>
            <?php else: ?>
                <div class="digiplanet-orders-table">
                    <table>
                        <thead>
                            <tr>
                                <th><?php _e('Order', 'digiplanet-digital-products'); ?></th>
                                <th><?php _e('Date', 'digiplanet-digital-products'); ?></th>
                                <th><?php _e('Status', 'digiplanet-digital-products'); ?></th>
                                <th><?php _e('Payment', 'digiplanet-digital-products'); ?></th>
                                <th><?php _e('Total', 'digiplanet-digital-products'); ?></th>
                                <th><?php _e('Actions', 'digiplanet-digital-products'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($orders as $order): ?>
                                <tr>
                                    <td>#<?php echo esc_html($order->order_number); ?></td>
                                    <td><?php echo date_i18n(get_option('date_format'), strtotime($order->created_at)); ?></td>
                                    <td><?php echo $order_manager->get_order_status_badge($order->status); ?></td>
                                    <td><?php echo $order_manager->get_payment_status_badge($order->payment_status); ?></td>
                                    <td><?php echo Digiplanet_Product_Manager::get_instance()->format_price($order->total_amount); ?></td>
                                    <td>
                                        <a href="<?php echo $this->get_account_url('orders') . '?view=' . $order->id; ?>" class="digiplanet-btn digiplanet-btn-sm">
                                            <?php _e('View', 'digiplanet-digital-products'); ?>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
        <?php
    }
    
    /**
     * Get user role label
     */
    private function get_user_role_label($user) {
        $roles = [
            'digital_customer' => __('Digital Customer', 'digiplanet-digital-products'),
            'software_client' => __('Software Client', 'digiplanet-digital-products'),
            'administrator' => __('Administrator', 'digiplanet-digital-products'),
        ];
        
        $user_roles = $user->roles;
        $primary_role = reset($user_roles);
        
        return $roles[$primary_role] ?? __('Customer', 'digiplanet-digital-products');
    }
    
    /**
     * Get account URL
     */
    public function get_account_url($page = 'dashboard') {
        return home_url('/account/' . $page . '/');
    }
    
    /**
     * Enqueue portal assets
     */
    public function enqueue_portal_assets() {
        if (get_query_var('digiplanet_account_page')) {
            wp_enqueue_style(
                'digiplanet-portal',
                DIGIPLANET_PLUGIN_URL . 'modules/user-portal/assets/css/portal.css',
                [],
                DIGIPLANET_VERSION
            );
            
            wp_enqueue_script(
                'digiplanet-portal',
                DIGIPLANET_PLUGIN_URL . 'modules/user-portal/assets/js/portal.js',
                ['jquery'],
                DIGIPLANET_VERSION,
                true
            );
        }
    }
}