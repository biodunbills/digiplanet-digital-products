<?php
/**
 * Product Card Template
 */

if (!defined('ABSPATH')) {
    exit;
}

global $product;

if (!$product) {
    return;
}

$product_id = $product->ID;
$product_title = get_the_title($product_id);
$product_excerpt = get_the_excerpt($product_id);
$product_price = get_post_meta($product_id, '_digiplanet_price', true);
$sale_price = get_post_meta($product_id, '_digiplanet_sale_price', true);
$is_featured = get_post_meta($product_id, '_digiplanet_featured', true);
$rating = get_post_meta($product_id, '_digiplanet_rating', true);
$review_count = get_post_meta($product_id, '_digiplanet_review_count', true);
$sales_count = get_post_meta($product_id, '_digiplanet_sales_count', true);
$download_count = get_post_meta($product_id, '_digiplanet_download_count', true);

$categories = wp_get_post_terms($product_id, 'digiplanet_category', ['fields' => 'names']);
$tags = wp_get_post_terms($product_id, 'digiplanet_tag', ['fields' => 'names']);

$is_on_sale = !empty($sale_price) && $sale_price < $product_price;
$final_price = $is_on_sale ? $sale_price : $product_price;

// Format price
$product_manager = Digiplanet_Product_Manager::get_instance();
$formatted_price = $product_manager->format_price($final_price);
$formatted_regular_price = $is_on_sale ? $product_manager->format_price($product_price) : '';

// Get product thumbnail
$thumbnail_id = get_post_thumbnail_id($product_id);
$thumbnail_url = $thumbnail_id ? wp_get_attachment_image_url($thumbnail_id, 'medium') : DIGIPLANET_ASSETS_URL . 'images/placeholder-product.png';

// Get product URL
$product_url = get_permalink($product_id);
?>

