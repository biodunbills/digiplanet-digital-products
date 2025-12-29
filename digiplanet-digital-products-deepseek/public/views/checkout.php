<?php
/**
 * Checkout template
 */

if (!defined('ABSPATH')) {
    exit;
}

$cart_manager = Digiplanet_Cart_Manager::get_instance();
$cart = $cart_manager->get_cart();
$subtotal = $cart_manager->get_cart_subtotal();
$tax = $cart_manager->get_cart_tax();
$total = $cart_manager->get_cart_total_with_tax();

if (empty($cart)) {
    wp_redirect(get_permalink(get_option('digiplanet_cart_page_id')));
    exit;
}

$user_id = get_current_user_id();
$user_data = $user_id ? get_userdata($user_id) : null;

// Get available payment gateways
$payments = Digiplanet_Payments::get_instance();
$gateways = $payments->get_available_gateways();
?>

<div class="digiplanet-checkout">
    <h1 class="digiplanet-checkout-title"><?php _e('Checkout', 'digiplanet-digital-products'); ?></h1>
    
    <div class="digiplanet-checkout-steps">
        <div class="digiplanet-step active" data-step="1">
            <span class="digiplanet-step-number">1</span>
            <span class="digiplanet-step-label"><?php _e('Billing Details', 'digiplanet-digital-products'); ?></span>
        </div>
        <div class="digiplanet-step" data-step="2">
            <span class="digiplanet-step-number">2</span>
            <span class="digiplanet-step-label"><?php _e('Payment', 'digiplanet-digital-products'); ?></span>
        </div>
        <div class="digiplanet-step" data-step="3">
            <span class="digiplanet-step-number">3</span>
            <span class="digiplanet-step-label"><?php _e('Confirmation', 'digiplanet-digital-products'); ?></span>
        </div>
    </div>
    
    <form class="digiplanet-checkout-form" id="digiplanet-checkout-form" method="post">
        <?php wp_nonce_field('digiplanet_checkout', 'digiplanet_checkout_nonce'); ?>
        
        <!-- Step 1: Billing Details -->
        <div class="digiplanet-checkout-step active" data-step="1">
            <h2><?php _e('Billing Details', 'digiplanet-digital-products'); ?></h2>
            
            <div class="digiplanet-form-section">
                <div class="digiplanet-form-row">
                    <div class="digiplanet-form-column">
                        <label for="digiplanet-first-name"><?php _e('First Name', 'digiplanet-digital-products'); ?> *</label>
                        <input type="text" 
                               id="digiplanet-first-name" 
                               name="first_name" 
                               class="digiplanet-form-control" 
                               value="<?php echo $user_data ? esc_attr($user_data->first_name) : ''; ?>" 
                               required>
                    </div>
                    <div class="digiplanet-form-column">
                        <label for="digiplanet-last-name"><?php _e('Last Name', 'digiplanet-digital-products'); ?> *</label>
                        <input type="text" 
                               id="digiplanet-last-name" 
                               name="last_name" 
                               class="digiplanet-form-control" 
                               value="<?php echo $user_data ? esc_attr($user_data->last_name) : ''; ?>" 
                               required>
                    </div>
                </div>
                
                <div class="digiplanet-form-row">
                    <label for="digiplanet-email"><?php _e('Email Address', 'digiplanet-digital-products'); ?> *</label>
                    <input type="email" 
                           id="digiplanet-email" 
                           name="email" 
                           class="digiplanet-form-control" 
                           value="<?php echo $user_data ? esc_attr($user_data->user_email) : ''; ?>" 
                           required>
                </div>
                
                <div class="digiplanet-form-row">
                    <label for="digiplanet-phone"><?php _e('Phone Number', 'digiplanet-digital-products'); ?></label>
                    <input type="tel" 
                           id="digiplanet-phone" 
                           name="phone" 
                           class="digiplanet-form-control" 
                           value="<?php echo $user_id ? get_user_meta($user_id, 'digiplanet_phone', true) : ''; ?>">
                </div>
                
                <div class="digiplanet-form-row">
                    <label for="digiplanet-company"><?php _e('Company Name', 'digiplanet-digital-products'); ?></label>
                    <input type="text" 
                           id="digiplanet-company" 
                           name="company" 
                           class="digiplanet-form-control" 
                           value="<?php echo $user_id ? get_user_meta($user_id, 'digiplanet_company', true) : ''; ?>">
                </div>
                
                <div class="digiplanet-form-row">
                    <label for="digiplanet-address"><?php _e('Address', 'digiplanet-digital-products'); ?></label>
                    <textarea id="digiplanet-address" 
                              name="address" 
                              class="digiplanet-form-control" 
                              rows="3"><?php echo $user_id ? get_user_meta($user_id, 'digiplanet_address', true) : ''; ?></textarea>
                </div>
                
                <div class="digiplanet-form-row">
                    <div class="digiplanet-form-column">
                        <label for="digiplanet-city"><?php _e('City', 'digiplanet-digital-products'); ?></label>
                        <input type="text" 
                               id="digiplanet-city" 
                               name="city" 
                               class="digiplanet-form-control" 
                               value="<?php echo $user_id ? get_user_meta($user_id, 'digiplanet_city', true) : ''; ?>">
                    </div>
                    <div class="digiplanet-form-column">
                        <label for="digiplanet-state"><?php _e('State / Province', 'digiplanet-digital-products'); ?></label>
                        <input type="text" 
                               id="digiplanet-state" 
                               name="state" 
                               class="digiplanet-form-control" 
                               value="<?php echo $user_id ? get_user_meta($user_id, 'digiplanet_state', true) : ''; ?>">
                    </div>
                </div>
                
                <div class="digiplanet-form-row">
                    <div class="digiplanet-form-column">
                        <label for="digiplanet-postcode"><?php _e('Postcode / ZIP', 'digiplanet-digital-products'); ?></label>
                        <input type="text" 
                               id="digiplanet-postcode" 
                               name="postcode" 
                               class="digiplanet-form-control" 
                               value="<?php echo $user_id ? get_user_meta($user_id, 'digiplanet_postcode', true) : ''; ?>">
                    </div>
                    <div class="digiplanet-form-column">
                        <label for="digiplanet-country"><?php _e('Country', 'digiplanet-digital-products'); ?></label>
                        <select id="digiplanet-country" name="country" class="digiplanet-form-control">
                            <option value=""><?php _e('Select Country', 'digiplanet-digital-products'); ?></option>
                            <option value="US" <?php selected($user_id ? get_user_meta($user_id, 'digiplanet_country', true) : '', 'US'); ?>>
                                United States
                            </option>
                            <option value="CA" <?php selected($user_id ? get_user_meta($user_id, 'digiplanet_country', true) : '', 'CA'); ?>>
                                Canada
                            </option>
                            <option value="GB" <?php selected($user_id ? get_user_meta($user_id, 'digiplanet_country', true) : '', 'GB'); ?>>
                                United Kingdom
                            </option>
                            <option value="NG" <?php selected($user_id ? get_user_meta($user_id, 'digiplanet_country', true) : '', 'NG'); ?>>
                                Nigeria
                            </option>
                            <!-- Add more countries as needed -->
                        </select>
                    </div>
                </div>
            </div>
            
            <!-- Account Creation (for guests) -->
            <?php if (!is_user_logged_in() && get_option('users_can_register')): ?>
                <div class="digiplanet-form-section">
                    <h3><?php _e('Create an Account', 'digiplanet-digital-products'); ?></h3>
                    <div class="digiplanet-form-row">
                        <label class="digiplanet-checkbox-label">
                            <input type="checkbox" name="create_account" value="1" id="digiplanet-create-account">
                            <span><?php _e('Create an account for faster checkout next time', 'digiplanet-digital-products'); ?></span>
                        </label>
                    </div>
                    
                    <div class="digiplanet-account-fields" style="display: none;">
                        <div class="digiplanet-form-row">
                            <label for="digiplanet-account-password"><?php _e('Create Password', 'digiplanet-digital-products'); ?> *</label>
                            <input type="password" 
                                   id="digiplanet-account-password" 
                                   name="account_password" 
                                   class="digiplanet-form-control" 
                                   disabled>
                            <p class="digiplanet-form-help">
                                <?php _e('Password must be at least 8 characters long.', 'digiplanet-digital-products'); ?>
                            </p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
            
            <!-- Order Notes -->
            <div class="digiplanet-form-section">
                <h3><?php _e('Additional Information', 'digiplanet-digital-products'); ?></h3>
                <div class="digiplanet-form-row">
                    <label for="digiplanet-order-notes"><?php _e('Order Notes', 'digiplanet-digital-products'); ?></label>
                    <textarea id="digiplanet-order-notes" 
                              name="order_notes" 
                              class="digiplanet-form-control" 
                              rows="4"
                              placeholder="<?php _e('Notes about your order, e.g. special notes for delivery.', 'digiplanet-digital-products'); ?>"></textarea>
                </div>
            </div>
            
            <div class="digiplanet-step-actions">
                <button type="button" class="digiplanet-btn digiplanet-btn-secondary digiplanet-step-back" disabled>
                    <?php _e('Back', 'digiplanet-digital-products'); ?>
                </button>
                <button type="button" class="digiplanet-btn digiplanet-btn-primary digiplanet-step-next">
                    <?php _e('Continue to Payment', 'digiplanet-digital-products'); ?>
                    <span class="dashicons dashicons-arrow-right-alt"></span>
                </button>
            </div>
        </div>
        
        <!-- Step 2: Payment -->
        <div class="digiplanet-checkout-step" data-step="2">
            <h2><?php _e('Payment Method', 'digiplanet-digital-products'); ?></h2>
            
            <!-- Order Review -->
            <div class="digiplanet-order-review">
                <h3><?php _e('Your Order', 'digiplanet-digital-products'); ?></h3>
                
                <table class="digiplanet-order-review-table">
                    <thead>
                        <tr>
                            <th><?php _e('Product', 'digiplanet-digital-products'); ?></th>
                            <th><?php _e('Subtotal', 'digiplanet-digital-products'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $product_manager = Digiplanet_Product_Manager::get_instance();
                        foreach ($cart as $item): 
                            $item_total = $item['price'] * $item['quantity'];
                        ?>
                            <tr>
                                <td>
                                    <?php echo esc_html($item['name']); ?> × <?php echo $item['quantity']; ?>
                                </td>
                                <td>
                                    <?php echo $product_manager->format_price($item_total); ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
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
                        
                        <tr class="digiplanet-order-total">
                            <th><?php _e('Total', 'digiplanet-digital-products'); ?></th>
                            <td><?php echo $product_manager->format_price($total); ?></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            
            <!-- Payment Methods -->
            <div class="digiplanet-payment-methods">
                <h3><?php _e('Select Payment Method', 'digiplanet-digital-products'); ?></h3>
                
                <?php if (empty($gateways)): ?>
                    <div class="digiplanet-alert digiplanet-alert-warning">
                        <?php _e('No payment methods are currently available.', 'digiplanet-digital-products'); ?>
                    </div>
                <?php else: ?>
                    <?php foreach ($gateways as $gateway_id => $gateway): ?>
                        <div class="digiplanet-payment-method">
                            <input type="radio" 
                                   id="payment-<?php echo $gateway_id; ?>" 
                                   name="payment_method" 
                                   value="<?php echo $gateway_id; ?>"
                                   <?php echo $gateway_id === 'stripe' ? 'checked' : ''; ?>>
                            <label for="payment-<?php echo $gateway_id; ?>">
                                <span class="digiplanet-payment-method-label">
                                    <?php echo $payments->get_payment_method_label($gateway_id); ?>
                                </span>
                                <?php if ($gateway_id === 'stripe'): ?>
                                    <span class="digiplanet-payment-icons">
                                        <span class="dashicons dashicons-visibility"></span>
                                        <?php _e('Credit/Debit Card', 'digiplanet-digital-products'); ?>
                                    </span>
                                <?php elseif ($gateway_id === 'paystack'): ?>
                                    <span class="digiplanet-payment-icons">
                                        <span class="dashicons dashicons-money"></span>
                                        <?php _e('Bank Transfer & Cards', 'digiplanet-digital-products'); ?>
                                    </span>
                                <?php endif; ?>
                            </label>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            
            <!-- Payment Forms (loaded dynamically) -->
            <div class="digiplanet-payment-forms">
                <?php foreach ($gateways as $gateway_id => $gateway): ?>
                    <div class="digiplanet-payment-form" id="<?php echo $gateway_id; ?>-payment-form" style="display: none;">
                        <?php 
                        // Load payment form template
                        $template_path = DIGIPLANET_TEMPLATES_DIR . 'checkout/' . $gateway_id . '-checkout.php';
                        if (file_exists($template_path)) {
                            include $template_path;
                        }
                        ?>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <!-- Terms and Conditions -->
            <div class="digiplanet-form-section">
                <div class="digiplanet-form-row">
                    <label class="digiplanet-checkbox-label">
                        <input type="checkbox" 
                               name="terms" 
                               value="1" 
                               id="digiplanet-terms" 
                               required>
                        <span>
                            <?php 
                            $terms_page_id = get_option('digiplanet_terms_page_id');
                            if ($terms_page_id) {
                                printf(
                                    __('I have read and agree to the %s', 'digiplanet-digital-products'),
                                    '<a href="' . get_permalink($terms_page_id) . '" target="_blank">' . __('terms and conditions', 'digiplanet-digital-products') . '</a>'
                                );
                            } else {
                                _e('I have read and agree to the terms and conditions', 'digiplanet-digital-products');
                            }
                            ?>
                            *
                        </span>
                    </label>
                </div>
            </div>
            
            <div class="digiplanet-step-actions">
                <button type="button" class="digiplanet-btn digiplanet-btn-secondary digiplanet-step-back">
                    <span class="dashicons dashicons-arrow-left-alt"></span>
                    <?php _e('Back to Billing', 'digiplanet-digital-products'); ?>
                </button>
                <button type="submit" class="digiplanet-btn digiplanet-btn-primary digiplanet-place-order">
                    <?php _e('Place Order', 'digiplanet-digital-products'); ?>
                    <span class="dashicons dashicons-lock"></span>
                </button>
            </div>
        </div>
        
        <!-- Step 3: Confirmation (shown after order placement) -->
        <div class="digiplanet-checkout-step" data-step="3">
            <div class="digiplanet-order-confirmation" style="display: none;">
                <!-- Content loaded via AJAX -->
            </div>
        </div>
    </form>
</div>

<script>
jQuery(document).ready(function($) {
    // Step navigation
    $('.digiplanet-step-next').on('click', function() {
        var currentStep = $('.digiplanet-checkout-step.active').data('step');
        var nextStep = currentStep + 1;
        
        // Validate current step
        if (currentStep === 1) {
            if (!validateBillingDetails()) {
                return;
            }
        }
        
        // Update steps
        $('.digiplanet-step').removeClass('active');
        $('.digiplanet-step[data-step="' + nextStep + '"]').addClass('active');
        
        // Update content
        $('.digiplanet-checkout-step').removeClass('active');
        $('.digiplanet-checkout-step[data-step="' + nextStep + '"]').addClass('active');
    });
    
    $('.digiplanet-step-back').on('click', function() {
        var currentStep = $('.digiplanet-checkout-step.active').data('step');
        var prevStep = currentStep - 1;
        
        // Update steps
        $('.digiplanet-step').removeClass('active');
        $('.digiplanet-step[data-step="' + prevStep + '"]').addClass('active');
        
        // Update content
        $('.digiplanet-checkout-step').removeClass('active');
        $('.digiplanet-checkout-step[data-step="' + prevStep + '"]').addClass('active');
    });
    
    // Create account toggle
    $('#digiplanet-create-account').on('change', function() {
        if ($(this).is(':checked')) {
            $('.digiplanet-account-fields').show();
            $('#digiplanet-account-password').prop('disabled', false).prop('required', true);
        } else {
            $('.digiplanet-account-fields').hide();
            $('#digiplanet-account-password').prop('disabled', true).prop('required', false);
        }
    });
    
    // Payment method selection
    $('input[name="payment_method"]').on('change', function() {
        var method = $(this).val();
        
        // Hide all payment forms
        $('.digiplanet-payment-form').hide();
        
        // Show selected payment form
        $('#' + method + '-payment-form').show();
    });
    
    // Initialize first payment method
    $('input[name="payment_method"]:checked').trigger('change');
    
    // Form submission
    $('#digiplanet-checkout-form').on('submit', function(e) {
        e.preventDefault();
        
        if (!validatePaymentDetails()) {
            return;
        }
        
        // Show loading
        $('.digiplanet-place-order').prop('disabled', true).addClass('loading');
        
        // Collect form data
        var formData = $(this).serialize();
        
        // Add cart data
        formData += '&action=digiplanet_process_checkout';
        formData += '&nonce=' + digiplanet_frontend.nonce;
        
        $.ajax({
            url: digiplanet_frontend.ajax_url,
            type: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    // Move to confirmation step
                    $('.digiplanet-step').removeClass('active');
                    $('.digiplanet-step[data-step="3"]').addClass('active');
                    
                    $('.digiplanet-checkout-step').removeClass('active');
                    $('.digiplanet-checkout-step[data-step="3"]').addClass('active');
                    
                    // Show confirmation
                    $('.digiplanet-order-confirmation').show().html(response.data.html);
                    
                    // Clear cart
                    DigiplanetCart.updateCartCount(0);
                    DigiplanetCart.updateCartTotal('0.00');
                } else {
                    alert(response.data.message);
                    $('.digiplanet-place-order').prop('disabled', false).removeClass('loading');
                }
            },
            error: function() {
                alert(digiplanet_frontend.error_message);
                $('.digiplanet-place-order').prop('disabled', false).removeClass('loading');
            }
        });
    });
    
    // Validation functions
    function validateBillingDetails() {
        var valid = true;
        
        // Required fields
        $('.digiplanet-checkout-step[data-step="1"] [required]').each(function() {
            if (!$(this).val().trim()) {
                valid = false;
                $(this).addClass('error');
            } else {
                $(this).removeClass('error');
            }
        });
        
        // Email validation
        var email = $('#digiplanet-email').val();
        if (email && !isValidEmail(email)) {
            valid = false;
            $('#digiplanet-email').addClass('error');
            alert('Please enter a valid email address.');
        }
        
        if (!valid) {
            alert('Please fill in all required fields.');
        }
        
        return valid;
    }
    
    function validatePaymentDetails() {
        // Check terms accepted
        if (!$('#digiplanet-terms').is(':checked')) {
            alert('You must accept the terms and conditions to proceed.');
            return false;
        }
        
        // Validate selected payment method
        var paymentMethod = $('input[name="payment_method"]:checked').val();
        if (!paymentMethod) {
            alert('Please select a payment method.');
            return false;
        }
        
        // Additional validation based on payment method
        if (paymentMethod === 'stripe') {
            // Validate Stripe card details
            // This would be handled by Stripe Elements
        }
        
        return true;
    }
    
    function isValidEmail(email) {
        var re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return re.test(email);
    }
});
</script>

