<?php
/**
 * Registration Template
 */

if (!defined('ABSPATH')) {
    exit;
}

// Redirect if already logged in
if (is_user_logged_in()) {
    wp_redirect(Digiplanet_User_Portal::get_instance()->get_account_url());
    exit;
}

// Check if registration is allowed
if (!get_option('users_can_register')) {
    wp_die(__('Registration is currently disabled.', 'digiplanet-digital-products'));
}

$errors = [];
$success = false;
$form_data = [
    'username' => '',
    'email' => '',
    'first_name' => '',
    'last_name' => '',
];

// Handle registration form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['digiplanet_register_nonce'])) {
    if (wp_verify_nonce($_POST['digiplanet_register_nonce'], 'digiplanet_register')) {
        $form_data = [
            'username' => sanitize_user($_POST['username']),
            'email' => sanitize_email($_POST['email']),
            'first_name' => sanitize_text_field($_POST['first_name']),
            'last_name' => sanitize_text_field($_POST['last_name']),
            'password' => $_POST['password'],
            'confirm_password' => $_POST['confirm_password'],
            'terms' => isset($_POST['terms']),
            'newsletter' => isset($_POST['newsletter']),
        ];
        
        // Validate form data
        $errors = $this->validate_registration($form_data);
        
        if (empty($errors)) {
            $account_manager = Digiplanet_Account_Manager::get_instance();
            $result = $account_manager->create_digital_customer(
                $form_data['email'],
                $form_data['password'],
                [
                    'first_name' => $form_data['first_name'],
                    'last_name' => $form_data['last_name'],
                    'user_login' => $form_data['username'],
                ]
            );
            
            if ($result['success']) {
                $success = true;
                
                // Subscribe to newsletter if selected
                if ($form_data['newsletter']) {
                    $this->subscribe_to_newsletter($form_data['email'], $form_data['first_name'], $form_data['last_name']);
                }
                
                // Auto-login the user
                $credentials = [
                    'user_login' => $form_data['username'],
                    'user_password' => $form_data['password'],
                    'remember' => true,
                ];
                
                $user = wp_signon($credentials, is_ssl());
                
                if (!is_wp_error($user)) {
                    // Redirect to account page after a short delay
                    echo '<script>
                        setTimeout(function() {
                            window.location.href = "' . Digiplanet_User_Portal::get_instance()->get_account_url() . '";
                        }, 3000);
                    </script>';
                }
            } else {
                $errors[] = $result['message'];
            }
        }
    }
}
?>

