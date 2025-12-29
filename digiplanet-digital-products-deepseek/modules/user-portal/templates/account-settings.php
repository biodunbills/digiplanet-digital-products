<?php
/**
 * Account Settings Template
 */

if (!defined('ABSPATH')) {
    exit;
}

$user_id = get_current_user_id();
$user = get_userdata($user_id);
$account_manager = Digiplanet_Account_Manager::get_instance();

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nonce_action = '';
    
    if (isset($_POST['profile_nonce'])) {
        $nonce_action = 'digiplanet_update_profile';
        $nonce_field = 'profile_nonce';
    } elseif (isset($_POST['password_nonce'])) {
        $nonce_action = 'digiplanet_change_password';
        $nonce_field = 'password_nonce';
    } elseif (isset($_POST['notification_nonce'])) {
        $nonce_action = 'digiplanet_update_notifications';
        $nonce_field = 'notification_nonce';
    } elseif (isset($_POST['billing_nonce'])) {
        $nonce_action = 'digiplanet_update_billing';
        $nonce_field = 'billing_nonce';
    }
    
    if ($nonce_action && wp_verify_nonce($_POST[$nonce_field], $nonce_action)) {
        switch ($nonce_action) {
            case 'digiplanet_update_profile':
                $result = $account_manager->update_profile($user_id, [
                    'first_name' => sanitize_text_field($_POST['first_name']),
                    'last_name' => sanitize_text_field($_POST['last_name']),
                    'email' => sanitize_email($_POST['email']),
                    'display_name' => sanitize_text_field($_POST['display_name']),
                    'phone' => sanitize_text_field($_POST['phone']),
                    'company' => sanitize_text_field($_POST['company']),
                ]);
                
                if ($result['success']) {
                    $profile_message = ['type' => 'success', 'text' => $result['message']];
                    $user = get_userdata($user_id); // Refresh user data
                } else {
                    $profile_message = ['type' => 'error', 'text' => $result['message']];
                }
                break;
                
            case 'digiplanet_change_password':
                $result = $account_manager->change_password(
                    $user_id,
                    $_POST['current_password'],
                    $_POST['new_password']
                );
                
                if ($result['success']) {
                    $password_message = ['type' => 'success', 'text' => $result['message']];
                } else {
                    $password_message = ['type' => 'error', 'text' => $result['message']];
                }
                break;
                
            case 'digiplanet_update_notifications':
                update_user_meta($user_id, 'digiplanet_email_notifications', isset($_POST['email_notifications']) ? 'yes' : 'no');
                update_user_meta($user_id, 'digiplanet_product_updates', isset($_POST['product_updates']) ? 'yes' : 'no');
                update_user_meta($user_id, 'digiplanet_newsletter', isset($_POST['newsletter']) ? 'yes' : 'no');
                update_user_meta($user_id, 'digiplanet_support_updates', isset($_POST['support_updates']) ? 'yes' : 'no');
                
                $notification_message = ['type' => 'success', 'text' => __('Notification preferences updated successfully.', 'digiplanet-digital-products')];
                break;
                
            case 'digiplanet_update_billing':
                update_user_meta($user_id, 'digiplanet_billing_address', [
                    'address_1' => sanitize_text_field($_POST['address_1']),
                    'address_2' => sanitize_text_field($_POST['address_2']),
                    'city' => sanitize_text_field($_POST['city']),
                    'state' => sanitize_text_field($_POST['state']),
                    'postcode' => sanitize_text_field($_POST['postcode']),
                    'country' => sanitize_text_field($_POST['country']),
                ]);
                
                $billing_message = ['type' => 'success', 'text' => __('Billing information updated successfully.', 'digiplanet-digital-products')];
                break;
        }
    }
}

// Get current settings
$email_notifications = get_user_meta($user_id, 'digiplanet_email_notifications', true) !== 'no';
$product_updates = get_user_meta($user_id, 'digiplanet_product_updates', true) !== 'no';
$newsletter = get_user_meta($user_id, 'digiplanet_newsletter', true) !== 'no';
$support_updates = get_user_meta($user_id, 'digiplanet_support_updates', true) !== 'no';

