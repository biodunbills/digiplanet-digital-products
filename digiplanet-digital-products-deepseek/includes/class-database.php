<?php
/**
 * Database management class
 */

if (!defined('ABSPATH')) {
    exit;
}

class Digiplanet_Database {
    
    /**
     * Create required database tables
     */
    public static function create_tables() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        $tables = array(
            "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}digiplanet_products (
                id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                name VARCHAR(255) NOT NULL,
                slug VARCHAR(255) NOT NULL,
                description LONGTEXT,
                short_description TEXT,
                price DECIMAL(10,2) NOT NULL DEFAULT '0.00',
                sale_price DECIMAL(10,2) DEFAULT NULL,
                sku VARCHAR(100) DEFAULT NULL,
                download_link VARCHAR(500) DEFAULT NULL,
                download_limit INT(11) DEFAULT 0,
                license_type VARCHAR(50) DEFAULT 'single',
                version VARCHAR(50) DEFAULT '1.0.0',
                category_id BIGINT(20) DEFAULT 0,
                subcategory_id BIGINT(20) DEFAULT 0,
                featured_image_id BIGINT(20) DEFAULT NULL,
                gallery_image_ids TEXT,
                file_size VARCHAR(50) DEFAULT NULL,
                file_format VARCHAR(50) DEFAULT NULL,
                requirements TEXT,
                tags TEXT,
                rating DECIMAL(3,2) DEFAULT '0.00',
                review_count INT(11) DEFAULT 0,
                sales_count INT(11) DEFAULT 0,
                view_count INT(11) DEFAULT 0,
                status ENUM('published', 'draft', 'archived') DEFAULT 'draft',
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                UNIQUE KEY slug (slug),
                KEY category_id (category_id),
                KEY status (status),
                KEY price (price)
            ) $charset_collate;",
            
            "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}digiplanet_categories (
                id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                name VARCHAR(255) NOT NULL,
                slug VARCHAR(255) NOT NULL,
                description TEXT,
                parent_id BIGINT(20) DEFAULT 0,
                image_id BIGINT(20) DEFAULT NULL,
                sort_order INT(11) DEFAULT 0,
                product_count INT(11) DEFAULT 0,
                status ENUM('active', 'inactive') DEFAULT 'active',
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                UNIQUE KEY slug (slug),
                KEY parent_id (parent_id)
            ) $charset_collate;",
            
            "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}digiplanet_orders (
                id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                order_number VARCHAR(50) NOT NULL,
                customer_id BIGINT(20) UNSIGNED NOT NULL,
                customer_email VARCHAR(255) NOT NULL,
                customer_name VARCHAR(255) NOT NULL,
                total_amount DECIMAL(10,2) NOT NULL,
                tax_amount DECIMAL(10,2) DEFAULT '0.00',
                discount_amount DECIMAL(10,2) DEFAULT '0.00',
                payment_method VARCHAR(50) NOT NULL,
                payment_status ENUM('pending', 'completed', 'failed', 'refunded') DEFAULT 'pending',
                transaction_id VARCHAR(255) DEFAULT NULL,
                ip_address VARCHAR(45) DEFAULT NULL,
                user_agent TEXT,
                notes TEXT,
                status ENUM('pending', 'processing', 'completed', 'cancelled') DEFAULT 'pending',
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                UNIQUE KEY order_number (order_number),
                KEY customer_id (customer_id),
                KEY payment_status (payment_status),
                KEY status (status)
            ) $charset_collate;",
            
            "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}digiplanet_order_items (
                id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                order_id BIGINT(20) UNSIGNED NOT NULL,
                product_id BIGINT(20) UNSIGNED NOT NULL,
                product_name VARCHAR(255) NOT NULL,
                product_price DECIMAL(10,2) NOT NULL,
                quantity INT(11) NOT NULL DEFAULT 1,
                subtotal DECIMAL(10,2) NOT NULL,
                license_key VARCHAR(100) DEFAULT NULL,
                download_count INT(11) DEFAULT 0,
                download_expires DATETIME DEFAULT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                KEY order_id (order_id),
                KEY product_id (product_id),
                KEY license_key (license_key)
            ) $charset_collate;",
            
            "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}digiplanet_licenses (
                id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                license_key VARCHAR(100) NOT NULL,
                product_id BIGINT(20) UNSIGNED NOT NULL,
                order_id BIGINT(20) UNSIGNED DEFAULT NULL,
                customer_id BIGINT(20) UNSIGNED DEFAULT NULL,
                status ENUM('active', 'inactive', 'expired', 'revoked') DEFAULT 'active',
                activation_count INT(11) DEFAULT 0,
                max_activations INT(11) DEFAULT 1,
                expires_at DATETIME DEFAULT NULL,
                last_activation DATETIME DEFAULT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                UNIQUE KEY license_key (license_key),
                KEY product_id (product_id),
                KEY customer_id (customer_id),
                KEY status (status)
            ) $charset_collate;",
            
            "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}digiplanet_reviews (
                id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                product_id BIGINT(20) UNSIGNED NOT NULL,
                customer_id BIGINT(20) UNSIGNED NOT NULL,
                order_id BIGINT(20) UNSIGNED NOT NULL,
                rating INT(11) NOT NULL,
                title VARCHAR(255) DEFAULT NULL,
                comment TEXT,
                status ENUM('pending', 'approved', 'spam') DEFAULT 'pending',
                helpful_count INT(11) DEFAULT 0,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                KEY product_id (product_id),
                KEY customer_id (customer_id),
                KEY status (status)
            ) $charset_collate;",
            
            "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}digiplanet_download_logs (
                id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                order_item_id BIGINT(20) UNSIGNED NOT NULL,
                customer_id BIGINT(20) UNSIGNED NOT NULL,
                ip_address VARCHAR(45) NOT NULL,
                user_agent TEXT,
                downloaded_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                KEY order_item_id (order_item_id),
                KEY customer_id (customer_id),
                KEY downloaded_at (downloaded_at)
            ) $charset_collate;"
        );
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        
        foreach ($tables as $table) {
            dbDelta($table);
        }
    }
    
    /**
     * Get database version
     */
    public static function get_db_version() {
        return get_option('digiplanet_db_version', '1.0.0');
    }
    
    /**
     * Update database version
     */
    public static function update_db_version($version) {
        update_option('digiplanet_db_version', $version);
    }
}