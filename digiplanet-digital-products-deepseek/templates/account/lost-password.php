<?php
/**
 * Lost Password Template
 */

if (!defined('ABSPATH')) {
    exit;
}

// Redirect if already logged in
if (is_user_logged_in()) {
    wp_redirect(Digiplanet_User_Portal::get_instance()->get_account_url());
    exit;
}

$errors = [];
$success = false;
$step = isset($_GET['step']) ? sanitize_text_field($_GET['step']) : 'request';
$key = isset($_GET['key']) ? sanitize_text_field($_GET['key']) : '';
$login = isset($_GET['login']) ? sanitize_text_field($_GET['login']) : '';

// Handle password reset request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['digiplanet_reset_request_nonce']) && wp_verify_nonce($_POST['digiplanet_reset_request_nonce'], 'digiplanet_reset_request')) {
        $email = sanitize_email($_POST['email']);
        
        if (empty($email) || !is_email($email)) {
            $errors[] = __('Please enter a valid email address.', 'digiplanet-digital-products');
        } else {
            $user = get_user_by('email', $email);
            if ($user) {
                // Generate reset key
                $key = get_password_reset_key($user);
                if (!is_wp_error($key)) {
                    // Send reset email
                    $reset_url = add_query_arg([
                        'key' => $key,
                        'login' => rawurlencode($user->user_login),
                        'step' => 'reset'
                    ], get_permalink());
                    
                    $subject = sprintf(__('Password Reset for %s', 'digiplanet-digital-products'), get_bloginfo('name'));
                    $message = sprintf(__('Someone has requested a password reset for the following account on %s:', 'digiplanet-digital-products'), get_bloginfo('name')) . "\r\n\r\n";
                    $message .= sprintf(__('Username: %s', 'digiplanet-digital-products'), $user->user_login) . "\r\n\r\n";
                    $message .= __('If this was a mistake, just ignore this email and nothing will happen.', 'digiplanet-digital-products') . "\r\n\r\n";
                    $message .= __('To reset your password, visit the following address:', 'digiplanet-digital-products') . "\r\n\r\n";
                    $message .= $reset_url . "\r\n";
                    
                    wp_mail($email, $subject, $message);
                    
                    $success = true;
                    $step = 'request_sent';
                } else {
                    $errors[] = __('Failed to generate reset key. Please try again.', 'digiplanet-digital-products');
                }
            } else {
                // Don't reveal if user exists
                $success = true;
                $step = 'request_sent';
            }
        }
    }
    
    // Handle password reset
    if (isset($_POST['digiplanet_reset_password_nonce']) && wp_verify_nonce($_POST['digiplanet_reset_password_nonce'], 'digiplanet_reset_password')) {
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];
        
        if (empty($password) || empty($confirm_password)) {
            $errors[] = __('Please enter and confirm your new password.', 'digiplanet-digital-products');
        } elseif ($password !== $confirm_password) {
            $errors[] = __('Passwords do not match.', 'digiplanet-digital-products');
        } elseif (strlen($password) < 8) {
            $errors[] = __('Password must be at least 8 characters long.', 'digiplanet-digital-products');
        } else {
            $user = check_password_reset_key($key, $login);
            if (is_wp_error($user)) {
                $errors[] = __('Invalid or expired reset link. Please request a new password reset.', 'digiplanet-digital-products');
            } else {
                reset_password($user, $password);
                $step = 'reset_complete';
            }
        }
    }
}
?>