$billing_address = get_user_meta($user_id, 'digiplanet_billing_address', true);
if (!is_array($billing_address)) {
    $billing_address = [
        'address_1' => '',
        'address_2' => '',
        'city' => '',
        'state' => '',
        'postcode' => '',
        'country' => '',
    ];
}
?>

<div class="digiplanet-account-settings">
    <h2><?php _e('Account Settings', 'digiplanet-digital-products'); ?></h2>
    
    <div class="digiplanet-settings-tabs">
        <nav class="digiplanet-settings-nav">
            <a href="#profile" class="digiplanet-settings-tab active">
                <i class="fas fa-user"></i>
                <?php _e('Profile', 'digiplanet-digital-products'); ?>
            </a>
            <a href="#password" class="digiplanet-settings-tab">
                <i class="fas fa-lock"></i>
                <?php _e('Password', 'digiplanet-digital-products'); ?>
            </a>
            <a href="#notifications" class="digiplanet-settings-tab">
                <i class="fas fa-bell"></i>
                <?php _e('Notifications', 'digiplanet-digital-products'); ?>
            </a>
            <a href="#billing" class="digiplanet-settings-tab">
                <i class="fas fa-credit-card"></i>
                <?php _e('Billing', 'digiplanet-digital-products'); ?>
            </a>
            <?php if (in_array('software_client', $user->roles)): ?>
                <a href="#api" class="digiplanet-settings-tab">
                    <i class="fas fa-code"></i>
                    <?php _e('API Access', 'digiplanet-digital-products'); ?>
                </a>
            <?php endif; ?>
        </nav>
        
        <div class="digiplanet-settings-content">
            <!-- Profile Tab -->
            <div id="profile" class="digiplanet-settings-pane active">
                <h3><?php _e('Profile Information', 'digiplanet-digital-products'); ?></h3>
                
                <?php if (isset($profile_message)): ?>
                    <div class="digiplanet-alert digiplanet-alert-<?php echo $profile_message['type']; ?>">
                        <?php echo esc_html($profile_message['text']); ?>
                    </div>
                <?php endif; ?>
                
                <form method="post" class="digiplanet-settings-form">
                    <?php wp_nonce_field('digiplanet_update_profile', 'profile_nonce'); ?>
                    
                    <div class="digiplanet-form-row">
                        <div class="digiplanet-form-group">
                            <label for="first_name"><?php _e('First Name', 'digiplanet-digital-products'); ?></label>
                            <input type="text" id="first_name" name="first_name" value="<?php echo esc_attr($user->first_name); ?>" required>
                        </div>
                        
                        <div class="digiplanet-form-group">
                            <label for="last_name"><?php _e('Last Name', 'digiplanet-digital-products'); ?></label>
                            <input type="text" id="last_name" name="last_name" value="<?php echo esc_attr($user->last_name); ?>">
                        </div>
                    </div>
                    
                    <div class="digiplanet-form-group">
                        <label for="display_name"><?php _e('Display Name', 'digiplanet-digital-products'); ?></label>
                        <input type="text" id="display_name" name="display_name" value="<?php echo esc_attr($user->display_name); ?>" required>
                        <p class="digiplanet-form-help"><?php _e('This will be displayed on your profile and reviews.', 'digiplanet-digital-products'); ?></p>
                    </div>
                    
                    <div class="digiplanet-form-group">
                        <label for="email"><?php _e('Email Address', 'digiplanet-digital-products'); ?></label>
                        <input type="email" id="email" name="email" value="<?php echo esc_attr($user->user_email); ?>" required>
                        <p class="digiplanet-form-help"><?php _e('Your email address is used for account notifications and password recovery.', 'digiplanet-digital-products'); ?></p>
                    </div>
                    
                    <div class="digiplanet-form-row">
                        <div class="digiplanet-form-group">
                            <label for="phone"><?php _e('Phone Number', 'digiplanet-digital-products'); ?></label>
                            <input type="tel" id="phone" name="phone" value="<?php echo esc_attr(get_user_meta($user_id, 'digiplanet_phone', true)); ?>">
                        </div>
                        
                        <div class="digiplanet-form-group">
                            <label for="company"><?php _e('Company', 'digiplanet-digital-products'); ?></label>
                            <input type="text" id="company" name="company" value="<?php echo esc_attr(get_user_meta($user_id, 'digiplanet_company', true)); ?>">
                        </div>
                    </div>
                    
                    <div class="digiplanet-form-actions">
                        <button type="submit" class="digiplanet-btn digiplanet-btn-primary">
                            <i class="fas fa-save"></i>
                            <?php _e('Save Changes', 'digiplanet-digital-products'); ?>
                        </button>
                    </div>
                </form>
            </div>
            
            <!-- Password Tab -->
            <div id="password" class="digiplanet-settings-pane">
                <h3><?php _e('Change Password', 'digiplanet-digital-products'); ?></h3>
                
                <?php if (isset($password_message)): ?>
                    <div class="digiplanet-alert digiplanet-alert-<?php echo $password_message['type']; ?>">
                        <?php echo esc_html($password_message['text']); ?>
                    </div>
                <?php endif; ?>
                
                <form method="post" class="digiplanet-settings-form">
                    <?php wp_nonce_field('digiplanet_change_password', 'password_nonce'); ?>
                    
                    <div class="digiplanet-form-group">
                        <label for="current_password"><?php _e('Current Password', 'digiplanet-digital-products'); ?></label>
                        <input type="password" id="current_password" name="current_password" required>
                    </div>
                    
                    <div class="digiplanet-form-group">
                        <label for="new_password"><?php _e('New Password', 'digiplanet-digital-products'); ?></label>
                        <input type="password" id="new_password" name="new_password" required minlength="8">
                        <div class="digiplanet-password-strength">
                            <div class="digiplanet-password-strength-meter">
                                <div class="digiplanet-password-strength-fill"></div>
                            </div>
                            <span class="digiplanet-password-strength-text"></span>
                        </div>
                        <p class="digiplanet-form-help"><?php _e('Password must be at least 8 characters long.', 'digiplanet-digital-products'); ?></p>
                    </div>
                    
                    <div class="digiplanet-form-group">
                        <label for="confirm_password"><?php _e('Confirm New Password', 'digiplanet-digital-products'); ?></label>
                        <input type="password" id="confirm_password" name="confirm_password" required>
                        <div class="digiplanet-password-match"></div>
                    </div>
                    
                    <div class="digiplanet-form-actions">
                        <button type="submit" class="digiplanet-btn digiplanet-btn-primary">
                            <i class="fas fa-key"></i>
                            <?php _e('Change Password', 'digiplanet-digital-products'); ?>
                        </button>
                    </div>
                </form>
            </div>
            
            <!-- Notifications Tab -->
            <div id="notifications" class="digiplanet-settings-pane">
                <h3><?php _e('Notification Preferences', 'digiplanet-digital-products'); ?></h3>
                
                <?php if (isset($notification_message)): ?>
                    <div class="digiplanet-alert digiplanet-alert-<?php echo $notification_message['type']; ?>">
                        <?php echo esc_html($notification_message['text']); ?>
                    </div>
                <?php endif; ?>
                
                <form method="post" class="digiplanet-settings-form">
                    <?php wp_nonce_field('digiplanet_update_notifications', 'notification_nonce'); ?>
                    
                    <div class="digiplanet-notification-settings">
                        <div class="digiplanet-notification-item">
                            <label class="digiplanet-notification-toggle">
                                <input type="checkbox" name="email_notifications" <?php checked($email_notifications); ?>>
                                <span class="digiplanet-notification-slider"></span>
                            </label>
                            <div class="digiplanet-notification-content">
                                <h4><?php _e('Email Notifications', 'digiplanet-digital-products'); ?></h4>
                                <p><?php _e('Receive important account notifications via email.', 'digiplanet-digital-products'); ?></p>
                            </div>
                        </div>
                        
                        <div class="digiplanet-notification-item">
                            <label class="digiplanet-notification-toggle">
                                <input type="checkbox" name="product_updates" <?php checked($product_updates); ?>>
                                <span class="digiplanet-notification-slider"></span>
                            </label>
                            <div class="digiplanet-notification-content">
                                <h4><?php _e('Product Updates', 'digiplanet-digital-products'); ?></h4>
                                <p><?php _e('Get notified about updates to your purchased products.', 'digiplanet-digital-products'); ?></p>
                            </div>
                        </div>
                        
                        <div class="digiplanet-notification-item">
                            <label class="digiplanet-notification-toggle">
                                <input type="checkbox" name="newsletter" <?php checked($newsletter); ?>>
                                <span class="digiplanet-notification-slider"></span>
                            </label>
                            <div class="digiplanet-notification-content">
                                <h4><?php _e('Newsletter', 'digiplanet-digital-products'); ?></h4>
                                <p><?php _e('Subscribe to our newsletter for tips, tutorials, and new product announcements.', 'digiplanet-digital-products'); ?></p>
                            </div>
                        </div>
                        
                        <?php if (in_array('software_client', $user->roles)): ?>
                            <div class="digiplanet-notification-item">
                                <label class="digiplanet-notification-toggle">
                                    <input type="checkbox" name="support_updates" <?php checked($support_updates); ?>>
                                    <span class="digiplanet-notification-slider"></span>
                                </label>
                                <div class="digiplanet-notification-content">
                                    <h4><?php _e('Support Updates', 'digiplanet-digital-products'); ?></h4>
                                    <p><?php _e('Receive updates on your support tickets.', 'digiplanet-digital-products'); ?></p>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="digiplanet-form-actions">
                        <button type="submit" class="digiplanet-btn digiplanet-btn-primary">
                            <i class="fas fa-save"></i>
                            <?php _e('Save Preferences', 'digiplanet-digital-products'); ?>
                        </button>
                    </div>
                </form>
            </div>
            
            <!-- Billing Tab -->
            <div id="billing" class="digiplanet-settings-pane">
                <h3><?php _e('Billing Information', 'digiplanet-digital-products'); ?></h3>
                
                <?php if (isset($billing_message)): ?>
                    <div class="digiplanet-alert digiplanet-alert-<?php echo $billing_message['type']; ?>">
                        <?php echo esc_html($billing_message['text']); ?>
                    </div>
                <?php endif; ?>
                
                <form method="post" class="digiplanet-settings-form">
                    <?php wp_nonce_field('digiplanet_update_billing', 'billing_nonce'); ?>
                    
                    <div class="digiplanet-form-group">
                        <label for="address_1"><?php _e('Street Address', 'digiplanet-digital-products'); ?></label>
                        <input type="text" id="address_1" name="address_1" value="<?php echo esc_attr($billing_address['address_1']); ?>">
                    </div>
                    
                    <div class="digiplanet-form-group">
                        <label for="address_2"><?php _e('Apartment, Suite, etc.', 'digiplanet-digital-products'); ?></label>
                        <input type="text" id="address_2" name="address_2" value="<?php echo esc_attr($billing_address['address_2']); ?>">
                    </div>
                    
                    <div class="digiplanet-form-row">
                        <div class="digiplanet-form-group">
                            <label for="city"><?php _e('City', 'digiplanet-digital-products'); ?></label>
                            <input type="text" id="city" name="city" value="<?php echo esc_attr($billing_address['city']); ?>">
                        </div>
                        
                        <div class="digiplanet-form-group">
                            <label for="state"><?php _e('State / Province', 'digiplanet-digital-products'); ?></label>
                            <input type="text" id="state" name="state" value="<?php echo esc_attr($billing_address['state']); ?>">
                        </div>
                    </div>
                    
                    <div class="digiplanet-form-row">
                        <div class="digiplanet-form-group">
                            <label for="postcode"><?php _e('Postal Code', 'digiplanet-digital-products'); ?></label>
                            <input type="text" id="postcode" name="postcode" value="<?php echo esc_attr($billing_address['postcode']); ?>">
                        </div>
                        
                        <div class="digiplanet-form-group">
                            <label for="country"><?php _e('Country', 'digiplanet-digital-products'); ?></label>
                            <select id="country" name="country">
                                <?php
                                $countries = [
                                    'US' => 'United States',
                                    'CA' => 'Canada',
                                    'GB' => 'United Kingdom',
                                    'AU' => 'Australia',
                                    'DE' => 'Germany',
                                    'FR' => 'France',
                                    'ES' => 'Spain',
                                    'IT' => 'Italy',
                                    'JP' => 'Japan',
                                    'IN' => 'India',
                                    'BR' => 'Brazil',
                                    'MX' => 'Mexico',
                                ];
                                
                                foreach ($countries as $code => $name): ?>
                                    <option value="<?php echo esc_attr($code); ?>" <?php selected($billing_address['country'], $code); ?>>
                                        <?php echo esc_html($name); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="digiplanet-form-actions">
                        <button type="submit" class="digiplanet-btn digiplanet-btn-primary">
                            <i class="fas fa-save"></i>
                            <?php _e('Save Billing Information', 'digiplanet-digital-products'); ?>
                        </button>
                    </div>
                </form>
            </div>
            
            <!-- API Access Tab (Software Clients Only) -->
            <?php if (in_array('software_client', $user->roles)): ?>
                <div id="api" class="digiplanet-settings-pane">
                    <h3><?php _e('API Access', 'digiplanet-digital-products'); ?></h3>
                    <p><?php _e('Generate API keys to integrate with our services programmatically.', 'digiplanet-digital-products'); ?></p>
                    
                    <?php
                    $api_keys = get_user_meta($user_id, 'digiplanet_api_keys', true);
                    if (!is_array($api_keys)) {
                        $api_keys = [];
                    }
                    ?>
                    
                    <div class="digiplanet-api-keys">
                        <?php if (!empty($api_keys)): ?>
                            <div class="digiplanet-api-keys-list">
                                <?php foreach ($api_keys as $key => $api_key): ?>
                                    <div class="digiplanet-api-key-item">
                                        <div class="digiplanet-api-key-info">
                                            <h4><?php echo esc_html($api_key['name']); ?></h4>
                                            <div class="digiplanet-api-key-meta">
                                                <span class="digiplanet-api-key-created">
                                                    <?php printf(__('Created: %s', 'digiplanet-digital-products'), date_i18n(get_option('date_format'), $api_key['created'])); ?>
                                                </span>
                                                <span class="digiplanet-api-key-last-used">
                                                    <?php 
                                                    if ($api_key['last_used']) {
                                                        printf(__('Last used: %s', 'digiplanet-digital-products'), human_time_diff($api_key['last_used'], current_time('timestamp')));
                                                    } else {
                                                        _e('Never used', 'digiplanet-digital-products');
                                                    }
                                                    ?>
                                                </span>
                                            </div>
                                        </div>
                                        
                                        <div class="digiplanet-api-key-actions">
                                            <button type="button" class="digiplanet-btn digiplanet-btn-sm" onclick="showApiKey('<?php echo esc_attr($api_key['key']); ?>')">
                                                <i class="fas fa-eye"></i>
                                                <?php _e('View Key', 'digiplanet-digital-products'); ?>
                                            </button>
                                            <button type="button" class="digiplanet-btn digiplanet-btn-sm digiplanet-btn-secondary" onclick="regenerateApiKey('<?php echo esc_attr($key); ?>')">
                                                <i class="fas fa-redo"></i>
                                                <?php _e('Regenerate', 'digiplanet-digital-products'); ?>
                                            </button>
                                            <button type="button" class="digiplanet-btn digiplanet-btn-sm digiplanet-btn-danger" onclick="deleteApiKey('<?php echo esc_attr($key); ?>')">
                                                <i class="fas fa-trash"></i>
                                                <?php _e('Delete', 'digiplanet-digital-products'); ?>
                                            </button>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="digiplanet-no-api-keys">
                                <i class="fas fa-key"></i>
                                <p><?php _e('No API keys generated yet.', 'digiplanet-digital-products'); ?></p>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="digiplanet-generate-api-key">
                        <h4><?php _e('Generate New API Key', 'digiplanet-digital-products'); ?></h4>
                        <form id="generate-api-key-form" method="post">
                            <?php wp_nonce_field('digiplanet_generate_api_key', 'api_key_nonce'); ?>
                            
                            <div class="digiplanet-form-group">
                                <label for="api_key_name"><?php _e('Key Name', 'digiplanet-digital-products'); ?></label>
                                <input type="text" id="api_key_name" name="api_key_name" placeholder="<?php _e('e.g., Production Server', 'digiplanet-digital-products'); ?>" required>
                                <p class="digiplanet-form-help"><?php _e('Give this key a descriptive name to identify its purpose.', 'digiplanet-digital-products'); ?></p>
                            </div>
                            
                            <div class="digiplanet-form-group">
                                <label for="api_key_permissions"><?php _e('Permissions', 'digiplanet-digital-products'); ?></label>
                                <select id="api_key_permissions" name="api_key_permissions">
                                    <option value="read"><?php _e('Read Only', 'digiplanet-digital-products'); ?></option>
                                    <option value="write"><?php _e('Read & Write', 'digiplanet-digital-products'); ?></option>
                                    <option value="admin"><?php _e('Administrative', 'digiplanet-digital-products'); ?></option>
                                </select>
                            </div>
                            
                            <div class="digiplanet-form-actions">
                                <button type="submit" class="digiplanet-btn digiplanet-btn-primary">
                                    <i class="fas fa-plus"></i>
                                    <?php _e('Generate API Key', 'digiplanet-digital-products'); ?>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- API Key Modal -->
