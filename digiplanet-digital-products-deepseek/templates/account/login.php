<?php
/**
 * Login Template
 */

if (!defined('ABSPATH')) {
    exit;
}

// Redirect if already logged in
if (is_user_logged_in()) {
    wp_redirect(Digiplanet_User_Portal::get_instance()->get_account_url());
    exit;
}

$redirect_to = isset($_GET['redirect_to']) ? $_GET['redirect_to'] : Digiplanet_User_Portal::get_instance()->get_account_url();
$errors = [];
$username = '';

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['digiplanet_login_nonce'])) {
    if (wp_verify_nonce($_POST['digiplanet_login_nonce'], 'digiplanet_login')) {
        $username = sanitize_user($_POST['username']);
        $password = $_POST['password'];
        $remember = isset($_POST['remember']);
        
        $credentials = [
            'user_login' => $username,
            'user_password' => $password,
            'remember' => $remember,
        ];
        
        $user = wp_signon($credentials, is_ssl());
        
        if (is_wp_error($user)) {
            $errors[] = $user->get_error_message();
        } else {
            wp_redirect($redirect_to);
            exit;
        }
    }
}
?>

<div class="digiplanet-login-page">
    <div class="digiplanet-login-container">
        <!-- Left Side - Branding -->
        <div class="digiplanet-login-left">
            <div class="digiplanet-login-brand">
                <a href="<?php echo home_url('/'); ?>" class="digiplanet-login-logo">
                    <?php
                    $logo_url = get_theme_mod('custom_logo') ? wp_get_attachment_image_url(get_theme_mod('custom_logo'), 'full') : DIGIPLANET_ASSETS_URL . 'images/logo.png';
                    ?>
                    <img src="<?php echo esc_url($logo_url); ?>" alt="<?php echo get_bloginfo('name'); ?>">
                </a>
                <h1><?php _e('Welcome Back', 'digiplanet-digital-products'); ?></h1>
                <p><?php _e('Sign in to your account to access your digital products, licenses, and account settings.', 'digiplanet-digital-products'); ?></p>
            </div>
            
            <div class="digiplanet-login-features">
                <div class="digiplanet-feature">
                    <i class="fas fa-box-open"></i>
                    <div>
                        <h4><?php _e('Your Digital Products', 'digiplanet-digital-products'); ?></h4>
                        <p><?php _e('Access all your purchased scripts and digital products', 'digiplanet-digital-products'); ?></p>
                    </div>
                </div>
                
                <div class="digiplanet-feature">
                    <i class="fas fa-key"></i>
                    <div>
                        <h4><?php _e('License Management', 'digiplanet-digital-products'); ?></h4>
                        <p><?php _e('Manage your software licenses and activations', 'digiplanet-digital-products'); ?></p>
                    </div>
                </div>
                
                <div class="digiplanet-feature">
                    <i class="fas fa-headset"></i>
                    <div>
                        <h4><?php _e('Premium Support', 'digiplanet-digital-products'); ?></h4>
                        <p><?php _e('Get dedicated support for your purchased products', 'digiplanet-digital-products'); ?></p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Right Side - Login Form -->
        <div class="digiplanet-login-right">
            <div class="digiplanet-login-form-container">
                <div class="digiplanet-login-form-header">
                    <h2><?php _e('Sign In to Your Account', 'digiplanet-digital-products'); ?></h2>
                    <p><?php _e('Enter your credentials to access your account', 'digiplanet-digital-products'); ?></p>
                </div>
                
                <?php if (!empty($errors)): ?>
                    <div class="digiplanet-alert digiplanet-alert-danger">
                        <?php foreach ($errors as $error): ?>
                            <p><?php echo esc_html($error); ?></p>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                
                <form method="post" class="digiplanet-login-form" id="digiplanet-login-form">
                    <?php wp_nonce_field('digiplanet_login', 'digiplanet_login_nonce'); ?>
                    
                    <input type="hidden" name="redirect_to" value="<?php echo esc_url($redirect_to); ?>">
                    
                    <div class="digiplanet-form-group">
                        <label for="username"><?php _e('Email Address or Username', 'digiplanet-digital-products'); ?></label>
                        <div class="digiplanet-input-with-icon">
                            <i class="fas fa-user"></i>
                            <input type="text" id="username" name="username" value="<?php echo esc_attr($username); ?>" required autocomplete="username">
                        </div>
                    </div>
                    
                    <div class="digiplanet-form-group">
                        <label for="password"><?php _e('Password', 'digiplanet-digital-products'); ?></label>
                        <div class="digiplanet-input-with-icon">
                            <i class="fas fa-lock"></i>
                            <input type="password" id="password" name="password" required autocomplete="current-password">
                            <button type="button" class="digiplanet-password-toggle" data-target="password">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="digiplanet-form-row">
                        <div class="digiplanet-form-group digiplanet-remember-me">
                            <label class="digiplanet-checkbox">
                                <input type="checkbox" name="remember" id="remember" value="1">
                                <span class="digiplanet-checkbox-checkmark"></span>
                                <span class="digiplanet-checkbox-label"><?php _e('Remember me', 'digiplanet-digital-products'); ?></span>
                            </label>
                        </div>
                        
                        <div class="digiplanet-form-group">
                            <a href="<?php echo wp_lostpassword_url(); ?>" class="digiplanet-forgot-password">
                                <?php _e('Forgot password?', 'digiplanet-digital-products'); ?>
                            </a>
                        </div>
                    </div>
                    
                    <div class="digiplanet-form-group">
                        <button type="submit" class="digiplanet-btn digiplanet-btn-primary digiplanet-btn-block">
                            <i class="fas fa-sign-in-alt"></i>
                            <?php _e('Sign In', 'digiplanet-digital-products'); ?>
                        </button>
                    </div>
                    
                    <?php if (get_option('users_can_register')): ?>
                        <div class="digiplanet-register-link">
                            <p><?php _e("Don't have an account?", 'digiplanet-digital-products'); ?>
                                <a href="<?php echo wp_registration_url(); ?>"><?php _e('Create one now', 'digiplanet-digital-products'); ?></a>
                            </p>
                        </div>
                    <?php endif; ?>
                    
                    <div class="digiplanet-social-login">
                        <div class="digiplanet-social-divider">
                            <span><?php _e('Or sign in with', 'digiplanet-digital-products'); ?></span>
                        </div>
                        
                        <div class="digiplanet-social-buttons">
                            <?php if (defined('NEXTEND_SOCIAL_LOGIN_VERSION')): ?>
                                <?php echo do_shortcode('[nextend_social_login]'); ?>
                            <?php else: ?>
                                <button type="button" class="digiplanet-social-btn digiplanet-social-google" onclick="window.location.href='<?php echo wp_login_url() . '?social=google'; ?>'">
                                    <i class="fab fa-google"></i>
                                    <?php _e('Google', 'digiplanet-digital-products'); ?>
                                </button>
                                
                                <button type="button" class="digiplanet-social-btn digiplanet-social-facebook" onclick="window.location.href='<?php echo wp_login_url() . '?social=facebook'; ?>'">
                                    <i class="fab fa-facebook-f"></i>
                                    <?php _e('Facebook', 'digiplanet-digital-products'); ?>
                                </button>
                                
                                <button type="button" class="digiplanet-social-btn digiplanet-social-github" onclick="window.location.href='<?php echo wp_login_url() . '?social=github'; ?>'">
                                    <i class="fab fa-github"></i>
                                    <?php _e('GitHub', 'digiplanet-digital-products'); ?>
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </form>
                
                <div class="digiplanet-login-footer">
                    <p><?php _e('By signing in, you agree to our', 'digiplanet-digital-products'); ?>
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
    
    // Form validation
    $('#digiplanet-login-form').on('submit', function(e) {
        var $form = $(this);
        var $username = $('#username');
        var $password = $('#password');
        var hasError = false;
        
        // Clear previous errors
        $('.digiplanet-form-group').removeClass('has-error');
        $('.digiplanet-error-message').remove();
        
        // Validate username
        if ($username.val().trim() === '') {
            showFieldError($username, '<?php _e('Please enter your email address or username', 'digiplanet-digital-products'); ?>');
            hasError = true;
        }
        
        // Validate password
        if ($password.val().trim() === '') {
            showFieldError($password, '<?php _e('Please enter your password', 'digiplanet-digital-products'); ?>');
            hasError = true;
        }
        
        if (hasError) {
            e.preventDefault();
            return false;
        }
        
        // Show loading state
        $form.find('button[type="submit"]').prop('disabled', true)
            .html('<i class="fas fa-spinner fa-spin"></i> <?php _e('Signing in...', 'digiplanet-digital-products'); ?>');
    });
    
    // Real-time validation
    $('#username, #password').on('blur', function() {
        var $input = $(this);
        var value = $input.val().trim();
        
        $input.closest('.digiplanet-form-group').removeClass('has-error');
        $input.siblings('.digiplanet-error-message').remove();
        
        if (value === '') {
            if ($input.attr('id') === 'username') {
                showFieldError($input, '<?php _e('Please enter your email address or username', 'digiplanet-digital-products'); ?>');
            } else {
                showFieldError($input, '<?php _e('Please enter your password', 'digiplanet-digital-products'); ?>');
            }
        }
    });
    
    // Social login
    $('.digiplanet-social-btn').on('click', function() {
        var social = $(this).hasClass('digiplanet-social-google') ? 'google' :
                     $(this).hasClass('digiplanet-social-facebook') ? 'facebook' : 'github';
        
        $.ajax({
            url: digiplanet_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'digiplanet_social_login',
                social: social,
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
    });
    
    // Auto-focus username field
    $('#username').focus();
});

