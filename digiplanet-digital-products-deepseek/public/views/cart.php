<?php
/**
 * Cart template
 */

if (!defined('ABSPATH')) {
    exit;
}

$cart_manager = Digiplanet_Cart_Manager::get_instance();
$cart = $cart_manager->get_cart();
$subtotal = $cart_manager->get_cart_subtotal();
$tax = $cart_manager->get_cart_tax();
$total = $cart_manager->get_cart_total_with_tax();
?>

<div class="digiplanet-cart">
    <h1 class="digiplanet-cart-title"><?php _e('Shopping Cart', 'digiplanet-digital-products'); ?></h1>
    
    <?php if (empty($cart)): ?>
        <div class="digiplanet-cart-empty">
            <div class="digiplanet-cart-empty-icon">
                <span class="dashicons dashicons-cart"></span>
            </div>
            <h2><?php _e('Your cart is empty', 'digiplanet-digital-products'); ?></h2>
            <p><?php _e('Looks like you haven\'t added any products to your cart yet.', 'digiplanet-digital-products'); ?></p>
            <a href="<?php echo get_permalink(get_option('digiplanet_products_page_id')); ?>" class="digiplanet-btn digiplanet-btn-primary">
                <?php _e('Browse Products', 'digiplanet-digital-products'); ?>
            </a>
        </div>
    <?php else: ?>
        <div class="digiplanet-cart-content">
            <!-- Cart Items -->
            <div class="digiplanet-cart-items">
                <table class="digiplanet-cart-table">
                    <thead>
                        <tr>
                            <th class="product-remove">&nbsp;</th>
                            <th class="product-thumbnail">&nbsp;</th>
                            <th class="product-name"><?php _e('Product', 'digiplanet-digital-products'); ?></th>
                            <th class="product-price"><?php _e('Price', 'digiplanet-digital-products'); ?></th>
                            <th class="product-quantity"><?php _e('Quantity', 'digiplanet-digital-products'); ?></th>
                            <th class="product-subtotal"><?php _e('Subtotal', 'digiplanet-digital-products'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $product_manager = Digiplanet_Product_Manager::get_instance();
                        foreach ($cart as $item): 
                            $item_total = $item['price'] * $item['quantity'];
                        ?>
                            <tr class="digiplanet-cart-item" data-product-id="<?php echo $item['product_id']; ?>">
                                <td class="product-remove">
                                    <button type="button" class="digiplanet-remove-item" data-product-id="<?php echo $item['product_id']; ?>" title="<?php _e('Remove this item', 'digiplanet-digital-products'); ?>">
                                        <span class="dashicons dashicons-trash"></span>
                                    </button>
                                </td>
                                
                                <td class="product-thumbnail">
                                    <?php if ($item['thumbnail']): ?>
                                        <a href="<?php echo home_url('/digital-product/' . $item['slug']); ?>">
                                            <img src="<?php echo esc_url($item['thumbnail']); ?>" alt="<?php echo esc_attr($item['name']); ?>">
                                        </a>
                                    <?php endif; ?>
                                </td>
                                
                                <td class="product-name">
                                    <a href="<?php echo home_url('/digital-product/' . $item['slug']); ?>">
                                        <?php echo esc_html($item['name']); ?>
                                    </a>
                                </td>
                                
                                <td class="product-price">
                                    <?php echo $product_manager->format_price($item['price']); ?>
                                </td>
                                
                                <td class="product-quantity">
                                    <div class="digiplanet-quantity-selector">
                                        <button type="button" class="digiplanet-qty-minus" data-product-id="<?php echo $item['product_id']; ?>">-</button>
                                        <input type="number" 
                                               class="digiplanet-qty-input" 
                                               value="<?php echo $item['quantity']; ?>" 
                                               min="1"
                                               data-product-id="<?php echo $item['product_id']; ?>">
                                        <button type="button" class="digiplanet-qty-plus" data-product-id="<?php echo $item['product_id']; ?>">+</button>
                                    </div>
                                </td>
                                
                                <td class="product-subtotal">
                                    <?php echo $product_manager->format_price($item_total); ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                
                <!-- Cart Actions -->
                <div class="digiplanet-cart-actions">
                    <a href="<?php echo get_permalink(get_option('digiplanet_products_page_id')); ?>" class="digiplanet-btn digiplanet-btn-secondary">
                        <span class="dashicons dashicons-arrow-left-alt"></span>
                        <?php _e('Continue Shopping', 'digiplanet-digital-products'); ?>
                    </a>
                    
                    <button type="button" class="digiplanet-btn digiplanet-btn-secondary digiplanet-update-cart">
                        <?php _e('Update Cart', 'digiplanet-digital-products'); ?>
                    </button>
                    
                    <button type="button" class="digiplanet-btn digiplanet-btn-secondary digiplanet-clear-cart">
                        <?php _e('Clear Cart', 'digiplanet-digital-products'); ?>
                    </button>
                </div>
            </div>
            
            <!-- Cart Totals -->
            <div class="digiplanet-cart-totals">
                <h2><?php _e('Cart Totals', 'digiplanet-digital-products'); ?></h2>
                
                <table class="digiplanet-totals-table">
                    <tr>
                        <th><?php _e('Subtotal', 'digiplanet-digital-products'); ?></th>
                        <td><?php echo $product_manager->format_price($subtotal); ?></td>
                    </tr>
                    
                    <?php if ($tax > 0): ?>
                        <tr>
                            <th><?php _e('Tax', 'digiplanet-digital-products'); ?></th>
                            <td><?php echo $product_manager->format_price($tax); ?></td>
                        </tr>
                    <?php endif; ?>
                    
                    <tr class="digiplanet-total-row">
                        <th><?php _e('Total', 'digiplanet-digital-products'); ?></th>
                        <td><?php echo $product_manager->format_price($total); ?></td>
                    </tr>
                </table>
                
                <!-- Coupon Code -->
                <div class="digiplanet-coupon-section">
                    <h3><?php _e('Coupon Code', 'digiplanet-digital-products'); ?></h3>
                    <div class="digiplanet-coupon-form">
                        <input type="text" 
                               class="digiplanet-coupon-input" 
                               placeholder="<?php _e('Enter coupon code', 'digiplanet-digital-products'); ?>">
                        <button type="button" class="digiplanet-btn digiplanet-btn-secondary digiplanet-apply-coupon">
                            <?php _e('Apply', 'digiplanet-digital-products'); ?>
                        </button>
                    </div>
                    <div class="digiplanet-coupon-message"></div>
                </div>
                
                <!-- Checkout Button -->
                <div class="digiplanet-checkout-button">
                    <a href="<?php echo get_permalink(get_option('digiplanet_checkout_page_id')); ?>" class="digiplanet-btn digiplanet-btn-primary digiplanet-proceed-checkout">
                        <?php _e('Proceed to Checkout', 'digiplanet-digital-products'); ?>
                        <span class="dashicons dashicons-arrow-right-alt"></span>
                    </a>
                </div>
                
                <!-- Security Badges -->
                <div class="digiplanet-security-badges">
                    <div class="digiplanet-security-badge">
                        <span class="dashicons dashicons-lock"></span>
                        <span><?php _e('Secure Checkout', 'digiplanet-digital-products'); ?></span>
                    </div>
                    <div class="digiplanet-security-badge">
                        <span class="dashicons dashicons-shield"></span>
                        <span><?php _e('SSL Encrypted', 'digiplanet-digital-products'); ?></span>
                    </div>
                    <div class="digiplanet-security-badge">
                        <span class="dashicons dashicons-yes"></span>
                        <span><?php _e('Money Back Guarantee', 'digiplanet-digital-products'); ?></span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Cross-sell Products -->
        <div class="digiplanet-cross-sell">
            <h2><?php _e('You Might Also Like', 'digiplanet-digital-products'); ?></h2>
            <?php echo do_shortcode('[digiplanet_featured_products limit="4" columns="4"]'); ?>
        </div>
    <?php endif; ?>
</div>

<style>
.digiplanet-cart {
    max-width: 1200px;
    margin: 0 auto;
    padding: 30px 20px;
}

.digiplanet-cart-title {
    margin-bottom: 30px;
    text-align: center;
    color: #333;
}

.digiplanet-cart-empty {
    text-align: center;
    padding: 60px 20px;
    background: #f8f9fa;
    border-radius: 8px;
}

.digiplanet-cart-empty-icon {
    font-size: 60px;
    color: #ccc;
    margin-bottom: 20px;
}

.digiplanet-cart-empty h2 {
    margin-bottom: 15px;
    color: #666;
}

.digiplanet-cart-content {
    display: grid;
    grid-template-columns: 1fr 350px;
    gap: 40px;
}

.digiplanet-cart-table {
    width: 100%;
    border-collapse: collapse;
    background: white;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
}

.digiplanet-cart-table th {
    background: #f8f9fa;
    padding: 15px;
    text-align: left;
    font-weight: 600;
    color: #495057;
    border-bottom: 2px solid #dee2e6;
}

.digiplanet-cart-table td {
    padding: 20px 15px;
    border-bottom: 1px solid #dee2e6;
    vertical-align: middle;
}

.digiplanet-cart-table tr:last-child td {
    border-bottom: none;
}

.digiplanet-cart-item .product-thumbnail img {
    width: 80px;
    height: 80px;
    object-fit: cover;
    border-radius: 4px;
}

.digiplanet-cart-item .product-remove button {
    background: none;
    border: none;
    color: #dc3545;
    cursor: pointer;
    padding: 5px;
    border-radius: 4px;
    transition: background-color 0.3s;
}

.digiplanet-cart-item .product-remove button:hover {
    background-color: #f8d7da;
}

.digiplanet-quantity-selector {
    display: flex;
    align-items: center;
    gap: 5px;
}

.digiplanet-quantity-selector button {
    width: 30px;
    height: 30px;
    border: 1px solid #dee2e6;
    background: white;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 4px;
    transition: all 0.3s;
}

.digiplanet-quantity-selector button:hover {
    background: #f8f9fa;
    border-color: #adb5bd;
}

.digiplanet-quantity-selector input {
    width: 50px;
    height: 30px;
    text-align: center;
    border: 1px solid #dee2e6;
    border-radius: 4px;
    font-size: 14px;
}

.digiplanet-cart-actions {
    display: flex;
    gap: 10px;
    margin-top: 20px;
    flex-wrap: wrap;
}

.digiplanet-cart-totals {
    background: #f8f9fa;
    padding: 25px;
    border-radius: 8px;
    position: sticky;
    top: 20px;
}

.digiplanet-totals-table {
    width: 100%;
    margin-bottom: 25px;
}

.digiplanet-totals-table th,
.digiplanet-totals-table td {
    padding: 12px 0;
    border-bottom: 1px solid #dee2e6;
    text-align: left;
}

.digiplanet-totals-table .digiplanet-total-row th,
.digiplanet-totals-table .digiplanet-total-row td {
    border-bottom: none;
    font-size: 18px;
    font-weight: 700;
    color: #2c3e50;
}

.digiplanet-coupon-section {
    margin-bottom: 25px;
    padding-bottom: 25px;
    border-bottom: 1px solid #dee2e6;
}

.digiplanet-coupon-form {
    display: flex;
    gap: 10px;
    margin-top: 10px;
}

.digiplanet-coupon-input {
    flex: 1;
    padding: 10px;
    border: 1px solid #dee2e6;
    border-radius: 4px;
    font-size: 14px;
}

.digiplanet-checkout-button {
    margin-bottom: 25px;
}

.digiplanet-proceed-checkout {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    width: 100%;
    padding: 15px;
    font-size: 16px;
    font-weight: 600;
}

.digiplanet-security-badges {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.digiplanet-security-badge {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 13px;
    color: #666;
}

.digiplanet-cross-sell {
    margin-top: 60px;
    padding-top: 40px;
    border-top: 1px solid #dee2e6;
}

.digiplanet-cross-sell h2 {
    margin-bottom: 30px;
    text-align: center;
}

@media (max-width: 992px) {
    .digiplanet-cart-content {
        grid-template-columns: 1fr;
    }
    
    .digiplanet-cart-totals {
        position: static;
    }
}

@media (max-width: 768px) {
    .digiplanet-cart-table {
        display: block;
        overflow-x: auto;
    }
    
    .digiplanet-cart-table th:nth-child(2),
    .digiplanet-cart-table td:nth-child(2) {
        display: none;
    }
    
    .digiplanet-cart-actions {
        flex-direction: column;
    }
    
    .digiplanet-cart-actions .digiplanet-btn {
        width: 100%;
        justify-content: center;
    }
}
</style>