<div id="api-key-modal" class="digiplanet-modal" style="display: none;">
    <div class="digiplanet-modal-content">
        <div class="digiplanet-modal-header">
            <h3><?php _e('API Key', 'digiplanet-digital-products'); ?></h3>
            <button type="button" class="digiplanet-modal-close" onclick="closeModal('api-key-modal')">&times;</button>
        </div>
        <div class="digiplanet-modal-body">
            <div class="digiplanet-api-key-display">
                <p><?php _e('Your new API key has been generated. Copy it now as it will not be shown again:', 'digiplanet-digital-products'); ?></p>
                <code class="digiplanet-api-key-value" id="api-key-value"></code>
                <div class="digiplanet-api-key-warning">
                    <i class="fas fa-exclamation-triangle"></i>
                    <?php _e('Store this key securely. You will not be able to view it again.', 'digiplanet-digital-products'); ?>
                </div>
            </div>
            <div class="digiplanet-modal-footer">
                <button type="button" class="digiplanet-btn digiplanet-btn-primary" onclick="copyApiKey()">
                    <i class="fas fa-copy"></i>
                    <?php _e('Copy to Clipboard', 'digiplanet-digital-products'); ?>
                </button>
                <button type="button" class="digiplanet-btn digiplanet-btn-secondary" onclick="closeModal('api-key-modal')">
                    <?php _e('Close', 'digiplanet-digital-products'); ?>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // Tab navigation
    $('.digiplanet-settings-tab').on('click', function(e) {
        e.preventDefault();
        
        var target = $(this).attr('href');
        
        $('.digiplanet-settings-tab').removeClass('active');
        $(this).addClass('active');
        
        $('.digiplanet-settings-pane').removeClass('active');
        $(target).addClass('active');
    });
    
    // Password strength meter
    $('#new_password').on('keyup', function() {
        var password = $(this).val();
        var strength = 0;
        var meter = $('.digiplanet-password-strength-fill');
        var text = $('.digiplanet-password-strength-text');
        
        // Length check
        if (password.length >= 8) strength++;
        if (password.length >= 12) strength++;
        
        // Complexity checks
        if (password.match(/[a-z]+/)) strength++;
        if (password.match(/[A-Z]+/)) strength++;
        if (password.match(/[0-9]+/)) strength++;
        if (password.match(/[$@#&!]+/)) strength++;
        
        // Update meter
        var percent = (strength / 6) * 100;
        meter.css('width', percent + '%');
        
        // Update text
        if (password.length === 0) {
            text.text('');
            meter.css('background-color', '');
        } else if (strength <= 2) {
            text.text('Weak').css('color', '#dc3545');
            meter.css('background-color', '#dc3545');
        } else if (strength <= 4) {
            text.text('Good').css('color', '#ffc107');
            meter.css('background-color', '#ffc107');
        } else {
            text.text('Strong').css('color', '#28a745');
            meter.css('background-color', '#28a745');
        }
    });
    
    // Password match check
    $('#confirm_password').on('keyup', function() {
        var password = $('#new_password').val();
        var confirm = $(this).val();
        var matchDiv = $('.digiplanet-password-match');
        
        if (confirm.length === 0) {
            matchDiv.html('');
        } else if (password === confirm) {
            matchDiv.html('<span style="color:#28a745"><i class="fas fa-check"></i> Passwords match</span>');
        } else {
            matchDiv.html('<span style="color:#dc3545"><i class="fas fa-times"></i> Passwords do not match</span>');
        }
    });
    
    // Generate API key form
    $('#generate-api-key-form').on('submit', function(e) {
        e.preventDefault();
        
        var formData = $(this).serialize();
        
        $.ajax({
            url: digiplanet_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'digiplanet_generate_api_key',
                data: formData,
                nonce: digiplanet_ajax.nonce
            },
            beforeSend: function() {
                $('#generate-api-key-form button[type="submit"]').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Generating...');
            },
            success: function(response) {
                if (response.success) {
                    $('#api-key-value').text(response.data.api_key);
                    $('#api-key-modal').show();
                    
                    // Reset form
                    $('#generate-api-key-form')[0].reset();
                } else {
                    showNotification(response.data.message, 'error');
                }
            },
            complete: function() {
                $('#generate-api-key-form button[type="submit"]').prop('disabled', false).html('<i class="fas fa-plus"></i> Generate API Key');
            }
        });
    });
});

