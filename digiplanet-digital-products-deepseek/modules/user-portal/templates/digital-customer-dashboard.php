<?php
/**
 * Digital Customer Dashboard Template
 */

if (!defined('ABSPATH')) {
    exit;
}

$user = wp_get_current_user();
$account_manager = Digiplanet_Account_Manager::get_instance();
$stats = $account_manager->get_customer_stats($user->ID);
$recent_products = $account_manager->get_customer_products($user->ID, 5, 0);
$recent_licenses = $account_manager->get_customer_licenses($user->ID);
?>

<div class="digiplanet-customer-dashboard">
    <!-- Welcome Section -->
    <div class="digiplanet-welcome-section">
        <h1><?php printf(__('Welcome back, %s!', 'digiplanet-digital-products'), esc_html($user->display_name)); ?></h1>
        <p><?php _e('Here\'s what\'s happening with your digital products account.', 'digiplanet-digital-products'); ?></p>
    </div>
    
    <!-- Stats Overview -->
    <div class="digiplanet-stats-grid">
        <div class="digiplanet-stat-card">
            <div class="digiplanet-stat-icon">
                <span class="dashicons dashicons-cart"></span>
            </div>
            <div class="digiplanet-stat-content">
                <h3><?php echo esc_html($stats['total_orders']); ?></h3>
                <p><?php _e('Total Orders', 'digiplanet-digital-products'); ?></p>
            </div>
        </div>
        
        <div class="digiplanet-stat-card">
            <div class="digiplanet-stat-icon">
                <span class="dashicons dashicons-products"></span>
            </div>
            <div class="digiplanet-stat-content">
                <h3><?php echo esc_html($stats['total_products']); ?></h3>
                <p><?php _e('Purchased Products', 'digiplanet-digital-products'); ?></p>
            </div>
        </div>
        
        <div class="digiplanet-stat-card">
            <div class="digiplanet-stat-icon">
                <span class="dashicons dashicons-lock"></span>
            </div>
            <div class="digiplanet-stat-content">
                <h3><?php echo esc_html($stats['active_licenses']); ?></h3>
                <p><?php _e('Active Licenses', 'digiplanet-digital-products'); ?></p>
            </div>
        </div>
        
        <div class="digiplanet-stat-card">
            <div class="digiplanet-stat-icon">
                <span class="dashicons dashicons-money-alt"></span>
            </div>
            <div class="digiplanet-stat-content">
                <h3><?php echo Digiplanet_Product_Manager::get_instance()->format_price($stats['total_spent']); ?></h3>
                <p><?php _e('Total Spent', 'digiplanet-digital-products'); ?></p>
            </div>
        </div>
    </div>
    
    <!-- Main Content Area -->
    <div class="digiplanet-dashboard-content">
        <!-- Recent Products -->
        <div class="digiplanet-dashboard-section">
            <div class="digiplanet-section-header">
                <h2><?php _e('Recent Products', 'digiplanet-digital-products'); ?></h2>
                <a href="<?php echo Digiplanet_User_Portal::get_instance()->get_account_url('products'); ?>" class="digiplanet-view-all">
                    <?php _e('View All Products', 'digiplanet-digital-products'); ?>
                </a>
            </div>
            
            <?php if (!empty($recent_products)): ?>
                <div class="digiplanet-products-grid">
                    <?php foreach ($recent_products as $product): ?>
                        <div class="digiplanet-product-card">
                            <div class="digiplanet-product-image">
                                <?php if ($product->featured_image_id): ?>
                                    <?php echo wp_get_attachment_image($product->featured_image_id, 'medium'); ?>
                                <?php else: ?>
                                    <div class="digiplanet-product-placeholder">
                                        <span class="dashicons dashicons-format-image"></span>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="digiplanet-product-content">
                                <h3><?php echo esc_html($product->name); ?></h3>
                                <div class="digiplanet-product-meta">
                                    <?php if ($product->license_key): ?>
                                        <span class="digiplanet-license-status active">
                                            <span class="dashicons dashicons-yes"></span>
                                            <?php _e('Licensed', 'digiplanet-digital-products'); ?>
                                        </span>
                                    <?php endif; ?>
                                    
                                    <?php if ($product->download_expires && strtotime($product->download_expires) > time()): ?>
                                        <span class="digiplanet-download-expiry">
                                            <span class="dashicons dashicons-clock"></span>
                                            <?php 
                                            printf(
                                                __('Expires in %s days', 'digiplanet-digital-products'),
                                                ceil((strtotime($product->download_expires) - time()) / DAY_IN_SECONDS)
                                            );
                                            ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                                <div class="digiplanet-product-actions">
                                    <?php
                                    $download_manager = Digiplanet_Download_Manager::get_instance();
                                    $download_url = $download_manager->get_download_url($product->id, $product->license_key);
                                    ?>
                                    <a href="<?php echo esc_url($download_url); ?>" class="digiplanet-btn digiplanet-btn-primary">
                                        <span class="dashicons dashicons-download"></span>
                                        <?php _e('Download', 'digiplanet-digital-products'); ?>
                                    </a>
                                    
                                    <?php if ($product->license_key): ?>
                                        <a href="<?php echo Digiplanet_User_Portal::get_instance()->get_account_url('licenses'); ?>" class="digiplanet-btn digiplanet-btn-secondary">
                                            <span class="dashicons dashicons-lock"></span>
                                            <?php _e('View License', 'digiplanet-digital-products'); ?>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="digiplanet-empty-state">
                    <div class="digiplanet-empty-icon">
                        <span class="dashicons dashicons-cart"></span>
                    </div>
                    <h3><?php _e('No Products Yet', 'digiplanet-digital-products'); ?></h3>
                    <p><?php _e('You haven\'t purchased any digital products yet.', 'digiplanet-digital-products'); ?></p>
                    <a href="<?php echo get_permalink(get_option('digiplanet_products_page_id')); ?>" class="digiplanet-btn digiplanet-btn-primary">
                        <?php _e('Browse Products', 'digiplanet-digital-products'); ?>
                    </a>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Recent Licenses -->
        <div class="digiplanet-dashboard-section">
            <div class="digiplanet-section-header">
                <h2><?php _e('Active Licenses', 'digiplanet-digital-products'); ?></h2>
                <a href="<?php echo Digiplanet_User_Portal::get_instance()->get_account_url('licenses'); ?>" class="digiplanet-view-all">
                    <?php _e('View All Licenses', 'digiplanet-digital-products'); ?>
                </a>
            </div>
            
            <?php if (!empty($recent_licenses)): ?>
                <div class="digiplanet-licenses-table">
                    <table>
                        <thead>
                            <tr>
                                <th><?php _e('Product', 'digiplanet-digital-products'); ?></th>
                                <th><?php _e('License Key', 'digiplanet-digital-products'); ?></th>
                                <th><?php _e('Status', 'digiplanet-digital-products'); ?></th>
                                <th><?php _e('Activations', 'digiplanet-digital-products'); ?></th>
                                <th><?php _e('Expires', 'digiplanet-digital-products'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_licenses as $license): ?>
                                <tr>
                                    <td><?php echo esc_html($license->product_name); ?></td>
                                    <td class="digiplanet-license-key">
                                        <code><?php echo esc_html($license->license_key); ?></code>
                                        <button class="digiplanet-copy-btn" data-clipboard-text="<?php echo esc_attr($license->license_key); ?>">
                                            <span class="dashicons dashicons-admin-page"></span>
                                        </button>
                                    </td>
                                    <td>
                                        <span class="digiplanet-license-status active">
                                            <?php echo ucfirst($license->status); ?>
                                        </span>
                                    </td>
                                    <td><?php echo esc_html($license->activation_count) . '/' . esc_html($license->max_activations); ?></td>
                                    <td>
                                        <?php if ($license->expires_at): ?>
                                            <?php echo date_i18n(get_option('date_format'), strtotime($license->expires_at)); ?>
                                        <?php else: ?>
                                            <?php _e('Lifetime', 'digiplanet-digital-products'); ?>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="digiplanet-empty-state">
                    <p><?php _e('No active licenses found.', 'digiplanet-digital-products'); ?></p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.digiplanet-customer-dashboard {
    padding: 20px;
}

