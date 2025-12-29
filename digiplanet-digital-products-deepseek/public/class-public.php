<?php
/**
 * Public-facing functionality
 */

if (!defined('ABSPATH')) {
    exit;
}

class Digiplanet_Public {
    
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
        add_action('wp_enqueue_scripts', [$this, 'enqueue_scripts']);
        add_action('init', [$this, 'register_shortcodes']);
        add_action('template_redirect', [$this, 'handle_public_requests']);
        add_action('wp_head', [$this, 'add_opengraph_tags']);
        add_filter('the_content', [$this, 'maybe_display_product']);
    }
    
    /**
     * Enqueue scripts and styles
     */
    public function enqueue_scripts() {
        // CSS
        wp_enqueue_style(
            'digiplanet-frontend',
            DIGIPLANET_ASSETS_URL . 'css/frontend-style.css',
            [],
            DIGIPLANET_VERSION
        );
        
        wp_enqueue_style(
            'digiplanet-responsive',
            DIGIPLANET_ASSETS_URL . 'css/responsive.css',
            ['digiplanet-frontend'],
            DIGIPLANET_VERSION
        );
        
        // JavaScript
        wp_enqueue_script(
            'digiplanet-frontend',
            DIGIPLANET_ASSETS_URL . 'js/frontend-script.js',
            ['jquery'],
            DIGIPLANET_VERSION,
            true
        );
        
        wp_enqueue_script(
            'digiplanet-cart',
            DIGIPLANET_ASSETS_URL . 'js/cart.js',
            ['jquery', 'digiplanet-frontend'],
            DIGIPLANET_VERSION,
            true
        );
        
        // Localize script
        wp_localize_script('digiplanet-frontend', 'digiplanet_frontend', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('digiplanet_frontend_nonce'),
            'cart_url' => get_permalink(get_option('digiplanet_cart_page_id')),
            'checkout_url' => get_permalink(get_option('digiplanet_checkout_page_id')),
            'account_url' => get_permalink(get_option('digiplanet_account_page_id')),
            'home_url' => home_url('/'),
            'currency' => get_option('digiplanet_currency', 'USD'),
            'currency_symbol' => $this->get_currency_symbol(),
            'loading_text' => __('Loading...', 'digiplanet-digital-products'),
            'added_to_cart' => __('Added to cart!', 'digiplanet-digital-products'),
            'error_message' => __('An error occurred. Please try again.', 'digiplanet-digital-products'),
        ]);
    }
    
    /**
     * Register shortcodes
     */
    public function register_shortcodes() {
        add_shortcode('digiplanet_products', [$this, 'products_shortcode']);
        add_shortcode('digiplanet_cart', [$this, 'cart_shortcode']);
        add_shortcode('digiplanet_checkout', [$this, 'checkout_shortcode']);
        add_shortcode('digiplanet_account', [$this, 'account_shortcode']);
        add_shortcode('digiplanet_product_search', [$this, 'product_search_shortcode']);
        add_shortcode('digiplanet_product_categories', [$this, 'product_categories_shortcode']);
        add_shortcode('digiplanet_featured_products', [$this, 'featured_products_shortcode']);
    }
    
    /**
     * Handle public requests
     */
    public function handle_public_requests() {
        // Handle download requests
        if (isset($_GET['digiplanet_download'])) {
            $this->handle_download_request();
        }
        
        // Handle product single view
        if (is_singular('digiplanet_product')) {
            $this->handle_product_single_view();
        }
        
        // Handle product archive
        if (is_post_type_archive('digiplanet_product')) {
            $this->handle_product_archive();
        }
    }
    
    /**
     * Products shortcode
     */
    public function products_shortcode($atts) {
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
            'pagination' => false,
        ], $atts, 'digiplanet_products');
        
        // Get current page for pagination
        $paged = get_query_var('paged') ?: 1;
        $offset = $atts['pagination'] ? ($paged - 1) * $atts['limit'] : 0;
        
        // Build query args
        $args = [
            'limit' => $atts['limit'],
            'offset' => $offset,
            'orderby' => $atts['orderby'],
            'order' => $atts['order'],
        ];
        
        if ($atts['category']) {
            $args['category'] = $atts['category'];
        }
        
        if ($atts['tag']) {
            $args['tag'] = $atts['tag'];
        }
        
        if ($atts['ids']) {
            $args['ids'] = $atts['ids'];
        }
        
        if ($atts['exclude']) {
            $args['exclude'] = $atts['exclude'];
        }
        
        if ($atts['featured']) {
            $args['featured'] = true;
        }
        
        if ($atts['onsale']) {
            $args['onsale'] = true;
        }
        
        // Get products
        $product_manager = Digiplanet_Product_Manager::get_instance();
        $products = $product_manager->search_products($args);
        $total_products = count($products);
        
        // Start output buffering
        ob_start();
        
        // Include template
        if (file_exists(DIGIPLANET_PLUGIN_DIR . 'public/views/product-grid.php')) {
            include DIGIPLANET_PLUGIN_DIR . 'public/views/product-grid.php';
        } else {
            echo '<div class="digiplanet-alert digiplanet-alert-error">';
            echo __('Product grid template not found.', 'digiplanet-digital-products');
            echo '</div>';
        }
        
        return ob_get_clean();
    }
    
    /**
     * Cart shortcode
     */
    public function cart_shortcode($atts) {
        ob_start();
        
        if (file_exists(DIGIPLANET_PLUGIN_DIR . 'public/views/cart.php')) {
            include DIGIPLANET_PLUGIN_DIR . 'public/views/cart.php';
        } else {
            echo '<div class="digiplanet-alert digiplanet-alert-error">';
            echo __('Cart template not found.', 'digiplanet-digital-products');
            echo '</div>';
        }
        
        return ob_get_clean();
    }
    
    /**
     * Checkout shortcode
     */
    public function checkout_shortcode($atts) {
        // Check if user is logged in for checkout
        if (!is_user_logged_in() && get_option('woocommerce_enable_guest_checkout') !== 'yes') {
            return $this->get_login_required_message();
        }
        
        ob_start();
        
        if (file_exists(DIGIPLANET_PLUGIN_DIR . 'public/views/checkout.php')) {
            include DIGIPLANET_PLUGIN_DIR . 'public/views/checkout.php';
        } else {
            echo '<div class="digiplanet-alert digiplanet-alert-error">';
            echo __('Checkout template not found.', 'digiplanet-digital-products');
            echo '</div>';
        }
        
        return ob_get_clean();
    }
    
    /**
     * Account shortcode
     */
    public function account_shortcode($atts) {
        if (!is_user_logged_in()) {
            return $this->get_login_form();
        }
        
        ob_start();
        
        if (file_exists(DIGIPLANET_PLUGIN_DIR . 'public/views/account.php')) {
            include DIGIPLANET_PLUGIN_DIR . 'public/views/account.php';
        } else {
            echo '<div class="digiplanet-alert digiplanet-alert-error">';
            echo __('Account template not found.', 'digiplanet-digital-products');
            echo '</div>';
        }
        
        return ob_get_clean();
    }
    
    /**
     * Product search shortcode
     */
    public function product_search_shortcode($atts) {
        $atts = shortcode_atts([
            'placeholder' => __('Search products...', 'digiplanet-digital-products'),
            'button_text' => __('Search', 'digiplanet-digital-products'),
            'ajax' => true,
        ], $atts, 'digiplanet_product_search');
        
        ob_start();
        ?>
        <div class="digiplanet-product-search">
            <form class="digiplanet-product-search-form" method="get" action="<?php echo home_url('/'); ?>">
                <input type="hidden" name="post_type" value="digiplanet_product">
                <div class="digiplanet-search-wrapper">
                    <input type="text" 
                           name="s" 
                           class="digiplanet-search-input" 
                           placeholder="<?php echo esc_attr($atts['placeholder']); ?>"
                           value="<?php echo get_search_query(); ?>">
                    <button type="submit" class="digiplanet-search-button">
                        <?php echo esc_html($atts['button_text']); ?>
                    </button>
                </div>
            </form>
            <?php if ($atts['ajax']): ?>
                <div class="digiplanet-search-results"></div>
            <?php endif; ?>
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Product categories shortcode
     */
    public function product_categories_shortcode($atts) {
        $atts = shortcode_atts([
            'parent' => 0,
            'columns' => 4,
            'hide_empty' => false,
            'show_count' => true,
            'show_image' => true,
        ], $atts, 'digiplanet_product_categories');
        
        $product_manager = Digiplanet_Product_Manager::get_instance();
        $categories = $product_manager->get_categories($atts['parent']);
        
        ob_start();
        
        if (file_exists(DIGIPLANET_PLUGIN_DIR . 'public/views/product-categories.php')) {
            include DIGIPLANET_PLUGIN_DIR . 'public/views/product-categories.php';
        } else {
            echo '<div class="digiplanet-categories-grid digiplanet-cols-' . $atts['columns'] . '">';
            foreach ($categories as $category) {
                echo '<div class="digiplanet-category-item">';
                echo '<h3>' . esc_html($category->name) . '</h3>';
                if ($atts['show_count']) {
                    echo '<span class="digiplanet-category-count">' . $category->product_count . ' ' . __('products', 'digiplanet-digital-products') . '</span>';
                }
                echo '</div>';
            }
            echo '</div>';
        }
        
        return ob_get_clean();
    }
    
    /**
     * Featured products shortcode
     */
    public function featured_products_shortcode($atts) {
        $atts = shortcode_atts([
            'limit' => 8,
            'columns' => 4,
            'title' => __('Featured Products', 'digiplanet-digital-products'),
        ], $atts, 'digiplanet_featured_products');
        
        $product_manager = Digiplanet_Product_Manager::get_instance();
        $products = $product_manager->get_featured_products($atts['limit']);
        
        ob_start();
        ?>
        <div class="digiplanet-featured-products">
            <?php if ($atts['title']): ?>
                <h2 class="digiplanet-section-title"><?php echo esc_html($atts['title']); ?></h2>
            <?php endif; ?>
            
            <div class="digiplanet-product-grid digiplanet-cols-<?php echo $atts['columns']; ?>">
                <?php foreach ($products as $product): ?>
                    <?php echo $this->get_product_card_html($product); ?>
                <?php endforeach; ?>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Handle download request
     */
    private function handle_download_request() {
        $order_item_id = intval($_GET['digiplanet_download'] ?? 0);
        $user_id = intval($_GET['user_id'] ?? 0);
        $token = $_GET['token'] ?? '';
        
        if (!$order_item_id || !$user_id || !$token) {
            wp_die(__('Invalid download request.', 'digiplanet-digital-products'));
        }
        
        $download_manager = Digiplanet_Download_Manager::get_instance();
        $download_manager->process_download($order_item_id, $user_id, $token);
    }
    
    /**
     * Handle product single view
     */
    private function handle_product_single_view() {
        global $post;
        
        // Increment view count
        $product_id = get_post_meta($post->ID, '_digiplanet_product_id', true);
        if ($product_id) {
            $product_manager = Digiplanet_Product_Manager::get_instance();
            $product_manager->increment_view_count($product_id);
        }
        
        // Add structured data
        add_action('wp_head', function() use ($product_id) {
            $this->output_product_structured_data($product_id);
        });
    }
    
    /**
     * Handle product archive
     */
    private function handle_product_archive() {
        // Add archive structured data
        add_action('wp_head', function() {
            $this->output_archive_structured_data();
        });
    }
    
    /**
     * Add OpenGraph tags
     */
    public function add_opengraph_tags() {
        if (is_singular('digiplanet_product')) {
            global $post;
            $product_id = get_post_meta($post->ID, '_digiplanet_product_id', true);
            
            if ($product_id) {
                $product_manager = Digiplanet_Product_Manager::get_instance();
                $product = $product_manager->get_product($product_id);
                
                if ($product) {
                    echo '<meta property="og:title" content="' . esc_attr($product->name) . '">';
                    echo '<meta property="og:description" content="' . esc_attr(wp_strip_all_tags($product->short_description)) . '">';
                    echo '<meta property="og:type" content="product">';
                    
                    if ($product->featured_image_id) {
                        $image_url = wp_get_attachment_url($product->featured_image_id);
                        echo '<meta property="og:image" content="' . esc_url($image_url) . '">';
                    }
                    
                    echo '<meta property="product:price:amount" content="' . esc_attr($product->sale_price ?: $product->price) . '">';
                    echo '<meta property="product:price:currency" content="' . esc_attr(get_option('digiplanet_currency', 'USD')) . '">';
                }
            }
        }
    }
    
    /**
     * Maybe display product on single page
     */
    public function maybe_display_product($content) {
        if (is_singular('digiplanet_product') && in_the_loop() && is_main_query()) {
            global $post;
            $product_id = get_post_meta($post->ID, '_digiplanet_product_id', true);
            
            if ($product_id) {
                ob_start();
                include DIGIPLANET_PLUGIN_DIR . 'public/views/product-detail.php';
                $product_content = ob_get_clean();
                
                return $product_content . $content;
            }
        }
        
        return $content;
    }
    
    /**
     * Get product card HTML
     */
    private function get_product_card_html($product) {
        $product_manager = Digiplanet_Product_Manager::get_instance();
        $price_html = $product_manager->format_price($product->price, $product->sale_price);
        
        ob_start();
        ?>
        <div class="digiplanet-product-card">
            <div class="digiplanet-product-image">
                <a href="<?php echo home_url('/digital-product/' . $product->slug); ?>">
                    <?php if ($product->featured_image_id): ?>
                        <?php echo wp_get_attachment_image($product->featured_image_id, 'medium'); ?>
                    <?php else: ?>
                        <img src="<?php echo DIGIPLANET_ASSETS_URL . 'images/placeholder-product.png'; ?>" alt="<?php echo esc_attr($product->name); ?>">
                    <?php endif; ?>
                </a>
                
                <?php if ($product->sale_price): ?>
                    <span class="digiplanet-product-badge digiplanet-badge-sale">
                        <?php echo $product_manager->calculate_sale_percentage($product->price, $product->sale_price); ?>% OFF
                    </span>
                <?php endif; ?>
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
            
            <div class="digiplanet-product-meta">
                <?php if ($product->rating > 0): ?>
                    <div class="digiplanet-rating">
                        <span class="digiplanet-rating-stars">
                            <?php echo str_repeat('★', floor($product->rating)); ?>
                            <?php echo str_repeat('☆', 5 - floor($product->rating)); ?>
                        </span>
                        <span class="digiplanet-rating-number">(<?php echo $product->rating; ?>)</span>
                    </div>
                <?php endif; ?>
                
                <?php if ($product->sales_count > 0): ?>
                    <div class="digiplanet-sales-count">
                        <span class="dashicons dashicons-chart-area"></span>
                        <?php echo number_format($product->sales_count); ?> <?php _e('sold', 'digiplanet-digital-products'); ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Get login required message
     */
    private function get_login_required_message() {
        ob_start();
        ?>
        <div class="digiplanet-login-required">
            <h2><?php _e('Login Required', 'digiplanet-digital-products'); ?></h2>
            <p><?php _e('You need to be logged in to proceed to checkout.', 'digiplanet-digital-products'); ?></p>
            <div class="digiplanet-login-links">
                <a href="<?php echo wp_login_url(get_permalink(get_option('digiplanet_checkout_page_id'))); ?>" class="digiplanet-btn digiplanet-btn-primary">
                    <?php _e('Login', 'digiplanet-digital-products'); ?>
                </a>
                <?php if (get_option('users_can_register')): ?>
                    <a href="<?php echo wp_registration_url(); ?>" class="digiplanet-btn digiplanet-btn-secondary">
                        <?php _e('Register', 'digiplanet-digital-products'); ?>
                    </a>
                <?php endif; ?>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Get login form
     */
    private function get_login_form() {
        ob_start();
        include DIGIPLANET_TEMPLATES_DIR . 'account/login.php';
        return ob_get_clean();
    }
    
    /**
     * Get currency symbol
     */
    private function get_currency_symbol() {
        $currency = get_option('digiplanet_currency', 'USD');
        $symbols = [
            'USD' => '$',
            'EUR' => '€',
            'GBP' => '£',
            'NGN' => '₦',
            'CAD' => 'C$',
            'AUD' => 'A$',
            'JPY' => '¥',
        ];
        
        return $symbols[$currency] ?? $currency;
    }
    
    /**
     * Output product structured data
     */
    private function output_product_structured_data($product_id) {
        $product_manager = Digiplanet_Product_Manager::get_instance();
        $product = $product_manager->get_product($product_id);
        
        if (!$product) {
            return;
        }
        
        $structured_data = [
            '@context' => 'https://schema.org',
            '@type' => 'Product',
            'name' => $product->name,
            'description' => wp_strip_all_tags($product->short_description),
            'sku' => $product->sku,
            'url' => home_url('/digital-product/' . $product->slug),
            'brand' => [
                '@type' => 'Brand',
                'name' => get_bloginfo('name')
            ],
            'offers' => [
                '@type' => 'Offer',
                'price' => $product->sale_price ?: $product->price,
                'priceCurrency' => get_option('digiplanet_currency', 'USD'),
                'availability' => 'https://schema.org/InStock',
                'url' => home_url('/digital-product/' . $product->slug),
            ]
        ];
        
        if ($product->rating > 0) {
            $structured_data['aggregateRating'] = [
                '@type' => 'AggregateRating',
                'ratingValue' => $product->rating,
                'reviewCount' => $product->review_count,
            ];
        }
        
        if ($product->featured_image_id) {
            $image_url = wp_get_attachment_url($product->featured_image_id);
            $structured_data['image'] = $image_url;
        }
        
        echo '<script type="application/ld+json">' . wp_json_encode($structured_data) . '</script>';
    }
    
    /**
     * Output archive structured data
     */
    private function output_archive_structured_data() {
        $structured_data = [
            '@context' => 'https://schema.org',
            '@type' => 'ItemList',
            'itemListElement' => [],
        ];
        
        // Get featured products for the list
        $product_manager = Digiplanet_Product_Manager::get_instance();
        $products = $product_manager->get_featured_products(5);
        
        foreach ($products as $index => $product) {
            $structured_data['itemListElement'][] = [
                '@type' => 'ListItem',
                'position' => $index + 1,
                'item' => [
                    '@type' => 'Product',
                    'name' => $product->name,
                    'url' => home_url('/digital-product/' . $product->slug),
                    'image' => $product->featured_image_id ? wp_get_attachment_url($product->featured_image_id) : '',
                    'offers' => [
                        '@type' => 'Offer',
                        'price' => $product->sale_price ?: $product->price,
                        'priceCurrency' => get_option('digiplanet_currency', 'USD'),
                    ]
                ]
            ];
        }
        
        echo '<script type="application/ld+json">' . wp_json_encode($structured_data) . '</script>';
    }
}