function showApiKey(key) {
    $('#api-key-value').text(key);
    $('#api-key-modal').show();
}

function copyApiKey() {
    var key = $('#api-key-value').text();
    
    var $temp = $('<input>');
    $('body').append($temp);
    $temp.val(key).select();
    
    try {
        document.execCommand('copy');
        showNotification('API key copied to clipboard!', 'success');
    } catch (err) {
        showNotification('Failed to copy API key.', 'error');
    }
    
    $temp.remove();
}

function regenerateApiKey(keyId) {
    if (!confirm('Are you sure you want to regenerate this API key? The old key will no longer work.')) {
        return;
    }
    
    $.ajax({
        url: digiplanet_ajax.ajax_url,
        type: 'POST',
        data: {
            action: 'digiplanet_regenerate_api_key',
            key_id: keyId,
            nonce: digiplanet_ajax.nonce
        },
        success: function(response) {
            if (response.success) {
                showNotification(response.data.message, 'success');
                setTimeout(function() {
                    location.reload();
                }, 2000);
            } else {
                showNotification(response.data.message, 'error');
            }
        }
    });
}

function deleteApiKey(keyId) {
    if (!confirm('Are you sure you want to delete this API key? This action cannot be undone.')) {
        return;
    }
    
    $.ajax({
        url: digiplanet_ajax.ajax_url,
        type: 'POST',
        data: {
            action: 'digiplanet_delete_api_key',
            key_id: keyId,
            nonce: digiplanet_ajax.nonce
        },
        success: function(response) {
            if (response.success) {
                showNotification(response.data.message, 'success');
                setTimeout(function() {
                    location.reload();
                }, 2000);
            } else {
                showNotification(response.data.message, 'error');
            }
        }
    });
}