<div class="digiplanet-product-card" data-product-id="<?php echo $product_id; ?>">
    <div class="digiplanet-product-card-inner">
        <!-- Product Image -->
        <div class="digiplanet-product-image">
            <a href="<?php echo esc_url($product_url); ?>" class="digiplanet-product-image-link">
                <img src="<?php echo esc_url($thumbnail_url); ?>" alt="<?php echo esc_attr($product_title); ?>" loading="lazy">
            </a>
            
            <!-- Product Badges -->
            <div class="digiplanet-product-badges">
                <?php if ($is_featured): ?>
                    <span class="digiplanet-product-badge digiplanet-badge-featured">
                        <i class="fas fa-star"></i>
                        <?php _e('Featured', 'digiplanet-digital-products'); ?>
                    </span>
                <?php endif; ?>
                
                <?php if ($is_on_sale): ?>
                    <span class="digiplanet-product-badge digiplanet-badge-sale">
                        <i class="fas fa-tag"></i>
                        <?php _e('Sale', 'digiplanet-digital-products'); ?>
                    </span>
                <?php endif; ?>
            </div>
            
            <!-- Quick Actions -->
            <div class="digiplanet-product-quick-actions">
                <button type="button" class="digiplanet-quick-action digiplanet-quick-view" data-product-id="<?php echo $product_id; ?>">
                    <i class="fas fa-eye"></i>
                    <span><?php _e('Quick View', 'digiplanet-digital-products'); ?></span>
                </button>
                
                <button type="button" class="digiplanet-quick-action digiplanet-add-to-cart" data-product-id="<?php echo $product_id; ?>">
                    <i class="fas fa-shopping-cart"></i>
                    <span><?php _e('Add to Cart', 'digiplanet-digital-products'); ?></span>
                </button>
            </div>
        </div>
        
        <!-- Product Content -->
        <div class="digiplanet-product-content">
            <!-- Product Categories -->
            <?php if (!empty($categories)): ?>
                <div class="digiplanet-product-categories">
                    <?php foreach (array_slice($categories, 0, 2) as $category): ?>
                        <a href="<?php echo get_term_link($category, 'digiplanet_category'); ?>" class="digiplanet-product-category">
                            <?php echo esc_html($category); ?>
                        </a>
                    <?php endforeach; ?>
                    <?php if (count($categories) > 2): ?>
                        <span class="digiplanet-product-category-more">
                            +<?php echo count($categories) - 2; ?> more
                        </span>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            
            <!-- Product Title -->
            <h3 class="digiplanet-product-title">
                <a href="<?php echo esc_url($product_url); ?>">
                    <?php echo esc_html($product_title); ?>
                </a>
            </h3>
            
            <!-- Product Rating -->
            <?php if ($rating > 0): ?>
                <div class="digiplanet-product-rating">
                    <div class="digiplanet-rating-stars">
                        <?php
                        $full_stars = floor($rating);
                        $has_half_star = ($rating - $full_stars) >= 0.5;
                        
                        for ($i = 1; $i <= 5; $i++):
                            if ($i <= $full_stars): ?>
                                <i class="fas fa-star"></i>
                            <?php elseif ($i == $full_stars + 1 && $has_half_star): ?>
                                <i class="fas fa-star-half-alt"></i>
                            <?php else: ?>
                                <i class="far fa-star"></i>
                            <?php endif;
                        endfor;
                        ?>
                    </div>
                    
                    <?php if ($review_count > 0): ?>
                        <span class="digiplanet-rating-count">
                            (<?php echo number_format($rating, 1); ?>)
                        </span>
                    <?php endif; ?>
                    
                    <?php if ($review_count > 0): ?>
                        <span class="digiplanet-review-count">
                            <?php printf(_n('%d review', '%d reviews', $review_count, 'digiplanet-digital-products'), $review_count); ?>
                        </span>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            
            <!-- Product Excerpt -->
            <?php if (!empty($product_excerpt)): ?>
                <div class="digiplanet-product-excerpt">
                    <?php echo wp_trim_words($product_excerpt, 20, '...'); ?>
                </div>
            <?php endif; ?>
            
            <!-- Product Meta -->
            <div class="digiplanet-product-meta">
                <?php if ($sales_count > 0): ?>
                    <div class="digiplanet-product-meta-item">
                        <i class="fas fa-shopping-cart"></i>
                        <span><?php printf(_n('%d sale', '%d sales', $sales_count, 'digiplanet-digital-products'), $sales_count); ?></span>
                    </div>
                <?php endif; ?>
                
                <?php if ($download_count > 0): ?>
                    <div class="digiplanet-product-meta-item">
                        <i class="fas fa-download"></i>
                        <span><?php printf(_n('%d download', '%d downloads', $download_count, 'digiplanet-digital-products'), $download_count); ?></span>
                    </div>
                <?php endif; ?>
                
                <?php
                $file_size = get_post_meta($product_id, '_digiplanet_file_size', true);
                if ($file_size): ?>
                    <div class="digiplanet-product-meta-item">
                        <i class="fas fa-hdd"></i>
                        <span><?php echo esc_html($file_size); ?></span>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Product Price -->
            <div class="digiplanet-product-price">
                <?php if ($is_on_sale): ?>
                    <span class="digiplanet-sale-price">
                        <?php echo $formatted_price; ?>
                    </span>
                    <span class="digiplanet-regular-price">
                        <?php echo $formatted_regular_price; ?>
                    </span>
                    <?php
                    $discount_percent = round(($product_price - $sale_price) / $product_price * 100);
                    if ($discount_percent > 0): ?>
                        <span class="digiplanet-discount-percent">
                            -<?php echo $discount_percent; ?>%
                        </span>
                    <?php endif; ?>
                <?php else: ?>
                    <span class="digiplanet-price">
                        <?php echo $formatted_price; ?>
                    </span>
                <?php endif; ?>
            </div>
            
            <!-- Product Actions -->
            <div class="digiplanet-product-actions">
                <div class="digiplanet-quantity-control">
                    <button type="button" class="digiplanet-quantity-minus">
                        <i class="fas fa-minus"></i>
                    </button>
                    <input type="number" class="digiplanet-quantity-input" value="1" min="1" max="99" data-product-id="<?php echo $product_id; ?>">
                    <button type="button" class="digiplanet-quantity-plus">
                        <i class="fas fa-plus"></i>
                    </button>
                </div>
                
                <button type="button" class="digiplanet-btn digiplanet-btn-primary digiplanet-add-to-cart-btn" data-product-id="<?php echo $product_id; ?>">
                    <i class="fas fa-shopping-cart"></i>
                    <?php _e('Add to Cart', 'digiplanet-digital-products'); ?>
                </button>
                
                <button type="button" class="digiplanet-btn digiplanet-btn-outline digiplanet-wishlist-btn" data-product-id="<?php echo $product_id; ?>">
                    <i class="far fa-heart"></i>
                </button>
            </div>
            
            <!-- Product Tags -->
            <?php if (!empty($tags)): ?>
                <div class="digiplanet-product-tags">
                    <?php foreach (array_slice($tags, 0, 3) as $tag): ?>
                        <span class="digiplanet-product-tag">
                            <?php echo esc_html($tag); ?>
                        </span>
                    <?php endforeach; ?>
                    <?php if (count($tags) > 3): ?>
                        <span class="digiplanet-product-tag-more" title="<?php echo esc_attr(implode(', ', array_slice($tags, 3))); ?>">
                            +<?php echo count($tags) - 3; ?>
                        </span>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Quick View Modal -->
