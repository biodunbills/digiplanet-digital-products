<?php
/**
 * Admin Dashboard View
 */

if (!defined('ABSPATH')) {
    exit;
}

// Get analytics data
$analytics = Digiplanet_Analytics::get_instance()->get_dashboard_stats();
$recent_orders = Digiplanet_Order_Manager::get_instance()->get_orders([], 5, 0);
$recent_products = Digiplanet_Product_Manager::get_instance()->get_products([], 5, 0);
$recent_customers = Digiplanet_Account_Manager::get_instance()->get_recent_customers(5);
?>

<div class="wrap digiplanet-dashboard">
    <h1><?php _e('Digiplanet Dashboard', 'digiplanet-digital-products'); ?></h1>
    
    <!-- Stats Overview -->
    <div class="digiplanet-stats-grid">
        <div class="digiplanet-stat-card">
            <div class="digiplanet-stat-icon">
                <span class="dashicons dashicons-chart-line"></span>
            </div>
            <div class="digiplanet-stat-content">
                <h3><?php echo esc_html($analytics['total_revenue']); ?></h3>
                <p><?php _e('Total Revenue', 'digiplanet-digital-products'); ?></p>
            </div>
        </div>
        
        <div class="digiplanet-stat-card">
            <div class="digiplanet-stat-icon">
                <span class="dashicons dashicons-cart"></span>
            </div>
            <div class="digiplanet-stat-content">
                <h3><?php echo esc_html($analytics['total_orders']); ?></h3>
                <p><?php _e('Total Orders', 'digiplanet-digital-products'); ?></p>
            </div>
        </div>
        
        <div class="digiplanet-stat-card">
            <div class="digiplanet-stat-icon">
                <span class="dashicons dashicons-products"></span>
            </div>
            <div class="digiplanet-stat-content">
                <h3><?php echo esc_html($analytics['total_products']); ?></h3>
                <p><?php _e('Products', 'digiplanet-digital-products'); ?></p>
            </div>
        </div>
        
        <div class="digiplanet-stat-card">
            <div class="digiplanet-stat-icon">
                <span class="dashicons dashicons-admin-users"></span>
            </div>
            <div class="digiplanet-stat-content">
                <h3><?php echo esc_html($analytics['total_customers']); ?></h3>
                <p><?php _e('Customers', 'digiplanet-digital-products'); ?></p>
            </div>
        </div>
    </div>
    
    <!-- Main Content Area -->
    <div class="digiplanet-dashboard-main">
        <div class="digiplanet-dashboard-row">
            <!-- Recent Orders -->
            <div class="digiplanet-dashboard-column">
                <div class="digiplanet-card">
                    <div class="digiplanet-card-header">
                        <h3><?php _e('Recent Orders', 'digiplanet-digital-products'); ?></h3>
                        <a href="<?php echo admin_url('admin.php?page=digiplanet-orders'); ?>" class="digiplanet-view-all">
                            <?php _e('View All', 'digiplanet-digital-products'); ?>
                        </a>
                    </div>
                    <div class="digiplanet-card-body">
                        <?php if (!empty($recent_orders)): ?>
                            <table class="digiplanet-table">
                                <thead>
                                    <tr>
                                        <th><?php _e('Order', 'digiplanet-digital-products'); ?></th>
                                        <th><?php _e('Customer', 'digiplanet-digital-products'); ?></th>
                                        <th><?php _e('Status', 'digiplanet-digital-products'); ?></th>
                                        <th><?php _e('Total', 'digiplanet-digital-products'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recent_orders as $order): ?>
                                        <tr>
                                            <td>
                                                <a href="<?php echo admin_url('admin.php?page=digiplanet-orders&action=view&order_id=' . $order->id); ?>">
                                                    #<?php echo esc_html($order->order_number); ?>
                                                </a>
                                            </td>
                                            <td><?php echo esc_html($order->customer_name); ?></td>
                                            <td>
                                                <span class="digiplanet-status-badge digiplanet-status-<?php echo esc_attr($order->status); ?>">
                                                    <?php echo ucfirst($order->status); ?>
                                                </span>
                                            </td>
                                            <td><?php echo Digiplanet_Product_Manager::get_instance()->format_price($order->total_amount); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php else: ?>
                            <p><?php _e('No orders found.', 'digiplanet-digital-products'); ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Recent Products -->
            <div class="digiplanet-dashboard-column">
                <div class="digiplanet-card">
                    <div class="digiplanet-card-header">
                        <h3><?php _e('Recent Products', 'digiplanet-digital-products'); ?></h3>
                        <a href="<?php echo admin_url('admin.php?page=digiplanet-products'); ?>" class="digiplanet-view-all">
                            <?php _e('View All', 'digiplanet-digital-products'); ?>
                        </a>
                    </div>
                    <div class="digiplanet-card-body">
                        <?php if (!empty($recent_products)): ?>
                            <div class="digiplanet-products-list">
                                <?php foreach ($recent_products as $product): ?>
                                    <div class="digiplanet-product-item">
                                        <div class="digiplanet-product-image">
                                            <?php if ($product->featured_image_id): ?>
                                                <?php echo wp_get_attachment_image($product->featured_image_id, 'thumbnail'); ?>
                                            <?php else: ?>
                                                <div class="digiplanet-product-placeholder">
                                                    <span class="dashicons dashicons-format-image"></span>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="digiplanet-product-info">
                                            <h4>
                                                <a href="<?php echo admin_url('admin.php?page=digiplanet-products&action=edit&product_id=' . $product->id); ?>">
                                                    <?php echo esc_html($product->name); ?>
                                                </a>
                                            </h4>
                                            <p class="digiplanet-product-price">
                                                <?php echo Digiplanet_Product_Manager::get_instance()->format_price($product->price); ?>
                                            </p>
                                            <div class="digiplanet-product-meta">
                                                <span class="digiplanet-product-sales">
                                                    <span class="dashicons dashicons-chart-bar"></span>
                                                    <?php echo esc_html($product->sales_count); ?> <?php _e('sales', 'digiplanet-digital-products'); ?>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p><?php _e('No products found.', 'digiplanet-digital-products'); ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Recent Customers -->
        <div class="digiplanet-dashboard-row">
            <div class="digiplanet-dashboard-column">
                <div class="digiplanet-card">
                    <div class="digiplanet-card-header">
                        <h3><?php _e('Recent Customers', 'digiplanet-digital-products'); ?></h3>
                        <a href="<?php echo admin_url('admin.php?page=digiplanet-customers'); ?>" class="digiplanet-view-all">
                            <?php _e('View All', 'digiplanet-digital-products'); ?>
                        </a>
                    </div>
                    <div class="digiplanet-card-body">
                        <?php if (!empty($recent_customers)): ?>
                            <table class="digiplanet-table">
                                <thead>
                                    <tr>
                                        <th><?php _e('Customer', 'digiplanet-digital-products'); ?></th>
                                        <th><?php _e('Email', 'digiplanet-digital-products'); ?></th>
                                        <th><?php _e('Role', 'digiplanet-digital-products'); ?></th>
                                        <th><?php _e('Orders', 'digiplanet-digital-products'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recent_customers as $customer): ?>
                                        <tr>
                                            <td><?php echo esc_html($customer->display_name); ?></td>
                                            <td><?php echo esc_html($customer->user_email); ?></td>
                                            <td>
                                                <?php
                                                $roles = [
                                                    'digital_customer' => __('Digital Customer', 'digiplanet-digital-products'),
                                                    'software_client' => __('Software Client', 'digiplanet-digital-products'),
                                                ];
                                                $primary_role = reset($customer->roles);
                                                echo esc_html($roles[$primary_role] ?? $primary_role);
                                                ?>
                                            </td>
                                            <td>
                                                <?php
                                                $order_count = Digiplanet_Order_Manager::get_instance()->get_customer_order_count($customer->ID);
                                                echo esc_html($order_count);
                                                ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php else: ?>
                            <p><?php _e('No customers found.', 'digiplanet-digital-products'); ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.digiplanet-dashboard {
    padding: 20px;
}