function closeModal(modalId) {
    $('#' + modalId).hide();
}

function showNotification(message, type) {
    var $notification = $('<div class="digiplanet-notification digiplanet-notification-' + type + '">' + message + '</div>');
    
    $('body').append($notification);
    
    setTimeout(function() {
        $notification.addClass('show');
    }, 10);
    
    setTimeout(function() {
        $notification.removeClass('show');
        setTimeout(function() {
            $notification.remove();
        }, 300);
    }, 3000);
}
</script>

<style>
.digiplanet-account-settings {
    padding: 30px;
}

.digiplanet-settings-nav {
    display: flex;
    border-bottom: 2px solid #e9ecef;
    margin-bottom: 30px;
    flex-wrap: wrap;
}

.digiplanet-settings-tab {
    padding: 15px 25px;
    text-decoration: none;
    color: #6c757d;
    border-bottom: 2px solid transparent;
    margin-bottom: -2px;
    display: flex;
    align-items: center;
    gap: 10px;
    transition: all 0.3s ease;
}

.digiplanet-settings-tab:hover {
    color: #3742fa;
}

.digiplanet-settings-tab.active {
    color: #3742fa;
    border-bottom-color: #3742fa;
    font-weight: 600;
}

.digiplanet-settings-pane {
    display: none;
}

