<?php
/**
 * Paystack Checkout Template
 */

if (!defined('ABSPATH')) {
    exit;
}

$cart_manager = Digiplanet_Cart_Manager::get_instance();
$payment_manager = Digiplanet_Payments::get_instance();
$settings = get_option('digiplanet_settings', []);

$cart_items = $cart_manager->get_cart_items();
$cart_total = $cart_manager->get_cart_total();
$tax_amount = $cart_manager->calculate_tax($cart_total);
$grand_total = $cart_total + $tax_amount;

// Check if Paystack is enabled
$paystack_enabled = isset($settings['paystack_enabled']) && $settings['paystack_enabled'] === 'yes';
$paystack_test_mode = isset($settings['paystack_test_mode']) && $settings['paystack_test_mode'] === 'yes';
$paystack_public_key = $paystack_test_mode ? 
    ($settings['paystack_test_public_key'] ?? '') : 
    ($settings['paystack_live_public_key'] ?? '');

if (!$paystack_enabled || empty($paystack_public_key) || empty($cart_items)) {
    wp_redirect(home_url('/cart/'));
    exit;
}

// Get customer info
$customer_id = get_current_user_id();
$customer_email = '';
$customer_name = '';

if ($customer_id) {
    $user = get_userdata($customer_id);
    $customer_email = $user->user_email;
    $customer_name = $user->display_name;
}

// Generate order reference
$order_ref = 'DP-' . date('YmdHis') . '-' . wp_rand(1000, 9999);
?>

