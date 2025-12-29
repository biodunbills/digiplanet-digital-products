<?php
/**
 * My Products Template
 */

if (!defined('ABSPATH')) {
    exit;
}

$user = wp_get_current_user();
$account_manager = Digiplanet_Account_Manager::get_instance();
$products = $account_manager->get_customer_products($user->ID, 20, 0);
$stats = $account_manager->get_customer_stats($user->ID);
?>

<div class="digiplanet-my-products">
    <!-- Header -->
    <div class="digiplanet-page-header">
        <h1><?php _e('My Products', 'digiplanet-digital-products'); ?></h1>
        <p><?php _e('All your purchased digital products in one place', 'digiplanet-digital-products'); ?></p>
    </div>
    
    <!-- Stats Overview -->
    <div class="digiplanet-stats-overview">
        <div class="digiplanet-stat-card">
            <div class="digiplanet-stat-icon">
                <span class="dashicons dashicons-products"></span>
            </div>
            <div class="digiplanet-stat-content">
                <h3><?php echo esc_html($stats['total_products']); ?></h3>
                <p><?php _e('Total Products', 'digiplanet-digital-products'); ?></p>
            </div>
        </div>
        
        <div class="digiplanet-stat-card">
            <div class="digiplanet-stat-icon">
                <span class="dashicons dashicons-download"></span>
            </div>
            <div class="digiplanet-stat-content">
                <h3><?php echo esc_html($this->get_total_downloads($user->ID)); ?></h3>
                <p><?php _e('Total Downloads', 'digiplanet-digital-products'); ?></p>
            </div>
        </div>
        
        <div class="digiplanet-stat-card">
            <div class="digiplanet-stat-icon">
                <span class="dashicons dashicons-clock"></span>
            </div>
            <div class="digiplanet-stat-content">
                <h3><?php echo esc_html($this->get_expiring_soon_count($user->ID)); ?></h3>
                <p><?php _e('Expiring Soon', 'digiplanet-digital-products'); ?></p>
            </div>
        </div>
    </div>
    
    <!-- Products Grid -->
    <div class="digiplanet-products-grid">
        <?php if (!empty($products)): ?>
            <?php foreach ($products as $product): ?>
                <div class="digiplanet-product-card">
                    <div class="digiplanet-product-header">
                        <div class="digiplanet-product-image">
                            <?php if ($product->featured_image_id): ?>
                                <?php echo wp_get_attachment_image($product->featured_image_id, 'medium'); ?>
                            <?php else: ?>
                                <div class="digiplanet-product-placeholder">
                                    <span class="dashicons dashicons-format-image"></span>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="digiplanet-product-badges">
                            <?php if ($product->license_key): ?>
                                <span class="digiplanet-badge digiplanet-badge-license">
                                    <span class="dashicons dashicons-lock"></span>
                                    <?php _e('Licensed', 'digiplanet-digital-products'); ?>
                                </span>
                            <?php endif; ?>
                            
                            <?php if ($product->download_expires && strtotime($product->download_expires) > time()): ?>
                                <?php
                                $days_remaining = ceil((strtotime($product->download_expires) - time()) / DAY_IN_SECONDS);
                                if ($days_remaining <= 7): ?>
                                    <span class="digiplanet-badge digiplanet-badge-expiring">
                                        <span class="dashicons dashicons-clock"></span>
                                        <?php printf(__('%d days left', 'digiplanet-digital-products'), $days_remaining); ?>
                                    </span>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="digiplanet-product-content">
                        <h3 class="digiplanet-product-title">
                            <?php echo esc_html($product->name); ?>
                        </h3>
                        
                        <?php if (!empty($product->version)): ?>
                            <p class="digiplanet-product-version">
                                <strong><?php _e('Version:', 'digiplanet-digital-products'); ?></strong>
                                <?php echo esc_html($product->version); ?>
                            </p>
                        <?php endif; ?>
                        
                        <?php if (!empty($product->file_size)): ?>
                            <p class="digiplanet-product-size">
                                <strong><?php _e('Size:', 'digiplanet-digital-products'); ?></strong>
                                <?php echo esc_html($product->file_size); ?>
                            </p>
                        <?php endif; ?>
                        
                        <?php if (!empty($product->file_format)): ?>
                            <p class="digiplanet-product-format">
                                <strong><?php _e('Format:', 'digiplanet-digital-products'); ?></strong>
                                <?php echo esc_html($product->file_format); ?>
                            </p>
                        <?php endif; ?>
                        
                        <div class="digiplanet-product-meta">
                            <div class="digiplanet-product-downloads">
                                <span class="dashicons dashicons-download"></span>
                                <?php printf(
                                    __('%d downloads', 'digiplanet-digital-products'),
                                    esc_html($product->download_count)
                                ); ?>
                            </div>
                            
                            <?php if ($product->download_expires): ?>
                                <div class="digiplanet-product-expiry">
                                    <span class="dashicons dashicons-calendar"></span>
                                    <?php
                                    if (strtotime($product->download_expires) > time()) {
                                        echo sprintf(
                                            __('Expires: %s', 'digiplanet-digital-products'),
                                            date_i18n(get_option('date_format'), strtotime($product->download_expires))
                                        );
                                    } else {
                                        echo '<span class="digiplanet-expired">' . __('Expired', 'digiplanet-digital-products') . '</span>';
                                    }
                                    ?>
                                </div>
                            <?php endif; ?>
                        </div>
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
                            <button type="button" class="digiplanet-btn digiplanet-btn-secondary digiplanet-copy-license" 
                                    data-license="<?php echo esc_attr($product->license_key); ?>">
                                <span class="dashicons dashicons-admin-page"></span>
                                <?php _e('Copy License', 'digiplanet-digital-products'); ?>
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
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
    
    <!-- Pagination -->
    <?php if (!empty($products) && count($products) >= 20): ?>
        <div class="digiplanet-pagination">
            <button type="button" class="digiplanet-btn digiplanet-btn-secondary" id="digiplanet-load-more">
                <?php _e('Load More', 'digiplanet-digital-products'); ?>
            </button>
        </div>
    <?php endif; ?>