<div class="digiplanet-password-reset-page">
    <div class="digiplanet-password-reset-container">
        <!-- Left Side - Info -->
        <div class="digiplanet-password-reset-left">
            <div class="digiplanet-password-reset-brand">
                <a href="<?php echo home_url('/'); ?>" class="digiplanet-password-reset-logo">
                    <?php
                    $logo_url = get_theme_mod('custom_logo') ? wp_get_attachment_image_url(get_theme_mod('custom_logo'), 'full') : DIGIPLANET_ASSETS_URL . 'images/logo.png';
                    ?>
                    <img src="<?php echo esc_url($logo_url); ?>" alt="<?php echo get_bloginfo('name'); ?>">
                </a>
                <h1><?php _e('Reset Your Password', 'digiplanet-digital-products'); ?></h1>
                <p><?php _e('Follow the steps below to regain access to your account.', 'digiplanet-digital-products'); ?></p>
            </div>
            
            <div class="digiplanet-reset-steps">
                <div class="digiplanet-reset-step <?php echo $step === 'request' ? 'active' : ''; ?>">
                    <div class="digiplanet-step-number">1</div>
                    <div class="digiplanet-step-content">
                        <h4><?php _e('Enter Your Email', 'digiplanet-digital-products'); ?></h4>
                        <p><?php _e('Provide the email address associated with your account.', 'digiplanet-digital-products'); ?></p>
                    </div>
                </div>
                
                <div class="digiplanet-reset-step <?php echo in_array($step, ['request_sent', 'reset']) ? 'active' : ''; ?>">
                    <div class="digiplanet-step-number">2</div>
                    <div class="digiplanet-step-content">
                        <h4><?php _e('Check Your Email', 'digiplanet-digital-products'); ?></h4>
                        <p><?php _e('Click the reset link sent to your email address.', 'digiplanet-digital-products'); ?></p>
                    </div>
                </div>
                
                <div class="digiplanet-reset-step <?php echo $step === 'reset' ? 'active' : ''; ?>">
                    <div class="digiplanet-step-number">3</div>
                    <div class="digiplanet-step-content">
                        <h4><?php _e('Create New Password', 'digiplanet-digital-products'); ?></h4>
                        <p><?php _e('Choose a strong, unique password for your account.', 'digiplanet-digital-products'); ?></p>
                    </div>
                </div>
                
                <div class="digiplanet-reset-step <?php echo $step === 'reset_complete' ? 'active' : ''; ?>">
                    <div class="digiplanet-step-number">4</div>
                    <div class="digiplanet-step-content">
                        <h4><?php _e('Sign In', 'digiplanet-digital-products'); ?></h4>
                        <p><?php _e('Access your account with your new password.', 'digiplanet-digital-products'); ?></p>
                    </div>
                </div>
            </div>
            
            <div class="digiplanet-security-tips">
                <h3><?php _e('Password Security Tips', 'digiplanet-digital-products'); ?></h3>
                <ul>
                    <li><i class="fas fa-check-circle"></i> <?php _e('Use at least 8 characters', 'digiplanet-digital-products'); ?></li>
                    <li><i class="fas fa-check-circle"></i> <?php _e('Include uppercase and lowercase letters', 'digiplanet-digital-products'); ?></li>
                    <li><i class="fas fa-check-circle"></i> <?php _e('Add numbers and special characters', 'digiplanet-digital-products'); ?></li>
                    <li><i class="fas fa-check-circle"></i> <?php _e('Avoid using personal information', 'digiplanet-digital-products'); ?></li>
                    <li><i class="fas fa-check-circle"></i> <?php _e('Use a unique password for this account', 'digiplanet-digital-products'); ?></li>
                </ul>
            </div>
        </div>
        
        <!-- Right Side - Forms -->
        <div class="digiplanet-password-reset-right">
            <div class="digiplanet-password-reset-form-container">
                <!-- Step 1: Request Reset -->
                <?php if ($step === 'request'): ?>
                    <div class="digiplanet-reset-step-form">
                        <div class="digiplanet-reset-form-header">
                            <h2><?php _e('Forgot Your Password?', 'digiplanet-digital-products'); ?></h2>
                            <p><?php _e('Enter your email address and we\'ll send you a link to reset your password.', 'digiplanet-digital-products'); ?></p>
                        </div>
                        
                        <?php if (!empty($errors)): ?>
                            <div class="digiplanet-alert digiplanet-alert-danger">
                                <?php foreach ($errors as $error): ?>
                                    <p><?php echo esc_html($error); ?></p>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                        
                        <form method="post" class="digiplanet-reset-form">
                            <?php wp_nonce_field('digiplanet_reset_request', 'digiplanet_reset_request_nonce'); ?>
                            
                            <div class="digiplanet-form-group">
                                <label for="email"><?php _e('Email Address', 'digiplanet-digital-products'); ?></label>
                                <div class="digiplanet-input-with-icon">
                                    <i class="fas fa-envelope"></i>
                                    <input type="email" id="email" name="email" required autocomplete="email">
                                </div>
                            </div>
                            
                            <div class="digiplanet-form-group">
                                <button type="submit" class="digiplanet-btn digiplanet-btn-primary digiplanet-btn-block">
                                    <i class="fas fa-paper-plane"></i>
                                    <?php _e('Send Reset Link', 'digiplanet-digital-products'); ?>
                                </button>
                            </div>
                        </form>
                        
                        <div class="digiplanet-back-to-login">
                            <a href="<?php echo wp_login_url(); ?>">
                                <i class="fas fa-arrow-left"></i>
                                <?php _e('Back to Sign In', 'digiplanet-digital-products'); ?>
                            </a>
                        </div>
                    </div>
                
                <!-- Step 2: Request Sent -->
                <?php elseif ($step === 'request_sent'): ?>
                    <div class="digiplanet-reset-step-form">
                        <div class="digiplanet-reset-success">
                            <div class="digiplanet-success-icon">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <h2><?php _e('Check Your Email', 'digiplanet-digital-products'); ?></h2>
                            <p><?php _e('We\'ve sent a password reset link to your email address. Please check your inbox and follow the instructions in the email.', 'digiplanet-digital-products'); ?></p>
                            
                            <div class="digiplanet-email-tips">
                                <h4><?php _e('Didn\'t receive the email?', 'digiplanet-digital-products'); ?></h4>
                                <ul>
                                    <li><?php _e('Check your spam or junk folder', 'digiplanet-digital-products'); ?></li>
                                    <li><?php _e('Make sure you entered the correct email address', 'digiplanet-digital-products'); ?></li>
                                    <li><?php _e('Wait a few minutes and try again', 'digiplanet-digital-products'); ?></li>
                                </ul>
                            </div>
                            
                            <div class="digiplanet-resend-link">
                                <p><?php _e('Still can\'t find it?', 'digiplanet-digital-products'); ?></p>
                                <a href="?step=request" class="digiplanet-btn digiplanet-btn-outline">
                                    <i class="fas fa-redo"></i>
                                    <?php _e('Resend Reset Link', 'digiplanet-digital-products'); ?>
                                </a>
                            </div>
                        </div>
                    </div>
                
                <!-- Step 3: Reset Password -->
                <?php elseif ($step === 'reset'): ?>
                    <div class="digiplanet-reset-step-form">
                        <div class="digiplanet-reset-form-header">
                            <h2><?php _e('Create New Password', 'digiplanet-digital-products'); ?></h2>
                            <p><?php _e('Please enter your new password below.', 'digiplanet-digital-products'); ?></p>
                        </div>
                        
                        <?php if (!empty($errors)): ?>
                            <div class="digiplanet-alert digiplanet-alert-danger">
                                <?php foreach ($errors as $error): ?>
                                    <p><?php echo esc_html($error); ?></p>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                        
                        <form method="post" class="digiplanet-reset-form">
                            <?php wp_nonce_field('digiplanet_reset_password', 'digiplanet_reset_password_nonce'); ?>
                            
                            <input type="hidden" name="key" value="<?php echo esc_attr($key); ?>">
                            <input type="hidden" name="login" value="<?php echo esc_attr($login); ?>">
                            
                            <div class="digiplanet-form-group">
                                <label for="password"><?php _e('New Password', 'digiplanet-digital-products'); ?></label>
                                <div class="digiplanet-input-with-icon">
                                    <i class="fas fa-lock"></i>
                                    <input type="password" id="password" name="password" required autocomplete="new-password">
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
                                <label for="confirm_password"><?php _e('Confirm New Password', 'digiplanet-digital-products'); ?></label>
                                <div class="digiplanet-input-with-icon">
                                    <i class="fas fa-lock"></i>
                                    <input type="password" id="confirm_password" name="confirm_password" required autocomplete="new-password">
                                    <button type="button" class="digiplanet-password-toggle" data-target="confirm_password">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                                <div class="digiplanet-password-match"></div>
                            </div>
                            
                            <div class="digiplanet-form-group">
                                <button type="submit" class="digiplanet-btn digiplanet-btn-primary digiplanet-btn-block">
                                    <i class="fas fa-key"></i>
                                    <?php _e('Reset Password', 'digiplanet-digital-products'); ?>
                                </button>
                            </div>
                        </form>
                    </div>
                
                <!-- Step 4: Reset Complete -->
                <?php elseif ($step === 'reset_complete'): ?>
                    <div class="digiplanet-reset-step-form">
                        <div class="digiplanet-reset-success">
                            <div class="digiplanet-success-icon">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <h2><?php _e('Password Reset Successful!', 'digiplanet-digital-products'); ?></h2>
                            <p><?php _e('Your password has been successfully reset. You can now sign in with your new password.', 'digiplanet-digital-products'); ?></p>
                            
                            <div class="digiplanet-success-actions">
                                <a href="<?php echo wp_login_url(); ?>" class="digiplanet-btn digiplanet-btn-primary">
                                    <i class="fas fa-sign-in-alt"></i>
                                    <?php _e('Sign In Now', 'digiplanet-digital-products'); ?>
                                </a>
                                
                                <a href="<?php echo home_url('/'); ?>" class="digiplanet-btn digiplanet-btn-outline">
                                    <i class="fas fa-home"></i>
                                    <?php _e('Return to Home', 'digiplanet-digital-products'); ?>
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
                
                <div class="digiplanet-reset-footer">
                    <p><?php _e('Need help?', 'digiplanet-digital-products'); ?>
                        <a href="<?php echo esc_url(home_url('/support/')); ?>"><?php _e('Contact Support', 'digiplanet-digital-products'); ?></a>
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
    $('.digiplanet-reset-form').on('submit', function(e) {
        var $form = $(this);
        var hasError = false;
        
        // Clear previous errors
        $('.digiplanet-form-group').removeClass('has-error');
        $('.digiplanet-error-message').remove();
        
        // Reset request form
        if ($form.find('#email').length) {
            var email = $('#email').val().trim();
            var emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            
            if (email === '') {
                showFieldError($('#email'), '<?php _e('Please enter your email address', 'digiplanet-digital-products'); ?>');
                hasError = true;
            } else if (!emailPattern.test(email)) {
                showFieldError($('#email'), '<?php _e('Please enter a valid email address', 'digiplanet-digital-products'); ?>');
                hasError = true;
            }
        }
        
        // Reset password form
        if ($form.find('#password').length) {
            var password = $('#password').val();
            var confirmPassword = $('#confirm_password').val();
            
            if (password === '') {
                showFieldError($('#password'), '<?php _e('Please enter a new password', 'digiplanet-digital-products'); ?>');
                hasError = true;
            } else if (password.length < 8) {
                showFieldError($('#password'), '<?php _e('Password must be at least 8 characters long', 'digiplanet-digital-products'); ?>');
                hasError = true;
            }
            
            if (confirmPassword === '') {
                showFieldError($('#confirm_password'), '<?php _e('Please confirm your new password', 'digiplanet-digital-products'); ?>');
                hasError = true;
            } else if (password !== confirmPassword) {
                showFieldError($('#confirm_password'), '<?php _e('Passwords do not match', 'digiplanet-digital-products'); ?>');
                hasError = true;
            }
        }
        
        if (hasError) {
            e.preventDefault();
            return false;
        }
        
        // Show loading state
        $form.find('button[type="submit"]').prop('disabled', true)
            .html('<i class="fas fa-spinner fa-spin"></i> <?php _e('Processing...', 'digiplanet-digital-products'); ?>');
    });
});

