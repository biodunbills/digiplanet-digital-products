<?php
/**
 * Account management class
 */

if (!defined('ABSPATH')) {
    exit;
}

class Digiplanet_Account_Manager {
    
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
     * Create new digital customer account
     */
    public function create_digital_customer($email, $password, $user_data = []) {
        // Check if user already exists
        if (email_exists($email)) {
            return [
                'success' => false,
                'message' => __('Email address already exists.', 'digiplanet-digital-products')
            ];
        }
        
        $username = $this->generate_username($email);
        
        $user_args = wp_parse_args($user_data, [
            'user_login' => $username,
            'user_email' => $email,
            'user_pass' => $password,
            'role' => 'digital_customer',
            'first_name' => $user_data['first_name'] ?? '',
            'last_name' => $user_data['last_name'] ?? '',
        ]);
        
        $user_id = wp_insert_user($user_args);
        
        if (is_wp_error($user_id)) {
            return [
                'success' => false,
                'message' => $user_id->get_error_message()
            ];
        }
        
        // Send welcome email
        $email_manager = Digiplanet_Email_Manager::get_instance();
        $email_manager->send_new_account_email($user_id, $password);
        
        return [
            'success' => true,
            'user_id' => $user_id,
            'message' => __('Account created successfully.', 'digiplanet-digital-products')
        ];
    }
    
    /**
     * Create software client account (admin only)
     */
    public function create_software_client($email, $user_data = []) {
        // Check if user already exists
        if (email_exists($email)) {
            return [
                'success' => false,
                'message' => __('Email address already exists.', 'digiplanet-digital-products')
            ];
        }
        
        $username = $this->generate_username($email);
        $password = wp_generate_password(12, true, true);
        
        $user_args = wp_parse_args($user_data, [
            'user_login' => $username,
            'user_email' => $email,
            'user_pass' => $password,
            'role' => 'software_client',
            'first_name' => $user_data['first_name'] ?? '',
            'last_name' => $user_data['last_name'] ?? '',
        ]);
        
        $user_id = wp_insert_user($user_args);
        
        if (is_wp_error($user_id)) {
            return [
                'success' => false,
                'message' => $user_id->get_error_message()
            ];
        }
        
        // Send welcome email with credentials
        $email_manager = Digiplanet_Email_Manager::get_instance();
        $email_manager->send_new_account_email($user_id, $password);
        
        return [
            'success' => true,
            'user_id' => $user_id,
            'message' => __('Software client account created successfully.', 'digiplanet-digital-products')
        ];
    }
    
    /**
     * Generate username from email
     */
    private function generate_username($email) {
        $username = sanitize_user(current(explode('@', $email)), true);
        
        // Ensure username is unique
        $counter = 1;
        $original_username = $username;
        
        while (username_exists($username)) {
            $username = $original_username . $counter;
            $counter++;
        }
        
        return $username;
    }
    
