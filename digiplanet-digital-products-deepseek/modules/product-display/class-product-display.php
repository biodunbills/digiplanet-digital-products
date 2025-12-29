<?php
/**
 * Product Display Module
 */

if (!defined('ABSPATH')) {
    exit;
}

class Digiplanet_Product_Display {
    
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
        add_shortcode('digiplanet_product_grid', [$this, 'product_grid_shortcode']);
        add_shortcode('digiplanet_product_carousel', [$this, 'product_carousel_shortcode']);
        add_shortcode('digiplanet_product_categories', [$this, 'product_categories_shortcode']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
    }
    
    /**
     * Enqueue assets
     */
    public function enqueue_assets() {
        // Product Display CSS
        wp_enqueue_style(
            'digiplanet-product-display',
            DIGIPLANET_PLUGIN_URL . 'modules/product-display/assets/css/product-display.css',
            [],
            DIGIPLANET_VERSION
        );
        
        // Carousel JavaScript (Slick Slider)
        if (is_singular('digiplanet_product') || has_shortcode(get_the_content(), 'digiplanet_product_carousel')) {
            wp_enqueue_style(
                'slick-carousel',
                'https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.css',
                [],
                '1.8.1'
            );
            
            wp_enqueue_script(
                'slick-carousel',
                'https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.min.js',
                ['jquery'],
                '1.8.1',
                true
            );
            
            wp_enqueue_script(
                'digiplanet-product-display',
                DIGIPLANET_PLUGIN_URL . 'modules/product-display/assets/js/product-display.js',
                ['jquery', 'slick-carousel'],
                DIGIPLANET_VERSION,
                true
            );
        }
    }
    
    /**
     * Product Grid Shortcode
     */
    public function product_grid_shortcode($atts) {
        $atts = shortcode_atts([
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
            'show_price' => true,
            'show_rating' => true,
            'show_add_to_cart' => true,
            'show_sale_badge' => true,
            'show_category' => true,
        ], $atts, 'digiplanet_product_grid');
        
        $query_args = [
            'post_type' => 'digiplanet_product',
            'posts_per_page' => intval($atts['limit']),
            'orderby' => $atts['orderby'],
            'order' => $atts['order'],
        ];
        
        // Add category filter
        if (!empty($atts['category'])) {
            $query_args['tax_query'][] = [
                'taxonomy' => 'digiplanet_category',
                'field' => 'slug',
                'terms' => explode(',', $atts['category']),
            ];
        }
        
        // Add tag filter
        if (!empty($atts['tag'])) {
            $query_args['tax_query'][] = [
                'taxonomy' => 'digiplanet_tag',
                'field' => 'slug',
                'terms' => explode(',', $atts['tag']),
            ];
        }
        
        // Add IDs filter
        if (!empty($atts['ids'])) {
            $query_args['post__in'] = array_map('intval', explode(',', $atts['ids']));
        }
        
        // Add exclude filter
        if (!empty($atts['exclude'])) {
            $query_args['post__not_in'] = array_map('intval', explode(',', $atts['exclude']));
        }
        
        // Add featured filter
        if ($atts['featured']) {
            $query_args['meta_query'][] = [
                'key' => '_featured',
                'value' => 'yes',
                'compare' => '=',
            ];
        }
        
        // Handle onsale filter
        if ($atts['onsale']) {
            $query_args['meta_query'][] = [
                'relation' => 'OR',
                [
                    'key' => '_sale_price',
                    'value' => 0,
                    'compare' => '>',
                    'type' => 'NUMERIC',
                ],
                [
                    'key' => '_sale_price_dates_from',
                    'value' => current_time('timestamp'),
                    'compare' => '<=',
                    'type' => 'NUMERIC',
                ],
                [
                    'key' => '_sale_price_dates_to',
                    'value' => current_time('timestamp'),
                    'compare' => '>=',
                    'type' => 'NUMERIC',
                ],
            ];
        }
        
        $products = new WP_Query($query_args);
        
        ob_start();
        
        if ($products->have_posts()) {
            $columns = intval($atts['columns']);
            $column_class = 'digiplanet-col-' . $columns;
            
            echo '<div class="digiplanet-product-grid ' . esc_attr($column_class) . '" data-columns="' . esc_attr($columns) . '">';
            
            while ($products->have_posts()) {
                $products->the_post();
                $this->render_product_card(get_the_ID(), $atts);
            }
            
            echo '</div>';
            
            wp_reset_postdata();
        } else {
            echo '<div class="digiplanet-no-products">';
            echo '<p>' . __('No products found.', 'digiplanet-digital-products') . '</p>';
            echo '</div>';
        }
        
        return ob_get_clean();
    }
    