function showFieldError($input, message) {
    $input.closest('.digiplanet-form-group').addClass('has-error');
    $input.after('<span class="digiplanet-error-message">' + message + '</span>');
}
</script>

<style>
.digiplanet-password-reset-page {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    padding: 20px;
}

.digiplanet-password-reset-container {
    display: flex;
    max-width: 1200px;
    width: 100%;
    background: white;
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
}

.digiplanet-password-reset-left {
    flex: 1;
    background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
    color: white;
    padding: 60px 40px;
    display: flex;
    flex-direction: column;
}

.digiplanet-password-reset-brand {
    text-align: center;
    margin-bottom: 60px;
}

.digiplanet-password-reset-logo {
    display: inline-block;
    margin-bottom: 30px;
}

.digiplanet-password-reset-logo img {
    height: 60px;
    width: auto;
}

.digiplanet-password-reset-left h1 {
    font-size: 36px;
    font-weight: 700;
    margin: 0 0 15px;
    color: white;
}

.digiplanet-password-reset-left p {
    font-size: 16px;
    opacity: 0.9;
    line-height: 1.6;
    margin: 0;
}

.digiplanet-reset-steps {
    margin-bottom: 40px;
}

.digiplanet-reset-step {
    display: flex;
    align-items: center;
    gap: 20px;
    margin-bottom: 25px;
    opacity: 0.6;
    transition: opacity 0.3s ease;
}

