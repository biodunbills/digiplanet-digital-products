<?php
/**
 * Product detail template
 */

if (!defined('ABSPATH')) {
    exit;
}

global $post;
$product_id = get_post_meta($post->ID, '_digiplanet_product_id', true);

if (!$product_id) {
    return;
}

$product_manager = Digiplanet_Product_Manager::get_instance();
$product = $product_manager->get_product($product_id);

if (!$product) {
    return;
}

$price_html = $product_manager->format_price($product->price, $product->sale_price);
$thumbnail = $product->featured_image_id ? wp_get_attachment_url($product->featured_image_id) : DIGIPLANET_ASSETS_URL . 'images/placeholder-product.png';
$gallery = $product->gallery ?? [];
$requirements = $product->requirements ?? [];
$related_products = $product_manager->get_related_products($product->id, 4);
$can_purchase = true; // Add purchase restrictions logic here
?>

<div class="digiplanet-product-detail">
    <!-- Product Gallery -->
    <div class="digiplanet-product-gallery">
        <div class="digiplanet-product-main-image">
            <img src="<?php echo esc_url($thumbnail); ?>" alt="<?php echo esc_attr($product->name); ?>" id="digiplanet-main-image">
        </div>
        
        <?php if (!empty($gallery)): ?>
            <div class="digiplanet-product-thumbnails">
                <div class="digiplanet-thumbnail active" data-image="<?php echo esc_url($thumbnail); ?>">
                    <img src="<?php echo esc_url($thumbnail); ?>" alt="<?php echo esc_attr($product->name); ?>">
                </div>
                
                <?php foreach ($gallery as $image): ?>
                    <div class="digiplanet-thumbnail" data-image="<?php echo esc_url($image['url']); ?>">
                        <img src="<?php echo esc_url($image['thumbnail']); ?>" alt="<?php echo esc_attr($product->name); ?>">
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Product Info -->
    <div class="digiplanet-product-info">
        <h1 class="digiplanet-product-title"><?php echo esc_html($product->name); ?></h1>
        
        <div class="digiplanet-product-meta">
            <?php if ($product->rating > 0): ?>
                <div class="digiplanet-rating">
                    <span class="digiplanet-rating-stars">
                        <?php echo str_repeat('★', floor($product->rating)); ?>
                        <?php echo str_repeat('☆', 5 - floor($product->rating)); ?>
                    </span>
                    <span class="digiplanet-rating-number">
                        <?php echo $product->rating; ?> (<?php echo $product->review_count; ?> <?php _e('reviews', 'digiplanet-digital-products'); ?>)
                    </span>
                </div>
            <?php endif; ?>
            
            <div class="digiplanet-sales-count">
                <span class="dashicons dashicons-chart-area"></span>
                <?php echo number_format_i18n($product->sales_count); ?> <?php _e('sold', 'digiplanet-digital-products'); ?>
            </div>
            
            <?php if ($product->version): ?>
                <div class="digiplanet-version">
                    <span class="dashicons dashicons-info"></span>
                    <?php _e('Version', 'digiplanet-digital-products'); ?>: <?php echo esc_html($product->version); ?>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="digiplanet-product-price">
            <span class="digiplanet-current-price"><?php echo $price_html; ?></span>
            <?php if ($product->sale_price): ?>
                <span class="digiplanet-original-price"><?php echo $product_manager->format_price($product->price); ?></span>
                <span class="digiplanet-sale-percentage">
                    <?php echo $product_manager->calculate_sale_percentage($product->price, $product->sale_price); ?>% OFF
                </span>
            <?php endif; ?>
        </div>
        
        <?php if ($product->short_description): ?>
            <div class="digiplanet-product-short-description">
                <?php echo wp_kses_post($product->short_description); ?>
            </div>
        <?php endif; ?>
        
        <!-- Product Actions -->
        <div class="digiplanet-product-actions">
            <?php if ($can_purchase): ?>
                <div class="digiplanet-quantity-selector">
                    <label for="digiplanet-product-quantity"><?php _e('Quantity', 'digiplanet-digital-products'); ?></label>
                    <div class="digiplanet-quantity-controls">
                        <button type="button" class="digiplanet-qty-minus">-</button>
                        <input type="number" 
                               id="digiplanet-product-quantity" 
                               class="digiplanet-qty-input" 
                               value="1" 
                               min="1"
                               data-product-id="<?php echo $product->id; ?>">
                        <button type="button" class="digiplanet-qty-plus">+</button>
                    </div>
                </div>
                
                <button type="button" 
                        class="digiplanet-btn digiplanet-btn-primary digiplanet-add-to-cart" 
                        data-product-id="<?php echo $product->id; ?>">
                    <span class="dashicons dashicons-cart"></span>
                    <?php _e('Add to Cart', 'digiplanet-digital-products'); ?>
                </button>
                
                <button type="button" 
                        class="digiplanet-btn digiplanet-btn-secondary digiplanet-buy-now" 
                        data-product-id="<?php echo $product->id; ?>">
                    <?php _e('Buy Now', 'digiplanet-digital-products'); ?>
                </button>
            <?php else: ?>
                <div class="digiplanet-cannot-purchase">
                    <p><?php _e('This product is not available for purchase.', 'digiplanet-digital-products'); ?></p>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Product Meta -->
        <div class="digiplanet-product-details">
            <?php if ($product->sku): ?>
                <div class="digiplanet-product-sku">
                    <strong><?php _e('SKU', 'digiplanet-digital-products'); ?>:</strong>
                    <span><?php echo esc_html($product->sku); ?></span>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($product->categories)): ?>
                <div class="digiplanet-product-categories">
                    <strong><?php _e('Categories', 'digiplanet-digital-products'); ?>:</strong>
                    <?php foreach ($product->categories as $category): ?>
                        <a href="<?php echo home_url('/product-category/' . $category->slug); ?>">
                            <?php echo esc_html($category->name); ?>
                        </a><?php echo $category !== end($product->categories) ? ', ' : ''; ?>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($product->tags)): ?>
                <div class="digiplanet-product-tags">
                    <strong><?php _e('Tags', 'digiplanet-digital-products'); ?>:</strong>
                    <?php foreach ($product->tags as $tag): ?>
                        <span class="digiplanet-tag"><?php echo esc_html($tag); ?></span>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Product Tabs -->
    <div class="digiplanet-product-tabs">
        <ul class="digiplanet-tabs-nav">
            <li class="active"><a href="#tab-description"><?php _e('Description', 'digiplanet-digital-products'); ?></a></li>
            <li><a href="#tab-requirements"><?php _e('Requirements', 'digiplanet-digital-products'); ?></a></li>
            <li><a href="#tab-reviews"><?php _e('Reviews', 'digiplanet-digital-products'); ?></a></li>
            <li><a href="#tab-faq"><?php _e('FAQ', 'digiplanet-digital-products'); ?></a></li>
        </ul>
        
        <div class="digiplanet-tabs-content">
            <!-- Description Tab -->
            <div id="tab-description" class="digiplanet-tab-content active">
                <?php if ($product->description): ?>
                    <div class="digiplanet-product-description">
                        <?php echo wp_kses_post(wpautop($product->description)); ?>
                    </div>
                <?php else: ?>
                    <p><?php _e('No description available.', 'digiplanet-digital-products'); ?></p>
                <?php endif; ?>
                
                <?php if ($product->file_size || $product->file_format): ?>
                    <div class="digiplanet-file-info">
                        <h4><?php _e('File Information', 'digiplanet-digital-products'); ?></h4>
                        <ul>
                            <?php if ($product->file_size): ?>
                                <li><strong><?php _e('File Size', 'digiplanet-digital-products'); ?>:</strong> <?php echo esc_html($product->file_size); ?></li>
                            <?php endif; ?>
                            <?php if ($product->file_format): ?>
                                <li><strong><?php _e('File Format', 'digiplanet-digital-products'); ?>:</strong> <?php echo esc_html($product->file_format); ?></li>
                            <?php endif; ?>
                            <?php if ($product->download_limit > 0): ?>
                                <li><strong><?php _e('Download Limit', 'digiplanet-digital-products'); ?>:</strong> <?php echo $product->download_limit; ?> <?php _e('downloads', 'digiplanet-digital-products'); ?></li>
                            <?php else: ?>
                                <li><strong><?php _e('Download Limit', 'digiplanet-digital-products'); ?>:</strong> <?php _e('Unlimited', 'digiplanet-digital-products'); ?></li>
                            <?php endif; ?>
                            <li><strong><?php _e('License Type', 'digiplanet-digital-products'); ?>:</strong> <?php echo ucfirst($product->license_type); ?></li>
                        </ul>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Requirements Tab -->
            <div id="tab-requirements" class="digiplanet-tab-content">
                <?php if (!empty($requirements)): ?>
                    <div class="digiplanet-requirements">
                        <h4><?php _e('System Requirements', 'digiplanet-digital-products'); ?></h4>
                        <ul>
                            <?php foreach ($requirements as $requirement): ?>
                                <li>
                                    <strong><?php echo esc_html($requirement['name']); ?>:</strong>
                                    <?php echo esc_html($requirement['value']); ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php else: ?>
                    <p><?php _e('No specific requirements listed.', 'digiplanet-digital-products'); ?></p>
                <?php endif; ?>
            </div>
            
            <!-- Reviews Tab -->
            <div id="tab-reviews" class="digiplanet-tab-content">
                <div class="digiplanet-reviews-summary">
                    <div class="digiplanet-average-rating">
                        <span class="digiplanet-rating-number"><?php echo $product->rating; ?></span>
                        <span class="digiplanet-rating-stars">
                            <?php echo str_repeat('★', floor($product->rating)); ?>
                            <?php echo str_repeat('☆', 5 - floor($product->rating)); ?>
                        </span>
                        <span class="digiplanet-review-count">
                            <?php echo $product->review_count; ?> <?php _e('reviews', 'digiplanet-digital-products'); ?>
                        </span>
                    </div>
                    
                    <?php if (is_user_logged_in() && Digiplanet_Security::can_access_product($product->id, get_current_user_id())): ?>
                        <button type="button" class="digiplanet-btn digiplanet-btn-primary digiplanet-write-review">
                            <?php _e('Write a Review', 'digiplanet-digital-products'); ?>
                        </button>
                    <?php endif; ?>
                </div>
                
                <!-- Reviews will be loaded via AJAX -->
                <div class="digiplanet-reviews-list"></div>
            </div>
            
            <!-- FAQ Tab -->
            <div id="tab-faq" class="digiplanet-tab-content">
                <!-- FAQ will be loaded from product meta or global settings -->
                <div class="digiplanet-faq-list">
                    <p><?php _e('No FAQ available for this product.', 'digiplanet-digital-products'); ?></p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Related Products -->
    <?php if (!empty($related_products)): ?>
        <div class="digiplanet-related-products">
            <h2><?php _e('Related Products', 'digiplanet-digital-products'); ?></h2>
            <div class="digiplanet-product-grid digiplanet-cols-4">
                <?php foreach ($related_products as $related_product): ?>
                    <?php echo Digiplanet_Public::get_instance()->get_product_card_html($related_product); ?>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