    /**
     * Product Carousel Shortcode
     */
    public function product_carousel_shortcode($atts) {
        $atts = shortcode_atts([
            'category' => '',
            'tag' => '',
            'limit' => 8,
            'orderby' => 'date',
            'order' => 'DESC',
            'ids' => '',
            'exclude' => '',
            'featured' => false,
            'onsale' => false,
            'autoplay' => true,
            'autoplay_speed' => 3000,
            'arrows' => true,
            'dots' => true,
            'slides_to_show' => 4,
            'slides_to_scroll' => 1,
            'responsive' => true,
        ], $atts, 'digiplanet_product_carousel');
        
        $query_args = [
            'post_type' => 'digiplanet_product',
            'posts_per_page' => intval($atts['limit']),
            'orderby' => $atts['orderby'],
            'order' => $atts['order'],
        ];
        
        // Add filters (same as grid)
        if (!empty($atts['category'])) {
            $query_args['tax_query'][] = [
                'taxonomy' => 'digiplanet_category',
                'field' => 'slug',
                'terms' => explode(',', $atts['category']),
            ];
        }
        
        if (!empty($atts['tag'])) {
            $query_args['tax_query'][] = [
                'taxonomy' => 'digiplanet_tag',
                'field' => 'slug',
                'terms' => explode(',', $atts['tag']),
            ];
        }
        
        if (!empty($atts['ids'])) {
            $query_args['post__in'] = array_map('intval', explode(',', $atts['ids']));
        }
        
        if (!empty($atts['exclude'])) {
            $query_args['post__not_in'] = array_map('intval', explode(',', $atts['exclude']));
        }
        
        $products = new WP_Query($query_args);
        
        ob_start();
        
        if ($products->have_posts()) {
            $carousel_settings = [
                'autoplay' => filter_var($atts['autoplay'], FILTER_VALIDATE_BOOLEAN),
                'autoplaySpeed' => intval($atts['autoplay_speed']),
                'arrows' => filter_var($atts['arrows'], FILTER_VALIDATE_BOOLEAN),
                'dots' => filter_var($atts['dots'], FILTER_VALIDATE_BOOLEAN),
                'slidesToShow' => intval($atts['slides_to_show']),
                'slidesToScroll' => intval($atts['slides_to_scroll']),
                'responsive' => [
                    [
                        'breakpoint' => 1024,
                        'settings' => [
                            'slidesToShow' => min(3, intval($atts['slides_to_show'])),
                            'slidesToScroll' => 1,
                        ],
                    ],
                    [
                        'breakpoint' => 768,
                        'settings' => [
                            'slidesToShow' => min(2, intval($atts['slides_to_show'])),
                            'slidesToScroll' => 1,
                        ],
                    ],
                    [
                        'breakpoint' => 480,
                        'settings' => [
                            'slidesToShow' => 1,
                            'slidesToScroll' => 1,
                        ],
                    ],
                ],
            ];
            
            echo '<div class="digiplanet-product-carousel" data-settings=\'' . json_encode($carousel_settings) . '\'>';
            
            while ($products->have_posts()) {
                $products->the_post();
                echo '<div class="digiplanet-carousel-item">';
                $this->render_product_card(get_the_ID(), [
                    'show_price' => true,
                    'show_rating' => true,
                    'show_add_to_cart' => true,
                    'show_sale_badge' => true,
                    'show_category' => true,
                ]);
                echo '</div>';
            }
            
            echo '</div>';
            
            wp_reset_postdata();
        } else {
            echo '<div class="digiplanet-no-products">';
            echo '<p>' . __('No products found.', 'digiplanet-digital-products') . '</p>';
            echo '</div>';
        }
        
        return ob_get_clean();
    }
    