<div class="digiplanet-register-page">
    <div class="digiplanet-register-container">
        <!-- Left Side - Benefits -->
        <div class="digiplanet-register-left">
            <div class="digiplanet-register-brand">
                <a href="<?php echo home_url('/'); ?>" class="digiplanet-register-logo">
                    <?php
                    $logo_url = get_theme_mod('custom_logo') ? wp_get_attachment_image_url(get_theme_mod('custom_logo'), 'full') : DIGIPLANET_ASSETS_URL . 'images/logo.png';
                    ?>
                    <img src="<?php echo esc_url($logo_url); ?>" alt="<?php echo get_bloginfo('name'); ?>">
                </a>
                <h1><?php _e('Join Digiplanet', 'digiplanet-digital-products'); ?></h1>
                <p><?php _e('Create your account to access premium digital products and exclusive features.', 'digiplanet-digital-products'); ?></p>
            </div>
            
            <div class="digiplanet-benefits">
                <h3><?php _e('Benefits of Joining', 'digiplanet-digital-products'); ?></h3>
                
                <div class="digiplanet-benefit">
                    <i class="fas fa-shopping-cart"></i>
                    <div>
                        <h4><?php _e('Easy Purchases', 'digiplanet-digital-products'); ?></h4>
                        <p><?php _e('Buy digital products with one-click checkout', 'digiplanet-digital-products'); ?></p>
                    </div>
                </div>
                
                <div class="digiplanet-benefit">
                    <i class="fas fa-download"></i>
                    <div>
                        <h4><?php _e('Instant Access', 'digiplanet-digital-products'); ?></h4>
                        <p><?php _e('Download purchased products immediately after payment', 'digiplanet-digital-products'); ?></p>
                    </div>
                </div>
                
                <div class="digiplanet-benefit">
                    <i class="fas fa-sync-alt"></i>
                    <div>
                        <h4><?php _e('Free Updates', 'digiplanet-digital-products'); ?></h4>
                        <p><?php _e('Get lifetime updates for all your purchased products', 'digiplanet-digital-products'); ?></p>
                    </div>
                </div>
                
                <div class="digiplanet-benefit">
                    <i class="fas fa-headset"></i>
                    <div>
                        <h4><?php _e('Priority Support', 'digiplanet-digital-products'); ?></h4>
                        <p><?php _e('Access dedicated customer support for all products', 'digiplanet-digital-products'); ?></p>
                    </div>
                </div>
                
                <div class="digiplanet-benefit">
                    <i class="fas fa-gift"></i>
                    <div>
                        <h4><?php _e('Exclusive Offers', 'digiplanet-digital-products'); ?></h4>
                        <p><?php _e('Receive special discounts and early access to new products', 'digiplanet-digital-products'); ?></p>
                    </div>
                </div>
            </div>
            
            <div class="digiplanet-testimonials">
                <h3><?php _e('What Our Customers Say', 'digiplanet-digital-products'); ?></h3>
                
                <div class="digiplanet-testimonial">
                    <div class="digiplanet-testimonial-content">
                        <p>"<?php _e('The scripts are well-coded and the support is exceptional. Highly recommended!', 'digiplanet-digital-products'); ?>"</p>
                    </div>
                    <div class="digiplanet-testimonial-author">
                        <strong>Sarah Johnson</strong>
                        <span><?php _e('Web Developer', 'digiplanet-digital-products'); ?></span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Right Side - Registration Form -->
        <div class="digiplanet-register-right">
            <div class="digiplanet-register-form-container">
                <div class="digiplanet-register-form-header">
                    <h2><?php _e('Create Your Account', 'digiplanet-digital-products'); ?></h2>
                    <p><?php _e('Fill in your details to get started', 'digiplanet-digital-products'); ?></p>
                </div>
                
                <?php if ($success): ?>
                    <div class="digiplanet-alert digiplanet-alert-success">
                        <h4><?php _e('Account Created Successfully!', 'digiplanet-digital-products'); ?></h4>
                        <p><?php _e('Welcome to Digiplanet! Your account has been created and you are now logged in.', 'digiplanet-digital-products'); ?></p>
                        <p><?php _e('Redirecting to your account dashboard...', 'digiplanet-digital-products'); ?></p>
                    </div>
                <?php else: ?>
                    <?php if (!empty($errors)): ?>
                        <div class="digiplanet-alert digiplanet-alert-danger">
                            <?php foreach ($errors as $error): ?>
                                <p><?php echo esc_html($error); ?></p>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    
                    <form method="post" class="digiplanet-register-form" id="digiplanet-register-form">
                        <?php wp_nonce_field('digiplanet_register', 'digiplanet_register_nonce'); ?>
                        
                        <div class="digiplanet-form-row">
                            <div class="digiplanet-form-group">
                                <label for="first_name"><?php _e('First Name', 'digiplanet-digital-products'); ?> *</label>
                                <div class="digiplanet-input-with-icon">
                                    <i class="fas fa-user"></i>
                                    <input type="text" id="first_name" name="first_name" value="<?php echo esc_attr($form_data['first_name']); ?>" required>
                                </div>
                            </div>
                            
                            <div class="digiplanet-form-group">
                                <label for="last_name"><?php _e('Last Name', 'digiplanet-digital-products'); ?></label>
                                <div class="digiplanet-input-with-icon">
                                    <i class="fas fa-user"></i>
                                    <input type="text" id="last_name" name="last_name" value="<?php echo esc_attr($form_data['last_name']); ?>">
                                </div>
                            </div>
                        </div>
                        
                        <div class="digiplanet-form-group">
                            <label for="username"><?php _e('Username', 'digiplanet-digital-products'); ?> *</label>
                            <div class="digiplanet-input-with-icon">
                                <i class="fas fa-at"></i>
                                <input type="text" id="username" name="username" value="<?php echo esc_attr($form_data['username']); ?>" required>
                            </div>
                            <div class="digiplanet-username-availability"></div>
                        </div>
                        
                        <div class="digiplanet-form-group">
                            <label for="email"><?php _e('Email Address', 'digiplanet-digital-products'); ?> *</label>
                            <div class="digiplanet-input-with-icon">
                                <i class="fas fa-envelope"></i>
                                <input type="email" id="email" name="email" value="<?php echo esc_attr($form_data['email']); ?>" required>
                            </div>
                        </div>
                        
                        <div class="digiplanet-form-row">
                            <div class="digiplanet-form-group">
                                <label for="password"><?php _e('Password', 'digiplanet-digital-products'); ?> *</label>
                                <div class="digiplanet-input-with-icon">
                                    <i class="fas fa-lock"></i>
                                    <input type="password" id="password" name="password" required>
                                    <button type="button" class="digiplanet-password-toggle" data-target="password">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                                <div class="digiplanet-password-strength">
                                    <div class="digiplanet-password-strength-meter">
                                        <div class="digiplanet-password-strength-fill"></div>
                                    </div>
                                    <span class="digiplanet-password-strength-text"></span>
                                </div>
                            </div>
                            
                            <div class="digiplanet-form-group">
                                <label for="confirm_password"><?php _e('Confirm Password', 'digiplanet-digital-products'); ?> *</label>
                                <div class="digiplanet-input-with-icon">
                                    <i class="fas fa-lock"></i>
                                    <input type="password" id="confirm_password" name="confirm_password" required>
                                    <button type="button" class="digiplanet-password-toggle" data-target="confirm_password">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                                <div class="digiplanet-password-match"></div>
                            </div>
                        </div>
                        
                        <div class="digiplanet-form-group">
                            <label class="digiplanet-checkbox">
                                <input type="checkbox" name="terms" id="terms" required>
                                <span class="digiplanet-checkbox-checkmark"></span>
                                <span class="digiplanet-checkbox-label">
                                    <?php 
                                    printf(
                                        __('I agree to the %s and %s', 'digiplanet-digital-products'),
                                        '<a href="' . esc_url(home_url('/terms/')) . '" target="_blank">' . __('Terms of Service', 'digiplanet-digital-products') . '</a>',
                                        '<a href="' . esc_url(home_url('/privacy/')) . '" target="_blank">' . __('Privacy Policy', 'digiplanet-digital-products') . '</a>'
                                    );
                                    ?>
                                </span>
                            </label>
                        </div>
                        
                        <div class="digiplanet-form-group">
                            <label class="digiplanet-checkbox">
                                <input type="checkbox" name="newsletter" id="newsletter" <?php checked($form_data['newsletter']); ?>>
                                <span class="digiplanet-checkbox-checkmark"></span>
                                <span class="digiplanet-checkbox-label">
                                    <?php _e('Subscribe to our newsletter for updates, tips, and special offers', 'digiplanet-digital-products'); ?>
                                </span>
                            </label>
                        </div>
                        
                        <div class="digiplanet-form-group">
                            <button type="submit" class="digiplanet-btn digiplanet-btn-primary digiplanet-btn-block">
                                <i class="fas fa-user-plus"></i>
                                <?php _e('Create Account', 'digiplanet-digital-products'); ?>
                            </button>
                        </div>
                        
                        <div class="digiplanet-login-link">
                            <p><?php _e('Already have an account?', 'digiplanet-digital-products'); ?>
                                <a href="<?php echo wp_login_url(); ?>"><?php _e('Sign in here', 'digiplanet-digital-products'); ?></a>
                            </p>
                        </div>
                        
                        <div class="digiplanet-social-register">
                            <div class="digiplanet-social-divider">
                                <span><?php _e('Or register with', 'digiplanet-digital-products'); ?></span>
                            </div>
                            
                            <div class="digiplanet-social-buttons">
                                <?php if (defined('NEXTEND_SOCIAL_LOGIN_VERSION')): ?>
                                    <?php echo do_shortcode('[nextend_social_login]'); ?>
                                <?php else: ?>
                                    <button type="button" class="digiplanet-social-btn digiplanet-social-google" onclick="socialRegister('google')">
                                        <i class="fab fa-google"></i>
                                        <?php _e('Google', 'digiplanet-digital-products'); ?>
                                    </button>
                                    
                                    <button type="button" class="digiplanet-social-btn digiplanet-social-facebook" onclick="socialRegister('facebook')">
                                        <i class="fab fa-facebook-f"></i>
                                        <?php _e('Facebook', 'digiplanet-digital-products'); ?>
                                    </button>
                                    
                                    <button type="button" class="digiplanet-social-btn digiplanet-social-github" onclick="socialRegister('github')">
                                        <i class="fab fa-github"></i>
                                        <?php _e('GitHub', 'digiplanet-digital-products'); ?>
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </form>
                <?php endif; ?>
                
                <div class="digiplanet-register-footer">
                    <p><?php _e('By creating an account, you agree to our', 'digiplanet-digital-products'); ?>
                        <a href="<?php echo esc_url(home_url('/terms/')); ?>"><?php _e('Terms of Service', 'digiplanet-digital-products'); ?></a>
                        <?php _e('and', 'digiplanet-digital-products'); ?>
                        <a href="<?php echo esc_url(home_url('/privacy/')); ?>"><?php _e('Privacy Policy', 'digiplanet-digital-products'); ?></a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // Password toggle
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
    
    // Username availability check
    $('#username').on('blur', function() {
        var username = $(this).val().trim();
        if (username.length < 3) return;
        
        $.ajax({
            url: digiplanet_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'digiplanet_check_username',
                username: username,
                nonce: digiplanet_ajax.nonce
            },
            beforeSend: function() {
                $('.digiplanet-username-availability').html('<span class="checking"><i class="fas fa-spinner fa-spin"></i> <?php _e('Checking availability...', 'digiplanet-digital-products'); ?></span>');
            },
            success: function(response) {
                if (response.success) {
                    if (response.data.available) {
                        $('.digiplanet-username-availability').html('<span class="available"><i class="fas fa-check-circle"></i> <?php _e('Username is available', 'digiplanet-digital-products'); ?></span>');
                    } else {
                        $('.digiplanet-username-availability').html('<span class="not-available"><i class="fas fa-times-circle"></i> ' + response.data.message + '</span>');
                    }
                }
            }
        });
    });
    
    // Password strength meter
    $('#password').on('keyup', function() {
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
            text.text('<?php _e('Weak', 'digiplanet-digital-products'); ?>').css('color', '#dc3545');
            meter.css('background-color', '#dc3545');
        } else if (strength <= 4) {
            text.text('<?php _e('Good', 'digiplanet-digital-products'); ?>').css('color', '#ffc107');
            meter.css('background-color', '#ffc107');
        } else {
            text.text('<?php _e('Strong', 'digiplanet-digital-products'); ?>').css('color', '#28a745');
            meter.css('background-color', '#28a745');
        }
    });
    
    // Password match check
    $('#confirm_password').on('keyup', function() {
        var password = $('#password').val();
        var confirm = $(this).val();
        var matchDiv = $('.digiplanet-password-match');
        
        if (confirm.length === 0) {
            matchDiv.html('');
        } else if (password === confirm) {
            matchDiv.html('<span class="match"><i class="fas fa-check"></i> <?php _e('Passwords match', 'digiplanet-digital-products'); ?></span>');
        } else {
            matchDiv.html('<span class="no-match"><i class="fas fa-times"></i> <?php _e('Passwords do not match', 'digiplanet-digital-products'); ?></span>');
        }
    });
    
    // Form validation
    $('#digiplanet-register-form').on('submit', function(e) {
        var $form = $(this);
        var hasError = false;
        
        // Clear previous errors
        $('.digiplanet-form-group').removeClass('has-error');
        $('.digiplanet-error-message').remove();
        
        // Check terms agreement
        if (!$('#terms').is(':checked')) {
            showFieldError($('#terms').closest('.digiplanet-checkbox'), '<?php _e('You must agree to the terms and conditions', 'digiplanet-digital-products'); ?>');
            hasError = true;
        }
        
        if (hasError) {
            e.preventDefault();
            return false;
        }
        
        // Show loading state
        $form.find('button[type="submit"]').prop('disabled', true)
            .html('<i class="fas fa-spinner fa-spin"></i> <?php _e('Creating account...', 'digiplanet-digital-products'); ?>');
    });
    
    // Real-time validation
    $('#first_name, #last_name, #username, #email, #password, #confirm_password').on('blur', function() {
        var $input = $(this);
        var value = $input.val().trim();
        var id = $input.attr('id');
        
        $input.closest('.digiplanet-form-group').removeClass('has-error');
        $input.siblings('.digiplanet-error-message').remove();
        
        if (value === '' && ['first_name', 'username', 'email', 'password', 'confirm_password'].includes(id)) {
            var messages = {
                'first_name': '<?php _e('Please enter your first name', 'digiplanet-digital-products'); ?>',
                'username': '<?php _e('Please enter a username', 'digiplanet-digital-products'); ?>',
                'email': '<?php _e('Please enter your email address', 'digiplanet-digital-products'); ?>',
                'password': '<?php _e('Please enter a password', 'digiplanet-digital-products'); ?>',
                'confirm_password': '<?php _e('Please confirm your password', 'digiplanet-digital-products'); ?>'
            };
            showFieldError($input, messages[id]);
        }
        
        // Email validation
        if (id === 'email' && value !== '') {
            var emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailPattern.test(value)) {
                showFieldError($input, '<?php _e('Please enter a valid email address', 'digiplanet-digital-products'); ?>');
            }
        }
        
        // Username validation
        if (id === 'username' && value !== '') {
            if (value.length < 3) {
                showFieldError($input, '<?php _e('Username must be at least 3 characters long', 'digiplanet-digital-products'); ?>');
            }
            if (!/^[a-zA-Z0-9_]+$/.test(value)) {
                showFieldError($input, '<?php _e('Username can only contain letters, numbers, and underscores', 'digiplanet-digital-products'); ?>');
            }
        }
    });
});

