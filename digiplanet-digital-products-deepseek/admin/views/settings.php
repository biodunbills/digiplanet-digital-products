<?php
/**
 * Settings admin view
 */

if (!defined('ABSPATH')) {
    exit;
}

$settings = get_option('digiplanet_settings', array());
$default_settings = array(
    'currency' => 'USD',
    'currency_position' => 'left',
    'thousand_separator' => ',',
    'decimal_separator' => '.',
    'decimals' => 2,
    'enable_tax' => 'no',
    'tax_rate' => 0,
    'enable_coupons' => 'no',
    'enable_reviews' => 'yes',
    'enable_ratings' => 'yes',
    'download_limit' => 5,
    'download_expiry' => 30,
    'license_expiry' => 365,
    'stripe_enabled' => 'no',
    'stripe_test_mode' => 'yes',
    'stripe_test_publishable_key' => '',
    'stripe_test_secret_key' => '',
    'stripe_live_publishable_key' => '',
    'stripe_live_secret_key' => '',
    'paystack_enabled' => 'no',
    'paystack_test_mode' => 'yes',
    'paystack_test_public_key' => '',
    'paystack_test_secret_key' => '',
    'paystack_live_public_key' => '',
    'paystack_live_secret_key' => '',
    'email_from_name' => get_bloginfo('name'),
    'email_from_address' => get_bloginfo('admin_email'),
    'products_per_page' => 12,
    'cart_page_id' => '',
    'checkout_page_id' => '',
    'account_page_id' => '',
    'terms_page_id' => '',
    'privacy_page_id' => '',
);

$settings = wp_parse_args($settings, $default_settings);

// Handle form submission
if (isset($_POST['digiplanet_settings_nonce']) && wp_verify_nonce($_POST['digiplanet_settings_nonce'], 'digiplanet_save_settings')) {
    $new_settings = array();
    
    // General Settings
    $new_settings['currency'] = sanitize_text_field($_POST['currency'] ?? 'USD');
    $new_settings['currency_position'] = sanitize_text_field($_POST['currency_position'] ?? 'left');
    $new_settings['thousand_separator'] = sanitize_text_field($_POST['thousand_separator'] ?? ',');
    $new_settings['decimal_separator'] = sanitize_text_field($_POST['decimal_separator'] ?? '.');
    $new_settings['decimals'] = absint($_POST['decimals'] ?? 2);
    $new_settings['enable_tax'] = isset($_POST['enable_tax']) ? 'yes' : 'no';
    $new_settings['tax_rate'] = floatval($_POST['tax_rate'] ?? 0);
    $new_settings['enable_coupons'] = isset($_POST['enable_coupons']) ? 'yes' : 'no';
    $new_settings['enable_reviews'] = isset($_POST['enable_reviews']) ? 'yes' : 'no';
    $new_settings['enable_ratings'] = isset($_POST['enable_ratings']) ? 'yes' : 'no';
    $new_settings['products_per_page'] = absint($_POST['products_per_page'] ?? 12);
    
    // Download Settings
    $new_settings['download_limit'] = absint($_POST['download_limit'] ?? 5);
    $new_settings['download_expiry'] = absint($_POST['download_expiry'] ?? 30);
    $new_settings['license_expiry'] = absint($_POST['license_expiry'] ?? 365);
    
    // Stripe Settings
    $new_settings['stripe_enabled'] = isset($_POST['stripe_enabled']) ? 'yes' : 'no';
    $new_settings['stripe_test_mode'] = isset($_POST['stripe_test_mode']) ? 'yes' : 'no';
    $new_settings['stripe_test_publishable_key'] = sanitize_text_field($_POST['stripe_test_publishable_key'] ?? '');
    $new_settings['stripe_test_secret_key'] = sanitize_text_field($_POST['stripe_test_secret_key'] ?? '');
    $new_settings['stripe_live_publishable_key'] = sanitize_text_field($_POST['stripe_live_publishable_key'] ?? '');
    $new_settings['stripe_live_secret_key'] = sanitize_text_field($_POST['stripe_live_secret_key'] ?? '');
    
    // Paystack Settings
    $new_settings['paystack_enabled'] = isset($_POST['paystack_enabled']) ? 'yes' : 'no';
    $new_settings['paystack_test_mode'] = isset($_POST['paystack_test_mode']) ? 'yes' : 'no';
    $new_settings['paystack_test_public_key'] = sanitize_text_field($_POST['paystack_test_public_key'] ?? '');
    $new_settings['paystack_test_secret_key'] = sanitize_text_field($_POST['paystack_test_secret_key'] ?? '');
    $new_settings['paystack_live_public_key'] = sanitize_text_field($_POST['paystack_live_public_key'] ?? '');
    $new_settings['paystack_live_secret_key'] = sanitize_text_field($_POST['paystack_live_secret_key'] ?? '');
    
    // Email Settings
    $new_settings['email_from_name'] = sanitize_text_field($_POST['email_from_name'] ?? get_bloginfo('name'));
    $new_settings['email_from_address'] = sanitize_email($_POST['email_from_address'] ?? get_bloginfo('admin_email'));
    
    // Page Settings
    $new_settings['cart_page_id'] = absint($_POST['cart_page_id'] ?? '');
    $new_settings['checkout_page_id'] = absint($_POST['checkout_page_id'] ?? '');
    $new_settings['account_page_id'] = absint($_POST['account_page_id'] ?? '');
    $new_settings['terms_page_id'] = absint($_POST['terms_page_id'] ?? '');
    $new_settings['privacy_page_id'] = absint($_POST['privacy_page_id'] ?? '');
    
    update_option('digiplanet_settings', $new_settings);
    $settings = $new_settings;
    
    echo '<div class="notice notice-success is-dismissible"><p>' . __('Settings saved successfully.', 'digiplanet-digital-products') . '</p></div>';
}
?>

