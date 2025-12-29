<?php
/**
 * Stripe Checkout Template
 */

if (!defined('ABSPATH')) {
    exit;
}

$cart_manager = Digiplanet_Cart_Manager::get_instance();
$cart_items = $cart_manager->get_cart_items();
$cart_total = $cart_manager->get_cart_total();
$tax_rate = get_option('digiplanet_tax_rate', 0);
$enable_tax = get_option('digiplanet_enable_tax', 'no') === 'yes';
$currency = get_option('digiplanet_currency', 'USD');
$stripe_publishable_key = get_option('digiplanet_stripe_test_mode', 'yes') === 'yes' 
    ? get_option('digiplanet_stripe_test_publishable_key', '') 
    : get_option('digiplanet_stripe_live_publishable_key', '');
?>

<div class="digiplanet-checkout digiplanet-stripe-checkout">
    <div class="digiplanet-checkout-container">
        <!-- Checkout Header -->
        <div class="digiplanet-checkout-header">
            <h1><?php _e('Checkout', 'digiplanet-digital-products'); ?></h1>
            <div class="digiplanet-checkout-steps">
                <div class="digiplanet-step active">
                    <span class="digiplanet-step-number">1</span>
                    <span class="digiplanet-step-label"><?php _e('Cart', 'digiplanet-digital-products'); ?></span>
                </div>
                <div class="digiplanet-step active">
                    <span class="digiplanet-step-number">2</span>
                    <span class="digiplanet-step-label"><?php _e('Information', 'digiplanet-digital-products'); ?></span>
                </div>
                <div class="digiplanet-step active">
                    <span class="digiplanet-step-number">3</span>
                    <span class="digiplanet-step-label"><?php _e('Payment', 'digiplanet-digital-products'); ?></span>
                </div>
                <div class="digiplanet-step">
                    <span class="digiplanet-step-number">4</span>
                    <span class="digiplanet-step-label"><?php _e('Confirmation', 'digiplanet-digital-products'); ?></span>
                </div>
            </div>
        </div>
        
        <div class="digiplanet-checkout-content">
            <!-- Left Column: Order Summary -->
            <div class="digiplanet-order-summary">
                <div class="digiplanet-summary-header">
                    <h2><?php _e('Order Summary', 'digiplanet-digital-products'); ?></h2>
                    <a href="<?php echo get_permalink(get_option('digiplanet_cart_page_id')); ?>" class="digiplanet-edit-cart">
                        <?php _e('Edit Cart', 'digiplanet-digital-products'); ?>
                    </a>
                </div>
                
                <!-- Order Items -->
                <div class="digiplanet-order-items">
                    <?php if (!empty($cart_items)): ?>
                        <?php foreach ($cart_items as $item): ?>
                            <?php $product = Digiplanet_Product_Manager::get_instance()->get_product($item['product_id']); ?>
                            <?php if ($product): ?>
                                <div class="digiplanet-order-item">
                                    <div class="digiplanet-item-image">
                                        <?php if ($product->featured_image_id): ?>
                                            <?php echo wp_get_attachment_image($product->featured_image_id, 'thumbnail'); ?>
                                        <?php else: ?>
                                            <div class="digiplanet-item-placeholder">
                                                <span class="dashicons dashicons-format-image"></span>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="digiplanet-item-details">
                                        <h3><?php echo esc_html($product->name); ?></h3>
                                        <?php if (!empty($product->version)): ?>
                                            <p class="digiplanet-item-version">
                                                <?php printf(__('Version: %s', 'digiplanet-digital-products'), esc_html($product->version)); ?>
                                            </p>
                                        <?php endif; ?>
                                        <p class="digiplanet-item-price">
                                            <?php echo Digiplanet_Product_Manager::get_instance()->format_price($product->price); ?>
                                            <?php if ($item['quantity'] > 1): ?>
                                                <span class="digiplanet-item-quantity">
                                                    × <?php echo esc_html($item['quantity']); ?>
                                                </span>
                                            <?php endif; ?>
                                        </p>
                                    </div>
                                    <div class="digiplanet-item-total">
                                        <?php echo Digiplanet_Product_Manager::get_instance()->format_price($product->price * $item['quantity']); ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="digiplanet-empty-cart">
                            <p><?php _e('Your cart is empty.', 'digiplanet-digital-products'); ?></p>
                            <a href="<?php echo get_permalink(get_option('digiplanet_products_page_id')); ?>" class="digiplanet-btn digiplanet-btn-primary">
                                <?php _e('Browse Products', 'digiplanet-digital-products'); ?>
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Order Totals -->
                <div class="digiplanet-order-totals">
                    <div class="digiplanet-total-row">
                        <span><?php _e('Subtotal', 'digiplanet-digital-products'); ?></span>
                        <span><?php echo Digiplanet_Product_Manager::get_instance()->format_price($cart_total); ?></span>
                    </div>
                    
                    <?php if ($enable_tax && $tax_rate > 0): ?>
                        <?php $tax_amount = $cart_total * ($tax_rate / 100); ?>
                        <div class="digiplanet-total-row">
                            <span><?php printf(__('Tax (%s%%)', 'digiplanet-digital-products'), $tax_rate); ?></span>
                            <span><?php echo Digiplanet_Product_Manager::get_instance()->format_price($tax_amount); ?></span>
                        </div>
                    <?php endif; ?>
                    
                    <div class="digiplanet-total-row digiplanet-grand-total">
                        <span><?php _e('Total', 'digiplanet-digital-products'); ?></span>
                        <span>
                            <?php 
                            $total_amount = $cart_total;
                            if ($enable_tax && $tax_rate > 0) {
                                $total_amount += $tax_amount;
                            }
                            echo Digiplanet_Product_Manager::get_instance()->format_price($total_amount);
                            ?>
                        </span>
                    </div>
                </div>
                
                <!-- Security Badges -->
                <div class="digiplanet-security-badges">
                    <div class="digiplanet-security-badge">
                        <span class="dashicons dashicons-lock"></span>
                        <span><?php _e('Secure Checkout', 'digiplanet-digital-products'); ?></span>
                    </div>
                    <div class="digiplanet-security-badge">
                        <span class="dashicons dashicons-shield"></span>
                        <span><?php _e('256-bit Encryption', 'digiplanet-digital-products'); ?></span>
                    </div>
                    <div class="digiplanet-security-badge">
                        <span class="dashicons dashicons-yes"></span>
                        <span><?php _e('Money Back Guarantee', 'digiplanet-digital-products'); ?></span>
                    </div>
                </div>
            </div>
            
            <!-- Right Column: Payment Form -->
            <div class="digiplanet-payment-form">
                <!-- Customer Information -->
                <div class="digiplanet-customer-info">
                    <h2><?php _e('Customer Information', 'digiplanet-digital-products'); ?></h2>
                    
                    <div class="digiplanet-form-group">
                        <label for="digiplanet-email">
                            <?php _e('Email Address', 'digiplanet-digital-products'); ?> *
                        </label>
                        <input type="email" 
                               id="digiplanet-email" 
                               name="digiplanet_email" 
                               required 
                               value="<?php echo esc_attr(wp_get_current_user()->user_email); ?>"
                               placeholder="<?php esc_attr_e('Enter your email address', 'digiplanet-digital-products'); ?>">
                    </div>
                    
                    <div class="digiplanet-form-row">
                        <div class="digiplanet-form-group">
                            <label for="digiplanet-first-name">
                                <?php _e('First Name', 'digiplanet-digital-products'); ?> *
                            </label>
                            <input type="text" 
                                   id="digiplanet-first-name" 
                                   name="digiplanet_first_name" 
                                   required 
                                   value="<?php echo esc_attr(wp_get_current_user()->first_name); ?>"
                                   placeholder="<?php esc_attr_e('First name', 'digiplanet-digital-products'); ?>">
                        </div>
                        
                        <div class="digiplanet-form-group">
                            <label for="digiplanet-last-name">
                                <?php _e('Last Name', 'digiplanet-digital-products'); ?> *
                            </label>
                            <input type="text" 
                                   id="digiplanet-last-name" 
                                   name="digiplanet_last_name" 
                                   required 
                                   value="<?php echo esc_attr(wp_get_current_user()->last_name); ?>"
                                   placeholder="<?php esc_attr_e('Last name', 'digiplanet-digital-products'); ?>">
                        </div>
                    </div>
                </div>
                
                <!-- Payment Method -->
                <div class="digiplanet-payment-method">
                    <h2><?php _e('Payment Method', 'digiplanet-digital-products'); ?></h2>
                    
                    <div class="digiplanet-payment-options">
                        <div class="digiplanet-payment-option active" data-method="stripe">
                            <div class="digiplanet-payment-option-header">
                                <div class="digiplanet-payment-radio">
                                    <input type="radio" 
                                           id="payment-stripe" 
                                           name="payment_method" 
                                           value="stripe" 
                                           checked>
                                </div>
                                <div class="digiplanet-payment-icon">
                                    <img src="<?php echo DIGIPLANET_PLUGIN_URL; ?>assets/images/stripe-logo.png" 
                                         alt="Stripe" 
                                         width="60">
                                </div>
                                <div class="digiplanet-payment-title">
                                    <h3><?php _e('Credit/Debit Card', 'digiplanet-digital-products'); ?></h3>
                                    <p><?php _e('Pay with Visa, Mastercard, American Express', 'digiplanet-digital-products'); ?></p>
                                </div>
                            </div>
                            
                            <div class="digiplanet-payment-option-content">
                                <!-- Stripe Card Element -->
                                <div class="digiplanet-form-group">
                                    <label for="digiplanet-card-element">
                                        <?php _e('Card Details', 'digiplanet-digital-products'); ?>
                                    </label>
                                    <div id="digiplanet-card-element" class="digiplanet-stripe-card-element">
                                        <!-- Stripe Card Element will be inserted here -->
                                    </div>
                                    <div id="digiplanet-card-errors" class="digiplanet-card-errors" role="alert"></div>
                                </div>
                                
                                <div class="digiplanet-form-row">
                                    <div class="digiplanet-form-group">
                                        <label for="digiplanet-card-name">
                                            <?php _e('Name on Card', 'digiplanet-digital-products'); ?> *
                                        </label>
                                        <input type="text" 
                                               id="digiplanet-card-name" 
                                               name="card_name" 
                                               required 
                                               placeholder="<?php esc_attr_e('Full name on card', 'digiplanet-digital-products'); ?>">
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <?php if (get_option('digiplanet_enable_paystack', 'no') === 'yes'): ?>
                            <div class="digiplanet-payment-option" data-method="paystack">
                                <div class="digiplanet-payment-option-header">
                                    <div class="digiplanet-payment-radio">
                                        <input type="radio" 
                                               id="payment-paystack" 
                                               name="payment_method" 
                                               value="paystack">
                                    </div>
                                    <div class="digiplanet-payment-icon">
                                        <img src="<?php echo DIGIPLANET_PLUGIN_URL; ?>assets/images/paystack-logo.png" 
                                             alt="Paystack" 
                                             width="60">
                                    </div>
                                    <div class="digiplanet-payment-title">
                                        <h3><?php _e('Paystack', 'digiplanet-digital-products'); ?></h3>
                                        <p><?php _e('Pay with card, bank transfer, or mobile money', 'digiplanet-digital-products'); ?></p>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Terms and Conditions -->
                <div class="digiplanet-terms-agreement">
                    <div class="digiplanet-form-group">
                        <input type="checkbox" 
                               id="digiplanet-terms" 
                               name="digiplanet_terms" 
                               required>
                        <label for="digiplanet-terms">
                            <?php 
                            $terms_page_id = get_option('digiplanet_terms_page_id');
                            $terms_link = $terms_page_id ? get_permalink($terms_page_id) : '#';
                            
                            printf(
                                __('I agree to the %sTerms and Conditions%s and %sPrivacy Policy%s', 'digiplanet-digital-products'),
                                '<a href="' . esc_url($terms_link) . '" target="_blank">',
                                '</a>',
                                '<a href="' . esc_url(get_privacy_policy_url()) . '" target="_blank">',
                                '</a>'
                            );
                            ?>
                        </label>
                    </div>
                </div>
                
                <!-- Submit Button -->
                <div class="digiplanet-checkout-actions">
                    <button type="submit" 
                            id="digiplanet-submit-payment" 
                            class="digiplanet-btn digiplanet-btn-primary digiplanet-btn-lg">
                        <span id="digiplanet-payment-text">
                            <?php 
                            printf(
                                __('Pay %s', 'digiplanet-digital-products'),
                                Digiplanet_Product_Manager::get_instance()->format_price($total_amount)
                            );
                            ?>
                        </span>
                        <span id="digiplanet-processing-text" style="display: none;">
                            <span class="digiplanet-spinner"></span>
                            <?php _e('Processing Payment...', 'digiplanet-digital-products'); ?>
                        </span>
                    </button>
                    
                    <p class="digiplanet-checkout-note">
                        <?php _e('Your payment is secure and encrypted. You\'ll receive an email confirmation immediately after purchase.', 'digiplanet-digital-products'); ?>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Stripe.js and Payment Processing -->