<style>
.digiplanet-checkout {
    max-width: 1000px;
    margin: 0 auto;
    padding: 30px 20px;
}

.digiplanet-checkout-title {
    text-align: center;
    margin-bottom: 40px;
    color: #333;
}

.digiplanet-checkout-steps {
    display: flex;
    justify-content: center;
    margin-bottom: 40px;
    border-bottom: 1px solid #dee2e6;
}

.digiplanet-step {
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 0 20px 20px;
    position: relative;
    color: #6c757d;
}

.digiplanet-step.active {
    color: #3498db;
}

.digiplanet-step.active .digiplanet-step-number {
    background: #3498db;
    color: white;
}

.digiplanet-step.active:after {
    background: #3498db;
}

.digiplanet-step-number {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: #6c757d;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    margin-bottom: 10px;
}

.digiplanet-step-label {
    font-size: 14px;
    font-weight: 600;
}

.digiplanet-step:after {
    content: '';
    position: absolute;
    bottom: -1px;
    left: 0;
    right: 0;
    height: 3px;
    background: transparent;
}

.digiplanet-step.active:after {
    background: #3498db;
}

.digiplanet-checkout-step {
    display: none;
}

.digiplanet-checkout-step.active {
    display: block;
}

.digiplanet-form-section {
    background: white;
    padding: 25px;
    border-radius: 8px;
    margin-bottom: 25px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
}