<div id="digiplanet-quick-view-<?php echo $product_id; ?>" class="digiplanet-modal digiplanet-quick-view-modal" style="display: none;">
    <div class="digiplanet-modal-content">
        <div class="digiplanet-modal-header">
            <h3><?php echo esc_html($product_title); ?></h3>
            <button type="button" class="digiplanet-modal-close" onclick="closeQuickView(<?php echo $product_id; ?>)">&times;</button>
        </div>
        <div class="digiplanet-modal-body">
            <!-- Quick view content will be loaded via AJAX -->
            <div class="digiplanet-quick-view-loading">
                <i class="fas fa-spinner fa-spin"></i>
                <?php _e('Loading product details...', 'digiplanet-digital-products'); ?>
            </div>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // Quick view
    $('.digiplanet-quick-view[data-product-id="<?php echo $product_id; ?>"]').on('click', function() {
        var productId = $(this).data('product-id');
        showQuickView(productId);
    });
    
    // Add to cart
    $('.digiplanet-add-to-cart-btn[data-product-id="<?php echo $product_id; ?>"]').on('click', function() {
        var productId = $(this).data('product-id');
        var $quantityInput = $(this).siblings('.digiplanet-quantity-control').find('.digiplanet-quantity-input');
        var quantity = $quantityInput.val();
        
        addToCart(productId, quantity);
    });
    
    // Quantity controls
    $('.digiplanet-quantity-minus').on('click', function() {
        var $input = $(this).siblings('.digiplanet-quantity-input');
        var currentValue = parseInt($input.val()) || 1;
        if (currentValue > 1) {
            $input.val(currentValue - 1);
        }
    });
    
    $('.digiplanet-quantity-plus').on('click', function() {
        var $input = $(this).siblings('.digiplanet-quantity-input');
        var currentValue = parseInt($input.val()) || 1;
        $input.val(currentValue + 1);
    });
    
    // Wishlist
    $('.digiplanet-wishlist-btn').on('click', function() {
        var productId = $(this).data('product-id');
        var $button = $(this);
        
        toggleWishlist(productId, $button);
    });
});

function showQuickView(productId) {
    $('#digiplanet-quick-view-' + productId).show();
    
    $.ajax({
        url: digiplanet_ajax.ajax_url,
        type: 'POST',
        data: {
            action: 'digiplanet_get_quick_view',
            product_id: productId,
            nonce: digiplanet_ajax.nonce
        },
        success: function(response) {
            if (response.success) {
                $('#digiplanet-quick-view-' + productId + ' .digiplanet-modal-body').html(response.data.html);
            } else {
                $('#digiplanet-quick-view-' + productId + ' .digiplanet-modal-body').html(
                    '<div class="digiplanet-quick-view-error">' + response.data.message + '</div>'
                );
            }
        }
    });
}

function closeQuickView(productId) {
    $('#digiplanet-quick-view-' + productId).hide();
}

function addToCart(productId, quantity) {
    $.ajax({
        url: digiplanet_ajax.ajax_url,
        type: 'POST',
        data: {
            action: 'digiplanet_add_to_cart',
            product_id: productId,
            quantity: quantity,
            nonce: digiplanet_ajax.nonce
        },
        beforeSend: function() {
            $('.digiplanet-add-to-cart-btn[data-product-id="' + productId + '"]').prop('disabled', true)
                .html('<i class="fas fa-spinner fa-spin"></i> Adding...');
        },
        success: function(response) {
            if (response.success) {
                showNotification(response.data.message, 'success');
                updateCartCount(response.data.cart_count);
                
                setTimeout(function() {
                    $('.digiplanet-add-to-cart-btn[data-product-id="' + productId + '"]').prop('disabled', false)
                        .html('<i class="fas fa-shopping-cart"></i> Add to Cart');
                }, 1000);
            } else {
                showNotification(response.data.message, 'error');
                $('.digiplanet-add-to-cart-btn[data-product-id="' + productId + '"]').prop('disabled', false)
                    .html('<i class="fas fa-shopping-cart"></i> Add to Cart');
            }
        }
    });
}