<script src="https://js.stripe.com/v3/"></script>
<script>
jQuery(document).ready(function($) {
    // Initialize Stripe
    var stripe = Stripe('<?php echo esc_js($stripe_publishable_key); ?>');
    var elements = stripe.elements();
    
    // Create card element
    var cardElement = elements.create('card', {
        style: {
            base: {
                fontSize: '16px',
                color: '#32325d',
                fontFamily: '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif',
                '::placeholder': {
                    color: '#aab7c4'
                }
            },
            invalid: {
                color: '#fa755a',
                iconColor: '#fa755a'
            }
        },
        hidePostalCode: true
    });
    
    // Mount card element
    cardElement.mount('#digiplanet-card-element');
    
    // Handle real-time validation errors
    cardElement.on('change', function(event) {
        var displayError = document.getElementById('digiplanet-card-errors');
        if (event.error) {
            displayError.textContent = event.error.message;
            displayError.style.display = 'block';
        } else {
            displayError.style.display = 'none';
            displayError.textContent = '';
        }
    });
    
    // Handle payment method selection
    $('input[name="payment_method"]').on('change', function() {
        $('.digiplanet-payment-option').removeClass('active');
        $(this).closest('.digiplanet-payment-option').addClass('active');
    });
    
    // Handle form submission
    $('#digiplanet-submit-payment').on('click', function(e) {
        e.preventDefault();
        
        // Validate form
        if (!validateCheckoutForm()) {
            return;
        }
        
        // Show processing state
        $('#digiplanet-payment-text').hide();
        $('#digiplanet-processing-text').show();
        $('#digiplanet-submit-payment').prop('disabled', true);
        
        var paymentMethod = $('input[name="payment_method"]:checked').val();
        
        if (paymentMethod === 'stripe') {
            processStripePayment();
        } else if (paymentMethod === 'paystack') {
            processPaystackPayment();
        }
    });
    
    // Validate checkout form
    function validateCheckoutForm() {
        var isValid = true;
        
        // Validate required fields
        $('#digiplanet-checkout input[required]').each(function() {
            if (!$(this).val().trim()) {
                isValid = false;
                $(this).addClass('digiplanet-error');
            } else {
                $(this).removeClass('digiplanet-error');
            }
        });
        
        // Validate email
        var email = $('#digiplanet-email').val();
        var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
            isValid = false;
            $('#digiplanet-email').addClass('digiplanet-error');
        }
        
        // Validate terms agreement
        if (!$('#digiplanet-terms').is(':checked')) {
            isValid = false;
            $('.digiplanet-terms-agreement').addClass('digiplanet-error');
        }
        
        return isValid;
    }
    
    // Process Stripe payment
    function processStripePayment() {
        // Create payment intent via AJAX
        $.ajax({
            url: digiplanet_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'digiplanet_create_payment_intent',
                nonce: digiplanet_ajax.nonce,
                amount: <?php echo intval($total_amount * 100); ?>,
                currency: '<?php echo esc_js($currency); ?>',
                customer_email: $('#digiplanet-email').val(),
                customer_name: $('#digiplanet-first-name').val() + ' ' + $('#digiplanet-last-name').val()
            },
            success: function(response) {
                if (response.success) {
                    // Confirm card payment
                    stripe.confirmCardPayment(response.data.client_secret, {
                        payment_method: {
                            card: cardElement,
                            billing_details: {
                                name: $('#digiplanet-card-name').val(),
                                email: $('#digiplanet-email').val()
                            }
                        }
                    }).then(function(result) {
                        if (result.error) {
                            // Show error
                            $('#digiplanet-card-errors').text(result.error.message).show();
                            resetPaymentButton();
                        } else {
                            // Payment successful
                            completeOrder(result.paymentIntent.id, 'stripe');
                        }
                    });
                } else {
                    alert('Error: ' + response.data.message);
                    resetPaymentButton();
                }
            },
            error: function() {
                alert('<?php esc_js(__('An error occurred. Please try again.', 'digiplanet-digital-products')); ?>');
                resetPaymentButton();
            }
        });
    }
    
    // Process Paystack payment
    function processPaystackPayment() {
        // Implement Paystack payment logic here
        alert('Paystack payment integration coming soon!');
        resetPaymentButton();
    }
    
    // Complete order after successful payment
    function completeOrder(transactionId, paymentMethod) {
        $.ajax({
            url: digiplanet_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'digiplanet_complete_order',
                nonce: digiplanet_ajax.nonce,
                transaction_id: transactionId,
                payment_method: paymentMethod,
                customer_email: $('#digiplanet-email').val(),
                customer_name: $('#digiplanet-first-name').val() + ' ' + $('#digiplanet-last-name').val()
            },
            success: function(response) {
                if (response.success) {
                    // Redirect to confirmation page
                    window.location.href = response.data.redirect_url;
                } else {
                    alert('Error: ' + response.data.message);
                    resetPaymentButton();
                }
            },
            error: function() {
                alert('<?php esc_js(__('An error occurred. Please try again.', 'digiplanet-digital-products')); ?>');
                resetPaymentButton();
            }
        });
    }
    
    // Reset payment button state
    function resetPaymentButton() {
        $('#digiplanet-payment-text').show();
        $('#digiplanet-processing-text').hide();
        $('#digiplanet-submit-payment').prop('disabled', false);
    }
});
</script>

