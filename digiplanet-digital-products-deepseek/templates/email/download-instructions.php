<?php
/**
 * Download Instructions Email Template
 */

if (!defined('ABSPATH')) {
    exit;
}

$product = isset($args['product']) ? $args['product'] : null;
$download_link = isset($args['download_link']) ? $args['download_link'] : '';
$customer = isset($args['customer']) ? $args['customer'] : null;

if (!$product || !$download_link || !$customer) {
    return;
}

$product_name = $product->post_title;
$product_version = get_post_meta($product->ID, '_digiplanet_version', true) ?: '1.0.0';
$file_size = get_post_meta($product->ID, '_digiplanet_file_size', true);
$file_format = get_post_meta($product->ID, '_digiplanet_file_format', true);
$requirements = get_post_meta($product->ID, '_digiplanet_requirements', true);
$installation_guide = get_post_meta($product->ID, '_digiplanet_installation_guide', true);
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php printf(__('Download Instructions: %s', 'digiplanet-digital-products'), $product_name); ?></title>
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
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
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
        
        .download-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 12px;
            padding: 30px;
            text-align: center;
            margin-bottom: 30px;
            color: white;
        }
        
        .download-card h2 {
            margin: 0 0 20px;
            font-size: 24px;
            font-weight: 600;
        }
        
        .download-link-container {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            backdrop-filter: blur(10px);
        }
        
        .download-link {
            display: inline-block;
            background: white;
            color: #667eea;
            padding: 15px 40px;
            text-decoration: none;
            border-radius: 50px;
            font-weight: 600;
            font-size: 18px;
            transition: all 0.3s ease;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        
        .download-link:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 40px rgba(0,0,0,0.3);
        }
        
        .download-info {
            font-size: 14px;
            opacity: 0.9;
            margin-top: 15px;
        }
        
        .product-details {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 25px;
            margin-bottom: 30px;
        }
        
        .product-details h3 {
            margin: 0 0 20px;
            color: #333;
            font-size: 20px;
            font-weight: 600;
        }
        
        .details-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
        }
        
        .detail-item {
            display: flex;
            flex-direction: column;
        }
        
        .detail-label {
            font-size: 14px;
            color: #6c757d;
            margin-bottom: 5px;
        }
        
        .detail-value {
            font-size: 16px;
            font-weight: 500;
            color: #333;
        }
        
        .instructions {
            margin-bottom: 30px;
        }
        
        .instructions h3 {
            margin: 0 0 15px;
            color: #333;
            font-size: 20px;
            font-weight: 600;
        }
        
        .instruction-steps {
            background: #fff;
            border-radius: 8px;
            padding: 25px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }
        
        .step {
            display: flex;
            margin-bottom: 20px;
            padding-bottom: 20px;
            border-bottom: 1px solid #f0f0f0;
        }
        
        .step:last-child {
            margin-bottom: 0;
            padding-bottom: 0;
            border-bottom: none;
        }
        
        .step-number {
            flex-shrink: 0;
            width: 40px;
            height: 40px;
            background: #667eea;
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            margin-right: 20px;
        }
        
        .step-content h4 {
            margin: 0 0 10px;
            color: #333;
            font-size: 18px;
            font-weight: 600;
        }
        
        .step-content p {
            margin: 0;
            color: #666;
            font-size: 15px;
            line-height: 1.6;
        }
        
        .requirements {
            background: #fff9e6;
            border-radius: 8px;
            padding: 25px;
            margin-bottom: 30px;
            border-left: 4px solid #ffc107;
        }
        
        .requirements h3 {
            margin: 0 0 15px;
            color: #856404;
            font-size: 18px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .requirements h3 i {
            font-size: 20px;
        }
        
        .requirements-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .requirements-list li {
            margin-bottom: 10px;
            padding-left: 25px;
            position: relative;
            color: #856404;
        }
        
        .requirements-list li:before {
            content: "✓";
            position: absolute;
            left: 0;
            color: #28a745;
            font-weight: bold;
        }
        
        .troubleshooting {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 25px;
            margin-bottom: 30px;
        }
        
        .troubleshooting h3 {
            margin: 0 0 15px;
            color: #333;
            font-size: 18px;
            font-weight: 600;
        }
        
        .faq-item {
            margin-bottom: 15px;
            padding: 15px;
            background: white;
            border-radius: 6px;
            border-left: 3px solid #dee2e6;
        }
        
        .faq-item h4 {
            margin: 0 0 10px;
            color: #333;
            font-size: 16px;
            font-weight: 600;
        }
        
        .faq-item p {
            margin: 0;
            color: #666;
            font-size: 14px;
            line-height: 1.5;
        }
        
        .support-section {
            text-align: center;
            padding: 30px;
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            border-radius: 8px;
            color: white;
        }
        
        .support-section h3 {
            margin: 0 0 15px;
            font-size: 24px;
        }
        
        .support-button {
            display: inline-block;
            background: white;
            color: #f5576c;
            padding: 12px 30px;
            text-decoration: none;
            border-radius: 50px;
            font-weight: 600;
            font-size: 16px;
            transition: all 0.3s ease;
            margin-top: 10px;
        }
        
        .support-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
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
        
        .expiry-notice {
            background: #fff3cd;
            border: 1px solid #ffc107;
            border-radius: 8px;
            padding: 15px;
            margin-top: 20px;
            text-align: center;
            color: #856404;
            font-size: 14px;
        }
        
        @media (max-width: 600px) {
            .email-header {
                padding: 30px 20px;
            }
            
            .email-content {
                padding: 30px 20px;
            }
            
            .download-card {
                padding: 20px;
            }
            
            .download-link {
                padding: 12px 30px;
                font-size: 16px;
            }
            
            .details-grid {
                grid-template-columns: 1fr;
            }
            
            .step {
                flex-direction: column;
            }
            
            .step-number {
                margin-right: 0;
                margin-bottom: 15px;
            }
        }
    </style>
</head>
<body>
    <div class="email-container">
        <!-- Header -->
        <div class="email-header">
            <h1><?php printf(__('Download %s', 'digiplanet-digital-products'), $product_name); ?></h1>
            <p><?php _e('Your download is ready! Follow the instructions below.', 'digiplanet-digital-products'); ?></p>
        </div>
        
        <!-- Content -->
        <div class="email-content">
            <!-- Download Card -->
            <div class="download-card">
                <h2><?php _e('Your Download is Ready', 'digiplanet-digital-products'); ?></h2>
                
                <div class="download-link-container">
                    <a href="<?php echo esc_url($download_link); ?>" class="download-link">
                        <?php _e('Download Now', 'digiplanet-digital-products'); ?>
                    </a>
                    
                    <div class="download-info">
                        <p><?php printf(__('File: %s v%s', 'digiplanet-digital-products'), $product_name, $product_version); ?></p>
                        <?php if ($file_size): ?>
                            <p><?php printf(__('Size: %s', 'digiplanet-digital-products'), $file_size); ?></p>
                        <?php endif; ?>
                        <?php if ($file_format): ?>
                            <p><?php printf(__('Format: %s', 'digiplanet-digital-products'), $file_format); ?></p>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="expiry-notice">
                    <i class="fas fa-clock"></i>
                    <?php _e('This download link will expire in 24 hours. Please download immediately.', 'digiplanet-digital-products'); ?>
                </div>
            </div>
            
            <!-- Product Details -->
            <div class="product-details">
                <h3><?php _e('Product Details', 'digiplanet-digital-products'); ?></h3>
                
                <div class="details-grid">
                    <div class="detail-item">
                        <span class="detail-label"><?php _e('Product Name', 'digiplanet-digital-products'); ?></span>
                        <span class="detail-value"><?php echo esc_html($product_name); ?></span>
                    </div>
                    
                    <div class="detail-item">
                        <span class="detail-label"><?php _e('Version', 'digiplanet-digital-products'); ?></span>
                        <span class="detail-value"><?php echo esc_html($product_version); ?></span>
                    </div>
                    
                    <?php if ($file_size): ?>
                        <div class="detail-item">
                            <span class="detail-label"><?php _e('File Size', 'digiplanet-digital-products'); ?></span>
                            <span class="detail-value"><?php echo esc_html($file_size); ?></span>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($file_format): ?>
                        <div class="detail-item">
                            <span class="detail-label"><?php _e('File Format', 'digiplanet-digital-products'); ?></span>
                            <span class="detail-value"><?php echo esc_html($file_format); ?></span>
                        </div>
                    <?php endif; ?>
                    
                    <div class="detail-item">
                        <span class="detail-label"><?php _e('Downloaded By', 'digiplanet-digital-products'); ?></span>
                        <span class="detail-value"><?php echo esc_html($customer->display_name); ?></span>
                    </div>
                    
                    <div class="detail-item">
                        <span class="detail-label"><?php _e('Download Date', 'digiplanet-digital-products'); ?></span>
                        <span class="detail-value"><?php echo date_i18n(get_option('date_format') . ' ' . get_option('time_format')); ?></span>
                    </div>
                </div>
            </div>
            
            <!-- Installation Instructions -->
            <div class="instructions">
                <h3><?php _e('Installation Instructions', 'digiplanet-digital-products'); ?></h3>
                
                <div class="instruction-steps">
                    <div class="step">
                        <div class="step-number">1</div>
                        <div class="step-content">
                            <h4><?php _e('Download the Files', 'digiplanet-digital-products'); ?></h4>
                            <p><?php _e('Click the "Download Now" button above to download the compressed file to your computer.', 'digiplanet-digital-products'); ?></p>
                        </div>
                    </div>
                    
                    <div class="step">
                        <div class="step-number">2</div>
                        <div class="step-content">
                            <h4><?php _e('Extract the Archive', 'digiplanet-digital-products'); ?></h4>
                            <p><?php _e('Use software like WinRAR (Windows) or The Unarchiver (Mac) to extract the downloaded .zip file.', 'digiplanet-digital-products'); ?></p>
                        </div>
                    </div>
                    
                    <div class="step">
                        <div class="step-number">3</div>
                        <div class="step-content">
                            <h4><?php _e('Upload to Your Server', 'digiplanet-digital-products'); ?></h4>
                            <p><?php _e('Upload the extracted files to your web server using an FTP client or your hosting control panel.', 'digiplanet-digital-products'); ?></p>
                        </div>
                    </div>
                    
                    <div class="step">
                        <div class="step-number">4</div>
                        <div class="step-content">
                            <h4><?php _e('Configure the Software', 'digiplanet-digital-products'); ?></h4>
                            <p><?php _e('Follow the configuration instructions in the README file or documentation included with the download.', 'digiplanet-digital-products'); ?></p>
                        </div>
                    </div>
                    
                    <div class="step">
                        <div class="step-number">5</div>
                        <div class="step-content">
                            <h4><?php _e('Activate License', 'digiplanet-digital-products'); ?></h4>
                            <p><?php _e('Enter your license key in the software settings to activate premium features and receive updates.', 'digiplanet-digital-products'); ?></p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Requirements -->
            <?php if ($requirements): ?>
                <div class="requirements">
                    <h3><i class="fas fa-check-circle"></i> <?php _e('System Requirements', 'digiplanet-digital-products'); ?></h3>
                    <ul class="requirements-list">
                        <?php 
                        $req_items = explode("\n", $requirements);
                        foreach ($req_items as $item):
                            if (trim($item)):
                        ?>
                            <li><?php echo esc_html(trim($item)); ?></li>
                        <?php 
                            endif;
                        endforeach; 
                        ?>
                    </ul>
                </div>
            <?php endif; ?>
            
            <!-- Troubleshooting -->
            <div class="troubleshooting">
                <h3><?php _e('Common Issues & Solutions', 'digiplanet-digital-products'); ?></h3>
                
                <div class="faq-item">
                    <h4><?php _e('The download link expired', 'digiplanet-digital-products'); ?></h4>
                    <p><?php _e('Download links are valid for 24 hours. You can generate a new download link from your account dashboard.', 'digiplanet-digital-products'); ?></p>
                </div>
                
                <div class="faq-item">
                    <h4><?php _e('File won\'t extract', 'digiplanet-digital-products'); ?></h4>
                    <p><?php _e('Ensure you have the latest version of your extraction software. Try downloading the file again if it appears corrupted.', 'digiplanet-digital-products'); ?></p>
                </div>
                
                <div class="faq-item">
                    <h4><?php _e('Installation errors', 'digiplanet-digital-products'); ?></h4>
                    <p><?php _e('Check that your server meets the system requirements. Review the error log for specific error messages.', 'digiplanet-digital-products'); ?></p>
                </div>
            </div>
            
            <!-- Support Section -->
            <div class="support-section">
                <h3><?php _e('Need Help With Installation?', 'digiplanet-digital-products'); ?></h3>
                <p><?php _e('Our support team is ready to assist you with any installation or configuration issues.', 'digiplanet-digital-products'); ?></p>
                <a href="<?php echo esc_url(home_url('/support/')); ?>" class="support-button">
                    <?php _e('Get Support', 'digiplanet-digital-products'); ?>
                </a>
            </div>
        </div>
        
        <!-- Footer -->
        <div class="email-footer">
            <p>&copy; <?php echo date('Y'); ?> <?php echo get_bloginfo('name'); ?>. <?php _e('All rights reserved.', 'digiplanet-digital-products'); ?></p>
            
            <div class="footer-links">
                <a href="<?php echo esc_url(home_url('/documentation/')); ?>" class="footer-link">
                    <?php _e('Documentation', 'digiplanet-digital-products'); ?>
                </a>
                <a href="<?php echo esc_url(home_url('/video-tutorials/')); ?>" class="footer-link">
                    <?php _e('Video Tutorials', 'digiplanet-digital-products'); ?>
                </a>
                <a href="<?php echo esc_url(home_url('/knowledge-base/')); ?>" class="footer-link">
                    <?php _e('Knowledge Base', 'digiplanet-digital-products'); ?>
                </a>
            </div>
        </div>
    </div>
</body>
</html>