<div class="digiplanet-paystack-checkout">
    <div class="digiplanet-checkout-container">
        <!-- Checkout Header -->
        <div class="digiplanet-checkout-header">
            <h1><?php _e('Paystack Checkout', 'digiplanet-digital-products'); ?></h1>
            <div class="digiplanet-checkout-progress">
                <div class="digiplanet-progress-step active">
                    <span class="digiplanet-step-number">1</span>
                    <span class="digiplanet-step-label"><?php _e('Cart', 'digiplanet-digital-products'); ?></span>
                </div>
                <div class="digiplanet-progress-step active">
                    <span class="digiplanet-step-number">2</span>
                    <span class="digiplanet-step-label"><?php _e('Checkout', 'digiplanet-digital-products'); ?></span>
                </div>
                <div class="digiplanet-progress-step active">
                    <span class="digiplanet-step-number">3</span>
                    <span class="digiplanet-step-label"><?php _e('Payment', 'digiplanet-digital-products'); ?></span>
                </div>
                <div class="digiplanet-progress-step">
                    <span class="digiplanet-step-number">4</span>
                    <span class="digiplanet-step-label"><?php _e('Complete', 'digiplanet-digital-products'); ?></span>
                </div>
            </div>
        </div>
        
        <div class="digiplanet-checkout-content">
            <!-- Left Column - Order Summary -->
            <div class="digiplanet-checkout-left">
                <div class="digiplanet-order-summary">
                    <h3><?php _e('Order Summary', 'digiplanet-digital-products'); ?></h3>
                    
                    <div class="digiplanet-order-items">
                        <?php foreach ($cart_items as $item): ?>
                            <?php $product = get_post($item['product_id']); ?>
                            <div class="digiplanet-order-item">
                                <div class="digiplanet-item-image">
                                    <?php if (has_post_thumbnail($item['product_id'])): ?>
                                        <?php echo get_the_post_thumbnail($item['product_id'], 'thumbnail'); ?>
                                    <?php else: ?>
                                        <img src="<?php echo DIGIPLANET_ASSETS_URL . 'images/placeholder-product.png'; ?>" alt="<?php echo esc_attr($product->post_title); ?>">
                                    <?php endif; ?>
                                </div>
                                
                                <div class="digiplanet-item-details">
                                    <h4><?php echo esc_html($product->post_title); ?></h4>
                                    <div class="digiplanet-item-meta">
                                        <span class="digiplanet-item-price">
                                            <?php echo Digiplanet_Product_Manager::get_instance()->format_price($item['price']); ?>
                                        </span>
                                        <span class="digiplanet-item-quantity">
                                            <?php printf(__('Quantity: %d', 'digiplanet-digital-products'), $item['quantity']); ?>
                                        </span>
                                    </div>
                                </div>
                                
                                <div class="digiplanet-item-total">
                                    <?php echo Digiplanet_Product_Manager::get_instance()->format_price($item['price'] * $item['quantity']); ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <div class="digiplanet-order-totals">
                        <div class="digiplanet-total-row">
                            <span><?php _e('Subtotal', 'digiplanet-digital-products'); ?></span>
                            <span><?php echo Digiplanet_Product_Manager::get_instance()->format_price($cart_total); ?></span>
                        </div>
                        
                        <?php if ($tax_amount > 0): ?>
                            <div class="digiplanet-total-row">
                                <span><?php _e('Tax', 'digiplanet-digital-products'); ?></span>
                                <span><?php echo Digiplanet_Product_Manager::get_instance()->format_price($tax_amount); ?></span>
                            </div>
                        <?php endif; ?>
                        
                        <div class="digiplanet-total-row digiplanet-grand-total">
                            <span><?php _e('Total', 'digiplanet-digital-products'); ?></span>
                            <span><?php echo Digiplanet_Product_Manager::get_instance()->format_price($grand_total); ?></span>
                        </div>
                    </div>
                    
                    <div class="digiplanet-payment-methods">
                        <h4><?php _e('Accepted Payment Methods', 'digiplanet-digital-products'); ?></h4>
                        <div class="digiplanet-payment-icons">
                            <i class="fab fa-cc-visa" title="Visa"></i>
                            <i class="fab fa-cc-mastercard" title="Mastercard"></i>
                            <i class="fab fa-cc-amex" title="American Express"></i>
                            <i class="fab fa-cc-discover" title="Discover"></i>
                            <i class="fas fa-mobile-alt" title="Mobile Money"></i>
                            <i class="fas fa-university" title="Bank Transfer"></i>
                        </div>
                    </div>
                </div>
                
                <div class="digiplanet-security-notice">
                    <div class="digiplanet-security-badge">
                        <i class="fas fa-lock"></i>
                        <span><?php _e('Secure Checkout', 'digiplanet-digital-products'); ?></span>
                    </div>
                    <p><?php _e('Your payment information is encrypted and secure. We never store your card details.', 'digiplanet-digital-products'); ?></p>
                </div>
            </div>
            
            <!-- Right Column - Payment Form -->
            <div class="digiplanet-checkout-right">
                <div class="digiplanet-payment-form-container">
                    <div class="digiplanet-payment-header">
                        <h3><?php _e('Payment Details', 'digiplanet-digital-products'); ?></h3>
                        <div class="digiplanet-paystack-logo">
                            <img src="<?php echo DIGIPLANET_ASSETS_URL . 'images/paystack-logo.png'; ?>" alt="Paystack">
                        </div>
                    </div>
                    
                    <?php if ($paystack_test_mode): ?>
                        <div class="digiplanet-test-mode-notice">
                            <i class="fas fa-flask"></i>
                            <span><?php _e('Test Mode Enabled', 'digiplanet-digital-products'); ?></span>
                            <p><?php _e('Use test card: 5061 0688 0122 2224 (Any expiry date, any CVV)', 'digiplanet-digital-products'); ?></p>
                        </div>
                    <?php endif; ?>
                    
                    <form id="digiplanet-paystack-form" method="post">
                        <input type="hidden" name="order_reference" value="<?php echo esc_attr($order_ref); ?>">
                        <input type="hidden" name="amount" value="<?php echo esc_attr($grand_total * 100); ?>"> <!-- Amount in kobo -->
                        <input type="hidden" name="currency" value="<?php echo esc_attr($settings['currency'] ?? 'NGN'); ?>">
                        <input type="hidden" name="metadata" value='<?php echo json_encode(['cart_items' => $cart_items]); ?>'>
                        
                        <div class="digiplanet-form-group">
                            <label for="customer_email"><?php _e('Email Address', 'digiplanet-digital-products'); ?> *</label>
                            <div class="digiplanet-input-with-icon">
                                <i class="fas fa-envelope"></i>
                                <input type="email" id="customer_email" name="email" value="<?php echo esc_attr($customer_email); ?>" required>
                            </div>
                        </div>
                        
                        <div class="digiplanet-form-group">
                            <label for="customer_name"><?php _e('Full Name', 'digiplanet-digital-products'); ?></label>
                            <div class="digiplanet-input-with-icon">
                                <i class="fas fa-user"></i>
                                <input type="text" id="customer_name" name="name" value="<?php echo esc_attr($customer_name); ?>">
                            </div>
                        </div>
                        
                        <div class="digiplanet-form-group">
                            <label for="customer_phone"><?php _e('Phone Number', 'digiplanet-digital-products'); ?></label>
                            <div class="digiplanet-input-with-icon">
                                <i class="fas fa-phone"></i>
                                <input type="tel" id="customer_phone" name="phone">
                            </div>
                        </div>
                        
                        <div class="digiplanet-payment-options">
                            <h4><?php _e('Select Payment Method', 'digiplanet-digital-products'); ?></h4>
                            
                            <div class="digiplanet-payment-option">
                                <input type="radio" id="payment_card" name="payment_method" value="card" checked>
                                <label for="payment_card">
                                    <i class="far fa-credit-card"></i>
                                    <span><?php _e('Debit/Credit Card', 'digiplanet-digital-products'); ?></span>
                                </label>
                            </div>
                            
                            <div class="digiplanet-payment-option">
                                <input type="radio" id="payment_bank" name="payment_method" value="bank">
                                <label for="payment_bank">
                                    <i class="fas fa-university"></i>
                                    <span><?php _e('Bank Transfer', 'digiplanet-digital-products'); ?></span>
                                </label>
                            </div>
                            
                            <div class="digiplanet-payment-option">
                                <input type="radio" id="payment_mobile" name="payment_method" value="mobile">
                                <label for="payment_mobile">
                                    <i class="fas fa-mobile-alt"></i>
                                    <span><?php _e('Mobile Money', 'digiplanet-digital-products'); ?></span>
                                </label>
                            </div>
                        </div>
                        
                        <div class="digiplanet-payment-fields" id="card_fields">
                            <div class="digiplanet-form-group">
                                <label for="card_number"><?php _e('Card Number', 'digiplanet-digital-products'); ?></label>
                                <div class="digiplanet-input-with-icon">
                                    <i class="far fa-credit-card"></i>
                                    <input type="text" id="card_number" name="card_number" placeholder="1234 5678 9012 3456" maxlength="19">
                                </div>
                            </div>
                            
                            <div class="digiplanet-form-row">
                                <div class="digiplanet-form-group">
                                    <label for="card_expiry"><?php _e('Expiry Date', 'digiplanet-digital-products'); ?></label>
                                    <input type="text" id="card_expiry" name="card_expiry" placeholder="MM/YY" maxlength="5">
                                </div>
                                
                                <div class="digiplanet-form-group">
                                    <label for="card_cvv"><?php _e('CVV', 'digiplanet-digital-products'); ?></label>
                                    <div class="digiplanet-input-with-icon">
                                        <i class="fas fa-lock"></i>
                                        <input type="password" id="card_cvv" name="card_cvv" placeholder="123" maxlength="4">
                                        <button type="button" class="digiplanet-password-toggle" data-target="card_cvv">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="digiplanet-payment-fields" id="bank_fields" style="display: none;">
                            <div class="digiplanet-bank-instructions">
                                <p><?php _e('After submitting, you will receive bank transfer details. Payment must be completed within 24 hours.', 'digiplanet-digital-products'); ?></p>
                            </div>
                        </div>
                        
                        <div class="digiplanet-payment-fields" id="mobile_fields" style="display: none;">
                            <div class="digiplanet-form-group">
                                <label for="mobile_network"><?php _e('Mobile Network', 'digiplanet-digital-products'); ?></label>
                                <select id="mobile_network" name="mobile_network">
                                    <option value=""><?php _e('Select Network', 'digiplanet-digital-products'); ?></option>
                                    <option value="mtn">MTN</option>
                                    <option value="airtel">Airtel</option>
                                    <option value="glo">Glo</option>
                                    <option value="9mobile">9mobile</option>
                                </select>
                            </div>
                            
                            <div class="digiplanet-form-group">
                                <label for="mobile_number"><?php _e('Mobile Number', 'digiplanet-digital-products'); ?></label>
                                <input type="tel" id="mobile_number" name="mobile_number" placeholder="08012345678">
                            </div>
                        </div>
                        
                        <div class="digiplanet-terms-agreement">
                            <label class="digiplanet-checkbox">
                                <input type="checkbox" name="terms" id="terms" required>
                                <span class="digiplanet-checkbox-checkmark"></span>
                                <span class="digiplanet-checkbox-label">
                                    <?php 
                                    printf(
                                        __('I agree to the %s and authorize this payment', 'digiplanet-digital-products'),
                                        '<a href="' . esc_url(home_url('/terms/')) . '" target="_blank">' . __('Terms of Service', 'digiplanet-digital-products') . '</a>'
                                    );
                                    ?>
                                </span>
                            </label>
                        </div>
                        
                        <div class="digiplanet-payment-actions">
                            <button type="button" class="digiplanet-btn digiplanet-btn-secondary" onclick="window.location.href='<?php echo home_url('/cart/'); ?>'">
                                <i class="fas fa-arrow-left"></i>
                                <?php _e('Back to Cart', 'digiplanet-digital-products'); ?>
                            </button>
                            
                            <button type="submit" class="digiplanet-btn digiplanet-btn-primary" id="paystack-pay-button">
                                <i class="fas fa-lock"></i>
                                <?php printf(__('Pay %s', 'digiplanet-digital-products'), Digiplanet_Product_Manager::get_instance()->format_price($grand_total)); ?>
                            </button>
                        </div>
                    </form>
                    
                    <div class="digiplanet-support-info">
                        <p><?php _e('Need help?', 'digiplanet-digital-products'); ?>
                            <a href="<?php echo esc_url(home_url('/support/')); ?>"><?php _e('Contact Support', 'digiplanet-digital-products'); ?></a>
                            <?php _e('or call', 'digiplanet-digital-products'); ?>
                            <a href="tel:+2341234567890">+234 123 456 7890</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Payment Processing Modal -->