.digiplanet-form-section h3 {
    margin-bottom: 20px;
    padding-bottom: 10px;
    border-bottom: 2px solid #3498db;
    color: #333;
}

.digiplanet-form-row {
    margin-bottom: 20px;
}

.digiplanet-form-column {
    display: inline-block;
    width: calc(50% - 10px);
    margin-right: 20px;
}

.digiplanet-form-column:last-child {
    margin-right: 0;
}

.digiplanet-form-label {
    display: block;
    margin-bottom: 8px;
    font-weight: 600;
    color: #333;
}

.digiplanet-form-control {
    width: 100%;
    padding: 12px;
    border: 1px solid #dee2e6;
    border-radius: 4px;
    font-size: 14px;
    transition: border-color 0.3s;
}

.digiplanet-form-control:focus {
    outline: none;
    border-color: #3498db;
    box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
}

.digiplanet-form-control.error {
    border-color: #e74c3c;
}

.digiplanet-checkbox-label {
    display: flex;
    align-items: center;
    cursor: pointer;
}

.digiplanet-checkbox-label input {
    margin-right: 10px;
}

.digiplanet-form-help {
    margin-top: 5px;
    font-size: 12px;
    color: #6c757d;
}

.digiplanet-step-actions {
    display: flex;
    justify-content: space-between;
    margin-top: 30px;
    padding-top: 30px;
    border-top: 1px solid #dee2e6;
}