.digiplanet-welcome-section {
    margin-bottom: 30px;
    padding-bottom: 20px;
    border-bottom: 1px solid #e0e0e0;
}

.digiplanet-welcome-section h1 {
    margin: 0 0 10px 0;
    color: #1d2327;
}

.digiplanet-welcome-section p {
    margin: 0;
    color: #646970;
    font-size: 16px;
}

.digiplanet-stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.digiplanet-stat-card {
    background: white;
    border: 1px solid #dcdcde;
    border-radius: 8px;
    padding: 20px;
    display: flex;
    align-items: center;
    gap: 15px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.04);
    transition: transform 0.2s, box-shadow 0.2s;
}

.digiplanet-stat-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.08);
}

.digiplanet-stat-icon {
    background: #2271b1;
    color: white;
    width: 50px;
    height: 50px;
    border-radius: 8px;
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
    font-size: 14px;
}

.digiplanet-dashboard-content {
    display: flex;
    flex-direction: column;
    gap: 30px;
}

.digiplanet-dashboard-section {
    background: white;
    border: 1px solid #dcdcde;
    border-radius: 8px;
    overflow: hidden;
}

.digiplanet-section-header {
    padding: 20px;
    border-bottom: 1px solid #e0e0e0;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.digiplanet-section-header h2 {
    margin: 0;
    font-size: 20px;
}

.digiplanet-view-all {
    color: #2271b1;
    text-decoration: none;
    font-size: 14px;
    font-weight: 500;
}

.digiplanet-view-all:hover {
    text-decoration: underline;
}

.digiplanet-products-grid {
    padding: 20px;
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 20px;
}