<div id="digiplanet-payment-modal" class="digiplanet-modal" style="display: none;">
    <div class="digiplanet-modal-content">
        <div class="digiplanet-modal-body">
            <div class="digiplanet-payment-processing">
                <div class="digiplanet-spinner">
                    <div class="digiplanet-spinner-circle"></div>
                </div>
                <h3><?php _e('Processing Payment', 'digiplanet-digital-products'); ?></h3>
                <p><?php _e('Please wait while we process your payment. Do not close this window.', 'digiplanet-digital-products'); ?></p>
                <div class="digiplanet-payment-status" id="payment-status"></div>
            </div>
        </div>
    </div>
</div>

<script src="https://js.paystack.co/v1/inline.js"></script>
<script>
jQuery(document).ready(function($) {
    // Initialize Paystack
    var paystack = new PaystackPop();
    
    // Format card number
    $('#card_number').on('input', function() {
        var value = $(this).val().replace(/\s+/g, '').replace(/[^0-9]/gi, '');
        var formatted = value.replace(/(\d{4})/g, '$1 ').trim();
        $(this).val(formatted);
    });
    
    // Format expiry date
    $('#card_expiry').on('input', function() {
        var value = $(this).val().replace(/\s+/g, '').replace(/[^0-9]/gi, '');
        if (value.length >= 2) {
            value = value.substring(0, 2) + '/' + value.substring(2, 4);
        }
        $(this).val(value);
    });
    
    // Toggle CVV visibility
    $('.digiplanet-password-toggle').on('click', function() {
        var $button = $(this);
        var $icon = $button.find('i');
        var targetId = $button.data('target');
        var $input = $('#' + targetId);
        
        if ($input.attr('type') === 'password') {
            $input.attr('type', 'text');
            $icon.removeClass('fa-eye').addClass('fa-eye-slash');
        } else {
            $input.attr('type', 'password');
            $icon.removeClass('fa-eye-slash').addClass('fa-eye');
        }
    });
    
    // Show/hide payment fields based on selected method
    $('input[name="payment_method"]').on('change', function() {
        var method = $(this).val();
        
        // Hide all fields
        $('.digiplanet-payment-fields').hide();
        
        // Show selected method fields
        $('#' + method + '_fields').show();
    });
    
    // Handle form submission
    $('#digiplanet-paystack-form').on('submit', function(e) {
        e.preventDefault();
        
        var $form = $(this);
        var formData = $form.serializeArray();
        var paymentMethod = $('input[name="payment_method"]:checked').val();
        
        // Validate form
        if (!validateForm(paymentMethod)) {
            return false;
        }
        
        // Show processing modal
        $('#digiplanet-payment-modal').show();
        $('#payment-status').html('<p><i class="fas fa-spinner fa-spin"></i> <?php _e('Initiating payment...', 'digiplanet-digital-products'); ?></p>');
        
        // Prepare payment data
        var paymentData = {
            key: '<?php echo esc_js($paystack_public_key); ?>',
            email: $('#customer_email').val(),
            amount: <?php echo $grand_total * 100; ?>,
            currency: '<?php echo esc_js($settings['currency'] ?? 'NGN'); ?>',
            ref: '<?php echo esc_js($order_ref); ?>',
            metadata: {
                custom_fields: [
                    {
                        display_name: "Customer Name",
                        variable_name: "customer_name",
                        value: $('#customer_name').val() || 'Guest'
                    },
                    {
                        display_name: "Phone Number",
                        variable_name: "phone_number",
                        value: $('#customer_phone').val() || ''
                    },
                    {
                        display_name: "Payment Method",
                        variable_name: "payment_method",
                        value: paymentMethod
                    }
                ]
            },
            callback: function(response) {
                // Payment successful
                $('#payment-status').html('<p class="success"><i class="fas fa-check-circle"></i> <?php _e('Payment successful! Processing order...', 'digiplanet-digital-products'); ?></p>');
                
                // Send payment verification to server
                $.ajax({
                    url: digiplanet_ajax.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'digiplanet_verify_paystack_payment',
                        reference: response.reference,
                        order_reference: '<?php echo $order_ref; ?>',
                        nonce: digiplanet_ajax.nonce
                    },
                    success: function(verifyResponse) {
                        if (verifyResponse.success) {
                            $('#payment-status').html('<p class="success"><i class="fas fa-check-circle"></i> ' + verifyResponse.data.message + '</p>');
                            
                            // Redirect to thank you page
                            setTimeout(function() {
                                window.location.href = verifyResponse.data.redirect_url;
                            }, 2000);
                        } else {
                            $('#payment-status').html('<p class="error"><i class="fas fa-exclamation-circle"></i> ' + verifyResponse.data.message + '</p>');
                            setTimeout(function() {
                                $('#digiplanet-payment-modal').hide();
                            }, 3000);
                        }
                    },
                    error: function() {
                        $('#payment-status').html('<p class="error"><i class="fas fa-exclamation-circle"></i> <?php _e('Payment verification failed. Please contact support.', 'digiplanet-digital-products'); ?></p>');
                        setTimeout(function() {
                            $('#digiplanet-payment-modal').hide();
                        }, 3000);
                    }
                });
            },
            onClose: function() {
                // Payment modal closed
                $('#digiplanet-payment-modal').hide();
                $('#payment-status').html('<p class="info"><i class="fas fa-info-circle"></i> <?php _e('Payment was cancelled.', 'digiplanet-digital-products'); ?></p>');
            }
        };
        
        // Add name if provided
        if ($('#customer_name').val()) {
            paymentData.name = $('#customer_name').val();
        }
        
        // Add phone if provided
        if ($('#customer_phone').val()) {
            paymentData.phone = $('#customer_phone').val();
        }
        
        // Initialize payment based on method
        switch (paymentMethod) {
            case 'card':
                // Card payment - use Paystack inline
                paystack.newTransaction(paymentData);
                break;
                
            case 'bank':
                // Bank transfer - show instructions
                $('#payment-status').html('<div class="bank-transfer-details"><h4><?php _e('Bank Transfer Details', 'digiplanet-digital-products'); ?></h4><p><?php _e('Please transfer', 'digiplanet-digital-products'); ?> <?php echo Digiplanet_Product_Manager::get_instance()->format_price($grand_total); ?> <?php _e('to:', 'digiplanet-digital-products'); ?></p><p><strong><?php _e('Bank:', 'digiplanet-digital-products'); ?></strong> Paystack Test Bank<br><strong><?php _e('Account Name:', 'digiplanet-digital-products'); ?></strong> Digiplanet Solutions LLC<br><strong><?php _e('Account Number:', 'digiplanet-digital-products'); ?></strong> 1234567890<br><strong><?php _e('Reference:', 'digiplanet-digital-products'); ?></strong> <?php echo $order_ref; ?></p><p><?php _e('After payment, please send proof of payment to payments@digiplanet.com', 'digiplanet-digital-products'); ?></p></div>');
                break;
                
            case 'mobile':
                // Mobile money
                var mobileNetwork = $('#mobile_network').val();
                var mobileNumber = $('#mobile_number').val();
                
                if (!mobileNetwork || !mobileNumber) {
                    $('#payment-status').html('<p class="error"><i class="fas fa-exclamation-circle"></i> <?php _e('Please select mobile network and enter your number.', 'digiplanet-digital-products'); ?></p>');
                    $('#digiplanet-payment-modal').hide();
                    return;
                }
                
                paymentData.channels = ['mobile_money'];
                paymentData.metadata.mobile_network = mobileNetwork;
                paymentData.metadata.mobile_number = mobileNumber;
                
                paystack.newTransaction(paymentData);
                break;
        }
    });
    
    // Form validation
    function validateForm(paymentMethod) {
        var isValid = true;
        
        // Clear previous errors
        $('.digiplanet-form-group').removeClass('has-error');
        $('.digiplanet-error-message').remove();
        
        // Validate email
        var email = $('#customer_email').val().trim();
        var emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!email || !emailPattern.test(email)) {
            showFieldError($('#customer_email'), '<?php _e('Please enter a valid email address.', 'digiplanet-digital-products'); ?>');
            isValid = false;
        }
        
        // Validate terms agreement
        if (!$('#terms').is(':checked')) {
            showFieldError($('#terms').closest('.digiplanet-checkbox'), '<?php _e('You must agree to the terms and conditions.', 'digiplanet-digital-products'); ?>');
            isValid = false;
        }
        
        // Validate card details if card payment
        if (paymentMethod === 'card') {
            var cardNumber = $('#card_number').val().replace(/\s+/g, '');
            var cardExpiry = $('#card_expiry').val();
            var cardCVV = $('#card_cvv').val();
            
            if (cardNumber.length < 13) {
                showFieldError($('#card_number'), '<?php _e('Please enter a valid card number.', 'digiplanet-digital-products'); ?>');
                isValid = false;
            }
            
            if (!cardExpiry || !/^\d{2}\/\d{2}$/.test(cardExpiry)) {
                showFieldError($('#card_expiry'), '<?php _e('Please enter expiry date in MM/YY format.', 'digiplanet-digital-products'); ?>');
                isValid = false;
            }
            
            if (!cardCVV || cardCVV.length < 3) {
                showFieldError($('#card_cvv'), '<?php _e('Please enter a valid CVV.', 'digiplanet-digital-products'); ?>');
                isValid = false;
            }
        }
        
        return isValid;
    }
    
    function showFieldError($element, message) {
        $element.closest('.digiplanet-form-group').addClass('has-error');
        if ($element.hasClass('digiplanet-checkbox')) {
            $element.append('<span class="digiplanet-error-message">' + message + '</span>');
        } else {
            $element.after('<span class="digiplanet-error-message">' + message + '</span>');
        }
    }
});
</script>