<style>
.digiplanet-checkout {
    min-height: 100vh;
    background: #f8f9fa;
    padding: 40px 20px;
}

.digiplanet-checkout-container {
    max-width: 1200px;
    margin: 0 auto;
}

.digiplanet-checkout-header {
    text-align: center;
    margin-bottom: 40px;
}

.digiplanet-checkout-header h1 {
    font-size: 36px;
    color: #1d2327;
    margin: 0 0 20px 0;
}

.digiplanet-checkout-steps {
    display: flex;
    justify-content: center;
    gap: 40px;
    position: relative;
}

.digiplanet-checkout-steps::before {
    content: '';
    position: absolute;
    top: 15px;
    left: 10%;
    right: 10%;
    height: 2px;
    background: #dcdcde;
    z-index: 1;
}

.digiplanet-step {
    display: flex;
    flex-direction: column;
    align-items: center;
    position: relative;
    z-index: 2;
}

.digiplanet-step-number {
    width: 32px;
    height: 32px;
    background: white;
    border: 2px solid #dcdcde;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    color: #646970;
    margin-bottom: 8px;
    transition: all 0.3s;
}

.digiplanet-step.active .digiplanet-step-number {
    background: #2271b1;
    border-color: #2271b1;
    color: white;
}

.digiplanet-step-label {
    font-size: 14px;
    color: #646970;
    font-weight: 500;
}