.digiplanet-settings-pane.active {
    display: block;
}

.digiplanet-settings-form {
    max-width: 600px;
}

.digiplanet-form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
}

.digiplanet-form-group {
    margin-bottom: 20px;
}

.digiplanet-form-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: 600;
    color: #333;
}

.digiplanet-form-group input,
.digiplanet-form-group select,
.digiplanet-form-group textarea {
    width: 100%;
    padding: 12px 15px;
    border: 1px solid #ced4da;
    border-radius: 6px;
    font-size: 16px;
    transition: border-color 0.3s ease;
}

.digiplanet-form-group input:focus,
.digiplanet-form-group select:focus,
.digiplanet-form-group textarea:focus {
    outline: none;
    border-color: #3742fa;
    box-shadow: 0 0 0 0.2rem rgba(55, 66, 250, 0.25);
}

.digiplanet-form-help {
    margin-top: 5px;
    font-size: 14px;
    color: #6c757d;
}

.digiplanet-form-actions {
    margin-top: 30px;
    padding-top: 20px;
    border-top: 1px solid #e9ecef;
}

.digiplanet-password-strength {
    margin-top: 10px;
}

.digiplanet-password-strength-meter {
    height: 5px;
    background: #e9ecef;
    border-radius: 3px;
    overflow: hidden;
    margin-bottom: 5px;
}