<style>
.digiplanet-paystack-checkout {
    min-height: 100vh;
    background: #f8f9fa;
    padding: 40px 20px;
}

.digiplanet-checkout-container {
    max-width: 1200px;
    margin: 0 auto;
    background: white;
    border-radius: 12px;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
    overflow: hidden;
}

.digiplanet-checkout-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 30px 40px;
}

.digiplanet-checkout-header h1 {
    margin: 0 0 30px;
    font-size: 32px;
    font-weight: 700;
}

.digiplanet-checkout-progress {
    display: flex;
    justify-content: space-between;
    position: relative;
}

.digiplanet-checkout-progress:before {
    content: '';
    position: absolute;
    top: 24px;
    left: 0;
    right: 0;
    height: 2px;
    background: rgba(255, 255, 255, 0.3);
    z-index: 1;
}

.digiplanet-progress-step {
    display: flex;
    flex-direction: column;
    align-items: center;
    position: relative;
    z-index: 2;
    flex: 1;
}

.digiplanet-step-number {
    width: 50px;
    height: 50px;
    background: rgba(255, 255, 255, 0.3);
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: 18px;
    margin-bottom: 10px;
    transition: all 0.3s ease;
}

.digiplanet-progress-step.active .digiplanet-step-number {
    background: white;
    color: #667eea;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
}