</div>

<style>
.digiplanet-my-products {
    padding: 20px;
}

.digiplanet-page-header {
    margin-bottom: 30px;
    text-align: center;
}

.digiplanet-page-header h1 {
    margin: 0 0 10px 0;
    font-size: 32px;
    color: #1d2327;
}

.digiplanet-page-header p {
    margin: 0;
    color: #646970;
    font-size: 16px;
}

.digiplanet-stats-overview {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-bottom: 40px;
}

.digiplanet-stat-card {
    background: white;
    border: 1px solid #dcdcde;
    border-radius: 8px;
    padding: 20px;
    display: flex;
    align-items: center;
    gap: 15px;
}

.digiplanet-stat-icon {
    background: #f0f6fc;
    color: #2271b1;
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

.digiplanet-products-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 30px;
    margin-bottom: 40px;
}

.digiplanet-product-card {
    background: white;
    border: 1px solid #dcdcde;
    border-radius: 12px;
    overflow: hidden;
    transition: all 0.3s ease;
}

.digiplanet-product-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.1);
}

.digiplanet-product-header {
    position: relative;
}

.digiplanet-product-image {
    height: 200px;
    overflow: hidden;
}

.digiplanet-product-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.5s ease;
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

.digiplanet-product-badges {
    position: absolute;
    top: 15px;
    left: 15px;
    display: flex;
    flex-direction: column;
    gap: 5px;
}

.digiplanet-badge {
    padding: 6px 10px;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    border-radius: 4px;
    color: white;
    display: flex;
    align-items: center;
    gap: 4px;
}

.digiplanet-badge-license {
    background: #46b450;
}

.digiplanet-badge-expiring {
    background: #ffb900;
}

.digiplanet-product-content {
    padding: 20px;
}

.digiplanet-product-title {
    margin: 0 0 15px 0;
    font-size: 18px;
    color: #1d2327;
    line-height: 1.4;
}

.digiplanet-product-version,
.digiplanet-product-size,
.digiplanet-product-format {
    margin: 0 0 8px 0;
    font-size: 14px;
    color: #646970;
}

.digiplanet-product-version strong,
.digiplanet-product-size strong,
.digiplanet-product-format strong {
    color: #1d2327;
}

.digiplanet-product-meta {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-top: 15px;
    margin-top: 15px;
    border-top: 1px solid #f0f0f0;
    font-size: 13px;
    color: #646970;
}

.digiplanet-product-downloads,
.digiplanet-product-expiry {
    display: flex;
    align-items: center;
    gap: 6px;
}

.digiplanet-product-downloads .dashicons,
.digiplanet-product-expiry .dashicons {
    font-size: 14px;
}

.digiplanet-expired {
    color: #dc3232;
    font-weight: 500;
}

.digiplanet-product-actions {
    padding: 0 20px 20px 20px;
    display: flex;
    gap: 10px;
}

.digiplanet-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    padding: 10px 16px;
    text-decoration: none;
    border-radius: 6px;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    border: 1px solid transparent;
    transition: all 0.2s;
    flex: 1;
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

.digiplanet-empty-state {
    grid-column: 1 / -1;
    text-align: center;
    padding: 60px 20px;
}

.digiplanet-empty-icon {
    margin-bottom: 20px;
}