.digiplanet-stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.digiplanet-stat-card {
    background: white;
    border: 1px solid #ccd0d4;
    border-radius: 4px;
    padding: 20px;
    display: flex;
    align-items: center;
    gap: 15px;
}

.digiplanet-stat-icon {
    background: #2271b1;
    color: white;
    width: 50px;
    height: 50px;
    border-radius: 4px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.digiplanet-stat-icon .dashicons {
    font-size: 24px;
    width: 24px;
    height: 24px;
}

.digiplanet-stat-content h3 {
    margin: 0 0 5px 0;
    font-size: 24px;
    color: #1d2327;
}

.digiplanet-stat-content p {
    margin: 0;
    color: #646970;
}

.digiplanet-dashboard-main {
    background: white;
    border: 1px solid #ccd0d4;
    border-radius: 4px;
    padding: 20px;
}

.digiplanet-dashboard-row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 20px;
    margin-bottom: 20px;
}

.digiplanet-card {
    background: #f6f7f7;
    border: 1px solid #dcdcde;
    border-radius: 4px;
}

.digiplanet-card-header {
    padding: 15px 20px;
    border-bottom: 1px solid #dcdcde;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.digiplanet-card-header h3 {
    margin: 0;
}

.digiplanet-view-all {
    color: #2271b1;
    text-decoration: none;
    font-size: 13px;
}