.digiplanet-step.active .digiplanet-step-label {
    color: #2271b1;
}

.digiplanet-checkout-content {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 40px;
    background: white;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
}

@media (max-width: 992px) {
    .digiplanet-checkout-content {
        grid-template-columns: 1fr;
    }
}

/* Order Summary */
.digiplanet-order-summary {
    padding: 30px;
    background: #f9f9f9;
    border-right: 1px solid #e0e0e0;
}

.digiplanet-summary-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
    padding-bottom: 15px;
    border-bottom: 1px solid #e0e0e0;
}

.digiplanet-summary-header h2 {
    margin: 0;
    font-size: 24px;
    color: #1d2327;
}

.digiplanet-edit-cart {
    color: #2271b1;
    text-decoration: none;
    font-size: 14px;
    font-weight: 500;
}

.digiplanet-edit-cart:hover {
    text-decoration: underline;
}

.digiplanet-order-items {
    margin-bottom: 30px;
}

.digiplanet-order-item {
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 15px;
    background: white;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    margin-bottom: 10px;
}

.digiplanet-item-image {
    width: 60px;
    height: 60px;
    flex-shrink: 0;
}

.digiplanet-item-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 6px;
}

.digiplanet-item-placeholder {
    width: 100%;
    height: 100%;
    background: #f0f0f1;
    border-radius: 6px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.digiplanet-item-placeholder .dashicons {
    color: #646970;
    font-size: 24px;
}

.digiplanet-item-details {
    flex: 1;
}

.digiplanet-item-details h3 {
    margin: 0 0 5px 0;
    font-size: 16px;
    color: #1d2327;
}

.digiplanet-item-version {
    margin: 0 0 5px 0;
    font-size: 12px;
    color: #646970;
}

.digiplanet-item-price {
    margin: 0;
    font-size: 14px;
    color: #2271b1;
    font-weight: 500;
}

.digiplanet-item-quantity {
    color: #646970;
    font-size: 12px;
}

.digiplanet-item-total {
    font-weight: 600;
    color: #1d2327;
    font-size: 16px;
}

.digiplanet-empty-cart {
    text-align: center;
    padding: 40px 20px;
    background: white;
    border: 2px dashed #e0e0e0;
    border-radius: 8px;
}

.digiplanet-empty-cart p {
    margin: 0 0 20px 0;
    color: #646970;
}

.digiplanet-order-totals {
    padding: 20px;
    background: white;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
}

.digiplanet-total-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px 0;
    border-bottom: 1px solid #f0f0f0;
}

