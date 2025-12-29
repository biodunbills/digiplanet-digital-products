<?php
/**
 * My Licenses Template
 * 
 * @package Digiplanet_Digital_Products
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

$account_manager = Digiplanet_Account_Manager::get_instance();
$license_manager = Digiplanet_License_Manager::get_instance();
$product_manager = Digiplanet_Product_Manager::get_instance();

$licenses = $account_manager->get_customer_licenses(get_current_user_id());
?>

<div class="digiplanet-account-section">
    <div class="digiplanet-section-header">
        <h2><?php esc_html_e('My Licenses', 'digiplanet-digital-products'); ?></h2>
        <p><?php esc_html_e('Manage your product licenses and activations', 'digiplanet-digital-products'); ?></p>
    </div>

    <?php if (empty($licenses)): ?>
        <div class="digiplanet-no-items">
            <div class="digiplanet-no-items-icon">
                <span class="dashicons dashicons-lock"></span>
            </div>
            <h3><?php esc_html_e('No licenses found', 'digiplanet-digital-products'); ?></h3>
            <p><?php esc_html_e('You haven\'t purchased any licensed products yet.', 'digiplanet-digital-products'); ?></p>
            <a href="<?php echo esc_url(get_permalink(get_option('digiplanet_products_page_id'))); ?>" class="digiplanet-btn digiplanet-btn-primary">
                <?php esc_html_e('Browse Products', 'digiplanet-digital-products'); ?>
            </a>
        </div>
    <?php else: ?>
        <div class="digiplanet-licenses-grid">
            <?php foreach ($licenses as $license): ?>
                <div class="digiplanet-license-card" data-license-id="<?php echo esc_attr($license->id); ?>">
                    <div class="digiplanet-license-header">
                        <h3><?php echo esc_html($license->product_name); ?></h3>
                        <span class="digiplanet-license-status digiplanet-status-<?php echo esc_attr($license->status); ?>">
                            <?php echo esc_html(ucfirst($license->status)); ?>
                        </span>
                    </div>
                    
                    <div class="digiplanet-license-body">
                        <div class="digiplanet-license-key">
                            <strong><?php esc_html_e('License Key:', 'digiplanet-digital-products'); ?></strong>
                            <code class="digiplanet-license-key-value"><?php echo esc_html($license->license_key); ?></code>
                            <button type="button" class="digiplanet-copy-btn" data-clipboard-text="<?php echo esc_attr($license->license_key); ?>">
                                <span class="dashicons dashicons-admin-page"></span>
                            </button>
                        </div>
                        
                        <div class="digiplanet-license-meta">
                            <div class="digiplanet-license-meta-item">
                                <span class="dashicons dashicons-info"></span>
                                <strong><?php esc_html_e('Version:', 'digiplanet-digital-products'); ?></strong>
                                <span><?php echo esc_html($license->version); ?></span>
                            </div>
                            
                            <div class="digiplanet-license-meta-item">
                                <span class="dashicons dashicons-update"></span>
                                <strong><?php esc_html_e('Activations:', 'digiplanet-digital-products'); ?></strong>
                                <span><?php echo esc_html($license->activation_count); ?>/<?php echo esc_html($license->max_activations); ?></span>
                            </div>
                            
                            <?php if ($license->expires_at): ?>
                                <div class="digiplanet-license-meta-item">
                                    <span class="dashicons dashicons-calendar"></span>
                                    <strong><?php esc_html_e('Expires:', 'digiplanet-digital-products'); ?></strong>
                                    <span><?php echo date_i18n(get_option('date_format'), strtotime($license->expires_at)); ?></span>
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($license->last_activation): ?>
                                <div class="digiplanet-license-meta-item">
                                    <span class="dashicons dashicons-clock"></span>
                                    <strong><?php esc_html_e('Last Activated:', 'digiplanet-digital-products'); ?></strong>
                                    <span><?php echo date_i18n(get_option('date_format'), strtotime($license->last_activation)); ?></span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="digiplanet-license-actions">
                        <?php if ($license->status === 'active' && $license->activation_count < $license->max_activations): ?>
                            <button type="button" class="digiplanet-btn digiplanet-btn-sm digiplanet-activate-license" data-license-key="<?php echo esc_attr($license->license_key); ?>">
                                <?php esc_html_e('Activate', 'digiplanet-digital-products'); ?>
                            </button>
                        <?php endif; ?>
                        
                        <?php if ($license->activation_count > 0): ?>
                            <button type="button" class="digiplanet-btn digiplanet-btn-sm digiplanet-view-activations" data-license-id="<?php echo esc_attr($license->id); ?>">
                                <?php esc_html_e('View Activations', 'digiplanet-digital-products'); ?>
                            </button>
                        <?php endif; ?>
                        
                        <button type="button" class="digiplanet-btn digiplanet-btn-sm digiplanet-btn-outline digiplanet-manage-license" data-license-id="<?php echo esc_attr($license->id); ?>">
                            <?php esc_html_e('Manage', 'digiplanet-digital-products'); ?>
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <!-- License Activation Modal -->
        <div id="digiplanet-license-modal" class="digiplanet-modal">
            <div class="digiplanet-modal-content">
                <div class="digiplanet-modal-header">
                    <h3><?php esc_html_e('License Activation', 'digiplanet-digital-products'); ?></h3>
                    <button type="button" class="digiplanet-modal-close">&times;</button>
                </div>
                <div class="digiplanet-modal-body">
                    <form id="digiplanet-activate-license-form">
                        <div class="digiplanet-form-group">
                            <label for="activation_domain"><?php esc_html_e('Domain/URL', 'digiplanet-digital-products'); ?></label>
                            <input type="url" id="activation_domain" name="activation_domain" required placeholder="https://example.com">
                        </div>
                        <div class="digiplanet-form-group">
                            <label for="activation_note"><?php esc_html_e('Note (Optional)', 'digiplanet-digital-products'); ?></label>
                            <textarea id="activation_note" name="activation_note" rows="3" placeholder="<?php esc_attr_e('Add a note about this activation...', 'digiplanet-digital-products'); ?>"></textarea>
                        </div>
                        <input type="hidden" id="license_key" name="license_key" value="">
                        <div class="digiplanet-modal-actions">
                            <button type="submit" class="digiplanet-btn digiplanet-btn-primary">
                                <?php esc_html_e('Activate License', 'digiplanet-digital-products'); ?>
                            </button>
                            <button type="button" class="digiplanet-btn digiplanet-btn-secondary digiplanet-modal-close">
                                <?php esc_html_e('Cancel', 'digiplanet-digital-products'); ?>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Activations List Modal -->
        <div id="digiplanet-activations-modal" class="digiplanet-modal">
            <div class="digiplanet-modal-content">
                <div class="digiplanet-modal-header">
                    <h3><?php esc_html_e('License Activations', 'digiplanet-digital-products'); ?></h3>
                    <button type="button" class="digiplanet-modal-close">&times;</button>
                </div>
                <div class="digiplanet-modal-body">
                    <div id="digiplanet-activations-list"></div>
                </div>
            </div>
        </div>
        
        <script type="text/javascript">
        jQuery(document).ready(function($) {
            // Copy license key
            $('.digiplanet-copy-btn').on('click', function(e) {
                e.preventDefault();
                const $button = $(this);
                const text = $button.data('clipboard-text');
                
                // Create temporary input element
                const $temp = $('<input>');
                $('body').append($temp);
                $temp.val(text).select();
                document.execCommand('copy');
                $temp.remove();
                
                // Show feedback
                const originalHTML = $button.html();
                $button.html('<span class="dashicons dashicons-yes"></span>');
                setTimeout(function() {
                    $button.html(originalHTML);
                }, 2000);
            });
            
            // Activate license
            $('.digiplanet-activate-license').on('click', function() {
                const licenseKey = $(this).data('license-key');
                $('#digiplanet-license-modal #license_key').val(licenseKey);
                $('#digiplanet-license-modal').addClass('digiplanet-modal-open');
            });
            
            // View activations
            $('.digiplanet-view-activations').on('click', function() {
                const licenseId = $(this).data('license-id');
                
                $.ajax({
                    url: digiplanet_ajax.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'digiplanet_get_license_activations',
                        license_id: licenseId,
                        nonce: digiplanet_ajax.nonce
                    },
                    beforeSend: function() {
                        $('#digiplanet-activations-list').html('<div class="digiplanet-loading"><?php esc_html_e("Loading...", "digiplanet-digital-products"); ?></div>');
                    },
                    success: function(response) {
                        if (response.success) {
                            $('#digiplanet-activations-list').html(response.data.html);
                            $('#digiplanet-activations-modal').addClass('digiplanet-modal-open');
                        } else {
                            alert(response.data.message);
                        }
                    }
                });
            });
            
            // Close modal
            $('.digiplanet-modal-close, .digiplanet-modal').on('click', function(e) {
                if ($(e.target).hasClass('digiplanet-modal') || $(e.target).hasClass('digiplanet-modal-close')) {
                    $('.digiplanet-modal').removeClass('digiplanet-modal-open');
                }
            });
            
            // License activation form
            $('#digiplanet-activate-license-form').on('submit', function(e) {
                e.preventDefault();
                
                const formData = $(this).serialize();
                
                $.ajax({
                    url: digiplanet_ajax.ajax_url,
                    type: 'POST',
                    data: formData + '&action=digiplanet_activate_license&nonce=' + digiplanet_ajax.nonce,
                    beforeSend: function() {
                        $('#digiplanet-activate-license-form button[type="submit"]').prop('disabled', true).text('<?php esc_html_e("Activating...", "digiplanet-digital-products"); ?>');
                    },
                    success: function(response) {
                        if (response.success) {
                            alert(response.data.message);
                            $('#digiplanet-license-modal').removeClass('digiplanet-modal-open');
                            location.reload();
                        } else {
                            alert(response.data.message);
                        }
                    },
                    complete: function() {
                        $('#digiplanet-activate-license-form button[type="submit"]').prop('disabled', false).text('<?php esc_html_e("Activate License", "digiplanet-digital-products"); ?>');
                    }
                });
            });
        });
        </script>
    <?php endif; ?>
</div>