.digiplanet-reset-step.active {
    opacity: 1;
}

.digiplanet-step-number {
    flex-shrink: 0;
    width: 40px;
    height: 40px;
    background: rgba(255, 255, 255, 0.2);
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: 18px;
}

.digiplanet-reset-step.active .digiplanet-step-number {
    background: white;
    color: #fa709a;
}

.digiplanet-step-content h4 {
    margin: 0 0 5px;
    font-size: 18px;
    font-weight: 600;
    color: white;
}

.digiplanet-step-content p {
    margin: 0;
    font-size: 14px;
    opacity: 0.8;
    line-height: 1.5;
}

.digiplanet-security-tips {
    margin-top: auto;
    padding-top: 40px;
    border-top: 1px solid rgba(255, 255, 255, 0.2);
}

.digiplanet-security-tips h3 {
    font-size: 22px;
    font-weight: 600;
    margin: 0 0 25px;
    color: white;
}

.digiplanet-security-tips ul {
    list-style: none;
    padding: 0;
    margin: 0;
}

.digiplanet-security-tips li {
    display: flex;
    align-items: center;
    gap: 15px;
    margin-bottom: 15px;
    font-size: 14px;
    color: white;
    opacity: 0.9;
}

.digiplanet-security-tips li i {
    color: #4cd964;
    font-size: 16px;
}