.digiplanet-view-all:hover {
    text-decoration: underline;
}

.digiplanet-card-body {
    padding: 20px;
}

.digiplanet-table {
    width: 100%;
    border-collapse: collapse;
}

.digiplanet-table th,
.digiplanet-table td {
    padding: 12px;
    text-align: left;
    border-bottom: 1px solid #dcdcde;
}

.digiplanet-table th {
    font-weight: 600;
    background: #f0f0f1;
}

.digiplanet-status-badge {
    display: inline-block;
    padding: 4px 8px;
    border-radius: 3px;
    font-size: 12px;
    font-weight: 500;
    text-transform: uppercase;
}

.digiplanet-status-pending {
    background: #f0f0f0;
    color: #50575e;
}

.digiplanet-status-processing {
    background: #f0f6fc;
    color: #2271b1;
}

.digiplanet-status-completed {
    background: #edf7ed;
    color: #1e4620;
}

.digiplanet-status-cancelled {
    background: #f7eded;
    color: #5c1d1d;
}

.digiplanet-products-list {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.digiplanet-product-item {
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 15px;
    background: white;
    border: 1px solid #dcdcde;
    border-radius: 4px;
}

.digiplanet-product-image {
    width: 50px;
    height: 50px;
    flex-shrink: 0;
}

.digiplanet-product-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 4px;
}

.digiplanet-product-placeholder {
    width: 100%;
    height: 100%;
    background: #f0f0f1;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 4px;
}

.digiplanet-product-placeholder .dashicons {
    color: #646970;
}

.digiplanet-product-info h4 {
    margin: 0 0 5px 0;
}

.digiplanet-product-info h4 a {
    text-decoration: none;
    color: #2271b1;
}

.digiplanet-product-info h4 a:hover {
    text-decoration: underline;
}

.digiplanet-product-price {
    color: #1d2327;
    font-weight: 600;
    margin: 0 0 8px 0;
}

.digiplanet-product-meta {
    display: flex;
    gap: 15px;
    font-size: 12px;
    color: #646970;
}

.digiplanet-product-sales .dashicons {
    font-size: 12px;
    vertical-align: middle;
}
</style>