.digiplanet-password-strength-fill {
    height: 100%;
    width: 0;
    transition: width 0.3s ease, background-color 0.3s ease;
}

.digiplanet-notification-settings {
    margin: 20px 0;
}

.digiplanet-notification-item {
    display: flex;
    align-items: flex-start;
    padding: 20px;
    border: 1px solid #e9ecef;
    border-radius: 8px;
    margin-bottom: 15px;
    transition: border-color 0.3s ease;
}

.digiplanet-notification-item:hover {
    border-color: #3742fa;
}

.digiplanet-notification-toggle {
    position: relative;
    display: inline-block;
    width: 50px;
    height: 24px;
    margin-right: 20px;
    flex-shrink: 0;
}

.digiplanet-notification-toggle input {
    opacity: 0;
    width: 0;
    height: 0;
}

.digiplanet-notification-slider {
    position: absolute;
    cursor: pointer;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: #ccc;
    transition: .4s;
    border-radius: 24px;
}

.digiplanet-notification-slider:before {
    position: absolute;
    content: "";
    height: 16px;
    width: 16px;
    left: 4px;
    bottom: 4px;
    background-color: white;
    transition: .4s;
    border-radius: 50%;
}

.digiplanet-notification-toggle input:checked + .digiplanet-notification-slider {
    background-color: #3742fa;
}