.digiplanet-password-reset-right {
    flex: 1.2;
    padding: 60px 40px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.digiplanet-password-reset-form-container {
    max-width: 500px;
    width: 100%;
}

.digiplanet-reset-step-form {
    margin-bottom: 30px;
}

.digiplanet-reset-form-header {
    text-align: center;
    margin-bottom: 40px;
}

.digiplanet-reset-form-header h2 {
    font-size: 28px;
    font-weight: 700;
    color: #333;
    margin: 0 0 10px;
}

.digiplanet-reset-form-header p {
    color: #6c757d;
    font-size: 16px;
    margin: 0;
}

.digiplanet-reset-form {
    margin-bottom: 30px;
}

.digiplanet-back-to-login {
    text-align: center;
    padding-top: 20px;
    border-top: 1px solid #e9ecef;
}

.digiplanet-back-to-login a {
    color: #fa709a;
    text-decoration: none;
    font-weight: 500;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: color 0.3s ease;
}

.digiplanet-back-to-login a:hover {
    color: #f8457a;
    text-decoration: underline;
}

.digiplanet-reset-success {
    text-align: center;
    padding: 30px 0;
}

.digiplanet-success-icon {
    font-size: 64px;
    color: #28a745;
    margin-bottom: 30px;
}

.digiplanet-reset-success h2 {
    font-size: 28px;
    font-weight: 700;
    color: #333;
    margin: 0 0 15px;
}

.digiplanet-reset-success p {
    color: #6c757d;
    font-size: 16px;
    line-height: 1.6;
    margin-bottom: 30px;
}

.digiplanet-email-tips {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 25px;
    margin-bottom: 30px;
    text-align: left;
}

.digiplanet-email-tips h4 {
    margin: 0 0 15px;
    color: #333;
    font-size: 18px;
    font-weight: 600;
}

.digiplanet-email-tips ul {
    list-style: none;
    padding: 0;
    margin: 0;
}

.digiplanet-email-tips li {
    padding-left: 25px;
    position: relative;
    margin-bottom: 10px;
    color: #6c757d;
    font-size: 14px;
}

.digiplanet-email-tips li:before {
    content: "•";
    position: absolute;
    left: 10px;
    color: #fa709a;
    font-size: 20px;
}

.digiplanet-email-tips li:last-child {
    margin-bottom: 0;
}

.digiplanet-resend-link {
    text-align: center;
}

.digiplanet-resend-link p {
    color: #6c757d;
    font-size: 14px;
    margin-bottom: 15px;
}

.digiplanet-success-actions {
    display: flex;
    gap: 15px;
    justify-content: center;
    margin-top: 30px;
}

.digiplanet-btn-outline {
    background: transparent;
    border: 2px solid #dee2e6;
    color: #495057;
    padding: 12px 30px;
    border-radius: 8px;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}

.digiplanet-btn-outline:hover {
    background: #f8f9fa;
    border-color: #adb5bd;
    color: #495057;
    text-decoration: none;
}

.digiplanet-reset-footer {
    text-align: center;
    padding-top: 20px;
    border-top: 1px solid #e9ecef;
    margin-top: 30px;
}

.digiplanet-reset-footer p {
    margin: 0;
    font-size: 14px;
    color: #6c757d;
}

.digiplanet-reset-footer a {
    color: #fa709a;
    text-decoration: none;
    font-weight: 500;
}

.digiplanet-reset-footer a:hover {
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

/* Responsive */
@media (max-width: 992px) {
    .digiplanet-password-reset-container {
        flex-direction: column;
        max-width: 600px;
    }
    
    .digiplanet-password-reset-left {
        padding: 40px 30px;
    }
    
    .digiplanet-password-reset-right {
        padding: 40px 30px;
    }
}

@media (max-width: 576px) {
    .digiplanet-password-reset-page {
        padding: 10px;
    }
    
    .digiplanet-password-reset-left,
    .digiplanet-password-reset-right {
        padding: 30px 20px;
    }
    
    .digiplanet-password-reset-left h1 {
        font-size: 28px;
    }
    
    .digiplanet-reset-form-header h2 {
        font-size: 24px;
    }
    
    .digiplanet-success-actions {
        flex-direction: column;
    }
    
    .digiplanet-reset-step {
        flex-direction: column;
        text-align: center;
        gap: 15px;
    }
}
</style>