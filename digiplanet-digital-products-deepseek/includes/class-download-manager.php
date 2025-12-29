<?php
/**
 * Download management class
 */

if (!defined('ABSPATH')) {
    exit;
}

class Digiplanet_Download_Manager {
    
    private static $instance = null;
    
    /**
     * Get singleton instance
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Process download request
     */
    public function process_download($order_item_id, $user_id, $token) {
        global $wpdb;
        
        // Verify token
        if (!Digiplanet_Security::verify_download_token($token, $order_item_id, $user_id)) {
            wp_die(__('Invalid download token.', 'digiplanet-digital-products'));
        }
        
        // Get order item details
        $order_item = $wpdb->get_row($wpdb->prepare("
            SELECT oi.*, p.download_link, p.download_limit, p.name as product_name
            FROM {$wpdb->prefix}digiplanet_order_items oi
            INNER JOIN {$wpdb->prefix}digiplanet_products p ON oi.product_id = p.id
            WHERE oi.id = %d
        ", $order_item_id));
        
        if (!$order_item) {
            wp_die(__('Download not found.', 'digiplanet-digital-products'));
        }
        
        // Check download limits
        if (!Digiplanet_Security::check_download_limit($order_item_id, $user_id)) {
            wp_die(__('Download limit reached.', 'digiplanet-digital-products'));
        }
        
        // Check if download expired
        if ($order_item->download_expires && strtotime($order_item->download_expires) < time()) {
            wp_die(__('Download link has expired.', 'digiplanet-digital-products'));
        }
        
        // Log download
        Digiplanet_Security::log_download($order_item_id, $user_id);
        
        // Get file path
        $file_path = $this->get_file_path($order_item->download_link);
        
        if (!$file_path || !file_exists($file_path)) {
            wp_die(__('File not found.', 'digiplanet-digital-products'));
        }
        
        // Serve file for download
        $this->serve_file($file_path, $order_item->product_name);
    }
    
    /**
     * Get file path from download link
     */
    private function get_file_path($download_link) {
        // Check if it's a URL or path
        if (filter_var($download_link, FILTER_VALIDATE_URL)) {
            // It's a URL, check if it's local
            $site_url = site_url();
            if (strpos($download_link, $site_url) === 0) {
                // Local file, convert to path
                $upload_dir = wp_upload_dir();
                $upload_url = $upload_dir['baseurl'];
                
                if (strpos($download_link, $upload_url) === 0) {
                    $file_path = str_replace($upload_url, $upload_dir['basedir'], $download_link);
                    return $file_path;
                }
            }
            return false; // External URL, not supported for direct download
        } else {
            // Assume it's a local path
            return $download_link;
        }
    }
    
    /**
     * Serve file for download
     */
    private function serve_file($file_path, $filename = '') {
        if (!file_exists($file_path)) {
            wp_die(__('File not found.', 'digiplanet-digital-products'));
        }
        
        // Clean output buffer
        if (ob_get_level()) {
            ob_end_clean();
        }
        
        // Get file info
        $file_size = filesize($file_path);
        $file_extension = pathinfo($file_path, PATHINFO_EXTENSION);
        
        if (!$filename) {
            $filename = basename($file_path);
        } else {
            $filename = sanitize_file_name($filename) . '.' . $file_extension;
        }
        
        // Set headers
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . $file_size);
        
        // Read file
        readfile($file_path);
        exit;
    }
    
    /**
     * Get download URL for order item
     */
    public function get_download_url($order_item_id, $user_id) {
        return Digiplanet_Security::generate_download_url($order_item_id, $user_id);
    }
    
    /**
     * Get customer's available downloads
     */
    public function get_customer_downloads($customer_id) {
        global $wpdb;
        
        return $wpdb->get_results($wpdb->prepare("
            SELECT 
                oi.id as order_item_id,
                oi.product_name,
                oi.license_key,
                oi.download_count,
                oi.download_expires,
                p.download_limit,
                o.order_number,
                o.created_at as order_date
            FROM {$wpdb->prefix}digiplanet_order_items oi
            INNER JOIN {$wpdb->prefix}digiplanet_orders o ON oi.order_id = o.id
            INNER JOIN {$wpdb->prefix}digiplanet_products p ON oi.product_id = p.id
            WHERE o.customer_id = %d 
            AND o.payment_status = 'completed'
            AND (oi.download_expires IS NULL OR oi.download_expires > NOW())
            ORDER BY o.created_at DESC
        ", $customer_id));
    }
    
    /**
     * Check if customer can download product
     */
    public function can_download_product($product_id, $customer_id) {
        global $wpdb;
        
        $count = $wpdb->get_var($wpdb->prepare("
            SELECT COUNT(*) 
            FROM {$wpdb->prefix}digiplanet_order_items oi
            INNER JOIN {$wpdb->prefix}digiplanet_orders o ON oi.order_id = o.id
            WHERE oi.product_id = %d 
            AND o.customer_id = %d 
            AND o.payment_status = 'completed'
            AND (oi.download_expires IS NULL OR oi.download_expires > NOW())
        ", $product_id, $customer_id));
        
        return $count > 0;
    }
    
    /**
     * Get download count for order item
     */
    public function get_download_count($order_item_id, $customer_id) {
        global $wpdb;
        
        return $wpdb->get_var($wpdb->prepare("
            SELECT COUNT(*) 
            FROM {$wpdb->prefix}digiplanet_download_logs 
            WHERE order_item_id = %d AND customer_id = %d
        ", $order_item_id, $customer_id));
    }
    
    /**
     * Get downloads remaining
     */
    public function get_downloads_remaining($order_item_id, $customer_id) {
        global $wpdb;
        
        $order_item = $wpdb->get_row($wpdb->prepare("
            SELECT oi.*, p.download_limit 
            FROM {$wpdb->prefix}digiplanet_order_items oi
            INNER JOIN {$wpdb->prefix}digiplanet_products p ON oi.product_id = p.id
            WHERE oi.id = %d
        ", $order_item_id));
        
        if (!$order_item) {
            return 0;
        }
        
        if ($order_item->download_limit == 0) {
            return -1; // Unlimited
        }
        
        $download_count = $this->get_download_count($order_item_id, $customer_id);
        $remaining = $order_item->download_limit - $download_count;
        
        return max(0, $remaining);
    }
    
    /**
     * Get download logs for admin
     */
    public function get_download_logs($filters = [], $limit = 50, $offset = 0) {
        global $wpdb;
        
        $where = ['1=1'];
        $params = [];
        
        if (!empty($filters['product_id'])) {
            $where[] = 'p.id = %d';
            $params[] = $filters['product_id'];
        }
        
        if (!empty($filters['customer_id'])) {
            $where[] = 'dl.customer_id = %d';
            $params[] = $filters['customer_id'];
        }
        
        if (!empty($filters['date_from'])) {
            $where[] = 'dl.downloaded_at >= %s';
            $params[] = $filters['date_from'];
        }
        
        if (!empty($filters['date_to'])) {
            $where[] = 'dl.downloaded_at <= %s';
            $params[] = $filters['date_to'];
        }
        
        $where_clause = implode(' AND ', $where);
        
        $query = $wpdb->prepare("
            SELECT 
                dl.*,
                u.user_email,
                u.display_name,
                p.name as product_name,
                oi.product_name as order_item_name
            FROM {$wpdb->prefix}digiplanet_download_logs dl
            INNER JOIN {$wpdb->prefix}users u ON dl.customer_id = u.ID
            INNER JOIN {$wpdb->prefix}digiplanet_order_items oi ON dl.order_item_id = oi.id
            INNER JOIN {$wpdb->prefix}digiplanet_products p ON oi.product_id = p.id
            WHERE {$where_clause}
            ORDER BY dl.downloaded_at DESC
            LIMIT %d OFFSET %d
        ", array_merge($params, [$limit, $offset]));
        
        return $wpdb->get_results($query);
    }
}