.digiplanet-step-label {
    font-size: 14px;
    font-weight: 500;
    color: rgba(255, 255, 255, 0.8);
}

.digiplanet-progress-step.active .digiplanet-step-label {
    color: white;
    font-weight: 600;
}

.digiplanet-checkout-content {
    display: grid;
    grid-template-columns: 1fr 1.2fr;
    min-height: 600px;
}

.digiplanet-checkout-left {
    padding: 40px;
    border-right: 1px solid #e9ecef;
    background: #f8f9fa;
}

.digiplanet-order-summary {
    background: white;
    border-radius: 8px;
    padding: 25px;
    margin-bottom: 30px;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
}

.digiplanet-order-summary h3 {
    margin: 0 0 25px;
    font-size: 20px;
    font-weight: 600;
    color: #333;
    padding-bottom: 15px;
    border-bottom: 2px solid #f0f0f0;
}

.digiplanet-order-items {
    max-height: 300px;
    overflow-y: auto;
    margin-bottom: 25px;
    padding-right: 10px;
}

.digiplanet-order-item {
    display: flex;
    align-items: center;
    padding: 15px 0;
    border-bottom: 1px solid #f0f0f0;
}

.digiplanet-order-item:last-child {
    border-bottom: none;
}

.digiplanet-item-image {
    flex-shrink: 0;
    width: 60px;
    height: 60px;
    border-radius: 6px;
    overflow: hidden;
    margin-right: 15px;
}