    /**
     * Product Categories Shortcode
     */
    public function product_categories_shortcode($atts) {
        $atts = shortcode_atts([
            'parent' => 0,
            'limit' => 0,
            'orderby' => 'name',
            'order' => 'ASC',
            'hide_empty' => true,
            'columns' => 4,
            'show_count' => true,
            'show_image' => true,
            'image_size' => 'medium',
        ], $atts, 'digiplanet_product_categories');
        
        $args = [
            'taxonomy' => 'digiplanet_category',
            'parent' => intval($atts['parent']),
            'orderby' => $atts['orderby'],
            'order' => $atts['order'],
            'hide_empty' => filter_var($atts['hide_empty'], FILTER_VALIDATE_BOOLEAN),
            'number' => intval($atts['limit']) > 0 ? intval($atts['limit']) : 0,
        ];
        
        $categories = get_terms($args);
        
        ob_start();
        
        if (!empty($categories) && !is_wp_error($categories)) {
            $columns = intval($atts['columns']);
            $column_class = 'digiplanet-col-' . $columns;
            
            echo '<div class="digiplanet-categories-grid ' . esc_attr($column_class) . '">';
            
            foreach ($categories as $category) {
                $this->render_category_card($category, $atts);
            }
            
            echo '</div>';
        } else {
            echo '<div class="digiplanet-no-categories">';
            echo '<p>' . __('No categories found.', 'digiplanet-digital-products') . '</p>';
            echo '</div>';
        }
        
        return ob_get_clean();
    }
    