.digiplanet-total-row:last-child {
    border-bottom: none;
}

.digiplanet-grand-total {
    font-size: 18px;
    font-weight: 600;
    color: #1d2327;
    padding-top: 15px;
    border-top: 2px solid #e0e0e0;
}

.digiplanet-security-badges {
    display: flex;
    justify-content: center;
    gap: 20px;
    margin-top: 30px;
    flex-wrap: wrap;
}

.digiplanet-security-badge {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 10px 15px;
    background: white;
    border: 1px solid #e0e0e0;
    border-radius: 6px;
    font-size: 12px;
    color: #646970;
}

.digiplanet-security-badge .dashicons {
    color: #46b450;
}

/* Payment Form */
.digiplanet-payment-form {
    padding: 30px;
}

.digiplanet-customer-info,
.digiplanet-payment-method {
    margin-bottom: 30px;
}

.digiplanet-customer-info h2,
.digiplanet-payment-method h2 {
    margin: 0 0 20px 0;
    font-size: 20px;
    color: #1d2327;
    padding-bottom: 10px;
    border-bottom: 1px solid #e0e0e0;
}

.digiplanet-form-group {
    margin-bottom: 20px;
}

.digiplanet-form-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: 500;
    color: #1d2327;
}

.digiplanet-form-group input[type="text"],
.digiplanet-form-group input[type="email"] {
    width: 100%;
    padding: 12px 15px;
    border: 1px solid #8c8f94;
    border-radius: 6px;
    font-size: 14px;
    transition: all 0.2s;
}