function toggleWishlist(productId, $button) {
    $.ajax({
        url: digiplanet_ajax.ajax_url,
        type: 'POST',
        data: {
            action: 'digiplanet_toggle_wishlist',
            product_id: productId,
            nonce: digiplanet_ajax.nonce
        },
        beforeSend: function() {
            $button.prop('disabled', true);
        },
        success: function(response) {
            if (response.success) {
                if (response.data.in_wishlist) {
                    $button.html('<i class="fas fa-heart"></i>');
                    $button.addClass('in-wishlist');
                    showNotification(response.data.message, 'success');
                } else {
                    $button.html('<i class="far fa-heart"></i>');
                    $button.removeClass('in-wishlist');
                    showNotification(response.data.message, 'info');
                }
            } else {
                showNotification(response.data.message, 'error');
            }
        },
        complete: function() {
            $button.prop('disabled', false);
        }
    });
}

function updateCartCount(count) {
    $('.digiplanet-cart-count').text(count);
}

function showNotification(message, type) {
    var $notification = $('<div class="digiplanet-notification digiplanet-notification-' + type + '">' + message + '</div>');
    
    $('body').append($notification);
    
    setTimeout(function() {
        $notification.addClass('show');
    }, 10);
    
    setTimeout(function() {
        $notification.removeClass('show');
        setTimeout(function() {
            $notification.remove();
        }, 300);
    }, 3000);
}
</script>

<style>
.digiplanet-product-card {
    background: #fff;
    border: 1px solid #e9ecef;
    border-radius: 12px;
    overflow: hidden;
    transition: all 0.3s ease;
    height: 100%;
    position: relative;
}

.digiplanet-product-card:hover {
    box-shadow: 0 15px 40px rgba(0, 0, 0, 0.1);
    transform: translateY(-5px);
    border-color: #dee2e6;
}

.digiplanet-product-card-inner {
    display: flex;
    flex-direction: column;
    height: 100%;
}