.digiplanet-item-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.digiplanet-item-details {
    flex: 1;
}

.digiplanet-item-details h4 {
    margin: 0 0 5px;
    font-size: 16px;
    font-weight: 600;
    color: #333;
}

.digiplanet-item-meta {
    display: flex;
    gap: 15px;
    font-size: 14px;
    color: #6c757d;
}

.digiplanet-item-total {
    font-weight: 600;
    color: #333;
    font-size: 16px;
}

.digiplanet-order-totals {
    border-top: 2px solid #f0f0f0;
    padding-top: 20px;
}

.digiplanet-total-row {
    display: flex;
    justify-content: space-between;
    margin-bottom: 10px;
    font-size: 16px;
    color: #6c757d;
}

.digiplanet-grand-total {
    font-size: 20px;
    font-weight: 700;
    color: #333;
    margin-top: 10px;
    padding-top: 10px;
    border-top: 2px solid #f0f0f0;
}

.digiplanet-payment-methods {
    margin-top: 25px;
    padding-top: 20px;
    border-top: 1px solid #e9ecef;
}

.digiplanet-payment-methods h4 {
    margin: 0 0 15px;
    font-size: 16px;
    color: #6c757d;
}

.digiplanet-payment-icons {
    display: flex;
    gap: 15px;
    font-size: 24px;
    color: #6c757d;
}