.digiplanet-notification-toggle input:checked + .digiplanet-notification-slider:before {
    transform: translateX(26px);
}

.digiplanet-notification-content h4 {
    margin: 0 0 5px 0;
    color: #333;
}

.digiplanet-notification-content p {
    margin: 0;
    color: #6c757d;
    font-size: 14px;
}

.digiplanet-api-keys {
    margin: 30px 0;
}

.digiplanet-api-key-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px;
    border: 1px solid #e9ecef;
    border-radius: 8px;
    margin-bottom: 15px;
}

.digiplanet-api-key-info h4 {
    margin: 0 0 10px 0;
    color: #333;
}

.digiplanet-api-key-meta {
    display: flex;
    gap: 20px;
    font-size: 14px;
    color: #6c757d;
}

.digiplanet-api-key-actions {
    display: flex;
    gap: 10px;
}

.digiplanet-no-api-keys {
    text-align: center;
    padding: 40px;
    color: #6c757d;
}

.digiplanet-no-api-keys i {
    font-size: 48px;
    margin-bottom: 20px;
    color: #adb5bd;
}

.digiplanet-generate-api-key {
    margin-top: 40px;
    padding: 30px;
    background: #f8f9fa;
    border-radius: 8px;
}

.digiplanet-api-key-display {
    text-align: center;
}

.digiplanet-api-key-value {
    display: block;
    background: #f8f9fa;
    padding: 15px;
    border-radius: 6px;
    font-family: 'Courier New', monospace;
    font-size: 14px;
    margin: 20px 0;
    word-break: break-all;
}

.digiplanet-api-key-warning {
    background: #fff3cd;
    border: 1px solid #ffc107;
    color: #856404;
    padding: 15px;
    border-radius: 6px;
    display: flex;
    align-items: center;
    gap: 10px;
}

@media (max-width: 768px) {
    .digiplanet-form-row {
        grid-template-columns: 1fr;
    }
    
    .digiplanet-api-key-item {
        flex-direction: column;
        align-items: flex-start;
        gap: 20px;
    }
    
    .digiplanet-api-key-actions {
        width: 100%;
        justify-content: flex-start;
    }
}
</style>