.digiplanet-product-card {
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    overflow: hidden;
    transition: box-shadow 0.2s;
}

.digiplanet-product-card:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
}

.digiplanet-product-image {
    height: 180px;
    overflow: hidden;
}

.digiplanet-product-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s;
}

.digiplanet-product-card:hover .digiplanet-product-image img {
    transform: scale(1.05);
}

.digiplanet-product-placeholder {
    width: 100%;
    height: 100%;
    background: #f0f0f1;
    display: flex;
    align-items: center;
    justify-content: center;
}

.digiplanet-product-placeholder .dashicons {
    color: #646970;
    font-size: 48px;
    width: 48px;
    height: 48px;
}

.digiplanet-product-content {
    padding: 20px;
}

.digiplanet-product-content h3 {
    margin: 0 0 10px 0;
    font-size: 18px;
    line-height: 1.4;
}

.digiplanet-product-meta {
    display: flex;
    gap: 10px;
    margin-bottom: 15px;
    flex-wrap: wrap;
}

.digiplanet-license-status {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 4px 8px;
    background: #edf7ed;
    color: #1e4620;
    border-radius: 4px;
    font-size: 12px;
    font-weight: 500;
}

.digiplanet-license-status .dashicons {
    font-size: 12px;
}

.digiplanet-license-status.active {
    background: #edf7ed;
    color: #1e4620;
}

.digiplanet-download-expiry {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 4px 8px;
    background: #f0f6fc;
    color: #2271b1;
    border-radius: 4px;
    font-size: 12px;
}

.digiplanet-download-expiry .dashicons {
    font-size: 12px;
}

.digiplanet-product-actions {
    display: flex;
    gap: 10px;
    margin-top: 15px;
}

.digiplanet-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 16px;
    text-decoration: none;
    border-radius: 4px;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    border: 1px solid transparent;
    transition: all 0.2s;
}

.digiplanet-btn-primary {
    background: #2271b1;
    color: white;
    border-color: #2271b1;
}

.digiplanet-btn-primary:hover {
    background: #135e96;
    border-color: #135e96;
    color: white;
}

.digiplanet-btn-secondary {
    background: transparent;
    color: #2271b1;
    border-color: #2271b1;
}

.digiplanet-btn-secondary:hover {
    background: #f0f6fc;
}

.digiplanet-btn .dashicons {
    font-size: 16px;
}

.digiplanet-empty-state {
    padding: 40px 20px;
    text-align: center;
}

.digiplanet-empty-icon {
    margin-bottom: 20px;
}

.digiplanet-empty-icon .dashicons {
    font-size: 48px;
    width: 48px;
    height: 48px;
    color: #646970;
}

.digiplanet-empty-state h3 {
    margin: 0 0 10px 0;
    color: #1d2327;
}

.digiplanet-empty-state p {
    margin: 0 0 20px 0;
    color: #646970;
}

.digiplanet-licenses-table {
    padding: 20px;
    overflow-x: auto;
}

.digiplanet-licenses-table table {
    width: 100%;
    border-collapse: collapse;
}

.digiplanet-licenses-table th,
.digiplanet-licenses-table td {
    padding: 12px;
    text-align: left;
    border-bottom: 1px solid #e0e0e0;
}

.digiplanet-licenses-table th {
    font-weight: 600;
    background: #f9f9f9;
}

.digiplanet-licenses-table tbody tr:hover {
    background: #f9f9f9;
}

.digiplanet-license-key {
    display: flex;
    align-items: center;
    gap: 10px;
}

.digiplanet-license-key code {
    font-family: monospace;
    background: #f0f0f1;
    padding: 4px 8px;
    border-radius: 3px;
    font-size: 12px;
}

.digiplanet-copy-btn {
    background: none;
    border: none;
    cursor: pointer;
    color: #646970;
    padding: 4px;
    border-radius: 3px;
    transition: all 0.2s;
}

.digiplanet-copy-btn:hover {
    background: #f0f0f1;
    color: #2271b1;
}

.digiplanet-copy-btn .dashicons {
    font-size: 16px;
    width: 16px;
    height: 16px;
}
</style>

<script>
jQuery(document).ready(function($) {
    // Copy license key functionality
    $('.digiplanet-copy-btn').on('click', function() {
        var button = $(this);
        var text = button.data('clipboard-text');
        
        // Create temporary input element
        var $temp = $('<input>');
        $('body').append($temp);
        $temp.val(text).select();
        
        // Execute copy command
        try {
            document.execCommand('copy');
            
            // Show success feedback
            var originalHtml = button.html();
            button.html('<span class="dashicons dashicons-yes"></span>');
            button.css('color', '#46b450');
            
            // Revert after 2 seconds
            setTimeout(function() {
                button.html(originalHtml);
                button.css('color', '');
            }, 2000);
        } catch (err) {
            console.log('Failed to copy text: ', err);
        }
        
        $temp.remove();
    });
});
</script>