function showFieldError($element, message) {
    $element.closest('.digiplanet-form-group').addClass('has-error');
    if ($element.hasClass('digiplanet-checkbox')) {
        $element.append('<span class="digiplanet-error-message">' + message + '</span>');
    } else {
        $element.after('<span class="digiplanet-error-message">' + message + '</span>');
    }
}

function socialRegister(provider) {
    $.ajax({
        url: digiplanet_ajax.ajax_url,
        type: 'POST',
        data: {
            action: 'digiplanet_social_register',
            provider: provider,
            nonce: digiplanet_ajax.nonce
        },
        beforeSend: function() {
            $('.digiplanet-social-btn').prop('disabled', true);
        },
        success: function(response) {
            if (response.success && response.data.redirect_url) {
                window.location.href = response.data.redirect_url;
            } else {
                showNotification(response.data.message, 'error');
            }
        },
        complete: function() {
            $('.digiplanet-social-btn').prop('disabled', false);
        }
    });
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
.digiplanet-register-page {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    padding: 20px;
}

.digiplanet-register-container {
    display: flex;
    max-width: 1400px;
    width: 100%;
    background: white;
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
}

.digiplanet-register-left {
    flex: 1;
    background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
    color: white;
    padding: 60px 40px;
    display: flex;
    flex-direction: column;
}

.digiplanet-register-brand {
    text-align: center;
    margin-bottom: 60px;
}

.digiplanet-register-logo {
    display: inline-block;
    margin-bottom: 30px;
}

.digiplanet-register-logo img {
    height: 60px;
    width: auto;
}

.digiplanet-register-left h1 {
    font-size: 36px;
    font-weight: 700;
    margin: 0 0 15px;
    color: white;
}

.digiplanet-register-left p {
    font-size: 16px;
    opacity: 0.9;
    line-height: 1.6;
    margin: 0;
}

.digiplanet-benefits {
    margin-bottom: 40px;
}

.digiplanet-benefits h3 {
    font-size: 22px;
    font-weight: 600;
    margin: 0 0 25px;
    color: white;
}

.digiplanet-benefit {
    display: flex;
    align-items: flex-start;
    gap: 20px;
    margin-bottom: 25px;
}

.digiplanet-benefit i {
    font-size: 24px;
    margin-top: 5px;
    flex-shrink: 0;
    background: rgba(255, 255, 255, 0.2);
    width: 50px;
    height: 50px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.digiplanet-benefit h4 {
    margin: 0 0 5px;
    font-size: 18px;
    font-weight: 600;
    color: white;
}

.digiplanet-benefit p {
    margin: 0;
    font-size: 14px;
    opacity: 0.8;
    line-height: 1.5;
}

.digiplanet-testimonials {
    margin-top: auto;
    padding-top: 40px;
    border-top: 1px solid rgba(255, 255, 255, 0.2);
}

.digiplanet-testimonials h3 {
    font-size: 22px;
    font-weight: 600;
    margin: 0 0 25px;
    color: white;
}

.digiplanet-testimonial {
    background: rgba(255, 255, 255, 0.1);
    border-radius: 12px;
    padding: 25px;
    backdrop-filter: blur(10px);
}

.digiplanet-testimonial-content p {
    font-size: 16px;
    font-style: italic;
    line-height: 1.6;
    margin: 0 0 20px;
    color: white;
}

.digiplanet-testimonial-author {
    display: flex;
    flex-direction: column;
}

.digiplanet-testimonial-author strong {
    font-size: 16px;
    font-weight: 600;
    color: white;
    margin-bottom: 5px;
}

.digiplanet-testimonial-author span {
    font-size: 14px;
    opacity: 0.8;
    color: white;
}

.digiplanet-register-right {
    flex: 1.2;
    padding: 60px 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow-y: auto;
    max-height: 100vh;
}

.digiplanet-register-form-container {
    max-width: 500px;
    width: 100%;
}

.digiplanet-register-form-header {
    text-align: center;
    margin-bottom: 40px;
}

.digiplanet-register-form-header h2 {
    font-size: 28px;
    font-weight: 700;
    color: #333;
    margin: 0 0 10px;
}

.digiplanet-register-form-header p {
    color: #6c757d;
    font-size: 16px;
    margin: 0;
}

.digiplanet-register-form {
    margin-bottom: 30px;
}

.digiplanet-form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
}

.digiplanet-username-availability {
    font-size: 13px;
    margin-top: 5px;
}

.digiplanet-username-availability .checking {
    color: #6c757d;
}

.digiplanet-username-availability .available {
    color: #28a745;
}

.digiplanet-username-availability .not-available {
    color: #dc3545;
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
    border-radius: 3px;
}

.digiplanet-password-strength-text {
    font-size: 13px;
    font-weight: 600;
}

.digiplanet-password-match {
    font-size: 13px;
    margin-top: 5px;
}

.digiplanet-password-match .match {
    color: #28a745;
}

.digiplanet-password-match .no-match {
    color: #dc3545;
}

.digiplanet-checkbox {
    display: flex;
    align-items: flex-start;
    cursor: pointer;
    position: relative;
    padding-left: 35px;
    user-select: none;
    margin-bottom: 10px;
}

.digiplanet-checkbox input {
    position: absolute;
    opacity: 0;
    cursor: pointer;
    height: 0;
    width: 0;
}

.digiplanet-checkbox-checkmark {
    position: absolute;
    left: 0;
    top: 0;
    height: 20px;
    width: 20px;
    background-color: #fff;
    border: 2px solid #dee2e6;
    border-radius: 4px;
    transition: all 0.3s ease;
}

.digiplanet-checkbox:hover input ~ .digiplanet-checkbox-checkmark {
    border-color: #43e97b;
}

.digiplanet-checkbox input:checked ~ .digiplanet-checkbox-checkmark {
    background-color: #43e97b;
    border-color: #43e97b;
}

.digiplanet-checkbox-checkmark:after {
    content: "";
    position: absolute;
    display: none;
}

.digiplanet-checkbox input:checked ~ .digiplanet-checkbox-checkmark:after {
    display: block;
}

.digiplanet-checkbox .digiplanet-checkbox-checkmark:after {
    left: 6px;
    top: 2px;
    width: 6px;
    height: 10px;
    border: solid white;
    border-width: 0 2px 2px 0;
    transform: rotate(45deg);
}

.digiplanet-checkbox-label {
    font-size: 14px;
    color: #495057;
    line-height: 1.5;
}

.digiplanet-checkbox-label a {
    color: #43e97b;
    text-decoration: none;
    font-weight: 500;
}

.digiplanet-checkbox-label a:hover {
    text-decoration: underline;
}

.digiplanet-btn-primary {
    background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
    color: white;
    border: none;
    padding: 16px 30px;
    border-radius: 8px;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
}

.digiplanet-btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 20px rgba(67, 233, 123, 0.3);
}