.digiplanet-security-notice {
    background: white;
    border-radius: 8px;
    padding: 20px;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
}

.digiplanet-security-badge {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 10px;
    color: #28a745;
    font-weight: 600;
}

.digiplanet-security-badge i {
    font-size: 20px;
}

.digiplanet-security-notice p {
    margin: 0;
    font-size: 14px;
    color: #6c757d;
    line-height: 1.5;
}

.digiplanet-checkout-right {
    padding: 40px;
}

.digiplanet-payment-form-container {
    max-width: 500px;
    margin: 0 auto;
}

.digiplanet-payment-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
    padding-bottom: 20px;
    border-bottom: 2px solid #f0f0f0;
}

.digiplanet-payment-header h3 {
    margin: 0;
    font-size: 24px;
    font-weight: 700;
    color: #333;
}

.digiplanet-paystack-logo img {
    height: 30px;
    width: auto;
}

.digiplanet-test-mode-notice {
    background: #fff3cd;
    border: 1px solid #ffc107;
    border-radius: 8px;
    padding: 15px;
    margin-bottom: 25px;
    display: flex;
    flex-direction: column;
    gap: 5px;
}

.digiplanet-test-mode-notice i {
    color: #856404;
    margin-right: 10px;
}

.digiplanet-test-mode-notice span {
    font-weight: 600;
    color: #856404;
}

.digiplanet-test-mode-notice p {
    margin: 5px 0 0;
    font-size: 14px;
    color: #856404;
}

.digiplanet-payment-options {
    margin: 25px 0;
}

.digiplanet-payment-options h4 {
    margin: 0 0 15px;
    font-size: 16px;
    font-weight: 600;
    color: #333;
}

.digiplanet-payment-option {
    margin-bottom: 10px;
}

.digiplanet-payment-option input[type="radio"] {
    display: none;
}

.digiplanet-payment-option label {
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 15px;
    border: 2px solid #e9ecef;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.3s ease;
}

.digiplanet-payment-option label:hover {
    border-color: #dee2e6;
    background: #f8f9fa;
}

.digiplanet-payment-option input[type="radio"]:checked + label {
    border-color: #667eea;
    background: rgba(102, 126, 234, 0.1);
}

.digiplanet-payment-option label i {
    font-size: 20px;
    color: #6c757d;
}

.digiplanet-payment-option input[type="radio"]:checked + label i {
    color: #667eea;
}

