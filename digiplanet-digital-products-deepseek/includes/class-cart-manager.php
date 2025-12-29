<?php
/**
 * Shopping cart management class
 */

if (!defined('ABSPATH')) {
    exit;
}

class Digiplanet_Cart_Manager {
    
    private static $instance = null;
    private $cart_session_key = 'digiplanet_cart';
    
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
        $this->init_session();
        add_action('init', [$this, 'maybe_clear_cart']);
    }
    
    /**
     * Initialize session
     */
    private function init_session() {
        if (!session_id() && !headers_sent()) {
            session_start();
        }
    }
    
    /**
     * Get cart contents
     */
    public function get_cart() {
        $cart = isset($_SESSION[$this->cart_session_key]) ? $_SESSION[$this->cart_session_key] : [];
        return $this->validate_cart_items($cart);
    }
    
    /**
     * Add item to cart
     */
    public function add_to_cart($product_id, $quantity = 1) {
        $product = Digiplanet_Product_Manager::get_instance()->get_product($product_id);
        
        if (!$product) {
            return [
                'success' => false,
                'message' => __('Product not found.', 'digiplanet-digital-products')
            ];
        }
        
        $cart = $this->get_cart();
        
        // Check if product already in cart
        $found = false;
        foreach ($cart as &$item) {
            if ($item['product_id'] == $product_id) {
                $item['quantity'] += $quantity;
                $found = true;
                break;
            }
        }
        
        if (!$found) {
            $cart[] = [
                'product_id' => $product_id,
                'quantity' => $quantity,
                'price' => $product->sale_price ?: $product->price,
                'name' => $product->name,
                'slug' => $product->slug,
                'thumbnail' => wp_get_attachment_url($product->featured_image_id)
            ];
        }
        
        $_SESSION[$this->cart_session_key] = $cart;
        
        return [
            'success' => true,
            'message' => __('Product added to cart.', 'digiplanet-digital-products'),
            'cart_count' => $this->get_cart_count(),
            'cart_total' => $this->get_cart_total()
        ];
    }
    
    /**
     * Remove item from cart
     */
    public function remove_from_cart($product_id) {
        $cart = $this->get_cart();
        
        foreach ($cart as $key => $item) {
            if ($item['product_id'] == $product_id) {
                unset($cart[$key]);
                break;
            }
        }
        
        $_SESSION[$this->cart_session_key] = array_values($cart);
        
        return [
            'success' => true,
            'message' => __('Product removed from cart.', 'digiplanet-digital-products'),
            'cart_count' => $this->get_cart_count(),
            'cart_total' => $this->get_cart_total()
        ];
    }
    
    /**
     * Update cart item quantity
     */
    public function update_cart_item($product_id, $quantity) {
        if ($quantity < 1) {
            return $this->remove_from_cart($product_id);
        }
        
        $cart = $this->get_cart();
        
        foreach ($cart as &$item) {
            if ($item['product_id'] == $product_id) {
                $item['quantity'] = $quantity;
                break;
            }
        }
        
        $_SESSION[$this->cart_session_key] = $cart;
        
        return [
            'success' => true,
            'message' => __('Cart updated.', 'digiplanet-digital-products'),
            'cart_count' => $this->get_cart_count(),
            'cart_total' => $this->get_cart_total()
        ];
    }
    
    /**
     * Clear cart
     */
    public function clear_cart() {
        unset($_SESSION[$this->cart_session_key]);
        return ['success' => true, 'message' => __('Cart cleared.', 'digiplanet-digital-products')];
    }
    
    /**
     * Get cart count
     */
    public function get_cart_count() {
        $cart = $this->get_cart();
        $count = 0;
        
        foreach ($cart as $item) {
            $count += $item['quantity'];
        }
        
        return $count;
    }
    
    /**
     * Get cart total
     */
    public function get_cart_total() {
        $cart = $this->get_cart();
        $total = 0;
        
        foreach ($cart as $item) {
            $total += $item['price'] * $item['quantity'];
        }
        
        return $total;
    }
    
    /**
     * Get cart subtotal (before tax)
     */
    public function get_cart_subtotal() {
        return $this->get_cart_total();
    }
    
    /**
     * Calculate tax
     */
    public function calculate_tax($amount) {
        $tax_rate = floatval(get_option('digiplanet_tax_rate', 0));
        $enable_tax = get_option('digiplanet_enable_tax', 'no') === 'yes';
        
        if (!$enable_tax || $tax_rate <= 0) {
            return 0;
        }
        
        return ($amount * $tax_rate) / 100;
    }
    
    /**
     * Get cart tax
     */
    public function get_cart_tax() {
        $subtotal = $this->get_cart_subtotal();
        return $this->calculate_tax($subtotal);
    }
    
    /**
     * Get cart total with tax
     */
    public function get_cart_total_with_tax() {
        $subtotal = $this->get_cart_subtotal();
        $tax = $this->get_cart_tax();
        return $subtotal + $tax;
    }
    
    /**
     * Validate cart items
     */
    private function validate_cart_items($cart) {
        $valid_cart = [];
        
        foreach ($cart as $item) {
            $product = Digiplanet_Product_Manager::get_instance()->get_product($item['product_id']);
            
            if ($product && $product->status === 'published') {
                $item['price'] = $product->sale_price ?: $product->price;
                $item['name'] = $product->name;
                $valid_cart[] = $item;
            }
        }
        
        return $valid_cart;
    }
    
    /**
     * Maybe clear cart on order completion
     */
    public function maybe_clear_cart() {
        if (isset($_GET['order_complete']) && isset($_GET['clear_cart'])) {
            $this->clear_cart();
        }
    }
    
    /**
     * Get cart HTML for display
     */
    public function get_cart_html() {
        $cart = $this->get_cart();
        $total = $this->get_cart_total_with_tax();
        $tax = $this->get_cart_tax();
        $subtotal = $this->get_cart_subtotal();
        
        ob_start();
        ?>
        <div class="digiplanet-cart">
            <?php if (empty($cart)): ?>
                <div class="digiplanet-cart-empty">
                    <p><?php _e('Your cart is empty.', 'digiplanet-digital-products'); ?></p>
                    <a href="<?php echo get_permalink(get_option('digiplanet_products_page_id')); ?>" class="digiplanet-btn digiplanet-btn-primary">
                        <?php _e('Browse Products', 'digiplanet-digital-products'); ?>
                    </a>
                </div>
            <?php else: ?>
                <div class="digiplanet-cart-items">
                    <table class="digiplanet-cart-table">
                        <thead>
                            <tr>
                                <th class="product-thumbnail"><?php _e('Product', 'digiplanet-digital-products'); ?></th>
                                <th class="product-name"><?php _e('Name', 'digiplanet-digital-products'); ?></th>
                                <th class="product-price"><?php _e('Price', 'digiplanet-digital-products'); ?></th>
                                <th class="product-quantity"><?php _e('Quantity', 'digiplanet-digital-products'); ?></th>
                                <th class="product-subtotal"><?php _e('Subtotal', 'digiplanet-digital-products'); ?></th>
                                <th class="product-remove"><?php _e('Remove', 'digiplanet-digital-products'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($cart as $item): ?>
                                <?php
                                $product = Digiplanet_Product_Manager::get_instance()->get_product($item['product_id']);
                                $item_total = $item['price'] * $item['quantity'];
                                ?>
                                <tr class="digiplanet-cart-item" data-product-id="<?php echo esc_attr($item['product_id']); ?>">
                                    <td class="product-thumbnail">
                                        <?php if ($item['thumbnail']): ?>
                                            <img src="<?php echo esc_url($item['thumbnail']); ?>" alt="<?php echo esc_attr($item['name']); ?>">
                                        <?php endif; ?>
                                    </td>
                                    <td class="product-name">
                                        <a href="<?php echo home_url('/digital-product/' . $item['slug']); ?>">
                                            <?php echo esc_html($item['name']); ?>
                                        </a>
                                    </td>
                                    <td class="product-price">
                                        <?php echo Digiplanet_Product_Manager::get_instance()->format_price($item['price']); ?>
                                    </td>
                                    <td class="product-quantity">
                                        <div class="digiplanet-quantity-selector">
                                            <button type="button" class="digiplanet-qty-minus" data-product-id="<?php echo esc_attr($item['product_id']); ?>">-</button>
                                            <input type="number" class="digiplanet-qty-input" value="<?php echo esc_attr($item['quantity']); ?>" min="1" data-product-id="<?php echo esc_attr($item['product_id']); ?>">
                                            <button type="button" class="digiplanet-qty-plus" data-product-id="<?php echo esc_attr($item['product_id']); ?>">+</button>
                                        </div>
                                    </td>
                                    <td class="product-subtotal">
                                        <?php echo Digiplanet_Product_Manager::get_instance()->format_price($item_total); ?>
                                    </td>
                                    <td class="product-remove">
                                        <button type="button" class="digiplanet-remove-item" data-product-id="<?php echo esc_attr($item['product_id']); ?>">
                                            <span class="dashicons dashicons-trash"></span>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <div class="digiplanet-cart-totals">
                    <h3><?php _e('Cart Totals', 'digiplanet-digital-products'); ?></h3>
                    <table class="digiplanet-totals-table">
                        <tr>
                            <th><?php _e('Subtotal', 'digiplanet-digital-products'); ?></th>
                            <td><?php echo Digiplanet_Product_Manager::get_instance()->format_price($subtotal); ?></td>
                        </tr>
                        <?php if ($tax > 0): ?>
                            <tr>
                                <th><?php _e('Tax', 'digiplanet-digital-products'); ?></th>
                                <td><?php echo Digiplanet_Product_Manager::get_instance()->format_price($tax); ?></td>
                            </tr>
                        <?php endif; ?>
                        <tr class="digiplanet-total-row">
                            <th><?php _e('Total', 'digiplanet-digital-products'); ?></th>
                            <td><?php echo Digiplanet_Product_Manager::get_instance()->format_price($total); ?></td>
                        </tr>
                    </table>
                    
                    <div class="digiplanet-cart-actions">
                        <a href="<?php echo get_permalink(get_option('digiplanet_products_page_id')); ?>" class="digiplanet-btn digiplanet-btn-secondary">
                            <?php _e('Continue Shopping', 'digiplanet-digital-products'); ?>
                        </a>
                        <a href="<?php echo get_permalink(get_option('digiplanet_checkout_page_id')); ?>" class="digiplanet-btn digiplanet-btn-primary">
                            <?php _e('Proceed to Checkout', 'digiplanet-digital-products'); ?>
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Get mini cart HTML
     */
    public function get_mini_cart_html() {
        $cart = $this->get_cart();
        $count = $this->get_cart_count();
        $total = $this->get_cart_total_with_tax();
        
        ob_start();
        ?>
        <div class="digiplanet-mini-cart">
            <button class="digiplanet-mini-cart-toggle">
                <span class="digiplanet-cart-icon">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path>
                        <line x1="3" y1="6" x2="21" y2="6"></line>
                        <path d="M16 10a4 4 0 0 1-8 0"></path>
                    </svg>
                    <?php if ($count > 0): ?>
                        <span class="digiplanet-cart-count"><?php echo $count; ?></span>
                    <?php endif; ?>
                </span>
                <span class="digiplanet-cart-total"><?php echo Digiplanet_Product_Manager::get_instance()->format_price($total); ?></span>
            </button>
            
            <div class="digiplanet-mini-cart-dropdown">
                <?php if (empty($cart)): ?>
                    <p class="digiplanet-mini-cart-empty"><?php _e('Your cart is empty.', 'digiplanet-digital-products'); ?></p>
                <?php else: ?>
                    <div class="digiplanet-mini-cart-items">
                        <?php foreach ($cart as $item): ?>
                            <div class="digiplanet-mini-cart-item">
                                <div class="digiplanet-mini-cart-item-thumbnail">
                                    <?php if ($item['thumbnail']): ?>
                                        <img src="<?php echo esc_url($item['thumbnail']); ?>" alt="<?php echo esc_attr($item['name']); ?>">
                                    <?php endif; ?>
                                </div>
                                <div class="digiplanet-mini-cart-item-details">
                                    <h4><?php echo esc_html($item['name']); ?></h4>
                                    <p><?php echo Digiplanet_Product_Manager::get_instance()->format_price($item['price']); ?> × <?php echo $item['quantity']; ?></p>
                                </div>
                                <button class="digiplanet-mini-cart-remove" data-product-id="<?php echo esc_attr($item['product_id']); ?>">
                                    <span class="dashicons dashicons-no-alt"></span>
                                </button>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="digiplanet-mini-cart-total">
                        <strong><?php _e('Total:', 'digiplanet-digital-products'); ?></strong>
                        <span><?php echo Digiplanet_Product_Manager::get_instance()->format_price($total); ?></span>
                    </div>
                    <div class="digiplanet-mini-cart-actions">
                        <a href="<?php echo get_permalink(get_option('digiplanet_cart_page_id')); ?>" class="digiplanet-btn digiplanet-btn-secondary">
                            <?php _e('View Cart', 'digiplanet-digital-products'); ?>
                        </a>
                        <a href="<?php echo get_permalink(get_option('digiplanet_checkout_page_id')); ?>" class="digiplanet-btn digiplanet-btn-primary">
                            <?php _e('Checkout', 'digiplanet-digital-products'); ?>
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
}