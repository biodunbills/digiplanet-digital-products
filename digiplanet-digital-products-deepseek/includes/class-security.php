<?php
/**
 * Security handling class
 */

if (!defined('ABSPATH')) {
    exit;
}

class Digiplanet_Security {
    
    /**
     * Sanitize input data
     */
    public static function sanitize($data, $type = 'text') {
        switch ($type) {
            case 'email':
                return sanitize_email($data);
                
            case 'url':
                return esc_url_raw($data);
                
            case 'textarea':
                return sanitize_textarea_field($data);
                
            case 'html':
                return wp_kses_post($data);
                
            case 'price':
                return floatval(preg_replace('/[^0-9.]/', '', $data));
                
            case 'int':
                return intval($data);
                
            case 'key':
                return sanitize_key($data);
                
            case 'title':
                return sanitize_title($data);
                
            case 'text_field':
            default:
                return sanitize_text_field($data);
        }
    }
    
    /**
     * Validate email
     */
    public static function validate_email($email) {
        return is_email($email);
    }
    
    /**
     * Generate nonce
     */
    public static function create_nonce($action = 'digiplanet_nonce') {
        return wp_create_nonce($action);
    }
    
    /**
     * Verify nonce
     */
    public static function verify_nonce($nonce, $action = 'digiplanet_nonce') {
        return wp_verify_nonce($nonce, $action);
    }
    
    /**
     * Validate price
     */
    public static function validate_price($price) {
        $price = preg_replace('/[^0-9.]/', '', $price);
        return is_numeric($price) && $price >= 0;
    }
    
    /**
     * Check if user can access product
     */
    public static function can_access_product($product_id, $user_id = null) {
        if (!$user_id) {
            $user_id = get_current_user_id();
        }
        
        // Check if user has purchased the product
        global $wpdb;
        
        $query = $wpdb->prepare("
            SELECT COUNT(*) 
            FROM {$wpdb->prefix}digiplanet_order_items oi
            INNER JOIN {$wpdb->prefix}digiplanet_orders o ON oi.order_id = o.id
            WHERE oi.product_id = %d 
            AND o.customer_id = %d 
            AND o.payment_status = 'completed'
            AND (oi.download_expires IS NULL OR oi.download_expires > NOW())
        ", $product_id, $user_id);
        
        return $wpdb->get_var($query) > 0;
    }
    
    /**
     * Check download limits
     */
    public static function check_download_limit($order_item_id, $user_id) {
        global $wpdb;
        
        $download_limit = $wpdb->get_var($wpdb->prepare("
            SELECT p.download_limit 
            FROM {$wpdb->prefix}digiplanet_order_items oi
            INNER JOIN {$wpdb->prefix}digiplanet_products p ON oi.product_id = p.id
            WHERE oi.id = %d
        ", $order_item_id));
        
        if ($download_limit == 0) {
            return true; // Unlimited downloads
        }
        
        $download_count = $wpdb->get_var($wpdb->prepare("
            SELECT COUNT(*) 
            FROM {$wpdb->prefix}digiplanet_download_logs 
            WHERE order_item_id = %d AND customer_id = %d
        ", $order_item_id, $user_id));
        
        return $download_count < $download_limit;
    }
    
    /**
     * Log download
     */
    public static function log_download($order_item_id, $user_id, $ip_address = null) {
        global $wpdb;
        
        if (!$ip_address) {
            $ip_address = self::get_client_ip();
        }
        
        $user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? sanitize_text_field($_SERVER['HTTP_USER_AGENT']) : '';
        
        $wpdb->insert(
            $wpdb->prefix . 'digiplanet_download_logs',
            [
                'order_item_id' => $order_item_id,
                'customer_id' => $user_id,
                'ip_address' => $ip_address,
                'user_agent' => $user_agent,
                'downloaded_at' => current_time('mysql')
            ],
            ['%d', '%d', '%s', '%s', '%s']
        );
    }
    
    /**
     * Get client IP address
     */
    public static function get_client_ip() {
        $ip = '';
        
        if (isset($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } elseif (isset($_SERVER['HTTP_X_FORWARDED'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED'];
        } elseif (isset($_SERVER['HTTP_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_FORWARDED_FOR'];
        } elseif (isset($_SERVER['HTTP_FORWARDED'])) {
            $ip = $_SERVER['HTTP_FORWARDED'];
        } elseif (isset($_SERVER['REMOTE_ADDR'])) {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        
        return filter_var($ip, FILTER_VALIDATE_IP) ? $ip : '0.0.0.0';
    }
    
    /**
     * Generate secure download URL
     */
    public static function generate_download_url($order_item_id, $user_id) {
        $token = self::create_download_token($order_item_id, $user_id);
        return add_query_arg([
            'digiplanet_download' => $order_item_id,
            'token' => $token,
            'user_id' => $user_id
        ], home_url('/'));
    }
    
    /**
     * Create download token
     */
    private static function create_download_token($order_item_id, $user_id) {
        $data = $order_item_id . '|' . $user_id . '|' . time();
        $hash = hash_hmac('sha256', $data, wp_salt());
        return $hash;
    }
    
    /**
     * Verify download token
     */
    public static function verify_download_token($token, $order_item_id, $user_id) {
        $expected_token = self::create_download_token($order_item_id, $user_id);
        return hash_equals($expected_token, $token);
    }
    
    /**
     * Prevent direct file access
     */
    public static function secure_file_download($file_path) {
        if (!file_exists($file_path)) {
            wp_die(__('File not found.', 'digiplanet-digital-products'));
        }
        
        // Check if it's a valid file
        $allowed_extensions = ['zip', 'pdf', 'txt', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'jpg', 'jpeg', 'png', 'gif', 'mp4', 'mp3', 'wav'];
        $file_ext = strtolower(pathinfo($file_path, PATHINFO_EXTENSION));
        
        if (!in_array($file_ext, $allowed_extensions)) {
            wp_die(__('Invalid file type.', 'digiplanet-digital-products'));
        }
        
        // Set headers
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($file_path) . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($file_path));
        
        // Clear output buffer
        ob_clean();
        flush();
        
        // Read file
        readfile($file_path);
        exit;
    }
}