.digiplanet-empty-icon .dashicons {
    font-size: 64px;
    width: 64px;
    height: 64px;
    color: #ccd0d4;
}

.digiplanet-empty-state h3 {
    margin: 0 0 10px 0;
    color: #1d2327;
    font-size: 24px;
}

.digiplanet-empty-state p {
    margin: 0 0 20px 0;
    color: #646970;
    font-size: 16px;
}

.digiplanet-pagination {
    text-align: center;
}

#digiplanet-load-more {
    padding: 12px 30px;
    font-size: 16px;
}
</style>

<script>
jQuery(document).ready(function($) {
    // Copy license key functionality
    $('.digiplanet-copy-license').on('click', function() {
        var button = $(this);
        var licenseKey = button.data('license');
        
        // Create temporary input element
        var $temp = $('<input>');
        $('body').append($temp);
        $temp.val(licenseKey).select();
        
        // Execute copy command
        try {
            document.execCommand('copy');
            
            // Show success feedback
            var originalHtml = button.html();
            button.html('<span class="dashicons dashicons-yes"></span> <?php esc_js_e('Copied!', 'digiplanet-digital-products'); ?>');
            button.css({
                'background': '#edf7ed',
                'border-color': '#46b450',
                'color': '#1e4620'
            });
            
            // Revert after 2 seconds
            setTimeout(function() {
                button.html(originalHtml);
                button.css({
                    'background': '',
                    'border-color': '',
                    'color': ''
                });
            }, 2000);
        } catch (err) {
            console.log('Failed to copy text: ', err);
            
            // Show error feedback
            var originalHtml = button.html();
            button.html('<span class="dashicons dashicons-no"></span> <?php esc_js_e('Failed!', 'digiplanet-digital-products'); ?>');
            button.css({
                'background': '#f7eded',
                'border-color': '#dc3232',
                'color': '#5c1d1d'
            });
            
            // Revert after 2 seconds
            setTimeout(function() {
                button.html(originalHtml);
                button.css({
                    'background': '',
                    'border-color': '',
                    'color': ''
                });
            }, 2000);
        }
        
        $temp.remove();
    });
    
    // Load more products
    var page = 1;
    var loading = false;
    
    $('#digiplanet-load-more').on('click', function() {
        if (loading) return;
        
        loading = true;
        var button = $(this);
        var originalText = button.html();
        
        button.html('<span class="digiplanet-spinner"></span> <?php esc_js_e('Loading...', 'digiplanet-digital-products'); ?>');
        button.prop('disabled', true);
        
        page++;
        
        $.ajax({
            url: digiplanet_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'digiplanet_load_more_products',
                nonce: digiplanet_ajax.nonce,
                page: page
            },
            success: function(response) {
                if (response.success && response.data.html) {
                    $('.digiplanet-products-grid').append(response.data.html);
                    
                    // Update button state
                    if (!response.data.has_more) {
                        button.hide();
                    } else {
                        button.html(originalText);
                        button.prop('disabled', false);
                    }
                } else {
                    button.html(originalText);
                    button.prop('disabled', false);
                }
                
                loading = false;
            },
            error: function() {
                button.html(originalText);
                button.prop('disabled', false);
                loading = false;
            }
        });
    });
    
    // Download tracking
    $('.digiplanet-btn-primary[href*="download"]').on('click', function(e) {
        var button = $(this);
        var originalHtml = button.html();
        
        // Add loading state
        button.html('<span class="digiplanet-spinner"></span> <?php esc_js_e('Preparing...', 'digiplanet-digital-products'); ?>');
        button.prop('disabled', true);
        
        // Track download in background
        var url = new URL(button.attr('href'));
        var productId = url.searchParams.get('product_id');
        var licenseKey = url.searchParams.get('license_key');
        
        if (productId) {
            $.ajax({
                url: digiplanet_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'digiplanet_track_download',
                    nonce: digiplanet_ajax.nonce,
                    product_id: productId,
                    license_key: licenseKey
                },
                complete: function() {
                    // Revert button state after a short delay
                    setTimeout(function() {
                        button.html(originalHtml);
                        button.prop('disabled', false);
                    }, 1000);
                }
            });
        }
    });
});

// Spinner animation
var style = document.createElement('style');
style.textContent = `
.digiplanet-spinner {
    display: inline-block;
    width: 16px;
    height: 16px;
    border: 2px solid rgba(255,255,255,0.3);
    border-radius: 50%;
    border-top-color: white;
    animation: digiplanet-spin 1s ease-in-out infinite;
}

@keyframes digiplanet-spin {
    to { transform: rotate(360deg); }
}
`;
document.head.appendChild(style);
</script>