function showFieldError($input, message) {
    $input.closest('.digiplanet-form-group').addClass('has-error');
    $input.after('<span class="digiplanet-error-message">' + message + '</span>');
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
.digiplanet-login-page {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    padding: 20px;
}

.digiplanet-login-container {
    display: flex;
    max-width: 1200px;
    width: 100%;
    background: white;
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
}

.digiplanet-login-left {
    flex: 1;
    background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
    color: white;
    padding: 60px 40px;
    display: flex;
    flex-direction: column;
}

.digiplanet-login-brand {
    text-align: center;
    margin-bottom: 60px;
}

.digiplanet-login-logo {
    display: inline-block;
    margin-bottom: 30px;
}

.digiplanet-login-logo img {
    height: 60px;
    width: auto;
}

.digiplanet-login-left h1 {
    font-size: 36px;
    font-weight: 700;
    margin: 0 0 15px;
    color: white;
}

.digiplanet-login-left p {
    font-size: 16px;
    opacity: 0.9;
    line-height: 1.6;
    margin: 0;
}

.digiplanet-login-features {
    margin-top: auto;
}

.digiplanet-feature {
    display: flex;
    align-items: flex-start;
    gap: 20px;
    margin-bottom: 30px;
}

.digiplanet-feature i {
    font-size: 24px;
    margin-top: 5px;
    flex-shrink: 0;
}

.digiplanet-feature h4 {
    margin: 0 0 5px;
    font-size: 18px;
    font-weight: 600;
}

.digiplanet-feature p {
    margin: 0;
    font-size: 14px;
    opacity: 0.8;
    line-height: 1.5;
}

.digiplanet-login-right {
    flex: 1;
    padding: 60px 40px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.digiplanet-login-form-container {
    max-width: 400px;
    width: 100%;
}

.digiplanet-login-form-header {
    text-align: center;
    margin-bottom: 40px;
}

.digiplanet-login-form-header h2 {
    font-size: 28px;
    font-weight: 700;
    color: #333;
    margin: 0 0 10px;
}

.digiplanet-login-form-header p {
    color: #6c757d;
    font-size: 16px;
    margin: 0;
}

.digiplanet-login-form {
    margin-bottom: 30px;
}

.digiplanet-form-group {
    margin-bottom: 20px;
}

.digiplanet-form-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: 600;
    color: #333;
    font-size: 14px;
}

.digiplanet-input-with-icon {
    position: relative;
}

.digiplanet-input-with-icon i {
    position: absolute;
    left: 15px;
    top: 50%;
    transform: translateY(-50%);
    color: #6c757d;
    font-size: 16px;
}

.digiplanet-input-with-icon input {
    width: 100%;
    padding: 12px 15px 12px 45px;
    border: 2px solid #e9ecef;
    border-radius: 8px;
    font-size: 16px;
    transition: all 0.3s ease;
}

.digiplanet-input-with-icon input:focus {
    outline: none;
    border-color: #4facfe;
    box-shadow: 0 0 0 3px rgba(79, 172, 254, 0.1);
}

.digiplanet-input-with-icon .digiplanet-password-toggle {
    position: absolute;
    right: 15px;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    color: #6c757d;
    cursor: pointer;
    font-size: 16px;
    padding: 0;
    width: 24px;
    height: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.digiplanet-form-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.digiplanet-remember-me {
    margin-bottom: 0;
}

.digiplanet-checkbox {
    display: flex;
    align-items: center;
    cursor: pointer;
    position: relative;
    padding-left: 35px;
    user-select: none;
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
    border-color: #4facfe;
}

.digiplanet-checkbox input:checked ~ .digiplanet-checkbox-checkmark {
    background-color: #4facfe;
    border-color: #4facfe;
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
}

.digiplanet-forgot-password {
    font-size: 14px;
    color: #4facfe;
    text-decoration: none;
    font-weight: 500;
}

.digiplanet-forgot-password:hover {
    text-decoration: underline;
}

.digiplanet-btn-block {
    width: 100%;
    display: block;
}

.digiplanet-btn-primary {
    background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
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
    gap: 10px;
}

.digiplanet-btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 20px rgba(79, 172, 254, 0.3);
}

.digiplanet-btn-primary:disabled {
    opacity: 0.7;
    cursor: not-allowed;
    transform: none;
    box-shadow: none;
}

.digiplanet-register-link {
    text-align: center;
    margin-top: 20px;
    padding-top: 20px;
    border-top: 1px solid #e9ecef;
}

.digiplanet-register-link p {
    margin: 0;
    color: #6c757d;
    font-size: 14px;
}

.digiplanet-register-link a {
    color: #4facfe;
    font-weight: 600;
    text-decoration: none;
}

.digiplanet-register-link a:hover {
    text-decoration: underline;
}

.digiplanet-social-login {
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

.digiplanet-login-footer {
    text-align: center;
    padding-top: 20px;
    border-top: 1px solid #e9ecef;
    margin-top: 30px;
}

.digiplanet-login-footer p {
    margin: 0;
    font-size: 12px;
    color: #6c757d;
    line-height: 1.5;
}

.digiplanet-login-footer a {
    color: #4facfe;
    text-decoration: none;
}

.digiplanet-login-footer a:hover {
    text-decoration: underline;
}

/* Error states */
.digiplanet-form-group.has-error .digiplanet-input-with-icon input {
    border-color: #dc3545;
}

.digiplanet-form-group.has-error .digiplanet-input-with-icon i {
    color: #dc3545;
}

.digiplanet-error-message {
    display: block;
    color: #dc3545;
    font-size: 13px;
    margin-top: 5px;
}

.digiplanet-alert {
    padding: 15px;
    border-radius: 8px;
    margin-bottom: 20px;
    border: 1px solid transparent;
}

.digiplanet-alert-danger {
    background-color: #f8d7da;
    border-color: #f5c6cb;
    color: #721c24;
}

/* Responsive */
@media (max-width: 992px) {
    .digiplanet-login-container {
        flex-direction: column;
        max-width: 500px;
    }
    
    .digiplanet-login-left {
        padding: 40px 30px;
    }
    
    .digiplanet-login-right {
        padding: 40px 30px;
    }
    
    .digiplanet-social-buttons {
        flex-direction: column;
    }
}

@media (max-width: 576px) {
    .digiplanet-login-page {
        padding: 10px;
    }
    
    .digiplanet-login-left,
    .digiplanet-login-right {
        padding: 30px 20px;
    }
    
    .digiplanet-login-left h1 {
        font-size: 28px;
    }
    
    .digiplanet-login-form-header h2 {
        font-size: 24px;
    }
    
    .digiplanet-form-row {
        flex-direction: column;
        align-items: flex-start;
        gap: 15px;
    }
}
</style>