.digiplanet-product-image {
    position: relative;
    overflow: hidden;
    height: 200px;
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

.digiplanet-product-badges {
    position: absolute;
    top: 15px;
    left: 15px;
    display: flex;
    flex-direction: column;
    gap: 5px;
    z-index: 2;
}

.digiplanet-product-badge {
    padding: 5px 10px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    display: inline-flex;
    align-items: center;
    gap: 5px;
}

.digiplanet-badge-featured {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.digiplanet-badge-sale {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    color: white;
}

.digiplanet-product-quick-actions {
    position: absolute;
    top: 50%;
    left: 0;
    right: 0;
    transform: translateY(-50%);
    display: flex;
    justify-content: center;
    gap: 10px;
    opacity: 0;
    transition: opacity 0.3s ease;
    z-index: 2;
}

.digiplanet-product-card:hover .digiplanet-product-quick-actions {
    opacity: 1;
}

.digiplanet-quick-action {
    background: rgba(255, 255, 255, 0.95);
    border: none;
    border-radius: 6px;
    padding: 12px 20px;
    font-size: 14px;
    font-weight: 600;
    color: #333;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 8px;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
}

.digiplanet-quick-action:hover {
    background: white;
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
}

.digiplanet-product-content {
    padding: 20px;
    flex: 1;
    display: flex;
    flex-direction: column;
}

.digiplanet-product-categories {
    display: flex;
    flex-wrap: wrap;
    gap: 5px;
    margin-bottom: 10px;
}

.digiplanet-product-category {
    font-size: 12px;
    color: #6c757d;
    text-decoration: none;
    padding: 3px 8px;
    background: #f8f9fa;
    border-radius: 4px;
    transition: all 0.3s ease;
}

.digiplanet-product-category:hover {
    background: #e9ecef;
    color: #495057;
}

.digiplanet-product-category-more {
    font-size: 12px;
    color: #adb5bd;
    font-style: italic;
}

.digiplanet-product-title {
    margin: 0 0 10px 0;
    font-size: 18px;
    font-weight: 600;
    line-height: 1.4;
}

.digiplanet-product-title a {
    color: #333;
    text-decoration: none;
    transition: color 0.3s ease;
}

.digiplanet-product-title a:hover {
    color: #3742fa;
}

.digiplanet-product-rating {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 10px;
    flex-wrap: wrap;
}

.digiplanet-rating-stars {
    color: #ffc107;
}

.digiplanet-rating-count {
    font-size: 14px;
    color: #6c757d;
    font-weight: 600;
}

.digiplanet-review-count {
    font-size: 14px;
    color: #adb5bd;
}

.digiplanet-product-excerpt {
    color: #6c757d;
    font-size: 14px;
    line-height: 1.6;
    margin-bottom: 15px;
    flex: 1;
}

.digiplanet-product-meta {
    display: flex;
    gap: 15px;
    margin-bottom: 15px;
    flex-wrap: wrap;
}

.digiplanet-product-meta-item {
    display: flex;
    align-items: center;
    gap: 5px;
    font-size: 13px;
    color: #6c757d;
}

.digiplanet-product-meta-item i {
    font-size: 12px;
}

.digiplanet-product-price {
    margin-bottom: 15px;
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
}

.digiplanet-price,
.digiplanet-sale-price {
    font-size: 24px;
    font-weight: 700;
    color: #3742fa;
}

.digiplanet-regular-price {
    font-size: 18px;
    color: #adb5bd;
    text-decoration: line-through;
}

.digiplanet-discount-percent {
    background: #28a745;
    color: white;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: 600;
}

.digiplanet-product-actions {
    display: flex;
    gap: 10px;
    margin-bottom: 15px;
}

.digiplanet-quantity-control {
    display: flex;
    border: 1px solid #dee2e6;
    border-radius: 6px;
    overflow: hidden;
}

.digiplanet-quantity-minus,
.digiplanet-quantity-plus {
    width: 40px;
    border: none;
    background: #f8f9fa;
    color: #495057;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: background 0.3s ease;
}

.digiplanet-quantity-minus:hover,
.digiplanet-quantity-plus:hover {
    background: #e9ecef;
}

.digiplanet-quantity-input {
    width: 50px;
    border: none;
    text-align: center;
    font-size: 16px;
    font-weight: 600;
    color: #333;
}

.digiplanet-quantity-input::-webkit-inner-spin-button,
.digiplanet-quantity-input::-webkit-outer-spin-button {
    -webkit-appearance: none;
    margin: 0;
}

.digiplanet-add-to-cart-btn {
    flex: 1;
}

.digiplanet-btn-outline {
    background: transparent;
    border: 2px solid #dee2e6;
    color: #495057;
}

.digiplanet-btn-outline:hover {
    background: #f8f9fa;
    border-color: #adb5bd;
}

.digiplanet-wishlist-btn.in-wishlist {
    color: #dc3545;
    border-color: #dc3545;
}

.digiplanet-product-tags {
    display: flex;
    flex-wrap: wrap;
    gap: 5px;
    margin-top: auto;
}

.digiplanet-product-tag {
    font-size: 11px;
    color: #6c757d;
    padding: 3px 8px;
    background: #f8f9fa;
    border-radius: 12px;
    border: 1px solid #e9ecef;
}

.digiplanet-product-tag-more {
    font-size: 11px;
    color: #adb5bd;
    font-style: italic;
    cursor: help;
}

/* Quick View Modal */
.digiplanet-quick-view-modal .digiplanet-modal-content {
    max-width: 900px;
    width: 90%;
}

.digiplanet-quick-view-loading {
    text-align: center;
    padding: 60px;
    color: #6c757d;
}

.digiplanet-quick-view-error {
    text-align: center;
    padding: 60px;
    color: #dc3545;
}

/* Responsive */
@media (max-width: 768px) {
    .digiplanet-product-image {
        height: 160px;
    }
    
    .digiplanet-product-quick-actions {
        opacity: 1;
        top: auto;
        bottom: 10px;
        transform: none;
    }
    
    .digiplanet-quick-action {
        padding: 8px 12px;
        font-size: 12px;
    }
    
    .digiplanet-product-actions {
        flex-direction: column;
    }
    
    .digiplanet-quantity-control {
        width: 100%;
        justify-content: space-between;
    }
}
</style>