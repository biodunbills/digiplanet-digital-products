<?php
/**
 * Digital Customer Portal
 */

if (!defined('ABSPATH')) {
    exit;
}

class Digiplanet_Digital_Customer {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        add_action('init', [$this, 'init']);
    }
    
    public function init() {
        // Register custom user role
        $this->register_digital_customer_role();
    }
    
    private function register_digital_customer_role() {
        $capabilities = [
            'read' => true,
            'edit_posts' => false,
            'delete_posts' => false,
            'upload_files' => false,
            'digiplanet_view_products' => true,
            'digiplanet_purchase_products' => true,
            'digiplanet_view_orders' => true,
            'digiplanet_view_licenses' => true,
            'digiplanet_download_products' => true,
            'digiplanet_submit_reviews' => true,
        ];
        
        add_role('digital_customer', __('Digital Customer', 'digiplanet-digital-products'), $capabilities);
    }
    
    public function get_dashboard_content($user_id) {
        $account_manager = Digiplanet_Account_Manager::get_instance();
        $order_manager = Digiplanet_Order_Manager::get_instance();
        
        $stats = $account_manager->get_customer_stats($user_id);
        $recent_orders = $order_manager->get_customer_orders($user_id, 5);
        $recent_products = $account_manager->get_customer_products($user_id, 5);
        $active_licenses = $account_manager->get_customer_licenses($user_id);
        
        ob_start();
        ?>
        <div class="digiplanet-dashboard">
            <h2><?php _e('Dashboard', 'digiplanet-digital-products'); ?></h2>
            
            <!-- Stats Overview -->
            <div class="digiplanet-dashboard-stats">
                <div class="digiplanet-stat-card">
                    <div class="digiplanet-stat-icon">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <div class="digiplanet-stat-content">
                        <h3><?php echo number_format($stats['total_orders']); ?></h3>
                        <p><?php _e('Total Orders', 'digiplanet-digital-products'); ?></p>
                    </div>
                </div>
                
                <div class="digiplanet-stat-card">
                    <div class="digiplanet-stat-icon">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                    <div class="digiplanet-stat-content">
                        <h3><?php echo Digiplanet_Product_Manager::get_instance()->format_price($stats['total_spent']); ?></h3>
                        <p><?php _e('Total Spent', 'digiplanet-digital-products'); ?></p>
                    </div>
                </div>
                
                <div class="digiplanet-stat-card">
                    <div class="digiplanet-stat-icon">
                        <i class="fas fa-box-open"></i>
                    </div>
                    <div class="digiplanet-stat-content">
                        <h3><?php echo number_format($stats['total_products']); ?></h3>
                        <p><?php _e('Products Purchased', 'digiplanet-digital-products'); ?></p>
                    </div>
                </div>
                
                <div class="digiplanet-stat-card">
                    <div class="digiplanet-stat-icon">
                        <i class="fas fa-key"></i>
                    </div>
                    <div class="digiplanet-stat-content">
                        <h3><?php echo number_format($stats['active_licenses']); ?></h3>
                        <p><?php _e('Active Licenses', 'digiplanet-digital-products'); ?></p>
                    </div>
                </div>
            </div>
            
            <div class="digiplanet-dashboard-content">
                <!-- Recent Orders -->
                <div class="digiplanet-dashboard-section">
                    <h3><?php _e('Recent Orders', 'digiplanet-digital-products'); ?></h3>
                    <?php if (!empty($recent_orders)): ?>
                        <div class="digiplanet-orders-list">
                            <table>
                                <thead>
                                    <tr>
                                        <th><?php _e('Order', 'digiplanet-digital-products'); ?></th>
                                        <th><?php _e('Date', 'digiplanet-digital-products'); ?></th>
                                        <th><?php _e('Status', 'digiplanet-digital-products'); ?></th>
                                        <th><?php _e('Total', 'digiplanet-digital-products'); ?></th>
                                        <th><?php _e('Actions', 'digiplanet-digital-products'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recent_orders as $order): ?>
                                        <tr>
                                            <td>#<?php echo esc_html($order->order_number); ?></td>
                                            <td><?php echo date_i18n(get_option('date_format'), strtotime($order->created_at)); ?></td>
                                            <td>
                                                <span class="digiplanet-status-badge digiplanet-status-<?php echo $order->status; ?>">
                                                    <?php echo ucfirst($order->status); ?>
                                                </span>
                                            </td>
                                            <td><?php echo Digiplanet_Product_Manager::get_instance()->format_price($order->total_amount); ?></td>
                                            <td>
                                                <a href="<?php echo Digiplanet_User_Portal::get_instance()->get_account_url('orders') . '?view=' . $order->id; ?>" class="digiplanet-btn digiplanet-btn-sm">
                                                    <?php _e('View', 'digiplanet-digital-products'); ?>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <a href="<?php echo Digiplanet_User_Portal::get_instance()->get_account_url('orders'); ?>" class="digiplanet-view-all">
                            <?php _e('View All Orders', 'digiplanet-digital-products'); ?>
                        </a>
                    <?php else: ?>
                        <p class="digiplanet-no-data"><?php _e('No orders found.', 'digiplanet-digital-products'); ?></p>
                    <?php endif; ?>
                </div>
                
                <!-- Recent Products -->
                <div class="digiplanet-dashboard-section">
                    <h3><?php _e('Recent Products', 'digiplanet-digital-products'); ?></h3>
                    <?php if (!empty($recent_products)): ?>
                        <div class="digiplanet-products-grid">
                            <?php foreach ($recent_products as $product): ?>
                                <div class="digiplanet-product-card">
                                    <div class="digiplanet-product-image">
                                        <?php
                                        $image_id = get_post_thumbnail_id($product->ID);
                                        if ($image_id) {
                                            echo wp_get_attachment_image($image_id, 'thumbnail');
                                        } else {
                                            echo '<img src="' . DIGIPLANET_ASSETS_URL . 'images/placeholder-product.png" alt="' . esc_attr($product->post_title) . '">';
                                        }
                                        ?>
                                    </div>
                                    <div class="digiplanet-product-content">
                                        <h4><?php echo esc_html($product->post_title); ?></h4>
                                        <div class="digiplanet-product-meta">
                                            <?php if (isset($product->license_key)): ?>
                                                <div class="digiplanet-license-info">
                                                    <i class="fas fa-key"></i>
                                                    <span><?php echo substr($product->license_key, 0, 8) . '...'; ?></span>
                                                </div>
                                            <?php endif; ?>
                                            <?php if (isset($product->download_expires) && $product->download_expires): ?>
                                                <div class="digiplanet-download-expiry">
                                                    <i class="fas fa-clock"></i>
                                                    <span><?php 
                                                        $expiry_date = strtotime($product->download_expires);
                                                        $days_left = ceil(($expiry_date - time()) / DAY_IN_SECONDS);
                                                        if ($days_left > 0) {
                                                            printf(__('Expires in %d days', 'digiplanet-digital-products'), $days_left);
                                                        } else {
                                                            _e('Expired', 'digiplanet-digital-products');
                                                        }
                                                    ?></span>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <a href="<?php echo get_permalink($product->ID); ?>" class="digiplanet-btn digiplanet-btn-sm">
                                            <?php _e('View Product', 'digiplanet-digital-products'); ?>
                                        </a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <a href="<?php echo Digiplanet_User_Portal::get_instance()->get_account_url('products'); ?>" class="digiplanet-view-all">
                            <?php _e('View All Products', 'digiplanet-digital-products'); ?>
                        </a>
                    <?php else: ?>
                        <p class="digiplanet-no-data"><?php _e('No products purchased yet.', 'digiplanet-digital-products'); ?></p>
                    <?php endif; ?>
                </div>
                
                <!-- Active Licenses -->
                <div class="digiplanet-dashboard-section">
                    <h3><?php _e('Active Licenses', 'digiplanet-digital-products'); ?></h3>
                    <?php if (!empty($active_licenses)): ?>
                        <div class="digiplanet-licenses-list">
                            <table>
                                <thead>
                                    <tr>
                                        <th><?php _e('Product', 'digiplanet-digital-products'); ?></th>
                                        <th><?php _e('License Key', 'digiplanet-digital-products'); ?></th>
                                        <th><?php _e('Status', 'digiplanet-digital-products'); ?></th>
                                        <th><?php _e('Activations', 'digiplanet-digital-products'); ?></th>
                                        <th><?php _e('Expiry', 'digiplanet-digital-products'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($active_licenses as $license): ?>
                                        <tr>
                                            <td><?php echo esc_html($license->product_name); ?></td>
                                            <td>
                                                <code class="digiplanet-license-key"><?php echo esc_html($license->license_key); ?></code>
                                                <button class="digiplanet-copy-license" data-license="<?php echo esc_attr($license->license_key); ?>">
                                                    <i class="fas fa-copy"></i>
                                                </button>
                                            </td>
                                            <td>
                                                <span class="digiplanet-status-badge digiplanet-status-active">
                                                    <?php echo ucfirst($license->status); ?>
                                                </span>
                                            </td>
                                            <td><?php echo $license->activation_count . '/' . $license->max_activations; ?></td>
                                            <td>
                                                <?php if ($license->expires_at): ?>
                                                    <?php echo date_i18n(get_option('date_format'), strtotime($license->expires_at)); ?>
                                                <?php else: ?>
                                                    <?php _e('Never', 'digiplanet-digital-products'); ?>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <a href="<?php echo Digiplanet_User_Portal::get_instance()->get_account_url('licenses'); ?>" class="digiplanet-view-all">
                            <?php _e('View All Licenses', 'digiplanet-digital-products'); ?>
                        </a>
                    <?php else: ?>
                        <p class="digiplanet-no-data"><?php _e('No active licenses found.', 'digiplanet-digital-products'); ?></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
    
    public function get_my_products_content($user_id) {
        $account_manager = Digiplanet_Account_Manager::get_instance();
        $products = $account_manager->get_customer_products($user_id, 20);
        
        ob_start();
        ?>
        <div class="digiplanet-my-products">
            <h2><?php _e('My Products', 'digiplanet-digital-products'); ?></h2>
            
            <?php if (!empty($products)): ?>
                <div class="digiplanet-products-grid">
                    <?php foreach ($products as $product): ?>
                        <div class="digiplanet-product-card">
                            <div class="digiplanet-product-image">
                                <?php
                                $image_id = get_post_thumbnail_id($product->ID);
                                if ($image_id) {
                                    echo wp_get_attachment_image($image_id, 'medium');
                                } else {
                                    echo '<img src="' . DIGIPLANET_ASSETS_URL . 'images/placeholder-product.png" alt="' . esc_attr($product->post_title) . '">';
                                }
                                ?>
                            </div>
                            
                            <div class="digiplanet-product-content">
                                <h3><?php echo esc_html($product->post_title); ?></h3>
                                
                                <div class="digiplanet-product-meta">
                                    <?php if (isset($product->version)): ?>
                                        <span class="digiplanet-product-version">
                                            <i class="fas fa-code-branch"></i>
                                            <?php echo esc_html($product->version); ?>
                                        </span>
                                    <?php endif; ?>
                                    
                                    <?php if (isset($product->file_size)): ?>
                                        <span class="digiplanet-product-size">
                                            <i class="fas fa-hdd"></i>
                                            <?php echo esc_html($product->file_size); ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                                
                                <?php if (isset($product->license_key)): ?>
                                    <div class="digiplanet-license-info">
                                        <h4><?php _e('License Key:', 'digiplanet-digital-products'); ?></h4>
                                        <div class="digiplanet-license-display">
                                            <code class="digiplanet-license-key"><?php echo esc_html($product->license_key); ?></code>
                                            <button class="digiplanet-copy-license" data-license="<?php echo esc_attr($product->license_key); ?>">
                                                <i class="fas fa-copy"></i>
                                                <?php _e('Copy', 'digiplanet-digital-products'); ?>
                                            </button>
                                        </div>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="digiplanet-product-actions">
                                    <?php
                                    $download_link = Digiplanet_Download_Manager::get_instance()->get_download_link($product->ID, $user_id);
                                    if ($download_link):
                                    ?>
                                        <a href="<?php echo esc_url($download_link); ?>" class="digiplanet-btn digiplanet-btn-primary">
                                            <i class="fas fa-download"></i>
                                            <?php _e('Download', 'digiplanet-digital-products'); ?>
                                        </a>
                                    <?php endif; ?>
                                    
                                    <a href="<?php echo get_permalink($product->ID); ?>" class="digiplanet-btn digiplanet-btn-secondary">
                                        <i class="fas fa-external-link-alt"></i>
                                        <?php _e('View Details', 'digiplanet-digital-products'); ?>
                                    </a>
                                </div>
                                
                                <?php if (isset($product->download_expires) && $product->download_expires): ?>
                                    <div class="digiplanet-download-info">
                                        <p>
                                            <i class="fas fa-clock"></i>
                                            <?php 
                                                $expiry_date = strtotime($product->download_expires);
                                                $days_left = ceil(($expiry_date - time()) / DAY_IN_SECONDS);
                                                if ($days_left > 0) {
                                                    printf(__('Download link expires in %d days', 'digiplanet-digital-products'), $days_left);
                                                } elseif ($days_left == 0) {
                                                    _e('Download link expires today', 'digiplanet-digital-products');
                                                } else {
                                                    _e('Download link has expired', 'digiplanet-digital-products');
                                                }
                                            ?>
                                        </p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="digiplanet-no-products">
                    <i class="fas fa-box-open"></i>
                    <h3><?php _e('No Products Yet', 'digiplanet-digital-products'); ?></h3>
                    <p><?php _e('You haven\'t purchased any digital products yet.', 'digiplanet-digital-products'); ?></p>
                    <a href="<?php echo get_post_type_archive_link('digiplanet_product'); ?>" class="digiplanet-btn digiplanet-btn-primary">
                        <?php _e('Browse Products', 'digiplanet-digital-products'); ?>
                    </a>
                </div>
            <?php endif; ?>
        </div>
        <?php
        return ob_get_clean();
    }
}