    /**
     * Get customer's purchased products
     */
    public function get_customer_products($customer_id, $limit = 20, $offset = 0) {
        global $wpdb;
        
        return $wpdb->get_results($wpdb->prepare("
            SELECT DISTINCT p.*, oi.license_key, oi.download_count, oi.download_expires
            FROM {$wpdb->prefix}digiplanet_products p
            INNER JOIN {$wpdb->prefix}digiplanet_order_items oi ON p.id = oi.product_id
            INNER JOIN {$wpdb->prefix}digiplanet_orders o ON oi.order_id = o.id
            WHERE o.customer_id = %d 
            AND o.payment_status = 'completed'
            AND (oi.download_expires IS NULL OR oi.download_expires > NOW())
            ORDER BY o.created_at DESC
            LIMIT %d OFFSET %d
        ", $customer_id, $limit, $offset));
    }
    
    /**
     * Get customer's active licenses
     */
    public function get_customer_licenses($customer_id) {
        global $wpdb;
        
        return $wpdb->get_results($wpdb->prepare("
            SELECT l.*, p.name as product_name, p.version
            FROM {$wpdb->prefix}digiplanet_licenses l
            INNER JOIN {$wpdb->prefix}digiplanet_products p ON l.product_id = p.id
            WHERE l.customer_id = %d 
            AND l.status = 'active'
            ORDER BY l.created_at DESC
        ", $customer_id));
    }
    
    /**
     * Update customer profile
     */
    public function update_profile($user_id, $data) {
        $user_data = [
            'ID' => $user_id
        ];
        
        if (!empty($data['first_name'])) {
            $user_data['first_name'] = sanitize_text_field($data['first_name']);
        }
        
        if (!empty($data['last_name'])) {
            $user_data['last_name'] = sanitize_text_field($data['last_name']);
        }
        
        if (!empty($data['email']) && is_email($data['email'])) {
            $user_data['user_email'] = sanitize_email($data['email']);
        }
        
        if (!empty($data['display_name'])) {
            $user_data['display_name'] = sanitize_text_field($data['display_name']);
        }
        
        $result = wp_update_user($user_data);
        
        if (is_wp_error($result)) {
            return [
                'success' => false,
                'message' => $result->get_error_message()
            ];
        }
        
        // Update custom meta
        if (!empty($data['phone'])) {
            update_user_meta($user_id, 'digiplanet_phone', sanitize_text_field($data['phone']));
        }
        
        if (!empty($data['company'])) {
            update_user_meta($user_id, 'digiplanet_company', sanitize_text_field($data['company']));
        }
        
        return [
            'success' => true,
            'message' => __('Profile updated successfully.', 'digiplanet-digital-products')
        ];
    }
    
    /**
     * Change password
     */
    public function change_password($user_id, $current_password, $new_password) {
        $user = get_userdata($user_id);
        
        if (!wp_check_password($current_password, $user->user_pass, $user_id)) {
            return [
                'success' => false,
                'message' => __('Current password is incorrect.', 'digiplanet-digital-products')
            ];
        }
        
        wp_set_password($new_password, $user_id);
        
        // Send password change notification
        $this->send_password_change_notification($user_id);
        
        return [
            'success' => true,
            'message' => __('Password changed successfully.', 'digiplanet-digital-products')
        ];
    }
    
    /**
     * Send password change notification
     */
    private function send_password_change_notification($user_id) {
        $user = get_userdata($user_id);
        
        $subject = __('Your Password Has Been Changed', 'digiplanet-digital-products');
        $to = $user->user_email;
        $headers = [
            'Content-Type: text/html; charset=UTF-8',
            'From: ' . get_bloginfo('name') . ' <' . get_bloginfo('admin_email') . '>'
        ];
        
        $message = __('Your password has been successfully changed. If you did not make this change, please contact support immediately.', 'digiplanet-digital-products');
        
        wp_mail($to, $subject, $message, $headers);
    }
    
    /**
     * Get customer dashboard stats
     */
    public function get_customer_stats($customer_id) {
        global $wpdb;
        
        $stats = [
            'total_orders' => 0,
            'total_spent' => 0,
            'total_products' => 0,
            'active_licenses' => 0,
        ];
        
        // Total orders
        $stats['total_orders'] = $wpdb->get_var($wpdb->prepare("
            SELECT COUNT(*) 
            FROM {$wpdb->prefix}digiplanet_orders 
            WHERE customer_id = %d 
            AND payment_status = 'completed'
        ", $customer_id));
        
        // Total spent
        $stats['total_spent'] = $wpdb->get_var($wpdb->prepare("
            SELECT SUM(total_amount) 
            FROM {$wpdb->prefix}digiplanet_orders 
            WHERE customer_id = %d 
            AND payment_status = 'completed'
        ", $customer_id)) ?: 0;
        
        // Total products
        $stats['total_products'] = $wpdb->get_var($wpdb->prepare("
            SELECT COUNT(DISTINCT product_id) 
            FROM {$wpdb->prefix}digiplanet_order_items oi
            INNER JOIN {$wpdb->prefix}digiplanet_orders o ON oi.order_id = o.id
            WHERE o.customer_id = %d 
            AND o.payment_status = 'completed'
        ", $customer_id));
        
        // Active licenses
        $stats['active_licenses'] = $wpdb->get_var($wpdb->prepare("
            SELECT COUNT(*) 
            FROM {$wpdb->prefix}digiplanet_licenses 
            WHERE customer_id = %d 
            AND status = 'active'
        ", $customer_id));
        
        return $stats;
    }
}