.digiplanet-order-review {
    background: white;
    padding: 25px;
    border-radius: 8px;
    margin-bottom: 25px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
}

.digiplanet-order-review-table {
    width: 100%;
    border-collapse: collapse;
    margin: 20px 0;
}

.digiplanet-order-review-table th,
.digiplanet-order-review-table td {
    padding: 12px 0;
    border-bottom: 1px solid #dee2e6;
    text-align: left;
}

.digiplanet-order-review-table tfoot th,
.digiplanet-order-review-table tfoot td {
    font-weight: bold;
}

.digiplanet-order-review-table .digiplanet-order-total th,
.digiplanet-order-review-table .digiplanet-order-total td {
    border-bottom: none;
    font-size: 18px;
    color: #2c3e50;
}

.digiplanet-payment-methods {
    background: white;
    padding: 25px;
    border-radius: 8px;
    margin-bottom: 25px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
}

.digiplanet-payment-method {
    margin-bottom: 15px;
}

.digiplanet-payment-method input[type="radio"] {
    margin-right: 10px;
}

.digiplanet-payment-method label {
    display: flex;
    align-items: center;
    justify-content: space-between;
    cursor: pointer;
    padding: 15px;
    border: 1px solid #dee2e6;
    border-radius: 4px;
    transition: all 0.3s;
}