.digiplanet-btn-primary:disabled {
    opacity: 0.7;
    cursor: not-allowed;
    transform: none;
    box-shadow: none;
}

.digiplanet-login-link {
    text-align: center;
    margin-top: 20px;
    padding-top: 20px;
    border-top: 1px solid #e9ecef;
}

.digiplanet-login-link p {
    margin: 0;
    color: #6c757d;
    font-size: 14px;
}

.digiplanet-login-link a {
    color: #43e97b;
    font-weight: 600;
    text-decoration: none;
}

.digiplanet-login-link a:hover {
    text-decoration: underline;
}

.digiplanet-social-register {
    margin-top: 30px;
}

.digiplanet-social-divider {
    display: flex;
    align-items: center;
    text-align: center;
    margin-bottom: 20px;
}

.digiplanet-social-divider::before,
.digiplanet-social-divider::after {
    content: '';
    flex: 1;
    border-bottom: 1px solid #e9ecef;
}

.digiplanet-social-divider span {
    padding: 0 15px;
    color: #6c757d;
    font-size: 14px;
}

.digiplanet-social-buttons {
    display: flex;
    gap: 10px;
}

.digiplanet-social-btn {
    flex: 1;
    padding: 12px;
    border: 2px solid #e9ecef;
    border-radius: 8px;
    background: white;
    color: #495057;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}

.digiplanet-social-btn:hover {
    border-color: #dee2e6;
    background: #f8f9fa;
    transform: translateY(-2px);
}

.digiplanet-social-google:hover {
    border-color: #db4437;
    background: #f8f1f0;
}

.digiplanet-social-facebook:hover {
    border-color: #4267B2;
    background: #f0f2f5;
}

.digiplanet-social-github:hover {
    border-color: #333;
    background: #f6f8fa;
}

.digiplanet-register-footer {
    text-align: center;
    padding-top: 20px;
    border-top: 1px solid #e9ecef;
    margin-top: 30px;
}

.digiplanet-register-footer p {
    margin: 0;
    font-size: 12px;
    color: #6c757d;
    line-height: 1.5;
}

.digiplanet-register-footer a {
    color: #43e97b;
    text-decoration: none;
}

.digiplanet-register-footer a:hover {
    text-decoration: underline;
}

/* Success alert */
.digiplanet-alert-success {
    background-color: #d4edda;
    border-color: #c3e6cb;
    color: #155724;
    padding: 20px;
    border-radius: 8px;
    margin-bottom: 20px;
}

.digiplanet-alert-success h4 {
    margin: 0 0 10px;
    font-size: 18px;
}

.digiplanet-alert-success p {
    margin: 0 0 10px;
    font-size: 14px;
}

.digiplanet-alert-success p:last-child {
    margin-bottom: 0;
}

/* Error states */
.digiplanet-form-group.has-error .digiplanet-input-with-icon input {
    border-color: #dc3545;
}

.digiplanet-form-group.has-error .digiplanet-input-with-icon i {
    color: #dc3545;
}

.digiplanet-form-group.has-error .digiplanet-checkbox-checkmark {
    border-color: #dc3545;
}

.digiplanet-error-message {
    display: block;
    color: #dc3545;
    font-size: 13px;
    margin-top: 5px;
}

/* Responsive */
@media (max-width: 1200px) {
    .digiplanet-register-container {
        flex-direction: column;
        max-width: 800px;
    }
    
    .digiplanet-register-left {
        padding: 40px 30px;
    }
    
    .digiplanet-register-right {
        padding: 40px 30px;
    }
}

@media (max-width: 768px) {
    .digiplanet-register-page {
        padding: 10px;
    }
    
    .digiplanet-register-left,
    .digiplanet-register-right {
        padding: 30px 20px;
    }
    
    .digiplanet-form-row {
        grid-template-columns: 1fr;
    }
    
    .digiplanet-register-left h1 {
        font-size: 28px;
    }
    
    .digiplanet-register-form-header h2 {
        font-size: 24px;
    }
    
    .digiplanet-social-buttons {
        flex-direction: column;
    }
}

@media (max-width: 576px) {
    .digiplanet-benefit {
        flex-direction: column;
        text-align: center;
    }
    
    .digiplanet-benefit i {
        margin: 0 auto 15px;
    }
}
</style>