.digiplanet-payment-option label span {
    font-weight: 500;
    color: #495057;
}

.digiplanet-payment-fields {
    margin: 25px 0;
    padding: 25px;
    background: #f8f9fa;
    border-radius: 8px;
}

.digiplanet-bank-instructions {
    background: white;
    padding: 20px;
    border-radius: 6px;
    border-left: 4px solid #28a745;
}

.digiplanet-bank-instructions p {
    margin: 0;
    color: #6c757d;
    line-height: 1.6;
}

.digiplanet-terms-agreement {
    margin: 25px 0;
}

.digiplanet-payment-actions {
    display: flex;
    gap: 15px;
    margin-top: 30px;
}

.digiplanet-btn-secondary {
    background: #6c757d;
    color: white;
    border: none;
    padding: 14px 30px;
    border-radius: 8px;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    text-decoration: none;
}

.digiplanet-btn-secondary:hover {
    background: #5a6268;
    color: white;
    text-decoration: none;
}

.digiplanet-btn-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: none;
    padding: 14px 30px;
    border-radius: 8px;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    flex: 1;
}

.digiplanet-btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
}

.digiplanet-support-info {
    margin-top: 30px;
    padding-top: 20px;
    border-top: 1px solid #e9ecef;
    text-align: center;
}

.digiplanet-support-info p {
    margin: 0;
    color: #6c757d;
    font-size: 14px;
}

.digiplanet-support-info a {
    color: #667eea;
    text-decoration: none;
    font-weight: 500;
}

.digiplanet-support-info a:hover {
    text-decoration: underline;
}

/* Payment Modal */
.digiplanet-payment-processing {
    text-align: center;
    padding: 40px;
}

.digiplanet-spinner {
    margin: 0 auto 30px;
    width: 80px;
    height: 80px;
    position: relative;
}

.digiplanet-spinner-circle {
    width: 100%;
    height: 100%;
    border: 8px solid #f0f0f0;
    border-top-color: #667eea;
    border-radius: 50%;
    animation: digiplanet-spin 1s linear infinite;
}

@keyframes digiplanet-spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

.digiplanet-payment-processing h3 {
    margin: 0 0 15px;
    font-size: 24px;
    color: #333;
}

.digiplanet-payment-processing p {
    color: #6c757d;
    margin-bottom: 20px;
}

.digiplanet-payment-status .success {
    color: #28a745;
}

.digiplanet-payment-status .error {
    color: #dc3545;
}

.digiplanet-payment-status .info {
    color: #17a2b8;
}

.bank-transfer-details {
    text-align: left;
    background: #f8f9fa;
    padding: 20px;
    border-radius: 8px;
    border: 1px solid #e9ecef;
}

.bank-transfer-details h4 {
    margin: 0 0 15px;
    color: #333;
}

.bank-transfer-details p {
    margin: 0 0 10px;
    color: #6c757d;
}

.bank-transfer-details strong {
    color: #333;
}

/* Responsive */
@media (max-width: 992px) {
    .digiplanet-checkout-content {
        grid-template-columns: 1fr;
    }
    
    .digiplanet-checkout-left {
        border-right: none;
        border-bottom: 1px solid #e9ecef;
    }
    
    .digiplanet-checkout-progress {
        flex-wrap: wrap;
        gap: 20px;
    }
    
    .digiplanet-progress-step {
        flex: none;
        width: calc(50% - 10px);
    }
}

@media (max-width: 768px) {
    .digiplanet-checkout-header {
        padding: 20px;
    }
    
    .digiplanet-checkout-left,
    .digiplanet-checkout-right {
        padding: 20px;
    }
    
    .digiplanet-payment-actions {
        flex-direction: column;
    }
    
    .digiplanet-step-label {
        font-size: 12px;
    }
}

@media (max-width: 576px) {
    .digiplanet-paystack-checkout {
        padding: 20px 10px;
    }
    
    .digiplanet-checkout-header h1 {
        font-size: 24px;
    }
    
    .digiplanet-progress-step {
        width: 100%;
    }
    
    .digiplanet-checkout-progress:before {
        display: none;
    }
    
    .digiplanet-order-item {
        flex-direction: column;
        align-items: flex-start;
        gap: 15px;
    }
    
    .digiplanet-item-image {
        margin-right: 0;
    }
    
    .digiplanet-item-details {
        width: 100%;
    }
    
    .digiplanet-item-total {
        align-self: flex-end;
    }
}
</style>