.digiplanet-form-group input:focus {
    outline: none;
    border-color: #2271b1;
    box-shadow: 0 0 0 2px rgba(34, 113, 177, 0.1);
}

.digiplanet-form-group input.digiplanet-error {
    border-color: #dc3232;
}

.digiplanet-form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 15px;
}

/* Payment Options */
.digiplanet-payment-options {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.digiplanet-payment-option {
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    overflow: hidden;
    transition: all 0.3s;
}

.digiplanet-payment-option.active {
    border-color: #2271b1;
    box-shadow: 0 0 0 1px #2271b1;
}

.digiplanet-payment-option-header {
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 15px;
    cursor: pointer;
    background: #f9f9f9;
}

.digiplanet-payment-radio {
    flex-shrink: 0;
}

.digiplanet-payment-radio input[type="radio"] {
    margin: 0;
}

.digiplanet-payment-icon {
    flex-shrink: 0;
}

.digiplanet-payment-icon img {
    display: block;
}

.digiplanet-payment-title {
    flex: 1;
}

.digiplanet-payment-title h3 {
    margin: 0 0 5px 0;
    font-size: 16px;
    color: #1d2327;
}

.digiplanet-payment-title p {
    margin: 0;
    font-size: 13px;
    color: #646970;
}

.digiplanet-payment-option-content {
    padding: 20px;
    border-top: 1px solid #e0e0e0;
    display: none;
}

.digiplanet-payment-option.active .digiplanet-payment-option-content {
    display: block;
}

/* Stripe Card Element */
.digiplanet-stripe-card-element {
    padding: 12px 15px;
    border: 1px solid #8c8f94;
    border-radius: 6px;
    background: white;
}

.digiplanet-stripe-card-element--focus {
    border-color: #2271b1;
    box-shadow: 0 0 0 2px rgba(34, 113, 177, 0.1);
}

.digiplanet-stripe-card-element--invalid {
    border-color: #dc3232;
}

.digiplanet-card-errors {
    color: #dc3232;
    font-size: 13px;
    margin-top: 8px;
    display: none;
}

/* Terms Agreement */
.digiplanet-terms-agreement {
    margin: 30px 0;
    padding: 20px;
    background: #f9f9f9;
    border-radius: 8px;
}

.digiplanet-terms-agreement.digiplanet-error {
    border: 1px solid #dc3232;
    background: #f7eded;
}

.digiplanet-terms-agreement input[type="checkbox"] {
    margin-right: 10px;
}

.digiplanet-terms-agreement label {
    margin: 0;
    font-size: 14px;
    line-height: 1.5;
}

.digiplanet-terms-agreement a {
    color: #2271b1;
    text-decoration: none;
}

.digiplanet-terms-agreement a:hover {
    text-decoration: underline;
}

/* Checkout Actions */
.digiplanet-checkout-actions {
    text-align: center;
}

.digiplanet-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    padding: 15px 30px;
    font-size: 16px;
    font-weight: 600;
    text-decoration: none;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.2s;
}

.digiplanet-btn-lg {
    padding: 18px 40px;
    font-size: 18px;
}

.digiplanet-btn-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    width: 100%;
}

.digiplanet-btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
}

.digiplanet-btn-primary:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    transform: none;
    box-shadow: none;
}

.digiplanet-checkout-note {
    margin: 20px 0 0 0;
    font-size: 13px;
    color: #646970;
    text-align: center;
    line-height: 1.5;
}

/* Spinner */
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
</style>