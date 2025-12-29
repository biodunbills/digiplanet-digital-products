<?php
/**
 * Uninstall Digiplanet Digital Products
 */

if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Check if we should remove all data
$remove_data = get_option('digiplanet_remove_data_on_uninstall', 'no');

if ($remove_data === 'yes') {
    global $wpdb;
    
    // Remove all custom tables
    $tables = [
        $wpdb->prefix . 'digiplanet_products',
        $wpdb->prefix . 'digiplanet_categories',
        $wpdb->prefix . 'digiplanet_orders',
        $wpdb->prefix . 'digiplanet_order_items',
        $wpdb->prefix . 'digiplanet_licenses',
        $wpdb->prefix . 'digiplanet_reviews',
        $wpdb->prefix . 'digiplanet_download_logs',
    ];
    
    foreach ($tables as $table) {
        $wpdb->query("DROP TABLE IF EXISTS $table");
    }
    
    // Remove options
    $options = [
        'digiplanet_version',
        'digiplanet_db_version',
        'digiplanet_currency',
        'digiplanet_currency_position',
        'digiplanet_decimal_places',
        'digiplanet_thousand_separator',
        'digiplanet_decimal_separator',
        'digiplanet_enable_tax',
        'digiplanet_tax_rate',
        'digiplanet_enable_stripe',
        'digiplanet_stripe_test_mode',
        'digiplanet_stripe_test_publishable_key',
        'digiplanet_stripe_test_secret_key',
        'digiplanet_stripe_live_publishable_key',
        'digiplanet_stripe_live_secret_key',
        'digiplanet_stripe_webhook_secret',
        'digiplanet_enable_paystack',
        'digiplanet_paystack_test_mode',
        'digiplanet_paystack_test_public_key',
        'digiplanet_paystack_test_secret_key',
        'digiplanet_paystack_live_public_key',
        'digiplanet_paystack_live_secret_key',
        'digiplanet_email_from_name',
        'digiplanet_email_from_address',
        'digiplanet_order_email_subject',
        'digiplanet_order_email_template',
        'digiplanet_cart_page_id',
        'digiplanet_checkout_page_id',
        'digiplanet_account_page_id',
        'digiplanet_products_page_id',
        'digiplanet_terms_page_id',
        'digiplanet_privacy_page_id',
        'digiplanet_remove_data_on_uninstall',
        'digiplanet_enable_debug_log',
    ];
    
    foreach ($options as $option) {
        delete_option($option);
    }
    
    // Remove user roles
    remove_role('digital_customer');
    remove_role('software_client');
    
    // Clear scheduled hooks
    wp_clear_scheduled_hook('digiplanet_daily_cleanup');
    
    // Flush rewrite rules
    flush_rewrite_rules();
}