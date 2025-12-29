<?php
/**
 * Product grid template
 */

if (!defined('ABSPATH')) {
    exit;
}

global $wp_query;
$product_manager = Digiplanet_Product_Manager::get_instance();
?>

<div class="digiplanet-product-grid-wrapper">
    <?php if (!empty($products)): ?>
        <div class="digiplanet-product-grid digiplanet-cols-<?php echo esc_attr($atts['columns']); ?>">
            <?php foreach ($products as $product): ?>
                <?php
                $price_html = $product_manager->format_price($product->price, $product->sale_price);
                $thumbnail = $product->featured_image_id ? wp_get_attachment_url($product->featured_image_id) : DIGIPLANET_ASSETS_URL . 'images/placeholder-product.png';
                ?>
                
                <div class="digiplanet-product-card" data-product-id="<?php echo $product->id; ?>">
                    <div class="digiplanet-product-image">
                        <a href="<?php echo home_url('/digital-product/' . $product->slug); ?>" class="digiplanet-product-link">
                            <img src="<?php echo esc_url($thumbnail); ?>" alt="<?php echo esc_attr($product->name); ?>">
                            
                            <?php if ($product->sale_price): ?>
                                <span class="digiplanet-product-badge digiplanet-badge-sale">
                                    <?php echo $product_manager->calculate_sale_percentage($product->price, $product->sale_price); ?>% OFF
                                </span>
                            <?php endif; ?>
                            
                            <?php if ($product->featured): ?>
                                <span class="digiplanet-product-badge digiplanet-badge-featured">
                                    <?php _e('Featured', 'digiplanet-digital-products'); ?>
                                </span>
                            <?php endif; ?>
                        </a>
                        
                        <div class="digiplanet-product-overlay">
                            <button type="button" 
                                    class="digiplanet-btn digiplanet-add-to-cart" 
                                    data-product-id="<?php echo $product->id; ?>">
                                <span class="dashicons dashicons-cart"></span>
                                <?php _e('Add to Cart', 'digiplanet-digital-products'); ?>
                            </button>
                        </div>
                    </div>
                    
                    <div class="digiplanet-product-content">
                        <h3 class="digiplanet-product-title">
                            <a href="<?php echo home_url('/digital-product/' . $product->slug); ?>">
                                <?php echo esc_html($product->name); ?>
                            </a>
                        </h3>
                        
                        <?php if ($product->short_description): ?>
                            <div class="digiplanet-product-excerpt">
                                <?php echo wp_kses_post(wp_trim_words($product->short_description, 20)); ?>
                            </div>
                        <?php endif; ?>
                        
                        <div class="digiplanet-product-price">
                            <span class="digiplanet-current-price"><?php echo $price_html; ?></span>
                            <?php if ($product->sale_price): ?>
                                <span class="digiplanet-original-price"><?php echo $product_manager->format_price($product->price); ?></span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="digiplanet-product-meta">
                            <?php if ($product->rating > 0): ?>
                                <div class="digiplanet-rating">
                                    <span class="digiplanet-rating-stars">
                                        <?php echo str_repeat('★', floor($product->rating)); ?>
                                        <?php echo str_repeat('☆', 5 - floor($product->rating)); ?>
                                    </span>
                                    <span class="digiplanet-rating-number">(<?php echo $product->rating; ?>)</span>
                                </div>
                            <?php else: ?>
                                <span class="digiplanet-no-rating"><?php _e('No ratings yet', 'digiplanet-digital-products'); ?></span>
                            <?php endif; ?>
                            
                            <?php if ($product->sales_count > 0): ?>
                                <div class="digiplanet-sales-count">
                                    <span class="dashicons dashicons-chart-area"></span>
                                    <?php echo number_format_i18n($product->sales_count); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="digiplanet-product-actions">
                            <button type="button" 
                                    class="digiplanet-btn digiplanet-btn-primary digiplanet-add-to-cart" 
                                    data-product-id="<?php echo $product->id; ?>">
                                <?php _e('Add to Cart', 'digiplanet-digital-products'); ?>
                            </button>
                            <a href="<?php echo home_url('/digital-product/' . $product->slug); ?>" class="digiplanet-btn digiplanet-btn-secondary">
                                <?php _e('View Details', 'digiplanet-digital-products'); ?>
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <?php if ($atts['pagination'] && $total_pages > 1): ?>
            <div class="digiplanet-pagination">
                <?php
                echo paginate_links([
                    'base' => add_query_arg('paged', '%#%'),
                    'format' => '',
                    'prev_text' => '&laquo; ' . __('Previous', 'digiplanet-digital-products'),
                    'next_text' => __('Next', 'digiplanet-digital-products') . ' &raquo;',
                    'total' => $total_pages,
                    'current' => $paged,
                    'mid_size' => 2,
                    'end_size' => 1,
                ]);
                ?>
            </div>
        <?php endif; ?>
        
    <?php else: ?>
        <div class="digiplanet-no-products">
            <p><?php _e('No products found.', 'digiplanet-digital-products'); ?></p>
            <a href="<?php echo home_url('/'); ?>" class="digiplanet-btn digiplanet-btn-primary">
                <?php _e('Continue Shopping', 'digiplanet-digital-products'); ?>
            </a>
        </div>
    <?php endif; ?>
</div>

<style>
.digiplanet-product-grid {
    display: grid;
    grid-template-columns: repeat(<?php echo $atts['columns']; ?>, 1fr);
    gap: 30px;
    margin: 30px 0;
}

.digiplanet-cols-1 { grid-template-columns: 1fr; }
.digiplanet-cols-2 { grid-template-columns: repeat(2, 1fr); }
.digiplanet-cols-3 { grid-template-columns: repeat(3, 1fr); }
.digiplanet-cols-4 { grid-template-columns: repeat(4, 1fr); }
.digiplanet-cols-5 { grid-template-columns: repeat(5, 1fr); }
.digiplanet-cols-6 { grid-template-columns: repeat(6, 1fr); }

@media (max-width: 1200px) {
    .digiplanet-cols-5,
    .digiplanet-cols-6 { grid-template-columns: repeat(4, 1fr); }
}

@media (max-width: 992px) {
    .digiplanet-cols-4,
    .digiplanet-cols-5,
    .digiplanet-cols-6 { grid-template-columns: repeat(3, 1fr); }
}

@media (max-width: 768px) {
    .digiplanet-cols-3,
    .digiplanet-cols-4,
    .digiplanet-cols-5,
    .digiplanet-cols-6 { grid-template-columns: repeat(2, 1fr); }
}

@media (max-width: 576px) {
    .digiplanet-cols-2,
    .digiplanet-cols-3,
    .digiplanet-cols-4,
    .digiplanet-cols-5,
    .digiplanet-cols-6 { grid-template-columns: 1fr; }
}
</style>