<div class="wrap digiplanet-admin-wrap">
    <h1><?php _e('Digiplanet Settings', 'digiplanet-digital-products'); ?></h1>
    
    <form method="post" action="">
        <?php wp_nonce_field('digiplanet_save_settings', 'digiplanet_settings_nonce'); ?>
        
        <div class="digiplanet-settings-tabs">
            <nav class="nav-tab-wrapper">
                <a href="#general" class="nav-tab nav-tab-active"><?php _e('General', 'digiplanet-digital-products'); ?></a>
                <a href="#currency" class="nav-tab"><?php _e('Currency', 'digiplanet-digital-products'); ?></a>
                <a href="#downloads" class="nav-tab"><?php _e('Downloads', 'digiplanet-digital-products'); ?></a>
                <a href="#stripe" class="nav-tab"><?php _e('Stripe', 'digiplanet-digital-products'); ?></a>
                <a href="#paystack" class="nav-tab"><?php _e('Paystack', 'digiplanet-digital-products'); ?></a>
                <a href="#email" class="nav-tab"><?php _e('Email', 'digiplanet-digital-products'); ?></a>
                <a href="#pages" class="nav-tab"><?php _e('Pages', 'digiplanet-digital-products'); ?></a>
            </nav>
            
            <div class="digiplanet-tab-content">
                <!-- General Settings -->
                <div id="general" class="tab-pane active">
                    <table class="form-table">
                        <tr>
                            <th><label for="enable_reviews"><?php _e('Enable Reviews', 'digiplanet-digital-products'); ?></label></th>
                            <td>
                                <input type="checkbox" id="enable_reviews" name="enable_reviews" value="1" <?php checked($settings['enable_reviews'], 'yes'); ?>>
                                <p class="description"><?php _e('Allow customers to leave reviews for products.', 'digiplanet-digital-products'); ?></p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th><label for="enable_ratings"><?php _e('Enable Ratings', 'digiplanet-digital-products'); ?></label></th>
                            <td>
                                <input type="checkbox" id="enable_ratings" name="enable_ratings" value="1" <?php checked($settings['enable_ratings'], 'yes'); ?>>
                                <p class="description"><?php _e('Allow customers to rate products.', 'digiplanet-digital-products'); ?></p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th><label for="enable_tax"><?php _e('Enable Tax', 'digiplanet-digital-products'); ?></label></th>
                            <td>
                                <input type="checkbox" id="enable_tax" name="enable_tax" value="1" <?php checked($settings['enable_tax'], 'yes'); ?>>
                                <p class="description"><?php _e('Enable tax calculations.', 'digiplanet-digital-products'); ?></p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th><label for="tax_rate"><?php _e('Tax Rate (%)', 'digiplanet-digital-products'); ?></label></th>
                            <td>
                                <input type="number" id="tax_rate" name="tax_rate" value="<?php echo esc_attr($settings['tax_rate']); ?>" step="0.01" min="0" max="100">
                                <p class="description"><?php _e('Tax rate percentage.', 'digiplanet-digital-products'); ?></p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th><label for="enable_coupons"><?php _e('Enable Coupons', 'digiplanet-digital-products'); ?></label></th>
                            <td>
                                <input type="checkbox" id="enable_coupons" name="enable_coupons" value="1" <?php checked($settings['enable_coupons'], 'yes'); ?>>
                                <p class="description"><?php _e('Enable coupon code functionality.', 'digiplanet-digital-products'); ?></p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th><label for="products_per_page"><?php _e('Products Per Page', 'digiplanet-digital-products'); ?></label></th>
                            <td>
                                <input type="number" id="products_per_page" name="products_per_page" value="<?php echo esc_attr($settings['products_per_page']); ?>" min="1" max="100">
                                <p class="description"><?php _e('Number of products to show per page.', 'digiplanet-digital-products'); ?></p>
                            </td>
                        </tr>
                    </table>
                </div>
                
                <!-- Currency Settings -->
                <div id="currency" class="tab-pane">
                    <table class="form-table">
                        <tr>
                            <th><label for="currency"><?php _e('Currency', 'digiplanet-digital-products'); ?></label></th>
                            <td>
                                <select id="currency" name="currency">
                                    <?php
                                    $currencies = Digiplanet_Product_Manager::get_instance()->get_currencies();
                                    foreach ($currencies as $code => $name): ?>
                                        <option value="<?php echo esc_attr($code); ?>" <?php selected($settings['currency'], $code); ?>>
                                            <?php echo esc_html($name . ' (' . $code . ')'); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                        </tr>
                        
                        <tr>
                            <th><label for="currency_position"><?php _e('Currency Position', 'digiplanet-digital-products'); ?></label></th>
                            <td>
                                <select id="currency_position" name="currency_position">
                                    <option value="left" <?php selected($settings['currency_position'], 'left'); ?>><?php _e('Left ($99.99)', 'digiplanet-digital-products'); ?></option>
                                    <option value="right" <?php selected($settings['currency_position'], 'right'); ?>><?php _e('Right (99.99$)', 'digiplanet-digital-products'); ?></option>
                                    <option value="left_space" <?php selected($settings['currency_position'], 'left_space'); ?>><?php _e('Left with space ($ 99.99)', 'digiplanet-digital-products'); ?></option>
                                    <option value="right_space" <?php selected($settings['currency_position'], 'right_space'); ?>><?php _e('Right with space (99.99 $)', 'digiplanet-digital-products'); ?></option>
                                </select>
                            </td>
                        </tr>
                        
                        <tr>
                            <th><label for="thousand_separator"><?php _e('Thousand Separator', 'digiplanet-digital-products'); ?></label></th>
                            <td>
                                <input type="text" id="thousand_separator" name="thousand_separator" value="<?php echo esc_attr($settings['thousand_separator']); ?>" maxlength="1">
                                <p class="description"><?php _e('Character to separate thousands.', 'digiplanet-digital-products'); ?></p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th><label for="decimal_separator"><?php _e('Decimal Separator', 'digiplanet-digital-products'); ?></label></th>
                            <td>
                                <input type="text" id="decimal_separator" name="decimal_separator" value="<?php echo esc_attr($settings['decimal_separator']); ?>" maxlength="1">
                                <p class="description"><?php _e('Character to separate decimals.', 'digiplanet-digital-products'); ?></p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th><label for="decimals"><?php _e('Number of Decimals', 'digiplanet-digital-products'); ?></label></th>
                            <td>
                                <input type="number" id="decimals" name="decimals" value="<?php echo esc_attr($settings['decimals']); ?>" min="0" max="4">
                                <p class="description"><?php _e('Number of decimal points to show.', 'digiplanet-digital-products'); ?></p>
                            </td>
                        </tr>
                    </table>
                </div>
                
                <!-- Download Settings -->
                <div id="downloads" class="tab-pane">
                    <table class="form-table">
                        <tr>
                            <th><label for="download_limit"><?php _e('Download Limit', 'digiplanet-digital-products'); ?></label></th>
                            <td>
                                <input type="number" id="download_limit" name="download_limit" value="<?php echo esc_attr($settings['download_limit']); ?>" min="0">
                                <p class="description"><?php _e('Maximum number of times a file can be downloaded. Set to 0 for unlimited.', 'digiplanet-digital-products'); ?></p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th><label for="download_expiry"><?php _e('Download Expiry (days)', 'digiplanet-digital-products'); ?></label></th>
                            <td>
                                <input type="number" id="download_expiry" name="download_expiry" value="<?php echo esc_attr($settings['download_expiry']); ?>" min="0">
                                <p class="description"><?php _e('Number of days download links are valid. Set to 0 for never expire.', 'digiplanet-digital-products'); ?></p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th><label for="license_expiry"><?php _e('License Expiry (days)', 'digiplanet-digital-products'); ?></label></th>
                            <td>
                                <input type="number" id="license_expiry" name="license_expiry" value="<?php echo esc_attr($settings['license_expiry']); ?>" min="0">
                                <p class="description"><?php _e('Number of days before licenses expire. Set to 0 for never expire.', 'digiplanet-digital-products'); ?></p>
                            </td>
                        </tr>
                    </table>
                </div>
                
                <!-- Stripe Settings -->
                <div id="stripe" class="tab-pane">
                    <table class="form-table">
                        <tr>
                            <th><label for="stripe_enabled"><?php _e('Enable Stripe', 'digiplanet-digital-products'); ?></label></th>
                            <td>
                                <input type="checkbox" id="stripe_enabled" name="stripe_enabled" value="1" <?php checked($settings['stripe_enabled'], 'yes'); ?>>
                                <p class="description"><?php _e('Enable Stripe payment gateway.', 'digiplanet-digital-products'); ?></p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th><label for="stripe_test_mode"><?php _e('Test Mode', 'digiplanet-digital-products'); ?></label></th>
                            <td>
                                <input type="checkbox" id="stripe_test_mode" name="stripe_test_mode" value="1" <?php checked($settings['stripe_test_mode'], 'yes'); ?>>
                                <p class="description"><?php _e('Enable test mode for Stripe.', 'digiplanet-digital-products'); ?></p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th><label for="stripe_test_publishable_key"><?php _e('Test Publishable Key', 'digiplanet-digital-products'); ?></label></th>
                            <td>
                                <input type="text" id="stripe_test_publishable_key" name="stripe_test_publishable_key" value="<?php echo esc_attr($settings['stripe_test_publishable_key']); ?>" class="regular-text">
                            </td>
                        </tr>
                        
                        <tr>
                            <th><label for="stripe_test_secret_key"><?php _e('Test Secret Key', 'digiplanet-digital-products'); ?></label></th>
                            <td>
                                <input type="password" id="stripe_test_secret_key" name="stripe_test_secret_key" value="<?php echo esc_attr($settings['stripe_test_secret_key']); ?>" class="regular-text">
                            </td>
                        </tr>
                        
                        <tr>
                            <th><label for="stripe_live_publishable_key"><?php _e('Live Publishable Key', 'digiplanet-digital-products'); ?></label></th>
                            <td>
                                <input type="text" id="stripe_live_publishable_key" name="stripe_live_publishable_key" value="<?php echo esc_attr($settings['stripe_live_publishable_key']); ?>" class="regular-text">
                            </td>
                        </tr>
                        
                        <tr>
                            <th><label for="stripe_live_secret_key"><?php _e('Live Secret Key', 'digiplanet-digital-products'); ?></label></th>
                            <td>
                                <input type="password" id="stripe_live_secret_key" name="stripe_live_secret_key" value="<?php echo esc_attr($settings['stripe_live_secret_key']); ?>" class="regular-text">
                            </td>
                        </tr>
                    </table>
                </div>
                
                <!-- Paystack Settings -->
                <div id="paystack" class="tab-pane">
                    <table class="form-table">
                        <tr>
                            <th><label for="paystack_enabled"><?php _e('Enable Paystack', 'digiplanet-digital-products'); ?></label></th>
                            <td>
                                <input type="checkbox" id="paystack_enabled" name="paystack_enabled" value="1" <?php checked($settings['paystack_enabled'], 'yes'); ?>>
                                <p class="description"><?php _e('Enable Paystack payment gateway.', 'digiplanet-digital-products'); ?></p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th><label for="paystack_test_mode"><?php _e('Test Mode', 'digiplanet-digital-products'); ?></label></th>
                            <td>
                                <input type="checkbox" id="paystack_test_mode" name="paystack_test_mode" value="1" <?php checked($settings['paystack_test_mode'], 'yes'); ?>>
                                <p class="description"><?php _e('Enable test mode for Paystack.', 'digiplanet-digital-products'); ?></p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th><label for="paystack_test_public_key"><?php _e('Test Public Key', 'digiplanet-digital-products'); ?></label></th>
                            <td>
                                <input type="text" id="paystack_test_public_key" name="paystack_test_public_key" value="<?php echo esc_attr($settings['paystack_test_public_key']); ?>" class="regular-text">
                            </td>
                        </tr>
                        
                        <tr>
                            <th><label for="paystack_test_secret_key"><?php _e('Test Secret Key', 'digiplanet-digital-products'); ?></label></th>
                            <td>
                                <input type="password" id="paystack_test_secret_key" name="paystack_test_secret_key" value="<?php echo esc_attr($settings['paystack_test_secret_key']); ?>" class="regular-text">
                            </td>
                        </tr>
                        
                        <tr>
                            <th><label for="paystack_live_public_key"><?php _e('Live Public Key', 'digiplanet-digital-products'); ?></label></th>
                            <td>
                                <input type="text" id="paystack_live_public_key" name="paystack_live_public_key" value="<?php echo esc_attr($settings['paystack_live_public_key']); ?>" class="regular-text">
                            </td>
                        </tr>
                        
                        <tr>
                            <th><label for="paystack_live_secret_key"><?php _e('Live Secret Key', 'digiplanet-digital-products'); ?></label></th>
                            <td>
                                <input type="password" id="paystack_live_secret_key" name="paystack_live_secret_key" value="<?php echo esc_attr($settings['paystack_live_secret_key']); ?>" class="regular-text">
                            </td>
                        </tr>
                    </table>
                </div>
                
                <!-- Email Settings -->
                <div id="email" class="tab-pane">
                    <table class="form-table">
                        <tr>
                            <th><label for="email_from_name"><?php _e('From Name', 'digiplanet-digital-products'); ?></label></th>
                            <td>
                                <input type="text" id="email_from_name" name="email_from_name" value="<?php echo esc_attr($settings['email_from_name']); ?>" class="regular-text">
                                <p class="description"><?php _e('Name that appears in the "From" field of emails.', 'digiplanet-digital-products'); ?></p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th><label for="email_from_address"><?php _e('From Email Address', 'digiplanet-digital-products'); ?></label></th>
                            <td>
                                <input type="email" id="email_from_address" name="email_from_address" value="<?php echo esc_attr($settings['email_from_address']); ?>" class="regular-text">
                                <p class="description"><?php _e('Email address that appears in the "From" field of emails.', 'digiplanet-digital-products'); ?></p>
                            </td>
                        </tr>
                    </table>
                </div>
                
                <!-- Page Settings -->
                <div id="pages" class="tab-pane">
                    <table class="form-table">
                        <tr>
                            <th><label for="cart_page_id"><?php _e('Cart Page', 'digiplanet-digital-products'); ?></label></th>
                            <td>
                                <?php
                                wp_dropdown_pages(array(
                                    'name' => 'cart_page_id',
                                    'id' => 'cart_page_id',
                                    'selected' => $settings['cart_page_id'],
                                    'show_option_none' => __('Select a page', 'digiplanet-digital-products'),
                                    'option_none_value' => '',
                                ));
                                ?>
                                <p class="description"><?php _e('Page where the cart will be displayed.', 'digiplanet-digital-products'); ?></p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th><label for="checkout_page_id"><?php _e('Checkout Page', 'digiplanet-digital-products'); ?></label></th>
                            <td>
                                <?php
                                wp_dropdown_pages(array(
                                    'name' => 'checkout_page_id',
                                    'id' => 'checkout_page_id',
                                    'selected' => $settings['checkout_page_id'],
                                    'show_option_none' => __('Select a page', 'digiplanet-digital-products'),
                                    'option_none_value' => '',
                                ));
                                ?>
                                <p class="description"><?php _e('Page where the checkout will be displayed.', 'digiplanet-digital-products'); ?></p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th><label for="account_page_id"><?php _e('Account Page', 'digiplanet-digital-products'); ?></label></th>
                            <td>
                                <?php
                                wp_dropdown_pages(array(
                                    'name' => 'account_page_id',
                                    'id' => 'account_page_id',
                                    'selected' => $settings['account_page_id'],
                                    'show_option_none' => __('Select a page', 'digiplanet-digital-products'),
                                    'option_none_value' => '',
                                ));
                                ?>
                                <p class="description"><?php _e('Page where the customer account area will be displayed.', 'digiplanet-digital-products'); ?></p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th><label for="terms_page_id"><?php _e('Terms & Conditions Page', 'digiplanet-digital-products'); ?></label></th>
                            <td>
                                <?php
                                wp_dropdown_pages(array(
                                    'name' => 'terms_page_id',
                                    'id' => 'terms_page_id',
                                    'selected' => $settings['terms_page_id'],
                                    'show_option_none' => __('Select a page', 'digiplanet-digital-products'),
                                    'option_none_value' => '',
                                ));
                                ?>
                                <p class="description"><?php _e('Page containing your terms and conditions.', 'digiplanet-digital-products'); ?></p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th><label for="privacy_page_id"><?php _e('Privacy Policy Page', 'digiplanet-digital-products'); ?></label></th>
                            <td>
                                <?php
                                wp_dropdown_pages(array(
                                    'name' => 'privacy_page_id',
                                    'id' => 'privacy_page_id',
                                    'selected' => $settings['privacy_page_id'],
                                    'show_option_none' => __('Select a page', 'digiplanet-digital-products'),
                                    'option_none_value' => '',
                                ));
                                ?>
                                <p class="description"><?php _e('Page containing your privacy policy.', 'digiplanet-digital-products'); ?></p>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
        
        <p class="submit">
            <input type="submit" class="button-primary" value="<?php _e('Save Changes', 'digiplanet-digital-products'); ?>">
        </p>
    </form>
</div>

<script>
jQuery(document).ready(function($) {
    // Tab navigation
    $('.nav-tab-wrapper a').on('click', function(e) {
        e.preventDefault();
        var tab = $(this).attr('href');
        
        $('.nav-tab-wrapper a').removeClass('nav-tab-active');
        $(this).addClass('nav-tab-active');
        
        $('.tab-pane').removeClass('active');
        $(tab).addClass('active');
    });
});
</script>