    /**
     * Render Product Card
     */
    private function render_product_card($product_id, $atts) {
        $product = get_post($product_id);
        $price = get_post_meta($product_id, '_price', true);
        $sale_price = get_post_meta($product_id, '_sale_price', true);
        $regular_price = get_post_meta($product_id, '_regular_price', true);
        $featured = get_post_meta($product_id, '_featured', true) === 'yes';
        $rating = get_post_meta($product_id, '_average_rating', true);
        $review_count = get_post_meta($product_id, '_rating_count', true);
        $categories = wp_get_post_terms($product_id, 'digiplanet_category');
        $primary_category = !empty($categories) ? $categories[0] : null;
        $thumbnail_id = get_post_thumbnail_id($product_id);
        
        $is_on_sale = !empty($sale_price) && $sale_price < $regular_price;
        $display_price = $is_on_sale ? $sale_price : $price;
        ?>
        <div class="digiplanet-product-card <?php echo $featured ? 'digiplanet-featured-product' : ''; ?>">
            <div class="digiplanet-product-image">
                <a href="<?php echo get_permalink($product_id); ?>">
                    <?php if ($thumbnail_id): ?>
                        <?php echo wp_get_attachment_image($thumbnail_id, 'medium', false, [
                            'class' => 'digiplanet-product-thumbnail',
                            'loading' => 'lazy',
                        ]); ?>
                    <?php else: ?>
                        <div class="digiplanet-product-placeholder">
                            <span class="dashicons dashicons-format-image"></span>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($atts['show_sale_badge'] && $is_on_sale): ?>
                        <span class="digiplanet-sale-badge"><?php _e('Sale', 'digiplanet-digital-products'); ?></span>
                    <?php endif; ?>
                    
                    <?php if ($featured && $atts['show_sale_badge']): ?>
                        <span class="digiplanet-featured-badge"><?php _e('Featured', 'digiplanet-digital-products'); ?></span>
                    <?php endif; ?>
                </a>
                
                <?php if ($atts['show_add_to_cart']): ?>
                    <div class="digiplanet-product-actions">
                        <button class="digiplanet-add-to-cart" data-product-id="<?php echo esc_attr($product_id); ?>">
                            <span class="dashicons dashicons-cart"></span>
                            <?php _e('Add to Cart', 'digiplanet-digital-products'); ?>
                        </button>
                        <a href="<?php echo get_permalink($product_id); ?>" class="digiplanet-view-details">
                            <span class="dashicons dashicons-search"></span>
                            <?php _e('View Details', 'digiplanet-digital-products'); ?>
                        </a>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="digiplanet-product-content">
                <?php if ($atts['show_category'] && $primary_category): ?>
                    <div class="digiplanet-product-category">
                        <a href="<?php echo get_term_link($primary_category); ?>">
                            <?php echo esc_html($primary_category->name); ?>
                        </a>
                    </div>
                <?php endif; ?>
                
                <h3 class="digiplanet-product-title">
                    <a href="<?php echo get_permalink($product_id); ?>">
                        <?php echo esc_html($product->post_title); ?>
                    </a>
                </h3>
                
                <?php if ($atts['show_rating'] && $rating > 0): ?>
                    <div class="digiplanet-product-rating">
                        <div class="digiplanet-star-rating">
                            <?php
                            $full_stars = floor($rating);
                            $half_star = ceil($rating - $full_stars);
                            $empty_stars = 5 - $full_stars - $half_star;
                            
                            for ($i = 0; $i < $full_stars; $i++) {
                                echo '<span class="dashicons dashicons-star-filled"></span>';
                            }
                            
                            for ($i = 0; $i < $half_star; $i++) {
                                echo '<span class="dashicons dashicons-star-half"></span>';
                            }
                            
                            for ($i = 0; $i < $empty_stars; $i++) {
                                echo '<span class="dashicons dashicons-star-empty"></span>';
                            }
                            ?>
                        </div>
                        <?php if ($review_count > 0): ?>
                            <span class="digiplanet-review-count">
                                (<?php echo esc_html($review_count); ?>)
                            </span>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($atts['show_price']): ?>
                    <div class="digiplanet-product-price">
                        <span class="digiplanet-price <?php echo $is_on_sale ? 'digiplanet-sale-price' : ''; ?>">
                            <?php
                            $price_formatted = Digiplanet_Product_Manager::get_instance()->format_price($display_price);
                            echo $price_formatted;
                            ?>
                        </span>
                        
                        <?php if ($is_on_sale && $regular_price): ?>
                            <del class="digiplanet-regular-price">
                                <?php echo Digiplanet_Product_Manager::get_instance()->format_price($regular_price); ?>
                            </del>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
                
                <div class="digiplanet-product-excerpt">
                    <?php echo wp_trim_words(get_the_excerpt($product_id), 20, '...'); ?>
                </div>
            </div>
        </div>
        <?php
    }
    
    /**
     * Render Category Card
     */
    private function render_category_card($category, $atts) {
        $thumbnail_id = get_term_meta($category->term_id, 'thumbnail_id', true);
        $product_count = $category->count;
        $category_url = get_term_link($category);
        ?>
        <div class="digiplanet-category-card">
            <a href="<?php echo esc_url($category_url); ?>" class="digiplanet-category-link">
                <div class="digiplanet-category-image">
                    <?php if ($atts['show_image'] && $thumbnail_id): ?>
                        <?php echo wp_get_attachment_image($thumbnail_id, $atts['image_size'], false, [
                            'class' => 'digiplanet-category-thumbnail',
                            'loading' => 'lazy',
                        ]); ?>
                    <?php else: ?>
                        <div class="digiplanet-category-placeholder">
                            <span class="dashicons dashicons-category"></span>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="digiplanet-category-content">
                    <h3 class="digiplanet-category-title">
                        <?php echo esc_html($category->name); ?>
                    </h3>
                    
                    <?php if ($atts['show_count'] && $product_count > 0): ?>
                        <span class="digiplanet-category-count">
                            <?php 
                            printf(
                                _n('%s product', '%s products', $product_count, 'digiplanet-digital-products'),
                                number_format_i18n($product_count)
                            );
                            ?>
                        </span>
                    <?php endif; ?>
                    
                    <?php if (!empty($category->description)): ?>
                        <div class="digiplanet-category-description">
                            <?php echo wp_trim_words($category->description, 15, '...'); ?>
                        </div>
                    <?php endif; ?>
                </div>
            </a>
        </div>
        <?php
    }
    