jQuery(document).ready(function($) {
    // Gallery thumbnail click
    $('.digiplanet-thumbnail').on('click', function() {
        var imageUrl = $(this).data('image');
        $('#digiplanet-main-image').attr('src', imageUrl);
        $('.digiplanet-thumbnail').removeClass('active');
        $(this).addClass('active');
    });
    
    // Tab switching
    $('.digiplanet-tabs-nav a').on('click', function(e) {
        e.preventDefault();
        var tabId = $(this).attr('href');
        
        // Update active tab
        $('.digiplanet-tabs-nav li').removeClass('active');
        $(this).parent().addClass('active');
        
        // Show active content
        $('.digiplanet-tab-content').removeClass('active');
        $(tabId).addClass('active');
    });
    
    // Quantity controls
    $('.digiplanet-qty-plus').on('click', function() {
        var $input = $(this).siblings('.digiplanet-qty-input');
        var currentVal = parseInt($input.val());
        $input.val(currentVal + 1);
    });
    
    $('.digiplanet-qty-minus').on('click', function() {
        var $input = $(this).siblings('.digiplanet-qty-input');
        var currentVal = parseInt($input.val());
        if (currentVal > 1) {
            $input.val(currentVal - 1);
        }
    });
    
    // Buy now button
    $('.digiplanet-buy-now').on('click', function() {
        var productId = $(this).data('product-id');
        var quantity = $('#digiplanet-product-quantity').val();
        
        // Add to cart and redirect to checkout
        $.ajax({
            url: digiplanet_frontend.ajax_url,
            type: 'POST',
            data: {
                action: 'digiplanet_add_to_cart',
                product_id: productId,
                quantity: quantity,
                nonce: digiplanet_frontend.nonce
            },
            success: function(response) {
                if (response.success) {
                    window.location.href = digiplanet_frontend.checkout_url;
                }
            }
        });
    });
    
    // Load reviews
    function loadReviews() {
        $.ajax({
            url: digiplanet_frontend.ajax_url,
            type: 'POST',
            data: {
                action: 'digiplanet_get_product_reviews',
                product_id: <?php echo $product->id; ?>,
                nonce: digiplanet_frontend.nonce
            },
            success: function(response) {
                if (response.success) {
                    $('.digiplanet-reviews-list').html(response.data.html);
                }
            }
        });
    }
    
    // Load reviews when reviews tab is clicked
    $('.digiplanet-tabs-nav a[href="#tab-reviews"]').on('click', function() {
        loadReviews();
    });
    
    // Write review
    $('.digiplanet-write-review').on('click', function() {
        // Show review form modal
        $('#digiplanet-review-modal').show();
    });
});
</script>