.digiplanet-payment-method input[type="radio"]:checked + label {
    border-color: #3498db;
    background-color: rgba(52, 152, 219, 0.05);
}

.digiplanet-payment-method-label {
    font-weight: 600;
    color: #333;
}

.digiplanet-payment-icons {
    color: #6c757d;
    font-size: 13px;
}

.digiplanet-payment-icons .dashicons {
    margin-right: 5px;
    vertical-align: middle;
}

.digiplanet-payment-forms {
    margin-bottom: 25px;
}

.digiplanet-payment-form {
    background: white;
    padding: 25px;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
}

.digiplanet-place-order.loading {
    position: relative;
    padding-right: 40px;
}

.digiplanet-place-order.loading:after {
    content: '';
    position: absolute;
    right: 15px;
    top: 50%;
    transform: translateY(-50%);
    width: 20px;
    height: 20px;
    border: 2px solid rgba(255,255,255,0.3);
    border-top-color: white;
    border-radius: 50%;
    animation: spin 0.8s linear infinite;
}

@keyframes spin {
    to { transform: translateY(-50%) rotate(360deg); }
}

@media (max-width: 768px) {
    .digiplanet-checkout-steps {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .digiplanet-step {
        flex-direction: row;
        padding: 10px 0;
        width: 100%;
    }
    
    .digiplanet-step-number {
        margin-right: 10px;
        margin-bottom: 0;
    }
    
    .digiplanet-form-column {
        width: 100%;
        margin-right: 0;
        margin-bottom: 15px;
    }
    
    .digiplanet-step-actions {
        flex-direction: column;
        gap: 10px;
    }
    
    .digiplanet-step-actions .digiplanet-btn {
        width: 100%;
        justify-content: center;
    }
}
</style>