    /**
     * Get products by category
     */
    public function get_products_by_category($category_slug, $limit = 12) {
        $args = [
            'post_type' => 'digiplanet_product',
            'posts_per_page' => $limit,
            'tax_query' => [
                [
                    'taxonomy' => 'digiplanet_category',
                    'field' => 'slug',
                    'terms' => $category_slug,
                ],
            ],
        ];
        
        return new WP_Query($args);
    }
    
    /**
     * Get products by tag
     */
    public function get_products_by_tag($tag_slug, $limit = 12) {
        $args = [
            'post_type' => 'digiplanet_product',
            'posts_per_page' => $limit,
            'tax_query' => [
                [
                    'taxonomy' => 'digiplanet_tag',
                    'field' => 'slug',
                    'terms' => $tag_slug,
                ],
            ],
        ];
        
        return new WP_Query($args);
    }
    
    /**
     * Get featured products
     */
    public function get_featured_products($limit = 8) {
        $args = [
            'post_type' => 'digiplanet_product',
            'posts_per_page' => $limit,
            'meta_query' => [
                [
                    'key' => '_featured',
                    'value' => 'yes',
                    'compare' => '=',
                ],
            ],
        ];
        
        return new WP_Query($args);
    }
    
    /**
     * Get on sale products
     */
    public function get_onsale_products($limit = 8) {
        $args = [
            'post_type' => 'digiplanet_product',
            'posts_per_page' => $limit,
            'meta_query' => [
                'relation' => 'OR',
                [
                    'key' => '_sale_price',
                    'value' => 0,
                    'compare' => '>',
                    'type' => 'NUMERIC',
                ],
                [
                    'key' => '_sale_price_dates_from',
                    'value' => current_time('timestamp'),
                    'compare' => '<=',
                    'type' => 'NUMERIC',
                ],
                [
                    'key' => '_sale_price_dates_to',
                    'value' => current_time('timestamp'),
                    'compare' => '>=',
                    'type' => 'NUMERIC',
                ],
            ],
        ];
        
        return new WP_Query($args);
    }
    
    /**
     * Get related products
     */
    public function get_related_products($product_id, $limit = 4) {
        $product = get_post($product_id);
        $categories = wp_get_post_terms($product_id, 'digiplanet_category', ['fields' => 'ids']);
        $tags = wp_get_post_terms($product_id, 'digiplanet_tag', ['fields' => 'ids']);
        
        if (empty($categories) && empty($tags)) {
            return new WP_Query();
        }
        
        $args = [
            'post_type' => 'digiplanet_product',
            'posts_per_page' => $limit,
            'post__not_in' => [$product_id],
            'tax_query' => [
                'relation' => 'OR',
            ],
        ];
        
        if (!empty($categories)) {
            $args['tax_query'][] = [
                'taxonomy' => 'digiplanet_category',
                'field' => 'term_id',
                'terms' => $categories,
            ];
        }
        
        if (!empty($tags)) {
            $args['tax_query'][] = [
                'taxonomy' => 'digiplanet_tag',
                'field' => 'term_id',
                'terms' => $tags,
            ];
        }
        
        return new WP_Query($args);
    }
}