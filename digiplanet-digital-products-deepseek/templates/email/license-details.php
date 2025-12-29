<?php
/**
 * License Details Email Template
 */

if (!defined('ABSPATH')) {
    exit;
}

$order = isset($args['order']) ? $args['order'] : null;
$order_items = isset($args['order_items']) ? $args['order_items'] : [];
$customer = isset($args['customer']) ? $args['customer'] : null;

if (!$order || !$customer) {
    return;
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php _e('Your License Details', 'digiplanet-digital-products'); ?></title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 0;
            background-color: #f6f6f6;
        }
        
        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
        }
        
        .email-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px 30px;
            text-align: center;
        }
        
        .email-header h1 {
            margin: 0;
            font-size: 28px;
            font-weight: 600;
        }
        
        .email-header p {
            margin: 10px 0 0;
            opacity: 0.9;
            font-size: 16px;
        }
        
        .email-content {
            padding: 40px 30px;
        }
        
        .order-details {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 25px;
            margin-bottom: 30px;
        }
        
        .order-details h2 {
            margin: 0 0 20px;
            color: #333;
            font-size: 20px;
            font-weight: 600;
        }
        
        .order-info {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .info-item {
            display: flex;
            flex-direction: column;
        }
        
        .info-label {
            font-size: 14px;
            color: #6c757d;
            margin-bottom: 5px;
        }
        
        .info-value {
            font-size: 16px;
            font-weight: 600;
            color: #333;
        }
        
        .license-section {
            margin-bottom: 30px;
        }
        
        .license-section h2 {
            margin: 0 0 20px;
            color: #333;
            font-size: 20px;
            font-weight: 600;
            padding-bottom: 10px;
            border-bottom: 2px solid #f0f0f0;
        }
        
        .license-item {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 15px;
            border-left: 4px solid #3742fa;
        }
        
        .license-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 15px;
        }
        
        .product-name {
            font-size: 18px;
            font-weight: 600;
            color: #333;
            margin: 0;
        }
        
        .license-key {
            font-family: 'Courier New', monospace;
            background: #fff;
            padding: 12px 15px;
            border-radius: 6px;
            font-size: 16px;
            font-weight: 600;
            color: #333;
            letter-spacing: 1px;
            text-align: center;
            margin: 10px 0;
            border: 2px dashed #dee2e6;
        }
        
        .license-meta {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            margin-top: 15px;
        }
        
        .meta-item {
            display: flex;
            flex-direction: column;
        }
        
        .meta-label {
            font-size: 13px;
            color: #6c757d;
            margin-bottom: 3px;
        }
        
        .meta-value {
            font-size: 15px;
            font-weight: 500;
            color: #333;
        }
        
        .download-section {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            border-radius: 8px;
            padding: 25px;
            text-align: center;
            margin-bottom: 30px;
        }
        
        .download-section h3 {
            color: white;
            margin: 0 0 15px;
            font-size: 20px;
        }
        
        .download-button {
            display: inline-block;
            background: white;
            color: #f5576c;
            padding: 12px 30px;
            text-decoration: none;
            border-radius: 50px;
            font-weight: 600;
            font-size: 16px;
            transition: all 0.3s ease;
        }
        
        .download-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        
        .instructions {
            background: #f0f7ff;
            border-radius: 8px;
            padding: 25px;
            margin-bottom: 30px;
        }
        
        .instructions h3 {
            margin: 0 0 15px;
            color: #333;
            font-size: 18px;
        }
        
        .instructions ol {
            margin: 0;
            padding-left: 20px;
        }
        
        .instructions li {
            margin-bottom: 10px;
        }
        
        .support-section {
            text-align: center;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 8px;
            margin-bottom: 30px;
        }
        
        .support-section h3 {
            margin: 0 0 10px;
            color: #333;
            font-size: 18px;
        }
        
        .support-links {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-top: 15px;
        }
        
        .support-link {
            color: #3742fa;
            text-decoration: none;
            font-weight: 500;
        }
        
        .support-link:hover {
            text-decoration: underline;
        }
        
        .email-footer {
            background: #333;
            color: #999;
            padding: 30px;
            text-align: center;
            font-size: 14px;
        }
        
        .footer-links {
            margin-top: 15px;
        }
        
        .footer-link {
            color: #999;
            text-decoration: none;
            margin: 0 10px;
        }
        
        .footer-link:hover {
            color: #fff;
        }
        
        @media (max-width: 600px) {
            .email-header {
                padding: 30px 20px;
            }
            
            .email-content {
                padding: 30px 20px;
            }
            
            .order-info {
                grid-template-columns: 1fr;
            }
            
            .license-meta {
                grid-template-columns: 1fr;
            }
            
            .support-links {
                flex-direction: column;
                gap: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="email-container">
        <!-- Header -->
        <div class="email-header">
            <h1><?php _e('Your License Details', 'digiplanet-digital-products'); ?></h1>
            <p><?php _e('Thank you for your purchase!', 'digiplanet-digital-products'); ?></p>
        </div>
        
        <!-- Content -->
        <div class="email-content">
            <!-- Order Details -->
            <div class="order-details">
                <h2><?php _e('Order Information', 'digiplanet-digital-products'); ?></h2>
                
                <div class="order-info">
                    <div class="info-item">
                        <span class="info-label"><?php _e('Order Number', 'digiplanet-digital-products'); ?></span>
                        <span class="info-value">#<?php echo esc_html($order->order_number); ?></span>
                    </div>
                    
                    <div class="info-item">
                        <span class="info-label"><?php _e('Order Date', 'digiplanet-digital-products'); ?></span>
                        <span class="info-value"><?php echo date_i18n(get_option('date_format'), strtotime($order->created_at)); ?></span>
                    </div>
                    
                    <div class="info-item">
                        <span class="info-label"><?php _e('Customer Name', 'digiplanet-digital-products'); ?></span>
                        <span class="info-value"><?php echo esc_html($customer->display_name); ?></span>
                    </div>
                    
                    <div class="info-item">
                        <span class="info-label"><?php _e('Email Address', 'digiplanet-digital-products'); ?></span>
                        <span class="info-value"><?php echo esc_html($customer->user_email); ?></span>
                    </div>
                </div>
            </div>
            
            <!-- License Section -->
            <div class="license-section">
                <h2><?php _e('License Keys', 'digiplanet-digital-products'); ?></h2>
                
                <?php foreach ($order_items as $item): ?>
                    <?php
                    $product = get_post($item->product_id);
                    if (!$product) continue;
                    ?>
                    
                    <div class="license-item">
                        <div class="license-header">
                            <h3 class="product-name"><?php echo esc_html($product->post_title); ?></h3>
                            <span class="license-status"><?php _e('Active', 'digiplanet-digital-products'); ?></span>
                        </div>
                        
                        <?php if ($item->license_key): ?>
                            <div class="license-key">
                                <?php echo esc_html($item->license_key); ?>
                            </div>
                            
                            <div class="license-meta">
                                <div class="meta-item">
                                    <span class="meta-label"><?php _e('License Type', 'digiplanet-digital-products'); ?></span>
                                    <span class="meta-value"><?php echo esc_html(get_post_meta($item->product_id, '_digiplanet_license_type', true) ?: 'Single Site'); ?></span>
                                </div>
                                
                                <div class="meta-item">
                                    <span class="meta-label"><?php _e('Activations', 'digiplanet-digital-products'); ?></span>
                                    <span class="meta-value">0 / <?php echo esc_html(get_post_meta($item->product_id, '_digiplanet_max_activations', true) ?: 1); ?></span>
                                </div>
                                
                                <?php if ($item->download_expires): ?>
                                    <div class="meta-item">
                                        <span class="meta-label"><?php _e('Download Expires', 'digiplanet-digital-products'); ?></span>
                                        <span class="meta-value"><?php echo date_i18n(get_option('date_format'), strtotime($item->download_expires)); ?></span>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="meta-item">
                                    <span class="meta-label"><?php _e('Product Version', 'digiplanet-digital-products'); ?></span>
                                    <span class="meta-value"><?php echo esc_html(get_post_meta($item->product_id, '_digiplanet_version', true) ?: '1.0.0'); ?></span>
                                </div>
                            </div>
                        <?php else: ?>
                            <p><?php _e('No license key generated for this product.', 'digiplanet-digital-products'); ?></p>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <!-- Download Section -->
            <div class="download-section">
                <h3><?php _e('Ready to Download?', 'digiplanet-digital-products'); ?></h3>
                <a href="<?php echo esc_url(Digiplanet_User_Portal::get_instance()->get_account_url('downloads')); ?>" class="download-button">
                    <?php _e('Download Your Files', 'digiplanet-digital-products'); ?>
                </a>
            </div>
            
            <!-- Instructions -->
            <div class="instructions">
                <h3><?php _e('How to Use Your License', 'digiplanet-digital-products'); ?></h3>
                <ol>
                    <li><?php _e('Download the software files from your account dashboard', 'digiplanet-digital-products'); ?></li>
                    <li><?php _e('Install the software on your website/server', 'digiplanet-digital-products'); ?></li>
                    <li><?php _e('Enter the license key when prompted during installation', 'digiplanet-digital-products'); ?></li>
                    <li><?php _e('Activate the license through the software settings', 'digiplanet-digital-products'); ?></li>
                    <li><?php _e('Enjoy your premium digital product!', 'digiplanet-digital-products'); ?></li>
                </ol>
            </div>
            
            <!-- Support Section -->
            <div class="support-section">
                <h3><?php _e('Need Help?', 'digiplanet-digital-products'); ?></h3>
                <p><?php _e('Our support team is here to help you with any questions or issues.', 'digiplanet-digital-products'); ?></p>
                
                <div class="support-links">
                    <a href="<?php echo esc_url(home_url('/support/')); ?>" class="support-link">
                        <?php _e('Visit Support Center', 'digiplanet-digital-products'); ?>
                    </a>
                    <a href="<?php echo esc_url(home_url('/contact/')); ?>" class="support-link">
                        <?php _e('Contact Support', 'digiplanet-digital-products'); ?>
                    </a>
                    <a href="<?php echo esc_url(home_url('/documentation/')); ?>" class="support-link">
                        <?php _e('View Documentation', 'digiplanet-digital-products'); ?>
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Footer -->
        <div class="email-footer">
            <p>&copy; <?php echo date('Y'); ?> <?php echo get_bloginfo('name'); ?>. <?php _e('All rights reserved.', 'digiplanet-digital-products'); ?></p>
            
            <div class="footer-links">
                <a href="<?php echo esc_url(home_url('/terms/')); ?>" class="footer-link">
                    <?php _e('Terms of Service', 'digiplanet-digital-products'); ?>
                </a>
                <a href="<?php echo esc_url(home_url('/privacy/')); ?>" class="footer-link">
                    <?php _e('Privacy Policy', 'digiplanet-digital-products'); ?>
                </a>
                <a href="<?php echo esc_url(home_url('/refund-policy/')); ?>" class="footer-link">
                    <?php _e('Refund Policy', 'digiplanet-digital-products'); ?>
                </a>
                <a href="<?php echo esc_url(home_url('/unsubscribe/')); ?>" class="footer-link">
                    <?php _e('Unsubscribe', 'digiplanet-digital-products'); ?>
                </a>
            </div>
        </div>
    </div>
</body>
</html>